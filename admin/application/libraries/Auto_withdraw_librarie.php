<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'../vendor/autoload.php';

class Auto_withdraw_librarie
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Bangkok');
    }

	public function index()
	{

	}
	public function transfer($username = null,$accnum = null,$code = null,$money=null,$deviceid,$api_refresh,$bank_number,$annotaion ='')
	{
		//require_once FCPATH .'../config.php';
		require_once FCPATH .'../lib/Scb.php';

		if(!is_null($username) && !is_null($money) && !is_null($accnum) && !is_null($code)){
			if (is_numeric($money)) {
				try{
					if($annotaion !=''){
						$personal_msg =$annotaion;
					}else{
						$personal_msg ="";
					}
					$api = new scb($deviceid, $api_refresh, $bank_number); //$deviceId,$api_refresh,$accnum
					$res = $api->Transfer($accnum, $code, $money,$annotaion);
					$json = json_decode($res, true);
					if ($json['status']['code'] == 1000) {
						return ['status' => true, 'msg' => $json];
					}elseif ($json['status'] == 0){
						return ['status' => false, 'msg' => $json['msg']];
					} else {
						return ['status' => false, 'msg' => $json['status']['description']];
					}
				}catch (Exception $ex){
					return ['status'=>false,"msg"=>"เกิดข้อผิดพลาดจาก API Error : ".$ex->getMessage().", กรุณาตรวจสอบยอดถอนบน Internet Banking/Mobile App ว่าถูกถอนไปจริงหรือไม่"];
				}
			} else {
				return ['status' => false, 'msg' => 'ข้อมูลไม่ถูกต้อง#2'];
			}
		}else{
			return ['status' => false, 'msg' => 'ข้อมูลไม่ถูกต้อง#1'];
		}
	}
	public function transfer_kplus($username = null,$accnum = null,$code = null,$money=null,$token,$pin,$bank_number)
	{
		require_once FCPATH .'../lib/Kplus.php';
		if(!is_null($username) && !is_null($money) && !is_null($accnum) && !is_null($code)){
			if (is_numeric($money)) {
				try{
					$api = new Kplus($bank_number,$token,$pin);
					$verifyAccount = $api->transferVerify($code, $accnum, $money);
					if(!empty($verifyAccount) && isset($verifyAccount['kbankInternalSessionId'])){
						$transferConfirm = $api->transferConfrim($verifyAccount['kbankInternalSessionId']);
						if(!empty($transferConfirm) && ((isset($transferConfirm['transactionReference']) && !empty($transferConfirm['transactionReference'])) || (isset($transferConfirm['rawQr']) && !empty($transferConfirm['rawQr'])))){
							return ['status' => true, 'msg' =>$transferConfirm];
						}else{
							$message = isset($transferConfirm['errors']) && !empty($transferConfirm['errors'][0]) && isset($transferConfirm['errors'][0]['msg'])  && isset($transferConfirm['errors'][0]['value']) ? $transferConfirm['errors'][0]['msg'].' - '.$transferConfirm['errors'][0]['value'] : "ไม่พบข้อมูล#1 หลังจากนี้ 5 - 10 นาที, กรุณาตรวจสอบยอดถอนบน Internet Banking เท่านั้นว่าถูกถอนไปจริงหรือไม่ **หากยังให้ทำการถอนใหม่อีคกรั้ง";
							return ['status' => false, 'msg' => $message];
						}
					}else{
						$message = isset($verifyAccount['error']) && !empty($verifyAccount['error']) ? $verifyAccount['error'] : "ระบบไม่สามารถทำรายการได้ในขณะนี้ กรุณาทำรายการใหม่อีกครั้งในภายหลัง#1";
						return ['status' => false, 'msg' => $message];
					}
				}catch (Exception $ex){
					return ['status'=>false,"msg"=>"เกิดข้อผิดพลาดจาก API Error : ".$ex->getMessage().", กรุณาตรวจสอบยอดถอนบน Internet Banking/Mobile App ว่าถูกถอนไปจริงหรือไม่"];
				}
			}else{
				return ['status' => false, 'msg' => 'ข้อมูลไม่ถูกต้อง#2'];
			}

		}else{
			return ['status' => false, 'msg' => 'ข้อมูลไม่ถูกต้อง#1'];
		}
	}
	public function transfer_kma($username = null,$accnum = null,$code = null,$money=null,$token,$pin,$bank_number)
	{
		require_once FCPATH .'../lib/kma/KMA.php';
		if(!is_null($username) && !is_null($money) && !is_null($accnum) && !is_null($code)){
			if (is_numeric($money)) {
				try{
					$api = new KMA($bank_number,$token,$pin);
					$transfer_result = $api->transfer($accnum,$code,$money);
					if(
						!is_null($transfer_result) && !empty($transfer_result)
						&& isset($transfer_result['QRCimagevalue']) && !is_null($transfer_result['QRCimagevalue'])
						&& isset($transfer_result['ToAccNameTH']) && !is_null($transfer_result['ToAccNameTH'])
						&& isset($transfer_result['ToAccNo']) && $transfer_result['ToAccNo'] == $accnum
						&& isset($transfer_result['ErrorCode']) && $transfer_result['ErrorCode'] == "0000"
					){
						return ['status' => true, 'msg' =>$transfer_result];
					}else{
						$message = isset($transfer_result['ErrorMessage']) ? $transfer_result['ErrorMessage'] : "ไม่พบข้อมูล#1 หลังจากนี้ 2 - 5 นาที, กรุณาตรวจสอบยอดถอนบน Internet Banking เท่านั้นว่าถูกถอนไปจริงหรือไม่ **หากยังให้ทำการถอนใหม่อีคกรั้ง";
						return ['status' => false, 'msg' => $message];
					}
				}catch (Exception $ex){
					return ['status'=>false,"msg"=>"เกิดข้อผิดพลาดจาก API, กรุณาตรวจสอบยอดถอนบน Internet Banking/Mobile App ว่าถูกถอนไปจริงหรือไม่"];
				}
			}else{
				return ['status' => false, 'msg' => 'ข้อมูลไม่ถูกต้อง#2'];
			}

		}else{
			return ['status' => false, 'msg' => 'ข้อมูลไม่ถูกต้อง#1'];
		}
	}
	public function transfer_kkp($username = null,$accnum = null,$code = null,$money=null,$idCard,$pin,$bank_number)
	{
		//require_once FCPATH .'../config.php';
		require_once FCPATH .'../lib/kkp/KkpClass.php';
		if(!is_null($username) && !is_null($money) && !is_null($accnum) && !is_null($code)){
			if (is_numeric($money)) {
				try{
					$data = array(
						"idCard" => trim($idCard),
						"pin" => trim($pin),
					);

					$api = new KkpClass($data); //$deviceId,$api_refresh,$accnum
					$res_sum = $api->summary();
					//print_r($res_sum);
					$res = $api->verifyTransfer($accnum, $code, $money);

					$res = $api->ConfirmTransfer($res);

					if ($res['result']['responseStatus']['httpStatus'] == '') {
						return ['status' => true, 'msg' => $res['result']['value'],'balance'=>$res_sum['result']['value'][0]['myAvailableBalance']];
					}elseif ($res['result']['responseStatus']['httpStatusCode'] == '410'){
						return ['status' => false, 'msg' => $res['result']['responseStatus']['responseMessage']];
					} else {
						return ['status' => false, 'msg' => $res['result']['responseStatus']['responseMessage']];
					}
				}catch (Exception $ex){
					return ['status'=>false,"msg"=>"เกิดข้อผิดพลาดจาก API Error : ".$ex->getMessage().", กรุณาตรวจสอบยอดถอนบน Internet Banking/Mobile App ว่าถูกถอนไปจริงหรือไม่"];
				}
			} else {
				return ['status' => false, 'msg' => 'ข้อมูลไม่ถูกต้อง#2'];
			}
		}else{
			return ['status' => false, 'msg' => 'ข้อมูลไม่ถูกต้อง#1'];
		}
	}
	public function transfer_truewallet($username = null,$accnum = null,$code = null,$money=null,$api_token_1,$api_token_2,$bank_number,$tmn_key_id,$tmn_id,$annotaion="")
	{
		//require_once FCPATH .'../config.php';
		require_once FCPATH . '../lib/TMNOoo.php';
		if(!is_null($username) && !is_null($money) && !is_null($accnum)){
			if (is_numeric($money)) {
				try{
					$_TMN = array();
					$_TMN['tmn_key_id'] = $tmn_key_id; //Key ID จากระบบ TMNOne
					$_TMN['mobile_number'] = $bank_number; //เบอร์ Wallet
					$_TMN['login_token'] = $api_token_2; //login_token จากขั้นตอนการเพิ่มเบอร์ Wallet
					$_TMN['pin'] = $api_token_1; //อย่าลืมใส่ PIN 6 หลักของ Wallet
					$_TMN['tmn_id'] = $tmn_id; //tmn_id จากขั้นตอนการเพิ่มเบอร์ Wallet

					if($annotaion !=''){
						$personal_msg =$annotaion;
					}else{
						$personal_msg ="";
					}

					//print_r($_TMN);
					$TMNOoo = new TMNOoo();
					//$TMNOoo->setProxy('zproxy.lum-superproxy.io:22225', 'brd-customer-hl_ebdb3c0e-zone-data_center-country-th', '0pi1xakwwrg5'); //เปิดใช้งาน HTTP Proxy สำหรับเชื่อมต่อกับระบบของ Wallet
					//$TMNOoo->Login();
					$TMNOoo->setData($_TMN['tmn_key_id'], $_TMN['mobile_number'], $_TMN['login_token'], $_TMN['tmn_id']);
					$TMNOoo->loginWithPin6($_TMN['pin']);
					$res = $TMNOoo->ConfirmTransferP2P($accnum,$money,$personal_msg);
					//print_r($res);
					//die();
					$res['transferAmount'] = $money;
					$json = $res;
					//print_r($json);
					if ($json['code'] == 'TRC-200') {
						return ['status' => true, 'msg' => $json];
					}elseif ($json['status'] == 0){
						return ['status' => false, 'msg' => $json['msg']];
					} else {
						return ['status' => false, 'msg' => $json['status']['description']];
					}
				}catch (Exception $ex){
					return ['status'=>false,"msg"=>"เกิดข้อผิดพลาดจาก API Error : ".$ex->getMessage().", กรุณาตรวจสอบยอดถอนบน Internet Banking/Mobile App ว่าถูกถอนไปจริงหรือไม่"];
				}
			} else {
				return ['status' => false, 'msg' => 'ข้อมูลไม่ถูกต้อง#2'];
			}
		}else{
			return ['status' => false, 'msg' => 'ข้อมูลไม่ถูกต้อง#1'];
		}
	}

	public function send_line_message($data,$token){
		include_once FCPATH .'../lib/send_line_message.php';
		//echo 'sendline_deposit';
		$send_line = new send_line_message();
		$send_line->sendline_deposit($data,$token);
	}
}
