<?php

class Credit extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->database();
        $this->load->model('credits_model');
        $this->load->model('jobs_model');
        $this->load->model('staff_accounts_model');
        $this->load->library('pagination');
    }
	
    public function credit_request(){

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Credit Request";

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;

        $this->form_validation->set_rules('invoice_paid', 'Has this invoice been paid', 'required');
        $this->form_validation->set_rules('refund_request', 'Refund request', 'required');
        $this->form_validation->set_rules('job_id', 'Job Number', 'required');
        $this->form_validation->set_rules('staff', 'Staff', 'required');
        $this->form_validation->set_rules('adjustment_reason', 'Reason for adjustment', 'required');	
        $this->form_validation->set_rules('adjustment_val_req', 'Adjustment value request', 'required');	

		if ( $this->form_validation->run() == true ){

            $job_id = $this->input->get_post('job_id');
            $staff = $this->input->get_post('staff');
            $adjustment_reason = $this->input->get_post('adjustment_reason');
            $adjustment_val_req = $this->input->get_post('adjustment_val_req');
            $invoice_paid = $this->input->get_post('invoice_paid');
            $refund_request = $this->input->get_post('refund_request');
            $refund_bank_details = $this->input->get_post('refund_bank_details');           
            $adjustment_type = $this->input->get_post('adjustment_type');     
            $reason_for_adjustment = $this->input->get_post('reason_for_adjustment');      

			$insert_data = array(
                'job_id' => $job_id,
                'date_of_request' => date('Y-m-d H:i:s'),
                'requested_by' => $staff,
                'reason' => $adjustment_reason,
                'reason_for_adjustment' => $reason_for_adjustment,                
                'adjustment_val_req' => $adjustment_val_req,
                'invoice_paid' => $invoice_paid,
                'country_id' => $country_id,  
                'deleted' => 0,  
                'active' => 1,
                'refund_request' => $refund_request,
                'refund_bank_details' => $refund_bank_details,
                'adjustment_type' => $adjustment_type
            );

           /* if( $invoice_paid == 1 ){ // yes
                $insert_data['refund_request'] = $refund_request;
                if( $refund_request == 1 ){ // yes
                    $insert_data['refund_bank_details'] = $refund_bank_details;
                }                
            }*/
            
            // insert credit request
            $this->db->insert('credit_requests', $insert_data);
            $insert_id = $this->db->insert_id(); //get last insert id > to be used for email item link
            $this->session->set_flashdata('new_credit_request', 1);            
            
            //insert log
            $log_details = "Credit request for <strong>{$adjustment_val_req}</strong>";
            $log_params = array(
                'title' => 36,  // Credit Request
                'details' => $log_details,
                'display_in_vjd' => 1,
                'created_by_staff' => $staff_id,
                'job_id' => $job_id
            );
            $this->system_model->insert_log($log_params);

            //EMAIL NOTIFICATIONS TO ACCOUNTS
            $email_data['item_url'] = BASEURL."credit/request_details/{$insert_id}?type=credit";
            $email_data['e_form'] = "Credit";
            $user_query = $this->gherxlib->getStaffInfo($params=array('sel_query'=>'sa.FirstName,sa.LastName','staff_id'=>$this->session->staff_id))->row_array();
            $email_data['user'] = $user_query['FirstName']." ".$user_query['LastName'];
            $e_no_reply = "noreply@".$this->config->item('sats_domain');
            $email_subject_reason_for_adjustment = substr($adjustment_reason,0,15);
            $e_subject = "New Credit Request: {$email_subject_reason_for_adjustment}";
            $config = Array(
                'mailtype' => 'html',
                'charset' => 'utf-8'
            );
            $this->email->initialize($config);
            $this->email->set_newline("\r\n");
            $this->email->from($e_no_reply, "Automated - No reply");
            $this->email->to($this->config->item('sats_accounts_email'));
            $this->email->subject($e_subject);
            $e_body = $this->load->view('emails/credit_or_refund_request', $email_data, TRUE);
            $this->email->message($e_body);
            $this->email->send();
            //EMAIL NOTIFICATIONS TO ACCOUNTS END
            
		}

        // get staff accounts
        $sel_query = '
            sa.`StaffID`,
            sa.`FirstName`,
            sa.`LastName`
        ';
        $params = array( 
            'sel_query' => $sel_query,
            'email' => $username,
            'active' => 1,
            'deleted' => 0,
            'sort_list' => array(
                array(
                    'order_by' => 'sa.`FirstName`',
                    'sort' => 'ASC',
				),
				array(
                    'order_by' => 'sa.`LastName`',
                    'sort' => 'ASC',
                ),
			),
            'display_query' => 0
        );
        
        // get user details
        $data['staff_sql'] = $this->staff_accounts_model->get_staff_accounts($params);

        // get credit request adjustment reason
        $data['cred_req_adj_res_sql'] = $this->db->query("
        SELECT *
        FROM `credit_request_adj_res`
        WHERE `active` = 1
        ORDER BY `reason`
        ");
        
        $this->load->view('templates/inner_header', $data);
        $this->load->view('credit/adjustment_request', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function refund_request(){

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Refund Request";

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;

        $this->form_validation->set_rules('job_id', 'Job Number', 'required');
        $this->form_validation->set_rules('staff', 'Staff', 'required');
        $this->form_validation->set_rules('adjustment_reason', 'Reason for adjustment', 'required');	
        $this->form_validation->set_rules('adjustment_val_req', 'Adjustment value request', 'required');	

		if ( $this->form_validation->run() == true ){

            $job_id = $this->input->get_post('job_id');
            $staff = $this->input->get_post('staff');
            $adjustment_reason = $this->input->get_post('adjustment_reason');
            $adjustment_val_req = $this->input->get_post('adjustment_val_req');
            $invoice_paid = $this->input->get_post('invoice_paid');
            //$refund_request = $this->input->get_post('refund_request');
            $refund_bank_details = $this->input->get_post('refund_bank_details');           
            $adjustment_type = $this->input->get_post('adjustment_type');  

            
			$insert_data = array(
                'job_id' => $job_id,
                'date_of_request' => date('Y-m-d H:i:s'),
                'requested_by' => $staff,
                'reason' => $adjustment_reason,
                'adjustment_val_req' => $adjustment_val_req,
                'invoice_paid' => $invoice_paid,
                'country_id' => $country_id,  
                'deleted' => 0,  
                'active' => 1,
                'refund_request' => 1,
                'refund_bank_details' => $refund_bank_details,
                'adjustment_type' => $adjustment_type
            );

           /* if( $invoice_paid == 1 ){ // yes
                $insert_data['refund_request'] = $refund_request;
                if( $refund_request == 1 ){ // yes
                    $insert_data['refund_bank_details'] = $refund_bank_details;
                }                
            }*/
            
            // insert refund request
            $this->db->insert('credit_requests', $insert_data);
            $insert_id = $this->db->insert_id(); //get last insert id > to be used for email item link
            $this->session->set_flashdata('new_credit_request', 1);            
            
            //insert log
            $log_details = "Refund request for <strong>{$adjustment_val_req}</strong>";
            $log_params = array(
                'title' => 67,  // Refund Request
                'details' => $log_details,
                'display_in_vjd' => 1,
                'created_by_staff' => $staff_id,
                'job_id' => $job_id
            );
            $this->system_model->insert_log($log_params);

            //EMAIL NOTIFICATIONS TO ACCOUNTS
            $email_data['item_url'] = BASEURL."credit/request_details/{$insert_id}?type=refund";
            $email_data['e_form'] = "Refund";
            $user_query = $this->gherxlib->getStaffInfo($params=array('sel_query'=>'sa.FirstName,sa.LastName','staff_id'=>$this->session->staff_id))->row_array();
            $email_data['user'] = $user_query['FirstName']." ".$user_query['LastName'];
            $e_no_reply = "noreply@".$this->config->item('sats_domain');
            $email_subject_reason_for_adjustment = substr($adjustment_reason,0,15);
            $e_subject = "New Refund Request: {$email_subject_reason_for_adjustment}";
            $config = Array(
                'mailtype' => 'html',
                'charset' => 'utf-8'
            );
            $this->email->initialize($config);
            $this->email->set_newline("\r\n");
            $this->email->from($e_no_reply, "Automated - No reply");
            $this->email->to($this->config->item('sats_accounts_email'));
            $this->email->subject($e_subject);
            $e_body = $this->load->view('emails/credit_or_refund_request', $email_data, TRUE);
            $this->email->message($e_body);
            $this->email->send();
            //EMAIL NOTIFICATIONS TO ACCOUNTS END

		}

        // get staff accounts
        $sel_query = '
            sa.`StaffID`,
            sa.`FirstName`,
            sa.`LastName`
        ';
        $params = array( 
            'sel_query' => $sel_query,
            'email' => $username,
            'active' => 1,
            'deleted' => 0,
            'sort_list' => array(
                array(
                    'order_by' => 'sa.`FirstName`',
                    'sort' => 'ASC',
				),
				array(
                    'order_by' => 'sa.`LastName`',
                    'sort' => 'ASC',
                ),
			),
            'display_query' => 0
        );
        
        // get user details
        $data['staff_sql'] = $this->staff_accounts_model->get_staff_accounts($params);
        
        $this->load->view('templates/inner_header', $data);
        $this->load->view('credit/refund_adjustment_request', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function request_details($credit_request_id){

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Adjustment Request Details";
        $this->load->library('upload');

        $data['type'] = $this->input->get_post('type');
        $submit = $this->input->get_post('btn_update');       

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;

        $this->form_validation->set_rules('adjustment_reason', 'Reason for adjustment', 'required');	
        $this->form_validation->set_rules('adjustment_val_req', 'Adjustment value request', 'required');
        $this->form_validation->set_rules('result', 'Result', 'required');	
        
       

            if ( $this->form_validation->run() == true ){
            
                // main data
                $job_id = $this->input->get_post('job_id'); 
                $reason_for_adjustment = $this->input->get_post('reason_for_adjustment');
                $adjustment_reason = $this->input->get_post('adjustment_reason');
                $adjustment_val_req = $this->input->get_post('adjustment_val_req');
                $invoice_paid = $this->input->get_post('invoice_paid');
                $refund_request = $this->input->get_post('refund_request');
                $refund_bank_details = $this->input->get_post('refund_bank_details'); 

                // hidden
                $requested_by_name = $this->input->get_post('requested_by_name'); 
                $prop_address = $this->input->get_post('prop_address');
                $rb_email = $this->input->get_post('rb_email'); 

                // accounts only data
                $result = $this->input->get_post('result'); 
                $amount_credited = $this->input->get_post('amount_credited');
                $date_processed = ( $this->input->get_post('date_processed') != '' )?$this->system_model->formatDate($this->input->get_post('date_processed')):null;
                $comments = $this->input->get_post('comments');


                //-----------------upload pdf start

                $upload_path = "./uploads/request_summary_files/";
                $upload_folder = "/uploads/request_summary_files/"; //note without dot

                //make directory if not exist and set permission to 777
                if(!is_dir($upload_folder)){
                    mkdir($upload_path,0777,true);
                }

                //Proof of Payment Upload
                if(!empty($_FILES['payment_pdf_upload_input']['name'])){

                    //file name
                    $filename = preg_replace('/#+/', 'num', $_FILES['payment_pdf_upload_input']['name']);
                    $filename2 = preg_replace('/\s+/', '_', $filename);
                    $proof_of_payment_pdf_name = rand().date('YmdHis')."_proofOfPayment_".$filename2;

                    $_FILES['file']['name'] = $_FILES['payment_pdf_upload_input']['name'];
                    $_FILES['file']['type'] = $_FILES['payment_pdf_upload_input']['type'];
                    $_FILES['file']['tmp_name'] = $_FILES['payment_pdf_upload_input']['tmp_name'];
                    $_FILES['file']['error'] = $_FILES['payment_pdf_upload_input']['error'];
                    $_FILES['file']['size'] = $_FILES['payment_pdf_upload_input']['size'];

                    //set upload config
                    $upload_params = array(
                        'file_name' => $proof_of_payment_pdf_name,
                        'upload_path' => $upload_path,
                        'max_size' => '2048', //2mb
                        'allowed_types' => 'pdf'
                    );
                    $upload1 = $this->gherxlib->do_upload('file',$upload_params);   
                    
                    //upload data
                    $uploadData1 = $this->upload->data();

                    //success 
                    if($upload1){
                        //delete current file
                        $summ_file = $this->credits_model->check_summary_pdf_file_exist($credit_request_id,'proof_of_payment_pdf'); //check if file exist > return col/file value
                        if( $summ_file != "" ){ //delete current file
                            unlink("{$upload_path}{$summ_file}");
                        }
                    }
                }//Proof of Payment Upload end
                

                //Proof of Proof of Allocation upload
                if(!empty($_FILES['allocation_pdf_upload_input']['name'])){
                        //file name
                    $filename = preg_replace('/#+/', 'num', $_FILES['allocation_pdf_upload_input']['name']);
                    $filename2 = preg_replace('/\s+/', '_', $filename);
                    $proof_of_allocation_pdf_name = rand().date('YmdHis')."_proofOfAllocation_".$filename2;

                    $_FILES['file']['name'] = $_FILES['allocation_pdf_upload_input']['name'];
                    $_FILES['file']['type'] = $_FILES['allocation_pdf_upload_input']['type'];
                    $_FILES['file']['tmp_name'] = $_FILES['allocation_pdf_upload_input']['tmp_name'];
                    $_FILES['file']['error'] = $_FILES['allocation_pdf_upload_input']['error'];
                    $_FILES['file']['size'] = $_FILES['allocation_pdf_upload_input']['size'];

                    //set upload config
                    $allocation_upload_params = array(
                        'file_name' => $proof_of_allocation_pdf_name,
                        'upload_path' => $upload_path,
                        'max_size' => '1024', //1mb
                        'allowed_types' => 'pdf'
                    );
                    $upload2 = $this->gherxlib->do_upload('file',$allocation_upload_params);
                    
                    //upload data
                    $uploadData2 = $this->upload->data();

                    //success
                    if($upload2){
                        //delete current file
                        $summ_file = $this->credits_model->check_summary_pdf_file_exist($credit_request_id,'proof_of_allocation_pdf'); //check if file exist > return col/file value
                        if( $summ_file != false ){ //delete current file
                            unlink("{$upload_path}{$summ_file}");
                        }
                    }
                    
                }//Proof of Proof of Allocation upload end

                //Email trail upload
                if(!empty($_FILES['trail_pdf_upload_input']['name'])){
                    //file name
                    $filename = preg_replace('/#+/', 'num', $_FILES['trail_pdf_upload_input']['name']);
                    $filename2 = preg_replace('/\s+/', '_', $filename);
                    $email_trail_pdf_name = rand().date('YmdHis')."_emailTrail_".$filename2;

                    $_FILES['file']['name'] = $_FILES['trail_pdf_upload_input']['name'];
                    $_FILES['file']['type'] = $_FILES['trail_pdf_upload_input']['type'];
                    $_FILES['file']['tmp_name'] = $_FILES['trail_pdf_upload_input']['tmp_name'];
                    $_FILES['file']['error'] = $_FILES['trail_pdf_upload_input']['error'];
                    $_FILES['file']['size'] = $_FILES['trail_pdf_upload_input']['size'];

                    //set upload config
                    $emailtrail_upload_params = array(
                        'file_name' => $email_trail_pdf_name,
                        'upload_path' => $upload_path,
                        'max_size' => '1024', //1mb
                        'allowed_types' => 'pdf'
                    );
                    $upload3 = $this->gherxlib->do_upload('file',$emailtrail_upload_params);
                    
                    //upload data
                    $uploadData3 = $this->upload->data();

                    //success
                    if($upload3){
                        //delete current file
                        $summ_file = $this->credits_model->check_summary_pdf_file_exist($credit_request_id,'email_trail_pdf'); //check if file exist > return col/file value
                        if( $summ_file != false ){ //delete current file
                            unlink("{$upload_path}{$summ_file}");
                        }
                    }
                }//Email trail upload end
                

                //Other upload
                if(!empty($_FILES['other_pdf_upload_input']['name'])){
                    //file name
                    $filename = preg_replace('/#+/', 'num', $_FILES['other_pdf_upload_input']['name']);
                    $filename2 = preg_replace('/\s+/', '_', $filename);
                    $other_pdf_name = rand().date('YmdHis')."_other_".$filename2;

                    $_FILES['file']['name'] = $_FILES['other_pdf_upload_input']['name'];
                    $_FILES['file']['type'] = $_FILES['other_pdf_upload_input']['type'];
                    $_FILES['file']['tmp_name'] = $_FILES['other_pdf_upload_input']['tmp_name'];
                    $_FILES['file']['error'] = $_FILES['other_pdf_upload_input']['error'];
                    $_FILES['file']['size'] = $_FILES['other_pdf_upload_input']['size'];

                    //set upload config
                    $other_upload_params = array(
                        'file_name' => $other_pdf_name,
                        'upload_path' => $upload_path,
                        'max_size' => '1024', //1mb
                        'allowed_types' => 'pdf'
                    );
                    $upload4 = $this->gherxlib->do_upload('file',$other_upload_params);

                    //upload data
                    $uploadData4 = $this->upload->data();

                    //success
                    if($upload4){
                        //delete current file
                        $summ_file = $this->credits_model->check_summary_pdf_file_exist($credit_request_id,'other_pdf'); //check if file exist > return col/file value
                        if( $summ_file != false ){ //delete current file
                            unlink("{$upload_path}{$summ_file}");
                        }
                    }
                }
                //Other upload end

                //-------------------upload pdf end


                // if more info needed
                /** Gherx > Disable - Jan 7 20201

                $cr_params = array(
                    'sel_query' => 'result',
                    'cr_id' => $credit_request_id,
                    'deleted' => 0,
                    'active' => 1,              
                    'country_id' => $country_id,			
                    'display_query' => 0
                );
                $cr_sql = $this->credits_model->get_credit_request($cr_params);
                $cr_row = $cr_sql->row();

                // if result is more info needed, update result to pending
                if( $cr_row->result == 2){ 
                    $result = null;
                }else if($result == 'pending'){ //post submission
                    $result = null;
                }else{ //post submission
                    $result = $result;
                }

                */

                if($result == 'pending'){
                    $result2 = null;
                }else{
                    $result2 = $result;
                }

                // update credit request
                $update_data = array(
                    'reason_for_adjustment' => $reason_for_adjustment,
                    'reason' => $adjustment_reason,
                    'adjustment_val_req' => $adjustment_val_req,

                    'invoice_paid' => $invoice_paid,
                    'refund_request' => $refund_request,
                    'refund_bank_details' => $refund_bank_details,
        
                    'result' => $result2,
                    'amount_credited' => $amount_credited,
                    'date_processed' => $date_processed,
                    'comments' => $comments,
                    'who' => $staff_id
                );

                //include file name to update query params when upload success
                if($upload1){
                    //set new data field to include to update query
                    $update_data['proof_of_payment_pdf'] = $uploadData1['file_name'];
                }
                if($upload2){
                    //set new data field to include to update query
                    $update_data['proof_of_allocation_pdf'] =  $uploadData2['file_name'];
                }
                if($upload3){
                    //set new data field to include to update query
                    $update_data['email_trail_pdf'] =  $uploadData3['file_name'];
                }
                if($upload4){
                    //set new data field to include to update query
                    $update_data['other_pdf'] =  $uploadData4['file_name'];
                }
                //include file name to update query params when upload success end
                
                $this->db->where('credit_request_id', $credit_request_id);
                $this->db->update('credit_requests', $update_data); 

                
                if( is_numeric($result) ){

                    $this->load->library('email');
                    $this->email->set_mailtype("html");

                    $this->email->from($this->config->item('sats_accounts_email'), 'SATS');
                    $this->email->to($rb_email);
                    //$this->email->cc($this->config->item('sats_info_email'));

                    // Subject
                    $subject = 'Adjustment Request #'.$job_id;
                    

                    // result
                    if( $result == 1 ){
                        $result_txt = "<b style='color:green'>Accepted</b>";
                    }else if( is_numeric($result) && $result == 0 ){
                        $result_txt = "<b style='color:red'>Declined</b>";
                    }else if( is_numeric($result) && $result == 2 ){
                        $result_txt = "<b style='color:#f37b53'>More info needed</b>";
                    }

                    $adjustment_val_req_txt = '$'.number_format($adjustment_val_req,2);

                    if($this->input->get_post('type') == 'refund'){
                        $request_type = 'Refund';
                    }elseif($this->input->get_post('type') == 'credit'){
                        $request_type = 'Credit';
                    }

                    if($amount_credited!="" && $amount_credited>0){
                        $amount_credited_formated = '$'.number_format($amount_credited,2);
                        $amount_credited_txt = '<p>Amount credited is: '.$amount_credited_formated.' .</p>';
                    }

                    // Message
                    $message = "
                    <html>
                    <head>
                    <title>{$subject}</title>
                    </head>
                    <body>
                    
                        <p>Dear {$requested_by_name},</p>
                        <p>The {$request_type} request for {$adjustment_val_req_txt} for {$prop_address} has been marked {$result_txt}, with the following comment: </p>
                        <p>{$comments}</p>
                        {$amount_credited_txt}
                        <p>Please contact accounts for any further information</p>

                    </body>
                    </html>
                    ";

                    $this->email->subject($subject);
                    //$email_content = $this->load->view('emails/reset-password', $email_data, true);
                    $this->email->message($message);
                    $this->email->send();

                }  

                $this->session->set_flashdata('credit_request_updated', 1);

                if($data['type'] == "credit"){
                    redirect('/credit/credit_request_summary');
                }else if($data['type'] == "refund"){
                    redirect('/credit/refund_request_summary');
                }
                
            }

        
            //DEFAULT LIST HERE>..
            // paginatied results
            $sel_query = "
            cr.`credit_request_id`,
            cr.`job_id`,
            cr.`date_of_request`,
            cr.`requested_by`,
            cr.`reason` AS cr_reason,            
            cr.`result`,
            cr.`comments` AS cr_comments,
            cr.`date_processed`,
            cr.`amount_credited`,
            cr.`who`,
            cr.`adjustment_val_req`,
            cr.`invoice_paid`,
            cr.`refund_request`,
            cr.`refund_bank_details`,
            cr.`proof_of_payment_pdf`,
            cr.`proof_of_allocation_pdf`,
            cr.`email_trail_pdf`,
            cr.`other_pdf`,
            cr.`reason_for_adjustment`,

            j.`invoice_amount`,

            p.`property_id` AS prop_id, 
            p.`address_1` AS p_address_1, 
            p.`address_2` AS p_address_2, 
            p.`address_3` AS p_address_3,
            p.`state` AS p_state,
            p.`postcode` AS p_postcode,
            
            a.`agency_id`,
            a.`agency_name`,

            rb.`StaffID` AS rb_staff_id,
            rb.`FirstName` AS rb_fname, 
            rb.`LastName` AS rb_lname,
            rb.`Email` AS rb_email,
            
            who.`StaffID` AS who_staff_id,
            who.`FirstName` AS who_fname, 
            who.`LastName` AS who_lname			
            ";
            $params = array(
                'sel_query' => $sel_query,

                'deleted' => 0,
                'active' => 1,
                'cr_id' => $credit_request_id,

                'limit' => $per_page,
                'offset' => $offset,

                'join_table' => array('jobs','property','agency','req_by','who'),
                                            
                'country_id' => $country_id,			
                'display_query' => 0
            );
            $cr_sql = $this->credits_model->get_credit_request($params);
            $cr_row = $cr_sql->row();
            $data['cr_row'] = $cr_row;

            // append checkdigit to job id for new invoice number
            $check_digit = $this->system_model->getCheckDigit(trim($cr_row->job_id));
            $bpay_ref_code = "{$cr_row->job_id}{$check_digit}";
            $data['invoice_num'] = $bpay_ref_code;

            // get staff accounts
            $sel_query = '
                sa.`StaffID`,
                sa.`FirstName`,
                sa.`LastName`
            ';
            $params = array( 
                'sel_query' => $sel_query,
                'email' => $username,
                'active' => 1,
                'deleted' => 0,
                'sort_list' => array(
                    array(
                        'order_by' => 'sa.`FirstName`',
                        'sort' => 'ASC',
                    ),
                    array(
                        'order_by' => 'sa.`LastName`',
                        'sort' => 'ASC',
                    ),
                ),
                'display_query' => 0
            );
            
            // get user details
            $data['staff_sql'] = $this->staff_accounts_model->get_staff_accounts($params);

            $data['cr_id'] = $credit_request_id;

            // get credit request adjustment reason
            $data['cred_req_adj_res_sql'] = $this->db->query("
            SELECT *
            FROM `credit_request_adj_res`
            WHERE `active` = 1
            ORDER BY `reason`
            ");
            
            $this->load->view('templates/inner_header', $data);
            $this->load->view('credit/adjustment_request_details', $data);
            $this->load->view('templates/inner_footer', $data);
        
        
        
    }


    public function credit_request_summary(){

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Credit Request Summary";

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;

        $from_filter = ( $this->input->get_post('from_filter') !='' )?$this->system_model->formatDate($this->input->get_post('from_filter')):null;
        $to_filter = ( $this->input->get_post('to_filter') !='' )?$this->system_model->formatDate($this->input->get_post('to_filter')):null;
        $requested_by = $this->input->get_post('req_by_filter');
        $result = ( $this->input->get_post('result_filter') != '' )?$this->input->get_post('result_filter'):'pending';
        $agency = $this->input->get_post('agency_filter');
        $search_filter = $this->input->get_post('search_filter');

        // pagination
        $per_page = $this->config->item('pagi_per_page');        
        $offset = $this->input->get_post('offset');

        // paginatied results
        $sel_query = "
            cr.`credit_request_id`,
            cr.`job_id`,
            cr.`date_of_request`,
            cr.`requested_by`,
            cr.`reason` AS cr_reason, 
            cr.`result`,
            cr.`comments` AS cr_comments,
            cr.`date_processed`,
            cr.`amount_credited`,
            cr.`who`,
            cr.adjustment_val_req,

            j.`invoice_amount`,
            
            a.`agency_id`,
            a.`agency_name`,
            aght.priority,
            apmd.abbreviation,

            rb.`StaffID` AS rb_staff_id,
			rb.`FirstName` AS rb_fname, 
			rb.`LastName` AS rb_lname,
            
            who.`StaffID` AS who_staff_id,
			who.`FirstName` AS who_fname, 
			who.`LastName` AS who_lname			
        ";
        $custom_where_main_list = "(cr.`adjustment_type`!=1 OR cr.`adjustment_type` IS NULL)"; //not refund (credit)
        $params = array(
            'sel_query' => $sel_query,

			'deleted' => 0,
            'active' => 1,

            'custom_where' => $custom_where_main_list,

            'limit' => $per_page,
            'offset' => $offset,

            'requested_by' => $requested_by,
            'result' => $result,
            'agency' => $agency,   
            'job_id_like' => $search_filter,         

            'dor_search_span' => array(
                'from' => $from_filter,
                'to' => $to_filter
            ),

            'sort_list' => array(
                array(
                    'order_by' => 'cr.`date_of_request`',
                    'sort' => 'DESC'
                )
            ),

            'join_table' => array('jobs','property','agency','req_by','who', 'agency_priority', 'agency_priority_marker_definition'),
                                           
            'country_id' => $country_id,			
			'display_query' => 0
        );

        $list_query = $this->credits_model->get_credit_request($params);

        if($this->input->get_post('export')==1){ ## EXPORT FUNCTION

            unset($params['limit']); ## remove limit
            unset($params['offset']); ## remove offset

            $lists_export_query = $list_query;

            $filename = "Credit_request_summary".date("d/m/Y").".csv";
            header("Content-Type: text/csv");
            header("Content-Disposition: Attachment; filename=$filename");
            header("Pragma: no-cache");
            echo "Date of Request,Invoice #,Amount,Credit Amount,Agency,Requested By,Reason,Result,Comments,Date Processed,Credited,Who\n";
            
            foreach ($lists_export_query->result() as $row) {

                $date_of_request = ( $this->system_model->isDateNotEmpty($row->date_of_request) )?date('d/m/Y',strtotime($row->date_of_request)):null;

                $check_digit = $this->system_model->getCheckDigit(trim($row->job_id));
                $bpay_ref_code = "{$row->job_id}{$check_digit}";

                $amount = number_format($row->invoice_amount,2);
                $credit_amount = number_format($row->adjustment_val_req,2);
                $agency_name = $row->agency_name;
                $requested_by = $this->system_model->formatStaffName($row->rb_fname,$row->rb_lname);
                $reason = $row->cr_reason;

                if( $row->result == 1 ){
                    $result = 'Accepted';
                }else if( is_numeric($row->result) && $row->result == 0 ){
                    $result = 'Declined';
                }else if( is_numeric($row->result) && $row->result == 2 ){
                    $result = 'More info needed';
                }else{
                    $result = 'Pending';
                }

                $date_processed = ( $this->system_model->isDateNotEmpty($row->date_processed) )?date('d/m/Y',strtotime($row->date_processed)):null;
                $credited = ( $row->amount_credited>0 )?'$'.$row->amount_credited:null;
                $who = $this->system_model->formatStaffName($row->who_fname,$row->who_lname);

                echo "\"{$date_of_request}\",\"{$bpay_ref_code}\",\"{$amount}\",\"{$credit_amount}\",\"{$agency_name}\",\"{$requested_by}\",\"{$reason}\",\"{$result}\",\"{$row->cr_comments}\",\"{$date_processed}\",\"{$credited}\",\"{$who}\"\n";
            
            }

        }else{ // NORMAL LISTING

            $data['lists'] = $list_query;

            // total rows
            $sel_query = "COUNT(cr.`credit_request_id`) as cr_count";
            $params = array(
                'sel_query' => $sel_query,
    
                'deleted' => 0,
                'active' => 1,
    
                'custom_where' => $custom_where_main_list,
    
                'requested_by' => $requested_by,
                'result' => $result,
                'agency' => $agency,
                'job_id_like' => $search_filter,   
    
                'dor_search_span' => array(
                    'from' => $from_filter,
                    'to' => $to_filter
                ),
    
                'join_table' => array('jobs','property','agency','req_by','who'),
                                               
                'country_id' => $country_id,			
                'display_query' => 0
            );
            $query = $this->credits_model->get_credit_request($params);
            $total_rows = $query->row()->cr_count;  
    
    
            // Requested by
            $sel_query = "DISTINCT(rb.`StaffID`), rb.`FirstName`, rb.`LastName`";
            $params = array(
                'sel_query' => $sel_query,
    
                'deleted' => 0,
                'active' => 1,
    
                'custom_where' => $custom_where_main_list,
    
                'join_table' => array('jobs','property','agency','req_by','who'),
                                               
                'country_id' => $country_id,			
                'display_query' => 0
            );
            $data['rb_filter_dp'] = $this->credits_model->get_credit_request($params);
    
            // Agency filter
            $sel_query = "DISTINCT(a.`agency_id`), a.`agency_name`";
            $custom_where = "a.`agency_id` > 1 AND cr.`refund_request`!=1"; // exclude this weird agency *** SELECT AGENCY ***  and credit_refund
            $params = array(
                'sel_query' => $sel_query,
                'custom_where' => $custom_where,
    
                'deleted' => 0,
                'active' => 1,
    
                'join_table' => array('jobs','property','agency','req_by','who'),
                                               
                'country_id' => $country_id,			
                'sort_list' => array(
                    array(
                        'order_by' => 'a.`agency_name`',
                        'sort' => 'ASC'
                    )
                ),
                'display_query' => 0
            );
            $data['agency_filter_dp'] = $this->credits_model->get_credit_request($params);
    
    
            // Total sum
            $sel_query = "SUM(j.`invoice_amount`) AS tot_inv_amount, SUM(cr.`amount_credited`) AS tot_am_cred ";
            $params = array(
                'sel_query' => $sel_query,
    
                'deleted' => 0,
                'active' => 1,
    
                'custom_where' => $custom_where_main_list,
    
                'requested_by' => $requested_by,
                'result' => $result,
                'agency' => $agency, 
                'job_id_like' => $search_filter,           
    
                'dor_search_span' => array(
                    'from' => $from_filter,
                    'to' => $to_filter
                ),
    
                'join_table' => array('jobs','property','agency','req_by','who'),
                                               
                'country_id' => $country_id,			
                'display_query' => 0
            );
            $query = $this->credits_model->get_credit_request($params);
            $data['total_sum'] = $query->row();
    
             // pagination settings
            $pagi_links_params_arr = array(
                'from_filter' => $this->input->get_post('from_filter'),
                'to_filter' => $this->input->get_post('to_filter'),
                'req_by_filter' => $this->input->get_post('req_by_filter'),
                'result_filter' => $this->input->get_post('result_filter'),
                'agency_filter' => $this->input->get_post('agency_filter'),
                'search_filter' => $this->input->get_post('search_filter')
            );
            $pagi_link_params = '/credit/credit_request_summary/?'.http_build_query($pagi_links_params_arr);
           
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
            $this->load->view('credit/adjustment_request_summary', $data);
            $this->load->view('templates/inner_footer', $data);

        }
    }

    public function refund_request_summary(){

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Refund Request Summary";

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;

        $from_filter = ( $this->input->get_post('from_filter') !='' )?$this->system_model->formatDate($this->input->get_post('from_filter')):null;
        $to_filter = ( $this->input->get_post('to_filter') !='' )?$this->system_model->formatDate($this->input->get_post('to_filter')):null;
        $requested_by = $this->input->get_post('req_by_filter');
        $result = ( $this->input->get_post('result_filter') != '' )?$this->input->get_post('result_filter'):'pending';
        $agency = $this->input->get_post('agency_filter');
        $search_filter = $this->input->get_post('search_filter');

        // pagination
        $per_page = $this->config->item('pagi_per_page');        
        $offset = $this->input->get_post('offset');

        // paginatied results
        $sel_query = "
            cr.`credit_request_id`,
            cr.`job_id`,
            cr.`date_of_request`,
            cr.`requested_by`,
            cr.`reason` AS cr_reason, 
            cr.`result`,
            cr.`comments` AS cr_comments,
            cr.`date_processed`,
            cr.`amount_credited`,
            cr.`who`,

            j.`invoice_amount`,
            j.`invoice_balance`,
            
            a.`agency_id`,
            a.`agency_name`,
            aght.priority,
            apmd.abbreviation,

            rb.`StaffID` AS rb_staff_id,
			rb.`FirstName` AS rb_fname, 
			rb.`LastName` AS rb_lname,
            
            who.`StaffID` AS who_staff_id,
			who.`FirstName` AS who_fname, 
			who.`LastName` AS who_lname			
        ";
        
        $custom_where_main_list = "adjustment_type=1";
        $params = array(
            'sel_query' => $sel_query,

			'deleted' => 0,
            'active' => 1,

            'custom_where' => $custom_where_main_list,

            'requested_by' => $requested_by,
            'result' => $result,
            'agency' => $agency,
            'job_id_like' => $search_filter,          

            'dor_search_span' => array(
                'from' => $from_filter,
                'to' => $to_filter
            ),

            'sort_list' => array(
                array(
                    'order_by' => 'cr.`date_of_request`',
                    'sort' => 'DESC'
                )
            ),

            'join_table' => array('jobs','property','agency','req_by','who','agency_priority', 'agency_priority_marker_definition'),
                                           
            'country_id' => $country_id,			
			'display_query' => 0
        );
        if($this->input->get_post('export')==1){ ## EXPORT FUNCTION

            $lists_export_query = $this->credits_model->get_credit_request($params);

            $filename = "Refund_request_summary".date("d/m/Y").".csv";
            header("Content-Type: text/csv");
            header("Content-Disposition: Attachment; filename=$filename");
            header("Pragma: no-cache");
            echo "Date of Request,Invoice #,Amount,Credit Amount,Balance,Agency,Requested By,Reason,Result,Comments,Date Processed,Credited,Who\n";
            
            foreach ($lists_export_query->result() as $row) {

                $date_of_request = ( $this->system_model->isDateNotEmpty($row->date_of_request) )?date('d/m/Y',strtotime($row->date_of_request)):null;

                $check_digit = $this->system_model->getCheckDigit(trim($row->job_id));
                $bpay_ref_code = "{$row->job_id}{$check_digit}";

                $amount = number_format($row->invoice_amount,2);
                $credit_amount = number_format($row->adjustment_val_req,2);
                $invoice_balance = number_format($row->invoice_balance,2);
                $agency_name = $row->agency_name;
                $requested_by = $this->system_model->formatStaffName($row->rb_fname,$row->rb_lname);
                $reason = $row->cr_reason;

                if( $row->result == 1 ){
                    $result = 'Accepted';
                }else if( is_numeric($row->result) && $row->result == 0 ){
                    $result = 'Declined';
                }else if( is_numeric($row->result) && $row->result == 2 ){
                    $result = 'More info needed';
                }else{
                    $result = 'Pending';
                }

                $date_processed = ( $this->system_model->isDateNotEmpty($row->date_processed) )?date('d/m/Y',strtotime($row->date_processed)):null;
                $credited = ( $row->amount_credited>0 )?'$'.$row->amount_credited:null;
                $who = $this->system_model->formatStaffName($row->who_fname,$row->who_lname);

                echo "\"{$date_of_request}\",\"{$bpay_ref_code}\",\"{$amount}\",\"{$credit_amount}\",\"{$invoice_balance}\",\"{$agency_name}\",\"{$requested_by}\",\"{$reason}\",\"{$result}\",\"{$row->cr_comments}\",\"{$date_processed}\",\"{$credited}\",\"{$who}\"\n";
            
            }

        }else{
            $params['limit'] = $per_page;
			$params['offset'] = $offset;

            $data['lists'] = $this->credits_model->get_credit_request($params);

            // total rows
            $sel_query = "COUNT(cr.`credit_request_id`) as cr_count";
            $params = array(
                'sel_query' => $sel_query,

                'deleted' => 0,
                'active' => 1,

                'custom_where' => $custom_where_main_list,

                'requested_by' => $requested_by,
                'result' => $result,
                'agency' => $agency,
                'job_id_like' => $search_filter,   

                'dor_search_span' => array(
                    'from' => $from_filter,
                    'to' => $to_filter
                ),

                'join_table' => array('jobs','property','agency','req_by','who'),
                                            
                'country_id' => $country_id,			
                'display_query' => 0
            );
            $query = $this->credits_model->get_credit_request($params);
            $total_rows = $query->row()->cr_count;  


            // Requested by
            $sel_query = "DISTINCT(rb.`StaffID`), rb.`FirstName`, rb.`LastName`";
            $params = array(
                'sel_query' => $sel_query,

                'deleted' => 0,
                'active' => 1,

                'custom_where' => $custom_where_main_list,

                'join_table' => array('jobs','property','agency','req_by','who'),
                                            
                'country_id' => $country_id,			
                'display_query' => 0
            );
            $data['rb_filter_dp'] = $this->credits_model->get_credit_request($params);

            // Agency filter
            $sel_query = "DISTINCT(a.`agency_id`), a.`agency_name`";
            $custom_where = "a.`agency_id` > 1"; // exclude this weird agency *** SELECT AGENCY ***  and credit_refund
            $params = array(
                'sel_query' => $sel_query,
                'custom_where' => $custom_where,

                'deleted' => 0,
                'active' => 1,

                'custom_where' => $custom_where_main_list,

                'join_table' => array('jobs','property','agency','req_by','who'),
                                            
                'country_id' => $country_id,			
                'sort_list' => array(
                    array(
                        'order_by' => 'a.`agency_name`',
                        'sort' => 'ASC'
                    )
                ),
                'display_query' => 0
            );
            $data['agency_filter_dp'] = $this->credits_model->get_credit_request($params);


            // Total sum
            $sel_query = "SUM(j.`invoice_amount`) AS tot_inv_amount, SUM(cr.`amount_credited`) AS tot_am_cred ";
            $params = array(
                'sel_query' => $sel_query,

                'deleted' => 0,
                'active' => 1,

                'custom_where' => $custom_where_main_list,

                'requested_by' => $requested_by,
                'result' => $result,
                'agency' => $agency, 
                'job_id_like' => $search_filter,           

                'dor_search_span' => array(
                    'from' => $from_filter,
                    'to' => $to_filter
                ),

                'join_table' => array('jobs','property','agency','req_by','who'),
                                            
                'country_id' => $country_id,			
                'display_query' => 0
            );
            $query = $this->credits_model->get_credit_request($params);
            $data['total_sum'] = $query->row();

            // pagination settings
            $pagi_links_params_arr = array(
                'from_filter' => $this->input->get_post('from_filter'),
                'to_filter' => $this->input->get_post('to_filter'),
                'req_by_filter' => $this->input->get_post('req_by_filter'),
                'result_filter' => $this->input->get_post('result_filter'),
                'agency_filter' => $this->input->get_post('agency_filter'),
                'search_filter' => $this->input->get_post('search_filter')
            );
            $pagi_link_params = '/credit/refund_request_summary/?'.http_build_query($pagi_links_params_arr);
        
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
            $this->load->view('credit/refund_request_summary', $data);
            $this->load->view('templates/inner_footer', $data);
        }
    }


    public function get_job_data(){

        $country_id = $this->config->item('country');
        $job_id = $this->input->get_post('job_id');
        $alreadyExist = 0;

        $sel_query = "cr.`credit_request_id`";
        $params = array(
            'sel_query' => $sel_query,

            'job_id' => $job_id,
			'deleted' => 0,
            'active' => 1,
                                           
            'country_id' => $country_id,			
			'display_query' => 0
        );
        $cr_sql = $this->credits_model->get_credit_request($params);
        $cr_count = $cr_sql->num_rows();

        if( $cr_count > 0 ){
            $alreadyExist = 1;
            $cr_row = $cr_sql->row();
        }
        //else{

            $sel_query = "
                j.`id` AS jid,
                j.`tmh_id`,
                j.`invoice_amount`,
                j.`invoice_balance`,

                a.`agency_name`
            ";

		
            $params = array(
                'sel_query' => $sel_query,          
                'job_id' => $job_id,
                'country_id' => $country_id,
                'display_query' => 0
            );

            $job_sql = $this->jobs_model->get_jobs($params);
            $job_row =  $job_sql->row();

            // append checkdigit to job id for new invoice number
            $check_digit = $this->system_model->getCheckDigit(trim($job_id));
            $bpay_ref_code = "{$job_id}{$check_digit}";

            // get invoice number
            if( isset($job_row->tmh_id) && $job_row->tmh_id != '' ){
                $invoice_num = $job_row->tmh_id;
            }else{
                $invoice_num = $bpay_ref_code;
            }

       // }

        $json_arr = array( 
            "alreadyExist" => $alreadyExist,
            "invoice_num" => $invoice_num,
            "amount" => number_format($job_row->invoice_amount,2,'.',''),
            "agency" => $job_row->agency_name,
            "cr_id" => $cr_row->credit_request_id,
            "balance" => number_format($job_row->invoice_balance,2,'.','')
        );
        echo json_encode($json_arr);

    }

    public function delete($cr_id){

        $bc_type = $this->input->get_post('type');
        if( isset($cr_id) && $cr_id != '' && is_numeric($cr_id) && $cr_id > 0 ){ // make sure id exist
            $this->db->delete('credit_requests', array('credit_request_id' => $cr_id));   
            $this->session->set_flashdata('credit_request_deleted', 1); 
            
            if($bc_type =="refund"){ //redirect to refund summary page
                redirect('/credit/refund_request_summary');
            }else{ //redirect to credit summary page
                redirect('/credit/credit_request_summary');
            }
        }  

    }

    public function ajax_credit_request_summary_update_amount_credited(){

        $credit_request_id = $this->input->post('credit_request_id');
        $amount_credited = $this->input->post('amount_credited');

        if( $credit_request_id!="" ){ #Validate

            foreach($credit_request_id as $val){
                if($val > 0){

                    ## Update credit_requests
                    $update_data = array(
                        'amount_credited' => $amount_credited,
                        'date_processed' => date('Y-m-d H:i:s'),
                        'result' => 1
                    );
                    $this->db->where('credit_request_id', $val);
                    $this->db->update('credit_requests', $update_data);

                }
            }

        }

    }


}

?>
