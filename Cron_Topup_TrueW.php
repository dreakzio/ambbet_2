<?php
require('config.php');
//if(isset($_GET['api_token']) && trim($_GET['api_token']) == API_TOKEN_KEY){
error_reporting(0);
date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
$current_date = date("d/m/Y");
$current_date_chk = date("Y-m-d");
$before_date = date('d/m/Y',(strtotime ( '-1 day' , strtotime ( date("Y-m-d")) ) ));
$before_date_chk = date('Y-m-d',(strtotime ( '-1 day' , strtotime ( date("Y-m-d")) ) ));
ob_start('ob_gzhandler');
require('conn_cron.php');
require('lib/TMNOoo_bk.php');
require ('lib/send_line_message.php');

//Check line notify active
$sql_line_notify_status_check = "SELECT * FROM `web_setting` where name = 'line_notify_status' and value = '1'";
$con_line_notify_status_check = $obj_con_cron->query($sql_line_notify_status_check);
$check_line_notify_status = $con_line_notify_status_check->num_rows;
$status_create_line_notify = false;
$token_line_notify = '';
if($check_line_notify_status > 0){
	$sql_line_notify_token_check = "SELECT * FROM `web_setting` where name = 'line_notify_token' and value IS NOT NULL and value <> ''";
	$con_line_notify_token_check = $obj_con_cron->query($sql_line_notify_token_check);
	while($rs_line_token =$con_line_notify_token_check->fetch_assoc() ){
		if(!empty($rs_line_token['value'])){
			$status_create_line_notify = true;
			$token_line_notify = trim($rs_line_token['value']);
		}
	}
}

//Check min amount for disabled auto
$deposit_min_amount_for_disable_auto = null;
$sql_deposit_min_amount_for_disable_auto_check = "SELECT * FROM `web_setting` where name = 'deposit_min_amount_for_disable_auto'";
$con_deposit_min_amount_for_disable_auto_check = $obj_con_cron->query($sql_deposit_min_amount_for_disable_auto_check);
while($rs_deposit_min_amount_for_disable_auto =$con_deposit_min_amount_for_disable_auto_check->fetch_assoc() ){
	if(!empty($rs_deposit_min_amount_for_disable_auto['value'])){
		$deposit_min_amount_for_disable_auto = trim($rs_deposit_min_amount_for_disable_auto['value']);
	}
}

$sql_line_send_messages_status = "SELECT a.value as line_send_messages_status ,b.value as line_messages_token,c.value as web_name ,d.value as line_login_callback
										FROM `web_setting` as a , `web_setting` as b , `web_setting` as c , `web_setting` as d
										where a.name = 'line_send_messages_status'
										  and b.name = 'line_messages_token' 
										  and c.name = 'web_name'
										  and d.name = 'line_login_callback'";
$ds_line_send_messages_status = $obj_con_cron->query($sql_line_send_messages_status);
$da_line_send_messages_status   = $ds_line_send_messages_status->fetch_assoc();

