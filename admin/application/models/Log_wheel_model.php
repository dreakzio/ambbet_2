<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Log_wheel_model extends CI_Model
{
	public function log_wheel_list($search = [])
	{
		$this->db->select('
        	log_wheel.*,
        	log_wheel.username as account_username,
        	log_wheel.username as account_full_name
        ');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('log_wheel.username', $search['search']);
			//$this->db->like('account.username', $search['search']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('log_wheel.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('log_wheel.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		//$this->db->join('account', 'account.id = log_wheel.account','left');
		$this->db->limit($search['per_page'], $search['page']);
		$this->db->order_by('log_wheel.id', 'DESC');
		$query = $this->db->get('log_wheel');
		return $query->result_array();
	}
	public function log_wheel_create($data = [])
	{
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
		$this->db->insert('log_wheel', $data);
		$id = $this->db->insert_id();
		return $id;
	}
	public function log_wheel_find($search = [])
	{
		$this->db->select('
        	log_wheel.*,
        	log_wheel.username as account_username,
        	log_wheel.username as account_full_name
         ',false);
		$this->db->order_by('log_wheel.id', 'DESC');
		if (isset($search['id'])) {
			$this->db->where('log_wheel.id', $search['id']);
		}
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('log_wheel.username', $search['search']);
			//$this->db->like('account.username', $search['search']);
		}
		//$this->db->join('account', 'account.id = log_wheel.account','left');
		$query = $this->db->get('log_wheel');
		return $query->row_array();
	}
	public function log_wheel_count($search = [])
	{
		$this->db->select('
         count(1) as cnt_row
         ',false);
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('log_wheel.username', $search['search']);
			//$this->db->like('account.username', $search['search']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('log_wheel.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('log_wheel.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		//$this->db->join('account', 'account.id = log_wheel.account','left');
		$query = $this->db->get('log_wheel');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}
}
