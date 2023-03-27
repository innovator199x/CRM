<?php

class Tech_locations extends MY_ApiController {

    public function __construct() {
        parent::__construct();

        $this->load->model('tech_locations_model');
        //$this->allowedActions = ['get_latest_location'];
    }

    public function save_location() {
        $this->api->assertMethod('post');

        $techId = $this->api->getJWTItem("staff_id");
        $latitude = $this->api->getPostData("latitude");
        $longitude = $this->api->getPostData("longitude");

        $time = date("Y-m-d H:i:s");

        $this->db->insert("tech_locations", [
            'tech_id' => $techId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'created' => $time,
        ]);

        $this->api->setSuccess(true);
        $this->api->putData('location', [
            'id' => $this->db->insert_id(),
            'tech_id' => $techId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'created' => $time,
        ]);
    }

    public function get_latest_location($techId = 2157) {
        if (is_null($techId)) {
            $techId = $this->api->getJWTItem("staff_id");
        }

        $lastLocation = $this->tech_locations_model->loadLastLocation($techId);
        $this->api->setSuccess(!is_null($lastLocation));
        $this->api->putData('last_location', $lastLocation);
    }

    public function get_locations($techId = 2157) {
        if (is_null($techId)) {
            $techId = $this->api->getJWTItem("staff_id");
        }

        $locations = $this->tech_locations_model->loadLocations($techId);
        $this->api->setSuccess(true);
        $this->api->putData('last_location', $locations);
    }

}