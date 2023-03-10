<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'../vendor/autoload.php';
use \Curl\Curl;
use Rct567\DomQuery\DomQuery;

class User extends CI_Controller
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
			'page_name' => "สมาชิก",
			'description' => 'หน้ารายการ',
			'page_url' => $currentURL,
		]);
		$data['page'] = 'user/user';
		$this->load->view('main', $data);
	}
	public function user_list_page()
	{
		$get = $this->input->get();

		$search = isset($get['search']) && isset($get['search']['value']) ? $get['search']['value'] : "";
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
		if(isset($get['date_start']) && isset($get['date_end'])){
			$search_data['date_start'] = $get['date_start'];
			$search_data['date_end'] = $get['date_end'];
		}
		if(isset($get['status']) ){
			$search_data['status'] = $get['status'];
		}
		if(isset($get['role']) && $get['role'] !== "" && in_array($_SESSION['user']['role'],[roleSuperAdmin()])){
			$search_data['role'] = $get['role'];
		}
		$user_count_all = $this->User_model->user_count();
		$user_count_search = $this->User_model->user_count($search_data);
		if(isset($get['sortBy']) && !empty($get['sortBy']) ){
			$search_data['sortBy'] = $get['sortBy'];
		}
		if(isset($get['orderBy']) && !empty($get['orderBy']) ){
			$search_data['orderBy'] = $get['orderBy'];
		}
		$data = $this->User_model->user_list_page($search_data);
		if(isset($get['empty']) && $get['empty'] ){
			$data = array_merge([['username'=> "ไม่มี","id" => ""]],$data);
		}
		$user_id_list = [];
		foreach ($data as $user){
			if(is_null($user['sum_amount'])){
				$user_id_list[] = $user['id'];
			}
		}
		if(count($user_id_list) > 0){
			$sum_amount_list = $this->Finance_model->sum_amount_deposit_and_withdraw(['account_list' => $user_id_list]);
			foreach($user_id_list as $user_id){
				if(array_key_exists($user_id,$sum_amount_list)){
					$this->User_model->user_update(['sum_amount'=>$sum_amount_list[$user_id]['sum_amount'],'id'=> $user_id]);
				}
			}
			foreach ($data as $index => $user){
				if(array_key_exists($user['id'],$sum_amount_list)){
					$data[$index]['sum_amount'] = $sum_amount_list[$user['id']]['sum_amount'];
				}
			}
		}
		echo json_encode([
			"draw" => isset($get['draw']) ? intval($get['draw']) : 1,
			"recordsTotal" => intval($user_count_all),
			"recordsFiltered" => intval($user_count_search),
			"data" => $data,
		]);
	}
	public function user_form_update($id = "")
	{
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "สมาชิก",
			'description' => 'หน้าแก้ไข',
			'page_url' => $currentURL,
		]);
		$data['user'] = $this->User_model->user_find([
			'id' => $id
		]);
		if ($data['user']=="") {
			redirect('user');
		}
		$sum_amount_list = $this->Finance_model->sum_amount_deposit_and_withdraw(['account_list' => [$data['user']['id']]]);
		if(array_key_exists($data['user']['id'],$sum_amount_list)){
			$this->User_model->user_update(['sum_amount'=>$sum_amount_list[$data['user']['id']]['sum_amount'],'id'=> $data['user']['id']]);
			$data['user']['sum_amount'] = $sum_amount_list[$data['user']['id']]['sum_amount'];
		}
		if(!empty($data['user']['ref_from_account'])){
			$data['user_select'] = $this->User_model->user_select2([
				'sortBy' => 'account.agent',
				'ref_from_account' => $data['user']['ref_from_account'],
				'orderBy' => 'DESC',
			]);
		}else {
			$data['user_select'] = [];
		}
		$data['page'] = 'user/user_update';
		$this->load->view('main', $data);
	}
	public function user_update($id = "")
	{
		$user = $this->User_model->user_find([
			'id' => $id
		]);
		if ($user=="") {
			redirect('user');
			exit();
		}
		check_parameter([
			'phone',
			'full_name',
			'line_id',
			'bank',
			'bank_number',
			'amount_wallet',
			'is_active_return_balance',
			'role',
			'turn_date',
			'remark',
			'is_auto_withdraw',
			//'turn_before',
			//'turn_over'
		],'POST');
		$post = $this->input->post();
		$post['full_name'] = trim(strip_tags(($post['full_name'])));
		$post['bank_number'] = str_replace("-","",trim($post['bank_number']));
		$post['bank_number']  = preg_replace('/[^0-9]/','',trim($post['bank_number']));
		if(!in_array($post['role'],canManageRole()[$_SESSION['user']['role']])){
			$this->session->set_flashdata('warning', 'ท่านไม่มีสิทธิ์จัดการผู้ใช้งานท่านนี้');
			redirect('user/user_form_update/'.$id);
			exit();
		}
		$bank_acc_chk = $this->Account_model->account_find_chk_fast([
			'bank_number' => $post['bank_number'],
			'deleted_ignore' => true,
		]);
		if ($bank_acc_chk!="" && $bank_acc_chk['id'] != $user['id']) {
			$this->session->set_flashdata('warning', 'เลขบัญชีนี้มีผู้ใช้งานแล้ว : '.$post['bank_number']);
			redirect('user/user_form_update/'.$id);
			exit();
		}
		$bank_full_name_chk = $this->Account_model->account_find_chk_fast([
			'full_name' => $post['full_name'],
			'id_ne' => $user['id'],
			//'deleted_ignore' => true,
		]);
		if ($bank_full_name_chk!="" && $bank_full_name_chk['id'] != $user['id']) {
			$this->session->set_flashdata('warning', 'ชื่อ-นามสกุลนี้มีผู้ใช้งานแล้ว : '.$post['full_name']);
			redirect('user/user_form_update/'.$id);
			exit();
		}
		$update = [
			'phone' => trim(strip_tags($post['phone'])),
			'full_name' => trim(strip_tags($post['full_name'])),
			'line_id' => trim(strip_tags($post['line_id'])),
			'bank' => $post['bank'],
			'bank_number' => trim(strip_tags($post['bank_number'])),
			'bank_name' => trim(strip_tags($post['bank_name'])),
			//'amount_wallet' => $post['amount_wallet'],
			'role' => $post['role'],
			'agent' => $post['agent'],
			'is_active_return_balance' => $post['is_active_return_balance'],
			'commission_percent' => $post['commission_percent'],
			'id' => $id,
			'turn_date' => $post['turn_date'],
			'remark' => $post['remark'],
			'is_auto_withdraw' => $post['is_auto_withdraw'],
			//'turn_before' => $post['turn_before'],
			//'turn_over' => $post['turn_over']
		];
		if ($post['password']!="") {
			$update['password'] = md5($post['password']);
		}
		foreach (game_code_list() as $game_code){
			if(isset($post['turn_over_'.strtolower($game_code)])){
				$update['turn_over_'.strtolower($game_code)] = $post['turn_over_'.strtolower($game_code)];
			}else{
				$update['turn_over_'.strtolower($game_code)] = "0";
			}
			if(isset($post['turn_before_'.strtolower($game_code)])){
				$update['turn_before_'.strtolower($game_code)] = $post['turn_before_'.strtolower($game_code)];
			}else{
				$update['turn_before_'.strtolower($game_code)] = "0";
			}
		}
		$this->User_model->user_update($update);
		$this->Ref_model->ref_agent_user_update([
			'from_account' => $id,
			'agent' => $post['agent']
		]);
		if(isset($post['username_ref']) && !empty($post['username_ref'])){
			$username_ref = $this->User_model->user_find([
				'id' => $post['username_ref']
			]);
			if($username_ref != ""){
				$this->Ref_model->ref_agent_user_add_and_or_update([
					'to_account' => $id,
					'to_account_username' => $user['username'],
					'agent' => $username_ref['agent'],
					'from_account' => $username_ref['id'],
					'from_account_username' => $username_ref['username'],
				]);
			}else{
				$this->Ref_model->ref_agent_user_delete([
					'to_account' => $id
				]);
			}
		}else{
			$this->Ref_model->ref_agent_user_delete([
				'to_account' => $id
			]);
		}

		$username_ref_old = $this->User_model->user_find([
			'id' => $user['ref_from_account']
		]);
		$data_before = [
			'phone' => $user['phone'],
			'full_name' => $user['full_name'],
			'line_id' => $user['line_id'],
			'bank' => $user['bank'],
			'bank_number' => $user['bank_number'],
			'bank_name' => $user['bank_name'],
			'role' => $user['role'],
			'agent' => $user['agent'],
			'ref_name' => $username_ref_old != "" ? $username_ref_old['username']." (".($username_ref_old['agent'] ? "พันธมิตร" : "สมาชิกปกติ").")" : "",
			'is_active_return_balance' => $user['is_active_return_balance'],
			'commission_percent' => $user['commission_percent'],
			'turn_date' => $user['turn_date'],
			'remark' => $user['remark'],
			'turn_before' => $user['turn_before'],
			'turn_over' => $user['turn_over'],
			'is_edit_pass' => "0",
			'point_for_wheel' => $user['point_for_wheel'],
			'is_auto_withdraw' => $user['is_auto_withdraw'],
		];
		$data_after = [
			'phone' => $post['phone'],
			'full_name' => $post['full_name'],
			'line_id' => $post['line_id'],
			'bank' => $post['bank'],
			'bank_number' => $post['bank_number'],
			'bank_name' => $post['bank_name'],
			'role' => $post['role'],
			'agent' => $post['agent'],
			'ref_name' => isset($username_ref) && $username_ref != "" ? $username_ref['username']." (".($username_ref['agent'] ? "พันธมิตร" : "สมาชิกปกติ").")" : "",
			'is_active_return_balance' => $post['is_active_return_balance'],
			'commission_percent' => $post['commission_percent'],
			'turn_date' => $user['turn_date'],
			'remark' => $post['remark'],
			'turn_before' => $user['turn_before'],
			'turn_over' => $user['turn_over'],
			'is_edit_pass' => isset($post['password']) && !empty($post['password']) ? "1" : "0",
			'point_for_wheel' => $post['point_for_wheel'],
			'is_auto_withdraw' => $post['is_auto_withdraw'],
		];
		foreach (game_code_list() as $game_code){
			$data_before['turn_over_'.strtolower($game_code)] = $user['turn_over_'.strtolower($game_code)];
			$data_before['turn_before_'.strtolower($game_code)] = $user['turn_before_'.strtolower($game_code)];
			$data_after['turn_over_'.strtolower($game_code)] = $post['turn_over_'.strtolower($game_code)];
			$data_after['turn_before_'.strtolower($game_code)] = $post['turn_before_'.strtolower($game_code)];
		}
		$this->Log_account_model->log_account_create([
			'manage_by' => $_SESSION['user']['id'],
			'manage_by_username' => $_SESSION['user']['username'],
			'account' => $user['id'],
			'username' => $user['username'],
			'role' => $_SESSION['user']['role'],
			'data_before' => json_encode($data_before),
			'data_after' => json_encode($data_after),
		]);

		$this->session->set_flashdata('toast', 'แก้ไขข้อมูลเรียบร้อยแล้ว');
		redirect('user');
	}
	public function user_delete($id = "")
	{
		check_parameter([],'POST');
		$user = $this->User_model->user_find([
			'id' => $id
		]);
		if ($user=="") {
			echo json_encode([
				'message' => 'ไม่พบข้อมูล',
				'error' => true
			]);
			eexit();
		}

		$post = $this->input->post();
		$update = [
			'deleted' => 1,
			'deleted_by' => $_SESSION['user']['id'],
			'id' => $id,
		];
		$this->User_model->user_update($update);
		echo json_encode([
			'message' => 'ลบข้อมูลเรียบร้อยแล้ว',
			'result' => true
		]);
	}
	public function user_select2()
	{

		$search = $this->input->get('search');
		$page = $this->input->get('page');
		$per_page = 25;
		if ($page=="") {
			$page=1;
		}
		$page = ($page-1) * $per_page;
		$search_data = [
			'per_page' => $per_page,//left
			'page' => $page,//start,right
		];
		$data = $this->User_model->user_list_page($search_data);
		echo json_encode([
			'data' => $data,
		]);
	}
	public function remaining_credit($id = "")
	{
		header('Content-Type: application/json');
		$user = $this->User_model->user_find([
			'id' => $id
		]);
		if ($user=="") {
			echo json_encode([
				'message' => "ทำรายการไม่สำเร็จ",
				'error' => true
			]);
			exit();
		}
		$balance_credit = $this->game_api_librarie->balanceCredit($user);
		echo json_encode([
			'message' => 'success',
			'result' => $balance_credit
		]);
	}

	public function remaining_credit_all($id = "")
	{
		header('Content-Type: application/json');
		$user = $this->User_model->user_find([
			'id' => $id
		]);
		if ($user=="") {
			echo json_encode([
				'message' => "ทำรายการไม่สำเร็จ",
				'error' => true
			]);
			exit();
		}
		$balance_credit = $this->game_api_librarie->balanceCredit($user);

		$form_data = [];
		$form_data['username'] = $user['account_agent_username'];
		if ($user['turn_date']!="") {
			$date = new DateTime($user['turn_date']);
			$form_data['date_begin'] = $date->format('Y-m-d');
		}
		$form_data = member_turn_data($form_data);
		$turnover_data = $this->game_api_librarie->getTurn($form_data);

		echo json_encode([
			'message' => 'success',
			'result' => $balance_credit,
			'turnover' => [
				'start_date' => $form_data['start_date'],
				'end_date' => $form_data['end_date'],
				'data' => $turnover_data,
			]
		]);
	}

	public function user_create_agent_username($id = "")
	{
		header('Content-Type: application/json');
		$user = $this->User_model->user_find([
			'id' => $id
		]);
		if ($user=="") {
			echo json_encode([
				'message' => "ทำรายการไม่สำเร็จ, ไม่พบสมาชิกนี้ในระบบ",
				'error' => true
			]);
			exit();
		}else if(!empty($user['account_agent_username']) && !is_null($user['account_agent_username'])){
			echo json_encode([
				'message' => "ทำรายการไม่สำเร็จ, สมาชิกท่านนี้ได้รับยูสเล่นเกมส์ไปแล้ว",
				'error' => true
			]);
			exit();
		}
		$password = $this->config->item('prefix_pass').substr(rand(10000000,99999999),2,4);
		/*$account_max_id = $this->Member_model->member_max_id();
		$username = 0;
		if(!is_null($account_max_id) && isset($account_max_id['username'])){
			$account_max_id['username'] = str_replace(strtolower($this->config->item('api_agent')),"",strtolower($account_max_id['username']));
			$username = (int)filter_var($account_max_id['username'], FILTER_SANITIZE_NUMBER_INT);
			$username += 1;
		}
		$username = trim($user['username']);
		$post_fix_username = str_pad( $username, 16 - strlen($this->config->item('api_agent')), "0", STR_PAD_LEFT );
		$username_full = $post_fix_username;*/
		$username_full = "";
		$response = $this->game_api_librarie->registerPlayer($user['username'],$password,$user['username'],$user['username']);
		if(isset($response['code']) && $response['code'] == 0 && isset($response['result']) && isset($response['result']['loginName']) ){
			$this->User_model->user_update([
				'id' => $user['id'],
				'create_user_manual_by' => $_SESSION['user']['id'],
			]);
			$username_full = $response['result']['loginName'];
			$this->Member_model->member_create([
				'account_id' => $user['id'],
				'accid' => $user['id'],
				'username' => $response['result']['loginName'],
				'password' => $password,
			]);
			echo json_encode([
				'message' => 'ทำรายการสำเร็จได้รับยูส : '.$response['result']['loginName'],
				'result' => true,
			]);
			exit();
		}else{
			if(isset($response['code']) && $response['code'] == "80000014"){
				echo json_encode([
					'message' => "ทำรายการไม่สำเร็จ, เบอร์โทรนี้มีผู้ใช้งานแล้ว...",
					'response' => "ทำรายการไม่สำเร็จ, เบอร์โทรนี้มีผู้ใช้งานแล้ว...",
					'error' => false
				]);
				exit();
			}else{
				echo json_encode([
					'message' => 'ทำรายการไม่สำเร็จ, กรุณาลองใหม่อีกครั้ง',
					'response' => $response,
					'username_full' => $username_full,
					'password' => $password,
					'error' => false,
				]);
				exit();
			}
		}
	}

	public function user_manage_transaction($id)
	{
		// get data from params by use normal and echo param exam** $id **

		// start add log
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'username' => $_SESSION['user']['username'],
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'page_name' => "จัดการธุรกรรม",
			'description' => 'ข้อมูลธุรกรรม',
			'page_url' => $currentURL,
		]);
		// end add log

		// start check id from params
		$data['user'] = $this->User_model->user_find([
			'id' => $id
		]);
		if ($data['user'] == "") {
			redirect('user');
		}
		$data['page'] = 'user/user_manage_transaction';
		$this->load->view('main', $data);
	}
}
