<?php

class Stock extends CI_Controller {

    public function __construct(){

        parent::__construct();
		$this->load->helper('url');
		$this->load->library('pagination');
        $this->load->database();
        $this->load->model('stock_model');
    }



    public function stock_items(){

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Stock Items";

        $country_id = $this->config->item('country');

         // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        //GET LIST
        $sel_query  = "
        s.stocks_id, 
        s.code, 
        s.item, 
        s.display_name, 
        s.price, 
        s.display, 
        s.show_on_stocktake, 
        s.status, 
        s.`carton`,
        s.`is_alarm`,
        
        sup.company_name, 
        sup.suppliers_id
        ";
        $params = array(
            'sel_query' => $sel_query,
            'country_id' => $country_id,
            'sort_list' => array(
                array(
                    'order_by' => 's.`code`',
                    'sort' => 'ASC',
                )
            )
        );
        $data['lists'] = $this->stock_model->getStock($params);

        //all list count
        $sel_query  = "COUNT(s.stocks_id) AS s_count";
        $params = array(
            'sel_query' => $sel_query,
            'country_id' => $country_id
        );
        $query = $this->stock_model->getStock($params);
        $total_rows = $query->row()->s_count;

        $pagi_links_params_arr = array(
            'suppliers_id' => ''
        );
        $pagi_link_params = '/stock/stock_items/?'.http_build_query($pagi_links_params_arr);
    
    
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
        $this->load->view('reports/stock_items', $data);
        $this->load->view('templates/inner_footer', $data);

    }


    public function add_tech_stock_process(){

        $this->load->library('form_validation');

        $code = $this->input->post('code');
        $item = $this->input->post('item');
        $display_name = $this->input->post('display_name');
        $price = $this->input->post('price');
        $display = ($this->input->post('display'))?$this->input->post('display'):0;
        $supplier = $this->input->post('supplier');
        $show_on_stocktake = ($this->input->post('show_on_stocktake'))?$this->input->post('show_on_stocktake'):0;        
        $carton = ( is_numeric($this->input->post('carton')) == true )?$this->input->post('carton'):null;


        //validate
		$this->form_validation->set_rules('code', 'Code', 'required');
		$this->form_validation->set_rules('item', 'Item', 'required');
		$this->form_validation->set_rules('price', 'Price', 'required');

		if($this->form_validation->run()!=FALSE){

            $data = array(
                'code' => $code,
                'item' => $item,
                'display_name' => $display_name,
                'price' => $price,
                'display' => $display,
                'created' => date("Y-m-d H:i:s"),
                'country_id' => $this->config->item('country'),
                'suppliers_id' => $supplier,
                'show_on_stocktake' => $show_on_stocktake,
                'carton' => $carton
            );
            $this->db->insert('stocks',$data);

            if($this->db->affected_rows()>0){

                $success_message = "Stock Successfully Added";
                $this->session->set_flashdata(array('success_msg'=>$success_message,'status'=>'success'));
                redirect(base_url('/stock/stock_items'),'refresh');

            }

        }else{
            $error_msg = "Error: Required fields must not be empty";
			$this->session->set_flashdata(array('error_msg'=>$error_msg,'status'=>'error'));
			redirect(base_url('/stock/stock_items'),'refresh');
        }



    }


    public function edit_tech_stock_process(){

        $this->load->library('form_validation');

        $stock_id = $this->input->post('stocks_id');
        $code = $this->input->post('code_edit');
        $item = $this->input->post('item_edit');
        $display_name = $this->input->post('display_name_edit');
        $price = $this->input->post('price_edit');
        $display = ($this->input->post('display_edit'))?$this->input->post('display_edit'):0;
        $status = $this->input->post('status_edit');
        $supplier = $this->input->post('supplier_edit');
        $show_on_stocktake = ($this->input->post('show_on_stocktake_edit'))?$this->input->post('show_on_stocktake_edit'):0;
        $is_alarm = ($this->input->post('is_alarm_edit'))?$this->input->post('is_alarm_edit'):0;
        $carton = ( is_numeric($this->input->post('carton_edit')) == true )?$this->input->post('carton_edit'):null;

        //validate
		$this->form_validation->set_rules('code_edit', 'Code', 'required');
		$this->form_validation->set_rules('item_edit', 'Item', 'required');
		$this->form_validation->set_rules('price_edit', 'Price', 'required');

		if($this->form_validation->run()!=FALSE){

            if($stock_id && $stock_id!="" && is_numeric($stock_id)){
                $data = array(
                    'code' => $code,
                    'item' => $item,
                    'display_name' => $display_name,
                    'price' => $price,
                    'display' => $display,
                    'status' => $status,
                    'suppliers_id' => $supplier,
                    'show_on_stocktake' => $show_on_stocktake,
                    'is_alarm' => $is_alarm,
                    'carton' => $carton
                );
                $this->db->where('stocks_id', $stock_id);
                $this->db->update('stocks',$data);
                $this->db->limit(1);

                if($this->db->affected_rows()>0){

                    $success_message = "Stock successfully updated";
                    $this->session->set_flashdata(array('success_msg'=>$success_message,'status'=>'success'));
                    redirect(base_url('/stock/stock_items'),'refresh');

                }else{
                    $error_msg = "Error: Required fields must not be empty";
                    $this->session->set_flashdata(array('error_msg'=>$error_msg,'status'=>'error'));
                    redirect(base_url('/stock/stock_items'),'refresh');
                }
            }
        }


    }


