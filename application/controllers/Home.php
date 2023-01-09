<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'../vendor/autoload.php';
use \Curl\Curl;

class Home extends CI_Controller
{
	protected $code_login = array(
		'sa_game' => 'sa',
		'sexy_game' => 'sexy',
		'dream_game' => 'dg',
		'pretty_game' => 'pt',
		'ag_game' => 'aggame',
		'allbet_game' => 'allbet',
		'live22_game' => 'live22',
		'ameba_game' => 'ameba',
		'spg_game' => 'spg',
		'ganapati_game' => 'ganapati',
		'pg_game' => 'pg',
		'ambgame_game' => 'ambgame',
		'slotxo_game' => 'slotxo',
		'askmebetslot_game' => 'askmebetslot',
		'evoplay_game' => 'evoplay',
		'ebet_game' => 'ebet',
		'bg_game' => 'bg',
		'betgame_game' => 'betgame',
		'greendragon_game' => 'greendragon',
		'pragmatic_game' => 'pragmatic',
		'keno_game' => 'keno',
		'atom_game' => 'atom',
		'number_game' => 'number',
		'kagaming_game' => 'kagaming',
		'allwayspin_game' => 'allwayspin',
		'booongo_game' => 'booongo',
		'iconicgaming_game' => 'iconicgaming',
		'wazdandirect_game' => 'wazdandirect',
		'funtagaming_game' => 'funtagaming',
		'funkygame_game' => 'funkygame',
		'mannaplay_game' => 'mannaplay',
		'pragmaticslot_game' => 'pragmaticslot',
		'ambslot_game' => 'ambslot',
		'hotgraph_game' => 'hotgraph',
		'jili_game' => 'jili',
		'lotto_game' => 'lotto',
		'simpleplay_game' => 'simpleplay',
		'microgame_game' => 'microgame',
		'yggdrasil_game' => 'yggdrasil',
		'upgslot_game' => 'upgslot',
		'p8_game' => 'p8',
		'tfgaming_game' => 'tfgaming',
		'mpoker_game' => 'mpoker',
		'avgaming_game' => 'avgaming',
		'dragongaming_game' => 'dragongaming',
		'i8_game' => 'i8',
		'kingmaker_game' => 'kingmaker',
		'kingpoker_game' => 'kingpoker',
		'mega7_game' => 'mega7',
		'relaxgame_game' => 'relaxgame',
		'slotracha' => 'Slotracha',
		'spinix_game' => 'Spinix',
		'advantplay_game' => 'Advantplay',
		'ninjaslot_game' => 'Ninjaslot',
		'ace333_game' => 'Ace333',
		'cq9_game' => 'Cq9',
		'wmslot_game' => 'Wmslot',
		'sboslot_game' => 'Sboslot',
		'wecasino_game' => 'Wecasino',
		'iampoker_game' => 'Rekop',
		'wmcasino_game' => 'Wmcasino',
	);
	public function __construct()
	{
		date_default_timezone_set('Asia/Bangkok');
		parent::__construct();
		$language = $this->session->userdata('language');
		$this->lang->load('text', $language);
	}
	public function index()
	{
		$this->check_login();
		//$this->user_agent_update_commission();
		$data['header_menu'] = 'header_menu';
		$data['middle_bar'] = 'middle_bar';
		$data['back_btn'] = false;
		$data['page'] = 'dashboard';
		$data['user'] = [
			'amount_deposit_auto' => 0.00,
			'username' => $_SESSION['user']['username'],
			'agent' => $_SESSION['user']['agent'],
		];
		$data['news'] = $this->New_model->new_list([
			'status' => 1,
			'status_alert' => 1,
		]);
		$data['footer_menu'] = 'footer_menu';
		$this->load->view('main', $data);
	}
	public function profile()
	{
		$this->check_login();
		$data['header_menu'] = 'header_menu';
		$data['middle_bar'] = 'middle_bar';
		$data['user'] = $this->Account_model->account_find_chk_fast([
			'id' => $_SESSION['user']['id']
		]);
		$data['back_btn'] = true;
		$data['footer_menu'] = 'footer_menu';
		$data['back_url'] = base_url('dashboard?tab=account');
		$data['page'] = 'profile';
		$this->load->view('main', $data);
	}
	public function promotion()
	{
		$this->check_login();
		$data['header_menu'] = 'header_menu';
		$data['middle_bar'] = 'middle_bar';
		$data['promotions'] = $this->Promotion_model->promotion_list([
			'status' => 1
		]);
		$data['user'] = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		$data['back_btn'] = true;
		$data['footer_menu'] = 'footer_menu';
		$data['back_url'] = base_url('wallet');
		$data['page'] = 'promotion';
		$this->load->view('main', $data);
	}
	public function news()
	{
		$this->check_login();
		$data['header_menu'] = 'header_menu';
		$data['middle_bar'] = 'middle_bar';
		$data['news'] = $this->New_model->new_list([
			'status' => 1
		]);
		$data['user'] = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		$data['footer_menu'] = 'footer_menu';
		$data['back_btn'] = true;
		$data['back_url'] = base_url('wallet');
		$data['page'] = 'new';
		$this->load->view('main', $data);
	}

