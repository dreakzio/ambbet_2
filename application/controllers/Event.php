<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'../vendor/autoload.php';
use \Curl\Curl;
use Rct567\DomQuery\DomQuery;

class Event extends CI_Controller
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
		$data['footer_menu'] = 'footer_menu';
		$data['back_btn'] = true;
		$data['back_url'] = base_url('dashboard');
		$data['page'] = 'event';
		$data['footer_menu'] = 'footer_menu';
		$this->load->view('main', $data);
    }

	public function checkin()
	{
		$data['header_menu'] = 'header_menu';
		$data['middle_bar'] = 'middle_bar';
		$data['footer_menu'] = 'footer_menu';
		$data['back_btn'] = true;
		$data['user'] = $this->Account_model->account_find_chk_fast([
			'id' => $_SESSION['user']['id']
		]);
		$data['back_url'] = base_url('event');
		$data['page'] = 'event_checkin';
		$data['footer_menu'] = 'footer_menu';
		$this->load->view('main', $data);
	}
}
