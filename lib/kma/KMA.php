<?php
error_reporting(0);
use Banking\KMA\Client;
class KMA {

	private $_DEVICE_INFO = null;
	private $_DEVICE_PIN = null;
	private $_DEVICE_ACC_NUM = null;

	public function __construct($acc_num,$deviceInfo,$pin){
		if(empty($deviceInfo)){
			echo "Data Device Info empty! : ".$acc_num;
		}else if(empty($pin)){
			echo "Data Pin empty! : ".$acc_num;
		}else if(strlen($pin) > 6 || strlen($pin) < 6){
			echo "pin should have 6 digits!! : ".$acc_num;
		}
		require __DIR__ . '/vendor/autoload.php';
		$this->_DEVICE_INFO = json_decode($deviceInfo,true);
		$this->_DEVICE_PIN = $pin;
		$this->_DEVICE_ACC_NUM = $acc_num;
	}

	public function transactions(){
		$client = new Client($this->_DEVICE_INFO);
		$login = $client->login($this->_DEVICE_PIN);
		$listAccount = $client->listAccountByCustIDNew();
		$listStatement = $client->listStatement($listAccount['Account'][0]['AccNo']);
		return $listStatement;
	}

    public function balanceAndTransactions(){
        $client = new Client($this->_DEVICE_INFO);
        $login = $client->login($this->_DEVICE_PIN);
        $results = [
            'balance' => null,
            'transactions' => [],
        ];
        $listAccount = $client->listAccountByCustIDNew();
        if(!empty($listAccount['Account'])){
            $results['balance'] = $listAccount['Account'][0]['AvailBal'];
            $listStatement = $client->listStatement($listAccount['Account'][0]['AccNo']);
            if(!empty($listStatement['Statement'])){
                $results['transactions'] = $listStatement['Statement'];
            }
        }
        return $results;
    }

	public function verification($accountTo,$accountToBankCode){
		$client = new Client($this->_DEVICE_INFO);
		$login = $client->login($this->_DEVICE_PIN);
		if ($accountToBankCode=='025') {
			$listAccount = $client->listAccountByCustIDNew();
			$preTransfer = $client->preTransfer([
				'transactiontype' => '2',
				'fraccno' => $listAccount['Account'][0]['AccNo'],
				'tobankcode' => $accountToBankCode, // รหัสธนาคาร
				'toaccno' => $accountTo, // เลข บช
				'amount' => 1,
				'fraccid' => $listAccount['Account'][0]['AccID'],
			]);
			$confirmTransfer = $client->confirmTransfer();
			//$fundTransfer = $client->fundTransfer('', '', $confirmTransfer['RefNo']);
			return $confirmTransfer;
		}else{
			$client = new Client($this->_DEVICE_INFO);
			$login = $client->login($this->_DEVICE_PIN);
			$listAccount = $client->listAccountByCustIDNew();
			$preTransfer = $client->preTransfer([
				'fraccno' => $listAccount['Account'][0]['AccNo'],
				'tobankcode' => $accountToBankCode, // รหัสธนาคาร
				'toaccno' =>  $accountTo, // เลข บช
				'amount' =>1,
				'fraccid' => $listAccount['Account'][0]['AccID'],
			]);
			$confirmTransfer = $client->confirmTransfer();
			//$fundTransfer = $client->fundTransfer('', '', $confirmTransfer['RefNo']);
            return $confirmTransfer;
		}
	}



	public function transfer($accountTo,$accountToBankCode,$amount){
		$client = new Client($this->_DEVICE_INFO);
		$login = $client->login($this->_DEVICE_PIN);
		if ($accountToBankCode=='025') {
			$listAccount = $client->listAccountByCustIDNew();
			$preTransfer = $client->preTransfer([
				'transactiontype' => '2',
				'fraccno' => $listAccount['Account'][0]['AccNo'],
                'tobankcode' => $accountToBankCode, // รหัสธนาคาร
                'toaccno' => $accountTo, // เลข บช
                'amount' => $amount,
                'fraccid' => $listAccount['Account'][0]['AccID'],
            ]);
			$confirmTransfer = $client->confirmTransfer();
            if(!is_null($confirmTransfer) && !empty($confirmTransfer) && is_null($confirmTransfer['ToAccNameTH'])){
                $confirmTransfer['ErrorMessage'] = "เลขบัญชี/ธนาคารบัญชีไม่ถูกต้อง";
                return $confirmTransfer;
            }
			$fundTransfer = $client->fundTransfer('', '', $confirmTransfer['RefNo']);
			return $fundTransfer;
		}else{
			$client = new Client($this->_DEVICE_INFO);
			$login = $client->login($this->_DEVICE_PIN);
			$listAccount = $client->listAccountByCustIDNew();
			$preTransfer = $client->preTransfer([
				'fraccno' => $listAccount['Account'][0]['AccNo'],
                'tobankcode' => $accountToBankCode, // รหัสธนาคาร
                'toaccno' =>  $accountTo, // เลข บช
                'amount' =>$amount,
                'fraccid' => $listAccount['Account'][0]['AccID'],
            ]);
			$confirmTransfer = $client->confirmTransfer();
            if(!is_null($confirmTransfer) && !empty($confirmTransfer) && is_null($confirmTransfer['ToAccNameTH'])){
                $confirmTransfer['ErrorMessage'] = "เลขบัญชี/ธนาคารบัญชีไม่ถูกต้อง";
                return $confirmTransfer;
            }
			$fundTransfer = $client->fundTransfer('', '', $confirmTransfer['RefNo']);
            return $fundTransfer;
		}
	}
}




