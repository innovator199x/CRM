<table class="table main-table borderless">

    <tbody>

        <tr>
            <th>Entry Gained via</th>
            <td>
                <select id="entry_gained_via" name="entry_gained_via" class="form-control entry_gained_via" data-db_table_field="entry_gained_via" data-db_table="jobs">
                    <option value="">---</option>	
                    <option value='1' <?php echo ( $job_row->entry_gained_via == 1 )?'selected':null; ?>>Tenant</option>
                    <option value='2' <?php echo ( $job_row->entry_gained_via == 2 )?'selected':null; ?>>Keys from Agency</option>
                    <option value='3' <?php echo ( $job_row->entry_gained_via == 3 )?'selected':null; ?>>Keys Left On-site</option>
                    <option value='4' <?php echo ( $job_row->entry_gained_via == 4 )?'selected':null; ?>>Lock-box</option>
                    <option value='5' <?php echo ( $job_row->entry_gained_via == 5 )?'selected':null; ?>>Met Agent</option>
                    <option value='6' <?php echo ( $job_row->entry_gained_via == 6 )?'selected':null; ?>>Locksmith</option>
                    <option value='7' <?php echo ( $job_row->entry_gained_via == 7 )?'selected':null; ?>>Tenant left door unlocked</option>
                    <option value='-1'<?php echo ( $job_row->entry_gained_via == -1 )?'selected':null; ?>>Other </option>	                                                            
                </select>
                <textarea class="form-control entry_gained_other_text mt-2" name="entry_gained_other_text" id="entry_gained_other_text" data-db_table_field="entry_gained_other_text" data-db_table="jobs" style="display:<?php echo ( $job_row->entry_gained_via == -1 )?'block;':'none;'; ?>"><?php echo stripslashes($job_row->entry_gained_other_text); ?></textarea>
            </td>
        </tr>
        <tr>
            <th>Switchboard Location</th>
            <td>
                <input type="text" name="ss_location" id="ss_location" class="form-control ss_location" data-db_table_field="ss_location" data-db_table="jobs" value="<?php echo $job_row->ss_location; ?>" />            
            </td>
        </tr>
        <tr>
            <th>Levels in Property</th>
            <td>
                <input type="number" id="survey_numlevels" class="form-control" data-db_table_field="survey_numlevels" data-db_table="jobs" value="<?php echo $job_row->survey_numlevels; ?>" />
            </td>
        </tr>
        <tr>
            <th>Number of Bedrooms</th>
            <td>
                <input type="number" name="ps_number_of_bedrooms" class="form-control ps_number_of_bedrooms" id="ps_number_of_bedrooms" data-db_table_field="ps_number_of_bedrooms" data-db_table="jobs" value="<?php echo ( $job_row->ps_number_of_bedrooms > 0 )?$job_row->ps_number_of_bedrooms:null; ?>" />
            </td>
        </tr>
        <tr>
            <th>Current Number of Alarms</th>
            <td>
                <input type="number" name="survey_numalarms" class="form-control survey_numalarms" id="survey_numalarms" data-db_table_field="survey_numalarms" data-db_table="jobs" value="<?php echo $job_row->survey_numalarms; ?>" />
            </td>
        </tr> 
        <tr>
            <th>Ceiling Type</th>
            <td>                                               
                <div class="radio">
                    <input type="radio" name="survey_ceiling" class="form-control survey_ceiling" data-db_table_field="survey_ceiling" data-db_table="jobs" id="ceiling_type1" <?php echo ( $job_row->survey_ceiling == 'CON' )?'checked':null; ?> value="CON" /> 
                    <label class="inline-block" for="ceiling_type1"><?php echo $this->jobs_model->display_ceiling_type_full('CON'); ?></label> 
                </div>  
                <div class="radio">
                    <input type="radio" name="survey_ceiling" class="form-control survey_ceiling" data-db_table_field="survey_ceiling" data-db_table="jobs" id="ceiling_type2" <?php echo ( $job_row->survey_ceiling == 'GYP' )?'checked':null; ?> value="GYP" /> 
                    <label class="inline-block" for="ceiling_type2"><?php echo $this->jobs_model->display_ceiling_type_full('GYP'); ?></label> 
                </div>                                               
            </td>
        </tr>    
        <tr>
            <th>Ladder Required</th>
            <td>
                <div class="radio">
                    <input type="radio" name="survey_ladder" class="form-control survey_ladder" data-db_table_field="survey_ladder" data-db_table="jobs" id="ladder_required1" value="4FT" <?php echo ( $job_row->survey_ladder == '4FT' )?'checked':null; ?> /> 
                    <label class="inline-block" for="ladder_required1">3FT</label> 
                </div>
                <div class="radio">
                    <input type="radio" name="survey_ladder" class="form-control survey_ladder" data-db_table_field="survey_ladder" data-db_table="jobs" id="ladder_required2" value="6FT" <?php echo ( $job_row->survey_ladder == '6FT' )?'checked':null; ?> /> 
                    <label class="inline-block" for="ladder_required2">6FT</label> 
                </div>
                <div class="radio">
                    <input type="radio" name="survey_ladder" class="form-control survey_ladder"  data-db_table_field="survey_ladder" data-db_table="jobs" id="ladder_required3" value="8FT" <?php echo ( $job_row->survey_ladder == '8FT' )?'checked':null; ?> /> 
                    <label class="inline-block" for="ladder_required3">8FT</label> 
                </div>                                              
            </td>  
        </tr>        


        <?php
        // QLD ONLY
        if( $job_row->p_state == 'QLD' ){ ?>
            <!--
            <tr>
                <th>Property already upgraded to interconnected alarms? (QLD ONLY)</th>    
                <td>                        
                    <div class="radio">
                        <input type="radio" name="prop_upgraded_to_ic_sa_survey" id="prop_upgraded_to_ic_sa_survey_yes" class="chk_yes inline-block prop_upgraded_to_ic_sa_survey" data-db_table_field="prop_upgraded_to_ic_sa" data-db_table="property" value="1" <?php echo (  $job_row->prop_upgraded_to_ic_sa == 1 )?'checked':null; ?> />
                        <label class="inline-block" for="prop_upgraded_to_ic_sa_survey_yes">Yes</label>
                    </div>
                    <div class="radio">
                        <input type="radio" name="prop_upgraded_to_ic_sa_survey" id="prop_upgraded_to_ic_sa_survey_no" class="chk_no inline-block prop_upgraded_to_ic_sa_survey" data-db_table_field="prop_upgraded_to_ic_sa" data-db_table="property" value="0" <?php echo (  $job_row->prop_upgraded_to_ic_sa == 0 && is_numeric($job_row->prop_upgraded_to_ic_sa) )?'checked':null; ?> />
                        <label class="inline-block" for="prop_upgraded_to_ic_sa_survey_no">No</label>
                    </div>
                </td>
            </tr>
            -->
            <tr>
                <th>Total Number of alarms required to meet NEW legislation (QLD ONLY)</th>
                <td>
                    <input type="number" name="qld_new_leg_alarm_num" class="form-control qld_new_leg_alarm_num" id="qld_new_leg_alarm_num" data-db_table_field="qld_new_leg_alarm_num" data-db_table="property" value="<?php echo $job_row->qld_new_leg_alarm_num; ?>" <?php echo ( $job_row->qld_new_leg_alarm_num > 0 )?'readonly="readonly"':''; ?> />
                </td>
            </tr>
            <tr>
                <td></td>
                <td>If this property is already upgraded, please input ‘0’ above.</td>
            </tr>
            <?php
        }
        ?>  
                   
    </tbody>

