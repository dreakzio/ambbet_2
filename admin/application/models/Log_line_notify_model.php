<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Log_line_notify_model extends CI_Model
{
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
	public function log_line_notify_list($search = [])
	{
		$this->db->select('
        	*
        ');
		$this->db->limit($search['per_page'], $search['page']);
		$this->db->order_by('log_line_notify.id', 'DESC');

		if (isset($search['search']) && !empty(trim($search['search']))) {
			if($search['search'] == "ฝาก"){
				$this->db->where('log_line_notify.type', "1");
			}else if($search['search'] == "ถอน"){
				$this->db->where('log_line_notify.type', "2");
			}else if($search['search'] == "รายงานประจำวัน"){
				$this->db->where('log_line_notify.type', "3");
			}else if($search['search'] == "อื่นๆ"){
				$this->db->where('log_line_notify.type', "4");
			}else if($search['search'] == "สมัครสมาชิก"){
				$this->db->where('log_line_notify.type', "5");
			}else if($search['search'] == "รอดำเนินการ"){
				$this->db->where('log_line_notify.status', "0");
			}else if($search['search'] == "ดำเนินการเรียบร้อย"){
				$this->db->where('log_line_notify.status', "1");
			}else{
				$this->db->like('log_line_notify.message', $search['search']);
			}
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('log_line_notify.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('log_line_notify.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		$query = $this->db->get('log_line_notify');
		return $query->result_array();
	}

	public function log_line_notify_count($search = [])
	{
		$this->db->select('
			count(1) as cnt_row
		');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			if($search['search'] == "ฝาก"){
				$this->db->where('log_line_notify.type', "1");
			}else if($search['search'] == "ถอน"){
				$this->db->where('log_line_notify.type', "2");
			}else if($search['search'] == "รายงานประจำวัน"){
				$this->db->where('log_line_notify.type', "3");
			}else if($search['search'] == "อื่นๆ"){
				$this->db->where('log_line_notify.type', "4");
			}else if($search['search'] == "สมัครสมาชิก"){
				$this->db->where('log_line_notify.type', "5");
			}else if($search['search'] == "รอดำเนินการ"){
				$this->db->where('log_line_notify.status', "0");
			}else if($search['search'] == "ดำเนินการเรียบร้อย"){
				$this->db->where('log_line_notify.status', "1");
			}else{
				$this->db->like('log_line_notify.message', $search['search']);
			}
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('log_line_notify.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('log_line_notify.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		$query = $this->db->get('log_line_notify');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}
}
