<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Transaction_model extends CI_Model
{
    public function transaction_sum_amount($search)
    {
        $this->db->select('
           sum(transaction.amount) as sum_amount
         ');
        if (isset($search['account'])) {
            $this->db->where('transaction.account', $search['account']);
        }
        $query = $this->db->get('transaction');
        return $query->result_array();
    }

    public function transaction_find($search = [])
  	{
  		$this->db->select('
          *
          ');
  		if (isset($search['id'])) {
  			$this->db->where('transaction.id', $search['id']);
  		}
  		if (isset($search['transref'])) {
  			$this->db->where('transaction.transref', $search['transref']);
  		}
  		$query = $this->db->get('transaction');
  		return $query->row_array();
  	}
  	public function transaction_create($data = [])
  	{
  		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
  		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
  		$this->db->insert('transaction', $data);
  		$id = $this->db->insert_id();
  		return $id;
  	}
}
