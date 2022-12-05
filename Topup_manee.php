
<?php
// $sec_rand = rand(10,20);
// sleep($sec_rand);
require('config.php');
ob_start('ob_gzhandler');
require('conn_cron.php');
require('lib/api_mamanee.php');
date_default_timezone_set("Asia/Bangkok");

$sql_bank_check = "SELECT * FROM `bank` where status = '1' and (bank_code = '05' or bank_code = '5') and deleted = '0' and (store_code is not null or store_code != '') and manee_status = '1' limit 1";
$con_bank_check = $obj_con_cron->query($sql_bank_check);
$rs =$con_bank_check->fetch_assoc();
$scb['api_token_1'] = decrypt(base64_decode($rs['api_token_1']),SECRET_KEY_SALT);
$scb['api_token_2'] = decrypt(base64_decode($rs['api_token_2']),SECRET_KEY_SALT);
$api = new mamanee($scb['api_token_2'],$scb['api_token_1'],$rs['store_code']);
$a = $api->transactions();
$json = json_decode($a, true)['data']['walletList'][0]['transactions']['list'];
// echo '<pre>';
// print_r($json);
// echo '</pre>';
foreach($json as $v){
    $balance = (float) str_replace(',', '', $v['amount']);
    $payment_gateway = $v['description'];
    $day = explode("T",trim($v['timestamp']));
    $tam = explode(".",trim($day[1]));
    $time_explode = explode(":",trim($tam[0]));
    $sql_report_sms = "SELECT * FROM `report_smses` where type_deposit_withdraw = 'D' and amount = '".$balance."' and payment_gateway = '".$payment_gateway."' and is_bot_running = '1'";
    $con_check_report_sms = $obj_con_cron->query($sql_report_sms);
    $check_report_sms = $con_check_report_sms->num_rows;


    if($check_report_sms == 0){
        // $check_all = true;

        //Insert report sms
        $sql_insert_report_sms = "INSERT INTO `report_smses` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`type_deposit_withdraw`) VALUES (NULL, '".$rs['id']."','".$payment_gateway."','".$balance."', current_timestamp(),'1','".$day[0]."','".$tam[0]."','D')";
        $check_all = $obj_con_cron->query($sql_insert_report_sms);
        if($check_all){
            $report_sms_id = $obj_con_cron->insert_id;

            //Insert report
            $sql_insert_report = "INSERT INTO `reports` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`sms_statement_refer_id`,`type_deposit_withdraw`) VALUES (NULL, '".$rs['id']."','".$payment_gateway."','".$balance."', current_timestamp(),'1','".$day[0]."','".$tam[0]."','".$report_sms_id."','D')";
            $check_all = $obj_con_cron->query($sql_insert_report);
            if($check_all){
                $report_id = $obj_con_cron->insert_id;

                //Update sms_statement_refer_id on report sms
                $sql_update_report_sms = "UPDATE `report_smses` SET `sms_statement_refer_id` = '".$report_id."' WHERE `report_smses`.`id` = ".$report_sms_id;
                $check_all =  $obj_con_cron->query($sql_update_report_sms);
                if($check_all){
                    $sql_check = "SELECT * FROM `transaction` where DATE_FORMAT(date_bank,'%Y-%m-%d %H:%i') = '".$day[0]." ".$time_explode[0].":".$time_explode[1]."' and amount = '".$balance."' and type = '1'";

                    $con_check = $obj_con_cron->query($sql_check);
                    $check = $con_check->num_rows;
                    if($check == 0){
                        $sql_acc_check = "SELECT * FROM `account` where ref_manee = '".$payment_gateway."' and deleted = '0' limit 1";
                        $con_acc_check = $obj_con_cron->query($sql_acc_check);
                        $check_acc = $con_acc_check->num_rows;
                        if($check_acc){
                            $rs_acc = $con_acc_check->fetch_assoc();
                            $sql ="INSERT INTO `transaction` (`id`, `date_bank`, `amount`,`account`,`type`,`bank_number`,`updated_at`) VALUES (NULL, '".$day[0]." ".$time_explode[0].":".$time_explode[1]."', '".$balance."', '".$rs_acc['id']."', '1', '".$rs_acc['bank_number']."', current_timestamp())";
                            $check_all = $obj_con_cron->query($sql);
                            if($check_all){
                                $credit_before = $rs_acc['amount_deposit_auto'];
                                $credit_after = !is_null($rs_acc['amount_deposit_auto']) && $rs_acc['amount_deposit_auto'] !== "" ? (float)$rs_acc['amount_deposit_auto'] + $balance : $balance;
                                $check_all = $obj_con_cron->query("UPDATE `account` SET `amount_deposit_auto` = '".$credit_after."',`ref_manee` = NULL WHERE `account`.`id` = ".$rs_acc['id']);
                                if($check_all){
                                    $sql_insert_credit_his ="INSERT INTO `credit_history` (`id`, `process`, `credit_before`,`credit_after`,`type`,`account`,`transaction`,`admin`,`date_bank`,`bank_id`,`bank_name`,`bank_number`,`bank_code`,`username`) 
                                    VALUES (NULL, '".$balance."', '".$credit_before."', '".$credit_after."', '1', '".$rs_acc['id']."', '1', '0', '".date("Y-m-d H:i:s", strtotime($day[0]." ".$tam[0]))."', '".$rs['id']."', '".$rs['account_name']."', '".$rs['bank_number']."', '".$rs['bank_code']."', '".$rs_acc['username']."')";
                                    $check_all = $obj_con_cron->query($sql_insert_credit_his);
                                    if($check_all){
                                        $credit_history_id = $obj_con_cron->insert_id;

                                        //Update deposit_withdraw_id on report sms,report
                                        $sql_update_report_sms = "UPDATE `report_smses` SET `deposit_withdraw_id` = '".$credit_history_id."' WHERE `report_smses`.`id` = ".$report_sms_id;
                                        $check_all = $obj_con_cron->query($sql_update_report_sms);
                                        if($check_all){
                                            $sql_update_report = "UPDATE `reports` SET `deposit_withdraw_id` = '".$credit_history_id."' WHERE `reports`.`id` = ".$report_id;
                                            $check_all = $obj_con_cron->query($sql_update_report);
                                            // if($status_create_line_notify && !empty($token_line_notify)){
                                            //     $message = "ยอดฝาก ".number_format($balance,2)." บาท ยูส ".$rs_acc['username']." เวลา ".$day[0]." ".$tam[0]." ปรับโดย AUTO";
                                            //     $sql_insert_line_notify ="INSERT INTO `log_line_notify` (`id`, `type`, `message`) VALUES (NULL, '1', '".$message."')";
                                            //     $obj_con_cron->query($sql_insert_line_notify);
                                            // }
                                            exit();
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
