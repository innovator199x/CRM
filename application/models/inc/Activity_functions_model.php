<?php

class Activity_functions_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
    }


    public function getActivity($params){

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
        $this->db->join('agency as a','a.agency_id = p.agency_id','left');        
        // sales commission version switch        
		if( $sales_commission_ver == 'new' ){
			$this->db->where('ps.is_payable',1);
		}else{
			$this->db->where('ps.service',1);
		}
        $this->db->where('a.country_id',$this->config->item('country'));


        //Optional Filters

        //Date from / to filter
        if(!empty($params['date_from_filter']) && !empty($params['date_to_filter'])){

            $date_from2 = $this->system_model->formatDate($params['date_from_filter']);
            $date_to2 = $this->system_model->formatDate($params['date_to_filter']);

            $this->db->where('CAST(ps.`status_changed` AS DATE) >=', $date_from2);
            $this->db->where('CAST(ps.`status_changed` AS DATE) <=', $date_to2);
            $this->db->where('( p.`is_nlm` IS NULL OR p.`is_nlm` = 0 )');

        }

        //state filter
        if(!empty($params['state_filter'])){
            $this->db->where('a.state',$params['state_filter']);
        }

         //agency filter
         if(!empty($params['agency_filter'])){
            $this->db->where('a.agency_id',$params['agency_filter']);
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
   



}
