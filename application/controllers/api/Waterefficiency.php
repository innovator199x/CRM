<?php

class Waterefficiency extends MY_ApiController {

    public function __construct() {
        parent::__construct();

        $this->load->model('tech_model');
        $this->load->model('tech_run_model');
    }

    public function test() {
        echo "TEST";
    }

    public function add() {
        $this->api->assertMethod('put');

        $postData = $this->api->getPostData();
        foreach ($postData["water_efficiency_details"] as &$d) {
            $d["created_date"] = date("Y-m-d H:i:s");
            $d["active"] = 1;
        }

        $this->db->trans_start();
        // add water efficiency
        $success = $this->db
            ->insert_batch("water_efficiency", $postData["water_efficiency_details"]);

        $lastInsertId = $this->db->insert_id(); // get last inserted id
        $count = count($postData["water_efficiency_details"]); // get count of added corded windows

        $this->db->trans_complete();

        if($success){
            $ids = [$lastInsertId];
            for ($x = 1; $x < $count; $x++) {
                $ids[] = $lastInsertId + $x;
            } // get all ids of the newly created water efficiency

            $waterEfficiencyDetails = $this->db->select()
                ->from("water_efficiency")
                ->where_in("water_efficiency_id", $ids)
                ->get()->result_array(); // get all the new water efficiency  from database
            $this->api->setStatusCode(201);
            $this->api->setSuccess(true);
            $this->api->setMessage('Water Efficiency Added.');
            $this->api->putData('water_efficiency', $waterEfficiencyDetails);
        }
        else {
            $this->api->setSuccess(false);
            $this->api->setMessage('Water Efficiency could not be added.');
        }
    }

    // update one water efficiency
    public function update($waterEfficiencyId) {
        $this->api->assertMethod('patch');

        $postData = $this->api->getPostData();

        $success = $this->db->set($postData)
            ->where("water_efficiency_id", $waterEfficiencyId)
            ->update("water_efficiency");

        if($success){
            $this->api->setStatusCode(200);
            $this->api->setSuccess(true);
            $this->api->setMessage('Water Efficiency updated.');
        }
        else {
            $this->api->setSuccess(false);
            $this->api->setMessage('Water Efficiency could not be updated.');
        }
    }

    // delete one water efficiency
    public function delete() {
        $this->api->assertMethod("delete");

        $jobId = $this->input->get("job_id");
        $waterEfficiencyId = $this->input->get("water_efficiency_id");

        $this->db->trans_start();
        $success = $this->db
            ->where("job_id", $jobId)
            ->where("water_efficiency_id", $waterEfficiencyId)
            ->limit(1)
            ->delete("water_efficiency");

        $this->db->trans_complete();
        if($success){
            $this->api->setStatusCode(200);
            $this->api->setSuccess(true);
            $this->api->setMessage('Water Efficiency deleted.');
        }
        else {
            $this->api->setSuccess(false);
            $this->api->setMessage('Water Efficiency could not be deleted.');
        }
    }

}