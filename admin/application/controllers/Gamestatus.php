<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Gamestatus extends CI_Controller
{
	public function __construct()
	{
		date_default_timezone_set('Asia/Bangkok');
		parent::__construct();
		if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != roleSuperAdmin()) {
			redirect('../admin');
		}
	}
	public function index()
	{
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "สถานะเกมส์",
			'description' => 'หน้ารายการ',
			'page_url' => $currentURL,
		]);
		$data['page'] = 'gamestatus/gamestatus';
		$this->load->view('main', $data);
	}

	public function new_list_gamestatus()
	{
		$Game_status = $this->game_api_librarie->getGameStatus();
		// echo $Game_status['result'];
		// print_r($Game_status); // work with phpView
		if(isset($Game_status['code']) && $Game_status['code'] == 0 && isset($Game_status['result']) && isset($Game_status['message']) ){
			echo json_encode([
				'message' => 'success',
				'result' => $Game_status['result']
			]);
		} else {
			echo "No cache response from api";
		}
	}
}
