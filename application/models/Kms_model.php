<?php

class Kms_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    /**
     * PULL KMS VEHICLE REPORTS
     * @param $params | ARRAY | array of data to use for select
     * @return $query | RESULT | result from the database
     * */
    public function get_kms($params) {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } elseif ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else {
            $sel_query = '*';
        }
        if ($params['distinct_sql'] != "") {
            $this->db->distinct();
        }
        $selection="";
        if (isset($params['selection'])) {
            $selection = $params['selection'];
        }
        $joinType = 'LEFT';
        if($selection==='driver') {
            $joinType='INNER';
        }
        $this->db->select($sel_query);
        $this->db->from('`kms` AS k');
        $this->db->join('`vehicles` as v', 'k.`vehicles_id` = v.`vehicles_id`', 'inner');
        $this->db->join('`staff_accounts` as sa', 'v.`StaffID` = sa.`StaffID`', 'inner');
        // $this->db->where( 'k.`country_id`',$params['country'] );

        if (null != $params['vehicle'] && $params['vehicle'] != '') {
            $this->db->where('k.`vehicles_id`', $params['vehicle']);
        }
        if ((null != $params['driver'] && $params['driver'] != '') || ( $params['driver'] != '' && $params['vehicle'] == '' )) {
            $this->db->where('v.`StaffID`', $params['driver']);
        }


        //custom query
        if ($params['custom_filter'] != '') {
            $this->db->where($params['custom_filter']);
        }
        // search
        if ($params['search'] != '') {
            $this->db->like($params['search']);
        }


        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
            $sort_str_imp = implode(", ", $sort_str_arr);
        }

        // paginate
        if ($params['paginate'] != "") {
            $this->db->limit($params['paginate']['limit'], $params['paginate']['offset']);
        }



        $query = $this->db->get();
        if ($params['echo_query'] == 1) {
            echo $this->db->get_compiled_select();
        }

        if ($params['return_count'] == 1) {
            return $query->num_rows();
        } else {
            return $query;
        }
    }

}
