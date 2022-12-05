<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Wallet_ref_deposit_model extends CI_Model
{
    public function wallet_ref_deposit_create($data = [])
    {
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
        $this->db->insert('wallet_ref_deposit', $data);
        $id = $this->db->insert_id();
        return $id;
    }

	public function wallet_ref_deposit_sum($search = [])
	{
		$this->db->select('
        	SUM(amount_wallet_ref) as amount_wallet_ref
        ');
		if (isset($search['id'])) {
			$this->db->where('wallet_ref_deposit.id', $search['id']);
		}
		if (isset($search['account'])) {
			$this->db->where('wallet_ref_deposit.account', $search['account']);
		}
		$query = $this->db->get('wallet_ref_deposit');
		return $query->row_array();
	}
}
