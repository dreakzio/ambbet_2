<?php

ini_set("display_errors",0);
error_reporting(E_ALL);
date_default_timezone_set('Asia/Bangkok');

require('../config.php');
require_once ('../conn_cron.php');

$json = array();

$current_date = date("d/m/Y");
$current_date_chk = date("Y-m-d");
$before_date = date('d/m/Y',(strtotime ( '-1 day' , strtotime ( date("Y-m-d")) ) ));
$before_date_chk = date('Y-m-d',(strtotime ( '-1 day' , strtotime ( date("Y-m-d")) ) ));
function is_between_times( $start = null, $end = null, $current = null ) {
    if ( $start == null ) $start = date('Y-m-d').' 00:00:00';
    if ( $end == null ) $end = date('Y-m-d').' 23:59:59';
    if ( $current == null ) $current = date('Y-m-d H:i:s');

    if(date_to_stamp($start) <= date_to_stamp($current) && date_to_stamp($current) <= date_to_stamp($end) ){
        return true;
    }else{
        return false;
    }
}

function date_to_stamp( $date, $slash_time = true, $timezone = 'Asia/Bangkok', $expression = "#^\d{2}([^\d]*)\d{2}([^\d]*)\d{4}$#is" ) {
    $return = false;
    $_timezone = date_default_timezone_get();
    date_default_timezone_set( $timezone );
    if( preg_match( $expression, $date, $matches ) )
        $return = date( "Y-m-d " . ( $slash_time ? '00:00:00' : "h:i:s" ), strtotime( str_replace( array($matches[1], $matches[2]), '-', $date ) . ' ' . date("h:i:s") ) );
    date_default_timezone_set( $_timezone );
    return $return;
}

require ('send_line_message.php');

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
$sql_line_send_messages_status = "SELECT a.value as line_send_messages_status ,b.value as line_messages_token,c.value as web_name ,d.value as line_login_callback
									FROM `web_setting` as a , `web_setting` as b , `web_setting` as c , `web_setting` as d
									where a.name = 'line_send_messages_status'
									  and b.name = 'line_messages_token' 
									  and c.name = 'web_name'
									  and d.name = 'line_login_callback'";
$ds_line_send_messages_status = $obj_con_cron->query($sql_line_send_messages_status);
$da_line_send_messages_status   = $ds_line_send_messages_status->fetch_assoc();

