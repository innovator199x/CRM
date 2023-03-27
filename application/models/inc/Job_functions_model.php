<?php

class Job_functions_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
        $this->load->model('/inc/functions_model');
    }


    function getJobDetails2($job_id, $query_only = false){

        $query = $this->db->query("SELECT j.id, DATE_FORMAT(j.date,'%d/%m/%Y') as date, j.date as date_no_format, j.status, j.comments, j.retest_interval, j.auto_renew, j.job_type, 
        sa.FirstName, sa.LastName, p.address_1, p.address_2, p.address_3, p.state, p.postcode, j.time_of_day, j.assigned_tech, 
        p.tenant_firstname1, p.tenant_lastname1, p.tenant_ph1, j.tech_comments, p.property_id, p.tenant_firstname2, p.tenant_lastname2, p.tenant_ph2, 
        a.agency_id, a.agency_name, a.send_emails, a.address_1 AS agent_address_1, a.address_2 AS agent_address_2, a.address_3 AS agent_address_3, a.phone AS agent_phone, a.state AS agent_state, a.postcode  AS agent_postcode 
        , j.job_price, j.price_used, p.price, j.work_order , j.ts_noshow, 
        DATE_FORMAT(j.client_emailed, '%e/%m/%Y @ %r' ) AS LastSent, ts_doorknock, p.agency_deleted, a.send_combined_invoice, 
        DATE_FORMAT(j.date, '%d/%m/%Y') AS date, j.key_access_required, p.tenant_email1, p.tenant_email2, p.tenant_mob1, p.tenant_mob2,
        DATE_FORMAT(j.entry_notice_emailed, '%d/%m/%Y @ %r') AS EntryNoticeLastSent, sa.ContactNumber, 
        DATE_FORMAT(j.date, '%W') as booking_date_name,
        DATE_FORMAT(j.date, '%d') AS booking_date_day,
        DATE_FORMAT(j.date, '%m') AS booking_date_month,
        DATE_FORMAT(j.date, '%Y') AS booking_date_year,
        a.agency_emails,
        a.send_entry_notice,
        DATE_FORMAT(DATE_ADD(j.date, INTERVAL 1 YEAR), '%d/%m/%Y') AS retest_date,
        j.ss_location,
        j.ss_quantity,
        j.tmh_id,
        j.ts_db_reading, p.key_number, 
        j.price_reason, 
        j.price_detail, 
        j.service AS jservice,              
        a.`country_id`,
        j.`ps_qld_leg_num_alarm`,
        a.`account_emails`,
        p.`qld_new_leg_alarm_num`,
        a.`display_bpay`,
        j.`show_as_paid`,
        j.`invoice_balance`,
        j.`invoice_payments`,
        a.`allow_upfront_billing`,
        j.`date` AS jdate,
        p.`prop_upgraded_to_ic_sa`,
        j.`prop_comp_with_state_leg`,
        j.`property_leaks`,
        j.`we_techconfirm`,
        j.`we_items_tested`,
        j.`leak_notes`,
        j.`we_sync`,
        apd.`api_prop_id` AS palace_prop_id,
        p.`holiday_rental`, 
        am.`marker_id`,
        j.`invoice_amount`,        
        j.`due_date`
        
        FROM `jobs` AS j
        LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN `api_property_data` AS apd ON (j.`property_id` = apd.`crm_prop_id` AND apd.`api` = 4)
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
        LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID` 
        LEFT JOIN `agency_markers` AS am ON a.`agency_id` = am.`agency_id` 
        WHERE  j.`id` = {$job_id}");

        if(!$query_only)
        {
            $job_details = $query->row_array();
            return $job_details;
        }
        else
        {
            return $query;
        }

    }

    function getTechSheetAlarmTypesJob($property_id, $fixed = false)
    {
        $fixed_array = array();

        $sql = "SELECT ppt.alarm_job_type_id, ajt.type, ajt.html_id, ajt.include_file FROM property_propertytype ppt, alarm_job_type ajt
                WHERE ppt.alarm_job_type_id = ajt.id AND ppt.property_id = {$property_id} ORDER BY ajt.id ASC";

        $result = $this->functions_model->mysqlMultiRows($sql);

        if($fixed)
        {
            if(is_array($result) && sizeof($result) > 0)
            {
                foreach($result as $row)
                {
                    $fixed_array[$row['alarm_job_type_id']] = $row['type'];
                }
            }
            return $fixed_array;
        }
        else
        {
            return $result;
        }
    }

    // get service
    function getService($serv_id){
        return $this->db->query("
            SELECT *
            FROM `alarm_job_type`
            WHERE `id` = {$serv_id}
            AND `active` = 1
        ");
    }
       
    public function getCountryText($country_id){
        
        $country_id2 = ($country_id=="")?$this->config->item('country'):$country_id;

        $c_sql = $this->db->query("SELECT *
                        FROM `countries` 
                        WHERE `country_id` = {$country_id2}
                    ");

        $c = $c_sql->row();
        
        switch($c->country_id){
            case 1:
                $country_text = 'Australian';
            break;
            case 2:
                $country_text = 'New Zealand';
            break;
            case 3:
                $country_text = 'Canadian';
            break;
            case 4:
                $country_text = 'British';
            break;
            case 5:
                $country_text = 'American';
            break;
        }
        
        return $country_text;
    }


    /**
     * Some of the legacy pages run the MYSQL loop manually on the page so need the $query_only option
     */
    public function getJobDetails($job_id, $query_only = false)
    {

        // improved query
        $query = "SELECT j.id, DATE_FORMAT(j.date,'%d/%m/%Y'), j.status, j.comments, j.retest_interval, j.auto_renew, j.job_type, 
        sa.FirstName, sa.LastName, p.address_1, p.address_2, p.address_3, p.state, p.postcode, j.time_of_day, j.assigned_tech, 
        p.tenant_firstname1, p.tenant_lastname1, p.tenant_ph1, j.tech_comments, p.property_id, p.tenant_firstname2, p.tenant_lastname2, p.tenant_ph2, 
        a.agency_id, a.agency_name, a.send_emails, a.address_1 AS agent_address_1, a.address_2 AS agent_address_2, a.address_3 AS agent_address_3, a.phone AS agent_phone, 
        a.state AS agent_state, a.postcode  AS agent_postcode 
        , j.job_price, j.price_used, p.price, j.work_order , j.ts_noshow, 
        DATE_FORMAT(j.client_emailed, '%e/%m/%Y @ %r' ) AS LastSent, ts_doorknock, p.agency_deleted, a.send_combined_invoice, 
        DATE_FORMAT(j.date, '%d/%m/%Y') AS date, j.key_access_required, p.tenant_email1, p.tenant_email2, p.tenant_mob1, p.tenant_mob2,
        DATE_FORMAT(j.entry_notice_emailed, '%d/%m/%Y @ %r') AS EntryNoticeLastSent, sa.ContactNumber, 
        DATE_FORMAT(j.date, '%W') as booking_date_name,
        DATE_FORMAT(j.date, '%d') AS booking_date_day,
        DATE_FORMAT(j.date, '%m') AS booking_date_month,
        DATE_FORMAT(j.date, '%Y') AS booking_date_year,
        a.agency_emails,
        a.send_entry_notice,
        DATE_FORMAT(DATE_ADD(j.date, INTERVAL 1 YEAR), '%d/%m/%Y') AS retest_date,
        j.ss_location,
        j.ss_quantity,
        j.tmh_id,
        j.`start_date`,
        j.`due_date`,
        a.`agency_emails`,
        j.`del_job`,
        j.`booked_with`,
        j.`booked_by`,
        j.ts_db_reading, 
        p.key_number, 
        j.price_reason, 
        j.price_detail, 
        j.`urgent_job`, 
        j.`urgent_job_reason`, 
        p.`tenant_changed`, 
        j.`date` AS jdate, 
        p.`holiday_rental`, 
        p.`alarm_code`,
        a.`key_email_req`,
        j.`preferred_time`,
        j.`ps_qld_leg_num_alarm`,
        j.`allocate_opt`,
        j.`allocate_notes`,
        j.`allocated_by`,
        p.`no_en`,
        j.`property_vacant`,
        p.`address_1` AS p_street_num,
        p.`address_2` AS p_street_name,
        p.`address_3` AS p_suburb,
        p.`qld_new_leg_alarm_num`,
        
        p.`tenant_firstname3`, 
        p.`tenant_lastname3`, 
        p.`tenant_ph3`, 
        p.`tenant_mob3`, 
        p.`tenant_email3`,  
        
        p.`tenant_firstname4`, 
        p.`tenant_lastname4`, 
        p.`tenant_ph4`, 
        p.`tenant_mob4`, 
        p.`tenant_email4`,
        
        j.`dha_need_processing`,
        j.`out_of_tech_hours`,
        
        j.`call_before`,
        j.`call_before_txt`,
        
        a.`account_emails`,
        
        a.`franchise_groups_id`,
        
        j.`show_as_paid`,
        j.`to_be_printed`,
        j.`repair_notes`,
        
        p.`prop_upgraded_to_ic_sa`,
        
        j.`job_priority`,
        
        `invoice_amount`,
        `invoice_payments`,
        `invoice_credits`,
        `invoice_balance`,
        
        a.`key_allowed`,
        a.`electrician_only`,
        
        j.`status` AS jstatus,
        j.`assigned_tech`
        
        
        FROM `jobs` AS j
        LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
        LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID` 
        WHERE  j.`id` = {$job_id}";

        if(!$query_only)
        {
            $job_details = mysqlSingleRow($query);
            return $job_details;
        }
        else
        {
            return $query;
        }
    }

    // get tenants from new tenants table
    public function getNewTenantsData($params) {


        // filters
        $filter_arr = array();

        $join_table_imp = '';
        $custom_table_join = '';
        $custom_filter_str = '';
        $group_by_str = '';
        $sort_str = '';
        $pag_str = '';

        if (isset($params['pt_id']) && $params['pt_id'] != "") {
            $filter_arr[] = "AND pt.`property_tenant_id` = {$params['pt_id']} ";
        }

        if (isset($params['property_id']) && $params['property_id'] != "") {
            $filter_arr[] = "AND pt.`property_id` = {$params['property_id']} ";
        }

        if (isset($params['pm_tenant_id']) && $params['pm_tenant_id'] != "") {
            $filter_arr[] = "AND pt.`pm_tenant_id` = {$params['pm_tenant_id']} ";
        }

        if (isset($params['active']) && $params['active'] != "") {
            $filter_arr[] = "AND pt.`active` = {$params['active']} ";
        }

        /*
          if($params['filterDate']!=''){
          if( $params['filterDate']['from']!="" && $params['filterDate']['to']!="" ){
          $filter_arr[] = " AND ( pt.`createdDate` BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}' ) ";
          }
          }

          if($params['phrase']!=''){
          $filter_arr[] = "
          AND (
          (CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$params['phrase']}%') OR
          (a.`agency_name` LIKE '%{$params['phrase']}%')
          )
          ";
          }
         */

        // combine all filters
        $filter_str = " WHERE pt.`property_tenant_id` > 0 " . implode(" ", $filter_arr);


        //custom query
        if (isset($params['custom_filter']) && $params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        if (isset($params['custom_select']) && $params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        } else if (isset($params['return_count']) && $params['return_count'] == 1) {
            $sel_str = " COUNT(pt.`property_tenant_id`) AS jcount ";
        } else if (isset($params['distinct_sql']) && $params['distinct_sql'] != "") {

            $sel_str = " DISTINCT {$params['distinct_sql']} ";
        } else {
            $sel_str = " * ";
        }




        // sort
        if (isset($params['sort_list']) && $params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }


        // GROUP BY
        if (isset($params['group_by']) && $params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if (isset($params['paginate']) && $params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }


        if (isset($params['custom_join_table']) && $params['custom_join_table'] != '') {
            $custom_table_join = $params['custom_join_table'];
        }


        $sql = "        
            SELECT {$sel_str}
            FROM `property_tenants` AS pt 
            {$join_table_imp}
            {$custom_table_join}
            {$filter_str}   
            {$custom_filter_str}
            {$group_by_str}
            {$sort_str}
            {$pag_str}
            
        ";

        if (isset($params['echo_query']) && $params['echo_query'] == 1) {
            echo $sql;
        }

        if (isset($params['return_count']) && $params['return_count'] == 1) {
            $j_sql = $this->db->query($sql);
            return $j_sql->num_rows();
        } else {
            return $this->db->query($sql);
        }
    }



}
