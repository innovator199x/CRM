<?php

class Vehicles_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
    }

    public function get_vehicles($params)
    {
        if( isset($params['sel_query']) ){
            $sel_query = $params['sel_query'];
        }else{
            $sel_query = '*';
        }
        
        $this->db->select($sel_query, false);
        $this->db->from('`vehicles` AS v');
        $this->db->join('`staff_accounts` AS sa', 'sa.`StaffID` = v.`StaffID`','left');        

        if( isset($params['country_id'])  && $params['country_id'] != ''){
            $this->db->where('v.`country_id`', $params['country_id']);
        }


        if( isset($params['tech_vehicle']) && $params['tech_vehicle']!=""){
            $this->db->where('v.`tech_vehicle`', $params['tech_vehicle']);
		}

        if( isset($params['v_status']) && $params['v_status']!=""){
            $this->db->where('v.`active`', 1);
            $this->db->order_by('sa.`FirstName`', 'ASC');
        }

        if( isset($params['staff_id']) && $params['staff_id']!=""){
            $this->db->where('v.`StaffID`', $params['staff_id']);
            //$this->db->order_by('sa.`FirstName`', 'ASC');
        }

        // sort
        if( isset($params['sort_list']) ){
            foreach( $params['sort_list'] as $sort_arr ){
                if( $sort_arr['order_by']!="" && $sort_arr['sort']!='' ){
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }
        
        // limit
        if( isset($params['limit']) && $params['limit'] > 0 ){
            $this->db->limit( $params['limit'], $params['offset']);
        }	


        $query = $this->db->get();
        return $query;

    }
    
    public function get_vehicle_details($param){
        $query = $this->db
            ->select('*')
            ->from('vehicles')
            ->where('vehicles_id', $param)
            ->get();

        if ($query->num_rows() > 0) {
            return $query->row(); 
        }
    }


    
    public function get_driver($param){
        $query = $this->db
            ->select('CONCAT(staff_accounts.FirstName, " ", staff_accounts.LastName) AS name')
            ->from('staff_accounts')
            ->where('StaffID', $param)
            ->limit(1)
            ->get();

        if ($query->num_rows() > 0) {
            return $query->row(); 
        }
    }

    public function get_vehicle_details_kms($param){
        $query = $this->db
            ->select('`kms`,`kms_updated`')
            ->from('kms')
            ->where('vehicles_id', $param)
            ->limit(1)
            ->order_by('kms_id', 'desc')
            ->get();

        if ($query->num_rows() > 0) {
            return $query->row(); 
        }
    }

    function all_logs_count($vehicles_id)
    {   
        $query = $this->db
        ->where('vehicles_id',$vehicles_id)
        ->get('vehicles_log');
        return $query->num_rows();  

    }
    
    function all_logs($vehicles_id,$limit,$start,$col,$dir)
    {   
       $query = $this->db
                ->limit($limit,$start)
                ->select('`vehicles_log.vehicles_log_id`,`vehicles_log.date`,`vehicles_log.price`,`vehicles_log.details`, CONCAT(staff_accounts.FirstName, " ", staff_accounts.LastName) AS name')
                ->join('`staff_accounts`', 'staff_accounts.`StaffID` = vehicles_log.`staff_id`','left')
                ->where('vehicles_id',$vehicles_id)
                ->order_by($col,$dir)
                ->get('vehicles_log');
        
        if($query->num_rows()>0)
        {
            return $query->result(); 
        }
        else
        {
            return null;
        }
        
    }

    function logs_search($vehicles_id,$limit,$start,$search,$col,$dir)
    {
        $query = $this->db
                ->select('`vehicles_log.vehicles_log_id`,`vehicles_log.date`,`vehicles_log.price`,`vehicles_log.details`, CONCAT(staff_accounts.FirstName, " ", staff_accounts.LastName) AS name')
                ->join('`staff_accounts`', 'staff_accounts.`StaffID` = vehicles_log.`staff_id`')
                ->where('vehicles_id',$vehicles_id)
                ->like(function($q) use($search){
                    $q->like('vehicles_log.vehicles_log_id',$search)
                    ->or_like('vehicles_log.date',$search)
                    ->or_like('vehicles_log.date',$search)
                    ->or_like('vehicles_log.details',$search)
                    ->or_like('staff_accounts.FirstName',$search)
                    ->or_like('staff_accounts.LastName',$search);
                })
                ->limit($limit,$start)
                ->order_by($col,$dir)
                ->get('vehicles_log');
        if($query->num_rows()>0)
        {
            return $query->result();  
        }
        else
        {
            return null;
        }
    }

    function logs_search_count($vehicles_id,$search)
    {
        $query = $this->db
                ->select('`vehicles_log.vehicles_log_id`,`vehicles_log.date`,`vehicles_log.price`,`vehicles_log.details`, CONCAT(staff_accounts.FirstName, " ", staff_accounts.LastName) AS name')
                ->join('`staff_accounts`', 'staff_accounts.`StaffID` = vehicles_log.`staff_id`','left')
                ->where('vehicles_id',$vehicles_id)
                ->like(function($q) use($search){
                    $q->like('vehicles_log.vehicles_log_id',$search)
                    ->or_like('vehicles_log.date',$search)
                    ->or_like('vehicles_log.date',$search)
                    ->or_like('vehicles_log.details',$search)
                    ->or_like('staff_accounts.FirstName',$search)
                    ->or_like('staff_accounts.LastName',$search);
                })
                ->get('vehicles_log');
        return $query->num_rows();
    }

    //Server Side Datatable | Log Files
    function all_log_files_count($log_id)
    {   
        $query = $this->db
        ->where('vehicle_log_id',$log_id)
        ->get('vehicle_log_files');
        return $query->num_rows();  

    }
    
    function all_log_files($log_id,$limit,$start,$col,$dir)
    {   
       $query = $this->db
                ->limit($limit,$start)
                ->where('vehicle_log_id',$log_id)
                ->order_by($col,$dir)
                ->get('vehicle_log_files');
        
        if($query->num_rows()>0)
        {
            return $query->result(); 
        }
        else
        {
            return null;
        }
        
    }

    function log_files_search($log_id,$limit,$start,$search,$col,$dir)
    {
        $query = $this->db
                ->where('vehicle_log_id',$log_id)
                ->like('filename',$search)
                ->limit($limit,$start)
                ->order_by($col,$dir)
                ->get('vehicle_log_files');
        if($query->num_rows()>0)
        {
            return $query->result();  
        }
        else
        {
            return null;
        }
    }

    function log_files_search_count($log_id,$search)
    {
        $query = $this->db
            ->where('vehicle_log_id',$log_id)
            ->like('filename',$search)
            ->get('vehicle_log_files');
        return $query->num_rows();
    }

    //Server Side Datatable | Tool START
    function all_tools_count($vehicles_id) {   
        $query = $this->db
            ->where('assign_to_vehicle',$vehicles_id)
            ->get('tools');
        
        return $query->num_rows();  
        }
        
        function all_tools($vehicles_id,$limit,$start,$col,$dir) {   
           $query = $this->db
                    ->limit($limit,$start)
                    ->select('`item_id`,`brand`,`description`')
                    ->where('assign_to_vehicle',$vehicles_id)
                    ->order_by($col,$dir)
                    ->get('tools');
            
            if($query->num_rows()>0) {
                return $query->result(); 
            } else {
                return null;
            }
        }
    
        function tools_search($vehicles_id,$limit,$start,$search,$col,$dir) {
            $query = $this->db
                    ->select('`item_id`,`brand`,`description`')
                    ->where('assign_to_vehicle',$vehicles_id)
                    ->like(function($q) use($search){
                        $q->like('item_id',$search)
                            ->or_like('brand',$search)
                            ->or_like('description',$search);
                    })
                    ->limit($limit,$start)
                    ->order_by($col,$dir)
                    ->get('tools');
            if($query->num_rows()>0) {
                return $query->result();  
            } else {
                return null;
            }
        }
    
        function tools_search_count($vehicles_id,$search)
        {
            $query = $this->db
                    ->select('`item_id`,`brand`,`description`')
                    ->where('assign_to_vehicle',$vehicles_id)
                    ->like(function($q) use($search){
                        $q->like('item_id',$search)
                            ->or_like('brand',$search)
                            ->or_like('description',$search);
                    })
                    ->get('tools');
            return $query->num_rows();
        } 
    //Server Side Datatable | Tool END

     public function select_file($id){
        $query = $this->db
            ->select('`vehicles_id`,`filename`')
            ->from('vehicle_files')
            ->where('vehicle_files_id', $id)
            ->limit(1)
            ->get();

        if ($query->num_rows() > 0) {
            return $query->row(); 
        }
    }


    //Server Side Datatable | Files START
    function all_files_count($vehicles_id) {   
        $query = $this->db
            ->where('vehicles_id',$vehicles_id)
            ->get('vehicle_files');
        
        return $query->num_rows();  
        }
        
        function all_files($vehicles_id,$limit,$start,$col,$dir) {   
           $query = $this->db
                    ->limit($limit,$start)
                    ->select('`vehicle_files_id`,`filename`,`date`')
                    ->where('vehicles_id',$vehicles_id)
                    ->order_by($col,$dir)
                    ->get('vehicle_files');
            
            if($query->num_rows()>0) {
                return $query->result(); 
            } else {
                return null;
            }
        }
    
        function files_search($vehicles_id,$limit,$start,$search,$col,$dir) {
            $query = $this->db
                    ->select('`vehicle_files_id`,`filename`,`date`')
                    ->where('vehicles_id',$vehicles_id)
                    ->like('filename',$search)
                    ->limit($limit,$start)
                    ->order_by($col,$dir)
                    ->get('vehicle_files');
            if($query->num_rows()>0) {
                return $query->result();  
            } else {
                return null;
            }
        }
    
        function files_search_count($vehicles_id,$search)
        {
            $query = $this->db
                    ->select('`vehicle_files_id`,`filename`,`date`')
                    ->where('vehicles_id',$vehicles_id)
                    ->like('filename',$search)
                    ->get('vehicle_files');
            return $query->num_rows();
        } 

        //Server Side Datatable | Files END

    public function get_tools($params){

        if( isset($params['sel_query']) ){
            $sel_query = $params['sel_query'];
        }else{
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`tools` AS t');
        $this->db->join('`tool_items` AS ti', 't.`item` = ti.`tool_items_id`','left');     
        $this->db->join('`vehicles` AS v', 't.`assign_to_vehicle` = v.`vehicles_id`','left');   

        if( isset($params['country_id'])  && $params['country_id'] != ''){
            $this->db->where('t.`country_id`', $params['country_id']);
        }

        if( isset($params['tech_vehicle']) && $params['tech_vehicle']!=""){
            $this->db->where('v.`tech_vehicle`', $params['tech_vehicle']);
		}

        //FITLERS

        if($params['tools_id']!=""){
            $this->db->where('t.`tools_id`', $params['tools_id']);
		}

        //item filter
        if (isset($params['item_filter']) && $params['item_filter'] != '') {
            $this->db->where('t.`item`', $params['item_filter']);
        }

        //vehicles filter
        if (isset($params['vehicle_filter']) && $params['vehicle_filter'] != '') {
            $this->db->where('v.`vehicles_id`', $params['vehicle_filter']);
        }

         //search filter
         if (isset($params['search_filter']) && $params['search_filter'] != '') {
            $search_filter = " CONCAT ( LOWER(t.`item_id`), LOWER(t.`brand`), LOWER(t.`description`) ) ";
            $this->db->like($search_filter, $params['search_filter']);
        }
        

        // sort
        if( isset($params['sort_list']) ){
            foreach( $params['sort_list'] as $sort_arr ){
                if( $sort_arr['order_by']!="" && $sort_arr['sort']!='' ){
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

    public function get_kms($params){

        $this->db->select('*');
        $this->db->from('`kms`');
        $this->db->where('kms.`vehicles_id`', $params['v_id']);
        $this->db->limit(1,0);
        $this->db->order_by("kms_updated desc");     

        $query = $this->db->get();
        
        return $query->result_array();

    }

    public function getLadderCheck($params){

        if( isset($params['sel_query']) ){
            $sel_query = $params['sel_query'];
        }else{
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('ladder_check');
        $this->db->where('active',1);
        $this->db->where('deleted',0);


        if($params['ladder_check_id']!=""){
            $this->db->where('ladder_check_id', $params['ladder_check_id']);
		}
        
		if($params['tools_id']!=""){
            $this->db->where('tools_id', $params['tools_id']);
		}
		
        // sort
        if( isset($params['sort_list']) ){
            foreach( $params['sort_list'] as $sort_arr ){
                if( $sort_arr['order_by']!="" && $sort_arr['sort']!='' ){
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }
        
        // limit
        if( isset($params['limit']) && $params['limit'] > 0 ){
            $this->db->limit( $params['limit'], $params['offset']);
        }	

        $query = $this->db->get();
        return $query;
		
    }
    
    // table ladder_inspection
	public function ladderInspectionSelection($params){


        if( isset($params['sel_query']) ){
            $sel_query = $params['sel_query'];
        }else{
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('ladder_inspection_selection');
        $this->db->where('active',1);
        $this->db->where('deleted',0);

        if($params['ladder_check_id']!=""){
            $this->db->where('ladder_check_id', $params['ladder_check_id']);
		}
		
		if($params['ladder_inspection_id']!=""){
            $this->db->where('ladder_inspection_id', $params['ladder_inspection_id']);
		}


        // sort
        if( isset($params['sort_list']) ){
            foreach( $params['sort_list'] as $sort_arr ){
                if( $sort_arr['order_by']!="" && $sort_arr['sort']!='' ){
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
    
    // table ladder_inspection
	public function getLadderInspection($params){
		
        if( isset($params['sel_query']) ){
            $sel_query = $params['sel_query'];
        }else{
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('ladder_inspection');
        $this->db->where('active',1);
        $this->db->where('deleted',0);

	    // sort
        if( isset($params['sort_list']) ){
            foreach( $params['sort_list'] as $sort_arr ){
                if( $sort_arr['order_by']!="" && $sort_arr['sort']!='' ){
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
    
    // table test_and_tag
	public function getTestAndTag($params){
        

        if( isset($params['sel_query']) ){
            $sel_query = $params['sel_query'];
        }else{
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('test_and_tag');
        $this->db->where('active',1);
        $this->db->where('deleted',0);
        

		if($params['tools_id']!=""){
            $this->db->where('tools_id', $params['tools_id']);
		}
		
		if($params['test_and_tag_id']!=""){
            $this->db->where('test_and_tag_id', $params['test_and_tag_id']);
        }
        

        // sort
        if( isset($params['sort_list']) ){
            foreach( $params['sort_list'] as $sort_arr ){
                if( $sort_arr['order_by']!="" && $sort_arr['sort']!='' ){
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


    // table lockout_kit_checklist
    public function getLockOutKitCheckList($params){


        if( isset($params['sel_query']) ){
            $sel_query = $params['sel_query'];
        }else{
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('lockout_kit_checklist');
        $this->db->where('active',1);
        $this->db->where('deleted',0);

        // sort
        if( isset($params['sort_list']) ){
            foreach( $params['sort_list'] as $sort_arr ){
                if( $sort_arr['order_by']!="" && $sort_arr['sort']!='' ){
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
    
    // table lockout_kit_check
	function getLockoutKitCheck($params){

        if( isset($params['sel_query']) ){
            $sel_query = $params['sel_query'];
        }else{
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('lockout_kit_check');
        $this->db->where('active',1);
        $this->db->where('deleted',0);
		
		if($params['tools_id']!=""){
            $this->db->where('tools_id', $params['tools_id']);
		}
		
		if($params['lockout_kit_check_id']!=""){
            $this->db->where('lockout_kit_check_id', $params['lockout_kit_check_id']);
		}
		
		// sort
        if( isset($params['sort_list']) ){
            foreach( $params['sort_list'] as $sort_arr ){
                if( $sort_arr['order_by']!="" && $sort_arr['sort']!='' ){
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
    
    // table lockout_kit_checklist_selection
	public function lockoutKitChecklistSelection($params){


        if( isset($params['sel_query']) ){
            $sel_query = $params['sel_query'];
        }else{
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('lockout_kit_checklist_selection');
        $this->db->where('active',1);
        $this->db->where('deleted',0);
		
		if($params['lockout_kit_check_id']!=""){
            $this->db->where('lockout_kit_check_id',$params['lockout_kit_check_id']);
		}
		
		if($params['lockout_kit_checklist_id']!=""){
            $this->db->where('lockout_kit_checklist_id',$params['lockout_kit_checklist_id']);
		}
		
		// sort
        if( isset($params['sort_list']) ){
            foreach( $params['sort_list'] as $sort_arr ){
                if( $sort_arr['order_by']!="" && $sort_arr['sort']!='' ){
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
    

    

}

