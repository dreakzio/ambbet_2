<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Log_line_notify_model extends CI_Model
{
	public function log_line_notify_list($search = [])
	{
		$this->db->select('
			*
        ');
		if (isset($search['start_date'])) {
			$this->db->where('created_at >=', $search['start_date']." 00:00:00");
			if(isset($search['end_date'])){
				$this->db->where('created_at <=', $search['end_date']." 23:59:59");
			}else{
				$this->db->where('created_at <=', $search['start_date']." 23:59:59");
			}
		}
		if(isset($search['created_at'])){
			$this->db->where('created_at >=', $search['created_at']." 01:00:00");
			$this->db->where('created_at <=', $search['created_at']." 02:00:00");
		}
		if (isset($search['status'])) {
			$this->db->where('status', $search['status']);
		}
		if (isset($search['type'])) {
			$this->db->where('type', $search['type']);
		}
		$this->db->order_by('log_line_notify.id', 'ASC');
		$this->db->limit($search['per_page'], $search['page']);
		$query = $this->db->get('log_line_notify');
		return $query->result_array();
	}

	public function log_line_notify_create($data = [])
	{
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
		$this->db->insert('log_line_notify', $data);
		$id = $this->db->insert_id();
		return $id;
	}
	public function log_line_notify_update($data)
	{
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['updated_at'] = isset($data['updated_at']) ? $data['updated_at'] : date('Y-m-d H:i:s');
		$this->db->where('id', $data['id']);
		$this->db->update('log_line_notify', $data);
	}
}
