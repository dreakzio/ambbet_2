<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Deposit_model extends CI_Model
{
    public function deposit_find($search = [])
    {
        $this->db->select('
        deposit.id,
        deposit.amount,
        deposit.promotion,
        deposit.promotion_name,
        deposit.percent,
        deposit.turn,
        deposit.bank,
        deposit.bank_number,
        deposit.max_value,
        deposit.sum_amount,
        deposit.created_at,
        account.username,
        account.full_name,
        account.line_id,
        account_agent.username as account_agent_username,
        account_agent.password as account_agent_password

       ');
        if (isset($search['id'])) {
            $this->db->where('deposit.id', $search['id']);
        }
        if (isset($search['account'])) {
            $this->db->where('deposit.account', $search['account']);
        }
        $this->db->join('account', 'account.id = deposit.account');
        $this->db->join('account_agent', 'account_agent.account_id = account.id');
        $query = $this->db->get('deposit');
        return $query->row_array();
    }
    public function deposit_create($data = [])
    {
        $this->db->insert('deposit', $data);
        $id = $this->db->insert_id();
        return $id;
    }
    public function deposit_update($data)
    {
        $this->db->where('id', $data['id']);
        $this->db->update('deposit', $data);
    }
    public function deposit_list_page($search)
    {
        $this->db->select('
        deposit.id,
        deposit.amount,
        deposit.promotion,
        deposit.promotion_name,
        deposit.percent,
        deposit.turn,
        deposit.bank,
        deposit.bank_number,
        deposit.max_value,
        deposit.sum_amount,
        deposit.created_at,
        account.username,
        account_agent.username as account_agent_username,
       ');
        $this->db->order_by('deposit.id', 'DESC');
        if (isset($search['id'])) {
            $this->db->where('deposit.id', $search['id']);
        }
        if (isset($search['account'])) {
            $this->db->where('deposit.account', $search['account']);
        }
        $this->db->join('account', 'account.id = deposit.account');
        $this->db->join('account_agent', 'account_agent.account_id = account.id');

        $query = $this->db->get('deposit');
        return $query->result_array();
    }
    public function deposit_count($search = [])
    {
        $this->db->select('
         count(1) as cnt_row
         ');
		if (isset($search['search']) && !empty(trim($search['search']))) {
            $this->db->like('deposit.amount', $search['search']);
            $this->db->or_like('deposit.sum_amount', $search['search']);
        }
        $this->db->where('account.deleted', 0);
        $this->db->join('account', 'account.id = deposit.account');
        $query = $this->db->get('deposit');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
    }
}
