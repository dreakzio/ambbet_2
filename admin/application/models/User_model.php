<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User_model extends CI_Model
{
	public function user_find($search = [])
	{
		$this->db->select('
         account.id,
         account.username,
         account.agent,
         account.line_id,
         account.bank,
         account.bank_number,
         account.bank_name,
         account.amount_wallet,
         account.full_name,
         account.phone,
         account.point_for_wheel,
        account.login_point,
        account.point_for_return_balance,
         account.role,
         account.amount_deposit_auto,
         account.commission_percent,
         account.is_active_return_balance,
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
         account.rank,
         account.remark,
         account.sum_amount,
         account.rank_point_sum,
         DATE_FORMAT( account.created_at, "%Y-%m-%d") as created_at,
         account.ref_transaction_id,
         account.is_auto_withdraw
       ',false);
		if (isset($search['id']) && !is_null($search['id'])) {
			$this->db->where('account.id', $search['id']);
		}else{
			return "";
		}
		if (isset($search['username'])) {
			$this->db->where('account.username', $search['username']);
		}
		//$this->db->join('account_agent', 'account_agent.account_id = account.id','left');
		//$this->db->join('ref', 'ref.to_account = account.id','left');
		$this->db->limit(1);
		$query = $this->db->get('account');
		$user = $query->row_array();
		if($user!=""){
			$user_account_agents = $this->getAccountAgentByAccountIdIn([$user['id']]);
			$user_ref = $this->getRefByAccountId($user['id']);
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
			if($user_ref != ""){
				$user['ref_from_account'] = $user_ref['from_account'];
			}else{
				$user['ref_from_account'] = null;
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
	public function user_create($data = [])
	{
		$this->db->insert('account_agent', $data);
		$id = $this->db->insert_id();
		$this->user_find(['id'=>$id]);
		return $id;
	}
	public function user_update($data)
	{
		$this->db->where('id', $data['id']);
		$this->db->update('account', $data);
		$this->user_find(['id'=>$data['id']]);
	}
	public function user_list_page($search)
	{
		if(isset($search['status']) && $search['status'] !== ""){
			$this->db->select('
         account.id,
         account.username,
         account.agent,
         account.line_id,
         account.bank,
         account.bank_number,
         account.bank_name,
         account.amount_wallet,
         account.amount_wallet_ref,
         account.full_name,
         account.phone,
         account.point_for_wheel,
        account.login_point,
        account.point_for_return_balance,
         account.amount_deposit_auto,
         account.rank,
         account.remark,
         account.sum_amount,
         account.rank_point_sum,
         account.ref_transaction_id,
         account.is_auto_withdraw,
         DATE_FORMAT( account.created_at, "%Y-%m-%d") as created_at,
         account_agent.username as account_agent_username,
         account_agent.password as account_agent_password
       ',false);
		}else{
			$this->db->select('
         account.id,
         account.username,
         account.agent,
         account.line_id,
         account.bank,
         account.bank_number,
         account.bank_name,
         account.amount_wallet,
         account.amount_wallet_ref,
         account.full_name,
         account.phone,
         account.point_for_wheel,
        account.login_point,
        account.point_for_return_balance,
         account.amount_deposit_auto,
         account.rank,
         account.remark,
         account.sum_amount,
         account.rank_point_sum,
         account.ref_transaction_id,
         account.is_auto_withdraw,
         DATE_FORMAT( account.created_at, "%Y-%m-%d") as created_at
       ');
		}
		if(isset($search['id_list']) && is_array($search['id_list']) && count($search['id_list']) > 0){
			$this->db->where_in('account.id', $search['id_list']);
		}
		if(isset($search['deleted_ignore']) && $search['deleted_ignore']){

		}else{
			$this->db->where('account.deleted', 0);
		}
		$this->db->limit($search['per_page'], $search['page']);
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('account.username', $search['search']);
			$this->db->or_like('account.line_id', $search['search']);
			$this->db->or_like('account.full_name', $search['search']);
			$this->db->or_like('account.bank_number', $search['search']);
			$this->db->group_end();

		}
		if(isset($search['role_list']) && is_array($search['role_list']) && count($search['role_list']) > 0){
			$this->db->where_in('account.role', $search['role_list']);
		}else if(isset($_SESSION['user']) && isset($_SESSION['user']['role'])){
			if(isset($search['role']) && array_key_exists($search['role'],canManageRole()[$_SESSION['user']['role']])){
				$this->db->where_in('account.role', [$search['role']]);
			}else{
				$this->db->where_in('account.role', canManageRole()[$_SESSION['user']['role']]);
			}
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('account.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('account.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if(isset($search['status']) && $search['status'] !== ""){
			if($search['status'] == "1"){
				$this->db->where('account_agent.username IS NOT NULL');
				$this->db->where('account_agent.username <>',"");
			}else{
				$this->db->group_start();
				$this->db->where('account_agent.username IS NULL')->or_where('account_agent.username',"");
				$this->db->group_end();
			}
			$this->db->join('account_agent', 'account_agent.account_id = account.id','left');
		}
		if(
			isset($search['orderBy']) &&
			isset($search['sortBy'])
		){
			$this->db->order_by($search['sortBy'],$search['orderBy']);
		}else{
			$this->db->order_by('account.id', 'DESC');
		}
		$query = $this->db->get('account');
		$results = $query->result_array();
		if(isset($search['status']) && $search['status'] !== ""){

		}else{
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
				$this->cacheModel($results[$index]);
			}
		}
		return $results;
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

	private function getRefByAccountId($account_id = null){
		$this->db->select('
			id,
			from_account,
			to_account,
			agent
        ');
		$this->db->where('to_account', $account_id );
		$query = $this->db->get('ref');
		$ref = $query->row_array();
		return $ref;
	}

	public function user_count($search = [])
	{
		$this->db->select('
         count(1) as cnt_row
         ',false);
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('account.username', $search['search']);
			$this->db->or_like('account.line_id', $search['search']);
			$this->db->or_like('account.full_name', $search['search']);
			$this->db->or_like('account.bank_number', $search['search']);
			$this->db->group_end();
		}
		if(isset($search['role_list']) && is_array($search['role_list']) && count($search['role_list']) > 0){
			$this->db->where_in('account.role', $search['role_list']);
		}else if(isset($_SESSION['user']) && isset($_SESSION['user']['role'])){
			if(isset($search['role']) && array_key_exists($search['role'],canManageRole()[$_SESSION['user']['role']])){
				$this->db->where_in('account.role', [$search['role']]);
			}else{
				$this->db->where_in('account.role', canManageRole()[$_SESSION['user']['role']]);
			}
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('account.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('account.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if(isset($search['status']) && $search['status'] !== ""){
			if($search['status'] == "1"){
				$this->db->where('account_agent.username IS NOT NULL');
				$this->db->where('account_agent.username <>',"");
			}else{
				$this->db->group_start();
				$this->db->where('account_agent.username IS NULL')->or_where('account_agent.username',"");
				$this->db->group_end();
			}
			$this->db->join('account_agent', 'account_agent.account_id = account.id','left');
		}
		$this->db->where('account.deleted', 0);
		$query = $this->db->get('account');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}
	public function user_list($search = [])
	{
		$this->db->select('
         account.id,
         account.username,
         account.agent,
         account.line_id,
         account.bank_number,
         account.amount_wallet,
         account.full_name,
         account.phone,
         account.point_for_wheel,
        account.login_point,
        account.point_for_return_balance,
         account.role,
         account.rank,
         account.remark,
         account.sum_amount,
         account.rank_point_sum,
         account.ref_transaction_id,
         account.is_auto_withdraw,
         DATE_FORMAT( account.created_at, "%Y-%m-%d") as created_at,
         account_agent.username as account_agent_username,
         account_agent.password as account_agent_password
         ',false);
		if (isset($search['id'])) {
			$this->db->where('account.id', $search['id']);
		}
		if (isset($search['username'])) {
			$this->db->where('account.username', $search['username']);
		}
		if(isset($search['role_list']) && is_array($search['role_list']) && count($search['role_list']) > 0){
			$this->db->where_in('account.role', $search['role_list']);
		}else if(isset($_SESSION['user']) && isset($_SESSION['user']['role'])){
			if(isset($search['role']) && array_key_exists($search['role'],canManageRole()[$_SESSION['user']['role']])){
				$this->db->where_in('account.role', [$search['role']]);
			}else{
				$this->db->where_in('account.role', canManageRole()[$_SESSION['user']['role']]);
			}
		}
		$this->db->join('account_agent', 'account_agent.account_id = account.id','left');
		$query = $this->db->get('account');
		return $query->result_array();
	}
	public function user_select2($search = [])
	{
		$this->db->select('
         account.id,
         account.username,
         account.agent,
         account.line_id,
         account.bank_number,
         account.bank,
         account.bank_name,
         account.amount_wallet,
         account.amount_deposit_auto,
         account.full_name,
         account.phone,
         account.point_for_wheel,
        account.login_point,
        account.point_for_return_balance,
         account.role,
         account.rank,
         account.remark,
         account.sum_amount,
         account.rank_point_sum,
         account.ref_transaction_id,
         account.is_auto_withdraw,
         DATE_FORMAT( account.created_at, "%Y-%m-%d") as created_at
         ');
		if(
			isset($search['orderBy']) &&
			isset($search['sortBy'])
		){
			$this->db->order_by($search['sortBy'],$search['orderBy']);
		}
		if (isset($search['ref_from_account'])) {
			$this->db->where('account.id', $search['ref_from_account']);
		}
		//$this->db->join('account_agent', 'account_agent.account_id = account.id');
		$query = $this->db->get('account');
		return $query->result_array();
	}
	public function user_select2_for_credit_wait($search = [])
	{
		$this->db->select('
         account.id,
         account.username,
         account.agent,
         account.bank_number,
         account.bank,
         account.bank_name,
         account.full_name,
         account.phone,
         account_agent.username as account_agent_username,
         account_agent.password as account_agent_password
         ');
		if(
			isset($search['orderBy']) &&
			isset($search['sortBy'])
		){
			$this->db->order_by($search['sortBy'],$search['orderBy']);
		}
		$this->db->join('account_agent', 'account_agent.account_id = account.id');
		$query = $this->db->get('account');
		return $query->result_array();
	}
	public function agent_list_page($search)
	{
		$this->db->select(' 
         account.id,
         account.username,
         account.agent,
         account.line_id,
         account.bank,
         account.bank_number,
         account.bank_name,
         account.amount_wallet,
         account.commission_percent,
         account.is_active_return_balance,
         account.full_name,
         account.phone,
         account.point_for_wheel,
        account.login_point,
        account.point_for_return_balance,
         account.amount_deposit_auto,
         account.rank,
         account.remark,
         account.sum_amount,
         account.rank_point_sum,
         account.ref_transaction_id,
         account.is_auto_withdraw,
         DATE_FORMAT( account.created_at, "%Y-%m-%d") as created_at
       ');
		$this->db->where('account.deleted', 0);
		$this->db->where('account.agent', 1);

		$this->db->limit($search['per_page'], $search['page']);
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('account.username', $search['search']);
			$this->db->or_like('account.line_id', $search['search']);
			$this->db->or_like('account.full_name', $search['search']);
			$this->db->or_like('account.bank_number', $search['search']);
			$this->db->group_end();
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('account.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('account.created_at <=', date("{$search['date_end']} 23:59:59"));
		}

		//$this->db->join('account_agent', 'account_agent.account_id = account.id');
		$this->db->order_by('account.id', 'DESC');
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
	public function agent_count($search = [])
	{
		$this->db->select('
         count(1) as cnt_row
         ');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('account.username', $search['search']);
			$this->db->or_like('account.line_id', $search['search']);
			$this->db->or_like('account.full_name', $search['search']);
			$this->db->or_like('account.bank_number', $search['search']);
			$this->db->group_end();
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('account.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('account.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		$this->db->where('account.agent', 1);
		$this->db->where('account.deleted', 0);
		//$this->db->join('account_agent', 'account_agent.account_id = account.id');
		$query = $this->db->get('account');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}
	public function staff_list_page($search)
	{
		if(isset($search['status']) && $search['status'] !== ""){
			$this->db->select('
         account.id,
         account.username,
         account.line_id,
         account.bank,
         account.bank_number,
		 account.role,
         account.agent,
         account.bank_name,
         account.amount_wallet,
         account.amount_wallet_ref,
         account.full_name,
         account.phone,
         account.point_for_wheel,
        account.login_point,
        account.point_for_return_balance,
         account.amount_deposit_auto,
         account.rank,
         account.remark,
         account.sum_amount,
         account.rank_point_sum,
         account.ref_transaction_id,
         account.is_auto_withdraw,
         DATE_FORMAT( account.created_at, "%Y-%m-%d") as created_at,
         account_agent.username as account_agent_username,
         account_agent.password as account_agent_password
       ',false);
		}else{
			$this->db->select('
         account.id,
         account.username,
         account.line_id,
         account.bank,
         account.bank_number,
         account.bank_name,
		 account.role,
         account.agent,
         account.amount_wallet,
         account.amount_wallet_ref,
         account.full_name,
         account.phone,
         account.point_for_wheel,
        account.login_point,
        account.point_for_return_balance,
         account.amount_deposit_auto,
         account.rank,
         account.remark,
         account.sum_amount,
         account.rank_point_sum,
         account.ref_transaction_id,
         account.is_auto_withdraw,
         DATE_FORMAT( account.created_at, "%Y-%m-%d") as created_at
       ');
		}
		$CI = & get_instance();
		$this->db->where('account.deleted', 0);
		$this->db->where('account.role !=', 2);
		$this->db->limit($search['per_page'], $search['page']);
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('account.username', $search['search']);
			$this->db->or_like('account.line_id', $search['search']);
			$this->db->or_like('account.full_name', $search['search']);
			$this->db->or_like('account.bank_number', $search['search']);
			$this->db->group_end();
			/*if(strpos(strtoupper($search['search']),strtoupper($CI->config->item('api_merchant_id'))) !== false){
				$this->db->or_like('account_agent.username',$search['search']);
			}*/
		}
		if(isset($search['role_list']) && is_array($search['role_list']) && count($search['role_list']) > 0){
			$this->db->where_in('account.role', $search['role_list']);
		}else if(isset($_SESSION['user']) && isset($_SESSION['user']['role'])){
			$this->db->where_in('account.role', canManageRole()[$_SESSION['user']['role']]);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('account.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('account.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if(isset($search['status']) && $search['status'] !== ""){
			if($search['status'] == "1"){
				$this->db->where('account_agent.username IS NOT NULL');
				$this->db->where('account_agent.username <>',"");
			}else{
				$this->db->group_start();
				$this->db->where('account_agent.username IS NULL')->or_where('account_agent.username',"");
				$this->db->group_end();
			}
			/*if(isset($search['search']) && strpos(strtoupper($search['search']),strtoupper($CI->config->item('api_merchant_id'))) !== false){
				$this->db->like('account_agent.username',$search['search']);
			}*/
			$this->db->join('account_agent', 'account_agent.account_id = account.id','left');
		}else if(isset($search['search']) && strpos(strtoupper($search['search']),strtoupper($CI->config->item('api_merchant_id'))) !== false){
			//$this->db->join('account_agent', 'account_agent.account_id = account.id','left');
		}
		if(
			isset($search['orderBy']) &&
			isset($search['sortBy'])
		){
			$this->db->order_by($search['sortBy'],$search['orderBy']);
		}else{
			$this->db->order_by('account.id', 'DESC');
		}

		$query = $this->db->get('account');
		$results = $query->result_array();
		if(isset($search['status']) && $search['status'] !== ""){

		}else{
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
		}
		return $results;
	}
	public function staff_count($search = [])
	{
		$this->db->select('
         count(1) as cnt_row
         ',false);
		$CI = & get_instance();
		$this->db->where('account.role !=', 2);
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('account.username', $search['search']);
			$this->db->or_like('account.line_id', $search['search']);
			$this->db->or_like('account.full_name', $search['search']);
			$this->db->or_like('account.bank_number', $search['search']);
			$this->db->group_end();
			/*if(strpos(strtoupper($search['search']),strtoupper($CI->config->item('api_merchant_id'))) !== false){
				$this->db->or_like('account_agent.username',$search['search']);
			}*/
		}
		if(isset($search['role_list']) && is_array($search['role_list']) && count($search['role_list']) > 0){
			$this->db->where_in('account.role', $search['role_list']);
		}else if(isset($_SESSION['user']) && isset($_SESSION['user']['role'])){
			$this->db->where_in('account.role', canManageRole()[$_SESSION['user']['role']]);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('account.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('account.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if(isset($search['status']) && $search['status'] !== ""){
			if($search['status'] == "1"){
				$this->db->where('account_agent.username IS NOT NULL');
				$this->db->where('account_agent.username <>',"");
			}else{
				$this->db->group_start();
				$this->db->where('account_agent.username IS NULL')->or_where('account_agent.username',"");
				$this->db->group_end();
			}
			/*if(isset($search['search']) && strpos(strtoupper($search['search']),strtoupper($CI->config->item('api_merchant_id'))) !== false){
				$this->db->like('account_agent.username',$search['search']);
			}*/
			$this->db->join('account_agent', 'account_agent.account_id = account.id','left');
		}else if(isset($search['search']) && strpos(strtoupper($search['search']),strtoupper($CI->config->item('api_merchant_id'))) !== false){
			//$this->db->join('account_agent', 'account_agent.account_id = account.id','left');
		}
		$this->db->where('account.deleted', 0);
		$query = $this->db->get('account');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}
	public function staff_find_all($search = [])
	{
		$this->db->select('*');
		if (isset($search['id'])) {
			$this->db->where('account.id', $search['id']);
		}
		if (isset($search['username'])) {
			$this->db->where('account.username', $search['username']);
		}
		$query = $this->db->get('account');
		return $query->row_array();
	}

	public function user_list_sum_deposit_page($search)
	{
		if(isset($search['status']) && $search['status'] !== ""){
			$this->db->select('
         account.id,
         account.username,
         account.line_id,
         account.bank,
         account.bank_number,
         account.bank_name,
         account.amount_wallet,
         account.amount_wallet_ref,
         account.full_name,
         account.phone,
         account.point_for_wheel,
        account.login_point,
        account.point_for_return_balance,
		account.login_point,
		account.login_process_job_date,
         account.amount_deposit_auto,
         account.rank,
         account.remark,
         account.sum_amount,
         account.rank_point_sum,
          account.ref_transaction_id,
         account.is_auto_withdraw,
         DATE_FORMAT( account.created_at, "%Y-%m-%d") as created_at,
         account_agent.username as account_agent_username,
         account_agent.password as account_agent_password
       ',false);
		}else{
			$this->db->select('
          account.id,
         account.username,
         account.line_id,
         account.bank,
         account.bank_number,
         account.bank_name,
         account.amount_wallet,
         account.amount_wallet_ref,
         account.full_name,
         account.phone,
         account.point_for_wheel,
        account.login_point,
        account.point_for_return_balance,
		account.login_point,
		account.login_process_job_date,
         account.amount_deposit_auto,
         account.rank,
         account.remark,
         account.sum_amount,
         account.rank_point_sum,
         account.ref_transaction_id,
         account.is_auto_withdraw,
         DATE_FORMAT( account.created_at, "%Y-%m-%d") as created_at
       ');
		}

		$this->db->where('account.deleted', 0);
		$this->db->limit($search['per_page'], $search['page']);
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('account.username', $search['search']);
			$this->db->or_like('account.line_id', $search['search']);
			$this->db->or_like('account.full_name', $search['search']);
			$this->db->or_like('account.bank_number', $search['search']);
			$this->db->group_end();
		}else{

		}
		if(isset($search['role_list']) && is_array($search['role_list']) && count($search['role_list']) > 0){
			$this->db->where_in('account.role', $search['role_list']);
		}else if(isset($_SESSION['user']) && isset($_SESSION['user']['role'])){
			$this->db->where_in('account.role', canManageRole()[$_SESSION['user']['role']]);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('account.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('account.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if(isset($search['status']) && $search['status'] !== ""){
			if($search['status'] == "1"){
				$this->db->where('account_agent.username IS NOT NULL');
				$this->db->where('account_agent.username <>',"");
			}else{
				$this->db->group_start();
				$this->db->where('account_agent.username IS NULL')->or_where('account_agent.username',"");
				$this->db->group_end();
			}
			$this->db->join('account_agent', 'account_agent.account_id = account.id','left');
		}
		$this->db->where('finance.type', 1);
		$this->db->where('finance.status', 1);
		$this->db->join('finance', 'finance.account = account.id');
		$this->db->group_by('account.id');
		$this->db->order_by('account.id', 'DESC');
		$query = $this->db->get('account');
		$results = $query->result_array();
		if(isset($search['status']) && $search['status'] !== ""){
			$user_id_list = [];
			foreach($results as $user){
				$user_id_list[] = $user['id'];
			}
			$user_account_finance_deposits = count($user_id_list) == 0 ? $user_id_list :  $this->getAccountFinanceDepositByAccountIdIn($user_id_list);
			foreach($results as $index => $result){
				if(array_key_exists($result['id'],$user_account_finance_deposits)){
					$results[$index]['sum_deposit'] = $user_account_finance_deposits[$result['id']]['sum_amount'];
				}else{
					$results[$index]['sum_deposit'] = 0;
				}
			}
		}else{
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
			$user_account_finance_deposits = count($user_id_list) == 0 ? $user_id_list :  $this->getAccountFinanceDepositByAccountIdIn($user_id_list);
			foreach($results as $index => $result){
				if(array_key_exists($result['id'],$user_account_finance_deposits)){
					$results[$index]['sum_deposit'] = $user_account_finance_deposits[$result['id']]['sum_amount'];
				}else{
					$results[$index]['sum_deposit'] = 0;
				}
			}
		}
		return $results;
	}
	public function user_sum_deposit_count($search = [])
	{
		$this->db->select('
         count(1) as cnt_row
         ',false);
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('account.username', $search['search']);
			$this->db->or_like('account.line_id', $search['search']);
			$this->db->or_like('account.full_name', $search['search']);
			$this->db->or_like('account.bank_number', $search['search']);
			$this->db->group_end();

		}else{

		}
		if(isset($search['role_list']) && is_array($search['role_list']) && count($search['role_list']) > 0){
			$this->db->where_in('account.role', $search['role_list']);
		}else if(isset($_SESSION['user']) && isset($_SESSION['user']['role'])){
			$this->db->where_in('account.role', canManageRole()[$_SESSION['user']['role']]);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('account.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('account.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if(isset($search['status']) && $search['status'] !== ""){
			if($search['status'] == "1"){
				$this->db->where('account_agent.username IS NOT NULL');
				$this->db->where('account_agent.username <>',"");
			}else{
				$this->db->group_start();
				$this->db->where('account_agent.username IS NULL')->or_where('account_agent.username',"");
				$this->db->group_end();
			}
			$this->db->join('account_agent', 'account_agent.account_id = account.id',' left');
		}
		$this->db->where('finance.type', 1);
		$this->db->where('finance.status', 1);
		$this->db->join('finance', 'finance.account = account.id');
		$this->db->group_by('account.id');
		$this->db->where('account.deleted', 0);
		$query = $this->db->get('account');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}
	public function user_list_sum_deposit_excel($search)
	{
		if(isset($search['status']) && $search['status'] !== ""){
			$this->db->select('
           account.id,
         account.username,
         account.line_id,
         account.bank,
         account.bank_number,
         account.bank_name,
         account.amount_wallet,
         account.amount_wallet_ref,
         account.full_name,
         account.phone,
         account.point_for_wheel,
        account.login_point,
        account.point_for_return_balance,
		account.login_point,
		account.login_process_job_date,
         account.amount_deposit_auto,
         account.rank,
         account.remark,
         account.sum_amount,
         account.rank_point_sum,
         account.ref_transaction_id,
         account.is_auto_withdraw,
         DATE_FORMAT( account.created_at, "%Y-%m-%d") as created_at,
         account_agent.username as account_agent_username,
         account_agent.password as account_agent_password
       ',false);
		}else{
			$this->db->select('
         account.id,
         account.username,
         account.line_id,
         account.bank,
         account.bank_number,
         account.bank_name,
         account.amount_wallet,
         account.amount_wallet_ref,
         account.full_name,
         account.phone,
         account.point_for_wheel,
        account.login_point,
        account.point_for_return_balance,
		account.login_point,
		account.login_process_job_date,
         account.amount_deposit_auto,
         account.rank,
         account.remark,
         account.sum_amount,
         account.rank_point_sum,
         account.ref_transaction_id,
         account.is_auto_withdraw,
         DATE_FORMAT( account.created_at, "%Y-%m-%d") as created_at
       ');
		}

		$this->db->where('account.deleted', 0);
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('account.username', $search['search']);
			$this->db->or_like('account.line_id', $search['search']);
			$this->db->or_like('account.full_name', $search['search']);
			$this->db->or_like('account.bank_number', $search['search']);
			$this->db->group_end();
		}else{

		}
		if(isset($search['role_list']) && is_array($search['role_list']) && count($search['role_list']) > 0){
			$this->db->where_in('account.role', $search['role_list']);
		}else if(isset($_SESSION['user']) && isset($_SESSION['user']['role'])){
			$this->db->where_in('account.role', canManageRole()[$_SESSION['user']['role']]);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('account.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('account.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if(isset($search['status']) && $search['status'] !== ""){
			if($search['status'] == "1"){
				$this->db->where('account_agent.username IS NOT NULL');
				$this->db->where('account_agent.username <>',"");
			}else{
				$this->db->group_start();
				$this->db->where('account_agent.username IS NULL')->or_where('account_agent.username',"");
				$this->db->group_end();
			}
			$this->db->join('account_agent', 'account_agent.account_id = account.id','left');
		}
		$this->db->where('finance.type', 1);
		$this->db->where('finance.status', 1);
		$this->db->join('finance', 'finance.account = account.id');
		$this->db->group_by('account.id');
		$this->db->order_by('account.id', 'DESC');
		$query = $this->db->get('account');
		$results = $query->result_array();
		if(isset($search['status']) && $search['status'] !== ""){
			$user_id_list = [];
			foreach($results as $user){
				$user_id_list[] = $user['id'];
			}
			$user_account_finance_deposits = count($user_id_list) == 0 ? $user_id_list :  $this->getAccountFinanceDepositByAccountIdIn($user_id_list);
			foreach($results as $index => $result){
				if(array_key_exists($result['id'],$user_account_finance_deposits)){
					$results[$index]['sum_deposit'] = $user_account_finance_deposits[$result['id']]['sum_amount'];
				}else{
					$results[$index]['sum_deposit'] = 0;
				}
			}
		}else{
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
			$user_account_finance_deposits = count($user_id_list) == 0 ? $user_id_list :  $this->getAccountFinanceDepositByAccountIdIn($user_id_list);
			foreach($results as $index => $result){
				if(array_key_exists($result['id'],$user_account_finance_deposits)){
					$results[$index]['sum_deposit'] = $user_account_finance_deposits[$result['id']]['sum_amount'];
				}else{
					$results[$index]['sum_deposit'] = 0;
				}
			}
		}
		return $results;
	}
	private function getAccountFinanceDepositByAccountIdIn($user_id_list = []){
		$this->db->select('
			account,
			SUM(amount) as sum_amount
        ');
		$this->db->where_in('account', $user_id_list );
		$this->db->where('type', 1 );
		$this->db->where('status', 1 );
		$this->db->group_by('account');
		$query = $this->db->get('finance');
		$results = $query->result_array();
		$data=[];
		foreach($results as $result){
			$data[$result['account']] = $result;
		}
		return $data;
	}

	public function user_list_not_deposit_less_than_7_page($search)
	{
		if(isset($search['status']) && $search['status'] !== ""){
			$this->db->select('
         account.id,
         account.username,
         account.line_id,
         account.bank,
         account.bank_number,
         account.bank_name,
         account.amount_wallet,
         account.amount_wallet_ref,
         account.full_name,
         account.phone,
         account.point_for_wheel,
        account.login_point,
        account.point_for_return_balance,
		account.login_point,
		account.login_process_job_date,
         account.amount_deposit_auto,
         account.rank,
         account.remark,
         account.sum_amount,
         account.rank_point_sum,
         account.ref_transaction_id,
         account.is_auto_withdraw,
         DATE_FORMAT( account.created_at, "%Y-%m-%d") as created_at,
         account_agent.username as account_agent_username,
         account_agent.password as account_agent_password
       ',false);
		}else{
			$this->db->select('
          account.id,
         account.username,
         account.line_id,
         account.bank,
         account.bank_number,
         account.bank_name,
         account.amount_wallet,
         account.amount_wallet_ref,
         account.full_name,
         account.phone,
         account.point_for_wheel,
        account.login_point,
        account.point_for_return_balance,
		account.login_point,
		account.login_process_job_date,
         account.amount_deposit_auto,
         account.rank,
         account.remark,
         account.sum_amount,
         account.rank_point_sum,
         account.ref_transaction_id,
         account.is_auto_withdraw,
         DATE_FORMAT( account.created_at, "%Y-%m-%d") as created_at
       ');
		}

		$this->db->where('account.deleted', 0);
		$this->db->limit($search['per_page'], $search['page']);
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('account.username', $search['search']);
			$this->db->or_like('account.line_id', $search['search']);
			$this->db->or_like('account.full_name', $search['search']);
			$this->db->or_like('account.bank_number', $search['search']);
			$this->db->group_end();
		}else{

		}
		if(isset($search['role_list']) && is_array($search['role_list']) && count($search['role_list']) > 0){
			$this->db->where_in('account.role', $search['role_list']);
		}else if(isset($_SESSION['user']) && isset($_SESSION['user']['role'])){
			$this->db->where_in('account.role', canManageRole()[$_SESSION['user']['role']]);
		}
		$date_list = [];
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){

			try{
				$from_date = new DateTime($search['date_start']);
				$end_date = new DateTime($search['date_end']);
				do{
					$day = $from_date->format('Y-m-d');
					$date_list[] = $day;
					$from_date = $from_date->add(new DateInterval('P1D'));
				}while($from_date->getTimestamp() <= $end_date->getTimestamp());
			}catch (Exception $ex){

			}
			$this->db->having('account.id NOT IN (SELECT account FROM finance where type = 1 and status = 1 and DATE_FORMAT( finance.created_at, "%Y-%m-%d") in (\''.implode("', '", $date_list).'\')  group by finance.account)');
		}
		if(isset($search['status']) && $search['status'] !== ""){
			if($search['status'] == "1"){
				$this->db->where('account_agent.username IS NOT NULL');
				$this->db->where('account_agent.username <>',"");
			}else{
				$this->db->group_start();
				$this->db->where('account_agent.username IS NULL')->or_where('account_agent.username',"");
				$this->db->group_end();
			}
			$this->db->join('account_agent', 'account_agent.account_id = account.id','left');
		}
		$this->db->where('finance.type', 1);
		$this->db->where('finance.status', 1);
		$this->db->join('finance', 'finance.account = account.id');
		$this->db->group_by('account.id');
		$this->db->order_by('account.id', 'DESC');
		$query = $this->db->get('account');
		$results = $query->result_array();
		if(isset($search['status']) && $search['status'] !== ""){

		}else{
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
		}
		return $results;
	}
	public function user_list_not_deposit_less_than_7_excel($search)
	{
		if(isset($search['status']) && $search['status'] !== ""){
			$this->db->select('
          account.id,
         account.username,
         account.line_id,
         account.bank,
         account.bank_number,
         account.bank_name,
         account.amount_wallet,
         account.amount_wallet_ref,
         account.full_name,
         account.phone,
         account.point_for_wheel,
        account.login_point,
        account.point_for_return_balance,
		account.login_point,
		account.login_process_job_date,
         account.amount_deposit_auto,
         account.rank,
         account.remark,
         account.sum_amount,
         account.rank_point_sum,
         account.ref_transaction_id,
         account.is_auto_withdraw,
         DATE_FORMAT( account.created_at, "%Y-%m-%d") as created_at,
         account_agent.username as account_agent_username,
         account_agent.password as account_agent_password
       ',false);
		}else{
			$this->db->select('
           account.id,
         account.username,
         account.line_id,
         account.bank,
         account.bank_number,
         account.bank_name,
         account.amount_wallet,
         account.amount_wallet_ref,
         account.full_name,
         account.phone,
         account.point_for_wheel,
        account.login_point,
        account.point_for_return_balance,
		account.login_point,
		account.login_process_job_date,
         account.amount_deposit_auto,
         account.rank,
         account.remark,
         account.sum_amount,
         account.rank_point_sum,
         account.ref_transaction_id,
         account.is_auto_withdraw,
         DATE_FORMAT( account.created_at, "%Y-%m-%d") as created_at
       ');
		}

		$this->db->where('account.deleted', 0);
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('account.username', $search['search']);
			$this->db->or_like('account.line_id', $search['search']);
			$this->db->or_like('account.full_name', $search['search']);
			$this->db->or_like('account.bank_number', $search['search']);
			$this->db->group_end();
		}else{

		}
		if(isset($search['role_list']) && is_array($search['role_list']) && count($search['role_list']) > 0){
			$this->db->where_in('account.role', $search['role_list']);
		}else if(isset($_SESSION['user']) && isset($_SESSION['user']['role'])){
			$this->db->where_in('account.role', canManageRole()[$_SESSION['user']['role']]);
		}
		$date_list = [];
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){

			try{
				$from_date = new DateTime($search['date_start']);
				$end_date = new DateTime($search['date_end']);
				do{
					$day = $from_date->format('Y-m-d');
					$date_list[] = $day;
					$from_date = $from_date->add(new DateInterval('P1D'));
				}while($from_date->getTimestamp() <= $end_date->getTimestamp());
			}catch (Exception $ex){

			}
			$this->db->having('account.id NOT IN (SELECT account FROM finance where type = 1 and status = 1 and DATE_FORMAT( finance.created_at, "%Y-%m-%d") in (\''.implode("', '", $date_list).'\') group by finance.account)');
		}
		if(isset($search['status']) && $search['status'] !== ""){
			if($search['status'] == "1"){
				$this->db->where('account_agent.username IS NOT NULL');
				$this->db->where('account_agent.username <>',"");
			}else{
				$this->db->group_start();
				$this->db->where('account_agent.username IS NULL')->or_where('account_agent.username',"");
				$this->db->group_end();
			}
			$this->db->join('account_agent', 'account_agent.account_id = account.id','left');
		}
		$this->db->where('finance.type', 1);
		$this->db->where('finance.status', 1);
		$this->db->join('finance', 'finance.account = account.id');
		$this->db->group_by('account.id');
		$this->db->order_by('account.id', 'DESC');
		$query = $this->db->get('account');
		$results = $query->result_array();
		if(isset($search['status']) && $search['status'] !== ""){

		}else{
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
		}
		return $results;
	}
	public function user_not_deposit_less_than_7_count($search = [])
	{
		$this->db->select('
         count(1) as cnt_row
         ',false);
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('account.username', $search['search']);
			$this->db->or_like('account.line_id', $search['search']);
			$this->db->or_like('account.full_name', $search['search']);
			$this->db->or_like('account.bank_number', $search['search']);
			$this->db->group_end();
		}else{

		}
		if(isset($search['role_list']) && is_array($search['role_list']) && count($search['role_list']) > 0){
			$this->db->where_in('account.role', $search['role_list']);
		}else if(isset($_SESSION['user']) && isset($_SESSION['user']['role'])){
			$this->db->where_in('account.role', canManageRole()[$_SESSION['user']['role']]);
		}
		$date_list = [];
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){

			try{
				$from_date = new DateTime($search['date_start']);
				$end_date = new DateTime($search['date_end']);
				do{
					$day = $from_date->format('Y-m-d');
					$date_list[] = $day;
					$from_date = $from_date->add(new DateInterval('P1D'));
				}while($from_date->getTimestamp() <= $end_date->getTimestamp());
			}catch (Exception $ex){

			}
			$this->db->having('account.id NOT IN (SELECT account FROM finance where type = 1 and status = 1 and DATE_FORMAT( finance.created_at, "%Y-%m-%d") in (\''.implode("', '", $date_list).'\') group by finance.account)');
		}
		if(isset($search['status']) && $search['status'] !== ""){
			if($search['status'] == "1"){
				$this->db->where('account_agent.username IS NOT NULL');
				$this->db->where('account_agent.username <>',"");
			}else{
				$this->db->group_start();
				$this->db->where('account_agent.username IS NULL')->or_where('account_agent.username',"");
				$this->db->group_end();
			}
			$this->db->join('account_agent', 'account_agent.account_id = account.id','left');
		}
		$this->db->group_by('account.id');
		$this->db->where('finance.type', 1);
		$this->db->where('finance.status', 1);
		$this->db->join('finance', 'finance.account = account.id');
		$this->db->where('account.deleted', 0);
		$query = $this->db->get('account');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}
}
