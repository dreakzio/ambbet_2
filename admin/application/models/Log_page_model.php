<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Log_page_model extends CI_Model
{
	public function log_page_list($search = [])
	{
		$canManage = canManageRole()[$_SESSION['user']['role']];
		$this->db->select('
			log_page.id,
		    log_page.ip,
		    log_page.page_name,
		    log_page.description,
		    log_page.page_url,
		    log_page.created_at,
		    log_page.updated_at,
         	CASE WHEN log_page.username IS NULL THEN log_page.admin ELSE log_page.username END AS admin_username
        ',false);
		$this->db->order_by('log_page.id', 'DESC');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('admin.username', $search['search']);
			$this->db->or_like('admin.full_name', $search['search']);
			$this->db->or_like('log_page.description', $search['search']);
			$this->db->or_like('log_page.page_name', $search['search']);
			$this->db->or_like('log_page.page_url', $search['search']);
			$this->db->group_end();
		}
		if(isset($search['role_list']) && is_array($search['role_list']) && count($search['role_list']) > 0){
			$this->db->where_in('log_page.role', $search['role_list']);
		}else if(isset($_SESSION['user']) && isset($_SESSION['user']['role'])){
			if(isset($search['role']) && array_key_exists($search['role'],$canManage)){
				$this->db->where_in('log_page.role', [$search['role'],null]);
			}else{
				$this->db->group_start();
				$this->db->where_in('log_page.role', $canManage)->or_where('log_page.role IS NULL');
				$this->db->group_end();
			}
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('log_page.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('log_page.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		$this->db->limit($search['per_page'], $search['page']);
		//$this->db->join('account as admin', 'admin.id = log_page.admin','left');
		$query = $this->db->get('log_page');
		return $query->result_array();
	}
	public function log_page_create($data = [])
	{
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
		$this->db->insert('log_page', $data);
		$id = $this->db->insert_id();
		return $id;
	}
	public function log_page_update($data)
	{
		$this->db->where('id', $data['id']);
		$this->db->update('log_page', $data);
	}
	public function log_page_find($search = [])
	{
		$canManage = canManageRole()[$_SESSION['user']['role']];
		$this->db->select('
			log_page.id,
		    log_page.ip,
		    log_page.page_name,
		    log_page.description,
		    log_page.page_url,
		    log_page.created_at,
		    log_page.updated_at,
         	CASE WHEN log_page.username IS NULL THEN log_page.admin ELSE log_page.username END AS admin_username
        ',false);
		$this->db->order_by('log_page.id', 'DESC');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('admin.username', $search['search']);
			$this->db->or_like('admin.full_name', $search['search']);
			$this->db->or_like('log_page.description', $search['search']);
			$this->db->or_like('log_page.page_name', $search['search']);
			$this->db->or_like('log_page.page_url', $search['search']);
		}
		if (isset($search['id'])) {
			$this->db->where('log_page.id', $search['id']);
		}
		if(isset($search['role_list']) && is_array($search['role_list']) && count($search['role_list']) > 0){
			$this->db->where_in('log_page.role', $search['role_list']);
		}else if(isset($_SESSION['user']) && isset($_SESSION['user']['role'])){
			if(isset($search['role']) && array_key_exists($search['role'],$canManage)){
				$this->db->where_in('log_page.role', [$search['role'],null]);
			}else{
				$this->db->group_start();
				$this->db->where_in('log_page.role', $canManage)->or_where('log_page.role IS NULL');
				$this->db->group_end();
			}
		}
		//$this->db->join('account as admin', 'admin.id = log_page.admin','left');
		$query = $this->db->get('log_page');
		return $query->row_array();
	}

	public function log_page_count($search = [])
	{
		$canManage = canManageRole()[$_SESSION['user']['role']];
		$this->db->select('
         count(1) as cnt_row
         ',false);
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('admin.username', $search['search']);
			$this->db->or_like('admin.full_name', $search['search']);
			$this->db->or_like('log_page.description', $search['search']);
			$this->db->or_like('log_page.page_name', $search['search']);
			$this->db->or_like('log_page.page_url', $search['search']);
			$this->db->group_end();
		}
		if(isset($search['role_list']) && is_array($search['role_list']) && count($search['role_list']) > 0){
			$this->db->where_in('log_page.role', $search['role_list']);
		}else if(isset($_SESSION['user']) && isset($_SESSION['user']['role'])){
			if(isset($search['role']) && array_key_exists($search['role'],$canManage)){
				$this->db->where_in('log_page.role', [$search['role'],null]);
			}else{
				$this->db->group_start();
				$this->db->where_in('log_page.role', $canManage)->or_where('log_page.role IS NULL');
				$this->db->group_end();
			}
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('log_page.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('log_page.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		//$this->db->join('account as admin', 'admin.id = log_page.admin','left');
		$query = $this->db->get('log_page');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}
}
