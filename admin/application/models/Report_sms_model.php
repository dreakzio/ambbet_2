<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_sms_model extends CI_Model
{
	public function report_sms_find($search = [])
	{
		$this->db->select('
 		 report_smses.*,
 		 bank.bank_code as bank_bank_code,
 		 bank.account_name as bank_account_name,
 		 bank.bank_number as bank_bank_number
         ',false);
		if (isset($search['id'])) {
			$this->db->where('report_smses.id', $search['id']);
		}
		if (isset($search['type_deposit_withdraw'])) {
			$this->db->where('report_smses.type_deposit_withdraw', $search['type_deposit_withdraw']);
		}
		if (isset($search['adjust_credit']) && $search['adjust_credit']) {
			$this->db->where('report_smses.deposit_withdraw_id IS NULL')->or_where('report_smses.deposit_withdraw_id',"");
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('report_smses.create_date >=', date("{$search['date_start']}"));
			$this->db->where('report_smses.create_date <=', date("{$search['date_end']}"));
		}
		$this->db->join('bank', 'report_smses.config_api_id = bank.id','left');
		$query = $this->db->get('report_smses');
		return $query->row_array();
	}
	public function report_find_for_update_credit_history($search = [])
	{
		$this->db->select('
 		 *
         ');
		if (isset($search['id'])) {
			$this->db->where('reports.id', $search['id']);
		}
		if (isset($search['sms_statement_refer_id'])) {
			$this->db->where('reports.sms_statement_refer_id', $search['sms_statement_refer_id']);
		}
		$query = $this->db->get('reports');
		return $query->row_array();
	}
	public function report_update($data)
	{
		$this->db->where('id', $data['id']);
		$this->db->update('reports', $data);
	}
	public function report_sms_update($data)
	{
		$this->db->where('id', $data['id']);
		$this->db->update('report_smses', $data);
	}
	public function report_sms_list_page($search)
	{
		$this->db->select('
          report_smses.id,
          report_smses.create_date,
          report_smses.create_time,
          report_smses.amount,
          report_smses.payment_gateway,
          report_smses.ref_number,
          report_smses.deposit_withdraw_id,
          report_smses.config_api_id,
          report_smses.created_at,
          report_smses.updated_at,
          report_smses.type_deposit_withdraw
         ');
		$this->db->limit($search['per_page'], $search['page']);
		$this->db->order_by('report_smses.id', 'DESC');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			if (isset($search['username']) && !empty(trim($search['username']))) {
				$this->db->like('report_smses.payment_gateway', $search['search']);
				$this->db->or_like('credit_history.username', $search['username']);
				$this->db->join('credit_history', 'credit_history.id = report_smses.deposit_withdraw_id','left');
			}else{
				$this->db->like('report_smses.payment_gateway', $search['search']);
			}
			$this->db->group_end();
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('report_smses.create_date >=', date("{$search['date_start']}"));
			$this->db->where('report_smses.create_date <=', date("{$search['date_end']}"));
		}

		if(
			isset($search['start_created_at']) && isset($search['end_created_at']) &&
			$search['start_created_at'] !== "" && $search['end_created_at'] !== ""
		){
			$split_start_date_time = explode(" ",$search['start_created_at']);
			$split_end_date_time = explode(" ",$search['end_created_at']);
			$start_date = trim($split_start_date_time[0]);
			$start_time = "00:00:00";
			$end_date = trim($split_end_date_time[0]);
			$end_time = "00:00:00";
			if(count($split_start_date_time) >= 2){
				$start_time = trim($split_start_date_time[1]).":00";
			}
			if(count($split_end_date_time) >= 2){
				$end_time = trim($split_end_date_time[1]).":59";
			}
			$this->db->where('STR_TO_DATE(CONCAT(`create_date`,\' \',`create_time`),\'%Y-%m-%d %H:%i:%s\') >= \''.$start_date.' '.$start_time.'\' AND STR_TO_DATE(CONCAT(`create_date`,\' \',`create_time`),\'%Y-%m-%d %H:%i:%s\') <= \''.$end_date.' '.$end_time.'\'');
		}else if(isset($search['start_created_at']) && $search['start_created_at'] !== ""){
			$split_start_date_time = explode(" ",$search['start_created_at']);
			$start_date = trim($split_start_date_time[0]);
			$start_time = "00:00:00";
			if(count($split_start_date_time) >= 2){
				$start_time = trim($split_start_date_time[1]).":00";
			}
			$this->db->where('STR_TO_DATE(CONCAT(`create_date`,\' \',`create_time`),\'%Y-%m-%d %H:%i:%s\') >= \''.$start_date.' '.$start_time.'\'');
		}else if(isset($search['end_created_at']) && $search['end_created_at'] !== ""){
			$split_end_date_time = explode(" ",$search['end_created_at']);
			$end_date = trim($split_end_date_time[0]);
			$end_time = "00:00:00";
			if(count($split_end_date_time) >= 2){
				$end_time = trim($split_end_date_time[1]).":59";
			}
			$this->db->where('STR_TO_DATE(CONCAT(`create_date`,\' \',`create_time`),\'%Y-%m-%d %H:%i:%s\') <= \''.$end_date.' '.$end_time.'\'');
		}
		if (isset($search['config_api_id'])) {
			$this->db->where('report_smses.config_api_id', $search['config_api_id']);
		}
		if (isset($search['bank_number'])) {
			$this->db->where('bank.bank_number', $search['bank_number']);
			$this->db->join('bank', 'report_smses.config_api_id = bank.id','left');
		}
		if (isset($search['type_deposit_withdraw'])) {
			$this->db->where('report_smses.type_deposit_withdraw', $search['type_deposit_withdraw']);
		}
		if (isset($search['adjust_credit']) && $search['adjust_credit']) {
			$this->db->where('report_smses.deposit_withdraw_id IS NULL')->or_where('report_smses.deposit_withdraw_id',"");
		}
		$query = $this->db->get('report_smses');
		return $query->result_array();
	}
	public function report_sms_count($search = [])
	{
		$this->db->select('
          count(1) as cnt_row
         ',false);
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			if (isset($search['username']) && !empty(trim($search['username']))) {
				$this->db->like('report_smses.payment_gateway', $search['search']);
				$this->db->or_like('credit_history.username', $search['username']);
				$this->db->join('credit_history', 'credit_history.id = report_smses.deposit_withdraw_id','left');
			}else{
				$this->db->like('report_smses.payment_gateway', $search['search']);
			}
			$this->db->group_end();
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('report_smses.create_date >=', date("{$search['date_start']}"));
			$this->db->where('report_smses.create_date <=', date("{$search['date_end']}"));
		}
		if(
			isset($search['start_created_at']) && isset($search['end_created_at']) &&
			$search['start_created_at'] !== "" && $search['end_created_at'] !== ""
		){
			$split_start_date_time = explode(" ",$search['start_created_at']);
			$split_end_date_time = explode(" ",$search['end_created_at']);
			$start_date = trim($split_start_date_time[0]);
			$start_time = "00:00:00";
			$end_date = trim($split_end_date_time[0]);
			$end_time = "00:00:00";
			if(count($split_start_date_time) >= 2){
				$start_time = trim($split_start_date_time[1]).":00";
			}
			if(count($split_end_date_time) >= 2){
				$end_time = trim($split_end_date_time[1]).":59";
			}
			$this->db->where('STR_TO_DATE(CONCAT(`create_date`,\' \',`create_time`),\'%Y-%m-%d %H:%i:%s\') >= \''.$start_date.' '.$start_time.'\' AND STR_TO_DATE(CONCAT(`create_date`,\' \',`create_time`),\'%Y-%m-%d %H:%i:%s\') <= \''.$end_date.' '.$end_time.'\'');
		}else if(isset($search['start_created_at']) && $search['start_created_at'] !== ""){
			$split_start_date_time = explode(" ",$search['start_created_at']);
			$start_date = trim($split_start_date_time[0]);
			$start_time = "00:00:00";
			if(count($split_start_date_time) >= 2){
				$start_time = trim($split_start_date_time[1]).":00";
			}
			$this->db->where('STR_TO_DATE(CONCAT(`create_date`,\' \',`create_time`),\'%Y-%m-%d %H:%i:%s\') >= \''.$start_date.' '.$start_time.'\'');
		}else if(isset($search['end_created_at']) && $search['end_created_at'] !== ""){
			$split_end_date_time = explode(" ",$search['end_created_at']);
			$end_date = trim($split_end_date_time[0]);
			$end_time = "00:00:00";
			if(count($split_end_date_time) >= 2){
				$end_time = trim($split_end_date_time[1]).":59";
			}
			$this->db->where('STR_TO_DATE(CONCAT(`create_date`,\' \',`create_time`),\'%Y-%m-%d %H:%i:%s\') <= \''.$end_date.' '.$end_time.'\'');
		}
		if (isset($search['config_api_id'])) {
			$this->db->where('report_smses.config_api_id', $search['config_api_id']);
		}
		if (isset($search['bank_number'])) {
			$this->db->where('bank.bank_number', $search['bank_number']);
			$this->db->join('bank', 'report_smses.config_api_id = bank.id','left');
		}
		if (isset($search['type_deposit_withdraw'])) {
			$this->db->where('report_smses.type_deposit_withdraw', $search['type_deposit_withdraw']);
		}
		if (isset($search['adjust_credit']) && $search['adjust_credit']) {
			$this->db->where('report_smses.deposit_withdraw_id IS NULL')->or_where('report_smses.deposit_withdraw_id',"");
		}
		$query = $this->db->get('report_smses');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}
}
