<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Log_transfer_out_model extends CI_Model
{
	public function log_transfer_out_create($data = [])
	{
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
		$this->db->insert('log_transfer_out', $data);
		$id = $this->db->insert_id();
		return $id;
	}
	public function log_transfer_out_update($data)
	{
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['updated_at'] = isset($data['updated_at']) ? $data['updated_at'] : date('Y-m-d H:i:s');
		$this->db->where('id', $data['id']);
		$this->db->update('log_transfer_out', $data);
	}
	public function log_transfer_out_list($search = [])
	{
		$this->db->select('
        	log_transfer_out.id,
        	log_transfer_out.amount,
        	log_transfer_out.created_at,
        	log_transfer_out.status,
        	log_transfer_out.updated_at,
        	log_transfer_out.bank_id,
        	log_transfer_out.description,
        	log_transfer_out.bank_to,
        	log_transfer_out.bank_number_to,
        	log_transfer_out.bank_acc_name_to,
        	CASE WHEN bank.bank_code IS NULL THEN log_transfer_out.bank ELSE bank.bank_code END AS bank,
        	CASE WHEN bank.bank_number IS NULL THEN log_transfer_out.bank_number ELSE bank.bank_number END AS bank_number,
        	CASE WHEN bank.account_name IS NULL THEN log_transfer_out.bank_acc_name ELSE bank.account_name END AS bank_acc_name,
        	CASE WHEN admin.full_name IS NULL THEN NULL ELSE admin.full_name END AS admin_full_name,
        	CASE WHEN admin.username IS NULL THEN NULL ELSE admin.username END AS admin_username
        ',false);
		$this->db->limit($search['per_page'], $search['page']);
		$this->db->order_by('log_transfer_out.id', 'DESC');
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('log_transfer_out.description', $search['search']);
			$this->db->or_like('log_transfer_out.amount', $search['search']);
			$this->db->or_like('log_transfer_out.bank_acc_name', $search['search']);
			$this->db->or_like('log_transfer_out.bank_acc_name_to', $search['search']);
			$this->db->or_like('log_transfer_out.bank_number', $search['search']);
			$this->db->or_like('log_transfer_out.bank_number_to', $search['search']);
			if($search['search'] == "ระหว่างดำเนินการ"){
				$this->db->or_where('log_transfer_out.status', "0");
			}else if($search['search'] == "สำเร็จ"){
				$this->db->or_where('log_transfer_out.status', "1");
			}else if($search['search'] == "ไม่สำเร็จ"){
				$this->db->or_where('log_transfer_out.status', "2");
			}else if($search['search'] == "AUTO"){
				$this->db->or_where('log_transfer_out.admin', "0");
			}
			$this->db->or_like('admin.full_name', $search['search']);
			$this->db->group_end();
			if(array_key_exists($search['search'],getKeyBankList())){
				$this->db->group_start();
				$this->db->where_in('log_transfer_out.bank', getKeyBankList()[$search['search']]);
				$this->db->or_where_in('log_transfer_out.bank_to', getKeyBankList()[$search['search']]);
				$this->db->group_end();
			}
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('log_transfer_out.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('log_transfer_out.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		$this->db->join('account as admin', 'admin.id = log_transfer_out.admin','left');
		$this->db->join('bank', 'bank.id = log_transfer_out.bank_id','left');
		$query = $this->db->get('log_transfer_out');
		return $query->result_array();
	}

	public function log_transfer_out_find($search = [])
	{
		$this->db->select('
        	log_transfer_out.id,
        	log_transfer_out.amount,
        	log_transfer_out.created_at,
        	log_transfer_out.status,
        	log_transfer_out.updated_at,
        	log_transfer_out.bank_id,
        	log_transfer_out.description,
        	log_transfer_out.bank_to,
        	log_transfer_out.bank_number_to,
        	log_transfer_out.bank_acc_name_to,
        	CASE WHEN bank.bank_code IS NULL THEN log_transfer_out.bank ELSE bank.bank_code END AS bank,
        	CASE WHEN bank.bank_number IS NULL THEN log_transfer_out.bank_number ELSE bank.bank_number END AS bank_number,
        	CASE WHEN bank.account_name IS NULL THEN log_transfer_out.bank_acc_name ELSE bank.account_name END AS bank_acc_name,
        	CASE WHEN admin.full_name IS NULL THEN NULL ELSE admin.full_name END AS admin_full_name,
        	CASE WHEN admin.username IS NULL THEN NULL ELSE admin.username END AS admin_username
        ',false);
		if (isset($search['id'])) {
			$this->db->where('log_transfer_out.id', $search['id']);
		}
		$this->db->order_by('log_transfer_out.id', 'DESC');
		$this->db->join('account as admin', 'admin.id = log_transfer_out.admin','left');
		$this->db->join('bank', 'bank.id = log_transfer_out.bank_id','left');
		$query = $this->db->get('log_transfer_out');
		return $query->row_array();
	}

	public function log_transfer_out_count($search = [])
	{
		$this->db->select('
         count(1) as cnt_row
         ',false);
		if (isset($search['search']) && !empty(trim($search['search']))) {
			$this->db->group_start();
			$this->db->like('log_transfer_out.description', $search['search']);
			$this->db->or_like('log_transfer_out.amount', $search['search']);
			$this->db->or_like('log_transfer_out.bank_acc_name', $search['search']);
			$this->db->or_like('log_transfer_out.bank_acc_name_to', $search['search']);
			$this->db->or_like('log_transfer_out.bank_number', $search['search']);
			$this->db->or_like('log_transfer_out.bank_number_to', $search['search']);
			if($search['search'] == "ระหว่างดำเนินการ"){
				$this->db->or_where('log_transfer_out.status', "0");
			}else if($search['search'] == "สำเร็จ"){
				$this->db->or_where('log_transfer_out.status', "1");
			}else if($search['search'] == "ไม่สำเร็จ"){
				$this->db->or_where('log_transfer_out.status', "2");
			}else if($search['search'] == "AUTO"){
				$this->db->or_where('log_transfer_out.admin', "0");
			}
			$this->db->or_like('admin.full_name', $search['search']);
			$this->db->group_end();
			if(array_key_exists($search['search'],getKeyBankList())){
				$this->db->group_start();
				$this->db->where_in('log_transfer_out.bank', getKeyBankList()[$search['search']]);
				$this->db->or_where_in('log_transfer_out.bank_to', getKeyBankList()[$search['search']]);
				$this->db->group_end();
			}
		}
		if(
			isset($search['date_start']) && isset($search['date_end']) &&
			$search['date_start'] !== "" && $search['date_end'] !== ""
		){
			$this->db->where('log_transfer_out.created_at >=', date("{$search['date_start']} 00:00:00"));
			$this->db->where('log_transfer_out.created_at <=', date("{$search['date_end']} 23:59:59"));
		}
		$this->db->join('account as admin', 'admin.id = log_transfer_out.admin','left');
		$query = $this->db->get('log_transfer_out');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
	}
}
