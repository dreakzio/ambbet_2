<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Group_menu_model extends CI_Model
{
	public function group_menu_list($search = [])
	{
		$this->db->select('
        	*
        ');
		$this->db->limit($search['per_page'], $search['page']);
		if(isset($search['order_by']) && isset($search['sort_by'])){
			$this->db->order_by('group_menu.'.$search['order_by'], $search['sort_by']);
		}else{
			$this->db->order_by('group_menu.id', 'DESC');
		}
		if (isset($search['url'])) {
			$this->db->where('group_menu.url', $search['url']);
		}
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('group_menu.name', $search['search']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('group_menu.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('group_menu.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if(isset($search['ignore_deleted'])){

		}else{
			$this->db->where('group_menu.is_deleted =', 0);
		}

		$query = $this->db->get('group_menu');
		return $query->result_array();
	}
	public function group_menu_create($data = [])
	{
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
		$this->db->insert('group_menu', $data);
		$id = $this->db->insert_id();
		return $id;
	}
	public function group_menu_update($data)
	{
		$this->db->where('id', $data['id']);
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['updated_at'] = isset($data['updated_at']) ? $data['updated_at'] : date('Y-m-d H:i:s');
		$this->db->update('group_menu', $data);
	}
	public function group_menu_find($search = [])
	{
		$this->db->select('
        	*
         ');
		$this->db->order_by('group_menu.id', 'DESC');
		if (isset($search['id'])) {
			$this->db->where('group_menu.id', $search['id']);
		}
		if (isset($search['url'])) {
			$this->db->where('group_menu.url', $search['url']);
		}
		if (isset($search['name'])) {
			$this->db->where('group_menu.name', $search['name']);
		}
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('group_menu.name', $search['search']);
		}
		if(isset($search['ignore_deleted'])){

		}else{
			$this->db->where('group_menu.is_deleted =', 0);
		}
		$query = $this->db->get('group_menu');
		return $query->row_array();
	}
	public function group_menu_count($search = [])
	{
		$this->db->select('
			count(1) as cnt_row
		');
		if (isset($search['url'])) {
			$this->db->where('group_menu.url', $search['url']);
		}
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('group_menu.name', $search['search']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('group_menu.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('group_menu.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if(isset($search['ignore_deleted'])){

		}else{
			$this->db->where('group_menu.is_deleted =', 0);
		}
		$query = $this->db->get('group_menu');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}
}
