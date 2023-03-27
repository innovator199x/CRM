<?php

class Cordedwindows extends MY_ApiController {

    public function __construct() {
        parent::__construct();

        $this->load->model('tech_model');
        $this->load->model('tech_run_model');
    }

    public function add() {
        $this->api->assertMethod('put');

        $postData = $this->api->getPostData();

        $this->db->trans_start();
        // add corded window(s)
        $success = $this->db
            ->insert_batch("corded_window", $postData["corded_windows"]);

        $lastInsertId = $this->db->insert_id(); // get last inserted id
        $count = count($postData["corded_windows"]); // get count of added corded windows

        $this->db->trans_complete();

        if($success){
            $ids = [$lastInsertId];
            for ($x = 1; $x < $count; $x++) {
                $ids[] = $lastInsertId + $x;
            } // get all ids of the newly created corded windows

            $cordedWindows = $this->db->select()
                ->from("corded_window")
                ->where_in("corded_window_id", $ids)
                ->get()->result_array(); // get all the new corded windows from database

            $this->api->setStatusCode(201);
            $this->api->setSuccess(true);
            $this->api->setMessage('Corded Windows Added.');
            $this->api->putData('corded_windows', $cordedWindows);
        }
        else {
            $this->api->setSuccess(false);
            $this->api->setMessage('Corded Windows could not be added.');
        }
    }

    // update one corded window
    public function update($safetySwitchId) {
        $this->api->assertMethod('patch');

        $postData = $this->api->getPostData();

        $success = $this->db->set($postData)
            ->where("corded_window_id", $safetySwitchId)
            ->update("corded_window");

        if($success){
            $this->api->setStatusCode(200);
            $this->api->setSuccess(true);
            $this->api->setMessage('Corded Window updated.');
        }
        else {
            $this->api->setSuccess(false);
            $this->api->setMessage('Corded Window could not be updated.');
        }
    }

    // delete one corded window
    public function delete() {
        $this->api->assertMethod("delete");

        $jobId = $this->input->get("job_id");
        $safetySwitchId = $this->input->get("corded_window_id");

        $this->db->trans_start();
        $success = $this->db
            ->where("job_id", $jobId)
            ->where("corded_window_id", $safetySwitchId)
            ->limit(1)
            ->delete("corded_window");

        $this->db->trans_complete();
        if($success){
            $this->api->setStatusCode(200);
            $this->api->setSuccess(true);
            $this->api->setMessage('Corded Window deleted.');
        }
        else {
            $this->api->setSuccess(false);
            $this->api->setMessage('Corded Window could not be deleted.');
        }
    }

}