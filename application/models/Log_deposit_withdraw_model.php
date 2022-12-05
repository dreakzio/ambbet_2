<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Log_deposit_withdraw_model extends CI_Model
{
	public function log_deposit_withdraw_list($search = [])
	{
		$this->db->select('
			log_deposit_withdraw.id,
		    log_deposit_withdraw.amount,
		    log_deposit_withdraw.amount_before,
		    log_deposit_withdraw.type,
		    log_deposit_withdraw.promotion_name,
		    log_deposit_withdraw.description,
		    log_deposit_withdraw.created_at,
        	CASE WHEN account.username IS NULL THEN NULL ELSE account.username END AS username,
         	CASE WHEN admin.username IS NULL THEN "AUTO" ELSE admin.username END AS admin_username
        ',false);
		$this->db->order_by('log_deposit_withdraw.id', 'DESC');
		if (isset($search['search'])) {
			$this->db->like('account.username', $search['search']);
			$this->db->or_like('log_deposit_withdraw.description', $search['search']);
			if(strpos($search['search'],"ฝาก") !== false){
				$this->db->or_like('log_deposit_withdraw.type', 1);
			}else if(strpos($search['search'],"ถอน") !== false){
				$this->db->or_like('log_deposit_withdraw.type', 2);
			}
		}
		$this->db->limit($search['per_page'], $search['page']);
		$this->db->join('account', 'account.id = log_deposit_withdraw.account','left');
		$this->db->join('account as admin', 'admin.id = log_deposit_withdraw.admin','left');
		$query = $this->db->get('log_deposit_withdraw');
		return $query->result_array();
	}
	public function log_deposit_withdraw_create($data = [])
	{
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
		$this->db->insert('log_deposit_withdraw', $data);
		$id = $this->db->insert_id();
		return $id;
	}
	public function log_deposit_withdraw_update($data)
	{
		$this->db->where('id', $data['id']);
		$this->db->update('log_deposit_withdraw', $data);
	}
	public function log_deposit_withdraw_find($search = [])
	{
		$this->db->select('
		    log_deposit_withdraw.id,
		    log_deposit_withdraw.amount,
		    log_deposit_withdraw.amount_before,
		    log_deposit_withdraw.type,
		    log_deposit_withdraw.promotion_name,
		    log_deposit_withdraw.description,
		    log_deposit_withdraw.created_at
         ');
		if (isset($search['id'])) {
			$this->db->where('log_deposit_withdraw.id', $search['id']);
		}
		$query = $this->db->get('log_deposit_withdraw');
		return $query->row_array();
	}

	public function log_deposit_withdraw_count($search = [])
	{
		$this->db->select('
         count(1) as cnt_row
         ',false);
		if (isset($search['search'])) {
			$this->db->like('account.username', $search['search']);
			$this->db->or_like('log_deposit_withdraw.description', $search['search']);
			if(strpos($search['search'],"ฝาก") !== false){
				$this->db->or_like('log_deposit_withdraw.type', 1);
			}else if(strpos($search['search'],"ถอน") !== false){
				$this->db->or_like('log_deposit_withdraw.type', 2);
			}
		}
		$this->db->join('account', 'account.id = log_deposit_withdraw.account','left');
		$query = $this->db->get('log_deposit_withdraw');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}
}
