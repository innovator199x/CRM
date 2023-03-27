<?php

class SMS extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('sms_model');
        $this->load->model('jobs_model');
        $this->load->model('properties_model');
        $this->load->model('blink_api_model');
        $this->load->model('fdynamiclink_model');
        $this->load->library('pagination');
    }

    public function templates() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "SMS Templates";
        $data['class_id'] = $this->system_model->getStaffClassID();

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;
        $category = $this->input->get_post('category');
        $status = $this->input->get_post('status');
        if ($status != '') {
            $status = ( $status >= 0 ) ? $status : '';
        } else {
            $status = 1;
        }
        $data['status'] = $status;

        // excluded SMS template
        /*
        24 - Send Letters
        17 - SMS (Custom)
         */
        $exlude_id_arr = array(24,17);

        // staff classes
        $sel_query = "sms_api_type_id, type_name, category, body, active";

        // No Answer
        // (Keys SMS Reply), (Yes/No SMS Reply)
        if ($country_id == 1) { // AU
            array_push($exlude_id_arr,27,28);
        } else if ($country_id == 2) {
            array_push($exlude_id_arr,2,3);
        }

        // only show reminder on these people
        if( $this->config->item('country') == 1 ){ // AU

            /*
            2070 - Developer testing
            2025 - Daniel  Kramarzewski
            2287 - Ben Taylor
            2175 - Thalia Paki
            2226 - Sarah Guthrie
            2056 - Rob
            */

            $allowed_staff = array(2070, 2025, 2287, 2175, 2226, 2056);

        }else if( $this->config->item('country') == 2 ){ // NZ

            /*
            2070 - Developer testing
            2025 - Daniel  Kramarzewski
            2231 - Ben Taylor
            2193 - Thalia Paki
            2214 - Sarah Guthrie
            2124 - Ashley Orchard
            2056 - Rob
            */

            $allowed_staff = array(2070, 2025, 2231, 2193, 2214, 2124, 2056);

        }

        // if logged user is not part of the allowed user, exclude the following SMS template
        if( !in_array($staff_id, $allowed_staff) ){
            // 10 - Entry Notice (SMS EN)
            // 19 - SMS (Reminder)
            array_push($exlude_id_arr,10,19); //
        }

        $exlude_id_imp = implode(",",$exlude_id_arr);

        $custom_where = "sms_api_type_id NOT IN ({$exlude_id_imp})";
        $params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,
            'active' => $status,
            'category_filter' => $category,
            'sort_list' => array(
                array(
                    'order_by' => 'category',
                    'sort' => 'ASC'
                )
            ),
            'display_query' => 0
        );
        $data['list'] = $this->sms_model->getSmsTemplates($params);
        $data['last_query'] = $this->db->last_query();

        //category filter
        $params = array(
            'sel_query' => "DISTINCT(category)",
            'custom_where' => $custom_where,
            'active' => $status,
            'category_filter' => $category,
            'sort_list' => array(
                array(
                    'order_by' => 'category',
                    'sort' => 'ASC'
                )
            ),
            'display_query' => 0
        );
        $data['category_filter'] = $this->sms_model->getSmsTemplates($params);

        $this->load->view('templates/inner_header', $data);
        $this->load->view('sms/templates', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    // update template
    public function update_template() {

        $sms_api_type_id = $this->input->get_post('sms_api_type_id');
        $type_name = $this->input->get_post('type_name');
        $category = $this->input->get_post('category');
        $body = $this->input->get_post('body');
        $active = $this->input->get_post('active');

        $today_ts = date('Y-m-d H:i:s');
        $staff_id = $this->session->staff_id;

        if ($sms_api_type_id > 0 && $type_name != '' && is_numeric($active)) {

            // get current SMS template status
            $originalData = $this->db
                ->select("
                    type_name,
                    active
                ")
                ->from('sms_api_type')
                ->where('sms_api_type_id', $sms_api_type_id)
                ->get()->row();

            // Update SMS template
            $data = array(
                'type_name' => $type_name,
                'category' => $category,
                'body' => $body,
                'active' => $active
            );

            $this->db->where('sms_api_type_id', $sms_api_type_id);
            $this->db->update('sms_api_type', $data);

            if ($this->db->affected_rows() > 0) {

                $logMessage = "Updated <b>{$type_name}</b> SMS template. ";

                if ($originalData->type_name != $type_name) {
                    $logMessage .= "Changed type_name from \"{$originalData->type_name}\". ";
                }

                if( $active == 1 && $originalData->active == 0 ){ // activated
                    $logMessage .= "Activated <b>{$type_name}</b> SMS template. ";

                }else if( $active == 0 && $originalData->active == 1 ){ // deactivated
                    $logMessage .= "Deactivated <b>{$type_name}</b> SMS template. ";
                }

                $this->system_model->insert_log([
                    'title' => 77,
                    'details' => $logMessage,
                    'created_by_staff' => $this->session->staff_id,
                ]);

                $this->session->set_flashdata('update_template_success', 1);
            }
            redirect('/sms/templates');
        }
    }

    // add template
    public function add_template() {

        $type_name = $this->input->get_post('type_name');
        $category = $this->input->get_post('category');
        $body = $this->input->get_post('body');

        $today_ts = date('Y-m-d H:i:s');
        $staff_id = $this->session->staff_id;

        $data = array(
            'type_name' => $type_name,
            'category' => $category,
            'body' => $body
        );

        $this->db->insert('sms_api_type', $data);

        $this->system_model->insert_log([
            'title' => 77,
            'details' => "SMS Template <b>{$type_name}</b> was added.",
            'created_by_staff' => $this->session->staff_id,
        ]);

        $this->session->set_flashdata('add_template_success', 1);
        redirect('/sms/templates');
    }

    public function send() 
    {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Send SMS";

        $country_id = $data['country_id'] = $this->config->item('country');
        $staff_id = $this->session->staff_id;
        $job_id = $this->input->get_post('job_id');
        $data['job_id'] = $job_id;
        $tenant_id = $this->input->get_post('tenant_id');
        $template = $this->input->get_post('template');

        $this->form_validation->set_rules('send_to', 'Send To', 'required');
        if( $job_id > 0 ){
            $this->form_validation->set_rules('template', 'Template', 'required');
        }        
        $this->form_validation->set_rules('body', 'Template Body', 'required');

        if ($this->form_validation->run() == true) {

            $sent_to_tenant_ids = $this->input->get_post('sent_to_tenant_ids');
            $sent_to_tenant_ids_exp = explode(":", $sent_to_tenant_ids);

            if( $job_id > 0 ){

                foreach ($sent_to_tenant_ids_exp as $tenant_id) {

                    // get tenants data
                    $sel_query = "
                        pt.`property_tenant_id`,
                        pt.`tenant_firstname`,
                        pt.`tenant_lastname`,
                        pt.`tenant_mobile`
                    ";
                    $params = array(
                        'sel_query' => $sel_query,
                        'property_tenant_id' => $tenant_id,
                        'display_query' => 0
                    );
                    
                    $pt_sql = $this->properties_model->get_property_tenants($params);
                    $pt_row = $pt_sql->row();

                    $send_to = $this->sms_model->formatToInternationNumber($pt_row->tenant_mobile);                    
                    $body = $this->input->get_post('body');
                    $send_to_tenant = $pt_row->tenant_firstname;


                    // parse tags
                    $sms_params = array(
                        'job_id' => $job_id,
                        'tenant_firstname' => $send_to_tenant,
                        'unparsed_template' => $body
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
                        'sms_type' => $template,
                    );
                    $this->sms_model->captureSmsData($sms_params);

                    //insert log
                    $log_details = "SMS to {$send_to_tenant} ({$send_to}) <strong>\"{$parsed_template_body}\"</strong>";
                    $log_params = array(
                        'title' => 40, // SMS sent
                        'details' => $log_details,
                        'display_in_vjd' => 1,
                        'created_by_staff' => $staff_id,
                        'job_id' => $job_id
                    );
                    $this->system_model->insert_log($log_params);
                }

                $this->session->set_flashdata('sms_sent', 1);

            }else{

                $send_to = $this->input->get_post('send_to');                

                $body = $this->input->get_post('body');   
                
                // parse tags without job ID
                $sms_params = array(                    
                    'unparsed_template' => $body
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
                    'message' => $body,
                    'mobile' => $send_to,
                    'sent_by' => $staff_id,
                    'sms_type' => $template,
                );
                $this->sms_model->captureSmsData($sms_params);

                $this->session->set_flashdata('sms_sent', 1);

            }

        }

        // staff classes
        $sel_query = "sms_api_type_id, type_name, category, body, active";

        if( $job_id > 0 ){

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
            */

            $custom_where = "sms_api_type_id NOT IN (24,18,{$exlude_id})";
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

        }else{ // no job ID

            $custom_where = "category = 'Agency Sales SMS'";
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

        }
        
        $data['sms_templates_sql'] = $this->sms_model->getSmsTemplates($params);
        $data['sms_category'] = $this->sms_model->get_sms_category($job_id);

        if ($job_id != '') {


            // job data
            $sel_query = "
                j.`id` AS jid,
                j.`booked_with`,
                j.`job_type`,

                a.`agency_id`,
                a.`franchise_groups_id`
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

            $data['job_sql'] = $this->jobs_model->get_jobs($job_params);


            // tenants
            $sel_query = "
                j.`id` AS jid,

                pt.`property_tenant_id`,
                pt.`tenant_firstname`,
                pt.`tenant_lastname`,
                pt.`tenant_mobile`
            ";
            $tenant_params = array(
                'sel_query' => $sel_query,
                'del_job' => 0,
                'p_deleted' => 0,
                'a_status' => 'active',
                'pt_active' => 1,
                'country_id' => $country_id,
                'job_id' => $job_id,
                'join_table' => array('property_tenants'),
                'sort_list' => array(
                    array(
                        'order_by' => 'pt.`tenant_firstname`',
                        'sort' => 'ASC'
                    ),
                    array(
                        'order_by' => 'pt.`tenant_lastname`',
                        'sort' => 'ASC'
                    )
                ),
                'display_query' => 0
            );

            $data['tenants_sql'] = $this->jobs_model->get_jobs($tenant_params);
        }

        $this->load->view('templates/inner_header', $data);
        $this->load->view('sms/send', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function get_template() {

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;
        $template = $this->input->get_post('template');
        $job_id = $this->input->get_post('job_id');

        // get template content
        $sel_query = "sms_api_type_id, body";
        $params = array(
            'sel_query' => $sel_query,
            'active' => 1,
            'sms_api_type_id' => $template,
            'display_query' => 0
        );
        $sql = $this->sms_model->getSmsTemplates($params);
        $row = $sql->row();
        $template_body = $row->body;

        if( $job_id > 0 ){
      
            // job data
            $sel_query = "
                j.`id` AS jid,
                j.`job_type`,

                a.`agency_id`,
                a.`franchise_groups_id`,
                j.`is_eo`
            ";
            $jobs_params = array(
                'sel_query' => $sel_query,
                'del_job' => 0,
                'p_deleted' => 0,
                'a_status' => 'active',
                'country_id' => $country_id,
                'job_id' => $job_id,
                'display_query' => 0
            );

            $sql = $this->jobs_model->get_jobs($jobs_params);
            $job_row = $sql->row();

            // No Answer
            if ($template == 1) {

                if ($job_row->agency_id == 1200) { // private agency
                    $template_body = "Please call SATS {tenant_number} to make an appointment to service your smoke alarms";
                } else if ( $job_row->job_type == "240v Rebook" || $job_row->is_eo == 1 ) {  // jobs with job type 240 rebook
                    $template_body = "{serv_name} need to be replaced on behalf of your landlord and {agency_name}. Please call SATS {tenant_number}";
                } else if ($this->system_model->getAgencyPrivateFranchiseGroups($job_row->franchise_groups_id) == true) { // agency with franchise group private
                    $template_body = "SATS need to test {serv_name} on behalf of your landlord. Please call {tenant_number}";
                }
            }

        

            // SMS count
            $params = array(
                'job_id' => $job_id,
                'unparsed_template' => $template_body
            );

            $parsed_template = $this->sms_model->parseTags($params);
            $final_template_body = $parsed_template;

        }else{
            
            $final_template_body = $template_body;
            
        }

        $sms_count = strlen($final_template_body);
        $sms_cost = ceil(strlen($final_template_body) / 160);

        $json_arr = array(
            "template_body" => $template_body,
            "sms_count" => $sms_count,
            "sms_cost" => $sms_cost
        );

        echo json_encode($json_arr);
    }

    public function parse_tags() {

        $job_id = $this->input->get_post('job_id');
        $unparsed_template = $this->input->get_post('unparsed_template');
        $tenant_id = $this->input->get_post('tenant_id');

        if( $job_id > 0 ){
            
            // get tenants data
            $sel_query = "pt.`property_tenant_id`, pt.`tenant_firstname`, pt.`tenant_lastname`, pt.`tenant_mobile`";
            $pt_params = array(
                'sel_query' => $sel_query,
                'property_tenant_id' => $tenant_id,
                'display_query' => 0
            );
        
            $tenants_sql = $this->properties_model->get_property_tenants($pt_params);
            $tenants_row = $tenants_sql->row();
            
            // SMS count
            $params = array(
                'job_id' => $job_id,
                'tenant_firstname' => $tenants_row->tenant_firstname,
                'unparsed_template' => $unparsed_template
            );

            $parsed_template = $this->sms_model->parseTags($params);
            $final_template_body = $parsed_template;

        }else{

            $final_template_body = $unparsed_template;

        }
        
        $parsed_template = $this->sms_model->parseTags($params);               


        $sms_count = strlen($final_template_body);
        $sms_cost = ceil(strlen($final_template_body) / 160);

        $json_arr = array(
            "parsed_template" => $final_template_body,
            "sms_count" => $sms_count,
            "sms_cost" => $sms_cost
        );

        echo json_encode($json_arr);
    }

    public function view_outgoing_sms() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Outgoing SMS";
        $from_date = $this->input->get_post('from_date');
        if ($from_date != '') {
            $from_date2 = $this->system_model->formatDate($from_date);
        }
        $to_date = $this->input->get_post('to_date');
        ;
        if ($to_date != '') {
            $to_date2 = $this->system_model->formatDate($to_date);
        }

        $show_all = $this->input->get_post('show_all');
        $unread = ($show_all == 1) ? '' : 1;
        $sms_type = $this->input->get_post('sms_type');
        $sent_by = $this->input->get_post('sent_by');
        $cb_status = $this->input->get_post('cb_status');
        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;

        $per_page = $this->config->item('pagi_per_page');
        $offset = ( null != $this->input->get_post('offset') ) ? $this->input->get_post('offset') : '0';

        if ($this->input->get('echo_query') !== null) {
            $echo_query = 1;
        } else {
            $echo_query = 0;
        }
        // list
        $cust_sel = "
                sas.`sms_api_sent_id`,
                sas.`sent_by`,sa.`FirstName`,
                sas.`job_id`,
                sas.`cb_status`,
                sas.`created_date` AS sas_created_date,
                sas.`mobile` AS sas_mobile,
                sas.`message`,

                sat.`type_name`,
                sat.`sms_api_type_id`,

                p.`property_id`,

                sa.`LastName`
                ";
        if ($from_date2 != '' && $to_date2 != '') {
            $cust_filt = "CAST(sas.`created_date` AS DATE) BETWEEN '{$from_date2}' AND '{$to_date2}'";
        }
        $list_params = array(
            'sort_list' => array(
                array(
                    'order_by' => 'sas.`created_date`',
                    'sort' => 'DESC'
                )
            ),
            'paginate' => array(
                'offset' => $offset,
                'limit' => $per_page
            ),
            'echo_query' => 0,
            'sms_type' => $sms_type,
            'sms_page' => 'outgoing',
            'sent_by' => $sent_by,
            'cb_status' => $cb_status,
            'custom_select' => $cust_sel,
            'custom_filter' => $cust_filt
        );
        $data['list'] = $this->sms_model->getSMSrepliesMergedData($list_params);
        $data['last_query'] = $this->db->last_query();
        $data['sat_list'] = $this->sms_model->getSMStype("sat.sms_api_type_id,sat.type_name");
        $data['crm_setting'] = $this->system_model->getCrmSettings([
            "sel_str" => "sms_credit,sms_credit_update_ts"
        ]);
        $data = array_merge($data, $_POST, $_GET);

        $t_params = array(
            'sort_list' => array(
                array(
                    'order_by' => 'sas.`created_date`',
                    'sort' => 'DESC'
                )
            ),
            'echo_query' => $echo_query,
            'sms_type' => $sms_type,
            'sms_page' => 'outgoing',
            'sent_by' => $sent_by,
            'cb_status' => $cb_status,
            'custom_select' => "COUNT(*) as total_rows",
            'custom_filter' => $cust_filt
        );
        $total_rows = $this->sms_model->getSMSrepliesMergedData($t_params)->row()->total_rows;
        // base url
        $base_url = '/sms/view_outgoing_sms/';

        $pagi_links_params_arr = array(
            'from_date' => $from_date,
            'to_date' => $to_date,
            'sms_type' => $sms_type,
            'cb_status' => $cb_status
        );
        $pagi_link_params = '/sms/view_outgoing_sms/?'.http_build_query($pagi_links_params_arr);

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
        $this->load->view('sms/view_outgoing_sms', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function view_incoming_sms() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Incoming SMS";
        $from_date = $this->input->get_post('from_date');
        if ($from_date != '') {
            $from_date2 = $this->system_model->formatDate($from_date);
        }
        $to_date = $this->input->get_post('to_date');
        ;
        if ($to_date != '') {
            $to_date2 = $this->system_model->formatDate($to_date);
        }

        $show_all = $this->input->get_post('show_all');
        $unread = ($show_all == 1) ? '' : 1;
        $sms_type = $this->input->get_post('sms_type');
        $sent_by = $this->input->get_post('sent_by');
        $cb_status = $this->input->get_post('cb_status');
        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;
        $phrase_filter = $this->input->get_post('phrase');

        $per_page = $this->config->item('pagi_per_page');
        $offset = ( null != $this->input->get_post('offset') ) ? $this->input->get_post('offset') : '0';

        // list
        $cust_sel = "
                sas.`sms_api_sent_id`,
                sas.`sent_by`,
                sas.`sms_type`,
                sas.`job_id`,

                sar.`sms_api_replies_id`,
                sar.`message_id`,
                sar.`created_date` AS sar_created_date,
                sar.`mobile` AS sar_mobile,
                sar.`response`,
                sar.`saved`,
                sar.`unread`,

                sa.`FirstName`,
                sa.`LastName`,

                sat.`type_name`,
                sat.`sms_api_type_id`,

                p.`property_id`
                ";
        if ($from_date2 != '' && $to_date2 != '') {
            $cust_filt = "CAST(sas.`created_date` AS DATE) BETWEEN '{$from_date2}' AND '{$to_date2}'";
        }
        $list_params = array(
            'sort_list' => array(
                array(
                    'order_by' => 'sar.`datetime_entry`',
                    'sort' => 'DESC'
                )
            ),
            'paginate' => array(
                'offset' => $offset,
                'limit' => $per_page
            ),
            'echo_query' => 0,
            'unread' => $unread,
            'sms_type' => $sms_type,
            'sms_page' => 'incoming',
            'sent_by' => $sent_by,
            'cb_status' => $cb_status,
            'custom_select' => $cust_sel,
            'phrase_filter' => $phrase_filter,
            'custom_filter' => $cust_filt,
            //'group_by' => "sar.sms_api_replies_id"
            
        );
        $data['list'] = $this->sms_model->getSMSrepliesMergedData($list_params);
        $data['sql_query'] = $this->db->last_query();
        //echo $this->db->last_query();
        //exit();

        $data['sat_list'] = $this->sms_model->getSMStype("sat.sms_api_type_id,sat.type_name");
        //echo $this->db->last_query();
        //exit();

        $list_params['custom_select'] = "DISTINCT(sas.`sent_by`),sa.`FirstName`, sa.`LastName`";
        unset($list_params['sent_by']);
        $data['sent_by_list'] = $this->sms_model->getSMSrepliesMergedData($list_params);
        $data['crm_setting'] = $this->system_model->getCrmSettings([
            "sel_str" => "sms_credit,sms_credit_update_ts"
        ]);
        $data = array_merge($data, $_POST, $_GET);

        $t_params = array(
            'sort_list' => array(
                array(
                    'order_by' => 'sas.`created_date`',
                    'sort' => 'DESC'
                )
            ),
            'unread' => $unread,
            'sms_type' => $sms_type,
            'sms_page' => 'incoming',
            'sent_by' => $sent_by,
            'phrase_filter' => $phrase_filter,
            'cb_status' => $cb_status,
            'custom_select' => "sar.sms_api_replies_id",
            'custom_filter' => $cust_filt,
            //'group_by' => "sar.sms_api_replies_id"
        );
        //$total_rows = $this->sms_model->getSMSrepliesMergedData_v2($t_params)->row()->total_rows;
        $total_rows = $this->sms_model->getSMSrepliesMergedData($t_params)->num_rows();

        $pagi_links_params_arr = array(
            'from_date' => $from_date,
            'to_date' => $to_date,
            'sms_type' => $sms_type,
            'sent_by' => $sent_by,
            'phrase_filter' => $phrase_filter,
            'show_all' => $show_all
        );
        $pagi_link_params = '/sms/view_incoming_sms/?'.http_build_query($pagi_links_params_arr);

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
        $this->load->view('sms/view_incoming_sms', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function view_job_feedback_sms() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Job Feedback";
        $uri = "/sms/view_job_feedback_sms";
        $data['uri'] = $uri;
        $tech = $this->input->get_post('tech');

        $from_date = ( $this->input->get_post('from_date') !='' )?$this->input->get_post('from_date'):date('01/m/Y');
        if ($from_date != '') {
            $from_date2 = $this->system_model->formatDate($from_date);
        }
        $to_date = ( $this->input->get_post('to_date') !='' )?$this->input->get_post('to_date'):date('t/m/Y');
        if ($to_date != '') {
            $to_date2 = $this->system_model->formatDate($to_date);
        }

        $show_all = $this->input->get('show_all');
        $unread = ($show_all == 1) ? '' : 1;
        $sms_type = 18;
        $sent_by = $this->input->get_post('sent_by');
        $cb_status = $this->input->get_post('cb_status');
        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( null != $this->input->get_post('offset') ) ? $this->input->get_post('offset') : '0';
        $export = $this->input->get_post('export');

        // list
        $cust_sel = "
                sas.`job_id`,
                sas.`sms_api_sent_id`,
                sas.`sent_by`,
                sas.`sms_type`,
                sas.`job_id`,

                sar.`sms_api_replies_id`,
                sar.`message_id`,
                sar.`created_date` AS sar_created_date,
                sar.`mobile` AS sar_mobile,
                sar.`response`,
                sar.`saved`,
                sar.`unread`,
                sar.`sms_replied_to`,

                sa.`FirstName`, 
                sa.`LastName`,

                sat.`type_name`,
                sat.`sms_api_type_id`,

                p.`property_id`,

                ass_tech.`StaffID` as at_StaffID,
                ass_tech.`FirstName` as at_FirstName,
                ass_tech.`LastName` as at_LastName
                ";
        if ($from_date2 != '' && $to_date2 != '') {
            $cust_filt = "CAST(sar.`created_date` AS DATE) BETWEEN '{$from_date2}' AND '{$to_date2}'";
        }
        $list_params = array(
            'sort_list' => array(
                array(
                    'order_by' => 'sar.`datetime_entry`',
                    'sort' => 'DESC'
                )
            ),
            'echo_query' => 0,
            'sms_type' => $sms_type,
            'sms_page' => 'incoming',
            'sent_by' => $sent_by,
            'tech' => $tech,
            'cb_status' => $cb_status,
            'custom_select' => $cust_sel,
            'custom_filter' => $cust_filt
        );


        // export should show all        
        if ( $export != 1 ){ 
            $list_params['paginate'] = array(
                'offset' => $offset,
                'limit' => $per_page
            );
        }

        $sms_sql = $this->sms_model->getSMSrepliesMergedData($list_params);

        if ( $export == 1 ) { // EXPORT         

            // file name
            $date_export = date('YmdHis');
            $filename = "job_feedback_export_{$date_export}.csv";

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            // file creation 
            $csv_file = fopen('php://output', 'w');            

            $header = array("SMS Type","Date","Time","From","Tenant","Message","Technician");
            fputcsv($csv_file, $header);
            
            
            foreach ( $sms_sql->result_array() as $cr ){ 

                $mob_num = '0'.substr($cr['sar_mobile'],2);

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
                'property_id' => $cr['property_id'],
                'pt_active' => 1,
                'display_query' => 0
                );
                $pt_sql = $this->properties_model->get_property_tenants($params);

                $tenant_name = null;
                if( $pt_sql->num_rows() > 0 ){

                    // loop through tenants
                    foreach($pt_sql->result() as $pt_row){
                        $tenants_num = str_replace(' ', '', trim($pt_row->tenant_mobile));
                        if( $tenants_num != '' && $tenants_num == $mob_num ){
                            $tenant_name = $pt_row->tenant_firstname;
                        }
                    }
                }

                $csv_row = [];                              

                $csv_row[] = $cr['type_name'];
                $csv_row[] = ( $this->system_model->isDateNotEmpty($cr['sar_created_date']) )?date('d/m/Y', strtotime($cr['sar_created_date'])):null;
                $csv_row[] = ( $this->system_model->isDateNotEmpty($cr['sar_created_date']) )?date('H:i', strtotime($cr['sar_created_date'])):null;
                $csv_row[] = $mob_num;
                $csv_row[] = $tenant_name;
                $csv_row[] = $cr['response'];
                $csv_row[] = "{$cr['at_FirstName']} {$cr['at_LastName']}";            
                
                fputcsv($csv_file,$csv_row); 

            }
            
        
            fclose($csv_file); 
            exit; 

        }else{

            $data['list'] = $sms_sql;
            $data['last_query'] = $this->db->last_query();
            $data['sat_list'] = $this->sms_model->getSMStype("sat.sms_api_type_id,sat.type_name");
            $list_params = array(
                'sort_list' => array(
                    array(
                        'order_by' => 'sas.`created_date`',
                        'sort' => 'DESC'
                    )
                ),
                'echo_query' => 0,
                'sms_type' => $sms_type,
                'sms_page' => 'incoming',
                'tech' => $tech,
                'custom_select' => "DISTINCT(sas.`sent_by`),sa.`FirstName`, sa.`LastName`",
                'custom_filter' => $cust_filt
            );
            $data['sent_by_list'] = $this->sms_model->getSMSrepliesMergedData($list_params);

            $list_params = array(
                'sort_list' => array(
                    array(
                        'order_by' => 'ass_tech.`FirstName`',
                        'sort' => 'ASC'
                    ),
                    array(
                        'order_by' => 'ass_tech.`LastName`',
                        'sort' => 'ASC'
                    )
                ),
                'echo_query' => 0,
                'sms_type' => $sms_type,
                'sms_page' => 'incoming',
                'custom_select' => "DISTINCT (j.`assigned_tech`), ass_tech.`StaffID`, ass_tech.`FirstName`, ass_tech.`LastName`",
                'custom_filter' => $cust_filt
            );
            $data['tech_list'] = $this->sms_model->getSMSrepliesMergedData($list_params);
            $data['crm_setting'] = $this->system_model->getCrmSettings([
                "sel_str" => "sms_credit,sms_credit_update_ts"
            ]);
            $data = array_merge($data, $_POST, $_GET);

            $t_params = array(
                'sort_list' => array(
                    array(
                        'order_by' => 'sas.`created_date`',
                        'sort' => 'DESC'
                    )
                ),
                'echo_query' => 0,
                'sms_type' => $sms_type,
                'sms_page' => 'incoming',
                'sent_by' => $sent_by,
                'tech' => $tech,
                'cb_status' => $cb_status,
                'custom_select' => "COUNT(*) as total_rows",
                'custom_filter' => $cust_filt
            );
            $total_rows = $this->sms_model->getSMSrepliesMergedData($t_params)->row()->total_rows;

            $pagi_links_params_arr = array(
                'from_date' => $from_date,
                'to_date' => $to_date,
                'sent_by' => $sent_by,
                'tech' => $tech
            );
            
            // pagination link
            $pagi_link_params = "{$uri}/?".http_build_query($pagi_links_params_arr);

            // explort link
            $data['export_link'] = "{$uri}/?export=1&".http_build_query($pagi_links_params_arr);

            // pagination
            $config['page_query_string'] = TRUE;
            $config['query_string_segment'] = 'offset';
            $config['total_rows'] = $total_rows;
            $config['per_page'] = $per_page;
            $config['base_url'] = $pagi_link_params;
            ;

            $this->pagination->initialize($config);

            $data['pagination'] = $this->pagination->create_links();

            $pc_params = array(
                'total_rows' => $total_rows,
                'offset' => $offset,
                'per_page' => $per_page
            );
            $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);
            $this->load->view('templates/inner_header', $data);
            $this->load->view('sms/view_job_feedback', $data);
            $this->load->view('templates/inner_footer', $data);
            
        }

        
    }

    function sms_replied_to_update() {
        $sms_id = $this->input->post('sms_api_replies_id');
        $sql = "
        SELECT sms_replied_to
        FROM `sms_api_replies`
        WHERE `sms_api_replies_id` = {$sms_id}
        ";
        $data = $this->db->query($sql);
        $sms_replied_to = $data->row()->sms_replied_to;

        if ($sms_replied_to == 0) {
            $this->db->query("
                UPDATE `sms_api_replies`
                SET `sms_replied_to` = '1'
                WHERE `sms_api_replies_id` = {$sms_id}
                ");
        } else {
            $this->db->query("
                UPDATE `sms_api_replies`
                SET `sms_replied_to` = '0'
                WHERE `sms_api_replies_id` = {$sms_id}
                ");
        }
    }

    public function update_sms_credits_action_form_submit() {
        $redirect = $this->input->get('redirect');
        $updated = $this->sms_model->getBalance_v2_jlcbada();
        if ($updated) {
            $this->session->set_flashdata([
                'success_msg' => 'Credit Balance updated.',
                'status' => 'success'
            ]);
        } else {
            $this->session->set_flashdata([
                'error_msg' => 'Update not successful.',
                'status' => 'error'
            ]);
        }
        if ($redirect === "incoming_sms") {
            redirect(base_url('/sms/view_incoming_sms'));
        } else {
            redirect(base_url('/sms/view_outgoing_sms'));
        }
    }

    public function toggle_sms_replies_action_ajax() {
        $sar_id = $this->input->post('sar_id');
        if ((int) $sar_id === 0) {
            $this->session->set_flashdata([
                'error_msg' => 'Update not successful.',
                'status' => 'error'
            ]);
        }
        $unread = $this->input->post('unread');
        $unread2 = ($unread == 1) ? 1 : 'NULL';
        $updated = $this->sms_model->toggle_sms_replies($sar_id, ['unread' => $unread2]);
        if ($updated) {
            $this->session->set_flashdata([
                'success_msg' => 'Sms reply updated.',
                'status' => 'success'
            ]);
        } else {
            $this->session->set_flashdata([
                'error_msg' => 'Update not successful.',
                'status' => 'error'
            ]);
        }
    }

    public function get_job_future_str_action_ajax() {
        $job_id = $this->input->post('job_id');
        $strQ = $this->sms_model->get_future_str($job_id)->result_array();
        if (count($strQ)) {
            ?>

            <table style="border-collapse: initial;">
                <?php
                foreach ($strQ as $other_str) {
                    ?>
                    <tr>
                        <td style="font-size: 13px;">
                            <a target="__blank" class="str_link" href="<?php echo $this->config->item("crm_link"); ?>/set_tech_run.php?tr_id=<?php echo $other_str['tech_run_id']; ?>">
                                <?php echo date('D d/m', strtotime($other_str['tr_date'])); ?>
                            </a>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <?Php
        }
    }

    public function process_sms_api_replies_action_ajax() {
        $staff_id = $this->session->staff_id;

        $job_id = $this->input->get_post('job_id');
        $message_id = $this->input->get_post('message_id');
        $sas_id = $this->input->get_post('sas_id');
        $sar_id = $this->input->get_post('sar_id');
        $tenant_name = $this->input->get_post('tenant_name');
        $reply_msg = $this->input->get_post('reply_msg');
        $btn_used = $this->input->get_post('btn_used');
        $sms_type = $this->input->get_post('sms_type');

        $sms = $this->sms_model->getSmsRepliesData($message_id, $sar_id)->row_array();
        $datetime_entry = $sms['created_date'];
        $sms_reply_date = date('d/m', strtotime($datetime_entry));
        $sms_reply_time = date('H:i', strtotime($datetime_entry));
        $sms_reply_mobile = $sms['mobile'];

        $current_time = date('H:i');

        if ($btn_used == 'save') {

            $jl_ct_txt = 'Saved';
            // mark as log saved
            $this->sms_model->toggle_sms_replies($sar_id, ['saved' => 1]);
        } else {
            $jl_ct_txt = 'Processed';
            $this->sms_model->toggle_sms_replies($sar_id, ['unread' => "NULL"]);
        }

        /*
        $params = [
            'contact_type' => "SMS Replies {$jl_ct_txt}",
            'eventdate' => $sms_reply_date,
            'comments' => "{$tenant_name} replied <strong>\"{$reply_msg}\"</strong>",
            'job_id' => $job_id,
            'staff_id' => $staff_id,
            'eventtime' => $sms_reply_time
        ];
        $this->sms_model->saveJobLog($params);
        */

        //insert log
        //$log_details = "SMS received at <b>{$sms_reply_time}</b> on <b>{$sms_reply_date}</b>: <b>\"{$reply_msg}\"</b>. Processed at: <b>{$current_time}</b>";

        $log_details = "<b>{$tenant_name}</b> Replies <b>\"{$reply_msg}\"</b> from number <b>+{$sms_reply_mobile}</b>. SMS received at <b>{$sms_reply_time}</b> on <b>{$sms_reply_date}</b>. Processed at: <b>{$current_time}</b>";
        $log_params = array(
            'title' => 76, // SMS Replies
            'details' => $log_details,
            'display_in_vjd' => 1,
            'created_by_staff' => $staff_id,
            'job_id' => $job_id
        );
        $this->system_model->insert_log($log_params);

        // do not delete Thank you SMS
        $ty_sms_type_id = 18; // thank you SMS
        if ($sms_type != $ty_sms_type_id) {

            /*
            // DELETE sms
            // SMS sent
            $this->db->where(["message_id" => $message_id, "sms_api_sent_id" => $sas_id])->delete("sms_api_sent");

            // SMS replies
            $this->db->where(["message_id" => $message_id, "sms_api_replies_id" => $sar_id])->delete("sms_api_replies");
            */

        }
    }

    public function datatable_logs() {
        $title = 77;

        $columns = array( 
            0 => 'details', 
            1 => 'name',
            2 => 'created_date'
        );

		$limit = $this->input->post('length');
        $start = $this->input->post('start');
        $order = $columns[$this->input->post('order')[0]['column']];
        $dir = $this->input->post('order')[0]['dir'];
  
        $totalData = $this->sms_model->all_logs_count($title);
            
        $totalFiltered = $totalData; 
            
        if(empty($this->input->post('search')['value'])) {            
            $logs = $this->sms_model->all_logs($title,$limit,$start,$order,$dir);
        }
        else {
            $search = $this->input->post('search')['value']; 
            $logs =  $this->sms_model->logs_search($title,$limit,$start,$search,$order,$dir);
            $totalFiltered = $this->sms_model->logs_search_count($title,$search);
        }

        $data = array();
        if(!empty($logs)) {
            foreach ($logs as $log) {
                $nestedData['details'] = $log->details;
                $nestedData['name'] = $log->name;
                $nestedData['created_date'] = $log->created_date;
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw" => intval($this->input->post('draw')),  
            "recordsTotal" => intval($totalData),  
            "recordsFiltered" => intval($totalFiltered), 
            "data" => $data   
        );
        echo json_encode($json_data); 
    }

    public function validate_number(){
        $number = $this->input->post('send_to');
         // send SMS
         $sms_params = array(
            'mobile' => $number
        );
        $sms_json = $this->sms_model->validateNumber($number);
        echo json_encode($sms_json);
    }

    public function yabbr_catch_callback(){
        $json_data = $this->input->raw_input_stream;

        $json_dec = json_decode($json_data);

        $event_obj = $json_dec->messages[0];

        $is_delivered = ($event_obj->receipts->delivered ? 1 : 0);
        $is_rejected = ($event_obj->receipts->rejected ? 1 : 0);
        $is_expired = ($event_obj->receipts->expired ? 1 : 0);
        $is_undelivered = ($event_obj->receipts->undelivered ? 1 : 0);
        
        $status = 'pending';
        if($is_delivered == 1){
            $status = 'delivered';
        } else if($is_rejected == 1){
            $status = 'hard-bounce';
        }

        $params = [
            'message_id' => $event_obj->id,
            'status' => $status,
            'mobile' => $event_obj->to,
            'datetime' => $event_obj->created,
            'is_delivered' => $is_delivered,
            'is_rejected' => $is_rejected,
            'is_expired' => $is_expired,
            'is_undelivered' => $is_undelivered,
        ];
        $this->sms_model->update_status($params);
    }

    //Catch reply from API
    public function yabbr_catch_reply(){
        $json_data = $this->input->raw_input_stream;
        $json_dec = json_decode($json_data);
        $event_obj = $json_dec->messages[0];

        $params = [
            'message_id' => $event_obj->id,
            'mobile' => $event_obj->from,
            'response' => $event_obj->content,
            'datetime_entry' => $event_obj->created,
            'created_date' => date('Y-m-d H:i:s'),
        ];
        
        $this->sms_model->saveReply($params);
    }

    public function yabbr_catch_callback_test_switch(){
        echo "test only";
    }

    public function yaabr_test(){
        $send = $this->input->get_post('send');
        $count = $this->input->get_post('count');

        if( $send==1 ){ ## if 1 trigger send sms
            
            $send_to = $this->sms_model->formatToInternationNumber('0428798182'); ##Test Ben's number
            $sms_msg = "Test SMS - Please ignore {$count}.";
             // send SMS
             $sms_params = array(
                'sms_msg' => $sms_msg,
                'mobile' => $send_to
            );
            $sms_json = $this->sms_model->sendSMS_test_yabbr($sms_params);

             // save SMS data on database
             $sms_params = array(
                'sms_json' => $sms_json,
                'job_id' => 1948,
                'message' => $sms_msg,
                'mobile' => $send_to,
                'sent_by' => '2070',
                'sms_type' => 17,
            );
            $send = $this->sms_model->captureSmsData($sms_params);

        }else{
            echo "No no no!";
        }
    }

    public function test_yabbr_catch_callback(){
        $json_data = $this->input->raw_input_stream;

        $json_dec = json_decode($json_data);

        $event_obj = $json_dec->messages[0];

        $is_delivered = ($event_obj->receipts->delivered ? 1 : 0);
        $is_rejected = ($event_obj->receipts->rejected ? 1 : 0);
        $is_expired = ($event_obj->receipts->expired ? 1 : 0);
        $is_undelivered = ($event_obj->receipts->undelivered ? 1 : 0);
        
        $status = 'pending';
        if($is_delivered == 1){
            $status = 'delivered';
        } else if($is_rejected == 1){
            $status = 'hard-bounce';
        }

        $params = [
            'message_id' => $event_obj->id,
            'status' => $status,
            'mobile' => $event_obj->to,
            'datetime' => $event_obj->created,
            'is_delivered' => $is_delivered,
            'is_rejected' => $is_rejected,
            'is_expired' => $is_expired,
            'is_undelivered' => $is_undelivered,
        ];
        $this->sms_model->update_status($params);
    }

    public function test_yabbr_catch_reply(){

        $this->load->library('email');

        $json_data = $this->input->raw_input_stream;
        ##$json_dec = json_decode($json_data);
        ##$event_obj = $json_dec->messages[0];

        /*$data = [
            'content' => $event_obj->content,
            'from' => $event_obj->from,
            'datetime_entry' => $event_obj->created,
            'json_reponse' => $json_data
        ];*/

        $data = [
            'content' => 'test',
            'json_reponse' => $json_data
        ];
        
        $this->db->insert('yaabr_test_reply', $data);


        //TRY EMAIL

        $email_content = ( !empty($json_data) ) ? $json_data : "No Raw Data";

        //email config
         $config = Array(
            'mailtype' => 'html',
            'charset' => 'iso-8859-1'
        );
        $this->email->initialize($config);
        $this->email->set_newline("\r\n");

        $this->email->from('itsmegherx@gmail.com', 'CRM');
        $this->email->to('vaultdweller123@gmail.com, bent@sats.com.au, alexw@sats.com.au, itsmegherx@gmail.com');
        $this->email->subject('Test Capture Yabbr Raw Data');
        $this->email->message($email_content);
        //$this->email->message('Test Static Content');
        $this->email->send();

    }

    public function sms_missing_job(){
        $data['start_load_time'] = microtime(true);
        $data['title'] = "SMS Missing Job";
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( null != $this->input->get_post('offset') ) ? $this->input->get_post('offset') : '0';


        $phrase = $this->input->get_post('phrase');
        $show_all = $this->input->get_post('show_all');

        ##main query start
        
        $q_params = array(
            'paginate' => array(
                'offset' => $offset,
                'limit' => $per_page
            ),
            'phrase' => $phrase,
            'show_all' => $show_all

        );
        $tt = $this->sms_model->get_sms_missing_job($q_params);
        $data['list'] = $tt;
        ##main query start end

        $data['last_query'] = $this->db->last_query();

        ##total rows
        $tot_params = array(
            'phrase' => $phrase,
            'show_all' => $show_all

        );
        $tot_q = $this->sms_model->get_sms_missing_job($tot_params);
        $total_rows = $tot_q->num_rows();
        ##total rows end

        $pagi_links_params_arr = array(
            //'from_date' => $from_date,
            //'to_date' => $to_date,
            //'sms_type' => $sms_type,
            //'sent_by' => $sent_by,
            'phrase' => $phrase,
            'show_all' => $show_all
        );
        $pagi_link_params = '/sms/sms_missing_job/?'.http_build_query($pagi_links_params_arr);

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
        $this->load->view('sms/sms_unlinked', $data);
        $this->load->view('templates/inner_footer', $data);
    }

}
?>
