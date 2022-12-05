<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Deposit extends CI_Controller
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
			'page_name' => "เครดิต",
			'description' => 'หน้ารายการ',
			'page_url' => $currentURL,
		]);
        $data['page'] = 'deposit/deposit';
        $this->load->view('main', $data);
    }
    public function deposit_list_page()
    {
        $get = $this->input->get();

        $search = $get['search']['value'];
        // $dir = $get['order'][0]['dir'];//order
        $per_page = $get['length'];//จำนวนที่แสดงต่อ 1 หน้า
        $page = $get['start'];
        $search_data = [
         'per_page' => $per_page,//left
         'page' => $page,
         'type' => 1
        ];
        if ($search!="") {
            $search_data['search'] = $search;
        }
		if(isset($get['date_start']) && isset($get['date_end'])){
			$search_data['date_start'] = $get['date_start'];
			$search_data['date_end'] = $get['date_end'];
		}
        $deposit_count_all = $this->Finance_model->finance_count([
        	'type' => 1
		]);
        $deposit_count_search = $this->Finance_model->finance_count($search_data);
        $data = $this->Finance_model->finance_list_page($search_data);
        echo json_encode([
         "draw" => intval($get['draw']),
         "recordsTotal" => intval($deposit_count_all),
         "recordsFiltered" => intval($deposit_count_search),
         "data" => $data,
       ]);
    }

	public function deposit_list_excel()
	{
		$post = $this->input->post();
		$search = $post['search']['value'];
		$search_data = [
			'type' => 1
		];
		if ($search!="") {
			$search_data['search'] = $search;
		}
		if(isset($post['date_start']) && isset($post['date_end'])){
			$search_data['date_start'] = $post['date_start'];
			$search_data['date_end'] = $post['date_end'];
		}
		$data = $this->Finance_model->finance_list_excel($search_data);
		echo json_encode([
			"data" => $data,
		]);
	}

    public function deposit_form_detail($id = "")
    {
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "เครดิต",
			'description' => 'หน้ารายละเอียด',
			'page_url' => $currentURL,
		]);
        $data['deposit'] = $this->Finance_model->finance_find([
          'id' => $id
        ]);
        $data['page'] = 'deposit/deposit_detail';
        $this->load->view('main', $data);
    }
    public function deposit_list_page_manage_transaction($account_id)
    {
        $get = $this->input->get();

        $search = $get['search']['value'];
        // $dir = $get['order'][0]['dir'];//order
        $per_page = $get['length'];//จำนวนที่แสดงต่อ 1 หน้า
        $page = $get['start'];
        $search_data = [
         'per_page' => $per_page,//left
         'page' => $page,
         'type' => 1
        ];
        if ($search!="") {
            $search_data['search'] = $search;
        }
		if(isset($get['date_start']) && isset($get['date_end'])){
			$search_data['date_start'] = $get['date_start'];
			$search_data['date_end'] = $get['date_end'];
		}
        $search_data['account'] = $account_id;
        $deposit_count_all = $this->Finance_model->finance_count([
        	'type' => 1,
            'account' => $account_id
		]);
        $deposit_count_search = $this->Finance_model->finance_count($search_data);
        $data = $this->Finance_model->finance_list_page($search_data);

        echo json_encode([
         "draw" => intval($get['draw']),
         "recordsTotal" => intval($deposit_count_all),
         "recordsFiltered" => intval($deposit_count_search),
         "data" => $data,
       ]);
    }
}
