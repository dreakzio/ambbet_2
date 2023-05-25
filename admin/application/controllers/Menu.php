<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Menu extends CI_Controller
{
	public $menu_service;
    public function __construct()
    {
        date_default_timezone_set('Asia/Bangkok');
        parent::__construct();
		if (!isset($_SESSION['user']) || !isset($_SESSION['user']['role'])) {
			redirect('../auth');
		}
		$this->load->library(['Menu_service']);
		if(!$this->menu_service->validate_permission_menu($this->uri)){
			redirect('../auth');
		}else if($_SESSION['user']['role'] != roleSuperAdmin()){
			redirect('../auth');
		}
    }
    public function category()
	{
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "เมนู (หมวดหมู่)",
			'description' => 'หน้ารายการ',
			'page_url' => $currentURL,
		]);
		$data['page'] = 'group_menu/group_menu';
		$this->load->view('main',$data);
	}

	public function category_list_page()
	{
		$get = $this->input->get();
		$search = $get['search']['value'];
		$per_page = $get['length'];
		$page = $get['start'];
		$search_data = [
			'per_page' => $per_page,
			'page' => $page,
			'order_by' => 'order',
			'sort_by' => 'ASC',
			'ignore_deleted' => true,
		];
		if ($search!="") {
			$search_data['search'] = $search;
		}
		$group_menu_count_all = $this->Group_menu_model->group_menu_count([
			'ignore_deleted' => true
		]);
		$group_menu_count_search = $this->Group_menu_model->group_menu_count($search_data);
		$data = $this->Group_menu_model->group_menu_list($search_data);
		echo json_encode([
			"draw" => intval($get['draw']),
			"recordsTotal" => intval($group_menu_count_all),
			"recordsFiltered" => intval($group_menu_count_search),
			"data" => $data,
		]);
	}

	public function category_form_update($id="")
	{
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "เมนู (หมวดหมู่)",
			'description' => 'หน้าแก้ไข',
			'page_url' => $currentURL,
		]);
		$data['group_menu'] = $this->Group_menu_model->group_menu_find([
			'id' => $id,
			'ignore_deleted' => true,
		]);
		if ($data['group_menu']=="") {
			redirect('menu/category');
			exit();
		}
		$data['page'] = 'group_menu/group_menu_update';
		$this->load->view('main', $data);
	}
	public function category_update($id="")
	{
		check_parameter([
			'name',
			'description',
			'is_deleted',
			'order',
			'icon_class'
		], 'POST');
		$post = $this->input->post();
		$update = [
			'name' => $post['name'],
			'id' => $id,
			'description' => $post['description'],
			'is_deleted' => $post['is_deleted'],
			'order' => $post['order'],
			'icon_class' => $post['icon_class'],
		];
		$this->Group_menu_model->group_menu_update($update);
		$this->session->set_flashdata('toast', 'แก้ไขข้อมูลเรียบร้อยแล้ว');
		redirect('menu/category');
	}

	public function category_form_create()
	{
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "เมนู (หมวดหมู่)",
			'description' => 'หน้าสร้าง',
			'page_url' => $currentURL,
		]);
		$data['page'] = 'group_menu/group_menu_create';
		$this->load->view('main', $data);
	}
	public function category_create()
	{
		check_parameter([
			'name',
			'description',
			'is_deleted',
			'order',
			'icon_class'
		], 'POST');
		$post = $this->input->post();
		$group_menu_chk = $this->Group_menu_model->group_menu_find([
			'name' => $post['name'],
			'ignore_deleted' => true,
		]);
		if($group_menu_chk != "" ){
			$this->session->set_flashdata('warning', 'ชื่อซ้ำกันในระบบ : '.$post['name']);
			redirect('menu/category_form_create');
			exit();
		}
		$create = [
			'name' => $post['name'],
			'description' => $post['description'],
			'is_deleted' => $post['is_deleted'],
			'order' => $post['order'],
			'icon_class' => $post['icon_class'],
		];
		$this->Group_menu_model->group_menu_create($create);
		$this->session->set_flashdata('toast', 'เพิ่มข้อมูลเรียบร้อยแล้ว');
		redirect('menu/category');
	}

	public function main()
	{
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "เมนู (เมนูหลัก)",
			'description' => 'หน้ารายการ',
			'page_url' => $currentURL,
		]);
		$data['group_menu_list']  = $this->Group_menu_model->group_menu_list([
			'per_page' => 999,
			'page' => 0,
			'order_by' => 'order',
			'sort_by' => 'ASC',
			'ignore_deleted' => true,
		]);
		$data['page'] = 'menu/menu';
		$this->load->view('main',$data);
	}

	public function main_list_page()
	{
		$get = $this->input->get();
		$search = $get['search']['value'];
		$per_page = $get['length'];
		$page = $get['start'];
		$search_data = [
			'per_page' => $per_page,
			'page' => $page,
			'order_by_group_menu' => 'order',
			'sort_by_group_menu' => 'ASC',
			'order_by' => 'order',
			'sort_by' => 'ASC',
			'ignore_deleted' => true,
		];
		if ($search!="") {
			$search_data['search'] = $search;
		}
		if(isset($get['parent_id']) && $get['parent_id'] !== ""){
			$search_data['parent_id'] = $get['parent_id'];
		}
		$menu_count_all = $this->Menu_model->menu_count([
			'ignore_deleted' => true
		]);
		$menu_count_search = $this->Menu_model->menu_count($search_data);
		$data = $this->Menu_model->menu_list($search_data);
		echo json_encode([
			"draw" => intval($get['draw']),
			"recordsTotal" => intval($menu_count_all),
			"recordsFiltered" => intval($menu_count_search),
			"data" => $data,
		]);
	}

	public function main_form_update($id="")
	{
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "เมนู (เมนูหลัก)",
			'description' => 'หน้าแก้ไข',
			'page_url' => $currentURL,
		]);
		$data['menu'] = $this->Menu_model->menu_find([
			'id' => $id,
			'ignore_deleted' => true,
		]);
		if ($data['menu']=="") {
			redirect('menu/main');
			exit();
		}
		$data['group_menu_list']  = $this->Group_menu_model->group_menu_list([
			'per_page' => 999,
			'page' => 0,
			'order_by' => 'order',
			'sort_by' => 'ASC',
			'ignore_deleted' => true,
		]);
		$data['page'] = 'menu/menu_update';
		$this->load->view('main', $data);
	}
	public function main_update($id="")
	{
		check_parameter([
			'name',
			'parent_id',
			'description',
			'is_deleted',
			'order',
			'icon_class'
		], 'POST');
		$post = $this->input->post();
		$node_menu_count = $this->Node_menu_model->node_menu_count([
			'parent_id' => $id
		]);
		$update = [
			'parent_id' => empty($post['parent_id']) ? null : $post['parent_id'],
			'name' => $post['name'],
			'id' => $id,
			'have_child' => ($node_menu_count > 0 ? '1' : '0'),
			'description' => $post['description'],
			'is_deleted' => $post['is_deleted'],
			'order' => $post['order'],
			'icon_class' => $post['icon_class'],
		];
		$this->Menu_model->menu_update($update);
		$this->session->set_flashdata('toast', 'แก้ไขข้อมูลเรียบร้อยแล้ว');
		redirect('menu/main');
	}

	public function sub()
	{
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "เมนู (เมนูย่อย)",
			'description' => 'หน้ารายการ',
			'page_url' => $currentURL,
		]);
		$data['menu_list']  = $this->Menu_model->menu_list([
			'per_page' => 999,
			'page' => 0,
			'order_by_group_menu' => 'order',
			'sort_by_group_menu' => 'ASC',
			'order_by' => 'order',
			'sort_by' => 'ASC',
			'ignore_deleted' => true,
		]);
		$data['page'] = 'node_menu/node_menu';
		$this->load->view('main',$data);
	}

	public function sub_list_page()
	{
		$get = $this->input->get();
		$search = $get['search']['value'];
		$per_page = $get['length'];
		$page = $get['start'];
		$search_data = [
			'per_page' => $per_page,
			'page' => $page,
			'order_by_group_menu' => 'order',
			'sort_by_group_menu' => 'ASC',
			'order_by_menu' => 'order',
			'sort_by_menu' => 'ASC',
			'order_by' => 'order',
			'sort_by' => 'ASC',
			'ignore_deleted' => true,
		];
		if ($search!="") {
			$search_data['search'] = $search;
		}
		if(isset($get['parent_id']) && $get['parent_id'] !== ""){
			$search_data['parent_id'] = $get['parent_id'];
		}
		$node_menu_count_all = $this->Node_menu_model->node_menu_count([
			'ignore_deleted' => true
		]);
		$node_menu_count_search = $this->Node_menu_model->node_menu_count($search_data);
		$data = $this->Node_menu_model->node_menu_list($search_data);
		echo json_encode([
			"draw" => intval($get['draw']),
			"recordsTotal" => intval($node_menu_count_all),
			"recordsFiltered" => intval($node_menu_count_search),
			"data" => $data,
		]);
	}

	public function sub_form_update($id="")
	{
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "เมนู (เมนูย่อย)",
			'description' => 'หน้าแก้ไข',
			'page_url' => $currentURL,
		]);
		$data['node_menu'] = $this->Node_menu_model->node_menu_find([
			'id' => $id,
			'ignore_deleted' => true,
		]);
		if ($data['node_menu']=="") {
			redirect('menu/sub');
			exit();
		}
		$data['menu_list']  = $this->Menu_model->menu_list([
			'per_page' => 999,
			'page' => 0,
			'order_by_group_menu' => 'order',
			'sort_by_group_menu' => 'ASC',
			'order_by' => 'order',
			'sort_by' => 'ASC',
			'ignore_deleted' => true,
		]);
		$data['page'] = 'node_menu/node_menu_update';
		$this->load->view('main', $data);
	}
	public function sub_update($id="")
	{
		check_parameter([
			'name',
			'description',
			'is_deleted',
			'order',
			'icon_class'
		], 'POST');
		$post = $this->input->post();
		$update = [
			'name' => $post['name'],
			'id' => $id,
			'description' => $post['description'],
			'is_deleted' => $post['is_deleted'],
			'order' => $post['order'],
			'icon_class' => $post['icon_class'],
		];
		$this->Node_menu_model->node_menu_update($update);
		$this->session->set_flashdata('toast', 'แก้ไขข้อมูลเรียบร้อยแล้ว');
		redirect('menu/sub');
	}

}
