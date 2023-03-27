<?php

class Messages extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->database();
        $this->load->model('messages_model');
        $this->load->model('tech_model');
        //$this->load->library('pagination');
    }
	
    public function index(){

        //$data['start_load_time'] = microtime(true);
        $data['title'] = "Messages";
        $uri = '/messages/index';
        $data['uri'] = $uri;

        $show_all = $this->input->get_post('show_all');
        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;
        $staff_class_id = $this->system_model->getStaffClassID();
        $show_all_filter = null;

        // pagination
        $per_page = 20;
        //$per_page = $this->config->item('pagi_per_page');
        $offset = ( $this->input->get_post('offset') )?$this->input->get_post('offset'):0;

        if( $show_all != 1 ){
            $show_all_filter = "AND mrb2.`read` IS NULL "; // show only unread
        }
        
        // paginated list
        $sql_str = "         
            SELECT 
                m3.`message_id`,
                m3.`message_header_id`, 
                m3.`message`,
                m3.`date`,
                
                mg2.`staff_id`,
                
                mrb2.`read`
            FROM `message` AS m3
            INNER JOIN(
                
                SELECT 
                    m.`message_header_id`, 
                    MAX(m.`date`) as latest_date

                FROM `message` AS m	
                LEFT JOIN `message_read_by` AS mrb ON ( m.`message_id` = mrb.`message_id` AND mrb.`staff_id` = {$staff_id} )
                LEFT JOIN `message_header` AS mh ON m.`message_header_id` = mh.`message_header_id`	
                INNER JOIN `message_group` AS mg ON ( mh.`message_header_id` = mg.`message_header_id` )
                WHERE mg.`staff_id` = {$staff_id}          
                GROUP BY m.`message_header_id`

            ) AS m4 ON ( m3.message_header_id = m4.message_header_id AND m3.date = m4.latest_date )
            LEFT JOIN `message_read_by` AS mrb2 ON ( m3.`message_id` = mrb2.`message_id` AND mrb2.`staff_id` = {$staff_id} )
            LEFT JOIN `message_header` AS mh2 ON m3.`message_header_id` = mh2.`message_header_id`
            INNER JOIN `message_group` AS mg2 ON ( mh2.`message_header_id` = mg2.`message_header_id` )
            WHERE mg2.`staff_id` = {$staff_id}   
            {$show_all_filter}
            ORDER by m3.date DESC
            LIMIT {$offset}, {$per_page}
        ";

        $data['msg_sql'] = $this->db->query($sql_str);      


        // total rows
        $sql_str = "
            SELECT COUNT(m3.`message_id`) AS msg_count
            FROM `message` AS m3
            INNER JOIN(
                
                SELECT 
                    m.`message_header_id`, 
                    MAX(m.`date`) as latest_date

                FROM `message` AS m	
                LEFT JOIN `message_read_by` AS mrb ON ( m.`message_id` = mrb.`message_id` AND mrb.`staff_id` = {$staff_id} )
                LEFT JOIN `message_header` AS mh ON m.`message_header_id` = mh.`message_header_id`	
                INNER JOIN `message_group` AS mg ON ( mh.`message_header_id` = mg.`message_header_id` )
                WHERE mg.`staff_id` = {$staff_id}          
                GROUP BY m.`message_header_id`

            ) AS m4 ON ( m3.message_header_id = m4.message_header_id AND m3.date = m4.latest_date )
            LEFT JOIN `message_read_by` AS mrb2 ON ( m3.`message_id` = mrb2.`message_id` AND mrb2.`staff_id` = {$staff_id} )
            LEFT JOIN `message_header` AS mh2 ON m3.`message_header_id` = mh2.`message_header_id`
            INNER JOIN `message_group` AS mg2 ON ( mh2.`message_header_id` = mg2.`message_header_id` )
            WHERE mg2.`staff_id` = {$staff_id}   
            {$show_all_filter}
            ORDER by m3.date DESC            
        ";
        $total_rows_sql = $this->db->query($sql_str);

        //$total_rows = $total_rows_sql->num_rows();
        $total_rows = $total_rows_sql->row()->msg_count;



        // pagination settings
        $pagi_links_params_arr = array(
            'show_all' => $show_all
        );
        $pagi_link_params = $uri.'/?'.http_build_query($pagi_links_params_arr);
       
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
        

        if( $staff_class_id == 6 ){ // tech
            $this->load->view('templates/inner_header_tech', $data);
        }else{
            $this->load->view('templates/inner_header', $data);
        }     
        $this->load->view('/messages/index', $data);
        if( $staff_class_id == 6 ){ // tech
            $this->load->view('templates/inner_footer_tech', $data);
        }else{
            $this->load->view('templates/inner_footer', $data);
        }        
        
    }

    // mark all unread message as read
    public function mark_as_read_all(){
        
        $this->messages_model->mark_as_read_all();
        redirect('/messages/index');

    }

    public function convo(){

        //$data['start_load_time'] = microtime(true);
        $data['title'] = "Conversation";

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;
        $staff_class_id = $this->system_model->getStaffClassID();

        $message_header_id = $this->input->get_post('id');

        $this->form_validation->set_rules('message', 'Message', 'required');

         // get logged staff name
         $params = array( 
            'sel_query' => '
                sa.`StaffID`,
                sa.`FirstName`,
                sa.`LastName`,
                sa.`profile_pic`
            ',
            'staff_id' => $staff_id,
            'active' => 1,
            'deleted' => 0,
            'display_query' => 0
        );
        
        // get user details
        $user_account_sql = $this->staff_accounts_model->get_staff_accounts($params);
        $user_account = $user_account_sql->row();	
        $logged_staff_name = $this->system_model->formatStaffName($user_account->FirstName,$user_account->LastName);
        $logged_user_profile_pic = $user_account->profile_pic;

        if ( $this->form_validation->run() == true ){

            $message_header_id = $this->input->get_post('id');  
            $message = $this->input->get_post('message');             

            // send message
			$data = array(
                'message_header_id' => $message_header_id,
                'author' => $staff_id,
                'message' => $message,
                'date' => date("Y-m-d H:i:s")
            );
        
            $this->db->insert('message', $data); 
            $message_id = $this->db->insert_id();
            
            //insert notifications                                   
            $notf_msg = "New <a href='".BASEURL."messages/convo/?id={$message_header_id}'>Message</a> From {$logged_staff_name}";

            // send notification to all message participants, except sender
            $mg_sql_str = "
            SELECT 
                mg.`message_group_id`,
                mg.`staff_id`

            FROM `message_group` AS mg
            WHERE mg.`message_header_id` = {$message_header_id}
            AND mg.`staff_id` != {$user_account->StaffID}
            ";
            $mg_sql = $this->db->query($mg_sql_str);

            foreach( $mg_sql->result() as $mg ){
                
                $notf_type = 1; // General Notifications
                $params = array(
                    'notf_type'=> $notf_type,
                    'staff_id'=> $mg->staff_id,
                    'country_id'=> $this->config->item('country'),
                    'notf_msg'=> $notf_msg
                );
                $this->gherxlib->insertNewNotification($params);

                // pusher notification
                $options = array(
                    'cluster' => $this->config->item('PUSHER_CLUSTER'),
                    'useTLS' => true
                );
                $pusher = new Pusher\Pusher(
                    $this->config->item('PUSHER_KEY'),
                    $this->config->item('PUSHER_SECRET'),
                    $this->config->item('PUSHER_APP_ID'),
                    $options
                );
            
                $pusher_data['notif_type'] = $notf_type;
                $ch = "ch".$mg->staff_id;
                $ev = "ev01";
                $out = $pusher->trigger($ch, $ev, $pusher_data);

                // mark read markers
                $insert_data = array(
                        'read' => 1,
                        'message_id ' => $message_id,
                        'staff_id ' => $staff_id,
                        'date ' => date("Y-m-d H:i:s")
                );
                $this->db->insert('message_read_by', $insert_data);

            }  
            
            redirect("/messages/convo/?id={$message_header_id}");

        }
        
        
        // update read markers
        // clear read markers
        $this->db->query("
            DELETE mrb
            FROM `message_read_by` AS mrb
            LEFT JOIN `message` AS m ON mrb.`message_id`  = m.`message_id`
            WHERE mrb.`staff_id` = {$staff_id}
            AND m.`message_header_id` = {$message_header_id}
        ");

        // get last message and mark it as read
        $msg_sql = $this->db->query("
            SELECT m.`message_id`
            FROM `message` AS m
            WHERE m.`message_header_id` = {$message_header_id}
            ORDER BY m.`date` DESC
            LIMIT 1
        ");
        $msg_sql = $msg_sql->row();

        // mark read markers
        $insert_data = array(
                'read' => 1,
                'message_id ' => $msg_sql->message_id,
                'staff_id ' => $staff_id,
                'date ' => date("Y-m-d H:i:s")
        );
        $this->db->insert('message_read_by', $insert_data);
        
        // select
        $sel_query = "
            m.`message_id`,
            m.`message_header_id`,
            m.`date`,
            m.`message`,
            m.`author`,
            
            sa.`FirstName`,
            sa.`LastName`,
            sa.`profile_pic`
        ";
        
		$params = array(
            'sel_query' => $sel_query,
            'message_header_id' => $message_header_id,

            'join_table' => array('staff_accounts'),

			'sort_list' => array(
				array(
					'order_by' => 'm.`date`',
					'sort' => 'ASC',
				)
            ),

			'display_query' => 0
		);
        $data['msg_sql'] = $this->messages_model->get_messages($params);
        
        
        $data['logged_user_profile_pic'] = $logged_user_profile_pic;
        
        if( $staff_class_id == 6 ){ // tech
            $this->load->view('templates/inner_header_tech', $data);
        }else{
            $this->load->view('templates/inner_header', $data);
        }   
        $this->load->view('/messages/convo', $data);
        $this->load->view('templates/inner_footer', $data);
        
    }

    // Create
    public function create(){

        //$data['start_load_time'] = microtime(true);
        $data['title'] = "Create";
        $uri = '/messages/create';        

        $country_id = $this->config->item('country');
        $staff_id = $this->session->staff_id;
        $staff_class_id = $this->system_model->getStaffClassID();

        $this->form_validation->set_rules('send_to[]', 'Send to', 'required');
        $this->form_validation->set_rules('message', 'Message', 'required');  
        
        $today = date("Y-m-d H:i:s");
        
		if ( $this->form_validation->run() == true ){

            $send_to = $this->input->get_post('send_to');
            $message = $this->input->get_post('message');  
            
            // create message header
            $data = array(
                'from' => $staff_id,
                'date' => date("Y-m-d H:i:s")
            );
            $this->db->insert('message_header', $data); 
            $message_header_id = $this->db->insert_id();

			// create message
            $data = array(
                'message_header_id' => $message_header_id,
                'author' => $staff_id,
                'message' => $message,
                'date' => $today
            );
            $this->db->insert('message', $data); 
            $message_id = $this->db->insert_id();

            // add sender to message message group
            $data = array(
                'message_header_id' => $message_header_id,
                'staff_id' => $staff_id     
            );
            $this->db->insert('message_group', $data); 

            // mark as read by default for sender
            $insert_data = array(
                'read' => 1,
                'message_id ' => $message_id,
                'staff_id ' => $staff_id,
                'date ' => $today
            );
            $this->db->insert('message_read_by', $insert_data);

            // insert notifications
            // get sender name
            $params = array( 
                'sel_query' => '
                    sa.`StaffID`,
                    sa.`FirstName`,
                    sa.`LastName`
                ',
                'staff_id' => $staff_id,
                'active' => 1,
                'deleted' => 0,
                'display_query' => 0
            );

            // get user details
            $user_account_sql = $this->staff_accounts_model->get_staff_accounts($params);
            $user_account = $user_account_sql->row();	
            $logged_staff_name = $this->system_model->formatStaffName($user_account->FirstName,$user_account->LastName);
                        
            $notf_msg = "New <a href='".BASEURL."messages/convo/?id={$message_header_id}'>Message</a> From {$logged_staff_name}";

            // send message to selected users
            foreach( $send_to as $send_to_staff_id ){
                
                // add other participants to message group
                $data = array(
                    'message_header_id' => $message_header_id,
                    'staff_id' => $send_to_staff_id     
                );
                $this->db->insert('message_group', $data); 

                $notf_type = 1; // General Notifications
                $params = array(
                    'notf_type'=> $notf_type,
                    'staff_id'=> $send_to_staff_id,
                    'country_id'=> $country_id,
                    'notf_msg'=> $notf_msg
                );
                $this->gherxlib->insertNewNotification($params);

                // pusher notification
                $options = array(
                    'cluster' => $this->config->item('PUSHER_CLUSTER'),
                    'useTLS' => true
                );
                $pusher = new Pusher\Pusher(
                    $this->config->item('PUSHER_KEY'),
                    $this->config->item('PUSHER_SECRET'),
                    $this->config->item('PUSHER_APP_ID'),
                    $options
                );
            
                $pusher_data['notif_type'] = $notf_type;
                $ch = "ch".$send_to_staff_id;
                $ev = "ev01";
                $pusher->trigger($ch, $ev, $pusher_data);
        
            }

            redirect("/messages/convo/?id={$message_header_id}");

        }
        
        // get staff accounts
        $custom_where = "sa.`StaffID` != {$staff_id}";
        $sel_query = '
            sa.`StaffID`,
            sa.`FirstName`,
            sa.`LastName`
        ';
        $params = array( 
            'sel_query' => $sel_query,
            'joins' => array('country_access'),
            'country_id' => $country_id,
            'active' => 1,
            'deleted' => 0,
            'sort_list' => array(
                array(
                    'order_by' => 'sa.`FirstName`',
                    'sort' => 'ASC',
				),
				array(
                    'order_by' => 'sa.`LastName`',
                    'sort' => 'ASC',
                ),
            ),
            'custom_where' => $custom_where,
            'display_query' => 0
        );
        
        // get user details
        $data['staff_sql'] = $this->staff_accounts_model->get_staff_accounts($params);
        
        $data['uri'] = $uri;
        
        if( $staff_class_id == 6 ){ // tech
            $this->load->view('templates/inner_header_tech', $data);
        }else{
            $this->load->view('templates/inner_header', $data);
        }     
        $this->load->view('messages/create', $data);
        $this->load->view('templates/inner_footer', $data);
    }


}

?>