	public function history()
	{
		$this->check_login();
		$data['header_menu'] = 'header_menu';
		$data['middle_bar'] = 'middle_bar';
		/*$data['histories'] = $this->Finance_model->finance_list([
			'account' => $_SESSION['user']['id']
		]);*/
		$data['user'] = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		$data['footer_menu'] = 'footer_menu';
		$data['back_btn'] = true;
		$data['back_url'] = base_url('wallet');
		$data['page'] = 'history';
		$this->load->view('main', $data);

	}

	private function check_login()
	{

		if (!isset($_SESSION['user'])) {
			session_destroy();
			redirect('auth');
		}
		$user = $_SESSION['user'];
		if (empty($user)) {
			session_destroy();
			redirect('auth');
			exit();
		}
		unset($_SESSION['line_login_chk']);
		unset($_SESSION['line_login_user_id']);
		unset($_SESSION['line_login_aff']);
	}
	public function ref()
	{
		$this->check_login();
		$data['user'] = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		if($data['user']['agent'] == "1"){
			redirect('agent');
			exit();
		}
		$data['count_ref'] = $this->Ref_model->ref_count([
			'from_account' => $data['user']['id']
		]);
		$data['count_commission'] = $this->Wallet_ref_deposit_model->wallet_ref_deposit_sum([
			'account' => $data['user']['id']
		]);
		$data['count_commission'] = $data['count_commission'] != "" ? $data['count_commission']['amount_wallet_ref'] : 0.00;
		$data['header_menu'] = 'header_menu';
		$data['middle_bar'] = 'middle_bar';
		$data['footer_menu'] = 'footer_menu';
		$data['back_btn'] = true;
		$data['back_url'] = base_url('dashboard');
		$data['page'] = 'ref';
		$this->load->view('main', $data);

	}

	public function opengame(){
		$data_token = base64_decode($_GET['token']);
		$data_explode = explode("###",$data_token);
		$data['user'] = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		$data['page'] = 'play';
		$data['title'] = $data_explode[1];
		$data['game_code'] = $data_explode[2];
		$data['url'] = $data_explode[0];
		$data['footer_menu'] = 'footer_menu';
		$this->load->view('main', $data);
	}

