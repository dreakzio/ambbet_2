<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Withdraw extends CI_Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Bangkok');
        parent::__construct();

        if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'],[roleAdmin(),roleSuperAdmin()])) {
            redirect('../auth');
        }
    }
    public function index()
    {
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "ถอนเงิน",
			'description' => 'หน้ารายการ',
			'page_url' => $currentURL,
		]);
		$data['user'] = [];
        $data['page'] = 'withdraw/withdraw';
        $this->load->view('main', $data);
    }
    public function withdraw_list_page()
    {
        $get = $this->input->get();
        $search = $get['search']['value'];
        // $dir = $get['order'][0]['dir'];//order
        $per_page = $get['length'];//จำนวนที่แสดงต่อ 1 หน้า
        $page = $get['start'];
        $search_data = [
         'per_page' => $per_page,//left
         'page' => $page,
         'type' => 2
        ];
        if ($search!="") {
            $search_data['search'] = $search;
        }
		if(isset($get['date_start']) && isset($get['date_end'])){
			$search_data['date_start'] = $get['date_start'];
			$search_data['date_end'] = $get['date_end'];
		}
		if(isset($get['status']) && $get['status'] !== ""){
			$search_data['status'] = $get['status'];
		}
        $withdraw_count_all = $this->Finance_model->finance_count([
        	'type' => 2
		]);
        $withdraw_count_search = $this->Finance_model->finance_count($search_data);
        $data = $this->Finance_model->finance_list_page($search_data);
        echo json_encode([
         "draw" => intval($get['draw']),
         "recordsTotal" => intval($withdraw_count_all),
         "recordsFiltered" => intval($withdraw_count_search),
         "data" => $data,
       ]);
    }

	public function withdraw_list_excel()
	{
		$post = $this->input->post();
		$search = $post['search']['value'];
		$search_data = [
			'type' => 2
		];
		if ($search!="") {
			$search_data['search'] = $search;
		}
		if(isset($post['date_start']) && isset($post['date_end'])){
			$search_data['date_start'] = $post['date_start'];
			$search_data['date_end'] = $post['date_end'];
		}
		if(isset($get['status']) && $get['status'] !== ""){
			$search_data['status'] = $get['status'];
		}
		$data = $this->Finance_model->finance_list_excel($search_data);
		echo json_encode([
			"data" => $data,
		]);
	}

	public function withdraw_list_no_paginate_page()
	{
		$get = $this->input->get();
		$search = $get['search']['value'];
		// $dir = $get['order'][0]['dir'];//order
		$per_page = $get['length'];//จำนวนที่แสดงต่อ 1 หน้า
		$page = $get['start'];
		$search_data = [
			'per_page' => $per_page,//left
			'page' => $page,
			'type' => 2,
			'status' => 0,
		];
		if ($search!="") {
			$search_data['search'] = $search;
		}
		$withdraw_count_all = $this->Finance_model->finance_no_join_count($search_data);
		$data = $this->Finance_model->finance_list_no_join_page($search_data);
		echo json_encode([
			"draw" => intval($get['draw']),
			"recordsTotal" => intval($withdraw_count_all),
			"data" => $data,
		]);
	}
    public function withdraw_form_detail($id = "")
    {
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "ถอนเงิน",
			'description' => 'หน้ารายละเอียด',
			'page_url' => $currentURL,
		]);
        $data['withdraw'] = $this->Finance_model->finance_find([
          'id' => $id
        ]);
        $data['page'] = 'withdraw/withdraw_detail';
        $this->load->view('main', $data);
    }
	public function withdraw_status($id = "")
	{
		check_parameter([], 'POST');
		$post = $this->input->post();

		$chk_cache_process_withdraw_chk = $this->cache->file->get('process_withdraw_'.$id);
		if($chk_cache_process_withdraw_chk  !== FALSE){
			$message_error = "รายการนี้กำลังถูกดำเนินการอยู่";
			echo json_encode([
				'message' => 'ทำรายการไม่สำเร็จ '.$message_error,
				'error' => true
			]);
			exit();
		}else{
			$this->cache->file->save('process_withdraw_'.$id,$_SESSION['user']['id'], 60);
		}

		$finance = $this->Finance_model->finance_find([
			'id' => $id
		]);
		$search_data = [
			'id' => $finance['account'],
		];
		$member = $this->User_model->user_find($search_data);
		//เพิ่ม Logs
		$log_deposit_withdraw_id = $this->Log_deposit_withdraw_model->log_deposit_withdraw_create([
			'account' => null,
			'amount' => null,
			'username' => $member['username'],
			'amount_before' => null,
			'type' => '2', //ถอน
			'description' => 'ถอนเงิน',
			'admin' =>$_SESSION['user']['id'],
		]);

		if ($finance!="") {

			if(
				($finance['status'] == "1" || $finance['status'] == "3") && ($post['status'] == "1" || $post['status'] == "3")
			){
				$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
					'id' => $log_deposit_withdraw_id
				]);
				$status = "สำเร็จ (ถอนออโต้)";
				if($finance['status'] == "3"){
					$status = "สำเร็จ (ถอนมือ)";
				}
				if($log_deposit_withdraw!=""){
					$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
						'id' => $log_deposit_withdraw_id,
						'account' => $finance['account'],
						'amount' => $finance['amount'],
						'description' => $log_deposit_withdraw['description']." | ทำรายการไม่สำเร็จ รายการ Finance#".$finance['id']." อยู่ในสถานะ".$status." อยู่แล้ว",
					]);
							$this->cache->file->delete('process_withdraw_'.$id);}
				echo json_encode([
					'message' => 'ทำรายการไม่สำเร็จ รายการ Finance#'.$finance["id"].' อยู่ในสถานะ'.$status.' อยู่แล้ว',
					'error' => true
				]);
				exit();
			}else{
				if($post['status'] == "1"){
					$finance = $this->Finance_model->finance_find([
						'id' => $id
					]);
					if($finance['status'] == "4"){
						$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
							'id' => $log_deposit_withdraw_id
						]);
						if($log_deposit_withdraw!=""){
							$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
								'id' => $log_deposit_withdraw_id,
								'account' => $finance['account'],
								'amount' => $finance['amount'],
								'description' => $log_deposit_withdraw['description']." | ทำรายการไม่สำเร็จ รายการ Finance#".$finance['id']." อยู่ในสถานะ ดำเนินการถอนออโต้ อยู่ ณ ขณะนี้",
							]);
									$this->cache->file->delete('process_withdraw_'.$id);}
						echo json_encode([
							'message' => 'ทำรายการไม่สำเร็จ รายการ Finance#'.$finance["id"].' อยู่ในสถานะ ดำเนินการถอนออโต้ อยู่ ณ ขณะนี้',
							'error' => true
						]);
						exit();
					}else{
						/*$this->Finance_model->finance_update([
							'id' => $id,
							'status' => "4" //ดำเนินการถอน
						]);*/
					}
				}
			}


			//ถอน auto
			//ตรวจสอบสมาชิก
			$amount_before = 0;
			if($member != ""){
				$amount_before = $this->remaining_credit($member);
			}else{
				$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
					'id' => $log_deposit_withdraw_id
				]);
				if($log_deposit_withdraw!=""){
					$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
						'id' => $log_deposit_withdraw_id,
						'account' => $finance['account'],
						'amount' => $finance['amount'],
						'description' => $log_deposit_withdraw['description']." | ทำรายการไม่สำเร็จ ไม่พบสมาชิกในระบบ",
					]);
							$this->cache->file->delete('process_withdraw_'.$id);}
				echo json_encode([
					'message' => 'ทำรายการไม่สำเร็จ ไม่พบสมาชิกในระบบ',
					'error' => true
				]);
				exit();
			}

			if ($post['status']=="2") {

				$form_data_deposit = [];
				$form_data_deposit["account_agent_username"] = $member['account_agent_username'];
				$form_data_deposit["amount"] = $finance['amount'];
				$form_data_deposit = member_credit_data($form_data_deposit);
				$response_deposit = $this->game_api_librarie->deposit($form_data_deposit);
				if (isset($response_deposit['ref'])) {

					$user_admin = $this->User_model->user_find([
						'id' => $_SESSION['user']['id']
					]);
					$log_add_credit_id = $this->Log_add_credit_model->log_add_credit_create([
						'account' => $member['id'],
						'username' => $member['username'],
						'full_name' => $member['full_name'],
						'from_amount' => $finance['amount'],
						'amount' => $form_data_deposit["amount"],
						'type' => 'bonus_from_not_approve_withdraw',
						'description' => 'เพิ่มเครดิตกลับคืน (ถอนเงินไม่อนุมัติ)',
						'manage_by' =>$user_admin['id'],
						'manage_by_username' =>$user_admin['username'],
						'manage_by_full_name' =>$user_admin['full_name'],
					]);

					$this->User_model->user_update([
						'id' => $finance['account'],
						//'amount_wallet' => ($finance['amount_wallet']+$finance['amount'])
					]);

					$log_deposit_id = $this->Log_deposit_withdraw_model->log_deposit_withdraw_create([
						'account' => $member['id'],
						'username' => $member['username'],
						'amount' => $finance['amount'],
						'amount_before' => $amount_before,
						'type' => '1', //ฝาก
						'description' => 'เพิ่มเครดิต (รายการถอน => ไม่อนุมัติ) #'.$finance['id'],
						'admin' =>$_SESSION['user']['id'],
					]);

					$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
						'id' => $log_deposit_withdraw_id
					]);
					if($log_deposit_withdraw!=""){
						$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
							'id' => $log_deposit_withdraw_id,
							'account' => $finance['account'],
							'amount' => $finance['amount'],
							'amount_before' => $amount_before,
							'description' => $log_deposit_withdraw['description']." | เปลี่ยนสถานะเป็นไม่อนุมัติ",
						]);
					}
				}else{
					$error_message = isset($response_deposit['code']) && !empty($response_deposit['code']) ? '(ไม่สำเร็จ : #'.$response_deposit['code'].')' : '(ไม่สำเร็จ)';
					$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
						'id' => $log_deposit_withdraw_id
					]);
					if($log_deposit_withdraw!=""){
						$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
							'id' => $log_deposit_withdraw_id,
							'account' => $finance['account'],
							'amount' => $finance['amount'],
							'amount_before' => $amount_before,
							'description' => $log_deposit_withdraw['description']." | เปลี่ยนสถานะเป็นไม่อนุมัติ ".$error_message,
						]);
								$this->cache->file->delete('process_withdraw_'.$id);}
					echo json_encode([
						'message' => 'ทำรายการไม่สำเร็จ '.$error_message,
						'error' => true
					]);
					exit();
				}

			}else{
				$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
					'id' => $log_deposit_withdraw_id
				]);
				if($log_deposit_withdraw!=""){
					$status_text = " รอตรวจสอบ";
					if($post['status'] == "1"){
						$status_text = " อนุมัติ (ถอนออโต้)";
					}else if($post['status'] == "3"){
						$status_text = " อนุมัติ (ถอนมือ)";
					}
					$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
						'id' => $log_deposit_withdraw_id,
						'account' => $finance['account'],
						'amount' => $finance['amount'],
						'amount_before' => $amount_before,
						'description' => $log_deposit_withdraw['description']." | เปลี่ยนสถานะเป็น".$status_text,
					]);
				}
			}
			$user_admin_withdraw = $this->User_model->user_find([
				'id' => $_SESSION['user']['id']
			]);
			$this->Finance_model->finance_update([
				'id' => $id,
				'status' => $post['status'],
				'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
				'manage_by_fullname' => $_SESSION['user']['username'].' - '.$user_admin_withdraw['full_name'],
				'manage_by' => $_SESSION['user']['id']
			]);

			if($post['status']=="1"){

				if($member != ""){

					//ตรวจธนาคาร
					$bank_list = array(
						'01' => 'bbl',
						'02' => 'kbank',
						'03' => 'ktb',
						'04' => 'tmb',
						'05' => 'scb',
						'06' => 'bay',
						'07' => 'gsb',
						'08' => 'tbank',
						'09' => 'baac',
						'1' => 'bbl',
						'2' => 'kbank',
						'3' => 'ktb',
						'4' => 'tmb',
						'5' => 'scb',
						'6' => 'bay',
						'7' => 'gsb',
						'8' => 'tbank',
						'9' => 'baac',
					);
					if(array_key_exists($finance['bank'],$bank_list)){
						$bank_data_list = $this->Bank_model->bank_data_list();
						$chk_match_bank = false;
						$bank_code = "";
						for($i =0;$i<count($bank_data_list);$i++){
							if(strtoupper($bank_list[$finance['bank']]) == strtoupper($bank_data_list[$i]['code_en'])){
								$bank_code = $bank_data_list[$i]['bank_code'];
								$chk_match_bank = true;
								break;
							}
						}

						if($chk_match_bank){
							try{

								$bank_can_withdraws = $this->Bank_model->bank_list(['status'=>1,'status_withdraw' => 1,'api_type' => 1]);
								$bank_can_withdraw_once = null;
								$chk_bank_can_withdraw = false;
								foreach($bank_can_withdraws as $bank_can_withdraw){
									if(isset($post['bank_id_withdraw']) && !empty($post['bank_id_withdraw']) && $post['bank_id_withdraw'] == $bank_can_withdraw['id'] && ($bank_can_withdraw['bank_code'] == "05" || $bank_can_withdraw['bank_code'] == "5")){
										$bank_can_withdraw_once = $bank_can_withdraw;
										$chk_bank_can_withdraw = true;
										break;
									}else if(isset($post['bank_id_withdraw']) && !empty($post['bank_id_withdraw']) && $post['bank_id_withdraw'] == $bank_can_withdraw['id'] && ($bank_can_withdraw['bank_code'] == "02" || $bank_can_withdraw['bank_code'] == "2")){
										$bank_can_withdraw_once = $bank_can_withdraw;
										$chk_bank_can_withdraw = true;
										break;
									}else if(isset($post['bank_id_withdraw']) && !empty($post['bank_id_withdraw']) && $post['bank_id_withdraw'] == $bank_can_withdraw['id'] && ($bank_can_withdraw['bank_code'] == "06" || $bank_can_withdraw['bank_code'] == "6")){
										$bank_can_withdraw_once = $bank_can_withdraw;
										$chk_bank_can_withdraw = true;
										break;
									}else if(empty($post['bank_id_withdraw']) && (($bank_can_withdraw['bank_code'] == "02" || $bank_can_withdraw['bank_code'] == "2") ||  ($bank_can_withdraw['bank_code'] == "05" || $bank_can_withdraw['bank_code'] == "5")||  ($bank_can_withdraw['bank_code'] == "06" || $bank_can_withdraw['bank_code'] == "6"))){
										$bank_can_withdraw_once = $bank_can_withdraw;
										$chk_bank_can_withdraw = true;
										break;
									}
								}

								if($chk_bank_can_withdraw){
									if(
										!isset($bank_can_withdraw_once['max_amount_withdraw_auto']) ||
										(
											isset($bank_can_withdraw_once['max_amount_withdraw_auto']) &&
											(float)$finance['amount'] > (float)$bank_can_withdraw_once['max_amount_withdraw_auto']
										)
									){
										$this->Finance_model->finance_update([
											'id' => $id,
											'status' => $finance['status']
										]);
										$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
											'id' => $log_deposit_withdraw_id
										]);
										if($log_deposit_withdraw!=""){
											$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
												'id' => $log_deposit_withdraw_id,
												'description' => $log_deposit_withdraw['description']." | ทำรายการไม่สำเร็จ, จำนวนเงินถอนออโต้ได้ไม่เกิน ( ".number_format($bank_can_withdraw_once['max_amount_withdraw_auto'],2)." บาท/ครั้ง) | ยอดถอน ".number_format($finance['amount'],2),
											]);
													$this->cache->file->delete('process_withdraw_'.$id);}
										echo json_encode([
											'message' => 'ทำรายการไม่สำเร็จ จำนวนเงินถอนออโต้ได้ไม่เกิน ( '.number_format($bank_can_withdraw_once["max_amount_withdraw_auto"],2).' บาท/ครั้ง)',
											'error' => true
										]);
									}else{
										$chk_cache_process_withdraw = $this->cache->file->get('process_withdraw');
										if($chk_cache_process_withdraw  !== FALSE){
											$this->Finance_model->finance_update([
												'id' => $id,
												'manage_by' => $_SESSION['user']['id'],
												'status' => $finance['status']
											]);
											$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
												'id' => $log_deposit_withdraw_id
											]);
											$message_error = "มีบางรายการกำลังดำเนินการถอนออโต้อยู่, กรุณาลองใหม่อีกครั้ง";
											if($log_deposit_withdraw!=""){
												$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
													'id' => $log_deposit_withdraw_id,
													'description' => $log_deposit_withdraw['description']." | ทำรายการไม่สำเร็จ ".$message_error,
												]);
														$this->cache->file->delete('process_withdraw_'.$id);}
											echo json_encode([
												'message' => 'ทำรายการไม่สำเร็จ '.$message_error,
												'error' => true
											]);
											exit();
										}
										$this->cache->file->save('process_withdraw',date('Y-m-d H:i:s'), 50);
										if($bank_can_withdraw_once['bank_code'] == "05" || $bank_can_withdraw_once['bank_code'] == "5"){
											$res_withdraw = $this->auto_withdraw_librarie->transfer($member['username'],$finance['bank_number'],$bank_code,$finance['amount'],decrypt(base64_decode($bank_can_withdraw_once['api_token_1']),$this->config->item('secret_key_salt')),decrypt(base64_decode($bank_can_withdraw_once['api_token_2']),$this->config->item('secret_key_salt')),$bank_can_withdraw_once['bank_number']);
										}else if($bank_can_withdraw_once['bank_code'] == "02" || $bank_can_withdraw_once['bank_code'] == "2"){
											$bank_code = getBankCodeForKbank()[$bank_code];
											$res_withdraw = $this->auto_withdraw_librarie->transfer_kplus($member['username'],$finance['bank_number'],$bank_code,$finance['amount'],decrypt(base64_decode($bank_can_withdraw_once['api_token_1']),$this->config->item('secret_key_salt')),decrypt(base64_decode($bank_can_withdraw_once['api_token_2']),$this->config->item('secret_key_salt')),$bank_can_withdraw_once['bank_number']);
										}else if($bank_can_withdraw_once['bank_code'] == "06" || $bank_can_withdraw_once['bank_code'] == "6"){
											$bank_code = getBankCodeForKrungsri()[$bank_code];
											$res_withdraw = $this->auto_withdraw_librarie->transfer_kma($member['username'],$finance['bank_number'],$bank_code,$finance['amount'],decrypt(base64_decode($bank_can_withdraw_once['api_token_1']),$this->config->item('secret_key_salt')),decrypt(base64_decode($bank_can_withdraw_once['api_token_2']),$this->config->item('secret_key_salt')),$bank_can_withdraw_once['bank_number']);
										}
										if($res_withdraw['status']){
											$this->cache->file->delete('process_withdraw');
											$qrcode = null;
											if(($bank_can_withdraw_once['bank_code'] == "05" || $bank_can_withdraw_once['bank_code'] == "5") && isset($res_withdraw['msg']['data']) && isset($res_withdraw['msg']['data']['additionalMetaData']) && isset($res_withdraw['msg']['data']['additionalMetaData']['paymentInfo']) && is_array($res_withdraw['msg']['data']['additionalMetaData']['paymentInfo']) && count($res_withdraw['msg']['data']['additionalMetaData']['paymentInfo']) > 0){
												foreach ($res_withdraw['msg']['data']['additionalMetaData']['paymentInfo'] as $paymentInfo){
													if(is_null($qrcode) && isset($paymentInfo['QRstring']) && !empty($paymentInfo['QRstring'])){
														$qrcode = $paymentInfo['QRstring'];
													}
												}
											}else if(($bank_can_withdraw_once['bank_code'] == "02" || $bank_can_withdraw_once['bank_code'] == "2")  && isset($res_withdraw['msg']['rawQr']) && !empty($res_withdraw['msg']['rawQr'])){
												$qrcode = $res_withdraw['msg']['rawQr'];
											}else if(($bank_can_withdraw_once['bank_code'] == "06" || $bank_can_withdraw_once['bank_code'] == "6" )  && isset($res_withdraw['msg']['QRCimagevalue'])){
												$qrcode = $res_withdraw['msg']['QRCimagevalue'];
											}
											$this->Finance_model->finance_update([
												'id' => $id,
												'qrcode' => $qrcode
											]);
											$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
												'id' => $log_deposit_withdraw_id
											]);
											if($log_deposit_withdraw!=""){
												$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
													'id' => $log_deposit_withdraw_id,
													'description' => $log_deposit_withdraw['description']." | ทำรายการสำเร็จ Finance ID #".$finance['id'],
												]);
											}

											//บันทึก line notify job
											date_default_timezone_set('Asia/Bangkok');
											$account_admin = $this->Account_model->account_find([
												'id' => $_SESSION['user']['id']
											]);
											$log_line_notify_id = $this->Log_line_notify_model->log_line_notify_create([
												'type' => 2,
												'message' => "ยอดถอน ".number_format($finance['amount'],2)." บาท ยูส ".$member['username']." เวลา ".date('Y-m-d H:i:s')." ถอนโดย ".$account_admin['full_name'],
											]);
			$this->cache->file->delete('process_withdraw_'.$id);
											echo json_encode([
												'message' => 'ทำรายการสำเร็จ',
												'result' => true,
											]);
										}else{
											$this->cache->file->delete('process_withdraw');
											$this->Finance_model->finance_update([
												'id' => $id,
												'status' => $finance['status']
											]);

											$sum_amount_list = $this->Finance_model->sum_amount_deposit_and_withdraw(['account_list' => [$member['id']]]);
											if(array_key_exists($member['id'],$sum_amount_list)){
												$this->User_model->user_update(['sum_amount'=>$sum_amount_list[$member['id']]['sum_amount'],'id'=> $member['id']]);
											}

											$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
												'id' => $log_deposit_withdraw_id
											]);
											$message_error = isset($res_withdraw['msg']) ? $res_withdraw['msg'] : "";
											if(is_null($message_error) || empty(trim($message_error))){
												$message_error = " กรุณาตรวจสอบยอดถอนจำนวน ".number_format($finance['amount'],2)." บาท บน Internet Banking/Mobile App ว่าถูกถอนไปจริงหรือไม่";
											}
											if($log_deposit_withdraw!=""){
												$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
													'id' => $log_deposit_withdraw_id,
													'description' => $log_deposit_withdraw['description']." | ทำรายการไม่สำเร็จ ".$message_error,
												]);
														$this->cache->file->delete('process_withdraw_'.$id);}
											echo json_encode([
												'message' => 'ทำรายการไม่สำเร็จ '.$message_error,
												'error' => true
											]);
										}
									}

								}else{
									$this->Finance_model->finance_update([
										'id' => $id,
										'status' => $finance['status']
									]);
									$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
										'id' => $log_deposit_withdraw_id
									]);
									if($log_deposit_withdraw!=""){
										$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
											'id' => $log_deposit_withdraw_id,
											'description' => $log_deposit_withdraw['description']." | ทำรายการไม่สำเร็จ ไม่มีธนาคารสำหรับถอนออโต้เปิดใช้งาน (SCB เท่านั้น)",
										]);
												$this->cache->file->delete('process_withdraw_'.$id);}
									echo json_encode([
										'message' => 'ทำรายการไม่สำเร็จ ไม่มีธนาคารสำหรับถอนออโต้เปิดใช้งาน (SCB เท่านั้น)',
										'error' => true
									]);
								}
							}catch (Exception $ex){
								$this->Finance_model->finance_update([
									'id' => $id,
									'status' => $finance['status']
								]);
								$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
									'id' => $log_deposit_withdraw_id
								]);
								if($log_deposit_withdraw!=""){
									$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
										'id' => $log_deposit_withdraw_id,
										'description' => $log_deposit_withdraw['description']." | ทำรายการไม่สำเร็จ Something...",
									]);
											$this->cache->file->delete('process_withdraw_'.$id);}
								echo json_encode([
									'message' => 'ทำรายการไม่สำเร็จ Something...',
									'error' => true
								]);
							}

						}else{
							$this->Finance_model->finance_update([
								'id' => $id,
								'status' => $finance['status']
							]);
							$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
								'id' => $log_deposit_withdraw_id
							]);
							if($log_deposit_withdraw!=""){
								$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
									'id' => $log_deposit_withdraw_id,
									'description' => $log_deposit_withdraw['description']." | ทำรายการไม่สำเร็จ ไม่พบธนาคารสมาชิกในระบบ#1",
								]);
										$this->cache->file->delete('process_withdraw_'.$id);}
							echo json_encode([
								'message' => 'ทำรายการไม่สำเร็จ ไม่พบธนาคารสมาชิกในระบบ#1',
								'error' => true
							]);
						}
					}else{
						$this->Finance_model->finance_update([
							'id' => $id,
							'status' => $finance['status']
						]);
						$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
							'id' => $log_deposit_withdraw_id
						]);
						if($log_deposit_withdraw!=""){
							$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
								'id' => $log_deposit_withdraw_id,
								'description' => $log_deposit_withdraw['description']." | ทำรายการไม่สำเร็จ ไม่พบธนาคารสมาชิกในระบบ#2",
							]);
									$this->cache->file->delete('process_withdraw_'.$id);}
						echo json_encode([
							'message' => 'ทำรายการไม่สำเร็จ ไม่พบธนาคารสมาชิกในระบบ#2',
							'error' => true
						]);
					}
				}else{
					$this->Finance_model->finance_update([
						'id' => $id,
						'status' => $finance['status']
					]);

					$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
						'id' => $log_deposit_withdraw_id
					]);
					if($log_deposit_withdraw!=""){
						$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
							'id' => $log_deposit_withdraw_id,
							'description' => $log_deposit_withdraw['description']." | ทำรายการไม่สำเร็จ ไม่พบสมาชิกในระบบ",
						]);
								$this->cache->file->delete('process_withdraw_'.$id);}
					echo json_encode([
						'message' => 'ทำรายการไม่สำเร็จ ไม่พบสมาชิกในระบบ',
						'error' => true
					]);
				}
			}else{
				$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
					'id' => $log_deposit_withdraw_id
				]);
				if($log_deposit_withdraw!=""){
					$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
						'id' => $log_deposit_withdraw_id,
						'description' => $log_deposit_withdraw['description']." | ทำรายการสำเร็จ",
					]);
				}

				if($post['status'] == "3"){
					//บันทึก line notify job
					date_default_timezone_set('Asia/Bangkok');
					$account_admin = $this->Account_model->account_find([
						'id' => $_SESSION['user']['id']
					]);
					$log_line_notify_id = $this->Log_line_notify_model->log_line_notify_create([
						'type' => 2,
						'message' => "ยอดถอน ".number_format($finance['amount'],2)." บาท ยูส ".$member['username']." เวลา ".date('Y-m-d H:i:s')." ถอนโดย ".$account_admin['full_name'],
					]);

					$sum_amount_list = $this->Finance_model->sum_amount_deposit_and_withdraw(['account_list' => [$member['id']]]);
					if(array_key_exists($member['id'],$sum_amount_list)){
						$this->User_model->user_update(['sum_amount'=>$sum_amount_list[$member['id']]['sum_amount'],'id'=> $member['id']]);
					}

				}
			$this->cache->file->delete('process_withdraw_'.$id);
				echo json_encode([
					'message' => 'ทำรายการสำเร็จ',
					'result' => true
				]);
			}

		} else {

			$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
				'id' => $log_deposit_withdraw_id
			]);
			if($log_deposit_withdraw!=""){
				$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
					'id' => $log_deposit_withdraw_id,
					'description' => $log_deposit_withdraw['description']." | ทำรายการไม่สำเร็จไม่พบ Finance ID",
				]);
			}
			$this->cache->file->delete('process_withdraw_'.$id);
			echo json_encode([
				'message' => 'ทำรายการไม่สำเร็จ',
				'error' => true
			]);
		}
	}

	public function withdraw_credit()
	{
		check_parameter([
			'account_id',
			'amount',
		], 'POST');
		$post = $this->input->post();
		$user = $this->User_model->user_find([
			'id' => $post['account_id']
		]);
		if ($user=="") {
			echo json_encode([
				'message' => 'ไม่พบข้อมูล Username นี้',
				'error' => true
			]);
			exit();
		}

		$credit_before = $this->remaining_credit($user);
		if ($post['amount']>$credit_before) {
			echo json_encode([
				'message' => "ยอดเงินคงเหลือไม่เพียงพอ : ".number_format($credit_before,2)." บาท",
				'error' => true
			]);
			exit();
		}

		/*$turnover_current = (float)$this->check_turn_before($user);
		if($turnover_current > (float)$user['turn_before']){
			$check_turn = $turnover_current-(float)$user['turn_before'];
		}else{
			$check_turn = (float)$user['turn_before'] - $turnover_current;
		}
		if ($check_turn < $user['turn_over']) {
			$turn_over = $user['turn_over']-$check_turn;
			echo json_encode([
				'message' => "เทิร์นคงเหลือ {$check_turn}/{$user['turn_over']}",
				'error' => true
			]);
			exit();
		}*/

		$turn_type = $this->Setting_model->setting_find([
			'name' => 'turn_type'
		]);
		$turn_type = $turn_type != "" ? $turn_type['value'] : 1;
		if($turn_type == "2"){
			$turnover_user = is_numeric($user['turn_over']) ? (float)$user['turn_over'] : 0.00;
			if ($credit_before<$turnover_user) {
				echo json_encode([
					'message' => "ยอดเครดิตคงเหลือต้องมากกว่าหรือเท่ากับ ".number_format($turnover_user, 2).' ฿',
					'error' => true
				]);
				exit();
			}
		}else{
			$chk_turn_all_pass = false;
			$text_error_turn_all = "";
			$turnover_data = $this->check_turn_before($user);
			$chk_take_turn_finish = 0;
			$chk_take_turn_finish_html = "";
			$chk_take_turn_finish_html_new = "";
			$chk_turn_zero_all = false;
			foreach (game_code_list() as $game_code) {
				if (
					!$chk_turn_zero_all &&
					(is_numeric($user['turn_over_' . strtolower($game_code)]) && (float)$user['turn_over_' . strtolower($game_code)] >= 1)
				) {
					$chk_turn_zero_all = true;
				}
			}
			if ($chk_turn_zero_all) {
				foreach (game_code_list() as $game_code) {
					if (!$chk_turn_all_pass) {
						if (isset($turnover_data[$game_code]) && isset($user['turn_over_' . strtolower($game_code)]) && (float)$user['turn_over_' . strtolower($game_code)] >= 0) {
							if($turn_type == "2"){
								$turnover_current = $credit_before;
							}else{
								$turnover_current = $turnover_data[$game_code]['amount'];
							}
							if ($turnover_current > (float)$user['turn_before_' . strtolower($game_code)]) {
								$check_turn = $turnover_current - (float)$user['turn_before_' . strtolower($game_code)];
							} else {
								$check_turn = 0;
							}
							if ($check_turn < $user['turn_over_' . strtolower($game_code)]) {

								$text_error_turn_all .= "<p class='mb-0'>เทิร์น " . game_code_text_list()[$game_code] . " คงเหลือ {$check_turn}/{$user['turn_over_' . strtolower($game_code)]}</p>";
								if ($check_turn  > 0 && $check_turn > $chk_take_turn_finish) {
									$chk_take_turn_finish = $check_turn;
									$chk_take_turn_finish_html = "<p class='mb-0'>เทิร์น " . game_code_text_list()[$game_code] . " คงเหลือ {$check_turn}/{$user['turn_over_' . strtolower($game_code)]}</p>";
									$chk_take_turn_finish_html_new = "<p class='mb-0 font-weight-bold text-success'>เทิร์น " . game_code_text_list()[$game_code] . " คงเหลือ {$check_turn}/{$user['turn_over_' . strtolower($game_code)]}</p>";
								}
								/*$turn_over = $user['turn_over']-$check_turn;
								echo json_encode([
									'message' => "เทิร์นคงเหลือ {$check_turn}/{$user['turn_over']}",
									'error' => true
								]);
								exit();*/
							} else {
								$turn_game_code = 'turn_over_' . strtolower($game_code);
								$chk_turn_all_pass = true;
							}
						}
					}
				}
			}

			if (!$chk_turn_all_pass && $chk_turn_zero_all) {
				echo json_encode([
					//'message' => "เทิร์นคงเหลือ {$check_turn}/{$user['turn_over']}",
					'message' => "<h5 class='text-danger font-weight-bold'>** เพียงทำเทิร์นให้ครบบางอย่างเท่านั้น</h5>" . str_replace($chk_take_turn_finish_html, $chk_take_turn_finish_html_new, $text_error_turn_all),
					'error' => true
				]);
				exit();
			}
		}


		if ($post['amount']>$credit_before) {
			echo json_encode([
				'message' => "ยอดเงินคงเหลือไม่เพียงพอ ".number_format($credit_before, 2).' ฿',
				'error' => true
			]);
			exit();
		}
		$amount_deposit = $_POST['amount'];
		$form_data = [];
		$form_data["account_agent_username"] = $user['account_agent_username'];
		$form_data["amount"] = $amount_deposit;
		$form_data = member_credit_data($form_data);

		//เพิ่ม Logs
		$transaction_message = !isset($post['transaction']) || (isset($post['transaction']) && $post['transaction'] == "Y") ? " (บันทึกลง Transaction + ปรับลด Credit)" : " (ปรับลด Credit อย่างเดียว)";
		$log_deposit_withdraw_id = $this->Log_deposit_withdraw_model->log_deposit_withdraw_create([
			'account' => $user['id'],
			'username' => $user['username'],
			'amount' => $form_data["amount"],
			'amount_before' => $credit_before,
			'type' => '2', //ถอน
			'description' => 'ลดเครดิต'.$transaction_message,
			'admin' =>$_SESSION['user']['id'],
		]);

		$response = $this->game_api_librarie->withdraw($form_data);
		if (isset($response['ref'])) {

			if(
				!isset($post['transaction']) || (isset($post['transaction']) && $post['transaction'] == "Y")
			){
				if($credit_before >= (float)$user['turn_over']){
					$this->Account_model->account_update([
						'id' => $user['id'],
						'turn_over' => 0,
						'sha1_acount' => '',
					]);
				}

				$this->Finance_model->finance_create([
					'account' => $user['id'],
					'amount' => $post['amount'],
					'bank' => $user['bank'],
					'bank_number' => $user['bank_number'],
					'bank_name' => $user['bank_name'],
					'username' => $user['username'],
					'type' => 2
				]);
			}

			$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
				'id' => $log_deposit_withdraw_id
			]);
			if($log_deposit_withdraw!=""){
				$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
					'id' => $log_deposit_withdraw_id,
					'description' => $log_deposit_withdraw['description']." | ทำรายการสำเร็จ",
				]);
			}

			echo json_encode([
				'message' => 'success',
				'result' => true
			]);
		} else {

			$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
				'id' => $log_deposit_withdraw_id
			]);
			if($log_deposit_withdraw!=""){
				$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
					'id' => $log_deposit_withdraw_id,
					'description' => $log_deposit_withdraw['description']." | ทำรายการไม่สำเร็จ",
				]);
			}

			echo json_encode([
				'message' => 'ทำรายการไม่สำเร็จ',
				'error' => true,
			]);
		}
	}

	public function check_turn_before($user)
	{
		$form_data = [];
		$form_data['username'] = $user['account_agent_username'];
		if ($user['turn_date']!="") {
			$date = new DateTime($user['turn_date']);
			$form_data['date_begin'] = $date->format('Y-m-d');
		}
		$form_data = member_turn_data($form_data);
		$turnover_amount = $this->game_api_librarie->getTurn($form_data);
		return $turnover_amount;
	}

	private function remaining_credit($user)
	{
		header('Content-Type: application/json');
		try {
			$balance_credit = $this->game_api_librarie->balanceCredit($user);
			return $balance_credit;
		} catch (\Exception $e) {
			echo json_encode([
				'message' => 'ทำรายการไม่สำเร็จ',
				'error' => true
			]);
			exit();
		}
	}

	public function withdraw_list_page_manage_transaction($account_id)
    {
        $get = $this->input->get();
        $search = $get['search']['value'];
        // $dir = $get['order'][0]['dir'];//order
        $per_page = $get['length'];//จำนวนที่แสดงต่อ 1 หน้า
        $page = $get['start'];
        $search_data = [
         'per_page' => $per_page,//left
         'page' => $page,
         'type' => 2
        ];
        if ($search!="") {
            $search_data['search'] = $search;
        }
		if(isset($get['date_start']) && isset($get['date_end'])){
			$search_data['date_start'] = $get['date_start'];
			$search_data['date_end'] = $get['date_end'];
		}
		if(isset($get['status']) && $get['status'] !== ""){
			$search_data['status'] = $get['status'];
		}
		$search_data['account'] = $account_id;
        $withdraw_count_all = $this->Finance_model->finance_count([
        	'type' => 2,
			'account' => $account_id
		]);
        $withdraw_count_search = $this->Finance_model->finance_count($search_data);
        $data = $this->Finance_model->finance_list_page($search_data);
        echo json_encode([
         "draw" => intval($get['draw']),
         "recordsTotal" => intval($withdraw_count_all),
         "recordsFiltered" => intval($withdraw_count_search),
         "data" => $data,
       ]);
    }
}
