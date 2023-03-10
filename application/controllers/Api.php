<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'../vendor/autoload.php';
use \Curl\Curl;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Rct567\DomQuery\DomQuery;

class Api extends CI_Controller
{
	public function __construct()
	{
		date_default_timezone_set('Asia/Bangkok');
		parent::__construct();
		$language = $this->session->userdata('language');
		$this->lang->load('text', $language);
	}
	public function indexKKsdjvi()
	{
		$refs = $this->Ref_model->ref_list_test2([
			'page' => 0,
			'per_page' => 10000,
		]);
		$user_list = [];
		foreach($refs as $ref){
			if(array_key_exists($ref['account'],$user_list)){
				$this->Ref_model->ref_deposit_update([
					'id' => $ref['id'],
					'username_to' => $user_list[$ref['account']],
				]);
			}else{
				$to_account = $this->Account_model->account_find_chk_fast([
					'id' => $ref['account'],
					'deleted_ignore' => true,
				]);
				if($to_account != ""){
					$user_list[$ref['account']] = $to_account['username'];
					$this->Ref_model->ref_deposit_update([
						'id' => $ref['id'],
						'username_to' => $to_account['username'],
					]);
				}
			}
		}
		echo "index";
	}
	public function indexJsuenncjd()
	{
		$refs = $this->Ref_model->ref_list_test([
			'page' => 0,
			'per_page' => 10000,
		]);
		$user_list = [];
		foreach($refs as $ref){
			if(array_key_exists($ref['to_account'],$user_list)){
				$this->Ref_model->ref_update([
					'id' => $ref['id'],
					'to_account_username' => $user_list[$ref['to_account']],
				]);
			}else{
				$to_account = $this->Account_model->account_find_chk_fast([
					'id' => $ref['to_account'],
					'deleted_ignore' => true,
				]);
				if($to_account != ""){
					$user_list[$ref['to_account']] = $to_account['username'];
					$this->Ref_model->ref_update([
						'id' => $ref['id'],
						'to_account_username' => $to_account['username'],
					]);
				}
			}

			if(array_key_exists($ref['from_account'],$user_list)){
				$this->Ref_model->ref_update([
					'id' => $ref['id'],
					'from_account_username' => $user_list[$ref['from_account']],
				]);
			}else{
				$from_account = $this->Account_model->account_find_chk_fast([
					'id' => $ref['from_account'],
					'deleted_ignore' => true,
				]);
				if($from_account != ""){
					$user_list[$ref['from_account']] = $from_account['username'];
					$this->Ref_model->ref_update([
						'id' => $ref['id'],
						'from_account_username' => $from_account['username'],
					]);
				}
			}
		}
		echo "index";
	}
	public function check_scam_deposit_ddiir4rf()
	{
		exit();
		$data = $this->Finance_model->finance_and_credit_history_group_by_account_chk_scam(['created_at'=>$_GET['created_at']]);
		$finances = $data['finance'];
		$credits = $data['credit'];
		$credit_logs = $data['credit_log'];
		$reports = [];
		foreach ($finances as $account => $sum_amount){
			$finance_sum  = (float)$sum_amount;
			if(array_key_exists($account,$credit_logs)){
				$finance_sum = (float)$credit_logs[$account];
			}
			if(array_key_exists($account,$credits)){
				if(round_up($finance_sum,2) > round_up((float)$credits[$account],2)){
					$user = $this->Account_model->account_find_chk_fast(['id'=>$account]);
					if($user != ""){
						$reports[] = "วันที่ [".$_GET['created_at']."] ยูส ".$user['username']." ได้ฝากเงินรวม : ".number_format($finance_sum,2)." => และมีการดำเนินการเติม (AUTO,เติมมือ) จากหน้าเครดิต : ".number_format($credits[$account],2)." => ยอดรวมไม่แมชกัน";
					}
				}
			}else{
				$user = $this->Account_model->account_find_chk_fast(['id'=>$account]);
				if($user != ""){
					$reports[] = "วันที่ [".$_GET['created_at']."] ยูส ".$user['username']." ได้ฝากเงินรวม : ".number_format($finance_sum,2)." => แต่ไม่มีการเติมเครดิต (AUTO,เติมมือ) จากหน้าเครดิตเลย";
				}
			}
		}
		echo json_encode($reports,JSON_UNESCAPED_UNICODE);
	}
	public function index()
	{
		echo "index";
	}
	public function indexsdfuiy()
	{
		$data_turnover_yesterday_all = $this->game_api_librarie->getYesterdayTurnoverAllTest();
		if(isset($data_turnover_yesterday_all['code']) && $data_turnover_yesterday_all['code'] == 0 && isset($data_turnover_yesterday_all['result']) && isset($data_turnover_yesterday_all['result']['durationDate']) && isset($data_turnover_yesterday_all['result']['remainTime'])){
			echo json_encode([
				'results' => $data_turnover_yesterday_all['result']['dataList'],
				'durationDate' => $data_turnover_yesterday_all['result']['durationDate'],
				'remainTime' => $data_turnover_yesterday_all['result']['remainTime'],
			]);
		}else{
			echo "No cache response from api";
		}
	}
	public function indexsdfuiyManWheelf()
	{
		//โบนัสวงล้อจากยอดฝากคิดจากการทำเทริน
		$wheel_amount_per_point = $this->Setting_model->setting_find([
			'name' => 'wheel_amount_per_point'
		]);
		$feature_wheel = $this->Feature_status_model->setting_find([
			'name' => 'wheel'
		]);
		if($feature_wheel!= "" && $feature_wheel['value'] == "1"){
			$user = $this->Account_model->account_find([
				'username' => $_GET['username'],
			]);
			if($user != ""){
				$finance_sum = $this->Finance_model->sum_bonus([
					'account' => $user['id'],
					'start_date' => $_GET['start_date'],
					'end_date' => $_GET['end_date'],
					'type' => 1,
					'status' => 1,
				]);
				$finance_sum = $finance_sum == "" ? 0.00 : ((float)($finance_sum['sum_amount']) > 0 ? (float)$finance_sum['sum_amount'] : 0);
				$date = $_GET['start_date'];
				if(
					$finance_sum > 0 && $wheel_amount_per_point != "" && is_numeric($wheel_amount_per_point['value']) &&
					(float)$wheel_amount_per_point['value'] > 0 && (float)$finance_sum >= (float)$wheel_amount_per_point['value'] &&
					$user['agent'] == "0" &&
					!is_null($user['account_agent_username']) &&
					!empty($user['account_agent_username'])
				){
					$date_cache = $_GET['c_start_date']."_".$_GET['c_end_date'];
					$data_turnover_yesterday_all = $this->cache->file->get('turnover_yesterday_all_'.$date_cache);
					$form_data = [];
					$form_data['username'] = $user['account_agent_username'];
					$form_data['account_agent_username'] = $user['account_agent_username'];
					$form_data['start_date'] = $date;
					$form_data['end_date'] = $date;
					if($data_turnover_yesterday_all !== false && array_key_exists(strtolower($form_data['username']),$data_turnover_yesterday_all)){
						$turnover_amount = isset($data_turnover_yesterday_all[strtolower($form_data['username'])]['amount']) ? $data_turnover_yesterday_all[strtolower($form_data['username'])]['amount'] : 0.00;
						if((float)$turnover_amount >= $finance_sum){
							$point = round((float)$finance_sum/(float)$wheel_amount_per_point['value'], 0, PHP_ROUND_HALF_UP);
							if($point > 0){
								$this->Account_model->account_update([
									'id' => $user['id'],
									'point_for_wheel' =>  (float)$user['point_for_wheel'] + $point,
								]);
								$log_wheel_id = $this->Log_wheel_model->log_wheel_create([
									'account' => $user['id'],
									'username' => $user['username'],
									'point_before' => $user['point_for_wheel'],
									'point_after' => (float)$user['point_for_wheel'] + $point,
									'point' => $point,
									'amount' => $finance_sum,
									'description' => "เงินฝากรวม [".$date." 00:00:00 - ".$date." 23:59:59] ".number_format( $finance_sum,2)." บาท/ทำเทิร์นรวมได้ ".number_format( $turnover_amount,2)." => (คำนวณจาก ".$wheel_amount_per_point['value']." บาท/ 1 เหรียญ)",
									'type' => '0', //เติม
									'status' => '1', //สำเร็จ
								]);
								echo json_encode([
									'account' => $user['id'],
									'point_before' => $user['point_for_wheel'],
									'point_after' => (float)$user['point_for_wheel'] + $point,
									'point' => $point,
									'amount' => $finance_sum,
									'description' => "เงินฝากรวม [".$date." 00:00:00 - ".$date." 23:59:59] ".number_format( $finance_sum,2)." บาท/ทำเทิร์นรวมได้ ".number_format( $turnover_amount,2)." => (คำนวณจาก ".$wheel_amount_per_point['value']." บาท/ 1 เหรียญ)",
									'type' => '0', //เติม
									'status' => '1', //สำเร็จ
								]);
							}else{
								$log_wheel_id = $this->Log_wheel_model->log_wheel_create([
									'account' => $user['id'],
									'username' => $user['username'],
									'point_before' => $user['point_for_wheel'],
									'point_after' => (float)$user['point_for_wheel'] + $point,
									'point' => $point,
									'amount' => $finance_sum,
									'description' => "เงินฝากรวม [".$date." 00:00:00 - ".$date." 23:59:59] ".number_format( $finance_sum,2)." บาท/ทำเทิร์นรวมได้ ".number_format( $turnover_amount,2)." => (คำนวณจาก ".$wheel_amount_per_point['value']." บาท/ 1 เหรียญ)",
									'type' => '0', //เติม
									'status' => '1', //สำเร็จ
								]);
								echo json_encode([
									'account' => $user['id'],
									'point_before' => $user['point_for_wheel'],
									'point_after' => (float)$user['point_for_wheel'] + $point,
									'point' => $point,
									'amount' => $finance_sum,
									'description' => "เงินฝากรวม [".$date." 00:00:00 - ".$date." 23:59:59] ".number_format( $finance_sum,2)." บาท/ทำเทิร์นรวมได้ ".number_format( $turnover_amount,2)." => (คำนวณจาก ".$wheel_amount_per_point['value']." บาท/ 1 เหรียญ)",
									'type' => '0', //เติม
									'status' => '1', //สำเร็จ
								]);
							}
						}else{
							$log_wheel_id = $this->Log_wheel_model->log_wheel_create([
								'account' => $user['id'],
								'username' => $user['username'],
								'point_before' => $user['point_for_wheel'],
								'point_after' => (float)$user['point_for_wheel'],
								'point' => 0.00,
								'amount' => $finance_sum,
								'description' => "เงินฝากรวม [".$date." 00:00:00 - ".$date." 23:59:59] ".number_format( $finance_sum,2)." บาท/ทำเทิร์นรวมได้ ".number_format( $turnover_amount,2)." => (คำนวณจาก ".$wheel_amount_per_point['value']." บาท/ 1 เหรียญ)",
								'type' => '0', //เติม
								'status' => '1', //สำเร็จ
							]);
							echo json_encode([
								'account' => $user['id'],
								'point_before' => $user['point_for_wheel'],
								'point_after' => (float)$user['point_for_wheel'],
								'point' => 0.00,
								'amount' => $finance_sum,
								'description' => "เงินฝากรวม [".$date." 00:00:00 - ".$date." 23:59:59] ".number_format( $finance_sum,2)." บาท/ทำเทิร์นรวมได้ ".number_format( $turnover_amount,2)." => (คำนวณจาก ".$wheel_amount_per_point['value']." บาท/ 1 เหรียญ)",
								'type' => '0', //เติม
								'status' => '1', //สำเร็จ
							]);
						}
					}else{
						echo "Empty Cache : ".json_encode($data_turnover_yesterday_all);
					}
				}else{
					$log_wheel_id = $this->Log_wheel_model->log_wheel_create([
						'account' => $user['id'],
						'username' => $user['username'],
						'point_before' => $user['point_for_wheel'],
						'point_after' => (float)$user['point_for_wheel'],
						'point' => 0.00,
						'amount' => $finance_sum,
						'description' => "เงินฝากรวม [".$date." 00:00:00 - ".$date." 23:59:59] ".number_format( $finance_sum,2)." บาท => (คำนวณจาก ".$wheel_amount_per_point['value']." บาท/ 1 เหรียญ) / ยูสนี้เป็น Agent : [".($user['agent'] == "1" ? "ใช่" : "ไม่ใช่")."] / ยังไม่ได้รับยูส : [".(empty($user['account_agent_username']) ? "ใช่" : "ไม่ใช่")."]",
						'type' => '0', //เติม
						'status' => '1', //สำเร็จ
					]);
					echo "User ".$_GET['username']." empty account_agent_username,agent and finance sum : ".number_format($finance_sum,2);
				}
			}else{
				echo "Empty user : ".$_GET['username'];
			}
		}else{
			echo "Wheel status : inactive";
		}
	}
	public function process_commission_ref(){
		//Max execute 50
		set_time_limit(60*50);
		if(isset($_GET['api_token']) && trim($_GET['api_token']) == $this->config->item('web_api_token')){

			//ให้บอททำงานตั้งแต่ 11:30 - 18:00
			date_default_timezone_set('Asia/Bangkok');
			$start_date_time = new DateTime(date('Y-m-d H:i:s'));
			$date_time_chk_1 = new DateTime(date('Y-m-d')." 04:00:00");
			$date_time_chk_2 = new DateTime(date('Y-m-d')." 11:40:00");

			if ($start_date_time->getTimestamp() >= $date_time_chk_1->getTimestamp() && $start_date_time->getTimestamp() < $date_time_chk_2->getTimestamp() ) {
				$chk_process = false;
				$date_cache = date('Y_m_d', strtotime('-2 days'))."_".date('Y_m_d', strtotime('-1 days'));
				$data_turnover_yesterday_all = $this->cache->file->get('turnover_yesterday_all_'.$date_cache);
				if($data_turnover_yesterday_all === FALSE){
					$data_turnover_yesterday_all = $this->game_api_librarie->getYesterdayTurnoverAll();
					if(!is_null($data_turnover_yesterday_all) && isset($data_turnover_yesterday_all['durationDate'])){
						$data  = trim($data_turnover_yesterday_all['durationDate']);
						if(!empty($data)){
							$data_explode_date = explode(" - ",$data);
							$start_date_time_split = explode(" ",trim($data_explode_date[0]));
							$start_date_split = explode("/",trim($start_date_time_split[0]));
							$start_date = $start_date_split[2].'_'.$start_date_split[1]."_".$start_date_split[0];

							$end_date_time_split = explode(" ",trim($data_explode_date[1]));
							$end_date_split = explode("/",trim($end_date_time_split[0]));
							$end_date = $end_date_split[2].'_'.$end_date_split[1]."_".$end_date_split[0];

							if($date_cache == $start_date."_".$end_date){
								$chk_process = true;
								$data_turnover_yesterday_all = isset($data_turnover_yesterday_all['dataList']) ? $data_turnover_yesterday_all['dataList'] : [];
								$data_turnover_yesterday_all_new = [];
								foreach($data_turnover_yesterday_all as $data_turnover_yesterday){
									if(!array_key_exists(strtolower($data_turnover_yesterday['member']['username']),$data_turnover_yesterday_all_new)){
										$data_turnover_yesterday_all_new[strtolower($data_turnover_yesterday['member']['username'])] = $data_turnover_yesterday;
									}
								}
								$data_turnover_yesterday_all = $data_turnover_yesterday_all_new;
								$this->cache->file->save('turnover_yesterday_all_'.$date_cache,$data_turnover_yesterday_all_new, 60*30);
							}else{
								$data_turnover_yesterday_all = null;
								$this->cache->file->save('turnover_yesterday_all_'.$date_cache,null, 60*30);
							}
						}else{
							$data_turnover_yesterday_all = null;
							$this->cache->file->save('turnover_yesterday_all_'.$date_cache,null, 60*30);
						}
					}else{
						$data_turnover_yesterday_all = null;
						$this->cache->file->save('turnover_yesterday_all_'.$date_cache,null, 60*30);
					}
				}else{
					if(!is_null($data_turnover_yesterday_all)){
						$chk_process = true;
					}
				}
				if($chk_process){
					$feature_bonus_return_balance_winlose = $this->Feature_status_model->setting_find([
						'name' => 'bonus_return_balance_winlose'
					]);
					$feature_bonus_aff_turnover_and_winlose = $this->Feature_status_model->setting_find([
						'name' => 'bonus_aff_turnover_and_winlose'
					]);
					$feature_bonus_aff_turnover_and_winlose_step2 = $this->Feature_status_model->setting_find([
						'name' => 'bonus_aff_turnover_and_winlose_step2'
					]);

					$date = date('Y-m-d', strtotime('-1 days'));
					//โบนัสคืนยอดเสียให้ตัวเอง
					if($feature_bonus_return_balance_winlose!= "" && $feature_bonus_return_balance_winlose['value'] == "1"){
						$ref_return_balance_status = $this->Setting_model->setting_find([
							'name' => 'ref_return_balance_status'
						]);
						if($ref_return_balance_status!= "" && $ref_return_balance_status['value'] == "1"){
							$ref_return_balance_rank1_deposit_min = $this->Setting_model->setting_find([
								'name' => 'ref_return_balance_rank1_deposit_min'
							]);
							$ref_return_balance_rank1_deposit_min = $ref_return_balance_rank1_deposit_min != "" && is_numeric($ref_return_balance_rank1_deposit_min['value']) ? $ref_return_balance_rank1_deposit_min['value'] : 0.00;
							$ref_return_balance_rank2_deposit_min = $this->Setting_model->setting_find([
								'name' => 'ref_return_balance_rank2_deposit_min'
							]);
							$ref_return_balance_rank2_deposit_min = $ref_return_balance_rank2_deposit_min != "" && is_numeric($ref_return_balance_rank2_deposit_min['value']) ? $ref_return_balance_rank2_deposit_min['value'] : 0.00;
							$ref_return_balance_rank3_deposit_min = $this->Setting_model->setting_find([
								'name' => 'ref_return_balance_rank3_deposit_min'
							]);
							$ref_return_balance_rank3_deposit_min = $ref_return_balance_rank3_deposit_min != "" && is_numeric($ref_return_balance_rank3_deposit_min['value']) ? $ref_return_balance_rank3_deposit_min['value'] : 0.00;
							$search_account = [
								"process_return_balance_job_date" => $date." 00:00:00",
								"limit" => 50,
							];

							$cache_data = $this->cache->file->get("process_commission_return_balance_".date('Y-m-d'));
							if($cache_data !== FALSE){
								$account_return_balance_list = [];
							}else{
								$account_return_balance_list = $this->Account_model->account_list_for_process_return_balance($search_account);
							}
							if(count($account_return_balance_list) == 0){
								$this->cache->file->save("process_commission_return_balance_".date('Y-m-d'),true,172800); // 2 days
							}


							foreach($account_return_balance_list as $account_return_balance){

								$this->Account_model->account_update([
									'id' => $account_return_balance['id'],
									'process_return_balance_job_date' => $date." ".date('H:i:s')
								]);

								if (
									$account_return_balance['agent'] == "0" &&
									!is_null($account_return_balance['account_agent_username']) &&
									!empty($account_return_balance['account_agent_username']) &&
									$account_return_balance['is_active_return_balance'] == "1"
								) {
									$form_data_return_balance = [];
									$form_data_return_balance['username'] = $account_return_balance['account_agent_username'];
									$form_data_return_balance['account_agent_username'] = $account_return_balance['account_agent_username'];
									$form_data_return_balance['start_date'] = $date;
									$form_data_return_balance['end_date'] = $date;
									//$winlose_return_balance_amount = $this->game_api_librarie->getYesterdayWinLose($form_data_return_balance);
									if(array_key_exists(strtolower($form_data_return_balance['account_agent_username']),$data_turnover_yesterday_all)){
										$winlose_return_balance_amount = isset($data_turnover_yesterday_all[strtolower($form_data_return_balance['account_agent_username'])]['memberWinLose']) ? $data_turnover_yesterday_all[strtolower($form_data_return_balance['account_agent_username'])]['memberWinLose'] : 0.00;
										$turnover_return_balance_amount = isset($data_turnover_yesterday_all[strtolower($form_data_return_balance['account_agent_username'])]['amount']) ? $data_turnover_yesterday_all[strtolower($form_data_return_balance['account_agent_username'])]['amount'] : 0.00;
										if($turnover_return_balance_amount >= 1){
											$rank_point_sum = 	isset($account_return_balance['rank_point_sum']) && is_numeric($account_return_balance['rank_point_sum']) ? (float)$account_return_balance['rank_point_sum'] : 0.00;
											$finance_sum = $this->Finance_model->sum_bonus([
												'account' => $account_return_balance['id'],
												'start_date' => $date,
												'end_date' => $date,
												'type' => 1,
												'status' => 1,
											]);
											$finance_promotion_sum = $finance_sum == "" ? 0.00 : ((float)($finance_sum['sum_promotion_amount']) > 0 ? (float)$finance_sum['sum_promotion_amount'] : 0.00);
											if($finance_promotion_sum >= 1 && $turnover_return_balance_amount >= $finance_promotion_sum){
												$finance_sum_amount = (float)($finance_sum['sum_amount']);
												$account_return_balance['rank_point_sum'] = ($rank_point_sum+$finance_sum_amount);
												if((float)$account_return_balance['rank_point_sum'] >= $ref_return_balance_rank1_deposit_min){
													$account_return_balance['rank'] = "1";
												}
												if((float)$account_return_balance['rank_point_sum'] >= $ref_return_balance_rank2_deposit_min){
													$account_return_balance['rank'] = "2";
												}
												if((float)$account_return_balance['rank_point_sum'] >= $ref_return_balance_rank3_deposit_min){
													$account_return_balance['rank'] = "3";
												}
												$this->Account_model->account_update([
													'id' => $account_return_balance['id'],
													'rank' => $account_return_balance['rank'],
													'rank_point_sum' =>$account_return_balance['rank_point_sum']
												]);

											}
										}
										$chk_winlose_return_balance = $this->checkWinLoseReturnTransfer($winlose_return_balance_amount,$account_return_balance,$account_return_balance);
									}else{
										$chk_winlose_return_balance = $this->checkWinLoseReturnTransfer(0.00,$account_return_balance,$account_return_balance);
									}
								}
							}
						}
					}

					//โบนัสวงล้อจากยอดฝากคิดจากการทำเทริน
					/*$wheel_amount_per_point = $this->Setting_model->setting_find([
						'name' => 'wheel_amount_per_point'
					]);
					$feature_wheel = $this->Feature_status_model->setting_find([
						'name' => 'wheel'
					]);
					if($feature_wheel!= "" && $feature_wheel['value'] == "1"){

						$search = [
							"wheel_process_job_date" => $date." 00:00:00",
							"limit" => 50,
						];

						$cache_data = $this->cache->file->get("process_commission_wheel_".date('Y-m-d'));
						if($cache_data !== FALSE){
							$user_list = [];
						}else{
							$user_list = $this->Account_model->user_list_for_process_wheel($search);
						}
						if(count($user_list) == 0){
							$this->cache->file->save("process_commission_wheel_".date('Y-m-d'),true,172800); // 2 days
						}


						foreach($user_list as $user){

							$this->Account_model->account_update([
								'id' => $user['id'],
								'wheel_process_job_date' => $date." ".date('H:i:s')
							]);

							$finance_sum = $this->Finance_model->sum_bonus([
								'account' => $user['id'],
								'start_date' => $date,
								'end_date' => $date,
								'type' => 1,
								'status' => 1,
							]);
							$finance_sum = $finance_sum == "" ? 0.00 : ((float)($finance_sum['sum_amount']) > 0 ? (float)$finance_sum['sum_amount'] : 0);

							if(
								$finance_sum > 0 && $wheel_amount_per_point != "" && is_numeric($wheel_amount_per_point['value']) &&
								(float)$wheel_amount_per_point['value'] > 0 && (float)$finance_sum >= (float)$wheel_amount_per_point['value'] &&
								$user['agent'] == "0" &&
								!is_null($user['account_agent_username']) &&
								!empty($user['account_agent_username'])
							){
								$form_data = [];
								$form_data['username'] = $user['account_agent_username'];
								$form_data['account_agent_username'] = $user['account_agent_username'];
								$form_data['start_date'] = $date;
								$form_data['end_date'] = $date;
								if(array_key_exists(strtolower($form_data['username']),$data_turnover_yesterday_all)){
									$turnover_amount = isset($data_turnover_yesterday_all[strtolower($form_data['username'])]['amount']) ? $data_turnover_yesterday_all[strtolower($form_data['username'])]['amount'] : 0.00;
									if((float)$turnover_amount >= $finance_sum){
										$point = round((float)$finance_sum/(float)$wheel_amount_per_point['value'], 0, PHP_ROUND_HALF_UP);
										if($point > 0){
											$this->Account_model->account_update([
												'id' => $user['id'],
												'point_for_wheel' =>  (float)$user['point_for_wheel'] + $point,
											]);
											$log_wheel_id = $this->Log_wheel_model->log_wheel_create([
												'account' => $user['id'],
												'point_before' => $user['point_for_wheel'],
												'point_after' => (float)$user['point_for_wheel'] + $point,
												'point' => $point,
												'amount' => $finance_sum,
												'description' => "เงินฝากรวม [".$date." 00:00:00 - ".$date." 23:59:59] ".number_format( $finance_sum,2)." บาท/ทำเทิร์นรวมได้ ".number_format( $turnover_amount,2)." => (คำนวณจาก ".$wheel_amount_per_point['value']." บาท/ 1 เหรียญ)",
												'type' => '0', //เติม
												'status' => '1', //สำเร็จ
											]);
										}else{
											$log_wheel_id = $this->Log_wheel_model->log_wheel_create([
												'account' => $user['id'],
												'point_before' => $user['point_for_wheel'],
												'point_after' => (float)$user['point_for_wheel'] + $point,
												'point' => $point,
												'amount' => $finance_sum,
												'description' => "เงินฝากรวม [".$date." 00:00:00 - ".$date." 23:59:59] ".number_format( $finance_sum,2)." บาท/ทำเทิร์นรวมได้ ".number_format( $turnover_amount,2)." => (คำนวณจาก ".$wheel_amount_per_point['value']." บาท/ 1 เหรียญ)",
												'type' => '0', //เติม
												'status' => '1', //สำเร็จ
											]);
										}
									}else{
										$log_wheel_id = $this->Log_wheel_model->log_wheel_create([
											'account' => $user['id'],
											'point_before' => $user['point_for_wheel'],
											'point_after' => (float)$user['point_for_wheel'],
											'point' => 0.00,
											'amount' => $finance_sum,
											'description' => "เงินฝากรวม [".$date." 00:00:00 - ".$date." 23:59:59] ".number_format( $finance_sum,2)." บาท/ทำเทิร์นรวมได้ ".number_format( $turnover_amount,2)." => (คำนวณจาก ".$wheel_amount_per_point['value']." บาท/ 1 เหรียญ)",
											'type' => '0', //เติม
											'status' => '1', //สำเร็จ
										]);
									}
								}
							}else{
								$log_wheel_id = $this->Log_wheel_model->log_wheel_create([
									'account' => $user['id'],
									'point_before' => $user['point_for_wheel'],
									'point_after' => (float)$user['point_for_wheel'],
									'point' => 0.00,
									'amount' => $finance_sum,
									'description' => "เงินฝากรวม [".$date." 00:00:00 - ".$date." 23:59:59] ".number_format( $finance_sum,2)." บาท => (คำนวณจาก ".$wheel_amount_per_point['value']." บาท/ 1 เหรียญ) / ยูสนี้เป็น Agent : [".($user['agent'] == "1" ? "ใช่" : "ไม่ใช่")."] / ยังไม่ได้รับยูส : [".(empty($user['account_agent_username']) ? "ใช่" : "ไม่ใช่")."]",
									'type' => '0', //เติม
									'status' => '1', //สำเร็จ
								]);
							}

						}

					}*/

					//โบนัสแนะนำเพื่อนจากยอด (เทิร์นโอเวอร์/เล่นเสีย) & โบนัสแนะนำเพื่อนจากยอด (เทิร์นโอเวอร์/เล่นเสีย) ขั้น 2
					if($feature_bonus_aff_turnover_and_winlose!= "" && $feature_bonus_aff_turnover_and_winlose['value'] == "1"){


						$search = [
							"ref_process_job_date" => $date." 00:00:00",
							"limit" => 20,
						];

						$cache_data = $this->cache->file->get("process_commission_ref_".date('Y-m-d'));
						if($cache_data !== FALSE){
							$ref_list = [];
						}else{
							$ref_list = $this->Ref_model->ref_list_for_process_commission($search);
						}
						if(count($ref_list) == 0){
							$this->cache->file->save("process_commission_ref_".date('Y-m-d'),true,172800); // 2 days
						}


						$ref_bonus_type = $this->Setting_model->setting_find([
							'name' => 'ref_bonus_type'
						]);
						foreach($ref_list as $ref){

							$this->Ref_model->ref_update([
								'id' => $ref['id'],
								'ref_process_job_date' => $date." ".date('H:i:s')
							]);

							$to_account = $this->Account_model->account_find([
								'id' => $ref['to_account']
							]);
							$from_account = $this->Account_model->account_find([
								'id' => $ref['from_account']
							]);
							if (
								$to_account!="" && $from_account != "" && $to_account['agent'] == "0" && $from_account['agent'] == "0" &&
								!empty($from_account['account_agent_username']) && !empty($to_account['account_agent_username'])
							) {

								if($ref_bonus_type != "" && $ref_bonus_type['value'] == "1"){
									$form_data = [];
									$form_data['username'] = $to_account['account_agent_username'];
									$form_data['account_agent_username'] = $to_account['account_agent_username'];
									$form_data['start_date'] = $date;
									$form_data['end_date'] = $date;
									//$winlose_amount = $this->game_api_librarie->getYesterdayWinLose($form_data);
									if(array_key_exists(strtolower($form_data['account_agent_username']),$data_turnover_yesterday_all)){
										$winlose_amount = isset($data_turnover_yesterday_all[strtolower($form_data['account_agent_username'])]['memberWinLose']) ? $data_turnover_yesterday_all[strtolower($form_data['account_agent_username'])]['memberWinLose'] : 0.00;
										$chk_winlose = $this->checkWinLose($winlose_amount,$from_account,$to_account);
									}else{
										$chk_winlose = $this->checkWinLose(0.00,$from_account,$to_account);
									}

								}else{
									$form_data = [];
									$form_data['username'] = $to_account['account_agent_username'];
									$form_data['date_begin'] = $date;
									$form_data['date_end'] = $date;
									$form_data = member_turn_data($form_data);
									$turnover_amount = 0.00;
									/*$turnover_data = $this->game_api_librarie->getTurn($form_data);
									foreach(game_code_list() as $game_code){
										if(isset($turnover_data[$game_code])){
											$turnover_amount += (float)$turnover_data[$game_code]['amount'];
										}
									}*/
									if(array_key_exists(strtolower($form_data['username']),$data_turnover_yesterday_all)){
										$turnover_amount = isset($data_turnover_yesterday_all[strtolower($form_data['username'])]['amount']) ? $data_turnover_yesterday_all[strtolower($form_data['username'])]['amount'] : 0.00;
									}
									$chk_turnover = $this->checkTurnOver($turnover_amount,$from_account,$to_account);
								}
								//Check สถานะเปิดโบนัสขั้น 2
								$ref_step2_status = $this->Setting_model->setting_find([
									'name' => 'ref_step2_status'
								]);
								if($ref_step2_status!= "" && $ref_step2_status['value'] == "1" && $feature_bonus_aff_turnover_and_winlose_step2!= "" && $feature_bonus_aff_turnover_and_winlose_step2['value'] == "1"){
									$ref_step2_list = $this->Ref_model->ref_no_join_no_paginate_commission_list(['from_account'=>$to_account['id']]);
									foreach($ref_step2_list as $ref_step2){
										$to_account_step2 = $this->Account_model->account_find([
											'id' => $ref_step2['to_account']
										]);
										$from_account_step2 = $this->Account_model->account_find([
											'id' => $ref_step2['from_account']
										]);
										if (
											$to_account_step2!="" && $from_account_step2 != "" && $to_account_step2['agent'] == "0" && $from_account_step2['agent'] == "0" &&
											!empty($from_account_step2['account_agent_username']) && !empty($to_account_step2['account_agent_username'])
										) {

											if($ref_bonus_type != "" && $ref_bonus_type['value'] == "1"){
												$form_data_step2 = [];
												$form_data_step2['username'] = $to_account_step2['account_agent_username'];
												$form_data_step2['date_begin'] = $date;
												$form_data_step2['date_end'] = $date;
												$form_data_step2 = member_turn_data($form_data_step2);
												$turnover_amount_step2 = 0.00;
												/*$turnover_data_step2 = $this->game_api_librarie->getTurn($form_data_step2);
												foreach(game_code_list() as $game_code){
													if(isset($turnover_data_step2[$game_code])){
														$turnover_amount_step2 += (float)$turnover_data_step2[$game_code]['amount'];
													}
												}*/
												if(array_key_exists(strtolower($form_data_step2['username']),$data_turnover_yesterday_all)){
													$turnover_amount_step2 = isset($data_turnover_yesterday_all[strtolower($form_data_step2['username'])]['amount']) ? $data_turnover_yesterday_all[strtolower($form_data_step2['username'])]['amount'] : 0.00;
												}
												$chk_turnover_step2 = $this->checkTurnOverStep2($turnover_amount_step2,$from_account,$from_account_step2,$to_account_step2);
											}else{
												$form_data_step2 = [];
												$form_data_step2['username'] = $to_account_step2['account_agent_username'];
												$form_data_step2['account_agent_username'] = $to_account_step2['account_agent_username'];
												$form_data_step2['start_date'] = $date;
												$form_data_step2['end_date'] = $date;
												//$winlose_amount_step2 = $this->game_api_librarie->getYesterdayWinLose($form_data_step2);
												if(array_key_exists(strtolower($form_data_step2['account_agent_username']),$data_turnover_yesterday_all)){
													$winlose_amount_step2 = isset($data_turnover_yesterday_all[strtolower($form_data_step2['account_agent_username'])]['memberWinLose']) ? $data_turnover_yesterday_all[strtolower($form_data_step2['account_agent_username'])]['memberWinLose'] : 0.00;
													$chk_winlose_step2 = $this->checkWinLoseStep2($winlose_amount_step2,$from_account,$from_account_step2,$to_account_step2);
												}else{
													$chk_winlose_step2 = $this->checkWinLoseStep2(0.00,$from_account,$from_account_step2,$to_account_step2);
												}

											}
										}
									}
								}

							}
						}

					}


				}
			}
			echo json_encode([
				"success" => true,
				"message" => "Running..."
			]);
			exit();
		}
		echo json_encode([
			"success" => false,
			"message" => "Invalid Params"
		]);
		exit();
	}

