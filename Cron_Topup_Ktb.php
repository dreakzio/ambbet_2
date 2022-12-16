<?php
require('config.php');

if(isset($_GET['api_token']) && trim($_GET['api_token']) == API_TOKEN_KEY){
	$sec_rand = rand(3,8);
	sleep($sec_rand);
	error_reporting(0);
	date_default_timezone_set("Asia/Bangkok"); //set เขตเวลาครับ
	$current_date = date("Y/m/d");
	$current_date_chk = date("Y-m-d");
	$before_date = date('Y/m/d',(strtotime ( '-1 day' , strtotime ( date("Y-m-d")) ) ));
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
	//$sql_bank_check = "SELECT * FROM `bank` where status = '1' and deleted = '0' and bank_number = '".$scb['accnum']."'";
	//$sql_bank_check = "SELECT * FROM `bank` where status = '1' and (bank_code = '03' or bank_code = '3') and deleted = '0'";
	$sql_bank_check = "SELECT * FROM `bank` where (bank_code = '03' or bank_code = '3') and deleted = '0'";
	$con_bank_check = $obj_con_cron->query($sql_bank_check);
	$chk_can_run_cron = false;
	$chk_is_withdraw = false;
	$chk_duplicate_date_and_hour_minute = [];
	while($rs =$con_bank_check->fetch_assoc() ){

		if(isset($_GET['force_login']) && $_GET['force_login'] == "Y" && !empty(trim($rs['api_token_3']))){
			$obj_con_cron->query("UPDATE `bank` SET `chk_cron_login` = '1' WHERE `bank`.`bank_number` = '".$rs['bank_number']."'");
			loginUserIdentify(trim(decrypt(base64_decode($rs['api_token_3']),SECRET_KEY_SALT)),trim($rs['bank_number']));
		}
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
				$ktb = $rs;
				$ktb['api_token_1'] = decrypt(base64_decode($ktb['api_token_1']),SECRET_KEY_SALT); //accountTokenNo
				$ktb['api_token_2'] = decrypt(base64_decode($ktb['api_token_2']),SECRET_KEY_SALT); //tokenID
				$ktb['api_token_3'] = decrypt(base64_decode($ktb['api_token_3']),SECRET_KEY_SALT); //userIdentity

				$startDat=str_replace("/","",$before_date);
				$endDate=str_replace("/","",$current_date);
				$accountTokenNo=trim($ktb['api_token_1']); 	//accountTokenNo
				$tokenID=trim($ktb['api_token_2']); 			//tokenID
				$userIdentity=trim($ktb['api_token_3']); 		//userIdentity

				if(!empty($accountTokenNo) && !empty($tokenID) && !empty($userIdentity)){
					$obj_con_cron->autocommit(true);
					//01:00-01:05
					//18:00-18:05
					if(!empty($userIdentity) && in_array(date('H:i'),["18:00","18:01","18:02","18:03","18:04","18:05","01:00","01:01","01:02","01:03","01:04","01:05"])){
						if($ktb['chk_cron_login'] == "0"){
							$obj_con_cron->query("UPDATE `bank` SET `chk_cron_login` = '1' WHERE `bank`.`bank_number` = '".$ktb['bank_number']."'");
							loginUserIdentify($userIdentity,$ktb['bank_number']);
						}
					}else if($ktb['chk_cron_login'] == "1"){
						$obj_con_cron->query("UPDATE `bank` SET `chk_cron_login` = '0' WHERE `bank`.`bank_number` = '".$ktb['bank_number']."'");
					}


					$UrlGetConfig	= "https://www.krungthaiconnext.ktb.co.th/KTB-Line-Balance/deposit/statement-content";
					$data_en		= '{"action":"UPDATE","accountTokenNumber":"'.$accountTokenNo.'","activeIndex":"0","lastSeq":"0","userIdentity":"'.$userIdentity.'","hasViewMore":false,"transaction":[]}';

					/*$UrlGetConfig	= "https://www.krungthaiconnext.ktb.co.th/KTB-Line-Balance/deposit/account-detail";

					$data_en		= "accountTokenNumber=".$accountTokenNo."&userIdentity=".$userIdentity."&userTokenIdentity=".$tokenID."&channel=Krungthai+Next&language=TH";*/

					//List IP
					$host = 'proxyprivates.com';if($socket =@fsockopen($host, 3128, $errno, $errstr, 2)) {fclose($socket);} else {echo 'offline.';exit;}
					$proxy_array	= array(
						'proxyprivates.com'
					);
//proxy
					$loginpassw = 'proxydata:f6Hj2DBefuNd7xNs';
					$proxy_ip = $proxy_array[array_rand($proxy_array)];
					$proxy_port = '3128';

					$ch            = curl_init();
					curl_setopt($ch, CURLOPT_URL, $UrlGetConfig);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $data_en);
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
					curl_setopt($ch, CURLOPT_HTTPHEADER, array(
						// 'Content-Type: application/x-www-form-urlencoded'
						'Content-Type: application/json'
					));
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


					curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_port);
					curl_setopt($ch, CURLOPT_PROXYTYPE, 'HTTP');
					curl_setopt($ch, CURLOPT_PROXY, $proxy_ip);
					curl_setopt($ch, CURLOPT_PROXYUSERPWD, $loginpassw);

					$response = curl_exec($ch);
					curl_close($ch);

					preg_match_all('/{"(.*)}/', $response, $matches);
					date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
					$month_th = array(
						"ม.ค." => "01",
						"ก.พ." => "02",
						"มี.ค." => "03",
						"เม.ย." => "04",
						"พ.ค." => "05",
						"มิ.ย." => "06",
						"ก.ค." => "07",
						"ส.ค." => "08",
						"ก.ย." => "09",
						"ต.ค." => "10",
						"พ.ย." => "11",
						"ธ.ค." => "12",
					);
					$json=[];
					$balance = null;
					if(!empty($matches[0]) && !empty($matches[0][0])){
						$reData		= json_decode($matches[0][0], true);
						if(!is_null($reData)  && isset($reData['availableBalance'])){
							$balance = trim(str_replace(",","",$reData['availableBalance']));
						}
						if(!is_null($reData) && isset($reData['transactions'])){
							foreach ($reData['transactions'] as $index => $transaction){
								$date_time = explode(" ",trim($transaction['dateTime']));
								if(date('m') == "01" && $month_th[$date_time[1]] == "12"){
									$reData['transactions'][$index]['transDate'] = date('Y',strtotime ( '-1 year'))."-".$month_th[$date_time[1]]."-".$date_time[0];
								}else{
									$reData['transactions'][$index]['transDate'] = date('Y')."-".$month_th[$date_time[1]]."-".$date_time[0];
								}
								$reData['transactions'][$index]['transTime'] = $date_time[2].":00";
								$reData['transactions'][$index]['transCmt'] = $transaction['cmt'];
								$reData['transactions'][$index]['transAmt'] = $transaction['balance'];
								$reData['transactions'][$index]['transCmt_full'] = $transaction['cmt'];
								if(strpos($transaction['cmt'],"-") !== false){
									$reData['transactions'][$index]['bank_code'] = codeMatchDb(explode("-",trim($transaction['cmt']))[0]);
								}else if(strpos(strtoupper($transaction['cmt']),"TR") !== false){
									$reData['transactions'][$index]['bank_code'] = codeMatchDb("TR");
								}else{
									$reData['transactions'][$index]['bank_code'] = codeMatchDb("");
								}
							}
							$json = $reData['transactions'];
						}
					}else{
						loginUserIdentify($userIdentity,$ktb['bank_number']);
					}
					$master = array(
						"Balance" => str_replace(",","",$balance),
						"Transactions" => $json
					);

					if(!empty($master) && isset($master['Balance']) && is_numeric($master['Balance'])){
						$obj_con_cron->query("UPDATE `bank` SET `balance` = '".$master['Balance']."' WHERE `bank`.`bank_number` = '".$ktb['bank_number']."'");
					}else{
						echo json_encode(['status'=>false,"message" => "Bank ".$ktb['bank_number']." can not get Balance, Please check token"]);
					}
					if($chk_is_withdraw){
						echo json_encode(['status'=>true,"message" => "Bank for withdraw available : ".$ktb['bank_number']]);
						$Transactions = [];
						foreach($master['Transactions'] as $index_t => $data_t){
							if(($index_t+1) <= 49){
								$Transactions[] = $data_t;
							}
						}
						$master['Transactions'] = array_reverse($Transactions);
						foreach ($master['Transactions'] as $v) {
							if (!empty($v['transCmt']) && !is_null($v['transCmt']) && strpos($v['transAmt'], "-") !== false) {
								$balance = (float) str_replace(',', '', $v['transAmt']);
								$balance = (float) str_replace('-', '', $balance);
								$bank_number = trim($v['transCmt']);
								if((trim($v['transDate']) == $current_date_chk || trim($v['transDate'])) == $before_date_chk
								) {
									$time_explode = explode(":", trim($v['transTime']));

									//Check report sms
									$payment_gateway = $v['transCmt_full'];
									$sql_report_sms = "SELECT id FROM `report_smses` where DATE_FORMAT(create_date,'%Y-%m-%d') = '" . $v['transDate'] . "' and DATE_FORMAT(create_time,'%H:%i') = '" . $time_explode[0] . ":" . $time_explode[1] . "' and type_deposit_withdraw = 'W' and amount <=> CAST('".$balance."' AS DECIMAL(15, 2)) and payment_gateway = '" . $payment_gateway . "' and is_bot_running = '1'";
									$con_check_report_sms = $obj_con_cron->query($sql_report_sms);
									$check_report_sms = $con_check_report_sms->num_rows;
									if ($check_report_sms == 0) {

										//Insert report sms
										$sql_insert_report_sms = "INSERT INTO `report_smses` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`type_deposit_withdraw`) VALUES (NULL, '" . $rs['id'] . "','" . $payment_gateway . "','" . $balance . "', current_timestamp(),'1','" . $v['transDate'] . "','" . $v['transTime'] . "','W')";
										$obj_con_cron->query($sql_insert_report_sms);
										$report_sms_id = $obj_con_cron->insert_id;

										//Insert report
										$sql_insert_report = "INSERT INTO `reports` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`sms_statement_refer_id`,`type_deposit_withdraw`) VALUES (NULL, '" . $rs['id'] . "','" . $payment_gateway . "','" . $balance . "', current_timestamp(),'1','" . $v['transDate'] . "','" . $v['transTime'] . "','" . $report_sms_id . "','W')";
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
						echo json_encode(['status'=>true,"message" => "Bank for deposit available : ".$ktb['bank_number']]);
						/*print_r($master);
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
						);
						$Transactions = [];
						foreach($master['Transactions'] as $index_t => $data_t){
							if(($index_t+1) <= 49){
								$Transactions[] = $data_t;
							}
						}
						$master['Transactions'] = array_reverse($Transactions);

						$obj_con_cron->autocommit(false);

						foreach ($master['Transactions'] as $v) {

							$payment_gateway = $v['transCmt_full'];
							$type_deposit_withdraw = strpos($v['transAmt'],"-") === false ? 'D' : 'W';
							/*if (!file_exists('tmp/stmt-ktb/'.$v['transDate'])) {
								mkdir('tmp/stmt-ktb/'.$v['transDate'], 0755, true);
							}*/
							//$cache_filename = 'tmp/stmt-ktb/'.$v['transDate'].'/' .base64_encode($time_explode[0].$time_explode[1].$balance.$payment_gateway);
							if(!empty($v['transCmt']) && !is_null($v['transCmt']) && !in_array($v,$data_chk)){
								$data_chk[] = $v;
								$balance = (float) str_replace(',', '', $v['transAmt']);
								$balance = (float) str_replace('-', '', $balance);
								$bank_number = trim($v['transCmt']);
								if(strpos(trim($v['transCmt_full'])," ~ Future") !== false){
									$v['transCmt_full'] = trim(explode(" ~ Future",trim($v['transCmt_full']))[0]);
								}else if(strpos(trim($v['transCmt_full']),"~ Future") !== false){
									$v['transCmt_full'] = trim(explode("~ Future",trim($v['transCmt_full']))[0]);
								}
								if(strpos(trim($v['transCmt_full']),"-") !== false){
									$bank_number_explode = explode("-",trim($v['transCmt_full']));
									$bank_number = trim($bank_number_explode[1]);
								}else if(strpos(trim($v['transCmt_full']),"TR") !== false){
									$bank_number_explode = explode(" ",trim($v['transCmt_full']));
									$bank_number = trim($bank_number_explode[2]);
								}else{
									$bank_number = "";
								}

								if((trim($v['transDate']) == $current_date_chk || trim($v['transDate'])) == $before_date_chk
									//&& $rs['bank'] == trim($v['bank_code']) && $rs['bank_number'] == $bank_number
								){
									$time_explode = explode(":",trim($v['transTime']));

									//Check report sms
									$sql_report_sms = "SELECT id FROM `report_smses` where DATE_FORMAT(create_date,'%Y-%m-%d') = '".$v['transDate']."' and DATE_FORMAT(create_time,'%H:%i') = '".$time_explode[0].":".$time_explode[1]."' and type_deposit_withdraw = '".$type_deposit_withdraw."' and amount <=> CAST('".$balance."' AS DECIMAL(15, 2)) and payment_gateway = '".$payment_gateway."' and is_bot_running = '1'";
									$con_check_report_sms = $obj_con_cron->query($sql_report_sms);
									$check_report_sms = $con_check_report_sms->num_rows;
									if($check_report_sms == 0) {
										try {
											$check_all = true;

											//Insert report sms
											$sql_insert_report_sms = "INSERT INTO `report_smses` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`type_deposit_withdraw`) VALUES (NULL, '" . $rs['id'] . "','" . $payment_gateway . "','" . $balance . "', current_timestamp(),'1','" . $v['transDate'] . "','" . $v['transTime'] . "','".$type_deposit_withdraw."')";
											$check_all = $obj_con_cron->query($sql_insert_report_sms);
											if($check_all){
												$report_sms_id = $obj_con_cron->insert_id;

												//Insert report
												$sql_insert_report = "INSERT INTO `reports` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`sms_statement_refer_id`,`type_deposit_withdraw`) VALUES (NULL, '" . $rs['id'] . "','" . $payment_gateway . "','" . $balance . "', current_timestamp(),'1','" . $v['transDate'] . "','" . $v['transTime'] . "','" . $report_sms_id . "','".$type_deposit_withdraw."')";
												$check_all = $obj_con_cron->query($sql_insert_report);
												if($check_all){
													$report_id = $obj_con_cron->insert_id;

													//Update sms_statement_refer_id on report sms
													$sql_update_report_sms = "UPDATE `report_smses` SET `sms_statement_refer_id` = '" . $report_id . "' WHERE `report_smses`.`id` = " . $report_sms_id;
													$check_all = $obj_con_cron->query($sql_update_report_sms);
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
																	$start_time_can_not_deposit_stmt = new DateTime($rs['start_time_can_not_deposit']);
																	$end_time_can_not_deposit_stmt = new DateTime($rs['end_time_can_not_deposit']);
																	$time_stmt_chk = new DateTime(trim($v['transDate'])." ".$time_explode[0].":".$time_explode[1]);
																	if(
																		$time_stmt_chk->getTimestamp() >= $start_time_can_not_deposit_stmt->getTimestamp() &&
																		$time_stmt_chk->getTimestamp() <= $end_time_can_not_deposit_stmt->getTimestamp()
																	){
																		if($obj_con_cron->commit()){
																			//$content = base64_encode("SELECT * FROM `report_smses` where DATE_FORMAT(create_date,'%Y-%m-%d') = '".$v['transDate']."' and DATE_FORMAT(create_time,'%H:%i') = '".$time_explode[0].":".$time_explode[1]."' and type_deposit_withdraw = '".$type_deposit_withdraw."' and amount <=> CAST('".$balance."' AS DECIMAL(15, 2)) and payment_gateway = '".$payment_gateway."' and is_bot_running = '1'");
																			//file_put_contents($cache_filename, $content);
																		}
																		$chk_ignore_create_transaction_credit_history = true;
																		echo json_encode(['status'=>true,"message" => "Bank for deposit available : ".$ktb['bank_number']." Ignore create transaction, credit history => start & end time range for auto ".$rs['start_time_can_not_deposit']." - ".$rs['end_time_can_not_deposit']]);
																	}
																}catch (Exception $ex){
																	if($obj_con_cron->commit()){
																		//$content = base64_encode("SELECT * FROM `report_smses` where DATE_FORMAT(create_date,'%Y-%m-%d') = '".$v['transDate']."' and DATE_FORMAT(create_time,'%H:%i') = '".$time_explode[0].":".$time_explode[1]."' and type_deposit_withdraw = '".$type_deposit_withdraw."' and amount <=> CAST('".$balance."' AS DECIMAL(15, 2)) and payment_gateway = '".$payment_gateway."' and is_bot_running = '1'");
																		//file_put_contents($cache_filename, $content);
																	}
																	echo json_encode(['status'=>true,"message" => "Bank for deposit available : ".$ktb['bank_number']." Ignore create transaction, credit history => start & end time range for auto ".$rs['start_time_can_not_deposit']." - ".$rs['end_time_can_not_deposit']. "Error {} ".$ex->getMessage()]);
																}
																if(!$chk_ignore_create_transaction_credit_history){
																	if(!in_array($rs['id'].$payment_gateway.$v['transDate'].$time_explode[0].":".$time_explode[1],$chk_duplicate_date_and_hour_minute)){
																		$chk_duplicate_date_and_hour_minute[] = $rs['id'].$payment_gateway.$v['transDate'].$time_explode[0].":".$time_explode[1];
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
																		//$content = base64_encode("SELECT * FROM `report_smses` where DATE_FORMAT(create_date,'%Y-%m-%d') = '".$v['transDate']."' and DATE_FORMAT(create_time,'%H:%i') = '".$time_explode[0].":".$time_explode[1]."' and type_deposit_withdraw = '".$type_deposit_withdraw."' and amount <=> CAST('".$balance."' AS DECIMAL(15, 2)) and payment_gateway = '".$payment_gateway."' and is_bot_running = '1'");
																		//file_put_contents($cache_filename, $content);
																	}
																	echo json_encode(['status'=>true,"message" => "Bank for deposit available : ".$ktb['bank_number']." Ignore auto deposit amount, ". (float)$balance." >= ".(float)$deposit_min_amount_for_disable_auto]);
																}else{
																	//Check transaction
																	$sql_check = "SELECT id FROM `transaction` where DATE_FORMAT(date_bank,'%Y-%m-%d %H:%i') = '".trim($v['transDate'])." ".$time_explode[0].":".$time_explode[1]."' and bank_number = '".$bank_number."' and amount = '".$balance."' and type = '1'";
																	$con_check = $obj_con_cron->query($sql_check);
																	$check = $con_check->num_rows;
																	if($check == 0){

																		$bank_number_equal = $bank_number;
																		$bank_equal = trim($v['bank_code']);
																		$bank_equal_2 = substr(trim($v['bank_code']),0,1) == "0" ? substr(trim($v['bank_code']),1) : trim($v['bank_code']);
																		if(!empty($bank_equal) && !empty($bank_number_equal)){
																			$sql_acc_check = "SELECT id,amount_deposit_auto,bank,bank_number,bank_name,username FROM `account` where (bank = '".$bank_equal."' OR bank = '".$bank_equal_2."') and bank_number = '".$bank_number_equal."' and deleted = '0'";
																			$con_acc_check = $obj_con_cron->query($sql_acc_check);
																			$check_acc = $con_acc_check->num_rows;
																			if($check_acc == 1) {
																				$check_add_once = false;
																				while ($rs_acc = $con_acc_check->fetch_assoc()) {
																					if (!$check_add_once) {
																						$check_add_once = true;
																						$sql = "INSERT INTO `transaction` (`id`, `date_bank`, `amount`,`account`,`type`,`bank_number`,`updated_at`) VALUES (NULL, '" . trim($v['transDate']) . " " . trim($v['transTime']) . "', '" . $balance . "', '" . $rs_acc['id'] . "', '1', '" . $rs_acc['bank_number'] . "', current_timestamp())";
																						$check_all = $obj_con_cron->query($sql);
																						if($check_all){
																							$credit_before = $rs_acc['amount_deposit_auto'];
																							$credit_after = !is_null($rs_acc['amount_deposit_auto']) && $rs_acc['amount_deposit_auto'] !== "" ? (float)$rs_acc['amount_deposit_auto'] + $balance : $balance;
																							$check_all = $obj_con_cron->query("UPDATE `account` SET `amount_deposit_auto` = '" . $credit_after . "' WHERE `account`.`id` = " . $rs_acc['id']);
																							if($check_all){
																								$sql_insert_credit_his = "INSERT INTO `credit_history` (`id`, `process`, `credit_before`,`credit_after`,`type`,`account`,`transaction`,`admin`,`date_bank`,`bank_id`,`bank_name`,`bank_number`,`bank_code`,`username`) VALUES (NULL, '" . $balance . "', '" . $credit_before . "', '" . $credit_after . "', '1', '" . $rs_acc['id'] . "', '1', '0', '".date("Y-m-d H:i:s", strtotime($v['transDate']." ".$v['transTime']))."', '".$ktb['id']."', '".$ktb['account_name']."', '".$ktb['bank_number']."', '".$ktb['bank_code']."', '".$rs_acc['username']."')";
																								$check_all = $obj_con_cron->query($sql_insert_credit_his);
																								if($check_all){
																									$credit_history_id = $obj_con_cron->insert_id;

																									//Update deposit_withdraw_id on report sms,report
																									$sql_update_report_sms = "UPDATE `report_smses` SET `deposit_withdraw_id` = '" . $credit_history_id . "' WHERE `report_smses`.`id` = " . $report_sms_id;
																									$check_all = $obj_con_cron->query($sql_update_report_sms);
																									if($check_all){
																										$sql_update_report = "UPDATE `reports` SET `deposit_withdraw_id` = '" . $credit_history_id . "' WHERE `reports`.`id` = " . $report_id;
																										$check_all = $obj_con_cron->query($sql_update_report);
																										if($check_all){
																											//Insert line notify
																											if ($status_create_line_notify && !empty($token_line_notify)) {
																												$message = "ยอดฝาก " . number_format($balance, 2) . " บาท ยูส " . $rs_acc['username'] . " เวลา " . trim($v['transDate']) . " " . trim($v['transTime']) . " ปรับโดย AUTO";
																												$sql_insert_line_notify = "INSERT INTO `log_line_notify` (`id`, `type`, `message`) VALUES (NULL, '1', '" . $message . "')";
																												$obj_con_cron->query($sql_insert_line_notify);
																											}
																											if($obj_con_cron->commit()){
																												//$content = base64_encode("SELECT * FROM `report_smses` where DATE_FORMAT(create_date,'%Y-%m-%d') = '".$v['transDate']."' and DATE_FORMAT(create_time,'%H:%i') = '".$time_explode[0].":".$time_explode[1]."' and type_deposit_withdraw = '".$type_deposit_withdraw."' and amount <=> CAST('".$balance."' AS DECIMAL(15, 2)) and payment_gateway = '".$payment_gateway."' and is_bot_running = '1'");
																												//file_put_contents($cache_filename, $content);
																											}
																											echo json_encode(['status' => true, "message" => trim($v['transDate']) . " " . trim($v['transTime']) . " | " . $balance . ' | ' . $rs_acc['bank_number']]);
																										}else{
																											if($obj_con_cron){
																												$obj_con_cron->rollback();
																											}else if(!is_null($obj_con_cron)){
																												$obj_con_cron->rollback();
																											}
																											echo json_encode(['status'=>false,"message"=>"Exception Sql : ".$sql_update_report]);
																										}
																									}else{
																										if($obj_con_cron){
																											$obj_con_cron->rollback();
																										}else if(!is_null($obj_con_cron)){
																											$obj_con_cron->rollback();
																										}
																										echo json_encode(['status'=>false,"message"=>"Exception Sql : ".$sql_update_report_sms]);
																									}
																								}else{
																									if($obj_con_cron){
																										$obj_con_cron->rollback();
																									}else if(!is_null($obj_con_cron)){
																										$obj_con_cron->rollback();
																									}
																									echo json_encode(['status'=>false,"message"=>"Exception Sql : ".$sql_insert_credit_his]);
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
																							echo json_encode(['status'=>false,"message"=>"Exception Sql : ".$sql]);
																						}
																					}
																				}
																			}else{
																				if($obj_con_cron->commit()){

																				}
																			}
																		}
																	}else{
																		if($obj_con_cron->commit()){
																			//$content = base64_encode("SELECT * FROM `report_smses` where DATE_FORMAT(create_date,'%Y-%m-%d') = '".$v['transDate']."' and DATE_FORMAT(create_time,'%H:%i') = '".$time_explode[0].":".$time_explode[1]."' and type_deposit_withdraw = '".$type_deposit_withdraw."' and amount <=> CAST('".$balance."' AS DECIMAL(15, 2)) and payment_gateway = '".$payment_gateway."' and is_bot_running = '1'");
																			//file_put_contents($cache_filename, $content);
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
									}
								}
							}
						}

					}
				}else{
					echo json_encode(['status'=>false,"message" => "Bank ".$bank_number." empty params token"]);
				}


			}catch (Exception $ex){
				echo json_encode(['status'=>false,"message" => "Bank Ktb ".$ktb['bank_number']." Error Exception ".$ex->getMessage()]);
			}
		}
	}
	$chk_duplicate_date_and_hour_minute = [];
}
function loginUserIdentify($userIdentity,$bank_number){
	$urlLogin		= 'https://www.krungthaiconnext.ktb.co.th/KTB-Line-Balance/check-access-limit';
	$dataPayload		= '{"userIdentity":"'.$userIdentity.'"}';
	$ch            = curl_init();
	curl_setopt($ch, CURLOPT_URL, $urlLogin);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 45);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $dataPayload);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json'
	));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$response = curl_exec($ch);
	curl_close($ch);
	echo json_encode(['status'=>false,"message" => "Bank ".$bank_number." update user identify : ".date('Y-m-d H:i:s')]);
}
function codeMatchDb($value){
	if ($value=="014") {
		return "05";
	}
	if ($value=="025") {
		return "06";
	}
	if ($value=="002") {
		return "01";
	}
	if ($value=="030") {
		return "07";
	}
	if ($value=="034") {
		return "09";
	}
	if ($value=="004") {
		return "02";
	}

	if ($value=="TR") {
		return "03";
	}

	if ($value=="065") {
		return "08";
	}

	if ($value=="011") {
		return "04";
	}
	return null;
}
?>