	public function playOnce($game){
		$isMobile = isset($_GET['isMobile']) ? $_GET['isMobile'] : null;
		$game_list = array(
			'sa_game' => 'Sa Gamimg',
			'sexy_game' => 'Sexy Gamimg',
			'dream_game' => 'Dream Gaming',
			'pretty_game' => 'Pretty Gaming',
			'ag_game' => 'AG Gaming',
			'ebet_game' => 'EBet Gaming',
			'allbet_game' => 'AllBet Gaming',
			'bg_game' => 'Big Game Gaming',
			'betgame_game' => 'BetGame Gaming',
			'greendragon_game' => 'GreenDragon Gaming',
			'pragmatic_game' => 'Pragmatic Gaming',
			'keno_game' => 'Keno Gaming',
			'atom_game' => 'Atom Gaming',
			'number_game' => 'Number Gaming',
			'kagaming_game' => 'KA Gaming',
			'allwayspin_game' => 'AllWaySpin Gaming',
			'booongo_game' => 'Booongo Gaming',
			'iconicgaming_game' => 'Iconic Gaming',
			'wazdandirect_game' => 'WazdanDirect Gaming',
			'funtagaming_game' => 'Funta Gaming',
			'funkygame_game' => 'Funky Gaming',
			'mannaplay_game' => 'Mannaplay Gaming',
			'pragmaticslot_game' => 'PragmaticSlot Gaming',
			'ambslot_game' => 'Ambslot Gaming',
			'hotgraph_game' => 'HotGraph Gaming',
			'jili_game' => 'Jili Gaming',
			'lotto_game' => 'AMBLotto Gaming',
			'simpleplay_game' => 'SimplePLay Gaming',
			'microgame_game' => 'Micro Gaming',
			'yggdrasil_game' => 'Yggdrasil Gaming',
			'upgslot_game' => 'UPGslot Gaming',
			'p8_game' => 'P8 Gaming',
			'tfgaming_game' => 'E-Sport Gaming',
			'mpoker_game' => 'M-Poker Gaming',
			'avgaming_game' => 'Cherry Gaming',
			'dragongaming_game' => 'Dragon Gaming',
			'i8_game' => 'I8 Gaming',
			'kingmaker_game' => 'King Maker Gaming',
			'kingpoker_game' => 'King Poker Gaming',
			'mega7_game' => 'Mega7 Gaming',
			'relaxgame_game' => 'Relax Gaming',
			'slotracha' => 'Slotracha',
			'spinix_game' => 'Spinix',
			'advantplay_game' => 'Advantplay',
			'ninjaslot_game' => 'Ninjaslot',
			'ace333_game' => 'Ace333',
			'cq9_game' => 'Cq9',
			'wmslot_game' => 'Wmslot',
			'sboslot_game' => 'Sboslot',
			'wecasino_game' => 'Wecasino',
			'iampoker_game' => 'Rekop',
			'wmcasino_game' => 'Wmcasino',
		);
		if(array_key_exists($game,$game_list)){
			$data['user'] = $_SESSION['user'];
			if(empty($data['user']['member_username'])){
				echo json_encode([
					'message' => 'success',
					'result' => true,
					'url' => ''
				]);
				exit();
			}
			$data['user']['account_agent_username'] = $data['user']['member_username'];
			$data['user']['account_agent_password'] = $data['user']['member_password'];
			$url = $this->game_api_librarie->playGame($this->code_login[strtolower($game)],$data['user'],"",$isMobile);
			if(!empty($url) && !is_array($url)){
				echo json_encode([
					'message' => 'success',
					'result' => true,
					'url' => base64_encode($url."###".$_GET['title']."###".str_replace("_game","",strtolower($game)))
				]);
			}else{
				$message = isset($url['message']) ? $url['message'] : "";
				echo json_encode([
					'message' => 'success',
					'result' => true,
					'res' => $message,
					'url' => ''
				]);
			}
		}else{
			echo json_encode([
				'message' => 'success',
				'result' => true,
				'url' => ''
			]);
		}
	}
	public function play($game,$game_id){
		$isMobile = isset($_GET['isMobile']) ? $_GET['isMobile'] : null;
		$game_list = array(
			'live22_game' => 'Live22 Gaming',
			'ameba_game' => 'Ameba Gaming',
			'spg_game' => 'Spade Gaming',
			'ganapati_game' => 'Gamatron Gaming',
			'pg_game' => 'PG Gaming',
			'ambgame_game' => 'AMB Gaming',
			'slotxo_game' => 'SlotXO Gaming',
			'askmebetslot_game' => 'Askmebet Gaming',
			'evoplay_game' => 'Evoplay Gaming',
			'bg_game' => 'Big Game Gaming',
			'betgame_game' => 'BetGame Gaming',
			'greendragon_game' => 'GreenDragon Gaming',
			'pragmatic_game' => 'Pragmatic Gaming',
			'keno_game' => 'Keno Gaming',
			'atom_game' => 'Atom Gaming',
			'number_game' => 'Number Gaming',
			'kagaming_game' => 'KA Gaming',
			'allwayspin_game' => 'AllWaySpin Gaming',
			'booongo_game' => 'Booongo Gaming',
			'iconicgaming_game' => 'Iconic Gaming',
			'wazdandirect_game' => 'WazdanDirect Gaming',
			'funtagaming_game' => 'Funta Gaming',
			'funkygame_game' => 'Funky Gaming',
			'mannaplay_game' => 'Mannaplay Gaming',
			'pragmaticslot_game' => 'PragmaticSlot Gaming',
			'ambslot_game' => 'Ambslot Gaming',
			'hotgraph_game' => 'HotGraph Gaming',
			'jili_game' => 'Jili Gaming',
			'lotto_game' => 'AMBLotto Gaming',
			'simpleplay_game' => 'SimplePLay Gaming',
			'microgame_game' => 'Micro Gaming',
			'yggdrasil_game' => 'Yggdrasil Gaming',
			'upgslot_game' => 'UPGslot Gaming',
			'p8_game' => 'P8 Gaming',
			'tfgaming_game' => 'E-Sport Gaming',
			'mpoker_game' => 'M-Poker Gaming',
			'avgaming_game' => 'Cherry Gaming',
			'dragongaming_game' => 'Dragon Gaming',
			'i8_game' => 'I8 Gaming',
			'kingmaker_game' => 'King Maker Gaming',
			'kingpoker_game' => 'King Poker Gaming',
			'mega7_game' => 'Mega7 Gaming',
			'relaxgame_game' => 'Relax Gaming',
			'slotracha' => 'Slotracha',
			'spinix_game' => 'Spinix',
			'advantplay_game' => 'Advantplay',
			'ninjaslot_game' => 'Ninjaslot',
			'ace333_game' => 'Ace333',
			'cq9_game' => 'Cq9',
			'wmslot_game' => 'Wmslot',
			'sboslot_game' => 'Sboslot',
			'wecasino_game' => 'Wecasino',
			'iampoker_game' => 'Rekop',
			'wmcasino_game' => 'Wmcasino',
		);
		if(array_key_exists($game,$game_list)){
			$data['user'] = $_SESSION['user'];
			if(empty($data['user']['member_username'])){
				echo json_encode([
					'message' => 'success',
					'result' => true,
					'url' => ''
				]);
				exit();
			}
			$data['user']['account_agent_username'] = $data['user']['member_username'];
			$data['user']['account_agent_password'] = $data['user']['member_password'];
			$url = $this->game_api_librarie->playGame($this->code_login[strtolower($game)],$data['user'],$game_id,$isMobile);
			if(!empty($url) && !is_array($url)){
				echo json_encode([
					'message' => 'success',
					'result' => true,
					'url' => base64_encode($url."###".$_GET['title']."###".str_replace("_game","",strtolower($game)))
				]);
			}else{
				$message = isset($url['message']) ? $url['message'] : "";
				echo json_encode([
					'message' => 'success',
					'result' => true,
					'res' => $message,
					'url' => ''
				]);
			}

		}else{
			echo json_encode([
				'message' => 'success',
				'result' => true,
				'url' => ''
			]);
		}
	}
	public function lobby($game)
	{
		$this->check_login();
		$game_list = array(
			'live22_game' => 'Live22 Gaming',
			'ameba_game' => 'Ameba Gaming',
			'spg_game' => 'Spade Gaming',
			'ganapati_game' => 'Gamatron Gaming',
			'pg_game' => 'PG Gaming',
			'ambgame_game' => 'AMB Gaming',
			'slotxo_game' => 'SlotXO Gaming',
			'askmebetslot_game' => 'Askmebet Gaming',
			'evoplay_game' => 'Evoplay Gaming',
			'bg_game' => 'Big Game Gaming',
			'betgame_game' => 'BetGame Gaming',
			'greendragon_game' => 'GreenDragon Gaming',
			'pragmatic_game' => 'Pragmatic Gaming',
			'keno_game' => 'Keno Gaming',
			'atom_game' => 'Atom Gaming',
			'number_game' => 'Number Gaming',
			'kagaming_game' => 'KA Gaming',
			'allwayspin_game' => 'AllWaySpin Gaming',
			'booongo_game' => 'Booongo Gaming',
			'iconicgaming_game' => 'Iconic Gaming',
			'wazdandirect_game' => 'WazdanDirect Gaming',
			'funtagaming_game' => 'Funta Gaming',
			'funkygame_game' => 'Funky Gaming',
			'mannaplay_game' => 'Mannaplay Gaming',
			'pragmaticslot_game' => 'PragmaticSlot Gaming',
			'ambslot_game' => 'Ambslot Gaming',
			'hotgraph_game' => 'HotGraph Gaming',
			'jili_game' => 'Jili Gaming',
			'lotto_game' => 'AMBLotto Gaming',
			'simpleplay_game' => 'SimplePLay Gaming',
			'microgame_game' => 'Micro Gaming',
			'yggdrasil_game' => 'Yggdrasil Gaming',
			'upgslot_game' => 'UPGslot Gaming',
			'p8_game' => 'P8 Gaming',
			'tfgaming_game' => 'E-Sport Gaming',
			'mpoker_game' => 'M-Poker Gaming',
			'avgaming_game' => 'Cherry Gaming',
			'dragongaming_game' => 'Dragon Gaming',
			'i8_game' => 'I8 Gaming',
			'kingmaker_game' => 'King Maker Gaming',
			'kingpoker_game' => 'King Poker Gaming',
			'mega7_game' => 'Mega7 Gaming',
			'relaxgame_game' => 'Relax Gaming',
			'slotracha' => 'Slotracha',
			'spinix_game' => 'Spinix',
			'advantplay_game' => 'Advantplay',
			'ninjaslot_game' => 'Ninjaslot',
			'ace333_game' => 'Ace333',
			'cq9_game' => 'Cq9',
			'wmslot_game' => 'Wmslot',
			'sboslot_game' => 'Sboslot',
			'wecasino_game' => 'Wecasino',
			'iampoker_game' => 'Rekop',
			'wmcasino_game' => 'Wmcasino',
		);
		if(array_key_exists($game,$game_list)){
			$response = $this->game_api_librarie->getGameList($this->code_login[strtolower($game)]);
			$data['header_menu'] = 'header_menu';
			$data['middle_bar'] = 'middle_bar';
			$data['page'] = 'lobby';
			$data['game_list'] = $response;
			$data['game_name'] = $game_list[$game];
			$data['game_code'] = strtolower($game);
			$data['user'] = $this->Account_model->account_find([
				'id' => $_SESSION['user']['id']
			]);
			$data['footer_menu'] = 'footer_menu';
			$this->load->view('main', $data);
		}else{
			redirect('dashboard');
		}
	}

