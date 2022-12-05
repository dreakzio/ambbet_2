<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'../vendor/autoload.php';

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

	public function bb_auto_transfer_sfo4rsdf()
	{
		if(isset($_GET['api_token'])&& $_GET['api_token'] == "rs2nvxdjJLaBr5eXXZddTshDsM4T7Tw34MXLJNWN"){
			$bank_can_autos = $this->Bank_model->bank_list(['status'=>1,'security_api' => true,'auto_transfer' => 1,'api_type' => 1,'bank_code_list'=>["05","5","02","2","06","6"]]);
			foreach ($bank_can_autos as $bank_can_auto){
				$chk_cache_process_withdraw_chk = $this->cache->file->get('process_auto_transfer_'.$bank_can_auto['id']);
				if($chk_cache_process_withdraw_chk  === FALSE){
					$this->cache->file->save('process_auto_transfer_'.$bank_can_auto['id'],true, 40);
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
}
