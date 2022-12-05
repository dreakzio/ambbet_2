<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Feature_status_model extends CI_Model
{
    public function setting_find($search = [])
    {
        $this->db->select('
         *
        ');
        if (isset($search['name'])) {
            $this->db->where('name', $search['name']);
        }
        $query = $this->db->get('feature_status');
        return $query->row_array();
    }
}
