<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Node_menu_model extends CI_Model
{
	public function node_menu_list($search = [])
	{
		$this->db->select('
        	node_menu.*,
        	menu.name as menu_name,
        	menu.is_deleted as menu_is_deleted,
        	group_menu.name as group_menu_name,
        	group_menu.is_deleted as group_menu_is_deleted
        ',false);
		$this->db->limit($search['per_page'], $search['page']);
		if(isset($search['order_by']) && isset($search['sort_by'])){
			if(isset($search['order_by_group_menu']) && isset($search['sort_by_group_menu'])){
				$this->db->order_by('group_menu.'.$search['order_by_group_menu'], $search['sort_by_group_menu']);
				if(isset($search['order_by_menu']) && isset($search['sort_by_menu'])){
					$this->db->order_by('menu.'.$search['order_by_menu'], $search['sort_by_menu']);
				}
			}
			$this->db->order_by('node_menu.'.$search['order_by'], $search['sort_by']);
		}else{
			$this->db->order_by('node_menu.id', 'DESC');
		}
		if (isset($search['url'])) {
			$this->db->where('node_menu.url', $search['url']);
		}
		if (isset($search['parent_id'])) {
			$this->db->where('node_menu.parent_id', $search['parent_id']);
		}
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('node_menu.name', $search['search']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('node_menu.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('node_menu.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if(isset($search['ignore_deleted'])){

		}else{
			$this->db->where('node_menu.is_deleted =', 0);
		}
		$this->db->join('menu', 'menu.id = node_menu.parent_id','left');
		$this->db->join('group_menu', 'group_menu.id = menu.parent_id','left');
		$query = $this->db->get('node_menu');
		return $query->result_array();
	}
	public function node_menu_create($data = [])
	{
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
		$this->db->insert('node_menu', $data);
		$id = $this->db->insert_id();
		return $id;
	}
	public function node_menu_update($data)
	{
		$this->db->where('id', $data['id']);
		$this->db->update('node_menu', $data);
	}
	public function node_menu_find($search = [])
	{
		$this->db->select('
        	node_menu.*,
        	menu.name as menu_name,
        	menu.is_deleted as menu_is_deleted,
        	group_menu.name as group_menu_name,
        	group_menu.is_deleted as group_menu_is_deleted
         ',false);
		$this->db->order_by('node_menu.id', 'DESC');
		if (isset($search['id'])) {
			$this->db->where('node_menu.id', $search['id']);
		}
		if (isset($search['parent_id'])) {
			$this->db->where('node_menu.parent_id', $search['parent_id']);
		}
		if (isset($search['url'])) {
			$this->db->where('node_menu.url', $search['url']);
		}
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('node_menu.name', $search['search']);
		}
		if(isset($search['ignore_deleted'])){

		}else{
			$this->db->where('node_menu.is_deleted =', 0);
		}
		$this->db->join('menu', 'menu.id = node_menu.parent_id','left');
		$this->db->join('group_menu', 'group_menu.id = menu.parent_id','left');
		$query = $this->db->get('node_menu');
		return $query->row_array();
	}
	public function node_menu_count($search = [])
	{
		$this->db->select('
			count(1) as cnt_row
		',false);
		if (isset($search['url'])) {
			$this->db->where('node_menu.url', $search['url']);
		}
		if (isset($search['parent_id'])) {
			$this->db->where('node_menu.parent_id', $search['parent_id']);
		}
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('node_menu.name', $search['search']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('node_menu.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('node_menu.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if(isset($search['ignore_deleted'])){

		}else{
			$this->db->where('node_menu.is_deleted =', 0);
		}
		$this->db->join('menu', 'menu.id = node_menu.parent_id','left');
		$this->db->join('group_menu', 'group_menu.id = menu.parent_id','left');
		$query = $this->db->get('node_menu');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}
}
