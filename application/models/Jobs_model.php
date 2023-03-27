<?php

class Jobs_model extends CI_Model {

    public function __construct() {
        //$this->load->database();
        $this->load->model('properties_model');
        $this->load->model('inc/functions_model');
        $this->load->model('sms_model');
    }

    public function get_jobs($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`jobs` AS j');
        $this->db->join('`property` AS p', 'j.`property_id` = p.`property_id`', 'left');
        $this->db->join('`agency` AS a', ' p.`agency_id` = a.`agency_id`', 'left');

        // set joins
        if ($params['join_table'] > 0) {

            foreach ($params['join_table'] as $join_table) {

                if ($join_table == 'job_reason') {
                    $this->db->join('`job_reason` AS jr', 'j.`job_reason_id` = jr.`job_reason_id`', 'left');
                }
                if ($join_table == 'tech') {
                    $this->db->join('`staff_accounts` AS t', 'j.`assigned_tech` = t.`StaffID`', 'left');
                }
                if ($join_table == 'alarm_job_type') {
                    $this->db->join('`alarm_job_type` AS ajt', 'j.`service` = ajt.`id`', 'left');
                }
                if ($join_table == 'agency_user_accounts') {
                    $this->db->join('`agency_user_accounts` AS aua', 'p.`pm_id_new` = aua.`agency_user_account_id`', 'left');
                }
                if ($join_table == 'job_type') {
                    $this->db->join('`job_type` AS jt', 'j.`job_type` = jt.`job_type`', 'left');
                }
                if ($join_table == 'maintenance') {
                    $this->db->join('`agency_maintenance` AS am', 'am.`agency_id` = a.`agency_id`', 'left');
                    $this->db->join('`maintenance` AS m', 'm.`maintenance_id` = am.`maintenance_id`', 'left');
                }
                if ($join_table == 'escalate_job_reason') {
                    $this->db->join('`selected_escalate_job_reasons` AS sejr', 'sejr.`job_id` = j.`id`', 'left');
                    $this->db->join('`escalate_job_reasons` AS ejr', 'ejr.`escalate_job_reasons_id` = sejr.`escalate_job_reasons_id`', 'left');
                }
                if ($join_table == 'trust_account_software') {
                    $this->db->join('`trust_account_software` AS tsa', 'a.`trust_account_software` = tsa.`trust_account_software_id`', 'left');                    
                }
                if ($join_table == 'staff_accounts') {
                    $this->db->join('`staff_accounts` AS sa', 'j.`assigned_tech` = sa.`StaffID`', 'left');
                }
                if ($join_table == 'allocated_by_join') {
                    $this->db->join('`staff_accounts` AS alloc_by', 'j.`allocated_by` = alloc_by.`StaffID`', 'left');
                }
                if ($join_table == 'regions') {
                    $this->db->join('`regions` AS r', 'p.`state` = r.`region_state`', 'left');
                }
                if ($join_table == 'property_tenants') {
                    $this->db->join('`property_tenants` AS pt', 'p.`property_id` =  pt.`property_id`', 'inner');
                }
                if ($join_table == 'countries') {
                    $this->db->join('`countries` AS c', 'a.`country_id` =  c.`country_id`', 'left');
                }
                if ($join_table == 'preferred_alarm') {
                    $this->db->join('`alarm_pwr` AS al_p', 'p.`preferred_alarm_id` = al_p.`alarm_pwr_id`', 'left');
                }
                if ($join_table == 'job_markers') {
                    $this->db->join('`job_markers` AS jm', 'j.`id` = jm.`job_id`', 'left');
                }
                if ($join_table == 'alarm') {
                    $this->db->join('alarm AS al', 'j.`id` = al.`job_id`', 'right');
                }
                if ($join_table == 'alarm_pwr') {
                    $this->db->join('`alarm_pwr` AS ap', 'al.`alarm_power_id` = ap.`alarm_pwr_id`', 'left');
                }
                if ($join_table == 'property_subscription') {
                    $this->db->join('`property_subscription` AS ps', 'p.`property_id` = ps.`property_id`', 'left');
                }

                

                // credits
                // pair
                if ($join_table == 'invoice_credits') {
                    $this->db->join('`invoice_credits` AS inv_cred', 'j.`id` = inv_cred.`job_id`', 'inner');
                }
                if ($join_table == 'credit_reason') {
                    $this->db->join('`credit_reason` AS cred_reas', 'inv_cred.`credit_reason` = cred_reas.`credit_reason_id`', 'left');
                }

                // payments
                // pair
                if ($join_table == 'invoice_payments') {
                    $this->db->join('`invoice_payments` AS inv_pay', 'j.`id` = inv_pay.`job_id`', 'inner');
                }
                if ($join_table == 'payment_types') {
                    $this->db->join('`payment_types` AS pay_type', 'inv_pay.`type_of_payment` = pay_type.`payment_type_id`', 'left');
                }

                // API property generic table
                // PMe
                if ($join_table == 'api_property_data_pme') {
                    $this->db->join('`api_property_data` AS apd_pme', '( p.`property_id` = apd_pme.`crm_prop_id` AND apd_pme.`api` = 1 )', 'left');
                }

                // palace
                if ($join_table == 'api_property_data_palace') {
                    $this->db->join('`api_property_data` AS apd_palace', '( p.`property_id` = apd_palace.`crm_prop_id` AND apd_palace.`api` = 4 )', 'left');
                }
                
                // ptree
                if ($join_table == 'api_property_data_ptree') {
                    $this->db->join('`api_property_data` AS apd_ptree', '( p.`property_id` = apd_ptree.`crm_prop_id` AND apd_ptree.`api` = 3 )', 'left');
                }

                if ($join_table == 'agency_priority') {
                    $this->db->join('agency_priority as aght', 'a.agency_id = aght.agency_id', 'left');
                }

                if ($join_table == 'agency_priority_marker_definition') {
                    $this->db->join('agency_priority_marker_definition as apmd', 'aght.priority = apmd.priority', 'left');
                }

            }
        }

        if ($params['job_created'] != '') {
            $job_created = "CAST( j.`created` AS DATE ) = '{$params['job_created']}'";
            $this->db->where($job_created);
        }

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

        // filter
        if (is_numeric($params['job_id'])) {
            $this->db->where('j.`id`', $params['job_id']);
        }
        if (is_numeric($params['property_id'])) {
            $this->db->where('p.`property_id`', $params['property_id']);
        }
        if (is_numeric($params['del_job'])) {
            $this->db->where('j.`del_job`', $params['del_job']);
        }
        //Include NLM properties when searching in the search_results page
        if (isset($params['is_nlm_include'])) {
            $this->db->where('p.`deleted`', $params['p_deleted']);
        } else {
            if (is_numeric($params['p_deleted'])) {
                $this->db->where('p.`deleted`', $params['p_deleted']);
                if($params['p_deleted'] == 0){
                    $this->db->where('( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )');
                }
            }
        }
        if (isset($params['a_status']) && $params['a_status'] != '') {
            $this->db->where('a.`status`', $params['a_status']);
        }
        //exclude job type ONCE Off in UPGRADE DATA DISCREPANCY page
        if (isset($params['exclude_job_type']) && $params['exclude_job_type'] != '') {
            $this->db->where("j.job_type != 'Once-Off'");
        }
        if (isset($params['key_access_details']) && $params['key_access_details'] != '') {
            $this->db->like('j.`key_access_details`', $params['key_access_details']);
        }
        if (isset($params['key_access_required']) && $params['key_access_required'] != '') {
            $this->db->where('j.`key_access_required`', $params['key_access_required']);
        }
        if(isset($params['a_status']) && $params['a_status'] == '' && $params['a_deactivated_ts'] == true){
            $this->db->where_in('a.`status`', array('target', 'deactivated'));
        }
        if (isset($params['a_deactivated_ts']) && $params['a_deactivated_ts'] == true) {
            $this->db->where_not_in('a.`deactivated_ts`', array('', '0000-00-00'));
        }
        if (is_numeric($params['country_id'])) {
            $this->db->where('a.`country_id`', $params['country_id']);
        }
        if (isset($params['out_of_tech_hours'])) {
            $this->db->where('j.`out_of_tech_hours`', $params['out_of_tech_hours']);
        }
        if (isset($params['pt_active'])) {
            $this->db->where('pt.`active`', $params['pt_active']);
        }
        if ( $params['preferred_alarm_id'] > 0 ) {
            $this->db->where('p.`preferred_alarm_id`', $params['preferred_alarm_id']);
        }
        if ( $params['a_filter'] > 0 ) {
            $this->db->where('j.`status`', 'To Be Invoiced');
        }

        // // agency priority filters
        // if ($params['agency_priority_filter'] != '') {
        //     $this->db->where('aght.`priority`', $params['agency_priority_filter']);
        // }

        // agency filters
        if (isset($params['agency_filter']) && $params['agency_filter'] != '') {
            $this->db->where('a.`agency_id`', $params['agency_filter']);
        }

        // Electrician Only(EO)	
        if ( is_numeric($params['is_eo']) ) {
            $this->db->where('j.`is_eo`', $params['is_eo']);
        }

        // multi agency filters
        if (count($params['multi_agency_filter']) > 0 && !empty($params['multi_agency_filter'])) {
            $this->db->where_in('a.agency_id', $params['multi_agency_filter']);
        }

        //agency deleted filter > default exclude eagency
        if ( $params['a_deleted'] && is_numeric($params['a_deleted']) ) {
            $this->db->where('a.`deleted`', $params['a_deleted']);
        }else if( $params['a_deleted'] == 'no filter' ){
            // empty, dont filter agency deleted field
        }else{
            $this->db->where('a.`deleted`', 0);
        }

        // Job Type filters
        if (isset($params['job_type']) && $params['job_type'] != '') {

            if ($params['job_type'] == 'cot & lr') {
                $cot_lr_combo = "( j.job_type = 'Change of Tenancy' OR j.job_type = 'Lease Renewal' )";
                $this->db->where($cot_lr_combo);
            } else {
                $this->db->where('j.`job_type`', $params['job_type']);
            }
        }

        // Job Status filter
        if (isset($params['job_status']) && $params['job_status'] != '') {

            // amend for covid-19
            if( $params['job_status'] == 'On Hold' ){
                $this->db->where("j.`status` IN('On Hold','On Hold - COVID')");
            }else if( $params['job_status'] == 'not_completed_cancelled' ){
                $this->db->where("j.`status` NOT IN('Completed','Cancelled')");
            }else{
                $this->db->where('j.`status`', $params['job_status']);
            }

        }

        if (isset($params['job_status_arr']) && $params['job_status_arr'] != '') {
            $this->db->group_start();
            foreach($params['job_status_arr'] as $v) {

                if ($v == 'completed') {
                    $this->db->where('j.`status`', "Completed");
                }
                if ($v == 'merged_certificates') {
                    $this->db->or_where('j.`status`', "Merged Certificates");
                }
            }
            $this->db->group_end();
        }

        // Service filters
        if (isset($params['service_filter']) && $params['service_filter'] != '') {
            $this->db->where('j.`service`', $params['service_filter']);
        }

        // State filters
        if (isset($params['state_filter']) && $params['state_filter'] != '') {
            $this->db->where('p.`state`', $params['state_filter']);
        }

        // Sales filters
        if (isset($params['is_sales']) && $params['is_sales'] != 0) {
            $this->db->where('p.`is_sales`', 1);
        }

        // Region filters
        if (isset($params['region_filter']) && $params['region_filter'] != '') {
            $this->db->where_in('p.`postcode`', $params['region_filter']);
        }

        // Maintenance filters
        if (isset($params['maintenance_filter']) && $params['maintenance_filter'] != '') {
            $this->db->where_in('m.`maintenance_id`', $params['maintenance_filter']);
        }

        // Maintenance filters
        if (isset($params['tsa_filter']) && $params['tsa_filter'] != '') {
            $this->db->where_in('a.`trust_account_software`', $params['tsa_filter']);
        }

        // Reason filters
        if (isset($params['reason_filter']) && $params['reason_filter'] != '') {
            $this->db->where_in('sejr.`escalate_job_reasons_id`', $params['reason_filter']);
        }

        // Date filters
        if (isset($params['date']) && $params['date'] != '') {
            $this->db->where('j.`date`', $params['date']);
        }

        // Date filters from booked page
        if (isset($params['date_from']) && $params['date_from'] != '' && isset($params['date_to']) && $params['date_to'] != '') {
            //$this->db->where('j.`date`', $params['date']);
            $this->db->where('j.`date` >=', $params['date_from']);
            $this->db->where('j.`date` <=', $params['date_to']);
        }

        // Date filters from booked page
        if (isset($params['date_from']) && $params['date_from'] != '' && $params['date_to'] == '') {
            $this->db->where('j.`date` >=', $params['date_from']);
        }

        // Date filters from booked page
        if (isset($params['date_to']) && $params['date_to'] != '' && $params['date_from'] == '') {
            $this->db->where('j.`date` <=', $params['date_to']);
        }

        //search
        if (isset($params['search']) && $params['search'] != '') {
            $search_filter = "CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode))";
            $this->db->like($search_filter, $params['search']);
        }

        // Office to call status exclude job status is On Hold, Allocate and/or Escalate
        if (isset($params['otc_status']) && $params['otc_status'] != '') {
            $otc_status = "j.status != 'On Hold' && j.status != 'Allocate' && j.status != 'Escalate'";
            $this->db->where($otc_status);
        }

        //search agency
        if (isset($params['search_agency']) && $params['search_agency'] != '') {
            $search_filter = "LOWER(a.agency_name)";
            $this->db->like($search_filter, $params['search_agency']);
        }

        // postcodes
        if (isset($params['postcodes']) && $params['postcodes'] != '') {
            $this->db->where("p.`postcode` IN ( {$params['postcodes']} )");
        }

        if (isset($params['cancelled_date']) && $params['cancelled_date'] != '') {
            $this->db->where('j.`cancelled_date`', $params['cancelled_date']);
        }

        //urgent
        if ($params['is_urgent'] && !empty($params['is_urgent'])) {
            $this->db->where('j.`urgent_job`', $params['is_urgent']);
        }

        // updated to 240v rebook marker
        if ( $params['updated_to_240v_rebook'] == 1 ) {
            $this->db->where("( j.`job_type` = '240v Rebook' OR jm.`job_type_change` = 1 )");
        }

        // credit reason
        if ($params['credit_reason_id'] && !empty($params['credit_reason_id'])) {
            $this->db->where('inv_cred.`credit_reason`', $params['credit_reason_id']);
        }

        // payment type
        if ($params['type_of_payment'] && !empty($params['type_of_payment'])) {
            $this->db->where('inv_pay.`type_of_payment`', $params['type_of_payment']);
        }

        // alarm discarded
        if ( is_numeric($params['ts_discarded']) ) {
            $this->db->where('al.`ts_discarded`', $params['ts_discarded']);
        }

        // multiple job ID filter
        if ( count($params['job_id_arr']) > 0 ) {
            $this->db->where_in("al.`job_id`", $params['job_id_arr']);
        }

        //is_sales property filter
        if ( is_numeric($params['is_sales']) && $params['is_sales'] == 1 ) {
            $this->db->where('p.`is_sales`', $params['is_sales']);
            $this->db->where('j.`job_type`', 'IC Upgrade');
        }

        // custom filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
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

        // having
        if (isset($params['having']) && $params['having'] != '') {
            $this->db->having($params['having']);
        }

        
        //tech search (by gherx)
        if (isset($params['tech_filter']) && $params['tech_filter'] != '') {
            $this->db->where('j.`assigned_tech`', $params['tech_filter']);
        }

        // eclude job ids
        if (isset($params['exclude_jobs']) && $params['exclude_jobs'] != '') {
            $this->db->where_not_in('j.`id`', $params['exclude_jobs']);
        }

        // sort
        if (isset($params['sort_list'])) {
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }

        // custom filter
        if (isset($params['custom_sort'])) {
            $this->db->order_by($params['custom_sort']);
        }

        // limit
        if (isset($params['limit']) && $params['limit'] > 0) {
            $this->db->limit($params['limit'], $params['offset']);
        }

        $query = $this->db->get();
        if (isset($params['display_query']) && $params['display_query'] == 1) {
            echo $this->db->last_query();
        }

        //echo $query;
        //exit();

        /*
        if (isset($params['date']) && $params['date'] != '') {
            echo "TEST";
            echo $query;
            exit();
        }
        */
        //echo $query;
        //exit();

        return $query;
    }

