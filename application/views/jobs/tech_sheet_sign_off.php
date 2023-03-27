<section>
                <div class="body-typical-body">
                    <div class="table-responsive">				                                                                         

                        <table class="table main-table">

                            <thead>
                                <tr>
                                    <th>Technician</th>
                                    <th>Date</th>
                                    <th>Safe Work Method Statements (SWMS)</th>
                                    <th>Repair Notes</th>
                                    <th>Job Notes</th>
                                    <th>Property Notes</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td>
                                        <?php echo "{$job_row->tech_fname} {$job_row->tech_lname}"; ?>
                                    </td>  
                                    <td>
                                        <?php
                                        if( $job_row->j_status == 'Completed' ){                                   
                                            echo ( $this->system_model->isDateNotEmpty($job_row->completed_timestamp) == true )?date('d/m/Y', strtotime($job_row->completed_timestamp)):null;
                                        }else{
                                            // ts_signoffdate surprisingly a varchar >.<
                                            echo ( $job_row->ts_signoffdate != '' )?$job_row->ts_signoffdate:date('d/m/Y');                                    
                                        }
                                        ?>
                                    </td> 
                                    <td>
                                        <p> Whilst on site at the above property, I observed and followed the following SWMS:</p>
                                        <ul>
                                            <li>
                                                <div class="checkbox">
                                                    <input type="checkbox" id="swms_heights" name="swms_heights" class="swms_chk" data-db_table_field="swms_heights" data-db_table="jobs" value="1" <?php echo ($job_row->swms_heights == 1)?'checked':null; ?> />
                                                    <label for="swms_heights">Working at Heights</label>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="checkbox">
                                                    <input type="checkbox" id="swms_uv_protection" name="swms_uv_protection" class="swms_chk" data-db_table_field="swms_uv_protection" data-db_table="jobs" value="1" <?php echo ($job_row->swms_uv_protection == 1)?'checked':null; ?> />
                                                    <label for="swms_uv_protection">UV Protection</label>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="checkbox">
                                                    <input type="checkbox" id="swms_asbestos" name="swms_asbestos" class="swms_chk" data-db_table_field="swms_asbestos" data-db_table="jobs" value="1" <?php echo ($job_row->swms_asbestos == 1)?'checked':null; ?> />
                                                    <label for="swms_asbestos">Likely to involve Disturbing Asbestos</label>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="checkbox">
                                                    <input type="checkbox" id="swms_powertools" name="swms_powertools" class="swms_chk" data-db_table_field="swms_powertools" data-db_table="jobs" value="1" <?php echo ($job_row->swms_powertools == 1)?'checked':null; ?> />
                                                    <label for="swms_powertools">Using Corded Power Tools</label>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="checkbox">
                                                    <input type="checkbox" id="swms_animals" name="swms_animals" class="swms_chk" data-db_table_field="swms_animals" data-db_table="jobs" value="1" <?php echo ($job_row->swms_animals == 1)?'checked':null; ?> />
                                                    <label for="swms_animals">Animals on Site</label>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="checkbox">
                                                    <input type="checkbox" id="swms_live_circuit" name="swms_live_circuit" class="swms_chk" data-db_table_field="swms_live_circuit" data-db_table="jobs" value="1" <?php echo ($job_row->swms_live_circuit == 1)?'checked':null; ?> />
                                                    <label for="swms_live_circuit">Working with Live Circuits</label>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="checkbox">
                                                    <input type="checkbox" id="swms_covid_19" name="swms_covid_19" class="swms_chk" data-db_table_field="swms_covid_19" data-db_table="jobs" value="1" <?php echo ($job_row->swms_covid_19 == 1)?'checked':null; ?> />
                                                    <label for="swms_covid_19">COVID-19 Protection</label>
                                                </div>
                                            </li>                                    
                                        </ul>
                                    </td> 
                                    <td>
                                        <!--textarea class="form-control repair_notes" name="repair_notes" id="repair_notes" data-db_table_field="repair_notes" data-db_table="jobs"><?php echo stripslashes($job_row->repair_notes); ?></textarea>-->
                                        <span id="update_repair_notes_lbl"><?php echo stripslashes($job_row->repair_notes); ?></span>
                                        <span data-toggle="tooltip" title="Edit" id="update_repair_notes_fb_link" class="font-icon font-icon-pencil edit_icon_red"></span>
                                    </td> 
                                    <td>
                                        <!--<textarea class="form-control tech_comments" name="tech_comments" id="tech_comments" data-db_table_field="tech_comments" data-db_table="jobs"><?php echo stripslashes($job_row->tech_comments); ?></textarea>-->
                                        <span id="update_tech_comments_lbl"><?php echo stripslashes($job_row->tech_comments); ?></span>
                                        <span data-toggle="tooltip" title="Edit" id="update_tech_comments_fb_link" class="font-icon font-icon-pencil edit_icon_red"></span>
                                    </td> 
                                    <td>
                                        <!--<textarea class="form-control p_comments" name="p_comments" id="p_comments" data-db_table_field="comments" data-db_table="property"><?php echo stripslashes($job_row->p_comments); ?></textarea>-->
                                        <span id="update_p_comments_lbl"><?php echo stripslashes($job_row->p_comments); ?></span>
                                        <span data-toggle="tooltip" title="Edit" id="update_p_comments_fb_link" class="font-icon font-icon-pencil edit_icon_red"></span>
                                    </td> 
                                </tr>           
                            </tbody>

                        </table>              
                                                
                    </div>
                </div>
            </section>
            

            <div class="form-groups row">
                <div class="col-md">

                    <table class="table main-table borderless">
                        <tbody>
                            <tr>
                               <td colspan="2">

                                    <!--
                                    <div class="radio d-inline">
                                        <input type="radio" name="ts_techconfirm" id="ts_techconfirm_yes" class="chk_yes inline-block ts_techconfirm" value="1" <?php echo ( $job_row->ts_techconfirm == 1 )?'checked':null; ?> />
                                        <label for="ts_techconfirm_yes">Yes</label>
                                    </div>
                                    <div class="radio d-inline">
                                        <input type="radio" name="ts_techconfirm" id="ts_techconfirm_no" class="chk_no inline-block ts_techconfirm" value="0" <?php echo ( $job_row->ts_techconfirm == 0 && is_numeric($job_row->ts_techconfirm) )?'checked':null; ?> />
                                        <label for="ts_techconfirm_no">No</label>
                                    </div>
                                    -->

                                    <div class="checkbox">
                                        <input type="checkbox" id="ts_techconfirm" name="ts_techconfirm" class="ts_techconfirm" value="1" <?php echo ($job_row->ts_techconfirm == 1)?'checked':null; ?> />
                                        <label for="ts_techconfirm">I confirm that all items on the above checklist have been completed and all Appliances noted have been Inspected and Maintained as per Manufacturers Recommendations and the Australian Standards.</label>
                                    </div>

                               </td>                               
                            </tr> 
                            <tr>
                                <td class="sign_off_tab_bottom_table_col">
                                    <div class="radio d-inline">
                                        <input type="radio" name="prop_comp_with_state_leg" id="prop_comp_with_state_leg_yes" class="chk_yes inline-block prop_comp_with_state_leg" value="1" <?php echo ( $job_row->prop_comp_with_state_leg == 1 )?'checked':null; ?> />
                                        <label for="prop_comp_with_state_leg_yes">Yes</label>
                                    </div>
                                    <div class="radio d-inline">
                                        <input type="radio" name="prop_comp_with_state_leg" id="prop_comp_with_state_leg_no" class="chk_no inline-block prop_comp_with_state_leg" value="0" <?php echo ( $job_row->prop_comp_with_state_leg == 0 && is_numeric($job_row->prop_comp_with_state_leg) )?'checked':null; ?> />
                                        <label for="prop_comp_with_state_leg_no">No</label>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    if( $job_row->p_state == 'QLD' ){   

                                        echo "Is this property pre-2022 compliant?";

                                    }else{

                                        echo "Is this Property compliant with current State Legislation?";

                                    }
                                    ?>
                                </td>
                            </tr>

                            <?php
                            // NSW
                            if( $job_row->p_state == 'NSW' && $job_row->holiday_rental == 1 ){ ?>

                                <tr>
                                    <td class="sign_off_tab_bottom_table_col">
                                        <div class="radio d-inline">
                                            <input type="radio" name="short_term_rental_compliant" id="short_term_rental_compliant_yes" class="chk_yes inline-block short_term_rental_compliant" data-db_table_field="short_term_rental_compliant" data-db_table="nsw_pro_comp" value="1" <?php echo ( $job_row->short_term_rental_compliant == 1 )?'checked':null; ?> />
                                            <label for="short_term_rental_compliant_yes">Yes</label>
                                        </div>
                                        <div class="radio d-inline">
                                            <input type="radio" name="short_term_rental_compliant" id="short_term_rental_compliant_no" class="chk_no inline-block short_term_rental_compliant" data-db_table_field="short_term_rental_compliant" data-db_table="nsw_pro_comp" value="0" <?php echo ( $job_row->short_term_rental_compliant == 0 && is_numeric($job_row->short_term_rental_compliant) )?'checked':null; ?> />
                                            <label for="short_term_rental_compliant_no">No</label>
                                        </div>
                                    </td>
                                    <td>Is the property compliant to NSW short term rental legislation?</td>
                                </tr> 

                                <tr>                                    
                                    <td>
                                        <input type="number" name="nsw_leg_num_alarms" class="form-control nsw_leg_num_alarms" id="nsw_leg_num_alarms" value="<?php echo $job_row->nsw_leg_num_alarms; ?>" data-db_table_field="req_num_alarms" data-db_table="nsw_pro_comp" />
                                    </td>
                                    <td>How many alarms are required inside the house?</td>
                                </tr> 

                                <tr>
                                    <td class="sign_off_tab_bottom_table_col">
                                        <div class="radio d-inline">
                                            <input type="radio" name="service_garage" id="service_garage_yes" class="chk_yes inline-block service_garage" data-db_table_field="service_garage" data-db_table="property" value="1" <?php echo ( $job_row->service_garage == 1 )?'checked':null; ?> />
                                            <label for="service_garage_yes">Yes</label>
                                        </div>
                                        <div class="radio d-inline">
                                            <input type="radio" name="service_garage" id="service_garage_no" class="chk_no inline-block service_garage" data-db_table_field="service_garage" data-db_table="property" value="0" <?php echo ( $job_row->service_garage == 0 && is_numeric($job_row->service_garage) )?'checked':null; ?> />
                                            <label for="service_garage_no">No</label>
                                        </div>
                                    </td>
                                    <td>Does the property have an attached garage that is not associated with the rental lease?</td>
                                </tr> 

                                <tr class="req_heat_alarm_tr" <?php echo ( $job_row->service_garage == 1 )?null:'style="display:none;"'; ?>>                                    
                                    <td>
                                        <input type="number" name="req_heat_alarm" class="form-control req_heat_alarm" id="req_heat_alarm" value="<?php echo $job_row->req_heat_alarm; ?>" data-db_table_field="req_heat_alarm" data-db_table="nsw_pro_comp" />
                                    </td>
                                    <td>How many heat alarms are required to be installed in the garage?</td>
                                </tr> 

                            <?php
                            }
                            ?>  

                            <?php
                            // QLD ONLY
                            if( $job_row->p_state == 'QLD' ){ ?>
                            <tr>
                                <td class="sign_off_tab_bottom_table_col">
                                    <div class="radio d-inline">
                                        <input type="radio" name="prop_upgraded_to_ic_sa" id="prop_upgraded_to_ic_sa_yes" class="chk_yes inline-block prop_upgraded_to_ic_sa" value="1" <?php echo ( $job_row->prop_upgraded_to_ic_sa == 1 )?'checked':null; ?> />
                                        <label for="prop_upgraded_to_ic_sa_yes">Yes</label>
                                    </div>
                                    <div class="radio d-inline">
                                        <input type="radio" name="prop_upgraded_to_ic_sa" id="prop_upgraded_to_ic_sa_no" class="chk_no inline-block prop_upgraded_to_ic_sa" value="0" <?php echo ( $job_row->prop_upgraded_to_ic_sa == 0 && is_numeric($job_row->prop_upgraded_to_ic_sa) )?'checked':null; ?> />
                                        <label for="prop_upgraded_to_ic_sa_no">No</label>
                                    </div>
                                </td>
                                <td>Does this property meet QLD NEW Legislation?</td>
                            </tr> 
                            <?php
                            }
                            ?>         
                        </tbody>

                    </table>                  
                
                    
                </div>
                
            </div>

            

            <div class="row">

                <div class="col-md-6 text-left">
                    <button type="button" class="btn btn-success techsheet_tab_prev">Previous</button>	
                </div>

                <div class="col-md-6 text-right">                    
                    <button type="button" id="btn_comp_ts" class="btn">SUBMIT COMPLETED TECHSHEET</button>                    	
                </div>

            </div>  