    public function suppliers(){
       
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Suppliers";

        $country_id = $this->config->item('country');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');


        //GET SUPPLIER LIST
        $sel_query = "suppliers_id, company_name, service_provided, address, contact_name, phone, email, website, notes, on_map, sales_agreement_number";
        $params = array(
            'sel_query' => $sel_query,
            'sort_list' => array(
                array(
                    'order_by' => 'company_name',
                    'sort' => 'ASC'
                )
            )
        );
        $data['lists'] = $this->stock_model->getSupplier($params);


        $this->load->view('templates/inner_header', $data);
        $this->load->view('reports/suppliers', $data);
        $this->load->view('templates/inner_footer', $data);
        
    }

    public function add_suppliers(){

        $this->load->library('form_validation');

        $country_id = $this->config->item('country');
        $company_name = $this->input->post('company_name');
        $service_provided = $this->input->post('service_provided');
        $address = $this->input->post('address');
        $contact_name = $this->input->post('contact_name');
        $phone = $this->input->post('phone');
        $email = $this->input->post('email');
        $website = $this->input->post('website');
        $notes = $this->input->post('notes');
        $sales_agreement_number = $this->input->post('sales_agreement_number');

        $coor = $this->system_model->getGoogleMapCoordinates($address);

         //validate
		$this->form_validation->set_rules('company_name', 'Company Name', 'required');
		$this->form_validation->set_rules('service_provided', 'Service Provided', 'required');
        
        if($this->form_validation->run()!=FALSE){

            $params  = array(
                'company_name' => $company_name,
                'service_provided' => $service_provided,
                'address' => $address,
                'contact_name' => $contact_name,
                'phone' => $phone,
                'email' => $email,
                'website' => $website,
                'notes' => $notes,
                'country_id' => $country_id,
                'lat' => $coor['lat'],
                'lng' => $coor['lng'],
                'on_map' => 1,
                'sales_agreement_number' => $sales_agreement_number
            );
            $this->db->insert('suppliers', $params);
            $this->db->limit(1);

            if($this->db->affected_rows()>0){

                $success_message = "Supplier Successfully Added";
                $this->session->set_flashdata(array('success_msg'=>$success_message,'status'=>'success'));
                redirect(base_url('/stock/suppliers'),'refresh');

            }

        }else{
            $error_msg = "Error: Required fields must not be empty";
            $this->session->set_flashdata(array('error_msg'=>$error_msg,'status'=>'error'));
            redirect(base_url('/stock/suppliers'),'refresh');
        }


    }


    public function update_suppliers(){

        $country_id = $this->config->item('country');
        $supp_id = $this->input->post('supp_id');
        $company_name = $this->input->post('company_name_edit');
        $service_provided = $this->input->post('service_provided_edit');
        $address = $this->input->post('address_edit');
        $contact_name = $this->input->post('contact_name_edit');
        $phone = $this->input->post('phone_edit');
        $email = $this->input->post('email_edit');
        $website = $this->input->post('website_edit');
        $notes = $this->input->post('notes_edit');
        $on_map = $this->input->post('on_map_edit');
        $sales_agreement_number = $this->input->post('sales_agreement_number');

        $coor = $this->system_model->getGoogleMapCoordinates($address);

        if($supp_id && !empty($supp_id) && is_numeric($supp_id)){

            $params  = array(
                'company_name' => $company_name,
                'service_provided' => $service_provided,
                'address' => $address,
                'contact_name' => $contact_name,
                'phone' => $phone,
                'email' => $email,
                'website' => $website,
                'notes' => $notes,
                'country_id' => $country_id,
                'lat' => $coor['lat'],
                'lng' => $coor['lng'],
                'on_map' => $on_map,
                'sales_agreement_number' => $sales_agreement_number
            );
            $this->db->where('suppliers_id', $supp_id);
            $this->db->update('suppliers', $params);
            $this->db->limit(1);
    
            if($this->db->affected_rows()>0){
    
                $success_message = "Supplier Successfully Updated";
                $this->session->set_flashdata(array('success_msg'=>$success_message,'status'=>'success'));
                redirect(base_url('/stock/suppliers'),'refresh');
    
            }

        }


    }

