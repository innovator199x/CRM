<?php

class Cronjobs extends CI_Controller {

    public function __construct(){
        
        parent::__construct();
		$this->load->helper('url');
        $this->load->database();
        $this->load->library('email');

        $this->load->model('jobs_model');
        $this->load->model('inc/email_functions_model');
        $this->load->model('cron_model');
    }


    public function index() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Cron Jobs";
        $uri = '/cronjobs/index';
        $data['uri'] = $uri;
        
        // get all active cron jobs
        $cron_sql_str = "
            SELECT 
                cron_type_id, 
                type_name, 
                description,
                ci_link
            FROM `cron_types` AS ct 
            WHERE `active` = 1
            AND (
                `ci_link` IS NOT NULL AND 
                `ci_link` != ''
            )
        ";
        $data['cron_sql'] = $this->db->query($cron_sql_str);
        

        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);

    }


    // orig old crm filename: send_pending.php
    public function send_service_due_email(){

        $cron_type_id = 1; // CRON type

        // insert staff ID or cron ID
        $triggered_by =  ( $this->session->staff_id > 0 )?$this->session->staff_id:-1;
           
        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        // get individual cron active status
        $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id);        

        if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){

            $country_id = $this->config->item('country');            
            $current_week = intval(date('W'));
            $current_year = date('Y');

            // get cron logs
            $this->db->select("COUNT(log_id) AS cl_count");
            $this->db->from('cron_log');
            $this->db->where('type_id',$cron_type_id);
            $this->db->where('week_no',$current_week);
            $this->db->where('year',$current_year);
            $this->db->where('country_id',$country_id);
            $cron_log_sql = $this->db->get();        
            $cron_log_count = $cron_log_sql->row()->cl_count;        
            
            if( $cron_log_count == 0 ){ // check if cron already ran
                
                // start cron log
                $this->db->query("
                    INSERT INTO 
                    `cron_log` (
                        `type_id`, 
                        `week_no`, 
                        `year`, 
                        `started`, 
                        `country_id`,
                        `triggered_by`
                    ) 
                    VALUES (
                        {$cron_type_id},
                        {$current_week},
                        {$current_year}, 
                        NOW(), 
                        {$country_id},
                        {$triggered_by}
                    )
                ");
                $cron_log_id = $this->db->insert_id();
                                
                $this->email_functions_model->service_due_email();                   
                
                // finish cron log
                if( $cron_log_id > 0 ){

                    $this->db->query("
                        UPDATE `cron_log` 
                        SET finished = NOW() 
                        WHERE log_id = {$cron_log_id}
                    ");

                }                

                echo "Cron job has finished executing";

            }else{
                echo "Cron job has already ran this week";
            }         

        }                                      

    }

    // orig old crm filename: send_report.php
    public function send_weekly_report_email(){  
        
        $cron_type_id = 2; // CRON type

        // insert staff ID or cron ID
        $triggered_by =  ( $this->session->staff_id > 0 )?$this->session->staff_id:-1;

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        // get individual cron active status
        $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id);     

        if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){

            $country_id = $this->config->item('country');             
            $current_week = intval(date('W'));
            $current_year = date('Y');
    
            // get cron logs
            $this->db->select("COUNT(log_id) AS cl_count");
            $this->db->from('cron_log');
            $this->db->where('type_id',$cron_type_id);
            $this->db->where('week_no',$current_week);
            $this->db->where('year',$current_year);
            $this->db->where('country_id',$country_id);
            $cron_log_sql = $this->db->get();        
            $cron_log_count = $cron_log_sql->row()->cl_count;    

            if( $cron_log_count == 0 ){ // check if cron already ran
            
                // start cron log
                $this->db->query("
                    INSERT INTO 
                    `cron_log` (
                        `type_id`, 
                        `week_no`, 
                        `year`, 
                        `started`, 
                        `country_id`,
                        `triggered_by`
                    ) 
                    VALUES (
                        {$cron_type_id},
                        {$current_week},
                        {$current_year}, 
                        NOW(), 
                        {$country_id},
                        {$triggered_by}
                    )
                ");
                $cron_log_id = $this->db->insert_id();                                
    
                $this->email_functions_model->weekly_report_email();                   
                
                // finish cron log
                if( $cron_log_id > 0 ){
    
                    $this->db->query("
                        UPDATE `cron_log` 
                        SET finished = NOW() 
                        WHERE log_id = {$cron_log_id}
                    ");
    
                }                
    
                echo "Cron job has finished executing";
    
            }else{
                echo "Cron job has already ran this week";
            }

        }                                                       

    }

    // orig old crm filename: send_keyaccess.php
    public function send_key_access_email(){  
        
        $cron_type_id = 3; // CRON type

        // insert staff ID or cron ID
        $triggered_by =  ( $this->session->staff_id > 0 )?$this->session->staff_id:-1;

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        // get individual cron active status
        $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id);   

        if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');
            
            $current_week = intval(date('W'));
            $current_year = date('Y');

            // get cron logs
            $this->db->select("COUNT(log_id) AS cl_count");
            $this->db->from('cron_log');
            $this->db->where('type_id',$cron_type_id);
            $this->db->where('week_no',$current_week);
            $this->db->where('year',$current_year);
            $this->db->where("CAST(`started` AS DATE) = '{$today}'");
            $this->db->where('country_id',$country_id);       
            $cron_log_sql = $this->db->get();        
            $cron_log_count = $cron_log_sql->row()->cl_count;        
        
            if( $cron_log_count == 0 ){ // check if cron already ran                
                
                // start cron log
                $this->db->query("
                    INSERT INTO 
                    `cron_log` (
                        `type_id`, 
                        `week_no`, 
                        `year`, 
                        `started`, 
                        `country_id`,
                        `triggered_by`
                    ) 
                    VALUES (
                        {$cron_type_id},
                        {$current_week},
                        {$current_year}, 
                        NOW(), 
                        {$country_id},
                        {$triggered_by}
                    )
                ");
                $cron_log_id = $this->db->insert_id();
                                                    
                $this->email_functions_model->key_access_email();   
                                
                // finish cron log
                if( $cron_log_id > 0 ){

                    $this->db->query("
                        UPDATE `cron_log` 
                        SET finished = NOW() 
                        WHERE log_id = {$cron_log_id}
                    ");

                }                                                          

                echo "Cron job has finished executing";

            }else{
                echo "Cron job has already ran this week";
            } 
            
        }                                              

    }


    // orig old crm filename: send_keyaccess.php
    public function send_key_access_email_48_hours(){  
        
        $cron_type_id = 33; // CRON type
        
        // insert staff ID or cron ID
        $triggered_by =  ( $this->session->staff_id > 0 )?$this->session->staff_id:-1;

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        // get individual cron active status
        $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id);   

        if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');
            
            $current_week = intval(date('W'));
            $current_year = date('Y');

            // get cron logs
            $this->db->select("COUNT(log_id) AS cl_count");
            $this->db->from('cron_log');
            $this->db->where('type_id',$cron_type_id);
            $this->db->where('week_no',$current_week);
            $this->db->where('year',$current_year);
            $this->db->where("CAST(`started` AS DATE) = '{$today}'");
            $this->db->where('country_id',$country_id);       
            $cron_log_sql = $this->db->get();        
            $cron_log_count = $cron_log_sql->row()->cl_count;        
        
            if( $cron_log_count == 0 ){ // check if cron already ran                
                
                // start cron log
                $this->db->query("
                    INSERT INTO 
                    `cron_log` (
                        `type_id`, 
                        `week_no`, 
                        `year`, 
                        `started`, 
                        `country_id`,
                        `triggered_by`
                    ) 
                    VALUES (
                        {$cron_type_id},
                        {$current_week},
                        {$current_year}, 
                        NOW(), 
                        {$country_id},
                        {$triggered_by}
                    )
                ");
                $cron_log_id = $this->db->insert_id();
                                                    
                $this->email_functions_model->key_access_email_48_hours();   
                //echo $this->db->last_query();
                //exit();

                // finish cron log
                if( $cron_log_id > 0 ){

                    $this->db->query("
                        UPDATE `cron_log` 
                        SET finished = NOW() 
                        WHERE log_id = {$cron_log_id}
                    ");

                }                                                          

                echo "Cron job has finished executing";

            }else{
                echo "Cron job has already ran this week";
            } 
            
        }                                              

    }

    // orig old crm filename: cron_on_hold_jobs_move_for_booking_au.php
    public function move_on_hold_jobs(){  
        
        $cron_type_id = 4; // CRON type

        // insert staff ID or cron ID
        $triggered_by =  ( $this->session->staff_id > 0 )?$this->session->staff_id:-1;

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        // get individual cron active status
        $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id);  

        if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');
            
            $current_week = intval(date('W'));
            $current_year = date('Y');

            // get cron logs
            $this->db->select("COUNT(log_id) AS cl_count");
            $this->db->from('cron_log');
            $this->db->where('type_id',$cron_type_id);
            $this->db->where('week_no',$current_week);
            $this->db->where('year',$current_year);
            $this->db->where("CAST(`started` AS DATE) = '{$today}'");
            $this->db->where('country_id',$country_id);       
            $cron_log_sql = $this->db->get();        
            $cron_log_count = $cron_log_sql->row()->cl_count;        
        
            if( $cron_log_count == 0 ){ // check if cron already ran                
                
                // start cron log
                $this->db->query("
                    INSERT INTO 
                    `cron_log` (
                        `type_id`, 
                        `week_no`, 
                        `year`, 
                        `started`, 
                        `country_id`,
                        `triggered_by`
                    ) 
                    VALUES (
                        {$cron_type_id},
                        {$current_week},
                        {$current_year}, 
                        NOW(), 
                        {$country_id},
                        {$triggered_by}
                    )
                ");
                $cron_log_id = $this->db->insert_id();                                   

                $this->cron_model->move_on_hold_jobs();                               
                
                // finish cron log
                if( $cron_log_id > 0 ){

                    $this->db->query("
                        UPDATE `cron_log` 
                        SET finished = NOW() 
                        WHERE log_id = {$cron_log_id}
                    ");

                }                                            

                echo "Cron job has finished executing";

            }else{
                echo "Cron job has already ran this week";
            }  

        }                                             

    }


    // orig old crm filename: cron_sms_au.php
    public function reminder_sms(){    
        
        $cron_type_id = 12; // CRON type

        // insert staff ID or cron ID
        $triggered_by =  ( $this->session->staff_id > 0 )?$this->session->staff_id:-1;

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        // get individual cron active status
        $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id); 

        if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){
            
            $country_id = $this->config->item('country');
            $today = date('Y-m-d');
            
            $current_week = intval(date('W'));
            $current_year = date('Y');

            // get cron logs
            $this->db->select("COUNT(log_id) AS cl_count");
            $this->db->from('cron_log');
            $this->db->where('type_id',$cron_type_id);
            $this->db->where('week_no',$current_week);
            $this->db->where('year',$current_year);
            $this->db->where("CAST(`started` AS DATE) = '{$today}'");
            $this->db->where('country_id',$country_id);       
            $cron_log_sql = $this->db->get();        
            $cron_log_count = $cron_log_sql->row()->cl_count;        
        
            if( $cron_log_count == 0 ){ // check if cron already ran
                
                
                // start cron log
                $this->db->query("
                    INSERT INTO 
                    `cron_log` (
                        `type_id`, 
                        `week_no`, 
                        `year`, 
                        `started`, 
                        `country_id`,
                        `triggered_by`
                    ) 
                    VALUES (
                        {$cron_type_id},
                        {$current_week},
                        {$current_year}, 
                        NOW(), 
                        {$country_id},
                        {$triggered_by}
                    )
                ");
                $cron_log_id = $this->db->insert_id();   
                    

                $this->cron_model->reminder_sms();               
                
                
                // finish cron log
                if( $cron_log_id > 0 ){

                    $this->db->query("
                        UPDATE `cron_log` 
                        SET finished = NOW() 
                        WHERE log_id = {$cron_log_id}
                    ");

                }                                                     

                echo "Cron job has finished executing";

            }else{
                echo "Cron job has already ran this week";
            } 

        }                                              

    }


    // orig old crm filename: send_escalate.php
    public function send_escalate_email(){

        $cron_type_id = 6; // CRON type

        // insert staff ID or cron ID
        $triggered_by =  ( $this->session->staff_id > 0 )?$this->session->staff_id:-1;

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        // get individual cron active status
        $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id); 

        if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){

            $country_id = $this->config->item('country');        
            
            $current_week = intval(date('W'));
            $current_year = date('Y');
    
            // get cron logs
            $this->db->select("COUNT(log_id) AS cl_count");
            $this->db->from('cron_log');
            $this->db->where('type_id',$cron_type_id);
            $this->db->where('week_no',$current_week);
            $this->db->where('year',$current_year);        
            $this->db->where('country_id',$country_id);       
            $cron_log_sql = $this->db->get();        
            $cron_log_count = $cron_log_sql->row()->cl_count;        
          
            if( $cron_log_count == 0 ){ // check if cron already ran
                                 
                // start cron log
                $this->db->query("
                    INSERT INTO 
                    `cron_log` (
                        `type_id`, 
                        `week_no`, 
                        `year`, 
                        `started`, 
                        `country_id`,
                        `triggered_by`
                    ) 
                    VALUES (
                        {$cron_type_id},
                        {$current_week},
                        {$current_year}, 
                        NOW(), 
                        {$country_id},
                        {$triggered_by}
                    )
                ");
                $cron_log_id = $this->db->insert_id();                                                      
    
                $this->email_functions_model->escalate_email();                               
            
                // finish cron log
                if( $cron_log_id > 0 ){
    
                    $this->db->query("
                        UPDATE `cron_log` 
                        SET finished = NOW() 
                        WHERE log_id = {$cron_log_id}
                    ");
    
                }                                         
    
                echo "Cron job has finished executing";
    
            }else{
                echo "Cron job has already ran this week";
            } 

        }                                             

    }


    // orig old crm filename: cron_tech_run_screenshot_bulk_au.php
    public function email_tech_runs(){

        $cron_type_id = 8; // CRON type

        // insert staff ID or cron ID
        $triggered_by =  ( $this->session->staff_id > 0 )?$this->session->staff_id:-1;

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        // get individual cron active status
        $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id); 

        if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){

            $country_id = $this->config->item('country');    
            $today = date('Y-m-d');    
            
            $current_week = intval(date('W'));
            $current_year = date('Y');

            // get cron logs
            $this->db->select("COUNT(log_id) AS cl_count");
            $this->db->from('cron_log');
            $this->db->where('type_id',$cron_type_id);
            $this->db->where('week_no',$current_week);
            $this->db->where('year',$current_year);    
            $this->db->where("CAST(`started` AS DATE) = '{$today}'");       
            $this->db->where('country_id',$country_id);       
            $cron_log_sql = $this->db->get();        
            $cron_log_count = $cron_log_sql->row()->cl_count;        
        
            if( $cron_log_count == 0 ){ // check if cron already ran
                                
                // start cron log
                $this->db->query("
                    INSERT INTO 
                    `cron_log` (
                        `type_id`, 
                        `week_no`, 
                        `year`, 
                        `started`, 
                        `country_id`,
                        `triggered_by`
                    ) 
                    VALUES (
                        {$cron_type_id},
                        {$current_week},
                        {$current_year}, 
                        NOW(), 
                        {$country_id},
                        {$triggered_by}
                    )
                ");
                $cron_log_id = $this->db->insert_id();                                                                      

                $this->email_functions_model->email_tech_runs();                               
                
                // finish cron log
                if( $cron_log_id > 0 ){

                    $this->db->query("
                        UPDATE `cron_log` 
                        SET finished = NOW() 
                        WHERE log_id = {$cron_log_id}
                    ");

                }                                                    

                echo "Cron job has finished executing";

            }else{
                echo "Cron job has already ran this week";
            }    

        }                                           

    }


    // orig old crm filename: cron_move_future_start_date_jobs_au.php
    public function move_future_start_date_jobs(){

        $cron_type_id = 10; // CRON type

        // insert staff ID or cron ID
        $triggered_by =  ( $this->session->staff_id > 0 )?$this->session->staff_id:-1;

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        // get individual cron active status
        $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id);

        if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){

            $country_id = $this->config->item('country');        

            
            $current_week = intval(date('W'));
            $current_year = date('Y');       
        
            // this cron needs to run in every hour so the cron log check is not needed
            // start cron log
            $this->db->query("
                INSERT INTO 
                `cron_log` (
                    `type_id`, 
                    `week_no`, 
                    `year`, 
                    `started`, 
                    `country_id`,
                    `triggered_by`
                ) 
                VALUES (
                    {$cron_type_id},
                    {$current_week},
                    {$current_year}, 
                    NOW(), 
                    {$country_id},
                    {$triggered_by}
                )
            ");
            $cron_log_id = $this->db->insert_id(); 

            $this->cron_model->move_future_start_date_jobs();               
                        
            // finish cron log
            if( $cron_log_id > 0 ){

                $this->db->query("
                    UPDATE `cron_log` 
                    SET finished = NOW() 
                    WHERE log_id = {$cron_log_id}
                ");

            }                             

            echo "Cron job has finished executing";     

        }                                        

    }





    // orig old crm filename: cron_create_renewals_au.php
    public function create_renewals(){

        $cron_type_id = 17; // CRON type
        $country_id = $this->config->item('country');
        
        if( $country_id == 2 ){ // old renewals is NZ only

            // insert staff ID or cron ID
            $triggered_by =  ( $this->session->staff_id > 0 )?$this->session->staff_id:-1;

            // get crm settings
            $crm_sql = $this->system_model->getCrmSettings([
                "sel_str" => "disable_all_crons"
            ]);
            $crm_row = $crm_sql->row();

            // get individual cron active status
            $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id);

            if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){
                
                $today = date('Y-m-d');    
                
                $current_week = intval(date('W'));
                $current_year = date('Y');
        
                // check renewals  
                $renewal_type = 1; // regular          
                $renew_sql = $this->db->query("
                    SELECT COUNT(`renewals_id`) AS renew_count
                    FROM  `renewals` 
                    WHERE CAST(  `date` AS DATE ) =  '{$today}'
                    AND  `country_id` ={$country_id}
                    AND `renewal_type` = {$renewal_type}
                "); 
                $renew_count = $renew_sql->row()->renew_count;        
            
                if( $renew_count == 0 ){ // check if cron already ran
                    
                    
                    // start cron log
                    $this->db->query("
                        INSERT INTO 
                        `cron_log` (
                            `type_id`, 
                            `week_no`, 
                            `year`, 
                            `started`, 
                            `country_id`,
                            `triggered_by`
                        ) 
                        VALUES (
                            {$cron_type_id},
                            {$current_week},
                            {$current_year}, 
                            NOW(), 
                            {$country_id},
                            {$triggered_by}
                        )
                    ");
                    $cron_log_id = $this->db->insert_id();                                                        
        
                    $this->cron_model->create_renewals();                               
                    
                    // finish cron log
                    if( $cron_log_id > 0 ){
        
                        $this->db->query("
                            UPDATE `cron_log` 
                            SET finished = NOW() 
                            WHERE log_id = {$cron_log_id}
                        ");
        
                    }   
                                                                
                    echo "Cron job has finished executing";
        
                }else{
                    echo "Cron job has already ran this week";
                } 

            } 

        }                                            

    }

    // orig old crm filename: cron_create_renewals_au.php
    public function create_renewals_v2(){

        $cron_type_id = 17; // CRON type
        $country_id = $this->config->item('country');

        if( $country_id == 1 ){ // renewals v2 is AU only

            // insert staff ID or cron ID
            $triggered_by =  ( $this->session->staff_id > 0 )?$this->session->staff_id:-1;
            $renewal_type = ( $this->input->get_post('renewal_type') != '' )?$this->input->get_post('renewal_type'):1;

            // get crm settings
            $crm_sql = $this->system_model->getCrmSettings([
                "sel_str" => "disable_all_crons"
            ]);
            $crm_row = $crm_sql->row();

            // get individual cron active status
            $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id);
            
            if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){
                
                $today = date('Y-m-d');    
                
                $current_week = intval(date('W'));
                $current_year = date('Y');
        
                // check renewals  
                $renewal_type = 1; // regular          
                $renew_sql = $this->db->query("
                    SELECT COUNT(`renewals_id`) AS renew_count
                    FROM  `renewals` 
                    WHERE CAST(  `date` AS DATE ) =  '{$today}'
                    AND  `country_id` ={$country_id}
                    AND `renewal_type` = {$renewal_type}
                "); 
                $renew_count = $renew_sql->row()->renew_count;        
            
                if( $renew_count == 0 ){ // check if cron already ran
                    
                    // start cron log
                    $this->db->query("
                        INSERT INTO 
                        `cron_log` (
                            `type_id`, 
                            `week_no`, 
                            `year`, 
                            `started`, 
                            `country_id`,
                            `triggered_by`
                        ) 
                        VALUES (
                            {$cron_type_id},
                            {$current_week},
                            {$current_year}, 
                            NOW(), 
                            {$country_id},
                            {$triggered_by}
                        )
                    ");
                    $cron_log_id = $this->db->insert_id();                                                        
                
                    $this->cron_model->create_renewals_v2($renewal_type);                               
  
                    // finish cron log
                    if( $cron_log_id > 0 ){
        
                        $this->db->query("
                            UPDATE `cron_log` 
                            SET finished = NOW() 
                            WHERE log_id = {$cron_log_id}
                        ");
        
                    }   
                                                                
                    echo "Cron job has finished executing";
        
                }else{
                    echo "Cron job has already ran this week";
                } 

            }
            
            
        }                                                     

    }


    public function create_renewals_v2_manual_run(){

        $cron_type_id = 17; // CRON type
        $country_id = $this->config->item('country');
        $controlled_date = $this->input->get_post('controlled_date');

        $controlled_date_ts = strtotime($controlled_date);

        $today_full = date('Y-m-d H:i:s',$controlled_date_ts);

        if( $controlled_date_ts != '' ){

            if( $country_id == 1 ){ // renewals v2 is AU only

                // insert staff ID or cron ID
                $triggered_by =  ( $this->session->staff_id > 0 )?$this->session->staff_id:-1;
                $renewal_type = ( $this->input->get_post('renewal_type') != '' )?$this->input->get_post('renewal_type'):1;
    
                // get crm settings
                $crm_sql = $this->system_model->getCrmSettings([
                    "sel_str" => "disable_all_crons"
                ]);
                $crm_row = $crm_sql->row();
    
                // get individual cron active status
                $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id);
                
                if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){
                    
                    $today = date('Y-m-d',$controlled_date_ts);    
                    
                    $current_week = intval(date('W',$controlled_date_ts));
                    $current_year = date('Y',$controlled_date_ts);
            
                    // check renewals  
                    $renewal_type = 1; // regular          
                    $renew_sql = $this->db->query("
                        SELECT COUNT(`renewals_id`) AS renew_count
                        FROM  `renewals` 
                        WHERE CAST(  `date` AS DATE ) =  '{$today}'
                        AND  `country_id` ={$country_id}
                        AND `renewal_type` = {$renewal_type}
                    "); 
                    $renew_count = $renew_sql->row()->renew_count;        
                
                    if( $renew_count == 0 ){ // check if cron already ran
                        
                        // start cron log
                        $this->db->query("
                            INSERT INTO 
                            `cron_log` (
                                `type_id`, 
                                `week_no`, 
                                `year`, 
                                `started`, 
                                `country_id`,
                                `triggered_by`
                            ) 
                            VALUES (
                                {$cron_type_id},
                                {$current_week},
                                {$current_year}, 
                                '{$today_full}', 
                                {$country_id},
                                {$triggered_by}
                            )
                        ");
                        $cron_log_id = $this->db->insert_id();                                                        
                    
                        $params = array(
                            'renewal_type' => $renewal_type,
                            'controlled_date_ts' => $controlled_date_ts
                        );
                        $this->cron_model->create_renewals_v2_manual_run($params);                               
      
                        // finish cron log
                        if( $cron_log_id > 0 ){
            
                            $this->db->query("
                                UPDATE `cron_log` 
                                SET finished = '{$today_full}'
                                WHERE log_id = {$cron_log_id}
                            ");
            
                        }   
                                                                    
                        echo "Cron job has finished executing";
            
                    }else{
                        echo "Cron job has already ran this week";
                    } 
    
                }
                
                
            }

        }                                                             

    }


    // no longer used, hume housing and NSW renewals only exist on AU and is now merge on renewals version 2, NZ doesn't have it
    /*
    public function create_renewals_hume_housing(){

        $cron_type_id = 17; // CRON type
        $country_id = $this->config->item('country');
        $today = date('Y-m-d');

        // insert staff ID or cron ID
        $triggered_by =  ( $this->session->staff_id > 0 )?$this->session->staff_id:-1;

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        // get individual cron active status
        $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id);

        if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){           
                
            // check renewals  
            $renewal_type = 2; // hume        
            $renew_sql = $this->db->query("
                SELECT COUNT(`renewals_id`) AS renew_count
                FROM  `renewals` 
                WHERE CAST(  `date` AS DATE ) =  '{$today}'
                AND  `country_id` ={$country_id}
                AND `renewal_type` = {$renewal_type}
            "); 
            $renew_count = $renew_sql->row()->renew_count;        

            if( $renew_count == 0 ){ // check if cron already ran
                                                                        
                $this->cron_model->create_renewals_hume_housing();                  
                echo "Cron job has finished executing";                

            }else{
                echo "Cron job has already ran this week";
            } 

        }              

    }


    public function create_renewals_nsw(){

        $cron_type_id = 17; // CRON type
        $country_id = $this->config->item('country');
        $today = date('Y-m-d');

        // insert staff ID or cron ID
        $triggered_by =  ( $this->session->staff_id > 0 )?$this->session->staff_id:-1;

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        // get individual cron active status
        $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id);

        if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){                                       

            // check renewals  
            $renewal_type = 3; // nsw        
            $renew_sql = $this->db->query("
                SELECT COUNT(`renewals_id`) AS renew_count
                FROM  `renewals` 
                WHERE CAST(  `date` AS DATE ) =  '{$today}'
                AND  `country_id` ={$country_id}
                AND `renewal_type` = {$renewal_type}
            "); 
            $renew_count = $renew_sql->row()->renew_count;        

            if( $renew_count == 0 ){ // check if cron already ran
                                                                        
                $this->cron_model->create_renewals_nsw();                  
                echo "Cron job has finished executing";                

            }else{
                echo "Cron job has already ran this week";
            } 

        }                                             

    }
    */

    // orig old crm filename: cron_process_pendings_to_on_hold_au.php
    public function process_service_due(){

        $cron_type_id = 18; // CRON type
        $state_filter = $this->input->get_post('state_filter');

        // insert staff ID or cron ID
        $triggered_by =  ( $this->session->staff_id > 0 )?$this->session->staff_id:-1;

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        // get individual cron active status
        $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id);

        if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');
            
            $current_week = intval(date('W'));
            $current_year = date('Y');

            // get cron logs
            $this->db->select("COUNT(log_id) AS cl_count");
            $this->db->from('cron_log');
            $this->db->where('type_id',$cron_type_id);
            $this->db->where('week_no',$current_week);
            $this->db->where('year',$current_year);
            $this->db->where("CAST(`started` AS DATE) = '{$today}'");
            $this->db->where('country_id',$country_id);       
            $cron_log_sql = $this->db->get();        
            $cron_log_count = $cron_log_sql->row()->cl_count;        
        
            if( $cron_log_count == 0 ){ // check if cron already ran             
                
           
                // start cron log
                $this->db->query("
                    INSERT INTO 
                    `cron_log` (
                        `type_id`, 
                        `week_no`, 
                        `year`, 
                        `started`, 
                        `country_id`,
                        `triggered_by`
                    ) 
                    VALUES (
                        {$cron_type_id},
                        {$current_week},
                        {$current_year}, 
                        NOW(), 
                        {$country_id},
                        {$triggered_by}
                    )
                ");
                $cron_log_id = $this->db->insert_id(); 
              
                                                

                $cron_params = array(
                    'state_filter' => $state_filter
                );
                $this->cron_model->process_service_due($cron_params);               
                      
                
                // finish cron log
                if( $cron_log_id > 0 ){

                    $this->db->query("
                        UPDATE `cron_log` 
                        SET finished = NOW() 
                        WHERE log_id = {$cron_log_id}
                    ");

                }   
              
                                                      

                echo "<br /><br />Cron job has finished executing";

            }else{
                echo "Cron job has already ran this week";
            }

        }                                               

    }

    // orig old crm filename: cron_activity_prev_week_report_au.php
    public function weekly_agent_activity(){

        $cron_type_id = 5; // CRON type

        // insert staff ID or cron ID
        $triggered_by =  ( $this->session->staff_id > 0 )?$this->session->staff_id:-1;

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        // get individual cron active status
        $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id);

        if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');
            
            $current_week = intval(date('W'));
            $current_year = date('Y');

            // get cron logs
            $this->db->select("COUNT(log_id) AS cl_count");
            $this->db->from('cron_log');
            $this->db->where('type_id',$cron_type_id);
            $this->db->where('week_no',$current_week);
            $this->db->where('year',$current_year);
            $this->db->where("CAST(`started` AS DATE) = '{$today}'");
            $this->db->where('country_id',$country_id);       
            $cron_log_sql = $this->db->get();        
            $cron_log_count = $cron_log_sql->row()->cl_count;        
        
            if( $cron_log_count == 0 ){ // check if cron already ran
                    
                
                // start cron log
                $this->db->query("
                    INSERT INTO 
                    `cron_log` (
                        `type_id`, 
                        `week_no`, 
                        `year`, 
                        `started`, 
                        `country_id`,
                        `triggered_by`
                    ) 
                    VALUES (
                        {$cron_type_id},
                        {$current_week},
                        {$current_year}, 
                        NOW(), 
                        {$country_id},
                        {$triggered_by}
                    )
                ");
                $cron_log_id = $this->db->insert_id();   
                
                                
                // get country data
                $country_params = array(
                    'sel_query' => 'c.agent_number, c.outgoing_email, c.iso',
                    'country_id' => $country_id
                );
                $country_sql = $this->system_model->get_countries($country_params);
                $country_row = $country_sql->row();

                // date range
                // last week monday
                $from = date("Y-m-d",strtotime("-7 days"));
                // last week sunday
                $to = date("Y-m-d",strtotime("-1 days"));
                                
                $agen_act_params = array(
                    'from' => $from,
                    'to' => $to,
                    'subject' => "Weekly Agent Activity ({$country_row->iso}) for WE ".date('d/m/Y')
                );
                $this->email_functions_model->agent_activity($agen_act_params);                               
                
                
                // finish cron log
                if( $cron_log_id > 0 ){

                    $this->db->query("
                        UPDATE `cron_log` 
                        SET finished = NOW() 
                        WHERE log_id = {$cron_log_id}
                    ");

                }    
                                                                  

                echo "Cron job has finished executing";

            }else{
                echo "Cron job has already ran this week";
            }  

        }                                             

    }



    // orig old crm filename: cron_activity_au.php
    public function daily_agent_activity(){

        $cron_type_id = 7; // CRON type

        // insert staff ID or cron ID
        $triggered_by =  ( $this->session->staff_id > 0 )?$this->session->staff_id:-1;

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        // get individual cron active status
        $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id);

        if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');
            
            $current_week = intval(date('W'));
            $current_year = date('Y');

            // get cron logs
            $this->db->select("COUNT(log_id) AS cl_count");
            $this->db->from('cron_log');
            $this->db->where('type_id',$cron_type_id);
            $this->db->where('week_no',$current_week);
            $this->db->where('year',$current_year);
            $this->db->where("CAST(`started` AS DATE) = '{$today}'");
            $this->db->where('country_id',$country_id);       
            $cron_log_sql = $this->db->get();        
            $cron_log_count = $cron_log_sql->row()->cl_count;        
        
            if( $cron_log_count == 0 ){ // check if cron already ran                
                
                
                // start cron log
                $this->db->query("
                    INSERT INTO 
                    `cron_log` (
                        `type_id`, 
                        `week_no`, 
                        `year`, 
                        `started`, 
                        `country_id`,
                        `triggered_by`
                    ) 
                    VALUES (
                        {$cron_type_id},
                        {$current_week},
                        {$current_year}, 
                        NOW(), 
                        {$country_id},
                        {$triggered_by}
                    )
                ");
                $cron_log_id = $this->db->insert_id();       
                      

                // get country data
                $country_params = array(
                    'sel_query' => 'c.agent_number, c.outgoing_email, c.iso',
                    'country_id' => $country_id
                );
                $country_sql = $this->system_model->get_countries($country_params);
                $country_row = $country_sql->row();
                                
                $agen_act_params = array(
                    'from' => $today,
                    'to' => $today,
                    'subject' => "Agent Activity ({$country_row->iso}) for ".date('d/m/Y')
                );
                $this->email_functions_model->agent_activity($agen_act_params);               
                
                
                // finish cron log
                if( $cron_log_id > 0 ){

                    $this->db->query("
                        UPDATE `cron_log` 
                        SET finished = NOW() 
                        WHERE log_id = {$cron_log_id}
                    ");

                }  
                                      

                echo "Cron job has finished executing";

            }else{
                echo "Cron job has already ran this week";
            }   

        }                                            

    }




    // agency portal source file filename: /controller/Compliance.php
    public function send_agency_compliance(){

        

        $cron_type_id = 7; // CRON type

        // insert staff ID or cron ID
        $triggered_by =  ( $this->session->staff_id > 0 )?$this->session->staff_id:-1;

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        // get individual cron active status
        $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id);

        if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');
            
            $current_week = intval(date('W'));
            $current_year = date('Y');

            // get cron logs
            $this->db->select("COUNT(log_id) AS cl_count");
            $this->db->from('cron_log');
            $this->db->where('type_id',$cron_type_id);
            $this->db->where('week_no',$current_week);
            $this->db->where('year',$current_year);
            $this->db->where("CAST(`started` AS DATE) = '{$today}'");
            $this->db->where('country_id',$country_id);       
            $cron_log_sql = $this->db->get();        
            $cron_log_count = $cron_log_sql->row()->cl_count;        
        
            if( $cron_log_count == 0 ){ // check if cron already ran
                
                /*
                // start cron log
                $this->db->query("
                    INSERT INTO 
                    `cron_log` (
                        `type_id`, 
                        `week_no`, 
                        `year`, 
                        `started`, 
                        `country_id`,
                        `triggered_by`
                    ) 
                    VALUES (
                        {$cron_type_id},
                        {$current_week},
                        {$current_year}, 
                        NOW(), 
                        {$country_id},
                        {$triggered_by}
                    )
                ");
                $cron_log_id = $this->db->insert_id();   
                */

                $this->email_functions_model->agency_compliance();               
                
                /*
                // finish cron log
                if( $cron_log_id > 0 ){

                    $this->db->query("
                        UPDATE `cron_log` 
                        SET finished = NOW() 
                        WHERE log_id = {$cron_log_id}
                    ");

                }    
            */
                                    

                echo "Cron job has finished executing";

            }else{
                echo "Cron job has already ran this week";
            }   

        }                                            

    }


    // orig old crm filename: merged_email_all_cron_au.php
    public function email_merge_job_invoice(){

        $cron_type_id = 28; // CRON type

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons, cron_merged_cert"
        ]);
        $crm_row = $crm_sql->row();

        // get individual cron active status
        $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id);

        if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');

            $this->email_functions_model->batchSendInvoicesCertificates($country_id); 

            echo "Cron job has finished executing";

        }                                                    

    }


    // new, no old crm orig file
    public function sms_merge_job_invoice(){

        $cron_type_id = 29; // CRON type

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons, cron_merge_sms"
        ]);
        $crm_row = $crm_sql->row();

        // get individual cron active status
        $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id);

        if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');

            $this->jobs_model->merged_jobs_sms_send_model($country_id); 

            echo "Cron job has finished executing";

        }        
                                        

    }


    // new, no old crm orig file
    public function post_invoice_to_api(){

        $cron_type_id = 30; // CRON type

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons, cron_pme_upload"
        ]);
        $crm_row = $crm_sql->row();

        // get individual cron active status
        $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id);

        if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){

            $this->load->model('Pme_model');
            $this->load->model('Palace_model');
            $this->load->model('console_model');

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');

            $this->Pme_model->send_all_certificates_and_invoices(); 

            $this->Palace_model->send_all_certificates_and_invoices();

            $this->console_model->send_all_certificates_and_invoices();            

            echo "Cron job has finished executing";

        }        
                                        

    }


    // new, no old crm orig file
    public function unservice_mark_properties(){

        $cron_type_id = 32; // CRON type

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons, cron_mark_unservice"
        ]);
        $crm_row = $crm_sql->row();

        // get individual cron active status
        $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id);

        if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){

            $this->load->model('daily_model');

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');

            $this->daily_model->mark_unserviced_property_for_cron();

            echo "Cron job has finished executing";

        }                                                

    }

    // orig old crm filename: cron_send_letter_functions_au.php
    public function process_send_letters(){

        $cron_type_id = 31; // CRON type

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons, cron_send_letters"
        ]);
        $crm_row = $crm_sql->row();

        // get individual cron active status
        $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id);

        if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');
            
            $this->cron_model->process_send_letters();

            echo "Cron job has finished executing";

        }        
                                                
    }


    public function ajax_send_letters_cron_toggle(){

        $country_id = $this->config->item('country');
        $cron_send_letters = $this->input->get_post('cron_send_letters');

        $this->db->query("
            UPDATE `crm_settings` 
            SET `cron_send_letters` = {$cron_send_letters}
            WHERE `country_id` = {$country_id}
        ");      

    }



    // orig old crm filename: cron_send_no_show_sms_au.php
    public function send_no_show_sms(){

        $cron_type_id = 13; // CRON type

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        // get individual cron active status
        $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id);

        if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');
    
            $this->cron_model->send_no_show_sms();  

            echo "Cron job has finished executing";

        }                                             

    }



    // orig old crm filename: cron_send_no_show_sms_au.php
    public function update_page_totals(){

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        if( $crm_row->disable_all_crons == 0 ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');
    
            $this->cron_model->update_page_totals();  

            echo "Cron job has finished executing";

        }                                             

    }

    public function update_main_page_totals(){

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        if( $crm_row->disable_all_crons == 0 ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');
    
            $this->cron_model->updateMainPageTotal();  

            echo "Cron job has finished executing";

        }                                             

    }



    // new, no old crm orig file
    public function email_weekly_sales_report(){

        
		$cron_type_id = 35; // CRON type, Weekly Sales Report
					 
        $country_id = $this->config->item('country');             
        $current_week = intval(date('W'));
        $current_year = date('Y');

        // insert staff ID or cron ID
        $triggered_by =  ( $this->session->staff_id > 0 )?$this->session->staff_id:-1;

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        if( $crm_row->disable_all_crons == 0 ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');

            // start cron log
            $this->db->query("
            INSERT INTO 
                `cron_log` (
                    `type_id`, 
                    `week_no`, 
                    `year`, 
                    `started`, 
                    `country_id`,
                    `triggered_by`
                ) 
                VALUES (
                    {$cron_type_id},
                    {$current_week},
                    {$current_year}, 
                    NOW(), 
                    {$country_id},
                    {$triggered_by}
                )
            ");
            $cron_log_id = $this->db->insert_id();

            $this->email_functions_model->email_weekly_sales_report(); 
            
            $this->db->query("
            UPDATE `cron_log` 
            SET finished = NOW() 
            WHERE log_id = {$cron_log_id}
            ");

            echo "Cron job has finished executing"; 

        }                                         

    }


    // new, no old crm orig file
    public function email_weekly_sales_report_reminder(){

        $cron_type_id = 36; // CRON type, Weekly Sales Report Reminder
					 
        $country_id = $this->config->item('country');             
        $current_week = intval(date('W'));
        $current_year = date('Y');

        // insert staff ID or cron ID
        $triggered_by =  ( $this->session->staff_id > 0 )?$this->session->staff_id:-1;

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        if( $crm_row->disable_all_crons == 0 ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');

            // start cron log
            $this->db->query("
            INSERT INTO 
                `cron_log` (
                    `type_id`, 
                    `week_no`, 
                    `year`, 
                    `started`, 
                    `country_id`,
                    `triggered_by`
                ) 
                VALUES (
                    {$cron_type_id},
                    {$current_week},
                    {$current_year}, 
                    NOW(), 
                    {$country_id},
                    {$triggered_by}
                )
            ");
            $cron_log_id = $this->db->insert_id();

            $this->email_functions_model->email_weekly_sales_report_reminder();  

            $this->db->query("
            UPDATE `cron_log` 
            SET finished = NOW() 
            WHERE log_id = {$cron_log_id}
            ");

            echo "Cron job has finished executing"; 

        }                                         

    }


    // new, no old crm orig file
    public function email_completed_ic_ugprade(){

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        if( $crm_row->disable_all_crons == 0 ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');

            $this->email_functions_model->email_completed_ic_ugprade();  

            echo "Cron job has finished executing"; 

        }                                         

    }


    // new, no old crm orig file
    public function pme_find_unmatched_properties(){

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        if( $crm_row->disable_all_crons == 0 ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');

            $this->cron_model->pme_find_unmatched_properties();  

            echo "Cron job has finished executing"; 

        }                                         

    }

    // new, no old crm orig file
    public function api_find_unmatched_properties(){

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        if( $crm_row->disable_all_crons == 0 ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');

            $this->cron_model->api_find_unmatched_properties();  

            echo "Cron job has finished executing"; 

        }                                         

    }

    // new, no old crm orig file
    public function ourtradie_find_unmatched_properties(){

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        if( $crm_row->disable_all_crons == 0 ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');

            $this->ourtradie_model->ourtradie_find_unmatched_properties();  

            echo "Cron job has finished executing"; 

        }                                         
    }


    // orig old crm filename: send_report.php
    public function send_qld_upgrade_report(){  

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        if( $crm_row->disable_all_crons == 0 ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');

            $this->email_functions_model->send_qld_upgrade_report();      

            echo "Cron job has finished executing"; 

        }                                                 
                                                       

    }


    // new
    public function send_once_off_report(){  

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        if( $crm_row->disable_all_crons == 0 ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');

            $this->email_functions_model->send_once_off_report();      

            echo "Cron job has finished executing"; 

        }                                                                                                    

    }

    // new
    public function create_active_properties_snapshot(){  

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        if( $crm_row->disable_all_crons == 0 ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');

            $this->cron_model->create_active_properties_snapshot();      

            echo "Cron job has finished executing"; 

        }                                                                                                    

    }


    // orig old crm filename: send_report.php
    public function email_tech_user_feedback(){  
        
        $cron_type_id = 34; // CRON type, Tech Tenant Feedback

        // insert staff ID or cron ID
        $triggered_by =  ( $this->session->staff_id > 0 )?$this->session->staff_id:-1;

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        // get individual cron active status
        $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id);     

        if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){

            $country_id = $this->config->item('country');             
            $current_week = intval(date('W'));
            $current_year = date('Y');
    
            // get cron logs
            $this->db->select("COUNT(log_id) AS cl_count");
            $this->db->from('cron_log');
            $this->db->where('type_id',$cron_type_id);
            $this->db->where('week_no',$current_week);
            $this->db->where('year',$current_year);
            $this->db->where('country_id',$country_id);
            $cron_log_sql = $this->db->get();        
            $cron_log_count = $cron_log_sql->row()->cl_count;    

            if( $cron_log_count == 0 ){ // check if cron already ran
            
                
                // start cron log
                $this->db->query("
                    INSERT INTO 
                    `cron_log` (
                        `type_id`, 
                        `week_no`, 
                        `year`, 
                        `started`, 
                        `country_id`,
                        `triggered_by`
                    ) 
                    VALUES (
                        {$cron_type_id},
                        {$current_week},
                        {$current_year}, 
                        NOW(), 
                        {$country_id},
                        {$triggered_by}
                    )
                ");
                $cron_log_id = $this->db->insert_id();    
                                           
    
                $this->email_functions_model->email_tech_user_feedback();                   
                
                
                // finish cron log
                if( $cron_log_id > 0 ){
    
                    $this->db->query("
                        UPDATE `cron_log` 
                        SET finished = NOW() 
                        WHERE log_id = {$cron_log_id}
                    ");
    
                } 
                              
    
                echo "Cron job has finished executing";
    
            }else{
                echo "Cron job has already ran this week";
            }

        }                                                       

    }

    
    // new
    public function weekly_qld_compliance_report(){  
                
        $cron_type_id = 37; // CRON type, Weekly QLD Compliance Report

        // insert staff ID or cron ID
        $triggered_by =  ( $this->session->staff_id > 0 )?$this->session->staff_id:-1;

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        // get individual cron active status
        $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id);     

        if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){

            $country_id = $this->config->item('country');             
            $current_week = intval(date('W'));
            $current_year = date('Y');
    
            // get cron logs
            $this->db->select("COUNT(log_id) AS cl_count");
            $this->db->from('cron_log');
            $this->db->where('type_id',$cron_type_id);
            $this->db->where('week_no',$current_week);
            $this->db->where('year',$current_year);
            $this->db->where('country_id',$country_id);
            $cron_log_sql = $this->db->get();        
            $cron_log_count = $cron_log_sql->row()->cl_count;    

            if( $cron_log_count == 0 ){ // check if cron already ran
                            
                // start cron log
                $this->db->query("
                    INSERT INTO 
                    `cron_log` (
                        `type_id`, 
                        `week_no`, 
                        `year`, 
                        `started`, 
                        `country_id`,
                        `triggered_by`
                    ) 
                    VALUES (
                        {$cron_type_id},
                        {$current_week},
                        {$current_year}, 
                        NOW(), 
                        {$country_id},
                        {$triggered_by}
                    )
                ");
                $cron_log_id = $this->db->insert_id();    
                                               
                $this->email_functions_model->weekly_qld_compliance_report();                       
                
                // finish cron log
                if( $cron_log_id > 0 ){
    
                    $this->db->query("
                        UPDATE `cron_log` 
                        SET finished = NOW() 
                        WHERE log_id = {$cron_log_id}
                    ");
    
                }                               
    
                echo "Cron job has finished executing";
    
            }else{
                echo "Cron job has already ran this week";
            }

        }         

    }


    // orig old crm filename: multi_cron_flush.php
    public function multi_cron_flush(){

          
       
        // cron flush settings
        $flush_params = array(
            'show_select_query' => true,
            'run_delete_query' => true
        );

        // insert staff ID or cron ID
        $triggered_by =  ( $this->session->staff_id > 0 )?$this->session->staff_id:-1;


         
        
        // tech run flush
        // orig old crm filename: cron_flush_tech_run_au.php
        $tech_run_flush_del  = $this->cron_model->tech_run_flush($flush_params);  
            
        if( $tech_run_flush_del == true ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');
    
            $cron_type_id = 11; // CRON type
            $current_week = intval(date('W'));
            $current_year = date('Y');

            // start cron log
            $this->db->query("
                INSERT INTO 
                `cron_log` (
                    `type_id`, 
                    `week_no`, 
                    `year`, 
                    `started`, 
                    `finished`,
                    `country_id`,
                    `triggered_by`                    
                ) 
                VALUES (
                    {$cron_type_id},
                    {$current_week},
                    {$current_year}, 
                    NOW(), 
                    NOW(), 
                    {$country_id},
                    {$triggered_by}
                )
            ");

        }  
        
        // SMS flush
        // orig old crm filename: cron_delete_sms_au.php
        $sms_flush_del  = $this->cron_model->sms_flush($flush_params);  
            
        if( $sms_flush_del == true ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');
    
            $cron_type_id = 14; // CRON type
            $current_week = intval(date('W'));
            $current_year = date('Y');

                // start cron log
            $this->db->query("
                INSERT INTO 
                `cron_log` (
                    `type_id`, 
                    `week_no`, 
                    `year`, 
                    `started`, 
                    `finished`,
                    `country_id`,
                    `triggered_by`
                ) 
                VALUES (
                    {$cron_type_id},
                    {$current_week},
                    {$current_year}, 
                    NOW(), 
                    NOW(), 
                    {$country_id},
                    {$triggered_by}
                )
            ");

        }
  
        // agency login flush
        // orig old crm filename: cron_delete_agency_tracking_au.php
        $agency_login_flush_del  = $this->cron_model->agency_login_flush($flush_params);  
            
        if( $agency_login_flush_del == true ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');
    
            $cron_type_id = 15; // CRON type
            $current_week = intval(date('W'));
            $current_year = date('Y');

                // start cron log
            $this->db->query("
                INSERT INTO 
                `cron_log` (
                    `type_id`, 
                    `week_no`, 
                    `year`, 
                    `started`, 
                    `finished`,
                    `country_id`,
                    `triggered_by`
                ) 
                VALUES (
                    {$cron_type_id},
                    {$current_week},
                    {$current_year}, 
                    NOW(), 
                    NOW(), 
                    {$country_id},
                    {$triggered_by}
                )
            ");

        }
  
        // cron log flush
        // orig old crm filename: cron_flush_cron_logs_au.php
        $cron_log_flush_del  = $this->cron_model->cron_log_flush($flush_params);  
            
        if( $cron_log_flush_del == true ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');
    
            $cron_type_id = 16; // CRON type
            $current_week = intval(date('W'));
            $current_year = date('Y');

                // start cron log
            $this->db->query("
                INSERT INTO 
                `cron_log` (
                    `type_id`, 
                    `week_no`, 
                    `year`, 
                    `started`, 
                    `finished`,
                    `country_id`,
                    `triggered_by`
                ) 
                VALUES (
                    {$cron_type_id},
                    {$current_week},
                    {$current_year}, 
                    NOW(), 
                    NOW(), 
                    {$country_id},
                    {$triggered_by}
                )
            ");

        }
      
        // calendar flush
        // orig old crm filename: multi_cron_flush.php
        $calendar_flush_del  = $this->cron_model->calendar_flush($flush_params);  
            
        if( $calendar_flush_del == true ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');
    
            $cron_type_id = 19; // CRON type
            $current_week = intval(date('W'));
            $current_year = date('Y');

                // start cron log
            $this->db->query("
                INSERT INTO 
                `cron_log` (
                    `type_id`, 
                    `week_no`, 
                    `year`, 
                    `started`, 
                    `finished`,
                    `country_id`,
                    `triggered_by`
                ) 
                VALUES (
                    {$cron_type_id},
                    {$current_week},
                    {$current_year}, 
                    NOW(), 
                    NOW(), 
                    {$country_id},
                    {$triggered_by}
                )
            ");

        }
     
        // tech run colour flush
        // orig old crm filename: multi_cron_flush.php
        $tech_run_colour_flush_del  = $this->cron_model->tech_run_colour_flush($flush_params);  
            
        if( $tech_run_colour_flush_del == true ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');
    
            $cron_type_id = 20; // CRON type
            $current_week = intval(date('W'));
            $current_year = date('Y');

                // start cron log
            $this->db->query("
                INSERT INTO 
                `cron_log` (
                    `type_id`, 
                    `week_no`, 
                    `year`, 
                    `started`, 
                    `finished`,
                    `country_id`,
                    `triggered_by`
                ) 
                VALUES (
                    {$cron_type_id},
                    {$current_week},
                    {$current_year}, 
                    NOW(), 
                    NOW(), 
                    {$country_id},
                    {$triggered_by}
                )
            ");

        }

        // notification flush
        // orig old crm filename: multi_cron_flush.php
        $notification_flush_del  = $this->cron_model->notification_flush($flush_params);  
            
        if( $notification_flush_del == true ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');
    
            $cron_type_id = 21; // CRON type
            $current_week = intval(date('W'));
            $current_year = date('Y');

                // start cron log
            $this->db->query("
                INSERT INTO 
                `cron_log` (
                    `type_id`, 
                    `week_no`, 
                    `year`, 
                    `started`, 
                    `finished`,
                    `country_id`,
                    `triggered_by`
                ) 
                VALUES (
                    {$cron_type_id},
                    {$current_week},
                    {$current_year}, 
                    NOW(), 
                    NOW(), 
                    {$country_id},
                    {$triggered_by}
                )
            ");

        }

        // message flush
        // orig old crm filename: multi_cron_flush.php
        $message_flush_del  = $this->cron_model->message_flush($flush_params);  
            
        if( $message_flush_del == true ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');
    
            $cron_type_id = 22; // CRON type
            $current_week = intval(date('W'));
            $current_year = date('Y');

                // start cron log
            $this->db->query("
                INSERT INTO 
                `cron_log` (
                    `type_id`, 
                    `week_no`, 
                    `year`, 
                    `started`, 
                    `finished`,
                    `country_id`,
                    `triggered_by`
                ) 
                VALUES (
                    {$cron_type_id},
                    {$current_week},
                    {$current_year}, 
                    NOW(), 
                    NOW(), 
                    {$country_id},
                    {$triggered_by}
                )
            ");

        }
     
        // SMS sent flush
        // orig old crm filename: multi_cron_flush.php
        $sms_sent_flush_del  = $this->cron_model->sms_sent_flush($flush_params);  
            
        if( $sms_sent_flush_del == true ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');
    
            $cron_type_id = 23; // CRON type
            $current_week = intval(date('W'));
            $current_year = date('Y');

                // start cron log
            $this->db->query("
                INSERT INTO 
                `cron_log` (
                    `type_id`, 
                    `week_no`, 
                    `year`, 
                    `started`, 
                    `finished`,
                    `country_id`,
                    `triggered_by`
                ) 
                VALUES (
                    {$cron_type_id},
                    {$current_week},
                    {$current_year}, 
                    NOW(), 
                    NOW(), 
                    {$country_id},
                    {$triggered_by}
                )
            ");

        }

        // SMS replies flush
        // orig old crm filename: multi_cron_flush.php
        $sms_replies_flush_del  = $this->cron_model->sms_replies_flush($flush_params);  
            
        if( $sms_replies_flush_del == true ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');
    
            $cron_type_id = 24; // CRON type
            $current_week = intval(date('W'));
            $current_year = date('Y');

                // start cron log
            $this->db->query("
                INSERT INTO 
                `cron_log` (
                    `type_id`, 
                    `week_no`, 
                    `year`, 
                    `started`, 
                    `finished`,
                    `country_id`,
                    `triggered_by`
                ) 
                VALUES (
                    {$cron_type_id},
                    {$current_week},
                    {$current_year}, 
                    NOW(), 
                    NOW(), 
                    {$country_id},
                    {$triggered_by}
                )
            ");

        }
 
        // KMS flush
        // orig old crm filename: multi_cron_flush.php
        //$kms_flush_del  = $this->cron_model->kms_flush($flush_params);  
        
        /*
        if( $kms_flush_del == true ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');
    
            $cron_type_id = 25; // CRON type
            $current_week = intval(date('W'));
            $current_year = date('Y');

                // start cron log
            $this->db->query("
                INSERT INTO 
                `cron_log` (
                    `type_id`, 
                    `week_no`, 
                    `year`, 
                    `started`, 
                    `finished`,
                    `country_id`,
                    `triggered_by`
                ) 
                VALUES (
                    {$cron_type_id},
                    {$current_week},
                    {$current_year}, 
                    NOW(), 
                    NOW(), 
                    {$country_id},
                    {$triggered_by}
                )
            ");

        }
        */
   
        // Tech Stocktake flush
        // orig old crm filename: multi_cron_flush.php
        $tech_stocktake_flush_del  = $this->cron_model->tech_stocktake_flush($flush_params);  
            
        if( $tech_stocktake_flush_del == true ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');
    
            $cron_type_id = 26; // CRON type
            $current_week = intval(date('W'));
            $current_year = date('Y');

                // start cron log
            $this->db->query("
                INSERT INTO 
                `cron_log` (
                    `type_id`, 
                    `week_no`, 
                    `year`, 
                    `started`, 
                    `finished`,
                    `country_id`,
                    `triggered_by`
                ) 
                VALUES (
                    {$cron_type_id},
                    {$current_week},
                    {$current_year}, 
                    NOW(), 
                    NOW(), 
                    {$country_id},
                    {$triggered_by}
                )
            ");

        }
  
        
        // flush agency old escalate notes
        // orig old crm filename: cron_flush_escalate_old_notes_au.php
        //$this->cron_model->agency_old_escate_notes_flush($flush_params);
            

    }


    //Test Email - Chops
    public function test_email(){
        // subject
        $subject = "Keys to be collected";
                
        $from_email = "chopstick.toto@gmail.com";
        $from_name = 'Smoke Alarm Testing Services';   
        $to_email = "lpagiwayan@gmail.com";                                                

        // email settings
        $email_config = Array(
            'mailtype' => 'html',
            'charset' => 'utf-8'
        );
        $this->email->initialize($email_config);    
        $this->email->clear(TRUE);        
        $this->email->from($from_email, $from_name);                
        $this->email->to($to_email); 
        //$this->email->cc($email_cc);                    
        //$this->email->bcc($this->config->item('sats_cc_email'));  //Stopped sending to cc@sats on 02/09/2020 as per Daniel’s instructions

        $this->email->subject($subject);
        $this->email->message($email_body);

        // send email
        $this->email->send();
    }

    public function update_retest_date(){

        // insert staff ID or cron ID
        $triggered_by =  ( $this->session->staff_id > 0 )?$this->session->staff_id:-1;

        $cron_type_id = 38; 
        $timestamp = date('Y-m-d H:i:s');
        $older_180_days = date('Y-m-d',strtotime("-180 days"));

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        // get individual cron active status
        $indiv_cron_active = $this->system_model->get_cron_active_status($cron_type_id);

        if( $crm_row->disable_all_crons == 0 && $indiv_cron_active == 1 ){

            $current_week = intval(date('W'));
            $current_year = date('Y');    
            $country_id = COUNTRY;

            $ttquery = "
            SELECT p.property_id FROM `property` AS p 
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE ( (p.retest_date IS NULL OR p.retest_date='') OR (p.retest_timestamp < '{$older_180_days}' OR p.retest_timestamp IS NULL) )
            AND p.deleted = 0
            AND (p.is_nlm IS NULL OR p.is_nlm=0)
            AND a.status = 'active'
            AND a.deleted = 0
            ";
            $lists = $this->db->query($ttquery);
        
            if( $lists->num_rows()>0 ){ #check array > not empty

                foreach( $lists->result_array() as $row ){ ##loop property

                    $property_id = $row['property_id'];

                    ## RETEST DATE UPDATE CODE > SAME AS VPD

                    ##Retest Date Update from VPD
                    //First query without assigned_tech and status filter
                    $sql_recent_job_no_tech_filter = $this->db->query("
                    SELECT j.id as j_id, j.date AS jdate, j.job_type as j_type
                    FROM `jobs` AS j
                    LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                    LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                    WHERE j.`property_id` = {$property_id}
                    AND j.`del_job` = 0
                    AND a.`country_id` = {$this->config->item('country')}
                    AND a.deleted = 0
                    ORDER BY j.`date` DESC
                    LIMIT 1
                    ");
                    $sql_recent_job_no_tech_filter_fet_arr = $sql_recent_job_no_tech_filter->row_array();

                    if($sql_recent_job_no_tech_filter_fet_arr['jdate'] > '2015-12-31'){
                    $assigned_tech_filter = "AND j.`assigned_tech` IS NOT NULL AND j.`assigned_tech` !=2"; // New > removed Other Supplier filter > Reason: We are assuming that SOMEONE has attended that property at that time, so we only need to attend a year after that point
                    }

                    //Second query with assigned_tech filter option based on job date condition
                    $sql_recent_job = $this->db->query("
                    SELECT j.id as j_id, j.date AS jdate, j.job_type as j_type
                    FROM `jobs` AS j
                    LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                    LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                    WHERE j.`property_id` = {$property_id}
                    AND j.`status` = 'Completed'
                    AND j.`del_job` = 0
                    AND a.`country_id` = {$this->config->item('country')}
                    AND a.deleted = 0
                    {$assigned_tech_filter}
                    ORDER BY j.`date` DESC
                    LIMIT 1
                    ");

                    $recent_jobdate_fetch_arr = $sql_recent_job->row_array();
                    $recent_jobdate = $recent_jobdate_fetch_arr['jdate'];
                    $recent_job_type = $recent_jobdate_fetch_arr['j_type'];

                    if( $sql_recent_job->num_rows()>0 ){ //recent completed job found
                        if( $property_id!="" ){ //check property id
                            if($recent_job_type=="Once-off"){ //once-off job > update retest_date to 1521-03-16
                                $this->db->query("
                                    UPDATE `property`
                                    SET `retest_date` = '1521-03-16', retest_timestamp = '{$timestamp}'
                                    WHERE `property_id` = {$property_id}
                                ");
                            }else{ // not once-off job > update retest_date to job_date+1year
                                $this->db->query("
                                    UPDATE `property`
                                    SET `retest_date` = DATE_ADD('$recent_jobdate', INTERVAL 1 YEAR), retest_timestamp = '{$timestamp}'
                                    WHERE `property_id` = {$property_id}
                                ");
                            }
                    
                        }
                    }else{ //if empty result > find job != Completed if return row update retest_date to job_date+365 otherwise update to null
                        $sql_not_completed_job = $this->db->query("
                            SELECT j.id as j_id, j.date AS jdate, j.job_type as j_type, j.created as j_created
                            FROM `jobs` AS j
                            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                            WHERE j.`property_id` = {$property_id}
                            AND j.`status` != 'Completed'
                            AND j.`del_job` = 0
                            AND a.`country_id` = {$this->config->item('country')}
                            AND a.deleted = 0
                            ORDER BY j.`date` DESC
                            LIMIT 1
                        ");
                        $recent_not_completed_jobdate_fetch_arr =$sql_not_completed_job->row_array();
                        $recent_not_completed_jobdate = $recent_not_completed_jobdate_fetch_arr['j_created'];
                        if( $sql_not_completed_job->num_rows()>0 ){ //active job found update to job date + 635 days
                            if( $property_id!="" ){ //check property id
                                $this->db->query("
                                    UPDATE `property`
                                    SET `retest_date` = DATE_ADD('$recent_not_completed_jobdate', INTERVAL 1 YEAR),retest_timestamp = '{$timestamp}'
                                    WHERE `property_id` = {$property_id}
                                ");
                            }
                        }else{ //no active job found > update retest_date to NULL
                            if( $property_id!="" ){ //check property id
                                $this->db->query("
                                    UPDATE `property`
                                    SET `retest_date` = '1521-03-17', retest_timestamp = '{$timestamp}'
                                    WHERE `property_id` = {$property_id}
                                ");
                            }
                        }
                    
                    }


                    $prop_array[] = $property_id;

                }

                //Inser Cron Log
                $this->db->query("
                    INSERT INTO 
                    `cron_log` (
                        `type_id`, 
                        `week_no`, 
                        `year`, 
                        `started`, 
                        `country_id`,
                        `triggered_by`
                    ) 
                    VALUES (
                        {$cron_type_id},
                        {$current_week},
                        {$current_year}, 
                        NOW(), 
                        {$country_id},
                        {$triggered_by}
                    )
                ");
                $cron_log_id = $this->db->insert_id(); 

                // finish cron log
                if( $cron_log_id > 0 ){

                    $this->db->query("
                        UPDATE `cron_log` 
                        SET finished = NOW() 
                        WHERE log_id = {$cron_log_id}
                    ");

                }   
                
                $p_arr = implode(", ",$prop_array);
                echo "Property checked:<br/>{$p_arr}";
                echo "<br/>";
                echo "Cron job has finished executing";

            }else{
                echo "Empty ID, process cannnot proceed!";
            }

        }

       

    }


    public function update_active_job_price_from_property_service_price(){

		$country_id = $this->config->item('country');

		// select active jobs
		$jobs_sql = $this->db->query("
		SELECT 
			j.`id` AS jid,
			j.`service` AS jservice,
			j.`job_price`,
            j.`created` AS jcreated,   

			p.`property_id`,

            a.`agency_id`
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		WHERE a.`status` = 'active'
		AND a.`deleted` = 0
		AND p.`deleted` = 0
		AND p.`is_sales` = 0
		AND j.`del_job` = 0
		AND a.`country_id` = {$country_id}
		AND j.`status` NOT IN('Cancelled','Completed','Merged Certificates')
		AND j.`job_type` != 'IC Upgrade'
		AND j.`job_price` > 0
		");

        // email content
        $email_body = "
        <p>Dear IT,</p>

        <p>We updated the following jobs:</p>

        <table>
            <tr>
                <th>Job ID</th>
                <th>Job Log</th>
            </tr>
        ";

        $jobs_updated_count = 0;
		foreach( $jobs_sql->result() as $jobs_row ){ 

            // dynamic price, from property service or variations
            $price_var_params = array(
                'service_type' => $jobs_row->jservice,
                'property_id' => $jobs_row->property_id
            );
            $price_var_arr = $this->system_model->get_property_price_variation($price_var_params);
            $update_price_to = $price_var_arr['dynamic_price_total'];

            // object parameters
            $agency_ex_obj = (object)[
                'agency_id' => $jobs_row->agency_id,
                'jcreated' => $jobs_row->jcreated
            ];
            $agency_lvl_ex_obj = (object)[
                'agency_id' => $jobs_row->agency_id,
                'jcreated' => $jobs_row->jcreated
            ];
            $prop_lvl_ex_obj = (object)[
                'property_id' => $jobs_row->property_id,
                'jcreated' => $jobs_row->jcreated
            ];
            $serv_lvl_ex_obj = (object)[
                'agency_id' => $jobs_row->agency_id,
                'jcreated' => $jobs_row->jcreated,
                'service_type' => $jobs_row->jservice
            ];

			if( $jobs_row->jid && ( $update_price_to > 0 && $jobs_row->job_price != $update_price_to ) ){				

                // check job variation
                $jv_sql = $this->db->query("
                    SELECT COUNT(`id`) AS jv_count
                    FROM `job_variation`
                    WHERE `job_id` = {$jobs_row->jid}
                    AND `active` = 1
                ");

                if( $jv_sql->row()->jv_count > 0 ){ // job variation exist, skip update

                    $email_body .= "
                    <tr>
                        <td><a href='{$this->config->item('crm_link')}/view_job_details.php?id={$jobs_row->jid}' target='_blank'>{$jobs_row->jid}</a></td>
                        <td>Skipped as job variation existed</td>
                    </tr>
                    ";                    

                }else if( $this->system_model->check_if_job_created_before_agency_exclusion_expired($agency_ex_obj) == true ){ // agency exclusion

                    $email_body .= "
                    <tr>
                        <td><a href='{$this->config->item('crm_link')}/view_job_details.php?id={$jobs_row->jid}' target='_blank'>{$jobs_row->jid}</a></td>
                        <td>Not updated as job was created prior to the agency's exclusion expiring</td>
                    </tr>
                    ";                    

                }else if( $this->system_model->check_if_job_created_before_agency_level_variation_expired($agency_lvl_ex_obj) == true ){ // agency level exclusion

                    $email_body .= "
                    <tr>
                        <td><a href='{$this->config->item('crm_link')}/view_job_details.php?id={$jobs_row->jid}' target='_blank'>{$jobs_row->jid}</a></td>
                        <td>Not updated as job was created prior to an agency level exclusion expiring</td>
                    </tr>
                    ";                    

                }else if( $this->system_model->check_if_job_created_before_property_level_variation_expired($prop_lvl_ex_obj) == true ){ // property level exclusion

                    $email_body .= "
                    <tr>
                        <td><a href='{$this->config->item('crm_link')}/view_job_details.php?id={$jobs_row->jid}' target='_blank'>{$jobs_row->jid}</a></td>
                        <td>Not updated as job was created prior to a property level exclusion expiring</td>
                    </tr>
                    ";                    

                }else if( $this->system_model->check_if_job_created_before_service_level_variation_expired($serv_lvl_ex_obj) == true ){ // service level exclusion

                    $email_body .= "
                    <tr>
                        <td><a href='{$this->config->item('crm_link')}/view_job_details.php?id={$jobs_row->jid}' target='_blank'>{$jobs_row->jid}</a></td>
                        <td>Not updated as job was created prior to a service level exclusion expiring</td>
                    </tr>
                    ";                    

                }else{ // update 

                    //Insert log
				    $log_details = "Job price updated from \$".number_format($jobs_row->job_price,2)." to \$".number_format($update_price_to,2)." due to price increase";

                    // update job price
                    $update_sql_str = "
                    UPDATE `jobs`
                    SET `job_price` = {$update_price_to}
                    WHERE `id` = {$jobs_row->jid}
                    ";
                    $this->db->query($update_sql_str);
                    
                    $jobs_updated_count++;

                    // AUTO - UPDATE INVOICE DETAILS
                    $this->system_model->updateInvoiceDetails($jobs_row->jid);  

                    // insert log
                    $log_params = array(
                        'title' => 63, // Job Update
                        'details' => $log_details,
                        'display_in_vjd' => 1,					
                        'job_id' => $jobs_row->jid
                    );

                    // dynamic, triggere by WHO
                    if( $this->session->staff_id > 0 ){ // triggered by user

                        $log_params['created_by_staff'] = $this->session->staff_id;

                    }else{ // triggered by cron

                        $log_params['auto_process'] = 1;

                    }

                    $this->system_model->insert_log($log_params);

                    $email_body .= "
                    <tr>
                        <td><a href='{$this->config->item('crm_link')}/view_job_details.php?id={$jobs_row->jid}' target='_blank'>{$jobs_row->jid}</a></td>
                        <td>{$log_details}</td>
                    </tr>
                    ";                    

                }				                

			}  

			
		}

        $email_body .= "
        </table>

        <p>You're welcome.</p>

        <p>From IT</p>
        ";

        // send email
        // subject
        $subject = "Job price update";                       

        // email settings
        $email_config = Array(
            'mailtype' => 'html',
            'charset' => 'utf-8'
        );
        $this->email->initialize($email_config);    
        $this->email->clear(TRUE);        
        $this->email->from($this->config->item('sats_it_email'),'SATS');                
        $this->email->to($this->config->item('sats_info_email')); 
        $this->email->cc($this->config->item('sats_it_email'));                    
        //$this->email->bcc($this->config->item('sats_cc_email')); 

        $this->email->subject($subject);
        $this->email->message($email_body);

        //if( $jobs_updated_count > 0 ){

            // send email
            $this->email->send();

        //}        

	}


    public function send_sms_to_renewed_jobs_yesterday(){  

        $cron_type_id = 39; // cron type: Pre-Booking Reminder SMS
        $country_id = $this->config->item('country');  

        // insert staff ID or cron ID
        $triggered_by =  ( $this->session->staff_id > 0 )?$this->session->staff_id:-1;
        
        $current_week = intval(date('W'));
        $current_year = date('Y');
        $first_of_current_month = date('Y-m-01');
        $last_day_of_current_month = date('Y-m-t');
        $current_day = date('d');

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        if( $crm_row->disable_all_crons == 0 ){

            // check if this type of cron already run this month
            $cron_log_sql = $this->db->query("
            SELECT COUNT(`log_id`) AS cl_count
            FROM `cron_log`
            WHERE `type_id` = {$cron_type_id}
            AND `finished` BETWEEN '{$first_of_current_month}' AND '{$last_day_of_current_month}'
            ");

            // only run once a month and skip 1st daty of month
            if( $current_day > 1 && $cron_log_sql->row()->cl_count == 0 ){

                // start cron log
                $this->db->query("
                    INSERT INTO 
                    `cron_log` (
                        `type_id`, 
                        `week_no`, 
                        `year`, 
                        `started`, 
                        `country_id`,
                        `triggered_by`
                    ) 
                    VALUES (
                        {$cron_type_id},
                        {$current_week},
                        {$current_year}, 
                        NOW(), 
                        {$country_id},
                        {$triggered_by}
                    )
                ");
                $cron_log_id = $this->db->insert_id();

                $this->cron_model->send_sms_to_renewed_jobs_yesterday();   

               // finish cron log
               if( $cron_log_id > 0 ){

                    $this->db->query("
                        UPDATE `cron_log` 
                        SET finished = NOW() 
                        WHERE log_id = {$cron_log_id}
                    ");

                }    

                echo "Cron job has finished executing";   

            }else{

                echo "Cron job has already ran this month";

            }
             

        }                                                 
                                                       

    }


    public function update_tenant_last_update_ts(){

        $api_id = 1; // PMe
        $today_full = date('Y-m-d H:i:s');

        $this->load->model('pme_model');
        
        // get DISTINCT agency         
        $dist_agency_sql = $this->db->query("
        SELECT DISTINCT(a.`agency_id`)
        FROM `property` AS p
        INNER JOIN `api_property_data` AS apd_pme ON p.`property_id` = apd_pme.`crm_prop_id` AND apd_pme.`api` = {$api_id}
        LEFT JOIN `api_last_tenant_update` AS altu ON apd_pme.`api_prop_id` = altu.`api_prop_id`
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        INNER JOIN `agency_api_tokens` AS aat ON a.`agency_id` = aat.`agency_id`
        WHERE p.`deleted` = 0
        AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
        AND a.`status` = 'active'
        AND a.`deleted` = 0
        AND (
            apd_pme.`api_prop_id` != '' AND
            apd_pme.`api_prop_id` IS NOT NULL
        )
        ");
        
        foreach( $dist_agency_sql->result() as $dist_agency_row ){

            // get api properties per agency       
            $json_response = $this->pme_model->get_all_properties($dist_agency_row->agency_id);
            $pme_prop_arr = json_decode($json_response);

            foreach( $pme_prop_arr as $pme_prop_row ){

                $pme_TenancyUpdatedOn = date('Y-m-d H:i:s',strtotime($pme_prop_row->TenancyUpdatedOn));

                $pme_sql = $this->db->query("
                SELECT COUNT(altu_id) AS altu_count
                FROM `api_last_tenant_update`
                WHERE `api_prop_id` = '{$pme_prop_row->Id}' 
                ");

                if( $pme_sql->row()->altu_count > 0 ){ // exist, update
        
                    $update_data = array(
                        'last_updated_ts' => $pme_TenancyUpdatedOn
                    ); 
                    $this->db->where('agency_id', $dist_agency_row->agency_id);           
                    $this->db->where('api_prop_id', $pme_prop_row->Id);
                    $this->db->update('api_last_tenant_update', $update_data);                        

                }else{ // new                       

                    $insert_data = array(
                        'agency_id' => $dist_agency_row->agency_id,
                        'api_prop_id' => $pme_prop_row->Id,                    
                        'last_updated_ts' => $pme_TenancyUpdatedOn,
                        'created_date' => $today_full               
                    );            
                    $this->db->insert('api_last_tenant_update', $insert_data);     

                }

            }
            
            
        }
        

    }


    public function pme_mark_same_tenant_as_updated(){

        $api_id = 1; // PMe
        $today_full = date('Y-m-d H:i:s');

        $this->load->model('pme_model');
        
        // get DISTINCT agency         
        $dist_agency_sql = $this->db->query("
        SELECT DISTINCT(a.`agency_id`), a.`agency_name`
        FROM `property` AS p
        INNER JOIN `api_property_data` AS apd_pme ON p.`property_id` = apd_pme.`crm_prop_id` AND apd_pme.`api` = {$api_id}
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        INNER JOIN `agency_api_tokens` AS aat ON a.`agency_id` = aat.`agency_id`
        WHERE p.`deleted` = 0
        AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
        AND a.`status` = 'active'
        AND a.`deleted` = 0
        AND (
            apd_pme.`api_prop_id` != '' AND
            apd_pme.`api_prop_id` IS NOT NULL
        )
        ");
        
        $agency_arr = [];
        foreach( $dist_agency_sql->result() as $dist_agency_row ){

            // get api properties per agency       
            $json_response = $this->pme_model->get_all_properties($dist_agency_row->agency_id);
            $pme_prop_dec = json_decode($json_response);

            $pme_prop_arr = [];
            foreach( $pme_prop_dec as $pme_prop_row ){

                // get tenancy contact ID
                $tenants_contact_id = $pme_prop_row->TenantContactId;	

                // get Pme tenants
                $pme_params = array(
                    'agency_id' => $dist_agency_row->agency_id,
                    'tenants_contact_id' => $tenants_contact_id
                );
                $pme_tenant_json = $this->pme_model->get_pme_tenant($pme_params);
                $pme_tenant_decode = json_decode($pme_tenant_json); 

                $pme_tenant_arr = [];
                if( count($pme_tenant_decode->Contact->ContactPersons) > 0 ){                   
                    
                    foreach( $pme_tenant_decode->Contact->ContactPersons as $pme_tenant ){

                        if( $tenants_contact_id != '' && ( $pme_tenant->FirstName != '' || $pme_tenant->LastName != '' ) ){

                            // wrap tenants
                            $pme_tenant_arr[] = (object) [
                                'pme_contact_id' => $tenants_contact_id,
                                'pme_tenant_fname' =>  $pme_tenant->FirstName,
                                'pme_tenant_lname' =>  $pme_tenant->LastName,
                                'pme_tenant_mobile' => $pme_tenant->CellPhone,
                                'pme_tenant_landline' =>  $pme_tenant->HomePhone,
                                'pme_tenant_email' => $pme_tenant->Email
                            ];   

                        }                                                                 
                        
                    }

                }

                // wrap property tenants
                $pme_prop_arr[] = (object) [
                    'pme_prop_id' => $pme_prop_row->Id,
                    'pme_prop_address' => $pme_prop_row->AddressText,
                    'pme_tenant_arr' => ( count($pme_tenant_arr) > 0 )?$pme_tenant_arr:null
                ];

            }

            // wrap agency properties
            $agency_arr[] = (object) [
                'agency_id' => $dist_agency_row->agency_id,
                'agency_name' => $dist_agency_row->agency_name,
                'pme_prop_arr' => ( count($pme_prop_arr) > 0 )?$pme_prop_arr:null
            ];
            
            
        }


        echo "<pre>";
        print_r($agency_arr);
        echo "</pre>";


        echo "<br /><br />";


        // get CRM connected properties    
        $crm_prop_sql = $this->db->query("
        SELECT 
            p.`property_id`,
            apd_pme.`api_prop_id`,

            p.`property_id`,
            p.`address_1` AS p_address_1,
            p.`address_2` AS p_address_2,
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,

            a.`agency_id`,
            a.`agency_name`
        FROM `property` AS p
        INNER JOIN `api_property_data` AS apd_pme ON p.`property_id` = apd_pme.`crm_prop_id` AND apd_pme.`api` = {$api_id}
        LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        INNER JOIN `agency_api_tokens` AS aat ON a.`agency_id` = aat.`agency_id`
        WHERE p.`deleted` = 0
        AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
        AND a.`status` = 'active'
        AND a.`deleted` = 0
        AND (
            apd_pme.`api_prop_id` != '' AND
            apd_pme.`api_prop_id` IS NOT NULL
        )
        ");

        foreach( $crm_prop_sql->result() as $crm_prop_row ){

            // loop through PME agency array
            foreach( $agency_arr as $agency_arr_obj ){

                if( $agency_arr_obj->agency_id == $crm_prop_row->agency_id ){ // agency match

                    echo "agency: {$crm_prop_row->agency_name}";
                    echo "<br />";

                    // loop through PMe property array
                    foreach( $agency_arr_obj->pme_prop_arr as $pme_prop_obj ){

                        if( $pme_prop_obj->pme_prop_id == $crm_prop_row->api_prop_id ){ // property match

                            echo "property ID: {$crm_prop_row->property_id}";
                            echo "<br />";
                            echo "property address: {$crm_prop_row->p_address_1} {$crm_prop_row->p_address_2}, {$crm_prop_row->p_address_3}";
                            echo "<br />";

                            // pme tenants count
                            $pme_tenant_count = count($pme_prop_obj->pme_tenant_arr);
                            echo "pme_tenant_count: {$pme_tenant_count}";
                             echo "<br />";
                            $tenant_match_count = 0;

                            // get tenants data
                            $sel_query = "
                                pt.`property_tenant_id`,
                                pt.`tenant_firstname`,
                                pt.`tenant_lastname`,
                                pt.`tenant_mobile`,
                                pt.`tenant_landline`,
                                pt.`tenant_email`
                            ";
                            $params = array(
                                'sel_query' => $sel_query,
                                'property_id' => $crm_prop_row->property_id,
                                'pt_active' => 1,
                                'display_query' => 0
                            );
                            $pt_sql = $this->properties_model->get_property_tenants($params);

                            if( $pme_tenant_count == $pt_sql->num_rows() ){

                                // loop throught CRM tenants
                                foreach( $pt_sql->result() as $pt_row ){

                                    // loop through PMe tenants
                                    foreach( $pme_prop_obj->pme_tenant_arr as $pme_tenant_obj ){

                                        if( 
                                            $pt_row->tenant_firstname == $pme_tenant_obj->pme_tenant_fname &&
                                            $pt_row->tenant_lastname == $pme_tenant_obj->pme_tenant_lname &&
                                            $pt_row->tenant_mobile == $pme_tenant_obj->pme_tenant_mobile &&
                                            $pt_row->tenant_landline == $pme_tenant_obj->pme_tenant_landline &&
                                            $pt_row->tenant_email == $pme_tenant_obj->pme_tenant_email
                                        ){

                                            $tenant_match_count++;

                                        }

                                    }                                    

                                }

                            }


                            echo "tenant_match_count: {$tenant_match_count}";
                            echo "<br />";

                            echo "crm_tenants_count: ".$pt_sql->num_rows();
                            echo "<br />";
                            
                            // if PMe/CRm Tenant match count same as PMe tenant and CRM tenant count
                            if( $tenant_match_count == $pt_sql->num_rows() && $tenant_match_count == $pme_tenant_count ){

                               echo $update_sql_str = "
                                UPDATE `property_tenants`
                                SET `modifiedDate` = '{$today_full}'
                                WHERE `property_id` = {$crm_prop_row->property_id}
                                ";
                                $this->db->query($update_sql_str);                               

                            }
                         
                        }

                    }

                }

                echo "<br /><br />";

            }

        }        

    }


}



?>
