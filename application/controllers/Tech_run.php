<?php

class Tech_run extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->database();
        $this->load->library('pagination');
        $this->load->helper('url');
        $this->load->model('tech_model');
        $this->load->model('vehicles_model');
        $this->load->model('tech_run_model');
        $this->load->library('email');
    }


    public function run_sheet_admin(){

        $staff_class_id = $this->system_model->getStaffClassID();
        $today = date("Y-m-d");

        if($staff_class_id==6){
            redirect('/home/index_tech');
        }

        $data['start_load_time'] = microtime(true);
        $country_id = $this->config->item('country');


        $data['tr_id'] = $this->uri->segment(3); //pass data
        $tr_id = $this->uri->segment(3); 

        if(!$tr_id && empty($tr_id)){
            redirect('/home/index_tech');
        }

        //get techrun by techrun id
        $tr_query = $this->db->select('*')->from('tech_run')->where('tech_run_id', $tr_id)->get();
        $data['tr'] = $tr_query->row_array();
        $tr = $tr_query->row_array();

        $data['hasTechRun'] = ( $tr_query->num_rows()>0 )?true:false;

        $data['tech_id'] = $tr['assigned_tech']; // pass data
        $tech_id = $tr['assigned_tech'];

        $data['day'] = date("d",strtotime($tr['date'])); //pass data
        $data['month'] = date("m",strtotime($tr['date'])); //pass data
        $data['year'] = date("Y",strtotime($tr['date'])); //pass data
        $date = $tr['date'];
        $data['date'] = $tr['date']; //pass data
        $sub_regions = $tr['sub_regions'];
        $data['sub_regions'] = $tr['sub_regions'];

        //get accomodation by tech run start
        $accom_query = $this->db->select('*')->from('accomodation')->where( array('accomodation_id'=> $tr['start'], 'country_id'=> $country_id) )->get();
        $accom_row = $accom_query->row_array();
        $data['accom_name'] = $accom_row['name'];
        $data['start_agency_address'] = $accom_row['address'];


        //get accomodation by tech run end
        $accom_query_end = $this->db->select('*')->from('accomodation')->where( array('accomodation_id'=> $tr['end'], 'country_id'=> $country_id) )->get();
        $end_acco = $accom_query_end->row_array();
        $data['end_accom_name'] = $end_acco['name'];
        $data['end_agency_address'] = $end_acco['address'];

        //get vehicle by staff_id
        $v_query = $this->tech_model->getVehicleByTechId($tech_id)->row_array();
        $data['v'] = $v_query; //pass data to view
        $vehicle_id = $v_query['vehicles_id'];

        //get kms by vehicle id
        $data['kms'] = $this->tech_model->getKmsByVehicleId($vehicle_id)->row_array();

        //get staff
        $staff_params = array(
            'sel_query' => "sa.StaffID, sa.FirstName, sa.LastName, sa.is_electrician, sa.ContactNumber, sa.ClassID",
            'staff_id' => $tech_id
        );
        $staff_query = $this->gherxlib->getStaffInfo($staff_params);
        $data['staff'] = $staff_query->row_array(); //pass data
        $staff = $staff_query->row_array();


        //get login user class_id
        $data['staff_classID'] = $this->db->select('ClassID')->from('staff_accounts')->where('StaffID', $this->session->staff_id)->get()->row()->ClassID;


        $data['isElectrician'] = ( $staff['is_electrician']==1 )?true:false;
        $data['tech_name'] = "{$staff['FirstName']} {$staff['LastName']}";
        $data['tech_mob1'] = $staff['ContactNumber'];

        //get tech stock
        $ts_query = $this->db->select('*')->from('tech_stock')->where('staff_id', $staff['StaffID'])->order_by('date','DESC')->limit(1)->get();
        $data['ts'] = $ts_query->row_array(); //pass data



        //get techrunrows
        $tr_sel = "
            trr.`tech_run_rows_id`,
            trr.`row_id_type`,
            trr.`row_id`,
            trr.`hidden`,
            trr.`dnd_sorted`,
            trr.`highlight_color`,
            
            trr_hc.`tech_run_row_color_id`,
            trr_hc.`hex`,
            
            j.`id` AS jid,
            j.`precomp_jobs_moved_to_booked`,
            j.`completed_timestamp`,		

            p.`property_id`,
            p.`qld_new_leg_alarm_num`,
            p.`preferred_alarm_id`,

            a.`agency_id`,
            a.`allow_upfront_billing`
        ";
        $tr_params = array(
            'sel_query' => $tr_sel,
            'sort_list' => array(
                array(
                    'order_by' => 'trr.sort_order_num',
                    'sort' => 'ASC'
                )
            ),
            'display_only_booked' => 1,
            'admins_only' => 1
        );
        $data['jr_list2'] = $this->tech_model->getTechRunRows($tr_id, $country_id, $tr_params);


        // get tech break
        $data['tb_sql'] = $this->db->query("
        SELECT *
        FROM `tech_breaks`
        WHERE CAST( `tech_break_start` AS Date ) = '{$today}'
        AND `tech_break_taken` = 1
        AND `tech_id` = {$tech_id}
        ");     


        $data['title'] =  $staff['FirstName']." ".$staff['LastName'];
        $this->load->view('templates/inner_header', $data);
        $this->load->view('tech_run/tech_day_schedule', $data);
        $this->load->view('templates/inner_footer', $data);

    }


    public function ajax_update_job_time(){

        $job_arr = $this->input->post('job_id');
        $job_time = $this->input->post('job_time');

        if(!empty($job_arr)){

            foreach($job_arr as $job_id){

                // update job time of day
                $update_data = array(
                    'time_of_day' => $job_time
                );
                $this->db->where('id', $job_id);
                $this->db->update('jobs', $update_data);
    
            }

        }
        

        echo json_encode($update_data);

    }

    public function ajax_rebook_script(){

        $json_data['status'] = false;
        $job_id_arr = $this->input->post('job_id');
        $is_240v = $this->input->post('is_240v');
        $isDHA = $this->input->post('isDHA');
        $staff_id = $this->session->staff_id;

        if(!empty($job_id_arr)){

            foreach($job_id_arr as $job_id){
                
                // get job type
                $job_sql = $this->db->select('key_access_required,status,job_reason_id,assigned_tech,date,key_access_details,job_type,comments')->from('jobs')->where('id', $job_id)->get();
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
                $jl_ct_txt = '';
                $log_title = 35;
                
                if( $isDHA==1 ){ // DHA
                    $status_txt = 'DHA';
                    $jl_ct_txt = '(DHA)';
                    $log_title = 33;
                }else{
                    $status_txt = 'To Be Booked';
                }
                
                if( $is_240v==1 ){ // 240v rebook
                    $job_type_txt = " `is_eo` = 1, ";  
                    $jl_ct_txt = '(240v)';
                    $log_title = 34;

                    // this needs to logged like it was updated to 240v rebook
                    $this->system_model->insert_job_markers($job_id,'240v Rebook');
                    $update_job_comments = " `comments` = '240v Rebook Job - {$j['comments']}', ";
                }else{
                    $job_type_txt = '';
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
                

                if($is_240v==1){
                    
                    // insert job log
                    $jl_msg = "Job is marked as Electrician Only(EO)";
                    $log_params = array(
                        'title' => $log_title,  //rebook title
                        'details' => $jl_msg,
                        'display_in_vjd' => 1,
                        'created_by_staff' => $this->session->staff_id,
                        'job_id' => $job_id
                    );
                    $this->system_model->insert_log($log_params);

                }else{

                    $jl_msg = "Job status updated from <strong>{$j['status']}</strong> to <strong>{$status_txt}</strong>";
                     // insert job log
                    $log_params = array(
                        'title' => $log_title,  
                        'details' => $jl_msg,
                        'display_in_vjd' => 1,
                        'created_by_staff' => $this->session->staff_id,
                        'job_id' => $job_id
                    );
                    $this->system_model->insert_log($log_params);

                }                     

            }

            $json_data['status'] = true;

        }

        echo json_encode($json_data);
        

    }


    public function ajax_add_kms(){
        
        $kms = $this->input->post('kms');
        $vehicles_id = $this->input->post('vehicles_id');
        $roof_ladder_secured = $this->input->post('roof_ladder_secured');
        $json_data['status'] = false;

        if($kms != 0 && !empty($vehicles_id)){
        //if(!empty($vehicles_id)){
            $add_data = array(
                'vehicles_id' => $vehicles_id,
                'kms' => $kms,
                'kms_updated' => date("Y-m-d H:i:s"),
                'roof_ladder_secured' => $roof_ladder_secured
            );
            $this->db->insert('kms', $add_data);
            $this->db->limit(1);

            if($this->db->affected_rows()>0){
                $json_data['status'] = true;
            }

        }
        
        echo json_encode($json_data);

    }


    public function run_sheet(){

        //$data['start_load_time'] = microtime(true);
        $country_id = $this->config->item('country');
        $today = date("Y-m-d");


        $data['tr_id'] = $this->uri->segment(3); //pass data
        $tr_id = $this->uri->segment(3); 

        /*if(!$tr_id && empty($tr_id)){
            redirect('/home/index_tech');
        }*/

        //get techrun by techrun id
        $tr_query = $this->db->select('*')->from('tech_run')->where('tech_run_id', $tr_id)->get();
        $data['tr'] = $tr_query->row_array();
        $tr = $tr_query->row_array();

        $data['hasTechRun'] = ( $tr_query->num_rows()>0 )?true:false;

        $data['tech_id'] = $tr['assigned_tech']; // pass data
        $tech_id = $tr['assigned_tech'];


        $data['day'] = date("d",strtotime($tr['date'])); //pass data
        $data['month'] = date("m",strtotime($tr['date'])); //pass data
        $data['year'] = date("Y",strtotime($tr['date'])); //pass data
        $date = $tr['date'];
        $data['date'] = $tr['date']; //pass data
        $sub_regions = $tr['sub_regions'];
        $data['sub_regions'] = $tr['sub_regions'];

        $staff_class = $this->system_model->getStaffClassID();
        if($staff_class==6){
            $tech_id = $this->session->staff_id;
            $data['tech_id'] = $this->session->staff_id;

            $data['day'] = date("d");
            $data['month'] = date("m");
            $data['year'] = date("y");
        }

        //get accomodation by tech run start
        $accom_query = $this->db->select('*')->from('accomodation')->where( array('accomodation_id'=> $tr['start'], 'country_id'=> $country_id) )->get();
        $accom_row = $accom_query->row_array();
        $data['accom_name'] = $accom_row['name'];
        $data['start_agency_address'] = $accom_row['address'];


        //get accomodation by tech run end
        $accom_query_end = $this->db->select('*')->from('accomodation')->where( array('accomodation_id'=> $tr['end'], 'country_id'=> $country_id) )->get();
        $end_acco = $accom_query_end->row_array();
        $data['end_accom_name'] = $end_acco['name'];
        $data['end_agency_address'] = $end_acco['address'];

        //get vehicle by staff_id
        $v_query = $this->tech_model->getVehicleByTechId($tech_id)->row_array();
        $data['v'] = $v_query; //pass data to view
        $vehicle_id = $v_query['vehicles_id'];

        //get kms by vehicle id
        $kms = $this->tech_model->getKmsByVehicleId($vehicle_id)->row_array();
        $data['kms'] = $kms;

        //get staff
        $staff_params = array(
            'sel_query' => "sa.StaffID, sa.FirstName, sa.LastName, sa.is_electrician, sa.ContactNumber, sa.ClassID",
            'staff_id' => $tech_id
        );
        $staff_query = $this->gherxlib->getStaffInfo($staff_params);
        $data['staff'] = $staff_query->row_array(); //pass data
        $staff = $staff_query->row_array();


        //get login user class_id
        $data['staff_classID'] = $this->db->select('ClassID')->from('staff_accounts')->where('StaffID', $this->session->staff_id)->get()->row()->ClassID;


        $data['isElectrician'] = ( $staff['is_electrician']==1 )?true:false;
        $data['tech_name'] = "{$staff['FirstName']} {$staff['LastName']}";
        $data['tech_mob1'] = $staff['ContactNumber'];

        //get tech stock
        $ts_query = $this->db->select('*')->from('tech_stock')->where('staff_id', $staff['StaffID'])->order_by('date','DESC')->limit(1)->get();
        $data['ts'] = $ts_query->row_array(); //pass data


        
        if($tr_id && !empty($tr_id)){
            //get techrunrows
            $tr_sel = "
                trr.`tech_run_rows_id`,
                trr.`row_id_type`,
                trr.`row_id`,
                trr.`hidden`,
                trr.`dnd_sorted`,
                trr.`highlight_color`,
                
                trr_hc.`tech_run_row_color_id`,
                trr_hc.`hex`,
                
                j.`id` AS jid,
                j.`precomp_jobs_moved_to_booked`,
                j.`completed_timestamp`,		

                p.`property_id`,
                p.`qld_new_leg_alarm_num`,
                p.`preferred_alarm_id`,

                a.`agency_id`,
                a.`allow_upfront_billing`
            ";
            $tr_params = array(
                'sel_query' => $tr_sel,
                'sort_list' => array(
                    array(
                        'order_by' => 'trr.sort_order_num',
                        'sort' => 'ASC'
                    )
                ),
                'display_only_booked' => 1,
                'display_query' => 0
            );
            $data['jr_list2'] = $this->tech_model->getTechRunRows($tr_id, $country_id, $tr_params);

        }


        // get all services for legend
        $data['serv_type_sql'] = $this->db->query("
            SELECT `id`, `type`
            FROM `alarm_job_type`
            WHERE `active` = 1
        ");

        $tech_name = $this->system_model->formatStaffName($staff['FirstName'],$staff['LastName']);
        $data['title'] =  "{$tech_name} ({$kms['number_plate']})  - ".$this->system_model->formatDate($date,'d/m/Y');


        // get tech break
        $tb_sql = $this->db->query("
        SELECT COUNT(`tech_break_id`) AS tb_count
        FROM `tech_breaks`
        WHERE CAST( `tech_break_start` AS Date ) = '{$today}'
        AND `tech_break_taken` = 1
        AND `tech_id` = {$tech_id}
        ");     
        $data['tb_count'] = $tb_sql->row()->tb_count;

        
        if( $staff_class == 6 ){ // tech
            $this->load->view('templates/inner_header_tech', $data);
        }else{
            $this->load->view('templates/inner_header', $data);
        }       
        $this->load->view('tech_run/tech_day_schedule_tech', $data);        
        if( $staff_class == 6 ){ // tech
            $this->load->view('templates/inner_footer_tech', $data);
        }else{
            $this->load->view('templates/inner_footer', $data);
        }  
       

    }


    public function take_lunch_break(){
        
        $tech_id = $this->input->get_post('tech_id');   
        $today = date('Y-m-d H:i:s');

        if( $tech_id > 0 ){

            $insert_data = array(    
                'tech_id' => $tech_id,                 
                'tech_break_start' => $today,
                'tech_break_taken' => 1
                                      
            );
            
            $this->db->insert('tech_breaks', $insert_data);

        }
       
    }


    public function check_if_tech_has_taken_break(){

        $tech_id = $this->session->staff_id;
        $today = date("Y-m-d");
        $ret = 0;

        $lunch_break_time = strtotime("11:30:00");

        if ( time() >= $lunch_break_time ) {

            if( $tech_id > 0 ){

                // get tech break
                $tb_sql_str = "
                SELECT COUNT(`tech_break_id`) AS tb_count
                FROM `tech_breaks`
                WHERE CAST( `tech_break_start` AS Date ) = '{$today}'
                AND `tech_break_taken` = 1
                AND `tech_id` = {$tech_id}
                ";
                
                $tb_sql = $this->db->query($tb_sql_str);     
                $tb_count = $tb_sql->row()->tb_count;

                if( $tb_count == 0 ){ // has not taken break yet
                    $ret =  1; 
                }   

            }                    

        }

        echo $ret;

    }


    public function run_sheet_simple(){

        //$data['start_load_time'] = microtime(true);
        $country_id = $this->config->item('country');


        $tr_id = $this->input->get_post('tr_id');        
        $data['tr_id'] = $tr_id; //pass data

        $data['uri'] = "/tech_run/run_sheet_simple/?tr_id={$tr_id}";

        //get techrun by techrun id
        $tr_query = $this->db->select('*')->from('tech_run')->where('tech_run_id', $tr_id)->get();
        $data['tr'] = $tr_query->row_array();
        $tr = $tr_query->row_array();

        $data['hasTechRun'] = ( $tr_query->num_rows()>0 )?true:false;

        $data['tech_id'] = $tr['assigned_tech']; // pass data
        $tech_id = $tr['assigned_tech'];


        $data['day'] = date("d",strtotime($tr['date'])); //pass data
        $data['month'] = date("m",strtotime($tr['date'])); //pass data
        $data['year'] = date("Y",strtotime($tr['date'])); //pass data
        $date = $tr['date'];
        $data['date'] = $tr['date']; //pass data
        $sub_regions = $tr['sub_regions'];
        $data['sub_regions'] = $tr['sub_regions'];

        $staff_class = $this->system_model->getStaffClassID();
        if($staff_class==6){
            $tech_id = $this->session->staff_id;
            $data['tech_id'] = $this->session->staff_id;

            $data['day'] = date("d");
            $data['month'] = date("m");
            $data['year'] = date("y");
        }

        //get accomodation by tech run start
        $accom_query = $this->db->select('*')->from('accomodation')->where( array('accomodation_id'=> $tr['start'], 'country_id'=> $country_id) )->get();
        $accom_row = $accom_query->row_array();
        $data['accom_name'] = $accom_row['name'];
        $data['start_agency_address'] = $accom_row['address'];


        //get accomodation by tech run end
        $accom_query_end = $this->db->select('*')->from('accomodation')->where( array('accomodation_id'=> $tr['end'], 'country_id'=> $country_id) )->get();
        $end_acco = $accom_query_end->row_array();
        $data['end_accom_name'] = $end_acco['name'];
        $data['end_agency_address'] = $end_acco['address'];

        //get vehicle by staff_id
        $v_query = $this->tech_model->getVehicleByTechId($tech_id)->row_array();
        $data['v'] = $v_query; //pass data to view
        $vehicle_id = $v_query['vehicles_id'];

        //get kms by vehicle id
        $kms = $this->tech_model->getKmsByVehicleId($vehicle_id)->row_array();
        $data['kms'] = $kms;

        //get staff
        $staff_params = array(
            'sel_query' => "sa.StaffID, sa.FirstName, sa.LastName, sa.is_electrician, sa.ContactNumber, sa.ClassID",
            'staff_id' => $tech_id
        );
        $staff_query = $this->gherxlib->getStaffInfo($staff_params);
        $data['staff'] = $staff_query->row_array(); //pass data
        $staff = $staff_query->row_array();


        //get login user class_id
        $data['staff_classID'] = $this->db->select('ClassID')->from('staff_accounts')->where('StaffID', $this->session->staff_id)->get()->row()->ClassID;


        $data['isElectrician'] = ( $staff['is_electrician']==1 )?true:false;
        $data['tech_name'] = "{$staff['FirstName']} {$staff['LastName']}";
        $data['tech_mob1'] = $staff['ContactNumber'];

        //get tech stock
        $ts_query = $this->db->select('*')->from('tech_stock')->where('staff_id', $staff['StaffID'])->order_by('date','DESC')->limit(1)->get();
        $data['ts'] = $ts_query->row_array(); //pass data


        
        if($tr_id && !empty($tr_id)){
            //get techrunrows
            $tr_sel = "
                trr.`tech_run_rows_id`,
                trr.`row_id_type`,
                trr.`row_id`,
                trr.`hidden`,
                trr.`dnd_sorted`,
                trr.`highlight_color`,
                
                trr_hc.`tech_run_row_color_id`,
                trr_hc.`hex`,
                
                j.`id` AS jid,
                j.`precomp_jobs_moved_to_booked`,
                j.`completed_timestamp`,		

                p.`property_id`,

                a.`agency_id`,
                a.`allow_upfront_billing`
            ";
            $tr_params = array(
                'sel_query' => $tr_sel,
                'sort_list' => array(
                    array(
                        'order_by' => 'trr.sort_order_num',
                        'sort' => 'ASC'
                    )
                ),
                'display_only_booked' => 1,
                'display_query' => 0
            );
            $data['jr_list2'] = $this->tech_model->getTechRunRows($tr_id, $country_id, $tr_params);

        }


        // get all services for legend
        $data['serv_type_sql'] = $this->db->query("
            SELECT `id`, `type`
            FROM `alarm_job_type`
            WHERE `active` = 1
        ");

        $tech_name = $this->system_model->formatStaffName($staff['FirstName'],$staff['LastName']);
        $data['title'] =  "{$tech_name} ({$kms['number_plate']})  - ".$this->system_model->formatDate($date,'d/m/Y');
        
        if( $staff_class == 6 ){ // tech
            $this->load->view('templates/inner_header_tech', $data);
        }else{
            $this->load->view('templates/inner_header', $data);
        }       
        $this->load->view('tech_run/run_sheet_simple', $data);        
        if( $staff_class == 6 ){ // tech
            $this->load->view('templates/inner_footer_tech', $data);
        }else{
            $this->load->view('templates/inner_footer', $data);
        }  
       

    }


    public function ajax_sort_tech_run(){

        $tr_id = $this->input->get_post('tr_id');
        $trw_ids = $this->input->get_post('tbl_maps');

        $this->tech_model->techRunDragAndDropSort($tr_id, $trw_ids);


    }


    public function ajax_tech_run_get_new_list(){

        $tr_id = $this->input->post('tr_id');
        $tech_id = $this->input->post('tech_id');
        $date = $this->input->post('date');
        $sub_regions = $this->input->post('sub_regions');
        $get_assigned_only = $this->input->post('get_assigned_only');

        $ret1 = 0;
        $ret2 = 0;

        if( ($tr_id && !empty($tr_id) && is_numeric($tr_id)) && ($tech_id && !empty($tech_id) && is_numeric($tech_id)) ){ //VALIDATE

            if($get_assigned_only==1){

                // get new jobs from via assigned
                $isAssigned = 1;
                $ret2 = $this->tech_model->appendTechRunNewListings($tr_id,$tech_id,$date,$sub_regions,$this->config->item('country'),$isAssigned);
    
            }else{
                // get new jobs from via region
                $ret1 = $this->tech_model->appendTechRunNewListings($tr_id,$tech_id,$date,$sub_regions,$this->config->item('country'));	
                // get new jobs from via assigned
                $isAssigned = 1;
                $ret2 = $this->tech_model->appendTechRunNewListings($tr_id,$tech_id,$date,$sub_regions,$this->config->item('country'),$isAssigned);
    
            }

        }else{
            echo "Empty ID";
        }

        echo $ret1+$ret2;

    }


    public function map(){

        $tr_id = $this->input->get_post('tr_id');
        
        $data['start_load_time'] = microtime(true);
        
        $uri = '/tech_run/map';
        $data['uri'] = $uri;
        
        $country_id = $this->config->item('country');

        if( $tr_id > 0 ){

            //get techrun by techrun id
            $tr_query = $this->db->select('`assigned_tech`,`date`,`start`,`end`')->from('tech_run')->where('tech_run_id', $tr_id)->get();            
            $tr = $tr_query->row_array();
            
            $data['tech_id'] = $tr['assigned_tech']; // pass data
            $tech_id = $tr['assigned_tech'];
            $data['day'] = date("d",strtotime($tr['date'])); //pass data
            $data['month'] = date("m",strtotime($tr['date'])); //pass data
            $data['year'] = date("Y",strtotime($tr['date'])); //pass data            
            $data['date'] = $tr['date']; //pass data            
            

            //get accomodation by tech run start
            $accom_query = $this->db->select('`name`,`address`,`lat`,`lng`')->from('accomodation')->where( array('accomodation_id'=> $tr['start'], 'country_id'=> $country_id) )->get();
            $accom_row = $accom_query->row_array();
            $data['accom_name'] = $accom_row['name'];
            $data['start_agency_address'] = $accom_row['address'];
            $data['start_accom_lat'] = $accom_row['lat'];
            $data['start_accom_lng'] = $accom_row['lng'];


            //get accomodation by tech run end
            $accom_query_end = $this->db->select('`name`,`address`,`lat`,`lng`')->from('accomodation')->where( array('accomodation_id'=> $tr['end'], 'country_id'=> $country_id) )->get();
            $end_acco = $accom_query_end->row_array();
            $data['end_accom_name'] = $end_acco['name'];
            $data['end_agency_address'] = $end_acco['address'];
            $data['end_accom_lat'] = $end_acco['lat'];
            $data['end_accom_lng'] = $end_acco['lng'];

            //get staff
            $staff_params = array(
                'sel_query' => "sa.StaffID, sa.FirstName, sa.LastName, sa.is_electrician, sa.ContactNumber, sa.ClassID",
                'staff_id' => $tech_id
            );
            $staff_query = $this->gherxlib->getStaffInfo($staff_params);
            $data['staff'] = $staff_query->row_array(); //pass data
            $staff = $staff_query->row_array();


            //get login user class_id
            $data['staff_classID'] = $this->db->select('ClassID')->from('staff_accounts')->where('StaffID', $this->session->staff_id)->get()->row()->ClassID;

            $data['isElectrician'] = ( $staff['is_electrician']==1 )?true:false;
            $data['tech_name'] = "{$staff['FirstName']} {$staff['LastName']}";
            $data['tech_mob1'] = $staff['ContactNumber'];

            // get country data
            $country_params = array(
                'sel_query' => 'c.`country`',
                'country_id' => $country_id
            );
            $country_sql = $this->system_model->get_countries($country_params);
            $country_row = $country_sql->row();
            $data['country_name'] = $country_row->country;

            // get accomodation list
            $data['accom_sql'] = $this->db->query("
                SELECT `accomodation_id`, `name`
                FROM `accomodation`
                WHERE `country_id` = {$country_id}
                ORDER BY `name`
			");			

            // get row colours            
            $data['trrc_sql'] = $this->db->query("
                SELECT `tech_run_row_color_id`,`color` 
                FROM  `tech_run_row_color`
                WHERE `active` = 1
            ");

            $data['start_accom'] = $tr['start'];
            $data['end_accom'] = $tr['end'];

            // get FN main and sub agencies
            $fn_agency_arr = $this->system_model->get_fn_agencies();
            $fn_agency_main = $fn_agency_arr['fn_agency_main'];
            $fn_agency_sub =  $fn_agency_arr['fn_agency_sub'];
            $fn_agency_sub_imp = implode(",",$fn_agency_sub);

            $data['fn_agency_main'] = $fn_agency_main;
            $data['fn_agency_sub'] =  $fn_agency_sub;      
            $data['fn_agency_sub_imp'] = $fn_agency_sub_imp;

            // get VISION REAL ESTATE main and sub agencies
            $vision_agency_arr = $this->system_model->get_vision_agencies();
            $vision_agency_main = $vision_agency_arr['vision_agency_main'];
            $vision_agency_sub =  $vision_agency_arr['vision_agency_sub'];
            $vision_agency_sub_imp = implode(",",$vision_agency_sub);

            $data['vision_agency_main'] = $vision_agency_main;
            $data['vision_agency_sub'] =  $vision_agency_sub;      
            $data['vision_agency_sub_imp'] = $vision_agency_sub_imp;


            //get tech run rows
            $tr_sel = "DISTINCT (a.`agency_id`), a.`agency_name`";
            $tr_params = array(
                'sel_query' => $tr_sel,                
                'job_rows_only' => 1
            );
            $data['agency_keys_sql'] = $this->tech_model->getTechRunRows($tr_id, $country_id, $tr_params);     
                        
            
            //get techrunrows
            $tr_sel = "
                trr.`tech_run_rows_id`,
                trr.`row_id_type`,
                trr.`row_id`,
                trr.`hidden`,
                trr.`dnd_sorted`,
                trr.`highlight_color`,
                
                trr_hc.`tech_run_row_color_id`,
                trr_hc.`hex`,
                
                j.`id` AS jid,
                j.`precomp_jobs_moved_to_booked`,
                j.`completed_timestamp`,		

                p.`property_id`,

                a.`agency_id`,
                a.`allow_upfront_billing`
            ";
            $tr_params = array(
                'sel_query' => $tr_sel,
                'sort_list' => array(
                    array(
                        'order_by' => 'trr.sort_order_num',
                        'sort' => 'ASC'
                    )
                ),
                'display_query' => 0
            );

            $jr_list2 = $this->tech_model->getTechRunRows($tr_id, $country_id, $tr_params);

            // update address that has empty coordinates
            foreach($jr_list2->result() as $trr_row){

                
				if( $trr_row->row_id_type == 'job_id' ){ // JOBS
					
                    $job_sql = $this->tech_model->getJobRowData($trr_row->row_id,$this->config->item('country'));
                                        
					if(  $job_sql->num_rows() > 0 ){
                        
                        $job_row = $job_sql->row();

						if( $job_row->p_lat == "" || $job_row->p_lng == "" ){

							$address = "{$job_row->p_address_1} {$job_row->p_address_2} {$job_row->p_address_3} {$job_row->p_state} {$job_row->p_postcode}";                                                  
                            $coordinate = $this->system_model->getGoogleMapCoordinates($address);	                            

                            if( $job_row->property_id > 0 && $coordinate['lat'] != '' && $coordinate['lng'] != '' ){

                                // update lat lng
                                $update_coor_str = "
                                    UPDATE `property`
                                    SET `lat` = {$coordinate['lat']},
                                        `lng` = {$coordinate['lng']}
                                    WHERE `property_id` = {$job_row->property_id}
                                ";
                                $this->db->query($update_coor_str);

                            }
							
						}
						
					}
			
                }else if( $trr_row->row_id_type == 'keys_id' ){ // KEYS
                    
                    $key_sql = $this->tech_model->getTechRunKeys($trr_row->row_id);

                    if(  $key_sql->num_rows() > 0 ){

                        $key_row = $key_sql->row();

                        if( $key_row->lat == "" || $key_row->lng == "" ){

							$address = "{$key_row->address_1} {$key_row->address_2} {$key_row->address_3} {$key_row->state} {$key_row->postcode}";                                                  
                            $coordinate = $this->system_model->getGoogleMapCoordinates($address);	                            

                            if( $key_row->agency_id > 0 && $coordinate['lat'] != '' && $coordinate['lng'] != '' ){

                                // update lat lng
                                echo $update_coor_str = "
                                UPDATE `agency`
                                SET `lat` = {$coordinate['lat']},
                                    `lng` = {$coordinate['lng']}
                                WHERE `agency_id` = {$key_row->agency_id}
                                ";
                                $this->db->query($update_coor_str);

                            }
							
						}
                        
                    }
					

                }else if( $trr_row->row_id_type == 'supplier_id' ){ // SUPPLIER

                    $sup_sql = $this->tech_model->getTechRunSuppliers($trr_row->row_id);

                    if(  $sup_sql->num_rows() > 0 ){

                        $sup_row = $sup_sql->row();

                        if( $sup_row->lat == "" || $sup_row->lng == "" ){

							$address = $sup_row->sup_address;                                                  
                            $coordinate = $this->system_model->getGoogleMapCoordinates($address);	

                            if( $sup_row->suppliers_id > 0 && $coordinate['lat'] != '' && $coordinate['lng'] != '' ){

                                // update lat lng
                                $update_coor_str = "
                                    UPDATE `suppliers`
                                    SET `lat` = {$coordinate['lat']},
                                        `lng` = {$coordinate['lng']}
                                    WHERE `suppliers_id` = {$sup_row->suppliers_id}
                                ";
                                $this->db->query($update_coor_str);

                            }
							
						}
                        
                    }

                }


            }
            
            // get tech run list
            $data['jr_list2'] = $jr_list2;

        }  

        $data['is_tech_run_map'] = true; // used to load different type of google map script   
        
        $data['title'] = "Tech Run Map - {$staff['FirstName']} {$staff['LastName']} ".date('d/m/Y',strtotime($tr['date']));

        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);

    }



    public function run_sheet_admin_map(){

        $tr_id = $this->input->get_post('tr_id');
        $staff_class_id = $this->system_model->getStaffClassID();
        
        $data['start_load_time'] = microtime(true);
        
        $uri = '/tech_run/run_sheet_admin_map';
        $data['uri'] = $uri;
        
        $country_id = $this->config->item('country');

        if( $tr_id > 0 ){

            //get techrun by techrun id
            $tr_query = $this->db->select('`assigned_tech`,`date`,`start`,`end`')->from('tech_run')->where('tech_run_id', $tr_id)->get();            
            $tr = $tr_query->row_array();
            
            $data['tech_id'] = $tr['assigned_tech']; // pass data
            $tech_id = $tr['assigned_tech'];
            $data['day'] = date("d",strtotime($tr['date'])); //pass data
            $data['month'] = date("m",strtotime($tr['date'])); //pass data
            $data['year'] = date("Y",strtotime($tr['date'])); //pass data            
            $data['date'] = $tr['date']; //pass data            
            

            //get accomodation by tech run start
            $accom_query = $this->db->select('`name`,`address`,`lat`,`lng`')->from('accomodation')->where( array('accomodation_id'=> $tr['start'], 'country_id'=> $country_id) )->get();
            $accom_row = $accom_query->row_array();
            $data['accom_name'] = $accom_row['name'];
            $data['start_agency_address'] = $accom_row['address'];
            $data['start_accom_lat'] = $accom_row['lat'];
            $data['start_accom_lng'] = $accom_row['lng'];


            //get accomodation by tech run end
            $accom_query_end = $this->db->select('`name`,`address`,`lat`,`lng`')->from('accomodation')->where( array('accomodation_id'=> $tr['end'], 'country_id'=> $country_id) )->get();
            $end_acco = $accom_query_end->row_array();
            $data['end_accom_name'] = $end_acco['name'];
            $data['end_agency_address'] = $end_acco['address'];
            $data['end_accom_lat'] = $end_acco['lat'];
            $data['end_accom_lng'] = $end_acco['lng'];

            //get staff
            $staff_params = array(
                'sel_query' => "sa.StaffID, sa.FirstName, sa.LastName, sa.is_electrician, sa.ContactNumber, sa.ClassID",
                'staff_id' => $tech_id
            );
            $staff_query = $this->gherxlib->getStaffInfo($staff_params);
            $data['staff'] = $staff_query->row_array(); //pass data
            $staff = $staff_query->row_array();


            //get login user class_id
            $data['staff_classID'] = $this->db->select('ClassID')->from('staff_accounts')->where('StaffID', $this->session->staff_id)->get()->row()->ClassID;

            $data['isElectrician'] = ( $staff['is_electrician']==1 )?true:false;
            $data['tech_name'] = "{$staff['FirstName']} {$staff['LastName']}";
            $data['tech_mob1'] = $staff['ContactNumber'];

            // get country data
            $country_params = array(
                'sel_query' => 'c.`country`',
                'country_id' => $country_id
            );
            $country_sql = $this->system_model->get_countries($country_params);
            $country_row = $country_sql->row();
            $data['country_name'] = $country_row->country;

            // get accomodation list
            $data['accom_sql'] = $this->db->query("
                SELECT `accomodation_id`, `name`
                FROM `accomodation`
                WHERE `country_id` = {$country_id}
                ORDER BY `name`
			");			

            // get row colours            
            $data['trrc_sql'] = $this->db->query("
                SELECT `tech_run_row_color_id`,`color` 
                FROM  `tech_run_row_color`
                WHERE `active` = 1
            ");

            $data['start_accom'] = $tr['start'];
            $data['end_accom'] = $tr['end'];

             // get FN main and sub agencies
            $fn_agency_arr = $this->system_model->get_fn_agencies();
            $fn_agency_main = $fn_agency_arr['fn_agency_main'];
            $fn_agency_sub =  $fn_agency_arr['fn_agency_sub'];
            $fn_agency_sub_imp = implode(",",$fn_agency_sub);

            $data['fn_agency_main'] = $fn_agency_main;
            $data['fn_agency_sub'] =  $fn_agency_sub;      
            $data['fn_agency_sub_imp'] = $fn_agency_sub_imp;

            // get VISION REAL ESTATE main and sub agencies
            $vision_agency_arr = $this->system_model->get_vision_agencies();
            $vision_agency_main = $vision_agency_arr['vision_agency_main'];
            $vision_agency_sub =  $vision_agency_arr['vision_agency_sub'];
            $vision_agency_sub_imp = implode(",",$vision_agency_sub);

            $data['vision_agency_main'] = $vision_agency_main;
            $data['vision_agency_sub'] =  $vision_agency_sub;      
            $data['vision_agency_sub_imp'] = $vision_agency_sub_imp;


            //get tech run rows
            $tr_sel = "DISTINCT (a.`agency_id`), a.`agency_name`";
            $tr_params = array(
                'sel_query' => $tr_sel,                
                'job_rows_only' => 1,
                'sort_list' => array(
                    array(
                        'order_by' => 'a.agency_name',
                        'sort' => 'ASC'
                    )
                ),
            );
            $data['agency_keys_sql'] = $this->tech_model->getTechRunRows($tr_id, $country_id, $tr_params);     
                        
            
            //get techrunrows
            $tr_sel = "
                trr.`tech_run_rows_id`,
                trr.`row_id_type`,
                trr.`row_id`,
                trr.`hidden`,
                trr.`dnd_sorted`,
                trr.`highlight_color`,
                
                trr_hc.`tech_run_row_color_id`,
                trr_hc.`hex`,
                
                j.`id` AS jid,
                j.`precomp_jobs_moved_to_booked`,
                j.`completed_timestamp`,		

                p.`property_id`,
                p.`qld_new_leg_alarm_num`,
                p.`preferred_alarm_id`,

                a.`agency_id`,
                a.`allow_upfront_billing`
            ";
            $tr_params = array(
                'sel_query' => $tr_sel,
                'sort_list' => array(
                    array(
                        'order_by' => 'trr.sort_order_num',
                        'sort' => 'ASC'
                    )
                ),
                'display_only_booked' => 1,
                'display_query' => 0
            );
            $data['jr_list2'] = $this->tech_model->getTechRunRows($tr_id, $country_id, $tr_params);

        }   
        $data['is_tech_run_map'] = true; // used to load different type of google map script   
        
        $data['title'] = "Tech Day Schedule Map (Admin) - {$staff['FirstName']} {$staff['LastName']} ".date('d/m/Y',strtotime($tr['date']));

        //load views
        if( $staff_class_id == 6 ){ // tech
            $this->load->view('templates/inner_header_tech', $data);
        }else{
            $this->load->view('templates/inner_header', $data);
        }     
        $this->load->view('tech_run/tds_admin_map', $data);
        if( $staff_class_id == 6 ){ // tech
            $this->load->view('templates/inner_footer_tech', $data);
        }else{
            $this->load->view('templates/inner_footer', $data);
        }

    }


    public function run_sheet_map(){

        $tr_id = $this->input->get_post('tr_id');
        $staff_class_id = $this->system_model->getStaffClassID();
        
        //$data['start_load_time'] = microtime(true);
        
        $uri = '/tech_run/run_sheet_map';
        $data['uri'] = $uri;
        
        $country_id = $this->config->item('country');

        if( $tr_id > 0 ){

            //get techrun by techrun id
            $tr_query = $this->db->select('`assigned_tech`,`date`,`start`,`end`')->from('tech_run')->where('tech_run_id', $tr_id)->get();            
            $tr = $tr_query->row_array();
            
            $data['tech_id'] = $tr['assigned_tech']; // pass data
            $tech_id = $tr['assigned_tech'];
            $data['day'] = date("d",strtotime($tr['date'])); //pass data
            $data['month'] = date("m",strtotime($tr['date'])); //pass data
            $data['year'] = date("Y",strtotime($tr['date'])); //pass data            
            $data['date'] = $tr['date']; //pass data            
            

            //get accomodation by tech run start
            $accom_query = $this->db->select('`name`,`address`,`lat`,`lng`')->from('accomodation')->where( array('accomodation_id'=> $tr['start'], 'country_id'=> $country_id) )->get();
            $accom_row = $accom_query->row_array();
            $data['accom_name'] = $accom_row['name'];
            $data['start_agency_address'] = $accom_row['address'];
            $data['start_accom_lat'] = $accom_row['lat'];
            $data['start_accom_lng'] = $accom_row['lng'];


            //get accomodation by tech run end
            $accom_query_end = $this->db->select('`name`,`address`,`lat`,`lng`')->from('accomodation')->where( array('accomodation_id'=> $tr['end'], 'country_id'=> $country_id) )->get();
            $end_acco = $accom_query_end->row_array();
            $data['end_accom_name'] = $end_acco['name'];
            $data['end_agency_address'] = $end_acco['address'];
            $data['end_accom_lat'] = $end_acco['lat'];
            $data['end_accom_lng'] = $end_acco['lng'];

            //get staff
            $staff_params = array(
                'sel_query' => "sa.StaffID, sa.FirstName, sa.LastName, sa.is_electrician, sa.ContactNumber, sa.ClassID",
                'staff_id' => $tech_id
            );
            $staff_query = $this->gherxlib->getStaffInfo($staff_params);
            $data['staff'] = $staff_query->row_array(); //pass data
            $staff = $staff_query->row_array();


            //get login user class_id
            $data['staff_classID'] = $this->db->select('ClassID')->from('staff_accounts')->where('StaffID', $this->session->staff_id)->get()->row()->ClassID;

            $data['isElectrician'] = ( $staff['is_electrician']==1 )?true:false;
            $data['tech_name'] = "{$staff['FirstName']} {$staff['LastName']}";
            $data['tech_mob1'] = $staff['ContactNumber'];

            // get country data
            $country_params = array(
                'sel_query' => 'c.`country`',
                'country_id' => $country_id
            );
            $country_sql = $this->system_model->get_countries($country_params);
            $country_row = $country_sql->row();
            $data['country_name'] = $country_row->country;

            // get accomodation list
            $data['accom_sql'] = $this->db->query("
                SELECT `accomodation_id`, `name`
                FROM `accomodation`
                WHERE `country_id` = {$country_id}
                ORDER BY `name`
			");			

            // get row colours            
            $data['trrc_sql'] = $this->db->query("
                SELECT `tech_run_row_color_id`,`color` 
                FROM  `tech_run_row_color`
                WHERE `active` = 1
            ");

            $data['start_accom'] = $tr['start'];
            $data['end_accom'] = $tr['end'];


            //get tech run rows
            $tr_sel = "DISTINCT (a.`agency_id`), a.`agency_name`";
            $tr_params = array(
                'sel_query' => $tr_sel,                
                'job_rows_only' => 1
            );
            $data['agency_keys_sql'] = $this->tech_model->getTechRunRows($tr_id, $country_id, $tr_params);     
                        
            
            //get techrunrows
            $tr_sel = "
                trr.`tech_run_rows_id`,
                trr.`row_id_type`,
                trr.`row_id`,
                trr.`hidden`,
                trr.`dnd_sorted`,
                trr.`highlight_color`,
                
                trr_hc.`tech_run_row_color_id`,
                trr_hc.`hex`,
                
                j.`id` AS jid,
                j.`precomp_jobs_moved_to_booked`,
                j.`completed_timestamp`,		

                p.`property_id`,
                p.`qld_new_leg_alarm_num`,
                p.`preferred_alarm_id`,

                a.`agency_id`,
                a.`allow_upfront_billing`
            ";
            $tr_params = array(
                'sel_query' => $tr_sel,
                'sort_list' => array(
                    array(
                        'order_by' => 'trr.sort_order_num',
                        'sort' => 'ASC'
                    )
                ),
                'display_only_booked' => 1,
                'display_query' => 0
            );
            $data['jr_list2'] = $this->tech_model->getTechRunRows($tr_id, $country_id, $tr_params);

        }   
        $data['is_tech_run_map'] = true; // used to load different type of google map script   
        
        //$data['title'] = "Tech Day Schedule Map (Tech) - {$staff['FirstName']} {$staff['LastName']} ".date('d/m/Y',strtotime($tr['date']));
        $data['title'] = "Run Sheet Map";

        //load views
        if( $staff_class_id == 6 ){ // tech
            $this->load->view('templates/inner_header_tech', $data);
        }else{
            $this->load->view('templates/inner_header', $data);
        }     
        $this->load->view('tech_run/tds_tech_map', $data);
        if( $staff_class_id == 6 ){ // tech
            $this->load->view('templates/inner_footer_tech', $data);
        }else{
            $this->load->view('templates/inner_footer', $data);
        } 
        

    }

    // assign pin colour
    public function ajax_assign_pin_colours(){       

        $tr_id = $this->input->get_post('tr_id');
        $trr_id_arr = $this->input->get_post('trr_id_arr');
        $trr_hl_color = $this->input->get_post('trr_hl_color');
        
        // convert tech run row ID, comma separated from string to array
        $trr_id_arr_exp = explode(",",$trr_id_arr); 
        
        $tr_params = array(
            'tr_id' => $tr_id,
            'trr_id_arr' => $trr_id_arr_exp,
            'trr_hl_color' => $trr_hl_color            
        );
        $this->tech_run_model->assign_pin_colours($tr_params);
		

    }

    // clear pin colours
    public function ajax_clear_all_pin_colors(){

        $tr_id = $this->input->get_post('tr_id');

        if( $tr_id > 0 ){
            
            $this->db->query("
                UPDATE `tech_run_rows`
                SET `highlight_color` = NULL
                WHERE `tech_run_id` = {$tr_id}
            ");

        }

        
    }

    // update start and end
    public function ajax_update_start_and_end(){       

        $tr_id = $this->input->get_post('tr_id');
        $start = $this->input->get_post('start');
        $end = $this->input->get_post('end');        
        
        $tr_params = array(
            'tr_id' => $tr_id,
            'start' => $start,
            'end' => $end            
        );
        $this->tech_run_model->update_start_and_end($tr_params);		

    }

    // add agency keys
    public function ajax_add_agency_keys(){       

        $tr_id = $this->input->get_post('tr_id');
        $keys_agency = $this->input->get_post('keys_agency');
        $agency_addresses_id = $this->input->get_post('agency_addresses_id');
        $tech_id = $this->input->get_post('tech_id');  
        $date = $this->input->get_post('date');      
        
        $tr_params = array(
            'tr_id' => $tr_id,
            'keys_agency' => $keys_agency,
            'agency_addresses_id' => $agency_addresses_id,
            'tech_id' => $tech_id,
            'date' => $date            
        );
        $this->tech_run_model->add_agency_keys($tr_params);		

    }

    // tech run keys
    // old page: tech_run_keys.php
    public function keys() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Keys";
        $uri = '/tech_run/keys';
        $data['uri'] = $uri;
        
        $tech_id = $this->input->get_post('tech_id');
        $data['tech_id'] = $tech_id;
        $date = ( $this->input->get_post('date') !='' )?$this->system_model->formatDate($this->input->get_post('date')):null;
        $data['date'] = $date;
        $tr_id = $this->input->get_post('tr_id');
        $data['tr_id'] = $tr_id;
        $country_id = $this->config->item('country');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( $this->input->get_post('offset') != '' )?$this->input->get_post('offset'):0;       
        
        
        //get techrun by techrun id
        $tr_query = $this->db->select('*')->from('tech_run')->where('tech_run_id', $tr_id)->get();
        $data['tr'] = $tr_query->row_array();
        $tr = $tr_query->row_array();

        $data['hasTechRun'] = ( $tr_query->num_rows()>0 )?true:false;

        $data['tech_id'] = $tr['assigned_tech']; // pass data
        $tech_id = $tr['assigned_tech'];
        $date = $tr['date'];
        $data['date'] = $tr['date']; //pass data

       
        // paginated list        
        $data['agency_sql'] = $this->db->query("
            SELECT DISTINCT a.`agency_id`, a.`agency_name`, a.`agency_specific_notes`
            FROM `tech_run_keys` AS kr
            LEFT JOIN `agency` AS a ON kr.`agency_id` = a.`agency_id`
            WHERE kr.`date` = '{$date}'
            AND ( 
                kr.`deleted` = 0 
                OR kr.`deleted` IS NULL 
            )
            AND a.`country_id` =  {$country_id}
            AND kr.`assigned_tech` ={$tech_id}
        ");


        /*
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
        */
        

        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);

    }

    public function ajax_update_property_key(){

        $property_id = $this->input->post('property_id');
        $key_number = $this->input->post('key_number');       

        if( $property_id > 0 ){
            
            // update property key
            $this->db->query("
                UPDATE `property`
                SET 
                    `key_number` = '{$key_number}'
                WHERE `property_id` = {$property_id}
            ");

        }

    }

    public function ajax_drop_off_save_signature(){

        $kr_id = $this->input->post('kr_id');
        $signature_svg = $this->input->post('signature_svg');  
        $date = date("Y-m-d H:i:s");     

        // update property key
        if( $kr_id > 0 ){
                        
            $this->db->query("
            UPDATE `tech_run_keys`
            SET 
                `completed` = 1,
                `completed_date` = {$date},
                `signature_svg`	= '{$signature_svg}'
            WHERE `tech_run_keys_id` = {$kr_id}
            ");

        }


    }

    // get tech run keys list
    public function ajax_job_key_list(){
        
        $tech_id = $this->input->post('tech_id');  
        $date = $this->input->post('date');  
        $agency_id = $this->input->post('agency_id');  

        $key_action = $this->input->post('key_action');        

        $params = array(
            'tech_id' => $tech_id,
            'date' => $date,
            'agency_id' => $agency_id,
            'key_action' => $key_action
        );
        $this->tech_run_model->get_tech_run_keys_list($params);

    }



    public function ajax_save_agency_key_pickup(){

        $this->load->model('properties_model');

        $trk_id = $this->input->post('trk_id');

        $tech_id = $this->input->post('tech_id');
        $agency_id = $this->input->post('agency_id');
        $date = $this->input->post('date');

        $agency_staff = $this->input->post('agency_staff');      
        $number_of_keys = $this->input->post('number_of_keys');                 

        $key_number_arr = $this->input->post('key_number_arr');
        $job_id_arr = $this->input->post('job_id_arr');
        $is_keys_picked_up_arr = $this->input->post('is_keys_picked_up_arr');
        $attend_property_arr = $this->input->post('attend_property_arr');
        $job_reason_arr = $this->input->post('job_reason_arr');     
        $reason_comment_arr = $this->input->post('reason_comment_arr'); 

        $now = date("Y-m-d H:i:s");      
        $country_id = $this->config->item('country');     
        
        // update property key
        if( $trk_id > 0 ){
              
            // update tech run key
            $this->db->query("
            UPDATE `tech_run_keys`
            SET 
                `completed` = 1,
                `completed_date` = '{$now}',
                `agency_staff`	= '{$agency_staff}',
                `number_of_keys` = {$number_of_keys}                
            WHERE `tech_run_keys_id` = {$trk_id}
            ");

            // clear agency keys
            if( $tech_id > 0 && $date != '' && $agency_id > 0 ){
                
                $this->db->query("
                DELETE
                FROM `agency_keys`
                WHERE `tech_id` = {$tech_id}
                AND `date` = '{$date}'
                AND `agency_id` = {$agency_id}
                ");

            }            
                   
            // loop through jobs
            foreach( $job_id_arr as $index => $job_id ){

                $is_keys_picked_up = $is_keys_picked_up_arr[$index];
                $attend_property = $attend_property_arr[$index];
                $job_reason = $job_reason_arr[$index];
                $reason_comment = $reason_comment_arr[$index]; 
                $key_number = $key_number_arr[$index];   

                if( $job_id > 0 ){

                    // get jobs data
                    $job_sql = $this->db->query("
                    SELECT                            
                        j.`assigned_tech`,                         
                        p.`property_id`
                    FROM `jobs` AS j
                    LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                    WHERE `id` = {$job_id}
                    ");                    
                    $job_row = $job_sql->row();
                    
                    $tech_id = $job_row->assigned_tech;                    
                    $property_id = $job_row->property_id;                                                
                                                                                                                  
                    if( $property_id > 0 ){
                
                        // update property key
                        $this->db->query("
                            UPDATE `property`
                            SET 
                                `key_number` = '{$key_number}'
                            WHERE `property_id` = {$property_id}
                        ");
            
                    }

                    // if keys pick up is NO                   
                    if( $is_keys_picked_up == 0 && is_numeric($is_keys_picked_up) ){    

                        // if property attended is NO and job reason selected  
                        if( $attend_property == 0 && is_numeric($attend_property) && $job_reason > 0 ){ // property attended is NO

                            // mark job as not completed
                            $mjnc_params = array(
                                'job_id' => $job_id,
                                'tech_id' => $tech_id,
                                'job_reason' => $job_reason,
                                'reason_comment' => $reason_comment
                            );
                            $this->tech_run_model->mark_job_not_completed($mjnc_params); 

                        }else if( $attend_property ==  1 ){ // property attended is YES

                            $log_title = 64; // Keys Not Collected
                            $log_details = "Tech <b>will</b> attend the property";
                            $log_params = array(
                                'title' => $log_title,  
                                'details' => $log_details,
                                'display_in_vjd' => 1,
                                'created_by_staff' => $this->session->staff_id,
                                'job_id' => $job_id
                            );
                            $this->system_model->insert_log($log_params);    

                        }                        

                    }

                    // create agency keys
                    $this->db->query("
                        INSERT INTO 
                        `agency_keys`(
                            `tech_id`,
                            `date`,
                            `agency_id`,
                            `job_id`,
                            `is_keys_picked_up`,
                            `attend_property`,
                            `job_reason`,
                            `reason_comment`,
                            `created_date`
                        )
                        VALUES(
                            {$tech_id},
                            '{$date}',
                            {$agency_id},
                            {$job_id},
                            ". ( ( is_numeric($is_keys_picked_up) )?$is_keys_picked_up:'NULL' ) .",
                            ". ( ( is_numeric($attend_property) )?$attend_property:'NULL' ) .",
                            ". ( ( $job_reason > 0 )?$job_reason:'NULL' ) .",
                            '{$reason_comment}',                   
                            '{$now}'
                        )                    
                    ");

                }                                                             

            }
            
        }        

    }


    public function ajax_save_agency_key_drop_off(){

        $trk_id = $this->input->post('trk_id');

        $agency_staff = $this->input->post('agency_staff');      
        $number_of_keys = $this->input->post('number_of_keys');    
        $signature_svg = $this->input->post('signature_svg'); 
        $refused_sig = $this->input->post('refused_sig');      
        $now = date("Y-m-d H:i:s");   
                
        $agency_keys_id_arr = $this->input->post('agency_keys_id_arr');
        $is_keys_returned_arr = $this->input->post('is_keys_returned_arr');
        $not_returned_notes_arr = $this->input->post('not_returned_notes_arr');
        $signature_update_str = '';

        // didnt refused signature
        if( $signature_svg != '' && $refused_sig != 1 ){
            $signature_update_str = ",`signature_svg` = '{$signature_svg}'";
        }        

        // update property key
        if( $trk_id > 0 ){
                        
            $this->db->query("
            UPDATE `tech_run_keys`
            SET 
                `completed` = 1,
                `completed_date` = '{$now}',
                `agency_staff`	= '{$agency_staff}',
                `number_of_keys` = {$number_of_keys},
                `refused_sig` = {$refused_sig} 
                {$signature_update_str}               
            WHERE `tech_run_keys_id` = {$trk_id}
            ");
            
        }    
        
        // loop through jobs
        foreach( $agency_keys_id_arr as $index => $agency_keys_id ){

            $is_keys_returned = $is_keys_returned_arr[$index];
            $not_returned_notes = $not_returned_notes_arr[$index];             

            if( $agency_keys_id > 0 ){                

                // update agency keys
                $this->db->query("
                    UPDATE `agency_keys`
                    SET 
                        `is_keys_returned` = ". ( ( is_numeric($is_keys_returned) )?$is_keys_returned:'NULL' ) .",
                        `not_returned_notes` = ". ( ( $not_returned_notes !='' )?"'{$not_returned_notes}'":'NULL' ) .",
                        `drop_off_ts` = '{$now}'
                    WHERE `agency_keys_id` = {$agency_keys_id}                  
                ");

            }                                                             

        }

    }


    public function available_dk(){

        $tr_id = $this->input->get_post('tr_id');
        $staff_class_id = $this->system_model->getStaffClassID();
        
        //$data['start_load_time'] = microtime(true);
        
        $uri = '/tech_run/available_dk';
        $data['uri'] = $uri;
        
        $country_id = $this->config->item('country');

        if( $tr_id > 0 ){

            //get techrun by techrun id
            $tr_query = $this->db->select('`assigned_tech`,`date`,`start`,`end`')->from('tech_run')->where('tech_run_id', $tr_id)->get();            
            $tr = $tr_query->row_array();
            
            $data['tech_id'] = $tr['assigned_tech']; // pass data
            $tech_id = $tr['assigned_tech'];
            $data['day'] = date("d",strtotime($tr['date'])); //pass data
            $data['month'] = date("m",strtotime($tr['date'])); //pass data
            $data['year'] = date("Y",strtotime($tr['date'])); //pass data            
            $data['date'] = $tr['date']; //pass data            
            

            //get accomodation by tech run start
            $accom_query = $this->db->select('`name`,`address`,`lat`,`lng`')->from('accomodation')->where( array('accomodation_id'=> $tr['start'], 'country_id'=> $country_id) )->get();
            $accom_row = $accom_query->row_array();
            $data['accom_name'] = $accom_row['name'];
            $data['start_agency_address'] = $accom_row['address'];
            $data['start_accom_lat'] = $accom_row['lat'];
            $data['start_accom_lng'] = $accom_row['lng'];


            //get accomodation by tech run end
            $accom_query_end = $this->db->select('`name`,`address`,`lat`,`lng`')->from('accomodation')->where( array('accomodation_id'=> $tr['end'], 'country_id'=> $country_id) )->get();
            $end_acco = $accom_query_end->row_array();
            $data['end_accom_name'] = $end_acco['name'];
            $data['end_agency_address'] = $end_acco['address'];
            $data['end_accom_lat'] = $end_acco['lat'];
            $data['end_accom_lng'] = $end_acco['lng'];

            //get staff
            $staff_params = array(
                'sel_query' => "sa.StaffID, sa.FirstName, sa.LastName, sa.is_electrician, sa.ContactNumber, sa.ClassID",
                'staff_id' => $tech_id
            );
            $staff_query = $this->gherxlib->getStaffInfo($staff_params);
            $data['staff'] = $staff_query->row_array(); //pass data
            $staff = $staff_query->row_array();


            //get login user class_id
            $data['staff_classID'] = $this->db->select('ClassID')->from('staff_accounts')->where('StaffID', $this->session->staff_id)->get()->row()->ClassID;

            $data['isElectrician'] = ( $staff['is_electrician']==1 )?true:false;
            $data['tech_name'] = "{$staff['FirstName']} {$staff['LastName']}";
            $data['tech_mob1'] = $staff['ContactNumber'];

            // get country data
            $country_params = array(
                'sel_query' => 'c.`country`',
                'country_id' => $country_id
            );
            $country_sql = $this->system_model->get_countries($country_params);
            $country_row = $country_sql->row();
            $data['country_name'] = $country_row->country;

            // get accomodation list
            $data['accom_sql'] = $this->db->query("
                SELECT `accomodation_id`, `name`
                FROM `accomodation`
                WHERE `country_id` = {$country_id}
                ORDER BY `name`
			");			

            // get row colours            
            $data['trrc_sql'] = $this->db->query("
                SELECT `tech_run_row_color_id`,`color` 
                FROM  `tech_run_row_color`
                WHERE `active` = 1
            ");

            $data['start_accom'] = $tr['start'];
            $data['end_accom'] = $tr['end'];


            //get tech run rows
            $tr_sel = "DISTINCT (a.`agency_id`), a.`agency_name`";
            $tr_params = array(
                'sel_query' => $tr_sel,                
                'job_rows_only' => 1
            );
            $data['agency_keys_sql'] = $this->tech_model->getTechRunRows($tr_id, $country_id, $tr_params);     
                        
            // job not completed reason
			$data['jr_sql'] = $this->db->query("
                SELECT `job_reason_id`, `name`
                FROM `job_reason`
                ORDER BY `name` ASC
            ");
            
            //get techrunrows
            $tr_sel = "
                trr.`tech_run_rows_id`,
                trr.`row_id_type`,
                trr.`row_id`,
                trr.`hidden`,
                trr.`dnd_sorted`,
                trr.`highlight_color`,
                
                trr_hc.`tech_run_row_color_id`,
                trr_hc.`hex`,
                
                j.`id` AS jid,
                j.`precomp_jobs_moved_to_booked`,
                j.`completed_timestamp`,		

                p.`property_id`,

                a.`agency_id`,
                a.`allow_upfront_billing`
            ";
            $tr_params = array(
                'sel_query' => $tr_sel,
                'sort_list' => array(
                    array(
                        'order_by' => 'p.`address_3`',
                        'sort' => 'ASC'
                    )
                ),
                'display_query' => 0,
                'dk_query_listing' => 1
            );
            $data['jr_list2'] = $this->tech_model->getTechRunRows($tr_id, $country_id, $tr_params);

        }   
        $data['is_tech_run_map'] = true; // used to load different type of google map script   
        
        //$data['title'] = "Available Door Knocks - {$staff['FirstName']} {$staff['LastName']} ".date('d/m/Y',strtotime($tr['date']));
        $data['title'] = "Available Door Knocks";
        $data['tr_id'] = $tr_id;

        //load views
        if( $staff_class_id == 6 ){ // tech
            $this->load->view('templates/inner_header_tech', $data);
        }else{
            $this->load->view('templates/inner_header', $data);
        }    
        $this->load->view($uri, $data);
        if( $staff_class_id == 6 ){ // tech
            $this->load->view('templates/inner_footer_tech', $data);
        }else{
            $this->load->view('templates/inner_footer', $data);
        }   
        

    }


    public function available_dk_admin(){

        $tr_id = $this->input->get_post('tr_id');
        $staff_class_id = $this->system_model->getStaffClassID();
        
        //$data['start_load_time'] = microtime(true);
        
        $uri = '/tech_run/available_dk_admin';
        $data['uri'] = $uri;
        
        $country_id = $this->config->item('country');

        if( $tr_id > 0 ){

            //get techrun by techrun id
            $tr_query = $this->db->select('`assigned_tech`,`date`,`start`,`end`')->from('tech_run')->where('tech_run_id', $tr_id)->get();            
            $tr = $tr_query->row_array();
            
            $data['tech_id'] = $tr['assigned_tech']; // pass data
            $tech_id = $tr['assigned_tech'];
            $data['day'] = date("d",strtotime($tr['date'])); //pass data
            $data['month'] = date("m",strtotime($tr['date'])); //pass data
            $data['year'] = date("Y",strtotime($tr['date'])); //pass data            
            $data['date'] = $tr['date']; //pass data            
            

            //get accomodation by tech run start
            $accom_query = $this->db->select('`name`,`address`,`lat`,`lng`')->from('accomodation')->where( array('accomodation_id'=> $tr['start'], 'country_id'=> $country_id) )->get();
            $accom_row = $accom_query->row_array();
            $data['accom_name'] = $accom_row['name'];
            $data['start_agency_address'] = $accom_row['address'];
            $data['start_accom_lat'] = $accom_row['lat'];
            $data['start_accom_lng'] = $accom_row['lng'];


            //get accomodation by tech run end
            $accom_query_end = $this->db->select('`name`,`address`,`lat`,`lng`')->from('accomodation')->where( array('accomodation_id'=> $tr['end'], 'country_id'=> $country_id) )->get();
            $end_acco = $accom_query_end->row_array();
            $data['end_accom_name'] = $end_acco['name'];
            $data['end_agency_address'] = $end_acco['address'];
            $data['end_accom_lat'] = $end_acco['lat'];
            $data['end_accom_lng'] = $end_acco['lng'];

            //get staff
            $staff_params = array(
                'sel_query' => "sa.StaffID, sa.FirstName, sa.LastName, sa.is_electrician, sa.ContactNumber, sa.ClassID",
                'staff_id' => $tech_id
            );
            $staff_query = $this->gherxlib->getStaffInfo($staff_params);
            $data['staff'] = $staff_query->row_array(); //pass data
            $staff = $staff_query->row_array();


            //get login user class_id
            $data['staff_classID'] = $this->db->select('ClassID')->from('staff_accounts')->where('StaffID', $this->session->staff_id)->get()->row()->ClassID;

            $data['isElectrician'] = ( $staff['is_electrician']==1 )?true:false;
            $data['tech_name'] = "{$staff['FirstName']} {$staff['LastName']}";
            $data['tech_mob1'] = $staff['ContactNumber'];

            // get country data
            $country_params = array(
                'sel_query' => 'c.`country`',
                'country_id' => $country_id
            );
            $country_sql = $this->system_model->get_countries($country_params);
            $country_row = $country_sql->row();
            $data['country_name'] = $country_row->country;

            // get accomodation list
            $data['accom_sql'] = $this->db->query("
                SELECT `accomodation_id`, `name`
                FROM `accomodation`
                WHERE `country_id` = {$country_id}
                ORDER BY `name`
			");			

            // get row colours            
            $data['trrc_sql'] = $this->db->query("
                SELECT `tech_run_row_color_id`,`color` 
                FROM  `tech_run_row_color`
                WHERE `active` = 1
            ");

            $data['start_accom'] = $tr['start'];
            $data['end_accom'] = $tr['end'];


            //get tech run rows
            $tr_sel = "DISTINCT (a.`agency_id`), a.`agency_name`";
            $tr_params = array(
                'sel_query' => $tr_sel,                
                'job_rows_only' => 1
            );
            $data['agency_keys_sql'] = $this->tech_model->getTechRunRows($tr_id, $country_id, $tr_params);     
                        
            // job not completed reason
			$data['jr_sql'] = $this->db->query("
                SELECT `job_reason_id`, `name`
                FROM `job_reason`
                ORDER BY `name` ASC
            ");
            
            //get techrunrows
            $tr_sel = "
                trr.`tech_run_rows_id`,
                trr.`row_id_type`,
                trr.`row_id`,
                trr.`hidden`,
                trr.`dnd_sorted`,
                trr.`highlight_color`,
                
                trr_hc.`tech_run_row_color_id`,
                trr_hc.`hex`,
                
                j.`id` AS jid,
                j.`precomp_jobs_moved_to_booked`,
                j.`completed_timestamp`,		

                p.`property_id`,

                a.`agency_id`,
                a.`allow_upfront_billing`
            ";
            $tr_params = array(
                'sel_query' => $tr_sel,
                'sort_list' => array(
                    array(
                        'order_by' => 'p.`address_3`',
                        'sort' => 'ASC'
                    )
                ),
                'display_query' => 0,
                'dk_query_listing' => 1
            );
            $data['jr_list2'] = $this->tech_model->getTechRunRows($tr_id, $country_id, $tr_params);
        }   
        $data['is_tech_run_map'] = true; // used to load different type of google map script   
        
        //$data['title'] = "Available Door Knocks - {$staff['FirstName']} {$staff['LastName']} ".date('d/m/Y',strtotime($tr['date']));
        $data['title'] = "Available Door Knocks";
        $data['tr_id'] = $tr_id;

        //load views
        if( $staff_class_id == 6 ){ // tech
            $this->load->view('templates/inner_header_tech', $data);
        }else{
            $this->load->view('templates/inner_header', $data);
        }    
        $this->load->view($uri, $data);
        if( $staff_class_id == 6 ){ // tech
            $this->load->view('templates/inner_footer_tech', $data);
        }else{
            $this->load->view('templates/inner_footer', $data);
        }   
        

    }

    // DK is current they are at the door, so date and tech is whoever processed it
    public function ajax_dk_complete(){

        $job_id = $this->input->post('job_id');
        $tech_id = $this->input->post('tech_id');
        $date = $this->input->post('date');

        //$tech_id = $this->session->staff_id; // current tech
        //$date = date("Y-m-d"); // current date          
        
        // update property key
        if( $job_id > 0 && $tech_id > 0 ){

            // get jobs data
            $job_sql = $this->db->query("
            SELECT                            
                j.`assigned_tech`, 
                j.`status`,
                
                p.`property_id`,
                p.`address_1` AS p_address_1,
                p.`address_2` AS p_address_2,
                p.`address_3` AS p_address_3,
                p.`state` AS p_state,
                p.`postcode` AS p_postcode
            FROM `jobs` AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            WHERE `id` = {$job_id}
            ");                    
            $job_row = $job_sql->row(); 
            
            $job_status = $job_row->status;         
            
            // update job 
            $this->db->query("
            UPDATE `jobs`
            SET 
                `status` = 'Booked',
                `door_knock` = 1,
                `booked_with`	= 'Agent',
                `assigned_tech` = {$tech_id},
                `booked_by` = {$tech_id},
                `date` = '{$date}'
            WHERE `id` = {$job_id}
            ");

            $log_title = 32; // Door Knock Booked
            $log_details = "This job was updated from <b>{$job_status}</b> to <b>Booked</b> during tech door knocking";
            $log_params = array(
                'title' => $log_title,  
                'details' => $log_details,
                'display_in_vjd' => 1,
                'created_by_staff' => $this->session->staff_id,
                'job_id' => $job_id
            );
            $this->system_model->insert_log($log_params);
            
        }        

    }


    public function ajax_dk_complete_by_bulk(){

        $job_id_arr = $this->input->post('job_id_arr');
        $tech_id = $this->input->post('tech_id');
        $date = $this->input->post('date');
        
        $logged_in_user = $this->session->staff_id;
        
        foreach( $job_id_arr as $job_id ){
        
            // update property key
            if( $job_id > 0 && $tech_id > 0 ){

                // get jobs data
                $job_sql = $this->db->query("
                SELECT                            
                    j.`assigned_tech`, 
                    j.`status`,
                    
                    p.`property_id`,
                    p.`address_1` AS p_address_1,
                    p.`address_2` AS p_address_2,
                    p.`address_3` AS p_address_3,
                    p.`state` AS p_state,
                    p.`postcode` AS p_postcode
                FROM `jobs` AS j
                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                WHERE `id` = {$job_id}
                ");                    
                $job_row = $job_sql->row(); 
                
                $job_status = $job_row->status;         
                
                // update job 
                $this->db->query("
                UPDATE `jobs`
                SET 
                    `status` = 'Booked',
                    `door_knock` = 1,
                    `booked_with`	= 'Agent',
                    `assigned_tech` = {$tech_id},
                    `booked_by` = {$logged_in_user},
                    `date` = '{$date}',
                    `tech_notes` = 'Door Knock'
                WHERE `id` = {$job_id}
                ");

                $log_title = 32; // Door Knock Booked
                $log_details = "This job was updated from <b>{$job_status}</b> to <b>Booked</b> from DKs Page";
                $log_params = array(
                    'title' => $log_title,  
                    'details' => $log_details,
                    'display_in_vjd' => 1,
                    'created_by_staff' => $this->session->staff_id,
                    'job_id' => $job_id
                );
                $this->system_model->insert_log($log_params);
                
            } 
        
        }

    }



    // DK is current they are at the door, so date and tech is whoever processed it
    public function ajax_dk_utc(){

        $job_id = $this->input->post('job_id');
        $tech_id = $this->input->post('tech_id');
        $job_reason = $this->input->post('job_reason');
        $reason_comment = $this->input->post('reason_comment');     
        
        // update property key
        if( $job_id > 0 && $tech_id > 0 && $job_reason > 0 ){
                        
            $mjnc_params = array(
                'job_id' => $job_id,
                'tech_id' => $tech_id,
                'job_reason' => $job_reason,
                'reason_comment' => $reason_comment
            );
            $this->tech_run_model->mark_job_not_completed($mjnc_params);
            
        }        

    }


    public function set()
    {

        $this->load->model('properties_model');

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Set Tech Run";
        $uri = "/tech_run/set";
        $data['uri'] = $uri;
        
        $country_id = $this->config->item('country');
        $tr_id = $this->input->get_post('tr_id');
        $data['tr_id'] = $tr_id;

        $today_full = date('Y-m-d H:i:s');
        $this_day = date('D');

        // technicians
        $data['tech_sql'] = $this->db->query("
        SELECT 
            sa.`StaffID`, 
            sa.`FirstName`, 
            sa.`LastName`, 
            sa.`is_electrician`, 
            sa.`active` AS sa_active
        FROM `staff_accounts` AS sa
        LEFT JOIN `country_access` AS ca ON sa.`StaffID` = ca.`staff_accounts_id`
        WHERE ca.`country_id` = {$country_id}
        AND sa.`Deleted` = 0
        AND sa.`ClassID` = 6
        AND sa.`active` = 1
        ORDER BY sa.`FirstName` ASC, sa.`LastName` ASC
        ");

        // booking staff
        $data['booking_staff_sql'] = $this->db->query("
        SELECT 
            sa.`StaffID`,
            sa.`FirstName`,
            sa.`LastName`
        FROM `staff_accounts` AS sa
        LEFT JOIN `staff_classes` AS sc ON sa.`ClassID` = sc.`ClassID`
        INNER JOIN `country_access` AS ca ON sa.`StaffID` = ca.`staff_accounts_id`
        WHERE sa.`Deleted` = 0
        AND sa.`active` = 1
        AND ca.`country_id` = {$country_id}
        ORDER BY sa.`FirstName` ASC, sa.`LastName` ASC
        ");

        // accomodation
        $data['acco_sql'] = $this->db->query("
        SELECT 
            `accomodation_id`,
            `name`
        FROM `accomodation`
        ORDER BY `name` ASC
        ");

        // tech run row color
        $data['trr_color_sql'] = $this->db->query("
        SELECT 
            `tech_run_row_color_id`,
            `hex`,
            `color`
        FROM  `tech_run_row_color`
        WHERE `active` = 1
        ");

        // job types
        $data['job_type_sql'] = $this->db->query("
        SELECT `job_type`
        FROM `job_type`
        ");        

        $tbb_query = "
        FROM  `jobs` AS j
        LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN `postcode` AS pc ON p.`postcode` = pc.`postcode`
        LEFT JOIN `sub_regions` AS sr ON pc.`sub_region_id` = sr.`sub_region_id`
        LEFT JOIN `regions` AS r ON sr.`region_id` = r.`regions_id`
        LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id`
        WHERE j.`status` = 'To be Booked'
        AND j.`del_job` = 0
        AND p.`deleted` = 0
        AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )	
        AND a.`status` =  'active'
        AND a.`deleted` = 0		
        ";
        $data['tbb_query'] = $tbb_query;        

        // get distinct state
        $dist_state_sql = $this->db->query("
        SELECT COUNT(j.`id`) AS jcount, r.`region_state`
        {$tbb_query}
        AND r.`region_state` != ''
        AND r.`region_state` IS NOT NULL
        GROUP BY r.`region_state`
        ORDER BY r.`region_state`						
        ");

        $state_arr = [];
        $dist_state_obj = $dist_state_sql->result();
        foreach( $dist_state_obj as $dist_state_row ) {
            $state_arr[] = "'{$dist_state_row->region_state}'";
        }

        $state_unique = array_unique($state_arr);

        // region
        if( count($state_unique) > 0 ){

            $state_unique_imp = implode(',',$state_unique);

            $region_sql_str = "
            SELECT COUNT(j.`id`) AS jcount, r.`regions_id`, r.`region_name`, r.`region_state`									
            {$tbb_query}											
            AND r.`region_state` IN({$state_unique_imp})
            AND r.`status` = 1
            GROUP BY r.`regions_id`
            ORDER BY r.`region_name` ASC
            ";
            $region_sql = $this->db->query($region_sql_str);
            $region_sql_res = $region_sql->result();

        }  

        $region_arr = [];
        foreach( $region_sql_res as $region_row ){
            $region_arr[] = $region_row->regions_id;
        }

        $region_unique = array_unique($region_arr);

        if( count($region_unique) > 0 ){

            $region_unique_imp = implode(',',$region_unique);

            // sub region
            $sub_region_sql_str = "
            SELECT COUNT(j.`id`) AS jcount, `sr`.`sub_region_id`, `sr`.`subregion_name`, sr.`region_id`
            {$tbb_query}
            AND sr.`region_id` IN({$region_unique_imp})
            AND sr.`active` = 1
            GROUP BY sr.`sub_region_id`
            ORDER BY sr.`subregion_name` ASC																					
            ";
            $sub_region_sql = $this->db->query($sub_region_sql_str);
            $sub_region_res = $sub_region_sql->result();
            
        }   
        
        foreach( $dist_state_obj as $dist_state_row ) { // state

            $region_index = 0;

            foreach( $region_sql_res as $region_row ) { // region loop

                if( $dist_state_row->region_state == $region_row->region_state ) { // compare state

                    $dist_state_row->region_arr_obj[$region_index] = $region_row;

                    $sub_region_index = 0;
                    foreach( $sub_region_res as $sub_region_row ){ // sub region

                        if( $region_row->regions_id == $sub_region_row->region_id ){ // compare region

                            $dist_state_row->region_arr_obj[$region_index]->sub_region_arr_obj[$sub_region_index] = $sub_region_row;

                            $sub_region_index++;

                        }

                    }

                    $region_index++;

                }

            }

        }
      
        // send data to view
        $data['dist_state_obj'] = $dist_state_obj;

        if( $tr_id > 0 ){

            // get tech working days and hours
            $check_tech_sql = $this->db->query("
            SELECT 
                tech_sa.`working_days`,
                twh.`working_hours`
            FROM `tech_run` AS tr
            LEFT JOIN `staff_accounts` AS tech_sa ON tr.`assigned_tech` = tech_sa.`StaffID`
            LEFT JOIN `tech_working_hours` AS twh ON tech_sa.`StaffID` = twh.`staff_id`
            WHERE tr.`tech_run_id` = {$tr_id}
            AND (
                tr.`working_hours` = '' OR
                tr.`working_hours` IS NULL
            )
            ");
            $check_tech_row = $check_tech_sql->row();
            $working_days_arr = explode(',',$check_tech_row->working_days);

            // update working hours from user data if none and only if it was working that day
            if( $check_tech_sql->num_rows() > 0 && in_array($this_day, $working_days_arr) && $tr_id > 0 ){

                $update_data = array(
                    'working_hours' => $check_tech_row->working_hours
                );                
                $this->db->where('tech_run_id', $tr_id);
                $this->db->update('tech_run', $update_data);              

            }

             // get tech run
            $tech_run_sql = $this->db->query("
            SELECT 
                tr.`assigned_tech`,
                tr.`date`,
                tr.`start`,
                tr.`end`,
                tr.`notes`,
                tr.`run_set`,
                tr.`sub_regions`,
                tr.`notes_updated_by`,
                tr.`notes_updated_ts`,
                tr.`run_set`,
                tr.`run_coloured`,
                tr.`ready_to_book`,
                tr.`first_call_over_done`,
                tr.`run_reviewed`,
                tr.`finished_booking`,
                tr.`additional_call_over`,
                tr.`additional_call_over_done`,
                tr.`ready_to_map`,
                tr.`run_complete`,                
                tr.`no_more_jobs`,
                tr.`show_hidden`,
                tr.`working_hours`,

                tech_sa.`FirstName` AS tech_sa_fname,
                tech_sa.`LastName` AS tech_sa_lname,
                tech_sa.`is_electrician` AS is_tech_elec,

                nub_sa.`FirstName` AS nub_sa_fname,
                nub_sa.`LastName` AS nub_sa_lname
            FROM `tech_run` AS tr
            LEFT JOIN `staff_accounts` AS tech_sa ON tr.`assigned_tech` = tech_sa.`StaffID`
            LEFT JOIN `staff_accounts` AS nub_sa ON tr.`notes_updated_by` = nub_sa.`StaffID`
            WHERE tr.`tech_run_id` = {$tr_id}
            ");

            $has_tech_run = ( $tech_run_sql->num_rows() > 0 )?true:false;
            $data['has_tech_run'] = $has_tech_run;

            if( $has_tech_run == true ){                        

                $tech_run_row = $tech_run_sql->row();
                $tech_id = $tech_run_row->assigned_tech;
                $date = $tech_run_row->date;

                $tech_name = $this->system_model->formatStaffName($tech_run_row->tech_sa_fname,$tech_run_row->tech_sa_lname);
                $run_day = date('D',strtotime($tech_run_row->date));
                $run_dmy = date('d/m/Y',strtotime($tech_run_row->date));

                // page title
                $data['title'] = "{$tech_name} {$run_day} {$run_dmy}";

                $data['notes_updated_by'] = $this->system_model->formatStaffName($tech_run_row->nub_sa_fname,$tech_run_row->nub_sa_lname);
                $data['notes_ts'] = ( $tech_run_row->notes_updated_ts != '' )?date('d/m/Y H:i',strtotime($tech_run_row->notes_updated_ts)):null;

                $data['tech_run_row'] = $tech_run_row;                

                // tech run job type filter
                $hide_job_types_sql = $this->db->query("
                SELECT `job_type`
                FROM `tech_run_hide_job_types`
                WHERE `tech_run_id` = {$tr_id}
                ");

                $job_type_filter_arr = [];
                foreach( $hide_job_types_sql->result() as $hide_job_types_row ){
                    $job_type_filter_arr[] = "'{$hide_job_types_row->job_type}'";
                }

                $job_type_filter_sql = null;
                $job_type_filter_imp = null;
                if( count($job_type_filter_arr) > 0 ){
                    $job_type_filter_imp = implode(",",$job_type_filter_arr);
                    $job_type_filter_sql = "AND j.`job_type` NOT IN($job_type_filter_imp)";
                }
                

                $data['hide_job_types_sql'] = $hide_job_types_sql;

                // find new jobs
                if( $tech_run_row->sub_regions != '' ){
                        
                    // get postcodes
                    $postcode_sql = $this->db->query("
                    SELECT `postcode`
                    FROM `postcode`
                    WHERE `sub_region_id` IN($tech_run_row->sub_regions)
                    AND `deleted` = 0
                    ");       

                    $postcode_arr = [];
                    foreach( $postcode_sql->result() as $postcode_row ){
                        $postcode_arr[] = $postcode_row->postcode;
                    }

                    // implode postcode
                    $postcode_imp = null;
                    if( count($postcode_arr) > 0 ){
                        $postcode_imp = implode(",",$postcode_arr);
                    }                

                    if( $postcode_imp != '' ){

                        // get jobs to be inserted to tech run rows
                        $get_jobs_sql = $this->db->query("
                        SELECT 
                            j.`id` AS jid,

                            p.`property_id`,
                            p.`lat`,
                            p.`lng`	
                        FROM jobs AS j
                        LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
                        LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id`
                        WHERE j.`del_job` = 0
                        AND p.`deleted` = 0
                        AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
                        AND a.`status` = 'active'
                        AND a.`deleted` = 0		
                        AND a.`country_id` = {$country_id}	
                        {$job_type_filter_sql}
                        AND (

                            (
                                ( 
                                    j.`status` = 'To Be Booked' 
                                    OR j.`status` = 'Booked' 
                                    OR j.`status` = 'DHA' 
                                    OR j.`status` = 'Escalate'
                                    OR j.`status` = 'On Hold' 
                                    OR j.`status` = 'Allocate'
                                ) 
                                AND j.`assigned_tech` = {$tech_id} 
                                AND j.`date` = '{$date}'

                            ) OR (

                                p.`postcode` IN ( {$postcode_imp} )	
                                AND (
                                    j.`status` = 'To Be Booked' 
                                    OR j.`status` = 'Booked' 
                                    OR j.`status` = 'DHA'
                                    OR j.`status` = 'Escalate'
                                    OR j.`status` = 'On Hold' 
                                    OR j.`status` = 'Allocate'
                                )
                                AND (
                                    j.`assigned_tech` = {$tech_id} 
                                    OR j.`assigned_tech` = 0
                                    OR j.`assigned_tech` IS NULL
                                )
                                AND(
                                    j.`date` = '{$date}'
                                    OR j.`date` IS NULL
                                    OR j.`date` = '0000-00-00'
                                    OR j.`date` = ''
                                )

                            )

                        )		                        
                        AND j.`id` NOT IN(
                            SELECT trr.`row_id`
                            FROM  `tech_run_rows` AS trr 
                            WHERE  trr.`row_id_type` =  'job_id'
                            AND trr.`status` = 1
                            AND trr.`tech_run_id` = {$tr_id}
                        )
                        ORDER BY j.`sort_order` ASC
                        ");      
                        //echo $this->db->last_query();          
                
                        if( $get_jobs_sql->num_rows() > 0 ){
                            
                            foreach( $get_jobs_sql->result() as $job_row ){

                                // update property coordinates 
                                $coor_params = array(
                                    'property_id' => $job_row->property_id
                                );
                                $this->properties_model->update_coordinates($coor_params);
                
                                // insert tech run row
                                $insert_data2 = array(
                                    'tech_run_id' => $tr_id,
                                    'row_id_type' => 'job_id',
                                    'row_id' => $job_row->jid,
                                    'sort_order_num' => 999999,
                                    'created_date' => $today_full,
                                    'status' => 1
                                );            
                                $this->db->insert('tech_run_rows', $insert_data2);
                    
                            }

                            $data['new_jobs_count'] = $get_jobs_sql->num_rows();

                        }                                                            

                    }     
                    
                }                

                // get calendar data
                $cal_sql = $this->db->query("
                SELECT 
                    `calendar_id`,
                    `booking_staff`,
                    `accomodation`,
                    `accomodation_id`,
                    `region`
                FROM `calendar`
                WHERE staff_id = {$tech_id}
                AND `date_start` = '{$date}'
                AND `date_finish` = '{$date}'
                ORDER BY `calendar_id` DESC
                ");
                $data['cal_row'] = $cal_sql->row();

                // booked
                $tot_jobs_sql = $this->db->query("
                SELECT count( j.`id` ) AS jcount
                FROM `jobs` AS j
                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                WHERE j.`assigned_tech` ={$tech_id}
                AND j.`status` = 'Booked'
                AND j.`date` = '".$date."'
                AND p.`deleted` = 0
                AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
                AND a.`status` = 'active'
                AND a.`deleted` = 0
                AND j.`del_job` = 0
                AND a.`country_id` = {$country_id}
                ");
                $data['tot_jobs_count'] = $tot_jobs_sql->row()->jcount;

                // door knocks
                $tot_dk_sql = $this->db->query("
                SELECT count( j.`id` ) AS jcount
                FROM `jobs` AS j
                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                WHERE j.`assigned_tech` ={$tech_id}
                AND j.`status` = 'Booked'
                AND j.`door_knock` = 1
                AND j.`date` = '".$date."'
                AND p.`deleted` = 0
                AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
                AND a.`status` = 'active'
                AND a.`deleted` = 0
                AND j.`del_job` = 0
                AND a.`country_id` = {$country_id}
                ");
                $data['tot_dk_count'] = $tot_dk_sql->row()->jcount; 

                // billable
                $tot_bill_sql = $this->db->query("
                SELECT count( j.`id` ) AS jcount
                FROM `jobs` AS j
                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                WHERE j.`assigned_tech` ={$tech_id}
                AND j.`status` = 'Booked'
                AND j.`door_knock` = 0
                AND j.`date` = '".$date."'
                AND p.`deleted` = 0
                AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
                AND a.`status` = 'active'
                AND a.`deleted` = 0
                AND j.`del_job` = 0
                AND(
                    j.`job_type` = 'Yearly Maintenance'
                    OR j.`job_type` = '240v Rebook'
                    OR j.`job_type` = 'Once-off'
                )
                AND p.`deleted` =0
                AND a.`country_id` = {$country_id}
                ");
                $data['tot_bill_count'] = $tot_bill_sql->row()->jcount;  

                // tech run logs
                $data['tech_run_logs_sql'] = $this->db->query("
                SELECT 
                    trl.`description`,
                    trl.`created`,

                    sa.`FirstName`,
                    sa.`LastName`
                FROM `tech_run_logs` AS trl
                LEFT JOIN `staff_accounts` AS sa ON trl.`created_by` = sa.`StaffID`
                WHERE trl.`tech_run_id` = {$tr_id}
                ");

                // suppliers
                $data['supp_sql'] = $this->db->query("
                SELECT 
                    `suppliers_id`,
                    `company_name`
                FROM `suppliers`
                WHERE `country_id` = {$country_id}
                AND `status` = 1
                ORDER BY `company_name` ASC
                ");

                // start accomodation
                $accomodation_sql = $this->db->query("
                    SELECT 
                        `name`,
                        `phone`,
                        `address`
                    FROM `accomodation`
                    WHERE `accomodation_id` = {$tech_run_row->start}
                    AND `country_id` = {$country_id}
                ");
                $data['start_acco_row'] = $accomodation_sql->row();

                // end accomodation
                $accomodation_sql = $this->db->query("
                    SELECT 
                        `name`,
                        `phone`,
                        `address`
                    FROM `accomodation`
                    WHERE `accomodation_id` = {$tech_run_row->end}
                    AND `country_id` = {$country_id}
                ");
                $data['end_acco_row'] = $accomodation_sql->row();


                // body query
                $body_sql_str = "
                FROM `tech_run_rows` AS trr
                LEFT JOIN `tech_run_row_color` AS trr_hc ON trr.`highlight_color` = trr_hc.`tech_run_row_color_id`
                LEFT JOIN `tech_run` AS tr ON trr.`tech_run_id` =  tr.`tech_run_id`
                LEFT JOIN `jobs` AS j ON ( trr.`row_id` = j.`id` AND trr.`row_id_type` = 'job_id' )  
                LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                LEFT JOIN `postcode` AS pc ON p.`postcode` = pc.`postcode`
                LEFT JOIN `sub_regions` AS sr ON pc.`sub_region_id` = sr.`sub_region_id`
                LEFT JOIN `regions` AS r ON sr.`region_id` = r.`regions_id`
                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`    
                LEFT JOIN `tech_run_keys` AS trk ON ( trr.`row_id` = trk.`tech_run_keys_id` AND trr.`row_id_type` = 'keys_id' )  
                LEFT JOIN `agency` AS key_a ON trk.`agency_id` = key_a.`agency_id`
                LEFT JOIN `tech_run_suppliers` AS trs ON ( trr.`row_id` = trs.`tech_run_suppliers_id` AND trr.`row_id_type` = 'supplier_id' )
                LEFT JOIN `suppliers` AS sup ON trs.`suppliers_id` = sup.`suppliers_id`
                ";

                if( $tech_run_row->run_complete == 1 ){

                    $dynamic_filter = "
                    AND(
                        j.`status` = 'Booked'
                        OR j.`status` = 'Pre Completion'
                        OR j.`status` = 'Merged Certificates'
                        OR j.`status` = 'Completed'
                        OR (
                            j.`status` = 'To Be Booked' AND
                            j.`door_knock` = 1	
                        )
                    )
                    AND (
                        j.`assigned_tech` = {$tech_run_row->assigned_tech} 
                        AND j.`date` = '{$tech_run_row->date}'
                    )
                    ";

                }else{ // default

                    $dynamic_filter = "
                    AND (
                        j.`status` = 'To Be Booked'	
                        OR j.`status` = 'Booked' 
                        OR j.`status` = 'DHA'
                        OR j.`status` = 'Escalate'
                        OR j.`status` = 'On Hold' 
                        OR j.`status` = 'Allocate'
                    )
                    AND ( 
                        j.`assigned_tech` = {$tech_run_row->assigned_tech} 
                        OR j.`assigned_tech` = 0
                        OR j.`assigned_tech` IS NULL 
                    ) 
                    AND(
                        j.`date` = '{$tech_run_row->date}'
                        OR j.`date` IS NULL
                        OR j.`date` = '0000-00-00'
                        OR j.`date` = ''
                    )
                    ";

                }

                // query filters
                $where_sql_str = "
                WHERE tr.`tech_run_id` = {$tr_id}
                AND tr.`country_id` = {$country_id}                  
                AND trr.`hidden` = 0 
                AND (
                    (
                        trr.`row_id_type` = 'job_id' 
                        AND j.`del_job` = 0
                        AND p.`deleted` = 0
                        AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
                        AND a.`deleted` = 0    
                        AND a.`status` = 'active'                                         
                        AND a.`country_id` = {$country_id}                        		
                        {$dynamic_filter}                        
                    )
                    OR (
                        trr.`row_id_type` = 'keys_id' AND tr.`tech_run_id` = {$tr_id}
                    )
                    OR (
                        trr.`row_id_type` = 'supplier_id' AND tr.`tech_run_id` = {$tr_id}
                    )    
                )                    
                ";

                // get distinct agency based on main query listing
                $data['sel_agency_jobs_sql'] = $this->db->query("        
                SELECT DISTINCT (a.`agency_id`), a.`agency_name`    
                {$body_sql_str}
                {$where_sql_str}    
                {$job_type_filter_sql}                     
                ORDER BY a.`agency_name` ASC
                ");  
                
                // get distinct job types
                $data['distinct_job_type_sql'] = $this->db->query("        
                SELECT DISTINCT (j.`job_type`)
                {$body_sql_str}
                {$where_sql_str}                         
                ORDER BY a.`agency_name` ASC
                ");  

                // get tech run list
                $data['tech_run_row_sql'] = $this->db->query("
                SELECT 
                trr.`tech_run_rows_id`,
                trr.`row_id_type`,
                trr.`row_id`,
                trr.`hidden`,
                trr.`dnd_sorted`,
                trr.`highlight_color`,

                trr_hc.`tech_run_row_color_id`,
                trr_hc.`hex`,	
                trr.`sort_order_num`,	

                j.`id` AS jid, 
                j.`sort_order`, 
                j.`job_type`, 
                j.time_of_day, 
                j.`tech_notes`, 
                j.`status` AS j_status, 
                j.`completed_timestamp`, 
                j.`job_reason_id`, 
                j.`ts_completed`, 
                j.`service` AS j_service, 
                j.`urgent_job`, 
                j.`created`, 
                j.`comments` AS j_comments,
                j.`key_access_required`,
                j.`date` AS jdate,
                j.`door_knock`,
                j.`start_date`,
                j.`due_date`,
                j.`unavailable`,
                j.`unavailable_date`,
                j.`job_entry_notice`,
                j.`preferred_time`,
                j.`call_before`,
                j.`call_before_txt`,
                j.`booked_with`,
                j.`survey_ladder`,
                j.`job_priority`,
                j.`is_eo`,
                j.`property_vacant`,
                DATEDIFF(CURDATE(), Date(j.`created`)) AS age,
                DATEDIFF(Date(p.`retest_date`), CURDATE()) AS deadline,

                p.`property_id`, 
                p.`address_1` AS p_address_1, 
                p.`address_2` AS p_address_2, 
                p.`address_3` AS p_address_3, 
                p.`state` AS p_state, 
                p.`postcode` AS p_postcode, 
                p.`key_number`,
                p.`qld_new_leg_alarm_num`,
                p.`lat` AS p_lat, 
                p.`lng` AS p_lng,
                p.`tenant_firstname1`,
                p.`tenant_lastname1`,
                p.`tenant_firstname2`,
                p.`tenant_lastname2`,
                p.`tenant_email1`,
                p.`tenant_email2`,
                p.`tenant_mob1`,
                p.`tenant_mob2`,
                p.`no_keys`,
                p.`comments` AS p_comments,
                p.`no_en`,
                p.`no_dk`,
                p.`requires_ppe`,
                p.`service_garage`,
                p.`holiday_rental`,
                DATEDIFF(Date(p.`retest_date`), CURDATE()) AS deadline,

                sr.`subregion_name`,

                a.`agency_id`, 
                a.`agency_name`, 
                a.`address_1` AS a_address_1, 
                a.`address_2` AS a_address_2, 
                a.`address_3` AS a_address_3, 
                a.`state` AS a_state, 
                a.`postcode` AS a_postcode, 
                a.`phone` AS a_phone,
                a.`allow_dk`,
                a.`key_allowed`,
                a.`agency_hours`,
                a.`electrician_only`,
                a.`send_entry_notice`,
                a.`allow_en`,
                a.`high_touch`,
                a.`allow_upfront_billing`,

                trk.`tech_run_keys_id`, 
                trk.`action` AS trk_action, 
                trk.`number_of_keys`, 
                trk.`agency_staff`, 
                trk.`completed` AS trk_completed, 
                trk.`completed_date`, 
                trk.`sort_order`,

                key_a.`agency_id` AS key_a_agency_id, 
                key_a.`agency_name` AS key_a_agency_name, 
                key_a.`address_1` AS key_a_address_1, 
                key_a.`address_2` AS key_a_address_2, 
                key_a.`address_3` AS key_a_address_3, 
                key_a.`state` AS key_a_state, 
                key_a.`postcode` AS key_a_postcode, 
                key_a.`phone`, 
                key_a.`agency_hours`, 
                key_a.`lat`, 
                key_a.`lng`,

                trs.`tech_run_suppliers_id`,

                sup.`suppliers_id`, 
                sup.`company_name`, 
                sup.`address` AS sup_address, 
                sup.`phone`, 
                sup.`lat`, 
                sup.`lng`, 
                sup.`on_map`	

                {$body_sql_str}      		
                {$where_sql_str} 
                {$job_type_filter_sql}
               
                
                ORDER BY trr.`sort_order_num` ASC, p.`address_3` ASC, p.`address_2` ASC
                ");

                // COPIED FROM OLD STR
                // First Natioanl Agency
                $fn_agency_arr = $this->system_model->get_fn_agencies();
                $data['fn_agency_main'] = $fn_agency_arr['fn_agency_main'];
                $data['fn_agency_sub'] =  $fn_agency_arr['fn_agency_sub'];
                $data['fn_agency_sub_imp'] = implode(",",$data['fn_agency_sub']);

                // Vision Real Estate
                $vision_agency_arr = $this->system_model->get_vision_agencies();
                $data['vision_agency_main'] = $vision_agency_arr['vision_agency_main'];
                $data['vision_agency_sub'] =  $vision_agency_arr['vision_agency_sub'];
                $data['vision_agency_sub_imp'] = implode(",",$data['vision_agency_sub']);

                $data['page_query'] = $this->db->last_query();               

            }                

        }                     

        $this->load->view('templates/inner_header', $data);
        $this->load->view('tech_run/set', $data);
        $this->load->view('templates/inner_footer', $data);

    }

    public function create_or_update(){

        $this->load->model('properties_model');

        $tr_id = $this->input->get_post('tr_id');

        $assigned_tech = $this->input->get_post('assigned_tech');        
        $date = ( $this->input->get_post('date') !='' )?$this->system_model->formatDate($this->input->get_post('date')):null;
        $sub_region_ms_tag = $this->input->get_post('sub_region_ms_tag');
        $start_point = $this->input->get_post('start_point');
        $end_point = $this->input->get_post('end_point');       
        $calendar = $this->input->get_post('calendar');
        $accomodation = $this->input->get_post('accomodation');
        $accomodation_id = $this->input->get_post('accomodation_id');
        $booking_staff = $this->input->get_post('booking_staff');
        $calendar_id = $this->input->get_post('calendar_id');
        $job_type_arr = $this->input->get_post('job_type_arr');
        $working_hours = $this->input->get_post('working_hours');

        $country_id = $this->config->item('country');        
        $today_full = date('Y-m-d H:i:s');
        $this_day = date('D');

        // update accomodation coordinates 
        // start
        $coor_params = array(
            'acco_id' => $start_point
        );
        $this->properties_model->update_coordinates($coor_params);

        // end
        $coor_params = array(
            'acco_id' => $end_point
        );
        $this->properties_model->update_coordinates($coor_params);        

        if( count($sub_region_ms_tag) > 0 ){

            // implode sub regions
            $sub_regions_imp = implode(",",$sub_region_ms_tag); 

            if( $tr_id > 0 ){ // update

                $update_data = array(
                    'assigned_tech' => $assigned_tech,
                    'date' => $date,        
                    'start' => $start_point,
                    'end' => $end_point,               
                    'country_id' => $country_id,
                    'sub_regions' => $sub_regions_imp,
                    'working_hours' => $working_hours
                );                     
                $this->db->where('tech_run_id', $tr_id);
                $this->db->update('tech_run', $update_data);

                // clear tech run rows
                $this->db->query("
                DELETE trr
                FROM `tech_run_rows` AS trr
                LEFT JOIN `jobs` AS j ON trr.`row_id` = j.`id` 
                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                LEFT JOIN `tech_run_row_color` AS trr_hc ON trr.`highlight_color` = trr_hc.`tech_run_row_color_id`
                WHERE trr.`tech_run_id` = {$tr_id}
                AND trr.`row_id_type` =  'job_id'
                AND j.`status` NOT IN('Booked','Pre Completion','Merged Certificates','Completed')
                AND trr.`highlight_color` IS NULL
                ");

            }else{ // insert

                // create tech run
                $insert_data = array(
                'assigned_tech' => $assigned_tech,
                'date' => $date,        
                'start' => $start_point,
                'end' => $end_point,
                'sorted' => 1,
                'country_id' => $country_id,
                'sub_regions' => $sub_regions_imp
                );    

                // get tech working days and hours
                $tech_sql = $this->db->query("
                SELECT 
                    sa.`working_days`,
                    twh.`working_hours`
                FROM `staff_accounts` AS sa
                LEFT JOIN `tech_working_hours` AS twh ON sa.`StaffID` = twh.`staff_id`
                WHERE sa.`StaffID` = {$assigned_tech}
                ");
                $tech_row = $tech_sql->row();

                $working_days_arr = explode(',',$tech_row->working_days);
                
                // insert working hours from users data when tech is active on this day
                if( in_array($this_day, $working_days_arr) ){
                    $insert_data['working_hours'] = $tech_row->working_hours;
                }
                
                $this->db->insert('tech_run', $insert_data);    
                $tr_id = $this->db->insert_id(); // tech run ID

            }       

            // get postcodes
            $postcode_sql = $this->db->query("
            SELECT `postcode`
            FROM `postcode`
            WHERE `sub_region_id` IN($sub_regions_imp)
            AND `deleted` = 0
            ");       

            $postcode_arr = [];
            foreach( $postcode_sql->result() as $postcode_row ){
                $postcode_arr[] = $postcode_row->postcode;
            }

            // implode postcode
            $postcode_imp = null;
            if( count($postcode_arr) > 0 ){
                $postcode_imp = implode(",",$postcode_arr);
            }                           	

            if( $postcode_imp != '' ){

                // get jobs to be inserted to tech run rows
                $job_sql_str = "
                SELECT 
                    j.`id` AS jid, 
                    j.`job_type`,
                    j.`status` AS jstatus,
                    j.`created` AS jcreated,

                    p.`property_id`,
                    p.`state` AS p_state	
                FROM jobs AS j
                LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
                LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id`
                WHERE j.`del_job` = 0
                AND p.`deleted` = 0
                AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
                AND a.`status` = 'active'
                AND a.`deleted` = 0		
                AND a.`country_id` = {$country_id}			
                AND p.`postcode` IN ( {$postcode_imp} )	
                AND (
                    j.`status` = 'To Be Booked' 
                    OR j.`status` = 'Booked' 
                    OR j.`status` = 'DHA'
                    OR j.`status` = 'Escalate'
                    OR j.`status` = 'On Hold' 
                    OR j.`status` = 'Allocate'
                )
                AND (
                    j.`assigned_tech` = {$assigned_tech} 
                    OR j.`assigned_tech` = 0
                    OR j.`assigned_tech` IS NULL
                )
                AND(
                    j.`date` = '{$date}'
                    OR j.`date` IS NULL
                    OR j.`date` = '0000-00-00'
                    OR j.`date` = ''
                )
                ORDER BY j.`created` ASC
                ";

                $get_jobs_sql = $this->db->query($job_sql_str);                     
                                
                $state_prio_arr = [];
                
                foreach( $get_jobs_sql->result() as $job_row ){
                   
                    // job priorities sort
                    if( $job_row->p_state == 'ACT' ){
                        
                        if( $job_row->job_type == 'Fix or Replace' ){

                            $state_prio_arr[0][1][] = $job_row;
                           
                        }else if( $job_row->job_type == 'Change of Tenancy' || $job_row->job_type == 'Lease Renewal' ){

                            $state_prio_arr[0][1][] = $job_row;
                            
                        }else if( $job_row->job_type == '240v Rebook' ){

                            $state_prio_arr[0][2][] = $job_row;
                            
                        }else if( $job_row->job_type == 'Yearly Maintenance' || $job_row->job_type == 'Once-off' ){

                            $state_prio_arr[0][3][] = $job_row;
                           
                        }else{

                            $state_prio_arr[0][4][] = $job_row;

                        }                        

                    }else if( $job_row->p_state == 'NSW' ){

                        if( $job_row->job_type == 'Fix or Replace' ){

                            $state_prio_arr[1][0][] = $job_row;
                           
                        }else if( $job_row->job_type == 'Change of Tenancy' || $job_row->job_type == 'Lease Renewal' ){

                            $state_prio_arr[1][1][] = $job_row;
                            
                        }else if( $job_row->job_type == '240v Rebook' ){

                            $state_prio_arr[1][2][] = $job_row;
                            
                        }else if( $job_row->job_type == 'Yearly Maintenance' || $job_row->job_type == 'Once-off' ){

                            $state_prio_arr[1][3][] = $job_row;
                           
                        }else{

                            $state_prio_arr[1][4][] = $job_row;
                            
                        } 
                       
                    }else if( $job_row->p_state == 'QLD' ){

                        if( $job_row->job_type == 'Fix or Replace' ){

                            $state_prio_arr[2][0][] = $job_row;
                           
                        }else if( $job_row->job_type == 'Change of Tenancy' || $job_row->job_type == 'Lease Renewal' ){

                            $state_prio_arr[2][1][] = $job_row;
                            
                        }else if( $job_row->job_type == '240v Rebook' ){

                            $state_prio_arr[2][2][] = $job_row;
                            
                        }else if( $job_row->job_type == 'Yearly Maintenance' || $job_row->job_type == 'Once-off' ){

                            $state_prio_arr[2][3][] = $job_row;
                           
                        }else{

                            $state_prio_arr[2][4][] = $job_row;
                            
                        }
                        
                    }else if( $job_row->p_state == 'SA' ){

                        if( $job_row->job_type == 'Fix or Replace' ){

                            $state_prio_arr[3][0][] = $job_row;
                           
                        }else if( $job_row->job_type == 'Change of Tenancy' || $job_row->job_type == 'Lease Renewal' ){

                            $state_prio_arr[3][1][] = $job_row;
                            
                        }else if( $job_row->job_type == '240v Rebook' ){

                            $state_prio_arr[3][2][] = $job_row;
                            
                        }else if( $job_row->job_type == 'Yearly Maintenance' || $job_row->job_type == 'Once-off' ){

                            $state_prio_arr[3][3][] = $job_row;
                           
                        }else{

                            $state_prio_arr[3][4][] = $job_row;
                            
                        }
                       
                    }else{

                        if( $job_row->job_type == 'Fix or Replace' ){

                            $state_prio_arr[4][0][] = $job_row;
                           
                        }else if( $job_row->job_type == 'Change of Tenancy' || $job_row->job_type == 'Lease Renewal' ){

                            $state_prio_arr[4][1][] = $job_row;
                            
                        }else if( $job_row->job_type == '240v Rebook' ){

                            $state_prio_arr[4][2][] = $job_row;
                            
                        }else if( $job_row->job_type == 'Yearly Maintenance' || $job_row->job_type == 'Once-off' ){

                            $state_prio_arr[4][3][] = $job_row;
                           
                        }else{

                            $state_prio_arr[4][4][] = $job_row;
                            
                        }
                       
                    }                    
        
                }

                // loop by state   
                $i = 2; 
                for( $state_ctr = 0; $state_ctr <= 4; $state_ctr++ ){

                   // loop by job priorities
                    for( $job_prio_ctr = 0; $job_prio_ctr <= 4; $job_prio_ctr++ ){

                        // get job data object
                        $job_row_data_arr = $state_prio_arr[$state_ctr][$job_prio_ctr];
                        
                        foreach( $job_row_data_arr as $job_row_obj ){

                            // update property coordinates 
                            $coor_params = array(
                                'property_id' => $job_row_obj->property_id
                            );
                            $this->properties_model->update_coordinates($coor_params);
                
                            // insert tech run row
                            $insert_data2 = array(
                                'tech_run_id' => $tr_id,
                                'row_id_type' => 'job_id',
                                'row_id' => $job_row_obj->jid,
                                'sort_order_num' => $i,
                                'created_date' => $today_full,
                                'status' => 1
                            );                                                                           
                            $this->db->insert('tech_run_rows', $insert_data2);
                            $i++;

                        }

                    }
                    

                }                               
                
                
                if( $calendar_id > 0 ){ // exist, update

                    $update_data = array(
                        'region' => $calendar,
                        'accomodation' => $accomodation,
                        'accomodation_id' => $accomodation_id,
                        'booking_staff' => $booking_staff
                    );                    
                    $this->db->where('calendar_id', $calendar_id);
                    $this->db->update('calendar', $update_data);

                }else{ // new, insert

                     // insert tech run row
                     $insert_data3 = array(
                        'staff_id' => $assigned_tech,
                        'date_start' => $date,
                        'date_finish' => $date,
                        'region' => $calendar,
                        'country_id' => $country_id,
                        'date_start_time' => '09:00',
                        'date_finish_time' => '17:00',
                        'accomodation' => $accomodation,
                        'accomodation_id' => $accomodation_id,
                        'booking_staff' => $booking_staff                 
                    );            
                    $this->db->insert('calendar', $insert_data3);                                      

                }   
                                                         

            }     
            
            $this->session->set_flashdata('success', true);
            redirect("/tech_run/set/?tr_id={$tr_id}");           

        }                

    }

    public function get_accomodation_and_booking_staff(){

        $assigned_tech = $this->input->post('assigned_tech');

        if( $assigned_tech > 0 ){

            // get tech accomodation and assigned call agent
            $staff_sql = $this->db->query("
            SELECT *
            FROM `staff_accounts`
            WHERE `StaffID` = {$assigned_tech}
            AND `ClassID` = 6
            ");
            $staff_row = $staff_sql->row();

            if( $staff_sql->num_rows() > 0 ){

                $json_arrr = array(
                    'call_agent' => $staff_row->other_call_centre,
                    'accomodation' => $staff_row->accomodation_id
                );
                
                echo json_encode($json_arrr);

            }            

        }        
        
    }

    public function already_exist(){

        $assigned_tech = $this->input->post('assigned_tech');        
        $date = ( $this->input->post('date') !='' )?$this->system_model->formatDate($this->input->post('date')):null;

        $tr_sql = $this->db->query("
        SELECT COUNT(`tech_run_id`) AS tr_count
        FROM `tech_run`
        WHERE `date` = '{$date}'
        AND `assigned_tech` = {$assigned_tech}
        ");

        echo $tr_sql->row()->tr_count;

    }


    public function delete(){

        $tr_id = $this->input->get_post('tr_id');

        if( $tr_id > 0 ){

            // delete tech run
            $this->db->where('tech_run_id', $tr_id);
            $this->db->delete('tech_run');

            // delete tech run rows
            $this->db->where('tech_run_id', $tr_id);
            $this->db->delete('tech_run_rows');

            // delete tech run logs
            $this->db->where('tech_run_id', $tr_id);
            $this->db->delete('tech_run_logs');

            // delete tech run color/colour        
            $this->db->where('tech_run_id', $tr_id);
            $this->db->delete('colour_table');

            $this->session->set_flashdata('delete_success', true);
            redirect("/tech_run/set");

        }            

    }

    // hide tech run rows
    public function hide_tech_run_rows(){

        $tr_id = $this->input->get_post('tr_id');
        $trr_id_arr = $this->input->get_post('trr_id_arr');
        $operation = $this->input->get_post('operation');

        if( $operation == 'hide' ){

            if( count($trr_id_arr) > 0 ){

                $update_data = array(
                    'hidden' => 1
                );                    
                $this->db->where('tech_run_id', $tr_id);
                $this->db->where_in('tech_run_rows_id', $trr_id_arr);
                $this->db->update('tech_run_rows', $update_data);
    
            }  

        }
            
    }

    // escalate jobs
    public function escalate_jobs(){

        $job_id_arr = $this->input->get_post('job_id_arr');

        $staff_id = $this->session->staff_id;
        $today_full = date('Y-m-d H:i:s');

        if( count($job_id_arr) > 0 ){

            foreach( $job_id_arr as $job_id ){

                if( $job_id > 0 ){

                    // update to escalate
                    $update_data = array(
                        'status' => 'Escalate'
                    );                    
                    $this->db->where('id', $job_id);
                    $this->db->update('jobs', $update_data);

                    // clear selected job escalate reason first
                    $this->db->where('job_id', $job_id);
                    $this->db->delete('selected_escalate_job_reasons');

                    // set escalate reason 
                    $escalate_job_reasons = 1; // verify tenant details

                    $insert_data = array(
                        'job_id' => $job_id,
                        'escalate_job_reasons_id' => $escalate_job_reasons,
                        'date_created' => $today_full,
                        'deleted' => 0,
                        'active' => 1
                    );                
                    $this->db->insert('selected_escalate_job_reasons', $insert_data);

                    // insert job log
                    // get escalate job reason
                    $ejr_sql = $this->db->query("
                    SELECT `reason_short`
                    FROM `escalate_job_reasons`
                    WHERE `escalate_job_reasons_id` = {$escalate_job_reasons}
                    ");
                    $ejr = $ejr_sql->row();

                    $log_details = "Job marked <b>Escalate</b> due to <b>{$ejr->reason_short}</b>";

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
            
    }


    // highlight rows
    public function highlight_row(){

        $tr_id = $this->input->get_post('tr_id');
        $trr_id_arr = $this->input->get_post('trr_id_arr');
        $trr_hl_color = $this->input->get_post('trr_hl_color');

        if( count($trr_id_arr) > 0 ){

            $update_data = array(
                'highlight_color' => $trr_hl_color
            );                    
            $this->db->where('tech_run_id', $tr_id);
            $this->db->where_in('tech_run_rows_id', $trr_id_arr);
            $this->db->update('tech_run_rows', $update_data);

        }
            
    }

    // remove color
    public function remove_color(){

        $tr_id = $this->input->get_post('tr_id');
        $trr_id_arr = $this->input->get_post('trr_id_arr');        

        if( count($trr_id_arr) > 0 ){

            $update_data = array(
                'highlight_color' => null
            );                    
            $this->db->where('tech_run_id', $tr_id);
            $this->db->where_in('tech_run_rows_id', $trr_id_arr);
            $this->db->update('tech_run_rows', $update_data);

        }
            
    }


    // escalate jobs
    public function change_tech(){

        $tr_id = $this->input->get_post('tr_id');
        $trr_id_arr = $this->input->get_post('trr_id_arr');

        $change_tech = $this->input->post('change_tech');
        $logged_user = $this->session->staff_id;

        if( $tr_id > 0 && count($trr_id_arr) > 0 ){

            $this->db->select("
            trr.`tech_run_rows_id`, 
            trr.`row_id_type`,
            trr.`row_id`,
            
            j.`status` AS j_status,

            sa.`FirstName` AS tech_from_fname, 
            sa.`LastName` AS tech_from_lname
            ");
            $this->db->from('`tech_run_rows` AS trr');
            $this->db->join('`jobs` AS j', "trr.`row_id` = j.`id`  AND trr.`row_id_type` = 'job_id'", 'left');
            $this->db->join('`staff_accounts` AS sa', "j.`assigned_tech` = sa.`StaffID`", 'left');
            $this->db->where_in('trr.tech_run_rows_id', $trr_id_arr);
            $trr_sql = $this->db->get();

            foreach( $trr_sql->result() as $trr_row ){

                $job_id = $trr_row->row_id;

                // get FROM tech  name
                $tech_from = $this->system_model->formatStaffName($trr_row->tech_from_fname,$trr_row->tech_from_lname);

                // get TO tech name
                $this->db->select("
                `FirstName` AS tech_from_fname, 
                `LastName` AS tech_from_lname
                ");
                $this->db->from('staff_accounts');
                $this->db->where('StaffID', $change_tech);
                $tech_to_sql = $this->db->get();
                $tech_to_row = $tech_to_sql->row();
                $tech_to = $this->system_model->formatStaffName($tech_to_row->tech_from_fname,$tech_to_row->tech_from_lname);

                if( $job_id > 0 && $trr_row->tech_run_rows_id > 0 ){

                    // insert log
                    $log_details = "Tech changed from <b>{$tech_from}</b> to <b>{$tech_to}</b>";

                    // insert job log                
                    $log_params = array(
                        'title' => 63,  // Job Update
                        'details' => $log_details,
                        'display_in_vjd' => 1,
                        'created_by_staff' => $logged_user,
                        'job_id' => $job_id
                    );
                    $this->system_model->insert_log($log_params);

                    // update tech
                    $update_data = array(
                        'assigned_tech' => $change_tech
                    );                    
                    $this->db->where('id', $job_id);
                    $this->db->update('jobs', $update_data);                    

                    // delete tech run row
                    $this->db->where('tech_run_id', $tr_id);
                    $this->db->where('tech_run_rows_id', $trr_row->tech_run_rows_id);
                    $this->db->delete('tech_run_rows');

                }               

            }            

        }        
            
    }


    // escalate jobs
    public function assign_dk(){

        $job_id_arr = $this->input->get_post('job_id_arr');
        $assigned_tech = $this->input->get_post('assigned_tech');
        $date = ( $this->input->get_post('date') !='' )?$this->system_model->formatDate($this->input->get_post('date')):null;
        $date_dmy = date("d/m/Y",strtotime($date));

        $logged_user = $this->session->staff_id;        

        if( count($job_id_arr) > 0 ){

            foreach( $job_id_arr as $job_id ){

                if( $job_id > 0 ){

                    // update to escalate
                    $update_data = array(
                        'status' => 'To Be Booked',
                        'assigned_tech' => $assigned_tech,
                        'date' => $date,
                        'tech_notes' => 'Door Knock',
                        'booked_with' => 'Agent',
                        'booked_by' => $logged_user,
                        'door_knock' => 1
                    );                    
                    $this->db->where('id', $job_id);
                    $this->db->update('jobs', $update_data);

                    // get tech name
                    $this->db->select("
                    `FirstName` AS tech_from_fname, 
                    `LastName` AS tech_from_lname
                    ");
                    $this->db->from('staff_accounts');
                    $this->db->where('StaffID', $assigned_tech);
                    $tech_sql = $this->db->get();
                    $tech_row = $tech_sql->row();
                    $tech_name = $this->system_model->formatStaffName($tech_row->tech_from_fname,$tech_row->tech_from_lname);
                                  
                    // insert job log   
                    $log_details = "Door Knock Booked for <b>{$date_dmy}</b>. Technician <b>{$tech_name}</b>";             

                    $log_params = array(
                        'title' => 63,  // Job Update
                        'details' => $log_details,
                        'display_in_vjd' => 1,
                        'created_by_staff' => $logged_user,
                        'job_id' => $job_id
                    );
                    $this->system_model->insert_log($log_params);

                }               

            }            

        }        
            
    }


    // mark tech sick
    public function mark_tech_sick(){

        $job_id_arr = $this->input->get_post('job_id_arr');
        $jr_id = 25; // Staff Sick
        $today_full = date('Y-m-d H:i:s');
        $today_dmy = date('d/m/Y');
        $logged_user = $this->session->staff_id;

        // get tech name
        $this->db->select("
        `FirstName` AS tech_from_fname, 
        `LastName` AS tech_from_lname
        ");
        $this->db->from('staff_accounts');
        $this->db->where('StaffID', $logged_user);
        $tech_sql = $this->db->get();
        $tech_row = $tech_sql->row();
        $logged_user_name = $this->system_model->formatStaffName($tech_row->tech_from_fname,$tech_row->tech_from_lname);      

        $jr_comment = "Bulk Marked tech sick on <b>{$today_dmy}</b> by <b>{$logged_user_name}</b>";

        if( count($job_id_arr) > 0 ){

            foreach( $job_id_arr as $job_id ){

                if( $job_id > 0 ){

                    // get DK
                    $job_sql = $this->db->query("
                    SELECT 
                        `door_knock`,
                        `assigned_tech`
                    FROM `jobs`
                    WHERE `id` = '{$job_id}'                    
                    ");
                    $job_row = $job_sql->row();

                    // update job
                    $update_data = array(
                        'status' => 'Pre Completion',
                        'job_reason_id' => $jr_id,
                        'job_reason_comment' => $jr_comment,
                        'completed_timestamp' => $today_full
                    );                    
                    $this->db->where('id', $job_id);
                    $this->db->update('jobs', $update_data);
                    
                    $dk_str = ( $job_row->door_knock == 1 )?' (DK)':null;      

                    $log_details = "{$jr_comment}{$dk_str}";

                    // insert job log            
                    $log_params = array(
                        'title' => 63,  // Job Update
                        'details' => $log_details,
                        'display_in_vjd' => 1,
                        'created_by_staff' => $logged_user,
                        'job_id' => $job_id
                    );
                    $this->system_model->insert_log($log_params);

                    //insert to jobs_not_completed table
                    $insert_data = array(
                        'job_id' => $job_id,
                        'reason_id' => $jr_id,
                        'reason_comment' => $jr_comment,
                        'tech_id' => $job_row->assigned_tech,
                        'date_created' => $today_full,
                        'door_knock' => $job_row->door_knock,  
                    );                    
                    $this->db->insert('jobs_not_completed', $insert_data);

                }               

            }            

        }   
            
    }


    // set colour table
    public function set_colour_table(){

        $tr_id = $this->input->get_post('tr_id');
        $colour_id = $this->input->get_post('colour_id');
        $time = $this->input->get_post('time');
        $jobs_num = $this->input->get_post('jobs_num');
        $no_keys = $this->input->get_post('no_keys');
        $booked_jobs = $this->input->get_post('booked_jobs');

        // compute booking status
        $status_dif = $jobs_num-$booked_jobs;

        $status_dif_txt = null;
        if( $jobs_num > 0 ){
            if( $status_dif > 0 ){
                $status_dif_txt = "-{$status_dif}";
            }else{
                $status_dif_txt = "FULL";
            }

        }

        if( $tr_id > 0 && $colour_id > 0 ){

            // check if this color already has values to determine update or insert
            $ct_sql = $this->db->query("
            SELECT COUNT(`colour_table_id`) AS ct_count
            FROM `colour_table`
            WHERE `tech_run_id` = {$tr_id}
            AND `colour_id` = {$colour_id}
            ");
            $ct_row = $ct_sql->row();

            if( $ct_row->ct_count > 0 ){ // exist, update

                // update job
                $update_data = array(
                    'colour_id' => $colour_id,
                    'time' => $time,
                    'jobs_num' => $jobs_num,
                    'no_keys' => $no_keys,
                    'booking_status' => $status_dif_txt
                );                    
                $this->db->where('tech_run_id', $tr_id);
                $this->db->where('colour_id', $colour_id);
                $this->db->update('colour_table', $update_data);

            }else{ // new, insert

                //insert to jobs_not_completed table
                $insert_data = array(
                    'tech_run_id' => $tr_id,
                    'colour_id' => $colour_id,
                    'time' => $time,
                    'jobs_num' => $jobs_num,
                    'no_keys' => $no_keys,
                    'booking_status' => $status_dif_txt
                );                    
                $this->db->insert('colour_table', $insert_data);

            }

        }        

    }

    public function update_colour_table_status(){

        $tr_id = $this->input->get_post('tr_id');
        $colour_id = $this->input->get_post('colour_id');
        $booking_status = $this->input->get_post('booking_status');

        if( $tr_id > 0 && $booking_status != '' ){

            // update colour table
            $update_data = array(
                'booking_status' => $booking_status
            );                    
            $this->db->where('tech_run_id', $tr_id);
            $this->db->where('colour_id', $colour_id);
            $this->db->update('colour_table', $update_data);

        }        

    }

    public function update_notes(){

        $tr_id = $this->input->get_post('tr_id');
        $notes = $this->input->get_post('notes');
        
        $today_full = date('Y-m-d H:i:s');
        $today_full_dmy = date('d/m/Y H:i');
        $logged_user = $this->session->staff_id;

        if( $tr_id > 0 && $notes != '' ){

            // update colour table
            $update_data = array(
                'notes' => $notes,
                'notes_updated_ts' => $today_full,
                'notes_updated_by' => $logged_user
            );                    
            $this->db->where('tech_run_id', $tr_id);            
            $this->db->update('tech_run', $update_data);

             // get tech name
            $this->db->select("
            `FirstName` AS tech_from_fname, 
            `LastName` AS tech_from_lname
            ");
            $this->db->from('staff_accounts');
            $this->db->where('StaffID', $logged_user);
            $tech_sql = $this->db->get();
            $tech_row = $tech_sql->row();
            $logged_user_name = $this->system_model->formatStaffName($tech_row->tech_from_fname,$tech_row->tech_from_lname);     
            
            $ret_array = array(
                'notes_updated_by' => $logged_user_name,
                'notes_updated_ts' => $today_full_dmy
            );

            echo json_encode($ret_array);	

        }        

    }

    public function sort(){

        $tr_id = $this->input->get_post('tr_id');
        $sort_by = $this->input->get_post('sort_by');

        if( $tr_id > 0 ){

            if( $sort_by == 1 ){ // sorty by colour

                $trr_sql = $this->db->query("
                SELECT *
                FROM  `tech_run_rows` AS trr
                LEFT JOIN  `tech_run` AS tr ON trr.`tech_run_id` = tr.`tech_run_id` 
                LEFT JOIN  `jobs` AS j ON trr.`row_id` = j.`id`                             
                LEFT JOIN `tech_run_row_color` AS trr_hc ON trr.`highlight_color` = trr_hc.`tech_run_row_color_id`
                WHERE tr.`tech_run_id` = {$tr_id}                
                AND trr.`row_id_type` =  'job_id'
                ORDER BY CASE WHEN trr.`highlight_color` IS NULL THEN 1 ELSE 0 END, trr.`highlight_color` ASC
                ");
     

            }else if( $sort_by == 2 ){ // sorty by street

                $trr_sql = $this->db->query("
                SELECT trr.`tech_run_rows_id`
                FROM  `tech_run_rows` AS trr
                LEFT JOIN  `tech_run` AS tr ON trr.`tech_run_id` = tr.`tech_run_id` 
                LEFT JOIN  `jobs` AS j ON trr.`row_id` = j.`id` 
                LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
                WHERE tr.`tech_run_id` = {$tr_id}
                AND trr.`row_id_type` =  'job_id'
                ORDER BY p.`address_2`
                ");
                
            }else if( $sort_by == 3 ){ // sorty by suburb
    
                $trr_sql = $this->db->query("
                SELECT trr.`tech_run_rows_id`
                FROM  `tech_run_rows` AS trr
                LEFT JOIN  `tech_run` AS tr ON trr.`tech_run_id` = tr.`tech_run_id` 
                LEFT JOIN  `jobs` AS j ON trr.`row_id` = j.`id` 
                LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id` 
                WHERE tr.`tech_run_id` = {$tr_id}
                AND trr.`row_id_type` =  'job_id'
                ORDER BY p.`address_3`
                ");               
                
            }

            if( $trr_sql->num_rows() > 0 ){

                $i = 2;
                foreach( $trr_sql->result() as $trr_row ){

                    if( $trr_row->tech_run_rows_id > 0 ){

                        // update sort order
                        $update_data = array(
                            'sort_order_num' => $i,
                            'dnd_sorted' => 0
                        );                    
                        $this->db->where('tech_run_rows_id', $trr_row->tech_run_rows_id);            
                        $this->db->update('tech_run_rows', $update_data);

                        $i++;

                    }

                }
                
            }            

        }        

    }

    public function get_existing_calendar(){

        $assigned_tech = $this->input->get_post('assigned_tech');
        $date = ( $this->input->get_post('date') !='' )?$this->system_model->formatDate($this->input->get_post('date')):null;

        // get calendar data
        $cal_sql = $this->db->query("
        SELECT 
            `calendar_id`,
            `region`
        FROM `calendar`
        WHERE staff_id = {$assigned_tech}
        AND `date_start` = '{$date}'
        AND `date_finish` = '{$date}'
        ORDER BY `calendar_id` DESC
        ");
        $cal_row = $cal_sql->row();            

        $ret_arr = array(
            "calendar_id"  => $cal_row->calendar_id,
            "region"=> $cal_row->region
        );
        echo json_encode($ret_arr);

    }


    public function add_key(){

        $tr_id = $this->input->get_post('tr_id');
        $keys_agency = $this->input->get_post('keys_agency');
        $assigned_tech = $this->input->get_post('assigned_tech');
        $date = $this->input->get_post('date');

        $today_full = date('Y-m-d H:i:s');

        // get tech run row count
        $tr_sql = $this->db->query("
        SELECT COUNT(trr.`tech_run_rows_id`) AS trr_count
        FROM `tech_run_rows` AS trr
        LEFT JOIN `tech_run` AS tr ON trr.`tech_run_id` =  tr.`tech_run_id`                             
        WHERE tr.`tech_run_id` = {$tr_id}         
        ");

        // +2 for start and end point
        $i = ($tr_sql->row()->trr_count)+2;

        $keys_array = array(
            'Pick Up',
            'Drop Off'
        );
    
        foreach($keys_array as $key_action){                               

            // insert keys 
            $insert_data = array(
                'assigned_tech' => $assigned_tech,
                'date' => $date,
                'action' => $key_action,
                'agency_id' => $keys_agency,
                'sort_order' => $i
            );                    
            $this->db->insert('tech_run_keys', $insert_data);
            $key_id = $this->db->insert_id(); 

            
            //  insert tech run rows for keys
            $insert_data2 = array(
                'tech_run_id' => $tr_id,
                'row_id_type' => 'keys_id',
                'row_id' => $key_id,
                'sort_order_num' => $i,
                'created_date' => $today_full,
                'status' => 1
            );                    
            $this->db->insert('tech_run_rows', $insert_data2);            
            $i++;
            
        }

    }


    public function add_supplier(){

        $tr_id = $this->input->get_post('tr_id');
        $supplier = $this->input->get_post('supplier');        

        $today_full = date('Y-m-d H:i:s');

        // get tech run row count
        $tr_sql = $this->db->query("
        SELECT COUNT(trr.`tech_run_rows_id`) AS trr_count
        FROM `tech_run_rows` AS trr
        LEFT JOIN `tech_run` AS tr ON trr.`tech_run_id` =  tr.`tech_run_id`                             
        WHERE tr.`tech_run_id` = {$tr_id}         
        ");

        // +2 for start and end point
        $i = ($tr_sql->row()->trr_count)+2;
    
        // insert supplier 
        $insert_data = array(
            'suppliers_id' => $supplier,
            'created_date' => $today_full,
            'active' => 1,
            'deleted' => 0
        );                    
        $this->db->insert('tech_run_suppliers', $insert_data);
        $supplier_id = $this->db->insert_id(); 
        
        //  insert tech run rows for supplier
        $insert_data2 = array(
            'tech_run_id' => $tr_id,
            'row_id_type' => 'supplier_id',
            'row_id' => $supplier_id,
            'sort_order_num' => $i,
            'created_date' => $today_full,
            'status' => 1
        );                    
        $this->db->insert('tech_run_rows', $insert_data2);    

    }


    public function status_update(){

        $tr_id = $this->input->get_post('tr_id');
        $assigned_tech = $this->input->get_post('assigned_tech');
        $date = $this->input->get_post('date');
        $tech_run_field = $this->input->get_post('tech_run_field');
        $update_to = $this->input->get_post('update_to');
        $run_status_name = $this->input->get_post('run_status_name'); 
        $booking_staff = $this->input->get_post('booking_staff');
        $country_id = $this->config->item('country'); 
        
        $today_full = date('Y-m-d H:i:s');
        $logged_user = $this->session->staff_id;

        if( $tr_id > 0 ){

            // update tech run status
            $update_data = array(
                $tech_run_field => $update_to
            );  
            $this->db->where('tech_run_id', $tr_id);            
            $this->db->update('tech_run', $update_data);

            $status_txt = ( $update_to == 1 )?'Activated':'Deactivated';
            $log_desc = "{$run_status_name} {$status_txt}";

            // insert tech run logs
            $insert_data = array(
                'tech_run_id' => $tr_id,
                'description' => $log_desc,
                'created_by' => $logged_user,
                'created' => $today_full
            );                    
            $this->db->insert('tech_run_logs', $insert_data);  

            if( $tech_run_field == 'run_complete' && $update_to == 1 ){ // "run mapped" activated

                // update TBB jobs date and tech to NULL
                $this->db->query("
                UPDATE `jobs` AS j
                RIGHT JOIN `tech_run_rows` AS trr ON j.`id` = trr.`row_id` AND trr.`row_id_type` = 'job_id'
                SET 
                    j.`date` = NULL, 
                    j.`assigned_tech` = NULL
                WHERE j.`status` = 'To Be Booked'
                AND j.`date` = '{$date}'
                AND j.`assigned_tech` = {$assigned_tech}
                AND trr.`tech_run_id` = {$tr_id}
                AND j.`door_knock` = 0
                ");
                
            }

            if( ( $tech_run_field == 'ready_to_book' || $tech_run_field == 'run_reviewed' || $tech_run_field == 'additional_call_over' ) && $update_to == 1 ){

                // get tech name
                $this->db->select("
                `FirstName` AS tech_from_fname, 
                `LastName` AS tech_from_lname
                ");
                $this->db->from('staff_accounts');
                $this->db->where('StaffID', $assigned_tech);
                $tech_sql = $this->db->get();
                $tech_row = $tech_sql->row();
                $tech_name = $this->system_model->formatStaffName($tech_row->tech_from_fname,$tech_row->tech_from_lname);    
		
                // send notification to call center agent watching the tech run
			    $day_txt = date("l",strtotime($date)); // day
                $notf_msg = "{$tech_name} {$day_txt} <a href='/tech_run/set/?tr_id={$tr_id}'>status changed to</a> '{$run_status_name}'";

                $notf_type = 1; // General Notifications
                $params = array(
                    'notf_type'=> $notf_type,
                    'staff_id'=> $booking_staff,
                    'country_id'=> $country_id,
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
                $ch = "ch".$booking_staff;
                $ev = "ev01";
                $pusher->trigger($ch, $ev, $pusher_data);
                
            }

        }          

    }


    // remove keys
    public function remove_keys(){

        $tr_id = $this->input->get_post('tr_id');
        $trr_id_arr = $this->input->get_post('trr_id_arr');  
        $trk_id_arr = $this->input->get_post('trk_id_arr');      

        if( count($trr_id_arr) > 0 ){

            // delete tech run rows
            $this->db->where('tech_run_id', $tr_id);
            $this->db->where_in('tech_run_rows_id', $trr_id_arr);
            $this->db->delete('tech_run_rows');
            
            // delete tech run keys
            $this->db->where_in('tech_run_keys_id', $trk_id_arr);
            $this->db->delete('tech_run_keys');


        }
            
    }

    // remove keys
    public function remove_supplier(){

        $tr_id = $this->input->get_post('tr_id');
        $trr_id_arr = $this->input->get_post('trr_id_arr');  
        $trs_id_arr = $this->input->get_post('trs_id_arr');      

        if( count($trs_id_arr) > 0 ){

            // delete tech run rows
            $this->db->where('tech_run_id', $tr_id);
            $this->db->where_in('tech_run_rows_id', $trr_id_arr);
            $this->db->delete('tech_run_rows');
            
            // delete tech run suppliers
            $this->db->where_in('tech_run_suppliers_id', $trs_id_arr);
            $this->db->delete('tech_run_suppliers');


        }
            
    }

    // "show hidden rows" toggle
    public function hidden_jobs_toggle(){

        $tr_id = $this->input->get_post('tr_id');
        $show_hidden = $this->input->get_post('show_hidden');  

        if( $tr_id > 0 ){

            // update tech show hidden state
            $update_data = array(
                'show_hidden' => $show_hidden
            );  
            $this->db->where('tech_run_id', $tr_id);            
            $this->db->update('tech_run', $update_data);

        }        

    }    


    // issue entry notices
    // assign pin colour
    public function issue_en(){       

        $trr_id_arr = $this->input->get_post('trr_id_arr');
        $str_tech = $this->input->get_post('str_tech');
        $str_tech_name = $this->input->get_post('str_tech_name');
        $str_date = $this->input->get_post('str_date');
        $en_time_arr = $this->input->get_post('en_time_arr');
        
        $tr_params = (object) [
            'trr_id_arr' => $trr_id_arr,     
            'str_tech' => $str_tech,
            'str_tech_name' => $str_tech_name,
            'str_date' => $str_date,
            'en_time_arr' => $en_time_arr      
        ];
        $this->tech_run_model->issue_en($tr_params);		

    }


    // "show hidden rows" toggle
    public function job_type_toggle(){

        $tr_id = $this->input->get_post('tr_id');
        $is_ticked = $this->input->get_post('is_ticked');  
        $job_type = $this->input->get_post('job_type');

        if( $tr_id > 0 ){

            if( $is_ticked == 1 ){ // ticked

                // delete
                $this->db->where('tech_run_id', $tr_id);
                $this->db->where('job_type', $job_type);
                $this->db->delete('tech_run_hide_job_types');
                
            }else{ // unticked
                
                // insert
                $insert_data = array(
                    'tech_run_id' => $tr_id,
                    'job_type' => $job_type
                );                    
                $this->db->insert('tech_run_hide_job_types', $insert_data);    
                
            }            

        }        

    }
    
    public function update_working_hours(){

        $tr_id = $this->input->get_post('tr_id');
        $working_hours = $this->input->get_post('working_hours');  

        if( $tr_id > 0 ){

            $update_data = array(
                'working_hours' => $working_hours
            );                
            $this->db->where('tech_run_id', $tr_id);
            $this->db->update('tech_run', $update_data);   

        }               

    }

    public function update_time_of_day(){

        $job_id = $this->input->get_post('job_id');
        $time_of_day = $this->input->get_post('time_of_day');  

        if( $job_id > 0 ){

            $update_data = array(
                'time_of_day' => $time_of_day
            );                
            $this->db->where('id', $job_id);
            $this->db->update('jobs', $update_data);   

        }               

    }


}

?>
