<?php

class Credits_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
    }

    public function get_credit_request($params)
    {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`credit_requests` AS cr');

        // set joins
		if( $params['join_table'] > 0 ){
			
			foreach(  $params['join_table'] as $join_table ){
				
				if( $join_table == 'jobs' ){
					$this->db->join('`jobs` AS j', 'cr.`job_id` = j.`id`', 'left');
				}if( $join_table == 'property' ){
					$this->db->join('`property` AS p', 'j.`property_id` = p.`property_id`', 'left');
				}if( $join_table == 'agency' ){
					$this->db->join('`agency` AS a', 'p.`agency_id` = a.`agency_id`', 'left');
				}if( $join_table == 'req_by' ){
					$this->db->join('`staff_accounts` AS rb', 'cr.`requested_by` = rb.`StaffID`', 'left');
				}if( $join_table == 'who' ){
					$this->db->join('`staff_accounts` AS who', 'cr.`who` = who.`StaffID`', 'left');
				}if( $join_table == 'agency_priority' ){
					$this->db->join('agency_priority as aght', 'a.agency_id = aght.agency_id', 'left');
				}if( $join_table == 'agency_priority_marker_definition' ){
					$this->db->join('agency_priority_marker_definition as apmd', 'aght.priority = apmd.priority', 'left');
				}
			}
			
		}

        // custom joins
		if( isset($params['custom_joins']) && $params['custom_joins'] != '' ){
			$this->db->join($params['custom_joins']['join_table'],$params['custom_joins']['join_on'], $params['custom_joins']['join_type']);
        }
		
        // filter
        if ( is_numeric($params['cr_id']) ) {
            $this->db->where('cr.`credit_request_id`', $params['cr_id']);
        }
        if ( is_numeric($params['job_id']) ) {
            $this->db->where('cr.`job_id`', $params['job_id']);
        }
        if ( is_numeric($params['deleted']) ) {
            $this->db->where('cr.`deleted`', $params['deleted']);
        }
        if ( is_numeric($params['active']) ) {
            $this->db->where('cr.`active`', $params['active']);
        }
		if ( is_numeric($params['country_id']) ) {
            $this->db->where('cr.`country_id`', $params['country_id']);
        }
        if ( $params['requested_by'] != '' ) {
            $this->db->where('cr.`requested_by`', $params['requested_by']);
        }
        if ( $params['agency'] != '' ) {
            $this->db->where('a.`agency_id`', $params['agency']);
        }

        if ( is_numeric($params['refund_request']) ) {
            $this->db->where('cr.`refund_request`', $params['refund_request']);
        }

        if( is_numeric($params['result']) ){
            $this->db->where('cr.`result`', $params['result']);
		}else if( $params['result'] == 'ALL' ){
			// dont use result filter, should show all results
		}else if( $params['result'] == 'pending' ){
            $this->db->where('cr.`result`', NULL);
        }
        
        if( !empty($params['dor_search_span']) ){
			if( $params['dor_search_span']['from'] != '' && $params['dor_search_span']['to'] != '' ){			
                $this->db->where("CAST(cr.`date_of_request` AS Date) BETWEEN '{$params['dor_search_span']['from']}' AND '{$params['dor_search_span']['to']}'");
            }           	
        }
        
        //jobs id > like search
        if ( is_numeric($params['job_id_like']) ) {
            $this->db->like('cr.`job_id`', $params['job_id_like']);
        }

      	// custom filter
        if( isset($params['custom_where']) ){
             $this->db->where($params['custom_where']);
        }
		
		// custom filter arr
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
		
		// custom filter
        if( isset($params['custom_sort']) ){
              $this->db->order_by($params['custom_sort']);
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

    /**
     * Check pdf files if already exist
     * @params $id > credit_request id
     * @params $col > table col
     * return credit_requests col value else false
     */
    public function check_summary_pdf_file_exist($id,$col){
        $this->db->select('*');
        $this->db->from('credit_requests');
        $this->db->where('credit_request_id', $id);
        $this->db->where('country_id',$this->config->item('country'));
        $query = $this->db->get();
        $row = $query->row();
        
        if($row->$col != "" || $row->$col != NULL){
            return $row->$col;
        }else{
            return false;
        }
    }
    

}
