<?php

class Functions_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
        $this->load->library('email');
    }

    public function isDHAagenciesV2($fg_id){
        // Defence Housing
        if( $fg_id == 14 ){
            return true;
        }else{
            return false;
        }
    }


    public function agencyHasMaintenanceProgram($agency_id){
        $this->db->select('*');
        $this->db->from('agency_maintenance');
        $this->db->where('agency_id', $agency_id);
        $this->db->where('maintenance_id >', 0);
        $this->db->where('status', 1);

        $sql = $this->db->get(); 
        
        if($sql->num_rows() > 0){
            return true;
        }else{
            return false;
        } 
        
    }

    function mysqlMultiRows( $query ){
        $result = $this->db->query($query) or die(mysql_error());

        if ( $result->num_rows() > 0 )
        {
                $row_array = array();
                foreach($result->result_array() as $row)
                {
                        array_push( $row_array, $row );
                }
                return $row_array;
        }
        else
        {
            return NULL;
        }
    }

    function mysqlSingleRow( $query )
    {
        $result = $this->db->query($query) or die(mysql_error());
        if ( $result->num_rows() > 0 )
        {
                $row = $result->row_array();
                return $row;
        }
        else
        {
                return 0;
        }

    }

    public function send_letters_no_tenant_email($params){
        $job_id = $params['job_id'];
        $staff_id = $params['staff_id'];
        $country_id = $params['country_id'];
        // get country
        $cntry = $this->getCountryViaCountryId($country_id);

        $this->db->select('*, 
                        p.address_1 AS p_address_1, 
                        p.address_2 AS p_address_2, 
                        p.address_3 AS p_address_3, 
                        p.state AS p_state, 
                        p.postcode AS p_postcode, 
                        a.new_job_email_to_agent');
        $this->db->from('jobs j');
        $this->db->join('property p','j.property_id = p.property_id', 'left');
        $this->db->join('agency a','p.agency_id = a.agency_id', 'left');
        $this->db->where('j.status', 'Send Letters');
        $this->db->where('p.deleted', 0);
        $this->db->where("( p.is_nlm = 0 OR p.is_nlm IS NULL )");
        $this->db->where('j.id', $job_id);
        $this->db->where('a.country_id', $country_id);
        $sql = $this->db->get();
        $j = $sql->row();

        $property_id = $j->property_id;
        $property_vacant = $j->property_vacant;
        $new_job_email_to_agent = $j->new_job_email_to_agent;
        
        // if agency option 'new job email to agent' = yes
        if( $new_job_email_to_agent == 1 ){
            
            // send email to agency
            unset($jemail);
            $jemail = array();
            $temp = explode("\n",trim($j->agency_emails));
            foreach($temp as $val){
                
                $val2 = preg_replace('/\s+/', '', $val);
                if(filter_var($val2, FILTER_VALIDATE_EMAIL)){
                    $jemail[] = $val2;
                }
                
            }
            
            $agency_name = $j->agency_name;

            $prop_address = "{$j->p_address_1} {$j->p_address_2} {$j->p_address_3} {$j->p_state} {$j->p_postcode}";
            
            // subject
            $subject_txt = "Ready for Booking {$j->p_address_1} {$j->p_address_2} {$j->p_address_3}";

            $email_data = array(
                'agency_name' => $agency_name, 
                'agent_number' => $cntry->agent_number,
                'prop_address' => $prop_address
            );
            $sendParams = array(
                'email_data' => $email_data,
                'outgoing_email' => $cntry->outgoing_email,
                'jemail' => $jemail,
                'subject_txt' => $subject_txt,
                'from_txt' => "Smoke Alarm Testing Services",
                'view_page' => "emails/tenant_with_no_detail"
            );
            $emailRes = $this->send_email_main($sendParams);

            $log_details = "Email tenant with no detail";
            $log_params = array(
                'title' => 29,  //No Tenant Letters Sent
                'details' => $log_details,
                'display_in_vpd' => 1,
                'created_by_staff' => $this->session->staff_id,
                'property_id' => $property_id,
                'job_id' => $job_id
            );
            $this->system_model->insert_log($log_params);
            if (!$emailRes) { 
                // return false;
                // die();
            }else {
                // $log_details = "Email tenant with no detail";
                // $log_params = array(
                //     'title' => 29,  //No Tenant Letters Sent
                //     'details' => $log_details,
                //     'display_in_vpd' => 1,
                //     'created_by_staff' => $this->session->staff_id,
                //     'property_id' => $property_id,
                //     'job_id' => $job_id
                // );
                // $this->system_model->insert_log($log_params);
            }

        }
        
        // update
        $updateData = array(
            'tenant_ltr_sent' => date("Y-m-d")
        );
        $this->db->where('property_id', $property_id);
        $this->db->update('property', $updateData);
        
        // if property vacant
        if( $property_vacant==1 ){
            
            // move to To Be Booked
            $updateData = array(
                'status' => 'To Be Booked'
            );
            $this->db->where('status', 'Send Letters');
            $this->db->where('id', $job_id);
            $this->db->update('jobs', $updateData);
            
        }else{
            
            // move to escalate
            $updateData = array(
                'status' => 'Escalate'
            );
            $this->db->where('status', 'Send Letters');
            $this->db->where('id', $job_id);
            $this->db->update('jobs', $updateData);
            
            // esclate job reason, verify tenant detail ID
            $verify_tenant_details_id = 1;
            
            // clear any 'Verify Tenant Details' escalate job reason first, to avoid duplicate entry    
            $this->db->where('job_id', $job_id);
            $this->db->where('escalate_job_reasons_id', $verify_tenant_details_id);
            $this->db->delete('selected_escalate_job_reasons');
            
            // insert escalate job reason - Verify Tenant Details
            $data = array(
                    'job_id' => $job_id,
                    'escalate_job_reasons_id' => $verify_tenant_details_id,
                    'date_created' => date('Y-m-d H:i:s'),
                    'deleted' => 0,
                    'active' => 1
            );
            $this->db->insert('selected_escalate_job_reasons', $data);
            
        }
        return true;
    }


    public function getCountryViaCountryId($country_id){
        $query =  $this->db->get_where('countries', array('country_id'=> $country_id));
        return $query->row();
    }


    public function send_letters_send_tenant_sms($params){
        $job_id = $params['job_id'];
        $staff_id = $params['staff_id'];
        $country_id = $params['country_id'];
        unset($to_arr);
        unset($tenant_arr);
        $to_arr = array();
        $tenant_arr = array();
        
        // get country
        $cntry = $this->getCountryViaCountryId($country_id);

        // get phone prefix
        $this->db->select('*,
                p.`address_1` AS p_address_1, 
                p.`address_2` AS p_address_2, 
                p.`address_3` AS p_address_3, 
                p.`state` AS p_state, 
                p.`postcode` AS p_postcode');
        $this->db->from('jobs j');
        $this->db->join('property p','j.property_id = p.property_id', 'left');
        $this->db->join('agency a','p.agency_id = a.agency_id', 'left');
        $this->db->join('alarm_job_type ajt','j.service = ajt.id', 'left');
        $this->db->join('countries c','a.country_id = c.country_id', 'left');
        $this->db->where('j.id', $job_id);
        $sql = $this->db->get();
        $p = $sql->row();
        
        $property_id = $p->property_id;
        $agency_name = $p->agency_name;
        $prop_address = "{$p->p_address_1} {$p->p_address_2} {$p->p_address_3} {$p->p_state} {$p->p_postcode}";
        $new_job_email_to_agent = $p->new_job_email_to_agent;

        // get phone prefix
        $prefix = $p->phone_prefix;
        $sms_provider = "@app.wholesalesms.com.au";
        $num_tenants = $this->getCurrentMaxTenants();
        $sent_by = $this->session->staff_id;
        $tent_full_mob_num = [];
        $tenant_mob_arr = [];
        $ten_name = [];
        $ten_mob = '';
        
        // new tenants switch
        //$new_tenants = 0;
        $new_tenants = 1;
        
        if( $new_tenants == 1 ){ // new
        
            $pt_params = array( 
                'property_id' => $property_id,
                'active' => 1
             );
            $pt_sql = $this->getNewTenantsData($pt_params);
            foreach ($pt_sql as $pt_row) {
                
                // tenant mobile 
                $ten_mob = trim($pt_row->tenant_mobile);
                if($ten_mob!=''){
                    $trimmed_mob = str_replace(' ', '', $ten_mob);
                    // reformat number
                    $remove_zero = substr($trimmed_mob,1);
                    $mob = $prefix.$remove_zero;

                    $tenant_mob_arr[] = "{$mob}{$sms_provider}";
                    $tent_full_mob_num[] = $mob;
                    
                    // tenant name 
                    $ten_name[] = "{$pt_row->tenant_firstname} {$pt_row->tenant_lastname}";
                }
            }
        
        }else{ // OLD TENANTS
        
            for( $i=1; $i<=$num_tenants; $i++ ){
                
                // tenant mobile 
                $ten_mob = trim($p->tenant_mobi.$i);
                if($ten_mob!=''){
                    $trimmed_mob = str_replace(' ', '', $ten_mob);
                    // reformat number
                    $remove_zero = substr($trimmed_mob,1);
                    $mob = $prefix.$remove_zero;

                    $tenant_mob_arr[] = "{$mob}{$sms_provider}";
                    $tent_full_mob_num[] = $mob;
                    
                    // tenant name 
                    $ten_name[] = $p->tenant_firstname.$i ." ". $p->tenant_lastname.$i;
                }
            }
        }

        $body = "SATS have been asked to test the smoke alarms at the property you occupy. Our staff will contact you shortly to make an appointment. Any questions {$cntry->tenant_number}";

        $headers .= "To: {$to}" . "\r\n";
        $headers .= "From: SATS - Smoke Alarm Testing Services <{$cntry->outgoing_email}>" . "\r\n";

        $subj = 'Smoke Alarm Testing';
            
        // SEND SMS
        foreach( $tent_full_mob_num as $tent_mob ){
            // send SMS via API
            // $sms_type = 24; // send letters
            // $ws_sms = new WS_SMS($country_id,$body,$tent_mob);  
            // $sms_res = $ws_sms->sendSMS();
            // $ws_sms->captureSMSdata($sms_res,$job_id,$body,$tent_mob,$sent_by,$sms_type);
        }
        
        // insert logs
        // $tenant_names = implode(", ",$ten_name);
        // $data = array(
        //     'contact_type' => 'Welcome SMS sent',
        //     'eventdate' => date('Y-m-d'),
        //     'comments' => 'SMS to {$tenant_names} <strong>\"".mysql_real_escape_string($body)."\"</strong>',
        //     'job_id' => $job_id,
        //     'staff_id' => $staff_id,
        //     'eventtime' => date("H:i")
        // );
        // $this->db->insert('job_log', $data);
        $log_details = "Test smoke alarm sent sms";
        $log_params = array(
            'title' => 30,  //Test Smoke Alarm SMS Sent
            'details' => $log_details,
            'display_in_vjd' => 1,
            'created_by_staff' => $this->session->staff_id,
            'property_id' => $property_id,
            'job_id' => $job_id
        );
        $this->system_model->insert_log($log_params);
        
        // insert property logs
        // $data = array(
        //     'property_id' => $property_id,
        //     'staff_id' => $staff_id,
        //     'event_type' => 'Welcome SMS',
        //     'event_details' => 'Welcome SMS Sent',
        //     'log_date' => date('Y-m-d H:i:s')
        // );
        // $this->db->insert('property_event_log', $data)
        $log_details = "Test smoke alarm sent sms";
        $log_params = array(
            'title' => 30,  //Test Smoke Alarm SMS Sent
            'details' => $log_details,
            'display_in_vpd' => 1,
            'created_by_staff' => $this->session->staff_id,
            'property_id' => $property_id,
            'job_id' => $job_id
        );
        $this->system_model->insert_log($log_params);

        // if agency option 'new job email to agent' = yes
        if( $new_job_email_to_agent == 1 ){
            
            // send to agency
            unset($jemail);
            $jemail = array();
            $temp = explode("\n",trim($p->agency_emails));
            foreach($temp as $val){
                
                $val2 = preg_replace('/\s+/', '', $val);
                if(filter_var($val2, FILTER_VALIDATE_EMAIL)){
                    $jemail[] = $val2;
                }
                
            }

            $subject2 = "Tenant Notification {$p->p_address_1} {$p->p_address_2} {$p->p_address_3}";

            $email_data = array(
                'agency_name' => $agency_name, 
                'prop_address' => $prop_address,
                'agent_number' => $cntry->agent_number
            );
            $sendParams = array(
                'email_data' => $email_data,
                'outgoing_email' => $cntry->outgoing_email,
                'jemail' => $jemail,
                'subject_txt' => $subject2,
                'from_txt' => "Smoke Alarm Testing Services",
                'view_page' => "emails/tenant_notification"
            );
            $emailRes = $this->send_email_main($sendParams);
            if (!$emailRes) { 
                // return false;
                // die();
            }
        }
        // update
        $updateData1 = array(
            'tenant_ltr_sent' => date("Y-m-d")
        );
        $this->db->where('property_id', $property_id);
        $this->db->update('property', $updateData1);

        $updateData2 = array(
            'status' => 'To Be Booked'
        );
        $this->db->where('id', $job_id);
        $this->db->update('jobs', $updateData2);

        return true;
    }


    public function send_letters_send_tenant_email($params){
        $job_id = $params['job_id'];
        $staff_id = $params['staff_id'];
        $country_id = $params['country_id'];
        unset($to_arr);
        unset($tenant_arr);
        $to_arr = array();
        $tenant_arr = array();
        
        // get country
        $cntry = $this->getCountryViaCountryId($country_id);

        // get phone prefix
        $this->db->select('p.`property_id`, 
                p.`tenant_firstname1`,
                p.`tenant_lastname1`,
                p.`tenant_email1`, 
                p.`tenant_firstname2`,
                p.`tenant_lastname2`,
                p.`tenant_email2`, 
                p.`address_1` AS p_address_1, 
                p.`address_2` AS p_address_2, 
                p.`address_3` AS p_address_3, 
                p.`state` AS p_state, 
                p.`postcode` AS p_postcode, 
                a.`agency_name`,
                a.`agency_emails`,
                a.`new_job_email_to_agent`,
                ajt.`type`');
        $this->db->from('jobs j');
        $this->db->join('property p','j.property_id = p.property_id', 'left');
        $this->db->join('agency a','p.agency_id = a.agency_id', 'left');
        $this->db->join('alarm_job_type ajt','j.service = ajt.id', 'left');
        $this->db->where('j.id', $job_id);
        $sql = $this->db->get();
        $p = $sql->row();
        
        $property_id = $p->property_id;
        $agency_name = $p->agency_name;
        $prop_address = "{$p->p_address_1} {$p->p_address_2} {$p->p_address_3} {$p->p_state} {$p->p_postcode}";
        $new_job_email_to_agent = $p->new_job_email_to_agent;
        
        echo "new_job_email_to_agent: {$new_job_email_to_agent}";

        $new_tenants = 1;

        if( $new_tenants == 1 ){ // new

            $pt_params = array( 
                'property_id' => $property_id,
                'active' => 1
             );
            $pt_sql = $this->getNewTenantsData($pt_params);
            foreach ($pt_sql as $pt_row) {
                // tenant emails
                if($pt_row->tenant_email!=""){
                    $to_arr[] = $pt_row->tenant_email;
                }
                
                // tenant name
                if($pt_row->tenant_firstname!=""){
                    $tenant_arr[] = "{$pt_row->tenant_firstname} {$pt_row->tenant_lastname}";
                }
            }
            
            $to = implode(",",$to_arr);
            $tenant_str_imp = implode(", ",$tenant_arr); // separate tenant names with a comma
            $last_comma_pos = strrpos($tenant_str_imp,","); // find the last comma(,) position
            $tenant_str = substr_replace($tenant_str_imp,' &',$last_comma_pos,1); // replace comma with ampersand(&)

        }else{ // OLD TENANTS

            // tenant emails
            if($p->tenant_email1!=""){
                $to_arr[] = $p->tenant_email1;
            }
            if($p->tenant_email2!=""){
                $to_arr[] = $p->tenant_email2;
            }
            
            $to = implode(",",$to_arr);
            
            // tenant name
            if($p->tenant_firstname1!=""){
                $tenant_arr[] = "{$p->tenant_firstname1} {$p->tenant_lastname1}";
            }
            if($p->tenant_firstname2!=""){
                $tenant_arr[] = "{$p->tenant_firstname2} {$p->tenant_lastname2}";
            }
            $tenant_str = implode(" & ",$tenant_arr);
        }
        
        $subj = 'Smoke Alarm Testing';

        $email_data = array(
            'tenant_str' => $tenant_str,
            'p_address_1' => $p->p_address_1,
            'p_address_2' => $p->p_address_2,
            'p_address_3' => $p->p_address_3,
            'p_state' => $p->p_state,
            'p_postcode' => $p->p_postcode,
            'tenant_str' => $tenant_str,
            'agency_name' => $agency_name,
            'trading_name' => $cntry->trading_name,
            'type' => $p->type,
            'agency_name' => $agency_name,
            'agency_name' => $agency_name,
            'tenant_number' => $cntry->tenant_number,
            'email_signature' => $cntry->email_signature
        );
        $sendParams = array(
            'email_data' => $email_data,
            'outgoing_email' => $cntry->outgoing_email,
            'jemail' => $jemail,
            'subject_txt' => $subj,
            'from_txt' => "Smoke Alarm Testing Services",
            'view_page' => "emails/tenant_send_letter"
        );
        $emailRes = $this->send_email_main($sendParams);
        // if (!$emailRes) { 
        //     return false;
        //     die();
        // }else {
        // }
        
        $log_details = "Test smoke alarm sent letter";
        $log_params = array(
            'title' => 31,  //Test Smoke Alarm Letter Sent
            'details' => $log_details,
            'display_in_vjd' => 1,
            'created_by_staff' => $this->session->staff_id,
            'property_id' => $property_id,
            'job_id' => $job_id
        );
        // insert property logs
        $log_details = "Test smoke alarm sent letter";
        $log_params = array(
            'title' => 31,  //Test Smoke Alarm Letter Sent
            'details' => $log_details,
            'display_in_vpd' => 1,
            'created_by_staff' => $this->session->staff_id,
            'property_id' => $property_id,
            'job_id' => $job_id
        );
        
        // if agency option 'new job email to agent' = yes
        if( $new_job_email_to_agent == 1 ){
            
            // send to agency
            unset($jemail);
            $jemail = array();
            $temp = explode("\n",trim($p->agency_emails));
            foreach($temp as $val){
                $val2 = preg_replace('/\s+/', '', $val);
                if(filter_var($val2, FILTER_VALIDATE_EMAIL)){
                    $jemail[] = $val2;
                }
            }
            
            // send email
            $to2 = implode(",",$jemail);
            
            // subject
            $subject2 = "Tenant Notification {$p->p_address_1} {$p->p_address_2} {$p->p_address_3}";

            $email_data = array(
                'agency_name' => $agency_name, 
                'prop_address' => $prop_address,
                'agent_number' => $cntry->agent_number
            );
            $sendParams = array(
                'email_data' => $email_data,
                'outgoing_email' => $cntry->outgoing_email,
                'jemail' => $jemail,
                'subject_txt' => $subject2,
                'from_txt' => "Smoke Alarm Testing Services",
                'view_page' => "emails/tenant_notification"
            );
            $emailRes = $this->send_email_main($sendParams);   
        }       

        // update
        $updateData1 = array(
            'tenant_ltr_sent' => date("Y-m-d")
        );
        $this->db->where('property_id', $property_id);
        $this->db->update('property', $updateData1);

        $updateData2 = array(
            'status' => 'To Be Booked'
        );
        $this->db->where('id', $job_id);
        $this->db->update('jobs', $updateData2);
        return true;
        
    }

    public function getCurrentMaxTenants(){
        $num_tenants = 4;
        return $num_tenants;
    }

    public function getNewTenantsData($params){
        $query = $this->db->get_where('property_tenants', array(
            'property_id' => $params['property_id'],
            'active' => $params['active'],
            'property_tenant_id >' => 0));
        $row = $query->result();
        return $row;    
    }

    function getPropertyAgentDetails($property_id)
    {
        $query = "SELECT p.property_id, p.address_1, p.address_2, p.address_3, p.state, p.postcode, p.landlord_lastname, p.landlord_firstname, a.agency_name,
        a.address_1 AS a_address_1, a.address_2 AS a_address_2, a.address_3 AS a_address_3, a.state AS a_state, a.postcode  AS a_postcode, p.price, a.agency_id, p.`compass_index_num`, p.`send_to_email_not_api`, a.`add_inv_to_agen`
        FROM property p 
        LEFT JOIN agency a ON p.agency_id = a.agency_id
        WHERE p.property_id = '" . $property_id . "'";	
        
        $result = $this->mysqlSingleRow($query);
        
        return $result;
    }

    public function send_email_main($params) {
        $email_data = $params['email_data'];
        $outgoing_email = $params['outgoing_email'];
        $jemail = $params['jemail'];
        $subject_txt = $params['subject_txt'];
        $from_txt = $params['from_txt'];
        $view_page = $params['view_page'];

        $config = Array(
            'mailtype'  => 'html', 
            'charset'   => 'iso-8859-1',
            'smtp_timeout' => 30
        );
        
        $this->email->initialize($config);
        $this->email->set_newline("\r\n");

        $this->email->from($outgoing_email, $from_txt);
        $this->email->to($jemail);
        $this->email->subject($subject_txt);
        $body = $this->load->view($view_page, $email_data, TRUE);
        $this->email->message($body);
        $this->email->send();
        $isSend = $this->email->send();
        return $isSend;
    }

    // generate QR code
    public function generate_qr_code($invoice_number,$property_id,$amount_due,$gst_amount,$due_date,$country_id){
        
        $country_id2 = ($country_id=="")?$this->config->item('country'):$country_id;
        
        $absolute_path = APPPATH . 'third_party/phpqrcode/temp/';
        $file_name = "invoice_{$invoice_number}_qr_code.png";
        
        $fin_path = $absolute_path.$file_name;
        
        // get country
        $cntry_sql =  $this->getCountryViaCountryId($country_id2);
        $cntry = $cntry_sql;
        
        $bsb = str_replace(' ','',$cntry->bsb);
        $bank_acc_num = str_replace('-','',str_replace(' ','',$cntry->ac_number));
        $abn = str_replace(' ','',$cntry->abn);
        $due_date = date("dmY",strtotime(str_replace('/','-',$due_date)));
        
        $data = "getpaidfaster.com.au/p 1={$bsb} 2={$bank_acc_num} 3={$amount_due} 4={$due_date} 5={$abn} 6= 7={$gst_amount} 8={$invoice_number} 9={$property_id}";
        
        // pack them on an array for return
        $qr_code['data'] = $data;
        $qr_code['path'] = $fin_path;

        return $qr_code;
        //$qr_code::png($data, $fin_path);
        
    }

    public function getWaterMeter($job_id){
        return $this->db->query("
            SELECT *
            FROM `water_meter`
            WHERE `job_id` = {$job_id}
        ");
    }


}
