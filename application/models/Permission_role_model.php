<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Permission_role_model extends CI_Model
{
	public function permission_role_list($search = [])
	{
		$this->db->select('
        	permission_role.*
        ',false);
		$this->db->limit($search['per_page'], $search['page']);
		if(isset($search['order_by']) && isset($search['sort_by'])){
			$this->db->order_by('permission_role.'.$search['order_by'], $search['sort_by']);
		}else if(isset($search['order_by_role_level']) && isset($search['sort_by_role_level'])){
			$this->db->order_by('role.'.$search['order_by_role_level'], $search['sort_by_role_level']);
			$this->db->join('role', 'role.role_id = permission_role.role_child_id','left');
		}else{
			$this->db->order_by('permission_role.id', 'DESC');
		}

		if (isset($search['role_id'])) {
			$this->db->where('permission_role.role_id', $search['role_id']);
		}
		if (isset($search['role_child_id'])) {
			$this->db->where('permission_role.role_child_id', $search['role_child_id']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('permission_role.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('permission_role.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		$query = $this->db->get('permission_role');
		return $query->result_array();
	}
	public function permission_role_create($data = [])
	{
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
		$this->db->insert('permission_role', $data);
		$id = $this->db->insert_id();
		return $id;
	}
	public function permission_role_update($data)
	{
		$this->db->where('id', $data['id']);
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['updated_at'] = isset($data['updated_at']) ? $data['updated_at'] : date('Y-m-d H:i:s');
		$this->db->update('permission_role', $data);
	}
	public function permission_role_find($search = [])
	{
		$this->db->select('
        	*
         ');
		$this->db->order_by('permission_role.id', 'DESC');
		if (isset($search['role_id'])) {
			$this->db->where('permission_role.role_id', $search['role_id']);
		}
		if (isset($search['role_child_id'])) {
			$this->db->where('permission_role.role_child_id', $search['role_child_id']);
		}
		if (isset($search['id'])) {
			$this->db->where('permission_role.id', $search['id']);
		}
		$this->db->limit(1);
		$query = $this->db->get('permission_role');
		return $query->row_array();
	}
	public function permission_role_delete($search = [])
	{
		if (isset($search['role_id']) && !empty($search['role_id'])) {
			$this->db->where('permission_role.role_id', $search['role_id']);
			if (isset($search['role_child_id_list_ignore']) && count($search['role_child_id_list_ignore']) > 0) {
				$this->db->where_not_in('permission_role.role_child_id', $search['role_child_id_list_ignore']);
			}
			if (isset($search['id']) && !empty($search['id'])) {
				$this->db->where('permission_role.id', $search['id']);
			}
			$this->db->delete('permission_role');
		}else if (isset($search['id']) && !empty($search['id'])) {
			$this->db->where('permission_role.id', $search['id']);
			$this->db->delete('permission_role');
		}
	}
}
