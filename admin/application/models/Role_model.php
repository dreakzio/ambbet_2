<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Role_model extends CI_Model
{
	public function role_list($search = [])
	{
		$this->db->select('
        	*
        ');
		$this->db->limit($search['per_page'], $search['page']);
		if (isset($search['role_id'])) {
			$this->db->where('role.role_id', $search['role_id']);
		}
		if (isset($search['role_name'])) {
			$this->db->where('role.role_name', $search['role_name']);
		}
		if (isset($search['role_level'])) {
			$this->db->where('role.role_level', $search['role_level']);
		}
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('role.role_name', $search['search']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('role.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('role.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if(isset($search['order_by']) && isset($search['sort_by'])){
			$this->db->order_by('role.'.$search['order_by'], $search['sort_by']);
		}else{
			$this->db->order_by('role.role_id', 'DESC');
		}
		$query = $this->db->get('role');
		return $query->result_array();
	}
	public function role_create($data = [])
	{
		$this->db->insert('role', $data);
		$id = $this->db->insert_id();
		return $id;
	}
	public function role_update($data)
	{
		$this->db->where('role_id', $data['role_id']);
		$this->db->update('role', $data);
	}
	public function role_find($search = [])
	{
		$this->db->select('
        	*
         ');
		$this->db->order_by('role.role_id', 'DESC');
		if (isset($search['role_id'])) {
			$this->db->where('role.role_id', $search['role_id']);
		}else if (isset($search['role_id_ignore'])) {
			$this->db->where('role.role_id <>', $search['role_id_ignore']);
		}
		if (isset($search['role_name'])) {
			$this->db->where('role.role_name', $search['role_name']);
		}
		if (isset($search['role_level'])) {
			$this->db->where('role.role_level', $search['role_level']);
		}
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('role.role_name', $search['search']);
		}
		$query = $this->db->get('role');
		return $query->row_array();
	}
	public function role_count($search = [])
	{
		$this->db->select('
			count(1) as cnt_row
		');
		if (isset($search['role_id'])) {
			$this->db->where('role.role_id', $search['role_id']);
		}
		if (isset($search['role_name'])) {
			$this->db->where('role.role_name', $search['role_name']);
		}
		if (isset($search['role_level'])) {
			$this->db->where('role.role_level', $search['role_level']);
		}
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('role.role_name', $search['search']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('role.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('role.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		$query = $this->db->get('role');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}
}
