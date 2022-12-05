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
	public function use_promotion_count($search = [])
	{
		$this->db->select('
         count(1) as cnt_row
         ');

		if (isset($search['account'])) {
			$this->db->where('finance.account', $search['account']);
		}
		if (isset($search['account'])) {
			$this->db->where('use_promotion.promotion', $search['promotion']);
		}
		if (isset($search['date_from'])) {
			$this->db->where('use_promotion.created_at >=', $search['date_from'].' 00:00:00');
			$this->db->where('use_promotion.created_at <=', $search['date_to'].' 23:59:59');
		}
		if (isset($search['start_time'])) {
			$this->db->where('use_promotion.created_at >=', $search['start_time']);
			$this->db->where('use_promotion.created_at <=', $search['end_time']);
		}
		$this->db->join('finance', 'finance.id = use_promotion.finance');
		$query = $this->db->get('use_promotion');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}
	public function promotion_list($search = [])
	{
		$this->db->select('
       		use_promotion.id,
       		use_promotion.finance,
       		use_promotion.promotion,
       		use_promotion.turn_football,
       		use_promotion.turn_step,
       		use_promotion.turn_parlay,
       		use_promotion.turn_game,
       		use_promotion.turn_casino,
       		use_promotion.turn_lotto,
       		use_promotion.turn_m2,
       		use_promotion.turn_multi_player,
       		use_promotion.turn_trading,
       		use_promotion.turn_keno,
       		use_promotion.promotion_name,
        ');
		if (isset($search['id'])) {
			$this->db->where('use_promotion.id', $search['id']);
		}
		if (isset($search['finance'])) {
			$this->db->where('use_promotion.finance', $search['finance']);
		}
		if (isset($search['limit']) && (int)$search['limit'] > 0) {
			$this->db->limit((int)$search['limit']);
		}
		if (isset($search['order_by']) && isset($search['order_by_type'])) {
			$this->db->order_by($search['order_by'], $search['order_by_type']);
		}else{
			$this->db->order_by('id', 'DESC');
		}
		$query = $this->db->get('use_promotion');
		return $query->result_array();
	}
}
