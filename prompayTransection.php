<?php
$sec_rand = rand(10,20);
sleep($sec_rand);
require('config.php');
require('conn_cron.php');
date_default_timezone_set("Asia/Bangkok");
$date_now=date("Y-m-d");
$startDate=$date_now;
$endDate=$date_now;

$sql_bank_check = "SELECT * FROM `bank` where status = '1' and (bank_code = '05' or bank_code = '5') and promptpay_number is not null and promptpay_status = 1 and deleted = 0 limit 1";
$con_bank_check = $obj_con_cron->query($sql_bank_check);
$scb = $con_bank_check->fetch_assoc();

$GLOBALS["accountFrom"]=$scb['bank_number'];
$pin=decrypt(base64_decode($scb['api_token_2']),SECRET_KEY_SALT);
$deviceId=decrypt(base64_decode($scb['api_token_1']),SECRET_KEY_SALT);
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://fasteasy.scbeasy.com:8443/v3/login/preloadandresumecheck',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_HEADER=> 1,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{"deviceId":"'.$deviceId.'","jailbreak":"0","tilesVersion":"42","userMode":"INDIVIDUAL"}',
    CURLOPT_HTTPHEADER => array(
    'Accept-Language:      th',
    'scb-channel:  APP',
	'user-agent:        Android/11;FastEasy/3.50.0/5329',
    'Content-Type:  application/json; charset=UTF-8',
    'Hos:  fasteasy.scbeasy.com:8443',
    'Connection:  close',
    ),
));

$response = curl_exec($curl);

curl_close($curl);

preg_match_all('/(?<=Api-Auth: ).+/', $response, $Auth);
$Auth=$Auth[0][0];

if ($Auth=="") {
    echo json_encode(['status'=>false,"message" => "Auth error[01]"]);
    exit();
}

$curl1 = curl_init();

curl_setopt_array($curl1, array(
    CURLOPT_URL => 'https://fasteasy.scbeasy.com/isprint/soap/preAuth',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{"loginModuleId":"PseudoFE"}',
    CURLOPT_HTTPHEADER => array(
    'Api-Auth: '.$Auth,
    'Content-Type: application/json',
    ),
));

$response1 = curl_exec($curl1);

curl_close($curl1);


$data = json_decode($response1,true);

$hashType=$data['e2ee']['pseudoOaepHashAlgo'];
$Sid=$data['e2ee']['pseudoSid'];
$ServerRandom=$data['e2ee']['pseudoRandom'];
$pubKey=$data['e2ee']['pseudoPubKey'];


$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => "http://206.189.47.27:80/pin/encrypt",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => "Sid=".$Sid."&ServerRandom=".$ServerRandom."&pubKey=".$pubKey."&pin=".$pin."&hashType=".$hashType,
    CURLOPT_HTTPHEADER => array(
    "Content-Type: application/x-www-form-urlencoded"
    ),
));

$response = curl_exec($curl);

curl_close($curl);


$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://fasteasy.scbeasy.com/v3/login',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_HEADER=> 1,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{"deviceId":"'.$deviceId.'","pseudoPin":"'.$response.'","pseudoSid":"'.$Sid.'"}',
    CURLOPT_HTTPHEADER => array(
    'Api-Auth:  '.$Auth,
    'Content-Type: application/json'
    ),
));

$response_auth = curl_exec($curl);

curl_close($curl);

preg_match_all('/(?<=Api-Auth:).+/', $response_auth, $Auth_result);
$Auth1=$Auth_result[0][0];

if ($Auth1=="") {
    echo json_encode(['status'=>false,"message" => "Auth error[02]"]);
    exit();

}


$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "https://fasteasy.scbeasy.com/v2/deposits/casa/transactions",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS =>"{\"accountNo\":\"".$GLOBALS["accountFrom"]."\",\"endDate\":\"".$endDate."\",\"pageNumber\":\"1\",\"pageSize\":20,\"productType\":\"2\",\"startDate\":\"".$startDate."\"}",
    CURLOPT_HTTPHEADER => array(
    'Api-Auth: '.$Auth1,
    'Accept-Language: th',
    'Content-Type: application/json; charset=UTF-8'
    ),
));

$response = curl_exec($curl);

curl_close($curl);



$result=json_decode($response,true);
if($result == "" || $result == null){
    echo json_encode(['status'=>false,"message" => "Empty response[03]"]);
    exit();
}

$check=$result['status']['description'];

