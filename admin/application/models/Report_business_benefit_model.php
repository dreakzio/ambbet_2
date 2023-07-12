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

	public function report_business_benefit_report_all_day_month_group_by($search = []){
		$group_by_process_date = 'DATE_FORMAT(process_date,"%Y-%m-%d") as created_at';
		if (isset($search['type'])) {
			if ($search['type'] == "month") {
				$group_by_process_date = 'DATE_FORMAT(process_date,"%Y-%m") as created_at';
			}else if($search['type'] == "current_date"){
				$group_by_process_date = 'process_date as created_at';
			}
		}
		$this->db->select('
			SUM(deposit) as sum_deposit,
			SUM(deposit_cnt) as sum_deposit_cnt,
			SUM(withdraw) as sum_withdraw,
			SUM(withdraw_cnt) as sum_withdraw_cnt,
			SUM(total) as sum_total,
			SUM(bonus) as sum_bonus,
			'.$group_by_process_date.'
        ');
		if (isset($search['created_at'])) {
			if (isset($search['type'])) {
				if ($search['type'] == "day") {
					$this->db->where('DATE_FORMAT( report_business_benefit.process_date, "%Y-%m") =', $search['created_at']);
					$this->db->group_by(array('DATE_FORMAT(report_business_benefit.process_date,"%Y-%m-%d")'));
				}else if ($search['type'] == "month") {
					$this->db->where('DATE_FORMAT( report_business_benefit.process_date, "%Y") =', $search['created_at']);
					$this->db->group_by(array('DATE_FORMAT(report_business_benefit.process_date,"%Y-%m")'));
				}else if ($search['type'] == "current_date") {
					$this->db->where('process_date =', $search['created_at']);
					$this->db->group_by(array('process_date'));
				}
			}
		}
		$query = $this->db->get('report_business_benefit');
		return $query->result_array();
	}
}
