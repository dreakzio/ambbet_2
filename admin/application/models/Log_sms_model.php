<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Log_sms_model extends CI_Model
{
	public function log_sms_list($search = [])
	{
		$this->db->select('
        	*
        ');
		$this->db->limit($search['per_page'], $search['page']);
		$this->db->order_by('log_sms.id', 'DESC');
		if (isset($search['ip'])) {
			$this->db->where('log_sms.ip', $search['ip']);
		}
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('log_sms.phone', $search['search']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('log_sms.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('log_sms.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		$query = $this->db->get('log_sms');
		return $query->result_array();
	}
	public function log_sms_create($data = [])
	{
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
		$this->db->insert('log_sms', $data);
		$id = $this->db->insert_id();
		return $id;
	}
	public function log_sms_find($search = [])
	{
		$this->db->select('
        	*
         ');
		$this->db->order_by('log_sms.id', 'DESC');
		if (isset($search['ip'])) {
			$this->db->where('log_sms.ip', $search['ip']);
		}
		if (isset($search['id'])) {
			$this->db->where('log_sms.id', $search['id']);
		}
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('log_sms.phone', $search['search']);
		}
		$query = $this->db->get('log_sms');
		return $query->row_array();
	}
	public function log_sms_count($search = [])
	{
		$this->db->select('
			count(1) as cnt_row
		');
		if (isset($search['ip'])) {
			$this->db->where('log_sms.ip', $search['ip']);
		}
		if (isset($search['created_at_limit'])) {
			$this->db->where('log_sms.created_at >=', $search['created_at_limit']);
			$this->db->where('log_sms.created_at <=', date("Y-m-d H:i:s"));
		}
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('log_sms.phone', $search['search']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('log_sms.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('log_sms.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		$query = $this->db->get('log_sms');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}
}
