<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class To_be_booked extends CI_Controller {

	public function __construct(){
		parent::__construct(); 
        $this->load->database();
        $this->load->model('jobs_model');
        $this->load->helper('url');
    }   

	public function ajax_to_be_booked_assign_dk(){	
        $data['status'] = false;
        $job_id = $this->input->post('job_id');
        $tech_id = $this->input->post('tech_id');
        $date = $this->input->post('date');
        $date2 = date("Y-m-d",strtotime(str_replace("/","-",$date)));
        $staff_id = $this->session->staff_id;

        foreach($job_id as $val){

            // update job
            $job_data = array(
                'status' => 'To Be Booked',
                'assigned_tech' => $tech_id,
                'date' => $date2,
                'tech_notes' => 'Door Knock',
                'booked_with' => 'Agent',
                'booked_by' => $staff_id,
                'door_knock' => 1


            );
           $update_jobs =  $this->jobs_model->update_job($val,$job_data);

           if($update_jobs){ //TRUE

                //get tech name
                $tech_params = array(
                    'sel_query' => 'sa.FirstName, sa.LastName',
                    'staffID' => $tech_id
                );
                $tech = $this->system_model->getTech($tech_params)->row_array();
                $tech_name = $this->system_model->formatStaffName($tech['FirstName'],$tech['LastName']);

                //insert job log
                $log_details = "Door Knock Booked for {$date}. Technician {$tech_name}";
                $log_params = array(
                    'title' => 32,  //Door Knock Booked
                    'details' => $log_details,
                    'display_in_vjd' => 1,
                    'created_by_staff' => $staff_id,
                    'job_id' => $val
                );
                $this->system_model->insert_log($log_params);

                $data['status'] = true;

                /*
                // insert DK job as new row on tech run - this is Ben's instruction, tech's run sheet page "new job check" function can already do this
                if( $tech_id > 0 && $date2 != '' ){

                    // get tech run via tech and date filter
                    $this->db->select('tech_run_id');
                    $this->db->from('tech_run');                
                    $this->db->where('assigned_tech', $tech_id); 
                    $this->db->where('date', $date2); 
                    $tr_sql = $this->db->get();                    

                    if( $tr_sql->num_rows() > 0 ){

                        $tr_row = $tr_sql->row();

                        if( $tr_row->tech_run_id > 0 && $val > 0 ){

                            // insert to tech run 
                            $tech_run_data = array(
                                'tech_run_id' => $tr_row->tech_run_id,
                                'row_id_type' => 'job_id',
                                'row_id' => $val,
                                'sort_order_num' => 999999,
                                'dnd_sorted' => 0,
                                'created_date' => date('Y-m-d H:i:s'),
                                'status' => 1
                            );
                            
                            $this->db->insert('tech_run_rows', $tech_run_data);

                        }                                            

                    }
                    
                } 
                */               


           }


        }

        echo json_encode($data);
		
    }
    
    public function ajax_rebook_script(){
        $data['status'] = false;
        $job_id_arr = $this->input->post('job_id');
        $is_240v = $this->input->post('is_240v');
        $isDHA = $this->input->post('isDHA');
        $staff_id = $this->session->staff_id;


        if(!empty($job_id_arr)){ //checked checbox / not empty checkbox


            foreach($job_id_arr as $job_id){

                // get job type
                $job_sql = $this->db->select('key_access_required,status,job_reason_id,assigned_tech,date,key_access_details,job_type,comments')->from('jobs')->where('id',$job_id)->get();
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
                
                if( $isDHA==1 ){ // DHA
                    $status_txt = 'DHA';
                    $jl_ct_txt = '(DHA)';
                }else{
                    $status_txt = 'To Be Booked';
                }

                if($jl_ct_txt=="(DHA)"){
                    $log_title = 33; //Rebook dha
                }elseif($jl_ct_txt=="(240v)"){
                    $log_title = 34; //Rebook 240
                }else{
                    $log_title = 35; //Rebook
                }
                
                if( $is_240v==1 ){ // 240v rebook
                    $job_type_txt = " `is_eo` = 1, ";       
                    $jl_ct_txt = '(240v)';
                    $log_title = 34; //Rebook 240 log title

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


                // insert job log
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

                    // job status update                   
                    // insert job log
                    $jl_msg = "Job status updated from <strong>{$j['status']}</strong> to <strong>{$status_txt}</strong>";
                    $log_params = array(
                        'title' => $log_title,  //rebook title
                        'details' => $jl_msg,
                        'display_in_vjd' => 1,
                        'created_by_staff' => $this->session->staff_id,
                        'job_id' => $job_id
                    );
                    $this->system_model->insert_log($log_params);
                    
                    
                }                

                $log_params = array(
                    'title' => $log_title,  //Door Knock Booked
                    'details' => $jl_msg,
                    'display_in_vjd' => 1,
                    'created_by_staff' => $staff_id,
                    'job_id' => $job_id
                );
                $this->system_model->insert_log($log_params);


            }

            $data['status'] = true;

        }else{ // Empty checkbox
           $data['error_msg'] = "Job tick/check box must not be empty";
           $data['status'] = false;
        }


        echo json_encode($data);


    }

	

}

