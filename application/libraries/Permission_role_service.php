<?php
class Permission_role_service
{
    private $Permission_role_model;
    private $Role_model;

    public function __construct()
    {
		date_default_timezone_set('Asia/Bangkok');
        $CI = &get_instance();
        $this->Permission_role_model = &$CI->Permission_role_model;
        $this->Role_model = &$CI->Role_model;
    }

	public function can_manage_role(){
		$this->merge_all_role_to_superadmin();
		$role_list = $this->Role_model->role_list([
			'page' => 0,
			'per_page' => 999,
			'order_by' => 'role_level',
			'sort_by' => 'ASC',
		]);
		$permission_role_list = $this->Permission_role_model->permission_role_list([
			'page' => 0,
			'per_page' => 999,
			'order_by_role_level' => 'role_level',
			'sort_by_role_level' => 'ASC',
		]);
		$result_permission_role = [];
		foreach ($permission_role_list as $permission_role){
			if(array_key_exists($permission_role['role_id'],$result_permission_role)){
				$result_permission_role[$permission_role['role_id']][] = $permission_role['role_child_id'];
			}else{
				$result_permission_role[$permission_role['role_id']] = [
					$permission_role['role_child_id']
				];
			}
		}
		$result_can_manage = [];
		foreach ($role_list as $role){
			if(array_key_exists($role['role_id'],$result_permission_role)){
				$result_can_manage[$role['role_id']] = $result_permission_role[$role['role_id']];
			}else{
				$result_can_manage[$role['role_id']] = [];
			}
		}

		return $result_can_manage;
	}
	public function role_display()
	{
		$role_list = $this->Role_model->role_list([
			'page' => 0,
			'per_page' => 999,
			'order_by' => 'role_level',
			'sort_by' => 'ASC',
		]);
		$results = [];
		foreach ($role_list as $role) {
			$results[$role['role_id']] = $role['role_name'].( $role['is_deleted'] == "1" ? " (ปิดใช้งาน)" :"");
		}
		return $results;
	}

	private function merge_all_role_to_superadmin(){
		if(isset($_SESSION['user']['role']) && $_SESSION['user']['role'] == roleSuperAdmin()){
			$role_list = $this->Role_model->role_list([
				'page' => 0,
				'per_page' => 999,
				'order_by' => 'role_level',
				'sort_by' => 'ASC',
			]);
			foreach ($role_list as $role){
				$chk = $this->Permission_role_model->permission_role_find([
					'role_id' => $_SESSION['user']['role'],
					'role_child_id' => $role['role_id'],
				]);
				if($chk == ""){
					$this->Permission_role_model->permission_role_create([
						'role_id' => $_SESSION['user']['role'],
						'role_child_id' => $role['role_id'],
						'updated_at' => date('Y-m-d H:i:s'),
					]);
				}
			}
		}
	}
}
