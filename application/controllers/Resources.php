<?php

class Resources extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->database();
        $this->load->model('resources_model');
        //$this->load->library('pagination');
    }
	
    public function index(){

        //$data['start_load_time'] = microtime(true);

        $staff_class_id = $this->system_model->getStaffClassID();
        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;
       
        if( $staff_class_id == 6 ){ // tech
            $data['title'] = "Resources";
        }else{
            $data['title'] = "Technician Documents";
        }


        // select
        $sel_query = "
            tdh.`tech_doc_header_id`,
            tdh.`name`
        ";
        
		$params = array(
			'sel_query' => $sel_query,
			'country_id' => $country_id,

            'group_by' => 'tdh.`tech_doc_header_id`',

			'sort_list' => array(
				array(
					'order_by' => 'tdh.`name`',
					'sort' => 'ASC',
				)
			),

			'display_query' => 0
		);
        $data['header_sql'] = $this->resources_model->get_tech_doc($params);

        if( $staff_class_id == 6 ){ // tech
            $this->load->view('templates/inner_header_tech', $data);
        }else{
            $this->load->view('templates/inner_header', $data);
        }  
        $this->load->view('/resources/index', $data);
        if( $staff_class_id == 6 ){ // tech
            $this->load->view('templates/inner_footer', $data);
        }else{
            $this->load->view('templates/inner_footer', $data);
        }        
        
    }

    public function tech_doc_admin(){

        //$data['start_load_time'] = microtime(true);

        $staff_class_id = $this->system_model->getStaffClassID();
        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;
       
        $data['title'] = "Technician Documents - Admin";


        // select
        $sel_query = "
            tdh.`tech_doc_header_id`,
            tdh.`name`
        ";
        
		$params = array(
			'sel_query' => $sel_query,
			'country_id' => $country_id,

            'group_by' => 'tdh.`tech_doc_header_id`',

			'sort_list' => array(
				array(
					'order_by' => 'tdh.`name`',
					'sort' => 'ASC',
				)
			),

			'display_query' => 0
		);
        $data['tech_doc_sql'] = $this->resources_model->get_tech_doc($params);


        // get headers
        $data['tech_doc_headers'] = $this->db->query("
            SELECT *
            FROM `tech_doc_header`
            WHERE `country_id` = {$country_id}
            ORDER BY `name`
        ");	


        $this->load->view('templates/inner_header', $data);  
        $this->load->view('/resources/tech_doc_admin', $data);
        $this->load->view('templates/inner_footer', $data);        
        
    }

    public function tech_doc_add_file(){

        
        $header = $this->db->escape_str($this->input->get_post('header'));
        $title = $this->db->escape_str($this->input->get_post('title'));       
    
        $today_full = date("Y-m-d H:i:s");

        
        // if a vehicle image file has been selected
        if( $_FILES["file"]['name'] != '' ){

            // Upload vehicle image
            $config['upload_path']          = 'uploads/tech_documents';
            //$config['allowed_types']        = 'gif|jpg|png|pdf';
            $config['allowed_types']        = '*';

            // custom filename, plus random characters to avoid conflict of same file name
            $file = pathinfo($_FILES["file"]['name']);
            $custom_filename = 'tech_doc_'.date('YmdHis').rand().'.'. $file['extension'];
            
            $config['file_name'] = $custom_filename; // set custom file name
            //$config['max_size']             = 100;
            //$config['max_width']            = 1024;
            //$config['max_height']           = 768;      
            
            $this->load->library('upload', $config);

            if ( $this->upload->do_upload('file') ){

                $upload_data = $this->upload->data();

                if( $upload_data ){

                    $file_name = $upload_data['file_name'];  
                    $file_type = 1; // file 

                    // add tech doc
                    $this->db->query("
                        INSERT INTO
                        `technician_documents`(
                            `tech_doc_header_id`,
                            `type`,
                            `filename`,                            
                            `title`,
                            `date`
                        )
                        VALUES(
                            '{$header}',
                            {$file_type},
                            '{$file_name}',          
                            '{$title}',
                            '{$today_full}'
                        )                        
                    "); 
                    
                    $this->session->set_flashdata('tech_doc_add_success',true);
                    
                }                

            }else{

                echo $this->upload->display_errors();

            }

        } 
        
        redirect("/resources/tech_doc_admin");

    }

    public function tech_doc_add_link(){

        
        $header = $this->db->escape_str($this->input->get_post('header'));
        $url = $this->db->escape_str($this->input->get_post('url'));
        $title = $this->db->escape_str($this->input->get_post('title'));       
    
        $today_full = date("Y-m-d H:i:s");

        
        // if a vehicle image file has been selected
        if( $url != '' ){

            $file_type = 2; // URL 

            // add tech doc
            $this->db->query("
                INSERT INTO
                `technician_documents`(
                    `tech_doc_header_id`,
                    `type`,
                    `url`,                            
                    `title`,
                    `date`
                )
                VALUES(
                    '{$header}',
                    {$file_type},
                    '{$url}',          
                    '{$title}',
                    '{$today_full}'
                )                        
            "); 
            
            $this->session->set_flashdata('tech_doc_add_success',true);

        }
        
        redirect("/resources/tech_doc_admin");

    }



    public function delete_tech_doc(){
        
        $tech_doc_id = $this->db->escape_str($this->input->get_post('tech_doc_id'));    

        // DELETE the file
        $file_type = 1; // File 

        $tech_doc_sql = $this->db->query("
        SELECT `filename`
        FROM `technician_documents`  
        WHERE `technician_documents_id` = {$tech_doc_id}   
        AND `type` = {$file_type}
        AND `filename` != ''
        ");

        if( $tech_doc_sql->num_rows() ){

            $tech_doc_row = $tech_doc_sql->row();
            $filename = $tech_doc_row->filename;

            if( $filename !='' ){

                $tech_doc_folder = 'uploads/tech_documents'; // uploaded folder
                $path_to_file = FCPATH."{$tech_doc_folder}/{$filename}";  
                
                if ( strpos($path_to_file, $tech_doc_folder ) !== false ) { // make sure it deletes on the uploaded folder    
            
                    if( file_exists($path_to_file) ){ // make sure file exist
    
                        unlink($path_to_file); // delete the file
    
                    }
    
                }

            }           

        }       

        // DELETE db entry        
        if( $tech_doc_id != '' ){

            // DELETE tech doc
            $this->db->query("
                DELETE
                FROM `technician_documents`  
                WHERE `technician_documents_id` = {$tech_doc_id}                   
            "); 

        }
        
        
       

    }


    public function add_tech_doc_header(){

        
        $header_name = $this->db->escape_str($this->input->get_post('header_name'));  
        $country_id = $this->config->item('country'); 

        if( $header_name != '' ){

            // add tech doc
            $this->db->query("
                INSERT INTO 
                `tech_doc_header`(
                    `name`,
                    `country_id`
                )
                VALUES(
                    '{$header_name}',
                    {$country_id}
                )                   
            "); 
            
            $this->session->set_flashdata('tech_doc_add_header_success',true);

        }
        
        redirect("/resources/tech_doc_admin");

    }


    public function update_tech_doc_header(){

        $tech_doc_header_id = $this->db->escape_str($this->input->get_post('tech_doc_header_id'));  
        $header_name = $this->db->escape_str($this->input->get_post('header_name'));  
        
        if( $header_name != '' && $tech_doc_header_id > 0 ){

            // update tech doc
            $this->db->query("
                UPDATE `tech_doc_header`
                SET `name` = '{$header_name}'      
                WHERE `tech_doc_header_id` = {$tech_doc_header_id}              
            ");             

        }

    }


    public function delete_tech_doc_header(){

        $tech_doc_header_id = $this->db->escape_str($this->input->get_post('tech_doc_header_id'));  
        
        if( $tech_doc_header_id > 0 ){

            // delete tech doc
            $this->db->query("
                DELETE
                FROM `tech_doc_header`    
                WHERE `tech_doc_header_id` = {$tech_doc_header_id}              
            ");             

        }

    }


    public function section(){

        //$data['start_load_time'] = microtime(true);
        
        $uri = '/resources/section';
        $data['uri'] = $uri;

        $header_id = $this->input->get_post('header_id');
        $data['header_id'] = $header_id;
        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;
        $staff_class_id = $this->system_model->getStaffClassID();

        if( $header_id > 0 ){

            // get tech do header
            $sel_query = "
                tdh.`tech_doc_header_id`,
                tdh.`name`                 
            ";
            
            $params = array(
                'sel_query' => $sel_query,
                'country_id' => $this->config->item('country'),
                'header_id' => $header_id,
                'display_query' => 0
            );
            $tdh_sql = $this->resources_model->get_tech_doc_header($params);
            $tdh_row = $tdh_sql->row();
            $data['title'] = $tdh_row->name;

            // get list items
            $sel_query = "
                td.`technician_documents_id`,
                td.`type`,
                td.`path`,
                td.`filename`,
                td.`title`,  
                td.`url`,
                td.`date`                      
            ";
            
            $params = array(
                'sel_query' => $sel_query,
                'country_id' => $this->config->item('country'),
                'header_id' => $header_id,

                'sort_list' => array(
                    array(
                        'order_by' => 'td.`title`',
                        'sort' => 'ASC',
                    )
                ),

                'display_query' => 0
            );
            $data['tech_doc_sql'] = $this->resources_model->get_tech_doc($params);
            
            if($data['tech_doc_sql']->num_rows() == 0 && $staff_class_id == 6){
                // pass staffs data to section view
                $data['all_admins_staffs'] = $this->resources_model->getAdminsStaffs();
                $data['all_admins_staffs_null'] = $this->resources_model->getAdminsStaffsNull();
    
                $data['all_techs_staffs'] = $this->resources_model->getTechsStaffs();
                $data['all_techs_staffs_null'] = $this->resources_model->getTechsStaffsNull();
                
            }

            // passed data
            $data['about_page_text'] = $this->resources_model->about_page_text($header_id);


            if( $staff_class_id == 6 ){ // tech
                $this->load->view('templates/inner_header_tech', $data);
            }else{
                $this->load->view('templates/inner_header', $data);
            }  

            $this->load->view($uri, $data);

            if( $staff_class_id == 6 ){ // tech
                $this->load->view('templates/inner_footer', $data);
            }else{
                $this->load->view('templates/inner_footer', $data);
            }     

        }
          
        
    }


}

?>
