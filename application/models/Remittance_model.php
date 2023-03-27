<?php

class Remittance_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }


    // get Invoice Credit
	public function getUnpaidJobs($params){




        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('jobs as j');
        $this->db->join('property as p','p.property_id = j.property_id','left');
        $this->db->join('agency as a','a.agency_id = p.agency_id','left');
        #$this->db->join('invoice_credits as inv_cred','inv_cred.job_id = j.id','inner');
        $this->db->where('j.id >', 0);
        
        // custom joins
		if( isset($params['custom_joins']) && $params['custom_joins'] != '' ){
			$this->db->join($params['custom_joins']['join_table'],$params['custom_joins']['join_on'], $params['custom_joins']['join_type']);
        }


        if( $params['agency_id'] != "" ){
            $this->db->where('p.agency_id', $params['agency_id']);
        }
        
        if( count($params['multi_agency_filter']) > 0 ){            
            $this->db->where_in('p.agency_id', $params['multi_agency_filter']);
		}
		
		if( $params['job_date'] != "" ){
            $this->db->where('j.date', $params['job_date']);
		}

		if( is_numeric($params['credit_reason']) && $params['credit_reason'!=""] ){
            $this->db->where('inv_cred.credit_reason', $params['credit_reason']);
		}
		
		if($params['filterDate']!=''){
			if( $params['filterDate']['from']!="" && $params['filterDate']['to']!="" ){
                $filter_date = "( j.`date` BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}' ) ";
                $this->db->where($filter_date);
			}			
        }
        
        if($params['phrase']!=''){
			$filter_phrase = "
			 (
				(CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$params['phrase']}%') OR
                (a.`agency_name` LIKE '%{$params['phrase']}%') OR 
                (j.`invoice_amount` LIKE '%{$params['phrase']}%') OR 
                (j.`id` LIKE '%{$params['phrase']}%')
			 )
             ";
             $this->db->where($filter_phrase);
        }
        
        if($params['p_address']!=''){
			$filter_phrase = "
            CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%".strtolower(trim($params['p_address']))."%'
             ";
             $this->db->where($filter_phrase);
		}


        // custom filter
        if( isset($params['custom_filter']) ){
            $this->db->where($params['custom_filter']);
        }

        // sort
        if (isset($params['sort_list'])) {
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }

        if( $params['group_by'] && !empty($params['group_by']) ){
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
    
    public function getPaymentTypes(){
        $query = $this->db->select('*')->from('payment_types')->where('active',1)->get();
		return $query;
	}


    // get Invoice Credit
	public function get_agency_payments($params){




        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('agency_payments AS agen_pay');
        $this->db->join('`payment_types` AS pt', 'agen_pay.`payment_type` = pt.`payment_type_id`', 'left');        
        
        // set joins
        if ($params['join_table'] > 0) {

            foreach ($params['join_table'] as $join_table) {

                if ($join_table == 'agency_payments_agencies') {
                    $this->db->join('`agency_payments_agencies` AS agen_pay_a', 'agen_pay.`agency_payments_id` = agen_pay_a.`agency_payments_id`', 'inner');
                    $this->db->join('`agency` AS a', 'agen_pay_a.`agency_id` = a.`agency_id`', 'left');
                }

                if ($join_table == 'agency_payments_jobs') {
                    $this->db->join('`agency_payments_jobs` AS agen_pay_j', 'agen_pay.`agency_payments_id` = agen_pay_j.`agency_payments_id`', 'inner');
                    $this->db->join('`jobs` AS j', 'agen_pay_j.`job_id` = j.`id`', 'left');
                }                
              
            }
        }        
        
        // custom joins
		if( isset($params['custom_joins']) && $params['custom_joins'] != '' ){
			$this->db->join($params['custom_joins']['join_table'],$params['custom_joins']['join_on'], $params['custom_joins']['join_type']);
        }


        if( $params['agency_payments_id'] > 0 ){
            $this->db->where('agen_pay.agency_payments_id', $params['agency_payments_id']);
        }

		if( $params['job_date'] != "" ){
            $this->db->where('agen_pay.date', $params['job_date']);
        }
        
        if($params['search_from_to']!=''){

			if( $params['search_from_to']['from']!="" && $params['search_from_to']['to']!="" ){
                $filter_date = "( agen_pay.`date` BETWEEN '{$params['search_from_to']['from']}' AND '{$params['search_from_to']['to']}' ) ";
                $this->db->where($filter_date);
            }	
            		
        }

		if( $params['amount'] != "" ){
            $this->db->where('agen_pay.amount', $params['amount']);
        }
        
        /*
		if( $params['reference'] != "" ){
            $this->db->where('agen_pay.reference', $params['reference']);
        }
        */

        if( $params['reference'] != "" ){
            $this->db->like('agen_pay.reference', $params['reference']);
        }
        
        
		if( $params['payment_type'] != "" ){
            $this->db->where('agen_pay.payment_type', $params['payment_type']);
        }

        if( $params['agency_id'] > 0 ){            
            $this->db->where('agen_pay_a.agency_id', $params['agency_id']);
        }
        
        if( count($params['multi_agency_filter']) > 0 ){            
            $this->db->where_in('agen_pay_a.agency_id', $params['multi_agency_filter']);
        }
        
        if( $params['payment_type_id'] != "" ){
            $this->db->where('pt.payment_type_id', $params['payment_type_id']);
        }

        if($params['phrase']!=''){
			$filter_phrase = "
			 (
				(CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$params['phrase']}%') OR
                (a.`agency_name` LIKE '%{$params['phrase']}%') OR 
                (j.`invoice_amount` LIKE '%{$params['phrase']}%') OR 
                (j.`id` LIKE '%{$params['phrase']}%')
			 )
             ";
             $this->db->where($filter_phrase);
        }


        // custom filter
        if( isset($params['custom_filter']) ){
            $this->db->where($params['custom_filter']);
        }

        // sort
        if (isset($params['sort_list'])) {
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }

        if( $params['group_by'] && !empty($params['group_by']) ){
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


    public function get_agen_pay_bank_deposit($bank_deposit_id=null){

        $bank_deposit_arr = array('No','Yes');
        if( isset($bank_deposit_id) ){            
            return $bank_deposit_arr[$bank_deposit_id];
        }else{
            return $bank_deposit_arr;
        }

    }

    public function get_agen_pay_remittance($remittance_id=null){

        $remittance_arr = array('No','Yes','Not Needed');
        if( isset($remittance_id) ){            
            return $remittance_arr[$remittance_id];
        }else{
            return $remittance_arr;
        }

    }
  

}
