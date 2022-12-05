<?php
defined('BASEPATH') or exit('No direct script access allowed');

class New_model extends CI_Model
{
	public function new_list()
	{
		$this->db->select('
            new.id,
            new.name,
			new.status,
			new.status_alert,
			new.url,
			new.status_image_alert,
			new.seq,
			new.image,          
        	IF(new.image!="", CONCAT("'.site_url().'assets/images/new/'.'",new.image), null) as image_url
        ');
		$this->db->order_by('new.seq', 'ASC');
		$this->db->where('new.deleted', 0);
		if (isset($search['id'])) {
			$this->db->where('new.id', $search['id']);
		}
		if (isset($search['status'])) {
			$this->db->where('new.status', $search['status']);
		}
		$query = $this->db->get('new');
		return $query->result_array();
	}

	public function new_create($data = [])
	{
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
		$this->db->insert('new', $data);
		$id = $this->db->insert_id();
		return $id;
	}
	public function new_update($data)
	{
		date_default_timezone_set("Asia/Bangkok"); //set เขตเวลา
		$data['updated_at'] = isset($data['updated_at']) ? $data['updated_at'] : date('Y-m-d H:i:s');
		$this->db->where('id', $data['id']);
		$this->db->update('new', $data);
	}
	public function new_find($search = [])
	{
		$this->db->select('
			new.id,
            new.name,
			new.status,
			new.status_alert,
			new.url,
			new.status_image_alert,
			new.seq,
			new.image,          
        	IF(new.image!="", CONCAT("'.site_url().'assets/images/new/'.'",new.image), null) as image_url
        ');
		$this->db->order_by('new.seq', 'ASC');
		$this->db->where('new.deleted', 0);
		if (isset($search['id'])) {
			$this->db->where('new.id', $search['id']);
		}
		if (isset($search['status'])) {
			$this->db->where('new.status', $search['status']);
		}
		$query = $this->db->get('new');
		return $query->row_array();
	}

}
