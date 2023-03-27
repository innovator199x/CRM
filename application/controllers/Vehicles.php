<?php

class Vehicles extends CI_Controller {

    public function __construct() {

        parent::__construct();
        $this->load->helper('url');
        $this->load->model('vehicles_model');
        $this->load->library('pagination');
        $this->load->database();
        $this->load->model('kms_model');
        $this->load->library('email');
    }

    public function view_vehicles() {

        $data['title'] = "View Vehicles";

        $country_id = $this->config->item('country');
        $driver = $this->input->get_post('driver');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $sel_query = "
            v.`plant_id` AS v_plantID,
            v.`vehicles_id` AS v_id,
            v.`make` AS v_make,
            v.`model` AS v_model,
            v.`key_number` AS v_keynumber,
            v.`vin_num` AS v_vinnum,
            v.`number_plate` AS v_numberplate,
            v.`rego_expires` AS v_regoexpires,
            v.`rego_expires` AS v_regoexpires,
            v.`tech_vehicle` AS v_techvehicle,
            v.`active` AS v_active,
            v.`next_service` AS v_nextservice,
            v.WOF,
            v.vehicle_ownership,
            v.transmission,

            sa.`FirstName` AS sa_firstname,
            sa.`LastName` AS sa_lastname,
            sa.`StaffID`,
            ";

        if (isset($_GET['page'])) {

            $params = array(
                'sel_query' => $sel_query,
                'a_status' => 1,
                'country_id' => $country_id,
                'staff_id' => $driver,
                'sort_list' => array(
                    array(
                        'order_by' => 'v.plant_id',
                        'sort' => 'ASC',
                    ),
                ),
            );

            $driver_params = array(
                'sel_query' => "DISTINCT(sa.StaffID), sa.FirstName, sa.LastName",
                'a_status' => 1,
                'country_id' => $country_id
            );

        } else {

            $params = array(
                'sel_query' => $sel_query,
                'a_status' => 1,
                'country_id' => $country_id,
                'staff_id' => $driver,
                'sort_list' => array(
                    array(
                        'order_by' => 'v.plant_id',
                        'sort' => 'ASC',
                    ),
                ),
                'v_status' => 1,
            );

            $driver_params = array(
                'sel_query' => "DISTINCT(sa.StaffID), sa.FirstName, sa.LastName",
                'a_status' => 1,
                'country_id' => $country_id,
                'v_status' => 1
            );

        }

        $data['lists'] = $this->vehicles_model->get_vehicles($params);

        //get driver for filter dropdown
        $data['driver'] = $this->vehicles_model->get_vehicles($driver_params);

        if (isset($_GET['page'])) {
            $params = array(
                'sel_query' => $sel_query,
                'a_status' => 1,
                'staff_id' => $driver
            );
        } else {
            $params = array(
                'sel_query' => $sel_query,
                'a_status' => 1,
                'v_status' => 1,
                'a_status' => 1,
                'staff_id' => $driver
            );
        }

        $query = $this->vehicles_model->get_vehicles($params);
        $total_rows = $query->num_rows();


        $pagi_links_params_arr = array(
            'agency_filter' => $agency_filter,
            'job_type_filter' => $job_type_filter,
            'service_filter' => $service_filter,
            'date_filter' => $date_filter,
            'search_filter' => $search,
            'sub_region_ms' => $sub_region_ms,
            'driver' => $driver
        );
        $pagi_link_params = '/vehicles/view_vehicles/?' . http_build_query($pagi_links_params_arr);

        // pagination settings
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $pagi_link_params;

        $this->pagination->initialize($config);

        $data['pagination'] = $this->pagination->create_links();

        // pagination count
        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );

        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

