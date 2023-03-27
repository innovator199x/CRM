<?php

class Purchase_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
    }


    /**
     * GET PURCHASE ORDER
     * return query
     */
    public function getPurchaseOrder($params){

        
        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('purchase_order as po');
        $this->db->join('agency as a','a.agency_id = po.agency_id','left');
        $this->db->join('agency_priority as aght','a.agency_id = aght.agency_id','left');
        $this->db->join('agency_priority_marker_definition as apmd','aght.priority = apmd.priority','left');
        $this->db->join('suppliers as sup','sup.suppliers_id = po.suppliers_id','left');
        $this->db->join('staff_accounts as sa','sa.StaffID = po.deliver_to','left');
        $this->db->join('staff_accounts as sa2','sa2.StaffID = po.ordered_by','left');
        $this->db->where('po.active',1);
        $this->db->where('po.deleted',0);
        $this->db->where('po.country_id',$this->config->item('country'));


        //OPTIONAL FILTERS

        if($params['purchase_order_id'] && !empty($params['purchase_order_id'])){
            $this->db->where('po.purchase_order_id', $params['purchase_order_id']);
        }

        if($params['filterDate'] && !empty($params['filterDate'])){
			if( $params['filterDate']['from']!="" && $params['filterDate']['to']!="" ){
                $filter_datea_str = " po.`date` BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}' ";
                $this->db->where($filter_datea_str);
			}			
        }

        if($params['supplier_id'] && !empty($params['supplier_id'])){
            $this->db->where('po.suppliers_id', $params['supplier_id']);
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

    
    /**
     * GET PURCHASE ORDER ITEM
     * return query
     */
    function getPurchaseOrderItem($params){


        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('purchase_order_item as poi');
        $this->db->join('purchase_order as po','po.purchase_order_id = poi.purchase_order_id','left');
        $this->db->join('stocks as s','s.stocks_id = poi.stocks_id','left');
        $this->db->where('poi.active',1);
        $this->db->where('poi.deleted',0);
        $this->db->where('po.country_id',$this->config->item('country'));


        //OPTIONAL FILTERS

        if($params['purchase_order_id'] && !empty($params['purchase_order_id'])){
            $this->db->where('poi.purchase_order_id', $params['purchase_order_id']);
        }

        if($params['filterDate'] && !empty($params['filterDate'])){
			if( $params['filterDate']['from']!="" && $params['filterDate']['to']!="" ){
                $filter_datea_str = " po.`date` BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}' ";
                $this->db->where($filter_datea_str);
			}			
        }

        if($params['supplier_id'] && !empty($params['supplier_id'])){
            $this->db->where('po.suppliers_id', $params['supplier_id']);
        }

        if($params['stock_supplier_id'] && !empty($params['stock_supplier_id'])){
            $this->db->where('s.suppliers_id', $params['stock_supplier_id']);
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
    
    public function getDynamicHandyManID(){
		
		if($this->config->item('country')==1){ // AU
			
			if( ENVIRONMENT == "production" ){ // live
				$handyman_id = 31; 
			}else{ // dev
				$handyman_id = 27;
			}
			
		}else if($this->config->item('country')==2){ // NZ
		
			if( ENVIRONMENT == "production" ){ // live
				$handyman_id = 32; 
			}else{ // dev
				$handyman_id = 6;
			}

		}
		
		return $handyman_id;
		
    }
    
    /**
     * Update Purchase Order
     */
    public function update_purchase_order($purchase_order_id, $data){
        $this->db->where('purchase_order_id', $purchase_order_id);
        $this->db->update('purchase_order', $data);
    }

    /**
     * Delete Purchase_order_item
     */
    public function delete_purchase_order_item($id){
        $this->db->where('purchase_order_id', $id);
        $this->db->delete('purchase_order_item');

        return $this->db->affected_rows();
    }

    /**
     * Insert purchase_order_item
     */
    public function add_purchase_order_item($data){
        $this->db->insert('purchase_order_item', $data);
    }

    // get Expenses last ID number
	public function getPurchaseOrderLastIDNumber(){
		return $this->db->query("
			SELECT `purchase_order_num`
			FROM `purchase_order`
			ORDER BY `purchase_order_id` DESC
			LIMIT 1
		");
	}

    

}
