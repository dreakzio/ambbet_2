<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Member_model extends CI_Model
{
    public function member_find($search = [])
    {
        $this->db->select('
        id,
        account_id,
        username,
        password
        ');
        if (isset($search['username'])) {
            $this->db->where('username', $search['username']);
        }
        if (isset($search['account_id'])) {
            $this->db->where('account_id', $search['account_id']);
        }
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get('account_agent');
        return $query->row_array();
    }
    public function member_create($data = [])
    {
        $this->db->insert('account_agent', $data);
        $id = $this->db->insert_id();
        return $id;
    }
    public function member_available_count($search = [])
    {
    	$this->db->select('
    		count(1) as cnt_row
    	');
        $this->db->where('account_id', 0);
        $query = $this->db->get('account_agent');
		$cnt_row =  $query->row_array();
		return $cnt_row != "" && isset($cnt_row['cnt_row']) && is_numeric($cnt_row['cnt_row']) ? (int)$cnt_row['cnt_row'] : 0;
    }
    public function member_update($data)
    {
        $this->db->where('id', $data['id']);
        $this->db->update('account_agent', $data);
    }
    public function member_list()
    {
        $this->db->select('
        id,
        account_id,
        username
        ');
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get('account_agent');
        return $query->result_array();
    }

	public function member_max_id()
	{
		$this->db->select_max('username');
		$query = $this->db->get('account_agent');
		$r=$query->row_array();
		return $r;
	}


    public function account_agent_max_id()
	{
		$this->db->select_max('id');
		$query = $this->db->get('account_agent');
		$r=$query->row_array();
		return $r;
    }
}
