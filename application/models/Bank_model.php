<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Bank_model extends CI_Model
{
    public function bank_data_list()
    {
        $this->db->select('
          *
        ');
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get('banks');
        return $query->result_array();
    }
    public function bank_create($data = [])
    {
        $this->db->insert('bank', $data);
        $id = $this->db->insert_id();
        return $id;
    }
    public function bank_update($data)
    {
        $this->db->where('id', $data['id']);
        $this->db->update('bank', $data);
    }
    public function bank_list($search = [])
    {
        $this->db->select('
          id,
		  bank_code,
		  bank_name,
		  account_name,
		  bank_number,
		  promptpay_number,
		  promptpay_status,
		  status,
		  deleted,
		  balance,
		  status_withdraw,
		  start_time_can_not_deposit,
		  end_time_can_not_deposit,
		  message_can_not_deposit,
		  max_amount_withdraw_auto,
		  updated_at,
		  error_message
        ');
        $this->db->order_by('id', 'DESC');
        $this->db->where('deleted', 0);
		if (isset($search['id'])) {
			$this->db->where('bank.id', $search['id']);
		}
		if (isset($search['status'])) {
			$this->db->where('status', $search['status']);
		}
		if (isset($search['status_withdraw'])) {
			$this->db->where('status_withdraw', $search['status_withdraw']);
		}
		if(isset($search['bank_code_list_not_in']) && count($search['bank_code_list_not_in']) > 0){
			$this->db->where_not_in('bank_code', $search['bank_code_list_not_in']);
		}else if (isset($search['bank_code_list']) && count($search['bank_code_list']) > 0) {
			$this->db->where_in('bank_code', $search['bank_code_list']);
		}
        $query = $this->db->get('bank');
        return $query->result_array();
    }
    public function bank_find($search = [])
    {
        $this->db->select('
          id,
		  bank_code,
		  bank_name,
		  account_name,
		  bank_number,
		  promptpay_number,
		  promptpay_status,
		  status,
		  deleted,
		  balance,
		  status_withdraw,
		  start_time_can_not_deposit,
		  end_time_can_not_deposit,
		  message_can_not_deposit,
		  max_amount_withdraw_auto,
		  updated_at,
		  error_message
         ');
        if (isset($search['id'])) {
            $this->db->where('bank.id', $search['id']);
        }
		if (isset($search['bank_number'])) {
            $this->db->where('bank_number', $search['bank_number']);
        }
		if (isset($search['status'])) {
			$this->db->where('status', $search['status']);
		}
		if (isset($search['status_withdraw'])) {
			$this->db->where('status_withdraw', $search['status_withdraw']);
		}
		if(isset($search['bank_code_list_not_in']) && count($search['bank_code_list_not_in']) > 0){
			$this->db->where_not_in('bank_code', $search['bank_code_list_not_in']);
		}else if (isset($search['bank_code_list']) && count($search['bank_code_list']) > 0) {
			$this->db->where_in('bank_code', $search['bank_code_list']);
		}
		$this->db->order_by('id', 'RANDOM');
        $this->db->where('promptpay_number', '');
		$this->db->where('deleted', 0);
        $query = $this->db->get('bank');
        return $query->row_array();
    }
    public function promptpay()
    {
        $this->db->select('*');
        $where = "`bank_code` = '05' AND (`promptpay_number` IS NOT null OR `promptpay_number` != '') AND `promptpay_status` = 1 AND `deleted` = 0";
		$this->db->where($where);
		$this->db->limit(1);
		$query = $this->db->get('bank');
		return $query->row_array();
    }
}
