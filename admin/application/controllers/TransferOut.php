<?php
defined('BASEPATH') or exit('No direct script access allowed');

class TransferOut extends CI_Controller
{

	public function __construct()
	{
		date_default_timezone_set('Asia/Bangkok');
		parent::__construct();
		if (!isset($_SESSION['user'])  || !in_array($_SESSION['user']['role'],[roleSuperAdmin()])) {
			redirect('../auth');
		}
	}
    public function index()
    {
		$bank_list = $this->Bank_model->bank_list(['bank_code_list'=>["05","5","02","2","06","6","11"],"status"=>"1","api_type"=>"1"]);
		$data['bank_list'] = [];
		$bank_code_list = [];
		$bank_list_data = [];
		foreach($bank_list as $index => $bank){

			unset($bank['username']);
			unset($bank['password']);
			unset($bank['api_token_1']);
			unset($bank['api_token_2']);
			unset($bank['api_token_3']);
			if(!in_array($bank['bank_number'],$bank_code_list)){
				$bank_code_list[] = $bank['bank_number'];
				$bank_list[$index] = $bank;
				$bank_list_data[] =  $bank_list[$index];
			}
		}
		$data['bank_list'] = $bank_list_data;
		$data['bank_code_list'] = $this->Bank_model->bank_data_list();
        $data['page'] = 'transfer_out/transfer_out';
        $this->load->view('main', $data);
    }

