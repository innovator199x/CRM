<?php

class System_model extends CI_Model {

    public function __construct() {
        $this->load->database();
        $this->load->model('properties_model');
        $this->access_check();
    }


    public function getServiceIcons($service, $color = '') {

        switch ($color) {
            case 1:
                $color_str = 'white';
                break;
            case 2:
                $color_str = 'grey';
                break;
            default:
                $color_str = 'colored';
        }

        switch ($service) {
            case 2:
                $serv_icon = 'smoke_' . $color_str . '.png';
            break;
            case 5:
                $serv_icon = 'safety_' . $color_str . '.png';
            break;
            case 6:
                $serv_icon = 'corded_' . $color_str . '.png';
            break;
            case 7:
                $serv_icon = 'water_' . $color_str . '.png';
            break;
            case 8:
                $serv_icon = 'sa_ss_' . $color_str . '.png';
            break;
            case 9:
                $serv_icon = 'sa_cw_ss_' . $color_str . '.png';
            break;
            case 11:
                $serv_icon = 'sa_wm_' . $color_str . '.png';
            break;
            case 12:
                $serv_icon = 'sa_' . $color_str . '_IC.png';
            break;
            case 13:
                $serv_icon = 'sa_ss_' . $color_str . '_IC.png';
            break;
            case 14:
                $serv_icon = 'sa_cw_ss_' . $color_str . '_IC.png';
            break;
            case 15: // Water Efficiency
                $serv_icon = 'we_' . $color_str . '.png';
            break;
            case 16: // Smoke Alarms & Water Efficiency
                $serv_icon = 'sawe_' . $color_str . '.png';
            break;
            case 17: // Bundle SA.SS.WE
                $serv_icon = 'sasswe_' . $color_str . '.png';
            break;
            case 18: // Bundle SA.SS.CW.WE
                $serv_icon = 'sasscwwe_' . $color_str . '.png';
            break;
            case 19: // Bundle SA.CW
                $serv_icon = 'sacw_' . $color_str . '.png';
            break;
            case 20: // Bundle SA.CW(IC)
                $serv_icon = 'sacw_' . $color_str . '_IC.png';
            break;
        }

        return $serv_icon;
    }

    /*
    function getServiceIcons_v2($service,$color='',$show_ic_icon=0){

        $append_ic = ($show_ic_icon == 1)?'_IC':'';

        switch($color){
            case 1:
                $color_str = 'white';
            break;
            case 2:
                $color_str = 'grey';
            break;
            default:
                $color_str = 'colored';
        }


        if( $service == 2 || $service == 12 ){ // Smoke Alarm
            $serv_icon = 'smoke_'.$color_str.$append_ic.'.png';
        }else if( $service == 5 ){ // Safety Switch
            $serv_icon = 'safety_'.$color_str.$append_ic.'.png';
        }else if( $service == 6 ){ // Corded Window
            $serv_icon = 'corded_'.$color_str.$append_ic.'.png';
        }else if( $service == 7 ){ // Water meter
            $serv_icon = 'water_'.$color_str.$append_ic.'.png';
        }else if( $service == 11 ){ // Smoke Alarm and Water Meter Bundle
            $serv_icon = 'sa_wm_'.$color_str.$append_ic.'.png';
        }else if( $service == 8 || $service == 13 ){ // Smoke Alarm and Safety Switch Bundle
            $serv_icon = 'sa_ss_'.$color_str.$append_ic.'.png';
        }else if( $service == 9 || $service == 14 ){ // Smoke Alarm, Corded Window and Safety Switch Bundle
            $serv_icon = 'sa_cw_ss_'.$color_str.$append_ic.'.png';
        }else if( $service == 15 ){ // Water Efficiency
            $serv_icon = 'we_'.$color_str.$append_ic.'.png';
        }else if( $service == 16 ){ // Smoke Alarms & Water Efficiency
            $serv_icon = 'sawe_'.$color_str.$append_ic.'.png';
        }else if( $service == 17 ){ // Bundle SA.SS.WE
            $serv_icon = 'sasswe_'.$color_str.$append_ic.'.png';
        }else if( $service == 18 ){ // Bundle SA.SS.CW.WE
            $serv_icon = 'sasscwwe_'.$color_str.$append_ic.'.png';
        }

        return $serv_icon;

    }
    */

    public function formatDate($date, $format = 'Y-m-d') {

        // format short year YY to full year YYYY
        $date_exp = explode("/",$date);
        $day = trim($date_exp[0]); // get day
        $month = trim($date_exp[1]); // get month
        $year = trim($date_exp[2]); // get year

        if( strlen($year) == 2 ){
            $datetime_obj = DateTime::createFromFormat('y', $year);
            $year_yyyy = $datetime_obj->format('Y'); // format to yyyy
            $date_fin = "{$day}/{$month}/{$year_yyyy}"; // format to dd/mm/yyyy
        }else{
            $date_fin = $date;
        }

        return date($format, strtotime(str_replace("/", "-", $date_fin)));

    }

    public function formatStaffName($fname, $lname) {
        return "{$fname}" . ( ($lname != "") ? ' ' . strtoupper(substr($lname, 0, 1)) . '.' : '' );
    }

    public function isDateNotEmpty($date) {
        if (
                $date != '' &&
                $date != '0000-00-00' &&
                $date != '0000-00-00 00:00:00' &&
                $date != '1970-01-01'
        ) {
            return true;
        } else {
            return false;
        }
    }

    public function logout() {

        // kick out
        $this->session->sess_destroy();
        redirect('/');
    }

    ## OLD TABLE NOT USED ANYMORE (use get_postcodes instead) > by:Gherx
    public function getRegion($params) {

        $this->db->select('*');
        $this->db->from('postcode_regions as pr');
        $this->db->join('countries as c', "c.country_id=pr.country_id", "left");
        $this->db->join('regions as r', "r.regions_id=pr.region", "left");
        $this->db->where(array('pr.deleted' => 0, 'pr.country_id' => $this->config->item('country')));

        if ($params['postcode_region_postcodes'] != "") {
            $this->db->like('pr.postcode_region_postcodes', $params['postcode_region_postcodes']);
        }

        // sort
        if (isset($params['sort_list'])) {
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }

        $query = $this->db->get();
        return $query;
    }

    // get the grand total of job price, new alarms and subcharge
    function getJobAmountGrandTotal($job_id, $country_id) {

        $grand_total = 0;

        $this->db->select('*');
        $this->db->from('jobs as j');
        $this->db->join('property as p', 'p.property_id = j.property_id', 'left');
        $this->db->join('agency as a', 'a.agency_id = p.agency_id', 'left');
        $this->db->where('j.id', $job_id);
        $this->db->where('a.country_id', $country_id);
        $query = $this->db->get();
        $row = $query->row_array();

        // get amount
        $grand_total = $row['job_price'];

        // get alarms
        $query = $this->db->select('*')->from('alarm')->where(array('job_id' => $job_id, 'new' => 1, 'ts_discarded' => 0))->get();
        foreach ($query->result_array() as $a) {
            $grand_total += $a['alarm_price'];
        }

        // surcharge
        $this->db->select('*, m.name as m_name');
        $this->db->from('agency_maintenance as am');
        $this->db->join('maintenance as m', 'm.maintenance_id = am.maintenance_id', 'left');
        $this->db->where('am.agency_id', $row['agency_id']);
        $this->db->where('am.maintenance_id >', 0);
        $sc_sql = $this->db->get();
        $sc = $sc_sql->row_array();

        if ($grand_total != 0 && $sc['surcharge'] == 1) {
            $grand_total += $sc['price'];
        }

        return $grand_total;
    }

