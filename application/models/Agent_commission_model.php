<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Agent_commission_model extends CI_Model
{
    public function agent_commission_find($search = [])
    {
        $this->db->select('
        agent_commission.id,
        agent_commission.month,
        agent_commission.year,
        agent_commission.withdraw,
        agent_commission.deposit,
        agent_commission.percent
        ');
        if (isset($search['account'])) {
            $this->db->where('agent_commission.account', $search['account']);
        }
        if (isset($search['month'])) {
            $this->db->where('agent_commission.month', $search['month']);
        }
        if (isset($search['year'])) {
            $this->db->where('agent_commission.year', $search['year']);
        }
        $query = $this->db->get('agent_commission');
        return $query->row_array();
    }
    public function agent_commission_create($data)
    {
        $this->db->insert('agent_commission', $data);
        $id = $this->db->insert_id();
        return $id;
    }
    public function finance_list($search)
    {
        $this->db->select('
        DATE_FORMAT(finance.created_at, "%Y-%m") as date
        ');
        $this->db->where('ref.agent', 1);
        if (isset($search['account'])) {
            $this->db->where('ref.from_account', $search['account']);
        }
        $this->db->order_by('finance.id', 'ASC');
        $this->db->group_by('MONTH(finance.created_at), YEAR(finance.created_at)');
        $this->db->join('ref', 'ref.to_account = finance.account');
        $this->db->join('account', 'account.id = ref.from_account');
        $query = $this->db->get('finance');
        return $query->result_array();
    }
}
