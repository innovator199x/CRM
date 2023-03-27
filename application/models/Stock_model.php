<?php

class Stock_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->database();
    }


    public function getStock($params){

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('stocks as s');
        $this->db->join('suppliers as sup','sup.suppliers_id = s.suppliers_id','left');


        //FILTERS
        if($params['country_id'] && $params['country_id']!=""){
            $this->db->where('s.country_id', $params['country_id']);
		}

		if($params['suppliers_id'] && $params['suppliers_id']!=""){
            $this->db->where('s.suppliers_id', $params['suppliers_id']);
		}

		if($params['status'] && $params['status']!=""){
            $this->db->where('s.status', $params['status']);
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


        //FILTERS
        if($params['suppliers_id']!=""){
            $this->db->where('suppliers_id', $params['suppliers_id']);
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


    public function getTechStock($params){

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('tech_stock as ts_main');
        $this->db->join('staff_accounts as sa','sa.StaffID = ts_main.staff_id','left');
        $this->db->join('vehicles as v','v.vehicles_id = ts_main.vehicle','left');
        $this->db->where('ts_main.country_id', $this->config->item('country'));

        if($params['is_search']==0){
            $this->db->join("(SELECT MAX(  `date` ) AS latestDate,  `vehicle` FROM  `tech_stock` WHERE  `country_id` = {$this->config->item('country')} GROUP BY  `vehicle`) as ts",'ts.vehicle = ts_main.vehicle AND ts.latestDate = ts_main.date','inner',NULL);
        }

        if($params['disable_tech_vehicle_filter'] != 1){
            $this->db->where('v.tech_vehicle',1);
        }
        $this->db->where('v.active',1);

        //FILTERS
        if($params['date']!=""){
            $date_filter = "CAST( ts_main.`date` AS Date ) = '{$params['date']}' ";
            $this->db->where($date_filter);
        }

        if($params['from']!="" && $params['to']!=""){
            $this->db->where('Date(ts_main.date) >=', $params['from']);
            $this->db->where('Date(ts_main.date) <=', $params['to']);
        }

		if($params['tech']!=""){
            $this->db->where('ts_main.staff_id', $params['tech']);
		}
		if($params['vehicle']!=""){
            $this->db->where('ts_main.vehicle', $params['vehicle']);
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

    public function getStocks($params){

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('stocks');
        $this->db->where('country_id', $this->config->item('country'));

        if($params['display']){
            $this->db->where('display', $params['display']);
        }
        if($params['status']){
            $this->db->where('status', $params['status']);
        }
        if($params['show_on_stocktake']){
            $this->db->where('show_on_stocktake', $params['show_on_stocktake']);
        }

        // sort
        if (isset($params['sort_list'])) {
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }

        return $this->db->get();

    }

    public function getTechStockItems($tech_stock_id,$stocks_id){
        return $this->db->query("
            SELECT *
            FROM `tech_stock_items`
            WHERE `tech_stock_id` = {$tech_stock_id}
            AND `stocks_id` = {$stocks_id}
        ");
    }

    public function getTechStockItemsWithStockIds($techStockId, $stocksIds) {
        $stocksIdsString = implode(', ', $stocksIds);
        return $this->db->query("
            SELECT *
            FROM `tech_stock_items`
            WHERE `tech_stock_id` = {$techStockId}
            AND `stocks_id` IN ({$stocksIdsString})
        ");
    }


    public function getLatestStocktake($staff_id,$stocks_id){
        return $this->db->query("
            SELECT ts_main. * , tsi. *
            FROM  `tech_stock` AS ts_main
            INNER JOIN (
                SELECT MAX(  `date` ) AS latestDate,  `staff_id`
                FROM  `tech_stock`
                WHERE  `country_id` = {$this->config->item('country')}
                AND  `staff_id` ={$staff_id}
                GROUP BY  `staff_id`
            ) AS ts ON ts_main.`staff_id` = ts.`staff_id`
            AND ts_main.`date` = ts.latestDate
            INNER JOIN  `tech_stock_items` AS tsi ON ts_main.`tech_stock_id` = tsi.`tech_stock_id`
            WHERE ts_main.`staff_id` ={$staff_id}
            AND tsi.`stocks_id` = {$stocks_id}
        ");
    }

    public function getLatestStocktakeWithStockIds($staffId, $stocksIds){
        $stocksIdsString = implode(', ', $stocksIds);
        return $this->db->query("
            SELECT ts_main. * , tsi. *
            FROM  `tech_stock` AS ts_main
            INNER JOIN (
                SELECT MAX(  `date` ) AS latestDate,  `staff_id`
                FROM  `tech_stock`
                WHERE  `country_id` = {$this->config->item('country')}
                AND  `staff_id` ={$staffId}
                GROUP BY  `staff_id`
            ) AS ts ON ts_main.`staff_id` = ts.`staff_id`
            AND ts_main.`date` = ts.latestDate
            INNER JOIN  `tech_stock_items` AS tsi ON ts_main.`tech_stock_id` = tsi.`tech_stock_id`
            WHERE ts_main.`staff_id` ={$staffId}
            AND tsi.`stocks_id` IN ({$stocksIdsString})
        ");
    }

    public function getTechstockSelectedVehicle($tech_stock_id){
        return $this->db->query("
            SELECT *
            FROM `tech_stock` AS ts
            LEFT JOIN `vehicles` AS v ON ts.`vehicle` = v.`vehicles_id`
            WHERE ts.`country_id` = {$this->config->item('country')}
            AND ts.`tech_stock_id` = {$tech_stock_id}
        ");
    }


    function staffVehicle($params){

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('vehicles as v');
        $this->db->join('staff_accounts as sa','sa.StaffID = v.StaffID','left');
        $this->db->where('v.country_id', $this->config->item('country'));

        if($params['staff_id'] && !empty($params['staff_id'])){
            $this->db->where('v.StaffID', $params['staff_id']);
        }

        return $this->db->get();
    }






}


?>