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
