<?php
class Tech_locations_model extends CI_Model {

    public function __construct(){
        $this->load->database();
    }

    public function loadLastLocation($techId) {
        $lastLocationResult = $this->db
            ->select('*')
            ->from('tech_locations')
            ->where('tech_id', $techId)
            ->order_by("created", 'DESC')
            ->limit(1)
            ->get();

        if ($lastLocationResult->num_rows()  == 0) {
            return null;
        }

        return $lastLocationResult->row_array();
    }

    public function loadLocations($techId) {
        $lastLocationResult = $this->db
            ->select('*')
            ->from('tech_locations')
            ->where('tech_id', $techId)
            ->order_by("created", 'DESC')
            ->get();

        return $lastLocationResult->result_array();
    }

}