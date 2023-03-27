<?php

class Gherxlib {

    // We'll use a constructor, as you can't directly call a function
    // from a property definition.
    protected $CI;

    public function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->database();
    }

    /**
     * FORMAT AGENCY/STAFF FULL NAME
     * return staff/agency abbv name
     */
    function formatStaffName($fname, $lname) {
        return "{$fname}" . ( ($lname != "") ? ' ' . strtoupper(substr($lname, 0, 1)) . '.' : '' );
    }

    /**
     * Get Global Setting
     * $params country_id
     * return query
     */
    function getGlobalSettings($params) {
        $this->CI->db->select('*');
        $this->CI->db->from('global_settings as gs');
        $this->CI->db->join('staff_accounts sa', 'sa.StaffID = gs.allocate_personnel');
        $this->CI->db->where('gs.active', 1);
        $this->CI->db->where('gs.deleted', 0);

        if ($params['country_id'] != "") {
            $this->CI->db->where('country_id', $params['country_id']);
        }

        $query = $this->CI->db->get();
        return $query;
    }

    /**
     * GET STAFF INFO
     * @params staff_id for row
     * return query
     */
    function getStaffInfo($params) {
        if ($params['sel_query'] && $params['sel_query'] != "") {
            $this->CI->db->select($params['sel_query']);
        } else {
            $this->CI->db->select('*');
        }

        $this->CI->db->from('staff_accounts as sa');
        $this->CI->db->join('country_access ca', 'ca.staff_accounts_id = sa.StaffID', 'INNER');

        // custom joins
        if (isset($params['custom_joins']) && $params['custom_joins'] != '') {
            $this->CI->db->join($params['custom_joins']['join_table'], $params['custom_joins']['join_on'], $params['custom_joins']['join_type']);
        }

        $this->CI->db->where('sa.active', 1);
        $this->CI->db->where('sa.Deleted', 0);
        $this->CI->db->where('ca.country_id', $this->CI->config->item('country'));

        //staff_id
        if ($params['staff_id'] != "") {
            $this->CI->db->where('sa.StaffID', $params['staff_id']);
        }

        // sort
        if (isset($params['sort_list'])) {
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->CI->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }

        $query = $this->CI->db->get();
        return $query;
    }

    /**
     * GET AGENCY INFO
     * $param agency_id
     * return row
     */
    function agency_info($agency_id) {
        $query = $this->CI->db->get_where('agency', array('agency_id' => $agency_id));
        return ($query->num_rows() > 0) ? $query->row() : false;
    }

    /**
     * Get Escalate Job Reason by JOB ID
     * @params job_id
     * return query
     */
    function getEscalateReason($job_id) {
        $this->CI->db->select('*');
        $this->CI->db->from('selected_escalate_job_reasons sejr');
        $this->CI->db->join('escalate_job_reasons ejr', 'ejr.escalate_job_reasons_id = sejr.escalate_job_reasons_id', 'left');
        $this->CI->db->where('sejr.deleted', 0);
        $this->CI->db->where('sejr.active', 1);
        $this->CI->db->where('sejr.job_id', $job_id);
        $query = $this->CI->db->get();
        return $query;
    }

    function getEscalateAgencyInfo($params) {
        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }
        $this->CI->db->select($sel_query);
        $this->CI->db->from('escalate_agency_info as eai');

        // country id filter
        if (is_numeric($params['country_id'])) {
            $this->CI->db->where('eai.`country_id`', $params['country_id']);
        }

        // agency filters
        if (isset($params['agency_filter']) && $params['agency_filter'] != '') {
            $this->CI->db->where('eai.`agency_id`', $params['agency_filter']);
        }

        // Date filters
        if (isset($params['date']) && $params['date'] != '') {
            $this->CI->db->where("DATE_FORMAT(eai.date_created,'%Y-%m-%d')", $params['date']);
            //$this->CI->db->where("CAST( eai.`date_created` AS Date ) = '{$params['date']}'");
        }

        $this->CI->db->where('eai.active', 1);
        $this->CI->db->where('eai.deleted', 0);

        $query = $this->CI->db->get();
        return $query;
    }

    /**
     * GET COUNTRY INFO
     * @param $country - country_id
     * return row
     */
    function getCountryViaCountryId($country) {
        $query = $this->CI->db->get_where('countries', array('country_id' => $country));
        return $query->row();
    }

    /**
     * GET REGION LABEL/NAME
     * @param $country - country_id
     * return region/district name (District/Region)
     */
    function getDynamicRegion($country) {
        // NZ
        if ($country == 2) {
            $region_str = 'District';
        } else {
            $region_str = 'Region';
        }
        return $region_str;
    }

    /**
     * GET STATE LABEL/NAME
     * $param $country - country_id
     * return state/region name (Region/State)
     */
    function getDynamicState($country) {
        // NZ
        if ($country == 2) {
            $state_str = 'Region';
        } else {
            $state_str = 'State';
        }
        return $state_str;
    }

    /**
     * GET AGE for BNE DATE AGE
     * @param $d1 - date
     * return age
     */
    function getAge($d1, $d2=null) {
        if( $d2!=NULL ){
            $date1 = date_create(date('Y-m-d', strtotime($d1)));
            $date2 = date_create(date('Y-m-d', strtotime($d2)));
            $diff = date_diff($date1, $date2);
            $age = $diff->format("%r%a");
            $age_val = (((int) $age) != 0) ? $age : 0;
        }else{
            $date1 = date_create(date('Y-m-d', strtotime($d1)));
            $date2 = date_create(date('Y-m-d'));
            $diff = date_diff($date1, $date2);
            $age = $diff->format("%r%a");
            $age_val = (((int) $age) != 0) ? $age : 0;
        }
       
        return $age_val;
    }

    /**
     * Get abbr
     */
    function getJobTypeAbbrv($jt) {

        // job type
        switch ($jt) {
            case 'Once-off':
                $jt = 'Once-off';
                break;
            case 'Change of Tenancy':
                $jt = 'COT';
                break;
            case 'Yearly Maintenance':
                $jt = 'YM';
                break;
            case 'Fix or Replace':
                $jt = 'FR';
                break;
            case '240v Rebook':
                $jt = '240v';
                break;
            case 'Lease Renewal':
                $jt = 'LR';
                break;
            case 'Annual Visit':
                $jt = 'Annual';
        }
        return $jt;
    }

    /**
     * Get Last Contact
     * @param job_id
     * return query
     */
    function getLastContact($job_id) {
        $query = $this->CI->db->select('eventdate')->from('job_log')->where('job_id', $job_id)->order_by('eventdate', 'DESC')->limit(1)->get();
        return $query;
    }

    /**
     * Get BNE to Call count for bubble
     * return num_rows
     */
    function getBneCount() {
        $custom_where = "p.`bne_to_call` = 1 AND j.status NOT IN('Completed','Cancelled','Merged Certificates','Booked','Pre Completion')";
        $sel_query = "j.`id` ";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'custom_where' => $custom_where,
        );
        $query = $this->CI->jobs_model->get_jobs($params);
        return ($query->num_rows() > 0) ? $query->num_rows() : false;
    }

    /**
     * Get Allocate total count for buble
     * return num_rows
     */
    function getAllocateCount() {
        $country_id = $this->CI->config->item('country');
        $job_status = 'Allocate';
        $sel_query = "j.`id` ";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
        );
        $query = $this->CI->jobs_model->get_jobs($params);
        return ($query->num_rows() > 0) ? $query->num_rows() : false;
    }

    /**
     * Get DHA total count for bubble
     * return num_rows
     */
    function getDHACount() {
        $country_id = $this->CI->config->item('country');
        $job_status = 'DHA';
        $sel_query = "j.`id` ";
        $params = array(
            'sel_query' => $sel_query,
            'p_deleted' => 0,
            'a_status' => 'active',
            'del_job' => 0,
            'country_id' => $country_id,
            'job_status' => $job_status,
        );
        $query = $this->CI->jobs_model->get_jobs($params);
        return ($query->num_rows() > 0) ? $query->num_rows() : false;
    }

    /**
     * GET Allocated by Name (FOR ALLOCATED)
     * @param Staff_id
     * return staff fname and lname
     */
    function getAllocatedBy($staff_id) {
        $query = $this->CI->db->get_where('staff_accounts', array('StaffID' => $staff_id), $limit, $offset);
        $row = $query->row_array();
        return "{$row['FirstName']}" . ( ($row['LastName'] != "") ? ' ' . strtoupper(substr($row['LastName'], 0, 1)) . '.' : '' );
    }

    /**
     * Allocated Deadline
     */
    function getAllocateDeadLine($all_opt, $all_ts) {

        if ($all_opt == 1 || $all_opt == 2) {

            if ($all_opt == 1) { // 2 hours
                $append_hour = 2;
            } else if ($all_opt == 2) { // 4 hours
                $append_hour = 4;
            }

            $deadline = date('Y-m-d H:i:s', strtotime($all_ts . " +{$append_hour} hours"));
        } else if ($all_opt == 3) {
            $deadline = date('Y-m-d 18:00:00');
        }

        return $deadline;
    }

    /**
     * Insert Notifications
     */
    function insertNewNotification($param) {

        // pass notification type, default is 1, general notification
        $notf_type = ( $param['notf_type'] != '' ) ? $param['notf_type'] : 1;

        $data = array(
            'notification_message' => $param['notf_msg'],
            'notify_to' => $param['staff_id'],
            'notf_type' => $notf_type,
            'country_id' => $param['country_id'],
        );
        $this->CI->db->insert('notifications', $data);

        if ($this->CI->db->affected_rows() > 0) {
            $dataUpdate = array('sound_notification' => 1);
            $this->CI->db->where('StaffID', $param['staff_id']);
            $this->CI->db->update('staff_accounts', $dataUpdate);
            $this->CI->db->limit(1);
        }
    }
    

    /**
     * GET who created Send Letters (For Added by in Send Letters)
     * @param property_id
     * return who created send letters
     */
    function getWhoCreatedSendLetters($property_id) {
        $query = $this->CI->db->get_where('property_event_log', array('property_id' => $property_id), $limit, $offset);
        $row = $query->row_array();
        if ($row['log_agency_id'] != "") {
            $who = 'AGENCY';
        } else if ($row['staff_id'] != 0) {
            $who = 'SATS';
        } else {
            $who = 'AGENCY';
        }

        return $who;
    }

    /**
     * GET who maximum tenant (For Send Letters)
     * @param N/A
     * return current maximum tenant
     */
    function getCurrentMaxTenants() {
        $num_tenants = 4;
        return $num_tenants;
    }

    /**
     * GET new tenant data (For Send Letters)
     * @param property_id, active
     * return new tenant data
     */
    function getNewTenantsData($params) {
        $query = $this->CI->db->get_where('property_tenants', array(
            'property_id' => $params['property_id'],
            'active' => $params['active'],
            'property_tenant_id >' => 0), $params['limit'], $params['offset']);
        $row = $query->result();
        return $row;
    }

    /**
     * GET email status (For Send Letters)
     * @param N/A
     * return new tenant data
     */
    function getCrnSetting($country_default) {
        $query = $this->CI->db->select('cron_send_letters')
                ->get_where('crm_settings', array('country_id' => $country_default));
        $row = $query->result();
        return $row;
    }

    function isDHAagencies($agency_id) {
        $dha_agencies = array(
            3043,
            3036,
            3046,
            1902,
            3044,
            1906,
            1927,
            3045
        );
        if (in_array($agency_id, $dha_agencies)) {
            return true;
        } else {
            return false;
        }
    }

    // get country data
    function get_country_data() {

        $country_id = $this->CI->config->item('country');
        // get country data
        $c_params = array('country_id' => $country_id);
        return $this->CI->system_model->get_countries($c_params);
    }

    // compute check digit
    function getCheckDigit($number) {

        $sumTable = array(array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9), array(0, 2, 4, 6, 8, 1, 3, 5, 7, 9));
        $length = strlen($number);
        $sum = 0;
        $flip = 1;
        // Sum digits (last one is check digit, which is not in parameter)
        for ($i = $length - 1; $i >= 0; --$i)
            $sum += $sumTable[$flip++ & 0x1][$number[$i]];
        // Multiply by 9
        $sum *= 9;

        return (int) substr($sum, -1, 1);
    }

    function crmLink($type, $id, $content, $target=NULL, $ht=NULL) {

        $targeton = ($target!="")?" target={$target} ":NULL;

        if ($type == "vpd") {

            return '<a href="' . $this->CI->config->item("crm_link") . '/view_property_details.php?id=' . $id . '">' . $content . '</a>';
        } elseif ($type == "vjd") {

            return '<a'. $targeton .' href="' . $this->CI->config->item("crm_link") . '/view_job_details.php?id=' . $id . '">' . $content . '</a>';
        } elseif ($type == 'vad') {
            $htText = "";
            $thBoldClass = "";
            if($ht==1){
                $htText = "(HT)";
                $thBoldClass = "j_bold";
            }
            if($ht==2){
                $htText = "(VIP)";
                $thBoldClass = "j_bold";
            }
            if($ht==3){
                $htText = "(HWC)";
                $thBoldClass = "j_bold";
            }
            //return '<a target="_blank" href="' . $this->CI->config->item("crm_link") . '/view_agency_details.php?id=' . $id . '">' . $content . '</a>';
            return '<a class="'.$thBoldClass.'" target="_blank" href="/agency/view_agency_details/' . $id . '">' . $content .' '.$htText. '</a>';
        } elseif ($type == 'tools') {

            return '<a href="' . $this->CI->config->item("crm_link") . '/view_tool_details.php?id=' . $id . '">' . $content . '</a>';
        } elseif ($type == 'vehicle') {

            return '<a href="' . $this->CI->config->item("crm_link") . '/view_vehicle_details.php?id=' . $id . '">' . $content . '</a>';
        } elseif ($type == 'run_sheet_admin') {

            return '<a href="' . $this->CI->config->item("crm_link") . '/run_sheet_admin.php?tr_id=' . $id . '">' . $content . '</a>';
        } elseif ($type == 'view_job_details_tech') {

            return '<a href="' . $this->CI->config->item("crm_link") . '/view_job_details_tech.php?id=' . $id . '">' . $content . '</a>';
        } elseif ($type == 'view_combined') {
            
            return '<a href="' . $this->CI->config->item("crm_link") . '/view_combined.php?id=' . $id . '">' . $content . '</a>';
        } elseif ($type === 'uploads_expenses') {
            
            return '<a target="_blank" href="' . $this->CI->config->item("crm_link") . '/' . $id . '">' . $content . '</a>';
        } elseif ($type === 'expense_details') {
            
            return '<a href="' . $this->CI->config->item("crm_link") . '/expense_details.php?id=' . $id . '">' . $content . '</a>';
        }elseif ($type === 'old_crm_task_ss') {
            
            return '<a class="fancybox-uploaded-screenshot" href="' . $this->CI->config->item("crm_link") . '/images/crm_task_screenshots/' . $id . '">' . $content . '</a>';
        }
    }

    function convertDateAus($date) {
        if (stristr($date, "-")) {
            $tmp = explode("-", $date);
            $date = $tmp[2] . "/" . $tmp[1] . "/" . $tmp[0];
        }
        return $date;
    }

    function printa($val) {
        echo "<pre>";
        print_r($val);
        echo "</pre>";
    }

    function get_country_iso() {
        $this->CI->db->select('iso');
        $this->CI->db->from('countries');
        $this->CI->db->where('country_id', $this->CI->config->item('country'));
        return $this->CI->db->get()->row()->iso;
    }

    /**
     * Upload Files
     */
    public function do_upload($userfile, $params) {
        if ($params['upload_path'] && $params['upload_path'] != "") {
            $upload_path = $params['upload_path'];
        } else {
            $upload_path = './images/';
        }

        if ($params['max_size'] && $params['max_size'] != "") {
            $max_size = $params['max_size'];
        } else {
            $max_size = 0; //no limit
        }

        if ($params['max_width'] && $params['max_width'] != "") {
            $max_width = $params['max_width'];
        } else {
            $max_width = 0; //no limit
        }

        if ($params['max_height'] && $params['max_height'] != "") {
            $max_height = $params['max_height'];
        } else {
            $max_height = 0; //no limit
        }


        // ------ Set value if set
        if ($params['file_name'] && $params['file_name'] != "") {
            $config['file_name'] = $params['file_name'];
        }
        if ($params['allowed_types'] && $params['allowed_types'] != "") {
            $config['allowed_types'] = $params['allowed_types'];
        }

        $config['upload_path'] = $upload_path;
        $config['max_size'] = $max_size;
        $config['max_width'] = $max_width;
        $config['max_height'] = $max_height;

        $this->CI->load->library('upload');
        $this->CI->upload->initialize($config);

        if (!$this->CI->upload->do_upload($userfile)) {
            #return 	$this->CI->upload->display_errors();
            return false;
        } else {
            #return	$this->CI->upload->data();
            return true;
        }
    }

    // get last service  - return row
    public function get_last_service_row($property_id) {
        $this->CI->db->select("j.id, j.date, j.status, j.assigned_tech, j.job_type, p.qld_new_leg_alarm_num, p.prop_upgraded_to_ic_sa, .p.state");
        $this->CI->db->from("jobs j");
        $this->CI->db->join('property as p', 'p.property_id = j.property_id', 'left');
        $this->CI->db->where('j.property_id', $property_id);
        $this->CI->db->group_start();
        $this->CI->db->where('j.status', 'Completed');
        $this->CI->db->or_where('j.status', 'Merged Certificates');
        $this->CI->db->group_end();
        $this->CI->db->where('j.del_job', 0);
        $this->CI->db->order_by('j.date', 'DESC');
        $this->CI->db->limit(1);
        $query = $this->CI->db->get();
        if ($query->num_rows() > 0) {
            return $query;
        } else {
            return false;
        }
    }

    public function convertEmailToArray($email){
	
		unset($jemail);
		$jemail = array();
		$temp = explode("\n",trim($email));
		foreach($temp as $val){
			
			$val2 = preg_replace('/\s+/', '', $val);
			if(filter_var($val2, FILTER_VALIDATE_EMAIL)){
				$jemail[] = $val2;
			}
			
		}
		
		// send email
		return $jemail;
	
    }

    function getGlobalSettings_personnel() {

       /* 
        $globalParams = array('country_id'=>$this->CI->config->item('country'));
        $globalSettings = $this->CI->gherxlib->getGlobalSettings($globalParams)->row();
        $globalSettings_personnel = $globalSettings->allocate_personnel;
        */

        $this->CI->db->select('*');
        $this->CI->db->from('staff_accounts as sa');
        $this->CI->db->join('country_access ca', 'ca.staff_accounts_id = sa.StaffID', 'INNER');
        $this->CI->db->where('sa.active', 1);
        $this->CI->db->where('sa.Deleted', 0);
        $this->CI->db->where('ca.country_id', $this->CI->config->item('country'));
        //$this->CI->db->where_in('sa.StaffID',$globalSettings_personnel);
        $this->CI->db->where('sa.StaffID',$this->CI->session->staff_id);

        $query = $this->CI->db->get();
        return $query->row_array();
    }

    public function getDaysMissedBy($completed_date, $end_date){

        $completed_date = date_create(date('Y-m-d', strtotime($completed_date)));
        $end_date = date_create(date('Y-m-d', strtotime($end_date)));

        if($completed_date<=$end_date){
            return NULL;
        }else{
            $diff = date_diff($end_date,$completed_date);
            $age = $diff->format("%r%a");
            $age_val = (((int) $age) != 0) ? $age : 0;
            return $age_val;
        }

    }
    

}
?>