	private function checkWinLoseReturnTransfer($winlose_amount = 0,$from_account,$to_account){
		$start_date = date('Y-m-d', strtotime('-2 days'))." 11:00";
		$end_date = date('Y-m-d', strtotime('-1 days'))." 11:00";
		if(!isset($to_account['rank']) || is_null($to_account['rank'])){
			$ref_percent = $this->Setting_model->setting_find([
				'name' => 'ref_return_balance_percent'
			]);
			$ref_turn = $this->Setting_model->setting_find([
				'name' => 'ref_return_balance_turn'
			]);
			$ref_max = $this->Setting_model->setting_find([
				'name' => 'ref_return_balance_max'
			]);
		}else{
			if($to_account['rank'] == "1"){
				$ref_percent = $this->Setting_model->setting_find([
					'name' => 'ref_return_balance_rank1_percent'
				]);
				$ref_turn = $this->Setting_model->setting_find([
					'name' => 'ref_return_balance_rank1_turn'
				]);
				$ref_max = $this->Setting_model->setting_find([
					'name' => 'ref_return_balance_rank1_max'
				]);
			}else if($to_account['rank'] == "2"){
				$ref_percent = $this->Setting_model->setting_find([
					'name' => 'ref_return_balance_rank2_percent'
				]);
				$ref_turn = $this->Setting_model->setting_find([
					'name' => 'ref_return_balance_rank2_turn'
				]);
				$ref_max = $this->Setting_model->setting_find([
					'name' => 'ref_return_balance_rank2_max'
				]);
			}else if($to_account['rank'] == "3"){
				$ref_percent = $this->Setting_model->setting_find([
					'name' => 'ref_return_balance_rank3_percent'
				]);
				$ref_turn = $this->Setting_model->setting_find([
					'name' => 'ref_return_balance_rank3_turn'
				]);
				$ref_max = $this->Setting_model->setting_find([
					'name' => 'ref_return_balance_rank3_max'
				]);
			}else{
				$ref_percent = $this->Setting_model->setting_find([
					'name' => 'ref_return_balance_rank1_percent'
				]);
				$ref_turn = $this->Setting_model->setting_find([
					'name' => 'ref_return_balance_rank1_turn'
				]);
				$ref_max = $this->Setting_model->setting_find([
					'name' => 'ref_return_balance_rank1_max'
				]);
			}
		}

		$ref_percent = $ref_percent!=""?$ref_percent['value']:0;
		$ref_max = $ref_max!="" && is_numeric($ref_max['value'])?(float)$ref_max['value']:50000.00;
		$ref_turn = $ref_turn!="" && $ref_turn['value'] != "" ?$ref_turn['value']:0;

		if((strpos($winlose_amount,"-") !== false || (float)$winlose_amount < 0) && number_format((float)$winlose_amount,2) != "-99,999,999.99"){
			$winlose_amount = (float)str_replace("-","",$winlose_amount);
			if($winlose_amount >= 1 && $winlose_amount < 99999999.99){
				$sum_amount = round_up(($winlose_amount*$ref_percent)/100,2);

				if($sum_amount  >= 1 && $sum_amount <= $ref_max){
					$point_for_return_balance_new = $to_account['point_for_return_balance'];
					$point_for_return_balance_new = is_numeric($point_for_return_balance_new) ? (float)$point_for_return_balance_new + (float)$sum_amount : 0.00;

					$this->Account_model->account_update([
						'id' => $to_account['id'],
						'point_for_return_balance' => $point_for_return_balance_new,
					]);

					$log_return_balance_id = $this->Log_return_balance_model->log_return_balance_create([
						'account' => $to_account['id'],
						'username' => $to_account['username'],
						'point_before' => is_numeric($to_account['point_for_return_balance']) ? $to_account['point_for_return_balance'] : 0.00,
						'point_after' => $point_for_return_balance_new,
						'point' => $sum_amount,
						'description' => "เพิ่มแต้ม (โบนัสคืนยอดเสียให้ตัวเอง) : ".number_format($sum_amount,2)." จากยอดเล่นเสีย -".number_format($winlose_amount,2)." [วันที่ ".$start_date." - ".$end_date.", คิดจากเปอร์เซ็นต์ (".number_format($ref_percent,2).")]",
						'type' => '0', //เพิ่ม
						'status' => '1', //สำเร็จ
					]);

					$this->Ref_model->ref_deposit_create([
						'account' => $to_account['id'],
						'finance' => null,
						'type' => 2,
						'percent' => $ref_percent,
						'turnover_amount' => $winlose_amount,
						'username_from' => $to_account['username'],
						'turn' => $ref_turn,
						'sum_amount' => $sum_amount
					]);

					//Add to main wallet
					/*$form_data = [];
					$form_data["account_agent_username"] = $to_account['account_agent_username'];
					$form_data["amount"] = $sum_amount;
					$form_data = member_credit_data($form_data);

					//เพิ่ม Logs
					$credit_before = $this->remaining_credit($to_account);
					$log_deposit_withdraw_id = $this->Log_deposit_withdraw_model->log_deposit_withdraw_create([
						'account' => $to_account['id'],
						'amount' => $form_data["amount"],
						'amount_before' => $credit_before,
						'type' => '1', //ฝาก
						'description' => 'เพิ่มเครดิต (โบนัสคืนยอดเสียให้ตัวเอง)',
						'admin' =>'0',
					]);

					$response = $this->game_api_librarie->deposit($form_data);
					if (isset($response['ref'])) {
						$turn_over = $to_account['turn_over'] != "" && !is_null($to_account['turn_over']) ? $to_account['turn_over'] : 0;
						$turn_date = $to_account['turn_date'];
						$turn_before = $to_account['turn_before'] != "" && !is_null($to_account['turn_before']) ? $to_account['turn_before'] : 0;

						foreach (game_code_list() as $game_code){
							${'turn_over_'.strtolower($game_code)} = $to_account['turn_over_'.strtolower($game_code)] != "" && !is_null($to_account['turn_over_'.strtolower($game_code)]) ? $to_account['turn_over_'.strtolower($game_code)] : 0;
						}

						if ($turn_date=='') {
							if (
								strtotime(date('Y-m-d H:i'))>=strtotime(date('Y-m-d')." 11:00")
								&&
								strtotime(date('Y-m-d H:i')) <=strtotime(date('Y-m-d')." 23:59")
							) {
								$turn_date = date('Y-m-d');
							} else {
								$turn_date = date('Y-m-d', strtotime('-1 days'));
							}
						} else {
							$turn_date = $to_account['turn_date'];
						}

						if ($ref_turn>0) {
							if ($to_account['turn_over']>0) {
								$turn_over = $turn_over+($sum_amount*$ref_turn);
							} else {
								$turn_over = ($sum_amount*$ref_turn);
							}

						}
						$data_update_member = [
							'id' => $to_account['id'],
							'turn_before' => $turn_before,
							'turn_over' => $turn_over,
							'turn_date' => $turn_date,
						];
						if ($ref_turn>0) {
							foreach (game_code_list() as $game_code){
								if((float)${'turn_over_'.strtolower($game_code)} >0){
									${'turn_over_'.strtolower($game_code)} = (float)${'turn_over_'.strtolower($game_code)}+($sum_amount*$ref_turn);
								}else{
									${'turn_over_'.strtolower($game_code)} = ($sum_amount*$ref_turn);
								}
								$data_update_member['turn_over_'.strtolower($game_code)] = ${'turn_over_'.strtolower($game_code)};
							}
						}
						$this->Account_model->account_update($data_update_member);
						$this->Ref_model->ref_deposit_create([
							'account' => $to_account['id'],
							'finance' => null,
							'type' => 2,
							'percent' => $ref_percent,
							'turnover_amount' => $winlose_amount,
							'username_from' => $to_account['username'],
							'turn' => $ref_turn,
							'sum_amount' => $sum_amount
						]);
						$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
							'id' => $log_deposit_withdraw_id
						]);
						if($log_deposit_withdraw!=""){
							$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
								'id' => $log_deposit_withdraw_id,
								'description' => $log_deposit_withdraw['description']." | ทำรายการสำเร็จ",
							]);
						}
					}else{
						$error_message = isset($response['code']) && !empty($response['code']) ? "#".$response['code'] : 'ทำรายการไม่สำเร็จ';

						$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
							'id' => $log_deposit_withdraw_id
						]);
						if($log_deposit_withdraw!=""){
							$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
								'id' => $log_deposit_withdraw_id,
								'description' => $log_deposit_withdraw['description']." | ".$error_message,
							]);
						}
					}*/
				}else{
					//Add to main wallet
					/*$form_data = [];
					$form_data["account_agent_username"] = $to_account['account_agent_username'];
					$form_data["amount"] = $sum_amount;
					$form_data = member_credit_data($form_data);
					$credit_before = $this->remaining_credit($to_account);
					$log_deposit_withdraw_id = $this->Log_deposit_withdraw_model->log_deposit_withdraw_create([
						'account' => $to_account['id'],
						'amount' => $form_data["amount"],
						'amount_before' => $credit_before,
						'type' => '1', //ฝาก
						'description' => 'เพิ่มเครดิต (โบนัสคืนยอดเสียให้ตัวเอง), ไม่เพิ่มเพราะต้องมากกว่าหรือเท่ากับ 1 ขึ้นไปและต้องน้อยกว่าหรือเท่ากับ 50,000',
						'admin' =>'0',
					]);*/
					$txt_rank = "";
					if(!empty($to_account['rank'])){
						if($to_account['rank'] == "1"){
							$txt_rank = "ยูส RANK : Member ";
						}else if($to_account['rank'] == "2"){
							$txt_rank = "ยูส RANK : Silver ";
						}else if($to_account['rank'] == "3"){
							$txt_rank = "ยูส RANK : Gold ";
						}
					}
					if($sum_amount  >= 1 && $sum_amount > $ref_max){
						$sum_amount_new = $ref_max;
						$point_for_return_balance_new = $to_account['point_for_return_balance'];
						$point_for_return_balance_new = is_numeric($point_for_return_balance_new) ? (float)$point_for_return_balance_new + (float)$sum_amount_new : 0.00;

						$this->Account_model->account_update([
							'id' => $to_account['id'],
							'point_for_return_balance' => $point_for_return_balance_new,
						]);

						$log_return_balance_id = $this->Log_return_balance_model->log_return_balance_create([
							'account' => $to_account['id'],
							'username' => $to_account['username'],
							'point_before' => is_numeric($to_account['point_for_return_balance']) ? $to_account['point_for_return_balance'] : 0.00,
							'point_after' => $point_for_return_balance_new,
							'point' => $sum_amount_new,
							'description' => "เพิ่มแต้ม (โบนัสคืนยอดเสียให้ตัวเอง) : ".number_format($sum_amount,2)." จากยอดเล่นเสีย -".number_format($winlose_amount,2)." [วันที่ ".$start_date." - ".$end_date.", คิดจากเปอร์เซ็นต์ (".number_format($ref_percent,2).", ".$txt_rank."จะได้สูงสุดเพียง ".number_format($ref_max,2).")]",
							//'description' => "เพิ่มแต้ม (โบนัสคืนยอดเสียให้ตัวเอง) : ".number_format($sum_amount,2)." จากยอดเล่นเสีย -".number_format($winlose_amount,2)." [วันที่ ".$start_date." - ".$end_date.", คิดจากเปอร์เซ็นต์ (".number_format($ref_percent,2).")]",
							'type' => '0', //เพิ่ม
							'status' => '1', //สำเร็จ
						]);

						$this->Ref_model->ref_deposit_create([
							'account' => $to_account['id'],
							'finance' => null,
							'type' => 2,
							'percent' => $ref_percent,
							'turnover_amount' => $winlose_amount,
							'username_from' => $to_account['username'],
							'turn' => $ref_turn,
							'sum_amount' => $sum_amount_new
						]);
					}else{
						$point_for_return_balance_new = $to_account['point_for_return_balance'];
						$log_return_balance_id = $this->Log_return_balance_model->log_return_balance_create([
							'account' => $to_account['id'],
							'username' => $to_account['username'],
							'point_before' => is_numeric($to_account['point_for_return_balance']) ? $to_account['point_for_return_balance'] : 0.00,
							'point_after' => $point_for_return_balance_new,
							'point' => 0.00,
							'description' => "เพิ่มแต้ม (โบนัสคืนยอดเสียให้ตัวเอง) : ".number_format($sum_amount,2)." จากยอดเล่นเสีย -".number_format($winlose_amount,2)." [วันที่ ".$start_date." - ".$end_date.", คิดจากเปอร์เซ็นต์ (".number_format($ref_percent,2).", ".$txt_rank."ไม่เพิ่มเพราะต้องมากกว่าหรือเท่ากับ 1 ขึ้นไปและต้องน้อยกว่าหรือเท่ากับ ".number_format($ref_max,2).")]",
							'type' => '0', //เพิ่ม
							'status' => '1', //สำเร็จ
						]);

						$this->Ref_model->ref_deposit_create([
							'account' => $to_account['id'],
							'finance' => null,
							'type' => 2,
							'percent' => $ref_percent,
							'turnover_amount' => $winlose_amount,
							'username_from' => $to_account['username'],
							'turn' => $ref_turn,
							'sum_amount' => $sum_amount
						]);
					}
				}
			}else{

				$sum_amount = round_up(($winlose_amount*$ref_percent)/100,2);
				//Add to main wallet
				/*$form_data = [];
				$form_data["account_agent_username"] = $to_account['account_agent_username'];
				$form_data["amount"] = $sum_amount;
				$form_data = member_credit_data($form_data);
				$credit_before = $this->remaining_credit($to_account);
				$log_deposit_withdraw_id = $this->Log_deposit_withdraw_model->log_deposit_withdraw_create([
					'account' => $to_account['id'],
					'amount' => $form_data["amount"],
					'amount_before' => $credit_before,
					'type' => '1', //ฝาก
					'description' => 'เพิ่มเครดิต (โบนัสคืนยอดเสียให้ตัวเอง), ไม่เพิ่มเพราะได้ต้องมากกว่าหรือเท่ากับ 1 ขึ้นไป',
					'admin' =>'0',
				]);*/
				$extend_txt_remark = "";
				if($winlose_amount > 99999999.99){
					$extend_txt_remark .= "/ค่ายอดเสียจาก API เกินจริง";
				}
				$point_for_return_balance_new = $to_account['point_for_return_balance'];
				$log_return_balance_id = $this->Log_return_balance_model->log_return_balance_create([
					'account' => $to_account['id'],
					'username' => $to_account['username'],
					'point_before' => is_numeric($to_account['point_for_return_balance']) ? $to_account['point_for_return_balance'] : 0.00,
					'point_after' => $point_for_return_balance_new,
					'point' => 0.00,
					'description' => "เพิ่มแต้ม (โบนัสคืนยอดเสียให้ตัวเอง) : ".number_format($sum_amount,2)." จากยอดเล่นเสีย -".number_format($winlose_amount,2)." [วันที่ ".$start_date." - ".$end_date.", คิดจากเปอร์เซ็นต์ (".number_format($ref_percent,2).", ไม่เพิ่มเพราะได้ต้องมากกว่าหรือเท่ากับ 1 ขึ้นไป".$extend_txt_remark.")]",
					'type' => '0', //เพิ่ม
					'status' => '1', //สำเร็จ
				]);
				$this->Ref_model->ref_deposit_create([
					'account' => $to_account['id'],
					'finance' => null,
					'type' => 2,
					'percent' => $ref_percent,
					'turnover_amount' => $winlose_amount,
					'username_from' => $to_account['username'],
					'turn' => $ref_turn,
					'sum_amount' => $sum_amount
				]);
			}
		}else if(strpos($winlose_amount,"-") === false && (float)$winlose_amount >= 0){
			$point_for_return_balance_new = $to_account['point_for_return_balance'];
			$log_return_balance_id = $this->Log_return_balance_model->log_return_balance_create([
				'account' => $to_account['id'],
				'username' => $to_account['username'],
				'point_before' => is_numeric($to_account['point_for_return_balance']) ? $to_account['point_for_return_balance'] : 0.00,
				'point_after' => $point_for_return_balance_new,
				'point' => 0.00,
				'description' => "เพิ่มแต้ม (โบนัสคืนยอดเสียให้ตัวเอง) : ".number_format(0,2)." จากยอดเล่นเสีย +".number_format($winlose_amount,2)." [วันที่ ".$start_date." - ".$end_date.", คิดจากเปอร์เซ็นต์ (".number_format($ref_percent,2).", ไม่เพิ่มเพราะไม่ได้เล่นเสีย)]",
				'type' => '0', //เพิ่ม
				'status' => '1', //สำเร็จ
			]);

			$this->Ref_model->ref_deposit_create([
				'account' => $to_account['id'],
				'finance' => null,
				'type' => 4,
				'percent' => $ref_percent,
				'turnover_amount' => $winlose_amount,
				'username_from' => $to_account['username'],
				'turn' => $ref_turn,
				'sum_amount' => 0
			]);
		}
		return true;
	}