$json = file_get_contents('php://input');
$request = json_decode($json, true);
//print_r($request);
$data1 = $request['data'];
//$data1 = '03/07@13:20 120.00 จากKBANK/x898822เข้าx410482 ใช้ได้15,120.00บ';
telegram_push_message('SCB sms :'.$data1);
//sleep(20);
header('Content-Type: application/json');
if($request['SDT']=='027777777'){

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
		'10' => 'KKP',
	);
	print_r($bank_list);
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
		'kkp' => ['10'],
	);

   // $data1 = $request['data'];
    $data1 = $data1;

    $test ="sms :".$data1.' เข้า server เวลา'.date("Y-m-d H:i:s");
    $pos = strpos($data1, 'โอนจาก');
    $pos2 = strpos($data1, 'จาก');
	$chk_duplicate_date_and_hour_minute = [];

	$deposit_min_amount_for_disable_auto = null;
	$sql_deposit_min_amount_for_disable_auto_check = "SELECT * FROM `web_setting` where name = 'deposit_min_amount_for_disable_auto'";
	$con_deposit_min_amount_for_disable_auto_check = $obj_con_cron->query($sql_deposit_min_amount_for_disable_auto_check);
	while($rs_deposit_min_amount_for_disable_auto =$con_deposit_min_amount_for_disable_auto_check->fetch_assoc() ){
		if(!empty($rs_deposit_min_amount_for_disable_auto['value'])){
			$deposit_min_amount_for_disable_auto = trim($rs_deposit_min_amount_for_disable_auto['value']);
		}
	}

    if($pos2!==false){

        $list = explode(" ",$data1);
        $bank_codetemp= str_replace('จาก','',$list[2]);

        $bank_code =substr($bank_codetemp,0,strpos($bank_codetemp, '/'));
        $bank_type  = $bank_code;//รหัสธนาคาร

        $list_data = explode(" ",$data1);

        $amount = trim($list_data[1]);
        $amount =str_replace("บ","",$amount);
        $amount =str_replace(",","",$amount);  // ยอดเงินที่โอน

        $bank_list = explode("x",$list_data[2]);
		$bank_to = $bank_list[2];
        $bank_no = str_replace('เข้า','',$bank_list['1']);
        $bank_no = $bank_no; //เบอร์บัญชีผู้รับ

        $list_time = explode("@",$list[0]);
        $c_date = explode('/', $list_time[0]);
        $ky = date("Y");
        $c_date = $c_date[0].'/'.$c_date[1].'/'.$ky;//วันที่
        $c_time = $list_time[1];
        $current_datetime = $c_date.' '.$c_time;//เวลา

		$sql_bank_check = "SELECT * FROM `bank` 
         						where status = '1' 
         						  and api_type=4 
         						  and (bank_code = '05' or bank_code = '5') 
         						  and deleted = '0' and bank_number LIKE '%{$bank_to}%'
         						order by status_withdraw asc";
		$con_bank_check = $obj_con_cron->query($sql_bank_check);
		$rs =$con_bank_check->fetch_assoc();
		$scb = $rs;

			$balance = (float) str_replace(',', '', $amount);
			$bank_number = explode("_",$data1);
			if(!empty($bank_number) && count($bank_number) > 1){
				$bank_number = $bank_number[1];
			}
			$date_explode = explode("/",trim($c_date)); //d/m/Y
			$v['date'] = $date_explode[2]."-".$date_explode[1]."-".$date_explode[0];
			$after_date_chk = date('Y-m-d',(strtotime ( '+1 day' , strtotime ( date("Y-m-d")) ) ));

			$payment_gateway = $data1;
			$list_data[3]= str_replace('ใช้ได้','',$list_data[3]);
			$balance_to= str_replace(",","",str_replace('บ','',$list_data[3]));

			if($balance_to > 0){
				$obj_con_cron->query("UPDATE `bank` SET `balance` = '".$balance_to."' WHERE `bank`.`bank_number` = '".$scb['bank_number']."'");
			}

			if((trim($v['date']) == $current_date_chk || trim($v['date']) == $before_date_chk || trim($v['date']) == $after_date_chk) ){

				$time_explode = explode(":",trim($c_time));

				//Check report sms
				$sql_report_sms = "SELECT id FROM `report_smses` where DATE_FORMAT(create_date,'%Y-%m-%d') = '".$v['date']."' 
												and DATE_FORMAT(create_time,'%H:%i') = '".$time_explode[0].":".$time_explode[1]."' 
												and type_deposit_withdraw = 'D' 
												and amount <=> CAST('".$balance."' AS DECIMAL(15, 2)) 
												and payment_gateway = '".$payment_gateway."' 
												and is_bot_running = '1'";
				$con_check_report_sms = $obj_con_cron->query($sql_report_sms);
				$check_report_sms = $con_check_report_sms->num_rows;

				if($check_report_sms == 0){
					try
					{
						$check_all = true;

						//Insert report sms
						$sql_insert_report_sms = "INSERT INTO `report_smses` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`type_deposit_withdraw`) 
																	VALUES (NULL, '".$rs['id']."','".$payment_gateway."','".$balance."', current_timestamp(),'1','".$v['date']."','".$v['time']."','D')";
						$check_all = $obj_con_cron->query($sql_insert_report_sms);
						if($check_all){
							$report_sms_id = $obj_con_cron->insert_id;

							//Insert report
							$sql_insert_report = "INSERT INTO `reports` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`sms_statement_refer_id`,`type_deposit_withdraw`) 
																VALUES (NULL, '".$rs['id']."','".$payment_gateway."','".$balance."', current_timestamp(),'1','".$v['date']."','".$v['time']."','".$report_sms_id."','D')";
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

										if(!is_null($deposit_min_amount_for_disable_auto)
											&& is_numeric($deposit_min_amount_for_disable_auto)
											&& (float)$deposit_min_amount_for_disable_auto > 0
											&& (float)$balance >= (float)$deposit_min_amount_for_disable_auto){
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
												if($bank_no !=='' && array_key_exists(trim(strtolower($bank_code)),$bank_stmt_list)){
													$bank_in = $bank_stmt_list[trim(strtolower($bank_code))];
													//print_r($bank_in);
													$bank_number_like = "%".trim($bank_no);

													if(!empty($bank_in) && count($bank_in) > 0 && !empty($bank_number_like)){
														$sql_acc_check = "SELECT id,amount_deposit_auto,bank,bank_number,bank_name,username,linebot_userid 
																				FROM `account` 
																				where bank IN ('".implode("','",$bank_in)."') 
																				and bank_number like '".$bank_number_like."' 
																				and deleted = '0' 
																				ORDER BY active_deposit_date DESC";
														$con_acc_check = $obj_con_cron->query($sql_acc_check);
														$check_acc = $con_acc_check->num_rows;
														if(true){
															//$check_add_once = false;
															while($rs_acc = $con_acc_check->fetch_assoc()) {

																	$check_add_once = true;
																	if($check_add_once){
																		$sql ="INSERT INTO `transaction` (`id`, `date_bank`, `amount`,`account`,`type`,`bank_number`,`updated_at`) 
																								VALUES (NULL, '".$v['date']." ".$v['time']."', '".$balance."', '".$rs_acc['id']."', '1', '".$rs_acc['bank_number']."', current_timestamp())";
																		$check_all = $obj_con_cron->query($sql);
																		if($check_all){
																			$credit_before = $rs_acc['amount_deposit_auto'];
																			$credit_after = !is_null($rs_acc['amount_deposit_auto']) && $rs_acc['amount_deposit_auto'] !== "" ? (float)$rs_acc['amount_deposit_auto'] + $balance : $balance;

																			$check_all = $obj_con_cron->query("UPDATE `account` SET `amount_deposit_auto` = '".$credit_after."' WHERE `account`.`id` = ".$rs_acc['id']);
																			if($check_all){
																				$sql_insert_credit_his ="INSERT INTO `credit_history` (`id`, `process`, `credit_before`,`credit_after`,`type`,`account`,`transaction`,`admin`,`date_bank`,`bank_id`,`bank_name`,`bank_number`,`bank_code`,`username`) 
																															VALUES (NULL, '".$balance."', '".$credit_before."', '".$credit_after."', '1', '".$rs_acc['id']."', '1', '0', '".date("Y-m-d H:i:s", strtotime($v['date']." ".$v['time']))."', '".$scb['id']."', '".$scb['account_name']."', '".$scb['bank_number']."', '".$scb['bank_code']."', '".$rs_acc['username']."')";
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
																//}

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

function httpGet($url)
{
    $ch = curl_init();

    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    $output=curl_exec($ch);

    curl_close($ch);
    return $output;
}
function telegram_push_message($message){

	// $chOne = curl_init();
	$access_token ='6166097230:AAF6rPPTRRvUmQ2EATxar9QnZgyGFeZ09S8';
	$group_id ='-994381529';

	$website="https://api.telegram.org/bot".$access_token;
	//$chatId=1234567;  //Receiver Chat Id
	$params=[
		'chat_id'=>$group_id,
		'text'=>$_SERVER['SERVER_NAME']." : ".$message,
	];
	$ch = curl_init($website . '/sendMessage');
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$result = curl_exec($ch);
	curl_close($ch);
}
?>
