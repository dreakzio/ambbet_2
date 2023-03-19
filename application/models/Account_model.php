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
        account.full_name,
        account.password,
        account.ref_transaction_id,
        account.agent,
        account.deleted,
        account.point_for_wheel,
        account.commission_percent,
        account.is_active_return_balance,
        account.login_process_job_date,
        account.login_point,
        account.point_for_return_balance,
        account.amount_deposit_auto,
        account.amount_wallet as amount_wallet,
        account.amount_wallet_ref,
        account.auto_accept_bonus,
        account.rank,
        account.is_auto_withdraw,
        account.phone
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
		if (isset($search['linebot_userid'])) {
			$this->db->where('account.linebot_userid', $search['linebot_userid']);
		}
		if (isset($search['account_agent_username'])) {
			$this->db->where('account_agent.username', $search['account_agent_username']);
			$this->db->join('account_agent', 'account_agent.account_id = account.id','left');
		}
		if(isset($search['deleted_ignore']) && $search['deleted_ignore']){

		}else{
			$this->db->where('account.deleted', 0);
		}
		if (isset($search['amount_deposit_auto'])) {
			$this->db->where('account.amount_deposit_auto >', $search['amount_deposit_auto']);
		}
        //$this->db->join('account_agent', 'account_agent.account_id = account.id','left');
		$this->db->limit(1);
		$query = $this->db->get('account');
		$user = $query->row_array();
		if($user!=""){
			$user_account_agents = $this->getAccountAgentByAccountIdIn([$user['id']]);
			if(array_key_exists($user['id'],$user_account_agents)){
				$user['account_agent_username'] = $user_account_agents[$user['id']]['account_agent_username'];
				$user['account_agent_password'] = $user_account_agents[$user['id']]['account_agent_password'];
				$user['account_agent_id'] = $user_account_agents[$user['id']]['account_id'];
				$user['accid'] = $user_account_agents[$user['id']]['accid'];
			}else{
				$user['account_agent_username'] = null;
				$user['account_agent_password'] = null;
				$user['account_agent_id'] = null;
				$user['accid'] = null;
			}
		}
		return $user;
    }
    public function account_create($data = [])
    {
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
        $this->db->insert('account', $data);
        $id = $this->db->insert_id();
        return $id;
    }
    public function account_update($data)
    {
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['updated_at'] = isset($data['updated_at']) ? $data['updated_at'] : date('Y-m-d H:i:s');
        $this->db->where('id', $data['id']);
        $this->db->update('account', $data);
		//print_r($this->db->last_query());
	}

	public function account_max_id()
	{
		$this->db->select_max('id');
		$query = $this->db->get('account');
		$r=$query->row_array();
		return $r;
	}

	public function account_list_for_process_return_balance($search = [])
	{
		$this->db->select('
			account.id,
			account.username,
			account.agent,
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
			account.turn_date,
			account.login_point,
            account.point_for_return_balance,
			account.is_active_return_balance,
			account.rank,
			account.rank_point_sum,
			account.ref_transaction_id,
			account.is_auto_withdraw
        ');
		if (isset($search['limit'])) {
			$this->db->limit($search['limit']);
		}
		$this->db->where('agent', 0);
		$this->db->where('deleted', 0);
		$this->db->where('is_active_return_balance', 1);
		if (isset($search['process_return_balance_job_date'])) {
			$this->db->group_start();
			$this->db->where("process_return_balance_job_date <",$search['process_return_balance_job_date'])
				->or_where('process_return_balance_job_date', null);
			$this->db->group_end();
		}else{
			$this->db->where('process_return_balance_job_date', null);
		}
		$this->db->where('created_at <',date('Y-m-d')." 00:00:00");
		//$this->db->join('account_agent', 'account_agent.account_id = account.id','left');
		$this->db->order_by('rand()');
		$query = $this->db->get('account');
		$results = $query->result_array();
		$user_id_list = [];
		foreach($results as $user){
			$user_id_list[] = $user['id'];
		}
		$user_account_agents = count($user_id_list) == 0 ? $user_id_list :  $this->getAccountAgentByAccountIdIn($user_id_list);
		foreach($results as $index => $result){
			if(array_key_exists($result['id'],$user_account_agents)){
				$results[$index]['account_agent_username'] = $user_account_agents[$result['id']]['account_agent_username'];
				$results[$index]['account_agent_password'] = $user_account_agents[$result['id']]['account_agent_password'];
			}else{
				$results[$index]['account_agent_username'] = null;
				$results[$index]['account_agent_password'] = null;
			}
		}

		return $results;
	}

	public function account_list_for_deposit_promotion($search = [])
	{
		$this->db->select('
			account.id,
			account.username,
			account.bank,
			account.bank_number,
			account.bank_name,
			account.full_name,
			account.agent,
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
			account.turn_date,
			account.login_point,
            account.point_for_return_balance,
			account.is_active_return_balance,
			account.rank,
			account.amount_deposit_auto,
			account.rank_point_sum,
			account.ref_transaction_id,
			 account.is_auto_withdraw
        ');
		if (isset($search['limit'])) {
			$this->db->limit($search['limit']);
		}
		$this->db->where('deleted', 0);
		$this->db->where('is_active_return_balance', 1);
		$this->db->where('auto_accept_bonus', 1);
		$this->db->where('amount_deposit_auto >', 0);

		$this->db->order_by('id asc');
		$query = $this->db->get('account');
		$results = $query->result_array();
		$user_id_list = [];
		foreach($results as $user){
			$user_id_list[] = $user['id'];
		}

		$user_account_agents = count($user_id_list) == 0 ? $user_id_list :  $this->getAccountAgentByAccountIdIn($user_id_list);
		foreach($results as $index => $result){
			if(array_key_exists($result['id'],$user_account_agents)){
				$results[$index]['account_agent_username'] = $user_account_agents[$result['id']]['account_agent_username'];
				$results[$index]['account_agent_password'] = $user_account_agents[$result['id']]['account_agent_password'];
			}else{
				$results[$index]['account_agent_username'] = null;
				$results[$index]['account_agent_password'] = null;
			}
		}

		return $results;
	}
    public function amount_promptpay_find($data = [])
    {
    	$this->db->select('
    		count(1) as cnt_row
    	');
        $this->db->where('amount_promptpay =', $data['amount_promptpay']);
        $query = $this->db->get('account');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
    }
    public function amount_promptpay_update($data)
    {
        date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['promptpay_time'] = isset($data['promptpay_time']) ? $data['promptpay_time'] : date('Y-m-d H:i:s');
        $this->db->where('username =', $data['username']);
        $this->db->update('account', $data);
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

	public function account_find_chk_fast($search = [])
	{
		$this->db->select('
        account.id,
        account.username,
        account.is_active_return_balance,
        account.full_name,
        account.role,
        account.agent,
        account.amount_deposit_auto,
        account.login_point,
        account.point_for_return_balance,
        account.amount_wallet as amount_wallet,
        account.amount_wallet_ref,
        account.ref_transaction_id,
        account.rank,
        account.is_auto_withdraw
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
		if(isset($search['deleted_ignore']) && $search['deleted_ignore']){

		}else{
			$this->db->where('account.deleted', 0);
		}
		$this->db->limit(1);
		$query = $this->db->get('account');
		return $query->row_array();
	}

	public function account_agent_find_by_account_id($account_id){
		$this->db->select('
			id,
			account_id,
			accid,
			username as account_agent_username,
			password as account_agent_password
        ');
		$this->db->where('account_id', $account_id );
		$query = $this->db->get('account_agent');
		return $query->row_array();
	}

	public function user_list_for_process_wheel($search = [])
	{
		$this->db->select('
			account.id,
			account.username,
			account.agent,
			account.point_for_wheel,
			account.wheel_process_job_date
        ');
		if (isset($search['limit'])) {
			$this->db->limit($search['limit']);
		}
		$this->db->where('account.agent', 0);
		$this->db->where('account.deleted', 0);
		if (isset($search['wheel_process_job_date'])) {
			$this->db->group_start();
			$this->db->where("account.wheel_process_job_date <",$search['wheel_process_job_date'])
				->or_where('account.wheel_process_job_date', null);
			$this->db->group_end();
		}else{
			$this->db->where('account.wheel_process_job_date', null);
		}
		$this->db->where('account.created_at <',date('Y-m-d')." 00:00:00");
		$this->db->order_by('rand()');
		//$this->db->join('account_agent', 'account_agent.account_id = account.id','left');
		$query = $this->db->get('account');
		$results = $query->result_array();
		$user_id_list = [];
		foreach($results as $user){
			$user_id_list[] = $user['id'];
		}
		$user_account_agents = count($user_id_list) == 0 ? $user_id_list :  $this->getAccountAgentByAccountIdIn($user_id_list);
		foreach($results as $index => $result){
			if(array_key_exists($result['id'],$user_account_agents)){
				$results[$index]['account_agent_username'] = $user_account_agents[$result['id']]['account_agent_username'];
				$results[$index]['account_agent_password'] = $user_account_agents[$result['id']]['account_agent_password'];
				$results[$index]['account_agent_id'] = $user_account_agents[$result['id']]['account_id'];
				$results[$index]['accid'] = $user_account_agents[$result['id']]['accid'];
			}else{
				$results[$index]['account_agent_username'] = null;
				$results[$index]['account_agent_password'] = null;
				$results[$index]['account_agent_id'] = null;
				$results[$index]['accid'] = null;
			}
		}

		return $results;
	}

	public function account_linebot_userid_update($data)
	{
		if(isset($data['linebot_userid']) && !empty($data['linebot_userid'])){
			date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
			$data['updated_at'] = isset($data['updated_at']) ? $data['updated_at'] : date('Y-m-d H:i:s');
			$this->db->where('linebot_userid', $data['linebot_userid']);
			$this->db->where('linebot_userid <>', '');
			$this->db->where('linebot_userid IS NOT NULL');
			if(isset($data['linebot_userid_empty'])){
				$data['linebot_userid'] = '';
				unset($data['linebot_userid_empty']);
			}
			$this->db->update('account', $data);
		}
	}
	public function account_linebot_userid_count($data = [])
	{
		$this->db->select('
    		count(1) as cnt_row
    	');
		$this->db->where('linebot_userid =', $data['linebot_userid']);
		$query = $this->db->get('account');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}
	public function  get_total_online_user()
	{
		$this->db->select('
    		count(1) as total
    	');
		$datetime = date("Y-m-d H:i:s", strtotime('- 20 minutes'));
		$this->db->where('last_activity >=', $datetime);
		$query = $this->db->get('account');
		$total_online =  $query->row_array();
		return $total_online != "" && isset($total_online['total']) && is_numeric($total_online['total']) ? (int)$total_online['total'] : 0;
	}
	public function get_account_by_account_agent_username($username){
		$data = null;

		$this->db->select('
    		account_agent.*
    	');
		$this->db->where('BINARY LOWER(account_agent.username) =', strtolower($username));
		$query = $this->db->get('account_agent');
		$account_agent =  $query->row_array();
		if($account_agent != ""){
			$this->db->select('
    			account.*
    		');
			$this->db->where('account.id', $account_agent['account_id']);
			$this->db->where('account.deleted', 0);
			$query = $this->db->get('account');
			$account =  $query->row_array();
			if($account != ""){
				$account['account_agent_username'] = $account_agent['username'];
				$account['account_agent_password'] = $account_agent['password'];
				$data = $account;
			}
		}
		return $data;
	}
}
