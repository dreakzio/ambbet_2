<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Menu_model extends CI_Model
{
	public function menu_list($search = [])
	{
		$this->db->select('
        	menu.*,
        	group_menu.name as group_menu_name,
        	group_menu.is_deleted as group_menu_is_deleted
        ',false);
		$this->db->limit($search['per_page'], $search['page']);
		if(isset($search['order_by']) && isset($search['sort_by'])){
			if(isset($search['order_by_group_menu']) && isset($search['sort_by_group_menu'])){
				$this->db->order_by('group_menu.'.$search['order_by_group_menu'], $search['sort_by_group_menu']);
			}
			$this->db->order_by('menu.'.$search['order_by'], $search['sort_by']);
		}else{
			$this->db->order_by('menu.id', 'DESC');
		}
		if (isset($search['url'])) {
			$this->db->where('menu.url', $search['url']);
		}
		if (isset($search['parent_id'])) {
			$this->db->where('menu.parent_id', $search['parent_id']);
		}
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('menu.name', $search['search']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('menu.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('menu.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if(isset($search['ignore_deleted'])){

		}else{
			$this->db->where('menu.is_deleted =', 0);
		}

		$this->db->join('group_menu', 'group_menu.id = menu.parent_id','left');
		$query = $this->db->get('menu');
		return $query->result_array();
	}
	public function menu_create($data = [])
	{
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
		$this->db->insert('menu', $data);
		$id = $this->db->insert_id();
		return $id;
	}
	public function menu_update($data)
	{
		$this->db->where('id', $data['id']);
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['updated_at'] = isset($data['updated_at']) ? $data['updated_at'] : date('Y-m-d H:i:s');
		$this->db->update('menu', $data);
	}
	public function menu_find($search = [])
	{
		$this->db->select('
        	menu.*,
        	group_menu.name as group_menu_name,
        	group_menu.is_deleted as group_menu_is_deleted
         ',false);
		$this->db->order_by('menu.id', 'DESC');
		if (isset($search['id'])) {
			$this->db->where('menu.id', $search['id']);
		}
		if (isset($search['parent_id'])) {
			$this->db->where('menu.parent_id', $search['parent_id']);
		}
		if (isset($search['url'])) {
			$this->db->where('menu.url', $search['url']);
		}
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('menu.name', $search['search']);
		}
		if(isset($search['ignore_deleted'])){

		}else{
			$this->db->where('menu.is_deleted =', 0);
		}
		$this->db->join('group_menu', 'group_menu.id = menu.parent_id','left');
		$query = $this->db->get('menu');
		return $query->row_array();
	}
	public function menu_count($search = [])
	{
		$this->db->select('
			count(1) as cnt_row
		',false);
		if (isset($search['url'])) {
			$this->db->where('menu.url', $search['url']);
		}
		if (isset($search['parent_id'])) {
			$this->db->where('menu.parent_id', $search['parent_id']);
		}
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('menu.name', $search['search']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('menu.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('menu.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if(isset($search['ignore_deleted'])){

		}else{
			$this->db->where('menu.is_deleted =', 0);
		}
		$this->db->join('group_menu', 'group_menu.id = menu.parent_id','left');
		$query = $this->db->get('menu');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}
}
