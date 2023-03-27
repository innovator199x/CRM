<?php

class Messages_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
    }

    public function get_message_header($params)
    {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`message_header` AS mh');
        $this->db->join('`message_group` AS mg', 'mh.`message_header_id` = mg.`message_header_id`', 'left');
        $this->db->join('`staff_accounts` AS sa', 'mh.`from` = sa.`StaffID`', 'left');

        // custom joins
		if( isset($params['custom_joins']) && $params['custom_joins'] != '' ){
			$this->db->join($params['custom_joins']['join_table'],$params['custom_joins']['join_on'], $params['custom_joins']['join_type']);
        }
		
        // filter
        if ( $params['staff_id'] > 0  ) {
            $this->db->where('mg.`staff_id`', $params['staff_id']);
        }

        if ( is_numeric($params['deleted'])  ) {
            $this->db->where('mh.`deleted`', $params['deleted']);
        }

      	// custom filter
        if( isset($params['custom_where']) ){
             $this->db->where($params['custom_where']);
        }
		
		// custom filter
        if( isset($params['custom_where_arr']) ){
			foreach( $params['custom_where_arr'] as $index => $custom_where ){
				if( $custom_where != '' ){
					$this->db->where($custom_where);
				}				
			}              
        }
		
		
		// group by
        if( isset($params['group_by']) && $params['group_by'] != '' ){
              $this->db->group_by($params['group_by']);
        }		

        // sort
        if (isset($params['sort_list'])) {
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }
		
		// custom filter
        if( isset($params['custom_sort']) ){
              $this->db->order_by($params['custom_sort']);
        }

        // limit
		if( isset($params['limit']) && $params['limit'] > 0 ){
			$this->db->limit( $params['limit'], $params['offset']);
		}	

		$query = $this->db->get();
		if( isset($params['display_query']) && $params['display_query'] == 1 ){
			echo $this->db->last_query();
		}
		
		return $query;
		
    } 

    public function get_message_group($params)
    {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`message_group` AS mg');
        $this->db->join('`staff_accounts` AS sa', 'mg.`staff_id` = sa.`StaffID`', 'left');

        // custom joins
		if( isset($params['custom_joins']) && $params['custom_joins'] != '' ){
			$this->db->join($params['custom_joins']['join_table'],$params['custom_joins']['join_on'], $params['custom_joins']['join_type']);
        }
		
        // filter
        if ( $params['message_header_id'] > 0  ) {
            $this->db->where('mg.`message_header_id`', $params['message_header_id']);
        } 

        if ( $params['staff_id'] > 0  ) {
            $this->db->where('mg.`staff_id`', $params['staff_id']);
        }

      	// custom filter
        if( isset($params['custom_where']) ){
             $this->db->where($params['custom_where']);
        }
		
		// custom filter
        if( isset($params['custom_where_arr']) ){
			foreach( $params['custom_where_arr'] as $index => $custom_where ){
				if( $custom_where != '' ){
					$this->db->where($custom_where);
				}				
			}              
        }
		
		
		// group by
        if( isset($params['group_by']) && $params['group_by'] != '' ){
              $this->db->group_by($params['group_by']);
        }		

        // sort
        if (isset($params['sort_list'])) {
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }
		
		// custom filter
        if( isset($params['custom_sort']) ){
              $this->db->order_by($params['custom_sort']);
        }

        // limit
		if( isset($params['limit']) && $params['limit'] > 0 ){
			$this->db->limit( $params['limit'], $params['offset']);
		}	

		$query = $this->db->get();
		if( isset($params['display_query']) && $params['display_query'] == 1 ){
			echo $this->db->last_query();
		}
		
		return $query;
		
    }

    public function get_messages($params)
    {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`message` AS m');        

        // set joins
		if( $params['join_table'] > 0 ){
			
			foreach(  $params['join_table'] as $join_table ){
				
				if( $join_table == 'message_header' ){
					$this->db->join('`message_header` AS mh', 'm.`message_header_id` = mh.`message_header_id`', 'left');
                }	
                
                if( $join_table == 'staff_accounts' ){
                    $this->db->join('`staff_accounts` AS sa', 'm.`author` = sa.`StaffID`', 'left');
				}
				
			}			
			
        }

        // custom joins
		if( isset($params['custom_joins']) && $params['custom_joins'] != '' ){
			$this->db->join($params['custom_joins']['join_table'],$params['custom_joins']['join_on'], $params['custom_joins']['join_type']);
        }
		
        // filter
        if ( $params['author'] > 0  ) {
            $this->db->where('m.`author`', $params['author']);
        }

        if ( $params['staff_id'] > 0  ) {
            $this->db->where('mg.`staff_id`', $params['staff_id']);
        }

        // filter
        if ( $params['message_header_id'] > 0  ) {
            $this->db->where('m.`message_header_id`', $params['message_header_id']);
        }

      	// custom filter
        if( isset($params['custom_where']) ){
             $this->db->where($params['custom_where']);
        }
		
		// custom filter
        if( isset($params['custom_where_arr']) ){
			foreach( $params['custom_where_arr'] as $index => $custom_where ){
				if( $custom_where != '' ){
					$this->db->where($custom_where);
				}				
			}              
        }
		
		
		// group by
        if( isset($params['group_by']) && $params['group_by'] != '' ){
              $this->db->group_by($params['group_by']);
        }		

        // sort
        if (isset($params['sort_list'])) {
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $this->db->order_by($sort_arr['order_by'], $sort_arr['sort']);
                }
            }
        }
		
		// custom filter
        if( isset($params['custom_sort']) ){
              $this->db->order_by($params['custom_sort']);
        }

        // limit
		if( isset($params['limit']) && $params['limit'] > 0 ){
			$this->db->limit( $params['limit'], $params['offset']);
		}	

		$query = $this->db->get();
		if( isset($params['display_query']) && $params['display_query'] == 1 ){
			echo $this->db->last_query();
		}
		
		return $query;
		
    }


    public function mark_as_read_all(){

        $staff_id = $this->session->staff_id;
        $today = date('Y-m-d H:i:s');

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
            AND mrb2.`read` IS NULL
            ORDER by m3.date DESC            
        ";

        $msg_sql = $this->db->query($sql_str);   
        foreach( $msg_sql->result() as $msg_row ){

            // get last message ID and mark is as read
            if( $msg_row->message_header_id > 0 && $msg_row->message_id > 0 && $staff_id > 0 ){

                // clear read of user per message header/convo, so the seen heads dont duplicate
                $delete_sql_str = "
                    DELETE mrb
                    FROM `message_read_by` AS mrb
                    LEFT JOIN `message` AS m ON mrb.`message_id`  = m.`message_id`
                    WHERE mrb.`staff_id` = {$staff_id}
                    AND m.`message_header_id` = {$msg_row->message_header_id}
                ";                                          
                $this->db->query($delete_sql_str);

                // insert new seen head at the recent message of convo
                $insert_data = array(
                    'message_id' => $msg_row->message_id,
                    'staff_id' => $staff_id,
                    'read' => 1,
                    'date' => $today
                );                                                            
                $this->db->insert('message_read_by', $insert_data);
                
            }                        

        }

         

    }


}
