<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ref_model extends CI_Model
{
    public function ref_create($data = [])
    {
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
        $this->db->insert('ref', $data);
        $id = $this->db->insert_id();
        return $id;
    }
    public function ref_find($search = [])
    {
        $this->db->select('
        ref.id,
        from_account.username as from_account_username,
        from_account.id as from_account_id,
        from_account.turn_date as from_account_turn_date,
        from_account.turn_before as from_account_turn_before,
        from_account.turn_over as from_account_turn_over,
        from_account.amount_wallet_ref as from_account_amount_wallet_ref,
        from_account.agent,
        account_agent.username as member_username,
        account_agent.password as member_password,

        ');
        if (isset($search['to_account'])) {
            $this->db->where('to_account', $search['to_account']);
        }
        $this->db->join('account as from_account', 'from_account.id = ref.from_account');
        $this->db->join('account_agent', 'account_agent.account_id = from_account.id');
        $query = $this->db->get('ref');
        return $query->row_array();
    }
    public function ref_deposit_create($data = [])
    {
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
        $this->db->insert('ref_deposit', $data);
        $id = $this->db->insert_id();
        return $id;
    }
	public function ref_update($data)
	{
		$this->db->where('id', $data['id']);
		$this->db->update('ref', $data);
	}
	public function ref_deposit_update($data)
	{
		$this->db->where('id', $data['id']);
		$this->db->update('ref_deposit', $data);
	}
    public function ref_list($search = [])
    {
        $this->db->select('
        ref.id,
          ref.to_account_username,
        ref.created_at,
         account_agent.username as account_agent_username
        ',false);
        if (isset($search['from_account'])) {
            $this->db->where('from_account', $search['from_account']);
        }
        if (isset($search['to_account'])) {
            $this->db->where('to_account', $search['to_account']);
        }
		$this->db->limit($search['per_page'], $search['page']);
        $this->db->order_by('ref.id', 'DESC');
        //$this->db->join('account as to_account', 'to_account.id = ref.to_account');
		$this->db->join('account_agent as account_agent','ref.to_account = account_agent.account_id','left');
        $query = $this->db->get('ref');
        return $query->result_array();
    }
	public function ref_list_test($search = [])
	{
		$this->db->select('
        ref.id,
        ref.from_account,
        ref.to_account
        ');
		$this->db->where('from_account_username IS NULL');
		$this->db->where('to_account_username IS NULL');
		$this->db->limit($search['per_page'], $search['page']);
		$this->db->order_by('ref.id', 'DESC');
		$query = $this->db->get('ref');
		return $query->result_array();
	}
	public function ref_list_test2($search = [])
	{
		$this->db->select('
        ref_deposit.id,
        ref_deposit.account
        ');
		$this->db->where('username_to IS NULL');
		$this->db->limit($search['per_page'], $search['page']);
		$this->db->order_by('ref_deposit.id', 'DESC');
		$query = $this->db->get('ref_deposit');
		return $query->result_array();
	}
    public function ref_count($search = [])
    {
		$this->db->select('
         count(1) as cnt_row
         ');
		if (isset($search['from_account'])) {
			$this->db->where('from_account', $search['from_account']);
		}
		if (isset($search['to_account'])) {
			$this->db->where('to_account', $search['to_account']);
		}
		$query = $this->db->get('ref');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
    }
    public function ref_deposit_list($search = [])
    {
        $this->db->select('
         ref_deposit.id,
         ref_deposit.percent,
         ref_deposit.sum_amount,
         ref_deposit.created_at,
         ref_deposit.username_from as username
         ');
         if (isset($search['username_to'])) {
             $this->db->where('ref_deposit.username_to', $search['username_to']);
         }
		if (isset($search['from_account'])) {
			$this->db->where('ref_deposit.account', $search['from_account']);
		}
		if (isset($search['type_list']) && is_array($search['type_list']) && count($search['type_list']) > 0) {
			$this->db->where_in('ref_deposit.type', $search['type_list']);
		}
		$this->db->limit($search['per_page'], $search['page']);
        //$this->db->join('finance', 'finance.id = ref_deposit.finance');
        //$this->db->join('account', 'account.id = ref_deposit.account');
        //$this->db->join('ref', 'ref.to_account = account.id');
        $this->db->order_by('ref_deposit.id', 'DESC');
        $query = $this->db->get('ref_deposit');
        return $query->result_array();
    }

	public function ref_list_for_process_commission($search = [])
	{
		$this->db->select('
			ref.id,
			ref.from_account,
			ref.to_account,
			ref.agent,
			ref.ref_process_job_date
        ');
		if (isset($search['from_account'])) {
			$this->db->where('from_account', $search['from_account']);
		}
		if (isset($search['to_account'])) {
			$this->db->where('to_account', $search['to_account']);
		}
		if (isset($search['limit'])) {
			$this->db->limit($search['limit']);
		}
		$this->db->where('agent', 0);
		if (isset($search['ref_process_job_date'])) {
			$this->db->group_start();
			$this->db->where("ref_process_job_date <",$search['ref_process_job_date'])
				->or_where('ref_process_job_date', null);
			$this->db->group_end();
		}else{
			$this->db->where('ref_process_job_date', null);
		}
		$this->db->order_by('rand()');
		//$this->db->order_by('ref.from_account', 'DESC');
		$query = $this->db->get('ref');
		return $query->result_array();
	}

	public function ref_no_join_no_paginate_commission_list($search = [])
	{
		$this->db->select('
        	*
        ',false);
		if (isset($search['from_account'])) {
			$this->db->where('from_account', $search['from_account']);
		}
		if (isset($search['to_account'])) {
			$this->db->where('to_account', $search['to_account']);
		}
		$this->db->where('agent', 0);
		$query = $this->db->get('ref');
		return $query->result_array();
	}

	public function ref_deposit_list_page($search)
	{
		$this->db->select('
         ref_deposit.id,
         ref_deposit.percent,
         ref_deposit.turn,
         ref_deposit.sum_amount,
         ref_deposit.created_at,
         ref_deposit.turnover_amount as wl_amount
         ',false);
		$this->db->limit($search['per_page'], $search['page']);
		if (isset($search['type_list']) && is_array($search['type_list']) && count($search['type_list']) > 0) {
			$this->db->where_in('ref_deposit.type', $search['type_list']);
		}
		$this->db->where_in('ref_deposit.account', $search['account']);
		$this->db->order_by('ref_deposit.id', 'DESC');
		$query = $this->db->get('ref_deposit');
		return $query->result_array();
	}
}
