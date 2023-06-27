<?php
require('config.php');

if(isset($_GET['api_token']) && trim($_GET['api_token']) == API_TOKEN_KEY){

	$sec_rand = rand(5,8);
	sleep($sec_rand);

	ini_set('display_errors', 1);
	error_reporting(E_ALL);
	date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
	$current_date = date("d/m/Y");
	$current_date_chk = date("Y-m-d");
	$before_date = date('d/m/Y',(strtotime ( '-1 day' , strtotime ( date("Y-m-d")) ) ));
	$before_date_chk = date('Y-m-d',(strtotime ( '-1 day' , strtotime ( date("Y-m-d")) ) ));
	ob_start('ob_gzhandler');
	require('conn_cron.php');
	require('lib/Scb.php');
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
	//$sql_bank_check = "SELECT * FROM `bank` where status = '1' and deleted = '0' and bank_number = '".$scb['accnum']."'";
	$sql_bank_check = "SELECT * FROM `bank` where status = '1' and (bank_code = '05' or bank_code = '5') and deleted = '0' order by status_withdraw asc";
	$con_bank_check = $obj_con_cron->query($sql_bank_check);
	$chk_can_run_cron = false;
	$chk_is_withdraw = false;
	$data_stmt_acc_list_api = [];
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

				$scb = $rs;
				//print_r($scb);
				$scb['api_token_1'] = decrypt(base64_decode($scb['api_token_1']),SECRET_KEY_SALT);
				$scb['api_token_2'] = decrypt(base64_decode($scb['api_token_2']),SECRET_KEY_SALT);
				//print_r($scb);
				$obj_con_cron->autocommit(true);
				if(!array_key_exists($scb['bank_number'],$data_stmt_acc_list_api)){
					$api = new scb($scb['api_token_1'],$scb['api_token_2'],$scb['bank_number']); //$deviceId,$api_refresh,$accnum

					//die();
					$balance = $api->GetBalance();
					//print_r($balance);
					if(isset($balance['status']) &&  $balance['status']==0){
						if($status_create_line_notify && !empty($token_line_notify)){
							$message = "SCB Device ID expired Bank NO :".$scb['bank_number'];
							$sql_line_notify_device_id_error ="INSERT INTO `log_line_notify` (`id`, `type`, `message`) VALUES (NULL, '1', '".$message."')";
							$obj_con_cron->query($sql_line_notify_device_id_error);
						}
						continue;
					}
				//	die();

					$balance = json_decode($balance,true);
					$transactions = $api->getTransaction();
					$data_stmt_acc_list_api[$scb['bank_number']] = [
						'balance' => $balance,
						'transaction' => $transactions,
					];
				}else{
					$balance = $data_stmt_acc_list_api[$scb['bank_number']]['balance'];
					$transactions = $data_stmt_acc_list_api[$scb['bank_number']]['transaction'];
				}
				if(!empty($balance) && isset($balance['availableBalance']) && is_numeric($balance['availableBalance'])){
					$obj_con_cron->query("UPDATE `bank` SET `balance` = '".$balance['availableBalance']."' WHERE `bank`.`bank_number` = '".$scb['bank_number']."'");
				}else{
					echo json_encode(['status'=>false,"message" => "Bank ".$scb['bank_number']." can not get Balance, Please check token"]);
				}
				if($chk_is_withdraw){
					echo json_encode(['status'=>true,"message" => "Bank for withdraw available : ".$scb['bank_number']]);
					//$trans = $api->getTransactionWithdraw();
					$trans = $transactions['withdraw'];
					$trans = array_reverse($trans);
					$after_date_chk = date('Y-m-d',(strtotime ( '+1 day' , strtotime ( date("Y-m-d")) ) ));
					foreach ($trans as $v) {
						$balance = (float) str_replace(',', '', $v['withdraws']);
						$bank_number = explode("_",$v['description']);
						if(!empty($bank_number) && count($bank_number) > 1){
							$bank_number = $bank_number[1];
						}
						$date_explode = explode("/",trim($v['date'])); //d/m/Y
						$v['date'] = $date_explode[2]."-".$date_explode[1]."-".$date_explode[0];
						if(trim($v['date']) == $current_date_chk || trim($v['date']) == $before_date_chk || trim($v['date']) == $after_date_chk){
							$time_explode = explode(":",trim($v['time']));

							//Check report sms
							$payment_gateway = $v['description_full'];
							$sql_report_sms = "SELECT id FROM `report_smses` where DATE_FORMAT(create_date,'%Y-%m-%d') = '".$v['date']."' and DATE_FORMAT(create_time,'%H:%i') = '".$time_explode[0].":".$time_explode[1]."' and type_deposit_withdraw = 'W' and amount <=> CAST('".$balance."' AS DECIMAL(15, 2)) and payment_gateway = '".$payment_gateway."' and is_bot_running = '1'";
							$con_check_report_sms = $obj_con_cron->query($sql_report_sms);
							$check_report_sms = $con_check_report_sms->num_rows;
							if($check_report_sms == 0) {

								//Insert report sms
								$sql_insert_report_sms = "INSERT INTO `report_smses` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`type_deposit_withdraw`) VALUES (NULL, '" . $rs['id'] . "','" . $payment_gateway . "','" . $balance . "', current_timestamp(),'1','" . $v['date'] . "','" . $v['time'] . "','W')";
								$obj_con_cron->query($sql_insert_report_sms);
								$report_sms_id = $obj_con_cron->insert_id;

								//Insert report
								$sql_insert_report = "INSERT INTO `reports` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`sms_statement_refer_id`,`type_deposit_withdraw`) VALUES (NULL, '" . $rs['id'] . "','" . $payment_gateway . "','" . $balance . "', current_timestamp(),'1','" . $v['date'] . "','" . $v['time'] . "','" . $report_sms_id . "','W')";
								$obj_con_cron->query($sql_insert_report);
								$report_id = $obj_con_cron->insert_id;

								//Update sms_statement_refer_id on report sms
								$sql_update_report_sms = "UPDATE `report_smses` SET `sms_statement_refer_id` = '" . $report_id . "' WHERE `report_smses`.`id` = " . $report_sms_id;
								$obj_con_cron->query($sql_update_report_sms);
							}
						}
					}
					//exit();
				}else{
					echo json_encode(['status'=>true,"message" => "Bank for deposit available : ".$scb['bank_number']]);
					//$trans = $api->getTransaction();
					$trans = $transactions['deposit'];
					/*echo json_encode($trans);
					exit();*/
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
					$bank_stmt_list = array(
						'bbl' => ['01','1'],
						'kbank' => ['02','2'],
						'ktb' => ['03','3'],
						'tmb' => ['04','4'],
						'scb' => ['05','5'],
						'bay' => ['06','6'],
						'gsb' => ['07','7'],
						'tbank' => ['08','8'],
						'baac' => ['09','9'],
						'ttb' => ['08','8','04','4'],
					);

					/*usort($trans, function($a, $b) {
					    try{
							$datetime1 = strtotime($a['date']." ".$a['time']);
							$datetime2 = strtotime($b['date']." ".$b['time']);
							if ($datetime1 < $datetime2)
								return 1;
							else if ($datetime1 > $datetime2)
								return -1;
							else
								return 0;
						}catch (Exception $ex){

						}
					});*/
					$trans = array_reverse($trans);

					$after_date_chk = date('Y-m-d',(strtotime ( '+1 day' , strtotime ( date("Y-m-d")) ) ));
					$obj_con_cron->autocommit(false);

					foreach ($trans as $v) {
						$balance = (float) str_replace(',', '', $v['deposits']);
						$bank_number = explode("_",$v['description']);
						if(!empty($bank_number) && count($bank_number) > 1){
							$bank_number = $bank_number[1];
						}
						$date_explode = explode("/",trim($v['date'])); //d/m/Y
						$v['date'] = $date_explode[2]."-".$date_explode[1]."-".$date_explode[0];

						$payment_gateway = $v['description_full'];
						/*if (!file_exists('tmp/stmt-scb/'.$v['date'])) {
							mkdir('tmp/stmt-scb/'.$v['date'], 0755, true);
						}*/
						//$cache_filename = 'tmp/stmt-scb/'.$v['date'].'/' .base64_encode($time_explode[0].$time_explode[1].$balance.$payment_gateway) ;
						if((trim($v['date']) == $current_date_chk || trim($v['date']) == $before_date_chk || trim($v['date']) == $after_date_chk) && !in_array($v,$data_chk)){
							$data_chk[] = $v;
							$time_explode = explode(":",trim($v['time']));

							//Check report sms
							$sql_report_sms = "SELECT id FROM `report_smses` where DATE_FORMAT(create_date,'%Y-%m-%d') = '".$v['date']."' and DATE_FORMAT(create_time,'%H:%i') = '".$time_explode[0].":".$time_explode[1]."' and type_deposit_withdraw = 'D' and amount <=> CAST('".$balance."' AS DECIMAL(15, 2)) and payment_gateway = '".$payment_gateway."' and is_bot_running = '1'";
							$con_check_report_sms = $obj_con_cron->query($sql_report_sms);
							$check_report_sms = $con_check_report_sms->num_rows;

							if($check_report_sms == 0){
								try
								{
									$check_all = true;

									//Insert report sms
									$sql_insert_report_sms = "INSERT INTO `report_smses` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`type_deposit_withdraw`) VALUES (NULL, '".$rs['id']."','".$payment_gateway."','".$balance."', current_timestamp(),'1','".$v['date']."','".$v['time']."','D')";
									$check_all = $obj_con_cron->query($sql_insert_report_sms);
									if($check_all){
										$report_sms_id = $obj_con_cron->insert_id;

										//Insert report
										$sql_insert_report = "INSERT INTO `reports` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`sms_statement_refer_id`,`type_deposit_withdraw`) VALUES (NULL, '".$rs['id']."','".$payment_gateway."','".$balance."', current_timestamp(),'1','".$v['date']."','".$v['time']."','".$report_sms_id."','D')";
										$check_all = $obj_con_cron->query($sql_insert_report);
										if($check_all){
											$report_id = $obj_con_cron->insert_id;

											//Update sms_statement_refer_id on report sms
											$sql_update_report_sms = "UPDATE `report_smses` SET `sms_statement_refer_id` = '".$report_id."' WHERE `report_smses`.`id` = ".$report_sms_id;
											$check_all =  $obj_con_cron->query($sql_update_report_sms);
											if($check_all){
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
														$start_time_can_not_deposit_stmt = new DateTime($v['date']." ".$rs['start_time_can_not_deposit']);
														$end_time_can_not_deposit_stmt = new DateTime($v['date']." ".$rs['end_time_can_not_deposit']);
														$time_stmt_chk = new DateTime($v['date']." ".$time_explode[0].":".$time_explode[1]);
														if(
															$time_stmt_chk->getTimestamp() >= $start_time_can_not_deposit_stmt->getTimestamp() &&
															$time_stmt_chk->getTimestamp() <= $end_time_can_not_deposit_stmt->getTimestamp()
														){
															if($obj_con_cron->commit()){
																//$content = base64_encode("SELECT * FROM `report_smses` where DATE_FORMAT(create_date,'%Y-%m-%d') = '".$v['date']."' and DATE_FORMAT(create_time,'%H:%i') = '".$time_explode[0].":".$time_explode[1]."' and type_deposit_withdraw = 'D' and amount <=> CAST('".$balance."' AS DECIMAL(15, 2)) and payment_gateway = '".$payment_gateway."' and is_bot_running = '1'");
																//file_put_contents($cache_filename, $content);
															}
															$chk_ignore_create_transaction_credit_history = true;
															echo json_encode(['status'=>true,"message" => "Bank for deposit available : ".$scb['bank_number']." Ignore create transaction, credit history => start & end time range for auto ".$rs['start_time_can_not_deposit']." - ".$rs['end_time_can_not_deposit']]);
														}
													}catch (Exception $ex){
														if($obj_con_cron->commit()){
															//$content = base64_encode("SELECT * FROM `report_smses` where DATE_FORMAT(create_date,'%Y-%m-%d') = '".$v['date']."' and DATE_FORMAT(create_time,'%H:%i') = '".$time_explode[0].":".$time_explode[1]."' and type_deposit_withdraw = 'D' and amount <=> CAST('".$balance."' AS DECIMAL(15, 2)) and payment_gateway = '".$payment_gateway."' and is_bot_running = '1'");
															//file_put_contents($cache_filename, $content);
														}
														echo json_encode(['status'=>true,"message" => "Bank for deposit available : ".$scb['bank_number']." Ignore create transaction, credit history => start & end time range for auto ".$rs['start_time_can_not_deposit']." - ".$rs['end_time_can_not_deposit']. "Error {} ".$ex->getMessage()]);
													}
													if(!$chk_ignore_create_transaction_credit_history){
														if(!in_array($rs['id'].$payment_gateway.$v['date'].$time_explode[0].":".$time_explode[1],$chk_duplicate_date_and_hour_minute)){
															$chk_duplicate_date_and_hour_minute[] = $rs['id'].$payment_gateway.$v['date'].$time_explode[0].":".$time_explode[1];
														}else{
															$chk_ignore_create_transaction_credit_history = true;
															if($obj_con_cron->commit()){

															}
														}
													}
												}

												if(!$chk_ignore_create_transaction_credit_history){

													if(!is_null($deposit_min_amount_for_disable_auto) && is_numeric($deposit_min_amount_for_disable_auto) && (float)$deposit_min_amount_for_disable_auto > 0 && (float)$balance >= (float)$deposit_min_amount_for_disable_auto){
														if($obj_con_cron->commit()){
															//$content = base64_encode("SELECT * FROM `report_smses` where DATE_FORMAT(create_date,'%Y-%m-%d') = '".$v['date']."' and DATE_FORMAT(create_time,'%H:%i') = '".$time_explode[0].":".$time_explode[1]."' and type_deposit_withdraw = 'D' and amount <=> CAST('".$balance."' AS DECIMAL(15, 2)) and payment_gateway = '".$payment_gateway."' and is_bot_running = '1'");
															//file_put_contents($cache_filename, $content);
														}
														echo json_encode(['status'=>true,"message" => "Bank for deposit available : ".$scb['bank_number']." Ignore auto deposit amount, ". (float)$balance." >= ".(float)$deposit_min_amount_for_disable_auto]);
													}else{


														//Check transaction
														$sql_check = "SELECT id FROM `transaction` where DATE_FORMAT(date_bank,'%Y-%m-%d %H:%i') = '".$v['date']." ".$time_explode[0].":".$time_explode[1]."' and bank_number like '%".$bank_number."%' and amount = '".$balance."' and type = '1'";

														$con_check = $obj_con_cron->query($sql_check);
														$check = $con_check->num_rows;
														$date_add_once_minute = new DateTime($v['date'].' '.$time_explode[0].":".$time_explode[1]);
														$date_add_once_minute->modify('+1 minutes');
														$date_add_once_minute = $date_add_once_minute->format('Y-m-d H:i');

														$sql_check_add_minute_once = "SELECT id FROM `transaction` where DATE_FORMAT(date_bank,'%Y-%m-%d %H:%i') = '".$date_add_once_minute."' and bank_number like '%".$bank_number."%' and amount = '".$balance."' and type = '1'";

														$con_check_add_minute_once = $obj_con_cron->query($sql_check_add_minute_once);
														$check_add_minute_once = $con_check_add_minute_once->num_rows;

														if($check == 0 && $check_add_minute_once == 0){

															$split_statement_detail = explode("_",strtolower($v['description']));
															$bank_number_like = "";
															$bank_in = [];
															if(count($split_statement_detail) > 1 && array_key_exists(trim($split_statement_detail[0]),$bank_stmt_list)){
																$bank_in = $bank_stmt_list[trim($split_statement_detail[0])];
																if (in_array('05',$bank_stmt_list[trim($split_statement_detail[0])])  || in_array('5',$bank_stmt_list[trim($split_statement_detail[0])])) {
																	$bank_number_like = "%".trim($split_statement_detail[1]);
																}else if(in_array('07',$bank_stmt_list[trim($split_statement_detail[0])])  || in_array('7',$bank_stmt_list[trim($split_statement_detail[0])])) {
																	$bank_number_like = "%".trim($split_statement_detail[1]);
																} else {
																	$bank_number_like = "%".trim($split_statement_detail[1]);
																}
																if(!empty($bank_in) && count($bank_in) > 0 && !empty($bank_number_like)){
																	$sql_acc_check = "SELECT id,amount_deposit_auto,bank,bank_number,bank_name,username,linebot_userid FROM `account` where bank IN ('".implode("','",$bank_in)."') and bank_number like '".$bank_number_like."' and deleted = '0' ORDER BY active_deposit_date DESC";
																	$con_acc_check = $obj_con_cron->query($sql_acc_check);
																	$check_acc = $con_acc_check->num_rows;
																	if(true){
																		$check_add_once = false;
																		while($rs_acc = $con_acc_check->fetch_assoc()) {
																			if(!$check_add_once) {
																				if($check_acc >= 2){
																					if (
																						(in_array('05',$bank_stmt_list[trim($split_statement_detail[0])])  || in_array('5',$bank_stmt_list[trim($split_statement_detail[0])])) &&
																						(
																							strpos(strtolower($payment_gateway),"mr. ") !== false ||
																							strpos(strtolower($payment_gateway),"mr ") !== false ||
																							strpos(strtolower($payment_gateway),"mrs.") !== false ||
																							strpos(strtolower($payment_gateway),"mrs ") !== false ||
																							strpos(strtolower($payment_gateway),"miss. ") !== false ||
																							strpos(strtolower($payment_gateway),"miss ") !== false ||
																							strpos(strtolower($payment_gateway),"master ") !== false ||
																							strpos(strtolower($payment_gateway),"master. ") !== false ||
																							strpos(strtolower($payment_gateway),"นาย ") !== false ||
																							strpos(strtolower($payment_gateway),"นาง ") !== false ||
																							strpos(strtolower($payment_gateway),"น.ส. ") !== false ||
																							strpos(strtolower($payment_gateway),"นางสาว ") !== false
																						)
																					) {
																						$payment_bank_name_ex = explode(" ",$payment_gateway);
																						if(count($payment_bank_name_ex) >= 6){
																							$bank_name_from_payment_like = $payment_bank_name_ex[count($payment_bank_name_ex)-2]." ".$payment_bank_name_ex[count($payment_bank_name_ex)-1];
																							if(strpos(strtolower($rs_acc['bank_name']),trim($bank_name_from_payment_like)) !== false){
																								$check_add_once = true;
																							}
																						}
																					}
																				}else{
																					$check_add_once = true;
																				}
																				if($check_add_once){
																					$sql ="INSERT INTO `transaction` (`id`, `date_bank`, `amount`,`account`,`type`,`bank_number`,`updated_at`)
																								VALUES (NULL, '".$v['date']." ".$v['time']."', '".$balance."', '".$rs_acc['id']."', '1', '".$rs_acc['bank_number']."', current_timestamp())";
																					$check_all = $obj_con_cron->query($sql);
																					if($check_all){
																						$credit_before = $rs_acc['amount_deposit_auto'];
																						$credit_after = !is_null($rs_acc['amount_deposit_auto']) && $rs_acc['amount_deposit_auto'] !== "" ? (float)$rs_acc['amount_deposit_auto'] + $balance : $balance;
																						$check_all = $obj_con_cron->query("UPDATE `account` SET `amount_deposit_auto` = '".$credit_after."' WHERE `account`.`id` = ".$rs_acc['id']);
																						if($check_all){
																							$sql_insert_credit_his ="INSERT INTO `credit_history` (`id`, `process`, `credit_before`,`credit_after`,`type`,`account`,`transaction`,`admin`,`date_bank`,`bank_id`,`bank_name`,`bank_number`,`bank_code`,`username`) VALUES (NULL, '".$balance."', '".$credit_before."', '".$credit_after."', '1', '".$rs_acc['id']."', '1', '0', '".date("Y-m-d H:i:s", strtotime($v['date']." ".$v['time']))."', '".$scb['id']."', '".$scb['account_name']."', '".$scb['bank_number']."', '".$scb['bank_code']."', '".$rs_acc['username']."')";
																							$check_all = $obj_con_cron->query($sql_insert_credit_his);
																							if($check_all){
																								$credit_history_id = $obj_con_cron->insert_id;

																								//Update deposit_withdraw_id on report sms,report
																								$sql_update_report_sms = "UPDATE `report_smses` SET `deposit_withdraw_id` = '".$credit_history_id."' WHERE `report_smses`.`id` = ".$report_sms_id;
																								$check_all = $obj_con_cron->query($sql_update_report_sms);
																								if($check_all){
																									$sql_update_report = "UPDATE `reports` SET `deposit_withdraw_id` = '".$credit_history_id."' WHERE `reports`.`id` = ".$report_id;
																									$check_all = $obj_con_cron->query($sql_update_report);
																									if($check_all){
																										//Insert line notify
																										if($status_create_line_notify && !empty($token_line_notify)){
																											$message = "ยอดฝาก ".number_format($balance,2)." บาท ยูส ".$rs_acc['username']." เวลา ".$v['date']." ".$v['time']." ปรับโดย AUTO";
																											$sql_insert_line_notify ="INSERT INTO `log_line_notify` (`id`, `type`, `message`) VALUES (NULL, '1', '".$message."')";
																											$obj_con_cron->query($sql_insert_line_notify);
																										}

																										if($da_line_send_messages_status['line_send_messages_status']==1){
																											$line_msg = array();
																											$line_msg['web_name'] = $da_line_send_messages_status['web_name'];
																											$line_msg['bank_tf_name'] = $bank_list[$rs_acc['bank']];
																											$line_msg['bank_tf_number'] = $rs_acc['bank_number'];
																											$line_msg['balance'] = number_format($balance,2);
																											$line_msg['bank_time'] = $v['date']." ".$v['time'];
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
																											//$content = base64_encode("SELECT * FROM `report_smses` where DATE_FORMAT(create_date,'%Y-%m-%d') = '".$v['date']."' and DATE_FORMAT(create_time,'%H:%i') = '".$time_explode[0].":".$time_explode[1]."' and type_deposit_withdraw = 'D' and amount <=> CAST('".$balance."' AS DECIMAL(15, 2)) and payment_gateway = '".$payment_gateway."' and is_bot_running = '1'");
																											//file_put_contents($cache_filename, $content);
																										}
																										echo json_encode(['status'=>true,"message"=>$v['date']." ".$v['time']." | ".$balance.' | '.$rs_acc['bank_number']]);
																									}else{
																										if($obj_con_cron){
																											$obj_con_cron->rollback();
																										}else if(!is_null($obj_con_cron)){
																											$obj_con_cron->rollback();
																										}
																									}

																								}else{
																									if($obj_con_cron){
																										$obj_con_cron->rollback();
																									}else if(!is_null($obj_con_cron)){
																										$obj_con_cron->rollback();
																									}
																								}

																							}else{
																								if($obj_con_cron){
																									$obj_con_cron->rollback();
																								}else if(!is_null($obj_con_cron)){
																									$obj_con_cron->rollback();
																								}
																							}
																						}else{
																							if($obj_con_cron){
																								$obj_con_cron->rollback();
																							}else if(!is_null($obj_con_cron)){
																								$obj_con_cron->rollback();
																							}
																						}
																					}else{
																						if($obj_con_cron){
																							$obj_con_cron->rollback();
																						}else if(!is_null($obj_con_cron)){
																							$obj_con_cron->rollback();
																						}
																					}
																				}
																			}

																		}
																	}
																}
															}
														}else{
															if($obj_con_cron->commit()){
																//$content = base64_encode("SELECT * FROM `report_smses` where DATE_FORMAT(create_date,'%Y-%m-%d') = '".$v['date']."' and DATE_FORMAT(create_time,'%H:%i') = '".$time_explode[0].":".$time_explode[1]."' and type_deposit_withdraw = 'D' and amount <=> CAST('".$balance."' AS DECIMAL(15, 2)) and payment_gateway = '".$payment_gateway."' and is_bot_running = '1'");
																//file_put_contents($cache_filename, $content);
															}
														}

													}

												}
											}else{
												if($obj_con_cron){
													$obj_con_cron->rollback();
												}else if(!is_null($obj_con_cron)){
													$obj_con_cron->rollback();
												}
											}
										}else{
											if($obj_con_cron){
												$obj_con_cron->rollback();
											}else if(!is_null($obj_con_cron)){
												$obj_con_cron->rollback();
											}
										}
									}else{
										if($obj_con_cron){
											$obj_con_cron->rollback();
										}else if(!is_null($obj_con_cron)){
											$obj_con_cron->rollback();
										}
									}
								} catch(Exception $e) {
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
				}
			}catch (Exception $ex){
				echo json_encode(['status'=>false,"message" => "Bank Scb ".$scb['bank_number']." Error Exception ".$ex->getMessage()]);
			}
		}

	}
	$chk_duplicate_date_and_hour_minute = [];
	$data_stmt_acc_list_api = [];
}
?>
