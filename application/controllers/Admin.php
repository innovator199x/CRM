<?php

class Admin extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('admin_model');
        $this->load->model('cron_model');
        $this->load->library('pagination');
    }

    public function accommodation() {

        $this->load->model('calendar_model');
        $area = $this->input->get_post('area');
        $name = $this->input->get_post('name');
        $search = $this->input->get_post('search');
        $export = $this->input->get_post('export');
        $country_id = $this->config->item('country');
        $filter_sql_str = null;

        $uri = '/admin/accommodation';
        $data['uri'] = $uri;

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ($this->input->get_post('offset')!="")?$this->input->get_post('offset'):0;

        /*
        //get accomodation
        $acco_params = array(
            'sel_query' => "*",
            'area' => $area,
            'search' => $search,
            'sort_list' => array(
                array('order_by' => 'area', 'sort' => 'ASC')
            ),
            'display_query' => 1
        );
        */

        
        if( $area != '' ){

            $filter_sql_str .= "
            AND `area` = '{$area}'
            ";

        }

        if( $name != '' ){

            $filter_sql_str .= "
            AND `name` LIKE '%{$name}%'
            ";

        }        
        
        if( $search != '' ){

            $filter_sql_str .= "
            AND CONCAT_WS(' ', LOWER(`street_number`), LOWER(`street_name`), LOWER(`suburb`), LOWER(`state`), LOWER(`postcode`) ) 
            LIKE '%" . strtolower($search) . "%'
            ";

        }                 

        $acco_sql_str = "
        SELECT 
            `accomodation_id`,
            `name`,
            `area`,
            `address`,         
            `street_number`,
            `street_name`,
            `suburb`,
            `state`,
            `postcode`,
            `phone`,
            `email`,
            `rate`,
            `comment`
        FROM `accomodation`
        WHERE `country_id` = {$country_id}
        {$filter_sql_str}
        ORDER BY `area` ASC        
        ";

        if($export==1){

            //$export_q = $this->calendar_model->getAccomodation($acco_params);

            $export_q = $this->db->query($acco_sql_str); // export has no pagination

            // file name
            $filename = 'accomodation_' . date('Y-m-d') . '.csv';

            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Pragma: no-cache");
            header("Expires: 0");

            // file creation
            $file = fopen('php://output', 'w');

            //header
            $header = array("Name", "Area", "Street Number", "Street Name", "Suburb", "State", "Postcode", "Phone", "Email", "Rate", "Comment");
            fputcsv($file, $header);

            foreach($export_q->result_array() as $row){
                
                $exportdata['Name'] =  $row['name'];
                $exportdata['Area'] = $row['area'];
                $exportdata['street_number'] = $row['street_number'];
                $exportdata['street_name'] = $row['street_name'];
                $exportdata['suburb'] = $row['suburb'];
                $exportdata['state'] = $row['state'];
                $exportdata['postcode'] = $row['postcode'];
                $exportdata['Phone'] = $row['phone'];
                $exportdata['Email'] = $row['email'];
                $exportdata['Rate'] = "$".$row['rate'];
                $exportdata['Comment'] =  $row['comment'];

                fputcsv($file, $exportdata);

            }

            fclose($file);
            exit;
            
        }else{

            $acco_with_pagination_sql_str = " 
            {$acco_sql_str}
            LIMIT {$offset}, {$per_page}"; // add pagination

            //$data['accomodation_list'] = $this->calendar_model->getAccomodation($acco_params);

            $data['accomodation_list'] = $this->db->query($acco_with_pagination_sql_str);
            
             // get all
            $acco_sql_tot_sql_str = "
            SELECT COUNT(`accomodation_id`) AS acco_count
            FROM `accomodation`
            WHERE `country_id` = {$country_id}
            ORDER BY `area` ASC                  
            ";
            $acco_sql_tot_sql = $this->db->query($acco_sql_tot_sql_str);
            $total_rows = $acco_sql_tot_sql->row()->acco_count;  

            $pagi_links_params_arr = array();
        
            $data['header_link_params'] = $pagi_links_params_arr;
            // export link
            $data['export_link_params'] = "{$uri}/?export=1&".http_build_query($pagi_links_params_arr);  

            $pagi_link_params = $uri.'?'.http_build_query($pagi_links_params_arr);


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

            //area filter
            $area_params = array(
                'sel_query' => "DISTINCT(area), area",
                'sort_list' => array(
                    array('order_by' => 'area', 'sort' => 'ASC')
                )
            );
            $data['area_list'] = $this->calendar_model->getAccomodation($area_params);

            $data['title'] = "Accommodation";
            //load views
            $this->load->view('templates/inner_header', $data);
            $this->load->view('admin/accomodation', $data);
            $this->load->view('templates/inner_footer', $data);

        }
        
    }

    /**
     * UPdate Accomodation
     */
    public function ajax_update_accomodation() {

        $accomodation_id = $this->input->post('accomodation_id');
        $name = $this->input->post('name');
        $area = $this->input->post('area');
        $address = $this->input->post('address');
        $street_number = $this->input->post('street_number');
        $street_name = $this->input->post('street_name');
        $suburb = $this->input->post('suburb');
        $state = $this->input->post('state');
        $postcode = $this->input->post('postcode');
        $phone = $this->input->post('phone');
        $email = $this->input->post('email');
        $rate = $this->input->post('rate');
        $comment = $this->input->post('comment');

        if (!empty($accomodation_id)) {

            //update accomodation
            $arroy = array();
            if (!empty($address)) {

                $address2 = "{$address}";
                $coordinate = $this->system_model->getGoogleMapCoordinates($address2);

                $arroy[] = array(
                    'address' => $address,
                    'street_number' => $street_number,
                    'street_name' => $street_name,
                    'suburb' => $suburb,
                    'state' => $state,
                    'postcode' => $postcode,
                    'lat' => $coordinate['lat'],
                    'lng' => $coordinate['lng']
                );
            }
            $arroy[] = array(
                'name' => $name,
                'area' => $area,
                'phone' => $phone,
                'email' => $email,
                'rate' => $rate,
                'comment' => $comment
            );

            $list = array();

            foreach ($arroy as $arr) {
                if (is_array($arr)) {
                    $list = array_merge($list, $arr);
                }
            }

            $this->db->where('accomodation_id', $accomodation_id);
            $this->db->update('accomodation', $list);
            $this->db->limit(1);

            //redirect and set session
            $success_message = "Accomodation Update Successfull";
            $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
            redirect(base_url('/admin/accommodation'), 'refresh');

        }
    }

    /**
     * DELETE ACCOMODATION
     */
    public function ajax_delete_accomodation() {

        $jsondata['status'] = false;
        $accomodation_id = $this->input->post('accomodation_id');

        if (!empty($accomodation_id)) {

            $this->db->where('accomodation_id', $accomodation_id);
            $this->db->delete('accomodation');
            $this->db->limit(1);

            $jsondata['status'] = true;
        }

        echo json_encode($jsondata);
    }

    /**
     * Add new accomodation
     */
    public function accomodation_process() {

        $name = $this->input->post('name');
        $area = $this->input->post('area');
        $address = $this->input->post('address');
        $street_number = $this->input->post('street_number');
        $street_name = $this->input->post('street_name');
        $suburb = $this->input->post('suburb');
        $state = $this->input->post('state');
        $postcode = $this->input->post('postcode');
        $phone = $this->input->post('phone');
        $email = $this->input->post('email');
        $rate = $this->input->post('rate');
        $comment = $this->input->post('comment');

        $insert_data = array(
            'name' => $name,
            'area' => $area,
            'address' => $address,
            'street_number' => $street_number,
            'street_name' => $street_name,
            'suburb' => $suburb,
            'state' => $state,
            'postcode' => $postcode,
            'phone' => $phone,
            'email' => $email,
            'rate' => $rate,
            'comment' => $comment,
            'country_id' => $this->config->item('country')
        );
        $this->db->insert('accomodation', $insert_data);
        $this->db->limit(1);

        //redirect and set session
        $success_message = "Accomodation Successfully Added";
        $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
        redirect(base_url('/admin/accommodation'), 'refresh');
    }

    public function alarm_pricing_page() {

        $this->load->model('alarms_model');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');


        //all list
        $params = array(
            'sel_query' => "*",
            'limit' => $per_page,
            'offset' => $offset
        );
        $data['lists'] = $this->alarms_model->get_alarm_power($params);
        $data['alarm_types'] = $this->alarms_model->getAlarmType($params);
        


        //total rows
        $total_params = array(
            'sel_query' => "COUNT(ap.`alarm_pwr_id`) AS ap_count",
            'limit' => $per_page,
            'offset' => $offset
        );
        $query = $this->alarms_model->get_alarm_power($total_params);
        $total_rows = $query->row()->ap_count;


        // pagination settings
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['base_url'] = '/admin/alarm_pricing_page';

        $this->pagination->initialize($config);

        $data['pagination'] = $this->pagination->create_links();

        // pagination count
        $pc_params = array(
            'total_rows' => $total_rows,
            'offset' => $offset,
            'per_page' => $per_page
        );
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

        $data['title'] = "Alarm Pricing Page";
        $this->load->view('templates/inner_header', $data);
        $this->load->view('admin/alarm_pricing_page', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    //update alarm pricing
    public function ajax_update_alarm_pricing() {

        $json_data['status'] = false;

        $alarm_pwr_id = $this->input->post('alarm_pwr_id');
        $alarm_pwr = $this->input->post('alarm_pwr');
        $alarm_make = $this->input->post('alarm_make');
        $alarm_model = $this->input->post('alarm_model');
        $alarm_expiry = $this->input->post('alarm_expiry');
        $alarm_price_ex = $this->input->post('alarm_price_ex');
        $alarm_price_inc = $this->input->post('alarm_price_inc');
        $active = $this->input->post('active');
        $battery_type = $this->input->post('battery_type');
        $is_replaceable = $this->input->post('is_replaceable');
        $alarm_pwr_source = $this->input->post('alarm_pwr_source');
        $alarm_type = $this->input->post('alarm_type');

        if ($alarm_pwr_id && !empty($alarm_pwr_id)) {

            $this->db->where('alarm_pwr_id', $alarm_pwr_id);
            $update_data = array(
                'alarm_pwr' => $alarm_pwr,
                'alarm_make' => $alarm_make,
                'alarm_model' => $alarm_model,
                'alarm_expiry' => $alarm_expiry,
                'alarm_price_ex' => $alarm_price_ex,
                'alarm_price_inc' => $alarm_price_inc,
                'active' => $active,
                'battery_type' => $battery_type,
                'is_replaceable' => $is_replaceable,
                'alarm_pwr_source' => $alarm_pwr_source,
                'alarm_type' => $alarm_type,
            );
            $this->db->update('alarm_pwr', $update_data);
            $this->db->limit(1);

            $json_data['status'] = true;
        }

        echo json_encode($json_data);
    }

    public function add_alarm_pricing() {

        $alarm_pwr = $this->input->post('name');
        $alarm_make = $this->input->post('make');
        $alarm_model = $this->input->post('model');
        $alarm_expiry = $this->input->post('expiry');
        $alarm_price_ex = $this->input->post('price_ex_gst');
        $alarm_price_inc = $this->input->post('price_inc_gst');
        $battery_type = $this->input->post('battery_type');
        $is_replaceable = $this->input->post('is_replaceable');
        $alarm_pwr_source = $this->input->post('alarm_pwr_source');

        if (!empty($alarm_pwr)) {

            $data = array(
                'alarm_pwr' => $alarm_pwr,
                'alarm_make' => $alarm_make,
                'alarm_model' => $alarm_model,
                'alarm_expiry' => $alarm_expiry,
                'alarm_price_ex' => $alarm_price_ex,
                'alarm_price_inc' => $alarm_price_inc,
                'alarm_job_type_id' => 2,
                'alarm_type' => 2,
                'active' => 1,
                'battery_type' => $battery_type,
                'is_replaceable' => $is_replaceable,
                'alarm_pwr_source' => $alarm_pwr_source
            );
            $this->db->insert('alarm_pwr', $data);
            $this->db->limit(1);

            //redirect and set session
            $success_message = "New Alarm Pricing Added";
            $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
            redirect(base_url('/admin/alarm_pricing_page'), 'refresh');
        }
    }

    public function agency_site_maintenance_mode() {

        $data['m'] = $this->admin_model->get_agency_site_maintenance_mode()->row_array();

        $data['title'] = "Maintenance Mode";
        $this->load->view('templates/inner_header', $data);
        $this->load->view('admin/agency_site_maintenance_mode', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function emergency_action() {

        $uri = "/admin/emergency_action";
        $data['uri'] = $uri;

        // get crm settings
        $agency_portal_mm_sql_str = "
            SELECT 
                `agency_portal_mm`, 
                `disable_all_crons`
            FROM `crm_settings` 
            WHERE `country_id` = {$this->config->item('country')}
        ";
        $agency_portal_mm_sql = $this->db->query($agency_portal_mm_sql_str);
        $agency_portal_mm_row = $agency_portal_mm_sql->row();   

        $data['agency_portal_mm'] = $agency_portal_mm_row->agency_portal_mm; // get agency maintenance mode
        $data['disable_all_crons'] = $agency_portal_mm_row->disable_all_crons; // get crons disable status

        // get all active cron jobs, excluding cron flush
        $cron_flush_id = '11,14,15,16,19,20,21,22,23,24,25,26';

        $cron_sql_str = "
            SELECT 
                `cron_type_id`, 
                `type_name`, 
                `description`,
                `ci_link`,
                `active_cron`
            FROM `cron_types` AS ct 
            WHERE `active` = 1
            AND `cron_type_id` NOT IN({$cron_flush_id})
        ";
        $data['cron_sql'] = $this->db->query($cron_sql_str);

        $data['title'] = "Emergency Action";
        $this->load->view('templates/inner_header', $data);
        $this->load->view($uri, $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function ajax_agency_maintenance_mode_toggle() {

        $agency_portal_mm = $this->input->post('agency_portal_mm');

        if( is_numeric($agency_portal_mm) ){

            $this->db->query("
                UPDATE `crm_settings`
                SET `agency_portal_mm` = {$agency_portal_mm}
                WHERE `country_id` = {$this->config->item('country')}
            ");

        }        
      
    }

    public function ajax_disable_all_crons_toggle() {

        $disable_all_crons = $this->input->post('disable_all_crons');

        if( is_numeric($disable_all_crons) ){

            $this->db->query("
                UPDATE `crm_settings`
                SET `disable_all_crons` = {$disable_all_crons}
                WHERE `country_id` = {$this->config->item('country')}
            ");

        }        
      
    }

    public function ajax_disable_indiv_crons_toggle() {

        $cron_type_id = $this->input->post('cron_type_id');
        $cron_status = $this->input->post('cron_status');

        if( $cron_type_id > 0 && is_numeric($cron_status) ){

            $this->db->query("
                UPDATE `cron_types`
                SET `active_cron` = {$cron_status}
                WHERE `cron_type_id` = {$cron_type_id}
            ");

        }        
      
    }

    public function ajax_switch_agency_site_maintenance_mode() {

        $mode = $this->input->post('mode');

        $switch_mode = ($mode == 1) ? 0 : 1;

        $data = array(
            'mode' => $switch_mode
        );
        $this->db->update('agency_site_maintenance_mode', $data);
        $this->db->limit(1);
    }

    public function view_admin_docs() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Admin Documents";
        $delete_doc = $this->input->get('delete');
        $delete_file = $this->input->get('file');

        if ($delete_file !== null) {
            if (strpos($delete_file, 'uploads/admin_documents') !== false) {
                unlink(FCPATH . $delete_file);
            } else {
                $this->session->set_flashdata([
                    'error_msg' => 'Unsuccessful deleting uploaded file',
                    'status' => 'error'
                ]);
                redirect(base_url('/admin/view_admin_docs'));
            }
        }
        if ($delete_doc !== null) {
            $this->admin_model->remove_internal_doc($delete_doc);
            $this->session->set_flashdata([
                'success_msg' => 'Document has been deleted',
                'status' => 'success'
            ]);
            redirect(base_url('/admin/view_admin_docs'));
        }

        $doc_headers = $this->admin_model->get_internal_doc_header($this->config->item('country'))->result_array();
        $admin_documents = array();
        foreach ($doc_headers as $headers) {
            $header_id = $headers['admin_doc_header_id'];
            $documents = $this->admin_model->get_internal_docs_by_header($header_id, $this->config->item('country'))->result_array();
            $admin_documents[$headers['admin_doc_header_id']] = ['name' => $headers['name'], 'docs' => $documents];
        }
        $data['admin_docs'] = $admin_documents;
        // base url
        $base_url = '/accounts/view_admin_docs/';

        // pagination
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['base_url'] = $base_url;

        $data = array_merge($data, $_POST, $_GET);
        $this->load->view('templates/inner_header', $data);
        $this->load->view('admin/view_admin_docs', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function add_internal_doc_action_form_submit() {
        $title = $this->input->post('title');
        $header = $this->input->post('header');
        $country_folder = "/" . strtolower($this->gherxlib->get_country_iso());
        $folder = "uploads/admin_documents{$country_folder}";
        if (!file_exists($folder)) {
            $create_dir = mkdir(FCPATH . $folder);
        }
        $file = pathinfo($_FILES["file"]['name']);
        $filename = $file['filename'] . "_" . date('Ymd') . "_" . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT) . "." . $file['extension'];
        $config['upload_path'] = $folder;
        $config['allowed_types'] = 'gif|jpg|png|pdf|docx';
        $config['max_size'] = 50000000;
        $config['file_name'] = $filename;
        $this->load->library('upload', $config);
        $uploadFile = $this->upload->do_upload('file');
       
        if ($uploadFile) {
            $upload_data = $this->upload->data();
            
            $params = [
                'admin_doc_header_id' => $header,
                'filename' => $upload_data['file_name'],
                'path' => $folder,
                'title' => $title,
                'date' => date("Y-m-d H:i:s")
            ];
            $this->admin_model->add_internal_doc($params);

            $this->session->set_flashdata([
                'success_msg' => 'Document has been added',
                'status' => 'success'
            ]);
            redirect(base_url('/admin/view_admin_docs'));
        } else {
            $upload_err_msg = strip_tags($this->upload->display_errors());
            $this->session->set_flashdata([
                'error_msg' => 'Unsuccessful.\n' . $upload_err_msg,
                'status' => 'error'
            ]);
            redirect(base_url('/admin/view_admin_docs'));
        }
    }

    public function add_internal_doc_link_action_form_submit() {
        $url = $this->input->post('url');
        $title = $this->input->post('title');
        $header = $this->input->post('header');
        $params = [
            'type' => 2,
            'admin_doc_header_id' => $header,
            'url' => $url,
            'title' => $title,
            'date' => date("Y-m-d H:i:s")
        ];
        $insert_id = $this->admin_model->add_internal_doc($params);

        if ($insert_id) {
            $this->session->set_flashdata([
                'success_msg' => 'Document has been added',
                'status' => 'success'
            ]);
            redirect(base_url('/admin/view_admin_docs'));
        } else {
            $this->session->set_flashdata([
                'error_msg' => 'Unsuccessful',
                'status' => 'error'
            ]);
            redirect(base_url('/admin/view_admin_docs'));
        }
    }

    public function edit_internal_doc_header_action_form_submit() {
        $delete = $this->input->post('delete');
        if ((int) $delete > 0) {
            //delete
            $is_deleted = $this->admin_model->remove_internal_doc_header((int) $delete);
            if (!$is_deleted) {
                $this->session->set_flashdata([
                    'error_msg' => 'Cannot Delete Header',
                    'status' => 'error'
                ]);
                redirect(base_url('/admin/view_admin_docs'));
            }
            $this->session->set_flashdata([
                'success_msg' => 'Headers has been successfuly deleted',
                'status' => 'success'
            ]);
            redirect(base_url('/admin/view_admin_docs'));
        }
        $names = $this->input->post('edit_name');
//        var_dump($names);die();
        foreach ($names as $header_id => $name) {
            $is_updated = $this->admin_model->edit_internal_doc_header(
                    ['name' => $name], $header_id, $this->config->item('country')
            );
            if (!$is_updated) {
                $this->session->set_flashdata([
                    'error_msg' => 'Cannot update Header ' . $name,
                    'status' => 'error'
                ]);
                redirect(base_url('/admin/view_admin_docs'));
            }
            $this->session->set_flashdata([
                'success_msg' => 'Headers has been updated',
                'status' => 'success'
            ]);
            redirect(base_url('/admin/view_admin_docs'));
        }
    }

    public function add_internal_doc_header_action_form_submit() {
        $name = $this->input->post('header_name');
        $is_added = $this->admin_model->add_internal_doc_header([
            'name' => $name,
            'country_id' => $this->config->item('country')
        ]);
        if (!$is_added) {
            $this->session->set_flashdata([
                'error_msg' => 'Cannot Add Header ' . $name,
                'status' => 'error'
            ]);
            redirect(base_url('/admin/view_admin_docs'));
        }
        $this->session->set_flashdata([
            'success_msg' => 'Headers has been added',
            'status' => 'success'
        ]);
        redirect(base_url('/admin/view_admin_docs'));
    }


    public function renewals() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Renewals";
        $country_id = $this->config->item('country');
        $uri = '/admin/renewals';
        $data['uri'] = $uri;

        $from = ( $this->input->get_post('from') != '' )?$this->input->get_post('from'):date('01/m/Y');
        $data['from'] = $from;
        $to = ( $this->input->get_post('to') != '' )?$this->input->get_post('to'):date('t/m/Y');    
        $data['to'] = $to; 

        $date_filter_str = null;

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = ($this->input->get_post('offset')!="")?$this->input->get_post('offset'):0;
       

        if( $from != '' && $to != '' ){

            $from_formated = ( $from !='' )?$this->system_model->formatDate($from):NULL;
            $to_formated = ( $to !='' )?$this->system_model->formatDate($to):NULL;

            $date_filter_str = "AND r.`date` BETWEEN '{$from_formated}' AND '{$to_formated}'";

        }
        
        // get paginated list
        $renewals_sql_str = "
            SELECT 
                r.`date`, 
                r.`num_jobs_created`,

                rt.`renewal_type_id`,
                rt.`name` AS rt_name
            FROM `renewals` AS r           
            LEFT JOIN `renewal_type` AS rt ON r.`renewal_type` =  rt.`renewal_type_id` 
            WHERE r.`country_id` = {$country_id}
            {$date_filter_str}            
            ORDER BY r.`date` DESC
            LIMIT {$offset}, {$per_page}
        ";
        $data['list'] = $this->db->query($renewals_sql_str);

        // get all
        $renewals_sql_str = "
            SELECT 
                COUNT(r.`renewals_id`) AS r_count,
                SUM(r.`num_jobs_created`) AS job_created_count
            FROM `renewals` AS r           
            LEFT JOIN `renewal_type` AS rt ON r.`renewal_type` =  rt.`renewal_type_id` 
            WHERE r.`country_id` = {$country_id}
            {$date_filter_str}
            ORDER BY r.`date` DESC
            LIMIT 10
        ";
        $renewals_sql = $this->db->query($renewals_sql_str);
        $renewals_row = $renewals_sql->row();
        $total_rows = $renewals_row->r_count;
        $data['job_created_count'] = $renewals_row->job_created_count;

        $pagi_links_params_arr = array(
            'from' => $from,
            'to' => $to
        );
        $pagi_link_params = '/admin/renewals/?'.http_build_query($pagi_links_params_arr);


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
        $this->load->view('admin/renewals', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function page_totals() {

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Page Totals";
        $country_id = $this->config->item('country');
        
        $page_total_sql = "
            SELECT 
                `page_total_id`,
                `page`,
                `total`
            FROM `page_total`
            WHERE `active` = 1
            ORDER BY `page` ASC            
        ";
        $data['page_total_sql'] = $this->db->query($page_total_sql);
       
        $this->load->view('templates/inner_header', $data);
        $this->load->view('admin/page_totals', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    // orig old crm filename: cron_send_no_show_sms_au.php
    public function update_page_totals(){

        // get crm settings
        $crm_sql = $this->system_model->getCrmSettings([
            "sel_str" => "disable_all_crons"
        ]);
        $crm_row = $crm_sql->row();

        if( $crm_row->disable_all_crons == 0 ){

            $country_id = $this->config->item('country');
            $today = date('Y-m-d');
    
            $this->cron_model->update_page_totals();              

        }     
        
        redirect('/admin/page_totals');

    }

    public function noticeboard(){

        //get noticeboard
        $noticeboard_sql = $this->db->select('*')->from('noticeboard')->where('country_id', $this->config->item('country'))->get();
        $data['noticeboard_row'] = $noticeboard_sql->row_array();

        //get agency statement
        $agency_statement_sql = $this->db->select('*')->from('crm_settings')->where('country_id', $this->config->item('country'))->get();
        $data['agency_statement_row'] = $agency_statement_sql->row_array();

        if($this->input->post('btn_modify_noticeboard')){
            $nb_id = $this->input->post('nb_id');
            $notice = $this->input->post('notice');

            if(!empty($nb_id)){ //Update

                $update_data = array(
                    'notice' => $notice
                );
                $this->db->where('id',$nb_id);
                $this->db->update('noticeboard',$update_data);
                $this->db->limit(1);

            }else{//Insert New

                $insert_data = array(
                    'notice' => $notice,
                    'date_updated' => date("Y-m-d H:i:s"),
                    'country_id' => $this->config->item('country')
                );
                $this->db->insert('noticeboard', $insert_data);
                $this->db->limit(1);

            }
            
            $success_message = "Noticeboard Successfully Updated";
            $this->session->set_flashdata(array('success_msg'=>$success_message,'status'=>'success'));
            redirect(base_url('/admin/noticeboard'));
        }

        $data['title'] = "Agency Noticeboard";
        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('admin/noticeboard', $data);
        $this->load->view('templates/inner_footer', $data);

    }

    public function change_statement_generic_note(){

        if($this->input->post('btn_modify_agency_statement')){
            $statement_generic_note = $this->input->post('statement_generic_note');
            $statement_generic_note_ts = date('Y-m-d H:i:s');

            $update_data = array(
                'statements_generic_note' => $statement_generic_note,
                'statements_generic_note_ts' => $statement_generic_note_ts
            );
            $this->db->where('country_id', $this->config->item('country'));
            $this->db->update('crm_settings', $update_data);
            $this->db->limit(1);
            
            $success_message = "Agency statement generic note successfully Updated";
            $this->session->set_flashdata(array('success_msg'=>$success_message,'status'=>'success'));
            redirect(base_url('/admin/noticeboard'));
        }
    }

    public function resources(){
        $this->load->model('properties_model');
        $data['uri'] = "/admin/resources";

        $data['agent_documents_path'] = "/uploads/agent_documents/";

        $data['get_resources_header'] = $this->admin_model->get_resources_header();

        $data['title'] = "Agent Documents";
        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('admin/resources', $data);
        $this->load->view('templates/inner_footer', $data);

    }

    public function ajax_update_resources(){
        
        $json_data['status'] = false;
        $resources_id = $this->input->post('resources_id');
        $title = $this->input->post('title');
        $heading = $this->input->post('heading');
        $state = $this->input->post('state');
        $due_date = $this->input->post('due_date');
        $due_date2 = ($this->system_model->isDateNotEmpty($due_date))?$this->system_model->formatDate($due_date):NULL;

        $state2 = implode(",",$state);

        if($resources_id && $resources_id!=""){
            $data = array(
                'title' => $title,
                'resources_header_id' => $heading,
                'states' => $state2,
                'date' => date("Y-m-d H:i:s"),
                'due_date' => $due_date2
            );
            $this->db->where('resources_id',$resources_id);
            $this->db->update('resources',$data);
            $this->db->limit(1);

            $json_data['status'] = true;
        }
        echo json_encode($json_data);

    }

    public function ajax_delete_resources(){

        $json_data['status'] = false;
        $resources_id = $this->input->post('resources_id');
        $type = $this->input->post('type');
        $del_path = $this->input->post('del_path');

        //Delete from DB
        if($resources_id && $resources_id!=""){
            $this->db->where('resources_id', $resources_id);
            $this->db->delete('resources');
            $this->db->limit(1);

            //Unlink/delete file
            if($type==1){
                if($del_path!=""){
                    // delete file
                    unlink($del_path);
                }
            }
              $json_data['status'] = true;
        }
        echo json_encode($json_data);
        
    }

    public function insert_new_resources(){

        $this->load->library('upload');

        $sel_type = $this->input->post('sel_type');
        $file = $this->input->post('file');
        $url = $this->input->post('url');
        $title = $this->input->post('title');
        $heading = $this->input->post('heading');
        $states = $this->input->post('states');
        $due_date = $this->input->post('due_date');
        $due_date2 = ($this->system_model->isDateNotEmpty($due_date))?$this->system_model->formatDate($due_date,'Y-m-d'):NULL;

        //Form validation
        if($sel_type==1){
            if (empty($_FILES['file']['name'])){
                $this->form_validation->set_rules('file', 'File', 'required');
            }
        }else if($sel_type==2){
            $this->form_validation->set_rules('url', 'Url', 'required');
        }
        $this->form_validation->set_rules('title', 'Title', 'required');
        $this->form_validation->set_rules('heading', 'Heading', 'required');	

        if ( $this->form_validation->run() == true ){
            //Upload and Insert File
            if($sel_type==1){

                 //Upload file
                $upload_path = "./uploads/agent_documents/";
                $upload_folder = "/uploads/agent_documents/"; //note without dot

                //make directory if not exist and set permission to 777
                if(!is_dir($upload_folder)){
                    mkdir($upload_path,0777,true);
                }

                $_FILES['file']['name'] = $_FILES['file']['name'];
                $_FILES['file']['type'] = $_FILES['file']['type'];
                $_FILES['file']['tmp_name'] = $_FILES['file']['tmp_name'];
                $_FILES['file']['error'] = $_FILES['file']['error'];
                $_FILES['file']['size'] = $_FILES['file']['size'];

                //set upload config
                $upload_params = array(
                    'file_name' => $_FILES['file']['name'],
                    'upload_path' => $upload_path,
                    'max_size' => '5000', //5mb
                    'allowed_types' => 'gif|jpg|jpeg|png|pdf|doc|xls'
                );
                $upload_file = $this->gherxlib->do_upload('file',$upload_params);   

                if($upload_file){ //Upload success insert data to database

                    $upload_data = $this->upload->data();

                    $insert_data = array(
                        'type' => 1,
                        'filename' => $upload_data['file_name'],
                        'path' => $upload_folder,
                        'title' => $title,
                        'date' => date("Y-m-d H:i:s"),
                        'resources_header_id' => $heading,
                        'due_date' => $due_date2,
                        'country_id' => $this->config->item('country')
                    );

                    //state optional > for AU only
                    if($this->admin_model->ifCountryHasState()){
                      $insert_data['states'] = implode(',',$states);
                    }

                    $this->admin_model->insert_resources($insert_data);

                    //Set flash success message
                    $success_message = "New document has been successfully added.";
                    $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
                    redirect(base_url('/admin/resources'), 'refresh');

                }else{
                    echo $this->upload->display_errors();
                }

            }else{ //Add/Insert normal link
                
                $insert_data = array(
                    'type' => 2,
                    'url' => $url,
                    'title' => $title,
                    'date' => date("Y-m-d H:i:s"),
                    'resources_header_id' => $heading,
                    'due_date' => $due_date2,
                    'country_id' => $this->config->item('country')
                );

                //state optional > for AU only
                if($this->admin_model->ifCountryHasState()){
                  $insert_data['states'] = implode(',',$states);
                }

                $this->admin_model->insert_resources($insert_data);

                //Set flash success message
                $success_message = "New document has been successfully added.";
                $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
                redirect(base_url('/admin/resources'), 'refresh');

            }

           
            
        }else{
            echo "Error: Please contact admin.";
        }
        
        $upload_path = "./uploads/request_summary_files/";
        $upload_folder = "/uploads/request_summary_files/"; //note without dot

        //make directory if not exist and set permission to 777
        if(!is_dir($upload_folder)){
            mkdir($upload_path,0777,true);
        }
        
    }

    public function ajax_delete_resources_header(){

        $json_data['status'] = false;
        $rh_id = $this->input->post('rh_id');

        if($rh_id && $rh_id!=""){
            $this->db->where('resources_header_id', $rh_id);
            $this->db->delete('resources_header');
            $this->db->limit(1);

            $json_data['status'] = true;
        }
        
        echo json_encode($json_data);

    }

    public function resources_update_header(){

        $rh_id = $this->input->post('rh_id');
        $edit_name = $this->input->post('edit_name');


        foreach($rh_id as $index => $val){
            if($val && $val!=""){
                $data = array(
                    'name' => $edit_name[$index]
                );
                $this->db->where('resources_header_id', $val);
                $this->db->update('resources_header', $data);
            }
        }

        //Set flash success message
        $success_message = "Update Successfull.";
        $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
        redirect(base_url('/admin/resources'), 'refresh');

    }

    public function alarm_guide(){

        $search = $this->input->get_post('search');

        // pagination
        $per_page = $this->config->item('pagi_per_page');
        $offset = $this->input->get_post('offset');

        $params = array(
            'search' => $search,
            'sort_list' => array(
                array(
                    'order_by' => 'make',
                    'sort' => 'asc'
                )
            ),
            'limit' => $per_page,
            'offset' => $offset,
            'display_query' => 0
        );
        $data['list'] = $this->admin_model->getSmokeAlarms($params);

        //Total rows
        $sel_query = "COUNT('smoke_alarm_id') AS jcount";
        $total_params = array(
            'sel_query' => $sel_query,
            'search' => $search
        );
        $query =  $this->admin_model->getSmokeAlarms($total_params);
        $total_rows = $query->row()->jcount;

        $pagi_links_params_arr = array(
            'search' => $search
        );
        $pagi_link_params = '/admin/alarm_guide/?'.http_build_query($pagi_links_params_arr);

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

        $data['title'] = "Alarm Guide";
        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('admin/alarm_guide', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function add_alarm(){

        $this->load->library('upload');

        if($this->input->post('btn_add_alarm')){

            $make  = $this->input->post('make');
            $model = $this->input->post('model');
            $power_type = $this->input->post('power_type');
            $detection_type = $this->input->post('detection_type');
            $expiry_manuf_date 	= $this->input->post('expiry_manuf_date');
            $loc_of_date = $this->input->post('loc_of_date');
            $remove_battery = $this->input->post('remove_battery');
            $hush_button = $this->input->post('hush_button');
            $common_faults  = $this->input->post('common_faults');
            $how_to_rem_al  = $this->input->post('how_to_rem_al');
            $adntl_notes = $this->input->post('adntl_notes');

            //Set validations
            $this->form_validation->set_rules('make', 'Make', 'required');
            $this->form_validation->set_rules('model', 'Model', 'required');
            $this->form_validation->set_rules('power_type', 'Power Type', 'required');
            $this->form_validation->set_rules('detection_type', 'Detection Type', 'required');
            $this->form_validation->set_rules('expiry_manuf_date', 'Expirty / Manufacture Date', 'required');
            $this->form_validation->set_rules('loc_of_date', 'Location of Date', 'required');
            $this->form_validation->set_rules('remove_battery', 'Removable Battery', 'required');
            $this->form_validation->set_rules('hush_button', 'Husth Button', 'required');
            $this->form_validation->set_rules('common_faults', 'Common Faults', 'required');
            $this->form_validation->set_rules('how_to_rem_al', 'How to Remove Alarm', 'required');
            $this->form_validation->set_rules('adntl_notes', 'Additional Notes', 'required');

            if (empty($_FILES['front_image']['name'])){
                $this->form_validation->set_rules('front_image', 'Front Image', 'required');
            }
            if (empty($_FILES['rear_image_1']['name'])){
                $this->form_validation->set_rules('rear_image_1', 'Rear Image 1', 'required');
            }
            if (empty($_FILES['rear_image_2']['name'])){
                $this->form_validation->set_rules('rear_image_2', 'Rear Image 2', 'required');
            }
            
            if ( $this->form_validation->run() == true ){

                //Insert new alarm
                $insertdata = array(
                    'make' => $make,
                    'model' => $model,
                    'power_type' => $power_type,
                    'detection_type' => $detection_type,
                    'expiry_manuf_date' => $expiry_manuf_date,
                    'loc_of_date' => $loc_of_date,
                    'remove_battery' => $remove_battery,
                    'hush_button' => $hush_button,
                    'common_faults' => $common_faults,
                    'how_to_rem_al' => $how_to_rem_al,
                    'adntl_notes' => $adntl_notes,
                    'country_id' => $this->config->item('country')
                );
                $sa_id = $this->admin_model->insert_new_alarm($insertdata);

                //Upload files
                $upload_path = "./images/smoke_alarms/";
                $upload_folder = "/images/smoke_alarms/"; //note without dot

                //make directory if not exist and set permission to 777
                if(!is_dir($upload_folder)){
                    mkdir($upload_path,0777,true);
                }

                //Upload front image
                $front_image_path = $_FILES['front_image']['name'];
                $front_image_xt = pathinfo($front_image_path, PATHINFO_EXTENSION);
                $upload_front_image_name = "img_{$sa_id}_" . rand() . "_" . date("YmdHis");
                $upload_params = array(
                    'file_name' => $upload_front_image_name,
                    'upload_path' => $upload_path,
                    'max_size' => '760', 
                    'allowed_types' => 'gif|jpg|jpeg|png'
                );
                $upload_front_image = $this->gherxlib->do_upload('front_image',$upload_params); 

                if($upload_front_image){
                    // store image path
                    $update_data = array('front_image'=>$upload_front_image_name.".".$front_image_xt);
                    $this->admin_model->update_alarm_image_path($sa_id,$update_data);
                }
                //Upload front image end

                //Upload rear image 1
                $rear_image_1_image_path = $_FILES['rear_image_1']['name'];
                $rear_image_1_xt = pathinfo($rear_image_1_image_path, PATHINFO_EXTENSION);
                $upload_rear_image1_name = "img_{$sa_id}_" . rand() . "_" . date("YmdHis");
                $upload_params_rear1 = array(
                    'file_name' => $upload_rear_image1_name,
                    'upload_path' => $upload_path,
                    'max_size' => '760', 
                    'allowed_types' => 'gif|jpg|jpeg|png'
                );
                $upload_rear_image1 = $this->gherxlib->do_upload('rear_image_1',$upload_params_rear1);

                if($upload_rear_image1){
                    // store image path
                    $update_data_rear1 = array('rear_image_1'=>$upload_rear_image1_name.".".$rear_image_1_xt);
                    $this->admin_model->update_alarm_image_path($sa_id,$update_data_rear1);
                }
                //Upload rear image 1 end

                //Upload rear image 2
                $rear_image_2_image_path = $_FILES['rear_image_2']['name'];
                $rear_image_2_xt = pathinfo($rear_image_2_image_path, PATHINFO_EXTENSION);
                $upload_rear_image2_name = "img_{$sa_id}_" . rand() . "_" . date("YmdHis");
                $upload_params_rear2 = array(
                    'file_name' => $upload_rear_image2_name,
                    'upload_path' => $upload_path,
                    'max_size' => '760', 
                    'allowed_types' => 'gif|jpg|jpeg|png'
                );
                $upload_rear_image2 = $this->gherxlib->do_upload('rear_image_2',$upload_params_rear2);

                if($upload_rear_image2){
                    // store image path
                    $update_data_rear2 = array('rear_image_2'=>$upload_rear_image2_name.".".$rear_image_2_xt);
                    $this->admin_model->update_alarm_image_path($sa_id,$update_data_rear2);
                }
                //Upload rear image 2 end
                
                //redirect and set session
                $success_message = "New Smoke Alarms Successfully Added";
                $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
                redirect(base_url('/admin/add_alarm'), 'refresh');

            }else{
                echo validation_errors();
                exit();
            }

        }

        $data['title'] = "Add Smoke Alarm";
        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('admin/add_alarm', $data);
        $this->load->view('templates/inner_footer', $data);

    }

    public function alarm_detail(){
        $id = $this->uri->segment(3);
        
        if(!$id && !is_numeric($id)){
            redirect('/admin/alarm_guide');
        }

        //Get Smoke Alarm by id
        $params = array(
            'smoke_alarm_id' => $id
        );
        $data['sa'] = $this->admin_model->getSmokeAlarms($params)->row_array();

        //Update post submit
        if($this->input->post('btn_update_alarm')){

            $make  = $this->input->post('make');
            $model = $this->input->post('model');
            $power_type = $this->input->post('power_type');
            $detection_type = $this->input->post('detection_type');
            $expiry_manuf_date 	= $this->input->post('expiry_manuf_date');
            $loc_of_date = $this->input->post('loc_of_date');
            $remove_battery = $this->input->post('remove_battery');
            $hush_button = $this->input->post('hush_button');
            $common_faults  = $this->input->post('common_faults');
            $how_to_rem_al  = $this->input->post('how_to_rem_al');
            $adntl_notes = $this->input->post('adntl_notes');

            $front_image_old_path = $this->input->post('front_image_old_path');
            $rear_image_1_old_path = $this->input->post('rear_image_1_old_path');
            $rear_image_2_old_path = $this->input->post('rear_image_2_old_path');

            //Set validations
            $this->form_validation->set_rules('make', 'Make', 'required');
            $this->form_validation->set_rules('model', 'Model', 'required');
            $this->form_validation->set_rules('power_type', 'Power Type', 'required');
            $this->form_validation->set_rules('detection_type', 'Detection Type', 'required');
            $this->form_validation->set_rules('expiry_manuf_date', 'Expirty / Manufacture Date', 'required');
            $this->form_validation->set_rules('loc_of_date', 'Location of Date', 'required');
            $this->form_validation->set_rules('remove_battery', 'Removable Battery', 'required');
            $this->form_validation->set_rules('hush_button', 'Husth Button', 'required');
            $this->form_validation->set_rules('common_faults', 'Common Faults', 'required');
            $this->form_validation->set_rules('how_to_rem_al', 'How to Remove Alarm', 'required');
            $this->form_validation->set_rules('adntl_notes', 'Additional Notes', 'required');

            if ( $this->form_validation->run() == true ){

                $udpate_data = array(
                    'make' => $make,
                    'model' => $model,
                    'power_type' => $power_type,
                    'detection_type' => $detection_type,
                    'expiry_manuf_date' => $expiry_manuf_date,
                    'loc_of_date' => $loc_of_date,
                    'remove_battery' => $remove_battery,
                    'hush_button' => $hush_button,
                    'common_faults' => $common_faults,
                    'how_to_rem_al' => $how_to_rem_al,
                    'adntl_notes' => $adntl_notes
                );
                $this->admin_model->update_smoke_alarms($id, $udpate_data);

                //Upload path
                $upload_path = "./images/smoke_alarms/";
                $upload_folder = "/images/smoke_alarms/"; //note without dot

                //make directory if not exist and set permission to 777
                if(!is_dir($upload_folder)){
                    mkdir($upload_path,0777,true);
                }

                //Front Image
                if (!empty($_FILES['front_image']['name'])){
                    //delete old file
                    $this->admin_model->deleteFile($front_image_old_path);

                    //upload new front image
                    $front_image_path = $_FILES['front_image']['name'];
                    $front_image_xt = pathinfo($front_image_path, PATHINFO_EXTENSION);
                    $upload_front_image_name = "img_{$id}_" . rand() . "_" . date("YmdHis");
                    $upload_params = array(
                        'file_name' => $upload_front_image_name,
                        'upload_path' => $upload_path,
                        'max_size' => '760', 
                        'allowed_types' => 'gif|jpg|jpeg|png'
                    );
                    $upload_front_image = $this->gherxlib->do_upload('front_image',$upload_params); 
    
                    if($upload_front_image){
                        // store image path
                        $update_data = array('front_image'=>$upload_front_image_name.".".$front_image_xt);
                        $this->admin_model->update_alarm_image_path($id,$update_data);
                    }
                }
                //Front Image End

                //Rear Image 1
                if (!empty($_FILES['rear_image_1']['name'])){
                    //delete old file
                    $this->admin_model->deleteFile($rear_image_1_old_path);

                    //upload new rear image 2
                    $rear_image_1_image_path = $_FILES['rear_image_1']['name'];
                    $rear_image_1_xt = pathinfo($rear_image_1_image_path, PATHINFO_EXTENSION);
                    $upload_rear_image1_name = "img_{$id}_" . rand() . "_" . date("YmdHis");
                    $upload_params_rear1 = array(
                        'file_name' => $upload_rear_image1_name,
                        'upload_path' => $upload_path,
                        'max_size' => '760', 
                        'allowed_types' => 'gif|jpg|jpeg|png'
                    );
                    $upload_rear_image1 = $this->gherxlib->do_upload('rear_image_1',$upload_params_rear1);
    
                    if($upload_rear_image1){
                        // store image path
                        $update_data_rear1 = array('rear_image_1'=>$upload_rear_image1_name.".".$rear_image_1_xt);
                        $this->admin_model->update_alarm_image_path($id,$update_data_rear1);
                    }
                }
                //Rear Image 1 End

                //Rear Image 2
                if (!empty($_FILES['rear_image_2']['name'])){
                     //delete old file
                     $this->admin_model->deleteFile($rear_image_2_old_path);

                     //upload new rear image 2
                     $rear_image_2_image_path = $_FILES['rear_image_2']['name'];
                     $rear_image_2_xt = pathinfo($rear_image_2_image_path, PATHINFO_EXTENSION);
                     $upload_rear_image2_name = "img_{$id}_" . rand() . "_" . date("YmdHis");
                     $upload_params_rear2 = array(
                         'file_name' => $upload_rear_image2_name,
                         'upload_path' => $upload_path,
                         'max_size' => '760', 
                         'allowed_types' => 'gif|jpg|jpeg|png'
                     );
                     $upload_rear_image2 = $this->gherxlib->do_upload('rear_image_2',$upload_params_rear2);
     
                     if($upload_rear_image2){
                         // store image path
                         $update_data_rear2 = array('rear_image_2'=>$upload_rear_image2_name.".".$rear_image_2_xt);
                         $this->admin_model->update_alarm_image_path($id,$update_data_rear2);
                     }

                }
                //Rear Image 2 End

                //redirect and set session
                $success_message = "Smoke Alarms Successfully Updated";
                $this->session->set_flashdata(array('success_msg' => $success_message, 'status' => 'success'));
                redirect(base_url("/admin/alarm_detail/{$id}"), 'refresh');

            }else{
                echo validation_errors();
                exit();
            }
        }

        $data['title'] = "Smoke Alarm Details";
        //load views
        $this->load->view('templates/inner_header', $data);
        $this->load->view('admin/alarm_detail', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function ajax_delete_smoke_alarm(){
        $json_data['status'] = false;
        $sa_id = $this->input->post('sa_id');

        if($sa_id!="" && is_numeric($sa_id)){
            $this->db->where('smoke_alarm_id', $sa_id);
            $this->db->where('country_id', $this->config->item('country'));
            $this->db->delete('smoke_alarms');
            $this->db->limit(1);
            $json_data['status'] = true;
        }
        echo json_encode($json_data);
    }

    //Countries - Chops
    public function countries(){
      //title
      $data['title'] = "Countries";

      //data from model
      $data['countries'] = $this->admin_model->getCountries();

      //load views
      $this->load->view('templates/inner_header', $data);
      $this->load->view('admin/countries_view', $data);
      $this->load->view('templates/inner_footer', $data);

    }//endfct

    //Countries Details - Chops
    public function country_details(){
      //title
      $data['title'] = "Country Details";

      $id = $this->uri->segment(3);
      $data['details'] = $this->admin_model->getCountryById($id);

      //load views
      $this->load->view('templates/inner_header', $data);
      $this->load->view('admin/country_details_view', $data);
      $this->load->view('templates/inner_footer', $data);

    }//endfct

    //Update Countriy By ID - Chops
    public function updateCountry(){
      $country_id = $this->input->post('country_id');

      $name = $this->input->post('name');
      $iso  = $this->input->post('iso');
      $agent_number  = $this->input->post('agent_number');
      $tenant_number = $this->input->post('tenant_number');
      $trading_name    = $this->input->post('trading_name');
      $company_address = $this->input->post('company_address');
      $outgoing_email  = $this->input->post('outgoing_email');
      $bank    = $this->input->post('bank');
      $abn     = $this->input->post('abn');
      $bsb     = $this->input->post('bsb');
      $web     = $this->input->post('web');
      $ac_name   = $this->input->post('ac_name');
      $ac_number = $this->input->post('ac_number');
      $facebook  = $this->input->post('facebook');
      $twitter   = $this->input->post('twitter');
      $instagram = $this->input->post('instagram');

      //Save to ARRAY
      $update_data = [
            'country' => $name,
            'iso'     => $iso,
            'agent_number'  => $agent_number,
            'tenant_number' => $tenant_number,
            'trading_name'    => $trading_name,
            'company_address' => $company_address,
            'outgoing_email'  => $outgoing_email,
            'bank' => $bank,
            'abn'  => $abn,
            'bsb'  => $bsb,
            'web'  => $web,
            'ac_name'   => $ac_name,
            'ac_number' => $ac_number,
            'facebook'  => $facebook,
            'twitter'   => $twitter,
            'instagram' => $instagram
        ];

        //Send data to Model
        $updateCountry = $this->admin_model->updateCountry($country_id, $update_data);

        //Response data from Model
        if ($updateCountry) {
            $this->session->set_flashdata(array('success_msg' => "Country details saved", 'status' => 'success'));
            redirect(base_url("/admin/country_details/{$country_id}"));
        }
    }//endfct

    //Add Regions - Chops
    public function add_region(){
        $data['title'] = "Add Region";

        $country_id = $this->config->item('country');
        $data['country_id'] = $country_id;

        $data['state'] = $this->admin_model->getAllStates($country_id);

        if(!empty($_POST)){
            //print_r($_POST);
            $region_name  = $this->input->post('name');
            $region_state = $this->input->post('state');

            $_SESSION['region_name']  = $region_name;
            $_SESSION['region_state'] = $region_state;

            $data['rduplicate'] = $this->admin_model->searchDuplicateRegion($region_name);

            if(empty($data['rduplicate'])){
                $params = array(
                    'region_name'  => $region_name,
                    'region_state' => $region_state,
                    'country_id'   => $country_id,
                    'status'       => 1
                );
                $status = $this->admin_model->addNewRegion($params);
                $_SESSION['region_name']  = [];
                $_SESSION['region_state'] = [];
            }

            //Response data from Model
            if ($status) {
                $this->session->set_flashdata(array('success_msg' => "Region details saved", 'status' => 'success'));
                redirect(base_url("/admin/view_regions"));
            }
            else{
                $this->session->set_flashdata(array('error_msg' => "Duplicate Region Name. Try again!", 'status' => 'error'));
                redirect(base_url("/admin/add_region"));
            }
        }
        else{
            $this->load->view('templates/inner_header', $data);
            $this->load->view('admin/add_region_view', $data);
            $this->load->view('templates/inner_footer', $data);
        }
    }//endfct

    //Add Regions - Chops
    public function add_subregion(){
        $data['title'] = "Add Sub Region";

        $error = $this->uri->segment(3);
        if(empty($error)){
            $_SESSION['region_id'] = [];
            $_SESSION['subregion_name'] = [];
            $_SESSION['dup_postcodes'] = [];
        }

        if(!empty($_POST)){

            $esubregion = $this->input->post('esubregion');
            $eregion_id = $this->input->post('eregion_id');
            
            if(!empty($esubregion)){
                
                $region_name = $this->input->post('eregion');

                $data['eduplicate'] = $this->admin_model->searchDuplicateSubregion($esubregion);
                //echo $this->db->last_query();
                //exit();

                if(empty($data['eduplicate'])){

                    $subregion_data = array(
                        'region_id'       => $eregion_id,
                        'subregion_name'  => $esubregion,
                        'active'         => 1
                    );
                    //print_r($subregion_data);
                    //exit();

                    $status = $this->admin_model->addNewSubRegion($subregion_data);
                    if ($status) {
                        $this->session->set_flashdata(array('success_msg' => "Subregion details saved", 'status' => 'success'));
                        redirect(base_url("/admin/view_regions"));
                    }
                }
                else{
                    $this->session->set_flashdata(array('error_msg' => "Duplicate Subregion. Try again!", 'status' => 'error'));
                    redirect(base_url("/admin/edit_region/{$eregion_id}"));
                }
            }

            $region_id      = $this->input->post('region');
            $subregion_name = $this->input->post('subregion');

            $tmp_postcodes   = str_replace('&nbsp;', '',$this->input->post('postcode'));
            $postcodes       = (explode(",",trim($tmp_postcodes)));
            $cntrpostcodes    = count($postcodes);

            $_SESSION['region_id'] = $region_id;
            $_SESSION['subregion_name'] = $subregion_name;

            $data['sduplicate'] = $this->admin_model->searchDuplicateSubregion($subregion_name);

            if(empty($data['sduplicate'])){
                $subregion_data = array(
                    'region_id'       => $region_id,
                    'subregion_name'  => $subregion_name,
                    'active'         => 1
                );
                $status = $this->admin_model->addNewSubRegion($subregion_data);
            }else{
                $status = 1;
            }

            $data['subregion_data'] = $this->admin_model->getSubregionidBySubregionname($subregion_name);
            $sub_region_id = $data['subregion_data'][0]->sub_region_id;

            $dup_postcode = array();
            $invalid_data = array();
            $dup_status = 0;
            $status = 0;
            $invalid = 0;
            $j = 0;
            $k =0;

            for ($y = 0; $y < $cntrpostcodes; $y++) {
                $new_postcode = trim($postcodes[$y]);

                $data['pduplicate'] = $this->admin_model->searchDuplicatePostcode($new_postcode);
                $dpostcode = $data['pduplicate'][0]->postcode;

                $cntr = strlen($new_postcode);

                if(!is_numeric($new_postcode) || $cntr != 4){
                    $invalid_data[$k++] = $new_postcode;
                    $invalid = 1;
                }

                else {
                    if(!empty($dpostcode)){
                        $dup_postcode[$j++] = $dpostcode;
                        $dup_status = 1;
                    }
        
                    else{
                        $postcode_data = array(
                            'sub_region_id'   => $sub_region_id,
                            'postcode'        => $new_postcode,
                            'deleted'         => 0
                        );
                        $status = $this->admin_model->addNewPostcode($postcode_data);
                    }
                }
            }

            //Response data from Model
            if ($status > 1 && $dup_status == 0) {
                $this->session->set_flashdata(array('success_msg' => "Sub Region details saved", 'status' => 'success'));
                $_SESSION['region_id'] = [];
                $_SESSION['subregion_name'] = [];
                $_SESSION['postcodes'] = [];
                redirect(base_url("/admin/view_regions"));
            }
            if ($status > 1 && $dup_status == 1){
                $this->session->set_flashdata(array('error_msg' => "Duplicate Postcode. Try again!", 'status' => 'error'));
                $_SESSION['dup_postcodes'] = $dup_postcode;
                redirect(base_url("/admin/add_subregion/{$sub_region_id}"));
            }
            if ($status == 0 && $dup_status == 1){
                $this->session->set_flashdata(array('error_msg' => "Duplicate Postcode. Try again!", 'status' => 'error'));
                $_SESSION['dup_postcodes'] = $dup_postcode;
                redirect(base_url("/admin/add_subregion/{$sub_region_id}"));
            }
            if ($invalid == 1){
                $this->session->set_flashdata(array('error_msg' => "Invalid Postcode. Try again!", 'status' => 'error'));
                $_SESSION['dup_postcodes'] = [];
                redirect(base_url("/admin/add_subregion/{$sub_region_id}"));
            }
        }
        else{
            $country_id = $this->config->item('country');
            $data['regions'] = $this->admin_model->getAllRegions($country_id);

            $this->load->view('templates/inner_header', $data);
            $this->load->view('admin/add_subregion_view', $data);
            $this->load->view('templates/inner_footer', $data);
        }
    }//endfct

    //DELETE Regions - Chops
    public function delete_region(){

        $region_id = $this->uri->segment(3);

        //Send data to Model
        $deleted = $this->admin_model->deleteRegionById($region_id);

        //Response data from Model
        if ($deleted) {
            $this->session->set_flashdata(array('success_msg' => "Region successfully deleted", 'status' => 'success'));
            redirect(base_url("/admin/view_regions/"));
        }

    }//endfct

    //DELETE Subregions - Chops
    public function delete_subregion(){
        $subregion_id = $this->uri->segment(3);

        //echo $subregion_id;
        //exit();

        //Send data to Model
        $deleted = $this->admin_model->deleteSubregionById($subregion_id);
        //echo "Status".$deleted;
        //exit();
        //Response data from Model
        if ($deleted) {
            $this->session->set_flashdata(array('success_msg' => "Subregion successfully deleted", 'status' => 'success'));
            redirect(base_url("/admin/view_regions/"));
        }

    }//endfct

    //View Regions - Chops
    public function view_regions(){
        $data['title'] = "Booking Regions";
        
        // pagination 
        $config = array();

        $per_page = 5;
        $offset = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

        $country_id = $this->config->item('country');
        $data['country_id'] = $country_id;

        $total_rows = $this->admin_model->countDistinctState($country_id);

        $config["base_url"]    = base_url() . "admin/view_regions";
        $config["total_rows"]  = $total_rows;
        $config["per_page"]    = 4;
        $config["uri_segment"] = 3;

        $this->pagination->initialize($config);
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        $data["links"] = $this->pagination->create_links();

        $data['state']   = $this->admin_model->getAllStateByDistinct($config["per_page"], $page, $country_id);

        // pagination count
        $pc_params = array(
            'total_rows' => $total_rows,
            'offset'     => $offset,
            'per_page'   => $per_page
        );
        
        $data['pagi_count'] = $this->jcclass->pagination_count($pc_params);

        $this->load->view('templates/inner_header', $data);
        $this->load->view('admin/regions_view', $data);
        $this->load->view('templates/inner_footer', $data);
    }//endfct
    
    //Edit Region By ID - Chops
    public function edit_region(){
        $data['title'] = "Edit Region";

        $country_id = $this->config->item('country');
        $data['country_id'] = $country_id;
   
        $region_id       = $this->uri->segment(3);
        $data['regions'] = $this->admin_model->getRegionsById($region_id);
        $data['state']   = $this->admin_model->getAllStates($country_id);
        
        $this->load->view('templates/inner_header', $data);
        $this->load->view('admin/region_details_view', $data);
        $this->load->view('templates/inner_footer', $data);
    }//endfct

    //Edit Subregion By ID - Chops
    public function edit_subregion(){
        $data['title'] = "Edit Booking Regions";

        $error = $this->uri->segment(4);
        if(!empty($error)){
            $_SESSION['postcodes'] = [];
            $_SESSION['subregion_id'] = [];
            $region_id = $this->uri->segment(3);
            $subregion_id = $this->uri->segment(4);
        }

        $subregion_id     = $this->uri->segment(4);
        if(empty($subregion_id)){
            $subregion_id = $_SESSION['subregion_id'];
        }

        $data['postcodes']   = $this->admin_model->getPostcodesBySubregion($subregion_id);

        $country_id = $this->config->item('country');
        $data['subregion_id_selected'] = $subregion_id;
        $data['region_id']        = $region_id;
        $data['regions']          = $this->admin_model->getAllRegions($country_id);
        $data['subregion_name']   = $this->admin_model->getAllSubregions();

        $this->load->view('templates/inner_header', $data);
        $this->load->view('admin/subregion_details_view', $data);
        $this->load->view('templates/inner_footer', $data);
    }//endfct

    //Edit Subregion By ID - Chops
    public function edit_subregionname(){
        
        $subregion_name = $this->input->post('esubregion');
        $subregion_id   = $this->input->post('eid');
        $segment3 = $this->input->post('segment3');
        $segment4 = $this->input->post('segment4');
        
        //Save to ARRAY
        $update_data = [
            'subregion_name'   => $subregion_name
        ];

        //edit_subregion/53/366
        //Send data to Model
        $updatesubregionname = $this->admin_model->updateSubRegionByName($subregion_id, $update_data);
        //echo $this->db->last_query();
        //exit();

        //Response data from Model
        if ($updatesubregionname) {
            $this->session->set_flashdata(array('success_msg' => "Subregion details saved", 'status' => 'success'));
            redirect(base_url("/admin/edit_subregion/{$segment3}/{$segment4}"));
        }
    }//endfct

    //UPDATE Region By ID - Chops
    public function update_region(){
        $region_id = $this->input->post('region_id');

        $region_name = $this->input->post('name');
        $state       = $this->input->post('state');

        //Save to ARRAY
        $update_data = [
            'region_name'   => $region_name,
            'region_state'  => $state
        ];

        //Send data to Model
        $updateRegion = $this->admin_model->updateRegionById($region_id, $update_data);

        //Response data from Model
        if ($updateRegion) {
            $this->session->set_flashdata(array('success_msg' => "Region details saved", 'status' => 'success'));
            redirect(base_url("/admin/view_regions"));
        }

    }//endfct

    //UPDATE Subregion By ID - Chops
    public function update_subregion(){
        $tmp_postcodes   = str_replace('&nbsp;', '',$this->input->post('postcode'));
        $postcodes       = (explode(",",trim($tmp_postcodes)));

        
        $cntrpostcodes    = count($postcodes);
        $cntrduppostcodes = count(array_values(array_unique($postcodes, SORT_REGULAR)));

        $region_id    = $this->input->post('region');
        $subregion_id    = $this->input->post('subregion_id');


        $_SESSION['subregion_id'] = $subregion_id;
        $_SESSION['postcodes'] = $postcodes;
        
        $validator = 0;
        $duplicate = 0;
        
        if($cntrduppostcodes < $cntrpostcodes){
            $i = $cntrpostcodes;
            $duplicate = 1;
        }

         //Save to ARRAY
         $update_data = [
            'region_id' => $region_id
        ];
        $updatesubregionregion = $this->admin_model->updateSubRegionByName($subregion_id, $update_data);

        for ($i = 0; $i < $cntrpostcodes; $i++) {
            $postcode = trim($postcodes[$i]);
            $cntr = strlen($postcode);
            if($cntr == 4){
                if(is_numeric($postcode)){
                    $data['pduplicate'] = $this->admin_model->searchDuplicatePostcode($postcode);

                    if(!empty($data['pduplicate'])){
                        $sub_id    = $data['pduplicate'][0]->sub_region_id;
                        if($sub_id != $subregion_id){
                            $duplicate = 1;
                            $i = $cntrpostcodes;
                        }
                    }
                    else{
                        $validator = 0;
                    }
                }
                else{
                    $validator = 1;
                    $i = $cntrpostcodes;
                }
            }
            else{
                $validator = 1;
                $i = $cntrpostcodes;
            }
        }

        if($validator == 0 && $duplicate == 0){
            $this->admin_model->deletePostcodeBySubregionName($subregion_id);

            for ($y = 0; $y < $cntrpostcodes; $y++) {
                $new_postcode = trim($postcodes[$y]);
                $params = array(
                    'postcode'          => $new_postcode,
                    'sub_region_id'     => $subregion_id,
                    'deleted'           => 0
                );
                $inserted = $this->admin_model->addNewPostcode($params);
            }
        }

        //Response data from Model

        if($duplicate > 0) {
            $this->session->set_flashdata(array('error_msg' => "Duplicate postcode. Try again!", 'status' => 'error'));
            redirect(base_url("/admin/edit_subregion/{$region_id}"));
        }
        if($inserted > 0) {
            $this->session->set_flashdata(array('success_msg' => "Subregion details saved", 'status' => 'success'));
            redirect(base_url("/admin/view_regions"));
        }
        if($validator > 0) {
            $this->session->set_flashdata(array('error_msg' => "Invalid postcode. Try again!", 'status' => 'error'));
            redirect(base_url("/admin/edit_subregion/{$region_id}"));
        }

    }//endfct

    //SEARCH Region from DB - Chops
    public function search_regions(){
        $data['title'] = "Search Regions";
        $region_flag = 0;
        $state_flag = 0;

        $country_id = $this->config->item('country');

        $keyword = $this->input->post('postcode');

        if(is_numeric($keyword)){
            $data['state'] = $this->admin_model->searchRegionsByPostcode($keyword);
            $data['postcode'] = $keyword;
        }
        else{
            if(empty($keyword)){
                $this->session->set_flashdata(array('error_msg' => "Search keyword is empty. Try again!", 'status' => 'error'));
                redirect(base_url("admin/view_regions"));
            }
            else{
                $data['filter_state'] = 1;
                $data['cstate']     = $this->admin_model->searchRegionCheckByState($keyword);

                if(!empty($data['cstate'])){
                    $state_flag = 1;
                    $state = $data['cstate'][0]->region_state;
                    $data['state']      = $this->admin_model->searchRegionsByState($state);
                }

                $data['cregion']    = $this->admin_model->searchRegionCheckByRegion($keyword,$country_id);

                if(!empty($data['cregion']) && $state_flag == 0){
                    $region_flag = 1;
                    $region = $data['cregion'][0]->region_name;
                    $data['regkeyword'] = $region;
                    $data['state']      = $this->admin_model->searchRegionsByRegion($region);
                }
                
                if($state_flag == 0 && $region_flag == 0){
                    $data['csubregion'] = $this->admin_model->searchRegionCheckBySubregion($keyword);

                    if(!empty($data['csubregion'])){
                        $subregion = $data['csubregion'][0]->subregion_name;
                        $data['subkeyword'] = $keyword;
                        $data['state']      = $this->admin_model->searchRegionsBySubregion($subregion);
                    }
                }
            }
            
        }

        $this->load->view('templates/inner_header', $data);
        $this->load->view('admin/regions_search_view', $data);
        $this->load->view('templates/inner_footer', $data);
    }//endfct

    //PASSWORDS - Chops
    public function passwords(){
        $data['title'] = "Passwords";

        $country_id = $this->config->item('country');
        $status = $this->input->get_post('status');

        $data['search_stats'] = $status;
        if($status){
            $data['accounts'] = $this->admin_model->getAllAccounts($country_id, $status);
        } else {
            $data['accounts'] = $this->admin_model->getAllAccounts($country_id, 1);
        }

        $data['export_link_params'] = "/admin/export_passwords";
        
        $this->load->view('templates/inner_header', $data);
        $this->load->view('admin/passwords_view', $data);
        $this->load->view('templates/inner_footer', $data);
    }//endfct

    //ADD passwords to DB - Chops
    public function add_passwords(){
        $data['title'] = "Add New Password";

        $country_id = $this->config->item('country');

        if(!empty($_POST)){
            $website   = $this->input->post('website');
            $email     = $this->input->post('email');
            $username  = $this->input->post('username');
            $password  = $this->input->post('password');
            $notes     = $this->input->post('notes');
            $date      = $this->input->post('expired_date');
            $newDate = date("Y-m-d",strtotime(str_replace("/","-",$date)));
            $upDate  = date("Y-m-d H:i:s");


            $params = array(
                'website'      => $website,
                'email'        => $email,
                'username'     => $username,
                'password'     => $password,
                'notes'        => $notes,
                'expiry_date'  => $newDate,
                'status'       => 1,
                'country_id'   => $country_id,
                'last_updated' =>$upDate
            );
            $status = $this->admin_model->addNewAccount($params);

            //Response data from Model
            if ($status) {
                $this->session->set_flashdata(array('success_msg' => "New account saved", 'status' => 'success'));
                redirect(base_url("/admin/passwords"));
            }
        }
        $this->load->view('templates/inner_header', $data);
        $this->load->view('admin/add_passwords_view', $data);
        $this->load->view('templates/inner_footer', $data);
    }//endfct

    //ADD passwords to DB - Chops
    public function edit_passwords(){
        $data['title'] = "Edit Password";

        $password_id = $this->uri->segment(3);
        $data['account'] = $this->admin_model->getAccountsById($password_id);

        $this->load->view('templates/inner_header', $data);
        $this->load->view('admin/edit_passwords_view', $data);
        $this->load->view('templates/inner_footer', $data);
    }//endfct

    //UPDATE Password By ID - Chops
    public function update_passwords(){
        $password_id = $this->input->post('password_id');

        $website        = $this->input->post('website');
        $email          = $this->input->post('email');
        $username       = $this->input->post('username');
        $password       = $this->input->post('password');
        $notes          = $this->input->post('notes');
        $status          = $this->input->post('status');
        $upDate  = date("Y-m-d H:i:s");

        $date = $this->input->post('expired_date');
        if($date){
            $expired_date = date("Y-m-d",strtotime(str_replace("/","-",$date)));
        } else {
            $expired_date = null;
        }

        //Save to ARRAY
        $update_data = [
            'website'       => $website,
            'email'         => $email,
            'username'      => $username,
            'password'      => $password,
            'notes'         => $notes,
            'status'         => $status,
            'expiry_date'   => $expired_date,
            'last_updated'   => $upDate
        ];

        //Send data to Model
        $updatePasswords = $this->admin_model->updatePasswordById($password_id, $update_data);

        //Response data from Model
        if ($updatePasswords) {
            $this->session->set_flashdata(array('success_msg' => "Account details updated", 'status' => 'success'));
            redirect(base_url("/admin/passwords"));
        }

    }//endfct

    //DELETE Password By ID - Chops
    public function delete_passwords(){
        $password_id = $this->uri->segment(3);

        //Send data to Model
        $deleted = $this->admin_model->deletePasswordById($password_id);

        //Response data from Model
        if ($deleted) {
            $this->session->set_flashdata(array('success_msg' => "Account successfully deleted", 'status' => 'success'));
            redirect(base_url("/admin/passwords/"));
        }

    }//endfct

    //EXPORT Passwords By ID - Chops
    public function export_passwords() 
    {
        $country_id = $this->config->item('country');
        $status = $this->input->get_post('status');
        $export = $this->input->get_post('export');

        try {
            if ($export == 1) {
                //File name 
                $filename = 'export_passwords' . date('Y-m-d H:i:s') . '.csv';
    
                header("Content-type: application/csv");
                header("Content-Disposition: attachment; filename={$filename}");
                header("Pragma: no-cache");
                header("Expires: 0");
    
                //File creation
                $file = fopen('php://output', 'w');
    
                //Header
                $header = array("Website", "Email", "Username", "Password", "Notes", "Expiry Date", "Last Updated");
                fputcsv($file, $header);
                
                $data = array();
                if($status != null){
                    $lists = $this->admin_model->getAllAccounts($country_id, $status);
                } else {
                    $lists = $this->admin_model->getAllAccounts($country_id, 1);
                }

                foreach ($lists as $k => $row) {
   
                   $data["website"]         = $row->website;
                   $data["email"]           = $row->email;
                   $data["username"]        = $row->username;
                   $data["password"]        = $row->password;
                   $data["notes"]           = $row->notes;
                   $data["expiry_date"]     = $row->expiry_date;
                   $data["last_updated"]    = $row->last_updated;
   
                   fputcsv($file, $data);
                }

               fclose($file);
               exit;
           }
            
        } catch(Exception $e) { //catch exception
            echo 'Message: ' .$e->getMessage();
        }

    }//endfct

    //SEARCH Emails from DB - Chops
    public function search_emails(){
        echo "TEST";
        exit();
    }//endfct


}//endclass

?>
