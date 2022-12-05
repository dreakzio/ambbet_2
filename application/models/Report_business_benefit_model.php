<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_business_benefit_model extends CI_Model
{
	public function report_business_benefit_list($search = [])
	{
		$this->db->select('
        	*
        ');
		$this->db->order_by('report_business_benefit.process_date', 'ASC');
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('report_business_benefit.process_date >=', date("{$search['date_start']}"));
			$this->db->where('report_business_benefit.process_date <=', date("{$search['date_end']}"));
		}
		$query = $this->db->get('report_business_benefit');
		return $query->result_array();
	}
	public function report_business_benefit_create($data = [])
	{
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['process_date'] = isset($data['process_date']) ? $data['process_date'] : date('Y-m-d');
		$data['updated_at'] = isset($data['updated_at']) ? $data['updated_at'] : date('Y-m-d H:i:s');
		$this->db->insert('report_business_benefit', $data);
		$id = $this->db->insert_id();
		return $id;
	}
	public function report_business_benefit_update($data)
	{
		$this->db->where('id', $data['id']);
		$data['updated_at'] = isset($data['updated_at']) ? $data['updated_at'] : date('Y-m-d H:i:s');
		$this->db->update('report_business_benefit', $data);
	}
	public function report_business_benefit_update_by_date($data)
	{
		$this->db->where('process_date', $data['process_date']);
		$this->db->update('report_business_benefit', $data);
	}
	public function report_business_benefit_find($search = [])
	{
		$this->db->select('
        	*
         ');
		$this->db->order_by('report_business_benefit.id', 'DESC');
		if (isset($search['id'])) {
			$this->db->where('report_business_benefit.id', $search['id']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('report_business_benefit.process_date >=', date("{$search['date_start']}"));
			$this->db->where('report_business_benefit.process_date <=', date("{$search['date_end']}"));
		}
		$query = $this->db->get('report_business_benefit');
		return $query->row_array();
	}
	public function report_business_benefit_count($search = [])
	{
		$this->db->select('
			count(1) as cnt_row
		');
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('report_business_benefit.process_date >=', date("{$search['date_start']}"));
			$this->db->where('report_business_benefit.process_date <=', date("{$search['date_end']}"));
		}
		$query = $this->db->get('report_business_benefit');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}
	public function report_business_benefit_sum_year_list($search = [])
	{
		$this->db->select('
        	DATE_FORMAT(process_date,"%Y-%m") as process_date,
        	SUM(deposit) as sum_deposit,
        	SUM(withdraw) as sum_withdraw,
        	SUM(total) as sum_total
        ');
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('report_business_benefit.process_date >=', date("{$search['date_start']}"));
			$this->db->where('report_business_benefit.process_date <=', date("{$search['date_end']}"));
		}
		$this->db->order_by('process_date', 'ASC');
		$this->db->group_by(array('DATE_FORMAT(report_business_benefit.process_date,"%Y-%m")'));
		$query = $this->db->get('report_business_benefit');
		return $query->result_array();
	}
}
