<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Log_return_balance_model extends CI_Model
{
	public function log_return_balance_list($search = [])
	{
		$this->db->select('
        	*
        ');
		$this->db->limit($search['per_page'], $search['page']);
		$this->db->order_by('log_return_balance.id', 'DESC');
		$query = $this->db->get('log_return_balance');
		return $query->result_array();
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
        	*
         ');
		$this->db->order_by('log_return_balance.id', 'DESC');
		if (isset($search['id'])) {
			$this->db->where('log_return_balance.id', $search['id']);
		}
		$query = $this->db->get('log_return_balance');
		return $query->row_array();
	}
	public function log_return_balance_count($search = [])
	{
		$query = $this->db->get('log_return_balance');
		return $query->num_rows();
	}
}
