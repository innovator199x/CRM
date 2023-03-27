<?php

class Accountslogs_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    public function getButtonAccountLogs($params) {
        if (isset($params['sel_query']) && $params['sel_query'] != '') {
            $sel_str = $params['sel_query'];
        } else {
            $sel_str = "*";
        }

        /*$this->db->select($sel_str);
        $this->db->from("`agency_event_log` AS ael");
        $this->db->join("`staff_accounts` AS sa", "ael.`staff_id` = sa.`StaffID`", "LEFT");
        $this->db->join("`agency` AS a", "ael.`agency_id` = a.`agency_id`", "LEFT");
        $this->db->where("(ael.`contact_type` = 'Phone Call - Accounts'
        OR ael.`contact_type` = 'Email - Accounts'
        OR ael.`contact_type` = 'Other - Accounts')");
        */

        $this->db->select($sel_str);
        $this->db->from("`sales_report` AS ael");
        $this->db->join("`staff_accounts` AS sa", "ael.`staff_id` = sa.`StaffID`", "LEFT");
        $this->db->join("`agency` AS a", "ael.`agency_id` = a.`agency_id`", "LEFT");
        $this->db->join("`main_log_type` AS mlt", "mlt.main_log_type_id = ael.contact_type", "LEFT");
        $this->db->where("(ael.`contact_type` = 5 OR ael.`contact_type` = 11 OR ael.`contact_type` = 14)");

        $filter_str = '';

        // search agency
        if ($params['agency'] != "") {
            $this->db->where("ael.`agency_id` = {$params['agency']}");
        }

        // search staff
        if ($params['staff'] != "") {
            $this->db->where("ael.`staff_id` = {$params['staff']}");
        }


        // date filter
        if ($params['search_date']['from'] != "" && $params['search_date']['to'] != "") {
            $this->db->where("CAST( ael.`date` AS Date )  BETWEEN '{$params['search_date']['from']}' AND '{$params['search_date']['to']}'");
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
            //echo $sql;
            echo $this->db->last_query();
        }

        if ($params['return_count'] == 1) {
            return $query->num_rows();
        } else {
            return $query;
        }
    }

    public function getAgencyInfo($agency_id) {
        if ((int) $agency_id === 0) {
            throw new Exception("Invalid Agency Selection");
        }
        $this->db->select('a.agency_name, a.address_1,a.address_2,a.address_3,a.state,a.postcode,c.country,a.country_id,a.franchise_groups_id');
        $this->db->from('agency a');
        $this->db->join('countries c', 'c.country_id=a.country_id', 'LEFT');
        $this->db->where('a.agency_id', $agency_id);
        $query = $this->db->get()->row_array();
        return $query;
    }

    public function getAgencies() {

        $this->db->select("DISTINCT (p.`agency_id`), a.`agency_name`, a.`status`");
        $this->db->from('jobs j');
        $this->db->join('property p', "j.`property_id` = p.`property_id` ", "LEFT");
        $this->db->join('agency a', " p.`agency_id` = a.`agency_id`  ", "LEFT");
        $this->db->where("j.id>0");
        $this->db->where("j.invoice_amount>0");
        $this->db->where("j.invoice_balance>0");
        $this->db->where("j.status='Completed'");
        $financial_year = $this->config->item('accounts_financial_year');
        $this->db->where("AND `j`.`invoice_balance` >0
                AND `j`.`status` = 'Completed'
                AND a.`status` != 'target'
                AND (
                        j.`date` >= '$financial_year' OR
                        j.`unpaid` = 1	
                )");
        $this->db->order_by("a.agency_name", "ASC");
        return $this->db->get();
    }

}
