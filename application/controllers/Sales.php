<?php

class Sales extends CI_Controller {

    public function __construct() {
        parent::__construct();
//$this->load->database();
        $this->load->library('pagination');
        $this->load->helper('url');
        $this->load->model('sales_model');
        $this->load->model('system_model');
    }

    public function view_sales_documents() {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Sales Document";
        $docs = $this->sales_model->get_sales_document()->result_array();
        $states_def = $this->system_model->getStateViaCountry()->result_array();

        $data['docs'] = $docs;
        $data['states_def'] = $states_def;
        $this->load->view('templates/inner_header', $data);
        $this->load->view('sales/view_sales_document', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function add_sales_document_action_form_submit() {
        $title = $this->input->post('title');
        $state = join(",", $this->input->post('states'));
        $country_folder = "/" . strtolower($this->gherxlib->get_country_iso());
        $folder = "uploads/sales_document{$country_folder}";
        if (!file_exists($folder)) {
            $create_dir = mkdir(FCPATH . $folder);
        }
        $file = pathinfo($_FILES["file"]['name']);
        $filename = $file['filename'] . "_" . date('Ymd') . "_" . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT) . "." . $file['extension'];
        $config['upload_path'] = $folder;
        $config['max_size'] = 50000000;
        $config['file_name'] = $filename;
        $config['allowed_types'] = '*';
        $this->load->library('upload', $config);

        $uploadFile = $this->upload->do_upload('file');
        $params = [
            'filename' => $filename,
            'path' => $folder,
            'title' => $title,
            'country_id' => $this->config->item('country'),
            'date' => date("Y-m-d H:i:s")
        ];
        if(count($this->input->post('states'))) {
            $params['states'] = $state;
        }
        if ($uploadFile) {
            $this->sales_model->add_sales_document($params);
            $this->session->set_flashdata([
                'success_msg' => 'Document has been added',
                'status' => 'success'
            ]);
            redirect(base_url('/sales/view_sales_documents'));
        } else {
            $upload_err_msg = strip_tags($this->upload->display_errors());
            $this->session->set_flashdata([
                'error_msg' => 'Unsuccessful.\n' . $upload_err_msg,
                'status' => 'error'
            ]);
            redirect(base_url('/sales/view_sales_documents'));
        }
    }

    public function remove_sales_document_action_form_submit() {
        $id = $this->input->get_post('sales_document_id');
        $isDelete = $this->sales_model->remove_sales_document((int) $id);
        if ($isDelete) {
            $this->session->set_flashdata([
                'success_msg' => 'Document has been added',
                'status' => 'success'
            ]);
            redirect(base_url('/sales/view_sales_documents'));
        } else {
            $upload_err_msg = strip_tags($this->upload->display_errors());
            $this->session->set_flashdata([
                'error_msg' => 'Unsuccessful.\n' . $upload_err_msg,
                'status' => 'error'
            ]);
            redirect(base_url('/sales/view_sales_documents'));
        }
    }

    public function delete_sales_document_action_form_submit() {
        $id = $this->input->get_post('sales_document_id');
        $isDelete = $this->sales_model->remove_sales_document((int) $id);
        if ($isDelete) {
            $this->session->set_flashdata([
                'success_msg' => 'Document has been Delete',
                'status' => 'success'
            ]);
            redirect(base_url('/sales/view_sales_documents'));
        } else {
            $this->session->set_flashdata([
                'error_msg' => 'Unsuccessful',
                'status' => 'error'
            ]);
            redirect(base_url('/sales/view_sales_documents'));
        }
    }

}