	private function remaining_credit($user)
	{
		try {
			$balance_credit = $this->game_api_librarie->balanceCredit($user);
			return $balance_credit;
		} catch (\Exception $e) {
			return 0.00;
		}
	}

	private function checkTurnOver($turnover_amount = 0,$from_account,$to_account){
		if((float)$turnover_amount > 0 && number_format((float)$turnover_amount,2) != "99,999,999.99"){
			$turnover_amount = (float)str_replace("-","",$turnover_amount);
			if($turnover_amount > 0 && $turnover_amount < 99999999.99){
				$ref_percent = $this->Setting_model->setting_find([
					'name' => 'ref_percent'
				]);
				$ref_turn = $this->Setting_model->setting_find([
					'name' => 'ref_turn'
				]);
				$ref_percent = $ref_percent!=""?$ref_percent['value']:0;
				$ref_turn = $ref_turn!=""?$ref_turn['value']:0;
				$sum_amount = round_up(($turnover_amount*$ref_percent)/100,2);

				if($sum_amount  > 0 && $sum_amount <= 50000.00){
					$from_account['amount_wallet_ref'] = is_numeric($from_account['amount_wallet_ref']) && (float)$from_account['amount_wallet_ref'] < 0 ? 0.00 : (float)$from_account['amount_wallet_ref'];
					$account_update = [
						'id' => $from_account['id'],
						'amount_wallet_ref' => ($from_account['amount_wallet_ref']+$sum_amount)
					];
					$this->Account_model->account_update($account_update);
					$this->Ref_model->ref_deposit_create([
						'account' => $from_account['id'],
						'finance' => null,
						'percent' => $ref_percent,
						'type' => 0,
						'turnover_amount' => $turnover_amount,
						'username_from' => $to_account['username'],
						'username_to' => $from_account['username'],
						// 'turn' => $ref_turn,
						'sum_amount' => $sum_amount
					]);
				}
			}
		}
		return true;
	}