<!-- fancy box - START -->							
<div id="update_repair_notes_fb" class="fancybox w-75 h-50" style="display:none;" >

    <h4>Repair Notes</h4>
    <textarea class="form-control w-100 h-75" id="repair_notes"><?php echo stripslashes($job_row->repair_notes); ?></textarea>
    <button type="button" id="update_repair_notes_btn" class="btn float-right mt-3">Update</button>	

</div>

<div id="update_tech_comments_fb" class="fancybox w-75 h-50" style="display:none;" >

    <h4>Job Notes</h4>
    <textarea class="form-control w-100 h-75" id="tech_comments"><?php echo stripslashes($job_row->tech_comments); ?></textarea>
    <button type="button" id="update_tech_comments_btn" class="btn float-right mt-3">Update</button>	

</div>

<div id="update_p_comments_fb" class="fancybox w-75 h-50" style="display:none;" >

    <h4>Property Notes</h4>
    <textarea class="form-control w-100 h-75" id="p_comments"><?php echo stripslashes($job_row->p_comments); ?></textarea>
    <button type="button" id="update_p_comments_btn" class="btn float-right mt-3">Update</button>	

</div>
<!-- fancy box - END -->	
<style>
 #repair_notes, #tech_comments, #p_comments{
     cursor: pointer;
 }
