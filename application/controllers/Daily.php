<?php

class Daily extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('properties_model');
        $this->load->model('daily_model');
        $this->load->model('agency_model');
        $this->load->model('jobs_model');
        $this->load->model('staff_accounts_model');
        $this->load->library('pagination');
    }

    public function active_unsold_services() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Active Unsold Services";
        $uri = '/daily/active_unsold_services';

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;

        // select
        $sel_query = "
        ps.`property_services_id`,
        
        ajt.`id`,
        ajt.`type`,
        ajt.`short_name`, 
        
        p.`property_id`,
        p.`address_1`,
        p.`address_2`,
        p.`address_3`,
        p.`state`,
        p.`postcode`,
        p.`deleted`,
        
        a.`agency_id`,
        a.`agency_name`,
        aght.priority,
        apmd.abbreviation,
        
        agen_serv.`agency_services_id`,
        agen_serv.`service_id`,
        agen_serv.`price`
		";

        $custom_where = "agen_serv.`agency_services_id` IS NULL";

        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'ps_service' => 1,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,
            'join_table' => array('alarm_job_type', 'agency_priority', 'agency_priority_marker_definition'),
            'custom_joins' => array(
                'join_table' => '`agency_services` AS agen_serv',
                'join_on' => '( agen_serv.`agency_id` = a.`agency_id` AND agen_serv.`service_id` = ps.`alarm_job_type_id` )',
                'join_type' => 'left'
            ),
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC',
                )
            ),
            'limit' => 200,
            'offset' => 0,
            'display_query' => 0
        );
        $data['list'] = $this->properties_model->getPropertyServices($params);
        $data['sql_query'] = $this->db->last_query(); //Show query on About

        // get all rows
        $sel_query = "COUNT(ps.`property_services_id`) AS jcount";            
        $custom_where = "agen_serv.`agency_services_id` IS NULL";
    
        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'ps_service' => 1,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,

            'join_table' => array('alarm_job_type', 'agency_priority'),

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
            'page' => $uri,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        $data['uri'] = $uri;

        $this->load->view('templates/inner_header', $data);
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function update_to_nr() {

        $property_services_id = $this->input->get_post('property_services_id');

        // update service to No Response
        if ($property_services_id > 0) {

            $update_str = "
            UPDATE `property_services`
            SET `service` = 2
            WHERE `property_services_id` = " . $this->db->escape($property_services_id) . "
            ";
            $this->db->query($update_str);
            $success = 1;
            $msg = 'Property Service Updated to No Response';
        } else {
            $success = 0;
            $msg = null;
        }

        $arr = array(
            "success" => $success,
            "msg" => $msg
        );
        echo json_encode($arr);
    }

    public function no_job_status() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "No Job Status";
        $uri = '/daily/no_job_status';

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;

        $sql_str = "
        SELECT 
            j.`id` AS jid,
            j.`del_job`,
            j.`date` AS jdate,
            j.`created` AS jcreated,
        
            ajt.`id`,
            ajt.`type`, 
            
        
            p.`property_id`,
            p.`address_1`,
            p.`address_2`,
            p.`address_3`,
            p.`state`,
            p.`postcode`,
            p.`deleted` AS pdeleted,
        
            a.`agency_id`,
            a.`agency_name`,
            a.`status` AS a_status,
            aght.priority,
            apmd.abbreviation
        FROM `jobs` AS j 
        LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
        LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        LEFT JOIN `agency_priority` as aght ON a.`agency_id` = aght.`agency_id`
        LEFT JOIN `agency_priority_marker_definition` as apmd ON aght.`priority` = apmd.`priority`
        WHERE (
            j.`status` = '' OR
            j.`status` IS NULL
        )
        ORDER BY j.`created` DESC
        ";
        $data['list'] = $this->db->query($sql_str);

        // get all rows
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
            'page' => $uri,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        $data['uri'] = $uri;

        $this->load->view('templates/inner_header', $data);
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function no_job_types() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "No Job Types";
        $uri = '/daily/no_job_status';

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;

        $sql_str = "
        SELECT 
            j.`id` AS jid,
            j.`del_job`,
            j.`date` AS jdate,
            j.`created` AS jcreated,
        
            ajt.`id`,
            ajt.`type`, 
            
        
            p.`property_id`,
            p.`address_1`,
            p.`address_2`,
            p.`address_3`,
            p.`state`,
            p.`postcode`,
            p.`deleted` AS pdeleted,
        
            a.`agency_id`,
            a.`agency_name`,
            a.`status` AS a_status,
            aght.priority,
            apmd.abbreviation
        FROM `jobs` AS j 
        LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
        LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        LEFT JOIN `agency_priority` as aght ON a.`agency_id` = aght.`agency_id`
        LEFT JOIN `agency_priority_marker_definition` as apmd ON aght.`priority` = apmd.`priority`
        WHERE (
            j.`job_type` = '' OR
            j.`job_type` IS NULL OR
            j.`job_type` = 'None Selected'
        )
        ORDER BY j.`created` DESC
        ";
        $data['list'] = $this->db->query($sql_str);

        // get all rows
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
            'page' => $uri,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        $data['uri'] = $uri;

        $this->load->view('templates/inner_header', $data);
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function view_no_id_properties() {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "No ID Properties";
        $per_page = $this->config->item('pagi_per_page');
        $offset = (int) $this->input->get_post('offset');

        $no_id_properties = $this->properties_model->getPropertyNoAgency($offset, $per_page)->result_array();
        $agencies = $this->agency_model->get_agency([
                    'sel_query' => '*',
                    'custom_where' => "status='active'"
                ])->result_array();
        $data['properties'] = $no_id_properties;
        $data['agencies'] = $agencies;
        $ptotal = $this->properties_model->getPropertyNoAgencyCount()->row()->prop_count;
        $total_rows = $ptotal;
        $data['sort_list'] = $total_rows;
        // base url
        $base_url = '/daily/view_no_id_properties/';

        // pagination
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $base_url;

        $this->pagination->initialize($config);

        $data['pagination'] = $this->pagination->create_links();

        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);
        $this->load->view('templates/inner_header', $data);
        $this->load->view('daily/view_no_id_properties', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function assign_agency_action_ajax() {
        $prop_id = $this->input->post('property_id');
        $agency_id = $this->input->post('agency_id');
        $isUpdated = $this->properties_model->update_property($prop_id, [
            'agency_id' => $agency_id
        ]);

        if ($isUpdated) {
            $this->session->set_flashdata([
                'success_msg' => 'Agency has been updated',
                'status' => 'success'
            ]);
        } else {
            $this->session->set_flashdata([
                'error_msg' => 'Unsuccessful',
                'status' => 'error'
            ]);
        }
    }

    public function view_last_contact() 
    {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Last Contact";
        $per_page = $this->config->item('pagi_per_page');        
        $offset = (int) $this->input->get_post('offset');
        $order_by = ( $this->input->get_post('order_by') != "" ) ? $this->input->get_post('order_by') : 'last_contact';
        $sort = ( $this->input->get_post('sort') != "" ) ? $this->input->get_post('sort') : 'ASC';
        $state = $this->input->get_post('state');
        $agency_filter = $this->input->get_post('agency_filter');
        $export = $this->input->get_post('export');

        $agency_priority_filter = $this->input->get_post('agency_priority_filter');
        
        if($export==1){
            $jobs = $this->daily_model->get_job_last_contact('', '', $order_by, $sort, $state, $agency_filter, $agency_priority_filter);

            // file name
            $datestamp = date('d-m-y');
            $filename = "last_contact{$datestamp}.csv";

            header("Content-Type: text/csv");
            header("Content-Disposition: Attachment; filename={$filename}");
            header("Pragma: no-cache");

            $str = "Created,Last Contact,Days,Job Type,Address,State,Agency,Job Notes,Comments,Assigned Tech,Job #\n";
            foreach ($jobs as $row) {
                $date_created = ($row['jcreated'] != "" && $row['jcreated'] != "0000-00-00") ? date("d/m/Y", strtotime($row['jcreated'])) : '';
                $last_contact = date("d/m/Y", strtotime($row['last_contact']));

                $now = time(); // or your date as well
                $your_date = strtotime($row['last_contact']);
                $datediff = $now - $your_date;
                $days = floor($datediff / (60 * 60 * 24));

                $job_type = $this->gherxlib->getJobTypeAbbrv($row['job_type']);

                $str .= "{$date_created},\"{$last_contact}\",\"{$days}\",\"{$job_type}\",\"{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}\",\"{$row['p_state']}\",\"{$row['agency_name']}\",\"{$row['comments']}\",\"{$row['lcc_comments']}\",\"{$row['FirstName']} {$row['LastName']}\",\"{$row['jid']}\"\n";
            }
            echo $str;
        } else {

            $jobs = $this->daily_model->get_job_last_contact($offset, $per_page, $order_by, $sort, $state, $agency_filter, $agency_priority_filter);
            $data['sql_query'] = $this->db->last_query(); //Show query on About
            $data['jobs'] = $jobs;
            $data['total_jobs'] = $this->daily_model->get_job_last_contact('', '', $order_by, $sort, $state, $agency_filter, $agency_priority_filter);

            $sa_tech = $this->staff_accounts_model->get_staff_accounts_with_country_access([
                        'sel_query' => "sa.`StaffID`, sa.`FirstName`, sa.`LastName`, sa.`is_electrician`, sa.`active` AS sa_active",
                        'joins' => array('country_access'),
                        'custom_where' => "ca.`country_id` ={$this->config->item('country')}
                                AND sa.`Deleted` = 0
                                AND sa.`ClassID` = 6
                                AND sa.`active` = 1",
                        "sort_list" => array(
                            ["order_by" => "sa.`FirstName`", "sort" => "ASC"],
                            ["order_by" => "sa.`LastName`", "sort" => "ASC"]
                        )
                    ])->result_array();
            $data['sa_tech'] = $sa_tech;
            $ptotal = count($data['total_jobs']);
            $total_rows = $ptotal;
            $data['sort_list'] = $total_rows;

            
            // get distinct agency
            $distinct_agency_arr = [];
            foreach ($jobs as $job_row){
            
                if( !in_array($job_row['agency_id'], $distinct_agency_arr) ){
                    $distinct_agency_arr[] = $job_row['agency_id'];
                }                      
                
            }

            $agency_imp = implode(",",$distinct_agency_arr);
            $data['agency_list_id'] = $agency_imp;
            $data['çount_distinct_agency_arr'] = count($distinct_agency_arr);

            if( count($distinct_agency_arr) > 0 ){

                $distinct_agency_imp = implode(",",$distinct_agency_arr);

                $data['dist_agency_sql'] = $this->db->query("
                    SELECT `agency_id`, `agency_name`
                    FROM `agency`
                    WHERE `agency_id` IN({$distinct_agency_imp})
                    ORDER BY `agency_name`
                ")->result();
            }

            $pagi_links_params_arr = array(
                'state' => $state
            );
            $pagi_link_params = '/daily/view_last_contact/?'.http_build_query($pagi_links_params_arr);

            // pagination
            $config['page_query_string'] = TRUE;
            $config['query_string_segment'] = 'offset';
            $config['total_rows'] = $total_rows;
            $config['per_page'] = $per_page;
            $config['base_url'] = $pagi_link_params;

            $this->pagination->initialize($config);

            $data['pagination'] = $this->pagination->create_links();

            $pc_params = array(
                'total_rows' => $total_rows,
                'offset' => $offset,
                'per_page' => $per_page
            );
            $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);
            $this->load->view('templates/inner_header', $data);
            $this->load->view('daily/view_last_contact', $data);
            $this->load->view('templates/inner_footer', $data);
        }
    }

    function snooze_property(){
        $property_id = $this->input->get_post('property_id');
        $snooze_reason = $this->input->get_post('snooze_reason');

        $snooze_days = 15;
		//update property
		$postpone_due_job_date = date('Y-m-d H:i:s', strtotime("+ {$snooze_days} days"));
		$this->db->where('property_id', $property_id);
		$this->db->set('postpone_due_job', $postpone_due_job_date);
		$this->db->update('property');
		//update property end

		//get property active jobs
		$active_job_query = $this->daily_model->get_properties_active_jobs_for_overdue_nsw_jobs($property_id);

		//insert job log
		$log_details = "Due date postponed for <strong>{$snooze_days}</strong> days because <strong>{$snooze_reason},</strong> affected jobs:({$active_job_query}).";
		$log_params = array(
			'title' => 68,  //Snooze day
			'details' => $log_details,
			'display_in_vpd' => 1,
			'created_by_staff' => $this->session->staff_id,
			'property_id' => $property_id
		);
		$this->system_model->insert_log($log_params);
    }

    public function assign_tech_to_jobs_action_ajax() {
        $job_ids = $this->input->post('job_id');
        $tech_id = $this->input->post('tech_id');
        $date = $this->input->post('date');
        $date2 = date("Y-m-d", strtotime(str_replace("/", "-", $date)));
        $isUpdated = true;
        foreach ($job_ids as $job_id) {
            $isUpdated = $this->jobs_model->update_job(
                    $job_id, [
                '`status`' => 'To Be Booked',
                '`assigned_tech`' => $tech_id,
                'date' => $date2
            ]);
            if (!$isUpdated) {
                $this->session->set_flashdata([
                    'error_msg' => 'Unsuccessful. Cannot update Job Id # ' . $job_id,
                    'status' => 'error'
                ]);
                exit;
            }
        }
        $this->session->set_flashdata([
            'success_msg' => 'Selected Jobs has been successfully assigned',
            'status' => 'success'
        ]);
    }

    public function view_no_active_job_properties() {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "No Active Job Properties";
        $per_page = $this->config->item('pagi_per_page');
        $offset = (int) $this->input->get_post('offset');
        $is_show = $this->input->get_post('show_all');

        $data['properties'] = $this->properties_model->get_no_active_job_properties(null, $is_show, $offset, $per_page)->result_array();
        $data['sql_query'] = $this->db->last_query(); //Show query on About

        $total_rows = $this->properties_model->get_no_active_job_properties(null, $is_show);
        $data['sort_list'] = $total_rows->num_rows();
        // base url
        $base_url = '/daily/view_no_active_job_properties/';

        // pagination
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows->num_rows();
        $config['per_page'] = $per_page;
        $config['base_url'] = $base_url;

        $this->pagination->initialize($config);

        $data['pagination'] = $this->pagination->create_links();

        $pc_params = array(
            'total_rows' => $total_rows->num_rows(),
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);
        $this->load->view('templates/inner_header', $data);
        $this->load->view('daily/view_no_active_job_properties', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function str_less_jobs(){

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');
        $page_url = '/daily/str_less_jobs';
        $data['page_url'] = $page_url;

        $sel_query = "
            j.`id` AS jid, 
            j.`created` AS jcreated, 
            j.`date` AS jdate, 
            j.`service` AS jservice, 
            j.`job_type`,

            p.`property_id`,
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3, 
            p.`state` AS p_state, 
            p.`postcode` AS p_postcode,   

            a.`agency_id`,
            a.`agency_name`,
            aght.priority,

            sa.`FirstName`,
            sa.`LastName`
        ";
        $params = array(
            'sel_query' => $sel_query,
            //'group_by' => 'j.id',
            //'limit' =>  $per_page,
           // 'offset' => $offset
        );
        $data['lists'] = $this->daily_model->findBookedJobsNotOnAnySTR($params);

        // all rows
       /* 
       $total_sel_query = "COUNT(DISTINCT(j.`id`)) as j_count";
        $total_params = array(
            'sel_query' => $total_sel_query,
        );
        $query = $this->daily_model->findBookedJobsNotOnAnySTR($total_params);
        $total_rows = $query->row()->j_count;

        // pagination settings
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = "/daily/str_less_jobs";

        $this->pagination->initialize($config);
        
        $data['pagination'] = $this->pagination->create_links();
        
        // pagination count
        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);
        
        */

        $data['start_load_time'] = microtime(true);
        $data['title'] = "STR-less Jobs";
        $this->load->view('templates/inner_header', $data);
        $this->load->view('jobs/str_less_jobs', $data);
        $this->load->view('templates/inner_footer', $data);

    }

    public function multiple_jobs(){

        $state_filter = $this->input->get_post('state_filter');
        $upfront_bill = ( $this->input->get_post('upfront_bill') == 1 )?1:0;        

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');
        $page_url = '/daily/multiple_jobs';

        $country_id = $this->config->item('country');
        $custom_where = null;

        $sel_query = "
        COUNT( j.`id` ) AS jcount , 
        
        j.`id`, j.`job_type`, 
        j.`status` , 
        j.`property_id` , 
        j.`service` AS jservice, 

        p.`address_1`, 
        p.`address_2`, 
        p.`address_3, 
        p.`state`, 
        p.`deleted`, 
        
        a.`agency_name`, 
        a.`agency_id`,
        a.`allow_upfront_billing`,
        aght.priority,
        
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type
        ";


        // upfront bill
        $custom_where = "a.`allow_upfront_billing` = {$upfront_bill}";

        // state filter        
        if( $state_filter != '' ){
            $custom_where .= " AND p.`state` = '{$state_filter}' ";
        }         
        
        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'limit' =>  $per_page,
            'offset' => $offset,
            'display_query' => 0
        );
        $data['lists'] = $this->daily_model->getMultipleJobs($params);

        // all rows
        $sel_query = "COUNT(j.id) as jcount";
        $total_params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where
        );
        $query = $this->daily_model->getMultipleJobs($total_params);
        //$total_rows = $query->row()->jcount;
        $total_rows = $query->num_rows();

        
        // state filter    
        $state_sql_str = "
            SELECT `state`
            FROM `states_def`
            WHERE `country_id` = {$country_id}                  
            ORDER BY `state`      
        ";    
        $data['state_sql'] = $this->db->query($state_sql_str);

        if($upfront_bill == 0 && empty($state_filter)){
            // update page total
            $page_tot_params = array(
                'page' => $page_url,
                'total' => $total_rows
            );
            $this->system_model->update_page_total($page_tot_params);
        }
        
        $pagi_links_params_arr = array(
            'state_filter' => $state_filter,
            'upfront_bill' => $upfront_bill
        );
        $pagi_link_params = '/daily/multiple_jobs/?'.http_build_query($pagi_links_params_arr);

        // pagination settings
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $pagi_link_params;

        $this->pagination->initialize($config);
        
        $data['pagination'] = $this->pagination->create_links();
        
        // pagination count
        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Multiple Jobs";
        $this->load->view('templates/inner_header', $data);
        $this->load->view('jobs/multiple_jobs', $data);
        $this->load->view('templates/inner_footer', $data);

    }


    public function duplicate_visit(){

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( $this->input->get_post('offset') != '' )?$this->input->get_post('offset'):0;
        $uri = "/daily/duplicate_visit";
        $data['uri'] = $uri;
        $agency_filter = $this->input->get_post('agency_filter');

        $sel_query = "         
        j.`id` AS jid, 
        j.`job_type`, 
        j.`status` , 
        j.`property_id` , 
        j.`service`,
        
        p.`address_1` , 
        p.`address_2` , 
        p.`address_3`, 
        p.`state`, 
        p.`deleted`, 

        a.`agency_id`, 
        a.`agency_name`,
        aght.priority   
        ";
        $params = array(
            'sel_query' => $sel_query,
            'agency_filter' => $agency_filter,
            'limit' =>  $per_page,
            'offset' => $offset,
            'display_query' => 0
        );
        $data['lists'] = $this->daily_model->getDuplicateVisit($params);

        $data['about_text'] = $this->db->last_query();

        // all rows
        $total_sel_query = "COUNT(j.id) as jcount";
        $total_params = array(
            'agency_filter' => $agency_filter,
            'sel_query' => $total_sel_query
        );
        $query = $this->daily_model->getDuplicateVisit($total_params);
        $total_rows = $query->row()->jcount;
        //$total_rows = $query->num_rows();

        // update page total
        $page_tot_params = array(
            'page' => $uri,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        //Agency name filter
        $sel_query = "DISTINCT(a.`agency_id`),
        a.`agency_name`";
        $agency_filter_params = array(
            'sel_query' => $sel_query,
            'limit' =>  $per_page,
            'offset' => $offset,
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['agency_filter'] =  $this->daily_model->getDuplicateVisit($agency_filter_params);

        // pagination settings
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $uri;

        $this->pagination->initialize($config);
        
        $data['pagination'] = $this->pagination->create_links();
        
        // pagination count
        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Duplicate Visit";
        $this->load->view('templates/inner_header', $data);
        $this->load->view('jobs/duplicate_visit', $data);
        $this->load->view('templates/inner_footer', $data);

    }

    public function missing_region(){

        $per_page = $this->config->item('pagi_per_page');
        $offset = ($this->input->get_post('offset')!="") ? $this->input->get_post('offset') :0;
        $page_url = '/daily/missing_region';
        $data['page_url'] = $page_url;

        //$data['lists'] = $this->daily_model->getMissingRegionProperty($offset, $per_page);

        $sel_query = "
		p.`property_id`, 
		p.`address_1` AS p_address_1, 
		p.`address_2` AS p_address_2, 
		p.`address_3` AS p_address_3, 
		p.`state` AS p_state,
		p.`postcode` AS p_postcode, 
		
		a.`agency_id`, 
		a.`agency_name`,
        aght.priority,
        apmd.abbreviation
		";

		// paginated
		$params = array(
			'sel_query' => $sel_query,			

			'p_deleted' => 0,
			'a_status' => 'active',					
            'join_table' => array('agency_priority', 'agency_priority_marker_definition'),
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
		$data['lists'] = $this->properties_model->get_properties($params);
        $data['sql_query'] = $this->db->last_query(); //Show query on About

        // all rows
        //$query = $this->daily_model->getMissingRegionProperty('','');
        //$total_rows = $query->num_rows();

        /*
        // pagination settings
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = "/daily/missing_region";

        $this->pagination->initialize($config);
        
        $data['pagination'] = $this->pagination->create_links();
        
        // pagination count
        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);
        */

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Missing Region";
        $this->load->view('templates/inner_header', $data);
        $this->load->view('daily/missing_region', $data);
        $this->load->view('templates/inner_footer', $data);

    }


    public function unserviced(){

        $per_page = $this->config->item('pagi_per_page');
        $offset = ($this->input->get_post('offset')!="") ? $this->input->get_post('offset') :0;
        $page_url = '/daily/unserviced';
        // $prop = $this->daily_model->getExcludedProperties();
        

        if( $this->input->get_post('export') ==1 ){ //EXPORT

            // $lists_export_query = $this->daily_model->getUnservicedProperties($prop,'','');
            $lists_export_query = $this->daily_model->get_unserviced_by_markers('','');

            $filename = 'unserviced_' . date('Y-m-d') . '.csv';

            header("Content-Type: text/csv");
            header("Content-Disposition: Attachment; filename=$filename");
            header("Pragma: no-cache");

            //header
            echo "Property ID,Address,Agency,Last Job\n";

            foreach ($lists_export_query->result_array() as $u)
            {

                $ex_prop_id = $u['property_id'];
                $ex_full_add = "{$u['p_address1']} {$u['p_address2']} {$u['p_address3']} {$u['p_state']} {$u['p_postcode']}";
                $ex_agency = $u['agency_name'];
                $ex_last_job = ($this->daily_model->getGetLastJob($u['property_id'])!="")?date("d/m/Y",strtotime($this->daily_model->getGetLastJob($u['property_id']))):'';
               
                echo "\"{$ex_prop_id}\",{$ex_full_add},\"$ex_agency\",{$ex_last_job}\n"; 		
          
            }

        }else{ //LIST

            //main list
            // $data['lists'] = $this->daily_model->getUnservicedProperties($prop,$offset,$per_page);
            $data['lists'] = $this->daily_model->get_unserviced_by_markers($offset,$per_page);

            // all rows
            // $query = $this->daily_model->getUnservicedProperties($prop,'','');
            $query = $this->daily_model->get_unserviced_by_markers('','');
            $total_rows = $query->num_rows();

            // update page total
            $page_tot_params = array(
                'page' => $page_url,
                'total' => $total_rows
            );
            $this->system_model->update_page_total($page_tot_params);

            // pagination settings
            $config['page_query_string'] = TRUE;
            $config['query_string_segment'] = 'offset';
            $config['total_rows'] = $total_rows;
            $config['per_page'] = $per_page;
            $config['base_url'] = "/daily/unserviced";

            $this->pagination->initialize($config);
            
            $data['pagination'] = $this->pagination->create_links();
            
            // pagination count
            $pc_params = array(
                'total_rows' => $total_rows,
                'offset' => $offset,
                'per_page' => $per_page
            );
            $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

            $data['start_load_time'] = microtime(true);
            $data['title'] = "Unserviced";
            $this->load->view('templates/inner_header', $data);
            $this->load->view('daily/unserviced', $data);
            $this->load->view('templates/inner_footer', $data);

        }

    }

    public function view_unserviced_for_cron() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Manual Marking of Properties as Unserviced";
        $this->load->view('templates/inner_header', $data);
        $this->load->view('daily/unserviced_for_cron', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function unserviced_for_cron(){
        
        $markProp = $this->daily_model->mark_unserviced_property_for_cron();

        $data['status'] = false;
        $data['countRes'] = $markProp;
        if($markProp > 0){
            $data['status'] = true;
        }
        echo json_encode($data);
    }

    public function view_nsw_act_job_with_tbb() {

        //$per_page = $this->config->item('pagi_per_page');
        $per_page = 100;
        $offset = ($this->input->get_post('offset')!="") ? $this->input->get_post('offset') :0;
        $agency_filter = $this->input->get_post('agency_filter');
        $page_url = '/daily/view_nsw_act_job_with_tbb';

        //main list
        /* comment out by Gherx > rebuild query 
        $data['lists'] = $this->daily_model->get_nsw_act_job_with_tbb($offset, $per_page);

        $query = $this->daily_model->get_nsw_act_job_with_tbb('','');
        $total_rows = $query->num_rows();
        */

        $sel_query = "
        p.`property_id`, 
        p.`address_1` AS p_address1, 
        p.`address_2` AS p_address2, 
        p.`address_3` AS p_address3, 
        p.`state` AS p_state,
        p.`postcode` AS p_postcode, 
        p.`retest_date`,
        p.`test_date`,
        
        a.`agency_id`, 
        a.`agency_name`,
        aght.priority,
        j.`id` AS id,
        DATEDIFF(CURDATE(), p.`retest_date`) AS deadline
        ";

        $next_30_days = date('Y-m-d',strtotime("+30 days"));
        $custom_where = "j.status = 'To Be Booked' AND ( p.`retest_date` != '' AND p.`retest_date` <= '{$next_30_days}' )
        AND a.`franchise_groups_id` != 14";

        $custom_joins = array(
            'join_table' => 'property_services` AS ps',
            'join_on' => '( p.`property_id` = ps.`property_id` AND ps.`service` = 1 AND ps.`alarm_job_type_id` != 6 )', // must be serviced to SATS and excluding CW
            'join_type' => 'inner'
        );
        $custom_where_overdue_30days_tot = "( p.retest_date > NOW() AND p.retest_date < DATE_ADD(NOW(), INTERVAL 30 DAY ) )";

        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'custom_where_arr' => array(
                $custom_where_overdue_30days_tot
            ),
            'p_deleted' => 0,
            'a_status' => 'active',
            'state_filter' => 'NSW',
            'agency_filter' => $agency_filter,
    
            'custom_joins' => $custom_joins,
            'join_table' => array('jobs', 'agency_priority'),
    
            'sort_list' => array(
                array(
                    'order_by' => 'p.`retest_date`',
                    'sort' => 'ASC',
                ),
                array(
                    'order_by' => 'p.`address_2`',
                    'sort' => 'ASC',
                ),
            ),
    
            'limit' => $per_page,
            'offset' => $offset,
            'group_by' => 'p.property_id',
            'display_query' => 0
        );
        $data['lists'] = $this->properties_model->get_properties($params);

        //Get all rows
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
            'agency_filter' => $agency_filter,
            'group_by' => 'p.property_id',
            'display_query' => 0
        );
        $query = $this->properties_model->get_properties($params);
        $total_rows = $query->num_rows();

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        //agency filter
        $sel_query = "DISTINCT(a.agency_id), a.agency_name";
        $params_agency_filter = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'custom_joins' => $custom_joins,
            'join_table' => array('jobs'),
            'p_deleted' => 0,
            'a_status' => 'active',
            'state_filter' => 'NSW',
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['agency_filter'] = $this->properties_model->get_properties($params_agency_filter);

        //get 30 days overdue
        $custom_where_overdue_30days_tot = "( p.retest_date > NOW() AND p.retest_date < DATE_ADD(NOW(), INTERVAL 30 DAY ) )";
        $params_overdue_30days_tot = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'custom_joins' => $custom_joins,
            'join_table' => array('jobs'),
            'p_deleted' => 0,
            'a_status' => 'active',
            'state_filter' => 'NSW',
            'agency_filter' => $agency_filter,
            'group_by' => 'p.property_id',
            'display_query' => 0,
            'custom_where_arr' => array(
                $custom_where_overdue_30days_tot
            ),
        );
        //$data['overdue_30days_tot'] = $this->properties_model->get_properties($params_overdue_30days_tot)->row()->overdue_count;
        $data['overdue_30days_tot'] = $this->properties_model->get_properties($params_overdue_30days_tot)->num_rows();
        
        // pagination settings
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = "/daily/view_nsw_act_job_with_tbb";
        $config['first_link'] = 'First';
        $config['last_link'] = 'Last';

        $this->pagination->initialize($config);
        
        $data['pagination'] = $this->pagination->create_links();
        
        // pagination count
        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Urgent NSW Jobs";
        $this->load->view('templates/inner_header', $data);
        $this->load->view('daily/view_nsw_act_job_with_tbb', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function action_required_jobs(){

        $this->load->model('jobs_model'); //load job model
        $page_url = '/daily/action_required_jobs';

        $phrase = $this->input->get_post('search_filter');
        $job_type = $this->input->get_post('job_type_filter');
        $service = $this->input->get_post('service_filter');
        $state =$this->input->get_post('state_filter');
        $date_filter = $this->input->get_post('date_filter');
        $agency_filter = $this->input->get_post('agency_filter');
        $date = ( $this->system_model->isDateNotEmpty($date_filter) ) ? $this->system_model->formatDate($date_filter) : NULL ;
        $job_status = 'Action Required';

        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $sel_query = " 
			*,
			j.`id` AS jid,
			j.`status` AS jstatus,
			j.`service` AS jservice,
			j.`created` AS jcreated,
			j.`date` AS jdate,
			j.`comments` AS j_comments,
			
			p.`address_1` AS p_address_1, 
			p.`address_2` AS p_address_2, 
			p.`address_3` AS p_address_3,
			p.`state` AS p_state,
			p.`postcode` AS p_postcode,
			p.`comments` AS p_comments,	
			
			a.`agency_id` AS a_id,
			a.`phone` AS a_phone,
			a.`address_1` AS a_address_1, 
			a.`address_2` AS a_address_2, 
			a.`address_3` AS a_address_3,
			a.`state` AS a_state,
			a.`postcode` AS a_postcode,
			a.`trust_account_software`,
			a.`tas_connected`,
			
			jr.`name` AS jr_name,
				
			sa.`FirstName`,
			sa.`LastName`,
			
			aua.`agency_user_account_id`,
			aua.`fname` AS pm_fname, 
			aua.`lname` AS pm_lname,
			aua.`email` AS pm_email,
			
			ajt.`id` AS ajt_id,
			ajt.`type` AS ajt_type
		";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $this->config->item('country'),
            'job_status' => $job_status,

            'job_type' => $job_type,
            'service_filter' => $service,
            'state_filter' => $state,
            'date' => $date,
            'search' => $phrase,
            'agency_filter' => $agency_filter,

            'join_table' => array('job_reason','staff_accounts','alarm_job_type','agency_user_accounts'),

            'limit' => $per_page,
            'offset' => $offset

        );
        $data['lists'] = $this->jobs_model->get_jobs($params);

        //Total Rows
        $sel_query = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $this->config->item('country'),
            'job_status' => $job_status,

            'job_type' => $job_type,
            'service_filter' => $service,
            'state_filter' => $state,
            'date' => $date,
            'search' => $phrase,
            'agency_filter' => $agency_filter,
        );
        $query =  $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        //job type filter
        $sel_query = "DISTINCT(j.`job_type`),`j.job_type`";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $this->config->item('country'),
            'job_status' => $job_status,
            'sort_list' => array(
                array(
                    'order_by' => 'j.`job_type`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['job_type_filter_json'] = json_encode($params);

        //Agency name filter
        $sel_query = "DISTINCT(a.`agency_id`),
        a.`agency_name`";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $this->config->item('country'),
            'job_status' => $job_status,
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['agency_filter_json'] = json_encode($params);

        //Services Filter
        $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $this->config->item('country'),
            'job_status' => $job_status,
            'join_table' => array('alarm_job_type'),
            'sort_list' => array(
                array(
                    'order_by' => 'j.`service`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['service_filter_json'] = json_encode($params);

        // state filter
		$sel_query = "DISTINCT(p.`state`)";
		$params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $this->config->item('country'),
            'job_status' => $job_status,
            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                ),
            ),
		);
		$data['state_filter_json'] = json_encode($params);	

        //http build query
        $pagi_links_params_arr = array(
            'job_type_filter' => $job_type,
            'service_filter' => $service,
            'state_filter' => $state,
            'date_filter' => $date,
            'search_filter' => $phrase
        );
        $pagi_link_params = '/daily/action_required_jobs/?'.http_build_query($pagi_links_params_arr);

        // pagination settings
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $pagi_link_params;

        $this->pagination->initialize($config);
        
        $data['pagination'] = $this->pagination->create_links();
        
        // pagination count
        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Action Required";
        $this->load->view('templates/inner_header', $data);
        $this->load->view('daily/action_required_jobs', $data);
        $this->load->view('templates/inner_footer', $data);

    }

    public function ajax_edit_action_required_jobs_comments(){
        $jsondata['status'] = false;
        $job_id = $this->input->post('job_id');
        $comments = $this->input->post('comments');

        if($job_id && !empty($job_id) && is_numeric($job_id)){

            $update_data = array(
                'comments' => $comments
            );
            $this->db->where('id', $job_id);
            $this->db->update('jobs', $update_data);
            $this->db->limit(1);
            if($this->db->affected_rows()>0){

                //insert log
                $log_params = array(
                    'title' => 48,  // Action Required Update
                    'details' => $comments,
                    'display_in_vjd' => 1,
                    'created_by_staff' => $this->session->staff_id,
                    'job_id' => $job_id
                );
                $this->system_model->insert_log($log_params);

                //set status to true
                $jsondata['status'] = true;

            }
        }

        echo json_encode($jsondata);
    }

    public function incorrectly_upgraded_properties(){

        if(COUNTRY==2){ //not applicable in NZ
            show_404();
        }

        $base_url = '/daily/incorrectly_upgraded_properties';
        $country_id = $this->config->item('country');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');
        $offset2 = $this->input->get_post('offset2');

        /* disable for now adjust below (gherx)
        $custom_where = "j.job_type = 'IC Upgrade' AND p.qld_new_leg_alarm_num >0 AND p.state='QLD'";
        */
        
        /*
        $custom_where = "(ps.`service` = 1 AND (ps.`alarm_job_type_id` != 12 AND ps.`alarm_job_type_id` != 13 AND ps.`alarm_job_type_id` != 14 AND ps.`alarm_job_type_id` != 11 AND ps.`alarm_job_type_id` != 6)) 
        AND ((j.job_type = 'IC Upgrade' AND j.status = 'Completed') OR (p.qld_new_leg_alarm_num = 0 OR p.prop_upgraded_to_ic_sa = 1) )"; 
        */

        // custom where no qld_new_leg_alarm_num
        $custom_where = "(ps.`service` = 1 AND (ps.`alarm_job_type_id` != 12 AND ps.`alarm_job_type_id` != 13 AND ps.`alarm_job_type_id` != 14 AND ps.`alarm_job_type_id` != 11 AND ps.`alarm_job_type_id` != 6 AND ps.`alarm_job_type_id` != 20)) 
        AND ((j.job_type = 'IC Upgrade' AND j.status = 'Completed') OR p.prop_upgraded_to_ic_sa = 1 )
        AND p.is_sales!=1 AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )";

        $sel_query = "
        j.id as j_id,
        j.job_type,
        j.status as j_status,
        j.`job_type` AS j_type,
        j.`service` AS j_service,
        p.property_id AS p_property_id,
        p.address_1 AS p_address_1,
        p.address_2 AS p_address_2,
        p.address_3 AS p_address_3,
        p.state AS p_state,
        p.postcode AS p_postcode,
        p.is_sales,
        a.agency_id,
        a.agency_name,
        aght.priority,
        ajt.`type` AS ajt_type
        ";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            //'job_status' => 'Completed',
            'exclude_job_type' => 1,
            'state_filter' => 'QLD',
            'custom_where' => $custom_where,
            'join_table' => array('job_type','alarm_job_type','agency_priority'),
            'custom_joins' => array(
                'join_table' => 'property_services as ps',
                'join_on' => 'p.`property_id` = ps.`property_id`',
                'join_type' => 'INNER'
            ),
            'limit' => $per_page,
            'offset' => $offset,
            'sort_list' => array(
                array(
                    'order_by' => 'p.address_2',
                    'sort' => 'ASC',
                ),
            ),
            'group_by' => 'p.property_id',
            'display_query' => 0
        );
        $data['lists'] = $this->jobs_model->get_jobs($params);
        $data['sql_query'] = $this->db->last_query();

        //Total Rows
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
            'page' => $base_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        // pagination
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $base_url;

        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();

        //pagi count
        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Upgrade Data Discrepancy";
        $this->load->view('templates/inner_header', $data);
        $this->load->view('daily/incorrectly_upgraded_properties', $data);
        $this->load->view('templates/inner_footer', $data);

    }

    //ajax recheck unserviced same as as cron (unservice_mark_properties)
    public function ajax_recheck_unserviced(){

        $jsondata['status'] = false;

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons, cron_mark_unservice"
        ]);
        $crm_row = $crm_sql->row();

        if( $crm_row->disable_all_crons == 0 && $crm_row->cron_mark_unservice == 1 ){

            $this->load->model('daily_model');

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');

            $this->daily_model->mark_unserviced_property_for_cron();

            $jsondata['status'] = true;
            $jsondata['status_msg'] = "Recheck Unserviced Success";

        }else{
            $jsondata['status'] = false;
            $jsondata['status_msg'] = "Error: Please contact admin or check cron settings";
        }

        echo json_encode($jsondata);

    }

    public function overdue_nsw_jobs() {

        //$per_page = $this->config->item('pagi_per_page');
        $per_page = 100;
        $offset = ($this->input->get_post('offset')!="") ? $this->input->get_post('offset') :0;
        $agency_filter = $this->input->get_post('agency_filter');
        $uri = '/daily/overdue_nsw_jobs';
        $data['uri'] = $uri;

        //region filter
        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);
        $show_is_eo = $this->input->get_post('show_is_eo');

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }    

        $export = $this->input->get_post('export');
        $overdue_nsw_jobs_filter_date = $this->input->get_post('overdue_nsw_jobs_filter_date');
        $overdue_nsw_jobs_filter_time = ltrim($this->input->get_post('overdue_nsw_jobs_filter_time'));

        $order_by = ( $this->input->get_post('order_by') !='' )?$this->input->get_post('order_by'):'j.date';
        $sort = ( $this->input->get_post('sort') !='' )?$this->input->get_post('sort'):'asc';
        $filter_orderby_columns = $this->input->get_post('order_by');

        //main list
        /* comment out by Gherx > rebuild query 
        $data['lists'] = $this->daily_model->get_nsw_act_job_with_tbb($offset, $per_page);

        $query = $this->daily_model->get_nsw_act_job_with_tbb('','');
        $total_rows = $query->num_rows();
        */

        $sel_query = "
            p.`property_id`, 
            p.`address_1` AS p_address1, 
            p.`address_2` AS p_address2, 
            p.`address_3` AS p_address3, 
            p.`state` AS p_state,
            p.`postcode` AS p_postcode, 
            p.`retest_date`,
            p.`test_date`,
            j.`is_eo`,
            j.`job_type` AS j_type,
            j.`preferred_time`,
            
            a.`agency_id`, 
            a.`agency_name`,
            j.`id` AS id,
            DATEDIFF(Date(p.`retest_date`), CURDATE()) AS deadline
        ";

        $today = date('Y-m-d');
        $next_30_days = date('Y-m-d',strtotime("+30 days"));
        $custom_where = "j.del_job = 0 AND j.status = 'To Be Booked' AND ( p.`retest_date` != '' AND p.`retest_date` <= '{$next_30_days}' )
        AND a.`franchise_groups_id` != 14";

        $custom_joins = array(
            'join_table' => 'property_services` AS ps',
            'join_on' => '( p.`property_id` = ps.`property_id` AND ps.`service` = 1 AND ps.`alarm_job_type_id` != 6 )', // must be serviced to SATS and excluding CW
            'join_type' => 'inner'
        );
        $custom_where_overdue_tot = "p.retest_date <= CURDATE() AND (CAST(p.postpone_due_job AS DATE) <= '{$today}' OR p.postpone_due_job IS NULL)";

        $custom_where_overdue_nsw_jobs_filter_time = $overdue_nsw_jobs_filter_time != "" ? "j.`preferred_time` LIKE '%$overdue_nsw_jobs_filter_time%'" : "";

        if ($filter_orderby_columns == 'retest_date') {
            $sort_list = array(
                'order_by' => 'p.`retest_date`',
                'sort' => $sort,
            );
        } else if($filter_orderby_columns == 'preferred_time') {
            $sort_list = array(
                'order_by' => 'j.`preferred_time`',
                'sort' => $sort,
            );
        } else {
            $sort_list = array(
                'order_by' => 'p.`retest_date`',
                'sort' => 'ASC',
            );
        }

        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'custom_where_arr' => array(
                $custom_where_overdue_tot, $custom_where_overdue_nsw_jobs_filter_time
            ),
            'p_deleted' => 0,
            'a_status' => 'active',
            'state_filter' => 'NSW',
            'agency_filter' => $agency_filter,
            'postcodes'=> $postcodes,
            'is_eo' => $show_is_eo,
            'custom_joins' => $custom_joins,
            'join_table' => array('jobs'),
            'sort_list' => array(
                $sort_list
            ),            
            'group_by' => 'p.property_id',
            'display_query' => 0
        );

        if( $export != 1 ){
            $params['limit'] = $per_page;
            $params['offset'] = $offset;
        }
        // echo "<pre>";
        $lists = $this->properties_model->get_properties($params)->result_array();
        // echo $this->db->last_query();
        // exit;


        if (count($lists) > 0) {

            $property_id_arr = [];
            foreach($lists as $job_row) {
                $property_id_arr[] = $job_row['property_id'];
            }

            $property_id_arr_unique = array_unique($property_id_arr);
            if( count($property_id_arr_unique) > 0 ){
                $property_id_arr_unique_imp = implode(",",$property_id_arr_unique);
            }
            
            $recent_job_sql = $this->daily_model->get_recent_non_completed_job_per_property($property_id_arr_unique_imp);

            foreach ($lists as &$job) {
                foreach ($recent_job_sql as $recent_job_row) {
                    if ($job['property_id'] == $recent_job_row['property_id']) {
                        $job['recent_job'] = $recent_job_row;
                        break;
                    }
                }
            }

        }

        if ($export == 1) { //EXPORT         
            
            
            // file name
            $date_export = date('YmdHis');
            $filename = "overdue_nsw_jobs_{$date_export}.csv";

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            // file creation 
            $csv_file = fopen('php://output', 'w');            

            $header = array('Deadline','Retest Date','Property Address','Agency','Active Job Status','Active Job Age','Preferred Time');

            fputcsv($csv_file, $header);
            
            foreach ( $lists as $u ){ 

                $csv_row = [];          
                
                //$recent_job_sql = $this->daily_model->get_recent_created_job($u['property_id']);
                //$recent_job_sql_row = $recent_job_sql->row_array();

                $recent_job_sql_row = $u['recent_job'];

                $created =  $recent_job_sql_row['jcreated'];
                $date1 = date_create(date('Y-m-d', strtotime($created)));
                $date2 = date_create(date('Y-m-d'));
                $diff = date_diff($date1, $date2);
                $age = $diff->format("%r%a");

                if( !empty($recent_job_sql_row) ){
                    $age_val = (((int) $age) != 0) ? $age : 0;
                }else{
                    $age_val = NULL;
                }

                $csv_row[] = ( $u['deadline'] > 0 )?$u['deadline']:'+'.($u['deadline']*-1);
                $csv_row[] = ($this->system_model->isDateNotEmpty($u['retest_date']) == true) ? $this->system_model->formatDate($u['retest_date'], 'd/m/Y') : NULL;
                $csv_row[] = "{$u['p_address1']} {$u['p_address2']}, {$u['p_address3']} {$u['p_state']} {$u['p_postcode']}";
                $csv_row[] = $u['agency_name'];
                $csv_row[] = $recent_job_sql_row['jstatus'];
                $csv_row[] = $age_val;
                $csv_row[] = $u['preferred_time'];
                
                fputcsv($csv_file,$csv_row);               

            }
        
            fclose($csv_file); 
            exit;
             
            
        }else{                        

            $data['lists'] = $lists;

            //Get all rows
            //$sel_query = "COUNT(p.`property_id`) AS pcount";
            $sel_query = "p.`property_id";
            $params = array(
                'sel_query' => $sel_query,
                'custom_where' => $custom_where,
                'custom_where_arr' => array(
                    $custom_where_overdue_tot, $custom_where_overdue_nsw_jobs_filter_time
                ),
                'custom_joins' => $custom_joins,
                'join_table' => array('jobs'),
                'p_deleted' => 0,
                'a_status' => 'active',
                'state_filter' => 'NSW',
                'agency_filter' => $agency_filter,
                'postcodes'=> $postcodes,
                'is_eo' => $show_is_eo,
                'group_by' => 'p.property_id',
                'display_query' => 0
            );
            $query = $this->properties_model->get_properties($params);
            $total_rows = $query->num_rows();

            // update page total
            $page_tot_params = array(
                'page' => $uri,
                'total' => $total_rows
            );
            $this->system_model->update_page_total($page_tot_params);

            //agency filter
            $sel_query = "DISTINCT(a.agency_id), a.agency_name";
            $params_agency_filter = array(
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
                'sort_list' => array(
                    array(
                        'order_by' => 'a.`agency_name`',
                        'sort' => 'ASC',
                    )
                ),
                'display_query' => 0
            );
            $data['agency_filter'] = $this->properties_model->get_properties($params_agency_filter);

            // Region Filter ( get distinct state )
            $sel_query = "DISTINCT(p.`state`)";
            $region_filter_arr = array(
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
                'sort_list' => array(
                    array(
                        'order_by' => 'a.`agency_name`',
                        'sort' => 'ASC',
                    )
                ),
                'display_query' => 0
            );
            $data['region_filter_json'] = json_encode($region_filter_arr);

            //get overdue total
        // $sel_query_overdue_tot = "COUNT(p.`property_id`) AS overdue_count";
            $sel_query_overdue_tot = "p.`property_id";
            $custom_where_overdue_tot = "p.retest_date <= NOW()";
            $params_overdue_tot = array(
                'sel_query' => $sel_query_overdue_tot,
                'custom_where' => $custom_where,
                'custom_joins' => $custom_joins,
                'join_table' => array('jobs'),
                'p_deleted' => 0,
                'a_status' => 'active',
                'state_filter' => 'NSW',
                'agency_filter' => $agency_filter,
                'group_by' => 'p.property_id',
                'display_query' => 0,
                'custom_where_arr' => array(
                    $custom_where_overdue_tot
                ),
            );
            //$data['overdue_tot'] = $this->properties_model->get_properties($params_overdue_tot)->row()->overdue_count;
            $data['overdue_tot'] = $this->properties_model->get_properties($params_overdue_tot)->num_rows();

            // pagination
            $pagi_links_params_arr = array(            
                'agency_filter' => $this->input->get_post('agency_filter'),
                'state_ms' => $this->input->get_post('state_ms'),
                'region_ms' => $this->input->get_post('region_ms'),
                'sub_region_ms' => $this->input->get_post('sub_region_ms'),
                'show_is_eo' => $this->input->get_post('show_is_eo'),
                'overdue_nsw_jobs_filter_date'  => $this->input->post('overdue_nsw_jobs_filter_date'),
                'overdue_nsw_jobs_filter_time'  => $this->input->post('overdue_nsw_jobs_filter_time')
            );
            $pagi_link_params = "{$uri}/?".http_build_query($pagi_links_params_arr);
            $data['pagi_links_params_arr'] = $pagi_links_params_arr;

            // export link
            $data['export_link'] = "{$uri}/?export=1&".http_build_query($pagi_links_params_arr);

            // pagination settings
            $config['page_query_string'] = TRUE;
            $config['query_string_segment'] = 'offset';
            $config['total_rows'] = $total_rows;
            $config['per_page'] = $per_page;
            $config['base_url'] = $pagi_link_params;

            $this->pagination->initialize($config);
            
            $data['pagination'] = $this->pagination->create_links();

            $filters_arr = array(
                'agency_filter' => $agency_filter,
                'job_type_filter' => $job_filter,
                'service_filter' => $service_filter
            );

            // header sort paramerts needs to exclude sort variables
            $data['header_link_params'] = $filters_arr;

            // append sort variables
            $filters_arr['order_by'] = $this->input->get_post('order_by');
            $filters_arr['sort'] = $this->input->get_post('sort') ? 'asc' : 'desc';
            
            // pagination count
            $pc_params = array(
                'total_rows' => $total_rows,
                'offset' => $offset,
                'per_page' => $per_page
            );
            $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

            // sort
            $data['order_by'] = $order_by;
            $data['sort'] = $sort; 

            $data['toggle_sort'] = ( $sort == 'asc' ) ? 'desc' : 'asc';

            $data['start_load_time'] = microtime(true);
            $data['title'] = "Overdue NSW Jobs";
            $this->load->view('templates/inner_header', $data);
            $this->load->view($uri, $data);
            $this->load->view('templates/inner_footer', $data);
            
        }
        
    }


    public function overdue_jobs() {

        //$per_page = $this->config->item('pagi_per_page');
        $per_page = 100;        
        $offset = ($this->input->get_post('offset')!="") ? $this->input->get_post('offset') :0;
        $agency_filter = $this->input->get_post('agency_filter');
        $state_filter = $this->input->get_post('state_filter');
        $page_url = '/daily/overdue_jobs';
        $data['uri'] = $page_url;

        //region filter
        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);
        $show_is_eo = $this->input->get_post('show_is_eo');

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }    

        //main list
        /* comment out by Gherx > rebuild query 
        $data['lists'] = $this->daily_model->get_nsw_act_job_with_tbb($offset, $per_page);

        $query = $this->daily_model->get_nsw_act_job_with_tbb('','');
        $total_rows = $query->num_rows();
        */

        // comment dev
        // add filter retest date != 1521-03-16 and 1521-03-17
        // in order not to showing that in the overdue jobs

        $sel_query = "
        p.`property_id`, 
        p.`address_1` AS p_address1, 
        p.`address_2` AS p_address2, 
        p.`address_3` AS p_address3, 
        p.`state` AS p_state,
        p.`postcode` AS p_postcode, 
        p.`retest_date`,
        p.`test_date`,
        j.`is_eo`,
        j.`job_type` AS j_type,
        
        a.`agency_id`, 
        a.`agency_name`,
        aght.priority,
        j.`id` AS id,
        DATEDIFF(Date(p.`retest_date`), CURDATE()) AS deadline
        ";

        $today = date('Y-m-d');
        $next_30_days = date('Y-m-d',strtotime("+30 days"));
        $custom_where = "j.del_job = 0 AND j.status = 'To Be Booked' AND ( p.`retest_date` != '' AND p.`retest_date` <= '{$next_30_days}' AND p.`retest_date` != '1521-03-16' AND p.`retest_date` != '1521-03-17' )
        AND a.`franchise_groups_id` != 14";

        $custom_joins = array(
            'join_table' => 'property_services` AS ps',
            'join_on' => '( p.`property_id` = ps.`property_id` AND ps.`service` = 1 AND ps.`alarm_job_type_id` != 6 )', // must be serviced to SATS and excluding CW
            'join_type' => 'inner'
        );
        $custom_where_overdue_tot = "p.retest_date <= CURDATE() AND (CAST(p.postpone_due_job AS DATE) <= '{$today}' OR p.postpone_due_job IS NULL)";

        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'custom_where_arr' => array(
                $custom_where_overdue_tot
            ),
            'p_deleted' => 0,
            'a_status' => 'active',            
            'agency_filter' => $agency_filter,
            'state_filter' => $state_filter,
            'postcodes'=> $postcodes,
            'is_eo' => $show_is_eo,
    
            'custom_joins' => $custom_joins,
            'join_table' => array('jobs', 'agency_priority'),
    
            'sort_list' => array(
                array(
                    'order_by' => 'p.`retest_date`',
                    'sort' => 'ASC',
                ),
                array(
                    'order_by' => 'p.`address_2`',
                    'sort' => 'ASC',
                ),
            ),
    
            'limit' => $per_page,
            'offset' => $offset,
            'group_by' => 'p.property_id',
            'display_query' => 0
        );
        $data['lists'] = $this->properties_model->get_properties($params);

        //Get all rows
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
            'agency_filter' => $agency_filter,
            'state_filter' => $state_filter,
            'postcodes'=> $postcodes,
            'is_eo' => $show_is_eo,
            'group_by' => 'p.property_id',
            'display_query' => 0
        );
        $query = $this->properties_model->get_properties($params);
        $total_rows = $query->num_rows();

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);


        //agency filter
        $sel_query = "DISTINCT(a.agency_id), a.agency_name";
        $params_agency_filter = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'custom_where_arr' => array(
                $custom_where_overdue_tot
            ),
            'custom_joins' => $custom_joins,
            'join_table' => array('jobs'),
            'p_deleted' => 0,
            'a_status' => 'active',            
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['agency_filter'] = $this->properties_model->get_properties($params_agency_filter);

        //agency filter
        $sel_query = "DISTINCT(p.state)";
        $params_agency_filter = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'custom_where_arr' => array(
                $custom_where_overdue_tot
            ),
            'custom_joins' => $custom_joins,
            'join_table' => array('jobs'),
            'p_deleted' => 0,
            'a_status' => 'active',            
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['state_filter_sql'] = $this->properties_model->get_properties($params_agency_filter);

        // Region Filter ( get distinct state )
        $sel_query = "DISTINCT(p.`state`)";
        $region_filter_arr = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'custom_where_arr' => array(
                $custom_where_overdue_tot
            ),
            'custom_joins' => $custom_joins,
            'join_table' => array('jobs'),
            'p_deleted' => 0,
            'a_status' => 'active',            
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['region_filter_json'] = json_encode($region_filter_arr);

        //get overdue total
       // $sel_query_overdue_tot = "COUNT(p.`property_id`) AS overdue_count";
        $sel_query_overdue_tot = "p.`property_id";
        $custom_where_overdue_tot = "p.retest_date <= NOW()";
        $params_overdue_tot = array(
            'sel_query' => $sel_query_overdue_tot,
            'custom_where' => $custom_where,
            'custom_joins' => $custom_joins,                                                                                                                                                                                                                                                                                                                                                                                                                                                             
            'join_table' => array('jobs'),
            'p_deleted' => 0,
            'a_status' => 'active',            
            'agency_filter' => $agency_filter,
            'group_by' => 'p.property_id',
            'display_query' => 0,
            'custom_where_arr' => array(
                $custom_where_overdue_tot
            ),
        );
        //$data['overdue_tot'] = $this->properties_model->get_properties($params_overdue_tot)->row()->overdue_count;
        $data['overdue_tot'] = $this->properties_model->get_properties($params_overdue_tot)->num_rows();

        $pagi_links_params_arr = array(
            'agency_filter' => $agency_filter,
            'state_filter' => $state_filter
        );
        $pagi_link_params = "{$page_url}/?".http_build_query($pagi_links_params_arr);

        // pagination settings
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $pagi_link_params;

        $this->pagination->initialize($config);
        
        $data['pagination'] = $this->pagination->create_links();
        
        // pagination count
        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Overdue Jobs";
        $this->load->view('templates/inner_header', $data);
        $this->load->view($page_url, $data);
        $this->load->view('templates/inner_footer', $data);
    }


    public function ajax_snooze(){
        $agay = $this->input->post('agay');
        $snooze_reason = $this->input->post('snooze_reason');
        $snooze_days = 15;

        //update property postpone_due_job
        foreach($agay as $agay_row){
            $json_dec = json_decode($agay_row);
            $prop_id = $json_dec->prop_id;
            $job_id = $json_dec->job_id;

            if($prop_id!="" && $job_id!=""){ //check job id > not empty
                //update property
                $postpone_due_job_date = date('Y-m-d H:i:s', strtotime("+ {$snooze_days} days"));
                $this->db->where('property_id', $prop_id);
                $this->db->set('postpone_due_job', $postpone_due_job_date);
                $this->db->update('property');
                //update property end

                //get property active jobs
                $active_job_query = $this->daily_model->get_properties_active_jobs_for_overdue_nsw_jobs($prop_id);

                //insert job log
                $log_details = "Due date postponed for <strong>{$snooze_days}</strong> days because <strong>{$snooze_reason},</strong> affected jobs:({$active_job_query}).";
                $log_params = array(
                    'title' => 68,  //Snooze day
                    'details' => $log_details,
                    'display_in_vpd' => 1,
                    'created_by_staff' => $this->session->staff_id,
                    'property_id' => $prop_id
                );
                $this->system_model->insert_log($log_params);
            }
        }
       
    }

    public function postponed_overdue_jobs(){

        //$per_page = $this->config->item('pagi_per_page');
        $per_page = 100;
        $offset = ($this->input->get_post('offset')!="") ? $this->input->get_post('offset') :0;
        $agency_filter = $this->input->get_post('agency_filter');

        $sel_query = "
        p.`property_id`, 
        p.`address_1` AS p_address1, 
        p.`address_2` AS p_address2, 
        p.`address_3` AS p_address3, 
        p.`state` AS p_state,
        p.`postcode` AS p_postcode, 
        p.`retest_date`,
        p.`test_date`,
        
        a.`agency_id`, 
        a.`agency_name`,
        aght.priority,
        j.`id` AS id,
        DATEDIFF(CURDATE(), p.`retest_date`) AS deadline
        ";

        $today = date('Y-m-d');
        $next_30_days = date('Y-m-d',strtotime("+30 days"));
        $custom_where = "j.del_job = 0 AND j.status = 'To Be Booked' AND ( p.`retest_date` != '' AND p.`retest_date` <= '{$next_30_days}' )
        AND a.`franchise_groups_id` != 14";

        $custom_joins = array(
            'join_table' => 'property_services` AS ps',
            'join_on' => '( p.`property_id` = ps.`property_id` AND ps.`service` = 1 AND ps.`alarm_job_type_id` != 6 )', // must be serviced to SATS and excluding CW
            'join_type' => 'inner'
        );
        $custom_where_overdue_tot = "p.retest_date <= CURDATE() AND (CAST(p.postpone_due_job AS DATE) > '{$today}' AND p.postpone_due_job IS NOT NULL)";

        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'custom_where_arr' => array(
                $custom_where_overdue_tot
            ),
            'p_deleted' => 0,
            'a_status' => 'active',
            'agency_filter' => $agency_filter,
    
            'custom_joins' => $custom_joins,
            'join_table' => array('jobs','agency_priority'),
    
            'sort_list' => array(
                array(
                    'order_by' => 'p.`retest_date`',
                    'sort' => 'ASC',
                ),
                array(
                    'order_by' => 'p.`address_2`',
                    'sort' => 'ASC',
                ),
            ),
    
            'limit' => $per_page,
            'offset' => $offset,
            'group_by' => 'p.property_id',
            'display_query' => 0
        );
        $data['lists'] = $this->properties_model->get_properties($params);

        //Get all rows
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
            'agency_filter' => $agency_filter,
            'group_by' => 'p.property_id',
            'display_query' => 0
        );
        $query = $this->properties_model->get_properties($params);
        $total_rows = $query->num_rows();

        //agency filter
        $sel_query = "DISTINCT(a.agency_id), a.agency_name";
        $params_agency_filter = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'custom_where_arr' => array(
                $custom_where_overdue_tot
            ),
            'custom_joins' => $custom_joins,
            'join_table' => array('jobs'),
            'p_deleted' => 0,
            'a_status' => 'active',
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['agency_filter'] = $this->properties_model->get_properties($params_agency_filter);


        // pagination settings
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = "/daily/postponed_overdue_jobs";

        $this->pagination->initialize($config);
        
        $data['pagination'] = $this->pagination->create_links();
        
        // pagination count
        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Postponed Overdue Jobs";
        $this->load->view('templates/inner_header', $data);
        $this->load->view('daily/postponed_overdue_jobs', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function overdue_other_jobs(){

        //$per_page = $this->config->item('pagi_per_page');
        $per_page = 100;
        $offset = ($this->input->get_post('offset')!="") ? $this->input->get_post('offset') :0;
        $agency_filter = $this->input->get_post('agency_filter');
        $page_url = '/daily/overdue_nsw_jobs';

        //region filter
        $state_filter = $this->input->get_post('state_filter');
        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);
        $show_is_eo = $this->input->get_post('show_is_eo');

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }    

        $sel_query = "
        p.`property_id`, 
        p.`address_1` AS p_address1, 
        p.`address_2` AS p_address2, 
        p.`address_3` AS p_address3, 
        p.`state` AS p_state,
        p.`postcode` AS p_postcode, 
        p.`retest_date`,
        p.`test_date`,
        j.`is_eo`,
        j.`job_type` AS j_type,
        
        a.`agency_id`, 
        a.`agency_name`,
        j.`id` AS id,
        DATEDIFF(CURDATE(), p.`retest_date`) AS deadline
        ";

        $today = date('Y-m-d');
        $next_30_days = date('Y-m-d',strtotime("+30 days"));
        $custom_where = "j.del_job = 0 AND j.status = 'To Be Booked' AND ( p.`retest_date` != '' AND p.`retest_date` <= '{$next_30_days}' )
        AND a.`franchise_groups_id` != 14
        AND p.state != 'NSW'";

        $custom_joins = array(
            'join_table' => 'property_services` AS ps',
            'join_on' => '( p.`property_id` = ps.`property_id` AND ps.`service` = 1 AND ps.`alarm_job_type_id` != 6 )', // must be serviced to SATS and excluding CW
            'join_type' => 'inner'
        );
        $custom_where_overdue_tot = "p.retest_date <= CURDATE() AND (CAST(p.postpone_due_job AS DATE) <= '{$today}' OR p.postpone_due_job IS NULL)";

        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'custom_where_arr' => array(
                $custom_where_overdue_tot
            ),
            'p_deleted' => 0,
            'a_status' => 'active',
            'state_filter' => $state_filter,
            'agency_filter' => $agency_filter,
            'postcodes'=> $postcodes,
            'is_eo' => $show_is_eo,
    
            'custom_joins' => $custom_joins,
            'join_table' => array('jobs'),
    
            'sort_list' => array(
                array(
                    'order_by' => 'p.`retest_date`',
                    'sort' => 'ASC',
                ),
                array(
                    'order_by' => 'p.`address_2`',
                    'sort' => 'ASC',
                ),
            ),
    
            'limit' => $per_page,
            'offset' => $offset,
            'group_by' => 'p.property_id',
            'display_query' => 0
        );
        if($this->input->get_post('search')==1 || $this->input->post('submitFilter')){ //process when search submit pressed
            $data['lists'] = $this->properties_model->get_properties($params);
        }

        //Get all rows
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
            'state_filter' => $state_filter,
            'agency_filter' => $agency_filter,
            'postcodes'=> $postcodes,
            'is_eo' => $show_is_eo,
            'group_by' => 'p.property_id',
            'display_query' => 0
        );
        $query = $this->properties_model->get_properties($params);
        $total_rows = $query->num_rows();

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

         //state filter
         $sel_query = "DISTINCT(p.state), p.state";
         $params_agency_filter = array(
             'sel_query' => $sel_query,
             'custom_where' => $custom_where,
             'custom_where_arr' => array(
                 $custom_where_overdue_tot
             ),
             'custom_joins' => $custom_joins,
             'join_table' => array('jobs'),
             'p_deleted' => 0,
             'a_status' => 'active',
             'sort_list' => array(
                 array(
                     'order_by' => 'p.`state`',
                     'sort' => 'ASC',
                 )
             ),
             'display_query' => 0
         );
         $data['state_filter'] = $this->properties_model->get_properties($params_agency_filter);

        //agency filter
        $sel_query = "DISTINCT(a.agency_id), a.agency_name";
        $params_agency_filter = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'custom_where_arr' => array(
                $custom_where_overdue_tot
            ),
            'custom_joins' => $custom_joins,
            'join_table' => array('jobs'),
            'p_deleted' => 0,
            'a_status' => 'active',
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['agency_filter'] = $this->properties_model->get_properties($params_agency_filter);

        // Region Filter ( get distinct state )
        $sel_query = "DISTINCT(p.`state`)";
        $region_filter_arr = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'custom_where_arr' => array(
                $custom_where_overdue_tot
            ),
            'custom_joins' => $custom_joins,
            'join_table' => array('jobs'),
            'p_deleted' => 0,
            'a_status' => 'active',
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['region_filter_json'] = json_encode($region_filter_arr);

        //get overdue total
       // $sel_query_overdue_tot = "COUNT(p.`property_id`) AS overdue_count";
        $sel_query_overdue_tot = "p.`property_id";
        $custom_where_overdue_tot = "p.retest_date <= NOW()";
        $params_overdue_tot = array(
            'sel_query' => $sel_query_overdue_tot,
            'custom_where' => $custom_where,
            'custom_joins' => $custom_joins,
            'join_table' => array('jobs'),
            'p_deleted' => 0,
            'a_status' => 'active',
            'agency_filter' => $agency_filter,
            'group_by' => 'p.property_id',
            'display_query' => 0,
            'custom_where_arr' => array(
                $custom_where_overdue_tot
            ),
        );
        //$data['overdue_tot'] = $this->properties_model->get_properties($params_overdue_tot)->row()->overdue_count;
        $data['overdue_tot'] = $this->properties_model->get_properties($params_overdue_tot)->num_rows();

        //http build query
        $pagi_links_params_arr = array(
            'search'=> $this->input->get_post('search'),
            'state_filter' => $state_filter,
            'agency_filter' => $agency_filter,
            'sub_region_ms' => $sub_region_ms
        );
        $pagi_link_params = '/daily/overdue_other_jobs/?'.http_build_query($pagi_links_params_arr);
        
        // pagination settings
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $pagi_link_params;

        $this->pagination->initialize($config);
        
        $data['pagination'] = $this->pagination->create_links();
        
        // pagination count
        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

        $data['title'] = "Overdue Other Jobs";
        $data['start_load_time'] = microtime(true);
        $this->load->view('templates/inner_header', $data);
        $this->load->view('daily/overdue_other_jobs', $data);
        $this->load->view('templates/inner_footer', $data);

    }



    public function active_properties_without_jobs()
    {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Active Properties Without Jobs";
        $uri = '/daily/active_properties_without_jobs';
        $data['uri'] = $uri;
        $country_id = $this->config->item('country');

        $per_page = $this->config->item('pagi_per_page');
        $offset = (int) $this->input->get_post('offset');

        $agency_filter = $this->input->get_post('agency_filter') != null ? $this->input->get_post('agency_filter') : null ; 

        $export = $this->input->get_post('export');

        //agency filter
        $query = "
            SELECT DISTINCT
                a_main.`agency_id`,
                a_main.`agency_name`
            FROM `property` AS p_main
            LEFT JOIN  `property_services` AS ps_main ON p_main.`property_id` = ps_main.`property_id` 
            LEFT JOIN `agency` AS a_main ON p_main.`agency_id` = a_main.`agency_id`
            LEFT JOIN `agency_priority` as aght ON a_main.`agency_id` = aght.`agency_id`
            WHERE p_main.`property_id` NOT IN(
                SELECT DISTINCT(p.`property_id`)
                FROM `jobs` AS j
                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`	
                WHERE p.`deleted` = 0
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
        $data['agency_filter_result'] = $this->db->query($query)->result();
            
        if ($export == 1) {

			// file name
			$date_export = date('YmdHis');
			$filename = "active_properties_without_jobs_{$date_export}.csv";

			header("Content-type: application/csv");
			header("Content-Disposition: attachment; filename={$filename}");
			header("Pragma: no-cache");
			header("Expires: 0");

			//file creation 
			$csv_file = fopen('php://output', 'w');
			$header = array('Property Address','Agency');

            fputcsv($csv_file, $header);

            $result = $this->daily_model->get_active_properties_without_jobs($country_id, $agency_filter, $offset, $per_page);

            foreach($result as $v) {
				$csv_row = [];
                $priority = ($v->priority > 0) ? '('.$v->abbreviation.')' : null;

				$csv_row[] = $v->address_1 . " " . $v->address_2 . ", " . $v->address_3 . " " . $v->state . " " . $v->postcode;
				$csv_row[] = "{$v->agency_name} {$priority}";

                fputcsv($csv_file,$csv_row);
			}           

            fclose($csv_file);
            exit;

        } else {
            if (!empty($agency_filter)) {
                // get paginated agency filter
                $data['property_sql'] = $this->daily_model->get_active_properties_without_jobs($country_id, $agency_filter, $offset, $per_page);
    
                // total row
                $property_sql = $this->daily_model->get_total_rows_property_count($country_id, $agency_filter);
                $total_rows = $property_sql->p_count;
            } else {
                // get paginated list                
                $data['property_sql'] = $this->daily_model->get_active_properties_without_jobs($country_id, $agency_filter, $offset, $per_page);

                // total row
                $property_sql = $this->daily_model->get_total_rows_property_count($country_id, $agency_filter);
                $total_rows = $property_sql->p_count;
            }
            $data['sql_query'] = $this->db->last_query(); //Show query on About

            $pagi_links_params_arr = array(
                'agency_filter' => $this->input->get_post('agency_filter'),
            );
            $pagi_link_params = "{$uri}?" . http_build_query($pagi_links_params_arr);

            $data['export_link'] = "{$uri}/?export=1&".http_build_query($pagi_links_params_arr);
        
            // pagination
            $config['page_query_string'] = TRUE;
            $config['query_string_segment'] = 'offset';
            $config['total_rows'] = $total_rows;
            $config['per_page'] = $per_page;
            //$config['base_url'] = $pagi_link_params;

            $this->pagination->initialize($config);

            $data['pagination'] = $this->pagination->create_links();

            $pc_params = array(
                'total_rows' => $total_rows,
                'offset' => $offset,
                'per_page' => $per_page
            );
            $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);
            $this->load->view('templates/inner_header', $data);
            $this->load->view($uri, $data);
            $this->load->view('templates/inner_footer', $data);
        }
       
        
        
    }

    public function missed_jobs(){

        $state_filter = $this->input->get_post('state_filter'); 
        $job_type_filter = $this->input->get_post('job_type_filter'); 
        $agency_filter = $this->input->get_post('agency_filter'); 
            
        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');
        $page_url = '/daily/missed_jobs';

        $country_id = $this->config->item('country');
        $custom_where = null;

        $job_status = "Booked";

        $sel_query = "
        j.`id`, j.`job_type`, 
        j.`status`,
        j.`date`,
        j.`property_id` , 
        j.`service` AS jservice, 

        p.`address_1`, 
        p.`address_2`, 
        p.`address_3, 
        p.`state`, 
        p.`deleted`, 
    
        a.`agency_name`, 
        a.`agency_id`,
        aght.priority,
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type
        ";
        
        $params = array(
            'sel_query' => $sel_query,
            'state_filter' => $state_filter,
            'job_type_filter' => $job_type_filter,
            'agency_filter' => $agency_filter,
            'limit' =>  $per_page,
            'offset' => $offset,
            'display_query' => 0
        );
        $data['lists'] = $this->daily_model->getMissedJobs($params);

        $data['last_query'] = $this->db->last_query(); 

        // all rows
        $sel_query = "COUNT(j.id) as jcount";
        $total_params = array(
            'sel_query' => $sel_query,
            'state_filter' => $state_filter,
            'job_type_filter' => $job_type_filter,
            'agency_filter' => $agency_filter,
        );
        $query = $this->daily_model->getMissedJobs($total_params);
        $total_rows = $query->num_rows();

        //State filter
        $sel_query = "DISTINCT(p.`state`),
        p.`state`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['state_filter_json'] = json_encode($params);


        //Job type Filter
        $sel_query = "DISTINCT(j.`job_type`),
         `j.job_type`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type'),
            'sort_list' => array(
                array(
                    'order_by' => 'j.`job_type`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['job_type_filter_json'] = json_encode($params);

        //Agency Filter
        $sel_query = "DISTINCT(a.`agency_id`), a.`agency_name`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type'),
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['agency_filter_json'] = json_encode($params);


        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        $pagi_links_params_arr = array(
            'state_filter' => $state_filter,
            'job_type_filter' => $job_type_filter,
            'agency_filter' => $agency_filter,
        );
        $pagi_link_params = '/daily/missed_jobs/?'.http_build_query($pagi_links_params_arr);

        // header sort paramerts needs to exclude sort variables
        $data['header_link_params'] = $pagi_links_params_arr;

        // append sort variables
        $filters_arr['order_by'] = $this->input->get_post('order_by');
        $filters_arr['sort'] = $this->input->get_post('sort');

        // pagination settings
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $pagi_link_params;

        $this->pagination->initialize($config);
        
        $data['pagination'] = $this->pagination->create_links();
        
        // pagination count
        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

        // sort
        $data['order_by'] = $order_by;
        $data['sort'] = $sort;

        $data['toggle_sort'] = ( $sort == 'asc' )?'desc':'asc';

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Missed Jobs";
        $this->load->view('templates/inner_header', $data);
        $this->load->view('jobs/jobs_didnot_rebooked', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function save_last_contact_comments(){

        $job_id = $this->input->post('job_id');
        $comments = $this->input->post('comments');
        $today = date('Y-m-d H:i:s');

        if( $job_id > 0 ){

            $lcc_sql = $this->db->query("
            SELECT COUNT(`id`) AS lcc_count
            FROM `last_contact_comments`
            WHERE `job_id` = {$job_id}
            ");
            $lcc_row = $lcc_sql->row();

            if( $lcc_row->lcc_count > 0 ){ // exist, update

                $update_data = array(
                    'comments' => $comments,
                    'last_update_date' => $today
                );
                
                $this->db->where('job_id', $job_id);
                $this->db->update('last_contact_comments', $update_data);

            }else{ // new, insert

                $insert_data = array(
                    'job_id' => $job_id,
                    'comments' => $comments,
                    'created_date' => $today,
                    'last_update_date' => $today
                );
                
                $this->db->insert('last_contact_comments', $insert_data);

            }

        }        
        
    }

}

?>
