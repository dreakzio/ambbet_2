<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'../vendor/autoload.php';

class Agent extends CI_Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Bangkok');
        parent::__construct();
        $this->check_login();
    }
	private function  check_login()
	{
		if (!isset($_SESSION['user'])) {
			session_destroy();
			redirect('auth');
			exit();
		}
		$user = $_SESSION['user'];
		if (empty($user)) {
			session_destroy();
			redirect('auth');
			exit();
		}else if($user['agent'] == "0"){
			redirect('ref');
			exit();
		}
	}

	public function index()
	{
		$data['header_menu'] = 'header_menu';
		$data['middle_bar'] = 'middle_bar';
		$data['back_btn'] = true;
		$data['back_url'] = base_url('dashboard');
		$data['footer_menu'] = 'footer_menu';
		$data['user'] = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		$data['page'] = 'agent';
		$this->load->view('main', $data);
	}

	public function report_member_list()
	{
		$get = $this->input->get();
		if(isset($get['per_page']) && $get['per_page'] > 20){
			$get['per_page'] = 20;
		}else if(!isset($get['per_page'])){
			$get['per_page'] = 20;
		}
		if(!isset($get['page'])){
			$get['page'] = 1;
		}
		header('Content-Type: application/json');
		$user = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		//$day = cal_days_in_month(CAL_GREGORIAN, $get['month'], $get['year']);
		$data = [];
		try {
			$from_date = new DateTime((isset($get['date_start']) ? $get['date_start'] : date("Y-m-d")));
			$end_date = new DateTime((isset($get['date_end']) ? $get['date_end'] : date("Y-m-d")));
			$interval = $from_date->diff($end_date);
			$days = (int)$interval->format('%a');
			if ($days <= 60) {

				$deposit = 0;
				$withdraw = 0;
				$search_data = [
					'per_page' => $get['per_page'],//left
					'page' =>  ($get['per_page'] * ($get['page'] - 1)),
					'account' => $user['id'],
					//'month' => $get['month'],
					//'year' => $get['year'],
					'date_start' => $get['date_start'],
					'date_end' => $get['date_end'],
					//'day' => "01",
					//'end_day' => $day,
				];
				$commission_year_month_day = $this->Finance_model->report_member_year_month_day($search_data);
				$data_count = $this->Finance_model->report_member_year_month_day_count($search_data);
				$totalPages = ceil($data_count / $get['per_page']);
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
				$data_total = [
					'username' => 'สรุปยอด',
					'deposit' => $deposit,
					'withdraw' => $withdraw,
					'sum_amount' => ($deposit-$withdraw),
					//'sum_amount' => ($deposit-$withdraw)*$user['commission_percent']/100
				];

				echo json_encode([
					'message' => 'success',
					'result' => $data,
					'result_total' => $data_total,
					'from' => (int)(($get['page'] - 1) * $get['per_page']) + 1 > $data_count ? $data_count : (int)(($get['page'] - 1) * $get['per_page']) + 1,
					'to' => (int)((($get['page'] - 1) * $get['per_page']) + $get['per_page'] > $data_count ? $data_count : (($get['page'] - 1) * $get['per_page']) + $get['per_page']),
					'total' => (int)$data_count,
					'page_count' => (int)$totalPages,
					'per_page' => (int)$get['per_page'],
					'page' => (int)$get['page'],
				]);
				exit();
			}else{
				$data = false;
			}
		}catch (Exception $ex){
			$data = false;
		}
		echo json_encode([
			'result' => $data,
			'result_total' => 0,
			'from' => 0,
			'to' => 0,
			'total' => 0,
			'page_count' =>0,
			'per_page' => (int)$get['per_page'],
			'page' => (int)$get['page'],
		]);
	}

	public function report_commission_list()
	{
		$get = $this->input->get();
		header('Content-Type: application/json');
		$user = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		$data = [];
		try{
			$from_date = new DateTime((isset($get['date_start'])?$get['date_start']:date("Y-m-d")));
			$end_date = new DateTime((isset($get['date_end'])?$get['date_end']:date("Y-m-d")));
			$interval = $from_date->diff($end_date);
			$days = (int)$interval->format('%a');
			if($days <= 60){
				$deposit = 0;
				$withdraw = 0;
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
					$data[] = [
						'day' => $day."/".$from_date->format("m")."/".$from_date->format("Y"),
						'deposit' => $commission_year_month_day['deposit'],
						'withdraw' => $commission_year_month_day['withdraw'],
						'sum' => $commission_year_month_day['sum']
					];
					$from_date = $from_date->add(new DateInterval('P1D'));
				}while($from_date->getTimestamp() <= $end_date->getTimestamp());

				//$month = strlen($get['month']) == 1 ? "0".$get['month'] : $get['month'];
				$data_total = [
					'day' => 'สรุปยอด',
					'deposit' => $deposit,
					'withdraw' => $withdraw,
					//'day_start' => "01",
					//'day_end' => $day,
					//'month_text' => array_key_exists($month,month_th_list()) ? month_th_list()[$month] : $month,
					'sum_amount' => ($deposit-$withdraw)*$user['commission_percent']/100
				];
			}else{
				$data = false;
				$data_total = [
					'day' => 'สรุปยอด',
					'deposit' => 0,
					'withdraw' => 0,
					'sum_amount' => 0
				];
			}
		}catch (Exception $ex){
			$data = false;
			$data_total = [
				'day' => 'สรุปยอด',
				'deposit' => 0,
				'withdraw' => 0,
				'sum_amount' => 0
			];
		}

		echo json_encode([
			'message' => 'success',
			'result' => $data,
			'result_total' => $data_total
		]);
	}

	public function ref_list()
	{
		$this->check_login();
		$get = $this->input->get();
		if(isset($get['per_page']) && $get['per_page'] > 20){
			$get['per_page'] = 20;
		}else if(!isset($get['per_page'])){
			$get['per_page'] = 20;
		}
		if(!isset($get['page'])){
			$get['page'] = 1;
		}
		header('Content-Type: application/json');
		$data = $this->Ref_model->ref_list([
			'from_account' => $_SESSION['user']['id'],
			'per_page' => $get['per_page'],
			'page' =>  ($get['per_page'] * ($get['page'] - 1)),
		]);
		$data_count = $this->Ref_model->ref_count([
			'from_account' => $_SESSION['user']['id']
		]);
		$totalPages = ceil($data_count / $get['per_page']);
		foreach($data as $index => $ref){
			$data[$index]['to_account_username'] = $ref['to_account_username'];
			$data[$index]['to_account_username'] .= !empty($ref['account_agent_username']) ? "" : " (ยังไม่ได้รับยูสเซอร์)";
		}
		echo json_encode([
			'message' => 'success',
			'result' => $data,
			'from' => (int)(($get['page'] - 1) * $get['per_page']) + 1 > $data_count ? $data_count : (int)(($get['page'] - 1) * $get['per_page']) + 1,
			'to' => (int)((($get['page'] - 1) * $get['per_page']) + $get['per_page'] > $data_count ? $data_count : (($get['page'] - 1) * $get['per_page']) + $get['per_page']),
			'total' => (int)$data_count,
			'page_count' => (int)$totalPages,
			'per_page' => (int)$get['per_page'],
			'page' => (int)$get['page'],
		]);
	}
}
