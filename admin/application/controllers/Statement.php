<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Statement extends CI_Controller
{
	public $menu_service;
	public function __construct()
	{
		date_default_timezone_set('Asia/Bangkok');
		parent::__construct();
		//if (!isset($_SESSION['user'])  || !in_array($_SESSION['user']['role'],[roleAdmin(),roleSuperAdmin()])) {
		if (!isset($_SESSION['user']) || !isset($_SESSION['user']['role'])) {
			redirect('../auth');
		}
		$this->load->library(['Menu_service']);
		if(!$this->menu_service->validate_permission_menu($this->uri)){
			redirect('../auth');
		}
	}
	public function index()
	{
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'username' => $_SESSION['user']['username'],
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'page_name' => "รายการเดินบัญชี",
			'description' => 'หน้ารายการ',
			'page_url' => $currentURL,
		]);
		$data['bank_list'] = $this->Bank_model->bank_list([
			'security' => 1,
		]);
		foreach($data['bank_list'] as $index => $bank){
			unset($bank['username']);
			unset($bank['password']);
			unset($bank['api_token_1']);
			unset($bank['api_token_2']);
			unset($bank['api_token_3']);
			$data['bank_list'][$index] = $bank;
		}
		$key_bank_number = [];
		$data_new = [];
		foreach($data['bank_list']  as $index => $bank){
			if(!array_key_exists($bank['bank_number'],$key_bank_number)){
				$key_bank_number[$bank['bank_number']] = $bank['bank_number'];
				$data_new[] = $bank;
			}
		}
		$data['bank_list']  = $data_new;
		$data['page'] = 'statement/statement';
		$this->load->view('main', $data);
	}
	public function statement_list_page()
	{
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
		echo json_encode([
			"draw" => intval($get['draw']),
			"recordsTotal" => intval($statement_count_all),
			"recordsFiltered" => intval($statement_count_search),
			"data" => $data,
		]);
	}
}