	private function checkTurnOverStep2($turnover_amount = 0,$from_master,$from_account,$to_account){
		if((float)$turnover_amount > 0 && number_format((float)$turnover_amount,2) != "99,999,999.99"){
			$turnover_amount = (float)str_replace("-","",$turnover_amount);
			if($turnover_amount > 0 && $turnover_amount < 99999999.99){
				$ref_percent = $this->Setting_model->setting_find([
					'name' => 'ref_step2_percent'
				]);
				$ref_turn = $this->Setting_model->setting_find([
					'name' => 'ref_step2_turn'
				]);
				$ref_percent = $ref_percent!=""?$ref_percent['value']:0;
				$ref_turn = $ref_turn!=""?$ref_turn['value']:0;
				$sum_amount = round_up(($turnover_amount*$ref_percent)/100,2);

				if($sum_amount  > 0 && $sum_amount <= 50000.00){
					$from_master['amount_wallet_ref'] = is_numeric($from_master['amount_wallet_ref']) && (float)$from_master['amount_wallet_ref'] < 0 ? 0.00 : (float)$from_master['amount_wallet_ref'];
					$account_update = [
						'id' => $from_master['id'],
						'amount_wallet_ref' => ($from_master['amount_wallet_ref']+$sum_amount)
					];
					$this->Account_model->account_update($account_update);
					$this->Ref_model->ref_deposit_create([
						'account' => $from_master['id'],
						'finance' => null,
						'type' => 0,
						'percent' => $ref_percent,
						'turnover_amount' => $turnover_amount,
						'username_from' => $to_account['username'],
						'username_from_ref' => $from_account['username'],
						'username_to' => $from_account['username'],
						// 'turn' => $ref_turn,
						'sum_amount' => $sum_amount
					]);
				}

			}
		}
		return true;
	}

