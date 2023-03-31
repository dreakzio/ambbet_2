<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Check_User extends CI_Controller{
	protected $CI;
	public function __construct()
	{
		$this->CI =& get_instance();
	}
	function update_user(){
		$process_report_business_benefit = $this->CI->cache->file->get("process_report_business_benefit");
		if($process_report_business_benefit === FALSE){
			$this->processReportBusinessBenefit();
		}
		if(isset($_SESSION['user']) && $_SESSION['user']['id']){

			$isAjaxRequest = false;
			$all_headers = getallheaders();
			if(
				isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
				strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'xmlhttprequest') == 0
			){
				$isAjaxRequest = true;
			}else if(isset($all_headers['Accept']) && strpos($all_headers['Accept'],"application/json") !== false){
				$isAjaxRequest = true;
			}
			$this->process_clear_cache();
			$gg_2fa_status =  $this->CI->Setting_model->setting_find([
				'name' => 'gg_2fa_status'
			]);
			if($gg_2fa_status != "" && $gg_2fa_status['value'] == "1"){
				$gg_2fa_secret =  $this->CI->Setting_model->setting_find([
					'name' => 'gg_2fa_secret'
				]);
				if(
					!isset($_SESSION['user']['gg_2fa_chk']) ||
					!isset($_SESSION['user']['gg_2fa_secret']) ||
					(isset($_SESSION['user']['gg_2fa_chk']) && !$_SESSION['user']['gg_2fa_chk']) ||
					(isset($_SESSION['user']['gg_2fa_secret']) && ($_SESSION['user']['gg_2fa_secret'] != $gg_2fa_secret['value']))
				){
					if($isAjaxRequest){
						exit();
					}else{

						if(!($this->CI->router->class == "home" && $this->CI->router->method == "verify_2fa") && !($this->CI->router->class == "home" && $this->CI->router->method == "verify_2fa_chk")){
							redirect('home/verify_2fa');
							exit();
						}
					}
				}
			}
			if(!$isAjaxRequest){

				$account = $this->CI->Account_model->account_find([
					'id' => $_SESSION['user']['id']
				]);
				if($account != ""){
					if($account['deleted'] == "1"){
						session_destroy();
						redirect('../auth');
						exit();
					}else{
						if(!in_array($account['role'],[roleAdmin(),roleSuperAdmin()])){
							session_destroy();
							redirect('../auth');
							exit();
						}else{
							$_SESSION['user'] = [
								'role' => $account['role'],
								'id' => $account['id'],
								'username' => $account['username'],
								'bank' => $account['bank'],
								'bank_number' => $account['bank_number'],
								'bank_name' => $account['bank_name'],
								'account_agent_id' => $_SESSION['user']['account_agent_id'],
								'gg_2fa_chk' => isset($_SESSION['user']['gg_2fa_chk']) ? $_SESSION['user']['gg_2fa_chk'] : false,
								'gg_2fa_secret' => isset($_SESSION['user']['gg_2fa_secret']) ? $_SESSION['user']['gg_2fa_secret'] : "",
							];

							//ตรวจสอบการผูก user_role
							init_role($account['id']);
						}
					}
				}else{
					session_destroy();
					redirect('../auth');
					exit();
				}
			}
		}
	}

	private function processReportBusinessBenefit(){
		$this->CI->cache->file->save("process_report_business_benefit",date('Y-m-d H:i:s'),150); //150 sec
		$days = date("Y-m-d");
		$days_addon = date("Y-m-d",strtotime('-1 days'));
		$reports = [];
		$date_time_chk_1 = new DateTime(date('Y-m-d H:i:s'));
		$date_time_chk_2 = new DateTime(date('Y-m-d')." 12:00:00");
		if($date_time_chk_1->getTimestamp() > $date_time_chk_2->getTimestamp()){
			$report_list = $this->CI->Finance_model->finance_report_all_day_group_by(['created_at'=>$days]);
		}else{
			$report_list = $this->CI->Finance_model->finance_report_all_day_group_by(['created_at'=>$days,'created_at_addon'=>$days_addon ]);
		}
		foreach($report_list as $report){
			$deposit = 0.00;
			$deposit_cnt = 0;
			$withdraw = 0.00;
			$withdraw_cnt = 0;
			$bonus = 0.00;
			if($report['type'] == "1"){
				$deposit = is_numeric($report['sum_amount']) ? (float)str_replace(",","",number_format($report['sum_amount'],2)) : 0.00;
				$bonus = is_numeric($report['sum_bonus']) ? (float)str_replace(",","",number_format($report['sum_bonus'],2)) : 0.00;
				$deposit_cnt = is_numeric($report['count']) ? (float)str_replace(",","",number_format($report['count'],0)) : 0;
			}else if($report['type'] == "2" && ($report['status'] == "1" || $report['status'] == "3")){
				$withdraw = is_numeric($report['sum_amount']) ? (float)str_replace(",","",number_format($report['sum_amount'],2)) : 0.00;
				$withdraw_cnt = is_numeric($report['count']) ? (float)str_replace(",","",number_format($report['count'],0)) : 0;
			}
			$total = (float)str_replace(",","",number_format($deposit - $withdraw,2));
			if(array_key_exists($report['created_at'],$reports)){
				$old_deposit = $reports[$report['created_at']]['deposit'];
				$old_deposit_cnt = $reports[$report['created_at']]['deposit_cnt'];
				$old_withdraw = $reports[$report['created_at']]['withdraw'];
				$old_withdraw_cnt = $reports[$report['created_at']]['withdraw_cnt'];
				$old_total = $reports[$report['created_at']]['total'];
				$old_bonus = $reports[$report['created_at']]['bonus'];
				$reports[$report['created_at']] = [
					'deposit' => (float)$old_deposit + (float)$deposit,
					'deposit_cnt' => (int)$old_deposit_cnt + (int)$deposit_cnt,
					'withdraw' => (float)$old_withdraw + (float)$withdraw,
					'withdraw_cnt' => (int)$old_withdraw_cnt + (int)$withdraw_cnt,
					'total' => (float)$old_total + (float)$total,
					'bonus' => (float)$old_bonus + (float)$bonus,
				];
			}else{
				$reports[$report['created_at']] = [
					'deposit' => (float)$deposit,
					'deposit_cnt' => (int)$deposit_cnt,
					'withdraw' => (float)$withdraw,
					'withdraw_cnt' => (int)$withdraw_cnt,
					'total' => (float)$total,
					'bonus' => (float)$bonus,
				];
			}
		}
		$deposit = array_key_exists($days,$reports) ? $reports[$days]['deposit'] : 0.00 ;
		$withdraw = array_key_exists($days,$reports) ? $reports[$days]['withdraw'] : 0.00 ;
		$bonus = array_key_exists($days,$reports) ? $reports[$days]['bonus'] : 0.00 ;
		$total = $deposit - $withdraw;
		$deposit_cnt = array_key_exists($days,$reports) ? $reports[$days]['deposit_cnt'] : 0 ;
		$withdraw_cnt= array_key_exists($days,$reports) ? $reports[$days]['withdraw_cnt'] : 0 ;
		$cnt_days = $this->CI->Report_business_benefit_model->report_business_benefit_count([
			'date_start' => $days,
			'date_end' => $days,
		]);
		if($cnt_days >= 1){
			$this->CI->Report_business_benefit_model->report_business_benefit_update_by_date([
				'process_date' =>$days,
				'deposit' =>$deposit,
				'deposit_cnt' =>$deposit_cnt,
				'withdraw' =>$withdraw,
				'withdraw_cnt' =>$withdraw_cnt,
				'total' =>$total,
				'bonus' =>$bonus,
			]);
		}else{
			$this->CI->Report_business_benefit_model->report_business_benefit_create([
				'process_date' =>$days,
				'deposit' =>$deposit,
				'deposit_cnt' =>$deposit_cnt,
				'withdraw' =>$withdraw,
				'withdraw_cnt' =>$withdraw_cnt,
				'total' =>$total,
				'bonus' =>$bonus,
			]);
		}

		if($date_time_chk_1->getTimestamp() > $date_time_chk_2->getTimestamp()){

		}else{
			$cnt_days_addon = $this->CI->Report_business_benefit_model->report_business_benefit_count([
				'date_start' => $days_addon,
				'date_end' => $days_addon,
			]);
			$bonus_addon = array_key_exists($days_addon,$reports) ? $reports[$days_addon]['bonus'] : 0.00 ;
			$deposit_addon = array_key_exists($days_addon,$reports) ? $reports[$days_addon]['deposit'] : 0.00 ;
			$withdraw_addon = array_key_exists($days_addon,$reports) ? $reports[$days_addon]['withdraw'] : 0.00 ;
			$deposit_addon_cnt = array_key_exists($days_addon,$reports) ? $reports[$days_addon]['deposit_cnt'] : 0 ;
			$withdraw_addon_cnt= array_key_exists($days_addon,$reports) ? $reports[$days_addon]['withdraw_cnt'] : 0 ;
			$total_addon = $deposit_addon - $withdraw_addon;
			if($cnt_days_addon >= 1){
				$this->CI->Report_business_benefit_model->report_business_benefit_update_by_date([
					'process_date' =>$days_addon,
					'deposit' =>$deposit_addon,
					'deposit_cnt' =>$deposit_addon_cnt,
					'withdraw' =>$withdraw_addon,
					'withdraw_cnt' =>$withdraw_addon_cnt,
					'total' =>$total_addon,
					'bonus' =>$bonus_addon,
				]);
			}else{
				$this->CI->Report_business_benefit_model->report_business_benefit_create([
					'process_date' =>$days_addon,
					'deposit' =>$deposit_addon,
					'deposit_cnt' =>$deposit_addon_cnt,
					'withdraw' =>$withdraw_addon,
					'withdraw_cnt' =>$withdraw_addon_cnt,
					'total' =>$total_addon,
					'bonus' =>$bonus_addon,
				]);
			}
		}
	}
	private function process_clear_cache(){
		date_default_timezone_set('Asia/Bangkok');
		$start_date_time = new DateTime(date('Y-m-d H:i:s'));
		$date_time_chk_1 = new DateTime(date('Y-m-d')." 01:10:00");
		$date_time_chk_2 = new DateTime(date('Y-m-d')." 01:20:00");
		$cache_data = $this->CI->cache->file->get("process_clear_cache_".date('Y-m-d'));
		if ($start_date_time->getTimestamp() >= $date_time_chk_1->getTimestamp() && $start_date_time->getTimestamp() < $date_time_chk_2->getTimestamp() && $cache_data === FALSE) {
			$this->CI->cache->file->save("process_clear_cache_".date('Y-m-d'),true,172800); // 2 days
			$cache_path =  APPPATH.'cache/';
			$handle = opendir($cache_path);
			while (($file = readdir($handle))!== FALSE)
			{
				//Leave the directory protection alone
				if ($file != '.htaccess' && $file != 'index.html' && $file != '.gitignore' && $file != "process_clear_cache_".date('Y-m-d') && $file != "process_report_business_benefit")
				{
					@unlink($cache_path.'/'.$file);
				}
			}
			closedir($handle);
		}
	}
}
