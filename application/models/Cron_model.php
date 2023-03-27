<?php

class Cron_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
        $this->load->model('jobs_model');
        $this->load->model('properties_model');
        $this->load->model('sms_model');
        $this->load->model('daily_model');
        $this->load->model('inc/email_functions_model');
    }

  
    public function getCronLogs($params){

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`cron_log` AS cl');
        $this->db->join('cron_types as ct','ct.cron_type_id = cl.type_id','left');
        $this->db->join('staff_accounts AS sa','cl.`triggered_by` = sa.`StaffID`','left');
        $this->db->where('cl.country_id', $this->config->item('country'));
        $this->db->where('ct.active', 1);

        if($params['cron_type'] && is_numeric($params['cron_type'])){
            $this->db->where('cl.type_id', $params['cron_type']);
        }
        
        if( $params['from']!="" && $params['to']!="" ){
            $from_from  = date('Y-m-d', strtotime($params['from']));
            $to_format  = date('Y-m-d', strtotime($params['to']));
            $date_filter = "CAST( cl.`started` AS Date ) BETWEEN '{$from_from}' AND '{$to_format}' ";
            $this->db->where($date_filter);
        }	


        // group by
        if( isset($params['group_by']) && $params['group_by'] != '' ){
            $this->db->group_by($params['group_by']);
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
        if( isset($params['custom_sort']) ){
                $this->db->order_by($params['custom_sort']);
        }

        // limit
        if( isset($params['limit']) && $params['limit'] > 0 ){
            $this->db->limit( $params['limit'], $params['offset']);
        }	

        $query = $this->db->get();
        if( isset($params['display_query']) && $params['display_query'] == 1 ){
            echo $this->db->last_query();
        }
        
        return $query;


    }

    public function getCronTypes(){
	
        return $this->db->query("
            SELECT *
            FROM `cron_types`
            WHERE `active` = 1
            ORDER BY `type_name` ASC
        ");
        
    }

    public function move_on_hold_jobs(){        

        $country_id = $this->config->item('country');
        $job_status = 'On Hold';

        $custom_where = "CURDATE( ) >= ( CAST( j.`start_date` AS Date ) - INTERVAL 1 DAY )";

        // get on hold jobs
        $sel_query = "                    
            j.`id` AS jid, 
            j.`job_type`, 

            p.`subscription_billed`,

            a.`allow_upfront_billing`
        ";
        
        $job_params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',

            'job_status' => $job_status,                   
            'country_id' => $country_id,            
                                    
            'display_query' => 1
        );

        $job_sql = $this->jobs_model->get_jobs($job_params);                   
        
        
        // process
        foreach( $job_sql->result() as $job_row ){

            $update_job_status = 'To Be Booked'; // default
		
            if( ( $job_row->allow_upfront_billing == 1 || $job_row->subscription_billed == 1 ) && $job_row->job_type == 'Yearly Maintenance' ){ 
                $update_job_status = 'To Be Invoiced';
            }else{
                $update_job_status = 'To Be Booked';
            }

            if( $job_row->jid > 0 ){

                // update job status
                $update_sql_str = "
                    UPDATE `jobs`
                    SET `status` = '{$update_job_status}'
                    WHERE `id` = {$job_row->jid}
                ";
                $this->db->query($update_sql_str);

                // insert job logs            
                $log_title = 49; // Move On Hold Jobs    
                $job_log = "Job moved from <strong>On Hold</strong> to <strong>{$update_job_status}</strong>";                        
                $log_params = array(
                    'title' => $log_title,
                    'details' => $job_log,
                    'display_in_vjd' => 1,
                    'auto_process' => 1,
                    'job_id' => $job_row->jid
                );
                $this->system_model->insert_log($log_params);    

            }            

        }                                  

    }


    public function reminder_sms(){        

        $country_id = $this->config->item('country');
        $job_status = 'Booked';
        
        $staff_id = -3; // CRON        
        
        $todays_day = date('D');
        $sql_date_text = null;
        
        if( $todays_day == 'Fri' ){ // if friday get saturday and monday

            $saturday = date("Y-m-d",strtotime("+1 day"));
            $next_monday = date("Y-m-d",strtotime("+3 day"));

            $custom_where = " 
                (
                    j.`date` = '{$saturday}' 
                    OR j.`date` = '{$next_monday}' 
                )
            ";

        }else{ // if mon - thur, get next day

            $next_day = date("Y-m-d",strtotime("+1 day"));
            $custom_where = " j.`date` = '{$next_day}' ";
            
        }

        // get to be booked jobs 
        $sel_query = "                    
        j.`id` AS jid, 
        j.`status` AS jstatus,
        j.`property_id`,
        j.`door_knock`,
        j.`key_access_required`,
        j.`booked_with`
        ";
        
        
        $job_params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',

            'job_status' => $job_status,                   
            'country_id' => $country_id, 
            
            'join_table' => array('countries'),	

            'sort_list' => array(
                array(
                    'order_by' => 'p.address_2',
                    'sort' => 'ASC',
                ),
            ),
                                    
            'display_query' => 0
        );

        $job_sql = $this->jobs_model->get_jobs($job_params);                   
        
        
        // process
        foreach( $job_sql->result() as $job_row ){

            $job_id = $job_row->jid;
            $property_id = $job_row->property_id;

            if(  $job_id > 0 && $property_id > 0 ){

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
                    if( $pt_row->tenant_mobile != "" && $pt_row->tenant_firstname == $job_row->booked_with ){

                        // format phone number
                        $send_to = $this->sms_model->formatToInternationNumber($pt_row->tenant_mobile);                                    

                        /*
                        // SMS body - old, will revert after COVID-19 passes
                        //$body = "{$pt_row->tenant_firstname}, SATS will be testing the {serv_name} at {p_address} on {job_date} between {time_of_day}. Any problems please call SATS {tenant_number}";                                                
                        
                        // COVID-19 SMS Body
                        $body = "{$pt_row->tenant_firstname}, SATS will be testing the {serv_name} at {p_address} on {job_date} between {time_of_day}. As our technicians will be attending your property, we ask that if you are sick or have come into contact with somebody who has or is being tested for COVID-19 or if you have travelled overseas within the last 14 days to let our team know. Any questions please call SATS {tenant_number} or for more info https://www.{$this->config->item('sats_domain')}";
                        */

                        // SMS body
                        // get template content     
                        $sms_type = 19; // SMS (Reminder)                    
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

                        //insert log
                        $log_details = "Reminder SMS to {$send_to} <strong>{$parsed_template_body}</strong>";
                        $log_params = array(
                            'title' => 40, // SMS sent
                            'details' => $log_details,
                            'display_in_vjd' => 1,
                            'auto_process' => 1,
                            'job_id' => $job_id
                        );
                        $this->system_model->insert_log($log_params);
                        
                        

                    }
                
                } 
            
            }

        } 
        
                                        

    }


    public function create_renewals(){      

        // variables
        $staff_id = -3; // CRON
        $country_id = $this->config->item('country');
        $today = date("Y-m-d");

        $last_year = date("Y",strtotime("-1 year"));	
        $next_month = date("m",strtotime("+1 month"));	
        $max_day = date("t",strtotime("{$last_year}-{$next_month}"));
        $next_month_full = date("Y-m-01 H:i:s",strtotime("+1 month"));
        
        $this_year = date("Y");
        $this_month = date("m");
        $dha_need_processing = 0;
        $date_str = "";     
        $filter_hume_housing_agency_filter_str = '';
        $state_filter_str = '';
        
        if( $country_id == 1 ){ // AU

            $hume_house_agency_id = 1598; // Hume Housing   
            $filter_hume_housing_agency_filter_str = "AND a.`agency_id` != {$hume_house_agency_id}";

            // exclude NSW
            $state_filter_str = "AND p.`state` != 'NSW'";  

        }                 
       

        // if december
        if( intval($this_month) == 12 ){
            $this_month_max_day = date("t",strtotime("{$this_year}-01"));
            $date_str = " AND j.`date` BETWEEN '{$this_year}-01-01' AND '{$this_year}-01-{$this_month_max_day}'";
        }else{
            $date_str = " AND j.`date` BETWEEN '{$last_year}-{$next_month}-01' AND '{$last_year}-{$next_month}-{$max_day}'";
        }

        
        // main query
        $job_sql_str = "
            SELECT 
                j.`id` AS jid, 
                j.`property_id`, 
                j.`job_price`, 
                j.`service` AS jservice, 
                j.`date` AS jdate, 

                ajt.`bundle`,
                ajt.`bundle_ids`,
                
                ps.`price` AS ps_price, 

                a.`agency_id`,
                a.`franchise_groups_id`,
                a.`allow_upfront_billing`,

                cp.`console_prop_id`
            FROM `jobs` AS j

            LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`

            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            INNER JOIN `property_services` AS ps ON ( j.`property_id` = ps.`property_id` AND j.`service` = ps.`alarm_job_type_id` AND ps.`service` =1 )

            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            INNER JOIN `agency_services` AS agen_serv ON ( a.`agency_id` = agen_serv.`agency_id` AND j.`service` = agen_serv.`service_id` AND agen_serv.`agency_services_id` IS NOT NULL )

            LEFT JOIN `console_properties` AS cp ON p.`property_id` = cp.`crm_prop_id`
            
            WHERE j.`status` = 'Completed'
            AND j.`job_type` = 'Yearly Maintenance'	

            AND j.`del_job` = 0
            AND p.`deleted` =0
            AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
            AND a.`status` = 'active'
            AND a.`deleted` = 0
            AND a.`country_id` = {$country_id}      
            {$state_filter_str}          
            {$filter_hume_housing_agency_filter_str}

            {$date_str}
        ";
        $job_sql = $this->db->query($job_sql_str);   
        
        
        foreach( $job_sql->result() as $job_row ){

            // service type
            $j_serv = $job_row->jservice;

            // if agency is DHA agencies with franchise group = 14(Defence Housing) OR if agency has maintenance program                
            if( $this->system_model->isDHAagenciesV2($job_row->franchise_groups_id) == true || $this->system_model->agencyHasMaintenanceProgram($job_row->agency_id) == true ){
                $dha_need_processing = 1;
            }         
            
            // is connected to console API
            if( $job_row->console_prop_id > 0 ){

                // +350 days, instructed by Ben T.
                $renewal_start_date = date('Y-m-d H:i:s',strtotime("{$job_row->jdate} +350 days"));

            }else{ // default

                $renewal_start_date = $next_month_full;
                
            }

            // dynamic price, get price from property service if excluded else get from variations
            $price_var_params = array(
                'service_type' => $job_row->jservice,
                'property_id' => $job_row->property_id
            );
            $price_var_arr = $this->system_model->get_property_price_variation($price_var_params);
            $dynamic_price = $price_var_arr['dynamic_price_total'];

            // insert renewal job
            $job_data = array(
                'status' => 'Pending',
                'retest_interval' => 350,
                'auto_renew' => 1,
                'job_type' => 'Yearly Maintenance',
                'property_id' => $job_row->property_id,
                'sort_order' => 1,
                'job_price' => $dynamic_price,
                'service' => $job_row->jservice,
                'created' => $renewal_start_date,
                'start_date' => $renewal_start_date,
                'dha_need_processing' => $dha_need_processing
            );                
            $this->db->insert('jobs', $job_data);

            $job_id = $this->db->insert_id();

            // AUTO - UPDATE INVOICE DETAILS
            $this->system_model->updateInvoiceDetails($job_id);                

        
            if( $job_row->bundle == 1 ){ // bundle service

                $b_ids = explode(",",trim($job_row->bundle_ids));

                // insert bundles
                foreach($b_ids as $ajt_id){

                    $bundle_data = array(
                        'job_id' => $job_id,
                        'alarm_job_type_id' => $ajt_id
                    );                
                    $this->db->insert('bundle_services', $bundle_data);
                    $bundle_serv_id = $this->db->insert_id();                       
                    
                    if( $job_id > 0 && $ajt_id > 0 && $bundle_serv_id > 0 ){

                        // sync service types of bundle
                        $syncParams = array("job_id" => $job_id, "jserv" => $ajt_id, "bundle_serv_id" => $bundle_serv_id);
                        $this->jobs_model->runSync($syncParams);

                    }                        

                }	

            }else{ // single service

                if( $job_id > 0 && $j_serv > 0 ){

                    // sync alarm
                    $syncParams = array("job_id" => $job_id, "jserv" => $j_serv);
                    $this->jobs_model->runSync($syncParams);

                }                   
                
            }

            // insert job logs            
            $log_title = 51; // Service Due
            $job_log = "Service Due Job Created";                        
            $log_params = array(
                'title' => $log_title,
                'details' => $job_log,
                'display_in_vjd' => 1,
                'auto_process' => 1,
                'job_id' => $job_id
            );
            $this->system_model->insert_log($log_params);   
            
            // mark is_eo
		    $this->system_model->mark_is_eo($job_id);

        }

        // insert renewals db
        $ym_tot = $job_sql->num_rows();

        $renewal_type = 1; // regular
        $renewals_params = array(
            'StaffID' => $staff_id,
            'country_id' => $country_id,
            'date' => date("Y-m-d H:i:s"),
            'num_jobs_created' => $ym_tot,
            'renewal_type' => $renewal_type
        );                
        $this->db->insert('renewals', $renewals_params);                                                

    }



    public function create_renewals_v2($renewal_type){      

        // variables
        $staff_id = -3; // CRON
        $country_id = $this->config->item('country');
	
        // using +1 month is an issue when this is triggered on day 31 and the next month has no day 31, it will skip the next month
        // strtotime("first day of +1 month") also works fine, useful to get the next {number} of months
        $next_month = date("Y-m-01",strtotime("first day of next month"));	            
        
        $dha_need_processing = 0;
        $date_str = null;     
        $hume_house_agency_id = 1598; // Hume Housing                 
               
        // get distinct agency
        $distinct_agency_sql_str = "
            SELECT
                DISTINCT (a.`agency_id`), 
                a.`state`,
                aop.`renewal_interval`,
                aop.`renewal_start_offset`
            FROM `jobs` AS j
            LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`

            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            INNER JOIN `property_services` AS ps ON ( j.`property_id` = ps.`property_id` AND j.`service` = ps.`alarm_job_type_id` AND ps.`service` =1 )

            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            INNER JOIN `agency_services` AS agen_serv ON ( a.`agency_id` = agen_serv.`agency_id` AND j.`service` = agen_serv.`service_id` AND agen_serv.`agency_services_id` IS NOT NULL )
            LEFT JOIN `agency_other_pref` AS aop ON ( a.`agency_id` = aop.`agency_id` )

            LEFT JOIN `console_properties` AS cp ON p.`property_id` = cp.`crm_prop_id`
            
            WHERE j.`status` = 'Completed'
            AND j.`job_type` = 'Yearly Maintenance'	
            AND j.`del_job` = 0
            AND p.`deleted` =0
            AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
            AND a.`status` = 'active'        
            AND a.`country_id` = {$country_id}   
        ";
        //echo "{$distinct_agency_sql_str}<br /><br />";

        $distinct_agency_sql = $this->db->query($distinct_agency_sql_str); 

        $renewal_count_total = 0;
        foreach( $distinct_agency_sql->result() as $distinct_agency_row ){

            // get renewal interval and start date offset
            $renewal_interval_default = 12; // months
            $renewal_start_offset_default = $this->config->item('renewal_start_offset_default'); // days 

            if( $country_id == 1 ){ // AU only

                if( $distinct_agency_row->agency_id == $hume_house_agency_id ){ // if Hume Agency, renewal interval in 9 months

                    $renewal_interval_default = 9; 
     
                }else if( $distinct_agency_row->state == 'NSW' ){ 
    
                    // if NSW, start 15 days earlier from regular cron(15 day earlier), so add more 15 days to the offset so it now becomes 30
                    $renewal_start_offset_default = $this->config->item('renewal_start_offset_nsw'); 
    
                }

            }            
            
            // overrides default if values are set
            $renewal_interval = ( $distinct_agency_row->renewal_interval > 0 )?$distinct_agency_row->renewal_interval:$renewal_interval_default;              

            $renewal_interval_start = date('Y-m-01',strtotime("{$next_month} -{$renewal_interval} months"));
            $renewal_interval_end = date('Y-m-t',strtotime("{$next_month} -{$renewal_interval} months"));

            // date filter
            $date_str = " AND j.`date` BETWEEN '{$renewal_interval_start}' AND '{$renewal_interval_end}'";

            if( $distinct_agency_row->agency_id > 0 ){

                // main query
                $job_sql_str = "
                    SELECT 
                        j.`id` AS jid, 
                        j.`property_id`, 
                        j.`job_price`, 
                        j.`service` AS jservice, 
                        j.`date` AS jdate, 

                        ajt.`bundle`,
                        ajt.`bundle_ids`,

                        p.`state` AS p_state,
                        
                        ps.`price` AS ps_price, 

                        a.`agency_id`,
                        a.`franchise_groups_id`,
                        a.`allow_upfront_billing`,

                        cp.`console_prop_id`
                    FROM `jobs` AS j
                    LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`

                    LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                    INNER JOIN `property_services` AS ps ON ( j.`property_id` = ps.`property_id` AND j.`service` = ps.`alarm_job_type_id` AND ps.`service` =1 )

                    LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                    INNER JOIN `agency_services` AS agen_serv ON ( a.`agency_id` = agen_serv.`agency_id` AND j.`service` = agen_serv.`service_id` AND agen_serv.`agency_services_id` IS NOT NULL )
                    LEFT JOIN `agency_other_pref` AS aop ON ( a.`agency_id` = aop.`agency_id` )

                    LEFT JOIN `console_properties` AS cp ON p.`property_id` = cp.`crm_prop_id`
                    
                    WHERE j.`status` = 'Completed'
                    AND j.`job_type` = 'Yearly Maintenance'	
                    AND j.`del_job` = 0
                    AND p.`deleted` =0
                    AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
                    AND a.`status` = 'active'        
                    AND a.`country_id` = {$country_id}                                
                    AND a.`agency_id` = {$distinct_agency_row->agency_id} 
                    {$date_str}
                ";
                //echo "{$job_sql_str}<br /><br />";

                $job_sql = $this->db->query($job_sql_str);   
                
                
                foreach( $job_sql->result() as $job_row ){
                    
                    // service type
                    $j_serv = $job_row->jservice;

                    // if agency is DHA agencies with franchise group = 14(Defence Housing) OR if agency has maintenance program                
                    if( 
                        $this->system_model->isDHAagenciesV2($job_row->franchise_groups_id) == true || 
                        $this->system_model->agencyHasMaintenanceProgram($job_row->agency_id) == true 
                    ){
                        $dha_need_processing = 1;
                    }     

                    // dynamic based from agency preference or default
                    if( $job_row->p_state == 'QLD' ){ 
                        $renewal_start_offset = 0; // no start date offset for QLD
                    }else{
                        $renewal_start_offset = ( is_numeric($distinct_agency_row->renewal_start_offset) )?$distinct_agency_row->renewal_start_offset:$renewal_start_offset_default;
                    }                    
                    $job_date_plus_renewal_interval = date('Y-m-d H:i:s',strtotime("{$job_row->jdate} +".($renewal_interval)." months"));
                    if( $job_row->p_state == 'QLD' ){ 
                        $created_and_start_date_str = 'Y-m-01 H:i:s'; // for QLD, first day of month
                    }else{ // default
                        $created_and_start_date_str = 'Y-m-d H:i:s';
                    }
                    $created_and_start_date = date($created_and_start_date_str,strtotime("{$job_date_plus_renewal_interval} -{$renewal_start_offset} days")); 
                    
                    // dynamic price, get price from property service if excluded else get from variations
                    $price_var_params = array(
                        'service_type' => $job_row->jservice,
                        'property_id' => $job_row->property_id
                    );
                    $price_var_arr = $this->system_model->get_property_price_variation($price_var_params);
                    $dynamic_price = $price_var_arr['dynamic_price_total'];

                    // insert renewal job
                    $job_data = array(
                        'status' => 'Pending',
                        'retest_interval' => 350,
                        'auto_renew' => 1,
                        'job_type' => 'Yearly Maintenance',
                        'property_id' => $job_row->property_id,
                        'sort_order' => 1,
                        'job_price' => $dynamic_price,
                        'service' => $job_row->jservice,
                        'created' => $created_and_start_date,
                        'start_date' => $created_and_start_date,
                        'dha_need_processing' => $dha_need_processing
                    ); 
                    
                    // for QLD state insert job date
                    if( $job_row->p_state == 'QLD' ){ 
                        $job_data['date'] = $created_and_start_date;
                    }
                    
                    $this->db->insert('jobs', $job_data);                    

                    $job_id = $this->db->insert_id();

                    // AUTO - UPDATE INVOICE DETAILS
                    $this->system_model->updateInvoiceDetails($job_id);                

                
                    if( $job_row->bundle == 1 ){ // bundle service

                        $b_ids = explode(",",trim($job_row->bundle_ids));

                        // insert bundles
                        foreach($b_ids as $ajt_id){

                            $bundle_data = array(
                                'job_id' => $job_id,
                                'alarm_job_type_id' => $ajt_id
                            );                
                            $this->db->insert('bundle_services', $bundle_data);
                            $bundle_serv_id = $this->db->insert_id();                       
                            
                            if( $job_id > 0 && $ajt_id > 0 && $bundle_serv_id > 0 ){

                                // sync service types of bundle
                                $syncParams = array("job_id" => $job_id, "jserv" => $ajt_id, "bundle_serv_id" => $bundle_serv_id);
                                $this->jobs_model->runSync($syncParams);

                            }                        

                        }	

                    }else{ // single service

                        if( $job_id > 0 && $j_serv > 0 ){

                            // sync alarm
                            $syncParams = array("job_id" => $job_id, "jserv" => $j_serv);
                            $this->jobs_model->runSync($syncParams);

                        }                   
                        
                    }

                    // insert job logs            
                    $log_title = 51; // Service Due
                    $job_log = "Service Due Job Created";                        
                    $log_params = array(
                        'title' => $log_title,
                        'details' => $job_log,
                        'display_in_vjd' => 1,
                        'auto_process' => 1,
                        'job_id' => $job_id
                    );
                    $this->system_model->insert_log($log_params);   
                    
                    // mark is_eo
                    $this->system_model->mark_is_eo($job_id);
                    
                }
                
                // insert renewals db
                $ym_tot = $job_sql->num_rows();   
                $renewal_count_total += $ym_tot;                                               

            }                    

        } 
        
        $renewal_type = 4; // combined
        $renewals_params = array(
            'StaffID' => $staff_id,
            'country_id' => $country_id,
            'date' => date("Y-m-d H:i:s"),
            'num_jobs_created' => $renewal_count_total,
            'renewal_type' => $renewal_type
        );                
        $this->db->insert('renewals', $renewals_params);

    }


    public function create_renewals_v2_manual_run($params){    
        
        $renewal_type = $params['renewal_type'];
        $controlled_date_ts = $params['controlled_date_ts'];
        $controlled_date = date('Y-m-d',$controlled_date_ts);

        // variables
        $staff_id = -3; // CRON
        $country_id = $this->config->item('country');
	
        // using +1 month is an issue when this is triggered on day 31 and the next month has no day 31, it will skip the next month
        // strtotime("first day of +1 month") also works fine, useful to get the next {number} of months
        //$next_month = date("Y-m-01",strtotime("first day of next month"));	     
        
        $next_month = date("Y-m-01",strtotime("{$controlled_date} +1 month"));
        
        $dha_need_processing = 0;
        $date_str = null;     
        $hume_house_agency_id = 1598; // Hume Housing                 
               
        // get distinct agency
        $distinct_agency_sql_str = "
            SELECT
                DISTINCT (a.`agency_id`), 
                a.`state`,
                aop.`renewal_interval`,
                aop.`renewal_start_offset`
            FROM `jobs` AS j
            LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`

            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            INNER JOIN `property_services` AS ps ON ( j.`property_id` = ps.`property_id` AND j.`service` = ps.`alarm_job_type_id` AND ps.`service` =1 )

            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            INNER JOIN `agency_services` AS agen_serv ON ( a.`agency_id` = agen_serv.`agency_id` AND j.`service` = agen_serv.`service_id` AND agen_serv.`agency_services_id` IS NOT NULL )
            LEFT JOIN `agency_other_pref` AS aop ON ( a.`agency_id` = aop.`agency_id` )

            LEFT JOIN `console_properties` AS cp ON p.`property_id` = cp.`crm_prop_id`
            
            WHERE j.`status` = 'Completed'
            AND j.`job_type` = 'Yearly Maintenance'	
            AND j.`del_job` = 0
            AND p.`deleted` =0
            AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
            AND a.`status` = 'active'        
            AND a.`country_id` = {$country_id}   
        ";
        //echo "{$distinct_agency_sql_str}<br /><br />";

        $distinct_agency_sql = $this->db->query($distinct_agency_sql_str); 

        $renewal_count_total = 0;
        foreach( $distinct_agency_sql->result() as $distinct_agency_row ){

            // get renewal interval and start date offset
            $renewal_interval_default = 12; // months
            $renewal_start_offset_default = $this->config->item('renewal_start_offset_default'); // days 

            if( $country_id == 1 ){ // AU only

                if( $distinct_agency_row->agency_id == $hume_house_agency_id ){ // if Hume Agency, renewal interval in 9 months

                    $renewal_interval_default = 9; 
     
                }else if( $distinct_agency_row->state == 'NSW' ){ 
    
                    // if NSW, start 15 days earlier from regular cron(15 day earlier), so add more 15 days to the offset so it now becomes 30
                    $renewal_start_offset_default = $this->config->item('renewal_start_offset_nsw'); 
    
                }

            }            
            
            // overrides default if values are set
            $renewal_interval = ( $distinct_agency_row->renewal_interval > 0 )?$distinct_agency_row->renewal_interval:$renewal_interval_default;              

            $renewal_interval_start = date('Y-m-01',strtotime("{$next_month} -{$renewal_interval} months"));
            $renewal_interval_end = date('Y-m-t',strtotime("{$next_month} -{$renewal_interval} months"));

            // date filter
            $date_str = " AND j.`date` BETWEEN '{$renewal_interval_start}' AND '{$renewal_interval_end}'";

            if( $distinct_agency_row->agency_id > 0 ){

                // main query
                $job_sql_str = "
                    SELECT 
                        j.`id` AS jid, 
                        j.`property_id`, 
                        j.`job_price`, 
                        j.`service` AS jservice, 
                        j.`date` AS jdate, 

                        ajt.`bundle`,
                        ajt.`bundle_ids`,

                        p.`state` AS p_state,
                        
                        ps.`price` AS ps_price, 

                        a.`agency_id`,
                        a.`franchise_groups_id`,
                        a.`allow_upfront_billing`,

                        cp.`console_prop_id`
                    FROM `jobs` AS j
                    LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`

                    LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                    INNER JOIN `property_services` AS ps ON ( j.`property_id` = ps.`property_id` AND j.`service` = ps.`alarm_job_type_id` AND ps.`service` =1 )

                    LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                    INNER JOIN `agency_services` AS agen_serv ON ( a.`agency_id` = agen_serv.`agency_id` AND j.`service` = agen_serv.`service_id` AND agen_serv.`agency_services_id` IS NOT NULL )
                    LEFT JOIN `agency_other_pref` AS aop ON ( a.`agency_id` = aop.`agency_id` )

                    LEFT JOIN `console_properties` AS cp ON p.`property_id` = cp.`crm_prop_id`
                    
                    WHERE j.`status` = 'Completed'
                    AND j.`job_type` = 'Yearly Maintenance'	
                    AND j.`del_job` = 0
                    AND p.`deleted` =0
                    AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
                    AND a.`status` = 'active'        
                    AND a.`country_id` = {$country_id}                                
                    AND a.`agency_id` = {$distinct_agency_row->agency_id} 
                    {$date_str}
                ";
                //echo "{$job_sql_str}<br /><br />";

                $job_sql = $this->db->query($job_sql_str);   
                
                
                foreach( $job_sql->result() as $job_row ){
                    
                    // service type
                    $j_serv = $job_row->jservice;

                    // if agency is DHA agencies with franchise group = 14(Defence Housing) OR if agency has maintenance program                
                    if( 
                        $this->system_model->isDHAagenciesV2($job_row->franchise_groups_id) == true || 
                        $this->system_model->agencyHasMaintenanceProgram($job_row->agency_id) == true 
                    ){
                        $dha_need_processing = 1;
                    }     

                    // dynamic based from agency preference or default
                    if( $job_row->p_state == 'QLD' ){ 
                        $renewal_start_offset = 0; // no start date offset for QLD
                    }else{
                        $renewal_start_offset = ( is_numeric($distinct_agency_row->renewal_start_offset) )?$distinct_agency_row->renewal_start_offset:$renewal_start_offset_default;
                    }                    
                    $job_date_plus_renewal_interval = date('Y-m-d H:i:s',strtotime("{$job_row->jdate} +".($renewal_interval)." months"));
                    if( $job_row->p_state == 'QLD' ){ 
                        $created_and_start_date_str = 'Y-m-01 H:i:s'; // for QLD, first day of month
                    }else{ // default
                        $created_and_start_date_str = 'Y-m-d H:i:s';
                    }
                    $created_and_start_date = date($created_and_start_date_str,strtotime("{$job_date_plus_renewal_interval} -{$renewal_start_offset} days")); 
                    
                    // dynamic price, get price from property service if excluded else get from variations
                    $price_var_params = array(
                        'service_type' => $job_row->jservice,
                        'property_id' => $job_row->property_id
                    );
                    $price_var_arr = $this->system_model->get_property_price_variation($price_var_params);
                    $dynamic_price = $price_var_arr['dynamic_price_total'];

                    // insert renewal job
                    $job_data = array(
                        'status' => 'Pending',
                        'retest_interval' => 350,
                        'auto_renew' => 1,
                        'job_type' => 'Yearly Maintenance',
                        'property_id' => $job_row->property_id,
                        'sort_order' => 1,
                        'job_price' => $dynamic_price,
                        'service' => $job_row->jservice,
                        'created' => $created_and_start_date,
                        'start_date' => $created_and_start_date,
                        'dha_need_processing' => $dha_need_processing
                    ); 
                    
                    // for QLD state insert job date
                    if( $job_row->p_state == 'QLD' ){ 
                        $job_data['date'] = $created_and_start_date;
                    }
                    
                    $this->db->insert('jobs', $job_data);                    

                    $job_id = $this->db->insert_id();

                    // AUTO - UPDATE INVOICE DETAILS
                    $this->system_model->updateInvoiceDetails($job_id);                

                
                    if( $job_row->bundle == 1 ){ // bundle service

                        $b_ids = explode(",",trim($job_row->bundle_ids));

                        // insert bundles
                        foreach($b_ids as $ajt_id){

                            $bundle_data = array(
                                'job_id' => $job_id,
                                'alarm_job_type_id' => $ajt_id
                            );                
                            $this->db->insert('bundle_services', $bundle_data);
                            $bundle_serv_id = $this->db->insert_id();                       
                            
                            if( $job_id > 0 && $ajt_id > 0 && $bundle_serv_id > 0 ){

                                // sync service types of bundle
                                $syncParams = array("job_id" => $job_id, "jserv" => $ajt_id, "bundle_serv_id" => $bundle_serv_id);
                                $this->jobs_model->runSync($syncParams);

                            }                        

                        }	

                    }else{ // single service

                        if( $job_id > 0 && $j_serv > 0 ){

                            // sync alarm
                            $syncParams = array("job_id" => $job_id, "jserv" => $j_serv);
                            $this->jobs_model->runSync($syncParams);

                        }                   
                        
                    }

                    // insert job logs            
                    $log_title = 51; // Service Due
                    $job_log = "Service Due Job Created";                        
                    $log_params = array(
                        'title' => $log_title,
                        'details' => $job_log,
                        'display_in_vjd' => 1,
                        'auto_process' => 1,
                        'job_id' => $job_id
                    );
                    $this->system_model->insert_log($log_params);   
                    
                    // mark is_eo
                    $this->system_model->mark_is_eo($job_id);
                    
                }
                
                // insert renewals db
                $ym_tot = $job_sql->num_rows();   
                $renewal_count_total += $ym_tot;                                               

            }                    

        } 
        
        $renewal_type = 4; // combined
        $renewals_params = array(
            'StaffID' => $staff_id,
            'country_id' => $country_id,
            'date' => date("Y-m-d H:i:s",$controlled_date_ts),
            'num_jobs_created' => $renewal_count_total,
            'renewal_type' => $renewal_type
        );                
        $this->db->insert('renewals', $renewals_params);

    }


    // no longer used, hume housing and NSW renewals only exist on AU and is now merge on renewals version 2, NZ doesn't have it
    /*
    public function create_renewals_hume_housing(){      

        // variables
        $staff_id = -3; // CRON
        $country_id = $this->config->item('country');
        $today = date("Y-m-d");
    
        $this_month = date("m");
        $dha_need_processing = 0;
        $date_str = "";

        $hume_house_agency_id = 1598; // Hume Housing            

        //if( $renewals_sql->num_rows() == 0 ){
                        
            $next_month = date('Y-m-01',strtotime("+1 month"));
            $next_month_full = date("Y-m-01 H:i:s",strtotime("+1 month"));
            
            $next_month_last_9_months = date('Y-m-01',strtotime("{$next_month} -9 months"));
            $next_month_last_9_1st_day = date('Y-m-01',strtotime($next_month_last_9_months));
            $next_month_last_9_last_day = date('Y-m-t',strtotime($next_month_last_9_months));

            $date_str = " AND j.`date` BETWEEN '{$next_month_last_9_1st_day}' AND '{$next_month_last_9_last_day}'";
            
            // main query
            $job_sql_str = "
                SELECT 
                    j.`id` AS jid, 
                    j.`property_id`, 
                    j.`job_price`, 
                    j.`service` AS jservice, 
                    j.`date` AS jdate, 

                    ajt.`bundle`,
                    ajt.`bundle_ids`,
                    
                    ps.`price` AS ps_price, 

                    a.`agency_id`,
                    a.`franchise_groups_id`,
                    a.`allow_upfront_billing`,

                    cp.`console_prop_id`
                FROM `jobs` AS j

                LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`

                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                INNER JOIN `property_services` AS ps ON ( j.`property_id` = ps.`property_id` AND j.`service` = ps.`alarm_job_type_id` AND ps.`service` =1 )

                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                INNER JOIN `agency_services` AS agen_serv ON ( a.`agency_id` = agen_serv.`agency_id` AND j.`service` = agen_serv.`service_id` AND agen_serv.`agency_services_id` IS NOT NULL )

                LEFT JOIN `console_properties` AS cp ON p.`property_id` = cp.`crm_prop_id`
                
                WHERE j.`status` = 'Completed'
                AND j.`job_type` = 'Yearly Maintenance'	

                AND j.`del_job` = 0
                AND p.`deleted` =0
                AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
                AND a.`status` = 'active'
                AND a.`deleted` = 0
                AND a.`country_id` = {$country_id}
                AND a.`agency_id` = {$hume_house_agency_id}

                {$date_str}
            ";
            $job_sql = $this->db->query($job_sql_str);   
            
            
            
            foreach( $job_sql->result() as $job_row ){

                // service type
                $j_serv = $job_row->jservice;

                // if agency is DHA agencies with franchise group = 14(Defence Housing) OR if agency has maintenance program                
                if( $this->system_model->isDHAagenciesV2($job_row->franchise_groups_id) == true || $this->system_model->agencyHasMaintenanceProgram($job_row->agency_id) == true ){
                    $dha_need_processing = 1;
                }   
                
                // is connected to console API
                if( $job_row->console_prop_id > 0 ){
                    
                    $renewal_start_date = date('Y-m-d H:i:s',strtotime("{$job_row->jdate} +9 months"));

                }else{ // default

                    $renewal_start_date = $next_month_full;
                    
                }

                // insert renewal job
                $job_data = array(
                    'status' => 'Pending',
                    'retest_interval' => 274,
                    'auto_renew' => 1,
                    'job_type' => 'Yearly Maintenance',
                    'property_id' => $job_row->property_id,
                    'sort_order' => 1,
                    'job_price' => $job_row->ps_price,
                    'service' => $job_row->jservice,
                    'created' => $renewal_start_date,
                    'start_date' => $renewal_start_date,
                    'dha_need_processing' => $dha_need_processing
                );                
                $this->db->insert('jobs', $job_data);

                $job_id = $this->db->insert_id();

                // AUTO - UPDATE INVOICE DETAILS
                $this->system_model->updateInvoiceDetails($job_id);                

          
                if( $job_row->bundle == 1 ){ // bundle service

                    $b_ids = explode(",",trim($job_row->bundle_ids));

                    // insert bundles
                    foreach($b_ids as $ajt_id){

                        $bundle_data = array(
                            'job_id' => $job_id,
                            'alarm_job_type_id' => $ajt_id
                        );                
                        $this->db->insert('bundle_services', $bundle_data);
                        $bundle_serv_id = $this->db->insert_id();                       
                        
                        if( $job_id > 0 && $ajt_id > 0 && $bundle_serv_id > 0 ){

                            // sync service types of bundle
                            $syncParams = array("job_id" => $job_id, "jserv" => $ajt_id, "bundle_serv_id" => $bundle_serv_id);
                            $this->jobs_model->runSync($syncParams);

                        }                        

                    }	

                }else{ // single service

                    if( $job_id > 0 && $j_serv > 0 ){

                        // sync alarm
                        $syncParams = array("job_id" => $job_id, "jserv" => $j_serv);
                        $this->jobs_model->runSync($syncParams);

                    }                   
                    
                }

                // insert job logs            
                $log_title = 51; // Service Due
                $job_log = "Service Due Job Created";                        
                $log_params = array(
                    'title' => $log_title,
                    'details' => $job_log,
                    'display_in_vjd' => 1,
                    'auto_process' => 1,
                    'job_id' => $job_id
                );
                $this->system_model->insert_log($log_params);     
                
                // mark is_eo
                $this->system_model->mark_is_eo($job_id);

            }

            // insert renewals db
            $ym_tot = $job_sql->num_rows();

            $renewal_type = 2; // hume
            $renewals_params = array(
                'StaffID' => $staff_id,
                'country_id' => $country_id,
                'date' => date("Y-m-d H:i:s"),
                'num_jobs_created' => $ym_tot,
                'renewal_type' => $renewal_type
            );                
            $this->db->insert('renewals', $renewals_params);
                                  
            

        //}                         

    }


    public function create_renewals_nsw(){      

        // variables
        $staff_id = -3; // CRON
        $country_id = $this->config->item('country');
        $today = date("Y-m-d");

        $last_year = date("Y",strtotime("-1 year"));	
        $next_month = date("m",strtotime("+1 month"));	
        $max_day = date("t",strtotime("{$last_year}-{$next_month}"));
        $current_month_full = date("Y-m-15 H:i:s");        
        
        $this_year = date("Y");
        $this_month = date("m");
        $dha_need_processing = 0;
        $date_str = "";     
        $filter_hume_housing_agency_filter_str = '';
        $state_filter_str = '';
        
        if( $country_id == 1 ){ // AU

            $hume_house_agency_id = 1598; // Hume Housing   
            $filter_hume_housing_agency_filter_str = "AND a.`agency_id` != {$hume_house_agency_id}";

            // exclude NSW
            $state_filter_str = "AND p.`state` = 'NSW'";  

        }         

        //if( $renewals_sql->num_rows() == 0 ){

            // if december
            if( intval($this_month) == 12 ){
                $this_month_max_day = date("t",strtotime("{$this_year}-01"));
                $date_str = " AND j.`date` BETWEEN '{$this_year}-01-01' AND '{$this_year}-01-{$this_month_max_day}'";
            }else{
                $date_str = " AND j.`date` BETWEEN '{$last_year}-{$next_month}-01' AND '{$last_year}-{$next_month}-{$max_day}'";
            }

            
            // main query
            $job_sql_str = "
                SELECT 
                    j.`id` AS jid, 
                    j.`property_id`, 
                    j.`job_price`, 
                    j.`service` AS jservice, 
                    j.`date` AS jdate, 

                    ajt.`bundle`,
                    ajt.`bundle_ids`,
                    
                    ps.`price` AS ps_price, 

                    a.`agency_id`,
                    a.`franchise_groups_id`,
                    a.`allow_upfront_billing`,

                    cp.`console_prop_id`
                FROM `jobs` AS j

                LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`

                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                INNER JOIN `property_services` AS ps ON ( j.`property_id` = ps.`property_id` AND j.`service` = ps.`alarm_job_type_id` AND ps.`service` =1 )

                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                INNER JOIN `agency_services` AS agen_serv ON ( a.`agency_id` = agen_serv.`agency_id` AND j.`service` = agen_serv.`service_id` AND agen_serv.`agency_services_id` IS NOT NULL )

                LEFT JOIN `console_properties` AS cp ON p.`property_id` = cp.`crm_prop_id`
                
                WHERE j.`status` = 'Completed'
                AND j.`job_type` = 'Yearly Maintenance'	

                AND j.`del_job` = 0
                AND p.`deleted` =0
                AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
                AND a.`status` = 'active'
                AND a.`deleted` = 0
            
                AND a.`country_id` = {$country_id}
                {$state_filter_str}  
                {$filter_hume_housing_agency_filter_str}

                {$date_str}
            ";
            $job_sql = $this->db->query($job_sql_str);   
            
            
            foreach( $job_sql->result() as $job_row ){

                // service type
                $j_serv = $job_row->jservice;

                // if agency is DHA agencies with franchise group = 14(Defence Housing) OR if agency has maintenance program                
                if( $this->system_model->isDHAagenciesV2($job_row->franchise_groups_id) == true || $this->system_model->agencyHasMaintenanceProgram($job_row->agency_id) == true ){
                    $dha_need_processing = 1;
                }     
                
                // is connected to console API
                if( $job_row->console_prop_id > 0 ){

                    // +350 days, instructed by Ben T.
                    $renewal_start_date = date('Y-m-d H:i:s',strtotime("{$job_row->jdate} +350 days"));

                }else{ // default

                    $renewal_start_date = $current_month_full;
                    
                }

                // insert renewal job
                $job_data = array(
                    'status' => 'Pending',
                    'retest_interval' => 350,
                    'auto_renew' => 1,
                    'job_type' => 'Yearly Maintenance',
                    'property_id' => $job_row->property_id,
                    'sort_order' => 1,
                    'job_price' => $job_row->ps_price,
                    'service' => $job_row->jservice,
                    'created' => $renewal_start_date,
                    'start_date' => $renewal_start_date,
                    'dha_need_processing' => $dha_need_processing
                );                
                $this->db->insert('jobs', $job_data);

                $job_id = $this->db->insert_id();

                // AUTO - UPDATE INVOICE DETAILS
                $this->system_model->updateInvoiceDetails($job_id);                

          
                if( $job_row->bundle == 1 ){ // bundle service

                    $b_ids = explode(",",trim($job_row->bundle_ids));

                    // insert bundles
                    foreach($b_ids as $ajt_id){

                        $bundle_data = array(
                            'job_id' => $job_id,
                            'alarm_job_type_id' => $ajt_id
                        );                
                        $this->db->insert('bundle_services', $bundle_data);
                        $bundle_serv_id = $this->db->insert_id();                       
                        
                        if( $job_id > 0 && $ajt_id > 0 && $bundle_serv_id > 0 ){

                            // sync service types of bundle
                            $syncParams = array("job_id" => $job_id, "jserv" => $ajt_id, "bundle_serv_id" => $bundle_serv_id);
                            $this->jobs_model->runSync($syncParams);

                        }                        

                    }	

                }else{ // single service

                    if( $job_id > 0 && $j_serv > 0 ){

                        // sync alarm
                        $syncParams = array("job_id" => $job_id, "jserv" => $j_serv);
                        $this->jobs_model->runSync($syncParams);

                    }                   
                    
                }

                // insert job logs            
                $log_title = 51; // Service Due
                $job_log = "Service Due Job Created";                        
                $log_params = array(
                    'title' => $log_title,
                    'details' => $job_log,
                    'display_in_vjd' => 1,
                    'auto_process' => 1,
                    'job_id' => $job_id
                );
                $this->system_model->insert_log($log_params);    
                
                // mark is_eo
                $this->system_model->mark_is_eo($job_id);

            }

            // insert renewals db
            $ym_tot = $job_sql->num_rows();

            
            $renewal_type = 3; // nsw
            $renewals_params = array(
                'StaffID' => $staff_id,
                'country_id' => $country_id,
                'date' => date("Y-m-d H:i:s"),
                'num_jobs_created' => $ym_tot,
                'renewal_type' => $renewal_type
            );                
            $this->db->insert('renewals', $renewals_params);
            
            
            
            

        //}                         

    }
    */


    public function process_service_due($params){        

        $country_id = $this->config->item('country');
        $state_filter = $params['state_filter'];
        $job_status = 'Pending';
        $hume_house_agency_id = 1598; // Hume Housing   

        $custom_where_arr = []; // clear
        $custom_where_arr[] = "a.`auto_renew` = 1 AND p.`manual_renewal` = 0 ";

        // get to be booked jobs 
        $sel_query = "                    
            j.`id` AS jid, 
            j.`property_id`, 
            
            ajt.`type` AS ajt_type,

            p.`address_1`,
            p.`address_2`,
            p.`address_3`,
            p.`state`,
            p.`postcode`,
            
            a.`agency_id`,
            a.`agency_name`
        ";

        /*
        // filter by state
        if( $state_filter == 'nsw' ){  

            if( $country_id == 1 ){ // AU only
                // NSW only exclude Hume Housing
                $custom_where_arr[] = "p.`state` = 'NSW' AND a.`agency_id` != {$hume_house_agency_id}"; 
            }   
                                  
        }else{

            if( $country_id == 1 ){ // AU only
                // NOT ( NSW only exclude Hume Housing )
                $custom_where_arr[] = "NOT ( p.`state` = 'NSW' AND a.`agency_id` != {$hume_house_agency_id} )"; 
            } 
                                    
        }
        */
        
        $job_params = array(
            'sel_query' => $sel_query, 
            'custom_where_arr' => $custom_where_arr,           
            
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',

            'job_status' => $job_status,                   
            'country_id' => $country_id, 
            
            'join_table' => array('alarm_job_type'),	
                                    
            'display_query' => 1
        );

        $job_sql = $this->jobs_model->get_jobs($job_params);                   
        
        
        // process
        foreach( $job_sql->result() as $job_row ){

            if( $job_row->jid > 0 ){

                // update to job status on hold
                $update_sql_str = "
                    UPDATE `jobs` 
                    SET 
                        status='On Hold', 
                        auto_renew=2 
                    WHERE `id` = {$job_row->jid}
                ";
                $this->db->query($update_sql_str);


                // log type
                $log_title = 51; // Service Due

                // insert property logs                          
                $log_txt = "{$job_row->ajt_type} Service Auto Renewed";                        
                $log_params = array(
                    'title' => $log_title,
                    'details' => $log_txt,
                    'display_in_vpd' => 1,
                    'auto_process' => 1,
                    'job_id' => $job_row->jid
                );
                $this->system_model->insert_log($log_params);  

                
                // insert job logs     
                $log_txt = "Job auto processed from Pending to On Hold by System";                               
                $log_params = array(
                    'title' => $log_title,
                    'details' => $log_txt,
                    'display_in_vjd' => 1,
                    'auto_process' => 1,
                    'job_id' => $job_row->jid
                );
                $this->system_model->insert_log($log_params);                  

            }            

        }  
         
                                   

    }


    public function move_future_start_date_jobs(){        

        $country_id = $this->config->item('country');
        $job_status = 'To Be Booked';

        $plus_5_days = date('Y-m-d',strtotime('+ 5 days'));

        $custom_where = "CAST( j.`start_date` AS Date ) >= '{$plus_5_days}'";

        // get to be booked jobs 
        $sel_query = "                    
            j.`id` AS jid
        ";
        
        $job_params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',

            'job_status' => $job_status,                   
            'country_id' => $country_id,            
                                    
            'display_query' => 1
        );

        $job_sql = $this->jobs_model->get_jobs($job_params);                   
        
        
        // process
        foreach( $job_sql->result() as $job_row ){

            if( $job_row->jid > 0 ){

                // update to job status on hold
                    $update_sql_str = "
                    UPDATE `jobs`
                    SET `status` = 'On Hold'
                    WHERE `id` = {$job_row->jid}
                ";
                $this->db->query($update_sql_str);

                
                // insert job logs            
                $log_title = 50; // Move Future Start Date Jobs   
                $job_log = "Job moved from <strong>To Be Booked</strong> to <strong>On Hold</strong>";                        
                $log_params = array(
                    'title' => $log_title,
                    'details' => $job_log,
                    'display_in_vjd' => 1,
                    'auto_process' => 1,
                    'job_id' => $job_row->jid
                );
                $this->system_model->insert_log($log_params); 
                 

            }            

        }     
                                   

    }

    // tech run flush
    public function tech_run_flush($params){                   

        $country_id = $this->config->item('country');
        $date_180_days_old = date('Y-m-d',strtotime("-180 days"));
        
        if( $params['show_select_query'] == true ){

            // select tech run rows
            echo "
                SELECT *
                FROM `tech_run_rows` AS trr
                LEFT JOIN `tech_run` AS tr ON trr.`tech_run_id` = tr.`tech_run_id`
                WHERE tr.`date` < '{$date_180_days_old}'
            ";  
            echo "<br />";

            // select tech run 
            echo "
                SELECT * 
                FROM `tech_run`
                WHERE `date` < '{$date_180_days_old}'
            ";
            echo "<br />";

            // select tech run logs
            echo "
                SELECT * 
                FROM `tech_run_logs`
                WHERE Date(`created`) < '{$date_180_days_old}'
            ";
            echo "<br />";

        }
        
        if( $params['run_delete_query'] == true ){

            // delete tech run rows
            $this->db->query("
                DELETE trr
                FROM `tech_run_rows` AS trr
                LEFT JOIN `tech_run` AS tr ON trr.`tech_run_id` = tr.`tech_run_id`
                WHERE tr.`date` < '{$date_180_days_old}'
            ");                   
            $delete_1_count = $this->db->affected_rows();   

            // delete tech run 
            $this->db->query("
                DELETE 
                FROM `tech_run`
                WHERE `date` < '{$date_180_days_old}'
            ");
            $delete_2_count = $this->db->affected_rows();   

            // delete tech run logs
            $this->db->query("
                DELETE 
                FROM `tech_run_logs`
                WHERE Date(`created`) < '{$date_180_days_old}'
            ");
            $delete_3_count = $this->db->affected_rows();   

            if( $delete_1_count > 0 || $delete_2_count > 0 || $delete_3_count > 0 ){
                return true;
            }else{
                return false;
            }

        }
         

        
                                   

    }

    // sms flush
    public function sms_flush($params){        

        $country_id = $this->config->item('country');

        $sms_type = 18; // TY SMS type
        
        $seven_days_old = date('Y-m-d',strtotime('-7 days'));
        $one_year_old = date('Y-m-d',strtotime('-1 year'));

        if( $params['show_select_query'] == true ){

            // select All SMS that are 7 days old or more except TY SMS
            echo "
                SELECT *
                FROM `sms_api_sent` AS sas 
                LEFT JOIN `sms_api_replies` AS sar ON sas.`message_id` = sar.`message_id`
                WHERE CAST( sas.`created_date` AS Date ) < '{$seven_days_old}'
                AND sas.`sms_type` != {$sms_type}
            ";      
            echo "<br />";            
            

            // Delete 1 year old TY SMS
            echo "
                SELECT *
                FROM `sms_api_sent` AS sas 
                LEFT JOIN `sms_api_replies` AS sar ON sas.`message_id` = sar.`message_id`
                WHERE CAST( sas.`created_date` AS Date ) < '{$one_year_old}'
                AND sas.`sms_type` = {$sms_type}
            "; 
            echo "<br />";  

        }
        
        if( $params['run_delete_query'] == true ){

            // select All SMS that are 7 days old or more except TY SMS
                $this->db->query("
                DELETE sas,sar
                FROM `sms_api_sent` AS sas 
                LEFT JOIN `sms_api_replies` AS sar ON sas.`message_id` = sar.`message_id`
                WHERE CAST( sas.`created_date` AS Date ) < '{$seven_days_old}'
                AND sas.`sms_type` != {$sms_type}
            ");      
            $delete_1_count = $this->db->affected_rows();            
            

            // Delete 1 year old TY SMS
            $this->db->query("
                DELETE sas,sar
                FROM `sms_api_sent` AS sas 
                LEFT JOIN `sms_api_replies` AS sar ON sas.`message_id` = sar.`message_id`
                WHERE CAST( sas.`created_date` AS Date ) < '{$one_year_old}'
                AND sas.`sms_type` = {$sms_type}
            ");   
            $delete_2_count = $this->db->affected_rows();


            if( $delete_1_count > 0 || $delete_2_count > 0 ){
                return true;
            }else{
                return false;
            }

        }
        
                                   

    }

    // agency login flush
    public function agency_login_flush($params){        

        $country_id = $this->config->item('country');

        $last_31_days = date('Y-m-d',strtotime('-31 days'));   
        
        if( $params['show_select_query'] == true ){

            echo "
                SELECT *
                FROM `agency_tracking`
                WHERE CAST( `logged_in_datetime` AS Date ) < '{$last_31_days}'
            ";      
            echo "<br />";
            
        }


        if( $params['run_delete_query'] == true ){

            // Delete All SMS that are 7 days old or more except TY SMS
            $this->db->query("
                DELETE
                FROM `agency_tracking`
                WHERE CAST( `logged_in_datetime` AS Date ) < '{$last_31_days}'
            ");      
            $delete_1_count = $this->db->affected_rows();                      

            if( $delete_1_count > 0 ){
                return true;
            }else{
                return false;
            }

        }                                                   

    }

    // cron log flush
    public function cron_log_flush($params){        

        $country_id = $this->config->item('country');

        $last_30_days = date('Y-m-d',strtotime('-30 days'));   
        
        if( $params['show_select_query'] == true ){

            echo "
                SELECT * 
                FROM `cron_log` 
                WHERE CAST( `started` AS Date ) < '{$last_30_days}'
                AND `country_id` = {$country_id}
            ";  
            echo "<br />";

        }


        if( $params['run_delete_query'] == true ){

            $this->db->query("
                DELETE 
                FROM `cron_log` 
                WHERE CAST( `started` AS Date ) < '{$last_30_days}'
                AND `country_id` = {$country_id}
            ");      
            $delete_1_count = $this->db->affected_rows();                      

            if( $delete_1_count > 0 ){
                return true;
            }else{
                return false;
            }                                   


        }
        
        
    }

    // calendar flush
    public function calendar_flush($params){        

        $country_id = $this->config->item('country');

        // 2 years old
        $last_2_years = date('Y-m-d',strtotime('-2 years'));

        if( $params['show_select_query'] == true ){
            
            echo "
            SELECT *  
            FROM `calendar` 
            WHERE `date_start` <= '{$last_2_years}'
            ";  
            echo "<br />";
    
        }

        if( $params['run_delete_query'] == true ){

            $this->db->query("
                DELETE 
                FROM `calendar` 
                WHERE `date_start` <= '{$last_2_years}'
            ");      
            $delete_1_count = $this->db->affected_rows();                      

            if( $delete_1_count > 0 ){
                return true;
            }else{
                return false;
            }

        }                        
                                   

    }

    // tech colour flush
    public function tech_run_colour_flush($params){        

        $country_id = $this->config->item('country');

        // 7 days old
        $last_7_days = date('Y-m-d',strtotime('-7 days'));  
        
        if( $params['show_select_query'] == true ){

            echo "
                SELECT * 
                FROM `colour_table` AS ct 
                LEFT JOIN `tech_run` AS tr ON ct.`tech_run_id` = tr.`tech_run_id`
                WHERE tr.`date` <= '{$last_7_days}'
            ";
            echo "<br />";

        }
        
        if( $params['run_delete_query'] == true ){

            $this->db->query("
                DELETE ct
                FROM `colour_table` AS ct 
                LEFT JOIN `tech_run` AS tr ON ct.`tech_run_id` = tr.`tech_run_id`
                WHERE tr.`date` <= '{$last_7_days}'
            ");      
            $delete_1_count = $this->db->affected_rows();                      

            if( $delete_1_count > 0 ){
                return true;
            }else{
                return false;
            }

        }        
                                   

    }

    // notification flush
    public function notification_flush($params){        

        $country_id = $this->config->item('country');

        // 30 days old
        $last_30_days = date('Y-m-d',strtotime('-30 days'));    
        
        if( $params['show_select_query'] == true ){

            echo "
                SELECT * 
                FROM `notifications` 
                WHERE CAST(`date_created` as Date) <= '{$last_30_days}'
            ";      
            echo "<br />";

        }


        if( $params['run_delete_query'] == true ){

            $this->db->query("
                DELETE
                FROM `notifications` 
                WHERE CAST(`date_created` as Date) <= '{$last_30_days}'
            ");      
            $delete_1_count = $this->db->affected_rows();                      

            if( $delete_1_count > 0 ){
                return true;
            }else{
                return false;
            }

        }                
                                   

    }


    // message flush
    public function message_flush($params){        

        $country_id = $this->config->item('country');

        // 2 years old
        $last_2_years = date('Y-m-d',strtotime('-2 years'));     
        
        if( $params['show_select_query'] == true ){

            echo "
                SELECT *
                FROM `message` 
                WHERE CAST(`date` AS Date) <= '{$last_2_years}'
            ";  
            echo "<br />"; 

        }


        if( $params['run_delete_query'] == true ){

            $this->db->query("
                DELETE
                FROM `message` 
                WHERE CAST(`date` AS Date) <= '{$last_2_years}'
            ");      
            $delete_1_count = $this->db->affected_rows();                      

            if( $delete_1_count > 0 ){
                return true;
            }else{
                return false;
            }

        }                
                                   

    }

    // sms sent flush
    public function sms_sent_flush($params){        

        $country_id = $this->config->item('country');

        // 30 days old
        $last_30_days = date('Y-m-d',strtotime('-30 days'));  
        
        if( $params['show_select_query'] == true ){

            echo "
                SELECT * 
                FROM `sms_api_sent` 
                WHERE CAST(`created_date` AS Date) <= '{$last_30_days}'
            ";
            echo "<br />";

        }

        if( $params['run_delete_query'] == true ){

            $this->db->query("
                DELETE
                FROM `sms_api_sent` 
                WHERE CAST(`created_date` AS Date) <= '{$last_30_days}'
            ");      
            $delete_1_count = $this->db->affected_rows();                      

            if( $delete_1_count > 0 ){
                return true;
            }else{
                return false;
            }

        }                
                                   

    }


    // sms replies flush
    public function sms_replies_flush($params){        

        $country_id = $this->config->item('country');

        // 90 days old
        $last_90_days = date('Y-m-d',strtotime('-90 days')); 
        
        if( $params['show_select_query'] == true ){

            echo "
                SELECT *  
                FROM `sms_api_replies` 
                WHERE CAST(`created_date` AS Date) <= '{$last_90_days}'
            ";
            echo "<br />";

        }


        if( $params['run_delete_query'] == true ){

            $this->db->query("
                DELETE 
                FROM `sms_api_replies` 
                WHERE CAST(`created_date` AS Date) <= '{$last_90_days}'
            ");      
            $delete_1_count = $this->db->affected_rows();                      

            if( $delete_1_count > 0 ){
                return true;
            }else{
                return false;
            }

        }                
                                   

    }


    // kms flush
    public function kms_flush($params){        

        $country_id = $this->config->item('country');

        // 1 years old
        $last_1_year = date('Y-m-d',strtotime('-1 year')); 
        
        if( $params['show_select_query'] == true ){

            echo "
                SELECT *
                FROM `kms` 
                WHERE CAST(`kms_updated` AS Date) <= '{$last_1_year}'
            ";
            echo "<br />";

        }

        if( $params['run_delete_query'] == true ){

            $this->db->query("
                DELETE 
                FROM `kms` 
                WHERE CAST(`kms_updated` AS Date) <= '{$last_1_year}'
            ");      
            $delete_1_count = $this->db->affected_rows();                      

            if( $delete_1_count > 0 ){
                return true;
            }else{
                return false;
            }

        }                
                                   

    }


    // Tech Stocktake flush
    public function tech_stocktake_flush($params){        

        $country_id = $this->config->item('country');

        // 2 months old
        $last_2_months = date('Y-m-d',strtotime('-2 months'));

        $sel_sql_str = "
            SELECT `tech_stock_id` 
            FROM `tech_stock` 
            WHERE CAST(`date` AS Date) <= '{$last_2_months}'
        ";         

        if( $params['show_select_query'] == true ){

            echo $sel_sql_str;
            echo "<br />";

        }

        if( $params['run_delete_query'] == true ){

            $tech_stock_sql = $this->db->query($sel_sql_str); 

            foreach( $tech_stock_sql->result() as $tech_stock_row ){

                if( $tech_stock_row->tech_stock_id > 0 ){
    
                    // delete tech stock items     
                    $this->db->query("
                    DELETE 
                    FROM `tech_stock_items` 
                    WHERE `tech_stock_id` = {$tech_stock_row->tech_stock_id}
                    ");
                    $delete_1_count = $this->db->affected_rows();  
    
                    // delete tech stock
                    $this->db->query("
                    DELETE 
                    FROM `tech_stock` 
                    WHERE `tech_stock_id` = {$tech_stock_row->tech_stock_id}
                    ");   
                    $delete_2_count = $this->db->affected_rows();              
        
                } 
    
            }
    
            if( $delete_1_count > 0 || $delete_2_count > 0 ){
                return true;
            }else{
                return false;
            }

        }        
                                   

    }


    // agency escalate notes
    public function agency_old_escate_notes_flush($params){        

        $job_status = 'Escalate';
        $country_id = $this->config->item('country');

        $sel_sql_str = "
            SELECT 
                `agency_id`, 
                `save_notes`, 
                `escalate_notes` 
            FROM `agency` 
            WHERE `save_notes` = 1 
            AND `escalate_notes` != ''
            AND `status` = 'active' 
        ";

        if( $params['show_select_query'] == true ){

            echo $sel_sql_str;
            echo "<br /><br />";

        }

       

        // find agency with saved escalate note
        $agency_sql = $this->db->query($sel_sql_str);  

        foreach( $agency_sql->result() as $agency_row ){

            $agency_id = $agency_row->agency_id;                

            if( $agency_id > 0 ){

                // get escalate jobs 
                $sel_query = "COUNT(j.`id`) AS jcount";

                $job_params = array(
                    'sel_query' => $sel_query,                
                    
                    'del_job' => 0,
                    'p_deleted' => 0,
                    'a_status' => 'active',                

                    'agency_filter' => $agency_id,                   
                    'country_id' => $country_id,    
                    'job_status' => $job_status
                );

                // display query
                if( $params['show_select_query'] == true ){
                    $job_params['display_query'] = 1;                               
                }

                $job_sql = $this->jobs_model->get_jobs($job_params); 

                 // display query by new line
                if( $params['show_select_query'] == true ){
                    echo "<br /><br />";          
                }
   
                // no escalate jobs found
                if( $job_sql->row()->jcount == 0 ){

                    if( $params['run_delete_query'] == true ){ // run delete/clear

                        $this->db->query("
                            UPDATE `agency`
                            SET 
                                `save_notes` = NULL,
                                `escalate_notes` = NULL,
                                `escalate_notes_ts` = NULL
                            WHERE `agency_id` = {$agency_id}
                        ");

                        $this->db->query("
                            DELETE
                            FROM `escalate_agency_info`
                            WHERE `agency_id` = {$agency_id}
                        "); 

                    }  

                }                                                                                            
    
            } 

        }

                
                                   

    }


    public function process_send_letters(){    
        
        $this->load->model('inc/email_functions_model');

        $country_id = $this->config->item('country');
        $job_status = 'Send Letters';        
        $today = date("Y-m-d");
        $today_full = date('Y-m-d H:i:s');
        $staff_id = -3; // CRON 

        // get to be booked jobs 
        $sel_query = "                    
            j.`id` AS jid, 
            j.`property_vacant`,
            j.`comments` AS j_comments,  

            p.`property_id`,
            p.`address_1` AS p_address_1, 
			p.`address_2` AS p_address_2, 
			p.`address_3` AS p_address_3, 
			p.`state` AS p_state, 
            p.`postcode` AS p_postcode,
            p.`comments` AS p_comments,
            
            a.`agency_id`,
            a.`agency_name`,
            a.`agency_emails`,
            a.`new_job_email_to_agent`,

            ajt.`id`,
            ajt.`type` AS service_type
        ";
        
        $job_params = array(
            'sel_query' => $sel_query,            
            
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',

            'join_table' => array('alarm_job_type'),

            'job_status' => $job_status,                   
            'country_id' => $country_id,                           
                                    
            'display_query' => 1
        );

        $job_sql = $this->jobs_model->get_jobs($job_params);   
        
        echo "<br /><br />";
        
        
        // process
        foreach( $job_sql->result() as $job_row ){

            $job_id = $job_row->jid;
            $property_id = $job_row->property_id;

            $has_conditions = false;
            $has_sms_tenant = false;
            $has_email_tenant = false;
            $has_email_no_tenant_to_agency = false;

            // job or property comments
            if( $job_row->j_comments != "" || $job_row->p_comments != "" ){                
                $has_conditions = true;
            }

            if( $job_id > 0 && $property_id > 0 && $has_conditions == false ){

                // if job is marked as property vacant, send no tenant email to agency
                if(  $job_row->property_vacant == 1 ){

                    $no_tenants = true;

                }else{

                    // get tenants 
                    $sel_query = "
                        pt.`property_tenant_id`,
                        pt.`tenant_firstname`,
                        pt.`tenant_lastname`,
                        pt.`tenant_mobile`,
                        pt.`tenant_email`
                    ";
                    $params = array(
                        'sel_query' => $sel_query,
                        'property_id' => $property_id,
                        'pt_active' => 1,
                        'display_query' => 1
                    );
                    $pt_sql = $this->properties_model->get_property_tenants($params);

                    $no_tenants = false;
                    $has_tenant_email = false;
                    $has_tenant_mobile = false;

                    if( $pt_sql->num_rows() > 0 ){

                        // loop through tenants
                        foreach($pt_sql->result() as $pt_row){

                            if( $pt_row->tenant_email != ""  ){ // if tenant has email

                                $has_tenant_email = true;
                                            
                            }else if( $pt_row->tenant_mobile != "" ){ // if tenant has mobile

                                $has_tenant_mobile = true;
                            
                            }

                        }                                  

                    }else{ // no tenants

                        $no_tenants = true;

                    }

                }

                

                // has mobile but no email
                if( $has_tenant_mobile == true && $has_tenant_email == false ){
                    
                    $params = array(
                        "job_id" => $job_id,
                        "staff_id" => $staff_id
                    );
                    $this->send_letters_sms_tenant($params);
                    $has_sms_tenant = true;

                }
                
                // has email
                if( $has_tenant_email == true ){
                  
                    $params = array(
                        "job_id" => $job_id,
                        "staff_id" => $staff_id
                    );
                    $this->send_letters_email_tenant($params);
                    $has_email_tenant = true;

                }
                
                // no tenants
                if( $no_tenants == true ){
                    
                    $params = array(
                        "job_id" => $job_id,
                        "staff_id" => $staff_id
                    );
                    $this->send_letters_no_tenant_email_to_agency($params);
                    $has_email_no_tenant_to_agency = true;        

                } 
                
                // if it missed the 3 catches above, needs to update to TBB
                if( $has_sms_tenant == false && $has_email_tenant == false && $has_email_no_tenant_to_agency == false ){

                    // update jobs
                        $this->db->query("
                        UPDATE `jobs`
                        SET `status` = 'To Be Booked'
                        WHERE `id` = {$job_id}
                    ");

                    //insert new job log 
                    $auto_process =  ( $staff_id > 0 )?0:1;
                    $log_params = array(
                        'title' => 59,  // Job type updated
                        'details' => "This job was updated from <b>Send Letters</b> to <b>To Be Booked</b>",
                        'display_in_vjd' => 1,
                        'created_by_staff' => $staff_id,
                        'auto_process' => $auto_process,
                        'job_id' => $job_id
                    );
                    $this->system_model->insert_log($log_params);
                    
                }


            }                                   

        }  
        
                                   

    }


    public function send_no_show_sms(){        

        $country_id = $this->config->item('country');
        $job_status = 'Pre Completion';

        $today = date('Y-m-d');
        $today_full = date("Y-m-d H:i:s");
        $sms_sent = false;

        $custom_where = "
        j.`job_reason_id` = 1 AND
        (
            CAST( j.`sms_sent_no_show` AS Date ) != '{$today}' OR
            j.`sms_sent_no_show` IS NULL
        )";

        // get to be booked jobs 
        $sel_query = "                    
            j.`id` AS jid,
            j.`booked_with`,
            j.`property_id`  
        ";
        
        $job_params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',

            'job_status' => $job_status,                   
            'country_id' => $country_id,            
                                    
            'display_query' => 0
        );

        $job_sql = $this->jobs_model->get_jobs($job_params);                   
        
        
        // process
        foreach( $job_sql->result() as $job_row ){

            $job_id = $job_row->jid;
            $property_id = $job_row->property_id;
            $booked_with = $job_row->booked_with;
            $staff_id = -3; // CRON 

            if( $job_id > 0 && $property_id > 0 ){

                $no_show_params = array(
                    'job_id' => $job_id,
                    'property_id' => $property_id,
                    'booked_with' => $booked_with,
                    'staff_id' => $staff_id
                );
                $this->send_no_show_sms_per_job($no_show_params);                                                           

            }            

        }    
        
                                   

    }


    public function send_no_show_sms_per_job($params){

        $job_id = $params['job_id'];
        $property_id = $params['property_id'];
        $booked_with = $params['booked_with'];
        $staff_id = $params['staff_id'];

        $sms_type = 4; // No-Show
        $auto_process =  ( $staff_id > 0 )?0:1;       
        $today_full = date("Y-m-d H:i:s");

        if( $job_id > 0 && $property_id > 0 ){


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
                    $job_log = "SMS to {$booked_with_tenant} ({$send_to}) <strong>\"{$parsed_template_body}\"</strong>";                        
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
                   
            
            if( $sms_sent == true ){

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


    public function send_letters_sms_tenant($params){                  

        $country_id = $this->config->item('country');        
        $job_id = $params['job_id'];

        // send SMS
        $sms_type = 24; // send letters
        $staff_id = $params['staff_id']; // can be staff or cron
        $auto_process =  ( $staff_id > 0 )?0:1;

        // get country data
        $country_params = array(
            'sel_query' => '
                c.`agent_number`, 
                c.`outgoing_email`,
                c.`tenant_number`,
                c.`trading_name`
            ',
            'country_id' => $country_id
        );
        $country_sql = $this->system_model->get_countries($country_params);
        $country_row = $country_sql->row();

        if( $job_id > 0 ){

            // get job data via ID
            $sel_query = "                    
                j.`id` AS jid, 
                j.`property_vacant`,

                p.`property_id`,
                p.`address_1` AS p_address_1, 
                p.`address_2` AS p_address_2, 
                p.`address_3` AS p_address_3, 
                p.`state` AS p_state, 
                p.`postcode` AS p_postcode,
                
                a.`agency_id`,
                a.`agency_name`,
                a.`agency_emails`,
                a.`new_job_email_to_agent`,
                a.`franchise_groups_id`,

                ajt.`id`,
                ajt.`type` AS service_type
            ";

            $job_params = array(
                'sel_query' => $sel_query,
                'job_id' => $job_id,
                'join_table' => array('alarm_job_type'),                                                       
                'display_query' => 1
            );

            $job_sql = $this->jobs_model->get_jobs($job_params);                   
                
            
            // loop through jobs
            foreach( $job_sql->result() as $job_row ){

                $property_id = $job_row->property_id;
                $agency_id = $job_row->agency_id;

                // property address
                $paddress = "{$job_row->p_address_1} {$job_row->p_address_2}, {$job_row->p_address_3}";

                $agency_name = $job_row->agency_name;
                $agency_emails =  $job_row->agency_emails;
                $franchise_groups_id =  $job_row->franchise_groups_id;      
                
                $ten_that_has_mob = [];

                if( $job_id > 0 && $property_id > 0 ){

                    // do not send SMS to tenants if job is marked as property vacant bec this means tenants have not yet moved in
                    if( $job_row->property_vacant != 1 ){

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

                        // loop through tenants
                        foreach($pt_sql->result() as $pt_row){
                            
                            // SMS tenants
                            if( $pt_row->tenant_mobile != "" ){ // has mobile
                                
                                // tenant name 
                                $ten_that_has_mob[] = "{$pt_row->tenant_firstname} {$pt_row->tenant_lastname}";												

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

                            }
                        
                        }

                    }                    
                    
                                    
                    if( count($ten_that_has_mob) > 0 ){

                        //insert new job log                        
                        $comb_tenant_names = $this->system_model->combine_tenant_names($ten_that_has_mob);                        
                        
                        $log_params = array(
                            'title' => 60, // Tenant Welcome SMS
                            'details' => "SMS to {$comb_tenant_names} ({$send_to}) <strong>{$parsed_template_body}</strong>",
                            'display_in_vjd' => 1,
                            'created_by_staff' => $staff_id,
                            'auto_process' => $auto_process,
                            'job_id' => $job_id
                        );
                        $this->system_model->insert_log($log_params);

                        //insert new property log            
                        $log_params = array(
                            'title' => 60, // Tenant Welcome SMS
                            'details' => "Welcome SMS Sent, see job ID <a href=\"{$this->config->item('crm_link')}/view_job_details.php?id={$job_id}\">$job_id</a>",
                            'display_in_vpd' => 1,
                            'created_by_staff' => $staff_id,
                            'auto_process' => $auto_process,
                            'property_id' => $property_id,
                        );
                        $this->system_model->insert_log($log_params);                                                                        

                    }

                    // update jobs
                    $this->db->query("
                        UPDATE `jobs`
                        SET `status` = 'To Be Booked'
                        WHERE `id` = {$job_id}
                    ");

                    //insert new job log 
                    $log_params = array(
                        'title' => 59,  // Job type updated
                        'details' => "This job was updated from <b>Send Letters</b> to <b>To Be Booked</b>",
                        'display_in_vjd' => 1,
                        'created_by_staff' => $staff_id,
                        'auto_process' => $auto_process,
                        'job_id' => $job_id
                    );
                    $this->system_model->insert_log($log_params);
                    
                    // update property
                    $this->db->query("
                        UPDATE property
                        SET `tenant_ltr_sent` = '".date("Y-m-d")."'
                        WHERE `property_id` = {$property_id}
                    ");   
                    
                    // send to email if allow on agency preference
                    if( $job_row->new_job_email_to_agent == 1 ){

                        $email_func_params = array(
                            'paddress' => $paddress,
                            'agency_name' => $agency_name,
                            'agency_emails' => $agency_emails,
                            'franchise_groups_id' => $franchise_groups_id
                        );
                        $this->email_functions_model->send_letters_email_to_agency($email_func_params);  
                        
                        //insert new agency log                           
                        $log_params = array(
                            'title' => 58, // Agency Notification Email
                            'details' => "Notification email sent to agency, \"The Tenants at {$paddress} have now been notified that SATS will be contacting them to book an appointment to service their property\"",
                            'display_in_vjd' => 1,
                            'created_by_staff' => $staff_id,
                            'auto_process' => $auto_process,
                            'job_id' => $job_id
                        );
                        $this->system_model->insert_log($log_params);  

                    }


                }            

            }

        }            
                                           

    }



    public function send_letters_email_tenant($params){                  

        $country_id = $this->config->item('country');        
        $job_id = $params['job_id'];

        // get country data
        $country_params = array(
            'sel_query' => '
                c.`agent_number`, 
                c.`outgoing_email`,
                c.`tenant_number`,
                c.`trading_name`
            ',
            'country_id' => $country_id
        );
        $country_sql = $this->system_model->get_countries($country_params);
        $country_row = $country_sql->row();

        // send SMS        
        $staff_id = $params['staff_id']; // can be staff or cron
        $auto_process =  ( $staff_id > 0 )?0:1;

        if( $job_id > 0 ){

            // get job data via ID
            $sel_query = "                    
                j.`id` AS jid, 
                j.`property_vacant`,

                p.`property_id`,
                p.`address_1` AS p_address_1, 
                p.`address_2` AS p_address_2, 
                p.`address_3` AS p_address_3, 
                p.`state` AS p_state, 
                p.`postcode` AS p_postcode,
                
                a.`agency_id`,
                a.`agency_name`,
                a.`agency_emails`,
                a.`new_job_email_to_agent`,
                a.`franchise_groups_id`,

                ajt.`id`,
                ajt.`type` AS service_type,
                ajt.`full_name` AS service_full_name     
            ";

            $job_params = array(
                'sel_query' => $sel_query,
                'job_id' => $job_id,
                'join_table' => array('alarm_job_type'),                                                       
                'display_query' => 1
            );

            $job_sql = $this->jobs_model->get_jobs($job_params);                   
                
            
            // loop through jobs
            foreach( $job_sql->result() as $job_row ){

                $property_id = $job_row->property_id;
                $agency_id = $job_row->agency_id;

                // property address
                $paddress = "{$job_row->p_address_1} {$job_row->p_address_2}, {$job_row->p_address_3}";

                $agency_name = $job_row->agency_name;
                $agency_emails =  $job_row->agency_emails;
                $franchise_groups_id =  $job_row->franchise_groups_id;  
                $service_type = $job_row->service_full_name;   
                
                $ten_that_has_email = [];
                $tenant_email_arr = [];

                if( $job_id > 0 && $property_id > 0 ){

                    // do not send EMAIL to tenants if job is marked as property vacant bec this means tenants have not yet moved in
                    if( $job_row->property_vacant != 1 ){

                        // get tenants 
                        $sel_query = "
                            pt.`property_tenant_id`,
                            pt.`tenant_firstname`,
                            pt.`tenant_lastname`,                    
                            pt.`tenant_email`                    
                        ";
                        $params = array(
                            'sel_query' => $sel_query,
                            'property_id' => $property_id,
                            'pt_active' => 1,
                            'display_query' => 0
                        );
                        $pt_sql = $this->properties_model->get_property_tenants($params);

                        // loop through tenants
                        foreach($pt_sql->result() as $pt_row){
                            
                            // SMS tenants
                            if( $pt_row->tenant_email != "" ){ // has email
                                
                                // tenant name 
                                $ten_that_has_email[] = "{$pt_row->tenant_firstname} {$pt_row->tenant_lastname}";

                                // tenant email
                                $tenant_email_arr[] = $pt_row->tenant_email;                                                                   

                            }
                        
                        }
                            
                    }                   
                    
                                    
                    // Email
                    if( count($ten_that_has_email) > 0 ){
                        
                        $comb_tenant_names = $this->system_model->combine_tenant_names($ten_that_has_email);

                        // tenant welcome email switch
                        if ( $this->system_model->getAgencyPrivateFranchiseGroups($franchise_groups_id) == true ){ // private FG, do not show agency Name
                            $private_agency_name_txt = null;
                        }else{ // default, display agency name
                            $private_agency_name_txt = " and {$agency_name}";
                        }

                        $tenant_welcome_txt = "Recently your Landlord{$private_agency_name_txt} engaged the services of {$country_row->trading_name} (SATS) to service the {$service_type} at the property you occupy.";

                        $email_func_params = array(
                            'paddress' => $paddress,
                            'agency_name' => $agency_name,                            
                            'comb_tenant_names' => $comb_tenant_names,
                            'tenant_email_arr' => $tenant_email_arr,
                            'service_type' => $service_type,
                            'tenant_welcome_txt' => $tenant_welcome_txt
                        );
                        $this->email_functions_model->send_letters_email_to_tenants($email_func_params);                                                    

                        //insert new job log 
                        $log_params = array(
                            'title' => 57, // Tenant Welcome Email
                            'details' => "Welcome email sent to tenant, \"{$tenant_welcome_txt}\"",
                            'display_in_vjd' => 1,
                            'created_by_staff' => $staff_id,
                            'auto_process' => $auto_process,
                            'job_id' => $job_id
                        );
                        $this->system_model->insert_log($log_params);

                        //insert new property log                           
                        $log_params = array(
                            'title' => 57, // Tenant Welcome Email 
                            'details' => "Welcome Email Sent, see job ID <a href=\"{$this->config->item('crm_link')}/view_job_details.php?id={$job_id}\">$job_id</a>",
                            'display_in_vpd' => 1,
                            'created_by_staff' => $staff_id,
                            'auto_process' => $auto_process,
                            'property_id' => $property_id,
                        );
                        $this->system_model->insert_log($log_params);                                                                                                    

                    }

                    // update jobs
                    $this->db->query("
                        UPDATE `jobs`
                        SET `status` = 'To Be Booked'
                        WHERE `id` = {$job_id}
                    ");

                    //insert new job log 
                    $log_params = array(
                        'title' => 59,  // Job type updated
                        'details' => "This job was updated from <b>Send Letters</b> to <b>To Be Booked</b>",
                        'display_in_vjd' => 1,
                        'created_by_staff' => $staff_id,
                        'auto_process' => $auto_process,
                        'job_id' => $job_id
                    );
                    $this->system_model->insert_log($log_params);
                    
                    // update property
                    $this->db->query("
                        UPDATE property
                        SET `tenant_ltr_sent` = '".date("Y-m-d")."'
                        WHERE `property_id` = {$property_id}
                    ");   
                    
                    // send to email if allow on agency preference
                    if( $job_row->new_job_email_to_agent == 1 ){

                        $email_func_params = array(
                            'paddress' => $paddress,
                            'agency_name' => $agency_name,
                            'agency_emails' => $agency_emails,
                            'franchise_groups_id' => $franchise_groups_id
                        );
                        $this->email_functions_model->send_letters_email_to_agency($email_func_params); 
                        
                        //insert new agency log                           
                        $log_params = array(
                            'title' => 58, // Agency Notification Email
                            'details' => "Notification email sent to agency, \"The Tenants at {$paddress} have now been notified that SATS will be contacting them to book an appointment to service their property\"",
                            'display_in_vjd' => 1,
                            'created_by_staff' => $staff_id,
                            'auto_process' => $auto_process,
                            'job_id' => $job_id
                        );
                        $this->system_model->insert_log($log_params);   

                    }


                }            

            }

        }            
                                           

    }



    public function send_letters_no_tenant_email_to_agency($params){                  

        $country_id = $this->config->item('country');        
        $job_id = $params['job_id'];
        $today_full = date('Y-m-d H:i:s');

        // send SMS        
        $staff_id = $params['staff_id']; // can be staff or cron
        $auto_process =  ( $staff_id > 0 )?0:1;

        if( $job_id > 0 ){

            // get job data via ID
            $sel_query = "                    
                j.`id` AS jid, 
                j.`property_vacant`,

                p.`property_id`,
                p.`address_1` AS p_address_1, 
                p.`address_2` AS p_address_2, 
                p.`address_3` AS p_address_3, 
                p.`state` AS p_state, 
                p.`postcode` AS p_postcode,
                p.`holiday_rental`,
                
                a.`agency_id`,
                a.`agency_name`,
                a.`agency_emails`,
                a.`new_job_email_to_agent`,
                a.`franchise_groups_id`,

                ajt.`id`,
                ajt.`type` AS service_type,
                ajt.`full_name` AS service_full_name     
            ";

            $job_params = array(
                'sel_query' => $sel_query,
                'job_id' => $job_id,
                'join_table' => array('alarm_job_type'),                                                       
                'display_query' => 1
            );

            $job_sql = $this->jobs_model->get_jobs($job_params);                   
                
            
            // loop through jobs
            foreach( $job_sql->result() as $job_row ){

                $property_id = $job_row->property_id;
                $agency_id = $job_row->agency_id;

                // property address
                $paddress = "{$job_row->p_address_1} {$job_row->p_address_2}, {$job_row->p_address_3}";

                $agency_name = $job_row->agency_name;
                $agency_emails =  $job_row->agency_emails;   
                $franchise_groups_id =  $job_row->franchise_groups_id;                        

                if( $job_id > 0 && $property_id > 0 ){

                                                            
                    
                    if( $job_row->property_vacant == 1 || $job_row->holiday_rental == 1 ){

                        // if property is vacant, send letter jobs to, to be booked
                        $this->db->query("
                            UPDATE jobs
                            SET `status` = 'To Be Booked'
                            WHERE `status` = 'Send Letters'
                            AND `id` = {$job_id}
                        ");

                        // send to email if allow on agency preference
                        if( $job_row->new_job_email_to_agent == 1 ){

                            // no tenant, email agency instead
                            $email_func_params = array(                        
                                'paddress' => $paddress,
                                'agency_name' => $agency_name,
                                'agency_emails' => $agency_emails,
                                'franchise_groups_id' => $franchise_groups_id
                            );
                            $this->email_functions_model->send_letters_email_to_agency_no_tenants($email_func_params); 

                             //insert new agency log                           
                            $log_params = array(
                                'title' => 58, // Agency Notification Email
                                'details' => "Notification Email sent to agency, \"{$paddress} is in our system as vacant. We will be contacting you soon for key access.\"",
                                'display_in_vjd' => 1,
                                'created_by_staff' => $staff_id,
                                'auto_process' => $auto_process,
                                'job_id' => $job_id
                            );
                            $this->system_model->insert_log($log_params);  

                        }                        

                        //insert new job log 
                        $log_params = array(
                            'title' => 59,  // Job type updated
                            'details' => "This job was updated from <b>Send Letters</b> to <b>To Be Booked</b>",
                            'display_in_vjd' => 1,
                            'created_by_staff' => $staff_id,
                            'auto_process' => $auto_process,
                            'job_id' => $job_id
                        );
                        $this->system_model->insert_log($log_params);

                    }else{

                        // move to escalate
                        $this->db->query("
                            UPDATE jobs
                            SET `status` = 'Escalate'
                            WHERE `status` = 'Send Letters'
                            AND `id` = {$job_id}
                        ");
                        
                        // esclate job reason, verify tenant detail ID
                        $verify_tenant_details_id = 1;
                        
                        // clear any 'Verify Tenant Details' escalate job reason first, to avoid duplicate entry	
                        $this->db->query("
                            DELETE 
                            FROM `selected_escalate_job_reasons`
                            WHERE `job_id` = {$job_id}
                            AND `escalate_job_reasons_id` = {$verify_tenant_details_id}
                        ");

                        // insert escalate job reason - Verify Tenant Details
                        $insert_data = array(
                            'job_id' => $job_id,
                            'escalate_job_reasons_id' => $verify_tenant_details_id,
                            'date_created' => $today_full,
                            'deleted' => 0,
                            'active' => 1
                        );                        
                        $this->db->insert('selected_escalate_job_reasons', $insert_data);

                        // send to email if allow on agency preference
                        if( $job_row->new_job_email_to_agent == 1 ){

                            // no tenant, email agency instead
                            $email_func_params = array(                        
                                'paddress' => $paddress,
                                'agency_name' => $agency_name,
                                'agency_emails' => $agency_emails,
                                'franchise_groups_id' => $franchise_groups_id
                            );
                            $this->email_functions_model->send_letters_email_to_agency_no_tenants_escalate($email_func_params);  
                            
                            //insert new agency log                           
                            $log_params = array(
                                'title' => 58, // Agency Notification Email
                                'details' => "Notification Email sent to agency, “{$paddress} is in our system ready for booking. We will be contacting you soon for updated tenant details.",
                                'display_in_vjd' => 1,
                                'created_by_staff' => $staff_id,
                                'auto_process' => $auto_process,
                                'job_id' => $job_id
                            );
                            $this->system_model->insert_log($log_params);                                                           

                        }

                        //insert new job log 
                        $log_params = array(
                            'title' => 59,  // Job type updated
                            'details' => "This job was updated from <b>Send Letters</b> to <b>Escalate</b>",
                            'display_in_vjd' => 1,
                            'created_by_staff' => $staff_id,
                            'auto_process' => $auto_process,
                            'job_id' => $job_id
                        );
                        $this->system_model->insert_log($log_params);
                        
                    }


                     //insert new property log
                     $lot_type = 55; // No Tenant Letter Sent
                     $log_details = "No Tenant Details Available on ".date('d/m/Y');                     

                     $log_params = array(
                         'title' => $lot_type, 
                         'details' => $log_details,
                         'display_in_vpd' => 1,
                         'created_by_staff' => $staff_id,
                         'auto_process' => $auto_process,
                         'property_id' => $property_id,
                     );
                     $this->system_model->insert_log($log_params);


                }            

            }

        }            
                                           

    }

    public function update_page_totals(){   

        $page_url = $this->input->post('page_url');
        $country_id = $this->config->item('country');

        $sleep_interval = 1; // by seconds

        // old crm
        // platform invoicing
        $page_url = "{$this->config->item('crm_link')}/platform_invoicing.php";

		$query = $this->db->query("
        SELECT j.`id` AS jid
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
        GROUP BY j.id
        ");
        $total_rows = $query->num_rows();

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );

        $this->system_model->update_page_total($page_tot_params);
        
        // sleep interval
        sleep($sleep_interval);

        // CI Version
        // platform invoicing
        $page_url = '/jobs/platform_invoicing';

        //return count($query->result()); 
        //End

		$query = $this->db->query("
            SELECT j.`id` AS jid
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
            GROUP BY j.id
        ");
        $total_rows = $query->num_rows();

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );

        $this->system_model->update_page_total($page_tot_params);
        
        // sleep interval
        sleep($sleep_interval);
        
        
        // JOBS
        // after hours
        $page_url = '/jobs/after_hours';

        $sel_query = "COUNT(j.`id`) AS jcount";
        $custom_where = "( j.`status` = 'To Be Booked' OR j.`status` = 'Escalate' OR j.`status` = 'Booked' )";
        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $this->config->item('country'),
            'out_of_tech_hours' => 1,
            'display_query' => 0
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);

  
        
        
        // allocate
        $page_url = '/jobs/allocate';

        $sel_query = "COUNT(j.`id`) AS jcount";
        $job_status = 'Allocate';
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' =>$job_status
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);


        // to be invoice
        $page_url = '/jobs/to_be_invoiced';

        $sel_query = "COUNT(j.`id`) AS jcount";
        $job_status = 'To Be Invoiced';
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' =>$job_status
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);

 
        
        // BNE to call
        $page_url = '/jobs/bne_to_call';

        $sel_query = "COUNT(j.`id`) AS jcount";
        $custom_where = "p.`bne_to_call` = 1 AND j.status NOT IN('Completed','Cancelled','Merged Certificates','Booked','Pre Completion')";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'custom_where' => $custom_where
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);

   
   
        // dha
        $page_url = '/jobs/dha';

        $sel_query = "COUNT(j.`id`) AS jcount";
        $job_status = 'DHA';
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);

     
        
     
        // escalate
        $page_url = '/jobs/escalate';

        $sel_query = "COUNT(j.`id`) AS jcount";
        $job_status = 'Escalate';
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'job_status' => $job_status,
            'country_id' => $country_id
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);

    

        // Short Term Rental
        $page_url = '/jobs/holiday_rentals';

        $sel_query = "COUNT(j.`id`) AS jcount";
        $custom_where = "p.`holiday_rental` = 1 AND j.`status` NOT IN('Completed','Cancelled','Merged Certificates','Booked','Pre Completion') ";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'custom_where' => $custom_where
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);


        
       
        // on hold
        $page_url = '/jobs/on_hold';

        $sel_query = "COUNT(j.`id`) AS jcount";
        $job_status="On Hold";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);

    

        // merge
        $page_url = '/jobs/merged_jobs';

        $sel_query = "COUNT(j.`id`) AS jcount";
        $job_status="Merged Certificates";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);

     

       
        // new jobs
        $page_url = '/jobs/new_jobs';

        $send_letter_count = 0;
        $sel_query = "j.`id` AS jid, j.`comments` AS j_comments,  p.`comments` AS p_comments";
        $job_status="Send Letters";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status
        );
        $query = $this->jobs_model->get_jobs($params);
        foreach( $query->result() as $job_row ){

            // job or property comments
            if( $job_row->j_comments != "" || $job_row->p_comments != "" ){                
                $send_letter_count++;
            }

        }

        $total_rows = $send_letter_count;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);



        // Pre Completion
        $page_url = '/jobs/pre_completion';

        $exclude_is_sales_in_total_count = "p.is_sales!=1"; //dont count is_sales properties > as per Ness request
        $send_letter_count = 0;
        $sel_query = "COUNT(j.`id`) AS jcount";
        $job_status="Pre Completion";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'custom_where_arr' => array($exclude_is_sales_in_total_count),
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);

        // TBB is_sales
        $page_url = '/jobs/to_be_booked?is_sales=1';
        
        $job_status = 'To Be Booked';
        $join_table_array = array('job_type','alarm_job_type','staff_accounts');
        $sel_query = "j.`id`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'job_status' => $job_status,
            'join_table' => $join_table_array,
            'country_id' => COUNTRY,
            'is_sales' => 1

        );
        $query = $this->jobs_model->get_jobs($params);
         $total_rows = $query->num_rows();
 
         // update page total
         $page_tot_params = array(
             'page' => $page_url,
             'total' => $total_rows
         );
         $this->system_model->update_page_total($page_tot_params);
 
         // sleep interval
         sleep($sleep_interval);


        // Service Due
        $page_url = '/jobs/service_due';

        $sel_query = "COUNT(j.`id`) AS jcount";
        $job_status = 'Pending';
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);




        
        // Vacant
        $page_url = '/jobs/vacant';

        $sel_query = "COUNT(j.`id`) AS jcount";
        $custom_where = "j.property_vacant = 1 AND j.status NOT IN('Completed','Cancelled','Merged Certificates','Booked','Pre Completion') ";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'custom_where' => $custom_where
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval); 



        
        // DAILY ITEMS
        // 30+ Day old Jobs
        $page_url = '/jobs/ageing_jobs_30_to_60';

        $date_span_from = date('Y-m-d', strtotime("-60 days"));
        $date_span_to = date('Y-m-d', strtotime("-30 days"));

        $sel_query = "COUNT(j.`id`) AS jcount";
        $custom_where = "
            (j.`status` = 'To Be Booked' OR j.`status` = 'Pre Completion' OR j.`status` = 'Booked' OR j.`status` = 'Escalate') 
            AND CAST(j.`created` AS DATE) BETWEEN '{$date_span_from}' 
            AND '{$date_span_to}' 
            AND p.`holiday_rental` != 1
        ";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'custom_where' => $custom_where,
            'display_query' => 0
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);





        // 60+ Day old Jobs
        $page_url = '/jobs/ageing_jobs_60_to_90';

        $date_span_from = date('Y-m-d', strtotime("-90 days"));
        $date_span_to = date('Y-m-d', strtotime("-60 days"));

        $sel_query = "COUNT(j.`id`) AS jcount";
        $custom_where = "
            (j.`status` = 'To Be Booked' OR j.`status` = 'Pre Completion' OR j.`status` = 'Booked' OR j.`status` = 'Escalate') 
            AND CAST(j.`created` AS DATE) BETWEEN '{$date_span_from}' 
            AND '{$date_span_to}' 
            AND p.`holiday_rental` != 1
        ";

        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'custom_where' => $custom_where,
            'display_query' => 0
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);




        // 90+ Day old Job
        $page_url = '/jobs/ageing_jobs_90';

        $last_90_days = date('Y-m-d', strtotime("-90 days"));
        
        $sel_query = "COUNT(j.`id`) AS jcount";            
        $custom_where = "
            (j.`status` = 'To Be Booked' OR j.`status` = 'Pre Completion' OR j.`status` = 'Booked' OR j.`status` = 'Escalate') 
            AND CAST(j.`created` AS DATE) < '{$last_90_days}' 
            AND p.`holiday_rental` != 1
        ";
        
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'custom_where' => $custom_where,
            'display_query' => 0
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);




        // Active Unsold Services
        $page_url = '/daily/active_unsold_services';
        
        $sel_query = "COUNT(ps.`property_services_id`) AS jcount";            
        $custom_where = "agen_serv.`agency_services_id` IS NULL";
    
        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'ps_service' => 1,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,

            'join_table' => array('alarm_job_type'),

            'custom_joins' => array(
                'join_table' => '`agency_services` AS agen_serv',
                'join_on' => '( agen_serv.`agency_id` = a.`agency_id` AND agen_serv.`service_id` = ps.`alarm_job_type_id` )',
                'join_type' => 'left'
            ),

            'display_query' => 0
        );
        $query = $this->properties_model->getPropertyServices($params);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);

        

        // no job status
        $page_url = '/daily/no_job_status';
            
        $sql_str = "
        SELECT COUNT(j.`id`) AS jcount
        FROM `jobs` AS j 
        LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
        LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        WHERE (
            j.`status` = '' OR
            j.`status` IS NULL
        )
        ";        
        $query = $this->db->query($sql_str);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);




        // No Job Types
        $page_url = '/daily/no_job_types';
        
        $sql_str = "
        SELECT COUNT(j.`id`) AS jcount
        FROM `jobs` AS j 
        LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
        LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        WHERE (
            j.`job_type` = '' OR
            j.`job_type` IS NULL OR
            j.`job_type` = 'None Selected'
        )
        ";        
        $query = $this->db->query($sql_str);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);





        // Daily Items > Dirty Address
        $page_url = '/reports/dirty_address/';
        
        $this->load->model('reports_model');
        
        $total_sel_query = "COUNT('property_id') as p_count";
        $total_params = array(
            'sel_query' => $total_sel_query,
        );
        $query = $this->reports_model->get_dirty_address($total_params);
        $total_rows = $query->row()->p_count;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);


        // Daily Items > Duplicate Postcode
        $page_url = '/properties/duplicate_postcode';
                                
        $duplicate = $this->properties_model->getPostcodeDuplicatesV2();
        $count = 0;
        
        if(!empty($duplicate)){
            foreach($duplicate as $pc){
                $count++;
            }
        }

        $total_rows = $count;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);



        // DAILY > duplicate_properties
        $page_url = '/properties/duplicate_properties';
            
        $params_total = array(
            'sel_query' => "COUNT( * ) AS jcount"
        );
        $query = $this->properties_model->jFindDupProp($params_total);
        $total_rows = $query->num_rows();

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);




        // DAILY > Property Needs Verification
        $page_url = '/property_me/properties_needs_verification';

        // exclude already NLM properties
        $custom_where = "( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )";
        
        // total row
        $sel_query = "COUNT(pnv.`pnv_id`) AS pnv_count";
        $params = array(
            'sel_query' => $sel_query,                                                                
            'active' => 1,
            'ignore_issue' => 0,
            'custom_where' => $custom_where
        );
        $tot_row_sql = $this->properties_model->get_properties_needs_verification($params);
        $total_rows = $tot_row_sql->row()->pnv_count;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);





        // DAILY > multiple jobs
        $page_url = '/daily/multiple_jobs';

        // all rows
        $total_sel_query = "COUNT(j.id) as jcount";
        $total_params = array(
            'sel_query' => $total_sel_query,
            'custom_where' => "a.`allow_upfront_billing`=0",
        );
        $query = $this->daily_model->getMultipleJobs($total_params);
        $total_rows = $query->num_rows();

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);




        // DAILY > str_less_jobs
        $page_url = '/daily/str_less_jobs';
    
        $sel_query = "
            j.`id` AS jid
        ";
        $params = array(
            'sel_query' => $sel_query,
        );
        $lists = $this->daily_model->findBookedJobsNotOnAnySTR($params);

        $counter = 0;
        foreach($lists->result_array() as $row){
            if( !$this->daily_model->findJobsOnSTR($row['jid']) ){
        
                $counter++;
            }
        }

        $total_rows = $counter;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);


        

        // DAILY > missing_region
        $page_url = '/daily/missing_region';

        $sel_query = "
        p.`property_id`, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3, 
        p.`state` AS p_state,
        p.`postcode` AS p_postcode, 
        
        a.`agency_id`, 
        a.`agency_name` 
        ";

        // paginated
        $params = array(
            'sel_query' => $sel_query,			

            'p_deleted' => 0,
            'a_status' => 'active',					

            'sort_list' => array(
                array(
                    'order_by' => 'p.`address_2`',
                    'sort' => 'ASC',
                ),
                array(
                    'order_by' => 'p.`address_1`',
                    'sort' => 'ASC',
                ),
            ),
            'display_query' => 0
        );
        $lists = $this->properties_model->get_properties($params);

        $total_rows = 0;
        $count = 0;
        if( $lists->num_rows() > 0 ){
            foreach($lists->result_array() as $row){

                // check if postcode exist on region postcode list
                if( $row['p_postcode'] != '' ){

                    $check_postcode = $this->daily_model->check_postcode_exist_on_list($row['p_postcode']);

                    // it didnt find postcode on region postcode list
                    if( $check_postcode == false ){ 
                        $count++;
                    }
                }
            }
        }
        $total_rows = $count;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);





        // DAILY > action_required_jobs
        $page_url = '/daily/action_required_jobs';
            
        $job_status = 'Action Required';

        //Total Rows
        $sel_query = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $this->config->item('country'),
            'job_status' => $job_status,
        );
        $query =  $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);





        // DAILY > incorrectly_upgraded_properties
        $page_url = '/daily/incorrectly_upgraded_properties';

        $custom_where = "(ps.`service` = 1 AND (ps.`alarm_job_type_id` != 12 AND ps.`alarm_job_type_id` != 13 AND ps.`alarm_job_type_id` != 14 AND ps.`alarm_job_type_id` != 11 AND ps.`alarm_job_type_id` != 6 AND ps.`alarm_job_type_id` != 20)) 
        AND ((j.job_type = 'IC Upgrade' AND j.status = 'Completed') OR p.prop_upgraded_to_ic_sa = 1 )
        AND p.is_sales!=1 AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )";
        
        $sel_query = "p.property_id";
        $params_total = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            //'job_status' => 'Completed',
            'exclude_job_type' => 1,
            'state_filter' => 'QLD',
            'custom_where' => $custom_where,
            'join_table' => array('job_type','alarm_job_type'),
            'custom_joins' => array(
                'join_table' => 'property_services as ps',
                'join_on' => 'p.`property_id` = ps.`property_id`',
                'join_type' => 'INNER'
            ),
            'group_by' => 'p.property_id',
        );
        $query =  $this->jobs_model->get_jobs($params_total);
        $total_rows = $query->num_rows();

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);





        // DAILY > incorrectly_upgraded_properties
        $page_url = '/daily/unserviced';
            
        $query =  $this->daily_model->get_unserviced_by_markers('','');
        $res = $query->result_array();
        $total_rows = count($res);

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);



     

        //next service bubble
        $page_url = '/properties/next_service';

        $ex_agency_arr = [];
        $ex_agency_filter =  null;

        $nsea_sql = $this->db->query("
        SELECT nsea.`nsea_id`, a.`agency_id`, a.`agency_name`
        FROM `next_service_exclude_agency` AS nsea
        LEFT JOIN `agency` AS a ON nsea.`agency_id` = a.`agency_id`
        WHERE nsea.`active` = 1
        ");
        foreach( $nsea_sql->result() as $nsea_row ){
            $ex_agency_arr[] = $nsea_row->agency_id;
        }

        if( count($ex_agency_arr) > 0 ){
            $ex_agency_imp = implode(",",$ex_agency_arr);
            $ex_agency_filter = "AND a.`agency_id` NOT IN({$ex_agency_imp})";
        }
        

        $once_off_date = '1521-03-16';
        $no_job_date = '1521-03-17';

        $next_30_days = date('Y-m-d',strtotime("+30 days"));
        $custom_where = "( p.`retest_date` != '' AND p.`retest_date` <= '{$next_30_days}' )
        AND a.`franchise_groups_id` != 14
        AND p.retest_date != '{$once_off_date}'
        AND (
            ( a.`allow_upfront_billing` = 1 AND p.`retest_date` = '{$no_job_date}' ) OR
            ( a.`allow_upfront_billing` = 0 AND p.`retest_date` != '{$no_job_date}' )
        )
        {$ex_agency_filter}
        ";

        $custom_joins = array(
            'join_table' => 'property_services` AS ps',
            'join_on' => '( p.`property_id` = ps.`property_id` AND ps.`service` = 1 AND ps.`alarm_job_type_id` != 6 )', // must be serviced to SATS and excluding CW
            'join_type' => 'inner'
        );

        $sel_query = "COUNT(p.`property_id`) AS pcount";
        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'custom_joins' => $custom_joins,
            'p_deleted' => 0,
            'a_status' => 'active',
            'next_services' => 1,
            'display_query' => 0
        );
        $query = $this->properties_model->get_properties($params);
        $total_rows = $query->row()->pcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);






        //view_nsw_act_job_with_tbb bubble
        $page_url = '/daily/view_nsw_act_job_with_tbb';

        $next_30_days = date('Y-m-d',strtotime("+30 days"));
        $custom_where = "j.status = 'To Be Booked' AND ( p.`retest_date` != '' AND p.`retest_date` <= '{$next_30_days}' )
        AND a.`franchise_groups_id` != 14";

        $custom_joins = array(
            'join_table' => 'property_services` AS ps',
            'join_on' => '( p.`property_id` = ps.`property_id` AND ps.`service` = 1 AND ps.`alarm_job_type_id` != 6 )', // must be serviced to SATS and excluding CW
            'join_type' => 'inner'
        );
        $custom_where_overdue_30days_tot = "( p.retest_date > NOW() AND p.retest_date < DATE_ADD(NOW(), INTERVAL 30 DAY ) )";

        //$sel_query = "COUNT(p.`property_id`) AS pcount";
        $sel_query = "p.`property_id";
        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'custom_where_arr' => array(
                $custom_where_overdue_30days_tot
            ),
            'custom_joins' => $custom_joins,
            'join_table' => array('jobs'),
            'p_deleted' => 0,
            'a_status' => 'active',
            'state_filter' => 'NSW',
            'group_by' => 'p.property_id',
            'display_query' => 0
        );
        $query = $this->properties_model->get_properties($params);
        //$total_rows = $query->row()->pcount;
        $total_rows = $query->num_rows();

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);



        

        // bubble for overdue_nsw_jobs
        $page_url = '/daily/overdue_nsw_jobs';

        $next_30_days = date('Y-m-d',strtotime("+30 days"));
        $custom_where = "j.status = 'To Be Booked' AND ( p.`retest_date` != '' AND p.`retest_date` <= '{$next_30_days}' )
        AND a.`franchise_groups_id` != 14";

        $custom_joins = array(
            'join_table' => 'property_services` AS ps',
            'join_on' => '( p.`property_id` = ps.`property_id` AND ps.`service` = 1 AND ps.`alarm_job_type_id` != 6 )', // must be serviced to SATS and excluding CW
            'join_type' => 'inner'
        );
        $custom_where_overdue_tot = "p.retest_date <= CURDATE() AND (CAST(p.postpone_due_job AS DATE) <= '".date('Y-m-d')."' OR p.postpone_due_job IS NULL)";

        //$sel_query = "COUNT(p.`property_id`) AS pcount";
        $sel_query = "p.`property_id";
        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'custom_where_arr' => array(
                $custom_where_overdue_tot
            ),
            'custom_joins' => $custom_joins,
            'join_table' => array('jobs'),
            'p_deleted' => 0,
            'a_status' => 'active',
            'state_filter' => 'NSW',
            'group_by' => 'p.property_id',
            'display_query' => 0
        );
        $query = $this->properties_model->get_properties($params);
        //$total_rows = $query->row()->pcount;
        $total_rows = $query->num_rows();

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);

        


        // Duplicate Visit
        $page_url = '/daily/duplicate_visit';

        $total_sel_query = "COUNT(j.id) as jcount";
        $total_params = array(
            'sel_query' => $total_sel_query
        );
        $query = $this->daily_model->getDuplicateVisit($total_params);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);





        // bubble for no_retest_date_property
        $page_url = '/reports/no_retest_date_property';

        $this->load->model('reports_model');

        //total rows
        $last_90_days = date('Y-m-d', strtotime(date('Y-m-d').' -90 days'));
        $custom_where = "p.retest_date IS NULL AND CAST(p.`created` AS DATE ) < '{$last_90_days}' AND `is_nlm` != 1";
        $paramstot = array(
            'sel_query' => "DISTINCT(p.property_id)",
            'custom_where' => $custom_where,
            'join_table' => array('agency_table'),
            'display_query' => 0
        );
        $querytot =  $this->reports_model->get_null_retest_date($paramstot);
        $total_rows = $querytot->num_rows();

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);


        //for view_no_active_job_properties
        $page_url = '/daily/view_no_active_job_properties';

        $this->load->model('properties_model');


        $ptotal = $this->properties_model->get_no_active_job_properties('COUNT(p.`property_id`) AS prop_count', 0)->row()->prop_count;
        $total_rows = $ptotal;

        // update page total
        $page_tot_params = array(
        'page' => $page_url,
        'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);
        //for view_no_active_job_properties end




        // Active Properties Without Jobs
        $page_url = '/daily/active_properties_without_jobs';
               
        $sql_str = "
        SELECT COUNT(p_main.`property_id`) AS p_count
        FROM `property` AS p_main
        LEFT JOIN  `property_services` AS ps_main ON p_main.`property_id` = ps_main.`property_id` 
        LEFT JOIN `agency` AS a_main ON p_main.`agency_id` = a_main.`agency_id`
        WHERE p_main.`property_id` NOT IN(
            
            SELECT DISTINCT(p.`property_id`)
            FROM `jobs` AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`	
            WHERE p.`deleted` = 0
            AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
            AND a.`status` = 'active'
            AND j.`del_job` = 0	
            AND a.`country_id` = {$country_id}

        )
        AND p_main.`deleted` = 0
        AND ( p_main.`is_nlm` = 0 OR p_main.`is_nlm` IS NULL )
        AND a_main.`status` = 'active'
        AND a_main.`country_id` = {$country_id}
        AND ps_main.`service` = 1
        ";
        $property_sql = $this->db->query($sql_str);
        $total_rows = $property_sql->row()->p_count;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);
        

        
        // Properties with empty coordinates
        $page_url = '/reports/properties_with_coordinates_errors';

        // Ben Taylor made this :)
        $coordinates_filter = null;
        if( $country_id == 1 ){ // AU
            $coordinates_filter = "
            ( p.`lat` NOT BETWEEN -43.644444 AND -10.689167 ) OR
            ( p.`lng` NOT BETWEEN 113.155 AND 153.637222 )
            ";
        }else if( $country_id == 2 ){ // NZ
            $coordinates_filter = "
            ( p.`lat` NOT BETWEEN -52.619444 AND -29.231667 ) OR
                (
                    ( p.`lng` NOT BETWEEN 165.870128 AND 180 ) AND
                    ( p.`lng` NOT BETWEEN -180 AND -175.831410 )
                )
            ";
        }
               
        // get all
        $property_sql_str = "
            SELECT COUNT(p.`property_id`) AS p_count
            FROM `property` AS p
            INNER JOIN `property_services` AS ps ON ( p.`property_id` = ps.`property_id` AND ps.`service` = 1 )
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE p.`deleted` = 0
            AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
            AND a.`status` = 'active'
            AND (
                ( p.`lat` = '' OR p.`lat` IS NULL ) OR
                ( p.`lng` = '' OR p.`lng` IS NULL ) OR
                {$coordinates_filter}                
            )                           
        ";
        $property_sql = $this->db->query($property_sql_str);
        $total_rows = $property_sql->row()->p_count;   

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);


        // Start PROPERTIES WITH MULTIPLE SERVICES
        $page_url = '/properties/properties_with_multiple_services';

        $sel_query = "ps.property_id";

        $properties_with_multiple_services =  $this->db->select($sel_query)
                      ->from('(SELECT ps.property_id, COUNT(service) AS services
                              FROM property_services ps
                              WHERE ps.service = 1
                              GROUP BY ps.property_id
                              HAVING services > 1) AS ps')
                      ->join('property AS p','p.property_id = ps.property_id')
                      ->join('(SELECT ps.property_id , GROUP_CONCAT(ps.alarm_job_type_id) AS services
                              FROM property_services ps 
                              JOIN alarm_job_type AS aj ON aj.id = ps.alarm_job_type_id
                              WHERE ps.service = 1
                              GROUP BY ps.property_id
                              HAVING (2 NOT IN (services) AND 6 NOT IN (services)) OR 
                                     (2 NOT IN (services) AND 15 NOT IN (services)) 
                              )  AS pt', 'pt.property_id = p.property_id')
                      ->join('agency AS a','a.agency_id = p.agency_id')
                      ->join('(SELECT a.agency_id, GROUP_CONCAT(a.service_id) AS services 
                                  FROM agency_services a 
                                  GROUP BY agency_id) AS asv','a.agency_id = asv.agency_id')
                      ->where('(p.is_nlm IS NULL OR p.is_nlm = 0)')
                      ->where('p.deleted', 0)
                      ->where('a.status', 'active');

        $total_rows = $properties_with_multiple_services->get()->num_rows();

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);

        // End PROPERTIES WITH MULTIPLE SERVICES


        // PMe updated tenants page
        $page_url = '/property_me/updated_tenants';

        $this->load->model('pme_model');

        $api_id = 1; // PMe

        // select query
        $sel_query = "
        SELECT      
            p.`property_id`,
            p.`address_1` AS p_address_1,
            p.`address_2` AS p_address_2,
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,

            apd_pme.`api_prop_id`,
            
            a.`agency_id`,
            a.`agency_name`,

            aht.`priority`,

            altu.`last_updated_ts`
        ";
        
        // main query body
        $main_query = "
        FROM `property` AS p
        INNER JOIN `api_property_data` AS apd_pme ON p.`property_id` = apd_pme.`crm_prop_id` AND apd_pme.`api` = {$api_id}
        LEFT JOIN `api_last_tenant_update` AS altu ON apd_pme.`api_prop_id` = altu.`api_prop_id`
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        INNER JOIN `agency_api_tokens` AS aat ON a.`agency_id` = aat.`agency_id`
        LEFT JOIN `agency_priority` AS aht ON a.`agency_id` = aht.`agency_id`
        WHERE p.`deleted` = 0
        AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
        AND a.`status` = 'active'
        AND a.`deleted` = 0
        AND (
            apd_pme.`api_prop_id` != '' AND
            apd_pme.`api_prop_id` IS NOT NULL
        )
        "; 

        // get DISTINCT agency         
        $dist_agency_sql = $this->db->query("
        SELECT DISTINCT(a.`agency_id`)
        {$main_query}
        ");
    
        $pme_agency_arr = [];
        $pme_prop_arr = [];
        foreach( $dist_agency_sql->result() as $dist_agency_row ){

            // get api properties per agency       
            $json_response = $this->pme_model->get_all_properties($dist_agency_row->agency_id);
            $json_response_dec = json_decode($json_response);

            foreach( $json_response_dec as $json_res_row ){                

                // put pme properties in an array
                $pme_prop_arr[] = $json_res_row;

            }

            // put in array of objects
            $pme_agency_arr[] = (object) [
                'agency_id' => $dist_agency_row->agency_id,
                'pme_prop' => $pme_prop_arr
            ];

        }     

        $data['pme_agency_arr'] = $pme_agency_arr;

        // main listing
        $lists = $this->db->query("
        {$sel_query}
        {$main_query}            
        ");

        $tenants_need_update_count = 0;
        if( $lists->num_rows() > 0 ){
            foreach($lists->result() as $row){

                $tenants_need_update = false;
                $crm_prop_last_updated = date('Y-m-d H:i:s',strtotime($row->last_updated_ts));

                // loop through agency
                foreach( $pme_agency_arr as $pme_agency_data ){

                    // pme property object
                    $pme_prop_arr = $pme_agency_data->pme_prop;    
                    
                    if( $pme_agency_data->agency_id == $row->agency_id ){

                        // loop through PMe properties
                        foreach( $pme_prop_arr as $pme_prop_obj ){

                            $pme_prop_last_updated = date('Y-m-d H:i:s',strtotime($pme_prop_obj->TenancyUpdatedOn));   
                                                                               
                            if( $pme_prop_obj->Id == $row->api_prop_id ){ // crm and PMe property match

                                // check if property tenants is up to date
                                $pt_sql_str = "
                                SELECT COUNT(pt.`property_tenant_id`) AS pt_count
                                FROM `property_tenants` AS pt
                                LEFT JOIN `property` AS p ON pt.`property_id` = p.`property_id`
                                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                                WHERE p.`property_id` = {$row->property_id}
                                AND a.`agency_id` = {$row->agency_id}
                                AND pt.`active` = 1
                                AND ( 
                                    pt.`modifiedDate` >= '{$pme_prop_last_updated}' OR 
                                    pt.`createdDate` >= '{$pme_prop_last_updated}'
                                )
                                ";
                                $pt_sql = $this->db->query($pt_sql_str);
                                $pt_is_up_to_date = ( $pt_sql->row()->pt_count > 0 )?true:false;

                                // last stored PMe updated date is out of date
                                if( $pme_prop_last_updated > $crm_prop_last_updated ){ 

                                    $tenants_need_update = true;

                                }                                                        
                                

                            }                                                    

                        }  
                    
                    }

                }

                
                if( $pt_is_up_to_date == false ){

                    if( $tenants_need_update == true ){ // out of date 
                        $tenants_need_update_count++;                                    
                    } 
           
                }                
                
            }

            // update page total
            $page_tot_params = array(
            'page' => $page_url,
            'total' => $tenants_need_update_count
            );
            $this->system_model->update_page_total($page_tot_params);

        }

    } //End Cron Jobs for Total Page

    //Update Main Page Total
    public function updateMainPageTotal(){
        $this->load->model('reports_model');
        $this->load->model('agency_model');
        $this->load->model('credits_model');
        $country_id = $this->config->item('country');
        $sleep_interval = 1; // by seconds

        // Cron Jobs on main.php To be booked
        $page_name = 'to-be-booked';

        $sel_query = "j.id";
        $params_total = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'job_status' => 'To Be Booked',
            'del_job' => 0,
            'country_id' => $country_id,
            'group_by' => 'j.id',
        );
        $query =  $this->jobs_model->get_jobs($params_total);
        $total_rows = $query->num_rows();

        // update page total
        $page_tot_params = array(
            'name' => $page_name,
            'total' => $total_rows
        );
        $this->system_model->update_main_page_total($page_tot_params);

        // sleep interval
        sleep($sleep_interval);

        // Cron Jobs on main.php Fix and replace
        $page_name = 'fix-or-replace';
        $cust_where = "j.`status` IN ('To Be Booked','Allocate')";
  
        $sel_query = "j.id";
        $params_total = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'job_type' => 'Fix or Replace',
            'custom_where' => $cust_where,
            'del_job' => 0,
            'country_id' => $country_id,
            'group_by' => 'j.id',
        );
        $query =  $this->jobs_model->get_jobs($params_total);
        $total_rows = $query->num_rows();
  
        // update page total
        $page_tot_params = array(
            'name' => $page_name,
            'total' => $total_rows
        );
        $this->system_model->update_main_page_total($page_tot_params);
  
        sleep($sleep_interval);

         // Cron Jobs on main.php 4 Days
        $day_loop = 1;
        for($i=1; $i <= 4; $i++){

            $booked_date_ts = strtotime("+{$day_loop} days");
            $booking_day = date('l',$booked_date_ts);

            if( $booking_day == 'Saturday' ){ // +2 days to skip Sunday
                $day_loop += 2;
            }else{
                $day_loop++;
            }
            
            $page_name = $booking_day;
    
            $sel_query = "j.id";
            $params_total = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'job_status' => 'Booked',
                'date' => date('Y-m-d',$booked_date_ts),
                'del_job' => 0,
                'country_id' => $country_id,
                'group_by' => 'j.id',
            );
            $query =  $this->jobs_model->get_jobs($params_total);
            $total_rows = $query->num_rows();
    
            // update page total
            $page_tot_params = array(
                'name' => $page_name,
                'total' => $total_rows
            );
            $this->system_model->update_main_page_total($page_tot_params);
    
            sleep($sleep_interval);
        }
        sleep($sleep_interval);

         // Cron Jobs on main.php | Total Jobs since June 2021
         $page_name = 'jobs-since-june-2021';
         $sel_query = "j.id";
         $custome_where_array_not_in = []; // clear
         $custome_where_array_not_in = "j.assigned_tech NOT IN(1,2) AND j.created >= '2021-06-21'";
         //$custom_where = "j.assigned_tech NOT IN(1,2)";
 
         $params_total = array(
             'sel_query' => $sel_query,
             'p_deleted' => 0,
             'del_job' => 0,
             'a_status' => 'active',
             'country_id' => $country_id,
             'group_by' => 'j.id',
              //Custom
              //'j_date' => ">= DATE_ADD(NOW(), INTERVAL -365 DAY)",
              //'custom_where' => $custom_where,
             'custom_where_arr' => array(
                 $custome_where_array_not_in
             ),
             'job_status' => 'Completed',
             //'is_sales' => 1,
             'display_query' => 0
         );
         $query =  $this->jobs_model->get_jobs($params_total);
         $total_rows = $query->num_rows();
     
         // update page total
         $page_tot_params = array(
             'name' => $page_name,
             'total' => $total_rows
         );
         $this->system_model->update_main_page_total($page_tot_params);
         sleep($sleep_interval);
 
 
           // Cron Jobs on main.php | 240v Rebook
         $page_name = '240v-rebook';
         $sel_query = "j.id";
         //$custome_where_array_not_in = []; // clear
         //$custome_where_array_not_in = "j.assigned_tech NOT IN(1,2) AND j.created >= '2021-06-21'";
         //$custom_where = "j.assigned_tech NOT IN(1,2)";
   
         $params_total = array(
             'sel_query' => $sel_query,
             'p_deleted' => 0,
             'del_job' => 0,
             'a_status' => 'active',
             'country_id' => $country_id,
             'group_by' => 'j.id',
             //Custom
             //'j_date' => ">= DATE_ADD(NOW(), INTERVAL -365 DAY)",
             //'custom_where' => $custom_where,
             'join_table' => array('job_markers'),
             'job_status' => 'To Be Booked',
             'updated_to_240v_rebook' => 1,
             //'is_sales' => 1,
             //'display_query' => 0
           );
           $query =  $this->jobs_model->get_jobs($params_total);
           $total_rows = $query->num_rows();
       
           // update page total
           $page_tot_params = array(
               'name' => $page_name,
               'total' => $total_rows
           );
           $this->system_model->update_main_page_total($page_tot_params);
           sleep($sleep_interval);
 
 
         // Cron Jobs on main.php | Electrician Only(EO)
         $page_name = 'electrician-only';
         $sel_query = "j.id";
         $params_total = array(
             'sel_query' => $sel_query,
             'p_deleted' => 0,
             'del_job' => 0,
             'a_status' => 'active',
             'country_id' => $country_id,
             'group_by' => 'j.id',
             //Custom
             'job_status' => 'To Be Booked',
             'is_eo' => 1
           );
           $query =  $this->jobs_model->get_jobs($params_total);
           $total_rows = $query->num_rows();
       
           // update page total
           $page_tot_params = array(
               'name' => $page_name,
               'total' => $total_rows
           );
           $this->system_model->update_main_page_total($page_tot_params);
           sleep($sleep_interval);

        // AU only
        if($country_id == 1){
            // Cron Jobs on main.php Sales to be fixed 
            $page_name = 'sales-properties-to-be-booked';

            //$custom_where = "p.is_sales = 1";
    
            $params_total = array(
                'sel_query' => 'j.id',
                'p_deleted' => 0,
                'is_sales' => 1,
                'a_status' => 'active',
                'job_status' => 'To Be Booked',
                'job_type' => 'IC Upgrade',
                //'custom_where' => $custom_where,
                'del_job' => 0,
                'country_id' => $country_id,
                'group_by' => 'j.id',
            );
            $query =  $this->jobs_model->get_jobs($params_total);
            $total_rows = $query->num_rows();
    
            // update page total
            $page_tot_params = array(
                'name' => $page_name,
                'total' => $total_rows
            );
            $this->system_model->update_main_page_total($page_tot_params);
    
            sleep($sleep_interval);


            // Cron Jobs on main.php DHA to be booked
            $page_name = 'dha-to-be-booked';
            /*$custom_where = "`a`.`franchise_groups_id`= 14 && `j`.`status`!='Completed' && `j`.`status`!='Cancelled'";
            $params_total = array(
                'sel_query' => 'j.id',
                'p_deleted' => 0,
                'del_job' => 0,
                'a_status' => 'active',
                'country_id' => $country_id,
                'group_by' => 'j.id',
                'custom_where' => $custom_where,
            );
            $query =  $this->jobs_model->get_jobs($params_total);
            $total_rows = $query->num_rows();*/

            $sel_query_dha = "COUNT(j.`id`) AS jcount";
            $params_dha = array(
                'sel_query' => $sel_query_dha,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => COUNTRY,
                'job_status' => "DHA"
            );
            $query_dha = $this->jobs_model->get_jobs($params_dha);
            $total_rows_dha = $query_dha->row()->jcount;
    
            // update page total
            $page_tot_params = array(
                'name' => $page_name,
                'total' => $total_rows_dha
            );
            $this->system_model->update_main_page_total($page_tot_params);
    
            sleep($sleep_interval);

            // Cron Jobs on main.php DHA Completed last 365 days
            $page_name = 'dha-completed-last-365-days';

            $custome_where_array = []; // clear
            $custome_where_array = "`a`.`franchise_groups_id` = 14 AND `j`.`date` >=  DATE_ADD(NOW(), INTERVAL -365 DAY )";
    
            $params_total = array(
                'sel_query' => 'j.id',
                'p_deleted' => 0,
                'del_job' => 0,
                'a_status' => 'active',
                'country_id' => $country_id,
                'group_by' => 'j.id',
                'custom_where_arr' => array(
                    $custome_where_array
                ),
                'job_status' => 'Completed',
                'display_query' => 0
            );
            $query =  $this->jobs_model->get_jobs($params_total);
            $total_rows = $query->num_rows();
    
            // update page total
            $page_tot_params = array(
                'name' => $page_name,
                'total' => $total_rows
            );
            $this->system_model->update_main_page_total($page_tot_params);
    
            sleep($sleep_interval);


            //Cron Jobs on main.php | Upgrades (Brooks)
            $page_name = 'upgrades-brooks';
            $sel_query = "p.property_id";
            $params_total = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'del_job' => 0,
                'a_status' => 'active',
                'country_id' => $country_id,
                'group_by' => 'j.id',
                //Custom
                'join_table' => array('job_type'),
                'job_status' => 'To Be Booked',
                'job_type' => 'IC Upgrade',
                'preferred_alarm_id' => 10,
            );
            $query =  $this->jobs_model->get_jobs($params_total);
            $total_rows = $query->num_rows();
        
            // update page total
            $page_tot_params = array(
                'name' => $page_name,
                'total' => $total_rows
            );
            $this->system_model->update_main_page_total($page_tot_params);
            sleep($sleep_interval);


            //Cron Jobs on main.php | Upgrades (Cavius)
            $page_name = 'upgrades-cavius';
            $sel_query = "p.property_id";
            $params_total = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'del_job' => 0,
                'a_status' => 'active',
                'country_id' => $country_id,
                'group_by' => 'j.id',
                //Custom
                'join_table' => array('job_type'),
                'job_status' => 'To Be Booked',
                'job_type' => 'IC Upgrade',
                'preferred_alarm_id' => 14,
            );
            $query =  $this->jobs_model->get_jobs($params_total);
            $total_rows = $query->num_rows();
        
            // update page total
            $page_tot_params = array(
                'name' => $page_name,
                'total' => $total_rows
            );
            $this->system_model->update_main_page_total($page_tot_params);
            sleep($sleep_interval);

            //Cron Jobs on main.php | Upgrades (Emerald)
            $page_name = 'upgrades-emerald';
            $sel_query = "p.property_id";
            $params_total = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'del_job' => 0,
                'a_status' => 'active',
                'country_id' => $country_id,
                'group_by' => 'j.id',
                //Custom
                'join_table' => array('job_type'),
                'job_status' => 'To Be Booked',
                'job_type' => 'IC Upgrade',
                'preferred_alarm_id' => 22,
            );
            $query =  $this->jobs_model->get_jobs($params_total);
            $total_rows = $query->num_rows();
        
            // update page total
            $page_tot_params = array(
                'name' => $page_name,
                'total' => $total_rows
            );
            $this->system_model->update_main_page_total($page_tot_params);
            sleep($sleep_interval);


            //Cron Jobs on main.php | NSW Overdue
            $page_name = 'nsw-overdue';
            $today = date('Y-m-d');
            $next_30_days = date('Y-m-d',strtotime("+30 days"));
                    
            $nsw_overdue = "
                    SELECT p.`property_id`
                    FROM `property` AS p
                    LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                    INNER JOIN `jobs` AS `j` ON p.`property_id` = j.`property_id`
                    INNER JOIN `property_services` AS `ps` ON ( p.`property_id` = ps.`property_id` AND ps.`service` = 1 AND ps.`alarm_job_type_id` != 6 )
                    WHERE p.`deleted` = 0
                    AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
                    AND a.`status` = 'active'
                    AND p.`state` = 'NSW'
                    AND `j`.`del_job` =0 
                    AND `j`.`status` = 'To Be Booked' 
                    AND a.`franchise_groups_id` != 14
                    AND p.`retest_date` <= CURDATE() 
                    AND (
                        p.`retest_date` != '' AND 
                        p.`retest_date` <= '{$next_30_days}' 
                    )
                    AND ( 
                        CAST(p.postpone_due_job AS DATE) <= '{$today}' OR 
                        p.`postpone_due_job` IS NULL 
                    )
                    GROUP BY p.`property_id`			
                    ";
            $query = $this->db->query($nsw_overdue);
            $total_rows = $query->num_rows();

            
                // update page total
                $page_tot_params = array(
                    'name' => $page_name,
                    'total' => $total_rows
                );
                $this->system_model->update_main_page_total($page_tot_params);
                sleep($sleep_interval);
        }
        sleep($sleep_interval);

        if( COUNTRY == 1 ){
            ##New > Upgrade Booked ----------
            $upgrade_booked_name = "upgrade-booked";
            $upgrade_booked_name_params = array(
                'sel_query' => 'COUNT(j.`id`) AS jcount',
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => COUNTRY,
                'job_status' => 'Booked',
                //'join_table' => array('job_type','alarm_job_type','staff_accounts'),
                'job_type' => 'IC Upgrade'
            );
            $query = $this->jobs_model->get_jobs($upgrade_booked_name_params);
            $total_rows = $query->row()->jcount;

            // update page total
            $page_tot_params = array(
                'name' => $upgrade_booked_name,
                'total' => $total_rows
            );
            $this->system_model->update_main_page_total($page_tot_params);
            ##New > Upgrade Booked End ----------

            sleep($sleep_interval);

            ##New > Uprade Completed ----------
            $first_day_of_month = $this->system_model->formatDate(date('01/m/Y'));
            $last_day_of_month = $this->system_model->formatDate(date('t/m/Y'));
            $custom_where_upgrade_completed = " al.`new` = 1 AND al.`ts_discarded` = 0 AND Date(j.`date`)  BETWEEN '{$first_day_of_month}' AND '{$last_day_of_month}'";
            $upgrade_completed_sel_query = "SUM(al.`alarm_price`) AS cost_of_alarms";
            $upgrade_completed_params = array(
                'sel_query' => $upgrade_completed_sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => COUNTRY,
                'job_status' => 'Completed',
                'join_table' => array('job_type','alarm_job_type'),

                'custom_joins_arr' => array(

                    array(
                        'join_table' => 'alarm as al',
                        'join_on' => 'j.id = al.job_id',
                        'join_type' => 'inner'
                    ),
                    array(
                        'join_table' => 'alarm_pwr AS al_p',
                        'join_on' => 'al.`alarm_power_id` = al_p.alarm_pwr_id',
                        'join_type' => 'left'
                    )

                ),
                
                'job_type' => 'IC Upgrade',
                'custom_where'=> $custom_where_upgrade_completed,
                'group_by' => 'j.id',
                'display_query' => 0
            );
            $job_tot_sql = $this->jobs_model->get_jobs($upgrade_completed_params);
            $total_rows = $job_tot_sql->num_rows();

            // update page total
            $page_tot_params = array(
                'name' => 'upgrade-completed',
                'total' => $total_rows
            );
            $this->system_model->update_main_page_total($page_tot_params);
            ##New > Uprade Completed End ----------
            sleep($sleep_interval);

            ##New > Uprade To Be Booked ----------
            $join_table_array = array('job_type','alarm_job_type','staff_accounts');
            $params_upgrade_tbb = array(
                'sel_query' => 'j.id',
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'job_status' => 'To Be Booked',
                'join_table' => $join_table_array,
                
                'job_type' => 'IC Upgrade',
                'country_id' => COUNTRY
            );

            $query_upgrade_tbb = $this->jobs_model->get_jobs($params_upgrade_tbb);
            $total_rows = $query_upgrade_tbb->num_rows();

            // update page total
            $page_tot_params = array(
                'name' => 'upgrade-to-be-booked',
                'total' => $total_rows
            );
            $this->system_model->update_main_page_total($page_tot_params);
            ##New > Uprade To Be Booked END ----------
            sleep($sleep_interval);

            ##Agency Audits - Not Completed ----------
            $total_rows_params = array(
                'sel_query' =>"COUNT(ad.agency_audit_id) as count",
                'active' => 1,
                'status' => 1 ##Pending
            );
            $tot_row_query = $this->agency_model->getAgencyAudits($total_rows_params);
            $agency_audits_total_rows = $tot_row_query->row()->count;

            // update page total
            $page_tot_params = array(
                'name' => 'agency-audits-not-completed',
                'total' => $agency_audits_total_rows
            );
            $this->system_model->update_main_page_total($page_tot_params);
            ##Agency Audits - Not Completed end ----------
            sleep($sleep_interval);
        }

        //dirty address
        $dirty_address_total_params = array(
            'sel_query' => "COUNT('property_id') as p_count"
        );
        $query = $this->reports_model->get_dirty_address($dirty_address_total_params);
        $dirty_address_total_rows = $query->row()->p_count;

        // update page total
        $page_tot_params_dirty_address = array(
            'name' => 'dirty-address',
            'total' => $dirty_address_total_rows
        );
        $this->system_model->update_main_page_total($page_tot_params_dirty_address);
        //dirty address end

        sleep($sleep_interval);

        //Multiple Jobs
        $custom_where_multiple_jobs = "a.`allow_upfront_billing` = 0";
        $getMultipleJobs_total_params = array(
            'sel_query' => "COUNT(j.id) as jcount",
            'custom_where' => $custom_where_multiple_jobs
        );
        $query = $this->daily_model->getMultipleJobs($getMultipleJobs_total_params);
        $getMultipleJobs_total_rows = $query->num_rows();

        #update page total
        $page_tot_params_getMultipleJobs = array(
            'name' => 'multiple-jobs',
            'total' => $getMultipleJobs_total_rows
        );
        $this->system_model->update_main_page_total($page_tot_params_getMultipleJobs);
        //Multiple Jobs end

        sleep($sleep_interval);

        //Duplicate Visit
        $getDuplicateVisit_total_params = array(
            'sel_query' => "COUNT(j.id) as jcount"
        );
        $query = $this->daily_model->getDuplicateVisit($getDuplicateVisit_total_params);
        $getDuplicateVisit_total_rows = $query->row()->jcount;

         #update page total
         $page_tot_params_getDuplicateVisit = array(
            'name' => 'duplicate-visit',
            'total' => $getDuplicateVisit_total_rows
        );
        $this->system_model->update_main_page_total($page_tot_params_getDuplicateVisit);
        //Duplicate Visit end

        sleep($sleep_interval);

        //Coordinate Errors
        // Ben Taylor made this :)
        $coordinates_filter = null;
        if( COUNTRY == 1 ){ // AU
            $coordinates_filter = "
            ( p.`lat` NOT BETWEEN -43.644444 AND -10.689167 ) OR
            ( p.`lng` NOT BETWEEN 113.155 AND 153.637222 )
            ";
        }else if( COUNTRY == 2 ){ // NZ
            $coordinates_filter = "
            ( p.`lat` NOT BETWEEN -52.619444 AND -29.231667 ) OR
                (
                    ( p.`lng` NOT BETWEEN 165.870128 AND 180 ) AND
                    ( p.`lng` NOT BETWEEN -180 AND -175.831410 )
                )
            ";
        }

        $property_sql_str = "
        SELECT COUNT(p.`property_id`) AS p_count
        FROM `property` AS p
        INNER JOIN `property_services` AS ps ON ( p.`property_id` = ps.`property_id` AND ps.`service` = 1 )
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        WHERE p.`deleted` = 0
        AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
        AND a.`status` = 'active'
        AND (
            ( p.`lat` = '' OR p.`lat` IS NULL ) OR
            ( p.`lng` = '' OR p.`lng` IS NULL ) OR
            {$coordinates_filter}                
        )                     
        ";
        $property_sql = $this->db->query($property_sql_str);
        $coordinate_error_total_rows = $property_sql->row()->p_count;  

        #update page total
        $page_tot_params_coordinate_error = array(
            'name' => 'coordinate-errors',
            'total' => $coordinate_error_total_rows
        );
        $this->system_model->update_main_page_total($page_tot_params_coordinate_error);
        //Coordinate Errors end

        sleep($sleep_interval);

        //Active Unsold Properties
        $params_unsold = array(
            'sel_query' => "COUNT(ps.`property_services_id`) AS jcount",
            'custom_where' => "agen_serv.`agency_services_id` IS NULL",
            'ps_service' => 1,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => COUNTRY,

            'join_table' => array('alarm_job_type'),

            'custom_joins' => array(
                'join_table' => '`agency_services` AS agen_serv',
                'join_on' => '( agen_serv.`agency_id` = a.`agency_id` AND agen_serv.`service_id` = ps.`alarm_job_type_id` )',
                'join_type' => 'left'
            )
        );
        $query_unsold = $this->properties_model->getPropertyServices($params_unsold);
        $total_rows_unsold = $query_unsold->row()->jcount;

         #update page total
         $page_tot_params_unsold = array(
            'name' => 'unsold-services',
            'total' => $total_rows_unsold
        );
        $this->system_model->update_main_page_total($page_tot_params_unsold);
        //Active Unsold Properties end

        sleep($sleep_interval);

        //No Job Type
        $sql_str_no_job_type = "
        SELECT COUNT(j.`id`) AS jcount
        FROM `jobs` AS j 
        LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
        LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        WHERE (
            j.`job_type` = '' OR
            j.`job_type` IS NULL OR
            j.`job_type` = 'None Selected'
        )
        ";        
        $query_no_job_type = $this->db->query($sql_str_no_job_type);
        $total_rows_no_job_type = $query_no_job_type->row()->jcount;

        #update page total
        $page_tot_params_no_job_typ = array(
            'name' => 'no-job-type',
            'total' => $total_rows_no_job_type
        );
        $this->system_model->update_main_page_total($page_tot_params_no_job_typ);
        //No Job Type END

        sleep($sleep_interval);

        //No Job Status
        $sql_str_no_job_status = "
        SELECT COUNT(j.`id`) AS jcount
        FROM `jobs` AS j 
        LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
        LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        WHERE (
            j.`status` = '' OR
            j.`status` IS NULL
        )
        ";        
        $query_no_job_status = $this->db->query($sql_str_no_job_status);
        $total_rows_no_job_status = $query_no_job_status->row()->jcount;

        #update page total
        $page_tot_params_no_job_status = array(
            'name' => 'no-job-status',
            'total' => $total_rows_no_job_status
        );
        $this->system_model->update_main_page_total($page_tot_params_no_job_status);
        //No Job Status End

        sleep($sleep_interval);

        //No Restest Date
        $last_90_days_no_retest_date = date('Y-m-d', strtotime(date('Y-m-d').' -90 days'));
        $paramstot_no_retest_date = array(
            'sel_query' => "DISTINCT(p.property_id)",
            'custom_where' => "p.retest_date IS NULL AND CAST(p.`created` AS DATE ) < '{$last_90_days_no_retest_date}' AND `is_nlm` != 1",
            'join_table' => array('agency_table'),
            'display_query' => 0
        );
        $querytot_no_retest_date =  $this->reports_model->get_null_retest_date($paramstot_no_retest_date);
        $total_rows_no_retest_date = $querytot_no_retest_date->num_rows();

         #update page total
         $page_tot_params_no_retest_date = array(
            'name' => 'no-retest-date',
            'total' => $total_rows_no_retest_date
        );
        $this->system_model->update_main_page_total($page_tot_params_no_retest_date);
        //No Restest Date End

        sleep($sleep_interval);

        //Data Descripancy
        if( COUNTRY ==1 ){ ## AU only

            $custom_where_des = "(ps.`service` = 1 AND (ps.`alarm_job_type_id` != 12 AND ps.`alarm_job_type_id` != 13 AND ps.`alarm_job_type_id` != 14 AND ps.`alarm_job_type_id` != 11 AND ps.`alarm_job_type_id` != 6 AND ps.`alarm_job_type_id` != 20)) 
            AND ((j.job_type = 'IC Upgrade' AND j.status = 'Completed') OR p.prop_upgraded_to_ic_sa = 1 )
            AND p.is_sales!=1 AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )";

            $params_total_descripancy = array(
                'sel_query' => "p.property_id",
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => COUNTRY,
                'exclude_job_type' => 1,
                'state_filter' => 'QLD',
                'custom_where' => $custom_where_des,
                'join_table' => array('job_type','alarm_job_type'),
                'custom_joins' => array(
                    'join_table' => 'property_services as ps',
                    'join_on' => 'p.`property_id` = ps.`property_id`',
                    'join_type' => 'INNER'
                ),
                'group_by' => 'p.property_id',
            );
            $query_descripancy =  $this->jobs_model->get_jobs($params_total_descripancy);
            $total_rows_descripancy = $query_descripancy->num_rows();

             #update page total
            $page_tot_params_descripancy = array(
                'name' => 'data-discrepancy',
                'total' => $total_rows_descripancy
            );
            $this->system_model->update_main_page_total($page_tot_params_descripancy);

            sleep($sleep_interval);

        }
        //Data Descripancy End

        //Unserviced Properties
        $query_unserviced = $this->daily_model->get_unserviced_by_markers('','');
        $total_rows_unserviced = $query_unserviced->num_rows();

         #update page total
         $page_tot_params_unserviced = array(
            'name' => 'unserviced-properties',
            'total' => $total_rows_unserviced
        );
        $this->system_model->update_main_page_total($page_tot_params_unserviced);
        //Unserviced Properties end

        sleep($sleep_interval);

        //Multiple Service
        $params_tt['total_rows'] = true;
        $total_rows_multiple_service = $this->properties_model->get_properties_with_multiple_services($params_tt);

         #update page total
         $page_tot_params_descripancy = array(
            'name' => 'multiple-service',
            'total' => $total_rows_multiple_service
        );
        $this->system_model->update_main_page_total($page_tot_params_descripancy);
        //Multiple Service End

        sleep($sleep_interval);

        //No Active job properties
        if (COUNTRY == 1) {
            $ptotal_no_active_prop = $this->properties_model->get_no_active_job_properties('COUNT(p.`property_id`) AS prop_count', null)->row()->prop_count;
        } else {
            $ptotal_no_active_prop = $this->properties_model->get_no_active_job_properties('p.`property_id`', null)->num_rows();
        }
        $total_rows_no_active_prop = empty($ptotal_no_active_prop) ? 0 : $ptotal_no_active_prop;

        #update page total
        $page_tot_params_unserviced = array(
            'name' => 'no-active-job',
            'total' => $total_rows_no_active_prop
        );
        $this->system_model->update_main_page_total($page_tot_params_unserviced);
        //No Active job properties end

        sleep($sleep_interval);

        //booked jobs
        $params_booked_jobs = array(
            'sel_query' => "COUNT(j.`id`) AS jcount",
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => COUNTRY,
            'job_status' => "Booked",
            'join_table' => array('job_type','alarm_job_type','staff_accounts')
        );
        $query_booked_jobs = $this->jobs_model->get_jobs($params_booked_jobs);
        $total_rows_booked_jobs = $query_booked_jobs->row()->jcount;

         #update page total
         $page_tot_params_booked_jobs = array(
            'name' => 'booked-jobs',
            'total' => $total_rows_booked_jobs
        );
        $this->system_model->update_main_page_total($page_tot_params_booked_jobs);
        //booked jobs end

        sleep($sleep_interval);

        //DHA to be invoiced
        $params_dha_tbb = array(
            'sel_query' => 'j.id',
            'maintenance_id' => 14,
            'group_by' => 'j.id',
            'display_echo' => 0
        );
        $query_dha_tbb = $this->jobs_model->new_platform_invoicing($params_dha_tbb);
        $total_rows_dha_tbb = $query_dha_tbb->num_rows();
        #update page total
        $page_tot_params_dha_tbb = array(
            'name' => 'dha-to-be-invoiced',
            'total' => $total_rows_dha_tbb
        );
        $this->system_model->update_main_page_total($page_tot_params_dha_tbb);
        //DHA to be invoiced end
        
        sleep($sleep_interval);

        //Platform invoicing
        $params_platform_invocing = array(
            'sel_query' => 'j.id',
            'group_by' => 'j.id',
            'display_echo' => 0
        );
        $query_platform_invocing = $this->jobs_model->new_platform_invoicing($params_platform_invocing);
        $total_platform_invocing = $query_platform_invocing->num_rows();
        #update page total
        $page_tot_params_platform_invocingb = array(
            'name' => 'platform-invoicing',
            'total' => $total_platform_invocing
        );
        $this->system_model->update_main_page_total($page_tot_params_platform_invocingb);
        //Platform invoicing end

        sleep($sleep_interval);
        
        //Incoming SMS
        $t_params_incoming_sms = array(
            'sms_page' => 'incoming',
            'custom_select' => "COUNT(*) as total_rows",
            'unread' => 1
        );
        $total_rows_incoming_sms = $this->sms_model->getSMSrepliesMergedData($t_params_incoming_sms)->row()->total_rows;
        #update page total
        $page_tot_params_incoming_sms = array(
            'name' => 'incoming-sms',
            'total' => $total_rows_incoming_sms
        );
        $this->system_model->update_main_page_total($page_tot_params_incoming_sms);
        //Incoming SMS end

        sleep($sleep_interval);

        //Credit Request
        $custom_where_main_list_credit_req = "(cr.`adjustment_type`!=1 OR cr.`adjustment_type` IS NULL)";
        $sel_query_credit_req = "COUNT(cr.`credit_request_id`) as cr_count";
        $params_credit_req = array(
            'sel_query' => $sel_query_credit_req,
            'result'=> 'pending',
            'deleted' => 0,
            'active' => 1,
            'custom_where' => $custom_where_main_list_credit_req,
            'join_table' => array('jobs','property','agency','req_by','who'),       
            'country_id' => COUNTRY
        );
        $query_credit_req = $this->credits_model->get_credit_request($params_credit_req);
        $total_rows_credit_req = $query_credit_req->row()->cr_count; 
        #update page total
        $page_tot_params_credit_req = array(
            'name' => 'credit-request',
            'total' => $total_rows_credit_req
        );
        $this->system_model->update_main_page_total($page_tot_params_credit_req);
        //Credit Request end

        sleep($sleep_interval);

        //Refund Request
        $custom_where_main_list_refund_req = "adjustment_type=1";
        $sel_query_refund_req = "COUNT(cr.`credit_request_id`) as cr_count";
        $params_refund_req = array(
            'sel_query' => $sel_query_refund_req,
            'deleted' => 0,
            'active' => 1,
            'custom_where' => $custom_where_main_list_refund_req,
            'result' => 'pending',
            'join_table' => array('jobs','property','agency','req_by','who'),       
            'country_id' => COUNTRY
        );
        $query_refund_req = $this->credits_model->get_credit_request($params_refund_req);
        $total_rows_refund_req = $query_refund_req->row()->cr_count;  
        #update page total
        $page_tot_params_refund_req = array(
            'name' => 'refund-request',
            'total' => $total_rows_refund_req
        );
        $this->system_model->update_main_page_total($page_tot_params_refund_req);
        //Refund Request end

        sleep($sleep_interval);

        //to-be-invoice
        $sel_query_tbb = "COUNT(j.`id`) AS jcount";
        $params_tbb = array(
            'sel_query' => $sel_query_tbb,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => COUNTRY,
            'job_status' => 'To Be Invoiced',
            'join_table' => array('property','agency','alarm_job_type','job_reason','staff_accounts'),
            'custom_where'=> NULL
        );
        $query_tbb = $this->jobs_model->get_jobs($params_tbb);
        $total_rows_tbb = $query_tbb->row()->jcount;
        #update page total
        $page_tot_params_tbb = array(
            'name' => 'to-be-invoiced',
            'total' => $total_rows_tbb
        );
        $this->system_model->update_main_page_total($page_tot_params_tbb);
        //to-be-invoice end

        sleep($sleep_interval);

        //New Jobs
        $send_letter_count = 0;
        $sel_query_newjobs = "j.`id` AS jid, j.`comments` AS j_comments,  p.`comments` AS p_comments";
        $params_newjobs = array(
            'sel_query' => $sel_query_newjobs,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => COUNTRY,
            'job_status' => 'Send Letters',
            'display_query' => 0
        );
        $query_newjobs = $this->jobs_model->get_jobs($params_newjobs);

        foreach( $query_newjobs->result() as $job_row ){

            // job or property comments
            if( $job_row->j_comments != "" || $job_row->p_comments != "" ){                
                $send_letter_count++;
            }

        }
        $total_rows_newjobs = $send_letter_count;
        #update page total
        $page_tot_params_newjobs = array(
            'name' => 'new-jobs',
            'total' => $total_rows_newjobs
        );
        $this->system_model->update_main_page_total($page_tot_params_newjobs);
        //New Jobs end

        sleep($sleep_interval);

        //Office to call
        $custom_where_office_to_call = "p.`bne_to_call` = 1 AND j.status NOT IN('Completed','Cancelled','Merged Certificates','Booked','Pre Completion','Pending','To Be Invoiced')";
        $sel_query_office_to_call = "COUNT(j.`id`) AS jcount";
        $params_office_to_call = array(
            'sel_query' => $sel_query_office_to_call,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'custom_where' => $custom_where_office_to_call,
            'join_table' => array('job_type','alarm_job_type'),
            'otc_status' => 1,
        );
        $query_office_to_call = $this->jobs_model->get_jobs($params_office_to_call);
        $total_rows_office_to_call = $query_office_to_call->row()->jcount;
        #update page total
        $page_tot_params_newjobs = array(
            'name' => 'office-to-call',
            'total' => $total_rows_office_to_call
        );
        $this->system_model->update_main_page_total($page_tot_params_newjobs);
        //Office to call end

        sleep($sleep_interval);

        //To Be Allocate
        $sel_query_allocate = "COUNT(j.`id`) AS jcount";
        $params_allocate = array(
            'sel_query' => $sel_query_allocate,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => COUNTRY,
            'job_status' => 'Allocate',
            'join_table' => array('job_type','alarm_job_type','allocated_by_join')
        );
        $query_allocate = $this->jobs_model->get_jobs($params_allocate);
        $total_rows_allocate = $query_allocate->row()->jcount;
        #update page total
        $page_tot_params_allocate = array(
            'name' => 'to-be-allocated',
            'total' => $total_rows_allocate
        );
        $this->system_model->update_main_page_total($page_tot_params_allocate);
        //To Be Allocate end

        sleep($sleep_interval);

        //missing region start
        $sel_query_missing_region = "
        p.`property_id`, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3, 
        p.`state` AS p_state,
        p.`postcode` AS p_postcode, 
        
        a.`agency_id`, 
        a.`agency_name` 
        ";

        // paginated
        $params_missing_r = array(
            'sel_query' => $sel_query_missing_region,			

            'p_deleted' => 0,
            'a_status' => 'active',					

            'sort_list' => array(
                array(
                    'order_by' => 'p.`address_2`',
                    'sort' => 'ASC',
                ),
                array(
                    'order_by' => 'p.`address_1`',
                    'sort' => 'ASC',
                ),
            ),
            'display_query' => 0
        );
        $lists = $this->properties_model->get_properties($params_missing_r);

        $total_rows = 0;
        $count = 0;
        if( $lists->num_rows() > 0 ){
            foreach($lists->result_array() as $row){

                // check if postcode exist on region postcode list
                if( $row['p_postcode'] != '' ){

                    $check_postcode = $this->daily_model->check_postcode_exist_on_list($row['p_postcode']);

                    // it didnt find postcode on region postcode list
                    if( $check_postcode == false ){ 
                        $count++;
                    }
                }
            }
        }
        $total_rows_missing_r = $count;
        $page_tot_params_missing_r= array(
            'name' => 'missing-region',
            'total' => $total_rows_missing_r
        );
        $this->system_model->update_main_page_total($page_tot_params_missing_r);
        //missing region end

        sleep($sleep_interval);

        //duplicate proeprties start
        $params_total_dup_prop = array(
            'sel_query' => "COUNT( * ) AS jcount"
        );
        $query_dup_prop = $this->properties_model->jFindDupProp($params_total_dup_prop);
        $total_rows_dup_prop = $query_dup_prop->num_rows();

        $page_tot_params_escalate = array(
            'name' => 'duplicate-properties',
            'total' => $total_rows_dup_prop
        );
        $this->system_model->update_main_page_total($page_tot_params_escalate);
        //duplicate proeprties end

        sleep($sleep_interval);

        //escalate start
        $tt_count_q_params = array(
            'sel_query' => "COUNT(j.id) as j_count",
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'job_status' => "Escalate",
            'country_id' => COUNTRY
        );
        $tt_count_q = $this->jobs_model->get_jobs($tt_count_q_params)->row();

        $page_tot_params_escalate = array(
            'name' => 'escalate',
            'total' => $tt_count_q->j_count
        );
        $this->system_model->update_main_page_total($page_tot_params_escalate);
        //escalate end

        sleep($sleep_interval);

        //Action Required start
        $sel_query_action_req = "COUNT(j.`id`) AS jcount";
        $params_action_req = array(
            'sel_query' => $sel_query_action_req,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $this->config->item('country'),
            'job_status' => 'Action Required',
        );
        $query_action_req =  $this->jobs_model->get_jobs($params_action_req);
        $total_rows_action_req = $query_action_req->row()->jcount;

        $page_tot_params_action_req = array(
            'name' => 'action-required',
            'total' => $total_rows_action_req
        );
        $this->system_model->update_main_page_total($page_tot_params_action_req);
        //Action Required end

        sleep($sleep_interval);

        //Properties need Verification start
         // exclude already NLM properties
         $custom_where_pnv = "( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )";
         // total row
         $sel_query_pnv = "COUNT(pnv.`pnv_id`) AS pnv_count";
         $params_pnv = array(
             'sel_query' => $sel_query_pnv,                                                                
             'active' => 1,
             'ignore_issue' => 0,
             'custom_where' => $custom_where_pnv
         );
         $tot_row_sql_pnv = $this->properties_model->get_properties_needs_verification($params_pnv);
         $total_rows_pnv = $tot_row_sql_pnv->row()->pnv_count;

         $page_tot_params_pnv = array(
            'name' => 'properties-need-verification',
            'total' => $total_rows_pnv
        );
        $this->system_model->update_main_page_total($page_tot_params_pnv);
        //Properties need Verification start end

        sleep($sleep_interval);

        //Sales Upgrades To be Booked start
        $join_table_array_sales_upgrade = array('job_type','alarm_job_type','staff_accounts');
        $sel_query_sales_upgrade = "j.`id`";
        $params_sales_upgrade = array(
            'sel_query' => $sel_query_sales_upgrade,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'job_status' => 'To Be Booked',
            'join_table' => $join_table_array_sales_upgrade,
            'country_id' => COUNTRY,
            'is_sales' => 1

        );
        $query_sales_upgrade = $this->jobs_model->get_jobs($params_sales_upgrade);
         $total_rows_sales_upgrade = $query_sales_upgrade->num_rows();

         $page_tot_params_sales_upgrade = array(
            'name' => 'sales-upgrade-to-be-booked',
            'total' => $total_rows_sales_upgrade
        );
        $this->system_model->update_main_page_total($page_tot_params_sales_upgrade);
        //Sales Upgrades To be Booked end

        sleep($sleep_interval);

        //Ready to map
        $run_map_q = $this->db->query("
            SELECT COUNT('tech_run_id') as run_id_count 
            FROM `tech_run` 
            WHERE ready_to_map = 1 
            AND ( run_complete!=1 OR run_complete IS NULL )
        ");
        $total_rows_run_map = $run_map_q->row()->run_id_count;
        #update page total
        $page_tot_params_run_map = array(
            'name' => 'ready-to-be-mapped',
            'total' => $total_rows_run_map
        );
        $this->system_model->update_main_page_total($page_tot_params_run_map);
        //Ready to map end

        sleep($sleep_interval);

        //Call over complete
        $call_over_q = $this->db->query("
            SELECT COUNT('tech_run_id') as run_id_count 
            FROM `tech_run` 
            WHERE (first_call_over_done = 1 OR finished_booking = 1 OR additional_call_over_done = 1)
            AND date >= CURDATE()
            AND ready_to_map != 1
        ");
        $total_rows_call_over = $call_over_q->row()->run_id_count;
        #update page total
        $page_tot_params_call_over = array(
            'name' => 'call-over-complete',
            'total' => $total_rows_call_over
        );
        $this->system_model->update_main_page_total($page_tot_params_call_over);
        //Call over complete end

        sleep($sleep_interval);

        
        //Missing regions
        ##custom query by Gherx > to simplyfy query rathen than using query from missing_region page
        //disabled use system_model->get_page_total instead
       /* $mr_sql = "
            SELECT COUNT(property_id) as p_count
            FROM `property` AS `p`
            LEFT JOIN `agency` AS `a` ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `agency_user_accounts` AS `aua` ON p.`pm_id_new` = aua.`agency_user_account_id`
            WHERE `p`.`deleted` = 0
            AND `a`.`status` = 'active'
            AND p.postcode NOT IN(
                SELECT pc.postcode
                FROM postcode AS pc
                LEFT JOIN `sub_regions` AS `sr` ON sr.sub_region_id = pc.sub_region_id
                WHERE pc.postcode = p.postcode
            )
            AND p.postcode != ''
            AND (p.is_nlm = 0 OR p.is_nlm IS NULL)";
        $mr_q = $this->db->query($mr_sql)->row();
        $total_rows_missing_region = $mr_q->p_count;
        #update page total
        $page_tot_params_missing_region = array(
            'name' => 'missing-region',
            'total' => $total_rows_missing_region
        );
        $this->system_model->update_main_page_total($page_tot_params_missing_region);*/
        //Missing regionsend
        
        
        // Active Properties Without Jobs >>>> START
        $result = $this->daily_model->get_active_properties_without_jobs_count($this->config->item('country'));
        #update page total
        $page_tot_params_call_over = array(
            'name' => 'active-properties-without-jobs',
            'total' => $result
        );
        $this->system_model->update_main_page_total($page_tot_params_call_over);

        // Active Properties Without Jobs >>>> END

        $this->system_model->update_property_jobs_count();
    }


    public function pme_find_unmatched_properties(){

        $this->load->model('pme_model');        

        $today = date('Y-m-d H:i:s');

        $agency_sql_str = "
            SELECT 
                a.`agency_id`,
                a.`agency_name`,
                
                agen_tok.`agency_api_token_id`,
                agen_tok.`connection_date`,
                agen_tok.`access_token`
            FROM `agency` AS a 
            LEFT JOIN `agency_api_integration` AS agen_api ON (a.`agency_id` = agen_api.`agency_id` AND agen_api.`connected_service` = 1 )
            LEFT JOIN `agency_api_tokens` AS agen_tok ON ( a.`agency_id` = agen_tok.`agency_id` AND agen_tok.`api_id` = 1 )
            WHERE a.`status` = 'active'    
            AND agen_api.`connected_service` = 1   
            AND agen_api.`active` = 1       
            AND a.deleted = 0
        ";
 
        $agency_sql = $this->db->query($agency_sql_str);
        foreach( $agency_sql->result() as $agency_row ){

            $agency_id = $agency_row->agency_id;
            $pmeType = 1;

            if( $agency_id > 0 ){

                echo "<h1>{$agency_row->agency_name}($agency_id)</h1>";

                // get CRM properties
               /* disabled and update below to join new generic table apd
               $property_sql_str = "
                SELECT 
                    `address_1` AS p_street_num,
                    `address_2` AS p_street_name,
                    `address_3` AS p_suburb,
                    `state` AS p_state,
                    `postcode` AS p_postcode,
                    `propertyme_prop_id`
                FROM `property`
                WHERE `agency_id` = {$agency_id}      
                AND ( `propertyme_prop_id` IS NOT NULL AND `propertyme_prop_id` != '' )                       
                ";*/

                $property_sql_str = "
                SELECT 
                    p.`address_1` AS p_street_num,
                    p.`address_2` AS p_street_name,
                    p.`address_3` AS p_suburb,
                    p.`state` AS p_state,
                    p.`postcode` AS p_postcode,
                    apd.api_prop_id AS propertyme_prop_id
                FROM `property` AS p
                LEFT JOIN `api_property_data` AS apd ON ( p.property_id = apd.crm_prop_id AND apd.api = {$pmeType} )
                WHERE p.`agency_id` = {$agency_id}    
                AND ( apd.`api_prop_id` IS NOT NULL AND apd.`api_prop_id` != '' AND apd.api = {$pmeType} )                     
                ";

                $property_sql = $this->db->query($property_sql_str);
                $crm_prop = $property_sql->result();

                
                // get PMe properties
                $response = $this->pme_model->get_all_properties($agency_id);	
                $json_dec = json_decode($response);
                //print_r($json_dec);

                // PNV
                // exclude already NLM properties and do not show already linked properties
                $pnv_sql_str = "
                SELECT 
                    pnv.`pnv_id`, 
                    pnv.`property_source`, 
                    pnv.`property_id`, 
                    pnv.`agency_id`, 
                    pnv.`note`, 
                    pnv.`agency_verified`,
                    pnv.`property_address`,

                    a.`agency_id`, 
                    a.`agency_name`
                FROM `properties_needs_verification` AS `pnv` 
                INNER JOIN `agency` AS `a` ON pnv.`agency_id` = a.`agency_id`
                WHERE pnv.`active` = 1
                AND a.`agency_id` = {$agency_id}
                AND pnv.`property_source` = 2
                AND pnv.`ignore_issue` = 0
                ";
                $pnv_sql = $this->db->query($pnv_sql_str);
                $pnv_row_res = $pnv_sql->result();
                //print_r($pnv_row_res);


                // hidden properties
                $hap_sql_str = "
                SELECT id AS hap_id, `api_prop_id`
                FROM `hidden_api_property`
                WHERE`agency_id` = {$agency_id}
                ";
                $hap_sql = $this->db->query($hap_sql_str);
                $hap_res = $hap_sql->result();


                // CRM Table
                $table_html = "
                <div style='float: left;'>
                <h2>CRM Properties</h2>
                <table style='border:1px solid; border:1px solid; border-collapse: collapse; margin-right: 50px;'>
                <tr>
                    <th style='text-align:left; border:1px solid;'>#</th>
                    <th style='text-align:left; border:1px solid;'>Address</th>
                    <th style='text-align:left; border:1px solid;'>Stored PMe Prop ID</th>
                </tr>
                ";
                foreach( $crm_prop as $index => $prop_row ){

                    $p_address = "{$prop_row->p_street_num} {$prop_row->p_street_name} {$prop_row->p_suburb} {$prop_row->p_state} {$prop_row->p_postcode}";

                    $table_html .= "<tr>
                        <td style='text-align:left; border:1px solid;'>".( $index+1 ).".)</td>
                        <td style='text-align:left; border:1px solid;'>{$p_address}</td>
                        <td style='text-align:left; border:1px solid;'>{$prop_row->propertyme_prop_id}</td>
                    </tr>";            

                }
                $table_html .= "</table>
                </div>
                ";

                echo $table_html;

                
                // PMe Table
                $table_html = "
                <div style='float: left;'>
                <h2>PMe Properties</h2>
                <table style='border:1px solid; border:1px solid; border-collapse: collapse;'>
                <tr>
                    <th style='text-align:left; border:1px solid;'>Count</th>
                    <th style='text-align:left; border:1px solid;'>Address</th>                    
                    <th style='text-align:left; border:1px solid;'>CRM property Connected to</th>
                    <th style='text-align:left; border:1px solid;'>PMe Prop ID</th>
                    <th style='text-align:left; border:1px solid;'>Has notes on PNV</th>     
                    <th style='text-align:left; border:1px solid;'>Property Sales?</th>
                    <th style='text-align:left; border:1px solid;'>Is Hidden?</th>               
                </tr>
                ";
                // pme
                $pnv_need_process_count = 0;
                foreach ( $json_dec as $key => $pme_row ){

                    $has_connected = false;
                    $pme_prop_id = null;
                    $crm_connected_prop = null;
                    $has_pnv_notes = false;
                    $pme_prop_with_notes = null;
                    $highlight_red = false;
                    $is_sales_prop = false;
                    $is_prop_hidden = false;
                
                    // pme             
                    $pme_address = $pme_row->AddressText;
                    
                    // crm        
                    foreach( $crm_prop as $index => $crm_row ){   
                                               

                        $crm_address = "{$prop_row->p_street_num} {$prop_row->p_street_name} {$prop_row->p_suburb} {$prop_row->p_state} {$prop_row->p_postcode}";             
                        
                        if( $pme_row->Id == $crm_row->propertyme_prop_id ){
                            $has_connected = true;
                            //$pme_prop_id = $pme_row->Id;
                            $crm_connected_prop = $crm_address;
                        }
                      

                    }

                    // PNV        
                    foreach( $pnv_row_res as $index => $pnv_row ){   
                                               

                        $pnv_prop_add = $pnv_row->property_address;
                        
                        if( $pme_row->Id == $pnv_row->property_id ){
                            $has_pnv_notes = true;                            
                            $pme_prop_with_notes = $pnv_prop_add;
                        }
                      

                    }

                    // hidden        
                    foreach( $hap_res as $index => $hap_row ){   
                                               
                        if( $pme_row->Id == $hap_row->api_prop_id ){
                            $is_prop_hidden = true;                                                        
                        }                      

                    }                    


                    // exclude sales property
                    if ( $pme_row->SaleAgreementUpdatedOn !== "0001-01-01" ) {
                        $is_sales_prop = true;
                    }

                    // not connected, no PNV notes and not property sales
                    if( $has_connected == false && $has_pnv_notes == false && $is_sales_prop == false && $is_prop_hidden == false ){
                        $pnv_need_process_count++;
                        $highlight_red = true;
                    }
                    
                
                    $table_html .= "<tr style='".( ( $highlight_red == true )?'background-color: green;':'background-color: red;' )."'>
                        <td style='text-align:left; border:1px solid;'>".( ( $highlight_red == true )?$pnv_need_process_count:null )."</td>
                        <td style='text-align:left; border:1px solid;'>{$pme_address}</td>                        
                        <td style='text-align:left; border:1px solid;'>{$crm_connected_prop}</td>
                        <td style='text-align:left; border:1px solid;'>{$pme_row->Id}</td>
                        <td style='text-align:left; border:1px solid;'>{$pme_prop_with_notes}</td>   
                        <td style='text-align:left; border:1px solid;'>".( ( $is_sales_prop == true )?'Yes':'No' )."</td> 
                        <td style='text-align:left; border:1px solid;'>".( ( $is_prop_hidden == true )?'Yes':'No' )."</td>                    
                    </tr>";            
                    
                }

                $table_html .= "
                <tr>
                    <td style='text-align:left; border:1px solid;' colspan='2'><b>BULK MATCH NEEDS TO PROCESS COUNT</b></td>
                    <td style='text-align:left; border:1px solid;'><b>{$pnv_need_process_count}</b></td>
                    <td style='text-align:left; border:1px solid;'></td>
                </tr>
                </table>
                </div>
                <div style='clear:both;'></div>
                ";

                echo $table_html;

                
                // check if agency already exist
                $check_sql_str = "
                SELECT COUNT(`pme_upc_id`) AS pme_upc_count
                FROM `pme_unmatched_property_count` 
                WHERE `agency_id` = {$agency_id}
                ";

                $check_sql = $this->db->query($check_sql_str);
                $pme_upc_count = $check_sql->row()->pme_upc_count;

                if( $pme_upc_count > 0 ){ // record found

                    // update
                    $update_sql_str = "
                    UPDATE `pme_unmatched_property_count` 
                    SET `count` = {$pnv_need_process_count}
                    WHERE `agency_id` = {$agency_id}
                    ";
                    $this->db->query($update_sql_str);

                    
                }else{

                    // insert 
                    $insert_sql_str = "
                    INSERT INTO 
                    `pme_unmatched_property_count` (
                        `agency_id`,
                        `count`,
                        `created_date`
                    )
                    VALUES (
                        {$agency_id},
                        {$pnv_need_process_count},
                        '{$today}'
                    )
                    ";
                    $this->db->query($insert_sql_str);

                }

            }

        }

    }


    public function api_find_unmatched_properties(){

        $this->load->model('pme_model');      
        $this->load->model('palace_model');
        $this->load->model('property_tree_model');
        $this->load->model('ourtradie_model');

        $today = date('Y-m-d H:i:s');

        $agency_sql_str = "
            SELECT 
                DISTINCT(agen_tok.`agency_id`),
                agen_tok.`api_id`,
                
                agen_api.`api_name`,

                a.`agency_name`
            FROM `agency_api_tokens` AS agen_tok 
            LEFT JOIN `agency_api` AS agen_api ON ( agen_tok.`api_id` = agen_api.`agency_api_id` )
            LEFT JOIN `agency` AS a ON ( a.`agency_id` = agen_tok.`agency_id`  )
            WHERE a.`status` = 'active'      
            AND a.`deleted` = 0
        ";
 
        $agency_sql = $this->db->query($agency_sql_str);
        foreach( $agency_sql->result() as $agency_row ){

            $agency_id = $agency_row->agency_id;            

            if( $agency_id > 0 ){

                echo "<h1>{$agency_row->agency_name}($agency_id) - {$agency_row->api_name}</h1>";

                $property_sql_str = "
                SELECT 
                    p.`address_1` AS p_street_num,
                    p.`address_2` AS p_street_name,
                    p.`address_3` AS p_suburb,
                    p.`state` AS p_state,
                    p.`postcode` AS p_postcode,

                    apd.`api_prop_id`
                FROM `property` AS p
                LEFT JOIN `api_property_data` AS apd ON ( p.property_id = apd.crm_prop_id AND apd.api = {$agency_row->api_id} )
                WHERE p.`agency_id` = {$agency_id}    
                AND ( apd.`api_prop_id` IS NOT NULL AND apd.`api_prop_id` != '' AND apd.api = {$agency_row->api_id} )                     
                ";

                $property_sql = $this->db->query($property_sql_str);
                $crm_prop = $property_sql->result();                            

                // PNV
                // exclude already NLM properties and do not show already linked properties
                $pnv_sql_str = "
                SELECT 
                    pnv.`pnv_id`, 
                    pnv.`property_source`, 
                    pnv.`property_id`, 
                    pnv.`agency_id`, 
                    pnv.`note`, 
                    pnv.`agency_verified`,
                    pnv.`property_address`,

                    a.`agency_id`, 
                    a.`agency_name`
                FROM `properties_needs_verification` AS `pnv`                
                INNER JOIN `agency` AS `a` ON pnv.`agency_id` = a.`agency_id`
                WHERE pnv.`active` = 1
                AND a.`agency_id` = {$agency_id}
                AND pnv.`property_source` = 2
                AND pnv.`ignore_issue` = 0
                ";
                $pnv_sql = $this->db->query($pnv_sql_str);
                $pnv_row_res = $pnv_sql->result();
                //print_r($pnv_row_res);


                // hidden properties
                $hap_sql_str = "
                SELECT id AS hap_id, `api_prop_id`
                FROM `hidden_api_property`
                WHERE`agency_id` = {$agency_id}
                ";
                $hap_sql = $this->db->query($hap_sql_str);
                $hap_res = $hap_sql->result();


                // CRM Table
                $table_html = "
                <div style='float: left;'>
                <h2>CRM Properties</h2>
                <table style='border:1px solid; border:1px solid; border-collapse: collapse; margin-right: 50px;'>
                <tr>
                    <th style='text-align:left; border:1px solid;'>#</th>
                    <th style='text-align:left; border:1px solid;'>Address</th>
                    <th style='text-align:left; border:1px solid;'>Stored {$agency_row->api_name} Prop ID</th>
                </tr>
                ";
                foreach( $crm_prop as $index => $prop_row ){

                    $p_address = "{$prop_row->p_street_num} {$prop_row->p_street_name} {$prop_row->p_suburb} {$prop_row->p_state} {$prop_row->p_postcode}";

                    $table_html .= "<tr>
                        <td style='text-align:left; border:1px solid;'>".( $index+1 ).".)</td>
                        <td style='text-align:left; border:1px solid;'>{$p_address}</td>
                        <td style='text-align:left; border:1px solid;'>{$prop_row->api_prop_id}</td>
                    </tr>";            

                }
                $table_html .= "</table>
                </div>
                ";

                echo $table_html;

                
                // API Table
                $table_html = "
                <div style='float: left;'>
                <h2>{$agency_row->api_name} Properties</h2>
                <table style='border:1px solid; border:1px solid; border-collapse: collapse;'>
                <tr>
                    <th style='text-align:left; border:1px solid;'>Count</th>
                    <th style='text-align:left; border:1px solid;'>Address</th>                    
                    <th style='text-align:left; border:1px solid;'>CRM property Connected to</th>
                    <th style='text-align:left; border:1px solid;'>{$agency_row->api_name} Prop ID</th>
                    <th style='text-align:left; border:1px solid;'>Has notes on PNV</th>     
                    <th style='text-align:left; border:1px solid;'>Property Sales?</th>
                    <th style='text-align:left; border:1px solid;'>Is Hidden?</th>               
                </tr>
                ";

                // get API properties
                $json_dec = null;
                if( $agency_row->api_id == 1 ){ // PMe

                    $response = $this->pme_model->get_all_properties($agency_id);	
                    $json_dec = json_decode($response);
                    //print_r($json_dec);

                }else if( $agency_row->api_id == 4 ){ // Palace

                    $json_dec = $this->palace_model->get_all_properties($agency_id);

                }else if( $agency_row->api_id == 3 ){ // PropertyTree

                    $json_dec = $this->property_tree_model->get_all_properties($agency_id);

                }else if( $agency_row->api_id == 6 ){ // OurProperty

                    $api = new OurtradieApi();      

                    $token['token'] = $this->ourtradie_model->getToken($agency_id, $agency_row->api_id);

                    /*
                    echo "<pre>";
                    print_r($token['token']);
                    echo "</pre>";
                    */

                    $access_token   = $token['token'][0]->access_token;
                    $tmp_ref_token   = $token['token'][0]->refresh_token;
                    $tmp_arr_ref_token = explode("+/-]",$tmp_ref_token);

                    $op_agency_id = $tmp_arr_ref_token[1];
                    //echo "op_agency_id: {$op_agency_id}";

                    $token = array('access_token' => $access_token);

                    //GetAllResidentialProperties
                    $params = array(
                        'Skip' 	 		=> 'No',
                        'Count'     => 'No',
                        'AgencyID'  => $op_agency_id
                    );
                    $op_json = $api->query('GetAllResidentialProperties', $params, '', $token, true);
                    $json_dec_temp = json_decode($op_json);
                    $json_dec = $json_dec_temp->data;

                    /*
                    echo "<pre>";
                    print_r($json_dec);
                    echo "</pre>";
                    */
                    

                }

                $pnv_need_process_count = 0;
                foreach ( $json_dec as $key => $api_row ){

                    $has_connected = false;
                    $pme_prop_id = null;
                    $crm_connected_prop = null;
                    $has_pnv_notes = false;
                    $pme_prop_with_notes = null;
                    $green_highlight = false;
                    $is_sales_prop = false;
                    $is_prop_hidden = false;
                    $api_address = null;
                    $api_prop_id = null;
                    $is_deleted = false;
                
                    // get API properties
                    if( $agency_row->api_id == 1 ){ // PMe
          
                        $api_address = $api_row->AddressText;
                        $api_prop_id = $api_row->Id;

                        // exclude sales property
                        if ( $api_row->SaleAgreementUpdatedOn !== "0001-01-01" ) {
                            $is_sales_prop = true;
                        }

                        if( $api_row->IsArchived == true ){
                            $is_deleted = true;
                        }
                        
                    }else if( $agency_row->api_id == 4 ){ // Palace

                        if (trim($api_row->PropertyUnit) != "") {
                            $addUnit = $api_row->PropertyUnit . "/";
                        }else {
                            $addUnit = "";
                        }

                        $api_address = $addUnit.$api_row->PropertyAddress1 . " " . $api_row->PropertyAddress2 . ", " .$api_row->PropertyAddress3 . " " . $api_row->PropertyAddress4 . " " . $api_row->PropertyPostCode;
                        $api_prop_id = $api_row->PropertyCode;

                        // deleted on API
                        if( $api_row->PropertyArchived == true ){
                            $is_deleted = true;
                        }
    
                    }else if( $agency_row->api_id == 3 ){ // PropertyTree
                        
                        $address_obj = $api_row->address;

                        // street
                        if( $address_obj->unit != '' && $address_obj->street_number != '' ){
                            $street_unit_num = "{$address_obj->unit}/{$address_obj->street_number}";
                        }else if( $address_obj->unit != '' ){
                            $street_unit_num = "{$address_obj->unit}";
                        }else if( $address_obj->street_number != '' ){
                            $street_unit_num = "{$address_obj->street_number}";
                        }
                            
                        $pt_prop_add = "{$street_unit_num} {$address_obj->address_line_1}, {$address_obj->suburb} {$address_obj->state} {$address_obj->post_code}";    
          
                        $api_address = $pt_prop_add;
                        $api_prop_id = $api_row->id;                        
                        
                    }if( $agency_row->api_id == 6 ){ // OurProperty
          
                        //$api_address = trim($api_row['Address1'],"Street").$api_row['Suburb']." ".$api_row['State']." ".$api_row['Postcode'];
                        $api_address = trim($api_row->Address1,"").$api_row->Suburb." ".$api_row->State." ".$api_row->Postcode;
                        //$api_address - '';
                        $api_prop_id = $api_row->ID;
                        
                    }                    
                    
                    // crm        
                    foreach( $crm_prop as $index => $crm_row ){   
                                               

                        $crm_address = "{$prop_row->p_street_num} {$prop_row->p_street_name} {$prop_row->p_suburb} {$prop_row->p_state} {$prop_row->p_postcode}";             
                        
                        if( $api_prop_id == $crm_row->api_prop_id ){
                            $has_connected = true;                            
                            $crm_connected_prop = $crm_address;
                        }
                      

                    }

                    // PNV        
                    foreach( $pnv_row_res as $index => $pnv_row ){   
                                               

                        $pnv_prop_add = $pnv_row->property_address;
                        
                        if( $api_prop_id == $pnv_row->property_id ){
                            $has_pnv_notes = true;                            
                            $pme_prop_with_notes = $pnv_prop_add;
                        }
                      

                    }

                    // hidden        
                    foreach( $hap_res as $index => $hap_row ){   
                                               
                        if( $api_prop_id == $hap_row->api_prop_id ){
                            $is_prop_hidden = true;                                                        
                        }                      

                    }                                     

                    // not connected, no PNV notes, not property sales and not hidden
                    if( $has_connected == false && $has_pnv_notes == false && $is_sales_prop == false && $is_prop_hidden == false && $is_deleted == false ){
                        $pnv_need_process_count++;
                        $green_highlight = true;
                    }
                    
                
                    $table_html .= "<tr style='".( ( $green_highlight == true )?'background-color: green;':'background-color: red;' )."'>
                        <td style='text-align:left; border:1px solid;'>".( ( $green_highlight == true )?$pnv_need_process_count:null )."</td>
                        <td style='text-align:left; border:1px solid;'>{$api_address}</td>                        
                        <td style='text-align:left; border:1px solid;'>{$crm_connected_prop}</td>
                        <td style='text-align:left; border:1px solid;'>{$api_prop_id}</td>
                        <td style='text-align:left; border:1px solid;'>{$pme_prop_with_notes}</td>   
                        <td style='text-align:left; border:1px solid;'>".( ( $is_sales_prop == true )?'Yes':'No' )."</td> 
                        <td style='text-align:left; border:1px solid;'>".( ( $is_prop_hidden == true )?'Yes':'No' )."</td>                    
                    </tr>";            
                    
                }

                $table_html .= "
                <tr>
                    <td style='text-align:left; border:1px solid;' colspan='2'><b>BULK MATCH NEEDS TO PROCESS COUNT</b></td>
                    <td style='text-align:left; border:1px solid;'><b>{$pnv_need_process_count}</b></td>
                    <td style='text-align:left; border:1px solid;'></td>
                </tr>
                </table>
                </div>
                <div style='clear:both;'></div>
                ";

                echo $table_html;
                
                // check if agency already exist
                $check_sql_str = "
                SELECT COUNT(`pme_upc_id`) AS pme_upc_count
                FROM `pme_unmatched_property_count` 
                WHERE `agency_id` = {$agency_id}
                ";

                $check_sql = $this->db->query($check_sql_str);
                $pme_upc_count = $check_sql->row()->pme_upc_count;

                if( $pme_upc_count > 0 ){ // record found

                    // update
                    $update_sql_str = "
                    UPDATE `pme_unmatched_property_count` 
                    SET `count` = {$pnv_need_process_count}
                    WHERE `agency_id` = {$agency_id}
                    ";
                    $this->db->query($update_sql_str);

                    
                }else{

                    // insert 
                    $insert_sql_str = "
                    INSERT INTO 
                    `pme_unmatched_property_count` (
                        `agency_id`,
                        `count`,
                        `created_date`
                    )
                    VALUES (
                        {$agency_id},
                        {$pnv_need_process_count},
                        '{$today}'
                    )
                    ";
                    $this->db->query($insert_sql_str);

                }                 
                              

            }

        }

    }


    public function create_active_properties_snapshot(){      

        $current_month = date("m");
        $current_year = date("Y");
        $today = date("Y-m-d H:i:s");

        $table_name = "active_properties_{$current_month}_{$current_year}";

        $this->db->trans_start();

        // create new table
        echo $create_table_query = "
        CREATE TABLE IF NOT EXISTS {$table_name} ( property_id INT(10) UNSIGNED NULL )
        ";
        echo "<br /><br />";
        $this->db->query($create_table_query);

        // insert property in new table
        echo $select_and_insert_query = "
        INSERT INTO {$table_name} ( `property_id` )
        SELECT p.`property_id`
        FROM `property` AS p
        INNER JOIN `property_services` AS ps ON ( p.`property_id` = ps.`property_id` AND ps.`service` = 1 )
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        WHERE p.`deleted` = 0
        AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
        AND a.`status` = 'active'
        AND `a`.`franchise_groups_id` != 14
        GROUP BY p.`property_id`
        ";
        echo "<br /><br />";
        $this->db->query($select_and_insert_query);

        // insert reference table
        $crontitle = date("M Y");
        $insert_cron_reference_sql_str = "
        INSERT INTO 
        `active_properties_cron` (
            `crontitle`,
            `crontable`,
            `crondate`,
            `cronstatus`
        )
        VALUES(
            '{$crontitle}',
            '{$table_name}',
            '{$today}',
            1
        )
        ";
        $this->db->query($insert_cron_reference_sql_str);
                           
        $this->db->trans_complete();                                             

    }


    public function send_sms_to_renewed_jobs_yesterday(){

        $yesterday = date('Y-m-d',strtotime("yesterday"));
        $staff_id = -3; // CRON
        $country_id = $this->config->item('country');

        $title = 51; // Service Due
        $logs_sql = $this->db->query("
        SELECT 
            j.`id` AS jid,

            p.`property_id`,
            p.`address_1` AS p_street_num,
            p.`address_2` AS p_street_name,
            p.`address_3` AS p_suburb,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode
        FROM `logs` AS l
        LEFT JOIN  `jobs` AS j ON l.`job_id` = j.`id` 
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        LEFT JOIN `agency_api_tokens` AS aat ON a.`agency_id` = aat.`agency_id`
        WHERE l.`title` = {$title}
        AND l.`auto_process` = 1
        AND p.`deleted` = 0
        AND ( p.is_nlm = 0 OR p.is_nlm IS NULL )
        AND j.`del_job` = 0
        AND DATE(l.`created_date`) = '{$yesterday}'
        AND p.`state` != 'QLD'
        AND aat.`agency_api_token_id`  IS NULL
        ");

        foreach( $logs_sql->result() as $logs_row ){

            $job_id = $logs_row->jid;
            $property_id = $logs_row->property_id;   
            
            $custom_where = "pt.`tenant_mobile` != ''"; // exclude tenants with empty mobile numbers

            // get only 1 tenant 
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
                'custom_where' => $custom_where,
                'offset' => 0,
                'limit' => 1,
                'display_query' => 0
            );
            $pt_sql = $this->properties_model->get_property_tenants($params);
            $pt_row = $pt_sql->row();

            // only SMS to booked with tenants
            if( $pt_row->tenant_mobile != "" ){

                // format phone number
                $send_to = $this->sms_model->formatToInternationNumber($pt_row->tenant_mobile); 
                
                // SMS type: Pre-Booking Reminder SMS
                if( ENVIRONMENT == 'production' ){ // live

                    if( $country_id == 1 ){ // AU                        
                        $sms_type = 55;   
                    }else if( $country_id == 2 ){ // NZ
                        $sms_type = 41; 
                    }                    
        
                }else{ // dev
        
                    $sms_type = 38; // Pre-Booking Reminder SMS  
        
                }

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

                //insert log
                $log_details = "Renewed jobs yesterday SMS to {$send_to} <strong>{$parsed_template_body}</strong>";
                $log_params = array(
                    'title' => 40, // SMS sent
                    'details' => $log_details,
                    'display_in_vjd' => 1,
                    'auto_process' => 1,
                    'job_id' => $job_id
                );
                $this->system_model->insert_log($log_params);
                                    
            }
            
        }

    }
    
    

}
