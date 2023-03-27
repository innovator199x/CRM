<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function __construct(){

        parent::__construct();

	}
	
	
	public function index()
	{
		$data['title'] = "Hello World";
		$this->load->view('templates/main_header', $data);
		$this->load->view('home/index', $data);		
		$this->load->view('templates/main_footer', $data);
	}

	public function test_email_template(){
		$data['title'] = "Sample Tempalte";
		//$this->load->view('emails/template/email_header.php', $data);
		$this->load->view('emails/sample', $data);
		//$this->load->view('emails/template/email_footer.php', $data);
	}

}