	public function play_game($game_code,$game_code_id){
		$data['user'] = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		$data['header_menu'] = 'header_menu';
		$data['middle_bar'] = 'middle_bar';
		$data['back_btn'] = true;
		$data['page'] = 'play_game_loader';
		$game_list = array(
			'live22_game' => 'Live22 Gaming',
			'ameba_game' => 'Ameba Gaming',
			'spg_game' => 'Spade Gaming',
			'ganapati_game' => 'Gamatron Gaming',
			'pg_game' => 'PG Gaming',
			'ambgame_game' => 'AMB Gaming',
			'slotxo_game' => 'SlotXO Gaming',
			'askmebetslot_game' => 'Askmebet Gaming',
			'evoplay_game' => 'Evoplay Gaming',
			'bg_game' => 'Big Game Gaming',
			'betgame_game' => 'BetGame Gaming',
			'greendragon_game' => 'GreenDragon Gaming',
			'pragmatic_game' => 'Pragmatic Gaming',
			'keno_game' => 'Keno Gaming',
			'atom_game' => 'Atom Gaming',
			'number_game' => 'Number Gaming',
			'kagaming_game' => 'KA Gaming',
			'allwayspin_game' => 'AllWaySpin Gaming',
			'booongo_game' => 'Booongo Gaming',
			'iconicgaming_game' => 'Iconic Gaming',
			'wazdandirect_game' => 'WazdanDirect Gaming',
			'funtagaming_game' => 'Funta Gaming',
			'funkygame_game' => 'Funky Gaming',
			'mannaplay_game' => 'Mannaplay Gaming',
			'pragmaticslot_game' => 'PragmaticSlot Gaming',
			'ambslot_game' => 'Ambslot Gaming',
			'hotgraph_game' => 'HotGraph Gaming',
			'jili_game' => 'Jili Gaming',
			'lotto_game' => 'AMBLotto Gaming',
			'simpleplay_game' => 'SimplePLay Gaming',
			'microgame_game' => 'Micro Gaming',
			'yggdrasil_game' => 'Yggdrasil Gaming',
			'upgslot_game' => 'UPGslot Gaming',
			'p8_game' => 'P8 Gaming',
			'tfgaming_game' => 'E-Sport Gaming',
			'mpoker_game' => 'M-Poker Gaming',
			'avgaming_game' => 'Cherry Gaming',
			'dragongaming_game' => 'Dragon Gaming',
			'i8_game' => 'I8 Gaming',
			'kingmaker_game' => 'King Maker Gaming',
			'kingpoker_game' => 'King Poker Gaming',
			'mega7_game' => 'Mega7 Gaming',
			'relaxgame_game' => 'Relax Gaming',
			'slotracha' => 'Slotracha',
			'spinix_game' => 'Spinix',
			'advantplay_game' => 'Advantplay',
			'ninjaslot_game' => 'Ninjaslot',
			'ace333_game' => 'Ace333',
			'cq9_game' => 'Cq9',
			'wmslot_game' => 'Wmslot',
			'sboslot_game' => 'Sboslot',
			'wecasino_game' => 'Wecasino',
			'iampoker_game' => 'Rekop',
			'wmcasino_game' => 'Wmcasino',
		);
		$data['type'] = "lobby";
		$data['game_title'] = array_key_exists($game_code,$game_list) ? $game_list[$game_code] : "";
		$data['game_code'] = $game_code;
		$data['game_code_id'] = $game_code_id;
		$data['footer_menu'] = 'footer_menu';
		$this->load->view('main', $data);
	}

