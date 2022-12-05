<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Setting_model extends CI_Model
{
    public function web_setting_find($search = [])
    {
        $this->db->select('
         *
         ');
        $query = $this->db->get('web_setting');
        return $query->row_array();
    }
    public function web_setting_update($data)
    {
        $this->db->where('id', $data['id']);
        $this->db->update('web_setting', $data);
    }
    public function setting_find($search = [])
    {
        $this->db->select('
         *
        ');
        if (isset($search['name'])) {
            $this->db->where('name', $search['name']);
        }
        $query = $this->db->get('web_setting');
        return $query->row_array();
    }

	public function setting_for_wheel_list($search = [])
	{
		$this->db->select('
         *
        ');
		$this->db->like('name', "wheel_name_",'right');
		$query = $this->db->get('web_setting');
		$results_name =$query->result_array();
		$results_name_key = [];
		foreach($results_name as $name){
			$results_name_key[$name['name']] = $name;
		}

		$this->db->select('
         *
        ');
		$this->db->like('name', "wheel_credit_",'right');
		$query = $this->db->get('web_setting');
		$results_credit =$query->result_array();
		$results_credit_key = [];
		foreach($results_credit as $credit){
			$results_credit_key[$credit['name']] = $credit;
		}

		$this->db->select('
         *
        ');
		$this->db->like('name', "wheel_percent_",'right');
		$query = $this->db->get('web_setting');
		$results_percent =$query->result_array();
		usort($results_percent, function($a, $b) {
			return $a['value'] <= $b['value'];
		});

		$this->db->select('
         *
        ');
		$this->db->like('name', "wheel_status_",'right');
		$query = $this->db->get('web_setting');
		$results_status =$query->result_array();
		$results_status_key = [];
		foreach($results_status as $status){
			$results_status_key[$status['name']] = $status;
		}

		$this->db->select('
         *
        ');
		$this->db->like('name', "wheel_color_",'right');
		$query = $this->db->get('web_setting');
		$results_color =$query->result_array();
		$results_color_key = [];
		foreach($results_color as $color){
			$results_color_key[$color['name']] = $color;
		}

		$results = [];
		foreach($results_percent as $percent){
			$data = [];
			$name = explode("_",$percent['name']);
			if(array_key_exists("wheel_status_".$name[2],$results_status_key) && $results_status_key["wheel_status_".$name[2]]['value'] == "1"){
				$data['id'] = $name[2];
				if(isset($search['all']) && $search['all']){
					$data['status'] = "1";
					$data['percent'] = is_numeric($percent['value']) ? $percent['value'] : 100;
				}
				if(array_key_exists("wheel_credit_".$name[2],$results_credit_key)){
					$data['credit'] = is_numeric($results_credit_key["wheel_credit_".$name[2]]['value']) ? $results_credit_key["wheel_credit_".$name[2]]['value'] : 0;
				}else{
					$data['credit'] = 0;
				}
				if(array_key_exists("wheel_name_".$name[2],$results_name_key)){
					$data['name'] = empty($results_name_key["wheel_name_".$name[2]]['value']) ? "ไม่ได้รางวัล"  : $results_name_key["wheel_name_".$name[2]]['value'];
				}else{
					$data['name'] = "ไม่ได้รางวัล";
				}
				if(array_key_exists("wheel_color_".$name[2],$results_color_key)){
					$data['color'] = empty($results_color_key["wheel_color_".$name[2]]['value']) ? "#ffffff"  : $results_color_key["wheel_color_".$name[2]]['value'];
				}else{
					$data['color'] = "#ffffff";
				}
				$results[] = $data;
			}

		}
		if(isset($search['sort_by']) && $search['sort_by']= "name"){
			usort($results, function($a, $b) {
				return $a['id'] <= $b['id'];
			});
		}

		return $results;
	}
}