	private function checkWinLose($winlose_amount = 0,$from_account,$to_account){
		if((strpos($winlose_amount,"-") !== false || (float)$winlose_amount < 0) && number_format((float)$winlose_amount,2) != "-99,999,999.99"){
			$winlose_amount = (float)str_replace("-","",$winlose_amount);
			if($winlose_amount > 0 && $winlose_amount < 99999999.99){
				$ref_percent = $this->Setting_model->setting_find([
					'name' => 'ref_percent'
				]);
				$ref_turn = $this->Setting_model->setting_find([
					'name' => 'ref_turn'
				]);
				$ref_percent = $ref_percent!=""?$ref_percent['value']:0;
				$ref_turn = $ref_turn!=""?$ref_turn['value']:0;
				$sum_amount = round_up(($winlose_amount*$ref_percent)/100,2);
				if($sum_amount  > 0 && $sum_amount <= 50000.00){
					$from_account['amount_wallet_ref'] = is_numeric($from_account['amount_wallet_ref']) && (float)$from_account['amount_wallet_ref'] < 0 ? 0.00 : (float)$from_account['amount_wallet_ref'];
					$account_update = [
						'id' => $from_account['id'],
						'amount_wallet_ref' => ($from_account['amount_wallet_ref']+$sum_amount)
					];
					$this->Account_model->account_update($account_update);
					$this->Ref_model->ref_deposit_create([
						'account' => $from_account['id'],
						'finance' => null,
						'type' => 1,
						'percent' => $ref_percent,
						'turnover_amount' => $winlose_amount,
						'username_from' => $to_account['username'],
						'username_to' => $from_account['username'],
						// 'turn' => $ref_turn,
						'sum_amount' => $sum_amount
					]);
				}

			}
		}
		return true;
	}

