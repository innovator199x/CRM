<?php

class Jobs extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('jobs_model');
        $this->load->model('properties_model');
        $this->load->model('inc/functions_model');
        $this->load->model('sms_model');
        $this->load->library('pagination');
        $this->load->helper('url');
        $this->load->model('daily_model');
    }

    public function index()
    {
        $data['start_load_time'] = microtime(true);
        
        $data['title'] = "All Jobs";

        $country_id = $this->config->item('country');

        $agency_filter = $this->input->get_post('agency_filter');
        $job_type_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $date_filter = ( $this->input->get_post('date_filter') !='' )?$this->system_model->formatDate($this->input->get_post('date_filter')):null;
        $search = $this->input->get_post('search');
        $search_submit = $this->input->get_post('search_submit');

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }       
        
        $order_by = ( $this->input->get_post('order_by') != "" )?$this->input->get_post('order_by'):'j.job_type';
        $sort = ( $this->input->get_post('sort') != "" )?$this->input->get_post('sort'):'ASC';

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $sel_query = "
            j.`id` AS jid,
            j.`status` AS j_status,
            j.`service` AS j_service,
            j.`date` AS j_date,
            j.`job_price` AS j_price,
            j.`job_type` AS j_type,
            j.`urgent_job`,
            j.`job_reason_id`,
            
            p.`property_id` AS prop_id, 
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            p.`comments` AS p_comments, 
            p.`deleted` AS p_deleted,
            
            a.`agency_id` AS a_id,
            a.`agency_name` AS agency_name,
            a.`phone` AS a_phone,
            a.`address_1` AS a_address_1, 
            a.`address_2` AS a_address_2, 
            a.`address_3` AS a_address_3,
            a.`state` AS a_state,
            a.`postcode` AS a_postcode,
            aght.priority,
            apmd.abbreviation,
            
            jt.`abbrv`,
            
            ajt.`id` AS ajt_id,
            ajt.`type` AS ajt_type
        ";

        
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
        
            'agency_filter' => $agency_filter,
            'job_type' => $job_type_filter,
            'service_filter' => $service_filter,
            'date' => $date_filter,
            'search' => $search,
            'postcodes' => $postcodes,
            
            'country_id' => $country_id,
            
            'join_table' => array('job_type','alarm_job_type','agency_priority', 'agency_priority_marker_definition'),

            'limit' => $per_page,
            'offset' => $offset,

            'sort_list' => array(   
                array(
                    'order_by' => 'j.`urgent_job`',
                    'sort' => 'DESC'
                ),
                array(
                    'order_by' => $order_by,
                    'sort' => $sort
                )
            ),          
            'display_query' => 0
        );

        $data['sql_query'] = '';
        if( $search_submit == 'Search' ){
            $data['lists'] = $this->jobs_model->get_jobs($params);
            $data['sql_query'] = $this->db->last_query(); //Show query on About
        }
       
        
        // total row
        $sel_query = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
        
            'agency_filter' => $agency_filter,
            'job_type' => $job_type_filter,
            'service_filter' => $service_filter,
            'date' => $date_filter,
            'search' => $search,
            'postcodes' => $postcodes,
            
            'country_id' => $country_id,
            
            'join_table' => array('job_type','alarm_job_type'),         
            'display_query' => 0
        );       
        if( $search_submit == 'Search' ){
            $query = $this->jobs_model->get_jobs($params);
            $total_rows = $query->row()->jcount;  
        }
           
        
        //Agency filter
        $sel_query = "DISTINCT(a.`agency_id`), a.`agency_name`";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,
            'sort_list' => array(   
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                )               
            )
        );
        $data['agency_filter_json'] = json_encode($params);
        
        //Job type Filter
        $sel_query = "DISTINCT(j.`job_type`), `j.job_type`";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,
            'sort_list' => array(
                array(
                    'order_by' => 'j.`job_type`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['job_type_filter_json'] = json_encode($params);
        
        //Services Filter
        $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,
            'join_table' => array('alarm_job_type'),
            'sort_list' => array(
                array(
                    'order_by' => 'ajt.`type`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['service_filter_json'] = json_encode($params);
        
        // Region Filter ( get distinct state )
        $sel_query = "DISTINCT(p.`state`)";
        $region_filter_arr = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type'),

            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );       
        $data['region_filter_json'] = json_encode($region_filter_arr);
    
        $pagi_links_params_arr = array(
            'agency_filter' => $agency_filter,
            'job_type_filter' => $job_type_filter,
            'service_filter' => $service_filter,
            'date_filter' => $date_filter,
            'search' => $search,
            'sub_region_ms' => $sub_region_ms,
            'search_submit' => $search_submit
        );
        $pagi_link_params = '/jobs/index/?'.http_build_query($pagi_links_params_arr);

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

        $this->load->view('templates/inner_header', $data);
        $this->load->view('jobs/all_jobs', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    
    /**
     * Allocate Jobs
     */
    public function allocate()
    {   
        $this->load->model('staff_accounts_model');
        $data['start_load_time'] = microtime(true);
        $page_url = '/jobs/allocate';
        $data['title'] = "Allocate";

        $job_status = 'Allocate';

        $country_id = $this->config->item('country');

        $agency_filter = $this->input->get_post('agency_filter');
        $jobType = $this->input->get_post('job_type_filter');
        $search = $this->input->get_post('search_filter');
        $allocated_by_filter = $this->input->get_post('allocated_by_filter');
        $added_by_filter = $this->input->get_post('added_by_filter');

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        } 
        
        $state_filter = $this->input->get_post('state_filter');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');
        $export = $this->input->get_post('export');

        $sel_query = "
        j.`id` AS jid,
        j.`status` AS j_status,
        j.`service` AS j_service,
        j.`created` AS j_created,
        j.`date` AS j_date,
        j.`comments` AS j_comments,
        j.`job_price` AS j_price,
        j.`job_type` AS j_type,
        j.`allocate_notes`,
        j.`allocate_response`,
        j.`allocated_by`,
        j.`allocate_timestamp`,
        j.`allocate_opt`,
        
        p.`property_id` AS prop_id, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3,
        p.`state` AS p_state,
        p.`postcode` AS p_postcode,
        p.`comments` AS p_comments, 
        
        a.`agency_id` AS a_id,
        a.`agency_name` AS agency_name,
        a.`phone` AS a_phone,
        a.`address_1` AS a_address_1, 
        a.`address_2` AS a_address_2, 
        a.`address_3` AS a_address_3,
        a.`state` AS a_state,
        a.`postcode` AS a_postcode,
        a.`trust_account_software`,
        a.`tas_connected`,
        
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type,

        alloc_by.`FirstName` AS alloc_by_fname, 
        alloc_by.`LastName` AS alloc_by_lname
        ";

        if( $allocated_by_filter != '' ){
            // Hide jobs from
            $custom_where_arr[] = "( j.`allocated_by` != {$allocated_by_filter} OR j.`allocated_by` IS NULL )"; 
        }

        if( $added_by_filter != '' ){
            $custom_where_arr[] = "alloc_by.StaffID = {$added_by_filter}"; 
        }
       

        $params = array(
            'sel_query' => $sel_query,
            'custom_where_arr' => $custom_where_arr,

            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' =>$job_status,
            'state_filter' => $state_filter,
            'join_table' => array('job_type','alarm_job_type','allocated_by_join'),
            
            'agency_filter' => $agency_filter,
            'job_type' => $jobType,
            'postcodes' => $postcodes,
            'search' => $search,

            'sort_list' => array(
                array(
                    'order_by' => 'a.agency_name',
                    'sort' => 'ASC',
                ),
            ),
            'display_query' => 0
        );

        if( $export == 1 ){
            $allocate_sql = $this->jobs_model->get_jobs($params);

            // file name
			$filename = 'allocate_export'.date('YmdHis').rand().'.csv';

			header("Content-type: application/csv");
			header("Content-Disposition: attachment; filename={$filename}");
			header("Pragma: no-cache");
			header("Expires: 0");

			// file creation
			$file = fopen('php://output', 'w');

			// csv header
			$csv_header = []; // clear
			$csv_header = array( 'Date', 'Time', 'Added By', 'Age', 'Job Type', 'Property Address', 'Region', 'Sub Region', 'Deadline', 'Notes', 'Response');
			fputcsv($file, $csv_header);

			// csv row
			foreach ( $allocate_sql->result_array() as $row ) {

				$csv_row = [];
                $date = ($this->system_model->isDateNotEmpty($row['allocate_timestamp']))?date('d/m/Y', strtotime($row['allocate_timestamp'])):""; 
                $time = ($this->system_model->isDateNotEmpty($row['allocate_timestamp']))?date('H:i', strtotime($row['allocate_timestamp'])):""; 
                $name = $this->system_model->formatStaffName($row['alloc_by_fname'],$row['alloc_by_lname']);
                $prop_address = $row['p_address_1']." ".$row['p_address_2'].", ".$row['p_address_3'];
                $getRegion = $this->system_model->getRegion_v2($row['p_postcode']);
                $current_timeday = date('Y-m-d H:i:s');
                $deadline = $this->gherxlib->getAllocateDeadLine($row['allocate_opt'],$row['allocate_timestamp']);
				$csv_row = array(

                    $date,
                    $time,
                    "$name",
                    $this->gherxlib->getAge($row['j_created']),
                    $row['j_type'],
                    "{$prop_address}",
                    $getRegion->row()->region_name,
                    $getRegion->row()->subregion_name,
                    "$deadline",
                    $row['allocate_notes'],
                    "{$row['allocate_response']}"
				);

				fputcsv($file, $csv_row);

			}

			fclose($file);

        } else {

            $params['limit'] = $per_page;
			$params['offset'] = $offset;

            $data['lists'] = $this->jobs_model->get_jobs($params);

            // all rows
            $sel_query = "COUNT(j.`id`) AS jcount";
            $params = array(
                'sel_query' => $sel_query,
                'custom_where_arr' => $custom_where_arr,

                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'job_status' =>$job_status,
                'state_filter' => $state_filter,
                'join_table' => array('job_type','alarm_job_type','allocated_by_join'),

                'agency_filter' => $agency_filter,
                'job_type' => $jobType,
                'postcodes' => $postcodes,
                'search' => $search,
            );
            $query = $this->jobs_model->get_jobs($params);
            $total_rows = $query->row()->jcount;

            ##get staff accounts exclude 5,6 and 8 classIds
            $custom_where_staff = "sa.ClassID NOT IN(5,6,8)";
            $get_staff_accounts_params = array(
                'sel_query' => "sa.StaffID, sa.FirstName, sa.LastName",
                'joins' => array('country_access'),
                'country_id' => COUNTRY,
                'custom_where' => $custom_where_staff,
                'sort_list' => array(
                    array(
                        'order_by' => 'sa.`FirstName`',
                        'sort' => 'ASC'
                    ),
                    array(
                        'order_by' => 'sa.`LastName`',
                        'sort' => 'ASC'
                    )
                ),
                'active' => 1,
                'deleted' => 0,
                'display_query' => 0
            );
            $data['staff_to_notify'] = $this->staff_accounts_model->get_staff_accounts($get_staff_accounts_params);


            // update page total
            $page_tot_params = array(
                'page' => $page_url,
                'total' => $total_rows
            );
            $this->system_model->update_page_total($page_tot_params);


            //Agency name filter
            $sel_query = "DISTINCT(a.`agency_id`),
            a.`agency_name`";
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'job_status' =>$job_status,
                'state_filter' => $state_filter,
                'distinct' => 'a.`agency_id`',

                'sort_list' => array(
                    array(
                        'order_by' => 'a.`agency_name`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['agency_filter_json'] = json_encode($params);

            //Job type Filter
            $sel_query = "DISTINCT(j.`job_type`),
            `j.job_type`";
            $params = array(
            'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'job_status' =>$job_status,
                'state_filter' => $state_filter,
                'join_table' => array('job_type'),

                'sort_list' => array(
                    array(
                        'order_by' => 'j.`job_type`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['job_type_filter_json'] = json_encode($params);


            // allocated by
            $sel_query = "DISTINCT(j.`allocated_by`), 
            alloc_by.`FirstName`, 
            alloc_by.`LastName`
            ";
            $params = array(
            'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'job_status' =>$job_status,
                'state_filter' => $state_filter,
                'join_table' => array('job_type','allocated_by_join'),

                'sort_list' => array(
                    array(
                        'order_by' => 'j.`job_type`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['allocated_by_filter_json'] = json_encode($params);


            // Region Filter ( get distinct state )
            $sel_query = "p.`state`";
            $region_filter_arr = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'job_status' =>$job_status,
                'state_filter' => $state_filter,

                'sort_list' => array(
                    array(
                        'order_by' => 'p.`state`',
                        'sort' => 'ASC',
                    )
                ),
                'group_by' => 'p.`state`',
                'display_query' => 0
            );
            $data['region_filter_json'] = json_encode($region_filter_arr);


            // state filter
            $sel_query = "p.`state`";
            $params = array(
                'sel_query' => $sel_query,
                'del_job' => 0,
                'p_deleted' => 0,
                'a_status' => 'active',         
                'country_id' => $country_id,
                
                'job_status' => $job_status,
                
                'join_table' => array('job_type','alarm_job_type','staff_accounts'),
                'sort_list' => array(
                    array(
                        'order_by' => 'p.`state`',
                        'sort' => 'ASC',
                    )
                ),
                'group_by' => 'p.`state`',
                'display_query' => 0
            );
            $data['state_filter_json'] = json_encode($params);  


            $pagi_links_params_arr = array(
                'agency_filter' => $agency_filter,
                'job_type_filter' => $jobType,
                'added_by_filter' => $added_by_filter,
                'search_filter' => $search,
                'sub_region_ms' => $sub_region_ms
            );
            $pagi_link_params = '/jobs/allocate/?'.http_build_query($pagi_links_params_arr);


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


            $this->load->view('templates/inner_header', $data);
            $this->load->view('jobs/allocate', $data);
            $this->load->view('templates/inner_footer', $data);
        }
    }



    /**
     * Update Global Settings Allocate Personel
     */
    public function ajax_update_allocate_personnel(){

        $data['status'] = false;
        $staff_id = $this->input->post('staff_id');

        if(!empty($staff_id)){
            $staff_id_imp = implode(",",$staff_id);
            $data = array(
                'allocate_personnel' => $staff_id_imp,
                'allocate_personnel_updated_by' => $this->session->staff_id
            );
            $this->db->where('country_id', $this->config->item('country'));
            $this->db->update('global_settings',$data);
            $this->db->limit(1);
        
            $data['status'] = true;
        }

        echo json_encode($data);
        
    }

    /**
     * Update/add Allocate response
     */
    public function ajax_update_allocate_response(){
        $response = $this->input->post('response');
        $job_id = $this->input->post('job_id');
        $allocated_by = $this->input->post('allocated_by');

        //Get old response > used for log details
        $allocate_response_sql = $this->db->select('allocate_response')->from('jobs')->where('id',$job_id)->get();
        $allocate_response_row = $allocate_response_sql->row_array();

        $data = array(
            'allocate_response' => $response,
            'allocate_timestamp' => date('Y-m-d H:i:s'),
            'allocated_by' => $allocated_by
        );
        $this->db->where('id', $job_id);
        $this->db->update('jobs',$data);
        $this->db->limit(1);
        if($this->db->affected_rows()>0){
            
            //Insert log
            $log_details = "Allocate response updated from: <strong>{$allocate_response_row['allocate_response']}</strong> to <strong>{$response}</strong>";
            $log_params = array(
                'title' => 63, // Job Update
                'details' => $log_details,
                'display_in_vjd' => 1,
                'created_by_staff' => $this->session->staff_id,
                'job_id' => $job_id
            );
            $this->system_model->insert_log($log_params);

            //insert notifications
           /* 
            $globalParams = array('country_id'=>$this->config->item('country_id'));
            $globalSettings = $this->gherxlib->getGlobalSettings($globalParams)->row();
            $gs_allocate_personnel = $this->gherxlib->formatStaffName($globalSettings->FirstName,$globalSettings->LastName); 
            */

            $tt = $this->gherxlib->getGlobalSettings_personnel();
            $tt_personnel = $this->gherxlib->formatStaffName($tt['FirstName'],$tt['LastName']);
            
            $notf_msg = "{$tt_personnel} has responded to <a href='{$this->config->item('crmci_link')}/jobs/allocate'>Allocate</a> job <a href='{$this->config->item('crm_link')}/view_job_details.php?id={$job_id}'> #{$job_id}</a>";
            
            $notf_type = 1; // General Notifications
            $params = array(
                'notf_type'=> $notf_type,
                'staff_id'=> $allocated_by,
                'country_id'=> $this->config->item('country'),
                'notf_msg'=> $notf_msg
            );
            $this->gherxlib->insertNewNotification($params);

            // pusher notification
            $options = array(
                'cluster' => $this->config->item('PUSHER_CLUSTER'),
                'useTLS' => true
            );
            $pusher = new Pusher\Pusher(
                $this->config->item('PUSHER_KEY'),
                $this->config->item('PUSHER_SECRET'),
                $this->config->item('PUSHER_APP_ID'),
                $options
            );
           
            $pusher_data['notif_type'] = $notf_type;
            $ch = "ch".$allocated_by;
            $ev = "ev01";
            $out = $pusher->trigger($ch, $ev, $pusher_data);

            $data['status'] = true;

        }
        echo json_encode($data);
    }

    /**
     * Display Booked Jobs
     */
    public function booked()
    {

        $data['start_load_time'] = microtime(true);

        $data['title'] = "Booked Jobs";

        $job_status = 'Booked';

        $country_id = $this->config->item('country');

        $job_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $state_filter = $this->input->get_post('state_filter');
        $agency_filter = $this->input->get_post('agency_filter');
        $date_filter = ($this->input->get_post('date_filter')!="")?$this->system_model->formatDate($this->input->get_post('date_filter')):NULL;
        $date_filter_from = ($this->input->get_post('date_filter_from')!="")?$this->system_model->formatDate($this->input->get_post('date_filter_from')):NULL;
        $date_filter_to = ($this->input->get_post('date_filter_to')!="")?$this->system_model->formatDate($this->input->get_post('date_filter_to')):NULL;
        $search = $this->input->get_post('search_filter');
        $tech_filter = $this->input->get_post('tech_filter');

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);
        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }  
        
        // pagination
        $per_page = $this->config->item('pagi_per_page');
        //$per_page = 5;
        $offset = $this->input->get_post('offset');

        $sel_query = "
        j.`id` AS jid,
        j.`status` AS j_status,
        j.`service` AS j_service,
        j.`created` AS j_created,
        j.`date` AS j_date,
        j.`comments` AS j_comments,
        j.`job_price` AS j_price,
        j.`job_type` AS j_type,
        j.`key_access_required`,
        
        p.`property_id` AS prop_id, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3,
        p.`state` AS p_state,
        p.`postcode` AS p_postcode,
        p.`comments` AS p_comments, 
        
        a.`agency_id` AS a_id,
        a.`agency_name` AS agency_name,
        a.`phone` AS a_phone,
        a.`address_1` AS a_address_1, 
        a.`address_2` AS a_address_2, 
        a.`address_3` AS a_address_3,
        a.`state` AS a_state,
        a.`postcode` AS a_postcode,
        a.`trust_account_software`,
        a.`tas_connected`,
        aght.priority,
        apmd.abbreviation,
        
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type
        ";

        // paginate
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type','staff_accounts','agency_priority', 'agency_priority_marker_definition'),
            
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'agency_filter' => $agency_filter,
            'region_filter' => $region_filter,
            'date'=>$date_filter,
            'date_from'=>$date_filter_from,
            'date_to'=>$date_filter_to,
            'search' => $search,
            'postcodes' => $postcodes,
            'tech_filter'=> $tech_filter,
            
            'country_id' => $country_id,

            'limit' => $per_page,
            'offset' => $offset,

            'sort_list' => array(
                array(
                    'order_by' => 'j.created',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['lists'] = $this->jobs_model->get_jobs($params);

        // all rows
        $sel_query = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type','staff_accounts'),

            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'agency_filter' => $agency_filter,
            'region_filter' => $region_filter,
            'date' => $date_filter,
            'date_from' => $date_filter_from,
            'date_to'=>$date_filter_to,
            'search' => $search,
            'postcodes' => $postcodes,
            'tech_filter' => $tech_filter
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;
        
        
        //Agency filter
        $sel_query = "DISTINCT(a.`agency_id`), a.`agency_name`";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,
            
            'job_status' => $job_status,
            
            'sort_list' => array(   
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                )               
            ),
            'display_query' => 0
        );
        $data['agency_filter_json'] = json_encode($params);

        //Job type Filter
        $sel_query = "DISTINCT(j.`job_type`), `j.job_type`";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,
            
            'job_status' => $job_status,
            
            'sort_list' => array(
                array(
                    'order_by' => 'j.`job_type`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['job_type_filter_json'] = json_encode($params);

        //Services Filter
        $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,
            
            'job_status' => $job_status,
            
            'join_table' => array('alarm_job_type','staff_accounts'),
            'sort_list' => array(
                array(
                    'order_by' => 'ajt.`type`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['service_filter_json'] = json_encode($params);            

        // state filter
        $sel_query = "p.`state`";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',         
            'country_id' => $country_id,
            
            'job_status' => $job_status,
            
            'join_table' => array('job_type','alarm_job_type','staff_accounts'),
            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                )
            ),
            'group_by' => 'p.`state`',
            'display_query' => 0
        );
        $data['state_filter_json'] = json_encode($params);  
        
        
        // Region Filter ( get distinct state )
        $sel_query = "p.`state`";
        $region_filter_arr = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            
            'job_status' => $job_status,
            
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type','staff_accounts'),

            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                )
            ),
            'group_by' => 'p.`state`',
            'display_query' => 0
        );
        $data['region_filter_json'] = json_encode($region_filter_arr);
        
        //tech filter
        $sel_query = "DISTINCT(j.`assigned_tech`),sa.`StaffID`,sa.`FirstName`,sa.`LastName`";
        $tech_filter_params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            
            'job_status' => $job_status,
            
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type','staff_accounts'),

            'sort_list' => array(
                array(
                    'order_by' => 'sa.`FirstName`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['tech_filter'] = $this->jobs_model->get_jobs($tech_filter_params);
        //print_r($data['tech_filter']);
        //exit();

        $pagi_links_params_arr = array(
            'agency_filter' => $agency_filter,
            'job_type_filter' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' =>  $state_filter,
            'date_filter' => $date_filter,
            'date_filter_from' => $date_filter_from,
            'date_filter_to' => $date_filter_to,
            'search_filter' => $search,
            'sub_region_ms' => $sub_region_ms,
            'tech_filter' => $tech_filter
        );
        $pagi_link_params = '/jobs/booked/?'.http_build_query($pagi_links_params_arr);


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


        $this->load->view('templates/inner_header', $data);
        $this->load->view('jobs/booked', $data);
        $this->load->view('templates/inner_footer', $data);
    }



    /**
     * Display completed Jobs
     */
    public function completed()
    {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Completed";
        $uri = "/jobs/completed";
        $data['uri'] = $uri;

        $job_status = 'Completed';
        $country_id = $this->config->item('country');

        $job_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $state_filter = $this->input->get_post('state_filter');
        $agency_filter = $this->input->get_post('agency_filter');
        $search = $this->input->get_post('search_filter');
        $show_is_eo = $this->input->get_post('show_is_eo');
        $updated_to_240v_rebook = $this->input->get_post('updated_to_240v_rebook');
        $is_sales = $this->input->get_post('is_sales');
        $search_submit = $this->input->get_post('search_submit');

        $dateFrom_field = $this->input->get_post('dateFrom_filter');
        $dateTo_field = $this->input->get_post('dateTo_filter');
        $dateFrom_filter = ( $dateFrom_field !='' )?$this->system_model->formatDate($dateFrom_field):NULL;
        $dateTo_filter = ( $dateTo_field !='' )?$this->system_model->formatDate($dateTo_field):NULL;
        
        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);
        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }  
        $export = $this->input->get_post('export');
       
        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $sel_query = "
            j.`id` AS jid,
            j.`status` AS j_status,
            j.`service` AS j_service,
            j.`created` AS j_created,
            j.`date` AS j_date,
            j.`comments` AS j_comments,
            j.`job_price` AS j_price,
            j.`job_type` AS j_type,
            j.`assigned_tech`,
            j.`tmh_id`,
            j.`tech_comments`,
            
            p.`property_id` AS prop_id, 
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            p.`comments` AS p_comments, 
            p.`landlord_firstname`,
            p.`landlord_lastname`,
            
            a.`agency_id` AS a_id,
            a.`agency_name` AS agency_name,
            a.`phone` AS a_phone,
            a.`address_1` AS a_address_1, 
            a.`address_2` AS a_address_2, 
            a.`address_3` AS a_address_3,
            a.`state` AS a_state,
            a.`postcode` AS a_postcode,
            a.`trust_account_software`,
            a.`tas_connected`,
            aght.priority,
            apmd.abbreviation,
            a.`salesrep`,
            
            ajt.`id` AS ajt_id,
            ajt.`type` AS ajt_type
        ";

        // Filter if no date and exclude tech = other suppliers
        if($dateFrom_field!="" && $dateTo_field!=""){
            $custom_where = "CAST(j.`date` AS Date)  BETWEEN '{$dateFrom_filter}' AND '{$dateTo_filter}' AND `j`.`assigned_tech` != 1";
        }else{
            $custom_where = "`j`.`assigned_tech` != 1";
        }
       
        // tables joined
        $join_table_array = array('job_type','alarm_job_type','agency_priority', 'agency_priority_marker_definition');
        if( $updated_to_240v_rebook == 1 ){
            $join_table_array[] = 'job_markers';
        }

        $params = array(
            'sel_query' => $sel_query,
            //'p_deleted' => 0,
            //'a_status' => 'active',
            //'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => $join_table_array,
            
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'agency_filter' => $agency_filter,
            'custom_where'=> $custom_where,
            'is_eo' => $show_is_eo,
            'is_sales' => $is_sales,
            'search' => $search,
            'postcodes' => $postcodes,
            'a_deleted' => 'no filter',

            'sort_list' => array(
                array(
                    'order_by' => 'j.created',
                    'sort' => 'ASC',
                ),
            ),
            'display_query' => 0
        );

        if( $updated_to_240v_rebook == 1 ){
            $params['updated_to_240v_rebook'] = 1;
        }

        if( $search_submit ){

            if( $export != 1 ){
                $params['limit'] = $per_page;
                $params['offset'] = $offset;
            }

            $data['lists'] = $this->jobs_model->get_jobs($params);
            $data['page_query'] = $this->db->last_query();
        }

        // export
        if ( $export == 1 && $search_submit ) {     
            
            // file name 
            $filename = "completed_jobs_".rand()."_".date('d/m/YHis').".csv";

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            // file creation 
            $csv_file = fopen('php://output', 'w');
            
            $csv_header = array("Invoice No","Invoice Amount","Date","Job Type","Service","Address","Suburb","State","Postcode","Landlord FirstName","Landlord LastName","Tenants","Tech Comments","Agency","Technician","Sales Rep");
            fputcsv($csv_file, $csv_header);
            
            foreach ($data['lists']->result() as $row) { 

                $getAlarmJobType = $this->db->get_where('alarm_job_type',array('id'=>$row->j_service))->row()->type;

                 //get technician
                 if( $row->assigned_tech > 0 ){

                    $tech_params = array(
                        'sel_query'=> "FirstName,LastName",
                        'staffID' => $row->assigned_tech
                    );
                    $technician = $this->system_model->getTech($tech_params)->row();
                    $tech_name = "{$technician->FirstName} {$technician->LastName}";

                }else{

                    $tech_name = '';

                }

                // get invoice number
                if(isset($row->tmh_id) || $row->tmh_id!=NULL)
                {
                    $invoice_num = $row->tmh_id;
                }
                else
                {
                    $invoice_num = $row->jid;
                }

                $grand_total = $this->system_model->price_ex_gst($row->j_price);

                // get alarms
                $a_sql = $this->db->query("
                    SELECT *
                    FROM `alarm`
                    WHERE `job_id`  = $row->jid
                ");
               foreach($a_sql->result_array() as $a)
                {       
                    if($a['new']==1){
                        //$grand_total += $a['alarm_price'];
                        $grand_total += $this->system_model->price_ex_gst($a['alarm_price']);
                    }               
                }

                //get staff 
                $staff_params = array(
                    'sel_query' => "sa.FirstName, sa.LastName",
                    'staff_id' => $row->salesrep
                );
                $staff_query = $this->gherxlib->getStaffInfo($staff_params);
                $staff_row = $staff_query->row_array();

                //TENANT
                $t_params = array(
                    'property_id'=> $row->prop_id,
                    'active'=> 1
                );
                $get_tenants = $this->gherxlib->getNewTenantsData($t_params);
                $tenant_array = array();
                foreach($get_tenants as $tenant_row){
                    $tenant_array[]= "Name: ".$tenant_row->tenant_firstname." ".$tenant_row->tenant_lastname." | PH: ".$tenant_row->tenant_landline." | Mob: ".$tenant_row->tenant_mobile;
                }
                //END TENANT

                $csv_row = [];                
                
                $csv_row[] = $invoice_num;
                $csv_row[] = "$".number_format($grand_total,2);
                $csv_row[] = $this->system_model->formatDate($row->j_date,'d/m/Y');
                $csv_row[] = $this->gherxlib->getJobTypeAbbrv($row->j_type);
                $csv_row[] = $getAlarmJobType;
                $csv_row[] = $row->p_address_1." ".$row->p_address_2;                
                $csv_row[] = $row->p_address_3;
                $csv_row[] = $row->p_state;                
                $csv_row[] = $row->p_postcode;
                $csv_row[] = $row->landlord_firstname;
                $csv_row[] = $row->landlord_lastname; 
                $csv_row[] = implode("\n",$tenant_array);         
                $csv_row[] = $row->tech_comments;
                $csv_row[] = $row->agency_name;
                $csv_row[] = $tech_name;
                $csv_row[] = "{$staff_row['FirstName']} {$staff_row['LastName']}";

                fputcsv($csv_file,$csv_row); 

            }
        
            fclose($csv_file);             
            
        }else{

            // tables joined
            $join_table_array = array('job_type','alarm_job_type');
            if( $updated_to_240v_rebook == 1 ){
                $join_table_array[] = 'job_markers';
            }

            // all rows
            $sel_query = "COUNT(j.`id`) AS jcount";
            $params = array(
                'sel_query' => $sel_query,
                //'p_deleted' => 0,
                //'a_status' => 'active',
                //'del_job' => 0,
                'country_id' => $country_id,
                'job_status' => $job_status,
                'join_table' => $join_table_array,
                
                'job_type' => $job_filter,
                'service_filter' => $service_filter,
                'state_filter' => $state_filter,
                'agency_filter' => $agency_filter,
                'is_eo' => $show_is_eo,
                'is_sales' => $is_sales,
                'custom_where'=> $custom_where,
                'search' => $search,
                'postcodes' => $postcodes
            );

            if( $updated_to_240v_rebook == 1 ){
                $params['updated_to_240v_rebook'] = 1;
            }

            if( $search_submit ){
                $query = $this->jobs_model->get_jobs($params);
                $total_rows = $query->row()->jcount;
            }
            
            
            // agency filter
            $data['agency_filter_sql'] = $this->db->query("
                SELECT `agency_id`, `agency_name`, `auto_renew`
                FROM `agency`
                WHERE `status` = 'active'                     
                ORDER BY `agency_name`      
            ");

            // get job types
            $params = array(             
                'display_query' => 0
            );
            $data['job_type_filter'] = $this->jobs_model->get_job_types($params);      
            
            // service filter
            $data['service_filter_sql'] = $this->db->query("
                SELECT `id`, `type`
                FROM `alarm_job_type`
                WHERE `active` = 1                  
                ORDER BY `type`      
            ");

            // state filter        
            $data['state_filter_sql'] = $this->db->query("
                SELECT `StateID`, `state`
                FROM `states_def`
                WHERE `country_id` = {$country_id}                  
                ORDER BY `state`      
            ");        

            $pagi_links_params_arr = array(
                'agency_filter' => $agency_filter,
                'job_type_filter' => $job_filter,
                'service_filter' => $service_filter,
                'state_filter' =>  $state_filter,
                'dateFrom_filter' => $dateFrom_field,
                'dateTo_filter' => $dateTo_field,
                'search_filter' => $search,
                'sub_region_ms' => $sub_region_ms,
                'show_is_eo' => $show_is_eo,
                'updated_to_240v_rebook' => $updated_to_240v_rebook,
                'is_sales' => $is_sales,
                'search_submit' => $search_submit
            );
            $pagi_link_params = "{$uri}?".http_build_query($pagi_links_params_arr);
            $data['pagi_links_params_arr'] = $pagi_links_params_arr;

            // explort link
            $data['export_link'] = "{$uri}/?export=1&".http_build_query($pagi_links_params_arr);

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

            $this->load->view('templates/inner_header', $data);
            $this->load->view('jobs/completed', $data);
            $this->load->view('templates/inner_footer', $data);

        }
        
    }


    public function invoiced_jobs()
    {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Invoiced Jobs";
        $data['uri'] = '/jobs/invoiced_jobs';

        $job_status = 'Completed';
        $country_id = $this->config->item('country');

        $job_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $state_filter = $this->input->get_post('state_filter');
        $agency_filter = $this->input->get_post('agency_filter');
        $search = $this->input->get_post('search_filter');
        $search_submit = $this->input->get_post('search_submit');
        $export = $this->input->get_post('export');

        $dateFrom_field = $this->input->get_post('dateFrom_filter');
        $dateTo_field = $this->input->get_post('dateTo_filter');
        $dateFrom_filter = ( $dateFrom_field !='' )?$this->system_model->formatDate($dateFrom_field):NULL;
        $dateTo_filter = ( $dateTo_field !='' )?$this->system_model->formatDate($dateTo_field):NULL;
        
        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);
        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }  
       
        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( $this->input->get_post('offset') != '' )?$this->input->get_post('offset'):0;


         // date filter
         $query_filter_arr = [];
         if($dateFrom_field!="" && $dateTo_field!=""){
             $query_filter_arr[] = "AND j.`date`  BETWEEN '{$dateFrom_filter}' AND '{$dateTo_filter}'";
         }
         $query_filter = implode(" ",$query_filter_arr);

        // paginated list : important: when updating update also sql_str_export but removed limit/offset 
        /* >>>>> disable for now used normal query
         $sql_str = "
             SELECT * 
             FROM completed_jobs 
             WHERE `jid` > 0
             {$query_filter}
             ORDER BY `j_date` ASC
             LIMIT {$offset}, {$per_page} 
         ";
         */
        //used normal query instead of view tweak
        $sql_str = "
            SELECT `j`.`id` AS `jid`, `j`.`service` AS `j_service`, `j`.`date` AS `j_date`, `j`.`job_price` AS `j_price`, `j`.`job_type` AS `j_type`, `j`.`invoice_amount`, `p`.`property_id` AS `prop_id`, `p`.`address_1` AS `p_address_1`, `p`.`address_2` AS `p_address_2`, `p`.`address_3` AS `p_address_3`, `p`.`state` AS `p_state`, `p`.`postcode` AS `p_postcode`, `a`.`agency_id` AS `a_id`, `a`.`agency_name` AS `agency_name`, aght.priority,  apmd.abbreviation
            FROM `jobs` AS `j`
            INNER JOIN `property` AS `p` ON j.`property_id` = p.`property_id` 
            INNER JOIN `agency` AS `a` ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `agency_priority` as aght ON a.`agency_id` = aght.`agency_id`
            LEFT JOIN `agency_priority_marker_definition` as apmd ON aght.`priority` = apmd.`priority`
            WHERE `j`.`status` = 'Completed'
            AND (
                j.`assigned_tech` != 1
                OR j.`assigned_tech` IS NULL
            )
            AND CAST(j.`date` AS Date) >= '{$this->config->item('accounts_financial_year')}'
            {$query_filter}
            ORDER BY j.`date` ASC
            LIMIT {$offset}, {$per_page} 
        ";

         //update this query also when updateing sql_str query above but no limit/offset
        /* >>>>> disable for now used normal query
        $sql_str_export = "
            SELECT * 
            FROM completed_jobs 
            WHERE `jid` > 0
            {$query_filter}
            ORDER BY `j_date` ASC
        ";
        */
        $sql_str_export = "
            SELECT `j`.`id` AS `jid`, `j`.`service` AS `j_service`, `j`.`date` AS `j_date`, `j`.`job_price` AS `j_price`, `j`.`job_type` AS `j_type`, `j`.`invoice_amount`, `p`.`property_id` AS `prop_id`, `p`.`address_1` AS `p_address_1`, `p`.`address_2` AS `p_address_2`, `p`.`address_3` AS `p_address_3`, `p`.`state` AS `p_state`, `p`.`postcode` AS `p_postcode`, `a`.`agency_id` AS `a_id`, `a`.`agency_name` AS `agency_name`
            FROM `jobs` AS `j`
            INNER JOIN `property` AS `p` ON j.`property_id` = p.`property_id` 
            INNER JOIN `agency` AS `a` ON p.`agency_id` = a.`agency_id` 
            WHERE `j`.`status` = 'Completed'
            AND (
                j.`assigned_tech` != 1
                OR j.`assigned_tech` IS NULL
            )
            AND CAST(j.`date` AS Date) >= '{$this->config->item('accounts_financial_year')}'
            {$query_filter}
            ORDER BY j.`date` ASC
        ";




        if ($export == 1) { //EXPORT

            $export_sql = $this->db->query($sql_str_export);

            // file name
            $date_export = date('d/m/Y');
            $filename = "Invoiced_jobs_{$date_export}.csv";

            header("Content-Type: text/csv");
            header("Content-Disposition: Attachment; filename={$filename}");
            header("Pragma: no-cache");

            // headers
            $str = "Date,Job Type,Service,Invoice Amount,Address,State,Agency,Job#\n";

            foreach ($export_sql->result_array() as $row) {

                //date
                $date = $this->system_model->formatDate($row['j_date'],'d/m/Y');

                //job type
                $job_type = $this->gherxlib->getJobTypeAbbrv($row['j_type']);

                //service 
                $service = $row['ajt_type'];

                //invoice amount
                $invoice_amount = number_format($this->system_model->price_ex_gst($row['invoice_amount']),2);

                //address
                $prop_address = "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}";

                //state
                $state = $row['p_state'];

                //agency
                $agency = $row['agency_name'];

                //job #
                $job_id = $row['jid'];

                $str .= "{$date},{$job_type},{$service},{$invoice_amount},\"{$prop_address}\",{$state},\"{$agency}\",{$job_id}\n";
            }

            echo $str;

        }else{

            // DISPLAY LISTING FROM MYSQL VIEW
            if( $search_submit == 'Search' ){

                $data['lists'] = $this->db->query($sql_str);

                // date filter
                $query_filter_arr = [];
                if($dateFrom_field!="" && $dateTo_field!=""){
                    $query_filter_arr[] = "AND `date`  BETWEEN '{$dateFrom_filter}' AND '{$dateTo_filter}'";
                }
                $query_filter = implode(" ",$query_filter_arr);
                
                $sql_str_total = "
                    SELECT COUNT(`id` ) AS jcount, SUM(`invoice_amount`) AS invoice_amount_tot 
                    FROM `jobs`
                    WHERE `status` = 'Completed'
                    AND (
                        `assigned_tech` != 1
                        OR `assigned_tech` IS NULL
                    )
                    AND CAST(`date` AS Date) >= '{$this->config->item('accounts_financial_year')}'
                    {$query_filter}
                ";
                $query = $this->db->query($sql_str_total);
                $row = $query->row();
                $total_rows = $row->jcount;  
                $data['invoice_amount_tot'] = $row->invoice_amount_tot;


                /*
                // total row, and total of invoice amount
                $sql_str = "
                    SELECT COUNT(`jid`) AS jcount, SUM(`invoice_amount`) AS invoice_amount_tot 
                    FROM completed_jobs 
                    WHERE `jid` > 0
                    {$query_filter}
                ";
                $query = $this->db->query($sql_str);
                $row = $query->row();
                $total_rows = $row->jcount;
                $data['invoice_amount_tot'] = $row->invoice_amount_tot;
                */


                // pagination link
                $pagi_links_params_arr = array(
                    'agency_filter' => $agency_filter,
                    'job_type_filter' => $job_filter,
                    'service_filter' => $service_filter,
                    'state_filter' =>  $state_filter,
                    'dateFrom_filter' => $dateFrom_field,
                    'dateTo_filter' => $dateTo_field,
                    'search_filter' => $search,
                    'sub_region_ms' => $sub_region_ms,
                    'search_submit' => $search_submit
                );
                $pagi_link_params = $data['uri'].'/?'.http_build_query($pagi_links_params_arr);


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
                        
            }   

            $data['export_link'] = $data['uri'] . '/?export=1&' . http_build_query($pagi_links_params_arr);

            $this->load->view('templates/inner_header', $data);
            $this->load->view($data['uri'], $data);
            $this->load->view('templates/inner_footer', $data);

        }

            

        
    }



    /**
     * Move to merge for completed Jobs
     */
     public function move_to_merge(){
        $data['status'] = false;
        $job_id = $this->input->post('job_id'); //array
        
        foreach($job_id as $row_jobID){
            $datas = array('status' => 'Merged Certificates');
            $updateToMerge = $this->jobs_model->move_to_merge($row_jobID,$datas);
        } 

        if($updateToMerge){
            //insert logs
          /*  $details = "Moved to <strong>Merged Certificates</strong>";
            $params_job_Log3 = array(
                'title' => 27, //Merged Certificates
                'details' => $details,
                'display_in_vpd' => 1,
                'display_in_vjd' => 1,
                'agency_id' => $this->session->agency_id,
                'created_by_staff' => $this->session->aua_id,
                'property_id' => $prop_id[$index],
                'job_id' => $job_id
            );
            $this->system_model->insert_log($params_job_Log3);
            */
                                    
            $data['status'] = true;
        }


         echo json_encode($data);
     }


    /**
     * Display deleted Jobs
     */ 
    public function deleted()
    {

    $data['start_load_time'] = microtime(true);

    $data['title'] = "Deleted Jobs";

    $country_id = $this->config->item('country');

    $job_filter = $this->input->get_post('job_type_filter');
    $service_filter = $this->input->get_post('service_filter');
    $state_filter = $this->input->get_post('state_filter');
    $date_filter = ( $this->input->get_post('date_filter') !='' )?$this->system_model->formatDate($this->input->get_post('date_filter')):NULL;
    $search = $this->input->get_post('search_filter');

    // pagination
    $per_page = $this->config->item('pagi_per_page');
    $offset = $this->input->get_post('offset');

    
    $sel_query = "
    j.`id` AS jid,
    j.`status` AS j_status,
    j.`service` AS j_service,
    j.`created` AS j_created,
    j.`date` AS j_date,
    j.`comments` AS j_comments,
    j.`job_price` AS j_price,
    j.`job_type` AS j_type,
    
    p.`property_id` AS prop_id, 
    p.`address_1` AS p_address_1, 
    p.`address_2` AS p_address_2, 
    p.`address_3` AS p_address_3,
    p.`state` AS p_state,
    p.`postcode` AS p_postcode,
    p.`comments` AS p_comments, 
    
    a.`agency_id` AS a_id,
    a.`agency_name` AS agency_name,
    a.`phone` AS a_phone,
    a.`address_1` AS a_address_1, 
    a.`address_2` AS a_address_2, 
    a.`address_3` AS a_address_3,
    a.`state` AS a_state,
    a.`postcode` AS a_postcode,
    a.`trust_account_software`,
    a.`tas_connected`,
    aght.priority,
    apmd.abbreviation,
    
    ajt.`id` AS ajt_id,
    ajt.`type` AS ajt_type
    ";

    $params = array(
        'sel_query' => $sel_query,
        'p_deleted' => 0,
        'a_status' => 'active',
        'del_job' => 1,
        'country_id' => $country_id,
        'join_table' => array('job_type','alarm_job_type', 'agency_priority', 'agency_priority_marker_definition'),
        
        'job_type' => $job_filter,
        'service_filter' => $service_filter,
        'state_filter' => $state_filter,
        'date'=> $date_filter,
        'search' => $search,

        'limit' => $per_page,
        'offset' => $offset,
        'sort_list' => array(
            array(
                'order_by' => 'j.urgent_job',
                'sort' => 'DESC',
            ),
            array(
                'order_by' => 'j.job_type',
                'sort' => 'ASC',
            ),
            array(
                'order_by' => 'p.address_3',
                'sort' => 'ASC',
            ),
        ),
        'display_query' => 0,
    );
    $data['lists'] = $this->jobs_model->get_jobs($params);

    // all rows
    $sel_query = "COUNT(j.`id`) AS jcount";
    $params = array(
        'sel_query' => $sel_query,
        'p_deleted' => 0,
        'a_status' => 'active',
        'del_job' => 1,
        'country_id' => $country_id,
        'join_table' => array('job_type','alarm_job_type'),
        
        'job_type' => $job_filter,
        'service_filter' => $service_filter,
        'state_filter' => $state_filter,
        'date'=> $date_filter,
        'search' => $search,
    );
    $query = $this->jobs_model->get_jobs($params);
    $total_rows = $query->row()->jcount;

    //Job type Filter
    $sel_query = "DISTINCT(j.`job_type`),
    `j.job_type`";
    $params = array(
    'sel_query' => $sel_query,
    'p_deleted' => 0,
    'a_status' => 'active',
    'del_job' => 1,
    'country_id' => $country_id,
    'join_table' => array('job_type'),
    'sort_list' => array(
        array(
            'order_by' => 'j.`job_type`',
            'sort' => 'ASC',
        ),
    ),
    );
    $data['job_type_filter_json'] = json_encode($params);

     //Services Filter
     $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
     $params = array(
     'sel_query' => $sel_query,
     'p_deleted' => 0,
     'a_status' => 'active',
     'del_job' => 1,
     'country_id' => $country_id,
     'join_table' => array('alarm_job_type'),
     'sort_list' => array(
         array(
             'order_by' => 'ajt.`type`',
             'sort' => 'ASC',
         ),
     ),
     );
     $data['service_filter_json'] = json_encode($params);

      //State filter
      $sel_query = "DISTINCT(p.`state`),
      p.`state`";
      $params = array(
          'sel_query' => $sel_query,
          'p_deleted' => 0,
          'a_status' => 'active',
          'del_job' => 1,
          'country_id' => $country_id,
          'sort_list' => array(
              array(
                  'order_by' => 'p.`state`',
                  'sort' => 'ASC',
              ),
          ),
      );
      $data['state_filter_json'] = json_encode($params);

      $pagi_links_params_arr = array(
        'job_type_filter' => $job_filter,
        'service_filter' => $service_filter,
        'state_filter' => $state_filter,
        'date_filter' => $date_filter,
        'search_filter' => $search
    );
    $pagi_link_params = '/jobs/deleted/?'.http_build_query($pagi_links_params_arr);


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


    $this->load->view('templates/inner_header', $data);
    $this->load->view('jobs/deleted', $data);
    $this->load->view('templates/inner_footer', $data);
    }


    // COT
    public function cot()
    {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "COT JOBS";

        $job_status = "To Be Booked";

        $country_id = $this->config->item('country');

        $agency_filter = $this->input->get_post('agency_filter');
        $job_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $state_filter = $this->input->get_post('state_filter');
        $startDate_filter = ( $this->input->get_post('date_filter') !='' )?$this->system_model->formatDate($this->input->get_post('date_filter')):NULL;
        $search = $this->input->get_post('search_filter');
        
        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }   


        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');
        
        if($this->input->get_post('date')!=""){
            $custom_where = "CAST(j.`start_date` AS Date)  >= '{$startDate_filter}'";
        }else{
            $custom_where = NULL;
        }
        
        $sel_query .= " 
            j.`id` AS jid, 
            j.`job_type`,
            j.`status` AS jstatus,
            j.`service` AS jservice,
            j.`created` AS jcreated,                
            j.`date` AS jdate,
            j.`job_price`,
            j.`start_date`,
            j.`due_date`,
            j.`comments`,
            j.`job_reason_id`,
            j.`job_reason_comment`,
            j.`urgent_job`,
            j.`client_emailed`,
            j.`door_knock`,
            j.`booked_with`,
            j.`sms_sent`,
            j.`assigned_tech`,
            j.`ts_completed`,
            j.`completed_timestamp`,
            j.`time_of_day`,
            j.`work_order`,
            j.`at_myob`,
            j.`no_dates_provided`,
            j.`agency_approve_en`,
            j.`ss_quantity`,
            j.`key_access_required`,
            j.`preferred_time`,
            j.`property_vacant`,
            j.`tech_comments`,
            j.`precomp_jobs_moved_to_booked`,
            j.`sms_sent_no_show`,
            j.`sms_sent_merge`,
            j.`bne_to_call_notes`,
            j.`assigned_tech`,
            
            p.`property_id`,
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3, 
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            
            p.`tenant_firstname1`,
            p.`tenant_lastname1`,
            p.`tenant_firstname2`,
            p.`tenant_lastname2`,
            p.`tenant_firstname3`,
            p.`tenant_lastname3`,
            p.`tenant_firstname4`,
            p.`tenant_lastname4`,
            
            p.`tenant_mob1`,
            p.`tenant_mob2`,
            p.`tenant_mob3`,
            p.`tenant_mob4`,
            
            p.`tenant_ph1`,
            p.`tenant_ph2`,
            p.`tenant_ph3`,
            p.`tenant_ph4`,
            
            p.`tenant_email1`,
            p.`tenant_email2`,
            p.`tenant_email3`,
            p.`tenant_email4`,
            
            p.`comments` AS p_comments,
            p.`holiday_rental`,
            
            p.`prop_upgraded_to_ic_sa`,

            a.`agency_id`,
            a.`agency_name`,
            a.`account_emails`,
            a.`send_emails`,
            a.`allow_dk`,
            a.`phone` AS a_phone,
            a.`auto_renew` AS a_auto_renew,
            a.`franchise_groups_id`,
            
            jr.`name` AS jr_name,
            
            sa.`FirstName`,
            sa.`LastName`,
            sa.`StaffID` AS staff_id
        ";

        $custom_where = " ( j.job_type = 'Change of Tenancy' OR j.job_type = 'Lease Renewal' )";
        $custom_sort = " (CASE WHEN j.due_date IS NULL THEN 1 ELSE 0 END), j.due_date ASC";
        
        // paginate
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'join_table' => array('property','agency','alarm_job_type','job_reason','staff_accounts'),
            'job_status' => $job_status,
            'job_type' => $job_filter,
            'agency_filter' => $agency_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'postcodes' => $postcodes,
            'custom_where' => $custom_where,
            'search' => $search,
            'postcodes' => $postcodes,

            'limit' => $per_page,
            'offset' => $offset,
            
            'custom_sort' => $custom_sort,
            'display_query' => 0
        );
        $data['lists'] = $this->jobs_model->get_jobs($params);

        // all rows
        $sel_query = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('property','agency','alarm_job_type','job_reason','staff_accounts'),
            'agency_filter' => $agency_filter,
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'postcodes' => $postcodes,
            'custom_where' => $custom_where,
            'search' => $search,
            'postcodes' => $postcodes
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;
        
        //Agency filter
        $sel_query = "DISTINCT(a.`agency_id`), a.`agency_name`";
        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type'),
            'custom_where' => $custom_where,
            'custom_sort' => $custom_sort,
            'country_id' => $country_id,
            'sort_list' => array(   
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                )               
            )
        );
        $data['agency_filter_json'] = json_encode($params);

        //Job type Filter
        $sel_query = "DISTINCT(j.`job_type`), `j.job_type`";
        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type'),
            'custom_sort' => $custom_sort,
            'sort_list' => array(
                array(
                    'order_by' => 'j.`job_type`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['job_type_filter_json'] = json_encode($params);

        //Services Filter
        $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type'),
            'custom_sort' => $custom_sort,
            'sort_list' => array(
                array(
                    'order_by' => 'ajt.`type`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['service_filter_json'] = json_encode($params);    
        
        
        //Services Filter
        $sel_query = "DISTINCT(p.`state`)";
        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type'),
            'custom_sort' => $custom_sort,
            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['state_filter_json'] = json_encode($params);

        
        
        // Region Filter ( get distinct state )
        $sel_query = "p.`state`";
        $region_filter_arr = array(
            'sel_query' => $sel_query,
            'custom_where_arr' => array(
                $custom_where
            ),
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'job_status' => $job_status,
            
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type'),

            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                )
            ),
            'group_by' => 'p.`state`',
            'display_query' => 0
        );
        $data['region_filter_json'] = json_encode($region_filter_arr);
        
        
        $pagi_links_params_arr = array(
            'agency_filter' => $agency_filter,
            'job_type_filter' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'date_filter' => $startDate_filter,
            'search_filter' => $search,
            'sub_region_ms' => $sub_region_ms
        );
        $pagi_link_params = '/jobs/cot/?'.http_build_query($pagi_links_params_arr);


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

        // pass data
        $data['job_status'] = $job_status;

        $this->load->view('templates/inner_header', $data);
        $this->load->view('jobs/cot', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    /**
     * Display DHA Jobs
     */
    public function dha()
    {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "DHA";
        $page_url = '/jobs/dha';

        $job_status = 'DHA';

        $country_id = $this->config->item('country');

        $job_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $state_filter = $this->input->get_post('state_filter');
        $date_filter = ( $this->input->get_post('date_filter') !='' )?$this->system_model->formatDate($this->input->get_post('date_filter')):NULL;
        $search = $this->input->get_post('search_filter');

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }   

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $sel_query = "
        j.`id` AS jid,
        j.`status` AS j_status,
        j.`service` AS j_service,
        j.`created` AS j_created,
        j.`date` AS j_date,
        j.`comments` AS j_comments,
        j.`job_price` AS j_price,
        j.`job_type` AS j_type,
        j.`start_date`,
        j.`due_date`,
        j.`no_dates_provided`,
        j.`work_order`,
        j.`urgent_job`,
        j.`job_reason_id`,
        
        p.`property_id` AS prop_id, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3,
        p.`state` AS p_state,
        p.`postcode` AS p_postcode,
        p.`comments` AS p_comments, 
        
        a.`agency_id` AS a_id,
        a.`agency_name` AS agency_name,
        a.`phone` AS a_phone,
        a.`address_1` AS a_address_1, 
        a.`address_2` AS a_address_2, 
        a.`address_3` AS a_address_3,
        a.`state` AS a_state,
        a.`postcode` AS a_postcode,
        a.`trust_account_software`,
        a.`tas_connected`,
        
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type
        ";

        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type'),
            
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'date'=>$date_filter,
            'search' => $search,
            'postcodes' => $postcodes,

            'limit' => $per_page,
            'offset' => $offset,
            
            'sort_list' => array(
                array(
                    'order_by' => 'j.start_date',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['lists'] = $this->jobs_model->get_jobs($params);

        // all rows
        $sel_query = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'date'=>$date_filter,
            'search' => $search,
            'postcodes' => $postcodes,
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

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

        //Services Filter
        $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
        $params = array(
           'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
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


        // Region Filter ( get distinct state )
        $sel_query = "DISTINCT(p.`state`)";
        $region_filter_arr = array(
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
                )
            ),
            'group_by' => 'p.`state`',
            'display_query' => 0
        );
        $data['region_filter_json'] = json_encode($region_filter_arr);
       
        $pagi_links_params_arr = array(
            'job_type_filter' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'date_filter' => $date_filter,
            'search_filter' => $search,
            'sub_region_ms' => $sub_region_ms
        );
        $pagi_link_params = '/jobs/dha/?'.http_build_query($pagi_links_params_arr);


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

        $this->load->view('templates/inner_header', $data);
        $this->load->view('jobs/dha', $data);
        $this->load->view('templates/inner_footer', $data);
    }


    /**
     * Display escalate Jobs
     */
    public function escalate()
    {

        $this->load->model('api_model');

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Escalate";
        $page_url = '/jobs/escalate';

        $job_status = 'Escalate';

        $country_id = $this->config->item('country');

        $agency_filter = $this->input->get_post('agency_filter');
        $state_filter = $this->input->get_post('state_filter');
        $tsa_filter = $this->input->get_post('tsa_filter');
        $search = $this->input->get_post('search');

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);
        $agency_priority_filter = $this->input->get_post('agency_priority_filter');
        if ($agency_priority_filter != "") {
            $agency_priority_custom_where = "aght.priority = {$agency_priority_filter}";
        }

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }

        $order_by = ( $this->input->get_post('order_by') !='' )?$this->input->get_post('order_by'):'a.agency_name';
        $sort = ( $this->input->get_post('sort') !='' )?$this->input->get_post('sort'):'asc';
        $filter_orderby_columns = $this->input->get_post('order_by');

        if ($filter_orderby_columns == 'last_updated') {
            $sort_list = array(
                'order_by' => 'a.`escalate_notes_ts`',
                'sort' => $sort,
            );
        } else {
            $sort_list = array(
                'order_by' => 'a.agency_name',
                'sort' => $sort,
            );
        }

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $sel_query = "COUNT(j.`id`) AS jcount,
            a.`agency_id` AS a_id,
            a.`agency_name` AS agency_name,
            a.`phone` AS a_phone,
            a.`address_1` AS a_address_1, 
            a.`address_2` AS a_address_2, 
            a.`address_3` AS a_address_3,
            a.`state` AS a_state,
            a.`postcode` AS a_postcode,
            a.`trust_account_software`,
            a.`tas_connected`,
            a.`save_notes`,
            a.`escalate_notes`,
            a.`escalate_notes_ts`,
            a.`propertyme_agency_id`,
            a.`esclate_notes_last_updated_by`,
            a.`pme_supplier_id`,
            a.`palace_diary_id`,
            aght.priority,
            apmd.abbreviation,
            
            tsa.`trust_account_software_id`,
            tsa.`tsa_name`
        ";

        $custom_where = '( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )';

        // paginated list
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'job_status' => $job_status,
            'country_id' => $country_id,
            'join_table' => array('maintenance','trust_account_software', 'agency_priority', 'agency_priority_marker_definition'),
            'custom_where_arr' => array($custom_where, $agency_priority_custom_where),

            'agency_filter' => $agency_filter,
            'state_filter' => $state_filter,
            'tsa_filter' => $tsa_filter,
            'postcodes' => $postcodes,
            'search_agency' => $search,

            'limit' => $per_page,
            'offset' => $offset,

            'sort_list' => array(
                $sort_list
            ),  
            'group_by' => 'a.`agency_id`',
            'display_query' => 0
        );
        $data['lists'] = $this->jobs_model->get_jobs($params);
        $data['sql_query'] = $this->db->last_query(); //Show query on About

        // all rows
        $sel_query = "COUNT(a.`agency_id`) AS a_count,";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'job_status' => $job_status,
            'country_id' => $country_id,
            'join_table' => array('maintenance', 'agency_priority'),
            'custom_where_arr' => array($custom_where, $agency_priority_custom_where),

            'agency_filter' => $agency_filter,
            'state_filter' => $state_filter,
            'tsa_filter' => $tsa_filter,
            'postcodes' => $postcodes,
            'search_agency' => $search,

            'group_by' => 'a.`agency_id`'
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->num_rows();

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        //Agency name filter
        $sel_query = "DISTINCT(a.`agency_id`), a.`agency_name`";
        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where, 
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'job_status' => $job_status,
            'country_id' => $country_id,
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['agency_filter_json'] = json_encode($params);


        //State Filter
        $sel_query = "DISTINCT(p.`state`)";
        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where, 
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'job_status' => $job_status,
            'country_id' => $country_id,
            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['state_filter_json'] = json_encode($params);

        /*
        //Maintenance Filter
        $sel_query = "DISTINCT(am.`maintenance_id`), m.name as m_name";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'job_status' => $job_status,
            'country_id' => $country_id,
            'join_table' => array('maintenance'),
            'sort_list' => array(
                array(
                    'order_by' => 'm.`name`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['maintenance_filter_json'] = json_encode($params);
        */


        // Region Filter ( get distinct state )
        $sel_query = "DISTINCT(p.`state`)";
        $region_filter_arr = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where, 
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'job_status' => $job_status,
            'country_id' => $country_id,

            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['region_filter_json'] = json_encode($region_filter_arr);


        $pagi_links_params_arr = array(
            'agency_filter' => $agency_filter,
            'job_type_filter' => $job_type_filter,
            'service_filter' => $service_filter,
            'date_filter' => $date_filter,
            'search' => $search,
            'sub_region_ms' => $sub_region_ms,
            'state_filter' => $state_filter,
            'agency_priority_filter' => $agency_priority_filter
        );
        $pagi_link_params = '/jobs/escalate/?'.http_build_query($pagi_links_params_arr);

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

        $this->load->view('templates/inner_header', $data);
        $this->load->view('jobs/escalate', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    // check agency has old escalate notes
    public function ajax_check_agency_old_escalate_notes(){
        echo $this->jobs_model->get_agency_old_escalate_notes();
    }

    // clear agency old escalate notes
    public function clear_agency_old_escalate_notes(){
        $this->jobs_model->get_agency_old_escalate_notes(array('clear'=>1));
    }


    public function escalate_jobs()
    {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Escalate Jobs";

        $job_status = 'Escalate';

        $country_id = $this->config->item('country');
        $agency_id = $this->uri->segment(3);

        $job_type_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $reason_filter = $this->input->get_post('reason_filter');   
        $date_filter = ( $this->input->get_post('date') !='' )?$this->system_model->formatDate( $this->input->get_post('date')):NULL;
        $search = $this->input->get_post('search');

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

        // pagination (offset/limit)
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $sel_query = "
            j.`id` AS jid,
            j.`status` AS j_status,
            j.`service` AS j_service,
            j.`created` AS j_created,
            j.`date` AS j_date,
            j.`comments` AS j_comments,
            j.`job_price` AS j_price,
            j.`job_type` AS j_type,
            j.`agency_approve_en`,
            j.status_changed_timestamp,
            j.`is_eo`,
            j.`job_type` AS j_type,
            
            p.`property_id` AS prop_id, 
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            p.`comments` AS p_comments, 

            apd_pme.`api` AS pme_api,
            apd_pme.`api_prop_id` AS pme_prop_id,

            apd_palace.`api` AS palace_api,
            apd_palace.`api_prop_id` AS palace_prop_id,
            
            a.`agency_id` AS a_id,
            a.`agency_name` AS agency_name,
            a.`phone` AS a_phone,
            a.`address_1` AS a_address_1, 
            a.`address_2` AS a_address_2, 
            a.`address_3` AS a_address_3,
            a.`state` AS a_state,
            a.`postcode` AS a_postcode,
            a.`trust_account_software`,
            a.`tas_connected`,
            a.`save_notes`,
            a.`escalate_notes`,
            a.`propertyme_agency_id`,
            a.`pme_supplier_id`,
            a.`palace_diary_id`,
            
            ajt.`id` AS ajt_id,
            ajt.`type` AS ajt_type,

            sejr.escalate_job_reasons_id,
            ejr.reason
        ";

        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'agency_filter' => $agency_id,
            'job_status' => $job_status,
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type','escalate_job_reason','api_property_data_pme','api_property_data_palace'),
            
            'job_type' => $job_type_filter,  
            'service_filter' => $service_filter,
            'reason_filter' => $reason_filter,
            'postcodes' => $postcodes,
            'date'=>$date_filter,
            'search' => $search,
            'is_eo' => $show_is_eo,

            'limit' => $per_page,
            'offset' => $offset,
            
            'sort_list' => array(
                array(
                    'order_by' => 'ejr.`reason`',
                    'sort' => 'ASC',
                ),
            ),

            'display_query' => 0
        );
        $data['lists'] = $this->jobs_model->get_jobs($params);
        $data['sql_query'] = $this->db->last_query();

       
        // all rows
        $sel_query = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'agency_filter' => $agency_id,
            'job_status' => $job_status,
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type','escalate_job_reason'),
            
            'job_type' => $job_type_filter,  
            'service_filter' => $service_filter,
            'reason_filter' => $reason_filter,
            'postcodes' => $postcodes,
            'date'=>$date_filter,
            'search' => $search,
            'is_eo' => $show_is_eo
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;
        

        //Job type Filter
        $sel_query = "DISTINCT(j.`job_type`),
                `j.job_type`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'agency_filter' => $agency_id,
            'job_status' => $job_status,
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type'),
            'sort_list' => array(
                array(
                    'order_by' => 'j.`job_type`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['job_type_filter_json'] = json_encode($params);

        //Services Filter
        $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'agency_filter' => $agency_id,
            'job_status' => $job_status,
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type'),
            'sort_list' => array(
                array(
                    'order_by' => 'j.`service`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['service_filter_json'] = json_encode($params);

        //Reason Filter
        $sel_query = " DISTINCT (sejr.`escalate_job_reasons_id`), `ejr.reason` ";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'agency_filter' => $agency_id,
            'job_status' => $job_status,
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type','escalate_job_reason','job_reason'),
            'sort_list' => array(
                array(
                    'order_by' => 'ejr.`reason`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['reason_filter_json'] = json_encode($params);

         // Region Filter ( get distinct state )
         $sel_query = "DISTINCT(p.`state`)";
        $region_filter_arr = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'agency_filter' => $agency_id,
            'job_status' => $job_status,
            'country_id' => $country_id,

            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                )
            ),
            'group_by' => 'p.`state`'
        );
        $data['region_filter_json'] = json_encode($region_filter_arr);
        
        $pagi_links_params_arr = array(
            'agency_filter' => $agency_filter,
            'job_type_filter' => $job_type_filter,
            'service_filter' => $service_filter,
            'date_filter' => $date_filter,
            'search' => $search,
            'sub_region_ms' => $sub_region_ms
        );
        $pagi_link_params = "/jobs/escalate_jobs/{$agency_id}?".http_build_query($pagi_links_params_arr);


        // pagination settings
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $pagi_link_params;
        
        $this->pagination->initialize($config);
        
        $data['pagination'] = $this->pagination->create_links();
        

        $data['pagination'] = $this->pagination->create_links();
        
        // pagination count
        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);
    

        $this->load->view('templates/inner_header', $data);
        $this->load->view('jobs/escalate_jobs', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    /**
     * Export Escalated Jobs in CSV format
     */
    public function export_escalate_jobs(){
        
        // file name 
        $filename = 'escalate_jobs_'.date('Y-m-d').'.csv';

        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename={$filename}");
        header("Pragma: no-cache");
        header("Expires: 0");

        $job_status = 'Escalate';

        $country_id = $this->config->item('country');
        $agency_id = $this->input->get_post('agency_id');

        $job_filter = $this->input->get_post('jobType');
        $service_filter = $this->input->get_post('service');
        $reason_filter = $this->input->get_post('reason');
        $region_filter = $this->input->get_post('region');
        $date_filter = ( $this->input->get_post('date') !='' )?$this->system_model->formatDate( $this->input->get_post('date')):NULL;
        $search = $this->input->get_post('search');

        // get data 
       $sel_query = "
            j.`id` AS jid,
            j.`status` AS j_status,
            j.`service` AS j_service,
            j.`created` AS j_created,
            j.`date` AS j_date,
            j.`comments` AS j_comments,
            j.`job_price` AS j_price,
            j.`job_type` AS j_type,
            j.`agency_approve_en`,
            
            p.`property_id` AS prop_id, 
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            p.`comments` AS p_comments, 
            p.`compass_index_num` AS p_propery_code,
            
            a.`agency_id` AS a_id,
            a.`agency_name` AS agency_name,
            a.`phone` AS a_phone,
            a.`address_1` AS a_address_1, 
            a.`address_2` AS a_address_2, 
            a.`address_3` AS a_address_3,
            a.`state` AS a_state,
            a.`postcode` AS a_postcode,
            a.`trust_account_software`,
            a.`tas_connected`,
            a.`save_notes`,
            a.`escalate_notes`,
            a.`propertyme_agency_id`,
            
            ajt.`id` AS ajt_id,
            ajt.`type` AS ajt_type,

            sejr.escalate_job_reasons_id,
            ejr.reason
        ";

        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'agency_filter' => $agency_id,
            'job_status' => $job_status,
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type','escalate_job_reason'),
            
            'job_type' => $job_filter,  
            'service_filter' => $service_filter,
            'reason_filter' => $reason_filter,
            'date'=>$date_filter,
            'search' => $search,
            
            'sort_list' => array(
                array(
                    'order_by' => 'j.created',
                    'sort' => 'ASC',
                ),
            ),
        );
        $list = $this->jobs_model->get_jobs($params);
       
        // file creation 
        $file = fopen('php://output', 'w');
    
        $header = array("Job Type","Service Type","Address","Suburb","State","Country","Postcode","Tenants","Tenants Phone Number","Agency","Job Comments","Created Date"); 

        if( $country_id  == 1 ){ // AU
            $fg_compass_housing = 39;
        }else if( $country_id  == 2 ){ // NZ
            $fg_compass_housing = null;
        }

        $query = $this->db->query("SELECT `franchise_groups_id` FROM `agency` WHERE `agency_id`={$agency_id}")->row();
        $franchise_group = $query->franchise_groups_id;
        
        if($franchise_group == $fg_compass_housing || ($agency_id == 1598 && $country_id == 1)){
            array_push($header,($franchise_group == $fg_compass_housing )?'Compass Index Number':' Property Code');
        }
        fputcsv($file, $header);
        
        foreach ($list->result() as $row){ 

            $getAlarmJobType = $this->db->get_where('alarm_job_type',array('id'=>$row->j_service))->row()->type;
            $age = $this->gherxlib->getAge($row->j_created);
            $address = $row->p_address_1.$row->p_address_2;

            $data['jobType'] = $this->gherxlib->getJobTypeAbbrv($row->j_type);
            $data['service'] = $getAlarmJobType;
            $data['address'] = $address;
            $data['suburb'] = $row->p_address_3;
            $data['state'] = $row->p_state;
            $data['country'] = ($this->config->item('country')==1)?'Australia':'New Zealand';
            $data['postcode'] = $row->p_postcode;

            $t_params = array(
                'property_id'=> $row->prop_id,
                'active'=> 1
            );
            $get_tenants = $this->gherxlib->getNewTenantsData($t_params);
            $tenant_array = array();
            $tenant_phone_arr = [];
            foreach($get_tenants as $tenant_row){
                $tenant_array[]= $tenant_row->tenant_firstname." ".$tenant_row->tenant_lastname;
                $tenant_phone_arr[] = $tenant_row->tenant_mobile;
            }

            $data['tenants'] = implode(" | ",$tenant_array);
            $data['tenants_mobile_arr'] = implode(" | ",$tenant_phone_arr);

            $data['agency'] = $row->agency_name;
            $data['job_comments'] = $row->j_comments;
            $data['job_created_date'] = $this->system_model->formatDate($row->j_created,'d/m/Y');

            if($franchise_group == $fg_compass_housing || ($agency_id == 1598 && $country_id == 1)){
                $data['propery_code'] = $row->p_propery_code;
            }

            fputcsv($file,$data); 

        }
       
        fclose($file); 
        exit; 

    }

    /**
     * Display merge jobs
     */
    public function merge()
    {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Merged Jobs";

        $job_status="Merged Certificates";
        $country_id = $this->config->item('country');

        $agency_filter = $this->input->get_post('agency_filter');
        $job_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $state_filter = $this->input->get_post('state_filter');
        $date_filter = ( $this->input->get_post('date_filter') !='' )?$this->system_model->formatDate($this->input->get_post('date_filter')):NULL;
        $search = $this->input->get_post('search_filter');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $sel_query = "
        j.`id` AS jid,
        j.`status` AS j_status,
        j.`service` AS j_service,
        j.`created` AS j_created,
        j.`date` AS j_date,
        j.`comments` AS j_comments,
        j.`job_price` AS j_price,
        j.`job_type` AS j_type,
        j.`at_myob`,
        j.`sms_sent_merge`,
        j.`client_emailed`,
        
        p.`property_id` AS prop_id, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3,
        p.`state` AS p_state,
        p.`postcode` AS p_postcode,
        p.`comments` AS p_comments, 
        
        a.`agency_id` AS a_id,
        a.`agency_name` AS agency_name,
        a.`phone` AS a_phone,
        a.`address_1` AS a_address_1, 
        a.`address_2` AS a_address_2, 
        a.`address_3` AS a_address_3,
        a.`state` AS a_state,
        a.`postcode` AS a_postcode,
        a.`trust_account_software`,
        a.`tas_connected`,
        a.`send_emails`,
        a.`account_emails`,
        
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type
        ";

           
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type'),
            
            'agency_filter' => $agency_filter,
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'date'=>$date_filter,
            'search' => $search,

            'limit' => $per_page,
            'offset' => $offset,

            'sort_list' => array(
                array(
                    'order_by' => 'j.created',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['lists'] = $this->jobs_model->get_jobs($params);

        // all rows
        $sel_query = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type'),

            'agency_filter' => $agency_filter,
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'date'=>$date_filter,
            'search' => $search,
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;

        //Agency name filter
        $sel_query = "DISTINCT(a.`agency_id`),
        a.`agency_name`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['agency_filter_json'] = json_encode($params);

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

        //Services Filter
        $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('alarm_job_type'),
            'sort_list' => array(
                array(
                    'order_by' => 'ajt.`type`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['service_filter_json'] = json_encode($params);

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
    
        $pagi_links_params_arr = array(
            'agency_filter' => $agency_filter,
            'job_type_filter' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'date_filter' => $date_filter,
            'search_filter' => $search
        );
        $pagi_link_params = '/jobs/merge/?'.http_build_query($pagi_links_params_arr);

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

        //get $email_stats
        $email_stats_query = $this->jobs_model->get_email_stats($date='',$job_status);        

        $data['email_stats'] = $this->functions_model->mysqlMultiRows($email_stats_query);                


        $this->load->view('templates/inner_header', $data);
        $this->load->view('jobs/merge', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    /**
     * Display on-hold jobs
     */
    public function on_hold()
    {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "On Hold";
        $page_url = '/jobs/on_hold';

        $job_status="On Hold";

        $country_id = $this->config->item('country');

        $agency_filter = $this->input->get_post('agency_filter');
        $job_type_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $state_filter = $this->input->get_post('state_filter');
        $date_filter = ( $this->input->get_post('date_filter') !='' )?$this->system_model->formatDate($this->input->get_post('date_filter')):NULL;
        $search = $this->input->get_post('search_filter');
        $job_status = $this->input->get_post('job_status');

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }       

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');
        
        if($this->input->get_post('date')!=""){
            $custom_where = "CAST(j.`start_date` AS Date)  >= '{$startDate_filter}'";
        }else{
            $custom_where = NULL;
        }

        ##status filter
        $custom_where_arr_1 = "j.status = 'On Hold'";
        if($job_status=="On Hold"){
            $custom_where_arr_1 = "j.status = 'On Hold'";
        }elseif($job_status=="On Hold - COVID"){
            $custom_where_arr_1 = "j.status = 'On Hold - COVID'";
        }
        

        $sel_query = "
        j.`id` AS jid,
        j.`status` AS j_status,
        j.`service` AS j_service,
        j.`created` AS j_created,
        j.`date` AS j_date,
        j.`comments` AS j_comments,
        j.`job_price` AS j_price,
        j.`job_type` AS j_type,
        j.`start_date`,
        j.`due_date`,
        j.`no_dates_provided`,
        j.`bne_to_call_notes`,
        j.`urgent_job`,
        j.`job_reason_id`,
        
        p.`property_id` AS prop_id, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3,
        p.`state` AS p_state,
        p.`postcode` AS p_postcode,
        p.`comments` AS p_comments, 
        
        a.`agency_id` AS a_id,
        a.`agency_name` AS agency_name,
        a.`phone` AS a_phone,
        a.`address_1` AS a_address_1, 
        a.`address_2` AS a_address_2, 
        a.`address_3` AS a_address_3,
        a.`state` AS a_state,
        a.`postcode` AS a_postcode,
        a.`trust_account_software`,
        a.`tas_connected`,
        aght.priority,
        apmd.abbreviation,
        
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type
        ";



        if($this->input->get_post('export') && $this->input->get_post('export')==1){ //EXPORT


            $params_export = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                //'job_status' => $job_status,
                'custom_where_arr' => array(
                    $custom_where_arr_1
                ),
                'join_table' => array('job_type','alarm_job_type', 'agency_priority', 'agency_priority_marker_definition'),
                
                'agency_filter' => $agency_filter,
                'job_type' => $job_type_filter,
                'service_filter' => $service_filter,
                'state_filter' => $state_filter,
                'custom_where' => $custom_where,
                'search' => $search,
                'postcodes' => $postcodes,
                
                'sort_list' => array(
                    array(
                        'order_by' => 'j.created',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $lists_export_query = $this->jobs_model->get_jobs($params_export);

            $filename = 'On_hold_' . date('Y-m-d') . '.csv';

            header("Content-Type: text/csv");
            header("Content-Disposition: Attachment; filename=$filename");
            header("Pragma: no-cache");

            //header
            echo "Start Date,Age,Region,Job Type,Service,Address,State,Agency,Comments,Job#,Last Contact\n";

            foreach ($lists_export_query->result_array() as $list_item)
            {

                ##postcode new table
                $params_get_region = array(
                    'sel_query' => 'sr.subregion_name as postcode_region_name',
                    'postcode' => $list_item['p_postcode'],
                );
                $getRegion = $this->system_model->get_postcodes($params_get_region)->row();
                $region = $getRegion->postcode_region_name;

                $export_start_date = ($this->system_model->isDateNotEmpty($list_item['start_date']))?$this->system_model->formatDate($list_item['start_date'],'d/m/Y'):'';
                $export_age = $this->gherxlib->getAge($list_item['j_created']);
                $export_job_type =  $this->gherxlib->getJobTypeAbbrv($list_item['j_type']);
                $export_service = $list_item['ajt_type'];
                $prop_address = $list_item['p_address_1']." ".$list_item['p_address_2'].", ".$list_item['p_address_3'];
                $export_state = $list_item['p_state'];
                $export_agency = $list_item['agency_name'];
                $export_comments = $list_item['j_comments'];
                $export_job_id = $list_item['jid'];
                $lastContact =  $this->gherxlib->getLastContact($list_item['jid'])->row_array();
                $export_last_contact = ($this->system_model->isDateNotEmpty($lastContact['eventdate']))?$this->system_model->formatDate($lastContact['eventdate'],'d/m/Y'):'';

                echo "\"{$export_start_date}\",{$export_age},\"$region\",{$export_job_type},$export_service,\"$prop_address\",{$export_state},{$export_agency},{$export_comments},{$export_job_id},{$export_last_contact}\n";       
          
            }
            


        }else{ //NORMAL LIST VIEW

             
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                //'job_status' => $job_status,
                'join_table' => array('job_type','alarm_job_type','agency_priority', 'agency_priority_marker_definition'),
                
                'agency_filter' => $agency_filter,
                'job_type' => $job_type_filter,
                'service_filter' => $service_filter,
                'state_filter' => $state_filter,
                'custom_where' => $custom_where,
                'search' => $search,
                'postcodes' => $postcodes,
                'custom_where_arr' => array(
                    $custom_where_arr_1
                ),

                'limit' => $per_page,
                'offset' => $offset,
                
                'sort_list' => array(
                    array(
                        'order_by' => 'j.created',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['lists'] = $this->jobs_model->get_jobs($params);
            $data['last_query'] = $this->db->last_query();

            // all rows
            $sel_query = "COUNT(j.`id`) AS jcount";
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                //'job_status' => $job_status,
                'custom_where_arr' => array(
                    $custom_where_arr_1
                ),
                'join_table' => array('job_type','alarm_job_type'),
                
                'agency_filter' => $agency_filter,
                'job_type' => $job_type_filter,
                'service_filter' => $service_filter,
                'state_filter' => $state_filter,
                'custom_where' => $custom_where,
                'search' => $search,
                'postcodes' => $postcodes
            );
            $query = $this->jobs_model->get_jobs($params);
            $total_rows = $query->row()->jcount;

            // update page total
            $page_tot_params = array(
                'page' => $page_url,
                'total' => $total_rows
            );
            $this->system_model->update_page_total($page_tot_params);


            //Agency  filter
            $sel_query = "DISTINCT(a.`agency_id`),
            a.`agency_name`";
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                //'job_status' => $job_status,
                'custom_where_arr' => array(
                    $custom_where_arr_1
                ),
                'join_table' => array('job_type','alarm_job_type'),
                'sort_list' => array(
                    array(
                        'order_by' => 'a.`agency_name`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['agency_filter_json'] = json_encode($params);

            //Job type Filter
            $sel_query = "DISTINCT(j.`job_type`),
                    `j.job_type`";
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                //'job_status' => $job_status,
                'custom_where_arr' => array(
                    $custom_where_arr_1
                ),
                'join_table' => array('job_type','alarm_job_type'),
                'sort_list' => array(
                    array(
                        'order_by' => 'j.`job_type`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['job_type_filter_json'] = json_encode($params);

            //Services Filter
            // $sel_query = "DISTINCT(j.`service`), `ajt.type`";
            $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                //'job_status' => $job_status,
                'custom_where_arr' => array(
                    $custom_where_arr_1
                ),
                'join_table' => array('job_type','alarm_job_type'),
                'sort_list' => array(
                    array(
                        'order_by' => 'ajt.`type`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['service_filter_json'] = json_encode($params);  


            //State Filter
            $sel_query = "DISTINCT(p.`state`)";
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                //'job_status' => $job_status,
                'custom_where_arr' => array(
                    $custom_where_arr_1
                ),
                'join_table' => array('job_type','alarm_job_type'),
                'sort_list' => array(
                    array(
                        'order_by' => 'p.`state`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['state_filter_json'] = json_encode($params);

            // Region Filter ( get distinct state )
            $sel_query = "p.`state`";
            $region_filter_arr = array(
                'sel_query' => $sel_query,
                'custom_where_arr' => array(
                    $custom_where
                ),
                'del_job' => 0,
                'p_deleted' => 0,
                'a_status' => 'active',
                'custom_where_arr' => array(
                    $custom_where_arr_1
                ),
                //'job_status' => $job_status,
                
                'country_id' => $country_id,
                'join_table' => array('job_type','alarm_job_type'),

                'sort_list' => array(
                    array(
                        'order_by' => 'p.`state`',
                        'sort' => 'ASC',
                    )
                ),
                'group_by' => 'p.`state`',
                'display_query' => 0
            );
            $data['region_filter_json'] = json_encode($region_filter_arr);
        
            $pagi_links_params_arr = array(
                'agency_filter' => $agency_filter,
                'job_type_filter' => $job_type_filter,
                'service_filter' => $service_filter,
                'state_filter' => $state_filter,
                'date_filter' => $date_filter,
                'search' => $search,
                'sub_region_ms' => $sub_region_ms,
                'job_status' => $job_status
            );
            $pagi_link_params = '/jobs/on_hold/?'.http_build_query($pagi_links_params_arr);

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


            $this->load->view('templates/inner_header', $data);
            $this->load->view('jobs/on_hold', $data);
            $this->load->view('templates/inner_footer', $data);

        }
        
       

          
    }

    /**
     * Display on-hold jobs
     */
    public function image_on_hold()
    {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Image - On Hold";
        $page_url = '/jobs/image_on_hold';

        $job_status="On Hold";

        $country_id = $this->config->item('country');

        $agency_filter = $this->input->get_post('agency_filter');
        $job_type_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $state_filter = $this->input->get_post('state_filter');
        $date_filter = ( $this->input->get_post('date_filter') !='' )?$this->system_model->formatDate($this->input->get_post('date_filter')):NULL;
        $date_filter_from = ($this->input->get_post('date_filter_from')!="")?$this->system_model->formatDate($this->input->get_post('date_filter_from')):NULL;
        $date_filter_to = ($this->input->get_post('date_filter_to')!="")?$this->system_model->formatDate($this->input->get_post('date_filter_to')):NULL;
        $search = $this->input->get_post('search_filter');
        $job_status = $this->input->get_post('job_status');

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);
        $tech_filter = $this->input->get_post('tech_filter');

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }       

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');
        
        if($this->input->get_post('date')!=""){
            $custom_where = "CAST(j.`start_date` AS Date)  >= '{$startDate_filter}'";
        }else{
            $custom_where = NULL;
        }

        ##status filter
        $custom_where_arr_1 = "j.status = 'On Hold'";
        if($job_status=="On Hold"){
            $custom_where_arr_1 = "j.status = 'On Hold'";
        }elseif($job_status=="On Hold - COVID"){
            $custom_where_arr_1 = "j.status = 'On Hold - COVID'";
        }

        $sel_query = "
            j.`id` AS jid,
            j.`status` AS j_status,
            j.`service` AS j_service,
            j.`created` AS j_created,
            j.`date` AS j_date,
            j.`comments` AS j_comments,
            j.`job_price` AS j_price,
            j.`job_type` AS j_type,
            j.`start_date`,
            j.`due_date`,
            j.`no_dates_provided`,
            j.`bne_to_call_notes`,
            j.`urgent_job`,
            j.`job_reason_id`,
            j.`key_access_required`,
            
            p.`property_id` AS prop_id, 
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            p.`comments` AS p_comments, 
            
            a.`agency_id` AS a_id,
            a.`agency_name` AS agency_name,
            a.`phone` AS a_phone,
            a.`address_1` AS a_address_1, 
            a.`address_2` AS a_address_2, 
            a.`address_3` AS a_address_3,
            a.`state` AS a_state,
            a.`postcode` AS a_postcode,
            a.`trust_account_software`,
            a.`tas_connected`,
            aght.priority,
            apmd.abbreviation,
            
            ajt.`id` AS ajt_id,
            ajt.`type` AS ajt_type
        ";

        $fg = 40; // Image
        $image_on_hold_custom_where = " atbl.`id` IS NULL AND a.`franchise_groups_id` = {$fg} ";

        // join airtable booked
        $airtable_booked_join = array(
            'join_table' => '`airtable` AS atbl',
            'join_on' => 'j.`id` = atbl.`job_id` AND atbl.`on_hold` = 1',
            'join_type' => 'left'
        );

        if($this->input->get_post('export') && $this->input->get_post('export')==1){ //EXPORT

            $params_export = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                //'job_status' => $job_status,
                
                'join_table' => array('job_type','alarm_job_type', 'agency_priority', 'agency_priority_marker_definition', 'airtable'),

                'custom_where_arr' => array(
                    $custom_where_arr_1, $image_on_hold_custom_where
                ),
                'custom_joins_arr' => array(
                    $airtable_booked_join
                ),
                
                'agency_filter' => $agency_filter,
                'job_type' => $job_type_filter,
                'service_filter' => $service_filter,
                'state_filter' => $state_filter,
                'date'=>$date_filter,
                'custom_where' => $custom_where,
                'search' => $search,
                'postcodes' => $postcodes,
                
                'sort_list' => array(
                    array(
                        'order_by' => 'j.created',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $lists_export_query = $this->jobs_model->get_jobs($params_export);

            $filename = 'Image_On_hold_' . date('Y-m-d') . '.csv';

            header("Content-Type: text/csv");
            header("Content-Disposition: Attachment; filename=$filename");
            header("Pragma: no-cache");

            //header
            echo "Start Date,Age,Region,Job Type,Service,Address,State,Agency,Comments,Job#,Last Contact\n";

            foreach ($lists_export_query->result_array() as $list_item)
            {

                ##postcode new table
                $params_get_region = array(
                    'sel_query' => 'sr.subregion_name as postcode_region_name',
                    'postcode' => $list_item['p_postcode'],
                );
                $getRegion = $this->system_model->get_postcodes($params_get_region)->row();
                $region = $getRegion->postcode_region_name;

                $export_start_date = ($this->system_model->isDateNotEmpty($list_item['start_date']))?$this->system_model->formatDate($list_item['start_date'],'d/m/Y'):'';
                $export_age = $this->gherxlib->getAge($list_item['j_created']);
                $export_job_type =  $this->gherxlib->getJobTypeAbbrv($list_item['j_type']);
                $export_service = $list_item['ajt_type'];
                $prop_address = $list_item['p_address_1']." ".$list_item['p_address_2'].", ".$list_item['p_address_3'];
                $export_state = $list_item['p_state'];
                $export_agency = $list_item['agency_name'];
                $export_comments = $list_item['j_comments'];
                $export_job_id = $list_item['jid'];
                $lastContact =  $this->gherxlib->getLastContact($list_item['jid'])->row_array();
                $export_last_contact = ($this->system_model->isDateNotEmpty($lastContact['eventdate']))?$this->system_model->formatDate($lastContact['eventdate'],'d/m/Y'):'';

                echo "\"{$export_start_date}\",{$export_age},\"$region\",{$export_job_type},$export_service,\"$prop_address\",{$export_state},{$export_agency},{$export_comments},{$export_job_id},{$export_last_contact}\n";       
          
            }
            


        }else{ //NORMAL LIST VIEW

             
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                //'job_status' => $job_status,
                'join_table' => array('job_type','alarm_job_type','agency_priority', 'agency_priority_marker_definition'),
                
                'agency_filter' => $agency_filter,
                'job_type' => $job_type_filter,
                'service_filter' => $service_filter,
                'state_filter' => $state_filter,
                'tech_filter' => $tech_filter,
                'date'=>$date_filter,
                'custom_where' => $custom_where,
                'search' => $search,
                'postcodes' => $postcodes,
                'custom_where_arr' => array(
                    $custom_where_arr_1, $image_on_hold_custom_where
                ),

                'custom_joins_arr' => array(
                    $airtable_booked_join
                ),

                'limit' => $per_page,
                'offset' => $offset,
                
                'sort_list' => array(
                    array(
                        'order_by' => 'j.created',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['lists'] = $this->jobs_model->get_jobs($params);
            $data['last_query'] = $this->db->last_query();

            // all rows
            $sel_query = "COUNT(j.`id`) as jcount";
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                //'job_status' => $job_status,
                
                'join_table' => array('job_type','alarm_job_type'),

                'custom_where_arr' => array(
                    $custom_where_arr_1, $image_on_hold_custom_where
                ),
                'custom_joins_arr' => array(
                    $airtable_booked_join
                ),

                'agency_filter' => $agency_filter,
                'job_type' => $job_type_filter,
                'service_filter' => $service_filter,
                'state_filter' => $state_filter,
                'custom_where' => $custom_where,
                'search' => $search,
                'postcodes' => $postcodes,
                'tech_filter' => $tech_filter
            );
            $query = $this->jobs_model->get_jobs($params);
            $total_rows = $query->row()->jcount;

            // update page total
            $page_tot_params = array(
                'page' => $page_url,
                'total' => $total_rows
            );
            $this->system_model->update_page_total($page_tot_params);

             //Agency  filter
             $sel_query = "DISTINCT(a.`agency_id`),
             a.`agency_name`";
             $params = array(
                 'sel_query' => $sel_query,
                 'p_deleted' => 0,
                 'a_status' => 'active',
                 'del_job' => 0,
                 'country_id' => $country_id,
                 //'job_status' => $job_status,
                 'custom_where_arr' => array(
                     $custom_where_arr_1
                 ),
                 'join_table' => array('job_type','alarm_job_type'),
                 'sort_list' => array(
                     array(
                         'order_by' => 'a.`agency_name`',
                         'sort' => 'ASC',
                     ),
                 ),
             );
             $data['agency_filter_json'] = json_encode($params);

            //Job type Filter
            $sel_query = "DISTINCT(j.`job_type`),
                    `j.job_type`";
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                //'job_status' => $job_status,
                
                'join_table' => array('job_type','alarm_job_type'),

                'custom_where_arr' => array(
                    $custom_where_arr_1, $image_on_hold_custom_where
                ),

                'custom_joins_arr' => array(
                    $airtable_booked_join
                ),
                
                'sort_list' => array(
                    array(
                        'order_by' => 'j.`job_type`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['job_type_filter_json'] = json_encode($params);

            //Services Filter
            // $sel_query = "DISTINCT(j.`service`), `ajt.type`";
            $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                //'job_status' => $job_status,
                
                'join_table' => array('job_type','alarm_job_type'),

                'custom_where_arr' => array(
                    $custom_where_arr_1, $image_on_hold_custom_where
                ),                
                'custom_joins_arr' => array(
                    $airtable_booked_join
                ),

                'sort_list' => array(
                    array(
                        'order_by' => 'ajt.`type`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['service_filter_json'] = json_encode($params);  


            //State Filter
            $sel_query = "DISTINCT(p.`state`)";
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                //'job_status' => $job_status,
                
                'join_table' => array('job_type','alarm_job_type'),
                'custom_where_arr' => array(
                    $custom_where_arr_1, $image_on_hold_custom_where
                ),
                'custom_joins_arr' => array(
                    $airtable_booked_join
                ),

                'sort_list' => array(
                    array(
                        'order_by' => 'p.`state`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['state_filter_json'] = json_encode($params);

            // Region Filter ( get distinct state )
            $sel_query = "p.`state`";
            $region_filter_arr = array(
                'sel_query' => $sel_query,
                'del_job' => 0,
                'p_deleted' => 0,
                'a_status' => 'active',
                
                //'job_status' => $job_status,
                
                'country_id' => $country_id,
                'join_table' => array('job_type','alarm_job_type'),

                'custom_where_arr' => array(
                    $custom_where, $custom_where_arr_1, $image_on_hold_custom_where
                ),
                'custom_joins_arr' => array(
                    $airtable_booked_join
                ),

                'sort_list' => array(
                    array(
                        'order_by' => 'p.`state`',
                        'sort' => 'ASC',
                    )
                ),
                'group_by' => 'p.`state`',
                'display_query' => 0
            );
            $data['region_filter_json'] = json_encode($region_filter_arr);

            //tech filter
            $sel_query = "DISTINCT(j.`assigned_tech`),sa.`StaffID`,sa.`FirstName`,sa.`LastName`";
            $tech_filter_params = array(
                'sel_query' => $sel_query,
                'del_job' => 0,
                'p_deleted' => 0,
                'a_status' => 'active',
                
                'job_status' => $job_status,

                'custom_joins_arr' => array(
                    $airtable_booked_join
                ),

                'custom_where'=> $custom_where,
                
                'country_id' => $country_id,
                'join_table' => array('job_type','alarm_job_type','staff_accounts'),

                'sort_list' => array(
                    array(
                        'order_by' => 'sa.`FirstName`',
                        'sort' => 'ASC',
                    )
                ),
                'display_query' => 0
            );
            $data['tech_filter'] = $this->jobs_model->get_jobs($tech_filter_params);
        
            $pagi_links_params_arr = array(
                'agency_filter' => $agency_filter,
                'job_type_filter' => $job_type_filter,
                'service_filter' => $service_filter,
                'state_filter' => $state_filter,
                'date_filter' => $date_filter,
                'search' => $search,
                'sub_region_ms' => $sub_region_ms,
                'job_status' => $job_status,
                'tech_filter' => $tech_filter
            );
            $pagi_link_params = '/jobs/image_on_hold/?'.http_build_query($pagi_links_params_arr);

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


            $this->load->view('templates/inner_header', $data);
            $this->load->view($page_url, $data);
            $this->load->view('templates/inner_footer', $data);

        }
          
    }

    /**
     * Display Short Term Rental jobs
     */
    public function holiday_rentals()
    {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Short Term Rentals";
        $page_url = '/jobs/holiday_rentals';


        $country_id = $this->config->item('country');
 
        $agency_filter = $this->input->get_post('agency_filter');
        $job_type_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $state_filter = $this->input->get_post('state_filter');
        $date_filter = ( $this->input->get_post('date') !='' )?$this->system_model->formatDate($this->input->get_post('date')):NULL;
        $search = $this->input->get_post('search');

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }   

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $sel_query = "
        j.`id` AS jid,
        j.`status` AS j_status,
        j.`service` AS j_service,
        j.`created` AS j_created,
        j.`date` AS j_date,
        j.`comments` AS j_comments,
        j.`job_price` AS j_price,
        j.`job_type` AS j_type,
        j.`start_date`,
        j.`due_date`,
        j.`no_dates_provided`,
        j.`bne_to_call_notes`,
        
        p.`property_id` AS prop_id, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3,
        p.`state` AS p_state,
        p.`postcode` AS p_postcode,
        p.`comments` AS p_comments, 
        
        a.`agency_id` AS a_id,
        a.`agency_name` AS agency_name,
        a.`phone` AS a_phone,
        a.`address_1` AS a_address_1, 
        a.`address_2` AS a_address_2, 
        a.`address_3` AS a_address_3,
        a.`state` AS a_state,
        a.`postcode` AS a_postcode,
        a.`trust_account_software`,
        a.`tas_connected`,
        aght.priority,
        apmd.abbreviation,
        
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type
        ";

        $custom_where = "p.`holiday_rental` = 1 AND j.`status` = 'To Be Booked'";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type', 'agency_priority', 'agency_priority_marker_definition'),
            'custom_where' => $custom_where,

            'agency_filter' => $agency_filter,
            'job_type' => $job_type_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'postcodes' => $postcodes,
            'date'=>$date_filter,
            'search'=>$search,

            'limit' => $per_page,
            'offset' => $offset,
            
            'sort_list' => array(
                array(
                    'order_by' => 'j.created',
                    'sort' => 'ASC',
                ),
            ),
        );

        if( $this->input->get_post('export') && $this->input->get_post('export')==1 ){ ##Unset limit and offset for export
            unset($params['limit']);
            unset($params['offset']);
        }

        $get_Jobs = $this->jobs_model->get_jobs($params);
        $data['last_query'] = $this->db->last_query();


        if( $this->input->get_post('export') && $this->input->get_post('export')==1 ){ ##Export

            $export_q = $get_Jobs;

            $filename = 'holiday_rentals' . date('Y-m-d') . '.csv';

            header("Content-Type: text/csv");
            header("Content-Disposition: Attachment; filename=$filename");
            header("Pragma: no-cache");

            //header
            echo "Start Date,End Date,Region,Booking,Job Type,Service,Address,State,Agency,Comments,Job#\n";

            foreach ($export_q->result_array() as $list_item)
            {

                $params_get_region = array(
                    'postcode' => $list_item['p_postcode'],
                );
                $getRegion = $this->system_model->get_postcodes($params_get_region)->row(); 
                $region = $getRegion->subregion_name;

                $export_start_date = ($this->system_model->isDateNotEmpty($list_item['start_date']))?$this->system_model->formatDate($list_item['start_date'],'d/m/Y'):'';
                $end_date = ($this->system_model->isDateNotEmpty($list_item['due_date']))?date('d/m/Y', strtotime($list_item['due_date'])):(($list_item['no_dates_provided']==1)?'N/A':'');

                #Booking
                $getStr = $this->system_model->getStrbyRegion($getRegion->sub_region_id);
                $tt = array();
                foreach($getStr->result_array() as $str_row){
                    $reg_arr = explode(",",$str_row['sub_regions']);

                    if( in_array($getRegion->sub_region_id, $reg_arr) ){
                        $tt[] = date('d/m',strtotime($str_row['date']));
                    }else{
                        $no_set_date_flag = 1;
                    }
                }
                $tt_imp = implode(',', $tt);

                $export_job_type =  $this->gherxlib->getJobTypeAbbrv($list_item['j_type']);
                $export_service = $list_item['ajt_type'];
                $prop_address = $list_item['p_address_1']." ".$list_item['p_address_2'].", ".$list_item['p_address_3'];
                $export_state = $list_item['p_state'];
                $export_agency = $list_item['agency_name'];
                $export_comments = $list_item['j_comments'];
                $export_job_id = $list_item['jid'];


                echo "\"{$export_start_date}\",{$end_date},\"$region\",\"{$tt_imp}\",\"{$export_job_type}\",$export_service,\"$prop_address\",{$export_state},{$export_agency},\"{$export_comments}\",{$export_job_id}\n";       
          
            }

        }else{ ##Normal Listing

            $data['lists'] = $get_Jobs;

             // all rows
            $sel_query = "COUNT(j.`id`) AS jcount";
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'join_table' => array('job_type','alarm_job_type'),
                'custom_where' => $custom_where,

                'agency_filter' => $agency_filter,
                'job_type' => $job_type_filter,
                'service_filter' => $service_filter,
                'state_filter' => $state_filter,
                'postcodes' => $postcodes,
                'date'=>$date_filter,
                'search'=>$search,
            );
            $query = $this->jobs_model->get_jobs($params);
            $total_rows = $query->row()->jcount;

            // update page total
            $page_tot_params = array(
                'page' => $page_url,
                'total' => $total_rows
            );
            $this->system_model->update_page_total($page_tot_params);

            //Agency name filter
            $sel_query = "DISTINCT(a.`agency_id`),
            a.`agency_name`";
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'join_table' => array('job_type','alarm_job_type'),
                'custom_where' => $custom_where,
                'sort_list' => array(
                    array(
                        'order_by' => 'a.`agency_name`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['agency_filter_json'] = json_encode($params);

            
            //Job type Filter
            $sel_query = "DISTINCT(j.`job_type`),
                    `j.job_type`";
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'join_table' => array('job_type'),
                'custom_where' => $custom_where,
                'sort_list' => array(
                    array(
                        'order_by' => 'j.`job_type`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['job_type_filter_json'] = json_encode($params);

            //Services Filter
            $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'join_table' => array('alarm_job_type'),
                'custom_where' => $custom_where,
                'sort_list' => array(
                    array(
                        'order_by' => 'j.`service`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['service_filter_json'] = json_encode($params);

            //State Filter
            $sel_query = "DISTINCT(p.`state`)";
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'custom_where' => $custom_where,
                'sort_list' => array(
                    array(
                        'order_by' => 'p.`state`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['state_filter_json'] = json_encode($params);       


            // Region Filter ( get distinct state )
            $sel_query = "p.`state`";
            $region_filter_arr = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'custom_where_arr' => array(
                    $custom_where
                ),

                'sort_list' => array(
                    array(
                        'order_by' => 'p.`state`',
                        'sort' => 'ASC',
                    )
                ),
                'group_by' => 'p.`state`',
                'display_query' => 0
            );
            $data['region_filter_arr'] = $region_filter_arr;
            $data['region_filter_json'] = json_encode($region_filter_arr);


            $pagi_links_params_arr = array(
                'agency_filter' => $agency_filter,
                'job_type_filter' => $job_type_filter,
                'service_filter' => $service_filter,
                'date_filter' => $date_filter,
                'search' => $search,
                'sub_region_ms' => $sub_region_ms,
                'state_filter' => $state_filter
            );
            $pagi_link_params = "/jobs/holiday_rentals/?".http_build_query($pagi_links_params_arr);

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


            $this->load->view('templates/inner_header', $data);
            $this->load->view("jobs/holiday_rentals", $data);
            $this->load->view('templates/inner_footer', $data);

        }
        
        
       
    }


    /**
     * Display pre-completion jobs
     */
    //public function pre_completion()
    public function pre_completion()
    {

        $this->load->model('email_model');
        $this->load->model('figure_model');

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Pre Completion";
        $uri = "/jobs/pre_completion";
        $data['uri'] = $uri;

        $job_status="Pre Completion";

        $uri_slug = $_GET['order_by'];
        
        $country_id = $this->config->item('country');

        $search = $this->input->get_post('search_filter');
        $job_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $date_filter = NULL;
        if($this->input->get_post('date_filter')!=""){
            $date_filter = $this->system_model->formatDate($this->input->get_post('date_filter'));
        }
        //$order_by = ( $this->input->get_post('order_by') !='' )?$this->input->get_post('order_by'):'j.date';
        //$order_by = ( $this->input->get_post('order_by') !='' )?$this->input->get_post('order_by'):'j.completed_timestamp';
        
        if($uri_slug == "j.date"){
            $order_by = '`j`.`date` , TIME(`j`.`completed_timestamp`)';
        }
        else{
            $order_by = ( $this->input->get_post('order_by') !='' )?$this->input->get_post('order_by'):'`j`.`date` , TIME(`j`.`completed_timestamp`)';
        }
        
        //$sort = ( $this->input->get_post('sort') !='' )?$this->input->get_post('sort'):'desc';
        $sort = ( $this->input->get_post('sort') !='' )?$this->input->get_post('sort'):'asc';

        $move_to_merge = $this->input->get_post('move_to_merge');   
        $jobs_not_comp_res = $this->db->escape_str($this->input->get_post('jobs_not_comp_res'));      

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $sel_query = "
        j.`id` AS jid,
        j.`status` AS j_status,
        j.`service` AS j_service,
        j.`created` AS j_created,
        j.`date` AS j_date,
        j.`completed_timestamp` AS j_ctimestamp,
        j.`comments` AS j_comments,
        j.`job_price` AS j_price,
        j.`job_type` AS j_type,
        j.`tech_comments`,
        j.`urgent_job`,
        j.`ss_quantity`,
        j.`ts_completed`,
        j.`job_reason_id`,
        j.`key_access_required`,
        j.`door_knock`,
        j.`completed_timestamp`,
        j.`sms_sent_no_show`,
        j.`sms_sent_merge`,
        j.`assigned_tech`,
        j.`job_reason_comment`,
        j.`job_priority`,
        j.`ps_number_of_bedrooms`,
        j.`ts_safety_switch`,
        j.`ts_safety_switch_reason`,
        j.`repair_notes`,
        j.`is_eo`,
        j.`prop_comp_with_state_leg`,
        
        jr.`name` AS jr_name,
        
        p.`property_id` AS prop_id, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3,
        p.`state` AS p_state,
        p.`postcode` AS p_postcode,
        p.`comments` AS p_comments, 
        p.`prop_upgraded_to_ic_sa`,
        p.`qld_new_leg_alarm_num`,
        p.`holiday_rental`,
        p.is_sales,
        p.`retest_date`,

        apd_pme.`api` AS pme_api,
        apd_pme.`api_prop_id` AS pme_prop_id,

        apd_palace.`api` AS palace_api,
        apd_palace.`api_prop_id` AS palace_prop_id,
        
        apd_ptree.`api` AS ptree_api,
        apd_ptree.`api_prop_id` AS ptree_prop_id,

        nsw_pc.`short_term_rental_compliant`,
        nsw_pc.`req_num_alarms` AS nsw_leg_num_alarms,
        nsw_pc.`req_heat_alarm`,
        
        a.`agency_id` AS a_id,
        a.`agency_name` AS agency_name,
        a.`phone` AS a_phone,
        a.`address_1` AS a_address_1, 
        a.`address_2` AS a_address_2, 
        a.`address_3` AS a_address_3,
        a.`state` AS a_state,
        a.`postcode` AS a_postcode,
        a.`trust_account_software`,
        a.`tas_connected`,
        a.`franchise_groups_id`,
        a.`pme_supplier_id`,
        a.`palace_diary_id`,
        aght.priority,
        
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type,

        sa.`FirstName`,
        sa.`LastName`
        ";

        // job not completed reason filter
        if( $jobs_not_comp_res > 0 ){
            $custom_where = "j.`job_reason_id` = {$jobs_not_comp_res}";
        }        
        
        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'job_status' => $job_status,
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type','job_reason','staff_accounts','api_property_data_pme','api_property_data_palace', 'api_property_data_ptree', 'agency_priority'),
            'custom_joins' => array(
                'join_table' => 'nsw_property_compliance as nsw_pc',
                'join_on' => 'p.property_id = nsw_pc.property_id',
                'join_type' => 'left'
            ),
            
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'date'=>$date_filter,
            'search' => $search,

            'sort_list' => array(
                array(
                    'order_by' => $order_by,
                    'sort' => $sort,
                ),
            ),
            'display_query' => 0
        );
                       
        $job_sql = $this->jobs_model->get_jobs($params)->result_array();
        $data['last_query'] = $this->db->last_query();
        //echo $data['last_query'];
        
        
        $jobs_id_arr = [];
        foreach( $job_sql as $job_row ) {
            $jobs_id_arr[] = $job_row['jid'];
        }

        if( count($jobs_id_arr) > 0 ){

			$jobs_id_imp = implode(",", $jobs_id_arr);

            // inner/sub query to check alarms with empty ts_expiry
            $alarm_sql = $this->db->query("
            SELECT COUNT(`alarm_id`) AS al_count, `job_id`
            FROM `alarm`
            WHERE `job_id` IN({$jobs_id_imp})          
            AND (
                `ts_expiry` IS NULL OR
                `ts_expiry` = ''
            )
            AND `ts_discarded` != 1
            GROUP BY `job_id`
            ")->result_array();

            // inner/sub query to check alarms with empty expiry
            $alarm_sql2 = $this->db->query("
            SELECT COUNT(`alarm_id`) AS al_count, `job_id`
            FROM `alarm`
            WHERE `job_id` IN({$jobs_id_imp})          
            AND (
                `expiry` IS NULL OR
                `expiry` = ''
            )
            AND `ts_discarded` != 1
            GROUP BY `job_id`
            ")->result_array();            
            
            foreach ( $job_sql as &$job ) {

                foreach  ($alarm_sql as $alarm_row ) {
                    if ( $job['jid'] == $alarm_row['job_id'] ) {
                        $job['al_count'] = $alarm_row['al_count'];
                        break;
                    }
                }

                foreach  ($alarm_sql2 as $alarm_row2 ) {
                    if ( $job['jid'] == $alarm_row2['job_id'] ) {
                        $job['empty_expiry_count'] = $alarm_row2['al_count'];
                        break;
                    }
                }

            }

		}   

        $data['lists'] = $job_sql;
        //echo "<br /><br />====Data";
        //print_r($data['lists']);
        //echo $this->db->last_query();


        // all rows
        $exclude_is_sales_in_total_count = "p.is_sales!=1"; //dont count is_sales properties > as per Ness request
        $sel_query = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'custom_where_arr' => array($exclude_is_sales_in_total_count),
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'job_status' => $job_status,
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type','job_reason','staff_accounts'),
            
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'date'=>$date_filter,
            'search' => $search,
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $uri,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        //Job type Filter
        $sel_query = "DISTINCT(j.`job_type`),
                `j.job_type`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'job_status' => $job_status,
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type','job_reason'),
            'sort_list' => array(
                array(
                    'order_by' => 'j.`job_type`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['job_type_filter_json'] = json_encode($params);

        //Services Filter
        // $sel_query = "DISTINCT(j.`service`),`ajt.type`";
        $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'job_status' => $job_status,
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type','job_reason','staff_accounts'),
            'sort_list' => array(
                array(
                    'order_by' => 'ajt.`type`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['service_filter_json'] = json_encode($params);  

        // get no completed reason
        $data['ncr_sql'] = $this->db->query("
        SELECT `job_reason_id`, `name`
        FROM `job_reason`
        ORDER BY `name`
        ");   

        // get SMS template
        $sel_query = "sms_api_type_id, type_name, category, body, active";

        // exclude SMS template
        // No Answer
        // (Keys SMS Reply), (Yes/No SMS Reply)
        if ($country_id == 1) { // AU
            $exlude_id = '27,28';
        } else if ($country_id == 2) {
            $exlude_id = '2,3';
        }

        /*
        24 - Send Letters
        18 - SMS (Thank You)
        17 - SMS (Custom)
        */

        $custom_where = "sms_api_type_id NOT IN (24,18,17,{$exlude_id})";
        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'active' => 1,
            'sort_list' => array(
                array(
                    'order_by' => 'type_name',
                    'sort' => 'ASC'
                )
            ),
            'display_query' => 0
        );
        $data['sms_templates_sql'] = $this->sms_model->getSmsTemplates($params);

        // get email templates
        $et_params = array(
            'echo_query' => 0,
            'active' => 1,
            'sort_list' => array(
                [
                    'order_by' => 'et.`template_name`',
                    'sort' => 'ASC'
                ]
            )
        );
        $data['email_templates'] = $this->email_model->get_email_templates($et_params);

        // get IC services
        $data['ic_services'] = $this->figure_model->getICService();
    
        $pagi_links_params_arr = array(
            'search_filter' => $search,
            'job_type_filter' => $job_filter,
            'service_filter' => $service_filter,
            'date_filter' => $date_filter
        );
        $pagi_link_params = $uri.'/?'.http_build_query($pagi_links_params_arr);

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

        $data['page_search_url'] = $pagi_link_params;
        $data['order_by'] = $order_by;
        $data['sort'] = $sort;
        $data['move_to_merge'] = $move_to_merge;

        $this->load->view('templates/inner_header', $data);
        $this->load->view('jobs/pre_completion', $data);
        $this->load->view('templates/inner_footer', $data);
    }




    public function ajax_precomp_send_now_show_sms(){

        $this->load->model('sms_model');
        $this->load->model('cron_model');

        $country_id = $this->config->item('country');        
        $staff_id = $this->session->staff_id;       
        $today_full = date("Y-m-d H:i:s");
        $sms_type = 4; // No-Show

        $job_id = $this->input->get_post('job_id');

        if( $job_id > 0 ){


            // get jobs 
            $sel_query = "                    
                j.`id` AS jid,
                j.`booked_with`,
                j.`property_id`  
            ";
            
            $job_params = array(
                'sel_query' => $sel_query,                
                
                'del_job' => 0,
                'p_deleted' => 0,
                'a_status' => 'active',

                'job_id' => $job_id,                   
                'country_id' => $country_id,            
                                        
                'display_query' => 0
            );
            $job_sql = $this->jobs_model->get_jobs($job_params);  
            $job_row = $job_sql->row(); 

            $property_id = $job_row->property_id;
            $booked_with = $job_row->booked_with;

            $no_show_params = array(
                'job_id' => $job_id,
                'property_id' => $property_id,
                'booked_with' => $booked_with,
                'staff_id' => $staff_id
            );
            $this->cron_model->send_no_show_sms_per_job($no_show_params);
            

        }        

    }





    /**
     * Display send letters job
     */
    public function new_jobs()
    {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "New Jobs";
        $uri = '/jobs/new_jobs';        

        $job_status="Send Letters";

        $country_id = $this->config->item('country');
        $job_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $state_filter = $this->input->get_post('state_filter');
        $date_filter = ( $this->input->get_post('date_filter') !='' )?$this->system_model->formatDate($this->input->get_post('date_filter')):NULL;
        $search = $this->input->get_post('search_filter');
        $export = $this->input->get_post('export');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');
        
        if($this->input->get_post('date')!=""){
            $custom_where = "CAST(j.`start_date` AS Date)  >= '{$date_filter}'";
        }else{
            $custom_where = NULL;
        }
        
        $sel_query = " 
            j.`id` AS jid, 
            j.`job_type`,
            j.`status` AS jstatus,
            j.`service` AS jservice,
            j.`created` AS jcreated,                
            j.`date` AS jdate,
            j.`job_price`,
            j.`start_date`,
            j.`due_date`,
            j.`comments` AS j_comments,           
            j.`assigned_tech`,
            j.`property_vacant`,
            j.`urgent_job`,
            
            p.`property_id`,
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3, 
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,                               
            p.`comments` AS p_comments,
            p.`holiday_rental`,                        

            a.`agency_id`,
            a.`agency_name`,
            aght.priority,     
            
            ajt.`type` AS ajt_type
        ";

        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('property','agency','alarm_job_type', 'agency_priority'),
            
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,            
            'custom_where' => $custom_where,
            'search' => $search,

            'limit' => $per_page,
            'offset' => $offset,
            
            'sort_list' => array(
                array(
                    'order_by' => 'j.urgent_job',
                    'sort' => 'DESC',
                ),
                array(
                    'order_by' => 'j.job_type',
                    'sort' => 'ASC',
                ),
                array(
                    'order_by' => 'p.address_3',
                    'sort' => 'ASC',
                ),
            ),
            'display_query' => 0
        );
        $job_sql = $this->jobs_model->get_jobs($params);          


        if ($export == 1) { //EXPORT         

            // file name
            $date_export = date('d/m/Y');
            $filename = "New_jobs_{$date_export}.csv";

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            // file creation 
            $csv_file = fopen('php://output', 'w');

            $csv_header = array("Added By","Job Type","Service Type","Price","Address","State","Agency","Job Comment","Property Comment","Start Date","End Date");
            fputcsv($csv_file, $csv_header);
            
            foreach ($job_sql->result() as $row){ 

                $csv_row = [];                
                               
                $csv_row[] = $this->gherxlib->getWhoCreatedSendLetters($row->property_id);
                $csv_row[]= $this->gherxlib->getJobTypeAbbrv($row->job_type);
                $csv_row[] = $row->ajt_type;
                $csv_row[] = '$'.$row->job_price;
                $csv_row[] = "{$row->p_address_1} {$row->p_address_2}, {$row->p_address_3}";
                $csv_row[] = $row->p_state;
                $csv_row[] = $row->agency_name;
                $csv_row[] = $row->j_comments;
                $csv_row[] = $row->p_comments;
                $csv_row[] = ( $this->system_model->isDateNotEmpty($row->start_date) == true )?date('d/m/Y',strtotime($row->start_date)):null;
                $csv_row[] = ( $this->system_model->isDateNotEmpty($row->due_date) == true )?date('d/m/Y',strtotime($row->due_date)):null;
                
                fputcsv($csv_file,$csv_row); 

            }
        
            fclose($csv_file); 
            exit; 

        }else{


            $data['lists'] = $job_sql;    
            
            // all rows
            $sel_query = "
            j.`id` AS jid, 
            j.`comments` AS j_comments,           

            p.`property_id`,                              
            p.`comments` AS p_comments
            ";
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'job_status' => $job_status,
                'join_table' => array('property','agency','alarm_job_type'),
            
                'job_type' => $job_filter,
                'service_filter' => $service_filter,
                'state_filter' => $state_filter,            
                'custom_where' => $custom_where,
                'search' => $search,
            );
            $query = $this->jobs_model->get_jobs($params);
            //$total_rows = $query->row()->jcount;
            $total_rows = $query->num_rows();

            // copied from cron bubble count, looks like only counting the yellow tab
            $send_letter_count = 0;
            foreach( $query->result() as $job_row ){

                // job or property comments
                if( $job_row->j_comments != "" || $job_row->p_comments != "" ){                
                    $send_letter_count++;
                }
    
            }

            // update page total
            $page_tot_params = array(
                'page' => $uri,
                'total' => $send_letter_count
            );
            $this->system_model->update_page_total($page_tot_params);

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
                'join_table' => array('job_type','alarm_job_type'),
                'sort_list' => array(
                    array(
                        'order_by' => 'j.urgent_job',
                        'sort' => 'DESC',
                    ),
                    array(
                        'order_by' => 'j.job_type',
                        'sort' => 'ASC',
                    ),
                    array(
                        'order_by' => 'p.address_3',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['job_type_filter_json'] = json_encode($params);

            //Services Filter
            $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'job_status' => $job_status,
                'join_table' => array('job_type','alarm_job_type'),
                'sort_list' => array(
                    array(
                        'order_by' => 'j.urgent_job',
                        'sort' => 'DESC',
                    ),
                    array(
                        'order_by' => 'j.job_type',
                        'sort' => 'ASC',
                    ),
                    array(
                        'order_by' => 'p.address_3',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['service_filter_json'] = json_encode($params);   


            //State Filter
            $sel_query = "DISTINCT(p.`state`)";
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'job_status' => $job_status,
                'join_table' => array('job_type','alarm_job_type'),
                'sort_list' => array(
                    array(
                        'order_by' => 'j.urgent_job',
                        'sort' => 'DESC',
                    ),
                    array(
                        'order_by' => 'j.job_type',
                        'sort' => 'ASC',
                    ),
                    array(
                        'order_by' => 'p.address_3',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['state_filter_json'] = json_encode($params);


            $pagi_links_params_arr = array(
                'job_type_filter' => $job_filter,
                'service_filter' => $service_filter,
                'state_filter' => $state_filter,
                'date_filter' => $date_filter,
                'search_filter' => $search,
            );
            $pagi_link_params = '/jobs/new_jobs/?'.http_build_query($pagi_links_params_arr);
    
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


            $data['uri'] = $uri;

            $this->load->view('templates/inner_header', $data);
            $this->load->view('jobs/send_letters', $data);
            $this->load->view('templates/inner_footer', $data);


        }



        
    }


    public function ajax_send_letters_sms_tenant() {

		$this->load->model('cron_model');

		$job_id_arr = $this->input->post('job_id_arr');
		$staff_id = $this->session->staff_id;				

		foreach( $job_id_arr as $job_id ){

			$params = array(
				"job_id" => $job_id,
				"staff_id" => $staff_id
			);
			$this->cron_model->send_letters_sms_tenant($params);

		}

	}


    public function ajax_send_letters_email_tenant() {

		$this->load->model('cron_model');

		$job_id_arr = $this->input->post('job_id_arr');
		$staff_id = $this->session->staff_id;				

		foreach( $job_id_arr as $job_id ){

			$params = array(
				"job_id" => $job_id,
				"staff_id" => $staff_id
			);
			$this->cron_model->send_letters_email_tenant($params);

		}

    }
    

    public function ajax_send_letters_no_tenant_email_to_agency() {

		$this->load->model('cron_model');

		$job_id_arr = $this->input->post('job_id_arr');
		$staff_id = $this->session->staff_id;				

		foreach( $job_id_arr as $job_id ){

			$params = array(
				"job_id" => $job_id,
				"staff_id" => $staff_id
			);
			$this->cron_model->send_letters_no_tenant_email_to_agency($params);

		}

	}



    /**
     * Display After Hours Job
     */
    public function after_hours()
    {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Outside Of Tech Hours";

        $page_url = '/jobs/after_hours';
        $country_id = $this->config->item('country');

        $job_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $state_filter = $this->input->get_post('state_filter');
        $agency_filter = $this->input->get_post('agency_filter');
        $date_filter = ($this->input->get_post('date_filter')!="")?date('Y-m-d',$this->input->get_post('date_filter')):NULL;
        $search = $this->input->get_post('search_filter');

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
        
        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $sel_query = "
        j.`id` AS jid,
        j.`status` AS j_status,
        j.`service` AS j_service,
        j.`created` AS j_created,
        j.`date` AS j_date,
        j.`start_date`,
        j.`due_date`,
        j.`comments` AS j_comments,
        j.`job_price` AS j_price,
        j.`job_type` AS j_type,
        j.`property_vacant`,
        j.`urgent_job`,
        j.`job_reason_id`,
        j.`preferred_time`,
        j.`is_eo`,
        j.`job_type` AS j_type,
        
        p.`property_id` AS prop_id, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3,
        p.`state` AS p_state,
        p.`postcode` AS p_postcode,
        p.`comments` AS p_comments, 
        
        a.`agency_id` AS a_id,
        a.`agency_name` AS agency_name,
        a.`phone` AS a_phone,
        a.`address_1` AS a_address_1, 
        a.`address_2` AS a_address_2, 
        a.`address_3` AS a_address_3,
        a.`state` AS a_state,
        a.`postcode` AS a_postcode,
        a.`trust_account_software`,
        a.`tas_connected`,
        aght.priority,
        apmd.abbreviation,
        
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type
        ";

        $custom_where = "( j.`status` = 'To Be Booked' OR j.`status` = 'Escalate' OR j.`status` = 'Booked' )";

        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'out_of_tech_hours' => 1,
            'join_table' => array('job_type','alarm_job_type', 'agency_priority', 'agency_priority_marker_definition'),
            
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'agency_filter' => $agency_filter,
            'date'=>$date_filter,
            'search' => $search,
            'postcodes'=> $postcodes,
            'is_eo' => $show_is_eo,
            
            'country_id' => $country_id,

            'limit' => $per_page,
            'offset' => $offset,

            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                )
            ),
            'display_query' => 0
        );

        if($this->input->get_post('export') && $this->input->get_post('export')==1){ ##Unset/remove pagination for export
            unset($params['limit']);
            unset($params['offset']);
        }

        $job_query = $this->jobs_model->get_jobs($params);


        if( $this->input->get_post('export') && $this->input->get_post('export') == 1 ){ ## Export CSV

            $export_q = $job_query;

            $filename = 'outside_of_tech_hours' . date('Y-m-d') . '.csv';

            header("Content-Type: text/csv");
            header("Content-Disposition: Attachment; filename=$filename");
            header("Pragma: no-cache");

            //header
            echo "Date,Job Type,Age,Service,Address,State,Region,Agency,Job#,Comments,Preferred Time\n";

            foreach ($export_q->result_array() as $list_item)
            {

                #using new table
                $params_get_region = array(
                    'sel_query' => 'sr.subregion_name as postcode_region_name',
                    'postcode' => $list_item['p_postcode'],
                );
                $getRegion = $this->system_model->get_postcodes($params_get_region)->row();
                $region = $getRegion->postcode_region_name;

                $export_start_date = ($this->system_model->isDateNotEmpty($list_item['j_date']))?$this->system_model->formatDate($list_item['j_date'],'d/m/Y'):'';
                $export_job_type =  $this->gherxlib->getJobTypeAbbrv($list_item['j_type']);
                $export_age = ($this->system_model->isDateNotEmpty($list_item['j_created']))?$this->system_model->formatDate($list_item['j_created'],'d/m/Y'):'';
                $export_service = $list_item['ajt_type'];
                $prop_address = $list_item['p_address_1']." ".$list_item['p_address_2'].", ".$list_item['p_address_3'];
                $export_state = $list_item['p_state'];
                $export_agency = $list_item['agency_name'];
                $export_job_id = $list_item['jid'];
                $export_comments = $list_item['j_comments'];   
                $preferred_time = $list_item['preferred_time'];   

                echo "\"{$export_start_date}\",{$export_job_type},\"$export_age\",{$export_service},\"{$prop_address}\",$export_state,\"$region\",{$export_agency},\"{$export_job_id}\",\"{$export_comments}\",{$preferred_time}\n";       
          
            }


        }else{ ## Normal Listing

            $data['lists'] = $job_query;

             // all rows
            $sel_query = "COUNT(j.`id`) AS jcount";
            $params = array(
                'sel_query' => $sel_query,
                'custom_where' => $custom_where,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'out_of_tech_hours' => 1,
                'join_table' => array('job_type','alarm_job_type'),

                'job_type' => $job_filter,
                'service_filter' => $service_filter,
                'state_filter' => $state_filter,
                'agency_filter' => $agency_filter,
                'date'=>$date_filter,
                'search' => $search,
                'postcodes'=> $postcodes,
                'is_eo' => $show_is_eo
            );
            $query = $this->jobs_model->get_jobs($params);
            $total_rows = $query->row()->jcount;

            // update page total
            $page_tot_params = array(
                'page' => $page_url,
                'total' => $total_rows
            );
            $this->system_model->update_page_total($page_tot_params);

            //Job type Filter
            $sel_query = "DISTINCT(j.`job_type`),
            `j.job_type`";
            $params = array(
                'sel_query' => $sel_query,
                'custom_where' => $custom_where,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'out_of_tech_hours' => 1,
                'join_table' => array('job_type'),

                'sort_list' => array(
                    array(
                        'order_by' => 'j.`job_type`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['job_type_filter_json'] = json_encode($params);


            //Services Filter
            $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
            $params = array(
                'sel_query' => $sel_query,
                'custom_where' => $custom_where,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'out_of_tech_hours' => 1,
                'join_table' => array('alarm_job_type'),

                'sort_list' => array(
                    array(
                        'order_by' => 'j.`service`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['service_filter_json'] = json_encode($params);    


            //State filter
            $sel_query = "DISTINCT(p.`state`),
            p.`state`";
            $params = array(
                'sel_query' => $sel_query,
                'custom_where' => $custom_where,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'out_of_tech_hours' => 1,

                'sort_list' => array(
                    array(
                        'order_by' => 'p.`state`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['state_filter_json'] = json_encode($params);


            // Region Filter ( get distinct state )
            $sel_query = "p.`state`";
            $region_filter_arr = array(
                'sel_query' => $sel_query,
                'custom_where_arr' => array(
                    $custom_where
                ),
                'del_job' => 0,
                'p_deleted' => 0,
                'a_status' => 'active',
                'country_id' => $country_id,
                'out_of_tech_hours' => 1,

                'sort_list' => array(
                    array(
                        'order_by' => 'p.`state`',
                        'sort' => 'ASC',
                    )
                ),
                'group_by' => 'p.`state`',
                'display_query' => 0
            );
            $data['region_filter_json'] = json_encode($region_filter_arr);
            

            //Agency  filter
            $sel_query = "DISTINCT(a.`agency_id`),
            a.`agency_name`";
            $params = array(
                'sel_query' => $sel_query,
                'custom_where' => $custom_where,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'out_of_tech_hours' => 1,

                'sort_list' => array(
                    array(
                        'order_by' => 'a.`agency_name`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['agency_filter_json'] = json_encode($params);


            $pagi_links_params_arr = array(
                'job_type_filter' => $job_filter,
                'service_filter' => $service_filter,
                'state_filter' =>  $state_filter,
                'agency_filter' => $agency_filter,
                'date_filter' => $date_filter,
                'search_filter' => $search,
                'sub_region_ms' => $sub_region_ms
            );
            $pagi_link_params = '/jobs/after_hours/?'.http_build_query($pagi_links_params_arr);


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

            $this->load->view('templates/inner_header', $data);
            $this->load->view('jobs/after_hours', $data);
            $this->load->view('templates/inner_footer', $data);

        }

       
    }



    /**
     * Display BNE TO CALL
     */
    public function bne_to_call()
    {
        $data['start_load_time'] = microtime(true);
        $page_url = '/jobs/bne_to_call';
        $data['title'] = "Office to call";

        $country_id = $this->config->item('country');

        $agency_filter = $this->input->get_post('agency_filter');
        $job_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $state = $this->input->get_post('state_filter');
        $date_filter =  ( $this->input->get_post('date_filter') !='' )?$this->system_model->formatDate($this->input->get_post('date_filter')):NULL;
        $search = $this->input->get_post('search_filter');

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }  
       
        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $sel_query = "
        j.`id` AS jid,
        j.`status` AS j_status,
        j.`service` AS j_service,
        j.`created` AS j_created,   
        j.`date` AS j_date,
        j.`comments` AS j_comments,
        j.`job_price` AS j_price,
        j.`job_type` AS j_type,
        j.`start_date`,
        j.`due_date`,
        j.`no_dates_provided`,
        j.`bne_to_call_notes`,
        
        p.`property_id` AS prop_id, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3,
        p.`state` AS p_state,
        p.`postcode` AS p_postcode,
        p.`comments` AS p_comments, 
        
        a.`agency_id` AS a_id,
        a.`agency_name` AS agency_name,
        a.`phone` AS a_phone,
        a.`address_1` AS a_address_1, 
        a.`address_2` AS a_address_2, 
        a.`address_3` AS a_address_3,
        a.`state` AS a_state,
        a.`postcode` AS a_postcode,
        a.`trust_account_software`,
        a.`tas_connected`,
        aght.priority,
        apmd.abbreviation,
        
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type
        ";

        $custom_where = "p.`bne_to_call` = 1 AND j.status NOT IN('Completed','Cancelled','Merged Certificates','Booked','Pre Completion','Pending','To Be Invoiced')";
        
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'custom_where' => $custom_where,
            'join_table' => array('job_type','alarm_job_type', 'agency_priority', 'agency_priority_marker_definition'),
            
            'agency_filter' => $agency_filter,
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state,
            'postcodes' => $postcodes,
            'date'=>$date_filter,
            'search' => $search,
            'otc_status' => 1,

            'limit' => $per_page,
            'offset' => $offset,
            
            'sort_list' => array(
                array(
                    'order_by' => 'j.urgent_job',
                    'sort' => 'DESC',
                ),
                array(
                    'order_by' => 'j.no_dates_provided',
                    'sort' => 'DESC',
                ),
                array(
                    'order_by' => 'p.address_3',
                    'sort' => 'DESC',
                ),
            ),
        );
        $data['lists'] = $this->jobs_model->get_jobs($params);

        $data['sql_query'] = $this->db->last_query();

        // Get all total rows
        $sel_query = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'custom_where' => $custom_where,
            'join_table' => array('job_type','alarm_job_type'),

            'agency_filter' => $agency_filter,
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state,
            'postcodes' => $postcodes,
            'date'=>$date_filter,
            'search' => $search,
            'otc_status' => 1,
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        //Agency name filter
        $sel_query = "DISTINCT(a.`agency_id`),
        a.`agency_name`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'custom_where' => $custom_where,
            'join_table' => array('job_type','alarm_job_type'),
            
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['agency_filter_json'] = json_encode($params);

        //Job type Filter
        $sel_query = "DISTINCT(j.`job_type`),
        `j.job_type`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'custom_where' => $custom_where,
            'join_table' => array('job_type','alarm_job_type'),

            'sort_list' => array(
                array(
                    'order_by' => 'j.`job_type`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['job_type_filter_json'] = json_encode($params);

        //Services Filter
        $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'custom_where' => $custom_where,
            'join_table' => array('job_type','alarm_job_type'),
            
            'sort_list' => array(
                array(
                    'order_by' => 'j.`service`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['service_filter_json'] = json_encode($params);

        //State Filter
        $sel_query = "DISTINCT(p.`state`)";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'custom_where' => $custom_where,
            'join_table' => array('job_type','alarm_job_type'),

            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['state_filter_json'] = json_encode($params);

        // Region Filter ( get distinct state )
        $sel_query = "DISTINCT(p.`state`)";
        $region_filter_arr = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'custom_where' => $custom_where,
            'join_table' => array('job_type','alarm_job_type'),

            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                )
            )
        );               
        $data['region_filter_json'] = json_encode($region_filter_arr);


        $pagi_links_params_arr = array(
            'agency_filter' => $agency_filter,
            'job_type_filter' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' =>  $state,
            'date_filter' => $date_filter,
            'search_filter' => $search,
            'sub_region_ms' => $sub_region_ms
        );
        $pagi_link_params = '/jobs/bne_to_call/?'.http_build_query($pagi_links_params_arr);


        // Pagination settings
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

        $this->load->view('templates/inner_header', $data);
        $this->load->view('jobs/bne_to_call', $data);
        $this->load->view('templates/inner_footer', $data);
    }


    public function ajax_update_job_bne_notes(){
        $data['status'] = false;
        $job_id = $this->input->post('job_id');
        $bne_note = $this->input->post('bne_note');

        $data = array(
            'bne_to_call_notes' => $bne_note
        );
        $this->db->where('id', $job_id);
        $this->db->update('jobs',$data);
        $this->db->limit(1);
        if($this->db->affected_rows()>0){
            $data['new_notes'] = $bne_note;
            $data['status'] = true;
        }

        echo json_encode($data);
    }

    /**
     * Get/Display Service Due Jobs list
     */
    public function service_due(){
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Service Due";
        $page_url = '/jobs/service_due';

        $job_status = 'Pending';

        $country_id = $this->config->item('country');

        $job_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $state_filter = $this->input->get_post('state_filter');
        $agency_filter = $this->input->get_post('agency_filter');
        $date_filter = ($this->input->get_post('date_filter')!="")?date('Y-m-d',$this->input->get_post('date_filter')):NULL;
        $search = $this->input->get_post('search_filter');

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }       
        
        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $sel_query = "
        j.`id` AS jid,
        j.`status` AS j_status,
        j.`service` AS j_service,
        j.`created` AS j_created,
        j.`date` AS j_date,
        j.`comments` AS j_comments,
        j.`job_price` AS j_price,
        j.`job_type` AS j_type,
        
        p.`property_id` AS prop_id, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3,
        p.`state` AS p_state,
        p.`postcode` AS p_postcode,
        p.`comments` AS p_comments, 
        
        a.`agency_id` AS a_id,
        a.`agency_name` AS agency_name,
        a.`phone` AS a_phone,
        a.`address_1` AS a_address_1, 
        a.`address_2` AS a_address_2, 
        a.`address_3` AS a_address_3,
        a.`state` AS a_state,
        a.`postcode` AS a_postcode,
        a.`trust_account_software`,
        a.`tas_connected`,
        a.`auto_renew`,
        aght.priority,
        apmd.abbreviation,
        
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type
        ";

        $custom_where = '( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )';

        $params = array(
            'sel_query' => $sel_query,
            // 'custom_where' => $custom_where, 
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type', 'agency_priority', 'agency_priority_marker_definition'),
            
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'agency_filter' => $agency_filter,
            'date'=>$date_filter,
            'search' => $search,
            'postcodes' => $postcodes,
            
            'country_id' => $country_id,

            'limit' => $per_page,
            'offset' => $offset,

            'sort_list' => array(
                array(
                    'order_by' => 'j.created',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['lists'] = $this->jobs_model->get_jobs($params);
        $data['sql_query'] = $this->db->last_query(); //Show query on About

        // all rows
        $sel_query = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type'),

            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'agency_filter' => $agency_filter,
            'date'=>$date_filter,
            'search' => $search,
            'postcodes' => $postcodes,

        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

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
            'join_table' => array('job_type','alarm_job_type'),

            'sort_list' => array(
                array(
                    'order_by' => 'j.`job_type`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['job_type_filter_json'] = json_encode($params);


        //Services Filter
        $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type'),

            'sort_list' => array(
                array(
                    'order_by' => 'j.`service`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['service_filter_json'] = json_encode($params); 


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
            'join_table' => array('job_type','alarm_job_type'),

            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['state_filter_json'] = json_encode($params);


        //Agency  filter
        $sel_query = "DISTINCT(a.`agency_id`), a.`auto_renew`,
        a.`agency_name`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type'),

            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['agency_filter_json'] = json_encode($params);

        // Region Filter ( get distinct state )
        $sel_query = "p.`state`";
        $region_filter_arr = array(
            'sel_query' => $sel_query,
            'custom_where_arr' => array(
                $custom_where
            ),
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'job_status' => $job_status,
            
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type'),

            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                )
            ),
            'group_by' => 'p.`state`',
            'display_query' => 0
        );
        $data['region_filter_json'] = json_encode($region_filter_arr);
    
        $pagi_links_params_arr = array(
            'agency_filter' => $agency_filter,
            'job_type_filter' => $job_type_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'date_filter' => $date_filter,
            'search' => $search,
            'sub_region_ms' => $sub_region_ms
        );
        $pagi_link_params = '/jobs/service_due/?'.http_build_query($pagi_links_params_arr);

        // pagination settings
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $pagi_link_params;
       // $config['base_url'] = "/jobs/service_due?jobType={$job_filter}&service={$service_filter}&state={$state_filter}&agency={$agency_filter}&region={$region_filter}&date={$date_filter}&search={$search}";
        
        $this->pagination->initialize($config);
        
        $data['pagination'] = $this->pagination->create_links();
        
        // pagination count
        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);


        $this->load->view('templates/inner_header', $data);
        $this->load->view('jobs/service_due_jobs', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    /**
     * Get/Display To be Booked Jobs list
     */
    public function to_be_booked(){
        
        $data['start_load_time'] = microtime(true);
        $data['title'] = "To Be Booked";
        $uri = "/jobs/to_be_booked";
        $data['uri'] = $uri;

        $job_status_filter = $this->input->get_post('job_status_filter');
        if( $job_status_filter == '-1' ){
            $job_status = null;
        }else{
            $job_status = 'To Be Booked';
        }
        

        $custom_filter = $this->input->get_post('custom_filter');
        if( $custom_filter != '' ){
            $custom_where = $custom_filter;            
        }

        $agency_priority_filter = $this->input->get_post('agency_priority_filter');
        if ($agency_priority_filter != "") {
            $agency_priority_custom_where = "aght.priority = {$agency_priority_filter}";
        }
        

        $country_id = $this->config->item('country');
        $is_urgent = $this->input->get_post('is_urgent');

        $job_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $state_filter = $this->input->get_post('state_filter');
        $agency_filter = $this->input->get_post('agency_filter');
        $date_filter = ($this->input->get_post('date_filter')!="")?$this->system_model->formatDate($this->input->get_post('date_filter')):NULL;
        $search = $this->input->get_post('search_filter');
        $export = $this->input->get_post('export');
        $show_is_eo = $this->input->get_post('show_is_eo');
        $updated_to_240v_rebook = $this->input->get_post('updated_to_240v_rebook');
        $is_sales = $this->input->get_post('is_sales');

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }

        $order_by = ( $this->input->get_post('order_by') !='' )?$this->input->get_post('order_by'):'j.date';
        $sort = ( $this->input->get_post('sort') !='' )?$this->input->get_post('sort'):'asc';
        
        // pagination
        $per_page = $this->config->item('pagi_per_page');    
        $offset = $this->input->get_post('offset');

        $sel_query = "
        j.`id` AS jid,
        j.`status` AS j_status,
        j.`service` AS j_service,
        j.`created` AS j_created,
        j.`date` AS j_date,
        j.`start_date`,
        j.`due_date`,
        j.`property_jobs_count`,
        j.`comments` AS j_comments,
        j.`job_price` AS j_price,
        j.`job_type` AS j_type,
        j.`property_jobs_count`,
        j.`property_vacant`,
        j.`urgent_job`,
        j.`job_reason_id`,
        DATEDIFF(CURDATE(), Date(j.`created`)) AS age,
        DATEDIFF(Date(p.`retest_date`), CURDATE()) AS deadline,
        j.`is_eo`,
        
        p.`property_id` AS prop_id, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3,
        p.`state` AS p_state,
        p.`postcode` AS p_postcode,
        p.`comments` AS p_comments, 
        p.`no_dk`,
        p.`holiday_rental`,
        
        a.`agency_id` AS a_id,
        a.`agency_name` AS agency_name,
        a.`phone` AS a_phone,
        a.`address_1` AS a_address_1, 
        a.`address_2` AS a_address_2, 
        a.`address_3` AS a_address_3,
        a.`state` AS a_state,
        a.`postcode` AS a_postcode,
        a.`trust_account_software`,
        a.`tas_connected`,
        a.`allow_dk`,
        aght.priority,
        apmd.abbreviation,
        
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type,

        sa.`is_electrician`
        ";

        // tables joined
        $join_table_array = array('job_type','alarm_job_type','staff_accounts', 'agency_priority', 'agency_priority_marker_definition');
        if($updated_to_240v_rebook == 1){
            $join_table_array[] = 'job_markers';
        
            if($show_is_eo == 1){
                $custom_where .= " ((j.`job_type` = '240v Rebook' OR jm.`job_type_change` = 1) OR j.`is_eo`=1) AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )  ";
            } else {
                $custom_where .= " (j.`job_type` = '240v Rebook' OR jm.`job_type_change` = 1) AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL ) ";
            }
        } else {
            if($show_is_eo == 1){
                $custom_where .= " j.`is_eo`=1 AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL ) ";
            }
        }

        $params = array(
            'sel_query' => $sel_query,
            // 'custom_where' => $custom_where,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'job_status' => $job_status,
            'join_table' => $join_table_array,
            'custom_where_arr' => array($custom_where, $agency_priority_custom_where),
            
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'agency_filter' => $agency_filter,
            'date'=> $date_filter,
            'search' => $search,
            'postcodes' => $postcodes,
            'is_urgent' => $is_urgent,
            'is_sales' => $is_sales,
            
            'country_id' => $country_id,

            'sort_list' => array(
                array(
                    'order_by' => $order_by,
                    'sort' => $sort,
                ),
            ),
            'display_query' => 0
        );

        if( $updated_to_240v_rebook == 1 ){
            $params['group_by'] =  'j.`id`';
        }

        // export should show all
        if ( $export != 1 ){ 
            $params['limit'] = $per_page;
            $params['offset'] = $offset;
        }

        $jobs = $this->jobs_model->get_jobs($params)->result_array();
        /*
        echo "====Jobs Data <br /><br />";
        echo "<pre>";
        print_r($jobs);
        echo "</pre>";
        exit();
        */

        $data['sql_query'] = $this->db->last_query();
        $jobsById = []; // make an object/map so you can access them by id easier later

        $jobsPerRegion = []; // map agencies per region
       
        for ($x = 0; $x < count($jobs); $x++) {
            $job = &$jobs[$x];

            $job['last_contact'] = null;
            $job['last_contact_old'] = null;
            $job['region'] = null; // empty for now
            $jobsById[$job['jid']] = &$job; // take note with the &. reference the id to the object

             #generate an empty array for later. there could be an unsafe shortcut for this
             if (!isset($jobsPerRegion[$job['p_postcode']])) {
                $jobsPerRegion[$job['p_postcode']] = [];
            }

            $jobsPerRegion[$job['p_postcode']][] = &$job; // add a reference of job to the region

        }

        $jobyIds = array_keys($jobsById); //ge job ids

        $regionCodes = array_keys($jobsPerRegion); // get postcodes

        if(!empty($jobsPerRegion)){
            $regions =  $this->system_model->getRegion_v2($regionCodes)->result_array();
        }

        foreach ($regions as $region) {
            for ($x = 0; $x < count($jobsPerRegion[$region['postcode']]); $x++) {
              $jobsPerRegion[$region['postcode']][$x]['region'] = $region;
            }
        }

        //Last contact
        
        
        if(!empty($jobyIds)){
            $last_contact_old = $this->db->select('job_id, MAX(eventdate) as last_contact_old')
            ->from('job_log')
            ->where_in('job_id', $jobyIds)
            ->group_by('job_id')
            ->get()->result_array();
        }
        
        /*
        echo "====Old Log Results: <br /><br />";
        print_r($last_contact_old);
        echo "<br /><br />";
        */
        
        if(!empty($jobyIds)){
            $last_contact = $this->db->select('job_id, MAX(created_date) as last_contact')
            ->from('logs')
            ->where_in('job_id', $jobyIds)
            ->group_by('job_id')
            ->get()->result_array();
        }
        
        //echo "====Last Contact Query: <br /><br />";
        //echo $this->db->last_query();
        //exit();

        foreach ($last_contact as $d) {
            $jobsById[$d['job_id']]['last_contact'] = $d['last_contact'];
        }  
        

        foreach ($last_contact_old as $e) {
            $jobsById[$e['job_id']]['last_contact_old'] = $e['last_contact_old'];
        }  
        //echo "====Last Contact <br /><br />";
        //print_r($jobsById);
        //exit();

        if ($export == 1) { //EXPORT         

            // file name
            $date_export = date('YmdHis');
            $filename = "to_be_booked_{$date_export}.csv";

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            // file creation 
            $csv_file = fopen('php://output', 'w');            

            $header = array("Date","Job Type","Age","Service","Price","Address","State","Region","Agency","Job Number","Last Contact","Start Date","Vacant","DK");
            fputcsv($csv_file, $header);
            
            foreach ($jobs as $row){ 

                $csv_row = [];  
                
                $prop_address = $row['p_address_1']." ".$row['p_address_2'].", ".$row['p_address_3'];

                $csv_row[] = ( $this->system_model->isDateNotEmpty($row['j_date']) )?date('d/m/Y', strtotime($row['j_date'])):null;
                $csv_row[] = $this->gherxlib->getJobTypeAbbrv($row['j_type']);
                $csv_row[] = $row['age'];
                $csv_row[] = $row['ajt_type'];
                $csv_row[] = $row['j_price'];
                $csv_row[] = $prop_address;
                $csv_row[] = $row['p_state'];
                $csv_row[] = $row['region']['subregion_name'];
                $csv_row[] = $row['agency_name'];
                $csv_row[] = $row['jid'];
                $csv_row[] = ( $this->system_model->isDateNotEmpty($row['last_contact']) )?date("d/m/Y",strtotime($row['last_contact'])):null;
                $csv_row[] = ( ( $row['j_type']=='Change of Tenancy' || $row['j_type']=='Lease Renewal' ) && $this->system_model->isDateNotEmpty($row['start_date']) )?date('d/m/Y',strtotime($row['start_date'])):null;
                $csv_row[] = ($row['property_vacant']==1)?'YES':null;
                $csv_row[] = ( $row['no_dk']==1 || $row['holiday_rental'] == 1 || ( is_numeric($row['allow_dk']) && $row['allow_dk'] == 0 ) )?'NO':null;
                
                fputcsv($csv_file,$csv_row); 

            }
        
            fclose($csv_file); 
            exit; 

        }else{

            $data['jobs'] = $jobs;
            
            /*
            echo "====Jobs Data: <br /><br />";
            echo "<pre>";
            print_r($data['jobs']);
            echo "</pre>";
            echo "<br /><br />";
            
            $data['last_contact'] = $last_contact;
            
            echo "====Last Contact Data: <br /><br />";
            echo "<pre>";
            print_r($data['last_contact']);
            echo "</pre>";
            echo "<br /><br />";
            exit();
            */

            //Total rows
            $sel_query = "j.`id`";
            $params = array(
                'sel_query' => $sel_query,
                // 'custom_where' => $custom_where,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'job_status' => $job_status,
                'join_table' => $join_table_array,
                'custom_where_arr' => array($custom_where, $agency_priority_custom_where),
                
                'job_type' => $job_filter,
                'service_filter' => $service_filter,
                'state_filter' => $state_filter,
                'agency_filter' => $agency_filter,
                'date'=> $date_filter,
                'search' => $search,
                'postcodes' => $postcodes,
                'is_urgent' => $is_urgent,
                'country_id' => $country_id,
                'is_sales' => $is_sales

            );

            if( $updated_to_240v_rebook == 1 ){
                $params['group_by'] =  'j.`id`';
            }

            $query = $this->jobs_model->get_jobs($params);
            $total_rows = $query->num_rows();

            // update page total > only for p.is_sales
            if($is_sales==1){
                $page_url = '/jobs/to_be_booked?is_sales=1';
                $page_tot_params = array(
                    'page' => $page_url,
                    'total' => $total_rows
                );
                $this->system_model->update_page_total($page_tot_params);
            }
            

            //Agency  filter
            $sel_query = "DISTINCT(a.`agency_id`), a.`agency_name`, aght.`priority`, apmd.`abbreviation`";
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'job_status' => $job_status,
                'join_table' => array('job_type','alarm_job_type', 'agency_priority', 'agency_priority_marker_definition'),

                'sort_list' => array(
                    array(
                        'order_by' => 'a.`agency_name`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['agency_filter_json'] = json_encode($params);

            //Job type Filter
            $sel_query = "DISTINCT(j.`job_type`), `j.job_type`";
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'job_status' => $job_status,
                'join_table' => array('job_type','alarm_job_type'),

                'sort_list' => array(
                    array(
                        'order_by' => 'j.`job_type`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['job_type_filter_json'] = json_encode($params);

            //Services Filter
            $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'job_status' => $job_status,
                'join_table' => array('job_type','alarm_job_type'),

                'sort_list' => array(
                    array(
                        'order_by' => 'j.`service`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['service_filter_json'] = json_encode($params);  

            //State filter
            $sel_query = "DISTINCT(p.`state`), p.`state`";
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'job_status' => $job_status,
                'join_table' => array('job_type','alarm_job_type'),

                'sort_list' => array(
                    array(
                        'order_by' => 'p.`state`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['state_filter_json'] = json_encode($params);  

            // Region Filter ( get distinct state )
            $sel_query = "p.`state`";
            $region_filter_arr = array(
                'sel_query' => $sel_query,
                
                'del_job' => 0,
                'p_deleted' => 0,
                'a_status' => 'active',                 
                'job_status' => $job_status,
                'join_table' => $join_table_array,
                'country_id' => $country_id,
                
                'sort_list' => array(
                    array(
                        'order_by' => 'p.`state`',
                        'sort' => 'ASC',
                    )
                ),
                'group_by' => 'p.`state`',
                'display_query' => 0
            );
            $data['region_filter_json'] = json_encode($region_filter_arr);

            $filters_arr = array(
                'agency_filter' => $agency_filter,
                'job_type_filter' => $job_filter,
                'service_filter' => $service_filter,
                'date_filter' => $this->input->get_post('date_filter'),
                'search_filter' => $search,
                'sub_region_ms' => $this->input->get_post('sub_region_ms'),
                'is_urgent' => $is_urgent,
                'state_filter' => $state_filter,
                'show_is_eo' => $show_is_eo,
                'updated_to_240v_rebook' => $updated_to_240v_rebook,
                'is_sales' => $is_sales,
                'agency_priority_filter' => $agency_priority_filter
            );

            // header sort paramerts needs to exclude sort variables
            $data['header_link_params'] = $filters_arr;

            // append sort variables
            $filters_arr['order_by'] = $this->input->get_post('order_by');
            $filters_arr['sort'] = $this->input->get_post('sort');

            // pagination link
            $pagi_link_params = "{$uri}/?".http_build_query($filters_arr);                

            // explort link
            $data['export_link'] = "{$uri}/?export=1&".http_build_query($filters_arr);                    


            // pagination settings
            $config['page_query_string'] = TRUE;
            $config['query_string_segment'] = 'offset'; // rename offset variable
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

            $this->load->view('templates/inner_header', $data);
            $this->load->view('jobs/to_be_booked', $data);
            $this->load->view('templates/inner_footer', $data);

        }
        
        
    }

    /**
     * Get/Display To be invoiced list
     */
    public function to_be_invoiced()
    {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "To Be Invoiced";

        $job_status="To Be Invoiced";

        $agency_filter = $this->input->get_post('agency_filter');
        $country_id = $this->config->item('country');
        $job_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $state_filter = $this->input->get_post('state_filter');
        $date_filter = ( $this->input->get_post('date_filter') !='' )?$this->system_model->formatDate($this->input->get_post('date_filter')):NULL;
        $search = $this->input->get_post('search_filter');

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }       

        // pagination
        $per_page = 250;
        $offset = $this->input->get_post('offset');
        
        if(!empty($date_filter)){
            //$custom_where = "CAST(j.`start_date` AS Date)  >= '{$startDate_filter}'";
            //$custom_where = "CAST(j.`start_date` AS Date)  >= '{$date_filter}'";
            $custom_where = "j.`date`  = '{$date_filter}'";
        }else{
            $custom_where = NULL;
        }
        
        $sel_query .= " 
            j.`id` AS jid, 
            j.`job_type`,
            j.`status` AS jstatus,
            j.`service` AS jservice,
            j.`created` AS jcreated,                
            j.`date` AS jdate,
            ps.`subscription_date`,
            j.`job_price`,
            j.`start_date`,
            j.`due_date`,
            j.`comments`,
            j.`job_reason_id`,
            j.`job_reason_comment`,
            j.`urgent_job`,
            j.`client_emailed`,
            j.`door_knock`,
            j.`booked_with`,
            j.`sms_sent`,
            j.`assigned_tech`,
            j.`ts_completed`,
            j.`completed_timestamp`,
            j.`time_of_day`,
            j.`work_order`,
            j.`at_myob`,
            j.`no_dates_provided`,
            j.`agency_approve_en`,
            j.`ss_quantity`,
            j.`key_access_required`,
            j.`preferred_time`,
            j.`property_vacant`,
            j.`tech_comments`,
            j.`precomp_jobs_moved_to_booked`,
            j.`sms_sent_no_show`,
            j.`sms_sent_merge`,
            j.`bne_to_call_notes`,
            j.`assigned_tech`,
            
            p.`property_id`,
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3, 
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            
            p.`tenant_firstname1`,
            p.`tenant_lastname1`,
            p.`tenant_firstname2`,
            p.`tenant_lastname2`,
            p.`tenant_firstname3`,
            p.`tenant_lastname3`,
            p.`tenant_firstname4`,
            p.`tenant_lastname4`,
            
            p.`tenant_mob1`,
            p.`tenant_mob2`,
            p.`tenant_mob3`,
            p.`tenant_mob4`,
            
            p.`tenant_ph1`,
            p.`tenant_ph2`,
            p.`tenant_ph3`,
            p.`tenant_ph4`,
            
            p.`tenant_email1`,
            p.`tenant_email2`,
            p.`tenant_email3`,
            p.`tenant_email4`,
            
            p.`comments` AS p_comments,
            p.`holiday_rental`,
            
            p.`prop_upgraded_to_ic_sa`,

            a.`agency_id`,
            a.`agency_name`,
            a.`account_emails`,
            a.`send_emails`,
            a.`allow_dk`,
            a.`phone` AS a_phone,
            a.`auto_renew` AS a_auto_renew,
            a.`franchise_groups_id`,
            aght.priority,
            apmd.abbreviation,
            
            jr.`name` AS jr_name,
            
            sa.`FirstName`,
            sa.`LastName`,
            sa.`StaffID` AS staff_id
        ";

        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'join_table' => array('property','agency','alarm_job_type','job_reason','staff_accounts','property_subscription', 'agency_priority', 'agency_priority_marker_definition'),
            'job_status' => $job_status,
            'job_type' => $job_filter,
            'agency_filter' => $agency_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'region_filter' => $region_filter,
            'custom_where' => $custom_where,
            'search' => $search,
            'postcodes' => $postcodes,

            //'limit' => $per_page,
            'offset' => $offset,
            
            'sort_list' => array(
                array(
                    'order_by' => 'j.urgent_job',
                    'sort' => 'DESC',
                ),
                array(
                    'order_by' => 'j.job_type',
                    'sort' => 'ASC',
                ),
                array(
                    'order_by' => 'p.address_3',
                    'sort' => 'ASC',
                ),
            ),
        );

        $data['lists'] = $this->jobs_model->get_jobs($params);

        // all rows
        $sel_query = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('property','agency','alarm_job_type','job_reason','staff_accounts'),
            'agency_filter' => $agency_filter,
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'region_filter' => $region_filter,
            'custom_where' => $custom_where,
            'search' => $search,
            'postcodes' => $postcodes,
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;
        
        //Agency name filter
        $sel_query = "DISTINCT(a.`agency_id`), a.`agency_name`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'a_filter' => 1,
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type'),
            // 'custom_where' => $custom_where,
            'custom_where_arr' => array(
                $custom_where
            ),
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['agency_filter_json'] = json_encode($params);

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
            'join_table' => array('job_type','alarm_job_type'),
            'sort_list' => array(
                array(
                    'order_by' => 'j.urgent_job',
                    'sort' => 'DESC',
                ),
                array(
                    'order_by' => 'j.job_type',
                    'sort' => 'ASC',
                ),
                array(
                    'order_by' => 'p.address_3',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['job_type_filter_json'] = json_encode($params);

        //Services Filter
        $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type'),
            'sort_list' => array(
                array(
                    'order_by' => 'j.urgent_job',
                    'sort' => 'DESC',
                ),
                array(
                    'order_by' => 'j.job_type',
                    'sort' => 'ASC',
                ),
                array(
                    'order_by' => 'p.address_3',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['service_filter_json'] = json_encode($params);   


        //State Filter
        $sel_query = "DISTINCT(p.`state`)";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type'),
            'sort_list' => array(
                array(
                    'order_by' => 'j.urgent_job',
                    'sort' => 'DESC',
                ),
                array(
                    'order_by' => 'j.job_type',
                    'sort' => 'ASC',
                ),
                array(
                    'order_by' => 'p.address_3',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['state_filter_json'] = json_encode($params);


        // Region Filter ( get distinct state )
        $sel_query = "p.`state`";
        $region_filter_arr = array(
            'sel_query' => $sel_query,
            'custom_where_arr' => array(
                $custom_where
            ),
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'job_status' => $job_status,
            
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type'),

            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                )
            ),
            'group_by' => 'p.`state`',
            'display_query' => 0
        );
        $data['region_filter_json'] = json_encode($region_filter_arr);
        
        
        /*$pagi_links_params_arr = array(
            'agency_filter' => $agency_filter,
            'job_type_filter' => $job_type_filter,
            'service_filter' => $service_filter,
            'date_filter' => $date_filter,
            'search_filter' => $search,
            'sub_region_ms' => $sub_region_ms
        );
        $pagi_link_params = '/jobs/to_be_invoiced/?'.http_build_query($pagi_links_params_arr);

        // pagination settings
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $pagi_link_params;
               
        $this->pagination->initialize($config);
        
        $data['pagination'] = $this->pagination->create_links();*/
        
        // pagination count
        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

        $this->load->view('templates/inner_header', $data);
        $this->load->view('jobs/to_be_invoiced', $data);
        $this->load->view('templates/inner_footer', $data);
    }
    
    public function vacant()
    {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Vacant Jobs";
        $page_url = '/jobs/vacant';

        $country_id = $this->config->item('country');
 
        $agency_filter = $this->input->get_post('agency_filter');
        $job_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $state_filter = $this->input->get_post('state_filter');
        $date_filter = ( $this->input->get_post('date_filter') !='' )?$this->system_model->formatDate($this->input->get_post('date_filter')):NULL;
        $search = $this->input->get_post('search_filter');
        
        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }
        
        $agency_priority_filter = $this->input->get_post('agency_priority_filter');
        if ($agency_priority_filter != "") {
            $agency_priority_custom_where = "aght.priority = {$agency_priority_filter}";
        }

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $sel_query = "
        j.`id` AS jid,
        j.`status` AS j_status,
        j.`service` AS j_service,
        j.`created` AS j_created,
        j.`date` AS j_date,
        j.`comments` AS j_comments,
        j.`job_price` AS j_price,
        j.`job_type` AS j_type,
        j.`start_date`,
        j.`due_date`,
        j.`no_dates_provided`,
        j.`bne_to_call_notes`,
        j.`urgent_job`,
        j.`job_reason_id`,
        
        p.`property_id` AS prop_id, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3,
        p.`state` AS p_state,
        p.`postcode` AS p_postcode,
        p.`comments` AS p_comments, 
        
        a.`agency_id` AS a_id,
        a.`agency_name` AS agency_name,
        a.`phone` AS a_phone,
        a.`address_1` AS a_address_1, 
        a.`address_2` AS a_address_2, 
        a.`address_3` AS a_address_3,
        a.`state` AS a_state,
        a.`postcode` AS a_postcode,
        a.`trust_account_software`,
        a.`tas_connected`,
        aght.priority,
        apmd.abbreviation,
        
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type
        ";
        
        // By default exclude jobs that has a start date 3 months from now
        $custom_where = "j.property_vacant = 1 AND j.status NOT IN('Completed','Cancelled','Merged Certificates','Booked','Pre Completion') AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL ) AND (j.start_date < DATE(NOW() + INTERVAL 3 MONTH) OR j.start_date IS NULL)";

        // If all jobs is checked show all jobs
        if(!empty($this->input->get_post('show_all_job'))) {
            $custom_where = "j.property_vacant = 1 AND j.status NOT IN('Completed','Cancelled','Merged Certificates','Booked','Pre Completion') AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )";
        }

        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type','agency_priority', 'agency_priority_marker_definition'),
            'custom_where_arr' => array($custom_where, $agency_priority_custom_where),

            'agency_filter' => $agency_filter,
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'date'=>$date_filter,
            'search'=>$search,
            'postcodes' => $postcodes,

            'limit' => $per_page,
            'offset' => $offset,
            
            'custom_sort' => "
                j.`urgent_job` DESC, 
                ( 
                    CASE WHEN ( 
                        CURDATE( ) >= ( j.`due_date` - INTERVAL 3 DAY ) AND
                        j.`due_date` != '1970-01-01' AND
                        j.`due_date` != '0000-00-00' 
                    ) THEN 1 ELSE 0 END 
                ) DESC,
                j.created ASC
            ",

            'display_query' => 0
        );
        $data['lists'] = $this->jobs_model->get_jobs($params);
        $data['sql_query'] = $this->db->last_query(); //Show query on About
        
        
        // all rows
        $sel_query = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type','agency_priority'),
            'custom_where_arr' => array($custom_where, $agency_priority_custom_where),

            'agency_filter' => $agency_filter,
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'date'=>$date_filter,
            'search'=>$search,
            'postcodes' => $postcodes,
        );
        $query = $this->jobs_model->get_jobs($params);
       
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        //Agency name filter
        $sel_query = "DISTINCT(a.`agency_id`),
        a.`agency_name`, aght.`priority`, apmd.`abbreviation`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type', 'agency_priority', 'agency_priority_marker_definition'),
            'custom_where' => $custom_where,
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['agency_filter_json'] = json_encode($params);

        
        //Job type Filter
        $sel_query = "DISTINCT(j.`job_type`),
                `j.job_type`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type'),
            'custom_where' => $custom_where,
            'sort_list' => array(
                array(
                    'order_by' => 'j.`job_type`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['job_type_filter_json'] = json_encode($params);

        //Services Filter
        $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type'),
            'custom_where' => $custom_where,
            'sort_list' => array(
                array(
                    'order_by' => 'j.`service`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['service_filter_json'] = json_encode($params);   

        //State Filter
        $sel_query = "DISTINCT(p.`state`)";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type'),
            'custom_where' => $custom_where,
            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['state_filter_json'] = json_encode($params);

        // Region Filter ( get distinct state )
        $sel_query = "p.`state`";
        $region_filter_arr = array(
            'sel_query' => $sel_query,
            'custom_where_arr' => array(
                $custom_where
            ),
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'job_status' => $job_status,
            
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type'),

            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                )
            ),
            'group_by' => 'p.`state`',
            'display_query' => 0
        );
        $data['region_filter_json'] = json_encode($region_filter_arr);

        $pagi_links_params_arr = array(
            'agency_filter' => $agency_filter,
            'job_type_filter' => $job_type_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'date_filter' => $date_filter,
            'search_filter' => $search,
            'sub_region_ms' => $sub_region_ms,
            'agency_priority_filter' => $agency_priority_filter
        );
        $pagi_link_params = '/jobs/vacant/?'.http_build_query($pagi_links_params_arr);

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

        // echo "<pre>";
        // var_dump($data);
        // die();
        $this->load->view('templates/inner_header', $data);
        $this->load->view('jobs/vacant_jobs', $data);
        $this->load->view('templates/inner_footer', $data);
    }

     /**
     * Update access note Comments via Ajax
     */
    public function updateJobAccessNotes(){
        $data['status'] = false;
        $job_id = $this->input->post('job_id');
        $j_comments = $this->input->post('j_comments');

        $data = array('access_notes'=>$j_comments);
        $update_sql = $this->jobs_model->update_job($job_id,$data);

        if($update_sql){
            $data['status'] = true;
        }

        echo json_encode($data);
    }

     /**
     * Update Job Comments via Ajax
     */
    public function updateJobComments(){
        $data['status'] = false;
        $job_id = $this->input->post('job_id');
        $j_comments = $this->input->post('j_comments');

        $data = array('comments'=>$j_comments);
        $update_sql = $this->jobs_model->update_job($job_id,$data);

        if($update_sql){
            $data['status'] = true;
        }

        echo json_encode($data);
    }

    
    /**
     * Get/Display To be MM Needs Processing list
     */
    public function maintenance_software_pre_com(){
        $data['start_load_time'] = microtime(true);
        $data['title'] = "MM Needs Processing";

        $job_status="";

        $date_filter = ( $this->input->get_post('date_filter') !='' )?$this->system_model->formatDate($this->input->get_post('date_filter')):NULL;
        $search = $this->input->get_post('search_filter');
        $maintenance_program_filter = $this->input->get_post('maintenance_program');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');
        
        if($this->input->get_post('date')!=""){
            $custom_where = "CAST(j.`start_date` AS Date)  >= '{$date_filter}'";
        }else{
            $custom_where = NULL;
        }
        
        $sel_query .= " 
            j.`dha_need_processing`,
            j.`id` AS jid, 
            j.`job_type`,
            j.`status` AS jstatus,
            j.`service` AS jservice,
            j.`created` AS jcreated,                
            j.`date` AS jdate,
            j.`job_price`,
            j.`start_date`,
            j.`due_date`,
            j.`comments`,
            j.`job_reason_id`,
            j.`job_reason_comment`,
            j.`urgent_job`,
            j.`client_emailed`,
            j.`door_knock`,
            j.`booked_with`,
            j.`sms_sent`,
            j.`assigned_tech`,
            j.`ts_completed`,
            j.`completed_timestamp`,
            j.`time_of_day`,
            j.`work_order`,
            j.`at_myob`,
            j.`no_dates_provided`,
            j.`agency_approve_en`,
            j.`ss_quantity`,
            j.`key_access_required`,
            j.`preferred_time`,
            j.`property_vacant`,
            j.`tech_comments`,
            j.`precomp_jobs_moved_to_booked`,
            j.`sms_sent_no_show`,
            j.`sms_sent_merge`,
            j.`bne_to_call_notes`,
            j.`assigned_tech`,
            
            p.`property_id`,
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3, 
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            
            p.`tenant_firstname1`,
            p.`tenant_lastname1`,
            p.`tenant_firstname2`,
            p.`tenant_lastname2`,
            p.`tenant_firstname3`,
            p.`tenant_lastname3`,
            p.`tenant_firstname4`,
            p.`tenant_lastname4`,
            
            p.`tenant_mob1`,
            p.`tenant_mob2`,
            p.`tenant_mob3`,
            p.`tenant_mob4`,
            
            p.`tenant_ph1`,
            p.`tenant_ph2`,
            p.`tenant_ph3`,
            p.`tenant_ph4`,
            
            p.`tenant_email1`,
            p.`tenant_email2`,
            p.`tenant_email3`,
            p.`tenant_email4`,
            
            p.`comments` AS p_comments,
            p.`holiday_rental`,
            
            p.`prop_upgraded_to_ic_sa`,
            p.`qld_new_leg_alarm_num`,

            a.`agency_id`,
            a.`agency_name`,
            a.`account_emails`,
            a.`send_emails`,
            a.`allow_dk`,
            a.`phone` AS a_phone,
            a.`auto_renew` AS a_auto_renew,
            a.`franchise_groups_id`,
            
            jr.`name` AS jr_name,
            
            sa.`FirstName`,
            sa.`LastName`,
            sa.`StaffID` AS staff_id,
            m.name as m_name
        ";

        $custom_where = " j.`id` > 0 
              AND am.`maintenance_id` > 0 
              AND am.`status` = 1 
              AND m.`status` = 1 
              AND ( j.`assigned_tech` != 1 OR j.`assigned_tech` IS NULL ) 
              AND j.`dha_need_processing` = 1 
              AND (
                j.`status` = 'Merged Certificates' 
                OR j.`status` = 'Completed'
              )  ";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'join_table' => array('property','agency','alarm_job_type','job_reason','staff_accounts','maintenance'),
            'job_status' => $job_status,
            'maintenance_program_filter' => $maintenance_program_filter,
            'custom_where' => $custom_where,
            'search' => $search,

            'limit' => $per_page,
            'offset' => $offset,
            
            'sort_list' => array(
                array(
                    'order_by' => 'a.agency_name',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['lists'] = $this->jobs_model->get_jobs($params);

        // all rows
        $sel_query = "j.`id` ";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('property','agency','alarm_job_type','job_reason','staff_accounts','maintenance'),
            'maintenance_program_filter' => $maintenance_program_filter,
            'custom_where' => $custom_where,
            'search' => $search,
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->num_rows();

        //Maintenance Filter
        $sel_query = "DISTINCT(am.`maintenance_id`), m.name as m_name";
        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'job_status' => $job_status,
            'country_id' => $country_id,
            'join_table' => array('maintenance'),
            'sort_list' => array(
                array(
                    'order_by' => 'm.`name`',
                    'sort' => 'ASC',
                ),
            ),
            'display_query' => 0
        );
        $data['maintenance_filter_json'] = json_encode($params);

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


        $this->load->view('templates/inner_header', $data);
        $this->load->view('jobs/dha_pre_com_jobs', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    /**
     * Get/Display To be MM Needs Processing list
     */
    public function platform_invoicing(){

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Platform Invoicing"; 

        $job_status="";

        $date_filter = ( $this->input->get_post('date_filter') !='' )?$this->system_model->formatDate($this->input->get_post('date_filter')):NULL;
        $search_filter = $this->input->get_post('search_filter');
        $maint_prog_filter = $this->input->get_post('maint_prog_filter');

        /*
        if(!empty($_POST)){
            print_r($_POST);
            exit();
        }
        */
        
        // pagination
        $per_page = $this->config->item('pagi_per_page');
        
        //$per_page = 3;
        $offset = $this->input->get_post('offset');
        
        if($this->input->get_post('date')!=""){
            $custom_where = "CAST(j.`start_date` AS Date)  >= '{$date_filter}'";
        }else{
            $custom_where = NULL;
        }

        $sel_query .= " 
            j.`id` AS jid, 
            j.`date` AS jdate, 
            j.`work_order`, 
            j.`mm_need_proc_inv_emailed`, 
            j.`client_emailed`, 
            j.`ts_completed`, 
            j.`qld_upgrade_quote_emailed`, 
            j.`dha_need_processing`, 
            p.`property_id`, 
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3, 
            p.`state` AS p_state, 
            p.`prop_upgraded_to_ic_sa`, 
            p.`qld_new_leg_alarm_num`,  
            apd.`api_prop_id`, 
            apd.`api`, 
            a.`agency_id` AS a_id, 
            a.`agency_name`, 
            a.`franchise_groups_id`, 
            a.`palace_supplier_id`, 
            a.`palace_diary_id`, 
            a.`pme_supplier_id`, 
            m.`name` AS m_name, 
            m.`maintenance_id` AS m_id,
            aat.`connection_date`
        ";

        $custom_where = " 
            j.`id` > 0 
            AND j.`del_job` = 0
            AND p.`deleted` = 0
            AND a.`status` = 'active'
            AND ( (am.`maintenance_id` > 0 AND am.`status` = 1 AND m.`status` = 1 AND j.`date` >= am.`updated_date`) OR a.`franchise_groups_id` = 14)
            AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
            AND j.`dha_need_processing` = 1 
            AND ( j.`assigned_tech` != 1 OR j.`assigned_tech` IS NULL ) 
            AND ( j.`status` = 'Merged Certificates' OR j.`status` = 'Completed')  
        ";

        $params = array(
            'sel_query' => $sel_query,
            'country_id' => $country_id,
            'join_table' => array('property','agency','api_property_data','agency_maintenance','maintenance','agency_api_tokens'),
            'date' => $date_filter,
            'maintenance_program_filter' => $maint_prog_filter,
            'custom_where' => $custom_where,
            'search' => $search_filter,

            'limit' => $per_page,
            'offset' => $offset,
            
            'sort_list' => array(
                array(
                    'order_by' => 'a.agency_name',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['lists'] = $this->jobs_model->get_jobs_invoicing($params);
        //echo $this->db->last_query();
        //exit();

        // all rows
        $sel_query = "j.`id` ";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'date' => $date_filter,
            'maintenance_program_filter' => $maint_prog_filter,
            'search' => $search_filter,
            'join_table' => array('property','agency','api_property_data','agency_maintenance','maintenance','agency_api_tokens'),
            'custom_where' => $custom_where,
        );
        $query = $this->jobs_model->get_jobs_invoicing($params);
        $total_rows = $query->num_rows();

        //Maintenance Filter
        $sel_query = "DISTINCT(am.`maintenance_id`), m.name as m_name";
        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'job_status' => $job_status,
            'date' => $date_filter,
            'maintenance_program_filter' => $maint_prog_filter,
            'search' => $search_filter,
            'country_id' => $country_id,
            'join_table' => array('maintenance'),
            'sort_list' => array(
                array(
                    'order_by' => 'm.`name`',
                    'sort' => 'ASC',
                ),
            ),
            'display_query' => 0
        );
        $data['maintenance_filter_json'] = json_encode($params);

        $pagi_links_params_arr = array(
            'maint_prog_filter' => $maint_prog_filter,
            'date_filter' => $date_filter,
            'search_filter' => $search_filter,
        );
        $pagi_link_params = '/jobs/platform_invoicing/?'.http_build_query($pagi_links_params_arr);

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

        $this->load->view('templates/inner_header', $data);
        $this->load->view('jobs/dha_pre_com_jobs', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    /**
     * Assign/move map tech via ajax
     */
    public function ajax_move_to_maps(){

        $job_id = $this->input->post('job_id');
        $tech_id = $this->input->post('tech_id');
        $date = $this->system_model->formatDate($this->input->post('date'),'Y-m-d');
        $date_format = $this->system_model->formatDate($this->input->post('date'),'d/m/Y');
        $page_type = $this->input->post('page_type');

        foreach($job_id as $val){
            if($val > 0){
                //get tech name
                $staff_info_params = array(
                    'sel_query' => 'FirstName, LastName',
                    'staff_id' => $tech_id
                );
                $staff_info = $this->gherxlib->getStaffInfo($staff_info_params)->row_array();  
                
                //get old job status
                $job_params = array(
                    'sel_query' => 'j.id AS jid, j.status as j_status',
                    'job_id' => $val,
                    'country_id' => $country_id
                );
                $jobs_aaw = $this->jobs_model->get_jobs($job_params)->row_array();

                //get escalate job reason
                $selected_escalate_job_reasons_query = $this->gherxlib->getEscalateReason($val);
                $selected_escalate_job_reasons_row = $selected_escalate_job_reasons_query->row_array();

                if($page_type == "after_hours" || $page_type == "overdue_nsw_jobs"){ // set pages not needed to update job status
                    //log var
                    $log_type = 1;
                    $log_title = 44;
                    $log_details = "Assigned to <strong>{$staff_info['FirstName']} {$staff_info['LastName']}</strong> from <strong>{$jobs_aaw['j_status']}</strong>, on <strong>{$date_format}</strong>";

                    //post array
                    $data = array(
                        'assigned_tech' => $tech_id
                    );
                    if($this->input->post('date') && $date!=""){
                        $data['date'] = $date;
                    }
                }else{ //set default params value
                    //log var
                    $log_type = 2;
                    if($page_type=='escalate'){ //separate log for escate CHANGED TO TBB button
                        $log_title = 72;
                        $log_details = "Job changed from <strong>Escalate - {$selected_escalate_job_reasons_row['reason_short']}</strong> to <strong>To Be Booked</strong>";
                    }else{
                        $log_title = 44;
                        $log_details = "Assigned to <strong>{$staff_info['FirstName']} {$staff_info['LastName']}</strong> from <strong>{$jobs_aaw['j_status']}</strong>, on <strong>{$date_format}</strong>";
                    }

                    //post array
                    $data = array(
                        'status' => 'To Be Booked',
                        'assigned_tech' => $tech_id
                    );
                    if($this->input->post('date') && $date!=""){
                        $data['date'] = $date;
                    }
                }
                $this->db->where('id',$val);
                $update_query =  $this->db->update('jobs',$data);

                //insert log
                $log_params = array(
                    'title' => $log_title,
                    'details' => $log_details,
                    'display_in_vjd' => 1,
                    'created_by_staff' => $this->session->staff_id,
                    'job_id' => $val
                );
                $this->system_model->insert_log($log_params);
            }
        }

        echo json_encode($data);
        
    }

    /**
     * Update Trust Account Software via ajax
    */
    public function ajax_update_agency_tas(){
        $agency_id = $this->input->post('agency_id');
        $tas_id = $this->input->post('tas_id');

        $data = array('trust_account_software'=>$tas_id, 'tas_connected'=> 0);
        $this->jobs_model->update_agency($agency_id, $data);

        echo json_encode($data);
    }

    /**
     * Update Agency Save notes via ajax
     */
    public function ajax_update_agency_save_notes(){
        $data['status'] = false;
        $agency_id = $this->input->post('agency_id');
        $save_notes_chk = $this->input->post('save_notes_chk');
        $escalate_notes = $this->input->post('escalate_notes');
        $esclate_notes_last_updated_by = $this->session->staff_id;
        
        $data = array(
            'save_notes' => $save_notes_chk,
            'escalate_notes' => $escalate_notes,
            'escalate_notes_ts' => date('Y-m-d H:i:s'),
            'esclate_notes_last_updated_by' => $esclate_notes_last_updated_by
        );
        $update_query = $this->jobs_model->update_agency($agency_id, $data);

        //get staff info to pupulate escalate_note_ts
        $staff_info_params = array(
            'sel_query' => 'FirstName, LastName',
            'staff_id' => $esclate_notes_last_updated_by
        );
        $staff_info = $this->gherxlib->getStaffInfo($staff_info_params)->row_array();

        if($update_query){
            $data['status'] = true;
            $data['date_ts'] = date('d/m/Y H:i');
            $data['update_by'] = $this->system_model->formatStaffName($staff_info['FirstName'],$staff_info['LastName']);
        }

        echo json_encode($data);
    }

    /**
     * ajax_insert_escalate_agency_info via ajax
     */
    public function ajax_insert_escalate_agency_info(){

        $data['result'] = false;
        $agency_id = $this->input->post('agency_id');
        $eai_field = $this->input->post('eai_field');
        $eai_val = $this->input->post('eai_val');
        $country_id = $this->config->item('country');

        $jparams = array(
            'country_id' => $country_id,
            'agency_filter' => $agency_id,
            'date' => date('Y-m-d')
        );
        $eai_sql = $this->gherxlib->getEscalateAgencyInfo($jparams);

        //>>>>>for log
        //get all job in a file
        $esca_params = array(
            'sel_query' => 'j.id AS jid',
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'agency_filter' => $agency_id,
            'job_status' => 'Escalate',
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type','escalate_job_reason'),
            'display_query' => 0
        );
        $escalate_jobs_in_file = $this->jobs_model->get_jobs($esca_params);
        
        //get agency name
        $log_agency_name_query = $this->db->select('agency_name')->from('agency')->where('agency_id',$agency_id)->get();
        $q_row = $log_agency_name_query->row_array();
        $log_agency_name = $q_row['agency_name'];
        //>>>>>for log end

        if($eai_sql->num_rows()>0){
            
            $eai = $eai_sql->row_array();

            //update escalate_agency_info
            if( $eai_field == 'notes' ){
                $this->db->set('notes_timestamp',date("Y-m-d H:i:s"));
            }
            $this->db->set($eai_field,$eai_val);
            $this->db->where('escalate_agency_info_id',$eai['escalate_agency_info_id']);
            $this->db->update('escalate_agency_info');
            //update escalate_agency_info end

            //add log to all jobs in file start
            if(!empty($escalate_jobs_in_file)){
                    foreach($escalate_jobs_in_file->result_array() as $new_row){
                        if($eai_val!=""){ //log detail for not empty notes
                            $details = "Updated the following note to <strong>{$log_agency_name}</strong>: <strong>{$eai_val}</strong>";	
                        }else{ //log for empty notes
                            $details = "Note cleared for <strong>{$log_agency_name}</strong>";	
                        }
                        $log_params = array(
                            'title' => 15,
                            'details' => $details,
                            'display_in_vjd' => 1,
                            'agency_id' => $agency_id,
                            'created_by_staff' => $this->session->staff_id,
                            'job_id' => $new_row['jid']
                        );
                        $this->system_model->insert_log($log_params);
                    }
            }
            //add log to all jobs in file end

            $data['result'] = true;

        }else{

            //insert escalate_agency_info
            $this->db->set('agency_id',$agency_id);
            $this->db->set($eai_field,$eai_val);
            if( $eai_field == 'notes' ){
                $this->db->set('notes_timestamp',date("Y-m-d H:i:s"));
            }
            $this->db->set('country_id',$this->config->item('country'));
           
            $this->db->insert('escalate_agency_info');

            //add log to all jobs in file start
            if(!empty($escalate_jobs_in_file)){
                foreach($escalate_jobs_in_file->result_array() as $new_row){
                    if($eai_val!=""){ //log detail for not empty notes
                        $details = "Added the following note to <strong>{$log_agency_name}</strong>: <strong>{$eai_val}</strong>";		
                    }else{ //log for empty notes
                        $details = "Note cleared for <strong>{$log_agency_name}</strong>";	
                    }
                    $log_params = array(
                        'title' => 15,
                        'details' => $details,
                        'display_in_vjd' => 1,
                        'agency_id' => $agency_id,
                        'created_by_staff' => $this->session->staff_id,
                        'job_id' => $new_row['jid']
                    );
                    $this->system_model->insert_log($log_params);  
                }
            }
            //add log to all jobs in file end

            $data['result'] = true;

        }

        echo json_encode($data);

    }

    //Escalate jobs process via ajax
    public function ajax_process_escalate_jobs(){

        $job_id = $this->input->post('job_id');
        $prop_id = $this->input->post('prop_id');
        $tenants_arr = $this->input->post('tenants_arr');

        //ADD TENANTS START > add tenants if field value exist
        foreach($tenants_arr as $tnt){
            //decode json
            $json_enc = json_decode($tnt);
            $new_tenant_fname = $json_enc->new_tenant_fname;
            $new_tenant_lname = $json_enc->new_tenant_lname;
            $new_tenant_mobile = $json_enc->new_tenant_mobile;
            $new_tenant_landline = $json_enc->new_tenant_landline;
            $new_tenant_email = $json_enc->new_tenant_email;

            if($prop_id!=""){ //property id not empty
                if($new_tenant_fname!="" || $new_tenant_lname!=""){ //firstname/lastname has value > insert tenant
                    //insert tenant start
                    $this->db->set('property_id',$prop_id);
                    $this->db->set('tenant_firstname',$new_tenant_fname);
                    $this->db->set('tenant_lastname',$new_tenant_lname);
                    $this->db->set('tenant_mobile',$new_tenant_mobile);
                    $this->db->set('tenant_landline',$new_tenant_landline);
                    $this->db->set('tenant_email',$new_tenant_email);
                    $this->db->set('active',1);
                    $this->db->insert('property_tenants');
                    //insert tenant start end
                }
            }
        }
        //ADD TENANTS END

        // clear escalate job reason
        $this->db->delete('selected_escalate_job_reasons',array('job_id'=>$job_id));

        // update status = to be booked
        $j_data = array('status'=>'To Be Booked');
        $this->db->where('id',$job_id);
        $this->db->update('jobs',$j_data);
        $this->db->limit(1);

        //insert logs
        $log_details = "Tenant Details updated";
        $log_params = array(
            'title' => 15,  //escalate job
            'details' => $log_details,
            'display_in_vjd' => 1,
            'created_by_staff' => $this->session->staff_id,
            'property_id' => $prop_id,
            'job_id' => $job_id
        );
        $this->system_model->insert_log($log_params);
        

    }

    public function ajax_toggle_cron_sms_on_off(){

        $data['status'] = false;
        $cron_status = $this->input->post('cron_status');
        $cron_file = $this->input->post('cron_file');
        $db_field = $this->input->post('db_field');
        $country_id = $this->config->item('country');

        //update crm setting
        $db_data = array($db_field=>$cron_status);
        $this->db->where('country_id',$country_id);
        $this->db->update('crm_settings',$db_data);
        $this->db->limit(1);

        if($this->db->affected_rows()>0){
            $data['status'] = false;
        }

        echo json_encode($data);
        

    }

    public function ajax_toggle_cron_on_off(){

        $data['status'] = false;
        $cron_status = $this->input->post('cron_status');
        $cron_file = $this->input->post('cron_file');
        $db_field = $this->input->post('db_field');
        $country_id = $this->config->item('country');

        //update crm setting
        $db_data = array($db_field=>$cron_status);
        $this->db->where('country_id',$country_id);
        $this->db->update('crm_settings',$db_data);
        $this->db->limit(1);

        if($this->db->affected_rows()>0){
            $data['status'] = false;
        }

        echo json_encode($data);
        

    }

    /**
     * Email All Certificates and Invoiced (Ajax request)
     */
    public function email_all_certificates_and_invoices(){

        $data['status'] = false;

        //load model
        $this->load->model('/inc/email_functions_model'); 

        //$num_emails_sent =  $this->email_functions_model->batchSendInvoicesCertificates();

        $ret_arr =  $this->email_functions_model->batchSendInvoicesCertificates();
        $num_emails_sent = $ret_arr['sent_count'];

        $data['countRes'] = $num_emails_sent;
        if($num_emails_sent>0){
            $data['status'] = true;
        }

        $data['error_prop'] = $ret_arr['error_prop'];
        
        echo json_encode($data);
    }

    /**
     * GET not email jobs yet
     */
    public function not_email_all_certificates_and_invoices(){

        $data['status'] = false;

        //load model
        $this->load->model('/inc/email_functions_model'); 
        $this->load->model('Pme_model');

        $country_id = $this->config->item('country');
        //$num_emails_sent =  $this->email_functions_model->batchSendInvoicesCertificates($country_id, true);

        $ret_arr =  $this->email_functions_model->batchSendInvoicesCertificates($country_id, true);
        $num_emails_sent = $ret_arr['sent_count'];

        $hyperLink = array();

        foreach ($num_emails_sent as $value) {
            $added = array();
            $prop_address = $value['address_1']." ".$value['address_2'].", ".$value['address_3'];
            $added['propId'] = $this->gherxlib->crmLink('vpd',$value['property_id'],$prop_address);
            $added['jobId'] = $this->gherxlib->crmLink('vjd',$value['id'],$value['id']);
            array_push($hyperLink, $added);
        }

        $num_upload_sent = $this->Pme_model->send_all_certificates_and_invoices(true);

        foreach ($num_upload_sent as $value) {
            $added = array();
            $prop_address = $value['p_address_1']." ".$value['p_address_2'].", ".$value['p_address_3'];
            $added['propId'] = $this->gherxlib->crmLink('vpd',$value['prop_id'],$prop_address);
            $added['jobId'] = $this->gherxlib->crmLink('vjd',$value['jid'],$value['jid']);
            array_push($hyperLink, $added);
        }

        $data['hyperLinksData'] = $hyperLink;

        if(count($num_emails_sent) <= 0 && count($num_upload_sent) <= 0){
            $data['status'] = true;
        }
        echo json_encode($data);
    }

    public function export_send_letters_jobs() {

        // file name 
        $filename = 'send_letters_'.date('Y-m-d').'.csv';

        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename={$filename}");
        header("Pragma: no-cache");
        header("Expires: 0");

        $job_status="Send Letters";

        $country_id = $this->config->item('country');
        $job_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $state_filter = $this->input->get_post('state_filter');
        $date_filter = ( $this->input->get_post('date_filter') !='' )?$this->system_model->formatDate($this->input->get_post('date_filter')):NULL;
        $search = $this->input->get_post('search_filter');

        if($this->input->get_post('date')!=""){
            $custom_where = "CAST(j.`start_date` AS Date)  >= '{$date_filter}'";
        }else{
            $custom_where = NULL;
        }

        // get data 
        $sel_query .= " 
            j.`id` AS jid, 
            j.`job_type`,
            j.`status` AS jstatus,
            j.`service` AS jservice,
            j.`created` AS jcreated,                
            j.`date` AS jdate,
            j.`job_price`,
            j.`start_date`,
            j.`due_date`,
            j.`comments`,
            j.`job_reason_id`,
            j.`job_reason_comment`,
            j.`urgent_job`,
            j.`client_emailed`,
            j.`door_knock`,
            j.`booked_with`,
            j.`sms_sent`,
            j.`assigned_tech`,
            j.`ts_completed`,
            j.`completed_timestamp`,
            j.`time_of_day`,
            j.`work_order`,
            j.`at_myob`,
            j.`no_dates_provided`,
            j.`agency_approve_en`,
            j.`ss_quantity`,
            j.`key_access_required`,
            j.`preferred_time`,
            j.`property_vacant`,
            j.`tech_comments`,
            j.`precomp_jobs_moved_to_booked`,
            j.`sms_sent_no_show`,
            j.`sms_sent_merge`,
            j.`bne_to_call_notes`,
            j.`assigned_tech`,
            
            p.`property_id`,
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3, 
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            
            p.`tenant_firstname1`,
            p.`tenant_lastname1`,
            p.`tenant_firstname2`,
            p.`tenant_lastname2`,
            p.`tenant_firstname3`,
            p.`tenant_lastname3`,
            p.`tenant_firstname4`,
            p.`tenant_lastname4`,
            
            p.`tenant_mob1`,
            p.`tenant_mob2`,
            p.`tenant_mob3`,
            p.`tenant_mob4`,
            
            p.`tenant_ph1`,
            p.`tenant_ph2`,
            p.`tenant_ph3`,
            p.`tenant_ph4`,
            
            p.`tenant_email1`,
            p.`tenant_email2`,
            p.`tenant_email3`,
            p.`tenant_email4`,
            
            p.`comments` AS p_comments,
            p.`holiday_rental`,
            
            p.`prop_upgraded_to_ic_sa`,

            a.`agency_id`,
            a.`agency_name`,
            a.`account_emails`,
            a.`send_emails`,
            a.`allow_dk`,
            a.`phone` AS a_phone,
            a.`auto_renew` AS a_auto_renew,
            a.`franchise_groups_id`,
            
            jr.`name` AS jr_name,
            
            sa.`FirstName`,
            sa.`LastName`
        ";

        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('property','agency','alarm_job_type','job_reason','staff_accounts'),
            
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'region_filter' => $region_filter,
            'custom_where' => $custom_where,
            'search' => $search,

            'limit' => $per_page,
            'offset' => $offset,
            
            'sort_list' => array(
                array(
                    'order_by' => 'j.urgent_job',
                    'sort' => 'DESC',
                ),
                array(
                    'order_by' => 'j.job_type',
                    'sort' => 'ASC',
                ),
                array(
                    'order_by' => 'p.address_3',
                    'sort' => 'ASC',
                ),
            ),
        );
        $list = $this->jobs_model->get_jobs($params);
       
        // file creation 
        $file = fopen('php://output', 'w');
    
        $header = array("Added By","Job Type","Service Type","Price","Address","State","Agency","Job Comment",
                            "Property Comment","Start Date","End Date");
        fputcsv($file, $header);
        
        foreach ($list->result() as $row){ 
            $getAlarmJobType = $this->db->get_where('alarm_job_type',array('id'=>$row->jservice))->row()->type;
            $prop_address = $row->p_address_1." ".$row->p_address_2.", ".$row->p_address_3;
                                
            $data['addedBy'] = $this->gherxlib->getWhoCreatedSendLetters($row->property_id);
            $data['jobType'] = $row->job_type;
            $data['service'] = $getAlarmJobType;
            $data['price'] = $row->job_price;
            $data['aaddress'] = $prop_address;
            $data['state'] = $row->p_state;
            $data['agencyName'] = $row->agency_name;
            $data['comments'] = $row->comments;
            $data['p_comments'] = $row->p_comments;
            $data['startDate'] = ($row->start_date!="")?date('d/m/Y',strtotime($row->start_date)):'';
            $data['endDate'] = ($row->due_date!="")?date('d/m/Y',strtotime($row->due_date)):'';


            fputcsv($file,$data); 
        }
       
        fclose($file); 
        exit; 

    }

    public function export_service_due_jobs(){

        // file name 
        $filename = 'Jobs_Pending_'.date('Y-m-d').'.csv';

        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename={$filename}");
        header("Pragma: no-cache");
        header("Expires: 0");

        $job_status = 'Pending';

         $country_id = $this->config->item('country');

        $job_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $state_filter = $this->input->get_post('state_filter');
        $agency_filter = $this->input->get_post('agency_filter');
        $region_filter = $this->input->get_post('region_filter_state');
        $date_filter = ($this->input->get_post('date_filter')!="")?date('Y-m-d',$this->input->get_post('date_filter')):NULL;
        $search = $this->input->get_post('search_filter');

        // get data 
        $sel_query = "
        j.`id` AS jid,
        j.`status` AS j_status,
        j.`service` AS j_service,
        j.`created` AS j_created,
        j.`date` AS j_date,
        j.`comments` AS j_comments,
        j.`job_price` AS j_price,
        j.`job_type` AS j_type,
        
        p.`property_id` AS prop_id, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3,
        p.`state` AS p_state,
        p.`postcode` AS p_postcode,
        p.`comments` AS p_comments, 
        
        a.`agency_id` AS a_id,
        a.`agency_name` AS agency_name,
        a.`phone` AS a_phone,
        a.`address_1` AS a_address_1, 
        a.`address_2` AS a_address_2, 
        a.`address_3` AS a_address_3,
        a.`state` AS a_state,
        a.`postcode` AS a_postcode,
        a.`trust_account_software`,
        a.`tas_connected`,
        a.`auto_renew`,
        
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type
        ";

        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type'),
            
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'agency_filter' => $agency_filter,
            'region_filter' => $region_filter,
            'date'=>$date_filter,
            'search' => $search,
            
            'country_id' => $country_id,

            'limit' => $per_page,
            'offset' => $offset,

            'sort_list' => array(
                array(
                    'order_by' => 'j.created',
                    'sort' => 'ASC',
                ),
            ),
        );
        $list = $this->jobs_model->get_jobs($params);
       
        // file creation 
        $file = fopen('php://output', 'w');
    
        $header = array("Job Type","Service Type","Address","Suburb","State","Postcode","Tenants Detail","Agency Name","Job Created Date");
        fputcsv($file, $header);
        
        foreach ($list->result() as $row){ 
            $getAlarmJobType = $this->db->get_where('alarm_job_type',array('id'=>$row->j_service))->row()->type;
            $address = $row->p_address_1.$row->p_address_2;

            $data['jobType'] = $row->j_type;
            $data['service'] = $getAlarmJobType;
            $data['address'] = $address;
            $data['suburb'] = $row->p_address_3;
            $data['state'] = $row->p_state;
            $data['postcode'] = $row->p_postcode;

            $t_params = array(
                'property_id'=> $row->prop_id,
                'active'=> 1
            );
            $get_tenants = $this->gherxlib->getNewTenantsData($t_params);
            $tenant_array = array();
            foreach($get_tenants as $tenant_row){
                $tenant_array[]= "Name: ".$tenant_row->tenant_firstname." ".$tenant_row->tenant_lastname." | PH: ".$tenant_row->tenant_landline." | Mob: ".$tenant_row->tenant_mobile;
            }

            $data['tenants'] = implode("\n",$tenant_array);


            $data['agency'] = $row->agency_name;
            $data['date'] = $this->system_model->formatDate($row->j_created,'d/m/Y');

            fputcsv($file,$data); 
        }
       
        fclose($file); 
        exit; 

    }

    public function update_pending_jobs(){
        $this->load->model('properties_model');

        $action = $this->input->post('action');
        $prop_id = $this->input->post('prop_id[]');
        $checkbox = $this->input->post('chk_job');
        $reason_they_left = $this->input->post('reason_they_left');
        $other_reason = $this->input->post('other_reason');
        $staff_id = $this->session->staff_id;

        //get staff name
        $staff_params = array(
           'sel_query' => "FirstName,LastName",
           'staff_id' => $this->session->staff_id,
       );
        $staff_info = $this->gherxlib->getStaffInfo($staff_params)->row_array();


        if($action == "Create Job"){  // CREATE JOB



        }elseif($action == "No Longer Manage"){  // NO LONGER MANAGED tt

            $nlm_chk_flag = 0;
            $nlm_prop_arr = [];
        
            if($checkbox){
           
                foreach($checkbox as $index => $val){

                    $job_id = $val;
                    $p_params = array(
                        'sel_query' => 'p.property_id,p.address_1,p.address_2,p.address_3,p.state,p.postcode',
                        'p_deleted'=> 0,
                        'join_table' => array('jobs'),
                        'job_id' => $job_id
                    );
                    $prop_detail = $this->properties_model->get_properties($p_params)->row_array();
                    
                    $prop_id = $prop_detail['property_id'];
                    $prop_name = "{$prop_detail['address_1']} {$prop_detail['address_2']}, {$prop_detail['address_3']} {$prop_detail['state']} {$prop_detail['postcode']}";
                    
                    ##Gherx > NLM > use nlm function nlm_property()
                    $nlm_params = array(
						'reason_they_left'=> $reason_they_left,
						'other_reason'=> $other_reason
					);
                    $nlm_prop = $this->properties_model->nlm_property($prop_id, $nlm_params);

                    if($nlm_prop == false){ ## has active job

                        //$cannot_nlm_prop_id_arr[] =  $p['property_id'];
                        //$cannot_nlm_address_arr[] =  $p_address;

                        $nlm_prop_arr[] = array( 
                            'prop_id' => $prop_id,
                            'prop_name' => $prop_name
                        );

                        //set flas session for unable to process proeprty that has active job
                        $this->session->set_flashdata(array('nlm_chk_flag'=>1,'propArray'=>$nlm_prop_arr));

                    }

                    /* Disable by Gherx > Use function nlm_property() to git rid of messy redundant code
                    if($this->system_model->NLMjobStatusCheck($prop_id)){
                        $nlm_chk_flag = 1;
                        //save property that is NLM and cannot process
                        $nlm_prop_arr[] = array( 
                            'prop_id' => $prop_id,
                            'prop_name' => $prop_name
                        );

                        //set flas session for unable to process proeprty that has active job
                        $this->session->set_flashdata(array('nlm_chk_flag'=>1,'propArray'=>$nlm_prop_arr));

                    }else{
                       

                        //UPDATE PROPERTY SET DELTED TO 1
                        $db_params = array(
                            'deleted'=> 1,
                            'agency_deleted' => 0,
                            'deleted_date' => date('Y-m-d H:i:s'),
                            'booking_comments' => "No longer managed as of ".date('d/m/Y')." - by SATS.",
                            'is_nlm' => 1,
                            'nlm_timestamp' => date('Y-m-d H:i:s'),                            
                            'nlm_by_sats_staff' => $staff_id
                        );

                        // check if property has money owing and needs to verify paid
                        if( $this->system_model->check_verify_paid($prop_id) == true ){
                            $db_params['nlm_display'] = 1;
                        }
                    
                        $this->properties_model->update_property($prop_id,$db_params);


                        //UPDATE JOBS SET STATUS CANCELLED
                        $jdb_params = array(
                            'status' => "Cancelled",
                            'comments' => "This property was marked No Longer Managed by SATS on ".date("d/m/Y")." and all jobs cancelled",
                            'cancelled_date' => date('Y-m-d'),
                        );
                        $this->jobs_model->update_job_by_prop_id($prop_id,$jdb_params);


                        //UPDATE PROPERTY SERVICES SET status_changed
                        /* DISABLE as per Joe's instruction
                        $ps_params = array(
                            'status_changed' => date("Y-m-d H:i:s")
                        );
                        $this->properties_model->update_property_services($prop_id, $ps_params);
                        */

                       /* 
                        //INSERT LOG
                        $log_details = "No Longer Managed, By {$staff_info['FirstName']} {$staff_info['LastName']} ";
                        $log_params = array(
                            'title' => 6,  //Property No Longer Managed     
                            'details' => $log_details,
                            'display_in_vpd' => 1,
                            'created_by_staff' => $this->session->staff_id,
                            'property_id' => $prop_id,
                        );
                        $this->system_model->insert_log($log_params);

                    }
                    */
                    


                }

                $success_message = "Property selected is/are no longer managed";


                //set session success message
                $this->session->set_flashdata(array('success_msg'=>$success_message,'status'=>'success'));
                redirect(base_url('jobs/service_due'));
                

            }else{ //EMPTY checkbox

                //set session success message
                $error_message = "No jobs has been selected, please go back to perform this action again.";
                $this->session->set_flashdata(array('error_msg'=>$error_message,'status'=>'error'));
                redirect(base_url('jobs/service_due'));

            }

        }

    }


    public function export_to_be_booked(){

        // file name 
        $filename = 'To_Be_Booked_'.date('Y-m-d').'.csv';

        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename={$filename}");
        header("Pragma: no-cache");
        header("Expires: 0");

        $job_status = 'To Be Booked';

        $country_id = $this->config->item('country');

        $job_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $state_filter = $this->input->get_post('state_filter');
        $agency_filter = $this->input->get_post('agency_filter');
        $date_filter = ($this->input->get_post('date_filter')!="")?date('Y-m-d',$this->input->get_post('date_filter')):NULL;
        $search = $this->input->get_post('search_filter');
        $is_urgent = $this->input->get_post('is_urgent');

        $sub_region_ms = $this->input->get_post('sub_region_ms');

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }
        
        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $sel_query = "
        j.`id` AS jid,
        j.`status` AS j_status,
        j.`service` AS j_service,
        j.`created` AS j_created,
        j.`date` AS j_date,
        j.`start_date`,
        j.`due_date`,
        j.`comments` AS j_comments,
        j.`job_price` AS j_price,
        j.`job_type` AS j_type,
        j.`property_vacant`,
        j.`urgent_job`,
        j.`job_reason_id`,
        DATEDIFF(CURDATE(), Date(j.`created`)) AS age,
        
        p.`property_id` AS prop_id, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3,
        p.`state` AS p_state,
        p.`postcode` AS p_postcode,
        p.`comments` AS p_comments, 
        
        a.`agency_id` AS a_id,
        a.`agency_name` AS agency_name,
        a.`phone` AS a_phone,
        a.`address_1` AS a_address_1, 
        a.`address_2` AS a_address_2, 
        a.`address_3` AS a_address_3,
        a.`state` AS a_state,
        a.`postcode` AS a_postcode,
        a.`trust_account_software`,
        a.`tas_connected`,
        a.`allow_dk`,
        
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type
        ";

        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type'),
            
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'agency_filter' => $agency_filter,
            'date'=> $date_filter,
            'search' => $search,
            'postcodes' => $postcodes,
            'is_urgent' => $is_urgent,
            
            'country_id' => $country_id,

            'sort_list' => array(
                array(
                    'order_by' => 'j.date',
                    'sort' => 'ASC',
                ),
            ),
        );
        $list = $this->jobs_model->get_jobs($params);
       
        // file creation 
        $file = fopen('php://output', 'w');
    
        $header = array("Date","Job Type","Age","Service","Price","Address","State","Region","Agency","Job Number","Last Contact");
        fputcsv($file, $header);
        
        foreach ($list->result() as $row){ 
            $getAlarmServices = $this->db->get_where('alarm_job_type',array('id'=>$row->j_service))->row()->type;
            $prop_address = $row->p_address_1." ".$row->p_address_2.", ".$row->p_address_3;

            //get region
           /* $params = array(
                'postcode_region_postcodes' => $row->p_postcode,
            );
            $getRegion = $this->system_model->getRegion($params)->row();*/
            #new table
            $getRegion = $this->system_model->getRegion_v2($row->p_postcode)->row();

            //get last contact 
            $lc_sql = $this->gherxlib->getLastContact($row->jid);
            $lc = $lc_sql->row_array();


            $data['date'] = ($this->system_model->isDateNotEmpty($row->j_date))?date('d/m/Y', strtotime($row->j_date)):'';
            $data['jobType'] = $this->gherxlib->getJobTypeAbbrv($row->j_type);
            $data['age'] = $row->age;
            $data['service'] = $getAlarmServices;
            $data['price'] = $row->j_price;
            $data['address'] = $prop_address;
            $data['state'] = $row->p_state;
            $data['region'] = $getRegion->subregion_name;
            $data['agency'] = $row->agency_name;
            $data['job_number'] = $row->jid;
            $data['last_contact'] = ($this->system_model->isDateNotEmpty($lc['eventdate']))?date("d/m/Y",strtotime($lc['eventdate'])):'';

            
            fputcsv($file,$data); 
        }
       
        fclose($file); 
        exit; 

    }


    public function view_jobs_export(){

        $status = $this->input->get_post('status');

        //country id
        $country_id = $this->config->item('country');

        //filter
        $job_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $state_filter = $this->input->get_post('state_filter');
        $date_filter = $this->input->get_post('date_filter');
        $tech_filter = $this->input->get_post('tech_filter');
        $agency_filter = $this->input->get_post('agency_filter');
        $search_filter = $this->input->get_post('search_filter');
        $show_is_eo = $this->input->get_post('show_is_eo');
        $updated_to_240v_rebook = $this->input->get_post('updated_to_240v_rebook');
        $is_sales = $this->input->get_post('is_sales');

        $dateFrom_field = $this->input->get_post('dateFrom_filter');
        $dateTo_field = $this->input->get_post('dateTo_filter');
        $dateFrom_filter = ( $dateFrom_field !='' )?$this->system_model->formatDate($dateFrom_field):NULL;
        $dateTo_filter = ( $dateTo_field !='' )?$this->system_model->formatDate($dateTo_field):NULL;
        

        $state_ms = $this->input->get_post('state_ms');
        #$data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        #$data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
       # $data['sub_region_ms_json'] = json_encode($sub_region_ms);
        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }  

        $rownum = 0;

        $techcomment = "";

        $job_status = "";

        //STATUS AND FILE NAME AND HEADER
        switch($status) {

            case "tobebooked" :
                $job_status = "To Be Booked";
                $fn = "To_Be_Booked";
                break;
        
            case "sendletters" :
                $job_status = "Send Letters";
                $fn = "SendLetters";
                break;
        
            case "booked" :
                $job_status = "Booked";
                $fn = $job_status;
                break;
        
            case "merged" :
                $job_status = "Merged Certificates";
                $fn = "Merged_Certificates";
                break;
        
            case "cancelled" :
                $job_status = "Cancelled";
                $fn = $job_status;
                break;
        
            case "completed" :
                $job_status = "Completed";
                $fn = $job_status;
                 $header = array("Invoice No","Invoice Amount","Date","Job Type","Service","Address","Suburb","State","Postcode","Landlord FirstName","Landlord LastName","Tenants","Tech Comments","Agency","Technician","Sales Rep");
                break;
        
            case "precompleted" :
                $job_status = "Pre Completion";
                $fn = "Pre_Completion";
                break;
                
            case "pending" :
                $job_status = "Pending";
                $fn = $job_status;
                break;  
                
            case "escalate" :
                $job_status = "Escalate";
                $fn = $job_status;
                break;  
        
            case "" :
                $job_status = "";
                $fn = "All";
                break;
        }



        //START CSV CREATIONq

        //file name
        $filename = "Jobs_" . $fn . "_" . date("d") . "-" . date("m") . "-" . date("y") . ".csv";


        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename={$filename}");
        header("Pragma: no-cache");
        header("Expires: 0");


        // file creation 
         $file = fopen('php://output', 'w');

        //Header
        fputcsv($file, $header);



        //PDF CONTENT
        if($job_status=="Completed"){   //COMPLETED DOWNLOAD

           
            $sel_query = "
            j.`id` AS jid,
            j.`status` AS j_status,
            j.`service` AS j_service,
            j.`created` AS j_created,
            j.`date` AS j_date,
            j.`comments` AS j_comments,
            j.`job_price` AS j_price,
            j.`job_type` AS j_type,
            j.tmh_id,
            j.`tech_comments`,
            j.`assigned_tech`,
            j.`booked_by`,
            
            p.`property_id` AS prop_id, 
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            p.`comments` AS p_comments,
            p.`landlord_firstname`,
            p.`landlord_lastname`,
            
            a.`agency_id` AS a_id,
            a.`agency_name` AS agency_name,
            a.`phone` AS a_phone,
            a.`address_1` AS a_address_1, 
            a.`address_2` AS a_address_2, 
            a.`address_3` AS a_address_3,
            a.`state` AS a_state,
            a.`postcode` AS a_postcode,
            a.`trust_account_software`,
            a.`tas_connected`,
            a.`salesrep`,
            
            ajt.`id` AS ajt_id,
            ajt.`type` AS ajt_type
            ";

            if($dateFrom_field!="" && $dateTo_field!=""){
                $custom_where = "CAST(j.`date` AS Date)  BETWEEN '{$dateFrom_filter}' AND '{$dateTo_filter}'";
            }else{
                $custom_where = NULL;
            }

            // tables joined
            $join_table_array = array('job_type','alarm_job_type');
            if( $updated_to_240v_rebook == 1 ){
                $join_table_array[] = 'job_markers';
            }

            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'job_status' => $job_status,
                'join_table' => $join_table_array,
                
                'job_type' => $job_filter,
                'service_filter' => $service_filter,
                'state_filter' => $state_filter,
                'agency_filter' => $agency_filter,
                'is_eo' => $show_is_eo,
                'is_sales' => $is_sales,
                'postcodes' => $postcodes,
                'custom_where'=> $custom_where,
                'search' => $search_filter,
                
                'sort_list' => array(
                    array(
                        'order_by' => 'j.created',
                        'sort' => 'ASC',
                    ),
                ),
                );

            if( $updated_to_240v_rebook == 1 ){
                $params['updated_to_240v_rebook'] = 1;
            }
            
            $list = $this->jobs_model->get_jobs($params);

            foreach ($list->result() as $row){
                //get alarmjobtype
                $getAlarmJobType = $this->db->get_where('alarm_job_type',array('id'=>$row->j_service))->row()->type;

                //get technician
                if( $row->assigned_tech > 0 ){

                    $tech_params = array(
                        'sel_query'=> "FirstName,LastName",
                        'staffID' => $row->assigned_tech
                    );
                    $technician = $this->system_model->getTech($tech_params)->row();
                    $tech_name = "{$technician->FirstName} {$technician->LastName}";

                }else{

                    $tech_name = '';

                }
                

                 // get invoice number
                if(isset($row->tmh_id) || $row->tmh_id!=NULL)
                {
                    $invoice_num = $row->tmh_id;
                }
                else
                {
                    $invoice_num = $row->jid;
                }

                //get job grand total
                //$grand_total = $row->j_price;
                $grand_total = $this->system_model->price_ex_gst($row->j_price);

                // get alarms
                $a_sql = $this->db->query("
                    SELECT *
                    FROM `alarm`
                    WHERE `job_id`  = $row->jid
                ");
               foreach($a_sql->result_array() as $a)
                {       
                    if($a['new']==1){
                        //$grand_total += $a['alarm_price'];
                        $grand_total += $this->system_model->price_ex_gst($a['alarm_price']);
                    }               
                }

                //get staff 
                $staff_params = array(
                    'sel_query' => "sa.FirstName, sa.LastName",
                    'staff_id' => $row->salesrep
                );
                $staff_query = $this->gherxlib->getStaffInfo($staff_params);
                $staff_row = $staff_query->row_array();


                $data['invoice_num'] = $invoice_num;
                $data['invoice_amount'] = "$".number_format($grand_total,2);
                $data['date'] = $this->system_model->formatDate($row->j_date,'d/m/Y');
                $data['jobType'] = $this->gherxlib->getJobTypeAbbrv($row->j_type);
                $data['service'] = $getAlarmJobType;
                $data['address'] = $row->p_address_1." ".$row->p_address_2;
                $data['suburb'] = $row->p_address_3;
                $data['state'] = $row->p_state;
                $data['postcode'] = $row->p_postcode;
                $data['landlord_fname'] = $row->landlord_firstname;
                $data['landlord_lname'] = $row->landlord_lastname;

                //TENANT
                $t_params = array(
                    'property_id'=> $row->prop_id,
                    'active'=> 1
                );
                $get_tenants = $this->gherxlib->getNewTenantsData($t_params);
                $tenant_array = array();
                foreach($get_tenants as $tenant_row){
                    $tenant_array[]= "Name: ".$tenant_row->tenant_firstname." ".$tenant_row->tenant_lastname." | PH: ".$tenant_row->tenant_landline." | Mob: ".$tenant_row->tenant_mobile;
                }
    
                $data['tenants'] = implode("\n",$tenant_array);
                //END TENANT
    
                $data['tech_comments'] = $row->tech_comments;
                $data['agency'] = $row->agency_name;
                $data['technician'] = $tech_name;
                $data['sales_rep'] = "{$staff_row['FirstName']} {$staff_row['LastName']}";

    
                fputcsv($file,$data); 
            }


        }


        fclose($file); 
        exit; 

    }


    public function ageing_jobs_30_to_60()
    {  
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Jobs 30-60 Days";
        $page_url = '/jobs/ageing_jobs_30_to_60';

        $country_id = $this->config->item('country');
 
        $agency_filter = $this->input->get_post('agency_filter');
        $job_filter = $this->input->get_post('job_type_filter');
        $state_filter = $this->input->get_post('state_filter');
        $agency_priority_filter = $this->input->get_post('agency_priority_filter');

        if ($agency_priority_filter != "") {
            $agency_priority_custom_where = "aght.priority = {$agency_priority_filter}";
        }

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }

        $date_span_from = date('Y-m-d', strtotime("-60 days"));
        $date_span_to = date('Y-m-d', strtotime("-30 days"));

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');
       
        $sel_query = "
                *, 
                j.`id` AS jid, 
                j.`created` AS jcreated, 
                j.`date` AS jdate,
                j.`service` AS jservice,

                p.`property_id`,
                p.`address_1` AS p_address_1,
                p.`address_2` AS p_address_2,
                p.`address_3` AS p_address_3,
                p.`state` AS p_state,
                p.`postcode` AS p_postcode,
                aght.priority,
                apmd.abbreviation
                ";

        $custom_where = "(j.`status` = 'To Be Booked' OR j.`status` = 'Pre Completion' OR j.`status` = 'Booked' OR j.`status` = 'Escalate') AND CAST(j.`created` AS DATE) BETWEEN '{$date_span_from}' AND '{$date_span_to}' AND p.`holiday_rental` != 1";
        
        if( $this->input->get_post('export')==1 ){

            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'join_table' => array('alarm_job_type', 'agency_priority', 'agency_priority_marker_definition'),
                'custom_where_arr' => array($custom_where, $agency_priority_custom_where),
            
                'agency_filter' => $agency_filter,
                'job_type' => $job_filter,
                'state_filter' => $state_filter,
                'postcodes' => $postcodes,
    
               
                'sort_list' => array(
                    array(
                        'order_by' => 'j.created',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $aa_query2 = $this->jobs_model->get_jobs($params);

            // file name
            $filename = 'jobs_30_60' . date('Y-m-d') . '.csv';

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            $country_id = $this->config->item('country');

            $all = $this->input->get_post('all');
            $date_from_filter = ($this->input->get_post('date_from_filter')) ? $this->input->get_post('date_from_filter') : date("01/m/Y");
            $date_to_filter = ($this->input->get_post('date_to_filter')) ? $this->input->get_post('date_to_filter') : date("t/m/Y");
            $salesrep_filter = $this->input->get_post('sales_rep_filter');
            $state_filter = $this->input->get_post('state_filter');

            $lists = $aa_query2;

            // file creation
            $file = fopen('php://output', 'w');

            //header
            $header = array("Date", "Age", "Job Type","Address","State","Agency","Job #");
            fputcsv($file, $header);

            foreach ($lists->result_array() as $row) {

                $date1=date_create(date('Y-m-d',strtotime($row['jcreated'])));
                $date2=date_create(date('Y-m-d'));
                $diff=date_diff($date1,$date2);
                $age = $diff->format("%a");


                $csvdata['Date'] =$this->customlib->isDateNotEmpty($row['jdate'])?date("d/m/Y",strtotime($row['jdate'])):''; ;
                $csvdata['Age'] = $age;
                $csvdata['Job Type'] = $row['job_type'];
                $csvdata['Address'] =$row['p_address_1']." ".$row['p_address_2'].", ".$row['p_address_3'];
                $csvdata['State'] = $row['p_state'];
                $csvdata['Agency'] = $row['agency_name'];
                $csvdata['Job #'] = $row['jid'];

        
                fputcsv($file, $csvdata);
            }

            fclose($file);
            exit;

        }else{

                    
                    $params = array(
                        'sel_query' => $sel_query,
                        'p_deleted' => 0,
                        'a_status' => 'active',
                        'del_job' => 0,
                        'country_id' => $country_id,
                        'join_table' => array('alarm_job_type', 'agency_priority', 'agency_priority_marker_definition'),
                        'custom_where_arr' => array($custom_where, $agency_priority_custom_where),

                        // 'agency_priority_filter' => $agency_priority_filter,
                        'agency_filter' => $agency_filter,
                        'job_type' => $job_filter,
                        'state_filter' => $state_filter,
                        'postcodes' => $postcodes,

                        'limit' => $per_page,
                        'offset' => $offset,
                        
                        'sort_list' => array(
                            array(
                                'order_by' => 'j.created',
                                'sort' => 'ASC',
                            ),
                        ),
                    );
                $data['lists'] = $this->jobs_model->get_jobs($params);
                $aa_query = $this->jobs_model->get_jobs($params);
                $data['sql_query'] = $this->db->last_query(); //Show query on About

                // all rows
                $sel_query = "COUNT(j.`id`) AS jcount";
                $params = array(
                    'sel_query' => $sel_query,
                    'p_deleted' => 0,
                    'a_status' => 'active',
                    'del_job' => 0,
                    'country_id' => $country_id,
                    'join_table' => array('alarm_job_type', 'agency_priority'),
                    'custom_where_arr' => array($custom_where, $agency_priority_custom_where),

                    // 'agency_priority_filter' => $agency_priority_filter,
                    'agency_filter' => $agency_filter,
                    'job_type' => $job_filter,
                    'state_filter' => $state_filter,
                    'postcodes' => $postcodes,
                );
                $query = $this->jobs_model->get_jobs($params);
                $total_rows = $query->row()->jcount;
                $data['total_rows'] = $query->row()->jcount;

                // update page total
                $page_tot_params = array(
                    'page' => $page_url,
                    'total' => $total_rows
                );
                $this->system_model->update_page_total($page_tot_params);

                //Agency Priority Marker filter
                $data['agency_priority_result'] = $this->jobs_model->get_agency_priority_marker_abbreviation();

                //Agency name filter
                $sel_query = "DISTINCT(a.`agency_id`),
                a.`agency_name`";
                $params = array(
                    'sel_query' => $sel_query,
                    'p_deleted' => 0,
                    'a_status' => 'active',
                    'del_job' => 0,
                    'country_id' => $country_id,
                    'join_table' => array('job_type','alarm_job_type'),
                    'custom_where' => $custom_where,
                    'sort_list' => array(
                        array(
                            'order_by' => 'a.`agency_name`',
                            'sort' => 'ASC',
                        ),
                    ),
                );
                $data['agency_filter_json'] = json_encode($params);


                //Job type Filter
                $sel_query = "DISTINCT(j.`job_type`),
                        `j.job_type`";
                $params = array(
                    'sel_query' => $sel_query,
                    'p_deleted' => 0,
                    'a_status' => 'active',
                    'del_job' => 0,
                    'country_id' => $country_id,
                    'join_table' => array('job_type','alarm_job_type'),
                    'custom_where' => $custom_where,
                    'sort_list' => array(
                        array(
                            'order_by' => 'j.`job_type`',
                            'sort' => 'ASC',
                        ),
                    ),
                );
                $data['job_type_filter_json'] = json_encode($params); 

                //State Filter
                $sel_query = "DISTINCT(p.`state`)";
                $params = array(
                    'sel_query' => $sel_query,
                    'p_deleted' => 0,
                    'a_status' => 'active',
                    'del_job' => 0,
                    'country_id' => $country_id,
                    'join_table' => array('job_type','alarm_job_type'),
                    'custom_where' => $custom_where,
                    'sort_list' => array(
                        array(
                            'order_by' => 'p.`state`',
                            'sort' => 'ASC',
                        ),
                    ),
                );
                $data['state_filter_json'] = json_encode($params);

                // Region Filter ( get distinct state )
                $sel_query = "p.`state`";
                $region_filter_arr = array(
                    'sel_query' => $sel_query,
                    'custom_where_arr' => array(
                        $custom_where
                    ),
                    'del_job' => 0,
                    'p_deleted' => 0,
                    'a_status' => 'active',
                    // 'job_status' => $job_status,
                    
                    'country_id' => $country_id,
                    'join_table' => array('job_type','alarm_job_type'),

                    'sort_list' => array(
                        array(
                            'order_by' => 'p.`state`',
                            'sort' => 'ASC',
                        )
                    ),
                    'group_by' => 'p.`state`',
                    'display_query' => 0
                );
                $data['region_filter_json'] = json_encode($region_filter_arr);

                //GET BOOKED COUNT
                $count_booked_sel_query = "COUNT(j.`id`) AS jcount";
                $custom_where = "j.`status` = 'Booked' AND CAST(j.`created` AS DATE) BETWEEN '{$date_span_from}' AND '{$date_span_to}' ";
                $params = array(
                    'sel_query' => $count_booked_sel_query,
                    'p_deleted' => 0,
                    'a_status' => 'active',
                    'del_job' => 0,
                    'country_id' => $country_id,
                    'join_table' => array('alarm_job_type'),
                    'custom_where' => $custom_where,

                );
                $data['booked_count'] = $this->jobs_model->get_jobs($params)->row()->jcount;

        $pagi_links_params_arr = array(
            'agency_filter' => $agency_filter,
            'job_type_filter' => $job_filter,
            'service_filter' => $service_filter,
            'date_filter' => $date_filter,
            'search_filter' => $search,
            'sub_region_ms' => $sub_region_ms,
            'state_filter' => $state_filter,
            'agency_priority_filter' => $agency_priority_filter
        );
        $pagi_link_params = '/jobs/ageing_jobs_30_to_60/?'.http_build_query($pagi_links_params_arr);

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


        $this->load->view('templates/inner_header', $data);
        $this->load->view('daily/ageing_jobs_30_to_60', $data);
        $this->load->view('templates/inner_footer', $data);
        }
    
    }


    public function ageing_jobs_60_to_90(){
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Jobs 60-90 Days";
        $page_url = '/jobs/ageing_jobs_60_to_90';

        $country_id = $this->config->item('country');
 
        $agency_filter = $this->input->get_post('agency_filter');
        $job_filter = $this->input->get_post('job_type_filter');
        $state_filter = $this->input->get_post('state_filter');
        $agency_priority_filter = $this->input->get_post('agency_priority_filter');

        if ($agency_priority_filter != "") {
            $agency_priority_custom_where = "aght.priority = {$agency_priority_filter}";
        }
        
        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }       

        $date_span_from = date('Y-m-d', strtotime("-90 days"));
        $date_span_to = date('Y-m-d', strtotime("-60 days"));

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $sel_query = "
                *, 
                j.`id` AS jid, 
                j.`created` AS jcreated, 
                j.`date` AS jdate,
                j.`service` AS jservice,

                p.`property_id`,
                p.`address_1` AS p_address_1,
                p.`address_2` AS p_address_2,
                p.`address_3` AS p_address_3,
                p.`state` AS p_state,
                p.`postcode` AS p_postcode,
                aght.priority,
                apmd.abbreviation
                ";

        $custom_where = "(j.`status` = 'To Be Booked' OR j.`status` = 'Pre Completion' OR j.`status` = 'Booked' OR j.`status` = 'Escalate') AND CAST(j.`created` AS DATE) BETWEEN '{$date_span_from}' AND '{$date_span_to}' AND p.`holiday_rental` != 1";
       
       
       
       
       
        if( $this->input->get_post('export')==1 ){
            
            
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'join_table' => array('alarm_job_type', 'agency_priority', 'agency_priority_marker_definition'),
            'custom_where_arr' => array($custom_where, $agency_priority_custom_where),

            'agency_filter' => $agency_filter,
            'job_type' => $job_filter,
            'state_filter' => $state_filter,
            'postcodes' => $postcodes,

        
            
            'sort_list' => array(
                array(
                    'order_by' => 'j.created',
                    'sort' => 'ASC',
                ),
            ),
        );
            $aa_query2 = $this->jobs_model->get_jobs($params);

                // file name
                $filename = 'jobs_60_90' . date('Y-m-d') . '.csv';
    
                header("Content-type: application/csv");
                header("Content-Disposition: attachment; filename={$filename}");
                header("Pragma: no-cache");
                header("Expires: 0");
    
                $country_id = $this->config->item('country');
    
                $all = $this->input->get_post('all');
                $date_from_filter = ($this->input->get_post('date_from_filter')) ? $this->input->get_post('date_from_filter') : date("01/m/Y");
                $date_to_filter = ($this->input->get_post('date_to_filter')) ? $this->input->get_post('date_to_filter') : date("t/m/Y");
                $salesrep_filter = $this->input->get_post('sales_rep_filter');
                $state_filter = $this->input->get_post('state_filter');
    
          
                
                $lists = $aa_query2;
    
                // file creation
                $file = fopen('php://output', 'w');
    
                //header
                $header = array("Date", "Age", "Job Type","Address","State","Agency","Job #");
                fputcsv($file, $header);
    
                foreach ($lists->result_array() as $row) {

                    $date1=date_create(date('Y-m-d',strtotime($row['jcreated'])));
                    $date2=date_create(date('Y-m-d'));
                    $diff=date_diff($date1,$date2);
                    $age = $diff->format("%a");

    
                    $csvdata['Date'] =$this->customlib->isDateNotEmpty($row['jdate'])?date("d/m/Y",strtotime($row['jdate'])):''; ;
                    $csvdata['Age'] = $age;
                    $csvdata['Job Type'] = $row['job_type'];
                    $csvdata['Address'] =$row['p_address_1']." ".$row['p_address_2'].", ".$row['p_address_3'];
                    $csvdata['State'] = $row['p_state'];
                    $csvdata['Agency'] = $row['agency_name'];
                    $csvdata['Job #'] = $row['jid'];

            
                    fputcsv($file, $csvdata);
                }
    
                fclose($file);
                exit;
           

        }else{
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'join_table' => array('alarm_job_type','agency_priority', 'agency_priority_marker_definition'),
            'custom_where_arr' => array($custom_where, $agency_priority_custom_where),

            'agency_filter' => $agency_filter,
            'job_type' => $job_filter,
            'state_filter' => $state_filter,
            'postcodes' => $postcodes,

            'limit' => $per_page,
            'offset' => $offset,
            
            'sort_list' => array(
                array(
                    'order_by' => 'j.created',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['lists'] = $this->jobs_model->get_jobs($params);
        $data['sql_query'] = $this->db->last_query(); //Show query on About
        
        // all rows
        $sel_query = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'join_table' => array('alarm_job_type', 'agency_priority'),
            'custom_where_arr' => array($custom_where, $agency_priority_custom_where),

            'agency_filter' => $agency_filter,
            'job_type' => $job_filter,
            'state_filter' => $state_filter,
            'postcodes' => $postcodes,
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;
        $data['total_rows'] = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        //Agency name filter
        $sel_query = "DISTINCT(a.`agency_id`),
        a.`agency_name`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type'),
            'custom_where' => $custom_where,
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['agency_filter_json'] = json_encode($params);

        
        //Job type Filter
        $sel_query = "DISTINCT(j.`job_type`),
                `j.job_type`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type'),
            'custom_where' => $custom_where,
            'sort_list' => array(
                array(
                    'order_by' => 'j.`job_type`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['job_type_filter_json'] = json_encode($params); 

        //State Filter
        $sel_query = "DISTINCT(p.`state`)";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type'),
            'custom_where' => $custom_where,
            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['state_filter_json'] = json_encode($params);

        // Region Filter ( get distinct state )
        $sel_query = "p.`state`";
        $region_filter_arr = array(
            'sel_query' => $sel_query,
            'custom_where_arr' => array(
                $custom_where
            ),
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'job_status' => $job_status,
            
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type'),

            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                )
            ),
            'group_by' => 'p.`state`',
            'display_query' => 0
        );
        $data['region_filter_json'] = json_encode($region_filter_arr);


        //GET BOOKED COUNT
        $count_booked_sel_query = "COUNT(j.`id`) AS jcount";
        $custom_where = "j.`status` = 'Booked' AND CAST(j.`created` AS DATE) BETWEEN '{$date_span_from}' AND '{$date_span_to}' ";
        $params = array(
            'sel_query' => $count_booked_sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'join_table' => array('alarm_job_type'),
            'custom_where' => $custom_where,

        );
        $data['booked_count'] = $this->jobs_model->get_jobs($params)->row()->jcount;


        $pagi_links_params_arr = array(
            'agency_filter' => $agency_filter,
            'job_type_filter' => $job_type_filter,
            'service_filter' => $service_filter,
            'date_filter' => $date_filter,
            'search_filter' => $search,
            'sub_region_ms' => $sub_region_ms,
            'agency_priority_filter' => $agency_priority_filter
        );
        $pagi_link_params = '/jobs/ageing_jobs_60_to_90/?'.http_build_query($pagi_links_params_arr);

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


        $this->load->view('templates/inner_header', $data);
        $this->load->view('daily/ageing_jobs_60_to_90', $data);
        $this->load->view('templates/inner_footer', $data);
     }
    }


    public function ageing_jobs_90()
    {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Jobs 90+ Days";
        $country_id = $this->config->item('country');
        $page_url = '/jobs/ageing_jobs_90';

        $job_type_filter = $this->input->get_post('job_type_filter');
        $state_filter = $this->input->get_post('state_filter');
        $agency_filter = $this->input->get_post('agency_filter');
        $agency_priority_filter = $this->input->get_post('agency_priority_filter');

        if ($agency_priority_filter != "") {
            $agency_priority_custom_where = "aght.priority = {$agency_priority_filter}";
        }

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }   


         // pagination settings
         $per_page = $this->config->item('pagi_per_page');
         $offset = $this->input->get_post('offset');


         //GET LIST
         $sel_query = "
         j.`id` AS jid,
         j.`status` AS j_status,
         j.`service` AS j_service,
         j.`created` AS j_created,
         j.`date` AS j_date,
         j.`job_price` AS j_price,
         j.`job_type` AS j_type,
         j.`preferred_time`,
         j.`out_of_tech_hours`,
         j.`access_notes`,
         
         p.`property_id`, 
         p.`address_1` AS p_address_1, 
         p.`address_2` AS p_address_2, 
         p.`address_3` AS p_address_3,
         p.`state` AS p_state,
         p.`postcode` AS p_postcode,
         p.`comments` AS p_comments,    
         p.`deleted` AS p_deleted,
         
         a.`agency_id` AS a_id,
         a.`agency_name` AS agency_name,
         aght.priority,
         apmd.abbreviation,
         
         ajt.`id` AS ajt_id,
         ajt.`type` AS ajt_type
         ";

        $last_90_days = date('Y-m-d', strtotime("-90 days"));
        #$custom_filter = "CAST(j.`created` AS DATE) < '{$last_90_days}' ";
        $custom_filter = "(j.`status` = 'To Be Booked' OR j.`status` = 'Pre Completion' OR j.`status` = 'Booked' OR j.`status` = 'Escalate') AND CAST(j.`created` AS DATE) < '{$last_90_days}' AND p.`holiday_rental` != 1";
       
        if( $this->input->get_post('export')==1 ){
            
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'join_table' => array('job_type','alarm_job_type','agency_priority', 'agency_priority_marker_definition'),
                'custom_where_arr' => array($custom_where, $agency_priority_custom_where),
                
                'postcodes' => $postcodes,
                'job_type' => $job_type_filter,
                'state_filter' => $state_filter,
                'agency_filter' => $agency_filter,
        
                'sort_list' => array(
                    array(
                        'order_by' => 'j.created',
                        'sort' => 'ASC',
                    ),
                ),
    
                'display_query' => 0,
            );
            $aa_query2 = $this->jobs_model->get_jobs($params);

                // file name
                $filename = 'jobs_90' . date('Y-m-d') . '.csv';
    
                header("Content-type: application/csv");
                header("Content-Disposition: attachment; filename={$filename}");
                header("Pragma: no-cache");
                header("Expires: 0");
    
                $country_id = $this->config->item('country');
    
                $all = $this->input->get_post('all');
                $date_from_filter = ($this->input->get_post('date_from_filter')) ? $this->input->get_post('date_from_filter') : date("01/m/Y");
                $date_to_filter = ($this->input->get_post('date_to_filter')) ? $this->input->get_post('date_to_filter') : date("t/m/Y");
                $salesrep_filter = $this->input->get_post('sales_rep_filter');
                $state_filter = $this->input->get_post('state_filter');
    
          
                
                $lists = $aa_query2;
    
                // file creation
                $file = fopen('php://output', 'w');
    
                //header
                $header = array("Date", "Age", "Job Type","Address","State","Agency","Job #");
                fputcsv($file, $header);
    
                foreach ($lists->result_array() as $row) {

                    $date1=date_create(date('Y-m-d',strtotime($row['j_created'])));
                    $date2=date_create(date('Y-m-d'));
                    $diff=date_diff($date1,$date2);
                    $age = $diff->format("%a");

    
                    $csvdata['Date'] =$this->customlib->isDateNotEmpty($row['j_date'])?date("d/m/Y",strtotime($row['j_date'])):''; ;
                    $csvdata['Age'] = $age;
                    $csvdata['Job Type'] = $this->gherxlib->getJobTypeAbbrv($row['j_type']);
                    $csvdata['Address'] =$row['p_address_1']." ".$row['p_address_2'].", ".$row['p_address_3'];
                    $csvdata['State'] = $row['p_state'];
                    $csvdata['Agency'] = $row['agency_name'];
                    $csvdata['Job #'] = $row['jid'];

            
                    fputcsv($file, $csvdata);
                }
    
                fclose($file);
                exit;
           

        }else{
       
       
       
       
       
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type','agency_priority', 'agency_priority_marker_definition'),
            'custom_where_arr' => array($custom_where, $agency_priority_custom_where),
            
            'postcodes' => $postcodes,
            'job_type' => $job_type_filter,
            'state_filter' => $state_filter,
            'agency_filter' => $agency_filter,
    
            'limit' => $per_page,
            'offset' => $offset,

            'sort_list' => array(
                array(
                    'order_by' => 'j.created',
                    'sort' => 'ASC',
                ),
            ),

            'display_query' => 0,
        );
        $data['lists'] = $this->jobs_model->get_jobs($params);
        $data['sql_query'] = $this->db->last_query(); //Show query on About

        //all rrows
        $sel_query = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'join_table' => array('agency_priority'),
            'custom_where_arr' => array($custom_where, $agency_priority_custom_where),
            
            'postcodes' => $postcodes,
            'job_type' => $job_type_filter,
            'state_filter' => $state_filter,
            'agency_filter' => $agency_filter,
        );
        $query = $this->jobs_model->get_jobs($params);
        $data['total_rows'] = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $data['total_rows']
        );
        $this->system_model->update_page_total($page_tot_params);


        // FILTERS QUERY

        //Job type Filter
        $sel_query = "DISTINCT(j.`job_type`),
                `j.job_type`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'join_table' => array('job_type'),
            'custom_where' => $custom_filter,
            'sort_list' => array(
                array(
                    'order_by' => 'j.`job_type`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['job_type_filter_json'] = json_encode($params); 

        //State Filter
        $sel_query = "DISTINCT(p.`state`)";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'custom_where' => $custom_filter,
            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['state_filter_json'] = json_encode($params);

         //Agency name filter
         $sel_query = "DISTINCT(a.`agency_id`),
         a.`agency_name`";
         $params = array(
             'sel_query' => $sel_query,
             'p_deleted' => 0,
             'a_status' => 'active',
             'del_job' => 0,
             'country_id' => $country_id,
             'custom_where' => $custom_filter,
             'sort_list' => array(
                 array(
                     'order_by' => 'a.`agency_name`',
                     'sort' => 'ASC',
                 ),
             ),
         );
         $data['agency_filter_json'] = json_encode($params);

          // Region Filter ( get distinct state )
        $sel_query = "p.`state`";
        $region_filter_arr = array(
            'sel_query' => $sel_query,
            'custom_where_arr' => array(
                $custom_filter
            ),
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,

            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                )
            ),
            'group_by' => 'p.`state`',
            'display_query' => 0
        );
        $data['region_filter_json'] = json_encode($region_filter_arr);



        //GET BOOKED COUNT
        $custom_filter = "j.`status` = 'Booked' AND CAST(j.`created` AS DATE) < '{$last_90_days}' ";
        $sel_query_cnt = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query_cnt,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type'),
            'custom_where' => $custom_filter,

            'postcodes' => $postcodes,
            'job_type' => $job_type_filter,
            'state_filter' => $state_filter,
            'agency_filter' => $agency_filter,
            
            'display_query' => 0,
        );
        $data['booked_count'] = $this->jobs_model->get_jobs($params)->row()->jcount;


        $pagi_links_params_arr = array(
            'job_type_filter' => $job_type_filter,
            'state_filter' => $state_filter,
            'agency_filter' => $agency_filter,
            'sub_region_ms' => $sub_region_ms,
            'agency_priority_filter' => $agency_priority_filter
        );
        $pagi_link_params = '/jobs/ageing_jobs_90/?'.http_build_query($pagi_links_params_arr);

        // pagination settings
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $data['total_rows'];
        $config['per_page'] = $per_page;
        $config['base_url'] = $pagi_link_params;
        $this->pagination->initialize($config);

        $data['pagination'] = $this->pagination->create_links();

        // pagination count
        $pc_params = array(
            'total_rows' => $data['total_rows'],
            'offset' => $offset,
            'per_page' => $per_page
        );
        
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

        $this->load->view('templates/inner_header', $data);
        $this->load->view('daily/ageing_jobs_90', $data);
        $this->load->view('templates/inner_footer', $data);

     }
        
    }


    public function cancelled(){

    $data['start_load_time'] = microtime(true);

    $data['title'] = "Cancelled Jobs";
    $job_status = 'Cancelled';

    $country_id = $this->config->item('country');

    $agency_filter = $this->input->get_post('agency_filter');
    $job_filter = $this->input->get_post('job_type_filter');
    $service_filter = $this->input->get_post('service_filter');
    $state_filter = $this->input->get_post('state_filter');
    $date_filter = ( $this->input->get_post('date_filter') !='' )?$this->system_model->formatDate($this->input->get_post('date_filter')):NULL;
    $search = $this->input->get_post('search_filter');

    // pagination
    $per_page = $this->config->item('pagi_per_page');
    $offset = $this->input->get_post('offset');

    
    $sel_query = "
    j.`id` AS jid,
    j.`status` AS j_status,
    j.`service` AS j_service,
    j.`created` AS j_created,
    j.`date` AS j_date,
    j.`comments` AS j_comments,
    j.`job_price` AS j_price,
    j.`job_type` AS j_type,
    j.`cancelled_date`,
    
    p.`property_id` AS prop_id, 
    p.`address_1` AS p_address_1, 
    p.`address_2` AS p_address_2, 
    p.`address_3` AS p_address_3,
    p.`state` AS p_state,
    p.`postcode` AS p_postcode,
    p.`comments` AS p_comments, 
    
    a.`agency_id` AS a_id,
    a.`agency_name` AS agency_name,
    a.`phone` AS a_phone,
    a.`address_1` AS a_address_1, 
    a.`address_2` AS a_address_2, 
    a.`address_3` AS a_address_3,
    a.`state` AS a_state,
    a.`postcode` AS a_postcode,
    a.`trust_account_software`,
    a.`tas_connected`,
    aght.priority,
    apmd.abbreviation,
    
    ajt.`id` AS ajt_id,
    ajt.`type` AS ajt_type
    ";

    $params = array(
        'sel_query' => $sel_query,
        'a_status' => 'active',
        'job_status' => $job_status,
        'country_id' => $country_id,
        'join_table' => array('job_type','alarm_job_type', 'agency_priority', 'agency_priority_marker_definition'),
        
        'agency_filter' => $agency_filter,
        'job_type' => $job_filter,
        'service_filter' => $service_filter,
        'state_filter' => $state_filter,
        'cancelled_date'=> $date_filter,
        'search' => $search,
        'del_job' => 0,

        'limit' => $per_page,
        'offset' => $offset,
        'sort_list' => array(
            array(
                'order_by' => 'j.`cancelled_date`',
                'sort' => 'DESC',
            ),
            array(
                'order_by' => 'j.`created`',
                'sort' => 'DESC',
            )
        ),
        'display_query' => 0
    );
    $data['lists'] = $this->jobs_model->get_jobs($params);
    $data['sql_query'] = $this->db->last_query();

    // all rows
    $sel_query = "COUNT(j.`id`) AS jcount";
    $params = array(
        'sel_query' => $sel_query,
        'a_status' => 'active',
        'job_status' => $job_status,
        'country_id' => $country_id,
        'join_table' => array('job_type','alarm_job_type'),
        
        'agency_filter' => $agency_filter,
        'job_type' => $job_filter,
        'service_filter' => $service_filter,
        'state_filter' => $state_filter,
        'cancelled_date'=> $date_filter,
        'search' => $search,
    );
    $query = $this->jobs_model->get_jobs($params);
    $total_rows = $query->row()->jcount;

    //Job type Filter
    $sel_query = "DISTINCT(j.`job_type`),
    `j.job_type`";
    $params = array(
    'sel_query' => $sel_query,
    'a_status' => 'active',
    'job_status' => $job_status,
    'country_id' => $country_id,
    'join_table' => array('job_type'),
    'sort_list' => array(
        array(
            'order_by' => 'j.`job_type`',
            'sort' => 'ASC',
        ),
    ),
    );
    $data['job_type_filter_json'] = json_encode($params);

     //Services Filter
     $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
     $params = array(
     'sel_query' => $sel_query,
     'a_status' => 'active',
     'job_status' => $job_status,
     'country_id' => $country_id,
     'join_table' => array('alarm_job_type'),
     'sort_list' => array(
         array(
             'order_by' => 'ajt.`type`',
             'sort' => 'ASC',
         ),
     ),
     );
     $data['service_filter_json'] = json_encode($params);

      //State filter
      $sel_query = "DISTINCT(p.`state`),
      p.`state`";
      $params = array(
          'sel_query' => $sel_query,
          'a_status' => 'active',
          'job_status' => $job_status,
          'country_id' => $country_id,
          'sort_list' => array(
              array(
                  'order_by' => 'p.`state`',
                  'sort' => 'ASC',
              ),
          ),
      );
      $data['state_filter_json'] = json_encode($params);


        //Agency  filter
        $sel_query = "DISTINCT(a.`agency_id`),
        a.`agency_name`";
        $params = array(
            'sel_query' => $sel_query,            
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'job_status' => $job_status,
            'country_id' => $country_id,            
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['agency_filter_json'] = json_encode($params);

      $pagi_links_params_arr = array(
        'job_type_filter' => $job_filter,
        'service_filter' => $service_filter,
        'state_filter' => $state_filter,
        'date_filter' => $date_filter,
        'search_filter' => $search
    );
    $pagi_link_params = '/jobs/cancelled/?'.http_build_query($pagi_links_params_arr);


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


    $this->load->view('templates/inner_header', $data);
    $this->load->view('jobs/cancelled', $data);
    $this->load->view('templates/inner_footer', $data);
    }


    public function ajax_move_to_merged(){

        $job_id_arr = $this->input->post('job_id'); 
        $staff_id = $this->session->staff_id;       

        if(!empty($job_id_arr)){

            foreach($job_id_arr as $job_id){

                if( $job_id > 0 ){

                    // update job to merged
                    $this->db->query("
                        UPDATE `jobs`
                        SET `status` = 'Merged Certificates'
                        WHERE `id` = {$job_id}
                    ");

                    // insert job log
                    $log_details = "Moved to <strong>Merged Certificates</strong>";
                    $log_params = array(
                        'title' => 27,  //merge certificate
                        'details' => $log_details,
                        'display_in_vjd' => 1,
                        'created_by_staff' => $staff_id,
                        'job_id' => $job_id
                    );
                    $this->system_model->insert_log($log_params);

                }

            }

        }

    }
    

    public function ajax_rebook_script(){

        $job_id_arr = $this->input->post('job_id');
        $is_240v = $this->input->post('is_240v');
        $isDHA = $this->input->post('isDHA');

        if(!empty($job_id_arr)){

            foreach($job_id_arr as $job_id){

                if( $job_id > 0 ){

                    // get job type
                    $job_sql = $this->db->select('key_access_required,status,job_reason_id,assigned_tech,date,key_access_details,job_type,comments')->where('id',$job_id)->get('jobs');
                    $j = $job_sql->row_array();


                    // Tech Run Keys - Key Access Required Marker   
                    $kar_sql_str = '';
                    $append_kar_update = '';
                    if( $j['key_access_required']==1 ){
                        
                        // if rebooked job is no show then add a marker to show in on tech keys page
                        if( $j['status'] == 'Pre Completion' && $j['job_reason_id']==1 ){
                            $append_kar_update = ',`rebooked_no_show` = 1';
                        }
                        
                        $kar_sql_str = "
                            ,`trk_kar` = '1'
                            ,`trk_tech` = '{$j['assigned_tech']}'
                            ,`trk_date` = '{$j['date']}'
                            ,`tkr_approved_by` = '{$j['key_access_details']}'
                            {$append_kar_update}
                        ";
                    }

                    $status_txt = '';                
                    $log_title_id = 35; //default Rebook title
                    
                    if( $isDHA==1 ){ // DHA
                        $status_txt = 'DHA';                    
                        $log_title_id = 33; // Rebook DHA log title
                    }else{
                        $status_txt = 'To Be Booked';
                    }
                    
                    $job_type_txt = null;
                    $update_job_comments = null;
                    if( $is_240v==1 ){ // 240v rebook
                        $job_type_txt = " `is_eo` = 1, ";                    
                        $log_title_id = 34; //Rebook 240 log title

                        // this needs to logged like it was updated to 240v rebook
                        $this->system_model->insert_job_markers($job_id,'240v Rebook');
                        $update_job_comments = " `comments` = '240v Rebook Job - {$j['comments']}', ";
                    }

                

                    // update job
                    $this->db->query("
                    UPDATE `jobs`
                        SET 
                            {$job_type_txt}
                            `status` = '{$status_txt}',
                            {$update_job_comments}
                            `date` = NULL,
                            `time_of_day` = NULL,
                            `assigned_tech` = NULL,
                            `ts_completed` = 0,
                            `ts_techconfirm` = NULL,
                            `cw_techconfirm` = NULL,
                            `ss_techconfirm` = NULL,
                            `job_reason_id` = 0,
                            `door_knock` = 0,
                            `completed_timestamp` = NULL,
                            `tech_notes` = NULL,
                            `job_reason_comment` = NULL,
                            `booked_with` = NULL,
                            `booked_by` = NULL,
                            `key_access_required` = 0,
                            `key_access_details` = NULL,
                            `call_before` = NULL,
                            `call_before_txt` = NULL,
                            `sms_sent` = NULL,
                            `client_emailed` = NULL,
                            `sms_sent_merge` = NULL,
                            `job_priority` = NULL
                            {$kar_sql_str}
                        WHERE `id` = {$job_id}
                    ");

                    // clear airtable
                    $this->db->query("
                    DELETE 
                    FROM `airtable`
                    WHERE `job_id` = {$job_id}
                    ");

                    // job type update
                    if($is_240v==1){
                        
                        // insert job log
                        $jl_msg = "Job is marked as Electrician Only(EO)";
                        $log_params = array(
                            'title' => $log_title_id,  //rebook title
                            'details' => $jl_msg,
                            'display_in_vjd' => 1,
                            'created_by_staff' => $this->session->staff_id,
                            'job_id' => $job_id
                        );
                        $this->system_model->insert_log($log_params);

                    }
    
                    // job status update                   
                    // insert job log
                    $jl_msg = "Job status updated from <strong>{$j['status']}</strong> to <strong>{$status_txt}</strong>";
                    $log_params = array(
                        'title' => $log_title_id,  //rebook title
                        'details' => $jl_msg,
                        'display_in_vjd' => 1,
                        'created_by_staff' => $this->session->staff_id,
                        'job_id' => $job_id
                    );
                    $this->system_model->insert_log($log_params);
                    

                }                           


            }
            
        }

    }


    public function missed_jobs(){

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Missed Jobs";
        $uri = '/jobs/missed_jobs';
        $data['uri'] = $uri;

        $export = $this->input->get_post('export');
        $data['search_post'] = $this->input->get_post('btn_search');
        $job_type_filter = $this->input->get_post('job_type_filter');

        $date_from_filter = ($this->input->get_post('date_from_filter')!="")?$this->system_model->formatDate($this->input->get_post('date_from_filter')):date('Y-m-d');
        $date_to_filter = ($this->input->get_post('date_to_filter')!="")?$this->system_model->formatDate($this->input->get_post('date_to_filter')):date('Y-m-d');
        $reason = $this->input->get_post('reason_filter');
        $tech = $this->input->get_post('tech_filter');
        $dk = ($this->input->get_post('dk')!="")?$this->input->get_post('dk'):0;
        $agency_filter = $this->input->get_post('agency_filter');
        $include_dk = ( $this->input->get_post('include_dk') != '' )?$this->input->get_post('include_dk'):0;

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');      

        $custom_where = null;
        if( $include_dk != 1 ){ // default

            // exclude DK and 'DK Nobody Home', 'DK Refused Entry','DK Refused COVID' job not completed reason
            $custom_where = 'jnc.door_knock != 1 AND jnc.reason_id NOT IN(16, 32, 33)';

        }            

        //get missed jobs
        $sel_query = "
            j.`id` AS jid, 
            j.`door_knock`,
            j.`created` AS jcreated,
            j.job_price,
            j.job_type,
            
            jnc.jobs_not_completed_id,
            jnc.reason_id,
            jnc.reason_comment,
            jnc.tech_id as jnc_tech_id,
            jnc.door_knock as jnc_door_knock,
            jnc.date_created as jnc_date_created,

            ass_tech.`StaffID` AS jl_staff_id,
            ass_tech.`FirstName` AS jl_staff_fname,
            ass_tech.`LastName` AS jl_staff_lname,
            
            jr.`name` AS jr_name,  
            jr.log_message,
            
            p.`property_id`,
            p.`address_1`, 
            p.`address_2`, 
            p.`address_3`,

            a.agency_id,
            a.`agency_name`,
            aght.priority
        ";
        $params = array(
            'sel_query' => $sel_query,

            'date_from_filter' => $date_from_filter,
            'date_to_filter' => $date_to_filter,
            'job_type_filter' => $job_type_filter,
            'agency_filter' => $agency_filter,
            'custom_where' => $custom_where,
            'reason' => $reason,
            'tech_filter' => $tech,
            'sort_list' => array(
                array(
                    'order_by' => 'jnc.date_created',
                    'sort' => 'DESC',
                ),
            ),
            'display_query' => 0
            
        );

        if ($export != 1){
            $params['limit'] = $per_page;
            $params['offset'] = $offset;
        }

        $data['lists'] = $this->jobs_model->getJobsNotCompletedV3($params);
        $data['sql_query'] = $this->db->last_query();

        // export
        if ($export == 1) {     
            
            // file name
            $date_export = date('d/m/Y');
            $filename = "missed_jobs_{$date_export}.csv";

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            // file creation 
            $csv_file = fopen('php://output', 'w');

            $csv_header = array("Date", "Time", "Age", "Price", "Technician", "Property", "Agency", "Reason", "Comments", "DK",  "Job Type");
            fputcsv($csv_file, $csv_header);
            
            foreach ($data['lists']->result() as $row) { 

                $csv_row = [];                
                
                $csv_row[] = date("d/m/Y",strtotime($row->jnc_date_created));
                $csv_row[] = date("H:i",strtotime($row->jnc_date_created));
                $csv_row[] = $this->gherxlib->getAge($row->jnc_date_created);
                $csv_row[] = number_format($this->system_model->price_ex_gst($row->job_price), 2);
                $csv_row[] = $this->system_model->formatStaffName($row->jl_staff_fname, $row->jl_staff_lname);
                $csv_row[] = "{$row->address_1} {$row->address_2}, {$row->address_3}";
                $csv_row[] = $row->agency_name;
                $csv_row[] = $row->jr_name;
                $csv_row[] = $row->reason_comment;
                $csv_row[] = ( $row->jnc_door_knock == 1 )?'Yes':null;
                $csv_row[] = $row->job_type;

                fputcsv($csv_file,$csv_row); 
            }
        
            fclose($csv_file);             
            
        }else{

            //total 
            $sel_query = "COUNT(jnc.jobs_not_completed_id) AS countbaby";
            $params = array(
                'sel_query' => $sel_query,
                'date_from_filter' => $date_from_filter,
                'date_to_filter' => $date_to_filter,
                'job_type_filter' => $job_type_filter,
                'reason' => $reason,
                'tech_filter' => $tech,
                'agency_filter' => $agency_filter,
                'custom_where' => $custom_where                
            );
            $total_rows  = $this->jobs_model->getJobsNotCompletedV3($params)->row()->countbaby;

            


            //get tech
            // $data['tech_list'] =  $this->customlib->getStaffData($this->config->item('country'));
            $params = array(
                'sel_query' => "DISTINCT(ass_tech.StaffID), ass_tech.FirstName, ass_tech.LastName, ass_tech.is_electrician",

                'date_from_filter' => $date_from_filter,
                'date_to_filter' => $date_to_filter,
                'job_type_filter' => $job_type_filter,
                'agency_filter' => $agency_filter,
                'custom_where' => $custom_where,
                'reason' => $reason,

                'sort_list' => array(
                    array(
                        'order_by' => 'ass_tech.FirstName',
                        'sort' => 'ASC',
                    ),
                ),
                'display_query' => 0
            );
            $data['tech_list'] = $this->jobs_model->getJobsNotCompletedV3($params);

            //Job type Filter
            $sel_query = "DISTINCT(j.`job_type`)";
            $params = array(
                'sel_query' => $sel_query,
                'del_job' => 0,
                'p_deleted' => 0,
                'a_status' => 'active',

                'date_from_filter' => $date_from_filter,
                    'date_to_filter' => $date_to_filter,            
                    'agency_filter' => $agency_filter,
                    'custom_where' => $custom_where,
                    'reason' => $reason,
                    'tech_filter' => $tech,

                'country_id' => $this->config->item('country'),
                'sort_list' => array(
                    array(
                        'order_by' => 'j.`job_type`',
                        'sort' => 'ASC',
                    )
                ),
                'display_query' => 0
            );
            $data['job_type_sql_filter'] = $this->jobs_model->getJobsNotCompletedV3($params);

            //reason filter 
            $data['reason_list'] =  $this->db->select('job_reason_id,name')->order_by('name', 'ASC')->get('job_reason');


            //get missed jobs
            $sel_query = "DISTINCT(a.`agency_id`), a.`agency_name`, aght.`priority`";
            $params = array(
                'sel_query' => $sel_query,

                'date_from_filter' => $date_from_filter,
                'date_to_filter' => $date_to_filter,
                'job_type_filter' => $job_type_filter,            
                'custom_where' => $custom_where,
                'reason' => $reason,
                'tech_filter' => $tech,

                'limit' => $per_page,
                'offset' => $offset,
                'sort_list' => array(
                    array(
                        'order_by' => 'a.agency_name',
                        'sort' => 'ASC',
                    ),
                ),
                'display_query' => 0
                
            );
            $data['agency_filter_sql'] = $this->jobs_model->getJobsNotCompletedV3($params);


            $pagi_links_params_arr = array(
                'date_from_filter' => $date_from_filter,
                'date_to_filter' => $date_to_filter,
                'job_type_filter' => $job_type_filter,
                'reason_filter' => $reason,
                'tech_filter' => $tech,
                'agency_filter' => $agency_filter,                
                'include_dk' => $include_dk
            );
            $pagi_link_params = '/jobs/missed_jobs/?'.http_build_query($pagi_links_params_arr);
            $data['pagi_links_params_arr'] = $pagi_links_params_arr;

            // explort link
            $data['export_link'] = "{$uri}/?export=1&".http_build_query($pagi_links_params_arr);

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

            //load views
            $this->load->view('templates/inner_header', $data);
            $this->load->view('jobs/missed_jobs', $data);
            $this->load->view('templates/inner_footer', $data);

        }                    

    }


    public function booked_report(){

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Booked Report";

        $service = $this->input->get_post('service_filter');
        $tech = $this->input->get_post('tech_filter');
        $search = $this->input->get_post('search_filter');

        $data['date'] = ($this->input->get_post('day')!='' && $this->input->get_post('month')!='' && $this->input->get_post('year')!='')?"{$this->input->get_post('year')}-{$this->input->get_post('month')}-{$this->input->get_post('day')}":date("Y-m-d");

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $sel_query = "
            j.`id` AS jid, 
            j.`created` AS jcreated, 
            j.`service` AS jservice, 
            jr.`name` AS jr_name, 
            j.`status` AS jstatus, 
            j.`date` AS jdate,
            j.ts_completed,
            j.job_reason_id,
            j.job_type,
            j.job_price,
            j.assigned_tech,
            j.door_knock as dk,
            p.property_id as prop_id,
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3, 
            p.`state` AS p_state,
            ajt.type as ajt_type,
            sa.FirstName as staff_fname,
            sa.LastName as staff_lname
            ";
        $params = array(
            'sel_query' => $sel_query,
            'service' => $service,
            'search' => $search,
            'tech_id' => $tech,
            'date' => $data['date'],

            'limit' => $per_page,
            'offset' => $offset,
            'sort_list' => array(
                array(
                    'order_by' => 'j.`date`',
                    'sort' => 'ASC',
                )
            )

        );
        $data['lists'] =  $this->jobs_model->bkd_getPrecompletedJobs($params);


        //Total
        $sel_query = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query,
            'service' => $service,
            'search' => $search,
            'tech_id' => $tech,
            'date' => $data['date']
        );
       $query =  $this->jobs_model->bkd_getPrecompletedJobs($params);
       $total_rows = $query->row()->jcount;


       //Services Filter
        $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
        $params = array(
            'sel_query' => $sel_query,
            'sort_list' => array(
                array(
                    'order_by' => 'ajt.`type`',
                    'sort' => 'ASC',
                ),
            )
        );
        $data['service_filter'] = $this->jobs_model->bkd_getPrecompletedJobs($params);
        

       //get tech
       $data['tech_list'] =  $this->customlib->getStaffData($this->config->item('country'));


       $pagi_links_params_arr = array(
        'service_filter' => $service,
        'tech_filter' => $tech,
        'search_filter' => $search
        );
        $pagi_link_params = '/jobs/booked_report/?'.http_build_query($pagi_links_params_arr);


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

        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('jobs/booked_report', $data);
        $this->load->view('templates/inner_footer', $data);
        
    }


    //Update Job TYpe to rebook via ajax
    public function ajax_update_job_type(){

        $json_data['status'] = false;

        $job_id = $this->input->post('job_id');
        $job_type = $this->input->post('job_type');
        $staff_id = $this->session->staff_id;

        if( $job_id && !empty($job_id) && is_numeric($job_id) && $job_type != '' ){   
            
            // get current job type
            $old_job_sql = $this->db->query("
            SELECT `job_type`
            FROM `jobs`
            WHERE `id` = {$job_id}
            ");
            $old_job_row = $old_job_sql->row();            

            // update job type
            $this->db->query("
            UPDATE `jobs`
            SET `job_type` = '{$job_type}'
            WHERE `id` = {$job_id}
            ");

            if($this->db->affected_rows()>0){
                
                //Insert Log
                $log_title = 63; // Job Update
                $log_details = "Job Type updated from <b>{$old_job_row->job_type}</b> to <b>{$job_type}</b>";

                $log_params = array(
                    'title' => $log_title, 
                    'details' => $log_details,
                    'display_in_vjd' => 1,
                    'created_by_staff' => $staff_id,
                    'job_id' => $job_id
                );
                $this->system_model->insert_log($log_params);

                $json_data['status'] = true;
                $json_data['msg'] = "Job Type successfully updated";

            }

        }

        echo json_encode($json_data);

    }


    public function new_jobs_report(){

        $data['start_load_time'] = microtime(true);
        $data['title'] = "New Jobs Report";

        $data['bntPost'] = $this->input->post('btnGetStats');

        //pass and set data variable
        $data['from'] = ($this->input->get_post('date_from_filter'))?$this->input->get_post('date_from_filter'):date("01/m/Y");
        $data['to'] = ($this->input->get_post('date_to_filter'))?$this->input->get_post('date_to_filter'):date("t/m/Y");

        $state_filter = $this->input->get_post('state_filter');


        $data['prev_day'] = array(
            'from' => date('d/m/Y', strtotime('-1 day', strtotime(str_replace("/","-",$data['from'])))),
            'to' => date('d/m/Y', strtotime('-1 day', strtotime(str_replace("/","-",$data['from'])))),
            'title' => '<em class="fa fa-arrow-circle-left"></em> Previous Day '
        );
        
        $data['next_day'] = array(
            'from' => date('d/m/Y', strtotime('+1 day', strtotime(str_replace("/","-",$data['from'])))),
            'to' => date('d/m/Y', strtotime('+1 day', strtotime(str_replace("/","-",$data['from'])))),
            'title' => 'Next Day <em class="fa fa-arrow-circle-right"></em> ',
            'css' => 'float: right;'
        );
        
        $staff_id = ($this->input->get_post('sid') ? (int)$this->input->get_post('sid'): "z");
        $tech_id = ($this->input->get_post('tid') ? (int)$this->input->get_post('tid'): "z");

        if($this->input->post('btnGetStats') || $this->input->get_post('get_sats')==1){
            
             // pagination
             $per_page = $this->config->item('pagi_per_page');
             $offset = ($this->input->get_post('offset')!="")?$this->input->get_post('offset'):0;
 
        
            # Get Staff details for display if needed
            /*
            if($staff_id === 0)
            {
                $staff_details['FirstName'] = "SATS System";
            }
            elseif(is_int($staff_id))
            {
                $staff_details = $this->user_class_model->getUserDetails($staff_id);
            }
            */
            
            # Get Tech details for display if needed
            /*
            if($tech_id === 0)
            {
                $tech_details['first_name'] = "Unassigned";
            }
            elseif(is_int($tech_id))
            {
                $tech_details = $this->user_class_model->getTechDetails($tech_id);
            }
            */
            
            # Staff and tech id's to filter
            $data['staff_filter'] = array(
                'staff_id' => $staff_id,
                'tech_id' => $tech_id
            );

           # $report_params = array('date_from_filter' => $data['from'], 'date_to_filter' => $data['to'], 'staff_id' => $staff_id, 'tech_id' => $tech_id);
        
        

            //get all job type and return array
            $data['jt_arr_sql']  = $this->db->get('job_type')->result_array();


            //GET LIST
            $sel_query = "DISTINCT(a.`agency_id`), a.`agency_name`, a.`salesrep`, a.`state`, aght.priority";
            $params = array(
                'sel_query' => $sel_query,
                'from' => $data['from'],
                'to' => $data['to'],
                'state' => $state_filter,
                'display_query' => 0
            );
            $data['sr_sql'] = $this->jobs_model->get_num_services($params);
            
        }



        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/new_jobs_report', $data);
        $this->load->view('templates/inner_footer', $data);
    }


    public function export_new_jobs_report(){


        //pass and set data variable
        $from = ($this->input->get_post('date_from_filter'))?$this->input->get_post('date_from_filter'):date("01/m/Y");
        $to = ($this->input->get_post('date_to_filter'))?$this->input->get_post('date_to_filter'):date("t/m/Y");

        $state_filter = $this->input->get_post('state_filter');



        $filename = "new_jobs_report_".rand()."_".date('YmdHis').".csv";
    
        header("Content-Type: text/csv");
        header("Content-Disposition: Attachment; filename={$filename}");
        header("Pragma: no-cache");
        

         //GET LIST
         $sel_query = "DISTINCT(a.`agency_id`), a.`agency_name`, a.`salesrep`, a.`state`";
         $params = array(
             'sel_query' => $sel_query,
             'from' => $from,
             'to' => $to,
             'state' => $state_filter,
             'display_query' => 0
         );
         $sr_sql = $this->jobs_model->get_num_services($params);


         //get all job type and return array
        $jt_arr_sql  = $this->db->get('job_type')->result_array();


        //FOR HEADER
        $export = [];
        $jt_arr = [];
        $jt_arr2 = array();
        foreach($jt_arr_sql as $jt){ 
            $jt_arr[] = array('job_type'=> $jt['job_type']);
            $jt_arr2[] =  $jt['job_type'];
        }


        foreach($sr_sql->result_array() as $sr){
                    
            $salesrep = '';
            
            // job types
            $jt_count = [];
            $jt_tot = 0;
            foreach( $jt_arr as $job_type ){

                $sel_query = " COUNT(j.`id`) AS jcount ";
                $params = array(
                    'sel_query' => $sel_query,
                    'agency_id' => $sr['agency_id'],
                    'job_type' => $job_type['job_type'],
                    'from' => $from,
                    'to' => $to,
                    'state' => $this->input->get_post('state_filter')
                );
                $serv_ret = $this->jobs_model->get_num_services($params)->row()->jcount;


                $jt_count[] = ($serv_ret>0)?$serv_ret:'';
                $jt_tot += $serv_ret;
            }
            

            // total new
            $total_new = ($jt_tot>0)? $jt_tot:'';
            
            // total amount
            $tot_jp = $this->jobs_model->getJobPriceTotal_v2($sr['agency_id'],$from,$to); 
            $total_amount = ($tot_jp>0)?$tot_jp:0;
            
            // deleted
            $deleted = $this->jobs_model->get_deleted($sr['agency_id'],1,$from,$to)->num_rows();
            $deleted_tot = ($deleted>0)?$deleted:'';
            
            // net 
            $net = ($jt_tot-$deleted_tot); 
            
            // Added by Agency
            $add_by_agency = $this->jobs_model->getAddedByAgency($sr['agency_id'],$from,$to); 
            $added_by_agency = ($add_by_agency>0)?$add_by_agency:'';
            
            // added by SATS
            $add_by_sats = $this->jobs_model->getAddedBySats($sr['agency_id'],$from,$to); 
            $added_by_sats = ($add_by_sats>0)?$add_by_sats:'';
            
            // salesrep
            $salesrep_sql = $this->jobs_model->this_getAgencySalesRep($sr['salesrep']);
            $salesrep = $salesrep_sql->row_array();
            
            $export[] = array(
                'agency_id' => $sr['agency_id'],
                'agency' => $sr['agency_name'],
                'state' => $sr['state'],
                'job_type_count' => $jt_count,
                'total_new' => $total_new,
                'total_amount' => $total_amount,
                'deleted_tot' => $deleted_tot,
                'net' => $net,
                'added_by_agency' => $added_by_agency,
                'added_by_sats' => $added_by_sats,
                'salesrep' => "{$salesrep['FirstName']} {$salesrep['LastName']}"
            );
            
        }
        

        // job type
        $jt_str = implode(",",$jt_arr2);

        // headers
        $export_str = "Agency,State,".$jt_str.",Total New,Total $,Deleted,Net,Added By Agency,Added By SATS,Salesrep\n";
      
        foreach( $export as $exp_row ){
            $exp_jt_str = implode(",",$exp_row['job_type_count']);
            $total_amount_fin = "$".number_format($exp_row['total_amount'],2);
            $export_str .= "\"{$exp_row['agency']}\",\"{$exp_row['state']}\",".$exp_jt_str.",\"{$exp_row['total_new']}\",\"{$total_amount_fin}\",\"{$exp_row['deleted_tot']}\",\"{$exp_row['net']}\",\"{$exp_row['added_by_agency']}\",\"{$exp_row['added_by_sats']}\",\"{$exp_row['salesrep']}\"\n";
        }

        
        echo $export_str;
        exit();

    }



    public function future_pendings(){

        $data['start_load_time'] = microtime(true);
        $uri = '/jobs/future_pendings';
        $data['uri'] = $uri;

        $btnGetStats = $this->input->get_post('btnGetStats');
        $get_sats = $this->input->get_post('get_sats');
        $country_id = $this->config->item('country');

        $agency_filter = $this->input->get_post('agency_filter');
        $state_filter = $this->input->get_post('state_filter');
        $search = $this->input->get_post('search');

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }    

        $export = $this->input->get_post('export');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ($this->input->get_post('offset')!="")?$this->input->get_post('offset'):0;

        $sql_from = ( $this->input->get_post('date_from_filter')!= "" )?$this->input->get_post('date_from_filter'):date("Y-m-01");
        $sql_to = ( $this->input->get_post('date_to_filter') != "" )?$this->input->get_post('date_to_filter'):date("Y-m-t");

        $next_month = date("m",strtotime("{$sql_from} +1 month"));
        $month_text = date("F",strtotime("{$sql_from} +1 month"));
        $last_year = date("Y",strtotime("{$sql_from} -1 year"));
        $last_day_of_month = date("t",strtotime("{$sql_from} -1 year"));    

        $data['title'] = "{$month_text} Service Due";

        
        //NEXT AND PREV DATE
        $data['prev_day'] = array(
            'from' => date("Y-m-01",strtotime("{$sql_from} -1 month")),
            'to' => date("Y-m-t",strtotime("{$sql_from} -1 month")),
            'title' => '<em class="fa fa-arrow-circle-left"></em> Previous Month '
        );
        
        $data['next_day'] = array(
            'from' => date("Y-m-01",strtotime("{$sql_to} +1 month")),
            'to' => date("Y-m-t",strtotime("{$sql_to} +1 month")),
            'title' => 'Next Month <em class="fa fa-arrow-circle-right"></em> ',
            'css' => 'float: right;'
        );

        $custom_date_filter = "j.date BETWEEN '{$last_year}-{$next_month}-01' AND '{$last_year}-{$next_month}-{$last_day_of_month}'";
        $custom_where = 'ps.service = 1 AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )';
        $sel_query = "
            CONCAT_WS('', LOWER(p.`address_1`), LOWER(p.`address_2`), LOWER(p.`address_3`), LOWER(p.`state`), LOWER(p.`postcode`) ),
            j.`property_id`,
            j.`date` AS jdate,
            
            a.`agency_id`,
            a.`agency_name`,
            aght.priority,
            
            p.`address_1` AS p_address1, 
            p.`address_2` AS p_address2, 
            p.`address_3` AS p_address3, 
            p.`state` AS p_state, 
            p.`postcode` AS p_postcode
        ";
        $jparams = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,
            'custom_joins' => array(
                'join_table' => 'property_services as ps',                
                'join_on' => '(ps.property_id = j.property_id AND j.service = ps.alarm_job_type_id)',
                'join_type' => 'INNER'
            ),
            'job_status' => 'Completed',
            'job_type' => 'Yearly Maintenance',
            'custom_where' => $custom_where,
            'join_table' => array('agency_priority'),
            'postcodes' => $postcodes,
            'agency_filter' => $agency_filter,
            'state_filter' => $state_filter,
            'search' => $search,
            'custom_where_arr' => array(
                $custom_date_filter
            ),

            'display_query' => 0
        );

        // export should show all
        if ( $export != 1 ){ 
            $jparams['limit'] = $per_page;
            $jparams['offset'] = $offset;
        }

        $job_sql = $this->jobs_model->get_jobs($jparams);
        $data['sql_query'] = $this->db->last_query(); //Show query on About
        if( $btnGetStats || $get_sats==1 ){
            $data['lists'] = $job_sql;                
        }


        if ( $export == 1 ) { //EXPORT         

            // file name
            $date_export = date('YmdHis');
            $filename = "future_pendings_export_{$date_export}.csv";

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            // file creation 
            $csv_file = fopen('php://output', 'w');                    

            $csv_header = array("Property ID","Address","Agency","Next Service Due");
            fputcsv($csv_file, $csv_header);
            
            foreach($job_sql->result_array() as $row){ 

                $csv_row = [];  
                
                $prop_address = "{$row['p_address1']} {$row['p_address2']}, {$row['p_address3']} {$row['p_state']} {$row['p_postcode']}";
                                                
                $csv_row[] = $row['property_id'];
                $csv_row[] = $prop_address;
                $csv_row[] = $row['agency_name'];
                $csv_row[] = date("F Y",strtotime($row['jdate'].' +1 year'));
                
                fputcsv($csv_file,$csv_row); 

            }
        
            fclose($csv_file); 
            exit; 

        }else{

            // total row
            $jparams = array(
                'sel_query' => 'COUNT(j.id) as j_count',
                'del_job' => 0,
                'p_deleted' => 0,
                'a_status' => 'active',
                'country_id' => $country_id,
                'custom_joins' => array(
                    'join_table' => 'property_services as ps',
                    'join_on' => '(ps.property_id = j.property_id AND j.service = ps.alarm_job_type_id)',
                    'join_type' => 'INNER'
                ),
                'job_status' => 'Completed',
                'job_type' => 'Yearly Maintenance',
                'custom_where' => $custom_where,

                'postcodes' => $postcodes,
                'agency_filter' => $agency_filter,
                'state_filter' => $state_filter,
                'search' => $search,
                'custom_where_arr' => array(
                    $custom_date_filter
                )
            ); 
            if( $btnGetStats || $get_sats==1 ){
                $query = $this->jobs_model->get_jobs($jparams);
                $total_rows = $query->row()->j_count;  
            }



            //Agency name filter
            $sel_query = "DISTINCT(a.`agency_id`),
            a.`agency_name`";
            $params = array(
                'sel_query' => $sel_query,
                'del_job' => 0,
                'p_deleted' => 0,
                'a_status' => 'active',
                'country_id' => $country_id,
                'custom_joins' => array(
                    'join_table' => 'property_services as ps',
                    'join_on' => '(ps.property_id = j.property_id AND j.service = ps.alarm_job_type_id)',
                    'join_type' => 'INNER'
                ),
                'job_status' => 'Completed',
                'job_type' => 'Yearly Maintenance',
                'custom_where' => 'ps.service = 1',

                'postcodes' => $postcodes,
                'agency_filter' => $agency_filter,
                'state_filter' => $state_filter,
                'search' => $search,
                'custom_where_arr' => array(
                    $custom_date_filter
                )
            );
            $data['agency_filter_json'] = json_encode($params);

            // Region Filter ( get distinct state )
            $sel_query = "DISTINCT(p.`state`)";
            $region_filter_arr = array(
                'sel_query' => $sel_query,
                'del_job' => 0,
                'p_deleted' => 0,
                'a_status' => 'active',
                'country_id' => $country_id,
                'custom_joins' => array(
                    'join_table' => 'property_services as ps',
                    'join_on' => '(ps.property_id = j.property_id AND j.service = ps.alarm_job_type_id)',
                    'join_type' => 'INNER'
                ),
                'job_status' => 'Completed',
                'job_type' => 'Yearly Maintenance',
                'custom_where' => 'ps.service = 1',

                'postcodes' => $postcodes,
                'agency_filter' => $agency_filter,
                'state_filter' => $state_filter,
                'search' => $search,
                'custom_where_arr' => array(
                    $custom_date_filter
                )
            );       
            $data['region_filter_json'] = json_encode($region_filter_arr);
            

            // state filter
            $sel_query = "DISTINCT(p.`state`)";
            $params = array(
                'sel_query' => $sel_query,
                'del_job' => 0,
                'p_deleted' => 0,
                'a_status' => 'active',
                'country_id' => $country_id,
                'custom_joins' => array(
                    'join_table' => 'property_services as ps',
                    'join_on' => '(ps.property_id = j.property_id AND j.service = ps.alarm_job_type_id)',
                    'join_type' => 'INNER'
                ),
                'job_status' => 'Completed',
                'job_type' => 'Yearly Maintenance',
                'custom_where' => 'ps.service = 1',

                'postcodes' => $postcodes,
                'agency_filter' => $agency_filter,
                'state_filter' => $state_filter,
                'search' => $search,
                'custom_where_arr' => array(
                    $custom_date_filter
                )
            );
            $data['state_filter_json'] = json_encode($params);  


            $pagi_links_params_arr = array(
                'agency_filter' => $agency_filter,
                'search' => $search,
                'state_filter' => $state_filter,
                'sub_region_ms' => $sub_region_ms,
                'get_sats' => 1
            );
            $pagi_link_params = '/jobs/future_pendings/?'.http_build_query($pagi_links_params_arr);
            $data['pagi_links_params_arr'] = $pagi_links_params_arr;

            // explort link
            $data['export_link'] = "{$uri}/?export=1&".http_build_query($pagi_links_params_arr);

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


            //load views
            $this->load->view('templates/inner_header', $data);
            $this->load->view('jobs/future_pendings', $data);
            $this->load->view('templates/inner_footer', $data);

        }                        


    }


    public function export_future_pendings(){

        $agency_filter = $this->input->get_post('agency_filter');
        $state_filter = $this->input->get_post('state_filter');
        $search = $this->input->get_post('search');

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }    

        $from = ( $this->input->get_post('date_from_filter')!= "" )?$this->input->get_post('date_from_filter'):date("Y-m-01",strtotime("+1 month"));
        $to = ( $this->input->get_post('date_to_filter') != "" )?$this->input->get_post('date_to_filter'):date("Y-m-t",strtotime("+1 month"));


       // file name
        $filename = "future_pendings_".date("M/Y",strtotime("+1 month")).".csv";
    
        header("Content-Type: text/csv");
        header("Content-Disposition: Attachment; filename={$filename}");
        header("Pragma: no-cache");

        // headers
        $str = "Property ID,Address,Agency\n";


        //content
        $sql_from = $this->input->get_post('date_from_filter');
        $sql_to = $this->input->get_post('date_to_filter');
        if( $sql_from!="" && $sql_to!="" ){
            $next_month = date("m",strtotime("{$sql_from} +1 month"));
            $last_year = date("Y",strtotime("{$sql_from} -1 year"));
            $last_day_of_month = date("t",strtotime("{$sql_from} -1 year"));    
        }else{
            $next_month = date("m", strtotime("+1 month"));
            $last_year = date("Y",strtotime("-1 year"));
            $last_day_of_month = date("t",strtotime("-1 year"));    
        }
        $custom_date_filter = "j.date BETWEEN '{$last_year}-{$next_month}-01' AND '{$last_year}-{$next_month}-{$last_day_of_month}'";
       
        $sel_query = "
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
        $jparams = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,
            'custom_joins' => array(
                'join_table' => 'property_services as ps',
                'join_on' => '(ps.property_id = j.property_id AND j.service = ps.alarm_job_type_id)',
                'join_type' => 'INNER'
            ),
            'job_status' => 'Completed',
            'job_type' => 'Yearly Maintenance',
            'custom_where' => 'ps.service = 1',

            'postcodes' => $postcodes,
            'agency_filter' => $agency_filter,
            'state_filter' => $state_filter,
            'search' => $search,
            'custom_where_arr' => array(
                $custom_date_filter
            ),

            'offset' => $offset,
            'limit' => $per_page
        );
        $u_sql = $this->jobs_model->get_jobs($jparams);

        foreach($u_sql->result_array() as $u){
            $str .= "{$u['property_id']},\"{$u['p_address1']} {$u['p_address2']} {$u['p_address3']} {$u['p_state']} {$u['p_postcode']}\",\"{$u['agency_name']}\"\n";
        }
        //content end


        echo $str;
        exit;

    }


    public function todays_jobs(){

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Todays Jobs";

        $country_id = $this->config->item('country');
        $search = $this->input->get_post('search');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        //GET ALL LIST
        $sel_query = "
            j.`id` AS jid,
            j.`status` AS j_status,
            j.`service` AS j_service,
            j.`date` AS j_date,
            j.`job_price` AS j_price,
            j.`job_type` AS j_type,
            j.`urgent_job`,
            j.start_date,
            j.due_date,
            j.comments as j_comments,
            j.preferred_time,
            
            p.`property_id` AS prop_id, 
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            p.`comments` AS p_comments, 
            
            a.`agency_id` AS a_id,
            a.`agency_name` AS agency_name,
            a.`postcode` AS a_postcode,
            aght.priority
        ";

        
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',

            'date' => date('Y-m-d'),
            'search' => $search,
            
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type', 'agency_priority'),
            'limit' => $per_page,
            'offset' => $offset,

            'sort_list' => array(   
                array(
                    'order_by' => 'j.`date`',
                    'sort' => 'DESC'
                )
            ),          
            'display_query' => 0
        );
        $data['lists'] = $this->jobs_model->get_jobs($params);

         // all rows
         $sel_query = "COUNT(j.`id`) AS jcount";
         $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',

            'date' => date('Y-m-d'),
            'search' => $search,

            'country_id' => $country_id,
         );
         $query = $this->jobs_model->get_jobs($params);
         $total_rows = $query->row()->jcount;


        //url parameters
        $pagi_links_params_arr = array(
            'search_filter' => $search
        );
        $pagi_link_params = '/jobs/todays_jobs/?'.http_build_query($pagi_links_params_arr);


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


        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('jobs/todays_jobs', $data);
        $this->load->view('templates/inner_footer', $data);


    }


    public function urgent_jobs(){

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Urgent Jobs";
        $job_status = 'To Be Booked';

        $country_id = $this->config->item('country');
        $job_type_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $agency_filter = $this->input->get_post('agency_filter');
        $date_filter = ($this->input->get_post('date_filter')!="")?$this->system_model->formatDate($this->input->get_post('date_filter')):NULL;
        $search = $this->input->get_post('search_filter');
       
        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }   

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');


        //GET ALL LIST
        $sel_query = "
            j.`id` AS jid,
            j.`status` AS j_status,
            j.`service` AS j_service,
            j.`created` AS j_created,   
            j.`date` AS j_date,
            j.`comments` AS j_comments,
            j.`job_price` AS j_price,
            j.`job_type` AS j_type,
            j.`start_date`,
            j.`due_date`,
            j.`no_dates_provided`,
            j.`bne_to_call_notes`,
            
            p.`property_id` AS prop_id, 
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            p.`comments` AS p_comments, 
            p.`deleted` AS p_deleted,
            
            a.`agency_id` AS a_id,
            a.`agency_name` AS agency_name,
            a.`phone` AS a_phone,
            a.`address_1` AS a_address_1, 
            a.`address_2` AS a_address_2, 
            a.`address_3` AS a_address_3,
            a.`state` AS a_state,
            a.`postcode` AS a_postcode,
            aght.priority,
            
            jt.`abbrv`,
            
            ajt.`id` AS ajt_id,
            ajt.`type` AS ajt_type
        ";
        $custom_where = "j.`urgent_job` = 1";  //URGENT JOB
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'custom_where' => $custom_where,
            'job_status' => $job_status,
        
            'job_type' => $job_type_filter,
            'service_filter' => $service_filter,
            'agency_filter' => $agency_filter,
            'date' => $date_filter,
            'search' => $search,
            'postcodes' => $postcodes,
            
            'country_id' => $country_id,
            
            'join_table' => array('job_type','alarm_job_type', 'agency_priority'),

            'limit' => $per_page,
            'offset' => $offset,

            'sort_list' => array(   
                array(
                    'order_by' => 'j.`created`',
                    'sort' => 'DESC'
                )
            ),          
            'display_query' => 0
        );
        $data['lists'] = $this->jobs_model->get_jobs($params);

        // Get all total rows
        $sel_query = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,
            'custom_where' => $custom_where,
            'job_status' => $job_status,
        
            'job_type' => $job_type_filter,
            'service_filter' => $service_filter,
            'agency_filter' => $agency_filter,
            'date' => $date_filter,
            'search' => $search,
            'postcodes' => $postcodes,
            
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;

        //Job type Filter
        $sel_query = "DISTINCT(j.`job_type`),`j.job_type`";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,
            'custom_where' => $custom_where,
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

        //Services Filter
        $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,
            'custom_where' => $custom_where,
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

        //Agency name filter
        $sel_query = "DISTINCT(a.`agency_id`),
        a.`agency_name`";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,
            'custom_where' => $custom_where,
            'job_status' => $job_status,
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['agency_filter_json'] = json_encode($params);

        // Region Filter ( get distinct state )
        $sel_query = "p.`state`";
        $region_filter_arr = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,
            'custom_where' => $custom_where,
            'job_status' => $job_status,
            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                )
            ),
            'group_by' => 'p.`state`',
            'display_query' => 0
        );
        $data['region_filter_json'] = json_encode($region_filter_arr);
        
        $pagi_links_params_arr = array(
            'job_type_filter' => $job_type_filter,
            'service_filter' => $service_filter,
            'agency_filter' => $agency_filter,
            'date_filter' => $date_filter,
            'search_filter' => $search,
            'sub_region_ms' => $sub_region_ms
        );
        $pagi_link_params = '/jobs/urgent_jobs/?'.http_build_query($pagi_links_params_arr);


        // Pagination settings
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


        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('jobs/urgent_jobs', $data);
        $this->load->view('templates/inner_footer', $data);

    }

    public function completed_report(){

        $data['start_load_time'] = microtime(true);
        
        $data['title'] = "Completed Report";

        $data['display_data'] =  ( $this->input->get_post('display_data') !='' )? $this->input->get_post('display_data') : false;

        $data['from'] = ( $this->input->get_post('date_from_filter') !='' )?$this->input->get_post('date_from_filter'):date('Y-m-01');
        $data['to'] = ( $this->input->get_post('date_to_filter') !='' )?$this->input->get_post('date_to_filter'):date('Y-m-t');
        $data['ajt_id'] = 0;

        //NEXT AND PREV DATE
        $data['prev_day'] = array(
            'from' => date("Y-m-01",strtotime("-1 month")),
            'to' => date("Y-m-t",strtotime("-1 month")),
            'title' => '<em class="fa fa-arrow-circle-left"></em> Previous Month '
        );
        
        $data['next_day'] = array(
            'from' => date("Y-m-01",strtotime("+1 month")),
            'to' => date("Y-m-t",strtotime("+1 month")),
            'title' => 'Next Month <em class="fa fa-arrow-circle-right"></em> ',
            'css' => 'float: right;'
        );


        //GET ALL ALARM JOB TYPE
        $data['ajt'] = $this->db->select('id,type')->where('active',1)->order_by('id','DESC')->get('alarm_job_type');
        
        // get job types
        $params = array( 
            "custom_where" => "job_type != '240v Rebook'",
            'display_query' => 0
         );
        $data['job_types'] = $this->jobs_model->get_job_types($params);

        // echo json_encode($data);
        // die();

         //load views
         $this->load->view('templates/inner_header', $data);
         $this->load->view('jobs/completed_report', $data);
         $this->load->view('templates/inner_footer', $data);
    }


    public function ajax_completed_report(){

        $from = $this->input->post('from');
        $to = $this->input->post('to');
        $ajt_id = $this->input->post('ajt_id');

        // get job types
        $params = array( 
            "custom_where" => "job_type != '240v Rebook'",
            'display_query' => 0
         );
        $data['job_types'] = $this->jobs_model->get_job_types($params);

        // echo json_encode($data);
        // die();
         //load views
        # $this->load->view('templates/inner_header', $data);
         $this->load->view('jobs/ajax_completed_report', $data);
        # $this->load->view('templates/inner_footer', $data);

    }

    public function status(){

        $this->load->model('agency_model');

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Status";

        //deactivated agency count
        $params = array(
            'sel_query' => "COUNT(a.`agency_id`) AS a_count",
            'a_status' => 'deactivated'
        );
        $agency = $this->agency_model->get_agency($params); 
        $data['inactive_agency_count'] = $agency->row()->a_count;

        // IC upgrade        
        $upgrade_brooks_str = "
        SELECT COUNT(j.`id`) AS jcount
        FROM `jobs` AS j
        LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN `agency` AS a ON  p.`agency_id` = a.`agency_id`
        LEFT JOIN `job_type` AS jt ON j.`job_type` = jt.`job_type`
        WHERE j.`del_job` = 0
        AND p.`deleted` = 0
        AND a.`status` = 'active'			
        AND j.`status` = 'To Be Booked'
        AND j.`job_type` = 'IC Upgrade'        
        ";
        $upgrade_brooks_sql = $this->db->query($upgrade_brooks_str);
        $data['ic_upgrade_count'] = $upgrade_brooks_sql->row()->jcount;

        // brooks upgrade count        
        $upgrade_brooks_str = "
        SELECT COUNT(j.`id`) AS jcount
        FROM `jobs` AS j
        LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN `agency` AS a ON  p.`agency_id` = a.`agency_id`
        LEFT JOIN `job_type` AS jt ON j.`job_type` = jt.`job_type`
        WHERE j.`del_job` = 0
        AND p.`deleted` = 0
        AND a.`status` = 'active'			
        AND j.`status` = 'To Be Booked'
        AND j.`job_type` = 'IC Upgrade'
        AND p.`preferred_alarm_id` = 10
        ";
        $upgrade_brooks_sql = $this->db->query($upgrade_brooks_str);
        $data['brooks_upgrade_count'] = $upgrade_brooks_sql->row()->jcount;

        // cavius upgrade count        
        $upgrade_brooks_str = "
        SELECT COUNT(j.`id`) AS jcount
        FROM `jobs` AS j
        LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN `agency` AS a ON  p.`agency_id` = a.`agency_id`
        LEFT JOIN `job_type` AS jt ON j.`job_type` = jt.`job_type`
        WHERE j.`del_job` = 0
        AND p.`deleted` = 0
        AND a.`status` = 'active'			
        AND j.`status` = 'To Be Booked'
        AND j.`job_type` = 'IC Upgrade'
        AND p.`preferred_alarm_id` = 14
        ";
        $upgrade_brooks_sql = $this->db->query($upgrade_brooks_str);
        $data['cavius_upgrade_count'] = $upgrade_brooks_sql->row()->jcount;

        // emerald planet upgrade count        
        $upgrade_brooks_str = "
        SELECT COUNT(j.`id`) AS jcount
        FROM `jobs` AS j
        LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN `agency` AS a ON  p.`agency_id` = a.`agency_id`
        LEFT JOIN `job_type` AS jt ON j.`job_type` = jt.`job_type`
        WHERE j.`del_job` = 0
        AND p.`deleted` = 0
        AND a.`status` = 'active'			
        AND j.`status` = 'To Be Booked'
        AND j.`job_type` = 'IC Upgrade'
        AND p.`preferred_alarm_id` = 22
        ";
        $upgrade_brooks_sql = $this->db->query($upgrade_brooks_str);
        $data['emerald_upgrade_count'] = $upgrade_brooks_sql->row()->jcount;

        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('jobs/status', $data);
        $this->load->view('templates/inner_footer', $data);

     }
     
     public function future_pendings_v2(){

        $data['start_load_time'] = microtime(true);
        $month_text = ( $_REQUEST['date_from_filter'] != "" )?date("F",strtotime("{$_REQUEST['date_from_filter']}")):date("F",strtotime("+1 month"));
        $data['title'] = "{$month_text} Service Due";

        $btnGetStats = $this->input->get_post('btnGetStats');
        $get_sats = $this->input->get_post('get_sats');
        $country_id = $this->config->item('country');

        $agency_filter = $this->input->get_post('agency_filter');
        $state_filter = $this->input->get_post('state_filter');
        $search = $this->input->get_post('search');

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }    

        $data['from'] = ( $this->input->get_post('date_from_filter')!= "" )?$this->input->get_post('date_from_filter'):date("Y-m-01",strtotime("+1 month")); //passed data
        $from = ( $this->input->get_post('date_from_filter')!= "" )?$this->input->get_post('date_from_filter'):date("Y-m-01",strtotime("+1 month"));
        $to = ( $this->input->get_post('date_to_filter') != "" )?$this->input->get_post('date_to_filter'):date("Y-m-t",strtotime("+1 month"));


        //NEXT AND PREV DATE
        $data['prev_day'] = array(
            'from' => date("Y-m-01",strtotime("{$from} -1 month")),
            'to' => date("Y-m-t",strtotime("{$from} -1 month")),
            'title' => '<em class="fa fa-arrow-circle-left"></em> Previous Month '
        );
        
        $data['next_day'] = array(
            'from' => date("Y-m-01",strtotime("{$from} +1 month")),
            'to' => date("Y-m-t",strtotime("{$from} +1 month")),
            'title' => 'Next Month <em class="fa fa-arrow-circle-right"></em> ',
            'css' => 'float: right;'
        );


       //$from_sql_ready = 


        // GET ALL LIST
       

             // pagination
            $per_page = $this->config->item('pagi_per_page');
            $offset = ($this->input->get_post('offset')!="")?$this->input->get_post('offset'):0;


           /* $jparams = array(
                'region_postcodes' => $postcodes,
                'agency' => $agency_filter,
                'phrase' => $search,
                'state' => $state_filter,

                'distinct' => '',
                'from' => $from,
                'to' => $to,

                'offset' => $offset,
                'limit' => $per_page
            );

            if( $btnGetStats || $get_sats==1 ){
                $data['lists'] = $this->jobs_model->getFuturePendings_v2($jparams);
                echo $this->db->last_query();exit;
            }*/

            $sql_from = $this->input->get_post('date_from_filter');
            $sql_to = $this->input->get_post('date_to_filter');

            $this_year = date("Y",strtotime($sql_from));
            $this_month = date("m",strtotime($sql_from));

            // if december
            if( intval($this_month)==12 ){

                $this_month_max_day = date("t",strtotime("{$this_year}-01"));
                $custom_date_filter = "j.`date` BETWEEN '{$this_year}-01-01' AND '{$this_year}-01-{$this_month_max_day}'";
                
            }else{
                
                if( $sql_from!="" && $sql_to!="" ){
                    $next_month = date("m",strtotime("{$sql_from} +1 month"));
                    $last_year = date("Y",strtotime("{$sql_from} -1 year"));
                    $last_day_of_month = date("t",strtotime("{$sql_from} -1 year"));    
                }else{
                    $next_month = date("m", strtotime("+1 month"));
                    $last_year = date("Y",strtotime("-1 year"));
                    $last_day_of_month = date("t",strtotime("-1 year"));    
                }
                $custom_date_filter = "j.date BETWEEN '{$last_year}-{$next_month}-01' AND '{$last_year}-{$next_month}-{$last_day_of_month}'";

            }

            
            $sel_query = "
                CONCAT_WS('', LOWER(p.`address_1`), LOWER(p.`address_2`), LOWER(p.`address_3`), LOWER(p.`state`), LOWER(p.`postcode`) ),
                j.`property_id`,
                j.`date` AS jdate,
                j.`status` AS j_status,
                j.`service` AS j_service,
                j.`job_price` AS j_price,
                j.`job_type` AS j_type,
                j.assigned_tech,
                
                a.`agency_id`,
                a.`agency_name`,
                
                p.`address_1` AS p_address1, 
                p.`address_2` AS p_address2, 
                p.`address_3` AS p_address3, 
                p.`state` AS p_state, 
                p.`postcode` AS p_postcode,
                p.created as prop_created_date,

                ajt.`id` AS ajt_id,
                ajt.`type` AS ajt_type,

                ps.`price` AS ps_price
            ";
            $jparams = array(
                'sel_query' => $sel_query,
                'del_job' => 0,
                'p_deleted' => 0,
                'a_status' => 'active',
                'country_id' => $country_id,
                'join_table' => array('job_type','alarm_job_type'),
                'custom_joins' => array(
                    'join_table' => 'property_services as ps',
                    'join_on' => '(ps.property_id = j.property_id AND j.service = ps.alarm_job_type_id)',
                    'join_type' => 'INNER'
                ),
                'job_status' => 'Completed',
                'job_type' => 'Yearly Maintenance',
                'custom_where' => 'ps.service = 1',

                'postcodes' => $postcodes,
                'agency_filter' => $agency_filter,
                'state_filter' => $state_filter,
                'search' => $search,
                'custom_where_arr' => array(
                    $custom_date_filter
                ),

                'offset' => $offset,
                'limit' => $per_page
            );

            if( $btnGetStats || $get_sats==1 ){
                $data['lists'] = $this->jobs_model->get_jobs($jparams);
                
            }
            


            // total row
            $jparams = array(
                'sel_query' => 'COUNT(j.id) as j_count',
                'del_job' => 0,
                'p_deleted' => 0,
                'a_status' => 'active',
                'country_id' => $country_id,
                'join_table' => array('job_type','alarm_job_type'),
                'custom_joins' => array(
                    'join_table' => 'property_services as ps',
                    'join_on' => '(ps.property_id = j.property_id AND j.service = ps.alarm_job_type_id)',
                    'join_type' => 'INNER'
                ),
                'job_status' => 'Completed',
                'job_type' => 'Yearly Maintenance',
                'custom_where' => 'ps.service = 1',

                'postcodes' => $postcodes,
                'agency_filter' => $agency_filter,
                'state_filter' => $state_filter,
                'search' => $search,
                'custom_where_arr' => array(
                    $custom_date_filter
                )
            ); 
            if( $btnGetStats || $get_sats==1 ){
                $query = $this->jobs_model->get_jobs($jparams);
                $total_rows = $query->row()->j_count;  
            }



         //Agency name filter
         $sel_query = "DISTINCT(a.`agency_id`),
         a.`agency_name`";
         $params = array(
             'sel_query' => $sel_query,
             'del_job' => 0,
             'p_deleted' => 0,
             'a_status' => 'active',
             'country_id' => $country_id,
             'custom_joins' => array(
                 'join_table' => 'property_services as ps',
                 'join_on' => '(ps.property_id = j.property_id AND j.service = ps.alarm_job_type_id)',
                 'join_type' => 'INNER'
             ),
             'job_status' => 'Completed',
             'job_type' => 'Yearly Maintenance',
             'custom_where' => 'ps.service = 1',

             'postcodes' => $postcodes,
             'agency_filter' => $agency_filter,
             'state_filter' => $state_filter,
             'search' => $search,
             'custom_where_arr' => array(
                 $custom_date_filter
             )
         );
         $data['agency_filter_json'] = json_encode($params);

         // Region Filter ( get distinct state )
        $sel_query = "DISTINCT(p.`state`)";
        $region_filter_arr = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,
            'custom_joins' => array(
                'join_table' => 'property_services as ps',
                'join_on' => '(ps.property_id = j.property_id AND j.service = ps.alarm_job_type_id)',
                'join_type' => 'INNER'
            ),
            'job_status' => 'Completed',
            'job_type' => 'Yearly Maintenance',
            'custom_where' => 'ps.service = 1',

            'postcodes' => $postcodes,
            'agency_filter' => $agency_filter,
            'state_filter' => $state_filter,
            'search' => $search,
            'custom_where_arr' => array(
                $custom_date_filter
            )
        );       
        $data['region_filter_json'] = json_encode($region_filter_arr);
        

        // state filter
        $sel_query = "DISTINCT(p.`state`)";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
             'p_deleted' => 0,
             'a_status' => 'active',
             'country_id' => $country_id,
             'custom_joins' => array(
                 'join_table' => 'property_services as ps',
                 'join_on' => '(ps.property_id = j.property_id AND j.service = ps.alarm_job_type_id)',
                 'join_type' => 'INNER'
             ),
             'job_status' => 'Completed',
             'job_type' => 'Yearly Maintenance',
             'custom_where' => 'ps.service = 1',

             'postcodes' => $postcodes,
             'agency_filter' => $agency_filter,
             'state_filter' => $state_filter,
             'search' => $search,
             'custom_where_arr' => array(
                 $custom_date_filter
             )
        );
        $data['state_filter_json'] = json_encode($params);  


        $pagi_links_params_arr = array(
            'agency_filter' => $agency_filter,
            'search' => $search,
            'state_filter' => $state_filter,
            'sub_region_ms' => $sub_region_ms,
            'date_from_filter' => $this->input->get_post('date_from_filter'),
            'date_to_filter' => $this->input->get_post('date_to_filter'),
            'get_sats' => 1
        );
        $pagi_link_params = '/jobs/future_pendings_v2/?'.http_build_query($pagi_links_params_arr);

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


        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('jobs/future_pendings_v2', $data);
        $this->load->view('templates/inner_footer', $data);

     }

     public function export_future_pendings_v2(){

        $agency_filter = $this->input->get_post('agency_filter');
        $state_filter = $this->input->get_post('state_filter');
        $search = $this->input->get_post('search');

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        $country_id = $this->config->item('country');

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }    

        $from = ( $this->input->get_post('date_from_filter')!= "" )?$this->input->get_post('date_from_filter'):date("Y-m-01",strtotime("+1 month"));
        $to = ( $this->input->get_post('date_to_filter') != "" )?$this->input->get_post('date_to_filter'):date("Y-m-t",strtotime("+1 month"));


       // file name
        $filename = "future_pendings_".date("M/Y",strtotime("+1 month")).".csv";
    
        header("Content-Type: text/csv");
        header("Content-Disposition: Attachment; filename={$filename}");
        header("Pragma: no-cache");

        // headers
        $str = "Property ID,Date Added,Address,Suburb,State,Postcode,Agency ID,Agency,Service,Amount,Last Billed,Next Service Due,Last Visit\n";


        $sql_from = $this->input->get_post('date_from_filter');
        $sql_to = $this->input->get_post('date_to_filter');

        $this_year = date("Y",strtotime($sql_from));
        $this_month = date("m",strtotime($sql_from));

        // if december
        if( intval($this_month)==12 ){

            $this_month_max_day = date("t",strtotime("{$this_year}-01"));
            $custom_date_filter = "j.`date` BETWEEN '{$this_year}-01-01' AND '{$this_year}-01-{$this_month_max_day}'";
            
        }else{
            
            if( $sql_from!="" && $sql_to!="" ){
                $next_month = date("m",strtotime("{$sql_from} +1 month"));
                $last_year = date("Y",strtotime("{$sql_from} -1 year"));
                $last_day_of_month = date("t",strtotime("{$sql_from} -1 year"));    
            }else{
                $next_month = date("m", strtotime("+1 month"));
                $last_year = date("Y",strtotime("-1 year"));
                $last_day_of_month = date("t",strtotime("-1 year"));    
            }
            $custom_date_filter = "j.date BETWEEN '{$last_year}-{$next_month}-01' AND '{$last_year}-{$next_month}-{$last_day_of_month}'";

        }
       
        $sel_query = "
            CONCAT_WS('', LOWER(p.`address_1`), LOWER(p.`address_2`), LOWER(p.`address_3`), LOWER(p.`state`), LOWER(p.`postcode`) ),
            j.`property_id`,
            j.`date` AS jdate,
            j.`status` AS j_status,
            j.`service` AS j_service,
            j.`job_price` AS j_price,
            j.`job_type` AS j_type,
            j.assigned_tech,
            
            a.`agency_id`,
            a.`agency_name`,
            
            p.`address_1` AS p_address1, 
            p.`address_2` AS p_address2, 
            p.`address_3` AS p_address3, 
            p.`state` AS p_state, 
            p.`postcode` AS p_postcode,
            p.created as prop_created_date,

            ajt.`id` AS ajt_id,
            ajt.`type` AS ajt_type,

            ps.`price` AS ps_price
        ";
        $jparams = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type'),
            'custom_joins' => array(
                'join_table' => 'property_services as ps',
                'join_on' => '(ps.property_id = j.property_id AND j.service = ps.alarm_job_type_id)',
                'join_type' => 'INNER'
            ),
            'job_status' => 'Completed',
            'job_type' => 'Yearly Maintenance',
            'custom_where' => 'ps.service = 1',

            'postcodes' => $postcodes,
            'agency_filter' => $agency_filter,
            'state_filter' => $state_filter,
            'search' => $search,
            'custom_where_arr' => array(
                $custom_date_filter
            )
        );
        $u_sql = $this->jobs_model->get_jobs($jparams);

        foreach($u_sql->result_array() as $u){
            //last visit query
            //$ls = $this->gherxlib->get_last_service_row($u['property_id'])->row_array();

            $prop_created = ($this->system_model->isDateNotEmpty($u['prop_created_date'])) ? $this->system_model->formatDate($u['prop_created_date'],'d/m/Y'):'';
            $agency_id = $u['agency_id'];
            $service = $u['ajt_type'];
            $price = "$".$u['ps_price'];
            $last_billed = $this->system_model->formatDate($u['jdate'],'d/m/Y');
            $nex_service_due = date("F Y",strtotime($u['jdate'].' +1 year'));
            //$last_visit = ( $u['assigned_tech']==1 || $u['assigned_tech']===NULL )?'': $this->system_model->formatDate($ls['date'],'d/m/Y');

            $last_visit = $this->jobs_model->get_last_visit_per_property($u['property_id']);

            $str .= "{$u['property_id']},\"{$prop_created}\",\"{$u['p_address1']} {$u['p_address2']}\",\"{$u['p_address3']}\",\"{$u['p_state']}\",\"{$u['p_postcode']}\",\"{$agency_id}\",\"{$u['agency_name']}\",\"{$service}\",\"{$price}\",\"{$last_billed}\",\"{$nex_service_due}\",\"{$last_visit}\"\n";
        }
        //content end


        echo $str;
        exit;

    }

    public function merged_jobs_send_api_invoices() {
        $this->load->model('Pme_model');
        $this->load->model('Palace_model');
        $this->load->model('console_model');
        $this->load->model('/inc/email_functions_model');
        $allRes = array();

        $result = $this->Pme_model->send_all_certificates_and_invoices();
        array_push($allRes, $result);

        $result = $this->Palace_model->send_all_certificates_and_invoices();
        array_push($allRes, $result);

        $result = $this->console_model->send_all_certificates_and_invoices();
        array_push($allRes, $result);

        $isUploadFail = array();
        foreach ($allRes as $val) {
            array_push($isUploadFail, $val['err']);
        }

        $returnArr = array(
            "err" => in_array(true, $isUploadFail)
        );

        echo json_encode($returnArr);
    }

    public function test_palace_upload(){

        $this->load->model('Palace_model');
        $this->load->model('/inc/job_functions_model');
        $this->load->model('/inc/pdf_template');
        $this->load->model('/inc/alarm_functions_model');
        $this->load->model('/inc/functions_model');
        
        ini_set('max_execution_time', 900); 
        
        $job_id = $this->input->get_post('job_id'); 

        if( $job_id > 0 ){

            $this->Palace_model->test_palace_upload($job_id);

        }else{

            echo "please enter job ID to test";

        }

    }

    public function merged_jobs()
    {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Merged Jobs";
        $page_url = '/jobs/merged_jobs';

        $job_status="Merged Certificates";
        $country_id = $this->config->item('country');

        $agency_filter = $this->input->get_post('agency_filter');
        $job_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $state_filter = $this->input->get_post('state_filter');
        $date_filter = ( $this->input->get_post('date_filter') !='' )?$this->system_model->formatDate($this->input->get_post('date_filter')):NULL;
        $search = $this->input->get_post('search_filter');
        $sort = ($this->input->get('sort') != '') ? $this->input->get('sort') : 'ASC';
        $order_by = ($this->input->get('order_by') != '') ? $this->input->get('order_by') : 'j.date';

        $agency_filter_pme = $this->input->get_post('agency_filter_pme');
        $job_filter_pme = $this->input->get_post('job_type_filter_pme');
        $service_filter_pme = $this->input->get_post('service_filter_pme');
        $state_filter_pme = $this->input->get_post('state_filter_pme');
        $date_filter_pme = ( $this->input->get_post('date_filter_pme') !='' )?$this->system_model->formatDate($this->input->get_post('date_filter_pme')):NULL;
        $search_pme = $this->input->get_post('search_filter_pme');
        $sort_pme = ($this->input->get('sort_pme') != '') ? $this->input->get('sort_pme') : 'ASC';
        $order_by_pme = ($this->input->get('order_by_pme') != '') ? $this->input->get('order_by_pme') : 'j.date';

        if (is_null($this->input->get_post('isPmeTab')) && is_null($this->input->get_post('isMergeTab')) && is_null($this->input->get_post('isDhaTab')) ) {
            $data['isPmeTab'] = "false";
            $data['isMergeTab'] = "true";
            $data['isDhaTab'] = "false";
        }else {
            $data['isPmeTab'] = $this->input->get_post('isPmeTab');
            $data['isMergeTab'] = $this->input->get_post('isMergeTab');
            $data['isDhaTab'] = $this->input->get_post('isDhaTab');
        }

        $console_api = 5; // console
        $pme_api = 1; // PMe
        $palace_api = 4; // Palace

        $this->load->model('Pme_model'); 

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');
        $offset2 = $this->input->get_post('offset2');

        // normal tab query
        $sel_query_pme = "
        j.`id` AS jid
        ";

        // indented so its easier to read
        $custom_where = "(
                (
                    (
                        apd_pme.`api_prop_id` IS NOT NULL AND 
                        apd_pme.`api_prop_id` != '' AND 
                        apd_pme.`api` = {$pme_api}  
                    ) AND 
                    (a.pme_supplier_id IS NOT NULL AND a.pme_supplier_id != '') AND 
                    (aat.connection_date IS NOT NULL AND aat.connection_date != '') 
                ) OR 
                (
                    (
                        apd_palace.`api_prop_id` IS NOT NULL AND 
                        apd_palace.`api_prop_id` != '' AND 
                        apd_palace.`api` = {$palace_api}
                    ) AND 
                    (a.palace_supplier_id IS NOT NULL AND a.palace_supplier_id != '') AND 
                    (a.palace_agent_id IS NOT NULL AND a.palace_agent_id != '') AND 
                    (a.palace_diary_id IS NOT NULL AND a.palace_diary_id != '')
                ) OR ( 
                    cp.`crm_prop_id` IS NOT NULL AND 
                    cp.`crm_prop_id` != '' 
                )
            )
            AND p.`send_to_email_not_api` = 0
            AND ( j.`prop_comp_with_state_leg` IS NULL OR j.`prop_comp_with_state_leg` = 1 )
            ";

        $paramsPme = array(
            'sel_query' => $sel_query_pme,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type', 'agency_api_logs'),

            'custom_joins_arr' => array(

                array(
                    'join_table' => '`console_properties` AS cp',
                    'join_on' => 'p.`property_id` = cp.`crm_prop_id`',
                    'join_type' => 'left'
                ),
                array(
                    'join_table' => '`api_job_data` AS ajd',
                   'join_on' => "( j.`id` = ajd.`crm_job_id` AND ajd.`api` = {$console_api} )",
                   'join_type' => 'left'
                ),
                array(
                    'join_table' => '`api_property_data` AS apd_pme',
                    'join_on' => "( p.`property_id` = apd_pme.`crm_prop_id` AND apd_pme.`api` = {$pme_api} )",
                    'join_type' => 'left'
                ),
                array(
                    'join_table' => '`api_property_data` AS apd_palace',
                    'join_on' => "( p.`property_id` = apd_palace.`crm_prop_id` AND apd_palace.`api` = {$palace_api} )",
                    'join_type' => 'left'
                )

            ),
            
            'agency_filter' => $agency_filter_pme,
            'job_type' => $job_filter_pme,
            'service_filter' => $service_filter_pme,
            'state_filter' => $state_filter_pme,
            'date'=>$date_filter_pme,
            'search' => $search_pme,
            'custom_where' => $custom_where,
            'group_by' => "j.id",

            'limit' => $per_page,
            'offset' => $offset2,

            'sort_list' => array(
                array(
                    'order_by' => $order_by_pme,
                    'sort' => $sort_pme,
                ),
            ),
        );

        $pmeQuery = $this->Pme_model->get_jobs_with_pme_connect($paramsPme);
        $pmeQueryArr = $pmeQuery->result_array();
        $excludePmeArr = array();
        foreach ($pmeQueryArr as $value) {
            array_push($excludePmeArr, $value['jid']);
        }
        if (empty($excludePmeArr)) {
            $excludePmeArr = array(-1);
        }

        $sel_query = "
        j.`id` AS jid,
        j.`status` AS j_status,
        j.`service` AS j_service,
        j.`created` AS j_created,
        j.`date` AS j_date,
        j.`comments` AS j_comments,
        j.`job_price` AS j_price,
        j.`job_type` AS j_type,
        j.`at_myob`,
        j.`sms_sent_merge`,
        j.`client_emailed`,
        j.`booked_with`,
        j.`assigned_tech`,
        j.`job_entry_notice`,
        j.`door_knock`,
        j.`invoice_amount`,
        j.`invoice_balance`,
        
        p.`property_id` AS prop_id, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3,
        p.`state` AS p_state,
        p.`postcode` AS p_postcode,
        p.`comments` AS p_comments,        
        
        a.`agency_id` AS a_id,
        a.`agency_name` AS agency_name,
        a.`phone` AS a_phone,
        a.`address_1` AS a_address_1, 
        a.`address_2` AS a_address_2, 
        a.`address_3` AS a_address_3,
        a.`state` AS a_state,
        a.`postcode` AS a_postcode,
        a.`trust_account_software`,
        a.`tas_connected`,
        a.`send_emails`,
        a.`account_emails`,
        a.`exclude_free_invoices`,
        
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type
        ";

           
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type'),
            
            'agency_filter' => $agency_filter,
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'date'=>$date_filter,
            'search' => $search,

            'limit' => $per_page,
            'offset' => $offset,

            'sort_list' => array(
                array(
                    'order_by' => $order_by,
                    'sort' => $sort,
                ),
            ),
            'exclude_jobs' => $excludePmeArr,
            'display_query' => 0
        );
        $data['lists'] = $this->jobs_model->get_jobs($params);
        $data['order_by'] = $order_by;
        $data['sort'] = $sort;
           
        $params_all = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type'),
            'exclude_jobs' => $excludePmeArr,
            
        );
        $data['lists_all'] = $this->jobs_model->get_jobs($params_all);
        //echo $this->db->last_query();

        //get total for normal merge_jobs
        $mer_total_price_ex_gst = 0;
        foreach($this->jobs_model->get_jobs($params_all)->result_array() as $m_total_row){
            
            $new_price_var_param = array(
                'service_type' => $m_total_row['j_service'],
                'job_id' => $m_total_row['jid'],
                'property_id' => $m_total_row['prop_id']
            );
            $new_price = $this->system_model->get_job_variations_v2($new_price_var_param);
            $merge_price_ex_gst = number_format($this->system_model->price_ex_gst($new_price['total_price_including_variations']),2);
            $mer_total_price_ex_gst += $merge_price_ex_gst;
        }
        $data['mer_total_price_ex_gst'] = $mer_total_price_ex_gst;
        //get total for normal merge_jobs end


        // API tab query
        $sel_query_pme = "
        j.`id` AS jid,
        j.`status` AS j_status,
        j.`service` AS j_service,
        j.`created` AS j_created,
        j.`date` AS j_date,
        j.`comments` AS j_comments,
        j.`job_price` AS j_price,
        j.`job_type` AS j_type,
        j.`at_myob`,
        j.`sms_sent_merge`,
        j.`client_emailed`,
        j.`is_pme_invoice_upload`,
        j.`is_pme_bill_create`,
        j.`assigned_tech`,
        j.`booked_with`,
        j.`door_knock`,
        j.`is_palace_invoice_upload`,
        j.`is_palace_bill_create`,
        j.`invoice_amount`,
        j.`invoice_balance`,
        
        p.`property_id` AS prop_id, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3,
        p.`state` AS p_state,
        p.`postcode` AS p_postcode,
        p.`comments` AS p_comments, 
        
        a.`agency_id` AS a_id,
        a.`agency_name` AS agency_name,
        a.`phone` AS a_phone,
        a.`address_1` AS a_address_1, 
        a.`address_2` AS a_address_2, 
        a.`address_3` AS a_address_3,
        a.`state` AS a_state,
        a.`postcode` AS a_postcode,
        a.`trust_account_software`,
        a.`tas_connected`,
        a.`send_emails`,
        a.`account_emails`,
        a.`pme_supplier_id`,
        a.`exclude_free_invoices`,

        aat.`connection_date`,
        
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type,
        ajt.`type` AS ajt_type,

        cp.`crm_prop_id`,
        ajd.`api_inv_uploaded`,
        ajd.`api_cert_uploaded`,

        apd_pme.`api` AS pme_api,
        apd_pme.`api_prop_id` AS pme_prop_id,

        apd_palace.`api` AS palace_api,
        apd_palace.`api_prop_id` AS palace_prop_id
        ";

        $custom_where = "(
                (
                    (
                        apd_pme.`api_prop_id` IS NOT NULL AND 
                        apd_pme.`api_prop_id` != '' AND 
                        apd_pme.`api` = {$pme_api} 
                    ) AND 
                    (a.pme_supplier_id IS NOT NULL AND a.pme_supplier_id != '') AND 
                    (aat.connection_date IS NOT NULL AND aat.connection_date != '')
                ) OR
                (
                    (
                        apd_palace.`api_prop_id` IS NOT NULL AND 
                        apd_palace.`api_prop_id` != '' AND 
                        apd_palace.`api` = {$palace_api}
                    ) AND 
                    (a.palace_supplier_id IS NOT NULL AND a.palace_supplier_id != '') AND 
                    (a.palace_agent_id IS NOT NULL AND a.palace_agent_id != '') AND 
                    (a.palace_diary_id IS NOT NULL AND a.palace_diary_id != '')
                ) OR ( 
                    cp.`crm_prop_id` IS NOT NULL AND 
                    cp.`crm_prop_id` != '' 
                )
            )
            AND p.`send_to_email_not_api` = 0
            AND ( j.`prop_comp_with_state_leg` IS NULL OR j.`prop_comp_with_state_leg` = 1 )
            ";

        $paramsPme = array(
            'sel_query' => $sel_query_pme,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type', 'agency_api_logs'),

            'custom_joins_arr' => array(

                array(
                    'join_table' => '`console_properties` AS cp',
                    'join_on' => 'p.`property_id` = cp.`crm_prop_id`',
                    'join_type' => 'left'
                ),
                array(
                    'join_table' => '`api_job_data` AS ajd',
                   'join_on' => "( j.`id` = ajd.`crm_job_id` AND ajd.`api` = {$console_api} )",
                   'join_type' => 'left'
                ),
                array(
                    'join_table' => '`api_property_data` AS apd_pme',
                    'join_on' => "( p.`property_id` = apd_pme.`crm_prop_id` AND apd_pme.`api` = {$pme_api} )",
                    'join_type' => 'left'
                ),
                array(
                    'join_table' => '`api_property_data` AS apd_palace',
                    'join_on' => "( p.`property_id` = apd_palace.`crm_prop_id` AND apd_palace.`api` = {$palace_api} )",
                    'join_type' => 'left'
                )

            ),
            
            'agency_filter' => $agency_filter_pme,
            'job_type' => $job_filter_pme,
            'service_filter' => $service_filter_pme,
            'state_filter' => $state_filter_pme,
            'date'=>$date_filter_pme,
            'search' => $search_pme,
            'custom_where' => $custom_where,
            'group_by' => "j.id",

            'limit' => $per_page,
            'offset' => $offset2,

            'sort_list' => array(
                array(
                    'order_by' => $order_by_pme,
                    'sort' => $sort_pme,
                ),
            )
        );
        $pmeQuery = $this->Pme_model->get_jobs_with_pme_connect($paramsPme);
        //echo $this->db->last_query();

        $data['listsPme'] = $pmeQuery;
        $data['order_by_pme'] = $order_by_pme;
        $data['sort_pme'] = $sort_pme;

        //all pme connected rows
        // i think this used in API tab count
        $custom_where = "(
                (
                    (
                        apd_pme.`api_prop_id` IS NOT NULL AND 
                        apd_pme.`api_prop_id` != '' AND 
                        apd_pme.`api` = {$pme_api} 
                    ) AND 
                    (a.pme_supplier_id IS NOT NULL AND a.pme_supplier_id != '') AND 
                    (aat.connection_date IS NOT NULL AND aat.connection_date != '')
                )
                OR (
                    (
                        apd_palace.`api_prop_id` IS NOT NULL AND 
                        apd_palace.`api_prop_id` != '' AND 
                        apd_palace.`api` = {$palace_api}
                    ) AND 
                    (a.palace_supplier_id IS NOT NULL AND a.palace_supplier_id != '') AND 
                    (a.palace_agent_id IS NOT NULL AND a.palace_agent_id != '') AND 
                    (a.palace_diary_id IS NOT NULL AND a.palace_diary_id != '')
                ) OR ( 
                    cp.`crm_prop_id` IS NOT NULL AND cp.`crm_prop_id` != '' 
                )  
            )        
            AND p.`send_to_email_not_api` = 0
            AND ( j.`prop_comp_with_state_leg` IS NULL OR j.`prop_comp_with_state_leg` = 1 )
            ";

        $paramsPme = array(
            'sel_query' => $sel_query_pme,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type', 'agency_api_logs'),

            'custom_joins_arr' => array(

                array(
                    'join_table' => '`console_properties` AS cp',
                    'join_on' => 'p.`property_id` = cp.`crm_prop_id`',
                    'join_type' => 'left'
                ),
                array(
                    'join_table' => '`api_job_data` AS ajd',
                   'join_on' => "( j.`id` = ajd.`crm_job_id` AND ajd.`api` = {$console_api} )",
                   'join_type' => 'left'
                ),
                array(
                    'join_table' => '`api_property_data` AS apd_pme',
                    'join_on' => "( p.`property_id` = apd_pme.`crm_prop_id` AND apd_pme.`api` = {$pme_api} )",
                    'join_type' => 'left'
                ),
                array(
                    'join_table' => '`api_property_data` AS apd_palace',
                    'join_on' => "( p.`property_id` = apd_palace.`crm_prop_id` AND apd_palace.`api` = {$palace_api} )",
                    'join_type' => 'left'
                )               

            ),

            'agency_filter' => $agency_filter_pme,
            'job_type' => $job_filter_pme,
            'service_filter' => $service_filter_pme,
            'state_filter' => $state_filter_pme,
            'date'=>$date_filter_pme,
            'search' => $search_pme,
            'custom_where' => $custom_where,
            'group_by' => "j.id",
        );
        $pmeQuery = $this->Pme_model->get_jobs_with_pme_connect($paramsPme);
        $total_rows_pme = $pmeQuery->num_rows();
        $data['listsPmeRow'] = $pmeQuery->num_rows();

        //get api merge total
        $mer_total_price_ex_gst_api = 0;
        foreach($pmeQuery->result_array() as $m_total_row_pme){
            
            $new_price_var_param_pme = array(
                'service_type' => $m_total_row_pme['j_service'],
                'job_id' => $m_total_row_pme['jid'],
                'property_id' => $m_total_row_pme['prop_id']
            );
            $new_price_pme = $this->system_model->get_job_variations_v2($new_price_var_param_pme);
            $merge_price_ex_gst_pme = number_format($this->system_model->price_ex_gst($new_price_pme['total_price_including_variations']),2);
            $mer_total_price_ex_gst_api += $merge_price_ex_gst_pme;
        }
        $data['mer_total_price_ex_gst_api'] = $mer_total_price_ex_gst_api;
        //get api merge total end

        // indented so its easier to read
        $custom_where = "(
                (
                    (j.`is_pme_invoice_upload` = 1 OR j.`is_pme_bill_create` = 1) AND 
                    (
                        apd_pme.`api_prop_id` IS NOT NULL AND 
                        apd_pme.`api_prop_id` != '' AND 
                        apd_pme.`api` = {$pme_api}
                    ) AND 
                    (a.pme_supplier_id IS NOT NULL AND a.pme_supplier_id != '') AND 
                    (aat.connection_date IS NOT NULL AND aat.connection_date != '')
                ) OR (
                    (j.`is_palace_invoice_upload` = 1 OR j.`is_palace_bill_create` = 1) AND 
                    (
                        apd_palace.`api_prop_id` IS NOT NULL AND 
                        apd_palace.`api_prop_id` != '' AND 
                        apd_palace.`api` = {$palace_api}
                    ) AND 
                    (a.palace_supplier_id IS NOT NULL AND a.palace_supplier_id != '') AND 
                    (a.palace_agent_id IS NOT NULL AND a.palace_agent_id != '') AND 
                    (a.palace_diary_id IS NOT NULL AND a.palace_diary_id != '')
                ) OR ( 
                    (
                        ajd.`api_inv_uploaded` = 1 OR 
                        ajd.`api_cert_uploaded` = 1
                    ) AND
                    ajd.`api` = {$console_api} 
                )
            )
            AND p.`send_to_email_not_api` = 0
            AND ( j.`prop_comp_with_state_leg` IS NULL OR j.`prop_comp_with_state_leg` = 1 )
            ";

        $paramsPmeSent = array(
            'sel_query' => $sel_query_pme,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type', 'agency_api_logs'),
            'custom_joins_arr' => array(

                array(
                    'join_table' => '`console_properties` AS cp',
                    'join_on' => 'p.`property_id` = cp.`crm_prop_id`',
                    'join_type' => 'left'
                ),
                array(
                    'join_table' => '`api_job_data` AS ajd',
                   'join_on' => "( j.`id` = ajd.`crm_job_id` AND ajd.`api` = {$console_api} )",
                   'join_type' => 'left'
                ),
                array(
                    'join_table' => '`api_property_data` AS apd_pme',
                    'join_on' => "( p.`property_id` = apd_pme.`crm_prop_id` AND apd_pme.`api` = {$pme_api} )",
                    'join_type' => 'left'
                ),
                array(
                    'join_table' => '`api_property_data` AS apd_palace',
                    'join_on' => "( p.`property_id` = apd_palace.`crm_prop_id` AND apd_palace.`api` = {$palace_api} )",
                    'join_type' => 'left'
                )

            ),

            'agency_filter' => $agency_filter_pme,
            'job_type' => $job_filter_pme,
            'service_filter' => $service_filter_pme,
            'state_filter' => $state_filter_pme,
            'date'=>$date_filter_pme,
            'search' => $search_pme,
            'custom_where' => $custom_where,
            'group_by' => "j.id",
            'display_query' => 0
        );
        $pmeQuerySent = $this->Pme_model->get_jobs_with_pme_connect($paramsPmeSent);
        $data['listsPmeRowSent'] = $pmeQuerySent->num_rows();



        // all rows
        $sel_query = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type'),

            'agency_filter' => $agency_filter,
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'date'=>$date_filter,
            'search' => $search,
            'exclude_jobs' => $excludePmeArr,
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;

        // update page total
        $page_tot_params = array(
            'page' => $page_url,
            'total' => $total_rows
        );
        $this->system_model->update_page_total($page_tot_params);

        $data['total_rows'] = $query->row()->jcount; // pass data to view

        //Agency name filter
        $sel_query = "DISTINCT(a.`agency_id`),
        a.`agency_name`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['agency_filter_json'] = json_encode($params);

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

        //Services Filter
        $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('alarm_job_type'),
            'sort_list' => array(
                array(
                    'order_by' => 'ajt.`type`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['service_filter_json'] = json_encode($params);

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
    
        $pagi_links_params_arr = array(
            'agency_filter' => $agency_filter,
            'job_type_filter' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'date_filter' => $date_filter,
            'search_filter' => $search,
            'sort' => $sort,
            'order_by' => $order_by
        );
        $pagi_link_params = '/jobs/merged_jobs/?'.http_build_query($pagi_links_params_arr);

        // pagination settings
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $pagi_link_params;
        
        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();
        $data['page_search_url'] = $pagi_link_params;

        // pagination count
        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);


        $pagi_links_params_arr_pme = array(
            'agency_filter_pme' => $agency_filter_pme,
            'job_type_filter_pme' => $job_filter_pme,
            'service_filter_pme' => $service_filter_pme,
            'state_filter_pme' => $state_filter_pme,
            'date_filter_pme' => $date_filter_pme,
            'search_filter_pme' => $search_pme,
            'order_by_pme' => $order_by_pme,
            'sort_pme' => $sort_pme
        );
        $pagi_link_params_pme = '/jobs/merged_jobs/?'.http_build_query($pagi_links_params_arr_pme);

        // pagination settings pme
        $configPme['page_query_string'] = TRUE;
        $configPme['query_string_segment'] = 'offset2';
        $configPme['total_rows'] = $total_rows_pme;
        $configPme['per_page'] = $per_page;
        $configPme['base_url'] = $pagi_link_params_pme;

        $this->pagination->initialize($configPme);
        $data['paginationPme'] = $this->pagination->create_links();
        $data['page_search_url_pme'] = $pagi_link_params_pme;
        
        // pagination count
        $pc_params_pme = array(
            'total_rows' => $total_rows_pme,
            'offset2' => $offset2,
            'per_page' => $per_page
        );
        $data['pagi_count_pme'] = $this->jcclass->pagination_count($pc_params_pme);

        //get $email_stats
        $email_stats_query = $this->jobs_model->get_email_stats($date='',$job_status, $excludePmeArr);        

        $data['email_stats'] = $this->functions_model->mysqlMultiRows($email_stats_query);     
        $data['count_email_invoice'] = $this->count_email_invoice()[0]['count'];
        $data['pre_completion'] = $this->count_sms_tenant()[0]['count'];

        if (!empty($excludePmeArr)) {
            $excludeIds = implode(",", $excludePmeArr);
        }else {
            $excludeIds = 0;
        }
        
        $total_age = $this->mj_getTotalAge("", $excludeIds);
        $total_job_count = $this->mj_getAllJobCount("", $excludeIds);
        $data['total_age_avg'] = round($total_age/$total_job_count);
        $data['final_total'] = $this->mj_getMergeJobTotalJobPrice("", $excludeIds)+$this->mj_getMergeJobTotalAlarmPrice("", $excludeIds)+$this->mj_getMergeJobTotalSubCharge("", $excludeIds);

        $total_age = $this->mj_getTotalAge("pme");
        $total_job_count = $this->mj_getAllJobCount("pme");
        $data['total_age_avg_pme'] = round($total_age/$total_job_count);
        $data['final_total_pme'] = $this->mj_getMergeJobTotalJobPrice("pme")+$this->mj_getMergeJobTotalAlarmPrice("pme")+$this->mj_getMergeJobTotalSubCharge("pme");

        $this->load->view('templates/inner_header', $data);
        $this->load->view('jobs/merged_jobs', $data);
        $this->load->view('templates/inner_footer', $data);

    }
    
    public function mj_getAllJobCount($stats = "", $excludePmeArr = ""){

        $pme_api = 1; // PMe
        $palace_api = 4; // Palace

        $addWhere = "";
        $addJoin = "";
        if (!empty($stats)) {
            $addWhere = "AND ((
                apd_pme.`api_prop_id` IS NOT NULL AND 
                apd_pme.`api_prop_id` != '' AND 
                apd_pme.`api` = {$pme_api}
            ) AND (a.pme_supplier_id IS NOT NULL AND a.pme_supplier_id != '') AND (aat.connection_date IS NOT NULL AND aat.connection_date != '')
                OR
                (
                    apd_palace.`api_prop_id` IS NOT NULL AND 
                    apd_palace.`api_prop_id` != '' AND 
                    apd_palace.`api` = {$palace_api}
                ) AND (a.palace_supplier_id IS NOT NULL AND a.palace_supplier_id != '') AND (a.palace_agent_id IS NOT NULL AND a.palace_agent_id != '') AND (a.palace_diary_id IS NOT NULL AND a.palace_diary_id != ''))
                AND p.`send_to_email_not_api` = 0
                AND ( j.`prop_comp_with_state_leg` IS NULL OR j.`prop_comp_with_state_leg` = 1 )
                ";
            $addJoin = "LEFT JOIN `agency_api_tokens` AS aat ON (a.`agency_id` = aat.`agency_id` AND aat.`api_id` = 1)";
        }else {
            $addWhere = "AND j.`id` NOT IN ({$excludePmeArr})";
        }
        $country_id = $this->config->item('country');
        $sql = $this->db->query("
            SELECT COUNT( j.`id` ) AS jcount
            FROM `jobs` AS j 
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
            LEFT JOIN `api_property_data` AS apd_pme ON ( p.`property_id` = apd_pme.`crm_prop_id` AND apd_pme.`api` = {$pme_api} )
            LEFT JOIN `api_property_data` AS apd_palace ON ( p.`property_id` = apd_palace.`crm_prop_id` AND apd_palace.`api` = {$palace_api} )
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
            {$addJoin}
            WHERE p.`deleted` =0 
            AND a.`status` = 'active' 
            AND j.`del_job` = 0 
            AND a.`country_id` = {$country_id} 
            AND j.`status` = 'Merged Certificates'
            {$addWhere}
        ");
        $row = $sql->row_array();
        return $row['jcount'];
    }

    public function mj_getTotalAge($stats = "", $excludePmeArr = ""){

        $pme_api = 1; // PMe
        $palace_api = 4; // Palace

        $addWhere = "";
        $addJoin = "";
        if (!empty($stats)) {
            $addWhere = "AND ((
                apd_pme.`api_prop_id` IS NOT NULL AND 
                apd_pme.`api_prop_id` != '' AND 
                apd_pme.`api` = {$pme_api}
            ) AND (a.pme_supplier_id IS NOT NULL AND a.pme_supplier_id != '') AND (aat.connection_date IS NOT NULL AND aat.connection_date != '')
                OR
                (
                    apd_palace.`api_prop_id` IS NOT NULL AND 
                    apd_palace.`api_prop_id` != '' AND 
                    apd_palace.`api` = {$palace_api}
                ) AND (a.palace_supplier_id IS NOT NULL AND a.palace_supplier_id != '') AND (a.palace_agent_id IS NOT NULL AND a.palace_agent_id != '') AND (a.palace_diary_id IS NOT NULL AND a.palace_diary_id != ''))
                AND p.`send_to_email_not_api` = 0
                AND ( j.`prop_comp_with_state_leg` IS NULL OR j.`prop_comp_with_state_leg` = 1 )
                ";
            $addJoin = "LEFT JOIN `agency_api_tokens` AS aat ON (a.`agency_id` = aat.`agency_id` AND aat.`api_id` = 1)";
        }else {
            $addWhere = "AND j.`id` NOT IN ({$excludePmeArr})"; 
        }
        $country_id = $this->config->item('country');
        $sql = $this->db->query("
            SELECT SUM( DATEDIFF( '".date('Y-m-d')."', CAST( j.`created` AS DATE ) ) ) AS sum_age
            FROM `jobs` AS j 
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
            LEFT JOIN `api_property_data` AS apd_pme ON ( p.`property_id` = apd_pme.`crm_prop_id` AND apd_pme.`api` = {$pme_api} )
            LEFT JOIN `api_property_data` AS apd_palace ON ( p.`property_id` = apd_palace.`crm_prop_id` AND apd_palace.`api` = {$palace_api} )
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            {$addJoin} 
            WHERE p.`deleted` =0 
            AND a.`status` = 'active' 
            AND j.`del_job` = 0 
            AND a.`country_id` = {$country_id} 
            AND j.`status` = 'Merged Certificates'
            {$addWhere}
        ");
        $row = $sql->row_array();
        return $row['sum_age'];
    }

    public function mj_getMergeJobTotalSubCharge($stats = "", $excludePmeArr = ""){

        $pme_api = 1; // PMe
        $palace_api = 4; // Palace

        $addWhere = "";
        $addJoin = "";
        if (!empty($stats)) {
            $addWhere = "AND ((
                apd_pme.`api_prop_id` IS NOT NULL AND 
                apd_pme.`api_prop_id` != '' AND 
                apd_pme.`api` = {$pme_api}
            ) AND (a.pme_supplier_id IS NOT NULL AND a.pme_supplier_id != '') AND (aat.connection_date IS NOT NULL AND aat.connection_date != '')
                OR
                (
                    apd_palace.`api_prop_id` IS NOT NULL AND 
                    apd_palace.`api_prop_id` != '' AND 
                    apd_palace.`api` = {$palace_api}
                ) AND (a.palace_supplier_id IS NOT NULL AND a.palace_supplier_id != '') AND (a.palace_agent_id IS NOT NULL AND a.palace_agent_id != '') AND (a.palace_diary_id IS NOT NULL AND a.palace_diary_id != ''))
                AND p.`send_to_email_not_api` = 0
                AND ( j.`prop_comp_with_state_leg` IS NULL OR j.`prop_comp_with_state_leg` = 1 )
                ";
            $addJoin = "LEFT JOIN `agency_api_tokens` AS aat ON (a.`agency_id` = aat.`agency_id` AND aat.`api_id` = 1)";
        }else {
            //$addWhere = "AND j.`id` NOT IN ({$excludePmeArr})";
        }
        $country_id = $this->config->item('country');
        $sql = $this->db->query("
            SELECT SUM(am.`price`) AS am_price
            FROM `jobs` AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `api_property_data` AS apd_pme ON ( p.`property_id` = apd_pme.`crm_prop_id` AND apd_pme.`api` = {$pme_api} )
            LEFT JOIN `api_property_data` AS apd_palace ON ( p.`property_id` = apd_palace.`crm_prop_id` AND apd_palace.`api` = {$palace_api} )
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `agency_maintenance` AS am on a.`agency_id` = am.`agency_id`
            {$addJoin} 
            WHERE j.`status` = 'Merged Certificates' 
            AND p.`deleted` =0 
            AND a.`status` = 'active' 
            AND j.`del_job` = 0 
            AND a.`country_id` = {$country_id} 
            AND am.`surcharge` = 1
            {$addWhere}
        ");
        $row = $sql->row_array();
        return $row['am_price'];
    }

    public function mj_getMergeJobTotalAlarmPrice($stats = "", $excludePmeArr = ""){

        $pme_api = 1; // PMe
        $palace_api = 4; // Palace

        $addWhere = "";
        $addJoin = "";
        if (!empty($stats)) {
            $addWhere = "AND ((
                apd_pme.`api_prop_id` IS NOT NULL AND 
                apd_pme.`api_prop_id` != '' AND 
                apd_pme.`api` = {$pme_api}
            ) AND (a.pme_supplier_id IS NOT NULL AND a.pme_supplier_id != '') AND (aat.connection_date IS NOT NULL AND aat.connection_date != '')
                OR
                (
                    apd_palace.`api_prop_id` IS NOT NULL AND 
                    apd_palace.`api_prop_id` != '' AND 
                    apd_palace.`api` = {$palace_api}
                ) AND (a.palace_supplier_id IS NOT NULL AND a.palace_supplier_id != '') AND (a.palace_agent_id IS NOT NULL AND a.palace_agent_id != '') AND (a.palace_diary_id IS NOT NULL AND a.palace_diary_id != ''))
                AND p.`send_to_email_not_api` = 0
                AND ( j.`prop_comp_with_state_leg` IS NULL OR j.`prop_comp_with_state_leg` = 1 )
                ";
            $addJoin = "LEFT JOIN `agency_api_tokens` AS aat ON (a.`agency_id` = aat.`agency_id` AND aat.`api_id` = 1)";
        }else {
            //$addWhere = "AND j.`id` NOT IN ({$excludePmeArr})";
        }
        $country_id = $this->config->item('country');
        $sql = $this->db->query("
            SELECT SUM(alrm.`alarm_price`) AS aprice
            FROM `alarm` AS alrm 
            LEFT JOIN `jobs` AS j ON  alrm.`job_id` = j.`id` 
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
            LEFT JOIN `api_property_data` AS apd_pme ON ( p.`property_id` = apd_pme.`crm_prop_id` AND apd_pme.`api` = {$pme_api} )
            LEFT JOIN `api_property_data` AS apd_palace ON ( p.`property_id` = apd_palace.`crm_prop_id` AND apd_palace.`api` = {$palace_api} )
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
            LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id` 
            LEFT JOIN `job_reason` AS jr ON j.`job_reason_id` = jr.`job_reason_id` 
            LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
            {$addJoin}
            WHERE j.`status` = 'Merged Certificates' 
            AND p.`deleted` =0 
            AND a.`status` = 'active' 
            AND j.`del_job` = 0 
            AND a.`country_id` = {$country_id} 
            AND alrm.`new`  = 1
            AND alrm.`ts_discarded` = 0
            {$addWhere}
        ");
        $row = $sql->row_array();
        return $row['aprice'];
    }

    public function mj_getMergeJobTotalJobPrice($stats = "", $excludePmeArr = "") {

        $pme_api = 1; // PMe
        $palace_api = 4; // Palace

        $addWhere = "";
        $addJoin = "";
        if (!empty($stats)) {
            $addWhere = "AND ((
                apd_pme.`api_prop_id` IS NOT NULL AND 
                apd_pme.`api_prop_id` != '' AND 
                apd_pme.`api` = {$pme_api}
            ) AND (a.pme_supplier_id IS NOT NULL AND a.pme_supplier_id != '') AND (aat.connection_date IS NOT NULL AND aat.connection_date != '')
                OR
                (
                    apd_palace.`api_prop_id` IS NOT NULL AND 
                    apd_palace.`api_prop_id` != '' AND 
                    apd_palace.`api` = {$palace_api}
                ) AND (a.palace_supplier_id IS NOT NULL AND a.palace_supplier_id != '') AND (a.palace_agent_id IS NOT NULL AND a.palace_agent_id != '') AND (a.palace_diary_id IS NOT NULL AND a.palace_diary_id != ''))
                AND p.`send_to_email_not_api` = 0
                AND ( j.`prop_comp_with_state_leg` IS NULL OR j.`prop_comp_with_state_leg` = 1 )
                ";
            $addJoin = "LEFT JOIN `agency_api_tokens` AS aat ON (a.`agency_id` = aat.`agency_id` AND aat.`api_id` = 1)";
        }else {
            //$addWhere = "AND j.`id` NOT IN ({$excludePmeArr})"; 
        }
        $country_id = $this->config->item('country');
        $sql = $this->db->query("
            SELECT SUM(j.`job_price`) AS jprice
            FROM `jobs` AS j 
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
            LEFT JOIN `api_property_data` AS apd_pme ON ( p.`property_id` = apd_pme.`crm_prop_id` AND apd_pme.`api` = {$pme_api} )
            LEFT JOIN `api_property_data` AS apd_palace ON ( p.`property_id` = apd_palace.`crm_prop_id` AND apd_palace.`api` = {$palace_api} )
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
            {$addJoin}
            WHERE j.`status` = 'Merged Certificates'
            AND p.`deleted` =0 
            AND a.`status` = 'active' 
            AND j.`del_job` = 0 
            AND a.`country_id` = {$country_id}
            {$addWhere}
        ");
        $row = $sql->row_array();
        return $row['jprice'];
    }

    public function count_email_invoice() {
        $country_id = $this->config->item('country');
        $sql_str = "SELECT count(j.client_emailed) AS count
        FROM jobs AS j
        LEFT JOIN property AS p ON j.property_id = p.property_id
        LEFT JOIN agency AS a ON p.agency_id = a.agency_id
        LEFT JOIN staff_accounts AS sa ON j.assigned_tech = sa.StaffID
        LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`   
        WHERE j.status = 'Merged Certificates'
        AND a.`country_id` = {$country_id}
        AND p.`deleted` = 0
        AND a.`status` = 'active'
        AND j.`del_job` = 0
        AND j.client_emailed IS NOT NULL";
        return $this->db->query($sql_str)->result_array();
    }

    public function count_sms_tenant() {
        $country_id = $this->config->item('country');
        $sql_str = "SELECT count(j.sms_sent_merge) AS count
        FROM jobs AS j
        LEFT JOIN property AS p ON j.property_id = p.property_id
        LEFT JOIN agency AS a ON p.agency_id = a.agency_id
        LEFT JOIN staff_accounts AS sa ON j.assigned_tech = sa.StaffID
        LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`   
        WHERE j.status = 'Merged Certificates'
        AND a.`country_id` = {$country_id}
        AND p.`deleted` = 0
        AND a.`status` = 'active'
        AND j.`del_job` = 0
        AND (!(j.`assigned_tech` <=> 1) AND !(j.`assigned_tech` <=> 2))
        AND DATE_FORMAT(j.sms_sent_merge, '%Y-%m-%d') = DATE_FORMAT(NOW(), '%Y-%m-%d')";
        return $this->db->query($sql_str)->result_array();
    }


    public function merged_jobs_sms_send() {
        $country_id = $this->config->item('country');
        $data['status'] = false;
        $num_sms_sent = $this->jobs_model->merged_jobs_sms_send_model($country_id);

        $data['countRes'] = $num_sms_sent;
        if($num_sms_sent>0){
            $data['status'] = true;
        }
        echo json_encode($data);
    }

    public function export_myob() {

        $country_id = $this->config->item('country');
        $get_past_myob = $this->input->get_post('get_past_myob');
        $date = $this->input->get_post('date');

        if( $get_past_myob == 1 ){ // get past MYOB export

            // get past MYOB export data based on the past date, assuming jobs is already completed
            $sql_str = "
            SELECT DISTINCT(j.`id`)
            FROM `logs` AS l
            LEFT JOIN `jobs` AS j ON l.`job_id` = j.`id`
            WHERE l.`title` = 27
            AND l.`details` = 'Job status updated from <strong>Merged Certificates</strong> to <strong>Completed</strong>'
            AND j.`status` = 'Completed'
            AND j.`date` = '{$date}'
            ";
            $query = $this->db->query($sql_str);

        }else{ // default

            //$query = $this->db->query("SELECT j.id FROM jobs j, property p, agency a WHERE (p.agency_id = a.agency_id AND j.property_id = p.property_id AND j.status = 'Merged Certificates') AND p.deleted = 0 AND a.`country_id` = {$country_id}");
            $query = $this->db->query("SELECT j.id FROM jobs j, property p, agency a WHERE (p.agency_id = a.agency_id AND j.property_id = p.property_id AND j.status = 'Merged Certificates') AND p.deleted = 0 AND ( p.is_nlm = 0 OR p.is_nlm IS NULL ) AND a.`country_id` = {$country_id}"); ##new with nlm stuff filter

        }        

        $jobs = $query->result_array();

        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=myob_import_" . date('d-m-Y') . ".csv");
        header("Pragma: no-cache");
        header("Expires: 0");


        function currencyFormatNoComma($job_price){
            return number_format($job_price,2,'.','');
        }

        if($country_id==1){
            $invoice_heading = 'Invoice #';
            $inc_tax_price = 'Inc-Tax Price';
            $inc_tax_total = 'Inc-Tax Total';
            $tax_code = 'Tax Code';
        }else{
            $invoice_heading = 'Invoice No.';
            $inc_tax_price = 'Inc-GST Price';
            $inc_tax_total = 'Inc-GST Total';
            $tax_code = 'GST Code';
        }

        echo "Co./Last Name,First Name,Addr 1 - Line 1,           - Line 2,           - Line 3,           - Line 4,Inclusive,{$invoice_heading},Date,Delivery Status,Item Number,Quantity,Description,Price,{$inc_tax_price},Discount,Total,{$inc_tax_total},Job,Journal Memo,Salesperson Last Name,Salesperson First Name,{$tax_code},Non-GST Amount,GST Amount,LCT Amount,Inc-Tax Freight Amount,Freight Tax Code,Freight Non-GST Amount,Freight GST Amount,Freight LCT Amount,Sale Status,Terms - Payment is Due,           - Discount Days,           - Balance Due Days,           - % Discount,           - % Monthly Charge,Amount Paid, Customer PO\n";

        foreach($jobs as $job)
        {

            $invoice_total = 0;
            $job_id = $job['id'];
            
            // append checkdigit to job id for new invoice number
            $check_digit = $this->gherxlib->getCheckDigit(trim($job_id));
            $bpay_ref_code = "{$job_id}{$check_digit}"; 
            
            if(!is_numeric($job_id)) exit();
            
            # Job Details
            $query = $this->db->query("SELECT j.job_type, j.property_id, DATE_FORMAT(j.date, '%d/%m/%Y')AS date, j.job_price, j.price_used, t.description, j.work_order,j.id AS j_id, j.service AS j_service FROM jobs j LEFT JOIN job_type t ON t.job_type = j.job_type WHERE j.id = {$job_id}");
            $job_details = $query->row_array();
            
            # Alarm Details
            $query = $this->db->query("SELECT a.*, p.alarm_pwr, t.alarm_type, r.alarm_reason FROM alarm a LEFT JOIN alarm_pwr p ON a.alarm_power_id = p.alarm_pwr_id LEFT JOIN alarm_type t ON t.alarm_type_id = a.alarm_type_id LEFT JOIN alarm_reason r ON r.alarm_reason_id = a.alarm_reason_id WHERE a.job_id = {$job_id}");
            $alarm_details = $query->result_array();
            $num_alarms = sizeof($alarm_details);
            
            # Property + Agent Details
            $query = $this->db->query("SELECT p.address_1, p.address_2, p.address_3, p.state, p.postcode, p.landlord_lastname, p.landlord_firstname, a.agency_name, a.address_1 AS a_address_1, a.address_2 AS a_address_2, a.address_3 AS a_address_3, a.state AS a_state, a.postcode  AS a_postcode, p.price, s.FirstName, s.LastName, a.`agency_id` FROM property p  LEFT JOIN agency a ON p.agency_id = a.agency_id LEFT JOIN staff_accounts s ON s.StaffID = a.salesrep WHERE p.property_id = {$job_details['property_id']}");
            $property_details = $query->row_array();

            # vehicle details
            $query_vehicle = $this->db->query("
                SELECT j.id, v.number_plate
                FROM jobs AS j 
                LEFT JOIN staff_accounts AS sa ON j.assigned_tech = sa.StaffID
                LEFT JOIN vehicles AS v ON sa.StaffID = v.StaffID
                WHERE j.id = {$job_id}
                AND v.`active` = 1
            ");
            $vehicle_row = $query_vehicle->row_array();
            
            # Sync price if not already
            /*
            if(!$job_details['price_used'])
            {
                $job_details['job_price'] = $property_details['price'];
                syncJobPrice($job_id, $property_details['price']);
                
            }
            */

            //new price variation function 
            $new_price_var_param = array(
                'service_type' => $job_details['j_service'],
                'job_id' => $job_details['j_id'],
                'property_id' => $job_details['property_id']
            );
            $new_price = $this->system_model->get_job_variations_v2($new_price_var_param);
             //new price variation function end

            ## Get dummy vehicle
            if(ENVIRONMENT=="production"){ //live
                $dummy_plant_id = 85;
            }else{
                $dummy_plant_id = 35;
            }
           
            $v_query = $this->db->select('*')->from('vehicles')->where('plant_id', $dummy_plant_id)->get();
            $v_row = $v_query->row_array();
            
            
            #Company Last Name, First Name
            if($property_details['agency_name']!=""){
                
                $aid_search = array(3043,3036,3046,1902,3044,1906,1927,3045);
            
                if (in_array($property_details['agency_id'], $aid_search)){
                    echo 'Defence Housing Australia - Master,,';
                }else{
                    echo $property_details['agency_name'].",,";
                }       
                
                #Addr - Line 1, - Line 2, -Line 3, - Line 4, IOnclusive
                echo $property_details['address_1']." " . $property_details['address_2'] . ",";
                echo $property_details['address_3']." " . $property_details['state'] . " " . $property_details['postcode'] . ",,,X,";
                
                #Invoice Number, Date, Customer PO, Ship Via, Delivery Status
                echo "\"=\"\"".$bpay_ref_code ."\"\"\"," . $job_details['date'] . ",A,";
                
                #Item Number, Quantity, Description, Price, Inc-Tax Price, Discount, Total, Inc-Tax Total
                switch($country_id){
                    case 1:
                        //$gst = $job_details['job_price'] / 11;
                        //$job_price = $job_details['job_price'] / 1.1;

                        $gst = $new_price['total_price_including_variations'] / 11;
                        $job_price = $new_price['total_price_including_variations'] / 1.1;
                        
                    break;
                    case 2:
                        //$gst = ($job_details['job_price']*3)/23;    
                        //$job_price = ($job_details['job_price']-$gst);

                        $gst = ($new_price['total_price_including_variations']*3)/23;    
                        $job_price = ($new_price['total_price_including_variations']-$gst);
                    break;
                }

                //new price with variations
                /*$new_price_var_param = array(
                    'service_type' => $job_details['j_service'],
                    'job_id' => $job_details['j_id'],
                    'property_id' => $job_details['property_id']
                );
                $new_price = $this->system_model->get_job_variations_v2($new_price_var_param);*/
                $new_price_exclusive_tax = $this->system_model->price_ex_gst($new_price['total_price_including_variations']);
                $new_price_inclusive_tax = $new_price['total_price_including_variations'];
                //new price with variations end

                echo $job_details['job_type'] . ",1," . $job_details['description'] . ",$" . currencyFormatNoComma($new_price_exclusive_tax) . ",$" . currencyFormatNoComma($new_price_inclusive_tax) . ",0%,$" . currencyFormatNoComma($new_price_exclusive_tax) . ",$" . currencyFormatNoComma($new_price_inclusive_tax) . ",";
                
                #Job, Comment
                //echo ",,";
                echo ($vehicle_row['number_plate']!="")?$vehicle_row['number_plate'].",":$v_row['number_plate'].",";
                
                #Journal Memo
                echo $property_details['address_1']." " . $property_details['address_2'] . ",";
                
                #Salesperson Last Name  Salesperson First Name  Shipping Date   Referral Source
                //echo $property_details['LastName'].",". $property_details['FirstName'].",";
                echo ",,";
                
                if($country_id==1){
                    $gst_text = 'GST';
                }else{
                    $gst_text = 'S15';
                }
                
                #Tax Code, Non GST Amount, GST Amount, LCT Amount
                echo "{$gst_text},$0.00,$" .currencyFormatNoComma($gst). ",$0.00,";
                
                #Freight Amount,Inc-Tax Freight Amount,Freight Tax Code,Freight Non-GST Amount, Freight GST Amount, Freight LCT Amount
                echo ",{$gst_text},$0.00,$0.00,$0.00,";
                
                #Sale Status, Currency Code, Exchange Rate, Terms - Payment is Due, - Discount Days, - Balance Due Days, - % Discount, - % Monthly Charge, Amount Paid
               // echo "I,5,1,7,0,0,$0.00,\n";
                echo "I,5,1,7,0,0,$0.00,";
                
                #Payment Method, Payment Notes, Name on Card, Card Number, Expiry Date, Authorisation Code, BSB Account Number, Drawer/Account Name, Cheque Number, Category, Location ID, Card ID, Record ID
                //echo ",,,,,,,,,,,,,,\n";

                echo $property_details['address_1']." " . $property_details['address_2'] . ",\n";
                
                $invoice_total += $job_price;
                
            }
            
            
            for($x = 0; $x < $num_alarms; $x++)
            {
                if($alarm_details[$x]['new'] == 1)
                {
                    if($property_details['agency_name']!=""){
                    
                        #Company Last Name, First Name
                        $aid_search = array(3043,3036,3046,1902,3044,1906,1927,3045);
                        
                        if (in_array($property_details['agency_id'], $aid_search)){
                            echo 'Defence Housing Australia - Master,,';
                        }else{
                            echo $property_details['agency_name'].",,";
                        }   
                        
                        #Addr - Line 1, - Line 2, -Line 3, - Line 4, IOnclusive
                        echo $property_details['address_1']." " . $property_details['address_2'] . ",";
                        echo $property_details['address_3']." " . $property_details['state'] . " " . $property_details['postcode'] . ",,,X,";
                        
                        #Invoice Number, Date, Customer PO, Ship Via, Delivery Status
                        echo "\"=\"\"".$bpay_ref_code ."\"\"\"," . $job_details['date'] . ",A,";
                        
                        #Item Number, Quantity, Description, Price, Inc-Tax Price, Discount, Total, Inc-Tax Total
                        switch($country_id){
                        case 1:
                            $gst = $alarm_details[$x]['alarm_price'] / 11;
                            $alarm_price = $alarm_details[$x]['alarm_price'] / 1.1;
                        break;
                        case 2:
                            $gst = ($alarm_details[$x]['alarm_price']*3)/23;
                            $alarm_price = ($alarm_details[$x]['alarm_price']-$gst);
                        break;
                    }
                        echo $alarm_details[$x]['alarm_pwr'] . ",1,Supply & Install " . $alarm_details[$x]['alarm_type'] . " Smoke Alarm,$" . currencyFormatNoComma($alarm_price) . ",$" . currencyFormatNoComma($alarm_details[$x]['alarm_price']) . ",0%,$" . currencyFormatNoComma($alarm_price) . ",$" . currencyFormatNoComma($alarm_details[$x]['alarm_price']) . ",";
                        
                        #Job, Comment
                        //echo ",,";
                        echo ($vehicle_row['number_plate']!="")?$vehicle_row['number_plate'].",":$v_row['number_plate'].",";
                        
                        #Journal Memo
                        echo $property_details['address_1']." " . $property_details['address_2'] . ",";
                        
                        #Salesperson Last Name  Salesperson First Name  Shipping Date   Referral Source
                        echo ",,";
                        
                        if($country_id==1){
                            $gst_text = 'GST';
                        }else{
                            $gst_text = 'S15';
                        }
                        #Tax Code, Non GST Amount, GST Amount, LCT Amount
                        echo "{$gst_text},$0.00,$" . currencyFormatNoComma($gst) . ",$0.00,";
                        
                        #Freight Amount,Inc-Tax Freight Amount,Freight Tax Code,Freight Non-GST Amount, Freight GST Amount, Freight LCT Amount
                        echo ",{$gst_text},$0.00,$0.00,$0.00,";
                        
                        #Sale Status, Currency Code, Exchange Rate, Terms - Payment is Due, - Discount Days, - Balance Due Days, - % Discount, - % Monthly Charge, Amount Paid
                       // echo "I,5,1,7,0,0,$0.00,\n";
                        echo "I,5,1,7,0,0,$0.00,";
                        
                        #Payment Method, Payment Notes, Name on Card, Card Number, Expiry Date, Authorisation Code, BSB Account Number, Drawer/Account Name, Cheque Number, Category, Location ID, Card ID, Record ID
                        //echo ",,,,,,,,,,,,,,\n";

                        echo $property_details['address_1']." " . $property_details['address_2'] . ",\n";
                        
                        $invoice_total += $alarm_price;
                    
                    }
                    
                    
                    if($property_details['agency_name']!=""){
                    
                        # SECOND ROW - Reason code
                    
                        if($alarm_details[$x]['alarm_reason'] == "Insufficient") $reasonstring = "New Install - Insufficient";
                        else $reasonstring = "Replaced - " . $alarm_details[$x]['alarm_reason'];    
                        
                        #Company Last Name, First Name
                        $aid_search = array(3043,3036,3046,1902,3044,1906,1927,3045);
                        
                        if (in_array($property_details['agency_id'], $aid_search)){
                            echo 'Defence Housing Australia - Master,,';
                        }else{
                            echo $property_details['agency_name'].",,";
                        }
                        
                        #Addr - Line 1, - Line 2, -Line 3, - Line 4, IOnclusive
                        echo $property_details['address_1']." " . $property_details['address_2'] . ",";
                        echo $property_details['address_3']." " . $property_details['state'] . " " . $property_details['postcode'] . ",,,X,";
                        
                        #Invoice Number, Date, Customer PO, Ship Via, Delivery Status
                        echo "\"=\"\"".$bpay_ref_code ."\"\"\"," . $job_details['date'] . ",A,";
                        
                        #Item Number, Quantity, Description, Price, Inc-Tax Price, Discount, Total, Inc-Tax Total
                        echo $alarm_details[$x]['alarm_reason'] . ",1," . $reasonstring . ",$0.00,$0.00,0%,$0.00,$0.00,";
                        
                        #Job, Comment
                        //echo ",,";
                        echo ($vehicle_row['number_plate']!="")?$vehicle_row['number_plate'].",":$v_row['number_plate'].",";
                        
                        #Journal Memo
                        echo $property_details['address_1']." " . $property_details['address_2'] . ",";
                        
                        #Salesperson Last Name  Salesperson First Name  Shipping Date   Referral Source
                        echo ",,";
                        
                        if($country_id==1){
                            $gst_text = 'GST';
                        }else{
                            $gst_text = 'S15';
                        }
                        #Tax Code, Non GST Amount, GST Amount, LCT Amount
                        echo "{$gst_text},$0.00,$0.00,$0.00,";
                        
                        #Freight Amount,Inc-Tax Freight Amount,Freight Tax Code,Freight Non-GST Amount, Freight GST Amount, Freight LCT Amount
                        echo ",{$gst_text},$0.00,$0.00,$0.00,";
                        
                        #Sale Status, Currency Code, Exchange Rate, Terms - Payment is Due, - Discount Days, - Balance Due Days, - % Discount, - % Monthly Charge, Amount Paid
                       // echo "I,5,1,7,0,0,$0.00,\n";
                        echo "I,5,1,7,0,0,$0.00,";
                        
                        #Payment Method, Payment Notes, Name on Card, Card Number, Expiry Date, Authorisation Code, BSB Account Number, Drawer/Account Name, Cheque Number, Category, Location ID, Card ID, Record ID
                        //echo ",,,,,,,,,,,,,,\n";

                        echo $property_details['address_1']." " . $property_details['address_2'] . ",\n";
                    
                    }
                    
                    
                    
                }
            }
            
            
            // Surcharge
            $sc_sql = $this->db->query("SELECT *, m.`name` AS m_name FROM `agency_maintenance` AS am LEFT JOIN `maintenance` AS m ON am.`maintenance_id` = m.`maintenance_id` WHERE am.`agency_id` = {$property_details['agency_id']} AND am.`maintenance_id` > 0");
            $sc = $sc_sql->row_array();
            if( $invoice_total!=0 && $sc['surcharge']==1 ){
                    
                #Company Last Name, First Name
                $aid_search = array(3043,3036,3046,1902,3044,1906,1927,3045);
                
                if (in_array($property_details['agency_id'], $aid_search)){
                    echo 'Defence Housing Australia - Master,,';
                }else{
                    echo $property_details['agency_name'].",,";
                }   
                
                #Addr - Line 1, - Line 2, -Line 3, - Line 4, IOnclusive
                echo $property_details['address_1']." " . $property_details['address_2'] . ",";
                echo $property_details['address_3']." " . $property_details['state'] . " " . $property_details['postcode'] . ",,,X,";
                
                #Invoice Number, Date, Customer PO, Ship Via, Delivery Status
                echo "\"=\"\"".$bpay_ref_code ."\"\"\"," . $job_details['date'] . ",A,";
                
                #Item Number, Quantity, Description, Price, Inc-Tax Price, Discount, Total, Inc-Tax Total
                switch($country_id){
                    case 1:
                        $gst = $sc['price'] / 11;
                        $sc_price = $sc['price'] / 1.1;
                    break;
                    case 2:
                        $gst = ($sc['price']*3)/23;
                        $sc_price = ($sc['price']-$gst);
                    break;
                }
                $surcharge_txt = ($sc['display_surcharge']==1)?$sc['surcharge_msg']:'';
                //echo $alarm_details[$x]['alarm_pwr'] . ",1,Supply & Install " . $alarm_details[$x]['alarm_type'] . " Smoke Alarm,$" . $alarm_price . ",$" . currencyFormatNoComma($alarm_details[$x]['alarm_price']) . ",0%,$" . $alarm_price . ",$" . currencyFormatNoComma($alarm_details[$x]['alarm_price']) . ",";
                echo $sc['m_name'] . ",1,\"{$surcharge_txt}\",$" . currencyFormatNoComma($sc_price) . ",$" . currencyFormatNoComma($sc['price']) . ",0%,$" . currencyFormatNoComma($sc_price) . ",$" . currencyFormatNoComma($sc['price']) . ",";
                
                #Job, Comment
                //echo ",,";
                echo ($vehicle_row['number_plate']!="")?$vehicle_row['number_plate'].",":"".",";
                
                #Journal Memo
                echo $property_details['address_1']." " . $property_details['address_2'] . ",";
                
                #Salesperson Last Name  Salesperson First Name  Shipping Date   Referral Source
                echo ",,";
                
                if($country_id==1){
                    $gst_text = 'GST';
                }else{
                    $gst_text = 'S15';
                }
                #Tax Code, Non GST Amount, GST Amount, LCT Amount
                echo "{$gst_text},$0.00,$" . currencyFormatNoComma($gst) . ",$0.00,";
                
                #Freight Amount,Inc-Tax Freight Amount,Freight Tax Code,Freight Non-GST Amount, Freight GST Amount, Freight LCT Amount
                echo ",{$gst_text},$0.00,$0.00,$0.00,";
                
                #Sale Status, Currency Code, Exchange Rate, Terms - Payment is Due, - Discount Days, - Balance Due Days, - % Discount, - % Monthly Charge, Amount Paid
               // echo "I,5,1,7,0,0,$0.00,\n";
                echo "I,5,1,7,0,0,$0.00,";
                
                #Payment Method, Payment Notes, Name on Card, Card Number, Expiry Date, Authorisation Code, BSB Account Number, Drawer/Account Name, Cheque Number, Category, Location ID, Card ID, Record ID
                //echo ",,,,,,,,,,,,,,\n";

                echo $property_details['address_1']." " . $property_details['address_2'] . ",\n";
            
            }
            
               
            echo "\n";
        }

    }

    public function mark_completed() {

        $staff_id =  $this->session->staff_id;
        $country_id = $this->config->item('country');

        $Query = $this->db->query("SELECT j.`date`, j.`property_id`, j.`id`, j.`job_type` FROM jobs AS j LEFT JOIN property AS p ON j.`property_id` = p.`property_id` LEFT JOIN agency AS a ON p.`agency_id` = a.`agency_id` WHERE j.`status` = 'Merged Certificates' AND a.`country_id` = {$country_id}");

        $result = $Query->result_array();
         
        foreach($Query->result_array() as $row){

            $jobdate = $row['date'];
            $property_id = $row['property_id'];
            $job_id = $row['id'];
            $job_type = $row['job_type'];

            if ($job_type != "Once-off") {
                $Query = $this->db->query("UPDATE property p, jobs j SET p.test_date='$jobdate', p.retest_date=(DATE_ADD('$jobdate', INTERVAL 1 YEAR)) WHERE (p.property_id = $property_id)");
            }else {
                $Query = $this->db->query("UPDATE property p, jobs j SET p.test_date='$jobdate', p.retest_date=NULL WHERE (p.property_id = $property_id)");
            }

            $Query = $this->db->query("UPDATE jobs SET status='Completed' WHERE status='Merged Certificates' AND `id`={$job_id}");
            
            //insert log
            $log_details = "Job status updated from <strong>Merged Certificates</strong> to <strong>Completed</strong>";
            $log_params = array(
                'title' => 27, // Merged Certificates
                'details' => $log_details,
                'display_in_vjd' => 1,
                'created_by_staff' => $staff_id,
                'job_id' => $job_id
            );
            $this->system_model->insert_log($log_params);

        }

        echo json_encode(array("err" => false));

    }


    public function on_hold_reasons() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "On Hold Reasons";
        $uri = '/jobs/on_hold_reasons';
        $data['uri'] = $uri;
        
        $date_filter = ( $this->input->get_post('date_filter') !='' )?$this->system_model->formatDate($this->input->get_post('date_filter')):null;
        $country_id = $this->config->item('country');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( $this->input->get_post('offset') != '' )?$this->input->get_post('offset'):0;        

        // paginated list
        $sel_query = "
            j.`id` AS jid,
            j.`status` AS j_status,            
            j.`date` AS j_date,            
            j.`job_type`,
            
            p.`property_id`, 
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,       
            
            a.`agency_id`,
            a.`agency_name` AS agency_name,
            
            jt.`abbrv`,
            
            ajt.`id` AS ajt_id,
            ajt.`type` AS ajt_type
        ";

        $custom_where = "j.`status` IN('On Hold','On Hold - COVID')";
        
        $params = array(
            'sel_query' => $sel_query,
            'custom_where'=> $custom_where,

            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
                    
            'date' => $date_filter,        
            'country_id' => $country_id,
            
            'join_table' => array('job_type','alarm_job_type'),

            'limit' => $per_page,
            'offset' => $offset,

            'sort_list' => array(   
                array(
                    'order_by' => 'j.`date`',
                    'sort' => 'DESC'
                )
            ),          
            'display_query' => 0
        );

        $data['job_sql'] = $this->jobs_model->get_jobs($params);


        // get total row
        $sel_query = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query,
            'custom_where'=> $custom_where,

            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
                    
            'date' => $date_filter,        
            'country_id' => $country_id,
            
            'join_table' => array('job_type','alarm_job_type'),      
            'display_query' => 0
        );
        $job_sql = $this->jobs_model->get_jobs($params);
        $total_rows = $job_sql->row()->jcount;
        $data['total_job_count'] = $job_sql->row()->jcount;

        // On Hold - Covid count
        $sel_query = "COUNT(j.`id`) AS jcount";        
        $params = array(
            'sel_query' => $sel_query,            

            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'job_status' => 'On Hold - COVID',  
                    
            'date' => $date_filter,        
            'country_id' => $country_id,
            
            'join_table' => array('job_type','alarm_job_type','agency_priority'),      
            'display_query' => 0
        );
        $job_sql = $this->jobs_model->get_jobs($params);
        $data['on_hold_covid_count'] = $job_sql->row()->jcount;


        // On Hold
        $sel_query = "COUNT(j.`id`) AS jcount";        
        $params = array(
            'sel_query' => $sel_query,            

            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'job_status' => 'On Hold',  
                    
            'date' => $date_filter,        
            'country_id' => $country_id,
            
            'join_table' => array('job_type','alarm_job_type','agency_priority'),      
            'display_query' => 0
        );
        $job_sql = $this->jobs_model->get_jobs($params);
        $data['on_hold_count'] = $job_sql->row()->jcount;


        $pagi_links_params_arr = array(
            'date_filter' => $date_filter
        );
        $pagi_link_params = $uri.'/?'.http_build_query($pagi_links_params_arr);


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
        

        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);

    }



    public function preferred_time()
    {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Preferred Time";
        $uri = $data['uri'] ='/jobs/preferred_time';
        
        $date_filter = ( $this->input->get_post('date_filter') !='' )?$this->system_model->formatDate($this->input->get_post('date_filter')):null;
        $country_id = $this->config->item('country');
        $key_access_details = $this->input->get_post('key_access_details');
        $key_access_required = $this->input->get_post('key_access_required');

        $state_filter = $this->input->get_post('state_filter');
        $agency_filter = $this->input->get_post('agency_filter');

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);

        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);

        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);

        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }  
        $export = $this->input->get_post('export');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( $this->input->get_post('offset') != '' )?$this->input->get_post('offset'):0;  
        
        //sort
        $sort = ( $this->input->get_post('sort') !='' )?$this->input->get_post('sort'):'asc';
        $filter_orderby_columns = $this->input->get_post('order_by');
        $preferred_time_filter = ltrim($this->input->get_post('preferred_time_filter'));

        if($filter_orderby_columns == 'preferred_time') {
            $sort_list = array(
                'order_by' => 'j.`preferred_time`',
                'sort' => $sort,
            );
        } else {
            $sort_list = array(
                'order_by' => 'j.created',
                'sort' => 'ASC',
            );
        }

        // paginated list
        $sel_query = "
            *, 
            j.`id` AS jid,
            j.`status` AS j_status,            
            j.`date` AS j_date,            
            j.`job_type`,
            j.`preferred_time`,
            j.`created` AS jcreated,
        
            p.`property_id`, 
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,       
            
            a.`agency_id`,
            a.`agency_name` AS agency_name,
            aght.priority,
            apmd.abbreviation,
            
            jt.`abbrv`,
            
            ajt.`id` AS ajt_id,
            ajt.`type` AS ajt_type
        ";

        $custom_where = "j.`status` = 'To Be Booked' AND j.`preferred_time` != '' AND j.`preferred_time` IS NOT NULL AND p.holiday_rental= 0 AND p.bne_to_call = 0";
        $custom_where_preferred_time_filter = ($preferred_time_filter != "") ? "j.`preferred_time` LIKE '%$preferred_time_filter%'" : "";

        $params = array(
            'sel_query' => $sel_query,
            'custom_where'=> $custom_where,
            'custom_where_arr' => array(
                $custom_where_preferred_time_filter
            ),
            'del_job' => 0,
            'agency_filter' => $agency_filter,
            'p_deleted' => 0,
            'a_status' => 'active',  
            'country_id' => $country_id,
            'state_filter' => $state_filter,
            'postcodes' => $postcodes,
            'key_access_details' => $key_access_details,
            'key_access_required' => $key_access_required,
            'out_of_tech_hours' => 0,
            'join_table' => array('job_type','alarm_job_type','agency_priority', 'agency_priority_marker_definition'),
            'sort_list' => array(   
                $sort_list
            ),          
            'display_query' => 0
        );

        if( $export == 1 ){

			$job_sql = $this->jobs_model->get_jobs($params);

			// file name
			$filename = 'preferred_time_export'.date('YmdHis').rand().'.csv';

			header("Content-type: application/csv");
			header("Content-Disposition: attachment; filename={$filename}");
			header("Pragma: no-cache");
			header("Expires: 0");

			// file creation
			$file = fopen('php://output', 'w');

			// csv header
			$csv_header = []; // clear
			$csv_header = array( 'Age', 'Job Type', 'Job Status', 'Preferred Time', 'Property Address', 'State', 'Agency');
			fputcsv($file, $csv_header);

			// csv row
			foreach ( $job_sql->result() as $job_row ) {

                $date1=date_create(date('Y-m-d',strtotime($job_row->jcreated)));
                $date2=date_create(date('Y-m-d'));
                $diff=date_diff($date1,$date2);
                $age = $diff->format("%a");

				$csv_row = [];
				$csv_row = array(
                    $age,
                    "$job_row->job_type",
                    "$job_row->j_status",
                    "$job_row->preferred_time",
					"{$job_row->p_address_1} {$job_row->p_address_2}, {$job_row->p_address_3}",
                    "$job_row->p_state",
                    "$job_row->agency_name"
				);

				fputcsv($file, $csv_row);

			}

			fclose($file);

		}else{ // page view
            $params['limit'] = $per_page;
			$params['offset'] = $offset;

            $data['job_sql'] = $this->jobs_model->get_jobs($params);
            $data['sql_query'] = $this->db->last_query(); //Show query on About

            //Agency name filter
            $sel_query = "DISTINCT(a.`agency_id`),
            a.`agency_name`";
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'out_of_tech_hours' => 0,
                'join_table' => array('job_type','alarm_job_type'),
                'custom_where' => $custom_where,

                'sort_list' => array(
                    array(
                        'order_by' => 'a.`agency_name`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['agency_filter_json'] = json_encode($params);

            //State Filter
            $sel_query = "DISTINCT(p.`state`)";
            $params = array(
                'sel_query' => $sel_query,
                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'country_id' => $country_id,
                'join_table' => array('job_type','alarm_job_type'),
                'custom_where' => $custom_where,
                'sort_list' => array(
                    array(
                        'order_by' => 'p.`state`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['state_filter_json'] = json_encode($params);

            // Region Filter ( get distinct state )
            $sel_query = "p.`state`";
            $region_filter_arr = array(
                'sel_query' => $sel_query,
                'custom_where_arr' => array(
                    $custom_where
                ),
                'del_job' => 0,
                'p_deleted' => 0,
                'a_status' => 'active',
                'country_id' => $country_id,
                'join_table' => array('job_type','alarm_job_type'),
                'sort_list' => array(
                    array(
                        'order_by' => 'p.`state`',
                        'sort' => 'ASC',
                    )
                ),
                'group_by' => 'p.`state`',
                'display_query' => 0
            );
            $data['region_filter_json'] = json_encode($region_filter_arr);


            // get total row
            $sel_query = "COUNT(j.`id`) AS jcount";
            $params = array(
                'sel_query' => $sel_query,
                'custom_where'=> $custom_where,
                'del_job' => 0,
                'p_deleted' => 0,
                'a_status' => 'active',  
                'country_id' => $country_id,
                'state_filter' => $state_filter,
                'postcodes' => $postcodes,
                'agency_filter' => $agency_filter,
                'key_access_details' => $key_access_details,
                'key_access_required' => $key_access_required,
                'out_of_tech_hours' => 0,
                
                'join_table' => array('job_type','alarm_job_type'),      
                'display_query' => 0
            );
            $job_sql = $this->jobs_model->get_jobs($params);
            $total_rows = $job_sql->row()->jcount;
            $data['total_job_count'] = $job_sql->row()->jcount;        

            $pagi_links_params_arr = array(
                'state_filter' => $state_filter,
                'agency_filter' => $agency_filter,
                'key_access_details' => $key_access_details,
                'key_access_required' => $key_access_required,
                'sub_region_ms' => $sub_region_ms
            );
            $pagi_link_params = $uri.'/?'.http_build_query($pagi_links_params_arr);

            $filters_arr = array(
                'preferred_time_filter' => $preferred_time_filter
            );

            // header sort paramerts needs to exclude sort variables
            $data['header_link_params'] = $filters_arr;


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

            $data['order_by'] = $order_by;
            $data['sort'] = $sort; 

            $data['toggle_sort'] = ( $sort == 'asc' ) ? 'desc' : 'asc';
            
            //load views
            $this->load->view('templates/inner_header', $data);
            $this->load->view($uri, $data);
            $this->load->view('templates/inner_footer', $data);
        }
    }

    public function completed_ic_upgrade()
    {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Completed IC Upgrade";
        $uri = '/jobs/completed_ic_upgrade';
        $data['uri'] = $uri;

        $job_status = 'Completed';
        $country_id = $this->config->item('country');

        $job_filter = $this->input->get_post('job_type_filter');
        $sales = $this->input->get_post('sales');
        $service_filter = $this->input->get_post('service_filter');
        $state_filter = $this->input->get_post('state_filter');
        $agency_filter = $this->input->get_post('agency_filter');
        $search = $this->input->get_post('search_filter');
        $search_submit = $this->input->get_post('search_submit');
        $export = $this->input->get_post('export');
        $alarm_brand_filter = $this->input->get_post('alarm_brand_filter');

        $dateFrom_field = $this->input->get_post('dateFrom_filter');
        $dateTo_field = $this->input->get_post('dateTo_filter');
        $dateFrom_filter = ( $dateFrom_field !='' )?$this->system_model->formatDate($dateFrom_field):NULL;
        $dateTo_filter = ( $dateTo_field !='' )?$this->system_model->formatDate($dateTo_field):NULL;
        
        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);
        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }  

        $order_by = ( $this->input->get_post('order_by') !='' )?$this->input->get_post('order_by'):'j.date';
        $sort = ( $this->input->get_post('sort') !='' )?$this->input->get_post('sort'):'asc';
       
        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $sel_query = "
        SUM(al.`alarm_price`) AS cost_of_alarms,
        SUM(al_p.`alarm_price_ex`) AS cost_of_alarms_ex_gst,
        j.`id` AS jid,
        j.`status` AS j_status,
        j.`service` AS j_service,
        j.`created` AS j_created,
        j.`date` AS j_date,
        j.`comments` AS j_comments,
        j.`job_price` AS j_price,
        j.`job_type` AS j_type,
        j.`assigned_tech`,
        j.`invoice_amount`,
        
        p.`property_id` AS prop_id, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3,
        p.`state` AS p_state,
        p.`is_sales` AS sales,
        p.`postcode` AS p_postcode,
        p.`comments` AS p_comments, 
        p.`created` AS p_created,
        
        a.`agency_id` AS a_id,
        a.`agency_name` AS agency_name,
        a.`phone` AS a_phone,
        a.`address_1` AS a_address_1, 
        a.`address_2` AS a_address_2, 
        a.`address_3` AS a_address_3,
        a.`state` AS a_state,
        a.`postcode` AS a_postcode,
        a.`trust_account_software`,
        a.`tas_connected`,
        aght.priority,
        apmd.abbreviation,
        
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type,    
        
        al_p.`alarm_pwr`,
        al.`make`
        ";

        // only get NEW alarm and not discarded
        $custom_where = " al.`new` = 1 AND al.`ts_discarded` = 0 ";

        // date filter
        if($dateFrom_field!="" && $dateTo_field!=""){
            $custom_where .= " AND Date(j.`date`)  BETWEEN '{$dateFrom_filter}' AND '{$dateTo_filter}' ";
        }

        // date filter
        if( $alarm_brand_filter != '' ){
            $custom_where .= " AND al.`make` LIKE '%{$alarm_brand_filter}%' ";
        }

        // sales filter
        if( $sales != 0 ){
            $custom_where .= " AND p.`is_sales` = 1 ";
        }
       
        $params = array(
            'sel_query' => $sel_query,
            //'p_deleted' => 0,
            //'a_status' => 'active',
            //'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type', 'agency_priority', 'agency_priority_marker_definition'),

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
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'agency_filter' => $agency_filter,
            'custom_where'=> $custom_where,
            'search' => $search,
            'postcodes' => $postcodes,
            'a_deleted' => 'no filter',
            
            'sort_list' => array(
                array(
                    'order_by' => $order_by,
                    'sort' => $sort,
                ),
            ),
            'group_by' => 'j.id',
            'display_query' => 0
        );

        // export should show all
        if ( $export != 1 ){ 
            $params['limit'] = $per_page;
            $params['offset'] = $offset;
        }

        if( $search_submit ){
            //print_r($params);

            $job_sql = $this->jobs_model->get_jobs($params)->result_array();
            $data['query_string'] = $this->db->last_query();

            $job_id_arr = [];
            foreach( $job_sql as &$job_row ) {
                $job_id_arr[] = $job_row['jid'];
            }

            $job_id_arr = array_unique($job_id_arr);     

            if( count($job_id_arr) > 0 ){

                // get alarms used
                $this->db->select("a.`alarm_power_id`, a.`job_id`, ap.`alarm_pwr`, a.`make`");
                $this->db->from('alarm AS a');
                $this->db->join('`alarm_pwr` AS ap', 'a.`alarm_power_id` = ap.`alarm_pwr_id`', 'left');
                $this->db->where("a.`ts_discarded`", 0);
                $this->db->where("a.`new`", 1);
                $this->db->where_in("a.`job_id`", $job_id_arr);
                //$this->db->group_by("a.`job_id`");            

                $alarms_sql = $this->db->get()->result_array();   
                                    

                // manually join tables                
                foreach ($job_sql as &$job_row) {
                    $alarm_power_used_arr = [];
                    foreach ($alarms_sql as &$alarms_row) {
                        if ( $job_row['jid'] == $alarms_row['job_id'] ) {                        
                            $alarm_power_used_arr[] = "{$alarms_row['alarm_pwr']}({$alarms_row['make']})";                                                
                            //$job_row['alarm_power_used'] = $alarms_row['alarm_pwr'];                        
                            //break;
                        }
                    }

                    if( count($alarm_power_used_arr) > 0 ){
                        $alarm_power_used_imp = implode(", ",$alarm_power_used_arr);
                        $job_row['alarm_power_used'] =  $alarm_power_used_imp;
                    }                
                }

            }                    

        }  
        
        if ( $export == 1 ) { //EXPORT         

            // file name
            $date_export = date('YmdHis');
            $filename = "completed_ic_upgrade_{$date_export}.csv";
    
            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");
    
            // file creation 
            $csv_file = fopen('php://output', 'w');

            $dynamic_state_header = $this->gherxlib->getDynamicState($this->config->item('country'));
    
            $csv_header = array(
                "Date","Job Type","Service","Price","Cost of Alarms","Alarm","Address",
                "Property Created Date",$dynamic_state_header,"Agency"
            );
            fputcsv($csv_file, $csv_header);
            
            
            foreach( $job_sql as $list_item ){ 
    
                $csv_row = [];  
                
                $prop_address = $list_item['p_address_1']." ".$list_item['p_address_2'].", ".$list_item['p_address_3'];
                                               
                $csv_row[] = ( $this->system_model->isDateNotEmpty($list_item['j_date']) )?date('d/m/Y', strtotime($list_item['j_date'])):'';
                $csv_row[] = $this->gherxlib->getJobTypeAbbrv($list_item['j_type']);
                $csv_row[] = $list_item['ajt_type'];
                //$csv_row[] = ( $list_item['invoice_amount'] > 0 )?number_format($list_item['invoice_amount'],2):null;
                $csv_row[] = ( $list_item['invoice_amount'] > 0 )?'$'.number_format($this->system_model->price_ex_gst($list_item['invoice_amount']),2):null;
                //$csv_row[] = ( $list_item['cost_of_alarms'] > 0 )?number_format($list_item['cost_of_alarms'],2):null;
                //$csv_row[] = ( $list_item['cost_of_alarms'] > 0 )?'$'.number_format($this->system_model->price_ex_gst($list_item['cost_of_alarms']),2):null;
                $csv_row[] = ( $list_item['cost_of_alarms_ex_gst'] > 0 )?'$'.number_format($list_item['cost_of_alarms_ex_gst'],2):null;
                $csv_row[] = $list_item['alarm_power_used'];                       
                $csv_row[] = $prop_address;
                $csv_row[] = $this->system_model->formatDate($list_item['p_created'],'d/m/Y');
                $csv_row[] = $list_item['p_state'];     
                $csv_row[] = $list_item['agency_name'];           
                
                fputcsv($csv_file,$csv_row); 
    
            }
                    
            fclose($csv_file); 
            exit; 
    
        }else{

            if( $search_submit ){
                $data['lists'] = $job_sql;
            }
            

            if( $search_submit ){

                // all rows
                $sel_query = "SUM(al.`alarm_price`) AS cost_of_alarms";
                $params = array(
                    'sel_query' => $sel_query,
                    //'p_deleted' => 0,
                    //'a_status' => 'active',
                    //'del_job' => 0,
                    'country_id' => $country_id,
                    'job_status' => $job_status,
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
                    'service_filter' => $service_filter,
                    'state_filter' => $state_filter,
                    'agency_filter' => $agency_filter,
                    'custom_where'=> $custom_where,
                    'search' => $search,
                    'postcodes' => $postcodes,
                    'a_deleted' => 'no filter',
                    
                    'sort_list' => array(
                        array(
                            'order_by' => 'j.created',
                            'sort' => 'ASC',
                        ),
                    ),
                    'group_by' => 'j.id',
                    'display_query' => 0
                );
                
                $job_tot_sql = $this->jobs_model->get_jobs($params);
                //echo $this->db->last_query();
                //exit();
                
                $total_rows = $job_tot_sql->num_rows();
                $data['total_rows'] = $total_rows;


                // get invoice total
                //$sel_query = "SUM(`invoice_amount`) AS invoice_amount_tot";
                $sel_query = "j.id, j.invoice_amount";
                $params = array(
                    'sel_query' => $sel_query,
                    //'p_deleted' => 0,
                    //'a_status' => 'active',
                    //'del_job' => 0,
                    'country_id' => $country_id,
                    'job_status' => $job_status,
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
                    'service_filter' => $service_filter,
                    'state_filter' => $state_filter,
                    'agency_filter' => $agency_filter,
                    'custom_where'=> $custom_where,
                    'search' => $search,
                    'postcodes' => $postcodes,
                    'a_deleted' => 'no filter',
                    
                    'sort_list' => array(
                        array(
                            'order_by' => 'j.created',
                            'sort' => 'ASC',
                        ),
                    ),                    
                    'group_by' => 'j.id',
                    'display_query' => 0
                );
                $job_tot_sql = $this->jobs_model->get_jobs($params);
                $data['job_tot_row'] = $job_tot_sql->result_array();
               
               // $invoice_amount_tot = $job_tot_row->invoice_amount_tot;              
               // $data['invoice_amount_tot'] = $invoice_amount_tot;   
                            

            }            
            
            // get cost of alarm total
            //$sel_query = "SUM(al.`alarm_price`) AS cost_of_alarms";
            $sel_query = "SUM(al_p.`alarm_price_ex`) AS cost_of_alarms_tot_ex";
            $params = array(
                'sel_query' => $sel_query,
                //'p_deleted' => 0,
                //'a_status' => 'active',
                //'del_job' => 0,
                'country_id' => $country_id,
                'job_status' => $job_status,
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
                'service_filter' => $service_filter,
                'state_filter' => $state_filter,
                'agency_filter' => $agency_filter,
                'custom_where'=> $custom_where,
                'search' => $search,
                'postcodes' => $postcodes,
                'a_deleted' => 'no filter',
                
                'sort_list' => array(
                    array(
                        'order_by' => 'j.created',
                        'sort' => 'ASC',
                    ),
                ),                    
                'display_query' => 0
            );
            $job_tot_sql = $this->jobs_model->get_jobs($params);
            $job_tot_row = $job_tot_sql->row();                                   
            //$data['total_cost_of_alarms'] = $job_tot_row->cost_of_alarms; 
            $data['cost_of_alarms_tot_ex'] = $job_tot_row->cost_of_alarms_tot_ex; 
            
            // distinct agency
            $sel_query = "DISTINCT(a.`agency_id`), a.`agency_name`";            
            $params = array(
                'sel_query' => $sel_query,
                //'p_deleted' => 0,
                //'a_status' => 'active',
                //'del_job' => 0,
                'country_id' => $country_id,
                'job_status' => $job_status,
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
                'service_filter' => $service_filter,
                'state_filter' => $state_filter,
                'agency_filter' => $agency_filter,
                'custom_where'=> $custom_where,
                'search' => $search,
                'postcodes' => $postcodes,
                'a_deleted' => 'no filter',
                
                'sort_list' => array(
                    array(
                        'order_by' => 'a.`agency_name`',
                        'sort' => 'ASC',
                    ),
                ),                    
                'display_query' => 0
            );

            $data['agency_filter_sql'] = $this->jobs_model->get_jobs($params);   

            // distinct state
            $sel_query = "DISTINCT(p.`property_id`), p.`state`";            
            $params = array(
                'sel_query' => $sel_query,
                //'p_deleted' => 0,
                //'a_status' => 'active',
                //'del_job' => 0,
                'country_id' => $country_id,
                'job_status' => $job_status,
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
                'service_filter' => $service_filter,
                'state_filter' => $state_filter,
                'agency_filter' => $agency_filter,
                'custom_where'=> $custom_where,
                'search' => $search,
                'postcodes' => $postcodes,
                'a_deleted' => 'no filter',
                
                'sort_list' => array(
                    array(
                        'order_by' => 'j.created',
                        'sort' => 'ASC',
                    ),
                ),                    
                'display_query' => 0
            );

            $data['state_filter_sql'] = $this->jobs_model->get_jobs($params); 
        
            $pagi_links_params_arr = array(
                'agency_filter' => $agency_filter,
                'job_type_filter' => $job_filter,
                'service_filter' => $service_filter,
                'state_filter' =>  $state_filter,
                'dateFrom_filter' => $dateFrom_field,
                'dateTo_filter' => $dateTo_field,
                'search_filter' => $search,
                'sub_region_ms' => $sub_region_ms,
                'search_submit' => $search_submit
            );

            // header sort paramerts needs to exclude sort variables
            $data['header_link_params'] = $pagi_links_params_arr;

            // pagination link
            $pagi_link_params = "{$uri}/?".http_build_query($pagi_links_params_arr);

            // explort link
            $data['export_link'] = "{$uri}/?export=1&".http_build_query($pagi_links_params_arr);

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

            $this->load->view('templates/inner_header', $data);
            $this->load->view('jobs/completed_ic_upgrade', $data);
            $this->load->view('templates/inner_footer', $data);

        }        

    }

   


    public function tech_sheet() {

        $this->load->model('tech_model');
        $this->load->model('figure_model');
        
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Technician Sheet";

        $job_id = $this->db->escape_str($this->input->get_post('job_id'));
        $tr_id = $this->db->escape_str($this->input->get_post('tr_id'));
        
        $country_id = $this->config->item('country');
        $staff_class = $this->system_model->getStaffClassID();
        $data['staff_class'] = $staff_class;

        $uri = "/jobs/tech_sheet/?job_id={$job_id}";
        $data['uri'] = $uri;       
         
        
        // store tech run ID on a session so tech can redirect back to techsheet when done
        if( $tr_id > 0 && $staff_class == 6 ){ 
            $this->session->set_userdata('techsheet_tr_id', $tr_id);
        }
        
       

        if( $job_id > 0 ){

            // get job data
            $sel_query = "
            j.`id` AS jid,
            j.`status` AS j_status,
            j.`service` AS j_service,
            j.`created` AS j_created,
            j.`date` AS j_date,
            j.`comments` AS j_comments,
            j.`job_price`,
            j.`job_type`,
            j.`assigned_tech`,
            j.`invoice_amount`,
            j.`work_order`,
            j.`completed_timestamp`,
            j.`ts_signoffdate`,
            j.`swms_heights`,
            j.`swms_uv_protection`,
            j.`swms_asbestos`,
            j.`swms_powertools`,
            j.`swms_animals`,
            j.`swms_live_circuit`,
            j.`swms_covid_19`,
            j.`tech_comments`,
            j.`repair_notes,
            j.`job_reason_id`,
            j.`job_reason_comment`,
            j.`survey_numlevels`,
            j.`survey_ladder`,
            j.`survey_ceiling`,
            j.`ps_number_of_bedrooms`,
            j.`ss_location`,
            j.`ss_quantity`,
            j.`ts_safety_switch`,
            j.`ts_safety_switch_reason`,
            j.`survey_numalarms`,
            j.`ts_batteriesinstalled`,
            j.`ts_items_tested`,
            j.`ss_items_tested`,
            j.`cw_items_tested`,
            j.`we_items_tested`,
            j.`ts_alarmsinstalled`,
            j.`survey_alarmspositioned`,
            j.`survey_minstandard`,
            j.`entry_gained_via`,
            j.`property_leaks`,
            j.`leak_notes`,
            j.`ss_image`,
            j.`ts_techconfirm`,
            j.`prop_comp_with_state_leg`,
            j.`booked_with`,
            j.`job_entry_notice`,
            j.`key_access_required`,
            j.`en_date_issued`,
            j.`key_access_details`,
            j.`entry_gained_other_text`,
            j.`door_knock`,
            j.`time_of_day`,
            
            p.`property_id`, 
            p.`address_1` AS p_street_num, 
            p.`address_2` AS p_street_name, 
            p.`address_3` AS p_suburb,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            p.`comments` AS p_comments, 
            p.`created` AS p_created,
            p.`key_number`,
            p.`alarm_code`,
            p.`prop_upgraded_to_ic_sa`,
            p.`qld_new_leg_alarm_num`,
            p.`preferred_alarm_id`,
            p.`holiday_rental`,
            p.`service_garage`,
            p.`is_sales`,

            pl.`code` AS lb_code,

            nsw_pc.`short_term_rental_compliant`,
            nsw_pc.`req_num_alarms` AS nsw_leg_num_alarms,
            nsw_pc.`req_heat_alarm`,

            al_p.`alarm_make` AS pref_alarm_make,
            
            a.`agency_id`,
            a.`agency_name` AS agency_name,
            a.`phone` AS a_phone,
            a.`address_1` AS a_street_num, 
            a.`address_2` AS a_street_name, 
            a.`address_3` AS a_suburb,
            a.`state` AS a_state,
            a.`postcode` AS a_postcode,
            a.`trust_account_software`,
            a.`tas_connected`,
            a.`agency_specific_notes`,
            aght.`priority`,
            apmd.abbreviation,
            
            ajt.`id` AS service_type_id,
            ajt.`type` AS service_type,
            ajt.`bundle` AS is_bundle_serv,

            t.`StaffID` AS tech_id,
            t.`FirstName` AS tech_fname,
            t.`LastName` AS tech_lname
            ";    
        
            $job_params = array(
                'sel_query' => $sel_query,
                'job_id' => $job_id,
                'country_id' => $country_id,            
                'join_table' => array('job_type','alarm_job_type','tech','preferred_alarm', 'agency_priority', 'agency_priority_marker_definition'),
                'custom_joins_arr' => array(
                    array(
                        'join_table' => 'nsw_property_compliance as nsw_pc',
                        'join_on' => 'p.property_id = nsw_pc.property_id',
                        'join_type' => 'left'
                    ),
                    array(
                        'join_table' => 'property_lockbox as pl',
                        'join_on' => 'p.property_id = pl.property_id',
                        'join_type' => 'left'
                    )
                ),
                'display_query' => 0
            );

            $job_sql = $this->jobs_model->get_jobs($job_params); 
            $job_row =   $job_sql->row();
            $data['job_row'] = $job_row; 

            $ic_service = $this->figure_model->getICService(); // check if IC service type
            $data['is_ic_service'] = ( in_array($job_row->j_service, $ic_service) )?1:0;

            // get 'job booked' log
            $job_log_sql = $this->db->query("
                SELECT `comments`
                FROM `job_log` 
                WHERE `job_id` = {$job_id} 
                AND `deleted` = 0 
                AND `contact_type` = 'Job Booked'
                ORDER BY `eventdate` DESC, `log_id` DESC
                LIMIT 1
            ");
            $job_log_row = $job_log_sql->row();
            $data['booked_job_log'] = $job_log_row->comments;

            // get not completed reason
            if( $job_row->door_knock != 1 ){  // Do not show NTTC on non-DK jobs            

                $ncr_sql_str = "
                    SELECT `job_reason_id`, `name`
                    FROM `job_reason`
                    WHERE `job_reason_id` != 14
                    ORDER BY `name`
                ";

            }else{ // show ALL   

                $ncr_sql_str = "
                    SELECT `job_reason_id`, `name`
                    FROM `job_reason`
                    ORDER BY `name`
                ";
                
            }

            $data['ncr_sql'] = $this->db->query($ncr_sql_str);           
            

            $service_types_arr = [];
            if( $job_row->is_bundle_serv == 1 ){ // if bundle services

                // get bundle services
                $bundle_serv_sql = $this->db->query("
                SELECT `bundle_services_id`, `alarm_job_type_id`
                FROM `bundle_services` AS bs
                LEFT JOIN `alarm_job_type` AS ajt ON ajt.`id` = bs.`alarm_job_type_id`
                WHERE `job_id` = {$job_id}
                ");

                foreach( $bundle_serv_sql->result() as $bundle_serv ){

                    switch( $bundle_serv->alarm_job_type_id ){

                        case 2: // Smoke Alarms
                            $data['has_sa'] =  true;
                        break;

                        case 5: // Safety Switch
                            $data['has_ss'] =  true;
                        break;

                        case 6: // Corded Window
                            $data['has_cw'] =  true;
                        break;

                        case 15: // Water Effeciency
                            $data['has_we'] =  true;
                        break;

                    }

                    $service_types_arr[] = $bundle_serv->alarm_job_type_id;

                    // sync service types of bundle
                    $syncParams = array("job_id" => $job_id, "jserv" => $bundle_serv->alarm_job_type_id, "bundle_serv_id" => $bundle_serv->bundle_services_id);
                    $this->jobs_model->runSync($syncParams);


                }                  
                
            }else{

                switch( $job_row->j_service ){

                    case 2: // Smoke Alarms
                        $data['has_sa'] =  true;
                    break;

                    case 5: // Safety Switch
                        $data['has_ss'] =  true;
                    break;

                    case 6: // Corded Window
                        $data['has_cw'] =  true;
                    break;

                    case 15: // Water Effeciency
                        $data['has_we'] =  true;
                    break;
                    
                }

                $service_types_arr[] = $job_row->j_service;

                // sync single service
                $syncParams = array("job_id" => $job_id, "jserv" => $job_row->j_service);
                $this->jobs_model->runSync($syncParams);

            }
            
            $data['bundle_serv_sql'] = $bundle_serv_sql;  
            $data['service_types_arr'] = $service_types_arr;  
            $data['is_bundle_serv'] = $job_row->is_bundle_serv;       

            if( $staff_class == 6 ){ // tech
                $this->load->view('templates/inner_header_tech', $data);
            }else{
                $this->load->view('templates/inner_header', $data);
            }       
            $this->load->view('jobs/tech_sheet', $data);        
            if( $staff_class == 6 ){ // tech
                $this->load->view('templates/inner_footer_tech', $data);
            }else{
                $this->load->view('templates/inner_footer', $data);
            }

        }else{
            echo "Job ID is required";
        }

        


    }
    

    public function get_dynamic_alarm_power(){

        $agency_id = $this->db->escape_str($this->input->get_post('agency_id'));
        $is_new = $this->db->escape_str($this->input->get_post('is_new'));
        $country_id = $this->config->item('country');

        if( $agency_id > 0 ){

            if( $is_new == 1 ){ // if new join agency alarms

                $sql = $this->db->query("
                    SELECT ap.`alarm_pwr_id`, ap.`alarm_pwr`, ap.`is_li` 
                    FROM `agency_alarms` AS aa
                    LEFT JOIN `alarm_pwr` AS ap ON aa.`alarm_pwr_id` = ap.`alarm_pwr_id`
                    WHERE aa.`agency_id` = {$agency_id}
                    ORDER BY `alarm_pwr`
                ");	
    
            }else{
    
                // exclude batteries
                $sql = $this->db->query("
                    SELECT `alarm_pwr_id`, `alarm_pwr`, `is_li` 
                    FROM `alarm_pwr`
                    WHERE `alarm_pwr_id` != 6
                    ORDER BY `alarm_pwr`
                ");	
    
            }
    
            $html_markup = null;
            foreach( $sql->result() as $row ){
                $html_markup .= '<option value="'.$row->alarm_pwr_id.'" data-is_li="'.$row->is_li.'">'.$row->alarm_pwr.'</option>';
            }
            
            echo $html_markup;

        }

        
    }


    public function ajax_get_alarm_power_details(){

        $alarm_pwr_id = $this->db->escape_str($this->input->get_post('alarm_pwr_id'));

        if( $alarm_pwr_id > 0 ){

            $alarm_sql = $this->db->query("
                SELECT 
                    `alarm_make`,
                    `alarm_model`,
                    `alarm_expiry`,
                    `alarm_type_id`
                FROM `alarm_pwr` AS a_pwr
                LEFT JOIN `alarm_type` AS a_typ ON a_pwr.`alarm_type` = a_typ.`alarm_type_id`
                WHERE a_pwr.`alarm_pwr_id` = {$alarm_pwr_id}
            ");

            $alarm_row = $alarm_sql->row();

            $alarm_arr['alarm_make'] = $alarm_row->alarm_make;
            $alarm_arr['alarm_model'] = $alarm_row->alarm_model;
            $alarm_arr['alarm_expiry'] = $alarm_row->alarm_expiry;
            $alarm_arr['alarm_type_id'] = $alarm_row->alarm_type_id;

            echo json_encode($alarm_arr);

        }

    }


    public function ajax_add_smoke_alarms(){

        $job_id = $this->input->get_post('job_id');        
        $agency_id = $this->input->get_post('agency_id');
        $alarms_arr = $this->input->get_post('alarms_arr');             

        foreach( $alarms_arr as $alarm ){

            // decodes json string to actual json object
	        $json_enc = json_decode($alarm);

            $sa_new = $json_enc->sa_new;
            $sa_rfc = $json_enc->sa_rfc;
            $sa_power = $json_enc->sa_power;
            $sa_type = $json_enc->sa_type;
            $sa_position = strtoupper($json_enc->sa_position);
            $sa_make = $json_enc->sa_make;
            $sa_model = $json_enc->sa_model;
            $sa_expiry = $json_enc->sa_expiry;
            $sa_reason = $json_enc->sa_reason;

            $insert_data = array(
                'job_id' => $job_id,
                'new' => $sa_new,
                'ts_required_compliance' => $sa_rfc,               
                'alarm_power_id' => $sa_power,
                'alarm_type_id' => $sa_type,
                'ts_position' => $sa_position,    
                'make' => $sa_make,
                'model' => $sa_model,
                'expiry' => $sa_expiry, 
                'ts_expiry' => $sa_expiry,                                                       
                'alarm_reason_id' => $sa_reason,
                'ts_added' => 1,
                'alarm_job_type_id' => 2
            );

            if( $sa_new == 1 ){ // new

                // get alarm price from agency
                $aa_sql = $this->db->query("
                    SELECT `price`
                    FROM `agency_alarms`
                    WHERE `agency_id` = {$agency_id}
                    AND `alarm_pwr_id` = {$sa_power}
                ");
                $aa_row = $aa_sql->row();

                //$insert_data['alarm_price'] = $aa_row->price;     

                // override alarm total amount
                $free_alarms_params = array(
                    'alarm_tot_amount' => $aa_row->price,
                    'job_id' => $job_id
                );
                $alarm_price = $this->system_model->free_alarms($free_alarms_params);
                $insert_data['alarm_price'] = $alarm_price;
                
                //insert job log
                $log_details = "New Alarm added at <b>{$sa_position}</b> with price <b>\${$alarm_price}</b>";
                $log_params = array(
                    'title' => 73, // New Alarm
                    'details' => $log_details,
                    'display_in_vjd' => 1,
                    'job_id' => $job_id,
                    'created_by_staff' => $this->session->staff_id
                );

                $this->system_model->insert_log($log_params);

            }
            
            $this->db->insert('alarm', $insert_data);

        }                    

    }


    public function ajax_add_safety_switch(){

        $job_id = $this->input->get_post('job_id');                
        $ss_arr = $this->input->get_post('ss_arr');             

        foreach( $ss_arr as $ss ){

            // decodes json string to actual json object
	        $json_enc = json_decode($ss);

            $ss_make = $json_enc->ss_make;
            $ss_model = $json_enc->ss_model;
            $ss_test = $json_enc->ss_test;

            $insert_data = array(
                'job_id' => $job_id,

                'make' => $ss_make,
                'model' => $ss_model,               
                'test' => $ss_test
            );
            $this->db->insert('safety_switch', $insert_data);

        }                    

    }


    public function ajax_add_corded_window(){

        $job_id = $this->input->get_post('job_id');                
        $cw_arr = $this->input->get_post('cw_arr');             

        foreach( $cw_arr as $cw ){

            // decodes json string to actual json object
	        $json_enc = json_decode($cw);

            $location = strtoupper($json_enc->cw_location);
            $num_of_windows = $json_enc->cw_num_of_windows;

            $insert_data = array(
                'job_id' => $job_id,

                'location' => $location,               
                'num_of_windows' => $num_of_windows
            );
            $this->db->insert('corded_window', $insert_data);

        }                    

    }

    public function ajax_add_water_effeciency(){

        $job_id = $this->input->get_post('job_id');                
        $we_arr = $this->input->get_post('we_arr');             

        foreach( $we_arr as $we ){

            // decodes json string to actual json object
	        $json_enc = json_decode($we);

            $location = strtoupper($json_enc->we_location);
            $device = $json_enc->we_device;
            $pass = $json_enc->we_pass;
            $note = $json_enc->we_notes;            

            $insert_data = array(
                'job_id' => $job_id,

                'location' => $location,               
                'device' => $device,
                'note' => $note
            );

            if( $pass != '' ){
                $insert_data['pass'] = $pass;
            }

            $this->db->insert('water_efficiency', $insert_data);

        }                    

    }

    
    public function ajax_mark_job_not_completed(){

        //load model
        $this->load->model('/inc/email_functions_model'); 

        $job_id = $this->db->escape_str($this->input->get_post('job_id'));   
        $jobs_not_comp_res = $this->db->escape_str($this->input->get_post('jobs_not_comp_res'));    
        $jobs_not_comp_com = $this->db->escape_str($this->input->get_post('jobs_not_comp_com'));  
        $staff_id = $this->session->staff_id;
        $today = date("Y-m-d H:i:s");

        // get job data
        $job_sql = $this->db->query("
            SELECT `door_knock`, `assigned_tech`
            FROM `jobs`
            WHERE `id` = '{$job_id}'
        ");
        $job_row = $job_sql->row();

        // get job reason
        $job_res_sql = $this->db->query("
            SELECT *
            FROM `job_reason`
            WHERE `job_reason_id` = {$jobs_not_comp_res}
        ");
        $job_res_row = $job_res_sql->row();

        
        // if refuse entry, send email
        if( $jobs_not_comp_res == 10 ){
            // no longer used said by sir dan
            //$this->email_functions_model->mark_job_not_completed_email($job_id);     
        }
           
        // update job
        $update_query_str = "
            UPDATE jobs 
            SET 
                `status` = 'Pre Completion', 
                `job_reason_id` = {$jobs_not_comp_res},
                `job_reason_comment` = '{$jobs_not_comp_com}',
                `completed_timestamp` = '{$today}'
            WHERE `id` = {$job_id}
        ";	
        $this->db->query($update_query_str);


        // Insert log
        $append_log_det = null;
        if( $jobs_not_comp_com != '' ){
            $append_log_det = ", Comment: {$jobs_not_comp_com}";
        }
        $log_details = "Due to <b>{$job_res_row->name}{$append_log_det}</b>";

        //insert to logs table
        $insert_log_sql = "INSERT INTO logs (`title`, `details`, `display_in_vjd`, `created_by_staff`, `job_id`,`created_date`) VALUES ('74', '{$log_details}', '1', '{$this->session->staff_id}', '{$job_id}','$today')";
        $this->db->query($insert_log_sql);

      
        //insert to jobs_not_completed table
        $insert_sql = "
            INSERT INTO 
            jobs_not_completed (
                `job_id`, 
                `reason_id`, 
                `reason_comment`, 
                `tech_id`, 
                `date_created`,
                `door_knock`
            ) 
            VALUES (
                {$job_id}, 
                {$jobs_not_comp_res}, 
                '{$jobs_not_comp_com}', 
                {$job_row->assigned_tech}, 
                '{$today}',
                '{$job_row->door_knock}'
            )
        ";
        $this->db->query($insert_sql);

    }


    // tenant update
    public function ajax_techsheet_update_tenants(){
        
        $pt_id = $this->db->escape_str($this->input->get_post('pt_id'));   
        $db_table_field = $this->db->escape_str($this->input->get_post('db_table_field'));    
        $db_table_value = $this->db->escape_str($this->input->get_post('db_table_value'));  
        
        $staff_id = $this->session->staff_id;
        $today = date("Y-m-d H:i:s");  
        
        // allowed field for update
        $allowed_field = array('tenant_firstname','tenant_lastname','tenant_mobile','tenant_landline');
           
        // update job
        if( $pt_id > 0 && in_array($db_table_field, $allowed_field) ){
            
            $update_query_str = "
            UPDATE `property_tenants` 
            SET `{$db_table_field}` = '{$db_table_value}'
            WHERE `property_tenant_id` = {$pt_id}
            ";	
            $this->db->query($update_query_str);

        }        

    }

    // techsheet inline ajax update
    public function ajax_techsheet_inline_update(){
        
        $job_id = $this->db->escape_str($this->input->get_post('job_id')); 
        $property_id = $this->db->escape_str($this->input->get_post('property_id')); 

        $db_table = $this->db->escape_str($this->input->get_post('db_table'));  
        $db_table_field = $this->db->escape_str($this->input->get_post('db_table_field'));    
        $db_table_value = $this->db->escape_str($this->input->get_post('db_table_value')); 
        $db_table_value_fin = $db_table_value; 

        $staff_id = $this->session->staff_id;
        $today = date("Y-m-d H:i:s");  
           
        if( $db_table == 'jobs' && $job_id > 0 ){ // jobs update

            // allowed field for update
            $allowed_field = array(
                'survey_numlevels','survey_ladder','survey_ceiling','ps_number_of_bedrooms','ts_safety_switch', 'comments',
                'ts_safety_switch_reason','survey_numalarms','repair_notes','tech_comments','swms_heights','swms_uv_protection',
                'swms_asbestos','swms_powertools','swms_animals','swms_live_circuit', 'swms_covid_19', 'ts_batteriesinstalled',
                'ts_items_tested', 'ts_alarmsinstalled', 'survey_alarmspositioned', 'survey_minstandard', 'entry_gained_via',
                'survey_alarmspositioned', 'survey_minstandard', 'entry_gained_via', 'property_leaks', 'leak_notes', 'ss_location', 
                'ss_quantity', 'entry_gained_other_text', 'ss_items_tested', 'cw_items_tested', 'we_items_tested'
            );

            if( in_array($db_table_field,$allowed_field) ){

                // if it has leak notes from WE append reminder text to `tech_comments`
                $we_reminder_txt = '--- Check leak notes!';
                $job_sql_str = "
                SELECT `tech_comments`
                FROM `jobs`       
                WHERE `id` = {$job_id}                
                ";	
                $job_sql_sql = $this->db->query($job_sql_str);      
                $job_sql_row = $job_sql_sql->row();          

                // append reminder text, if text already exist dont append
                $append_update = null;
                if( $db_table_field == 'leak_notes' && $job_sql_row->tech_comments != '' && strpos($job_sql_row->tech_comments, $we_reminder_txt) == false ){
                    $append_update = ",`tech_comments` = '{$job_sql_row->tech_comments} {$we_reminder_txt}'";
                }	

                if( $db_table_field == 'ss_location' ){ // location to all CAPS
                    $db_table_value_fin = strtoupper($db_table_value);
                }

                $update_query_str = "
                UPDATE `jobs` 
                SET 
                    `{$db_table_field}` = '{$db_table_value_fin}'
                    {$append_update}
                WHERE `id` = {$job_id}
                ";	
                $this->db->query($update_query_str);

            }                    

        } else if( $db_table == 'property' && $property_id > 0 ){ // property update

            // allowed field for update
            $allowed_field = array(
                'key_number','alarm_code','comments','prop_upgraded_to_ic_sa', 
                'qld_new_leg_alarm_num','service_garage'
            );

            if( in_array($db_table_field,$allowed_field) ){

                $update_query_str = "
                UPDATE `property` 
                SET `{$db_table_field}` = '{$db_table_value}'
                WHERE `property_id` = {$property_id}
                ";	
                $this->db->query($update_query_str);

                if( $db_table_field == 'service_garage' ){

                    // insert log
                    $log_details = "Property <b>". ( ( $db_table_value == 1 )?'marked':'unmarked' ) ."</b> as <b>Attached garage requires alarm</b>";
                    $log_params = array(
                        'title' => 65,  // Property Update   
                        'details' => $log_details,
                        'display_in_vpd' => 1,
                        'created_by_staff' => $this->session->staff_id,
                        'property_id' => $property_id
                    );
                    $this->system_model->insert_log($log_params);
    
                }

            }                   

        }else if( $db_table == 'nsw_pro_comp' && $property_id > 0 ){ // property update

            // allowed field for update
            $allowed_field = array('short_term_rental_compliant','req_num_alarms','req_heat_alarm');

            if( in_array($db_table_field,$allowed_field) ){

                // check if entry already exist
                $sel_query_str = "
                SELECT *
                FROM `nsw_property_compliance`             
                WHERE `property_id` = {$property_id}                
                ";	
                $nsw_prop_comp_sql = $this->db->query($sel_query_str);
                
                if( $nsw_prop_comp_sql->num_rows() > 0 ){ // exist, update

                    $update_query_str = "
                    UPDATE `nsw_property_compliance` 
                    SET `{$db_table_field}` = '{$db_table_value}'
                    WHERE `property_id` = {$property_id}
                    ";	
                    $this->db->query($update_query_str);

                }else{ // empty, insert

                    $insert_query_str = "
                    INSERT INTO  
                    `nsw_property_compliance` (
                        `property_id`,
                        `{$db_table_field}`
                    )
                    VALUES(
                        {$property_id},
                        '{$db_table_value}'                        
                    )                   
                    ";	
                    $this->db->query($insert_query_str);

                }                

            }                   

        }        

    }

    // upload safety switch switch board image
    public function upload_ss_switchboard_images(){
        
        $job_id = $this->db->escape_str($this->input->get_post('job_id')); 
        $tr_id = $this->session->techsheet_tr_id;                             
        
        if( $job_id > 0 ){

            // if a file image file has been selected
            if( $_FILES["ss_image"]['name'] != '' ){

                $folder = 'uploads/switchboard_image'; // upload path
                $image_name = "switchboard{$job_id}".rand().date("YmdHis"); // file name
                $file = pathinfo($_FILES["ss_image"]['name']); // file extension
                $image_name_full = "{$image_name}.{$file['extension']}"; // full file name with extension
                $server_path = $_SERVER['DOCUMENT_ROOT'].'/'.$folder; // servier path
                
                $handle = new upload($_FILES['ss_image']); // initiate class.upload library

                if ($handle->uploaded) {
                   
                    $handle->file_new_name_body = $image_name; // image name
                    $handle->image_resize = true;
                    $handle->image_x = 760; // width
                    $handle->image_ratio_y = true; // aspect ratio
                    $handle->process($server_path);

                    if ($handle->processed) { // success

                        $handle->clean();
                       
                        // update switch board image
                        $this->db->query("
                        UPDATE `jobs` 
                        SET `ss_image` = '{$image_name_full}'
                        WHERE `id` = {$job_id}                     
                        "); 
                         
                        //$this->session->set_flashdata('switchboard_upload_success',true);                        

                    } else { // error
                        echo 'error : ' . $handle->error;
                    }

                }
                

            } 
            
            if( $tr_id > 0 ){
                redirect("/jobs/tech_sheet/?job_id={$job_id}&tr_id={$tr_id}");
            }else{
                redirect("/jobs/tech_sheet/?job_id={$job_id}");

            }
            
        }
            

    }


    // delete smoke alarm
    public function ajax_delete_techsheet_smoke_alarm(){
                
        $job_id = $this->db->escape_str($this->input->get_post('job_id')); 
        $alarm_id = $this->db->escape_str($this->input->get_post('alarm_id'));  
        $tr_id = $this->session->techsheet_tr_id;
        
        $staff_id = $this->session->staff_id;
        $today = date("Y-m-d H:i:s");  
                 
        if( $job_id > 0 && $alarm_id > 0 ){
            
            $delete_query_str = "
            DELETE
            FROM `alarm`             
            WHERE `alarm_id` = {$alarm_id}
            AND `job_id` = {$job_id}
            ";	
            $this->db->query($delete_query_str);

            if( $tr_id > 0 ){
                redirect("/jobs/tech_sheet/?job_id={$job_id}&tr_id={$tr_id}");
            }else{
                redirect("/jobs/tech_sheet/?job_id={$job_id}");

            }

        }              

    }


    // delete smoke alarm
    public function ajax_delete_techsheet_safety_switch(){
                
        $job_id = $this->db->escape_str($this->input->get_post('job_id')); 
        $safety_switch_id = $this->db->escape_str($this->input->get_post('safety_switch_id'));  
        $tr_id = $this->session->techsheet_tr_id;
        
        $staff_id = $this->session->staff_id;
        $today = date("Y-m-d H:i:s");  
                 
        if( $job_id > 0 && $safety_switch_id > 0 ){
            
            $delete_query_str = "
            DELETE
            FROM `safety_switch`             
            WHERE `safety_switch_id` = {$safety_switch_id}
            AND `job_id` = {$job_id}
            ";	
            $this->db->query($delete_query_str);

            if( $tr_id > 0 ){
                redirect("/jobs/tech_sheet/?job_id={$job_id}&tr_id={$tr_id}");
            }else{
                redirect("/jobs/tech_sheet/?job_id={$job_id}");

            }

        }              

    }


    // delete corded window
    public function ajax_delete_techsheet_corded_window(){
                
        $job_id = $this->db->escape_str($this->input->get_post('job_id')); 
        $corded_window_id = $this->db->escape_str($this->input->get_post('corded_window_id'));  
        $tr_id = $this->session->techsheet_tr_id;
        
        $staff_id = $this->session->staff_id;
        $today = date("Y-m-d H:i:s");  
                 
        if( $job_id > 0 && $corded_window_id > 0 ){
            
            $delete_query_str = "
            DELETE
            FROM `corded_window`             
            WHERE `corded_window_id` = {$corded_window_id}
            AND `job_id` = {$job_id}
            ";	
            $this->db->query($delete_query_str);

            if( $tr_id > 0 ){
                redirect("/jobs/tech_sheet/?job_id={$job_id}&tr_id={$tr_id}");
            }else{
                redirect("/jobs/tech_sheet/?job_id={$job_id}");

            }

        }              

    }


    // delete water efficiency
    public function ajax_delete_techsheet_water_efficiency(){
                
        $job_id = $this->db->escape_str($this->input->get_post('job_id')); 
        $water_efficiency_id = $this->db->escape_str($this->input->get_post('water_efficiency_id')); 
        $tr_id = $this->session->techsheet_tr_id; 
        
        $staff_id = $this->session->staff_id;
        $today = date("Y-m-d H:i:s");  
                 
        if( $job_id > 0 && $water_efficiency_id > 0 ){
            
            $delete_query_str = "
            DELETE
            FROM `water_efficiency`             
            WHERE `water_efficiency_id` = {$water_efficiency_id}
            AND `job_id` = {$job_id}
            ";	
            $this->db->query($delete_query_str);

            if( $tr_id > 0 ){
                redirect("/jobs/tech_sheet/?job_id={$job_id}&tr_id={$tr_id}");
            }else{
                redirect("/jobs/tech_sheet/?job_id={$job_id}");

            }

        }              

    }


    // smoke alarm row update
    public function ajax_techsheet_smoke_alarm_row_update(){
        
        $alarm_id = $this->db->escape_str($this->input->get_post('alarm_id'));   
        $db_table_field = $this->db->escape_str($this->input->get_post('db_table_field'));    
        $db_table_value = $this->db->escape_str($this->input->get_post('db_table_value'));  
        
        $staff_id = $this->session->staff_id;
        $today = date("Y-m-d H:i:s");  
        
        // allowed field for update
        $allowed_field = array(
            'ts_required_compliance','alarm_power_id','alarm_type_id','ts_position','make','model','expiry','ts_expiry','ts_db_rating',
            'alarm_reason_id', 'ts_fixing', 'ts_cleaned', 'ts_newbattery', 'ts_testbutton', 'ts_visualind', 'ts_meetsas1851', 
            'ts_discarded', 'rec_batt_exp'
        );
           
        // update job
        if( $alarm_id > 0 && in_array($db_table_field, $allowed_field) ){

            $db_table_value_fin = "'{$db_table_value}'";

            // if Recording Battery Expiry format to Y-m-d
            if( $db_table_field == 'rec_batt_exp' ){

                // format to Y-m-d set day to 1
                //$rec_batt_exp_split = str_split($db_table_value, 2); // split by 2 characters
                $rec_batt_exp_split = explode("/",$db_table_value);

                $rec_batt_exp_formatted = "{$rec_batt_exp_split[1]}-{$rec_batt_exp_split[0]}-01";
                $db_table_value = $rec_batt_exp_formatted;

            }

            // if Recording Battery Expiry format to Y-m-d
            if( $db_table_field == 'rec_batt_exp' || $db_table_field == 'expiry' || $db_table_field == 'ts_expiry' ){

                if( $db_table_value == '' ){
                    $db_table_value_fin = 'NULL';
                }

            }

            // location to all CAPS
            if( $db_table_field == 'ts_position' ){
                
                $db_table_value_fin = "'".strtoupper($db_table_value)."'";

            }
            
            
            $update_query_str = "
            UPDATE `alarm` 
            SET `{$db_table_field}` = {$db_table_value_fin}
            WHERE `alarm_id` = {$alarm_id}
            ";	
            $this->db->query($update_query_str);

        }        

    }

    // smoke alarm battery expiry update
    public function ajax_techsheet_smoke_alarm_batt_exp_update(){
        
        $alarm_id = $this->db->escape_str($this->input->get_post('alarm_id'));   
        $rec_batt_exp = $this->db->escape_str($this->input->get_post('rec_batt_exp'));            
        
        $staff_id = $this->session->staff_id;
        $today = date("Y-m-d H:i:s");  

        $skip_update = false;
        $rec_batt_exp_formatted2 = null;
        $rec_batt_exp_formatted_final = 'NULL';

        // update job
        if( $alarm_id > 0 ){

            if( $rec_batt_exp != '' ){

                // format to Y-m-d set day to 1
                //$rec_batt_exp_split = str_split($rec_batt_exp, 2); // split by 2 characters
                $rec_batt_exp_split = explode("/",$rec_batt_exp);

                $rec_batt_exp_month = $rec_batt_exp_split[0];
                $rec_batt_exp_year = $rec_batt_exp_split[1];

                if( $rec_batt_exp_month >= 1 && $rec_batt_exp_month <= 12 ){

                    $rec_batt_exp_formatted = "{$rec_batt_exp_year}-{$rec_batt_exp_month}-01";
                    $rec_batt_exp_formatted2 = date('Y-m-d',strtotime($rec_batt_exp_formatted));
                    $rec_batt_exp_formatted_final = "'{$rec_batt_exp_formatted2}'";
                    
                }else{
                    $skip_update = true;
                }
                

            }
            
            
            if( $skip_update == false ){

                $update_query_str = "
                UPDATE `alarm` 
                SET `rec_batt_exp` = {$rec_batt_exp_formatted_final}
                WHERE `alarm_id` = {$alarm_id}
                ";	
                $this->db->query($update_query_str);

                echo $rec_batt_exp_formatted2;

            }             

        }        

    }

    public function ajax_update_smoke_alarm_discarded_and_reason(){
        
        $alarm_id = $this->db->escape_str($this->input->get_post('alarm_id'));   
        $ts_discarded = $this->db->escape_str($this->input->get_post('ts_discarded'));    
        $ts_discarded_reason = $this->db->escape_str($this->input->get_post('ts_discarded_reason'));  
        
        $staff_id = $this->session->staff_id;
        $today = date("Y-m-d H:i:s");  

        if( $alarm_id > 0 ){ 

            $update_data = array(
                'ts_discarded' => $ts_discarded,
                'ts_discarded_reason' => $ts_discarded_reason
            );
            
            $this->db->where('alarm_id', $alarm_id);
            $this->db->update('alarm', $update_data);

        }      

    }


    // corded window row update
    public function ajax_techsheet_corded_window_row_update(){
        
        $corded_window_id = $this->db->escape_str($this->input->get_post('corded_window_id'));   
        $db_table_field = $this->db->escape_str($this->input->get_post('db_table_field'));    
        $db_table_value = $this->db->escape_str($this->input->get_post('db_table_value'));  
        $db_table_value_fin = $db_table_value; 
        
        $staff_id = $this->session->staff_id;
        $today = date("Y-m-d H:i:s");  
        
        // allowed field for update
        $allowed_field = array('location','num_of_windows');
           
        // update job
        if( $corded_window_id > 0 && in_array($db_table_field, $allowed_field) ){


            if( $db_table_field == 'location' ){ // location to all CAPS
                $db_table_value_fin = strtoupper($db_table_value);
            }
            
            $update_query_str = "
            UPDATE `corded_window` 
            SET `{$db_table_field}` = '{$db_table_value_fin}'
            WHERE `corded_window_id` = {$corded_window_id}
            ";	
            $this->db->query($update_query_str);

        }        

    }


    // safety switch row update
    public function ajax_techsheet_safety_switch_row_update(){
        
        $safety_switch_id = $this->db->escape_str($this->input->get_post('safety_switch_id'));   
        $db_table_field = $this->db->escape_str($this->input->get_post('db_table_field'));    
        $db_table_value = $this->db->escape_str($this->input->get_post('db_table_value'));  
        
        $staff_id = $this->session->staff_id;
        $today = date("Y-m-d H:i:s");  
        
        // allowed field for update
        $allowed_field = array('make','model','test');
           
        // update job
        if( $safety_switch_id > 0 && in_array($db_table_field, $allowed_field) ){
            
            $update_query_str = "
            UPDATE `safety_switch` 
            SET `{$db_table_field}` = '{$db_table_value}'
            WHERE `safety_switch_id` = {$safety_switch_id}
            ";	
            $this->db->query($update_query_str);

        }        

    }


    // water efficiency row update
    public function ajax_techsheet_water_efficiency_row_update(){
        
        $water_efficiency_id = $this->db->escape_str($this->input->get_post('water_efficiency_id'));   
        $db_table_field = $this->db->escape_str($this->input->get_post('db_table_field'));    
        $db_table_value = $this->db->escape_str($this->input->get_post('db_table_value'));  
        $job_id = $this->input->get_post('job_id');  
        $db_table_value_fin = $db_table_value; 
        
        $staff_id = $this->session->staff_id;
        $today = date("Y-m-d H:i:s");  
        
        // allowed field for update
        $allowed_field = array('pass','location','note');
           
        // update job
        if( $water_efficiency_id > 0 && in_array($db_table_field, $allowed_field) ){

            if( $db_table_field == 'location' ){ // location to all CAPS
                $db_table_value_fin = strtoupper($db_table_value);
            }
            
            $update_query_str = "
            UPDATE `water_efficiency` 
            SET `{$db_table_field}` = '{$db_table_value_fin}'
            WHERE `water_efficiency_id` = {$water_efficiency_id}
            ";	
            $this->db->query($update_query_str);

            $query = $this->db->query("
            SELECT COUNT( * ) AS total
            FROM `water_efficiency`
            WHERE `job_id` = {$job_id} AND pass IS NULL
            ");
            $row = $query->row();

            echo $row->total;
        }        

    }


    // submit tech sheet
    public function submit_tech_sheet(){

        $job_id = $this->db->escape_str($this->input->get_post('job_id'));
        $ts_techconfirm = $this->db->escape_str($this->input->get_post('ts_techconfirm'));
        $prop_comp_with_state_leg = $this->db->escape_str($this->input->get_post('prop_comp_with_state_leg'));
        $prop_upgraded_to_ic_sa = $this->db->escape_str($this->input->get_post('prop_upgraded_to_ic_sa'));
        
        $staff_id = $this->session->staff_id;
        $today = date("Y-m-d H:i:s");  
           
        if( $job_id > 0 ){

            // get job data
            $job_sql = $this->db->query("
            SELECT 
                j.`status` AS jstatus, 
                j.`service` AS jservice,
                j.`property_id`,
                
                p.`state` AS p_state,
                
                ajt.`bundle`                                
			FROM `jobs` AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
			WHERE j.`id` = {$job_id}
            ");
            $job_row = $job_sql->row();

            if( $job_row->bundle == 1 ){

                // get bundle services
                $bundle_serv_sql = $this->db->query("
                SELECT `bundle_services_id`, `alarm_job_type_id`
                FROM `bundle_services` AS bs
                LEFT JOIN `alarm_job_type` AS ajt ON ajt.`id` = bs.`alarm_job_type_id`
                WHERE `job_id` = {$job_id}
                ");

                foreach( $bundle_serv_sql->result() as $bundle_serv ){

                    // update bundle service completed status
                    $update_bundle_serv_sql_str = "
                    UPDATE `bundle_services`
                    SET `completed` = 1
                    WHERE `job_id` = {$job_id}
                    AND `bundle_services_id` = {$bundle_serv->bundle_services_id}
                    ";
                    $this->db->query($update_bundle_serv_sql_str);

                    $ts_confirm_marker = null;
                    if( $bundle_serv->alarm_job_type_id == 2 ){ // smoke alarm
                        $ts_confirm_marker = 'ts_techconfirm';
                    }else if( $bundle_serv->alarm_job_type_id == 5 ){ // safety switch
                        $ts_confirm_marker = 'ss_techconfirm';
                    }else if( $bundle_serv->alarm_job_type_id == 6 ){ // corded window
                        $ts_confirm_marker = 'cw_techconfirm';
                    }else if( $bundle_serv->alarm_job_type_id == 15 ){ // water efficiency
                        $ts_confirm_marker = 'we_techconfirm';
                    }

                    if( $ts_confirm_marker != '' ){

                        // update confirm checkbox marker
                        $update_query_str = "
                        UPDATE `jobs`
                        SET `{$ts_confirm_marker}` = 1
                        WHERE `id` = {$job_id}
                        ";	
                        $this->db->query($update_query_str);   

                    }                    

                }

            }else{

                $ts_confirm_marker = null;
                if( $job_row->jservice == 2 ){ // smoke alarm
                    $ts_confirm_marker = 'ts_techconfirm';
                }else if( $job_row->jservice == 5 ){ // safety switch
                    $ts_confirm_marker = 'ss_techconfirm';
                }else if( $job_row->jservice == 6 ){ // corded window
                    $ts_confirm_marker = 'cw_techconfirm';
                }else if( $job_row->jservice == 15 ){ // water efficiency
                    $ts_confirm_marker = 'we_techconfirm';
                }

                if( $ts_confirm_marker != '' ){

                    // update confirm checkbox marker
                    $update_query_str = "
                    UPDATE `jobs`
                    SET `{$ts_confirm_marker}` = 1
                    WHERE `id` = {$job_id}
                    ";	
                    $this->db->query($update_query_str);   

                }  
                
            }
                    
            // update job
            $update_query_str = "
            UPDATE `jobs`
            SET 
                `status` = 'Pre Completion',
                `ts_completed` = 1,
                `completed_timestamp` = '{$today}',
                `precomp_jobs_moved_to_booked` = NULL,
                `prop_comp_with_state_leg` = {$prop_comp_with_state_leg},                
				`job_reason_id` = NULL,
				`job_reason_comment` = NULL
			WHERE `id` = {$job_id}
            ";	
            $this->db->query($update_query_str);   

            if( $job_row->property_id > 0 ){

                if( $job_row->p_state == 'QLD' ){

                    // update property
                    $update_query_str = "
                    UPDATE `property`
                    SET 
                        `prop_upgraded_to_ic_sa` = {$prop_upgraded_to_ic_sa}
                    WHERE `property_id` = {$job_row->property_id}
                    ";	
                    $this->db->query($update_query_str);   

                }
                
            }            
            
            // insert log
            $log_details = "<b>Techsheet Completed</b>, job changed from <b>{$job_row->jstatus}</b> to <b>Pre Completion</b>";
            $log_params = array(
                'title' => 75, // Techsheet Completed
                'details' => $log_details,
                'display_in_vjd' => 1,
                'created_by_staff' => $this->session->staff_id,
                'job_id' => $job_id
            );
            $this->system_model->insert_log($log_params);

        } 

    }



    // tenant update
    public function ajax_techsheet_update_expiry_and_ts_expiry(){
        
        $alarm_id = $this->db->escape_str($this->input->get_post('alarm_id'));  
        $selected_expiry = $this->db->escape_str($this->input->get_post('selected_expiry'));
                
        if( $alarm_id > 0 ){

            // update expiry and ts_expiry
            $update_query_str = "
            UPDATE `alarm`
            SET 
                `expiry` = '{$selected_expiry}',
                `ts_expiry` = '{$selected_expiry}'
            WHERE `alarm_id` = {$alarm_id}
            ";	
            $this->db->query($update_query_str);   

        }        
              

    }


    // smoke alarm row update
    public function ajax_save_existing_alarm_questions(){
        
        $alarm_id = $this->db->escape_str($this->input->get_post('alarm_id')); 

        $ts_fixing = $this->db->escape_str($this->input->get_post('ts_fixing'));    
        $ts_cleaned = $this->db->escape_str($this->input->get_post('ts_cleaned'));  
        $ts_newbattery = $this->db->escape_str($this->input->get_post('ts_newbattery'));  
        $ts_testbutton = $this->db->escape_str($this->input->get_post('ts_testbutton'));  
        $ts_visualind = $this->db->escape_str($this->input->get_post('ts_visualind'));  
        $ts_meetsas1851 = $this->db->escape_str($this->input->get_post('ts_meetsas1851'));  
        
        if( $alarm_id > 0 ){

            $update_query_str = "
            UPDATE `alarm` 
            SET 
                `ts_fixing` = '{$ts_fixing}',
                `ts_cleaned` = '{$ts_cleaned}',
                `ts_newbattery` = '{$ts_newbattery}',
                `ts_testbutton` = '{$ts_testbutton}',
                `ts_visualind` = '{$ts_visualind}',
                `ts_meetsas1851` = '{$ts_meetsas1851}'
            WHERE `alarm_id` = {$alarm_id}
            ";	
            $this->db->query($update_query_str);

        }        

    }


    public function ajax_save_no_alarm_reason(){

        $job_id = $this->db->escape_str($this->input->get_post('job_id')); 
        $no_alam_reason = $this->db->escape_str($this->input->get_post('no_alam_reason')); 

        // fetch `tech_comments`
        $sel_query_str = "
        SELECT `tech_comments`
        FROM `jobs`        
        WHERE `id` = {$job_id}
        ";	
        $job_sql = $this->db->query($sel_query_str); 
        $job_row = $job_sql->row();

        // append no alarm reason to tech comments
        if( $job_row->tech_comments != '' ){
            $tech_comments = "{$job_row->tech_comments} - no alarms reason: {$no_alam_reason}";
        }else{
            $tech_comments = $no_alam_reason;
        }        

        // update `tech_comments`
        $update_query_str = "
        UPDATE `jobs`
        SET `tech_comments` = '{$tech_comments}'
        WHERE `id` = {$job_id}
        ";	
        $this->db->query($update_query_str); 

    }

    // send SMS
    public function send_sms_or_email(){

        $this->load->model('sms_model');
        $this->load->model('email_model');        
        $this->load->model('/inc/email_functions_model');

        $country_id = $this->config->item('country');        
        $staff_id = $this->session->staff_id;       
        $today_full = date("Y-m-d H:i:s");        

        $job_id_arr = $this->input->get_post('job_id_arr');
        $sms_type = $this->input->get_post('sms_type');
        $email_type = $this->input->get_post('email_type');          
        
        foreach( $job_id_arr as $job_id ){

            if( $job_id > 0 &&  $sms_type > 0 ){

                $send_sms_params = array(
                    'job_id' => $job_id,
                    'staff_id' => $staff_id,
                    'sms_type' => $sms_type
                );
                $this->jobs_model->send_sms_to_booked_with_tenant($send_sms_params);            
    
            } 

            if( $job_id > 0 &&  $email_type > 0 ){

                $email_params = array(
                    'job_id' => $job_id,
                    'email_type' => $email_type
                );
                $this->email_functions_model->send_email_to_agency_using_template($email_params);                 
    
            } 
            
        }               

    }


    public function ajax_update_repair_notes(){

        $job_id = $this->db->escape_str($this->input->get_post('job_id')); 
        $repair_notes = $this->db->escape_str($this->input->get_post('repair_notes'));   
        
        if( $job_id > 0 ){

            // update `tech_comments`
            $update_query_str = "
            UPDATE `jobs`
            SET `repair_notes` = '{$repair_notes}'
            WHERE `id` = {$job_id}
            ";	
            $this->db->query($update_query_str); 

        }        

    }

    public function ajax_update_tech_comments(){

        $job_id = $this->db->escape_str($this->input->get_post('job_id')); 
        $tech_comments = $this->db->escape_str($this->input->get_post('tech_comments'));   
        
        if( $job_id > 0 ){

            // update `tech_comments`
            $update_query_str = "
            UPDATE `jobs`
            SET `tech_comments` = '{$tech_comments}'
            WHERE `id` = {$job_id}
            ";	
            $this->db->query($update_query_str); 

        }        

    }

    public function ajax_update_property_comments(){

        $property_id = $this->db->escape_str($this->input->get_post('property_id')); 
        $p_comments = $this->db->escape_str($this->input->get_post('p_comments'));   
        
        if( $property_id > 0 ){

            // update `tech_comments`
            $update_query_str = "
            UPDATE `property`
            SET `comments` = '{$p_comments}'
            WHERE `property_id` = {$property_id}
            ";	
            $this->db->query($update_query_str); 

        }        

    }

    public function approved_alarm_numbers(){
        
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Approved Alarm Numbers";
        $uri = "/jobs/approved_alarm_numbers";
        $data['uri'] = $uri;

        $job_status = 'To Be Booked';       
        
        $preferred_alarm_id = $this->input->get_post('preferred_alarm_id');                
        $state_filter = $this->input->get_post('state_filter');              
        $country_id = $this->config->item('country');  
        $export = $this->input->get_post('export');
        
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


        $order_by = ( $this->input->get_post('order_by') !='' )?$this->input->get_post('order_by'):'j.date';
        $sort = ( $this->input->get_post('sort') !='' )?$this->input->get_post('sort'):'asc';
        
        // pagination
        $per_page = $this->config->item('pagi_per_page');        
        $offset = $this->input->get_post('offset');

        $sel_query = "
        j.`id` AS jid,
        j.`status` AS j_status,
        j.`service` AS j_service,
        j.`created` AS j_created,
        j.`date` AS j_date,
        j.`start_date`,
        j.`due_date`,
        j.`comments` AS j_comments,
        j.`job_price` AS j_price,
        j.`job_type` AS j_type,
        j.`property_vacant`,
        j.`urgent_job`,
        j.`job_reason_id`,
        j.`is_eo`,
        j.`job_type` AS j_type,

        DATEDIFF(CURDATE(), Date(j.`created`)) AS age,
        
        p.`property_id` AS prop_id, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3,
        p.`state` AS p_state,
        p.`postcode` AS p_postcode,
        p.`comments` AS p_comments, 
        p.`no_dk`,
        p.`holiday_rental`,
        p.`preferred_alarm_id`,
        p.`qld_new_leg_alarm_num`,

        al_p.`alarm_make` AS pref_alarm_make,
        
        a.`agency_id` AS a_id,
        a.`agency_name` AS agency_name,
        a.`phone` AS a_phone,
        a.`address_1` AS a_address_1, 
        a.`address_2` AS a_address_2, 
        a.`address_3` AS a_address_3,
        a.`state` AS a_state,
        a.`postcode` AS a_postcode,
        a.`trust_account_software`,
        a.`tas_connected`,
        a.`allow_dk`,
        aght.priority,
        apmd.abbreviation,
        
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type,

        sa.`is_electrician`
        ";

        $custom_where = "j.`job_type` = 'IC Upgrade'";

        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,      

            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'job_status' => $job_status,
            'country_id' => $country_id,

            'state_filter' => $state_filter,            
            'postcodes' => $postcodes,            
            'preferred_alarm_id' => $preferred_alarm_id,
            'is_eo' => $show_is_eo,

            'join_table' => array('job_type','alarm_job_type','staff_accounts','preferred_alarm','agency_priority', 'agency_priority_marker_definition'),                       

            'sort_list' => array(
                array(
                    'order_by' => $order_by,
                    'sort' => $sort,
                ),
            ),
            'display_query' => 0
        );

        // export should show all
        if ( $export != 1 ){ 
            $params['limit'] = $per_page;
            $params['offset'] = $offset;
        }
        
        $jobs = $this->jobs_model->get_jobs($params)->result_array();
        $jobsById = []; // make an object/map so you can access them by id easier later

        $jobsPerRegion = []; // map agencies per region
       
        for ($x = 0; $x < count($jobs); $x++) {
            $job = &$jobs[$x];

            $job['last_contact'] = null;
            $job['region'] = null; // empty for now
            $jobsById[$job['jid']] = &$job; // take note with the &. reference the id to the object

             #generate an empty array for later. there could be an unsafe shortcut for this
             if (!isset($jobsPerRegion[$job['p_postcode']])) {
                $jobsPerRegion[$job['p_postcode']] = [];
            }

            $jobsPerRegion[$job['p_postcode']][] = &$job; // add a reference of job to the region

        }

        $jobyIds = array_keys($jobsById); //ge job ids

        $regionCodes = array_keys($jobsPerRegion); // get postcodes

        if(!empty($jobsPerRegion)){
            $regions =  $this->system_model->getRegion_v2($regionCodes)->result_array();
        }

        foreach ($regions as $region) {
            for ($x = 0; $x < count($jobsPerRegion[$region['postcode']]); $x++) {
              $jobsPerRegion[$region['postcode']][$x]['region'] = $region;
            }
        }                
        
        if ( $export == 1 ) { //EXPORT         

            // file name
            $date_export = date('YmdHis');
            $filename = "approve_alarm_numbers_export_{$date_export}.csv";

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            // file creation 
            $csv_file = fopen('php://output', 'w');

            $region_header = $this->gherxlib->getDynamicRegion($this->config->item('country'));

            $csv_header = array("Job Type","Age","Service","Address",$region_header,"Preferred Alarm","Required # of Alarms");
            fputcsv($csv_file, $csv_header);
            
            foreach($jobs as $list_item){ 

                $csv_row = [];  
                
                $prop_address = $list_item['p_address_1']." ".$list_item['p_address_2'].", ".$list_item['p_address_3'];
                                               
                $csv_row[] = $list_item['j_type'];
                $csv_row[] = $list_item['age'];
                $csv_row[] = $list_item['ajt_type'];
                $csv_row[] = $prop_address;
                $csv_row[] = $list_item['region']['subregion_name'];
                $csv_row[] = $list_item['agency_name'];
                $csv_row[] = $list_item['pref_alarm_make'];
                $csv_row[] = $list_item['qld_new_leg_alarm_num'];                
                
                fputcsv($csv_file,$csv_row); 

            }
        
            fclose($csv_file); 
            exit; 

        }else{

            // main query
            $data['jobs'] = $jobs; 

            //Total rows
            $sel_query = "COUNT(j.`id`) AS jcount";
            $params = array(
                'sel_query' => $sel_query,
                'custom_where' => $custom_where,      

                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'job_status' => $job_status,
                'country_id' => $country_id,

                'state_filter' => $state_filter,            
                'postcodes' => $postcodes,            
                'preferred_alarm_id' => $preferred_alarm_id,
                'is_eo' => $show_is_eo,

                'join_table' => array('job_type','alarm_job_type','staff_accounts','preferred_alarm'),                     
                                
                'display_query' => 0
            );
            $query = $this->jobs_model->get_jobs($params);
            $total_rows = $query->row()->jcount;      

            //State filter
            $sel_query = "DISTINCT(p.`state`), p.`state`";
            $params = array(
                'sel_query' => $sel_query,
                'custom_where' => $custom_where,      

                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'job_status' => $job_status,
                'country_id' => $country_id,
                        
                'postcodes' => $postcodes,            
                'preferred_alarm_id' => $preferred_alarm_id,

                'join_table' => array('job_type','alarm_job_type','staff_accounts','preferred_alarm'),      

                'sort_list' => array(
                    array(
                        'order_by' => 'p.`state`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['state_filter_json'] = json_encode($params);  

            // Region Filter ( get distinct state )
            $sel_query = "p.`state`";
            $region_filter_arr = array(
                'sel_query' => $sel_query,
                'custom_where' => $custom_where,      

                'p_deleted' => 0,
                'a_status' => 'active',
                'del_job' => 0,
                'job_status' => $job_status,
                'country_id' => $country_id,
                           
                'postcodes' => $postcodes,            
                'preferred_alarm_id' => $preferred_alarm_id,

                'join_table' => array('job_type','alarm_job_type','staff_accounts','preferred_alarm'),      

                'sort_list' => array(
                    array(
                        'order_by' => 'p.`state`',
                        'sort' => 'ASC',
                    )
                ),
                'group_by' => 'p.`state`',
                'display_query' => 0
            );
            $data['region_filter_json'] = json_encode($region_filter_arr);

            // pagination url params
            $pagi_links_params_arr = array(
                'preferred_alarm_id' => $this->input->get_post('preferred_alarm_id'),
                'state_filter' => $state_filter,
                'sub_region_ms' => $this->input->get_post('sub_region_ms')                                   
            );

            // pagination link
            $pagi_link_params = "{$uri}/?".http_build_query($pagi_links_params_arr);

            // explort link
            $data['export_link'] = "{$uri}/?export=1&".http_build_query($pagi_links_params_arr);

            // pagination settings
            $config['page_query_string'] = TRUE;
            $config['query_string_segment'] = 'offset'; // rename offset variable
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

            $this->load->view('templates/inner_header', $data);
            $this->load->view($uri, $data);
            $this->load->view('templates/inner_footer', $data);

        }        
        
    }

    /**
     * Assign/move map tech via ajax
     */
    public function ajax_update_os_call_over(){

        $job_id_arr = $this->input->post('job_id_arr');
        $tech_id = $this->input->post('tech_id');
        $date = $this->system_model->formatDate($this->input->post('date'),'Y-m-d');
        $staff_id = $this->session->staff_id;        

        foreach( $job_id_arr as $job_id ){

            if( $job_id > 0 ){

                // get current job comments                
                $sql_sel_str = "
                SELECT `comments`
                FROM `jobs`               
                WHERE `id` = {$job_id}
                ";            
                $job_sql = $this->db->query($sql_sel_str);
                $job_row = $job_sql->row();

                // get tech name
                $tech_params = array(
                    'sel_query' => 'sa.FirstName, sa.LastName',
                    'staffID' => $tech_id
                );
                $tech = $this->system_model->getTech($tech_params)->row_array();
                $tech_name = $this->system_model->formatStaffName($tech['FirstName'],$tech['LastName']);

                if( $job_row->comments != '' ){ // combine                    
                    $log_details = "OS Call Over for {$this->input->post('date')}. Technician: {$tech_name} Previous comment = {$job_row->comments}";
                }else{                    
                    $log_details = "OS Call Over for {$this->input->post('date')}. Technician: {$tech_name}";
                }               

                // update job
                $sql_update_str = "
                UPDATE `jobs`
                SET 
                    `assigned_tech` = {$tech_id},
                    `date` = '{$date}',
                    `comments` = 'OS Call Over'
                WHERE `id` = {$job_id}
                ";            
                $this->db->query($sql_update_str);

                // insert on tech run
                $str_sql = $this->db->query("
                SELECT `tech_run_id` 
                FROM `tech_run`
                WHERE `assigned_tech` = {$tech_id}
                AND `date` = '{$date}'
                ");
                $str_row = $str_sql->row();

                if( $str_row->tech_run_id > 0 ){

                    // insert tech run rows to tech run
                    $insert_tech_run_str = "
                    INSERT INTO
                    `tech_run_rows` (
                        `tech_run_id`,
                        `row_id_type`,
                        `row_id`,
                        `sort_order_num`,
                        `dnd_sorted`,
                        `created_date`,
                        `status`
                    )
                    VALUES (
                        {$str_row->tech_run_id},
                        'job_id',
                        {$job_id},
                        999999,
                        0,
                        '".date('Y-m-d H:i:s')."',
                        1
                    )
                    ";
                    $this->db->query($insert_tech_run_str);

                }                
                    
    
                // insert job log                
                $log_params = array(
                    'title' => 63,  // Job Update
                    'details' => $log_details,
                    'display_in_vjd' => 1,
                    'created_by_staff' => $staff_id,
                    'job_id' => $job_id
                );
                $this->system_model->insert_log($log_params);
    
            } 

        }               
        
    }

    // used on TBB page to update jobs to on hold with start and end date
    public function save_multiple_jobs_on_hold(){

        $job_id_arr = array_filter($this->input->post('job_id_arr'));
        $on_hold_start = ( $this->input->get_post('on_hold_start') !='' )?$this->system_model->formatDate($this->input->get_post('on_hold_start')):null;
        $on_hold_end = ( $this->input->get_post('on_hold_end') !='' )?$this->system_model->formatDate($this->input->get_post('on_hold_end')):null;

        if( count($job_id_arr) > 0 && $on_hold_start !='' ){

            // UPDATE SET data
            $set_data = array(
                'start_date' => $on_hold_start,                
                'status' => 'On Hold'
            );

            if( $on_hold_end != '' ){
                $set_data['due_date'] = $on_hold_end;
            }
            
            $this->db->where_in('id', $job_id_arr);
            $this->db->update('jobs', $set_data);

        }        

    }

    public function updatePropertyJobsCount(){
        $jobs = $this->db->select('id')
        ->from('jobs')
        ->group_by('property_id')
        ->order_by('id', 'asc')
        ->get()
        ->result();

        foreach($jobs as $job){
            // UPDATE SET data
            $set_data = array(
                'property_jobs_count' => 1
            );
            $update = $this->db->where('id', $job->id)
                ->update('jobs', $set_data);
        }
    }

    public function deactivated_agencies_with_active_jobs(){

        $data['start_load_time'] = microtime(true);

        $data['title'] = "Deactivated Agencies with Active Jobs";

        $country_id = $this->config->item('country');

        $job_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $state_filter = $this->input->get_post('state_filter');
        $date_filter = ( $this->input->get_post('date_filter') !='' )?$this->system_model->formatDate($this->input->get_post('date_filter')):NULL;
        $search = $this->input->get_post('search_filter');
        $a_status_filter = ( $this->input->get_post('a_status_filter') != '' )?$this->input->get_post('a_status_filter'):'all';

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        
        $sel_query = "
        j.`id` AS jid,
        j.`status` AS j_status,
        j.`service` AS j_service,
        j.`created` AS j_created,
        j.`date` AS j_date,
        j.`comments` AS j_comments,
        j.`job_price` AS j_price,
        j.`job_type` AS j_type,
        
        p.`property_id` AS prop_id, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3,
        p.`state` AS p_state,
        p.`postcode` AS p_postcode,
        p.`comments` AS p_comments, 
        
        a.`agency_id` AS a_id,
        a.`agency_name` AS agency_name,
        a.`phone` AS a_phone,
        a.`address_1` AS a_address_1, 
        a.`address_2` AS a_address_2, 
        a.`address_3` AS a_address_3,
        a.`state` AS a_state,
        a.`postcode` AS a_postcode,
        a.`trust_account_software`,
        a.`tas_connected`,
        a.`status` AS a_status,
        aght.priority,
        apmd.abbreviation,
        
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type
        ";

        $a_status_filter_sql_str = null;
        if( $a_status_filter == 'all' ){
            $a_status_filter_sql_str = " AND a.`status` IN('deactivated','target')";
        }else{
            $a_status_filter_sql_str = " AND a.`status` = '{$a_status_filter}'";
        }

        $custom_where = "( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL ){$a_status_filter_sql_str}";
        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'p_deleted' => 0,            
            'a_deactivated_ts' => true,
            'del_job' => 0,
            'job_status' => 'not_completed_cancelled',
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type', 'agency_priority', 'agency_priority_marker_definition'),
            
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'date'=> $date_filter,
            'search' => $search,

            'limit' => $per_page,
            'offset' => $offset,
            'sort_list' => array(
                array(
                    'order_by' => 'j.urgent_job',
                    'sort' => 'DESC',
                ),
                array(
                    'order_by' => 'j.job_type',
                    'sort' => 'ASC',
                ),
                array(
                    'order_by' => 'p.address_3',
                    'sort' => 'ASC',
                ),
            ),
            'display_query' => 0,
        );
        $jobs_query = $this->jobs_model->get_jobs($params);
        $data['lists'] = $jobs_query;
        $total_rows = $jobs_query->num_rows();

        $data['sql_query'] = $this->db->last_query();

        // all rows
        $sel_query = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'deactivated',
            'del_job' => 0,
            'job_status' => 'not_completed_cancelled',
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type'),
            
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'date'=> $date_filter,
            'search' => $search,
        );
        $query = $this->jobs_model->get_jobs($params);
        //$total_rows = $query->row()->jcount;

        //Job type Filter
        $sel_query = "DISTINCT(j.`job_type`),
        `j.job_type`";
        $params = array(
        'sel_query' => $sel_query,
        'p_deleted' => 0,
        'a_status' => 'active',
        'del_job' => 1,
        'country_id' => $country_id,
        'join_table' => array('job_type'),
        'sort_list' => array(
            array(
                'order_by' => 'j.`job_type`',
                'sort' => 'ASC',
            ),
        ),
        );
        $data['job_type_filter_json'] = json_encode($params);

        //Services Filter
        $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
        $params = array(
        'sel_query' => $sel_query,
        'p_deleted' => 0,
        'a_status' => 'active',
        'del_job' => 1,
        'country_id' => $country_id,
        'join_table' => array('alarm_job_type'),
        'sort_list' => array(
            array(
                'order_by' => 'ajt.`type`',
                'sort' => 'ASC',
            ),
        ),
        );
        $data['service_filter_json'] = json_encode($params);

        //State filter
        $sel_query = "DISTINCT(p.`state`),
        p.`state`";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 1,
            'country_id' => $country_id,
            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['state_filter_json'] = json_encode($params);

        $pagi_links_params_arr = array(
            'job_type_filter' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'date_filter' => $date_filter,
            'search_filter' => $search
        );
        $pagi_link_params = '/jobs/deactivated_agencies_with_active_jobs/?'.http_build_query($pagi_links_params_arr);


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


        $this->load->view('templates/inner_header', $data);
        $this->load->view('jobs/deactivated_agencies_with_active_jobs', $data);
        $this->load->view('templates/inner_footer', $data);
    }


    public function image_booked()
    {

        $data['start_load_time'] = microtime(true);

        $data['title'] = "Image - Booked Jobs";
        $uri = '/jobs/image_booked';
        $data['uri'] = $uri;

        $job_status = 'Booked';

        $country_id = $this->config->item('country');

        $job_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $state_filter = $this->input->get_post('state_filter');
        $agency_filter = $this->input->get_post('agency_filter');
        $date_filter = ($this->input->get_post('date_filter')!="")?$this->system_model->formatDate($this->input->get_post('date_filter')):NULL;
        $date_filter_from = ($this->input->get_post('date_filter_from')!="")?$this->system_model->formatDate($this->input->get_post('date_filter_from')):NULL;
        $date_filter_to = ($this->input->get_post('date_filter_to')!="")?$this->system_model->formatDate($this->input->get_post('date_filter_to')):NULL;
        $search = $this->input->get_post('search_filter');
        $tech_filter = $this->input->get_post('tech_filter');

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);
        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }  
        
        // pagination
        $per_page = $this->config->item('pagi_per_page');
        //$per_page = 5;
        $offset = $this->input->get_post('offset');

        $sel_query = "
        j.`id` AS jid,
        j.`status` AS j_status,
        j.`service` AS j_service,
        j.`created` AS j_created,
        j.`date` AS j_date,
        j.`comments` AS j_comments,
        j.`job_price` AS j_price,
        j.`job_type` AS j_type,
        j.`key_access_required`,
        
        p.`property_id` AS prop_id, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3,
        p.`state` AS p_state,
        p.`postcode` AS p_postcode,
        p.`comments` AS p_comments, 
        
        a.`agency_id` AS a_id,
        a.`agency_name` AS agency_name,
        a.`phone` AS a_phone,
        a.`address_1` AS a_address_1, 
        a.`address_2` AS a_address_2, 
        a.`address_3` AS a_address_3,
        a.`state` AS a_state,
        a.`postcode` AS a_postcode,
        a.`trust_account_software`,
        a.`tas_connected`,
        aght.priority,
        
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type
        ";

        //echo $sel_query;
        //exit();

        $fg = 40; // Image
        $custom_where = " atbl.`id` IS NULL AND a.`franchise_groups_id` = {$fg} ";

        // join airtable booked
        $airtable_booked_join = array(
            'join_table' => '`airtable` AS atbl',
            'join_on' => 'j.`id` = atbl.`job_id` AND atbl.`booked` = 1',
            'join_type' => 'left'
        );

        // paginate
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type','staff_accounts', 'agency_priority'),

            'custom_joins_arr' => array(
                $airtable_booked_join
            ),

            'custom_where'=> $custom_where,
            
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'agency_filter' => $agency_filter,
            'region_filter' => $region_filter,
            'date'=>$date_filter,
            'date_from'=>$date_filter_from,
            'date_to'=>$date_filter_to,
            'search' => $search,
            'postcodes' => $postcodes,
            'tech_filter'=> $tech_filter,
            
            'country_id' => $country_id,

            'limit' => $per_page,
            'offset' => $offset,

            'sort_list' => array(
                array(
                    'order_by' => 'j.created',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['lists'] = $this->jobs_model->get_jobs($params);
        $data['page_query'] = $this->db->last_query();
        //exit();

        // all rows
        $sel_query = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
            'join_table' => array('job_type','alarm_job_type','staff_accounts'),

            'custom_joins_arr' => array(
                $airtable_booked_join
            ),

            'custom_where'=> $custom_where,

            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'agency_filter' => $agency_filter,
            'region_filter' => $region_filter,
            'date' => $date_filter,
            'date_from' => $date_filter_from,
            'date_to'=>$date_filter_to,
            'search' => $search,
            'postcodes' => $postcodes,
            'tech_filter' => $tech_filter
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;
        
        
        //Agency filter
        $sel_query = "DISTINCT(a.`agency_id`), a.`agency_name`";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,
            
            'job_status' => $job_status,

            'custom_joins_arr' => array(
                $airtable_booked_join
            ),

            'custom_where'=> $custom_where,
            
            'sort_list' => array(   
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                )               
            ),
            'display_query' => 0
        );
        $data['agency_filter_json'] = json_encode($params);

        //Job type Filter
        $sel_query = "DISTINCT(j.`job_type`), `j.job_type`";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,
            
            'job_status' => $job_status,

            'custom_joins_arr' => array(
                $airtable_booked_join
            ),

            'custom_where'=> $custom_where,
            
            'sort_list' => array(
                array(
                    'order_by' => 'j.`job_type`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['job_type_filter_json'] = json_encode($params);

        //Services Filter
        $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,
            
            'job_status' => $job_status,

            'custom_joins_arr' => array(
                $airtable_booked_join
            ),

            'custom_where'=> $custom_where,
            
            'join_table' => array('alarm_job_type','staff_accounts'),
            'sort_list' => array(
                array(
                    'order_by' => 'ajt.`type`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['service_filter_json'] = json_encode($params);            

        // state filter
        $sel_query = "p.`state`";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',         
            'country_id' => $country_id,
            
            'job_status' => $job_status,

            'custom_joins_arr' => array(
                $airtable_booked_join
            ),

            'custom_where'=> $custom_where,
            
            'join_table' => array('job_type','alarm_job_type','staff_accounts'),
            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                )
            ),
            'group_by' => 'p.`state`',
            'display_query' => 0
        );
        $data['state_filter_json'] = json_encode($params);  
        
        
        // Region Filter ( get distinct state )
        $sel_query = "p.`state`";
        $region_filter_arr = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            
            'job_status' => $job_status,
            
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type','staff_accounts'),

            'custom_joins_arr' => array(
                $airtable_booked_join
            ),

            'custom_where'=> $custom_where,

            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                )
            ),
            'group_by' => 'p.`state`',
            'display_query' => 0
        );
        $data['region_filter_json'] = json_encode($region_filter_arr);
        
        //tech filter
        $sel_query = "DISTINCT(j.`assigned_tech`),sa.`StaffID`,sa.`FirstName`,sa.`LastName`";
        $tech_filter_params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            
            'job_status' => $job_status,

            'custom_joins_arr' => array(
                $airtable_booked_join
            ),

            'custom_where'=> $custom_where,
            
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type','staff_accounts'),

            'sort_list' => array(
                array(
                    'order_by' => 'sa.`FirstName`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['tech_filter'] = $this->jobs_model->get_jobs($tech_filter_params);
        //print_r($data['tech_filter']);
        //exit();

        $pagi_links_params_arr = array(
            'agency_filter' => $agency_filter,
            'job_type_filter' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' =>  $state_filter,
            'date_filter' => $date_filter,
            'date_filter_from' => $date_filter_from,
            'date_filter_to' => $date_filter_to,
            'search_filter' => $search,
            'sub_region_ms' => $sub_region_ms,
            'tech_filter' => $tech_filter
        );
        $pagi_link_params = $uri.'/?'.http_build_query($pagi_links_params_arr);


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


        $this->load->view('templates/inner_header', $data);
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);
    }


    public function image_completed()
    {

        $data['start_load_time'] = microtime(true);

        $data['title'] = "Image - Completed Jobs";
        $uri = '/jobs/image_completed';
        $data['uri'] = $uri;

        $job_status = 'Completed';

        $country_id = $this->config->item('country');

        $job_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $state_filter = $this->input->get_post('state_filter');
        $agency_filter = $this->input->get_post('agency_filter');
        $date_filter = ($this->input->get_post('date_filter')!="")?$this->system_model->formatDate($this->input->get_post('date_filter')):NULL;
        $date_filter_from = ($this->input->get_post('date_filter_from')!="")?$this->system_model->formatDate($this->input->get_post('date_filter_from')):NULL;
        $date_filter_to = ($this->input->get_post('date_filter_to')!="")?$this->system_model->formatDate($this->input->get_post('date_filter_to')):NULL;
        $search = $this->input->get_post('search_filter');
        $tech_filter = $this->input->get_post('tech_filter');

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);
        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }  

        $order_by = ( $this->input->get_post('order_by') !='' ) ? $this->input->get_post('order_by') : 'j.`created`';
        $sort = ( $this->input->get_post('sort') != '' ) ? $this->input->get_post('sort') : 'asc';
        $filter_orderby_columns = $this->input->get_post('order_by');
        
        // pagination
        $per_page = $this->config->item('pagi_per_page');
        //$per_page = 5;
        $offset = $this->input->get_post('offset');

        $sel_query = "
        j.`id` AS jid,
        j.`status` AS j_status,
        j.`service` AS j_service,
        j.`created` AS j_created,
        j.`date` AS j_date,
        j.`comments` AS j_comments,
        j.`job_price` AS j_price,
        j.`job_type` AS j_type,
        j.`prop_comp_with_state_leg`,
        
        p.`property_id` AS prop_id, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3,
        p.`state` AS p_state,
        p.`postcode` AS p_postcode,
        p.`comments` AS p_comments, 
        p.`prop_upgraded_to_ic_sa`,
        
        a.`agency_id` AS a_id,
        a.`agency_name` AS agency_name,
        a.`phone` AS a_phone,
        a.`address_1` AS a_address_1, 
        a.`address_2` AS a_address_2, 
        a.`address_3` AS a_address_3,
        a.`state` AS a_state,
        a.`postcode` AS a_postcode,
        a.`trust_account_software`,
        a.`tas_connected`,
        aght.priority,
        apmd.abbreviation,
        
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type
        ";

        //echo $sel_query;
        //exit();

        $fg = 40; // Image
        $custom_where = " atbl.`id` IS NULL AND a.`franchise_groups_id` = {$fg} AND j.`assigned_tech` NOT IN(1,2) ";

        // join airtable completed
        $airtable_completed_join = array(
            'join_table' => '`airtable` AS atbl',
            'join_on' => 'j.`id` = atbl.`job_id` AND atbl.`completed` = 1',
            'join_type' => 'left'
        );

        if ($filter_orderby_columns == 'created_date') {
            $sort_list = array(
                array(
                    'order_by' => 'j.date',
                    'sort' => $sort
                )
            );
        } else if($filter_orderby_columns == 'compliant') {
            $sort_list = array(
                array(
                    'order_by' => 'j.prop_comp_with_state_leg',
                    'sort' => $sort,
                ),
                array(
                    'order_by' => 'p.prop_upgraded_to_ic_sa',
                    'sort' => $sort,
                )
            );
        } else {
            $sort_list = array(
                array(
                    'order_by' => 'j.prop_comp_with_state_leg',
                    'sort' => 'DESC',
                ),
                array(
                    'order_by' => 'p.prop_upgraded_to_ic_sa',
                    'sort' => 'DESC',
                ),
                array(
                    'order_by' => 'j.date',
                    'sort' => 'ASC',
                )
            );
           
        }
        
        // paginate
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            // 'job_status' => $job_status, --- /****  use the job_status_arr ****/
            'job_status_arr'    => array('completed', 'merged_certificates'),
            'join_table' => array('job_type','alarm_job_type','staff_accounts', 'agency_priority', 'agency_priority_marker_definition'),

            'custom_joins_arr' => array(
                $airtable_completed_join
            ),

            'custom_where'=> $custom_where,
            
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'agency_filter' => $agency_filter,
            'region_filter' => $region_filter,
            'date'=>$date_filter,
            'date_from'=>$date_filter_from,
            'date_to'=>$date_filter_to,
            'search' => $search,
            'postcodes' => $postcodes,
            'tech_filter'=> $tech_filter,
            
            'country_id' => $country_id,

            'sort_list' => $sort_list
        );
        $data['lists'] = $this->jobs_model->get_jobs($params);
        $data['page_query'] = $this->db->last_query();

        // all rows
        $sel_query = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            // 'job_status' => $job_status,
            'job_status_arr'    => array('completed', 'merged_certificates'),
            'join_table' => array('job_type','alarm_job_type','staff_accounts', 'agency_priority', 'agency_priority_marker_definition'),

            'custom_joins_arr' => array(
                $airtable_completed_join
            ),

            'custom_where'=> $custom_where,

            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'agency_filter' => $agency_filter,
            'region_filter' => $region_filter,
            'date' => $date_filter,
            'date_from' => $date_filter_from,
            'date_to'=>$date_filter_to,
            'search' => $search,
            'postcodes' => $postcodes,
            'tech_filter' => $tech_filter,

            'sort_list' => $sort_list
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;
        
        
        //Agency filter
        $sel_query = "DISTINCT(a.`agency_id`), a.`agency_name`";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,
            
            'job_status' => $job_status,

            'custom_joins_arr' => array(
                $airtable_completed_join
            ),

            'custom_where'=> $custom_where,
            
            'sort_list' => array(   
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                )               
            ),
            'display_query' => 0
        );
        $data['agency_filter_json'] = json_encode($params);

        //Job type Filter
        $sel_query = "DISTINCT(j.`job_type`), `j.job_type`";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,
            
            'job_status' => $job_status,

            'custom_joins_arr' => array(
                $airtable_completed_join
            ),

            'custom_where'=> $custom_where,
            
            'sort_list' => array(
                array(
                    'order_by' => 'j.`job_type`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['job_type_filter_json'] = json_encode($params);

        //Services Filter
        $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,
            
            'job_status' => $job_status,

            'custom_joins_arr' => array(
                $airtable_completed_join
            ),

            'custom_where'=> $custom_where,
            
            'join_table' => array('alarm_job_type','staff_accounts'),
            'sort_list' => array(
                array(
                    'order_by' => 'ajt.`type`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['service_filter_json'] = json_encode($params);            

        // state filter
        $sel_query = "p.`state`";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',         
            'country_id' => $country_id,
            
            'job_status' => $job_status,

            'custom_joins_arr' => array(
                $airtable_completed_join
            ),

            'custom_where'=> $custom_where,
            
            'join_table' => array('job_type','alarm_job_type','staff_accounts'),
            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                )
            ),
            'group_by' => 'p.`state`',
            'display_query' => 0
        );
        $data['state_filter_json'] = json_encode($params);  
        
        
        // Region Filter ( get distinct state )
        $sel_query = "p.`state`";
        $region_filter_arr = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            
            'job_status' => $job_status,
            
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type','staff_accounts'),

            'custom_joins_arr' => array(
                $airtable_completed_join
            ),

            'custom_where'=> $custom_where,

            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                )
            ),
            'group_by' => 'p.`state`',
            'display_query' => 0
        );
        $data['region_filter_json'] = json_encode($region_filter_arr);
        
        //tech filter
        $sel_query = "DISTINCT(j.`assigned_tech`),sa.`StaffID`,sa.`FirstName`,sa.`LastName`";
        $tech_filter_params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            
            'job_status' => $job_status,

            'custom_joins_arr' => array(
                $airtable_completed_join
            ),

            'custom_where'=> $custom_where,
            
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type','staff_accounts'),

            'sort_list' => array(
                array(
                    'order_by' => 'sa.`FirstName`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['tech_filter'] = $this->jobs_model->get_jobs($tech_filter_params);
        //print_r($data['tech_filter']);
        //exit();

        $pagi_links_params_arr = array(
            'agency_filter' => $agency_filter,
            'job_type_filter' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' =>  $state_filter,
            'date_filter' => $date_filter,
            'date_filter_from' => $date_filter_from,
            'date_filter_to' => $date_filter_to,
            'search_filter' => $search,
            'sub_region_ms' => $sub_region_ms,
            'tech_filter' => $tech_filter
        );
        $pagi_link_params = $uri.'/?'.http_build_query($pagi_links_params_arr);

        $data['pagi_links_params_arr'] = $pagi_links_params_arr;

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

        $data['toggle_sort'] = ( $sort == 'asc' ) ? 'desc' : 'asc';


        $this->load->view('templates/inner_header', $data);
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);
    }


    public function image_missed_jobs()
    {

        $data['start_load_time'] = microtime(true);

        $data['title'] = "Image - Missed Jobs";
        $uri = '/jobs/image_missed_jobs';
        $data['uri'] = $uri;

        $country_id = $this->config->item('country');

        $job_filter = $this->input->get_post('job_type_filter');
        $service_filter = $this->input->get_post('service_filter');
        $state_filter = $this->input->get_post('state_filter');
        $agency_filter = $this->input->get_post('agency_filter');
        $date_filter = ($this->input->get_post('date_filter')!="")?$this->system_model->formatDate($this->input->get_post('date_filter')):NULL;
        $date_filter_from = ($this->input->get_post('date_filter_from')!="")?$this->system_model->formatDate($this->input->get_post('date_filter_from')):NULL;
        $date_filter_to = ($this->input->get_post('date_filter_to')!="")?$this->system_model->formatDate($this->input->get_post('date_filter_to')):NULL;
        $search = $this->input->get_post('search_filter');
        $tech_filter = $this->input->get_post('tech_filter');

        $state_ms = $this->input->get_post('state_ms');
        $data['state_ms_json'] = json_encode($state_ms);
        $region_ms = $this->input->get_post('region_ms');
        $data['region_ms_json'] = json_encode($region_ms);
        $sub_region_ms = $this->input->get_post('sub_region_ms');
        $data['sub_region_ms_json'] = json_encode($sub_region_ms);
        if( !empty($sub_region_ms) ){
            $postcodes = $this->system_model->getPostCodeViaSubRegion($sub_region_ms);
        }  
        
        // pagination
        $per_page = $this->config->item('pagi_per_page');
        //$per_page = 5;
        $offset = $this->input->get_post('offset');

        $sel_query = "
        j.`id` AS jid,
        j.`status` AS j_status,
        j.`service` AS j_service,
        j.`created` AS j_created,
        j.`date` AS j_date,
        j.`comments` AS j_comments,
        j.`job_price` AS j_price,
        j.`job_type` AS j_type,

        jr.`name` AS jr_name,
        
        p.`property_id` AS prop_id, 
        p.`address_1` AS p_address_1, 
        p.`address_2` AS p_address_2, 
        p.`address_3` AS p_address_3,
        p.`state` AS p_state,
        p.`postcode` AS p_postcode,
        p.`comments` AS p_comments, 
        
        a.`agency_id` AS a_id,
        a.`agency_name` AS agency_name,
        a.`phone` AS a_phone,
        a.`address_1` AS a_address_1, 
        a.`address_2` AS a_address_2, 
        a.`address_3` AS a_address_3,
        a.`state` AS a_state,
        a.`postcode` AS a_postcode,
        a.`trust_account_software`,
        a.`tas_connected`,
        aght.priority,
        apmd.abbreviation,
        
        ajt.`id` AS ajt_id,
        ajt.`type` AS ajt_type
        ";

        //echo $sel_query;
        //exit();

        $fg = 40; // Image
        $custom_where = " atbl.`id` IS NULL AND a.`franchise_groups_id` = {$fg} AND j.`job_reason_id` > 0 ";

        // join airtable booked
        $airtable_booked_join = array(
            'join_table' => '`airtable` AS atbl',
            'join_on' => 'j.`id` = atbl.`job_id` AND atbl.`missed` = 1',
            'join_type' => 'left'
        );

        // paginate
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'join_table' => array('job_type','alarm_job_type','staff_accounts','job_reason','agency_priority', 'agency_priority_marker_definition'),

            'custom_joins_arr' => array(
                $airtable_booked_join
            ),

            'custom_where'=> $custom_where,
            
            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'agency_filter' => $agency_filter,
            'region_filter' => $region_filter,
            'date'=>$date_filter,
            'date_from'=>$date_filter_from,
            'date_to'=>$date_filter_to,
            'search' => $search,
            'postcodes' => $postcodes,
            'tech_filter'=> $tech_filter,
            
            'country_id' => $country_id,

            'limit' => $per_page,
            'offset' => $offset,

            'sort_list' => array(
                array(
                    'order_by' => 'j.created',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['lists'] = $this->jobs_model->get_jobs($params);
        $data['page_query'] = $this->db->last_query();
        //exit();

        // all rows
        $sel_query = "COUNT(j.`id`) AS jcount";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type','staff_accounts'),

            'custom_joins_arr' => array(
                $airtable_booked_join
            ),

            'custom_where'=> $custom_where,

            'job_type' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' => $state_filter,
            'agency_filter' => $agency_filter,
            'region_filter' => $region_filter,
            'date' => $date_filter,
            'date_from' => $date_filter_from,
            'date_to'=>$date_filter_to,
            'search' => $search,
            'postcodes' => $postcodes,
            'tech_filter' => $tech_filter
        );
        $query = $this->jobs_model->get_jobs($params);
        $total_rows = $query->row()->jcount;
        
        
        //Agency filter
        $sel_query = "DISTINCT(a.`agency_id`), a.`agency_name`";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,            

            'custom_joins_arr' => array(
                $airtable_booked_join
            ),

            'custom_where'=> $custom_where,
            
            'sort_list' => array(   
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                )               
            ),
            'display_query' => 0
        );
        $data['agency_filter_json'] = json_encode($params);

        //Job type Filter
        $sel_query = "DISTINCT(j.`job_type`), `j.job_type`";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,

            'custom_joins_arr' => array(
                $airtable_booked_join
            ),

            'custom_where'=> $custom_where,
            
            'sort_list' => array(
                array(
                    'order_by' => 'j.`job_type`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['job_type_filter_json'] = json_encode($params);

        //Services Filter
        $sel_query = "DISTINCT(ajt.`id`), ajt.`type`";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            'country_id' => $country_id,

            'custom_joins_arr' => array(
                $airtable_booked_join
            ),

            'custom_where'=> $custom_where,
            
            'join_table' => array('alarm_job_type','staff_accounts'),
            'sort_list' => array(
                array(
                    'order_by' => 'ajt.`type`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['service_filter_json'] = json_encode($params);            

        // state filter
        $sel_query = "p.`state`";
        $params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',         
            'country_id' => $country_id,

            'custom_joins_arr' => array(
                $airtable_booked_join
            ),

            'custom_where'=> $custom_where,
            
            'join_table' => array('job_type','alarm_job_type','staff_accounts'),
            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                )
            ),
            'group_by' => 'p.`state`',
            'display_query' => 0
        );
        $data['state_filter_json'] = json_encode($params);  
        
        
        // Region Filter ( get distinct state )
        $sel_query = "p.`state`";
        $region_filter_arr = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',
            
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type','staff_accounts'),

            'custom_joins_arr' => array(
                $airtable_booked_join
            ),

            'custom_where'=> $custom_where,

            'sort_list' => array(
                array(
                    'order_by' => 'p.`state`',
                    'sort' => 'ASC',
                )
            ),
            'group_by' => 'p.`state`',
            'display_query' => 0
        );
        $data['region_filter_json'] = json_encode($region_filter_arr);
        
        //tech filter
        $sel_query = "DISTINCT(j.`assigned_tech`),sa.`StaffID`,sa.`FirstName`,sa.`LastName`";
        $tech_filter_params = array(
            'sel_query' => $sel_query,
            'del_job' => 0,
            'p_deleted' => 0,
            'a_status' => 'active',

            'custom_joins_arr' => array(
                $airtable_booked_join
            ),

            'custom_where'=> $custom_where,
            
            'country_id' => $country_id,
            'join_table' => array('job_type','alarm_job_type','staff_accounts'),

            'sort_list' => array(
                array(
                    'order_by' => 'sa.`FirstName`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $data['tech_filter'] = $this->jobs_model->get_jobs($tech_filter_params);
        //print_r($data['tech_filter']);
        //exit();

        $pagi_links_params_arr = array(
            'agency_filter' => $agency_filter,
            'job_type_filter' => $job_filter,
            'service_filter' => $service_filter,
            'state_filter' =>  $state_filter,
            'date_filter' => $date_filter,
            'date_filter_from' => $date_filter_from,
            'date_filter_to' => $date_filter_to,
            'search_filter' => $search,
            'sub_region_ms' => $sub_region_ms,
            'tech_filter' => $tech_filter
        );
        $pagi_link_params = $uri.'/?'.http_build_query($pagi_links_params_arr);


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


        $this->load->view('templates/inner_header', $data);
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);
    }


    public function ajax_save_in_airtable(){

        $job_id = $this->input->get_post('job_id');
        $ticked_from = $this->input->get_post('ticked_from');

        $country_id = $this->config->item('country');        
        $staff_id = $this->session->staff_id;    
        
        // get staff name
        $staff_sql = $this->db->query("
        SELECT `FirstName`, `LastName` 
        FROM `staff_accounts`
        WHERE `StaffID` = {$staff_id}
        ");
        $staff_row = $staff_sql->row();
        $staff_name = $this->system_model->formatStaffName($staff_row->FirstName,$staff_row->LastName);

        if( $job_id > 0 ){

            $insert_data = array(
                'job_id' => $job_id
            ); 

            switch( $ticked_from ){

                case 'booked':
                    $insert_data['booked'] = 1;
                    $log_details = "{$staff_name} has updated this booking in Airtable.";
                break;

                case 'completed':
                    $insert_data['completed'] = 1;
                    $log_details = "{$staff_name} has updated this completed job in Airtable";
                break;

                case 'missed':
                    $insert_data['missed'] = 1;
                    $log_details = "{$staff_name} has updated this missed job in Airtable";
                break;
                    
                case 'on_hold':
                    $insert_data['on_hold'] = 1;
                    $log_details = "{$staff_name} has updated this on-hold job in Airtable";
                    break;

            }
                      
            $this->db->insert('airtable', $insert_data);   
            
            //Insert log            
            $log_params = array(
                'title' => 63, // Job Update
                'details' => $log_details,
                'display_in_vjd' => 1,
                'created_by_staff' => $staff_id,
                'job_id' => $job_id
            );
            $this->system_model->insert_log($log_params);

        }        

    }

}