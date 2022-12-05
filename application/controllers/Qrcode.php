<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'../vendor/autoload.php';
require_once APPPATH.'../lib/Scb.php';

class Qrcode extends CI_Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Bangkok');
        parent::__construct();
        $this->check_login();
    }

	private function check_login()
	{
		if (!isset($_SESSION['user'])) {
			session_destroy();
			redirect('auth');
			exit();
		}
		$user = $_SESSION['user'];
		if (empty($user)) {
			session_destroy();
			redirect('auth');
			exit();
		}
	}

  public function upload()
  {
    $user = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
    $image_name = $this->qrcode_image('image');
		$path = FCPATH.'slips/'.$image_name;

		$qrcode = new \Zxing\QrReader($path);
		$text = $qrcode->text(); //return decoded text from QR Code

    $bank = $this->Bank_model->bank_find(['status'=>2,'status_withdraw' => 0,'bank_code_list_not_in'=>["10"]]);
    $api_token_1 = is_null($bank['api_token_1']) ? $bank['api_token_1'] : decrypt(base64_decode($bank['api_token_1']),$this->config->item('secret_key_salt'));
		$api_token_2 = is_null($bank['api_token_2']) ? $bank['api_token_2'] : decrypt(base64_decode($bank['api_token_2']),$this->config->item('secret_key_salt'));

		$api = new scb($api_token_1,$api_token_2,$bank['bank_number']);
		$scan = $api->qr_scan($text);
    unlink($path); //remove slip

    if (isset($scan['status'])) {
      if ($scan['status']['code']==1000) {

        $bank_logo = $scan['data']['pullSlip']['sender']['bankLogo']; // result -> /transfer/bank-logo/xxx.png
		    $bank_logo = substr($bank_logo,20,-4); // result -> xxx.png

        switch ($bank_logo) {
          case '006': //ktb
              $my_bank = substr($bank['bank_number'],6);
              $user_bank = substr($user['bank_number'],6);
              $receiver = $scan['data']['pullSlip']['receiver']['accountNumber'];
              $receiver = str_replace("X","",$receiver);
              $receiver = str_replace("-","",$receiver);
              $sender = $scan['data']['pullSlip']['sender']['accountNumber'];
              $sender = str_replace("X","",$sender);
              $sender = str_replace("-","",$sender);
            break;
          case '004': //kbank
              $my_bank = substr($bank['bank_number'],5,-1);
              $user_bank = substr($user['bank_number'],5,-1);
              $receiver = $scan['data']['pullSlip']['receiver']['accountNumber'];
              $receiver = str_replace("x","",$receiver);
              $receiver = str_replace("-","",$receiver);
              $sender = $scan['data']['pullSlip']['sender']['accountNumber'];
              $sender = str_replace("x","",$sender);
              $sender = str_replace("-","",$sender);
            break;
          case '011': //tmb
              $my_bank = $bank['bank_number'];
              $user_bank = substr($user['bank_number'],4,-1);
              $receiver = $scan['data']['pullSlip']['receiver']['accountNumber'];
              $receiver = str_replace("-","",$receiver);
              $sender = $scan['data']['pullSlip']['sender']['accountNumber'];
              $sender = str_replace("x","",$sender);
              $sender = str_replace("-","",$sender);
            break;
          case '014': //scb
              $my_bank = substr($bank['bank_number'],6);
              $user_bank = substr($user['bank_number'],6);
              $receiver = $scan['data']['pullSlip']['receiver']['accountNumber'];
              $receiver = str_replace("x","",$receiver);
              $receiver = str_replace("-","",$receiver);
              $sender = $scan['data']['pullSlip']['sender']['accountNumber'];
              $sender = str_replace("x","",$sender);
              $sender = str_replace("-","",$sender);
            break;
          case '030': //gsb
              $my_bank = substr($bank['bank_number'],6);
              $user_bank = substr($user['bank_number'],8);
              $receiver = $scan['data']['pullSlip']['receiver']['accountNumber'];
              $receiver = substr($receiver,6);
              $sender = $scan['data']['pullSlip']['sender']['accountNumber'];
              $sender = substr($sender,8);
            break;
          case '065': //tbank
                $my_bank = substr($bank['bank_number'],3,-1);
                $user_bank = substr($user['bank_number'],3,-1);
                $receiver = $scan['data']['pullSlip']['receiver']['accountNumber'];
                $receiver = str_replace("x","",$receiver);
                $receiver = str_replace("-","",$receiver);
                $sender = $scan['data']['pullSlip']['sender']['accountNumber'];
                $sender = str_replace("x","",$sender);
                $sender = str_replace("-","",$sender);
              break;
            case '002': //bbl
                $my_bank1 = substr($bank['bank_number'],0,4);
                $my_bank2 = substr($bank['bank_number'],7);
                $my_bank = $my_bank1.''.$my_bank2;
                $user_bank1 = substr($user['bank_number'],0,4);
                $user_bank2 = substr($user['bank_number'],7);
                $user_bank = $user_bank1.''.$user_bank2;
                $receiver = $scan['data']['pullSlip']['receiver']['accountNumber'];
                $receiver = str_replace("x","",$receiver);
                $receiver = str_replace("-","",$receiver);
                $sender = $scan['data']['pullSlip']['sender']['accountNumber'];
                $sender = str_replace("x","",$sender);
                $sender = str_replace("-","",$sender);
              break;
            case '025': //bay
	 		         $my_bank = $bank['bank_number'];
	 		         $user_bank = substr($user['bank_number'],3,-1);
	 		         $receiver = $scan['data']['pullSlip']['receiver']['accountNumber'];
	 		         $sender = $scan['data']['pullSlip']['sender']['accountNumber'];
	 		         $sender = str_replace("X","",$sender);
	 		         $sender = str_replace("-","",$sender);
	 		        break;
          default:
            // code...
            break;
        }
        if ($receiver==$my_bank) {
          $transref = $scan['data']['pullSlip']['transRef'];
          $find_transref = $this->Transaction_model->transaction_find(['transref' => $transref]);
          if ($find_transref=='') {
            $amount = $scan['data']['amount'];
            $date_bank = $scan['data']['pullSlip']['dateTime'];
            $date_bank = substr($date_bank,0,-9);
            $date_bank = str_replace("T"," ",$date_bank);
            if ($sender==$user_bank) {
              $transaction = array(
                'account' => $user['id'],
                'bank_number' => $user['bank_number'],
                'amount' => $amount,
                'type' => 1,
                'date_bank' => $date_bank,
                'transref' => $transref
              );
              $insert_id = $this->Transaction_model->transaction_create($transaction);
              if ($insert_id) {
                $credit_before = $user['amount_deposit_auto'];
                $amount_update = ($user['amount_deposit_auto'] + $amount);
                $this->Account_model->account_update(['id' => $user[id], 'amount_deposit_auto'=> $amount_update]);
                // Create credit history
                $credit_history = array(
                  'account' => $user['id'],
                  'process' => $amount,
                  'credit_before' => $credit_before,
                  'credit_after' => $amount_update,
                  'type' => 1,
                  'slip' => 1,
                  'transaction' => 1,
                  'admin' => 0
                );
                $this->db->insert('credit_history',$credit_history);

                echo json_encode([
                  'message' => 'success',
                  'result' => true
                ]);
                exit();
              }
            } else {
              echo json_encode([
              'message' => "เลขบัญชีผู้ใช้ไม่ตรงกัน",
              'error' => true
              ]);
              exit();
            }
          } else {
            echo json_encode([
            'message' => "สลิปนี้ถูกใช้งานแล้ว",
            'error' => true
            ]);
            exit();
          }
        } else {
          echo json_encode([
          'message' => "บัญชีผู้รับไม่ถูกต้อง",
          'error' => true
          ]);
          exit();
        }

      } else {
        echo json_encode([
        'message' => "Token Error",
        'error' => true
        ]);
        exit();
      }
    } else {
      echo json_encode([
      'message' => "เชื่อมต่อธนาคารล้มเหลว ลองอีกครั้ง",
      'error' => true
      ]);
      exit();
    }

  }

  public function qrcode_image($name)
  {
      $type_file = pathinfo($_FILES[$name]["name"], PATHINFO_EXTENSION);
      $random_string = random_string('alnum', 5);
      $rename = "slip".date('YmdHis').'_'.$random_string.".".$type_file;
      $config['upload_path']          = 'slips/';
      $config['allowed_types']        = 'jpg|png|jpeg';
      // $config['max_size']             = 60000;
      // $config['max_width']            = 4000;
      // $config['max_height']           = 4000;
      $config['file_name']           = $rename;
      //resize
      $config['image_library'] = 'gd2';
      $config['source_image'] = $config['upload_path'].$rename;
      // $config['create_thumb'] = TRUE;
      // $config['maintain_ratio'] = false;
      // $config['width']     = 700;
      // $config['height']   = 200;
      // $this->upload->clear();
      if ($_FILES[$name]['error']==0) {
          $this->upload->initialize($config);
          $this->load->library('upload', $config);
          $this->upload->do_upload($name);
          $this->image_lib->clear();
          $this->image_lib->initialize($config);
          $this->load->library('image_lib', $config);
          $this->image_lib->resize();
          return $rename;
      }
  }
}
