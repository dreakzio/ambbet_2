<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Transaction_model extends CI_Model
{
	public function transaction_find($search = [])
	{
		$this->db->select('
         transaction.id,
         transaction.amount,
         transaction.type,
         transaction.date_bank
         ');
		if (isset($search['id'])) {
			$this->db->where('transaction.id', $search['id']);
		}
		if (isset($search['date_bank'])) {
			$this->db->where('DATE_FORMAT(transaction.date_bank,"%Y-%m-%d %H:%i:%s") =', $search['date_bank']);
		}
		if (isset($search['bank_number'])) {
			$this->db->where('transaction.bank_number', $search['bank_number']);
		}
		if (isset($search['account'])) {
			$this->db->where('transaction.account', $search['account']);
		}
		if (isset($search['type'])) {
			$this->db->where('transaction.type', $search['type']);
		}
		if (isset($search['amount'])) {
			$this->db->where('transaction.amount', $search['amount']);
		}
		$this->db->limit(1);
		//$this->db->join('account', 'account.id = transaction.account');
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

	public function transaction_list_page($search)
	{
		$this->db->select('
         transaction.id,
         transaction.amount,
         transaction.type,
         transaction.date_bank
         ');
		if (isset($search['id'])) {
			$this->db->where('transaction.id', $search['id']);
		}
		if (isset($search['date_bank'])) {
			$this->db->where('DATE_FORMAT(transaction.date_bank,"%Y-%m-%d %H:%i") =', $search['date_bank']);
		}

		$this->db->limit($search['per_page'], $search['page']);
		if (isset($search['search']) && !empty(trim($search['search']))) {
			// $this->db->like('account.transactionname', $search['search']);
			// $this->db->or_like('account.line_id', $search['search']);
			// $this->db->or_like('account.full_name', $search['search']);
		}
		//$this->db->join('account', 'account.id = transaction.account');
		$this->db->order_by('transaction.id', 'DESC');
		$query = $this->db->get('transaction');
		return $query->result_array();
	}
	public function transaction_count($search = [])
	{
		$this->db->select('
          count(1) as cnt_row
         ');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			// $this->db->like('account.transactionname', $search['search']);
			// $this->db->or_like('account.line_id', $search['search']);
			// $this->db->or_like('account.full_name', $search['search']);
		}
		$this->db->where('account.deleted', 0);
		//$this->db->join('account', 'account.id = transaction.account');
		$query = $this->db->get('transaction');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}
}
