
<style>
.is_keys_picked_up_radio{
    margin-right: 5px;
}
.agency_staff{
    margin-left: 15px;
}
button.link_keys{
    width: 87px;
}

.job_reason{
    display: none;
    width: auto;
}
.agency_staff_div,
.key_num_hid{
    display: none;
}
.paddress_th{
    width: 40% !important;
}
.keys_picked_up_th{
    width: 417px !important;
}
.is_keys_picked_up_yes:checked + label::after,
.is_keys_returned_yes:checked + label::after{
	background: #00e600 !important;	
}
.is_keys_picked_up_no:checked + label::after,
.is_keys_returned_no:checked + label::after{
	background: #ff0000 !important;	
}
.keys_table {
    margin: 0;
}
.key_number_th{
    width: 116px;
}
.pick_up_time_th{
    width: 96px;
}
.drop_off_time_th{
    width: 110px;
}
</style>

<div class="box-typical box-typical-padding">

    <?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => "{$uri}/?tr_id={$tr_id}"
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);

?>
    
	
    

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">

                <?php
                foreach( $agency_sql->result() as $a_row ){ 
                    
                    $agency_id = $a_row->agency_id;
                    $country_id = $this->config->item('country');
                    
                    ?>

                    <div>
                        <h2 class="float-left"><?php echo str_replace('*do not use*','',$a_row->agency_name); ?></h2>                                          
                    </div>
                             
                        <table class="table main-table table-bordered">
                            <thead>
                                <tr>	
                                    <th>Address</th>          
                                    <th class="key_number_th">Key Number</th>
                                    <th>Approved By</th>                                    
                                    <th>Verify</th>                                    
                                    <th class="pick_up_time_th">Pickup Time</th>  
                                    <th>Picked Up</th>
                                    <th class="drop_off_time_th">Drop Off Time</th>  
                                    <th>Returned</th>                                                       	
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
                                    $drop_off_ts = $agency_key_row->drop_off_ts;
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
                                    <!-- address -->                                						
                                    <td class="prop_address">                                        
                                        <?php echo $p_address; ?>                                                        
                                    </td>
                                    <!-- key number -->
                                    <td class="prop_key_num">
                                        <input type="text" class="form-control key_number" value="<?php echo $job_row->key_number; ?>" placeholder="Insert Key Number" />
                                        <input type="hidden" class="job_id" value="<?php echo $job_row->jid; ?>" />
                                    </td>
                                    <td>
                                        <?php  echo $job_row->key_access_details; ?>
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
                                     <!-- pick up timestamp -->
                                    <td>
                                        <?php 
                                        echo $this->system_model->isDateNotEmpty($ak_created_date)?date('H:i', strtotime($ak_created_date)):null;                                                                                    
                                        ?>
                                    </td>
                                    <td class="is_keys_picked_up_td">  
                                                            
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
                                                    // job not completed reason
                                                    $jr_sql = $this->db->query("
                                                        SELECT `job_reason_id`, `name`
                                                        FROM `job_reason`
                                                        ORDER BY `name` ASC
                                                    ");                                        
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
                                            
                                        <input type="hidden" class="agency_keys_id" value="<?php echo $agency_keys_id; ?>" />
                                    </td>
                                    <td>
                                        <?php
                                        echo $this->system_model->isDateNotEmpty($drop_off_ts)?date('H:i', strtotime($drop_off_ts)):null;   
                                        ?>
                                    </td>	
                                    <td>
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
                                    </td>								                                
                                </tr>
                            <?php   

                            $job_id_arr[] = $job_id;

                            $row_count++;

                            }
                        }else{ ?>
                            <tr><td colspan='8'>No Data</td></tr>
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

                                $agency_keys_id = $job_row->agency_keys_id;
                                $is_keys_picked_up = $job_row->is_keys_picked_up;
                                $attend_property = $job_row->attend_property;
                                $job_reason = $job_row->ak_job_reason;
                                $reason_comment = $job_row->ak_reason_comment;
                                $ak_created_date = $job_row->ak_created_date;
                                $drop_off_ts = $job_row->drop_off_ts;
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
                                    <!-- address -->                                						
                                    <td class="prop_address">                                        
                                        <?php echo $p_address; ?>                                                        
                                    </td>
                                    <!-- key number -->
                                    <td class="prop_key_num">
                                        <input type="text" class="form-control key_number" value="<?php echo $job_row->key_number; ?>" placeholder="Insert Key Number" />
                                        <input type="hidden" class="job_id" value="<?php echo $job_row->jid; ?>" />
                                    </td>
                                    <td>
                                        <?php  echo $job_row->key_access_details; ?>
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
                                    <!-- pick up timestamp -->
                                    <td>
                                        <?php 
                                        echo $this->system_model->isDateNotEmpty($ak_created_date)?date('H:i', strtotime($ak_created_date)):null;                                                                                    
                                        ?>
                                    </td>
                                    <td class="is_keys_picked_up_td">  
                                                            
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
                                                    // job not completed reason
                                                    $jr_sql = $this->db->query("
                                                        SELECT `job_reason_id`, `name`
                                                        FROM `job_reason`
                                                        ORDER BY `name` ASC
                                                    ");                                        
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
                                            
                                        <input type="hidden" class="agency_keys_id" value="<?php echo $agency_keys_id; ?>" />
                                    </td>
                                    <td>
                                        <?php
                                        echo $this->system_model->isDateNotEmpty($drop_off_ts)?date('H:i', strtotime($drop_off_ts)):null;   
                                        ?>
                                    </td>	
                                    <td>
                                        <div class="radio float-left mr-2">
                                            <input type="radio" name="is_keys_returned<?php echo $row_count."-".$key_action_no_space; ?>" id="is_keys_returned_yes<?php echo $row_count."-".$key_action_no_space; ?>" class="is_keys_returned is_keys_returned_yes inline-block" value="1" <?php echo ( $is_keys_returned == 1 )?'checked':null; ?> />  
                                            <label class="inline-block" for="is_keys_returned_yes<?php echo $row_count."-".$key_action_no_space; ?>">Yes</label>   
                                        </div>

                                        <div class="radio float-left">
                                            <input type="radio" name="is_keys_returned<?php echo $row_count."-".$key_action_no_space; ?>" id="is_keys_returned_no<?php echo $row_count."-".$key_action_no_space; ?>" class="is_keys_returned is_keys_returned_no inline-block" value="0" <?php echo ( $is_keys_returned == 0 && is_numeric($is_keys_returned) )?'checked':null; ?> />          
                                            <label class="inline-block" for="is_keys_returned_no<?php echo $row_count."-".$key_action_no_space; ?>">No</label> 
                                        </div>


                                        <div class="keys_not_returned_div" style="display:<?php echo ( $is_keys_returned == 0 && is_numeric($is_keys_returned) )?'block':'none'; ?>;">
                                    
                                            <!-- key not returned note -->
                                            <div><input type="text" name="not_returned_notes" class="form-control not_returned_notes" placeholder="Comment" value="<?php echo ( $not_returned_notes != '' )?$not_returned_notes:null ?>" /></div>   

                                        </div>
                                    </td>								                                
                                </tr>
                            <?php   
                            $row_count++;
                            }
                        }
                        ?>
                        </tbody>
                    </table> 

                    <table class="table main-table keys_table table-bordered">
                        <thead>
                            <tr>	
                                <th>Action</th>          
                                <th>Agency</th>
                                <th>No. Of Keys</th>  
                                <th>Signature</th>                                                       	
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // tech run keys
                            $trk_sql = $this->db->query("
                                SELECT 
                                    kr.`tech_run_keys_id`, kr.`action`, kr.`number_of_keys`, kr.`agency_staff`, kr.`completed`, kr.`completed_date`, kr.`sort_order`, kr.`signature_svg`,
                                    a.`agency_id`, a.`agency_name`, a.`address_1`, a.`address_2`, a.`address_3`, a.`state`, a.`postcode`, a.`phone`, a.`agency_hours`, a.`lat`, a.`lng`
                                FROM `tech_run_keys` AS kr
                                LEFT JOIN `agency` AS a ON kr.`agency_id` = a.`agency_id`
                                WHERE kr.`date` = '{$date}'
                                AND ( 
                                    kr.`deleted` = 0 
                                    OR kr.`deleted` IS NULL 
                                )
                                AND a.`country_id` = {$country_id}
                                AND kr.`assigned_tech` ={$tech_id}
                                AND kr.`agency_id` = {$agency_id}
                            ");
                            if($trk_sql->num_rows() > 0){
                                foreach($trk_sql->result() as $trk_row){ 
                                    
                                    // background color
                                    $bgcolor = ( $trk_row->completed == 1 )?'#c2ffa7':'';
                                    
                                    // display signature
                                    $signature = null;
                                    if( $trk_row->action == 'Drop Off' ){
                                        if( $trk_row->signature_svg != '' ){
                                            $signature = "<a href='javascript:void(0);' class='view_signature' data-signature_svg='{$trk_row->signature_svg}'>View</a>";
                                        }                                    
                                    }else{
                                        $signature = "N/A";
                                    }
                                    
                                ?>                                   
                                    <tr style="background-color:<?php echo $bgcolor; ?>">
                                        <td><?php echo $trk_row->action; ?></td>        
                                        <td><?php echo $trk_row->agency_staff; ?></td>
                                        <td><?php echo $trk_row->number_of_keys; ?></td>
                                        <td><?php echo $signature; ?></td>        						
                                    </tr>                                                    
                                <?php
                                }
                            }
                            ?>  
                        </tbody>
                    </table> 
                    
                <?php
                } 
                ?>                             
			</div>

		 <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
        <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>

		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
    This page shows all On Hold jobs, both regular and for COVID-19 reasons                    
	</p>

</div>

<!-- SIGNATURE -->
<div id="drop_off_fb" class="jfancybox agency_keys_lb" style="display:none;" >
	
	<img class="signature_svg_img" />

</div>
<!-- Fancybox END -->


<script>
jQuery(document).ready(function(){   

    // signature lightbox
    jQuery(".view_signature").click(function(){

        var view_link = jQuery(this);			
        var signature_svg = view_link.attr("data-signature_svg");

        
        jQuery(".signature_svg_img").attr("src",''); // clear each time    
        jQuery(".signature_svg_img").attr("src",signature_svg);
        
        // trigger lightbox
        $.fancybox.open({
            src  : '#drop_off_fb',
            touch : false
        });                  
        
    });
   
});
</script>