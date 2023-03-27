<?php

class Agency_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    public function get_agency($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`agency` AS a');
        //$this->db->join('`countries` AS c', 'a.`country_id` = c.`country_id`', 'left');
        // set joins
        if ($params['join_table'] > 0) {

            foreach ($params['join_table'] as $join_table) {

                if ($join_table == 'country') {
                    $this->db->join('`countries` AS c', 'a.`country_id` = c.`country_id`', 'left');
                }
                if ($join_table == 'salesrep') {
                    $this->db->join('`staff_accounts` AS sa', 'a.`salesrep` = sa.`StaffID`', 'left');
                }

                //by gherx
                /*if ($join_table == 'postcode_regions') {
                    $this->db->join('`postcode_regions` AS pr', 'pr.postcode_region_id = a.postcode_region_id', 'left');
                }*/
                //updated to new table >Gherx
                if ($join_table == 'postcode_regions') {
                    $this->db->join('`sub_regions` AS sr', 'sr.sub_region_id = a.postcode_region_id', 'left');
                }

                //by gherx > NEW TABLE
                if ($join_table == 'postcode') {
                    $this->db->join('`postcode` AS pc', 'pc.postcode = a.postcode', 'left');
                }

                //by gherx
                if ($join_table == 'franchise_groups') {
                    $this->db->join('`franchise_groups` AS fg', 'fg.franchise_groups_id = a.franchise_groups_id', 'left');
                }

                //by gherx
                if ($join_table == 'agency_using') {
                    $this->db->join('`agency_using` AS au', 'au.agency_using_id = a.agency_using_id', 'left');
                }

                //by gherx
                if ($join_table == 'agency_event_log') {
                    $this->db->join('`agency_event_log` AS ael', 'ael.agency_id = a.agency_id', 'left');
                }

                if ($join_table == 'trust_account_software') {
                    $this->db->join('`trust_account_software` AS tas', 'a.trust_account_software = tas.trust_account_software_id', 'left');
                }

                ## join agency markers
                if ($join_table == 'agency_markers') {
                    $this->db->join('`agency_markers` AS am', 'a.agency_id = am.agency_id', 'left');
                }

                if ($join_table == 'agency_other_pref') {
                    $this->db->join('`agency_other_pref` AS aop', 'a.agency_id = aop.agency_id', 'left');
                }

                if ($join_table == 'agency_priority') {
                    $this->db->join('`agency_priority` AS aght', 'a.agency_id = aght.agency_id', 'left');
                }
                if ($join_table == 'agency_priority_marker_definition') {
                    $this->db->join('`agency_priority_marker_definition` AS apmd', 'aght.priority = apmd.priority', 'left');
                }
                
            }
        }

        // custom joins
        if (isset($params['custom_joins']) && $params['custom_joins'] != '') {
            $this->db->join($params['custom_joins']['join_table'], $params['custom_joins']['join_on'], $params['custom_joins']['join_type']);
        }

        // custom joins (gherx)
        if (isset($params['custom_joins_ver2']) && $params['custom_joins_ver2'] != '') {
            $this->db->join($params['custom_joins_ver2']['join_table'], $params['custom_joins_ver2']['join_query'], $params['custom_joins_ver2']['join_type']);
        }

        // filter
        if (isset($params['agency_id']) && $params['agency_id'] != '') {
            $this->db->where('a.`agency_id`', $params['agency_id']);
        }

        if (isset($params['a_status']) && $params['a_status'] != '') {
            $this->db->where('a.`status`', $params['a_status']);
        }
        if(isset($params['a_status']) && $params['a_status'] == '' && $params['a_deactivated_ts'] == true){
            $this->db->where_in('a.`status`', array('target', 'deactivated'));
        }
        if (isset($params['a_deactivated_ts']) && $params['a_deactivated_ts'] == true) {
            $this->db->where_not_in('a.`deactivated_ts`', array('', '0000-00-00'));
        }
        if ($params['date_from_deac'] != '' && $params['date_to_deac'] != '') {
            $deactivated_date = "a.deactivated_ts BETWEEN '".$params['date_from_deac']."' AND '".$params['date_to_deac']."'";
            $this->db->where($deactivated_date);
        }

        if (isset($params['salesrep']) && $params['salesrep'] != '') {
            $this->db->where('a.`salesrep`', $params['salesrep']);
        }

        if (is_numeric($params['initial_setup_done'])) {
            $this->db->where('a.`initial_setup_done`', $params['initial_setup_done']);
        }

        //search
        if (isset($params['search']) && $params['search'] != '') {
            $search_filter = "CONCAT_WS(' ', LOWER(a.agency_name), LOWER(a.address_1), LOWER(a.address_2), LOWER(a.address_3), LOWER(a.state), LOWER(a.postcode))";
            $this->db->like($search_filter, $params['search']);
        }

        //agency name search
        if (isset($params['agency_name']) && $params['agency_name'] != '') {
            $agency_name_filter = "a.`agency_name` LIKE '%{$params['agency_name']}%'";
            $this->db->where($agency_name_filter);
        }

        //agency_using_id > by gherx
        if (isset($params['agency_using_id']) && $params['agency_using_id'] != '') {
            $this->db->where('a.`agency_using_id`', $params['agency_using_id']);
        }

        //state filter > by gherx
        if (isset($params['state']) && $params['state'] != '') {
            $this->db->where('a.`state`', $params['state']);
        }

        // postcodes > by gherx
        if (isset($params['postcodes']) && $params['postcodes'] != '') {
            $this->db->where("a.`postcode` IN ( {$params['postcodes']} )");
        }

        // postcodes > by gherx
        if (isset($params['country_id']) && $params['country_id'] != '') {
            $this->db->where('a.`country_id`', $params['country_id']);
        }


        // allow_upfront_billing
        if (isset($params['allow_upfront_billing']) && $params['allow_upfront_billing'] != '') {
            $this->db->where('a.`allow_upfront_billing`', $params['allow_upfront_billing']);
        }

        // trust account software
        if ($params['trust_account_software'] > 0) {
            $this->db->where('a.`trust_account_software`', $params['trust_account_software']);
        }

        // custom filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
        }

        if (isset($params['high_touch_filter']) && $params['high_touch_filter'] != '') {
            $this->db->where('aght.`priority`', $params['high_touch_filter']);
        }

        // custom filter
        if (isset($params['custom_where_arr'])) {
            foreach ($params['custom_where_arr'] as $index => $custom_where) {
                if ($custom_where != '') {
                    $this->db->where($custom_where);
                }
            }
        }

        //deleted filter > default exclude deleted agency
        if( $params['a_deleted']!==false ){

            if ( isset($params['a_deleted']) && is_numeric($params['a_deleted']) ) {
                $this->db->where('a.`deleted`', $params['a_deleted']);
            }else{
                $this->db->where('a.`deleted`', 0);
            }
            
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

    public function get_agency_export($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`agency` AS a');
        //$this->db->join('`countries` AS c', 'a.`country_id` = c.`country_id`', 'left');
        // set joins
        if ($params['join_table'] > 0) {

            foreach ($params['join_table'] as $join_table) {

                if ($join_table == 'country') {
                    $this->db->join('`countries` AS c', 'a.`country_id` = c.`country_id`', 'left');
                }
                if ($join_table == 'salesrep') {
                    $this->db->join('`staff_accounts` AS sa', 'a.`salesrep` = sa.`StaffID`', 'left');
                }
                //updated to new table >Gherx
                if ($join_table == 'postcode_regions') {
                    $this->db->join('`sub_regions` AS sr', 'sr.sub_region_id = a.postcode_region_id', 'left');
                }

                if ($join_table == 'agency_priority') {
                    $this->db->join('agency_priority as aght', 'a.agency_id = aght.agency_id', 'left');
                }
                
            }
        }

        if (isset($params['a_status']) && $params['a_status'] != '') {
            $this->db->where('a.`status`', $params['a_status']);
        }
        if(isset($params['a_status']) && $params['a_status'] == '' && $params['a_deactivated_ts'] == true){
            $this->db->where_in('a.`status`', array('target', 'deactivated'));
        }

        if (isset($params['salesrep']) && $params['salesrep'] != '') {
            $this->db->where('a.`salesrep`', $params['salesrep']);
        }

        //search
        if (isset($params['search']) && $params['search'] != '') {
            $search_filter = "CONCAT_WS(' ', LOWER(a.agency_name), LOWER(a.address_1), LOWER(a.address_2), LOWER(a.address_3), LOWER(a.state), LOWER(a.postcode))";
            $this->db->like($search_filter, $params['search']);
        }

        //agency name search
        if (isset($params['agency_name']) && $params['agency_name'] != '') {
            $agency_name_filter = "a.`agency_name` LIKE '%{$params['agency_name']}%'";
            $this->db->where($agency_name_filter);
        }

        //state filter > by gherx
        if (isset($params['state']) && $params['state'] != '') {
            $this->db->where('a.`state`', $params['state']);
        }

        // postcodes > by gherx
        if (isset($params['postcodes']) && $params['postcodes'] != '') {
            $this->db->where("a.`postcode` IN ( {$params['postcodes']} )");
        }

        // postcodes > by gherx
        if (isset($params['country_id']) && $params['country_id'] != '') {
            $this->db->where('a.`country_id`', $params['country_id']);
        }

        //deleted filter > default exclude deleted agency
        if( $params['a_deleted']!==false ){

            if ( isset($params['a_deleted']) && is_numeric($params['a_deleted']) ) {
                $this->db->where('a.`deleted`', $params['a_deleted']);
            }else{
                $this->db->where('a.`deleted`', 0);
            }
            
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

    /**
     * Get Last Contact filter by Agency ID
     */
    public function get_agency_last_contact($agency_id) {

        //Gherx: disabled > use new log table
        /*$this->db->select('agency_event_log_id,eventdate');
        $this->db->from('agency_event_log');
        $this->db->where('agency_id', $agency_id);
        $this->db->order_by('eventdate', 'DESC');
        $this->db->limit('1');*/

        $this->db->select('log_id as agency_event_log_id, created_date as eventdate');
        $this->db->from('logs');
        $this->db->where('agency_id', $agency_id);
        $this->db->where('display_in_vad', 1);
        $this->db->order_by('created_date', 'DESC');
        $this->db->limit('1');

        return $this->db->get();
    }

    /**
    * Get Next Contact filter by Agency ID
    **/
    public function get_agency_next_contact($agency_id) {

        $this->db->select('next_contact');
        $this->db->from('agency_event_log');
        $this->db->where('agency_id', $agency_id);
        $this->db->where('next_contact !=', "0000-00-00");
        $this->db->order_by('next_contact', 'DESC');
        $this->db->limit('1');

        return $this->db->get();
    }

    /**
    * Get Next Contact filter by Agency ID
    **/
    public function get_agency_next_contact_sr($agency_id) {

        $this->db->select('next_contact');
        $this->db->from('sales_report');
        $this->db->where('agency_id', $agency_id);
        $this->db->where('next_contact !=', NULL);
        $this->db->order_by('next_contact', 'DESC');
        $this->db->limit('1');

        return $this->db->get();
    }

    public function getAgencySalesRep($status) {

        $this->db->distinct('a.salesrep');
        $this->db->select('a.salesrep, sa.FirstName, sa.LastName');
        $this->db->from('agency as a');
        $this->db->join('staff_accounts as sa', 'sa.StaffID = a.salesrep', 'left');
        $this->db->where('a.status', $status);
        $this->db->where('a.country_id', $this->config->item('country'));
        $this->db->where('a.salesrep !=', 0);
        $this->db->where('sa.active',1);
        $this->db->order_by('sa.FirstName', 'ASC');

        return $this->db->get();
    }

    public function getAgencyUsing() {

        $this->db->select('agency_using_id, name');
        $this->db->from('agency_using');
        $this->db->order_by('name', 'ASC');
        $this->db->where('country_id', $this->config->item('country'));
        return $this->db->get();
    }

    public function jgetAgencyKeys($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('jobs as j');
        $this->db->join('property as p', 'p.property_id = j.property_id', 'left');
        $this->db->join('alarm_job_type as ajt', 'ajt.id = j.service', 'left');
        $this->db->join('agency as a', 'a.agency_id = p.agency_id', 'left');
        $this->db->join('job_reason as jr', 'jr.job_reason_id = j.job_reason_id', 'left');
        $this->db->join('staff_accounts as sa', 'sa.StaffID = j.assigned_tech', 'left');
        $this->db->join('agency_priority as aght', 'a.agency_id = aght.agency_id', 'left');
        $this->db->where('p.deleted', 0);
        $this->db->where("( p.is_nlm = 0 OR p.is_nlm IS NULL )");
        $this->db->where('a.status', 'active');
        $this->db->where('j.del_job', 0);
        //$this->db->where('a.phone_call_req', 1); ##removed > old VAD not using anymore
        $this->db->where('a.key_allowed', 1);
        $this->db->where('j.key_access_required', 1);
        $this->db->where('a.country_id', $this->config->item('country'));


        //FILTERS
        if ($params['agency'] != "") {
            $this->db->where('p.agency_id', $params['agency']);
        }

        if ($params['tech_id'] != "") {
            $this->db->where('sa.StaffID', $params['tech_id']);
        }

        //Date filter
        if ($params['from_date'] != "" && $params['to_date'] != "") {
            $from_date_str = date('Y-m-d', strtotime(str_replace('/', '-', $params['from_date'])));
            $to_date_str = date('Y-m-d', strtotime(str_replace('/', '-', $params['to_date'])));
        } else {
            $from_date_str = date('Y-m-d');
            $to_date_str = date('Y-m-d');
        }
        $datefitler = "j.`date` BETWEEN '{$from_date_str}' AND '{$to_date_str}'";
        $this->db->where($datefitler);

        //deleted filter > default exclude deleted agency
        if ( isset($params['a_deleted']) && is_numeric($params['a_deleted']) ) {
            $this->db->where('a.`deleted`', $params['a_deleted']);
        }else{
            $this->db->where('a.`deleted`', 0);
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

    public function getAgencyAdmin($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('agency_user_accounts as aua');
        $this->db->join('agency as a', "a.agency_id = aua.agency_id", 'left');
        $this->db->where('aua.agency_user_account_id >', 0);

        //FILTERS
        if ($params['agency'] != "") {
            $this->db->where('a.agency_id', $params['agency']);
        }

        if ($params['user_type'] != "") {
            $this->db->where('aua.user_type', $params['user_type']);
        }

        if ($params['active'] != "") {
            $this->db->where('aua.active', $params['active']);
        }

        // date filter
        if ($params['search_date']['from'] != "" && $params['search_date']['to'] != "") {
            $filter_date = "CAST( aua.`date_created` AS Date )  BETWEEN '{$params['search_date']['from']}' AND '{$params['search_date']['to']}'";
            $this->db->where($filter_date);
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

    public function getAgencyUserLogins($params) {


        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('agency_user_logins as aul');
        $this->db->join('agency_user_accounts as aua', 'aua.agency_user_account_id = aul.user', 'left');
        $this->db->join('agency as a', 'a.agency_id = aua.agency_id', 'left');
        $this->db->join('agency_priority as aght', 'a.agency_id = aght.agency_id', 'left');
        $this->db->join('agency_priority_marker_definition as apmd', 'aght.priority = apmd.priority', 'left');
        $this->db->where('aul.agency_user_login_id >', 0);


        // search agency
        if ($params['agency'] != "") {
            $this->db->where('a.agency_id', $params['agency']);
        }

        // search user
        if ($params['user'] != "") {
            $this->db->where('aul.user', $params['user']);
        }

        // date from/to filter
        if ($params['from'] != "" && $params['to'] != "") {
            $date_filter_where = "CAST(aul.`date_created` AS DATE) BETWEEN '{$params['from']}' AND '{$params['to']}'";
            $this->db->where($date_filter_where);
            //$this->db->where('aul.date_created >=', $params['from']);
            //$this->db->where('aul.date_created <=', $params['to']);
        }

        //deleted filter > default exclude deleted agency
        if ( isset($params['a_deleted']) && is_numeric($params['a_deleted']) ) {
            $this->db->where('a.`deleted`', $params['a_deleted']);
        }else{
            $this->db->where('a.`deleted`', 0);
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

    public function getMaintenanceProgramAgencies($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('agency_maintenance as am');
        $this->db->join('agency as a', 'a.agency_id = am.agency_id', 'left');
        $this->db->join('maintenance as m', 'm.maintenance_id = am.maintenance_id', 'left');
        $this->db->join('agency_priority as aght', 'a.agency_id = aght.agency_id', 'left');
        $this->db->where('a.status', 'active');
        $this->db->where('am.status', 1);
        $this->db->where('m.status', 1);

        //FILTERS

        if ($params['agency_id'] != "") {
            $this->db->where('a.agency_id', $params['agency_id']);
        }

        if ($params['mm_id'] != "") {
            $this->db->where('m.maintenance_id', $params['mm_id']);
        }

        if ($params['search'] != "") {
            $search_filter = "a.`agency_name` LIKE '%{$params['search']}%'";
            $this->db->where($search_filter);
        }

        //deleted filter > default exclude deleted agency
        if ( isset($params['a_deleted']) && is_numeric($params['a_deleted']) ) {
            $this->db->where('a.`deleted`', $params['a_deleted']);
        }else{
            $this->db->where('a.`deleted`', 0);
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

    public function get_agency_services($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`agency_services` AS agen_serv');

        // set joins
        if ($params['join_table'] > 0) {

            foreach ($params['join_table'] as $join_table) {

                if ($join_table == 'agency') {
                    $this->db->join('`agency` AS a', 'agen_serv.`agency_id` = a.`agency_id`', 'left');
                }

                if ($join_table == 'alarm_job_type') {
                    $this->db->join('`alarm_job_type` AS ajt', 'agen_serv.`service_id` = ajt.`id`', 'left');
                }
            }
        }

        // custom joins
        if (isset($params['custom_joins']) && $params['custom_joins'] != '') {
            $this->db->join($params['custom_joins']['join_table'], $params['custom_joins']['join_on'], $params['custom_joins']['join_type']);
        }

        // filter
        if (isset($params['agency_id']) && $params['agency_id'] != '') {
            $this->db->where('agen_serv.`agency_id`', $params['agency_id']);
        }

        // custom filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
        }

        // custom filter
        if (isset($params['custom_where_arr'])) {
            foreach ($params['custom_where_arr'] as $index => $custom_where) {
                if ($custom_where != '') {
                    $this->db->where($custom_where);
                }
            }
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

    public function get_users($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`agency_user_accounts` AS aua');
        //$this->db->join('`countries` AS c', 'a.`country_id` = c.`country_id`', 'left');
        // set joins
        if ($params['join_table'] > 0) {

            foreach ($params['join_table'] as $join_table) {

                if ($join_table == 'agency_user_account_types') {
                    $this->db->join('agency_user_account_types AS auat', 'aua.`user_type` = auat.`agency_user_account_type_id`', 'left');
                }

                if ($join_table == 'agency') {
                    $this->db->join('agency AS a', 'aua.`agency_id` = a.`agency_id`', 'left');
                }
            }
        }

        // custom joins
        if (isset($params['custom_joins']) && $params['custom_joins'] != '') {
            $this->db->join($params['custom_joins']['join_table'], $params['custom_joins']['join_on'], $params['custom_joins']['join_type']);
        }

        // filter
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

        // custom filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
        }

        // custom filter
        if (isset($params['custom_where_arr'])) {
            foreach ($params['custom_where_arr'] as $index => $custom_where) {
                if ($custom_where != '') {
                    $this->db->where($custom_where);
                }
            }
        }


        // group by
        if (isset($params['group_by']) && $params['group_by'] != '') {
            $this->db->group_by($params['group_by']);
        }

        // having
        if (isset($params['having'])) {
            $this->db->having($params['having']['field'], $params['having']['val']);
        }

        if (isset($params['having_custom'])) {
            $this->db->having($params['having_custom']);
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

    public function getIntegratedAPI($agency_id) {

        $sql_str = "
        SELECT *
        FROM `agency_api_integration`
        WHERE `agency_id` = {$agency_id}
        AND `active` = 1
        ";

        return $this->db->query($sql_str);
    }

    function get_agency_connected_service($api_service_id = null) {

        $api_service_arr = array(
            array(
                'id' => 4,
                'name' => 'Palace'
            ),
            array(
                'id' => 3,
                'name' => 'Property Tree'
            ),
            array(
                'id' => 1,
                'name' => 'PropertyMe'
            ),
            array(
                'id' => 2,
                'name' => 'Tapi'
            )
        );

        if ($api_service_id > 0) {

            foreach ($api_service_arr as $index => $api_service) {

                if ($api_service['id'] == $api_service_id) {
                    return $api_service;
                }
            }
        } else {
            return $api_service_arr;
        }
    }

    //GET AGENCY SERVICE DUE
    public function get_agency_service_due($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`jobs` AS j');
        $this->db->join('property as p', 'p.property_id = j.property_id', 'left');
        $this->db->join('agency as a', 'a.agency_id = p.agency_id', 'left');
        $this->db->join('staff_accounts as sa', 'sa.StaffID = a.salesrep', 'left');
        $this->db->join('agency_regions as ar', 'ar.agency_region_id = a.agency_region_id', 'left');
        $this->db->join('agency_priority as aght', 'a.agency_id = aght.agency_id', 'left');
        $this->db->where('j.status', 'Pending');
        $this->db->where('a.status', 'active');
        $this->db->where('p.deleted', 0);
        $this->db->where("( p.is_nlm = 0 OR p.is_nlm IS NULL )");
        $this->db->where('j.del_job', 0);
        $this->db->where('a.country_id', $this->config->item('country'));

        if ($params['state'] && !empty($params['state'])) {
            $state_where = "LOWER(a.state)";
            $this->db->like($state_where, $params['state']);
        }

        // sales rep
        if ($params['salesrep'] && !empty($params['salesrep'])) {
            $this->db->where('a.salesrep', $params['salesrep']);
        }

        if (isset($params['postcodes']) && $params['postcodes'] != '') {
            $this->db->where("a.`postcode` IN ( {$params['postcodes']} )");
        }

        //agency region
        if ($params['a_region'] && !empty($params['a_region'])) {
            $this->db->where('a.agency_region_id', $params['a_region']);
        }

        //search/phrase
        if (isset($params['search']) && $params['search'] != '') {
            $search_filter = " CONCAT_WS( ' ', LOWER(a.agency_name), LOWER(a.contact_first_name), LOWER(a.contact_last_name), LOWER(sa.FirstName), LOWER(sa.LastName), LOWER(a.state), LOWER(ar.agency_region_name), LOWER(a.`account_emails`), LOWER(a.`agency_emails`), LOWER(a.`contact_email`) )";
            $this->db->like($search_filter, $params['search']);
        }

        // custom filter
        if (isset($params['custom_where_arr'])) {
            foreach ($params['custom_where_arr'] as $index => $custom_where) {
                if ($custom_where != '') {
                    $this->db->where($custom_where);
                }
            }
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

    public function get_booking_notes($params) {

        if ($params['custom_select'] != '') {
            $this->db->select($params['custom_select']);
        } else if ($params['return_count'] == 1) {
            $this->db->select(" COUNT(*) AS jcount ");
        } else if ($params['distinct_sql'] != "") {
            $this->db->select(" DISTINCT {$params['distinct_sql']} ");
        } else {
            $sel_str = " 
				*
			";
        }
        $this->db->from("`booking_notes` AS bn");
        $this->db->join("`agency` AS a", "bn.`agency_id` = a.`agency_id`", "LEFT");
        $this->db->join("`booking_notes_log` AS bnl", "bnl.`booking_notes_id` = bn.booking_notes_id AND bnl.`title` = 'Add Booking Notes'", "LEFT");
        $this->db->join("`staff_accounts` AS st_ac", "bnl.`staff_id` = st_ac.`StaffID`", "LEFT");
        $this->db->where("bn.`active` = 1");
        $this->db->where("a.`deleted` = 0");

        if ($params['agency_id'] != "") {
            $this->db->where("a.`agency_id` = '{$params['agency_id']}'");
        }

        if ($params['phrase'] != '') {
            $this->db->where("(
				bn.`notes` LIKE '%{$params['phrase']}%' OR
				a.`agency_name` LIKE '%{$params['phrase']}%'
			 )");
        }


        //custom query
        if ($params['custom_filter'] != '') {
            $this->db->where($params['custom_filter']);
        }
        if (isset($params['sort_list']) && $params['sort_list'] != '') {

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

    public function update_booking_notes($bn_notes, $bn_id) {
        if ((int) $bn_id <= 0) {
            return false;
        }
        $sql = "UPDATE `booking_notes`
	SET `notes` = '{$bn_notes}'
	WHERE `booking_notes_id` = {$bn_id}";
        $query = $this->db->query($sql);
        return $this->db->affected_rows();
    }

    public function delete_booking_notes($bn_id) {
        if ((int) $bn_id <= 0) {
            return false;
        }
        $sql = "UPDATE `booking_notes`
	SET `active` = 0
	WHERE `booking_notes_id` = {$bn_id}";
        $query = $this->db->query($sql);
        return $this->db->affected_rows();
    }

    public function create_booking_notes($agency_booking_notes, $agency_id, $country_id) {
        $sql = "
	INSERT INTO
	`booking_notes` (
		`notes`,
		`agency_id`,
		`created_date`,
		`country_id`
	)
	VALUES (
		'{$agency_booking_notes}',
		{$agency_id},
		'" . date("Y-m-d H:i:s") . "',
		{$country_id}
	)";
        $query = $this->db->query($sql);
        return $this->db->insert_id();
    }

    public function add_booking_notes_log($params) {
        $sql = "
            INSERT INTO
            `booking_notes_log` (
                    `booking_notes_id`,
                    `title`,
                    `msg`,
                    `staff_id`,
                    `date_created`,
                    `active`,
                    `country_id`
            )
            VALUES (
                    {$params['bn_id']},
                    '{$params['title']}',
                    '{$params['msg']}',
                    {$params['staff_id']},
                    '" . date('Y-m-d H:i:s') . "',
                    1,
                    {$params['country_id']}
            )";
        $query = $this->db->query($sql);
        return $this->db->affected_rows();
    }

    public function add_agency($agency_name, $franchise_groups_id, $address_1, $address_2, $address_3, $phone, $state, $postcode, $postcode_region_id, $country, $lat, $lng, $tot_properties, $agency_hours, $comment, $login_id, $password, $contact_first_name, $contact_last_name, $contact_phone, $contact_email, $agency_emails, $account_emails, $send_emails, $send_combined_invoice, $send_entry_notice, $require_work_order, $allow_indiv_pm, $salesrep, $agen_stat, $agency_using_id, $legal_name, $auto_renew, $key_allowed, $key_email_req, $phone_call_req, $abn, $acc_name, $acc_phone, $allow_dk = '', $website, $allow_en, $agency_specific_notes, $new_job_email_to_agent, $display_bpay, $allow_upfront_billing, $agency_special_deal) {

        $this->db->select('*');
        $this->db->from("`postcode_regions`");
        $this->db->where("`postcode_region_postcodes` LIKE '%{$postcode}%'");
        $this->db->where("`country_id` = {$this->config->item('country')}");
        $this->db->where("`deleted` = 0");
        $pcr = $this->db->get();
        $pcr_id = $pcr->row()->postcode_region_id;
        $this->db->query("
			INSERT INTO
			`agency` (
				`agency_name`,`franchise_groups_id`,`address_1`,`address_2`,`address_3`,
				`phone`,`state`,`postcode`,`lat`,`lng`,`postcode_region_id`,
				`tot_properties`,`agency_hours`,`comment`,`status`,`contact_first_name`,
				`contact_last_name`,`contact_phone`,`contact_email`,`agency_emails`,
				`account_emails`,`send_emails`,`send_combined_invoice`,`send_entry_notice`,
				`require_work_order`,`allow_indiv_pm`,`salesrep`,`pass_timestamp`,`tot_prop_timestamp`,
				`agency_using_id`,`legal_name`,`country_id`,`auto_renew`,`key_allowed`,`key_email_req`,
				`abn`,`accounts_name`,`accounts_phone`,`allow_dk`,`website`,`allow_en`,
				`agency_specific_notes`,`new_job_email_to_agent`,`display_bpay`,`allow_upfront_billing`,
				`invoice_pm_only`,`electrician_only`,`agency_special_deal`
			)
			VALUES (
				'" . $agency_name . "','" . $franchise_groups_id . "','" . $address_1 . "',
				'" . $address_2 . "','" . $address_3 . "','" . $phone . "','" . $state . "','" . $postcode . "',
				'{$lat}','{$lng}',
				'" . $pcr_id . "','" . $tot_properties . "','" . $agency_hours . "','" . $comment . "',
				'" . $agen_stat . "','" . $contact_first_name . "','" . $contact_last_name . "',
				'" . $contact_phone . "','" . $contact_email . "','" . $agency_emails . "',
				'" . $account_emails . "',1,1,1,0,
				'" . $allow_indiv_pm . "','" . $salesrep . "','" . date('Y-m-d H:i:s') . "','" . date('Y-m-d H:i:s') . "',
				'" . $agency_using_id . "','" . $legal_name . "',{$this->config->item('country')},1,1,0,
				'" . $abn . "','" . $acc_name . "','" . $acc_phone . "',1,'" . $website . "','" . $allow_en . "',
				'" . $agency_specific_notes . "','" . $new_job_email_to_agent . "',0,'" . $allow_upfront_billing . "',
				0,0,'" . $agency_special_deal . "'
			)
		");
        $agency_id = $this->db->insert_id();
        // add agency logs
        $this->db->query("
			INSERT INTO 
			`agency_event_log`(
				`contact_type`,`eventdate`,`comments`,`agency_id`,`staff_id`
			) 
			VALUES(
			   'New Agency','" . date('Y-m-d') . "','Agency added as {$agen_stat} agency','{$agency_id}','{$this->session->staff_id}'
			 )
		 ");
        return $agency_id;
    }

    /**
     * Get getAgencyAudits
     */
    public function getAgencyAudits($params){
        
        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }
        $this->db->select($sel_query);
        $this->db->from('`agency_audits` AS ad');
        $this->db->join('agency as a','a.agency_id = ad.agency_id','left');
        $this->db->join('staff_accounts as sb','sb.StaffID = ad.submitted_by','left');
        $this->db->join('staff_accounts as at','at.StaffID = ad.assigned_to','left');

        //Filters
        if($params['active']!=""){
            $this->db->where('ad.active', $params['active']);
        }
        
        if($params['status']!=""){
            $this->db->where('ad.status', $params['status']);
        }
        
        if($params['agency_audits_id']!=""){
            $this->db->where('ad.agency_audits_id', $params['agency_audits_id']);
        }
        
        if($params['date']!=""){
            $date_range = "CAST( ad.`date_created` AS Date ) = '{$params['date']}'";
            $this->db->where($date_range);
        }
    
        if(is_numeric(($params['submitted_by']))){
            $this->db->where('ad.submitted_by', $params['submitted_by']);
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

    public function getStatusName($status){
	
        switch($status){
            case 1:
                $status_name = 'Pending';
            break;
            case 2:
                //$status_name = 'Declined';
            break;
            case 3:
                $status_name = 'In Progress';
            break;
            case 4:
                $status_name = 'Completed';
            break;
        }
        
        return $status_name;
        
    }

    /**
     * Get all alarms
     * return query
     */
    public function get_alarms(){
        $this->db->select('*');
        $this->db->from('alarm_pwr');
        $this->db->where('active',1);
        $q = $this->db->get();
        return $q;
    }
    
    /**
     * Get all services
     * return query
     */
    public function get_services(){
        $this->db->select('*');
        $this->db->from('alarm_job_type');
        $this->db->where('active',1);
        $q = $this->db->get();
        return $q;
    }

    /**
     * Get maintenance
     * return query
     */
    public function agency_get_maintenance(){
        $this->db->select('*');
        $this->db->from('maintenance');
        return $this->db->get();
    }
    
    public function agency_get_sales_rep(){
        $ClassID_group = "(sa.ClassID = 2 OR sa.ClassID = 5 OR sa.ClassID = 9)";
        $this->db->distinct('ca.staff_accounts_id');
        $this->db->select('ca.staff_accounts_id, sa.FirstName, sa.LastName');
        $this->db->from('staff_accounts as sa');
        $this->db->join('country_access as ca','ca.staff_accounts_id=sa.StaffID','inner');
        $this->db->where('sa.deleted',0);
        $this->db->where('sa.active',1);
        $this->db->where('ca.country_id',$this->config->item('country'));
        $this->db->where($ClassID_group);
        $this->db->order_by("sa.FirstName", "asc");
        $q = $this->db->get();
        return $q;
    }

    function getAgencyUsingByCountry(){
        return $this->db->query("
            SELECT *
            FROM `agency_using`
            WHERE `country_id` ={$this->config->item('country')}
            ORDER BY `name` ASC
        ");
    }

    public function get_franchise_groups(){
        $this->db->select('*');
        $this->db->from('franchise_groups');
        $this->db->where('country_id', $this->config->item('country'));
        $this->db->order_by("name", "asc");
        return $this->db->get();
    }

    public function getRegionViaPostCode($postcode){
        /*$sql = $this->db->query("
            SELECT * 
            FROM  `postcode_regions`
            WHERE `postcode_region_postcodes` LIKE '%{$postcode}%'
            AND `country_id` = {$this->config->item('country')}
            AND `deleted` = 0
        ");
        return $sql;*/
        #updated use new table
        $this->db->select('*, sr.sub_region_id as postcode_region_id');
        $this->db->from('postcode as pc');
        $this->db->join('sub_regions as sr', 'sr.sub_region_id=pc.sub_region_id','left');
        $this->db->where('pc.postcode', $postcode);
        return $this->db->get();

    }

    public function add_agency_data($data){

        $this->db->insert('agency', $data);
        $this->db->limit(1);

        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }

    }

    public function add_agency_maintenance($data){

        $this->db->insert('agency_maintenance', $data);
        $this->db->limit(1);

        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }

    }

    public function add_agency_alarms($data){
        $this->db->insert('agency_alarms', $data);
        $this->db->limit(1);
        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    public function add_agency_services($data){
        $this->db->insert('agency_services', $data);
        $this->db->limit(1);
        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * get Agency Service Price
     * return price
     */
    function getAgencyServicePrice($agency, $ajt) {

        $sql = $this->db->query("
            SELECT *
            FROM `agency_services` 
            WHERE `agency_id` = {$agency}
            AND `service_id` = {$ajt}
        ");
        $row = $sql->row_array();
        return $row['price'];

    }

    // get active services
    function getAlarmPower() {

        return $this->db->query("
			SELECT *
			FROM `alarm_pwr` 
		");
    }

     // get Agency Service Price
     function getAgencyAlarmsPrice($agency, $alarm_pwr_id) {

        $sql = $this->db->query("
			SELECT *
			FROM `agency_alarms` 
			WHERE `agency_id` = {$agency}
			AND `alarm_pwr_id` = {$alarm_pwr_id}
		");
        $row = $sql->row_array();
        return $row['price'];
    }

      // get active services
      function getActiveServices() {

        return $this->db->query("
			SELECT *
			FROM `alarm_job_type`
			WHERE `active` =1
		");
    }

    public function get_regions_by_postcodes($regionCodes){

        $this->db->select('*');
        $this->db->from('postcode as p');
        $this->db->join('sub_regions as sr', "sr.sub_region_id=p.sub_region_id", "right");
        $this->db->join('regions as r', "r.regions_id=sr.region_id", "right");
        $this->db->where('p.deleted',0);
        $this->db->where_in('p.postcode', $regionCodes);
        $query = $this->db->get();
        return $query;

    }

    public function getSatsToServicePropertyServices($agency_id){

        /*$sql = $this->db->query("
            SELECT count(ps.`property_services_id`) AS jcount
            FROM `property_services` AS ps 
            LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id` 
            WHERE ps.`service` = 1 
            AND p.`agency_id` = {$agency_id} 
            AND p.deleted = 0
            AND (p.is_nlm = 0 || p.is_nlm IS NULL)
        ");
        $row = $sql->row_array();
        return $row['jcount']; */

        ## new updated query
        $sql = $this->db->query("
        SELECT COUNT(DISTINCT(p.`property_id`)) AS jcount
        FROM `property_services` AS ps 
        LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id` 
        WHERE ps.`service` = 1 
        AND p.`agency_id` = {$agency_id} 
        AND p.deleted = 0
        AND (p.is_nlm = 0 || p.is_nlm IS NULL)
    ");
    $row = $sql->row_array();
    return $row['jcount'];
    
    }

    public function getNewPropertyManagers($params){
        
        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('agency_user_accounts as aua');
        $this->db->join('agency_user_2fa as au_2fa','aua.agency_user_account_id = au_2fa.user_id','left');
        $this->db->join('agency_user_account_types as auat','auat.agency_user_account_type_id = aua.user_type','left');
        $this->db->join('agency as a','a.agency_id = aua.agency_id','left');

         // filter
         if (isset($params['agency_id']) && $params['agency_id'] != '') {
            $this->db->where('aua.`agency_id`', $params['agency_id']);
        }

        if ($params['aua_id'] != "") {
            $this->db->where('aua.`agency_user_account_id`', $params['aua_id']);
        }

        if ($params['active'] != "") {
            $this->db->where('aua.active', $params['active']);
        }

        if ($params['user_type'] != "") {
            $this->db->where('aua.`user_type`', $params['user_type']);
        }

        if ($params['email'] != "") {
            $this->db->where('aua.`email`', $params['email']);
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

    public function agency_user_account_types(){
        $this->db->select('*');
        $this->db->from('agency_user_account_types');
        $this->db->where('active',1);
        $q = $this->db->get();
        return $q;
    }

    public function get_approved_agency_services($agency_id,$service_id){
		
        $this->db->select('*');
        $this->db->from('agency_services');
        $this->db->where('agency_id',$agency_id);
        $this->db->where_in('service_id', $service_id);
        $q = $this->db->get();
        return $q;
    }
    
    public function get_approved_agency_alarms($agency_id,$alarm_pwr_id){
	    
        $this->db->select('*');
        $this->db->from('agency_alarms');
        $this->db->where('agency_id',$agency_id);
        $this->db->where_in('alarm_pwr_id', $alarm_pwr_id);
        $q = $this->db->get();
        return $q;
        
    }
    
    public function get_agency_onboarding(){
        $this->db->select('onboarding_id,name');
        $this->db->from('agency_onboarding');
        $this->db->where('active',1);
        $q = $this->db->get();
        return $q;
    }

    public function get_agency_onboarding_selected($agency_id, $onboarding_id){

        $onboarding_id_imp = implode(',',$onboarding_id);
        return $this->db->query("
            SELECT aob_sel.`onboarding_selected_id`, aob_sel.`updated_date`, sa.`FirstName`, sa.`LastName`, aob_sel.onboarding_id
            FROM `agency_onboarding_selected` AS aob_sel
            LEFT JOIN `staff_accounts` AS sa ON aob_sel.`updated_by` = sa.`StaffID`
            WHERE aob_sel.`agency_id` = {$agency_id}
            AND aob_sel.`onboarding_id` IN ({$onboarding_id_imp})
        ");
                            
    }

    public function get_agency_event_log($params){

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('agency_event_log as c');
        $this->db->join('staff_accounts sa','sa.StaffID = c.staff_id','left');

        if ($params['agency_id'] != "") {
            $this->db->where('c.agency_id', $params['agency_id']);
        }

        // custom filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
        }

        $this->db->order_by('c.agency_event_log_id','desc');
        
        // limit
        if (isset($params['limit']) && $params['limit'] > 0) {
            $this->db->limit($params['limit'], $params['offset']);
        }

        $q = $this->db->get();
        return $q;

    }

    /**
     * Get New Logs
     */
    public function getNewLogs($params){

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('logs as l');
        $this->db->join('log_titles as ltit','ltit.log_title_id = l.title','left');
        $this->db->join('agency_user_accounts as aua','aua.agency_user_account_id = l.created_by','left');
        $this->db->join('staff_accounts as sa','sa.StaffID = l.created_by_staff','left');

        if ($params['job_id'] != "") {
            $this->db->where('l.job_id', $params['job_id']);
        }

        if ($params['property_id'] != "") {
            $this->db->where('l.property_id', $params['property_id']);
        }

        if ($params['agency_id'] != "") {
            $this->db->where('l.agency_id', $params['agency_id']);
        }

        if ($params['display_in_vjd'] != "") {
            $this->db->where('l.display_in_vjd', $params['display_in_vjd']);
        }

        if ($params['display_in_vpd'] != "") {
            $this->db->where('l.display_in_vpd', $params['display_in_vpd']);
        }

        if ($params['display_in_vad'] != "") {
            $this->db->where('l.display_in_vad', $params['display_in_vad']);
        }

        if ($params['display_in_portal'] != "") {
            $this->db->where('l.display_in_portal', $params['display_in_portal']);
        }

        if ($params['display_in_accounts'] != "") {
            $this->db->where('l.display_in_accounts', $params['display_in_accounts']);
        }

        if ($params['display_in_accounts_hid'] != "") {
            $this->db->where('l.display_in_accounts_hid', $params['display_in_accounts_hid']);
        }

        if ($params['display_in_sales'] != "") {
            $this->db->where('l.display_in_sales', $params['display_in_sales']);
        }

        if (is_numeric($params['deleted'])) {
            $this->db->where('l.deleted', $params['deleted']);
        }

        if ($params['log_type'] != "") {
            $this->db->where_in('l.log_type', $params['log_type']);
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

    /**
     * parse the tags on logs link
     */
    public function parseDynamicLink_to_crm($params) {

        $log_details = $params['log_details'];

        // property logs
        $tag = '{p_address}';
        // find the tag
        if (strpos($log_details, $tag) !== false) {

            // get logs data
            $log_sql_str = "
			SELECT `l`.`property_id`
			FROM `logs` AS `l`
			WHERE `l`.`log_id` = {$params['log_id']}
			";
            $log_sql = $this->db->query($log_sql_str);
            $l_row = $log_sql->row_array();
            $property_id = $l_row['property_id'];

            if (isset($property_id) && $property_id > 0) {

                // get property data
                $p_sql_str = "
				SELECT
					`p`.`property_id`,
					`p`.`address_1`,
					`p`.`address_2`,
					`p`.`address_3`,
					`p`.`state`,
					`p`.`postcode`
				FROM `property` AS `p`
				WHERE `p`.`property_id` = {$property_id}
				";
                $p_sql = $this->db->query($p_sql_str);
                $p_row = $p_sql->row_array();
                $vpd_link = "<a href='/view_property_details.php?id={$property_id}'>{$p_row['address_1']} {$p_row['address_2']} {$p_row['address_3']}</a>";

                // replace tags
                $log_details = str_replace($tag, $vpd_link, $log_details);
            }
        }


        // agency user
        $tag = 'agency_user';
        // find the tag
        if (strpos($log_details, $tag) !== false) {

            // break down the tag to get the agency user ID
            $tag_string = $this->jcclass->get_part_of_string($log_details, '{', '}');
            $str_exp = explode(':', $tag_string);
            $aua_id = $str_exp[1];


            // get agency user data
            $sel_query = "
				aua.`agency_user_account_id`,
				aua.`fname`,
				aua.`lname`
			";

            $user_sql_str = "
			SELECT `aua`.`agency_user_account_id`, `aua`.`fname`, `aua`.`lname`
			FROM `agency_user_accounts` AS `aua`
			LEFT JOIN `agency_user_account_types` AS `auat` ON aua.`user_type` = auat.`agency_user_account_type_id`
			LEFT JOIN `agency` AS `a` ON aua.`agency_id` = a.`agency_id`
			WHERE `aua`.`agency_user_account_id` = {$aua_id}
			";
            $user_sql = $this->db->query($user_sql_str);
            $user_row = $user_sql->row_array();
            $user_full_name = "{$user_row['fname']} {$user_row['lname']}";

            // replace tags
            $log_details = str_replace('{' . $tag_string . '}', $user_full_name, $log_details);
        }


        // created by
        $tag = '{created_by}';
        // find the tag
        if (strpos($log_details, $tag) !== false) {

            // get logs data
            $log_sql_str = "
			SELECT `l`.`created_by`
			FROM `logs` AS `l`
			WHERE `l`.`log_id` = {$params['log_id']}
			";
            $log_sql = $this->db->query($log_sql_str);

            if ($log_sql->num_rows() > 0) {

                $l_row = $log_sql->row_array();
                $created_by = $l_row['created_by'];

                // get agency user data
                $sel_query = "
					aua.`agency_user_account_id`,
					aua.`fname`,
					aua.`lname`
				";

                $user_sql_str = "
				SELECT `aua`.`agency_user_account_id`, `aua`.`fname`, `aua`.`lname`
				FROM `agency_user_accounts` AS `aua`
				LEFT JOIN `agency_user_account_types` AS `auat` ON aua.`user_type` = auat.`agency_user_account_type_id`
				LEFT JOIN `agency` AS `a` ON aua.`agency_id` = a.`agency_id`
				WHERE `aua`.`agency_user_account_id` = {$created_by}
				";
                $user_sql = $this->db->query($user_sql_str);
                $user_row = $user_sql->row_array();
                $user_full_name = "{$user_row['fname']} {$user_row['lname']}";

                // replace tags
                $log_details = str_replace($tag, $user_full_name, $log_details);
            }
        }


        return $log_details;
    }

    # Get Property Files - will eventually move these into a class / similar
    public function getPropertyFiles2($agency_id)
    {
        // path
        $upload_path = $_SERVER['DOCUMENT_ROOT'].'/uploads/agency_files/';

        # if subdir doesn't exist then return null
        if(!is_dir($upload_path . $agency_id))
        {
            //echo $upload_path;
            return null;
        }
        else 
        {
            if ($handle = opendir($upload_path . $agency_id)) 
            {
                $files = array();
                
                while (false !== ($entry = readdir($handle))) 
                {
                    if($entry != "." && $entry != "..")
                    {	
                        $files[] = $entry;
                    }
                }
            
                closedir($handle);
            
                return $files;
            }
            else
            {
                return null;
            }
        }
    }

    public function getContractorAppointment($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('contractor_appointment as ca');
        $this->db->where('ca.contractor_appointment_id >',0);
        $this->db->where('ca.country_id', COUNTRY);

        if ($params['agency_id'] != "") {
            $this->db->where('ca.agency_id', $params['agency_id']);
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

    public function get_property_list($params){

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('property as p');
        $this->db->where('p.agency_id', $params['agency_id']);

        if(!empty($params['search'])){
            $search_filter = "CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode))";
            $this->db->like($search_filter, $params['search']);
        }

        if($params['status']!=""){
            $this->db->where('p.deleted', $params['status']);
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

    /**
     * Get Last Attended by property id
     * return date
     */
    public function last_attended($property_id){
        $last_attended_query = $this->db->query("
            SELECT `date`
            FROM `jobs`
            WHERE `property_id` = {$property_id}
            AND ( `status` = 'Merged Certificates' OR `status` = 'Completed' )
            AND `assigned_tech` != 1
            AND `assigned_tech` != 2
            AND `assigned_tech` != 'NULL'
            AND `del_job` = 0
            ORDER BY `date` DESC
            LIMIT 0 , 1
        ");	

        $row = $last_attended_query->row_array();
        return $row['date'];
    }
    
    /**
     * Get last Last YM by Property_id and Service_id
     * return date
     */
    public function get_last_ym_by_prop_and_service($prop_id, $service_ic){
        $lym_sql = $this->db->query("
            SELECT `date`
            FROM `jobs`
            WHERE `property_id` ={$prop_id}
            AND `status` = 'Completed'
            AND `job_type` = 'Yearly Maintenance'
            AND `service` = {$service_ic}
            ORDER BY `date` DESC
            LIMIT 0 , 1
        ");	
        $lym = $lym_sql->row_array();
        $lym_date = ($lym['date']!=""&&$lym['date']!="0000-00-00")?date("Y-m-d",strtotime($lym['date'])):'----';
        return $lym_date;
    }   

    public function agency_api_get_contact($agency_id, $contact_id){

        $this->load->model('pme_model');
        $end_points = "https://app.propertyme.com/api/v1/contacts/{$contact_id}";

        $api_id = 1; // PMe

        // get access token
        $pme_params = array(
            'agency_id' => $agency_id,
            'api_id' => $api_id
        );
        $access_token = $this->pme_model->getAccessToken($pme_params);

        $pme_params = array(
            'access_token' => $access_token,
            'end_points' => $end_points
        );
        
        $response =  $this->pme_model->call_end_points_v2($pme_params);
        return json_decode($response);
        
    }

    public function update_agency($agency_id,$data){

        if(!empty($agency_id) && is_numeric($agency_id)){ # Validate agency id
            $this->db->where('agency_id', $agency_id);
            $this->db->update('agency', $data);
            $this->db->limit(1);
        }        

    }

    public function get_agency_specific_brochures($params){

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('agency_specific_brochures');
        
        if ($params['agency_specific_brochures_id'] != "") {
            $this->db->where('agency_specific_brochures_id', $params['agency_specific_brochures_id']);
        }

        if ($params['agency_id'] != "") {
            $this->db->where('agency_id', $params['agency_id']);
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

    /** for log purposes > return name */
    public function get_maintenance_provider_old_new_value($id){
        $this->db->select('*');
        $this->db->from('maintenance');
        $this->db->where('maintenance_id', $id);
        $q = $this->db->get();
        $row = $q->row_array();
        return $row['name'];
    }

    /** for log purposes return name */
    public function get_trusAccountSoftware_new_old_val($id){
        $this->db->select('*');
        $this->db->from('trust_account_software');
        $this->db->where('trust_account_software_id', $id);
        $q = $this->db->get();
        $row = $q->row_array();
        return $row['tsa_name'];
    }

    /** get agency_using > return name */
    function getAgencyUsingByCountry_new_od_val($id){
        $this->db->select('*');
        $this->db->from('agency_using');
        $this->db->where('agency_using_id', $id);
        $q = $this->db->get();
        $row = $q->row_array();
        return $row['name'];
    }

    public function getApiSoftweareName($id){
        $this->db->select('api_name');
        $this->db->from('agency_api');
        $this->db->where('agency_api_id', $id);
        $q = $this->db->get();
        $row = $q->row_array();
        return $row['api_name'];
    }

    public function getMainLogType($params){

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('main_log_type');

        //optional filter
        if($params['main_log_type_id']!="" && $params['main_log_type_id']>0){
            $this->db->where('main_log_type_id', $params['main_log_type_id']);
        }

        if($params['contact_type'] && $params['contact_type']!=""){
            $this->db->where('contact_type', $params['contact_type']);
        }

        if($params['is_show'] && $params['is_show']!=""){
            $this->db->where('is_show', $params['is_show']);
        }

        if($params['active'] && $params['active']!=""){
            $this->db->where('active', $params['active']);
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

    public function add_sales_report($data){
        $this->db->insert('sales_report',$data);
        $this->db->limit(1);

        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**Get Contact Type BY ID*/
    public function get_contact_type($id){
        $this->db->select('*');
        $this->db->from('main_log_type');
        $this->db->where('main_log_type_id', $id);
        return $this->db->get()->row_array();
    }

    /**
     * Insert to sales_snapshot table
     */
    public function insert_sales_snapshot($data){
        $this->db->insert('sales_snapshot', $data);
        $this->db->limit(1);

        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    public function vad_address_type_name($type){

        switch($type){
            case 1:
                $type_name = 'Mailing Address';
            break;
            case 2:
                $type_name = 'Key Address';
            break;
        }
        
        return $type_name;

    }

    //GET Staff Access By StaffID from DB - CHOPS
    public function getStaffAccess($staff_id) {
        return $this->db->select('ClassName')
        ->from('`staff_accounts` AS sa')
        ->join('staff_classes as sc', 'sa.ClassID = sc.ClassID', 'left')
        ->where('sa.StaffID', $staff_id)
        ->where('sa.deleted', 0)
        ->where('sa.active', 1)
        ->get()->result_object();
        $this->db->get('staff_accounts');
    }//endfct

    //GET Trusted Accounts from DB - CHOPS
    public function getTrustedAccounts($country_id,$agency,$tas_filter) {
        $this->db->select('`a`.`agency_id` , `a`.`agency_name` , `tas`.`trust_account_software_id` , `tas`.`tsa_name`, `a`.`pme_supplier_id`, `a`.`palace_diary_id`');
        $this->db->from('`agency` AS `a`');
        $this->db->join('`trust_account_software` AS `tas`', '`a`.`trust_account_software` = `tas`.`trust_account_software_id`', 'left');
        $this->db->where('`a`.`status`', 'active');
        $this->db->where('`a`.`country_id`', $country_id);
        $this->db->where('`a`.`trust_account_software` >', 0);
        $this->db->order_by('`a`.`agency_name`','desc');

        if($agency != "null" && $tas_filter != "null"){
            $this->db->like('`a`.`agency_name`', $agency);
            $this->db->where('`a`.`trust_account_software`', $tas_filter);
        }
        // custom filter
        if ($agency != "null") {
            $this->db->like('`a`.`agency_name`', $agency);
        }

        if ($tas_filter != "null") {
            $this->db->where('`a`.`trust_account_software`', $tas_filter);
        }

        $query = $this->db->get()->result_object(); 
        return $query;
    }//endfct

    //GET Available API from DB - CHOPS
    public function getAvailableApi($agency_id) {
        return $this->db->select('`agen_api_int`.`api_integration_id` , `agen_api_int`.`connected_service` , `agen_api`.`api_name`')
        ->from('`agency_api_integration` AS `agen_api_int`')
        ->join('`agency_api` AS `agen_api`', 'agen_api_int.`connected_service` = agen_api.`agency_api_id`', 'left')
        ->where('`agen_api_int`.`active`', 1)
        ->where('`agen_api_int`.`agency_id`', $agency_id)
        ->get()->result_object();
        $this->db->get('agency_api_integration');
    }//endfct

    //GET Connected API from DB - CHOPS
    public function getConnectedApi($agency_id) {
        return $this->db->select('`agen_api_tok`.`agency_api_token_id` , `agen_api_tok`.`agency_id` , `agen_api_tok`.`api_id` , `agen_api`.`api_name`')
        ->from('`agency_api_tokens` AS `agen_api_tok`')
        ->join('`agency_api` AS `agen_api`', 'agen_api_tok.`api_id` = agen_api.`agency_api_id`', 'left')
        ->where('`agen_api_tok`.`active`', 1)
        ->where('`agen_api_tok`.`agency_id`', $agency_id)
        ->get()->result_object();
        $this->db->get('agency_api_tokens');
    }//endfct

    //GET Agencies Connected API from DB - CHOPS
    public function get_agencies_api_connected() {
        return $this->db->select('aap.`agency_id`, a.`agency_name`')
        ->from('`agency_api_tokens` AS `aap`')
        ->join('`agency` AS `a`', 'aap.`agency_id` = a.`agency_id`', 'left')
        ->where('aap.`api_id`', 1)
        ->where('aap.`access_token` !=', NULL)
        ->order_by('a.`agency_name`', 'ASC')
        ->get()->result_object();
        $this->db->get('agency_api_tokens');
    }

    //GET Free Alarms
    public function get_free_alarms_display($state, $free) {

        $this->db->select('`ap`.`alarm_pwr`');
        $this->db->from('`free_alarms_display` AS `fal`');
        $this->db->join('`alarm_pwr` AS `ap`', '`fal`.`alarm_pwr_id` = `ap`.`alarm_pwr_id`', 'left');

        //FREE without state
        if($state == "" && $free == 1){
            $this->db->where('`fal`.`free`', 1);
        }

        //PAID without state
        if($state == "" && $free == 0){
            $this->db->where('`fal`.`free`', 0);
        }

        //FREE  with state
        if ($state != "" && $free == 1) {
            $this->db->where('`fal`.`state`', $state);
            $this->db->where('`fal`.`free`', 1);
        }

        //PAID with state
        if ($state != "" && $free == 0) {
            $this->db->where('`fal`.`state`', $state);
            $this->db->where('`fal`.`free`', 0);
        }

        $query = $this->db->get()->result_object(); 
        return $query;
    }

    //Check Free Alarms
    public function check_free_alarms($alarm_pwr_id) {
        return $this->db->select('fa_id')
        ->from('free_alarms')
        ->where('alarm_pwr_id', $alarm_pwr_id)
        ->where('free', 1)
        ->where('state =', "")
        ->get()->result_object();
        $this->db->get('free_alarms');
    }

    //get agency priority fullname
    public function get_agency_priority($agency_id){
        $this->db->select("id, priority, priority_full_name")
        ->from("agency_priority_marker_definition");
        $result = $this->db->get()->result();

        return $result;
    }

    //save_agency_high_tax
    public function save_agency_high_touch($agency_id, $priority, $reason, $staff_id)
    {
        $added_date = date('Y-m-d H:i:s');
        $last_modified = date('Y-m-d H:i:s');

        if (count($this->check_if_current_agency_ht_exists($agency_id)) == 0) {
            $data = array(
                'agency_id'         => $agency_id,
                'priority'        => $priority,
                'added_by'          => $staff_id,
                'added_date'        => $added_date,
                'reason'            => $reason
            );

            $this->db->insert('agency_priority', $data);
        } else {
            $data = array(
                'agency_id'         => $agency_id,
                'priority'        => $priority,
                'added_by'          => $staff_id,
                'reason'            => $reason,
                'modified_date'     => $last_modified
            );

            $this->db->where('agency_id', $agency_id );
            $this->db->update('agency_priority', $data);
        }
        
        return true;
    }

    public function get_current_agency_ht($agency_id)
    {
        $this->db->select("agt.priority")
        ->from("agency_priority agt")
        ->join("agency a", "a.agency_id = agt.agency_id", "inner")
        ->where('agt.agency_id', $agency_id);

        $result = $this->db->get()->result();

        return $result[0]->priority;
    }

    public function get_current_agency_ht_reason($agency_id)
    {
        $this->db->select("agt.reason")
        ->from("agency_priority agt")
        ->join("agency a", "a.agency_id = agt.agency_id", "left")
        ->where('agt.agency_id', $agency_id);

        $result = $this->db->get()->result();

        return $result[0]->reason;
    }

    public function get_current_agency_ht_added_timestamp($agency_id)
    {
        $this->db->select("aght.added_date,aght.modified_date")
        ->from("agency_priority aght")
        ->join("agency a", "a.agency_id = aght.agency_id", "left")
        ->where('aght.agency_id', $agency_id);

        $result = $this->db->get()->row();

        return $result;
    }

    public function check_if_current_agency_ht_exists($agency_id)
    {
        $this->db->select("agt.agency_id")
        ->from("agency_priority agt")
        ->join("agency a", "a.agency_id = agt.agency_id", "left")
        ->where('agt.agency_id', $agency_id);

        $result = $this->db->get()->result();

        return $result;
    }

    public function get_agency_details($id)
    {
        $params = array(
            'sel_query' => 'agency_name',
            'agency_id' => $id,
            'display_query' => 0
        );

        $query = $this->get_agency($params);
        $row = $query->row();

        return $row->agency_name;
    }

    public function get_price_increase_excluded_agency($params)
    {
        $this->db->select("a.agency_name as a_name, a.address_1, a.address_2, a.address_3, a.agency_id as a_id, 
                           a.salesrep, a.state, a.active_prop_with_sats, sr.sub_region_id as postcode_region_id, sr.subregion_name as postcode_region_name, 
                           au.name as `agency_using, sa.FirstName, sa.LastName, aght.priority, exa.exclude_until");
        $this->db->from("price_increase_excluded_agency as exa");

        if ($params['join_table'] > 0) {

            foreach ($params['join_table'] as $join_table) {
                if ($join_table == 'agency') {
                    $this->db->join('`agency AS a', 'a.agency_id = exa.agency_id', 'left');
                }

                if ($join_table == 'salesrep') {
                    $this->db->join('`staff_accounts` AS sa', 'a.`salesrep` = sa.`StaffID`', 'left');
                }

                if ($join_table == 'postcode_regions') {
                    $this->db->join('`sub_regions` AS sr', 'sr.sub_region_id = a.postcode_region_id', 'left');
                }

                if ($join_table == 'agency_using') {
                    $this->db->join('`agency_using` AS au', 'au.agency_using_id = a.agency_using_id', 'left');
                }

                if ($join_table == 'agency_priority') {
                    $this->db->join('`agency_priority` AS aght', 'a.agency_id = aght.agency_id', 'left');
                }
                
            }
        }

        // custom joins
        if (isset($params['custom_joins']) && $params['custom_joins'] != '') {
            $this->db->join($params['custom_joins']['join_table'], $params['custom_joins']['join_on'], $params['custom_joins']['join_type']);
        }

        if ($params['date_from_deac'] != '' && $params['date_to_deac'] != '') {
            $deactivated_date = "a.deactivated_ts BETWEEN '".$params['date_from_deac']."' AND '".$params['date_to_deac']."'";
            $this->db->where($deactivated_date);
        }

        if (isset($params['salesrep']) && $params['salesrep'] != '') {
            $this->db->where('a.`salesrep`', $params['salesrep']);
        }

        //search
        if (isset($params['search']) && $params['search'] != '') {
            $search_filter = "CONCAT_WS(' ', LOWER(a.agency_name), LOWER(a.address_1), LOWER(a.address_2), LOWER(a.address_3), LOWER(a.state), LOWER(a.postcode))";
            $this->db->like($search_filter, $params['search']);
        }

        if (isset($params['agency_name']) && $params['agency_name'] != '') {
            $agency_name_filter = "a.`agency_name` LIKE '%{$params['agency_name']}%'";
            $this->db->where($agency_name_filter);
        }

        if (isset($params['agency_using_id']) && $params['agency_using_id'] != '') {
            $this->db->where('a.`agency_using_id`', $params['agency_using_id']);
        }

        if (isset($params['state']) && $params['state'] != '') {
            $this->db->where('a.`state`', $params['state']);
        }

        if (isset($params['postcodes']) && $params['postcodes'] != '') {
            $this->db->where("a.`postcode` IN ( {$params['postcodes']} )");
        }

        if (isset($params['country_id']) && $params['country_id'] != '') {
            $this->db->where('a.`country_id`', $params['country_id']);
        }

        // custom filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
        }

        // custom filter
        if (isset($params['custom_where_arr'])) {
            foreach ($params['custom_where_arr'] as $index => $custom_where) {
                if ($custom_where != '') {
                    $this->db->where($custom_where);
                }
            }
        }

        //deleted filter > default exclude deleted agency
        if( $params['a_deleted']!==false ){

            if ( isset($params['a_deleted']) && is_numeric($params['a_deleted']) ) {
                $this->db->where('a.`deleted`', $params['a_deleted']);
            }else{
                $this->db->where('a.`deleted`', 0);
            }
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

    public function get_property_variation_in_agency_pricing_tab($agency_id)
    {
        $query = $this->db->query("
            SELECT 
                p.property_id, p.`address_1`, p.`address_2`, p.`address_3`, p.`state`,
                p.`postcode`, pv.property_id, pv.agency_price_variation, pv.date_applied, pv.deleted_ts, pv.active, 
                apv.active, a.status, apv.scope, apv.`expiry` , p.holiday_rental, a.country_id, p.`is_nlm` , 
                p.`deleted` , pie.agency_id, pie.exclude_until
            FROM property_variation AS pv
            LEFT JOIN property AS p ON p.property_id = pv.property_id
            LEFT JOIN agency AS a ON a.agency_id = p.agency_id
            LEFT JOIN price_increase_excluded_agency AS pie ON a.agency_id = pie.agency_id
            LEFT JOIN agency_price_variation AS apv ON pv.agency_price_variation = apv.id
            WHERE a.agency_id = {$agency_id}
            AND (
                pie.exclude_until < '2023-02-28'
                OR pie.exclude_until IS NULL
            )
            AND p.`deleted` = 0
            AND (
                p.`is_nlm` =0
                OR p.`is_nlm` IS NULL
            )
            AND a.`status` = 'active'
            AND a.`country_id` =1
            AND pv.`active` =1
            AND apv.`active` =1
            AND apv.`scope` =1
            AND p.holiday_rental !=1
            AND (
            apv.`expiry` >= '2023-02-28'
            OR apv.`expiry` IS NULL
            )
            GROUP BY p.property_id
        ");

        return $query;
    }
}
