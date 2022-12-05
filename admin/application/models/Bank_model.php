<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Bank_model extends CI_Model
{
    public function web_setting_find($search = [])
    {
        $this->db->select('
          *
         ');
        $query = $this->db->get('web_setting');
		$result = $query->row_array();
		return $result;
    }
    public function bank_data_list()
    {
        $this->db->select('
        	*
        ');
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get('banks');
		$results = $query->result_array();
		return $results;
    }
    public function bank_create($data = [])
    {
        $this->db->insert('bank', $data);
        $id = $this->db->insert_id();
        return $id;
    }
    public function bank_update($data)
    {
        $this->db->where('id', $data['id']);
        $this->db->update('bank', $data);
    }
    public function bank_list($search = [])
    {
    	$security_api = "";
		if((isset($search['status_withdraw']) && $search['status_withdraw'] == "1") || (isset($search['security_api']) && $search['security_api'] === true)){
			$security_api = "api_token_1,
			api_token_2,
			api_token_3,
			auto_min_amount_transfer,
		    auto_transfer_bank_code,
		    auto_transfer_bank_acc_name,
		    auto_transfer_bank_number,";
		}
        $this->db->select('
         id,
		  bank_code,
		  bank_name,
		  username,
		  password,
		  account_name,
		  bank_number,
		  promptpay_number,
		  promptpay_status,
		  status,
		  '.$security_api.'
		  deleted,
		  balance,
		  status_withdraw,
		  start_time_can_not_deposit,
		  end_time_can_not_deposit,
		  message_can_not_deposit,
		  max_amount_withdraw_auto,
		  updated_at,
		  chk_cron_login,
		  error_message,
		  api_type,
		  auto_transfer
        ');
        $this->db->order_by('id', 'DESC');
        $this->db->where('deleted', 0);
		if (isset($search['id'])) {
			$this->db->where('bank.id', $search['id']);
		}
		if (isset($search['status'])) {
			$this->db->where('status', $search['status']);
		}
		if (isset($search['api_type'])) {
			$this->db->where('api_type', $search['api_type']);
		}
		if (isset($search['auto_transfer'])) {
			$this->db->where('auto_transfer', $search['auto_transfer']);
		}
		if (isset($search['status_withdraw'])) {
			$this->db->where('status_withdraw', $search['status_withdraw']);
			if($search['status_withdraw'] == 1){
				$this->db->order_by('id', 'RANDOM()');
			}
		}
		if (isset($search['bank_code_list']) && is_array($search['bank_code_list']) && count($search['bank_code_list']) > 0) {
			$this->db->where_in('bank_code', $search['bank_code_list']);
		}
        $query = $this->db->get('bank');
		$results = $query->result_array();
		foreach ($results as $index => $result){
			if((isset($search['status_withdraw']) && $search['status_withdraw'] == "1") || (isset($search['security_api']) && $search['security_api'] === true)){

			}else{
				$results[$index]['api_token_1'] = "";
				$results[$index]['api_token_2'] = "";
				$results[$index]['api_token_3'] = "";
			}
		}
        return $results;
    }
    public function bank_find($search = [])
    {
		$security_api = "";
		if((isset($search['status_withdraw']) && $search['status_withdraw'] == "1") || (isset($search['security_api']) && $search['security_api'] === true)){
			$security_api = "api_token_1,
			api_token_2,
			api_token_3,";
		}
        $this->db->select('
          id,
		  bank_code,
		  bank_name,
		  username,
		  '.$security_api.'
		  password,
		  account_name,
		  bank_number,
		  promptpay_number,
		  promptpay_status,
		  status,
		  deleted,
		  balance,
		  status_withdraw,
		  start_time_can_not_deposit,
		  end_time_can_not_deposit,
		  message_can_not_deposit,
		  max_amount_withdraw_auto,
		  updated_at,
		  chk_cron_login,
		  error_message,
		  api_type,
		  auto_transfer,
		  auto_min_amount_transfer,
		  auto_transfer_bank_code,
		  auto_transfer_bank_acc_name,
		  auto_transfer_bank_number
         ');
        if (isset($search['id'])) {
            $this->db->where('bank.id', $search['id']);
        }
		if (isset($search['status'])) {
			$this->db->where('status', $search['status']);
		}
		if (isset($search['status_withdraw'])) {
			$this->db->where('status_withdraw', $search['status_withdraw']);
		}
		$this->db->order_by('id', 'RANDOM()');
		$this->db->where('deleted', 0);
        $query = $this->db->get('bank');
        $result = $query->row_array();
        if($result != ""){
			if((isset($search['status_withdraw']) && $search['status_withdraw'] == "1") || (isset($search['security_api']) && $search['security_api'] === true)){

			}else{
				$result['api_token_1'] = "";
				$result['api_token_2'] = "";
				$result['api_token_3'] = "";
			}
		}
        return $result;
    }
}