	public function user_agent_update_commission()
	{
		$user = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		if ($user['agent']==1) {
			$date = date('Y-m-d', strtotime('-1 months'));
			$date = new DateTime($date);
			$agent_commission_find = $this->Agent_commission_model->agent_commission_find([
				'account' => $user['id'],
				'month' => $date->format('m'),
				'year' => $date->format('Y'),
			]);
			if ($agent_commission_find == null || $agent_commission_find=="") {
				$commission_finance_find = $this->Finance_model->commission_finance_find([
					'account' => $user['id'],
					'month' => $date->format('m'),
					'year' => $date->format('Y'),
				]);
				$commission_finance_sum = $this->Finance_model->commission_finance_sum([
					'account' => $user['id'],
					'month' => $date->format('m'),
					'year' => $date->format('Y'),
				]);
				if ($commission_finance_find=="") {
					$this->Agent_commission_model->agent_commission_create([
						'account' => $user['id'],
						'deposit' => 0,
						'withdraw' => 0,
						'percent' => $user['commission_percent'],
						'month' => $date->format('m'),
						'year' => $date->format('Y'),
					]);
				} else {
					$this->Agent_commission_model->agent_commission_create([
						'account' => $user['id'],
						'deposit' => $commission_finance_sum['deposit'],
						'withdraw' => $commission_finance_sum['withdraw'],
						'percent' => $user['commission_percent'],
						'month' => $date->format('m'),
						'year' => $date->format('Y'),
					]);
				}
			}
		}
	}

