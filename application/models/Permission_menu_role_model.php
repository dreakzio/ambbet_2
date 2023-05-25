<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Permission_menu_role_model extends CI_Model
{
	public function permission_menu_role_list($search = [])
	{
		$this->db->select('
        	permission_menu_role.*,
        	menu.name as menu_name,
        	menu.description as menu_description,
        	menu.url as menu_url,
        	menu.icon_class as menu_icon_class,
        	menu.have_child as menu_have_child,
        	menu.parent_id as menu_parent_id,
        	menu.order as menu_order
        ',false);
		$this->db->limit($search['per_page'], $search['page']);
		if(isset($search['order_by']) && isset($search['sort_by'])){
			$this->db->order_by('permission_menu_role.'.$search['order_by'], $search['sort_by']);
		}else if(isset($search['order_by_menu']) && isset($search['sort_by_menu'])){
			$this->db->order_by('menu.'.$search['order_by_menu'], $search['sort_by_menu']);
		}else if(isset($search['order_by_group_menu']) && isset($search['sort_by_group_menu'])){
			$this->db->order_by('group_menu.'.$search['order_by_group_menu'], $search['sort_by_group_menu']);
			$this->db->join('group_menu', 'group_menu.id = menu.parent_id','left');
			if(isset($search['order_by_menu']) && isset($search['sort_by_menu'])){
				$this->db->order_by('menu.'.$search['order_by_menu'], $search['sort_by_menu']);
			}
		}else{
			$this->db->order_by('permission_menu_role.id', 'DESC');
		}

		if (isset($search['role_id'])) {
			$this->db->where('permission_menu_role.role_id', $search['role_id']);
		}
		if (isset($search['menu_id'])) {
			$this->db->where('permission_menu_role.menu_id', $search['menu_id']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('permission_menu_role.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('permission_menu_role.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if(isset($search['ignore_deleted'])){

		}else{
			$this->db->where('menu.is_deleted =', 0);
		}
		$this->db->join('menu', 'menu.id = permission_menu_role.menu_id','left');
		$query = $this->db->get('permission_menu_role');
		return $query->result_array();
	}
	public function permission_menu_role_create($data = [])
	{
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
		$this->db->insert('permission_menu_role', $data);
		$id = $this->db->insert_id();
		return $id;
	}
	public function permission_menu_role_update($data)
	{
		$this->db->where('id', $data['id']);
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['updated_at'] = isset($data['updated_at']) ? $data['updated_at'] : date('Y-m-d H:i:s');
		$this->db->update('permission_menu_role', $data);
	}
	public function permission_menu_role_find($search = [])
	{
		$this->db->select('
        	*
         ');
		$this->db->order_by('permission_menu_role.id', 'DESC');
		if (isset($search['role_id'])) {
			$this->db->where('permission_menu_role.role_id', $search['role_id']);
		}
		if (isset($search['menu_id'])) {
			$this->db->where('permission_menu_role.menu_id', $search['menu_id']);
		}
		if (isset($search['id'])) {
			$this->db->where('permission_menu_role.id', $search['id']);
		}
		$this->db->limit(1);
		$query = $this->db->get('permission_menu_role');
		return $query->row_array();
	}
	public function permission_menu_role_delete($search = [])
	{
		if (isset($search['role_id']) && !empty($search['role_id'])) {
			$this->db->where('permission_menu_role.role_id', $search['role_id']);
			if (isset($search['menu_id_list_ignore']) && count($search['menu_id_list_ignore']) > 0) {
				$this->db->where_not_in('permission_menu_role.menu_id', $search['menu_id_list_ignore']);
			}
			if (isset($search['id']) && !empty($search['id'])) {
				$this->db->where('permission_menu_role.id', $search['id']);
			}
			$this->db->delete('permission_menu_role');
		}else if (isset($search['id']) && !empty($search['id'])) {
			$this->db->where('permission_menu_role.id', $search['id']);
			$this->db->delete('permission_menu_role');
		}
	}
	public function permission_menu_role_cnt($search = [])
	{
		$this->db->select('
        	count(1) as cnt_row
         ',false);
		if (isset($search['role_id'])) {
			$this->db->where('permission_menu_role.role_id', $search['role_id']);
		}
		if (isset($search['menu_url'])) {
			if(strpos($search['menu_url'],"/") !== FALSE){
				$this->db->where('menu.url', $search['menu_url'])->or_where('menu.url',explode("/",$search['menu_url'])[0]);
			}else{
				$this->db->where('menu.url', $search['menu_url']);
			}
		}else if (isset($search['menu_url_ajax'])) {
			$this->db->where('menu.url LIKE', $search['menu_url_ajax'].'%');
		}
		if(isset($search['ignore_deleted'])){

		}else{
			$this->db->where('menu.is_deleted =', 0);
		}
		if(isset($search['group_menu_ignore_deleted'])){

		}else{
			$this->db->where('group_menu.is_deleted =', 0);
		}

		$this->db->join('menu', 'menu.id = permission_menu_role.menu_id','left');
		$this->db->join('group_menu', 'group_menu.id = menu.parent_id','left');
		$query = $this->db->get('permission_menu_role');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}

	public function permission_node_menu_role_cnt($search = [])
	{
		$this->db->select('
        	count(1) as cnt_row
         ',false);
		if (isset($search['role_id'])) {
			$this->db->where('permission_menu_role.role_id', $search['role_id']);
		}
		if (isset($search['menu_url'])) {
			if(strpos($search['menu_url'],"/") !== FALSE){
				$this->db->where('node_menu.url', $search['menu_url'])->or_where('node_menu.url',explode("/",$search['menu_url'])[0]);
			}else{
				$this->db->where('node_menu.url', $search['menu_url']);
			}
		}else if (isset($search['menu_url_ajax'])) {
			$this->db->where('node_menu.url LIKE', $search['menu_url_ajax'].'%');
		}
		if(isset($search['ignore_deleted'])){

		}else{
			$this->db->where('menu.is_deleted =', 0);
		}
		if(isset($search['group_menu_ignore_deleted'])){

		}else{
			$this->db->where('group_menu.is_deleted =', 0);
		}
		$this->db->join('menu', 'menu.id = permission_menu_role.menu_id','left');
		$this->db->join('group_menu', 'group_menu.id = menu.parent_id','left');
		$this->db->join('node_menu', 'node_menu.parent_id = menu.id','left');
		$query = $this->db->get('permission_menu_role');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}
}