	private function checkWinLoseStep2($winlose_amount = 0,$from_master,$from_account,$to_account){
		if((strpos($winlose_amount,"-") !== false || (float)$winlose_amount < 0) && number_format((float)$winlose_amount,2) != "-99,999,999.99"){
			$winlose_amount = (float)str_replace("-","",$winlose_amount);
			if($winlose_amount > 0  && $winlose_amount < 99999999.99){
				$ref_percent = $this->Setting_model->setting_find([
					'name' => 'ref_step2_percent'
				]);
				$ref_turn = $this->Setting_model->setting_find([
					'name' => 'ref_step2_turn'
				]);
				$ref_percent = $ref_percent!=""?$ref_percent['value']:0;
				$ref_turn = $ref_turn!=""?$ref_turn['value']:0;
				$sum_amount = round_up(($winlose_amount*$ref_percent)/100,2);
				if($sum_amount  > 0 && $sum_amount <= 50000.00){
					$from_master['amount_wallet_ref'] = is_numeric($from_master['amount_wallet_ref']) && (float)$from_master['amount_wallet_ref'] < 0 ? 0.00 : (float)$from_master['amount_wallet_ref'];
					$account_update = [
						'id' => $from_master['id'],
						'amount_wallet_ref' => ($from_master['amount_wallet_ref']+$sum_amount)
					];
					$this->Account_model->account_update($account_update);
					$this->Ref_model->ref_deposit_create([
						'account' => $from_master['id'],
						'finance' => null,
						'type' => 1,
						'percent' => $ref_percent,
						'turnover_amount' => $winlose_amount,
						'username_from' => $to_account['username'],
						'username_from_ref' => $from_account['username'],
						'username_to' => $from_account['username'],
						// 'turn' => $ref_turn,
						'sum_amount' => $sum_amount
					]);
				}

			}
		}
		return true;
	}

