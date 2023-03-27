<?php

class Alarms extends MY_ApiController {

    public function __construct() {
        parent::__construct();

        $this->load->model('tech_model');
        $this->load->model('tech_run_model');
    }

    public function add_alarm() {
        $this->api->assertMethod('put');

        $postData = $this->api->getPostData();

        if ($postData["new"] == 0) {
            // add default values to existing alarms since by default, they are checked on CI
            $postData["ts_fixing"] = 1;
            $postData["ts_cleaned"] = 1;
            $postData["ts_newbattery"] = 1;
            $postData["ts_testbutton"] = 1;
            $postData["ts_meetsas1851"] = 1;
            $postData["ts_visualind"] = 1;
            $postData["ts_alarm_sounds_other"] = 1;
        }

        $recBattExp = $postData["rec_batt_exp"];
        // convert rec_batt_exp to YYYY-MM-DD
        if (isset($recBatExp) && preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $recBattExp) != 1) {
            $parts = explode("/", $recBattExp);
            $postData["rec_batt_exp"] = "20{$parts[1]}-{$parts[0]}-01";
        }

        $this->db->trans_start();

        // save new alarm
        $success = $this->db->set($postData)
            ->insert("alarm");

        // get new alarm from database
        $newAlarmId = $this->db->insert_id();
        $newAlarm = $this->db->select()
            ->from("alarm")
            ->where("alarm_id", $newAlarmId)
            ->limit(1)
            ->get()->row_array();

        $this->db->trans_complete();

        if($success){
            $this->api->setStatusCode(201);
            $this->api->setSuccess(true);
            $this->api->setMessage('Alarm Added.');
            $this->api->putData('alarm', $newAlarm); // add new alarm to response
        }
        else {
            $this->api->setSuccess(false);
            $this->api->setMessage('Alarm could not be added.');
        }
    }

    public function update_alarm($alarmId) {
        $this->api->assertMethod('patch');

        $postData = $this->api->getPostData();

        $recBattExp = $postData["rec_batt_exp"];

        // convert rec_batt_exp to YYYY-MM-DD
        if (isset($recBattExp) && preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $recBattExp) != 1) {
            $parts = explode("/", $recBattExp);
            $postData["rec_batt_exp"] = "20{$parts[1]}-{$parts[0]}-01";
        }

        $success = $this->db->set($postData)
            ->where("alarm_id", $alarmId)
            ->update("alarm");

        if($success){
            $this->api->setStatusCode(200);
            $this->api->setSuccess(true);
            $this->api->setMessage('Alarm updated.');
        }
        else {
            $this->api->setSuccess(false);
            $this->api->setMessage('Alarm could not be updated.');
        }
    }

    public function delete_alarm() {
        $this->api->assertMethod("delete");

        $jobId = $this->input->get("job_id");
        $alarmId = $this->input->get("alarm_id");

        $this->db->trans_start();
        $success = $this->db
            ->where("job_id", $jobId)
            ->where("alarm_id", $alarmId)
            ->limit(1)
            ->delete("alarm");

        $this->db->trans_complete();
        if($success){
            $this->api->setStatusCode(200);
            $this->api->setSuccess(true);
            $this->api->setMessage('Alarm deleted.');
        }
        else {
            $this->api->setSuccess(false);
            $this->api->setMessage('Alarm could not be deleted.');
        }
    }

}