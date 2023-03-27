<?php

class Reports_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
    }

    /**
     * GET SALESAGENCYLOGS
     * @params offset
     * @params limit
     * @params date from
     * @params date to
     * @parms salesrep
     * @parms state 
     */
    function getSalesRepAgencyLogs($params){

        if($params['sel_query'] && !empty($params['sel_query'])){
            $sel_query = $params['sel_query'];
        }else{
            $sel_query = '*';
        }

        /*$this->db->select($sel_query);
        $this->db->from('agency_event_log as ael');
        $this->db->join('agency as a','a.agency_id = ael.agency_id',"left");
        $this->db->join('staff_accounts as sa','sa.StaffID = ael.staff_id','left');
        $this->db->where('sa.deleted',0);
        $this->db->where('sa.active',1);
        $this->db->where('sa.ClassID',5);
        $this->db->where('ael.contact_type !=','Agency Update');
        $this->db->where('a.country_id ',$this->config->item('country'));*/

        $this->db->select($sel_query);
        $this->db->from('sales_report as ael');
        $this->db->join('agency as a','a.agency_id = ael.agency_id',"left");
        $this->db->join('agency_priority as aght', 'a.agency_id = aght.agency_id', 'left');
        $this->db->join('agency_priority_marker_definition as apmd', 'aght.priority = apmd.priority', 'left');
        $this->db->join('staff_accounts as sa','sa.StaffID = ael.staff_id','left');
        $this->db->join('main_log_type as mlt','mlt.main_log_type_id=ael.contact_type','left');
        $this->db->where('sa.deleted',0);
        $this->db->where('sa.active',1);
        $this->db->where('sa.ClassID',5);
        $this->db->where('a.country_id ',$this->config->item('country'));
        $this->db->where('a.deleted ', 0);

        //date from and to filter
        if( !empty($params['date_from_filter']) && !empty($params['date_to_filter']) ){
            $from2 = date('Y-m-d', strtotime( str_replace( "/", "-", $params['date_from_filter'] ) ) );
            $to2 = date('Y-m-d', strtotime( str_replace( "/", "-", $params['date_to_filter'] ) ) );
            
          /* $this->db->where('ael.eventdate >=', $from2);
           $this->db->where('ael.eventdate <=', $to2);*/

           $this->db->where('ael.date >=', $from2);
           $this->db->where('ael.date <=', $to2);
        }

        //sales rep filter
        if($params['salesrep_filter'] && !empty($params['salesrep_filter'])){
            $this->db->where('ael.staff_id',$params['salesrep_filter']);
        }

        //state filter
        if( $params['state_filter'] && !empty('state_filter') ){
            $this->db->where('a.state', $params['state_filter']);
        }

         // limit/offset
		if( isset($params['limit']) && $params['limit'] > 0 ){
			$this->db->limit( $params['limit'], $params['offset']);
		}	
        
        $query = $this->db->get();

        return $query;
       

    }

    function getLeaveReport($params){

        if($params['sel_query'] && !empty($params['sel_query'])){
            $sel_query = $params['sel_query'];
        }else{
            $sel_query = '*';
        }


        $this->db->select($sel_query);
        $this->db->from('calendar as c');
        $this->db->join('staff_accounts as sa','sa.StaffID = c.staff_id','left');
        $this->db->where('sa.deleted',0);
        $this->db->where('c.marked_as_leave',1);
      
        if($params['state_filter']=='1'){
            $this->db->where('sa.active',1);
        }elseif($params['state_filter']=='0'){
            $this->db->where('sa.active',0);
        }
      

        //date from and to filter
        if( !empty($params['date_from_filter']) && !empty($params['date_to_filter']) ){
            $from2 = date('Y-m-d', strtotime( str_replace( "/", "-", $params['date_from_filter'] ) ) );
            $to2 = date('Y-m-d', strtotime( str_replace( "/", "-", $params['date_to_filter'] ) ) );
            
      

           $this->db->where('c.date_start >=', $from2);
           $this->db->where('c.date_start <=', $to2);
        }

    
        if( $params['staff_filter'] && !empty('staff_filter') ){
            $this->db->where('sa.StaffID', $params['staff_filter']);
        }

       
        
        $query = $this->db->get();

        return $query;
       

    }

    

    public function getDynamicServices($sales_ic = NULL){

       $query =  $this->db->get_where('alarm_job_type',array('active'=>1));
       return $query;

    }

    /**
     * DISTINCT agency log (contact type)
     * 
     * AL Note: To be deleted fetched data from old agency logs > new function get_sales_report > from sales_report table
     */
    public function getAgencyLogs(){

        //$ignore = array('Agency Changed to Target','Conference','Phone Call - Accounts','Email - Accounts','Other - Accounts','Agency Update');

        // allowed agency log titles to display
        $allowed_log_titles_arr = 
        array(
            'Cold Call',
            'Cold Call In',
            'Conference',
            'E-mail',            
            'Follow up',
            'Happy Call',
            'Mailout',
            'Meeting',
            'Other',
            'Pack Sent',
            'Phone Call',            
            'Pop In'
        );        
        
        $this->db->distinct();
        $this->db->select("contact_type");
        $this->db->from('agency_event_log');
        //$this->db->where_not_in('contact_type',$ignore);
        $this->db->where_in('contact_type',$allowed_log_titles_arr);
        $query = $this->db->get();
        return $query;

    }

    /**
     * // distint sales rep
     */

     public function distinct_salesrep($from,$to, $sales_ic = NULL){

        $str = "";					
        if( $from!='all' && $to!='all' ){
            $from2 = date("Y-m-d",strtotime(str_replace("/","-",$from)));
            $to2 = date("Y-m-d",strtotime(str_replace("/","-",$to)));
            $str = "
                AND CAST(ps.`status_changed` AS DATE) BETWEEN '{$from2}' AND '{$to2}'
                AND (
                    p.`is_nlm` IS NULL 
                    OR p.`is_nlm` = 0
                )
            ";
        }

        $sales_ic_Str = "";
        $join_jobs = "";
        if(is_numeric($sales_ic) && $sales_ic!="" && $sales_ic>0){ //p.is_sales=1 only
            $sales_ic_Str = " 
            AND p.is_sales = 1 AND j.status='Completed'
            ";

            $join_jobs = "
            LEFT JOIN `jobs` AS j ON p.`property_id` = j.`property_id`
            ";
        }

        $sr_sql = $this->db->query("
            SELECT DISTINCT a.`salesrep` , sa.`FirstName` , sa.`LastName`
            FROM `property_services` AS ps            
            LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `staff_accounts` AS sa ON sa.`StaffID` = a.`salesrep`
            {$join_jobs}
            WHERE ps.`service` = 1
            AND a.`country_id` = {$this->config->item('country')}
            {$str}
            {$sales_ic_Str}
            ORDER BY sa.`FirstName` ASC, sa.`LastName` ASC
        ");

        return $sr_sql;

     }


     function getAgencyLogsCount($staff_id,$contact_type,$country_id,$from,$to){
	
        $str = '';
        if( ($from!='' && $to!='') && ( $from!='all' && $to!='all' ) ){
            $from2 = date("Y-m-d",strtotime(str_replace("/","-",$from)));
            $to2 = date("Y-m-d",strtotime(str_replace("/","-",$to)));
            $str = " AND ael.`eventdate` BETWEEN '{$from2}' AND '{$to2}' ";
        }
        
        
        $sql_str = "
            SELECT count(ael.`agency_event_log_id`) AS jcount
            FROM `agency_event_log` AS ael
            LEFT JOIN `agency` AS a ON ael.`agency_id` = a.`agency_id`
            WHERE ael.`contact_type` = '{$contact_type}'
            AND ael.`staff_id` = {$staff_id}
            AND a.`country_id` = {$country_id}
            {$str}
        ";
        $sql = $this->db->query($sql_str);
        $row = $sql->row_array();
    
        return $row['jcount'];
        
        
    }

    /**
     * GET Sales Count or Property
     */
    function get_num_services( $salesrep,$ajt,$from,$to,$country_id,$exclude_dha = NULL,$sales_ic = NULL ){
					
        $str = "";					
        if( $from!='all' && $to!='all' ){
            $from2 = date("Y-m-d",strtotime(str_replace("/","-",$from)));
            $to2 = date("Y-m-d",strtotime(str_replace("/","-",$to)));
            $str = "
                AND CAST(ps.`status_changed` AS DATE) BETWEEN '{$from2}' AND '{$to2}'
                AND (
                    p.`is_nlm` IS NULL 
                    OR p.`is_nlm` = 0
                )
                AND p.deleted = 0
            ";
        }

        $dhaStr = "";
        if(is_numeric($exclude_dha) && $exclude_dha!="" && $exclude_dha>0){
            $dhaStr = " 
            AND a.franchise_groups_id!=14
            ";
        }

        $sales_ic_Str = "";
        if(is_numeric($sales_ic) && $sales_ic!="" && $sales_ic>0){ //p.is_sales=1 and IC job only
            $sales_ic_Str = " 
            AND p.is_sales = 1 AND j.job_type = 'IC Upgrade' AND j.status = 'Completed'
            ";

            $join_job_ic = "LEFT JOIN `jobs` AS j ON p.`property_id` = j.`property_id`";
        }

        // sales commission version on page parameter overrides global settings
		$sales_commission_ver = ( $this->input->get_post('ver') != '' )?$this->input->get_post('ver'):$this->config->item('sales_commission_ver');

        // sales commission version switch
        $sales_commission_str =  null;
		if( $sales_commission_ver == 'new' ){
			$sales_commission_str = 'AND ps.`is_payable` = 1';
		}else{
			$sales_commission_str = 'AND ps.`service` = 1';
		}
        
        $sql = "
            SELECT COUNT(ps.`property_services_id`) AS p_count
            FROM `property_services` AS ps
            LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            {$join_job_ic}
            WHERE a.`salesrep` ={$salesrep}
            AND ps.`alarm_job_type_id` ={$ajt}            
            AND a.`country_id` = {$country_id}
            {$sales_commission_str}
            {$str}
            {$dhaStr}
            {$sales_ic_Str}
        ";
       
        return $this->db->query($sql);
        
    }


    public function getTechCompletedJobs($from_date, $to_date, $country_id){
        
        if( $from_date!="" && $to_date!="" ){
            $from_date = date('Y-m-d',strtotime(str_replace('/','-',$from_date)));
            $to_date = date('Y-m-d',strtotime(str_replace('/','-',$to_date)));
        }else{
            $from_date = date('Y-m-d');
            $to_date = date('Y-m-d');
        }

        $sr_sql = $this->db->query("
            SELECT COUNT(j.`id`) AS num_jobs, j.`assigned_tech`, sa.`StaffID`, sa.FirstName, sa.LastName 
            FROM jobs AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
            WHERE j.`date` BETWEEN '{$from_date}' AND '{$to_date}'
            AND p.`deleted` =0
            AND a.`status` = 'active'
            AND j.`del_job` = 0
            AND a.`country_id` = {$country_id}   
            AND j.`status` = 'Completed'
            AND j.`assigned_tech` IS NOT NULL
            AND j.`assigned_tech` > 1
            GROUP BY j.`assigned_tech`
            ORDER BY sa.FirstName, sa.LastName 
        ");
        
        return $sr_sql->result_array();

     }

    public function jmissed_jobs($from_date,$to_date,$tech,$country_id){

        // if( $from_date!="" && $to_date!="" ){
        //     $from_date_str = date('Y-m-d',strtotime(str_replace('/','-',$from_date)));
        //     $to_date_str = date('Y-m-d',strtotime(str_replace('/','-',$to_date)));
        // }else{
        //     $from_date_str = date('Y-m-d');
        //     $to_date_str = date('Y-m-d');
        // }
        
        // if($tech!=""){
        //     $str .= " AND jl.`staff_id` = {$tech} ";
        // }
        
        // $str .= " ORDER BY ass_tech.`FirstName`, ass_tech.`LastName` ";

        // $jr_str = "";
        // $jr_sql = $this->db->query("
        //     SELECT * 
        //     FROM `job_reason` 
        //     ".(($reason!='')?" WHERE `name` = '{$reason}' ":"")."
        // ");
        // $jr = $jr_sql->result_array();
        // foreach ($jr as $val) {
        //     $jr_str .= ",'{$val['name']}', '{$val['name']} DK'";
        // }
        
        // $fr_filter = substr($jr_str,1);
        
        // $sql = $this->db->query("
        //     SELECT COUNT(j.id) AS num_jobs
        //     FROM  `job_log` AS jl
        //     LEFT JOIN `jobs` AS j ON jl.`job_id` = j.`id` 
        //     LEFT JOIN `job_reason` AS jr ON j.`job_reason_id` = jr.`job_reason_id` 
        //     LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
        //     LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
        //     LEFT JOIN `staff_accounts` AS ass_tech ON j.`assigned_tech` = ass_tech.`StaffID`
        //     LEFT JOIN `staff_accounts` AS sa ON jl.`staff_id` = sa.`StaffID`
        //     WHERE jl.`contact_type` 
        //     IN (
        //         {$fr_filter}
        //     )
        //     AND a.`status` =  'active'
        //     AND p.`deleted` =0
        //     AND j.`del_job` = 0
        //     AND jl.`eventdate` BETWEEN '{$from_date_str}' AND '{$to_date_str}'
        //     AND a.`country_id` ={$country_id}       
        //     {$str}
        // ");

        // $mj = $sql->result_array();
        // return $mj[0]['num_jobs'];
        
    }

    public function ra_dk_completed($from_date,$to_date,$tech,$country_id){
        
        if( $from_date!="" && $to_date!="" ){
            $from_date = date('Y-m-d',strtotime(str_replace('/','-',$from_date)));
            $to_date = date('Y-m-d',strtotime(str_replace('/','-',$to_date)));
        }else{
            $from_date = date('Y-m-d');
            $to_date = date('Y-m-d');
        }
        
        if($tech!=""){
            $str .= " AND j.`assigned_tech` = {$tech} ";
        }
        
        $sql = $this->db->query("
            SELECT COUNT(j.id) AS num_jobs 
            FROM jobs AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE j.`date` BETWEEN '{$from_date}' AND '{$to_date}'
            AND p.`deleted` =0
            AND a.`status` = 'active'
            AND j.`del_job` = 0
            AND j.`status` = 'Completed'
            AND j.`door_knock` = 1
            AND a.`country_id` = {$country_id}  
            {$str}
        ");

        $mj = $sql->result_array();
        return $mj[0]['num_jobs'];
    }

    public function getStaffBookedJobs($from_date,$to_date,$country_id){
        
        if( $from_date!="" && $to_date!="" ){
            $from_date = date('Y-m-d',strtotime(str_replace('/','-',$from_date)));
            $to_date = date('Y-m-d',strtotime(str_replace('/','-',$to_date)));
        }else{
            $from_date = date('Y-m-d');
            $to_date = date('Y-m-d');
        }
        
        $sql = $this->db->query("
            SELECT COUNT(j.id) AS num_jobs, sa.`StaffID`, sa.FirstName, sa.LastName  
            FROM jobs AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `staff_accounts` AS sa ON j.`booked_by` = sa.`StaffID`
            WHERE j.`date` >= '{$from_date}' 
            AND j.`date` <= '{$to_date}'
            AND p.`deleted` =0
            AND a.`status` = 'active'
            AND j.`del_job` = 0
            AND a.`country_id` = {$country_id}
            AND ( j.`booked_by` != 0 AND j.`booked_by` IS NOT NULL )
            GROUP BY j.`booked_by`
            ORDER BY sa.FirstName, sa.LastName  
        ");
        
        return $sql->result_array();
        
    }

    public function getStaffNoEnNoDKBookedJobs($from_date,$to_date,$staff_id,$country_id){
        
        if( $from_date!="" && $to_date!="" ){
            $from_date = date('Y-m-d',strtotime(str_replace('/','-',$from_date)));
            $to_date = date('Y-m-d',strtotime(str_replace('/','-',$to_date)));
        }else{
            $from_date = date('Y-m-d');
            $to_date = date('Y-m-d');
        }
        
        $sql = $this->db->query("
            SELECT COUNT(j.id) AS num_jobs
            FROM jobs AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `staff_accounts` AS sa ON j.`booked_by` = sa.`StaffID`
            WHERE j.`date` >= '{$from_date}' 
            AND j.`date` <= '{$to_date}'
            AND sa.`StaffID` = {$staff_id}
            AND p.`deleted` =0
            AND a.`status` = 'active'
            AND j.`del_job` = 0
            AND a.`country_id` = {$country_id}
            AND ( j.`booked_by` != 0 AND j.`booked_by` IS NOT NULL )
            AND j.`door_knock` = 0
            AND j.`job_entry_notice` = 0
        ");
        
        return $sql->result_array();
        
    }

    public function getStaffENBookedJobs($from_date,$to_date,$staff_id,$country_id){
        
        if( $from_date!="" && $to_date!="" ){
            $from_date = date('Y-m-d',strtotime(str_replace('/','-',$from_date)));
            $to_date = date('Y-m-d',strtotime(str_replace('/','-',$to_date)));
        }else{
            $from_date = date('Y-m-d');
            $to_date = date('Y-m-d');
        }
        
        $sql = $this->db->query("
            SELECT COUNT(j.id) AS num_jobs
            FROM jobs AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `staff_accounts` AS sa ON j.`booked_by` = sa.`StaffID`
            WHERE j.`date` >= '{$from_date}' 
            AND j.`date` <= '{$to_date}'
            AND sa.`StaffID` = {$staff_id}
            AND p.`deleted` =0
            AND a.`status` = 'active'
            AND j.`del_job` = 0
            AND a.`country_id` = {$country_id}
            AND ( j.`booked_by` != 0 AND j.`booked_by` IS NOT NULL )
            AND j.`job_entry_notice` = 1
        ");
        
        return $sql->result_array();
        
    }

        public function getStaffDKBookedJobs($from_date,$to_date,$staff_id,$country_id){
        
        if( $from_date!="" && $to_date!="" ){
            $from_date = date('Y-m-d',strtotime(str_replace('/','-',$from_date)));
            $to_date = date('Y-m-d',strtotime(str_replace('/','-',$to_date)));
        }else{
            $from_date = date('Y-m-d');
            $to_date = date('Y-m-d');
        }

        $sql_str = "
            SELECT COUNT(j.id) AS num_jobs
            FROM jobs AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `staff_accounts` AS sa ON j.`booked_by` = sa.`StaffID`
            WHERE j.`date` >= '{$from_date}' 
            AND j.`date` <= '{$to_date}'
            AND sa.`StaffID` = {$staff_id}
            AND p.`deleted` =0
            AND a.`status` = 'active'
            AND j.`del_job` = 0
            AND a.`country_id` = {$country_id}
            AND ( j.`booked_by` != 0 AND j.`booked_by` IS NOT NULL )
            AND j.`door_knock` = 1
        ";
        
        $sql = $this->db->query($sql_str);
        
        return $sql->result_array();
        
    }


    public function getSnapshotSalesRep(){
        //Shaquille smith access FOR AU = 2296 / NZ = 2259
        if($this->config->item('country')==1){ //AU
            $brad = 2165;
            $gavin = 2189;
            $shaquille = 2296; //Shaquille Smith
            $where = "(sa.`ClassID` = 5 OR sa.`StaffID` = {$brad} OR sa.`StaffID` = {$gavin} OR sa.`StaffID` = {$shaquille})";
        }else{ //NZ
            $gavin = 2202;
            $shaquille = 2259; //Shaquille Smith
            $where = "(sa.`ClassID` = 5 OR sa.`StaffID` = {$gavin} OR sa.`StaffID` = {$shaquille})";
        }

        $this->db->distinct('ss.sales_snapshot_sales_rep_id');
        $this->db->select('ss.sales_snapshot_sales_rep_id, sa.FirstName AS first_name, sa.LastName AS last_name');
        $this->db->from('sales_snapshot as ss');
        $this->db->join('staff_accounts as sa','sa.StaffID = ss.sales_snapshot_sales_rep_id','left');
        $this->db->join('country_access as ca','ca.staff_accounts_id = sa.StaffID','left');
        $this->db->join('agency as a','a.agency_id = ss.agency_id','left');
        $this->db->join('agency_priority as aght','a.agency_id = aght.agency_id','left');
        $this->db->where('ca.country_id', $this->config->item('country'));
        #$this->db->where('sa.ClassID', 5); // class id 5 = SALES
        $this->db->where($where);
        $this->db->where('sa.active', 1); 
        $this->db->where('sa.Deleted', 0); 
        $this->db->order_by('sa.FirstName','ASC');
        return $this->db->get();
        
    }

    public function getSnapshot($params){

       if($this->config->item('country')==1){ //AU
            $brad = 2165;
            $gavin = 2189;
            $shaquille = 2296;
            $where = "(sa.`ClassID` = 5 OR sa.`StaffID` = {$brad} OR sa.`StaffID` = {$gavin} OR sa.`StaffID` = {$shaquille} )";
        }else{ //NZ
            $gavin = 2202;
            $shaquille = 2259;
            $where = "(sa.`ClassID` = 5 OR sa.`StaffID` = {$gavin} OR sa.`StaffID` = {$shaquille})";
        }

        if($params['sel_query'] && !empty($params['sel_query'])){
            $sel_query = $params['sel_query'];
        }else{
            $sel_query = "*";
        }
        
        $this->db->select($sel_query);
        $this->db->from('sales_snapshot as ss');
        $this->db->join('agency as a','a.agency_id = ss.agency_id','left');
        //$this->db->join('postcode_regions as pr','pr.postcode_region_id = a.postcode_region_id','left');
        $this->db->join('sub_regions as sr','sr.sub_region_id = a.postcode_region_id','left'); ##new table
        $this->db->join('sales_snapshot_status as ss_s','ss_s.sales_snapshot_status_id = ss.sales_snapshot_status_id','left');
        $this->db->join('agency_priority as aght','a.agency_id = aght.agency_id','left');
        $this->db->join('staff_accounts as sa','sa.StaffID = ss.sales_snapshot_sales_rep_id','left');
        $this->db->where('ss.country_id',$this->config->item('country'));
        #$this->db->where('sa.ClassID', 5); // class id 5 = SALES
        $this->db->where($where);
        $this->db->where('sa.Deleted',0);
        $this->db->where('sa.active',1);

        if($params['sales_snapshot_sales_rep_id_where'] && !empty($params['sales_snapshot_sales_rep_id_where'])){
            $this->db->where('sa.StaffID', $params['sales_snapshot_sales_rep_id_where']);
        }

        if($params['snapshot_id'] && !empty($params['snapshot_id'])){
            $this->db->where('ss.sales_snapshot_id', $params['snapshot_id']);
        }
        
        $this->db->order_by('ss.date','DESC');

        return $this->db->get();
							

    }


    /**
     * Update Snapshot
     * @params data
     * @params snapshot id
     */
    public function updateSnapShot($snapshot_id, $data){  

        if($snapshot_id!=""){

            $this->db->where('sales_snapshot_id', $snapshot_id);
            $this->db->update('sales_snapshot', $data);
            $this->db->limit(1);

            if($this->db->affected_rows()>0){
                return true;
            }else{
                return false;
            }

        }
        

    }


    public function kpi_getTotalPropertyCount(){

        $fg = 14; // Defence Housing
        $fg_filter = "AND a.`franchise_groups_id` != {$fg}";

		return $this->db->query("
			SELECT COUNT(DISTINCT(p.`property_id`)) AS p_count, p.`property_id`
			FROM `property_services` AS ps
			LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE ps.`service` =1
			AND p.`deleted` =0
            AND (
                p.`is_nlm` IS NULL 
                OR p.`is_nlm` = 0
            )
			AND a.`status` = 'active'
            AND a.`country_id` = {$this->config->item('country')}
            {$fg_filter} 
        ");
        
    }

    public function get_total_properties_excluding_dha(){

        $fg = 14; // Defence Housing        

		return $this->db->query("
			SELECT COUNT(DISTINCT(p.`property_id`)) AS p_count, p.`property_id`
			FROM `property_services` AS ps
			LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE ps.`service` =1
			AND p.`deleted` =0
			AND a.`status` = 'active'
            AND a.`country_id` = {$this->config->item('country')}
            AND a.`franchise_groups_id` != {$fg}
        ");
        
    }
    
    /**
     * get agency count
     * return agency count
     */
    public function getTotalAgencyCount(){
        $fg = 14; // Defence Housing
        $fg_filter = "AND franchise_groups_id != {$fg}";

        $sql = $this->db->query("
        SELECT COUNT(`agency_id`) AS a_count
            FROM `agency`
            WHERE `status` =  'active'
            AND `country_id` ={$this->config->item('country')}
            {$fg_filter} 
        ");
        $row = $sql->row();
        return $row->a_count;
    }


    /**
     * Get count of services alarms 
     */
    public function get_property_services($params){

        // sales commission version on page parameter overrides global settings
		$sales_commission_ver = ( $this->input->get_post('ver') != '' )?$this->input->get_post('ver'):$this->config->item('sales_commission_ver');
        
        if($params['sel_query'] && $params['sel_query']!=""){
            $sel_query = $params['sel_query'];
        }else{
            $sel_query  = "*";
        }
        
        $this->db->select($sel_query);
        $this->db->from('property_services as ps');
        $this->db->join('property as p','p.property_id = ps.property_id','left');
        $this->db->join('agency as a','p.`agency_id` = a.`agency_id`','left');
        // sales commission version switch        
		if( $sales_commission_ver == 'new' ){
			$this->db->where('ps.is_payable',1);
		}else{
			$this->db->where('ps.service',1);
		}        
        $this->db->where('p.deleted',0);
        $this->db->where("(p.`is_nlm` IS NULL OR p.`is_nlm` = 0)");

        //agency id
        if(!empty($params['agency_id'])){
            $this->db->where('p.agency_id', $params['agency_id']);
        }

        // country id
        if( $params['country_id'] > 0 ){
            $this->db->where('a.country_id', $params['country_id']);
        }

        //alarm job type id
        if(!empty($params['alarm_job_type_id'])){
            $this->db->where('ps.alarm_job_type_id', $params['alarm_job_type_id']);
        }

        //Date from / to filter
        if(!empty($params['date_from_filter']) && !empty($params['date_to_filter'])){

            $date_from2 = $this->system_model->formatDate($params['date_from_filter']);
            $date_to2 = $this->system_model->formatDate($params['date_to_filter']);

            $this->db->where('CAST(ps.`status_changed` AS DATE) >=', $date_from2);
            $this->db->where('CAST(ps.`status_changed` AS DATE) <=', $date_to2);

        }

       
        $query = $this->db->get();
		if( isset($params['display_query']) && $params['display_query'] == 1 ){
			echo $this->db->last_query();
		}
		
		return $query;
       


    }

    /**
     * Get Deleted Services
     * 
     */
    public function get_deleted_services($params){
        
        if($params['sel_query'] && $params['sel_query']!=""){
            $sel_query = $params['sel_query'];
        }else{
            $sel_query  = "*";
        }

        $this->db->select($sel_query);
        $this->db->from('property_services as ps');
        $this->db->join('property as p ','p.property_id = ps.property_id','left');
        $this->db->join('agency as a ','a.agency_id = p.agency_id','left');
        $this->db->where('p.deleted',0);
        $this->db->where('p.is_nlm',1);
        $this->db->where('ps.service',1);
        $this->db->where('a.country_id', $this->config->item('country'));

        //agency id
        if(!empty($params['agency_id'])){
            $this->db->where('a.agency_id', $params['agency_id']);
        }

        //Date from / to filter
        if(!empty($params['date_from_filter']) && !empty($params['date_to_filter'])){

            $date_from2 = $this->system_model->formatDate($params['date_from_filter']);
            $date_to2 = $this->system_model->formatDate($params['date_to_filter']);

            //$this->db->where('CAST(p.deleted_date AS DATE) >=', $date_from2); ## updated below since we do not use deleted as nlm
            $this->db->where('CAST(p.nlm_timestamp AS DATE) >=', $date_from2);
            //$this->db->where('CAST(p.deleted_date AS DATE) <=', $date_to2); ## updated below since we do not use deleted as nlm
            $this->db->where('CAST(p.nlm_timestamp AS DATE) <=', $date_to2);

        }


        return $this->db->get();

    }


    public function getAddedBySats($params){

        // sales commission version on page parameter overrides global settings
		$sales_commission_ver = ( $this->input->get_post('ver') != '' )?$this->input->get_post('ver'):$this->config->item('sales_commission_ver');

        if($params['sel_query'] && $params['sel_query']!=""){
            $sel_query = $params['sel_query'];
        }else{
            $sel_query  = "*";
        }

        $this->db->select($sel_query);
        $this->db->from('property_services as ps');
        $this->db->join('property as p ','p.property_id = ps.property_id','left');
        $this->db->join('agency as a ','a.agency_id = p.agency_id','left');        
        // sales commission version switch        
		if( $sales_commission_ver == 'new' ){
			$this->db->where('ps.is_payable',1);
		}else{
			$this->db->where('ps.service',1);
		}  
        $this->db->where('a.country_id', $this->config->item('country'));
        $this->db->where('p.added_by >',0 );

        //agency id
        if(!empty($params['agency_id'])){
            $this->db->where('a.agency_id', $params['agency_id']);
        }

        //Date from / to filter
        if(!empty($params['date_from_filter']) && !empty($params['date_to_filter'])){

            $date_from2 = $this->system_model->formatDate($params['date_from_filter']);
            $date_to2 = $this->system_model->formatDate($params['date_to_filter']);

            $this->db->where('CAST(ps.`status_changed` AS DATE) >=', $date_from2);
            $this->db->where('CAST(ps.`status_changed` AS DATE) <=', $date_to2);

        }

        return $this->db->get();

    }


    public function getAddedByAgency($params){

        // sales commission version on page parameter overrides global settings
		$sales_commission_ver = ( $this->input->get_post('ver') != '' )?$this->input->get_post('ver'):$this->config->item('sales_commission_ver');

        if($params['sel_query'] && $params['sel_query']!=""){
            $sel_query = $params['sel_query'];
        }else{
            $sel_query  = "*";
        }

        $this->db->select($sel_query);
        $this->db->from('property_services as ps');
        $this->db->join('property as p ','p.property_id = ps.property_id','left');
        $this->db->join('agency as a ','a.agency_id = p.agency_id','left');        
        // sales commission version switch        
		if( $sales_commission_ver == 'new' ){
			$this->db->where('ps.is_payable',1);
		}else{
			$this->db->where('ps.service',1);
		}
        $this->db->where('a.country_id', $this->config->item('country'));
        $this->db->where('p.added_by <=',0 );

        //agency id
        if(!empty($params['agency_id'])){
            $this->db->where('a.agency_id', $params['agency_id']);
        }

        //Date from / to filter
        if(!empty($params['date_from_filter']) && !empty($params['date_to_filter'])){

            $date_from2 = $this->system_model->formatDate($params['date_from_filter']);
            $date_to2 = $this->system_model->formatDate($params['date_to_filter']);

            $this->db->where('CAST(ps.`status_changed` AS DATE) >=', $date_from2);
            $this->db->where('CAST(ps.`status_changed` AS DATE) <=', $date_to2);

        }

        return $this->db->get();

    }


    public function get_contractors($params){

        if($params['sel_query'] && $params['sel_query']!=""){
            $sel_query = $params['sel_query'];
        }else{
            $sel_query  = "*";
        }
        $this->db->select($sel_query);
        $this->db->from('contractors as c');
        $this->db->where('country_id',$this->config->item('country'));
        $this->db->order_by('area','ASC');

        return $this->db->get();

    }



    public function getKeyMapRoutes_v2($params){

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('tech_run_keys AS kr');
        $this->db->join('agency as a',"a.agency_id = kr.agency_id",'left');
        $this->db->join('agency_priority as aght', 'a.agency_id = aght.agency_id', 'left');
        $this->db->join('staff_accounts as sa',"sa.StaffID = kr.assigned_tech",'left');
        $this->db->where('kr.tech_run_keys_id >',0);

        if( $params['date'] != "" && $params['date'] ){
            $this->db->where('kr.date', $params['date']);
        }
        
        if( $params['agency_id'] != "" && $params['agency_id'] ){
            $this->db->where('kr.agency_id', $params['agency_id']);
        }
        
        if( $params['tech_id'] != "" && $params['tech_id'] ){
            $this->db->where('kr.assigned_tech', $params['tech_id']);
        }
        
        if(  is_numeric($params['completed']) && $params['completed'] ){
            $this->db->where('kr.completed', $params['completed']);
        }
        
        if(  $params['country_id'] != '' && $params['country_id'] ){
            $this->db->where('a.country_id', $params['country_id']);
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



    public function get_expiring_alarm($alarm,$date,$is_batteries,$country_id){

        $last_year = date("Y",strtotime("{$date} -1 year"));	
        $this_month = date("m",strtotime($date));
        $this_year = date("Y",strtotime($date));	
        $max_day = date("t",strtotime("{$last_year}-{$this_month}"));
        $bat_str = "";

        if($is_batteries!=1){
            $bat_str = "
                AND a.`expiry` = '{$this_year}'
                AND j.`job_type` = 'Yearly Maintenance'
                AND a.`alarm_power_id` = {$alarm}
            ";
        }
        
        $str = "
            SELECT count( a.`alarm_id` ) AS jcount
            FROM `alarm` AS a
            LEFT JOIN `jobs` AS j ON a.`job_id` = j.`id`
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS agen ON agen.`agency_id` = p.`agency_id`
            WHERE j.`status` = 'Completed'		
            AND (j.`date` BETWEEN '{$last_year}-{$this_month}-01' AND '{$last_year}-{$this_month}-{$max_day}')
            AND p.`deleted` = 0
            AND agen.`status` = 'active'
            AND j.`del_job` = 0
            AND agen.`country_id` = {$country_id}
            {$bat_str}
        ";
        $sql = $this->db->query($str);
        $row = $sql->row_array();
        return $row['jcount'];

    }


    public function getRegion($params){

        if($params['sel_query'] && !empty($params['sel_query'])){
            $sel_query = $params['sel_query'];
        }else{
            $sel_query = '*';
        }

       /* $this->db->select($sel_query);
        $this->db->from('postcode_regions as pr');
        $this->db->join('countries as c', 'c.country_id = pr.country_id', 'left');
        $this->db->join('regions as r', 'r.regions_id = pr.region', 'left');
        $this->db->where('pr.deleted',0);
        $this->db->where('pr.country_id',$this->config->item('country'));*/

        ##fetched from new table
        $this->db->select($sel_query);        
        $this->db->from('`postcode` AS pc');
        $this->db->join('`sub_regions` AS sr', 'pc.`sub_region_id` = sr.`sub_region_id`', 'left');
        $this->db->join('`regions` AS r', 'sr.`region_id` = r.`regions_id`', 'left');

        //FILTERS
        #region state filter
        if($params['state']!="" && $params['state']){
            $this->db->where('r.region_state', $params['state']);
		}
        
        #sub_region_id filter
		if($params['postcode_region_id']!="" && $params['postcode_region_id']){
            $this->db->where_in('sr.sub_region_id', $params['postcode_region_id']);
		}
        
        #postcode filter
		if($params['postcode_region_postcodes']!=""){
            $this->db->like('pc.postcode', $params['postcode_region_postcodes']);
        }

        // custom filter
        if( isset($params['custom_where']) ){
            $this->db->where($params['custom_where']);
        }
        
        // custom filter
        if( isset($params['custom_where_arr']) ){
            foreach( $params['custom_where_arr'] as $index => $custom_where ){
                if( $custom_where != '' ){
                    $this->db->where($custom_where);
                }				
            }              
        }
        
        // group by
        if( isset($params['group_by']) && $params['group_by'] != '' ){
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

        // limit/offset
		if( isset($params['limit']) && $params['limit'] > 0 ){
			$this->db->limit( $params['limit'], $params['offset']);
        }	
        
        $query = $this->db->get();
		if( isset($params['display_query']) && $params['display_query'] == 1 ){
			echo $this->db->last_query();
		}
        
        return $query;

    }


    public function this_getPropertyCount($postcode, $dh){

        //clean postcode
        $clean_postcode = $this->system_model->get_sanitized_postcode($postcode);

        $fg = 14; // Defence Housing
        if($dh && $dh == 1){ //exclude DH
            $fg_filter = "AND a.`franchise_groups_id` != {$fg}";
        }else{ //include DH
            $fg_filter = "";
        }
       

        $sql_str = "
            SELECT count( ps.`property_services_id` ) AS ps_count
            FROM `property_services` AS ps
            LEFT JOIN `property` AS p ON p.`property_id` = ps.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE ps.`service` =1
            AND p.`deleted` =0
            AND a.`status` = 'active'
            AND p.`postcode` IN({$clean_postcode})
            AND a.`country_id` = {$this->config->item('country')}
            {$fg_filter} 
	    ";
	
        $sql = $this->db->query($sql_str);
        $sql_row = $sql->row_array();
        return $sql_row['ps_count'];

    }

    public function get_property_service_data($postcode_arr, $ex_dh){
    
        $this->db->select("COUNT( DISTINCT ps.`property_id` ) AS ps_count, SUM( ps.`price` ) AS ps_price, p.`postcode`");
        $this->db->from('`property_services` AS ps');
        $this->db->join('`property` AS p', 'ps.`property_id` = p.`property_id`', 'left');
        $this->db->join('`agency` AS a', ' p.`agency_id` = a.`agency_id`', 'left');
        $this->db->where('ps.`service`', 1);
        $this->db->where('p.`deleted`', 0);
        $this->db->where("( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )");
        $this->db->where('a.`status`', 'active');
        $this->db->where('a.`country_id`', $this->config->item('country'));        
      
        if($ex_dh && $ex_dh == 1){ //exclude DH  

            $fg = 14; // Defence Housing          
            $this->db->where("a.`franchise_groups_id` != {$fg}");

        }

        $this->db->where_in('p.`postcode`', $postcode_arr);
        $this->db->group_by('p.`postcode`');
        $query = $this->db->get();       
        //echo $this->db->last_query();
        
        return $query;       

    }


    public function this_getTotPropertyServicePrice($postcode, $dh){

         //clean postcode
         $clean_postcode = $this->system_model->get_sanitized_postcode($postcode);

         $fg = 14; // Defence Housing
         if($dh && $dh == 1){ //exclude DH
            $fg_filter = "AND a.`franchise_groups_id` != {$fg}";
        }else{ //include DH
            $fg_filter = "";
        }
		
        $sql_str = "
            SELECT SUM( ps.`price` ) AS tot_ps_price
            FROM `property_services` AS ps
            LEFT JOIN `property` AS p ON p.`property_id` = ps.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE ps.`service` =1
            AND p.`deleted` =0
            AND a.`status` = 'active'
            AND p.`postcode` IN({$clean_postcode})
            AND a.`country_id` = {$this->config->item('country')}
            {$fg_filter} 
        ";
        
        $sql = $this->db->query($sql_str);
        $sql_row = $sql->row_array();
        return $sql_row['tot_ps_price'];
        
    }


    public function get_dirty_address($params){

        if($params['sel_query'] && !empty($params['sel_query'])){
            $sel_query = $params['sel_query'];
        }else{
            $sel_query = '*';
        }

        $suburb_where = "p.address_3 REGEXP BINARY '^[A-Z]+$'"; //uppercases
        $suburb_where2 = "p.address_2 REGEXP BINARY '^[A-Z]+$'"; //uppercase

        $like = ( " (p.address_1 LIKE '%/%' AND p.address_1 LIKE '%-%') " ); // (/and-)
        $like2 = ( " (p.address_2 LIKE '% - %' OR p.address_2 LIKE '%- %' OR p.address_2 LIKE '% -%') " ); // address2 hypen
        $like3 = ( " (p.address_2 LIKE '%,%' OR p.address_3 LIKE '%,%' ) " ); // comma
        $null_filter = "p.address_3 IS NULL OR p.address_3 = '' "; //new added oct-6-20

        $nz_auckland = "(p.address_3 = 'Auckland' OR p.address_3 = 'auckland')"; //nz auckland string
        $nz_state_uppercase = "p.state REGEXP BINARY '^[A-Z]+$'"; //nz uppercase state 
        $escape_Hawkes_bay = $this->db->escape("Hawke's Bay");
       //$escape_Hawkes_bay2 = $this->db->escape("Hawkeâ€™s Bay");
        $nz_staten_not_equal = "( p.state!='Auckland' AND p.state!='Bay of Plenty' AND p.state!='Canterbury' AND p.state!='Northland' AND p.state!='Otago' AND p.state!='Waikato' AND p.state!='Whangarei' AND p.state!='Manawatu-Wanganui' AND p.state!='Southland' AND p.state!='Wellington' AND p.state!='Gisborne' AND p.state!='Taranaki' AND p.state!={$escape_Hawkes_bay} AND p.state!='Manawatu' AND p.state!='Tasman' )";

        $this->db->select($sel_query);
        $this->db->from('property as p');
        $this->db->join('agency as a','a.agency_id = p.agency_id','left');
        $this->db->join('agency_priority as aght', 'a.agency_id = aght.agency_id', 'left');
        $this->db->where('ignore_dirty_address',0);
        $this->db->group_start();
        $this->db->or_where('p.address_1 is NULL', NULL, FALSE);
        $this->db->or_where('p.address_1',"");
        $this->db->or_where('p.postcode is NULL', NULL, FALSE);
        $this->db->or_where('p.postcode',"");
        $this->db->or_where('p.state is NULL', NULL, FALSE);
        $this->db->or_where('p.state',"");
        $this->db->or_where($suburb_where);
        $this->db->or_where($suburb_where2);
        $this->db->or_where($like);
        $this->db->or_where($like2);
        $this->db->or_where($like3);
        $this->db->or_where('LENGTH(p.`postcode`) < 4'); // postcode is less that 4
        $this->db->or_where($null_filter); // null address_3/suburb

        if($this->config->item('country')==2){ //for NZ
            $this->db->or_where($nz_auckland); //catch NZ address_3 > Auckland/auckland string and uppercase state
            $this->db->or_where($nz_state_uppercase); //nz uppercase state
            $this->db->or_where($nz_staten_not_equal); //state not equal
        }

        $this->db->group_end();

       // if($this->config->item('country')==2){
           
        //}

        $this->db->where('a.country_id', $this->config->item('country'));

        //search
        if (isset($params['search']) && $params['search'] != '') {
            $search_filter = "CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode))";
            $this->db->like($search_filter, $params['search']);
        }

         // limit/offset
		if( isset($params['limit']) && $params['limit'] > 0 ){
        $this->db->limit( $params['limit'], $params['offset']);
        }
        
        return $this->db->get();

    }

    public function get_nsw_property_report($start,$limit){
        
        // paginate
        if(is_numeric($start) && is_numeric($limit)){
            $str = "LIMIT {$start}, {$limit}";
        }

        return $this->db->query("
            SELECT a.* FROM (SELECT MAX(j.`id`) AS id,
                p.`property_id`,
                p.`address_1` AS p_address1, 
                p.`address_2` AS p_address2, 
                p.`address_3` AS p_address3, 
                p.`state` AS p_state, 
                p.`postcode` AS p_postcode,
                a.`agency_id`,
                a.`agency_name`,
                p.`test_date`,
                MAX(j.`status`) AS job_status,
                MAX(j.`job_type`) AS job_type,
                MAX(j.`date`) AS job_date,
                (365 - DATEDIFF(NOW(), MAX(j.`date`))) AS deadline
            FROM `jobs` AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            INNER JOIN `property_services` AS ps ON ( 
                j.`property_id` = ps.`property_id` 
                AND j.`service` = ps.`alarm_job_type_id` 
            )
            WHERE a.`country_id` = {$this->config->item('country')}
            AND p.`state` = 'NSW'
            AND ps.`service` = 1
            AND j.`date` IS NOT NULL
            AND j.`date` != '0000-00-00'
            AND p.`deleted` = 0
            AND j.`del_job` = 0
            AND a.`status` = 'active'
            GROUP BY j.`property_id`
            ORDER BY deadline ASC) a
            WHERE DATEDIFF(NOW(), a.`job_date`) > 300
            AND a.`job_status` != 'DHA' 
            AND a.`job_type` != 'Once-off'
            {$str}
        ");
    
    }

    public function get_null_retest_date($params){
        
        if($params['sel_query'] && !empty($params['sel_query'])){
            $sel_query = $params['sel_query'];
        }else{
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('property as p');
        $this->db->join('property_services as ps', 'ps.property_id = p.property_id','left');

        //join table params
        if ($params['join_table'] > 0) {
            foreach ($params['join_table'] as $join_table) {
                if ($join_table == 'agency_table') {
                    $this->db->join('agency as a', 'a.agency_id = p.agency_id','left');
                    $this->db->where('a.country_id',$this->config->item('country'));
                }
            }
        }

       
        $this->db->where('p.deleted',0);
        $this->db->where('ps.service',1);
        $where = '(p.is_nlm IS NULL OR p.is_nlm = 0)'; 
        $this->db->where($where);

        // custom filter
        if( isset($params['custom_where']) ){
            $this->db->where($params['custom_where']);
        }
        
		if( isset($params['limit']) && $params['limit'] > 0 ){
			$this->db->limit( $params['limit'], $params['offset']);
        }	
        
        $query = $this->db->get();

        if( isset($params['display_query']) && $params['display_query'] == 1 ){
			echo $this->db->last_query();
        }
        
        return $query;

        /*
        // paginate
        if(is_numeric($start) && is_numeric($limit)){
            $str = "LIMIT {$start}, {$limit}";
        }
        
        return $this->db->query("SELECT j.id, j.status, j.date, j.property_id, p.retest_date, j.job_type, p.address_1 as p_address1, p.address_2 as p_address2, p.address_3 as p_address3, p.state as p_state, p.postcode as p_postcode
        FROM jobs AS j
        INNER JOIN (
        
            SELECT j4.property_id, MAX(j4.date) AS latest_date
            FROM jobs AS j4
            LEFT JOIN property AS p2 ON j4.property_id = p2.property_id	
            LEFT JOIN agency AS a2 ON p2.agency_id = a2.agency_id
            WHERE j4.del_job = 0
            AND j4.status = 'Completed'
            AND j4.job_type != 'Once-off'
            AND a2.country_id = {$this->config->item('country')}
            AND p2.deleted = 0
            AND a2.status = 'active'
            GROUP BY j4.property_id DESC
        
        ) AS j3 ON ( j.property_id = j3.property_id AND j.date = j3.latest_date )
        LEFT JOIN property AS p ON j.property_id = p.property_id
        LEFT JOIN agency AS a ON p.agency_id = a.agency_id
        WHERE j.del_job = 0
        AND j.status = 'Completed'
        AND a.country_id = {$this->config->item('country')}
        AND p.deleted = 0
        AND a.status = 'active'
        AND p.retest_date IS NULL
        GROUP BY p.property_id 
        {$str}
        ");
        */

    }



    public function get_sales_calendar_entry($staff_id,$date){

        return $this->db->query("
        SELECT *
        FROM `calendar`
        WHERE `staff_id` = $staff_id
        AND '{$date}' BETWEEN `date_start` AND `date_finish`
        ");

    }

    

    public function get_tech_break($params){

        if($params['sel_query'] && !empty($params['sel_query'])){
            $sel_query = $params['sel_query'];
        }else{
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('tech_breaks as tb');
        $this->db->join('staff_accounts as sa', 'tb.tech_id = sa.StaffID', 'full outer');
        

        //FILTERS
        if( $params['tb_start'] != '' ){           
            $this->db->where("CAST(tb.tech_break_start AS Date) = '{$params['tb_start']}'");
        }	
        
        if( $params['tb_taken'] != '' ){
            $this->db->where('tb.tech_break_taken', $params['tb_taken']);
        }
        
        if( $params['tech'] != '' ){
            $this->db->where('tb.tech_id', $params['tech']);
        }

        if( $params['staff_class'] != '' ){
            $this->db->where('sa.ClassID', $params['staff_class']);
        }                        

        // custom filter
        if( isset($params['custom_where']) ){
            $this->db->where($params['custom_where']);
        }
        
        // custom filter
        if( isset($params['custom_where_arr']) ){
            foreach( $params['custom_where_arr'] as $index => $custom_where ){
                if( $custom_where != '' ){
                    $this->db->where($custom_where);
                }				
            }              
        }
        
        // group by
        if( isset($params['group_by']) && $params['group_by'] != '' ){
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

        // limit/offset
		if( isset($params['limit']) && $params['limit'] > 0 ){
			$this->db->limit( $params['limit'], $params['offset']);
        }	
        
        $query = $this->db->get();
		if( isset($params['display_query']) && $params['display_query'] == 1 ){
			echo $this->db->last_query();
		}
        
        return $query;

    }    
    
    
    public function week_sales_report_table_row($staff_id){

        $today = date('Y-m-d');
        $country = $this->config->item('country'); 

        // get staff accounts
        $sel_query = '
        sa.`StaffID`,
        sa.`FirstName`,
        sa.`LastName`,
        sa.`sa_position`
        ';

        $params = array( 
            'sel_query' => $sel_query,            
            'staff_id' => $staff_id,
            'active' => 1,
            'deleted' => 0,
            'display_query' => 0
        );
        
        // get user details
        $user_account_sql = $this->staff_accounts_model->get_staff_accounts($params);  
        $user_account_row = $user_account_sql->row();
        $staff_name = "{$user_account_row->FirstName} {$user_account_row->LastName}";

        // today KMS
        $kms_sql = $this->db->query("
        SELECT `kms`
        FROM `kms` AS k
        LEFT JOIN `vehicles` AS v ON k.`vehicles_id` = v.`vehicles_id`        
        WHERE v.`StaffID` = {$staff_id}
        ORDER BY k.`kms_updated` DESC
        LIMIT 1
        ");
        $kms_row = $kms_sql->row();
        $recent_kms = $kms_row->kms;



        // THIS WEEK SCHEDULE:
        // this week monday
        $this_week_monday = date('Y-m-d',strtotime('monday this week'));
        $calendar_sql = $this->reports_model->get_sales_calendar_entry($staff_id,$this_week_monday);
        $this_week_monday_cal_details_arr = [];
        foreach( $calendar_sql->result() as $calendar_row ){
            $this_week_monday_cal_details_arr[] = $calendar_row->region;
        }  
        
        if( count($this_week_monday_cal_details_arr) > 0 ){
            $this_week_monday_cal_details_imp = implode(', ',$this_week_monday_cal_details_arr);
        }

        // this week tuesday
        $this_week_tuesday = date('Y-m-d',strtotime('tuesday this week'));
        $calendar_sql = $this->reports_model->get_sales_calendar_entry($staff_id,$this_week_tuesday);
        $this_week_tuesday_cal_details_arr = [];
        foreach( $calendar_sql->result() as $calendar_row ){
            $this_week_tuesday_cal_details_arr[] = $calendar_row->region;
        }  
        
        if( count($this_week_tuesday_cal_details_arr) > 0 ){
            $this_week_tuesday_cal_details_imp = implode(', ',$this_week_tuesday_cal_details_arr);
        }

        // this week wednesday
        $this_week_wednesday = date('Y-m-d',strtotime('wednesday this week'));
        $calendar_sql = $this->reports_model->get_sales_calendar_entry($staff_id,$this_week_wednesday);
        $this_week_wednesday_cal_details_arr = [];
        foreach( $calendar_sql->result() as $calendar_row ){
            $this_week_wednesday_cal_details_arr[] = $calendar_row->region;
        }  
        
        if( count($this_week_wednesday_cal_details_arr) > 0 ){
            $this_week_wednesday_cal_details_imp = implode(', ',$this_week_wednesday_cal_details_arr);
        }

        // this week thursday
        $this_week_thursday = date('Y-m-d',strtotime('thursday this week'));
        $calendar_sql = $this->reports_model->get_sales_calendar_entry($staff_id,$this_week_thursday);
        $this_week_thursday_cal_details_arr = [];
        foreach( $calendar_sql->result() as $calendar_row ){
            $this_week_thursday_cal_details_arr[] = $calendar_row->region;
        }  
        
        if( count($this_week_thursday_cal_details_arr) > 0 ){
            $this_week_thursday_cal_details_imp = implode(', ',$this_week_thursday_cal_details_arr);
        }

        // this week friday
        $this_week_friday = date('Y-m-d',strtotime('friday this week'));
        $calendar_sql = $this->reports_model->get_sales_calendar_entry($staff_id,$this_week_friday);
        $this_week_friday_cal_details_arr = [];
        foreach( $calendar_sql->result() as $calendar_row ){
            $this_week_friday_cal_details_arr[] = $calendar_row->region;
        }  
        
        if( count($this_week_friday_cal_details_arr) > 0 ){
            $this_week_friday_cal_details_imp = implode(', ',$this_week_friday_cal_details_arr);
        }


        // NEXT WEEK SCHEDULE:
        // next week monday
        $next_week_monday = date('Y-m-d',strtotime('monday next week'));
        $calendar_sql = $this->reports_model->get_sales_calendar_entry($staff_id,$next_week_monday);
        $next_week_monday_cal_details_arr = [];
        foreach( $calendar_sql->result() as $calendar_row ){
            $next_week_monday_cal_details_arr[] = $calendar_row->region;
        }  
        
        if( count($next_week_monday_cal_details_arr) > 0 ){
            $next_week_monday_cal_details_imp = implode(', ',$next_week_monday_cal_details_arr);
        }

        // next week tuesday
        $next_week_tuesday = date('Y-m-d',strtotime('tuesday next week'));
        $calendar_sql = $this->reports_model->get_sales_calendar_entry($staff_id,$next_week_tuesday);
        $next_week_tuesday_cal_details_arr = [];
        foreach( $calendar_sql->result() as $calendar_row ){
            $next_week_tuesday_cal_details_arr[] = $calendar_row->region;
        }  
        
        if( count($next_week_tuesday_cal_details_arr) > 0 ){
            $next_week_tuesday_cal_details_imp = implode(', ',$next_week_tuesday_cal_details_arr);
        }

        // next week wednesday
        $next_week_wednesday = date('Y-m-d',strtotime('wednesday next week'));
        $calendar_sql = $this->reports_model->get_sales_calendar_entry($staff_id,$next_week_wednesday);
        $next_week_wednesday_cal_details_arr = [];
        foreach( $calendar_sql->result() as $calendar_row ){
            $next_week_wednesday_cal_details_arr[] = $calendar_row->region;
        }  
        
        if( count($next_week_wednesday_cal_details_arr) > 0 ){
            $next_week_wednesday_cal_details_imp = implode(', ',$next_week_wednesday_cal_details_arr);
        }

        // next week thursday
        $next_week_thursday = date('Y-m-d',strtotime('thursday next week'));
        $calendar_sql = $this->reports_model->get_sales_calendar_entry($staff_id,$next_week_thursday);
        $next_week_thursday_cal_details_arr = [];
        foreach( $calendar_sql->result() as $calendar_row ){
            $next_week_thursday_cal_details_arr[] = $calendar_row->region;
        }  
        
        if( count($next_week_thursday_cal_details_arr) > 0 ){
            $next_week_thursday_cal_details_imp = implode(', ',$next_week_thursday_cal_details_arr);
        }

        // next week friday
        $next_week_friday = date('Y-m-d',strtotime('friday next week'));
        $calendar_sql = $this->reports_model->get_sales_calendar_entry($staff_id,$next_week_friday);
        $next_week_friday_cal_details_arr = [];
        foreach( $calendar_sql->result() as $calendar_row ){
            $next_week_friday_cal_details_arr[] = $calendar_row->region;
        }  
        
        if( count($next_week_friday_cal_details_arr) > 0 ){
            $next_week_friday_cal_details_imp = implode(', ',$next_week_friday_cal_details_arr);
        }

        // sales commission version on page parameter overrides global settings
		$sales_commission_ver = ( $this->input->get_post('ver') != '' )?$this->input->get_post('ver'):$this->config->item('sales_commission_ver');

        // sales commission version switch
        $sales_commission_str =  null;
		if( $sales_commission_ver == 'new' ){
			$sales_commission_str = 'AND ps.`is_payable` = 1';
		}else{
			$sales_commission_str = 'AND ps.`service` = 1';
		}

        // Sales Result
        // this week
        $query_filter_str = "";		
        $this_week_start = date('Y-m-d',strtotime('monday this week')); 
        $this_week_end = date('Y-m-d',strtotime('friday this week')); 

        $query_filter_str = "
            AND CAST(ps.`status_changed` AS DATE) BETWEEN '{$this_week_start}' AND '{$this_week_end}'
            AND (
                p.`is_nlm` IS NULL 
                OR p.`is_nlm` = 0
            )
            AND p.deleted = 0
        ";
        
        $sql = "
            SELECT COUNT(ps.`property_services_id`) AS ps_count
            FROM `property_services` AS ps
            LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE a.`salesrep` ={$staff_id}            
            {$sales_commission_str}
            AND a.`country_id` = {$country}
            {$query_filter_str}
        ";
        
        $sales_res_sql = $this->db->query($sql);
        $sales_res_row = $sales_res_sql->row();
        $this_week_sales_res = $sales_res_row->ps_count;



        // this month
        $query_filter_str = "";		
        $this_month_start = date('Y-m-d',strtotime('first day of this month')); 
        $this_month_end = date('Y-m-d',strtotime('last day of this month')); 

        $query_filter_str = "
            AND CAST(ps.`status_changed` AS DATE) BETWEEN '{$this_month_start}' AND '{$this_month_end}'
            AND (
                p.`is_nlm` IS NULL 
                OR p.`is_nlm` = 0
            )
            AND p.deleted = 0
        ";
        
        $sql = "
            SELECT COUNT(ps.`property_services_id`) AS ps_count
            FROM `property_services` AS ps
            LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE a.`salesrep` ={$staff_id}            
            {$sales_commission_str}
            AND a.`country_id` = {$country}
            {$query_filter_str}
        ";
        
        $sales_res_sql = $this->db->query($sql);
        $sales_res_row = $sales_res_sql->row();
        $this_month_sales_res = $sales_res_row->ps_count;





        
        // PHP (server side)
        $view_data = array(
            'staff_id' => $staff_id,
            'staff_name' => $staff_name,
            'staff_position' => $user_account_row->sa_position,
            'recent_kms' => $recent_kms,

            'this_week_monday_cal' => $this_week_monday_cal_details_imp,
            'this_week_tuesday_cal' => $this_week_tuesday_cal_details_imp,           
            'this_week_wednesday_cal' => $this_week_wednesday_cal_details_imp,
            'this_week_thursday_cal' => $this_week_thursday_cal_details_imp,
            'this_week_friday_cal' => $this_week_friday_cal_details_imp,

            'next_week_monday_cal' => $next_week_monday_cal_details_imp,
            'next_week_tuesday_cal' => $next_week_tuesday_cal_details_imp,           
            'next_week_wednesday_cal' => $next_week_wednesday_cal_details_imp,
            'next_week_thursday_cal' => $next_week_thursday_cal_details_imp,
            'next_week_friday_cal' => $next_week_friday_cal_details_imp,                     
            
            'this_week_sales_res' => $this_week_sales_res,
            'this_month_sales_res' => $this_month_sales_res
        );
        //echo json_encode($view_data);

        return $this->load->view('reports/weekly_sales_report_tr', $view_data, true);

    }


    public function get_sats_serviced_property_count_via_postcode($params){
        
        $postcode_arr = $params['postcode_arr'];
        $exc_dha = $params['exc_dha'];
        $fg_dha = 14;
        $exc_dha_filter = null;
    
        if( $exc_dha == true ){
            $exc_dha_filter = "AND a.`franchise_groups_id` != {$fg_dha}";
        }

        if( count($postcode_arr) > 0 ){

            $postcode_imp = implode(",",$postcode_arr);

            // exclude DHA property count                               
            $sql_str = "
            SELECT count( ps.`property_services_id` ) AS p_count
            FROM `property_services` AS ps
            LEFT JOIN `property` AS p ON p.`property_id` = ps.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE ps.`service` =1
            AND p.`deleted` =0
            AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
            AND a.`status` = 'active'                
            AND a.`country_id` = {$this->config->item('country')}   
            AND p.`postcode` IN({$postcode_imp})
            {$exc_dha_filter}
            "; 
            
            $sql = $this->db->query($sql_str);      
            return $sql->row()->p_count; 

        }    
    
    }
    

    public function get_sats_serviced_property_service_price_sum_via_postcode($params){
        
        $postcode_arr = $params['postcode_arr'];
        $exc_dha = $params['exc_dha'];
        $fg_dha = 14;
        $exc_dha_filter = null;
    
        if( $exc_dha == true ){
            $exc_dha_filter = "AND a.`franchise_groups_id` != {$fg_dha}";
        }

        if( count($postcode_arr) > 0 ){

            $postcode_imp = implode(",",$postcode_arr);

            // exclude DHA property count                               
            $sql_str = "
            SELECT SUM( ps.`price` ) AS ps_price_tot  
            FROM `property_services` AS ps
            LEFT JOIN `property` AS p ON p.`property_id` = ps.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            WHERE ps.`service` =1
            AND p.`deleted` =0
            AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
            AND a.`status` = 'active'                
            AND a.`country_id` = {$this->config->item('country')}   
            AND p.`postcode` IN({$postcode_imp})
            {$exc_dha_filter}
            "; 
            $sql = $this->db->query($sql_str);      
            return $sql->row()->ps_price_tot; 

        }    
    
    }

    public function get_sales_report($params){

        if($params['sel_query'] && !empty($params['sel_query'])){
            $sel_query = $params['sel_query'];
        }else{
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('sales_report as sr');
        $this->db->join('main_log_type as mlt','mlt.main_log_type_id=sr.contact_type','left');

        if ($params['join_table'] > 0) {
            foreach ($params['join_table'] as $join_table) {
                if ($join_table == 'salesrep') {
                    $this->db->join('`staff_accounts` AS sa', 'sr.`staff_id` = sa.`StaffID`', 'left');
                }
            }
        }

        $this->db->where('sr.active',1);

        //optional filters
        if( $params['id'] && $params['id']!="" ){
            $this->db->where('sr.id', $params['id']);
        }
        if( $params['contact_type'] && $params['contact_type']!="" ){
            $this->db->where('sr.contact_type', $params['contact_type']);
        }
        if( $params['staff_id'] && $params['staff_id']!="" ){
            $this->db->where('sr.staff_id', $params['staff_id']);
        }
        if( $params['agency_id'] && $params['agency_id']!="" ){
            $this->db->where('sr.agency_id', $params['agency_id']);
        }
        if( $params['class_id'] && $params['class_id']!="" ){
            $this->db->where('sa.ClassID', $params['class_id']);
        }

        // custom filter
        if( isset($params['custom_where']) ){
            $this->db->where($params['custom_where']);
        }

        // group by
        if( isset($params['group_by']) && $params['group_by'] != '' ){
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

        // limit/offset
		if( isset($params['limit']) && $params['limit'] > 0 ){
			$this->db->limit( $params['limit'], $params['offset']);
		}	
        
        $query = $this->db->get();
        return $query;

    }

    /**
     *  get ic sales ajt only
     */
    function get_num_services_for_IC_sales( $salesrep,$from,$to,$exclude_dha = NULL){
        $country_id = COUNTRY;
        $str = "";					
        if( $from!='all' && $to!='all' ){
            $from2 = date("Y-m-d",strtotime(str_replace("/","-",$from)));
            $to2 = date("Y-m-d",strtotime(str_replace("/","-",$to)));
            $str = "
                AND CAST(j.`date` AS DATE) BETWEEN '{$from2}' AND '{$to2}'
                AND (
                    p.`is_nlm` IS NULL 
                    OR p.`is_nlm` = 0
                )
                AND p.deleted = 0
            ";
        }

        $dhaStr = "";
        if(is_numeric($exclude_dha) && $exclude_dha!="" && $exclude_dha>0){
            $dhaStr = " 
            AND a.franchise_groups_id!=14
            ";
        }

        // sales commission version on page parameter overrides global settings
		$sales_commission_ver = ( $this->input->get_post('ver') != '' )?$this->input->get_post('ver'):$this->config->item('sales_commission_ver');

        // sales commission version switch
        $sales_commission_str =  null;
		if( $sales_commission_ver == 'new' ){
			$sales_commission_str = 'AND ps.`is_payable` = 1';
		}else{
			$sales_commission_str = 'AND ps.`service` = 1';
		}
        
        $sql = "
            SELECT COUNT(ps.`property_services_id`) AS p_count
            FROM `property_services` AS ps
            LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            LEFT JOIN alarm_job_type AS ajt ON ps.alarm_job_type_id = ajt.`id`
            LEFT JOIN `jobs` AS j ON p.`property_id` = j.`property_id`
            WHERE a.`salesrep` = {$salesrep}     
            AND a.`country_id` = {$country_id}
            AND p.is_sales = 1 AND j.job_type = 'IC Upgrade' AND j.status = 'Completed'
            {$sales_commission_str}
            {$str}
            {$dhaStr}
        ";
       
        return $this->db->query($sql);
        
    }

    public function distinct_salesrep_for_sales_property($from,$to){

        $str = "";					
        if( $from!='all' && $to!='all' ){
            $from2 = date("Y-m-d",strtotime(str_replace("/","-",$from)));
            $to2 = date("Y-m-d",strtotime(str_replace("/","-",$to)));
            $str = "
                AND CAST(j.`date` AS DATE) BETWEEN '{$from2}' AND '{$to2}'
                AND (
                    p.`is_nlm` IS NULL 
                    OR p.`is_nlm` = 0
                )
            ";
        }

        $sr_sql = $this->db->query("
            SELECT DISTINCT a.`salesrep` , sa.`FirstName` , sa.`LastName`
            FROM `property_services` AS ps            
            LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `staff_accounts` AS sa ON sa.`StaffID` = a.`salesrep`
            LEFT JOIN `jobs` AS j ON p.`property_id` = j.`property_id`
            WHERE ps.`service` = 1
            AND p.is_sales = 1 AND j.status='Completed'
            AND a.`country_id` = {$this->config->item('country')}
            {$str}
            ORDER BY sa.`FirstName` ASC, sa.`LastName` ASC
        ");

        return $sr_sql;

    }

    //Server Side Datatable | Property Gained and Lost
    function property_gained_lost($limit,$start,$filter,$col,$dir)
    {

        $country_id = $this->config->item('country');
        if($country_id == 1){ //AU
            $exclude = "1448"; //Adams Test Agency
        } else {
            $exclude = "5536,6603";
        }  
        
        $search = $filter['search'];
        $status_changed_from = $filter['status_changed_from'];
        $status_changed_to = $filter['status_changed_to'];
        $view_type = $filter['view_type'];
        if($view_type == 1){
            $query = $this->db->select('p.property_id, p.address_1, p.address_2, p.address_3, a.agency_id, a.agency_name')
            ->from('property_services AS ps')
            ->join('property AS p', 'ps.property_id = p.property_id`','left')
            ->join('agency AS a', 'p.agency_id = a.agency_id','left')
            ->group_start()
            ->where('ps.service',1)
            ->where('ps.is_payable',1)
            ->where('p.is_sales !=',1)
            ->where("ps.status_changed >=", $status_changed_from)
            ->where("ps.status_changed <=", $status_changed_to)
            ->where_not_in('a.agency_id', $exclude)
            ->distinct('p.property_id')
            ->group_end()
            ->limit($limit,$start)
            ->order_by($col,$dir)
            ->get();
        } else {
            $from = $this->system_model->formatDate($status_changed_from,'Y-m-d');
            $to = $this->system_model->formatDate($status_changed_to,'Y-m-d');

            $lost_sql_str = "SELECT  
                DISTINCT(p.`property_id`),                 
                p.`address_1`,
                p.`address_2`,
                p.`address_3`,
                p.`state`,
                p.`postcode`,
                a.`agency_id`,
                a.`agency_name`
            FROM property_services AS ps
            INNER JOIN property AS p ON ps.property_id = p.property_id	
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
            WHERE (
                p.`property_id` NOT IN(
                    SELECT DISTINCT(p_inner.`property_id`)
                    FROM property_services AS ps_inner
                    INNER JOIN property AS p_inner ON ps_inner.property_id = p_inner.property_id	
                    WHERE ps_inner.service = 1
                )
                AND DATE(ps.`status_changed`) BETWEEN '$from' AND '$to'
            ) OR (
                (
                    p.`deleted` = 1 AND
                    DATE(p.`deleted_date`) BETWEEN '$from' AND '$to'
                ) OR (
                    p.`is_nlm` = 1 AND
                    DATE(p.`nlm_timestamp`) BETWEEN '$from' AND '$to'
                )                
            )
            AND p.is_sales != 1
            AND a.agency_id NOT IN($exclude)
            ORDER BY $col $dir
            LIMIT $limit OFFSET $start
            ";
            $query = $this->db->query($lost_sql_str);
        }
        return $query->result();
    }

    function property_gained_lost_count($filter)
    {

        $country_id = $this->config->item('country');
        if($country_id == 1){ //AU
            $exclude = "1448"; //Adams Test Agency
        } else {
            $exclude = "5536,6603";
        } 
        $status_changed_from = $filter['status_changed_from'];
        $status_changed_to = $filter['status_changed_to'];
        $view_type = $filter['view_type'];

        if($view_type == 1){
            $query = $this->db->select('p.property_id, p.address_1, p.address_2, p.address_3, a.agency_id, a.agency_name')
                ->from('property_services AS ps')
                ->join('property AS p', 'ps.property_id = p.property_id`','left')
                ->join('agency AS a', 'p.agency_id = a.agency_id','left')
                ->where('ps.service',1)
                ->where('ps.is_payable',1)
                ->where('p.is_sales !=',1)
                ->where("ps.status_changed >=", $status_changed_from)
                ->where("ps.status_changed <=", $status_changed_to)
                ->where_not_in('a.agency_id', $exclude)
                ->distinct('p.property_id')
                ->get();
        } else {
            $from = $this->system_model->formatDate($status_changed_from,'Y-m-d');
            $to = $this->system_model->formatDate($status_changed_to,'Y-m-d');

            $lost_sql_str = "SELECT  
                DISTINCT(p.`property_id`),                 
                p.`address_1`,
                p.`address_2`,
                p.`address_3`,
                p.`state`,
                p.`postcode`,
                a.`agency_id`,
                a.`agency_name`
                FROM property_services AS ps
                INNER JOIN property AS p ON ps.property_id = p.property_id	
                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
                WHERE (
                    p.`property_id` NOT IN(
                        SELECT DISTINCT(p_inner.`property_id`)
                        FROM property_services AS ps_inner
                        INNER JOIN property AS p_inner ON ps_inner.property_id = p_inner.property_id	
                        WHERE ps_inner.service = 1
                    )
                    AND DATE(ps.`status_changed`) BETWEEN '$from' AND '$to'
                ) OR (
                    (
                        p.`deleted` = 1 AND
                        DATE(p.`deleted_date`) BETWEEN '$from' AND '$to'
                    ) OR (
                        p.`is_nlm` = 1 AND
                        DATE(p.`nlm_timestamp`) BETWEEN '$from' AND '$to'
                    )                
                )
                AND p.is_sales != 1
                AND a.agency_id NOT IN($exclude)";
            $query = $this->db->query($lost_sql_str);
        }
        return $query->num_rows();
    }
     // End Server Side Datatable | Property Gained and Lost



    public function get_support_ticket_help_topic($option){

        $option_str = null;

        switch( $option ){
            case 1:
                $option_str = 'Bug';
            break;
            case 2:
                $option_str = 'Suggestion';
            break;
            case 3:
                $option_str = 'Feature Needed';
            break;
            case 4:
                $option_str = 'Feature Wanted';
            break;
            case 5:
                $option_str = 'Other';
            break;
        }

        return $option_str;

    }

    public function get_support_ticket_priority($option){

        $option_str = null;

        switch( $option ){
            case 1:
                $option_str = 'Low';
            break;
            case 2:
                $option_str = 'Medium';
            break;
            case 3:
                $option_str = 'High';
            break;
        }

        return $option_str;        

    }

    public function get_support_ticket_status($option){

        $option_str = null;

        switch( $option ){
            case 1:
                $option_str = 'Pending';
            break;
            case 2:
                $option_str = 'Declined';
            break;
            case 3:
                $option_str = 'Progress';
            break;
            case 4:
                $option_str = 'Completed';
            break;
        }

        return $option_str;
        
    }

    public function sales_snapsho_about_qry()
    {
        $this->db->select("
            ss.sales_snapshot_sales_rep_id, 
            ss_s.name as status_name, 
            ss.sales_snapshot_id, 
            ss.sales_snapshot_status_id as ss_status_id, 
            ss.details, 
            ss.date, 
            ss.properties, 
            a.agency_id, 
            a.agency_name, 
            sr.sub_region_id as postcode_region_id, 
            sr.subregion_name as postcode_region_name, 
            sa.FirstName AS first_name, 
            sa.LastName AS last_name 
        ");

        $this->db->from("sales_snapshot AS ss");
        $this->db->join("agency AS a", "a.agency_id = ss.agency_id", "left");
        $this->db->join("sub_regions AS sr", "sr.sub_region_id = a.postcode_region_id", "left");
        $this->db->join("sales_snapshot_status AS ss_s", "ss_s ON ss_s.sales_snapshot_status_id = ss.sales_snapshot_status_id", "left");
        $this->db->join("staff_accounts AS sa", "sa.StaffID = ss.sales_snapshot_sales_rep_id", "left");
        $this->db->where("ss.country_id", $this->config->item("country"));
        $this->db->where("(sa.ClassID = 5 OR sa.StaffID = 2165 OR sa.StaffID = 2186 OR sa.StaffID = 2296)");
        $this->db->where("sa.deleted", 0);
        $this->db->where("sa.active", 1);
        $this->db->where("sa.StaffID", $this->session->staff_id);
        $this->db->order_by("ss.date", "desc");
        
        $result = $this->db->get()->result();
        $last_query = $this->db->last_query(); 

        return $last_query;
    }


}
