<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Finance_model extends CI_Model
{
	public function finance_find($search = [])
	{
		$this->db->select('
        finance.id,
        finance.type,
        finance.amount,
        finance.created_at,
        finance.bank,
        finance.account,
        finance.account as account_id,
        finance.bank_name,
        finance.bank_number,
        finance.ip,
        finance.manage_by_fullname,
        finance.manage_by,
        finance.qrcode,
        finance.status,
        finance.is_auto_withdraw,
        finance.auto_withdraw_status,
        finance.auto_withdraw_created_at,
        finance.auto_withdraw_updated_at,
        finance.auto_withdraw_remark,
        finance.bank_withdraw_id,
		finance.bank_withdraw_name
        ');
		if (isset($search['id'])) {
			$this->db->where('finance.id', $search['id']);
		}
		if (isset($search['type'])) {
			$this->db->where('finance.type', $search['type']);
		}
		if (isset($search['is_auto_withdraw_ignore']) && $search['is_auto_withdraw_ignore'] == true) {
			$this->db->group_start();
			$this->db->where('finance.is_auto_withdraw IS NULL')->or_where('finance.is_auto_withdraw',0);
			$this->db->group_end();
		}
		$this->db->limit(1);
		$query = $this->db->get('finance');
		$results = $query->result_array();
		$account_id_list = [];
		$account_list = [];
		$use_promotion_id_list = [];
		foreach($results as $result){
			$account_list[] = $result['account_id'];
			$use_promotion_id_list[] = $result['id'];
			$account_id_list[] = $result['account_id'];
		}
		$account_list = count($account_list) == 0 ? $account_list : $this->getAccountByAccountIdIn($account_list);
		$account_agents = $account_list;
		$use_promotions = count($use_promotion_id_list) == 0 ? $use_promotion_id_list :  $this->getUsePromotionByFinanceIdIn($use_promotion_id_list);
		foreach($results as $index => $result){
			if(array_key_exists($result['account_id'],$account_list)){
				$results[$index]['amount_wallet'] = $account_list[$result['account_id']]['amount_wallet'];
				$results[$index]['username'] = $account_list[$result['account_id']]['username'];
				$results[$index]['full_name'] = $account_list[$result['account_id']]['full_name'];
				$results[$index]['line_id'] = $account_list[$result['account_id']]['line_id'];
				$results[$index]['remark'] = $account_list[$result['account_id']]['remark'];
			}else{
				$results[$index]['amount_wallet'] = 0;
				$results[$index]['username'] = "";
				$results[$index]['full_name'] = "";
				$results[$index]['line_id'] = "";
				$results[$index]['remark'] = "";
			}
			if(array_key_exists($result['account_id'],$account_agents)){
				$results[$index]['account_agent_username'] = $account_agents[$result['account_id']]['account_agent_username'];
				$results[$index]['account_agent_password'] = $account_agents[$result['account_id']]['account_agent_password'];
			}else{
				$results[$index]['account_agent_username'] = "";
				$results[$index]['account_agent_password'] = "";
			}
			if(array_key_exists($result['id'],$use_promotions)){
				$results[$index]['promotion'] = $use_promotions[$result['id']]['promotion'];
				$results[$index]['promotion_name'] = $use_promotions[$result['id']]['promotion_name'];
				$results[$index]['percent'] = $use_promotions[$result['id']]['percent'];
				$results[$index]['turn'] = $use_promotions[$result['id']]['turn'];
				$results[$index]['max_value'] = $use_promotions[$result['id']]['max_value'];
				$results[$index]['sum_amount'] = $use_promotions[$result['id']]['sum_amount'];
			}else{
				$results[$index]['promotion'] = "";
				$results[$index]['promotion_name'] = "";
				$results[$index]['percent'] = "";
				$results[$index]['turn'] = "";
				$results[$index]['max_value'] = "";
				$results[$index]['sum_amount'] = "";
			}
		}
		$data = is_array($results) && count($results) == 1 ? $results[0] : "";
		if($data != ""){
			$this->cacheModel($data);
		}
		return $data;
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
			if(isset($data['status'])){
				$response['status'] = $data['status'];
			}
			if(isset($data['is_auto_withdraw'])){
				$response['is_auto_withdraw'] = $data['is_auto_withdraw'];
			}
			if(isset($data['auto_withdraw_status'])){
				$response['auto_withdraw_status'] = $data['auto_withdraw_status'];
			}
			if(isset($data['auto_withdraw_remark'])){
				$response['auto_withdraw_remark'] = $data['auto_withdraw_remark'];
			}
			if(isset($data['bank_withdraw_id'])){
				$response['bank_withdraw_id'] = $data['bank_withdraw_id'];
			}
			if(isset($data['bank_withdraw_name'])){
				$response['bank_withdraw_name'] = $data['bank_withdraw_name'];
			}
			return $response;
		}else{
			return $this->finance_find(['id'=> $data['id']]);
		}
	}

	public function finance_create($data = [])
	{
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
		$this->db->insert('finance', $data);
		$id = $this->db->insert_id();
		$this->finance_find(['id'=>$id]);
		return $id;
	}
	public function finance_update($data)
	{
		$this->db->where('id', $data['id']);
		$this->db->update('finance', $data);
		$this->finance_find(['id'=>$data['id']]);
	}
	public function finance_list($search = [])
	{
		$this->db->select('
        finance.id,
        finance.account,
        finance.status,
        finance.type,
         finance.created_at,
        finance.is_auto_withdraw,
        finance.auto_withdraw_status,
        finance.auto_withdraw_created_at,
        finance.auto_withdraw_updated_at,
        finance.auto_withdraw_remark,
		finance.bank_withdraw_id,
		finance.bank_withdraw_name
        ');
		$this->db->order_by('id', 'DESC');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('finance.amount', $search['search']);
		}
		if (isset($search['type'])) {
			$this->db->where('finance.type', $search['type']);
		}
		if (isset($search['status'])) {
			$this->db->where('finance.status', $search['status']);
		}
		if (isset($search['status_list']) && is_array($search['status_list']) && count($search['status_list']) > 0) {
			$this->db->where_in('finance.status', $search['status_list']);
		}
		if (isset($search['is_auto_withdraw'])) {
			$this->db->where('finance.is_auto_withdraw', $search['is_auto_withdraw']);
		}
		if (isset($search['auto_withdraw_status'])) {
			$this->db->where('finance.auto_withdraw_status', $search['auto_withdraw_status']);
		}
		if (isset($search['auto_withdraw_status_list']) && is_array($search['auto_withdraw_status_list']) && count($search['auto_withdraw_status_list']) > 0) {
			$this->db->where_in('finance.auto_withdraw_status', $search['auto_withdraw_status_list']);
		}
		$this->db->limit($search['per_page'], $search['page']);
		$query = $this->db->get('finance');
		$results = $query->result_array();
		$use_promotion_id_list = [];
		$account_list = [];
		foreach($results as $result){
			$account_list[] = $result['account'];
			if($result['type'] == "1"){
				$use_promotion_id_list[] = $result['id'];
			}
		}
		$account_list = count($account_list) == 0 ? $account_list : $this->getAccountByAccountIdIn($account_list);
		$use_promotions = count($use_promotion_id_list) == 0 ? $use_promotion_id_list :  $this->getUsePromotionByFinanceIdIn($use_promotion_id_list);
		foreach($results as $index => $result){
			$results[$index] = $this->cacheGetData($result);
		}
		return $results;
	}
	public function finance_list_page($search = [])
	{
		if(isset($search['search']) && $search['search'] !== ""){
			$this->db->select('
				finance.id,
				finance.account,
				finance.status,
				finance.type,
				finance.created_at,
				finance.is_auto_withdraw,
				finance.auto_withdraw_status,
				finance.auto_withdraw_created_at,
				finance.auto_withdraw_updated_at,
				finance.auto_withdraw_remark,
				finance.bank_withdraw_id,
				finance.bank_withdraw_name
				');
		}else{
			$this->db->select('
				finance.id,
				finance.account,
				finance.status,
				finance.type,
				finance.created_at,
				finance.is_auto_withdraw,
				finance.auto_withdraw_status,
				finance.auto_withdraw_created_at,
				finance.auto_withdraw_updated_at,
				finance.auto_withdraw_remark,
				finance.bank_withdraw_id,
				finance.bank_withdraw_name
				');
		}

		$this->db->order_by('id', 'DESC');
		if (isset($search['type'])) {
			$this->db->where('finance.type', $search['type']);
		}
		if (isset($search['status'])) {
			$this->db->where('finance.status', $search['status']);
			if($search['status'] == "0"){
				$this->db->group_start();
				$this->db->where('finance.auto_withdraw_status', 0)->or_where('finance.auto_withdraw_status IS NULL');
				$this->db->group_end();
			}else if($search['status'] == "1"){
				$this->db->group_start();
				$this->db->where('finance.auto_withdraw_status', 2)->or_where('finance.auto_withdraw_status IS NULL');
				$this->db->group_end();
			}else if($search['status'] == "2"){
				$this->db->group_start();
				$this->db->where('finance.auto_withdraw_status', 3)->or_where('finance.auto_withdraw_status IS NULL');
				$this->db->group_end();
			}else if($search['status'] == "4"){
				$this->db->group_start();
				$this->db->where('finance.auto_withdraw_status', 1)->or_where('finance.auto_withdraw_status IS NULL');
				$this->db->group_end();
			}
		}
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('finance.bank_number', $search['search']);
			$this->db->or_like('finance.username', $search['search']);
			$this->db->or_like('finance.amount', $search['search']);
			$this->db->group_end();
			//$this->db->where('account.deleted', 0);
			//$this->db->join('account', 'account.id = finance.account');
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('finance.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('finance.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if (isset($search['account'])) {
			$this->db->where('finance.account', $search['account']);
		}
		if (isset($search['status_list']) && is_array($search['status_list']) && count($search['status_list']) > 0) {
			$this->db->where_in('finance.status', $search['status_list']);
		}
		if (isset($search['is_auto_withdraw'])) {
			$this->db->where('finance.is_auto_withdraw', $search['is_auto_withdraw']);
		}
		if (isset($search['auto_withdraw_status'])) {
			$this->db->where('finance.auto_withdraw_status', $search['auto_withdraw_status']);
		}
		if (isset($search['auto_withdraw_status_list']) && is_array($search['auto_withdraw_status_list']) && count($search['auto_withdraw_status_list']) > 0) {
			$this->db->where_in('finance.auto_withdraw_status', $search['auto_withdraw_status_list']);
		}
		$this->db->limit($search['per_page'], $search['page']);
		$query = $this->db->get('finance');
		$results = $query->result_array();
		$use_promotion_id_list = [];
		$account_list = [];
		foreach($results as $result){
			$account_list[] = $result['account'];
			if($result['type'] == "1"){
				$use_promotion_id_list[] = $result['id'];
			}
		}
		$account_list = count($account_list) == 0 ? $account_list : $this->getAccountByAccountIdIn($account_list);
		$use_promotions = count($use_promotion_id_list) == 0 ? $use_promotion_id_list :  $this->getUsePromotionByFinanceIdIn($use_promotion_id_list);
		foreach($results as $index => $result){
			$results[$index] = $this->cacheGetData($result);
		}
		return $results;
	}
	private function getUsePromotionByFinanceIdIn($use_promotion_id_list = []){
		$data=[];
		$use_promotion_id_new_list = [];
		$cache_data_use_promotion = $this->cache->file->get(base64_encode(get_class($this)."_use_promotion_".date("Y_m_d")));
		foreach ($use_promotion_id_list as $use_promotion_id){
			$use_promotion = null;
			if($cache_data_use_promotion !== FALSE && !is_null($cache_data_use_promotion)) {
				if (array_key_exists($use_promotion_id, $cache_data_use_promotion)) {
					$use_promotion = $cache_data_use_promotion[$use_promotion_id];
				}
			}
			if(!is_null($use_promotion)){
				$data[$use_promotion_id] = $use_promotion;
			}else{
				$use_promotion_id_new_list[] = $use_promotion_id;
			}
		}
		if(count($use_promotion_id_new_list) > 0){
			$this->db->select('
			use_promotion.id,
			use_promotion.type,
			use_promotion.promotion,
			use_promotion.promotion_name,
			use_promotion.percent,
			use_promotion.turn,
			 use_promotion.turn_football,
			  use_promotion.turn_step,
			  use_promotion.turn_parlay,
			  use_promotion.turn_game,
			  use_promotion.turn_casino,
			  use_promotion.turn_lotto,
			  use_promotion.turn_m2,
			  use_promotion.turn_multi_player,
				use_promotion.max_value,
				use_promotion.max_use,
				use_promotion.sum_amount,
				use_promotion.finance
        ');
			$this->db->where_in('finance', $use_promotion_id_new_list );
			$query = $this->db->get('use_promotion');
			$results = $query->result_array();
			if(($cache_data_use_promotion !== FALSE && is_null($cache_data_use_promotion)) || $cache_data_use_promotion === FALSE){
				$cache_data_use_promotion = [];
			}
			foreach($results as $result){
				$cache_data_use_promotion[$result['finance']] = $result;
				$data[$result['finance']] = $result;
				$this->cache->file->save(base64_encode(get_class($this)."_use_promotion_".date("Y_m_d")),$cache_data_use_promotion, 31556926); // 1 year
			}
		}
		return $data;
	}

	private function processCacheAccountModel($account_id){
		$user = null;
		$cache_data_user = $this->cache->file->get(base64_encode("User_model_".date("Y_m_d")));
		if($cache_data_user !== FALSE && !is_null($cache_data_user)) {
			if (array_key_exists($account_id, $cache_data_user)) {
				$user = $cache_data_user[$account_id];
			}
		}
		return $user;
	}

	private function getAccountByAccountIdIn($account_id_list = []){
		$data=[];
		$account_id_new_list = [];
		foreach ($account_id_list as $user_id){
			$user = $this->processCacheAccountModel($user_id);
			if(!is_null($user)){
				$data[$user_id] = $user;
			}else{
				$account_id_new_list[] = $user_id;
			}
		}
		if(count($account_id_new_list) > 0){
			$user_list = $this->User_model->user_list_page([
				'deleted_ignore' => true,
				'role_list' => [roleSuperAdmin(),roleAdmin(),roleMember()],
				'id_list' => $account_id_new_list,
				'page' => 0,
				'per_page' => count($account_id_new_list),
			]);
			foreach ($user_list as $user){
				$data[$user['id']] = $user;
			}
		}
		return $data;
	}

	private function getAccountAgentByAccountIdIn($account_id_list = []){
		$data=[];
		$account_id_new_list = [];
		foreach ($account_id_list as $user_id){
			$user = $this->processCacheAccountModel($user_id);
			if(!is_null($user)){
				$data[$user_id] = $user;
			}else{
				$account_id_new_list[] = $user_id;
			}
		}
		if(count($account_id_new_list) > 0){
			$user_list = $this->User_model->user_list_page([
				'deleted_ignore' => true,
				'role_list' => [roleSuperAdmin(),roleAdmin(),roleMember()],
				'id_list' => $account_id_new_list,
				'page' => 0,
				'per_page' => count($account_id_new_list),
			]);
			foreach ($user_list as $user){
				$data[$user['id']] = $user;
			}
		}
		return $data;
	}
    public function finance_count($search = [])
    {
        $this->db->select('
         count(1) as cnt_row
         ');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('finance.bank_number', $search['search']);
			$this->db->or_like('finance.username', $search['search']);
			$this->db->or_like('finance.amount', $search['search']);
			$this->db->group_end();
			//$this->db->join('account', 'account.id = finance.account');
        }
        if (isset($search['type'])) {
            $this->db->where('finance.type', $search['type']);
        }
		if (isset($search['status'])) {
			$this->db->where('finance.status', $search['status']);
			if($search['status'] == "0"){
				$this->db->group_start();
				$this->db->where('finance.auto_withdraw_status', 0)->or_where('finance.auto_withdraw_status IS NULL');
				$this->db->group_end();
			}else if($search['status'] == "1"){
				$this->db->group_start();
				$this->db->where('finance.auto_withdraw_status', 2)->or_where('finance.auto_withdraw_status IS NULL');
				$this->db->group_end();
			}else if($search['status'] == "2"){
				$this->db->group_start();
				$this->db->where('finance.auto_withdraw_status', 3)->or_where('finance.auto_withdraw_status IS NULL');
				$this->db->group_end();
			}else if($search['status'] == "4"){
				$this->db->group_start();
				$this->db->where('finance.auto_withdraw_status', 1)->or_where('finance.auto_withdraw_status IS NULL');
				$this->db->group_end();
			}
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('finance.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('finance.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		if (isset($search['account'])) {
			$this->db->where('finance.account', $search['account']);
		}
		if (isset($search['status_list']) && is_array($search['status_list']) && count($search['status_list']) > 0) {
			$this->db->where_in('finance.status', $search['status_list']);
		}
		if (isset($search['is_auto_withdraw'])) {
			$this->db->where('finance.is_auto_withdraw', $search['is_auto_withdraw']);
		}
		if (isset($search['auto_withdraw_status'])) {
			$this->db->where('finance.auto_withdraw_status', $search['auto_withdraw_status']);
		}
		if (isset($search['auto_withdraw_status_list']) && is_array($search['auto_withdraw_status_list']) && count($search['auto_withdraw_status_list']) > 0) {
			$this->db->where_in('finance.auto_withdraw_status', $search['auto_withdraw_status_list']);
		}
        $query = $this->db->get('finance');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
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

	private function getAccountIdRefByFromAccountIdIn($account_id_list = []){
		$this->db->select('
			to_account
        ');
		$this->db->where('agent', 1);
		$this->db->where_in('from_account', $account_id_list );
		$query = $this->db->get('ref');
		$results = $query->result_array();
		$data=[];
		foreach($results as $result){
			$data[] = $result['to_account'];
		}
		return $data;
	}

    public function commission_year_month_day($search)
    {
		$account_list= [];
		if (isset($search['account'])) {
			$account_list = $this->getAccountIdRefByFromAccountIdIn([$search['account']]);
		}
		else if (isset($search['account_list'])) {
			$account_list = $this->getAccountIdRefByFromAccountIdIn($search['account_list']);
		}
		$month = date('m');
		$finance_list = $this->db->select('
            id,
            amount,
            account,
            type,
            status
          ');
		//$finance_list = $this->db->where('ref.agent', 1);
		$account_list = empty($account_list) ? [null] : $account_list;
		if (isset($search['account'])) {
			$finance_list = $this->db->where_in('account',$account_list);
			//$finance_list = $this->db->where('ref.from_account', $search['account']);
		}
		else if (isset($search['account_list'])) {
			$finance_list = $this->db->where_in('account',$account_list);
			//$finance_list = $this->db->where_in('ref.from_account', $search['account_list']);
		}
		if(isset($search['day']) && isset($search['end_day'])){
			$finance_list = $this->db->where('finance.created_at >=', date("{$search['year']}-{$search['month']}-{$search['day']} 00:00:00"));
			$finance_list = $this->db->where('finance.created_at <=', date("{$search['year']}-{$search['month']}-{$search['end_day']} 23:59:59"));
		}else{
			$this->db->where('DATE_FORMAT(created_at,"%Y-%m-%d") =', "{$search['year']}-{$search['month']}-{$search['day']}");
			//$finance_list = $this->db->where('finance.created_at >=', date("{$search['year']}-{$search['month']}-{$search['day']} 00:00:00"));
			//$finance_list = $this->db->where('finance.created_at <=', date("{$search['year']}-{$search['month']}-{$search['day']} 23:59:59"));
		}
		//$finance_list = $this->db->join('ref','ref.to_account = finance.account');
		//$finance_list = $this->db->join('use_promotion','use_promotion.finance = finance.id','left');
		//$finance_list = $this->db->join('account', 'account.id = ref.from_account');
		$finance_list = $this->db->get('finance');
		$finance_list =  $finance_list->result_array();

		$use_promotion_id_list = [];
		foreach($finance_list as $result){
			$use_promotion_id_list[] = $result['id'];
		}
		$data_new = [];
		$finance_bonus_sum = 0.00;
		$finance_withdraw_sum = 0.00;
		$finance_deposit_sum = 0.00;
		$use_promotions = count($use_promotion_id_list) == 0 ? $use_promotion_id_list :  $this->getUsePromotionByFinanceIdIn($use_promotion_id_list);
		foreach($finance_list as $index => $finance_list_data){
			if(isset($search['account_list'])){
				$finance_bonus_sum = 0.00;
				$finance_withdraw_sum = 0.00;
				$finance_deposit_sum = 0.00;
			}
			if(array_key_exists($finance_list_data['id'],$use_promotions)){
				$finance_list_data['promotion_type'] = $use_promotions[$finance_list_data['id']]['type'];
				$finance_list_data['promotion_max_value'] = $use_promotions[$finance_list_data['id']]['max_value'];
				$finance_list_data['promotion_max_use'] = $use_promotions[$finance_list_data['id']]['max_use'];
				$finance_list_data['promotion_percent'] = $use_promotions[$finance_list_data['id']]['percent'];
				$finance_list_data['promotion_id'] = $use_promotions[$finance_list_data['id']]['id'];
				$finance_list_data['promotion_sum_amount'] = $use_promotions[$finance_list_data['id']]['sum_amount'];
			}else{
				$finance_list_data['promotion_type'] = null;
				$finance_list_data['promotion_max_value'] = null;
				$finance_list_data['promotion_max_use'] = null;
				$finance_list_data['promotion_percent'] = null;
				$finance_list_data['promotion_id'] = null;
				$finance_list_data['promotion_sum_amount'] = null;
			}
			if($finance_list_data['type'] == "1"){
				$finance_deposit_sum += (float)$finance_list_data['amount'];
				if(!empty($finance_list_data['promotion_id']) && !empty($finance_list_data['promotion_type']) && !empty($finance_list_data['status']) && $finance_list_data['status'] == "1"){
					if($finance_list_data['promotion_type'] == "1"){
						if($finance_list_data['promotion_percent'] == 0 && $finance_list_data['promotion_max_value'] == 0 && $finance_list_data['promotion_max_use'] >= 1){
							$finance_bonus_sum += (float)$finance_list_data['promotion_sum_amount'] - (float)$finance_list_data['amount'];
						}else{
							$percent_calculate = round_up(($finance_list_data['promotion_percent']*$finance_list_data['amount'])/100,2);
							if ($percent_calculate>$finance_list_data['promotion_max_value']) {
								$percent_calculate = $finance_list_data['promotion_max_value'];
							}
							$finance_bonus_sum += (float)$percent_calculate;
						}
					}else if($finance_list_data['promotion_type'] == "2"){
						$finance_bonus_sum += (float)$finance_list_data['promotion_sum_amount'] - (float)$finance_list_data['amount'];
					}
				}
			}else if($finance_list_data['type'] == "2"){
				if($finance_list_data['status'] == "1" || $finance_list_data['status'] == "3"){
					$finance_withdraw_sum += (float)$finance_list_data['amount'];
				}
			}
			if(array_key_exists($finance_list_data['account'],$data_new)){
				$old_data = $data_new[$finance_list_data['account']];
				$data_new[$finance_list_data['account']] = [
					'sum' => ($finance_deposit_sum-$finance_withdraw_sum) + $old_data['sum'] ,
					'deposit' => $finance_deposit_sum + $old_data['deposit'],
					'withdraw' => $finance_withdraw_sum + $old_data['withdraw'],
					'bonus' => $finance_bonus_sum + $old_data['bonus'],
				];
			}else{
				$data_new[$finance_list_data['account']] = [
					'sum' => ($finance_deposit_sum-$finance_withdraw_sum),
					'deposit' => $finance_deposit_sum,
					'withdraw' => $finance_withdraw_sum,
					'bonus' => $finance_bonus_sum,
				];
			}
		}
		if (isset($search['account'])) {
			return [
				'sum' => ($finance_deposit_sum-$finance_withdraw_sum),
				'deposit' => $finance_deposit_sum,
				'withdraw' => $finance_withdraw_sum,
				'bonus' => $finance_bonus_sum,
			];
		}
		else if (isset($search['account_list'])) {
			return $data_new;
		}
    }
    public function commission_detail($search)
    {
        $this->db->select('
          finance.id,
          finance.amount,
          finance.created_at
          ');
        $this->db->where('ref.agent', 1);
        if (isset($search['account'])) {
            $this->db->where('ref.from_account', $search['account']);
        }
        $this->db->where('finance.created_at >=', date("{$search['year']}-{$search['month']}-{$search['day']} 00:00:00"));
        $this->db->where('finance.created_at <=', date("{$search['year']}-{$search['month']}-{$search['day']} 23:59:59"));
        $this->db->join('ref', 'ref.to_account = finance.account');
        $this->db->join('account', 'account.id = ref.from_account');
        $query = $this->db->get('finance');
        return $query->result_array();
    }
	public function report_member_year_month_day($search)
	{
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
			$this->db->where_in('to_account.role', canManageRole()[$_SESSION['user']['role']]);
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
		$users =  $query->result_array();
		$user_list = [];
		$user_key_list = [];
		foreach($users as $user){
			$user_list[] = $user['id'];
			if(!array_key_exists($user['id'],$user_key_list)){
				$user_key_list[$user['id']] = ['username' => $user['username'],'account_agent_username'=>$user['account_agent_username'],'created_at'=>$user['created_at']];
			}
		}
		if(count($user_list) > 0){
			$month = date('m');
			$finance_deposit = $this->db->select('
          finance.account,(SELECT SUM(finance.amount)) as sum
          ');
			$finance_deposit = $this->db->where_in('finance.account', $user_list);
			/*if (isset($search['account'])) {
				$finance_deposit = $this->db->where('ref.from_account', $search['account']);
			}*/
			$finance_deposit = $this->db->where('finance.type', 1);
			$finance_deposit = $this->db->where_in('finance.status', [1]);
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
			/*if (isset($search['account'])) {
				$finance_withdraw = $this->db->where('ref.from_account', $search['account']);
			}*/
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
						"created_at" => $value['created_at'],
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
						"created_at" => $value['created_at'],
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
       ',false);
		if (isset($search['search']) && !empty($search['search'])) {
			$this->db->like('to_account.username', $search['search'])->or_like('to_account.full_name', $search['search']);
		}
		$this->db->where('ref.agent', 1);
		if (isset($search['account'])) {
			$this->db->where('ref.from_account', $search['account']);
		}

		if(isset($search['date_start']) && isset($search['date_end']) && $search['date_start'] != "" && $search['date_end'] != ""){
			$this->db->where('ref.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('ref.created_at <=', date("{$search['date_end']} 23:59:59"));
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

	public function finance_list_no_join_page($search = [])
	{
		$this->db->select('
        finance.id,
        finance.status,
        finance.created_at,
        finance.is_auto_withdraw,
        finance.auto_withdraw_status,
        finance.auto_withdraw_created_at,
        finance.auto_withdraw_updated_at,
        finance.auto_withdraw_remark,
        finance.bank_withdraw_id,
		finance.bank_withdraw_name
        ');
		$this->db->order_by('id', 'DESC');
		if (isset($search['type'])) {
			$this->db->where('finance.type', $search['type']);
		}
		if (isset($search['status'])) {
			$this->db->where('finance.status', $search['status']);
		}
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('finance.bank_number', $search['search']);
			$this->db->or_like('finance.amount', $search['search']);
		}
		$this->db->limit($search['per_page'], $search['page']);
		//$this->db->join('account', 'account.id = finance.account');
		//$this->db->where('account.deleted', 0);
		$query = $this->db->get('finance');

		$results = $query->result_array();
		foreach($results as $index => $result){
			$results[$index] = $this->cacheGetData($result);
		}
		return $results;
	}
	public function finance_no_join_count($search = [])
	{
		$this->db->select('
          count(1) as cnt_row
         ');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->like('finance.bank_number', $search['search']);
			$this->db->or_like('finance.amount', $search['search']);
		}
		if (isset($search['type'])) {
			$this->db->where('finance.type', $search['type']);
		}
		//$this->db->where('account.deleted', 0);
		//$this->db->join('account', 'account.id = finance.account');
		$query = $this->db->get('finance');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}
	public function finance_report_summary_per_day_month($search = []){
		$this->db->select('
			sum(amount) as sum_amount
        ');
		if (isset($search['type'])) {
			$this->db->where('finance.type', $search['type']);
		}
		if (isset($search['created_at'])) {
			if(strlen($search['created_at']) > 7){
				$this->db->where('DATE_FORMAT( finance.created_at, "%Y-%m-%d") =', $search['created_at']);
			}else{
				$this->db->where('DATE_FORMAT( finance.created_at, "%Y-%m") =', $search['created_at']);
			}
		}
		if (isset($search['status_list']) && is_array($search['status_list']) && count($search['status_list']) > 0) {
			$this->db->where_in('finance.status', $search['status_list']);
		}else{
			if (isset($search['status']) && $search['status'] !== "") {
				$this->db->where('finance.status', $search['status']);
			}
		}
		//$this->db->where('finance.status', 1);
		$query = $this->db->get('finance');
		return $query->result_array();
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
			if(strlen($search['created_at']) > 7){
				$this->db->where('DATE_FORMAT( finance.created_at, "%Y-%m-%d") =', $search['created_at']);
			}else{
				$this->db->where('DATE_FORMAT( finance.created_at, "%Y-%m") =', $search['created_at']);
			}
		}
		$query = $this->db->get('finance');
		return $query->result_array();
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

	public function finance_report_all_month_year_group_by($search = []){
		$this->db->select('
			sum(amount) as sum_amount,
			count(id) as count,
			DATE_FORMAT(created_at,"%Y-%m") as created_at,
			status,
			type
        ');
		if (isset($search['created_at'])) {
			if(strlen($search['created_at']) == 7){
				$this->db->where('DATE_FORMAT( finance.created_at, "%Y-%m") =', $search['created_at']);
				$this->db->group_by(array('DATE_FORMAT(finance.created_at,"%Y-%m")', "finance.type", "finance.status"));
			}else{
				$this->db->where('DATE_FORMAT( finance.created_at, "%Y") =', $search['created_at']);
				$this->db->group_by(array('DATE_FORMAT(finance.created_at,"%Y-%m")', "finance.type", "finance.status"));
			}
		}
		$query = $this->db->get('finance');
		return $query->result_array();
	}

	public function finance_list_excel($search = [])
	{
		$this->db->select('
        finance.id,
        finance.type,
        finance.amount,
        finance.created_at,
        finance.bank,
        finance.bank_number,
        finance.status,
        finance.account as account_id,
        finance.username,
        finance.username as full_name,
         finance.is_auto_withdraw,
        finance.auto_withdraw_status,
        finance.auto_withdraw_created_at,
        finance.auto_withdraw_updated_at,
        finance.auto_withdraw_remark,
        finance.bank_withdraw_id,
		finance.bank_withdraw_name
        ');
		$this->db->order_by('id', 'DESC');
		if (isset($search['type'])) {
			$this->db->where('finance.type', $search['type']);
		}
		if (isset($search['status'])) {
			$this->db->where('finance.status', $search['status']);
		}
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('finance.bank_number', $search['search']);
			$this->db->or_like('finance.username', $search['search']);
			$this->db->or_like('finance.amount', $search['search']);
			$this->db->group_end();
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('finance.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('finance.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		//$this->db->where('account.deleted', 0);
		//$this->db->join('account', 'account.id = finance.account');
		$query = $this->db->get('finance');
		$results = $query->result_array();
		$account_id_list = [];
		$use_promotion_id_list = [];
		foreach($results as $result){
			$account_id_list[] = $result['account_id'];
			$use_promotion_id_list[] = $result['id'];
		}
		$account_agents = count($account_id_list) == 0 ? $account_id_list : $this->getAccountAgentByAccountIdIn($account_id_list);
		$use_promotions = count($use_promotion_id_list) == 0 ? $use_promotion_id_list :  $this->getUsePromotionByFinanceIdIn($use_promotion_id_list);
		foreach($results as $index => $result){
			if(array_key_exists($result['account_id'],$account_agents)){
				$results[$index]['account_agent_username'] = $account_agents[$result['account_id']]['account_agent_username'];
				$results[$index]['account_agent_password'] = $account_agents[$result['account_id']]['account_agent_password'];
			}else{
				$results[$index]['account_agent_username'] = "";
				$results[$index]['account_agent_password'] = "";
			}
			if(array_key_exists($result['id'],$use_promotions)){
				$results[$index]['promotion'] = $use_promotions[$result['id']]['promotion'];
				$results[$index]['promotion_name'] = $use_promotions[$result['id']]['promotion_name'];
				$results[$index]['percent'] = $use_promotions[$result['id']]['percent'];
				$results[$index]['turn'] = $use_promotions[$result['id']]['turn'];
				$results[$index]['turn_football'] = $use_promotions[$result['id']]['turn_football'];
				$results[$index]['turn_step'] = $use_promotions[$result['id']]['turn_step'];
				$results[$index]['turn_parlay'] = $use_promotions[$result['id']]['turn_parlay'];
				$results[$index]['turn_game'] = $use_promotions[$result['id']]['turn_game'];
				$results[$index]['turn_casino'] = $use_promotions[$result['id']]['turn_casino'];
				$results[$index]['turn_lotto'] = $use_promotions[$result['id']]['turn_lotto'];
				$results[$index]['turn_m2'] = $use_promotions[$result['id']]['turn_m2'];
				$results[$index]['turn_multi_player'] = $use_promotions[$result['id']]['turn_multi_player'];
				$results[$index]['turn_trading'] = $use_promotions[$result['id']]['turn_trading'];
				$results[$index]['turn_keno'] = $use_promotions[$result['id']]['turn_keno'];
				$results[$index]['max_value'] = $use_promotions[$result['id']]['max_value'];
				$results[$index]['sum_amount'] = $use_promotions[$result['id']]['sum_amount'];
			}else{
				$results[$index]['promotion'] = "";
				$results[$index]['promotion_name'] = "";
				$results[$index]['percent'] = "";
				$results[$index]['turn'] = "";
				$results[$index]['turn_football'] = "";
				$results[$index]['turn_step'] = "";
				$results[$index]['turn_parlay'] = "";
				$results[$index]['turn_game'] = "";
				$results[$index]['turn_casino'] = "";
				$results[$index]['turn_lotto'] = "";
				$results[$index]['turn_m2'] = "";
				$results[$index]['turn_multi_player'] = "";
				$results[$index]['turn_trading'] = "";
				$results[$index]['turn_keno'] = "";
				$results[$index]['max_value'] = "";
				$results[$index]['sum_amount'] = "";
			}
		}
		return $results;
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
	public function finance_for_check_turn_find($search = [])
	{
		$this->db->select('
        finance.id,
        finance.created_at,
        finance.ref_transaction_id,
        finance.created_at,
        finance.amount
        ');
		if (isset($search['id'])) {
			$this->db->where('finance.id', $search['id']);
		}
		if (isset($search['account'])) {
			$this->db->where('finance.account', $search['account']);
		}
		if (isset($search['ref_transaction_id'])) {
			$this->db->where('finance.ref_transaction_id', $search['ref_transaction_id']);
		}
		if (isset($search['type'])) {
			$this->db->where('finance.type', $search['type']);
		}
		if (isset($search['status'])) {
			$this->db->where('finance.status', $search['status']);
		}
		$this->db->where('finance.ref_transaction_id <>', '');
		$this->db->where('finance.ref_transaction_id IS NOT NULL');
		$this->db->order_by('finance.id', 'DESC');
		//$this->db->join('account', 'account.id = finance.account');
		$query = $this->db->get('finance');
		return $query->row_array();
	}

	public function finance_for_auto_withdraw_find($search = [])
	{
		$this->db->select('
        finance.id,
        finance.username,
        finance.type,
        finance.amount,
        finance.created_at,
        finance.bank,
        finance.account,
        finance.account as account_id,
        finance.bank_name,
        finance.bank_number,
        finance.ip,
        finance.manage_by_fullname,
        finance.manage_by,
        finance.qrcode,
        finance.status,
        finance.is_auto_withdraw,
        finance.auto_withdraw_status,
        finance.auto_withdraw_created_at,
        finance.auto_withdraw_updated_at,
        finance.auto_withdraw_remark,
        finance.bank_withdraw_id,
		finance.bank_withdraw_name
        ');
		if (isset($search['id'])) {
			$this->db->where('finance.id', $search['id']);
		}
		if (isset($search['type'])) {
			$this->db->where('finance.type', $search['type']);
		}
		if (isset($search['status'])) {
			$this->db->where('finance.status', $search['status']);
		}
		if (isset($search['status_list']) && is_array($search['status_list']) && count($search['status_list']) > 0) {
			$this->db->where_in('finance.status', $search['status_list']);
		}
		if (isset($search['is_auto_withdraw'])) {
			$this->db->where('finance.is_auto_withdraw', $search['is_auto_withdraw']);
		}
		if (isset($search['auto_withdraw_status'])) {
			$this->db->where('finance.auto_withdraw_status', $search['auto_withdraw_status']);
		}
		if (isset($search['auto_withdraw_status_list']) && is_array($search['auto_withdraw_status_list']) && count($search['auto_withdraw_status_list']) > 0) {
			$this->db->where_in('finance.auto_withdraw_status', $search['auto_withdraw_status_list']);
		}
		$this->db->order_by('finance.id', 'ASC');
		$this->db->limit(1);
		$query = $this->db->get('finance');
		$results = $query->result_array();
		$data = is_array($results) && count($results) == 1 ? $results[0] : null;
		return $data;
	}
}
