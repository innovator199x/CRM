<?php

class Regions extends CI_Controller {

    public function __construct() {

        parent::__construct();
        $this->load->database();
    }

    public function postcode_info($postcode) {
        $postcodeInfo = $this->db->select("p.postcode, sr.subregion_name AS subregion, r.region_name AS region, sd.state_full_name AS state")
            ->from("postcode AS p")
            ->join("sub_regions AS sr", "p.sub_region_id = sr.sub_region_id", "inner")
            ->join("regions AS r", "r.regions_id = sr.region_id", "inner")
            ->join("states_def as sd", "sd.state = r.region_state", "inner")
            ->where("p.postcode", $postcode)
            ->where("p.deleted", 0)
            // ->where("( p.is_nlm = 0 OR p.is_nlm IS NULL )") // No Column in database
            ->where("sr.active", 1)
            ->where("r.status", 1)
            ->where("sd.country_id", $this->config->item("country"))
            ->get()->row_array();

        echo json_encode([
            "success" => !is_null($postcodeInfo),
            "info" => $postcodeInfo,
        ]);
    }

    public function find_assignable_regions() {
        $query = $this->input->get("term");
        // $postcodes = $this->db->select("id AS id, postcode AS label, 'postcode' AS type")
        //     ->from("postcode")
        //     ->where("postcode LIKE", "{$query}%")
        //     ->where("deleted", 0)
        //     ->limit(5)
        //     ->get()->result_array();

        // $subRegions = $this->db->select("sub_region_id AS id, subregion_name AS label, 'sub_region' AS type")
        //     ->from("sub_regions")
        //     ->where("subregion_name LIKE", "{$query}%")
        //     ->where("active", 1)
        //     ->limit(5)
        //     ->get()->result_array();

        $regions = $this->db->select("regions_id AS id, region_name AS label, 'region' AS type")
            ->from("regions")
            ->where("region_name LIKE", "{$query}%")
            ->where("status", 1)
            ->where("country_id", $this->config->item("country"))
            ->limit(5)
            ->get()->result_array();

        // $states = $this->db->select("StateID AS id, state_full_name AS label, 'state' AS type")
        //     ->from("states_def")
        //     ->where("country_id", $this->config->item("country"))
        //     ->group_start()
        //     ->where("state_full_name LIKE", "{$query}%")
        //     ->or_where("state LIKE", "{$query}%")
        //     ->group_end()
        //     ->limit(5)
        //     ->get()->result_array();

        // $result = array_merge($states, $regions, $subRegions, $postcodes);

        echo json_encode($regions);
    }

}