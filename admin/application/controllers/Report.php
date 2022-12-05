<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'../vendor/autoload.php';

class Report extends CI_Controller
{
	public function __construct()
	{
		date_default_timezone_set('Asia/Bangkok');
		parent::__construct();
		if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'],[roleAdmin(),roleSuperAdmin()])) {
			redirect('../auth');
		}
	}
	public function member_register_sum_deposit()
	{
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "รายงาน",
			'description' => 'ยอดฝากรวมรายวัน',
			'page_url' => $currentURL,
		]);
		$data['page'] = 'report/member_register_sum_deposit';
		$this->load->view('main', $data);
	}
	public function member_not_deposit_less_than_7()
	{
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "รายงาน",
			'description' => 'ไม่ได้ฝากเข้ามามากกว่า 7 วัน (นับจากวันที่เลือกย้อนหลังไป)',
			'page_url' => $currentURL,
		]);
		$data['page'] = 'report/member_not_deposit_less_than_7';
		$this->load->view('main', $data);
	}
	public function user_list_member_register_sum_deposit_page()
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
		if(isset($get['date_start']) && isset($get['date_end'])){
			$search_data['date_start'] = $get['date_start'];
			$search_data['date_end'] = $get['date_end'];
		}
		if(isset($get['status']) ){
			$search_data['status'] = $get['status'];
		}
		$user_count_all = $this->User_model->user_sum_deposit_count();
		$user_count_search = $this->User_model->user_sum_deposit_count($search_data);
		$data = $this->User_model->user_list_sum_deposit_page($search_data);
		echo json_encode([
			"draw" => intval($get['draw']),
			"recordsTotal" => intval($user_count_all),
			"recordsFiltered" => intval($user_count_search),
			"data" => $data,
		]);
	}

	public function user_list_member_register_sum_deposit_excel()
	{
		$post = $this->input->post();
		$search = $post['search']['value'];
		$search_data = [
		];
		if ($search!="") {
			$search_data['search'] = $search;
		}
		if(isset($post['date_start']) && isset($post['date_end'])){
			$search_data['date_start'] = $post['date_start'];
			$search_data['date_end'] = $post['date_end'];
		}
		if(isset($post['status']) ){
			$search_data['status'] = $post['status'];
		}
		$data = $this->User_model->user_list_sum_deposit_excel($search_data);
		echo json_encode([
			"data" => $data,
		]);
	}

	public function user_list_member_not_deposit_less_than_7_page()
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
		if(isset($get['date_start']) && isset($get['date_end']) && $get['date_start'] !== "" && $get['date_end'] !== "" ){
			$search_data['date_start'] = $get['date_start'];
			$search_data['date_end'] = $get['date_end'];
		}else{
			echo json_encode([
				"draw" => intval($get['draw']),
				"recordsTotal" => 0,
				"recordsFiltered" => 0,
				"data" => [],
			]);
			exit();
		}
		$user_count_all = $this->User_model->user_not_deposit_less_than_7_count();
		$user_count_search = $this->User_model->user_not_deposit_less_than_7_count($search_data);
		$data = $this->User_model->user_list_not_deposit_less_than_7_page($search_data);
		echo json_encode([
			"draw" => intval($get['draw']),
			"recordsTotal" => intval($user_count_all),
			"recordsFiltered" => intval($user_count_search),
			"data" => $data,
		]);
	}

	public function user_list_member_not_deposit_less_than_7_excel()
	{
		$post = $this->input->post();
		$search = $post['search']['value'];
		$search_data = [
		];
		if ($search!="") {
			$search_data['search'] = $search;
		}
		if(isset($post['date_start']) && isset($post['date_end']) && $post['date_start'] !== "" && $post['date_end'] !== "" ){
			$search_data['date_start'] = $post['date_start'];
			$search_data['date_end'] = $post['date_end'];
		}else{
			echo json_encode([
				"data" => [],
			]);
			exit();
		}
		if(isset($get['status']) ){
			$search_data['status'] = $get['status'];
		}
		$data = $this->User_model->user_list_sum_deposit_excel($search_data);
		echo json_encode([
			"data" => $data,
		]);
	}

	public function add_credit()
	{
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "รายงาน",
			'description' => 'ยอดเติมเครดิต',
			'page_url' => $currentURL,
		]);
		$data['page'] = 'report/add_credit';
		$this->load->view('main', $data);
	}

	public function add_credit_list_page()
	{
		$get = $this->input->get();

		$search = $get['search']['value'];
		// $dir = $get['order'][0]['dir'];//order
		$per_page = $get['length'];//จำนวนที่แสดงต่อ 1 หน้า
		$page = $get['start'];
		$search_data = [
			'per_page' => $per_page,//left
			'page' => $page,//start,right
			'type' => 1
		];
		if ($search!="") {
			$search_data['search'] = $search;
		}
		if(isset($get['date_start']) && isset($get['date_end'])){
			$search_data['date_start'] = $get['date_start'];
			$search_data['date_end'] = $get['date_end'];
		}
		if(isset($get['status_add'])){
			$search_data['status_add'] = $get['status_add'];
		}
		$credit_count_all = $this->Credit_model->credit_count();
		$credit_count_search = $this->Credit_model->credit_count($search_data);
		$data = $this->Credit_model->credit_list_page($search_data);
		$sum_amount = $this->Credit_model->credit_sum_amount($search_data);
		if($sum_amount != "" && is_numeric($sum_amount['sum_amount'])){
			$sum_amount = (float)$sum_amount['sum_amount'];
		}else{
			$sum_amount = 0.00;
		}
		$data[] = [
			"account"=> "",
			"admin"=> "",
			"admin_username"=> "",
			"created_at"=> intval($credit_count_search),
			"credit_after"=> "",
			"credit_before"=> "",
			"id"=> "",
			"process"=> "",
			"type"=> "",
			"username"=> "สรุปจำนวนรายการ",
		];
		$data[] = [
			"account"=> "",
			"admin"=> "",
			"admin_username"=> "",
			"created_at"=> $sum_amount,
			"credit_after"=> "",
			"credit_before"=> "",
			"id"=> "",
			"process"=> "",
			"type"=> "",
			"username"=> "สรุปยอดรวม",
		];
		echo json_encode([
			"draw" => intval($get['draw']),
			"recordsTotal" => intval($credit_count_all),
			"recordsFiltered" => intval($credit_count_search),
			"data" => $data,
		]);
	}

	public function add_credit_list_excel()
	{
		$post = $this->input->post();
		$search = $post['search']['value'];
		$search_data = [
			'type' => 1
		];
		if ($search!="") {
			$search_data['search'] = $search;
		}
		if(isset($post['date_start']) && isset($post['date_end'])){
			$search_data['date_start'] = $post['date_start'];
			$search_data['date_end'] = $post['date_end'];
		}
		if(isset($post['status_add'])){
			$search_data['status_add'] = $post['status_add'];
		}
		$credit_count_search = $this->Credit_model->credit_count($search_data);
		$data = $this->Credit_model->credit_list_excel($search_data);
		$sum_amount = $this->Credit_model->credit_sum_amount($search_data);
		if($sum_amount != "" && is_numeric($sum_amount['sum_amount'])){
			$sum_amount = (float)$sum_amount['sum_amount'];
		}else{
			$sum_amount = 0.00;
		}
		$data[] = [
			"account"=> "",
			"admin"=> "",
			"admin_username"=> "",
			"created_at"=> intval($credit_count_search),
			"credit_after"=> "",
			"credit_before"=> "",
			"id"=> "",
			"process"=> "",
			"type"=> "",
			"username"=> "สรุปจำนวนรายการ",
		];
		$data[] = [
			"account"=> "",
			"admin"=> "",
			"admin_username"=> "",
			"created_at"=> $sum_amount,
			"credit_after"=> "",
			"credit_before"=> "",
			"id"=> "",
			"process"=> "",
			"type"=> "",
			"username"=> "สรุปยอดรวม",
		];
		echo json_encode([
			"data" => $data,
		]);
	}

	public function add_bonus()
	{
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "รายงาน",
			'description' => 'การรับโบนัส',
			'page_url' => $currentURL,
		]);
		$data['page'] = 'report/add_bonus';
		$this->load->view('main', $data);
	}

	public function add_bonus_list_page()
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
		if(isset($get['type']) && $get['type'] !== ""){
			$search_data['type'] = $get['type'];
		}
		if(isset($get['date_start']) && isset($get['date_end'])){
			$search_data['date_start'] = $get['date_start'];
			$search_data['date_end'] = $get['date_end'];
		}
		$credit_count_all = $this->Log_add_credit_model->log_add_credit_count();
		$credit_count_search = $this->Log_add_credit_model->log_add_credit_count($search_data);
		$data = $this->Log_add_credit_model->log_add_credit_list_page($search_data);
		$sum_amount = $this->Log_add_credit_model->log_add_credit_sum_amount($search_data);
		if($sum_amount != "" && is_numeric($sum_amount['sum_amount'])){
			$sum_amount = (float)$sum_amount['sum_amount'];
		}else{
			$sum_amount = 0.00;
		}
		$data[] = [
			"created_at"=> "สรุปจำนวนรายการ",
			"username"=> "",
			"from_amount"=> "",
			"amount"=> "",
			"type"=> "",
			"description"=> "",
			"manage_by"=> intval($credit_count_search),
		];
		$data[] = [
			"created_at"=> "สรุปยอดรวม",
			"username"=> "",
			"from_amount"=> "",
			"amount"=> "",
			"type"=> "",
			"description"=> "",
			"manage_by"=> $sum_amount,
		];
		echo json_encode([
			"draw" => intval($get['draw']),
			"recordsTotal" => intval($credit_count_all),
			"recordsFiltered" => intval($credit_count_search),
			"data" => $data,
		]);
	}

	public function add_bonus_list_excel()
	{
		$post = $this->input->post();
		$search = $post['search']['value'];
		$search_data = [

		];
		if ($search!="") {
			$search_data['search'] = $search;
		}
		if(isset($post['type']) && $post['type'] !== ""){
			$search_data['type'] = $post['type'];
		}
		if(isset($post['date_start']) && isset($post['date_end'])){
			$search_data['date_start'] = $post['date_start'];
			$search_data['date_end'] = $post['date_end'];
		}
		$credit_count_search = $this->Log_add_credit_model->log_add_credit_count($search_data);
		$data = $this->Log_add_credit_model->log_add_credit_list_excel($search_data);
		$sum_amount = $this->Log_add_credit_model->log_add_credit_sum_amount($search_data);
		if($sum_amount != "" && is_numeric($sum_amount['sum_amount'])){
			$sum_amount = (float)$sum_amount['sum_amount'];
		}else{
			$sum_amount = 0.00;
		}
		$data[] = [
			"created_at"=> "สรุปจำนวนรายการ",
			"username"=> "",
			"from_amount"=> "",
			"amount"=> "",
			"type"=> "",
			"description"=> "",
			"manage_by"=> intval($credit_count_search),
		];
		$data[] = [
			"created_at"=> "สรุปยอดรวม",
			"username"=> "",
			"from_amount"=> "",
			"amount"=> "",
			"type"=> "",
			"description"=> "",
			"manage_by"=> $sum_amount,
		];
		echo json_encode([
			"data" => $data,
		]);
	}

	public function add_promotion()
	{
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "รายงาน",
			'description' => 'การรับโปรโมชั่น',
			'page_url' => $currentURL,
		]);
		$data['page'] = 'report/add_promotion';
		$this->load->view('main', $data);
	}

	public function add_promotion_list_page()
	{
		$get = $this->input->get();
		$search_data = [];
		if(isset($get['date_start']) && isset($get['date_end'])){
			$search_data['date_start'] = $get['date_start'];
			$search_data['date_end'] = $get['date_end'];
		}
		$promotion_list = $this->Promotion_model->promotion_list();
		$promotions = [];
		$use_promotion_list = $this->Use_promotion_model->use_promotion_sum_use($search_data);
		$total = 0;
		$total_bonus = 0.00;
		$data =[];
		foreach ($promotion_list as $promotion){
			$total_use = 0;
			$total_bonus_use = 0.00;
			foreach ($use_promotion_list as $use_promotion){
				if($use_promotion['promotion'] == $promotion['id']){
					$total_use += intval($use_promotion['total']);
					$total_bonus_use += floatval($use_promotion['total_bonus']);
				}
			}
			$promotion['total'] = $total_use;
			$promotion['total_bonus'] = $total_bonus_use;
			$promotions[$promotion['id']] = $promotion;
		}
		foreach($promotions as $promotion){
			$total += $promotion['total'];
			$total_bonus += $promotion['total_bonus'];
			$data[] = [
				'name' => $promotion['name'],
				'total' => $promotion['total'],
				'total_bonus' => $promotion['total_bonus'],
			];
		}
		$data[] = [
			"name"=> "สรุปผลรวม",
			"total"=> $total,
			"total_bonus" => $total_bonus
		];
		echo json_encode([
			"draw" => intval($get['draw']),
			"data" => $data,
		]);
	}
	public function business_profit()
	{
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "รายงาน",
			'description' => 'ผลประกอบการ',
			'page_url' => $currentURL,
		]);
		$data['page'] = 'report/business_profit';
		$this->load->view('main', $data);
	}

	public function business_profit_page()
	{
		$get = $this->input->get();
		$search_data = [
			'date_start' => date('Y-m-d'),
			'date_end' => date('Y-m-d'),
		];
		if(isset($get['date_start']) && isset($get['date_end'])){
			$search_data['date_start'] = $get['date_start'];
			$search_data['date_end'] = $get['date_end'];
		}
		$report_all_year = $this->Report_business_benefit_model->report_business_benefit_sum_year_list([
			'date_start' => (isset($get['year_start']) ? $get['year_start'] : date('Y')).'-01-01',
			'date_end' =>(isset($get['year_end']) ? $get['year_end'] : date('Y')).'-12-31'
		]);
		$data_all_year = [
			'labels' => [],
			'datasets' => [],
		];
		$data_deposit_all_year = [];
		$data_withdraw_all_year = [];
		$data_total_all_year = [];
		foreach ($report_all_year as $data_month){
			$data_all_year['labels'][] = $data_month['process_date'];
			$data_deposit_all_year[] = $data_month['sum_deposit'];
			$data_withdraw_all_year[] = $data_month['sum_withdraw'];
			$data_total_all_year[] = $data_month['sum_total'];
		}
		$data_all_year['datasets'][] = [
			'label' => 'ฝาก',
			'borderWidth' =>1,
			'hidden' =>true,
			'data' => $data_deposit_all_year,
			'backgroundColor' =>[
				'rgba(54, 162, 235, 0.2)',
				'rgba(54, 162, 235, 0.2)',
				'rgba(54, 162, 235, 0.2)',
			],
			'borderColor' =>[
				'rgb(54, 162, 235)',
				'rgb(54, 162, 235)',
				'rgb(54, 162, 235)',
			],
		];
		$data_all_year['datasets'][] = [
			'label' => 'ถอน',
			'borderWidth' =>1,
			'hidden' =>true,
			'data' => $data_withdraw_all_year,
			'backgroundColor' =>[
				'rgba(255, 99, 132, 0.2)',
				'rgba(255, 99, 132, 0.2)',
				'rgba(255, 99, 132, 0.2)',
			],
			'borderColor' =>[
				'rgb(255, 99, 132)',
				'rgb(255, 99, 132)',
				'rgb(255, 99, 132)',
			],
		];
		$data_all_year['datasets'][] = [
			'label' => 'กำไร',
			'borderWidth' =>1,
			'data' => $data_total_all_year,
			'backgroundColor' =>[
				'rgba(75, 192, 192, 0.2)',
				'rgba(75, 192, 192, 0.2)',
				'rgba(75, 192, 192, 0.2)',
			],
			'borderColor' =>[
				'rgb(75, 192, 192)',
				'rgb(75, 192, 192)',
				'rgb(75, 192, 192)',
			],
		];
		$report_all_pick = $this->Report_business_benefit_model->report_business_benefit_list($search_data);
		$data_all_pick = [
			'labels' => [],
			'datasets' => [],
		];
		$data_deposit_all_pick = [];
		$data_withdraw_all_pick = [];
		$data_total_all_pick = [];
		foreach ($report_all_pick as $data_day){
			$data_all_pick['labels'][] = $data_day['process_date'];
			$data_deposit_all_pick[] = $data_day['deposit'];
			$data_withdraw_all_pick[] = $data_day['withdraw'];
			$data_total_all_pick[] = $data_day['total'];
		}
		$data_all_pick['datasets'][] = [
			'label' => 'ฝาก',
			'borderWidth' =>1,
			'hidden' =>true,
			'data' => $data_deposit_all_pick,
			'backgroundColor' =>[
				'rgba(54, 162, 235, 0.2)',
				'rgba(54, 162, 235, 0.2)',
				'rgba(54, 162, 235, 0.2)',
			],
			'borderColor' =>[
				'rgb(54, 162, 235)',
				'rgb(54, 162, 235)',
				'rgb(54, 162, 235)',

			],
		];
		$data_all_pick['datasets'][] = [
			'label' => 'ถอน',
			'borderWidth' =>1,
			'hidden' =>true,
			'data' => $data_withdraw_all_pick,
			'backgroundColor' =>[
				'rgba(255, 99, 132, 0.2)',
				'rgba(255, 99, 132, 0.2)',
				'rgba(255, 99, 132, 0.2)',
			],
			'borderColor' =>[
				'rgb(255, 99, 132)',
				'rgb(255, 99, 132)',
				'rgb(255, 99, 132)',
			],
		];
		$data_all_pick['datasets'][] = [
			'label' => 'กำไร',
			'borderWidth' =>1,
			'data' => $data_total_all_pick,
			'backgroundColor' =>[
				'rgba(75, 192, 192, 0.2)',
				'rgba(75, 192, 192, 0.2)',
				'rgba(75, 192, 192, 0.2)',
			],
			'borderColor' =>[
				'rgb(75, 192, 192)',
				'rgb(75, 192, 192)',
				'rgb(75, 192, 192)',
			],
		];
		echo json_encode([
			"data_all_year" => $data_all_year,
			"data_all_pick" => $data_all_pick,
		]);
	}

	public function business_profit_excel()
	{
		$post = $this->input->post();
		$search_data = [
			'date_start' => date('Y-m-d'),
			'date_end' => date('Y-m-d'),
		];
		if(isset($post['date_start']) && isset($post['date_end'])){
			$search_data['date_start'] = $post['date_start'];
			$search_data['date_end'] = $post['date_end'];
		}
		$report_all_year = $this->Report_business_benefit_model->report_business_benefit_sum_year_list([
			'date_start' => (isset($post['year_start']) ? $post['year_start'] : date('Y')).'-01-01',
			'date_end' =>(isset($post['year_end']) ? $post['year_end'] : date('Y')).'-12-31'
		]);
		$report_all_pick = $this->Report_business_benefit_model->report_business_benefit_list($search_data);
		echo json_encode([
			"data_all_year" => $report_all_year,
			"data_all_pick" => $report_all_pick,
		]);
	}
}
