<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model('staff_accounts_model');
    }

	public function index(){
		//unset($_SESSION['loginFailedCounter']);

		$crm_login = trim($this->input->get_post('crm_login'));

		$this->form_validation->set_rules('username', 'Email', 'required');
		if( $crm_login != 1 ){
			$this->form_validation->set_rules('password', 'Password', 'required');

			if($this->session->userdata('loginFailedCounter')==3){ //validate captcha if failed login == 3
				$this->form_validation->set_rules('g-recaptcha-response','reCaptcha','required|callback_validate_recaptcha');
			}

		}

		if ( $this->form_validation->run() == false ){

			$data['title'] = 'SATS CRM';
			$this->load->view('templates/main_header', $data);
			$this->load->view('login/index',$data);
			$this->load->view('templates/main_footer');

		}else{

			if( hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']) ) {
				
				// authenticate user
				$this->authenticate();
				
			}			

		}


	}

	public function authenticate(){

		// form input
		$username = trim(rawurldecode($this->input->get_post('username')));
		$password = trim(rawurldecode($this->input->get_post('password')));
		$expand_menu = trim(rawurldecode($this->input->get_post('expand_menu')));
		$page = trim(rawurldecode($this->input->get_post('page')));
		$page_params = trim(rawurldecode($this->input->get_post('page_params')));
		$country_id = $this->config->item('country');
		$staff_id = $this->input->get_post('staff_id');

		// get user data via username
		$params = array(
			'sel_query' => '
				sa.`StaffID`,
				sa.`ClassID`,
				sa.`Email`,
				sa.`password_new`
			',
			'joins' => array('country_access'),
			'email' => $username,
			'country_id' => $country_id,
			'staff_id' => $staff_id,
			'active' => 1,
			'deleted' => 0,
			'display_query' => 0
		);


		$skip_auth = ( $staff_id > 0 )?true:false;

		// get user details
		$user_account_sql = $this->staff_accounts_model->get_staff_accounts($params);


		if( $user_account_sql->num_rows() > 0 ){

			// user data
			$user_account = $user_account_sql->row();
			$password_hash = $user_account->password_new;
			$staff_id = $user_account->StaffID;
			$staff_class_id = $user_account->ClassID;

			// create session array
			$sess_arr = array(
				'staff_id' => $staff_id,
				'user_last_active' => time()
			);

			if( $skip_auth == false ){ // normal login

				// login from CRM site
				if ( password_verify($password, $password_hash) ) { // verify password

					// set session
					$this->session->set_userdata($sess_arr);

					// crm menu to be expanded
					//$this->session->set_flashdata('expand_menu', $expand_menu);

					$page_params2 = str_replace(':','=',$page_params); // replace : with =
					$page_params3 = str_replace('-','&',$page_params2); // replace - with &

					if( $staff_class_id == 6 ){ // tech
						$redirect_page = 'home/index';
					}else{

						//$redirect_page = ( isset($page) && $page != '' )?$page.( ( $this->input->get_post('page_params') != '' )?"/?{$page_params3}":null ):'bookings/view_schedule';
						$redirect_page = '/home';
					}

					// capture login
					$capture_login_sql_str = "
					INSERT INTO 
					`crm_user_logins`(
						`user`,
						`ip`,
						`date_created`
					)
					VALUES(
						{$staff_id},
						'{$_SERVER['REMOTE_ADDR']}',
						'".date('Y-m-d H:i:s')."'
					)
					";
					$this->db->query($capture_login_sql_str);

					redirect($redirect_page);


				}else{ // wrong password

					//$this->session->sess_destroy();
					$this->session->set_flashdata('password_incorrect', 1);
					redirect('/');

				}

			}else{ // skip login process, used on crm link to CI

				// set session
				$this->session->set_userdata($sess_arr);

				// crm menu to be expanded
				//$this->session->set_flashdata('expand_menu', $expand_menu);

				$page_params2 = str_replace(':','=',$page_params); // replace : with =
				$page_params3 = str_replace('-','&',$page_params2); // replace - with &

				//$redirect_page = ( isset($page) && $page != '' )?$page.( ( $this->input->get_post('page_params') != '' )?"/?{$page_params3}":null ):'bookings/view_schedule';
				$redirect_page = ( isset($page) && $page != '' )?$page.( ( $this->input->get_post('page_params') != '' )?"/?{$page_params3}":null ):'/home';
				redirect($redirect_page);

			}


		}else{

			// account doesnt exist
			//$this->session->sess_destroy();
			$this->session->set_flashdata('account_doesnt_exist', 1);
			redirect('/');

		}


	}


}

