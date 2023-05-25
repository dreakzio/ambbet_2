<?php
defined('BASEPATH') or exit('No direct script access allowed');

class LogWheel extends CI_Controller
{

	public $menu_service;
	public function __construct()
	{
		date_default_timezone_set('Asia/Bangkok');
		parent::__construct();
		//if (!isset($_SESSION['user'])  || !in_array($_SESSION['user']['role'],[roleAdmin(),roleSuperAdmin()])) {
		if (!isset($_SESSION['user']) || !isset($_SESSION['user']['role'])) {
			redirect('../auth');
		}
		$this->load->library(['Menu_service']);
		if(!$this->menu_service->validate_permission_menu($this->uri)){
			redirect('../auth');
		}
	}

    public function index()
    {
        $data['page'] = 'log/wheel';
        $this->load->view('main', $data);
    }
    public function log_wheel_list_page()
    {
        $get = $this->input->get();
        $search = $get['search']['value'];
        // $dir = $get['order'][0]['dir'];//order
        $per_page = $get['length'];//จำนวนที่แสดงต่อ 1 หน้า
        $page = $get['start'];
        $search_data = [
         'per_page' => $per_page,//left
         'page' => $page,//start,right
        ];
        if ($search!="") {
            $search_data['search'] = $search;
        }
		if(isset($get['date_start']) && isset($get['date_end'])){
			$search_data['date_start'] = $get['date_start'];
			$search_data['date_end'] = $get['date_end'];
		}
        $log_count_all = $this->Log_wheel_model->log_wheel_count();
        $log_count_search = $this->Log_wheel_model->log_wheel_count($search_data);
        $data = $this->Log_wheel_model->log_wheel_list($search_data);
        echo json_encode([
         "draw" => intval($get['draw']),
         "recordsTotal" => intval($log_count_all),
         "recordsFiltered" => intval($log_count_search),
         "data" => $data,
       ]);
    }
}
