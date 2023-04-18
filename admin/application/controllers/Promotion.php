<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Promotion extends CI_Controller
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
			'page_name' => "โปรโมชั่น",
			'description' => 'หน้ารายการ',
			'page_url' => $currentURL,
		]);
		$data['page'] = 'promotion/promotion';
		$this->load->view('main', $data);
	}
	public function promotion_form_create()
	{
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "โปรโมชั่น",
			'description' => 'หน้าสร้าง',
			'page_url' => $currentURL,
		]);
		$data['page'] = 'promotion/promotion_create';
		$this->load->view('main', $data);
	}
	public function promotion_create()
	{
		check_parameter([
			'name',
			'percent',
			'max_value',
			//'turn',
			'status',
			'max_use',
			'category',
			'fix_amount_deposit_bonus',
			'fix_amount_deposit',
			'type'
		], 'POST');
		$post = $this->input->post();
		$promotion_image = $this->promotion_image('image');
		$create = [
			'name' => $post['name'],
			'percent' => $post['percent'],
			'max_value' => $post['max_value'],
			//'turn' => $post['turn'],
			'status' => $post['status'],
			'max_use' => $post['max_use'],
			'category' => $post['category'],
			'fix_amount_deposit_bonus' => $post['fix_amount_deposit_bonus'],
			'fix_amount_deposit' => $post['fix_amount_deposit'],
			'type' => $post['type'],
			'start_time' => $post['start_time'] == '' ? NULL : $post['start_time'],
			'end_time' => $post['end_time'] == '' ? NULL : $post['end_time'],
			'number_of_deposit_days' => $post['number_of_deposit_days'] == '' ? NULL : $post['number_of_deposit_days'],
			'description' => isset($post['description']) ?  $post['description'] : NULL
		];
		foreach (game_code_list() as $game_code){
			if(isset($post['turn_'.strtolower($game_code)])){
				$create['turn_'.strtolower($game_code)] = $post['turn_'.strtolower($game_code)];
			}else{
				$create['turn_'.strtolower($game_code)] = "0";
			}
		}
		if ($promotion_image!="") {
			$create['image'] = $promotion_image;
		}
		$this->Promotion_model->promotion_create($create);
		$this->session->set_flashdata('toast', 'บันทึกข้อมูลเรียบร้อยแล้ว');
		redirect('promotion');
	}
	public function promotion_form_update($id="")
	{
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "โปรโมชั่น",
			'description' => 'หน้าแก้ไข',
			'page_url' => $currentURL,
		]);
		$data['promotion'] = $this->Promotion_model->promotion_find([
			'id' => $id
		]);
		if ($data['promotion']=="") {
			redirect('promotion');
			exit();
		}

		$data['page'] = 'promotion/promotion_update';
		$this->load->view('main', $data);
	}
	public function promotion_update($id="")
	{
		if ($id=="") {
			redirect('promotion');
		}
		check_parameter([
			'name',
			'percent',
			'max_value',
			//'turn',
			'status',
			'max_use',
			'category',
			'fix_amount_deposit_bonus',
			'fix_amount_deposit',
			'type'
		], 'POST');
		$post = $this->input->post();
		$promotion_image = $this->promotion_image('image');
		$update = [
			'name' => $post['name'],
			'percent' => $post['percent'],
			'max_value' => $post['max_value'],
			//'turn' => $post['turn'],
			'status' => $post['status'],
			'id' => $id,
			'max_use' => $post['max_use'],
			'category' => $post['category'],
			'fix_amount_deposit_bonus' => $post['fix_amount_deposit_bonus'],
			'fix_amount_deposit' => $post['fix_amount_deposit'],
			'type' => $post['type'],
			'start_time' => $post['start_time'] == '' ? NULL : $post['start_time'],
			'end_time' => $post['end_time'] == '' ? NULL : $post['end_time'],
			'number_of_deposit_days' => $post['number_of_deposit_days'] == '' ? NULL : $post['number_of_deposit_days'],
			'description' => isset($post['description']) ?  $post['description'] : NULL
		];
		foreach (game_code_list() as $game_code){
			if(isset($post['turn_'.strtolower($game_code)])){
				$update['turn_'.strtolower($game_code)] = $post['turn_'.strtolower($game_code)];
			}else{
				$update['turn_'.strtolower($game_code)] = "0";
			}
		}
		if ($promotion_image!="") {
			$update['image'] = $promotion_image;
			$promotion = $this->Promotion_model->promotion_find([
				'id' => $id
			]);
			if ($promotion!="") {
				$path = 'assets/images/promotion/'.$promotion['image'];
				$type_file = pathinfo($path, PATHINFO_EXTENSION);
				if ($type_file!=""&&file_exists($path)) {
					unlink($path);
				}
			}
		}
		$this->Promotion_model->promotion_update($update);
		$this->session->set_flashdata('toast', 'แก้ไขข้อมูลเรียบร้อยแล้ว');
		redirect('promotion');
	}
	public function promotion_list()
	{
		$data = $this->Promotion_model->promotion_list();
		echo json_encode([
			'message' => 'success',
			'result' => $data
		]);
	}
	public function promotion_delete($id = "")
	{
		check_parameter([], 'POST');
		$promotion = $this->Promotion_model->promotion_find([
			'id' => $id
		]);
		if ($promotion=="") {
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
		$this->Promotion_model->promotion_update($update);
		echo json_encode([
			'message' => 'ลบข้อมูลเรียบร้อยแล้ว',
			'result' => true
		]);
	}
	public function promotion_image($name)
	{
		$type_file = pathinfo($_FILES[$name]["name"], PATHINFO_EXTENSION);
		$random_string = random_string('alnum', 5);
		$rename = "promotion_".date('YmdHis').'_'.$random_string.".".$type_file;
		$config['upload_path']          = 'assets/images/promotion/';
		$config['allowed_types']        = 'gif|jpg|png|jpeg';
		// $config['max_size']             = 60000;
		// $config['max_width']            = 4000;
		// $config['max_height']           = 4000;
		$config['file_name']           = $rename;
		//resize
		$config['image_library'] = 'gd2';
		$config['source_image'] = $config['upload_path'].$rename;
		// $config['create_thumb'] = TRUE;
		$config['maintain_ratio'] = TRUE;
		$config['width']     = 700;
		$config['height']   = 200;
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
