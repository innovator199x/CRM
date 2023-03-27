<?php

class Resources_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
    }

    public function get_tech_doc($params)
    {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('technician_documents AS td');
        $this->db->join('tech_doc_header AS tdh', 'tdh.tech_doc_header_id = td.tech_doc_header_id', 'left');

        // custom joins
		if( isset($params['custom_joins']) && $params['custom_joins'] != '' ){
			$this->db->join($params['custom_joins']['join_table'],$params['custom_joins']['join_on'], $params['custom_joins']['join_type']);
        }
		
        // filter
        if ( $params['country_id'] > 0  ) {
            $this->db->where('tdh.`country_id`', $params['country_id']);
        }

        if ( $params['header_id'] > 0  ) {
            $this->db->where('td.`tech_doc_header_id`', $params['header_id']);
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


    public function get_tech_doc_header($params)
    {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('tech_doc_header AS tdh');        

        // custom joins
		if( isset($params['custom_joins']) && $params['custom_joins'] != '' ){
			$this->db->join($params['custom_joins']['join_table'],$params['custom_joins']['join_on'], $params['custom_joins']['join_type']);
        }
		
        // filter
        if ( $params['header_id'] > 0  ) {
            $this->db->where('tdh.`tech_doc_header_id`', $params['header_id']);
        }
        
        if ( $params['country_id'] > 0  ) {
            $this->db->where('tdh.`country_id`', $params['country_id']);
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

    public function get_dynamic_link_and_icon($params){

        $data_arr = [];

        if( $params['type'] == 1 ){ // file
													
            if( strpos($params['filename'],".doc") != false ){ // wordoc
                $file_icon = 'file-word-o';
            }else if( strpos($params['filename'],".pdf") != false ){ // pdf
                $file_icon = 'file-pdf-o';
            }else{ // other
                $file_icon = 'file-o';
            }
            
            $tech_doc_cont = "/uploads/tech_documents/{$params['filename']}";

            
        
        }else if( $params['type'] == 2 ){ // link
                                                            
            // youtube
            if( strpos($params['url'],"youtu") != false ){
                $file_icon = 'youtube-play';
            }else{ // other link
                $file_icon = 'external-link';
            }
            
            $tech_doc_cont = $params['url'];
            
        }

        return $data_arr = array(
            'file_icon' => $file_icon,
            'tech_doc_cont' => $tech_doc_cont,
        );

    }


    public function about_page_text($header_id){
        
        $about_text = null;

        switch($header_id){

            case 8: // SWMS (Safe Work Method Statement)
                $about_text = 'This page displays all the SWMS (Safe Work Method Statements)';
            break;

            case 3: // DOOR KNOCK LETTERS
                $about_text = 'This page displays templates in different languages';
            break;

            case 5: // FORMS
                $about_text = 'This page displays all forms you need to submit';
            break;

        }

        return $about_text;


    }


    public function get_resources_header_id($resource_heading){


        if( $resource_heading == 'Multi-Lingual' ){ // DOOR KNOCK LETTERS

            if( $this->config->item('country') == 1 ){ // AU

                if( ENVIRONMENT == 'production' ){ // LIVE
                    $res_id = 3;    
                }else{ // DEV  
                    $res_id = 3;  
                }  

            }else if( $this->config->item('country') == 2 ){ // NZ
                $res_id = 5; 
            }
           
        }else if( $resource_heading == 'SWMS' ){ // SWMS (Safe Work Method Statement)
            
            if( $this->config->item('country') == 1 ){ // AU

                if( ENVIRONMENT == 'production' ){ // LIVE
                    $res_id = 12;    
                }else{ // DEV  
                    $res_id = 8;  
                }  

            }else if( $this->config->item('country') == 2 ){ // NZ
                $res_id = 15; 
            }

        }else if( $resource_heading == 'Forms' ){
           
            if( $this->config->item('country') == 1 ){ // AU

                if( ENVIRONMENT == 'production' ){ // LIVE
                    $res_id = 10;    
                }else{ // DEV  
                    $res_id = 5;  
                }  

            }else if( $this->config->item('country') == 2 ){ // NZ
                $res_id = 13; 
            }

        }else if( $resource_heading == 'Contact List' ){
           
            if( $this->config->item('country') == 1 ){ // AU

                if( ENVIRONMENT == 'production' ){ // LIVE
                    $res_id = 18;    
                }else{ // DEV  
                    $res_id = 9;  
                }  

            }else if( $this->config->item('country') == 2 ){ // NZ
                $res_id = 20; 
            }

        }
        
        return $res_id;

    }

    //GET admin staffs details from DB / CHOPS
    public function getAdminsStaffs() {
        return $this->db->select('`sa`.`FirstName`, `sa`.`LastName`, `sa`.`Email`, `sa`.`ContactNumber`, `sa`.`sa_position`, `sa`.`profile_pic`, `sd`.`state_full_name`')
        ->from('`staff_accounts` AS sa')
        ->join('staff_states as ss', 'sa.StaffID = ss.StaffID', 'left')
        ->join('states_def as sd', 'ss.StateID = sd.StateID', 'left')
        ->where('sa.ClassID !=', 6)
        ->where('sd.state_full_name !=', NULL)
        ->order_by('sd.state_full_name','asc')
        ->get()->result_object();
        $this->db->get('staff_accounts');
    }//endfct

    //GET admin staffs details with NULL state from DB / CHOPS
    public function getAdminsStaffsNull() {
        return $this->db->select('`sa`.`FirstName`, `sa`.`LastName`, `sa`.`Email`, `sa`.`ContactNumber`, `sa`.`sa_position`, `sa`.`profile_pic`, `sd`.`state_full_name`')
        ->from('`staff_accounts` AS sa')
        ->join('staff_states as ss', 'sa.StaffID = ss.StaffID', 'left')
        ->join('states_def as sd', 'ss.StateID = sd.StateID', 'left')
        ->where('sa.ClassID !=', 6)
        ->where('sd.state_full_name', NULL)
        ->order_by('sd.state_full_name','asc')
        ->get()->result_object();
        $this->db->get('staff_accounts');
    }//endfct

    //GET tech staffs details from DB / CHOPS
    public function getTechsStaffs() {
        return $this->db->select('`sa`.`FirstName`, `sa`.`LastName`, `sa`.`Email`, `sa`.`ContactNumber`, `sa`.`sa_position`, `sa`.`profile_pic`, `sd`.`state_full_name`')
        ->from('`staff_accounts` AS sa')
        ->join('staff_states as ss', 'sa.StaffID = ss.StaffID', 'left')
        ->join('states_def as sd', 'ss.StateID = sd.StateID', 'left')
        ->where('sa.ClassID', 6)
        ->order_by('sd.state_full_name','asc')
        ->get()->result_object();
        $this->db->get('staff_accounts');
    }//endfct

    //GET tech staffs details with NULL state from DB / CHOPS
    public function getTechsStaffsNull() {
        return $this->db->select('`sa`.`FirstName`, `sa`.`LastName`, `sa`.`Email`, `sa`.`ContactNumber`, `sa`.`sa_position`, `sa`.`profile_pic`, `sd`.`state_full_name`')
        ->from('`staff_accounts` AS sa')
        ->join('staff_states as ss', 'sa.StaffID = ss.StaffID', 'left')
        ->join('states_def as sd', 'ss.StateID = sd.StateID', 'left')
        ->where('sa.ClassID', 6)
        ->where('sd.state_full_name', NULL)
        ->order_by('sd.state_full_name','asc')
        ->get()->result_object();
        $this->db->get('staff_accounts');
    }//endfct


}
