<?php

class Statements_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    public function getButtonStatements($params) {
// filters
        $filter_arr = array();

        if (isset($params['custom_select']) && $params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        } else if (isset($params['return_count']) && $params['return_count'] == 1) {
            $sel_str = " COUNT(j.`id`) AS jcount ";
        } else {
            $sel_str = " 
				j.`id` AS jid,
				j.`status` AS jstatus,
				j.`service` AS jservice,
				j.`created` AS jcreated,
				j.`date` AS jdate,
				j.`comments` AS j_comments,
                                j.invoice_amount,
                                j.invoice_payments,
                                j.invoice_refunds,
                                j.invoice_credits,
                                j.invoice_balance,
                                j.property_id,
				
				p.`address_1` AS p_address_1, 
				p.`address_2` AS p_address_2, 
				p.`address_3` AS p_address_3,
				p.`state` AS p_state,
				p.`postcode` AS p_postcode,
				p.`comments` AS p_comments,
				p.`compass_index_num`,
				
				a.`agency_id` AS a_id,
				a.`phone` AS a_phone,
				a.`address_1` AS a_address_1, 
				a.`address_2` AS a_address_2, 
				a.`address_3` AS a_address_3,
				a.`state` AS a_state,
				a.`postcode` AS a_postcode,
				a.`account_emails`,
				a.`agency_emails`,
                a.`franchise_groups_id`,
                a.`statements_agency_comments`
			";
        }

        if (isset($params['agency_id']) && (int) $params['agency_id'] !== 0) {
            $filter_arr[] = "AND p.`agency_id` = {$params['agency_id']}";
        }

        if (isset($params['job_date']) && $params['job_date'] != "") {
            $filter_arr[] = "AND j.`date` = '{$params['job_date']}'";
        }

        if (isset($params['filterDate']) && $params['filterDate'] != '') {
            if ($params['filterDate']['from'] != "" && $params['filterDate']['to'] != "") {
                $filter_arr[] = " AND ( j.`date` BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}' ) ";
            }
        }

        if (isset($params['phrase']) && $params['phrase'] != '') {
            $filter_arr[] = "
			AND (
				(CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$params['phrase']}%') OR
				(a.`agency_name` LIKE '%{$params['phrase']}%')
			 )
			 ";
        }
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        // combine all filters
        $filter_str = " j.`id` > 0 " . implode(" ", $filter_arr) . " " . $custom_filter_str;


        //accounts filter query > moved from controler to main query model so that no need to update each function controller (by:gherx)
        $financial_year = $this->config->item('accounts_financial_year');
        $custom_filter_accounts = "
        `j`.`invoice_balance` !=0
        AND `j`.`status` = 'Completed'
        AND a.`status` != 'target'
        AND (
                j.`date` >= '$financial_year' OR
                j.`unpaid` = 1	
        )
        ";
        //accounts filter query > moved end


        $this->db->select($sel_str);
        $this->db->from('jobs j');
        $this->db->join("property p", "j.property_id=p.property_id", 'LEFT');
        $this->db->join("agency a", "p.agency_id=a.agency_id", 'LEFT');
        $this->db->where($custom_filter_accounts);
        $this->db->where($filter_str);
         
// sort
        if (isset($params['sort_list']) && $params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }

        // GROUP BY
        if (isset($params['group_by']) && $params['group_by'] != '') {
            $this->db->group_by($params['group_by']);
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




//        if (isset($params['sel_query'])) {
//            $sel_query = $params['sel_query'];
//        } else {
//            $sel_query = '*';
//        }
//        if ($params['distinct_sql'] != "") {
//            $this->db->distinct();
//        }
//
//        $this->db->select($sel_query);
//        $this->db->from('`agency` a');
//        $this->db->join('`agency_regions` as ar', 'ar.agency_region_id=a.agency_region_id', 'LEFT');
//        $this->db->join('`staff_accounts` as sa', 'a.salesrep = sa.StaffID', 'LEFT');
//        $where = "a.`status` = 'active' AND a.`franchise_groups_id` =" . $params['franchise_groups_id'];
//        if (isset($params['state']) && $params['state'] != "") {
//            $where .= " AND LOWER(a.state) LIKE '%{$params['state']}%' ";
//        }
//
//        // sales rep
//        if (isset($params['salesrep']) && $params['salesrep'] != "") {
//            $where .= " AND (CONCAT_WS(' ',LOWER(sa.FirstName), LOWER(sa.LastName)) LIKE '%{$params['salesrep']}%') ";
//        }
//
//        // region
//        if (isset($params['region']) && $params['region'] != "") {
//            $where .= " AND (LOWER(ar.agency_region_name) LIKE '%{$params['region']}%') ";
//        }
//
//        // phrase
//        if (isset($params['phrase']) && $params['phrase'] != "") {
//            $where .= " AND ( CONCAT_WS( ' ', LOWER(a.agency_name), LOWER(a.contact_first_name), LOWER(a.contact_last_name), LOWER(sa.FirstName), LOWER(sa.LastName), LOWER(a.state), LOWER(ar.agency_region_name) ) LIKE '%{$params['phrase']}%') ";
//        }
//        $this->db->where($where);
//
//
//
//        // sort
//        if ($params['sort_list'] != '') {
//
//            $sort_str_arr = array();
//            foreach ($params['sort_list'] as $sort_arr) {
//                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
//                    //$sort_str_arr[] = $sort_arr['order_by'] . ' ' . $sort_arr['sort'];
//                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
//                }
//            }
//            $sort_str_imp = implode(", ", $sort_str_arr);
//            // $sort_str = "ORDER BY {$sort_str_imp}";
//            //$this->db->order_by( $sort_str_imp );
//        }
//
//
//        // GROUP BY
//        if ($params['group_by'] != '') {
//            //$group_by_str = "GROUP BY {$params['group_by']}";
//            $this->db->group_by($params['group_by']);
//        }
//
//
//        // paginate
//        if ($params['paginate'] != "") {
//            $this->db->limit($params['paginate']['limit'], $params['paginate']['offset']);
//        }
//
//        $query = $this->db->get();
//        if ($params['echo_query'] == 1) {
//            //echo $sql;
//            echo $this->db->last_query();
//        }
//
//        if ($params['return_count'] == 1) {
//            // $j_sql = mysql_query($sql);
//            // $row = mysql_fetch_array($j_sql);
//            // return $row['jcount'];
//
//            return $query->num_rows();
//        } else {
//            return $query;
//        }
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
        $financial_year=$this->config->item('accounts_financial_year');
        $this->db->where("`j`.`invoice_balance` >0
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
