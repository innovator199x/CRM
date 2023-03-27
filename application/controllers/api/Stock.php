<?php

class Stock extends MY_ApiController {

    public function __construct() {
        parent::__construct();

        $this->load->model('stock_model');
    }

    public function tech_stock_for_update($staff_id = null, $tech_stock_id = null) {
        $this->api->assertMethod('get');

        $staffId = $staff_id != null ? $staff_id : $this->api->getJWTItem('staff_id');
        $techStockId = $tech_stock_id;
        $staffClassId = $this->api->getJWTItem('class_id');

        //get stocks
        $getStockParams = [
            'sel_query' => '*',
            'status' => 1,
            'show_on_stocktake' => 1,
            'sort_list' => [
                [
                    'order_by' => 'item',
                    'sort' => 'ASC'
                ],
            ],
        ];

        $stocksResult = $this->stock_model->getStocks($getStockParams);

        $stocksIds = [];
        $stocks = [];
        foreach ($stocksResult->result_array() as $row) {
            $stocks[$row['stocks_id']] = $row;
            $stocks[$row['stocks_id']]['quantity'] = 0;

            $stocksIds[] = $row['stocks_id'];
        }

        $staffVehicle = null;

        if ($techStockId != null) {
            $techStockItems = $this->stock_model->getTechStockItemsWithStockIds($techStockId, $stocksIds);

            foreach ($techStockItems->result_array() as $row) {
                $stocks[$row['stocks_id']]['quantity'] += $row['quantity'];
            }

            $vehiclesResult = $this->stock_model->getTechstockSelectedVehicle($techStockId)->row_array();

            $staffVehicleId = $vehiclesResult['vehicle'];
        }
        else {
            $stockTakes = $this->stock_model->getLatestStocktakeWithStockIds($staffId, $stocksIds);

            foreach ($stockTakes->result_array() as $row) {
                $stocks[$row['stocks_id']]['quantity'] += $row['quantity'];
            }

            $staffVehicleParams = ['sel_query'=>'*', 'staff_id' => $staffId];
            $vehiclesResult = $this->stock_model->staffVehicle($staffVehicleParams)->row_array();

            $staffVehicleId = $vehiclesResult['vehicles_id'];
        }

        $staffVehicleParams = ['sel_query'=>'*'];
        $vehicles = $this->stock_model->staffVehicle($staffVehicleParams)->result_array();

        foreach ($vehicles as &$vehicle) {
            $vehicle['selected'] = $vehicle['vehicles'] == $staffVehicleId;
        }

        $this->api->setSuccess(true);

        $this->api->putData('staff_id', $staffId);
        $this->api->putData('tech_stock_id', $techStockId);
        $this->api->putData('staff_class_id',$staffClassId);
        $this->api->putData('stocks', $stocks);
        $this->api->putData('staff_vehicle_id', $staffVehicleId);
        $this->api->putData('vehicles', $vehicles);
    }

    public function update_tech_stock() {
        $this->api->assertMethod('patch');

        $this->load->model('tech_model');

        $stocksInput = $this->api->getPostData('stocks');
        $staffId = $this->api->getPostData('staff_id');
        $vehicleId = $this->api->getPostData('vehicle_id');
        $staffClassId = $this->api->getJWTItem('class_id');

        $insertData = [
            'staff_id' => $staffId,
            'date' => date('Y-m-d H:i:s'),
            'status' => 1,
            'country_id' => $this->config->item('country'),
            'vehicle' => $vehicleId,
        ];

        // use transaction
        $this->db->insert('tech_stock', $insertData);
        $newTechStockId = $this->db->insert_id();

        if ($newTechStockId) {
            $techStockInsertData = [];
            foreach($stocksInput as $stockId => $quantity) {
                $techStockInsertData[] = [
                    'tech_stock_id' => $newTechStockId,
                    'stocks_id' => $stockId,
                    'quantity' => $quantity,
                    'status' => 1,
                ];
            }

            $this->api->putData('new_tech_stock_id', $newTechStockId);

            $this->db->insert_batch('tech_stock_items', $techStockInsertData);

            $this->api->setSuccess(true);
            $this->api->setMessage('Stock successfully updated.');

            $techStockData  = $this->db->select('tech_stock_id, date')->from('tech_stock')->where('vehicle', $vehicleId)->order_by('date','DESC')->limit(1)->get()->row_array();
            $techStockDate = new DateTimeImmutable($techStockData['date'], new DateTimeZone(date_default_timezone_get()));
            $next7Days = $techStockDate->modify('+7 days')->format('Y-m-d');

            $techStock = [
                'original_date' => $techStockDate->format('Y-m-d'),
                'next_schedule' => $next7Days,
            ];

            $this->api->putData('tech_stock', $techStock);

            return;
        }

        $this->api->setSuccess(false);
        $this->api->setMessage('Stock not updated.');

    }

}