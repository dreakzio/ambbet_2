<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
{
	public $menu_service;
	public function __construct()
	{
		date_default_timezone_set('Asia/Bangkok');
		parent::__construct();
		if(empty($this->uri->segment(1))){
			redirect('home');
			exit();
		}
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
			'page_name' => "แอดมิน",
			'description' => 'หน้าหลัก',
			'page_url' => $currentURL,
		]);
        $this->load->view('main');
    }
	public function verify_2fa(){
		$gg_2fa_status = $this->Setting_model->setting_find([
			'name' => 'gg_2fa_status'
		]);
		if($gg_2fa_status != "" && $gg_2fa_status['value'] == "1"){
			$gg_2fa_secret = $this->Setting_model->setting_find([
				'name' => 'gg_2fa_secret'
			]);
			if(
				!isset($_SESSION['user']['gg_2fa_chk']) ||
				!isset($_SESSION['user']['gg_2fa_secret']) ||
				(isset($_SESSION['user']['gg_2fa_chk']) && !$_SESSION['user']['gg_2fa_chk']) ||
				(isset($_SESSION['user']['gg_2fa_secret']) && ($_SESSION['user']['gg_2fa_secret'] != $gg_2fa_secret['value']))
			){
				try{
					$gg2fa_secret = decrypt(base64_decode($gg_2fa_secret['value']),$this->config->item('secret_key_salt'));
					if($gg2fa_secret === FALSE){
						$this->db->update_batch('web_setting', [[
							'name' => 'gg_2fa_secret',
							'value' => base64_encode(encrypt($gg_2fa_secret['value'],$this->config->item('secret_key_salt')))
						]], 'name');
					}
				}catch (Exception $ex){
					$this->db->update_batch('web_setting', [[
						'name' => 'gg_2fa_secret',
						'value' => base64_encode(encrypt($gg_2fa_secret['value'],$this->config->item('secret_key_salt')))
					]], 'name');
				}
				$this->load->view('main_gg_2fa');
			}else{
				redirect('/');
				exit();
			}
		}else{
			redirect('/');
			exit();
		}
	}
	public function verify_2fa_chk()
	{
		$gg_2fa_status = $this->Setting_model->setting_find([
			'name' => 'gg_2fa_status'
		]);
		if($gg_2fa_status != "" && $gg_2fa_status['value'] == "1"){
			$gg_2fa_secret = $this->Setting_model->setting_find([
				'name' => 'gg_2fa_secret'
			]);
			try{
				$gg2fa_secret = decrypt(base64_decode($gg_2fa_secret['value']),$this->config->item('secret_key_salt'));
				if($gg2fa_secret === FALSE){
					$this->db->update_batch('web_setting', [[
						'name' => 'gg_2fa_secret',
						'value' => base64_encode(encrypt($gg_2fa_secret['value'],$this->config->item('secret_key_salt')))
					]], 'name');
				}
			}catch (Exception $ex){
				$this->db->update_batch('web_setting', [[
					'name' => 'gg_2fa_secret',
					'value' => base64_encode(encrypt($gg_2fa_secret['value'],$this->config->item('secret_key_salt')))
				]], 'name');
				$gg2fa_secret = $gg_2fa_secret['value'];
			}
			$checkResult = $this->google_authenticator_librarie->verifyCode($gg2fa_secret, $_POST['gcode']); // 1 = 1*30sec clock tolerance
			if($checkResult){
				$_SESSION['user']['gg_2fa_chk'] = true;
				$_SESSION['user']['gg_2fa_secret'] = $gg_2fa_secret['value'];
				redirect('/');
			}else{
				$_SESSION['verify_2fa_error'] =  'Google 2FA Code ไม่ถูกต้อง';
				redirect('home/verify_2fa');
			}
		}
		exit();
	}

	public function report_history_withdraw(){
		$get = $this->input->get();
		$per_page = $get['length'];//จำนวนที่แสดงต่อ 1 หน้า
		$page = $get['start'];
		$search_data = [
			'per_page' => $per_page,//left
			'page' => $page,
			'type' => 2
		];
		$data = $this->Finance_model->finance_list_page($search_data);
		echo json_encode([
			"result" => $data,
		]);
	}

	public function report_history_deposit(){
		$get = $this->input->get();
		$per_page = $get['length'];//จำนวนที่แสดงต่อ 1 หน้า
		$page = $get['start'];
		$search_data = [
			'per_page' => $per_page,//left
			'page' => $page,
			'type' => 1
		];
		$data = $this->Finance_model->finance_list_page($search_data);
		echo json_encode([
			"result" => $data,
		]);
	}

	public function report_summary_per_day(){
		$day_of_month=cal_days_in_month(CAL_GREGORIAN, date("m"), date("Y"));
		$dayloop=$day_of_month;
		$data = [];
		$report_summary_per_day = $this->cache->file->get(base64_encode('report_summary_per_day'));
		if($report_summary_per_day !== FALSE){
			echo json_encode([
				"result" => $report_summary_per_day,
			]);
			exit();
		}
		//$report_list = $this->Finance_model->finance_report_all_day_group_by(['created_at'=>date("Y") . '-' . date("m") ]);
		$report_list = $this->Report_business_benefit_model->report_business_benefit_report_all_day_month_group_by(['created_at'=>date("Y") . '-' . date("m"),'type'=>'day' ]);
		$reports = [];
		foreach($report_list as $report){
			$deposit = $report['sum_deposit'];
			$withdraw = $report['sum_withdraw'];
			$total = $report['sum_total'];
			$bonus = $report['sum_bonus'];
			if(array_key_exists($report['created_at'],$reports)){
				$old_deposit = $reports[$report['created_at']]['deposit'];
				$old_withdraw = $reports[$report['created_at']]['withdraw'];
				$old_total = $reports[$report['created_at']]['total'];
				$old_bonus = $reports[$report['created_at']]['bonus'];
				$reports[$report['created_at']] = [
					'deposit' => (float)$old_deposit + (float)$deposit,
					'withdraw' => (float)$old_withdraw + (float)$withdraw,
					'total' => (float)$old_total + (float)$total,
					'bonus' => (float)$old_bonus + (float)$bonus,
				];
			}else{
				$reports[$report['created_at']] = [
					'deposit' => (float)$deposit,
					'withdraw' => (float)$withdraw,
					'total' => (float)$total,
					'bonus' => (float)$bonus,
				];
			}
		}
		while($dayloop <= $day_of_month && $dayloop > 0) {
			$day = sprintf("%02d", $dayloop);
			if ($day <= date("d")) {
				$day_indata = date("Y") . '-' . date("m") . "-" . (strlen($day) == 1 ? "0".$day : $day);
				if(array_key_exists($day_indata,$reports)){
					$data[] = [
						'day'=>$day."-".date("m")."-".date("Y"),
						'deposit'=>$reports[$day_indata]['deposit'],
						'withdraw'=>$reports[$day_indata]['withdraw'],
						'total'=>$reports[$day_indata]['total'],
						'bonus'=>$reports[$day_indata]['bonus'],
					];
				}else{
					$data[] = [
						'day'=>$day."-".date("m")."-".date("Y"),
						'deposit'=>0.00,
						'withdraw'=>0.00,
						'total'=>0.00,
						'bonus'=>0.00,
					];
				}
			}
			$dayloop--;
		}
		$this->cache->file->save(base64_encode('report_summary_per_day'),$data, 15);
		echo json_encode([
			"result" => $data,
		]);
	}

	public function report_summary_per_month(){
		$report_summary_per_month = $this->cache->file->get(base64_encode('report_summary_per_month'));
		if($report_summary_per_month !== FALSE){
			echo json_encode([
				"result" => $report_summary_per_month,
			]);
			exit();
		}
		$data = [];
		$month_loop=12;

		//$report_list = $this->Finance_model->finance_report_all_month_year_group_by(['created_at'=>date("Y") ]);
		$report_list = $this->Report_business_benefit_model->report_business_benefit_report_all_day_month_group_by(['created_at'=>date("Y"),'type'=>'month']);
		$reports = [];
		foreach($report_list as $report){
			$deposit = $report['sum_deposit'];
			$withdraw = $report['sum_withdraw'];
			$total = $report['sum_total'];
			$bonus = $report['sum_bonus'];
			if(array_key_exists($report['created_at'],$reports)){
				$old_deposit = $reports[$report['created_at']]['deposit'];
				$old_withdraw = $reports[$report['created_at']]['withdraw'];
				$old_total = $reports[$report['created_at']]['total'];
				$old_bonus = $reports[$report['created_at']]['bonus'];
				$reports[$report['created_at']] = [
					'deposit' => (float)$old_deposit + (float)$deposit,
					'withdraw' => (float)$old_withdraw + (float)$withdraw,
					'total' => (float)$old_total + (float)$total,
					'bonus' => (float)$old_bonus + (float)$bonus,
				];
			}else{
				$reports[$report['created_at']] = [
					'deposit' => (float)$deposit,
					'withdraw' => (float)$withdraw,
					'total' => (float)$total,
					'bonus' => (float)$bonus
				];
			}
		}
		while($month_loop<=12 && $month_loop>0){
			$month=sprintf("%02d",$month_loop);
			if($month_loop<=date("m")){
				$month_indata=date("Y").'-'.$month;
				if(array_key_exists($month_indata,$reports)){
					$data[] = [
						'month'=>$month."-".date("Y"),
						'deposit'=>$reports[$month_indata]['deposit'],
						'withdraw'=>$reports[$month_indata]['withdraw'],
						'total'=>$reports[$month_indata]['total'],
						'bonus'=>$reports[$month_indata]['bonus'],
					];
				}else{
					$data[] = [
						'month'=>$month."-".date("Y"),
						'deposit'=>0.00,
						'withdraw'=>0.00,
						'total'=>0.00,
						'bonus'=>0.00,
					];
				}
			}
			$month_loop--;
		}
		$this->cache->file->save(base64_encode('report_summary_per_month'),$data, 100);
		echo json_encode([
			"result" => $data,
		]);
	}

	public function report_summary_all_day(){
		echo json_encode([
			"result" => get_data_report_all_day(),
		]);
	}


}
