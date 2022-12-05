<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Bank_register_ignore_model extends CI_Model
{

    public function bank_register_ignore_list($search = [])
    {
        $this->db->select('
          id,
		  status,
		  name,
		  code,
		  created_at,
		  updated_at
        ');
        $this->db->order_by('id', 'DESC');
		if (isset($search['id'])) {
			$this->db->where('bank_register_ignore.id', $search['id']);
		}
		if (isset($search['status'])) {
			$this->db->where('bank_register_ignore.status', $search['status']);
		}
		if (isset($search['code'])) {
			$this->db->where('bank_register_ignore.code', $search['code']);
		}
        $query = $this->db->get('bank_register_ignore');
        return $query->result_array();
    }
}
