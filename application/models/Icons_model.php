<?php

class Icons_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
    }

    /**
     * PULL BUTTON ICONS FROM DATABASE
     * @param $params | ARRAY | array of data to use for select
     * @return $query | RESULT | result from the database
     **/
    public function getButtonIcons( $params ){

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } elseif( $params['return_count']==1 ) {
            $sel_str = " COUNT(*) AS jcount ";
        } else {
            $sel_query = '*';
        }
        if( $params['distinct_sql']!="" ){
            $this->db->distinct();
        }
        $this->db->select( $sel_query );
        $this->db->from( '`icons` AS ico' );
        $this->db->where( 'ico.`icon_id` > 0' );

        // filters
        //$filter_arr = array();
        
        if($params['active']!=""){
            //$filter_arr[] = "AND ico.`active` = {$params['active']}";
            $this->db->where( 'ico.`active`', $params['active'] );
        }
        
        if($params['bi_id']!=""){
            //$filter_arr[] = "AND ico.`icon_id` = {$params['bi_id']}";
            $this->db->where( 'ico.`icon_id`', $params['bi_id'] );
        }

        
        /*	
        if($params['filterDate']!=''){
            if( $params['filterDate']['from']!="" && $params['filterDate']['to']!="" ){
                $filter_arr[] = "AND CAST(sar.`created_date` AS DATE) BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}'";
            }			
        }
            
        if($params['phrase']!=''){
            $filter_arr[] = "AND (
                bn.`notes` LIKE '%{$params['phrase']}%' OR
                a.`agency_name` LIKE '%{$params['phrase']}%'
            )";
        }
        */
        
        
        // combine all filters
        // if( count($filter_arr)>0 ){
        //     $filter_str = " WHERE ico.`icon_id` > 0 ".implode(" ",$filter_arr);
        // }
        

        //custom query
        if( $params['custom_filter']!='' ){
            //$custom_filter_str = $params['custom_filter'];
            $this->db->where( $params['custom_filter'] );
        }
        
        // if($params['custom_select']!=''){
        //     $sel_str = " {$params['custom_select']} ";
        // }else if($params['return_count']==1){
        //     $sel_str = " COUNT(*) AS jcount ";
        // }else if($params['distinct_sql']!=""){
            
        //     $sel_str = " DISTINCT {$params['distinct_sql']} ";	
            
        // }else{
        //     $sel_str = " 
        //         *
        //     ";
        // }
        
        // search
        if( $params['search'] != '' ){
            $this->db->like( $params['search'] );
        }
        
        
        // sort
        if( $params['sort_list']!='' ){
            
            $sort_str_arr = array();
            foreach( $params['sort_list'] as $sort_arr ){
                if( $sort_arr['order_by']!="" && $sort_arr['sort']!='' ){
                    //$sort_str_arr[] = $sort_arr['order_by'] . ' ' . $sort_arr['sort'];
                    $this->db->order_by( $sort_arr['order_by'] , $sort_arr['sort'] );
                }
            }
            $sort_str_imp = implode(", ",$sort_str_arr);
            // $sort_str = "ORDER BY {$sort_str_imp}";
            //$this->db->order_by( $sort_str_imp );
        }		
        
        
        // GROUP BY
        if($params['group_by']!=''){
            //$group_by_str = "GROUP BY {$params['group_by']}";
            $this->db->group_by( $params['group_by'] );
        }
        
        
        // paginate
        if($params['paginate']!=""){
            // if(is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])){
                //$pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
                $this->db->limit( $params['paginate']['limit'], $params['paginate']['offset'] );
            // }
        }
        

        // $sql = "		
        //     SELECT {$sel_str}
        //     FROM `icons` AS ico
        //     {$custom_table_join}
        //     {$filter_str}	
        //     {$custom_filter_str}
        //     {$group_by_str}
        //     {$sort_str}
        //     {$pag_str}
            
        // ";		
        $query = $this->db->get();
        if($params['echo_query']==1){
            //echo $sql;
            echo $this->db->last_query();
        }
        
        if($params['return_count']==1){
            // $j_sql = mysql_query($sql);
            // $row = mysql_fetch_array($j_sql);
            // return $row['jcount'];
            
            return $query->num_rows();
        }else{
            return $query;
        }

    }

    /**
     *  INSERT THE ICON TO DB
     *  @param $params | ARRAY | array of data to be used for the update
     */
    public function add_icon( $params ){
        $icon_data = [
            'icon' => $params['icon_path'],
            'page' => $params['page'],
            'description' => $params['desc'],
            'date_created' => date("Y-m-d H:i:s")
        ];
        $this->db->insert( 'icons', $icon_data );
        return $this->db->insert_id();
    }


    /**
     *  UPDATES THE ICON DETAILS
     *  @param $params | ARRAY | array of data to be used for the update
     **/
    public function update_icon( $params ){
        $this->db->where( 'icon_id', $params['icon_id'] );
        $this->db->update( 'icons', $params['icon'] );
        $affected_rows = $this->db->affected_rows();
        return $affected_rows;
    }
    /**
     *  DELETES THE ICON DETAILS
     *  @param $params | ARRAY | array of data to be used for the update
     **/
    public function remove_icon( $params ){
        $this->db->where( 'icon_id', $params['icon_id'] );
        $this->db->delete( 'icons' );
        $affected_rows = $this->db->affected_rows();
        return $affected_rows;
    }

}
