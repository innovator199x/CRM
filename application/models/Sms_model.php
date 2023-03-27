<?php

class Sms_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    public function getSmsTemplates($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`sms_api_type');

        // filter
        if (is_numeric($params['sms_api_type_id'])) {
            $this->db->where('sms_api_type_id', $params['sms_api_type_id']);
        }

        if (is_numeric($params['active'])) {
            if($params['active'] == -1){
                $this->db->where_in('active', [0,1]);
            } else {
                $this->db->where('active', $params['active']);
            }
        }
        
        // custom filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
        }

        //category filter (by gherx)
        if($params['category_filter'] && !empty($params['category_filter'])){
            $this->db->where('category', $params['category_filter']);
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
        if (isset($params['custom_sort'])) {
            $this->db->order_by($params['custom_sort']);
        }

        $query = $this->db->get();
        if (isset($params['display_query']) && $params['display_query'] == 1) {
            echo $this->db->last_query();
        }

        return $query;
    }

    // Wholesale SMS API
    // get SMS balance
    public function getBalance() {

        // init curl object  
        $ch = curl_init();

        //$url = "https://app.wholesalesms.com.au/api/v2/get-balance.json";

        $authorization = base64_encode("{$this->config->item('ws_sms_api_key')}:{$this->config->item('ws_sms_api_secret')}");
        $header = array("Authorization: Basic {$authorization}","Content-Type: application/x-www-form-urlencoded");

        $api_endpoint = "https://app.wholesalesms.com.au/api/v2/get-balance.json";

        // define options
		$optArray = array(
			CURLOPT_URL => $api_endpoint,
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => true
		);

        // apply those options
        curl_setopt_array($ch, $optArray);

        // execute request and get response
        $result = curl_exec($ch);
        curl_close($ch);

        $json_data = json_decode($result);

        return $json_data->balance;
    }

    // send SMS
    public function sendSMS($params) {

        // init curl object  
        $ch = curl_init();

        // parameters
        $sms_msg = trim($params['sms_msg']);
        $to_phone = trim($params['mobile']);
        $country_id = $this->config->item('country');
        $yabbr_switch = trim($this->config->item('yabbr_switch'));
        if($country_id == 1 && $yabbr_switch == 1){ //AU
            $api_key = trim($this->config->item('yabbr_sms_api_key'));
            
            $header = array("x-api-key:$api_key","content-type: application/json");
            $api_endpoint = "https://api.yabbr.io/2019-01-23/messages";

            $data = array(
                'to' => $to_phone,
                'from' => $this->config->item('yabbr_virtual_number'), ## When updating please update CRM OLD also and most important update the Yabbr Virtual Numbers SMS Forwarding
                'content' => "$sms_msg",
                'type' => 'sms'
            );

            $payload = json_encode($data);

        } else {
            $reply_url = trim($this->config->item('ws_sms_reply_url'));
            $dlr_url = trim($this->config->item('ws_sms_dlvr_url'));
    
            $authorization = base64_encode("{$this->config->item('ws_sms_api_key')}:{$this->config->item('ws_sms_api_secret')}");
            $header = array("Authorization: Basic {$authorization}","Content-Type: application/x-www-form-urlencoded");
    
            $api_endpoint = "https://app.wholesalesms.com.au/api/v2/send-sms.json";
        
            $data = array(
                'message' => $sms_msg,
                'to' => $to_phone,
                'reply_callback' => $reply_url,
                'dlr_callback' => $dlr_url			
            );
            
            $payload = http_build_query($data);
        }

        // define options
		$optArray = array(
			CURLOPT_URL => $api_endpoint,
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => true,			
			CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $payload
		);

        // apply those options
        curl_setopt_array($ch, $optArray);

        // execute request and get response
        $result = curl_exec($ch);
        curl_close($ch);

        $json_dec = json_decode($result);

        return $json_dec;
    }

    // format local number to international format
    public function formatToInternationNumber($mobile) {

        // trim
        $trim = str_replace(' ', '', trim($mobile));
        // remove 0 infront
        $remove_zero = substr($trim, 1);
        // get phone prefix
        $prefix = $this->config->item('country_code');

        // reformat number, international format
        return $prefix . $remove_zero;
    }

    public function captureSmsData($params) {
        $country_id = $this->config->item('country');

        $yabbr_switch = trim($this->config->item('yabbr_switch'));
        if($country_id == 1 && $yabbr_switch == 1){ //AU
            $message = $params['message'];
            $sms_count = strlen($message);
            $sms_cost = ceil(strlen($message)/160);
    
            $data = array(
                'job_id' => $params['job_id'],
                //'message_id' => $params['message_id'],
                //'message_id' => $params['sms_json']->messages[0]->id,
                'message' => $message,
                'mobile' => $params['mobile'],
                'send_at' => $params['sms_json']->created,
                'sent_by' => $params['sent_by'],
                'sms_type' => $params['sms_type'],
                'sms' => $sms_count,
                'cost' => $sms_cost,
                'recipients' => 1,
                'delivery_stats_pending' => 1,
                'cb_status' => 'pending',
                'error_code' => $params['sms_json']->error->code,
                'error_desc' => $params['sms_json']->error->description,
                'created_date' => date('Y-m-d H:i:s')
            );
        } else {
            $data = array(
                'job_id' => $params['job_id'],
                'message_id' => $params['sms_json']->message_id,
                'message' => $params['message'],
                'mobile' => $params['mobile'],
                'send_at' => $params['sms_json']->send_at,
                'sent_by' => $params['sent_by'],
                'sms_type' => $params['sms_type'],
                'recipients' => $params['sms_json']->recipients,
                'sms' => $params['sms_json']->recipients,
                'cost' => $params['sms_json']->cost,
                'delivery_stats_delivered' => $params['sms_json']->delivery_stats->delivered,
                'delivery_stats_bounced' => $params['sms_json']->delivery_stats->bounced,
                'delivery_stats_responses' => $params['sms_json']->delivery_stats->responses,
                'delivery_stats_pending' => $params['sms_json']->delivery_stats->pending,
                'delivery_stats_optouts' => $params['sms_json']->delivery_stats->optouts,
                'error_code' => $params['sms_json']->error->code,
                'error_desc' => $params['sms_json']->error->description,
                'created_date' => date('Y-m-d H:i:s')
            );
        }
        $this->db->insert('sms_api_sent', $data);
    }

    function parseTags($params) {

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;

        if( $params['job_id'] > 0 ){

            // job data
            $sel_query = "
                j.`id` AS jid,
                j.`service` AS j_service,
                j.`date` AS j_date,
                j.`time_of_day`,
                j.`booked_with`,
                
                p.`property_id` AS prop_id, 
                p.`address_1` AS p_address_1, 
                p.`address_2` AS p_address_2, 
                p.`address_3` AS p_address_3,
                p.`state` AS p_state,
                p.`postcode` AS p_postcode,
                
                a.`agency_id`,
                a.`agency_name` AS agency_name,
                a.`franchise_groups_id`,
            
                c.`tenant_number`,

                ajt.`id` AS ajt_id,
                ajt.`type` AS ajt_type,
                ajt.`full_name` AS service_full_name               
            ";
            $jobs_params = array(
                'sel_query' => $sel_query,
                'del_job' => 0,
                'p_deleted' => 0,
                'a_status' => 'active',            
                'country_id' => $country_id,
                'job_id' => $params['job_id'],
                'join_table' => array('alarm_job_type', 'countries'),            
                'display_query' => 0
            );

            $sql = $this->jobs_model->get_jobs($jobs_params);
            $row = $sql->row();

            // data
            $p_address = "{$row->p_address_1} {$row->p_address_2} {$row->p_address_3} {$row->p_state} {$row->p_postcode}";
            $job_date = ( $this->system_model->isDateNotEmpty($row->j_date) ) ? date('d/m/Y', strtotime($row->j_date)) : null;
            $time_of_day = $row->time_of_day;
            $service = $row->service_full_name;
            $tenant_number = $row->tenant_number;
            $booked_with = $row->booked_with;
            $tenant_name = $params['tenant_firstname'];      

            // private FG
            if ($this->system_model->getAgencyPrivateFranchiseGroups($row->franchise_groups_id) == true) {
                $agency_name = 'your agency';
                $your_agency = 'your landlord';
            } else {
                $agency_name = $row->agency_name;
                $your_agency = 'your agency';
            }

            // EN
            if (is_numeric(strpos($params['unparsed_template'], '{en_link}'))) {

                $encrypt = rawurlencode($this->encryption_model->encrypt($row->jid));
                $baseUrl = $_SERVER["SERVER_NAME"];
                if(isset($_SERVER['HTTPS'])){
                    $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
                } else{
                    $protocol = 'http';
                }

                $en_link = "{$protocol}://{$baseUrl}/pdf/entry_notice/?job_id={$encrypt}";

            }

        } 
        
        $sats_domain = "https://www.{$this->config->item('sats_domain')}";
        $link_upgrade_to_sell = "{$sats_domain}/upgrade-to-sell/";
        $sats_google_review = "https://bit.ly/3G8PbXM";

        $find = array(
            '{agency_name}', '{tenant_name}', '{p_address}', '{job_date}', '{time_of_day}', '{serv_name}', '{tenant_number}', '{en_link}', '{booked_with}', 
            '{sats_domain}', '{link_upgrade_to_sell}','{sats_google_review}'
        );
        
        $replace = array(
            $agency_name, $tenant_name, $p_address, $job_date, $time_of_day, $service, $tenant_number, $en_link, $booked_with, 
            $sats_domain, $link_upgrade_to_sell,$sats_google_review
        );

        return str_replace($find, $replace, $params['unparsed_template']);
    }

    // get SMS replies
    function getSMSrepliesMergedData($params) {

        if ($params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct'] != "") {

            switch ($params['distinct']) {
                case 'sas.`sent_by`':
                    $sel_str = "DISTINCT sas.`sent_by`, sa.`StaffID`, sa.`FirstName`, sa.`LastName` ";
                    break;
            }
        } else {
            $sel_str = " 
				*, 
				sas.`created_date` AS sas_created_date,
				sar.`created_date` AS sar_created_date
			";
        }
        $this->db->select($sel_str);
        $this->db->from("`sms_api_sent` AS sas ");
        if ($params['sms_page'] == 'incoming') {
            $join_type = 'INNER';
        } else if ($params['sms_page'] == 'outgoing') {
            $join_type = 'LEFT';
        } else {
            $join_type = 'LEFT';
        }
        $this->db->join("`sms_api_replies` AS sar", "sas.`message_id` = sar.`message_id`", $join_type);
        $this->db->join("`jobs` AS j", "sas.`job_id` = j.`id`", "LEFT");
        $this->db->join("`property` AS p", "j.property_id=p.property_id", "LEFT");
        $this->db->join('agency as a', 'a.agency_id = p.agency_id', 'LEFT');
        $this->db->join("staff_accounts AS sa", "sas.sent_by=sa.StaffID", "LEFT");
        $this->db->join("sms_api_type sat", "sas.sms_type=sat.sms_api_type_id", "LEFT");
        $this->db->join("staff_accounts ass_tech", "j.assigned_tech=ass_tech.StaffID", "LEFT");
        $this->db->where("sas.active=1");
        if ($params['tech'] != "") {
            $this->db->where("j.`assigned_tech` = '{$params['tech']}'");
        }

        if ($params['cb_status'] != "") {
            $this->db->where("sas.`cb_status` = '{$params['cb_status']}'");
        }

        if ($params['sent_by'] != "") {
            $this->db->where("sas.`sent_by` = {$params['sent_by']}");
        }

        if ($params['sr_id'] != "") {
            $this->db->where("sar.`sms_api_replies_id` = {$params['sr_id']}");
        }

        if ($params['unread'] != "") {
            $this->db->where("sar.`unread` = 1");
        }

        if ($params['sms_type'] != "") {
            $this->db->where("sas.`sms_type` = {$params['sms_type']}");
        }

        // agency filter
        if ( $params['agency_filter'] > 0 ) {
            $this->db->where('a.agency_id', $params['agency_filter']);
        }

        //30 days
        if ($params['30_days'] && !empty($params['30_days'])) {
            $this->db->where('sas.created_date > NOW( ) - INTERVAL 30 DAY');
        }

        if ($params['filterDate'] != '') {
            if ($params['filterDate']['from'] != "" && $params['filterDate']['to'] != "") {
                $this->db->where("CAST(sar.`created_date` AS DATE) BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}'");
            }
        }
        
        if ($params['phrase_filter'] != "") {
            $this->db->where("sar.`response` LIKE '%{$params['phrase_filter']}%'");
            $this->db->or_where("sar.`mobile` LIKE '%{$params['phrase_filter']}%'");
        }

        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
            $this->db->where($custom_filter_str);
        }
        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $this->db->limit($params['paginate']['limit'], $params['paginate']['offset']);
            }
        }
        $query = $this->db->get();
        if ($params['echo_query'] == 1) {
            echo $this->db->last_query();
        }

        return $query;
    }

    public function getSMStype($sel_str) {
        $this->db->select($sel_str);
        $this->db->from("`sms_api_type` sat");
        $this->db->where("sat.active=1");
        $this->db->order_by("sat.type_name", "ASC");
        return $this->db->get();
    }

    public function getBalance_v2_jlcbada() {

        $balance = $this->getBalance();
        $today = date('Y-m-d H:i:s');
        $this->db->where("`country_id` = {$this->config->item('country')}");
        $this->db->update("`crm_settings`", [
            'sms_credit' => $balance,
            'sms_credit_update_ts' => $today
        ]);
        $affected_rows = $this->db->affected_rows();
        return $affected_rows;
    }

    public function toggle_sms_replies($sar_id, $param) {
        $this->db->where("`sms_api_replies_id` = {$sar_id}");
        $this->db->update("`sms_api_replies`", $param);
        $affected_rows = $this->db->affected_rows();
        return $affected_rows;
    }

    public function get_future_str($job_id) {
        $country_id = $this->config->item('country');
        $today = date('Y-m-d');


        $this->db->select("tr.tech_run_id,tr.`date` AS tr_date");
        $this->db->from("`tech_run_rows` AS trr");
        $this->db->join("`tech_run` AS tr", "trr.`tech_run_id` = tr.`tech_run_id`", "LEFT");
        $this->db->join("`staff_accounts` AS sa", "tr.`assigned_tech` = sa.`StaffID`", "LEFT");
        $this->db->join("`jobs` AS j", "j.`id` = trr.`row_id`", "LEFT");
        $this->db->join("`property` AS p", "j.`property_id` = p.`property_id`", "LEFT");
        $this->db->join("`agency` AS a", "p.`agency_id` = a.`agency_id` AND trr.`row_id_type` =  'job_id'", "LEFT");
        $this->db->where("j.`id` = {$job_id}");
        $this->db->where("tr.`date` >=  '{$today}'");
        $this->db->where("trr.`hidden` = 0");
        $this->db->where("j.`del_job` = 0");
        $this->db->where("tr.`country_id` = {$country_id}");
        $this->db->where("a.`country_id` = {$country_id}");
        return $this->db->get();
    }

    public function getSmsRepliesData($msg_id, $sar_id) {
        $this->db->select("*");
        $this->db->from("`sms_api_replies`");
        $this->db->where("`message_id` = '{$msg_id}'");
        $this->db->where("`sms_api_replies_id` = {$sar_id}");
        return $this->db->get();
    }

    public function saveJobLog($params) {
        $this->db->insert('job_log', $params);
    }

    //Server Side Datatable | Email Logs START
    function all_logs($title_id,$limit,$start,$col,$dir) {   
        $query = $this->db
            ->select('logs.details, logs.created_date, CONCAT(staff_accounts.FirstName, " ", staff_accounts.LastName) AS name')
            ->from('logs')
            ->join('staff_accounts', 'staff_accounts.StaffID=logs.created_by_staff')
            ->where('logs.title',$title_id)
            ->order_by($col,$dir)
            ->limit($limit,$start)
            ->get();
        
        if($query->num_rows()>0) {
            return $query->result(); 
        } else {
            return null;
        }
    }

    function all_logs_count($title_id) {   
        $query = $this->db
            ->select('logs.details, logs.created_date, CONCAT(staff_accounts.FirstName, " ", staff_accounts.LastName) AS name')
            ->from('logs')
            ->join('staff_accounts', 'staff_accounts.StaffID=logs.created_by_staff')
            ->where('logs.title',$title_id)
            ->get();
        
        return $query->num_rows();  
    }
    
    function logs_search($title_id,$limit,$start,$search,$col,$dir) {
        $query = $this->db
            ->select('logs.details, logs.created_date, CONCAT(staff_accounts.FirstName, " ", staff_accounts.LastName) AS name')
            ->from('logs')
            ->join('staff_accounts', 'staff_accounts.StaffID=logs.created_by_staff')
            ->where('logs.title',$title_id)
            ->group_start()
                ->like('logs.details',$search)
                ->or_like('staff_accounts.FirstName',$search)
                ->or_like('staff_accounts.LastName',$search)
                ->or_like('logs.created_date',$search)
            ->group_end()
            ->limit($limit,$start)
            ->order_by($col,$dir)
            ->get();
        if($query->num_rows()>0) {
            return $query->result();  
        } else {
            return null;
        }
    }
    
    function logs_search_count($title_id,$search) {
        $query = $this->db
            ->select('logs.details, logs.created_date, CONCAT(staff_accounts.FirstName, " ", staff_accounts.LastName) AS name')
            ->from('logs')
            ->join('staff_accounts', 'staff_accounts.StaffID=logs.created_by_staff')
            ->where('logs.title',$title_id)
            ->group_start()
                ->like('logs.details',$search)
                ->or_like('staff_accounts.FirstName',$search)
                ->or_like('staff_accounts.LastName',$search)
                ->or_like('logs.created_date',$search)
            ->group_end()
            ->limit($limit,$start)
            ->order_by($col,$dir)
            ->get();
        return $query->num_rows();
    } 
    //Server Side Datatable | Email Logs END

     // Validate Number
     public function validateNumber($number) {

        // init curl object  
        $ch = curl_init();

        // parameters
        $api_key = trim($this->config->item('yabbr_sms_api_key'));
        $authorization = base64_encode($api_key);
        $header = array("x-api-key:$api_key", "content-type:application/json");
        $api_endpoint = "https://api.yabbr.io/2019-01-23/validations";
        $data = array('number' => $number);
        
        // define options
		$optArray = array(
			CURLOPT_URL => $api_endpoint,
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => true,			
			CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => json_encode($data)
		);

        // apply those options
        curl_setopt_array($ch, $optArray);

        // execute request and get response
        $result = curl_exec($ch);
        return json_decode($result);
        curl_close($ch);
    }

    //New SMS API
    public function update_status($params) {
        $mobile = '+' . $params['mobile'];
        $date = date('Y-m-d H:i:s');
        $update_status = $this->db->where('mobile',$mobile)
            ->where('message_id', null)
            ->update("`sms_api_sent`", [
                'cb_mobile' => $params['mobile'],
                'cb_datetime' => $params['datetime'],
                'cb_status' => $params['status'],
                'delivery_stats_delivered' => $params['is_delivered'],
                'delivery_stats_bounced' => $params['is_rejected'],
                'delivery_stats_pending' => 0,
                'updated_at' => $date
            ]);
    }

    public function saveReply($params) {
        $message_id = $params['message_id'];
        $mob_num = $params['mobile'];
        /*$sent_id = implode(",", $this->newsms_model->getSent_id($mob_num));

        $custom_date = date('d/m H:i');
        
        if(!empty($sent_id)){
            $update_status = $this->db->where('sms_api_sent_id', $sent_id)
                ->where('message_id', null)
                ->where('cb_status', 'delivered')
                ->update('sms_api_sent', ['message_id' => $message_id]);
        } else {
            $message_id = implode(",", $this->newsms_model->getLastReplyID($mob_num));
            $params['message_id'] = $message_id;
        }*/

        //$sent_id =  $this->sms_model->getSent_id_v2($mob_num);
        $sent_id =  $this->getSent_id_v2($mob_num);

        if( !empty($sent_id) ){

            $sent_id = $sent_id['sms_api_sent_id'];
            $this->db->where('sms_api_sent_id', $sent_id);
            $this->db->where('message_id', null);
            $this->db->where('cb_status', 'delivered');
            $this->db->update('sms_api_sent', ['message_id' => $message_id]);

        }else{
            //$getLastReplyID = $this->sms->getLastReplyID($mob_num);  ##disabled > might caused error 
            $getLastReplyID = $this->getLastReplyID($mob_num);
            $message_id = $getLastReplyID['message_id'];
            $params['message_id'] = $message_id;
        }

        $this->db->insert('sms_api_replies', $params);

        // get SMS other infos on sms_api_sent table
        $sms_sent = $this->db->select('sas.sent_by, a.country_id, sas.sms_type')
            ->from('sms_api_sent AS sas')
            ->join('jobs AS j', 'sas.job_id = j.id', 'left')
            ->join('property AS p', 'j.property_id = p.property_id', 'left')
            ->join('agency AS a', 'p.agency_id = a.agency_id', 'left')
            ->where('sas.message_id', $message_id)
            ->get();
        
        $sent_row = $sms_sent->row_array();
	    $notify_staff = $sent_row['sent_by'];
	    $country_id = $this->config->item('country');
        // set SMS notification	
	
        $crm_ci_page = 'sms/view_incoming_sms';
        $notf_msg = "New <a href=\"{$crm_ci_page}\">SMS</a> from {$mob_num} " . $custom_date;

        $notf_type = 2; // SMS notification
        $jparams = array(
            'notf_type'=> $notf_type,
            'notf_msg'=> $notf_msg,
            'staff_id'=> $notify_staff,
            'country_id'=> $country_id		
        );
        $this->gherxlib->insertNewNotification($jparams);

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
        $ch = "ch".$notify_staff;
        $ev = "ev01";
        $out = $pusher->trigger($ch, $ev, $pusher_data);

        // If No Show
        if( $sent_row['sms_type'] == 4 ){
            
            $cust_serv_pips_arr = [];
            // customer service people
            if( $country_id == 1 ){ // AU
            
                /*
                AU:
                2175 - Thalia
                2058 - Jemma
                2191 - Ashlee R
                2209 - Hine
                */
                if(ENVIRONMENT=="production"){ //live
                    $cust_serv_pips_arr = array(2175,2058,2191,2209);
                }else{
                    $cust_serv_pips_arr = array(2070,11);
                }
                
            }else if( $country_id == 2 ){ // NZ
                
                /*
                NZ:
                2147 - Tiana
                2124 - Ashley O
                */
                if(ENVIRONMENT=="production"){ //live
                    $cust_serv_pips_arr = array(2147,2124);
                }else{
                    $cust_serv_pips_arr = array(2070,11);
                }
            }
            
            
            foreach( $cust_serv_pips_arr as $cust_serv_pips ){
                
                $notf_type = 3; // SMS No Show notificatio	
                $crm_ci_page = 'sms/view_incoming_sms';
                $notf_msg = "New <a href=\"{$crm_ci_page}\">SMS</a> from {$mob_num} " . $custom_date; 

                $jparams = array(
                    'notf_type'=> $notf_type,
                    'notf_msg'=> $notf_msg,
                    'staff_id'=> $cust_serv_pips,
                    'country_id'=> $country_id		
                );
                $this->gherxlib->insertNewNotification($jparams);

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
                $ch = "ch".$notify_staff;
                $ev = "ev01";
                $out = $pusher->trigger($ch, $ev, $pusher_data);
            }	
        }
    }


    public function getSent_id($number){
        $query = $this->db
            ->select('sms_api_sent_id')
            ->from('sms_api_sent')
            ->where('mobile','+' . $number)
            ->where('message_id',null)
            ->order_by('sms_api_sent_id','ASC')
            ->limit(1)
            ->get();

        if($query->num_rows()>0) {
            return $query->row_array(); 
        } else {
            return null;
        }
    }

    public function getMessageIdNotNull($number){
        $query = $this->db
            ->select('message_id')
            ->from('sms_api_sent')
            ->where('mobile','+' . $number)
            ->where('message_id !=',null)
            ->order_by('sms_api_sent_id','ASC')
            ->limit(1)
            ->get();

        if($query->num_rows()>0) {
            return $query->row_array(); 
        } else {
            return null;
        }
    }

    public function getLastReplyID($number){
        $query = $this->db
            ->select('message_id')
            ->from('sms_api_replies')
           // ->where('mobile','+' . $number)
            ->where('mobile',$number)
            ->order_by('sms_api_replies_id','DESC')
            ->limit(1)
            ->get();

        if($query->num_rows()>0) {
            return $query->row_array(); 
        } else {
            return null;
        }
    }

    public function getSent_id_v2($number){
        $query = $this->db
            ->select('sms_api_sent_id')
            ->from('sms_api_sent')
            ->where('mobile','+' . $number)
            ->where('message_id',null)
            ->order_by('sms_api_sent_id','DESC')
            ->limit(1)
            ->get();

        if($query->num_rows()>0) {
            return $query->row_array(); 
        } else {
            return null;
        }
    }

    /**
     * This is Yabbr SMS test only > DO not use
     */
    public function sendSMS_test_yabbr($params) {

        // init curl object  
        $ch = curl_init();

        // parameters
        $sms_msg = trim($params['sms_msg']);
        $to_phone = trim($params['mobile']);
        $country_id = $this->config->item('country');
        $yabbr_switch = trim($this->config->item('yabbr_switch'));

        $api_key = trim($this->config->item('yabbr_sms_api_key'));
            
        $header = array("x-api-key:$api_key","content-type: application/json");
        $api_endpoint = "https://api.yabbr.io/2019-01-23/messages";

        $data = array(
            'to' => $to_phone,
            'from' => '61485817467', ## When updating please update CRM OLD also and most important update the Yabbr Virtual Numbers SMS Forwarding
            'content' => "$sms_msg",
            'type' => 'sms'
        );

        $payload = json_encode($data);

        // define options
		$optArray = array(
			CURLOPT_URL => $api_endpoint,
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => true,			
			CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $payload
		);

        // apply those options
        curl_setopt_array($ch, $optArray);

        // execute request and get response
        $result = curl_exec($ch);
        curl_close($ch);

        $json_dec = json_decode($result);

        return $json_dec;
    }

    public function get_sms_category($id)
    {
        $excluded_id = array();

        $this->db->select("sms_api_type_id, type_name, category, body, active");
        $this->db->from("sms_api_type");
        $this->db->where("active", 1);

        if ($id > 0) {
            // Exclude sms_api_type based on country_id
            if ($this->config->item('country') == 1) { // AU
                $excluded_id = array(27,28);
            } else if ($this->config->item('country') == 2) { // NZ
                $excluded_id = array(2,3);
            }
            $exclude_default = array(24, 18);
            $exclude_arr_merge = array_merge($exclude_default, $excluded_id);

            $this->db->where_not_in("sms_api_type_id", $exclude_arr_merge);
            $this->db->where("category IS NOT NULL");
            $this->db->where("category <> ''");
            
        } else {
            $this->db->where("category", "Agency Sales SMS");
        }

        $this->db->order_by("type_name", "asc");
        $this->db->group_by("category", "asc");

        return $this->db->get()->result();
    }

    public function get_sms_template_by_category_name($category_name)
    {
        $excluded_id = array();

        $this->db->select("sms_api_type_id, type_name, category, body, active");
        $this->db->from("sms_api_type");
        $this->db->where("active", 1);

        if (!empty($category_name)) {
            
            // Exclude sms_api_type based on country_id
            if ($this->config->item('country') == 1) { // AU
                $excluded_id = array(27,28);
            } else if ($this->config->item('country') == 2) { // NZ
                $excluded_id = array(2,3);
            }

            $exclude_default = array(24, 18);
            $exclude_arr_merge = array_merge($exclude_default, $excluded_id);

            $this->db->where_not_in("sms_api_type_id", $exclude_arr_merge);
            $this->db->where("category", $category_name);
        }

        return $this->db->get()->result();
    }

    public function get_sms_template_by_default()
    {
        $excluded_id = array();

        $this->db->select("sms_api_type_id, type_name, category, body, active");
        $this->db->from("sms_api_type");
        $this->db->where("active", 1);
            
        // Exclude sms_api_type based on country_id
        if ($this->config->item('country') == 1) { // AU
            $excluded_id = array(27,28);
        } else if ($this->config->item('country') == 2) { // NZ
            $excluded_id = array(2,3);
        }

        $exclude_default = array(24, 18);
        $exclude_arr_merge = array_merge($exclude_default, $excluded_id);

        $this->db->where_not_in("sms_api_type_id", $exclude_arr_merge);
        $this->db->order_by("type_name", "asc");

        return $this->db->get()->result();
    }

     // get SMS replies
     function getSMSrepliesMergedData_v2($params) {

        if ($params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct'] != "") {

            switch ($params['distinct']) {
                case 'sas.`sent_by`':
                    $sel_str = "DISTINCT sas.`sent_by`, sa.`StaffID`, sa.`FirstName`, sa.`LastName` ";
                    break;
            }
        } else {
            $sel_str = " 
				*, 
				sas.`created_date` AS sas_created_date,
				sar.`created_date` AS sar_created_date
			";
        }
        $this->db->select($sel_str);
        $this->db->from("`sms_api_sent` AS sas ");
        if ($params['sms_page'] == 'incoming') {
            $join_type = 'INNER';
        } else if ($params['sms_page'] == 'outgoing') {
            $join_type = 'LEFT';
        } else {
            $join_type = 'LEFT';
        }
        $this->db->join("`sms_api_replies` AS sar", "sas.`message_id` = sar.`message_id` OR sar.message_id IS NULL", $join_type);
        $this->db->join("`jobs` AS j", "sas.`job_id` = j.`id`", "LEFT");
        $this->db->join("`property` AS p", "j.property_id=p.property_id", "LEFT");
        $this->db->join('agency as a', 'a.agency_id = p.agency_id', 'LEFT');
        $this->db->join("staff_accounts AS sa", "sas.sent_by=sa.StaffID", "LEFT");
        $this->db->join("sms_api_type sat", "sas.sms_type=sat.sms_api_type_id", "LEFT");
        $this->db->join("staff_accounts ass_tech", "j.assigned_tech=ass_tech.StaffID", "LEFT");
        $this->db->where("sas.active=1");
        if ($params['tech'] != "") {
            $this->db->where("j.`assigned_tech` = '{$params['tech']}'");
        }

        if ($params['cb_status'] != "") {
            $this->db->where("sas.`cb_status` = '{$params['cb_status']}'");
        }

        if ($params['sent_by'] != "") {
            $this->db->where("sas.`sent_by` = {$params['sent_by']}");
        }

        if ($params['sr_id'] != "") {
            $this->db->where("sar.`sms_api_replies_id` = {$params['sr_id']}");
        }

        if ($params['unread'] != "") {
            $this->db->where("sar.`unread` = 1");
        }

        if ($params['sms_type'] != "") {
            $this->db->where("sas.`sms_type` = {$params['sms_type']}");
        }

        // agency filter
        if ( $params['agency_filter'] > 0 ) {
            $this->db->where('a.agency_id', $params['agency_filter']);
        }

        //30 days
        if ($params['30_days'] && !empty($params['30_days'])) {
            $this->db->where('sas.created_date > NOW( ) - INTERVAL 30 DAY');
        }

        if ($params['filterDate'] != '') {
            if ($params['filterDate']['from'] != "" && $params['filterDate']['to'] != "") {
                $this->db->where("CAST(sar.`created_date` AS DATE) BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}'");
            }
        }
        
        if ($params['phrase_filter'] != "") {
            $this->db->where("sar.`response` LIKE '%{$params['phrase_filter']}%'");
            $this->db->or_where("sar.`mobile` LIKE '%{$params['phrase_filter']}%'");
        }

        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
            $this->db->where($custom_filter_str);
        }


        // group by
        if (isset($params['group_by']) && $params['group_by'] != '') {
            $this->db->group_by($params['group_by']);
        }

        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $this->db->limit($params['paginate']['limit'], $params['paginate']['offset']);
            }
        }
        $query = $this->db->get();
        if ($params['echo_query'] == 1) {
            echo $this->db->last_query();
        }

        return $query;
    }

    public function get_sms_missing_job($params){
        
        $this->db->select('*');
        $this->db->from('sms_api_replies');

        if( !$params['show_all'] AND $params['show_all']!=1 ){
            $this->db->where('unread',1);
        }

        $this->db->where('active',1);
        $this->db->where('message_id',NULL);

        if ($params['phrase'] != "") {
            $this->db->group_start();
            $this->db->where("response LIKE '%{$params['phrase']}%'");
            $this->db->or_where("mobile LIKE '%{$params['phrase']}%'");
            $this->db->group_end();
        }

        $this->db->order_by('created_date','DESC');
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $this->db->limit($params['paginate']['limit'], $params['paginate']['offset']);
            }
        }
     
        return $this->db->get();

    }

}