	public function process_line_notify(){
		if(isset($_GET['api_token']) && trim($_GET['api_token']) == $this->config->item('web_api_token')){

			date_default_timezone_set('Asia/Bangkok');
			$date_before = date('Y-m-d', strtotime('-1 days'));
			$date_current = date('Y-m-d');

			//Check Auto Transfer
			$base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
			$base_url .= "://". @$_SERVER['HTTP_HOST'];
			$base_url .=     str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $base_url.'admin/api/bank_auto_transfer_sfo4rsdf?api_token=rs2nvxdjJLaBr5eXXZddTshDsM4T7Tw34MXLJNWN',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 60,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'GET',

			));
			$response = curl_exec($curl);
			curl_close($curl);

			//Check Finance Auto Withdraw
			$base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
			$base_url .= "://". @$_SERVER['HTTP_HOST'];
			$base_url .=     str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $base_url.'admin/api/finance_auto_withdraw_sfo4rsdf?api_token=rs2nvxdjJLaBr5eXXZddTshDsM4T7Tw34MXLJNWN',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 60,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'GET',

			));
			$response = curl_exec($curl);
			curl_close($curl);

			$start_date_time = new DateTime(date('Y-m-d H:i:s'));
			if($start_date_time->format('H') == "01" && in_array($start_date_time->format('i'),["05","06","07","08","09","10","11","12","13","14","15","16","17","18","19","20"])){
				//รายงานผลทุกเวลา 00:00
				$report_list = $this->Log_line_notify_model->log_line_notify_list([
					'page' => 0,
					'per_page' => 10,
					'status' => 1,
					'type' => 3,
					'created_at' => $date_current,
				]);
				if(count($report_list) == 0 ){

					//ยอดฝากวันนี้
					$deposit_data = $this->Finance_model->finance_report_all_day(['type'=>1,'created_at'=>$date_before]);
					$deposit = $deposit_data[0]['sum_amount'] ;
					//ยอดถอนวันนี้
					$withdraw_data = $this->Finance_model->finance_report_all_day(['type'=>2,'created_at'=>$date_before,'status_list'=>["1","3"]]);
					$withdraw = $withdraw_data[0]['sum_amount'] ;
					//กำไรสุทธิ์วันนี้
					$total = $deposit - $withdraw;
					$message = "สรุปยอดวันที่ : ".$date_before."\n";
					$message .= "ยอดฝากวันนี้ : ".number_format($deposit,2)." บาท\n";
					$message .= "ยอดถอนวันนี้ : ".number_format($withdraw,2)." บาท\n";
					$message .= "กำไรสุทธิ : ".number_format($total,2)." บาท";
					$response_report = line_notify_message(3,$message,null);
				}
			}
			sleep(2);
			$line_notify_list = $this->Log_line_notify_model->log_line_notify_list([
				'page' => 0,
				'per_page' => 10,
				'status' => 0,
				'start_date' => $date_before,
				'end_date' => $date_current,
			]);
			foreach($line_notify_list as $line_notify){
				$cache_data = $this->cache->file->get(base64_encode("Line_notify_".date("Y_m")));
				if($cache_data !== FALSE  && !array_key_exists($line_notify['id'],$cache_data)){
					$cache_data[$line_notify['id']] = $line_notify['id'];
					$this->cache->file->save(base64_encode("Line_notify_".date("Y_m")),$cache_data, 31556926); // 1 year
				}else{
					$cache_data = [
						$line_notify['id'] => $line_notify['id']
					];
					$this->cache->file->save(base64_encode("Line_notify_".date("Y_m")),$cache_data, 31556926); // 1 year
					$response = line_notify_message($line_notify['type'],$line_notify['message'],$line_notify['id']);
				}
			}

			//ลบประวัติ
			date_default_timezone_set('Asia/Bangkok');
			$date_for_clear_log = new DateTime();
			$date_for_clear_log->modify('-2 month');
			$date_for_clear_log->modify('first day of this month');
			$date_for_clear_log = $date_for_clear_log->format('Y-m-d');
			$clear_log_start_date_time = new DateTime(date('Y-m-d H:i:s'));
			$date_for_clear_log_line = new DateTime();
			$date_for_clear_log_line->modify('-2 days');
			$date_for_clear_log_line = $date_for_clear_log_line->format('Y-m-d');
			$date_for_clear_log_report_benefit = new DateTime();
			$date_for_clear_log_report_benefit->modify('-12 month');
			$date_for_clear_log_report_benefit->modify('first day of this month');
			$date_for_clear_log_report_benefit = $date_for_clear_log_report_benefit->format('Y-m-d');
			$date_for_clear_log_1_month = new DateTime();
			$date_for_clear_log_1_month->modify('-1 month');
			$date_for_clear_log_1_month = $date_for_clear_log_1_month->format('Y-m-d');
			$clear_log_date_time_chk_1 = new DateTime(date('Y-m-d')." 03:50:00");
			$clear_log_date_time_chk_2 = new DateTime(date('Y-m-d')." 04:00:00");
			if ($clear_log_start_date_time->getTimestamp() >= $clear_log_date_time_chk_1->getTimestamp() && $clear_log_start_date_time->getTimestamp() < $clear_log_date_time_chk_2->getTimestamp() ) {

				$cache_path =  APPPATH.'cache/';
				$handle = opendir($cache_path);
				while (($file = readdir($handle))!== FALSE)
				{
					//Leave the directory protection alone
					if (
						strpos($file,"process_deposit_cache_".date('Y_m_d', strtotime('-1 days'))) !== FALSE ||
						strpos($file,"process_withdraw_cache_".date('Y_m_d', strtotime('-1 days'))) !== FALSE
					)
					{
						@unlink($cache_path.'/'.$file);
					}
				}
				closedir($handle);


				require_once FCPATH .'/conn_cron.php';

				$chk_scb = rmdir(APPPATH . '../tmp/stmt-scb/'.date('Y-m-d', strtotime('-1 days')));
				$chk_ktb = rmdir(APPPATH . '../tmp/stmt-ktb/'.date('Y-m-d', strtotime('-1 days')));
				$chk_tw = rmdir(APPPATH . '../tmp/stmt-tw/'.date('Y-m-d', strtotime('-1 days')));

				$sql_log_line_notify_log = "DELETE FROM `log_line_notify` WHERE created_at < '".$date_for_clear_log_line." 00:00:00'";
				$obj_con_cron->query($sql_log_line_notify_log);

				$sql_finance_log = "DELETE FROM `finance` WHERE created_at < '".$date_for_clear_log." 00:00:00'";
				$obj_con_cron->query($sql_finance_log);

				$sql_transaction_log = "DELETE FROM `transaction` WHERE created_at < '".$date_for_clear_log." 00:00:00'";
				$obj_con_cron->query($sql_transaction_log);

				$sql_credit_log = "DELETE FROM `credit_history` WHERE created_at < '".$date_for_clear_log." 00:00:00'";
				$obj_con_cron->query($sql_credit_log);

				$sql_use_promotion_log = "DELETE FROM `use_promotion` WHERE created_at < '".$date_for_clear_log." 00:00:00'";
				$obj_con_cron->query($sql_use_promotion_log);

				$sql_report_sms_log = "DELETE FROM `report_smses` WHERE created_at < '".$date_for_clear_log." 00:00:00'";
				$obj_con_cron->query($sql_report_sms_log);

				$sql_report_log = "DELETE FROM `reports` WHERE created_at < '".$date_for_clear_log." 00:00:00'";
				$obj_con_cron->query($sql_report_log);

				$sql_ref_deposit_log = "DELETE FROM `ref_deposit` WHERE created_at < '".$date_for_clear_log." 00:00:00'";
				$obj_con_cron->query($sql_ref_deposit_log);

				$sql_wallet_deposit_log = "DELETE FROM `wallet_deposit` WHERE created_at < '".$date_for_clear_log." 00:00:00'";
				$obj_con_cron->query($sql_wallet_deposit_log);

				$sql_wallet_ref_deposit_log = "DELETE FROM `wallet_ref_deposit` WHERE created_at < '".$date_for_clear_log." 00:00:00'";
				$obj_con_cron->query($sql_wallet_ref_deposit_log);

				$sql_withdraw_log = "DELETE FROM `withdraw` WHERE created_at < '".$date_for_clear_log." 00:00:00'";
				$obj_con_cron->query($sql_withdraw_log);

				$sql_log_deposit_withdraw_log = "DELETE FROM `log_deposit_withdraw` WHERE created_at < '".$date_for_clear_log." 00:00:00'";
				$obj_con_cron->query($sql_log_deposit_withdraw_log);

				$sql_log_page_log = "DELETE FROM `log_page` WHERE created_at < '".$date_for_clear_log_1_month." 00:00:00'";
				$obj_con_cron->query($sql_log_page_log);

				$sql_log_sms_log = "DELETE FROM `log_sms` WHERE created_at < '".$date_for_clear_log_1_month." 00:00:00'";
				$obj_con_cron->query($sql_log_sms_log);

				$sql_log_wheel_log = "DELETE FROM `log_wheel` WHERE created_at < '".$date_for_clear_log_1_month." 00:00:00'";
				$obj_con_cron->query($sql_log_wheel_log);

				$sql_log_return_balance_log = "DELETE FROM `log_return_balance` WHERE created_at < '".$date_for_clear_log_1_month." 00:00:00'";
				$obj_con_cron->query($sql_log_return_balance_log);

				$sql_log_add_credit_log = "DELETE FROM `log_add_credit` WHERE created_at < '".$date_for_clear_log." 00:00:00'";
				$obj_con_cron->query($sql_log_add_credit_log);

				$sql_log_report_benefit = "DELETE FROM `report_business_benefit` WHERE process_date < '".$date_for_clear_log_report_benefit."'";
				$obj_con_cron->query($sql_log_report_benefit);
			}

			if(is_array($line_notify_list) && count($line_notify_list) == 0){
				echo json_encode([
					"success" => true,
					"message" => "Running No record..."
				]);
				exit();
			}
			echo json_encode([
				"success" => true,
				"message" => "Running..."
			]);
			exit();
		}
		echo json_encode([
			"success" => false,
			"message" => "Invalid Params"
		]);
		exit();
	}
}
