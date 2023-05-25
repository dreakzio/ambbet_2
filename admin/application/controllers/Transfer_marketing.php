<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'../vendor/autoload.php';
use \Curl\Curl;
use Rct567\DomQuery\DomQuery;

class Transfer_marketing extends CI_Controller
{
	public $menu_service;
	public function __construct()
	{
		date_default_timezone_set('Asia/Bangkok');
		parent::__construct();
		//if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'],[roleAdmin(),roleSuperAdmin()])) {
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
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "โยกสมาชิกการตลาด",
			'description' => 'หน้ารายการ',
			'page_url' => $currentURL,
		]);
		$data['page'] = 'transfer_marketing/transfer_marketing';
		$this->load->view('main', $data);
	}


	private function helloworld(){
		return "test";
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
		if(isset($get['role']) && $get['role'] !== "" ){
			$search_data['role'] = $get['role'];
		}
		$user_count_all = $this->Transfer_marketing_model->user_count();
		$user_count_search = $this->Transfer_marketing_model->user_count($search_data);
		if(isset($get['sortBy']) && !empty($get['sortBy']) ){
			$search_data['sortBy'] = $get['sortBy'];
		}
		if(isset($get['orderBy']) && !empty($get['orderBy']) ){
			$search_data['orderBy'] = $get['orderBy'];
		}
		$data = $this->Transfer_marketing_model->user_list_page($search_data);

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
		$merge_data = [];
		$ref_list_id = []; // current 25 account
		$declare = [];
		foreach ($data as $user){
				$ref_list_id[] = $user['id'];
		}
		$ref_list_from = count($ref_list_id) == 0 ? $ref_list_id : $this->Transfer_marketing_model->getRefByAccountId_2($ref_list_id); // true !!! data for find ref || current 13 account
		
		foreach($data as $index => $result){
			if(array_key_exists($result['id'],$ref_list_from)){
				$data[$index]['from_account_username'] = $ref_list_from[$result['id']]['from_account_username'];
			}else{
				$data[$index]['from_account_username'] = null;
			}
		}

		echo json_encode([
			"draw" => isset($get['draw']) ? intval($get['draw']) : 1,
			"recordsTotal" => intval($user_count_all),
			"recordsFiltered" => intval($user_count_search),
			// "ref_list_from" => $ref_list_from,
			"data" => $data,
		]);
	}
	public function transfer_marketing_form_update($id = "")
	{
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "โยกสมาชิกการตลาด",
			'description' => 'หน้าแก้ไข',
			'page_url' => $currentURL,
		]);
		$data['user'] = $this->User_model->user_find([
			'id' => $id
		]);
		if ($data['user']=="") {
			redirect('transfer_marketing');
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
		$data['page'] = 'transfer_marketing/transfer_marketing_update';
		// var_dump($data);
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
		],'POST');
		$post = $this->input->post();
		$post['full_name'] = trim($post['full_name']);
		$update = [
			'phone' => $post['phone'],
			'full_name' => $post['full_name'],
			// 'commission_percent' => $post['commission_percent'],
			'id' => $id,
		];
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
			'agent' => $user['agent'],
			'ref_name' => $username_ref_old != "" ? $username_ref_old['username']." (".($username_ref_old['agent'] ? "พันธมิตร" : "สมาชิกปกติ").")" : "",
			// 'commission_percent' => $user['commission_percent'],
			'turn_date' => $user['turn_date'],
		];
		$data_after = [
			'phone' => $post['phone'],
			'full_name' => $post['full_name'],
			'role' => $post['role'],
			'agent' => $post['agent'],
			'ref_name' => isset($username_ref) && $username_ref != "" ? $username_ref['username']." (".($username_ref['agent'] ? "พันธมิตร" : "สมาชิกปกติ").")" : "",
			// 'commission_percent' => $post['commission_percent'],
		];
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
		redirect('transfer_marketing');
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
}
