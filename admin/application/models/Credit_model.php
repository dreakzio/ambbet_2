<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Credit_model extends CI_Model
{
	public function credit_find($search = [])
	{
		$this->db->select('
 		 credit_history.id,
         credit_history.account,
         credit_history.credit_before,
         credit_history.credit_after,
         credit_history.process,
         credit_history.admin,
         credit_history.type,
         credit_history.date_bank,
         credit_history.transaction,
         credit_history.created_at,
         credit_history.username,
         credit_history.slip_image
         ');
		if (isset($search['id'])) {
			$this->db->where('credit_history.id', $search['id']);
		}
		if (isset($search['account'])) {
			$this->db->where('credit_history.account', $search['account']);
		}
		if (isset($search['type'])) {
			$this->db->where('credit_history.type', $search['type']);
		}
		if (isset($search['process'])) {
			$this->db->where('credit_history.process', $search['process']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end'])
		){
			$this->db->where('credit_history.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('credit_history.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		$this->db->limit(1);
		$query = $this->db->get('credit_history');
		$data = $query->row_array();
		if($data != ""){
			$this->processCacheAccountModel($data['account']);
			$account_id_list = [];
			if(!empty($data['admin']) && !is_null($data['admin'])){
				$cache_data = $this->cache->file->get(base64_encode(get_class($this)."_account_agent_".date("Y_m_d")));
				if($cache_data !== FALSE && !is_null($data) && array_key_exists($data['admin'],$cache_data)){
					$data['admin_username'] = $cache_data[$data['admin']]['full_name'];
				}else{
					$account_id_list[] = $data['admin'];
					$accounts =  count($account_id_list) > 0 ? $this->getAccountByAccountIdIn($account_id_list) : [];
					if (array_key_exists($data['admin'], $accounts)) {
						$data['admin_username'] = $accounts[$data['admin']]['full_name'];
					} else {
						$data['admin_username'] = 'AUTO';
					}
					if(is_null($cache_data)){
						$cache_data = [];
					}
					$cache_data[$data['admin']] = [
						'full_name'	=> $data['admin_username']
					];
					$this->cache->file->save(base64_encode(get_class($this)."_account_agent_".date("Y_m_d")),$cache_data, 31556926); // 1 year
				}
			}else{
				$data['admin_username'] = 'AUTO';
			}
			$this->cacheModel($data);
		}
		return $data;
	}


	private function processCacheAccountModel($account_id){
		$user = null;
		$cache_data_user = $this->cache->file->get(base64_encode("User_model_".date("Y_m_d")));
		if($cache_data_user !== FALSE && !is_null($cache_data_user)){
			if(!array_key_exists($account_id,$cache_data_user)){
				$user = $this->User_model->user_find([
					'id' => $account_id
				]);
			}else{
				$user = $cache_data_user[$account_id];
			}
		}else if($cache_data_user === FALSE || is_null($cache_data_user)){
			$user = $this->User_model->user_find([
				'id' => $account_id
			]);
		}
		return $user;
	}

	private function cacheModel($data){
		$cache_data = $this->cache->file->get(base64_encode(get_class($this)."_".date("Y_d",strtotime($data['created_at']))));
		if($cache_data !== FALSE && !is_null($data)){
			$cache_data[$data['id']] = $data;
			$this->cache->file->save(base64_encode(get_class($this)."_".date("Y_d",strtotime($data['created_at']))),$cache_data, 31556926); // 1 year
		}else if(!is_null($data)){
			$cache_data = [
				$data['id'] => $data
			];
			$this->cache->file->save(base64_encode(get_class($this)."_".date("Y_d",strtotime($data['created_at']))),$cache_data, 31556926); // 1 year
		}
	}

	private function cacheGetData($data){
		$cache_data = $this->cache->file->get(base64_encode(get_class($this)."_".date("Y_d",strtotime($data['created_at']))));
		if($cache_data !== FALSE && array_key_exists($data['id'],$cache_data)){
			return $cache_data[$data['id']];
		}else{
			return $this->credit_find(['id'=> $data['id']]);
		}
	}

	public function credit_create($data = [])
	{
		if (empty($data)) {
			return;
		}
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
		$this->db->insert('credit_history', $data);
		$id = $this->db->insert_id();
		$this->credit_find(['id'=>$id]);
		return $id;
	}
	public function credit_update($data)
	{
		$this->db->where('id', $data['id']);
		$this->db->update('credit_history', $data);
		$this->credit_find(['id'=>$data['id']]);
	}
	public function credit_list_page($search)
	{
		$this->db->select('
         credit_history.id,
         credit_history.created_at
         ',false);
		$this->db->limit($search['per_page'], $search['page']);
		$this->db->limit(25);
		$this->db->order_by('credit_history.id', 'DESC');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('credit_history.username', $search['search']);
			$this->db->or_like('credit_history.process', $search['search']);
			$this->db->group_end();
			//$this->db->join('account', 'account.id = credit_history.account');
		}
		if(
			isset($search['date_start']) && isset($search['date_end'])
		){
			$this->db->where('credit_history.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('credit_history.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if(
			isset($search['status_add']) &&
			$search['status_add'] !== ""
		){
			$this->db->group_start();
			if($search['status_add'] == "2"){
				$this->db->where('credit_history.admin <>', 0);
				$this->db->where('credit_history.admin IS NOT NULL');
			}else{
				$this->db->where('credit_history.admin', 0)
					->or_where('credit_history.admin IS NULL');
			}
			$this->db->group_end();
		}
		if (isset($search['type'])) {
			$this->db->where('credit_history.type', $search['type']);
		}
		if (isset($search['status'])) {
			$this->db->where('credit_history.status', $search['status']);
		}else if(isset($search['status_list']) && count($search['status_list']) > 0){
			$this->db->where_in('credit_history.status', $search['status_list']);
		}else{
			$this->db->where('credit_history.status', 1);
		}
		if (isset($search['account'])) {
			$this->db->where('credit_history.account', $search['account']);
		}
		$query = $this->db->get('credit_history');
		$results = $query->result_array();
		foreach($results as $index => $result){
			$results[$index] = $this->cacheGetData($result);
		}
		/*foreach($results as $result){
			$account_id_list[] = $result['account'];
			if(!empty($result['admin']) & !is_null($result['admin'])){
				$account_id_list[] = $result['admin'];
			}
		}
		$accounts = count($account_id_list) == 0 ? $account_id_list :  $this->getAccountByAccountIdIn($account_id_list);
		foreach($results as $index => $result) {
			if (array_key_exists($result['account'], $accounts)) {
				$results[$index]['username'] = $accounts[$result['account']]['username'];
			} else {
				$results[$index]['username'] = null;
			}
			if(!empty($result['admin']) & !is_null($result['admin'])){
				if (array_key_exists($result['admin'], $accounts)) {
					$results[$index]['admin_username'] = $accounts[$result['admin']]['full_name'];
				} else {
					$results[$index]['admin_username'] = 'AUTO';
				}
			}else{
				$results[$index]['admin_username'] = 'AUTO';
			}
		}*/
		return $results;
	}

	private function getAccountByAccountIdIn($account_id_list = []){
		$this->db->select('
			account.id as account_id,
			account.amount_wallet,
			account.username,
			account.full_name,
			account.deleted,
			account.line_id
        ');
		$this->db->where_in('id', $account_id_list );
		$query = $this->db->get('account');
		$results = $query->result_array();
		$data=[];
		foreach($results as $result){
			$data[$result['account_id']] = $result;
		}
		return $data;
	}

	public function credit_count($search = [])
	{
		$this->db->select('
         count(1) as cnt_row
         ');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('credit_history.username', $search['search']);
			$this->db->or_like('credit_history.process', $search['search']);
			$this->db->group_end();
			//$this->db->join('account', 'account.id = credit_history.account');
		}
		if(
			isset($search['date_start']) && isset($search['date_end'])
		){
			$this->db->where('credit_history.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('credit_history.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if(
			isset($search['status_add']) &&
			$search['status_add'] !== ""
		){
			$this->db->group_start();
			if($search['status_add'] == "2"){
				$this->db->where('credit_history.admin <>', 0);
				$this->db->where('credit_history.admin IS NOT NULL');
			}else{
				$this->db->where('credit_history.admin', 0)
					->or_where('credit_history.admin IS NULL');
			}
			$this->db->group_end();
		}
		if (isset($search['type'])) {
			$this->db->where('credit_history.type', $search['type']);
		}
		if (isset($search['status'])) {
			$this->db->where('credit_history.status', $search['status']);
		}else if(isset($search['status_list']) && count($search['status_list']) > 0){
			$this->db->where_in('credit_history.status', $search['status_list']);
		}else{
			$this->db->where('credit_history.status', 1);
		}
		if (isset($search['account'])) {
			$this->db->where('credit_history.account', $search['account']);
		}
		$query = $this->db->get('credit_history');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}

	public function credit_sum_amount($search = [])
	{
		$this->db->select('
         sum(process) as sum_amount
         ');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('credit_history.username', $search['search']);
			$this->db->or_like('credit_history.process', $search['search']);
			$this->db->group_end();
			//$this->db->join('account', 'account.id = credit_history.account');
		}
		if(
			isset($search['date_start']) && isset($search['date_end'])
		){
			$this->db->where('credit_history.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('credit_history.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if(
			isset($search['status_add']) &&
			$search['status_add'] !== ""
		){
			$this->db->group_start();
			if($search['status_add'] == "2"){
				$this->db->where('credit_history.admin <>', 0);
				$this->db->where('credit_history.admin IS NOT NULL');
			}else{
				$this->db->where('credit_history.admin', 0)
					->or_where('credit_history.admin IS NULL');
			}
			$this->db->group_end();
		}
		if (isset($search['type'])) {
			$this->db->where('credit_history.type', $search['type']);
		}
		if (isset($search['status'])) {
			$this->db->where('credit_history.status', $search['status']);
		}else if(isset($search['status_list']) && count($search['status_list']) > 0){
			$this->db->where_in('credit_history.status', $search['status_list']);
		}else{
			$this->db->where('credit_history.status', 1);
		}
		$query = $this->db->get('credit_history');
		return $query->row_array();
	}

	public function credit_list_excel($search)
	{
		$this->db->select('
         credit_history.id,
         credit_history.account,
         credit_history.credit_before,
         credit_history.credit_after,
         credit_history.process,
         credit_history.admin,
         credit_history.type,
         credit_history.date_bank,
         credit_history.transaction,
         credit_history.created_at,
         credit_history.username,
         CASE WHEN admin.username IS NULL THEN "AUTO" ELSE admin.full_name END AS admin_username
         ',false);
		$this->db->order_by('credit_history.id', 'DESC');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('credit_history.username', $search['search']);
			$this->db->or_like('credit_history.process', $search['search']);
			$this->db->group_end();
		}
		if(
			isset($search['date_start']) && isset($search['date_end'])
		){
			$this->db->where('credit_history.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('credit_history.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if(
			isset($search['status_add']) &&
			$search['status_add'] !== ""
		){
			$this->db->group_start();
			if($search['status_add'] == "2"){
				$this->db->where('credit_history.admin <>', 0);
				$this->db->where('credit_history.admin IS NOT NULL');
			}else{
				$this->db->where('credit_history.admin', 0)
					->or_where('credit_history.admin IS NULL');
			}
			$this->db->group_end();
		}
		if (isset($search['type'])) {
			$this->db->where('credit_history.type', $search['type']);
		}
		if (isset($search['status'])) {
			$this->db->where('credit_history.status', $search['status']);
		}else if(isset($search['status_list']) && count($search['status_list']) > 0){
			$this->db->where_in('credit_history.status', $search['status_list']);
		}else{
			$this->db->where('credit_history.status', 1);
		}
		//$this->db->join('account', 'account.id = credit_history.account');
		$this->db->join('account as admin', 'admin.id = credit_history.admin','left');
		$query = $this->db->get('credit_history');
		return $query->result_array();
	}

	public function credit_list($search)
	{
		$this->db->select('
         credit_history.id,
         credit_history.created_at
         ',false);
		$this->db->order_by('credit_history.id', 'DESC');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('credit_history.username', $search['search']);
			$this->db->or_like('credit_history.process', $search['search']);
			$this->db->group_end();
		}
		if (isset($search['id_list']) && is_array($search['id_list']) && count($search['id_list']) > 0) {
			$this->db->where_in('credit_history.id', $search['id_list']);
		}
		if(
			isset($search['date_start']) && isset($search['date_end'])
		){
			$this->db->where('credit_history.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('credit_history.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if(
			isset($search['status_add']) &&
			$search['status_add'] !== ""
		){
			$this->db->group_start();
			if($search['status_add'] == "2"){
				$this->db->where('credit_history.admin <>', 0);
				$this->db->where('credit_history.admin IS NOT NULL');
			}else{
				$this->db->where('credit_history.admin', 0)
					->or_where('credit_history.admin IS NULL');
			}
			$this->db->group_end();
		}
		if (isset($search['type'])) {
			$this->db->where('credit_history.type', $search['type']);
		}
		if (isset($search['status'])) {
			$this->db->where('credit_history.status', $search['status']);
		}else if(isset($search['status_list']) && count($search['status_list']) > 0){
			$this->db->where_in('credit_history.status', $search['status_list']);
		}else{
			$this->db->where('credit_history.status', 1);
		}
		$query = $this->db->get('credit_history');
		$results = $query->result_array();
		foreach($results as $index => $result){
			$results[$index] = $this->cacheGetData($result);
		}
		return $results;
	}
}
