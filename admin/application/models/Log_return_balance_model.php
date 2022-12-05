<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Log_return_balance_model extends CI_Model
{
	public function log_return_balance_list($search = [])
	{
		$this->db->select('
        	log_return_balance.*,
        	log_return_balance.username as account_username,
        	log_return_balance.username as full_name
        ');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('log_return_balance.username', $search['search']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('log_return_balance.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('log_return_balance.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		$this->db->limit($search['per_page'], $search['page']);
		$this->db->order_by('log_return_balance.id', 'DESC');
		$query = $this->db->get('log_return_balance');
		$results = $query->result_array();
		return $results;
	}
	public function log_return_balance_create($data = [])
	{
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
		$this->db->insert('log_return_balance', $data);
		$id = $this->db->insert_id();
		return $id;
	}
	public function log_return_balance_find($search = [])
	{
		$this->db->select('
        	log_return_balance.*,
        	log_return_balance.username as account_username,
        	log_return_balance.username as full_name
         ');
		$this->db->order_by('log_return_balance.id', 'DESC');
		if (isset($search['id'])) {
			$this->db->where('log_return_balance.id', $search['id']);
		}
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('log_return_balance.username', $search['search']);
		}
		$query = $this->db->get('log_return_balance');
		$result = $query->row_array();
		return $result;
	}
	public function log_return_balance_count($search = [])
	{
		$this->db->select('
          count(1) as cnt_row
         ',false);
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('log_return_balance.username', $search['search']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('log_return_balance.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('log_return_balance.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		$query = $this->db->get('log_return_balance');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}
	private function getAccountByAccountIdIn($account_id_list = []){
		$this->db->select('
			account.id as account_id,
			account.username,
			account.full_name
        ');
		$this->db->where_in('id', $account_id_list );
		$query = $this->db->get('account');
		$results = $query->result_array();
		$data=[];
		foreach($results as $result){
			$data[$result['account_id']] = $result;
		}
		return $data;
	}
}
