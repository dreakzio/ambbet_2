<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Log_add_credit_model extends CI_Model
{
	public function log_add_credit_list($search = [])
	{
		$this->db->select('
        	*
        ');
		if (isset($search['type'])) {
			$this->db->where('log_add_credit.type', $search['type']);
		}else if(isset($search['type_list'])){
			$this->db->where_in('log_add_credit.type', $search['type_list']);
		}
		if (isset($search['account'])) {
			$this->db->where('log_add_credit.account', $search['account']);
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
        	*
         ');
		$this->db->order_by('log_add_credit.id', 'DESC');
		if (isset($search['id'])) {
			$this->db->where('log_add_credit.id', $search['id']);
		}
		$query = $this->db->get('log_add_credit');
		return $query->row_array();
	}
	public function log_add_credit_count($search = [])
	{
		$query = $this->db->get('log_add_credit');
		return $query->num_rows();
	}
}