</style>	
<script>
jQuery(document).ready(function(){

    // repair notes lightbox
    jQuery("#update_repair_notes_fb_link").click(function(){

        $.fancybox.open({
            src  : '#update_repair_notes_fb'
        });

    });

    // update repair notes
    jQuery("#update_repair_notes_btn").click(function(){

        var job_id = <?php echo $this->input->get_post('job_id'); ?>;
        var lightbox_dom = jQuery("#update_repair_notes_fb");
        var repair_notes = lightbox_dom.find("#repair_notes").val();        

        jQuery('#load-screen').show();        
        if( job_id > 0 ){

            jQuery.ajax({
                type: "POST",
                url: "/jobs/ajax_update_repair_notes",
                data: { 
                    job_id: job_id,
                    repair_notes: repair_notes
                }
            }).done(function( ret ){

                jQuery('#load-screen').hide();                  

                //location.reload();
                jQuery("#update_repair_notes_lbl").text(repair_notes); 
                $.fancybox.close();                         			

            });

        }        

    });

    // job notes(tech_comments) lightbox
    jQuery("#update_tech_comments_fb_link").click(function(){

        $.fancybox.open({
            src  : '#update_tech_comments_fb'
        });

    });

    // update job notes(tech_comments)
    jQuery("#update_tech_comments_btn").click(function(){

        var job_id = <?php echo $this->input->get_post('job_id'); ?>;
        var lightbox_dom = jQuery("#update_tech_comments_fb");
        var tech_comments = lightbox_dom.find("#tech_comments").val();        

        jQuery('#load-screen').show();        
        if( job_id > 0 ){

            jQuery.ajax({
                type: "POST",
                url: "/jobs/ajax_update_tech_comments",
                data: { 
                    job_id: job_id,
                    tech_comments: tech_comments
                }
            }).done(function( ret ){

                jQuery('#load-screen').hide();                  

                //location.reload();
                jQuery("#update_tech_comments_lbl").text(tech_comments); 
                $.fancybox.close();                         			

            });

        }        

    });

    // property comments lightbox
    jQuery("#update_p_comments_fb_link").click(function(){

        $.fancybox.open({
            src  : '#update_p_comments_fb'
        });

    });

    // update property comments
    jQuery("#update_p_comments_btn").click(function(){

        var property_id = <?php echo $job_row->property_id; ?>;
        var lightbox_dom = jQuery("#update_p_comments_fb");
        var p_comments = lightbox_dom.find("#p_comments").val();        

        jQuery('#load-screen').show();        
        if( property_id > 0 ){

            jQuery.ajax({
                type: "POST",
                url: "/jobs/ajax_update_property_comments",
                data: { 
                    property_id: property_id,
                    p_comments: p_comments
                }
            }).done(function( ret ){

                jQuery('#load-screen').hide();                  

                //location.reload();
                jQuery("#update_p_comments_lbl").text(p_comments); 
                $.fancybox.close();                         			

            });

        }        

    });


    // service garage script
    jQuery(".service_garage").click(function(){

        var service_garage = jQuery(this).val();

        if( service_garage == 1 ){
            jQuery(".req_heat_alarm_tr").show();
        }else{
            jQuery(".req_heat_alarm_tr").hide();
        }

    });


});
</script>