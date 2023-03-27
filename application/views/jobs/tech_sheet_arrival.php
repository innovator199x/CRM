<div id="arrival_tab_main_div">
<table class="table main-table">

    <thead>
        <tr>
            <th>Address</th>
            <th class="text-center">Service Type</th>
            <th>Job Type</th>            
            <th>Key</th>
            <th>House Alarm Code</th>   
            <th>Lockbox Code</th>                      	
        </tr>
    </thead>

    <tbody>
        <td>
            <?php echo "{$job_row->p_street_num} {$job_row->p_street_name} {$job_row->p_suburb}"; ?>
        </td>
        <td class="text-center">
            <img src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($job_row->j_service); ?>" />
        </td>
        <td>
            <?php echo $job_row->job_type; ?>
        </td>
        <td>
            <span class="key_number_span">
                <?php echo ( $job_row->key_number !='' )?$job_row->key_number:'No Key #'; ?>
            </span>
            <input type="hidden" class="key_number" value="<?php echo $job_row->key_number; ?>" />
            <span data-toggle="tooltip" title="Edit" id="edit_key_number" class="font-icon font-icon-pencil edit_icon_red"></span>
        </td>
        <td>   
            <span class="alarm_code_span">                                             
            <?php echo ( $job_row->alarm_code != '' )?$job_row->alarm_code:'No Alarm Code'; ?>
            </span>
            <input type="hidden" class="alarm_code" value="<?php echo $job_row->alarm_code; ?>" /> 
            <span data-toggle="tooltip" title="Edit" id="edit_alarm_code" class="font-icon font-icon-pencil edit_icon_red"></span>
        </td>  
        <td>
            <?php echo $job_row->lb_code; ?>
        </td>
    </tbody>                      

</table> 

<table class="table main-table">
    
    <tbody>
        <tr>
            <td colspan="3" class="align-top">
                
                <table class="table main-table">
                    <thead>
                        <tr>
                            <th>            
                                <span class="fa fa-users" data-toggle="tooltip" title="Onsite Contacts" ></span>
                            </th>
                            <th>                                
                                <span class="fa fa-mobile" data-toggle="tooltip" title="Mobile" ></span>
                            </th> 
                            <th>     
                                <span class="fa fa-phone" data-toggle="tooltip" title="Phone" ></span>
                            </th>                                                        
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    if( $job_row->property_id > 0 ){

                        // get tenants 
                            $sel_query = "
                            pt.`property_tenant_id`,
                            pt.`tenant_firstname`,
                            pt.`tenant_lastname`,
                            pt.`tenant_mobile`,
                            pt.`tenant_landline`
                        ";
                        $params = array(
                            'sel_query' => $sel_query,
                            'property_id' => $job_row->property_id,
                            'pt_active' => 1,                                                                          
                            'display_query' => 0
                        );
                        $pt_sql = $this->properties_model->get_property_tenants($params);
                        $pt_num_row = $pt_sql->num_rows();
                        
                        if( $pt_num_row > 0 ){                                           
                            foreach($pt_sql->result() as $pt_row){                                       
                                ?>
                                <tr>
                                    <td>                                        
                                        <label class="txt_lbl"><?php echo "{$pt_row->tenant_firstname} $pt_row->tenant_lastname"; ?></label>                                                       
                                        <input type="text" class="form-control txt_hid pt_fname tenant_update" data-db_table_field="tenant_firstname" value="<?php echo $pt_row->tenant_firstname; ?>" />
                                    </td>    
                                    <td>  
                                        <label class="txt_lbl"><?php echo $pt_row->tenant_mobile; ?></label>                                                        
                                        <input type="text" class="form-control txt_hid pt_mob tenant_update tenant_mobile" data-db_table_field="tenant_mobile" value="<?php echo $pt_row->tenant_mobile; ?>" />
                                    </td>                               
                                    <td>   
                                        <label class="txt_lbl"><?php echo $pt_row->tenant_landline; ?></label>                                                       
                                        <input type="text" class="form-control txt_hid pt_landline tenant_update phone-with-code-area-mask-input" data-db_table_field="tenant_landline" value="<?php echo $pt_row->tenant_landline; ?>" />
                                    </td>                                                                      
                                </tr>
                                <?php
                            }                                                                                 
                        }else{ ?>
                            <tr><td colspan="3">No active tenants</td></tr>
                        <?php   
                        }                    

                    }     
                    ?>
                    <tr>
                        <td colspan="3">
                        <?php //echo $booked_job_log;
                        if( $job_row->booked_with != 'Agent' ){
                            echo "Booked with {$job_row->booked_with} via [SMS / Phone] on ".date('d/m/Y',strtotime($job_row->j_date))." <b>@{$job_row->time_of_day}</b>";
                        }else if( $job_row->booked_with == 'Agent' ){

                            if( $job_row->job_entry_notice == 1 ){
                                echo "Booked via Entry Notice on [EN DATE] ".date('d/m/Y',strtotime($job_row->en_date_issued))." <b>@{$job_row->time_of_day}</b>";
                            }else if( $job_row->key_access_required == 1 ){
                                echo "Booked with agent due to {$job_row->key_access_details} <b>@{$job_row->time_of_day}</b>";
                            }
                            
                        }  
                        ?>
                        </td>
                    </tr>
                    </tbody> 
                    </table>

                

            </td>
            <td colspan="2" class="align-top">

                <table class="table main-table">

                    <thead>
                        <tr>
                            <th>
                                <span class="fa fa-building" data-toggle="tooltip" title="Agency" ></span>
                            </th> 
                            <th>
                                <span class="fa fa-phone" data-toggle="tooltip" title="Phone" ></span>
                            </th>                                                                                          
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td class="<?php echo ( $job_row->priority > 0 )?'j_bold':null; ?>">
                                <?php echo "{$job_row->agency_name}".( ( $job_row->priority > 0 )?' ('.$job_row->abbreviation.')':null ); ?>
                            </td>
                            <td>
                                <?php echo $job_row->a_phone; ?>
                            </td>                                            
                        </tr>                     
                    </tbody>

                </table>

            </td>							       	
        </tr>
    </tbody>

