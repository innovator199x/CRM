<?php

class Agency2 extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('agency_model');
        $this->load->model('staff_accounts_model');
        $this->load->model('properties_model');
        $this->load->library('pagination');
    }

    public function view_agency_booking_notes() {


        $data['start_load_time'] = microtime(true);
        $data['title'] = "Agency Booking Notes";
        $per_page = $this->config->item('pagi_per_page');
        $offset = ( null != $this->input->get_post('offset') ) ? $this->input->get_post('offset') : '0';

        $agencies = $this->agency_model->get_booking_notes([
                    'sort_list' => array(
                        array(
                            'order_by' => 'a.`agency_name`',
                            'sort' => 'ASC'
                        )
                    ),
                    'distinct_sql' => '(a.`agency_id`), a.`agency_name` ',
                    'echo_query' => 0,
                    'country_id' => $this->config->item('country')
                ])->result_array();
        $data['agencies'] = $agencies;
        $agency_booking_notes = array();
        foreach ($agencies as $agency) {

            if( $agency['agency_id']!="" ){ //agency id must not be empty
                $booking_notes = $this->agency_model->get_booking_notes([
                    'sort_list' => array(
                        array(
                            'order_by' => 'a.`agency_name`',
                            'sort' => 'ASC'
                        )
                    ),
                    'custom_select' => 'bn.notes,bn.booking_notes_id,st_ac.FirstName,st_ac.LastName',
                    'echo_query' => 0,
                    'country_id' => $this->config->item('country'),
                    'agency_id' => $agency['agency_id']
                ])->result_array();
                $agency_booking_notes[$agency['agency_id']] = [
                    'name' => $agency['agency_name'],
                    'data' => $booking_notes
                ];
            }
           
        }

        $agency_list = $this->agency_model->get_agency([
            'a_status' => 'active',
            'sel_query' => "a.agency_id,a.agency_name",
            'country_id' => $this->config->item('country'),
            'sort_list' => array(
                array(
                    'order_by' => 'a.`agency_name`',
                    'sort' => 'ASC'
                )
            ),
            'display_echo' => 0
        ]);
        $data['agency_list'] = $agency_list->result_array();
        $data['agency_booking_notes'] = $agency_booking_notes;
        $this->load->view('templates/inner_header', $data);
        $this->load->view('agency/view_agency_booking_notes', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function update_booking_notes_action_ajax() {
        $bn_id = $this->input->get_post('bn_id');
        $bn_notes = $this->input->get_post('bn_notes');
        $staff_id = $this->session->staff_id;
        $country_id = $this->config->item('country');
        $isUpdate = $this->agency_model->update_booking_notes($bn_notes, $bn_id);
        $logAdded = $this->agency_model->add_booking_notes_log([
            'bn_id' => $bn_id,
            'title' => 'Update Booking Notes',
            'msg' => 'Booking notes has been updated',
            'staff_id' => $staff_id,
            'country_id' => $country_id
        ]);
        if ($isUpdate && $logAdded) {
            $this->session->set_flashdata([
                'success_msg' => 'Selected Note has been successfully updated',
                'status' => 'success'
            ]);
        } else {
            $this->session->set_flashdata([
                'error_msg' => 'Unsuccessful',
                'status' => 'error'
            ]);
            exit;
        }
    }

    public function delete_booking_notes_action_ajax() {
        $bn_id = $this->input->get_post('bn_id');
        $staff_id = $this->session->staff_id;
        $country_id = $this->config->item('country');
        $isDeleted = $this->agency_model->delete_booking_notes($bn_id);
        $logAdded = $this->agency_model->add_booking_notes_log([
            'bn_id' => $bn_id,
            'title' => 'Notes Deleted',
            'msg' => 'Booking notes has been deleted',
            'staff_id' => $staff_id,
            'country_id' => $country_id
        ]);
        if ($isDeleted && $logAdded) {
            $this->session->set_flashdata([
                'success_msg' => 'Selected Note has been successfully deleted',
                'status' => 'success'
            ]);
        } else {
            $this->session->set_flashdata([
                'error_msg' => 'Unsuccessful',
                'status' => 'error'
            ]);
            exit;
        }
    }

    public function create_booking_notes_action_form_submit() {
        $country_id = $this->config->item('country');
        $agency_booking_notes = $this->input->post('agency_booking_notes');
        $agency_id = $this->input->post('agency_id');
        $staff_id = $this->session->staff_id;
        $bn_id = $this->agency_model->create_booking_notes($agency_booking_notes, $agency_id, $country_id);

        $logAdded = $this->agency_model->add_booking_notes_log([
            'bn_id' => $bn_id,
            'title' => 'Add Booking Notes',
            'msg' => 'New booking notes created',
            'staff_id' => $staff_id,
            'country_id' => $country_id
        ]);
        if ((int) $bn_id > 0 && $logAdded) {
            $this->session->set_flashdata([
                'success_msg' => 'Note has been successfully Added',
                'status' => 'success'
            ]);
        } else {
            $this->session->set_flashdata([
                'error_msg' => 'Unsuccessful',
                'status' => 'error'
            ]);
        }
        redirect(base_url('/agency2/view_agency_booking_notes'));
    }

    public function view_add_prospects() {
        $data['start_load_time'] = microtime(true);
        $data['title'] = "Add Prospects";
        $state_list = $this->properties_model->getCountryState()->result_array();
        $data['state_list'] = $state_list;
        $this->load->view('templates/inner_header', $data);
        $this->load->view('agency/view_add_prospects', $data);
        $this->load->view('templates/inner_footer', $data);
    }

    public function add_prospects_action_form_submit() {

        // profile
        $agency_name = $this->input->post('agency_name');
        $franchise_group = $this->input->post('franchise_group');
        $abn = $this->input->post('abn');
        $street_number = $this->input->post('street_number');
        $street_name = $this->input->post('street_name');
        $suburb = $this->input->post('suburb');
        $phone = $this->input->post('phone');
        $state = $this->input->post('state');
        $postcode = $this->input->post('postcode');
        $region = $this->input->post('region');
        $country = $this->input->post('country');
        $totprop = $this->input->post('totprop');
        $user = $this->input->post('user');
        $pass2 = null;
        $agency_using = $this->input->post('agency_using');

        // agency contact
        $ac_fname = $this->input->post('ac_fname');
        $ac_lname = $this->input->post('ac_lname');
        $ac_phone = $this->input->post('ac_phone');
        $ac_email = $this->input->post('ac_email');
        $agency_emails = $this->input->post('agency_emails');


        // preferences

        $allow_dk = $this->input->post('allow_dk');
        $allow_en = $this->input->post('allow_en');
        $comment = $this->input->post('comment');
        $agency_hours = $this->input->post('agency_hours');


        $acc_name = $this->input->post('acc_name');
        $acc_phone = $this->input->post('acc_phone');
        $website = $this->input->post('website');


        // sales rep
        $salesrep = $this->input->post('salesrep');

        // sales rep
        $agen_stat = $this->input->post('agen_stat');

        // legal name




        $address = "{$street_number} {$street_name} {$suburb} {$state} {$postcode}, {$_SESSION['country_name']}";
        $coordinate = $this->system_model->getGoogleMapCoordinates($address);

        $agency_id = $this->agency_model->add_agency($agency_name, $franchise_group, $street_number, $street_name, $suburb, $phone, $state, $postcode, $region, $country, $coordinate['lat'], $coordinate['lng'], $totprop, $agency_hours, $comment, $user, $pass2, $ac_fname, $ac_lname, $ac_phone, $ac_email, $agency_emails, '', '', '', '', '', '', $salesrep, $agen_stat, $agency_using, '', '', '', '', '', $abn, $acc_name, $acc_phone, $allow_dk, $website, $allow_en, '', '', '', '', '');
        $link = $this->config->item("crm_link") . "/view_agency_details.php?id={$agency_id}&add_agency_ty_msg=1";
        redirect($link);
    }

}
