<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Log_add_credit_model extends CI_Model
{
	public function log_add_credit_list($search = [])
	{
		$this->db->select('
        	log_add_credit.*
        ');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('log_add_credit.username', $search['search']);
		}
		if (isset($search['type'])) {
			$this->db->where('log_add_credit.type', $search['type']);
		}
		if (isset($search['account'])) {
			$this->db->where('log_add_credit.account', $search['account']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('log_add_credit.created_at >=', date("{$search['date_start']}".(strpos($search['date_start'],":") !== false ? "" : " 00:00:00")));
			$this->db->where('log_add_credit.created_at <=', date("{$search['date_end']}".(strpos($search['date_start'],":") !== false ? "" : " 23:59:59")));
		}
		$this->db->limit($search['per_page'], $search['page']);
		$this->db->order_by('log_add_credit.id', 'DESC');
		$query = $this->db->get('log_add_credit');
		return $query->result_array();
	}

	public function log_add_credit_list_page($search = [])
	{
		$this->db->select('
        	log_add_credit.*
        ');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('log_add_credit.username', $search['search']);
		}
		if (isset($search['type'])) {
			$this->db->where('log_add_credit.type', $search['type']);
		}
		if (isset($search['account'])) {
			$this->db->where('log_add_credit.account', $search['account']);
		}
		if (isset($search['amount_more_than_equal'])) {
			$this->db->where('log_add_credit.amount >=', $search['amount_more_than_equal']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('log_add_credit.created_at >=', date("{$search['date_start']}".(strpos($search['date_start'],":") !== false ? "" : " 00:00:00")));
			$this->db->where('log_add_credit.created_at <=', date("{$search['date_end']}".(strpos($search['date_start'],":") !== false ? "" : " 23:59:59")));
		}
		$this->db->limit($search['per_page'], $search['page']);
		$this->db->order_by('log_add_credit.id', 'DESC');
		$query = $this->db->get('log_add_credit');
		return $query->result_array();
	}

	public function log_add_credit_create($data = [])
	{
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
		$this->db->insert('log_add_credit', $data);
		$id = $this->db->insert_id();
		return $id;
	}
	public function log_add_credit_find($search = [])
	{
		$this->db->select('
        	log_add_credit.*
         ');
		$this->db->order_by('log_add_credit.id', 'DESC');
		if (isset($search['id'])) {
			$this->db->where('log_add_credit.id', $search['id']);
		}
		if (isset($search['type'])) {
			$this->db->where('log_add_credit.type', $search['type']);
		}
		if (isset($search['account'])) {
			$this->db->where('log_add_credit.account', $search['account']);
		}
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('log_add_credit.username', $search['search']);
		}
		$query = $this->db->get('log_add_credit');
		return $query->row_array();
	}
	public function log_add_credit_count($search = [])
	{
		$this->db->select('
         log_add_credit.id
         ');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('log_add_credit.username', $search['search']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('log_add_credit.created_at >=', date("{$search['date_start']}".(strpos($search['date_start'],":") !== false ? "" : " 00:00:00")));
			$this->db->where('log_add_credit.created_at <=', date("{$search['date_end']}".(strpos($search['date_start'],":") !== false ? "" : " 23:59:59")));
		}
		if (isset($search['type'])) {
			$this->db->where('log_add_credit.type', $search['type']);
		}
		if (isset($search['account'])) {
			$this->db->where('log_add_credit.account', $search['account']);
		}
		$query = $this->db->get('log_add_credit');
		return $query->num_rows();
	}

	public function log_add_credit_sum_amount($search = [])
	{
		$this->db->select('
         sum(amount) as sum_amount
         ');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('log_add_credit.username', $search['search']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('log_add_credit.created_at >=', date("{$search['date_start']}".(strpos($search['date_start'],":") !== false ? "" : " 00:00:00")));
			$this->db->where('log_add_credit.created_at <=', date("{$search['date_end']}".(strpos($search['date_start'],":") !== false ? "" : " 23:59:59")));
		}
		if (isset($search['type'])) {
			$this->db->where('log_add_credit.type', $search['type']);
		}
		if (isset($search['account'])) {
			$this->db->where('log_add_credit.account', $search['account']);
		}
		$query = $this->db->get('log_add_credit');
		return $query->row_array();
	}

	public function log_add_credit_list_excel($search = [])
	{
		$this->db->select('
        	log_add_credit.*
        ');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('log_add_credit.username', $search['search']);
		}
		if (isset($search['type'])) {
			$this->db->where('log_add_credit.type', $search['type']);
		}
		if (isset($search['account'])) {
			$this->db->where('log_add_credit.account', $search['account']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('log_add_credit.created_at >=', date("{$search['date_start']}".(strpos($search['date_start'],":") !== false ? "" : " 00:00:00")));
			$this->db->where('log_add_credit.created_at <=', date("{$search['date_end']}".(strpos($search['date_start'],":") !== false ? "" : " 23:59:59")));
		}
		$this->db->order_by('log_add_credit.id', 'DESC');
		$query = $this->db->get('log_add_credit');
		return $query->result_array();
	}
}
