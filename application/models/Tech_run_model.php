<?php
class Tech_run_model extends CI_Model {

    public function __construct(){
        $this->load->database();
    }

    // old function: assignTechRunPinColors
    public function assign_pin_colours($params){

        $trr_hl_color = ( $params['trr_hl_color'] != -1 )?$params['trr_hl_color']:'NULL'; // color

        foreach( $params['trr_id_arr'] as $trr_id ){

            if( $params['tr_id'] > 0 && $trr_id > 0 ){

                // update
                $this->db->query("
                UPDATE `tech_run_rows`
                SET `highlight_color` = {$trr_hl_color}
                WHERE `tech_run_id` = {$params['tr_id']}
                AND `tech_run_rows_id` = {$trr_id}
                ");

            }

        }

    }

    // old function: techRunUpdateStartEndPoint
    function update_start_and_end($params){

        if( $params['start']!="" || $params['end']!="" ){

            $country_id = $this->config->item('country');

            // get country data
            $country_params = array(
                'sel_query' => 'c.`country`',
                'country_id' => $country_id
            );
            $country_sql = $this->system_model->get_countries($country_params);
            $country_row = $country_sql->row();
            $country_name = $country_row->country;

            // check lat/lng
            // start point
            if($params['start']>0){

                // start
                // get accomodation address
                $acc_sql = $this->db->query("
                    SELECT `address`
                    FROM `accomodation`
                    WHERE `accomodation_id` = {$params['start']}
                    AND `lat` IS NULL
                    AND `lng` IS NULL
                ");

                if( $acc_sql->num_rows()>0 ){

                    $acc_row = $acc_sql->result();

                    // get geocode
                    $coor = $this->system_model->getGoogleMapCoordinates("{$acc_row->address}, {$country_name}");

                    // update agency lat/lng
                    $this->db->query("
                        UPDATE `accomodation`
                        SET
                            `lat` = '{$coor['lat']}',
                            `lng` = '{$coor['lng']}'
                        WHERE `accomodation_id` = {$params['start']}
                    ");

                }

            }

            // end point
            if($params['end']>0){

                // end
                // get accomodation address
                $acc_sql = $this->db->query("
                    SELECT `address`
                    FROM `accomodation`
                    WHERE `accomodation_id` = {$params['end']}
                    AND `lat` IS NULL
                    AND `lng` IS NULL
                ");

                if($acc_sql->num_rows()>0){

                    $acc_row = $acc_sql->result();

                    // get geocode
                    $coor = $this->system_model->getGoogleMapCoordinates("{$acc_row->address}, {$country_name}");

                    // update agency lat/lng
                    $this->db->query("
                        UPDATE `accomodation`
                        SET
                            `lat` = '{$coor['lat']}',
                            `lng` = '{$coor['lng']}'
                        WHERE `accomodation_id` = {$params['end']}
                    ");

                }


            }

            // update start and end point
            if( $params['tr_id'] > 0 && $params['start'] > 0 && $params['end'] > 0 ){

                $this->db->query("
                UPDATE `tech_run`
                SET `start` = {$params['start']},
                    `end` = {$params['end']}
                WHERE `tech_run_id` = {$params['tr_id']}
                ");

            }



        }

    }


    // old function: techRunAddAgencyKeys
    function add_agency_keys($params){

        $this->load->model('tech_model');

        // data
        $tr_id = $params['tr_id'];
        $keys_agency = $params['keys_agency'];
        $agency_addresses_id = $params['agency_addresses_id'];
        $tech_id = $params['tech_id'];
        $date = $params['date'];
        $country_id = $this->config->item('country');

        // get country data
        $country_params = array(
            'sel_query' => 'c.`country`',
            'country_id' => $country_id
        );
        $country_sql = $this->system_model->get_countries($country_params);
        $country_row = $country_sql->row();
        $country_name = $country_row->country;

        //get tech run rows
        $tr_sel = "COUNT(trr.`tech_run_rows_id`) AS trr_count";
        $tr_params = array(
            'sel_query' => $tr_sel
        );
        $trr_sql = $this->tech_model->getTechRunRows($tr_id, $country_id, $tr_params);
        $count = $trr_sql->row()->trr_count;

        $i = ($count)+2;

        // type of keys
        $keys_array = array(
            'Pick Up',
            'Drop Off'
        );

        // insert both pick up and drop off keys
        foreach($keys_array as $key_type){

            // check agency lat/lng
            $agen_sql = $this->db->query("
                SELECT
                    `address_1`,
                    `address_2`,
                    `address_3`,
                    `state`,
                    `postcode`
                FROM `agency`
                WHERE `agency_id` = {$keys_agency}
                AND `lat` IS NULL
                AND `lng` IS NULL
            ");

            if( $agen_sql->num_rows() > 0 ){

                $agen_row = $agen_sql->result();

                // get geocode
                $coor = $this->system_model->getGoogleMapCoordinates("{$agen_row->address_1} {$agen_row->address_2} {$agen_row->address_3} {$agen_row->state} {$agen_row->postcode}, {$country_name}");

                // update agency lat/lng
                $this->db->query("
                    UPDATE `agency`
                    SET
                        `lat` = '{$coor['lat']}',
                        `lng` = '{$coor['lng']}'
                    WHERE `agency_id` = {$keys_agency}
                ");

            }

            // insert keys
            $this->db->query("
                INSERT INTO
                `tech_run_keys`(
                    `assigned_tech`,
                    `date`,
                    `action`,
                    `agency_id`,
                    `sort_order`,
                    `agency_addresses_id`
                )
                VALUES(
                    {$tech_id},
                    '{$date}',
                    '{$key_type}',
                    '{$keys_agency}',
                    {$i},
                    {$agency_addresses_id}
                )
            ");
            $key_id = $this->db->insert_id();

            //  insert tech run rows
            $this->db->query("
                INSERT INTO
                `tech_run_rows` (
                    `tech_run_id`,
                    `row_id_type`,
                    `row_id`,
                    `sort_order_num`,
                    `created_date`,
                    `status`
                )
                VALUES (
                    {$tr_id},
                    'keys_id',
                    {$key_id},
                    {$i},
                    '".date('Y-m-d H:i:s')."',
                    1
                )
            ");

            $i++;

        }

    }

    // get property keys of agency
    public function get_agency_key_per_job($params){

        // update property key
        $sql = "
            SELECT *
            FROM `agency_keys`
            WHERE `job_id` = {$params['job_id']}
            AND `tech_id` = {$params['tech_id']}
            AND `date` = '{$params['date']}'
            AND `agency_id` = {$params['agency_id']}
        ";

        if( $params['display_query'] == 1 ){
            echo $sql;
        }
        return $this->db->query($sql);


    }


    // mark job as not completed
    public function mark_job_not_completed($params){

        $job_id = $params['job_id'];
        $tech_id = $params['tech_id'];

        $job_reason = $params['job_reason'];
        $reason_comment = $params['reason_comment'];

        $country_id = $this->config->item('country');

        if( $job_id > 0 ){

            // get jobs data
            $job_sql = $this->db->query("
            SELECT
                j.`assigned_tech`,
                j.`status`,
                j.`door_knock`,

                p.`property_id`,
                p.`address_1` AS p_address_1,
                p.`address_2` AS p_address_2,
                p.`address_3` AS p_address_3,
                p.`state` AS p_state,
                p.`postcode` AS p_postcode
            FROM `jobs` AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            WHERE `id` = {$job_id}
            ");
            $job_row = $job_sql->row();

            $property_id = $job_row->property_id;
            $job_status = $job_row->status;
            $p_address = "{$job_row->p_address_1} {$job_row->p_address_2} {$job_row->p_address_3}";

            // get job reason
            $jr_sql = $this->db->query("
                SELECT *
                FROM `job_reason`
                WHERE `job_reason_id` = {$job_reason}
            ");
            $jr_row = $jr_sql->row();
            $reason_name = $jr_row->name;

            // update job
            $this->db->query("
                UPDATE jobs
                SET
                    `status` = 'Pre Completion',
                    `job_reason_id` = ". ( ( $job_reason > 0 )?$job_reason:'NULL' ) .",
                    `job_reason_comment` = '{$reason_comment}',
                    `completed_timestamp` = '".date("Y-m-d H:i:s")."'
                WHERE `id` = '{$job_id}'
            ");

            // insert job log
            $log_title = 62; // Job Incomplete
            $log_details = "This job was marked incompleted due to: {$reason_name}, {$reason_comment}";
            $log_params = array(
                'title' => $log_title,
                'details' => $log_details,
                'display_in_vjd' => 1,
                'created_by_staff' => $this->session->staff_id,
                'job_id' => $job_id
            );
            $this->system_model->insert_log($log_params);

            $log_title = 63; // Job Update
            $log_details = "Job status updated from <b>{$job_status}</b> to <b>Pre Completion</b>";
            $log_params = array(
                'title' => $log_title,
                'details' => $log_details,
                'display_in_vjd' => 1,
                'created_by_staff' => $this->session->staff_id,
                'job_id' => $job_id
            );
            $this->system_model->insert_log($log_params);

            // insert to jobs_not_completed table
            $this->db->query("
                INSERT INTO
                jobs_not_completed (
                    `job_id`,
                    `reason_id`,
                    `reason_comment`,
                    `tech_id`,
                    `date_created`,
                    `door_knock`
                )
                VALUES (
                    '{$job_id}',
                    '{$job_reason}',
                    '{$reason_comment}',
                    '{$tech_id}',
                    '".date("Y-m-d H:i:s")."',
                    '{$job_row->door_knock}'
                )
            ");


            // Refused Entry
            if( $job_reason == 10 ){

                $return_as_string =  true;
                $email_body = null;

                // mail
                $view_data['p_address'] = $p_address;
                $view_data['property_id'] = $property_id;

                // content
                $email_body .= $this->load->view('emails/template/email_header', $view_data, $return_as_string);
                $email_body .= $this->load->view('emails/refused_entry_email', $view_data, $return_as_string);
                $email_body .= $this->load->view('emails/template/email_footer', $view_data, $return_as_string);

                // subject
                $subject = "Refused Entry";

                // get country data
                $country_params = array(
                    'sel_query' => 'c.agent_number, c.outgoing_email',
                    'country_id' => $country_id
                );
                $country_sql = $this->system_model->get_countries($country_params);
                $country_row = $country_sql->row();

                $from_email = $country_row->outgoing_email;
                $from_name = 'Smoke Alarm Testing Services';
                $to_email = $this->config->item('sats_no_show_email');
                //$to_email = 'vaultdweller123@gmail.com';

                // email settings
                $email_config = Array(
                    'mailtype' => 'html',
                    'charset' => 'utf-8'
                );
                $this->email->initialize($email_config);
                $this->email->clear(TRUE);
                $this->email->from($from_email, $from_name);
                $this->email->to($to_email);
                $this->email->cc($this->config->item('sats_reports_email'));
                $this->email->bcc($this->config->item('sats_cc_email'));

                $this->email->subject($subject);
                $this->email->message($email_body);

                // send email
                $this->email->send();

            }

        }


    }

    public function get_tech_run_keys_list($params){

        $tech_id = $params['tech_id'];
        $date = $params['date'];
        $agency_id = $params['agency_id'];

        $key_action = $params['key_action'];
        $key_action_no_space = str_replace(' ', '-', strtolower($key_action));

        $country_id = $this->config->item('country');
        $row_count = 0;

    ?>
        <table class="table main-table keys_table table-bordered">
            <thead>
                <tr>
                    <th class="paddress_th">Address</th>
                    <th class="key_num_th">Key Number</th>
                    <th><?php echo ( $key_action == 'Pick Up' )?'Approved By?':'Picked Up'; ?></th>                    
                    <th style="width: 7%">Verify</th>                    
                    <th class="keys_picked_up_th"><?php echo ( $key_action == 'Pick Up' )?'Keys Picked Up?':'Keys Returned?'; ?></th>
                </tr>
            </thead>

            <tbody>
            <?php
            $job_sql = $this->db->query("
                SELECT
                    j.`id` AS jid,
                    j.`service` AS j_service,
                    j.`key_access_details`,
                    j.`ts_completed`,
                    j.`status` AS jstatus,
                    j.`door_knock`,
                    j.`due_date`,
                    j.`property_vacant`,

                    p.`property_id`,
                    p.`address_1` AS p_address_1,
                    p.`address_2` AS p_address_2,
                    p.`address_3` AS p_address_3,
                    p.`state` AS p_state,
                    p.`postcode` AS p_postcode,
                    p.`key_number`,
                    p.`lat` AS p_lat,
                    p.`lng` AS p_lng,

                    a.`agency_id`,
                    a.`agency_name`,
                    a.`address_1` AS a_address_1,
                    a.`address_2` AS a_address_2,
                    a.`address_3` AS a_address_3,
                    a.`state` AS a_state,
                    a.`postcode` AS a_postcode,
                    a.`phone` AS a_phone,
                    a.`allow_dk`
                FROM jobs AS j
                LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id`
                LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id`
                LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
                WHERE p.`deleted` =0
                AND a.`status` = 'active'
                AND j.`del_job` = 0
                AND a.`country_id` = {$country_id}
                AND j.`key_access_required` = 1
                AND j.`assigned_tech` ={$tech_id}
                AND j.`date` = '{$date}'
                AND a.`agency_id` = {$agency_id}
            ");
            $job_id_arr = [];
            if($job_sql->num_rows() > 0){
                foreach($job_sql->result() as $index => $job_row){

                    $bg_color = null;

                    $job_id = $job_row->jid;
                    $p_address = "{$job_row->p_address_1} {$job_row->p_address_2}, {$job_row->p_address_3}";
                    $door_knock = $job_row->door_knock;

                    $agen_key_params = array(
                        'job_id' => $job_id,
                        'tech_id' => $tech_id,
                        'date' => $date,
                        'agency_id' => $agency_id,
                        'display_query' => 0
                    );
                    $agency_key_sql = $this->tech_run_model->get_agency_key_per_job($agen_key_params);
                    if( $agency_key_sql->num_rows() ){

                        $agency_key_row = $agency_key_sql->row();

                        $agency_keys_id = $agency_key_row->agency_keys_id;
                        $is_keys_picked_up = $agency_key_row->is_keys_picked_up;
                        $attend_property = $agency_key_row->attend_property;
                        $job_reason = $agency_key_row->job_reason;
                        $reason_comment = $agency_key_row->reason_comment;
                        $ak_created_date = $agency_key_row->created_date;
                        $is_keys_returned = $agency_key_row->is_keys_returned;
                        $not_returned_notes = $agency_key_row->not_returned_notes;

                    }else{

                        $is_keys_picked_up = null;
                        $job_reason = null;
                        $reason_comment = null;
                        $is_keys_returned = null;
                        $not_returned_notes = null;

                    }


                    if( $job_row->ts_completed == 1 ){
                        $bg_color = '#c2ffa7';
                    }

                    // check for not complete reason
                    $jnc_sql = $this->db->query("
                        SELECT COUNT(`jobs_not_completed_id`) AS jnc_count
                        FROM `jobs_not_completed`
                        WHERE `job_id` = {$job_row->jid}
                        AND DATE(`date_created`) = '{$date}'
                    ");
                    $jnc_count = $jnc_sql->row()->jnc_count;

                    if( $jnc_count > 0 ){
                        $bg_color = 'orange';
                    }


                ?>
                    <tr class="body_tr jalign_left prop_row" style="background-color:<?php echo $bg_color; ?>">
                        <td class="prop_address">
                            <?php //echo "{$p_address} ({$job_row->jstatus})"; ?>
                            <?php echo $p_address; ?>
                        </td>
                        <td class="prop_key_num">
                            <input type="text" class="form-control key_number" value="<?php echo $job_row->key_number; ?>" placeholder="Insert Key Number" />
                            <input type="hidden" class="job_id" value="<?php echo $job_row->jid; ?>" />
                        </td>
                        <td>
                            <?php
                            if( $key_action == 'Pick Up' ){
                                echo $job_row->key_access_details;
                            }else{
                                echo $this->system_model->isDateNotEmpty($ak_created_date)?date('H:i', strtotime($ak_created_date)):null;
                            }
                            ?>
                        </td>                        
                        <td>
                            <?php
                            if ( 

                                (
                                    $this->system_model->isDateNotEmpty($job_row->due_date) == false || 
                                    ( $this->system_model->isDateNotEmpty($job_row->due_date) && $job_row->due_date < date('Y-m-d')  )
                                ) &&
                                $job_row->property_vacant == 1 
                                
                            ) {
                            ?>
                                <span class="text-danger">Verify vacant</span>
                            <?php 
                            } 
                            ?>
                        </td>                        
                        <td class="is_keys_picked_up_td">

                            <?php
                            if( $key_action == 'Pick Up' ){ ?>

                                <div class="radio float-left mr-2">
                                    <input type="radio" name="is_keys_picked_up<?php echo $row_count."-".$key_action_no_space; ?>" id="is_keys_picked_up_yes<?php echo $row_count."-".$key_action_no_space; ?>" class="is_keys_picked_up is_keys_picked_up_yes inline-block" value="1" <?php echo ( $is_keys_picked_up == 1 )?'checked':null; ?> />
                                    <label class="inline-block" for="is_keys_picked_up_yes<?php echo $row_count."-".$key_action_no_space; ?>">Yes</label>
                                </div>

                                <div class="radio float-left">
                                    <input type="radio" name="is_keys_picked_up<?php echo $row_count."-".$key_action_no_space; ?>" id="is_keys_picked_up_no<?php echo $row_count."-".$key_action_no_space; ?>" class="is_keys_picked_up is_keys_picked_up_no inline-block" value="0" <?php echo ( $is_keys_picked_up == 0 && is_numeric($is_keys_picked_up) )?'checked':null; ?> />
                                    <label class="inline-block" for="is_keys_picked_up_no<?php echo $row_count."-".$key_action_no_space; ?>">No</label>
                                </div>


                                <div class="job_reason_div" style="display:<?php echo ( $is_keys_picked_up == 0 && is_numeric($is_keys_picked_up) )?'block':'none'; ?>;">


                                    <select id="attend_property" class="form-control attend_property">
                                        <option value="" disabled selected hidden>Attend Property?</option>
                                        <option value="1" <?php echo ( $attend_property == 1 )?'selected':null; ?>>Yes</option>
                                        <option value="0" <?php echo ( is_numeric($attend_property) && $attend_property == 0 )?'selected':null; ?>>No</option>
                                    </select>

                                    <div class="not_completed_div" style="display:<?php echo ( $attend_property == 0 && is_numeric($attend_property) )?'block':'none'; ?>;">
                                        <?php                                    
                                        // get not completed reason
                                        if( $door_knock != 1 ){  // Do not show NTTC on non-DK jobs            

                                            $ncr_sql_str = "
                                                SELECT `job_reason_id`, `name`
                                                FROM `job_reason`
                                                WHERE `job_reason_id` != 14
                                                ORDER BY `name`
                                            ";

                                        }else{ // show ALL   

                                            $ncr_sql_str = "
                                                SELECT `job_reason_id`, `name`
                                                FROM `job_reason`
                                                ORDER BY `name`
                                            ";
                                            
                                        }
                                        // job not completed reason
                                        $jr_sql = $this->db->query($ncr_sql_str);
                                        ?>
                                        <select id="job_reason" class="form-control job_reason">
                                            <option value="">----</option>
                                            <?php
                                            foreach( $jr_sql->result() as $jr ){
                                            ?>
                                                <option value="<?php echo $jr->job_reason_id; ?>" <?php echo ( $jr->job_reason_id == $job_reason )?'selected':null ?>><?php echo $jr->name; ?></option>
                                            <?php
                                            }
                                            ?>
                                        </select>

                                        <!-- comment -->
                                        <div><input type="text" name="reason_comment" class="form-control reason_comment" placeholder="Comment" value="<?php echo ( $reason_comment != '' )?$reason_comment:null ?>" /></div>
                                    </div>

                                </div>

                            <?php
                            }else{ // drop off 

                                if( $is_keys_picked_up == true ){ 
                            ?>

                                <div class="radio float-left mr-2">
                                    <input type="radio" name="is_keys_returned<?php echo $row_count."-".$key_action_no_space; ?>" id="is_keys_returned_yes<?php echo $row_count."-".$key_action_no_space; ?>" class="is_keys_returned is_keys_returned_yes inline-block" value="1" <?php echo ( $is_keys_returned == 1 )?'checked':null; ?> />
                                    <label class="inline-block" for="is_keys_returned_yes<?php echo $row_count."-".$key_action_no_space; ?>">Yes</label>
                                </div>

                                <div class="radio float-left">
                                    <input type="radio" name="is_keys_returned<?php echo $row_count."-".$key_action_no_space; ?>" id="is_keys_returned_no<?php echo $row_count."-".$key_action_no_space; ?>" class="is_keys_returned is_keys_returned_no inline-block" value="0" <?php echo ( $is_keys_returned == 0 && is_numeric($is_keys_returned) )?'checked':null; ?> />
                                    <label class="inline-block" for="is_keys_returned_no<?php echo $row_count."-".$key_action_no_space; ?>">Other</label>
                                </div>


                                <div class="keys_not_returned_div" style="display:<?php echo ( $is_keys_returned == 0 && is_numeric($is_keys_returned) )?'block':'none'; ?>;">

                                    <!-- key not returned note -->
                                    <div><input type="text" name="not_returned_notes" class="form-control not_returned_notes" placeholder="Comment" value="<?php echo ( $not_returned_notes != '' )?$not_returned_notes:null ?>" /></div>

                                </div>                                

                            <?php
                                }else{
                                    echo "N/A";
                                } 
                            ?>

                                <input type="hidden" class="is_keys_picked_up" value="<?php echo $is_keys_picked_up; ?>" />

                            <?php    
                            }
                            ?>

                            <input type="hidden" class="agency_keys_id" value="<?php echo $agency_keys_id; ?>" />
                        </td>
                    </tr>
                <?php

                $job_id_arr[] = $job_id;

                $row_count++;

                }
            }else{ ?>
                <tr><td colspan='4'>No Data</td></tr>
            <?php
            }
            ?>


            <?php
            // get rebooked jobs
            $exclude_jobs_above = null;
            if( count($job_id_arr) > 0 ){

                $job_id_imp = implode(",",$job_id_arr);
                $exclude_jobs_above = "AND ak.job_id NOT IN({$job_id_imp})";

            }

            $job_sql_str = "
                SELECT
                    ak.`is_keys_picked_up`,
                    ak.`attend_property`,
                    ak.`job_reason` AS ak_job_reason,
                    ak.`reason_comment` AS ak_reason_comment,
                    ak.`created_date` AS ak_created_date,

                    j.`id` AS jid,
                    j.`service` AS j_service,
                    j.`key_access_details`,
                    j.`ts_completed`,
                    j.`status` AS jstatus,
                    j.`door_knock`,
                    j.`due_date`,
                    j.`property_vacant`,

                    p.`property_id`,
                    p.`address_1` AS p_address_1,
                    p.`address_2` AS p_address_2,
                    p.`address_3` AS p_address_3,
                    p.`state` AS p_state,
                    p.`postcode` AS p_postcode,
                    p.`key_number`,
                    p.`lat` AS p_lat,
                    p.`lng` AS p_lng,

                    a.`agency_id`,
                    a.`agency_name`,
                    a.`address_1` AS a_address_1,
                    a.`address_2` AS a_address_2,
                    a.`address_3` AS a_address_3,
                    a.`state` AS a_state,
                    a.`postcode` AS a_postcode,
                    a.`phone` AS a_phone,
                    a.`allow_dk`
                FROM `agency_keys` AS ak
                LEFT JOIN jobs AS j ON ak.`job_id` = j.`id`
                LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id`
                LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id`
                LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
                WHERE ak.`tech_id` ={$tech_id}
                AND ak.`date` = '{$date}'
                AND ak.`agency_id` = {$agency_id}
                {$exclude_jobs_above}
            ";
            $job_sql = $this->db->query($job_sql_str);

            if($job_sql->num_rows() > 0){
                foreach($job_sql->result() as $index => $job_row){

                    $bg_color = null;

                    $job_id = $job_row->jid;
                    $p_address = "{$job_row->p_address_1} {$job_row->p_address_2}, {$job_row->p_address_3}";
                    $door_knock = $job_row->door_knock;

                    $agency_keys_id = $job_row->agency_keys_id;
                    $is_keys_picked_up = $job_row->is_keys_picked_up;
                    $attend_property = $job_row->attend_property;
                    $job_reason = $job_row->ak_job_reason;
                    $reason_comment = $job_row->ak_reason_comment;
                    $ak_created_date = $job_row->ak_created_date;
                    $is_keys_returned = $job_row->is_keys_returned;
                    $not_returned_notes = $job_row->not_returned_notes;


                    if( $job_row->ts_completed == 1 ){
                        $bg_color = '#c2ffa7';
                    }

                    // check for not complete reason
                    $jnc_sql = $this->db->query("
                        SELECT COUNT(`jobs_not_completed_id`) AS jnc_count
                        FROM `jobs_not_completed`
                        WHERE `job_id` = {$job_row->jid}
                        AND DATE(`date_created`) = '{$date}'
                    ");
                    $jnc_count = $jnc_sql->row()->jnc_count;

                    if( $jnc_count > 0 ){
                        $bg_color = 'orange';
                    }


                ?>
                    <tr class="body_tr jalign_left prop_row" style="background-color:<?php echo $bg_color; ?>">
                        <td class="prop_address">
                            <?php //echo "{$p_address} ({$job_row->jstatus})"; ?>
                            <?php echo $p_address; ?>
                        </td>
                        <td class="prop_key_num">
                            <input type="text" class="form-control key_number" value="<?php echo $job_row->key_number; ?>" />
                            <input type="hidden" class="job_id" value="<?php echo $job_row->jid; ?>" />
                        </td>
                        <td>
                            <?php
                            if( $key_action == 'Pick Up' ){
                                echo $job_row->key_access_details;
                            }else{
                                echo $this->system_model->isDateNotEmpty($ak_created_date)?date('H:i', strtotime($ak_created_date)):null;
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if ( 

                                (
                                    $this->system_model->isDateNotEmpty($job_row->due_date) == false || 
                                    ( $this->system_model->isDateNotEmpty($job_row->due_date) && $job_row->due_date < date('Y-m-d')  )
                                ) &&
                                $job_row->property_vacant == 1 
                                
                            ) {
                            ?>
                                <span class="text-danger">Verify vacant</span>
                            <?php 
                            } 
                            ?>
                        </td>
                        <td class="is_keys_picked_up_td">

                            <?php
                            if( $key_action == 'Pick Up' ){ ?>

                                <div class="radio float-left mr-2">
                                    <input type="radio" name="is_keys_picked_up<?php echo $row_count."-".$key_action_no_space; ?>" id="is_keys_picked_up_yes<?php echo $row_count."-".$key_action_no_space; ?>" class="is_keys_picked_up is_keys_picked_up_yes inline-block" value="1" <?php echo ( $is_keys_picked_up == 1 )?'checked':null; ?> />
                                    <label class="inline-block" for="is_keys_picked_up_yes<?php echo $row_count."-".$key_action_no_space; ?>">Yes</label>
                                </div>

                                <div class="radio float-left">
                                    <input type="radio" name="is_keys_picked_up<?php echo $row_count."-".$key_action_no_space; ?>" id="is_keys_picked_up_no<?php echo $row_count."-".$key_action_no_space; ?>" class="is_keys_picked_up is_keys_picked_up_no inline-block" value="0" <?php echo ( $is_keys_picked_up == 0 && is_numeric($is_keys_picked_up) )?'checked':null; ?> />
                                    <label class="inline-block" for="is_keys_picked_up_no<?php echo $row_count."-".$key_action_no_space; ?>">No</label>
                                </div>


                                <div class="job_reason_div" style="display:<?php echo ( $is_keys_picked_up == 0 && is_numeric($is_keys_picked_up) )?'block':'none'; ?>;">


                                    <select id="attend_property" class="form-control attend_property">
                                        <option value="" disabled selected hidden>Attend Property?</option>
                                        <option value="1" <?php echo ( $attend_property == 1 )?'selected':null; ?>>Yes</option>
                                        <option value="0" <?php echo ( is_numeric($attend_property) && $attend_property == 0 )?'selected':null; ?>>No</option>
                                    </select>

                                    <div class="not_completed_div" style="display:<?php echo ( $attend_property == 0 && is_numeric($attend_property) )?'block':'none'; ?>;">
                                        <?php
                                        // get not completed reason
                                        if( $door_knock != 1 ){  // Do not show NTTC on non-DK jobs            

                                            $ncr_sql_str = "
                                                SELECT `job_reason_id`, `name`
                                                FROM `job_reason`
                                                WHERE `job_reason_id` != 14
                                                ORDER BY `name`
                                            ";

                                        }else{ // show ALL   

                                            $ncr_sql_str = "
                                                SELECT `job_reason_id`, `name`
                                                FROM `job_reason`
                                                ORDER BY `name`
                                            ";
                                            
                                        }
                                        // job not completed reason
                                        $jr_sql = $this->db->query($ncr_sql_str);
                                        ?>
                                        <select id="job_reason" class="form-control job_reason">
                                            <option value="">----</option>
                                            <?php
                                            foreach( $jr_sql->result() as $jr ){
                                            ?>
                                                <option value="<?php echo $jr->job_reason_id; ?>" <?php echo ( $jr->job_reason_id == $job_reason )?'selected':null ?>><?php echo $jr->name; ?></option>
                                            <?php
                                            }
                                            ?>
                                        </select>

                                        <!-- comment -->
                                        <div><input type="text" name="reason_comment" class="form-control reason_comment" placeholder="Comment" value="<?php echo ( $reason_comment != '' )?$reason_comment:null ?>" /></div>
                                    </div>

                                </div>

                            <?php
                            }else{ // drop off 
                            
                                if( $is_keys_picked_up == true ){

                            ?>

                                <div class="radio float-left mr-2">
                                    <input type="radio" name="is_keys_returned<?php echo $row_count."-".$key_action_no_space; ?>" id="is_keys_returned_yes<?php echo $row_count."-".$key_action_no_space; ?>" class="is_keys_returned is_keys_returned_yes inline-block" value="1" <?php echo ( $is_keys_returned == 1 )?'checked':null; ?> />
                                    <label class="inline-block" for="is_keys_returned_yes<?php echo $row_count."-".$key_action_no_space; ?>">Yes</label>
                                </div>

                                <div class="radio float-left">
                                    <input type="radio" name="is_keys_returned<?php echo $row_count."-".$key_action_no_space; ?>" id="is_keys_returned_no<?php echo $row_count."-".$key_action_no_space; ?>" class="is_keys_returned is_keys_returned_no inline-block" value="0" <?php echo ( $is_keys_returned == 0 && is_numeric($is_keys_returned) )?'checked':null; ?> />
                                    <label class="inline-block" for="is_keys_returned_no<?php echo $row_count."-".$key_action_no_space; ?>">Other</label>
                                </div>


                                <div class="keys_not_returned_div" style="display:<?php echo ( $is_keys_returned == 0 && is_numeric($is_keys_returned) )?'block':'none'; ?>;">

                                    <!-- key not returned note -->
                                    <div><input type="text" name="not_returned_notes" class="form-control not_returned_notes" placeholder="Comment" value="<?php echo ( $not_returned_notes != '' )?$not_returned_notes:null ?>" /></div>

                                </div>                                

                            <?php
                                }else{
                                    echo "N/A";
                                } 
                            ?>

                                <input type="hidden" class="is_keys_picked_up" value="<?php echo $is_keys_picked_up; ?>" />
                                
                            <?php
                            }
                            ?>

                            <input type="hidden" class="agency_keys_id" value="<?php echo $agency_keys_id; ?>" />
                        </td>
                    </tr>
                <?php
                $row_count++;
                }
            }
            ?>
            </tbody>
        </table>

    <?php
    }

    public function getJobsNotCompleted($jobIds, $date = null) {

        $jobIdsString = implode(',', $jobIds);

        $dateFilter = "";
        if ($date != null) {
            $dateFilter = "
                AND DATE(`date_created`) = '{$date}'
            ";
        }

        $sql = "
            SELECT `job_id`, COUNT(`jobs_not_completed_id`) AS jnc_count
            FROM `jobs_not_completed`
            WHERE `job_id` IN ({$jobIdsString})
            {$dateFilter}
            GROUP BY `job_id`
        ";

        $jobsNotCompletedResult = $this->db->query($sql);

        return $jobsNotCompletedResult->result();
    }

    public function getTechRunKeyList($params) {
        $techId = $params['tech_id'];
        $date = $params['date'];
        $agencyId = $params['agency_id'];

        $keyAction = $params['key_action'];
        $keyActionNoSpace = str_replace(' ', '-', strtolower($keyAction));

        $countryId = $this->config->item('country');
        $rowCount = 0;

        $jobsResult = $this->db->query("
            SELECT
                j.`id` AS jid,
                j.`service` AS j_service,
                j.`key_access_details`,
                j.`ts_completed`,
                j.`status` AS jstatus,

                p.`property_id`,
                p.`address_1` AS p_address_1,
                p.`address_2` AS p_address_2,
                p.`address_3` AS p_address_3,
                p.`state` AS p_state,
                p.`postcode` AS p_postcode,
                p.`key_number`,
                p.`lat` AS p_lat,
                p.`lng` AS p_lng,

                a.`agency_id`,
                a.`agency_name`,
                a.`address_1` AS a_address_1,
                a.`address_2` AS a_address_2,
                a.`address_3` AS a_address_3,
                a.`state` AS a_state,
                a.`postcode` AS a_postcode,
                a.`phone` AS a_phone,
                a.`allow_dk`
            FROM jobs AS j
            LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
            WHERE p.`deleted` =0
            AND a.`status` = 'active'
            AND j.`del_job` = 0
            AND a.`country_id` = {$countryId}
            AND j.`key_access_required` = 1
            AND j.`assigned_tech` = {$techId}
            AND j.`date` = '{$date}'
            AND a.`agency_id` = {$agencyId}
        ");


        $jobsAssoc = [];

        if ($jobsResult->num_rows() > 0) {

            $jobs = $jobsResult->result_array();
            for($index = 0; $index < count($jobs); $index++) {
                $bgColor = null;

                $jobId = $jobs[$index]['jid'];

                $jobs[$index]['agency_key'] = [
                    'agency_keys_id' => null,
                    'is_keys_picked_up' => null,
                    'attend_property' => null,
                    'job_reason' => null,
                    'reason_comment' => null,
                    'created_date' => null,
                    'is_keys_returned' => null,
                    'not_returned_notes' => null,
                ];
                $jobs[$index]['jnc_count'] = 0;

                $jobsAssoc[$jobId] = &$jobs[$index];
            }

            $jobIds = array_keys($jobsAssoc);
            $jobIdsString = implode(',', $jobIds);

            $sql = "
                SELECT *
                FROM `agency_keys`
                WHERE
                `job_id` IN ({$jobIdsString})
                AND `tech_id` = {$params['tech_id']}
                AND `date` = '{$params['date']}'
                AND `agency_id` = {$params['agency_id']}
                GROUP BY `job_id`
            ";

            $agencyKeysResult = $this->db->query($sql);

            foreach($agencyKeysResult->result() as $agencyKey) {
                $jobsAssoc[$agencyKey->job_id]['agency_key'] = [
                    'agency_keys_id' => $agencyKey->agency_keys_id,
                    'is_keys_picked_up' => $agencyKey->is_keys_picked_up,
                    'attend_property' => $agencyKey->attend_property,
                    'job_reason' => $agencyKey->job_reason,
                    'reason_comment' => $agencyKey->reason_comment,
                    'created_date' => $agencyKey->created_date,
                    'is_keys_returned' => $agencyKey->is_keys_returned,
                    'not_returned_notes' => $agencyKey->not_returned_notes,
                ];
            }

            $jobsNotCompleted = $this->getJobsNotCompleted($jobIds, $date);

            foreach($jobsNotCompleted as $notCompletedJob) {
                $jobsAssoc[$notCompletedJob->job_id]['jnc_count'] = $notCompletedJob->jnc_count;
            }
        }

        if (!empty($jobsAssoc)) {
            $jobIdsString = implode(',', array_keys($jobsAssoc));

            $excludedJobsClause = "AND ak.job_id NOT IN ({$jobIdsString})";
        }
        else {
            $excludedJobsClause = "";
        }

        $otherJobsResult = $this->db->query("
            SELECT
                ak.`is_keys_picked_up`,
                ak.`attend_property`,
                ak.`job_reason` AS ak_job_reason,
                ak.`reason_comment` AS ak_reason_comment,
                ak.`created_date` AS ak_created_date,

                j.`id` AS jid,
                j.`service` AS j_service,
                j.`key_access_details`,
                j.`ts_completed`,
                j.`status` AS jstatus,

                p.`property_id`,
                p.`address_1` AS p_address_1,
                p.`address_2` AS p_address_2,
                p.`address_3` AS p_address_3,
                p.`state` AS p_state,
                p.`postcode` AS p_postcode,
                p.`key_number`,
                p.`lat` AS p_lat,
                p.`lng` AS p_lng,

                a.`agency_id`,
                a.`agency_name`,
                a.`address_1` AS a_address_1,
                a.`address_2` AS a_address_2,
                a.`address_3` AS a_address_3,
                a.`state` AS a_state,
                a.`postcode` AS a_postcode,
                a.`phone` AS a_phone,
                a.`allow_dk`
            FROM `agency_keys` AS ak
            LEFT JOIN jobs AS j ON ak.`job_id` = j.`id`
            LEFT JOIN  `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN  `agency` AS a ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
            WHERE ak.`tech_id` ={$techId}
            AND ak.`date` = '{$date}'
            AND ak.`agency_id` = {$agencyId}
            {$excludedJobsClause}
        ");

        $otherJobsAssoc = [];

        if ($otherJobsResult->num_rows() > 0) {
            $otherJobs = $otherJobsResult->result_array();
            for ($index = 0; $index < count($otherJobs); $index++) {

                $jobId = $jobs[$index]['jid'];

                $jobs[$index]['jnc_count'] = 0;

                $otherJobsAssoc[$jobId] = &$jobs[$index];
            }


            $jobsNotCompleted = $this->getJobsNotCompleted($jobIds, $date);

            foreach($jobsNotCompleted as $notCompletedJob) {
                $otherJobsAssoc[$notCompletedJob->job_id]['jnc_count'] = $notCompletedJob->jnc_count;
            }
        }

        return [
            'jobs' => array_values($jobsAssoc),
            'other_jobs' => array_values($otherJobsAssoc),
        ];
    }


    public function issue_en($tr_params){

        $this->load->model('properties_model');
        $this->load->model('sms_model');
        $this->load->model('jobs_model');
        $this->load->model('/inc/pdf_template');

        // parameter data
        $trr_id_arr = $tr_params->trr_id_arr;
        $str_tech = $tr_params->str_tech;
        $str_tech_name = $tr_params->str_tech_name;
        $str_date = $tr_params->str_date;
        $en_time_arr = $tr_params->en_time_arr;        
        
        $today_full = date('Y-m-d H:i:s');

        $logged_user = $this->session->staff_id;
        $booked_with = 'Agent';
                
        $en_date = $str_date; 
        $country_id = $this->config->item('country');  

         // get country data
         $country_params = array(
            'sel_query' => 'c.agent_number, c.outgoing_email',
            'country_id' => $country_id
        );
        $country_sql = $this->system_model->get_countries($country_params);
        $country_row = $country_sql->row();       

        $view_data['agency_portal_link'] = $this->config->item('agencyci_link');
        $view_data['outgoing_email'] = $country_row->outgoing_email;
        $view_data['agent_number'] = $country_row->agent_number;                      
        

        foreach( $trr_id_arr as $index => $trr_id ){

            $combined_logs_arr = []; // clear

            // clear          
            $email_body = null;  
            $sms_sent = false;

            $en_time = $en_time_arr[$index];

            // get jobs data
            $trr_sql = $this->db->query("
            SELECT 
                j.`id` AS jid,
                j.`service` AS jservice, 
                j.`job_type`,
                j.`date` AS jdate, 
                
                p.`property_id`,
                p.`address_1` AS p_address_1, 
                p.`address_2` AS p_address_2, 
                p.`address_3` AS p_address_3, 
                p.`state` AS p_state, 
                p.`postcode` AS p_postcode, 
                p.pm_id_new,
                
                a.`agency_id`,
                a.`agency_name`,
                a.`agency_emails`,
                a.`en_to_pm`,
                a.`send_en_to_agency`
            FROM `tech_run_rows` AS trr
            LEFT JOIN `tech_run` AS tr ON trr.`tech_run_id` =  tr.`tech_run_id`
            LEFT JOIN `jobs` AS j ON ( trr.`row_id` = j.`id` AND trr.`row_id_type` = 'job_id' )  
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
            WHERE trr.`tech_run_rows_id` = {$trr_id}
            ");

            $trr_row = $trr_sql->row();

            // query data
            $job_id = $trr_row->jid;
            $job_type = $trr_row->job_type;
            $property_id = $trr_row->property_id;
            $agency_id = $trr_row->agency_id;            
            $p_address = "{$trr_row->p_address_1} {$trr_row->p_address_2} {$trr_row->p_address_3}, {$trr_row->p_state} {$trr_row->p_postcode}";        
            $agency_name = $trr_row->agency_name;
            $job_date = date('Y-m-d',strtotime($trr_row->jdate));
            $job_date_dmy = date('d/m/Y',strtotime($trr_row->jdate));
            $agency_emails = $trr_row->agency_emails;
            $pm_id_new = $trr_row->pm_id_new;
            $en_to_pm = $trr_row->en_to_pm;
            $send_en_to_agency = $trr_row->send_en_to_agency;

            // get tenants 
            $sel_query = "
                pt.`property_tenant_id`,
                pt.`tenant_firstname`,
                pt.`tenant_lastname`,
                pt.`tenant_mobile`,
                pt.`tenant_email`
            ";
            $params = array(
                'sel_query' => $sel_query,
                'property_id' => $property_id,
                'pt_active' => 1,
                'display_query' => 0
            );
            $pt_sql = $this->properties_model->get_property_tenants($params);

            // clear tenants
            $tenant_mobile_arr = [];
            $tenant_names_arr = [];
            $tenant_email_arr = [];            

            foreach( $pt_sql->result() as $pt_row ){

                // tenant names
                if( $pt_row->tenant_firstname != '' ){

                    $tenant_names_arr[] = $pt_row->tenant_firstname; 

                }                 
                
                // mobile
                if( $pt_row->tenant_mobile != '' ){

                    $tenant_mobile_arr[] = $this->sms_model->formatToInternationNumber($pt_row->tenant_mobile); // format number  

                }         
                
                // email
                if( $pt_row->tenant_email != '' ){

                    if( filter_var(trim($pt_row->tenant_email), FILTER_VALIDATE_EMAIL) ){ // validate email
                        $tenant_email_arr[] = $pt_row->tenant_email;
                    }

                }
                
                
            }   
            
            // get PM
            $pm_id = $pm_id_new; // pm id   
            $pm_email = null;
            $pm_sql = $this->db->query("
                SELECT `email`
                FROM `agency_user_accounts`
                WHERE `agency_user_account_id` = {$pm_id}    
                AND `agency_id` = {$agency_id}    
            ");
            if ( $pm_sql->num_rows() > 0) {

                // sanitize email            
                $pm_row = $pm_sql->row();                      
                if( filter_var(trim($pm_row->email), FILTER_VALIDATE_EMAIL) ) {
                    $pm_email = $pm_row->email;                                 
                }

            }    
        
            // agency email
            $agency_emails_arr = []; // clear
            $agency_emails_imp = null;

            $agency_emails_imp = explode("\n", trim($agency_emails));
            foreach ($agency_emails_imp as $agency_email) {            
                if (filter_var(trim($agency_email), FILTER_VALIDATE_EMAIL)) {                
                    $agency_emails_arr[] = $agency_email;                
                }
            }

            $en_bcc_emails = []; // clear
            if( $en_to_pm == 1 ){ // send to PM - YEs

                // PM exist, only send to PM
                if( $pm_email != '' ){ 

                    $en_bcc_emails[] = $pm_email;

                }else{ // PM doesnt exist, send to agency
                    
                    if( count($agency_emails_arr) > 0 ){
                        $en_bcc_emails = $agency_emails_arr;   
                    }
                                    
                }

            }else{ // send to PM - NO

                if ( $send_en_to_agency == 1 ) {
                    if( count($agency_emails_arr) > 0 ){
                        $en_bcc_emails = $agency_emails_arr;   
                    }
                }

            }   
            
            $proceed_en_operation = true; // defaul to run EN
            if( $country_id == 2 && count($tenant_email_arr) == 0 ){ // on NZ dont run EN if no tenant emails
                $proceed_en_operation = false;
            }

            if( $proceed_en_operation == true ){
            
                // update job, this update needs to happen before sending those EN pdf's
                $update_data = array(
                    'assigned_tech' => $str_tech,
                    'date' => $en_date,
                    'time_of_day' => $en_time,
                    'job_entry_notice' => 1,
                    'key_access_required' => 1,
                    'key_access_details' => 'Entry Notice',
                    'tech_notes' => 'EN - Keys',
                    'booked_by' => $logged_user,
                    'booked_with' => $booked_with,
                    'en_date_issued' => $today_full                
                );                
                $this->db->where('id', $job_id);
                $this->db->update('jobs', $update_data);            

                // email settings
                $email_config = Array(
                    'mailtype' => 'html',
                    'charset' => 'utf-8'
                );
                $this->email->initialize($email_config);   
                $this->email->clear(TRUE);         
                $this->email->from($country_row->outgoing_email, 'Smoke Alarm Testing Services');                
                $this->email->to($tenant_email_arr);     
                if( count($en_bcc_emails) > 0 ){
                    $this->email->bcc($en_bcc_emails);  
                }                                                         
                $this->email->subject("Entry Notice - {$p_address}");
            
                // append tenant names
                $tenants_str = null;
                $num_tenants = count($tenant_names_arr);

                for( $z=0; $z<$num_tenants; $z++ ){

                    if($z==0){
                        $tenants_txt_sep = "";
                    }else if($z==($num_tenants-1)){
                        $tenants_txt_sep = " and ";
                    }else{
                        $tenants_txt_sep = ", ";
                    }
                    $tenants_str .= "{$tenants_txt_sep}{$tenant_names_arr[$z]}";
                    
                }

                // EN email content
                $html_content  = "<p>Dear {$tenants_str},</p><br />
                <p>
                    Please find the attached entry notice for {$p_address} on {$job_date_dmy}. 
                    We will collect the keys from {$agency_name} to complete the service. Please contact us with any enquiries you may have.
                </p>
                <p>
                    <strong>Property Address</strong><br />
                    {$p_address}
                </p>
                <p>
                    Kind Regards,<br />
                    SATS Team
                </p>";

                $return_as_string =  true;

                $view_data['paddress'] = $p_address;
                $view_data['agency_name'] = $agency_name;

                // content
                $email_body = ''; // clear
                $email_body .= $this->load->view('emails/template/email_header', $view_data, $return_as_string);
                $email_body .= nl2br($html_content);
                $email_body .= $this->load->view('emails/template/email_footer', $view_data, $return_as_string);                      
                
                $this->email->message($email_body);

                // attach EN pdf
                $pdf_name = 'en_pdf_'.rand().date('YmdHis').'.pdf';

                $en_pdf_params = array(
                    'job_id' => $job_id,
                    'output' => 'S'
                );
                $en_pdf = $this->pdf_template->entry_notice_switch($en_pdf_params);                    
                $this->email->attach($en_pdf, 'attachment',  $pdf_name, 'application/pdf');

                $email_sent = false;
                if( $this->email->send() ){ // send email

                    $email_sent = true;

                    if ( count($tenant_names_arr) > 0 ){

                        // insert log
                        $combined_logs_arr[] = "Entry Notice emailed to <strong>Tenants</strong>";                        

                    }

                    if ( $send_en_to_agency == 1 ) {

                        // insert log
                        $combined_logs_arr[] = "Entry Notice emailed to <strong>{$agency_name}</strong>";
                        
                    }  
                    
                    // update job
                    $update_data = array(
                        'entry_notice_emailed' => $today_full
                    );                    
                    $this->db->where('id', $job_id);
                    $this->db->update('jobs', $update_data);

                }

                // SMS
                if( $job_type == "IC Upgrade" && $country_id == 1 ){
                    $sms_type = 47; // Entry Notice (SMS EN) IC UPgrade
                }else{
                    $sms_type = 10; // Entry Notice (SMS EN)
                }

                // get template content                      
                $sel_query = "sms_api_type_id, body";
                $params = array(
                    'sel_query' => $sel_query,
                    'active' => 1,
                    'sms_api_type_id' => $sms_type,
                    'display_query' => 0
                );
                $sql = $this->sms_model->getSmsTemplates($params);
                $row = $sql->row();
                $unparsed_template = $row->body;         

                // parse tags
                $sms_params = array(
                    'job_id' => $job_id,
                    'unparsed_template' => $unparsed_template
                );

                $parsed_template_body = $this->sms_model->parseTags($sms_params);   
                
                $sms_sent = false;
                foreach( $tenant_mobile_arr as $tenant_mobile ){

                    // send SMS
                    $sms_params = array(
                        'sms_msg' => $parsed_template_body,
                        'mobile' => $tenant_mobile
                    );
                    $sms_json = $this->sms_model->sendSMS($sms_params);

                    // save SMS data on database
                    $sms_params = array(
                        'sms_json' => $sms_json,
                        'job_id' => $job_id,
                        'message' => $parsed_template_body,
                        'mobile' => $tenant_mobile,
                        'sent_by' => $logged_user,
                        'sms_type' => $sms_type,
                    );
                    $this->sms_model->captureSmsData($sms_params);
                    $sms_sent = true;                                  
                    
                }
                
                if( count($tenant_mobile_arr) > 0 && $sms_sent == true ){

                    $tenant_name_imp = implode(', ', $tenant_names_arr);
                    
                    // insert log
                    $indiv_logs_str = "Reminder SMS to {$tenant_name_imp} <strong>{$parsed_template_body}</strong>";
                    $log_params = array(
                        'title' => 63, // Job Update
                        'details' => $indiv_logs_str,
                        'display_in_vjd' => 1,
                        'created_by_staff' => $logged_user,
                        'job_id' => $job_id
                    );
                    $this->system_model->insert_log($log_params);
            
                    // update job
                    $update_data = array(
                        'sms_sent' => $today_full,                
                    );                    
                    $this->db->where('id', $job_id);
                    $this->db->update('jobs', $update_data);

                }
                
                // insert EN Date Issued either email or SMS sent
                if( $email_sent == true || $sms_sent == true ){   
                    
                    // update job as booked
                    $update_data = array(
                        'status' => 'Booked'             
                    );                
                    $this->db->where('id', $job_id);
                    $this->db->update('jobs', $update_data);        

                    // insert log
                    $combined_logs_arr[] = "EN Booked via Key Access with <strong>{$booked_with}</strong> for <strong>" . ( $this->system_model->isDateNotEmpty($en_date) ? date("d/m/Y", strtotime($en_date)) : null ) . "</strong> @ <strong>{$en_time}</strong>. Technician <strong>{$str_tech_name}</strong>";                    

                }else{

                    // reset job updates if neither SMS or email EN is sent
                    $update_data = array(
                        'assigned_tech' => null,
                        'date' => null,
                        'time_of_day' => null,
                        'job_entry_notice' => 0,
                        'key_access_required' => 0,
                        'key_access_details' => null,
                        'tech_notes' => null,
                        'booked_by' => null,
                        'booked_with' => null,
                        'en_date_issued' => null             
                    );                
                    $this->db->where('id', $job_id);
                    $this->db->update('jobs', $update_data);        

                }

                if( count($combined_logs_arr) > 0 ){

                    // combined logs separator
                    $tenant_name_imp = implode('; ', $combined_logs_arr);

                    $log_params = array(
                        'title' => 63, // Job Update
                        'details' => $tenant_name_imp,
                        'display_in_vjd' => 1,
                        'created_by_staff' => $logged_user,
                        'job_id' => $job_id
                    );
                    $this->system_model->insert_log($log_params);

                }                

            }

        }

    }


}
?>