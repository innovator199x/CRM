<?php

class Menu extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->database();
        $this->load->model('staff_accounts_model');
        $this->load->library('pagination');
    }
	
    public function manager(){

        $data['start_load_time'] = microtime(true);
        $data['title'] = "Menu Manager";

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;


        $page_display = $this->input->get_post('page_display');
        if( is_numeric($page_display) && $page_display == 0 ){
            $status = $page_display;
        }else if( $page_display == 1 ){
            $status = $page_display;
        }else if( $page_display == -1 ){
            $status = null;
        }else if( $page_display == '' ){
            $status = 1; // default to active
        }

        // staff classes
        $sel_query = "ClassID, ClassName";
        $params = array(
            'sel_query' => $sel_query,
            'active' => 1,         
            
            'sort_list' => array(
                array(
                    'order_by' => 'sort_index',
                    'sort' => 'ASC'
                )
            ),
            
			'display_query' => 0
        );
        $staff_classes_sql = $this->system_model->getStaffClasses($params);
        $data['staff_classes_arr'] = $staff_classes_sql->result();

        // menu
        $sel_query = "menu_id, menu_name, active, icon_class_new";
        $params = array(
            'sel_query' => $sel_query,
            'active' => 1,  
            
            'sort_list' => array(
                array(
                    'order_by' => 'sort_index',
                    'sort' => 'ASC'
                )
            ),

			'display_query' => 0
        );
        $menu_sql = $this->menu_model->getMenu($params);   
        $data['menu_arr'] = $menu_sql->result(); 
        
        // get staff accounts     
        $sel_query = '
            sa.`StaffID`,
            sa.`FirstName`, 
            sa.`LastName`,
            sa.`active`
        ';   
		$params = array( 
			'sel_query' => $sel_query,
            
            'sort_list' => array(
                array(
                    'order_by' => 'sa.`FirstName`',
                    'sort' => 'ASC'
                ),
                array(
                    'order_by' => 'sa.`LastName`',
                    'sort' => 'ASC'
                )
            ),

            'deleted' => 0,
            'active' => 1,
			'display_query' => 0
		);
		
		// get user details
        $staff_accounts_sql = $this->staff_accounts_model->get_staff_accounts($params);
        $data['staff_accounts_arr'] = $staff_accounts_sql->result(); 
        
        // data
        $data['page_display'] = $page_display;
        $data['status'] = $status;

        $this->load->view('templates/inner_header', $data);
        $this->load->view('menu/manager', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function ajax_sort_menu(){
        
        $sort_menu = $this->input->get_post('sort_menu'); 
        $menu_id_arr = $this->input->get_post('sort_menu_tbl');
        if( $sort_menu == 1 ){
            $this->menu_model->sort_menu($menu_id_arr);
        }
        
    }

    // add menu
    public function add_menu(){

        $menu_name = $this->input->get_post('menu_name');

        // add menu
        $data = array(
            'menu_name' => $menu_name
        );
        
        $this->db->insert('menu', $data);
        $menu_id = $this->db->insert_id();

        // staff class
        $sel_query = "ClassID, ClassName";
        $params = array(
            'sel_query' => $sel_query,
            'active' => 1,         
            
            'sort_list' => array(
                array(
                    'order_by' => 'sort_index',
                    'sort' => 'ASC'
                )
            ),
            
			'display_query' => 0
        );

        $staff_classes_sql = $this->system_model->getStaffClasses($params);

        foreach( $staff_classes_sql->result() as $sc_row ){

            // add menu permission
            $data = array(
                'menu' => $menu_id,
                'staff_class' => $sc_row->ClassID
            );
            
            $this->db->insert('menu_permission_class', $data);

        }

        $this->session->set_flashdata('new_menu_success', 1);  
        redirect('/menu/manager');

    }


    // update menu
    public function update_menu(){

        $menu_id = $this->input->get_post('menu_id');
        $menu_name = $this->input->get_post('menu_name');
        $active = $this->input->get_post('active');
        $icon_class_new = $this->input->get_post('icon_class_new');

        if( $menu_id > 0 && $menu_name != '' &&  is_numeric($active) ){

            $data = array(
                'menu_name' => $menu_name,
                'active' => $active,
                'icon_class_new' => $icon_class_new
            );
            
            $this->db->where('menu_id', $menu_id);
            $this->db->update('menu', $data);

            $this->session->set_flashdata('update_menu_success', 1);  
            redirect('/menu/manager');

        }        

    }

    // add page
    public function add_page(){

        $page_name = $this->input->get_post('page_name');
        $page_url = $this->input->get_post('page_url');
        $menu = $this->input->get_post('menu');

        // add page
        $data = array(
            'page_name' => $page_name,
            'page_url' => $page_url,
            'menu' => $menu
        );
        
        $this->db->insert('crm_pages', $data);
        $page_id = $this->db->insert_id();

        // staff class
        $sel_query = "ClassID, ClassName";
        $params = array(
            'sel_query' => $sel_query,
            'active' => 1,         
            
            'sort_list' => array(
                array(
                    'order_by' => 'sort_index',
                    'sort' => 'ASC'
                )
            ),
            
			'display_query' => 0
        );

        $staff_classes_sql = $this->system_model->getStaffClasses($params);

        foreach( $staff_classes_sql->result() as $sc_row ){

            // add menu permission
            $data = array(
                'page' => $page_id,
                'staff_class' => $sc_row->ClassID
            );
            
            $this->db->insert('crm_page_permission_class', $data);

        }

        $this->session->set_flashdata('new_page_success', 1);  
        redirect('/menu/manager');

    }


    // update page
    public function update_page(){

        $page_id = $this->input->get_post('page_id');
        $page_name = $this->input->get_post('page_name');
        $page_url = $this->input->get_post('page_url');
        $menu = $this->input->get_post('menu');
        $active = $this->input->get_post('active');

        if( $page_id > 0 && $page_name != '' && $page_url != '' && $menu > 0 &&  is_numeric($active) ){

            $data = array(
                'page_name' => $page_name,
                'page_url' => $page_url,
                'menu' => $menu,
                'active' => $active
            );
            
            $this->db->where('crm_page_id', $page_id);
            $this->db->update('crm_pages', $data);

            $this->session->set_flashdata('update_page_success', 1);  
            redirect('/menu/manager');

        }        

    }


    // allow/deny staff
    public function allow_deny_staff(){

        $menu_id = $this->input->get_post('menu_id');
        $page_id = $this->input->get_post('page_id');
        $staff_account_arr = $this->input->get_post('staff_account');
        $denied = $this->input->get_post('denied');

        foreach( $staff_account_arr as $staff_account ){
	
            if( $staff_account != '' ){
                
                if( $menu_id > 0 ){ // allow staff to menu
                   
                    $data = array(
                        'menu' => $menu_id,
                        'user' => $staff_account,
                        'denied' => $denied
                    );
                    
                    $this->db->insert('menu_permission_user', $data);

                }else if( $page_id > 0 ){ // allow staff to page
                   
                    $data = array(
                        'page' => $page_id,
                        'user' => $staff_account,
                        'denied' => $denied
                    );

                    $this->db->insert('crm_page_permission_user', $data);

                }                

            }            
            
        }
        
        $this->session->set_flashdata('staff_allowed_success', 1);  
        redirect('/menu/manager');
        

    }


    public function get_allowed_denied_staff(){

        $menu_id = $this->input->get_post('menu_id');
        $page_id = $this->input->get_post('page_id');
        $denied = $this->input->get_post('denied');

        if( $menu_id > 0 ){ // allowed staff to menu
                   
           // get staff accounts     
            $sel_query = '
                mpu.mpu_id,
                sa.StaffID,
                sa.FirstName, 
                sa.LastName,
                sa.active
            ';   
            $params = array(
                'sel_query' => $sel_query,
                'active' => 1, 
                'menu' => $menu_id,
                'denied' => $denied,
                'display_query' => 0
            );
            
            $sql = $this->menu_model->get_menu_permission_user($params);
            
            // html markup
            $dp_str = null;
            foreach( $sql->result() as $sa_row ){

                $dp_str .='
                <div class="form-group row">
                    <div class="col-sm-8">
                        <p class="form-control-static">
                            <input type="text" class="form-control page_url" value="'.$this->system_model->formatStaffName($sa_row->FirstName,$sa_row->LastName).'" readonly="readonly" />
                        </p>
                    </div>
                    <div class="col-sm-4">
                        <p class="form-control-static">
                            <button type="button" class="btn btn-danger btn_remove_user_permission">Remove</button>
                            <input type="hidden" class="mpu_id" value="'.$sa_row->mpu_id.'" />
                            <input type="hidden" class="staff_id" value="'.$sa_row->StaffID.'" />
                        </p>
                    </div>
                </div>
                ';

            } 

        }else if( $page_id > 0 ){ // denied to page
           
            // get staff accounts     
            $sel_query = '
                cppu.cppu_id,
                sa.StaffID,
                sa.FirstName, 
                sa.LastName,
                sa.active
            ';   
            $params = array(
                'sel_query' => $sel_query,
                'active' => 1, 
                'page' => $page_id,
                'denied' => $denied,
                'display_query' => 0
            );
            
            $sql = $this->menu_model->get_page_permission_user($params);

            // html markup
            $dp_str = null;
            foreach( $sql->result() as $sa_row ){

                $dp_str .='
                <div class="form-group row">
                    <div class="col-sm-8">
                        <p class="form-control-static">
                            <input type="text" class="form-control page_url" value="'.$this->system_model->formatStaffName($sa_row->FirstName,$sa_row->LastName).'" readonly="readonly" />
                        </p>
                    </div>
                    <div class="col-sm-4">
                        <p class="form-control-static">
                            <button type="button" class="btn btn-danger btn_remove_user_permission">Remove</button>
                            <input type="hidden" class="cppu_id" value="'.$sa_row->cppu_id.'" />
                            <input type="hidden" class="staff_id" value="'.$sa_row->StaffID.'" />
                        </p>
                    </div>
                </div>
                ';

            }

        }

        echo $dp_str;

    }


    // menu staff class tick update
    public function menu_staff_class_permission_update(){

        $menu_id = $this->input->get_post('menu_id');
        $staff_class = $this->input->get_post('staff_class');
        $allow = $this->input->get_post('allow');


        if( $menu_id > 0 && $staff_class > 0 ){

            // delete
            $this->db->where('menu', $menu_id);
            $this->db->where('staff_class', $staff_class);
            $this->db->delete('menu_permission_class');
        
            if( $allow == 1 ){

                // insert
                $data = array(
                    'menu' => $menu_id,
                    'staff_class' => $staff_class
                );        
                $this->db->insert('menu_permission_class', $data);

            }

        }                
        
    }

    // page staff class tick update
    public function page_staff_class_permission_update(){

        $page_id = $this->input->get_post('page_id');
        $staff_class = $this->input->get_post('staff_class');
        $allow = $this->input->get_post('allow');


        if( $page_id > 0 && $staff_class > 0 ){

            // delete
            $this->db->where('page', $page_id);
            $this->db->where('staff_class', $staff_class);
            $this->db->delete('crm_page_permission_class');
        
            if( $allow == 1 ){

                // insert
                $data = array(
                    'page' => $page_id,
                    'staff_class' => $staff_class
                );        
                $this->db->insert('crm_page_permission_class', $data);

            }

        }                
        
    }

    // page staff class tick update
    public function remove_user_permission(){

        $mpu_id = $this->input->get_post('mpu_id');
        $cppu_id = $this->input->get_post('cppu_id');


        if(  $mpu_id > 0 ){

            // delete
            $this->db->where('mpu_id', $mpu_id);
            $this->db->delete('menu_permission_user');

        }

        if(  $cppu_id > 0 ){

            // delete
            $this->db->where('cppu_id', $cppu_id);
            $this->db->delete('crm_page_permission_user');

        }              
        
    }


}



?>
