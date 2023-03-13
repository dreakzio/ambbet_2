<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'../vendor/autoload.php';
use \Curl\Curl;
use Rct567\DomQuery\DomQuery;

class Deposit extends CI_Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Bangkok');
        parent::__construct();
		$language = $this->session->userdata('language');
		$this->lang->load('text', $language);
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

    public function index()
    {
        $data['user'] = $this->Account_model->account_find([
          'id' => $_SESSION['user']['id']
        ]);
		//print_r($data['user']);
		$this->Account_model->account_update([
			'id' => $_SESSION['user']['id'],
			'active_deposit_date' => date('Y-m-d H:i:s')
		]);
        $promotion_data = [];
        $promotion = $this->Promotion_model->promotion_list([
          'status' => 1
        ]);
        foreach ($promotion as $key => $value) {
            switch ($value['type']) {
            case '1':
            $use_promotion = $this->Use_promotion_model->use_promotion_count([
              'account' => $_SESSION['user']['id'],
              'promotion' => $value['id']
            ]);
            if ($use_promotion<$value['max_use']) {
                $value['remaining'] = ($value['max_use']-$use_promotion);
                $promotion_data[] = $value;
            }
            break;
            case '2':
            $use_promotion = $this->Use_promotion_model->use_promotion_count([
              'account' => $_SESSION['user']['id'],
              'promotion' => $value['id'],
              'date_from' =>  date('Y-m-d'),
              'date_to' =>  date('Y-m-d'),
            ]);
            if ($use_promotion<$value['max_use']) {
                $value['remaining'] = ($value['max_use']-$use_promotion);
                $promotion_data[] = $value;
            }
            break;
            case '3':
				date_default_timezone_set('Asia/Bangkok');
				$start_date_pro = new DateTime();
				$start_date_pro->modify('Monday this week');
				$end_date_pro = new DateTime();
				$end_date_pro->modify('Sunday this week');
            $use_promotion = $this->Use_promotion_model->use_promotion_count([
              'account' => $_SESSION['user']['id'],
              'promotion' => $value['id'],
				'date_from' =>  $start_date_pro->format('Y-m-d'),
				'date_to' =>  $end_date_pro->format('Y-m-d'),
            ]);
            if ($use_promotion<$value['max_use']) {
                $value['remaining'] = ($value['max_use']-$use_promotion);
                $promotion_data[] = $value;
            }
            break;
            case '4':
				date_default_timezone_set('Asia/Bangkok');
				$start_date_pro = new DateTime();
				$start_date_pro->modify('first day of this month');
				$end_date_pro = new DateTime();
				$end_date_pro->modify('last day of this month');
            $use_promotion = $this->Use_promotion_model->use_promotion_count([
              'account' => $_SESSION['user']['id'],
              'promotion' => $value['id'],
				'date_from' =>  $start_date_pro->format('Y-m-d'),
				'date_to' =>  $end_date_pro->format('Y-m-d'),
            ]);
            if ($use_promotion<$value['max_use']) {
                $value['remaining'] = ($value['max_use']-$use_promotion);
                $promotion_data[] = $value;
            }
            break;
            case '5':
            $current_time = date('Y-m-d H:i:s');
            $start_time = date('Y-m-d H:i:s',strtotime("today {$value['start_time']}"));
            $end_time = date('Y-m-d H:i:s',strtotime("today {$value['end_time']}"));
            if ($current_time >= $start_time && $current_time <= $end_time) {
              $use_promotion = $this->Use_promotion_model->use_promotion_count([
                'account' => $_SESSION['user']['id'],
                'promotion' => $value['id'],
                'start_time' =>  $start_time,
                'end_time' =>  $end_time,
              ]);
              if ($use_promotion<$value['max_use']) {
                  $value['remaining'] = ($value['max_use']-$use_promotion);
                  $promotion_data[] = $value;
              }
            }
            break;
            case '6':
            $days_deposit = $value['number_of_deposit_days'];
            $dataLastday = $this->getLastNDays($days_deposit);
            $finance = $this->Finance_model->finance_find_created_at([
              'account' => $_SESSION['user']['id'],
              'limit'=> $days_deposit
            ]);
            $result_intersect = array_intersect($dataLastday,$finance);
            $countDay = count($result_intersect);
            if ($countDay >= $days_deposit) {
              $use_promotion = $this->Use_promotion_model->use_promotion_count([
                'account' => $_SESSION['user']['id'],
                'promotion' => $value['id']
              ]);
              if ($use_promotion<$value['max_use']) {
                  $value['remaining'] = ($value['max_use']-$use_promotion);
                  $promotion_data[] = $value;
              }
            }
            break;
            // no break
            default:
              // code...
              break;
          }
        }
        $data['promotion'] = $promotion_data;
		$finance_current = $this->Finance_model->finance_list([
			'account' => $_SESSION['user']['id'],
			'type' => 1,
			'limit' => 1
		]);
		$promotion_active = null;
		if(!empty($finance_current) && is_array($finance_current) && count($finance_current) > 0){
			$finance_current = $finance_current[0]['id'];
			$promotion_active = $this->Use_promotion_model->promotion_list([
				'finance' => $finance_current,
				'limit' => 1
			]);
			if(!empty($promotion_active) && is_array($promotion_active) && count($promotion_active) > 0){
				$promotion_active = $promotion_active[0]['promotion'];
			}else{
				$promotion_active = null;
			}
		}
		if(!is_null($promotion_active)){
			$chk_promotion_active_exist = false;
			foreach($promotion_data as $promotion){
				if($promotion['id'] == $promotion_active){
					$chk_promotion_active_exist = $promotion['id'];
					break;
				}
			}
			$promotion_active = !$chk_promotion_active_exist ? null : $chk_promotion_active_exist;
		}
		$auto_create_member = $this->Setting_model->setting_find([
			'name' => 'auto_create_member'
		]);
		$data['auto_create_member'] = $auto_create_member;
		$data['promotion_active'] = $promotion_active;
		// ----- Start Bank Section --------
		$data['bank'] = "";
		$data['bank_all'] = null;
        $data['bank_all'] = $this->Bank_model->bank_list([
			'status'=>1,
			'status_withdraw' => 0,
			'bank_code_list_not_in'=>["null","10"]
        ]);
		if(in_array($data['user']['bank'],["02","2"])){
			//$data['bank'] = $this->Bank_model->bank_find(['status'=>1,'status_withdraw' => 0,'bank_code_list'=>["02","2"]]);
			$data['bank'] = $this->Bank_model->bank_find(['status'=>1,'status_withdraw' => 0,'bank_code_list_not_in'=>["null","10"]]);
		}
		if($data['bank'] == ""){
			/*$data['bank'] = $this->Bank_model->bank_find(['status'=>1,'status_withdraw' => 0,'bank_code_list_not_in'=>["02","2","10"]]);
			if($data['bank'] == ""){
				$data['bank'] = $this->Bank_model->bank_find(['status'=>1,'status_withdraw' => 0,'bank_code_list_not_in'=>["null","10"]]);
			}*/
			$data['bank'] = $this->Bank_model->bank_find(['status'=>1,'status_withdraw' => 0,'bank_code_list_not_in'=>["null","10"]]);
		}

		$data['bank_truewallet'] = $this->Bank_model->bank_find(['status'=>1,'status_withdraw' => 0,'bank_code_list'=>["10"]]);
		$bank_chk_true_wallet = $data['bank_truewallet'];
		if(
			$bank_chk_true_wallet != "" &&
			isset($bank_chk_true_wallet['start_time_can_not_deposit'])
			&& !is_null($bank_chk_true_wallet['start_time_can_not_deposit'])
			&& !empty($bank_chk_true_wallet['start_time_can_not_deposit'])
			&& isset($bank_chk_true_wallet['end_time_can_not_deposit'])
			&& !is_null($bank_chk_true_wallet['end_time_can_not_deposit'])
			&& !empty($bank_chk_true_wallet['end_time_can_not_deposit'])
		){
			try{
				$start_time_can_not_deposit = new DateTime($bank_chk_true_wallet['start_time_can_not_deposit']);
				$end_time_can_not_deposit = new DateTime($bank_chk_true_wallet['end_time_can_not_deposit']);
				$time_current = new DateTime(date('H:i'));

				if( in_array($start_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12"))){
					if(!in_array($end_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
						!in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
					){
						$end_time_can_not_deposit = $end_time_can_not_deposit->add(new DateInterval('P1D'));
					}
					if(
						!in_array($time_current->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
						in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
					){
						$start_time_can_not_deposit = $start_time_can_not_deposit->sub(new DateInterval('P1D'));
					}
					if($start_time_can_not_deposit->getTimestamp() > $end_time_can_not_deposit->getTimestamp()){
						$data['bank_truewallet'] = "";
					}else if(
						$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
						$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
					){
						if($start_time_can_not_deposit->format("H") == "23" &&  $end_time_can_not_deposit->format("H") == "23" &&
							in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
						){

						}else{
							$data['bank_truewallet'] = "";
						}

					}else{

					}
				}else if(
					$start_time_can_not_deposit->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
				){
					if(
						$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
						$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
					){
						$data['bank_truewallet'] = "";
					}else{

					}
				}
			}catch (Exception $ex){
				$data['bank_truewallet'] = "";
			}
		}

		if($data['bank_truewallet'] == ""){
			$bank_list = $this->Bank_model->bank_list(['status'=>1,'status_withdraw' => 0,'bank_code_list'=>["10"]]);
			foreach($bank_list as $bank_chk){
				$chk = false;
				if(
					isset($bank_chk['start_time_can_not_deposit'])
					&& !is_null($bank_chk['start_time_can_not_deposit'])
					&& !empty($bank_chk['start_time_can_not_deposit'])
					&& isset($bank_chk['end_time_can_not_deposit'])
					&& !is_null($bank_chk['end_time_can_not_deposit'])
					&& !empty($bank_chk['end_time_can_not_deposit'])
				){
					try{
						$start_time_can_not_deposit = new DateTime($bank_chk['start_time_can_not_deposit']);
						$end_time_can_not_deposit = new DateTime($bank_chk['end_time_can_not_deposit']);
						$time_current = new DateTime(date('H:i'));

						if( in_array($start_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12"))){
							if(!in_array($end_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
								!in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
							){
								$end_time_can_not_deposit = $end_time_can_not_deposit->add(new DateInterval('P1D'));
							}
							if(
								!in_array($time_current->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
								in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
							){
								$start_time_can_not_deposit = $start_time_can_not_deposit->sub(new DateInterval('P1D'));
							}
							if($start_time_can_not_deposit->getTimestamp() > $end_time_can_not_deposit->getTimestamp()){

							}else if(
								$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
								$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
							){
								if($start_time_can_not_deposit->format("H") == "23" &&  $end_time_can_not_deposit->format("H") == "23" &&
									in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
								){
									$chk = true;
								}else{

								}
							}else{
								$chk = true;
							}
						}else if(
							$start_time_can_not_deposit->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
						){
							if(
								$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
								$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
							){

							}else{
								$chk = true;
							}
						}
					}catch (Exception $ex){

					}
				}else{
					$chk = true;
				}
				if($chk){
					$data['bank_truewallet'] = $bank_chk;
					break;
				}
			}
		}

		$bank_chk = $data['bank'];
		if(
			$bank_chk != "" &&
			isset($bank_chk['start_time_can_not_deposit'])
			&& !is_null($bank_chk['start_time_can_not_deposit'])
			&& !empty($bank_chk['start_time_can_not_deposit'])
			&& isset($bank_chk['end_time_can_not_deposit'])
			&& !is_null($bank_chk['end_time_can_not_deposit'])
			&& !empty($bank_chk['end_time_can_not_deposit'])
		){
			try{
				$start_time_can_not_deposit = new DateTime($bank_chk['start_time_can_not_deposit']);
				$end_time_can_not_deposit = new DateTime($bank_chk['end_time_can_not_deposit']);
				$time_current = new DateTime(date('H:i'));

				if( in_array($start_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12"))){
					if(!in_array($end_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
						!in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
					){
						$end_time_can_not_deposit = $end_time_can_not_deposit->add(new DateInterval('P1D'));
					}
					if(
						!in_array($time_current->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
						in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
					){
						$start_time_can_not_deposit = $start_time_can_not_deposit->sub(new DateInterval('P1D'));
					}
					if($start_time_can_not_deposit->getTimestamp() > $end_time_can_not_deposit->getTimestamp()){
						$data['bank'] = "";
					}else if(
						$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
						$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
					){
						if($start_time_can_not_deposit->format("H") == "23" &&  $end_time_can_not_deposit->format("H") == "23" &&
							in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
						){

						}else{
							$data['bank'] = "";
						}

					}else{

					}
				}else if(
					$start_time_can_not_deposit->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
				){
					if(
						$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
						$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
					){
						$data['bank'] = "";
					}else{

					}
				}
			}catch (Exception $ex){
				$data['bank'] = "";
			}
		}

		if($data['bank'] == ""){
			$bank_list = $this->Bank_model->bank_list(['status'=>1,'status_withdraw' => 0,'bank_code_list_not_in'=>["10"]]);
			foreach($bank_list as $bank_chk){
				$chk = false;
				if(
					isset($bank_chk['start_time_can_not_deposit'])
					&& !is_null($bank_chk['start_time_can_not_deposit'])
					&& !empty($bank_chk['start_time_can_not_deposit'])
					&& isset($bank_chk['end_time_can_not_deposit'])
					&& !is_null($bank_chk['end_time_can_not_deposit'])
					&& !empty($bank_chk['end_time_can_not_deposit'])
				){
					try{
						$start_time_can_not_deposit = new DateTime($bank_chk['start_time_can_not_deposit']);
						$end_time_can_not_deposit = new DateTime($bank_chk['end_time_can_not_deposit']);
						$time_current = new DateTime(date('H:i'));

						if( in_array($start_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12"))){
							if(!in_array($end_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
								!in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
							){
								$end_time_can_not_deposit = $end_time_can_not_deposit->add(new DateInterval('P1D'));
							}
							if(
								!in_array($time_current->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
								in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
							){
								$start_time_can_not_deposit = $start_time_can_not_deposit->sub(new DateInterval('P1D'));
							}
							if($start_time_can_not_deposit->getTimestamp() > $end_time_can_not_deposit->getTimestamp()){

							}else if(
								$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
								$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
							){
								if($start_time_can_not_deposit->format("H") == "23" &&  $end_time_can_not_deposit->format("H") == "23" &&
									in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
								){
									$chk = true;
								}else{

								}
							}else{
								$chk = true;
							}
						}else if(
							$start_time_can_not_deposit->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
						){
							if(
								$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
								$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
							){

							}else{
								$chk = true;
							}
						}
					}catch (Exception $ex){

					}
				}else{
					$chk = true;
				}
				if($chk){
					$data['bank'] = $bank_chk;
					break;
				}
			}
		}

		$bank_all_chk = $data['bank_all'];
		if(
			$bank_all_chk != "" &&
			isset($bank_all_chk['start_time_can_not_deposit'])
			&& !is_null($bank_all_chk['start_time_can_not_deposit'])
			&& !empty($bank_all_chk['start_time_can_not_deposit'])
			&& isset($bank_all_chk['end_time_can_not_deposit'])
			&& !is_null($bank_all_chk['end_time_can_not_deposit'])
			&& !empty($bank_all_chk['end_time_can_not_deposit'])
		){
			try{
				$start_time_can_not_deposit = new DateTime($bank_all_chk['start_time_can_not_deposit']);
				$end_time_can_not_deposit = new DateTime($bank_all_chk['end_time_can_not_deposit']);
				$time_current = new DateTime(date('H:i'));

				if( in_array($start_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12"))){
					if(!in_array($end_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
						!in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
					){
						$end_time_can_not_deposit = $end_time_can_not_deposit->add(new DateInterval('P1D'));
					}
					if(
						!in_array($time_current->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
						in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
					){
						$start_time_can_not_deposit = $start_time_can_not_deposit->sub(new DateInterval('P1D'));
					}
					if($start_time_can_not_deposit->getTimestamp() > $end_time_can_not_deposit->getTimestamp()){
						$data['bank_all'] = "";
					}else if(
						$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
						$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
					){
						if($start_time_can_not_deposit->format("H") == "23" &&  $end_time_can_not_deposit->format("H") == "23" &&
							in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
						){

						}else{
							$data['bank_all'] = "";
						}

					}else{

					}
				}else if(
					$start_time_can_not_deposit->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
				){
					if(
						$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
						$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
					){
						$data['bank_all'] = "";
					}else{

					}
				}
			}catch (Exception $ex){
				$data['bank_all'] = "";
			}
		}

		if($data['bank_all'] == ""){
			$bank_list = $this->Bank_model->bank_list(['status'=>1,'status_withdraw' => 0,'bank_code_list_not_in'=>["10"]]);
			foreach($bank_list as $bank_all_chk){
				$chk = false;
				if(
					isset($bank_all_chk['start_time_can_not_deposit'])
					&& !is_null($bank_all_chk['start_time_can_not_deposit'])
					&& !empty($bank_all_chk['start_time_can_not_deposit'])
					&& isset($bank_all_chk['end_time_can_not_deposit'])
					&& !is_null($bank_all_chk['end_time_can_not_deposit'])
					&& !empty($bank_all_chk['end_time_can_not_deposit'])
				){
					try{
						$start_time_can_not_deposit = new DateTime($bank_all_chk['start_time_can_not_deposit']);
						$end_time_can_not_deposit = new DateTime($bank_all_chk['end_time_can_not_deposit']);
						$time_current = new DateTime(date('H:i'));

						if( in_array($start_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12"))){
							if(!in_array($end_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
								!in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
							){
								$end_time_can_not_deposit = $end_time_can_not_deposit->add(new DateInterval('P1D'));
							}
							if(
								!in_array($time_current->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
								in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
							){
								$start_time_can_not_deposit = $start_time_can_not_deposit->sub(new DateInterval('P1D'));
							}
							if($start_time_can_not_deposit->getTimestamp() > $end_time_can_not_deposit->getTimestamp()){

							}else if(
								$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
								$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
							){
								if($start_time_can_not_deposit->format("H") == "23" &&  $end_time_can_not_deposit->format("H") == "23" &&
									in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
								){
									$chk = true;
								}else{

								}
							}else{
								$chk = true;
							}
						}else if(
							$start_time_can_not_deposit->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
						){
							if(
								$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
								$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
							){

							}else{
								$chk = true;
							}
						}
					}catch (Exception $ex){

					}
				}else{
					$chk = true;
				}
				if($chk){
					$data['bank_all'] = $bank_all_chk;
					break;
				}
			}
		}

    if($data['bank'] == ""){
		    $data['bank'] = $this->Bank_model->bank_find(['status'=>1,'status_withdraw' => 0,'bank_code_list_not_in'=>["null","10"]]);
			//$data['bank'] = $this->Bank_model->bank_find(['status'=>2,'status_withdraw' => 0,'bank_code_list_not_in'=>["02","2","10"]]);
		}
        $bank_promptpay = $this->Bank_model->promptpay();
        $data['promptpay'] = $bank_promptpay['promptpay_status'];
		$data['header_menu'] = 'header_menu';
		$data['middle_bar'] = 'middle_bar';
		$data['footer_menu'] = 'footer_menu';
		$data['back_btn'] = true;
		$data['back_url'] = base_url('dashboard');
		$data['page'] = 'deposit';
		$data['footer_menu'] = 'footer_menu';

		$this->load->view('main', $data);
    }
    public function chk_bank_can_deposit($id){ // 4 is work and return result
		if(empty($id)){
			exit();
		}
		$bank_chk = $this->Bank_model->bank_find(['status'=>1,'status_withdraw' => 0,'id'=>$id]);
		if ($bank_chk=="") {
		  $bank_chk = $this->Bank_model->bank_find(['status'=>2,'status_withdraw' => 0,'id'=>$id]);
		}
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$chk = false;
		if($bank_chk != ""){
			if(
				isset($bank_chk['start_time_can_not_deposit'])
				&& !is_null($bank_chk['start_time_can_not_deposit'])
				&& !empty($bank_chk['start_time_can_not_deposit'])
				&& isset($bank_chk['end_time_can_not_deposit'])
				&& !is_null($bank_chk['end_time_can_not_deposit'])
				&& !empty($bank_chk['end_time_can_not_deposit'])
			){
				try{
					$start_time_can_not_deposit = new DateTime($bank_chk['start_time_can_not_deposit']);
					$end_time_can_not_deposit = new DateTime($bank_chk['end_time_can_not_deposit']);
					$time_current = new DateTime(date('H:i'));

					if( in_array($start_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12"))){
						if(!in_array($end_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
							!in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
						){
							$end_time_can_not_deposit = $end_time_can_not_deposit->add(new DateInterval('P1D'));
						}
						if(
							!in_array($time_current->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
							in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
						){
							$start_time_can_not_deposit = $start_time_can_not_deposit->sub(new DateInterval('P1D'));
						}
						if($start_time_can_not_deposit->getTimestamp() > $end_time_can_not_deposit->getTimestamp()){

						}else if(
							$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
							$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
						){
							if($start_time_can_not_deposit->format("H") == "23" &&  $end_time_can_not_deposit->format("H") == "23" &&
								in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
							){
								$chk = true;
							}else{

							}
						}else{
							$chk = true;
						}
					}else if(
						$start_time_can_not_deposit->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
					){
						if(
							$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
							$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
						){

						}else{
							$chk = true;
						}
					}
				}catch (Exception $ex){

				}
			}else{
				$chk = true;
			}

		}
		echo json_encode([
			'message' => 'success',
			'start_time_can_not_deposit' => isset($bank_chk['start_time_can_not_deposit']) ? $bank_chk['start_time_can_not_deposit'] : null,
			'end_time_can_not_deposit' => isset($bank_chk['end_time_can_not_deposit']) ? $bank_chk['end_time_can_not_deposit'] : null,
			'result' => $chk]);
		exit();
	}
    public function deposit_credit()
    {
        header('Content-Type: application/json');
        check_parameter([
        'promotion'
        ], 'POST');
        $post = $this->input->post();
		sleep(rand(1,4));
		$chk_process_deposit_cache =  $this->cache->file->get('process_deposit_cache_'.date('Y_m_d')."_".$_SESSION['user']['id']);
		if($chk_process_deposit_cache !== FALSE){
			echo json_encode([
				'message' => "ทำรายการไม่สำเร็จ, กรุณาลองใหม่อีกครั้ง",
				'error' => true
			]);
			exit();
		}
		$chk_process_deposit_cache =  $this->cache->file->get('process_deposit_cache_'.date('Y_m_d')."_".$_SESSION['user']['id']);
		if($chk_process_deposit_cache !== FALSE){
			echo json_encode([
				'message' => "ทำรายการไม่สำเร็จ, กรุณาลองใหม่อีกครั้ง",
				'error' => true
			]);
			exit();
		}
		$this->cache->file->save('process_deposit_cache_'.date('Y_m_d')."_".$_SESSION['user']['id'],$_SESSION['user']['id'], 5);
        $user = $this->Account_model->account_find([
        'id' => $_SESSION['user']['id']
        ]);
        $promotion = $this->Promotion_model->promotion_find([
        'id' => $post['promotion']
        ]);

        if ($user=="") {
            echo json_encode([
            'message' => "ทำรายการไม่สำเร็จ",
            'error' => true
          ]);
            exit();
        }
		if (empty($user['account_agent_username']) || is_null($user['account_agent_username'])) {
			echo json_encode([
				'message' => "ทำรายการไม่สำเร็จ, เนื่องจากท่านยังไม่ได้รับยูสเซอร์",
				'error' => true
			]);
			exit();
		}
        if ($promotion=="") {
            echo json_encode([
            'message' => "ไม่พบโปรโมชั่นนี้",
            'error' => true
            ]);
            exit();
        }
        if ($user['amount_deposit_auto']<=0) {
            echo json_encode([
            'message' => "ยอดเงินฝากคงเหลือไม่เพียงพอ",
            'error' => true
            ]);
            exit();
        }
		$amount_deposit_auto_old = $user["amount_deposit_auto"];
		$this->Account_model->account_update([
			'id' => $_SESSION['user']['id'],
			'amount_deposit_auto' => 0,
		]);

		//Check outstand
		/*$turn_before_data = $this->check_turn_before($user);
		foreach (game_code_list() as $game_code) {
			if (array_key_exists($game_code, $turn_before_data)) {
				if (empty($chk_outstand) && is_numeric($turn_before_data[$game_code]['outstanding']) && (float)$turn_before_data[$game_code]['outstanding'] > 0) {
					$chk_outstand = "ท่านมียอดเล่นคงค้างที่ยังไม่ได้ถูกคำนวณอยู่ [" . game_code_text_list()[$game_code] . "] : " . number_format($turn_before_data[$game_code]['outstanding'], 2);
					$this->Account_model->account_update([
						'id' => $_SESSION['user']['id'],
						'amount_deposit_auto' => $amount_deposit_auto_old,
					]);
					echo json_encode([
						'message' => $chk_outstand,
						'error' => true
					]);
					exit();
				}
			}
		}*/
        $remaining_credit = $this->remaining_credit($user);

        $clear_turn = $this->Setting_model->setting_find([
          'name' => 'clear_turn'
        ]);
        $clear_turn = $clear_turn==''?10:$clear_turn['value'];
        $turn_date = $user['turn_date'];
        $turn_over = $user['turn_over'];
        $turn_over_football = $user['turn_over_football'];
        $turn_over_step = $user['turn_over_step'];
        $turn_over_parlay = $user['turn_over_parlay'];
        $turn_over_game = $user['turn_over_game'];
        $turn_over_casino = $user['turn_over_casino'];
        $turn_over_lotto = $user['turn_over_lotto'];
        $turn_over_m2 = $user['turn_over_m2'];
        $turn_over_multi_player = $user['turn_over_multi_player'];
        $turn_over_trading = $user['turn_over_trading'];
        $turn_over_keno = $user['turn_over_keno'];
		if ($turn_date=='' || is_null($turn_date)) {
			if (
				strtotime(date('Y-m-d H:i'))>=strtotime(date('Y-m-d')." 11:00")
				&&
				strtotime(date('Y-m-d H:i')) <=strtotime(date('Y-m-d')." 23:59")
			) {
				$turn_date = date('Y-m-d');
			} else {
				$turn_date = date('Y-m-d', strtotime('-1 days'));
			}
		} else {
			$turn_date = $user['turn_date'];
		}
		try{
			$turn_date = new DateTime($turn_date);
			$turn_date = $turn_date->format('Y-m-d');
		}catch (Exception $ex){
			if (
				strtotime(date('Y-m-d H:i'))>=strtotime(date('Y-m-d')." 11:00")
				&&
				strtotime(date('Y-m-d H:i')) <=strtotime(date('Y-m-d')." 23:59")
			) {
				$turn_date = date('Y-m-d');
			} else {
				$turn_date = date('Y-m-d', strtotime('-1 days'));
			}
		}

		if (floor($remaining_credit)<=$clear_turn) {
			if (
				strtotime(date('Y-m-d H:i'))>=strtotime(date('Y-m-d')." 11:00")
				&&
				strtotime(date('Y-m-d H:i')) <=strtotime(date('Y-m-d')." 23:59")
			) {
				$turn_date_now = date('Y-m-d');
			} else {
				$turn_date_now = date('Y-m-d', strtotime('-1 days'));
			}
			$turn_before = null;
			$turn_before_football = null;
			$turn_before_step = null;
			$turn_before_parlay = null;
			$turn_before_game = null;
			$turn_before_casino = null;
			$turn_before_lotto = null;
			$turn_before_m2 = null;
			$turn_before_multi_player = null;
			$turn_before_trading = null;
			$turn_before_keno = null;
			//ตรวจสอบวัน turn ล่าสุดจาก ref transaction
			$finance_chk_turn = $this->Finance_model->finance_for_check_turn_find([
				'account' => $user['id'],
				'type' => 1,
				'status' => 1,
			]);
			if($turn_date_now == date('Y-m-d') && $finance_chk_turn != "" &&
				strtotime($finance_chk_turn['created_at'])>=strtotime(date('Y-m-d')." 11:00")
				&&
				strtotime($finance_chk_turn['created_at']) <=strtotime(date('Y-m-d')." 23:59")
			){
				$user['turn_date'] = $turn_date_now;
				$user['date_end'] = $turn_date_now;
				$turn_before = 0;
				$turn_before_data = $this->check_turn_before($user);
				foreach (game_code_list() as $game_code){
					if(array_key_exists($game_code,$turn_before_data)){
						$turn_before += (float)$turn_before_data[$game_code]['amount'];
						//${'turn_before_'.strtolower($game_code)} = (float)$turn_before_data[$game_code]['amount'];
						${'turn_before_'.strtolower($game_code)} = 0;
					}
				}

			}
			$this->Account_model->account_update([
				'id' => $_SESSION['user']['id'],
				'turn_date' => $turn_date_now,
				'turn_before' => $turn_before,
				'turn_before_football' => $turn_before_football,
				'turn_before_step' => $turn_before_step,
				'turn_before_parlay' => $turn_before_parlay,
				'turn_before_game' => $turn_before_game,
				'turn_before_casino' => $turn_before_casino,
				'turn_before_lotto' => $turn_before_lotto,
				'turn_before_m2' => $turn_before_m2,
				'turn_before_multi_player' => $turn_before_multi_player,
				'turn_before_trading' => $turn_before_trading,
				'turn_before_keno' => $turn_before_keno,
				'turn_over' => 0,
				'turn_over_football' => 0,
				'turn_over_step' => 0,
				'turn_over_parlay' => 0,
				'turn_over_game' => 0,
				'turn_over_casino' => 0,
				'turn_over_lotto' => 0,
				'turn_over_m2' => 0,
				'turn_over_multi_player' => 0,
				'turn_over_trading' => 0,
				'turn_over_keno' => 0,
				'sha1_acount' => '',
				'ref_transaction_id' => '',
			]);
			$turn_over = 0;
			$turn_over_football = 0;
			$turn_over_step = 0;
			$turn_over_parlay = 0;
			$turn_over_game = 0;
			$turn_over_casino = 0;
			$turn_over_lotto = 0;
			$turn_over_m2 = 0;
			$turn_over_multi_player = 0;
			$turn_date = $turn_date_now;
		} else {
			$user['turn_date'] = $turn_date;
			$user['date_end'] = $turn_date;
			if(is_null($user['turn_before'])){
				$turn_before_football = null;
				$turn_before_step = null;
				$turn_before_parlay = null;
				$turn_before_game = null;
				$turn_before_casino = null;
				$turn_before_lotto = null;
				$turn_before_m2 = null;
				$turn_before_multi_player = null;
				$turn_before = 0;
				$turn_before_data = $this->check_turn_before($user);
				foreach (game_code_list() as $game_code){
					if(array_key_exists($game_code,$turn_before_data)){
						$turn_before += (float)$turn_before_data[$game_code]['amount'];
						${'turn_before_'.strtolower($game_code)} = (float)$turn_before_data[$game_code]['amount'];
					}
				}
			}else{
				$turn_before = $user['turn_before'];
				$turn_before_football = $user['turn_before_football'];
				$turn_before_step = $user['turn_before_step'];
				$turn_before_parlay = $user['turn_before_parlay'];
				$turn_before_game = $user['turn_before_game'];
				$turn_before_casino = $user['turn_before_casino'];
				$turn_before_lotto = $user['turn_before_lotto'];
				$turn_before_m2 = $user['turn_before_m2'];
				$turn_before_multi_player = $user['turn_before_multi_player'];
				$turn_before_trading = $user['turn_before_trading'];
				$turn_before_keno = $user['turn_before_keno'];
			}
		}

        if($promotion['category'] == "2"){
			if(
				is_null($promotion['fix_amount_deposit']) ||
				(
					!is_null($promotion['fix_amount_deposit']) &&
					(float)$user['amount_deposit_auto'] != (float)$promotion['fix_amount_deposit']
				)
			){
				$this->Account_model->account_update([
					'id' => $_SESSION['user']['id'],
					'amount_deposit_auto' => $amount_deposit_auto_old,
				]);
				echo json_encode([
					'message' => "ยอดเงินฝากไม่เข้าเงื่อนไขในการรับโปรโมชั่นนี้",
					'error' => true
				]);
				exit();
			}else{
				$percent_calculate = is_null($promotion['fix_amount_deposit_bonus']) ? 0 : (float)$promotion['fix_amount_deposit_bonus'];
			}
		}else{
			$percent_calculate = round_up(($promotion['percent']*$user['amount_deposit_auto'])/100,2);
			if ($percent_calculate>$promotion['max_value']) {
				$percent_calculate = $promotion['max_value'];
			}
		}
        $amount_deposit = ($user['amount_deposit_auto']+$percent_calculate);
        $amount_deposit_auto_remain = 0;
		if ($turn_over>0) {
			$turn_over = $turn_over+($amount_deposit*$promotion['turn']);
		} else {
			$turn_over = ($amount_deposit*$promotion['turn']);
		}

		foreach (game_code_list() as $game_code){
			if (${'turn_over_'.strtolower($game_code)}>0) {
				${'turn_over_'.strtolower($game_code)} = ${'turn_over_'.strtolower($game_code)}+($amount_deposit*$promotion['turn_'.strtolower($game_code)]);
			} else {
				${'turn_over_'.strtolower($game_code)} = ($amount_deposit*$promotion['turn_'.strtolower($game_code)]);
			}
		}

		$form_data = [];
        $form_data["account_agent_username"] = $user['account_agent_username'];
        $form_data["amount"] = $amount_deposit;
		$form_data = member_credit_data($form_data);

		//เพิ่ม Logs
		$promotion_name = null;
		if($promotion!=""){

			$promotions = $this->Promotion_model->promotion_list([
				'status' => 1
			]);
			foreach ($promotions as $key => $value) {
				if($value['id'] == $promotion['id']){
					switch ($value['type']) {
						case '1':
							$use_promotion = $this->Use_promotion_model->use_promotion_count([
								'account' => $user['id'],
								'promotion' => $value['id']
							]);
							if ($use_promotion<$value['max_use']) {
								$promotion['remaining'] = ($value['max_use']-$use_promotion);
							}else{
								$this->Account_model->account_update([
									'id' => $_SESSION['user']['id'],
									'amount_deposit_auto' => $amount_deposit_auto_old,
								]);
								echo json_encode([
									'message' => "ท่านได้ใช้โปรโมชั่นนี้ครบจำนวนแล้ว",
									'error' => true
								]);
								exit();
							}
							break;
						case '2':
							$use_promotion = $this->Use_promotion_model->use_promotion_count([
								'account' => $user['id'],
								'promotion' => $value['id'],
								'date_from' =>  date('Y-m-d'),
								'date_to' =>  date('Y-m-d'),
							]);
							if ($use_promotion<$value['max_use']) {
								$promotion['remaining'] = ($value['max_use']-$use_promotion);
							}else{
								$this->Account_model->account_update([
									'id' => $_SESSION['user']['id'],
									'amount_deposit_auto' => $amount_deposit_auto_old,
								]);
								echo json_encode([
									'message' => "ท่านได้ใช้โปรโมชั่นนี้ครบจำนวนแล้ว",
									'error' => true
								]);
								exit();
							}
							break;
						case '3':
							date_default_timezone_set('Asia/Bangkok');
							$start_date_pro = new DateTime();
							$start_date_pro->modify('Monday this week');
							$end_date_pro = new DateTime();
							$end_date_pro->modify('Sunday this week');
							$use_promotion = $this->Use_promotion_model->use_promotion_count([
								'account' => $user['id'],
								'promotion' => $value['id'],
								'date_from' =>  $start_date_pro->format('Y-m-d'),
								'date_to' =>  $end_date_pro->format('Y-m-d'),
							]);
							if ($use_promotion<$value['max_use']) {
								$promotion['remaining'] = ($value['max_use']-$use_promotion);
							}else{
								$this->Account_model->account_update([
									'id' => $_SESSION['user']['id'],
									'amount_deposit_auto' => $amount_deposit_auto_old,
								]);
								echo json_encode([
									'message' => "ท่านได้ใช้โปรโมชั่นนี้ครบจำนวนแล้ว",
									'error' => true
								]);
								exit();
							}
							break;
						case '4':
							date_default_timezone_set('Asia/Bangkok');
							$start_date_pro = new DateTime();
							$start_date_pro->modify('first day of this month');
							$end_date_pro = new DateTime();
							$end_date_pro->modify('last day of this month');
							$use_promotion = $this->Use_promotion_model->use_promotion_count([
								'account' => $user['id'],
								'promotion' => $value['id'],
								'date_from' =>  $start_date_pro->format('Y-m-d'),
								'date_to' =>  $end_date_pro->format('Y-m-d'),
							]);
							if ($use_promotion<$value['max_use']) {
								$promotion['remaining'] = ($value['max_use']-$use_promotion);
							}else{
								$this->Account_model->account_update([
									'id' => $_SESSION['user']['id'],
									'amount_deposit_auto' => $amount_deposit_auto_old,
								]);
								echo json_encode([
									'message' => "ท่านได้ใช้โปรโมชั่นนี้ครบจำนวนแล้ว",
									'error' => true
								]);
								exit();
							}
							break;
              case '5':
              $current_time = date('Y-m-d H:i:s');
              $start_time = date('Y-m-d H:i:s',strtotime("today {$value['start_time']}"));
              $end_time = date('Y-m-d H:i:s',strtotime("today {$value['end_time']}"));
              if ($current_time >= $start_time && $current_time <= $end_time) {
                $use_promotion = $this->Use_promotion_model->use_promotion_count([
                  'account' => $_SESSION['user']['id'],
                  'promotion' => $value['id'],
                  'start_time' =>  $start_time,
                  'end_time' =>  $end_time,
                ]);
                if ($use_promotion<$value['max_use']) {
                    $promotion['remaining'] = ($value['max_use']-$use_promotion);
                } else {
                  $this->Account_model->account_update([
  									'id' => $_SESSION['user']['id'],
  									'amount_deposit_auto' => $amount_deposit_auto_old,
  								]);
  								echo json_encode([
  									'message' => "ท่านได้ใช้โปรโมชั่นนี้ครบจำนวนแล้ว",
  									'error' => true
  								]);
  								exit();
                }
              } else {
                echo json_encode([
                  'message' => "หมดเวลารับโปรโมชั่นนี้",
                  'error' => true
                ]);
                exit();
              }
              break;
              case '6':
              $days_deposit = $value['number_of_deposit_days'];
              $dataLastday = $this->getLastNDays($days_deposit);
              $finance = $this->Finance_model->finance_find_created_at([
                'account' => $_SESSION['user']['id'],
                'limit'=> $days_deposit
              ]);
              $result_intersect = array_intersect($dataLastday,$finance);
              $countDay = count($result_intersect);
              if ($countDay >= $days_deposit) {
                $use_promotion = $this->Use_promotion_model->use_promotion_count([
                  'account' => $_SESSION['user']['id'],
                  'promotion' => $value['id']
                ]);
                if ($use_promotion<$value['max_use']) {
                    $promotion['remaining'] = ($value['max_use']-$use_promotion);
                } else {
                  $this->Account_model->account_update([
  									'id' => $_SESSION['user']['id'],
  									'amount_deposit_auto' => $amount_deposit_auto_old,
  								]);
  								echo json_encode([
  									'message' => "ท่านได้ใช้โปรโมชั่นนี้ครบจำนวนแล้ว",
  									'error' => true
  								]);
  								exit();
                }
              } else {
                echo json_encode([
                  'message' => "ท่านไม่ได้ฝากต่อเนื่องตามที่กำหนด",
                  'error' => true
                ]);
                exit();
              }
              break;
						default:
							$promotion['remaining'] = 0;
							break;
					}
					break;
				}
			}

			$promotion_name = "".$promotion['name'];
			if($promotion['max_value']>0 && $promotion['category'] == "1"){
				$text_append_promotion_name = "";
				foreach (game_code_list() as $index => $game_code){
					if($index == 0){
						$text_append_promotion_name .= "".game_code_text_list()[$game_code]." ".$promotion['turn_'.strtolower($game_code)]." เท่า";
					}else{
						$text_append_promotion_name .= ", ".game_code_text_list()[$game_code]." ".$promotion['turn_'.strtolower($game_code)]." เท่า";
					}
				}
				$promotion_name .= " สูงสุด ".number_format($promotion['max_value'])." บาท ( ทำเทิร์น : ".$text_append_promotion_name." )";
			}else if($promotion['category'] == "2"){
				$text_append_promotion_name = "";
				foreach (game_code_list() as $index => $game_code){
					if($index == 0){
						$text_append_promotion_name .= "".game_code_text_list()[$game_code]." ".$promotion['turn_'.strtolower($game_code)]." เท่า";
					}else{
						$text_append_promotion_name .= ", ".game_code_text_list()[$game_code]." ".$promotion['turn_'.strtolower($game_code)]." เท่า";
					}
				}
				$promotion_name .= " ( ทำเทิร์น : ".$text_append_promotion_name." )";
			}
			if($promotion['type']>1){
				$promotion_name .= " ใช้ไปแล้ว (".((float)$promotion['max_use']-(float)$promotion['remaining'] )."/".$promotion['max_use'].")";
			}
		}
		$log_deposit_withdraw_id = $this->Log_deposit_withdraw_model->log_deposit_withdraw_create([
			'account' => $user['id'],
			'username' => $user['username'],
			'amount' => $form_data["amount"],
			'amount_before' => $remaining_credit,
			'type' => '1', //ฝาก
			'description' => 'ฝากเงิน',
			'admin' =>$user['id'],
			'promotion_name' =>$promotion_name,
		]);

		$response = $this->game_api_librarie->deposit($form_data);
		if (isset($response['ref'])) {

			if((float)$form_data["amount"] > (float)$user['amount_deposit_auto']){
				$log_add_credit_id = $this->Log_add_credit_model->log_add_credit_create([
					'account' => $user['id'],
					'username' => $user['username'],
					'full_name' => $user['full_name'],
					'from_amount' => $user['amount_deposit_auto'],
					'amount' => (float)$form_data["amount"] - (float)$user['amount_deposit_auto'],
					'type' => 'bonus_promotion',
					'description' => "ฝากเงิน ".$promotion_name,
					'manage_by' =>$user['id'],
					'manage_by_username' =>$user['username'],
					'manage_by_full_name' =>$user['full_name'],
				]);
			}else{
				$log_add_credit_id = $this->Log_add_credit_model->log_add_credit_create([
					'account' => $user['id'],
					'username' => $user['username'],
					'full_name' => $user['full_name'],
					'from_amount' => $user['amount_deposit_auto'],
					'amount' => 0,
					'type' => 'bonus_not_use_promotion',
					'description' => "ฝากเงิน ".$promotion_name,
					'manage_by' =>$user['id'],
					'manage_by_username' =>$user['username'],
					'manage_by_full_name' =>$user['full_name'],
				]);
			}

			$user_new = $this->Account_model->account_find([
				'id' => $_SESSION['user']['id']
			]);

			$finance_id = $this->Finance_model->finance_create([
				'account' => $user['id'],
				'amount' => $user['amount_deposit_auto'],
				'from_amount' => (float)$form_data["amount"] - (float)$user['amount_deposit_auto'],
				'bank' => $user['bank'],
				'bank_number' => $user['bank_number'],
				'bank_name' => $user['bank_name'],
				'ref_transaction_id' => $response['ref'],
				'username' => $user['username'],
				'type' => 1,
				'status' => 1
			]);
			$this->Use_promotion_model->use_promotion_create([
				'finance' => $finance_id,
				'promotion' => $promotion['id'],
				'promotion_name' => $promotion['name'],
				'percent' => $promotion['percent'],
				'turn' => $promotion['turn'],
				'turn_football' => $promotion['turn_football'],
				'turn_step' => $promotion['turn_step'],
				'turn_parlay' => $promotion['turn_parlay'],
				'turn_game' => $promotion['turn_game'],
				'turn_casino' => $promotion['turn_casino'],
				'turn_lotto' => $promotion['turn_lotto'],
				'turn_m2' => $promotion['turn_m2'],
				'turn_multi_player' => $promotion['turn_multi_player'],
				'turn_trading' => $promotion['turn_trading'],
				'turn_keno' => $promotion['turn_keno'],
				'max_value' => $promotion['max_value'],
				'sum_amount' => $amount_deposit,
				'amount' => $amount_deposit_auto_old,
				'max_use' => $promotion['max_use'],
				'type' => $promotion['type']
			]);

			$sum_amount_list = $this->Finance_model->sum_amount_deposit_and_withdraw(['account_list' => [$user['id']]]);
			$sum_amount = 0.00;
			if(array_key_exists($user['id'],$sum_amount_list)){
				$sum_amount = $sum_amount_list[$user['id']]['sum_amount'];
			}

			if($user_new!="" && empty($user_new['ref_transaction_id'])){
				$this->Account_model->account_update([
					'id' => $user['id'],
					'amount_deposit_auto' => $amount_deposit_auto_remain,
					'turn_before' => $turn_before,
					'turn_before_football' => $turn_before_football,
					'turn_before_step' => $turn_before_step,
					'turn_before_parlay' => $turn_before_parlay,
					'turn_before_game' => $turn_before_game,
					'turn_before_casino' => $turn_before_casino,
					'turn_before_lotto' => $turn_before_lotto,
					'turn_before_m2' => $turn_before_m2,
					'turn_before_multi_player' => $turn_before_multi_player,
					'turn_before_trading' => $turn_before_trading,
					'turn_before_keno' => $turn_before_keno,
					'ref_transaction_id' => $response['ref'],
					'turn_over' => $turn_over,
					'turn_over_football' => $turn_over_football,
					'turn_over_step' => $turn_over_step,
					'turn_over_parlay' => $turn_over_parlay,
					'turn_over_game' => $turn_over_game,
					'turn_over_casino' => $turn_over_casino,
					'turn_over_lotto' => $turn_over_lotto,
					'turn_over_m2' => $turn_over_m2,
					'turn_over_multi_player' => $turn_over_multi_player,
					'turn_over_trading' => $turn_over_trading,
					'turn_over_keno' => $turn_over_keno,
					'turn_date' => $turn_date,
					'sum_amount' => $sum_amount,
				]);
			}else{
				$this->Account_model->account_update([
					'id' => $user['id'],
					'amount_deposit_auto' => $amount_deposit_auto_remain,
					'turn_before' => $turn_before,
					'turn_before_football' => $turn_before_football,
					'turn_before_step' => $turn_before_step,
					'turn_before_parlay' => $turn_before_parlay,
					'turn_before_game' => $turn_before_game,
					'turn_before_casino' => $turn_before_casino,
					'turn_before_lotto' => $turn_before_lotto,
					'turn_before_m2' => $turn_before_m2,
					'turn_over' => $turn_over,
					'turn_over_football' => $turn_over_football,
					'turn_over_step' => $turn_over_step,
					'turn_over_parlay' => $turn_over_parlay,
					'turn_over_game' => $turn_over_game,
					'turn_over_casino' => $turn_over_casino,
					'turn_over_lotto' => $turn_over_lotto,
					'turn_over_m2' => $turn_over_m2,
					'turn_over_multi_player' => $turn_over_multi_player,
					'turn_over_trading' => $turn_over_trading,
					'turn_over_keno' => $turn_over_keno,
					'turn_date' => $turn_date,
					'sum_amount' => $sum_amount,
				]);
			}

			$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
				'id' => $log_deposit_withdraw_id
			]);
			if($log_deposit_withdraw!=""){
				$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
					'id' => $log_deposit_withdraw_id,
					'description' => $log_deposit_withdraw['description']." | ทำรายการสำเร็จ",
				]);
			}

			$wheel_amount_per_point = $this->Setting_model->setting_find([
				'name' => 'wheel_amount_per_point'
			]);
			$feature_wheel = $this->Feature_status_model->setting_find([
				'name' => 'wheel'
			]);
			if($feature_wheel != "" && $feature_wheel['value'] == "1"){
				if($wheel_amount_per_point != "" && is_numeric($wheel_amount_per_point['value']) && (float)$wheel_amount_per_point['value'] > 0 && (float)$user['amount_deposit_auto'] >= (float)$wheel_amount_per_point['value']){
					$point = round((float)$user['amount_deposit_auto']/(float)$wheel_amount_per_point['value'], 0, PHP_ROUND_HALF_UP);
					if($point > 0){
						$this->Account_model->account_update([
							'id' => $user['id'],
							'point_for_wheel' =>  (float)$user['point_for_wheel'] + $point,
						]);
						$log_wheel_id = $this->Log_wheel_model->log_wheel_create([
							'account' => $user['id'],
							'username' => $user['username'],
							'point_before' => $user['point_for_wheel'],
							'point_after' => (float)$user['point_for_wheel'] + $point,
							'point' => $point,
							'amount' => $user['amount_deposit_auto'],
							'description' => "เงินฝาก ".number_format( $user['amount_deposit_auto'],2)." บาท (คำนวณจาก ".$wheel_amount_per_point['value']." บาท/ 1 เหรียญ)",
							'type' => '0', //เติม
							'status' => '1', //สำเร็จ
						]);
					}
				}

			}


            //$this->ref_bonus($user, $finance_id);
            echo json_encode([
            'message' => 'success',
            'result' => true
            ]);
        } else {
			$error_message = isset($response['code']) && !empty($response['code']) ? "#".$response['code'] : 'ทำรายการไม่สำเร็จ';

			$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
				'id' => $log_deposit_withdraw_id
			]);
			if($log_deposit_withdraw!=""){
				$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
					'id' => $log_deposit_withdraw_id,
					'description' => $log_deposit_withdraw['description']." | ".$error_message,
				]);
			}
			$this->Account_model->account_update([
				'id' => $_SESSION['user']['id'],
				'amount_deposit_auto' => $amount_deposit_auto_old,
			]);
            echo json_encode([
            'message' => $error_message,
            'error' => true,
            ]);
        }
    }
    public function check_turn_before($user)
    {
        $form_data = [];
        $form_data['username'] = $user['account_agent_username'];
        if ($user['turn_date']!="") {
            $date = new DateTime($user['turn_date']);
            $form_data['date_begin'] = $date->format('Y-m-d');
        }
		if(isset($user['date_end'])){
			$date = new DateTime($user['date_end']);
			$form_data['date_end'] = $date->format('Y-m-d');
		}
        $form_data = member_turn_data($form_data);
        $turnover_amount = $this->game_api_librarie->getTurn($form_data);
        return $turnover_amount;
    }
    private function remaining_credit($user)
    {
        header('Content-Type: application/json');
        try {
			$balance_credit = $this->game_api_librarie->balanceCredit($user);
            return $balance_credit;
        } catch (\Exception $e) {
            echo json_encode([
            'message' => 'ทำรายการไม่สำเร็จ',
            'error' => true
            ]);
            exit();
        }
    }
    private function ref_bonus($user, $finance_id = '')
    {
        $ref_find = $this->Ref_model->ref_find([
            'to_account' => $user['id']
        ]);
		if ($ref_find!=""&&$ref_find['agent']==0) {
            $ref_percent = $this->Setting_model->setting_find([
            'name' => 'ref_percent'
            ]);
            $ref_turn = $this->Setting_model->setting_find([
            'name' => 'ref_turn'
            ]);
            $ref_percent = $ref_percent!=""?$ref_percent['value']:0;
            $ref_turn = $ref_turn!=""?$ref_turn['value']:0;
            $sum_amount = round_up(($user['amount_deposit_auto']*$ref_percent)/100,2);
            // $turn_over = ($sum_amount*$ref_turn)+$ref_find['from_account_turn_over'];
            $this->Ref_model->ref_deposit_create([
            'account' => $ref_find['from_account_id'],
            'finance' => $finance_id,
            'percent' => $ref_percent,
            // 'turn' => $ref_turn,
            'sum_amount' => $sum_amount
           ]);
            $account_update = [
             'id' => $ref_find['from_account_id'],
             'amount_wallet_ref' => ($ref_find['from_account_amount_wallet_ref']+$sum_amount)
           ];
            //กรณีไม่ติด turn มาก่อน
            // if ($ref_find['from_account_turn_date']=='') {
            //     $turn_before = $this->check_turn_before([
            //    'account_agent_username' => $ref_find['member_username'],
            //    'turn_date' => $ref_find['from_account_turn_date']
            //  ]);
            //     $account_update['turn_date'] = date('Y-m-d H:i:s');
            //     $account_update['turn_before'] = $turn_before;
            // }
            $this->Account_model->account_update($account_update);
        }
    }
    public function wallet_ref_deposit()
    {
        $user = $this->Account_model->account_find([
        'id' => $_SESSION['user']['id']
      ]);
        if ($user=="") {
            echo json_encode([
        'message' => 'ทำรายการไม่สำเร็จ',
        'error' => true
        ]);
            exit();
        }
		$old_amount_wallet_ref = $user['amount_wallet_ref'];
		$this->Account_model->account_update([
			'id' => $user['id'],
			'amount_wallet_ref' => 0.00,
		]);
		if (empty($user['account_agent_username']) || is_null($user['account_agent_username'])) {
			$this->Account_model->account_update([
				'id' => $user['id'],
				'amount_wallet_ref' => $old_amount_wallet_ref
			]);
			echo json_encode([
				'message' => "ทำรายการไม่สำเร็จ, เนื่องจากท่านยังไม่ได้รับยูสเซอร์",
				'error' => true
			]);
			exit();
		}
        if ($user['amount_wallet_ref']<=0) {
			$this->Account_model->account_update([
				'id' => $user['id'],
				'amount_wallet_ref' => $old_amount_wallet_ref
			]);
            echo json_encode([
        'message' => 'ยอดคงเหลือไม่เพียงพอ',
        'error' => true
        ]);
            exit();
        }
		$form_data = [];
		$form_data["account_agent_username"] = $user['account_agent_username'];
		$form_data["amount"] = $user['amount_wallet_ref'];
		$form_data = member_credit_data($form_data);

		$credit_before = $this->remaining_credit($user);
		//เพิ่ม Logs
		$log_deposit_withdraw_id = $this->Log_deposit_withdraw_model->log_deposit_withdraw_create([
			'account' => $user['id'],
			'username' => $user['username'],
			'amount' => $form_data["amount"],
			'amount_before' => $credit_before,
			'type' => '1', //ฝาก
			'description' => 'เพิ่มเครดิต (Commission)',
			'admin' =>$user['id'],
		]);

		$response = $this->game_api_librarie->deposit($form_data);
		if (isset($response['ref'])) {

			$log_add_credit_id = $this->Log_add_credit_model->log_add_credit_create([
				'account' => $user['id'],
				'username' => $user['username'],
				'full_name' => $user['full_name'],
				'from_amount' => $form_data['amount'],
				'amount' => $form_data["amount"],
				'type' => 'bonus_commission',
				'description' => "เพิ่มเครดิต (Commission)",
				'manage_by' =>$user['id'],
				'manage_by_username' =>$user['username'],
				'manage_by_full_name' =>$user['full_name'],
			]);

			$user_after = $this->Account_model->account_find([
			  'id' => $user['id']
			]);
				$ref_turn = $this->Setting_model->setting_find([
			  'name' => 'ref_turn'
			]);
            $ref_turn = $ref_turn!=""?$ref_turn['value']:0;
            $data_ref = [
				'account' => $user['id'],
				'amount_wallet_ref' => $user['amount_wallet_ref'],
				'turn' => $ref_turn,
				'turn_over_before' => $user['turn_over'],
				'turn_over_after' => ($user['amount_wallet_ref']*$ref_turn)+$user_after['turn_over']
			];
			foreach (game_code_list() as $game_code){
				$data_ref['turn_'.strtolower($game_code)] = $ref_turn;
				$data_ref['turn_over_before_'.strtolower($game_code)] = $user['turn_over_'.strtolower($game_code)];
				$data_ref['turn_over_after_'.strtolower($game_code)] = ($user['amount_wallet_ref']*$ref_turn)+$user_after['turn_over_'.strtolower($game_code)];
			}
            $this->Wallet_ref_deposit_model->wallet_ref_deposit_create($data_ref);

			$turn_over = $user_after['turn_over'] != "" && !is_null($user_after['turn_over']) ? $user_after['turn_over'] : 0;
			$turn_date = $user_after['turn_date'];
			$turn_before = $user_after['turn_before'] != "" && !is_null($user_after['turn_before']) ? $user_after['turn_before'] : 0;
			if ($turn_date=='') {
				if (
					strtotime(date('Y-m-d H:i'))>=strtotime(date('Y-m-d')." 11:00")
					&&
					strtotime(date('Y-m-d H:i')) <=strtotime(date('Y-m-d')." 23:59")
				) {
					$turn_date = date('Y-m-d');
				} else {
					$turn_date = date('Y-m-d', strtotime('-1 days'));
				}
			} else {
				$turn_date = $user_after['turn_date'];
			}
			$data_user_update = [
				'id' => $user['id'],
				'turn_date' => $turn_date,
				'turn_over' => ($user['amount_wallet_ref']*$ref_turn)+$user_after['turn_over'],
				'amount_wallet_ref' => 0.00,
			];
			foreach (game_code_list() as $game_code){
				$data_user_update['turn_over_'.strtolower($game_code)] = ($user['amount_wallet_ref']*$ref_turn)+$user_after['turn_over_'];
			}


			$clear_turn = $this->Setting_model->setting_find([
				'name' => 'clear_turn'
			]);
			$clear_turn = $clear_turn==''?10:$clear_turn['value'];
			if(
				(float)$credit_before <= (float)$clear_turn
			){
				foreach (game_code_list() as $game_code){
					$data_user_update['turn_before_'.strtolower($game_code)] = 0;
				}
				$data_user_update['ref_transaction_id'] = $response['ref'];
				$data_user_update['turn_before'] = 0;
				$this->Account_model->account_update($data_user_update);
			}else{
				$this->Account_model->account_update($data_user_update);
			}
			$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
				'id' => $log_deposit_withdraw_id
			]);
			if($log_deposit_withdraw!=""){
				$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
					'id' => $log_deposit_withdraw_id,
					'description' => $log_deposit_withdraw['description']." | ทำรายการสำเร็จ",
				]);
			}

            echo json_encode([
            'message' => 'ทำรายการสำเร็จ',
            'result' => true
            ]);
        } else {
			$this->Account_model->account_update([
				'id' => $user['id'],
				'amount_wallet_ref' => $old_amount_wallet_ref
			]);
			$error_message = isset($response['code']) && !empty($response['code']) ? "#".$response['code'] : 'ทำรายการไม่สำเร็จ';

			$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
				'id' => $log_deposit_withdraw_id
			]);
			if($log_deposit_withdraw!=""){
				$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
					'id' => $log_deposit_withdraw_id,
					'description' => $log_deposit_withdraw['description']." | ".$error_message,
				]);
			}

            echo json_encode([
          'message' => $error_message,
          'error' => true,
          'response' => $response,
          ]);
        }
    }

	public function wallet_ref_deposit_return_balance()
	{
		if(isset($_SESSION['wallet_ref_deposit_return_balance'])){
			try{
				$hiDate = new DateTime($_SESSION['wallet_ref_deposit_return_balance']);
				$loDate = new DateTime(date('Y-m-d H:i:s'));
				$diff = $hiDate->diff($loDate);
				$secs = ((($diff->format("%a") * 24) + $diff->format("%H")) * 60 +
						$diff->format("%i")) * 60 + $diff->format("%s");
				if($secs <= 3){
					echo json_encode([
						'message' => "ไม่สามารถดึงยอดเทิร์นได้",
						'error' => true
					]);
					exit();
				}
			}catch (Exception $ex){

			}
		}
		$_SESSION['wallet_ref_deposit_return_balance'] = date('Y-m-d H:i:s');
		$user = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		if ($user=="") {
			echo json_encode([
				'message' => 'ทำรายการไม่สำเร็จ',
				'error' => true
			]);
			exit();
		}
		if (empty($user['account_agent_username']) || is_null($user['account_agent_username'])) {
			echo json_encode([
				'message' => "ทำรายการไม่สำเร็จ, เนื่องจากท่านยังไม่ได้รับยูสเซอร์",
				'error' => true
			]);
			exit();
		}
		if ($user['point_for_return_balance']<1) {
			echo json_encode([
				'message' => 'โบนัสคืนยอดเสียคงเหลือต้องมากกว่าหรือเท่ากับ 1.00',
				'error' => true
			]);
			exit();
		}
		$point_for_return_balance_old = $user['point_for_return_balance'];
		$this->Account_model->account_update([
			'id' => $user['id'],
			'point_for_return_balance' => 0.00,
		]);
		$winlose_amount = is_numeric($point_for_return_balance_old) ? (float)$point_for_return_balance_old : 0.00;
		if($winlose_amount >= 1){

			if(isset($user['rank']) && !is_null($user['rank'])){
				if($user['rank'] =="1"){
					$ref_turn = $this->Setting_model->setting_find([
						'name' => 'ref_return_balance_rank1_turn'
					]);
				}else if($user['rank'] =="2"){
					$ref_turn = $this->Setting_model->setting_find([
						'name' => 'ref_return_balance_rank2_turn'
					]);
				}else if($user['rank'] =="3"){
					$ref_turn = $this->Setting_model->setting_find([
						'name' => 'ref_return_balance_rank3_turn'
					]);
				}else{
					$ref_turn = $this->Setting_model->setting_find([
						'name' => 'ref_return_balance_rank1_turn'
					]);
				}
			}else{
				$ref_turn = $this->Setting_model->setting_find([
					'name' => 'ref_return_balance_turn'
				]);
			}

			/*$ref_percent = $this->Setting_model->setting_find([
				'name' => 'ref_return_balance_percent'
			]);
			$ref_percent = $ref_percent!=""?$ref_percent['value']:0;*/
			$ref_turn = $ref_turn!="" && $ref_turn['value'] != "" ?$ref_turn['value']:0;
			$sum_amount = $winlose_amount;
			if($sum_amount  >= 1){
				//Add to main wallet
				$form_data = [];
				$form_data["account_agent_username"] = $user['account_agent_username'];
				$form_data["amount"] = $sum_amount;
				$form_data = member_credit_data($form_data);

				//เพิ่ม Logs
				$credit_before = $this->remaining_credit($user);
				$log_deposit_withdraw_id = $this->Log_deposit_withdraw_model->log_deposit_withdraw_create([
					'account' => $user['id'],
					'username' => $user['username'],
					'amount' => $form_data["amount"],
					'amount_before' => $credit_before,
					'type' => '1', //ฝาก
					'description' => 'เพิ่มเครดิต (โบนัสคืนยอดเสียให้ตัวเอง)',
					'admin' =>$user['id'],
				]);

				$response = $this->game_api_librarie->deposit($form_data);
				if (isset($response['ref'])) {

					$log_add_credit_id = $this->Log_add_credit_model->log_add_credit_create([
						'account' => $user['id'],
						'username' => $user['username'],
						'full_name' => $user['full_name'],
						'from_amount' => $winlose_amount,
						'amount' => $form_data["amount"],
						'type' => 'bonus_return_balance_winlose',
						'description' => 'เพิ่มเครดิต (โบนัสคืนยอดเสียให้ตัวเอง)',
						'manage_by' =>$user['id'],
						'manage_by_username' =>$user['username'],
						'manage_by_full_name' =>$user['full_name'],
					]);

					$log_return_balance_id = $this->Log_return_balance_model->log_return_balance_create([
						'account' => $user['id'],
						'username' => $user['username'],
						'point_before' => is_numeric($point_for_return_balance_old) ? $point_for_return_balance_old : 0.00,
						'point_after' => 0.00,
						'point' => $sum_amount,
						'description' => "ใช้แต้ม (โบนัสคืนยอดเสียให้ตัวเอง) : ".number_format($sum_amount,2)." [เทิร์น ".$ref_turn." เท่า]",
						'type' => '1', //ใช้
						'status' => '1', //สำเร็จ
					]);

					$turn_over = $user['turn_over'] != "" && !is_null($user['turn_over']) ? $user['turn_over'] : 0;
					$turn_date = $user['turn_date'];
					$turn_before = $user['turn_before'] != "" && !is_null($user['turn_before']) ? $user['turn_before'] : 0;
					if ($turn_date=='') {
						if (
							strtotime(date('Y-m-d H:i'))>=strtotime(date('Y-m-d')." 11:00")
							&&
							strtotime(date('Y-m-d H:i')) <=strtotime(date('Y-m-d')." 23:59")
						) {
							$turn_date = date('Y-m-d');
						} else {
							$turn_date = date('Y-m-d', strtotime('-1 days'));
						}
					} else {
						$turn_date = $user['turn_date'];
					}
					if ($ref_turn>0) {
						if ($user['turn_over']>0) {
							$turn_over = $turn_over+($sum_amount*$ref_turn);
						} else {
							$turn_over = ($sum_amount*$ref_turn);
						}
					}
					$turn_over_football = $user['turn_over_football'];
					$turn_over_step = $user['turn_over_step'];
					$turn_over_parlay = $user['turn_over_parlay'];
					$turn_over_game = $user['turn_over_game'];
					$turn_over_casino = $user['turn_over_casino'];
					$turn_over_lotto = $user['turn_over_lotto'];
					$turn_over_m2 = $user['turn_over_m2'];
					$turn_over_multi_player = $user['turn_over_multi_player'];
					$turn_over_trading = $user['turn_over_trading'];
					$turn_over_keno = $user['turn_over_keno'];
					foreach (game_code_list() as $game_code){
						if (${'turn_over_'.strtolower($game_code)}>0) {
							${'turn_over_'.strtolower($game_code)} = ${'turn_over_'.strtolower($game_code)}+($sum_amount*$ref_turn);
						} else {
							${'turn_over_'.strtolower($game_code)} = ($sum_amount*$ref_turn);
						}
					}
					$turn_before_football = null;
					$turn_before_step = null;
					$turn_before_parlay = null;
					$turn_before_game = null;
					$turn_before_casino = null;
					$turn_before_lotto = null;
					$turn_before_m2 = null;
					$turn_before_multi_player = null;
					$turn_before_trading = null;
					$turn_before_keno = null;
					$turn_before_data = $this->check_turn_before($user);
					foreach (game_code_list() as $game_code){
						if(array_key_exists($game_code,$turn_before_data)){
							$turn_before += (float)$turn_before_data[$game_code]['amount'];
							${'turn_before_'.strtolower($game_code)} = (float)$turn_before_data[$game_code]['amount'];
						}
					}
					$clear_turn = $this->Setting_model->setting_find([
						'name' => 'clear_turn'
					]);
					$clear_turn = $clear_turn==''?10:$clear_turn['value'];
					if(
						(float)$credit_before <= (float)$clear_turn
					){
						$this->Account_model->account_update([
							'ref_transaction_id' => $response['ref'],
							'id' => $user['id'],
							'turn_before' => 0,
							'turn_before_football' => 0,
							'turn_before_step' => 0,
							'turn_before_parlay' => 0,
							'turn_before_game' => 0,
							'turn_before_casino' => 0,
							'turn_before_lotto' => 0,
							'turn_before_m2' => 0,
							'turn_before_multi_player' => 0,
							'turn_before_trading' => 0,
							'turn_before_keno' => 0,
							'turn_over' => $turn_over,
							'turn_over_football' => $turn_over_football,
							'turn_over_step' => $turn_over_step,
							'turn_over_parlay' => $turn_over_parlay,
							'turn_over_game' => $turn_over_game,
							'turn_over_casino' => $turn_over_casino,
							'turn_over_lotto' => $turn_over_lotto,
							'turn_over_m2' => $turn_over_m2,
							'turn_over_multi_player' => $turn_over_multi_player,
							'turn_over_trading' => $turn_over_trading,
							'turn_over_keno' => $turn_over_keno,
							'turn_date' => $turn_date,
						]);
					}else{
						$this->Account_model->account_update([
							'id' => $user['id'],
							'turn_before' => $turn_before,
							'turn_before_football' => $turn_before_football,
							'turn_before_step' => $turn_before_step,
							'turn_before_parlay' => $turn_before_parlay,
							'turn_before_game' => $turn_before_game,
							'turn_before_casino' => $turn_before_casino,
							'turn_before_lotto' => $turn_before_lotto,
							'turn_before_m2' => $turn_before_m2,
							'turn_before_multi_player' => $turn_before_multi_player,
							'turn_before_trading' => $turn_before_trading,
							'turn_before_keno' => $turn_before_keno,
							'turn_over' => $turn_over,
							'turn_over_football' => $turn_over_football,
							'turn_over_step' => $turn_over_step,
							'turn_over_parlay' => $turn_over_parlay,
							'turn_over_game' => $turn_over_game,
							'turn_over_casino' => $turn_over_casino,
							'turn_over_lotto' => $turn_over_lotto,
							'turn_over_m2' => $turn_over_m2,
							'turn_over_multi_player' => $turn_over_multi_player,
							'turn_over_trading' => $turn_over_trading,
							'turn_over_keno' => $turn_over_keno,
							'turn_date' => $turn_date,
						]);
					}

					$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
						'id' => $log_deposit_withdraw_id
					]);
					if($log_deposit_withdraw!=""){
						$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
							'id' => $log_deposit_withdraw_id,
							'description' => $log_deposit_withdraw['description']." | ทำรายการสำเร็จ",
						]);
					}
					echo json_encode([
						'message' => 'ทำรายการสำเร็จ',
						'result' => true
					]);
					exit();
				}else{
					$this->Account_model->account_update([
						'id' => $user['id'],
						'point_for_return_balance' => $point_for_return_balance_old,
					]);
					$error_message = isset($response['code']) && !empty($response['code']) ? "#".$response['code'] : 'ทำรายการไม่สำเร็จ';

					$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
						'id' => $log_deposit_withdraw_id
					]);
					if($log_deposit_withdraw!=""){
						$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
							'id' => $log_deposit_withdraw_id,
							'description' => $log_deposit_withdraw['description']." | ".$error_message,
						]);
					}
					echo json_encode([
						'message' => 'ทำรายการไม่สำเร็จ, กรุณาลองใหม่อีกครั้ง',
						'error' => true
					]);
					exit();
				}
			}
		}else{
			$this->Account_model->account_update([
				'id' => $user['id'],
				'point_for_return_balance' => $point_for_return_balance_old,
			]);
			echo json_encode([
				'message' => 'โบนัสคืนยอดเสียคงเหลือต้องมากกว่าหรือเท่ากับ 1.00',
				'error' => true
			]);
			exit();
		}
	}

	public function wallet_login_point()
	{
		if(isset($_SESSION['wallet_login_point'])){
			try{
				$hiDate = new DateTime($_SESSION['wallet_login_point']);
				$loDate = new DateTime(date('Y-m-d H:i:s'));
				$diff = $hiDate->diff($loDate);
				$secs = ((($diff->format("%a") * 24) + $diff->format("%H")) * 60 +
						$diff->format("%i")) * 60 + $diff->format("%s");
				if($secs <= 3){
					echo json_encode([
						'message' => "ไม่สามารถดึงยอดเทิร์นได้",
						'error' => true
					]);
					exit();
				}
			}catch (Exception $ex){

			}
		}
		$_SESSION['wallet_login_point'] = date('Y-m-d H:i:s');
		$login_status = $this->Setting_model->setting_find([
			'name' => 'login_status'
		]);
		if($login_status == "" || $login_status['value'] == "0"){
			echo json_encode([
				'message' => 'กิจจกรรมเช็คอินอยู่ในสถานะปิดใช้งาน',
				'error' => true
			]);
			exit();
		}
		$user = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		if ($user=="") {
			echo json_encode([
				'message' => 'ทำรายการไม่สำเร็จ',
				'error' => true
			]);
			exit();
		}
		if (empty($user['account_agent_username']) || is_null($user['account_agent_username'])) {
			echo json_encode([
				'message' => "ทำรายการไม่สำเร็จ, เนื่องจากท่านยังไม่ได้รับยูสเซอร์",
				'error' => true
			]);
			exit();
		}
		if ($user['login_point']<1) {
			echo json_encode([
				'message' => 'แต้มกิจจกรรมเช็คอินคงเหลือต้องมากกว่าหรือเท่ากับ 1.00',
				'error' => true
			]);
			exit();
		}
		$login_point_old = $user['login_point'];
		$this->Account_model->account_update([
			'id' => $user['id'],
			'login_point' => 0.00,
		]);
		$winlose_amount = is_numeric($login_point_old) ? (float)$login_point_old : 0.00;
		if($winlose_amount >= 1){
			$ref_turn = $this->Setting_model->setting_find([
				'name' => 'login_turn'
			]);
			$ref_turn = $ref_turn!="" && $ref_turn['value'] != "" ?$ref_turn['value']:0;
			$sum_amount = $winlose_amount;
			if($sum_amount  >= 1){
				//Add to main wallet
				$form_data = [];
				$form_data["account_agent_username"] = $user['account_agent_username'];
				$form_data["amount"] = $sum_amount;
				$form_data = member_credit_data($form_data);

				//เพิ่ม Logs
				$credit_before = $this->remaining_credit($user);
				$log_deposit_withdraw_id = $this->Log_deposit_withdraw_model->log_deposit_withdraw_create([
					'account' => $user['id'],
					'username' => $user['username'],
					'amount' => $form_data["amount"],
					'amount_before' => $credit_before,
					'type' => '1', //ฝาก
					'description' => 'เพิ่มเครดิต (โบนัสกิจกรรมเช็คอิน)',
					'admin' =>$user['id'],
				]);

				$response = $this->game_api_librarie->deposit($form_data);
				if (isset($response['ref'])) {

					$log_add_credit_id = $this->Log_add_credit_model->log_add_credit_create([
						'account' => $user['id'],
						'username' => $user['username'],
						'full_name' => $user['full_name'],
						'from_amount' => $sum_amount,
						'amount' => $form_data["amount"],
						'type' => 'bonus_checkin',
						'description' => 'เพิ่มเครดิต (โบนัสกิจกรรมเช็คอิน)',
						'manage_by' =>$user['id'],
						'manage_by_username' =>$user['username'],
						'manage_by_full_name' =>$user['full_name'],
					]);

					$turn_over = $user['turn_over'] != "" && !is_null($user['turn_over']) ? $user['turn_over'] : 0;
					$turn_date = $user['turn_date'];
					$turn_before = $user['turn_before'] != "" && !is_null($user['turn_before']) ? $user['turn_before'] : 0;
					if ($turn_date=='') {
						if (
							strtotime(date('Y-m-d H:i'))>=strtotime(date('Y-m-d')." 11:00")
							&&
							strtotime(date('Y-m-d H:i')) <=strtotime(date('Y-m-d')." 23:59")
						) {
							$turn_date = date('Y-m-d');
						} else {
							$turn_date = date('Y-m-d', strtotime('-1 days'));
						}
					} else {
						$turn_date = $user['turn_date'];
					}
					if ($ref_turn>0) {
						if ($user['turn_over']>0) {
							$turn_over = $turn_over+($sum_amount*$ref_turn);
						} else {
							$turn_over = ($sum_amount*$ref_turn);
						}
					}
					$turn_over_football = $user['turn_over_football'];
					$turn_over_step = $user['turn_over_step'];
					$turn_over_parlay = $user['turn_over_parlay'];
					$turn_over_game = $user['turn_over_game'];
					$turn_over_casino = $user['turn_over_casino'];
					$turn_over_lotto = $user['turn_over_lotto'];
					$turn_over_m2 = $user['turn_over_m2'];
					$turn_over_multi_player = $user['turn_over_multi_player'];
					$turn_over_trading = $user['turn_over_trading'];
					$turn_over_keno = $user['turn_over_keno'];
					foreach (game_code_list() as $game_code){
						if (${'turn_over_'.strtolower($game_code)}>0) {
							${'turn_over_'.strtolower($game_code)} = ${'turn_over_'.strtolower($game_code)}+($sum_amount*$ref_turn);
						} else {
							${'turn_over_'.strtolower($game_code)} = ($sum_amount*$ref_turn);
						}
					}
					$turn_before_football = null;
					$turn_before_step = null;
					$turn_before_parlay = null;
					$turn_before_game = null;
					$turn_before_casino = null;
					$turn_before_lotto = null;
					$turn_before_m2 = null;
					$turn_before_multi_player = null;
					$turn_before_trading = null;
					$turn_before_keno = null;
					$turn_before_data = $this->check_turn_before($user);
					foreach (game_code_list() as $game_code){
						if(array_key_exists($game_code,$turn_before_data)){
							$turn_before += (float)$turn_before_data[$game_code]['amount'];
							${'turn_before_'.strtolower($game_code)} = (float)$turn_before_data[$game_code]['amount'];
						}
					}
					$clear_turn = $this->Setting_model->setting_find([
						'name' => 'clear_turn'
					]);
					$clear_turn = $clear_turn==''?10:$clear_turn['value'];
					if(
						(float)$credit_before <= (float)$clear_turn
					){
						$this->Account_model->account_update([
							'ref_transaction_id' => $response['ref'],
							'id' => $user['id'],
							'turn_before' => 0,
							'turn_before_football' => 0,
							'turn_before_step' => 0,
							'turn_before_parlay' => 0,
							'turn_before_game' => 0,
							'turn_before_casino' => 0,
							'turn_before_lotto' => 0,
							'turn_before_m2' => 0,
							'turn_before_multi_player' => 0,
							'turn_before_trading' => 0,
							'turn_before_keno' => 0,
							'turn_over' => $turn_over,
							'turn_over_football' => $turn_over_football,
							'turn_over_step' => $turn_over_step,
							'turn_over_parlay' => $turn_over_parlay,
							'turn_over_game' => $turn_over_game,
							'turn_over_casino' => $turn_over_casino,
							'turn_over_lotto' => $turn_over_lotto,
							'turn_over_m2' => $turn_over_m2,
							'turn_over_multi_player' => $turn_over_multi_player,
							'turn_over_trading' => $turn_over_trading,
							'turn_over_keno' => $turn_over_keno,
							'turn_date' => $turn_date,
						]);
					}else{
						$this->Account_model->account_update([
							'id' => $user['id'],
							'turn_before' => $turn_before,
							'turn_before_football' => $turn_before_football,
							'turn_before_step' => $turn_before_step,
							'turn_before_parlay' => $turn_before_parlay,
							'turn_before_game' => $turn_before_game,
							'turn_before_casino' => $turn_before_casino,
							'turn_before_lotto' => $turn_before_lotto,
							'turn_before_m2' => $turn_before_m2,
							'turn_before_multi_player' => $turn_before_multi_player,
							'turn_before_trading' => $turn_before_trading,
							'turn_before_keno' => $turn_before_keno,
							'turn_over' => $turn_over,
							'turn_over_football' => $turn_over_football,
							'turn_over_step' => $turn_over_step,
							'turn_over_parlay' => $turn_over_parlay,
							'turn_over_game' => $turn_over_game,
							'turn_over_casino' => $turn_over_casino,
							'turn_over_lotto' => $turn_over_lotto,
							'turn_over_m2' => $turn_over_m2,
							'turn_over_multi_player' => $turn_over_multi_player,
							'turn_over_trading' => $turn_over_trading,
							'turn_over_keno' => $turn_over_keno,
							'turn_date' => $turn_date,
						]);
					}

					$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
						'id' => $log_deposit_withdraw_id
					]);
					if($log_deposit_withdraw!=""){
						$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
							'id' => $log_deposit_withdraw_id,
							'description' => $log_deposit_withdraw['description']." | ทำรายการสำเร็จ",
						]);
					}
					echo json_encode([
						'message' => 'ทำรายการสำเร็จ',
						'result' => true
					]);
					exit();
				}else{
					$this->Account_model->account_update([
						'id' => $user['id'],
						'login_point' => $login_point_old,
					]);
					$error_message = isset($response['code']) && !empty($response['code']) ? "#".$response['code'] : 'ทำรายการไม่สำเร็จ';

					$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
						'id' => $log_deposit_withdraw_id
					]);
					if($log_deposit_withdraw!=""){
						$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
							'id' => $log_deposit_withdraw_id,
							'description' => $log_deposit_withdraw['description']." | ".$error_message,
						]);
					}
					echo json_encode([
						'message' => 'ทำรายการไม่สำเร็จ, กรุณาลองใหม่อีกครั้ง',
						'error' => true
					]);
					exit();
				}
			}
		}else{
			$this->Account_model->account_update([
				'id' => $user['id'],
				'login_point' => $login_point_old,
			]);
			echo json_encode([
				'message' => 'โบนัสคืนยอดเสียคงเหลือต้องมากกว่าหรือเท่ากับ 1.00',
				'error' => true
			]);
			exit();
		}
	}

    public function getLastNDays($days, $format = 'Y-m-d')
    {
        $m = date("m"); $de= date("d",strtotime('-1 days')); $y= date("Y");
        $dateArray = array();
        for($i=0; $i<=$days-1; $i++){
            $dateArray[] = date($format, mktime(0,0,0,$m,($de-$i),$y));
        }
        return $dateArray;
    }
    public function gen_qrcode()
    {
        require FCPATH.'lib/promptpay/PromptPayQR.php';
        $bank_prom = $this->Bank_model->promptpay();
        $qrcode_amount = $_REQUEST['qrcode_amount'];
        do {
            $random = '0.'.rand(1,99);
            $num = $qrcode_amount + $random;
            $float_amount = sprintf("%.2f",$num);
            $account_amount = $this->Account_model->amount_promptpay_find([
                'amount_promptpay' => $float_amount
            ]);
        } while ($account_amount != 0);
        $this->Account_model->amount_promptpay_update([
            'username' => $_SESSION['user']['username'],
            'amount_promptpay' => $float_amount,
            'promptpay_time' => date('Y-m-d H:i:s')
        ]);
		$PromptPayQR = new PromptPayQR(); // new object
        $PromptPayQR->size = 8; // Set QR code size to 8
        $PromptPayQR->id =$bank_prom['promptpay_number']; // PromptPay ID
        $PromptPayQR->amount = $float_amount; // Set amount (not necessary)

		echo json_encode([
			'qrcode' => $PromptPayQR->generate(),
			'amount' => $float_amount
		]);
    }
	public function test_a_index()
    {
        $data['user'] = $this->Account_model->account_find([
          'id' => $_SESSION['user']['id']
        ]);
		$this->Account_model->account_update([
			'id' => $_SESSION['user']['id'],
			'active_deposit_date' => date('Y-m-d H:i:s')
		]);
        $promotion_data = [];
        $promotion = $this->Promotion_model->promotion_list([
          'status' => 1
        ]);
        foreach ($promotion as $key => $value) {
            switch ($value['type']) {
            case '1':
            $use_promotion = $this->Use_promotion_model->use_promotion_count([
              'account' => $_SESSION['user']['id'],
              'promotion' => $value['id']
            ]);
            if ($use_promotion<$value['max_use']) {
                $value['remaining'] = ($value['max_use']-$use_promotion);
                $promotion_data[] = $value;
            }
            break;
            case '2':
            $use_promotion = $this->Use_promotion_model->use_promotion_count([
              'account' => $_SESSION['user']['id'],
              'promotion' => $value['id'],
              'date_from' =>  date('Y-m-d'),
              'date_to' =>  date('Y-m-d'),
            ]);
            if ($use_promotion<$value['max_use']) {
                $value['remaining'] = ($value['max_use']-$use_promotion);
                $promotion_data[] = $value;
            }
            break;
            case '3':
				date_default_timezone_set('Asia/Bangkok');
				$start_date_pro = new DateTime();
				$start_date_pro->modify('Monday this week');
				$end_date_pro = new DateTime();
				$end_date_pro->modify('Sunday this week');
            $use_promotion = $this->Use_promotion_model->use_promotion_count([
              'account' => $_SESSION['user']['id'],
              'promotion' => $value['id'],
				'date_from' =>  $start_date_pro->format('Y-m-d'),
				'date_to' =>  $end_date_pro->format('Y-m-d'),
            ]);
            if ($use_promotion<$value['max_use']) {
                $value['remaining'] = ($value['max_use']-$use_promotion);
                $promotion_data[] = $value;
            }
            break;
            case '4':
				date_default_timezone_set('Asia/Bangkok');
				$start_date_pro = new DateTime();
				$start_date_pro->modify('first day of this month');
				$end_date_pro = new DateTime();
				$end_date_pro->modify('last day of this month');
            $use_promotion = $this->Use_promotion_model->use_promotion_count([
              'account' => $_SESSION['user']['id'],
              'promotion' => $value['id'],
				'date_from' =>  $start_date_pro->format('Y-m-d'),
				'date_to' =>  $end_date_pro->format('Y-m-d'),
            ]);
            if ($use_promotion<$value['max_use']) {
                $value['remaining'] = ($value['max_use']-$use_promotion);
                $promotion_data[] = $value;
            }
            break;
            case '5':
            $current_time = date('Y-m-d H:i:s');
            $start_time = date('Y-m-d H:i:s',strtotime("today {$value['start_time']}"));
            $end_time = date('Y-m-d H:i:s',strtotime("today {$value['end_time']}"));
            if ($current_time >= $start_time && $current_time <= $end_time) {
              $use_promotion = $this->Use_promotion_model->use_promotion_count([
                'account' => $_SESSION['user']['id'],
                'promotion' => $value['id'],
                'start_time' =>  $start_time,
                'end_time' =>  $end_time,
              ]);
              if ($use_promotion<$value['max_use']) {
                  $value['remaining'] = ($value['max_use']-$use_promotion);
                  $promotion_data[] = $value;
              }
            }
            break;
            case '6':
            $days_deposit = $value['number_of_deposit_days'];
            $dataLastday = $this->getLastNDays($days_deposit);
            $finance = $this->Finance_model->finance_find_created_at([
              'account' => $_SESSION['user']['id'],
              'limit'=> $days_deposit
            ]);
            $result_intersect = array_intersect($dataLastday,$finance);
            $countDay = count($result_intersect);
            if ($countDay >= $days_deposit) {
              $use_promotion = $this->Use_promotion_model->use_promotion_count([
                'account' => $_SESSION['user']['id'],
                'promotion' => $value['id']
              ]);
              if ($use_promotion<$value['max_use']) {
                  $value['remaining'] = ($value['max_use']-$use_promotion);
                  $promotion_data[] = $value;
              }
            }
            break;
            // no break
            default:
              // code...
              break;
          }
        }
        $data['promotion'] = $promotion_data;
		//var_dump($data['promotion']);
		$finance_current = $this->Finance_model->finance_list([
			'account' => $_SESSION['user']['id'],
			'type' => 1,
			'limit' => 1
		]);
		$promotion_active = null;
		if(!empty($finance_current) && is_array($finance_current) && count($finance_current) > 0){
			$finance_current = $finance_current[0]['id'];
			$promotion_active = $this->Use_promotion_model->promotion_list([
				'finance' => $finance_current,
				'limit' => 1
			]);
			if(!empty($promotion_active) && is_array($promotion_active) && count($promotion_active) > 0){
				$promotion_active = $promotion_active[0]['promotion'];
			}else{
				$promotion_active = null;
			}
		}
		if(!is_null($promotion_active)){
			$chk_promotion_active_exist = false;
			foreach($promotion_data as $promotion){
				if($promotion['id'] == $promotion_active){
					$chk_promotion_active_exist = $promotion['id'];
					break;
				}
			}
			$promotion_active = !$chk_promotion_active_exist ? null : $chk_promotion_active_exist;
		}
		$auto_create_member = $this->Setting_model->setting_find([
			'name' => 'auto_create_member'
		]);
		$data['auto_create_member'] = $auto_create_member;
		$data['promotion_active'] = $promotion_active;
		// ----- Start Bank Section --------
		$data['bank'] = "";
		$data['bank_all'] = null;
        $data['bank_all'] = $this->Bank_model->bank_list([
			'status'=>1,
			'status_withdraw' => 0,
			'bank_code_list_not_in'=>["null","10"]
        ]);
		// ----- handle banks ----- !
		if(in_array($data['user']['bank'],["02","2"])){
			//$data['bank'] = $this->Bank_model->bank_find(['status'=>1,'status_withdraw' => 0,'bank_code_list'=>["02","2"]]);
			// ---******* [bank_code_list_not_in] ******= cut truewallet -----
			$data['bank'] = $this->Bank_model->bank_find(['status'=>1,'status_withdraw' => 0,'bank_code_list_not_in'=>["null","10"]]);
		}
		// print_r($data);
		if($data['bank'] == ""){
			/*$data['bank'] = $this->Bank_model->bank_find(['status'=>1,'status_withdraw' => 0,'bank_code_list_not_in'=>["02","2","10"]]);
			if($data['bank'] == ""){
				$data['bank'] = $this->Bank_model->bank_find(['status'=>1,'status_withdraw' => 0,'bank_code_list_not_in'=>["null","10"]]);
			}*/
			$data['bank'] = $this->Bank_model->bank_find(['status'=>1,'status_withdraw' => 0,'bank_code_list_not_in'=>["null","10"]]);
		}
		// ------ true wallet -------
		$data['bank_truewallet'] = $this->Bank_model->bank_find(['status'=>1,'status_withdraw' => 0,'bank_code_list'=>["10"]]);
		$bank_chk_true_wallet = $data['bank_truewallet'];
		if(
			$bank_chk_true_wallet != "" &&
			isset($bank_chk_true_wallet['start_time_can_not_deposit'])
			&& !is_null($bank_chk_true_wallet['start_time_can_not_deposit'])
			&& !empty($bank_chk_true_wallet['start_time_can_not_deposit'])
			&& isset($bank_chk_true_wallet['end_time_can_not_deposit'])
			&& !is_null($bank_chk_true_wallet['end_time_can_not_deposit'])
			&& !empty($bank_chk_true_wallet['end_time_can_not_deposit'])
		){
			try{
				$start_time_can_not_deposit = new DateTime($bank_chk_true_wallet['start_time_can_not_deposit']);
				$end_time_can_not_deposit = new DateTime($bank_chk_true_wallet['end_time_can_not_deposit']);
				$time_current = new DateTime(date('H:i'));

				if( in_array($start_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12"))){
					if(!in_array($end_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
						!in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
					){
						$end_time_can_not_deposit = $end_time_can_not_deposit->add(new DateInterval('P1D'));
					}
					if(
						!in_array($time_current->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
						in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
					){
						$start_time_can_not_deposit = $start_time_can_not_deposit->sub(new DateInterval('P1D'));
					}
					if($start_time_can_not_deposit->getTimestamp() > $end_time_can_not_deposit->getTimestamp()){
						$data['bank_truewallet'] = "";
					}else if(
						$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
						$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
					){
						if($start_time_can_not_deposit->format("H") == "23" &&  $end_time_can_not_deposit->format("H") == "23" &&
							in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
						){

						}else{
							$data['bank_truewallet'] = "";
						}

					}else{

					}
				}else if(
					$start_time_can_not_deposit->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
				){
					if(
						$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
						$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
					){
						$data['bank_truewallet'] = "";
					}else{

					}
				}
			}catch (Exception $ex){
				$data['bank_truewallet'] = "";
			}
		}

		if($data['bank_truewallet'] == ""){
			$bank_list = $this->Bank_model->bank_list(['status'=>1,'status_withdraw' => 0,'bank_code_list'=>["10"]]);
			foreach($bank_list as $bank_chk){
				$chk = false;
				if(
					isset($bank_chk['start_time_can_not_deposit'])
					&& !is_null($bank_chk['start_time_can_not_deposit'])
					&& !empty($bank_chk['start_time_can_not_deposit'])
					&& isset($bank_chk['end_time_can_not_deposit'])
					&& !is_null($bank_chk['end_time_can_not_deposit'])
					&& !empty($bank_chk['end_time_can_not_deposit'])
				){
					try{
						$start_time_can_not_deposit = new DateTime($bank_chk['start_time_can_not_deposit']);
						$end_time_can_not_deposit = new DateTime($bank_chk['end_time_can_not_deposit']);
						$time_current = new DateTime(date('H:i'));

						if( in_array($start_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12"))){
							if(!in_array($end_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
								!in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
							){
								$end_time_can_not_deposit = $end_time_can_not_deposit->add(new DateInterval('P1D'));
							}
							if(
								!in_array($time_current->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
								in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
							){
								$start_time_can_not_deposit = $start_time_can_not_deposit->sub(new DateInterval('P1D'));
							}
							if($start_time_can_not_deposit->getTimestamp() > $end_time_can_not_deposit->getTimestamp()){

							}else if(
								$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
								$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
							){
								if($start_time_can_not_deposit->format("H") == "23" &&  $end_time_can_not_deposit->format("H") == "23" &&
									in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
								){
									$chk = true;
								}else{

								}
							}else{
								$chk = true;
							}
						}else if(
							$start_time_can_not_deposit->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
						){
							if(
								$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
								$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
							){

							}else{
								$chk = true;
							}
						}
					}catch (Exception $ex){

					}
				}else{
					$chk = true;
				}
				if($chk){
					$data['bank_truewallet'] = $bank_chk;
					break;
				}
			}
		}

		$bank_chk = $data['bank'];
		if(
			$bank_chk != "" &&
			isset($bank_chk['start_time_can_not_deposit'])
			&& !is_null($bank_chk['start_time_can_not_deposit'])
			&& !empty($bank_chk['start_time_can_not_deposit'])
			&& isset($bank_chk['end_time_can_not_deposit'])
			&& !is_null($bank_chk['end_time_can_not_deposit'])
			&& !empty($bank_chk['end_time_can_not_deposit'])
		){
			try{
				$start_time_can_not_deposit = new DateTime($bank_chk['start_time_can_not_deposit']);
				$end_time_can_not_deposit = new DateTime($bank_chk['end_time_can_not_deposit']);
				$time_current = new DateTime(date('H:i'));

				if( in_array($start_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12"))){
					if(!in_array($end_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
						!in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
					){
						$end_time_can_not_deposit = $end_time_can_not_deposit->add(new DateInterval('P1D'));
					}
					if(
						!in_array($time_current->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
						in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
					){
						$start_time_can_not_deposit = $start_time_can_not_deposit->sub(new DateInterval('P1D'));
					}
					if($start_time_can_not_deposit->getTimestamp() > $end_time_can_not_deposit->getTimestamp()){
						$data['bank'] = "";
					}else if(
						$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
						$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
					){
						if($start_time_can_not_deposit->format("H") == "23" &&  $end_time_can_not_deposit->format("H") == "23" &&
							in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
						){

						}else{
							$data['bank'] = "";
						}

					}else{

					}
				}else if(
					$start_time_can_not_deposit->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
				){
					if(
						$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
						$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
					){
						$data['bank'] = "";
					}else{

					}
				}
			}catch (Exception $ex){
				$data['bank'] = "";
			}
		}

		if($data['bank'] == ""){
			$bank_list = $this->Bank_model->bank_list(['status'=>1,'status_withdraw' => 0,'bank_code_list_not_in'=>["10"]]);
			foreach($bank_list as $bank_chk){
				$chk = false;
				if(
					isset($bank_chk['start_time_can_not_deposit'])
					&& !is_null($bank_chk['start_time_can_not_deposit'])
					&& !empty($bank_chk['start_time_can_not_deposit'])
					&& isset($bank_chk['end_time_can_not_deposit'])
					&& !is_null($bank_chk['end_time_can_not_deposit'])
					&& !empty($bank_chk['end_time_can_not_deposit'])
				){
					try{
						$start_time_can_not_deposit = new DateTime($bank_chk['start_time_can_not_deposit']);
						$end_time_can_not_deposit = new DateTime($bank_chk['end_time_can_not_deposit']);
						$time_current = new DateTime(date('H:i'));

						if( in_array($start_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12"))){
							if(!in_array($end_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
								!in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
							){
								$end_time_can_not_deposit = $end_time_can_not_deposit->add(new DateInterval('P1D'));
							}
							if(
								!in_array($time_current->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
								in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
							){
								$start_time_can_not_deposit = $start_time_can_not_deposit->sub(new DateInterval('P1D'));
							}
							if($start_time_can_not_deposit->getTimestamp() > $end_time_can_not_deposit->getTimestamp()){

							}else if(
								$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
								$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
							){
								if($start_time_can_not_deposit->format("H") == "23" &&  $end_time_can_not_deposit->format("H") == "23" &&
									in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
								){
									$chk = true;
								}else{

								}
							}else{
								$chk = true;
							}
						}else if(
							$start_time_can_not_deposit->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
						){
							if(
								$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
								$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
							){

							}else{
								$chk = true;
							}
						}
					}catch (Exception $ex){

					}
				}else{
					$chk = true;
				}
				if($chk){
					$data['bank'] = $bank_chk;
					break;
				}
			}
		}

		$bank_all_chk = $data['bank_all'];
		if(
			$bank_all_chk != "" &&
			isset($bank_all_chk['start_time_can_not_deposit'])
			&& !is_null($bank_all_chk['start_time_can_not_deposit'])
			&& !empty($bank_all_chk['start_time_can_not_deposit'])
			&& isset($bank_all_chk['end_time_can_not_deposit'])
			&& !is_null($bank_all_chk['end_time_can_not_deposit'])
			&& !empty($bank_all_chk['end_time_can_not_deposit'])
		){
			try{
				$start_time_can_not_deposit = new DateTime($bank_all_chk['start_time_can_not_deposit']);
				$end_time_can_not_deposit = new DateTime($bank_all_chk['end_time_can_not_deposit']);
				$time_current = new DateTime(date('H:i'));

				if( in_array($start_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12"))){
					if(!in_array($end_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
						!in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
					){
						$end_time_can_not_deposit = $end_time_can_not_deposit->add(new DateInterval('P1D'));
					}
					if(
						!in_array($time_current->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
						in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
					){
						$start_time_can_not_deposit = $start_time_can_not_deposit->sub(new DateInterval('P1D'));
					}
					if($start_time_can_not_deposit->getTimestamp() > $end_time_can_not_deposit->getTimestamp()){
						$data['bank_all'] = "";
					}else if(
						$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
						$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
					){
						if($start_time_can_not_deposit->format("H") == "23" &&  $end_time_can_not_deposit->format("H") == "23" &&
							in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
						){

						}else{
							$data['bank_all'] = "";
						}

					}else{

					}
				}else if(
					$start_time_can_not_deposit->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
				){
					if(
						$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
						$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
					){
						$data['bank_all'] = "";
					}else{

					}
				}
			}catch (Exception $ex){
				$data['bank_all'] = "";
			}
		}

		if($data['bank_all'] == ""){
			$bank_list = $this->Bank_model->bank_list(['status'=>1,'status_withdraw' => 0,'bank_code_list_not_in'=>["10"]]);
			foreach($bank_list as $bank_all_chk){
				$chk = false;
				if(
					isset($bank_all_chk['start_time_can_not_deposit'])
					&& !is_null($bank_all_chk['start_time_can_not_deposit'])
					&& !empty($bank_all_chk['start_time_can_not_deposit'])
					&& isset($bank_all_chk['end_time_can_not_deposit'])
					&& !is_null($bank_all_chk['end_time_can_not_deposit'])
					&& !empty($bank_all_chk['end_time_can_not_deposit'])
				){
					try{
						$start_time_can_not_deposit = new DateTime($bank_all_chk['start_time_can_not_deposit']);
						$end_time_can_not_deposit = new DateTime($bank_all_chk['end_time_can_not_deposit']);
						$time_current = new DateTime(date('H:i'));

						if( in_array($start_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12"))){
							if(!in_array($end_time_can_not_deposit->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
								!in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
							){
								$end_time_can_not_deposit = $end_time_can_not_deposit->add(new DateInterval('P1D'));
							}
							if(
								!in_array($time_current->format("H"),array("23","22","21","20","19","18","17","16","15","14","13","12")) &&
								in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
							){
								$start_time_can_not_deposit = $start_time_can_not_deposit->sub(new DateInterval('P1D'));
							}
							if($start_time_can_not_deposit->getTimestamp() > $end_time_can_not_deposit->getTimestamp()){

							}else if(
								$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
								$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
							){
								if($start_time_can_not_deposit->format("H") == "23" &&  $end_time_can_not_deposit->format("H") == "23" &&
									in_array($time_current->format("H"),array("00","01","02","03","04","05","06"))
								){
									$chk = true;
								}else{

								}
							}else{
								$chk = true;
							}
						}else if(
							$start_time_can_not_deposit->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
						){
							if(
								$time_current->getTimestamp() >= $start_time_can_not_deposit->getTimestamp() &&
								$time_current->getTimestamp() <= $end_time_can_not_deposit->getTimestamp()
							){

							}else{
								$chk = true;
							}
						}
					}catch (Exception $ex){

					}
				}else{
					$chk = true;
				}
				if($chk){
					$data['bank_all'] = $bank_all_chk;
					break;
				}
			}
		}

    if($data['bank'] == ""){
		    $data['bank'] = $this->Bank_model->bank_find(['status'=>1,'status_withdraw' => 0,'bank_code_list_not_in'=>["null","10"]]);
			//$data['bank'] = $this->Bank_model->bank_find(['status'=>2,'status_withdraw' => 0,'bank_code_list_not_in'=>["02","2","10"]]);
		}
		var_dump($data);
     	// $bank_promptpay = $this->Bank_model->promptpay();
     	// $data['promptpay'] = $bank_promptpay['promptpay_status'];
		// $data['header_menu'] = 'header_menu';
		// $data['middle_bar'] = 'middle_bar';
		// $data['footer_menu'] = 'footer_menu';
		// $data['back_btn'] = true;
		// $data['back_url'] = base_url('dashboard');
		// $data['page'] = 'deposit';
		// $data['footer_menu'] = 'footer_menu';
		// $this->load->view('main', $data);
    }
}
