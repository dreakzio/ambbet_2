<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Promotion_model extends CI_Model
{
    public function promotion_list()
    {
        $this->db->select('
              promotion.name,
        promotion.percent,
        promotion.turn,
                  promotion.turn_football,
          promotion.turn_step,
          promotion.turn_parlay,
          promotion.turn_game,
          promotion.turn_casino,
          promotion.turn_lotto,
          promotion.turn_m2,
          promotion.turn_multi_player,
          promotion.turn_trading,
          promotion.turn_keno,
        promotion.max_value,
        promotion.category,
        promotion.fix_amount_deposit_bonus,
        promotion.fix_amount_deposit,
        promotion.status,
        promotion.id,
        promotion.max_use,
        promotion.type,
        promotion.start_time,
        promotion.end_time,
        promotion.number_of_deposit_days,
        promotion.image,
        IF(promotion.image!="", CONCAT("'.site_url().'assets/images/promotion/'.'",promotion.image), null) as image_url
        ');
        $this->db->order_by('promotion.id', 'DESC');
        $this->db->where('promotion.deleted', 0);
        $query = $this->db->get('promotion');
        return $query->result_array();
    }

    public function promotion_create($data = [])
    {
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
        $this->db->insert('promotion', $data);
        $id = $this->db->insert_id();
        return $id;
    }
    public function promotion_update($data)
    {
        $this->db->where('id', $data['id']);
        $this->db->update('promotion', $data);
    }
    public function promotion_find($search = [])
    {
        $this->db->select('
          promotion.name,
          promotion.percent,
          promotion.turn,
                    promotion.turn_football,
          promotion.turn_step,
          promotion.turn_parlay,
          promotion.turn_game,
          promotion.turn_casino,
          promotion.turn_lotto,
          promotion.turn_m2,
          promotion.turn_multi_player,
          promotion.turn_trading,
          promotion.turn_keno,
          promotion.max_value,
          promotion.category,
          promotion.fix_amount_deposit_bonus,
          promotion.fix_amount_deposit,
          promotion.status,
          promotion.id,
          promotion.max_use,
          promotion.type,
          promotion.start_time,
          promotion.end_time,
          promotion.number_of_deposit_days,
          promotion.image,
          IF(promotion.image!="", CONCAT("'.site_url().'assets/images/promotion/'.'",promotion.image), null) as image_url
         ');
        $this->db->where('promotion.deleted', 0);
        if (isset($search['id'])) {
            $this->db->where('promotion.id', $search['id']);
        }
        $query = $this->db->get('promotion');
        return $query->row_array();
    }

}
