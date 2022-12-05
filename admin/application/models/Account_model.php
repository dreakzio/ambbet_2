<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Account_model extends CI_Model
{
    public function account_find($search = [])
    {
        $this->db->select('
        account.id,
        account.username,
        account.bank,
        account.bank_number,
        account.bank_name,
        account.full_name,
        account.role,
        account.turn_date,
        account.turn_before,
         account.turn_before_football,
         account.turn_before_step,
         account.turn_before_parlay,
         account.turn_before_game,
         account.turn_before_casino,
         account.turn_before_lotto,
         account.turn_before_m2,
         account.turn_before_multi_player,
         account.turn_before_trading,
         account.turn_before_keno,
         account.turn_over,
         account.turn_over_football,
         account.turn_over_step,
         account.turn_over_parlay,
         account.turn_over_game,
         account.turn_over_casino,
         account.turn_over_lotto,
         account.turn_over_m2,
         account.turn_over_multi_player,
         account.turn_over_trading,
         account.turn_over_keno,
        account.password,
        account.deleted,
        account.point_for_wheel,
        account.login_point,
        account.point_for_return_balance,
        account.agent,
        account.commission_percent,
        account.ref_transaction_id,
        account.amount_deposit_auto,
        account.amount_wallet as amount_wallet,
        account.amount_wallet_ref,
        account.rank,
        account.remark,
        account.sum_amount,
        account.rank_point_sum
        ');
        if (isset($search['id'])) {
            $this->db->where('account.id', $search['id']);
        }
        if (isset($search['username'])) {
            $this->db->where('account.username', $search['username']);
        }
        if (isset($search['password'])) {
            $this->db->where('account.password', md5($search['password']));
        }
        if (isset($search['bank'])) {
            $this->db->where('account.bank', $search['bank']);
        }
        if (isset($search['bank_number'])) {
            $this->db->where('account.bank_number', $search['bank_number']);
        }
		if (isset($search['account_agent_username'])) {
			$this->db->where('account_agent.username', $search['account_agent_username']);
			$this->db->join('account_agent', 'account_agent.account_id = account.id','left');
		}
		$this->db->limit(1);
        $query = $this->db->get('account');
		$user = $query->row_array();
		if($user!=""){
			$user_account_agents = $this->getAccountAgentByAccountIdIn([$user['id']]);
			if(array_key_exists($user['id'],$user_account_agents)){
				$user['account_agent_username'] = $user_account_agents[$user['id']]['account_agent_username'];
				$user['account_agent_password'] = $user_account_agents[$user['id']]['account_agent_password'];
				$user['account_agent_id'] = $user_account_agents[$user['id']]['id'];
				$user['accid'] = $user_account_agents[$user['id']]['accid'];
			}else{
				$user['account_agent_username'] = null;
				$user['account_agent_password'] = null;
				$user['account_agent_id'] = null;
				$user['accid'] = null;
			}
			$this->cacheModel($user);
		}
		return $user;
    }

	private function cacheModel($data){
		$cache_data = $this->cache->file->get(base64_encode(get_class($this)."_".date("Y_m_d")));
		if($cache_data !== FALSE && !is_null($data)){
			$cache_data[$data['id']] = $data;
			$this->cache->file->save(base64_encode(get_class($this)."_".date("Y_m_d")),$cache_data, 31556926); // 1 year
		}else if(!is_null($data)){
			$cache_data = [
				$data['id'] => $data
			];
			$this->cache->file->save(base64_encode(get_class($this)."_".date("Y_m_d")),$cache_data, 31556926); // 1 year
		}
	}

	public function account_find_chk_fast($search = [])
	{
		$this->db->select('
        account.id,
        account.username,
        account.role,
        account.agent,
        account.deleted,
        account.bank,
        account.bank_number,
        account.bank_name,
        account.full_name,
        account.amount_deposit_auto,
        account.login_point,
        account.point_for_return_balance,
        account.amount_wallet as amount_wallet,
        account.amount_wallet_ref,
        account.rank,
        account.remark,
        account.sum_amount,
        account.rank_point_sum
        ');
		if (isset($search['id'])) {
			$this->db->where('account.id', $search['id']);
		}
		if (isset($search['username'])) {
			$this->db->where('account.username', $search['username']);
		}
		if (isset($search['password'])) {
			$this->db->where('account.password', md5($search['password']));
		}
		if (isset($search['bank'])) {
			$this->db->where('account.bank', $search['bank']);
		}
		if (isset($search['full_name'])) {
			$this->db->where('account.full_name', $search['full_name']);
		}
		if (isset($search['bank_number'])) {
			$this->db->where('account.bank_number', $search['bank_number']);
		}
		if (isset($search['linebot_userid'])) {
			$this->db->where('account.linebot_userid', $search['linebot_userid']);
		}
		if (isset($search['id_ne'])) {
			$this->db->where('account.id <>', $search['id_ne']);
		}
		if(isset($search['deleted_ignore']) && $search['deleted_ignore']){

		}else{
			$this->db->where('account.deleted', 0);
		}
		$this->db->limit(1);
		$query = $this->db->get('account');
		return $query->row_array();
	}

    public function account_create($data = [])
    {
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
        $this->db->insert('account', $data);
        $id = $this->db->insert_id();
		$this->account_find(['id'=>$id]);
        return $id;
    }
    public function account_update($data)
    {
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['updated_at'] = isset($data['updated_at']) ? $data['updated_at'] : date('Y-m-d H:i:s');
        $this->db->where('id', $data['id']);
        $this->db->update('account', $data);
		$this->account_find(['id'=>$data['id']]);
    }

	public function account_max_id()
	{
		$this->db->select_max('id');
		$query = $this->db->get('account');
		$r=$query->row_array();
		return $r;
	}

	public function account_report_all_day($search = []){
		$this->db->select('
			count(1) as sum_account
        ');
		if (isset($search['created_at'])) {
			$this->db->like('account.created_at', $search['created_at']);
		}
		$query = $this->db->get('account');
		return $query->result_array();
	}

	public function account_find_all($id)
	{
		$this->db->select('
        *
        ');
		$this->db->where('account.id', $id);
		$query = $this->db->get('account');
		return $query->row_array();
	}

	private function getAccountAgentByAccountIdIn($user_id_list = []){
		$this->db->select('
			id,
			account_id,
			accid,
			username as account_agent_username,
			password as account_agent_password
        ');
		$this->db->where_in('account_id', $user_id_list );
		$query = $this->db->get('account_agent');
		$results = $query->result_array();
		$data=[];
		foreach($results as $result){
			$data[$result['account_id']] = $result;
		}
		return $data;
	}
}
