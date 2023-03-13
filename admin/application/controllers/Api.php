<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'../vendor/autoload.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

class Api extends CI_Controller
{
	public function __construct()
	{
		date_default_timezone_set('Asia/Bangkok');
		parent::__construct();
	}
	public function index()
	{
		exit();
	}

	public function bank_list()
	{
		if(!isset($_GET['api_token']) && !$_GET['api_token'] == "rs2nvxdjJLaBr5eXXZddTshDsM4T7Tw34MXLJNWN"){
			return;
		}
		$data = $this->Bank_model->bank_list($_GET);
		if(!empty($data)){
			foreach($data as $index => $bank){
				unset($bank['username']);
				unset($bank['password']);
				unset($bank['api_token_1']);
				unset($bank['api_token_2']);
				unset($bank['api_token_3']);
				$data[$index] = $bank;
			}

		}
		$key_bank_number = [];
		$data_new = [];
		if(isset($_GET['group_by']) && $_GET['group_by'] == "bank_number"){
			foreach($data as $index => $bank){
				if(!array_key_exists($bank['bank_number'],$key_bank_number)){
					$key_bank_number[$bank['bank_number']] = $bank['bank_number'];
					$data_new[] = $bank;
				}
			}
		}else{
			$data_new = $data;
		}
		header("Content-Type: application/json");
		echo json_encode([
			'message' => 'success',
			'result' => $data_new
		]);
	}

	public function statement_list()
	{
		if(!isset($_GET['api_token']) && !$_GET['api_token'] == "rs2nvxdjJLaBr5eXXZddTshDsM4T7Tw34MXLJNWN"){
			return;
		}
		$get = $this->input->get();

		$search = $get['search']['value'];
		// $dir = $get['order'][0]['dir'];//order
		$per_page = $get['length'];//จำนวนที่แสดงต่อ 1 หน้า
		$page = $get['start'];
		$search_data = [
			'per_page' => $per_page,//left
			'page' => $page,//start,right
		];
		if ($search!="") {
			$search_data['search'] = $search;
		}
		$username = preg_replace('/[^0-9]+/', '', $get['search']['value']);
		if(!empty(trim($username)) && strlen(trim($username)) >= 10){
			$search_data['username'] = $username;
		}
		if(isset($get['date_start']) && isset($get['date_end'])){
			$search_data['start_created_at'] = $get['date_start'];
			$search_data['end_created_at'] = $get['date_end'];
		}
		if(isset($get['config_api_id']) && !empty($get['config_api_id'])){
			$search_data['config_api_id'] = $get['config_api_id'];
		}
		if(isset($get['bank_number']) && !empty($get['bank_number'])){
			$search_data['bank_number'] = $get['bank_number'];
		}
		if(isset($get['type_deposit_withdraw']) && !empty($get['type_deposit_withdraw'])){
			$search_data['type_deposit_withdraw'] = $get['type_deposit_withdraw'];
		}
		$search_data_all = [
			'start_created_at'=> $get['date_start'],
			'end_created_at'=> $get['date_end'],
		];
		if(isset($get['bank_number']) && !empty($get['bank_number'])){
			$search_data_all['bank_number'] = $get['bank_number'];
		}
		$statement_count_all = $this->Report_sms_model->report_sms_count($search_data_all);
		$statement_count_search = $this->Report_sms_model->report_sms_count($search_data);
		$data = $this->Report_sms_model->report_sms_list_page($search_data);
		$credit_history_id_list = [];
		foreach($data as $index => $report){
			if(!empty($report['deposit_withdraw_id']) && !is_null($report['deposit_withdraw_id'])){
				$credit_history_id_list[] = $report['deposit_withdraw_id'];
			}
		}
		$credit_history_list = count($credit_history_id_list) > 0 ? $this->Credit_model->credit_list(['id_list'=> $credit_history_id_list])  : [];
		$credit_history_username = [];
		foreach ($credit_history_list as $credit_history){
			$credit_history_username[$credit_history['id']] = $credit_history;
		}
		$bank_list = $this->Bank_model->bank_list([
			//'status' => '1'
		]);
		$bank_list_data = [];
		foreach ($bank_list as $bank){
			$bank_list_data[$bank['id']] = [
				'bank_bank_code' => $bank['bank_code'],
				'bank_account_name' => $bank['account_name'],
				'bank_bank_number' => $bank['bank_number'],
			];
		}
		foreach($data as $index => $report){
			if(!empty($report['deposit_withdraw_id']) && !is_null($report['deposit_withdraw_id']) && array_key_exists($report['deposit_withdraw_id'],$credit_history_username)){
				$data[$index]['username'] = $credit_history_username[$report['deposit_withdraw_id']]['username'];
			}else{
				$data[$index]['username'] = null;
			}
			if(array_key_exists($report['config_api_id'],$bank_list_data)){
				$data[$index]['bank_bank_code'] = $bank_list_data[$report['config_api_id']]['bank_bank_code'];
				$data[$index]['bank_account_name'] = $bank_list_data[$report['config_api_id']]['bank_account_name'];
				$data[$index]['bank_bank_number'] = $bank_list_data[$report['config_api_id']]['bank_bank_number'];
			}else{
				$data[$index]['bank_bank_code'] = "";
				$data[$index]['bank_account_name'] = "";
				$data[$index]['bank_bank_number'] = "";
			}
		}
		header("Content-Type: application/json");
		echo json_encode([
			"draw" => intval($get['draw']),
			"recordsTotal" => intval($statement_count_all),
			"recordsFiltered" => intval($statement_count_search),
			"data" => $data,
		]);
	}

