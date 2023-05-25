<?php
class Menu_service
{
	private $Group_menu_model;
	private $Menu_model;
	private $Node_menu_model;
	private $Permission_menu_role_model;
	private $Role_model;

	public function __construct()
	{
		date_default_timezone_set('Asia/Bangkok');
		$CI = &get_instance();
		$this->Group_menu_model = &$CI->Group_menu_model;
		$this->Menu_model = &$CI->Menu_model;
		$this->Node_menu_model = &$CI->Node_menu_model;
		$this->Permission_menu_role_model = &$CI->Permission_menu_role_model;
		$this->Role_model = &$CI->Role_model;
	}

	public function get_menu_list($role=null,$ignore_deleted = null,$cached=false){
		if(isset($_SESSION['user']['role'])){
			if($cached && isset($_SESSION['user']['group_menu_list'])){
				$chk_seconds =  strtotime(date("Y-m-d H:i:s")) - strtotime($_SESSION['user']['group_menu_cached']);
				if($chk_seconds <= 180 && !is_null($_SESSION['user']['group_menu_cached'])){
					return $_SESSION['user']['group_menu_list'];
				}
			}
			$group_menu_results = [];
			$search_menu_list = [
				'page' => 0,
				'per_page' => 999,
				'order_by' => 'order',
				'sort_by' => 'ASC',
			];
			if(!is_null($ignore_deleted) && $ignore_deleted){
				$search_menu_list['ignore_deleted'] = true;
			}
			$group_menu_list = $this->Group_menu_model->group_menu_list($search_menu_list);
			foreach ($group_menu_list as $group_menu){
				$group_menu['menu_list'] = [];
				$group_menu_results[$group_menu['id']] = $group_menu;
			}
			$search_permission_role_menu_list = [
				'role_id' => !is_null($role) ? $role : $_SESSION['user']['role'],
				'page' => 0,
				'per_page' => 999,
				'order_by_menu' => 'order',
				'sort_by_menu' => 'ASC',
			];
			if(!is_null($ignore_deleted) && $ignore_deleted){
				$search_permission_role_menu_list['ignore_deleted'] = true;
			}
			$permission_menu_role_list = $this->Permission_menu_role_model->permission_menu_role_list($search_permission_role_menu_list);
			foreach ($permission_menu_role_list as $permission_menu_role){
				if(array_key_exists($permission_menu_role['menu_parent_id'],$group_menu_results)){
					if(isset($permission_menu_role['menu_have_child']) && $permission_menu_role['menu_have_child'] == 1){
						$search_node_menu_list = [
							'parent_id' => $permission_menu_role['menu_id'],
							'page' => 0,
							'per_page' => 999,
							'order_by' => 'order',
							'sort_by' => 'ASC',
						];
						if(!is_null($ignore_deleted) && $ignore_deleted){
							$search_node_menu_list['ignore_deleted'] = true;
						}
						$permission_menu_role['node_menu_list'] = $this->Node_menu_model->node_menu_list($search_node_menu_list);
					}
					$group_menu_results[$permission_menu_role['menu_parent_id']]['menu_list'][] = $permission_menu_role;
				}
			}
			if($cached){
				$_SESSION['user']['group_menu_list'] = $group_menu_results;
				$_SESSION['user']['group_menu_cached'] = date('Y-m-d H:i:s');
			}
			return $group_menu_results;
		}
		return [];
	}
	public function validate_permission_menu($uri_segment){
		$isAjaxRequest = false;
		$all_headers = getallheaders();
		if(
			isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
			strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'xmlhttprequest') == 0
		){
			$isAjaxRequest = true;
		}else if(isset($all_headers['Accept']) && strpos($all_headers['Accept'],"application/json") !== false){
			$isAjaxRequest = true;
		}
		$menu_url = "home";
		if(!empty($uri_segment->segment(1))){
			if($isAjaxRequest){
				$menu_url = $uri_segment->segment(1);
			}else{
				$menu_url = "";
				$uri_segment_cnt = count($uri_segment->segment_array());
				for($i=1;$i<=$uri_segment_cnt;$i++){
					$menu_url .= ($i==1 ? $uri_segment->segment($i) : "/".$uri_segment->segment($i));
				}
			}
		}
		if(isset($_SESSION['user']['role'])){

			if(!$this->validateRole()){
				return false;
			}

			$search_permission_menu_role_cnt = [
				'role_id' => $_SESSION['user']['role'],
			];
			if($isAjaxRequest){
				$search_permission_menu_role_cnt['menu_url_ajax'] = $menu_url;
			}else{
				$search_permission_menu_role_cnt['menu_url'] = $menu_url;
				$permission_menu_role_cnt = $this->Permission_menu_role_model->permission_menu_role_cnt($search_permission_menu_role_cnt);
				if(
					count(explode("/",$menu_url)) >= 2 && count(explode("_",explode("/",$menu_url)[1])) >= 2 && $permission_menu_role_cnt == 0
				){
					$search_permission_menu_role_cnt['menu_url'] =  explode("/",$menu_url)[0].'/'.explode("_",explode("/",$menu_url)[1])[0];
					$permission_menu_role_cnt = $this->Permission_menu_role_model->permission_menu_role_cnt($search_permission_menu_role_cnt);
					$permission_node_menu_role_cnt = $this->Permission_menu_role_model->permission_node_menu_role_cnt($search_permission_menu_role_cnt);
					if(
						$permission_menu_role_cnt == 0 && $permission_node_menu_role_cnt == 0
					){
						$search_permission_menu_role_cnt['menu_url'] =  explode("/",$menu_url)[0].'/'.explode("_",explode("/",$menu_url)[1])[0]."_".explode("_",explode("/",$menu_url)[1])[1];
					}
				}
			}
			$chk_add_cached_ajax_url = false;
			if($isAjaxRequest && isset($_SESSION['user']['permission_menu_ajax_list'])){
				$chk_seconds =  strtotime(date("Y-m-d H:i:s")) - strtotime($_SESSION['user']['group_menu_cached']);
				if($chk_seconds <= 180 && !is_null($_SESSION['user']['permission_menu_ajax_cached'])){
					if(in_array($search_permission_menu_role_cnt['menu_url_ajax'],$_SESSION['user']['permission_menu_ajax_list'])){
						return true;
					}else{
						$chk_add_cached_ajax_url = true;
					}
				}else{
					$chk_add_cached_ajax_url = true;
					$_SESSION['user']['permission_menu_ajax_cached'] = date("Y-m-d H:i:s");
					$_SESSION['user']['permission_menu_ajax_list'] = [];
				}
			}

			$permission_menu_role_cnt = $this->Permission_menu_role_model->permission_menu_role_cnt($search_permission_menu_role_cnt);
			if($permission_menu_role_cnt >= 1){
				if($chk_add_cached_ajax_url){
					$_SESSION['user']['permission_menu_ajax_list'][] = $search_permission_menu_role_cnt['menu_url_ajax'];
				}
				return true;
			}else{
				if($_SESSION['user']['role'] == roleSuperAdmin() && !$isAjaxRequest){
					$this->merge_all_menu_to_superadmin();
				}
				$permission_menu_role_cnt = $this->Permission_menu_role_model->permission_menu_role_cnt($search_permission_menu_role_cnt);
				if($permission_menu_role_cnt >= 1){
					if($chk_add_cached_ajax_url){
						$_SESSION['user']['permission_menu_ajax_list'][] = $search_permission_menu_role_cnt['menu_url_ajax'];
					}
					return true;
				}
				$permission_node_menu_role_cnt = $this->Permission_menu_role_model->permission_node_menu_role_cnt($search_permission_menu_role_cnt);
				if($permission_node_menu_role_cnt >= 1){
					if($chk_add_cached_ajax_url){
						$_SESSION['user']['permission_menu_ajax_list'][] = $search_permission_menu_role_cnt['menu_url_ajax'];
					}
					return true;
				}

			}
		}
		return false;
	}

	private function validateRole(){
		//Validate role empty or inactive
		$role_valid = $this->Role_model->role_find([
			'role_id' => $_SESSION['user']['role']
		]);
		if($role_valid == "" || $role_valid['is_deleted'] == "1"){
			return false;
		}
		return true;
	}

	private function merge_all_menu_to_superadmin(){
		$menu_list = $this->Menu_model->menu_list([
			'page' => 0,
			'per_page' => 999,
			'order_by' => 'order',
			'sort_by' => 'ASC',
		]);
		foreach ($menu_list as $menu){
			$chk_permission_role_menu = $this->Permission_menu_role_model->permission_menu_role_find([
				'role_id' => $_SESSION['user']['role'],
				'menu_id' => $menu['id'],
			]);
			if($chk_permission_role_menu == ""){
				$this->Permission_menu_role_model->permission_menu_role_create([
					'role_id' => $_SESSION['user']['role'],
					'menu_id' => $menu['id'],
					'is_create' => 1,
					'is_update' => 1,
					'is_view' => 1,
					'is_delete' => 1,
					'is_export' => 1,
					'is_search' => 1,
				]);
			}
		}
	}

	public function cnt_menu_list(){
		if(!$this->validateRole()){
			return 0;
		}
		$permission_menu_role_cnt = $this->Permission_menu_role_model->permission_menu_role_cnt([
			'role_id' => $_SESSION['user']['role'],
		]);
		return $permission_menu_role_cnt;
	}

	public function get_menu_url_ignore_list(){
		return [
			"menu/category",
			"menu/main",
			"menu/sub",
			"role",
		];
	}

}
