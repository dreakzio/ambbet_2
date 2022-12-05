<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Wallet_deposit_model extends CI_Model
{
    // public function withdraw_find($search = [])
    // {
    //     $this->db->select('
    //     withdraw.id,
    //     withdraw.amount,
    //     withdraw.bank,
    //     withdraw.bank_number
    //     ');
    //     if (isset($search['id'])) {
    //         $this->db->where('withdraw.id', $search['id']);
    //     }
    //     if (isset($search['account'])) {
    //         $this->db->where('withdraw.account', $search['account']);
    //     }
    //     $this->db->join('account', 'account.id = withdraw.account');
    //     $query = $this->db->get('withdraw');
    //     return $query->row_array();
    // }
    public function wallet_deposit_create($data = [])
    {
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
        $this->db->insert('wallet_deposit', $data);
        $id = $this->db->insert_id();
        return $id;
    }
}
