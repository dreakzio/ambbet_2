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
	public function ref_list_page($search)
	{
		$this->db->select('
         ref.id,
         ref.created_at,
             ref.to_account,
         ref.from_account_username,
         ref.to_account_username
        ',false);
		$this->db->limit($search['per_page'], $search['page']);
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('ref.from_account_username', $search['search']);
			$this->db->or_like('ref.to_account_username', $search['search']);
			$this->db->group_end();
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('ref.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('ref.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		//$this->db->join('account as from_account', 'from_account.id = ref.from_account');
		//$this->db->join('account as to_account', 'to_account.id = ref.to_account');
		//$this->db->join('account_agent as account_agent','to_account.id = account_agent.account_id','left');
		$this->db->order_by('ref.id', 'DESC');
		$query = $this->db->get('ref');
		$results = $query->result_array();
		$user_id_list = [];
		foreach($results as $user){
			$user_id_list[] = $user['to_account'];
		}
		$user_account_agents = count($user_id_list) == 0 ? $user_id_list :  $this->getAccountAgentByAccountIdIn($user_id_list);
		foreach($results as $index => $result){
			if(array_key_exists($result['to_account'],$user_account_agents)){
				$results[$index]['account_agent_username'] = $user_account_agents[$result['to_account']]['account_agent_username'];
				$results[$index]['account_agent_password'] = $user_account_agents[$result['to_account']]['account_agent_password'];
			}else{
				$results[$index]['account_agent_username'] = null;
				$results[$index]['account_agent_password'] = null;
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

	public function ref_count($search = [])
	{
		$this->db->select('
         count(1) as cnt_row
         ');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('ref.from_account_username', $search['search']);
			$this->db->or_like('ref.to_account_username', $search['search']);
			$this->db->group_end();
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('ref.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('ref.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		//$this->db->join('account as from_account', 'from_account.id = ref.from_account');
		//$this->db->join('account as to_account', 'to_account.id = ref.to_account');
		$query = $this->db->get('ref');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}
	public function ref_deposit_list_page($search)
	{
		if (isset($search['search']) && !empty($search['search'])) {
			$this->db->select('
         ref_deposit.id,
         ref_deposit.percent,
         ref_deposit.sum_amount,
         ref_deposit.created_at,
         ref_deposit.username_from,
         ref_deposit.username_from_ref,
         ref_deposit.type,
         ref_deposit.turn,
         ref_deposit.turnover_amount,
         ref_deposit.username_to as to_account_username
         ',false);
		}else{
			$this->db->select('
         ref_deposit.id,
         ref_deposit.percent,
         ref_deposit.sum_amount,
         ref_deposit.created_at,
         ref_deposit.username_from,
         ref_deposit.username_from_ref,
         ref_deposit.type,
         ref_deposit.turn,
         ref_deposit.turnover_amount,
         ref_deposit.account,
         ref_deposit.username_to as to_account_username
         ');
		}

		$this->db->limit($search['per_page'], $search['page']);
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('ref_deposit.username_from', $search['search']);
			$this->db->or_like('ref_deposit.username_to', $search['search']);
			$this->db->group_end();
			//$this->db->join('account as to_account', 'to_account.id = ref_deposit.account');
			//$this->db->join('account as from_account', 'from_account.username = ref_deposit.username_from','left');
		}
		if (isset($search['type_list']) && is_array($search['type_list']) && count($search['type_list']) > 0) {
			$this->db->where_in('ref_deposit.type', $search['type_list']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('ref_deposit.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('ref_deposit.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		//$this->db->join('finance', 'finance.id = ref_deposit.finance','left');
		//$this->db->join('account', 'account.id = finance.account','left');
		$this->db->order_by('ref_deposit.id', 'DESC');
		$query = $this->db->get('ref_deposit');
		$results = $query->result_array();
		$user_id_list = [];
		foreach($results as $index => $result){
			$results[$index]["username"] = null;
			$user_id_list[] = $result['account'];
		}
		if (isset($search['search']) && !empty($search['search'])) {

		}else{
			/*$user_account_agents = count($user_id_list) == 0 ? $user_id_list :  $this->getAccountByAccountIdIn($user_id_list);
			foreach($results as $index => $result){
				if(array_key_exists($result['account'],$user_account_agents)){
					$results[$index]['to_account_username'] = $user_account_agents[$result['account']]['username'];
				}else{
					$results[$index]['to_account_username'] = null;
				}
			}*/
		}
		return $results;
	}

	private function getAccountByAccountIdIn($user_id_list = []){
		$this->db->select('
			id,
			username
        ');
		$this->db->where_in('id', $user_id_list );
		$query = $this->db->get('account');
		$results = $query->result_array();
		$data=[];
		foreach($results as $result){
			$data[$result['id']] = $result;
		}
		return $data;
	}

	public function ref_deposit_count($search = [])
	{
		$this->db->select('
          count(1) as cnt_row
         ');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('ref_deposit.username_from', $search['search']);
			$this->db->or_like('ref_deposit.username_to', $search['search']);
			$this->db->group_end();
			//$this->db->join('account as to_account', 'to_account.id = ref_deposit.account');
			//$this->db->join('account as from_account', 'from_account.username = ref_deposit.username_from','left');
		}
		if (isset($search['type_list']) && is_array($search['type_list']) && count($search['type_list']) > 0) {
			$this->db->where_in('ref_deposit.type', $search['type_list']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('ref_deposit.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('ref_deposit.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		$query = $this->db->get('ref_deposit');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}

	public function ref_agent_sum_member_by_from_account_list($search = [])
	{

		$this->db->select('
            count(from_account) as sum_member,
            from_account
         ');
		$this->db->where_in('from_account',$search['from_account_list']);
		$this->db->where('agent',1);
		$this->db->group_by('from_account');
		$query = $this->db->get('ref');
		$results = $query->result_array();
		$data = [];
		foreach($results as $result){
			$data[$result['from_account']] = ['sum_member' =>$result['sum_member']];
		}
		return $data;
	}

	public function ref_agent_member_register_list($search)
	{
		$canManage = canManageRole()[$_SESSION['user']['role']];
		$this->db->select('
         to_account.id,
         to_account.username,
         ref.created_at,
         account_agent.username as account_agent_username
       ',false);
		if (isset($search['search']) && !empty($search['search'])) {
			$this->db->like('to_account.username', $search['search'])->or_like('to_account.full_name', $search['search']);
		}
		$this->db->where('ref.agent', 1);
		if (isset($search['account'])) {
			$this->db->where('ref.from_account', $search['account']);
		}
		if(isset($search['role_list']) && is_array($search['role_list']) && count($search['role_list']) > 0){
			$this->db->where_in('to_account.role', $search['role_list']);
		}else if(isset($_SESSION['user']) && isset($_SESSION['user']['role'])){
			$this->db->where_in('to_account.role', $canManage);
		}
		if(isset($search['date_start']) && isset($search['date_end']) && $search['date_start'] != "" && $search['date_end'] != ""){
			$this->db->where('ref.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('ref.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if(isset($search['date_start_member']) && isset($search['date_end_member']) && $search['date_start_member'] != "" && $search['date_end_member'] != ""){
			$this->db->where('ref.created_at >=', date("{$search['date_start_member']} 00:00:00"));
			$this->db->where('ref.created_at <=', date("{$search['date_end_member']} 23:59:59"));
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
		}
		$this->db->limit($search['per_page'], $search['page']);
		$this->db->join('account as to_account','ref.to_account = to_account.id');
		$this->db->join('account_agent as account_agent','to_account.id = account_agent.account_id','left');
		$this->db->order_by('ref.id', 'desc');
		$query = $this->db->get('ref');
		return $query->result_array();
	}

	public function ref_agent_member_register_count($search)
	{
		$this->db->select('
        	count(1) as cnt_row
       ',false);
		if (isset($search['search']) && !empty($search['search'])) {
			$this->db->like('to_account.username', $search['search'])->or_like('to_account.full_name', $search['search']);
		}
		$this->db->where('ref.agent', 1);
		if (isset($search['account'])) {
			$this->db->where('ref.from_account', $search['account']);
		}
		if(isset($search['role_list']) && is_array($search['role_list']) && count($search['role_list']) > 0){
			$this->db->where_in('to_account.role', $search['role_list']);
		}else if(isset($_SESSION['user']) && isset($_SESSION['user']['role'])){
			$this->db->where_in('to_account.role', canManageRole()[$_SESSION['user']['role']]);
		}
		if(isset($search['date_start']) && isset($search['date_end']) && $search['date_start'] != "" && $search['date_end'] != ""){
			$this->db->where('ref.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('ref.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if(isset($search['date_start_member']) && isset($search['date_end_member']) && $search['date_start_member'] != "" && $search['date_end_member'] != ""){
			$this->db->where('ref.created_at >=', date("{$search['date_start_member']} 00:00:00"));
			$this->db->where('ref.created_at <=', date("{$search['date_end_member']} 23:59:59"));
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
		}
		$this->db->join('account as to_account','ref.to_account = to_account.id');
		$this->db->join('account_agent as account_agent','to_account.id = account_agent.account_id','left');
		$query = $this->db->get('ref');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}

	public function ref_agent_user_update($data)
	{
		$this->db->where('from_account', $data['from_account']);
		$this->db->update('ref', $data);
	}

	public function ref_agent_user_add_and_or_update($data = [])
	{
		$this->db->select('
         ref.id,
         ref.from_account
       ');
		$this->db->where('to_account', $data['to_account']);
		$query = $this->db->get('ref');
		$ref = $query->row_array();
		if($ref == ""){
			date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
			$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
			$this->db->insert('ref', $data);
			$id = $this->db->insert_id();
		}else{
			if($ref['from_account'] != $data['from_account']){
				$this->db->where('to_account', $data['to_account']);
				$this->db->update('ref', $data);
			}
		}
		return true;
	}

	public function ref_agent_user_delete($data = [])
	{
		$this->db->where('to_account', $data['to_account']);
		$this->db->delete('ref');
	}
	public function ref_deposit_create($data = [])
	{
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
		$this->db->insert('ref_deposit', $data);
		$id = $this->db->insert_id();
		return $id;
	}
}