    /**
     * Delete Supplier via ajax
     */
    public function ajax_delete_supplier(){

        $json_data['status'] = false;

        $suppliers_id = $this->input->post('supp_id');

        if($suppliers_id && $suppliers_id!="" && is_numeric($suppliers_id)){

            $this->db->where('suppliers_id', $suppliers_id);
            $this->db->delete('suppliers');
          

            if($this->db->affected_rows()>0){
                $json_data['status'] = true;
                $json_data['msg'] = 'Supplier successfully deleted';
            }

        }else{

            $json_data['status'] = false;
            $json_data['msg'] = 'Error: Please contact admin';

        }

        
        echo json_encode($json_data);



    }


    public function tech_stock(){

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Tech Stock Report";
        $uri = "/stock/tech_stock";
        $data['uri'] = $uri;

        $btn_post = $this->input->get_post('btn_search');
        $date = ($this->input->get_post('date_filter')!="")?$this->system_model->formatDate($this->input->get_post('date_filter')):NULL; 
        $from = ($this->input->get_post('date_from_filter')!="")?$this->system_model->formatDate($this->input->get_post('date_from_filter')):NULL; 
        $to = ($this->input->get_post('date_to_filter')!="")?$this->system_model->formatDate($this->input->get_post('date_to_filter')):NULL; 
        $tech = $this->input->get_post('tech_filter');
        $vehicle = $this->input->get_post('vech_filter');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');
        $export = $this->input->get_post('export');

        if($btn_post && $from!=""){
            $is_search = 1;
        }else{
            $is_search = 0;
        }

        //get tech stock list
        $params = array(
            'sel_query' => 'sa.FirstName, sa.LastName, ts_main.date, ts_main.tech_stock_id, ts_main.staff_id, v.number_plate, sa.is_electrician',
            'from' => $from,
            'to' => $to,
            'tech' => $tech,
            'vehicle' => $vehicle,
            'sort_list' => array(
                array(
                    'order_by' => 'ts_main.date',
                    'sort' => 'DESC'
                )
            ),
            'is_search' => $is_search,
            'disable_tech_vehicle_filter' => 1,
            'display_query' => 0
        );

        $tech_stock_sql = $this->stock_model->getTechStock($params);

        //get stocks
        $get_stock_params = array(
            'sel_query' => '*',
            'display' => 1,
            'sort_list' => array(
                array(
                    'order_by' => 'sort_index',
                    'sort' => 'ASC'
                )
            )
        );
        $stocks_sql = $this->stock_model->getStocks($get_stock_params);

        if ( $export == 1 ) { // EXPORT         

            // file name
            $date_export = date('YmdHis');
            $filename = "job_feedback_export_{$date_export}.csv";

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            // file creation 
            $csv_file = fopen('php://output', 'w');            

            //$header = array("Technician","Vehicle","Day","From","Tenant","Message","Technician");

            $header[] = 'Technician';
            $header[] = 'Vehicle';
            $header[] = 'Day';
            $header[] = 'Date';

            foreach( $stocks_sql->result() as $stocks_row ){
                $header[] = $stocks_row->display_name;
            }

            fputcsv($csv_file, $header);
            
            $tot_array = [];
            foreach ( $tech_stock_sql->result_array() as $row ){ 

                $csv_row = [];                              

                $csv_row[] = $this->system_model->formatStaffName($row['FirstName'], $row['LastName']);
                $csv_row[] = $row['number_plate'];
                $csv_row[] = ( $this->system_model->isDateNotEmpty($row['date']) )?date('l', strtotime($row['date'])):null;
                $csv_row[] = ( $this->system_model->isDateNotEmpty($row['date']) )?date('d/m/Y H:i', strtotime($row['date'])):null;
                foreach( $stocks_sql->result_array() as $s ){

                    $ts_sql2 = $this->stock_model->getTechStockItems($row['tech_stock_id'],$s['stocks_id']);
                    $ts2 = $ts_sql2->row_array();
                    $ts2['quantity'];
                    $tot_array[$s['stocks_id']] = $tot_array[$s['stocks_id']]+$ts2['quantity'];
                    $csv_row[] = $ts2['quantity'];

                }           
                
                fputcsv($csv_file,$csv_row); 

            }
            
            
        
            fclose($csv_file); 
            exit; 

        }else{

            $data['lists'] = $tech_stock_sql;

            //get total
            $params = array(
                'sel_query' => 'COUNT(ts_main.tech_stock_id) as s_count',
                'from' => $from,
                'to' => $to,
                'is_search' => $is_search,
                'disable_tech_vehicle_filter' => 1,
                'display_query' => 0
            );
            $query = $this->stock_model->getTechStock($params);
            $total_rows = $query->row()->s_count;
            

            //get stocks
            $data['getStocks'] = $stocks_sql;


            //Tech FILTER
            $tech_params = array(
                'sel_query' => 'DISTINCT(ts_main.staff_id), sa.FirstName, sa.LastName',
                'sort_list' => array(
                    array(
                        'order_by' => 'sa.FirstName',
                        'sort' => 'ASC'
                    )
                ),
                'is_search' => $is_search,
                'display_query' => 0
            );
            $data['tech'] = $this->stock_model->getTechStock($tech_params);

            //Vech Filter
            $vech_params = array(
                'sel_query' => 'DISTINCT(ts_main.vehicle), v.number_plate, ',
                'sort_list' => array(
                    array(
                        'order_by' => 'v.number_plate',
                        'sort' => 'ASC'
                    )
                ),
                'is_search' => $is_search,
                'display_query' => 0
            );
            $data['vech'] = $this->stock_model->getTechStock($vech_params);




            $pagi_links_params_arr = array(
                'date_from_filter' => $from,
                'date_to_filter' => $to,
                'tech_filter' => $tech,
                'vech_filter' => $vehicle
            );           

            // pagination link
            $pagi_link_params = "{$uri}/?".http_build_query($pagi_links_params_arr);

            // explort link
            $data['export_link'] = "{$uri}/?export=1&".http_build_query($pagi_links_params_arr);

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
            $this->load->view('reports/tech_stock', $data);
            $this->load->view('templates/inner_footer', $data);

        }        

    }


