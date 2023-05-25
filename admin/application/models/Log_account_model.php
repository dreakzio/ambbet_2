<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Log_account_model extends CI_Model
{
	public function log_account_list($search = [])
	{
		$canManage = canManageRole()[$_SESSION['user']['role']];
		$this->db->select('
        	log_account.id,
        	log_account.manage_by_username,
        	log_account.username,
        	log_account.role,
        	log_account.data_before,
        	log_account.data_after,
        	log_account.created_at,
        	log_account.updated_at
        ');
		$this->db->limit($search['per_page'], $search['page']);
		$this->db->limit(25);
		$this->db->order_by('log_account.id', 'DESC');
		if(isset($search['role_list']) && is_array($search['role_list']) && count($search['role_list']) > 0){
			$this->db->where_in('log_account.role', $search['role_list']);
		}else if(isset($_SESSION['user']) && isset($_SESSION['user']['role'])){
			$this->db->where_in('log_account.role',$canManage);
		}
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('log_account.username', $search['search']);
			$this->db->or_like('log_account.manage_by_username', $search['search']);
			$this->db->group_end();
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('log_account.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('log_account.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		$query = $this->db->get('log_account');
		return $query->result_array();
	}
	public function log_account_create($data = [])
	{
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
		$this->db->insert('log_account', $data);
		$id = $this->db->insert_id();
		return $id;
	}
	public function log_account_find($search = [])
	{
		$canManage = canManageRole()[$_SESSION['user']['role']];
		$this->db->select('
        	log_account.id,
        	log_account.manage_by_username,
        	log_account.username,
        	log_account.role,
        	log_account.data_before,
        	log_account.data_after,
        	log_account.created_at,
        	log_account.updated_at
         ');
		$this->db->order_by('log_account.id', 'DESC');
		if (isset($search['id'])) {
			$this->db->where('log_account.id', $search['id']);
		}
		if(isset($search['role_list']) && is_array($search['role_list']) && count($search['role_list']) > 0){
			$this->db->where_in('log_account.role', $search['role_list']);
		}else if(isset($_SESSION['user']) && isset($_SESSION['user']['role'])){
			$this->db->where_in('log_account.role', $canManage);
		}
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('log_account.username', $search['search']);
			$this->db->or_like('log_account.manage_by_username', $search['search']);
			$this->db->group_end();
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('log_account.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('log_account.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		$query = $this->db->get('log_account');
		return $query->row_array();
	}
	public function log_account_count($search = [])
	{
		$canManage = canManageRole()[$_SESSION['user']['role']];
		$this->db->select('
			count(1) as cnt_row
		');
		if(isset($search['role_list']) && is_array($search['role_list']) && count($search['role_list']) > 0){
			$this->db->where_in('log_account.role', $search['role_list']);
		}else if(isset($_SESSION['user']) && isset($_SESSION['user']['role'])){
			$this->db->where_in('log_account.role', $canManage);
		}
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('log_account.username', $search['search']);
			$this->db->or_like('log_account.manage_by_username', $search['search']);
			$this->db->group_end();
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('log_account.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('log_account.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		$query = $this->db->get('log_account');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}
}
