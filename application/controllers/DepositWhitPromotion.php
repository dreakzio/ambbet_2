<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'../vendor/autoload.php';
use \Curl\Curl;
use Rct567\DomQuery\DomQuery;

class DepositWhitPromotion extends CI_Controller
{
	public function __construct()
	{
		date_default_timezone_set('Asia/Bangkok');
		parent::__construct();
	}
	public function index(){
		$auto_deposit = $this->Setting_model->setting_find([
			'name' => 'deposit_with_bonus_auto'
		]);
		if($auto_deposit['value']==0){
			echo json_encode(['status'=>false,"message" => "Deposit with Bonus is Disabled"]);
			die();
		}
		$users = $this->Account_model->account_list_for_deposit_promotion([
			'auto_accept_bonus' => 1,
			'amount_deposit_auto' => 0
		]);
		/*print_r($users);
		die();*/
		$totalAcc =  count($users);
		for($i=0; $i <= $totalAcc; $i++){

				$amount_deposit_auto_old = $users[$i]["amount_deposit_auto"];
				$this->Account_model->account_update([
					'id' => $users[$i]['id'],
					'amount_deposit_auto' => 0,
				]);

				$remaining_credit = $this->remaining_credit($users[$i]);

				$clear_turn = $this->Setting_model->setting_find([
					'name' => 'clear_turn'
				]);
				$clear_turn = $clear_turn==''?10:$clear_turn['value'];
				$turn_date = $users[$i]['turn_date'];
				$turn_over = $users[$i]['turn_over'];
				$turn_over_football = $users[$i]['turn_over_football'];
				$turn_over_step = $users[$i]['turn_over_step'];
				$turn_over_parlay = $users[$i]['turn_over_parlay'];
				$turn_over_game = $users[$i]['turn_over_game'];
				$turn_over_casino = $users[$i]['turn_over_casino'];
				$turn_over_lotto = $users[$i]['turn_over_lotto'];
				$turn_over_m2 = $users[$i]['turn_over_m2'];
				$turn_over_multi_player = $users[$i]['turn_over_multi_player'];
				$turn_over_trading = $users[$i]['turn_over_trading'];
				$turn_over_keno = $users[$i]['turn_over_keno'];
				if ($turn_date=='' || is_null($turn_date)) {
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
					$turn_date = $users[$i]['turn_date'];
				}
				try{
					$turn_date = new DateTime($turn_date);
					$turn_date = $turn_date->format('Y-m-d');
				}catch (Exception $ex){
					if (
						strtotime(date('Y-m-d H:i'))>=strtotime(date('Y-m-d')." 11:00")
						&&
						strtotime(date('Y-m-d H:i')) <=strtotime(date('Y-m-d')." 23:59")
					) {
						$turn_date = date('Y-m-d');
					} else {
						$turn_date = date('Y-m-d', strtotime('-1 days'));
					}
				}
				//=====
				if (floor($remaining_credit)<=$clear_turn) {
					if (
						strtotime(date('Y-m-d H:i'))>=strtotime(date('Y-m-d')." 11:00")
						&&
						strtotime(date('Y-m-d H:i')) <=strtotime(date('Y-m-d')." 23:59")
					) {
						$turn_date_now = date('Y-m-d');
					} else {
						$turn_date_now = date('Y-m-d', strtotime('-1 days'));
					}
					$turn_before = null;
					$turn_before_football = null;
					$turn_before_step = null;
					$turn_before_parlay = null;
					$turn_before_game = null;
					$turn_before_casino = null;
					$turn_before_lotto = null;
					$turn_before_m2 = null;
					$turn_before_multi_player = null;
					$turn_before_trading = null;
					$turn_before_keno = null;
					//ตรวจสอบวัน turn ล่าสุดจาก ref transaction
					$finance_chk_turn = $this->Finance_model->finance_for_check_turn_find([
						'account' => $users[$i]['id']
					]);
					if($turn_date_now == date('Y-m-d') && $finance_chk_turn != "" &&
						strtotime($finance_chk_turn['created_at'])>=strtotime(date('Y-m-d')." 11:00")
						&&
						strtotime($finance_chk_turn['created_at']) <=strtotime(date('Y-m-d')." 23:59")
					){
						$users[$i]['turn_date'] = $turn_date_now;
						$users[$i]['date_end'] = $turn_date_now;
						$turn_before = 0;
						$turn_before_data = $this->check_turn_before($users[$i]);
						foreach (game_code_list() as $game_code){
							if(array_key_exists($game_code,$turn_before_data)){
								$turn_before += (float)$turn_before_data[$game_code]['amount'];
								//${'turn_before_'.strtolower($game_code)} = (float)$turn_before_data[$game_code]['amount'];
								${'turn_before_'.strtolower($game_code)} = 0;
							}
						}

					}
					$this->Account_model->account_update([
						'id' => $users[$i]['id'],
						'turn_date' => $turn_date_now,
						'turn_before' => $turn_before,
						'turn_before_football' => $turn_before_football,
						'turn_before_step' => $turn_before_step,
						'turn_before_parlay' => $turn_before_parlay,
						'turn_before_game' => $turn_before_game,
						'turn_before_casino' => $turn_before_casino,
						'turn_before_lotto' => $turn_before_lotto,
						'turn_before_m2' => $turn_before_m2,
						'turn_before_multi_player' => $turn_before_multi_player,
						'turn_before_trading' => $turn_before_trading,
						'turn_before_keno' => $turn_before_keno,
						'turn_over' => 0,
						'turn_over_football' => 0,
						'turn_over_step' => 0,
						'turn_over_parlay' => 0,
						'turn_over_game' => 0,
						'turn_over_casino' => 0,
						'turn_over_lotto' => 0,
						'turn_over_m2' => 0,
						'turn_over_multi_player' => 0,
						'turn_over_trading' => 0,
						'turn_over_keno' => 0,
						'sha1_acount' => '',
						'ref_transaction_id' => '',
					]);
					$turn_over = 0;
					$turn_over_football = 0;
					$turn_over_step = 0;
					$turn_over_parlay = 0;
					$turn_over_game = 0;
					$turn_over_casino = 0;
					$turn_over_lotto = 0;
					$turn_over_m2 = 0;
					$turn_over_multi_player = 0;
					$turn_date = $turn_date_now;
				} else {
					$users[$i]['turn_date'] = $turn_date;
					$users[$i]['date_end'] = $turn_date;
					if(is_null($users[$i]['turn_before'])){
						$turn_before_football = null;
						$turn_before_step = null;
						$turn_before_parlay = null;
						$turn_before_game = null;
						$turn_before_casino = null;
						$turn_before_lotto = null;
						$turn_before_m2 = null;
						$turn_before_multi_player = null;
						$turn_before = 0;
						$turn_before_data = $this->check_turn_before($users[$i]);
						foreach (game_code_list() as $game_code){
							if(array_key_exists($game_code,$turn_before_data)){
								$turn_before += (float)$turn_before_data[$game_code]['amount'];
								${'turn_before_'.strtolower($game_code)} = (float)$turn_before_data[$game_code]['amount'];
							}
						}
					}else{
						$turn_before = $users[$i]['turn_before'];
						$turn_before_football = $users[$i]['turn_before_football'];
						$turn_before_step = $users[$i]['turn_before_step'];
						$turn_before_parlay = $users[$i]['turn_before_parlay'];
						$turn_before_game = $users[$i]['turn_before_game'];
						$turn_before_casino = $users[$i]['turn_before_casino'];
						$turn_before_lotto = $users[$i]['turn_before_lotto'];
						$turn_before_m2 = $users[$i]['turn_before_m2'];
						$turn_before_multi_player = $users[$i]['turn_before_multi_player'];
						$turn_before_trading = $users[$i]['turn_before_trading'];
						$turn_before_keno = $users[$i]['turn_before_keno'];
					}
				}

				$best_promotions = $this->Promotion_model->promotion_find_best(['amount_deposit_auto' => $users[$i]['amount_deposit_auto']]);
				foreach ($best_promotions as $best_promotion){
					$promotion = $this->checkUsePromotion($users[$i]['id'],$best_promotion);
					if($promotion['canUsePro']==1){
						$best_promotion['remaining']=$promotion['remaining'];
						$best_promotion['canUsePro']=$promotion['canUsePro'];
						//return $promotion;
						break;
					}
				}

				if($best_promotion['category'] == "2"){
					if(
						is_null($best_promotion['fix_amount_deposit']) ||
						(
							!is_null($best_promotion['fix_amount_deposit']) &&
							(float)$users[$i]['amount_deposit_auto'] != (float)$best_promotion['fix_amount_deposit']
						)
					){
						$this->Account_model->account_update([
							'id' => $users[$i]['id'],
							'amount_deposit_auto' => $amount_deposit_auto_old,
						]);
						echo json_encode([
							'message' => "ยอดเงินฝากไม่เข้าเงื่อนไขในการรับโปรโมชั่นนี้",
							'error' => true
						]);
						exit();
					}else{
						$percent_calculate = is_null($best_promotion['fix_amount_deposit_bonus']) ? 0 : (float)$best_promotion['fix_amount_deposit_bonus'];
					}
				}else{
					$percent_calculate = round_up(($best_promotion['percent']*$users[$i]['amount_deposit_auto'])/100,2);
					if ($percent_calculate>$best_promotion['max_value']) {
						$percent_calculate = $best_promotion['max_value'];
					}
				}
				$amount_deposit = ($users[$i]['amount_deposit_auto']+$percent_calculate);
				$amount_deposit_auto_remain = 0;
				if ($turn_over>0) {
					$turn_over = $turn_over+($amount_deposit*$best_promotion['turn']);
				} else {
					$turn_over = ($amount_deposit*$best_promotion['turn']);
				}

				foreach (game_code_list() as $game_code){
					if (${'turn_over_'.strtolower($game_code)}>0) {
						${'turn_over_'.strtolower($game_code)} = ${'turn_over_'.strtolower($game_code)}+($amount_deposit*$best_promotion['turn_'.strtolower($game_code)]);
					} else {
						${'turn_over_'.strtolower($game_code)} = ($amount_deposit*$best_promotion['turn_'.strtolower($game_code)]);
					}
				}

				$form_data = [];
				$users[$i]['account_agent_username'];
				$form_data["account_agent_username"] = $users[$i]['account_agent_username'];
				$form_data["amount"] = $amount_deposit;
				$form_data = member_credit_data($form_data);
				//print_r($form_data);
				//die();
				if($best_promotion!=""){
					$promotion = $best_promotion;
					$promotion_name = "".$promotion['name'];
					if($promotion['max_value']>0 && $promotion['category'] == "1"){
						$text_append_promotion_name = "";
						foreach (game_code_list() as $index => $game_code){
							if($index == 0){
								$text_append_promotion_name .= "".game_code_text_list()[$game_code]." ".$promotion['turn_'.strtolower($game_code)]." เท่า";
							}else{
								$text_append_promotion_name .= ", ".game_code_text_list()[$game_code]." ".$promotion['turn_'.strtolower($game_code)]." เท่า";
							}
						}
						$promotion_name .= " สูงสุด ".number_format($promotion['max_value'])." บาท ( ทำเทิร์น : ".$text_append_promotion_name." )";
					}else if($promotion['category'] == "2"){
						$text_append_promotion_name = "";
						foreach (game_code_list() as $index => $game_code){
							if($index == 0){
								$text_append_promotion_name .= "".game_code_text_list()[$game_code]." ".$promotion['turn_'.strtolower($game_code)]." เท่า";
							}else{
								$text_append_promotion_name .= ", ".game_code_text_list()[$game_code]." ".$promotion['turn_'.strtolower($game_code)]." เท่า";
							}
						}
						$promotion_name .= " ( ทำเทิร์น : ".$text_append_promotion_name." )";
					}
					if($promotion['type']>1){
						$promotion_name .= " ใช้ไปแล้ว (".((float)$promotion['max_use']-(float)$promotion['remaining'] )."/".$promotion['max_use'].")";
					}
				}
			$log_deposit_withdraw_id = $this->Log_deposit_withdraw_model->log_deposit_withdraw_create([
				'account' => $users[$i]['id'],
				'username' => $users[$i]['username'],
				'amount' => $form_data["amount"],
				'amount_before' => $remaining_credit,
				'type' => '1', //ฝาก
				'description' => 'ฝากเงิน',
				'admin' =>$users[$i]['id'],
				'promotion_name' =>$promotion_name,
			]);

			//print_r($form_data);
			$response = $this->game_api_librarie->deposit($form_data);
			//print_r($response);
			if (isset($response['ref'])) {

				if((float)$form_data["amount"] > (float)$users[$i]['amount_deposit_auto']){
					$log_add_credit_id = $this->Log_add_credit_model->log_add_credit_create([
						'account' => $users[$i]['id'],
						'username' => $users[$i]['username'],
						'full_name' => $users[$i]['full_name'],
						'from_amount' => $users[$i]['amount_deposit_auto'],
						'amount' => (float)$form_data["amount"] - (float)$users[$i]['amount_deposit_auto'],
						'type' => 'bonus_promotion',
						'description' => "ฝากเงิน ".$promotion_name,
						'manage_by' =>$users[$i]['id'],
						'manage_by_username' =>$users[$i]['username'],
						'manage_by_full_name' =>$users[$i]['full_name'],
					]);
				}else{
					$log_add_credit_id = $this->Log_add_credit_model->log_add_credit_create([
						'account' => $users[$i]['id'],
						'username' => $users[$i]['username'],
						'full_name' => $users[$i]['full_name'],
						'from_amount' => $users[$i]['amount_deposit_auto'],
						'amount' => 0,
						'type' => 'bonus_not_use_promotion',
						'description' => "ฝากเงิน ".$promotion_name,
						'manage_by' =>$users[$i]['id'],
						'manage_by_username' =>$users[$i]['username'],
						'manage_by_full_name' =>$users[$i]['full_name'],
					]);
				}

				$user_new = $this->Account_model->account_find([
					'id' => $users[$i]['id']
				]);

				$finance_id = $this->Finance_model->finance_create([
					'account' => $users[$i]['id'],
					'amount' => $users[$i]['amount_deposit_auto'],
					'from_amount' => (float)$form_data["amount"] - (float)$users[$i]['amount_deposit_auto'],
					'bank' => $users[$i]['bank'],
					'bank_number' => $users[$i]['bank_number'],
					'bank_name' => $users[$i]['bank_name'],
					'ref_transaction_id' => $response['ref'],
					'username' => $users[$i]['username'],
					'type' => 1,
					'status' => 1
				]);
				$this->Use_promotion_model->use_promotion_create([
					'finance' => $finance_id,
					'promotion' => $promotion['id'],
					'promotion_name' => $promotion['name'],
					'percent' => $promotion['percent'],
					'turn' => $promotion['turn'],
					'turn_football' => $promotion['turn_football'],
					'turn_step' => $promotion['turn_step'],
					'turn_parlay' => $promotion['turn_parlay'],
					'turn_game' => $promotion['turn_game'],
					'turn_casino' => $promotion['turn_casino'],
					'turn_lotto' => $promotion['turn_lotto'],
					'turn_m2' => $promotion['turn_m2'],
					'turn_multi_player' => $promotion['turn_multi_player'],
					'turn_trading' => $promotion['turn_trading'],
					'turn_keno' => $promotion['turn_keno'],
					'max_value' => $promotion['max_value'],
					'sum_amount' => $amount_deposit,
					'amount' => $amount_deposit_auto_old,
					'max_use' => $promotion['max_use'],
					'type' => $promotion['type']
				]);

				$sum_amount_list = $this->Finance_model->sum_amount_deposit_and_withdraw(['account_list' => [$users[$i]['id']]]);
				$sum_amount = 0.00;
				if(array_key_exists($users[$i]['id'],$sum_amount_list)){
					$sum_amount = $sum_amount_list[$users[$i]['id']]['sum_amount'];
				}

				if($user_new!="" && empty($user_new['ref_transaction_id'])){
					$this->Account_model->account_update([
						'id' => $users[$i]['id'],
						'amount_deposit_auto' => $amount_deposit_auto_remain,
						'turn_before' => $turn_before,
						'turn_before_football' => $turn_before_football,
						'turn_before_step' => $turn_before_step,
						'turn_before_parlay' => $turn_before_parlay,
						'turn_before_game' => $turn_before_game,
						'turn_before_casino' => $turn_before_casino,
						'turn_before_lotto' => $turn_before_lotto,
						'turn_before_m2' => $turn_before_m2,
						'turn_before_multi_player' => $turn_before_multi_player,
						'turn_before_trading' => $turn_before_trading,
						'turn_before_keno' => $turn_before_keno,
						'ref_transaction_id' => $response['ref'],
						'turn_over' => $turn_over,
						'turn_over_football' => $turn_over_football,
						'turn_over_step' => $turn_over_step,
						'turn_over_parlay' => $turn_over_parlay,
						'turn_over_game' => $turn_over_game,
						'turn_over_casino' => $turn_over_casino,
						'turn_over_lotto' => $turn_over_lotto,
						'turn_over_m2' => $turn_over_m2,
						'turn_over_multi_player' => $turn_over_multi_player,
						'turn_over_trading' => $turn_over_trading,
						'turn_over_keno' => $turn_over_keno,
						'turn_date' => $turn_date,
						'sum_amount' => $sum_amount,
					]);
				}else{
					$this->Account_model->account_update([
						'id' => $users[$i]['id'],
						'amount_deposit_auto' => $amount_deposit_auto_remain,
						'turn_before' => $turn_before,
						'turn_before_football' => $turn_before_football,
						'turn_before_step' => $turn_before_step,
						'turn_before_parlay' => $turn_before_parlay,
						'turn_before_game' => $turn_before_game,
						'turn_before_casino' => $turn_before_casino,
						'turn_before_lotto' => $turn_before_lotto,
						'turn_before_m2' => $turn_before_m2,
						'turn_over' => $turn_over,
						'turn_over_football' => $turn_over_football,
						'turn_over_step' => $turn_over_step,
						'turn_over_parlay' => $turn_over_parlay,
						'turn_over_game' => $turn_over_game,
						'turn_over_casino' => $turn_over_casino,
						'turn_over_lotto' => $turn_over_lotto,
						'turn_over_m2' => $turn_over_m2,
						'turn_over_multi_player' => $turn_over_multi_player,
						'turn_over_trading' => $turn_over_trading,
						'turn_over_keno' => $turn_over_keno,
						'turn_date' => $turn_date,
						'sum_amount' => $sum_amount,
					]);
				}

				$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
					'id' => $log_deposit_withdraw_id
				]);
				if($log_deposit_withdraw!=""){
					$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
						'id' => $log_deposit_withdraw_id,
						'description' => $log_deposit_withdraw['description']." (Add Credit Auto) | ทำรายการสำเร็จ",
					]);
				}

				$wheel_amount_per_point = $this->Setting_model->setting_find([
					'name' => 'wheel_amount_per_point'
				]);
				$feature_wheel = $this->Feature_status_model->setting_find([
					'name' => 'wheel'
				]);
				if($feature_wheel != "" && $feature_wheel['value'] == "1"){
					if($wheel_amount_per_point != "" && is_numeric($wheel_amount_per_point['value']) && (float)$wheel_amount_per_point['value'] > 0 && (float)$users[$i]['amount_deposit_auto'] >= (float)$wheel_amount_per_point['value']){
						$point = round((float)$user['amount_deposit_auto']/(float)$wheel_amount_per_point['value'], 0, PHP_ROUND_HALF_UP);
						if($point > 0){
							$this->Account_model->account_update([
								'id' => $users[$i]['id'],
								'point_for_wheel' =>  (float)$users[$i]['point_for_wheel'] + $point,
							]);
							$log_wheel_id = $this->Log_wheel_model->log_wheel_create([
								'account' => $users[$i]['id'],
								'username' => $users[$i]['username'],
								'point_before' => $users[$i]['point_for_wheel'],
								'point_after' => (float)$users[$i]['point_for_wheel'] + $point,
								'point' => $point,
								'amount' => $users[$i]['amount_deposit_auto'],
								'description' => "เงินฝาก ".number_format( $users[$i]['amount_deposit_auto'],2)." บาท (คำนวณจาก ".$wheel_amount_per_point['value']." บาท/ 1 เหรียญ)",
								'type' => '0', //เติม
								'status' => '1', //สำเร็จ
							]);
						}
					}

				}

				//$this->ref_bonus($user, $finance_id);
				echo json_encode([
					'message' => 'success',
					'result' => true
				]);
			} else {
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
				$this->Account_model->account_update([
					'id' => $users[$i]['id'],
					'amount_deposit_auto' => $amount_deposit_auto_old,
				]);
				echo json_encode([
					'message' => $error_message,
					'error' => true,
				]);
			}

		}
	}
	public function checkUsePromotion($user_id,$promotion_data)
	{
		global $obj_con_cron;
		//Check Promotion user use
		$amount_deposit_auto_old = $promotion_data['amount_deposit_auto'];
		//เช็คว่าลูกค้าได้รับโปรโมชั่นนี้ไปหรือยัง
		switch ($promotion_data['type']) {
			case '1':
				$use_promotion = $this->Use_promotion_model->use_promotion_count(['account'=>$user_id, "promotion"=>$promotion_data['id']]);
				if ($use_promotion<$promotion_data['max_use']) {
					$remaining = ($promotion_data['max_use']-$use_promotion);
					$canUsePro = 1;
				}else{
					$canUsePro = 0;
					$remaining = $use_promotion;
				}
				break;
			case '2':
				$use_promotion = $this->Use_promotion_model->use_promotion_count([
					'account' => $user_id,
					'promotion' => $promotion_data['id'],
					'date_from' =>  date('Y-m-d'),
					'date_to' =>  date('Y-m-d'),
				]);
				if ($use_promotion<$promotion_data['max_use']) {
					$remaining = ($promotion_data['max_use']-$use_promotion);
					$canUsePro = 1;
				}else{
					$canUsePro = 0;
					$remaining = $use_promotion;
				}
				break;

			case '3':
				date_default_timezone_set('Asia/Bangkok');
				$start_date_pro = new DateTime();
				$start_date_pro->modify('Monday this week');
				$end_date_pro = new DateTime();
				$end_date_pro->modify('Sunday this week');
				$use_promotion = $this->Use_promotion_model->use_promotion_count([
					'account' => $user_id,
					'promotion' => $promotion_data['id'],
					'date_from' =>  $start_date_pro->format('Y-m-d'),
					'date_to' =>  $end_date_pro->format('Y-m-d'),
				]);
				if ($use_promotion<$promotion_data['max_use']) {
					$remaining = ($promotion_data['max_use']-$use_promotion);
					$canUsePro = 1;
				}else{
					$canUsePro = 0;
					$remaining = $use_promotion;
				}
				break;

			case '4':
				date_default_timezone_set('Asia/Bangkok');
				$start_date_pro = new DateTime();
				$start_date_pro->modify('first day of this month');
				$end_date_pro = new DateTime();
				$end_date_pro->modify('last day of this month');
				$use_promotion = $this->Use_promotion_model->use_promotion_count([
					'account' => $user_id,
					'promotion' => $promotion_data['id'],
					'date_from' =>  $start_date_pro->format('Y-m-d'),
					'date_to' =>  $end_date_pro->format('Y-m-d'),
				]);
				if ($use_promotion<$promotion_data['max_use']) {
					$remaining = ($promotion_data['max_use']-$use_promotion);
					$canUsePro = 1;

				}else{
					$canUsePro = 0;
					$remaining = $use_promotion;
				}
				break;

			case '5':
				$current_time = date('Y-m-d H:i:s');
				$start_time = date('Y-m-d H:i:s',strtotime("today {$promotion_data['start_time']}"));
				$end_time = date('Y-m-d H:i:s',strtotime("today {$promotion_data['end_time']}"));
				if ($current_time >= $start_time && $current_time <= $end_time) {
					$use_promotion = $this->Use_promotion_model->use_promotion_count([
						'account' => $user_id,
						'promotion' => $promotion_data['id'],
						'start_time' =>  $start_time,
						'end_time' =>  $end_time,
					]);
					if ($use_promotion<$promotion_data['max_use']) {
						$remaining = ($promotion_data['max_use']-$use_promotion);
						$canUsePro = 1;

					}else{
						$canUsePro = 0;
						$remaining = $use_promotion;
					}
				}else{
					$canUsePro = 0;
					$remaining = 0;
				}
				break;

			case '6':
				$days_deposit = $promotion_data['number_of_deposit_days'];
				$dataLastday = $this->getLastNDays($days_deposit);
				$finance = $this->Finance_model->finance_find_created_at([
					'account' => $user_id,
					'limit'=> $days_deposit
				]);
				$result_intersect = array_intersect($dataLastday,$finance);
				$countDay = count($result_intersect);
				if ($countDay >= $days_deposit) {
					$use_promotion = $this->Use_promotion_model->use_promotion_count([
						'account' => $user_id,
						'promotion' => $promotion_data['id'],
					]);
					if ($use_promotion<$promotion_data['max_use']) {
						$remaining = ($promotion_data['max_use']-$use_promotion);
						$canUsePro = 1;

					}else{
						$canUsePro = 0;
						$remaining = $use_promotion;
					}
				}else{
					$canUsePro = 0;
					$remaining = 0;
				}
				break;

			default:
				$canUsePro = 0;
				$remaining = 0;
				break;
		}
		$return = array('remaining'=>$remaining,'canUsePro'=>$canUsePro);
		//print_r($return);
		return $return;
	}

	public function getLastNDays($days, $format = 'Y-m-d')
	{
		$m = date("m"); $de= date("d",strtotime('-1 days')); $y= date("Y");
		$dateArray = array();
		for($i=0; $i<=$days-1; $i++){
			$dateArray[] = date($format, mktime(0,0,0,$m,($de-$i),$y));
		}
		return $dateArray;
	}
	private function remaining_credit($user)
	{
		header('Content-Type: application/json');
		try {
			$balance_credit = $this->game_api_librarie->balanceCredit($user);
			//print_r($balance_credit);
			return $balance_credit;
		} catch (\Exception $e) {
			echo json_encode([
				'message' => 'ทำรายการไม่สำเร็จ',
				'error' => true
			]);
			exit();
		}
	}
	public function check_turn_before($user)
	{
		$form_data = [];
		$form_data['username'] = $user['account_agent_username'];
		if ($user['turn_date']!="") {
			$date = new DateTime($user['turn_date']);
			$form_data['date_begin'] = $date->format('Y-m-d');
		}
		if(isset($user['date_end'])){
			$date = new DateTime($user['date_end']);
			$form_data['date_end'] = $date->format('Y-m-d');
		}
		$form_data = member_turn_data($form_data);
		$turnover_amount = $this->game_api_librarie->getTurn($form_data);
		return $turnover_amount;
	}
}
