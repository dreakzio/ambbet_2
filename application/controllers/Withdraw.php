<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'../vendor/autoload.php';
use \Curl\Curl;
use Rct567\DomQuery\DomQuery;

class Withdraw extends CI_Controller
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

		$data['header_menu'] = 'header_menu';
		$data['middle_bar'] = 'middle_bar';
		$data['user'] = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		$data['withdraw_min_amount'] = $this->Setting_model->setting_find([
			'name' => 'withdraw_min_amount'
		]);
		$data['footer_menu'] = 'footer_menu';
		$data['back_btn'] = true;
		$data['back_url'] = base_url('wallet');
		$data['page'] = 'withdraw';
		$data['footer_menu'] = 'footer_menu';
		$this->load->view('main', $data);
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
	public function withdraw_credit()
	{
		check_parameter([
			'amount',
		], 'POST');
		$post = $this->input->post();
		$user = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		if ($user=="") {
			echo json_encode([
				'message' => "ทำรายการไม่สำเร็จ",
				'error' => true
			]);
			exit();
		}else if(strpos($post['amount'],".") !== false){
			echo json_encode([
				'message' => "ยอดถอนต้องไม่มีทศนิยม",
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
		//ตรวจสอบยอดถอนขั้นต่ำ
		$withdraw_min_amount = $this->Setting_model->setting_find([
			'name' => 'withdraw_min_amount'
		]);
		if($withdraw_min_amount!="" && is_numeric($withdraw_min_amount['value'])){
			$withdraw_min_amount = (float)$withdraw_min_amount['value'];
			if((float)$post['amount'] < $withdraw_min_amount){
				echo json_encode([
					'message' => "ทำรายการไม่สำเร็จ, ยอดถอนต้องมากกว่าหรือเท่ากับ ".number_format($withdraw_min_amount)." บาท",
					'error' => true
				]);
				exit();
			}
		}
		$limit_with = $this->Withdraw_model->withdraw_limit([
			'id' => $_SESSION['user']['id']
		]);
		//จำกัดการถอน ครั้ง/คน/วัน
		$limit_withdraw_per_day = $this->Setting_model->setting_find([
			'name' => 'limit_withdraw_per_day'
		]);
		if($limit_withdraw_per_day!="" && is_numeric($limit_withdraw_per_day['value'])){
			$limit_withdraw_per_day = (int)$limit_withdraw_per_day['value'];
			if($limit_withdraw_per_day <= $limit_with[0]['per_day']){
				echo json_encode([
					'message' => "ทำรายการไม่สำเร็จ, ท่านสามารถถอนได้ไม่เกิน ".number_format($limit_withdraw_per_day)." ครั้ง/วัน",
					'error' => true
				]);
				exit();
			}
		}
		//จำกัดจำนวนเงินที่ถอนได้ สูงสุง/วัน/คน
		$limit_max_withdraw_per_day = $this->Setting_model->setting_find([
			'name' => 'limit_max_withdraw_per_day'
		]);
		if($limit_max_withdraw_per_day!="" && is_numeric($limit_max_withdraw_per_day['value'])){
			$limit_max_withdraw_per_day = (float)$limit_max_withdraw_per_day['value'];
			if($limit_max_withdraw_per_day <= $limit_with[0]['max_limit']){
				echo json_encode([
					'message' => "ทำรายการไม่สำเร็จ, ท่านสามารถถอนได้ไม่เกิน ".number_format($limit_max_withdraw_per_day)." บาท/วัน",
					'error' => true
				]);
				exit();
			}
		}

		$credit_before = $this->remaining_credit($user);
		if ($post['amount']>$credit_before) {
			echo json_encode([
				'message' => "ยอดเงินคงเหลือไม่เพียงพอ",
				'error' => true
			]);
			exit();
		}

		$turn_type = $this->Setting_model->setting_find([
			'name' => 'turn_type'
		]);
		$turn_type = $turn_type != "" ? $turn_type['value'] : 1;
		//if($turn_type == "2"){
		if(false){
			$turnover_user = is_numeric($user['turn_over']) ? (float)$user['turn_over'] : 0.00;
			if ($credit_before<$turnover_user) {
				echo json_encode([
					'message' => "ยอดเครดิตคงเหลือต้องมากกว่าหรือเท่ากับ ".number_format($turnover_user, 2).' ฿',
					'error' => true
				]);
				exit();
			}
		}else{
			$chk_turn_all_pass = false;
			$text_error_turn_all = "";
			$turn_game_code = "turn_over";
			$turnover_data = $this->check_turn_before($user);
			$chk_take_turn_finish = 0;
			$chk_take_turn_finish_html = "";
			$chk_take_turn_finish_html_new = "";
			//ตรวจสอบ turn = 0 all game code
			$chk_turn_zero_all = false;
			foreach (game_code_list() as $game_code) {
				if (
					!$chk_turn_zero_all &&
					(is_numeric($user['turn_over_' . strtolower($game_code)]) && (float)$user['turn_over_' . strtolower($game_code)] >= 1)
				) {
					$chk_turn_zero_all = true;
				}
			}
			if ($chk_turn_zero_all) {
				foreach (game_code_list() as $game_code) {
					if (!$chk_turn_all_pass) {
						if (isset($turnover_data[$game_code]) && isset($user['turn_over_' . strtolower($game_code)]) && (float)$user['turn_over_' . strtolower($game_code)] >= 0) {
							if($turn_type == "2"){
								$turnover_current = $credit_before;
							}else{
								$turnover_current = $turnover_data[$game_code]['amount'];
							}
							if ($turnover_current > (float)$user['turn_before_' . strtolower($game_code)]) {
								$check_turn = $turnover_current - (float)$user['turn_before_' . strtolower($game_code)];
							} else {
								$check_turn = 0;
							}
							if ($check_turn < $user['turn_over_' . strtolower($game_code)]) {

								$text_error_turn_all .= "<p class='mb-0'>เทิร์น " . game_code_text_list()[$game_code] . " คงเหลือ {$check_turn}/{$user['turn_over_' . strtolower($game_code)]}</p>";
								if ($check_turn  > 0 && $check_turn > $chk_take_turn_finish) {
									$chk_take_turn_finish = $check_turn;
									$chk_take_turn_finish_html = "<p class='mb-0'>เทิร์น " . game_code_text_list()[$game_code] . " คงเหลือ {$check_turn}/{$user['turn_over_' . strtolower($game_code)]}</p>";
									$chk_take_turn_finish_html_new = "<p class='mb-0 font-weight-bold text-success'>เทิร์น " . game_code_text_list()[$game_code] . " คงเหลือ {$check_turn}/{$user['turn_over_' . strtolower($game_code)]}</p>";
								}
								/*$turn_over = $user['turn_over']-$check_turn;
								echo json_encode([
									'message' => "เทิร์นคงเหลือ {$check_turn}/{$user['turn_over']}",
									'error' => true
								]);
								exit();*/
							} else {
								$turn_game_code = 'turn_over_' . strtolower($game_code);
								$chk_turn_all_pass = true;
							}
						}
					}
				}
			}
			if (!$chk_turn_all_pass && $chk_turn_zero_all) {
				echo json_encode([
					//'message' => "เทิร์นคงเหลือ {$check_turn}/{$user['turn_over']}",
					'message' => "<h5 class='text-danger font-weight-bold'>** เพียงทำเทิร์นให้ครบบางอย่างเท่านั้น</h5>" . str_replace($chk_take_turn_finish_html, $chk_take_turn_finish_html_new, $text_error_turn_all),
					'error' => true,
					'data' => $turnover_data
				]);
				exit();
			}
		}

		if ($post['amount']>$credit_before) {
			echo json_encode([
				'message' => "ยอดเงินคงเหลือไม่เพียงพอ ".number_format($credit_before, 2).' ฿',
				'error' => true
			]);
			exit();
		}
		$amount_deposit = $_POST['amount'];
		$form_data = [];
		$form_data["account_agent_username"] = $user['account_agent_username'];
		$form_data["amount"] = $amount_deposit;
		$form_data = member_credit_data($form_data);


		//เพิ่ม Logs
		$log_deposit_withdraw_id = $this->Log_deposit_withdraw_model->log_deposit_withdraw_create([
			'account' => $user['id'],
			'username' => $user['username'],
			'amount' => $form_data["amount"],
			'amount_before' => $credit_before,
			'type' => '2', //ถอน
			'description' => 'ลดเครดิต',
			'admin' =>$user['id'],
		]);

		$withdraw_credit_date  = isset($_SESSION['withdraw_credit_date']) ? $_SESSION['withdraw_credit_date'] : null;
		if(!is_null($withdraw_credit_date)){
			try{
				$hiDate = new DateTime($_SESSION['withdraw_credit_date']);
				$loDate = new DateTime(date('Y-m-d H:i:s'));
				$diff = $hiDate->diff($loDate);
				$secs = ((($diff->format("%a") * 24) + $diff->format("%H")) * 60 +
						$diff->format("%i")) * 60 + $diff->format("%s");

				if($secs <= 70){
					echo json_encode([
						'message' => "ท่านทำรายการถอนติดต่อกัน, กรุณารออีก 1 นาที จึงสามารถทำรายการถอนได้ใหม่อีกครั้ง",
						'error' => true
					]);
					exit();
				}
			}catch (Exception $ex){

			}
		}
		$_SESSION['withdraw_credit_date'] = date('Y-m-d H:i:s');
		$response = $this->game_api_librarie->withdraw($form_data);
		if (isset($response['ref'])) {

			if($credit_before >= (float)$user[$turn_game_code]){
				if (
					strtotime(date('Y-m-d H:i'))>=strtotime(date('Y-m-d')." 11:00")
					&&
					strtotime(date('Y-m-d H:i')) <=strtotime(date('Y-m-d')." 23:59")
				) {
					$turn_date = date('Y-m-d');
				} else {
					$turn_date = date('Y-m-d', strtotime('-1 days'));
				}
				if($post['amount']>=$credit_before && $post['amount']<=$credit_before){
					$user['turn_over'] = 0;
					$turn_before = 0;
					foreach(game_code_list() as $game_code){
						${'turn_before_'.strtolower($game_code)} = 0;
						${'turn_over_'.strtolower($game_code)} = 0;
					}
				}else{
					try{
						$turn_date_before = new DateTime($user['turn_date']);
						$turn_date_before = $turn_date_before->format('Y-m-d');
					}catch (Exception $ex){
						$turn_date_before = $turn_date;
					}
					$user['turn_date'] = $turn_date_before;
					$user['date_end'] = $turn_date;
					$turn_data = $this->check_turn_before($user);

					$turn_before = 0;
					foreach(game_code_list() as $game_code){
						if(isset($turn_data[$game_code])){
							$turn_before += (float)$turn_data[$game_code]['amount'];
							${'turn_before_'.strtolower($game_code)} = (float)$turn_data[$game_code]['amount'];
							if(!is_null($user['turn_over_'.strtolower($game_code)]) && !empty($user['turn_over_'.strtolower($game_code)]) && is_numeric($user['turn_over_'.strtolower($game_code)]) && (float)$user['turn_over_'.strtolower($game_code)] >= (float)$turn_data[$game_code]['amount']){
								${'turn_over_'.strtolower($game_code)} = (float)$user['turn_over_'.strtolower($game_code)]-(float)$turn_data[$game_code]['amount'];
							}else{
								${'turn_over_'.strtolower($game_code)} = 0;
							}

						}

					}
					//$turn_before = (float)$user['turn_before'] + (float)$post['amount'];
				}
				if(!is_null($user['turn_over']) && !empty($user['turn_over']) && is_numeric($user['turn_over']) && (float)$user['turn_over'] >= $turn_before){
					$turn_over = (float)$user['turn_over']-$turn_before;
				}else{
					$turn_over = 0;
				}
				$data_user_update = [
					'id' => $user['id'],
					//'turn_over' => 0,
					'turn_before' => $turn_before,
					'turn_over' => $turn_over,
					'turn_date' => $turn_date,
					'sha1_acount' => '',
				];
				foreach(game_code_list() as $game_code){
					$data_user_update['turn_over_'.strtolower($game_code)] = ${'turn_over_'.strtolower($game_code)};
					$data_user_update['turn_before_'.strtolower($game_code)] = ${'turn_before_'.strtolower($game_code)};
				}
				$this->Account_model->account_update($data_user_update);
			}

			$this->Finance_model->finance_create([
				'account' => $user['id'],
				'amount' => $post['amount'],
				'bank' => $user['bank'],
				'bank_number' => $user['bank_number'],
				'bank_name' => $user['bank_name'],
				'username' => $user['username'],
				'type' => 2
			]);

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
				'message' => 'success',
				'result' => true
			]);
		} else {
			$_SESSION['withdraw_credit_date'] = null;
			$log_deposit_withdraw = $this->Log_deposit_withdraw_model->log_deposit_withdraw_find([
				'id' => $log_deposit_withdraw_id
			]);
			if($log_deposit_withdraw!=""){
				$this->Log_deposit_withdraw_model->log_deposit_withdraw_update([
					'id' => $log_deposit_withdraw_id,
					'description' => $log_deposit_withdraw['description']." | ทำรายการไม่สำเร็จ",
				]);
			}

			echo json_encode([
				'message' => 'ทำรายการไม่สำเร็จ',
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
}
