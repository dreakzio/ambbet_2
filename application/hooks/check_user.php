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
			if($this->CI->router->class != "api"){
				if($this->CI->router->class == "auth" && $this->CI->router->method == "logout"){
					session_destroy();
					$url = $this->CI->config->item('domain_name');
					$url_explode = explode(".",$url);
					if(count($url_explode) >= 3 && strpos($url_explode[0],'www') !== FALSE){
						redirect('auth');
					}else if(count($url_explode) >= 3 && strpos($url_explode[0],'www') === FALSE){
						redirect("https://".$url_explode[1].".".$url_explode[2]);
					}else{
						redirect('auth');
					}
					exit();
				}
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
				//$this->process_clear_cache();
				if(!$isAjaxRequest){

					$account = "";
					if(!isset($_SESSION['user']['cached'])){
						$_SESSION['user']['cached'] = date("Y-m-d H:i:s");
					}else{
						try{
							$hiDate = new DateTime($_SESSION['user']['cached']);
							$loDate = new DateTime(date('Y-m-d H:i:s'));
							$diff = $hiDate->diff($loDate);
							$diffInMinutes = $diff->i;
							if($diffInMinutes >= 3){
								$account = $this->CI->Account_model->account_find([
									'id' => $_SESSION['user']['id']
								]);
								if($account == "") {
									session_destroy();
									redirect('auth');
									exit();
								}else{

									$_SESSION['user'] = [
										'role' => $account['role'],
										'id' => $account['id'],
										'username' => $account['username'],
										'bank' => $account['bank'],
										'bank_number' => $account['bank_number'],
										'bank_name' => $account['bank_name'],
										'agent' => $account['agent'],
										'rank' => $account['rank'],
										'member_username' => $account['account_agent_username'],
										'member_password' => $account['account_agent_password'],
										'account_agent_id' => $account['account_agent_id'],
										'login_process_job_date' => $account['login_process_job_date'],
										'cached' => date('Y-m-d H:i:s'),
										'gg_2fa_chk' => isset($_SESSION['user']['gg_2fa_chk']) ? $_SESSION['user']['gg_2fa_chk'] : false,
										'gg_2fa_secret' => isset($_SESSION['user']['gg_2fa_secret']) ? $_SESSION['user']['gg_2fa_secret'] : "",
									];
									//ตรวจสอบการผูก user_role
									init_role($account['id']);
								}
							}
						}catch (Exception $ex){
							$_SESSION['user']['cached'] = date('Y-m-d H:i:s');
						}
					}

					if(empty($_SESSION['user']['login_process_job_date']) || is_null($_SESSION['user']['login_process_job_date'])){
						$account = $this->CI->Account_model->account_find([
							'id' => $_SESSION['user']['id']
						]);
						if($account == "") {
							session_destroy();
							redirect('auth');
							exit();
						}else{
							$_SESSION['user'] = [
								'role' => $account['role'],
								'id' => $account['id'],
								'username' => $account['username'],
								'bank' => $account['bank'],
								'bank_number' => $account['bank_number'],
								'bank_name' => $account['bank_name'],
								'agent' => $account['agent'],
								'rank' => $account['rank'],
								'member_username' => $account['account_agent_username'],
								'member_password' => $account['account_agent_password'],
								'account_agent_id' => $account['account_agent_id'],
								'login_process_job_date' => $account['login_process_job_date'],
								'cached' => date('Y-m-d H:i:s'),
								'gg_2fa_chk' => isset($_SESSION['user']['gg_2fa_chk']) ? $_SESSION['user']['gg_2fa_chk'] : false,
								'gg_2fa_secret' => isset($_SESSION['user']['gg_2fa_secret']) ? $_SESSION['user']['gg_2fa_secret'] : "",
							];

							//ตรวจสอบการผูก user_role
							init_role($account['id']);

							$login_status = $this->CI->Setting_model->setting_find([
								'name' => "login_status"
							]);
							if($login_status != "" && $login_status['value'] == "1"){
								$login_point = $this->CI->Setting_model->setting_find([
									'name' => "login_point"
								]);
								$login_point = $login_point!= "" && is_numeric($login_point['value']) ? (float)str_replace(",","",number_format($login_point['value'],2)) : 0.00;
								$u_login_point = is_numeric($account['login_point']) ? (float)str_replace(",","",number_format($account['login_point'],2)) : 0.00;
								try{
									if(is_null($_SESSION['user']['login_process_job_date']) || empty($_SESSION['user']['login_process_job_date'])){
										$this->CI->Account_model->account_update([
											'id' => $_SESSION['user']['id'],
											'login_process_job_date' => date('Y-m-d H:i:s'),
											'last_activity' => date('Y-m-d H:i:s'),
											'login_point' => $login_point,
										]);
									}else{
										$date_time_chk_1 = new DateTime($_SESSION['user']['login_process_job_date']);
										$date_time_chk_2 = new DateTime(date('Y-m-d'));
										if($date_time_chk_1->format('Y-m-d') != $date_time_chk_2->format('Y-m-d')){
											$this->CI->Account_model->account_update([
												'id' => $_SESSION['user']['id'],
												'login_process_job_date' => date('Y-m-d H:i:s'),
												'last_activity' => date('Y-m-d H:i:s'),
												'login_point' => ($login_point + $u_login_point),
											]);
										}
									}

								}catch (Exception $ex){
									$this->CI->Account_model->account_update([
										'id' => $_SESSION['user']['id'],
										'login_process_job_date' => date('Y-m-d H:i:s'),
										'last_activity' => date('Y-m-d H:i:s'),
										'login_point' => $login_point,
									]);
								}
							}
						}
					}

					if(empty($_SESSION['user']['member_username']) || is_null($_SESSION['user']['member_username'])){
						$account = !empty($account) ? $account : $this->CI->Account_model->account_find([
							'id' => $_SESSION['user']['id']
						]);
						if($account != ""){
							if($account['deleted'] == "1"){
								session_destroy();
								redirect('auth');
								exit();
							}else{
								$_SESSION['user'] = [
									'role' => $account['role'],
									'id' => $account['id'],
									'username' => $account['username'],
									'bank' => $account['bank'],
									'bank_number' => $account['bank_number'],
									'bank_name' => $account['bank_name'],
									'agent' => $account['agent'],
									'rank' => $account['rank'],
									'member_username' => $account['account_agent_username'],
									'member_password' => $account['account_agent_password'],
									'account_agent_id' => $account['account_agent_id'],
									'login_process_job_date' => $account['login_process_job_date'],
									'cached' => isset($_SESSION['user']['cached']) ? $_SESSION['user']['cached'] : date("Y-m-d H:i:s"),
									'gg_2fa_chk' => isset($_SESSION['user']['gg_2fa_chk']) ? $_SESSION['user']['gg_2fa_chk'] : false,
									'gg_2fa_secret' => isset($_SESSION['user']['gg_2fa_secret']) ? $_SESSION['user']['gg_2fa_secret'] : "",
								];

								//ตรวจสอบการผูก user_role
								init_role($account['id']);

								$this->CI->Account_model->account_update([
									'id' => $_SESSION['user']['id'],
									'last_activity' => date('Y-m-d H:i:s'),
								]);

								if($account['role'] == roleMember() && empty($account['account_agent_username']) && $this->CI->router->class != "deposit"){
									$auto_create_member = $this->CI->Setting_model->setting_find([
										'name' => 'auto_create_member'
									]);

									if($auto_create_member!="" && $auto_create_member['value'] == "0"){
										$auto_create_member_deposit_amount = $this->CI->Setting_model->setting_find([
											'name' => 'auto_create_member_deposit_amount'
										]);
										$flag_gen_user = false;
										if($auto_create_member_deposit_amount!="" && is_numeric($auto_create_member_deposit_amount['value'])){
											$sum_amount = $this->CI->Transaction_model->transaction_sum_amount([
												'account' => $_SESSION['user']['id']
											]);
											$sum_amount = is_null($sum_amount[0]['sum_amount']) ? 0 : (float)$sum_amount[0]['sum_amount'];
											if($sum_amount >= (float)$auto_create_member_deposit_amount['value']){
												$flag_gen_user = true;
											}
										}else{
											$flag_gen_user = true;
										}

										if($isAjaxRequest === FALSE){
											if($flag_gen_user){
												$this->autoGenerateUsername();
											}else{
												redirect('deposit');
												exit();
											}
										}else if($this->CI->router->class == "account" && $this->CI->router->method == "check_username_exist"){
											if($flag_gen_user){
												$this->autoGenerateUsername(false);
											}
										}
									}else if(($auto_create_member!="" && $auto_create_member['value'] == "1") || $auto_create_member==""){
										$this->autoGenerateUsername();
									}
								}else if(in_array($account['role'],[roleAdmin(),roleSuperAdmin()]) && empty($account['account_agent_username']) && $this->CI->router->class != "deposit"){
									if($isAjaxRequest === FALSE){
										$this->autoGenerateUsername();
									}
								}
							}
						}else{
							session_destroy();
							redirect('auth');
							exit();
						}
					}
				}
			}
		}
	}

	private function autoGenerateUsername($redirect= true){
		$account = $this->CI->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		if(empty($account['account_agent_username'])){
			try{
				$password = $this->CI->config->item('prefix_pass').substr(rand(10000000,99999999),2,4);
				/*$account_max_id = $this->CI->Member_model->member_max_id();
				$username = 0;
				if(!is_null($account_max_id) && isset($account_max_id['username'])){
					$account_max_id['username'] = str_replace(strtolower($this->CI->config->item('api_agent')),"",strtolower($account_max_id['username']));
					$username = (int)filter_var($account_max_id['username'], FILTER_SANITIZE_NUMBER_INT);
					$username += 1;
				}
				$username = trim($account['username']);
				$post_fix_username = str_pad( $username, 16 - strlen($this->CI->config->item('api_agent')), "0", STR_PAD_LEFT );
				$username_full = $post_fix_username;*/
				$response = $this->CI->game_api_librarie->registerPlayer($account['username'],$password,$account['username'],$account['username']);
				if(isset($response['code']) && $response['code'] == 0 && isset($response['result']) && isset($response['result']['loginName']) ){
					$account_agent_id = $this->CI->Member_model->member_create([
						'account_id' => $account['id'],
						'accid' => $account['id'],
						'username' => $response['result']['loginName'],
						'password' => $password,
					]);
					$_SESSION['user'] = [
						'role' => $account['role'],
						'id' => $account['id'],
						'username' => $account['username'],
						'bank' => $account['bank'],
						'bank_number' => $account['bank_number'],
						'bank_name' => $account['bank_name'],
						'member_username' => $response['result']['loginName'],
						'member_password' => $password,
						'account_agent_id' => $account_agent_id
					];
				}else{
				  	if($redirect){
						redirect('deposit');
						exit();
				    }
				}
			}catch (Exception $ex){

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
		$date_time_chk_1 = new DateTime(date('Y-m-d')." 01:00:00");
		$date_time_chk_2 = new DateTime(date('Y-m-d')." 01:10:00");
		$date_time_chk_all = new DateTime(date('Y-m-d', strtotime('-5 days'))." 23:59:59");
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
					$chk_unlink = false;
					$file_date = $file;
					if(
						strpos($file,"turnover_yesterday_all") !== FALSE
					){
						$file_date = str_replace("turnover_yesterday_all_","",$file_date);
						$file_date = str_replace("_","-",$file_date);
						$file_date = substr($file_date,0,10);
						$date_time_file_chk = new DateTime($file_date." 23:59:59");
						if ($date_time_file_chk->getTimestamp() < $date_time_chk_all->getTimestamp()) {
							$chk_unlink = true;
						}
					}else if(
						strpos($file,"process_commission_wheel") !== FALSE
					){
						$file_date = str_replace("process_commission_wheel_","",$file_date);
						$date_time_file_chk = new DateTime($file_date." 23:59:59");
						if ($date_time_file_chk->getTimestamp() < $date_time_chk_all->getTimestamp()) {
							$chk_unlink = true;
						}
					}else if(
						strpos($file,"process_commission_return_balance") !== FALSE
					){
						$file_date = str_replace("process_commission_return_balance_","",$file_date);
						$date_time_file_chk = new DateTime($file_date." 23:59:59");
						if ($date_time_file_chk->getTimestamp() < $date_time_chk_all->getTimestamp()) {
							$chk_unlink = true;
						}
					}else if(
						strpos($file,"process_commission_ref") !== FALSE
					){
						$file_date = str_replace("process_commission_ref_","",$file_date);
						$date_time_file_chk = new DateTime($file_date." 23:59:59");
						if ($date_time_file_chk->getTimestamp() < $date_time_chk_all->getTimestamp()) {
							$chk_unlink = true;
						}
					}else if(
						strpos($file,"process_commission_random") !== FALSE
					){
						$file_date = str_replace("process_commission_random_","",$file_date);
						$date_time_file_chk = new DateTime($file_date." 23:59:59");
						if ($date_time_file_chk->getTimestamp() < $date_time_chk_all->getTimestamp()) {
							$chk_unlink = true;
						}

					}

					if($chk_unlink){
						@unlink($cache_path.'/'.$file);
					}

				}
			}
			closedir($handle);
		}
	}
}
