<?php
require('config.php');
if(isset($_GET['api_token']) && trim($_GET['api_token']) == API_TOKEN_KEY){
	$sec_rand = rand(1,5);
	sleep($sec_rand);
	error_reporting(0);
	date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
	$current_date = date("d/m/Y");
	$current_date_chk = date("Y-m-d");
	$before_date = date('d/m/Y',(strtotime ( '-1 day' , strtotime ( date("Y-m-d")) ) ));
	$before_date_chk = date('Y-m-d',(strtotime ( '-1 day' , strtotime ( date("Y-m-d")) ) ));
	ob_start('ob_gzhandler');
	require('conn_cron.php');

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

	$sql_bank_check = "SELECT * FROM `bank` where  status = '1' and  (bank_code = '06' or bank_code = '6') and deleted = '0' order by api_type,status_withdraw asc";
	//Check bank active
	$con_bank_check = $obj_con_cron->query($sql_bank_check);
	$chk_can_run_cron = false;
	$chk_is_withdraw = false;
	$data_stmt_acc_list_api = [];
	$chk_bank_duplicate = [];
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

			//ดึงยอด internet banking
			if(isset($rs['api_type']) && $rs['api_type'] == "2"){
				try{
					require('lib/Krungsri.php');
					$krungsri = $rs;
					$api = new Krungsri($krungsri['username'],$krungsri['password'],$krungsri['bank_number']);
					$api_data = $api->process();
					$obj_con_cron->autocommit(true);
					if(!empty($api_data) && isset($api_data['balance']) && is_numeric($api_data['balance'])){
						$obj_con_cron->query("UPDATE `bank` SET `balance` = '".$api_data['balance']."' WHERE `bank`.`bank_number` = '".$krungsri['bank_number']."'");
					}else{
						echo json_encode(['status'=>false,"message" => "Bank ".$krungsri['bank_number']." can not get Balance, Please check token"]);
					}
					if($chk_is_withdraw){
						$obj_con_cron->autocommit(true);
						echo json_encode(['status'=>true,"message" => "Bank for withdraw available : ".$krungsri['bank_number']]);
						$trans = isset($api_data['transactions']) && !empty($api_data['transactions']) ? $api_data['transactions'] : [];
						$after_date_chk = date('Y-m-d',(strtotime ( '+1 day' , strtotime ( date("Y-m-d")) ) ));
						foreach ($trans as $v) {
							$balance = (float) str_replace(',', '', $v['amount']);
							if((trim($v['date']) == $current_date_chk || trim($v['date']) == $before_date_chk || trim($v['date']) == $after_date_chk) && $v['type'] == "W"){
								$time_explode = explode(":",trim($v['time']));

								//Check report sms
								$payment_gateway = $v['fromAccount'];
								$info = $v['info'];
								$info_balance = $v['balance'];

								$bank_acc = preg_replace('/[^0-9]+/', '', $payment_gateway);
								$bank_number_like = "%".$payment_gateway."%";
								if(!empty($bank_number_like)){
									$sql_report_sms = "SELECT * FROM `report_smses` where DATE_FORMAT(create_date,'%Y-%m-%d') = '".$v['date']."' and DATE_FORMAT(create_time,'%H:%i') = '".$time_explode[0].":".$time_explode[1]."' and type_deposit_withdraw = 'W' and amount <=> CAST('".$balance."' AS DECIMAL(15, 2)) and payment_gateway like '".$bank_number_like."' and is_bot_running = '1' and type = '".$info."'";
									$con_check_report_sms = $obj_con_cron->query($sql_report_sms);
									$check_report_sms = $con_check_report_sms->num_rows;
									if($check_report_sms == 0) {

										//Insert report sms
										$sql_insert_report_sms = "INSERT INTO `report_smses` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`type_deposit_withdraw`,`type`) VALUES (NULL, '" . $rs['id'] . "','" . $payment_gateway . "','" . $balance . "', current_timestamp(),'1','" . $v['date'] . "','" . $v['time'] . "','W','".$info."')";
										$obj_con_cron->query($sql_insert_report_sms);
										$report_sms_id = $obj_con_cron->insert_id;

										//Insert report
										$sql_insert_report = "INSERT INTO `reports` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`sms_statement_refer_id`,`type_deposit_withdraw`,`type`) VALUES (NULL, '" . $rs['id'] . "','" . $payment_gateway . "','" . $balance . "', current_timestamp(),'1','" . $v['date'] . "','" . $v['time'] . "','" . $report_sms_id . "','W','".$info."')";
										$obj_con_cron->query($sql_insert_report);
										$report_id = $obj_con_cron->insert_id;

										//Update sms_statement_refer_id on report sms
										$sql_update_report_sms = "UPDATE `report_smses` SET `sms_statement_refer_id` = '" . $report_id . "' WHERE `report_smses`.`id` = " . $report_sms_id;
										$obj_con_cron->query($sql_update_report_sms);
									}
								}

							}
						}
						//exit();
					}else{

						if(!in_array($krungsri['bank_number'],$chk_bank_duplicate)){
							$chk_bank_duplicate[] = $krungsri['bank_number'];
							$obj_con_cron->autocommit(false);
							echo json_encode(['status'=>true,"message" => "Bank for deposit available : ".$krungsri['bank_number']]);
							$trans = isset($api_data['transactions']) && !empty($api_data['transactions']) ? $api_data['transactions'] : [];
							$after_date_chk = date('Y-m-d',(strtotime ( '+1 day' , strtotime ( date("Y-m-d")) ) ));
							foreach ($trans as $v) {
								$balance = (float) str_replace(',', '', $v['amount']);
								if((trim($v['date']) == $current_date_chk || trim($v['date']) == $before_date_chk || trim($v['date']) == $after_date_chk) && !in_array($v,$data_chk)){
									$data_chk[] = $v;
									$time_explode = explode(":",trim($v['time']));

									//Check report sms
									$payment_gateway = $v['fromAccount'];
									$type = $v['type'];
									$info = $v['info'];
									$info_balance = $v['balance'];

									$bank_acc = preg_replace('/[^0-9]+/', '', $payment_gateway);
									$bank_number_like = "%".$payment_gateway."%";

									if(true){
										$sql_report_sms = "SELECT * FROM `report_smses` where DATE_FORMAT(create_date,'%Y-%m-%d') = '".$v['date']."' and DATE_FORMAT(create_time,'%H:%i') = '".$time_explode[0].":".$time_explode[1]."' and type_deposit_withdraw = '".$type."' and amount <=> CAST('".$balance."' AS DECIMAL(15, 2)) and payment_gateway like '".$bank_number_like."' and is_bot_running = '1' and type = '".$info."'";
										$con_check_report_sms = $obj_con_cron->query($sql_report_sms);
										$check_report_sms = $con_check_report_sms->num_rows;

										if($check_report_sms == 0){

											//Insert report sms
											$sql_insert_report_sms = "INSERT INTO `report_smses` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`type_deposit_withdraw`,`type`) VALUES (NULL, '".$rs['id']."','".$payment_gateway."','".$balance."', current_timestamp(),'1','".$v['date']."','".$v['time']."','".$type."','".$info."')";
											$obj_con_cron->query($sql_insert_report_sms);
											$report_sms_id = $obj_con_cron->insert_id;

											//Insert report
											$sql_insert_report = "INSERT INTO `reports` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`sms_statement_refer_id`,`type_deposit_withdraw`,`type`) VALUES (NULL, '".$rs['id']."','".$payment_gateway."','".$balance."', current_timestamp(),'1','".$v['date']."','".$v['time']."','".$report_sms_id."','".$type."','".$info."')";
											$obj_con_cron->query($sql_insert_report);
											$report_id = $obj_con_cron->insert_id;

											//Update sms_statement_refer_id on report sms
											$sql_update_report_sms = "UPDATE `report_smses` SET `sms_statement_refer_id` = '".$report_id."' WHERE `report_smses`.`id` = ".$report_sms_id;
											$check_all = $obj_con_cron->query($sql_update_report_sms);
											if($check_all) {
												if ($type == 'W') {
													if ($obj_con_cron->commit()) {

													}
												} else {
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
															$time_stmt_chk = new DateTime($v['date']." ".$v['time']);
															if(
																$time_stmt_chk->getTimestamp() >= $start_time_can_not_deposit_stmt->getTimestamp() &&
																$time_stmt_chk->getTimestamp() <= $end_time_can_not_deposit_stmt->getTimestamp()
															){
																if($obj_con_cron->commit()){

																}
																$chk_ignore_create_transaction_credit_history = true;
																echo json_encode(['status'=>true,"message" => "Bank for deposit available : ".$krungsri['bank_number']." Ignore create transaction, credit history => start & end time range for auto ".$rs['start_time_can_not_deposit']." - ".$rs['end_time_can_not_deposit']]);
															}
														}catch (Exception $ex){
															if($obj_con_cron->commit()){

															}
															echo json_encode(['status'=>true,"message" => "Bank for deposit available : ".$krungsri['bank_number']." Ignore create transaction, credit history => start & end time range for auto ".$rs['start_time_can_not_deposit']." - ".$rs['end_time_can_not_deposit']. "Error {} ".$ex->getMessage()]);
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

													if(!$chk_ignore_create_transaction_credit_history && $type == "D"){

														if(!is_null($deposit_min_amount_for_disable_auto) && is_numeric($deposit_min_amount_for_disable_auto) && (float)$deposit_min_amount_for_disable_auto > 0 && (float)$balance >= (float)$deposit_min_amount_for_disable_auto){
															if($obj_con_cron->commit()){

															}
															echo json_encode(['status'=>true,"message" => "Bank for deposit available : ".$krungsri['bank_number']." Ignore auto deposit amount, ". (float)$balance." >= ".(float)$deposit_min_amount_for_disable_auto]);
														}else{

															$bank_acc = preg_replace('/[^0-9]+/', '', $payment_gateway);
															$bank_number_like = "";
															if(strlen($bank_acc) >= 9) {
																$bank_number_like = "%" . substr($bank_acc, -7);
															}

															if(!empty($bank_number_like)){
																//Check transaction
																$sql_check = "SELECT * FROM `transaction` where DATE_FORMAT(date_bank,'%Y-%m-%d %H:%i') = '".date('Y-m-d H:i',strtotime($v['date']." ".$v['time']))."' and bank_number like '".$bank_number_like."' and amount = '".$balance."' and type = '1'";

																$con_check = $obj_con_cron->query($sql_check);
																$check = $con_check->num_rows;
																if($check == 0){
																	$sql_acc_check = "SELECT id,amount_deposit_auto,bank,bank_number,bank_name,username FROM `account` where bank_number like '".$bank_number_like."' and deleted = '0' ORDER BY active_deposit_date DESC";
																	$con_acc_check = $obj_con_cron->query($sql_acc_check);
																	$check_acc = $con_acc_check->num_rows;

																	if($check_acc == 1){
																		$check_add_once = false;
																		while($rs_acc = $con_acc_check->fetch_assoc()) {
																			if(!$check_add_once) {
																				$check_add_once = true;
																				$sql ="INSERT INTO `transaction` (`id`, `date_bank`, `amount`,`account`,`type`,`bank_number`,`updated_at`) VALUES (NULL, '".date('Y-m-d H:i:s',strtotime($v['date']." ".$v['time']))."', '".$balance."', '".$rs_acc['id']."', '1', '".$rs_acc['bank_number']."', current_timestamp())";
																				$check_all = $obj_con_cron->query($sql);
																				if($check_all){
																					$credit_before = $rs_acc['amount_deposit_auto'];
																					$credit_after = !is_null($rs_acc['amount_deposit_auto']) && $rs_acc['amount_deposit_auto'] !== "" ? (float)$rs_acc['amount_deposit_auto'] + $balance : $balance;
																					$credit_after  = number_format((float)$credit_after, 2, '.', '');
																					$check_all = $obj_con_cron->query("UPDATE `account` SET `amount_deposit_auto` = '".$credit_after."' WHERE `account`.`id` = ".$rs_acc['id']);
																					if($check_all){
																						$sql_insert_credit_his ="INSERT INTO `credit_history` (`id`, `process`, `credit_before`,`credit_after`,`type`,`account`,`transaction`,`admin`,`date_bank`,`bank_id`,`bank_name`,`bank_number`,`bank_code`,`username`) VALUES (NULL, '".$balance."', '".$credit_before."', '".$credit_after."', '1', '".$rs_acc['id']."', '1', '0','".date('Y-m-d H:i:s',strtotime($v['date']." ".$v['time']))."', '".$krungsri['id']."', '".$krungsri['account_name']."', '".$krungsri['bank_number']."', '".$krungsri['bank_code']."', '".$rs_acc['username']."')";
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
																									if($obj_con_cron->commit()){

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
																	}else{
																		if($obj_con_cron->commit()){

																		}
																	}
																}else{
																	if($obj_con_cron->commit()){

																	}
																}
															}else{
																if($obj_con_cron->commit()){

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
						}
					}
				}catch (Exception $ex){
					echo json_encode(['status'=>false,"message" => "Bank Krungsri ".$krungsri['bank_number']." Error Exception ".$ex->getMessage()]);
				}
			}
			//ดึงยอด app KMA
			else {
				try {
					require('lib/kma/KMA.php');
					$krungsri = $rs;
					$krungsri['api_token_1'] = decrypt(base64_decode($krungsri['api_token_1']), SECRET_KEY_SALT); //token
					$krungsri['api_token_2'] = decrypt(base64_decode($krungsri['api_token_2']), SECRET_KEY_SALT); //pin
					$api = new KMA($krungsri['bank_number'], $krungsri['api_token_1'], $krungsri['api_token_2']); //$accnum,$token,$pin
					$api_data = $api->balanceAndTransactions();
					$obj_con_cron->autocommit(true);
					if(!empty($api_data) && isset($api_data['balance']) && is_numeric($api_data['balance'])){
						$obj_con_cron->query("UPDATE `bank` SET `balance` = '".$api_data['balance']."' WHERE `bank`.`bank_number` = '".$krungsri['bank_number']."'");
					}else{
						echo json_encode(['status'=>false,"message" => "Bank ".$krungsri['bank_number']." can not get Balance, Please check token"]);
					}
					if($chk_is_withdraw){
						$obj_con_cron->autocommit(true);
						echo json_encode(['status'=>true,"message" => "Bank for withdraw available : ".$krungsri['bank_number']]);
						$trans = isset($api_data['transactions']) && !empty($api_data['transactions']) ? $api_data['transactions'] : [];
						$after_date_chk = date('Y-m-d',(strtotime ( '+1 day' , strtotime ( date("Y-m-d")) ) ));
						foreach ($trans as $v) {
							$v['amount'] = $v['TranAmount'];
							$v['bank_code_in'] = !empty($v['ToBankCode']) && array_key_exists($v['ToBankCode'],getBankCodeForKrungsri()) ? getBankCodeForKrungsri()[$v['ToBankCode']] : null;
							$TranDateTime = explode("T",$v['TranDateTime']);
							$v['date'] = trim($TranDateTime[0]);
							$TranTimeExplode = explode(":",trim($TranDateTime[1]));
							$v['time'] = $TranTimeExplode[0].":".$TranTimeExplode[1].":00";
							$v['type'] = $v['AmountType'] == "CR" ? "D" : "W";
							$v['fromAccount'] = $v['Mnemonic']." ".$v['TranRef'];
							$v['balance'] = (float) str_replace(',', '', $v['AvialBal']);
							$v['info'] = $v['TranChannel'];
							$balance = (float) str_replace(',', '', $v['amount']);
							if((trim($v['date']) == $current_date_chk || trim($v['date']) == $before_date_chk || trim($v['date']) == $after_date_chk) && $v['type'] == "W"){
								$time_explode = explode(":",trim($v['time']));

								//Check report sms
								$payment_gateway = $v['fromAccount'];

								$bank_acc = preg_replace('/[^0-9]+/', '', $payment_gateway);
								$bank_number_like = "%".$payment_gateway."%";

								if(!empty($bank_number_like)){
									$info = $v['info'];
									$info_balance = $v['balance'];
									$sql_report_sms = "SELECT * FROM `report_smses` where DATE_FORMAT(create_date,'%Y-%m-%d') = '".$v['date']."' and DATE_FORMAT(create_time,'%H:%i') = '".$time_explode[0].":".$time_explode[1]."' and type_deposit_withdraw = 'W' and amount <=> CAST('".$balance."' AS DECIMAL(15, 2)) and payment_gateway like '".$bank_number_like."' and is_bot_running = '1' and type = '".$info."'";
									$con_check_report_sms = $obj_con_cron->query($sql_report_sms);
									$check_report_sms = $con_check_report_sms->num_rows;
									if($check_report_sms == 0) {

										//Insert report sms
										$sql_insert_report_sms = "INSERT INTO `report_smses` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`type_deposit_withdraw`,`type`) VALUES (NULL, '" . $rs['id'] . "','" . $payment_gateway . "','" . $balance . "', current_timestamp(),'1','" . $v['date'] . "','" . $v['time'] . "','W','".$info."')";
										$obj_con_cron->query($sql_insert_report_sms);
										$report_sms_id = $obj_con_cron->insert_id;

										//Insert report
										$sql_insert_report = "INSERT INTO `reports` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`sms_statement_refer_id`,`type_deposit_withdraw`,`type`) VALUES (NULL, '" . $rs['id'] . "','" . $payment_gateway . "','" . $balance . "', current_timestamp(),'1','" . $v['date'] . "','" . $v['time'] . "','" . $report_sms_id . "','W','".$info."')";
										$obj_con_cron->query($sql_insert_report);
										$report_id = $obj_con_cron->insert_id;

										//Update sms_statement_refer_id on report sms
										$sql_update_report_sms = "UPDATE `report_smses` SET `sms_statement_refer_id` = '" . $report_id . "' WHERE `report_smses`.`id` = " . $report_sms_id;
										$obj_con_cron->query($sql_update_report_sms);
									}
								}

							}
						}
						//exit();
					}else{

						if(!in_array($krungsri['bank_number'],$chk_bank_duplicate)) {
							$chk_bank_duplicate[] = $krungsri['bank_number'];
							$obj_con_cron->autocommit(false);
							echo json_encode(['status'=>true,"message" => "Bank for deposit available : ".$krungsri['bank_number']]);
							$trans = isset($api_data['transactions']) && !empty($api_data['transactions']) ? $api_data['transactions'] : [];
							$after_date_chk = date('Y-m-d',(strtotime ( '+1 day' , strtotime ( date("Y-m-d")) ) ));
							foreach ($trans as $v) {
								$v['amount'] = $v['TranAmount'];
								$v['bank_code_in'] = !empty($v['ToBankCode']) && array_key_exists($v['ToBankCode'],getBankCodeForKrungsri()) ? getBankCodeForKrungsri()[$v['ToBankCode']] : null;
								$TranDateTime = explode("T",$v['TranDateTime']);
								$v['date'] = trim($TranDateTime[0]);
								$TranTimeExplode = explode(":",trim($TranDateTime[1]));
								$v['time'] = $TranTimeExplode[0].":".$TranTimeExplode[1].":00";
								$v['type'] = $v['AmountType'] == "CR" ? "D" : "W";
								$v['fromAccount'] = $v['Mnemonic']." ".$v['TranRef'];
								$v['to_bank_acc'] = trim($v['TranRef']);
								$v['balance'] = (float) str_replace(',', '', $v['AvialBal']);
								$v['info'] = $v['TranChannel'];
								$balance = (float) str_replace(',', '', $v['amount']);
								if((trim($v['date']) == $current_date_chk || trim($v['date']) == $before_date_chk || trim($v['date']) == $after_date_chk) && !in_array($v,$data_chk)){
									$data_chk[] = $v;
									$time_explode = explode(":",trim($v['time']));

									//Check report sms
									$payment_gateway = $v['fromAccount'];

									$bank_acc = preg_replace('/[^0-9]+/', '', $payment_gateway);
									$bank_number_like = "%".$payment_gateway."%";
									if(true){
										$type = $v['type'];
										$info = $v['info'];
										$info_balance = $v['balance'];
										$sql_report_sms = "SELECT * FROM `report_smses` where DATE_FORMAT(create_date,'%Y-%m-%d') = '".$v['date']."' and DATE_FORMAT(create_time,'%H:%i') = '".$time_explode[0].":".$time_explode[1]."' and type_deposit_withdraw = '".$type."' and amount <=> CAST('".$balance."' AS DECIMAL(15, 2)) and payment_gateway like '".$bank_number_like."' and is_bot_running = '1' and type = '".$info."'";
										$con_check_report_sms = $obj_con_cron->query($sql_report_sms);
										$check_report_sms = $con_check_report_sms->num_rows;

										if($check_report_sms == 0){

											//Insert report sms
											$sql_insert_report_sms = "INSERT INTO `report_smses` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`type_deposit_withdraw`,`type`) VALUES (NULL, '".$rs['id']."','".$payment_gateway."','".$balance."', current_timestamp(),'1','".$v['date']."','".$v['time']."','".$type."','".$info."')";
											$obj_con_cron->query($sql_insert_report_sms);
											$report_sms_id = $obj_con_cron->insert_id;

											//Insert report
											$sql_insert_report = "INSERT INTO `reports` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`sms_statement_refer_id`,`type_deposit_withdraw`,`type`) VALUES (NULL, '".$rs['id']."','".$payment_gateway."','".$balance."', current_timestamp(),'1','".$v['date']."','".$v['time']."','".$report_sms_id."','".$type."','".$info."')";
											$obj_con_cron->query($sql_insert_report);
											$report_id = $obj_con_cron->insert_id;

											//Update sms_statement_refer_id on report sms
											$sql_update_report_sms = "UPDATE `report_smses` SET `sms_statement_refer_id` = '".$report_id."' WHERE `report_smses`.`id` = ".$report_sms_id;
											$check_all = $obj_con_cron->query($sql_update_report_sms);
											if($check_all) {
												if ($type == 'W') {
													if ($obj_con_cron->commit()) {

													}
												} else {
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
															$time_stmt_chk = new DateTime($v['date']." ".$v['time']);
															if(
																$time_stmt_chk->getTimestamp() >= $start_time_can_not_deposit_stmt->getTimestamp() &&
																$time_stmt_chk->getTimestamp() <= $end_time_can_not_deposit_stmt->getTimestamp()
															){
																if($obj_con_cron->commit()){

																}
																$chk_ignore_create_transaction_credit_history = true;
																echo json_encode(['status'=>true,"message" => "Bank for deposit available : ".$krungsri['bank_number']." Ignore create transaction, credit history => start & end time range for auto ".$rs['start_time_can_not_deposit']." - ".$rs['end_time_can_not_deposit']]);
															}
														}catch (Exception $ex){
															if($obj_con_cron->commit()){

															}
															echo json_encode(['status'=>true,"message" => "Bank for deposit available : ".$krungsri['bank_number']." Ignore create transaction, credit history => start & end time range for auto ".$rs['start_time_can_not_deposit']." - ".$rs['end_time_can_not_deposit']. "Error {} ".$ex->getMessage()]);
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

													if(!$chk_ignore_create_transaction_credit_history && $type == "D"){

														if(!is_null($deposit_min_amount_for_disable_auto) && is_numeric($deposit_min_amount_for_disable_auto) && (float)$deposit_min_amount_for_disable_auto > 0 && (float)$balance >= (float)$deposit_min_amount_for_disable_auto){
															if($obj_con_cron->commit()){

															}
															echo json_encode(['status'=>true,"message" => "Bank for deposit available : ".$krungsri['bank_number']." Ignore auto deposit amount, ". (float)$balance." >= ".(float)$deposit_min_amount_for_disable_auto]);
														}else{

															$bank_acc = preg_replace('/[^0-9]+/', '', $payment_gateway);
															$bank_number_like = "";
															if(strlen($bank_acc) >= 9) {
																$bank_number_like = "%" . substr($bank_acc, -7);
															}

															if(!empty($bank_number_like)){
																//Check transaction
																$sql_check = "SELECT * FROM `transaction` where DATE_FORMAT(date_bank,'%Y-%m-%d %H:%i') = '".date('Y-m-d H:i',strtotime($v['date']." ".$v['time']))."' and bank_number like '".$bank_number_like."' and amount = '".$balance."' and type = '1'";

																$con_check = $obj_con_cron->query($sql_check);
																$check = $con_check->num_rows;
																if($check == 0){
																	if(!is_null($v['bank_code_in'])){
																		if(!empty($v['to_bank_acc'])){
																			$sql_acc_check = "SELECT id,amount_deposit_auto,bank,bank_number,bank_name,username FROM `account` where bank_number = '".$v['to_bank_acc']."' and bank in ('".implode("','",$v['bank_code_in'])."') and deleted = '0' ORDER BY active_deposit_date DESC";
																		}else{
																			$sql_acc_check = "SELECT id,amount_deposit_auto,bank,bank_number,bank_name,username FROM `account` where bank_number like '".$bank_number_like."' and bank in ('".implode("','",$v['bank_code_in'])."') and deleted = '0' ORDER BY active_deposit_date DESC";
																		}
																	}else{
																		if(!empty($v['to_bank_acc'])){
																			$sql_acc_check = "SELECT id,amount_deposit_auto,bank,bank_number,bank_name,username FROM `account` where bank_number = '".$v['to_bank_acc']."' and deleted = '0' ORDER BY active_deposit_date DESC";
																		}else{
																			$sql_acc_check = "SELECT id,amount_deposit_auto,bank,bank_number,bank_name,username FROM `account` where bank_number like '".$bank_number_like."' and deleted = '0' ORDER BY active_deposit_date DESC";
																		}
																	}
																	$con_acc_check = $obj_con_cron->query($sql_acc_check);
																	$check_acc = $con_acc_check->num_rows;

																	if($check_acc == 1){
																		$check_add_once = false;
																		while($rs_acc = $con_acc_check->fetch_assoc()) {
																			if(!$check_add_once) {
																				$check_add_once = true;
																				$sql ="INSERT INTO `transaction` (`id`, `date_bank`, `amount`,`account`,`type`,`bank_number`,`updated_at`) VALUES (NULL, '".date('Y-m-d H:i:s',strtotime($v['date']." ".$v['time']))."', '".$balance."', '".$rs_acc['id']."', '1', '".$rs_acc['bank_number']."', current_timestamp())";
																				$check_all = $obj_con_cron->query($sql);
																				if($check_all){
																					$credit_before = $rs_acc['amount_deposit_auto'];
																					$credit_after = !is_null($rs_acc['amount_deposit_auto']) && $rs_acc['amount_deposit_auto'] !== "" ? (float)$rs_acc['amount_deposit_auto'] + $balance : $balance;
																					$credit_after  = number_format((float)$credit_after, 2, '.', '');
																					$check_all = $obj_con_cron->query("UPDATE `account` SET `amount_deposit_auto` = '".$credit_after."' WHERE `account`.`id` = ".$rs_acc['id']);
																					if($check_all){
																						$sql_insert_credit_his ="INSERT INTO `credit_history` (`id`, `process`, `credit_before`,`credit_after`,`type`,`account`,`transaction`,`admin`,`date_bank`,`bank_id`,`bank_name`,`bank_number`,`bank_code`,`username`) VALUES (NULL, '".$balance."', '".$credit_before."', '".$credit_after."', '1', '".$rs_acc['id']."', '1', '0','".date('Y-m-d H:i:s',strtotime($v['date']." ".$v['time']))."', '".$krungsri['id']."', '".$krungsri['account_name']."', '".$krungsri['bank_number']."', '".$krungsri['bank_code']."', '".$rs_acc['username']."')";
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
																									if($obj_con_cron->commit()){

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
																	}else{
																		if($obj_con_cron->commit()){

																		}
																	}
																}else{
																	if($obj_con_cron->commit()){

																	}
																}
															}else{
																if($obj_con_cron->commit()){

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
						}
					}
				}catch (Exception $ex){
					echo json_encode(['status'=>false,"message" => "Bank Krungsri ".$krungsri['bank_number']." Error Exception ".$ex->getMessage()]);
				}
			}

		}

	}
	$chk_duplicate_date_and_hour_minute = [];
}
function getBankCodeForKrungsri(){
	return array(
		"004" => ["02","2"], 		//KBANK
		"002"  => ["01","1"], 		//BBL
		"006"  => ["03","3"],		//KTB
		"025"  => ["06","6"],		//KRUNGSRI
		"011"  => ["08","8","04","4"],		//TMBTHANACHART
		"065"  => ["08","8","04","4"],		//TMBTHANACHART
		"014"  => ["05","5"],		//SCB
		"030"  => ["07","7"],		//GSB
		"034"  => ["09","9"]		//BAAC
	);
}
?>
