<?php

class Alarms_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
    }

    
    public function getNewAlarms($params){

        if($params['sel_query'] && !empty($params['sel_query'])){
            $sel_query = $params['sel_query'];
        }else{
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('alarm as alrm');
        $this->db->join('jobs as j','j.id = alrm.job_id','inner');
        $this->db->join('property as p','p.property_id = j.property_id','left');
        $this->db->join('agency as a','a.agency_id = p.agency_id','left');
        $this->db->join('alarm_pwr as alrm_p','alrm_p.alarm_pwr_id = alrm.alarm_power_id','inner');
        $this->db->join('alarm_reason as alrm_r','alrm_r.alarm_reason_id = alrm.alarm_reason_id','inner');
        $this->db->join('staff_accounts as sa','sa.StaffID = j.assigned_tech','inner');
        $this->db->where('alrm.alarm_id >',0);


        //FILTERS
        if($params['new']!="" && $params['new']){
            $this->db->where('alrm.new', $params['new']);
        }	
        
        if($params['state']!="" && $params['state']){
            $this->db->where('p.state', $params['state']);
        }
        
        if($params['alarm_pwr']!="" && $params['alarm_pwr']){
            $this->db->where('alrm_p.alarm_pwr_id', $params['alarm_pwr']);
        }
        
        if($params['alarm_reason']!="" && $params['alarm_reason']){
            $this->db->where('alrm_r.alarm_reason_id', $params['alarm_reason']);
        }

        if($params['agency_filter']!="" && $params['agency_filter']){
            $this->db->where('a.agency_id', $params['agency_filter']);
        }

        if($params['job_type']!="" && $params['job_type']){
            $this->db->where('j.job_type', $params['job_type']);
        }
        
        // date filter
        if($params['filterDate']!='' && $params['filterDate']){
            if( $params['filterDate']['from']!="" && $params['filterDate']['to']!="" ){
                $filter_date = "j.`date` BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}'";
                $this->db->where($filter_date);
            }			
        }

        // tech filter
        if($params['tech']!="" && $params['tech']){
            $this->db->where('j.assigned_tech', $params['tech']);
        }

        //active tech filter
        if($params['active_tech']!="" && $params['active_tech']){
            $this->db->where('sa.active', $params['active_tech']);
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


        return $this->db->get();

    }



    public function getDiscardedAlarms($params){

        if ($params['sel_query'] && !empty($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('alarm as a');
        $this->db->join('jobs as j','j.id = a.job_id','inner');
        $this->db->join('alarm_discarded_reason as adr','adr.id = a.ts_discarded_reason','left');
        $this->db->join('property as p','p.property_id = j.property_id','left');
        $this->db->join('alarm_pwr as ap','ap.alarm_pwr_id = a.alarm_power_id','left');
        $this->db->join('alarm_type as at','at.alarm_type_id = a.alarm_type_id','left');
        $this->db->join('staff_accounts as sa','sa.StaffID = j.assigned_tech','left');
        $this->db->where('a.ts_discarded',1);


        //FILTERS
        // search reason
        if($params['reason'] && $params['reason']!=""){
            $this->db->where('a.ts_discarded_reason', $params['reason']);
        }
        
        // search state
        if($params['state'] && $params['state']!=""){
            $this->db->where('p.state', $params['state']);
        }
        
        // date filter
        if( $params['filterDate']['from']!="" && $params['filterDate']['to']!="" ){
            $date_filter_str= " CAST( j.`date` AS Date )  BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}' ";
            $this->db->where($date_filter_str);
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

    public function get_alarm_power($params){

        if($params['sel_query'] && !empty($params['sel_query'])){
            $sel_query = $params['sel_query'];
        }else{
            $sel_query = "*";
        }

        $this->db->select($sel_query);
        $this->db->from('alarm_pwr as ap');
        $this->db->where('ap.alarm_pwr_id >', 0);

        if($params['alarm_pwr_id']!=""){
            $this->db->where('ap.alarm_pwr_id', $params['alarm_pwr_id']);
        }

        if($params['alarm_reason']!=""){
            $this->db->where('ap.alarm_reason_id', $params['alarm_reason']);
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

    public function getAlarmType(){
        return $this->db->select('alarm_type_id, alarm_type')
            ->from('alarm_type')
            ->where('alarm_job_type_id',2)
            ->order_by('alarm_type', 'asc')
            ->get();
    }


}

