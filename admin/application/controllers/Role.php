<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Role extends CI_Controller
{
	public $menu_service;
	public $permission_role_service;
    public function __construct()
    {
        date_default_timezone_set('Asia/Bangkok');
        parent::__construct();
		if (!isset($_SESSION['user']) || !isset($_SESSION['user']['role'])) {
			redirect('../auth');
		}
		$this->load->library(['Menu_service','Permission_role_service']);
		if(!$this->menu_service->validate_permission_menu($this->uri)){
			redirect('../auth');
		}else if($_SESSION['user']['role'] != roleSuperAdmin()){
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
			'page_name' => "สิทธิ์การใช้งาน",
			'description' => 'หน้ารายการ',
			'page_url' => $currentURL,
		]);
		$data['page'] = 'role/role';
		$this->load->view('main',$data);
	}

	public function role_list_page()
	{
		$get = $this->input->get();
		$search = $get['search']['value'];
		$per_page = $get['length'];
		$page = $get['start'];
		$search_data = [
			'per_page' => $per_page,
			'page' => $page,
			'order_by' => 'role_level',
			'sort_by' => 'ASC',
		];
		if ($search!="") {
			$search_data['search'] = $search;
		}
		$role_count_all = $this->Role_model->role_count();
		$role_count_search = $this->Role_model->role_count($search_data);
		$data = $this->Role_model->role_list($search_data);
		echo json_encode([
			"draw" => intval($get['draw']),
			"recordsTotal" => intval($role_count_all),
			"recordsFiltered" => intval($role_count_search),
			"data" => $data,
		]);
	}

	public function role_get_menu_list($role_id){
		$menu_list = $this->menu_service->get_menu_list($role_id,true);
		echo json_encode([
			'data' => $menu_list,
		]);
	}

	public function role_get_role_can_manage_list($role_id){
		$can_manage = $this->permission_role_service->can_manage_role()[$role_id];
		echo json_encode([
			'data' => $can_manage,
		]);
	}

	public function role_form_update($id="")
	{
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "สิทธิ์การใช้งาน",
			'description' => 'หน้าแก้ไข',
			'page_url' => $currentURL,
		]);
		$data['role'] = $this->Role_model->role_find([
			'role_id' => $id
		]);

		if ($data['role']=="" || in_array($data['role']['role_id'],[roleSuperAdmin(),roleMember()])) {
			redirect('role');
			exit();
		}
		$group_menu_list = $this->menu_service->get_menu_list(null,true);
		$menu_url_ignore_list = $this->menu_service->get_menu_url_ignore_list();
		$data['group_menu_list'] = $group_menu_list;
		$data['menu_url_ignore_list'] = $menu_url_ignore_list;
		$role_can_manage_list = $this->permission_role_service->can_manage_role()[$data['role']['role_id']];
		$data['role_can_manage_list'] = $role_can_manage_list;
		$permission_menu_role_list = $this->Permission_menu_role_model->permission_menu_role_list([
			'role_id' => $data['role']['role_id'],
			'ignore_deleted' => true,
		]);
		$role_menu_list = [];
		foreach ($permission_menu_role_list as $permission_menu_role){
			$role_menu_list[] = $permission_menu_role['menu_id'];
		}
		$data['role_menu_list'] = $role_menu_list;
		$data['page'] = 'role/role_update';
		$this->load->view('main', $data);
	}

	public function role_update($id="")
	{
		check_parameter([
			'role_name',
			'role_level',
			'is_deleted'
		], 'POST');
		$post = $this->input->post();
		$role_chk = $this->Role_model->role_find([
			'role_name' => $post['role_name'],
			'role_id_ignore' => $id,
		]);
		if($role_chk != "" ){
			$this->session->set_flashdata('warning', 'ชื่อตำแหน่งซ้ำกันในระบบ : '.$post['role_name']);
			redirect('role/role_form_update/'.$id);
			exit();
		}
		$role_data = $this->Role_model->role_find([
			'role_id' => $id
		]);
		if($role_data == "" || in_array($role_data['role_id'],[roleSuperAdmin(),roleMember()])){
			redirect('role');
			exit();
		}
		$update = [
			'role_name' => $post['role_name'],
			'role_id' => $id,
			'role_level' => $post['role_level'],
			'is_deleted' => $post['is_deleted'],
		];
		$this->Role_model->role_update($update);
		if(!isset($post['role_list']) || count($post['role_list']) == 0){
			try{
				$this->Permission_role_model->permission_role_delete([
					'role_id' => $role_data['role_id']
				]);
			}catch (Exception $ex){

			}
		}
		if(!isset($post['menu_list']) || count($post['menu_list']) == 0){
			try{
				$this->Permission_menu_role_model->permission_menu_role_delete([
					'role_id' => $role_data['role_id']
				]);
			}catch (Exception $ex){

			}
		}
		$role_child_id_list_ignore = [];
		if(isset($post['role_list']) && count($post['role_list']) > 0){
			foreach ($post['role_list'] as $role){
				if(!in_array($role,[roleSuperAdmin()])){
					$role_child_id_list_ignore[] = $role;
					try{
						$permission_role_chk = $this->Permission_role_model->permission_role_find([
							'role_id' => $role_data['role_id'],
							'role_child_id' => $role,
						]);
						if($permission_role_chk == ""){
							$this->Permission_role_model->permission_role_create([
								'role_id' => $role_data['role_id'],
								'role_child_id' => $role,
							]);
						}
					}catch (Exception $ex){

					}
				}
			}
		}

		if(count($role_child_id_list_ignore) > 0){
			try{
				$this->Permission_role_model->permission_role_delete([
					'role_id' => $role_data['role_id'],
					'role_child_id_list_ignore' => $role_child_id_list_ignore,
				]);
			}catch (Exception $ex){

			}
		}
		$menu_id_list_ignore = [];
		if(isset($post['menu_list']) && count($post['menu_list']) > 0){
			foreach ($post['menu_list'] as $menu){
				$menu_chk = $this->Menu_model->menu_find([
					'id' => $menu,
					'ignore_deleted' => true,
				]);
				if($menu_chk != ""){
					$chk_create = true;
					if($menu_chk['have_child']){
						$node_menu_list = $this->Node_menu_model->node_menu_list([
							'parent_id' => $menu_chk['id'],
							'ignore_deleted' => true,
						]);
						foreach ($node_menu_list as $node_menu){
							if($chk_create && in_array(strtolower($node_menu['url']),$this->menu_service->get_menu_url_ignore_list())){
								$chk_create = false;
							}
						}
					}
					if($chk_create){
						$menu_id_list_ignore[] = $menu;
						try{
							$permission_menu_role_chk = $this->Permission_menu_role_model->permission_menu_role_find([
								'role_id' => $role_data['role_id'],
								'menu_id' => $menu_chk['id'],
							]);
							if($permission_menu_role_chk == ""){
								$this->Permission_menu_role_model->permission_menu_role_create([
									'role_id' => $role_data['role_id'],
									'menu_id' => $menu_chk['id'],
									'is_create' => 1,
									'is_update' => 1,
									'is_view' => 1,
									'is_delete' => 1,
									'is_export' => 1,
									'is_search' => 1,
								]);
							}

						}catch (Exception $ex){

						}
					}
				}
			}
		}

		if(count($menu_id_list_ignore) > 0){
			try{
				$this->Permission_menu_role_model->permission_menu_role_delete([
					'role_id' => $role_data['role_id'],
					'menu_id_list_ignore' => $menu_id_list_ignore,
				]);
			}catch (Exception $ex){

			}
		}
		$this->session->set_flashdata('toast', 'แก้ไขข้อมูลเรียบร้อยแล้ว');
		redirect('role');
	}

	public function role_form_create()
	{
		$this->load->helper('url');
		$currentURL = current_url();
		$log_page_id = $this->Log_page_model->log_page_create([
			'ip' => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $this->input->ip_address(),
			'admin' => $_SESSION['user']['id'],
			'username' => $_SESSION['user']['username'],
			'page_name' => "สิทธิ์การใช้งาน",
			'description' => 'หน้าสร้าง',
			'page_url' => $currentURL,
		]);
		$group_menu_list = $this->menu_service->get_menu_list(null,true);
		$menu_url_ignore_list = $this->menu_service->get_menu_url_ignore_list();
		$data['group_menu_list'] = $group_menu_list;
		$data['menu_url_ignore_list'] = $menu_url_ignore_list;
		$data['page'] = 'role/role_create';
		$this->load->view('main', $data);
	}

	public function role_create()
	{
		check_parameter([
			'role_name',
			'role_level',
			'is_deleted',
		], 'POST');
		$post = $this->input->post();
		$role_chk = $this->Role_model->role_find([
			'role_name' => $post['role_name']
		]);
		if($role_chk != "" ){
			$this->session->set_flashdata('warning', 'ชื่อตำแหน่งซ้ำกันในระบบ : '.$post['role_name']);
			redirect('role/role_form_create');
			exit();
		}
		$create = [
			'role_name' => $post['role_name'],
			'role_level' => $post['role_level'],
			'is_deleted' => $post['is_deleted'],
		];
		$role_id = $this->Role_model->role_create($create);
		if(!empty($role_id)){
			if(isset($post['role_list']) && count($post['role_list']) > 0){
				foreach ($post['role_list'] as $role){
					if(!in_array($role,[roleSuperAdmin()])){
						try{
							$this->Permission_role_model->permission_role_create([
								'role_id' => $role_id,
								'role_child_id' => $role,
							]);
						}catch (Exception $ex){

						}
					}
				}
			}
			if(isset($post['menu_list']) && count($post['menu_list']) > 0){
				foreach ($post['menu_list'] as $menu){
					$menu_chk = $this->Menu_model->menu_find([
						'id' => $menu,
						'ignore_deleted' => true,
					]);
					if($menu_chk != ""){
						$chk_create = true;
						if($menu_chk['have_child']){
							$node_menu_list = $this->Node_menu_model->node_menu_list([
								'parent_id' => $menu_chk['id'],
								'ignore_deleted' => true,
							]);
							foreach ($node_menu_list as $node_menu){
								if($chk_create && in_array(strtolower($node_menu['url']),$this->menu_service->get_menu_url_ignore_list())){
									$chk_create = false;
								}
							}
						}
						if($chk_create){
							try{
								$this->Permission_menu_role_model->permission_menu_role_create([
									'role_id' => $role_id,
									'menu_id' => $menu_chk['id'],
									'is_create' => 1,
									'is_update' => 1,
									'is_view' => 1,
									'is_delete' => 1,
									'is_export' => 1,
									'is_search' => 1,
								]);
							}catch (Exception $ex){

							}
						}
					}
				}
			}

		}
		$this->session->set_flashdata('toast', 'เพิ่มข้อมูลเรียบร้อยแล้ว');
		redirect('role');
	}
}
