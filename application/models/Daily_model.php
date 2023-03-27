<?php

class Daily_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
        $this->load->model('daily_model');
        $this->load->model('jobs_model');
    }

  
    public function findBookedJobsNotOnAnySTR($params){
	
        $today = date('Y-m-d');
        $next_2_days = date('Y-m-d',strtotime('+2 days'));
        
        if ($params['sel_query'] && !empty($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('jobs as j');
        $this->db->join('property as p','p.property_id = j.property_id','left');
        $this->db->join('agency as a','a.agency_id = p.agency_id','left');
        $this->db->join('agency_priority as aght', 'a.agency_id = aght.agency_id', 'left');
        $this->db->join('staff_accounts as sa','sa.StaffID = j.assigned_tech','left');
       // $this->db->join('tech_run_rows as trr','trr.row_id = j.id AND trr.row_id_type = "job_id"','left');
        //$this->db->join('tech_run as tr','tr.tech_run_id = trr.tech_run_id','left');
        $this->db->where('j.status','Booked');
        $this->db->where('j.date',$next_2_days);
       // $this->db->where(array('tr.tech_run_id' => NULL));
        
        // sort
        if (isset($params['sort_list'])) {
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }

        // group by
        if( isset($params['group_by']) && $params['group_by'] != '' ){
            $this->db->group_by($params['group_by']);
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

    public function findJobsOnSTR($job_id){

        // fetch all future STR
        $sql_str = "
            SELECT COUNT(trr.`tech_run_rows_id`) AS trr_count
            FROM `tech_run_rows` AS trr
            LEFT JOIN `tech_run` AS tr ON trr.`tech_run_id` = tr.`tech_run_id` 
            LEFT JOIN `jobs` AS j ON ( j.`id` = trr.`row_id` AND trr.`row_id_type` =  'job_id' )
            WHERE j.`id` = {$job_id}	
            AND trr.`hidden` = 0
            AND j.`del_job` = 0
            AND tr.`country_id` = {$this->config->item('country')}
            AND tr.`date` >= '".date('Y-m-d')."'
        ";
        $sql = $this->db->query($sql_str);
        $row = $sql->row_array();

        if( $row['trr_count'] > 0 ){
            return true;
        }else{
            return false;
        }
        
    }

    /**
     * get multiple jobs
     */
    public function getMultipleJobs($params){

       // $having = "HAVING jcount >=2";

        if ($params['sel_query'] && !empty($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('jobs as j');
        $this->db->join('`alarm_job_type` AS ajt', 'j.`service` = ajt.`id`', 'left');
        $this->db->join('property as p','p.property_id = j.property_id','left');
        $this->db->join('agency as a','a.agency_id = p.agency_id','left');
        $this->db->join('agency_priority as aght', 'a.agency_id = aght.agency_id', 'left');
        $this->db->where('j.status !=', 'Completed');
        $this->db->where('j.status !=', 'Cancelled');
        $this->db->where('p.deleted', 0);
        $this->db->where('( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )');
        $this->db->where('a.status','active');
        $this->db->where('j.del_job',0);
        $this->db->where('a.country_id',$this->config->item('country'));

        // custom filter
        if ( $params['custom_where'] !='' ) {
            $this->db->where($params['custom_where']);
        }

        $this->db->group_by('j.property_id');
        $this->db->having('jcount >=',2);
        
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


    public function getDuplicateVisit($params){

        // $having = "HAVING jcount >=2";
 
        if ($params['sel_query'] && !empty($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $last_30_days = date('Y-m-d',strtotime('-30 days'));

        //agency_filter
        if( isset($params['agency_filter']) && $params['agency_filter'] != "" ){            
            $custom_agency_filter = "AND a.agency_id = {$params['agency_filter']}";
        }
        
        // limit
        if( isset($params['limit']) && $params['limit'] > 0 ){            
            $limit_str = "LIMIT {$params['offset']},{$params['limit']}";
        }
        /* >> disabled and update as per below
        $sql_str = "
            SELECT {$sel_query}     
            FROM `jobs` as j
            LEFT JOIN `property` as p ON p.`property_id` = j.`property_id`
            LEFT JOIN `agency` as a ON a.`agency_id` = p.`agency_id`
            WHERE 
            j.`status` = 'Completed'
            AND j.`date` >= ( CURDATE( ) - INTERVAL 30 DAY)
            AND j.`assigned_tech` != 1 
            AND j.`assigned_tech` != 2    
            AND j.`del_job` =0
            AND p.`deleted` =0
            AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
            AND a.`status` = 'active'
            AND a.`country_id` = {$this->config->item('country')}
            AND j.`property_id` IN(
                SELECT DISTINCT j2.`property_id`
                FROM `jobs` as j2
                LEFT JOIN `property` as p2 ON p2.`property_id` = j2.`property_id`
                LEFT JOIN `agency` as a2 ON a2.`agency_id` = p2.`agency_id`
                WHERE ( j2.`job_type` = 'Yearly Maintenance' OR j2.`job_type` = 'Annual Visit' )
                AND (
                    j2.`status` != 'Completed' 
                    AND j2.`status` != 'Merged Certificates' 
                    AND j2.`status` != 'Pre Completion' 
                    AND j2.`status` != 'Cancelled' 
                    AND j2.`status` != 'DHA'
                    AND j2.`status` != 'Pending'
                    AND j2.`status` != 'To Be Invoiced'
                ) 
                AND j2.`del_job` =0
                AND p2.`deleted` =0
                AND ( p2.`is_nlm` = 0 OR p2.`is_nlm` IS NULL )
                AND a2.`status` = 'active'
                AND a2.`country_id` = {$this->config->item('country')}    
            )                           
            {$custom_agency_filter}
            {$limit_str}                                                 
        ";*/
                
        ##new query with extra filter for start_date
        $sql_str = "
        SELECT {$sel_query}     
        FROM `jobs` as j
        LEFT JOIN `property` as p ON p.`property_id` = j.`property_id`
        LEFT JOIN `agency` as a ON a.`agency_id` = p.`agency_id`
        LEFT JOIN `agency_priority` as aght ON a.`agency_id` = aght.`agency_id`
        WHERE 
        j.`status` = 'Completed'
        AND j.`date` >= ( CURDATE( ) - INTERVAL 30 DAY)
        AND j.`assigned_tech` != 1 
        AND j.`assigned_tech` != 2    
        AND j.`del_job` =0
        AND p.`deleted` =0
        AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
        AND a.`status` = 'active'
        AND a.`country_id` = {$this->config->item('country')}
        AND j.`property_id` IN(
            SELECT DISTINCT j2.`property_id`
            FROM `jobs` as j2
            LEFT JOIN `property` as p2 ON p2.`property_id` = j2.`property_id`
            LEFT JOIN `agency` as a2 ON a2.`agency_id` = p2.`agency_id`
            LEFT JOIN `agency_other_pref` as aop ON a2.`agency_id` = aop.`agency_id`
            WHERE ( j2.`job_type` = 'Yearly Maintenance' OR j2.`job_type` = 'Annual Visit' )
            AND (
                j2.`status` != 'Completed' 
                AND j2.`status` != 'Merged Certificates' 
                AND j2.`status` != 'Pre Completion' 
                AND j2.`status` != 'Cancelled' 
                AND j2.`status` != 'DHA'
                AND j2.`status` != 'Pending'
                AND j2.`status` != 'To Be Invoiced'
            ) 
            AND j2.`del_job` =0
            AND p2.`deleted` =0
            AND ( p2.`is_nlm` = 0 OR p2.`is_nlm` IS NULL )
            AND a2.`status` = 'active'
            AND a2.`country_id` = {$this->config->item('country')}   
            AND (j.date + INTERVAL 30 DAY) >= (j2.start_date + INTERVAL 
                                                    CASE WHEN aop.renewal_start_offset !='' THEN aop.renewal_start_offset
                                                    WHEN a.state = 'NSW' THEN {$this->config->item('renewal_start_offset_nsw')}
                                                    ELSE {$this->config->item('renewal_start_offset_default')}
                                                    END
                                                DAY) 
        )                           
        {$custom_agency_filter}
        {$limit_str}                                                 
    ";

        $query = $this->db->query($sql_str);
        
        if( isset($params['display_query']) && $params['display_query'] == 1 ){
            echo $this->db->last_query();
        }
        
        return $query;
         
    }

    // get property's active job
    public function get_duplicate_visit_active_jobs($property_id){

        if( $property_id > 0 ){

            return $this->db->query("
            SELECT 
                j.`id` AS jid,
                j.`job_type`,
                j.`status`
            FROM `jobs` as j     
            WHERE ( j.`job_type` = 'Yearly Maintenance' OR j.`job_type` = 'Annual Visit' )
            AND (
                j.`status` != 'Completed' 
                AND j.`status` != 'Merged Certificates' 
                AND j.`status` != 'Pre Completion' 
                AND j.`status` != 'Cancelled' 
                AND j.`status` != 'DHA'
            ) 
            AND j.`property_id` = {$property_id} 
            AND j.`del_job` =0
            ");

        }        

    }

    public function getOtherMultipleJobs($property_id,$jid){
        $sql = "
            SELECT 
            j.`id`, 
            j.`job_type`, 
            j.`status`, 
            j.`property_id`, 
            j.`service` AS jservice, 

            p.`address_1`, 
            p.`address_2`, 
            p.`address_3`, 
            p.`state`, 
            p.`deleted`, 
            
            a.`agency_name`, 
            a.`agency_id`,
            a.`allow_upfront_billing`,
            aght.priority,
            
            ajt.`id` AS ajt_id,
            ajt.`type` AS ajt_type
            
            FROM `jobs` AS j
            LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `agency_priority` AS aght ON a.`agency_id` = aght.`agency_id`
            WHERE j.`status` != 'Completed'
            AND j.`status` != 'Cancelled'
            AND p.`deleted` =0
            AND a.`status` = 'active'
            AND j.`del_job` = 0
            AND p.`property_id` = {$property_id}
            AND j.`id` != {$jid}
            AND a.`country_id` ={$this->config->item('country')}
        ";
        return $this->db->query($sql);
    }

    ## Note old table > no use anymore :Gherx
    public function getMissingRegionProperty($start,$limit){
        
        if(is_numeric($start) && is_numeric($limit)){
            $str .= " LIMIT {$start}, {$limit}";
        }
    
        return $this->db->query("
            SELECT  p.`property_id`, p.`address_1`, p.`address_2`,  p.`address_3`, p.`state`, p.`postcode`, a.`agency_id` , a.`agency_name`
            FROM  `property` AS p
            LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id` 
            LEFT JOIN  `postcode_regions` AS pr ON (
                pr.`postcode_region_postcodes` LIKE CONCAT(  '%', p.`postcode` ,  '%' ) AND 
                pr.`country_id` ={$this->config->item('country')} AND 
                pr.`deleted` = 0
            )
            WHERE p.`deleted` =0
            AND a.`status` =  'active'
            AND a.`country_id` ={$this->config->item('country')}
            AND pr.`postcode_region_id` IS NULL 
            {$str}
        ");
        
    }

    public function getUnservicedProperties($prop,$start,$limit){

        // paginate
        if(is_numeric($start) && is_numeric($limit)){
            $str = "LIMIT {$start}, {$limit}";
        }
        
        // format array to comma separated
        $p_arr = array();
        foreach( $prop->result_array() as $p){
            $p_arr[] = $p['property_id'];
        }
        $ex_prop = implode(",",$p_arr);
    
        return $this->db->query("
            SELECT 
                DISTINCT j.`property_id`,			
                p.`address_1` AS p_address1, 
                p.`address_2` AS p_address2, 
                p.`address_3` AS p_address3, 
                p.`state` AS p_state, 
                p.`postcode` AS p_postcode,
                a.`agency_id`,
                a.`agency_name`
            FROM `jobs` AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            INNER JOIN `property_services` AS ps ON ( 
                j.`property_id` = ps.`property_id` 
                AND j.`service` = ps.`alarm_job_type_id` 
            )
            WHERE p.`property_id` NOT IN({$ex_prop})
            AND ps.`service` =1
            AND p.`deleted` = 0
            AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
            AND p.`agency_deleted` = 0
            AND a.`status` = 'active'
            AND (p.is_nlm!=1 OR p.is_nlm IS NULL)
            AND a.`country_id` = {$this->config->item('country')}
            AND (j.`status`!='Booked' AND j.`status`!='To Be Booked' AND j.`status`!='Send Letters' AND j.`status`!='On Hold' AND j.`status`!='On Hold - COVID' AND j.`status`!='Pre Completion' AND j.`status`!='Merged Certificates')
            AND (j.`date` >  DATE_ADD(NOW(), INTERVAL - 350 DAY ) OR ( j.`date` <  DATE_ADD(NOW(), INTERVAL - 350 DAY ) AND j.`job_type`='Yearly Maintenance' ))
            ORDER BY j.`property_id` DESC
            {$str}
        ");
        
    }

    public function getExcludedProperties(){

        return $this->db->query("
            SELECT DISTINCT j.`property_id`
            FROM `jobs` AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `property_services` AS ps ON ( j.`property_id` = ps.`property_id`
            AND j.`service` = ps.`alarm_job_type_id` )
            WHERE
            a.`country_id` = {$this->config->item('country')}
            AND p.`deleted` = 0
            AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
            AND ( 
                j.`status` = 'Pending'
                OR j.`date` IS NULL
                OR j.`date` = '0000-00-00'
                OR j.`job_type` = 'Once-off'
                OR ( j.`job_type` = '240v Rebook' || j.`is_eo` = 1 )
                OR ( j.`date` >= '". date("Y-m-d",strtotime("-1 year"))."' AND j.`job_type` = 'Yearly Maintenance' ) 			
            )	
        ");
    
    }

    public function update_unservice_properties_for_cron($prop_id){
        $this->db->query("
            UPDATE `property` 
            SET 
                `is_unserviced` = 1
            WHERE `property_id` IN ({$prop_id})
        ");
        $res = $this->db->affected_rows();
        if($res > 0){
            return $res;
        }else {
            return 0;
        }
    
    }

    public function unamark_unservice_properties_for_cron(){
        $this->db->query("
            UPDATE `property` 
            SET 
                `is_unserviced` = 0
            WHERE `is_unserviced` = 1
        ");
        $res = $this->db->affected_rows();
        if($res > 0){
            return $res;
        }else {
            return 0;
        }

    }

    public function get_unserviced_by_markers($start,$limit){

        // paginate
        if(is_numeric($start) && is_numeric($limit)){
            $str = "LIMIT {$start}, {$limit}";
        }
    
        return $this->db->query("
            SELECT 
                DISTINCT j.`property_id`,     
                j.`property_id`,
                j.`id` AS j_id,    
                j.`status` AS j_status,    
                j.`job_type` AS j_type,    
                p.`address_1` AS p_address1, 
                p.`address_2` AS p_address2, 
                p.`address_3` AS p_address3, 
                p.`state` AS p_state, 
                p.`postcode` AS p_postcode,
                a.`agency_id`,
                a.`agency_name`,
                ps.service
            FROM `jobs` AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `property_services` AS ps ON j.`property_id` = ps.`property_id`
            WHERE p.is_unserviced = 1
            AND p.`deleted` = 0
            AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
            AND p.is_sales = 0
            AND ps.service = 1
            GROUP BY j.`property_id`
            ORDER BY j.`property_id` DESC
            {$str}
        ");
    
    }

    public function mark_unserviced_property_for_cron(){

        ini_set('max_execution_time', 900); 
        $excludeIds = $this->getExcludedProperties();
        $prop = $this->getUnservicedProperties($excludeIds,'','');

        $p_arr = array();
        foreach( $prop->result_array() as $p){
            $p_arr[] = $p['property_id'];
        }

        //new function get property where service = 1 and 0 jobs > gherx
        $excludeIds_2 = $this->unservice2_get_property_with_jobs();
        $prop_2 = $this->unservice2_get_property_without_jobs($excludeIds_2);
        $p_arr_2 = array();
        foreach( $prop_2->result_array() as $p_2){
            $p_arr_2[] = $p_2['property_id'];
        }
        //new function get property where service = 1 and 0 jobs end > gherx
        
        $arr_merge = array_merge($p_arr, $p_arr_2); //combine two array
        $arr_merge_unique = array_unique($arr_merge); //removed duplicate values from array

        if (empty($arr_merge_unique)) {
            $unservice_prop = 0;
        }else {
            $unservice_prop = implode(",",$arr_merge_unique);
        }

        $unmarkProp = $this->unamark_unservice_properties_for_cron();
        $markProp = $this->update_unservice_properties_for_cron($unservice_prop);

        return $markProp;
    }

    public function get_unservice_properties_for_cron(){
        
        return $this->db->query("
            SELECT DISTINCT j.`property_id`,           
                p.`address_1` AS p_address1, 
                p.`address_2` AS p_address2, 
                p.`address_3` AS p_address3, 
                p.`state` AS p_state, 
                p.`postcode` AS p_postcode,
                a.`agency_id`,
                a.`agency_name`
            FROM `jobs` AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            INNER JOIN `property_services` AS ps ON ( j.`property_id` = ps.`property_id`
            AND j.`service` = ps.`alarm_job_type_id` )
            WHERE ps.`service` = 1
            AND a.`country_id` = {$this->config->item('country')}
            AND ( 
                j.`status` = 'Pending'
                OR j.`date` IS NULL
                OR j.`date` = '0000-00-00'
                OR j.`job_type` = 'Once-off'
                OR ( j.`job_type` = '240v Rebook' || j.`is_eo` == 1 )
                OR ( j.`date` >= '". date("Y-m-d",strtotime("-1 year"))."' AND j.`job_type` = 'Yearly Maintenance' )            
            )
            AND p.`is_unserviced` != 1
            AND p.`deleted` != 1
            LIMIT 0, 1000
        ");
    
    }

    public function get_nsw_act_job_with_tbb($start,$limit){
        
        // paginate
        if(is_numeric($start) && is_numeric($limit)){
            $str = "LIMIT {$start}, {$limit}";
        }

        $job_status = "To Be Booked";
        return $this->db->query("
            SELECT MAX(j.`id`) AS id,
                p.`property_id`,
                p.`address_1` AS p_address1, 
                p.`address_2` AS p_address2, 
                p.`address_3` AS p_address3, 
                p.`state` AS p_state, 
                p.`postcode` AS p_postcode,
                a.`agency_id`,
                a.`agency_name`,
                p.`test_date`,
                (365 - DATEDIFF(NOW(), p.`test_date`)) AS deadline
            FROM `jobs` AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE a.`country_id` = {$this->config->item('country')}
            AND (p.`state` = 'NSW' OR p.`state` = 'ACT')
            AND j.`status` = '{$job_status}'
            AND p.`test_date` IS NOT NULL
            AND p.`test_date` != '0000-00-00'
            AND j.`date` IS NOT NULL
            AND j.`date` != '0000-00-00'
            AND p.`deleted` = 0
            AND (365 - DATEDIFF(NOW(), p.`test_date`)) <= 15
            GROUP BY p.`property_id`
            {$str}
        ");
    
    }

    /**
     * Last completed YM 
     */
    public function getGetLastJob($prop_id){
        $sql = $this->db->query("
            SELECT MAX(j.`date`) AS jdate
            FROM `jobs` AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE j.`property_id` = {$prop_id}		
            AND j.`job_type` = 'Yearly Maintenance'
            AND j.`status` = 'Completed'
            AND a.`country_id` = {$this->config->item('country')}
            GROUP BY j.`property_id`
        ");
        $row = $sql->row_array();
        return $row['jdate'];
    }

    /**
     * Get last job date
     * return date
     */
    public function get_last_job_date($property_id) {
        $this->db->select("j.id, j.date");
        $this->db->from("jobs j");
        $this->db->join('property as p', 'p.property_id = j.property_id', 'left');
        $this->db->join('agency as a', 'a.agency_id = p.agency_id', 'left');
        $this->db->where('j.property_id', $property_id);
        $this->db->where('a.country_id', $this->config->item('country'));
        $this->db->where('j.status', 'Completed');
        $this->db->where('j.del_job', 0);
        $this->db->order_by('j.date', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        
        $date_row = $query->row_array();
        return $date_row['date'];
    }

    /**
     * Get recent created job by property id
     */
    public function get_recent_created_job($prop_id){

        $this->db->select('j.id,j.created,j.date,j.status,j.job_type,p.property_id as p_prop_id');
        $this->db->from('jobs as j');
        $this->db->join('property as p','p.property_id = j.property_id','left');
        $this->db->join('agency as a','a.agency_id = p.agency_id','left');
        $this->db->where('a.`country_id`', $this->config->item('country'));
        $this->db->where('j.`del_job`', 0);
        $this->db->where('j.status!=','Completed');
        $this->db->where('j.`property_id`', $prop_id);
        $this->db->order_by('j.created','DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        return $query;

    }


    public function check_postcode_exist_on_list($postcode){
        
        if( $postcode != '' ){

            // add comma and start and end of a comma separated values
           /* $sql_str = "
            SELECT COUNT(`postcode_region_id`) AS pr_count
            FROM `postcode_regions` 
            WHERE CONCAT(',', `postcode_region_postcodes`, ',') LIKE '%,{$postcode},%'
            AND `deleted` = 0            
            ";
            $sql = $this->db->query($sql_str);
            $row = $sql->row();*/

            ##new table
            $tt_where = "pc.postcode = $postcode";
            $this->db->select('COUNT(sr.sub_region_id) as pc_count');
            $this->db->from('postcode as pc');
            $this->db->join('sub_regions as sr','sr.sub_region_id = pc.sub_region_id','left');
            $this->db->where($tt_where);
            $q = $this->db->get();

            if( $q ){

                $row = $q->row();
            
                if( $row->pc_count > 0 ){ // found postcode
                    return true;
                }else{
                    return false;
                } 

            }else{

                return false;

            }             

        }                       
        
    }

    /**
     * Get properties active jobs
     * @params property_id
     * return comma separated job id with link
     */
    public function get_properties_active_jobs_for_overdue_nsw_jobs($prop_id){

        if($prop_id && $prop_id > 0){ //property id not empty
            
            $this->db->select('id,job_type,status');
            $this->db->from('jobs');
            $this->db->where('property_id', $prop_id);
            $this->db->group_start();
            $this->db->where('status!=','Completed');
            $this->db->where('status!=','Merged Certificates');
            $this->db->where('status!=','Pre Completion');
            $this->db->where('status!=','Cancelled');
            $this->db->where('status!=','DHA');
            $this->db->group_end();
            $this->db->where('del_job',0);
            $query = $this->db->get();
            foreach($query->result_array() as $row){
                $aw_arr[] = $this->gherxlib->crmLink('vjd',$row['id'],$row['id']);
                
            }
            return implode(',',$aw_arr);

        }

    }

    public function unservice2_get_property_with_jobs(){
        $sel = "DISTINCT(j.property_id)";
        $this->db->select($sel);
        $this->db->from('jobs as j');
        $this->db->join('property as p', 'p.property_id = j.property_id', 'left');
        $this->db->where('j.del_job',0);
        $this->db->where('p.deleted',0);
        $this->db->where('( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )');
        $this->db->where('j.property_id>',0);
        $query = $this->db->get();
        return $query;
    }

    public function unservice2_get_property_without_jobs($excludeIds){

        $p_arr = array();
        foreach( $excludeIds->result_array() as $p){
            $p_arr[] = $p['property_id'];
        }
        $ex_prop = implode(",",$p_arr);

        $tt_where = "(p.is_nlm!=1 OR p.is_nlm IS NULL)"; //Gherx > exclude NLM 

        $sel = "DISTINCT(p.property_id)";
        $this->db->select($sel);
        $this->db->from('property as p');
        $this->db->join('agency as a', 'a.agency_id = p.agency_id', 'left');
        $this->db->join('property_services as ps', 'ps.property_id = p.property_id', 'inner');
        $this->db->where_not_in('p.property_id', $ex_prop, false);
        $this->db->where('ps.service',1);
        $this->db->where('p.deleted',0);
        $this->db->where('( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )');
        $this->db->where('p.agency_deleted',0);
        $this->db->where('a.status','active');
        $this->db->where('a.deleted',0);
        $this->db->where($tt_where);
        $this->db->where('a.country_id',$this->config->item('country'));
        $query = $this->db->get();
        return $query;

    } 

    public function getMissedJobs($params){
        if ($params['sel_query'] && !empty($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('jobs as j');
        $this->db->join('`alarm_job_type` AS ajt', 'j.`service` = ajt.`id`', 'left');
        $this->db->join('property as p','p.property_id = j.property_id','left');
        $this->db->join('agency as a','a.agency_id = p.agency_id','left');
        $this->db->join('agency_priority as aght', 'a.agency_id = aght.agency_id', 'left');
        $this->db->where('j.status','Booked');
        $this->db->group_start();
        $this->db->where('j.date <', date('Y-m-d'));
        $this->db->or_where('j.date =', null);
        $this->db->or_where('j.date =', '');
        $this->db->group_end();
        $this->db->where('p.deleted', 0);
        $this->db->where("( p.is_nlm = 0 OR p.is_nlm IS NULL )");
        $this->db->where('a.status','active');
        $this->db->where('j.del_job',0);
        $this->db->where('a.country_id',$this->config->item('country'));

        if ( $params['state_filter'] !='' ) {
            $this->db->where('p.state', $params['state_filter']);
        }
        if ( $params['job_type_filter'] !='' ) {
           $this->db->where('j.job_type', $params['job_type_filter']);
       }

       if ( $params['agency_filter'] !='' ) {
           $this->db->where('a.agency_id', $params['agency_filter']);
       }

       $this->db->group_by('j.property_id');
        
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

    public function get_recent_non_completed_job_per_property($property_id_arr_unique_imp) 
    {
        // get recent non completed job per property
        $recent_job_sql_str  = "
         SELECT 
             j.id AS jid,
             j.status AS jstatus,
             j.created AS jcreated,
             p.`property_id`
         FROM jobs as j
         LEFT JOIN property as p ON  j.property_id = p.property_id
         LEFT JOIN agency as a ON p.agency_id = a.agency_id 
         INNER JOIN (
             SELECT p_inner.`property_id`, MAX(j_inner.`created`) AS MaxDate
             FROM jobs as j_inner
             LEFT JOIN property as p_inner ON  j_inner.property_id = p_inner.property_id
             LEFT JOIN agency as a_inner ON p_inner.agency_id = a_inner.agency_id   
             WHERE j_inner.`del_job` = 0
             AND p_inner.`deleted` = 0
             AND ( 
                 p_inner.`is_nlm` = 0 OR 
                 p_inner.`is_nlm` IS NULL 
             )
             AND a_inner.`status` = 'active'
             AND a_inner.`deleted` = 0
             AND j_inner.`status` != 'Completed'
             AND p_inner.`property_id` IN ({$property_id_arr_unique_imp})
             GROUP BY p_inner.`property_id`  
         ) AS inner_join_prop ON( p.`property_id` = inner_join_prop.`property_id` AND j.`created` = inner_join_prop.MaxDate )
         WHERE j.`del_job` = 0
         AND p.`deleted` = 0
         AND ( 
             p.`is_nlm` = 0 OR 
             p.`is_nlm` IS NULL 
         )
         AND a.`status` = 'active'
         AND a.`deleted` = 0    
         AND j.status != 'Completed'    
         AND p.`property_id` IN ({$property_id_arr_unique_imp})
         ";

         return $this->db->query($recent_job_sql_str)->result_array();
    }

    public function get_active_properties_without_jobs($country_id, $agency_filter, $offset, $per_page)
    {
        $sql_str .= "
            SELECT 
                p_main.`property_id`, 
                p_main.`address_1`,
                p_main.`address_2`,
                p_main.`address_3`,
                p_main.`state`,
                p_main.`postcode`,
                
                a_main.`agency_id`,
                a_main.`agency_name`,
                aght.priority,
                apmd.abbreviation
            FROM `property` AS p_main
            LEFT JOIN  `property_services` AS ps_main ON p_main.`property_id` = ps_main.`property_id` 
            LEFT JOIN `agency` AS a_main ON p_main.`agency_id` = a_main.`agency_id`
            LEFT JOIN `agency_priority` as aght ON a_main.`agency_id` = aght.`agency_id`
            LEFT JOIN `agency_priority_marker_definition` as apmd ON aght.`priority` = apmd.`priority`
            WHERE p_main.`property_id` NOT IN(
                SELECT DISTINCT(p.`property_id`)
                FROM `jobs` AS j
                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`	
                WHERE p.`deleted` = 0
                AND a.`status` = 'active'
                AND j.`del_job` = 0	
                AND a.`country_id` = {$country_id}
            )
            AND p_main.`deleted` = 0
            AND ( p_main.`is_nlm` = 0 OR p_main.`is_nlm` IS NULL )
            AND a_main.`status` = 'active'       
            AND a_main.`country_id` = {$country_id}
            AND ps_main.`service` = 1
        ";

        if (!empty($agency_filter)) {
            $sql_str .= "AND a_main.`agency_id` = {$agency_filter}";
        } else {
            $sql_str .= "LIMIT {$offset}, {$per_page}"; 
        }

        return $this->db->query($sql_str)->result();
    }

    public function get_total_rows_property_count($country_id, $agency_filter)
    {
        // total row
        $sql_str .= "
            SELECT COUNT(p_main.`property_id`) AS p_count
            FROM `property` AS p_main
            LEFT JOIN  `property_services` AS ps_main ON p_main.`property_id` = ps_main.`property_id` 
            LEFT JOIN `agency` AS a_main ON p_main.`agency_id` = a_main.`agency_id`
            WHERE p_main.`property_id` NOT IN(

                SELECT DISTINCT(p.`property_id`)
                FROM `jobs` AS j
                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`	
                WHERE p.`deleted` = 0
                AND a.`status` = 'active'
                AND j.`del_job` = 0	
                AND a.`country_id` = {$country_id}

            )
            AND p_main.`deleted` = 0
            AND a_main.`status` = 'active'
            AND a_main.`country_id` = {$country_id}
            AND ps_main.`service` = 1
        ";

        if (!empty($agency_filter)) {
            $sql_str .= "AND a_main.`agency_id` = {$agency_filter}";
        }

        return $this->db->query($sql_str)->row();
    }

    public function get_active_properties_without_jobs_count($country_id)
    {
        $sql_str = "
            SELECT COUNT(p_main.`property_id`) as property_count
            FROM `property` AS p_main
            LEFT JOIN  `property_services` AS ps_main ON p_main.`property_id` = ps_main.`property_id` 
            LEFT JOIN `agency` AS a_main ON p_main.`agency_id` = a_main.`agency_id`
            LEFT JOIN `agency_priority` as aght ON a_main.`agency_id` = aght.`agency_id`
            WHERE p_main.`property_id` NOT IN(
                SELECT DISTINCT(p.`property_id`)
                FROM `jobs` AS j
                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`	
                WHERE p.`deleted` = 0
                AND a.`status` = 'active'
                AND j.`del_job` = 0	
                AND a.`country_id` = {$country_id}
            )
            AND p_main.`deleted` = 0
            AND ( p_main.`is_nlm` = 0 OR p_main.`is_nlm` IS NULL )
            AND a_main.`status` = 'active'       
            AND a_main.`country_id` = {$country_id}
            AND ps_main.`service` = 1
        ";

        $result = $this->db->query($sql_str)->row();
        
        return $result->property_count;
    }

    public function update_intentionally_hidden_active_properties($params)
    {   
        if ($params['property_id'] == null) {
            return false;
        }

        $timestamp = date("Y-m-d H:i:s");
        $rresults = $this->check_if_job_property_id_exists($params['property_id']);

        if ($rresults > 0) {
            $data = array(
                'hidden'              => $params['is_acknowledge'],
                'added_by'            => $params['staff_id'],
                'date_modified'       => $timestamp
            );

            $this->db->where('property_id', $params['property_id'] );
            $this->db->update('intentionally_hidden_active_properties', $data);
        } else {
            $data = array(
                'property_id'         => $params['property_id'],
                'hidden'              => $params['is_acknowledge'],
                'added_by'            => $params['staff_id'],
                'date_created'        => $timestamp
            );

            $this->db->where('property_id', $params['property_id'] );
            $this->db->insert('intentionally_hidden_active_properties', $data);
        }
        
        return true;
    }

    public function check_if_job_property_id_exists($id)
    {
        $this->db->select("*")
        ->from("intentionally_hidden_active_properties")
        ->where('property_id', $id);

        $result = $this->db->get()->row();

        return $result;
    }

    public function get_job_last_contact($limit, $start, $order_by = '', $sort = '', $state = '',$agency_filter = NULL, $agency_priority_filter = NULL) 
    {
        $date_delay = date('Y-m-d', strtotime('-14 days'));

        $tt_q = $this->db->query(
            "SELECT `p`.`property_id`,p.postpone_due_job,MAX( jl.`eventdate` ) AS last_contact
            FROM `job_log` AS `jl`
            LEFT JOIN `jobs` AS `j` ON jl.`job_id` = j.`id`
            LEFT JOIN `last_contact_comments` AS `lcc` ON j.`id` = lcc.`job_id`
            LEFT JOIN `property` AS `p` ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS `a` ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `agency_priority` AS `aght` ON a.`agency_id` = aght.`agency_id`
            LEFT JOIN `staff_accounts` AS `sa` ON sa.`StaffID`=j.`assigned_tech`
            WHERE `j`.`status` = 'To Be Booked'
                        AND `p`.`deleted` =0
                        AND (`p`.`is_nlm` =0 OR `p`.`is_nlm` IS NULL)
                        AND `a`.`status` = 'active'
                        AND `j`.`del_job` =0
                        AND `a`.`country_id` = {$this->config->item('country')}	
                        AND j.`status` != 'On Hold'
                        AND ( jl.`contact_type` = 'Phone Call' OR jl.`contact_type` = 'SMS sent' OR jl.`contact_type` = 'TY SMS Sent' 
                            OR jl.`contact_type` = 'SMS Entry Notice' OR jl.`contact_type` = 'Email Entry Notice' 
                            OR jl.`contact_type` = 'Email Template'
                            )
            GROUP BY `jl`.`job_id`
            HAVING `last_contact` <=  '{$date_delay}'
            UNION
            SELECT `p`.`property_id`,p.postpone_due_job,MAX( jl.`created_date` ) AS last_contact
            FROM `logs` AS `jl`
            LEFT JOIN `jobs` AS `j` ON jl.`job_id` = j.`id`
            LEFT JOIN `last_contact_comments` AS `lcc` ON j.`id` = lcc.`job_id`
            LEFT JOIN `property` AS `p` ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS `a` ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `agency_priority` AS `aght` ON a.`agency_id` = aght.`agency_id`
            LEFT JOIN `staff_accounts` AS `sa` ON sa.`StaffID`=j.`assigned_tech`
            WHERE `j`.`status` = 'To Be Booked'
                        AND `p`.`deleted` =0
                        AND (`p`.`is_nlm` =0 OR `p`.`is_nlm` IS NULL)
                        AND `a`.`status` = 'active'
                        AND `j`.`del_job` =0
                        AND `a`.`country_id` = {$this->config->item('country')}	
                        AND j.`status` != 'On Hold'
                        AND jl.title IN (54,57,78,40,53,60)
            GROUP BY `jl`.`job_id`
            HAVING `last_contact` <=  '{$date_delay}'
            "
        );

        // $tt_q->result_array();
        // echo "<pre>";
        // echo $this->db->last_query();
        // exit;

        $ttmo = array();
        foreach( $tt_q->result_array() as $tt_row ){
            $postpone_due_job_date = date('Y-m-d', strtotime($tt_row['postpone_due_job']));
            $date_now = date('Y-m-d');
            if ($postpone_due_job_date <= $date_now) {
                // do nothing
            } else {
                $ttmo[] = intval($tt_row['property_id']);
            }
        }

        if( !empty($ttmo) ){
            $ttmo_implode = implode(", ", $ttmo);
            $property_id_not_in = "AND p.property_id NOT IN(".$ttmo_implode.")";
        }
        $filter_str = '';
        if ($state != '') {
            $filter_str = ' AND p.`state` = "'.$this->db->escape_str($state).'" ';
        }
        $agency = '';
        if ($agency_filter != '') {
            $agency = ' AND a.`agency_id` = "'.$this->db->escape_str($agency_filter).'" ';
        }

        $order = '';
        if ($sort != "" && $order_by != "") {
            // $this->db->order_by($order_by, $sort);
            $order = "ORDER BY $order_by $sort";
        }
        $limit = '';
        if (is_numeric($start) && is_numeric($limit)) {
            // $this->db->limit($start, $limit);
            $limit = "LIMIT $start, $limit";
        }

        $agency_priority = ''; 
        if ($agency_priority_filter != "") {
            $agency_priority = "AND aght.priority = {$agency_priority_filter}";
        }

        $data = $this->db->query(
            "SELECT DISTINCT
                (
                    CASE 
                        WHEN MAX(dd.last_contact) > MAX(dd.newdate) THEN MAX(dd.last_contact)
                        ELSE MAX(dd.newdate)
                    END
                ) as last_contact_v2,
                MAX(dd.newdate) as newdate_v2,
                dd.*
            FROM 
                (
              SELECT 
                MAX(DATE_FORMAT(CONCAT(jl.`eventdate`, ' ', jl.`eventtime`), '%Y-%m-%d %H:%i')) as newdate,
                MAX(DATE_FORMAT(jl.`eventdate`, '%Y-%m-%d %H:%i')) AS last_contact,
                j.`id` AS jid,
                j.`created` AS jcreated, 
                j.`date` AS jdate, 
                j.`job_type`, 
                j.`service` AS jservice, 
                j.`job_price`,
                j.`comments`,

                lcc.`comments` AS lcc_comments,

                p.`property_id`,
                p.`address_1` AS p_address_1,
                p.`address_2` AS p_address_2,
                p.`address_3` AS p_address_3,
                p.`state` AS p_state,
                
                a.`agency_id`,
                a.`agency_name`,
                aght.priority,
                apmd.abbreviation,
                                
                sa.FirstName,
                sa.LastName
            FROM `job_log` AS `jl`
            LEFT JOIN `jobs` AS `j` ON jl.`job_id` = j.`id`
            LEFT JOIN `last_contact_comments` AS `lcc` ON j.`id` = lcc.`job_id`
            LEFT JOIN `property` AS `p` ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS `a` ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `agency_priority` AS `aght` ON a.`agency_id` = aght.`agency_id`
            LEFT JOIN `agency_priority_marker_definition` AS `apmd` ON aght.`priority` = apmd.`priority`
            LEFT JOIN `staff_accounts` AS `sa` ON sa.`StaffID`=j.`assigned_tech`
            WHERE `j`.`status` = 'To Be Booked'
                        AND `p`.`deleted` =0
                        AND (`p`.`is_nlm` =0 OR `p`.`is_nlm` IS NULL)
                        AND `a`.`status` = 'active'
                        AND `j`.`del_job` =0
                        AND `a`.`country_id` = {$this->config->item('country')}	
                        AND j.`status` != 'On Hold'
                        AND ( jl.`contact_type` = 'Phone Call' OR jl.`contact_type` = 'SMS sent' OR jl.`contact_type` = 'TY SMS Sent' 
                            OR jl.`contact_type` = 'SMS Entry Notice' OR jl.`contact_type` = 'Email Entry Notice' 
                            OR jl.`contact_type` = 'Email Template'
                            )
                        {$filter_str}
                        {$agency}
                        {$property_id_not_in}
                        {$agency_priority}
            GROUP BY `jl`.`job_id`
            HAVING `last_contact` <=  '{$date_delay}'
            UNION
            SELECT 
                MAX(DATE_FORMAT(jl.`created_date`, '%Y-%m-%d %H:%i')) as newdate,
                MAX( DATE_FORMAT(jl.`created_date`, '%Y-%m-%d %H:%i') ) AS last_contact,
                j.`id` AS jid,
                j.`created` AS jcreated, 
                j.`date` AS jdate, 
                j.`job_type`, 
                j.`service` AS jservice, 
                j.`job_price`,
                j.`comments`,

                lcc.`comments` AS lcc_comments,

                p.`property_id`,
                p.`address_1` AS p_address_1,
                p.`address_2` AS p_address_2,
                p.`address_3` AS p_address_3,
                p.`state` AS p_state,
                
                a.`agency_id`,
                a.`agency_name`,
                aght.priority,
                apmd.abbreviation,
                                
                sa.FirstName,
                sa.LastName
            FROM `logs` AS `jl`
            LEFT JOIN `jobs` AS `j` ON jl.`job_id` = j.`id`
            LEFT JOIN `last_contact_comments` AS `lcc` ON j.`id` = lcc.`job_id`
            LEFT JOIN `property` AS `p` ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS `a` ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `agency_priority` AS `aght` ON a.`agency_id` = aght.`agency_id`
            LEFT JOIN `agency_priority_marker_definition` AS `apmd` ON aght.`priority` = apmd.`priority`
            LEFT JOIN `staff_accounts` AS `sa` ON sa.`StaffID`=j.`assigned_tech`
            WHERE `j`.`status` = 'To Be Booked'
                        AND `p`.`deleted` =0
                        AND (`p`.`is_nlm` =0 OR `p`.`is_nlm` IS NULL)
                        AND `a`.`status` = 'active'
                        AND `j`.`del_job` =0
                        AND `a`.`country_id` = {$this->config->item('country')}	
                        AND j.`status` != 'On Hold'
                        AND jl.title IN (54,57,78,40,53,60)
                        {$filter_str}
                        {$agency}
                        {$property_id_not_in}
                        {$agency_priority}
            GROUP BY `jl`.`job_id`
            HAVING `last_contact` <=  '{$date_delay}'
            {$order}
            {$limit}) as dd
                GROUP BY dd.jid
            "
        )->result_array();

        return $data;
    }

}
