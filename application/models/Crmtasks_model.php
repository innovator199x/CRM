<?php

class Crmtasks_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    function getButtonCrmTasks($params) {
        if ($params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct_sql'] != "") {

            $sel_str = " DISTINCT {$params['distinct_sql']} ";
        } else {
            $sel_str = " 
			*
		";
        }

        $this->db->select($sel_str);
        $this->db->from('`crm_tasks` AS ct');
        $this->db->join('`staff_accounts` AS rb', 'ct.`requested_by` = rb.`StaffID`', 'LEFT');
        $this->db->join('`crm_task_status` AS cts', 'ct.`status` = cts.`id`', 'LEFT');
        $this->db->join('`crm_task_help_topic` AS ctht', 'ct.`help_topic` = ctht.`id`', 'LEFT');
        $this->db->join('`crm_task_details_devs` AS ctdd', 'ct.`crm_task_id` = ctdd.ticket_id', 'LEFT');

        // custom joins
        if (isset($params['custom_joins']) && $params['custom_joins'] != '') {
            $this->db->join($params['custom_joins']['join_table'], $params['custom_joins']['join_on'], $params['custom_joins']['join_type']);
        }

        // multiple custom joins
        if( count($params['custom_joins_arr']) > 0 ){

            foreach( $params['custom_joins_arr'] as $custom_joins ){
                $this->db->join($custom_joins['join_table'], $custom_joins['join_on'], $custom_joins['join_type']);
            }            

        }

        // filters
        $this->db->where("ct.`crm_task_id` > 0");
        if ($params['active'] != "") {
            $this->db->where("ct.`active` = {$params['active']}");
        }

        if ($params['ct_id'] != "") {
            $this->db->where("ct.`crm_task_id` = {$params['ct_id']}");
        }

        if ($params['date'] != "") {
            $this->db->where("CAST( ct.`date_created` AS Date ) = '{$params['date']}'");
        }

        if ($params['user'] != "") {
            $this->db->where("ct.`requested_by` = {$params['user']}");
        }

        if ($params['assigned'] != "") {
            $this->db->where("ctdd.`dev_id` = {$params['assigned']}");
        }

        if ($params['status'] != "") {
            $this->db->where("ct.`status` = {$params['status']}");
        }

        if ( $params['help_topic'] > 0 ) {
            $this->db->where("ct.`help_topic` = {$params['help_topic']}");
        }

        if ( $params['ticket_priority'] > 0 ) {
            $this->db->where("ct.`ticket_priority` = {$params['ticket_priority']}");
        }

        //custom query
        if ($params['custom_filter'] != '') {
            $this->db->where($params['custom_filter']);
        }

        // custom filter
        if (isset($params['custom_where_arr'])) {
            foreach ($params['custom_where_arr'] as $index => $custom_where) {
                if ($custom_where != '') {
                    $this->db->where($custom_where);
                }
            }
        }

        // group by
        if (isset($params['group_by']) && $params['group_by'] != '') {
            $this->db->group_by($params['group_by']);
        }

        if (isset($params['sort_list']) && $params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }

        // paginate
        if (isset($params['paginate']) && $params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $this->db->limit($params['paginate']['limit'], $params['paginate']['offset']);
            }
        }
        $query = $this->db->get();
        if ($params['echo_query'] == 1) {
            echo $this->db->last_query();
        }

        if ($params['return_count'] == 1) {
            return $query->row()->jcount;
        } else {
            return $query;
        }
    }

    public function update_crmtask($params, $ct_id) {
        $this->db->where("`crm_task_id` = {$ct_id}");
        if (isset($params['action']) && $params['action'] === 'delete') {
            $this->db->delete("`crm_tasks`");
        } else {
            $this->db->update("`crm_tasks`", $params);
        }

        $affected_rows = $this->db->affected_rows();
        return $affected_rows;
    }
     public function add_crmtask($params) {
        $this->db->insert('crm_tasks', $params);
    }

    function getComplaints($params) {
        if ($params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct_sql'] != "") {
            $sel_str = " DISTINCT {$params['distinct_sql']} ";
        } else {
            $sel_str = " 
			*
		";
        }

        $this->db->select($sel_str);
        $this->db->from('`complaints` AS comp');
        $this->db->join('`staff_accounts` AS rb', 'comp.`requested_by` = rb.`StaffID`', 'LEFT');
        $this->db->join('`complaints_status` AS cs', 'comp.`status` = cs.`id`', 'LEFT');
        $this->db->join('`complaints_topic` AS ct', 'comp.`comp_topic` = ct.`comp_topic_id`', 'LEFT');
        // $this->db->join('`crm_task_details_devs` AS ctdd', 'ct.`crm_task_id` = ctdd.ticket_id', 'LEFT');

        // custom joins
        if (isset($params['custom_joins']) && $params['custom_joins'] != '') {
            $this->db->join($params['custom_joins']['join_table'], $params['custom_joins']['join_on'], $params['custom_joins']['join_type']);
        }

        // multiple custom joins
        if( count($params['custom_joins_arr']) > 0 ){

            foreach( $params['custom_joins_arr'] as $custom_joins ){
                $this->db->join($custom_joins['join_table'], $custom_joins['join_on'], $custom_joins['join_type']);
            }            

        }

        // filters
        $this->db->where("comp.`comp_id` > 0");
        if ($params['active'] != "") {
            $this->db->where("ct.`active` = {$params['active']}");
        }

        if ($params['ct_id'] != "") {
            $this->db->where("ct.`crm_task_id` = {$params['ct_id']}");
        }

        if ($params['date'] != "") {
            $this->db->where("CAST( ct.`date_created` AS Date ) = '{$params['date']}'");
        }

        if ($params['user'] != "") {
            $this->db->where("comp.`requested_by` = {$params['user']}");
        }

        if ($params['assigned'] != "") {
            $this->db->where("ctdd.`dev_id` = {$params['assigned']}");
        }

        if ($params['status'] != "") {
            $this->db->where("comp.`status` = {$params['status']}");
        }

        if ( $params['help_topic'] > 0 ) {
            $this->db->where("ct.`help_topic` = {$params['help_topic']}");
        }

        if ( $params['ticket_priority'] > 0 ) {
            $this->db->where("comp.`ticket_priority` = {$params['ticket_priority']}");
        }

        //custom query
        if ($params['custom_filter'] != '') {
            $this->db->where($params['custom_filter']);
        }

        // custom filter
        if (isset($params['custom_where_arr'])) {
            foreach ($params['custom_where_arr'] as $index => $custom_where) {
                if ($custom_where != '') {
                    $this->db->where($custom_where);
                }
            }
        }

        // group by
        if (isset($params['group_by']) && $params['group_by'] != '') {
            $this->db->group_by($params['group_by']);
        }

        if (isset($params['sort_list']) && $params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }

        // paginate
        if (isset($params['paginate']) && $params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $this->db->limit($params['paginate']['limit'], $params['paginate']['offset']);
            }
        }
        $query = $this->db->get();
        if ($params['echo_query'] == 1) {
            echo $this->db->last_query();
        }

        if ($params['return_count'] == 1) {
            return $query->row()->jcount;
        } else {
            return $query;
        }
    }

}
