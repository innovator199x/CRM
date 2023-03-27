<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use App\Exceptions\HttpException;
use Exception;

class TemplateHooks {

    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
    }

    public function preloadData() {
        if ($this->CI->session->staff_id) {
            $this->CI->templatedatahandler->preloadData();
        }
    }

}