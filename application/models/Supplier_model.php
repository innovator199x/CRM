<?php

class Supplier_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
    }

    
    /**
     * GET SUPPLIER
     * return query
     */
    public function getSupplier($params){

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('suppliers');
        $this->db->where('status',1);
        $this->db->where('country_id',$this->config->item('country'));

        
        //optional filters
        if($params['suppliers_id'] && !empty($params['suppliers_id'])){
            $this->db->where('suppliers_id', $params['suppliers_id']);
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
    

}