	public function play_wheel()
	{
		$this->check_login();
		$feature_wheel = $this->Feature_status_model->setting_find([
			'name' => 'wheel'
		]);
		if($feature_wheel == "" || $feature_wheel['value'] == "0"){
			redirect('dashboard');
			exit();
		}
		$data['header_menu'] = 'header_menu';
		$data['middle_bar'] = 'middle_bar';
		$data['user'] = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		$data['footer_menu'] = 'footer_menu';
		$data['page'] = 'play_wheel';
		$this->load->view('main', $data);
	}

	public function game()
	{
		$this->check_login();
		$data['header_menu'] = 'header_menu';
		$data['middle_bar'] = 'middle_bar';
		$data['user'] = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		$data['footer_menu'] = 'footer_menu';
		$data['page'] = 'game';
		$this->load->view('main', $data);
	}
	public function play_game_open(){
		$data['header_menu'] = 'header_menu';
		$data['middle_bar'] = 'middle_bar';
		$data_token = base64_decode($_GET['token']);
		$data_explode = explode("###",$data_token);
		$data['page'] = 'open_link';
		$data['url'] = $data_explode[0];
		$data['footer_menu'] = 'footer_menu';
		$this->load->view('main', $data);
		//redirect($data_explode[0]);
	}
	public function play_game_once($game_code){
		$this->check_login();
		$data['user'] = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		$data['header_menu'] = 'header_menu';
		$data['middle_bar'] = 'middle_bar';
		$data['page'] = 'play_game_loader';
		$game_list = array(
			'sa_game' => 'Sa Gamimg',
			'sexy_game' => 'Sexy Gamimg',
			'dream_game' => 'Dream Gaming',
			'pretty_game' => 'Pretty Gaming',
			'ag_game' => 'AG Gaming',
			'ebet_game' => 'EBet Gaming',
			'allbet_game' => 'Allbet Gaming',
			'bg_game' => 'Big Game Gaming',
			'betgame_game' => 'BetGame Gaming',
			'greendragon_game' => 'GreenDragon Gaming',
			'pragmatic_game' => 'Pragmatic Gaming',
			'keno_game' => 'Keno Gaming',
			'atom_game' => 'Atom Gaming',
			'number_game' => 'Number Gaming',
			'kagaming_game' => 'KA Gaming',
			'allwayspin_game' => 'AllWaySpin Gaming',
			'booongo_game' => 'Booongo Gaming',
			'iconicgaming_game' => 'Iconic Gaming',
			'wazdandirect_game' => 'WazdanDirect Gaming',
			'funtagaming_game' => 'Funta Gaming',
			'funkygame_game' => 'Funky Gaming',
			'mannaplay_game' => 'Mannaplay Gaming',
			'pragmaticslot_game' => 'PragmaticSlot Gaming',
			'ambslot_game' => 'Ambslot Gaming',
			'hotgraph_game' => 'HotGraph Gaming',
			'jili_game' => 'Jili Gaming',
			'lotto_game' => 'AMBLotto Gaming',
			'simpleplay_game' => 'SimplePLay Gaming',
			'microgame_game' => 'Micro Gaming',
			'yggdrasil_game' => 'Yggdrasil Gaming',
			'upgslot_game' => 'UPGslot Gaming',
			'p8_game' => 'P8 Gaming',
			'tfgaming_game' => 'E-Sport Gaming',
			'mpoker_game' => 'M-Poker Gaming',
			'avgaming_game' => 'Cherry Gaming',
			'dragongaming_game' => 'Dragon Gaming',
			'i8_game' => 'I8 Gaming',
			'kingmaker_game' => 'King Maker Gaming',
			'kingpoker_game' => 'King Poker Gaming',
			'mega7_game' => 'Mega7 Gaming',
			'relaxgame_game' => 'Relax Gaming',
			'slotracha' => 'Slotracha',
			'spinix_game' => 'Spinix',
			'advantplay_game' => 'Advantplay',
			'ninjaslot_game' => 'Ninjaslot',
			'ace333_game' => 'Ace333',
			'cq9_game' => 'Cq9',
			'wmslot_game' => 'Wmslot',
			'sboslot_game' => 'Sboslot',
			'wecasino_game' => 'Wecasino',
			'iampoker_game' => 'Rekop',
			'wmcasino_game' => 'Wmcasino',
		);
		$data['type'] = "";
		if(strtolower($game_code) == "ambbet"){
			$chk_reset_pass = $this->game_api_librarie->resetPass([
				'account_agent_username' => $data['user']['account_agent_username'],
				'account_agent_password' => $data['user']['account_agent_password'],
			]);
			if($chk_reset_pass){

				if(!empty($this->config->item('api_domain_play_game'))){
					redirect('https://'.$this->config->item('api_domain_play_game').'/login/auto/?username='.$data['user']['account_agent_username'].'&password='.$data['user']['account_agent_password'].'&url=https://'.$this->config->item('domain_name').'&hash='.$this->config->item('api_hash').'&state=sport&lang=th');
				}else{
					if(strpos($this->config->item('api_domain'),"mvp168") !== false){
						redirect('https://mvpatm168.com/login/auto/?username='.$data['user']['account_agent_username'].'&password='.$data['user']['account_agent_password'].'&url=https://'.$this->config->item('domain_name').'&hash='.$this->config->item('api_hash').'&state=sport&lang=th');
					}else if(strpos($this->config->item('api_domain'),"scc777") !== false){
						redirect('https://scc777.net/login/auto/?username='.$data['user']['account_agent_username'].'&password='.$data['user']['account_agent_password'].'&url=https://'.$this->config->item('domain_name').'&hash='.$this->config->item('api_hash').'&state=sport&lang=th');
					}else{
						redirect('https://ambbets.cloud/login/auto/?username='.$data['user']['account_agent_username'].'&password='.$data['user']['account_agent_password'].'&url=https://'.$this->config->item('domain_name').'&hash='.$this->config->item('api_hash').'&state=sport&lang=th');
					}
				}
			}else{
				redirect('game');
			}
			exit();
		}else{
			$data['game_title'] = array_key_exists($game_code,$game_list) ? $game_list[$game_code] : "";
			$data['game_code'] = $game_code;
			$data['footer_menu'] = 'footer_menu';
			$this->load->view('main', $data);
		}
	}

	public function change_password()
	{
		$this->check_login();
		$data['header_menu'] = 'header_menu';
		$data['middle_bar'] = 'middle_bar';
		$data['user'] = $this->Account_model->account_find([
			'id' => $_SESSION['user']['id']
		]);
		$data['footer_menu'] = 'footer_menu';
		$data['page'] = 'change_password';
		$this->load->view('main', $data);
	}

	public function get_Gamestatus()
	{
		$Game_status = $this->game_api_librarie->Gamestatus();
		// echo gettype($Game_status);
		// echo $Game_status['result'];
		// print_r($Game_status); // work with phpView
		if(isset($Game_status['code']) && $Game_status['code'] == 0 && isset($Game_status['result']) && isset($Game_status['message']) ){
		echo json_encode([
			'message' => 'success',
			'result' => $Game_status['result']
		],JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
		} else {
			echo "empty response";
		}
		// $this->output->cache(1); // cache 1 minute !!!
	}

}
