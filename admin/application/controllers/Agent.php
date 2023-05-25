<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'../vendor/autoload.php';
use \Curl\Curl;
use Rct567\DomQuery\DomQuery;

class Agent extends CI_Controller
{
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
		//เพิ่ม Logs
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "พันธมิตร",
			'description' => 'หน้ารายการ',
			'page_url' => $currentURL,
		]);
		$data['page'] = 'agent/agent';
		$this->load->view('main', $data);
	}
	public function agent_list_page()
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
		$user_count_all = $this->User_model->agent_count();
		$user_count_search = $this->User_model->agent_count($search_data);
		$data = $this->User_model->agent_list_page($search_data);
		$form_account_list = [];
		foreach($data as $index => $agent){
			$form_account_list[] = $agent['id'];
		}
		$sum_member_list = count($form_account_list) == 0 ? [] : $this->Ref_model->ref_agent_sum_member_by_from_account_list(['from_account_list'=>$form_account_list]);
		foreach($data as $index => $agent){
			$data[$index]['sum_member'] = array_key_exists($agent['id'],$sum_member_list) ? $sum_member_list[$agent['id']]['sum_member'] : 0 ;
		}
		$day = cal_days_in_month(CAL_GREGORIAN, date("m"), date("Y"));
		/*$commission_year_month_day = $this->Finance_model->commission_year_month_day([
			'account_list' => $form_account_list,
			'month' => date("m"),
			'year' => date("Y"),
			'day' => "01",
			'end_day' => $day,
		]);
        foreach($data as $index => $value){
			$deposit = 0.00;
			$withdraw = 0.00;
			if(array_key_exists($value['id'],$commission_year_month_day)){
				$deposit += (float)$commission_year_month_day[$value['id']]['deposit'];
				$withdraw += (float)$commission_year_month_day[$value['id']]['withdraw'];
				$sum_amount = ($deposit-$withdraw)*((float)$value['commission_percent']/100);
				$data[$index]['sum_commission'] = $sum_amount;
			}else{
				$deposit += 0;
				$withdraw += 0;
				$sum_amount = 0;
				$data[$index]['sum_commission'] = 0;
			}

		}*/
		echo json_encode([
			"draw" => intval($get['draw']),
			"recordsTotal" => intval($user_count_all),
			"recordsFiltered" => intval($user_count_search),
			"data" => $data,
		]);
	}
	public function commission($id = "")
	{
		//เพิ่ม Logs
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "Commission",
			'description' => 'หน้ารายการ',
			'page_url' => $currentURL,
		]);
		$data['user'] = $this->User_model->user_find([
			'id' => $id
		]);
		$commission_group_by_year = $this->Finance_model->commission_group_by_year([
			'account' => $id
		]);
		$data['commission_group_by_year'] = $commission_group_by_year;
		$form_account_list[] = $id;
		$sum_member_list = count($form_account_list) == 0 ? [] : $this->Ref_model->ref_agent_sum_member_by_from_account_list(['from_account_list'=>$form_account_list]);
		$data['user']['sum_member'] = array_key_exists($id,$sum_member_list) ? $sum_member_list[$id]['sum_member'] : 0 ;
		$data['page'] = 'agent/commission';
		$this->load->view('main', $data);
	}
	public function commission_list()
	{
		$get = $this->input->get();
		$user = $this->User_model->user_find([
			'id' => $get['account']
		]);
		//$day = cal_days_in_month(CAL_GREGORIAN, $get['month'], $get['year']);
		$data = [];
		$deposit = 0;
		$withdraw = 0;
		$bonus = 0;
		try{
			$from_date = new DateTime((isset($get['date_start'])?$get['date_start']:date("Y-m-d")));
			$end_date = new DateTime((isset($get['date_end'])?$get['date_end']:date("Y-m-d")));
			do{
				$day = $from_date->format('d');
				$commission_year_month_day = $this->Finance_model->commission_year_month_day([
					'account' => $user['id'],
					'month' => $from_date->format("m"),
					'year' => $from_date->format("Y"),
					'day' => $day
				]);
				$deposit += $commission_year_month_day['deposit'];
				$withdraw += $commission_year_month_day['withdraw'];
				$bonus += $commission_year_month_day['bonus'];
				$data[] = [
					'day' => $day."/".$from_date->format("m")."/".$from_date->format("Y"),
					'deposit' => $commission_year_month_day['deposit'],
					'withdraw' => $commission_year_month_day['withdraw'],
					'bonus' => $commission_year_month_day['bonus'],
					'sum' => $commission_year_month_day['sum']
				];
				$from_date = $from_date->add(new DateInterval('P1D'));
			}while($from_date->getTimestamp() <= $end_date->getTimestamp());
		}catch (Exception $ex){

		}
		$data[] = [
			'day' => 'สรุปยอด',
			'deposit' => $deposit,
			'withdraw' => $withdraw,
			'bonus' => $bonus,
			'sum_amount' => ($deposit-$withdraw)
		];
		$data[] = [
			'day' => 'สรุปคอมมิชชั่น ('.$user['commission_percent'].' %)',
			'deposit' => $deposit,
			'withdraw' => $withdraw,
			'bonus' => $bonus,
			'sum_amount' => ($deposit-$withdraw)*$user['commission_percent']/100
		];
		echo json_encode([
			'result' => $data
		]);
	}
	public function commission_detail($id = '', $year = '', $month='', $dasy = '')
	{
		//เพิ่ม Logs
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "Commission Detail",
			'description' => 'หน้ารายละเอียด',
			'page_url' => $currentURL,
		]);
		$get = $this->input->get();
		$data['commission_detail'] = $this->Finance_model->commission_detail([
			'account' => $id,
			'month' => $month,
			'year' => $year,
			'day' => $dasy
		]);
		$data['page'] = 'agent/commission_detail';
		$this->load->view('main', $data);
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
		$balance_credit = $this->game_api_librarie->balanceCredit(array(
			'username' => $user['account_agent_username'],
			'balance_type' => 'M',
		));
		echo json_encode([
			'message' => 'success',
			'result' => $balance_credit['success'] && isset($balance_credit['data']['MAIN']) && isset($balance_credit['data']['MAIN']['value']) ? $balance_credit['data']['MAIN']['value'] : 0.00
		]);
	}
	public function reportmember($id)
	{
		//เพิ่ม Logs
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "Report Member",
			'description' => 'หน้ารายการ',
			'page_url' => $currentURL,
		]);
		$data['user'] = $this->User_model->user_find([
			'id' => $id
		]);
		$data['page'] = 'agent/reportmember';
		$this->load->view('main', $data);
	}

	public function reportmember_list()
	{
		$get = $this->input->get();
		$user = $this->User_model->user_find([
			'id' => $get['account']
		]);
		$search = isset($get['search']) && $get['search']['value'] ? $get['search']['value'] : "";
		$per_page = isset($get['length']) ? $get['length'] : 20;//จำนวนที่แสดงต่อ 1 หน้า
		$page = isset($get['start']) ? $get['start'] : 0;
		//$day = cal_days_in_month(CAL_GREGORIAN, $get['month'], $get['year']);
		$data = [];
		$deposit = 0;
		$withdraw = 0;
		$search_data = [
			'per_page' => $per_page,//left
			'page' => $page,
			'account' => $get['account'],
			//'month' => $get['month'],
			//'year' => $get['year'],
			//'day' => "01",
			//'end_day' => $day,
			'date_start' => $get['date_start'],
			'date_end' => $get['date_end'],
		];
		if ($search!="") {
			$search_data['search'] = $search;
		}
		$commission_year_month_day = $this->Finance_model->report_member_year_month_day($search_data);
		$data_count_all = $this->Finance_model->report_member_year_month_day_count(['account' => $get['account']]);
		unset($search_data['date_start']);
		unset($search_data['date_end']);
		$data_count_search = $this->Finance_model->report_member_year_month_day_count($search_data);
		foreach($commission_year_month_day as $report_members){
			$deposit += $report_members['deposit'];
			$withdraw += $report_members['withdraw'];
			$username = $report_members['username'];
			$username .= !empty($report_members['account_agent_username']) ? "" : " (ยังไม่ได้รับยูสเซอร์)";
			$data[] = [
				'username' => $username,
				'deposit' => $report_members['deposit'],
				'withdraw' => $report_members['withdraw'],
				'sum' => $report_members['sum']
			];
		}
		$data[] = [
			'username' => 'สรุปยอด',
			'deposit' => $deposit,
			'withdraw' => $withdraw,
			'sum_amount' => ($deposit-$withdraw)
		];
		echo json_encode([
			"draw" => intval($get['draw']),
			"recordsTotal" => intval($data_count_all),
			"recordsFiltered" => intval($data_count_search),
			"data" => $data,
		]);
	}

	public function reportmember_register($id)
	{
		//เพิ่ม Logs
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "Report Member Register",
			'description' => 'หน้ารายการ',
			'page_url' => $currentURL,
		]);
		$data['user'] = $this->User_model->user_find([
			'id' => $id
		]);
		if($data['user'] != ""){
			$this->Ref_model->ref_agent_user_update([
				'from_account' => $id,
				'agent' => $data['user']['agent']
			]);
		}
		$form_account_list[] = $id;
		$sum_member_list = count($form_account_list) == 0 ? [] : $this->Ref_model->ref_agent_sum_member_by_from_account_list(['from_account_list'=>$form_account_list]);
		$data['user']['sum_member'] = array_key_exists($id,$sum_member_list) ? $sum_member_list[$id]['sum_member'] : 0 ;
		$data['page'] = 'agent/reportmember_register';
		$this->load->view('main', $data);
	}

	public function reportmember_register_list()
	{
		$get = $this->input->get();
		$search = isset($get['search']) && $get['search']['value'] ? $get['search']['value'] : "";
		$per_page = isset($get['length']) ? $get['length'] : 20;//จำนวนที่แสดงต่อ 1 หน้า
		$page = isset($get['start']) ? $get['start'] : 0;
		$search_data = [
			'per_page' => $per_page,//left
			'page' => $page,
			'account' => $get['account'],
			'status' => $get['status'],
			'date_start' => $get['date_start'],
			'date_end' => $get['date_end'],
			'date_start_member' => $get['date_start'],
			'date_end_member' => $get['date_end'],
		];
		if ($search!="") {
			$search_data['search'] = $search;
		}
		$data = $this->Ref_model->ref_agent_member_register_list($search_data);
		$data_count_all = $this->Ref_model->ref_agent_member_register_count(['account' => $get['account']]);
		$data_count_search = $this->Ref_model->ref_agent_member_register_count($search_data);
		echo json_encode([
			"draw" => intval($get['draw']),
			"recordsTotal" => intval($data_count_all),
			"recordsFiltered" => intval($data_count_search),
			"data" => $data,
		]);
	}
}
