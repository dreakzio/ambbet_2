<?php
defined('BASEPATH') or exit('No direct script access allowed');

class News extends CI_Controller
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
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "ประกาศ",
			'description' => 'หน้ารายการ',
			'page_url' => $currentURL,
		]);
		$data['page'] = 'new/new';
		$this->load->view('main', $data);
	}
	public function new_form_create()
	{
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "ประกาศ",
			'description' => 'หน้าสร้าง',
			'page_url' => $currentURL,
		]);
		$data['page'] = 'new/new_create';
		$this->load->view('main', $data);
	}
	public function new_create()
	{
		check_parameter([
			'name',
			'status',
			'seq'
		], 'POST');
		$post = $this->input->post();
		$new_image = $this->new_image('image');
		$create = [
			'name' => $post['name'],
			'seq' => $post['seq'],
			'status' => $post['status'],
			'status_alert' => $post['status_alert'],
			'status_image_alert' => $post['status_image_alert'],
			'url' => isset($post['url']) ? $post['url'] : null,
		];
		if ($new_image!="") {
			$create['image'] = $new_image;
		}
		$this->New_model->new_create($create);
		$this->session->set_flashdata('toast', 'บันทึกข้อมูลเรียบร้อยแล้ว');
		redirect('news');
	}
	public function new_form_update($id="")
	{
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "ประกาศ",
			'description' => 'หน้าแก้ไข',
			'page_url' => $currentURL,
		]);
		$data['new'] = $this->New_model->new_find([
			'id' => $id
		]);
		if ($data['new']=="") {
			redirect('news');
			exit();
		}

		$data['page'] = 'new/new_update';
		$this->load->view('main', $data);
	}
	public function new_update($id="")
	{
		if ($id=="") {
			redirect('news');
		}
		check_parameter([
			'name',
			'status',
			'seq'
		], 'POST');
		$post = $this->input->post();
		$new_image = $this->new_image('image');
		$update = [
			'name' => $post['name'],
			'status' => $post['status'],
			'status_alert' => $post['status_alert'],
			'status_image_alert' => $post['status_image_alert'],
			'url' => isset($post['url']) ? $post['url'] : null,
			'id' => $id,
			'seq' => $post['seq'],
		];
		if ($new_image!="") {
			$update['image'] = $new_image;
			$new = $this->New_model->new_find([
				'id' => $id
			]);
			if ($new!="") {
				$path = 'assets/images/new/'.$new['image'];
				$type_file = pathinfo($path, PATHINFO_EXTENSION);
				if ($type_file!=""&&file_exists($path)) {
					unlink($path);
				}
			}
		}
		$this->New_model->new_update($update);
		$this->session->set_flashdata('toast', 'แก้ไขข้อมูลเรียบร้อยแล้ว');
		redirect('news');
	}
	public function new_list()
	{
		$data = $this->New_model->new_list();
		echo json_encode([
			'message' => 'success',
			'result' => $data
		]);
	}
	public function new_delete($id = "")
	{
		check_parameter([], 'POST');
		$new = $this->New_model->new_find([
			'id' => $id
		]);
		if ($new=="") {
			echo json_encode([
				'message' => 'ไม่พบข้อมูล',
				'error' => true
			]);
			exit();
		}
		$update = [
			'deleted' => 1,
			'id' => $id,
		];
		$this->New_model->new_update($update);
		echo json_encode([
			'message' => 'ลบข้อมูลเรียบร้อยแล้ว',
			'result' => true
		]);
	}
	public function new_image($name)
	{
		$type_file = pathinfo($_FILES[$name]["name"], PATHINFO_EXTENSION);
		$random_string = random_string('alnum', 5);
		$rename = "new_".date('YmdHis').'_'.$random_string.".".$type_file;
		$config['upload_path']          = 'assets/images/new/';
		$config['allowed_types']        = 'gif|jpg|png|jpeg';
		// $config['max_size']             = 60000;
		$config['max_width']            = 1040;
		$config['max_height']           = 1040;
		$config['file_name']           = $rename;
		//resize
		$config['image_library'] = 'gd2';
		$config['source_image'] = $config['upload_path'].$rename;
		// $config['create_thumb'] = TRUE;
		$config['maintain_ratio'] = TRUE;
		$config['width']     = 1040;
		$config['height']   = 1040;
		// $this->upload->clear();
		$this->upload->initialize($config);
		$this->load->library('upload', $config);
		if ($_FILES[$name]['error']==0) {
			if($this->upload->do_upload($name)){
				$this->image_lib->clear();
				$this->image_lib->initialize($config);
				$this->load->library('image_lib', $config);
				$this->image_lib->resize();
				return $rename;
			}else{
				echo $this->upload->display_errors();
				exit();
			}
		}

	}
}