    public function get_jobs_v2($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`jobs` AS j');

        // property join
        $append_join_str = [];
        if (is_numeric($params['property_id'])) {
            $append_join_str[] = "AND p.`property_id` = {$params['property_id']}";
        }
        // State filters
        if (isset($params['state_filter']) && $params['state_filter'] != '') {
            $append_join_str[] = "AND p.`state` = '{$params['state_filter']}'";
        }
        // Region filters
        if (isset($params['region_filter']) && $params['region_filter'] != '') {
            $append_join_str[] = "AND p.`postcode` IN ({$params['region_filter']})";
        }
        // postcodes filters
        if (isset($params['postcodes']) && $params['postcodes'] != '') {
            $append_join_str[] = "AND p.`postcode` IN ({$params['postcodes']})";
        }
        if (is_numeric($params['p_deleted'])) {
            $append_join_str[] = "AND p.`deleted` = {$params['p_deleted']}";
            if($params['p_deleted'] == 0){
                $append_join_str[] = "AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )";
            }
        }
        $append_join_imp = implode(" ", $append_join_str);
        $this->db->join('`property` AS p', "j.`property_id` = p.`property_id` {$append_join_imp}", 'inner');


        // agency join
        $append_join_str = [];
        if (isset($params['a_status']) && $params['a_status'] != '') {
            $append_join_str[] = "AND a.`status` = '{$params['a_status']}'";
        }
        if (is_numeric($params['country_id'])) {
            $append_join_str[] = "AND a.`country_id` = {$params['country_id']}";
        }
        // agency filters
        if (isset($params['agency_filter']) && $params['agency_filter'] != '') {
            $append_join_str[] = "AND a.`agency_id` = {$params['agency_filter']}";
        }
        // Maintenance filters
        if (isset($params['tsa_filter']) && $params['tsa_filter'] != '') {
            $append_join_str[] = "AND a.`trust_account_software` = {$params['tsa_filter']}";
        }
        //search agency
        if (isset($params['search_agency']) && $params['search_agency'] != '') {
            $append_join_str[] = "AND LOWER(a.agency_name) = '{$params['search_agency']}'";
        }
        $append_join_imp = implode(" ", $append_join_str);
        $this->db->join('`agency` AS a', "p.`agency_id` = a.`agency_id` {$append_join_imp}", 'inner');
        $this->db->join('`agency_priority` AS aght', 'a.`agency_id` = aght.`agency_id`', 'inner');

        // set joins
        if ($params['join_table'] > 0) {

            foreach ($params['join_table'] as $join_table) {

                if ($join_table == 'job_reason') {
                    $this->db->join('`job_reason` AS jr', 'j.`job_reason_id` = jr.`job_reason_id`', 'inner');
                }
                if ($join_table == 'techs') {
                    $this->db->join('`techs` AS t', 'j.`tech_id` = t.`id`', 'inner');
                }
                if ($join_table == 'alarm_job_type') {
                    $this->db->join('`alarm_job_type` AS ajt', 'j.`service` = ajt.`id`', 'inner');
                }
                if ($join_table == 'agency_user_accounts') {
                    $this->db->join('`agency_user_accounts` AS aua', 'p.`pm_id_new` = aua.`agency_user_account_id`', 'inner');
                }
                if ($join_table == 'job_type') {
                    $this->db->join('`job_type` AS jt', 'j.`job_type` = jt.`job_type`', 'inner');
                }
                if ($join_table == 'maintenance') {

                    $this->db->join('`agency_maintenance` AS am', 'am.`agency_id` = a.`agency_id`', 'inner');

                    $append_join_str = [];
                    // Maintenance filters
                    if (isset($params['maintenance_filter']) && $params['maintenance_filter'] != '') {
                        $append_join_str[] = "AND m.`maintenance_id` = {$params['maintenance_filter']}";
                    }
                    $append_join_imp = implode(" ", $append_join_str);
                    $this->db->join('`maintenance` AS m', "m.`maintenance_id` = am.`maintenance_id` {$append_join_imp}", 'inner');
                }
                if ($join_table == 'escalate_job_reason') {

                    $append_join_str = [];
                    // Reason filters
                    if (isset($params['reason_filter']) && $params['reason_filter'] != '') {
                        $append_join_str[] = "AND sejr.`escalate_job_reasons_id` = {$params['reason_filter']}";
                    }
                    $append_join_imp = implode(" ", $append_join_str);
                    $this->db->join('`selected_escalate_job_reasons` AS sejr', "sejr.`job_id` = j.`id` {$append_join_imp}", 'inner');
                    $this->db->join('`escalate_job_reasons` AS ejr', 'ejr.`escalate_job_reasons_id` = sejr.`escalate_job_reasons_id`', 'inner');
                }
                if ($join_table == 'staff_accounts') {
                    $this->db->join('`staff_accounts` AS sa', 'j.`assigned_tech` = sa.`StaffID`', 'inner');
                }
                if ($join_table == 'regions') {
                    $this->db->join('`regions` AS r', 'p.`state` = r.`region_state`', 'inner');
                }
                if ($join_table == 'property_tenants') {

                    $append_join_str = [];
                    if (isset($params['pt_active'])) {
                        $append_join_str[] = "AND pt.`active` = {$params['pt_active']}";
                    }
                    $append_join_imp = implode(" ", $append_join_str);
                    $this->db->join('`property_tenants` AS pt', "p.`property_id` =  pt.`property_id` {$append_join_imp}", 'inner');
                }
                if ($join_table == 'countries') {
                    $this->db->join('`countries` AS c', 'a.`country_id` =  c.`country_id`', 'inner');
                }

                // credits
                // pair
                if ($join_table == 'invoice_credits') {
                    $append_join_str = [];
                    // credit reason
                    if ($params['credit_reason_id'] && !empty($params['credit_reason_id'])) {
                        $append_join_str[] = "AND inv_cred.`credit_reason` = {$params['credit_reason_id']}";
                    }
                    $append_join_imp = implode(" ", $append_join_str);
                    $this->db->join('`invoice_credits` AS inv_cred', "j.`id` = inv_cred.`job_id` {$append_join_imp}", 'inner');
                }
                if ($join_table == 'credit_reason') {
                    $this->db->join('`credit_reason` AS cred_reas', 'inv_cred.`credit_reason` = cred_reas.`credit_reason_id`', 'inner');
                }

                // payments
                // pair
                if ($join_table == 'invoice_payments') {
                    $append_join_str = [];
                    // payment type
                    if ($params['type_of_payment'] && !empty($params['type_of_payment'])) {
                        $append_join_str[] = "AND inv_pay.`type_of_payment` = {$params['type_of_payment']}";
                    }
                    $append_join_imp = implode(" ", $append_join_str);
                    $this->db->join('`invoice_payments` AS inv_pay', "j.`id` = inv_pay.`job_id` {$append_join_imp}", 'inner');
                }
                if ($join_table == 'payment_types') {
                    $this->db->join('`payment_types` AS pay_type', 'inv_pay.`type_of_payment` = pay_type.`payment_type_id`', 'inner');
                }
            }
        }



        // custom joins
        if (isset($params['custom_joins']) && $params['custom_joins'] != '') {
            $this->db->join($params['custom_joins']['join_table'], $params['custom_joins']['join_on'], $params['custom_joins']['join_type']);
        }

        // filter
        if (is_numeric($params['job_id'])) {
            $this->db->where('j.`id`', $params['job_id']);
        }

        if ($params['job_created'] != '') {
            $job_created = "CAST( j.`created` AS DATE ) = '{$params['job_created']}'";
            $this->db->where($job_created);
        }

        if (is_numeric($params['del_job'])) {
            $this->db->where('j.`del_job`', $params['del_job']);
        }
        if (isset($params['out_of_tech_hours'])) {
            $this->db->where('j.`out_of_tech_hours`', $params['out_of_tech_hours']);
        }

        // Job Type filters
        if (isset($params['job_type']) && $params['job_type'] != '') {

            if ($params['job_type'] == 'cot & lr') {
                $cot_lr_combo = "( j.job_type = 'Change of Tenancy' OR j.job_type = 'Lease Renewal' )";
                $this->db->where($cot_lr_combo);
            } else {
                $this->db->where('j.`job_type`', $params['job_type']);
            }
        }

        // Job Status filter
        if (isset($params['job_status']) && $params['job_status'] != '') {
            
            // amend for covid-19
            if( $params['job_status'] == 'On Hold' ){
                $this->db->where("j.`status` IN('On Hold','On Hold - COVID')");
            }else{
                $this->db->where('j.`status`', $params['job_status']);
            }

        }

        // Service filters
        if (isset($params['service_filter']) && $params['service_filter'] != '') {
            $this->db->where('j.`service`', $params['service_filter']);
        }

        // Date filters
        if (isset($params['date']) && $params['date'] != '') {
            $this->db->where('j.`date`', $params['date']);
        }

        if (isset($params['cancelled_date']) && $params['cancelled_date'] != '') {
            $this->db->where('j.`cancelled_date`', $params['cancelled_date']);
        }

        //urgent
        if ($params['is_urgent'] && !empty($params['is_urgent'])) {
            $this->db->where('j.`urgent_job`', $params['is_urgent']);
        }

        // search: LIKE SQL returns buggy result when used on JOIN statement
        if (isset($params['search']) && $params['search'] != '') {
            $search_filter = "CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode))";
            $this->db->like($search_filter, $params['search']);
        }

        // custom filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
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

        // having
        if (isset($params['having']) && $params['having'] != '') {
            $this->db->having($params['having']);
        }

        // sort
        if (isset($params['sort_list'])) {
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }

        // custom filter
        if (isset($params['custom_sort'])) {
            $this->db->order_by($params['custom_sort']);
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

    public function get_jobs_invoicing($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`jobs` AS j');
        $this->db->join('`property` AS p', 'j.`property_id` = p.`property_id`', 'left');
        $this->db->join('`agency` AS a', ' p.`agency_id` = a.`agency_id`', 'left');

        // set joins
        if ($params['join_table'] > 0) {

            foreach ($params['join_table'] as $join_table) {

                if ($join_table == 'api_property_data') {
                    $this->db->join('`api_property_data` AS apd', 'j.`property_id` = apd.`crm_prop_id`', 'left');
                }
                if ($join_table == 'agency_maintenance') {
                    $this->db->join('`agency_maintenance` AS am', 'a.`agency_id` = am.`agency_id`', 'left');
                }
                if ($join_table == 'maintenance') {
                    $this->db->join('`maintenance` AS m', 'am.`maintenance_id` = m.`maintenance_id`', 'left');
                }
                if ($join_table == 'agency_api_tokens') {
                    $this->db->join('`agency_api_tokens` AS aat', 'a.`agency_id` = aat.`agency_id` AND aat.`api_id` = 1', 'left');
                }
            }
        }

        // Date filters
        if (isset($params['date']) && $params['date'] != '') {
            $this->db->where('j.`date`', $params['date']);
        }

        // Maintenance filters
        if (isset($params['maintenance_program_filter']) && $params['maintenance_program_filter'] != '') {
            $this->db->where_in('m.`maintenance_id`', $params['maintenance_program_filter']);
        }

        //search
        if (isset($params['search']) && $params['search'] != '') {
            $search_filter = "CONCAT_WS(' ', LOWER(p.`address_1`), LOWER(p.`address_2`), LOWER(p.`address_3`), (a.`agency_name`))";
            $this->db->like($search_filter, $params['search']);
        }

        // custom filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
        }

        // custom filter
        if (isset($params['custom_where_arr'])) {
            foreach ($params['custom_where_arr'] as $index => $custom_where) {
                if ($custom_where != '') {
                    $this->db->where($custom_where);
                }
            }
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

        if (isset($params['display_query']) && $params['display_query'] == 1) {
            echo $this->db->last_query();
        }

        //echo $query;
        //exit();

        /*
        if (isset($params['date']) && $params['date'] != '') {
            echo "TEST";
            echo $query;
            exit();
        }
        */
        //echo $query;
        //exit();

        $query = $this->db->get();
        return $query;
    }

    /**
     * Update Jobs Status To Merge Certificates by Job_ID
     */
    public function move_to_merge($job_id, $data) {
        $this->db->where('id', $job_id);
        $this->db->update('jobs', $data);
        $this->db->limit(1);
        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    /**
     * Update Jobs by job ID
     * $params job_id, data
     */
    public function update_job($job_id, $data) {
        $this->db->where('id', $job_id);
        $this->db->update('jobs', $data);
        $this->db->limit(1);
        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    /**
     * Update Jobs by property id
     * $params property id, data
     */
    public function update_job_by_prop_id($prop_id, $data) {
        $this->db->where('property_id', $prop_id);
        $this->db->where('status!=', 'Completed');
        $this->db->update('jobs', $data);
        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    /**
     * Update Agency by agency id
     * $params agency_id, data
     */
    public function update_agency($agency_id, $data) {
        $this->db->where('agency_id', $agency_id);
        $this->db->update('agency', $data);
        $this->db->limit(1);
        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    /**
     * Get bundled services
     * $params params
     */
    function getbundle_services($params) {
        $job_id = $params['job_id'];
        $bs_id = $params['bs_id'];
        $is_limit = $params['is_limit'];
        $str = "";
        if ($bs_id != "") {
            $str .= "AND `bundle_services_id` = {$bs_id}";
        }
        if ($is_limit != "") {
            $limit_str = "LIMIT 1";
        }

        $sql = "SELECT *
            FROM `bundle_services` AS bs
            LEFT JOIN `alarm_job_type` AS ajt ON ajt.`id` = bs.`alarm_job_type_id`
            WHERE `job_id` = {$job_id}
            {$str}
            ORDER BY ajt.`id`
            {$limit_str}
        ";

        $bundServ = $this->db->query($sql);
        $service = $bundServ->result_array();

        return $service;
    }

    /**
     * Sync job and job service
     * $params params
     */
    function runSync($params) {

        $job_id = isset($params['job_id']) ? $params['job_id'] : "";
        $jserv = isset($params['jserv']) ? $params['jserv'] : "";
        $bundle_serv_id = isset($params['bundle_serv_id']) ? $params['bundle_serv_id'] : "";
        
        // get job details
        $job_sql = $this->db->query("
            SELECT 
                j.`property_id`,
                j.`alarms_synced`,
                j.`ss_sync`,
                j.`cw_sync`,
                j.`wm_sync`,
                j.`we_sync`,
    
                ajt.`bundle`,
                ajt.`bundle_ids`                
            FROM `jobs` AS j
            LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
            WHERE j.`id` = {$job_id}
        ");
        $job_row = $job_sql->row();
    
        // if bundle
        if ( $job_row->bundle == 1 && $bundle_serv_id > 0 ) {
    
            // check if job bundle service already synced
            $bun_ser_sql_str = "
                SELECT `sync`
                FROM `bundle_services`
                WHERE `bundle_services_id` = {$bundle_serv_id}
            ";
    
            $bun_serv_sql = $this->db->query($bun_ser_sql_str);
            $bun_serv_row = $bun_serv_sql->row();
            
            // if not yet snyc, do sync
            if ($bun_serv_row->sync == 0 || $params['override_sync']==1) {                

                // get sync type
                $servi_type_sql_str = "
                    SELECT `sync_marker` 
                    FROM `alarm_job_type`
                    WHERE `id` = {$jserv}
                ";
                $servi_type_sql = $this->db->query($servi_type_sql_str);
                $servi_type_row = $servi_type_sql->row();
                
                if( $servi_type_row->sync_marker != '' ){

                    // sync per service type
                    switch ($servi_type_row->sync_marker) {
                        case 'alarms_synced':
                            $prev_job_sql = $this->getPrevSmokeAlarm($job_row->property_id);
                            if ($prev_job_sql->num_rows() > 0){
                                $this->snycSmokeAlarmData($job_id, $prev_job_sql);
                            }
                        break;         
                        case 'ss_sync':
                            $prev_job_sql = $this->getPrevSafetySwitch($job_row->property_id);
                            if ($prev_job_sql->num_rows() > 0){
                                $this->snycSafetySwitchData($job_id, $prev_job_sql);
                            }
                        break;
                        case 'cw_sync':
                            $prev_job_sql = $this->getPrevCordedWindow($job_row->property_id);
                            if ($prev_job_sql->num_rows() > 0){
                                $this->snycCordedWindowData($job_id, $prev_job_sql);
                            }
                        break;
                        case 'wm_sync':
                            $prev_job_sql = $this->getPrevWaterMeter($job_row->property_id);
                            if ($prev_job_sql->num_rows() > 0){
                                $this->snycWaterMeter($job_id, $prev_job_sql);
                            }
                        break;
                        case 'we_sync': // WE
                            $prev_job_sql = $this->getPrevWaterEfficiency($job_row->property_id);
                            if ($prev_job_sql->num_rows() > 0){
                                $this->SnycWaterEfficiency($job_id, $prev_job_sql);
                            }
                        break;
                    }

                }
    
                // mark
                $this->markAsSyncBundle($bundle_serv_id);

            }

        } else { // single service

            // get sync type
            $servi_type_sql_str = "
                SELECT `sync_marker` 
                FROM `alarm_job_type`
                WHERE `id` = {$jserv}
            ";
            $servi_type_sql = $this->db->query($servi_type_sql_str);
            $servi_type_row = $servi_type_sql->row();
            
            if( $servi_type_row->sync_marker != '' ){

                // sync per service type
                switch ($servi_type_row->sync_marker) {
                    case 'alarms_synced':                        
                        $is_sync = $job_row->alarms_synced;                     
                        if ($is_sync == 0 || $params['override_sync']==1){                            
                            $prev_job_sql = $this->getPrevSmokeAlarm($job_row->property_id);
                            if ($prev_job_sql->num_rows() > 0){                                
                                $this->snycSmokeAlarmData($job_id, $prev_job_sql);
                                $this->markAsSync($job_id, $jserv);
                            }
                        }                        
                    break;         
                    case 'ss_sync':
                        $is_sync = $job_row->ss_sync;
                        if ($is_sync == 0 || $params['override_sync']==1){
                            $prev_job_sql = $this->getPrevSafetySwitch($job_row->property_id);
                            if ($prev_job_sql->num_rows() == 0) {
                                $prev_job_sql = $this->getPrevSmokeAlarm($job_row->property_id);
                                if ($prev_job_sql->num_rows() > 0){
                                    $this->snycSafetySwitchData($job_id, $prev_job_sql);
                                    $this->markAsSync($job_id, $jserv);
                                }
                            }
                        }
                    break;
                    case 'cw_sync':
                        $is_sync = $job_row->cw_sync;
                        if ($is_sync == 0 || $params['override_sync']==1){
                            $prev_job_sql = $this->getPrevCordedWindow($job_row->property_id);
                            if ($prev_job_sql->num_rows() > 0){
                                $this->snycCordedWindowData($job_id, $prev_job_sql);
                                $this->markAsSync($job_id, $jserv);
                            }
                        }
                    break;
                    case 'wm_sync':
                        $is_sync = $job_row->wm_sync;
                        if ($is_sync == 0 || $params['override_sync']==1){
                            $prev_job_sql = $this->getPrevWaterMeter($job_row->property_id);
                            if ($prev_job_sql->num_rows() > 0){
                                $this->snycWaterMeter($job_id, $prev_job_sql);
                                $this->markAsSync($job_id, $jserv);
                            }
                        }
                    break;
                    case 'we_sync': // WE                       
                        $is_sync = $job_row->we_sync;                     
                        if ($is_sync == 0 || $params['override_sync']==1){                            
                            $prev_job_sql = $this->getPrevWaterEfficiency($job_row->property_id);
                            if ($prev_job_sql->num_rows() > 0){                                
                                $this->SnycWaterEfficiency($job_id, $prev_job_sql);
                                $this->markAsSync($job_id, $jserv);
                            }
                        }                        
                    break; 
                }

            }
               
        }
    }

    function getPrevSmokeAlarm($prop_id) {
        
        return $this->db->query("
            SELECT DISTINCT j.`id`
            FROM `alarm` AS a
            LEFT JOIN `jobs` AS j ON j.`id` = a.`job_id`
            WHERE j.`property_id` ={$prop_id}
            AND j.status IN('Completed','Merged Certificates')
            AND j.`id` != ''
            AND j.`del_job` = 0
            AND a.`ts_discarded` = 0
            AND j.`assigned_tech` != 1
		    AND j.`assigned_tech` != 2
            ORDER BY j.`date` DESC, j.`id` DESC
            LIMIT 0,1
        ");
    }

    function getPrevSafetySwitch($prop_id) {
        
        return $this->db->query("
            SELECT DISTINCT j.`id`
            FROM `safety_switch` AS ss
            LEFT JOIN `jobs` AS j ON j.`id` = ss.`job_id`
            WHERE j.`property_id` ={$prop_id}
            AND j.status IN('Completed','Merged Certificates')
            AND j.`id` != ''
            AND j.`del_job` = 0
            AND j.`assigned_tech` != 1
		    AND j.`assigned_tech` != 2
            ORDER BY j.`date` DESC, j.`id` DESC
            LIMIT 0,1
        ");

    }

    function getPrevCordedWindow($prop_id) {
        
        return $this->db->query("
            SELECT DISTINCT j.`id`
            FROM `corded_window` AS cw
            LEFT JOIN `jobs` AS j ON j.`id` = cw.`job_id`
            WHERE j.`property_id` ={$prop_id}
            AND j.status IN('Completed','Merged Certificates')
            AND j.`id` != ''
            AND j.`del_job` = 0
            AND j.`assigned_tech` != 1
		    AND j.`assigned_tech` != 2
            ORDER BY j.`date` DESC, j.`id` DESC
            LIMIT 0,1
        ");

    }

    function getPrevWaterEfficiency($prop_id) {
        
        return $this->db->query("
            SELECT j.`id`
            FROM `water_efficiency` AS we
            LEFT JOIN `jobs` AS j ON j.`id` = we.`job_id`
            WHERE j.`property_id` = {$prop_id}
            AND j.status IN('Completed','Merged Certificates')
            AND j.`id` > 0
            AND j.`del_job` = 0
            AND j.`assigned_tech` != 1
		    AND j.`assigned_tech` != 2
            ORDER BY j.`date` DESC, j.`id` DESC
            LIMIT 0,1
        ");

    }

    function getPrevWaterMeter($prop_id) {
        
        return $this->db->query("
            SELECT DISTINCT j.`id`
            FROM `water_meter` AS wm
            LEFT JOIN `jobs` AS j ON j.`id` = wm.`job_id`
            WHERE j.`property_id` ={$prop_id}
            AND j.status IN('Completed','Merged Certificates')
            AND j.`id` != ''
            AND j.`del_job` = 0
            AND j.`assigned_tech` != 1
		    AND j.`assigned_tech` != 2
            ORDER BY j.`date` DESC, j.`id` DESC
            LIMIT 0,1
        ");

    }

    function snycSmokeAlarmData($job_id, $prev_job_sql) {

        // get previous job
        $prev_job_row = $prev_job_sql->row();

        // previous job ID
        if( $prev_job_row->id > 0 ){

            // previous job data
            $this->db->select('
                `survey_numlevels`,
                `survey_ceiling`,
                `survey_ladder`,
                `ts_safety_switch`,
                `ss_location`,
                `ss_quantity`,
                `ts_safety_switch_reason`,
                `ss_image`
            ');
            $this->db->from('jobs');
            $this->db->where('id', $prev_job_row->id);
            $pj_sql = $this->db->get();

            $prev_job = $pj_sql->row();

            if( $job_id > 0 ){        

                // update job details
                $this->db->query("
                    UPDATE `jobs` 
                    SET 
                        `survey_numlevels` = '{$prev_job->survey_numlevels}', 
                        `survey_ceiling` = '{$prev_job->survey_ceiling}', 
                        `survey_ladder` = '{$prev_job->survey_ladder}',
                        `ts_safety_switch` = '{$prev_job->ts_safety_switch}', 
                        `ss_location` = '{$prev_job->ss_location}',
                        `ss_quantity` = '{$prev_job->ss_quantity}', 
                        `ts_safety_switch_reason` = '{$prev_job->ts_safety_switch_reason}',
                        `ss_image` = '{$prev_job->ss_image}'
                    WHERE `id` = {$job_id} 
                ");

            }        

            // get previous job and insert previous alarm to this job
            $insert_sql_str = "
            INSERT INTO 
            `alarm` (
                `job_id`,
                `alarm_power_id`,
                `alarm_type_id`,			
                `make`,
                `model`,
                `ts_position`,			
                `alarm_job_type_id`,
                `expiry`,
                `ts_required_compliance`
            )
            SELECT 
                {$job_id}, 					
                `alarm_power_id`,
                `alarm_type_id`,			
                UPPER( `make` ),
                UPPER( `model` ),
                UPPER( `ts_position` ),			
                `alarm_job_type_id`,
                `expiry`,
                `ts_required_compliance`
            FROM `alarm`
            WHERE `job_id` = {$prev_job_row->id}
            AND `ts_discarded` = 0
            ";
            $this->db->query($insert_sql_str);

        }

    }

    function snycSafetySwitchData($job_id, $prev_job_sql) {

        // get property id
        $this->db->select('property_id');
        $this->db->from('jobs');
        $this->db->where('id', $job_id);
        $prop_sql = $this->db->get();
        $p_row = $prop_sql->row();

        if( $p_row->property_id > 0 ){

            // check if no SS data yet
            $this->db->select('ss.`safety_switch_id`');
            $this->db->from('safety_switch AS ss');
            $this->db->join('`jobs` AS j', 'ss.`job_id` = j.`id`', 'left');
            $this->db->where('j.property_id', $p_row->property_id);
            $this->db->where('j.status', 'Completed');
            $ss_sql = $this->db->get();

            // has already SS data, get previous SS data
            if ($ss_sql->num_rows() > 0) {

                // get previous job
                $prev_job_row = $prev_job_sql->row();

                if( $prev_job_row->id > 0 ){

                    // get previous SS data
                    $this->db->select('
                        `ss_location`,
                        `ss_quantity`
                    ');
                    $this->db->from('jobs');
                    $this->db->where('id', $prev_job_row->id);
                    $prev_ss_data_sql = $this->db->get();
                    $prev_ss_data_row = $prev_ss_data_sql->row();

                    if( $job_id > 0 ){

                        // update safety switch job details                 
                        $this->db->query("
                            UPDATE `jobs`
                            SET 
                                `ss_location` = '{$prev_ss_data_row->ss_location}',
                                `ss_quantity` = '{$prev_ss_data_row->ss_quantity}'
                            WHERE `id` = {$job_id}
                        ");
                        
                    }   

                }                     
                
            } else { // no SS data yet, get it from alarm
                
                $prev_job_sql = $this->getPrevSmokeAlarm($p_row->property_id);
                $prev_job_row = $prev_job_sql->row();

                if( $prev_job_row->id ){

                   // get previous SS data
                   $this->db->select('
                        `ss_location`,
                        `ss_quantity`
                    ');
                    $this->db->from('jobs');
                    $this->db->where('id', $prev_job_row->id);
                    $prev_ss_data_sql = $this->db->get();
                    $prev_ss_data_row = $prev_ss_data_sql->row();

                    if( $job_id > 0 ){

                        // update safety switch job details                 
                        $this->db->query("
                            UPDATE `jobs`
                            SET 
                                `ss_location` = '{$prev_ss_data_row->ss_location}',
                                `ss_quantity` = '{$prev_ss_data_row->ss_quantity}'
                            WHERE `id` = {$job_id}
                        ");
                        
                    } 

                }
                
            }

            // get previous job and insert previous safety switch to this job        
            $this->db->query("
                INSERT INTO 
                `safety_switch` (
                    `job_id`, 
                    `make`, 
                    `model`
                )
                SELECT {$job_id}, `make`, `model`
                FROM `safety_switch`
                WHERE `job_id` = {$prev_job_row->id}
            ");


        }       

    }

    // get previous job and insert previous corded window to this job
    function snycCordedWindowData($job_id, $prev_job_sql) {

        // get previous job
        $prev_job_row = $prev_job_sql->row();
            
        if(  $job_id > 0 && $prev_job_row->id > 0 ){

            $ss_sql2 = "
                INSERT INTO 
                `corded_window` (
                    `job_id`,
                    `covering`,
                    `ftllt1_6m`,
                    `tag_present`,
                    `clip_rfc`,
                    `clip_present`,
                    `loop_lt220m`,
                    `seventy_n`,
                    `cw_image`,
                    `location`,
                    `num_of_windows`
                )
                SELECT 
                    '{$job_id}', 
                    `covering`,
                    `ftllt1_6m`,
                    `tag_present`,
                    `clip_rfc`,
                    `clip_present`,
                    `loop_lt220m`,
                    `seventy_n`,
                    `cw_image`, 
                    `location`,
                    `num_of_windows`
                FROM `corded_window`
                WHERE `job_id` = {$prev_job_row->id}
            ";
            $this->db->query($ss_sql2);

        }                           
        
    }

    // get previous job and insert previous corded window to this job
    function SnycWaterEfficiency($job_id, $prev_job_sql) {

        $today_full_ts = date('Y-m-d H:i:s');

        // get previous job
        $prev_job_row = $prev_job_sql->row();
            
        if(  $job_id > 0 && $prev_job_row->id > 0 ){

            $ss_sql2 = "             
                INSERT INTO 
                `water_efficiency` (
                    `job_id`,
                    `device`,
                    `location`,
                    `note`,
                    `created_date`
                )
                SELECT 
                    '{$job_id}', 
                    `device`,
                    `location`,
                    `note`,
                    '{$today_full_ts}'
                FROM `water_efficiency`
                WHERE `job_id` = {$prev_job_row->id}
            ";
            $this->db->query($ss_sql2);

        }                           
        
    }

    // get previous job and insert previous water meter to this job
    function snycWaterMeter($job_id, $prev_job_sql) {

       // get previous job
       $prev_job_row = $prev_job_sql->row();
        
        if( $job_id > 0 && $prev_job_row->id > 0 ){

            $ss_sql2 = "
                INSERT INTO 
                `water_meter` (
                    `job_id`,
                    `location`,
                    `meter_image`,
                    `created_date`,
                    `active`
                )
                SELECT 
                    '{$job_id}', 
                    `location`,
                    `meter_image`,
                    '" . date('Y-m-d H:i:s') . "',
                    '1'
                FROM `water_meter`
                WHERE `job_id` = {$prev_job_row->id}
            ";
            $this->db->query($ss_sql2);

        }
        
    }

    function markAsSyncBundle($bundle_id) {

        // marked as synced    
        if( $bundle_id > 0 ){
            $this->db->query("
                UPDATE `bundle_services`
                SET `sync` = 1
                WHERE `bundle_services_id` = {$bundle_id}
            ");
        }
        

    }

    function markAsSync($job_id, $jserv) {     

        // get sync type
        $servi_type_sql_str = "
        SELECT `sync_marker` 
        FROM `alarm_job_type`
        WHERE `id` = {$jserv}
        ";
        $servi_type_sql = $this->db->query($servi_type_sql_str);
        $servi_type_row = $servi_type_sql->row();

        // update sync marker
        if( $servi_type_row->sync_marker != '' ){

            $update_sql_str = "
            UPDATE `jobs`
            SET `{$servi_type_row->sync_marker}` = 1
            WHERE `id` = {$job_id}
            ";
            $this->db->query($update_sql_str);

        }

    }

    /**
     * Get email stats
     * $params date, page total rows, job_status
     */
    public function get_email_stats($date = '', $job_status, $excludePmeArr = array()) {

        if ($date && $date != '') {
            $filter = " AND j.date='{$date}'";
        }

        if (!empty($excludePmeArr)) {
            $excludeIds = implode(",", $excludePmeArr);
        }else {
            $excludeIds = 0;
        }

        return $email_stats_query = "(
            SELECT 'sent' as result_type, COUNT(j.id) AS result
            FROM jobs j, property p, agency a 
            WHERE j.property_id = p.property_id 
            AND j.status = '{$job_status}'" . $filter . "
            AND  p.agency_id = a.agency_id            
            AND a.account_emails LIKE '%@%'
            AND j.client_emailed IS NOT NULL
            AND p.`deleted` =0
            AND a.`status` = 'active'
            AND j.`del_job` = 0
            AND a.`country_id` ={$this->config->item('country')}
            AND j.`id` NOT IN ({$excludeIds})
        )
        
        UNION ALL
        (
            SELECT 'total', COUNT(j.id) AS result
            FROM jobs j, property p, agency a 
            WHERE j.property_id = p.property_id 
            AND j.status = '{$job_status}'" . $filter . "
            AND  p.agency_id = a.agency_id            
            AND a.account_emails LIKE '%@%'
            AND p.`deleted` =0
            AND a.`status` = 'active'
            AND j.`del_job` = 0
            AND a.`country_id` ={$this->config->item('country')}
            AND j.`id` NOT IN ({$excludeIds})
        )";
    }

    /**
     * Get Print Count Query
     */
    public function getPrintCountQuery() {

        return $print_query = "(
            SELECT COUNT(j.id) as to_print FROM jobs j, property p, agency a 
            WHERE j.status = 'Merged Certificates'
            AND j.property_id = p.property_id
            AND p.agency_id = a.agency_id            
            AND a.send_combined_invoice = 0
            AND p.`deleted` =0
            AND a.`status` = 'active'
            AND j.`del_job` = 0
            AND a.`country_id` ={$this->config->item('country')}
            )
            UNION
            (
                SELECT COUNT(j.id) as to_print FROM jobs j, property p, agency a 
                WHERE j.status = 'Merged Certificates'
                AND j.property_id = p.property_id
                AND p.agency_id = a.agency_id                
                AND a.send_combined_invoice = 1
                AND p.`deleted` =0
                AND a.`status` = 'active'
                AND j.`del_job` = 0
                AND a.`country_id` ={$this->config->item('country')}
            )";
    }

    /**
     * Get Merge Job Sent Count
     * return count
     */
    public function mergeJobSentSmsCount() {
        $ss_sql = $this->db->query("
            SELECT COUNT(j.id) AS jcount
            FROM `jobs` AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
            LEFT JOIN `job_reason` AS jr ON j.`job_reason_id` = jr.`job_reason_id`
            LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
            WHERE j.`status` = 'Merged Certificates'
            AND CAST( j.`sms_sent_merge` AS date )  = '" . date("Y-m-d") . "'
            AND p.`deleted` =0
            AND a.`status` = 'active'
            AND j.`del_job` = 0
            AND a.`country_id` = {$this->config->item('country')}		
        ");
        $ss = $ss_sql->row_array();
        return $ss['jcount'];
    }

    // merge SMS sent on API tab
    public function merge_api_sms_sent_count(){

        $pme_api = 1; // PMe
        $palace_api = 4; // Palace

        $sql = $this->db->query("
            SELECT COUNT(j.id) AS jcount
            FROM `jobs` AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `api_property_data` AS apd_pme ON ( p.`property_id` = apd_pme.`crm_prop_id` AND apd_pme.`api` = {$pme_api} )
            LEFT JOIN `api_property_data` AS apd_palace ON ( p.`property_id` = apd_palace.`crm_prop_id` AND apd_palace.`api` = {$palace_api} )
            LEFT JOIN `agency` AS a ON  p.`agency_id` = a.`agency_id`
            LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
            LEFT JOIN `job_type` AS jt ON j.`job_type` = jt.`job_type`
            LEFT JOIN `agency_api_tokens` AS aat ON a.`agency_id` = aat.`agency_id` AND aat.`api_id` = 1
            WHERE p.`deleted` = 0
            AND a.`status` = 'active'
            AND j.`del_job` = 0
            AND a.`country_id` = 1
            AND j.`status` = 'Merged Certificates'
            AND Date( j.`sms_sent_merge` )  = '" . date("Y-m-d") . "'
            AND (
                (
                    ( 
                        apd_pme.`api_prop_id` IS NOT NULL AND 
                        apd_pme.`api_prop_id` != '' AND 
                        apd_pme.`api` = {$pme_api}
                    ) AND
                    ( a.`pme_supplier_id` IS NOT NULL AND a.`pme_supplier_id` != '' ) AND 
                    ( aat.`connection_date` IS NOT NULL AND aat.`connection_date` != '' )
                )                
                OR
                (
                    ( 
                        apd_palace.`api_prop_id` IS NOT NULL AND 
                        apd_palace.`api_prop_id` != '' AND 
                        apd_palace.`api` = {$palace_api}
                    ) AND 
                    ( a.`palace_supplier_id` IS NOT NULL AND a.`palace_supplier_id` != '' ) AND 
                    ( a.`palace_agent_id` IS NOT NULL AND a.`palace_agent_id` != '' ) AND 
                    ( a.`palace_diary_id` IS NOT NULL AND a.palace_diary_id != '' )
                )
            )
            AND p.`send_to_email_not_api` = 0            	
        ");

        return $sql->row()->jcount;        
        
    }


    // merge sent on normal tab
    public function merge_sms_sent_count(){

        $pme_api = 1; // PMe
        $palace_api = 4; // Palace

        $sql = $this->db->query("
            SELECT COUNT(j.id) AS jcount
            FROM `jobs` AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `api_property_data` AS apd_pme ON ( p.`property_id` = apd_pme.`crm_prop_id` AND apd_pme.`api` = {$pme_api} )
            LEFT JOIN `api_property_data` AS apd_palace ON ( p.`property_id` = apd_palace.`crm_prop_id` AND apd_palace.`api` = {$palace_api} )
            LEFT JOIN `agency` AS a ON  p.`agency_id` = a.`agency_id`
            LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
            LEFT JOIN `job_type` AS jt ON j.`job_type` = jt.`job_type`
            LEFT JOIN `agency_api_tokens` AS aat ON a.`agency_id` = aat.`agency_id` AND aat.`api_id` = 1
            WHERE p.`deleted` = 0
            AND a.`status` = 'active'
            AND j.`del_job` = 0
            AND a.`country_id` = 1
            AND j.`status` = 'Merged Certificates'            
            AND Date( j.`sms_sent_merge` )  = '" . date("Y-m-d") . "'	
            AND NOT(
                (
                    (
                        ( 
                            apd_pme.`api_prop_id` IS NOT NULL AND 
                            apd_pme.`api_prop_id` != '' AND 
                            apd_pme.`api` = {$pme_api}
                        ) AND
                        ( a.`pme_supplier_id` IS NOT NULL AND a.`pme_supplier_id` != '' ) AND 
                        ( aat.`connection_date` IS NOT NULL AND aat.`connection_date` != '' )
                    )                
                    OR
                    (
                        ( 
                            apd_palace.`api_prop_id` IS NOT NULL AND 
                            apd_palace.`api_prop_id` != '' AND 
                            apd_palace.`api` = {$palace_api}
                         ) AND 
                        ( a.`palace_supplier_id` IS NOT NULL AND a.`palace_supplier_id` != '' ) AND 
                        ( a.`palace_agent_id` IS NOT NULL AND a.`palace_agent_id` != '' ) AND 
                        ( a.`palace_diary_id` IS NOT NULL AND a.palace_diary_id != '' )
                    )
                )
                AND p.`send_to_email_not_api` = 0
            )
        ");

        return $sql->row()->jcount;        
        
    }

    public function getJobsNotCompletedV2($params) {


        $get_reason2 = $this->db->select('name')->get('job_reason');
        foreach ($get_reason2->result_array() as $row) {
            $jr_str[] = $row['name'];
        }


        if ($params['sel_query'] && !empty($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }


        $this->db->select($sel_query);
        $this->db->from('job_log as jl');
        $this->db->join('jobs as j', 'j.id = jl.job_id', 'left');
        $this->db->join('job_reason as jr', 'jr.job_reason_id = j.job_reason_id', 'left');
        $this->db->join('property as p', 'p.property_id = j.property_id', 'left');
        $this->db->join('agency as a', 'a.agency_id = p.agency_id', 'left');
        $this->db->join('staff_accounts as ass_tech', 'ass_tech.StaffID = j.assigned_tech', 'left');
        $this->db->join('staff_accounts as sa', 'sa.StaffID = jl.staff_id', 'left');
        $this->db->where('a.status', 'active');
        $this->db->where('p.deleted', 0);
        $this->db->where("( p.is_nlm = 0 OR p.is_nlm IS NULL )");
        $this->db->where('j.del_job', 0);
        $this->db->where('jl.deleted', 0);
        $this->db->where('a.country_id', $this->config->item('country'));
        $this->db->where("jl.`comments` NOT LIKE '%Status Changed from 240v Rebook%'");


        //reson filter
        if ($params['reason'] && !empty($params['reason'])) {
            $this->db->where('jl.contact_type', $params['reason']);
        } else {

            $this->db->where_in('jl.contact_type', $jr_str);
        }

        //date filter
        if ($params['date_from_filter'] != "" && $params['date_to_filter'] != "") {
            $this->db->where('jl.eventdate >=', $params['date_from_filter']);
            $this->db->where('jl.eventdate <=', $params['date_to_filter']);
        }


        //tech filter
        if ($params['tech_filter'] && !empty($params['tech_filter'])) {
            $this->db->where('jl.staff_id', $params['tech_filter']);
        }

        //dk filter
        if ($params['dk_filter'] == 0 && !empty($params['dk_filter'])) {
            $dk_str = "jl.`contact_type` NOT LIKE '%DK%' ";
            $this->db->where($dk_str, NULL, FALSE);
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

    public function getJobsNotCompletedV3($params) {


        $get_reason2 = $this->db->select('name,job_reason_id')->get('job_reason');
        foreach ($get_reason2->result_array() as $row) {
            $jr_str[] = $row['name'];
            $jr_str_id[] = $row['job_reason_id'];
        }


        if ($params['sel_query'] && !empty($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }


        $this->db->select($sel_query);
        $this->db->from('jobs_not_completed as jnc');
        $this->db->join('jobs as j', 'j.id = jnc.job_id', 'left');
        $this->db->join('job_reason as jr', 'jr.job_reason_id = jnc.reason_id', 'left');
        $this->db->join('property as p', 'p.property_id = j.property_id', 'left');
        $this->db->join('agency as a', 'a.agency_id = p.agency_id', 'left');
        $this->db->join('agency_priority as aght', 'a.agency_id = aght.agency_id', 'left');
        $this->db->join('staff_accounts as ass_tech', 'ass_tech.StaffID = jnc.tech_id', 'left');
        $this->db->where('a.status', 'active');
        $this->db->where('p.deleted', 0);
        $this->db->where("( p.is_nlm = 0 OR p.is_nlm IS NULL )");
        $this->db->where('j.del_job', 0);
        $this->db->where('jnc.active', 1);
        $this->db->where('a.country_id', $this->config->item('country'));
        $this->db->where("jnc.`reason_comment` NOT LIKE '%Status Changed from 240v Rebook%'");


        //reson filter
        if ($params['reason'] && !empty($params['reason'])) {
            $this->db->where('jnc.reason_id', $params['reason']);
        } else {

            $this->db->where_in('jnc.reason_id', $jr_str_id);
        }

        //date filter
        if ($params['date_from_filter'] != "" && $params['date_to_filter'] != "") {
            $this->db->where(" DATE_FORMAT(jnc.date_created, '%Y-%m-%d') >=", $params['date_from_filter']);
            $this->db->where(" DATE_FORMAT(jnc.date_created, '%Y-%m-%d') <=", $params['date_to_filter']);
        }


        //tech filter
        if ($params['tech_filter'] && !empty($params['tech_filter'])) {
            $this->db->where('jnc.tech_id', $params['tech_filter']);
        }

        //Missed jobs for last 30 days for Agency health check page only
        if ($params['30_days'] && !empty($params['30_days'])) {
            $this->db->where('jnc.date_created > NOW( ) - INTERVAL 30 DAY');
        }

        //job type filter
        if ($params['job_type_filter'] && !empty($params['job_type_filter'])) {
            $this->db->where('j.job_type', $params['job_type_filter']);
        }

        // agency filter
        if ( $params['agency_filter'] > 0 ) {
            $this->db->where('a.agency_id', $params['agency_filter']);
        }

        // custom filter
        if ( $params['custom_where'] != '' ) {
            $this->db->where($params['custom_where']);
        }

        // custom filter
        if (isset($params['custom_where_arr'])) {
            foreach ($params['custom_where_arr'] as $index => $custom_where) {
                if ($custom_where != '') {
                    $this->db->where($custom_where);
                }
            }
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

    public function bkd_getPrecompletedJobs($params) {

        if ($params['sel_query'] && !empty($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('jobs as j');
        $this->db->join('property as p', 'p.property_id = j.property_id', 'left');
        $this->db->join('alarm_job_type as ajt', 'ajt.id = j.service', 'left');
        $this->db->join('agency as a', 'a.agency_id = p.agency_id', 'left');
        $this->db->join('job_reason as jr', 'jr.job_reason_id = j.job_reason_id', 'left');
        $this->db->join('staff_accounts as sa', 'sa.StaffID = j.assigned_tech', 'left');
        $this->db->where('p.deleted', 0);
        $this->db->where("( p.is_nlm = 0 OR p.is_nlm IS NULL )");
        $this->db->where('a.status', 'active');
        $this->db->where('j.del_job', 0);
        $this->db->where('a.country_id', $this->config->item('country'));


        //FILTERS
        if ($params['service'] && !empty($params['service'])) {
            $this->db->where('j.service', $params['service']);
        }

        if ($params['tech_id'] && !empty($params['tech_id'])) {
            $this->db->where('j.assigned_tech', $params['tech_id']);
        }

        if ($params['date'] && !empty($params['date'])) {
            $this->db->where('j.date', $params['date']);
        }

        if ($params['search'] && !empty($params['search'])) {
            $search_filter = "CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode))";
            $this->db->like($search_filter, $params['search']);
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
        if ($params['limit'] && $params['limit'] > 0) {
            $this->db->limit($params['limit'], $params['offset']);
        }

        $query = $this->db->get();
        if ($params['display_query'] && $params['display_query'] == 1) {
            echo $this->db->last_query();
        }

        return $query;
    }

    /**
     * Get Total Price
     * return Total Price
     */
    public function bkd_getPriceTotal($date) {
        $sql = $this->db->query("
            SELECT SUM( j.`job_price` ) AS PriceTotal
            FROM `jobs` AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
            LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
            WHERE a.`country_id` = {$this->config->item('country')}
            AND j.`date` = '{$date}'
            AND p.`deleted` =0
            AND a.`status` = 'active'
            AND j.`del_job` = 0
        ");
        $row = $sql->row_array();
        return $row['PriceTotal'];
    }

    /**
     * Get Alarm Price Total
     * return alarm price total
     */
    public function bkd_alarmPriceTotal($date) {
        $sql = $this->db->query("
            SELECT SUM( al.`alarm_price` ) AS PriceTotal
            FROM  `alarm` AS al
            LEFT JOIN  `jobs` AS j ON al.`job_id` = j.`id` 
            LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
            LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
            WHERE a.`country_id` ={$this->config->item('country')}
            AND j.`date` =  '{$date}'
            AND p.`deleted` =0
            AND a.`status` =  'active'
            AND j.`del_job` = 0
            AND al.`new` = 1
            AND al.`ts_discarded` = 0
        ");
        $row = $sql->row_array();
        return $row['PriceTotal'];
    }

    public function get_num_services($params) {

        if ($params['sel_query'] && !empty($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('jobs as j');
        $this->db->join('property as p', 'p.property_id = j.property_id', 'left');
        $this->db->join('agency as a', 'a.agency_id = p.agency_id', 'left');
        $this->db->join('agency_priority as aght', 'a.agency_id = aght.agency_id', 'left');
        $this->db->where('j.del_job', 0);
        $this->db->where('a.status', 'active');
        $this->db->where('a.country_id', $this->config->item('country'));


        //FILTERS
        if ($params['agency_id'] && !empty($params['agency_id'])) {
            $this->db->where('a.agency_id', $params['agency_id']);
        }

        if ($params['state'] && !empty($params['state'])) {
            $this->db->where('a.state', $params['state']);
        }

        if ($params['job_type'] && !empty($params['job_type'])) {
            $this->db->where('j.job_type', $params['job_type']);
        }

        if ($params['from'] != 'all' && $params['to'] != 'all') {
            $from2 = date("Y-m-d", strtotime(str_replace("/", "-", $params['from'])));
            $to2 = date("Y-m-d", strtotime(str_replace("/", "-", $params['to'])));
            $where_date = " CAST(j.`created` AS DATE) BETWEEN '{$from2}' AND '{$to2}' ";
            $this->db->where($where_date);
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

    public function getJobPriceTotal($agency_id, $from, $to) {

        $str = "";
        if ($from != 'all' && $to != 'all') {
            $from2 = date("Y-m-d", strtotime(str_replace("/", "-", $from)));
            $to2 = date("Y-m-d", strtotime(str_replace("/", "-", $to)));
            $str = "AND CAST(j.`created` AS DATE) BETWEEN '{$from2}' AND '{$to2}'";
        }

        $sql_str = "
            SELECT SUM(j.`job_price`) AS jtot
            FROM jobs AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE a.`agency_id` ={$agency_id}
            AND a.`status` = 'active'
            AND j.`del_job` =0
            AND a.`country_id` = {$this->config->item('country')}
            {$str}
        ";

        $sql = $this->db->query($sql_str);
        $row = $sql->row_array();

        return $row['jtot'];
    }

    public function get_deleted($agency_id, $del_stat, $from, $to) {

        $str = "";
        if ($from != 'all' && $to != 'all') {
            $from2 = date("Y-m-d", strtotime(str_replace("/", "-", $from)));
            $to2 = date("Y-m-d", strtotime(str_replace("/", "-", $to)));
            $str = "AND CAST(p.`deleted_date` AS DATE) BETWEEN '{$from2}' AND '{$to2}'";
        }

        $sql = "
            SELECT *
            FROM jobs AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE a.`agency_id` ={$agency_id}            
            AND(
                j.`del_job` = 1 OR
                j.`status` = 'Cancelled'
            )            	
            AND a.`country_id` = {$this->config->item('country')}
            {$str}
        ";

        return $this->db->query($sql);
    }

    public function getAddedByAgency($agency_id, $from, $to) {

        $str = "";
        if ($from != 'all' && $to != 'all') {
            $from2 = date("Y-m-d", strtotime(str_replace("/", "-", $from)));
            $to2 = date("Y-m-d", strtotime(str_replace("/", "-", $to)));
            $str = "AND CAST(j.`created` AS DATE) BETWEEN '{$from2}' AND '{$to2}'";
        }

        $sql_str = "
            SELECT COUNT(j.`id`) AS jcount
            FROM jobs AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE a.`agency_id` ={$agency_id}
            AND a.`status` = 'active'
            AND j.`del_job` =0
            AND p.`added_by` <= 0
            AND a.`country_id` = {$this->config->item('country')}
            {$str}
        ";

        $sql = $this->db->query($sql_str);
        $row = $sql->row_array();

        return $row['jcount'];
    }

    public function getAddedBySats($agency_id, $from, $to) {

        $str = "";
        if ($from != 'all' && $to != 'all') {
            $from2 = date("Y-m-d", strtotime(str_replace("/", "-", $from)));
            $to2 = date("Y-m-d", strtotime(str_replace("/", "-", $to)));
            $str = "AND CAST(j.`created` AS DATE) BETWEEN '{$from2}' AND '{$to2}'";
        }

        $sql_str = "
            SELECT COUNT(j.`id`) AS jcount
            FROM jobs AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE a.`agency_id` ={$agency_id}
            AND a.`status` = 'active'
            AND j.`del_job` =0
            AND p.`added_by` > 0
            AND a.`country_id` = {$this->config->item('country')}
            {$str}
        ";

        $sql = $this->db->query($sql_str);
        $row = $sql->row_array();

        return $row['jcount'];
    }

    public function this_getAgencySalesRep($sr_id) {
        return $this->db->query("
            SELECT DISTINCT a.`salesrep` , sa.`FirstName` , sa.`LastName`
            FROM `agency` AS a
            LEFT JOIN `staff_accounts` AS sa ON sa.`StaffID` = a.`salesrep`
            WHERE a.`salesrep` = {$sr_id}
            AND a.`status` = 'active'
            AND a.`country_id` ={$this->config->item('country')}
            AND a.`salesrep` !=0
             
        ");
    }

    public function getFuturePendings_v2($params) {

        $str = "";
        $sel_str = "";
        if ($params['sel_query'] != "") {
            $sel_str = $params['sel_query'];
        } else if ($params['getCount'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else {


            if ($params['distinct'] != "") {

                if ($params['distinct'] == 'agency') {
                    $sel_str = " DISTINCT(p.`agency_id`), a.`agency_name` ";
                } else if ($params['distinct'] == 'state') {
                    $sel_str = " DISTINCT(p.`state`) ";
                }
            } else {
                $sel_str = "
                    CONCAT_WS('', LOWER(p.`address_1`), LOWER(p.`address_2`), LOWER(p.`address_3`), LOWER(p.`state`), LOWER(p.`postcode`) ),
                    j.`property_id`,
                    j.`date` AS jdate,
                    
                    a.`agency_id`,
                    a.`agency_name`,
                    
                    p.`address_1` AS p_address1, 
                    p.`address_2` AS p_address2, 
                    p.`address_3` AS p_address3, 
                    p.`state` AS p_state, 
                    p.`postcode` AS p_postcode
                ";
            }
        }


        if ($params['phrase'] != "") {
            $str .= " AND CONCAT_WS(' ', LOWER(p.`address_1`), LOWER(p.`address_2`), LOWER(p.`address_3`), LOWER(p.`state`), LOWER(p.`postcode`) ) LIKE '%" . strtolower(trim($params['phrase'])) . "%' ";
        }

        if ($params['state'] != "") {
            $str .= " AND p.`state` = '{$params['state']}' ";
        }

        if ($params['agency'] != "" && $params['agency'] != "Any") {
            $str .= " AND a.`agency_id` = {$params['agency']} ";
        }

        if ($params['region_postcodes'] != "") {
            $str .= " AND p.`postcode` IN ( {$params['region_postcodes']} ) ";
        }

        // paginate

        if ($params['offset'] != "" && $params['limit'] != "") {
            $str .= " LIMIT {$params['offset']}, {$params['limit']} ";
        } else if ($params['limit'] != "" && $params['offset'] == 0) {
            $str .= " LIMIT {$params['limit']} ";
        }




        if ($params['from'] != "" && $params['to'] != "") {

            //$next_month = date("m",strtotime("{$params['from']} +1 month"));
            $next_month = date("m", strtotime("{$params['from']}"));
            $last_year = date("Y", strtotime("{$params['from']} -1 year"));
            $last_day_of_month = date("t", strtotime("{$params['from']} -1 year"));
        } else {

            // default
            //$next_month = date("m",strtotime("+1 month"));
            $next_month = date("m");
            $last_year = date("Y", strtotime("-1 year"));
            $last_day_of_month = date("t", strtotime("-1 year"));
        }



        $j_str = "
            SELECT 
                {$sel_str}
            FROM `jobs` AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            INNER JOIN `property_services` AS ps ON ( j.`property_id` = ps.`property_id`
            AND j.`service` = ps.`alarm_job_type_id` )
            WHERE j.`status` = 'Completed'
            AND j.`job_type` = 'Yearly Maintenance'
            AND ps.`service` =1
            AND p.`deleted` =0
            AND a.`status` = 'active'
            AND j.`del_job` = 0
            AND a.`country_id` = {$this->config->item('country')}
            AND j.`date`
            BETWEEN '{$last_year}-{$next_month}-01'
            AND '{$last_year}-{$next_month}-{$last_day_of_month}'
            {$str}
        ";

        return $this->db->query($j_str);
    }

    public function getCompletedCount($from, $to, $serv_type, $job_type, $country_id, $return_data = 0, $agency_id=null) {

        if ($return_data == 1) {
            $sel_str = "CAST( j.`created` AS DATE ) AS jcreated, j.`date` ";
        } else {
            $sel_str = "count( j.`id` ) AS jtot ";
        }

        $jt_str = ($job_type != "") ? " AND j.`job_type` = '{$job_type}' " : '';
        $serv_type_str = ($serv_type >0) ? "AND j.`service` = {$serv_type}" : "";

        $ahc = "";
        if ($agency_id != "") {
            $ahc = " AND a.agency_id = {$agency_id} ";
        }

        $sql = "
            SELECT {$sel_str}
            FROM `jobs` AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE j.`status` = 'Completed'
            AND p.`deleted` = 0
            AND a.`status` = 'active'
            AND j.`del_job` = 0
            AND a.`country_id` = {$this->config->item('country')}
            {$serv_type_str}
            {$jt_str}		
            {$ahc}		
            AND (j.`date`
            BETWEEN '{$from}'
            AND '{$to}')
         ";

        $ci_sql = $this->db->query($sql);


        if ($return_data == 1) {
            return $ci_sql;
        } else {
            $ci = $ci_sql->row_array();
            return $ci['jtot'];
        }
    }

    public function daysToComplete($from, $to, $serv_type = '', $job_type = '', $min = '', $max = '', $country_id, $agency_id=null) {

        $jt_str = ($job_type != "") ? " AND j.`job_type` = '{$job_type}' " : '';

        $cr_from = date("Y-m-1", strtotime("{$from} -{$days} month"));
        $cr_to = date("Y-m-t", strtotime("{$to} -{$days} month"));
        //$cr_to = date("Y-m-t",strtotime($cr_to_temp));

        $ahc = "";
        if ($agency_id != "") {
            $ahc = " AND a.agency_id = {$agency_id} ";
        }

        $serv_type_str = ($serv_type >0) ? "AND j.`service` = {$serv_type}" : "";

      
        if ($min == $max) {

            //$cr_str = " AND CAST( j.`created` AS DATE ) <= DATE_SUB( j.`date` , INTERVAL {$min} DAY ) ";

            $cr_str = "AND DATEDIFF(j.`date`, Date(j.`created`)) >= {$max}";

        } else {

            //$cr_str = " AND ( CAST( j.`created` AS DATE ) BETWEEN DATE_SUB( j.`date` , INTERVAL {$max} DAY ) AND DATE_SUB( j.`date` , INTERVAL {$min} DAY ) ) ";
        
            $cr_str = "
            AND  
                CASE
                    WHEN ( Date(j.`created`) > j.`date` ) THEN 0    
                    ELSE DATEDIFF(j.`date`, Date(j.`created`))
                END  
            BETWEEN {$min} AND {$max}
            ";
        
        }
       
        

        // do not include DHA agencies
        $sql = "
            SELECT count( j.`id` ) AS jtot
            FROM `jobs` AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE j.`status` = 'Completed'
            AND a.`country_id` = {$this->config->item('country')}
            AND p.`deleted` = 0
            AND a.`status` = 'active'
            AND j.`del_job` = 0
            {$serv_type_str}
            {$jt_str}
            {$ahc}
            AND (
                j.`date` 
                BETWEEN '{$from}'
                AND '{$to}'
            )
            {$cr_str}
         ";

        $ci_sql = $this->db->query($sql);
        $ci = $ci_sql->row_array();
        return $ci['jtot'];
    }

    public function get_job_count($status, $job_type = "", $letter_sent = "", $lease_renewal = "") {
        $lease_renewal = ($lease_renewal !== "") ? " OR  j.job_type = '{$lease_renewal}'" : "";
        $job_type = ($job_type !== "") ? " AND ( j.`job_type` = '{$job_type}' {$lease_renewal} )" : "";
        $letter_sent = ($letter_sent !== "") ? " AND j.`letter_sent` ={$letter_sent}" : "";

        $sql = "
			SELECT COUNT(j.`id`) as j_count
			FROM jobs AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE j.status = '{$status}'
			AND p.deleted = '0'
            AND ( p.is_nlm = 0 OR p.is_nlm IS NULL )
			AND a.`status` = 'active'
			AND j.`del_job` = 0
			AND a.`country_id` = {$this->config->item('country')}
			{$job_type}
			{$letter_sent}
			
		";
        $row = $this->db->query($sql)->row();
        return $row->j_count;
    }

    public function get_services_total($ajt) {

        $fg = 14; // Defence Housing
        $fg_filter = "AND a.`franchise_groups_id` != {$fg}";

        $sql = "
		SELECT COUNT( ps.`property_services_id` ) AS jcount
		FROM `property_services` AS ps
		LEFT JOIN `property` AS p ON p.`property_id` = ps.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE ps.`alarm_job_type_id` ={$ajt}
		AND ps.`service` =1
		AND p.`deleted` = 0
        AND ( p.is_nlm = 0 OR p.is_nlm IS NULL )
		AND a.`status` = 'active'
        AND a.`deleted` = 0
        AND a.`country_id` = {$this->config->item('country')}  
        {$fg_filter}      
		";

        $sql = $this->db->query($sql)->row();
        return $sql->jcount;
        
    }

    public function get_urgent_count() {
        $urg_sql = $this->db->query("
            SELECT COUNT(j.`id`) as j_count
                FROM (
                jobs j, property p, agency a
                )
                WHERE a.agency_id = p.agency_id
                AND j.property_id = p.property_id
                AND p.deleted = '0'
                AND ( p.is_nlm = 0 OR p.is_nlm IS NULL )
                AND a.`status` = 'active'
                AND j.`del_job` = 0
                AND a.`country_id` = {$this->config->item('country')}
                AND j.status = 'To Be Booked'
                AND j.`urgent_job` = 1	
        ");

        $sql = $urg_sql->row();
        return $sql->j_count;
    }

    public function dha_count() {

        $fg = 14; // Defence Housing

        $dha_sql = $this->db->query("
            SELECT COUNT( ps.`property_services_id` ) AS num_serv
            FROM `property_services` AS ps
            LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE a.`franchise_groups_id` = {$fg}
            AND p.`deleted` =0
            AND a.`status` = 'active'
            AND ps.`alarm_job_type_id` =2
            AND ps.`service` =1
            AND a.`country_id` = {$this->config->item('country')}
        ");

        $row = $dha_sql->row();
        return $row->num_serv;
    }

    // get job type
    public function get_job_types($params) {

        $this->db->select('*');
        $this->db->from('`job_type`');

        // custom filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
        }

        // custom filter
        if (isset($params['custom_where_arr'])) {
            foreach ($params['custom_where_arr'] as $index => $custom_where) {
                if ($custom_where != '') {
                    $this->db->where($custom_where);
                }
            }
        }

        $query = $this->db->get();
        if (isset($params['display_query']) && $params['display_query'] == 1) {
            echo $this->db->last_query();
        }

        return $query;
    }

    public function getTotalUnpaidAmount($params) {

        $country_id = $this->config->item('country');
        $today = date('Y-m-d');

        $financial_year = $this->config->item('accounts_financial_year'); // 1/7/19

        $sel_query = "
        j.`id`, 
        j.`invoice_balance`, 
        j.`date`,
        DATE_ADD(j.`date`, INTERVAL 30 DAY) AS due_date, 
        DATEDIFF( '{$today}', j.`date`) AS DateDiff 
        ";


        // static financial year 
        $financial_year = $this->config->item('accounts_financial_year');
        // unpaid marker

        $custom_where = "`j`.`invoice_balance` >0
            AND `j`.`status` = 'Completed'
            AND a.`status` != 'target'
            AND (
                    j.`date` >= '$financial_year' OR
                    j.`unpaid` = 1	
            )";

        $job_params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'agency_filter' => $params['agency_id'],
            'country_id' => $country_id,
            'having' => $params['having'],
            'display_query' => $params['display_query']
        );

        $sql = $this->get_jobs($job_params);
        $tot = 0;
        foreach ($sql->result() as $row) {
            $tot += $row->invoice_balance;
        }

        return $tot;
    }

    public function get_last_visit_per_property($property_id) {

        $last_visit_sql = $this->db->query("
            SELECT `id`, `date`
            FROM `jobs`
            WHERE `property_id` = {$property_id}
            AND `status` = 'Completed'
            AND `assigned_tech` > 1
            AND `del_job` = 0
            ORDER BY `date` DESC
            LIMIT 1
        ");
        $lv_row = $last_visit_sql->row();
        return ( $this->system_model->isDateNotEmpty($lv_row->date) ) ? $this->system_model->formatDate($lv_row->date, 'd/m/Y') : null;
    }

    public function get_agency_old_escalate_notes($params = null) {

        $has_old_notes = 0;

        // get agency with saved notes
        $this->db->select('
            `agency_id`,
            `save_notes`,
            `escalate_notes`
        ');
        $this->db->from('`agency`');
        $this->db->where('`save_notes`', 1);
        $this->db->where("`escalate_notes` != ''");
        $agency_sql = $this->db->get();
        echo $this->db->last_query();

        echo "<br /><br />";

        foreach ($agency_sql->result() as $agency_row) {

            $agency_id = $agency_row->agency_id;
            $job_status = 'Escalate';
            $country_id = $this->config->item('country');

            $sel_query = "COUNT(j.`id`) AS jcount";

            $job_params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'job_status' => $job_status,
                'country_id' => $country_id,
                'agency_filter' => $agency_id,
                'display_query' => 1
            );
            $job_sql = $this->get_jobs($job_params);
            $job_sql_row = $job_sql->row();

            // no more escalate jobs on this agency
            if ($job_sql_row->jcount == 0) {

                $has_old_notes = 1;

                if ($params['clear'] == 1) {

                    // clear agency notes saved
                    if ($agency_id > 0) {

                        // `agency` notes
                        $data = array(
                            'save_notes' => null,
                            'escalate_notes' => null,
                            'escalate_notes_ts' => null
                        );

                        $this->db->where('agency_id', $agency_id);
                        $this->db->update('agency', $data);

                        // `escalate_agency_info` clear
                        $this->db->delete('escalate_agency_info', array('agency_id' => $agency_id));
                    }
                }
            }
        }

        return $has_old_notes;
    }

    public function get_job_last_contact($limit, $start, $order_by = '', $sort = '', $state = '',$agency_filter = NULL) {

        $date_delay = date('Y-m-d', strtotime('-14 days'));
        $tt_q = $this->db->query(
            "SELECT `p`.`property_id`,p.postpone_due_job,MAX( jl.`eventdate` ) AS last_contact
            FROM `job_log` AS `jl`
            LEFT JOIN `jobs` AS `j` ON jl.`job_id` = j.`id`
            LEFT JOIN `last_contact_comments` AS `lcc` ON j.`id` = lcc.`job_id`
            LEFT JOIN `property` AS `p` ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS `a` ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `agency_priority` AS `aght` ON a.`agency_id` = aght.`agency_id`
            LEFT JOIN `staff_accounts` AS `sa` ON sa.`StaffID`=j.`assigned_tech`
            WHERE `j`.`status` = 'To Be Booked'
                        AND `p`.`deleted` =0
                        AND (`p`.`is_nlm` =0 OR `p`.`is_nlm` IS NULL)
                        AND `a`.`status` = 'active'
                        AND `j`.`del_job` =0
                        AND `a`.`country_id` = {$this->config->item('country')}	
                        AND j.`status` != 'On Hold'
                        AND ( jl.`contact_type` = 'Phone Call' OR jl.`contact_type` = 'SMS sent' OR jl.`contact_type` = 'TY SMS Sent' 
                            OR jl.`contact_type` = 'SMS Entry Notice' OR jl.`contact_type` = 'Email Entry Notice' 
                            OR jl.`contact_type` = 'Email Template'
                            )
            GROUP BY `jl`.`job_id`
            HAVING `last_contact` <=  '{$date_delay}'
            UNION
            SELECT `p`.`property_id`,p.postpone_due_job,MAX( jl.`created_date` ) AS last_contact
            FROM `logs` AS `jl`
            LEFT JOIN `jobs` AS `j` ON jl.`job_id` = j.`id`
            LEFT JOIN `last_contact_comments` AS `lcc` ON j.`id` = lcc.`job_id`
            LEFT JOIN `property` AS `p` ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS `a` ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `agency_priority` AS `aght` ON a.`agency_id` = aght.`agency_id`
            LEFT JOIN `staff_accounts` AS `sa` ON sa.`StaffID`=j.`assigned_tech`
            WHERE `j`.`status` = 'To Be Booked'
                        AND `p`.`deleted` =0
                        AND (`p`.`is_nlm` =0 OR `p`.`is_nlm` IS NULL)
                        AND `a`.`status` = 'active'
                        AND `j`.`del_job` =0
                        AND `a`.`country_id` = {$this->config->item('country')}	
                        AND j.`status` != 'On Hold'
                        AND jl.title IN (54,57,78,40,53,60)
            GROUP BY `jl`.`job_id`
            HAVING `last_contact` <=  '{$date_delay}'
            "
        );
        $ttmo = array();
        foreach( $tt_q->result_array() as $tt_row ){
            $postpone_due_job_date = date('Y-m-d', strtotime($tt_row['postpone_due_job']));
            $date_now = date('Y-m-d');
            if ($postpone_due_job_date <= $date_now) {
                // do nothing
            } else {
                $ttmo[] = intval($tt_row['property_id']);
            }
        }

        if( !empty($ttmo) ){
            $ttmo_implode = implode(", ", $ttmo);
            $property_id_not_in = "AND p.property_id NOT IN(".$ttmo_implode.")";
        }
        $filter_str = '';
        if ($state != '') {
            $filter_str = ' AND p.`state` = "'.$this->db->escape_str($state).'" ';
        }
        $agency = '';
        if ($agency_filter != '') {
            $agency = ' AND a.`agency_id` = "'.$this->db->escape_str($agency_filter).'" ';
        }
        $order = '';
        if ($sort != "" && $order_by != "") {
            // $this->db->order_by($order_by, $sort);
            $order = "ORDER BY $order_by $sort";
        }
        $limit = '';
        if (is_numeric($start) && is_numeric($limit)) {
            // $this->db->limit($start, $limit);
            $limit = "LIMIT $start, $limit";
        }
        
        $data = $this->db->query(
            "SELECT MAX( jl.`eventdate` ) AS last_contact,
                j.`id` AS jid,
                j.`created` AS jcreated, 
                j.`date` AS jdate, 
                j.`job_type`, 
                j.`service` AS jservice, 
                j.`job_price`,
                j.`comments`,

                lcc.`comments` AS lcc_comments,

                p.`property_id`,
                p.`address_1` AS p_address_1,
                p.`address_2` AS p_address_2,
                p.`address_3` AS p_address_3,
                p.`state` AS p_state,
                
                a.`agency_id`,
                a.`agency_name`,
                aght.priority,
                apmd.abbreviation,
                                
                sa.FirstName,
                sa.LastName
            FROM `job_log` AS `jl`
            LEFT JOIN `jobs` AS `j` ON jl.`job_id` = j.`id`
            LEFT JOIN `last_contact_comments` AS `lcc` ON j.`id` = lcc.`job_id`
            LEFT JOIN `property` AS `p` ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS `a` ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `agency_priority` AS `aght` ON a.`agency_id` = aght.`agency_id`
            LEFT JOIN `agency_priority_marker_definition` AS `apmd` ON aght.`priority` = apmd.`priority`
            LEFT JOIN `staff_accounts` AS `sa` ON sa.`StaffID`=j.`assigned_tech`
            WHERE `j`.`status` = 'To Be Booked'
                        AND `p`.`deleted` =0
                        AND (`p`.`is_nlm` =0 OR `p`.`is_nlm` IS NULL)
                        AND `a`.`status` = 'active'
                        AND `j`.`del_job` =0
                        AND `a`.`country_id` = {$this->config->item('country')}	
                        AND j.`status` != 'On Hold'
                        AND ( jl.`contact_type` = 'Phone Call' OR jl.`contact_type` = 'SMS sent' OR jl.`contact_type` = 'TY SMS Sent' 
                            OR jl.`contact_type` = 'SMS Entry Notice' OR jl.`contact_type` = 'Email Entry Notice' 
                            OR jl.`contact_type` = 'Email Template'
                            )
                        {$filter_str}
                        {$agency}
                        {$property_id_not_in}
                        {$agency_priority}
            GROUP BY `jl`.`job_id`
            HAVING `last_contact` <=  '{$date_delay}'
            UNION
            SELECT MAX( jl.`created_date` ) AS last_contact,
                j.`id` AS jid,
                j.`created` AS jcreated, 
                j.`date` AS jdate, 
                j.`job_type`, 
                j.`service` AS jservice, 
                j.`job_price`,
                j.`comments`,

                lcc.`comments` AS lcc_comments,

                p.`property_id`,
                p.`address_1` AS p_address_1,
                p.`address_2` AS p_address_2,
                p.`address_3` AS p_address_3,
                p.`state` AS p_state,
                
                a.`agency_id`,
                a.`agency_name`,
                aght.priority,
                apmd.abbreviation,
                                
                sa.FirstName,
                sa.LastName
            FROM `logs` AS `jl`
            LEFT JOIN `jobs` AS `j` ON jl.`job_id` = j.`id`
            LEFT JOIN `last_contact_comments` AS `lcc` ON j.`id` = lcc.`job_id`
            LEFT JOIN `property` AS `p` ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS `a` ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `agency_priority` AS `aght` ON a.`agency_id` = aght.`agency_id`
            LEFT JOIN `agency_priority_marker_definition` AS `apmd` ON aght.`priority` = apmd.`priority`
            LEFT JOIN `staff_accounts` AS `sa` ON sa.`StaffID`=j.`assigned_tech`
            WHERE `j`.`status` = 'To Be Booked'
                        AND `p`.`deleted` =0
                        AND (`p`.`is_nlm` =0 OR `p`.`is_nlm` IS NULL)
                        AND `a`.`status` = 'active'
                        AND `j`.`del_job` =0
                        AND `a`.`country_id` = {$this->config->item('country')}	
                        AND j.`status` != 'On Hold'
                        AND jl.title IN (54,57,78,40,53,60)
                        {$filter_str}
                        {$agency}
                        {$property_id_not_in}
                        {$agency_priority}
            GROUP BY `jl`.`job_id`
            HAVING `last_contact` <=  '{$date_delay}'
            {$order}
            {$limit}
            "
        );
        
        $query = $data;
        return $query;
    }

    public function getTotalUnpaidAmount_by_jobid($params) {

        $country_id = $this->config->item('country');
        $today = date('Y-m-d');

        $financial_year = $this->config->item('accounts_financial_year'); // 1/7/19

        $sel_query = "
        j.`id`, 
        j.`invoice_balance`, 
        j.`date`,
        DATE_ADD(j.`date`, INTERVAL 30 DAY) AS due_date, 
        DATEDIFF( '{$today}', j.`date`) AS DateDiff 
        ";


        // static financial year 
        $financial_year = $this->config->item('accounts_financial_year');
        // unpaid marker

        $custom_where = "`j`.`invoice_balance` >0
            AND `j`.`status` = 'Completed'
            AND a.`status` != 'target'
            AND (
                    j.`date` >= '$financial_year' OR
                    j.`unpaid` = 1	
            )";

        $job_params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'agency_filter' => $params['agency_id'],
            'job_id' => $params['job_id'],
            'country_id' => $country_id,
            'having' => $params['having'],
            'display_query' => $params['display_query']
        );

        $sql = $this->get_jobs($job_params);
        
        $sql_row = $sql->row_array();
        $tot = $sql_row['invoice_balance'];

        return $tot;
    }


    public function merged_jobs_sms_send_model($country_id='') {

        ini_set('max_execution_time', 600); 
        $this->load->model('/inc/job_functions_model');
        $country_id = $country_id == '' ? $this->config->item('country') : $country_id;

        $sql_str = "SELECT j.`sms_sent_merge`,
                    p.`property_id`,
                    j.`booked_with`,
                    j.`id` AS jid,
                    j.assigned_tech,
                    j.door_knock,

                    p.`tenant_mob1`,
                    p.`tenant_mob2`,
                    p.`tenant_mob3`,
                    p.`tenant_mob4`,

                    p.`tenant_firstname1`,
                    p.`tenant_lastname1`,
                    p.`tenant_firstname2`,
                    p.`tenant_lastname2`,
                    p.`tenant_firstname3`,
                    p.`tenant_lastname3`,
                    p.`tenant_firstname4`,
                    p.`tenant_lastname4`,

                    p.`tenant_firstname1`,
                    c.`phone_prefix`,
                    a.`agency_id`
        
                    FROM jobs AS j
                    LEFT JOIN property AS p ON j.property_id = p.property_id
                    LEFT JOIN agency AS a ON p.agency_id = a.agency_id
                    LEFT JOIN staff_accounts AS sa ON j.assigned_tech = sa.StaffID
                    LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`   
                    WHERE j.status = 'Merged Certificates'
                    AND a.`country_id` = {$country_id}
                    AND p.`deleted` =0
                    AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
                    AND a.`status` = 'active'
                    AND a.`deleted` = 0
                    AND j.`del_job` = 0
                    AND (!(j.`assigned_tech` <=> 1) AND !(j.`assigned_tech` <=> 2))
                    ";
        
        $mj_sql = $this->db->query($sql_str);

        $sms_provider = "@app.wholesalesms.com.au";
        $staff_id =  $this->session->staff_id;
        $sent_by = $staff_id;
        $sms_type = 18; // SMS (Thank You)

        $sms_count = 0;
        foreach($mj_sql->result_array() as $row){
            
            if( date("Y-m-d",strtotime($row['sms_sent_merge'])) != date("Y-m-d") ){

                // get phone prefix
                $prefix = $row['phone_prefix'];
                
                // new tenants switch
                //$new_tenants = 0;
                $new_tenants = $this->config->item('sats_new_tenant');

                if( $new_tenants == 1 ){ // NEW TENANTS

                    $pt_params = array( 
                        'property_id' => $row['property_id'],
                        'active' => 1
                     );
                    $pt_sql = $this->job_functions_model->getNewTenantsData($pt_params);

                    foreach($pt_sql->result_array() as $pt_row){
                        
                        $send_sms = false;
                        if( $pt_row['tenant_mobile'] != "" && $pt_row['tenant_firstname'] == $row['booked_with'] ){
                            $send_sms = true;
                        }else {
                            if($row['booked_with'] == "Agent" && $row['door_knock'] == 1 ) {
                                $send_sms = true;
                            }
                        }

                        // loop through tenants, send sms only on tenants that are booked with
                        if( $send_sms ){ 

                            // tenant name 
                            $ten_name = "{$pt_row['tenant_firstname']} {$pt_row['tenant_lastname']}";

                            // tenant mobile 
                            $trim = str_replace(' ', '', trim($pt_row['tenant_mobile']));

                            // reformat number
                            $remove_zero = substr($trim ,1);
                            $mob = $prefix.$remove_zero;
                            
                            // SMS body
                            // get template content
                            $sel_query = "sms_api_type_id, body";
                            $params = array(
                                'sel_query' => $sel_query,
                                'active' => 1,
                                'sms_api_type_id' => $sms_type,
                                'display_query' => 0
                            );
                            $sms_temp_sql = $this->sms_model->getSmsTemplates($params);
                            $sms_temp_row = $sms_temp_sql->row();
                            $unparsed_template = $sms_temp_row->body;                       

                            // parse tags
                            $sms_params = array(
                                'job_id' => $row['jid'],
                                'unparsed_template' => $unparsed_template
                            );
                            $parsed_template_body = $this->sms_model->parseTags($sms_params);   
                            
                            // send SMS via API
                            $sms_params = array(
                                'sms_msg' => $parsed_template_body,
                                'mobile' => $mob
                            );
                            $sms_res = $this->sms_model->sendSMS($sms_params);   

                            // save SMS data on database
                            $sms_params = array(
                                'sms_json' => $sms_res,
                                'job_id' => $row['jid'],
                                'message' => $parsed_template_body,
                                'mobile' => $mob,
                                'sent_by' => $sent_by,
                                'sms_type' => $sms_type,
                            );
                            $this->sms_model->captureSmsData($sms_params);
                            
                            //insert log
                            $log_details = "SMS to {$ten_name} ({$mob}) <strong>\"{$parsed_template_body}\"</strong>";
                            $log_params = array(
                                'title' => 40, // SMS sent
                                'details' => $log_details,
                                'display_in_vjd' => 1,
                                'job_id' => $row['jid'],
                                'property_id' => $row['property_id'],
                                'agency_id' => $row['agency_id']
                            );

                            // if not CRON, user logged
                            if($this->session->staff_id !='' ){
                                $append_jlval = $this->session->staff_id;
                                $log_params['created_by_staff'] = $append_jlval;
                            }else{
                                $append_jlval = 1;
                                $log_params['auto_process'] = $append_jlval;
                            }

                            $this->system_model->insert_log($log_params);
                            
                            // update sms sent
                            $this->db->query("
                                UPDATE `jobs`
                                SET `sms_sent_merge` = '".date("Y-m-d H:i:s")."'
                                WHERE `id` = {$row['jid']}
                            ");
                            $sms_count++;

                        }
                        
                    }

                }       
                
            }

        }

        return $sms_count;
    }

    function job_has_alarms($job_id){

		$sql = $this->db->query("
			SELECT COUNT(`alarm_id`) AS a_count
			FROM `alarm`
			WHERE `job_id` = {$job_id}
		");
		$count = $sql->row()->a_count;
		return ( $count > 0 )?true:false;

    }
    
    public function get_last_completed_job($params){

        $property_id = $params['property_id'];
        $sel_query = $params['sel_query'];
    
        if( $property_id > 0 && $sel_query != '' ){

            $sql_str = "
                SELECT $sel_query
                FROM `jobs` AS j
                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                WHERE p.`property_id` = {$property_id}
                AND j.`status` = 'Completed'
                AND p.`deleted` = 0
                AND j.`del_job` = 0
                AND a.`status` = 'active'
                AND j.`assigned_tech` NOT IN(1,2)
                ORDER BY j.`date` DESC
                LIMIT 1
            ";

            if( $params['display_query'] == 1 ){
                echo $sql_str;
            }
            return $this->db->query($sql_str);

        }        
        
    }

    public function display_ceiling_type_full($ceiling_type){

        switch($ceiling_type){

            case 'CON':
                $ceiling_type_full = 'Concrete';
            break;

            case 'GYP':
                $ceiling_type_full = 'Gyprock';
            break;

        }

        return $ceiling_type_full;

    }


    public function send_sms_to_booked_with_tenant($params){

        $job_id = $params['job_id'];        
        $staff_id = $params['staff_id'];
        $sms_type = $params['sms_type'];
        $country_id = $this->config->item('country');    
        
        $auto_process =  ( $staff_id > 0 )?0:1;       
        $today_full = date("Y-m-d H:i:s");

        if( $job_id > 0 ){

            // get job data
            $sel_query = "
                j.`booked_with`,
                p.`property_id`
            ";
            
            $job_params = array(
                'sel_query' => $sel_query,
                'del_job' => 0,
                'p_deleted' => 0,
                'a_status' => 'active',          
                'country_id' => $country_id,  
                'job_id' => $job_id,                      
                'display_query' => 0
            );
            $job_sql = $this->get_jobs($job_params);
            $job_row = $job_sql->row();
            $property_id = $job_row->property_id; // get property ID
            $booked_with = $job_row->booked_with; // get booked with tenant

            if( $property_id > 0 ){

                // get tenants 
                $sel_query = "
                    pt.`property_tenant_id`,
                    pt.`tenant_firstname`,
                    pt.`tenant_lastname`,
                    pt.`tenant_mobile`
                ";
                $params = array(
                    'sel_query' => $sel_query,
                    'property_id' => $property_id,
                    'pt_active' => 1,
                    'display_query' => 0
                );
                $pt_sql = $this->properties_model->get_property_tenants($params);

                foreach($pt_sql->result() as $pt_row){

                    
                    // only SMS to booked with tenants
                    if( $pt_row->tenant_mobile != "" && $pt_row->tenant_firstname == $booked_with ){

                        // booked with tenant
                        $booked_with_tenant = "{$pt_row->tenant_firstname} {$pt_row->tenant_lastname}";

                        // format phone number
                        $send_to = $this->sms_model->formatToInternationNumber($pt_row->tenant_mobile);                          
                        
                        // SMS body
                        // get template content
                        $sel_query = "sms_api_type_id, body";
                        $params = array(
                            'sel_query' => $sel_query,
                            'active' => 1,
                            'sms_api_type_id' => $sms_type,
                            'display_query' => 0
                        );
                        $sql = $this->sms_model->getSmsTemplates($params);
                        $row = $sql->row();
                        $unparsed_template = $row->body;                           

                        // parse tags
                        $sms_params = array(
                            'job_id' => $job_id,
                            'unparsed_template' => $unparsed_template
                        );
                        $parsed_template_body = $this->sms_model->parseTags($sms_params);                        
                                            
                        // send SMS
                        $sms_params = array(
                            'sms_msg' => $parsed_template_body,
                            'mobile' => $send_to
                        );
                        $sms_json = $this->sms_model->sendSMS($sms_params);

                        // save SMS data on database
                        $sms_params = array(
                            'sms_json' => $sms_json,
                            'job_id' => $job_id,
                            'message' => $parsed_template_body,
                            'mobile' => $send_to,
                            'sent_by' => $staff_id,
                            'sms_type' => $sms_type,
                        );
                        $this->sms_model->captureSmsData($sms_params);                        

                        // insert job logs            
                        $log_title = 40; // SMS sent
                        $job_log = "SMS to {$booked_with_tenant} ({$send_to}) <b>\"{$parsed_template_body}\"</b>";                        
                        $log_params = array(
                            'title' => $log_title,
                            'details' => $job_log,
                            'display_in_vjd' => 1,
                            'created_by_staff' => $staff_id,
                            'auto_process' => $auto_process,
                            'job_id' => $job_id
                        );
                        $this->system_model->insert_log($log_params); 

                        $sms_sent = true;                                                                                                         

                    }
                
                }

            }
                   
            
            if( $sms_sent == true && $sms_type == 4 ){ // No-Show

                // no show sms sent marker
                $update_sql_str = "
                    UPDATE jobs
                    SET `sms_sent_no_show` = '{$today_full}'
                    WHERE `id` = {$job_id}
                ";
                $this->db->query($update_sql_str);  

            }                                           
            

        }


    }

    /**
     * By Gherx
     * getJobPriceTotal version 2
     * Get price total exclusive GST
     */
    public function getJobPriceTotal_v2($agency_id, $from, $to) {

        $str = "";
        if ($from != 'all' && $to != 'all') {
            $from2 = date("Y-m-d", strtotime(str_replace("/", "-", $from)));
            $to2 = date("Y-m-d", strtotime(str_replace("/", "-", $to)));
            $str = "AND CAST(j.`created` AS DATE) BETWEEN '{$from2}' AND '{$to2}'";
        }

        $sql_str = "
            SELECT j.id, j.job_price
            FROM jobs AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE a.`agency_id` ={$agency_id}
            AND a.`status` = 'active'
            AND j.`del_job` =0
            AND a.`country_id` = {$this->config->item('country')}
            {$str}
        ";

        $sql = $this->db->query($sql_str);
        $row_sql = $sql->result_array();

        $tot = 0;
        foreach($row_sql as $row){
            $ex_gst_price = $this->system_model->price_ex_gst($row['job_price']);
            $tot += $ex_gst_price;
        }

        return $tot;

    }

 
    //GET agency is from property
    public function getAgencyId($prop_id) {
        return $this->db->select('agency_id')
        ->from('property')
        ->where('property_id', $prop_id)
        ->get()->result_object();
        $this->db->get('property');
    }//endfct

    public function new_platform_invoicing($params){

        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }
    
        if($params['sel_query']!=""){
            $sel_query = $params['sel_query'];
        }else{
            $sel_query = "j.`id` AS jid, j.`date` AS jdate, j.`work_order`, j.`mm_need_proc_inv_emailed`, j.`client_emailed`, j.`ts_completed`, j.`qld_upgrade_quote_emailed`, j.`dha_need_processing`, p.`property_id`, p.`address_1` AS p_address_1, p.`address_2` AS p_address_2, p.`address_3` AS p_address_3, p.`state` AS p_state, p.`prop_upgraded_to_ic_sa`, p.`qld_new_leg_alarm_num`,  apd.`api_prop_id`, apd.`api`, a.`agency_id` AS a_id, a.`agency_name`, a.`franchise_groups_id`, a.`palace_supplier_id`, a.`palace_diary_id`, a.`pme_supplier_id`, m.`name` AS m_name, aat.`connection_date`";
        }
    
        //filter
        $filter_arr = array();
        if ($params['date'] != '') {
            $filter_arr[] = "AND j.`date` = '{$params['date']}'";
        }
    
        if ($params['phrase'] != '') {
            $filter_arr[] = "AND (
                (CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$params['phrase']}%') OR
                (a.`agency_name` LIKE '%{$params['phrase']}%')
             )";
        }
    
        if ($params['maintenance_id'] != "") {
            if( $params['maintenance_id']==14 ){ //DHA filter
                $filter_arr[] = "AND a.`franchise_groups_id` = 14";
            }else{
                $filter_arr[] = "AND am.`maintenance_id` = {$params['maintenance_id']}";
            }
        }
    
        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = implode(" ", $filter_arr);
        }
        //filter end
    
    
        $new_query = "
            SELECT {$sel_query}
            FROM `jobs` AS j 
            INNER JOIN (
                SELECT j2.id,j2.property_id,a2.franchise_groups_id
                FROM jobs as j2
                LEFT JOIN `property` AS p2 ON j2.`property_id` = p2.`property_id` 
                LEFT JOIN `agency` AS a2 ON p2.`agency_id` = a2.`agency_id`  
            
                WHERE j2.`id` > 0 
                AND j2.`del_job` = 0 
                AND p2.`deleted` = 0 
                AND a2.`status` = 'active' 
                AND j2.`dha_need_processing` = 1 
                AND ( j2.`status` = 'Merged Certificates' OR j2.`status` = 'Completed' )
                AND ( j2.`assigned_tech` != 1 OR j2.`assigned_tech` IS NULL )
            
            )as tt ON j.id = tt.id
    
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
            LEFT JOIN `api_property_data` AS apd ON j.`property_id` = apd.`crm_prop_id` 
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
            LEFT JOIN `agency_maintenance` AS am ON a.`agency_id` = am.`agency_id` 
            LEFT JOIN `maintenance` AS m ON am.`maintenance_id` = m.`maintenance_id` 
            LEFT JOIN `agency_api_tokens` AS aat ON a.`agency_id` = aat.`agency_id` AND aat.`api_id` = 1 
    
            WHERE j.`id` > 0 
            AND ( (am.`maintenance_id` > 0 AND am.`status` = 1 AND m.`status` = 1 AND j.`date` >= am.`updated_date`  )  OR tt.franchise_groups_id = 14)
            AND j.`del_job` = 0 
            AND p.`deleted` = 0 
            AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
            AND a.`status` = 'active' 
            AND j.`dha_need_processing` = 1 
            AND ( j.`status` = 'Merged Certificates' OR j.`status` = 'Completed' )
            AND ( j.`assigned_tech` != 1 OR j.`assigned_tech` IS NULL )
            {$filter_str}
            GROUP BY {$params['group_by']}
            ORDER BY a.`agency_name` ASC 
            {$pag_str}
        ";
        
        if ($params['display_echo'] == 1) {
            echo $new_query;
        }
    
        return $this->db->query($new_query);
    }

    public function get_agency_priority_marker_abbreviation()
    {
        return $this->db->select('apmd.*')->from('agency_priority_marker_definition as apmd')->get()->result();
    }

}