$json=[];
foreach ($result['data']['txnList'] as $value) {

    $txnRemark=$value['txnRemark']; //รายการ
    $txnDateTime=$value['txnDateTime']; //เวลา
    $txnAmount=str_replace(',','',$value['txnAmount']); //ยอดเงิน

    preg_match('/Prompt/', $txnRemark, $txnRemark1);



        if ($txnRemark1[0]=="Prompt") {

            $payment_gateway = $txnRemark;
            $balance = $txnAmount;
            $v['date'] = date("Y-m-d", strtotime($txnDateTime));
            $v['time'] = date("H:i:s", strtotime(str_replace('T','',str_replace('.000+07:00','',$txnDateTime))));
            //$sql_report_sms = "SELECT * FROM `report_smses` where payment_gateway = '".$payment_gateway."' and amount = '".$balance."' and create_date = '".$v['date']."' and create_time = '".$v['time']."' and is_bot_running = '1'";
			$sql_report_sms = "SELECT * FROM `report_smses` where DATE_FORMAT(create_date,'%Y-%m-%d') = '".$v['date']."' and DATE_FORMAT(create_time,'%H:%i:%s') = '".$v['time']."' and type_deposit_withdraw = 'D' and amount <=> CAST('".$balance."' AS DECIMAL(15, 2)) and payment_gateway = '".$payment_gateway."' and is_bot_running = '1'";
            $con_check_report_sms = $obj_con_cron->query($sql_report_sms);
            $check_report_sms = $con_check_report_sms->num_rows;


            if($check_report_sms == 0){

                //Insert report sms
                $sql_insert_report_sms = "INSERT INTO `report_smses` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`type_deposit_withdraw`) VALUES (NULL, '".$scb['id']."','".$payment_gateway."','".$balance."', current_timestamp(),'1','".$v['date']."','".$v['time']."','D')";
                $obj_con_cron->query($sql_insert_report_sms);
                $report_sms_id = $obj_con_cron->insert_id;

                //Insert report
                $sql_insert_report = "INSERT INTO `reports` (`id`, `config_api_id`, `payment_gateway`,`amount`,`created_at`,`is_bot_running`,`create_date`,`create_time`,`sms_statement_refer_id`,`type_deposit_withdraw`) VALUES (NULL, '".$scb['id']."','".$payment_gateway."','".$balance."', current_timestamp(),'1','".$v['date']."','".$v['time']."','".$report_sms_id."','D')";
                $obj_con_cron->query($sql_insert_report);
                $report_id = $obj_con_cron->insert_id;

                //Update sms_statement_refer_id on report sms
                $sql_update_report_sms = "UPDATE `report_smses` SET `sms_statement_refer_id` = '".$report_id."' WHERE `report_smses`.`id` = ".$report_sms_id;
                $obj_con_cron->query($sql_update_report_sms);
                /****************************************/

                    $sql_acc_check = "SELECT * FROM `account` where deleted = '0' and amount_promptpay = '".$balance."' ORDER BY active_deposit_date DESC";
                    $con_acc_check = $obj_con_cron->query($sql_acc_check);
                    $check_acc = $con_acc_check->num_rows;
                    if($check_acc == 1){
                    $check_add_once = false;
                    while($rs_acc = $con_acc_check->fetch_assoc()) {
                        if(!$check_add_once) {
                        $check_add_once = true;
                        $sql ="INSERT INTO `transaction` (`id`, `date_bank`, `amount`,`account`,`type`,`bank_number`,`updated_at`) VALUES (NULL, '".$v['date']." ".$v['time']."', '".$balance."', '".$rs_acc['id']."', '1', '".$rs_acc['bank_number']."', current_timestamp())";
                        $obj_con_cron->query($sql);
                        $credit_before = $rs_acc['amount_deposit_auto'];
                        $credit_after = !is_null($rs_acc['amount_deposit_auto']) && $rs_acc['amount_deposit_auto'] !== "" ? (float)$rs_acc['amount_deposit_auto'] + $balance : $balance;
                        $obj_con_cron->query("UPDATE `account` SET `amount_deposit_auto` = '".$credit_after."', `amount_promptpay` = '0', `promptpay_time` = NULL WHERE `account`.`id` = ".$rs_acc['id']);
                        $sql_insert_credit_his ="INSERT INTO `credit_history` (`id`, `process`, `credit_before`,`credit_after`,`type`,`account`,`transaction`,`admin`,`username`) VALUES (NULL, '".$balance."', '".$credit_before."', '".$credit_after."', '1', '".$rs_acc['id']."', '1', '0', '".$rs_acc['username']."')";
                        $obj_con_cron->query($sql_insert_credit_his);
                        $credit_history_id = $obj_con_cron->insert_id;

                        //Update deposit_withdraw_id on report sms,report
                        $sql_update_report_sms = "UPDATE `report_smses` SET `deposit_withdraw_id` = '".$credit_history_id."' WHERE `report_smses`.`id` = ".$report_sms_id;
                        $obj_con_cron->query($sql_update_report_sms);
                        $sql_update_report = "UPDATE `reports` SET `deposit_withdraw_id` = '".$credit_history_id."' WHERE `reports`.`id` = ".$report_id;
                        $obj_con_cron->query($sql_update_report);

                        //Insert line notify
                        if($status_create_line_notify && !empty($token_line_notify)){
                            $message = "ยอดฝาก ".number_format($balance,2)." บาท ยูส ".$rs_acc['username']." เวลา ".$v['date']." ".$v['time']." ปรับโดย PromptPay";
                            $sql_insert_line_notify ="INSERT INTO `log_line_notify` (`id`, `type`, `message`) VALUES (NULL, '1', '".$message."')";
                            $obj_con_cron->query($sql_insert_line_notify);
                            return 'สำเร็จ';
                        }

                        echo json_encode(['status'=>true,"message"=>$v['date']." ".$v['time']." | ".$balance.' | '.$rs_acc['bank_number']]);
                        }

                    }
                    }
                // if ($obj_con_cron->query("UPDATE `account` SET `amount_deposit_auto` = '".$txnAmount."' WHERE `account`.`username` = ".)){

                // }
            }
        }else{

        }
    }
    $obj_con_cron->query("UPDATE `account` SET `amount_promptpay` = '0', `promptpay_time` = NULL WHERE `promptpay_time` < NOW() - INTERVAL 10 MINUTE");
?>
