<?php

class Blink_api_model extends CI_Model{

    public function __construct(){
        $this->load->database();
    }
	
	// BLINK API ( URL shortener ) -- START
	// GET ACCESS TOKEN
	public function getBlinkAccessToken(){
	
		$url = "https://app.bl.ink/api/v3/access_token";
		$header = array(
			"Content-Type: application/json"
		);
		
		// authentication data
		// using refresh token
		$data = array(
			"email" => $this->config->item('blink_email'),
			"refresh_token" => $this->config->item('blink_refresh_token')
		); 
		
		/*
		// using password
		$data = array(
			"email" => $this->config->item('blink_email'),
			"password" => $this->config->item('blink_pass')
		);
		*/
	
		$curl_params = array(
			'url' => $url,
			'post_get' => 'POST',
			'header' => $header,
			'data' => $data,			
			'display_cron_options' => 0,
			'display_json' => 0
		);
	
		$result_json = $this->system_model->jcustom_curl($curl_params);
	
		return $access_token = $result_json->access_token;
	
	}
	
	// GET DOMAIN
	public function getBlinkDomain($access_token){
	
		$url = "https://app.bl.ink/api/v3/domains";
		$header = array(
			"Authorization: Bearer {$access_token}",
			"Content-Type: application/json"
		);
	
		$curl_params = array(
			'url' => $url,
			'post_get' => 'POST',
			'header' => $header,
			'display_cron_options' => 0,
			'display_json' => 1
		);
	
		$result_json = $this->system_model->jcustom_curl($curl_params);
	
		return $domain_id = $result_json->objects[0]->id;
	
	}
	
	// SHORTEN LINK
	public function shortenLink($params){

		$domain_id = $this->config->item('blink_domain_id');
	
		$url = "https://app.bl.ink/api/v3/{$domain_id}/links";
		$header = array(
			"Authorization: Bearer {$params['access_token']}",
			"Content-Type: application/json"
		);
		// parameters
		$data = array(
			"url" => $params['orig_link']
		);  
	
		$curl_params = array(
			'url' => $url,
			'post_get' => 'POST',
			'data' => $data,
			'header' => $header,
			'display_cron_options' => 0,
			'display_json' => 0
		);
	
		$result_json = $this->system_model->jcustom_curl($curl_params);
	
		return $short_link = $result_json->objects->short_link;
	
	}
	// BLINK API ( URL shortener ) -- END

}
