<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Log_deposit_withdraw_model extends CI_Model
{
	public function log_deposit_withdraw_list($search = [])
	{
		$this->db->select('
			log_deposit_withdraw.id,
			log_deposit_withdraw.description,
		    log_deposit_withdraw.created_at
        ',false);
		$this->db->order_by('log_deposit_withdraw.id', 'DESC');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('log_deposit_withdraw.username', $search['search']);
			//$this->db->like('account.username', $search['search']);
			$this->db->or_like('log_deposit_withdraw.description', $search['search']);
			if(strpos($search['search'],"ฝาก") !== false){
				$this->db->or_like('log_deposit_withdraw.type', 1);
			}else if(strpos($search['search'],"ถอน") !== false){
				$this->db->or_like('log_deposit_withdraw.type', 2);
			}
			$this->db->group_end();
			//$this->db->join('account', 'account.id = log_deposit_withdraw.account','left');
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('log_deposit_withdraw.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('log_deposit_withdraw.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if (isset($search['account'])) {
			$this->db->where('log_deposit_withdraw.account', $search['account']);
		}
		$this->db->limit($search['per_page'], $search['page']);
		$query = $this->db->get('log_deposit_withdraw');
		$results = $query->result_array();
		foreach($results as $index => $result){
			$results[$index] = $this->cacheGetData($result);
		}
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

	public function log_deposit_withdraw_create($data = [])
	{
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
		$this->db->insert('log_deposit_withdraw', $data);
		$id = $this->db->insert_id();
		$this->log_deposit_withdraw_find(['id'=>$id]);
		return $id;
	}
	public function log_deposit_withdraw_update($data)
	{
		$this->db->where('id', $data['id']);
		$this->db->update('log_deposit_withdraw', $data);
		$this->log_deposit_withdraw_find(['id'=>$data['id']]);
	}
	public function log_deposit_withdraw_find($search = [])
	{
		$this->db->select('
		    log_deposit_withdraw.id,
		    log_deposit_withdraw.account,
		    log_deposit_withdraw.amount,
		    log_deposit_withdraw.amount_before,
		    log_deposit_withdraw.type,
		    log_deposit_withdraw.admin,
		    log_deposit_withdraw.promotion_name,
		    log_deposit_withdraw.description,
		    log_deposit_withdraw.created_at,
		    log_deposit_withdraw.username
         ',false);
		$this->db->order_by('log_deposit_withdraw.id', 'DESC');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('log_deposit_withdraw.username', $search['search']);
			//$this->db->like('account.username', $search['search']);
			$this->db->or_like('log_deposit_withdraw.description', $search['search']);
			if(strpos($search['search'],"ฝาก") !== false){
				$this->db->or_like('log_deposit_withdraw.type', 1);
			}else if(strpos($search['search'],"ถอน") !== false){
				$this->db->or_like('log_deposit_withdraw.type', 2);
			}
			$this->db->group_end();
			//$this->db->join('account', 'account.id = log_deposit_withdraw.account','left');
		}
		if (isset($search['id'])) {
			$this->db->where('log_deposit_withdraw.id', $search['id']);
		}
		$query = $this->db->get('log_deposit_withdraw');
		$data = $query->row_array();
		if($data != ""){
			$account_id_list = [];
			//$account_id_list[] = $data['account'];
			if(!empty($data['admin'])){
				$account_id_list[] = $data['admin'];
			}
			$accounts =  count($account_id_list) > 0 ? $this->getAccountByAccountIdIn($account_id_list) : [];
			/*if (array_key_exists($data['account'], $accounts)) {
				$data['username'] = $accounts[$data['account']]['username'];
			} else {
				$data['username'] = null;
			}*/
			if(!empty($data['admin']) & !is_null($data['admin'])){
				if (array_key_exists($data['admin'], $accounts)) {
					$data['admin_username'] = $accounts[$data['admin']]['full_name'];
				} else {
					$data['admin_username'] = 'AUTO';
				}
			}else{
				$data['admin_username'] = 'AUTO';
			}
			$this->cacheModel($data);
		}
		return $data;
	}

	public function log_deposit_withdraw_count($search = [])
	{
		$this->db->select('
         count(1) as cnt_row
         ',false);
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('log_deposit_withdraw.username', $search['search']);
			//$this->db->like('account.username', $search['search']);
			$this->db->or_like('log_deposit_withdraw.description', $search['search']);
			if(strpos($search['search'],"ฝาก") !== false){
				$this->db->or_like('log_deposit_withdraw.type', 1);
			}else if(strpos($search['search'],"ถอน") !== false){
				$this->db->or_like('log_deposit_withdraw.type', 2);
			}
			$this->db->group_end();
			//$this->db->join('account', 'account.id = log_deposit_withdraw.account','left');
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('log_deposit_withdraw.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('log_deposit_withdraw.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if (isset($search['account'])) {
			$this->db->where('log_deposit_withdraw.account', $search['account']);
		}
		$query = $this->db->get('log_deposit_withdraw');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
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
			$response = $cache_data[$data['id']];
			if(isset($data['description'])){
				$response['description'] = $data['description'];
			}
			return $response;
		}else{
			return $this->log_deposit_withdraw_find(['id'=> $data['id']]);
		}
	}

	public function log_deposit_withdraw_report_for_withdraw_auto_status($search = [])
	{
		$this->db->select('
         count(1) as cnt,
         SUM(amount) as sum_amount
         ');
		$this->db->where('log_deposit_withdraw.type', 2);
		$this->db->where('log_deposit_withdraw.withdraw_status_request', 1);
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('log_deposit_withdraw.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('log_deposit_withdraw.created_at <=', date("{$search['date_end']} 23:59:59"));
		}else{
			if(
				isset($search['date_start']) && $search['date_start'] !== ""
			){
				$this->db->where('log_deposit_withdraw.created_at >=', date("{$search['date_start']} 00:00:00"));
			}else{
				$this->db->where('log_deposit_withdraw.created_at >=', date("Y-m-d H:i:s"));
			}
			if(
				isset($search['date_end']) && $search['date_end'] !== ""
			){
				$this->db->where('log_deposit_withdraw.created_at <=', date("{$search['date_end']} 23:59:59"));
			}
		}
		if (isset($search['account'])) {
			$this->db->where('log_deposit_withdraw.account', $search['account']);
		}
		if (isset($search['withdraw_status_status'])) {
			$this->db->where('log_deposit_withdraw.withdraw_status_status', $search['withdraw_status_status']);
		}else if (isset($search['withdraw_status_status_ignore'])) {
			$this->db->where('log_deposit_withdraw.withdraw_status_status <>', $search['withdraw_status_status_ignore']);
		}else if (isset($search['withdraw_status_status_list']) && count($search['withdraw_status_status_list']) > 0) {
			$this->db->where_in('log_deposit_withdraw.withdraw_status_status', $search['withdraw_status_status_list']);
		}
		$query = $this->db->get('log_deposit_withdraw');
		$cnt_row =  $query->row_array();
		return $cnt_row;
	}
}
