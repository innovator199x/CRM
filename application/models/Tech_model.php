<?php

class Tech_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
    }


    /**
     * Get Tech Run
     * return query
     */
    public function get_tech_run_info($params){

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('tech_run as tr');
        $this->db->where('country_id',$this->config->item('country'));

        //tech run id filter
        if($params['tech_run_id'] && !empty($params['tech_run_id'])){
            $this->db->where('tech_run_id',$params['tech_run_id']);
        }

        //assigned_tech filter
        if($params['assigned_tech'] && !empty($params['assigned_tech'])){
            $this->db->where('assigned_tech',$params['assigned_tech']);
        }

         //date filter
         if($params['date'] && !empty($params['date'])){
            $this->db->where('date',$params['date']);
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
        if( isset($params['limit']) && $params['limit'] > 0 ){
            $this->db->limit( $params['limit'], $params['offset']);
        }

        $query = $this->db->get();
        if( isset($params['display_query']) && $params['display_query'] == 1 ){
            echo $this->db->last_query();
        }

        return $query;

    }


    public function getTechRunRows($tech_run_id,$country_id,$params=""){


        $params2 = array(
            'sel_query' => "run_complete,assigned_tech as tr_tech,date",
            'tech_run_id' => $tech_run_id,
            'display_query'=> 0

        );
        $tr = $this->get_tech_run_info($params2)->row_array();


        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $is_available_dk_page = false;

        // check if page is tech_run/available_dk/?tr_id=x
        // if true, agency that has allowed door knock set to false should not display on the page
        if (strtolower($this->uri->segment(1)) == "tech_run" && strtolower($this->uri->segment(2)) == "available_dk" ){
            $is_available_dk_page = true;
        }

        $this->db->select($sel_query);
        $this->db->from('tech_run_rows as trr');
        $this->db->join('tech_run as tr','tr.tech_run_id = trr.tech_run_id','left');
        $this->db->join('jobs as j','j.id = trr.row_id','left');
        $this->db->join('property as p','p.property_id = j.property_id','left');
        $this->db->join('`alarm_pwr` AS al_p','p.`preferred_alarm_id` = al_p.`alarm_pwr_id`','left');
        $this->db->join('agency as a','a.agency_id = p.agency_id','left');
        $this->db->join('tech_run_row_color as trr_hc','trr_hc.tech_run_row_color_id = trr.highlight_color','left');

        if ($is_available_dk_page){
            $this->db->join('selected_escalate_job_reasons AS sejr','sejr.job_id = j.id','left');
        }

        $this->db->where('tr.tech_run_id',$tech_run_id);
        $this->db->where('tr.country_id',$country_id);
        $this->db->where('p.deleted',0);
        $this->db->where("(p.is_nlm = 0 OR p.is_nlm IS NULL)"); ## new add nlm filter > dont show nlm
        $this->db->where('a.status','active');
        $this->db->where('j.del_job',0);
        $this->db->where('a.country_id',$country_id);

        if ($is_available_dk_page){
            $this->db->where('a.allow_dk', 1);
            $this->db->where('p.no_dk', 0);
            // 2 = Old Jobs, 8 = Tenant Unresponsive
            $this->db->where('((j.status = "Escalate" AND sejr.escalate_job_reasons_id IN (2, 8)) OR j.status = "To Be Booked")');
        }

        if( $params['hide_hidden']==1 ){
            $this->db->where('trr.hidden',0);
        }

        if( $params['postcode_regions']!="" ){
            $this->db->where_in('p.postcode', $params['postcode_regions']);
        }


        //IF RUN COMPLETE START

        // job listing only, exclude keys and supplier row, used in add keys dropdown FN scrip
        if( $params['job_rows_only'] == 1 ){ 

            $append_keys_and_supplier_row = "
            AND `row_id_type` = 'job_id'
            ";

        }else{ // default

            $append_keys_and_supplier_row = "
            OR (
                trr.`row_id_type` = 'keys_id' AND tr.`tech_run_id` = {$tech_run_id}
            )
            OR (
                trr.`row_id_type` = 'supplier_id' AND tr.`tech_run_id` = {$tech_run_id}
            )
            ";

        }



        // remove booked and on hold filter on DK listing
        if(  $params['dk_query_listing'] == 1 ){

            $dk_query_listing = null;

        }else{

            // on hold status filter
            $dk_query_listing = "
            OR j.`status` = 'Booked'
            OR j.`status` = 'On Hold'
            OR j.`status` = 'On Hold - COVID'
            ";

        }


        if(!empty($tr['tr_tech'])){

            // dont allow DK listing to see completed jobs
            if( ( $tr['run_complete']==1 && $params['dk_query_listing'] != 1 ) ||  $params['display_only_booked']==1){

                // apply on admin version only, dont show on techs
                $admins_only_str = null;
                if( $params['admins_only'] == 1 ){
                    $admins_only_str = "
                    OR (
                        j.`status` = 'To Be Booked' AND
                        j.`comments` = 'OS Call Over'
                    )
                    ";
                }

                $filter = "
                    (
                        j.`status` = 'Booked'
                        OR j.`status` = 'Pre Completion'
                        OR j.`status` = 'Merged Certificates'
                        OR j.`status` = 'Completed'
                        OR (
                            j.`status` = 'To Be Booked' AND
                            j.`door_knock` = 1	
                        ) 
                        {$admins_only_str}                       
                    )
                    AND (
                        j.`assigned_tech` = {$tr['tr_tech']}
                        AND j.`date` = '{$tr['date']}'
                    )
                    {$append_keys_and_supplier_row}
                ";
                $this->db->where($filter);

            }else{

                //$append_onhold = " OR j.`status` = 'On Hold' ";

                $filter = "
                (
                    j.`status` = 'To Be Booked'
                    OR j.`status` = 'Booked'
                    OR j.`status` = 'DHA'
                    OR j.`status` = 'Escalate'
                    OR j.`status` = 'Allocate'
                    {$dk_query_listing}
                )
                AND (
                    j.`assigned_tech` = {$tr['tr_tech']}
                    OR j.`assigned_tech` = 0
                    OR j.`assigned_tech` IS NULL
                )
                AND(
                    j.`date` = '{$tr['date']}'
                    OR j.`date` IS NULL
                    OR j.`date` = '0000-00-00'
                    OR j.`date` = ''
                )
                OR (
                    trr.`row_id_type` = 'keys_id' AND tr.`tech_run_id` = {$tech_run_id}
                )
                OR (
                    trr.`row_id_type` = 'supplier_id' AND tr.`tech_run_id` = {$tech_run_id}
                )
                {$append_keys_and_supplier_row}
                ";
                $this->db->where($filter);

            }
        }
        //IF RUN COMPLETE END



        // sort
        if (isset($params['sort_list'])) {
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }

        // limit
        if( isset($params['limit']) && $params['limit'] > 0 ){
            $this->db->limit( $params['limit'], $params['offset']);
        }

        $query = $this->db->get();
        if( isset($params['display_query']) && $params['display_query'] == 1 ){
            echo $this->db->last_query();
        }

        return $query;


    }


    function getJobRowData($job_id,$country_id){
        return $this->db->query("
            SELECT
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

                p.`property_id`,
                p.`address_1` AS p_address_1,
                p.`address_2` AS p_address_2,
                p.`address_3` AS p_address_3,
                p.`state` AS p_state,
                p.`postcode` AS p_postcode,
                p.`key_number`,
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
                p.`requires_ppe`,
                p.`preferred_alarm_id`,   
                p.`service_garage`,             

                al_p.`alarm_make` AS pref_alarm_make,

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
                a.`electrician_only`



            FROM jobs AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `alarm_pwr` AS al_p ON p.`preferred_alarm_id` = al_p.`alarm_pwr_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
            WHERE p.`deleted` =0
            AND a.`status` = 'active'
            AND j.`del_job` = 0
            AND a.`country_id` = {$country_id}
            AND j.`id` = {$job_id}
        ");
    }

    function getJobRowDataWithJobIds($jobIds,$countryId){
        $jobIdsString = implode(',', $jobIds);
        $result = $this->db->query("
            SELECT
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

                p.`property_id`,
                p.`address_1` AS p_address_1,
                p.`address_2` AS p_address_2,
                p.`address_3` AS p_address_3,
                p.`state` AS p_state,
                p.`postcode` AS p_postcode,
                p.`key_number`,
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
                a.`electrician_only`



            FROM jobs AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
            WHERE p.`deleted` =0
            AND a.`status` = 'active'
            AND j.`del_job` = 0
            AND a.`country_id` = {$countryId}
            AND j.`id` IN ({$jobIdsString})
            GROUP BY j.`id`
        ");
        if ($result->num_rows() > 0) {
            return $result->result_array();
        }
        return [];
    }


    /**
     * Check if electrician
     * return boolean
     */
    public function isElectrician($tech_id){

        $this->db->select('StaffID');
        $this->db->from('staff_accounts');
        $this->db->where('StaffID', $tech_id);
        $this->db->where('is_electrician', 1);
        $query = $this->db->get();

        if($query->num_rows()>0){
            return true;
        }else{
            return false;
        }

    }


    public function getTechRunKeys($tech_run_keys_id){


        $sel_query = "thk.`tech_run_keys_id`, thk.`action`, thk.`number_of_keys`, thk.`agency_staff`, thk.`completed`, thk.`completed_date`, thk.`sort_order`, thk.`signature_svg`, thk.`refused_sig`,
        a.`agency_id`, a.`agency_name`, a.`address_1`, a.`address_2`, a.`address_3`, a.`state`, a.`postcode`, a.`phone`, a.`phone` AS a_phone, a.`agency_hours`, a.`lat`, a.`lng`,
        agen_add.`id` AS agen_add_id,
        agen_add.`address_1` AS agen_add_street_num, 
        agen_add.`address_2` AS agen_add_street_name, 
        agen_add.`address_3` AS agen_add_suburb, 
        agen_add.`state` AS agen_add_state, 
        agen_add.`postcode` AS agen_add_postcode,
        agen_add.`lat` AS agen_add_lat,
        agen_add.`lng` AS agen_add_lng			
        ";

        $this->db->select($sel_query);
        $this->db->from('tech_run_keys as thk');
        $this->db->join('agency as a','a.agency_id = thk.agency_id','left');
        $this->db->join('agency_addresses AS agen_add','thk.agency_addresses_id = agen_add.id','left');
        $this->db->where('thk.tech_run_keys_id', $tech_run_keys_id);
        $this->db->where('a.country_id', $this->config->item('country'));
        $this->db->group_start();
        $this->db->where('thk.deleted',0);
        $this->db->or_where('thk.deleted', NULL);
        $this->db->group_end();
        $query = $this->db->get();
        return $query;

    }
    public function getTechRunKeysByIds($techRunKeysIds){


        $sel_query = "thk.`tech_run_keys_id`, thk.`action`, thk.`number_of_keys`, thk.`agency_staff`, thk.`completed`, thk.`completed_date`, thk.`sort_order`, thk.`signature_svg`,
        a.`agency_id`, a.`agency_name`, a.`address_1`, a.`address_2`, a.`address_3`, a.`state`, a.`postcode`, a.`phone`, a.`agency_hours`, a.`lat`, a.`lng`";

        $this->db->select($sel_query);
        $this->db->from('tech_run_keys as thk');
        $this->db->join('agency as a','a.agency_id = thk.agency_id','left');
        $this->db->where_in('thk.tech_run_keys_id', $techRunKeysIds);
        $this->db->where('a.country_id', $this->config->item('country'));
        $this->db->group_start();
        $this->db->where('thk.deleted',0);
        $this->db->or_where('thk.deleted', NULL);
        $this->db->group_end();

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return [];

    }

    /**
     * Get Vechicle by tech id
     * $params tech_id/staff_id
     * return query
     */
    public function getVehicleByTechId($tech_id){

        $this->db->select('*');
        $this->db->from('staff_accounts as sa');
        $this->db->join('vehicles as v','v.StaffID = sa.StaffID','left');
        $this->db->where('sa.StaffID', $tech_id);
        $query = $this->db->get();
        return $query;

    }


    /**
     * Get KMS by Vehicle id
     * $params vechicle id
     * return query
     */
    public function getKmsByVehicleId($id){

        $this->db->select('*');
        $this->db->from('kms as k');
        $this->db->join('vehicles as v','v.vehicles_id = k.vehicles_id','left');
        $this->db->where('k.vehicles_id', $id);
        $this->db->where('v.country_id', $this->config->item('country'));
        $this->db->order_by('k.kms_updated', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        return $query;

    }

    /**
     * GET KMS BY STAFF ID
     */
    public function getKmsByStaffId($id){

        $this->db->select('*, v.vehicles_id as v_vehicle_id');
        $this->db->from('kms as k');
        $this->db->join('vehicles as v','v.vehicles_id = k.vehicles_id','left');
        $this->db->where('v.StaffID', $id);
        $this->db->where('v.country_id', $this->config->item('country'));
        $this->db->order_by('k.kms_updated', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        return $query;

    }

    /**
     * Check first visit
     */
    function checkfirstVisit($job_id,$service){

        if( $job_id > 0 ){

            // FIRST VISIT: SA = no smoke alarms, CW = no windows, bundle = no smoke alarms
            if( $service == 6 ){ // Corded Window

                // Corded Window
                $sql = $this->db->query("
                    SELECT COUNT(`corded_window_id`) AS cw_count
                    FROM `corded_window`
                    WHERE `job_id` ={$job_id}
                ");
                $count = $sql->row()->cw_count;

            }else{

                // Smoke Alarms
                $sql = $this->db->query("
                    SELECT COUNT(`alarm_id`) AS sa_count
                    FROM `alarm`
                    WHERE `job_id` ={$job_id}
                ");
                $count = $sql->row()->sa_count;

            }

            if( $count == 0 ){
                return true;
            }else{
                return false;
            }

        }


    }

    // check first visit
    public function check_prop_first_visit($property_id) {

        if( $property_id > 0 ){

            // exclude other supplier(1) and upfront bill(2)
            $job_sql = $this->db->query("
                SELECT COUNT(id) AS j_count
                FROM `jobs`
                WHERE `property_id` = {$property_id}
                AND `status` = 'Completed'
                AND `assigned_tech` != 1
                AND `assigned_tech` != 2
            ");

            $job_row = $job_sql->row();

            if( $job_row->j_count == 0 ) { // first visit
                return true;
            } else {
                return false;
            }

        }

    }

    public function checkPropertyFirstVisitsByIds($propertyIds) {
        if (!empty($propertyIds)) {
            $propertyIdsString = implode(',', $propertyIds);
            $jobResult = $this->db->query("
                SELECT COUNT(id) AS j_count, `property_id`
                FROM `jobs`
                WHERE `property_id` IN ({$propertyIdsString})
                AND `status` = 'Completed'
                AND `assigned_tech` != 1
                AND `assigned_tech` != 2
                GROUP BY `property_id`
            ");

            if ($jobResult->num_rows() > 0) {
                return $jobResult->result_array();
            }
        }
        return [];
    }

    /**
     * Get Job log by job id
     * return query
     */
    public function getJobLogByJobId($params){

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('job_log as j');
        $this->db->join('staff_accounts sa','sa.StaffID = j.staff_id','left');
        $this->db->where('j.deleted', 0);

        if($params['job_id'] && !empty($params['job_id'])){
            $this->db->where('j.job_id', $params['job_id']);
        }

        if($params['eventdate'] && !empty($params['eventdate'])){
            $this->db->where('j.eventdate', $params['eventdate']);
        }

        if($params['contact_type'] && !empty($params['contact_type'])){
            $this->db->where('j.contact_type', $params['contact_type']);
        }

        $query = $this->db->get();
        return $query;

    }

    public function getJobLogByJobIds($params){

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('job_log as j');
        $this->db->join('staff_accounts sa','sa.StaffID = j.staff_id','left');
        $this->db->where('j.deleted', 0);

        if ($params['job_ids'] && !empty($params['job_ids'])) {
            $this->db->where_in('j.job_id', $params['job_ids']);
        }

        if ($params['eventdate'] && !empty($params['eventdate'])) {
            $this->db->where('j.eventdate', $params['eventdate']);
        }

        if ($params['contact_type'] && !empty($params['contact_type'])) {
            $this->db->where('j.contact_type', $params['contact_type']);
        }

        if ($params['group'] && !empty($params['group'])) {
            $this->db->group_by($params['group']);
        }

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return [];

    }


    public function getNumberOfBookedKeys($tech_id,$date,$country_id,$agency_id){

        $sql_str = "
        SELECT j.`id` AS jid
        FROM jobs AS j
        LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id`
        WHERE p.`deleted` =0
        AND a.`status` = 'active'
        AND j.`del_job` = 0
        AND a.`country_id` = {$country_id}
        AND j.`assigned_tech` ={$tech_id}
        AND j.`date` = '{$date}'
        AND a.`agency_id` = {$agency_id}
        AND j.`key_access_required` = 1
        AND(
            j.`status` = 'Booked'
            OR j.`status` = 'Pre Completion'
            OR j.`status` = 'Merged Certificates'
            OR j.`status` = 'Completed'
        )
        ";
        $sql = $this->db->query($sql_str);

        return $sql->num_rows();

    }

    public function is_key_already_picked_up($tech_id,$date,$agency_id){

        $sql_str = "
        SELECT COUNT(trk.`tech_run_keys_id`) AS trk_count
        FROM `tech_run_keys` AS trk
        WHERE trk.`agency_id` = {$agency_id}
        AND trk.`assigned_tech` ={$tech_id}
        AND trk.`date` = '{$date}'
        AND trk.`action` = 'Pick Up'
        AND trk.`completed` = 1
        AND trk.`completed_date` != ''    
        ";
        $sql = $this->db->query($sql_str);
        $trk_count = $sql->row()->trk_count;

        return ( $trk_count > 0 )?true:false;

    }

    public function getNumberOfBookedKeysByAgencyIds($techId, $date, $countryId, $agencyIds){

        $agencyIdsString = implode(',', $agencyIds);
        $sql_str = "
        SELECT a.`agency_id` AS agency_id, COUNT(j.`id`) as j_count
        FROM jobs AS j
        LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id`
        WHERE p.`deleted` =0
        AND a.`status` = 'active'
        AND j.`del_job` = 0
        AND a.`country_id` = {$countryId}
        AND j.`assigned_tech` = {$techId}
        AND j.`date` = '{$date}'
        AND a.`agency_id` IN ({$agencyIdsString})
        AND j.`key_access_required` = 1
        AND(
            j.`status` = 'Booked'
            OR j.`status` = 'Pre Completion'
            OR j.`status` = 'Merged Certificates'
            OR j.`status` = 'Completed'
        )
        GROUP BY a.`agency_id`
        ";
        $sql = $this->db->query($sql_str);

        if ($sql->num_rows() > 0) {
            return $sql->result_array();
        }
        return [];
    }


    public function getTechRunSuppliers($tech_run_suppliers_id){

        return $this->db->query("
            SELECT
                trs.`tech_run_suppliers_id`, sup.`suppliers_id`, sup.`company_name`, sup.`address` AS sup_address, sup.`phone`, sup.`lat`, sup.`lng`, sup.`on_map`
            FROM `tech_run_suppliers` AS trs
            LEFT JOIN `suppliers` AS sup ON trs.`suppliers_id` = sup.`suppliers_id`
            WHERE trs.`tech_run_suppliers_id` = {$tech_run_suppliers_id}
            AND (
                trs.`deleted` = 0
                OR trs.`deleted` IS NULL
            )
        ");

    }

    public function getTechRunSuppliersByIds($suppliersIds){
        $supplierIdsString = implode(',', $suppliersIds);

        $result = $this->db->query("
            SELECT
                trs.`tech_run_suppliers_id`, sup.`suppliers_id`, sup.`company_name`, sup.`address` AS sup_address, sup.`phone`, sup.`lat`, sup.`lng`, sup.`on_map`
            FROM `tech_run_suppliers` AS trs
            LEFT JOIN `suppliers` AS sup ON trs.`suppliers_id` = sup.`suppliers_id`
            WHERE trs.`tech_run_suppliers_id` IN ({$supplierIdsString})
            AND (
                trs.`deleted` = 0
                OR trs.`deleted` IS NULL
            )
            GROUP BY sup.`suppliers_id`
        ");

        if ($result->num_rows() > 0) {
            return $result->result_array();
        }
        return [];
    }

    public function cutFullAddress($address){

        $find_array = array('NSW','VIC','QLD','ACT','TAS','SA','WA','NT');

        foreach( $find_array as $state ){

            $cut_start = strpos($address, $state);
            //echo "index: {$cut_start}";
            if( $cut_start>0 ){
                $cut_index = $cut_start;

            }

        }

        $find_array = array('New South Wales','Australian Capital Territory','Victoria','South Australia','Queensland','Tasmania','Western Australia','Northern Territory');

        foreach( $find_array as $state ){

            $cut_start = strpos($address, $state);
            //echo "index: {$cut_start}";
            if( $cut_start>0 ){
                $cut_index = $cut_start;

            }

        }

        return substr($address,0,$cut_index);

    }


    public function techRunDragAndDropSort($tr_id,$trw_ids){

        $i = 2;
        foreach($trw_ids as $trw_id){

            if($trw_id!=""){

                $this->db->query("
				UPDATE `tech_run_rows`
				SET
					`sort_order_num` = {$i},
					`dnd_sorted` = 1
                WHERE `tech_run_rows_id` = {$trw_id}
                AND `tech_run_id` = {$tr_id}
                ");

                $i++;

            }

        }

    }



    public function appendTechRunNewListings($tech_run_id,$tech_id,$date,$sub_regions,$country_id,$isAssigned="",$display_only_booked=""){


        $tr_sql = $this->db->select('agency_filter')->from('tech_run')->where('tech_run_id', $tech_run_id)->get();
        $tr = $tr_sql->row_array();

        $j_sql = $this->getSTRnewlyAddedListing($tech_run_id,$tech_id,$date,$sub_regions,$country_id,$isAssigned,$display_only_booked,$tr['agency_filter']);

        $num_rows = $j_sql->num_rows();

        if( $num_rows>0 ){

            if($tech_run_id!=""){ //tech run id not empty proceed

                foreach($j_sql->result_array() as $j){

                    $str3 = "
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
                            {$tech_run_id},
                            'job_id',
                            {$j['jid']},
                            999999,
                            0,
                            '".date('Y-m-d H:i:s')."',
                            1
                        )
                    ";
                    $this->db->query($str3);
                    $num_jobs++;

                }
            }

        }

        // delete duplicates
        $this->deleteTechRunDuplicates($tech_run_id);

        return $num_rows;


    }


    public function getSTRnewlyAddedListing($tech_run_id,$tech_id,$date,$sub_regions,$country_id='',$isAssigned="",$display_only_booked="",$agency_id){


        $region_str = $this->getRegionFilterforQuery($tech_id,$date,$sub_regions,$country_id,$tech_run_id,$isAssigned,$display_only_booked,$agency_id);


        $str = "
            SELECT
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

                p.`property_id`,
                p.`address_1` AS p_address_1,
                p.`address_2` AS p_address_2,
                p.`address_3` AS p_address_3,
                p.`state` AS p_state,
                p.`postcode` AS p_postcode,
                p.`key_number`,
                p.`lat` AS p_lat,
                p.`lng` AS p_lng,

                a.`agency_id`,
                a.`agency_name`,
                a.`address_1` AS a_address_1,
                a.`address_2` AS a_address_2,
                a.`address_3` AS a_address_3,
                a.`state` AS a_state,
                a.`postcode` AS a_postcode,
                a.`phone` AS a_phone,
                a.`allow_dk`
            FROM jobs AS j
            LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id`
            {$region_str}
            AND j.`id` NOT IN(
                SELECT trr.`row_id`
                FROM  `tech_run_rows` AS trr
                WHERE  trr.`row_id_type` =  'job_id'
                AND trr.`status` = 1
                AND trr.`tech_run_id` = {$tech_run_id}
            )
            ORDER BY j.`sort_order`
        ";

        //echo "<hr />";
        return $this->db->query($str);
    }



    // query needed for sub region filter to work
    public function getRegionFilterforQuery($tech_id,$date,$sub_regions,$country_id,$tech_run_id="",$isAssigned="",$display_only_booked="",$agency_id){


        // if electrician?
        $tsql = $this->db->query("
            SELECT *
            FROM  `staff_accounts`
            WHERE `StaffID` = {$tech_id}
            AND `is_electrician` = 1
        ");
        $isElectrician = ( $tsql->num_rows()>0 )?true:false;
        //echo "is Electrician? ".var_dump($isElectrician);

        // standard filter condition

        $sql_str = "
            LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
            WHERE p.`deleted` =0
            AND a.`status` = 'active'
            AND j.`del_job` = 0
            AND a.`country_id` = {$country_id}
        ";



        //echo "Tech Run: {$tech_run_id}";

        if( $tech_run_id!="" ){

            // if run complete
            $rt_sql = $this->db->query("
                SELECT *
                FROM  `tech_run`
                WHERE  `tech_run_id` = {$tech_run_id}
                AND `run_complete` = 1
            ");

            $isRunComplete = ( $rt_sql->num_rows()>0 )?true:false;

        }else{
            $isRunComplete = false;
        }

        //echo "Is run complete: ".var_dump($isRunComplete);


        // show only booked if run complete
        if( $isRunComplete == true || $display_only_booked==1 ){

            // if complete
            $sql_str .= "
                AND j.`assigned_tech` ={$tech_id}
                AND j.`date` = '{$date}'
                AND (
                    j.`status` = 'Booked'
                    OR j.`status` = 'Pre Completion'
                    OR j.`status` = 'Merged Certificates'
                    OR j.`status` = 'Completed'
                    OR (
                        j.`status` = 'To Be Booked' AND
                        j.`door_knock` = 1	
                    )
                )
            ";

        }else{

            // if region filter is present
            if($sub_regions!=""){



                    // enable/disable on hold
                    $append_onhold = " OR j.`status` = 'On Hold' ";


                    if($isAssigned==1){

                        // fetch job via assigned
                        $sql_str .= "
                            AND (
                                j.`status` = 'To Be Booked'
                                OR j.`status` = 'Booked'
                                OR j.`status` = 'DHA'
                                OR j.`status` = 'Escalate'
                                {$append_onhold}
                                OR j.`status` = 'Allocate'
                            )
                            AND j.`assigned_tech` = {$tech_id}
                            AND j.`date` = '{$date}'
                        ";

                    }else{
                    

                        // get all postcode that belong to passed multiple sub region
                        $sel_query = "pc.`postcode`";                
                        $sub_region_params = array(
                            'sel_query' => $sel_query,
                            'sub_region_id_imp' => $sub_regions,                                                          
                            'deleted' => 0,
                            'display_query' => 0
                        );
                        $postcode_sql = $this->system_model->get_postcodes($sub_region_params);
                        
                        $postcodes_arr = [];
                        foreach ($postcode_sql->result() as $postcode_row) {
                            $postcodes_arr[] = $postcode_row->postcode;
                        }

                        if( count($postcodes_arr) > 0 ){
                            $postcodes_imp = implode(",", $postcodes_arr);
                        }                        

                        // Agency filter
                        $sql_str_filter = '';
                        if( $agency_id!='' ){
                            $sql_str_filter .= "
                                AND a.`agency_id` IN ({$agency_id})
                            ";
                        }

                        $sql_str .= "
                            AND p.`postcode` IN ( {$postcodes_imp} )
                            AND (
                                j.`status` = 'To Be Booked'
                                OR j.`status` = 'Booked'
                                OR j.`status` = 'DHA'
                                OR j.`status` = 'Escalate'
                                {$append_onhold}
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
                            {$sql_str_filter}
                        ";

                    }



                }else{

                    // if no regions
                    $sql_str .= "
                        AND (
                            j.`status` = 'To Be Booked'
                            OR j.`status` = 'Booked'
                            OR j.`status` = 'DHA'
                            OR j.`status` = 'Escalate'
                            {$append_onhold}
                            OR j.`status` = 'Allocate'
                        )
                        AND j.`assigned_tech` ={$tech_id}
                        AND j.`date` = '{$date}'
                    ";

                }

        }

        return $sql_str;

    }


    public function deleteTechRunDuplicates($tech_run_id){

        $dup_sql = $this->getTechRunDuplicates($tech_run_id);
        if( $dup_sql->num_rows() > 0  ){
            foreach($dup_sql->result_array() as $dup){
                $this->deleteTechRunRowDuplicates($dup['tech_run_rows_id']);
            }
        }

    }

    public function getTechRunDuplicates($tech_run_id){
        return $this->db->query("
            SELECT trr.`tech_run_rows_id`
            FROM  `tech_run_rows` AS trr
            WHERE trr.`row_id_type` =  'job_id'
            AND trr.`tech_run_id` ={$tech_run_id}
            GROUP BY trr.`row_id`
            HAVING COUNT( trr.`row_id` ) >1
        ");
    }

    public function deleteTechRunRowDuplicates($trr_id){

        if($trr_id!=""){
            $this->db->where('tech_run_rows_id', $trr_id);
            $this->db->delete('tech_run_rows');
        }

    }

    /**
     * Get Job sched by date
     */
    public function getJobSched(){

        $this->db->select('j.status, p.address_1, p.address_2, p.address_3, p.postcode, j.time_of_day, j.id, sa.FirstName, sa.LastName, j.date');
        $this->db->from('jobs as j');
        $this->db->join('property as p', 'p.property_id = j.property_id','left');
        $this->db->join('agency as a', 'a.agency_id = p.agency_id','left');
        $this->db->join('staff_accounts as sa', 'sa.StaffID = j.assigned_tech','left');
        $this->db->where('j.status','Booked');
        #$this->db->where('j.date',$jobdate);
        $this->db->where('p.deleted',0);
        $this->db->where("( p.is_nlm = 0 OR p.is_nlm IS NULL )");
        $this->db->where('j.del_job',0);
        $this->db->where('a.country_id',$this->config->item('country'));
        $this->db->order_by('sa.StaffID');
        $query = $this->db->get();
        return $query;

    }

    public function get_tech_run_id_by_date(){
        // check tech run
        $this->db->select("*");
        $this->db->from('tech_run');
        $this->db->where('assigned_tech', $this->session->staff_id);
        $this->db->where('date', date('Y-m-d'));
        $query = $this->db->get();
        return $query;
    }

    public function getTechRunIdForStaff($staffId, $date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }

        $this->db->select("*");
        $this->db->from('tech_run');
        $this->db->where('assigned_tech', $staffId);
        $this->db->where('date', $date);
        $query = $this->db->get();
        return $query->row_array();
    }





}
