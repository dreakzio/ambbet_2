<?php
require('config.php');
require('conn_cron.php');
require ('application/libraries/Game_api_librarie.php');
ini_set('display_errors',1);
error_reporting(E_ALL);

if(isset($_GET['api_token']) && trim($_GET['api_token']) == API_TOKEN_KEY){
	//Check
	//ob_start('ob_gzhandler');
	$sec_rand = rand(3,8);
	sleep($sec_rand);
	error_reporting(0);
	date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
	$current_date = date("d/m/Y");
	$current_date_chk = date("Y-m-d");
	$before_date = date('d/m/Y',(strtotime ( '-1 day' , strtotime ( date("Y-m-d")) ) ));
	$before_date_chk = date('Y-m-d',(strtotime ( '-1 day' , strtotime ( date("Y-m-d")) ) ));
	ob_start('ob_gzhandler');

	deposit_whit_promotion();
}
function getSetting($setting_name){
	global $obj_con_cron;

	$sql = "select name,value from web_setting where  name = '{$setting_name}'";
	$ds  = $obj_con_cron->query($sql);
	$da = $ds->fetch_assoc();
	//print_r($da);
	return $da['value'];
}
function deposit_whit_promotion(){
	global $obj_con_cron;
	// ตรวจสอบว่าระบบให้่อนุมัติเตะเครดิตหรือไม่
	if(getSetting('deposit_with_bonus_auto')==1){
		$sqlGetUserAmountDepositAuto = "Select a.*,b.accid, b.username  as account_agent_username,b.password as account_agent_password from account a 
           								inner join  account_agent b on(a.id = b.account_id)
         							where a.amount_deposit_auto >0 and a.deleted=0 and auto_accept_bonus=1";

		$ds_Acc_Amd = $obj_con_cron->query($sqlGetUserAmountDepositAuto);
		// check clear turn
		$clear_turn = getSetting('clear_turn');

		while ($da_Acc_Amd = $ds_Acc_Amd->fetch_assoc()){
			$promotion = getBestPromotion($da_Acc_Amd);
			$turn_date = $da_Acc_Amd['turn_date'];
			$turn_over = $da_Acc_Amd['turn_over'];
			$turn_over_football = $da_Acc_Amd['turn_over_football'];
			$turn_over_step = $da_Acc_Amd['turn_over_step'];
			$turn_over_parlay = $da_Acc_Amd['turn_over_parlay'];
			$turn_over_game = $da_Acc_Amd['turn_over_game'];
			$turn_over_casino = $da_Acc_Amd['turn_over_casino'];
			$turn_over_lotto = $da_Acc_Amd['turn_over_lotto'];
			$turn_over_m2 = $da_Acc_Amd['turn_over_m2'];
			$turn_over_multi_player = $da_Acc_Amd['turn_over_multi_player'];
			$turn_over_trading = $da_Acc_Amd['turn_over_trading'];
			$turn_over_keno = $da_Acc_Amd['turn_over_keno'];
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
				$turn_date = $da_Acc_Amd['turn_date'];
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


			if($promotion['canUsePro']==1){
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

			//save log_deposit_withdraw_create

		}

	}else{
		echo json_encode(['status'=>false,"message" => "Deposit with Bonus is Disabled"]);
	}
}


function getBestPromotion($user_data)
{
	global $obj_con_cron;
	$sqlGetBestPro = "SELECT *,IF(category=1,(percent*{$user_data['amount_deposit_auto']})/100,if(fix_amount_deposit={$user_data['amount_deposit_auto']},fix_amount_deposit_bonus,0)) as pro_cal  
						FROM `promotion`  
					  WHERE status =1 and IF(category=1,(percent*{$user_data['amount_deposit_auto']})/100,if(fix_amount_deposit={$user_data['amount_deposit_auto']},fix_amount_deposit_bonus,0)) > 0
						ORDER BY `pro_cal`  DESC;";
	$ds_bestPromotion = $obj_con_cron->query($sqlGetBestPro);
	while ($da_bestPromotion = $ds_bestPromotion->fetch_assoc()){

		$dataAccUsePro = checkUsePromotion($user_data['id'],$da_bestPromotion);
		if($dataAccUsePro['canUsePro']){
			$da_bestPromotion['remaining']=$dataAccUsePro['remaining'];
			return $da_bestPromotion;
			break;
		}
	}
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
				$canUsePro = 1;
			}else{
				$canUsePro = 0;
				$remaining = $use_promotion;
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
			$use_promotion = use_promotion_count([
				'user_id' => $user_id,
				'id' => $promotion_data['id'],
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
			$use_promotion = use_promotion_count([
				'user_id' => $user_id,
				'id' => $promotion_data['id'],
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
				$use_promotion = use_promotion_count([
					'user_id' => $user_id,
					'id' => $promotion_data['id'],
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
	print_r($return);
	return $return;
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

function game_code_list(){
	return [
		"FOOTBALL",
		"STEP",
		"PARLAY",
		"GAME",
		"CASINO",
		"LOTTO",
		"M2",
		"MULTI_PLAYER",
		"TRADING",
		"KENO",
	];
}
function game_code_text_list(){
	return [
		"FOOTBALL" => "FOOTBALL",
		"STEP" => "STEP",
		"PARLAY" => "PARLAY",
		"GAME" => "GAME",
		"CASINO" => "CASINO",
		"LOTTO" => "LOTTO",
		"M2" => "M2",
		"MULTI_PLAYER" => "MULTI_PLAYER",
		"TRADING" => "HOTGRAPH",
		"KENO" => "KENO",
	];
}
?>