	public function bank_auto_transfer_sfo4rsdf()
	{
		$ip_server = $_SERVER['SERVER_ADDR'];
		$ip_client = isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address();
		if(isset($_GET['api_token'])&& $_GET['api_token'] == "rs2nvxdjJLaBr5eXXZddTshDsM4T7Tw34MXLJNWN" && $ip_server == $ip_client){
			$bank_can_autos = $this->Bank_model->bank_list(['status'=>1,'security_api' => true,'auto_transfer' => 1,'api_type' => 1,'bank_code_list'=>["05","5","02","2","06","6","11"]]);
			foreach ($bank_can_autos as $bank_can_auto){
				$chk_cache_process_withdraw_chk = $this->cache->file->get('process_auto_transfer_'.$bank_can_auto['id']);
				if($chk_cache_process_withdraw_chk  === FALSE){
					$this->cache->file->save('process_auto_transfer_'.$bank_can_auto['id'],true, 65);
					try{
						if(
							is_numeric($bank_can_auto['auto_min_amount_transfer']) && is_numeric($bank_can_auto['balance']) && (float)$bank_can_auto['auto_min_amount_transfer'] > 0.00  && (float)$bank_can_auto['balance'] > 0.00 && (float)$bank_can_auto['balance'] >= (float)$bank_can_auto['auto_min_amount_transfer'] &&
							!empty($bank_can_auto['auto_transfer_bank_code']) &&
							!empty($bank_can_auto['auto_transfer_bank_acc_name']) &&
							!empty($bank_can_auto['auto_transfer_bank_number']) && strlen($bank_can_auto['auto_transfer_bank_number'])  >= 10 && is_numeric($bank_can_auto['auto_transfer_bank_number'])
						){
							//เพิ่ม Logs
							$log_transfer_out_id = $this->Log_transfer_out_model->log_transfer_out_create([
								'bank_id' => $bank_can_auto['id'],
								'amount' => str_replace(",","",$bank_can_auto['auto_min_amount_transfer']),
								'admin' => 0,
								'bank' => $bank_can_auto["bank_code"],
								'bank_number' => $bank_can_auto['bank_number'],
								'bank_acc_name' => $bank_can_auto['account_name'],
								'bank_to' => $bank_can_auto["auto_transfer_bank_code"],
								'bank_acc_name_to' => $bank_can_auto['auto_transfer_bank_acc_name'],
								'bank_number_to' => $bank_can_auto['auto_transfer_bank_number'],
								'description' => "ถอนเงิน",
							]);
							$res_withdraw = [];
							if($bank_can_auto['bank_code'] == "05" || $bank_can_auto['bank_code'] == "5"){
								$res_withdraw = $this->auto_withdraw_librarie->transfer("0000000000",$bank_can_auto['auto_transfer_bank_number'],$bank_can_auto['auto_transfer_bank_code'],$bank_can_auto['auto_min_amount_transfer'],decrypt(base64_decode($bank_can_auto['api_token_1']),$this->config->item('secret_key_salt')),decrypt(base64_decode($bank_can_auto['api_token_2']),$this->config->item('secret_key_salt')),$bank_can_auto['bank_number']);
							}else if($bank_can_auto['bank_code'] == "02" || $bank_can_auto['bank_code'] == "2"){
								$bank_code = getBankCodeForKbank()[$bank_can_auto['auto_transfer_bank_code']];
								$res_withdraw = $this->auto_withdraw_librarie->transfer_kplus("0000000000",$bank_can_auto['auto_transfer_bank_number'],$bank_code,$bank_can_auto['auto_min_amount_transfer'],decrypt(base64_decode($bank_can_auto['api_token_1']),$this->config->item('secret_key_salt')),decrypt(base64_decode($bank_can_auto['api_token_2']),$this->config->item('secret_key_salt')),$bank_can_auto['bank_number']);
							}else if($bank_can_auto['bank_code'] == "06" || $bank_can_auto['bank_code'] == "6"){
								$bank_code = getBankCodeForKrungsri()[$bank_can_auto['auto_transfer_bank_code']];
								$res_withdraw = $this->auto_withdraw_librarie->transfer_kma("0000000000",$bank_can_auto['auto_transfer_bank_number'],$bank_code,$bank_can_auto['auto_min_amount_transfer'],decrypt(base64_decode($bank_can_auto['api_token_1']),$this->config->item('secret_key_salt')),decrypt(base64_decode($bank_can_auto['api_token_2']),$this->config->item('secret_key_salt')),$bank_can_auto['bank_number']);
							}else if($bank_can_auto['bank_code'] == "11"){
								$res_withdraw = $this->auto_withdraw_librarie->transfer_kkp("0000000000",$bank_can_auto['bank_number'],$bank_can_auto['auto_transfer_bank_code'],$bank_can_auto['auto_min_amount_transfer'],decrypt(base64_decode($bank_can_auto['api_token_1']),$this->config->item('secret_key_salt')),decrypt(base64_decode($bank_can_auto['api_token_2']),$this->config->item('secret_key_salt')),$bank_can_auto['bank_number']);
							}
							if(!empty($res_withdraw)){
								if($res_withdraw['status']){
									$log_transfer_out = $this->Log_transfer_out_model->log_transfer_out_find([
										'id' => $log_transfer_out_id
									]);
									if($log_transfer_out!=""){
										$this->Log_transfer_out_model->log_transfer_out_update([
											'id' => $log_transfer_out_id,
											'status' => 1,
											'description' => $log_transfer_out['description']." | ทำรายการสำเร็จ Log ID #".$log_transfer_out_id." | ".json_encode($res_withdraw,JSON_UNESCAPED_UNICODE),
										]);
									}
								}else{
									$log_transfer_out = $this->Log_transfer_out_model->log_transfer_out_find([
										'id' => $log_transfer_out_id
									]);
									if($log_transfer_out!=""){
										$this->Log_transfer_out_model->log_transfer_out_update([
											'id' => $log_transfer_out_id,
											'status' => 2,
											'description' => $log_transfer_out['description']." | ทำรายการไม่สำเร็จ | ".json_encode($res_withdraw,JSON_UNESCAPED_UNICODE),
										]);
									}
								}
							}
						}
						//$this->cache->file->delete('process_auto_transfer_'.$bank_can_auto['id']);
					}catch (Exception $ex){
						//$this->cache->file->delete('process_auto_transfer_'.$bank_can_auto['id']);
					}
				}else{
					//$this->cache->file->delete('process_auto_transfer_'.$bank_can_auto['id']);
				}
			}
		}
		echo json_encode(['success'=>true,"message"=>"Process..."]);
		exit();
	}
	public function finance_auto_withdraw_sfo4rsdf()
	{
		set_time_limit(100); //because cloudflare timeout if > 100 sec
		$response = [
			'status' => false,
			'id' => 'EMPTY',
		];
		$ip_server = $_SERVER['SERVER_ADDR'];
		$ip_client = isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address();
		if(isset($_GET['api_token'])&& $_GET['api_token'] == "rs2nvxdjJLaBr5eXXZddTshDsM4T7Tw34MXLJNWN"  && $ip_server == $ip_client){
			try{
				$finance_auto_withdraw = $this->Finance_model->finance_for_auto_withdraw_find([
					'type' => '2',
					'status' => 0,
					'is_auto_withdraw' => 1,
					'auto_withdraw_status' => 0,
				]);

				if(!is_null($finance_auto_withdraw) && !empty($finance_auto_withdraw)){
					$response['id'] = $finance_auto_withdraw['id'];

					$web_auto_withdraw_status = $this->Setting_model->setting_find([
						'name' => 'auto_withdraw_status'
					]);
					$web_auto_withdraw_status = $web_auto_withdraw_status==''?0:$web_auto_withdraw_status['value'];
					if($web_auto_withdraw_status == "1" || $web_auto_withdraw_status == 1){
						$chk_cache_process_withdraw_chk = $this->cache->file->get('process_auto_finance_withdraw_'.$finance_auto_withdraw['id']);
						if($chk_cache_process_withdraw_chk  === FALSE){
							$this->cache->file->save('process_auto_finance_withdraw_'.$finance_auto_withdraw['id'],true, 10);
							$bank_can_autos = $this->Bank_model->bank_list(['status'=>1,'status_withdraw'=>1,'security_api' => true,'api_type' => 1,'bank_code_list'=>["05","5","02","2","06","6","11"]]);
							$bank_can_auto = null;
							$bank_can_auto_chk = false;
							foreach ($bank_can_autos as $bank_can_auto_chk_process){
								if(!$bank_can_auto_chk){
									$bank_can_auto_chk = true;
								}
								$chk_cache_process_withdraw_bank_chk = $this->cache->file->get('process_auto_finance_withdraw_bank_'.$bank_can_auto_chk_process['id']);
								if($chk_cache_process_withdraw_bank_chk === FALSE && is_null($bank_can_auto)){
									$bank_can_auto = $bank_can_auto_chk_process;
								}
							}

							$this->Finance_model->finance_update([
								'id' => $finance_auto_withdraw['id'],
								'auto_withdraw_status' => 1,
								'ip' => "127.0.0.1",
								'status' => 4,
								'bank_withdraw_id' => !is_null($bank_can_auto) && !empty($bank_can_auto) ?  $bank_can_auto['id'] : null,
								'bank_withdraw_name' => !is_null($bank_can_auto) && !empty($bank_can_auto) ? $bank_can_auto['bank_name']." | ".$bank_can_auto['account_name'].' | '.$bank_can_auto['bank_number'] : null
							]);

							if(!is_null($bank_can_auto) && !empty($bank_can_auto)){
								$chk_cache_process_withdraw_bank_chk = $this->cache->file->get('process_auto_finance_withdraw_bank_'.$bank_can_auto['id']);
								if($chk_cache_process_withdraw_bank_chk  === FALSE){
									$this->cache->file->save('process_auto_finance_withdraw_bank_'.$bank_can_auto['id'],true, 30);

									//เพิ่ม log
									$log_deposit_withdraw_id = $this->Log_deposit_withdraw_model->log_deposit_withdraw_create([
										'account' => $finance_auto_withdraw['account'],
										'amount' => $finance_auto_withdraw['amount'],
										'username' => $finance_auto_withdraw['username'],
										'amount_before' => null,
										'withdraw_status_request' => 1,
										'type' => '2', //ถอน
										'description' => 'ถอนเงิน',
										'admin' => 0, //AUTO
									]);

									$res_withdraw = [];
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
									$bank_data_list = $this->Bank_model->bank_data_list();
									$chk_match_bank = false;
									$bank_code = "";
									for($i =0;$i<count($bank_data_list);$i++){
										if(strtoupper($bank_list[$finance_auto_withdraw['bank']]) == strtoupper($bank_data_list[$i]['code_en'])){
											$bank_code = $bank_data_list[$i]['bank_code'];
											$chk_match_bank = true;
											break;
										}
									}

									if($chk_match_bank){
										if($bank_can_auto['bank_code'] == "05" || $bank_can_auto['bank_code'] == "5"){
											$res_withdraw = $this->auto_withdraw_librarie->transfer($finance_auto_withdraw['username'],$finance_auto_withdraw['bank_number'],$bank_code,$finance_auto_withdraw['amount'],decrypt(base64_decode($bank_can_auto['api_token_1']),$this->config->item('secret_key_salt')),decrypt(base64_decode($bank_can_auto['api_token_2']),$this->config->item('secret_key_salt')),$bank_can_auto['bank_number']);
										}else if($bank_can_auto['bank_code'] == "02" || $bank_can_auto['bank_code'] == "2"){
											$bank_code = getBankCodeForKbank()[$bank_code];
											$res_withdraw = $this->auto_withdraw_librarie->transfer_kplus($finance_auto_withdraw['username'],$finance_auto_withdraw['bank_number'],$bank_code,$finance_auto_withdraw['amount'],decrypt(base64_decode($bank_can_auto['api_token_1']),$this->config->item('secret_key_salt')),decrypt(base64_decode($bank_can_auto['api_token_2']),$this->config->item('secret_key_salt')),$bank_can_auto['bank_number']);
										}else if($bank_can_auto['bank_code'] == "06" || $bank_can_auto['bank_code'] == "6"){
											$bank_code = getBankCodeForKrungsri()[$bank_code];
											$res_withdraw = $this->auto_withdraw_librarie->transfer_kma($finance_auto_withdraw['username'],$finance_auto_withdraw['bank_number'],$bank_code,$finance_auto_withdraw['amount'],decrypt(base64_decode($bank_can_auto['api_token_1']),$this->config->item('secret_key_salt')),decrypt(base64_decode($bank_can_auto['api_token_2']),$this->config->item('secret_key_salt')),$bank_can_auto['bank_number']);
										}else if($bank_can_auto['bank_code'] == "11"){
											$res_withdraw = $this->auto_withdraw_librarie->transfer_kkp($finance_auto_withdraw['username'],$finance_auto_withdraw['bank_number'],$bank_code,$finance_auto_withdraw['amount'],decrypt(base64_decode($bank_can_auto['api_token_1']),$this->config->item('secret_key_salt')),decrypt(base64_decode($bank_can_auto['api_token_2']),$this->config->item('secret_key_salt')),$bank_can_auto['bank_number']);
										}
										$this->cache->file->delete('process_auto_finance_withdraw_'.$finance_auto_withdraw['id']);
										//$this->cache->file->delete('process_auto_finance_withdraw_bank_'.$bank_can_auto['id']);
										if(!empty($res_withdraw)){
											if($res_withdraw['status']){

												$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
													'id' => $log_deposit_withdraw_id
												]);
												if($log_deposit_withdraw!=""){
													$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
														'id' => $log_deposit_withdraw_id,
														'description' => $log_deposit_withdraw['description']." | ทำรายการสำเร็จ Finance ID #".$finance_auto_withdraw['id'],
														'withdraw_status_status' => 1,
													]);
												}

												$response['status'] = true;
												$response['message'] = "Withdraw auto complete : ".json_encode($res_withdraw,JSON_UNESCAPED_UNICODE);
												$qrcode = null;
												if(($bank_can_auto['bank_code'] == "05" || $bank_can_auto['bank_code'] == "5") && isset($res_withdraw['msg']['data']) && isset($res_withdraw['msg']['data']['additionalMetaData']) && isset($res_withdraw['msg']['data']['additionalMetaData']['paymentInfo']) && is_array($res_withdraw['msg']['data']['additionalMetaData']['paymentInfo']) && count($res_withdraw['msg']['data']['additionalMetaData']['paymentInfo']) > 0){
													foreach ($res_withdraw['msg']['data']['additionalMetaData']['paymentInfo'] as $paymentInfo){
														if(is_null($qrcode) && isset($paymentInfo['QRstring']) && !empty($paymentInfo['QRstring'])){
															$qrcode = $paymentInfo['QRstring'];
														}
													}
												}else if(($bank_can_auto['bank_code'] == "02" || $bank_can_auto['bank_code'] == "2")  && isset($res_withdraw['msg']['rawQr']) && !empty($res_withdraw['msg']['rawQr'])){
													$qrcode = $res_withdraw['msg']['rawQr'];
												}else if(($bank_can_auto['bank_code'] == "06" || $bank_can_auto['bank_code'] == "6" )  && isset($res_withdraw['msg']['QRCimagevalue'])){
													$qrcode = $res_withdraw['msg']['QRCimagevalue'];
												}else if(($bank_can_auto['bank_code'] == "11" )  && isset($res_withdraw['msg']['qrData'])){
													$qrcode = $res_withdraw['msg']['qrData'];
												}
												$this->Finance_model->finance_update([
													'id' => $finance_auto_withdraw['id'],
													'qrcode' => $qrcode,
													'status' => 1,
													'ip' => "127.0.0.1",
													'auto_withdraw_status' => 2,
													'auto_withdraw_updated_at' => date('Y-m-d H:i:s'),
												]);


												//บันทึก line notify job
												$log_line_notify_id = $this->Log_line_notify_model->log_line_notify_create([
													'type' => 2,
													'message' => "ยอดถอน ".number_format($finance_auto_withdraw['amount'],2)." บาท ยูส ".$finance_auto_withdraw['username']." เวลา ".date('Y-m-d H:i:s')." ถอนโดย AUTO",
												]);
											}else{
												$message_error = isset($res_withdraw['msg']) ? $res_withdraw['msg'] : "";
												if(is_null($message_error) || empty(trim($message_error))){
													$message_error = " กรุณาตรวจสอบยอดถอนจำนวน ".number_format($finance_auto_withdraw['amount'],2)." บาท บน Internet Banking/Mobile App ว่าถูกถอนไปจริงหรือไม่";
												}

												$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
													'id' => $log_deposit_withdraw_id
												]);
												if($log_deposit_withdraw!="") {
													$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
														'id' => $log_deposit_withdraw_id,
														'description' => $log_deposit_withdraw['description'] . " | ทำรายการไม่สำเร็จ " . json_encode($res_withdraw,JSON_UNESCAPED_UNICODE),
														'withdraw_status_status' => 0,
													]);
												}

												$response['message'] = 'Withdraw Failed : '.$message_error;
												$this->Finance_model->finance_update([
													'id' => $finance_auto_withdraw['id'],
													//'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
													'ip' => "127.0.0.1",
													'auto_withdraw_updated_at' => date('Y-m-d H:i:s'),
													'auto_withdraw_status' => 3,
													'status' => 0,
													'auto_withdraw_remark' => 'ถอนออโต้ไม่สำเร็จ '.$bank_can_auto['bank_number'].' '.$message_error.', ให้แอดมินดำเนินการแทน BOT',
												]);

											}
										}else{
											$message_error = isset($res_withdraw['msg']) ? $res_withdraw['msg'] : "";
											if(is_null($message_error) || empty(trim($message_error))){
												$message_error = " กรุณาตรวจสอบยอดถอนจำนวน ".number_format($finance_auto_withdraw['amount'],2)." บาท บน Internet Banking/Mobile App ว่าถูกถอนไปจริงหรือไม่";
											}

											$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
												'id' => $log_deposit_withdraw_id
											]);
											if($log_deposit_withdraw!="") {
												$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
													'id' => $log_deposit_withdraw_id,
													'description' => $log_deposit_withdraw['description'] . " | ทำรายการไม่สำเร็จ " . $message_error,
													'withdraw_status_status' =>0,
												]);
											}

											$response['message'] = 'Withdraw Failed : '.$message_error;
											$this->Finance_model->finance_update([
												'id' => $finance_auto_withdraw['id'],
												//'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
												'ip' => "127.0.0.1",
												'auto_withdraw_updated_at' => date('Y-m-d H:i:s'),
												'auto_withdraw_status' => 3,
												'status' => 0,
												'auto_withdraw_remark' => 'ถอนออโต้ไม่สำเร็จ '.$bank_can_auto['bank_number'].' '.$message_error.', ให้แอดมินดำเนินการแทน BOT',
											]);
										}
									}else{
										$this->cache->file->delete('process_auto_finance_withdraw_'.$finance_auto_withdraw['id']);
										//$this->cache->file->delete('process_auto_finance_withdraw_bank_'.$bank_can_auto['id']);
										$response['message'] = 'Bank can auto withdraw '.$bank_can_auto['bank_number'].' is not available';
										$this->Finance_model->finance_update([
											'id' => $finance_auto_withdraw['id'],
											//'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
											'ip' => "127.0.0.1",
											'auto_withdraw_updated_at' => date('Y-m-d H:i:s'),
											'auto_withdraw_status' => 3,
											'status' => 0,
											'auto_withdraw_remark' => 'ค่ายธนาคารลูกค้าไม่มีในระบบ '.$finance_auto_withdraw['bank'].', ให้แอดมินดำเนินการแทน BOT',
										]);
									}

								}else{
									$this->cache->file->delete('process_auto_finance_withdraw_'.$finance_auto_withdraw['id']);
									$response['message'] = 'Bank can auto withdraw '.$bank_can_auto['bank_number'].' is not available';
									$this->Finance_model->finance_update([
										'id' => $finance_auto_withdraw['id'],
										//'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
										'ip' => "127.0.0.1",
										'auto_withdraw_updated_at' => date('Y-m-d H:i:s'),
										'auto_withdraw_status' => 3,
										'status' => 0,
										'auto_withdraw_remark' => 'ธนาคารถอนออโต้ '.$bank_can_auto['bank_number'].' อยู่ในสถานะไม่ว่าง, ให้แอดมินดำเนินการแทน BOT',
									]);
								}
							}else{
								$this->cache->file->delete('process_auto_finance_withdraw_'.$finance_auto_withdraw['id']);
								if($bank_can_auto_chk){
									$this->Finance_model->finance_update([
										'id' => $finance_auto_withdraw['id'],
										'ip' => "127.0.0.1",
										'auto_withdraw_updated_at' => date('Y-m-d H:i:s'),
										'auto_withdraw_status' => 0,
										'status' => 0,
									]);
									$response['status'] = true;
									$response['message'] = 'All Bank can auto withdraw => processing...';
								}else{
									$this->Finance_model->finance_update([
										'id' => $finance_auto_withdraw['id'],
										//'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
										'ip' => "127.0.0.1",
										'auto_withdraw_updated_at' => date('Y-m-d H:i:s'),
										'auto_withdraw_status' => 3,
										'status' => 0,
										'auto_withdraw_remark' => 'ธนาคารที่พร้อมถอนออโต้ไม่มีพร้อมใช้งาน, ให้แอดมินดำเนินการแทน BOT',
									]);
									$response['message'] = 'All Bank can auto withdraw is EMPTY';
								}
							}
						}else{
							$response['status'] = true;
							$response['message'] = 'Finance auto withdraw is processing....';
						}
					}else{
						$response['message'] = 'Web Setting Config [auto_withdraw_status=0] is inactive';
						$this->Finance_model->finance_update([
							'id' => $finance_auto_withdraw['id'],
							//'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
							'ip' => "127.0.0.1",
							'auto_withdraw_updated_at' => date('Y-m-d H:i:s'),
							'auto_withdraw_status' => 3,
							'status' => 0,
							'auto_withdraw_remark' => 'สถานะของเว็บส่วน BOT ถอนออโต้ถูกปิดใช้งาน, ให้แอดมินดำเนินการแทน BOT',
						]);
					}
				}else{
					$response['status'] = true;
					$response['message'] = 'Finance auto withdraw list = 0';
				}
			}catch (Exception $ex){
				if(isset($finance_auto_withdraw) && !empty($finance_auto_withdraw) && !is_null($finance_auto_withdraw)){
					$this->cache->file->delete('process_auto_finance_withdraw_'.$finance_auto_withdraw['id']);
				}
				if(isset($bank_can_auto) && !empty($bank_can_auto) && !is_null($bank_can_auto)){
					//$this->cache->file->delete('process_auto_finance_withdraw_bank_'.$bank_can_auto['id']);
				}
				$response['message'] = $ex;
			}
		}else{
			$response['message'] = 'Token invalid/IP is not allow';
		}
		echo json_encode(['success'=>$response['status'],"message"=>"Finance Auto Withdraw #".($response['id'])." : ".json_encode($response['message'],JSON_UNESCAPED_UNICODE)]);
		exit();
	}
}