//Check bank active
//$sql_bank_check = "SELECT * FROM `bank` where status = '1' and deleted = '0' and bank_number = '".$truewallet['accnum']."'";
$sql_bank_check = "SELECT * FROM `bank` where status = '1' and bank_code = '10' and deleted = '0'";
$con_bank_check = $obj_con_cron->query($sql_bank_check);
$chk_can_run_cron = false;
$chk_is_withdraw = false;
$chk_duplicate_date_and_hour_minute = [];
while($rs =$con_bank_check->fetch_assoc() ){
	$data_chk = [];
	$chk_can_run_cron = true;
	$chk_is_withdraw = false;
	if(isset($rs['status_withdraw']) && $rs['status_withdraw'] == "1"){
		$chk_is_withdraw = true;
	}
	if(
		isset($rs['start_time_can_not_deposit'])
		&& !is_null($rs['start_time_can_not_deposit'])
		&& !empty($rs['start_time_can_not_deposit'])
		&& isset($rs['end_time_can_not_deposit'])
		&& !is_null($rs['end_time_can_not_deposit'])
		&& !empty($rs['end_time_can_not_deposit'])
		&& !$chk_is_withdraw
	){
		try{
			$start_time_can_not_deposit = new DateTime($rs['start_time_can_not_deposit']);
			$end_time_can_not_deposit = new DateTime($rs['end_time_can_not_deposit']);
			$time_current = new DateTime(date('H:i'));
			if( in_array($start_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12"))){
				if(!in_array($end_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
					!in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
				){
					$end_time_can_not_deposit = $end_time_can_not_deposit->add(new DateInterval('P1D'));
				}
				if(!in_array($time_current->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
					in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
				){
					$start_time_can_not_deposit = $start_time_can_not_deposit->sub(new DateInterval('P1D'));
				}
				if($start_time_can_not_deposit->getTimestamp() > $end_time_can_not_deposit->getTimestamp()){
					echo json_encode(['status'=>false,"message" => "Start-End Time ignore check 1"]);
					$chk_can_run_cron = false;
					//exit();
				}else if(
					$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
					$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
				){
					if($start_time_can_not_deposit->format("H") == "23" &&  $end_time_can_not_deposit->format("H") == "23" &&
						in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
					){

					}else{
						echo json_encode(['status'=>false,"message" => "Start-End Time ignore check 2"]);
						$chk_can_run_cron = false;
					}
				}
			}else if(
				$start_time_can_not_deposit->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
			){
				if(
					$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
					$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
				){
					echo json_encode(['status'=>false,"message" => "Start-End Time ignore check 3"]);
					$chk_can_run_cron = false;
					//exit();
				}
			}
		}catch (Exception $ex){

		}
	}

	if($chk_can_run_cron){

		try{
			$truewallet = $rs;
			if(!empty($truewallet['username']) && !empty($truewallet['password']) && !empty( $truewallet['api_token_1']) && strlen($truewallet['api_token_1']) >= 6  && !empty( $truewallet['api_token_2']) && strlen( $truewallet['api_token_2']) <= 60){
				//$TMNOne = new TMNOne();
				//$TMNOne->setData($truewallet['username'], $truewallet['bank_number'], $truewallet['api_token_2'], $truewallet['password']);
				//$TMNOne->loginWithPin6($truewallet['api_token_1']); //Login เข้าระบบ Wallet ด้วย PIN

				$_TMN = array();
				$_TMN['tmn_key_id'] = $truewallet['username']; //Key ID จากระบบ TMNOne
				$_TMN['mobile_number'] = $truewallet['bank_number']; //เบอร์ Wallet
				$_TMN['login_token'] = $truewallet['api_token_2']; //login_token จากขั้นตอนการเพิ่มเบอร์ Wallet
				$_TMN['pin'] = $truewallet['api_token_1']; //อย่าลืมใส่ PIN 6 หลักของ Wallet
				$_TMN['tmn_id'] = $truewallet['password']; //tmn_id จากขั้นตอนการเพิ่มเบอร์ Wallet

				$TMNOoo = new TMNOoo($_TMN);
				//$TMNOoo->setProxy('zproxy.lum-superproxy.io:22225', 'brd-customer-hl_ebdb3c0e-zone-data_center-country-th', '0pi1xakwwrg5'); //เปิดใช้งาน HTTP Proxy สำหรับเชื่อมต่อกับระบบของ Wallet
				$random_limit = 20;
				//$TMNOoo->Login();
				$TMNOoo->loginWithPin6($_TMN['pin']);
				$balance =  $TMNOoo->GetBalance();
				$obj_con_cron->autocommit(true);
				if(!empty($balance) && isset($balance['data']) && isset($balance['data']['current_balance'])){
					$obj_con_cron->query("UPDATE `bank` SET `balance` = '".str_replace(',', '', $balance['data']['current_balance'])."',`updated_at` = '".date('Y-m-d H:i:s')."' WHERE `bank`.`bank_number` = '".$truewallet['bank_number']."'");
				}else{
					echo json_encode(['status'=>false,"message" => "Bank Truewallet ".$truewallet['bank_number']." can not get Balance : {} ".json_encode($balance)]);
				}
				if($chk_is_withdraw){
					echo json_encode(['status'=>true,"message" => "Bank truewallet for withdraw available : ".$truewallet['bank_number']]);
				}else{
					echo json_encode(['status'=>true,"message" => "Bank truewallet for deposit available : ".$truewallet['bank_number']]);
					//$trans = $TMNOne->fetchTransactionHistory(date('Y-m-d',time()-86400), date('Y-m-d',time()+86400),$random_limit);
					$trans = $TMNOoo->GetTransaction(date('Y-m-d',time()-86400), date('Y-m-d',time()+86400),$random_limit,1);
					/*echo json_encode($trans);
					exit();*/

					if(isset($trans["data"]) && isset($trans["data"]['total']) && $trans["data"]['total'] >= 0 ){
						if (isset($trans['code']) && $trans['code'] !== "" && $trans['code'] == "HTC-200") {
							$trans["data"]["activities"] = array_reverse($trans["data"]["activities"]);

							$obj_con_cron->autocommit(false);

							foreach ($trans["data"]["activities"] as $index => $report) {

								$data = null;
								$report_id = $report['report_id'];
								$sql_report_sms = "SELECT id FROM `report_smses` where report_id = '".$report_id."' and is_bot_running = '1' and config_api_id = '".$truewallet['id']."'";
								$con_check_report_sms = $obj_con_cron->query($sql_report_sms);
								$check_report_sms = $con_check_report_sms->num_rows;
								if($check_report_sms == 0){
									//$data = $TMNOne->fetchTransactionInfo($report["report_id"]);
									$data = $TMNOoo->GetTransactionReport($report["report_id"]);
									if((isset($data['code']) && $data['code'] == "HTC-200") || isset($data['transaction_id']) || (isset($data['cached']) && $data['cached'])){
										if(isset($data['transaction_id']) || (isset($data['cached']) && $data['cached'])){
											$data = [
												'data' => $data
											];
										}
										$logo_url = $report['logo_url'];
										$type = $report['type'];
										$date = explode(" ",trim($report['date_time']));
										$data_explode = explode("-",str_replace('/', '-', $date[0]));
										$create_date = substr(date("Y"),0,-strlen($data_explode[2])).$data_explode[2].'-'.$data_explode[1].'-'.$data_explode[0];
										$create_time = $date[1].':00';
										if(is_numeric(str_replace(',', '', $report['amount'])) && (float)str_replace(',', '', $report['amount']) > 0){
											$type_deposit_withdraw = "D";
										}else{
											$type_deposit_withdraw = "W";
										}
										$amount = str_replace("+","",str_replace(',', '', $report['amount']));
										$amount = str_replace("-","",$amount);
										$title = $report['title'];
										$sub_title = $report['sub_title'];
										$payment_gateway = $title.(empty($sub_title) ? '' : ' - '.$sub_title);

										if(
											isset($data['data']['transaction_id']) &&
											isset($data['data']['transaction_reference_id'])
										){
											$ref_number = isset($data['data']['transaction_id']['value']) ? $data['data']['transaction_id']['value'] : null;
											$ref1 = isset($data['data']['transaction_reference_id']) ? preg_replace('/[^0-9]+/', '', $data['data']['transaction_reference_id']['value']) : null;
											$service_code = isset($data['data']['action']) && isset($data['data']['action']['value']) ? $data['data']['action']['value'] : null;
											$tel_number_cust_code = $ref1;
											$payment_gateway .= is_null($tel_number_cust_code) ? "" : " ".$tel_number_cust_code;
										}else{
											$ref_number = null;
											if(empty($data['data']['section4'])){
												if($data['data']['section3']['column2']['cell1']['title'] == "เลขที่อ้างอิง"){
													$ref_number = $data['data']['section3']['column2']['cell1']['value'];
												}
											}else{
												$ref_number = $data['data']['section4']['column2']['cell1']['value'];
											}
											$ref1 = isset($data['data']['ref1']) ? $data['data']['ref1'] : null;
											$service_code = isset($data['data']['service_code']) ? $data['data']['service_code'] : null;

											$tel_number_cust_code = null;
											if(isset($data['data']['section_sof'])){
												$tel_number_cust_code = $data['data']['section2']['column1']['cell1']['value'][0]['title'];
											}else{
												if(isset($data['data']['service_type'])){
													$tel_number_cust_code = $data['data']['section2']['column1']['cell1']['value'];
												}else{
													$tel_number_cust_code = $data['data']['section2']['column1']['cell1']['value'];
												}
											}
											if(!is_null($ref1)){
												$tel_number_cust_code = $ref1;
											}
										}
										$data_insert = [
											"logo_url" => $logo_url,
											"tel_number_cust_code" => $tel_number_cust_code,
											"type" => $type,
											"payment_gateway" => $payment_gateway,
											"amount" => $amount,
											"create_date" => $create_date,
											"type_deposit_withdraw" => $type_deposit_withdraw,
											"report_id" => $report_id,
											"ref_number" => $ref_number,
											"create_time"=>$create_time,
										];
										///print_r($data_insert);
										/*if (!file_exists('tmp/stmt-tw/'.$create_date)) {
											mkdir('tmp/stmt-tw/'.$create_date, 0755, true);
										}*/
										//$cache_filename = 'tmp/stmt-tw/'.$create_date.'/' .base64_encode($report_id);
										if(true){
											try {
												$check_all = true;
												//Insert report sms
												$sql_insert_report_sms = "INSERT INTO `report_smses` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`type_deposit_withdraw`,`tel_number_cust_code`,`logo_url`,`report_id`,`type`,`ref_number`) VALUES (NULL, '".$rs['id']."','".$data_insert['payment_gateway']."','".$data_insert['amount']."', current_timestamp(),'1','".$data_insert['create_date']."','".$data_insert['create_time']."','".$data_insert['type_deposit_withdraw']."','".$data_insert['tel_number_cust_code']."','".$data_insert['logo_url']."','".$data_insert['report_id']."','".$data_insert['type']."','".$data_insert['ref_number']."')";
												$check_all = $obj_con_cron->query($sql_insert_report_sms);
												if($check_all){
													$report_sms_id = $obj_con_cron->insert_id;

													//Insert report
													$sql_insert_report = "INSERT INTO `reports` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`sms_statement_refer_id`,`type_deposit_withdraw`,`tel_number_cust_code`,`logo_url`,`report_id`,`type`,`ref_number`) VALUES (NULL, '".$rs['id']."','".$data_insert['payment_gateway']."','".$data_insert['amount']."', current_timestamp(),'1','".$data_insert['create_date']."','".$data_insert['create_time']."','".$report_sms_id."','".$data_insert['type_deposit_withdraw']."','".$data_insert['tel_number_cust_code']."','".$data_insert['logo_url']."','".$data_insert['report_id']."','".$data_insert['type']."','".$data_insert['ref_number']."')";
													$check_all =  $obj_con_cron->query($sql_insert_report);
													if($check_all){
														$report_id = $obj_con_cron->insert_id;

														//Update sms_statement_refer_id on report sms
														$sql_update_report_sms = "UPDATE `report_smses` SET `sms_statement_refer_id` = '".$report_id."' WHERE `report_smses`.`id` = ".$report_sms_id;
														$check_all =  $obj_con_cron->query($sql_update_report_sms);
														if($check_all){
															//ดำเนินการออโต้เติมเครดิต
															if(!is_null($service_code) && $service_code == "creditor"){
																$chk_ignore_create_transaction_credit_history =  false;
																if(
																	isset($rs['start_time_can_not_deposit'])
																	&& !is_null($rs['start_time_can_not_deposit'])
																	&& !empty($rs['start_time_can_not_deposit'])
																	&& isset($rs['end_time_can_not_deposit'])
																	&& !is_null($rs['end_time_can_not_deposit'])
																	&& !empty($rs['end_time_can_not_deposit'])
																	&& !$chk_is_withdraw
																){
																	try {
																		$time_explode = explode(":",trim($data_insert['create_time']));
																		$start_time_can_not_deposit_stmt = new DateTime($data_insert['create_date']." ".$rs['start_time_can_not_deposit']);
																		$end_time_can_not_deposit_stmt = new DateTime($data_insert['create_date']." ".$rs['end_time_can_not_deposit']);
																		$time_stmt_chk = new DateTime($data_insert['create_date']." ".$time_explode[0].":".$time_explode[1]);
																		if(
																			$time_stmt_chk->getTimestamp() >= $start_time_can_not_deposit_stmt->getTimestamp() &&
																			$time_stmt_chk->getTimestamp() <= $end_time_can_not_deposit_stmt->getTimestamp()
																		){
																			if($obj_con_cron->commit()){
																				//$content = base64_encode($report_id);
																				//file_put_contents($cache_filename, $content);
																			}
																			$chk_ignore_create_transaction_credit_history = true;
																			echo json_encode(['status'=>true,"message" => "Bank Truewallet for deposit available : ".$truewallet['bank_number']." Ignore create transaction, credit history => start & end time range for auto ".$rs['start_time_can_not_deposit']." - ".$rs['end_time_can_not_deposit']]);
																		}
																	}catch (Exception $ex){
																		if($obj_con_cron->commit()){
																			//$content = base64_encode($report_id);
																			//file_put_contents($cache_filename, $content);
																		}
																		echo json_encode(['status'=>true,"message" => "Bank Truewallet for deposit available : ".$truewallet['bank_number']." Ignore create transaction, credit history => start & end time range for auto ".$rs['start_time_can_not_deposit']." - ".$rs['end_time_can_not_deposit']. "Error {} ".$ex->getMessage()]);
																	}
																	if(!$chk_ignore_create_transaction_credit_history){
																		if(!in_array($rs['id'].$data_insert['payment_gateway'].$data_insert['create_date'].explode(":",trim($data_insert['create_time']))[0].":".explode(":",trim($data_insert['create_time']))[1],$chk_duplicate_date_and_hour_minute)){
																			$chk_duplicate_date_and_hour_minute[] = $rs['id'].$data_insert['payment_gateway'].$data_insert['create_date'].explode(":",trim($data_insert['create_time']))[0].":".explode(":",trim($data_insert['create_time']))[1];
																		}else{
																			$chk_ignore_create_transaction_credit_history = true;
																			if($obj_con_cron->commit()){

																			}
																		}
																	}
																}

																if(!$chk_ignore_create_transaction_credit_history) {

																	if (!is_null($deposit_min_amount_for_disable_auto) && is_numeric($deposit_min_amount_for_disable_auto) && (float)$deposit_min_amount_for_disable_auto > 0 && (float)$data_insert['amount'] >= (float)$deposit_min_amount_for_disable_auto) {
																		if($obj_con_cron->commit()){
																			//$content = base64_encode($report_id);
																			//file_put_contents($cache_filename, $content);
																		}
																		echo json_encode(['status' => true, "message" => "Bank Truewallet for deposit available : " . $truewallet['bank_number'] . " Ignore auto deposit amount, " . (float)$data_insert['amount'] . " >= " . (float)$deposit_min_amount_for_disable_auto]);
																	} else {
																		$bank_acc = preg_replace('/[^0-9]+/', '', $data_insert['tel_number_cust_code']);
																		if(is_numeric($bank_acc)){

																			if(strlen($bank_acc) >= 10 ){
																				$sql_acc_check = "SELECT id,amount_deposit_auto,bank,bank_number,bank_name,username,linebot_userid FROM `account` where username like '%".$bank_acc."%' and deleted = '0' ORDER BY active_deposit_date DESC";
																			}else{
																				$payment_gateway_explode = explode("*** ".$bank_acc,$payment_gateway);
																				$bank_name_like = count($payment_gateway_explode) >= 2 ? trim(str_replace("Receive money from - ","",trim($payment_gateway_explode[0]))) : "";
																				$sql_acc_check = "SELECT id,amount_deposit_auto,bank,bank_number,bank_name,username,linebot_userid FROM `account` where ((bank_name like '".$bank_name_like."%') or (username like '%".$bank_acc."%' and bank_name like '".$bank_name_like."%')) and deleted = '0' ORDER BY active_deposit_date DESC";
																			}
																			$con_acc_check = $obj_con_cron->query($sql_acc_check);
																			$check_acc = $con_acc_check->num_rows;
																			if($check_acc == 1){
																				$check_add_once = false;
																				while($rs_acc = $con_acc_check->fetch_assoc()) {
																					if(!$check_add_once) {
																						$time_explode = explode(":",trim($data_insert['create_time']));
																						//Check transaction
																						$sql_check = "SELECT id FROM `transaction` where DATE_FORMAT(date_bank,'%Y-%m-%d %H:%i') = '".$data_insert['create_date']." ".$time_explode[0].":".$time_explode[1]."' and bank_number like '%".$rs_acc['bank_number']."%' and amount = '".$data_insert['amount']."' and type = '1'";
																						$con_check = $obj_con_cron->query($sql_check);
																						$check = $con_check->num_rows;
																						if($check == 0){
																							$check_add_once = true;
																							$sql ="INSERT INTO `transaction` (`id`, `date_bank`, `amount`,`account`,`type`,`bank_number`,`updated_at`) VALUES (NULL, '".$data_insert['create_date']." ".$data_insert['create_time']."', '".$data_insert['amount']."', '".$rs_acc['id']."', '1', '".$rs_acc['bank_number']."', current_timestamp())";
																							$check_all =  $obj_con_cron->query($sql);
																							if($check_all){
																								$credit_before = $rs_acc['amount_deposit_auto'];
																								$credit_after = !is_null($rs_acc['amount_deposit_auto']) && $rs_acc['amount_deposit_auto'] !== "" ? (float)$rs_acc['amount_deposit_auto'] + $data_insert['amount'] : $data_insert['amount'];
																								$check_all =  $obj_con_cron->query("UPDATE `account` SET `amount_deposit_auto` = '".$credit_after."' WHERE `account`.`id` = ".$rs_acc['id']);
																								if($check_all){
																									$sql_insert_credit_his ="INSERT INTO `credit_history` (`id`, `process`, `credit_before`,`credit_after`,`type`,`account`,`transaction`,`admin`,`date_bank`,`bank_id`,`bank_name`,`bank_number`,`bank_code`,`username`) VALUES (NULL, '".$data_insert['amount']."', '".$credit_before."', '".$credit_after."', '1', '".$rs_acc['id']."', '1', '0', '".date("Y-m-d H:i:s", strtotime($data_insert['create_date']." ".$data_insert['create_time']))."', '".$truewallet['id']."', '".$truewallet['account_name']."', '".$truewallet['bank_number']."', '".$truewallet['bank_code']."', '".$rs_acc['username']."')";
																									$check_all =  $obj_con_cron->query($sql_insert_credit_his);
																									if($check_all){
																										$credit_history_id = $obj_con_cron->insert_id;

																										//Update deposit_withdraw_id on report sms,report
																										$sql_update_report_sms = "UPDATE `report_smses` SET `deposit_withdraw_id` = '".$credit_history_id."' WHERE `report_smses`.`id` = ".$report_sms_id;
																										$check_all =  $obj_con_cron->query($sql_update_report_sms);
																										if($check_all){
																											$sql_update_report = "UPDATE `reports` SET `deposit_withdraw_id` = '".$credit_history_id."' WHERE `reports`.`id` = ".$report_id;
																											$check_all =  $obj_con_cron->query($sql_update_report);
																											if($check_all){
																												//Insert line notify
																												if($status_create_line_notify && !empty($token_line_notify)){
																													$message = "ยอดฝาก ".number_format($data_insert['amount'],2)." บาท ยูส ".$rs_acc['username']." เวลา ".$data_insert['create_date']." ".$data_insert['create_time']." ปรับโดย AUTO";
																													$sql_insert_line_notify ="INSERT INTO `log_line_notify` (`id`, `type`, `message`) VALUES (NULL, '1', '".$message."')";
																													$obj_con_cron->query($sql_insert_line_notify);
																												}

																												if($da_line_send_messages_status['line_send_messages_status']==1){
																													$bank_list = array(
																														'01' => 'bbl',
																														'02' => 'kbank',
																														'03' => 'ktb',
																														'04' => 'tmb',
																														'05' => 'scb',
																														'06' => 'bay',
																														'07' => 'gsb',
																														'08' => 'tbank',
																														'09' => 'baac',
																														'1' => 'bbl',
																														'2' => 'kbank',
																														'3' => 'ktb',
																														'4' => 'tmb',
																														'5' => 'scb',
																														'6' => 'bay',
																														'7' => 'gsb',
																														'8' => 'tbank',
																														'9' => 'baac',
																													);
																													$line_msg = array();
																													$line_msg['web_name'] = $da_line_send_messages_status['web_name'];
																													$line_msg['bank_tf_name'] = $bank_list[$rs_acc['bank']];
																													$line_msg['bank_tf_number'] = $rs_acc['bank_number'];
																													$line_msg['balance'] = number_format($data_insert['amount'],2);
																													$line_msg['bank_time'] = $data_insert['create_date']." ".$data_insert['create_time'];
																													$line_msg['credit_after'] = $credit_after;
																													$line_msg['url_login'] = $da_line_send_messages_status['line_login_callback'];
																													$line_msg['linebot_userid'] = $rs_acc['linebot_userid'];
																													$line_msg['type_tran'] = 1;

																													if($rs_acc['linebot_userid']!=''){
																														$sendtoline = new send_line_message();
																														$sendtoline->sendline_deposit($line_msg,$da_line_send_messages_status['line_messages_token']);
																													}

																												}

																												if($obj_con_cron->commit()){
//																															$content = base64_encode($report_id);
																													//file_put_contents($cache_filename, $content);
																												}
																												echo json_encode(['status'=>true,"message"=>$data_insert['create_date']." ".$data_insert['create_time']." | ".$data_insert['amount'].' | '.$rs_acc['bank_number']]);
																											}else{
																												if($obj_con_cron){
																													$obj_con_cron->rollback();
																												}else if(!is_null($obj_con_cron)){
																													$obj_con_cron->rollback();
																												}
																												echo json_encode(['status'=>false,"message"=>"Exception Sql : ".$e]);
																											}
																										}else{
																											if($obj_con_cron){
																												$obj_con_cron->rollback();
																											}else if(!is_null($obj_con_cron)){
																												$obj_con_cron->rollback();
																											}
																											echo json_encode(['status'=>false,"message"=>"Exception Sql : ".$e]);
																										}
																									}else{
																										if($obj_con_cron){
																											$obj_con_cron->rollback();
																										}else if(!is_null($obj_con_cron)){
																											$obj_con_cron->rollback();
																										}
																										echo json_encode(['status'=>false,"message"=>"Exception Sql : ".$e]);
																									}

																								}else{
																									if($obj_con_cron){
																										$obj_con_cron->rollback();
																									}else if(!is_null($obj_con_cron)){
																										$obj_con_cron->rollback();
																									}
																									echo json_encode(['status'=>false,"message"=>"Exception Sql : ".$e]);
																								}
																							}else{
																								if($obj_con_cron){
																									$obj_con_cron->rollback();
																								}else if(!is_null($obj_con_cron)){
																									$obj_con_cron->rollback();
																								}
																								echo json_encode(['status'=>false,"message"=>"Exception Sql : ".$e]);
																							}

																						}
																					}

																				}
																			}else{
																				if($obj_con_cron->commit()){
																					//$content = base64_encode($report_id);
																					//file_put_contents($cache_filename, $content);
																				}
																			}
																		}else{
																			if($obj_con_cron->commit()){
																				//$content = base64_encode($report_id);
																				//file_put_contents($cache_filename, $content);
																			}
																		}
																	}
																}
															}else{
																if($obj_con_cron->commit()){
																	//$content = base64_encode($report_id);
																	//file_put_contents($cache_filename, $content);
																}
															}
														}else{
															if($obj_con_cron){
																$obj_con_cron->rollback();
															}else if(!is_null($obj_con_cron)){
																$obj_con_cron->rollback();
															}
															echo json_encode(['status'=>false,"message"=>"Exception Sql : ".$e]);
														}
													}else{
														if($obj_con_cron){
															$obj_con_cron->rollback();
														}else if(!is_null($obj_con_cron)){
															$obj_con_cron->rollback();
														}
														echo json_encode(['status'=>false,"message"=>"Exception Sql : ".$e]);
													}
												}else{
													if($obj_con_cron){
														$obj_con_cron->rollback();
													}else if(!is_null($obj_con_cron)){
														$obj_con_cron->rollback();
													}
													echo json_encode(['status'=>false,"message"=>"Exception Sql : ".$e]);
												}
											}catch(Exception $e) {
												if($obj_con_cron){
													$obj_con_cron->rollback();
												}else if(!is_null($obj_con_cron)){
													$obj_con_cron->rollback();
												}
												echo json_encode(['status'=>false,"message"=>"Exception Sql : ".$e]);
											}
										}else{
											echo json_encode(['status'=>true,"message" => "Bank truewallet for deposit available : ".$truewallet['bank_number']." Report ID cache exist #".$report_id." {} ".json_encode($data)]);
										}

									}else{
										echo json_encode(['status'=>true,"message" => "Bank truewallet for deposit available : ".$truewallet['bank_number']." No transaction report #".$report_id." {} ".json_encode($data)]);
									}
								}
							}
						}else{
							echo json_encode(['status'=>true,"message" => "Bank truewallet for deposit available : ".$truewallet['bank_number']." No transaction ".json_encode($trans)]);
						}
					}else{
						echo json_encode(['status'=>true,"message" => "Bank truewallet for deposit available : ".$truewallet['bank_number']." No transaction ".json_encode($trans)]);
					}
				}
			}else{
				echo json_encode(['status'=>false,"message" => "Bank Truewallet ".$truewallet['bank_number']." Please check [username,password,pin and other token]"]);
			}
		}catch (Exception $ex){
			echo json_encode(['status'=>false,"message" => "Bank Truewallet ".$truewallet['bank_number']." Error Exception ".$ex->getMessage()]);
		}
	}

}
$chk_duplicate_date_and_hour_minute = [];
//}
?>