</table>


<table class="table main-table">

    <thead>
        <tr>
            <th>Job Notes</th>
            <th>Agency Specific Notes</th>
            <th>Property Notes</th>
        </tr>
    </thead>

    <tbody>
        <tr>
            <td>   
                <span class="j_comments_span">   
                    <?php echo trim(stripslashes($job_row->j_comments)); ?> 
                </div>                        
                <span data-toggle="tooltip" title="Edit" id="edit_job_notes" class="font-icon font-icon-pencil edit_icon_red"></span>
            </td>  
            <td>
                <?php echo $job_row->agency_specific_notes; ?>
            </td>
            <td>    
                <span class="p_comments_span">            
                    <?php echo trim(stripslashes($job_row->p_comments)); ?>  
                </span>              
                <span data-toggle="tooltip" title="Edit" id="edit_property_notes" class="font-icon font-icon-pencil edit_icon_red"></span>                               
            </td> 
        </tr>           
    </tbody>

</table>

<?php
// short term rental, previously known as holiday rental
if( $job_row->holiday_rental == 1 ){ ?>

    <div class="mb-3 mr-3 float-left">
        <img src="/images/row_icons/holiday_coloured.png">
        This property is a short term rental.
    </div>

<?php    
}

// service garage
if( $job_row->service_garage == 1 && $job_row->p_state == 'NSW' ){ ?>

    <div class="mb-2 mr-3 float-left">
        <img src="/images/service_garage.png" class="service_garage_icon">
        This property must have a heat alarm in the garage that is interconnected to the house.
    </div>

<?php    
}

