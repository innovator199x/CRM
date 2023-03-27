<?php

class Home extends MY_ApiController {

    public function index() {
        $this->load->model('tech_model');
        $trResult = $this->tech_model->getTechRunIdForStaff($this->api->getJWTItem('staff_id'));

        if ($trResult) {
            $techRunId = $trResult['tech_run_id'];

        }
        else {
            $techRunId = null;
        }

        $latestVaccination = $this->db->select("
            vaccine_brand,
            completed_on,
            certificate_image
            ")
            ->from("vaccinations")
            ->where("StaffID", $this->api->getJWTItem("staff_id"))
            ->order_by("completed_on", "desc")
            ->limit(1)
            ->get()->row_array();

        $this->api->setSuccess(true);
        $this->api->putData('tech_run_id', $techRunId);
        $this->api->putData('latest_vaccination', $latestVaccination);
    }

    public function alarm_stocks() {
        $techStockId = $this->db->select("
            tech_stock_id
        ")
        ->from("tech_stock")
        ->where("staff_id", $this->api->getJWTItem("staff_id"))
        ->where("country_id", $this->config->item("country"))
        ->order_by("date DESC")
        ->limit(1)
        ->get()->row()->tech_stock_id;

        $stocks = $this->db->select("
            s.stocks_id,
            s.code,
            s.item,
            s.display_name,
            s.carton,
            tsi.quantity
        ")
            ->from("stocks AS s")
            ->join("tech_stock_items AS tsi", "s.stocks_id = tsi.stocks_id AND tsi.tech_stock_id = {$techStockId}", "left")
            ->where("s.status", 1)
            ->where("s.show_on_stocktake", 1)
            ->where("s.is_alarm", 1)
            ->group_by("s.stocks_id")
            ->get()->result_array();

        $this->api->putData("tech_stock_id", $techStockId);
        $this->api->putData("stocks", $stocks);

        $this->api->setSuccess(true);

    }

    public function save_alarm_stocks() {
        $this->api->assertMethod("patch");

        $postData = $this->api->getPostData();
        $stocks = $postData["stocks"];
        $stocksIds = array_map(function($stock) {
            return $stock["stocks_id"];
        }, $stocks);

        foreach($stocks as &$stock) {
            $stock["tech_stock_id"] = $postData["tech_stock_id"];
            $stock["status"] = 1;
        }

        $this->db->trans_start();

        $this->db
            ->where("tech_stock_id", $postData["tech_stock_id"])
            ->where_in("stocks_id", $stocksIds)
            ->delete("tech_stock_items");

        $this->db->insert_batch("tech_stock_items", $stocks);

        $this->db->trans_complete();

        $success = $this->db->trans_status();

        $this->api->setSuccess($success);
        if ($success) {
            $this->api->setMessage("Stocks updated successfully.");
        }
        else {
            $this->api->setMessage("Update failed.");
        }

    }

}