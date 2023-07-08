<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Use_promotion_model extends CI_Model
{
    public function use_promotion_create($data = [])
    {
        $this->db->insert('use_promotion', $data);
        $id = $this->db->insert_id();
        return $id;
    }

	public function use_promotion_sum_use($search)
	{
		$this->db->select('
           count(id) as total,
           (SUM(sum_amount) - SUM(amount)) as total_bonus,
           use_promotion.promotion
        ');
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('use_promotion.created_at >=', date("{$search['date_start']}".(strpos($search['date_start'],":") !== false ? "" : " 00:00:00")));
			$this->db->where('use_promotion.created_at <=', date("{$search['date_end']}".(strpos($search['date_start'],":") !== false ? "" : " 23:59:59")));
		}
		$this->db->group_by(array("use_promotion.promotion"));
		$query = $this->db->get('use_promotion');
		$results = $query->result_array();
		return $results;
	}
	
	public function point_bonus_all()
	{
		$this->db->select('
           (SUM(sum_amount) - SUM(amount)) as total_bonus,
        ');
		$this->db->where('created_at >=', date('Y-m-d 00:00:00'));
		$this->db->where('created_at <=', date('Y-m-d H:i:s'));
		$query = $this->db->get('use_promotion');
		$my_bo = $query->result_array();
		return $my_bo;
	}

	public function point_bonus_all_month()
	{
		$this->db->select('
           (SUM(sum_amount) - SUM(amount)) as total_bonus_month,
        ');
		$this->db->where('MONTH(created_at)', date('m'));
		$this->db->where('YEAR(created_at)', date('Y'));
		$query = $this->db->get('use_promotion');
		$my_bo_month = $query->result_array();
		return $my_bo_month;
	}
}
