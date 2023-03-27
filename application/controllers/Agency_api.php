<?php
class Agency_api extends CI_Controller {

	public function __construct(){

		parent::__construct();

        $this->load->model('palace_model');
        $this->load->model('api_model');

	}

	public function hide_api_property_toggle(){

        $agency_id = $this->input->get_post('agency_id');
        $api_prop_id_arr = $this->input->get_post('api_prop_id_arr');
        $hide_it = $this->input->get_post('hide_it');
        
        foreach( $api_prop_id_arr as $api_prop_id ){

            if( $agency_id > 0 && $api_prop_id != ''  ){

                // clear
                $this->db->where('agency_id', $agency_id);
                $this->db->where('api_prop_id', $api_prop_id);
                $this->db->delete('hidden_api_property');

                if( $hide_it == 1 ){

                    $insert_data = array(
                        'agency_id' => $agency_id,
                        'api_prop_id' => $api_prop_id
                    );                    
                    $this->db->insert('hidden_api_property', $insert_data);

                }
                
    
            }

        }                

    }

}
?>