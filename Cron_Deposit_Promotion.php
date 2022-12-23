<?php
require('config.php');
if(isset($_GET['api_token']) && trim($_GET['api_token']) == API_TOKEN_KEY){
	//Check
	//ob_start('ob_gzhandler');

}
require('conn_cron.php');

function getBestPromotion($user_id)
{

}

//count Promotion use
function checkUsePromotion($user_id,$promotion_data)
{
	global $obj_con_cron;
	//Check Promotion user use
	$amount_deposit_auto_old = $promotion_data['amount_deposit_auto'];
	//เช็คว่าลูกค้าได้รับโปรโมชั่นนี้ไปหรือยัง
	switch ($promotion_data['type']) {
		case '1':
			$use_promotion = use_promotion_count(['user_id'=>$user_id, "id"=>$promotion_data['id']]);
			if ($use_promotion<$promotion_data['max_use']) {
				$remaining = ($promotion_data['max_use']-$use_promotion);
			}

			break;
		case '2':
			$use_promotion = use_promotion_count([
				'user_id' => $user_id,
				'id' => $promotion_data['id'],
				'date_from' =>  date('Y-m-d'),
				'date_to' =>  date('Y-m-d'),
			]);
			if ($use_promotion<$promotion_data['max_use']) {
				$remaining = ($promotion_data['max_use']-$use_promotion);
			}

			break;
		case '3':
			date_default_timezone_set('Asia/Bangkok');
			$start_date_pro = new DateTime();
			$start_date_pro->modify('Monday this week');
			$end_date_pro = new DateTime();
			$end_date_pro->modify('Sunday this week');
			$use_promotion = use_promotion_count([
				'user_id' => $user_id,
				'id' => $promotion_data['id'],
				'date_from' =>  $start_date_pro->format('Y-m-d'),
				'date_to' =>  $end_date_pro->format('Y-m-d'),
			]);
			if ($use_promotion<$promotion_data['max_use']) {
				$remaining = ($promotion_data['max_use']-$use_promotion);
			}

			break;
		case '4':
			date_default_timezone_set('Asia/Bangkok');
			$start_date_pro = new DateTime();
			$start_date_pro->modify('first day of this month');
			$end_date_pro = new DateTime();
			$end_date_pro->modify('last day of this month');
			$use_promotion = use_promotion_count([
				'user_id' => $user_id,
				'id' => $promotion_data['id'],
				'date_from' =>  $start_date_pro->format('Y-m-d'),
				'date_to' =>  $end_date_pro->format('Y-m-d'),
			]);
			if ($use_promotion<$promotion_data['max_use']) {
				$remaining = ($promotion_data['max_use']-$use_promotion);
			}

			break;
		case '5':
			$current_time = date('Y-m-d H:i:s');
			$start_time = date('Y-m-d H:i:s',strtotime("today {$promotion_data['start_time']}"));
			$end_time = date('Y-m-d H:i:s',strtotime("today {$promotion_data['end_time']}"));
			if ($current_time >= $start_time && $current_time <= $end_time) {
				$use_promotion = use_promotion_count([
					'user_id' => $user_id,
					'id' => $promotion_data['id'],
					'start_time' =>  $start_time,
					'end_time' =>  $end_time,
				]);
				if ($use_promotion<$promotion_data['max_use']) {
					$remaining = ($promotion_data['max_use']-$use_promotion);
				}
			}
			break;
		case '6':
			$days_deposit = $promotion_data['number_of_deposit_days'];
			$dataLastday = getLastNDays($days_deposit);
			$finance = finance_find_created_at([
				'account' => $user_id,
				'limit'=> $days_deposit
			]);
			$result_intersect = array_intersect($dataLastday,$finance);
			$countDay = count($result_intersect);
			if ($countDay >= $days_deposit) {
				$use_promotion = use_promotion_count([
					'user_id' => $user_id,
					'id' => $promotion_data['id'],
				]);
				if ($use_promotion<$promotion_data['max_use']) {
					$remaining = ($promotion_data['max_use']-$use_promotion);
				}
			}
			break;
		default:
			$remaining = 0;
			break;
	}
	return $remaining;
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

function use_promotion_count($search = []){
	global $obj_con_cron;
	$date_from_where ='';
	$start_time_where ='';
	if(isset($search['date_from']) && isset($search['date_to'])){
		$date_from_where  = " and a.created_at BETWEEN '{$search['date_from']} 00:00:00' and '{$search['$date_to']} 23:59:59' ";
	}

	if(isset($search['start_time']) && isset($search['end_time'])){
		$start_time_where  = " and a.created_at BETWEEN '{$search['start_time']}' and '{$search['end_time']}' ";
	}

	$sqlUsePromotion = "Select Count(1) as cnt_row 
						from use_promotion a 
						 inner join finance b on(a.finance = b.id)
						 where  a.promotion = {$search['id']}
						  and b.account = {$search['user_id']}
						  {$date_from_where}
						  {$start_time_where}
						  ";
	$dsUsePromotion = $obj_con_cron->query($sqlUsePromotion);
	$daUsePromotion = $dsUsePromotion->fetch_assoc();
	return isset($daUsePromotion['cnt_row']) && is_numeric($daUsePromotion['cnt_row']) ? (int)$daUsePromotion['cnt_row'] : 0;
}

function getLastNDays($days, $format = 'Y-m-d')
{
	$m = date("m"); $de= date("d",strtotime('-1 days')); $y= date("Y");
	$dateArray = array();
	for($i=0; $i<=$days-1; $i++){
		$dateArray[] = date($format, mktime(0,0,0,$m,($de-$i),$y));
	}
	return $dateArray;
}

function finance_find_created_at($search = [])
{
	global $obj_con_cron;
	$sql  = "select id , account, created_at 
				from finance 
					where account = {$search['account']}
					order by created_at DESC LIMIT {$search['limit']}";

	$ds = $obj_con_cron->query($sql);
	while($da = $ds->fetch_assoc()){
		$data[] = date("Y-m-d", strtotime($da['created_at']));
	}
	return array_unique($data);
}
?>
