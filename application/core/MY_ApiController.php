<?php

class MY_ApiController extends CI_Controller {
    public $allowedActions = [];

    //MY_APiController
    public function __construct() {
        parent::__construct();

        $this->output =& load_class('Output', 'core');
        $this->load->config('jwt');

        $this->load->library('Api');

        $this->load->helper('jwt');
        $this->load->helper('authorization');

        $this->output->set_content_type('application/json');

    }

    public function _exists($value, $field) {
        if (!$value) { return FALSE; }
        sscanf($field, '%[^.].%[^.]', $table, $field);

		return isset($this->db)
        ? ($this->db->limit(1)->get_where($table, array($field => $value))->num_rows() > 0)
        : FALSE;
    }
}