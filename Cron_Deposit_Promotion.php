<?php
require('config.php');
if(isset($_GET['api_token']) && trim($_GET['api_token']) == API_TOKEN_KEY){
	//Check
	//ob_start('ob_gzhandler');

}
require('conn_cron.php');

function getBestPromotion($user_id){

}

function checkUsePromotion($user_id,$promotion_data)
{
	global $obj_con_cron;
	//Check Promotion user use
	$sqlUsePromotion = "Select Count(1) as cnt_row 
						from use_promotion a 
						 inner join finance b on(a.finance = b.id)
						 where  a.promotion = {$promotion_data['id']}
						  and b.account = {$user_id}";
	$dsUsePromotion = $obj_con_cron->query($sqlUsePromotion);
	$daUsePromotion = $dsUsePromotion->fetch_assoc();
	$use_promotion = $daUsePromotion['cnt_row'];
	switch ($promotion_data['type']) {
		case '1':
			$use_promotion = $this->Use_promotion_model->use_promotion_count([
				'account' => $user_id,
				'promotion' => $promotion_data['id']
			]);
			if ($use_promotion<$promotion_data['max_use']) {
				$promotion['remaining'] = ($promotion_data['max_use']-$use_promotion);
			}else{
				$this->Account_model->account_update([
					'id' => $_SESSION['user']['id'],
					'amount_deposit_auto' => $amount_deposit_auto_old,
				]);
				echo json_encode([
					'message' => "ท่านได้ใช้โปรโมชั่นนี้ครบจำนวนแล้ว",
					'error' => true
				]);
				exit();
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
				$promotion['remaining'] = ($promotion_data['max_use']-$use_promotion);
			}else{
				$this->Account_model->account_update([
					'id' => $_SESSION['user']['id'],
					'amount_deposit_auto' => $amount_deposit_auto_old,
				]);
				echo json_encode([
					'message' => "ท่านได้ใช้โปรโมชั่นนี้ครบจำนวนแล้ว",
					'error' => true
				]);
				exit();
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
				$promotion['remaining'] = ($promotion_data['max_use']-$use_promotion);
			}else{
				$this->Account_model->account_update([
					'id' => $_SESSION['user']['id'],
					'amount_deposit_auto' => $amount_deposit_auto_old,
				]);
				echo json_encode([
					'message' => "ท่านได้ใช้โปรโมชั่นนี้ครบจำนวนแล้ว",
					'error' => true
				]);
				exit();
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
				$promotion['remaining'] = ($promotion_data['max_use']-$use_promotion);
			}else{
				$this->Account_model->account_update([
					'id' => $_SESSION['user']['id'],
					'amount_deposit_auto' => $amount_deposit_auto_old,
				]);
				echo json_encode([
					'message' => "ท่านได้ใช้โปรโมชั่นนี้ครบจำนวนแล้ว",
					'error' => true
				]);
				exit();
			}
			break;
		case '5':
			$current_time = date('Y-m-d H:i:s');
			$start_time = date('Y-m-d H:i:s',strtotime("today {$promotion_data['start_time']}"));
			$end_time = date('Y-m-d H:i:s',strtotime("today {$promotion_data['end_time']}"));
			if ($current_time >= $start_time && $current_time <= $end_time) {
				$use_promotion = $this->Use_promotion_model->use_promotion_count([
					'account' => $_SESSION['user']['id'],
					'promotion' => $promotion_data['id'],
					'start_time' =>  $start_time,
					'end_time' =>  $end_time,
				]);
				if ($use_promotion<$promotion_data['max_use']) {
					$promotion['remaining'] = ($promotion_data['max_use']-$use_promotion);
				} else {
					$this->Account_model->account_update([
						'id' => $_SESSION['user']['id'],
						'amount_deposit_auto' => $amount_deposit_auto_old,
					]);
					echo json_encode([
						'message' => "ท่านได้ใช้โปรโมชั่นนี้ครบจำนวนแล้ว",
						'error' => true
					]);
					exit();
				}
			} else {
				echo json_encode([
					'message' => "หมดเวลารับโปรโมชั่นนี้",
					'error' => true
				]);
				exit();
			}
			break;
		case '6':
			$days_deposit = $promotion_data['number_of_deposit_days'];
			$dataLastday = $this->getLastNDays($days_deposit);
			$finance = $this->Finance_model->finance_find_created_at([
				'account' => $_SESSION['user']['id'],
				'limit'=> $days_deposit
			]);
			$result_intersect = array_intersect($dataLastday,$finance);
			$countDay = count($result_intersect);
			if ($countDay >= $days_deposit) {
				$use_promotion = $this->Use_promotion_model->use_promotion_count([
					'account' => $_SESSION['user']['id'],
					'promotion' => $promotion_data['id']
				]);
				if ($use_promotion<$promotion_data['max_use']) {
					$promotion['remaining'] = ($promotion_data['max_use']-$use_promotion);
				} else {
					$this->Account_model->account_update([
						'id' => $_SESSION['user']['id'],
						'amount_deposit_auto' => $amount_deposit_auto_old,
					]);
					echo json_encode([
						'message' => "ท่านได้ใช้โปรโมชั่นนี้ครบจำนวนแล้ว",
						'error' => true
					]);
					exit();
				}
			} else {
				echo json_encode([
					'message' => "ท่านไม่ได้ฝากต่อเนื่องตามที่กำหนด",
					'error' => true
				]);
				exit();
			}
			break;
		default:
			$promotion['remaining'] = 0;
			break;
	}
	break;

}
function getPromotion($promotion_id){
	global $obj_con_cron;
	$sqlPromotion = "Select name,
        percent,
        turn,
        turn_football,
        turn_step,
        turn_parlay,
        turn_game,
        turn_casino,
        turn_lotto,
        turn_m2,
        turn_multi_player,
        turn_trading,
        turn_keno,
        max_value,
        category,
        fix_amount_deposit_bonus,
        fix_amount_deposit,
        status,
        id,
        max_use,
        type,
		start_time,
        end_time,
        number_of_deposit_days,
         from Promotion where id ={$promotion_id}";

	$dsPromotion = $obj_con_cron->query($sqlPromotion);
	$promotion_data = $dsPromotion->fetch_assoc();
	print_r($promotion_data);
	return $promotion_data;

}
 getPromotion(14);
?>