// if first visit
if( $this->tech_model->check_prop_first_visit($job_row->property_id) == true   ){ ?>
<div class="mb-3">
    <img src="<?php echo $this->config->item('crmci_link'); ?>/images/first_icon.png" class="row_icons" title="First visit" data-toggle="tooltip" /> First Visit
</div>
<?php
}else{
?>
    <table class="table main-table">

        <thead>
            <tr>
                <th>Property Information</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>

                    

                    <table class="table main-table">

                        <tbody>
                            <tr>
                                <th>Ladder</th>
                                <td><?php echo ( $job_row->survey_ladder == '4FT' )?'3FT':$job_row->survey_ladder; ?></li></td> 
                            </tr>   
                            <tr>
                                <th>Ceiling Type</th>
                                <td><?php echo $this->jobs_model->display_ceiling_type_full($job_row->survey_ceiling); ?></td> 
                            </tr>
                            <tr>
                                <th>Switchboard Location</th>
                                <td>
                                    <input type="text" class="form-control ss_location" data-db_table_field="ss_location" data-db_table="jobs" value="<?php echo $job_row->ss_location; ?>" />            
                                </td> 
                            </tr> 
                            <?php
                            // get existing alarms
                            $existing_alarms_sql = $this->db->query("
                            SELECT 
                                al.`expiry`, 
                                al.`alarm_type_id`,
                                al.`make`,
                                al.`model`,
                                al.`ts_position`,
                                
                                al_pwr.`alarm_pwr_id`,
                                al_pwr.`alarm_pwr`,
                                
                                al_type.`alarm_type_id`,
                                al_type.`alarm_type`    
                            FROM alarm AS al 
                            LEFT JOIN `alarm_pwr` AS al_pwr ON al.`alarm_power_id` = al_pwr.`alarm_pwr_id`
                            LEFT JOIN `alarm_type` AS al_type ON al.`alarm_type_id` = al_type.`alarm_type_id`
                            WHERE al.job_id = {$this->input->get_post('job_id')}
                            AND al.`new` != 1
                            ORDER BY al.alarm_id ASC
                            ");                           

                            if( $existing_alarms_sql->num_rows() > 0 ){
                            ?>
                            <tr>
                                <th>Existing Alarms</th>
                                <td>                              
                                    <table class="table main-table">                                
                                        <thead>
                                            <tr> 
                                                <th>Position</th>                                                 
                                                <th>Power</th>     
                                                <th>Type</th>                                             
                                                <th>Make</th>  
                                                <th>Model</th>                                 
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach( $existing_alarms_sql->result() as $existing_alarms_row ){ ?>   
                                                <tr>
                                                    <td><?php echo $existing_alarms_row->ts_position; ?></td>                                                   
                                                    <td><?php echo $existing_alarms_row->alarm_pwr; ?></td>    
                                                    <td><?php echo $existing_alarms_row->alarm_type; ?></td>                                                                                                    
                                                    <td><?php echo $existing_alarms_row->make; ?></td>
                                                    <td><?php echo $existing_alarms_row->model; ?></td>                               
                                                </tr>                                     
                                            <?php
                                            }
                                            ?>
                                        </tbody>
                                    </table>   
                                    <?php
                                    if( $this->config->item('country') == 1 ){ // AU

                                        if( $job_row->p_state == 'QLD' ){ // QLD 

                                            if( $job_row->preferred_alarm_id > 0 ){
                                                $num_qld_alarm_txt = ( $job_row->qld_new_leg_alarm_num > 0 )?"{$job_row->qld_new_leg_alarm_num} ":null;
                                            ?>
                                                <p>This upgrade job is approved for <?php echo "{$num_qld_alarm_txt}{$job_row->pref_alarm_make}"; ?> alarms, if you require more, please call the office</p>                                
                                            <?php
                                            }
                                            
                                        }else{ // non-QLD ?>                                            
                                            <p>This property uses <?php echo $this->system_model->display_free_emerald_or_paid_brooks($job_row->agency_id); ?> alarms. </p>                             
                                        <?php    
                                        }

                                    }else{ // NZ ?>
                                        <p>This upgrade job is approved for <?php echo $this->system_model->display_orca_or_cavi_alarms($job_row->agency_id); ?> alarms, if you require more, please call the office</p>
                                    <?php
                                    }                                    
                                    ?>                                    
                                </td> 
                            </tr> 
                            <?php   
                            }
                            ?>         
                            </tbody>
                            <?php
                            $get_expired_alarm_sql_str = "
                            SELECT 
                                al.`expiry`, 
                                al.`alarm_type_id`,
                                al.`make`,
                                al.`model`,
                                al.`ts_position`,

                                al_type.`alarm_type_id`,
                                al_type.`alarm_type`,
                                
                                al_pwr.`alarm_pwr_id`,
                                al_pwr.`alarm_pwr`  
                            FROM `alarm` AS al
                            LEFT JOIN `alarm_pwr` AS al_pwr ON al.`alarm_power_id` = al_pwr.`alarm_pwr_id`
                            LEFT JOIN `alarm_type` AS al_type ON al.`alarm_type_id` = al_type.`alarm_type_id`
                            WHERE al.`alarm_power_id` != 6
                            AND al.`job_id` = {$this->input->get_post('job_id')}    
                            AND al.`expiry` <= '{$this_year}'                             
                            ";
                            $get_expired_alarm_sql = $this->db->query($get_expired_alarm_sql_str);
                            if( $get_expired_alarm_sql->num_rows() > 0 ){
                            ?>
                            <tr>
                                <th>Expired Alarms</th>
                                <td>                              
                                    <table class="table main-table">                                
                                        <thead>
                                            <tr>  
                                                <th>Power</th>                                                  
                                                <th>Position</th>                                 
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach( $get_expired_alarm_sql->result() as $get_expired_alarm_row ){ ?>   
                                                <tr>
                                                    <td><?php echo $get_expired_alarm_row->alarm_pwr; ?></td>
                                                    <td><?php echo $get_expired_alarm_row->ts_position; ?></td>                               
                                                </tr>                                     
                                            <?php
                                            }
                                            ?>
                                        </tbody>
                                    </table>                                   
                                </td> 
                            </tr> 
                            <?php   
                            }                            
                            ?>                                 
                        </tbody>                        

                    </table>

                    
                    
                </td> 
            </tr>           
        </tbody>

    </table>

<?php  
    //}
}
?>

<?php
// preffered alarms
if( $this->config->item('country') == 1 ){ // AU

    if( $job_row->p_state == 'QLD' ){ // QLD 
    
        if( $job_row->preferred_alarm_id > 0 ){
            $num_qld_alarm_txt = ( $job_row->qld_new_leg_alarm_num > 0 )?"{$job_row->qld_new_leg_alarm_num} ":null;
        ?>
            <p>This upgrade job is approved for <?php echo "{$num_qld_alarm_txt}{$job_row->pref_alarm_make}"; ?> alarms, if you require more, please call the office</p>                                
        <?php
        }
        
    }else{ // non-QLD ?>                                            
        <p>This property uses <?php echo $this->system_model->display_free_emerald_or_paid_brooks($job_row->agency_id); ?> alarms. </p>                             
    <?php    
    }

}else{ // NZ ?>
    <p>This upgrade job is approved for <?php echo $this->system_model->display_orca_or_cavi_alarms($job_row->agency_id); ?> alarms, if you require more, please call the office</p>
<?php
}                                    

// sales upgrade
if( $job_row->job_type == 'IC Upgrade' && $job_row->is_sales == 1 ){ ?>
    <p class="text-danger"><b>Sales Upgrade</b></p>
<?php
}
?>
</div>


<div class="row">

    <div class="col-md-6 text-left">
        <button type="button" id="unable_to_complete_btn" class="btn btn-danger unable_to_complete_btn">Unable to complete Job</button>                	
    </div>

    <div class="col-md-6 text-right">
        <button type="button" class="btn techsheet_tab_next">Next</button>                  	
    </div>

</div> 


<!-- key -->			
<div id="edit_key_number_fb" class="fancybox" style="display:none;" >

    <h4>Edit Key</h4>

    <div>
        <table class="table main-table borderless">
            <tbody>
                <tr> 
                    <th>Key</th>               
                    <td>
                        <input type="text" class="form-control key_number" />
                    </td>                    
                </tr>                
            </tbody>                                  
        </table>
    </div>    

    <div class="text-right">
        <button type="button" id="update_key_number_btn" class="btn">Update</button>
    </div>

</div>


<!-- alarm code -->							
<div id="edit_house_alarm_code_fb" class="fancybox" style="display:none;" >

    <h4>Edit House Alarm Code</h4>

    <div>
        <table class="table main-table borderless">
            <tbody>
                <tr> 
                    <th>House Alarm Code</th>               
                    <td>
                        <input type="text" class="form-control alarm_code" />
                    </td>                    
                </tr>            
            </tbody>                                  
        </table>
    </div>    

    <div class="text-right">
        <button type="button" id="update_alarm_code_btn" class="btn">Update</button>
    </div>

</div>

<!-- job notes -->			
<div id="edit_job_notes_fb" class="fancybox w-100 h-50" style="display:none;" >

    <h4>Edit Job Notes</h4>

    <div class="w-100 h-75 mb-4">
        <table class="table main-table w-100 h-100 borderless">
            <tbody>
                <tr> 
                    <th>Job Notes</th>               
                    <td class="w-75 h-100">
                        <textarea class="form-control j_comments w-100 h-100"><?php echo trim(stripslashes($job_row->j_comments)); ?></textarea>
                    </td>                    
                </tr>                
            </tbody>                                  
        </table>
    </div>    

    <div class="text-right">
        <button type="button" id="update_job_notes_btn" class="btn">Update</button>
    </div>
    

</div>

<!-- property notes -->			
<div id="edit_property_notes_fb" class="fancybox w-100 h-50" style="display:none;" >

    <h4>Edit Property Notes</h4>
    
    <div class="w-100 h-75 mb-4">
        <table class="table main-table w-100 h-100 borderless">
            <tbody>
                <tr> 
                    <th>Property Notes</th>               
                    <td class="w-75 h-100">
                        <textarea class="form-control p_comments w-100 h-100"><?php echo trim(stripslashes($job_row->p_comments)); ?></textarea>
                    </td>                    
                </tr>                
            </tbody>                                  
        </table>
    </div> 

    <div class="text-right">
        <button type="button" id="update_property_notes_btn" class="btn">Update</button>
    </div>

</div>

<style>
.service_garage_icon{
    width: 30px;
}
</style>

<script>

jQuery(document).ready(function(){

    
    // key number
    jQuery("#edit_key_number").click(function(){

        var node = jQuery(this);
        var parent_td = node.parents("td:first");
        var key_number = parent_td.find(".key_number").val(); 
        

        var lb_id = '#edit_key_number_fb'; // lightbox ID
        var lb_div = jQuery(lb_id); // lightbox div      

        lb_div.find(".key_number").val(key_number);

        jQuery.fancybox.open({
            src  : lb_id
        });

    });
    
    jQuery("#update_key_number_btn").click(function(){

        var property_id = <?php echo $job_row->property_id; ?>;  

        var lb_id = '#edit_key_number_fb'; // lightbox ID
        var lb_div = jQuery(lb_id); // lightbox div  
        var key_number = lb_div.find(".key_number").val();

        // job update
        if( property_id > 0 ){

            jQuery('#load-screen').show();
            jQuery(".techsheet_tab_next:visible").prop("disabled",true); // disable NEXT button

            jQuery.ajax({
                type: "POST",
                url: "/jobs/ajax_techsheet_inline_update",
                data: { 
                    property_id: property_id,
                    db_table_field: 'key_number',
                    db_table_value: key_number,
                    db_table: 'property'
                }
            }).done(function( ret ){

                jQuery('#load-screen').hide();  
                jQuery(".techsheet_tab_next:visible").prop("disabled",false); // enable NEXT button

                //location.reload();      
                jQuery("#arrival_tab_main_div .key_number_span").html(key_number);      
                $.fancybox.close();                       			

            });

        }

    });
    
    // alarm code
    jQuery("#edit_alarm_code").click(function(){

        var node = jQuery(this);
        var parent_td = node.parents("td:first");
        var alarm_code = parent_td.find(".alarm_code").val(); 
        

        var lb_id = '#edit_house_alarm_code_fb'; // lightbox ID
        var lb_div = jQuery(lb_id); // lightbox div      

        lb_div.find(".alarm_code").val(alarm_code);

        jQuery.fancybox.open({
            src  : lb_id
        });

    });

    jQuery("#update_alarm_code_btn").click(function(){

        var property_id = <?php echo $job_row->property_id; ?>;  

        var lb_id = '#edit_house_alarm_code_fb'; // lightbox ID
        var lb_div = jQuery(lb_id); // lightbox div  
        var alarm_code = lb_div.find(".alarm_code").val();

        // job update
        if( property_id > 0 ){

            jQuery('#load-screen').show();
            jQuery(".techsheet_tab_next:visible").prop("disabled",true); // disable NEXT button

            jQuery.ajax({
                type: "POST",
                url: "/jobs/ajax_techsheet_inline_update",
                data: { 
                    property_id: property_id,
                    db_table_field: 'alarm_code',
                    db_table_value: alarm_code,
                    db_table: 'property'
                }
            }).done(function( ret ){

                jQuery('#load-screen').hide();   
                jQuery(".techsheet_tab_next:visible").prop("disabled",false); // enable NEXT button 
                //location.reload();      

                jQuery("#arrival_tab_main_div .alarm_code_span").html(alarm_code);      
                $.fancybox.close();                                			

            });

        }

    });

    // job notes
    jQuery("#edit_job_notes").click(function(){

        jQuery.fancybox.open({
            src  : '#edit_job_notes_fb'
        });

    });

    jQuery("#update_job_notes_btn").click(function(){

        var job_id = <?php echo $this->input->get_post('job_id'); ?>; 

        var lb_id = '#edit_job_notes_fb'; // lightbox ID
        var lb_div = jQuery(lb_id); // lightbox div  
        var j_comments = lb_div.find(".j_comments").val();
        var j_comments_trimmed = j_comments.trim();

        // job update
        if( job_id > 0 ){

            jQuery('#load-screen').show();
            jQuery(".techsheet_tab_next:visible").prop("disabled",true); // disable NEXT button

            jQuery.ajax({
                type: "POST",
                url: "/jobs/ajax_techsheet_inline_update",
                data: { 
                    job_id: job_id,
                    db_table_field: 'comments',
                    db_table_value: j_comments,
                    db_table: 'jobs'
                }
            }).done(function( ret ){

                jQuery('#load-screen').hide();  
                jQuery(".techsheet_tab_next:visible").prop("disabled",false); // enable NEXT button  
                //location.reload(); 

                jQuery("#arrival_tab_main_div .j_comments_span").html(j_comments_trimmed);      
                $.fancybox.close();                                      			

            });

        }

    });

    // property notes
    jQuery("#edit_property_notes").click(function(){

        jQuery.fancybox.open({
            src  : '#edit_property_notes_fb'
        });

    });

    jQuery("#update_property_notes_btn").click(function(){

        var property_id = <?php echo $job_row->property_id; ?>;  

        var lb_id = '#edit_property_notes_fb'; // lightbox ID
        var lb_div = jQuery(lb_id); // lightbox div  
        var p_comments = lb_div.find(".p_comments").val();
        var p_comments_trimmed = p_comments.trim();

        // job update
        if( property_id > 0 ){

            jQuery('#load-screen').show();
            jQuery(".techsheet_tab_next:visible").prop("disabled",true); // disable NEXT button
            
            jQuery.ajax({
                type: "POST",
                url: "/jobs/ajax_techsheet_inline_update",
                data: { 
                    property_id: property_id,
                    db_table_field: 'comments',
                    db_table_value: p_comments,
                    db_table: 'property'
                }
            }).done(function( ret ){

                jQuery('#load-screen').hide();  
                jQuery(".techsheet_tab_next:visible").prop("disabled",false); // enable NEXT button  
                //location.reload();   

                jQuery("#arrival_tab_main_div .p_comments_span").html(p_comments_trimmed);      
                $.fancybox.close();                                 			

            });

        }

    });
    

});

</script>