    /**
     * check if Expiry Dates don't match
     * @param job_id
     * return boolean
     */
    function isAlarmExpiryDatesMatch($job_id) {

        $this->db->select('*');
        $this->db->from('alarm');
        $this->db->where('`expiry` != `ts_expiry`');
        $this->db->where('job_id', $job_id);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * check if Job is $0 and YM
     * @param job_id
     * return boolean
     */
    function isJobZeroPrice_Ym($job_id) {
        $this->db->select('*');
        $this->db->from('jobs');
        $this->db->where('job_price', '0.00');
        $this->db->where('job_type', 'Yearly Maintenance');
        $this->db->where('id', $job_id);
        $this->db->where('del_job', '0');
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * check New Alarms Installed
     * @param job_id
     * return boolean
     */
    function isJobHasNewAlarm($job_id) {
        $this->db->select('*');
        $this->db->from('alarm');
        $this->db->where('new', 1);
        $this->db->where('job_id', $job_id);

        if ($this->db->get()->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Property has Expired Alarms
     * @param job_id, property_id
     * return boolean
     */
    function isPropertyAlarmExpired($job_id, $property_id) {
        $query = $this->db->query("
			SELECT *
			FROM `alarm` AS alrm
			LEFT JOIN jobs AS j ON alrm.`job_id` = j.`id`
			LEFT JOIN property AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN agency AS a ON p.`agency_id` = a.`agency_id`
			WHERE (
				alrm.`expiry` <= '" . date("Y") . "'
				AND alrm.`expiry` != ''
			)
			AND alrm.`ts_discarded` = 0
			AND j.`id` ={$job_id}
			AND j.`property_id` ={$property_id}
			AND a.`country_id` = {$this->config->item('country')}
			AND p.`deleted` =0
            AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
			AND a.`status` = 'active'
			AND j.`del_job` = 0
		");
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * COT FR and LR price must be 0
     * @param job_id
     * return boolean
     */
    function CotLrFrPriceMustBeZero($job_id) {
        $query = $this->db->query("
			SELECT *
			FROM  `jobs`
			WHERE (
				`job_type` = 'Change of Tenancy' OR
				`job_type` = 'Lease Renewal' OR
				`job_type` = 'Fix or Replace'
			)
			AND `job_price` != 0.00
			AND `id` ={$job_id}
			AND `del_job` = 0
		");
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * If 240v has 0 price
     * @param job_id
     * return boolean
     */
    function is240vPriceZero($job_id) {
        $this->db->select('*');
        $this->db->from('jobs');
        $this->db->where('job_type', '240v Rebook');
        $this->db->where('job_price', '0.00');
        $this->db->where('id', $job_id);
        $this->db->where('del_job', 0);
        if ($this->db->get()->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * If discarded alarm is not equal to new alarm
     * @param job_id
     * return boolean
     */
    function isMissingAlarms($job_id) {

        // discarded alarm
        $query = $this->db->select('*')->from('alarm')->where(array('job_id' => $job_id, 'ts_discarded' => 1))->get();
        $dis_num = $query->num_rows();


        // new alarm
        $query2 = $this->db->select('*')->from('alarm')->where(array('job_id' => $job_id, 'ts_discarded' => 0, 'new' => 1))->get();
        $new_num = $query2->num_rows();

        if ($dis_num == $new_num) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * No Alarm
     * @param job_id
     * return boolean
     */
    function isNoAlarms($job_id) {

        $query = $this->db->select('*')->from('alarm')->where(array('job_id' => $job_id, 'ts_discarded' => 0))->get();

        if ($query->num_rows() == 0) {
            return true;
        } else {
            return false;
        }
    }

    // If job date is not today
    function isJobDateNotToday($job_id) {

        $query = $this->db->select('*')->from('jobs')->where(array('id' => $job_id, 'date!=' => date("Y-m-d")))->get();

        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    // If job date is not today
    function isSSfailed($job_id) {

        $query = $this->db->select('*')->from('safety_switch')->where(array('job_id' => $job_id, 'test' => 0))->get();
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    function isSafetySwitchServiceTypes($service) {

        $ss_serv_types = array(5,8,9,13,14,17,18);
        if (in_array($service, $ss_serv_types)) {
            return true;
        } else {
            return false;
        }
    }

    // display error for these agencies
    function ifDHAAgencies($job_id) {
        $sql = $this->db->query("
			SELECT *
			FROM `jobs` AS j
			LEFT JOIN property AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN agency AS a ON p.`agency_id` = a.`agency_id`
			WHERE j.`id` ={$job_id}
			AND a.`agency_id` IN(3043,3036,3046,1902,3044,1906,1927,3045)
			AND a.`country_id` = {$this->config->item('country')}
			AND p.`deleted` =0
            AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
			AND a.`status` = 'active'
			AND j.`del_job` = 0
		");
        if ($sql->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get Last YM
     * @param property_id, service
     */
    function pcjGetLastYMCompletedDate($property_id, $service) {
        $sql = $this->db->query("
			SELECT *
			FROM `jobs`
			WHERE `property_id` ={$property_id}
			AND `service` = {$service}
			AND `status` = 'Completed'
			AND `job_type` = 'Yearly Maintenance'
			AND `del_job` = 0
			ORDER BY `date` DESC
			LIMIT 0 , 1
		");
        $row = $sql->row_array();
        echo ($row['date'] != "" && $row['date'] != "0000-00-00") ? date("d/m/Y", strtotime($row['date'])) : '';
    }

    /**
     * Find booked with tenants
     * @param job_id
     * return array
     */
    function findBookedWithTenantNumber($job_id) {

        $this->db->select('*');
        $this->db->from('jobs as j');
        $this->db->join('property as p', 'p.property_id = j.property_id', 'left');
        $this->db->where('id', $job_id);
        $query = $this->db->get();
        $row = $query->row_array();

        $available_tenants_arr = [];
        $has_tenant_email = 0;

        $pt_params = array(
            'property_id' => $row['property_id'],
            'active' => 1
        );
        $pt_sql = $this->properties_model->get_new_tenants($pt_params);

        foreach ($pt_sql->result_array() as $pt_row) {
            if ($pt_row["tenant_mobile"] != "" && $pt_row['tenant_firstname'] == $row['booked_with']) {
                $booked_with_tent_num = $pt_row["tenant_mobile"];
                $booked_with_tent_fname = $pt_row['tenant_firstname'];
            }
        }

        return array(
            'booked_with_tent_num' => $booked_with_tent_num,
            'booked_with_tent_fname' => $booked_with_tent_fname
        );
    }

    /**
     * Get private franchise group
     * @param franchise group
     * return boolean
     */
    function getAgencyPrivateFranchiseGroups($franchise_group) {

        $private_fg = false;
        if ($this->config->item('country') == 1) { // AU
            if ($franchise_group == 10) { // AU private ID
                $private_fg = true;
            }
        } else if ($this->config->item('country') == 2) { // NZ
            if ($franchise_group == 37) { // NZ private ID
                $private_fg = true;
            }
        }

        return $private_fg;
    }

    /**
     * Get SMS Template
     * @params array
     * return sms template
     */
    function getSMStemplate($params) {

        switch ($params['sms_type']) {
            case 16:
                $sms_temp = "This is to confirm your appointment made today for the {$params['date']} @ {$params['time']} to service the {$params['serv_name']} at {$params['paddress']}. Please ensure someone is home to allow access. SATS {$params['tenant_number']}";
                break;
            case 4: // No-Show, Agency Notified
                $sms_temp = "We attended your property today to check your smoke alarms as per our appointment and nobody was home. Please call {$params['tenant_number']} to reschedule or we will notify {$params['landlord_txt']} of the missed appointment";
                break;
            case 10: // Entry Notice, SMS EN
                $sms_temp = "Smoke Alarm Testing Services (SATS) have issued you an Entry Notice to test the {$params['serv_name']} at {$params['paddress']} on {$params['jdatetemp']} and will collect the keys from your Real Estate. Click here to view <link>";
                break;
            case 9: // Entry Notice, SMS EN
                $sms_temp = "Smoke Alarm Testing Services (SATS) have issued you an Entry Notice to test the {$params['serv_name']} at {$params['paddress']} on {$params['jdatetemp']} and will collect the keys from your agency. Email may appear in Spam/Junk folders. View this Entry Notice by clicking this link <link>";
                break;
        }

        return $sms_temp;
    }

    /**
     * find an expired 240v alarm
     * @params job_id
     * return boolean
     */
    function findExpired240vAlarm($job_id, $year=null) {

        $year2 = ( $year != '' ) ? $year : date("Y");

        $alarm_sql = $this->db->query("
			SELECT COUNT(al.`alarm_id`) AS al_count
            FROM `alarm` AS al
            LEFT JOIN `alarm_pwr` AS al_pwr ON al.`alarm_power_id` = al_pwr.`alarm_pwr_id`
			WHERE al.`job_id` = {$job_id}
			AND al.`expiry` <= '{$year2}'
            AND al.`ts_discarded` = 0
            AND al_pwr.`is_240v` = 1
        ");
        $alarm_count = $alarm_sql->row()->al_count;

        if ( $alarm_count > 0) {
            return true;
        } else {
            return false;
        }

    }

    public function mark_is_eo($job_id, $year=null) {  
        
        if( $job_id > 0 ){

            // copied from findExpired240vAlarm
            $year2 = ( $year != '' ) ? $year : date("Y");
            $alarm_sql = $this->db->query("
                SELECT COUNT(al.`alarm_id`) AS al_count
                FROM `alarm` AS al
                LEFT JOIN `alarm_pwr` AS al_pwr ON al.`alarm_power_id` = al_pwr.`alarm_pwr_id`
                WHERE al.`job_id` = {$job_id}
                AND al.`expiry` <= '{$year2}'
                AND al.`ts_discarded` = 0
                AND al_pwr.`is_240v` = 1
            ");
            $alarm_count = $alarm_sql->row()->al_count;

            // FR - 240v check, find 240v alarms even if not expired
            $alarm_sql2 = $this->db->query("
                SELECT COUNT(al.`alarm_id`) AS al_count
                FROM `alarm` AS al
                LEFT JOIN `jobs` AS j ON al.`job_id` = j.`id`
                LEFT JOIN `alarm_pwr` AS al_pwr ON al.`alarm_power_id` = al_pwr.`alarm_pwr_id`
                WHERE al.`job_id` = {$job_id}	
                AND j.`job_type` = 'Fix or Replace'		
                AND al.`ts_discarded` = 0
                AND al_pwr.`is_240v` = 1
            ");
            $alarm_count2 = $alarm_sql2->row()->al_count;

            if ( $alarm_count > 0 || $alarm_count2 > 0 ) {
            
                // set this job as EO = for electrician only
                $this->db->query("
                UPDATE `jobs`
                SET `is_eo` = 1
                WHERE `id` = {$job_id}
                ");
                
            } 

        }        

    }


    public function insert_job_markers($job_id,$new_job_type){

        $today = date('Y-m-d H:i:s');

        if( $job_id > 0 ){

            // get current job type
            $job_sql = $this->db->query("
            SELECT `job_type`
            FROM `jobs`        
            WHERE `id` = {$job_id}
            ");
            $job_row = $job_sql->row();

            if( $new_job_type != $job_row->job_type ){
                
                // determine what kind of job type change
                if( $job_row->job_type == 'Yearly Maintenance' && $new_job_type == '240v Rebook' ){
                    $job_type_change = 1;
                }else if ( $job_row->job_type == 'Change of Tenancy' && $new_job_type == 'Yearly Maintenance' ){
                    $job_type_change = 2;
                }

                if( $job_type_change > 0 ){

                    // log this change, using ben's `job_markers` table
                    $this->db->query("
                    INSERT INTO 
                    `job_markers` (
                        `job_id`,
                        `job_type_change`,
                        `date`
                    )
                    VALUES(
                        {$job_id},
                        {$job_type_change},
                        '{$today}'
                    )
                    ");  

                }                             

            }

        }                

    }

    function findExpiredAlarm($job_id, $year=null) {

        $year2 = ( $year != '' ) ? $year : date("Y");

        $alarm_sql = $this->db->query("
            SELECT COUNT(al.`alarm_id`) AS al_count
            FROM `alarm` AS al
            LEFT JOIN `alarm_pwr` AS al_pwr ON al.`alarm_power_id` = al_pwr.`alarm_pwr_id`
            WHERE al.`job_id` = {$job_id}
            AND al.`expiry` <= '{$year2}'
            AND al.`ts_discarded` = 0
        ");
        $alarm_count = $alarm_sql->row()->al_count;

        if ( $alarm_count > 0) {
            return true;
        } else {
            return false;
        }

    }

    function findExpiredAlarmByJobIds($jobIds, $year=null) {
        if (empty($jobIds)) return [];
        $jobIdsString = implode(',', $jobIds);

        $year2 = ( $year != '' ) ? $year : date("Y");

        $alarmCountResult = $this->db->query("
            SELECT al.`job_id`, COUNT(al.`alarm_id`) AS al_count
            FROM `alarm` AS al
            LEFT JOIN `alarm_pwr` AS al_pwr ON al.`alarm_power_id` = al_pwr.`alarm_pwr_id`
            WHERE al.`job_id` IN ({$jobIdsString})
            AND al.`expiry` <= '{$year2}'
            AND al.`ts_discarded` = 0
            GROUP BY al.`job_id`
        ");

        $result = [];

        if ($alarmCountResult->num_rows()) {
            foreach ($alarmCountResult->result_array() as $row) {
                $result[$row['job_id']] = $row['al_count'] > 0;
            }
        }

        return $result;

    }



    /**
     * fetch all STR created on this region (by region)
     * @params postcode region id
     * return query
     */
    function getStrbyRegion($postcode_region_id) {

        $query = $this->db->query("
			SELECT *
			FROM  `tech_run`
			WHERE `sub_regions` LIKE '%$postcode_region_id%'
			AND `date` >= '" . date('Y-m-d') . "'
			AND `country_id` = {$this->config->item('country')}
			ORDER BY `date`
		");
        return $query;
    }

    /**
     * Get Tech Run by tech id
     * $params array -  tech id
     * return query
     */
    public function getTech_run($params) {

        if (isset($params['sel_query']) && !empty($params['sel_query'])) {
            $this->db->select($params['sel_query']);
        } else {
            $this->db->select('*');
        }

        $this->db->from('tech_run as tr');

        //joins tables
        if ($params['join_table'] > 0) {

            foreach ($params['join_table'] as $join_table) {

                if ($join_table == 'tech_run_rows') {
                    $this->db->join('tech_run_rows as trr', 'trr.tech_run_id = tr.tech_run_id', 'left');
                }

                if ($join_table == 'staff_accounts') {
                    $this->db->join('staff_accounts as sa', 'sa.StaffID = tr.assigned_tech', 'left');
                }

                if ($join_table == 'jobs') {
                    $this->db->join('jobs as j', 'j.id = trr.row_id', 'left');
                }

                if ($join_table == 'property') {
                    $this->db->join('property as p', 'p.property_id = j.property_id', 'left');
                }

                if ($join_table == 'agency') {
                    $this->db->join('agency as a', 'a.agency_id = p.agency_id', 'left');
                }
            }
        }

        //filters
        //tech run assigned tech
        if ($params['tech'] != "") {
            $this->db->where('tr.assigned_tech', $params['tech']);
        }

        //tech run date
        if ($params['date'] != "") {
            $this->db->where('tr.date', $params['date']);
        }

        //tech run rows type
        if ($params['row_id_type'] != "") {
            $this->db->where('trr.row_id_type', $params['row_id_type']);
        }

        //job id
        if ($params['job_id'] != "") {
            $this->db->where('j.id', $params['job_id']);
        }

        //tech run row hidden
        if ($params['hidden'] != "") {
            $this->db->where('trr.hidden', $params['hidden']);
        }

        //job deleted
        if ($params['del_job'] != "") {
            $this->db->where('j.del_job', $params['del_job']);
        }

        //tech run rows country id
        if ($params['tr_country_id'] != "") {
            $this->db->where('tr.country_id', $params['tr_country_id']);
        }

        //agency country id
        if ($params['a_country_id'] != "") {
            $this->db->where('a.country_id', $params['a_country_id']);
        }

        //filter date onwards on Escalate Jobs STR
        $date = date('Y-m-d');
        $date_onwards = "tr.date >= '$date'";
        if ($params['date_onwards'] != "") {
            $this->db->where($date_onwards);
            $this->db->order_by('tr.date', 'ASC');
        }

        //db get
        $query = $this->db->get();

        return $query;
    }

    /**
     * Get Job Details for MM Needs Processing
     * $params job_id
     * return query
     */
    function getJobDetails2($job_id, $query_only = false) {

        $query = $this->db->query("SELECT j.id, DATE_FORMAT(j.date,'%d/%m/%Y'), j.status, j.comments, j.retest_interval, j.auto_renew, j.job_type,
			sa.FirstName, sa.LastName, p.address_1, p.address_2, p.address_3, p.state, p.postcode, j.time_of_day, j.assigned_tech,
			p.tenant_firstname1, p.tenant_lastname1, p.tenant_ph1, j.tech_comments, p.property_id, p.tenant_firstname2, p.tenant_lastname2, p.tenant_ph2,
			a.agency_id, a.agency_name, a.address_1 AS agent_address_1, a.address_2 AS agent_address_2, a.address_3 AS agent_address_3, a.phone AS agent_phone, a.state AS agent_state, a.postcode  AS agent_postcode
			, j.job_price, j.price_used, p.price, j.work_order , j.ts_noshow,
			DATE_FORMAT(j.client_emailed, '%e/%m/%Y @ %r' ) AS LastSent, ts_doorknock, p.agency_deleted, a.send_combined_invoice,
			DATE_FORMAT(j.date, '%d/%m/%Y') AS date, j.key_access_required, p.tenant_email1, p.tenant_email2, p.tenant_mob1, p.tenant_mob2,
			DATE_FORMAT(j.entry_notice_emailed, '%d/%m/%Y @ %r') AS EntryNoticeLastSent, sa.ContactNumber,
			DATE_FORMAT(j.date, '%W') as booking_date_name,
			DATE_FORMAT(j.date, '%d') AS booking_date_day,
			DATE_FORMAT(j.date, '%m') AS booking_date_month,
			DATE_FORMAT(j.date, '%Y') AS booking_date_year,
			a.agency_emails,
			a.send_entry_notice,
			DATE_FORMAT(DATE_ADD(j.date, INTERVAL 1 YEAR), '%d/%m/%Y') AS retest_date,
			j.ss_location,
			j.ss_quantity,
			j.tmh_id,
			j.ts_db_reading, p.key_number,
			j.price_reason,
			j.price_detail,
			j.service AS jservice,
			a.`country_id`,
			j.`ps_qld_leg_num_alarm`,
			a.`account_emails`,
			p.`qld_new_leg_alarm_num`,
			a.`display_bpay`,
			j.`show_as_paid`,
			j.`invoice_balance`

			FROM `jobs` AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
			WHERE  j.`id` = {$job_id}");

        return $query->result_array();
    }

    /**
     * Get Alarms Details for MM Needs Processing
     * $params job_id
     * return query
     */
    function getPropertyAlarms($job_id, $incnew = 1, $discarded = 1, $alarm_job_type_id = 1) {

        $query = "  SELECT a.*, p.alarm_pwr, t.alarm_type, r.alarm_reason
	                FROM alarm a
	                    LEFT JOIN alarm_pwr p ON a.alarm_power_id = p.alarm_pwr_id
	                    LEFT JOIN alarm_type t ON t.alarm_type_id = a.alarm_type_id
	                    LEFT JOIN alarm_reason r ON r.alarm_reason_id = a.alarm_reason_id
	                WHERE a.job_id = '" . $job_id . "'";

        if ($alarm_job_type_id == 4 || $alarm_job_type_id == 5) { // Safety Switch view and mech should have same alarms
            $query .= " AND a.alarm_job_type_id IN (4,5)";
        } else {
            $query .= " AND a.alarm_job_type_id = {$alarm_job_type_id}";
        }

        if ($incnew == 0)
            $query .= " AND a.New = 0";
        if ($incnew == 2)
            $query .= " AND a.New = 1";

        if ($discarded == 0)
            $query .= " AND a.ts_discarded = 0";
        if ($discarded == 2)
            $query .= " AND a.ts_discarded = 1";

        $query .= " ORDER BY a.alarm_id ASC ";

        $alarms = $this->db->query($query);

        return $alarms->result_array();
    }

    /**
     * Get Job detail for maintenance program
     * $params array
     * return query
     */
    function getJobsData($params) {

        // filters
        $filter_arr = array();

        // fields that needs join
        $join_tbl_str = '';
        if ($params['ejr_id'] != '') {
            $filter_arr[] = " AND sejr.`escalate_job_reasons_id` = '{$params['ejr_id']}' ";
            $join_tbl_str = "
			INNER JOIN `selected_escalate_job_reasons` AS sejr ON j.`id` = sejr.`job_id`
			LEFT JOIN `escalate_job_reasons` AS ejr ON sejr.`escalate_job_reasons_id` = ejr.`escalate_job_reasons_id`
			";
        }

        // maintenance program join
        $extra_field = '';
        if ($params['join_maintenance_program'] == 1) {
            $extra_field = '
				am.`maintenance_id`,
				m.`name` AS m_name,
			';
            $filter_arr[] = " AND am.`maintenance_id` > 0 ";
            $filter_arr[] = " AND am.`status` = 1 ";
            $filter_arr[] = " AND m.`status` = 1 ";
            $join_tbl_str = "
			INNER JOIN `agency_maintenance` AS am ON a.`agency_id` = am.`agency_id`
			LEFT JOIN `maintenance` AS m ON am.`maintenance_id` = m.`maintenance_id`
			";
        }

        // maintenance program join
        $extra_field = '';
        if ($params['mp_join'] == 1) {
            $extra_field = '
				am.`maintenance_id`,
				m.`name` AS m_name,
			';
            $join_tbl_str = "
			LEFT JOIN `agency_maintenance` AS am ON a.`agency_id` = am.`agency_id`
			LEFT JOIN `maintenance` AS m ON am.`maintenance_id` = m.`maintenance_id`
			";
        }

        if ($params['remove_deleted_filter'] != 1) {

            // deleted marker
            if ($params['j_del'] != "") {
                $filter_arr[] = "AND j.`del_job` = {$params['j_del']}";
            } else {
                $filter_arr[] = "AND j.`del_job` = 0";
            }

            if ($params['p_del'] != "") {
                $filter_arr[] = "AND p.`deleted` = {$params['p_del']}";
            } else {
                $filter_arr[] = "AND p.`deleted` = 0";
            }
        }

        /*
        if ($params['a_status'] != "") {
            $filter_arr[] = "AND a.`status` = {$params['a_status']}";
        } else {
            $filter_arr[] = "AND a.`status` = 'active'";
        }*/

        ##updated by gherx
        if ($params['a_status'] && $params['a_status'] != "") {
            $filter_arr[] = "AND a.`status` = {$params['a_status']}";
        }
        ##updated by gherx end

        if ($params['to_be_printed'] != "") {
            $filter_arr[] = " AND j.`to_be_printed` = {$params['to_be_printed']}";
        }


        if ($params['property_managers_id'] != "") {
            $filter_arr[] = " AND p.`pm_id_new` = {$params['property_managers_id']}";
        }

        if ($params['job_id'] != "") {
            $filter_arr[] = "AND j.`id` = {$params['job_id']}";
        }

        if ($params['maintenance_id'] != "") {
            $filter_arr[] = "AND am.`maintenance_id` = {$params['maintenance_id']}";
        }

        if ($params['job_service'] != '') {
            $filter_arr[] = " AND j.`service` = '{$params['job_service']}' ";
        }

        if ($params['country_id'] != "") {
            $filter_arr[] = "AND a.`country_id` = {$params['country_id']}";
        }

        if ($params['agency_id'] != "") {
            $filter_arr[] = "AND a.`agency_id` = {$params['agency_id']}";
        }

        if ($params['job_type'] != '') {
            $filter_arr[] = "AND j.`job_type` = '{$params['job_type']}'";
        }

        if ($params['postcode_region_id'] != "") {
            $filter_arr[] = "AND p.`postcode` IN ( {$params['postcode_region_id']} )";
        }

        if ($params['a_postcode_region_id'] != "") {
            $filter_arr[] = "AND a.`postcode` IN ( {$params['a_postcode_region_id']} )";
        }

        if ($params['a_state'] != '') {
            $filter_arr[] = "AND a.`state` = '{$params['a_state']}'";
        }

        if ($params['job_status'] != '') {
            $filter_arr[] = "AND j.`status` = '{$params['job_status']}'";
        }

        if ($params['booked'] == 1) {
            $filter_arr[] = "AND j.`status` = 'Booked'";
        }

        if ($params['job_created'] != '') {
            $filter_arr[] = "AND CAST( j.`created` AS DATE ) = '{$params['job_created']}'";
        }

        if ($params['ts_completed'] == 1) {
            $filter_arr[] = "AND j.`ts_completed` = 1";
        }

        if (is_numeric($params['dk'])) {
            $filter_arr[] = "AND j.`door_knock` = {$params['dk']}";
        }

        if ($params['date'] != '') {
            $filter_arr[] = "AND j.`date` = '{$params['date']}'";
        }

        if (is_numeric($params['urgent_job'])) {
            $filter_arr[] = "AND j.`urgent_job` = '{$params['urgent_job']}'";
        }

        if (is_numeric($params['auto_renew'])) {
            $filter_arr[] = "AND a.`auto_renew` ={$params['auto_renew']}";
        }

        if (is_numeric($params['out_of_tech_hours'])) {
            $filter_arr[] = "AND j.`out_of_tech_hours` = {$params['out_of_tech_hours']}";
        }

        if ($params['date_range'] != '') {
            $filter_arr[] = "AND j.`date` BETWEEN '{$params['date_range']['from']}' AND '{$params['date_range']['to']}'";
        }

        if ($params['exclude_status_for_kpi_report'] == 1) {
            $filter_arr[] = "AND (
				j.`status` != 'On Hold' AND
				j.`status` != 'Pending' AND
				j.`status` != 'Completed' AND
				j.`status` != 'Cancelled'
			)";
        }

        if ($params['status_booked_or_completed'] == 1) {
            $filter_arr[] = "AND (
				j.`status` = 'Booked' OR
				j.`status` = 'Completed'
			)";
        }

        if ($params['completed_status_for_kpi_report'] == 1) {
            $filter_arr[] = "AND ( j.`status` = 'Completed' OR j.`status` = 'Merged Certificates' )";
        }

        if ($params['query_for_estimated_income'] == 1) {
            $filter_arr[] = "AND (
				 ( j.`status` = 'Booked' AND j.`door_knock` !=1 ) OR
				  j.`status` = 'Completed'  OR
				  j.`status` = 'Merged Certificates'
			)";
        }

        if ($params['exclude_tech_other_supplier'] == 1) {
            $filter_arr[] = "AND (
                j.`assigned_tech` != 1
                OR j.`assigned_tech` IS NULL
            )";
        }

        if ($params['dha_need_processing'] == 1) {
            $filter_arr[] = "AND j.`dha_need_processing` = 1";
        }

        if ($params['phrase'] != '') {
            $filter_arr[] = "AND (
				(CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$params['phrase']}%') OR
				(a.`agency_name` LIKE '%{$params['phrase']}%')
			 )";
        }

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE j.`id` > 0 " . implode(" ", $filter_arr);
        }

        $sel_str = "
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

			{$extra_field}

			aua.`agency_user_account_id`,
			aua.`fname` AS pm_fname,
			aua.`lname` AS pm_lname,
			aua.`email` AS pm_email,

			ajt.`id` AS ajt_id,
			ajt.`type` AS ajt_type
		";

        if ($params['return_count'] == 1) {
            $sel_str = " COUNT(j.id) AS jcount ";
        } else if ($params['distinct'] != "") {
            switch ($params['distinct']) {
                case 'p.`property_managers_id`':
                    $sel_str = " DISTINCT p.`property_managers_id`, pm.`name`, pm.`pm_email` ";
                    break;
                case 'p.`pm_id_new`':
                    $sel_str = " DISTINCT p.`pm_id_new`, aua.`fname`, aua.`lname`, aua.`email` ";
                    break;
                case 'j.`job_type`':
                    $sel_str = " DISTINCT j.`job_type` ";
                    break;
                case 'j.`service`':
                    $sel_str = " DISTINCT j.`service`, ajt.`id` , ajt.`type` ";
                    break;
                case 'p.`state`':
                    $sel_str = " DISTINCT p.`state` ";
                    break;
                case 'a.`state`':
                    $sel_str = " DISTINCT a.`state` ";
                    break;
                case 'p.`agency_id`':
                    $sel_str = " DISTINCT p.`agency_id`, a.`agency_name` ";
                    break;
                case 'a.`agency_id`':
                    $sel_str = " DISTINCT a.`agency_id`, a.`agency_name` ";
                    break;
                case 'a.`state`':
                    $sel_str = " DISTINCT a.`agency_id`, a.`state` ";
                    break;
                case 'sa.`assigned_tech`':
                    $sel_str = " DISTINCT sa.`StaffID`, sa.`FirstName`, sa.`LastName` ";
                    break;
                case 'am.`maintenance_id`':
                    $sel_str = " DISTINCT am.`maintenance_id`, m.`name` AS m_name ";
                    break;
                    break;
                case 'tech_id': // need to find where did i passed this
                    $sel_str = " DISTINCT j.`assigned_tech`";
                    break;
                case 'j.`status`':
                    $sel_str = " DISTINCT j.`status` ";
                    break;
                case 'sejr.`escalate_job_reasons_id`':
                    $sel_str = " DISTINCT sejr.`escalate_job_reasons_id`, ejr.`reason` ";
                    $join_tbl_str = "
					INNER JOIN `selected_escalate_job_reasons` AS sejr ON j.`id` = sejr.`job_id`
					LEFT JOIN `escalate_job_reasons` AS ejr ON sejr.`escalate_job_reasons_id` = ejr.`escalate_job_reasons_id`
					";
                    break;
            }
        } else if ($params['sum_age'] == "1") {
            $sel_str = " SUM( DATEDIFF( '" . date('Y-m-d') . "', CAST( j.`created` AS DATE ) ) ) AS sum_age ";
        } else if ($params['sum_job_price'] == "1") {
            $sel_str = " SUM( j.`job_price` ) AS j_price ";
        } else if ($params['count_jobs_by_agency'] == "1") {
            $sel_str = " COUNT( j.`id` ) AS esc_num_jobs, a.`agency_id`, a.`agency_name`, a.`phone`, a.`state`, a.`save_notes`, a.`escalate_notes`, a.`escalate_notes_ts`, a.`trust_account_software`, a.`tas_connected`, a.`propertyme_agency_id` ";
        } else if ($params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        }

        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }

        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "SELECT {$sel_str}
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		LEFT JOIN `job_reason` AS jr ON j.`job_reason_id` = jr.`job_reason_id`
		LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
		LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
		LEFT JOIN `agency_user_accounts` AS aua ON p.`pm_id_new` = aua.`agency_user_account_id`
		{$join_tbl_str}
		{$filter_str}
		{$custom_filter_str}
		{$group_by_str}
		{$sort_str}
		{$pag_str}
		";

        if ($params['display_echo'] == 1) {
            echo $sql;
        }

        if ($params['return_count'] == 1) {
            $jobData = $this->db->query($sql);
            return $jobData[0]['jcount'];
        } else {
            $jobData = $this->db->query($sql);

            return $jobData->result_array();
        }
    }

    /**
     * Get tech
     * @params array
     * return query
     */
    public function getTech($params) {

        if ($params['sel_query'] != "") {
            $this->db->select($params['sel_query']);
        } else {
            $this->db->select('*');
        }

        $this->db->from('staff_accounts as sa');
        $this->db->join('country_access as ca', 'ca.staff_accounts_id = sa.StaffID', 'left');
        $this->db->where('ca.country_id', $this->config->item('country'));
        $this->db->where('sa.Deleted', 0);
        $this->db->where('sa.ClassID', 6);
        $this->db->where('sa.active', 1);

        if ($params['staffID'] != "") {
            $this->db->where('sa.StaffID', $params['staffID']);
        }

        $this->db->order_by('sa.FirstName', 'ASC');
        $this->db->order_by('sa.LastName', 'ASC');
        $query = $this->db->get();
        return $query;
    }

    /**
     * update job invoice details
     * @params array
     * return query
     */
    function updateInvoiceDetails($job_id) {

        if ($job_id != '') {

            // get job details
            $this->db->select('`invoice_amount`, `invoice_payments`, `invoice_refunds`, `invoice_credits`, `invoice_balance`');
            $this->db->from('jobs');
            $this->db->where('id', $job_id);
            $query = $this->db->get();
            $job = $query->result_array();

            $invoice_amount_orig = $job[0]['invoice_amount'];
            $invoice_payments_orig = $job[0]['invoice_payments'];
            $invoice_refunds_orig = $job[0]['invoice_refunds'];
            $invoice_credits_orig = $job[0]['invoice_credits'];
            $invoice_balance_orig = $job[0]['invoice_balance'];

            // get the calculated values
            // invoice amount
            $inv_a = $this->getJobTotalAmount($job_id);
            $invoice_amount = ( $inv_a > 0 ) ? $inv_a : 0;

            // invoice payments
            $inv_p = $this->getJobInvoicePayments($job_id);
            $invoice_payments = ( $inv_p > 0 ) ? $inv_p : 0;

            // invoice refunds
            $inv_r = $this->getJobInvoiceRefunds($job_id);
            $invoice_refunds = ( $inv_r > 0 ) ? $inv_r : 0;

            // invoice credits
            $inv_c = $this->getJobInvoiceCredits($job_id);
            $invoice_credits = ( $inv_c > 0 ) ? $inv_c : 0;

            // invoice balance
            $invoice_balance = ($invoice_amount + $invoice_refunds) - ( $invoice_payments + $invoice_credits);

            $test_val = "
			invoice_amount_orig: {$invoice_amount_orig} - invoice_amount: {$invoice_amount}<br />
			invoice_payments_orig: {$invoice_payments_orig} - invoice_payments: {$invoice_payments}<br />
			invoice_refunds_orig: {$invoice_refunds_orig} - invoice_refunds: {$invoice_refunds}<br />
			invoice_credits_orig: {$invoice_credits_orig} - invoice_credits: {$invoice_credits}<br />
			invoice_balance_orig: {$invoice_balance_orig} - invoice_balance: {$invoice_balance}<br />
			";
            //echo $test_val;
            // only update if invoice details changed
            if (
                    $invoice_amount_orig == '' ||
                    $invoice_amount_orig != $invoice_amount ||
                    $invoice_payments_orig != $invoice_payments ||
                    $invoice_refunds_orig != $invoice_refunds ||
                    $invoice_credits_orig != $invoice_credits ||
                    $invoice_balance_orig != $invoice_balance
            ) {
                $updateData = array(
                    'invoice_amount' => $invoice_amount,
                    'invoice_payments' => $invoice_payments,
                    'invoice_refunds' => $invoice_refunds,
                    'invoice_credits' => $invoice_credits,
                    'invoice_balance' => $invoice_balance
                );

                $this->db->where('id', $job_id);
                $this->db->update('jobs', $updateData);
            }
        }
    }

    /**
     * Get job invoice credit
     * @params job_id
     * return query
     */
    function getJobInvoiceCredits($job_id) {
        $sql = "SELECT SUM(`credit_paid`) AS credit_paid_tot
			FROM `invoice_credits`
			WHERE `job_id` = {$job_id}
			AND `active` = 1
		";

        $invoiceCredits = $this->db->query($sql);
        $row = $invoiceCredits->row_array();

        return $row['credit_paid_tot'];
    }

    /**
     * Get job invoice refund
     * @params job_id
     * return query
     */
    function getJobInvoiceRefunds($job_id) {
        $sql = "SELECT SUM(`amount_paid`) AS refund_paid_tot
			FROM `invoice_refunds`
			WHERE `job_id` = {$job_id}
			AND `active` = 1
		";

        $invoiceRefund = $this->db->query($sql);
        $row = $invoiceRefund->row_array();

        return $row['refund_paid_tot'];
    }

    /**
     * Get job invoice payment
     * @params job_id
     * return query
     */
    function getJobInvoicePayments($job_id) {
        $sql = "SELECT SUM(`amount_paid`) AS amount_paid_tot
			FROM `invoice_payments`
			WHERE `job_id` = {$job_id}
			AND `active` = 1
		";

        $invoicePayment = $this->db->query($sql);
        $row = $invoicePayment->row_array();

        return $row['amount_paid_tot'];
    }

    /**
     * Get job total amout
     * @params job_id
     * return query
     */
    function getJobTotalAmount($job_id) {

        $grand_total = 0;

        $sql = "SELECT *, j.service AS j_service
			FROM `jobs` AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE j.`id` = {$job_id}
		";

        $jobData = $this->db->query($sql);
        $row1 = $jobData->row_array();

      /*  if ($jobData->num_rows() > 0) {
            // get amount
            $grand_total = $row1['job_price'];
        }*/

        //set price variations start
        $tt_params = array(
            'service_type' => $row1['j_service'],
            'property_id' => $row1['property_id'],
            'job_id' => $job_id
        );
        //$tt_price = $this->system_model->get_job_variation($tt_params);
        $tt_price = $this->system_model->get_job_variations_v2($tt_params);
        $grand_total = $tt_price['total_price_including_variations'];
        //set price variations start end

        // get new alarm
        $a_sql = "SELECT *
            FROM `alarm`
            WHERE `job_id`  = {$job_id}
            AND `new` = 1
            AND `ts_discarded` = 0
        ";

        $ajobData = $this->db->query($a_sql);
        $row2 = $ajobData->result_array();
        if ($ajobData->num_rows() > 0) {
            foreach ($row2 as $a) {
                $grand_total += $a['alarm_price'];
            }
        }


        if ($jobData->num_rows() > 0) {

            // surcharge
            $sc_sql = "SELECT *, m.`name` AS m_name
                FROM `agency_maintenance` AS am
                LEFT JOIN `maintenance` AS m ON am.`maintenance_id` = m.`maintenance_id`
                WHERE am.`agency_id` = {$row1['agency_id']}
                AND am.`maintenance_id` > 0
            ";

            $scjobData = $this->db->query($sc_sql);
            $sc = $scjobData->result_array();
            if ($scjobData->num_rows() > 0) {
                if ($grand_total != 0 && $sc[0]['surcharge'] == 1) {
                    $grand_total += $sc[0]['price'];
                }
            }
        }

        
        //New job_variation tweak start
       /* $jv_sql = $this->db->query("
            SELECT 
            jv.id, 
            jv.amount,
            jv.type,
            jv.reason
            FROM `job_variation` as jv
            WHERE jv.job_id = {$job_id}
            AND jv.active = 1
        "); */

        /*
        $jv_sql = $this->db->query("
            SELECT 
            jv.id, 
            jv.amount,
            jv.type,
            jv.reason,

            dv.variation_id,
            dv.display_on,
            dv.type as display_variation_type
            FROM `job_variation` as jv
            INNER JOIN `display_variation` AS dv ON ( jv.`id` = dv.`variation_id` AND dv.type = 2 )
            WHERE jv.job_id = {$job_id}
            AND jv.active = 1
        "); 


        if( $jv_sql->num_rows()>0 ){
            foreach( $jv_sql->result_array() as $jv_row ){
                if( $jv_row['type'] == 1 ){ ##discount
                    $grand_total -= $jv_row['amount'];
                }else{ ##surcharge
                    $grand_total += $jv_row['amount'];
                }
            }
        }
        //New job_variation tweak end

        //get property varitaion
        $pv_sql = $this->db->query("
                SELECT 
                apv.`type` AS apv_type,
                apv.`amount`,
                apv.`scope`,

                apvr.`reason` AS apvr_reason,

                dv.`display_on`,
                dv.type as display_type,
        
                ajt.`type` AS ajt_type,
                ajt.`short_name`
            FROM `property_variation` AS pv        
            LEFT JOIN `agency_price_variation` AS apv ON ( pv.`agency_price_variation` = apv.`id` AND pv.`property_id` = {$row1['property_id']} )
            LEFT JOIN `agency_price_variation_reason` AS apvr ON apv.`reason` = apvr.`id`
            LEFT JOIN `display_variation` AS dv ON ( apv.`id` = dv.`variation_id` AND dv.type = 1 )
            LEFT JOIN `alarm_job_type` AS ajt ON ( apv.`scope` = ajt.`id` AND apv.`scope` >= 2 )
            WHERE apv.`agency_id` = {$row1['agency_id']}  
            AND (
                apv.`scope` = 1 OR
                apv.`scope` = {$row1['service']}
            )
            ");  

            if( $pv_sql->num_rows()>0 ){

                foreach( $pv_sql->result() as $pv_row ){ 
                    if( $pv_row->apv_type == 1 ){ //discount
                        $grand_total -= $pv_row->amount;
                    }else{ //surcharge
                        $grand_total += $pv_row->amount;
                    }
                }

            }
        //get property varitaion end

        */

        


        return $grand_total;
    }

    /**
     * Insert Logs
     */
    public function insert_log($params) {

        $data = [];

        $data = array(
            'title' => $params['title'],
            'details' => $params['details'],
            'created_date' => date('Y-m-d H:i:s')
        );

        if (isset($params['created_by']) && $params['created_by'] > 0) {
            $data['created_by'] = $params['created_by'];
        }

        if (isset($params['created_by_staff']) && $params['created_by_staff'] > 0) {
            $data['created_by_staff'] = $params['created_by_staff'];
        }

        // ID's
        if (isset($params['job_id']) && $params['job_id'] > 0) {
            $data['job_id'] = $params['job_id'];
        }

        if (isset($params['property_id']) && $params['property_id'] > 0) {
            $data['property_id'] = $params['property_id'];
        }

        if (isset($params['agency_id']) && $params['agency_id'] > 0) {
            $data['agency_id'] = $params['agency_id'];
        }

        // markers
        if (isset($params['display_in_vjd']) && is_numeric($params['display_in_vjd'])) {
            $data['display_in_vjd'] = $params['display_in_vjd'];
        }

        if (isset($params['display_in_vpd']) && is_numeric($params['display_in_vpd'])) {
            $data['display_in_vpd'] = $params['display_in_vpd'];
        }

        if (isset($params['display_in_vad']) && is_numeric($params['display_in_vad'])) {
            $data['display_in_vad'] = $params['display_in_vad'];
        }

        if (isset($params['display_in_portal']) && is_numeric($params['display_in_portal'])) {
            $data['display_in_portal'] = $params['display_in_portal'];
        }

        if (isset($params['display_in_accounts']) && is_numeric($params['display_in_accounts'])) {
            $data['display_in_accounts'] = $params['display_in_accounts'];
        }

        if (isset($params['display_in_accounts_hid']) && is_numeric($params['display_in_accounts_hid'])) {
            $data['display_in_accounts_hid'] = $params['display_in_accounts_hid'];
        }

        if (isset($params['display_in_sales']) && is_numeric($params['display_in_sales'])) {
            $data['display_in_sales'] = $params['display_in_sales'];
        }

        if (isset($params['auto_process']) && is_numeric($params['auto_process'])) {
            $data['auto_process'] = $params['auto_process'];
        }

        if (isset($params['log_type']) && is_numeric($params['log_type'])) {
            $data['log_type'] = $params['log_type'];
        }

        if ($this->db->insert('logs', $data)) {
            return true;
        } else {
            return false;
        }
    }

    // get countries
    public function get_countries($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`countries` AS c');

        // filters
        if (isset($params['country_id'])) {
            $this->db->where('c.`country_id`', $params['country_id']);
        }

        // custom filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
        }

        // sort
        if (isset($params['sort_list'])) {
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }

        // limit
        if (isset($params['limit']) && $params['limit'] > 0) {
            $this->db->limit($params['limit'], $params['offset']);
        }


        $query = $this->db->get();
        if (isset($params['display_query']) && $params['display_query'] == 1) {
            echo $this->db->last_query();
        }

        return $query;
    }

    public function get_user_accounts($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('agency_user_accounts AS aua');
        $this->db->join('agency_user_account_types AS auat', 'aua.`user_type` = auat.`agency_user_account_type_id`', 'left');
        $this->db->join('agency AS a', 'aua.`agency_id` = a.`agency_id`', 'left');
        if (isset($params['active'])) {
            $this->db->where('aua.`active`', $params['active']);
        }
        if (isset($params['user_type'])) {
            $this->db->where('aua.`user_type`', $params['user_type']);
        }
        if (isset($params['email'])) {
            $this->db->where('aua.`email`', $params['email']);
        }
        if (isset($params['agency_id'])) {
            $this->db->where('aua.`agency_id`', $params['agency_id']);
        }
        if (isset($params['aua_id'])) {
            $this->db->where('aua.`agency_user_account_id`', $params['aua_id']);
        }
        if (isset($params['reset_password_code'])) {
            $this->db->where('aua.`reset_password_code`', $params['reset_password_code']);
        }
        if (isset($params['password'])) {
            $this->db->where('aua.`password`', $params['password']);
        }
        $this->db->order_by('aua.fname', 'ASC');
        $this->db->order_by('aua.lname', 'ASC');
        if (isset($params['limit']) && $params['limit'] > 0) {
            $this->db->limit($params['limit'], $params['offset']);
        }
        return $this->db->get();
    }


    function getInvoiceCreditReason($credit_reason_id) {

        if ($credit_reason_id == -1) { // other
            $credit_reason = 'Other';
        } else {
            $credit_reason_sql = $this->getCreditReason($credit_reason_id);
            $credit_reason = $credit_reason_sql['reason'];
        }

        return $credit_reason;
    }

    public function getCreditReason($credit_reason_id = null) {

        $append_str = null;
        if ($credit_reason_id > 0) {
            $append_str = " AND `credit_reason_id` = {$credit_reason_id} ";
        }

        $sql_str = "
            SELECT *
            FROM `credit_reason`
            WHERE `active` = 1
            {$append_str}
        ";

        $sql = $this->db->query($sql_str);
        $row = $sql->row_array();

        return $row;
    }

    function getInvoiceCreditReason_old($ic_reason_id) {

        switch ($ic_reason_id) {
            case 1:
                $reason = 'In Good Faith';
                break;
            case 2:
                $reason = 'Duplicate Charge';
                break;
            case 3:
                $reason = 'Multiple Property Discount';
                break;
            case 4:
                $reason = 'Incorrect Pricing';
                break;
            case 5:
                $reason = 'Agents Property';
                break;
            case 6:
                $reason = 'Write Off BAD DEBT';
                break;
        }

        return $reason;
    }

    function get240vRfAgencyAlarm($agency_id) {
        $sql_str = "
			SELECT `price`
			FROM `agency_alarms`
			WHERE `agency_id` = {$agency_id}
			AND `alarm_pwr_id` = 10
			LIMIT 1
		";
        $sql = $this->db->query($sql_str);
        $row = $sql->row_array();
        return $row['price'];
    }


    function getIcAlarmAgencyService($agency_id) {
        $sql_str = "
            SELECT `price`
            FROM `agency_services`
            WHERE `agency_id` = {$agency_id}
            AND `service_id` = 12
        ";
        $sql = $this->db->query($sql_str);
        $row = $sql->row_array();
        return $row['price'];
    }

    function NLMjobStatusCheck($property_id) {

        $sql = $this->db->query("
			SELECT `id`
			FROM `jobs`
			WHERE `property_id` = {$property_id}
			AND `del_job` = 0
			AND (
				`status` = 'Booked' OR
				`status` = 'Pre Completion' OR
				`status` = 'Merged Certificates'
			)
		");

        if ($sql->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getMainRegion($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`regions` AS r');
        //$this->db->join('`property` AS p', 'j.`property_id` = p.`property_id`', 'left');

        /*
          if( $params['join_table'] > 0 ){

          foreach(  $params['join_table'] as $join_table ){

          if( $join_table == 'job_reason' ){
          $this->db->join('`job_reason` AS jr', 'j.`job_reason_id` = jr.`job_reason_id`', 'left');
          }

          }

          }
         */

        // custom joins
        if (isset($params['custom_joins']) && $params['custom_joins'] != '') {
            $this->db->join($params['custom_joins']['join_table'], $params['custom_joins']['join_on'], $params['custom_joins']['join_type']);
        }

        // filter
        if (isset($params['region_state']) && $params['region_state'] != '') {
            $this->db->where('r.`region_state`', $params['region_state']);
        }

        if (is_numeric($params['country_id'])) {
            $this->db->where('r.`country_id`', $params['country_id']);
        }

        if (is_numeric($params['r_status'])) {
            $this->db->where('r.`status`', $params['r_status']);
        }

        // custom filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
        }


        // group by
        if (isset($params['group_by']) && $params['group_by'] != '') {
            $this->db->group_by($params['group_by']);
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

        // limit
        if (isset($params['limit']) && $params['limit'] > 0) {
            $this->db->limit($params['limit'], $params['offset']);
        }

        $query = $this->db->get();
        if (isset($params['display_query']) && $params['display_query'] == 1) {
            echo $this->db->last_query();
        }

        return $query;
    }

    public function getSubRegion($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }
    
        $this->db->select($sel_query);        
        $this->db->from('`sub_regions` AS sr');
        $this->db->join('`regions` AS r', 'sr.`region_id` = r.`regions_id`', 'left');
    
        // custom joins
        if (isset($params['custom_joins']) && $params['custom_joins'] != '') {
            $this->db->join($params['custom_joins']['join_table'], $params['custom_joins']['join_on'], $params['custom_joins']['join_type']);
        }
    
        // filter
        //country filter > by:Gherx
        $this->db->where('r.country_id', COUNTRY);

        // catching old parameter name for region ID
        if (is_numeric($params['main_region_id'])) {
            $this->db->where('sr.`region_id`', $params['main_region_id']);
        }

        // new region id filter parameter
        if (is_numeric($params['region_id'])) {
            $this->db->where('sr.`region_id`', $params['region_id']);
        }

        if (is_numeric($params['sub_region_id'])) {
            $this->db->where('pc.`sub_region_id`', $params['sub_region_id']);
        }
    
        if (is_numeric($params['active'])) {
            $this->db->where('sr.`active`', $params['active']);
        }
    
        // custom filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
        }
    
    
        // group by
        if (isset($params['group_by']) && $params['group_by'] != '') {
            $this->db->group_by($params['group_by']);
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
    
        // limit
        if (isset($params['limit']) && $params['limit'] > 0) {
            $this->db->limit($params['limit'], $params['offset']);
        }
    
        $query = $this->db->get();
        if (isset($params['display_query']) && $params['display_query'] == 1) {
            echo $this->db->last_query();
        }
    
        return $query;
    }

    public function get_postcodes($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }
    
        $this->db->select($sel_query);        
        $this->db->from('`postcode` AS pc');
        $this->db->join('`sub_regions` AS sr', 'pc.`sub_region_id` = sr.`sub_region_id`', 'left');
        $this->db->join('`regions` AS r', 'sr.`region_id` = r.`regions_id`', 'left');
    
        // custom joins
        if (isset($params['custom_joins']) && $params['custom_joins'] != '') {
            $this->db->join($params['custom_joins']['join_table'], $params['custom_joins']['join_on'], $params['custom_joins']['join_type']);
        }
        
        //country filter > by:Gherx
        $this->db->where('r.country_id', COUNTRY);

        // filter
        if (is_numeric($params['region_id'])) {
            $this->db->where('sr.`region_id`', $params['region_id']);
        }

        // filter
        if (is_numeric($params['sub_region_id'])) {
            $this->db->where('pc.`sub_region_id`', $params['sub_region_id']);
        }     

        // filter
        if ( count($params['sub_region_id_arr']) > 0 ) {
            $this->db->where_in('pc.`sub_region_id`', $params['sub_region_id_arr']);
        }

        // filter
        if ( $params['sub_region_id_imp'] != '' ) {
            $this->db->where("pc.`sub_region_id` IN({$params['sub_region_id_imp']})");
        }
        
        // postcode filter > by Gherx:
        if ( is_numeric($params['postcode']) && $params['postcode']!="" ) {
            $this->db->where('pc.`postcode`', $params['postcode']);
        }    
    
        if (is_numeric($params['delete'])) {
            $this->db->where('pc.`deleted`', $params['delete']);
        }
    
        // custom filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
        }
    
    
        // group by
        if (isset($params['group_by']) && $params['group_by'] != '') {
            $this->db->group_by($params['group_by']);
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
    
        // limit
        if (isset($params['limit']) && $params['limit'] > 0) {
            $this->db->limit($params['limit'], $params['offset']);
        }
    
        $query = $this->db->get();
        if (isset($params['display_query']) && $params['display_query'] == 1) {
            echo $this->db->last_query();
        }
    
        return $query;
    }

    public function getRegionFilterStateListings($params) {

        $rf_class = $params['rf_class'];
        $region_filter_json = $params['region_filter_json'];


        // get distinct state
        if ($rf_class == 'jobs') {
            $this->load->model('jobs_model');
            $state_filter = $this->jobs_model->get_jobs($region_filter_json);
        } else if ($rf_class == 'property') {
            $this->load->model('properties_model');
            $state_filter = $this->properties_model->get_properties($region_filter_json);
        } else if ($rf_class == 'agency') {
            $this->load->model('agency_model');
            $state_filter = $this->agency_model->get_agency($region_filter_json);
        } else if ($rf_class == 'regions') {
            $this->load->model('reports_model');
            $state_filter = $this->reports_model->getRegion($region_filter_json);
        }


        $state_str = null;
        foreach ($state_filter->result() as $index => $state) {

            // get all region that belong to this state
            $sel_query = "r.`regions_id`, r.`region_name`";
            $params = array(
                'sel_query' => $sel_query,
                'region_state' => $state->state,
                'country_id' => $this->config->item('country'),
                'r_status' => 1,
                'display_query' => 0
            );
            $regions_sql = $this->system_model->getMainRegion($params);

            $region_postcodes = [];
            foreach ($regions_sql->result() as $index => $region) {

                // get all postcode that belong to this region
                $sel_query = "pc.`postcode`";                
                $sub_region_params = array(
                    'sel_query' => $sel_query,
                    'region_id' => $region->regions_id,                                
                    'deleted' => 0,
                    'display_query' => 0
                );
                $postcode_sql = $this->get_postcodes($sub_region_params);
                
                foreach ($postcode_sql->result() as $postcode_row) {
                    $region_postcodes[] = $postcode_row->postcode;
                }                    
                
            }

            $postcodes = null;
            $jcount = 0;
            if (count($region_postcodes) > 0) {

                $postcodes = implode(",", $region_postcodes);

                // count
                if ($rf_class == 'jobs') {

                    // get jobs via passed postcode
                    $sel_query = "COUNT(j.`id`) AS jcount";
                    $custom_where = "p.`postcode` IN ({$postcodes})";

                    // assign region json to array to customize query parameter
                    $j_params = $region_filter_json;

                    $j_params['sel_query'] = $sel_query;
                    $j_params['custom_where_arr'][] = $custom_where;
                    $j_params['group_by'] = null;
                    $j_params['display_query'] = 0;

                    $j_sql = $this->jobs_model->get_jobs($j_params);
                    $j_row = $j_sql->row();
                    $jcount = $j_row->jcount;
                } else if ($rf_class == 'property') {

                    // get jobs via passed postcode
                    $sel_query = "COUNT(p.`property_id`) AS pcount";
                    $custom_where = "p.`postcode` IN ({$postcodes})";

                    // assign region json to array to customize query parameter
                    $j_params = $region_filter_json;

                    $j_params['sel_query'] = $sel_query;
                    $j_params['custom_where_arr'][] = $custom_where;
                    $j_params['group_by'] = null;
                    $j_params['display_query'] = 0;

                    $j_sql = $this->properties_model->get_properties($j_params);
                    $j_row = $j_sql->row();
                    $jcount = $j_row->pcount;
                } else if ($rf_class == 'agency') {

                    // get jobs via passed postcode
                    $sel_query = "COUNT(a.`agency_id`) AS acount";
                    $custom_where = "a.`postcode` IN ({$postcodes})";

                    // assign region json to array to customize query parameter
                    $j_params = $region_filter_json;

                    $j_params['sel_query'] = $sel_query;
                    $j_params['custom_where_arr'][] = $custom_where;
                    $j_params['group_by'] = null;
                    $j_params['display_query'] = 0;

                    $j_sql = $this->agency_model->get_agency($j_params);
                    $j_row = $j_sql->row();
                    $jcount = $j_row->acount;
                } else if ($rf_class == 'regions') {

                    // get jobs via passed postcode
                    //$sel_query = "COUNT(pr.`postcode_region_id`) AS pr_count";
                    //$custom_where = "pr.`postcode_region_postcodes` IN ({$postcodes})";
                    #new table > by:Gherx
                    $sel_query = "COUNT(sr.`sub_region_id`) AS pr_count";
                    $custom_where = "pc.`postcode` IN ({$postcodes})";

                    // assign region json to array to customize query parameter
                    $j_params = $region_filter_json;

                    $j_params['sel_query'] = $sel_query;
                    $j_params['custom_where_arr'][] = $custom_where;
                    $j_params['group_by'] = null;
                    $j_params['display_query'] = 0;

                    $j_sql = $this->reports_model->getRegion($j_params);
                    $j_row = $j_sql->row();
                    $jcount = $j_row->pr_count;
                }
            }

            if ($jcount > 0 && $state->state != '') {

                $state_str .= '
					<div class="checkbox state_div">

						<input type="checkbox" id="chk_state_' . $state->state . '" name="state_ms[]" class="state_ms" value="' . $state->state . '">
						<label for="chk_state_' . $state->state . '" class="rf_state_lbl">' . $state->state . ' ' . ( ( $jcount > 0 ) ? "({$jcount})" : null ) . '</label>

						<div class="region_div"></div>

					</div>
				';
            }
        }

        echo $state_str;
    }

    public function getMainRegionListings($params) {

        $state = $params['state'];
        $rf_class = $params['rf_class'];
        $region_filter_json = $params['region_filter_json'];

        $region_str = null;
        // get main region
        $sel_query = "r.`regions_id`, r.`region_name`";
        $params = array(
            'sel_query' => $sel_query,
            'region_state' => $state,
            'r_status' => 1,
            'sort_list' => array(
                array(
                    'order_by' => 'r.`region_name`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $regions_sql = $this->getMainRegion($params);



        foreach ($regions_sql->result() as $index => $region) {

            $region_postcodes = [];                        

            // get all postcode that belong to this region
            $sel_query = "pc.`postcode`";                
            $sub_region_params = array(
                'sel_query' => $sel_query,
                'region_id' => $region->regions_id,                                
                'deleted' => 0,
                'display_query' => 0
            );
            $postcode_sql = $this->get_postcodes($sub_region_params);
            
            foreach ($postcode_sql->result() as $postcode_row) {
                $region_postcodes[] = $postcode_row->postcode;
            }

            $postcodes = null;
            $jcount = 0;
            if (count($region_postcodes) > 0) {

                $postcodes = implode(",", $region_postcodes);

                // count
                if ($rf_class == 'jobs') {

                    $this->load->model('jobs_model');

                    // get jobs via passed postcode
                    $sel_query = "COUNT(j.`id`) AS jcount";
                    $custom_where = "p.`postcode` IN ({$postcodes})";

                    // assign region jason to array to customize query parameter
                    $j_params = $region_filter_json;

                    $j_params['sel_query'] = $sel_query;
                    $j_params['custom_where_arr'][] = $custom_where;
                    $j_params['group_by'] = null;
                    $j_params['display_query'] = 0;

                    $j_sql = $this->jobs_model->get_jobs($j_params);
                    $j_row = $j_sql->row();
                    $jcount = $j_row->jcount;
                } else if ($rf_class == 'property') {

                    $this->load->model('properties_model');

                    // get jobs via passed postcode
                    $sel_query = "COUNT(p.`property_id`) AS pcount";
                    $custom_where = "p.`postcode` IN ({$postcodes})";

                    // assign region jason to array to customize query parameter
                    $j_params = $region_filter_json;

                    $j_params['sel_query'] = $sel_query;
                    $j_params['custom_where_arr'][] = $custom_where;
                    $j_params['group_by'] = null;
                    $j_params['display_query'] = 0;

                    $j_sql = $this->properties_model->get_properties($j_params);
                    $j_row = $j_sql->row();
                    $jcount = $j_row->pcount;
                } else if ($rf_class == 'agency') {
                    $this->load->model('agency_model');

                    // get jobs via passed postcode
                    $sel_query = "COUNT(a.`agency_id`) AS acount";
                    $custom_where = "a.`postcode` IN ({$postcodes})";

                    // assign region jason to array to customize query parameter
                    $j_params = $region_filter_json;

                    $j_params['sel_query'] = $sel_query;
                    $j_params['custom_where_arr'][] = $custom_where;
                    $j_params['group_by'] = null;
                    $j_params['display_query'] = 0;

                    $j_sql = $this->agency_model->get_agency($j_params);
                    $j_row = $j_sql->row();
                    $jcount = $j_row->acount;
                } else if ($rf_class == 'regions') {
                    $this->load->model('reports_model');

                    // get jobs via passed postcode
                   // $sel_query = "COUNT(pr.`postcode_region_id`) AS pr_count";
                   // $custom_where = "pr.`postcode_region_postcodes` IN ({$postcodes})";
                    #new table :by:Gherx
                    $sel_query = "COUNT(sr.`sub_region_id`) AS pr_count";
                    $custom_where = "pc.`postcode` IN ({$postcodes})";

                    // assign region json to array to customize query parameter
                    $j_params = $region_filter_json;

                    $j_params['sel_query'] = $sel_query;
                    $j_params['custom_where_arr'][] = $custom_where;
                    $j_params['group_by'] = null;
                    $j_params['display_query'] = 0;

                    $j_sql = $this->reports_model->getRegion($j_params);
                    $j_row = $j_sql->row();
                    $jcount = $j_row->pr_count;
                }
            }



            if ($jcount > 0) {
                $region_str .= '
					<div class="checkbox region_div_chk">
						<input type="checkbox" id="chk_region_' . $region->regions_id . '" name="region_ms[]" class="region_ms" value="' . $region->regions_id . '">
						<label for="chk_region_' . $region->regions_id . '" class="rf_region_lbl">' . $region->region_name . ' ' . ( ( $jcount > 0 ) ? "({$jcount})" : null ) . '</label>

						<div class="sub_region_div"></div>
					</div>
				';
            }
        }

        return $region_str;
    }

    public function getSubRegionListings($params) {

        $region_id = $params['region_id'];
        $rf_class = $params['rf_class'];
        $region_filter_json = $params['region_filter_json'];

        // get sub regions
        $sel_query = "sr.`sub_region_id`, sr.`subregion_name`";
        $params = array(
            'sel_query' => $sel_query,
            'main_region_id' => $region_id,
            'country_id' => $this->config->item('country'),
            'active' => 1,
            'sort_list' => array(
                array(
                    'order_by' => 'sr.`subregion_name`',
                    'sort' => 'ASC',
                )
            ),
            'display_query' => 0
        );
        $sub_regions_sql = $this->getSubRegion($params);

        $sub_region_str = null;
        $jcount = 0;
        foreach ($sub_regions_sql->result() as $index => $sub_region) {

            $region_postcodes = [];

            // get all postcode that belong to this region
            $sel_query = "pc.`postcode`";                
            $sub_region_params = array(
                'sel_query' => $sel_query,
                'region_id' => $region_id, 
                'sub_region_id' => $sub_region->sub_region_id,                               
                'deleted' => 0,
                'display_query' => 0
            );
            $postcode_sql = $this->get_postcodes($sub_region_params);
            
            foreach ($postcode_sql->result() as $postcode_row) {
                $region_postcodes[] = $postcode_row->postcode;
            }

            if( count($region_postcodes) > 0 ){
                $postcodes = implode(",", $region_postcodes);

                if ($rf_class == 'jobs') {

                        $this->load->model('jobs_model');

                        // get jobs via passed postcode
                        $sel_query = "COUNT(j.`id`) AS jcount";
                        $custom_where = "p.`postcode` IN ({$postcodes})";

                        // assign region jason to array to customize query parameter
                        $j_params = $region_filter_json;

                        $j_params['sel_query'] = $sel_query;
                        $j_params['custom_where_arr'][] = $custom_where;
                        $j_params['group_by'] = null;
                        $j_params['display_query'] = 0;

                        $j_sql = $this->jobs_model->get_jobs($j_params);
                        $j_row = $j_sql->row();
                        $jcount = $j_row->jcount;
                    

                } else if ($rf_class == 'property') {

                        $this->load->model('properties_model');

                        // get jobs via passed postcode
                        $sel_query = "COUNT(p.`property_id`) AS pcount";
                        $custom_where = "p.`postcode` IN ({$postcodes})";

                        // assign region jason to array to customize query parameter
                        $j_params = $region_filter_json;

                        $j_params['sel_query'] = $sel_query;
                        $j_params['custom_where_arr'][] = $custom_where;
                        $j_params['group_by'] = null;
                        $j_params['display_query'] = 0;

                        $j_sql = $this->properties_model->get_properties($j_params);
                        $j_row = $j_sql->row();
                        $jcount = $j_row->pcount;
                    
                } else if ($rf_class == 'agency') {

                        $this->load->model('agency_model');

                        // get jobs via passed postcode
                        $sel_query = "COUNT(a.`agency_id`) AS acount";
                        $custom_where = "a.`postcode` IN ({$postcodes})";

                        // assign region jason to array to customize query parameter
                        $j_params = $region_filter_json;

                        $j_params['sel_query'] = $sel_query;
                        $j_params['custom_where_arr'][] = $custom_where;
                        $j_params['group_by'] = null;
                        $j_params['display_query'] = 0;

                        $j_sql = $this->agency_model->get_agency($j_params);
                        $j_row = $j_sql->row();
                        $jcount = $j_row->acount;
                    

                } else if ($rf_class == 'regions') {

                        $this->load->model('reports_model');

                        // get jobs via passed postcode
                        //$sel_query = "COUNT(pr.`postcode_region_id`) AS pr_count";
                        //$custom_where = "pr.`postcode_region_postcodes` IN ({$postcodes})";
                        #new table
                        $sel_query = "COUNT(sr.`sub_region_id`) AS pr_count";
                        $custom_where = "pc.`postcode` IN ({$postcodes})";

                        // assign region json to array to customize query parameter
                        $j_params = $region_filter_json;

                        $j_params['sel_query'] = $sel_query;
                        $j_params['custom_where_arr'][] = $custom_where;
                        $j_params['group_by'] = null;
                        $j_params['display_query'] = 0;

                        $j_sql = $this->reports_model->getRegion($j_params);
                        $j_row = $j_sql->row();
                        $jcount = $j_row->pr_count;
                    
                }

                if ($jcount > 0) {
                    $sub_region_str .= '
                        <div class="checkbox sub_region_div_chk">
                            <input type="checkbox" id="chk_sub_region_' . $sub_region->sub_region_id . '" name="sub_region_ms[]" class="sub_region_ms" value="' . $sub_region->sub_region_id . '">
                            <label for="chk_sub_region_' . $sub_region->sub_region_id . '" class="rf_sub_region_lbl">' . $sub_region->subregion_name . ' ' . ( ( $jcount > 0 ) ? "({$jcount})" : null ) . '</label>
                        </div>
                    ';
                }
            }

            
        }

        return $sub_region_str;
    }

    public function getAllPostcodes($sub_region_params) {

        $sub_regions_sql = $this->getSubRegion($sub_region_params);

        if ($sub_regions_sql->num_rows() > 0) {

            foreach ($sub_regions_sql->result() as $index => $sub_region) {
                if ($sub_region->postcode_region_postcodes != '') {
                    $postcodes_arr[] = explode(",", trim($sub_region->postcode_region_postcodes));
                }
            }

            $rejoin_arr = [];
            foreach ($postcodes_arr as $pc) {
                // remove empty
                $pc2 = array_filter($pc);
                $rejoin_arr[] = implode(",", $pc2);
            }

            return $postcodes = implode(",", $rejoin_arr);
        } else {

            return false;
        }
    }

    public function getPostCodeViaSubRegion($sub_region_ms) {

        // remove empty array through array_filter
        $sub_region_ids = implode(",", array_filter($sub_region_ms));

        /*
        $sel_query = "pr.`postcode_region_postcodes`";
        $sub_region_params = array(
            'sel_query' => $sel_query,
            'sub_region_ids' => $sub_region_ids,
            'country_id' => $this->config->item('country'),
            'pr_deleted' => 0,
            'display_query' => 0
        );
        $sub_regions_sql = $this->getSubRegion($sub_region_params);
        */

        $sel_query = "pc.`postcode`";
        $custom_where = "pc.`sub_region_id` IN({$sub_region_ids})";
        $sub_region_params = array(
            'sel_query' => $sel_query,
            'custom_where' => $custom_where,            
            'country_id' => $this->config->item('country'),
            'deleted' => 0,
            'display_query' => 0
        );
        $postcode_sql = $this->get_postcodes($sub_region_params);
        
        $postcodes_arr = [];
        foreach ($postcode_sql->result() as $postcode_row) {
            $postcodes_arr[] = $postcode_row->postcode;
        }

        return implode(",", $postcodes_arr);
       
    }

    public function getServiceTypes($params) {

        // select
        if ($params['sel_query'] != "") {
            $this->db->select($params['sel_query']);
        } else {
            $this->db->select('*');
        }

        // from
        $this->db->from('`alarm_job_type` AS ajt');

        // filters
        if (isset($params['ajt_id']) && $params['ajt_id'] != '') {
            $this->db->where('ajt.`id`', $params['ajt_id']);
        }
        if (isset($params['bundle']) && $params['bundle'] != '') {
            $this->db->where('ajt.`bundle`', $params['bundle']);
        }
        if (isset($params['active']) && $params['active'] != '') {
            $this->db->where('ajt.`active`', $params['active']);
        }

        // custom filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
        }

        // sort
        if (isset($params['sort_list'])) {
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }

        $query = $this->db->get();
        if (isset($params['display_query']) && $params['display_query'] == 1) {
            echo $this->db->last_query();
        }

        return $query;
    }

    function getToolsLastInspection($params) {

        if ($params['item'] == 1) { // if ladder
            return $this->db->query("
				SELECT *
				FROM `ladder_check`
				WHERE `tools_id` = {$params['tools_id']}
				ORDER BY `date` DESC
				LIMIT 1
			");
        } else if ($params['item'] == 2) { // if drill
            return $this->db->query("
				SELECT *
				FROM `test_and_tag`
				WHERE `tools_id` = {$params['tools_id']}
				ORDER BY `date` DESC
				LIMIT 1
			");
        } else if ($params['item'] == 4) { // if drill
            return $this->db->query("
				SELECT *
				FROM `lockout_kit_check`
				WHERE `tools_id` = {$params['tools_id']}
				ORDER BY `date` DESC
				LIMIT 1
			");
        }
    }

    public function getServiceTypeStatus($params) {

        $service = 'N/A';

        $sel_query = "ps.`service`";
        $pm_array = array(
            'sel_query' => $sel_query,
            'property_id' => $params['property_id'],
            'ajt_id' => $params['ajt_id'],
            'display_query' => 0
        );
        $pm_sql = $this->properties_model->getPropertyServices($pm_array);
        $pm_row = $pm_sql->row();

        switch ($pm_row->service) {
            case 0:
                $service = 'DIY';
                break;
            case 1:
                $service = 'SATS';
                break;
            case 2:
                $service = 'No Response';
                break;
            case 3:
                $service = 'Other Provider';
                break;
        }

        return $service;
    }

    /**
     * Get driver
     * @params array
     * return query
     */
    public function getDriver() {

        $this->db->select('sa.StaffID, sa.FirstName, sa.LastName');
        $this->db->from('staff_accounts as sa');
        $this->db->join('country_access as ca', 'ca.staff_accounts_id = sa.StaffID', 'left');
        $this->db->where('ca.country_id', $this->config->item('country'));
        $this->db->where('sa.Deleted', 0);
        $this->db->where('sa.active', 1);
        $this->db->order_by('sa.FirstName', 'ASC');
        $this->db->order_by('sa.LastName', 'ASC');
        $query = $this->db->get();
        return $query;
    }

    function ra_job_type_count($from_date, $to_date, $tech, $job_type = '', $country_id) {

        if ($from_date != "" && $to_date != "") {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $to_date)));
        } else {
            $from_date = date('Y-m-d');
            $to_date = date('Y-m-d');
        }

        $this->db->select('COUNT(j.id) AS num_jobs, j.job_type');
        $this->db->from('jobs AS j');
        $this->db->join('property as p', 'j.property_id = p.property_id', 'left');
        $this->db->join('agency as a', 'p.agency_id = a.agency_id', 'left');
        $this->db->where('j.date >=', $from_date);
        $this->db->where('j.date <=', $to_date);
        $this->db->where('p.deleted', 0);
        $this->db->where("( p.is_nlm = 0 OR p.is_nlm IS NULL )");
        $this->db->where('a.status', 'active');
        $this->db->where('j.del_job', 0);
        $this->db->where('a.country_id', $country_id);
        $this->db->where('j.status', 'Completed');
        $this->db->where_in('j.job_type', $job_type);

        if ($tech != "") {
            $this->db->where('j.assigned_tech', $tech);
        }

        $this->db->group_by('j.job_type');

        $query = $this->db->get();

        return $query;
    }

    /**
     * AL: get rebook count only > (240 rebook marked) > any job_type
     */
    function ra_job_type_count_2($params) {

        /*if ($from_date != "" && $to_date != "") {
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $from_date)));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $to_date)));
        } else {
            $from_date = date('Y-m-d');
            $to_date = date('Y-m-d');
        }*/

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        if($params['from_date']!="" && $params['to_date']!=""){
            $from_date = date('Y-m-d', strtotime(str_replace('/', '-', $params['from_date'])));
            $to_date = date('Y-m-d', strtotime(str_replace('/', '-', $params['to_date'])));
        }else{
            $from_date = date('Y-m-d');
            $to_date = date('Y-m-d');
        }

        //$this->db->select('COUNT(DISTINCT j.id ) AS num_jobs, j.job_type');
        $this->db->select($sel_query);
        $this->db->from('jobs AS j');
        $this->db->join('property as p', 'j.property_id = p.property_id', 'left');
        $this->db->join('agency as a', 'p.agency_id = a.agency_id', 'left');

        if ($params['assigned_tech'] != 2) {
            $this->db->join('job_markers as jm', 'j.id = jm.job_id', 'left');
        }

        $this->db->where('p.deleted', 0);
        $this->db->where("( p.is_nlm = 0 OR p.is_nlm IS NULL )");
        $this->db->where('a.status', 'active');
        $this->db->where('j.del_job', 0);
        //$this->db->where('j.is_eo', 1);
        $this->db->where('a.country_id', COUNTRY);
        $this->db->where('j.status', 'Completed');

        if ($params['assigned_tech'] != 2 && $params['upfront_flag != 1']) {
            $this->db->where('jm.job_type_change', 1); ## added marked 240 rebook
        }

        $this->db->where('j.date >=', $from_date);
        $this->db->where('j.date <=', $to_date);

        if($params['ajt_id'] && $params['ajt_id']!=""){
            $this->db->where('j.service', $params['ajt_id']);
        }

        if($params['job_type_change'] && $params['job_type_change']!=""){
            $this->db->where('jm.job_type_change', $params['job_type_change']);
        }

        if($params['ahc_agency_id'] && $params['ahc_agency_id']!=""){
            $this->db->where('a.agency_id', $params['ahc_agency_id']);
        }

        if($params['is_eo'] != 0){
            $this->db->where('j.is_eo', $params['is_eo']);
        }
      
        if( $params['min'] >= 0 && $params['max'] > 0 ){
            if ( $params['min'] == $params['max'] ) {
                $cr_str = "CAST( j.`created` AS DATE ) <= DATE_SUB( j.`date` , INTERVAL {$params['min']} DAY ) ";
            } else {
                $cr_str = "( CAST( j.`created` AS DATE ) BETWEEN DATE_SUB( j.`date` , INTERVAL {$params['max']} DAY ) AND DATE_SUB( j.`date` , INTERVAL {$params['min']} DAY ) ) ";
            }

            $this->db->where($cr_str);
        }

        if ($params['tech'] != "") {
            $this->db->where('j.assigned_tech', $params['tech']);
        }

        if ($params['assigned_tech'] == 2) {
            $this->db->where('j.assigned_tech', $params['assigned_tech']);
        }

       // $this->db->group_by('j.job_type');

        $query = $this->db->get();

        //echo $this->db->last_query();
        //exit();

        return $query;
    }

    /**
     * Get All Sales Rep
     */
    public function getSalesRep() {

        $this->db->select('DISTINCT(ca.staff_accounts_id), sa.FirstName, sa.LastName ');
        $this->db->from('staff_accounts as sa');
        $this->db->join('country_access as ca', 'ca.staff_accounts_id = sa.StaffID', 'INNER');
        $this->db->where('ca.country_id', $this->config->item('country'));
        $this->db->where('sa.deleted', 0);
        $this->db->where('sa.active', 1);
        $this->db->where('sa.ClassID', 5);
        
        // Include Shaquille Smith to SalesRep dropdown
        $this->db->or_where('
            (
                sa.staffID = 2296 AND ca.country_id = 1
            )
        ');
        $this->db->order_by('sa.FirstName');
        $query = $this->db->get();

        return $query;
    }

    /**
     * Get All Agency State
     */
    public function getAllAgencyState() {

        $this->db->select('DISTINCT(a.state)');
        $this->db->from('agency as a');
        $this->db->where('a.country_id', $this->config->item('country'));
        $this->db->where('a.state !=', '');
        $this->db->order_by('a.state');
        return $this->db->get();
    }

    /**
     * Get notification count
     */
    public function getOverallNotification($params) {

        $filter_arr = array();

        $filter_arr[] = "AND n.`active` = 1";
        $filter_arr[] = "AND n.`deleted` = '0'";

        if ($params['notify_to'] != "") {
            $filter_arr[] = "AND n.`notify_to` = {$params['notify_to']}";
        }

        if (is_numeric($params['read'])) {
            $filter_arr[] = "AND n.`read` = {$params['read']}";
        }

        if ($params['notf_type']) {
            $filter_arr[] = "AND n.`notf_type` = {$params['notf_type']}";
        }

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . substr(implode(" ", $filter_arr), 3);
        }

        $sel_str = "*";

        if ($params['return_count'] == 1) {
            $sel_str = " COUNT(notifications_id) AS jcount ";
        } else if ($params['distinct'] != "") {
            switch ($params['distinct']) {
                case 'j.`job_type`':
                    $sel_str = " DISTINCT j.`job_type` ";
                    break;
                case 'j.`service`':
                    $sel_str = " DISTINCT j.`service`, ajt.`id` , ajt.`type` ";
                    break;
            }
        } else if ($params['sum_age'] == "1") {
            $sel_str = " SUM( DATEDIFF( '" . date('Y-m-d') . "', CAST( j.`created` AS DATE ) ) ) AS sum_age ";
        }

        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }

        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = $this->db->query("
			SELECT {$sel_str}
			FROM `notifications` AS n
			{$join_tbl_str}
			{$filter_str}
			{$custom_filter_str}
			{$group_by_str}
			{$sort_str}
			{$pag_str}
		");

        if ($params['return_count'] == 1) {
            $j_sql = $sql->result_array();
            return $j_sql[0]['jcount'];
        } else {
            return $sql->result_array();
        }
    }

    /**
     * Get mark notification as read
     */
    public function updateNotifiationRead($notifIds) {
        $updateData = array('read' => 1);
        $this->db->where("notifications_id IN (" . implode(',', $notifIds) . ")", NULL, false);
        $this->db->update('notifications', $updateData);
    }

    // compute check digit
    public function getCheckDigit($number) {

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

    /**
     * Check if VIP
     * param staff id
     * return boolean
     */
    public function isVIP($staff_id) {

        $vip = array(2025, 2056, 11, 2155); //add/edit VIP value
        if (in_array($staff_id, $vip)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get Alarms
     */
    public function getAlarms($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('alarm as alrm');
        $this->db->join('jobs as j', 'j.id = alrm.job_id', 'left');


        //Join Tables
        if ($params['join_table'] && $params['join_table'] > 0) {
            foreach ($params['join_table'] as $join_table) {

                //join property table
                if ($join_table == 'property') {
                    $this->db->join('property as p', 'p.property_id = j.property_id', 'left');
                }

                //join agency table
                if ($join_table == 'agency') {
                    $this->db->join('agency as a', 'a.agency_id = p.agency_id', 'left');
                }
            }
        }


        //FILTERS HERE...

        /*if ($params['p_deleted'] && $params['p_deleted'] != "") {
            $this->db->where('p.deleted', $params['p_deleted']);
        }*/
        ##copied from jobs model (Marks idea)
        if (is_numeric($params['p_deleted'])) {
            $this->db->where('p.`deleted`', $params['p_deleted']);
            if($params['p_deleted'] == 0){
                $this->db->where('( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )');
            }
        }
        if ($params['a_status'] && $params['a_status'] != "") {
            $this->db->where('a.status', $params['a_status']);
        }
        if ($params['del_job'] && $params['del_job'] != "") {
            $this->db->where('j.del_job', $params['del_job']);
        }

        //new_alarm
        if ($params['new_alarm'] == 1) {
            $this->db->where('alrm.new', 1);
        }

        //query_for_estimated_income
        if ($params['query_for_estimated_income'] == 1) {
            $query_for_estimated_income_where = " ( ( j.`status` = 'Booked' AND j.`door_knock` != 1 ) OR j.`status` = 'Completed'  OR j.`status` = 'Merged Certificates' ) ";
            $this->db->where($query_for_estimated_income_where);
        }

        //country id
        if ($params['country_id'] && $params['country_id'] != "") {
            $this->db->where('a.country_id', $params['country_id']);
        }

        // Date filters
        if (isset($params['date']) && $params['date'] != '') {
            $this->db->where('j.`date`', $params['date']);
        }




        // custom where filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
        }

        // custom where filter Array
        if (isset($params['custom_where_arr'])) {
            foreach ($params['custom_where_arr'] as $index => $custom_where) {
                if ($custom_where != '') {
                    $this->db->where($custom_where);
                }
            }
        }


        $query = $this->db->get();

        if (isset($params['print_query']) && $params['print_query'] == 1) {
            echo $this->db->last_query();
        }

        return $query;
    }

    public function getStaffClasses($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('staff_classes');

        if (is_numeric($params['active'])) {
            $this->db->where('active', $params['active']);
        }

        // custom where filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
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

    public function getStaffClassID() {

        $this->load->model('staff_accounts_model');

        // get staff class ID
        $params = array(
            'sel_query' => 'sa.`ClassID`',
            'staff_id' => $this->session->staff_id,
            'active' => 1,
            'deleted' => 0,
            'display_query' => 0
        );

        // get user details
        $user_account_sql = $this->staff_accounts_model->get_staff_accounts($params);
        $staff_row = $user_account_sql->row();

        return $staff_row->ClassID;
    }

    function tester() {

        if( $this->config->item('country') == 1 ){ // AU

            if( ENVIRONMENT == 'production' ){ // live

                /*
                2070 - Developer testing
                2025 - Daniel
                11 - Ness
                2287 - Ben Taylor
                2056 - Robert Bell
                2175 - Thalia Paki
                2478 - Simon A
                */

                return array(2070, 2025, 11, 2287, 2056, 2175, 2478);

            }else{ // dev

                /*
                2070 - Developer testing
                2025 - Daniel
                11 - Ness
                2287 - Ben Taylor
                2056 - Robert Bell
                2175 - Thalia Paki
                2221 - Simon A
                */
                return array(2070, 2025, 11, 2287, 2056, 2175, 2221);

            }

            

        }else if( $this->config->item('country') == 2 ){ // NZ

            /*
            2070 - Developer testing
            2025 - Daniel
            11 - Ness
            2231 - Ben Taylor
            2056 - Robert Bell
            2193 - Thalia Paki
            2322 - Simon A
            */

            return array(2070, 2025, 11, 2231, 2056, 2193, 2322);

        }

    }

    function big_3() {

        if( $this->config->item('country') == 1 ){ // AU
                    
            /*
            2070 - Developer testing
            2025 - Daniel
            2287 - Ben Taylor
            11 - Ness
            2189 - Gavin Coulson
            2296 - Shaquille Smith
            */

            return array(2070, 2025, 2287, 11, 2189, 2296);

        }else if( $this->config->item('country') == 2 ){ // NZ

            /*
            2070 - Developer testing
            2025 - Daniel
            2231 - Ben Taylor
            2202 - Gavin Coulson
            2259 - Shaquille Smith
            */

            return array(2070, 2025, 2231, 2202, 2259);

        }

    }

    function getServiceCount($agency_id, $ajt) {

        $sql = $this->db->query("
			SELECT count(ps.`property_services_id`) as jcount
			FROM `property_services` AS ps
			LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE p.`agency_id` ={$agency_id}
			AND p.`deleted` =0
			AND a.`status` = 'active'
			AND ps.`alarm_job_type_id` ={$ajt}
			AND ps.`service` =1
            AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
		");

        $row = $sql->row_array();
        return $row['jcount'];
    }

    public function access_check() {

        if ($this->router->directory == "api/") {
            return;
        }

        $controller = $this->router->fetch_class();
        $method = $this->router->fetch_method();

        $url = "{$controller}/{$method}";

        // exclude page
        $pages = array(
            'login/index',
            'login/authenticate',

            'test/session',                        
            'test/php_info',
            'test/process_nsw_pending_jobs_of_june_1_2021',

            'sys/link_user_to_ci',

            'pdf/view_combined',
            'pdf/view_invoice',
            'pdf/view_quote',
            'pdf/view_certificate',
            'pdf/entry_notice',

            'jobs/pre_completion',
            'reports/property_gained_and_lost',

            'console/catch_webhook_data',
            'sms/yabbr_catch_callback',
            'sms/yabbr_catch_reply'
            //'sms/yabbr_catch_callback_test_switch',
            //'sms/yaabr_test',
            //'sms/test_yabbr_catch_callback',
            //'sms/test_yabbr_catch_reply'
        );

        // exclude controller
        $ex_controller = array(
            'cronjobs'
        );

        if( !in_array($url, $pages) && !in_array($controller, $ex_controller) ){

            // session active
            if (isset($this->session->staff_id) && $this->session->staff_id > 0){

                // old school testers
                $tester_arr = []; 
                $tester_arr = $this->tester();

                // devs
                $devs_sql = $this->db->query("
                SELECT `StaffID`
                FROM `staff_accounts`
                WHERE `ClassID` = 11
                ");
                $devs_arr = []; 
                foreach( $devs_sql->result() as $devs_row ){
                    $devs_arr[] = $devs_row->StaffID;
                }

                $merge_ids = array_merge($tester_arr,$devs_arr);

                $allowed_direct_access = array_unique($merge_ids);
                
                // get logged user staff class
                $logged_user_class_id = $this->getStaffClassID();
                
                // prevent direct access to non testers 
                // never allow password page to be direct accessed 
                if ( !in_array($this->session->staff_id, $allowed_direct_access) || $url == 'admin/passwords' ){ 

                    // do not allow direct url acces (copy paste url)
                    if (!isset($_SERVER['HTTP_REFERER'])) {

                        // php redirect won't work. i have to use js :(
                        if( $logged_user_class_id == 6 ){ // techs
                            echo "<script>window.location='/home/index'</script>"; 
                        }else{ // staff
                            echo "<script>window.location='/jobs'</script>";
                        }                        

                    }

                }

            }else{ // if session expired
                // kick out
                $this->session->sess_destroy();
                redirect('/');
                exit();
            }
        }

    }

    // Custom cURL
    public function jcustom_curl($params) {

        $ch = curl_init();

        // parameters
        $data = $params['data'];
        $data_string = json_encode($data);

        // define options
        $optArray = array(
            CURLOPT_URL => $params['url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CUSTOMREQUEST => $params['post_get'],
            CURLOPT_HTTPHEADER => $params['header']
        );

        if (!empty($params['data'])) {
            $optArray[CURLOPT_POSTFIELDS] = $data_string;
        }

        if ($params['display_cron_options'] == 1) {
            echo "<pre>";
            print_r($optArray);
            echo "</pre>";
        }

        // apply those options
        curl_setopt_array($ch, $optArray);

        // Execute
        $output = curl_exec($ch);

        // decode json
        $result_json = json_decode($output);

        if ($params['display_json'] == 1) {
            echo "<pre>";
            print_r($result_json);
            echo "</pre>";
        }

        curl_close($ch); // Close curl handle

        return $result_json;
    }

    public function getStateViaCountry() {

        $this->db->select('*');
        $this->db->from('`states_def`');
        $this->db->where('country_id', $this->config->item('country'));
        $query = $this->db->get();
        return $query;
    }

    public function getHomeTotals() {

        $arr = array();

        $query = "select count(*) from property  where (service ='1' and deleted <> 1 AND agency_id > 0);";
        $result = $this->db->query($query)->row_array();
        $arr[0] = $result['count(*)'];

        $query = "select count(*) from property where (service ='0' and deleted <> 1 AND agency_id > 0);";
        $result = $this->db->query($query)->row_array();
        $arr[1] = $result['count(*)'];

        //$query = "select count(*) from agency;";
        $query = "select count(*) from agency where (status ='active') AND `country_id` = {$this->config->item('country')};";
        $result = $this->db->query($query)->row_array();
        $arr[2] = $result['count(*)'];

        $query = "select count(*) from property where (service ='2' and deleted <> 1 AND agency_id > 0);";
        $result = $this->db->query($query)->row_array();
        $arr[3] = $result['count(*)'];

        $query = "select count(*) from property where (service ='3' and deleted <> 1 AND agency_id > 0);";
        $result = $this->db->query($query)->row_array();
        $arr[4] = $result['count(*)'];


        return $arr;
    }

    public function getGoogleMapCoordinates($address) {

        $coordinates = array();

        // init curl object
        $ch = curl_init();

        $API_key = $this->config->item('gmap_api_key');

        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . rawurlencode($address) . "&key={$API_key}";

        // define options
        $optArray = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false
        );

        // apply those options
        curl_setopt_array($ch, $optArray);

        // execute request and get response
        $result = curl_exec($ch);


        $result_json = json_decode($result);

        $lat = $result_json->results[0]->geometry->location->lat;
        $lng = $result_json->results[0]->geometry->location->lng;


        $coordinates['lat'] = $lat;
        $coordinates['lng'] = $lng;

        curl_close($ch);

        return $coordinates;
    }


    public function getGoogleMapCoordinates_v2($address) {

        $coordinates = array();

        // init curl object
        $ch = curl_init();

        $API_key = $this->config->item('gmap_api_key');

        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . rawurlencode($address) . "&key={$API_key}";

        // define options
        $optArray = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false
        );

        // apply those options
        curl_setopt_array($ch, $optArray);

        // execute request and get response
        $result = curl_exec($ch);


        $result_json = json_decode($result);

        $lat = $result_json->results[0]->geometry->location->lat;
        $lng = $result_json->results[0]->geometry->location->lng;


        $coordinates['lat'] = $lat;
        $coordinates['lng'] = $lng;

        curl_close($ch);

        //return $coordinates;

        return $result_json;

    }


    public function update_coordinate_using_gmap($property_id) {

        if( $property_id > 0 ){

            // get property
            $prop_sql_str = "
            SELECT 
                `property_id`,
                `address_1`,
                `address_2`,
                `address_3`,
                `state`,
                `postcode`
            FROM `property`
            WHERE `property_id` = {$property_id}
            ";   
            
            $prop_sql = $this->db->query($prop_sql_str);
            $prop_row = $prop_sql->row();

           
            $address = "{$prop_row->address_1} {$prop_row->address_2} {$prop_row->address_3} {$prop_row->state} {$prop_row->postcode}";                                                  
            $coordinate = $this->getGoogleMapCoordinates($address);	                            

            if( $prop_row->property_id > 0 && $coordinate['lat'] != '' && $coordinate['lng'] != '' ){

                // update lat lng
                $update_coor_str = "
                    UPDATE `property`
                    SET `lat` = {$coordinate['lat']},
                        `lng` = {$coordinate['lng']}
                    WHERE `property_id` = {$prop_row->property_id}
                ";
                $this->db->query($update_coor_str);

            }
            

        }        
        
    }

    public function isDHAagenciesV2($fg_id) {
        // Defence Housing
        if ($fg_id == 14) {
            return true;
        } else {
            return false;
        }
    }

    function agencyHasMaintenanceProgram($agency_id) {

        $sql = $this->db->query("
			SELECT *
			FROM `agency_maintenance`
			WHERE `agency_id` = {$agency_id}
			AND `maintenance_id` > 0
			AND `status` = 1
		");

        if ($sql->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getAvatar($photo) {

        if ($photo && $photo != '') {
            return "staff_profile/{$photo}";
        } else {
            return $this->config->item('photo_empty');
        }
    }

    // also apply changes on js inc/custom.js - getStreetAbrvFullName
    public function getStreetAbrvFullName($street) {

        $result = $street;

        $find_arr = ['Ally','Arc','Ave','Bvd','Bypa','Cct','Cl','Crn','Ct','Cir','Cres','Cds','Dr','Esp','Grn','Gr','Hwy','Jnc','Pde','Pl','Rdge','Rd','Sq','St','Tce'];
        $replace_arr = ['Alley','Arcade','Avenue','Boulevard','Bypass','Circuit','Close','Corner','Court','Circle','Crescent','Cul-de-sac','Drive','Esplanade','Green','Grove','Highway','Junction','Parade','Place','Ridge','Road','Square','Street','Terrace'];

        foreach ($find_arr as $index => $find) {

            if (preg_match("/\b{$find}\b/i", $street)) {

                $replace = $replace_arr[$index];
                $result = preg_replace("/\b{$find}\b/i", $replace, $street);
            }
        }

        return $result;
    }

    // replace state
    public function replace_state_old($state) {

        $result = $state;

        $find_arr = [
            'Hamilton','Palmerston North','Oamaru','Whanganui','Timaru','Palmerston North','Whanganui','Whakatane','Tokora',
            'Hastings','Napier','Clive','Feilding',
            'Christchurch','Queenstown','Wanaka','Waimakariri','Upper Hutt','Lower Hutt','New Plymouth','Dunedin','Horowhenua','Invercargill'
        ];
        $replace_arr = [
            'Waikato','Manawatu-Wanganui','Otago','Manawatu-Wanganui','Canterbury','Manawatu-Wanganui','Manawatu-Wanganui','Bay of Plenty','Waikato',
            "Hawke\'s Bay","Hawke\'s Bay","Hawke\'s Bay","Hawke\'s Bay",
            'Canterbury','Otago','Otago','Canterbury','Wellington','Wellington','Taranaki','Otago','Manawatu-Wanganui','Southland'
        ];

        foreach ($find_arr as $index => $find) {

            if (preg_match("/\b{$find}\b/i", $state)) {

                $replace = $replace_arr[$index];
                $result = preg_replace("/\b{$find}\b/i", $replace, $state);
            }
        }

        return $result;
    }

    // replace state
    public function replace_state($state) {

        $replaced_state = $state;

        // single replace
        if( $state == 'Whakatane' ){
            $replaced_state = 'Bay of Plenty';
        }

        if( $state == 'New Plymouth' ){
            $replaced_state = 'Taranaki';
        }

        // multiple replace
        $replace_to_waikato = ['Hamilton','Tokora'];
        if( in_array($state, $replace_to_waikato) ){
            $replaced_state = 'Waikato';
        }

        $replace_to_manawatu = ['Palmerston North','Whanganui','Palmerston North','Horowhenua'];
        if( in_array($state, $replace_to_manawatu) ){
            $replaced_state = 'Manawatu-Wanganui';
        }

        $replace_to_otago = ['Oamaru','Queenstown','Wanaka','Dunedin'];
        if( in_array($state, $replace_to_otago) ){
            $replaced_state = 'Otago';
        }

        $replace_to_canterbury = ['Timaru','Christchurch','Waimakariri'];
        if( in_array($state, $replace_to_canterbury) ){
            $replaced_state = 'Canterbury';
        }

        $replace_to_hawkes_bay = ['Hastings','Napier','Clive','Feilding'];
        if( in_array($state, $replace_to_hawkes_bay) ){
            $replaced_state = "Hawke\'s Bay";
        }

        $replace_to_wellington = ['Upper Hutt','Lower Hutt'];
        if( in_array($state, $replace_to_wellington) ){
            $replaced_state = 'Wellington';
        }

        $replace_to_southland = ['Invercargill','Winton'];
        if( in_array($state, $replace_to_southland) ){
            $replaced_state = 'Southland';
        }

        return $replaced_state;
        
    }

    public function sumExpense($exp_sum_id) {
        if ((int) $exp_sum_id === 0) {
            return 0;
        }
        $this->db->select("SUM( `amount` ) AS jtot");
        $this->db->from("`expenses`");
        $this->db->where("`expense_summary_id` ={$exp_sum_id}");
        return $this->db->get()->row()->jtot;
    }

    public function getEnteredBy($exp_sum_id) {
        $this->db->select("eb_sa.`FirstName`, eb_sa.`LastName`");
        $this->db->from("`expenses` AS exp");
        $this->db->join("`staff_accounts` AS eb_sa", "exp.`entered_by` = eb_sa.`StaffID`", "LEFT");
        $this->db->where("exp.`expense_summary_id` ={$exp_sum_id}");
        $this->db->group_by("exp.`entered_by`");
        $this->db->limit("1");

        $row = $this->db->get()->row_array();
        return "{$row['FirstName']} {$row['LastName']}";
    }

    public function getCrmSettings($params) {
        $sel_str = "*";
        if ($params['sel_str'] != '') {
            $sel_str = $params['sel_str'];
        }
        $country_id = $this->config->item('country');
        $this->db->select($sel_str);
        $this->db->from("crm_settings");
        $this->db->where("`country_id` = {$country_id}");
        return $this->db->get();
    }

    /**
     * clean postcodes
     */
    public function get_sanitized_postcode($postcode){
        $postcode_exp = explode(",",$postcode);
        $postcode_sanitize = array_filter($postcode_exp); // clear empty array
        $postcode_imp = implode(",",$postcode_sanitize);
        return $postcode_imp;
    }

    public function display_job_icons($params){

        $domain = ($params['display_in_email'] == true)?$this->config->item('crm_link'):null;

        $icons_str = '<img data-toggle="tooltip" title="'.$params['sevice_type_name'].'" src="'.$domain.'/images/serv_img/'.$this->system_model->getServiceIcons($params['service_type']).'" />';

        // if job type is 'IC Upgrade' show IC upgrade icon
        if( $params['job_type'] == 'IC Upgrade' ){
            $icons_str .= '<img data-toggle="tooltip" title="'.$params['job_type'].'" src="'.$domain.'/images/serv_img/upgrade_colored.png" class="j_icons" />';
        }

        if( $params['job_type'] == '240v Rebook' ){
            $icons_str .= '<img data-toggle="tooltip" title="'.$params['job_type'].'" src="'.$domain.'/images/240v_colored.png" class="j_icons" />';
        }

        if( $params['is_eo'] == 1 ){
            $icons_str .= '<img data-toggle="tooltip" title="Electrician Only" src="'.$domain.'/images/EO_colored.png" class="j_icons" />';
        }

        if( $params['job_type'] == 'Fix or Replace' ){
            $icons_str .= '<img data-toggle="tooltip" title="'.$params['job_type'].'" src="'.$domain.'/images/fr_colored.png" class="j_icons" />';
        }

        return $icons_str;

    }

    public function display_job_icons_v2($params){

        $icons_str = null;

        $job_id = $params['job_id'];
        $domain = ($params['display_in_email'] == true)?$this->config->item('crm_link'):null;

        if( $job_id > 0 ){

            // get jobs data
            $job_sql = $this->db->query("
            SELECT
                j.`id` AS jid,
                j.`service` AS jservice,
                j.`job_type`,
                j.`assigned_tech`,
                j.`is_eo`,

                ajt.`type` AS sevice_type_name,

                p.`state` AS p_state,
                p.`service_garage`
            FROM `jobs` AS j            
            LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            WHERE j.`id` = {$job_id}
            ");
            $job_row = $job_sql->row();

            $icons_str = '<img data-toggle="tooltip" title="'.$job_row->sevice_type_name.'" src="'.$domain.'/images/serv_img/'.$this->system_model->getServiceIcons($job_row->jservice).'" class="j_icons" />';

            // if job type is 'IC Upgrade' show IC upgrade icon
            if( $job_row->job_type == 'IC Upgrade' ){
                $icons_str .= '<img data-toggle="tooltip" title="'.$job_row->job_type.'" src="'.$domain.'/images/serv_img/upgrade_colored.png" class="j_icons" />';
            }

            if( $job_row->job_type == '240v Rebook' ){
                $icons_str .= '<img data-toggle="tooltip" title="'.$job_row->job_type.'" src="'.$domain.'/images/240v_colored.png" class="j_icons" />';
            }
    
            if( $job_row->is_eo == 1 ){
                $icons_str .= '<img data-toggle="tooltip" title="Electrician Only" src="'.$domain.'/images/EO_colored.png" class="j_icons" />';
            }

            if( $job_row->job_type == 'Fix or Replace' ){
                $icons_str .= '<img data-toggle="tooltip" title="'.$job_row->job_type.'" src="'.$domain.'/images/fr_colored.png" class="j_icons" />';
            }
            

            /*
            // empty, OS and UB
            if( $job_row->assigned_tech == "" || $job_row->assigned_tech == 1 || $job_row->assigned_tech == 2 ){
                $icons_str .= '<img data-toggle="tooltip" title="No Technician" src="'.$domain.'/images/no_tech.png" class="j_icons" />';
            }
            */

            return $icons_str;

        }

    }

    public function check_api_logs_by_JobId($jobId) {
        $this->db->select('id');
        $this->db->from('agency_api_logs');
        $this->db->where('job_id', $jobId);
        $this->db->where('status', 1);
        $query = $this->db->get();
        $res = $query->num_rows();
        if ($res > 0 ) {
            return true;
        }else {
            return false;
        }
    }

    public function currency_format_custom($params){

        // Sets the number of decimal points.
        $dp = ( $params['dp'] != '' )?$params['dp']:2;
        // Sets the separator for the decimal point.
        $dp_sep = ( $params['dp_sep'] != '' )?$params['dp_sep']:'.';
        // Sets the thousands separator.
        $k_sep = ( $params['k_sep'] != '' )?$params['k_sep']:'';

        return number_format($params['number'], $dp, $dp_sep, $k_sep);

    }

    public function currency_format($number,$k_sep=''){
        return number_format($number, 2, '.', $k_sep);
    }

    public function get_fn_agencies(){

        // First National script
        if ( COUNTRY == 1 ) { // AU

            if ( ENVIRONMENT == 'production' ) { // LIVE

                // 4718 - First National Sarina
                $fn_agency_main = 4718;
                // 4318 - First National Mackay
                // 4724 - First National Nebo
                $fn_agency_sub = array(4318,4724);
                $fn_agency_sub_imp = implode(",",$fn_agency_sub);

            } else { // DEV

                // 4188 - First National Sarina
                $fn_agency_main = 4188;
                // 4186 - First National Mackay
                // 4187 - First National Nebo
                $fn_agency_sub = array(4186,4187);
                $fn_agency_sub_imp = implode(",",$fn_agency_sub);

            }

        }

        return array(
            'fn_agency_main' => $fn_agency_main,
            'fn_agency_sub' => $fn_agency_sub
        );

    }

    public function get_vision_agencies(){

        // First National script
        if ( COUNTRY == 1 ) { // AU

            if ( ENVIRONMENT == 'production' ) { // LIVE

                // 4637 - Vision Real Estate Mackay
                $vision_agency_main = 4637;
                // 6782 - Vision Real Estate Dysart
                $vision_agency_sub = array(6782);
                $vision_agency_sub_imp = implode(",",$vision_agency_sub);

            } else { // DEV

                // 4192 - Vision Real Estate Mackay
                $vision_agency_main = 4192;
                // 4193 - Vision Real Estate Dysart
                $vision_agency_sub = array(4193);
                $vision_agency_sub_imp = implode(",",$vision_agency_sub);

            }

        }

        return array(
            'vision_agency_main' => $vision_agency_main,
            'vision_agency_sub' => $vision_agency_sub
        );

    }

    public function display_orca_or_cavi_alarms($agency_id){

        if( $agency_id > 0 ){

            $has_orca_0_price = false;
            $has_cavi = false;
            $alarm_make = null;

            $agen_al_sql_str = "
                SELECT aa.`agency_alarm_id`, aa.`price`, ap.`alarm_make`
                FROM `agency_alarms` AS aa
                LEFT JOIN `alarm_pwr` AS ap ON aa.`alarm_pwr_id` = ap.`alarm_pwr_id`
                WHERE aa.`agency_id` = {$agency_id}
                AND ap.`active` = 1
            ";
            //$agen_al_sql = mysql_query($agen_al_sql_str);
            $agen_al_sql = $this->db->query($agen_al_sql_str);
            foreach( $agen_al_sql->result() as $agen_al_row ){

                if( $agen_al_row->alarm_make == 'Orca' && $agen_al_row->price == 0 ){
                    $has_orca_0_price = true;
                }

                if( $agen_al_row->alarm_make == 'Cavius' ){
                    $has_cavi = true;
                }

            }

            if( $has_orca_0_price == true && $has_cavi == false ){
                $alarm_make = "Orca";
            }else{
                $alarm_make = "Cavius";
            }

            return $alarm_make;

        }        

    }


    public function display_free_emerald_or_paid_brooks( $agency_id, $use_short = false ){

        if( $agency_id > 0 ){

            // get state
            $agency_sql_str = "
            SELECT `state`
            FROM `agency`
            WHERE `agency_id` = {$agency_id}
            ";
            $agency_sql = $this->db->query($agency_sql_str);
            $agency_row = $agency_sql->row();
            
            $alarm_make = null;

            // PAID alarms, brooks    
            // find 240v or 3vLi  
            $agen_al_sql_str = "
                SELECT COUNT(aa.`agency_alarm_id`) AS aa_count
                FROM `agency_alarms` AS aa
                LEFT JOIN `alarm_pwr` AS ap ON aa.`alarm_pwr_id` = ap.`alarm_pwr_id`
                WHERE aa.`agency_id` = {$agency_id}  
                AND ap.`alarm_pwr_id` IN(2,7)           
                AND ap.`active` = 1
            ";        
            $agen_al_sql = $this->db->query($agen_al_sql_str);
            $brooks_count = $agen_al_sql->row()->aa_count;                                           


            if( $brooks_count > 0 ){ // found brooks alarm
                
                $alarm_make = "Brooks";

            }else{ // else emerald

                // FREE alarms, emerald
                if( $agency_row->state == 'NSW' || $agency_row->state == 'ACT' ){
                
                    // find 9v(EP) or 240v(EP)
                    $agen_al_sql_str = "
                        SELECT COUNT(aa.`agency_alarm_id`) AS aa_count
                        FROM `agency_alarms` AS aa
                        LEFT JOIN `alarm_pwr` AS ap ON aa.`alarm_pwr_id` = ap.`alarm_pwr_id`
                        WHERE aa.`agency_id` = {$agency_id}        
                        AND ap.`alarm_pwr_id` IN(18,21)
                        AND aa.`price` = 0
                        AND ap.`active` = 1
                    ";        
                    $agen_al_sql = $this->db->query($agen_al_sql_str);
                    $free_emerald_count = $agen_al_sql->row()->aa_count;

                }else if( $agency_row->state == 'SA' ){
                    
                    // find 3VLi(EP) or 240v(EP)
                    $agen_al_sql_str = "
                        SELECT COUNT(aa.`agency_alarm_id`) AS aa_count
                        FROM `agency_alarms` AS aa
                        LEFT JOIN `alarm_pwr` AS ap ON aa.`alarm_pwr_id` = ap.`alarm_pwr_id`
                        WHERE aa.`agency_id` = {$agency_id}
                        AND ap.`alarm_pwr_id` IN(19,21)
                        AND aa.`price` = 0
                        AND ap.`active` = 1
                    ";        
                    $agen_al_sql = $this->db->query($agen_al_sql_str);
                    $free_emerald_count = $agen_al_sql->row()->aa_count;

                }

                if( $free_emerald_count > 0 ){

                    if( $use_short == true ){
                        $alarm_make = "Emerald";
                    }else{
                        $alarm_make = "Emerald Planet";
                    }

                }                               

            }

            return $alarm_make;


        }        

    }

    public function displayOrcaOrCaviAlarmsByAgencyIds($agencyIds){
        if (!empty($agencyIds)) {
            $agencyIdsString = implode($agencyIds);

            $query = "
                SELECT aa.`agency_id`, aa.`agency_alarm_id`, aa.`price`, ap.`alarm_make`
                FROM `agency_alarms` AS aa
                LEFT JOIN `alarm_pwr` AS ap ON aa.`alarm_pwr_id` = ap.`alarm_pwr_id`
                WHERE aa.`agency_id` IN ({$agencyIdsString})
                AND ap.`active` = 1
            ";
            $alarms = array_fill_keys($agencyIds, [
                'free' => false,
                'cavi' => false,
            ]);

            $result = $this->db->query($query);
            foreach( $result->result() as $row ){

                if( $row->alarm_make == 'Orca' && $row->price == 0 ){
                    $alarms[$row['agency_id']]['free'] = true;
                }

                if( $agen_rowal_row->alarm_make == 'Cavius' ){
                    $alarms[$row['agency_id']]['cavi'] = true;
                }

            }

            $returnValue = array_fill_keys($agencyIds, 'Cavius');
            foreach ($alarms as $key => $row) {
                if( $row['free'] == true && $row['cavi'] == false ){
                    $returnValue[$key] = "Orca";
                }
            }

            return $returnValue;
        }

        return [];
    }


    public function combine_tenant_names($tenants_names_arr){

        if( $tenants_names_arr > 0 ){

            if( count( $tenants_names_arr ) == 1 ){ // single tenant

                return $tenants_names_arr[0];

            }else{  // more than 1 tenant, combine

                $tenant_str_imp = implode(", ",$tenants_names_arr); // separate tenant names with a comma
                $last_comma_pos = strrpos($tenant_str_imp,","); // find the last comma(,) position
                return substr_replace($tenant_str_imp,' &',$last_comma_pos,1); // replace comma with ampersand(&)

            }

        }

    }


    public function old_crm_redirect($page, $page_params = null) {

        return "{$this->config->item('crm_link')}/authenticate.php?staff_id={$this->session->staff_id}&page=" . rawurlencode($page) . "&page_params=" . rawurlencode($page_params);

    }

    // get water efficiency services ID
    public function we_services_id(){

        return array(15,16,17,18);

    }

    // check if agency has maintenance program
    public function check_agency_has_mm($agency_id){

        if( $agency_id > 0 ){

            // check if agency has maintenance program
            $am_sql = $this->db->query("
                SELECT COUNT(`agency_maintenance_id`) AS am_count
                FROM `agency_maintenance`
                WHERE `agency_id` = {$agency_id}
                AND `maintenance_id` > 0
            ");

            $am_row = $am_sql->row();
            if( $am_row->am_count > 0 ){
                return true;
            }else{
                return false;
            }

        }

    }

    // check if property has money owing and needs to verify paid
    public function check_verify_paid($property_id){

        $job_sql_str = "
        SELECT COUNT(j.`id`) AS jcount
        FROM `jobs` AS j
        WHERE j.`property_id` = {$property_id}
        AND j.`status` = 'Completed'
        AND j.`invoice_balance` > 0
        AND (
            j.`date` >= '{$this->config->item('accounts_financial_year')}'  OR
            j.`unpaid` = 1
        )
        ";

        $job_sql = $this->db->query($job_sql_str);
        $job_count = $job_sql->row()->jcount;

        if( $job_count > 0 ){
            return true;
        }else{
            return false;
        }

    }


    public function get_cron_active_status($cron_type_id){

        if( $cron_type_id > 0 ){

            $cron_sql = $this->db->query("
            SELECT `active_cron`
            FROM `cron_types`
            WHERE `cron_type_id` = {$cron_type_id}
            ");

            $cron_row = $cron_sql->row();
            return $cron_row->active_cron;

        }


    }

    public function update_page_total($params){

        $page = $params['page'];
        $total = $params['total'];

        // check if page total exist
        $page_to_sql = $this->db->query("
        SELECT COUNT(`page_total_id`) AS page_tol_count
        FROM `page_total`
        WHERE `page` = '{$page}'
        ");
        $page_to_count = $page_to_sql->row()->page_tol_count;

        if( $page_to_count > 0 ){ // it exist

           // update
           $this->db->query("
           UPDATE `page_total`
           SET
               `total` = {$total}
           WHERE `page` = '{$page}'
           ");

        }else{

           // insert
           $this->db->query("
           INSERT INTO
           `page_total`(
               `page`,
               `total`
           )
           VALUES(
               '{$page}',
               {$total}
           )
           ");

        }

    }

    public function update_main_page_total($params){

        $name = $params['name'];
        $total = $params['total'];

        // check if page total exist
        $sql = $this->db->query("
        SELECT COUNT(`id`) AS count
        FROM `main_page_total`
        WHERE `name` = '{$name}'
        ");
        $count = $sql->row()->count;

        if( $count > 0 ){ // it exist
           // update
           $this->db->query("
           UPDATE `main_page_total`
           SET
               `total` = {$total}
           WHERE `name` = '{$name}'
           ");

        }else{

           // insert
           $this->db->query("
           INSERT INTO
           `main_page_total`(
               `name`,
               `total`
           )
           VALUES(
               '{$name}',
               {$total}
           )
           ");

        }
    }

    public function update_property_jobs_count(){
        $jobs = $this->db->select('id')
        ->from('jobs')
        ->group_by('property_id')
        ->order_by('id', 'asc')
        ->get()
        ->result();

        foreach($jobs as $job){
            $set_data = array(
                'property_jobs_count' => 1
            );
            $update = $this->db->where('id', $job->id)
                ->where('property_jobs_count', 0)
                ->update('jobs', $set_data);
        }
    }

    public function get_page_total($page){

        if( $page != '' ){

            $page_sql_str = "
            SELECT `total`
            FROM `page_total`
            WHERE `page` =  '{$page}'
            ";

            $page_sql = $this->db->query($page_sql_str);
            $page_row = $page_sql->row();
            return $page_row->total;

        }

    }

    public function ifCountryHasState(){

        $this->db->select("*");
        $this->db->from('countries');
        $this->db->where('country_id', $this->config->item('country'));
        $q = $this->db->get();
        $row = $q->row();
        if($q->states==1){
            return true;
        }else{
            return false;
        }

    }

    /**
     * Get region by postcode from new table (regions)
     */
    public function getRegion_v2($regionCodes) {

        $this->db->select('*');
        $this->db->from('postcode as p');
        $this->db->join('sub_regions as sr', "sr.sub_region_id=p.sub_region_id", "right");
        $this->db->join('regions as r', "r.regions_id=sr.region_id", "right");
        $this->db->where('p.deleted',0);
        $this->db->where_in('p.postcode', $regionCodes);
        $query = $this->db->get();
        return $query;

    }


    public function price_ex_gst($price){

        $price_ex_gst = 0;

        if( $this->config->item('country') == 1 ){ // AU
            $gst = $price/11;
            $price_ex_gst = $price-$gst;        
        }else if( $this->config->item('country') == 2 ){ // NZ
            $gst = ($price*3)/23;
            $price_ex_gst = $price-$gst;                
        }

        return $price_ex_gst;

    }

    public function can_edit_vad_api(){

        // check if user is global(2) or full access(9) 
        $sql_str = "
        SELECT COUNT(StaffID) AS sa_count
        FROM `staff_accounts`
        WHERE `active` = 1
        AND `Deleted` = 0 
        AND `ClassID` IN(2,9)
        AND `StaffID` = {$this->session->staff_id}
        ";        

        $sql = $this->db->query($sql_str);
        $row = $sql->row();

        // allow other users via user ID
        $allowed_user_arr = [];
        $is_allowed = false;

        if( $this->config->item('country') == 1 ){ // AU

            // Krystal
            //Charlotte B
            $allowed_user_arr = array(
                2348,2428 
            );      

        }else if( $this->config->item('country') == 2 ){ // NZ
            
            // Krystal
            //Charlotte B
            $allowed_user_arr = array(
                2255,2289
            );   
                       
        }

        if( in_array( $this->session->staff_id, $allowed_user_arr ) ){
            $is_allowed = true;
        }
        
        if( $row->sa_count > 0 || $is_allowed == true ){
            return true;
        }else{
            return false;
        }
        
    }


    // Get Interconnected Service (IC), copied function name from old crm
    public function getICService(){
        
        $sql = $this->db->query("
        SELECT `id`
        FROM `alarm_job_type`
        WHERE `active` = 1
        AND `is_ic` = 1
        ");

        $ic_serv_arr = [];
        foreach( $sql->result() as $row ){
            $ic_serv_arr[] = $row->id;
        }

        return $ic_serv_arr;

    }

    public function get_agency_price_variation($params){

        $service_type = $params['service_type'];
        $agency_id = $params['agency_id'];

        $today = date('Y-m-d');

        // get dynamic price
        $dynamic_price = 0;
        $ret_arr = [];

        $price_variation_total = 0;
        $price_variation_total_str = null;

        // get price increase excluded agency
        $piea_sql = $this->db->query("
        SELECT *
        FROM `price_increase_excluded_agency`
        WHERE `agency_id` = {$agency_id}
        AND (
            `exclude_until` >= '{$today}' OR
            `exclude_until` IS NULL
        )
        ");        

        // get agency specific service price
        $assp_sql = $this->db->query("
        SELECT *
        FROM `agency_specific_service_price`
        WHERE `service_type` = {$service_type}
        AND `agency_id` = {$agency_id}
        ");
        
        // get agency default service price
        $adsp_sql = $this->db->query("
        SELECT *
        FROM `agency_default_service_price`
        WHERE `service_type` = {$service_type}
        ");  

        // get agency price variation
        $apv_sql = $this->db->query("
        SELECT 
            apv.`type` AS apv_type,
            apv.`amount`,
            apv.`scope`,

            ajt.`type` AS ajt_type,
            ajt.`short_name`
        FROM `agency_price_variation` AS apv
        LEFT JOIN `alarm_job_type` AS ajt ON ( apv.`scope` = ajt.`id` AND apv.`scope` >= 2 )
        WHERE apv.`agency_id` = {$agency_id}    
        AND (
            apv.`scope` = 0 OR
            apv.`scope` = {$service_type}
        )
        AND (
            apv.expiry >= '{$today}'
            OR apv.expiry IS NULL
        )
        AND apv.`active` = 1
        ");  

        foreach( $apv_sql->result() as $apv_row ){  
            
            $service_type_str = ( $apv_row->scope >= 2 )?"{$apv_row->short_name} Service ":null;
                        
            if( $apv_row->apv_type == 1 ){ // discount
                $price_variation_total-=$apv_row->amount;
                $price_variation_total_str .= " - \$".number_format($apv_row->amount,2)." {$service_type_str}Discount";
            }else{ // surcharge
                $price_variation_total+=$apv_row->amount;
                $price_variation_total_str .= " + \$".number_format($apv_row->amount,2)." {$service_type_str}Surcharge";
            }            

        }  
                            
        if( $piea_sql->num_rows() > 0 ){ // price increase excluded agency IF block
            
            // get agency services
            $agen_serv_sql = $this->db->query("
            SELECT *
            FROM `agency_services`
            WHERE `service_id` = {$service_type}
            AND `agency_id` = {$agency_id}
            ");
            $agen_serv_row = $agen_serv_sql->row();                
            
            $dynamic_price = $agen_serv_row->price;
            $dynamic_price_total = $dynamic_price; // no added price variation

        }else if( $assp_sql->num_rows() > 0 ){ // agency specific service price IF block

            $assp_row = $assp_sql->row();
            $dynamic_price = $assp_row->price;
            $dynamic_price_total = $dynamic_price+$price_variation_total; // add variations
            
        }else if( $adsp_sql->num_rows() > 0 ){ // agency default service price IF block

            $adsp_row = $adsp_sql->row();    
            $dynamic_price = $adsp_row->price;
            $dynamic_price_total = $dynamic_price+$price_variation_total; // add variations

        }

        $final_total_str = ( $price_variation_total != 0 )?' = $'.number_format($dynamic_price_total,2):null;

        $dynamic_price_text = '$'.number_format($dynamic_price,2);
        $price_text = '$'.number_format($dynamic_price_total,2);
        $price_breakdown_text = '$'.number_format($dynamic_price,2).$price_variation_total_str.$final_total_str;

        return $ret_arr = array(
            'dynamic_price' => $dynamic_price,
            'price_variation_total' => $price_variation_total,
            'dynamic_price_total' => $dynamic_price_total,
            'dynamic_price_text' => $dynamic_price_text,
            'price_text' => $price_text,
            'price_breakdown_text' => $price_breakdown_text                  
        );
    
    }


    public function free_alarms($params){

        $alarm_tot_amount = $params['alarm_tot_amount'];
        $job_id = $params['job_id'];

        $today = date('Y-m-d');

        $return_price = $alarm_tot_amount;

        if( $job_id > 0 ){

            // get jobs data
            $job_sql = $this->db->query("
            SELECT  j.`job_type`, p.`holiday_rental`, a.`agency_id`
            FROM `jobs` AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE `id` = {$job_id}
            ");
            $job_row = $job_sql->row();

            // get price increase excluded agency
            $piea_sql = $this->db->query("
            SELECT COUNT(`id`) AS piea_count
            FROM `price_increase_excluded_agency`
            WHERE `agency_id` = {$job_row->agency_id}
            AND (
                `exclude_until` >= '{$today}' OR
                `exclude_until` IS NULL
            )
            ");
            
            // get agency preference selected
            $agency_pref_id = 22; // 'Paid alarms?' agency preference
            $aps_sql = $this->db->query("
            SELECT COUNT(`id`) AS aps_count
            FROM `agency_preference_selected`
            WHERE `agency_id` = {$job_row->agency_id}
            AND `agency_pref_id` = {$agency_pref_id}
            AND `sel_pref_val` = 1
            "); 
            
            if( 
                $job_row->job_type == 'IC Upgrade' || 
                $job_row->holiday_rental == 1 || 
                $piea_sql->row()->piea_count > 0 || 
                $aps_sql->row()->aps_count > 0 
            ){ // agency is excluded to price increase
                $return_price = $alarm_tot_amount;
            }else{ // price increase, alarm price is 0
                $return_price = 0;
            }            

        }  
        
        return $return_price;

    }

    public function get_property_price_variation($params){

        $service_type = $params['service_type'];
        $property_id = $params['property_id'];

        $today = date('Y-m-d');
    
        // get dynamic price
        $dynamic_price = 0;
        $ret_arr = [];
    
        $price_variation_total = 0;
        $price_variation_total_str = null;
    
        // get property data
        $prop_sql = $this->db->query("
        SELECT 
            `agency_id`,
            `holiday_rental`,
            `state`
        FROM `property`
        WHERE `property_id` = {$property_id}
        ");
        $prop_row = $prop_sql->row();
        $agency_id = $prop_row->agency_id;                
    
        // get price increase excluded agency
        $piea_sql = $this->db->query("
        SELECT *
        FROM `price_increase_excluded_agency`
        WHERE `agency_id` = {$agency_id}
        AND (
            `exclude_until` >= '{$today}' OR
            `exclude_until` IS NULL
        )
        ");   

        $prop_state_esc = $this->db->escape($prop_row->state);
        // get short term service price
        $stsp_sql = $this->db->query("
        SELECT *
        FROM `short_term_service_price`
        WHERE `service_type` = {$service_type}
        AND `state` = {$prop_state_esc}
        ");       
                            
        if( $piea_sql->num_rows() > 0 ){ // agency is price increase excluded

            // get property services
            $ps_sql = $this->db->query("
            SELECT *
            FROM `property_services`
            WHERE `alarm_job_type_id` = {$service_type}
            AND `service` = 1
            AND `property_id` = {$property_id}
            ");           

            if( $ps_sql->num_rows() > 0 ){

                $ps_row = $ps_sql->row();
                $dynamic_price = $ps_row->price;                

            }else{

                // get agency services
                $agen_serv_sql = $this->db->query("
                SELECT *
                FROM `agency_services`
                WHERE `service_id` = {$service_type}
                AND `agency_id` = {$agency_id}
                ");

                $agen_serv_row = $agen_serv_sql->row();                                
                $dynamic_price = $agen_serv_row->price;                
                
            }

            $dynamic_price_total = $dynamic_price; // no added price variation
    
        }else if( $prop_row->holiday_rental == 1 && $stsp_sql->num_rows() > 0 ){ // short term service price
            
            $stsp_row = $stsp_sql->row();
            $dynamic_price = $stsp_row->price;
            $dynamic_price_total = $dynamic_price; // no added price variation
            
        }else{ // agency and property variation    
            
            // get agency specific service price
            $assp_sql = $this->db->query("
            SELECT *
            FROM `agency_specific_service_price`
            WHERE `service_type` = {$service_type}
            AND `agency_id` = {$agency_id}
            "); 
            $assp_row = $assp_sql->row(); 

            // get agency default service price
            $adsp_sql = $this->db->query("
                SELECT *
                    FROM `agency_default_service_price`
                    WHERE `service_type` = {$service_type}
                "); 
            $adsp_row = $adsp_sql->row();

            if ($assp_sql->num_rows() > 0) {
                $dynamic_price = $assp_row->price;
            } else {
                $dynamic_price = $adsp_row->price;
            }

            // get agency price variation
            $apv_sql = $this->db->query("
            SELECT 
                apv.`type` AS apv_type,
                apv.`amount`,
                apv.`scope`,
        
                ajt.`type` AS ajt_type,
                ajt.`short_name`
            FROM `agency_price_variation` AS apv
            LEFT JOIN `alarm_job_type` AS ajt ON ( apv.`scope` = ajt.`id` AND apv.`scope` >= 2 )
            WHERE apv.`agency_id` = {$agency_id}    
            AND (
                apv.`scope` = 0 OR
                apv.`scope` = {$service_type}
            )
            AND (
                apv.expiry >= '{$today}'
                OR apv.expiry IS NULL
            )
            AND apv.`active` = 1
            ");                  
        
            foreach( $apv_sql->result() as $apv_row ){  
                
                $service_type_str = ( $apv_row->scope >= 2 )?"{$apv_row->short_name} Service ":null;
                            
                if( $apv_row->apv_type == 1 ){ // discount
                    $price_variation_total-=$apv_row->amount;
                    $price_variation_total_str .= " - \$".number_format($apv_row->amount,2)." {$service_type_str}Discount";
                }else{ // surcharge
                    $price_variation_total+=$apv_row->amount;
                    $price_variation_total_str .= " + \$".number_format($apv_row->amount,2)." {$service_type_str}Surcharge";
                }            
        
            }
    
            // get property variation
            $pv_sql = $this->db->query("
            SELECT 
                apv.`type` AS apv_type,
                apv.`amount`,
                apv.`scope`,
        
                ajt.`type` AS ajt_type,
                ajt.`short_name`
            FROM `property_variation` AS pv        
            LEFT JOIN `agency_price_variation` AS apv ON ( pv.`agency_price_variation` = apv.`id` AND pv.`property_id` = {$property_id} )
            LEFT JOIN `alarm_job_type` AS ajt ON ( apv.`scope` = ajt.`id` AND apv.`scope` >= 2 )
            WHERE apv.`agency_id` = {$agency_id}    
            AND (
                apv.`scope` = 1 OR
                apv.`scope` = {$service_type}
            )
            AND (
                apv.expiry >= '{$today}'
                OR apv.expiry IS NULL
            )
            AND apv.`active` = 1
            AND pv.`active` = 1
            ");  
        
            foreach( $pv_sql->result() as $pv_row ){  
                
                $service_type_str = ( $pv_row->scope >= 2 )?"{$pv_row->short_name} Service ":null;
                            
                if( $pv_row->apv_type == 1 ){ // discount
                    $price_variation_total-=$pv_row->amount;
                    $price_variation_total_str .= " - \$".number_format($pv_row->amount,2)." {$service_type_str}Discount";
                }else{ // surcharge
                    $price_variation_total+=$pv_row->amount;
                    $price_variation_total_str .= " + \$".number_format($pv_row->amount,2)." {$service_type_str}Surcharge";
                }            
        
            }
    
            //$dynamic_price = $ps_row->price;
            $dynamic_price_total = $dynamic_price+$price_variation_total; // add variations
    
        }
    
        $final_total_str = ( $price_variation_total )?' = $'.number_format($dynamic_price_total,2):null;
    
        $dynamic_price_text = '$'.number_format($dynamic_price,2);
        $price_text = '$'.number_format($dynamic_price_total,2);
        $price_breakdown_text = '$'.number_format($dynamic_price,2).$price_variation_total_str.$final_total_str;
    
        return $ret_arr = array(
            'dynamic_price' => $dynamic_price,
            'price_variation_total' => $price_variation_total,
            'dynamic_price_total' => $dynamic_price_total,
            'dynamic_price_text' => $dynamic_price_text,
            'price_text' => $price_text,
            'price_breakdown_text' => $price_breakdown_text                  
        );
    
    }

    /**
     * check price_increase_excluded_agency by agency_id
     * return boolean
     */
    public function check_price_increase_excluded_agency($agency_id){
        $piea_sql = $this->db->query("
                    SELECT *
                    FROM `price_increase_excluded_agency`
                    WHERE `agency_id` = {$agency_id}                  
                    AND (
                        `exclude_until` >= '".date('Y-m-d')."' OR
                        `exclude_until` IS NULL
                    )
                    ");  

        if( $piea_sql->num_rows()>0 ){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Get jobs variation price > used for invoice
     */
    public function get_job_variation($params){

        $service_type = $params['service_type'];
        $property_id = $params['property_id'];
        $job_id = $params['job_id'];

        $today = date('Y-m-d');

        ##get new price including variations
        $price_var_params = array(
            'service_type' => $service_type,
            'property_id' => $property_id
        );
        $price_var_arr = $this->system_model->get_property_price_variation($price_var_params);
        $price_inc_var = number_format($price_var_arr['dynamic_price_total'],2);
        ##get new price including variations end

        //get agency_default_service_price
        $agency_default_price_sql = $this->db->query("
        SELECT 
            `service_type`,
            `price`
        FROM `agency_default_service_price`
        WHERE `service_type` = {$service_type}
        AND active = 1
        ");
        $agency_default_price_row = $agency_default_price_sql->row();
        $agency_default_price = $agency_default_price_row->price;
        //get agency_default_service_price end
    
        // get dynamic price
        $dynamic_price = 0;
        $price_variation_total = 0;
        $price_variation_total_display_on = 0;
        $computed_to_price = 0;
        $ret_arr = [];
        $display_var_arr = [];

        $display_on_portal_arr = array(6,7);

        // get property data
        $prop_sql = $this->db->query("
        SELECT 
            `agency_id`,
            `holiday_rental`,
            `state`
        FROM `property`
        WHERE `property_id` = {$property_id}
        ");
        $prop_row = $prop_sql->row();
        $agency_id = $prop_row->agency_id;
    
        // get property services
        $ps_sql = $this->db->query("
        SELECT *
        FROM `property_services`
        WHERE `alarm_job_type_id` = {$service_type}
        AND `service` = 1
        AND `property_id` = {$property_id}
        ");
        $ps_row = $ps_sql->row();          
        
        // get short term service price
        $prop_price_esc = $this->db->escape($prop_row->state);
        $stsp_sql = $this->db->query("
        SELECT *
        FROM `short_term_service_price`
        WHERE `service_type` = {$service_type}
        AND `state` = {$prop_price_esc}
        ");   
        $stsp_sql_row = $stsp_sql->row();    

        ## Get job_price and date
        $job_sql = $this->db->query("
            SELECT job_price, date AS j_date, created AS j_created
            FROM `jobs`
            WHERE `id` = {$job_id}
            AND `del_job` = 0
        ");     
        $job_row = $job_sql->row();
        $job_price = $job_row->job_price;
        $job_date = $job_row->j_date;
        $job_created = $job_row->j_created;

        //get agency specific price
        $assp_sql = $this->db->query("
        SELECT *
        FROM `agency_specific_service_price`
        WHERE `service_type` = {$service_type}
        AND `agency_id` = {$agency_id}
        "); 
        $assp_row = $assp_sql->row(); 
        //get agency specific price end

        // get price increase excluded agency
        $piea_sql = $this->db->query("
            SELECT *
            FROM `price_increase_excluded_agency`
            WHERE `agency_id` = {$agency_id}
            AND (
                `exclude_until` >= '{$today}' OR
                `exclude_until` IS NULL
            )
        ");  
        if( $piea_sql->num_rows() > 0 ){ //agency is price increase excluded

            /*if( $ps_sql->num_rows() > 0 ){

                $dynamic_price = $ps_row->price;                

            }else{

                // get agency services
                $agen_serv_sql = $this->db->query("
                SELECT *
                FROM `agency_services`
                WHERE `service_id` = {$service_type}
                AND `agency_id` = {$agency_id}
                ");

                $agen_serv_row = $agen_serv_sql->row();                                
                $dynamic_price = $agen_serv_row->price;                
                
            }

            $dynamic_price_total = $dynamic_price; // no added price variation*/

            $dynamic_price = $job_row->job_price;

        }else if( $prop_row->holiday_rental == 1 && $stsp_sql->num_rows() > 0 ){ // short term service price

            $loc = "Short Term Rental Price"; ## dont use > for testing only

            /*$jv_q = $this->db->query("
                SELECT 
                jv.id, 
                jv.amount,
                jv.type,
                jv.reason,

                dv.variation_id,
                dv.display_on,
                dv.type as display_variation_type
                FROM `job_variation` as jv
                LEFT JOIN `display_variation` AS dv ON ( jv.`id` = dv.`variation_id` AND dv.type = 2 )
                WHERE jv.job_id = {$job_id}
                AND jv.active = 1
            ");
            
            foreach( $jv_q->result_array() as $jv_row ){

                // $service_type_str = ( $pv_row->scope >= 2 )?"{$pv_row->short_name} Service ":null;

                $service_type_str = "Job";
                
                if( $jv_row['type'] == 1 ){ // discount
                    
                    $price_variation_total-=$jv_row['amount'];

                    if( in_array($jv_row['display_on'], $display_on_portal_arr) && $jv_row['display_variation_type']==2 ){ // displayed

                        $price_variation_total_display_on+=$jv_row['amount'];
                        
                        $display_var_arr[] = array(
                            'type' => $jv_row['type'],
                            'item' => "Discount",
                            'description' => $service_type_str,
                            'unit_price' => $jv_row['amount'],
                            'amount' => $jv_row['amount']
                        );

                    }else{ // not displayed, gets deducted from price
                        $computed_to_price-=$jv_row['amount'];
                    }
                }else{ // surcharge
                    $price_variation_total+=$jv_row['amount'];
                    
                    if( in_array($jv_row['display_on'], $display_on_portal_arr) && $jv_row['display_variation_type']==2 ){ // displayed
                        
                        $price_variation_total_display_on-=$jv_row['amount'];

                        $display_var_arr[] = array(
                            'type' => $jv_row['type'],
                            'item' => "Surcharge",
                            'description' => $service_type_str,
                            'unit_price' => $jv_row['amount'],
                            'amount' => $jv_row['amount']
                        );

                    }else{ // not displayed, gets added to price
                        $computed_to_price+=$jv_row['amount'];
                    }
                } 
            } 

            $dynamic_price = $job_price;
            $dynamic_price_total = $dynamic_price+$price_variation_total; // add variations
            */

            $dynamic_price = $stsp_sql_row->price;
            $dynamic_price_total = $dynamic_price; // add variations
            
            
        }else{ // agency and property variation    
            
            $loc = "Prop/Agency Variation"; ## dont use > for testing only   

            //set default price
            if ($assp_sql->num_rows() > 0) {
                $default_agency_price_swithced = $assp_row->price;
            } else {
                $default_agency_price_swithced = $agency_default_price;
            }
            
            ##get agency_price_variation
            $apv_sql = $this->db->query("
            SELECT 
                apv.`id` AS apv_id,
                apv.`type` AS apv_type,
                apv.`amount`,
                apv.`scope`,
                apv.type,
                apv.updated_date,
                apv.created_date,

                apvr.`reason` AS apvr_reason,

                dv.`display_on`,
                dv.type as display_type,

                ajt.`type` AS ajt_type,
                ajt.`short_name`
            FROM `agency_price_variation` AS apv
            LEFT JOIN `agency_price_variation_reason` AS apvr ON apv.`reason` = apvr.`id`
            LEFT JOIN `display_variation` AS dv ON ( apv.`id` = dv.`variation_id` AND dv.type=1 )
            LEFT JOIN `alarm_job_type` AS ajt ON ( apv.`scope` = ajt.`id` AND apv.`scope` >= 2 )
            WHERE apv.`agency_id` = {$agency_id}    
            AND (
                apv.`scope` = 0 OR
                apv.`scope` = {$service_type}
            )
            AND (
                apv.expiry >= '{$today}'
                OR apv.expiry IS NULL
            )
            AND apv.type != 0
            AND apv.`active` = 1
            ");  

            foreach( $apv_sql->result() as $apv_row ){ 
                
                //$service_type_str = ( $apv_row->scope >= 2 )?"{$apv_row->short_name} Service ":null;

               // $updated_date = date("Y-m-d", strtotime($apv_row->updated_date));
                $agency_var_created_date = date("Y-m-d H:i:s", strtotime($apv_row->created_date));
                $job_created = date("Y-m-d H:i:s", strtotime($job_created));
                $agency_price_var_scope = $apv_row->scope;

                if( $apv_row->scope==0 ){
                    $service_type_str = "Agency";
                }elseif( $apv_row->scope ==1 ){
                    $service_type_str = "Property";
                }else{
                    $service_type_str = "{$apv_row->short_name} Service";
                }

                if( 
                    $job_row->job_price != $price_inc_var AND 
                    ( ($agency_var_created_date >=$job_created AND $agency_price_var_scope==0) OR ($agency_var_created_date >=$job_created AND $agency_price_var_scope==2) )
                )
                {

                   // $job_price = $ps_row->price;
                    //$job_price = $job_row->job_price; //set original price from job_price
                 
                }else{

                   // $job_price = $this->_setJobPriceToZero($job_row->job_price,$default_agency_price_swithced);

                    if( $apv_row->apv_type == 1 ){ // discount

                        $price_variation_total-=$apv_row->amount;

                        if( in_array($apv_row->display_on, $display_on_portal_arr) ) { //display

                            $computed_to_price-=$apv_row->amount;
                            $price_variation_total_display_on+=$apv_row->amount;
                        
                            $display_var_arr[] = array(
                            'type' => $apv_row->apv_type,
                            'item' => "Discount",
                            'description' => $service_type_str,
                            'unit_price' => $apv_row->amount,
                            'amount' => $apv_row->amount,
                            'test_marker' => 'agency variation',
                            'test_job_price' => $this->_setJobPriceToZero($job_price,$default_agency_price_swithced),
                            'test_price_inc_var' => $price_inc_var,
                            'test_orig_price_from_job_price' => $job_row->job_price
                        );

                        }else{ //not display
                            //$computed_to_price-=$apv_row->amount; ##disable for now > not applicable ofr agency_variation
                        }
    
                    }else{ // surcharge
      
                        $price_variation_total+=$apv_row->amount;

                        if( in_array($apv_row->display_on, $display_on_portal_arr) ) { //display

                            $computed_to_price+=$apv_row->amount;
                            $price_variation_total_display_on-=$apv_row->amount;

                            $display_var_arr[] = array(
                                'type' => $apv_row->apv_type,
                                'item' => "Surcharge",
                                'description' => $service_type_str,
                                'unit_price' => $apv_row->amount,
                                'amount' => $apv_row->amount,
                                'test_marker' => 'agency variation',
                                'test_job_price' => $this->_setJobPriceToZero($job_price,$default_agency_price_swithced),
                                'test_price_inc_var' => $price_inc_var,
                                'test_orig_price_from_job_price' => $job_row->job_price
                            );

                        }else{ //not display
                            //$computed_to_price+=$apv_row->amount; ##disable for now > not applicable ofr agency_variation
                        }
    
                    } 

                }
                     
                 

            }
            ##get agency_price_variation end
                         

            // get property variation
            $pv_sql = $this->db->query("
                SELECT 
                pv.date_applied,
                pv.deleted_ts AS pv_deleted_ts,

                apv.`type` AS apv_type,
                apv.`amount`,
                apv.`scope`,
                apv.updated_date,
                apv.expiry AS apv_expiry,

                apvr.`reason` AS apvr_reason,

                dv.`display_on`,
                dv.type as display_type,
        
                ajt.`type` AS ajt_type,
                ajt.`short_name`
            FROM `property_variation` AS pv        
            LEFT JOIN `agency_price_variation` AS apv ON ( pv.`agency_price_variation` = apv.`id` AND pv.`property_id` = {$property_id} )
            LEFT JOIN `agency_price_variation_reason` AS apvr ON apv.`reason` = apvr.`id`
            LEFT JOIN `display_variation` AS dv ON ( apv.`id` = dv.`variation_id` AND dv.type = 1 )
            LEFT JOIN `alarm_job_type` AS ajt ON ( apv.`scope` = ajt.`id` AND apv.`scope` >= 2 )
            WHERE apv.`agency_id` = {$agency_id}    
            AND (
                apv.`scope` = 1 OR
                apv.`scope` = {$service_type}
            )
            ");  
                

            foreach( $pv_sql->result() as $pv_row ){  

                $agency_var_created_date = date("Y-m-d H:i:s", strtotime($pv_row->created_date));
                $date_applied = date("Y-m-d 23:59:00", strtotime($pv_row->date_applied));
                $job_created = date("Y-m-d H:i:s", strtotime($job_created));
                $agency_price_var_scope = $pv_row->scope;
                $apv_expiry = ($pv_row->apv_expiry!="") ? date("Y-m-d 23:59:00", strtotime($pv_row->apv_expiry)) : "";
                $pv_deleted_ts = ($pv_row->pv_deleted_ts!="") ? date("Y-m-d H:i:s", strtotime($pv_row->pv_deleted_ts)) : "";
                $today_ts = date("Y-m-d 23:59:00");
                
                if( $pv_row->scope==0 ){
                    $service_type_str = "Agency";
                }elseif( $pv_row->scope ==1 ){
                    $service_type_str = "Property";
                }else{
                    $service_type_str = "{$pv_row->short_name} Service";
                }

                if( 
                    //$job_row->job_price != $price_inc_var AND
                   ( $date_applied >= $job_created OR ($agency_var_created_date >=$job_created AND $agency_price_var_scope==0) OR ($agency_var_created_date >=$job_created AND $agency_price_var_scope==2) ) 
                )
                { //job created before variation

                    $job_price = $job_row->job_price;; //set original price from job_price

                }else{

                    //$job_price = $this->_setJobPriceToZero($job_row->job_price,$default_agency_price_swithced);
                    
                    if( $pv_row->apv_type == 1 ){ // discount
    
                        $price_variation_total-=$pv_row->amount;

                        if( in_array($pv_row->display_on, $display_on_portal_arr) ) { //display
    
                            $computed_to_price-=$pv_row->amount;
                            $price_variation_total_display_on+=$pv_row->amount; ##revert
                           
                            $display_var_arr[] = array(
                                'type' => $pv_row->apv_type,
                                'item' => "Discount",
                                'description' => $service_type_str,
                                'unit_price' => $pv_row->amount,
                                'amount' => $pv_row->amount,
                                'test_marker' => 'property variation',
                                'test_job_price' => $this->_setJobPriceToZero($job_price,$default_agency_price_swithced),
                                'test_price_inc_var' => $price_inc_var,
                                'test_orig_price_from_job_price' => $job_row->job_price
                            );
    
                        }else{ //not display
                           //$computed_to_price-=$pv_row->amount; ##disable for now > not applicable ofr proiperty_variation
                        }
    
                        
                    }else{ // surcharge
    
                       $price_variation_total+=$pv_row->amount;

                       if( in_array($pv_row->display_on, $display_on_portal_arr) ) { //display
   
                           $computed_to_price+=$pv_row->amount;
                           $price_variation_total_display_on-=$pv_row->amount; ##revert
                           
                           $display_var_arr[] = array(
                               'type' => $pv_row->apv_type,
                               'item' => "Surcharge",
                               'description' => $service_type_str,
                               'unit_price' => $pv_row->amount,
                               'amount' => $pv_row->amount,
                               'test_marker' => 'property variation',
                               'test_job_price' => $this->_setJobPriceToZero($job_price,$default_agency_price_swithced),
                               'test_price_inc_var' => $price_inc_var,
                               'test_orig_price_from_job_price' => $job_row->job_price
                           );
                       }else{ //not display
                         // $computed_to_price+=$pv_row->amount; ##disable for now > not applicable ofr proiperty_variation
                       }
    
                    }   

                }

                         
        
            }
            // get property variation end


            ##Job Variations
            $jv_sql = $this->db->query("
                SELECT 
                jv.id, 
                jv.amount,
                jv.type,
                jv.reason,
                jv.date_applied,

                dv.variation_id,
                dv.display_on,
                dv.type as display_variation_type
                FROM `job_variation` as jv
                LEFT JOIN `display_variation` AS dv ON ( jv.`id` = dv.`variation_id` AND dv.type = 2 )
                WHERE jv.job_id = {$job_id}
                AND jv.active = 1
            "); 

            foreach( $jv_sql->result_array() as $jv_row ){

                $service_type_str =  "Job Variation";

                if( $jv_row['type'] == 1 ){  ## Discount

                    $price_variation_total-=$jv_row['amount'];

                    //if( in_array($jv_row['display_on'], $display_on_portal_arr) && $jv_row['display_variation_type']==2 ){ // displayed
                    if( in_array($jv_row['display_on'], $display_on_portal_arr) ){ // displayed
                        
                        $computed_to_price-=$jv_row['amount']; ## add to orig price > for job_variation only
                        $price_variation_total_display_on+=$jv_row['amount'];
                        
                        $display_var_arr[] = array(
                            'type' => $jv_row['type'],
                            'item' => "Discount",
                            'description' => $service_type_str,
                            'unit_price' => $jv_row['amount'],
                            'amount' => $jv_row['amount'],
                            'test_marker' => 'job variation'
                        );
                        
                    }else{
                        $computed_to_price-=$jv_row['amount'];
                    }

                }else{ ## Surcharge

                    $price_variation_total+=$jv_row['amount'];

                    //if( in_array($jv_row['display_on'], $display_on_portal_arr) && $jv_row['display_variation_type']==2 ){ // displayed
                    if( in_array($jv_row['display_on'], $display_on_portal_arr) ){ // displayed
                        
                        $computed_to_price+=$jv_row['amount'];  ## add to orig price > for job_variation only
                        $price_variation_total_display_on-=$jv_row['amount'];

                        $display_var_arr[] = array(
                            'type' => $jv_row['type'],
                            'item' => "Surcharge",
                            'description' => $service_type_str,
                            'unit_price' => $jv_row['amount'],
                            'amount' => $jv_row['amount'],
                            'test_marker' => 'job variation'
                        );

                    }else{ // not displayed, gets added to price
                        $computed_to_price+=$jv_row['amount'];
                    }

                }

            }
            ##Job Variations End

            $dynamic_price = $job_price;
            $dynamic_price_total = $dynamic_price+$price_variation_total; // add variations
            
        }

        if( $dynamic_price <= 0 ){ //if price  = 0
            $price_variation_total_display_on = 0;
            $computed_to_price = 0;
            $display_var_arr = NULL;
        }
        
        //$dynamic_price_total_display_on = $dynamic_price+$price_variation_total_display_on+$computed_to_price; // add variations DISPAY 
        $dynamic_price_total_display_on = $dynamic_price+$price_variation_total_display_on; // add variations DISPAY 
        $dynamic_price_included_not_show_var = $dynamic_price+$computed_to_price; ## Orig price + job variation NOT DISPLAY
        $total_price_including_variations = $dynamic_price_total_display_on+$computed_to_price;

        return $ret_arr = array(
            'dynamic_price' => $dynamic_price, ##orig price
            'dynamic_price_total_display_on' => $dynamic_price_total_display_on, ##price with +|- variations > new reverse engineering
            'total_price_including_variations' => $total_price_including_variations,
            'display_var_arr' => $display_var_arr, ##array of variations
            'dynamic_price_included_not_show_var' => $dynamic_price_included_not_show_var,
            'price_variation_total' => $price_variation_total, ##reserve for testing > variation total regardless if display on/off
            'dynamic_price_total' => $dynamic_price_total, ##reserve for testing
            'loc'=>$loc ##reserve for testing
        );

    }


    public function capture_api_data($params){
        
        $job_id = $params['job_id'];
        $api_endpoint = $params['api_endpoint'];
        $http_header = $params['http_header'];
        $payload = $params['payload'];
        $http_status_code = $params['http_status_code'];
        $raw_response = $params['raw_response']; 
        $other_errors = $params['other_errors'];

        $today = date('Y-m-d H:i:s');

        if( $job_id > 0 && ( $api_endpoint != '' || $payload != '' || is_numeric($http_status_code) || $raw_response != '' ) ){

            $insert_data = array(
                'job_id' => $job_id,
                'api_endpoint' => $api_endpoint,
                'http_header' => $http_header,
                'payload' => $payload,
                'http_status_code' => $http_status_code,
                'raw_response' => $raw_response,
                'other_errors' => $other_errors,
                'date_added' => $today
            );            
            $this->db->insert('agency_api_data_capture', $insert_data);

        }        

    }


    public function api_request_limit_counter_and_delay($params){

        $agency_id = $params['agency_id'];
        $api_id = $params['api_id'];
        $request_limit = $params['request_limit'];
        $sleep_interval_sec = $params['sleep_interval_sec'];        

        // get count
        $sql = $this->db->query("
        SELECT `count`
        FROM `agency_api_request_count`
        WHERE `api_id` = {$api_id}
        AND `agency_id` = {$agency_id}
        ");
        $row = $sql->row();

        if( $row->count >= $request_limit ){ // request limit

            // sleep interval
            sleep($sleep_interval_sec); // 1 minute

            // reset to 1
            $count = 1;

        }else{

            // increment count
            $count = ($row->count+1);

        }

        if( $sql->num_rows() > 0 ){ // exist, update

            // update count
            $update_data = array(
                'count' => $count
            );            
            $this->db->where('api_id', $api_id);
            $this->db->where('agency_id', $agency_id);
            $this->db->update('agency_api_request_count', $update_data);

        }else{ //  new, insert

            $insert_data = array(
                'api_id' => $api_id,
                'count' => $count,
                'agency_id' => $agency_id
            );            
            $this->db->insert('agency_api_request_count', $insert_data);

        }                

    }

    public function get_subscription_valid_date_range($property_id){

        if( $property_id > 0 ){

            // get property subscription
            $prop_subs_sql = $this->db->query("
            SELECT `subscription_date`
            FROM `property_subscription`
            WHERE `property_id` = {$property_id}
            ");
            $prop_subs_row = $prop_subs_sql->row();

            if( $prop_subs_sql->num_rows() > 0 ){

                $today = date('Y-m-d');
                $this_year = date("Y");

                $sub_date_month = date("m",strtotime($prop_subs_row->subscription_date));
                $sub_date_day = date("d",strtotime($prop_subs_row->subscription_date));

                // this year using subscription month and day
                $sub_date_this_year = date('Y-m-d', strtotime("{$this_year}-{$sub_date_month}-{$sub_date_day}"));	

                // if today's date is within the subscription date this year
                if( $today >= date('Y-m-d', strtotime($sub_date_this_year) )  ){ 

                    $sub_valid_from = date('Y-m-d', strtotime($sub_date_this_year));

                }else{ // else get previous year, but using subscript date month and day

                    $sub_valid_from = date("Y-{$sub_date_month}-{$sub_date_day}", strtotime("-1 year"));

                }

                // subscription valid to = add 1 year then - 1 day
                $sub_valid_to_temp = date('Y-m-d', strtotime("{$sub_valid_from} +1 year"));
                $sub_valid_to = date('Y-m-d', strtotime("{$sub_valid_to_temp} -1 day"));

                return (object) [
                    'success' => true,
                    'sub_valid_from' => $sub_valid_from,
                    'sub_valid_to' => $sub_valid_to
                ];

            }else{

                return (object) [
                    'success' => false
                ];

            }

            

        }        
     

    }

    /**
     * set and display job price to 0 if original job_price = 0
     * else display new job price value
     */
    private function _setJobPriceToZero($job_price, $new_job_price){

        if( $job_price==0 ){
            return $job_price;
        }else{
            return $new_job_price;
        }

    }

    public function get_job_variations_v2($params){

        $job_id = $params['job_id'];
        $service_type = $params['service_type'];
        $property_id = $params['property_id'];

        $display_on_portal_arr = array(6,7);
        
        $today = date('Y-m-d');

        $dynamic_price = 0;
        $price_variation_total = 0;
        $price_variation_total_display_on = 0;
        $computed_to_price = 0;
        $ret_arr = [];
        $display_var_arr = [];
        $not_display_for_job_variation_only = 0;

        // get jobs data
        $job_sql = $this->db->query("
            SELECT 
                j.`job_price`,
                j.`date`,
                j.created,
                a.`agency_id`
            FROM `jobs` AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE j.`id` = {$job_id}
        ");
        $job_row = $job_sql->row();
        $dynamic_price = $job_row->job_price;

        // get jobs variation
        $jv_sql = $this->db->query("
            SELECT 
            jv.id, 
            jv.amount,
            jv.type,
            jv.reason,
            jv.date_applied,

            dv.variation_id,
            dv.display_on,
            dv.type as display_variation_type
            FROM `job_variation` as jv
            LEFT JOIN `display_variation` AS dv ON ( jv.`id` = dv.`variation_id` AND dv.type = 2 )
            WHERE jv.job_id = {$job_id}
            AND jv.active = 1
        "); 

        if( $jv_sql->num_rows()> 0 ){

            foreach( $jv_sql->result_array() as $jv_row ){

                $service_type_str =  "Job Variation";

                if( $jv_row['type'] == 1 ){  ## Discount

                    if( $dynamic_price>0 ){ //apply discount if price !=0 to avoid negative total

                        $price_variation_total-=$jv_row['amount'];

                        if( in_array($jv_row['display_on'], $display_on_portal_arr) ){ // displayed
                            
                            $computed_to_price-=$jv_row['amount']; ## add to orig price > for job_variation only
                            //$price_variation_total_display_on+=$jv_row['amount'];
                            
                            $display_var_arr[] = array(
                                'type' => $jv_row['type'],
                                'item' => "Discount",
                                'description' => $service_type_str,
                                'unit_price' => $jv_row['amount'],
                                'amount' => $jv_row['amount'],
                                'test_marker' => 'job variation'
                            );
                            
                        }else{
                            $computed_to_price-=$jv_row['amount'];
                            $not_display_for_job_variation_only-=$jv_row['amount']; //show for job_var not display only > calculation leave as is but count this to YM jobs
                        }
                        
                    }

                    

                }else{ ## Surcharge

                    $price_variation_total+=$jv_row['amount'];

                    //if( in_array($jv_row['display_on'], $display_on_portal_arr) && $jv_row['display_variation_type']==2 ){ // displayed
                    if( in_array($jv_row['display_on'], $display_on_portal_arr) ){ // displayed
                        
                        $computed_to_price+=$jv_row['amount'];  ## add to orig price > for job_variation only
                        //$price_variation_total_display_on-=$jv_row['amount'];

                        $display_var_arr[] = array(
                            'type' => $jv_row['type'],
                            'item' => "Surcharge",
                            'description' => $service_type_str,
                            'unit_price' => $jv_row['amount'],
                            'amount' => $jv_row['amount'],
                            'test_marker' => 'job variation'
                        );

                    }else{ // not displayed, gets added to price
                        $computed_to_price+=$jv_row['amount'];
                        $not_display_for_job_variation_only+=$jv_row['amount']; //show for job_var not display only > calculation leave as is but count this to YM jobs
                    }

                }

            }

        }
        // get jobs variation end


        // get property variation
        $pv_sql = $this->db->query("
                SELECT 
                pv.date_applied,
                pv.deleted_ts AS pv_deleted_ts,

                apv.`type` AS apv_type,
                apv.`amount`,
                apv.`scope`,
                apv.updated_date,
                apv.expiry AS apv_expiry,

                apvr.`reason` AS apvr_reason,

                dv.`display_on`,
                dv.type as display_type,
        
                ajt.`type` AS ajt_type,
                ajt.`short_name`
                FROM `property_variation` AS pv        
                LEFT JOIN `agency_price_variation` AS apv ON ( pv.`agency_price_variation` = apv.`id` AND pv.`property_id` = {$property_id} )
                LEFT JOIN `agency_price_variation_reason` AS apvr ON apv.`reason` = apvr.`id`
                LEFT JOIN `display_variation` AS dv ON ( apv.`id` = dv.`variation_id` AND dv.type = 1 )
                LEFT JOIN `alarm_job_type` AS ajt ON ( apv.`scope` = ajt.`id` AND apv.`scope` >= 2 )
                WHERE apv.`agency_id` = {$job_row->agency_id}
                AND (
                    apv.`scope` = 1 OR
                    apv.`scope` = {$service_type}
                )
                AND (
                    pv.`active` = 1 OR
                    DATE(pv.`deleted_ts`) > '{$job_row->date}'
                )
                AND pv.`date_applied` <= '{$job_row->date}'
                AND (
                    apv.`active` = 1 OR 
                    DATE(apv.`deleted_ts`) > '{$job_row->date}'
                )
                AND (
                    apv.`expiry` >= '{$job_row->date}'   
                    OR apv.expiry IS NULL
                )
                AND DATE(apv.`created_date`) <= '{$job_row->date}'
            ");  

        if( $pv_sql->num_rows() > 0 ){

            foreach( $pv_sql->result() as $pv_row ){  

                if( $pv_row->scope==0 ){
                    $service_type_str = "Agency";
                }elseif( $pv_row->scope ==1 ){
                    $service_type_str = "Property";
                }else{
                    $service_type_str = "{$pv_row->short_name} Service";
                }

                if( $pv_row->apv_type == 1 ){ // discount
                    
                    if( $dynamic_price>0 ){ //apply discount if price !=0 to avoid negative total

                        $price_variation_total-=$pv_row->amount;

                        if( in_array($pv_row->display_on, $display_on_portal_arr) ) { //display
    
                            $computed_to_price-=$pv_row->amount;
                            $price_variation_total_display_on+=$pv_row->amount; ##revert
                           
                            $display_var_arr[] = array(
                                'type' => $pv_row->apv_type,
                                'item' => "Discount",
                                'description' => $service_type_str,
                                'unit_price' => $pv_row->amount,
                                'amount' => $pv_row->amount,
                                'test_marker' => 'property variation'
                            );
    
                        }else{ //not display
                           //$computed_to_price-=$pv_row->amount; ##disable for now > not applicable ofr proiperty_variation
                        }

                    }
                   

                    
                }else{ // surcharge

                   $price_variation_total+=$pv_row->amount;

                   if( in_array($pv_row->display_on, $display_on_portal_arr) ) { //display

                       $computed_to_price+=$pv_row->amount;
                       $price_variation_total_display_on-=$pv_row->amount; ##revert
                       
                       $display_var_arr[] = array(
                           'type' => $pv_row->apv_type,
                           'item' => "Surcharge",
                           'description' => $service_type_str,
                           'unit_price' => $pv_row->amount,
                           'amount' => $pv_row->amount,
                           'test_marker' => 'property variation'
                       );
                   }else{ //not display
                     // $computed_to_price+=$pv_row->amount; ##disable for now > not applicable ofr proiperty_variation
                   }

                }  

            }
            
        }
        // get property variation   


        // get agency price variation
        $apv_sql = $this->db->query("
            SELECT 
                apv.`id` AS apv_id,
                apv.`type` AS apv_type,
                apv.`amount`,
                apv.`scope`,
                apv.type,
                apv.updated_date,
                apv.created_date,

                apvr.`reason` AS apvr_reason,

                dv.`display_on`,
                dv.type as display_type,

                ajt.`type` AS ajt_type,
                ajt.`short_name`
            FROM `agency_price_variation` AS apv
            LEFT JOIN `agency_price_variation_reason` AS apvr ON apv.`reason` = apvr.`id`
            LEFT JOIN `display_variation` AS dv ON ( apv.`id` = dv.`variation_id` AND dv.type=1 )
            LEFT JOIN `alarm_job_type` AS ajt ON ( apv.`scope` = ajt.`id` AND apv.`scope` >= 2 )
            WHERE apv.`agency_id` = {$job_row->agency_id}
            AND (
                apv.`scope` = 0 OR
                apv.`scope` = {$service_type}
            )
            AND (
                apv.`active` = 1 OR 
                DATE(apv.`deleted_ts`) > '{$job_row->date}'
            )
            AND apv.`expiry` >= '{$job_row->date}'    
            AND DATE(apv.`created_date`) <= '{$job_row->date}'
            ");  
        
        if( $apv_sql->num_rows() > 0 ){

            foreach( $apv_sql->result() as $apv_row ){  
            
                if( $apv_row->scope==0 ){
                    $service_type_str = "Agency";
                }elseif( $apv_row->scope ==1 ){
                    $service_type_str = "Property";
                }else{
                    $service_type_str = "{$apv_row->short_name} Service";
                }

                if( $apv_row->apv_type == 1 ){ // discount

                    if( $dynamic_price>0 ){ //apply discount if price !=0 to avoid negative total

                        $price_variation_total-=$apv_row->amount;

                        if( in_array($apv_row->display_on, $display_on_portal_arr) ) { //display

                            $computed_to_price-=$apv_row->amount;
                            $price_variation_total_display_on+=$apv_row->amount;
                        
                            $display_var_arr[] = array(
                            'type' => $apv_row->apv_type,
                            'item' => "Discount",
                            'description' => $service_type_str,
                            'unit_price' => $apv_row->amount,
                            'amount' => $apv_row->amount,
                            'test_marker' => 'agency variation'
                        );

                        }else{ //not display
                            //$computed_to_price-=$apv_row->amount; ##disable for now > not applicable ofr agency_variation
                        }
                        
                    }


                }else{ // surcharge
  
                    $price_variation_total+=$apv_row->amount;

                    if( in_array($apv_row->display_on, $display_on_portal_arr) ) { //display

                        $computed_to_price+=$apv_row->amount;
                        $price_variation_total_display_on-=$apv_row->amount;

                        $display_var_arr[] = array(
                            'type' => $apv_row->apv_type,
                            'item' => "Surcharge",
                            'description' => $service_type_str,
                            'unit_price' => $apv_row->amount,
                            'amount' => $apv_row->amount,
                            'test_marker' => 'agency variation'
                        );

                    }else{ //not display
                        //$computed_to_price+=$apv_row->amount; ##disable for now > not applicable ofr agency_variation
                    }

                }          
        
            }

        }   
        // get agency price variation end


        $dynamic_price_total = $dynamic_price+$price_variation_total; // add variations


        /*if( $dynamic_price <= 0 ){ //if price  = 0
            $price_variation_total_display_on = 0;
            $computed_to_price = 0;
            $display_var_arr = NULL;
        }*/

        $dynamic_price_total_display_on = $dynamic_price+$price_variation_total_display_on+$not_display_for_job_variation_only; // add variations DISPAY 
        $dynamic_price_included_not_show_var = $dynamic_price+$computed_to_price; ## Orig price + job variation NOT DISPLAY
        ##$total_price_including_variations = $dynamic_price_total_display_on+$computed_to_price;
        $total_price_including_variations = $dynamic_price+$price_variation_total_display_on+$computed_to_price;

        return $ret_arr = array(
            'dynamic_price' => $dynamic_price, ##orig price
            'dynamic_price_total_display_on' => $dynamic_price_total_display_on, ##price with +|- variations > new reverse engineering
            'total_price_including_variations' => $total_price_including_variations,
            'display_var_arr' => $display_var_arr, ##array of variations
            'dynamic_price_included_not_show_var' => $dynamic_price_included_not_show_var,
            'price_variation_total' => $price_variation_total, ##reserve for testing > variation total regardless if display on/off
            'dynamic_price_total' => $dynamic_price_total ##reserve for testing
        );

        

    }


    // parse the tags on logs link
    public function parseDynamicLink($params) {
        
        $log_id = $params['log_id'];
        $parsed_log = null;

        // get logs data
        $log_sql_str = "
        SELECT 
            `details`,
            `property_id`
        FROM `logs`
        WHERE `log_id` = {$log_id}
        ";
        $log_sql = $this->db->query($log_sql_str);
        $log_row = $log_sql->row();

        // property logs
        $tag = '{p_address}';
        // find the tag
        if( strpos($log_row->details, $tag) !== false ) { 

            if( $log_row->property_id > 0 ){

                // get property data
                $prop_sql_str = "
                SELECT
                    `p`.`property_id`,
                    `p`.`address_1`,
                    `p`.`address_2`,
                    `p`.`address_3`,
                    `p`.`state`,
                    `p`.`postcode`
                FROM `property` AS `p`
                WHERE `p`.`property_id` = {$log_row->property_id}
                ";
                $prop_sql = $this->db->query($prop_sql_str);
                $prop_row = $prop_sql->row();

                // property link
                $vpd_link = "<a href='".($this->config->item("crm_link"))."/view_property_details.php?id={$log_row->property_id}'>{$prop_row->address_1} {$prop_row->address_2} {$prop_row->address_3}</a>";
                
                // replace tags
                $parsed_log = str_replace($tag, $vpd_link, $log_row->details);

            }            

        }
        
        // find the agency user tag
        $tag = 'agency_user';
        if (strpos($log_row->details, $tag) !== false) {

            // break down the tag to get the agency user ID
            $tag_string = $this->get_part_of_string($log_row->details, '{', '}');
            $str_exp = explode(':', $tag_string);
            $aua_id = $str_exp[1];

            // get agency staff user data
            $user_sql_str = "
			SELECT 
                `agency_user_account_id`, 
                `fname`, 
                `lname`
			FROM `agency_user_accounts
			WHERE `agency_user_account_id` = {$aua_id}
			";
            $user_sql = $this->db->query($user_sql_str);
            $user_row = $user_sql->row();
            $user_full_name = "{$user_row->fname} {$user_row->lname}";

            // replace tags
            $parsed_log = str_replace('{' . $tag_string . '}', $user_full_name, $log_row->details);

        }

     
        // find the SATS staff tag
        $tag = 'staff_user';
        if( strpos($log_row->details, $tag) !== false ) {

            // break down the tag to get the agency user ID
            $tag_string = $this->get_part_of_string($log_row->details, '{', '}');
            $str_exp = explode(':', $tag_string);
            $staff_id = $str_exp[1];

            // get SATS staff user data
            $staff_sql_str = "
			SELECT 
                `StaffID`,
                `FirstName`, 
                `LastName`
			FROM `staff_accounts` 
            WHERE `StaffID` = {$staff_id}
			";
            $staff_sql = $this->db->query($staff_sql_str);
            $staff_row = $staff_sql->row();
            $staff_full_name = "{$staff_row->FirstName} {$staff_row->LastName}";

            // replace tags
            $parsed_log = str_replace('{' . $tag_string . '}', $staff_full_name, $log_row->details);

        }

        return $parsed_log;

    }

    // used in parsing tags on logs
    public function get_part_of_string($string, $start_str, $end_str) {

        $startpos = strpos($string, $start_str);
        $endpos = strpos($string, $end_str);

        $length = $endpos - $startpos;
        return substr($string, $startpos + 1, $length - 1);
    }

    // also update on CI sats_crm_class.php -- start
    public function check_if_job_created_before_agency_exclusion_expired($obj){

        $sql = $this->db->query("
        SELECT COUNT(`id`) AS jcount
        FROM `price_increase_excluded_agency`
        WHERE `agency_id` = {$obj->agency_id}
        AND `exclude_until` >= '".date('Y-m-d',strtotime($obj->jcreated))."'
        ");
        
        if( $sql->row()->jcount > 0 ){
            return true;
        }else{
            return false;
        }

    }

    public function check_if_job_created_before_agency_level_variation_expired($obj){

        $sql = $this->db->query("
        SELECT COUNT(`id`) AS jcount
        FROM `agency_price_variation`
        WHERE `agency_id` = {$obj->agency_id}
        AND `expiry` >= '".date('Y-m-d',strtotime($obj->jcreated))."'
        AND `scope` = 0
        AND `active` = 1
        ");
        
        if( $sql->row()->jcount > 0 ){
            return true;
        }else{
            return false;
        }

    }

    public function check_if_job_created_before_property_level_variation_expired($obj){

        $sql = $this->db->query("
        SELECT COUNT(pv.`id`) AS jcount
        FROM `property_variation` AS pv        
		LEFT JOIN `agency_price_variation` AS apv ON ( pv.`agency_price_variation` = apv.`id` AND pv.`property_id` = {$obj->property_id} )
        WHERE apv.`expiry` >= '".date('Y-m-d',strtotime($obj->jcreated))."'
        AND apv.`scope` = 1
        AND apv.`active` = 1
        AND pv.`active` = 1
        ");
        
        if( $sql->row()->jcount > 0 ){
            return true;
        }else{
            return false;
        }

    }

    public function check_if_job_created_before_service_level_variation_expired($obj){

        $sql = $this->db->query("
        SELECT COUNT(`id`) AS jcount
        FROM `agency_price_variation`
        WHERE `agency_id` = {$obj->agency_id}
        AND `expiry` >= '".date('Y-m-d',strtotime($obj->jcreated))."'
        AND `scope` = {$obj->service_type}
        AND `active` = 1
        ");
        
        if( $sql->row()->jcount > 0 ){
            return true;
        }else{
            return false;
        }

    }
    // also update on CI sats_crm_class.php -- end


}