    public function update_tech_stock($staff_id=null,$tech_stock_id=null){

        $data['title'] = "Update Tech Stock";
        
        $data['staff_id'] = ( $staff_id > 0 )?$staff_id:$this->session->staff_id;
        $data['tech_stock_id'] = $tech_stock_id;
        $staff_class_id = $this->system_model->getStaffClassID();

         //get stocks
         $get_stock_params = array(
            'sel_query' => '*',
            'status' => 1,
            'show_on_stocktake' => 1,
            'sort_list' => array(
                array(
                    'order_by' => 'item',
                    'sort' => 'ASC'
                )
            )
        );
        $data['getStocks'] = $this->stock_model->getStocks($get_stock_params);

        if( $staff_class_id == 6 ){ // tech
            $this->load->view('templates/inner_header_tech', $data);
        }else{
            $this->load->view('templates/inner_header', $data);
        }    
        $this->load->view('reports/update_tech_stock', $data);
        $this->load->view('templates/inner_footer', $data);

    }


    public function update_tech_stock_process(){

        $this->load->model('tech_model');

        $stocks = $this->input->post('stocks');
        $staff_id = $this->input->post('staff_id');
        $quantity = $this->input->post('quantity');
        $vehicle = $this->input->post('vehicle');

        $staff_class_id = $this->system_model->getStaffClassID();

        //Insert tech_stock
        $insert_data = array(
            'staff_id' => $staff_id,
            'date' => date("Y-m-d H:i:s"),
            'status' => 1,
            'country_id' => $this->config->item('country'),
            'vehicle' => $vehicle
        );
        $this->db->insert('tech_stock', $insert_data);
        $ts_id = $this->db->insert_id();


        //Insert tech_stock_items
        if($ts_id){
            foreach($stocks as $index=>$stock_id){

                $tech_stock_params = array(
                    'tech_stock_id' => $ts_id,
                    'stocks_id' => $stock_id,
                    'quantity' => $quantity[$index],
                    'status' => 1
                );
                $this->db->insert('tech_stock_items', $tech_stock_params);
            
            }
        }


        $success_message = "Stock Successfully Updated";
        $this->session->set_flashdata(array('success_msg'=>$success_message,'status'=>'success'));
        
        if( $staff_class_id == 6 ){ // tech
            redirect("/home");  
        }else{
            redirect("/stock/update_tech_stock/{$staff_id}");
        }
        
                     

    }
    
	



}
?>
