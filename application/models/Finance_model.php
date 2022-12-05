<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Finance_model extends CI_Model
{
	public function finance_find($search = [])
	{
		$this->db->select('
        finance.id,
        finance.amount
        ');
		if (isset($search['id'])) {
			$this->db->where('finance.id', $search['id']);
		}
		if (isset($search['account'])) {
			$this->db->where('finance.account', $search['account']);
		}
		$this->db->join('account', 'account.id = finance.account');
		$query = $this->db->get('finance');
		return $query->row_array();
	}
	public function finance_create($data = [])
	{
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
		$this->db->insert('finance', $data);
		$id = $this->db->insert_id();
		return $id;
	}
	public function finance_update($data)
	{
		$this->db->where('id', $data['id']);
		$this->db->update('finance', $data);
	}
	public function finance_list($search)
	{
		$this->db->select('
        finance.id,
        finance.type,
        finance.amount,
        finance.created_at,
        finance.status
        ');
		if (isset($search['account'])) {
			$this->db->where('finance.account', $search['account']);
		}
		if (isset($search['type'])) {
			$this->db->where('finance.type', $search['type']);
		}
		$this->db->order_by('id', 'DESC');
		//$this->db->join('account', 'account.id = finance.account');
		$query = $this->db->get('finance');
		return $query->result_array();
	}
	public function finance_history($search)
	{
		$this->db->select('
        finance.id,
        finance.type,
        finance.amount,
        finance.created_at,
        finance.status
        ');
		$this->db->limit(20);
		if (isset($search['account'])) {
			$this->db->where('finance.account', $search['account']);
		}
		if (isset($search['type']) && $search['type'] !== "") {
			$this->db->where('finance.type', $search['type']);
		}
		$this->db->order_by('id', 'DESC');
		//$this->db->join('account', 'account.id = finance.account');
		$query = $this->db->get('finance');
		return $query->result_array();
	}
	public function commission_finance_sum($search)
	{
		$finance_deposit = $this->db->select('
        (SELECT SUM(finance.amount)) as sum
        ');
		$finance_deposit = $this->db->where('ref.agent', 1);
		$finance_deposit = $this->db->where('finance.status', 1);
		if (isset($search['account'])) {
			$finance_deposit = $this->db->where('ref.from_account', $search['account']);
		}
		$finance_deposit = $this->db->where('finance.type', 1);
		$finance_deposit = $this->db->where('finance.created_at >=', date("{$search['year']}-{$search['month']}-01 00:00:00"));
		$finance_deposit = $this->db->where('finance.created_at <=', date("{$search['year']}-{$search['month']}-31 23:59:59"));
		$finance_deposit = $this->db->join('ref', 'ref.to_account = finance.account');
		$finance_deposit = $this->db->join('account', 'account.id = ref.from_account');
		$finance_deposit = $this->db->get('finance');
		$finance_deposit_sum = $finance_deposit->row_array()['sum']!=""?$finance_deposit->row_array()['sum']:0;

		$finance_withdraw = $this->db->select('
          (SELECT SUM(finance.amount)) as sum
          ');
		$finance_withdraw = $this->db->where('ref.agent', 1);
		$finance_withdraw = $this->db->where('finance.status', 1);
		if (isset($search['account'])) {
			$finance_withdraw = $this->db->where('ref.from_account', $search['account']);
		}
		$finance_withdraw = $this->db->where('finance.type', 2);
		$finance_withdraw = $this->db->where_in('finance.status', [1,3]);
		$finance_withdraw = $this->db->where('finance.created_at >=', date("{$search['year']}-{$search['month']}-01 00:00:00"));
		$finance_withdraw = $this->db->where('finance.created_at <=', date("{$search['year']}-{$search['month']}-31 23:59:59"));
		$finance_withdraw = $this->db->join('ref', 'ref.to_account = finance.account');
		$finance_withdraw = $this->db->join('account', 'account.id = ref.from_account');
		$finance_withdraw = $this->db->get('finance');
		$finance_withdraw_sum = $finance_withdraw->row_array()['sum']!=""?$finance_withdraw->row_array()['sum']:0;
		return [
			'sum' => ($finance_deposit_sum-$finance_withdraw_sum),
			'deposit' => $finance_deposit_sum,
			'withdraw' => $finance_withdraw_sum
		];

	}
	public function commission_finance_find($search)
	{
		$this->db->select('
        finance.id,
        finance.amount,
        finance.created_at,
        finance.type,
        finance.status
        ');
		$this->db->where('ref.agent', 1);
		$this->db->where('finance.status', 1);
		if (isset($search['account'])) {
			$this->db->where('ref.from_account', $search['account']);
		}
		$this->db->where('finance.created_at >=', date("{$search['year']}-{$search['month']}-01 00:00:00"));
		$this->db->where('finance.created_at <=', date("{$search['year']}-{$search['month']}-31 23:59:59"));
		$this->db->join('ref', 'ref.to_account = finance.account');
		$this->db->join('account', 'account.id = ref.from_account');
		$query = $this->db->get('finance');
		return $query->result_array();
	}

	public function commission_group_by_year($search)
	{
		$this->db->select('
        DATE_FORMAT(finance.created_at, "%Y") as year
        ');
		$this->db->where('ref.agent', 1);
		if (isset($search['account'])) {
			$finance_withdraw = $this->db->where('ref.from_account', $search['account']);
		}
		$this->db->order_by('finance.id', 'DESC');
		$this->db->group_by('YEAR(finance.created_at)');
		$this->db->join('ref', 'ref.to_account = finance.account');
		$this->db->join('account', 'account.id = ref.from_account');
		$query = $this->db->get('finance');
		return $query->result_array();
	}
	public function commission_year_month_day($search)
	{
		$month = date('m');
		$finance_deposit = $this->db->select('
          (SELECT SUM(finance.amount)) as sum
          ');
		$finance_deposit = $this->db->where('ref.agent', 1);
		if (isset($search['account'])) {
			$finance_deposit = $this->db->where('ref.from_account', $search['account']);
		}
		$finance_deposit = $this->db->where('finance.type', 1);
		if(isset($search['day']) && isset($search['end_day'])){
			$finance_deposit = $this->db->where('finance.created_at >=', date("{$search['year']}-{$search['month']}-{$search['day']} 00:00:00"));
			$finance_deposit = $this->db->where('finance.created_at <=', date("{$search['year']}-{$search['month']}-{$search['end_day']} 23:59:59"));
		}else{
			$finance_deposit = $this->db->where('finance.created_at >=', date("{$search['year']}-{$search['month']}-{$search['day']} 00:00:00"));
			$finance_deposit = $this->db->where('finance.created_at <=', date("{$search['year']}-{$search['month']}-{$search['day']} 23:59:59"));
		}
		$finance_deposit = $this->db->join('ref','ref.to_account = finance.account');
		$finance_deposit = $this->db->join('account', 'account.id = ref.from_account');
		$finance_deposit = $this->db->get('finance');
		$finance_deposit_sum = $finance_deposit->row_array()['sum']!=""?$finance_deposit->row_array()['sum']:0;

		$finance_withdraw = $this->db->select('
          (SELECT SUM(finance.amount)) as sum
          ');
		$finance_withdraw = $this->db->where('ref.agent', 1);
		if (isset($search['account'])) {
			$finance_withdraw = $this->db->where('ref.from_account', $search['account']);
		}
		$finance_withdraw = $this->db->where('finance.type', 2);
		$finance_withdraw = $this->db->where_in('finance.status', [1,3]);
		if(isset($search['day']) && isset($search['end_day'])){
			$finance_withdraw = $this->db->where('finance.created_at >=', date("{$search['year']}-{$search['month']}-{$search['day']} 00:00:00"));
			$finance_withdraw = $this->db->where('finance.created_at <=', date("{$search['year']}-{$search['month']}-{$search['end_day']} 23:59:59"));
		}else{
			$finance_withdraw = $this->db->where('finance.created_at >=', date("{$search['year']}-{$search['month']}-{$search['day']} 00:00:00"));
			$finance_withdraw = $this->db->where('finance.created_at <=', date("{$search['year']}-{$search['month']}-{$search['day']} 23:59:59"));
		}
		$finance_withdraw = $this->db->join('ref', 'ref.to_account = finance.account');
		$finance_withdraw = $this->db->join('account', 'account.id = ref.from_account');
		$finance_withdraw = $this->db->get('finance');
		$finance_withdraw_sum = $finance_withdraw->row_array()['sum']!=""?$finance_withdraw->row_array()['sum']:0;
		return [
			'sum' => ($finance_deposit_sum-$finance_withdraw_sum),
			'deposit' => $finance_deposit_sum,
			'withdraw' => $finance_withdraw_sum
		];
	}

	public function report_member_year_month_day($search)
	{
		date_default_timezone_set('Asia/Bangkok');
		$this->db->select('
         to_account.id,
         to_account.username,
         account_agent.username as account_agent_username
       ',false);
		$this->db->where('ref.agent', 1);
		if (isset($search['account'])) {
			$this->db->where('ref.from_account', $search['account']);
		}
		if(isset($search['role_list']) && is_array($search['role_list']) && count($search['role_list']) > 0){
			$this->db->where_in('to_account.role', $search['role_list']);
		}else if(isset($_SESSION['user']) && isset($_SESSION['user']['role']) && count(canManageRole()[$_SESSION['user']['role']]) > 0){
			$this->db->where_in('to_account.role', canManageRole()[$_SESSION['user']['role']]);
		}
		$this->db->limit($search['per_page'], $search['page']);
		$this->db->join('account as to_account','ref.to_account = to_account.id');
		$this->db->join('account_agent as account_agent','to_account.id = account_agent.account_id','left');
		$this->db->order_by('ref.id', 'desc');
		$query = $this->db->get('ref');
		$users =  $query->result_array();
		$user_list = [];
		$user_key_list = [];
		foreach($users as $user){
			$user_list[] = $user['id'];
			if(!array_key_exists($user['id'],$user_key_list)){
				$user_key_list[$user['id']] = ['username' => $user['username'],'account_agent_username'=>$user['account_agent_username']];
			}
		}
		if(count($user_list) > 0){
			$month = date('m');
			$finance_deposit = $this->db->select('
          finance.account,(SELECT SUM(finance.amount)) as sum
          ');
			$finance_deposit = $this->db->where_in('finance.account', $user_list);
			$finance_deposit = $this->db->where('finance.type', 1);
			if(isset($search['date_start']) && isset($search['date_end'])){
				//$finance_deposit = $this->db->where('finance.created_at >=', date("{$search['year']}-{$search['month']}-{$search['day']} 00:00:00"));
				//$finance_deposit = $this->db->where('finance.created_at <=', date("{$search['year']}-{$search['month']}-{$search['end_day']} 23:59:59"));
				$finance_deposit = $this->db->where('finance.created_at >=', date("{$search['date_start']} 00:00:00"));
				$finance_deposit = $this->db->where('finance.created_at <=', date("{$search['date_end']} 23:59:59"));
			}else{
				$finance_deposit = $this->db->where('finance.created_at >=', date("".date('Y-m-d')." 00:00:00"));
				$finance_deposit = $this->db->where('finance.created_at <=', date("".date('Y-m-d')." 23:59:59"));
			}
			$finance_deposit = $this->db->group_by("finance.account");
			$finance_deposit = $this->db->get('finance');
			$finance_deposit = $finance_deposit->result_array();

			$finance_withdraw = $this->db->select('
          finance.account,(SELECT SUM(finance.amount)) as sum
          ');
			$finance_withdraw = $this->db->where_in('finance.account', $user_list);
			$finance_withdraw = $this->db->where('finance.type', 2);
			$finance_withdraw = $this->db->where_in('finance.status', [1,3]);
			if(isset($search['date_start']) && isset($search['date_end'])){
				//$finance_withdraw = $this->db->where('finance.created_at >=', date("{$search['year']}-{$search['month']}-{$search['day']} 00:00:00"));
				//$finance_withdraw = $this->db->where('finance.created_at <=', date("{$search['year']}-{$search['month']}-{$search['end_day']} 23:59:59"));
				$finance_withdraw = $this->db->where('finance.created_at >=', date("{$search['date_start']} 00:00:00"));
				$finance_withdraw = $this->db->where('finance.created_at <=', date("{$search['date_end']} 23:59:59"));
			}else{
				$finance_withdraw = $this->db->where('finance.created_at >=', date("".date('Y-m-d')." 00:00:00"));
				$finance_withdraw = $this->db->where('finance.created_at <=', date("".date('Y-m-d')." 23:59:59"));
			}
			$finance_withdraw = $this->db->group_by("finance.account");
			$finance_withdraw = $this->db->get('finance');
			$finance_withdraw = $finance_withdraw->result_array();

			$response_before = [];
			foreach($finance_deposit as $deposit){
				if(array_key_exists($deposit['account'],$response_before)){
					$response_before[$deposit['account']]['deposit'] += (float)$deposit['sum'];
					$response_before[$deposit['account']]['sum'] += (float)$deposit['sum'];
				}else{
					$response_before[$deposit['account']] = ['deposit'=>0.00,'sum'=>0.00,'withdraw'=>0.00];
					$response_before[$deposit['account']]['deposit'] += (float)$deposit['sum'];
					$response_before[$deposit['account']]['sum'] += (float)$deposit['sum'];
				}
			}
			foreach($finance_withdraw as $withdraw){
				if(array_key_exists($withdraw['account'],$response_before)){
					$response_before[$withdraw['account']]['withdraw'] += (float)$withdraw['sum'];
					$response_before[$withdraw['account']]['sum'] -= (float)$withdraw['sum'];
				}else{
					$response_before[$withdraw['account']] = ['withdraw'=>0.00,'sum'=>0.00,'deposit'=>0.00];
					$response_before[$withdraw['account']]['withdraw'] += (float)$withdraw['sum'];
					$response_before[$withdraw['account']]['sum'] -= (float)$withdraw['sum'];
				}
			}

			$response_after =[];
			foreach($user_key_list as $key => $value){
				if(array_key_exists($key,$response_before)){
					$response_after[] = [
						"id" => $key,
						"username" => $value['username'],
						"account_agent_username" => $value['account_agent_username'],
						"deposit" => $response_before[$key]['deposit'],
						"withdraw" => $response_before[$key]['withdraw'],
						"sum" => $response_before[$key]['sum'],
					];
				}else{
					$response_after[] = [
						"id" => $key,
						"username" => $value['username'],
						"account_agent_username" => $value['account_agent_username'],
						"deposit" => 0,
						"withdraw" => 0,
						"sum" => 0,
					];
				}

			}

			usort($response_after, function($a, $b) {
				return $a['id'] <= $b['id'];
			});

		}
		return $response_after;
	}

	public function report_member_year_month_day_count($search)
	{
		$this->db->select('
         count(1) as cnt_row
       ');
		$this->db->where('ref.agent', 1);
		if (isset($search['account'])) {
			$this->db->where('ref.from_account', $search['account']);
		}
		//$this->db->join('account as to_account','ref.to_account = to_account.id');
		$query = $this->db->get('ref');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}


	public function finance_report_all_day($search = []){
		$this->db->select('
			sum(amount) as sum_amount,
			count(id) as count
        ');
		if (isset($search['type'])) {
			$this->db->where('finance.type', $search['type']);
		}
		if (isset($search['status_list']) && is_array($search['status_list']) && count($search['status_list']) > 0) {
			$this->db->where_in('finance.status', $search['status_list']);
		}else{
			if (isset($search['status']) && $search['status'] !== "") {
				$this->db->where('finance.status', $search['status']);
			}
		}
		if (isset($search['created_at'])) {
			$this->db->like('finance.created_at', $search['created_at']);
		}
		$query = $this->db->get('finance');
		return $query->result_array();
	}
	public function finance_for_turn_list($search=[])
	{
		$this->db->select('
        finance.id,
        finance.type,
        finance.amount,
        finance.ref_transaction_id,
        finance.created_at,
        finance.status
        ');
		if (isset($search['start_date'])) {
			$this->db->where('finance.created_at >=', $search['start_date']." 00:00:00");
			if(isset($search['end_date'])){
				$this->db->where('finance.created_at <=', $search['end_date']." 23:59:59");
			}else{
				$this->db->where('finance.created_at <=', $search['start_date']." 23:59:59");
			}
		}
		if (isset($search['account'])) {
			$this->db->where('finance.account', $search['account']);
		}
		if (isset($search['username'])) {
			$this->db->where('account_agent.username', $search['username']);
		}
		$this->db->where('finance.type', 1);
		$this->db->where('finance.status', 1);
		$this->db->order_by('id', 'ASC');
		$this->db->join('account', 'account.id = finance.account');
		$this->db->join('account_agent', 'account_agent.accid = account.id');
		$query = $this->db->get('finance');
		return $query->result_array();
	}

	public function finance_find_created_at($search = [])
	{
		$this->db->select('
        finance.id,
				finance.account,
				finance.created_at
        ');
		if (isset($search['account'])) {
			$this->db->where('finance.account', $search['account']);
		}
		if (isset($search['limit'])) {
			$this->db->limit($search['limit']);
		}
		$this->db->order_by('created_at','DESC');
		$this->db->join('account', 'account.id = finance.account');
		$query = $this->db->get('finance');
		$data1 = $query->result_array();
		$data = [];
		foreach ($data1 as $key => $value) {
			$data[] = date("Y-m-d", strtotime($value['created_at']));
		}
		return array_unique($data);
	}

	public function finance_for_check_turn_find($search = [])
	{
		$this->db->select('
        finance.id,
        finance.created_at,
        finance.ref_transaction_id,
        finance.amount
        ');
		if (isset($search['id'])) {
			$this->db->where('finance.id', $search['id']);
		}
		if (isset($search['account'])) {
			$this->db->where('finance.account', $search['account']);
		}
		$this->db->where('finance.ref_transaction_id <>', '');
		$this->db->where('finance.ref_transaction_id IS NOT NULL');
		$this->db->order_by('finance.id', 'DESC');
		$this->db->join('account', 'account.id = finance.account');
		$query = $this->db->get('finance');
		return $query->row_array();
	}

	public function sum_bonus($search = [])
	{
		$this->db->select('
        finance.id,
        finance.amount
        ');
		if (isset($search['id'])) {
			$this->db->where('finance.id', $search['id']);
		}
		if (isset($search['account'])) {
			$this->db->where('finance.account', $search['account']);
		}
		if (isset($search['type'])) {
			$this->db->where('finance.type', $search['type']);
		}
		if (isset($search['status'])) {
			$this->db->where('finance.status', $search['status']);
		}
		if (isset($search['start_date'])) {
			$this->db->where('finance.created_at >=', $search['start_date']." 00:00:00");
			if(isset($search['end_date'])){
				$this->db->where('finance.created_at <=', $search['end_date']." 23:59:59");
			}else{
				$this->db->where('finance.created_at <=', $search['start_date']." 23:59:59");
			}
		}
		$query = $this->db->get('finance');
		$results = $query->result_array();
		$response = [
			'sum_promotion_amount' => 0.00,
			'sum_amount' => 0.00
		];
		$finance_id_list = [];
		foreach ($results as $result){
			$response['sum_amount'] += (float)$result['amount'];
			$finance_id_list[] = $result['id'];
		}
		if(count($finance_id_list) > 0){
			$this->db->select('
       		  sum(use_promotion.sum_amount) as sum_promotion_amount
        	');
			$this->db->where_in('use_promotion.finance', $finance_id_list);
			$query = $this->db->get('use_promotion');
			$result_use_promotions = $query->row_array();
			$response['sum_promotion_amount'] =  $result_use_promotions != "" && isset($result_use_promotions['sum_promotion_amount']) && is_numeric($result_use_promotions['sum_promotion_amount']) ? (int)$result_use_promotions['sum_promotion_amount'] : 0.00;
		}
		return $response;
	}

	public function sum_amount_deposit_and_withdraw($search)
	{
		$this->db->select('
           SUM(finance.amount) as sum_amount,
           finance.account,
           finance.type,
           finance.status
        ');
		if (isset($search['account_list']) && !empty($search['account_list'])) {
			$this->db->where_in('finance.account', $search['account_list']);
		}else{
			$this->db->where_in('finance.account', [NULL]);
		}
		$this->db->group_by(array("finance.account","finance.type","finance.status"));
		$query = $this->db->get('finance');
		$results = $query->result_array();
		$data = [];
		foreach ($results as  $value){
			$deposit = $value['type'] == "1" ? (float)$value['sum_amount'] : 0.00;
			$withdraw = $value['type'] == "2" && ($value['status'] == "1" || $value['status'] == "3") ? (float)$value['sum_amount'] : 0.00;
			$sum_amount = $deposit - $withdraw;
			if(array_key_exists($value['account'],$data)){
				$data[$value['account']]['deposit'] += $deposit;
				$data[$value['account']]['withdraw'] += $withdraw;
				$data[$value['account']]['sum_amount'] += $sum_amount;
			}else{
				$data[$value['account']] = [
					"deposit" => $deposit,
					"withdraw" => $withdraw,
					"sum_amount" => $sum_amount,
				];
			}
		}
		return $data;
	}

	public function finance_report_all_day_group_by($search = []){
		$this->db->select('
			sum(amount) as sum_amount,
			sum(from_amount) as sum_bonus,
			count(id) as count,
			DATE_FORMAT(created_at,"%Y-%m-%d") as created_at,
			status,
			type
        ');
		if (isset($search['created_at'])) {
			if(strlen($search['created_at']) > 7){
				if(isset($search['created_at_addon']) && !empty(trim($search['created_at_addon']))){
					$this->db->where('DATE_FORMAT( finance.created_at, "%Y-%m-%d") =', $search['created_at'])
						->or_where('DATE_FORMAT( finance.created_at, "%Y-%m-%d") =', $search['created_at_addon']);
				}else{
					$this->db->where('DATE_FORMAT( finance.created_at, "%Y-%m-%d") =', $search['created_at']);
				}
				$this->db->group_by(array('DATE_FORMAT(finance.created_at,"%Y-%m-%d")', "finance.type", "finance.status"));
			}else if(strlen($search['created_at']) == 7){
				$this->db->where('DATE_FORMAT( finance.created_at, "%Y-%m") =', $search['created_at']);
				$this->db->group_by(array('DATE_FORMAT(finance.created_at,"%Y-%m-%d")', "finance.type", "finance.status"));
			}
		}
		$query = $this->db->get('finance');
		return $query->result_array();
	}


	public function finance_and_credit_history_group_by_account_chk_scam($search = []){
		$this->db->select('
			sum(amount) as sum_amount,
			account
        ');
		if (isset($search['created_at'])) {
			$this->db->where('finance.created_at >=', $search['created_at']." 00:00:00");
			$this->db->where('finance.created_at <=', $search['created_at']." 23:59:59");
		}
		$this->db->where('finance.type =', 1);
		$this->db->group_by(array('account'));
		$query = $this->db->get('finance');
		$result_finance = $query->result_array();
		$finances = [];
		foreach ($result_finance as $finance){
			$finances[$finance['account']] = $finance['sum_amount'];
		}
		$this->db->select('
			sum(process) as sum_amount,
			account
        ');
		if (isset($search['created_at'])) {
			$this->db->where('credit_history.created_at >=', $search['created_at']." 00:00:00");
			$this->db->where('credit_history.created_at <=', $search['created_at']." 23:59:59");
		}
		$this->db->where('credit_history.type =', 1);
		$this->db->group_by(array('account'));
		$query = $this->db->get('credit_history');
		$result_credit = $query->result_array();
		$credits = [];
		foreach ($result_credit as $credit){
			$credits[$credit['account']] = $credit['sum_amount'];
		}
		$this->db->select('
			sum(from_amount) as sum_amount,
			account
        ');
		if (isset($search['created_at'])) {
			$this->db->where('log_add_credit.created_at >=', $search['created_at']." 00:00:00");
			$this->db->where('log_add_credit.created_at <=', $search['created_at']." 23:59:59");
		}
		$this->db->where_in('log_add_credit.type', ['bonus_promotion','bonus_not_use_promotion']);
		$this->db->group_by(array('account'));
		$query = $this->db->get('log_add_credit');
		$result_credit_log = $query->result_array();
		$credit_logs = [];
		foreach ($result_credit_log as $credit_log){
			$credit_logs[$credit_log['account']] = $credit_log['sum_amount'];
		}
		$results = [
			'finance' => $finances,
			'credit' => $credits,
			'credit_log' => $credit_logs,
		];
		return $results;
	}

}
