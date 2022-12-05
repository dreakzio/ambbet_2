<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ref extends CI_Controller
{

	public function __construct()
	{
		date_default_timezone_set('Asia/Bangkok');
		parent::__construct();
		if (!isset($_SESSION['user'])  || !in_array($_SESSION['user']['role'],[roleAdmin(),roleSuperAdmin()])) {
			redirect('../auth');
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
			'page_name' => "แนะนำเพื่อน",
			'description' => 'หน้ารายการ',
			'page_url' => $currentURL,
		]);
        $data['page'] = 'ref/ref';
        $this->load->view('main', $data);
    }
    public function ref_list_page()
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
        $ref_count_all = $this->Ref_model->ref_count();
        $ref_count_search = $this->Ref_model->ref_count($search_data);
        $data = $this->Ref_model->ref_list_page($search_data);
        echo json_encode([
         "draw" => intval($get['draw']),
         "recordsTotal" => intval($ref_count_all),
         "recordsFiltered" => intval($ref_count_search),
         "data" => $data,
       ]);
    }
    public function bonus()
    {
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "โบนัสแนะนำเพื่อน",
			'description' => 'หน้ารายการ',
			'page_url' => $currentURL,
		]);
        $data['page'] = 'ref/bonus';
        $this->load->view('main', $data);
    }
    public function ref_deposit_list_page()
    {
        $get = $this->input->get();
        $search = $get['search']['value'];
        // $dir = $get['order'][0]['dir'];//order
        $per_page = $get['length'];//จำนวนที่แสดงต่อ 1 หน้า
        $page = $get['start'];
        $search_data = [
         'per_page' => $per_page,//left
         'page' => $page,//start,right
		 'type_list' => ["0","1"]
        ];
        if ($search!="") {
            $search_data['search'] = $search;
        }
		if(isset($get['date_start']) && isset($get['date_end'])){
			$search_data['date_start'] = $get['date_start'];
			$search_data['date_end'] = $get['date_end'];
		}
        $ref_deposit_count_all = $this->Ref_model->ref_deposit_count(['type_list' => ["0","1"]]);
        $ref_deposit_count_search = $this->Ref_model->ref_deposit_count($search_data);
        $data = $this->Ref_model->ref_deposit_list_page($search_data);
        echo json_encode([
         "draw" => intval($get['draw']),
         "recordsTotal" => intval($ref_deposit_count_all),
         "recordsFiltered" => intval($ref_deposit_count_search),
         "data" => $data,
       ]);
    }
}
