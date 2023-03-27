<?php
class API_model extends CI_Model {

	public function __construct(){
        $this->load->database();
        
        $this->clientId = $this->config->item('PME_CLIENT_ID');
        $this->clientSecret = $this->config->item('PME_CLIENT_SECRET');
        $this->clientScope = $this->config->item('PME_CLIENT_Scope');
        $this->urlCallBack = urlencode($this->config->item('PME_URL_CALLBACK'));
        $this->accessTokenUrl = $this->config->item('PME_ACCESS_TOKEN_URL');
        $this->authorizeUrl = $this->config->item('PME_AUTHORIZE_URL');
    }
	
	public function getPmeAccessToken($authorization_code) {

        $token_url = $this->accessTokenUrl;
        $client_id = $this->clientId;
        $client_secret = $this->clientSecret;
        $callback_uri = $this->urlCallBack;

        $authorization = base64_encode("$client_id:$client_secret");
        $header = array("Authorization: Basic {$authorization}","Content-Type: application/x-www-form-urlencoded");
        $content = "grant_type=authorization_code&code=$authorization_code&redirect_uri=$callback_uri";

        $curl_opt = array(
            CURLOPT_URL => $token_url,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $content
        );

        $curl = curl_init();
        
        curl_setopt_array($curl, $curl_opt);
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;

    }


    public function refreshPmeToken($refresh_token) {

        $token_url = $this->accessTokenUrl;
        $client_id = $this->clientId;
        $client_secret = $this->clientSecret;
        $callback_uri = $this->urlCallBack;

        $authorization = base64_encode("$client_id:$client_secret");
        $header = array("Authorization: Basic {$authorization}","Content-Type: application/x-www-form-urlencoded");
        $content = "grant_type=refresh_token&refresh_token=$refresh_token&redirect_uri=$callback_uri";

        $curl_opt = array(
            CURLOPT_URL => $token_url,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $content
        );

        $curl = curl_init();
        
        curl_setopt_array($curl, $curl_opt);
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;

    }

    public function pme_auth_link(){

        return $this->config->item('PME_AUTHORIZE_URL') . "?response_type=code&state=abc123&client_id=".$this->config->item('PME_CLIENT_ID')."&scope=".$this->config->item('PME_CLIENT_Scope')."&redirect_uri=".$this->config->item('PME_URL_CALLBACK');

    }

    public function get_agency_api($params)
    {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`agency_api`');
		
        // filter
        if ( $params['agency_api_id'] > 0 ) {
            $this->db->where('`agency_api_id`', $params['agency_api_id']);
        }

        if( $params['active'] > 0 ){
			$this->db->where('`active`', $params['active']);
		}

      	// custom filter
        if( isset($params['custom_where']) ){
             $this->db->where($params['custom_where']);
        }
		
		// custom filter arr
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


    public function get_agency_api_integration($params)
    {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`agency_api_integration` AS agen_api_int');
        $this->db->join('`agency_api` AS agen_api', 'agen_api_int.`connected_service` = agen_api.`agency_api_id`', 'left');

        // set joins
		if( $params['join_table'] > 0 ){
			
			foreach(  $params['join_table'] as $join_table ){
				
				if( $join_table == 'agency' ){
					$this->db->join('`agency` AS a', 'agen_api_int.`agency_id` = a.`agency_id`', 'left');
                }
                			
			}			
			
		}

        // custom joins
		if( isset($params['custom_joins']) && $params['custom_joins'] != '' ){
			$this->db->join($params['custom_joins']['join_table'],$params['custom_joins']['join_on'], $params['custom_joins']['join_type']);
        }
		
        // filter
        if ( $params['api_integration_id'] > 0 ) {
            $this->db->where('agen_api_int.`api_integration_id`', $params['api_integration_id']);
        }

        if ( is_numeric($params['active']) ) {
            $this->db->where('agen_api_int.`active`', $params['active']);
        }

        if ( $params['agency_id'] > 0 ) {
            $this->db->where('agen_api_int.`agency_id`', $params['agency_id']);
        }

        if ( $params['api_id'] > 0 ) {
            $this->db->where('agen_api_int.`connected_service`', $params['api_id']);
        }

      	// custom filter
        if( isset($params['custom_where']) ){
             $this->db->where($params['custom_where']);
        }
		
		// custom filter arr
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

    public function get_agency_api_tokens($params)
    {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('`agency_api_tokens` AS agen_api_tok');
        $this->db->join('`agency_api` AS agen_api', 'agen_api_tok.`api_id` = agen_api.`agency_api_id`', 'left');

        // set joins
		if( $params['join_table'] > 0 ){
			
			foreach(  $params['join_table'] as $join_table ){
				
				if( $join_table == 'agency' ){
					$this->db->join('`agency` AS a', 'agen_api_tok.`agency_id` = a.`agency_id`', 'left');
                }

                if( $join_table == 'pme_unmatched_property_count' ){
					$this->db->join('`pme_unmatched_property_count` AS pme_upc', 'agen_api_tok.`agency_id` = pme_upc.`agency_id`', 'left');
                }
                			
			}			
			
		}
		
        // filter
        if ( $params['agency_api_token_id'] > 0 ) {
            $this->db->where('agen_api_tok.`agency_api_token_id`', $params['agency_api_token_id']);
        }

        if ( $params['api_id'] > 0 ) {
            $this->db->where('agen_api_tok.`api_id`', $params['api_id']);
        }

        if ( $params['deactivated'] > 0 ) {
            $this->db->where('`a`.`status` !=', "deactivated");
        }

        if ( $params['target'] > 0 ) {
            $this->db->where('`a`.`status` !=', "target");
        }

        if ( $params['agency_id'] > 0 ) {
            $this->db->where('agen_api_tok.`agency_id`', $params['agency_id']);
        }

        if( $params['active'] > 0 ){
			$this->db->where('agen_api_tok.`active`', $params['active']);
		}

      	// custom filter
        if( isset($params['custom_where']) ){
             $this->db->where($params['custom_where']);
        }
		
		// custom filter arr
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

    public function get_agency_api_marker($params)
    {

        if (isset($params['sel_query'])) {
            $sel_query = $params['sel_query'];
        } else {
            $sel_query = '*';
        }

        $this->db->select($sel_query);
        $this->db->from('agency');
        $this->db->where('agency_id', $params['agency_id']);

        $query = $this->db->get();
		return $query;
		
    }

    public function if_notes_already_exist_in_pnv($params) {

        if( $params['property_id'] != '' && $params['property_source'] !='' ){

            $this->db->select('pnv_id');
            $this->db->from('properties_needs_verification');
            $this->db->where('property_id', $params['property_id']);
            $this->db->where('property_source', $params['property_source']);
            $query = $this->db->get();
            $pnv_count = $query->num_rows();

            if ($pnv_count > 0 ) {
                return true;
            }else {
                return false;
            }

        }         

    }
		
		
}
?>