        $this->load->view('templates/inner_header', $data);
        $this->load->view('vehicles/view_vehicles', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function view_tools() {
        $data['start_load_time'] = microtime(true);

        $data['title'] = "View Tools";

        $country_id = $this->config->item('country');


        //GET_POST
        $item_filter = $this->input->get_post('item_filter');
        $vehicle_filter = $this->input->get_post('vehicle_filter');
        $search_filter = $this->input->get_post('search_filter');

        // pagination settings
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $sel_query = "
        t.`item_id` AS t_itemid, 
        t.`item`, 
        t.`purchase_date` AS t_purchase_date,
        t.`purchase_price` AS t_purchase_price,
        t.`brand` AS t_brand,
        t.`description` AS t_description,      
        t.`tools_id`,

        ti.`item_name` AS ti_itemname,

        v.`number_plate`
        ";

        $params = array(
            'sel_query' => $sel_query,
            'assign_to_vehicle' => $vehicle_filter,
            'search_phrase' => $search_filter,
            'item' => $item_filter,
            'country_id' => $country_id,
            'item_filter' => $item_filter,
            'vehicle_filter' => $vehicle_filter,
            'search_filter' => $search_filter,
            'limit' => $per_page,
            'offset' => $offset,
            'sort_list' => array(
                array(
                    'order_by' => 't.`item_id`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['lists'] = $this->vehicles_model->get_tools($params);

        // ALL ROWS
        $sel_query_tot = "COUNT(t.`item_id`) AS tCount";
        $params = array(
            'sel_query' => $sel_query_tot,
            'assign_to_vehicle' => $vehicle_filter,
            'search_phrase' => $search_filter,
            'item' => $item_filter,
            'country_id' => $country_id,
            'item_filter' => $item_filter,
            'vehicle_filter' => $vehicle_filter,
            'search_filter' => $search_filter,
        );
        $query = $this->vehicles_model->get_tools($params);
        $total_rows = $query->row()->tCount;


        // FILTER QUEYRIES
        // item filter
        $sel_query = " DISTINCT(t.`item`), ti.`item_name` ";
        $params = array(
            'sel_query' => $sel_query,
            'country_id' => $country_id,
            'sort_list' => array(
                array(
                    'order_by' => 'ti.`item_name`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['item_filter'] = $this->vehicles_model->get_tools($params);

        //vehicle fitler
        $sel_query = " DISTINCT(v.`vehicles_id`), v.`number_plate` ";
        $params = array(
            'sel_query' => $sel_query,
            'country_id' => $country_id,
        );
        $data['vehicle_filter'] = $this->vehicles_model->get_tools($params);



        $pagi_links_params_arr = array(
            'item_filter' => $job_filitem_filterter,
            'vehicle_filter' => $service_filter,
            'search_filter' => $search_filter,
        );
        $pagi_link_params = '/vehicles/view_tools/?' . http_build_query($pagi_links_params_arr);

        // pagination settings
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $pagi_link_params;

        $this->pagination->initialize($config);

        $data['pagination'] = $this->pagination->create_links();

        // pagination count
        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );

        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);


        $this->load->view('templates/inner_header', $data);
        $this->load->view('vehicles/view_tools', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function view_tool_details() {
       
        $data['title'] = "Tool Details";

        $tool_id = $this->uri->segment(3);
        $data['tool_id'] = $this->uri->segment(3);
        
        if($tool_id && !empty($tool_id) && is_numeric($tool_id)){


            //get tools by tools id
            $sel_query = "t.tools_id, t.item, t.item_id, t.brand, t.description, t.purchase_date, t.purchase_price, t.assign_to_vehicle, ti.item_name";
            $tools_params = array(
                'sel_query' => $sel_query,
                'country_id' => $country_id,
                'tools_id' => $tool_id
            );
            $data['t'] = $this->vehicles_model->get_tools($tools_params)->row_array();


            //assign vehicle dropdown
            $sel_query = " DISTINCT(v.`vehicles_id`), v.`number_plate`, v.`tech_vehicle`, sa.FirstName, sa.LastName ";
            $params = array(
                'sel_query' => $sel_query,
                'country_id' => $country_id,
                'tech_vehicle' => 1,
                'sort_list' => array(
                    array(
                        'order_by' => 'v.`number_plate`',
                        'sort' => 'ASC',
                    ),
                ),
            );
            $data['assign_vehicle_dropdown'] = $this->vehicles_model->get_vehicles($params);


            //get laddercheck
            $tt_params = array(
                'sel_query' => "*",
                'tools_id' => $tool_id,
                'sort_list' => array(
                    array(
                        'order_by' => 'date',
                        'sort' => 'DESC'
                    )
                )

            );
            $data['lc_sql'] = $this->vehicles_model->getLadderCheck($tt_params);
           


            //get test and tag
             $v_params = array(
                'sel_query' => "*",
                'tools_id' => $tool_id,
                'sort_list' => array(
                    array(
                        'order_by' => 'date',
                        'sort' => 'DESC'
                    )
                )

            );
            $data['tnt_sql'] = $this->vehicles_model->getTestAndTag($v_params);


            //get lockout kit check
            $lkc_params = array(
                'tools_id' => $tool_id,
                'sort_list' => array(
                    array(
                        'order_by' => 'date',
                        'sort' => 'DESC'
                    )
                )
            );
            $data['lkc_sql'] = $this->vehicles_model->getLockoutKitCheck($lkc_params);



            $this->load->view('templates/inner_header', $data);
            $this->load->view('vehicles/view_tool_details', $data);
            $this->load->view('templates/inner_footer', $data);

        }else{
            redirect('/vehicles/view_tools','refresh');
        }

    }

    /**
     * Export Escalated Jobs in CSV format
     */
    public function export_view_vehicle() {

        // file name 
        $filename = "Export_Vehicle_" . date("d/m/Y") . ".csv";

        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename={$filename}");
        header("Pragma: no-cache");
        header("Expires: 0");

        $driver = $this->input->get_post('driver');

        $sel_query = "
            v.`plant_id` AS v_plantID,
            v.`vehicles_id` AS v_id,
            v.`make` AS v_make,
            v.`model` AS v_model,
            v.`key_number` AS v_keynumber,
            v.`vin_num` AS v_vinnum,
            v.`number_plate` AS v_numberplate,
            v.`rego_expires` AS v_regoexpires,
            v.`rego_expires` AS v_regoexpires,
            v.`tech_vehicle` AS v_techvehicle,
            v.`active` AS v_active,
            v.`next_service` AS v_nextservice,
            v.`vehicle_ownership`,
            v.transmission,

            sa.`FirstName` AS sa_firstname,
            sa.`LastName` AS sa_lastname,
            ";

        $sel_query = "*";
        $country_id = $this->config->item('country');

        if( $this->input->get_post('page') && $this->input->get_post('page')=='all' ){

            $params = array(
                'sel_query' => $sel_query,
                'a_status' => 1,
                'country_id' => $country_id,
                'staff_id' => $driver,
                'sort_list' => array(
                    array(
                        'order_by' => 'v.plant_id',
                        'sort' => 'ASC',
                    ),
                ),
            );

        }else{

            $params = array(
                'sel_query' => $sel_query,
                'a_status' => 1,
                'country_id' => $country_id,
                'staff_id' => $driver,
                'sort_list' => array(
                    array(
                        'order_by' => 'v.plant_id',
                        'sort' => 'ASC',
                    ),
                ),
                'v_status' => 1,
            );
            
        }
       
        $lists = $this->vehicles_model->get_vehicles($params);


        // file creation 
        $file = fopen('php://output', 'w');

        $header = array("Make", "Model","Transmission", "Plant ID", "Year", "Number Plate", "Rego Expires", "Warranty Expires", "Fuel Type", "eTag Number", "Serviced By", "Next Service", "Fuel Card Number", "Purchase Date", "Purchase Price", "Roadside Assistance Number", "Insurance Policy Number", "Policy Expires", "Driver", "Fuel Card Pin", "VIN Number", "Ownership","KMS","KMS Updated");
        fputcsv($file, $header);

        foreach ($lists->result() as $row) {

            if ($row->vehicle_ownership == 1) {
                $vehicle_ownership = 'Company';
            } else if ($row->vehicle_ownership == 2) {
                $vehicle_ownership = 'Personal';
            } else {
                $vehicle_ownership = null;
            }

            //get kms
            $kms_query = $this->db->select('*')->from('kms')->where('vehicles_id',$row->vehicles_id)->limit(1)->order_by('kms_updated','DESC')->get()->row_array();
            $kms = $kms_query['kms'];
            $KMS_updated = ($this->system_model->isDateNotEmpty($kms_query['kms_updated'])) ? $this->system_model->formatDate($kms_query['kms_updated'], 'd/m/Y') : NULL ; 

            $data['make'] = $row->make;
            $data['model'] = $row->model;
            $data['transmission'] = $row->transmission;
            $data['plant_id'] = $row->plant_id;
            $data['year'] = $row->year;
            $data['number_plate'] = $row->number_plate;
            $data['rego_expires'] = (($row->rego_expires != "" && $row->rego_expires != "0000-00-00") ? date("d/m/Y", strtotime($row->rego_expires)) : '');
            $data['warranty_expires'] = (($row->warranty_expires != "" && $row->warranty_expires != "0000-00-00" && $row->warranty_expires != "1970-01-01") ? date("d/m/Y", strtotime($row->warranty_expires)) : '');
            $data['fuel_type'] = $row->fuel_type;
            $data['etag_num'] = $row->etag_num;
            $data['serviced_by'] = $row->serviced_by;
            $data['next_service'] = $row->next_service;
            $data['fuel_card_num'] = $row->fuel_card_num;
            $data['purchase_date'] = (($row->purchase_date != "" && $row->purchase_date != "0000-00-00" && $row->purchase_date != "1970-01-01") ? date("d/m/Y", strtotime($row->purchase_date)) : '');
            $data['purchase_price'] = $row->purchase_price;
            $data['ra_num'] = $row->ra_num;
            $data['ins_pol_num'] = $row->ins_pol_num;
            $data['policy_expires'] = (($row->policy_expires != "" && $row->policy_expires != "0000-00-00 00:00:00" && $row->policy_expires != "1970-01-01 00:00:00") ? date("d/m/Y", strtotime($row->policy_expires)) : '');
            $data['FirstName'] = $row->FirstName . " " . $row->LastName;
            $data['fuel_card_pin'] = $row->fuel_card_pin;
            $data['vin_num'] = $row->vin_num;
            $data['vehicle_ownership'] = $vehicle_ownership;
            $data['kms'] = $kms;
            $data['KMS_updated'] = $KMS_updated;

           
            fputcsv($file, $data);
        }

        fclose($file);
        exit;
    }

    public function add_tools() {
        $this->load->library('form_validation');
        $data['title'] = "Add Tools";
        $country_id = $this->config->item('country');

        $item = $this->input->post('item');
        $item_id = $this->input->post('item_id');
        $brand = ($item == 1) ? $this->input->post('brand_dp') : $this->input->post('brand_input');
        $description = ($item == 1) ? $this->input->post('description_dp') : $this->input->post('description_input');
        $purchase_date = $this->input->post('purchase_date');
        $purchase_date2 = ($purchase_date != "") ? $this->system_model->formatDate($purchase_date) : "";
        $purchase_price = $this->input->post('purchase_price');
        $assign_to_vehicle = $this->input->post('assign_to_vehicle');


        //item dropdown
        $sel_query = " DISTINCT(t.`item`), ti.`item_name` ";
        $params = array(
            'sel_query' => $sel_query,
            'country_id' => $country_id,
            'sort_list' => array(
                array(
                    'order_by' => 'ti.`item_name`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['item_dropdown'] = $this->vehicles_model->get_tools($params);

        //assign vehicle dropdown
        $sel_query = " DISTINCT(v.`vehicles_id`), v.`number_plate`, v.`tech_vehicle`, sa.FirstName, sa.LastName ";
        $params = array(
            'sel_query' => $sel_query,
            'country_id' => $country_id,
            'tech_vehicle' => 1,
            'sort_list' => array(
                array(
                    'order_by' => 'v.`number_plate`',
                    'sort' => 'ASC',
                ),
            ),
        );
        $data['assign_vehicle_dropdown'] = $this->vehicles_model->get_vehicles($params);

        //ADD TOOLS
        if ($this->input->post('btn_add_tools')) {

            $this->form_validation->set_rules('item', 'Item', 'required');
            $this->form_validation->set_rules('item_id', 'Item ID', 'required');

            if ($this->form_validation->run() != FALSE) {

                $db_data = array(
                    'item' => $item,
                    'item_id' => $item_id,
                    'brand' => $brand,
                    'description' => $description,
                    'purchase_date' => $purchase_date2,
                    'purchase_price' => $purchase_price,
                    'assign_to_vehicle' => $assign_to_vehicle,
                    'active' => 1,
                    'deleted' => 0,
                    'date_created' => date('Y-m-d H:i:s'),
                    'country_id' => $country_id
                );
                $this->db->insert('tools', $db_data);
                $this->db->limit(1);

                //set session success message
                $success_message = "New tools added";
                $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
                redirect(base_url('/vehicles/add_tools'), 'refresh');
            } else {
                $error_msg = "Error: Required field must not be empty";
                $this->session->set_flashdata(array('error_msg' => $error_msg, 'status' => 'error'));
                redirect(base_url('/vehicles/add_tools'), 'refresh');
            }
        }


        $this->load->view('templates/inner_header', $data);
        $this->load->view('vehicles/add_tools', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function ajax_update_vehicles() {

        $vehicles_id = $this->input->get_post('vehicles_id');
        $make = $this->input->get_post('make');
        $model = $this->input->get_post('model');
        $transmission = $this->input->get_post('transmission');
        $plant_id = $this->input->get_post('plant_id');
        $key_number = $this->input->get_post('key_number');
        $vin_num = $this->input->get_post('vin_num');
        $number_plate = $this->input->get_post('number_plate');
        $staff_id = $this->input->get_post('staff_id');
        $og_staff_id = $this->input->get_post('og_staff_id');
        $tech_vehicle = $this->input->get_post('tech_vehicle');
        $kms = $this->input->get_post('kms');
        $next_service = $this->input->get_post('next_service');
        $active = $this->input->get_post('active');
        $rego_expires = ( $this->system_model->isDateNotEmpty($this->input->get_post('rego_expires')) == true ) ? $this->system_model->formatDate($this->input->get_post('rego_expires')) : 'NULL';

        if ($this->config->item('country') == 2) { //WOF NZ ONLY
            $wof = ( $this->system_model->isDateNotEmpty($this->input->get_post('wof')) == true ) ? $this->system_model->formatDate($this->input->get_post('wof')) : NULL;
        } else {
            $wof = NULL;
        }
        $vehicle_ownership = $this->input->get_post('vehicle_ownership');


        $updateData = array(
            'make' => $make,
            'model' => $model,
            'plant_id' => $plant_id,
            'key_number' => $key_number,
            'vin_num' => $vin_num,
            'number_plate' => $number_plate,
            'StaffID' => $staff_id,
            'next_service' => $next_service,
            'tech_vehicle' => $tech_vehicle,
            'active' => $active,
            'rego_expires' => $rego_expires,
            'WOF' => $wof,
            'vehicle_ownership' => $vehicle_ownership,
            'transmission' => $transmission
        );

        $this->db->where('vehicles_id', $vehicles_id);
        $this->db->update('vehicles', $updateData);
        $updateRes = ($this->db->affected_rows() != 1) ? false : true;

        $data = array(
            'vehicles_id' => $vehicles_id,
            'kms' => $kms,
            'kms_updated' => date("Y-m-d H:i:s")
        );
        $this->db->insert('kms', $data);
        $addRes = ($this->db->affected_rows() != 1) ? false : true;
        
        if ($addRes || $updateRes) {

            //insert log if driver is updated
            if( $staff_id!=$og_staff_id ){

                $og_driver_name_params = array(
                    'staff_id' => $og_staff_id
                );
                $og_driver_name_q = $this->gherxlib->getStaffInfo($og_driver_name_params)->row_array();
                $og_driver_name = "{$og_driver_name_q['FirstName']} {$og_driver_name_q['LastName']}";

                $new_driver_name_params = array(
                    'staff_id' => $staff_id
                );
                $new_driver_name_q = $this->gherxlib->getStaffInfo($new_driver_name_params)->row_array();
                $new_driver_name = "{$new_driver_name_q['FirstName']} {$new_driver_name_q['LastName']}";

                $log_og_driver = ($og_staff_id=="") ? 'NULL' : $og_driver_name;
                $log_details = "Driver changed from {$log_og_driver} to $new_driver_name";
                $log_params = array(
                    'vehicles_id' => $vehicles_id,
                    'date' => date("Y-m-d H:i:s"),
                    'details' => $log_details,
                    'staff_id' => $this->session->staff_id
                );
                $this->db->insert('vehicles_log',$log_params);
            }

            //insert user detail log
            if($staff_id!=""){
                $saveData = [
                    'date' => date("Y-m-d H:i:s"),
                    'details' => $log_details,
                    'staff_id' => $staff_id,
                    'added_by' => $this->session->staff_id,
                ];
                $this->db->insert("user_log", $saveData);
            }
            
            if($og_staff_id!=""){
                $saveData = [
                    'date' => date("Y-m-d H:i:s"),
                    'details' => $log_details,
                    'staff_id' => $og_staff_id,
                    'added_by' => $this->session->staff_id,
                ];
                $this->db->insert("user_log", $saveData);
            }
            
            echo true;
        } else {
            echo false;
        }

       /* if ($updateRes) {
            $data = array(
                'vehicles_id' => $vehicles_id,
                'kms' => $kms,
                'kms_updated' => date("Y-m-d H:i:s")
            );
            $this->db->insert('kms', $data);
            $addRes = ($this->db->affected_rows() != 1) ? false : true;
            if ($addRes) {
                echo true;
            } else {
                echo false;
            }
        } else {
            echo false;
        }*/
    }

    public function add_vehicle() {

        $data['title'] = "Add Vehicle";

        //get staff name
        $staff_params = array(
            'sel_query' => "DISTINCT(ca.`staff_accounts_id`), sa.`FirstName`, sa.`LastName`",
            'sort_list' => array(
                array(
                    'order_by' => 'sa.FirstName',
                    'sort' => 'ASC'
                ),
            ),
        );
        $staff_info = $this->gherxlib->getStaffInfo($staff_params)->result_array();

        $data['staff_info'] = $staff_info;
        $this->load->view('templates/inner_header', $data);
        $this->load->view('vehicles/add_vehicle', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function view_vehicle_details() {

        $data['title'] = "Vehicle Details";
        //get staff name
        $staff_params = array(
            'sel_query' => "DISTINCT(ca.`staff_accounts_id`), sa.`FirstName`, sa.`LastName`",
            'sort_list' => array(
                array(
                    'order_by' => 'sa.FirstName',
                    'sort' => 'ASC'
                ),
            ),
        );

        $vehicle_id = $this->uri->segment(3);

        $data['vehicle'] = $this->vehicles_model->get_vehicle_details($vehicle_id);
        $data['driver'] = $this->vehicles_model->get_driver($data['vehicle']->StaffID);

        $data['kms'] = $this->vehicles_model->get_vehicle_details_kms($vehicle_id);

        $data['staff_info'] = $this->gherxlib->getStaffInfo($staff_params)->result_array();

        $this->load->view('templates/inner_header', $data);
        $this->load->view('vehicles/vehicle_details/index', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function get_vehicle_logs() {

        $columns = array( 
            0 => 'vehicles_log_id', 
            1 => 'date',
            2 => 'price',
            3 => 'details',
            4 => 'name'
        );

		$limit = $this->input->post('length');
        $start = $this->input->post('start');
        $vehicles_id = $this->input->post('vehicles_id');
        $order = $columns[$this->input->post('order')[0]['column']];
        $dir = $this->input->post('order')[0]['dir'];
  
        $totalData = $this->vehicles_model->all_logs_count($vehicles_id);
            
        $totalFiltered = $totalData; 
            
        if(empty($this->input->post('search')['value'])) {            
            $logs = $this->vehicles_model->all_logs($vehicles_id,$limit,$start,$order,$dir);
        }
        else {
            $search = $this->input->post('search')['value']; 
            $logs =  $this->vehicles_model->logs_search($vehicles_id,$limit,$start,$search,$order,$dir);
            $totalFiltered = $this->vehicles_model->logs_search_count($vehicles_id,$search);
        }

        $data = array();
        if(!empty($logs)) {
            foreach ($logs as $log)
            {
                $date = date_create($log->date);
                $nestedData['id'] = $log->vehicles_log_id;
                $nestedData['date'] = date_format($date,"m/d/Y");
                $nestedData['price'] = '$'.$log->price;
                $nestedData['details'] = $log->details;
                $nestedData['name'] = $log->name;
                $nestedData['action'] = "<button class='btn btn-danger btn-delete-log' type='button' data-id='$log->vehicles_log_id'><span class='fa fa-trash'></span></button>
                <button class='btn btn-primary btn-view-files' type='button' data-id='$log->vehicles_log_id'><span class='fa fa-file'></span></button>";
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw" => intval($this->input->post('draw')),  
            "recordsTotal" => intval($totalData),  
            "recordsFiltered" => intval($totalFiltered), 
            "data" => $data   
        );
        echo json_encode($json_data); 
    }

    public function datatable_tools() {

        $columns = array( 
            0 => 'item_id', 
            1 => 'brand',
            2 => 'description'
        );

		$limit = $this->input->post('length');
        $start = $this->input->post('start');
        $vehicles_id = $this->input->post('vehicles_id');
        $order = $columns[$this->input->post('order')[0]['column']];
        $dir = $this->input->post('order')[0]['dir'];
  
        $totalData = $this->vehicles_model->all_tools_count($vehicles_id);
            
        $totalFiltered = $totalData; 
            
        if(empty($this->input->post('search')['value'])) {            
            $tools = $this->vehicles_model->all_tools($vehicles_id,$limit,$start,$order,$dir);
        }
        else {
            $search = $this->input->post('search')['value']; 
            $tools =  $this->vehicles_model->tools_search($vehicles_id,$limit,$start,$search,$order,$dir);
            $totalFiltered = $this->vehicles_model->tools_search_count($vehicles_id,$search);
        }

        $data = array();
        if(!empty($tools)) {
            foreach ($tools as $tool) {
                $nestedData['item_id'] = $tool->item_id;
                $nestedData['brand'] = $tool->brand;
                $nestedData['description'] = $tool->description;
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw" => intval($this->input->post('draw')),  
            "recordsTotal" => intval($totalData),  
            "recordsFiltered" => intval($totalFiltered), 
            "data" => $data   
        );
        echo json_encode($json_data); 
    }

    public function datatable_files() {
        $columns = array( 
            0 => 'filename', 
            1 => 'date'
        );

		$limit = $this->input->post('length');
        $start = $this->input->post('start');
        $vehicles_id = $this->input->post('vehicles_id');
        $order = $columns[$this->input->post('order')[0]['column']];
        $dir = $this->input->post('order')[0]['dir'];
  
        $totalData = $this->vehicles_model->all_files_count($vehicles_id);
            
        $totalFiltered = $totalData; 
            
        if(empty($this->input->post('search')['value'])) {            
            $files = $this->vehicles_model->all_files($vehicles_id,$limit,$start,$order,$dir);
        }
        else {
            $search = $this->input->post('search')['value']; 
            $files =  $this->vehicles_model->files_search($vehicles_id,$limit,$start,$search,$order,$dir);
            $totalFiltered = $this->vehicles_model->files_search_count($vehicles_id,$search);
        }

        $data = array();
        if(!empty($files)) {
            foreach ($files as $file)
            {
                $date = date_create($file->date);
                $nestedData['filename'] = "<a href='../../../uploads/vehicle_files/$vehicles_id/$file->filename' target='_blank' class='text-link'>$file->filename</a>";
                $nestedData['date'] = date_format($date,"m/d/Y");
                $nestedData['action'] = "<button class='btn btn-danger btn-delete-file' type='button' data-id='$file->vehicle_files_id'><span class='fa fa-trash'></span></button>";
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw" => intval($this->input->post('draw')),  
            "recordsTotal" => intval($totalData),  
            "recordsFiltered" => intval($totalFiltered), 
            "data" => $data   
        );
        echo json_encode($json_data); 
    }

    public function datatable_log_files() {
        $columns = array( 
            0 => 'filename', 
            1 => 'date'
        );

		$limit = $this->input->post('length');
        $start = $this->input->post('start');
        $vehicle_log_id = $this->input->post('vehicle_log_id');
        $order = $columns[$this->input->post('order')[0]['column']];
        $dir = $this->input->post('order')[0]['dir'];
  
        $totalData = $this->vehicles_model->all_log_files_count($vehicle_log_id);
            
        $totalFiltered = $totalData; 
            
        if(empty($this->input->post('search')['value'])) {            
            $files = $this->vehicles_model->all_log_files($vehicle_log_id,$limit,$start,$order,$dir);
        }
        else {
            $search = $this->input->post('search')['value']; 
            $files =  $this->vehicles_model->log_files_search($vehicle_log_id,$limit,$start,$search,$order,$dir);
            $totalFiltered = $this->vehicles_model->log_files_search_count($vehicle_log_id,$search);
        }

        $data = array();
        if(!empty($files)) {
            foreach ($files as $file)
            {
                $nestedData['filename'] = "<a href='../../../uploads/vehicle_log_files/$file->vehicle_id/$file->filename' target='_blank' class='text-link'>$file->filename</a>";
                $nestedData['date'] = $file->date;
                $nestedData['action'] = "<button class='btn btn-danger btn-delete-file' type='button' data-id='$file->vehicle_log_file_id'><span class='fa fa-trash'></span></button>";
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw" => intval($this->input->post('draw')),  
            "recordsTotal" => intval($totalData),  
            "recordsFiltered" => intval($totalFiltered), 
            "data" => $data   
        );
        echo json_encode($json_data); 
    }



    public function vehicle_file_upload() {
        $vehicle_id = $this->input->post('vehicle_id');

        $folder = "uploads/vehicle_files/$vehicle_id";
        if (!file_exists($folder)) {
            $create_dir = mkdir($folder);
        }

        $file = pathinfo($_FILES["file"]['name']);
        $filename = $file['filename'] . "_" . date('Ymd') . "_" . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT) . "." . $file['extension'];
        $config['upload_path'] = $folder;
        $config['allowed_types'] = '*';
        $config['max_size'] = 2000;
        $config['file_name'] = $filename;
        $this->load->library('upload', $config);
        $uploadFile = $this->upload->do_upload('file');
       
        if ($uploadFile) {
            $upload_data = $this->upload->data();
            
            $params = [
                'vehicles_id' => $vehicle_id,
                'filename' => $upload_data['file_name'],
                'path' => $folder,
                'date' => date("Y-m-d H:i:s")
            ];
            $this->db->insert('vehicle_files', $params);
            $this->db->limit(1);
            
            $message = "Document has been added";
            $status_code = 200;
            
        } else {
            $upload_err_msg = strip_tags($this->upload->display_errors());
            $message = $upload_err_msg;
            $status_code = 400;
        }

        echo json_encode([
            "statusCode" => $status_code,
            "message" => $message
        ]);
    }


    public function add_vehicle_script() {

        $make = $this->input->post('make');
        $model = $this->input->post('model');
        $year = $this->input->post('year');
        $number_plate = $this->input->post('number_plate');
        $rego_expires = ($this->input->post('rego_expires') != "") ? $this->system_model->formatDate($this->input->post('rego_expires')) : null;
        $warranty_expires = ($this->input->post('warranty_expires') != "") ? $this->system_model->formatDate($this->input->post('warranty_expires')) : null;
        $fuel_type = $this->input->post('fuel_type');
        $etag_num = $this->input->post('etag_num');
        $serviced_by = $this->input->post('serviced_by');
        $fuel_card_num = $this->input->post('fuel_card_num');
        $purchase_date = ($this->input->post('purchase_date') != "") ? $this->system_model->formatDate($this->input->post('purchase_date')) : '0000-00-00';
        $purchase_price = $this->input->post('purchase_price');
        $ra_num = $this->input->post('ra_num');
        $ins_pol_num = $this->input->post('ins_pol_num');
        $policy_expires = ($this->input->post('pol_exp') != "") ? $this->system_model->formatDate($this->input->post('pol_exp')) : null;
        $StaffID = $this->input->post('StaffID');
        $fuel_card_pin = $this->input->post('fuel_card_pin');
        $vin_num = $this->input->post('vin_num');
        $plant_id = $this->input->post('plant_id');
        $tech_vehicle = $this->input->post('tech_vehicle');
        $country_id = $this->config->item('country');
        $key_number = $this->input->post('key_number');
        $transmission = $this->input->post('transmission');

        //WOF Applies only NZ not AU
        if ($this->config->item('country') == 2) { //NZ
            $wof = $this->system_model->formatDate($this->input->post('wof'));
        } else {
            $wof = NULL;
        }

        $vehicle_ownership = $this->input->post('vehicle_ownership');


        $data = array(
            'make' => $make,
            'model' => $model,
            'year' => $year,
            'number_plate' => $number_plate,
            'rego_expires' => $rego_expires,
            'warranty_expires' => $warranty_expires,
            'fuel_type' => $fuel_type,
            'etag_num' => $etag_num,
            'serviced_by' => $serviced_by,
            'fuel_card_num' => $fuel_card_num,
            'purchase_date' => $purchase_date,
            'purchase_price' => $purchase_price,
            'ra_num' => $ra_num,
            'ins_pol_num' => $ins_pol_num,
            'policy_expires' => $policy_expires,
            'StaffID' => $StaffID,
            'fuel_card_pin' => $fuel_card_pin,
            'vin_num' => $vin_num,
            'plant_id' => $plant_id,
            'tech_vehicle' => $tech_vehicle,
            'country_id' => $country_id,
            'key_number' => $key_number,
            'WOF' => $wof,
            'vehicle_ownership' => $vehicle_ownership,
            'transmission' => $transmission
        );

        //WOF Applies only NZ not AU
        if ($this->config->item('country') !== 2) { //NZ
            unset($data['WOF']);
        }
        if ($warranty_expires == null) {
            unset($data['warranty_expires']);
        }
        if ($policy_expires == null) {
            unset($data['policy_expires']);
        }

        $this->db->insert('vehicles', $data);
        $this->db->limit(1);

        $v_id = $this->db->insert_id();

        // email settings
        $email_config = Array(
            'mailtype' => 'html',
            'charset' => 'utf-8'
        );

        $from_email = $this->config->item('sats_info_email'); 
        $from_name = 'Smoke Alarm Testing Services'; 
        
        //$to_email = 'vaultdweller123@gmail.com';
        $to_email = $this->config->item('sats_accounts_email');
        $subject = 'New Vehicle Added';
        
        $email_body = "<p>Hi Accounts Team</p><br />

        <p>A new Vehicle has been added, the Number Plate is $number_plate</p>
        
        <p>
        Kind Regard,<br />
        Dev Team
        </p>";

        $this->email->initialize($email_config);   
        $this->email->clear(TRUE);         
        $this->email->from($from_email, $from_name);                
        $this->email->to($to_email);                                             

        $this->email->subject($subject);
        $this->email->message($email_body);

        // send email
        $this->email->send();  
        
        $insert_data = array(
            'vehicles_id' => $v_id,
            'kms' => 0,
            'kms_updated' => date('Y-m-d H:i:s'),
        );
    
        $this->db->insert('kms', $insert_data);
        $this->db->limit(1);

        $success_message = "New vehicle added";
        $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
        redirect(base_url('/vehicles/add_vehicle'), 'refresh');
    }

    public function update_vehicle_script() {

        $vehicles_id = $this->input->post('vehicles_id');

        //Make Group
        $make = $this->input->post('make');
        $transmission = $this->input->post('transmission');
        $model = $this->input->post('model');
        $plant_id = $this->input->post('plant_id');
        $year = $this->input->post('year');
        $vin_num = $this->input->post('vin_num');

        //Purchase Group
        $purchase_date = ($this->input->post('purchase_date') != "") ? $this->system_model->formatDate($this->input->post('purchase_date')) : '0000-00-00';
        $purchase_price = $this->input->post('purchase_price');
        $warranty_expires = ($this->input->post('warranty_expires') != "") ? $this->system_model->formatDate($this->input->post('warranty_expires')) : null;

        //Fuel Group
        $fuel_type = $this->input->post('fuel_type');
        $fuel_card_num = $this->input->post('fuel_card_num');
        $fuel_card_pin = $this->input->post('fuel_card_pin');

        //Toll Pass Group
        $etag_num = $this->input->post('etag_num');

        //Driver Group
        $StaffID = $this->input->post('StaffID');

        //Ownership Group
        $vehicle_ownership = $this->input->post('vehicle_ownership');
        $serviced_by = $this->input->post('serviced_by');
        $ra_num = $this->input->post('ra_num'); //Road Assistance Number
        $tech_vehicle = $this->input->post('tech_vehicle');

        //Kilometers Group
        $kms = $this->input->post('kms');
        $next_service = $this->input->post('next_service');

        //WOF Applies only NZ not AU
        if ($this->config->item('country') == 2) { //NZ
            $wof = $this->system_model->formatDate($this->input->post('wof'));
        } else {
            $wof = NULL;
        }

        $update_data = array(
            'StaffID' => $StaffID,
            'make' => $make,
            'model' => $model,
            'year' => $year,
            'fuel_type' => $fuel_type,
            'etag_num' => $etag_num,
            'serviced_by' => $serviced_by,
            'ra_num' => $ra_num, //Road Assistance Number
            'next_service' => $next_service,
            'fuel_card_num' => $fuel_card_num,
            'purchase_date' => $purchase_date,
            'purchase_price' => $purchase_price,
            'fuel_card_pin' => $fuel_card_pin,
            'vin_num' => $vin_num,
            'plant_id' => $plant_id,
            'tech_vehicle' => $tech_vehicle,
            'country_id' => $country_id,
            'WOF' => $wof,
            'vehicle_ownership' => $vehicle_ownership,
            'transmission' => $transmission
        );


        if ($make == null || $transmission == null || $model == null || $plant_id == null || $year == null || $vin_num == null) {
            unset($data['make']);
            unset($data['transmission']);
            unset($data['model']);
            unset($data['plant_id']);
            unset($data['year']);
            unset($data['vin_num']);
        }

        if ($purchase_date == null || $purchase_price == null || $warranty_expires == null) {
            unset($data['purchase_date']);
            unset($data['purchase_price']);
            unset($data['warranty_expires']);
        }

        if ($fuel_type == null || $fuel_card_num == null || $fuel_card_pin == null) {
            unset($data['fuel_type']);
            unset($data['fuel_card_num']);
            unset($data['fuel_card_pin']);
        }

        if ($etag_num == null) {
            unset($data['etag_num']);
        }

        if ($StaffID == null) {
            unset($data['StaffID']);
        }

        if ($vehicle_ownership == null || $serviced_by == null || $ra_num == null || $tech_vehicle == null) {
            unset($data['vehicle_ownership']);
            unset($data['serviced_by']);
            unset($data['ra_num']);
            unset($data['tech_vehicle']);
        }

        //WOF Applies only NZ not AU
        if ($this->config->item('country') !== 2) { //NZ
            unset($data['WOF']);
        }
        if ($warranty_expires == null) {
            unset($data['warranty_expires']);
        }

        if ($year == null) {
            unset($data['year']);
        }

        if ($policy_expires == null) {
            unset($data['policy_expires']);
        }

        $this->db->where('vehicles_id', $vehicles_id);
        $this->db->update('vehicles', $update_data);
        $this->db->limit(1);

        if($kms){
            $insert_data = array(
                'vehicles_id' => $vehicles_id,
                'kms' => $kms,
                'kms_updated' => date('Y-m-d H:i:s')
            );
    
            $this->db->insert('kms', $insert_data);
            $this->db->limit(1);
        }
        $success_message = "Update Success";
        $this->session->set_flashdata(array('success_msg'=>$success_message,'status'=>'success'));
        redirect(base_url('/vehicles/view_vehicle_details/'.$vehicles_id), 'refresh');
    }

    public function ajax_update_fields() {
        $postData = $this->input->post();

        $this->db->trans_start();
        
        $affectedRows = 0;
        foreach($postData as $table => $tableData) {
            $idField = $tableData["_idField"];
            $idValue = $tableData["_idValue"];
            
            if ($idField == 'vehicles_id1') {

                //remove the 1 in the vehicles_id1 for the notification of updating the plate number
                $field = substr($idField, 0, -1);

                //old number_plate
                $this->db->select('number_plate');
                $this->db->from('vehicles');
                $this->db->where('vehicles_id', $idValue);
                $pt_sql = $this->db->get();
                $pt_row = $pt_sql->row();
                $old_plate = $pt_row->number_plate;

                $email_data['old_plate'] = $old_plate;

                //save then get the new value of number plate
                $this->db->set($tableData["fields"])
                ->where($field, $idValue)
                ->update($table);

                //new number_plate
                $this->db->select('number_plate');
                $this->db->from('vehicles');
                $this->db->where('vehicles_id', $idValue);
                $pt_sql = $this->db->get();
                $pt_row = $pt_sql->row();
                $new_plate = $pt_row->number_plate;
                
                $email_data['new_plate'] = $new_plate;
                
                $email_from = $this->config->item('sats_accounts_email');
                $email_to = $this->config->item('sats_accounts_email');
                $email_subject = "Vehicle Registration Notification";
                
                //email config
                $config = Array(
                    'mailtype' => 'html',
                    'charset' => 'iso-8859-1'
                );
                $this->email->initialize($config);
                $this->email->set_newline("\r\n");
                
                $this->email->from($email_from, 'CRM SATS');
                $this->email->to($email_to); 
                $this->email->subject($email_subject);
                $body = $this->load->view('/emails/number_plate_email_notification.php', $email_data, TRUE);
                $this->email->message($body);
                $this->email->send();
            } else {
                $field = $idField;

                $this->db->set($tableData["fields"])
                ->where($field, $idValue)
                ->update($table);
            }
        }

        $this->db->trans_complete();

        $success = $this->db->trans_status();

        $jsonData = [];
        $jsonData["success"] = $success;

        if ($success) {
            $jsonData["message"] = "Update successful.";
        }
        else {
            $jsonData["message"] = "Update failed.";
        }

        echo json_encode($jsonData);
        return;
    }

    

    public function upload_vehicle_pic() {
        if ($this->input->method() == "post") {
            $postData = $this->input->post();
            $id = $postData["vehicles_id"];
            $userDataToSave = [];

            $uploadFile = function($fileField, $uploadPath) use ($id) {
                //Upload front image
                if (isset($_FILES[$fileField]) && !empty($_FILES[$fileField]['name'])) {
                    if(!is_dir($uploadPath)){
                        mkdir($uploadPath,0755,TRUE);
                    } 
                    $imagePath = $_FILES[$fileField]['name'];
                    $ext = pathinfo($imagePath, PATHINFO_EXTENSION);
                    $imageName = "img_{$id}_" . rand() . "_" . date("YmdHis");
                    $uploadParams = array(
                        'file_name' => $imageName,
                        'upload_path' => $uploadPath,
                        'max_size' => '2048',
                        'allowed_types' => '*'
                    );
                    if ($this->gherxlib->do_upload($fileField, $uploadParams)) {
                        return "{$imageName}.{$ext}";
                    }
                }
                return false;
            };

            $fileFields = [
                [
                    'fileField' => 'vehicle_pic',
                    'columnName' => 'image',
                    'uploadPath' => 'images/vehicle/',
                ],
            ];

            foreach ($fileFields as $fileData) {
                $uploadResult = $uploadFile($fileData['fileField'], $fileData['uploadPath']);
                if ($uploadResult != false) {
                    $userDataToSave[$fileData['columnName']] = $uploadResult;
                }
            }

            $this->db->trans_start();
            $this->db->update('vehicles', $userDataToSave, "vehicles_id = {$id}", 1);
            $this->db->trans_complete();
            echo json_encode([
                "success" => 'ok',
            ]);
        }
    }

    public function remove_vehicle_pic($vehicle_id) {
        $success = false;
        if ($this->input->method() == "delete") {
            $result = $this->db->set(["image" => null])
                ->where("vehicles_id", $vehicle_id)
                ->update("vehicles");
            if ($result) {
                $success = true;
            }
        }
        echo json_encode([
            "success" => $success,
        ]);
    }

    public function remove_vehicle_file($id) {
        $success = false;
        if ($this->input->method() == "delete") {
            //$filename = $this->vehicles_model->select_file($id);
            //$path_to_file = "/uploads/vehicle_files/$filename->vehicles_id/$filename->filename";

            //if(unlink($path_to_file)) {
                $result = $this->db->delete('vehicle_files', array('vehicle_files_id' => $id));
                if ($result) {
                    $success = true;
                }
            // }else {
            //     $success = false;
            // }

        }
        echo json_encode([
            "success" => $success,
        ]);
    }

    public function remove_log_file($id) {
        $success = false;
        if ($this->input->method() == "delete") {
                $result = $this->db->delete('vehicle_log_files', array('vehicle_log_file_id' => $id));
                if ($result) {
                    $success = true;
                }
        }
        echo json_encode([
            "success" => $success,
        ]);
    }

    public function log_delete($id) {
        $success = false;
        if ($this->input->method() == "delete") {
            $result = $this->db->delete('vehicles_log', array('vehicles_log_id' => $id));

            if ($result) {
                $success = true;
            }
        }
        echo json_encode([
            "success" => $success,
        ]);
    }



    public function update_vehicle_kilometers() {

        //Vehicles ID
        $vehicles_id = $this->input->post('vehicle_id');

        //Kilometers Group
        $kms = $this->input->post('kms');
        $next_service = $this->input->post('next_service');

        $update_data = array(
            'next_service' => $next_service
        );

        $this->db->where('vehicles_id', $vehicles_id);
        $this->db->update('vehicles', $update_data);
        $this->db->limit(1);

        $insert_data = array(
            'vehicles_id' => $vehicles_id,
            'kms' => $kms,
            'kms_updated' => date('Y-m-d H:i:s'),
        );
    
        $this->db->insert('kms', $insert_data);
        $this->db->limit(1);
    }

    
    public function add_vehicle_servicing_script() {

        $id = $this->input->post('vehicles_id');
        $date = ($this->input->post('log_date') != "") ? $this->system_model->formatDate($this->input->post('log_date')) : null;
        $price = $this->input->post('log_price');
        $details = $this->input->post('log_details');

        $data = array(
            'vehicles_id' => $id,
            'date' => $date,
            'price' => $price,
            'details' => $details,
            'staff_id' => $this->session->staff_id
        );

        $this->db->insert('vehicles_log', $data);
        $this->db->limit(1);

        $insert_id = $this->db->insert_id();

        $folder = "uploads/vehicle_log_files/$id";
        if (!file_exists($folder)) {
            $create_dir = mkdir($folder);
        }

        $data = [];
        $count = count($_FILES['files']['name']);
    
        for($i=0;$i<$count;$i++){
            $filename = '';
            if(!empty($_FILES['files']['name'][$i])){
        
                $_FILES['file']['name'] = $_FILES['files']['name'][$i];
                $_FILES['file']['type'] = $_FILES['files']['type'][$i];
                $_FILES['file']['tmp_name'] = $_FILES['files']['tmp_name'][$i];
                $_FILES['file']['error'] = $_FILES['files']['error'][$i];
                $_FILES['file']['size'] = $_FILES['files']['size'][$i];

                $file = pathinfo($_FILES['files']['name'][$i]);
                $filename = $file['filename'] . "_" . date('Ymd') . "_" . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT) . "." . $file['extension'];
    
                $config['upload_path'] = $folder; 
                $config['allowed_types'] = '*';
                $config['max_size'] = '5000';
                $config['file_name'] = $filename;
    
                $this->load->library('upload',$config); 
                $this->upload->initialize($config);
                $uploadFile = $this->upload->do_upload('file');

                if ($uploadFile) {
                    $upload_data = $this->upload->data();

                    $params = [
                        'vehicle_log_id' => $insert_id,
                        'vehicle_id' => $id,
                        'path' => $folder,
                        'filename' => $upload_data['file_name'],
                        'date' => date("Y-m-d H:i:s")
                    ];
                    $this->db->insert('vehicle_log_files', $params);
                    $this->db->limit(1);
                }
            }
        }
    }

    //  DISPLAY KMS PAGE
    public function view_kms() {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "KMS Report";

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( null != $this->input->get_post('offset') ) ? $this->input->get_post('offset') : '0';

        $vehicle = ( $this->input->get_post('vehicle') != "" ) ? $this->input->get_post('vehicle') : '';
        $driver = ( $this->input->get_post('driver') != "" ) ? $this->input->get_post('driver') : '';
        $country = $this->config->item('country');

        // GET KMS
        $params = [
            'paginate' => [
                'offset' => $offset,
                'limit' => $per_page
            ],
            'sort_list' => [
                [
                    'order_by' => 'k.`kms_updated`',
                    'sort' => 'DESC'
                ]
            ],
            'vehicle' => $vehicle,
            'driver' => $driver,
            'country' => $country
        ];
        $data['kms'] = $this->kms_model->get_kms($params);

        $vparams['sel_query'] = 'DISTINCT (
            k.`vehicles_id`
            ), v.`number_plate`
        ';
        $data['vehicles'] = $this->kms_model->get_kms($vparams);
        $data['filter_vehicle'] = $vehicle;
        $data['filter_driver'] = $driver;

        $dparams['sel_query'] = "DISTINCT (
            `sa`.`StaffID`
            ), sa.`StaffID`, sa.`FirstName` , sa.`LastName`";
        $dparams['selection'] = "driver";
        $dparams['sort_list'] = [
            ['order_by' => '`sa`.`FirstName`', 'sort' => 'ASC'],
            ['order_by' => '`sa`.`LastName`', 'sort' => 'ASC']
        ];
//        $dparams['echo_query'] = 1;
        $data['drivers'] = $this->kms_model->get_kms($dparams);
        // TOTAL ROWS
        $tparams = [
            'sel_query' => 'COUNT(*) as kms_count'
        ];
        $total_rows = $data['kms']->num_rows();


        // BASE URL
        $base_url = '/vehicles/view_kms';

        // PAGINATION
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = $base_url;
        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();


        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

        $this->load->view('templates/inner_header', $data);
        $this->load->view('vehicles/kms', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function update_tool(){

        $tools_id = $this->input->post('tools_id');
        $item = $this->input->post('item');
        $item_id = $this->input->post('item_id');
        $brand = $this->input->post('brand');
        $description = $this->input->post('description');
        $purchase_date = $this->input->post('purchase_date');
        $purchase_date2 = $this->system_model->formatDate($purchase_date);
        $purchase_price = $this->input->post('purchase_price');
        $assign_to_vehicle = $this->input->post('assign_to_vehicle');

        //update tools

        $update_data = array(
            'item_id' => $item_id,
            'brand' => $brand,
            'description' => $description,
            'purchase_date' => $purchase_date2,
            'purchase_price' => $purchase_price,
            'assign_to_vehicle' => $assign_to_vehicle
        );
        $this->db->where('tools_id', $tools_id);
        $this->db->update('tools', $update_data);
        $this->db->limit(1);

        //set session success message
        $success_message = "Update Success";
        $this->session->set_flashdata(array('success_msg'=>$success_message,'status'=>'success'));
        redirect(base_url("/vehicles/view_tool_details/{$tools_id}"),'refresh');

    }
    

    public function ladder_check_details(){

        $ladder_check_id = $this->uri->segment(3);
        $data['ladder_check_id'] = $this->uri->segment(3);
        $tools_id = $this->uri->segment(4);
        $data['tools_id'] = $this->uri->segment(4);


        //get laddercheck
        $v_params = array(
            'sel_query' => "*",
            'ladder_check_id' => $ladder_check_id
        );
        $data['lc'] = $this->vehicles_model->getLadderCheck($v_params)->row_array();


        //getLadderInspection
        $getLadderInspection_params = array(
            'sel_query' => "*"
        );
        $data['li_sql'] = $this->vehicles_model->getLadderInspection($getLadderInspection_params);


        $data['title'] = "Ladder Check Details";
        $this->load->view('templates/inner_header', $data);
        $this->load->view('vehicles/ladder_check_details', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    /**
     * Update Ladder
     */
    public function ladder_check_update(){

        $ladder_check_id = $this->input->post('ladder_check_id');
        $tools_id = $this->input->post('tools_id');
        $ladder_inspection = $this->input->post('ladder_inspection');
        $date = $this->input->post('date');
        $date2 = $this->system_model->formatDate($date);
        $inspection_due = $this->input->post('inspection_due');
        $inspection_due2 = $this->system_model->formatDate($inspection_due);


        if(!empty($ladder_check_id) && is_numeric($ladder_check_id)){


            //update ladder_check
            $update_data = array(
                'date' => $date2,
                'inspection_due' => $inspection_due2
            );
            $this->db->where('ladder_check_id', $ladder_check_id);
            $this->db->where('tools_id', $tools_id);
            $this->db->update('ladder_check', $update_data);


            //clear/delete
            $this->db->where('ladder_check_id', $ladder_check_id);
            $this->db->delete('ladder_inspection_selection');


            // loop and insert ladder_inspection_selection
            foreach( $ladder_inspection as $index=>$li_id ){
	
                $ladder_opt = $this->input->post('ladder_opt'.($index+1));

                $insert_data = array(
                    'ladder_check_id' => $ladder_check_id,
                    'ladder_inspection_id' => $li_id,
                    'value' => $ladder_opt
                );
                $this->db->insert('ladder_inspection_selection', $insert_data);
                
            }

            //set session success message
            $success_message = "Update Successful";
            $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
            redirect(base_url("/vehicles/ladder_check_details/{$ladder_check_id}/{$tools_id}"), 'refresh');


        }


    }

    //ADD LADDER CHECK
    public function ladder_check(){

        $tools_id = $this->uri->segment(3);
        $data['tools_id'] = $this->uri->segment(3);

        //redirect if no tools id
        if(!$tools_id || $tools_id=="" || !is_numeric($tools_id)){
            redirect('/vehicles/view_tools','refresh');
        }


      
        //if press submit insert new data
        if($this->input->post('btn_add_ladder_check')){

            $tools_id = $this->input->post('tools_id');
            $ladder_inspection = $this->input->post('ladder_inspection');
            $date = $this->input->post('date');
            $date2 = $this->system_model->formatDate($date);
            $inspection_due = $this->input->post('inspection_due');
            $inspection_due2 = $this->system_model->formatDate($inspection_due);

            //Insert ladder_check
            $insert_params = array(
                'tools_id' => $tools_id,
                'date' => $date2,
                'inspection_due' => $inspection_due2
            );
            $this->db->insert('ladder_check', $insert_params);

            $ladder_check_id = $this->db->insert_id();


            //Loop and insert ladder_inspection_selection
            foreach( $ladder_inspection as $index=>$li_id ){
	
                $ladder_opt = $this->input->post('ladder_opt'.($index+1));

                $ladder_inspection_data = array(
                    'ladder_check_id' => $ladder_check_id,
                    'ladder_inspection_id' => $li_id,
                    'value' => $ladder_opt
                );
                $this->db->insert('ladder_inspection_selection', $ladder_inspection_data);  
                
            }

              //set session success message
              $success_message = "New Data Successfully Added";
              $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
              redirect(base_url("/vehicles/view_tool_details/{$tools_id}"), 'refresh');


        }else{ //normal view

            //getLadderInspection
            $getLadderInspection_params = array(
                'sel_query' => "*"
            );
            $data['li_sql'] = $this->vehicles_model->getLadderInspection($getLadderInspection_params);
           
    
            $data['title'] = "Ladder Check";
            $this->load->view('templates/inner_header', $data);
            $this->load->view('vehicles/ladder_check', $data);
            $this->load->view('templates/inner_footer', $data);

        }


    }


    public function test_tag(){

        $tool_id = $this->uri->segment(3);
        $data['tool_id'] = $this->uri->segment(3);

        if($this->input->post('btn_add_test_tag')){ // submit pressed > insert test tag

            $tools_id = $this->input->post('tools_id');
            $date = $this->input->post('date');
            $date2 = $this->system_model->formatDate($date);
            $tnt_comp = $this->input->post('tnt_comp');
            $comment = $this->input->post('comment');
            $inspection_due = $this->input->post('inspection_due');
            $inspection_due2 = $this->system_model->formatDate($inspection_due);

            
            //Insert test_and_tag
            $test_and_tag_data = array(
                'tools_id' => $tools_id,
                'date' => $date2,
                'tnt_completed' => $tnt_comp,
                'comment' => $comment,
                'inspection_due' => $inspection_due2
            );
            $this->db->insert('test_and_tag', $test_and_tag_data);

            //set session success message
            $success_message = "New Data Successfully Added";
            $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
            redirect(base_url("/vehicles/view_tool_details/{$tools_id}"), 'refresh');


        }else{//normal view list

            $data['title'] = "Test and Tag";
            $this->load->view('templates/inner_header', $data);
            $this->load->view('vehicles/test_tag', $data);
            $this->load->view('templates/inner_footer', $data);

        }

        
    }

    /**
     * Test and Tag Detail/Update page
     */
    public function test_tag_details(){

        $test_and_tag_id = $this->uri->segment(3);
        $data['test_and_tag_id'] = $this->uri->segment(3);
        $tools_id = $this->uri->segment(4);
        $data['tools_id'] = $this->uri->segment(4);

         //get test and tag
         $v_params = array(
            'sel_query' => "*",
            'test_and_tag_id' => $test_and_tag_id
        );
        $tnt_sql = $this->vehicles_model->getTestAndTag($v_params);
        $data['tnt'] = $tnt_sql->row_array();

        $data['title'] = "Test and Tag Details";
        $this->load->view('templates/inner_header', $data);
        $this->load->view('vehicles/test_tag_details', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    /**
     * Update Test Tag script
     */
    public function test_tag_update(){

        $test_and_tag_id = $this->input->post('test_and_tag_id');
        $tools_id = $this->input->post('tools_id');
        $date = $this->input->post('date');
        $date2 = $this->system_model->formatDate($date);
        $tnt_comp = $this->input->post('tnt_comp');
        $comment = $this->input->post('comment');
        $inspection_due = $this->input->post('inspection_due');
        $inspection_due2 = $this->system_model->formatDate($inspection_due);

        //Insert test_and_tag
        $test_and_tag_data = array(
            'date' => $date2,
            'tnt_completed' => $tnt_comp,
            'comment' => $comment,
            'inspection_due' => $inspection_due2
        );
        $this->db->where('test_and_tag_id',$test_and_tag_id);
        $this->db->where('tools_id',$tools_id);
        $this->db->update('test_and_tag', $test_and_tag_data);

        //set session success message
        $success_message = "Update Successful";
        $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
        redirect(base_url("/vehicles/test_tag_details/{$test_and_tag_id}/{$tools_id}"), 'refresh');



    }

    /**
     * Add lockout_kit_check
     */
    public function lockout_kit_check(){

        $tools_id = $this->uri->segment(3);
        $data['tools_id'] = $this->uri->segment(3);

        if(!$tools_id || empty($tools_id) || !is_numeric($tools_id)){
            redirect("/vehicles/view_tools",'refresh');
        }

        if($this->input->post('btn_add_lockout_kit_check')){ // SUBMIT CLICKED > add new lockout_kit_check

            //post
            $tools_id = $this->input->post('tools_id');
            $lockout_kit_checklist = $this->input->post('lockout_kit_checklist');
            $date = $this->input->post('date');
            $date2 = $this->system_model->formatDate($date);
            $checklist_due = $this->input->post('checklist_due');
            $checklist_due2 = $this->system_model->formatDate($checklist_due);

            //insert lockout_kit_check
            $lockout_kit_check_data = array(
                'tools_id' => $tools_id,
                'date' => $date2,
                'inspection_due' => $checklist_due2
            );
            $this->db->insert('lockout_kit_check', $lockout_kit_check_data);

            $lockout_kit_check_id = $this->db->insert_id();

            //insert lockout_kit_checklist_selection
            foreach( $lockout_kit_checklist as $index=>$li_id ){
	
                $lockout_kit_opt = $this->input->post('lockout_kit_opt'.($index+1));
                
                $lockout_kit_checklist_selection_data = array(
                    'lockout_kit_check_id' => $lockout_kit_check_id,
                    'lockout_kit_checklist_id' => $li_id,
                    'value' => $lockout_kit_opt
                );
                $this->db->insert('lockout_kit_checklist_selection', $lockout_kit_checklist_selection_data);
                
            }

            //set session success message
            $success_message = "New Data Successfully Added";
            $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
            redirect(base_url("/vehicles/view_tool_details/{$tools_id}"), 'refresh');


        }else{ //NORMAL VIEW LIST

            //getLockOutKitCheckList
            $params = array(
                'sel_query' => "*"
            );
            $data['li_sql'] = $this->vehicles_model->getLockOutKitCheckList($params);

            $data['title'] = "Lockout Kit Check";
            $this->load->view('templates/inner_header', $data);
            $this->load->view('vehicles/lockout_kit_check', $data);
            $this->load->view('templates/inner_footer', $data);
            
        }

    }

    //lockout_kit_check_details/edit page
    public function lockout_kit_check_details(){

        $lockout_kit_check_id = $this->uri->segment(3);
        $data['lockout_kit_check_id'] = $this->uri->segment(3);
        $tools_id = $this->uri->segment(4);
        $data['tools_id'] = $this->uri->segment(4);

        //get lockout kit check
        $lkc_params = array(
            'lockout_kit_check_id' => $lockout_kit_check_id
        );
        $lkc_sql = $this->vehicles_model->getLockoutKitCheck($lkc_params);
        $data['lc'] = $lkc_sql->row_array();


        //get getLockOutKitCheckList
        $getLockOutKitCheckList_params = array(
            'sel_query' => "*"
        );
        $data['li_sql'] = $this->vehicles_model->getLockOutKitCheckList($getLockOutKitCheckList_params);


        $data['title'] = "Lockout Kit Check Details";
        $this->load->view('templates/inner_header', $data);
        $this->load->view('vehicles/lockout_kit_check_details', $data);
        $this->load->view('templates/inner_footer', $data);

    }

    public function lockout_kit_check_update(){

        $lockout_kit_check_id = $this->input->post('lockout_kit_check_id');
        $tools_id = $this->input->post('tools_id');
        $lockout_kit_checklist = $this->input->post('lockout_kit_checklist');
        $date = $this->input->post('date');
        $date2 = $this->system_model->formatDate($date);
        $checklist_due = $this->input->post('checklist_due');
        $inspection_due2 = $this->system_model->formatDate($checklist_due);

        if(!empty($lockout_kit_check_id) && !empty($tools_id)){

            //update lockout_kit_check
            $update_data = array(
                'date' => $date2,
                'inspection_due' => $inspection_due2
            );
            $this->db->where('lockout_kit_check_id',$lockout_kit_check_id);
            $this->db->where('tools_id',$tools_id);
            $this->db->update('lockout_kit_check', $update_data);


            //clear
            $this->db->where('lockout_kit_check_id',$lockout_kit_check_id);
            $this->db->delete('lockout_kit_checklist_selection');

            
            //loop and insert lockout_kit_checklist_selection
            foreach( $lockout_kit_checklist as $index=>$li_id ){
	
                $lockout_kit_opt = $this->input->post('lockout_kit_opt'.($index+1));
                
                $lockout_kit_checklist_selection_data = array(
                    'lockout_kit_check_id' => $lockout_kit_check_id,
                    'lockout_kit_checklist_id' => $li_id,
                    'value' => $lockout_kit_opt
                );
                $this->db->insert('lockout_kit_checklist_selection', $lockout_kit_checklist_selection_data);
                
                
            }

            //set session success message
            $success_message = "Update Successful";
            $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
            redirect(base_url("/vehicles/lockout_kit_check_details/{$lockout_kit_check_id}/{$tools_id}"), 'refresh');
 

        }

    }

    public function ajax_duplicate_vehicle_user(){
        $json_data['status'] = false;

        $staffid = $this->input->get_post('staffid');

        if($staffid!=""){
            $staff_id_where = "(StaffID = {$staffid} AND StaffID!=1)";

            $this->db->select('vehicles_id');
            $this->db->from('vehicles');
            $this->db->where($staff_id_where);
            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                $json_data['status'] = true;
            } else {
                $json_data['status'] = false;

            }
        }else{
            $json_data['status'] = false;
        }

        

        echo json_encode($json_data);
    }

}

?>
