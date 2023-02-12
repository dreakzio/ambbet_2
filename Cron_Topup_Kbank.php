<?php
require('config.php');
if(isset($_GET['api_token']) && trim($_GET['api_token']) == API_TOKEN_KEY){
	$sec_rand = rand(3,8);
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

	//Check bank active
	$sql_bank_check = "SELECT * FROM `bank` where status = '1' and (bank_code = '02' or bank_code = '2') and deleted = '0' order by status_withdraw asc";
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

			//ดึงยอด internet banking
			if(isset($rs['api_type']) && $rs['api_type'] == "2"){
				try{
					require('lib/Kbank.php');
					$kbank = $rs;
					$api = new Kbank($kbank['username'],$kbank['password'],$kbank['bank_number']); //$username,$password,$accnum
					$results = $api->getBalanceAndTransactions();
					$trans = array_reverse($results['transactions']);
					$balance = $results['balance'];
					$obj_con_cron->autocommit(true);
					if(!is_null($balance) && is_numeric($balance)){
						$obj_con_cron->query("UPDATE `bank` SET `balance` = '".$balance."' WHERE `bank`.`bank_number` = '".$kbank['bank_number']."'");
					}else{
						echo json_encode(['status'=>false,"message" => "Bank ".$kbank['bank_number']." can not get Balance, Please check params"]);
					}
					echo json_encode(['status'=>true,"message" => "Bank for deposit available : ".$kbank['bank_number']]);
					/*echo json_encode($trans);
					exit();*/

					$after_date_chk = date('Y-m-d',(strtotime ( '+1 day' , strtotime ( date("Y-m-d")) ) ));
					if($chk_is_withdraw){
						$obj_con_cron->autocommit(true);
					}else{
						$obj_con_cron->autocommit(false);
					}

					foreach ($trans as $v) {
						$balance = (float) str_replace(',', '', $v['amount']);
						$payment_gateway = trim($v['payment_gateway']);
						$type_deposit_withdraw = trim($v['type_deposit_withdraw']);
						if($chk_is_withdraw && $v['type_deposit_withdraw'] == "W"){
							//Check report sms

							$sql_report_sms = "SELECT id FROM `report_smses` 
          										where DATE_FORMAT(create_date,'%Y-%m-%d') = '".$v['date']."' 
          										and DATE_FORMAT(create_time,'%H:%i') = '".explode(":",trim($v['time']))[0].":".explode(":",trim($v['time']))[1]."' 
          										and type_deposit_withdraw = 'W' 
          										and type = '".trim($v['type'])."' 
          										and amount <=> CAST('".$balance."' AS DECIMAL(15, 2)) 
          										and payment_gateway like '".explode(" | ",$payment_gateway)[0]."%' 
          										and is_bot_running = '1'";
							$con_check_report_sms = $obj_con_cron->query($sql_report_sms);
							$check_report_sms = $con_check_report_sms->num_rows;


							if($check_report_sms == 0){

								$sql_insert_report_sms = "INSERT INTO `report_smses` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`type_deposit_withdraw`,`type`) VALUES (NULL, '" . $rs['id'] . "','" . $payment_gateway . "','" . $balance . "', current_timestamp(),'1','" . $v['date'] . "','" . $v['time'] . "','W','".$v['type']."')";
								$check_all = $obj_con_cron->query($sql_insert_report_sms);
								$report_sms_id = $obj_con_cron->insert_id;

								$sql_insert_report = "INSERT INTO `reports` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`sms_statement_refer_id`,`type_deposit_withdraw`,`type`) VALUES (NULL, '" . $rs['id'] . "','" . $payment_gateway . "','" . $balance . "', current_timestamp(),'1','" . $v['date'] . "','" . $v['time'] . "','" . $report_sms_id . "','W','".$v['type']."')";
								$check_all = $obj_con_cron->query($sql_insert_report);
								$report_id = $obj_con_cron->insert_id;

								//Update sms_statement_refer_id on report sms
								$sql_update_report_sms = "UPDATE `report_smses` SET `sms_statement_refer_id` = '" . $report_id . "' WHERE `report_smses`.`id` = " . $report_sms_id;
								$check_all = $obj_con_cron->query($sql_update_report_sms);

							}
						}else if(!$chk_is_withdraw){
							if((trim($v['date']) == $current_date_chk || trim($v['date']) == $before_date_chk || trim($v['date']) == $after_date_chk) && !in_array($v,$data_chk)){
								$data_chk[] = $v;
								//Check report sms

								$sql_report_sms = "SELECT id FROM `report_smses` where DATE_FORMAT(create_date,'%Y-%m-%d') = '".$v['date']."' and DATE_FORMAT(create_time,'%H:%i') = '".explode(":",trim($v['time']))[0].":".explode(":",trim($v['time']))[1]."' and type_deposit_withdraw = '".$type_deposit_withdraw."' and type = '".$v['type']."' and amount <=> CAST('".$balance."' AS DECIMAL(15, 2)) and payment_gateway = '".$payment_gateway."' and is_bot_running = '1'";
								$con_check_report_sms = $obj_con_cron->query($sql_report_sms);
								$check_report_sms = $con_check_report_sms->num_rows;

								if($check_report_sms == 0){


									try
									{
										$check_all = true;

										//Insert report sms
										$sql_insert_report_sms = "INSERT INTO `report_smses` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`type_deposit_withdraw`,`type`) VALUES (NULL, '".$rs['id']."','".$payment_gateway."','".$balance."', current_timestamp(),'1','".$v['date']."','".$v['time']."','".$type_deposit_withdraw."','".$v['type']."')";
										$check_all = $obj_con_cron->query($sql_insert_report_sms);
										if($check_all){
											$report_sms_id = $obj_con_cron->insert_id;

											//Insert report
											$sql_insert_report = "INSERT INTO `reports` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`sms_statement_refer_id`,`type_deposit_withdraw`,`type`) VALUES (NULL, '".$rs['id']."','".$payment_gateway."','".$balance."', current_timestamp(),'1','".$v['date']."','".$v['time']."','".$report_sms_id."','".$type_deposit_withdraw."','".$v['type']."')";
											$check_all = $obj_con_cron->query($sql_insert_report);
											if($check_all){
												$report_id = $obj_con_cron->insert_id;

												//Update sms_statement_refer_id on report sms
												$sql_update_report_sms = "UPDATE `report_smses` SET `sms_statement_refer_id` = '".$report_id."' WHERE `report_smses`.`id` = ".$report_sms_id;
												$check_all =  $obj_con_cron->query($sql_update_report_sms);
												if($check_all){
													if($type_deposit_withdraw == 'W'){
														if($obj_con_cron->commit()){

														}
													}else{
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
																	echo json_encode(['status'=>true,"message" => "Bank for deposit available : ".$kbank['bank_number']." Ignore create transaction, credit history => start & end time range for auto ".$rs['start_time_can_not_deposit']." - ".$rs['end_time_can_not_deposit']]);
																}
															}catch (Exception $ex){
																if($obj_con_cron->commit()){

																}
																echo json_encode(['status'=>true,"message" => "Bank for deposit available : ".$kbank['bank_number']." Ignore create transaction, credit history => start & end time range for auto ".$rs['start_time_can_not_deposit']." - ".$rs['end_time_can_not_deposit']. "Error {} ".$ex->getMessage()]);
															}
														}

														if(!$chk_ignore_create_transaction_credit_history){

															if(!is_null($deposit_min_amount_for_disable_auto) && is_numeric($deposit_min_amount_for_disable_auto) && (float)$deposit_min_amount_for_disable_auto > 0 && (float)$balance >= (float)$deposit_min_amount_for_disable_auto){
																if($obj_con_cron->commit()){

																}
																echo json_encode(['status'=>true,"message" => "Bank for deposit available : ".$kbank['bank_number']." Ignore auto deposit amount, ". (float)$balance." >= ".(float)$deposit_min_amount_for_disable_auto]);
															}else{

																$bank_number = explode(" |",$payment_gateway);
																$bank_number = trim($bank_number[1]);
																$bank_number = explode(" ",$bank_number);
																$bank_number = trim($bank_number[0]);
																$is_kbank = false;
																if(strpos($bank_number,"xxx-x")  !== false || strpos($payment_gateway,"LINE BK") !== false){
																	$is_kbank = true;
																	$bank_number = preg_replace('/[^0-9]+/', '', $bank_number);
																}else{
																	$bank_number = preg_replace('/[^0-9]+/', '', $bank_number);
																}

																if(!empty($bank_number)){
																	//Check transaction
																	$sql_check = "SELECT id FROM `transaction` where DATE_FORMAT(date_bank,'%Y-%m-%d %H:%i') = '".$v['date']." ".explode(":",trim($v['time']))[0].":".explode(":",trim($v['time']))[1]."' and bank_number like '%".$bank_number."%' and amount = '".$balance."' and type = '1'";

																	$con_check = $obj_con_cron->query($sql_check);
																	$check = $con_check->num_rows;

																	if($check == 0){

																		if($is_kbank){
																			if(strlen($bank_number) >= 10){
																				$bank_number_like = "bank_number like '%".$bank_number."%'";
																			}else{
																				$bank_number_like = "";
																				for($i = 0 ;$i<=9 ;$i++){
																					if($i == 0){
																						$bank_number_like .= "bank_number like '%".$bank_number.$i."'";
																					}else{
																						$bank_number_like .= " or bank_number like '%".$bank_number.$i."'";
																					}
																				}
																			}
																			$sql_acc_check = "SELECT id,amount_deposit_auto,bank,bank_number,bank_name,username FROM `account` where bank IN ('".implode("','",['02','2'])."') and (".$bank_number_like.") and deleted = '0' ORDER BY active_deposit_date DESC";
																			//$sql_acc_check = "SELECT id,amount_deposit_auto,bank,bank_number,bank_name,username FROM `account` where bank IN ('".implode("','",['02','2'])."') and bank_number like '".$bank_number_like."' and deleted = '0' ORDER BY active_deposit_date DESC";
																		}else{
																			$sql_acc_check = "SELECT id,amount_deposit_auto,bank,bank_number,bank_name,username FROM `account` where bank NOT IN ('".implode("','",['02','2'])."') and bank_number like '%".$bank_number."%' and deleted = '0' ORDER BY active_deposit_date DESC";
																		}
																		$con_acc_check = $obj_con_cron->query($sql_acc_check);
																		$check_acc = $con_acc_check->num_rows;
																		if($check_acc == 1) {
																			$check_add_once = false;
																			while($rs_acc = $con_acc_check->fetch_assoc()) {
																				if(!$check_add_once) {
																					$check_add_once = true;
																					$sql ="INSERT INTO `transaction` (`id`, `date_bank`, `amount`,`account`,`type`,`bank_number`,`updated_at`) VALUES (NULL, '".$v['date']." ".explode(":",trim($v['time']))[0].":".explode(":",trim($v['time']))[1].":00', '".$balance."', '".$rs_acc['id']."', '1', '".$rs_acc['bank_number']."', current_timestamp())";
																					$check_all = $obj_con_cron->query($sql);
																					if($check_all){
																						$credit_before = $rs_acc['amount_deposit_auto'];
																						$credit_after = !is_null($rs_acc['amount_deposit_auto']) && $rs_acc['amount_deposit_auto'] !== "" ? (float)$rs_acc['amount_deposit_auto'] + $balance : $balance;
																						$check_all = $obj_con_cron->query("UPDATE `account` SET `amount_deposit_auto` = '".$credit_after."' WHERE `account`.`id` = ".$rs_acc['id']);
																						if($check_all){
																							$sql_insert_credit_his ="INSERT INTO `credit_history` (`id`, `process`, `credit_before`,`credit_after`,`type`,`account`,`transaction`,`admin`,`date_bank`,`bank_id`,`bank_name`,`bank_number`,`bank_code`,`username`) VALUES (NULL, '".$balance."', '".$credit_before."', '".$credit_after."', '1', '".$rs_acc['id']."', '1', '0', '".date("Y-m-d H:i", strtotime($v['date']." ".$v['time'])).":00', '".$kbank['id']."', '".$kbank['account_name']."', '".$kbank['bank_number']."', '".$kbank['bank_code']."', '".$rs_acc['username']."')";
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
					echo json_encode(['status'=>false,"message" => "Bank Kbank ".$kbank['bank_number']." Error Exception ".$ex->getMessage()]);
				}
			}
			//ดึงยอด app kplus
			else{
				try{
					require('lib/Kplus.php');
					$kbank = $rs;
					$kbank['api_token_1'] = decrypt(base64_decode($kbank['api_token_1']),SECRET_KEY_SALT); //token
					$kbank['api_token_2'] = decrypt(base64_decode($kbank['api_token_2']),SECRET_KEY_SALT); //pin
					$api = new Kplus($kbank['bank_number'],$kbank['api_token_1'],$kbank['api_token_2']); //$accnum,$token,$pin
					if(!array_key_exists($kbank['bank_number'],$data_stmt_acc_list_api)){
						$results = [
							'balance' => $api->getBalance(),
							'transactions' => $api->getTransactions(),
						];
						$data_stmt_acc_list_api[$kbank['bank_number']] = $results;
					}else{
						$results = $data_stmt_acc_list_api[$kbank['bank_number']];
					}
					$trans = array_reverse($results['transactions']);
					$balance = $results['balance'];
					$obj_con_cron->autocommit(true);
					if(!is_null($balance) && is_numeric($balance)){
						$obj_con_cron->query("UPDATE `bank` SET `balance` = '".$balance."' WHERE `bank`.`bank_number` = '".$kbank['bank_number']."'");
					}else{
						echo json_encode(['status'=>false,"message" => "Bank ".$kbank['bank_number']." can not get Balance, Please check params"]);
					}
					echo json_encode(['status'=>true,"message" => "Bank for deposit available : ".$kbank['bank_number']]);

					$after_date_chk = date('Y-m-d',(strtotime ( '+1 day' , strtotime ( date("Y-m-d")) ) ));
					if($chk_is_withdraw){
						$obj_con_cron->autocommit(true);
					}else{
						$obj_con_cron->autocommit(false);
					}

					foreach ($trans as $index => $v) {
						$v['type_deposit_withdraw'] = $v['transactionType'] == "CR" ? "D" : "W";
						$v['date'] =date('Y-m-d',$v['transactionUxDate']);
						$v['time'] =date('H:i:s',$v['transactionUxDate']);
						$v['type'] = $v['channel'];
						$balance = (float) str_replace(',', '', $v['amount']);
						$report_id = $v['rqUid'];
						$type_deposit_withdraw = trim($v['type_deposit_withdraw']);
						$bank_code_in = ["NULL"];
						if($chk_is_withdraw && $v['type_deposit_withdraw'] == "W"){
							//Check report sms

							$sql_report_sms = "SELECT id FROM `report_smses` where report_id = '".$report_id."' and is_bot_running = '1'";
							$con_check_report_sms = $obj_con_cron->query($sql_report_sms);
							$check_report_sms = $con_check_report_sms->num_rows;


							if($check_report_sms == 0){

								$detail = $api->getTransactionDetail($report_id);
								if(!empty($detail) && !is_null($detail) && !isset($detail['error']) && !isset($detail['errors'])) {
									$v= getPaymentGateway($type_deposit_withdraw,$v,$detail);
									$payment_gateway = trim($v['payment_gateway']);

									$sql_report_sms2 = "SELECT id FROM `report_smses` where DATE_FORMAT(create_date,'%Y-%m-%d') = '".$v['date']."' and DATE_FORMAT(create_time,'%H:%i') = '".explode(":",trim($v['time']))[0].":".explode(":",trim($v['time']))[1]."' and type_deposit_withdraw = 'W' and type = '".trim($v['type'])."' and amount <=> CAST('".$balance."' AS DECIMAL(15, 2)) and payment_gateway like '".explode(" | ",$payment_gateway)[0]."%' and is_bot_running = '1'";
									$con_check_report_sms2 = $obj_con_cron->query($sql_report_sms2);
									$check_report_sms2 = $con_check_report_sms2->num_rows;

									if($check_report_sms2 == 0){
										$sql_insert_report_sms = "INSERT INTO `report_smses` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`type_deposit_withdraw`,`type`,`report_id`) VALUES (NULL, '" . $rs['id'] . "','" . $payment_gateway . "','" . $balance . "', current_timestamp(),'1','" . $v['date'] . "','" . $v['time'] . "','W','".$v['type']."','".$report_id."')";
										$check_all = $obj_con_cron->query($sql_insert_report_sms);
										$report_sms_id = $obj_con_cron->insert_id;

										$sql_insert_report = "INSERT INTO `reports` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`sms_statement_refer_id`,`type_deposit_withdraw`,`type`,`report_id`) VALUES (NULL, '" . $rs['id'] . "','" . $payment_gateway . "','" . $balance . "', current_timestamp(),'1','" . $v['date'] . "','" . $v['time'] . "','" . $report_sms_id . "','W','".$v['type']."','".$report_id."')";
										$check_all = $obj_con_cron->query($sql_insert_report);
										$report_id = $obj_con_cron->insert_id;

										//Update sms_statement_refer_id on report sms
										$sql_update_report_sms = "UPDATE `report_smses` SET `sms_statement_refer_id` = '" . $report_id . "' WHERE `report_smses`.`id` = " . $report_sms_id;
										$check_all = $obj_con_cron->query($sql_update_report_sms);
									}
								}
							}
						}else if(!$chk_is_withdraw){
							if((trim($v['date']) == $current_date_chk || trim($v['date']) == $before_date_chk || trim($v['date']) == $after_date_chk) && !in_array($v,$data_chk)){
								$data_chk[] = $v;
								//Check report sms

								$sql_report_sms = "SELECT id FROM `report_smses` where report_id = '".$report_id."' and is_bot_running = '1'";
								$con_check_report_sms = $obj_con_cron->query($sql_report_sms);
								$check_report_sms = $con_check_report_sms->num_rows;


								if($check_report_sms == 0){

									$detail = $api->getTransactionDetail($report_id);
									if(!empty($detail) && !is_null($detail) && !isset($detail['error']) && !isset($detail['errors'])) {
										$v= getPaymentGateway($type_deposit_withdraw,$v,$detail);
										$payment_gateway = trim($v['payment_gateway']);

										$sql_report_sms2 = "SELECT id FROM `report_smses` where DATE_FORMAT(create_date,'%Y-%m-%d') = '".$v['date']."' 
															and DATE_FORMAT(create_time,'%H:%i') = '".explode(":",trim($v['time']))[0].":".explode(":",trim($v['time']))[1]."' 
															and type_deposit_withdraw = '".$type_deposit_withdraw."' 
															and type = '".$v['type']."' 
															and amount <=> CAST('".$balance."' AS DECIMAL(15, 2)) 
															and payment_gateway = '".$payment_gateway."' 
															and is_bot_running = '1'";
										$con_check_report_sms2 = $obj_con_cron->query($sql_report_sms2);
										$check_report_sms2 = $con_check_report_sms2->num_rows;

										if($check_report_sms2 == 0){
											try
											{
												$check_all = true;

												//Insert report sms
												$sql_insert_report_sms = "INSERT INTO `report_smses` (`id`, `config_api_id`, `payment_gateway`
																									 ,`amount`,`created_at`,`is_bot_running`
																									 ,`create_date`,`create_time`,`type_deposit_withdraw`
																									 ,`type`,`report_id`) 
																				VALUES (NULL, '".$rs['id']."','".$payment_gateway."'
																						,'".$balance."', current_timestamp(),'1'
																						,'".$v['date']."','".$v['time']."','".$type_deposit_withdraw."','".$v['type']."','".$report_id."')";
												$check_all = $obj_con_cron->query($sql_insert_report_sms);
												if($check_all){
													$report_sms_id = $obj_con_cron->insert_id;

													//Insert report
													$sql_insert_report = "INSERT INTO `reports` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`sms_statement_refer_id`,`type_deposit_withdraw`,`type`,`report_id`) VALUES (NULL, '".$rs['id']."','".$payment_gateway."','".$balance."', current_timestamp(),'1','".$v['date']."','".$v['time']."','".$report_sms_id."','".$type_deposit_withdraw."','".$v['type']."','".$report_id."')";
													$check_all = $obj_con_cron->query($sql_insert_report);
													if($check_all){
														$report_id = $obj_con_cron->insert_id;

														//Update sms_statement_refer_id on report sms
														$sql_update_report_sms = "UPDATE `report_smses` SET `sms_statement_refer_id` = '".$report_id."' WHERE `report_smses`.`id` = ".$report_sms_id;
														$check_all =  $obj_con_cron->query($sql_update_report_sms);
														if($check_all){
															if($type_deposit_withdraw == 'W'){
																if($obj_con_cron->commit()){

																}
															}else{
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
																			echo json_encode(['status'=>true,"message" => "Bank for deposit available : ".$kbank['bank_number']." Ignore create transaction, credit history => start & end time range for auto ".$rs['start_time_can_not_deposit']." - ".$rs['end_time_can_not_deposit']]);
																		}
																	}catch (Exception $ex){
																		if($obj_con_cron->commit()){

																		}
																		echo json_encode(['status'=>true,"message" => "Bank for deposit available : ".$kbank['bank_number']." Ignore create transaction, credit history => start & end time range for auto ".$rs['start_time_can_not_deposit']." - ".$rs['end_time_can_not_deposit']. "Error {} ".$ex->getMessage()]);
																	}
																	if(!$chk_ignore_create_transaction_credit_history){
																		if(!in_array($rs['id'].$payment_gateway.$v['date'].explode(":",trim($v['time']))[0].":".explode(":",trim($v['time']))[1],$chk_duplicate_date_and_hour_minute)){
																			$chk_duplicate_date_and_hour_minute[] = $rs['id'].$payment_gateway.$v['date'].explode(":",trim($v['time']))[0].":".explode(":",trim($v['time']))[1];
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

																		}
																		echo json_encode(['status'=>true,"message" => "Bank for deposit available : ".$kbank['bank_number']." Ignore auto deposit amount, ". (float)$balance." >= ".(float)$deposit_min_amount_for_disable_auto]);
																	}else{

																		$bank_number = !empty($detail['fromAccountNo']) ? trim($detail['fromAccountNo']) : (!empty($v['fromAccountNo']) ? trim($v['fromAccountNo']) : "");
																		$bank_number_all = !empty($detail['fromAccountNo']) ? trim($detail['fromAccountNo']) : (!empty($v['fromAccountNo']) ? trim($v['fromAccountNo']) : "");
																		$bank_number = preg_replace('/[^0-9]+/', '', $bank_number);

																		if(!empty($bank_number)){
																			//Check transaction
																			$sql_check = "SELECT id FROM `transaction` where DATE_FORMAT(date_bank,'%Y-%m-%d %H:%i') = '".$v['date']." ".explode(":",trim($v['time']))[0].":".explode(":",trim($v['time']))[1]."' and bank_number like '%".$bank_number."%' and amount = '".$balance."' and type = '1'";

																			$con_check = $obj_con_cron->query($sql_check);
																			$check = $con_check->num_rows;

																			if($check == 0){

																				$chk_bank_acc_more = false;
																				if(strlen($bank_number) >= 10){
																					$bank_number_like = "bank_number like '%".$bank_number."%'";
																				}else{
																					$bank_number_like = "";
																					if(strlen($bank_number) >= 3 && strtolower(substr($bank_number_all,-2)) == "-x"){
																						for($i = 0 ;$i<=9 ;$i++){
																							if($i == 0){
																								$bank_number_like .= "bank_number like '%".$bank_number.$i."'";
																							}else{
																								$bank_number_like .= " or bank_number like '%".$bank_number.$i."'";
																							}
																						}
																						$chk_bank_acc_more = true;
																					}else{
																						$chk_bank_acc_more = true;
																						$bank_number_like = "bank_number like '%".$bank_number."%'";
																					}
																				}
																				$sql_acc_check = "SELECT id,amount_deposit_auto,bank,bank_number,bank_name,username FROM `account` where bank IN ('".implode("','",$v['bank_code_list'])."') and (".$bank_number_like.") and deleted = '0' ORDER BY active_deposit_date DESC";
																				$con_acc_check = $obj_con_cron->query($sql_acc_check);
																				$check_acc = $con_acc_check->num_rows;
																				$chk_bank_acc_name_match_cnt = 0;
																				$acc_once_data = null;
																				$acc_list_data = [];
																				$acc_list = [];
																				if($check_acc > 1 && $chk_bank_acc_more){
																					$bank_number_format = strtolower(str_replace("-","",$bank_number_all));
																					$bank_number_split = explode($bank_number,$bank_number_format);
																					while($rs_acc = $con_acc_check->fetch_assoc()) {
																						$acc_list[] = $rs_acc;
																						$acc_bank_number_slit = explode($bank_number,$rs_acc['bank_number']);
																						if(count($bank_number_split) == 2 && count($acc_bank_number_slit) == count($bank_number_split)){
																							if(strlen($bank_number_split[0]) == strlen($acc_bank_number_slit[0]) && strlen($bank_number_split[1]) == strlen($acc_bank_number_slit[1])){
																								$acc_list_data[] = $rs_acc['bank_number'];
																								$acc_once_data = $rs_acc;
																								$chk_bank_acc_name_match_cnt += 1;
																							}
																						}
																					}
																				}

																				$chk_bank_acc_full_name_match_cnt = 0;
																				$bank_name_list = [];
																				if($check_acc > 1 && $chk_bank_acc_more && $chk_bank_acc_name_match_cnt > 1 && !is_null($acc_once_data)){
																					$payment_gateway_chk_split = explode(" | ",$payment_gateway);
																					if(count($payment_gateway_chk_split) >= 4 && count(explode(" ",trim($payment_gateway_chk_split[2]))) == 3){
																						$full_name_chk_text = explode(" ",trim($payment_gateway_chk_split[2]))[1]." ".explode(" ",trim($payment_gateway_chk_split[2]))[2];
																						$full_name_chk_text_2 = explode(" ",trim($payment_gateway_chk_split[2]))[1]." ".explode("  ",trim($payment_gateway_chk_split[2]))[2];
																						$full_name_chk_text_3 = explode(" ",trim($payment_gateway_chk_split[2]))[1]." ".explode("   ",trim($payment_gateway_chk_split[2]))[2];
																						foreach($acc_list as $rs_acc) {
																							$bank_name_list[] = [
																								'bank_name' => $rs_acc['bank_name'],
																								'bank_number' => $rs_acc['bank_number'],
																								'chk' => (strpos($rs_acc['bank_name'],trim($full_name_chk_text))),
																								'chk2' => in_array($rs_acc['bank_number'],$acc_list_data),
																							];
																							if(!empty(trim($full_name_chk_text)) && (strpos($rs_acc['bank_name'],trim($full_name_chk_text)) !== FALSE || strpos($rs_acc['bank_name'],trim($full_name_chk_text_2)) !== FALSE || strpos($rs_acc['bank_name'],trim($full_name_chk_text_3)) !== FALSE) && in_array($rs_acc['bank_number'],$acc_list_data)){
																								$acc_once_data = $rs_acc;
																								$chk_bank_acc_full_name_match_cnt += 1;
																							}
																						}
																						if($chk_bank_acc_full_name_match_cnt == 1){
																							$chk_bank_acc_name_match_cnt = 1;
																						}
																					}
																				}
																				if($check_acc == 1) {
																					$check_add_once = false;
																					while($rs_acc = $con_acc_check->fetch_assoc()) {
																						if(!$check_add_once) {
																							$check_add_once = true;
																							$sql ="INSERT INTO `transaction` (`id`, `date_bank`, `amount`,`account`,`type`,`bank_number`,`updated_at`) VALUES (NULL, '".$v['date']." ".explode(":",trim($v['time']))[0].":".explode(":",trim($v['time']))[1].":00', '".$balance."', '".$rs_acc['id']."', '1', '".$rs_acc['bank_number']."', current_timestamp())";
																							$check_all = $obj_con_cron->query($sql);
																							if($check_all){
																								$credit_before = $rs_acc['amount_deposit_auto'];
																								$credit_after = !is_null($rs_acc['amount_deposit_auto']) && $rs_acc['amount_deposit_auto'] !== "" ? (float)$rs_acc['amount_deposit_auto'] + $balance : $balance;
																								$check_all = $obj_con_cron->query("UPDATE `account` SET `amount_deposit_auto` = '".$credit_after."' WHERE `account`.`id` = ".$rs_acc['id']);
																								if($check_all){
																									$sql_insert_credit_his ="INSERT INTO `credit_history` (`id`, `process`, `credit_before`,`credit_after`,`type`,`account`,`transaction`,`admin`,`date_bank`,`bank_id`,`bank_name`,`bank_number`,`bank_code`,`username`) VALUES (NULL, '".$balance."', '".$credit_before."', '".$credit_after."', '1', '".$rs_acc['id']."', '1', '0', '".date("Y-m-d H:i", strtotime($v['date']." ".$v['time'])).":00', '".$kbank['id']."', '".$kbank['account_name']."', '".$kbank['bank_number']."', '".$kbank['bank_code']."', '".$rs_acc['username']."')";
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
																				}else if($check_acc > 1 && $chk_bank_acc_more && $chk_bank_acc_name_match_cnt == 1 && !is_null($acc_once_data)){
																					$rs_acc = $acc_once_data;
																					$sql ="INSERT INTO `transaction` (`id`, `date_bank`, `amount`,`account`,`type`,`bank_number`,`updated_at`) VALUES (NULL, '".$v['date']." ".explode(":",trim($v['time']))[0].":".explode(":",trim($v['time']))[1].":00', '".$balance."', '".$rs_acc['id']."', '1', '".$rs_acc['bank_number']."', current_timestamp())";
																					$check_all = $obj_con_cron->query($sql);
																					if($check_all){
																						$credit_before = $rs_acc['amount_deposit_auto'];
																						$credit_after = !is_null($rs_acc['amount_deposit_auto']) && $rs_acc['amount_deposit_auto'] !== "" ? (float)$rs_acc['amount_deposit_auto'] + $balance : $balance;
																						$check_all = $obj_con_cron->query("UPDATE `account` SET `amount_deposit_auto` = '".$credit_after."' WHERE `account`.`id` = ".$rs_acc['id']);
																						if($check_all){
																							$sql_insert_credit_his ="INSERT INTO `credit_history` (`id`, `process`, `credit_before`,`credit_after`,`type`,`account`,`transaction`,`admin`,`date_bank`,`bank_id`,`bank_name`,`bank_number`,`bank_code`,`username`) VALUES (NULL, '".$balance."', '".$credit_before."', '".$credit_after."', '1', '".$rs_acc['id']."', '1', '0', '".date("Y-m-d H:i", strtotime($v['date']." ".$v['time'])).":00', '".$kbank['id']."', '".$kbank['account_name']."', '".$kbank['bank_number']."', '".$kbank['bank_code']."', '".$rs_acc['username']."')";
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
						}
					}
				}catch (Exception $ex){
					echo json_encode(['status'=>false,"message" => "Bank Kbank ".$kbank['bank_number']." Error Exception ".$ex->getMessage()]);
				}
			}

		}

	}
	$chk_duplicate_date_and_hour_minute = [];
	$data_stmt_acc_list_api = [];
}
function getBackCodeList($name_bank){
	$bank_txt_to_code = [
		'ธ.กสิกรไทย' => ['02','2'],
		'ธ.กรุงเทพ' => ['01','1'],
		'ธ.กรุงไทย' => ['03','3'],
		'ธ.ทหารไทยธนชาต' => ['04','4','08','8'],
		'ธ.ไทยพาณิชย์' => ['05','5'],
		'ธ.กรุงศรีอยุธยา' => ['06','6'],
		'ธ.ออมสิน' => ['07','7'],
		'ธ. ธ.ก.ส.' => ['09','9'],
		'ธ.ธนชาต' => ['04','4','08','8'],
	];
	if(strpos($name_bank,"กสิกรไทย") !== false){
		return ['02','2'];
	}else if(strpos($name_bank,"กรุงเทพ") !== false){
		return ['01','1'];
	}else if(strpos($name_bank,"กรุงไทย") !== false){
		return ['03','3'];
	}else if(strpos($name_bank,"ธนชาต") !== false || strpos($name_bank,"ทหารไทย") !== false){
		return ['04','4','08','8'];
	}else if(strpos($name_bank,"ไทยพาณิชย์") !== false){
		return ['05','5'];
	}else if(strpos($name_bank,"กรุงศรีอยุธยา") !== false){
		return ['06','6'];
	}else if(strpos($name_bank,"ออมสิน") !== false){
		return ['07','7'];
	}else if(strpos($name_bank,"ธ.ก.ส") !== false){
		return ['09','9'];
	}else{
		return ['NULL'];
	}
}
function getPaymentGateway($type,$transaction,$transaction_detail = null){
	$transaction['bank_code_list'] = ["NULL"];
	if($type == "D"){
		if(!empty($transaction_detail) && !isset($transaction_detail['error'])){
			$transaction['payment_gateway'] =  $transaction['channel'];
			if(!empty($transaction_detail['fromAccountNo'])){
				$transaction['payment_gateway'] .= " | ".$transaction_detail['fromAccountNo'];
			}else if(!empty($transaction['fromAccountNo'])){
				$transaction['payment_gateway'] .= " | ".$transaction['fromAccountNo'];
			}
			if(!empty($transaction_detail['fromAccountName'])){
				$transaction['payment_gateway'] .= " | ".$transaction_detail['fromAccountName'];
			}else if(!empty($transaction['fromAccountName'])){
				$transaction['payment_gateway'] .= " | ".$transaction['fromAccountName'];
			}
			if(!empty($transaction_detail['fromBankName'])){
				$transaction['bank_code_list'] = getBackCodeList($transaction_detail['fromBankName']);
				$transaction['payment_gateway'] .= " | ".$transaction_detail['fromBankName'];
			}
		}else{
			$transaction['payment_gateway'] =  $transaction['channel'];
			if(!empty($transaction['fromAccountNo'])){
				$transaction['payment_gateway'] .= " | ".$transaction['fromAccountNo'];
			}
			if(!empty($transaction['fromAccountName'])){
				$transaction['payment_gateway'] .= " | ".$transaction['fromAccountName'];
			}
		}
	}else{
		if(!empty($transaction_detail) && !isset($transaction_detail['error'])){
			$transaction['payment_gateway'] =  $transaction['channel'];
			if(!empty($transaction_detail['toAccountNo'])){
				$transaction['payment_gateway'] .= " | ".$transaction_detail['toAccountNo'];
			}else if(!empty($transaction['toAccountNo'])){
				$transaction['payment_gateway'] .= " | ".$transaction['toAccountNo'];
			}
			if(!empty($transaction_detail['toAccountName'])){
				$transaction['payment_gateway'] .= " | ".$transaction_detail['toAccountName'];
			}else if(!empty($transaction['toAccountName'])){
				$transaction['payment_gateway'] .= " | ".$transaction['toAccountName'];
			}
			if(!empty($transaction_detail['toBankName'])){
				$transaction['bank_code_list'] = getBackCodeList($transaction_detail['toBankName']);
				$transaction['payment_gateway'] .= " | ".$transaction_detail['toBankName'];
			}
		}else{
			$transaction['payment_gateway'] =  $transaction['channel'];
			if(!empty($transaction['toAccountNo'])){
				$transaction['payment_gateway'] .= " | ".$transaction['toAccountNo'];
			}
			if(!empty($transaction['toAccountName'])){
				$transaction['payment_gateway'] .= " | ".$transaction['toAccountName'];
			}
		}
	}
	return $transaction;
}
?>
