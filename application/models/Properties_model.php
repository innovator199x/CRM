<?php

class Properties_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    /**
     * Get Property
     */
    public function get_properties($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        if(isset($params['search_type']) && $params['search_type'] != ''){
            $this->db->distinct()->select($sel_query);
        }
        else{
            $this->db->select($sel_query);
        }
        
        $this->db->from('`property` AS p');
        $this->db->join('`agency` AS a', 'p.`agency_id` = a.`agency_id`', 'left');
        $this->db->join('`agency_user_accounts` AS aua', 'p.`pm_id_new` = aua.`agency_user_account_id`', 'left');

        // set joins
        if (!empty($params['join_table'])) {

            foreach ($params['join_table'] as $join_table) {

                if ($join_table == 'property_services') {
                    $this->db->join('`property_services` AS ps', 'p.`property_id` = ps.`property_id`', 'inner');
                }

                if ($join_table == 'jobs') {
                    $this->db->join('`jobs` AS j', 'p.`property_id` = j.`property_id`', 'inner');
                }

                if ($join_table == 'staff_accounts') {
                    $this->db->join('`staff_accounts` AS sa', 'sa.`StaffID` = a.`salesrep`', 'left');
                }

                if ($join_table == 'countries') {
                    $this->db->join('`countries` AS c', 'a.`country_id` = c.`country_id`', 'left');
                }

                 // API property generic table
                // PMe
                if ($join_table == 'api_property_data_pme') {
                    $this->db->join('`api_property_data` AS apd_pme', '( p.`property_id` = apd_pme.`crm_prop_id` AND apd_pme.`api` = 1 )', 'left');
                }

                // palace
                if ($join_table == 'api_property_data_palace') {
                    $this->db->join('`api_property_data` AS apd_palace', '( p.`property_id` = apd_palace.`crm_prop_id` AND apd_palace.`api` = 4 )', 'left');
                }

                // propertytree
                if ($join_table == 'api_property_data_pt') {
                    $this->db->join('`api_property_data` AS apd_pt', '( p.`property_id` = apd_pt.`crm_prop_id` AND apd_pt.`api` = 3 )', 'left');
                }

                if ($join_table == 'agency_priority') {
                    $this->db->join('agency_priority as aght', 'a.agency_id = aght.agency_id', 'left');
                }

                if ($join_table == 'agency_priority_marker_definition') {
                    $this->db->join('agency_priority_marker_definition as apmd', 'aght.priority = apmd.priority', 'left');
                }
                
            }
        }

        // custom joins
        if (isset($params['custom_joins']) && $params['custom_joins'] != '') {
            $this->db->join($params['custom_joins']['join_table'], $params['custom_joins']['join_on'], $params['custom_joins']['join_type']);
        }

        if(isset($params['search_type']) && $params['search_type'] != ''){
            $this->db->join($params['custom_joins_bn']['join_table'], $params['custom_joins_bn']['join_on'], $params['custom_joins_bn']['join_type']);
            $this->db->like('opd.`building_name`', $params['building_name']);
        }

        // filters
        // property
        if (isset($params['property_id'])) {
            $this->db->where('p.`property_id`', $params['property_id']);
        }
        if (isset($params['p_deleted'])) {
            $this->db->where('p.`deleted`', $params['p_deleted']);
        }else{//default
            $this->db->where('p.`deleted`',0);
        }
        if (isset($params['pm_id']) && $params['pm_id'] != '') {
            $this->db->where('p.`pm_id_new`', $params['pm_id']);
        }
        if (isset($params['job_id']) && $params['job_id'] != '') {
            $this->db->where('j.`id`', $params['job_id']);
        }
        if (isset($params['ps_service']) && $params['ps_service'] != '') {
            $this->db->where('ps.`service`', $params['ps_service']);
        }

        // agency filters
        if (isset($params['agency_filter']) && $params['agency_filter'] != '') {
            $this->db->where('a.`agency_id`', $params['agency_filter']);
        }
        if (isset($params['a_status'])) {
            $this->db->where('a.`status`', $params['a_status']);
        }
        if (isset($params['a_deleted'])) {
            $this->db->where('p.`agency_deleted`', $params['a_deleted']);
        }

        // Date filters
        if (isset($params['date']) && $params['date'] != '') {
            $this->db->where('p.`deleted_date`', $params['date']);
        }

        ##newly added by Gherx NLM filter
        $tt_nlm = '(p.is_nlm = 0 OR p.is_nlm IS NULL)'; //included NULL as there's some property nlm = NULL
       /* if ( isset($params['is_nlm'])) {

                if($params['is_nlm'] == 0){
                    $this->db->where($tt_nlm);
                }else if( $params['is_nlm'] ==1 ){
                    $this->db->where('p.`is_nlm`', $params['is_nlm']);
                }
           
        }else{//default
            $this->db->where($tt_nlm);
        }*/
        //Include NLM properties when searching in the search_results page
        if (isset($params['is_nlm_include'])) {
        } else {
            if ( array_key_exists('is_nlm', $params) ) {

                if($params['is_nlm'] == 0){
                    $this->db->where($tt_nlm);
                }else if( $params['is_nlm'] == 1 ){
                    $this->db->where('p.`is_nlm`', $params['is_nlm']);
                }
        
            }else{//default
                $this->db->where($tt_nlm);
            }
        }

        // search
        if (isset($params['search']) && $params['search'] != '') {
            $search_filter = "CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode))";
            $this->db->like($search_filter, $params['search']);
        }

        // postcodes
        if (isset($params['postcodes']) && $params['postcodes'] != '') {
            $this->db->where("p.`postcode` IN ( {$params['postcodes']} )");
        }

        //state 
        if (isset($params['state_filter']) && $params['state_filter'] != '') {
            if($params['state_filter']==-1){
                $this->db->where("(p.state IS NULL OR p.state = '')");
            }else{
                $this->db->where('p.`state`', $params['state_filter']);
            }
            
        }

        //date filter - chops 
        
        if($params['next_services'] == 1){
            if ($params['date_filter_from'] == '' && $params['date_filter_to'] == '') {
                $next_30_days = date('Y-m-d',strtotime("+30 days"));
                $this->db->where('p.`retest_date` >=', $next_30_days);
            }
        
            if (isset($params['date_filter_from']) && $params['date_filter_from'] != '' && isset($params['date_filter_to']) && $params['date_filter_to'] != '') {
                $this->db->where('p.`retest_date` >=', $params['date_filter_from']);
                $this->db->where('p.`retest_date` <=', $params['date_filter_to']);
            }
        
            if (isset($params['date_filter_from']) && $params['date_filter_from'] != '' && $params['date_filter_to'] == '') {
                $this->db->where('p.`retest_date` >=', $params['date_filter_from']);
            }
        
            if (isset($params['date_filter_to']) && $params['date_filter_to'] != '' && $params['date_filter_from'] == '') {
                $this->db->where('p.`retest_date` <=', $params['date_filter_to']);
            }
        }

        if (is_numeric($params['country_id'])) {
            $this->db->where('a.`country_id`', $params['country_id']);
        }

        // Electrician Only(EO)	
        if ( is_numeric($params['is_eo']) ) {
            $this->db->where('j.`is_eo`', $params['is_eo']);
        }

        // custom filter
        if ( $params['custom_where'] != '' ) {
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

    public function getPropertyServices($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`property_services` AS ps');
        $this->db->join('`property` AS p', 'ps.`property_id` = p.`property_id`', 'left');
        $this->db->join('`agency` AS a', 'p.`agency_id` = a.`agency_id`', 'left');
        
        // set joins
        if ($params['join_table'] > 0) {

            foreach ($params['join_table'] as $join_table) {

                if ($join_table == 'alarm_job_type') {
                    $this->db->join('`alarm_job_type` AS ajt', 'ps.`alarm_job_type_id` = ajt.`id`', 'left');
                }

                if ($join_table == 'staff_accounts') {
                    $this->db->join('`staff_accounts` AS sa', 'sa.`StaffID` = a.`salesrep`', 'left');
                }

                if ($join_table == 'agency_priority') {
                    $this->db->join('`agency_priority` AS aght', 'a.`agency_id` = aght.`agency_id`', 'left');
                }
                
                if ($join_table == 'agency_priority_marker_definition') {
                    $this->db->join('`agency_priority_marker_definition` AS apmd', 'aght.`priority` = apmd.`priority`', 'left');
                }
            }
        }



        // custom joins
        if (isset($params['custom_joins']) && $params['custom_joins'] != '') {
            $this->db->join($params['custom_joins']['join_table'], $params['custom_joins']['join_on'], $params['custom_joins']['join_type']);
        }

        // filters
        // property
        if (isset($params['property_id'])) {
            $this->db->where('p.`property_id`', $params['property_id']);
        }
        if (isset($params['p_deleted'])) {
            $this->db->where('p.`deleted`', $params['p_deleted']);
            if(is_numeric($params['p_deleted']) && $params['p_deleted'] == 0){
                $this->db->where('( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )');
            }
        }else{
            $this->db->where('p.`deleted`', 0);
            $this->db->where('( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )');
        }
        if (isset($params['pm_id']) && $params['pm_id'] != '') {
            $this->db->where('p.`pm_id_new`', $params['pm_id']);
        }

        // agency filters
        if (isset($params['agency_filter']) && $params['agency_filter'] != '') {
            $this->db->where('a.`agency_id`', $params['agency_filter']);
        }
        if (isset($params['a_status'])) {
            $this->db->where('a.`status`', $params['a_status']);
        }

        // Date filters
        if (isset($params['date']) && $params['date'] != '') {
            $this->db->where('j.`date`', $params['date']);
        }

        //PS Service Status
        if (isset($params['ps_service']) && $params['ps_service'] != '') {
            $this->db->where('ps.`service`', $params['ps_service']);
        }

        // country ID
        if (is_numeric($params['country_id']) && $params['country_id'] > 0) {
            $this->db->where('a.`country_id`', $params['country_id']);
        }

        // salesrep
        if (is_numeric($params['salesrep']) && $params['salesrep'] > 0) {
            $this->db->where('a.`salesrep`', $params['salesrep']);
        }

        // ajt id
        if (isset($params['ajt_id']) && $params['ajt_id'] != '') {
            $this->db->where('ps.`alarm_job_type_id`', $params['ajt_id']);
        }

        // is payable?
        if ( is_numeric($params['is_payable']) ) {
            $this->db->where('ps.`is_payable`', $params['is_payable']);
        }

        // search
        if (isset($params['search']) && $params['search'] != '') {
            $search_filter = "CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode))";
            $this->db->like($search_filter, $params['search']);
        }

        // custom filter
        if (isset($params['custom_where'])) {
            $this->db->where($params['custom_where']);
        }

        if (isset($params['is_nlm'])) {
            $this->db->where('is_nlm', $params['is_nlm']);
        }else{
            $tt_where = "(is_nlm=0 OR is_nlm IS NULL)";
            $this->db->where($tt_where);
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

    public function get_properties_with_active_services($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`property_services` AS ps');
        $this->db->join('`property` AS p', 'ps.`property_id` = p.`property_id`', 'left');
        $this->db->join('`agency` AS a', 'p.`agency_id` = a.`agency_id`', 'left');

        // filters
        // property
        if (isset($params['property_id'])) {
            $this->db->where('p.`property_id`', $params['property_id']);
        }
        if (isset($params['p_deleted'])) {
            $this->db->where('p.`deleted`', $params['p_deleted']);
        }
        if (isset($params['pm_id']) && $params['pm_id'] != '') {
            $this->db->where('p.`pm_id_new`', $params['pm_id']);
        }

        // agency filters
        if (isset($params['agency_filter']) && $params['agency_filter'] != '') {
            $this->db->where('a.`agency_id`', $params['agency_filter']);
        }
        if (isset($params['a_status'])) {
            $this->db->where('a.`status`', $params['a_status']);
        }

        // Date filters
        if (isset($params['date']) && $params['date'] != '') {
            $this->db->where('j.`date`', $params['date']);
        }

        //PS Service Status
        if (isset($params['ps_service']) && $params['ps_service'] != '') {
            $this->db->where('ps.`service`', $params['ps_service']);
        }

        // search
        if (isset($params['search']) && $params['search'] != '') {
            $search_filter = "CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode))";
            $this->db->like($search_filter, $params['search']);
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
     * Get New Tenant from new tenants table
     * params array
     * return query
     */
    public function get_new_tenants($params) {

        $this->db->select('pt.property_tenant_id, pt.property_id, pt.tenant_firstname, pt.tenant_lastname, pt.tenant_mobile, pt.tenant_landline, pt.tenant_email');
        $this->db->from('property_tenants as pt');

        if (!empty($params['property_id'])) {
            $this->db->where('property_id', $params['property_id']);
        }

        if (!empty($params['active'])) {
            $this->db->where('active', $params['active']);
        }

        if (!empty($params['limit'])) {
            $this->db->limit($params['limit']);
        }

        $query = $this->db->get();
        return $query;
    }

    /**
     * Update Tenant Details/Info/active/reactive
     */
    public function update_tenant_details($tenant_id, $data) {
        $this->db->where('property_tenant_id', $tenant_id);
        $this->db->update('property_tenants', $data);
        $this->db->limit(1);
        return ($this->db->affected_rows() > 0) ? true : false;
    }

    /**
     * ADD NEW TENANTS
     * Insert tenants by batch
     * param $data array
     * param $type normal/batch insert 
     */
    public function add_tenants($data, $type = NULL) {

        if ($type == "batch" && $type) { // type is set and = batch insert batch
            $this->db->insert_batch('property_tenants', $data);
            return ($this->db->affected_rows() > 0) ? true : false;
        } else { // type not set/normal insert normal
            $this->db->insert('property_tenants', $data);
            return ($this->db->affected_rows() > 0) ? true : false;
        }
    }

    /**
     * Update Property
     * @params property id
     * @params data array
     * return boolean
     */
    public function update_property($prop_id, $data) {

        $this->db->where('property_id', $prop_id);
        $this->db->update('property', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Update property services
     * @params property id
     * @params data array
     * return boolean
     */
    public function update_property_services($prop_id, $data) {
        $this->db->where('property_id', $prop_id);
        $this->db->update('property_services', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    // property tenants
    public function get_property_tenants($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`property_tenants` AS pt');
        $this->db->join('`property` AS p', 'pt.`property_id` =  p.`property_id`', 'left');

        // set join
        if ($params['join_table'] > 0) {

            foreach ($params['join_table'] as $join_table) {

                if ($join_table == 'agency') {
                    $this->db->join('`agency` AS a', ' p.`agency_id` = a.`agency_id`', 'left');
                }
            }
        }

        // custom joins
        if (isset($params['custom_joins']) && $params['custom_joins'] != '') {
            $this->db->join($params['custom_joins']['join_table'], $params['custom_joins']['join_on'], $params['custom_joins']['join_type']);
        }

        // filter
        if (is_numeric($params['property_id'])) {
            $this->db->where('p.`property_id`', $params['property_id']);
        }
        if (is_numeric($params['p_deleted'])) {
            $this->db->where('p.`deleted`', $params['p_deleted']);
        }
        if (isset($params['a_status']) && $params['a_status'] != '') {
            $this->db->where('a.`status`', $params['a_status']);
        }
        if (is_numeric($params['country_id'])) {
            $this->db->where('a.`country_id`', $params['country_id']);
        }

        if (isset($params['agency_filter']) && $params['agency_filter'] != '') {
            $this->db->where('a.`agency_id`', $params['agency_filter']);
        }

        // State filters
        if (isset($params['state_filter']) && $params['state_filter'] != '') {
            $this->db->where('p.`state`', $params['state_filter']);
        }

        // Region filters
        if (isset($params['region_filter']) && $params['region_filter'] != '') {
            $this->db->where_in('p.`postcode`', $params['region_filter']);
        }

        //search
        if (isset($params['search']) && $params['search'] != '') {
            $search_filter = "CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode))";
            $this->db->like($search_filter, $params['search']);
        }

        //search agency
        if (isset($params['search_agency']) && $params['search_agency'] != '') {
            $search_filter = "LOWER(a.agency_name)";
            $this->db->like($search_filter, $params['search_agency']);
        }

        // postcodes
        if (isset($params['postcodes']) && $params['postcodes'] != '') {
            $this->db->where("p.`postcode` IN ( {$params['postcodes']} )");
        }

        if ($params['property_tenant_id'] > 0) {
            $this->db->where('pt.`property_tenant_id`', $params['property_tenant_id']);
        }

        if ($params['pt_active'] > 0) {
            $this->db->where('pt.`active`', $params['pt_active']);
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

    function get_services($property_id, $alarm_job_type_id) {

        $ps_sql = $this->db->query("
			SELECT service
			FROM `property_services` 
			WHERE `property_id` = {$property_id}
			AND `alarm_job_type_id` = {$alarm_job_type_id}
		");

        if ($ps_sql->num_rows() > 0) {
            $s = $ps_sql->row_array();
            $service = $s['service'];
            switch ($service) {
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
        } else {
            $service = "N/A";
        }

        return $service;
    }

    /**
     * Restore Property by property ID
     * @params Property ID
     * @parmas data array
     */
    public function restore_property($prop_id, $data) {

        $this->db->where('property_id', $prop_id);
        $this->db->update('property', $data);
        $this->db->limit(1);

        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getCountryState() {
        $sql = "
            SELECT *
            FROM `states_def`
            WHERE `country_id` ={$this->config->item('country')}
        ";
        return $this->db->query($sql);
    }

    /**
     * Get PM 
     */
    public function get_agency_pm($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('agency_user_accounts as aua');
        $this->db->join('agency_user_account_types as auat', 'auat.agency_user_account_type_id = aua.user_type', 'left');

        if ($params['active'] && !empty($params['active'])) {
            $this->db->where('aua.active', $params['active']);
        }

        if ($params['agency_id'] && !empty($params['agency_id'])) {
            $this->db->where('aua.agency_id', $params['agency_id']);
        }

        if ($params['user_type'] && !empty($params['user_type'])) {
            $this->db->where('aua.user_type', $params['user_type']);
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

    public function check_duplicate_property($address_1, $address_2, $address_3, $state, $postcode) {

        $address_1_escate = $this->db->escape_str($address_1);
        $address_2_escate = $this->db->escape_str($address_2);
        $address_3_escate = $this->db->escape_str($address_3);

        $duplicateQuery = "
            SELECT 
                p.`property_id`,
                p.`address_1` AS p_address_1,
                p.`address_2` AS p_address_2,
                p.`address_3` AS p_address_3,
                p.`state` AS p_state,
                p.`postcode` AS p_postcode,
                p.`deleted`,
                a.`agency_id`,
                a.`agency_name`
            FROM `property`AS p
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE TRIM(LCASE(p.`address_1`)) = LCASE('" . $address_1_escate . "') 
            AND TRIM(LCASE(p.`address_2`)) = LCASE('" . $address_2_escate . "')
            AND TRIM(LCASE(p.`address_3`)) = LCASE('" . $address_3_escate . "') 
            AND TRIM(LCASE(p.`state`)) = LCASE('" . $this->db->escape_str($state) . "') 
            AND TRIM(LCASE(p.`postcode`)) = LCASE('" . $postcode . "');
        ";
        return $this->db->query($duplicateQuery);
    }

    public function check_duplicate_property_v2($params) {

        /*
          some address returns different suburb and postcode from the google object when matching with a property that has 2 address.
          sample property:
          10/2 Yulestar St Hamilton QLD 4007
          10/2 Yulestar St, Albion QLD 4010
         */

        $other_address = "{$params['suburb']} {$params['state']} {$params['postcode']}";

        $exist_in_crm_sql_str = "
            SELECT 
                `property_id`,
                `address_1`,
                `address_2`,
                `address_3`,
                `state`,
                `postcode`,
                `deleted`
            FROM `property`                                                          
            WHERE CONCAT_WS( ' ', TRIM(LOWER(address_3)), TRIM(LOWER(state)), TRIM(LOWER(postcode)) ) = '" . $this->db->escape_str(strtolower(trim($other_address))) . "'
            AND(
                TRIM(LOWER(address_1)) = '" . $this->db->escape_str(strtolower(trim($params['street_number_full']))) . "'
            )                                                                
            AND (
                TRIM(LOWER(address_2)) = '" . $this->db->escape_str(strtolower(trim($params['street_name']))) . "'
            )
            ORDER BY `address_2` ASC, `address_3` ASC, `address_1` ASC
        ";
        return $this->db->query($exist_in_crm_sql_str);
    }


    public function check_duplicate_full_address($params) {

        $street_num_fin_str = '';
        if( $params['street_num_fin'] != '' ){
            $street_num_fin_str = "{$params['street_num_fin']} ";
        }
        $full_address = "{$street_num_fin_str}{$params['street_name_fin']} {$params['suburb']} {$params['state']} {$params['postcode']}";

        $exist_in_crm_sql_str = "
            SELECT 
                p.`property_id`,
                p.`address_1` AS p_address_1,
                p.`address_2` AS p_address_2,
                p.`address_3` AS p_address_3,
                p.`state` AS p_state,
                p.`postcode` AS p_postcode,
                p.`deleted`,
                p.`is_nlm`,
                
                a.`agency_id`,
                a.`agency_name`
            FROM `property`AS p
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`                                                     
            WHERE CONCAT_WS( ' ', TRIM(LOWER(p.`address_1`)), TRIM(LOWER(p.`address_2`)), TRIM(LOWER(p.`address_3`)), TRIM(LOWER(p.`state`)), TRIM(LOWER(p.`postcode`)) ) = '" . $this->db->escape_str(strtolower(trim($full_address))) . "'           
        ";
        return $this->db->query($exist_in_crm_sql_str);

    }

    public function add_property($data) {

        $this->db->insert('property', $data);
        $this->db->limit(1);

        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    public function add_data_property($data) {

        $this->db->insert('api_property_data', $data);
        $this->db->limit(1);
    
        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    //add property services 
    public function add_property_services($data) {

        $this->db->insert('property_services', $data);
        return ($this->db->affected_rows() > 0) ? true : false;
    }

    // add property type
    public function add_property_type($data) {

        $this->db->insert('property_propertytype', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Insert Jobs
     * return last insert id
     */
    public function add_jobs($data) {

        $this->db->insert('jobs', $data);
        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        } else {
            return false;
        }
    }

    /**
     * Insert Property Fiels
     * Return Boolean
     */
    public function isnert_property_files($data) {

        $this->db->insert('property_files', $data);
        $this->db->limit(1);
        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function nlm_property( $prop_id, $params = [] ) {

        $nlm_from_agency = $params['nlm_from_agency'];
        $agency_id = $params['agency_id'];
        $agency_status = $params['agency_status'];

        $proceed_nlm = false;
        $return = false;

        $this->load->model('jobs_model');
        
        if( $nlm_from_agency == true ){ // NLM from agency, skip active job check

            $proceed_nlm = true;

        }else{ // default, NLM from property

            $has_active_jobs = $this->system_model->NLMjobStatusCheck($prop_id);

            if( $has_active_jobs == false ){ // dont NLM if it has active jobs
                $proceed_nlm = true;
            }

        }
        
        if( $proceed_nlm == true ){ // NLM process below -------------         
            
            // leaving reason data
            $reason_they_left = $params['reason_they_left'];
            $other_reason = $params['other_reason'];

            if( is_numeric($reason_they_left) ){

				// insert agency leaving reason
				$agency_res_insert_data = array(
					'property_id' => $prop_id,
					'reason' => $reason_they_left
				);

				// "other" reason
				if( $reason_they_left == -1 ){
					$agency_res_insert_data['other_reason'] = $other_reason;
				}

				$this->db->insert('property_nlm_reason', $agency_res_insert_data);

			}

            $db_params = array(
                'agency_deleted' => 0,
                'booking_comments' => "No longer managed as of " . date('d/m/Y') . " - by SATS.",
                'is_nlm' => 1,
                'nlm_timestamp' => date('Y-m-d H:i:s'),                
                'nlm_by_sats_staff' => $this->session->staff_id
            );
    
            // check if property has money owing and needs to verify paid
            if( $this->system_model->check_verify_paid($prop_id) == true ){
                $db_params['nlm_display'] = 1;
            }
    
            $this->update_property($prop_id, $db_params);

            // unlink property
            if( $prop_id > 0 ){
                $this->db->delete('api_property_data', array('crm_prop_id' => $prop_id));
            }            
    
            // replaced "cancelled" update query to loop and cancel every job so logs can be inserted(as ben's instruction)
            // get all jobs except Completed and Cancelled
            $job_sql = $this->db->query("
            SELECT `id` AS jid, `status`
            FROM `jobs`
            WHERE `property_id` = {$prop_id}
            AND `status` NOT IN('Completed','Cancelled') 
            ");
    
            foreach( $job_sql->result() as $job_row ){
    
                if( $job_row->jid > 0 ){
                            
                    // update job to cancelled
                    $update_data = array(
                        'status' => 'Cancelled',
                        'comments' => "This property was marked No Longer Managed by SATS on " . date("d/m/Y") . " and all jobs cancelled",
                        'cancelled_date' => date('Y-m-d')
                    );                    
                    $this->db->where('id', $job_row->jid);
                    $this->db->where('property_id', $prop_id);
                    $this->db->update('jobs', $update_data);

                    // insert log
                    if( $nlm_from_agency == true ){ // when NLM was called from deactivating agency
                        
                        $log_details = "Job with status <b>{$job_row->status}</b> cancelled due to agency being marked <b>{$agency_status}/b>";
    
                    }else{ // default
                        
                        $log_details = "Job with status <b>{$job_row->status}</b> cancelled due to Property being marked NLM";
                    }
                    
                    $log_params = array(
                        'title' => 72,  // Job Status Updated
                        'details' => $log_details,
                        'display_in_vjd' => 1,
                        'created_by_staff' => $this->session->staff_id,
                        'job_id' => $job_row->jid
                    );
                    $this->system_model->insert_log($log_params);
    
                }                
    
            }
    
            
            //update property_services
            // if property has completed job with a price this month and service changed this month
            $this_month_start = date("Y-m-01");
            $this_month_end = date("Y-m-t");

            // get completed job this month
            $job_sql_str = "
            SELECT j.`id`
            FROM `jobs` AS j               
            WHERE j.`property_id` = {$prop_id}
            AND j.`status` = 'Completed'
            AND j.`job_price` > 0
            AND j.`date` BETWEEN '{$this_month_start}' AND '{$this_month_end}'                         
            ";
            $job_sql = $this->db->query($job_sql_str);

            // get status change this month
            $ps_sql_str = "
            SELECT ps.`status_changed`
            FROM `property` AS p 
            INNER JOIN `property_services` AS ps ON p.`property_id` = ps.`property_id`
            WHERE p.`property_id` = {$prop_id} 
            AND CAST( ps.`status_changed` AS DATE ) BETWEEN '{$this_month_start}' AND '{$this_month_end}'
            ";
            $ps_sql = $this->db->query($ps_sql_str);

            $clear_is_payable = null;
            $payable = '';
            if( $job_sql->num_rows() > 0 && $ps_sql->num_rows() > 0 ){

                // DO nothing, leave is_payable as it is

            }else{

                // clear is_payable
                $clear_is_payable = "`is_payable` = 0,";
                $payable = '0';

            }
            //update property_services end

            // loop through existing property services                
            $ps_sql2 = $this->db->query("
            SELECT 
                ps.`property_services_id` AS ps_id,
                ps.`is_payable`,
                ajt.`type` AS service_type_name 
            FROM `property_services` AS ps  
            LEFT JOIN `alarm_job_type` AS ajt ON ps.`alarm_job_type_id` = ajt.`id`              
            WHERE ps.`property_id` = {$prop_id}  
            AND ps.`service` NOT IN(0,3)
            AND ps.`service` = 1  
            ");

            foreach( $ps_sql2->result() as $ps_row2 ){

                if( $ps_row2->ps_id > 0 ){ 

                    $this->db->query("
                    UPDATE `property_services`
                    SET 
                        `service` = 2,
                        {$clear_is_payable}
                        `status_changed` = '".date('Y-m-d H:i:s')."'
                    WHERE `property_services_id` = {$ps_row2->ps_id}
                    AND `property_id` = {$prop_id}
                    ");

                    if ($payable == '0') {
                        $details =  "Property Service <b>{$ps_row2->service_type_name}</b> unmarked <b>payable</b>";
                        $params = array(
                            'title' => 3, // Property Service Updated
                            'details' => $details,
                            'display_in_vpd' => 1,									
                            'agency_id' => $agency_id,
                            'created_by_staff' => $this->session->staff_id,
                            'property_id' => $prop_id
                        );
                        $this->system_model->insert_log($params);
                    }
                }                    

            } 

            if( $this->db->affected_rows() > 0 ){

                // add log
                $property_log_params = array(
                    'title' => 3, // Property Service Updated
                    'details' => 'Service changed from <b>SATS</b> to <b>No Response</b> as the agency was deactivated',
                    'display_in_vpd' => 1,
                    'agency_id' => $agency_id,
                    'created_by_staff' => $this->session->staff_id,
                    'property_id' => $prop_id
                );
                $this->system_model->insert_log($property_log_params);

            }
    
            // Insert job log
            //get staff name
            $staff_params = array(
                'sel_query' => "FirstName,LastName",
                'staff_id' => $this->session->staff_id,
            );
            $staff_info = $this->gherxlib->getStaffInfo($staff_params)->row_array();
            $log_details = "No Longer Managed, By {$staff_info['FirstName']} {$staff_info['LastName']} ";
            $log_params = array(
                'title' => 6, //Property No Longer Managed 	
                'details' => $log_details,
                'display_in_vpd' => 1,
                'created_by_staff' => $this->session->staff_id,
                'property_id' => $prop_id,
            );
            $this->system_model->insert_log($log_params);
    
            if( $nlm_from_agency != true ){

                ## Gherx > Add NLM Email Notification
                if(COUNTRY == 1){ //email for AU only
                    $noti_params = array('property_id' => $prop_id);
                    $this->nlm_email_notification($noti_params);
                }

            }            
    
            $return = true;

        }

        return $return;

    }

    public function jFindDupProp($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = "*";
        }

        // query switch tweak in order to work agency filter
        if($params['agency_filter']>0){ //filter by agency_id

            if (is_numeric($params['offset']) && is_numeric($params['limit'])) {
                $pag_str .= " LIMIT {$params['offset']}, {$params['limit']} ";
            }

            $q = "
            SELECT `p`.`property_id`, `p`.`address_1`, `p`.`address_2`, `p`.`address_3`, `p`.`state`, `p`.`postcode`, `p`.`deleted`, `p2`.`agency_id`, `p2`.`agency_name`
            FROM property AS p
            LEFT JOIN (
                SELECT `p2`.`property_id`, COUNT( * ) AS jcount2, `a2`.`agency_id`, `a2`.`agency_name`
                FROM `property` as `p2`
                Left JOIN `agency` as `a2` ON `a2`.`agency_id` = `p2`.`agency_id`
                WHERE `p2`.`address_1` != ''
                AND `p2`.`address_2` != ''
                AND `p2`.`address_3` != ''
                AND `p2`.`is_sales` = 0
                AND p2.deleted = 0
                AND ( p2.`is_nlm` = 0 OR p2.`is_nlm` IS NULL )
                AND `a2`.`country_id` = {$this->config->item('country')}
                GROUP BY TRIM( p2.`address_1` ), TRIM( p2.`address_2` ), TRIM( p2.`address_3` ), TRIM( p2.`state` ), TRIM( p2.`postcode` )
                HAVING `jcount2` > 1
            ) AS p2 ON p.property_id = p2.property_id
            WHERE p2.agency_id = {$params['agency_filter']}
            {$pag_str}";

            $query = $this->db->query($q);

        }else{ // no filter 

            $this->db->select($sel_query);
            $this->db->from('property as p');
            $this->db->join('agency as a', 'a.agency_id = p.agency_id', 'left');
            $this->db->where("p.address_1!=", "");
            $this->db->where("p.address_2!=", "");
            $this->db->where("p.address_3!=", "");
            $this->db->where("p.is_sales", 0);        
            $this->db->where("p.deleted", 0);       
            $where = "(p.is_nlm IS NULL OR p.is_nlm = 0)"; 
            $this->db->where($where);
            $this->db->where('a.country_id', $this->config->item('country'));

            $group_by = "TRIM( p.`address_1` ) , TRIM( p.`address_2` ) , TRIM( p.`address_3` ) , TRIM( p.`state` ) , TRIM( p.`postcode` )";
            $this->db->group_by($group_by);

            $this->db->having('jcount >', 1);

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

        }

        return $query;

        
    }

    function jGetOtherDupProp($property_id, $address_1, $address_2, $address_3, $state, $postcode) {
        $query = $this->db->query("
			SELECT 
				p.property_id, 
				p.`address_1`, 
				p.`address_2`, 
				p.`address_3`, 
				p.`state`, 
				p.`postcode`, 
				p.`deleted`,
				
				a.`agency_id`,
				a.`agency_name`
			FROM `property` AS p 
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE TRIM(LCASE(p.`address_1`)) = LCASE('" . $this->db->escape_str(trim($address_1)) . "') 
			  AND TRIM(LCASE(p.`address_2`)) = LCASE('" . $this->db->escape_str(trim($address_2)) . "') 
			  AND TRIM(LCASE(p.`address_3`)) = LCASE('" . $this->db->escape_str(trim($address_3)) . "') 
			  AND TRIM(LCASE(p.`state`)) = LCASE('" . $this->db->escape_str(trim($state)) . "') 
			  AND TRIM(LCASE(p.`postcode`)) = LCASE('" . $this->db->escape_str(trim($postcode)) . "')
            AND p.`property_id` != {$property_id}
            AND p.`is_sales` = 0
			AND a.`country_id` = {$this->config->item('country')};
        ");
        return $query;
    }

    function getPostcodeDuplicates() {

        // run comparison through all postcode
        $country_id = $this->config->item('country');
        $sql_str = "
			SELECT *
			FROM `postcode_regions` 
			WHERE `country_id` = {$country_id}
			AND `deleted` = 0
		";
        $sql = $this->db->query($sql_str);

        $duplicate = [];
        foreach ($sql->result_array() as $row) {

            // breakdown csv postcode, then compare to db postcode except itself
            $arr1 = explode(",", $row['postcode_region_postcodes']);
            $arr2 = array_filter($arr1);
            foreach ($arr2 as $pc) {

                $sql_str2 = "
				SELECT *
				FROM `postcode_regions` 
				WHERE `country_id` = {$country_id}
				AND `postcode_region_postcodes` LIKE '%{$pc}%'
				AND `postcode_region_id` != {$row['postcode_region_id']}
				AND `deleted` = 0
				";

                $sql2 = $this->db->query($sql_str2);
                if ($sql2->num_rows() > 0) {
                    $row2 = $sql2->row_array();

                    if (!in_array($pc, $duplicate)) {
                        $duplicate[] = $pc;
                    }
                }
            }
        }

        return $duplicate;
    }

    public function getPostcodeDuplicatesV2(){

        $params = array(
            'sel_query' => "sr.subregion_name as postcode_region_name, sr.sub_region_id as postcode_region_id, pc.postcode as postcode_region_postcodes",
            'delete' => 0        
        );
        $sql = $this->system_model->get_postcodes($params);

        $duplicate = [];
        foreach ($sql->result_array() as $row) {
           $pc = $row['postcode_region_postcodes'];
           $sub_region_id = $row['postcode_region_id'];

           $this->db->select('*');
           $this->db->from('postcode as pc');
           $this->db->join('sub_regions as sr','sr.sub_region_id = pc.sub_region_id','left');
           $this->db->where('pc.postcode', $pc);
           $this->db->where('sr.sub_region_id !=',$sub_region_id);
           $this->db->where('pc.deleted',0);
           $sql2 = $this->db->get();

           if ($sql2->num_rows() > 0) {

                if (!in_array($pc, $duplicate)) {
                    $duplicate[] = $pc;
                }

            }
        }
        return $duplicate;

    }

    function getPropertyNoAgency($start = 0, $limit = -1) {
        $this->db->select('*');
        $this->db->from('property');
        $this->db->where('agency_id=0');
        $this->db->order_by('tenant_ltr_sent', 'ASC');
        if ($limit >= 0) {
            $this->db->limit($limit, $start);
        }
        $query = $this->db->get();
        return $query;
    }

    function getPropertyNoAgencyCount() {
        $this->db->select('COUNT(*) as prop_count');
        $this->db->from('property');
        $this->db->where('agency_id=0');
        $this->db->order_by('tenant_ltr_sent', 'ASC');
        if ($limit >= 0) {
            $this->db->limit($limit, $start);
        }
        $query = $this->db->get();
        return $query;
    }

    public function get_properties_needs_verification($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('properties_needs_verification AS pnv');
        $this->db->join('`property` AS p', 'pnv.`property_id` = p.`property_id` AND pnv.`property_source`=1', 'left');

        // PMe API properties needs it own agency ID field from pnv table bec it cannot get it from property table bec it doesnt belong to crm
        $this->db->join('`agency` AS a', 'CASE WHEN pnv.`property_source`= 1 THEN p.`agency_id` = a.`agency_id` WHEN ( pnv.`property_source`= 2 OR pnv.`property_source`= 3 OR pnv.`property_source`= 7) THEN pnv.`agency_id` = a.`agency_id` END', 'inner');
        $this->db->join('agency_priority as aght', 'a.agency_id = aght.agency_id', 'left');
        $this->db->join('agency_priority_marker_definition as apmd', 'aght.priority = apmd.priority', 'left');

        if ($params['pnv_stat'] == 1) {
            $this->db->join('`api_property_data` AS apd', 'p.`property_id` = apd.`crm_prop_id`', 'left');
        }

        // custom joins
        if (isset($params['custom_joins']) && $params['custom_joins'] != '') {
            $this->db->join($params['custom_joins']['join_table'], $params['custom_joins']['join_on'], $params['custom_joins']['join_type']);
        }

        // filter
        if ($params['pnv_id'] > 0) {
            $this->db->where('pnv.`pnv_id`', $params['pnv_id']);
        }

        if (is_numeric($params['active'])) {
            $this->db->where('pnv.`active`', $params['active']);
        }

        if ($params['agency_id'] > 0) {
            $this->db->where('a.`agency_id`', $params['agency_id']);
        }

        if ($params['property_source'] > 0) {
            $this->db->where('pnv.`property_source`', $params['property_source']);
        }

        if ($params['property_id'] != '') {
            $this->db->where('pnv.`property_id`', $params['property_id']);
        }

        if (is_numeric($params['ignore_issue'])) {
            $this->db->where('pnv.`ignore_issue`', $params['ignore_issue']);
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

    public function get_no_active_job_properties($select = null, $is_show = null, $offset=null, $limit=null) {
        if (is_numeric($offset) && is_numeric($limit)) {
            $str = " LIMIT {$offset}, {$limit}";
        }

        if ($select === "" || $select === null) {
            $select = "p.`property_id`, p.`address_1`, p.`address_2`, p.`address_3`, a.`agency_id`, a.`agency_name`, p.`created`, j.service as j_service, j.job_type as j_type, ajt.type as ajt_type, aght.priority, pa.`hidden`";
        }

        if ($is_show == 1) {
            // $is_show_str = "AND pa.`is_acknowledged` = {$is_show}";
            $is_show_str = "";
        } else {
            $is_show_str = "AND pa.`hidden` IS  NULL OR pa.`hidden` = 0";
        }


        $sql = "
		SELECT $select
            FROM `property` AS p
            INNER JOIN jobs AS j ON p.property_id = j.property_id
            INNER JOIN property_services AS ps ON ( j.`service` = ps.`alarm_job_type_id` AND j.`property_id` = ps.`property_id` AND ps.service =1 )
            LEFT JOIN `intentionally_hidden_active_properties` AS pa ON pa.`property_id` = p.`property_id` 
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `agency_priority` AS aght ON a.`agency_id` = aght.`agency_id`
            LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
            WHERE ( j.`status` =  'Cancelled' OR j.`status` =  'Completed')
            AND NOT YEAR(j.date) BETWEEN '" . date('Y', strtotime('-1 year')) . "' AND '" . date('Y') . "'
            AND YEAR(j.date) != '0'
            AND p.`deleted` =0
            AND a.`status` = 'active'
            AND j.`del_job` =0
            AND j.`status` = 'Completed'
            AND a.franchise_groups_id != 14
            AND a.`country_id` = {$this->config->item('country')}
            AND (
            p.`is_nlm` =0
            OR p.`is_nlm` IS NULL
                        )
            AND p.property_id NOT IN (
                    SELECT  DISTINCT(p.`property_id`)
                    FROM `jobs` AS j1
                    LEFT JOIN `property` AS p ON j1.`property_id` = p.`property_id`
                    LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                    WHERE p.`deleted` =0
                    AND a.`status` = 'active'
                    AND j1.`del_job` = 0
                    AND j1.`status` =  'Completed'
                    AND a.`country_id` = {$this->config->item('country')}
                    AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
                    AND (
                    p.`is_nlm` =0
                    OR p.`is_nlm` IS NULL
                    )
                    AND YEAR( j1.`date` )
                    BETWEEN '" . date('Y', strtotime('-1 year')) . "' AND '" . date('Y') . "'
                )
            AND p.property_id NOT IN (
                SELECT DISTINCT(p2.`property_id`)
                FROM  `jobs` AS j2
                LEFT JOIN  `property` AS p2 ON j2.`property_id` = p2.`property_id` 
                LEFT JOIN  `agency` AS a2 ON p2.`agency_id` = a2.`agency_id` 
                WHERE p2.`deleted` =0
                AND a2.`status` =  'active'
                AND j2.`del_job` =0
                AND j2.`status` !=  'Cancelled'
                AND j2.`status` !=  'Completed'
                AND a2.`country_id` ={$this->config->item('country')}
            )
            {$is_show_str}
            GROUP BY p.property_id
            ORDER BY p.property_id
            {$str}
	";
        $query = $this->db->query($sql);

        // echo "<pre>";
        // echo $this->db->last_query();
        // exit;
        return $query;
    }

    public function nlm_email_notification($params){

        ##get property details
        $p_params = array(
            'sel_query'=> "p.property_id, p.address_1, p.address_2, p.address_3",
            'property_id' => $params['property_id'],
            'is_nlm' => 1
        );
        $prop_q = $this->get_properties($p_params);
        $prop_row = $prop_q->row_array();

        $email_data['prop_id'] = $prop_row['property_id'];
        $email_data['prop_name'] = "{$prop_row['address_1']} {$prop_row['address_2']}, {$prop_row['address_3']}";

        $getCountryInfo = $this->gherxlib->getCountryViaCountryId($this->config->item('country'));

        $email_from = $getCountryInfo->outgoing_email;
        //$email_to = "itsmegherx@gmail.com";
        $email_to = $this->config->item('sats_accounts_email');
        $email_subject = "Property NLM Notification";

        //email config
        $config = Array(
            'mailtype' => 'html',
            'charset' => 'iso-8859-1'
        );
        $this->email->initialize($config);
        $this->email->set_newline("\r\n");

        $this->email->from($email_from, 'CRM');
        $this->email->to($email_to); 
        $this->email->subject($email_subject);
        $body = $this->load->view('emails/nlm_email.php', $email_data, TRUE);
        $this->email->message($body);
        $this->email->send();

    }

    public function get_properties_with_multiple_services($params) {

        $sel_query = "";
        if ($params['get_agencies']){
            $sel_query = "a.agency_name agency";
        } elseif ($params['get_states']){
            $sel_query = "p.state ";
        } else {
            $sel_query = "CONCAT(p.address_1, ' ', p.address_2,', ',p.address_3, ', ', p.state) address,
                      ps.property_id, 
                      pt.services AS property_services, 
                      a.agency_id,
                      a.agency_name,
                      aght.priority,
                      apmd.abbreviation,
                      asv.services AS agency_services";
        }

        $this->db->select($sel_query)
            ->from('(SELECT ps.property_id, COUNT(service) AS services
                    FROM property_services ps
                    WHERE ps.service = 1
                    GROUP BY ps.property_id
                    HAVING services > 1) AS ps')

            ->join('property AS p','p.property_id = ps.property_id')

            ->join('(SELECT ps.property_id , GROUP_CONCAT(ps.alarm_job_type_id) AS services
                    FROM property_services ps 
                    JOIN alarm_job_type AS aj ON aj.id = ps.alarm_job_type_id
                    WHERE ps.service = 1
                    GROUP BY ps.property_id
                    HAVING (2 NOT IN (services) AND 6 NOT IN (services)) OR 
                           (2 NOT IN (services) AND 15 NOT IN (services)) 
                    )  AS pt', 'pt.property_id = p.property_id')

            ->join('agency AS a','a.agency_id = p.agency_id')
            ->join('agency_priority AS aght','a.agency_id = aght.agency_id')
            ->join('agency_priority_marker_definition AS apmd','aght.priority = apmd.priority')

            ->join('(SELECT a.agency_id, GROUP_CONCAT(a.service_id) AS services 
                        FROM agency_services a 
                        GROUP BY agency_id) AS asv','a.agency_id = asv.agency_id')
            ->where('(p.is_nlm IS NULL OR p.is_nlm = 0)')
            ->where('p.deleted', 0)
            ->where('a.status', 'active');
        
        // state filter
        if ($params['state_filter']){
            $this->db->where('p.state', $params['state_filter']);
        }

        // agency filter
        if ($params['agency_filter']) {
            $this->db->where('a.agency_name', $params['agency_filter']);
        }

        // get agencies or states
        if ($params['get_agencies'] || $params['get_states']){
            return $this->db->get()->result(); 
        }

        // search filter
        if ($params['search_filter']){
            $this->db->having('(a.agency_name LIKE "%' . $params['search_filter'] . '%" 
                     OR address LIKE "%' . $params['search_filter'] . '%"  )');
        }

        // limit
        if (isset($params['limit']) && $params['limit'] > 0) {
            $this->db->limit($params['limit'], $params['offset']);
        }

        if ($params['total_rows']){
                return $this->db->get()->num_rows(); 
        }
            
        return $this->db->get()->result();               
    }

    //Get Agency Name
	public function getAgencyName($agency_id, $country_id){

        $this->db->select('agency_name');
        $this->db->from('agency');
        $this->db->where('agency_id', $agency_id);

        // country filter
        if ($country_id == 1) {
            $this->db->where('franchise_groups_id', 10);
        }

        if ($country_id == 2) {
            $this->db->where('franchise_groups_id', 37);
        }

        return $this->db->get()->result();   

    }

    public function payableCheck($property_id){
        // clear is_payable conditions, must be placed before property update bec nlm_timestamp gets cleared	
		$this_month_start = date("Y-m-01");
		$this_month_end = date("Y-m-t");

		$sixty_days_ago = date("Y-m-d",strtotime("-61 days"));

		// get NLM date
        $this->db->select('nlm_timestamp');
        $this->db->from('property');
        $this->db->where('property_id', $property_id);
        $data = $this->db->get()->result();

        $tmp_date = $data[0]->nlm_timestamp;

        $nlm_date = date('Y-m-d',strtotime($tmp_date));
        //$nlm_date = "2022-03-25";

        /*
        Month Start: 2022-04-01
        Month End: 2022-04-30
        Month Ago: 2022-02-18
        NLM Date: 2022-04-25
        */

		// if status change is within 60 days ago but not within this month
        if(  $nlm_date > $sixty_days_ago && !( $nlm_date >= $this_month_start && $nlm_date <= $this_month_end ) ){

            //echo "IF";
            //exit();

			// clear is_payable
            $updateService = array(
                'is_payable' => 0,
                'service'   => 2
            );

            $this->db->where('property_id', $property_id);
            $this->db->update('property_services', $updateService);

            if($this->db->affected_rows()>0){
                return true;
            }
            else{
                return false;
            }

		}
        else{
            //echo "ELSE";
            //exit();
            /*
			// update active service to is_payable to 1 and updated status changed to today

            $this->db->select('ajt.`type` AS ajt_type_name');
            $this->db->from('`property_services` as ps');
            $this->db->join('`alarm_job_type` AS ajt','ps.`alarm_job_type_id` = ajt.`id`','left');
            $this->db->where('ps.`property_id`', $property_id);
            $ps_tt_sql = $this->db->get()->result();
            
            //print_r($ps_tt_sql);
            //exit();

            ## Al > add is_payable log
			$mark_unmark = "marked";
            foreach ($ps_tt_sql as $val) {

                $logData = array(
                    'property_id' => $property_id,
                    'staff_id' => $_SESSION['staff_id'],
                    'event_type' => 'Property Sales Commission',
                    'event_details' => 'Property Service <b>'.$val->ajt_type_name.'</b> ' .$mark_unmark. ' <b>payable</b>',
                    'log_date' => date('Y-m-d H:i:s'),
                    'hide_delete' => 1
                );
                $this->db->insert('property_event_log', $logData);
            }
            */
            
            // set is_payable
            $updateService = array(
                'is_payable' => 1,
                'status_changed' => date('Y-m-d H:i:s')
            );

            $this->db->where('property_id', $property_id);
            $this->db->where('service', 1);
            $this->db->update('property_services', $updateService);

            if($this->db->affected_rows()>0){
                return true;
            }
            else{
                return false;
            }
		}
    }

    //Check property if from API
	public function apiCheck($property_id){

        $this->db->select('api');
        $this->db->from('api_property_data');
        $this->db->where('crm_prop_id', $property_id);

        return $this->db->get()->result();   

    }

    //Check property if from API
	public function get_connected_pme_properties($agency_id){
        $this->db->select('p.`property_id`, apd.`api`, apd.`api_prop_id`, p.`agency_id`, p.`is_nlm`, p.`address_1`, p.`address_2`, p.`address_3`, p.`state`, p.`postcode`');
        $this->db->from('property as p');
        $this->db->join('`api_property_data` AS apd', 'p.`property_id` = apd.`crm_prop_id`', 'left');
        $this->db->where('p.`agency_id`', $agency_id);
        $this->db->where('apd.`api_prop_id` !=', NULL);

        return $this->db->get()->result();   

    }

    public function get_unlinked_api_properties($params){

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        if( $params['custom_where'] && $params['custom_where']!="" ){
            $custom_where = $params['custom_where'];
        }

        if (is_numeric($params['offset']) && is_numeric($params['limit'])) {
            $pag_str .= " LIMIT {$params['offset']}, {$params['limit']} ";
        }

        return $this->db->query("
            SELECT {$sel_query}
            FROM `property` AS `p`
            LEFT JOIN `agency` AS `a` ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `agency_priority` AS `aght` ON a.`agency_id` = aght.`agency_id`
            LEFT JOIN `agency_api_tokens` as `apt` ON `apt`.`agency_id` = `a`.`agency_id`
            LEFT JOIN `agency_api` as `aapi` ON `apt`.`api_id` = `aapi`.`agency_api_id`
            LEFT JOIN `api_property_data` as `apd` ON `p`.`property_id` = `apd`.`crm_prop_id`
            WHERE `p`.`deleted` = 0
            AND `a`.`status` = 'active'
            AND `p`.`agency_deleted` = 0
            AND (`p`.`is_nlm` =0 OR `p`.`is_nlm` IS NULL)
            AND apt.agency_api_token_id IS NOT NULL
            AND apd.api_prop_id IS NULL 
            AND (apd.active IS NULL OR apd.active = 1)
            {$custom_where}
            {$pag_str}
        ");
    }

    public function get_update_property_variation($agency_id, $offset = null, $per_page = null)
    {
        $this->db->select("
            a.agency_id,
            a.agency_name,
            aght.priority,
            p.property_id,
            p.address_1,
            p.address_2,
            p.address_3,
            p.state`,
            p.postcode,
            p.qld_new_leg_alarm_num            
        ");

        $this->db->from("property as p");
        $this->db->join("agency as a", "p.agency_id = a.agency_id", "left");
        $this->db->join("agency_priority as aght", "a.agency_id = aght.agency_id", "left");
        $this->db->where("p.deleted", 0);
        $this->db->group_start();
        $this->db->where("p.is_nlm", 0);
        $this->db->or_where("p.is_nlm IS NULL");
        $this->db->group_end();

        if ($agency_id){
            $this->db->where("a.agency_id", $agency_id);
        }

        return $this->db->get()->result();
    }

    public function get_property_service_price($property_id)
    {
        $this->db->select("property_services_id, price");
        $this->db->from("property_services");
        $this->db->where("property_id", $property_id);
        
        return $this->db->get();
    }

    public function get_property_current_variation($property_id)
    {    
        $this->db->select("
            pv.agency_price_variation,
            apv.amount,
            apv.type,
            apvr.reason
        ");

        $this->db->from("property_variation as pv");
        $this->db->join("agency_price_variation as apv", "pv.agency_price_variation = apv.id", "left");
        $this->db->join("agency_price_variation_reason as apvr", "apv.reason = apvr.id", "left");
        $this->db->where("pv.property_id", $property_id);
        $this->db->where("pv.active", 1);

        return $this->db->get();
    }

    public function update_coordinates($params){

        $property_id = $params['property_id'];
        $acco_id = $params['acco_id'];

        if( $property_id > 0 ){ // property

            $prop_sql = $this->db->query("
            SELECT 
                `property_id`, 
                `address_1` AS p_address_1, 
                `address_2` AS p_address_2, 
                `address_3` AS p_address_3, 
                `state` AS p_state, 
                `postcode` AS p_postcode, 
                `lat`,
                `lng`
            FROM `property`
            WHERE `property_id` = {$property_id}
            ");            

            if( $prop_sql->num_rows() > 0 ){

                $prop_row = $prop_sql->row();
                $prop_address = "{$prop_row->p_address_1} {$prop_row->p_address_2} {$prop_row->p_address_3} {$prop_row->p_state} {$prop_row->p_postcode}";   

                if( ( $prop_row->lat == "" || $prop_row->lng == "" ) && $prop_address != '' ){
                    
                    $coordinate = $this->system_model->getGoogleMapCoordinates($prop_address);	                            

                    if( $property_id > 0 && $coordinate['lat'] != '' && $coordinate['lng'] != '' ){

                        // update lat lng
                        $update_data = array(
                            'lat' => $coordinate['lat'],
                            'lng' => $coordinate['lng']
                        );  
                        $this->db->where('property_id', $property_id);            
                        $this->db->update('property', $update_data);

                    }
                    
                }

            }            

        }else if( $acco_id > 0 ){ // accomodation

            $acco_sql = $this->db->query("
            SELECT 
                `lat`,
                `lng`,
                `address`
            FROM `accomodation`
            WHERE `accomodation_id` = {$acco_id}
            ");            

            if( $acco_sql->num_rows() > 0 ){

                $acco_row = $acco_sql->row();

                if( ( $acco_row->lat == "" || $acco_row->lng == "" ) && $acco_row->address != '' ){
                    
                    $coordinate = $this->system_model->getGoogleMapCoordinates($acco_row->address);	                            

                    if( $property_id > 0 && $coordinate['lat'] != '' && $coordinate['lng'] != '' ){

                        // update lat lng
                        $update_data = array(
                            'lat' => $coordinate['lat'],
                            'lng' => $coordinate['lng']
                        );  
                        $this->db->where('accomodation_id', $acco_id);            
                        $this->db->update('accomodation', $update_data);

                    }
                    
                }

            }            

        }        

    }

}