    public function transfer_out_money(){
		check_parameter([
			'bank_id',
			'amount',
			'bank_to',
			'bank_number_to',
			'bank_acc_name_to',
		], 'POST');
		$post = $this->input->post();

		$withdraw_credit_date  = isset($_SESSION['withdraw_credit_date']) ? $_SESSION['withdraw_credit_date'] : null;
		if(!is_null($withdraw_credit_date)){
			try{
				$hiDate = new DateTime($_SESSION['withdraw_credit_date']);
				$loDate = new DateTime(date('Y-m-d H:i:s'));
				$diff = $hiDate->diff($loDate);
				$secs = ((($diff->format("%a") * 24) + $diff->format("%H")) * 60 +
						$diff->format("%i")) * 60 + $diff->format("%s");

				if($secs <= 70){
					echo json_encode([
						'message' => "ท่านทำรายการโยกเงินออกติดต่อกัน, กรุณารออีก 1 นาที จึงสามารถทำรายการได้ใหม่อีกครั้ง",
						'error' => true
					]);
					exit();
				}
			}catch (Exception $ex){

			}
		}

		//เพิ่ม Logs
		$log_transfer_out_id = $this->Log_transfer_out_model->log_transfer_out_create([
			'bank_id' => $post['bank_id'],
			'amount' => str_replace(",","",$post['amount']),
			'admin' => $_SESSION['user']['id'],
			'bank' => $post["bank"],
			'bank_number' => $post["bank_number"],
			'bank_acc_name' => $post["bank_acc_name"],
			'bank_to' => $post["bank_to"],
			'bank_acc_name_to' => $post["bank_acc_name_to"],
			'bank_number_to' => $post["bank_number_to"],
			'description' => "ถอนเงิน",
		]);

		try{
			$bank = $this->Bank_model->bank_find(['id'=>$post['bank_id'],'security_api'=>true]);
			if($bank != "" && $bank['status'] == "1" && ($bank['bank_code'] == "05" || $bank['bank_code'] == "5" || $bank['bank_code'] == "02" || $bank['bank_code'] == "2"|| $bank['bank_code'] == "06" || $bank['bank_code'] == "6" || $bank['bank_code'] == "11")){
				if($bank['bank_code'] == "05" || $bank['bank_code'] == "5"){
					$res_withdraw = $this->auto_withdraw_librarie->transfer($post["bank_number_to"],$post["bank_number_to"],$post["bank_to"],str_replace(",","",$post['amount']),decrypt(base64_decode($bank['api_token_1']),$this->config->item('secret_key_salt')),decrypt(base64_decode($bank['api_token_2']),$this->config->item('secret_key_salt')),$bank['bank_number']);
				}else if($bank['bank_code'] == "06" || $bank['bank_code'] == "6"){
					$post["bank_to"] = getBankCodeForKrungsri()[$post["bank_to"]];
					$res_withdraw = $this->auto_withdraw_librarie->transfer_kma($post["bank_number_to"],$post["bank_number_to"],$post["bank_to"],str_replace(",","",$post['amount']),decrypt(base64_decode($bank['api_token_1']),$this->config->item('secret_key_salt')),decrypt(base64_decode($bank['api_token_2']),$this->config->item('secret_key_salt')),$bank['bank_number']);
				}else if($bank['bank_code'] == "02" || $bank['bank_code'] == "2"){
					$post["bank_to"] = getBankCodeForKbank()[$post["bank_to"]];
					$res_withdraw = $this->auto_withdraw_librarie->transfer_kplus($post["bank_number_to"],$post["bank_number_to"],$post["bank_to"],str_replace(",","",$post['amount']),decrypt(base64_decode($bank['api_token_1']),$this->config->item('secret_key_salt')),decrypt(base64_decode($bank['api_token_2']),$this->config->item('secret_key_salt')),$bank['bank_number']);
				}else if($bank['bank_code'] == "11"){
					$res_withdraw = $this->auto_withdraw_librarie->transfer_kkp($post["bank_number_to"],$post["bank_number_to"],$post["bank_to"],str_replace(",","",$post['amount']),decrypt(base64_decode($bank['api_token_1']),$this->config->item('secret_key_salt')),decrypt(base64_decode($bank['api_token_2']),$this->config->item('secret_key_salt')),$bank['bank_number']);
					$this->Bank_model->bank_update(['id'=>$post['bank_id'],'balance'=>(float)str_replace(",",'',$res_withdraw['balance'])]);
				}
				if($res_withdraw['status']){
					$log_transfer_out = $this->Log_transfer_out_model->log_transfer_out_find([
						'id' => $log_transfer_out_id
					]);
					if($log_transfer_out!=""){
						$this->Log_transfer_out_model->log_transfer_out_update([
							'id' => $log_transfer_out_id,
							'status' => 1,
							'description' => $log_transfer_out['description']." | ทำรายการสำเร็จ Log ID #".$log_transfer_out_id,
						]);
					}
					echo json_encode([
						'message' => 'ทำรายการสำเร็จ, Log ID #'.$log_transfer_out_id,
						'result' => true,
					]);
					exit();
				}else{
					$log_transfer_out = $this->Log_transfer_out_model->log_transfer_out_find([
						'id' => $log_transfer_out_id
					]);
					if($log_transfer_out!=""){
						$this->Log_transfer_out_model->log_transfer_out_update([
							'id' => $log_transfer_out_id,
							'status' => 2,
							'description' => $log_transfer_out['description']." | ทำรายการไม่สำเร็จ ".$res_withdraw['msg'],
						]);
					}
					echo json_encode([
						'message' => 'ทำรายการไม่สำเร็จ '.$res_withdraw['msg'],
						'error' => true
					]);
					exit();
				}

			}else{
				$log_transfer_out = $this->Log_transfer_out_model->log_transfer_out_find([
					'id' => $log_transfer_out_id
				]);
				if($log_transfer_out!=""){
					$this->Log_transfer_out_model->log_transfer_out_update([
						'id' => $log_transfer_out_id,
						'status' => 2,
						//'description' => $log_transfer_out['description']." | ทำรายการไม่สำเร็จ, ธนาคาร ".$post["bank_number"]." อาจจะอยู่ในสถานะปิดใช้งาน / ไม่ใช่ธนาคาร SCB / ไม่ได้เป็นธนาคารที่ใช้ถอนเงิน",
						'description' => $log_transfer_out['description']." | ทำรายการไม่สำเร็จ, ธนาคาร ".$post["bank_number"]." อาจจะอยู่ในสถานะปิดใช้งาน / ต้องเป็นธนาคาร SCB,KBANK เท่านั้น",
					]);
				}
				echo json_encode([
					//'message' => "ทำรายการไม่สำเร็จ, ธนาคาร ".$post["bank_number"]." อาจจะอยู่ในสถานะปิดใช้งาน / ไม่ใช่ธนาคาร SCB / ไม่ได้เป็นธนาคารที่ใช้ถอนเงิน",
					'message' => "ทำรายการไม่สำเร็จ, ธนาคาร ".$post["bank_number"]." อาจจะอยู่ในสถานะปิดใช้งาน / ต้องเป็นธนาคาร SCB,KBANK เท่านั้น",
					'error' => true
				]);
				exit();
			}
		}catch (Exception $ex){
			$log_transfer_out = $this->Log_transfer_out_model->log_transfer_out_find([
				'id' => $log_transfer_out_id
			]);
			if($log_transfer_out!=""){
				$this->Log_transfer_out_model->log_transfer_out_update([
					'id' => $log_transfer_out_id,
					'status' => 2,
					'description' => $log_transfer_out['description']." | ทำรายการไม่สำเร็จ, Something error : ".$ex->getMessage(),
				]);
			}
			echo json_encode([
				'message' => 'ทำรายการไม่สำเร็จ Something error : '.$ex->getMessage(),
				'error' => true
			]);
			exit();
		}

	}
}
