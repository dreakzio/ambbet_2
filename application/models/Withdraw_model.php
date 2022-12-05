<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Withdraw_model extends CI_Model
{
    public function withdraw_find($search = [])
    {
        $this->db->select('
        withdraw.id,
        withdraw.amount,
        withdraw.bank,
        withdraw.bank_number
        ');
        if (isset($search['id'])) {
            $this->db->where('withdraw.id', $search['id']);
        }
        if (isset($search['account'])) {
            $this->db->where('withdraw.account', $search['account']);
        }
        $this->db->join('account', 'account.id = withdraw.account');
        $query = $this->db->get('withdraw');
        return $query->row_array();
    }
    public function withdraw_create($data = [])
    {
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
        $this->db->insert('withdraw', $data);
        $id = $this->db->insert_id();
        return $id;
    }
    public function withdraw_update($data)
    {
        $this->db->where('id', $data['id']);
        $this->db->update('withdraw', $data);
    }
    public function withdraw_limit($data)
    {
        date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d');
        $this->db->select('
        COUNT(id) as per_day,
        SUM(amount) as max_limit
        ');
        $this->db->where('finance.account', $data['id']);
		$this->db->where('finance.type', 2);
		$this->db->where_in('finance.status', [0,1,3,4]);
        $this->db->like('finance.created_at', $data['created_at']);
        $query = $this->db->get('finance');
		return $query->result_array();
    }
}
