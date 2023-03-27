<?php

class Staff_accounts_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    // wip 
    
    public function get_staff_accounts($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`staff_accounts` AS sa');
        //$this->db->join('`alarm_job_type` AS ajt', 'j.`service` = ajt.`id`', 'left');	

        if (isset($params['joins']) && count($params['joins'])) {
            foreach ($params['joins'] as $join) {
                if ($join === 'country_access') {
                    $this->db->join('`country_access` AS ca', 'sa.`StaffID` = ca.`staff_accounts_id`', 'INNER');
                }
            }
        }

        // filters
        if ( $params['staff_id'] > 0 ) {
            $this->db->where('sa.`StaffID`', $params['staff_id']);
        }
        if ( $params['class_id'] > 0 ) {
            $this->db->where('sa.`ClassID`', $params['class_id']);
        }
        if ( $params['email'] != '' ) {
            $this->db->where('sa.`Email`', $params['email']);
        }
        if ( $params['password'] != '' ) {
            $this->db->where('sa.`Password`', $params['password']);
        }
        if ( is_numeric($params['active']) ) {
            $this->db->where('sa.`active`', $params['active']);
        }
        if ( is_numeric($params['deleted']) ) {
            $this->db->where('sa.`Deleted`', $params['deleted']);
        }
        if ( $params['country_id'] > 0 ) {
            $this->db->where('ca.`country_id`', $params['country_id']);
        }

        // search
        if (isset($params['search']) && $params['search'] != '') {
            $search_filter = "CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode))";
            $this->db->like($search_filter, $params['search']);
        }

        // custom filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
        }

        // sort
        if (isset($params['sort_list'])) {
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }

        // limit
        if (isset($params['limit']) && $params['limit'] > 0) {
            $this->db->limit($params['limit'], $params['offset']);
        }


        $query = $this->db->get();
        if (isset($params['display_query']) && $params['display_query'] == 1) {
            echo $this->db->last_query();
        }

        return $query;
    }

    public function get_staff_accounts_with_country_access($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`staff_accounts` AS sa');
        if (isset($params['joins']) && count($params['joins'])) {
            foreach ($params['joins'] as $join) {
                if ($join === 'country_access') {
                    $this->db->join('`country_access` AS ca', 'sa.`StaffID` = ca.`staff_accounts_id`', 'LEFT');
                }
            }
        }
        //$this->db->join('`alarm_job_type` AS ajt', 'j.`service` = ajt.`id`', 'left');	
        // filters
        if (isset($params['staff_id'])) {
            $this->db->where('sa.`StaffID`', $params['staff_id']);
        }
        if (isset($params['class_id'])) {
            $this->db->where('sa.`ClassID`', $params['class_id']);
        }
        if (isset($params['email'])) {
            $this->db->where('sa.`Email`', $params['email']);
        }
        if (isset($params['password'])) {
            $this->db->where('sa.`Password`', $params['password']);
        }
        if (isset($params['active'])) {
            $this->db->where('sa.`active`', $params['active']);
        }
        if (isset($params['deleted'])) {
            $this->db->where('sa.`Deleted`', $params['deleted']);
        }

        // search
        if (isset($params['search']) && $params['search'] != '') {
            $search_filter = "CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode))";
            $this->db->like($search_filter, $params['search']);
        }

        // custom filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
        }

        // sort
        if (isset($params['sort_list'])) {
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }

        // limit
        if (isset($params['limit']) && $params['limit'] > 0) {
            $this->db->limit($params['limit'], $params['offset']);
        }


        $query = $this->db->get();
        if (isset($params['display_query']) && $params['display_query'] == 1) {
            echo $this->db->last_query();
        }

        return $query;
    }

    public function check_if_email_exist($email) {

        // get email
        $params = array(
            'sel_query' => 'COUNT(sa.StaffID) AS sa_count',
            'email' => $email,
            'display_query' => 0
        );

        // get user details
        $sql = $this->get_staff_accounts($params);
        $row = $sql->row();

        if ($row->sa_count > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function get_staff_accounts_details($id)
    {
        $params = array(
            'sel_query' => 'sa.FirstName, sa.email',
            'staff_id' => $id,
            'display_query' => 0
        );

        $query = $this->get_staff_accounts($params);
        $row = $query->row();
        
        return $row;
    }

}

?>