</table>



<table class="table main-table">

    <thead>
        <tr>
            <th>SWMS (Safe Work Method Statements)</th>
        </tr>
    </thead>

    <tbody>
        <tr>
            <td>

                Please assess the worksite before conducting any work. Click <span id="view_swms_lbl" class="text-danger">HERE</span> to view SWMS.

                <table id="swms_table" class="table main-table borderless my-3">
                    <!--
                    <tr target="_blank">
                        <td><label class="inline-block">Working at Heights</label></td>
                        <td>
                            <a href="<?php echo $this->config->item('crm_link'); ?>/pdf_swms.php?id=<?php echo $this->input->get_post('job_id'); ?>&swms_type=heights">
                                <img src="/images/pdf.png" class="inline-block" />
                            </a>                                                            
                        </td>
                    </tr>
                    -->
                    <tr>
                        <td><label class="inline-block">Working at Heights</label></td>
                        <td>
                            <a href="/pdf/swms/?job_id=<?php echo $this->input->get_post('job_id'); ?>&swms_type=heights" target="_blank">
                                <img src="/images/pdf.png" class="inline-block" />
                            </a>                                                            
                        </td>
                    </tr>
                    <tr>
                        <td><label class="inline-block">UV Protection</label></td>
                        <td>
                            <a href="/pdf/swms/?job_id=<?php echo $this->input->get_post('job_id'); ?>&swms_type=uv_protection" target="_blank">
                                <img src="/images/pdf.png" class="inline-block" />
                            </a>   
                        </td>
                    </tr>
                    <tr>
                        <td><label class="inline-block">Likely to involve Disturbing Asbestos</label></td>
                        <td>
                            <a href="/pdf/swms/?job_id=<?php echo $this->input->get_post('job_id'); ?>&swms_type=asbestos" target="_blank">
                                <img src="/images/pdf.png" class="inline-block" />
                            </a> 
                        </td>
                    </tr>
                    <tr>
                        <td><label class="inline-block">Using Corded Power Tools</label></td>
                        <td>
                            <a href="/pdf/swms/?job_id=<?php echo $this->input->get_post('job_id'); ?>&swms_type=powertools" target="_blank">
                                <img src="/images/pdf.png" class="inline-block" />
                            </a> 
                        </td>
                    </tr>
                    <tr>
                        <td><label class="inline-block">Animals on Site</label></td>
                        <td>
                            <a href="/pdf/swms/?job_id=<?php echo $this->input->get_post('job_id'); ?>&swms_type=animals" target="_blank">
                                <img src="/images/pdf.png" class="inline-block" />
                            </a> 
                        </td>
                    </tr>
                    <tr>
                        <td><label class="inline-block">Working with Live Circuits</label></td>
                        <td>
                            <a href="/pdf/swms/?job_id=<?php echo $this->input->get_post('job_id'); ?>&swms_type=live_circuits" target="_blank">
                                <img src="/images/pdf.png" class="inline-block" />
                            </a> 
                        </td>
                    </tr>
                    <tr>
                        <td><label class="inline-block">Covid-19 Protection</label></td>
                        <td>
                            <a href="/pdf/swms/?job_id=<?php echo $this->input->get_post('job_id'); ?>&swms_type=covid_19" target="_blank">
                                <img src="/images/pdf.png" class="inline-block" />
                            </a> 
                        </td>
                    </tr>                                  
                </table>
                
            </td> 
        </tr>           
    </tbody>

</table>

<div class="row">

    <div class="col-md-6 text-left">
        <button type="button" class="btn btn-success techsheet_tab_prev">Previous</button>	                    	
    </div>

    <div class="col-md-6 text-right">
        <button type="button" class="btn techsheet_tab_next">Next</button>                  	
    </div>

</div> 

<script>
jQuery(document).ready(function(){

    /*
    jQuery(".prop_upgraded_to_ic_sa_survey").click(function(){

        var node = jQuery(this);
        var prop_upgraded_to_ic_sa = node.val();

        if( prop_upgraded_to_ic_sa == 1 ){
            jQuery("#tot_num_ic_qld_td").hide();
        }else{
            jQuery("#tot_num_ic_qld_td").show();
        }

    });
    */

    jQuery("#entry_gained_via").change(function(){

        var node = jQuery(this);
        var entry_gained_via = node.val();
        if( entry_gained_via == -1 ){
            jQuery("#entry_gained_other_text").show();
        }else{
            jQuery("#entry_gained_other_text").hide();
        }

    });

});
</script>

