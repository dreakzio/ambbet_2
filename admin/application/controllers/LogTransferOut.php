<?php
defined('BASEPATH') or exit('No direct script access allowed');

class LogTransferOut extends CI_Controller
{

	public function __construct()
	{
		date_default_timezone_set('Asia/Bangkok');
		parent::__construct();
		if (!isset($_SESSION['user'])  || !in_array($_SESSION['user']['role'],[roleSuperAdmin()])) {
			redirect('../auth');
		}
	}

    public function index()
    {
        $data['page'] = 'log/transfer_out';
        $this->load->view('main', $data);
    }
    public function log_transfer_out_list_page()
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
        $log_count_all = $this->Log_transfer_out_model->log_transfer_out_count();
        $log_count_search = $this->Log_transfer_out_model->log_transfer_out_count($search_data);
        $data = $this->Log_transfer_out_model->log_transfer_out_list($search_data);
        echo json_encode([
         "draw" => intval($get['draw']),
         "recordsTotal" => intval($log_count_all),
         "recordsFiltered" => intval($log_count_search),
         "data" => $data,
       ]);
    }
}
