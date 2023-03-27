<?php

class Agency_api_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    public function is_api_property_hidden($params){

        $api_prop_id = $params['api_prop_id'];
        $agency_id = $params['agency_id'];
        $api_id = $params['api_id'];

        if( $api_prop_id != '' ){

            $sql_str = "
            SELECT COUNT(hap.`id`) AS hap_count
            FROM `hidden_api_property` AS hap
            LEFT JOIN `agency_api_tokens` AS apt ON ( hap.`agency_id` = apt.`agency_id` AND apt.`api_id` = {$api_id} )
            WHERE hap.`api_prop_id` = '{$api_prop_id}'
            AND hap.`agency_id` = {$agency_id}
            AND apt.`api_id` = {$api_id}
            ";

            $sql = $this->db->query($sql_str);

            $hap_count = $sql->row()->hap_count;

            if( $hap_count > 0 ){
                return true;
            }else{
                return false;
            }

        }        

    }


}
