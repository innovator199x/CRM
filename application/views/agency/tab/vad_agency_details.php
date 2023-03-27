    <div class="box-typical-body">
        <div class="row">
            <div class="col-md-12 columns">
               
                    <?php
                    if( $row['status']=='target' ){ 
                        echo "<div class='alert alert-danger'><div id='permission_error'><strong>This Agency is a Target Agency</strong></div></div>";
                    }else if( $row['status']=='deactivated' ){ 
                        echo "<div class='alert alert-danger'><div id='permission_error'><strong>This Agency is a Deactivated Agency</strong></div></div>";
                    }
                    ?>
               
            </div>
        </div>

        <?php  
        $prop_full_add = "{$row['address_1']} {$row['address_2']} {$row['address_3']} {$row['state']} {$row['postcode']}"; 
        if(empty($row['address_1']) || empty($row['address_2']) || empty($row['address_3']) || empty($row['state']) || empty($row['postcode'])){
            $address_label = "No Address";
        } else {
            $address_label = $prop_full_add;
        }
        ?>

        <div class="row">
           
            <div class="col-md-6 columns text-left">


                <div class="row form-group">
                    <div class="col-md-6 column tt_boxes">
                        <label>Agency Name
                        </label>
                        <a class="<?php echo ( $row['priority'] > 0 )?'j_bold':null; ?>" data-auto-focus="false" data-fancybox data-src="#fancybox_agency_name" href="javascript:;"><?php echo $row['agency_name'].( ( $row['priority'] > 0 )?' ('.$row['abbreviation'].')':null ); ?></a>
                    </div>

                    <div class="col-md-6 column tt_boxes">
                        <label>Agency ID</label>
                        <?php echo $row['agency_id']; ?>

                    </div>
                </div>

                <div class="row form-group">
                    <div class="col-md-6 column tt_boxes">
                        <div class="top_agency_address left">
                            
                             <label><span class="left">Address &nbsp;</span>
                              
                                <?php
                                    if($agency_addresses_q->num_rows()>0){

                                        foreach($agency_addresses_q->result_array() as $agency_addresses_row){
                                            if($agency_addresses_row['type']==1){
                                                echo "<span class='fa fa-envelope left' style='margin-right:5px;'></span>  ";
                                            }
                                            if($agency_addresses_row['type']==2){
                                                echo "<span class='fa fa-key left' style='margin-right:5px;'></span>  ";
                                            }
                                        }

                                    }
                                ?>
                               
                                </label>
                           <div style="clear:both;"></div>
                           <a data-auto-focus="false" data-fancybox data-src="#fancybox_agency_address" href="javascript:;">
                               <?php echo $address_label; ?>
                        </a>
                        </div>
                    </div>
                    <div class="col-md-6 column tt_boxes">
                        <div class="left top_agency_region">
                            <label>Region</label>
                            <a data-auto-focus="false" data-fancybox data-src="#fancybox_agency_address" href="javascript:;"><?php echo $row['postcode_region_name']; ?></a>
                        </div>
                    </div>
                </div>

                <div class="row form-group">
                    <div class="col-md-6 column tt_boxes">
                        <div class="left">
                            <label>Franchise Group</label>
                            <a data-auto-focus="false" data-fancybox data-src="#fancybox_agency_franchise_group" href="javascript:;"><?php echo ($row['franchise_name']!='') ? $row['franchise_name'] : 'No Data'; ?></a>
                        </div>
                    </div>
                    <div class="col-md-6 column tt_boxes">
                        <div class="left">
                            <label>Sales Rep</label>
                            <a data-auto-focus="false" data-fancybox data-src="#fancybox_agency_sales_rep" href="javascript:;"><?php echo ($row['FirstName']!="") ? "{$row['FirstName']} {$row['LastName']}" : "No Data"; ?></a>
                        </div>
                    </div>
                </div>

                <div class="row form-group">
                    <div class="col-md-6 column tt_boxes">
                        <div class="left">
                            <label>Office Hours</label>
                            <a data-auto-focus="false" data-fancybox data-src="#fancybox_agency_hours" href="javascript:;"><?php echo ($row['agency_hours']!="")? $row['agency_hours'] : "<em>Add office hours</em>"; ?></a>
                        </div>
                    </div>
                    <div class="col-md-6 column tt_boxes">
                        <div class="left">
                            <label>Status</label>
                            <a data-auto-focus="false" class="text-capitalize agency_status_text" data-fancybox data-src="#fancybox_agency_status" href="javascript:;"><?php echo $row['status'] ?></a>
                        </div>
                    </div>
                </div>


                <div class="row form-group">
                    <div class="col-md-6 column tt_boxes">
                        <div class="left">
                            <label>Agency Priority</label>
                            
                            <a data-auto-focus="false" class="text-capitalize agency_status_text" data-fancybox data-src="#fancybox_agency_high_touch" href="javascript:;">
                                <!--<span><?php //echo ($row['priority'] == 1) ? "High Touch Agency" : "Regular Agency"; ?></span>-->
                                <span>
                                <?php 
                                if($row['priority'] == 0){
                                    echo "Regular Agency"; 
                                }
                                else{
                                    echo $row['priority_full_name']." Agency"; 
                                }
                                ?></span>
                            </a>

                        </div>
                    </div>    
                    <div class="col-md-6 column tt_boxes">
                        <div class="left">
                            <label>Health Check</label>
                            <a target="_blank" href="<?php echo $this->config->item("crmci_link"); ?>/agency/agency_health_check/<?php echo $agency_id; ?>">Generate Report</a>
                        </div>
                    </div>                
                </div>


                <!-- FANCY BOXES -->

                <div id="fancybox_agency_api_sec" style="display:none;">
                    <section class="card card-blue-fill">
                        <header class="card-header">API's</header>
                        <div class="card-block">
                            <table class="table table-hover main-table">
                                <thead>
                                    <tr>
                                        <th>Software</th>
                                        <th>Available to Connect</th>
                                        <th>API Active</th>
                                        <th>Marker Name</th>
                                        <th>Marker ID</th>
                                        <th>Edit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($api) > 0){
                                    foreach($api as $api_row){ ?>
                                        <tr>
                                            <td>
                                                <?php echo $api_row['api_name'] ?>
                                            </td>
                                            <td>
                                                <?php echo ( $api_row['active'] == 1 )?'<span class="text-green">Yes</span>':'<span class="text-red">No</span>'; ?>
                                            </td>
                                            <td>
                                                <?php                                                     
                                                    if( $api_row['agency_api_id'] == 5 ){ // console cloud, using webhooks

                                                        // check if agency has API key stored
                                                        $cak_sql_str = "
                                                        SELECT COUNT(`id`) AS cak_count
                                                        FROM `console_api_keys`
                                                        WHERE `agency_id` = {$agency_id}
                                                        ";
                                                        $cak_sql = $this->db->query($cak_sql_str);

                                                        echo ( $cak_sql->row()->cak_count > 0 )?'<span class="text-green">Yes</span>':'<span style="color:red;">No</span>';

                                                    }else{ // other API using agency tokens

                                                        echo ( $api_row['agency_api_token_id'] > 0 )?'<span class="text-green">Yes</span>':'<span style="color:red;">No</span>';

                                                    }                                                   
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    if ($api_row['agency_api_id'] == 1) { // PME
                                                        $contact_json = $this->agency_model->agency_api_get_contact($agency_id, $row['pme_supplier_id']);
                                                        echo $contact_json->Contact->Reference;
                                                    }else if($api_row['agency_api_id'] == 4){ // Palace
                                                        $palace_diary_json = $this->palace_model->get_palace_diary_by_id($agency_id, $row['palace_diary_id']);
                                                        echo $palace_diary_json[0]->DiaryGroupDescription;
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    if ($api_row['agency_api_id'] == 1) { // PMe
                                                        echo $row['pme_supplier_id'];
                                                    } else if ($api_row['agency_api_id'] == 4){ // Palace
                                                        echo $row['palace_diary_id'];
                                                    }
                                                ?>
                                            </td>
                                            <td class="action_div">
                                                <?php if( $this->system_model->can_edit_vad_api() == true ) {?>
                                                <a data-toggle="tooltip" title=""  data-fancybox data-src="#api_edit_facybox_<?php echo $api_row['api_integration_id']; ?>" class="btn_edit fancybox_btn action_a" data-original-title="Edit"><i class="font-icon font-icon-pencil"></i></a> | 
                                                <a data-api_token_id="<?php echo $api_row['agency_api_token_id']; ?>" data-agency_id="<?php echo $agency_id; ?>" data-api_integration_id="<?php echo $api_row['api_integration_id'] ?>" data-api_id="<?php echo $api_row['connected_service'] ?>" data-toggle="tooltip" title="" class="<?php echo ( $api_row['agency_api_token_id'] > 0 ) ? 'remove_agency_token_btn' : 'btn_delete' ?> action_a" data-original-title="<?php echo ( $api_row['agency_api_token_id'] > 0 ) ? 'Remove API Token' : 'Remove API' ?>"><span class="glyphicon glyphicon-trash"></span></a>
                                                <?php } ?>
                                                <div style="display:none;" class="api_fancybox" id="api_edit_facybox_<?php echo $api_row['api_integration_id']; ?>">
                                                    <h4>Edit <?php echo $api_row['api_name'] ?></h4>
                                                    <div class="form-group">
                                                        <label class="form-label">Software</label>
                                                        <select name="edit_api_connected_service" title="Connected Service" class="edit_api_connected_service form-control">
                                                            <option value="">----</option>		
                                                            <?php foreach($agency_api as $agency_api_row){ ?>
                                                                <option value="<?php echo $agency_api_row['agency_api_id']; ?>" <?php echo ( $agency_api_row['agency_api_id'] == $api_row['connected_service'] )?'selected="selected"':''; ?>>
                                                                    <?php echo $agency_api_row['api_name']; ?>
                                                                </option>
                                                            <?php  } ?>									
                                                        </select>
                                                        <input type="hidden" name="og_edit_api_connected_service" class="og_edit_api_connected_service" value="<?php echo $api_row['connected_service']; ?>">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">Available to Connect</label>
                                                            <select name="edit_api_status" title="Connected Service" class="edit_api_status form-control">								
                                                                <option value="1" <?php echo ( $api_row['active'] == 1 )?'selected="selected"':''; ?>>Yes</option>
                                                                <option value="0" <?php echo ( $api_row['active'] == 0 )?'selected="selected"':''; ?>>No</option>																					
                                                            </select>
                                                            <input type="hidden" name="og_edit_api_status" class="og_edit_api_status" value="<?php echo $api_row['active']; ?>">
                                                    </div>
                                                    <div class="form-group row">
                                                        <input type="hidden" class="api_integration_id" value="<?php echo $api_row['api_integration_id'] ?>">
                                                        <input type="hidden" class="api_id" value="<?php echo $api_row['connected_service'] ?>">
                                                        <input type="hidden" class="agency_api_token_id" value="<?php echo $api_row['agency_api_token_id'] ?>">
                                                        <div class="col-md-6 columns text-left">  <button data-fancybox-close="btn_cancel_agency_high_touch" class="btn btn-danger">Cancel</button></div>
                                                        <div class="col-md-6 columns text-right"><button class="btn btn_update_api_integ">Update</button></div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php 
                                    } }else{
                                        echo "<tr><td colspan='6'>No Data</td></tr>";
                                    } ?>
                                </tbody>
                            </table>

                            <div class="vad_cta_box">
                                <div class="row">
                                    <div class="col-md-6 columns text-left">
                                    <?php 
                                        if($user_ctype == "GLOBAL" || $user_ctype == "FULL ACCESS"){
                                    ?>
                                            <button id="btn_unlink_connected_api_prop" class="btn btn-danger">Unlink Connected API Properties</button>
                                    <?php
                                        }
                                    ?>
                                    </div>
                                    <div class="col-md-6 columns text-right"> <button data-fancybox data-src="#add_api_fancybox" class="btn">Add API integration</button></div>
                                </div>
                               
                                <!-- Add API fancybox -->
                                <div style="display:none" id="add_api_fancybox">
                                    <h4>Add API integration</h4>
                                    <?php echo form_open("/agency/add_agency_api_integration","id=add_api_form"); ?>
                                        <div class="form-group">
                                            <label class="form-label">Software</label>
                                            <select name="connected_service" id="api_connected_service" title="Connected Service" class="form-control connected_service">
                                                <option value="">----</option>
                                                <?php foreach($agency_api as $agency_api_row){ ?>
                                                    <option value="<?php echo $agency_api_row['agency_api_id']; ?>" >
                                                        <?php echo $agency_api_row['api_name']; ?>
                                                    </option>
                                                <?php  } ?>		
                                            </select>
                                        </div>
                                        <div class="row">
                                            <input type="hidden" name="agency_id" value="<?php echo $agency_id; ?>" />
                                            <div class="col-md-6 columns text-left">  <button data-fancybox-close="" class="btn btn-danger">Cancel</button></div>
                                            <div class="col-md-6 columns text-right">  <button type="button" id="btn_save_api_integ" class="btn">Save</button></div>
                                        </div>
                                    </form>
                                </div>
                                <!-- Add API fancybox end -->

                            </div>
                            <div class="api_checkbox_box">
                                <div class="form-group">
                                    <div class="left api_billable_checkbox_section">
                                        <div class="checkbox">
                                            <?php
                                                $is_api = ($row['api_billable']==1)? "checked='checked'" : null ;
                                            ?>
                                            <input <?php echo ( $this->system_model->can_edit_vad_api() == true )?null:"disabled='disabled'"; ?> <?php echo $is_api; ?> class="prop_chk" name="api_billable" type="checkbox" id="api_billable" value="1">
                                            <label for="api_billable">Push Invoices via API instead of email?</label>
                                        </div>
                                        <input type="hidden" name="og_api_billable" id="og_api_billable" class="og_api_billable" value="<?php echo $row['api_billable']; ?>"/>
                                    </div>
                                    <div> <span style="display:none;" id="api_billable_green_tick" class="fa fa-check-square text-green"></span></div>
                                </div>
                                <div class="clear_b"></div>
                                <div class="form-group">
                                    <div class="left no_bulk_match_green_tick_checkbox_section">
                                        <div class="checkbox">
                                            <?php
                                                $is_no_bulk_match = ($row['no_bulk_match']==1)? "checked='checked'" : null ;
                                            ?>
                                            <input <?php echo ( $this->system_model->can_edit_vad_api() == true )?null:"disabled='disabled'"; ?> <?php echo $is_no_bulk_match; ?> class="prop_chk" name="no_bulk_match" type="checkbox" id="no_bulk_match" value="1">
                                            <label for="no_bulk_match">Add 'Shared Portfolio' warning on Bulk Match?</label>
                                        </div>
                                        <input type="hidden" name="og_no_bulk_match" id="og_no_bulk_match" class="og_no_bulk_match" value="<?php echo $row['no_bulk_match']; ?>"/>
                                    </div>
                                    <div> <span style="display:none;" id="no_bulk_match_green_tick" class="fa fa-check-square text-green"></span></div>
                                </div>
                                <div class="clear_b"></div>
                            </div>

                        </div>
                    </section>
                </div>

                <div id="fancybox_agency_details_sec" style="display:none;">
                        <?php 
                            echo form_open("/agency/update_agency/{$agency_id}/{$tab}","id=vad_form"); 
                            $hidden_input_data_agency_id = array(
                                'type'  => 'hidden',
                                'name'  => 'agency_id',
                                'id'    => 'agency_id',
                                'value' => $agency_id,
                                'class' => 'agency_id'
                            );
                            echo form_input($hidden_input_data_agency_id);
                        ?>
                            <section class="card card-blue-fill">
                                <header class="card-header">Agency Details</header>
                                <div class="card-block">

                                    <div class="form-group row">
                                        <label class="col-sm-4 form-control-label">Legal Name</label>
                                        <div class="col-sm-8">
                                            <p class="form-control-static">
                                            <input class="form-control" name="legal_name" id="legal_name" title="Legal Name" type="text" value="<?php echo $row['legal_name']; ?>">
                                            <input class="form-control" name="og_legal_name" id="og_legal_name" type="hidden" value="<?php echo $row['legal_name']; ?>">
                                            </p>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-4 form-control-label"><?php echo ($this->config->item('country')==1)?'ABN Number':'GST Number'; ?></label>
                                        <div class="col-sm-8">
                                            <p class="form-control-static">
                                            <input class="form-control" name="abn" id="abn" type="text" title="ABN Number" value="<?php echo $row['abn']; ?>">
                                            <input class="form-control" name="og_abn" id="og_abn" type="hidden" value="<?php echo $row['abn']; ?>">
                                            </p>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-4 form-control-label">Team Meeting</label>
                                        <div class="col-sm-8">
                                            <p class="form-control-static">
                                            <input class="form-control" title="Team Meeting" name='team_meeting' id='team_meeting' type="text" value="<?php echo $row['team_meeting']; ?>">
                                            <input class="form-control" name='og_team_meeting' id='og_team_meeting' type="hidden" value="<?php echo $row['team_meeting']; ?>">
                                            </p>
                                        </div>
                                    </div>

                                </div>
                            </section>

                            <section class="card card-blue-fill" style="min-width:700px;">
                                <header class="card-header">Agency/Accounts Emails</header>
                                <div class="card-block">
                                    <div class="row form-group">
                                        <div class="col-md-6 columns">
                                            <label class="form-label">Agency Emails</label>
                                            <textarea style="height:165px;" title="Agency Emails" name='agency_emails'  id='agency_emails' class='form-control formtextarea' title='Agency Emails'><?php echo $row['agency_emails']; ?></textarea>
                                            <small><strong>(Reports, Key Sheet)</strong> (one per line)</small>
                                            <input type="hidden" name="og_agency_emails" value="<?php echo $row['agency_emails']; ?>">
                                        </div>
                                        <div class="col-md-6 columns">
                                            <label class="form-label">Accounts Emails</label>
                                            <textarea style="height:165px;" title="Accounts Emails" name='account_emails' id='account_emails' class='form-control formtextarea' title='Accounts Emails'><?php echo $row['account_emails']; ?></textarea>
                                            <small><strong>(Invoices, Certificates)</strong> (one per line)</small>
                                            <input type="hidden" name="og_account_emails" value="<?php echo $row['account_emails']; ?>">
                                        </div>
                                    </div>
                                </div>
                            </section>
                            <div class="row">
                                <div class="col-md-6 column text-left">
                                <a href="/agency/send_sales_emails/<?php echo $agency_id; ?>" class="btn" id="btn_send_sales_emails">Send Sales Emails</a>
                                </div>
                                <div class="col-md-6 column">
                                <div class="text-right"><button type="button" class="btn" id="btn_update_additional_details">Update</button></div>
                                </div>
                            </div>
                        </form>                                        
                </div>

                <!-- fancy boxes -->
                <div id="fancybox_agency_name" style="display:none;">

                    <section class="card card-blue-fill">
                        <header class="card-header">Agency Name</header>
                        <div class="card-block">
                            <input class="form-control" name="agency_name" id="agency_name" title="Agency Name" type="text" value="<?php echo $row['agency_name'] ?>">
                            <input class="form-control" id="og_agency_name" name="og_agency_name" type="hidden" value="<?php echo $row['agency_name'] ?>">
                        </div>
                    </section>

                    <div class="form-group text-right">
                        <button type="button" class="btn" id="btn_update_agency_name">Update</button>
                    </div>                       
                </div>

                <div id="fancybox_agency_status" style="display:none;">
                    <section class="card card-blue-fill">
                        <header class="card-header">Status</header>
                        <div class="card-block">
                            <div class="form-group">
                            <select id="statuss" class="form-control statuss" name="status" title="Agency Status">
                                <?php
                                //if( $row['status']=='active' ){ 
                                    ?>
                                    <option <?php echo ($row['status']=='active')?'selected="selected"':''; ?> value='active'>Active</option>
                                <?php	
                                //}
                                ?>								
                                <option <?php echo ($row['status']=='target')?'selected="selected"':''; ?> value='target'>Target</option>
                                <option <?php echo ($row['status']=='deactivated')?'selected="selected"':''; ?> value='deactivated'>Deactivated</option>
                            </select>
                            <input id="og_status" type="hidden" name="og_status" value="<?php echo $row['status'] ?>">
                            </div>
                        </div>
                    </section>

                    <div class="deactivate_agency_reason_div" style="display:<?php echo ( $row['status'] == 'deactivated' || $row['status'] == 'target' )?'block':'none' ?>;">
                        <section class="card card-red-fill">
                            <header class="card-header"><span id="deactivate_box_header"><?php echo ucfirst($row['status']); ?></span> Details</header>
                            <div class="card-block">
                                <div class="row">
                                    <div class="col-md-6 columns">
                                        <div class="form-group">
                                            <label class="form-label">Active Properties with SATS</label>
                                            <input class="form-control" type="text" id="active_prop_with_sats" name="active_prop_with_sats" value="<?php echo $row['active_prop_with_sats'] ?>">
                                            <input class="form-control" type="hidden" name="og_active_prop_with_sats" value="<?php echo $row['active_prop_with_sats'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6 columns">
                                        <div class="form-group">
                                            <label class="form-label">Changing To</label>
                                            <select id="agency_using" name="agency_using" title="Currently Using" class="form-control agency_using">
                                                <option value="0">----</option>
                                                <?php foreach($gency_using_q as $au){ ?>
                                                <option value="<?php echo $au['agency_using_id']; ?>" <?php echo($row['agency_using_id']==$au['agency_using_id'])?'selected="selected"':''; ?>><?php echo $au['name']; ?></option>
                                                <?php  } ?>
                                            </select>
                                            <input id="og_agency_using" type="hidden" name="og_agency_using" value="<?php echo $row['agency_using_id']; ?>">
                                                </div>
                                    </div>

                                    <div class="col-md-12 columns">
                                        <div class="form-group">
                                            <label class="form-label">Reason they Left</label>
                                            <select class="form-control" id="reason_they_left" name="reason_they_left">
                                                <option value="">---Select Reason---</option>
                                                <?php
                                                // get leaving reason                                                
                                                $lr_sql = $this->db->query("
                                                SELECT *
                                                FROM `leaving_reason`
                                                WHERE `active` = 1
                                                AND `display_on` IN(1,4)
                                                ORDER BY `reason` ASC
                                                ");   
                                                foreach( $lr_sql->result() as $lr_row ){ ?>
                                                    <option value="<?php echo $lr_row->id; ?>"><?php echo $lr_row->reason; ?></option> 
                                                <?php
                                                }                                         
                                                ?>  
                                                <option value="-1">Other</option>                                                                                                                                             
                                            </select>
                                        </div>
                                    </div>


                                    <div id="other_reason_div" class="col-md-12 columns">
                                        <div class="form-group">
                                            <label class="form-label">Other Reason</label>
                                            <textarea class="form-control addtextarea" id="other_reason" name="other_reason"></textarea>
                                            <input type="hidden" name="og_deactivate_reason" value="<?php echo $row['deactivated_reason']; ?>">
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </section>
                    </div>

                    <div class="row">
                        <div class="col-md-6 columns text-left">
                            <button id="btn_hard_delete" class="btn btn-danger btn_hard_delete" type="button">Delete Agency</button>
                        </div>
                        <div class="col-md-6 columns text-right">
                            <button class="btn" id="btn_update_agency_status">Update</button>
                        </div>
                    </div>
                </div>


                <div id="fancybox_agency_hours" style="display:none;">
                    <section class="card card-blue-fill">
                        <header class="card-header">Agency Hours</header>
                        <div class="card-block">
                            <div class="form-group">
                                <input class="form-control" title="Agency Hours" name='agency_hours' id='agency_hours' type="text" value="<?php echo $row['agency_hours']; ?>">
                                <input class="form-control" name='og_agency_hours' id='og_agency_hours' type="hidden" value="<?php echo $row['agency_hours']; ?>">
                            </div>  
                        </div>
                    </section>
                    <div class="form-group text-right">           
                        <button class="btn" id="btn_update_agency_office_hour">Update</button>  
                    </div>
                </div>

                <div id="fancybox_agency_franchise_group" style="display:none;">
                    <section class="card card-blue-fill">
                        <header class="card-header">Franchise Group</header>
                        <div class="card-block">
                            <div class="form-group">
                                <select id="franchise_group" name="franchise_group" class="form-control" title="Franchise Group">
                                    <option value="">----</option>
                                    <?php
                                    foreach($fg_sql->result_array() as $fg){ ?>
                                        <option value="<?php echo $fg['franchise_groups_id'] ?>" <?php echo ($row['franchise_groups_id']==$fg['franchise_groups_id'])?'selected="selected"':''; ?>><?php echo $fg['name'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>	
                                <input type="hidden" id="og_franchise_group" name="og_franchise_group" value="<?php echo ($row['franchise_groups_id']==0)?null:$row['franchise_groups_id']; ?>">
                            </div>
                        </div>
                    </section>
                   <div class="text-right"> <button class="btn" id="btn_update_agency_franchice">Update</button> </div>
                </div>

                <div id="fancybox_agency_sales_rep" style="display:none;">
                    <section class="card card-blue-fill">
                            <header class="card-header">Sales Rep</header>
                            <?php 
                                if(COUNTRY==1){
                                    $allowed_user = array(2191); // Ashlee Ryan
                                }else{
                                    $allowed_user = array(2124); // Ashley Orchard
                                }
                            ?>

                            <?php if( $user_type == 2 || $row['status']=='deactivated' || in_array($staff_id, $allowed_user) ){ 
                                $diable_attr = 'disabled';
                            }else{
                                $diable_attr = NULL;
                            }?>

                            <div class="card-block">
                                <div class="form-group">
                                <select class="form-control" name="salesrep" id="salesrep" title="Sales Rep" <?php echo $diable_attr; ?> >
                                    <option value="">-- Select a Sales Rep --</option>
                                        <?php foreach($salesrep_sql->result_array() as $salesrep){ ?>
                                        <option value="<?php echo $salesrep['staff_accounts_id'] ?>" <?php echo ($salesrep['staff_accounts_id']==$row['salesrep'])?'selected="selected"':''; ?>><?php echo $salesrep['FirstName'] .' '. $salesrep['LastName'] ?></option>
                                        <?php } ?>
                                    </select>
                                    <input id="og_salesrep" type="hidden" name="og_salesrep" value="<?php echo ($row['salesrep']==0)?null:$row['salesrep']; ?>">
                                </div>
                            </div>
                    </section>
                    <div class="text-right"><button class="btn" id="btn_update_agency_salesrep">Update</button></div>
                </div>


                <div id="fancybox_agency_address" style="display:none;">
                    <section class="card card-blue-fill">
                        <header class="card-header">
                            <div class="row">
                                <div class="col-md-9" style="margin-top:9px;"> <span >Address </span> </div>
                                <div class="col-md-3">
                                    <select id="agency_addresses" name="agency_addresses" class="form-control">
                                        <option value="">Default Address</option>
                                        <option value="1">Mailing Address</option>
                                        <option value="2">Key Address</option>
                                    </select>
                                </div>
                            </div> 
                    </header>
                        <div class="card-block">
                           
                            <div id="ajax_address_div">
                            <div class="default_address">
                                <div class="form-group">
                                    <label class="form-label">Google Address Bar</label>
                                    <input type='text' name='fullAdd' id='fullAdd' class='form-control vw-pro-dtl-tnt short-fld'  value="<?php echo $prop_full_add; ?>" />
                                    <input type='hidden' name='og_fullAdd' id='og_fullAdd' class='form-control vw-pro-dtl-tnt short-fld'  value="<?php echo $prop_full_add; ?>" />
                                </div>
                                <div class="row">
                                    <div class="col-md-2 columns">
                                        <div class="form-group">
                                            <label class="form-label">No.</label>
                                            <input type='text' name='address_1' id='address_1' value="<?php echo $row['address_1'] ?>" class='form-control vw-pro-dtl-tnt short-fld'>
                                        </div>
                                    </div>
                                    <div class="col-md-4 columns">
                                        <div class="form-group">
                                            <label class="form-label">Street</label>
                                            <input type='text' name='address_2' id='address_2' value="<?php echo $row['address_2'] ?>" class='form-control vw-pro-dtl-tnt long-fld streetinput'>
                                        </div>
                                    </div>
                                    <div class="col-md-2 columns">
                                        <div class="form-group">
                                            <label class="form-label">Suburb</label>
                                            <input type='text'  name='address_3' id='address_3' value="<?php echo $row['address_3'] ?>" class='form-control vw-pro-dtl-tnt big-fld'>
                                        </div>
                                    </div>
                                    <div class="col-md-2 columns">
                                        <div class="form-group">
                                            <?php if($this->config->item('country') == 1){ ?>
                                                <label class="form-label">State</label>
                                                <select class="form-control" id="state" name="state">
                                                    <option value="">----</option>
                                                    <?php
                                                    foreach($getCountryState->result_array() as $state){ ?>
                                                        <option value='<?php echo $state['state']; ?>' <?php echo ($state['state']==$row['state'])?'selected="selected"':''; ?>><?php echo $state['state']; ?></option>
                                                    <?php	  
                                                    }
                                                    ?>
                                                </select>
                                            <?php }else{?>
                                                <label class="form-label">Region</label>
                                                <input class="form-control" type="text" name="state" id="state" value="<?php echo $row['state']; ?>">
                                            <?php } ?>
                                                <input type="hidden" name="og_state" id="og_state" value="<?php echo $row['state'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-2 columns">
                                        <div class="form-group">
                                            <label class="form-label">Poscode</label>
                                            <input class="form-control" name='postcode' id='postcode' type="text" value="<?php echo $row['postcode']; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 columns">
                                        <div class="form-group">
                                            <label class="form-label">
                                                    <?php echo $this->customlib->getDynamicRegionViaCountry($this->config->item('country')); ?>
                                            </label>
                                            <?php 
                                                if( $row['postcode_region_id']!="" ){ ?>
                                                    <input class="form-control" readonly="readonly" name='postcode_region_name' id='postcode_region_name' type="text" value="<?php echo $row['postcode_region_name']; ?>">
                                                    <input class="form-control" name='og_postcode_region_name' id='og_postcode_region_name' type="hidden" value="<?php echo $row['postcode_region_name']; ?>">
                                                <?php	
                                                }else{
                                                    echo "NO region set up for this postcode";
                                                }
                                            ?>
                                        </div>
                                    </div>

                                </div>
                        </div>
                            </div>                    
                        </div>
                    </section>

                    <div class="text-right">
                        <button style="display:none;" class="btn btn-danger" id="btn_delete_agency_address">Delete</button>
                        &nbsp;&nbsp;
                        <button class="btn btn-primmary" id="btn_update_agency_address">Update</button>
                        &nbsp;&nbsp;
                        <button style="display:none;" class="btn btn-primary" id="btn_add_gaency_address">Add</button>
                        <button style="display:none;" class="btn btn-primary" id="btn_add_key_address">Add Key Address</button>
                    </div>
                </div>

                <div id="fancybox_agency_high_touch" style="display:none;">
                    <section class="card card-blue-fill">
                        <header class="card-header">Agency Priority</header><div class=""></div>
                        <div class="card-block">
                            <div class="form-group">
                                <!--<input type="checkbox" id="priority" name="priority" value="<?//= $row['priority'] == 1) ? 1 : 0 ?>" <?//=( $row['priority'] == 1 )?'checked="checked"':null; ?> /> HT Marker -->    
                                <label class="form-label">Please select the agency priority</label>
                                <select name="priority-selected" id="priority-selected" title="Options" class="form-control">
                                    <?php foreach($priority_fullname as $priority_row){ 
                                        if($row['priority_full_name'] == $priority_row->priority_full_name){
                                            $selected = "selected";
                                        }    
                                        else{
                                            $selected = "";
                                        }
                                    ?>
                                        <option value="<?php echo $priority_row->priority; ?>" <?php echo $selected; ?>>
                                            <?php echo $priority_row->priority_full_name; ?>
                                        </option>
                                    <?php  } ?>	
                                </select>     
                            </div> 
                            <div class="form-group">
                                <label>Marked Date: <a href="javascript::void(0)"><?php echo ($row['priority_date_added']->modified_date != null ? date('Y-m-d', strtotime($row['priority_date_added']->modified_date)) : ""); ?></a></label>
                            </div>
                            <div class="form-group">
                                <label>Marked Time: <a href="javascript::void(0)"><?php echo ($row['priority_date_added']->modified_date != null ? date('H:i:s', strtotime($row['priority_date_added']->modified_date)) : ""); ?></a></label>
                            </div>                           
                            <div class="form-group">
                                <label for="priority_reason"><b>Reason:</b></label><br/>
                                <textarea id="priority_reason" class="form-control" rows="10" cols="50" name="priority_reason" required><?= $row['priority_reason'] ?></textarea>
                                <div class="error_panel">
                                    <span style="color:red;" class="hide">Reason field is required!</span>
                                </div>
                            </div>  
                        </div>
                    </section>
                    <div class="form-group text-right">           
                        <button name="unmark" class="btn btn-danger agency-priority" id="btn_cancel_agency_high_touch" label="unmark" style="float: left;">Unmark</button>
                        <button name="save" class="btn agency-priority" label="save" id="btn_update_agency_high_touch">Save</button>
                    </div>
                </div>

                <div id="fancybox_agency_high_touch_send_mail" style="display:none;">
                    <section class="card card-blue-fill">
                        <header class="card-header">High Touch Send Email</header><div class=""></div>
                        <div class="card-block">
                            <div class="form-group">
                                <label for="sats_info_label">Email</label>
                                <input id="sats_info_email" type="text" class="form-control" name="sats_info_email" />
                            </div>
                            <div class="form-group">
                                <label for="sats_info_label">Email</label>
                                <input id="sats_sales_email" type="text" class="form-control" name=="sats_sales_email" />
                            </div>
                        </div>
                    </section>
                    <div class="form-group text-right">
                        <button class="btn" id="btn_update_agency_high_touch">Send Email</button>
                        <!-- <button class="btn" id="btn_cancel_agency_high_touch">Cancel</button> -->
                    </div>
                </div>

                <!-- FANCY BOXES END -->
    
                
            </div>

            <div class="col-md-6 columns">

                <div class="row">

                    <div class="col-md-6 columns">

                            <div class="row form-group">
                                <div class="col-md-12 columns">
                                    <div class="left tt_boxes text-left">
                                        <label>Trust Account Software</label>
                                        <a data-auto-focus="false" data-fancybox data-src="#fancybox_agency_trus_account_software_sec" href="javascript:;"><?php echo ($row['tsa_name']!="")?$row['tsa_name']:'No Data'; ?></a>
                                    </div>
                                </div>               
                            </div>

                            <div class="row form-group">
                                <div class="col-md-12 columns">
                                    <div class="left tt_boxes text-left">
                                        <label>Maintenance Program</label>         
                                        <a data-auto-focus="false" data-fancybox data-src="#fancybox_maintenance_program_sec" href="javascript:;"><?php echo ($sel_m['name']!="")?$sel_m['name']:'None'; ?></a>
                                    </div>
                                </div>
                            </div>

                            <div class="row form-group">
                                <div class="col-md-12 columns">
                                    <div class="left tt_boxes text-left">
                                        <label>Emails & Legal</label>
                                        <a data-auto-focus="false" data-fancybox data-src="#fancybox_agency_details_sec" href="javascript:;">Click Here</a>
                                    </div>
                                </div>
                            </div>

                            <div class="row form-group">
                                <div class="col-md-12 columns">
                                    <div class="left tt_boxes text-left">
                                        <label>API's</label>         
                                        <?php if(count($api) > 0){
                                            $api_name_arr = [];
                                            foreach($api as $api_row){

                                                if($api_row['agency_api_token_id']>0){
                                                    $api_name_arr[] = $api_row['api_name'];
                                                }
                                                
                                            }
                                            $api_names =  implode(", ", $api_name_arr);
                                            if(!empty($api_name_arr)){
                                        ?>
                                            <a data-auto-focus="false" data-fancybox data-src="#fancybox_agency_api_sec" href="javascript:;"><?php echo $api_names; ?></a>
                                        <?php
                                            }else{
                                                echo '<a data-auto-focus="false" data-fancybox data-src="#fancybox_agency_api_sec" href="javascript:;">No Active API</a>';
                                            }
                                        ?>
                                    
                                        <?php
                                        }else{
                                            echo '<a data-auto-focus="false" data-fancybox data-src="#fancybox_agency_api_sec" href="javascript:;">No API</a>';
                                        } ?>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="row form-group">
                                <div class="col-md-12 columns">
                                    <div class="left tt_boxes text-left">
                                        <label>From Other Company</label>
                                        <a data-auto-focus="false" data-fancybox data-src="#fancybox_from_other_company" href="javascript:;"><?php echo ( $afoc_row->sac_id > 0 )?$afoc_row->company_name:'No Data'; ?></a>
                                    </div>
                                </div>               
                            </div>
                        
                        


                            <div id="fancybox_agency_trus_account_software_sec" style="display:none;">
                                <section class="card card-blue-fill">
                                    <header class="card-header">Trust Account Software</header>
                                    <div class="card-block">           
                                        <div class="form-group row">
                                            <label class="col-sm-4 form-control-label">Trust Acct. Software</label>
                                            <div class="col-sm-8">
                                                <p class="form-control-static">
                                                    <select name="trust_acc_soft" id="trust_acc_soft" title="Trust Acct. Software" class="form-control trust_acc_soft">
                                                        <option value="">----</option>
                                                        <?php
                                                        foreach($tas_sql as $tsa_row){ ?>
                                                            <option value="<?php echo $tsa_row['trust_account_software_id'] ?>" <?php echo ( $tsa_row['trust_account_software_id'] == $row['trust_account_software'] )?'selected="selected"':''; ?>><?php echo $tsa_row['tsa_name']; ?></option>
                                                        <?php
                                                        }
                                                        ?>							
                                                    </select>
                                                    <input type="hidden" id="og_trust_acc_soft" name="og_trust_acc_soft" value="<?php echo ($row['trust_account_software']==0) ?null:$row['trust_account_software']; ?>" >
                                                </p>
                                            </div>
                                        </div>

                                        <div id="tas_connected_div"  style="<?php echo ( $row['trust_account_software'] > 0 )?'display: block;':'display: none;' ?>">
                                            <div class="form-group row">
                                                <label class="col-sm-4 form-control-label">Trust Account Agency ID</label>
                                                <div class="col-sm-8">
                                                    <p class="form-control-static">
                                                        <input class="form-control" name='propertyme_agency_id' id='propertyme_agency_id' type="text" value="<?php echo $row['propertyme_agency_id']; ?>">
                                                        <input class="form-control" name='og_propertyme_agency_id' id='og_propertyme_agency_id' type="hidden" value="<?php echo $row['propertyme_agency_id']; ?>">
                                                    </p>
                                                </div>
                                            </div>

                                            <p id="tas_bottom_txt">Please note this does not connect the <span id="tas_dynamic_name"><?php echo $row['tsa_name']; ?></span> API</p>

                                        </div>                                        

                                    </div>
                                </section>

                                <div class="text-right"><button class="btn" id="btn_update_agency_trust_account_soft">Update</button></div>

                            </div>
                        
                            <div id="fancybox_maintenance_program_sec" style="display:none;">
                                <section class="card card-blue-fill">
                                    <header class="card-header">Maintenance Program</header>
                                    <div class="card-block">

                                        <div class="form-group row">
                                            <label class="col-sm-4 form-control-label">Maintenance Provider</label>
                                            <div class="col-sm-8">
                                                <select name="maintenance" class="maintenance mm_prog form-control" id="maintenance">
                                                    <option value="">None</option>
                                                    <?php foreach($m_array as $m){ ?>
                                                    <option value='<?php echo $m['maintenance_id']; ?>' <?php echo ($m['maintenance_id']==$sel_m['maintenance_id'])?'selected="selected"':''; ?>><?php echo $m['name']; ?></option>
                                                    <?php } ?>
                                                </select>
                                                <input type="hidden" id="og_maintenance" name="og_maintenance" value="<?php echo $sel_m['maintenance_id']; ?>">
                                            </div>
                                        </div>
                                                        
                                        <div class="maintenance_prog_row" style="display:<?php echo ( $sel_mp_num_rows > 0 )?'block':'none'; ?>">

                                            <div class="form-group row">
                                                <label class="col-sm-4 form-control-label">Apply Surcharge to all Invoices?</label>
                                                <div class="col-sm-8">
                                                    <div class="radio">
                                                        <input type="radio" id="m_surcharge_1" class="addinput mm_prog" title="Apply Surcharge to all Invoices" value="1" name="m_surcharge" <?php echo ($sel_m['surcharge']==1)?'checked="checked"':''; ?>>
                                                        <label for="m_surcharge_1">Yes </label>
                                                        &nbsp;&nbsp;
                                                        <input type="radio" id="m_surcharge_2" class="addinput mm_prog" title="Apply Surcharge to all Invoices" value="0" name="m_surcharge" <?php echo ($sel_m['surcharge']==0)?'checked="checked"':''; ?>>
                                                        <label for="m_surcharge_2">No </label>
                                                    </div>
                                                    <input type="hidden" id="og_m_surcharge" name="og_m_surcharge" value="<?php echo $sel_m['surcharge']; ?>">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-4 form-control-label">Display Message on all Invoices?</label>
                                                <div class="col-sm-8">
                                                    <div class="radio">
                                                        <input type="radio" id="m_disp_surcharge_1" class="addinput mm_prog" title="Display Message on all Invoices" value="1" name="m_disp_surcharge" <?php echo ($sel_m['display_surcharge']==1)?'checked="checked"':''; ?>>
                                                        <label for="m_disp_surcharge_1">Yes </label>
                                                        &nbsp;&nbsp;
                                                        <input type="radio" id="m_disp_surcharge_2" class="addinput mm_prog" title="Display Message on all Invoices" value="0" name="m_disp_surcharge" <?php echo ($sel_m['display_surcharge']==0)?'checked="checked"':''; ?>>
                                                        <label for="m_disp_surcharge_2">No </label>
                                                    </div>
                                                    <input type="hidden" id="og_m_disp_surcharge" name="og_m_disp_surcharge" value="<?php echo $sel_m['display_surcharge']; ?>">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-4 form-control-label">Surcharge</label>
                                                <div class="col-sm-8">
                                                    <p class="form-control-static">
                                                        <div class="input-group">
                                                            <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                            <input type="text" class="form-control mm_prog" name="m_price" id="m_price" title="Surcharge" value="<?php echo $sel_m['price']; ?>" />
                                                            <input type="hidden" class="form-control og_m_price" name="og_m_price" id="og_m_price" value="<?php echo $sel_m['price']; ?>" />
                                                        </div>
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-4 form-control-label">Invoice Message</label>
                                                <div class="col-sm-8">
                                                    <p class="form-control-static">
                                                        <textarea  class="form-control agency_comments mm_prog" name='m_surcharge_msg' id='m_surcharge_msg'><?php echo $sel_m['surcharge_msg']; ?></textarea>
                                                        <input type="hidden" id="og_m_surcharge_msg" name="og_m_surcharge_msg" value="<?php echo $sel_m['surcharge_msg']; ?>">
                                                    </p>
                                                </div>
                                            </div>

                                            <p id="mp_bottom_txt">All invoices will divert to platform invoicing to process via <span id="mp_dynamic_name"><?php echo $sel_m['name']; ?></span></p>

                                        </div>
                                    </div>
                                </section>

                                <div class="text-right"><button class="btn" id="btn_update_agency_maintenance_prog">Update</button></div>
                            </div>

                            <div id="fancybox_from_other_company" style="display:none;">

                                <section class="card card-blue-fill">
                                    <header class="card-header">From Other Company</header>
                                    <div class="card-block">           
                                        <div class="form-group row">
                                            <label class="col-sm-4 form-control-label">Company</label>
                                            <div class="col-sm-8">
                                                <p class="form-control-static">
                                                    <select name="from_other_company" id="from_other_company" class="form-control from_other_company">
                                                        <option value="">----</option>
                                                        <?php                                                                                       
                                                        foreach( $sa_comp_sql->result() as $sa_comp_row ){ ?>
                                                            <option 
                                                                value="<?php echo $sa_comp_row->sac_id; ?>" 
                                                                <?php echo ( $sa_comp_row->sac_id == $afoc_row->sac_id )?'selected':null; ?>
                                                            >
                                                                <?php echo $sa_comp_row->company_name; ?>
                                                            </option>
                                                        <?php
                                                        }                                    
                                                        ?>							
                                                    </select>                                                    
                                                </p>
                                            </div>
                                        </div>                                     

                                    </div>
                                </section>

                                <div class="text-right">
                                    <button class="btn" id="from_other_company_btn_update">Update</button>
                                </div>

                            </div>

                    </div>
                    
                    <div class="col-md-6 columns">

                        <div class="row form-group">
                            <div class="col-md-12 columns">
                                <div class="left tt_boxes text-left">
                                    <label>Properties under Management</label>
                                    <a data-auto-focus="false" data-fancybox data-src="#fancybox_prop_uner_management" href="javascript:;"><?php echo $row['tot_properties'] ?></a>

                                    <div id="fancybox_prop_uner_management" style="display:none;">
                                        <section class="card card-blue-fill">
                                            <header class="card-header">Properties under Management</header>
                                            <div class="card-block">
                                                <div class="form-group">   
                                                    <input class="form-control" type="text" id="total_prop" class="total_prop" name="total_prop" value="<?php echo $row['tot_properties'] ?>">
                                                    <input type="hidden" id="og_total_prop" value="<?php echo $row['tot_properties'] ?>">
                                                </div>
                                                <div class="form-group text-right">
                                                    <button class="btn btn-update-total-prop" >Update</button>
                                                </div>
                                            </div>
                                        </section>
                                    </div>
                                </div>
                            </div>               
                        </div>

                        <div class="row form-group">
                            <div class="col-md-12 columns">
                                <div class="left tt_boxes text-left">
                                    <label>Active Services</label>
                                    <?php echo $getSatsToServicePropertyServices ?>
                                </div>
                            </div>               
                        </div>

                        <div class="row form-group">
                            <div class="col-md-12 columns">
                                <div class="left tt_boxes text-left">
                                    <label>Website</label>
                                    <a data-auto-focus="false" data-fancybox data-src="#fancybox_website" href="javascript:;"> <?php echo ($row['website']!="") ? $row['website'] : '<em>Add Website</em>'; ?></a>
                                </div>
                                <div id="fancybox_website" style="display:none;">
                                    <section class="card card-blue-fill">
                                        <header class="card-header">Website</header>
                                        <div class="card-block">
                                            <div class="form-group">   
                                                <input class="form-control" title="Website" name='website' id='website' type="text" value="<?php echo $row['website']; ?>">
                                                <input class="form-control" name='og_website' id='og_website' type="hidden" value="<?php echo $row['website']; ?>">
                                            </div>
                                        </div>
                                    </section>   
                                    <div class="form-group text-right">
                                        <button class="btn btn-update-website" id="btn-update-website" >Update</button>
                                    </div>  
                                </div>
                            </div>               
                        </div>

                        <div class="row form-group">
                            <div class="col-md-12 columns">
                                <div class="left tt_boxes text-left">
                                    <label>Joined SATS</label>
                                   <a data-auto-focus="false" data-fancybox data-src="#fancybox_joined_sats" href="javascript:;">   <?php echo ($this->system_model->isDateNotEmpty($row['joined_sats'])) ? $this->system_model->formatDate($row['joined_sats'],'d/m/Y') : "<em>Add Date</em>" ?></a>
                                </div>
                                <div id="fancybox_joined_sats" style="display:none;">
                                    <section class="card card-blue-fill">
                                        <header class="card-header">Joined SATS</header>
                                        <div class="card-block">
                                            <div class="form-group">   
                                                <input id="joined_sats" class="form-control flatpickr flatpickr-input" title="Joined SATS" type="text" name="joined_sats" value="<?php echo ( $row['joined_sats']!='' )?date('d/m/Y',strtotime($row['joined_sats'])):''; ?>" />
                                                <input type="hidden" id="og_joined_sats" name="og_joined_sats" value="<?php echo $this->system_model->formatDate($row['joined_sats'],'d/m/Y') ?>">
                                            </div>
                                        </div>
                                    </section>    
                                        <div class="form-group text-right">
                                            <button class="btn btn-update-joined-sats" id="btn-update-joined-sats" >Update</button>
                                        </div>
                                </div>
                            </div>               
                        </div>

                    </div>

                </div>
                
                   

            </div>
        </div>

        <div class="row">
            <div class="col-md-6 columns">
                    <div class="row">
                        <div class="col-md-12 text-left">
                            <section class="card card-blue-fill">
                                <header class="card-header">Contact Details</header>
                                <div class="card-block">

                                        <div class="row th_div form-group">

                                            <div class="col-md-3 columns"><strong>Contact Type</strong></div>
                                            <div class="col-md-3 columns"><strong><span class="font-icon font-icon-user"></span></strong></div>
                                            <div class="col-md-3 columns"><strong><span class="fa fa-phone"></span></strong></div>
                                            <div class="col-md-3 columns"><strong><span class="fa fa-envelope"></span></strong></div>

                                        </div>

                                        <div class="row form-group">

                                            <div class="col-md-3 columns">
                                                <a data-auto-focus="false" data-fancybox data-src="#fancybox_agency_landline" href="javascript:;">Office</a>
                                            </div>
                                            <div class="col-md-3 columns"></div>
                                            <div class="col-md-3 columns"><?php echo $row['phone']; ?></div>
                                            <div class="col-md-3 columns"></div>

                                        </div>

                                        <div class="row form-group">

                                            <div class="col-md-3 columns">
                                                <a data-auto-focus="false" data-fancybox data-src="#fancybox_agency_contact_sec" href="javascript:;">Agency</a>
                                            </div>
                                            <div class="col-md-3 columns"><?php echo $row['contact_first_name']." ".$row['contact_last_name']; ?></div>
                                            <div class="col-md-3 columns"><?php echo $row['contact_phone']; ?></div>
                                            <div class="col-md-3 columns"><span><?php echo $row['contact_email']; ?></span></div>

                                        </div>

                                        <div class="row form-group">

                                            <div class="col-md-3 columns">
                                                <a data-auto-focus="false" data-fancybox data-src="#fancybox_agency_accounts_contact_sec" href="javascript:;">Accounts</a>
                                            </div>
                                            <div class="col-md-3 columns"><?php echo $row['accounts_name']; ?></div>
                                            <div class="col-md-3 columns"><?php echo $row['accounts_phone']; ?></div>
                                            <div class="col-md-3 columns"></div>

                                        </div>

                                        <div class="row form-group">

                                            <div class="col-md-3 columns">
                                                <a data-auto-focus="false" data-fancybox data-src="#fancybox_agency_tenant_details_sec" href="javascript:;">Tenant Details</a>
                                            </div>
                                            <div class="col-md-3 columns"><?php echo $row['tenant_details_contact_name']; ?></div>
                                            <div class="col-md-3 columns"><?php echo $row['tenant_details_contact_phone']; ?></div>
                                            <div class="col-md-3 columns"></div>

                                        </div>
                                        
                                </div>
                                
                            </section>

                            <!-- CONTACT DETAILS fancyboxes -->

                            <div id="fancybox_agency_landline" style="display:none;">
                                <section class="card card-blue-fill">
                                    <header class="card-header">Office Phone</header>
                                    <div class="card-block">
                                        <div class="form-group">
                                            <label class="form-label">Landline</label>
                                            <input class="form-control" title="Landline" name='phone' id='phone' type="text" value="<?php echo $row['phone']; ?>">
                                            <input class="form-control" name='og_phone' id='og_phone' type="hidden" value="<?php echo $row['phone']; ?>">
                                        </div>  
                                    </div>  
                                </section>  
                                <div class="text-right"><button class="btn" id="btn_update_agency_landline">Update</button></div>
                            </div>

                            <div id="fancybox_agency_contact_sec" style="display:none;">
                                <section class="card card-blue-fill">
                                    <header class="card-header">Agency Contact</header>
                                    <div class="card-block">
                                        <div class="row form-group">
                                            <div class="col-md-3 columns">
                                                <label class="form-label">First Name</label>
                                                <input class="form-control" title="Agency Contact First Name" id="ac_fname" name="ac_fname" type="text" value="<?php echo $row['contact_first_name']; ?>">
                                                <input class="form-control" First Name" id="og_ac_fname" name="og_ac_fname" type="hidden" value="<?php echo $row['contact_first_name']; ?>">
                                            </div>
                                            <div class="col-md-3 columns">
                                                <label class="form-label">Last Name</label>
                                                <input class="form-control" title="Agency Contact Last Name" id="ac_lname" name="ac_lname" type="text" value="<?php echo $row['contact_last_name']; ?>">
                                                <input class="form-control" id="og_ac_lname" name="og_ac_lname" type="hidden" value="<?php echo $row['contact_last_name']; ?>">
                                            </div>
                                            <div class="col-md-3 columns">
                                                <label class="form-label">Phone</label>
                                                <input class="form-control" title="Agency Contact Phone" id="ac_phone" name="ac_phone"  type="text" value="<?php echo $row['contact_phone']; ?>">
                                                <input class="form-control" title="Agency Contact Phone" id="og_ac_phone" name="og_ac_phone"  type="hidden" value="<?php echo $row['contact_phone']; ?>">
                                            </div>
                                            <div class="col-md-3 columns">
                                                <label class="form-label">Email</label>
                                                <input class="form-control" title="Agency Contact Email" id="ac_email" name="ac_email"  type="text" value="<?php echo $row['contact_email']; ?>">
                                                <input class="form-control" title="Agency Contact Email" id="og_ac_email" name="og_ac_email"  type="hidden" value="<?php echo $row['contact_email']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </section>
                                <div class="text-right"><button class="btn" id="btn_update_agency_contact">Update</button></div>
                            </div>

                            <div id="fancybox_agency_accounts_contact_sec" style="display:none;">
                                <section class="card card-blue-fill">
                                    <header class="card-header">Accounts Contact</header>
                                    <div class="card-block">
                                    <div class="row form-group">
                                        <div class="col-md-6 columns">
                                            <label class="form-label">Name</label>
                                            <input class="form-control" title="Accounts Contact Name" id="acc_name" name="acc_name" type="text" value="<?php echo $row['accounts_name']; ?>">
                                            <input class="form-control" title="Accounts Contact Name" id="og_acc_name" name="og_acc_name" type="hidden" value="<?php echo $row['accounts_name']; ?>">
                                        </div>
                                        <div class="col-md-6 columns">
                                            <label class="form-label">Phone</label>
                                            <input class="form-control" title="Accounts Contact Phone" id="acc_phone" name="acc_phone" type="text" value="<?php echo $row['accounts_phone']; ?>">
                                            <input class="form-control" title="Accounts Contact Phone" id="og_acc_phone" name="og_acc_phone" type="hidden" value="<?php echo $row['accounts_phone']; ?>">
                                        </div>
                                        </div>
                                    </div>
                                </section>  
                                <div class="text-right"><button class="btn" id="btn_update_agency_account_contact">Update</button></div>
                            </div>

                            <div id="fancybox_agency_tenant_details_sec" style="display:none;">
                                <section class="card card-blue-fill">
                                    <header class="card-header">Tenant Details Contact</header>
                                    <div class="card-block">
                                        <div class="row form-group">
                                            <div class="col-md-6 columns">
                                                <label class="form-label">Name</label>
                                                <input class="form-control" title="Tenant Details Contact Name" id="tdc_name" name="tdc_name" type="text" value="<?php echo $row['tenant_details_contact_name']; ?>">
                                                <input class="form-control" title="Tenant Details Contact Name" id="og_tdc_name" name="og_tdc_name" type="hidden" value="<?php echo $row['tenant_details_contact_name']; ?>">
                                            </div>
                                            <div class="col-md-6 columns">
                                                <label class="form-label">Phone</label>
                                                <input class="form-control" title="Tenant Details Contact Phone" id="tdc_phone"  name="tdc_phone" type="text" value="<?php echo $row['tenant_details_contact_phone']; ?>">
                                                <input class="form-control" title="Tenant Details Contact Phone" id="og_tdc_phone"  name="og_tdc_phone" type="hidden" value="<?php echo $row['tenant_details_contact_phone']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </section>
                                <div class="text-right"><button class="btn" id="btn_update_agency_tenant">Update</button></div>
                            </div>

                            <!-- CONTACT DETAILS fancyboxes end -->

                        </div>
                    </div>
            </div>
            <div class="col-md-6 columns">

                <section class="card card-blue-fill">
                    <header class="card-header">Agency Notes</header>
                    <div class="card-block">

                        <div class="form-group tt_boxes">
                            <label class="form-control-label">Agency Specific Notes</label>
                            <?php if($row['agency_specific_notes']==""){
                              ?>
                              <a data-auto-focus="false" data-fancybox data-src="#fancybox_agency_notes" href="javascript:;"><i>Add notes here</i></a>
                              <?php
                            }else{
                            ?>
                            <a data-auto-focus="false" data-fancybox data-src="#fancybox_agency_notes" href="javascript:;">  <?php echo $row['agency_specific_notes']; ?></a>
                            <?php
                            } ?>
                            
                        </div>
                        <div class="form-group tt_boxes">
                            <label class="form-control-label">Agency Comments</label>
                            <?php if($row['comment']==""){
                            ?>
                                <a data-auto-focus="false" data-fancybox data-src="#fancybox_agency_notes" href="javascript:;"><i>Add agency comments here</i></a>
                            <?php
                            }else{
                                ?>
                            <a data-auto-focus="false" data-fancybox data-src="#fancybox_agency_notes" href="javascript:;">  <?php echo $row['comment']; ?></a>
                            <?php
                            } ?>
                            
                        </div>

                    </div>
                </section>

                <div id="fancybox_agency_notes" style="display:none">
                    <section class="card card-blue-fill">
                        <header class="card-header">Agency Notes</header>
                        <div class="card-block">
                            <div class="form-group">
                                <label class="form-label">Agency Specific Notes</label>
                                <textarea class="form-control" title="Agency Specific Notes" name='agency_specific_notes' id='agency_specific_notes' type="text"><?php echo $row['agency_specific_notes']; ?></textarea>
                                    <input class="form-control" name='og_agency_specific_notes' id='og_agency_specific_notes' type="hidden" value="<?php echo $row['agency_specific_notes']; ?>">
                            </div>   
                            <div class="form-group">
                                <label class="form-label">Agency Comments</label>
                                <textarea class="form-control addtextarea formtextarea agency_comments" title="Agency Comments" name='comment' id='comment'><?php echo $row['comment']; ?></textarea>
                                <input class="form-control" name='og_comment' id='og_comment' type="hidden" value="<?php echo $row['comment']; ?>">
                            </div>
                        </div>
                    </section>
                    <div class="text-right"><button class="btn" id="btn_update_agency_notes">Update</button>  </div> 
                </div>

            </div>          
        </div>

        <div class="row vad_cta_box">
            <div class="col-md-12 columns">
                <div class="text-left">
                    <?php if($row['status']!='active'){ ?>
                        <button id="btn-activate" class="btn btn-success btn-activate" type="button">Change Agency to Active</button>
                    <?php } ?>
                </div>
            </div>
        </div>


    </div>

<style>
#tas_bottom_txt,
#mp_bottom_txt{
    font-style: italic;
    font-size: 13px;
    color: red;
}    
#other_reason_div{
    display: none;
}
.hide{
    display: none;
}

.show {
    display: block;
}

.error-text {
    border : 1px solid red;
}

.error_panel span {
    font-size: 14px;
}
</style>
<script>

    jQuery(document).ready(function(){

       	// TAS script
        jQuery("#trust_acc_soft").change(function(){
            
            var dom = jQuery(this);
            var option_val = dom.val();
            var opt_sel = dom.find("option:selected");
            var opt_sel_txt = opt_sel.text();
            
            console.log("tas: "+option_val);
            console.log("opt_sel_txt: "+opt_sel_txt);
            
            if( option_val > 0 ){
                jQuery("#tas_connected_div").show();
                jQuery("#tas_bottom_txt").show();
                jQuery("#tas_dynamic_name").text(opt_sel_txt);
            }else{
                jQuery("#tas_connected_div").hide();
                jQuery("#tas_bottom_txt").hide();
                jQuery("#tas_dynamic_name").text('');
            }
            
        });

        // maintenance hide/show toggle
        jQuery("#maintenance").change(function(){

            var dom = jQuery(this);
            var option_val = dom.val();
            var opt_sel = dom.find("option:selected");
            var opt_sel_txt = opt_sel.text();
            
            if( option_val > 0 ){
                jQuery(".maintenance_prog_row").show();
                jQuery("#mp_dynamic_name").text(opt_sel_txt);
            }else{
                jQuery(".maintenance_prog_row").hide();
                jQuery("#mp_dynamic_name").text('');
            }
            
        });

        // Maintenance Program flag
        jQuery(".mm_prog").change(function(){
            jQuery("#mm_program_edited").val(1);
        });

        // change status
        jQuery("#statuss").change(function(){
            
            var prompt = '';
            
            if(jQuery(this).val()=="target"){
                swal({
                    title: "Warning!",
                    text: "If you change status to Target, all Jobs will be cancelled and all properties will be deactivated. Are you sure you want to continue?",
                    type: "warning",
                    showCancelButton: true,
                    cancelButtonText: "Cancel!",
                    cancelButtonClass: "btn-danger",
                    confirmButtonClass: "btn-success",
                    confirmButtonText: "Yes",
                    closeOnConfirm: true,
                },
                function(isConfirm) {
                    if (isConfirm) { // yes
                        jQuery("#deactivate_box_header").text("Target");
                        $('.deactivate_agency_reason_div').show();
                    }else{
                        $('#statuss option').eq(0).prop('selected',true);
                    }
                });
                
            }else if(jQuery(this).val()=="deactivated"){
                swal({
                    title: "Warning!",
                    text: "If you change status to Deactivated, all Jobs will be cancelled and all properties will be deactivated. Are you sure you want to continue?",
                    type: "warning",
                    showCancelButton: true,
                    cancelButtonText: "Cancel!",
                    cancelButtonClass: "btn-danger",
                    confirmButtonClass: "btn-success",
                    confirmButtonText: "Yes",
                    closeOnConfirm: true,
                },
                function(isConfirm) {
                    if (isConfirm) { // yes
                        jQuery("#deactivate_box_header").text("Deactivated");
                        $('.deactivate_agency_reason_div').show();
                    }else{
                        $('#statuss option').eq(0).prop('selected',true);
                    }
                });
                
            }else{
                $('.deactivate_agency_reason_div').hide();
            }
            
        });

        /** Delete Property */
        jQuery("#btn_hard_delete").click(function(){

             swal({
                title: "Warning!",
                text: "You are about to delete this agency! once deleted it cannot be undone. Are you sure you want to continue?",
                type: "warning",
                showCancelButton: true,
                cancelButtonText: "Cancel!",
                cancelButtonClass: "btn-danger",
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes",                       
                closeOnConfirm: true,
            },
            function(isConfirm) {
                if (isConfirm) {
                    $('#load-screen').show();
                    jQuery.ajax({
                        type: "POST",
                        url: "/agency/ajax_hard_delete_agency",
                        dataType: 'json',
                        data: {
                            agency_id: <?php echo $agency_id; ?>
                        }
                    }).done(function( ret ) {	
                        $('#load-screen').hide();

                        var num_prop = parseInt(ret.prop_count);

                        if(num_prop>0){
                            swal('','ABORTED!!! cannot delete this agency. properties under this agency still exist','error');
                        }else{

                            swal({
                                title:"Success!",
                                text: "Delete Successful",
                                type: "success",
                                showCancelButton: false,
                                confirmButtonText: "OK",
                                closeOnConfirm: false,  
                                showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                                timer: <?php echo $this->config->item('timer') ?>
                            });

                            <?php
                            if( $row['status'] == 'target' ){ ?>
                                var full_url = "/agency/view_target_agencies";
                                setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
                            <?php
                            }else if( $row['status'] == 'deactivated' ){ ?>
                                var full_url = "/agency/view_deactivated_agencies";
                                setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
                            <?php	
                            }else{ ?>
                                var full_url = "/agency/view_agencies";
                                setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
                            <?php
                            }
                            ?>	

                        }
                    });	
                }
            });	
            
        });


        //Update Agency 
        $('.btn_update').on('click',function(e){

            e.preventDefault();

            var address = $('#fullAdd').val();
            var franchise_group = $('#franchise_group').val();
            var salesrep = $('#salesrep').val();
            var agency_name = $('#agency_name').val();
            var active_prop_with_sats = jQuery("#active_prop_with_sats").val();
            var agency_status = jQuery("#status").val();

            var error = "";
            var submitcount = 0;

            if(agency_name==""){
                error += "Agency Name is required\n";
            }

            if(address==""){
                error += "Address is required\n";
            }

            if(franchise_group==""){
                error += "Franchise Group is required\n";
            }

            if(salesrep==""){
                error += "Sales rep is required\n";
            }

            if(agency_status == "deactivated"){
                if(active_prop_with_sats==""){
                    error += "Active Properties with SATS is required\n";
                }
            }

            if(error!=""){

                swal({
                    title: "",                    
                    text: error,
                    type: "error",		
                    customClass: 'update_agency_validation_swal'
                });

                return false;

            }else{

                swal({
                    title: "Warning!",
                    text: "Update Details?",
                    type: "warning",
                    showCancelButton: true,
                    cancelButtonText: "Cancel!",
                    cancelButtonClass: "btn-danger",
                    confirmButtonClass: "btn-success",
                    confirmButtonText: "Yes",
                    closeOnConfirm: true,
                },
                function(isConfirm) {
                    if (isConfirm) { // yes

                        if(submitcount==0){
                            submitcount++;
                            jQuery("#vad_form").submit();
                            return false;
                        }else{
                            swal('','Form submission is in progress','error');
                            return false;
                        }

                    }
                });

            }

        })

        //re activate
        $('.btn-activate').on('click',function(){
            swal({
                title: "Warning!",
                text: "Change Agency to Active?",
                type: "warning",
                showCancelButton: true,
                cancelButtonText: "Cancel!",
                cancelButtonClass: "btn-danger",
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes",
                closeOnConfirm: true,
            },
            function(isConfirm) {
                if (isConfirm) { // yes
                    $('#load-screen').show();
                    jQuery.ajax({
                        type: "POST",
                        url: "/agency/ajax_re_activate",
                        dataType: 'json',
                        data: {
                            agency_id: <?php echo $agency_id; ?>
                        }
                    }).done(function( ret ) {	
                        if(ret.status){
                            $('#load-screen').hide();
                            swal({
                                title:"Success!",
                                text: "Re-activate Successful",
                                type: "success",
                                showCancelButton: false,
                                confirmButtonText: "OK",
                                closeOnConfirm: false,  
                                showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                                timer: <?php echo $this->config->item('timer') ?>
                            });
                            var full_url = "/agency/view_agency_details/<?php echo $agency_id; ?>";
                            setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
                        }
                        
                    });	
                }
            });
        })

        /** NEW CODES > AFTER DANS VAD VISUAL CHANGES */

        $('#btn_update_agency_name').on('click',function(){

            var agency_name = $('#agency_name').val();
            var og_agency_name = $('#og_agency_name').val();
            var err = "";

            if(agency_name==""){
                err+="Agency is required";
            }

            if(err!=""){
                swal('',err,'error');
                return false;
            }

            $('#load-screen').show();
            jQuery.ajax({
                        type: "POST",
                        url: "/agency/ajax_update_agency",
                        dataType: 'json',
                        data: {
                            type: $(this).attr('id'),
                            agency_name: agency_name,
                            og_agency_name: og_agency_name,
                            agency_id: <?php echo $agency_id; ?>
                        }
                    }).done(function( ret ) {	
                        $('#load-screen').hide();
                        if(ret.status){
                            $('#load-screen').hide();
                            swal({
                                title:"Success!",
                                text: "Update Successful",
                                type: "success",
                                showCancelButton: false,
                                confirmButtonText: "OK",
                                closeOnConfirm: false,  
                                showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                                timer: <?php echo $this->config->item('timer') ?>
                            });
                            var full_url = "/agency/view_agency_details/<?php echo $agency_id; ?>";
                            setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
                        }
                    });	


        })

        //update agency address
        $('#btn_update_agency_address').on('click',function(){
            var address_1 = $('#address_1').val();
            var address_2 = $('#address_2').val();
            var address_3 = $('#address_3').val();
            var state = $('#state').val();
            var og_state = $('#og_state').val();
            var postcode = $('#postcode').val();
            var postcode_region_name = $('#postcode_region_name').val();
            var og_postcode_region_name = $('#og_postcode_region_name').val();
            var fullAdd = $('#fullAdd').val();
            var og_fullAdd = $('#og_fullAdd').val();
            var address_type = $('#agency_addresses').val();
            var err = "";

            /*
            if(fullAdd==""){
                err+="Address is required";
            }
            */

            if(fullAdd == "" || address_1 == "" || address_2 == "" || address_3 == "" || state == "" || postcode == ""){
                err+="Complete address is required";
            }

            if(err!=""){
                swal('',err,'error');
                return false;
            }

            $('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/agency/ajax_update_agency",
                dataType: 'json',
                data: {
                    type: $(this).attr('id'),
                    agency_id: <?php echo $agency_id; ?>,
                    address_1: address_1,
                    address_2: address_2,
                    address_3: address_3,
                    state: state,
                    og_state: og_state,
                    postcode: postcode,
                    fullAdd: fullAdd,
                    og_fullAdd: og_fullAdd,
                    postcode_region_name: postcode_region_name,
                    og_postcode_region_name: og_postcode_region_name,
                    address_type: address_type
                    
                }
            }).done(function( ret ) {	
                $('#load-screen').hide();
                if(ret.status){
                    $('#load-screen').hide();
                    swal({
                        title:"Success!",
                        text: "Update Successful",
                        type: "success",
                        showCancelButton: false,
                        confirmButtonText: "OK",
                        closeOnConfirm: false,  
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });
                    var full_url = "/agency/view_agency_details/<?php echo $agency_id; ?>";
                    setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
                }
            }); 

        })


        //Update fronchise group
        $('#btn_update_agency_franchice').on('click',function(){
            var franchise_group = $("#franchise_group").val();
            var og_franchise_group = $("#og_franchise_group").val();
            var err = "";

            if(franchise_group==""){
                err+="Franchise is required";
            }

            if(err!=""){
                swal('',err,'error');
                return false;
            }

            $('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/agency/ajax_update_agency",
                dataType: 'json',
                data: {
                    type: $(this).attr('id'),
                    agency_id: <?php echo $agency_id; ?>,
                    franchise_group: franchise_group,
                    og_franchise_group: og_franchise_group
                }
            }).done(function( ret ) {	
                $('#load-screen').hide();
                if(ret.status){
                    $('#load-screen').hide();
                    swal({
                        title:"Success!",
                        text: "Update Successful",
                        type: "success",
                        showCancelButton: false,
                        confirmButtonText: "OK",
                        closeOnConfirm: false,  
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });
                    var full_url = "/agency/view_agency_details/<?php echo $agency_id; ?>";
                    setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
                }
            });	

        });

        //Update Office Hours
        $('#btn_update_agency_office_hour').on('click',function(){

            var agency_hours = $('#agency_hours').val();
            var og_agency_hours = $('#og_agency_hours').val();

            $('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/agency/ajax_update_agency",
                dataType: 'json',
                data: {
                    type: $(this).attr('id'),
                    agency_id: <?php echo $agency_id; ?>,
                    agency_hours: agency_hours,
                    og_agency_hours: og_agency_hours
                }
            }).done(function( ret ) {	
                $('#load-screen').hide();
                if(ret.status){
                    $('#load-screen').hide();
                    swal({
                        title:"Success!",
                        text: "Update Successful",
                        type: "success",
                        showCancelButton: false,
                        confirmButtonText: "OK",
                        closeOnConfirm: false,  
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });
                    var full_url = "/agency/view_agency_details/<?php echo $agency_id; ?>";
                    setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
                }
            });	

        })

        $(".agency-priority").on('click', function(elem) {
            $this = $(this);
            //console.log('==== this: ', $this)

            if ($this.text() === 'Unmark') {
                var priority = 0;
                var unmark = 1;
            } else {
                var priority = $("#priority-selected").val();
                var unmark = 0;
            }
            
            var priority_reason = $("#priority_reason").val();
            var salesrep = $('#salesrep').val();
            var og_salesrep = $('#og_salesrep').val();

            // $('#load-screen').show();

            jQuery.ajax({
                type: "POST",
                url: "",
                url: "<?php echo site_url(); ?>ajax/agency_ajax/ajax_high_touch_update_v2",
                dataType: 'json',
                data: {
                    agency_id: <?php echo $agency_id; ?>,
                    salesrep: salesrep,
                    og_salesrep: og_salesrep,
                    priority_reason: priority_reason,
                    priority: priority,
                    unmark: unmark,
                    // ht_date_added: ht_date_added
                }
            }).done(function(response) {
                var data = JSON.parse(JSON.stringify(response));
                console.log(data.error);
                if (data.error == 0) {
                    console.log("0");
                    $("#priority_reason").addClass("error-text");
                    $(".error_panel span").addClass('show');
                    $(".error_panel span").removeClass('hide');
                } else {
                    $('#load-screen').hide();
                    location.reload();
                    console.log("1");
                }
                
            });
        });

        // $("#btn_update_agency_high_touch").on('click', function() {
        //     return false;

        //     var priority_reason = $("#priority_reason").val();
        //     var priority = $("#priority-selected").val();

        //     // var ht_date_added = $("#high_touch_date_added").val();

        //     var salesrep = $('#salesrep').val();
        //     var og_salesrep = $('#og_salesrep').val();

        //     // $('#load-screen').show();

        //     jQuery.ajax({
        //         type: "POST",
        //         url: "",
        //         url: "<?php //echo site_url(); ?>ajax/agency_ajax/ajax_high_touch_update_v2",
        //         dataType: 'json',
        //         data: {
        //             agency_id: <?php //echo $agency_id; ?>,
        //             salesrep: salesrep,
        //             og_salesrep: og_salesrep,
        //             priority_reason: priority_reason,
        //             priority: priority,
        //             // ht_date_added: ht_date_added
        //         }
        //     }).done(function(response) {
        //         var data = JSON.parse(JSON.stringify(response));
        //         console.log(data.error);
        //         if (data.error == 0) {
        //             console.log("0");
        //             $("#priority_reason").addClass("error-text");
        //             $(".error_panel span").addClass('show');
        //             $(".error_panel span").removeClass('hide');
        //         } else {
        //             $('#load-screen').hide();
        //             location.reload();
        //             console.log("1");
        //         }
                
        //     });

        // });

        //Update Salesrep
        $('#btn_update_agency_salesrep').on('click',function(){
            var salesrep = $('#salesrep').val();
            var og_salesrep = $('#og_salesrep').val();

            var err = "";

            if(salesrep==""){
                err+="Salesrep is required";
            }

            if(err!=""){
                swal('',err,'error');
                return false;
            }

            $('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/agency/ajax_update_agency",
                dataType: 'json',
                data: {
                    type: $(this).attr('id'),
                    agency_id: <?php echo $agency_id; ?>,
                    salesrep: salesrep,
                    og_salesrep: og_salesrep
                }
            }).done(function( ret ) {	
                $('#load-screen').hide();
                if(ret.status){
                    $('#load-screen').hide();
                    swal({
                        title:"Success!",
                        text: "Update Successful",
                        type: "success",
                        showCancelButton: false,
                        confirmButtonText: "OK",
                        closeOnConfirm: false,  
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });
                    var full_url = "/agency/view_agency_details/<?php echo $agency_id; ?>";
                    setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
                }
            });	

        })

        jQuery("#reason_they_left").change(function(){

            var reason_they_left_dom = jQuery(this);
            var reason_they_left =  reason_they_left_dom.find("option:checked").val();

            if( reason_they_left == -1 ){
                jQuery("#other_reason_div").show();
            }else{
                jQuery("#other_reason_div").hide();
            }            

        });

        //Update Status
        $('#btn_update_agency_status').on('click',function(){
            var status = $('#statuss').val();
            var og_status = $('#og_status').val();
            var active_prop_with_sats = $('#active_prop_with_sats').val();
            var og_active_prop_with_sats = $('#og_active_prop_with_sats').val();
            var agency_using = $('#agency_using').val();
            var og_agency_using = $('#og_agency_using').val();
            var deactivate_reason = $('#deactivate_reason').val();
            var og_deactivate_reason = $('#og_deactivate_reason').val();
            var reason_they_left = jQuery("#reason_they_left").val();
            var other_reason = jQuery("#other_reason").val();
            var deactivate_reason_str = '';  
            var error = '';   

            if( status == 'target' || status == 'deactivated' ){

                // validation
                if( reason_they_left == '' ){
                    error += "'Reason They Left' is required\n";
                }else{
                    if( reason_they_left == -1 && other_reason == '' ){
                        error += "'Other Reason' is required\n";
                    }
                } 

            }                       

            if( error != "" ){ // error

                swal('', error, 'error'); 

            }else{

                // ajax update
                $('#load-screen').show();
                jQuery.ajax({
                    type: "POST",
                    url: "/agency/ajax_update_agency",
                    dataType: 'json',
                    data: {
                        type: $(this).attr('id'),
                        agency_id: <?php echo $agency_id; ?>,
                        status: status,
                        og_status: og_status,
                        active_prop_with_sats: active_prop_with_sats,
                        og_active_prop_with_sats: og_active_prop_with_sats,
                        agency_using: agency_using,
                        og_agency_using: og_agency_using,
                        deactivate_reason: deactivate_reason_str,
                        og_deactivate_reason: og_deactivate_reason,
                        reason_they_left: reason_they_left,
                        other_reason: other_reason
                        
                    }
                }).done(function( ret ) {	
                    $('#load-screen').hide();
                    if(ret.status){
                        $('#load-screen').hide();
                        $.fancybox.close();
                        
                        if(ret.status_text!=""){
                            var success_text = ret.status_text;
                        }else{
                            var success_text = "Update Successful";
                        }

                        swal({
                            title:"Success!",
                            html: true,
                            text: success_text,
                            type: "success",
                            showCancelButton: false,
                            confirmButtonText: "OK",
                            closeOnConfirm: false,
                            showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                            timer: <?php echo $this->config->item('timer') ?>
                        });

                        var full_url = "/agency/view_agency_details/<?php echo $agency_id; ?>";
                        setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
                        
                        $('.agency_status_text').text(status);
                    
                    }
                });

            }                    	            

        })

        //Update Trust Account Software
        $('#btn_update_agency_trust_account_soft').on('click',function(){

            var trust_acc_soft = $('#trust_acc_soft').val();
            var og_trust_acc_soft = $('#og_trust_acc_soft').val();
            var propertyme_agency_id = $('#propertyme_agency_id').val();
            var og_propertyme_agency_id = $('#og_propertyme_agency_id').val();
            var err = "";

            if(trust_acc_soft==""){
                err+="Trust Account Software is required";
            }

            if(err!=""){
                swal('',err,'error');
                return false;
            }

            $('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/agency/ajax_update_agency",
                dataType: 'json',
                data: {
                    type: $(this).attr('id'),
                    agency_id: <?php echo $agency_id; ?>,
                    trust_acc_soft: trust_acc_soft,
                    og_trust_acc_soft: og_trust_acc_soft,
                    propertyme_agency_id: propertyme_agency_id,
                    og_propertyme_agency_id: og_propertyme_agency_id

                }
            }).done(function( ret ) {	
                $('#load-screen').hide();
                if(ret.status){
                    $('#load-screen').hide();
                    swal({
                        title:"Success!",
                        text: "Update Successful",
                        type: "success",
                        showCancelButton: false,
                        confirmButtonText: "OK",
                        closeOnConfirm: false,  
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });
                    var full_url = "/agency/view_agency_details/<?php echo $agency_id; ?>";
                    setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
                }
            });

        })

        //Update Maintenance Program
        $('#btn_update_agency_maintenance_prog').on('click',function(){

            var maintenance = $('#maintenance').val();
            var og_maintenance = $('#og_maintenance').val();
            var m_surcharge = $('input[name="m_surcharge"]:checked').val();
            var og_m_surcharge = $('#og_m_surcharge').val();
            var m_disp_surcharge = $('input[name="m_disp_surcharge"]:checked').val();
            var og_m_disp_surcharge = $('#og_m_disp_surcharge').val();
            var m_price = $('#m_price').val();
            var og_m_price = $('#og_m_price').val();
            var m_surcharge_msg = $('#m_surcharge_msg').val();
            var og_m_surcharge_msg = $('#og_m_surcharge_msg').val();

            $('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/agency/ajax_update_agency",
                dataType: 'json',
                data: {
                    type: $(this).attr('id'),
                    agency_id: <?php echo $agency_id; ?>,
                    maintenance: maintenance,
                    og_maintenance: og_maintenance,
                    m_surcharge: m_surcharge,
                    og_m_surcharge: og_m_surcharge,
                    m_disp_surcharge: m_disp_surcharge,
                    og_m_disp_surcharge: og_m_disp_surcharge,
                    m_price:m_price,
                    og_m_price: og_m_price,
                    m_surcharge_msg: m_surcharge_msg,
                    og_m_surcharge_msg: og_m_surcharge_msg
                }
            }).done(function( ret ) {	
                $('#load-screen').hide();
                if(ret.status){
                    $('#load-screen').hide();
                    swal({
                        title:"Success!",
                        text: "Update Successful",
                        type: "success",
                        showCancelButton: false,
                        confirmButtonText: "OK",
                        closeOnConfirm: false,  
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });
                    var full_url = "/agency/view_agency_details/<?php echo $agency_id; ?>";
                    setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
                }
            });

        })

        //Update agency additional details
        $('#btn_update_additional_details').on('click',function(){

            var agency_emails = $('#agency_emails').val();
            var account_emails = $('#account_emails').val();
            
            var error = "";
            var submitcount = 0;
            
            <?php
            // required only on active
            if( $row['status']=='active' ){ ?>
            
                if(agency_emails==""){
                    error += "Agency emails are required\n";
                }
                if(account_emails==""){
                    error += "Account emails is required\n";
                }
               
                //agency email validate white space line
                var agency_email_str_arr = jQuery('#agency_emails').val().split('\n');
                if( jQuery.inArray("", agency_email_str_arr) !== -1 ){
                    error += "Agency emails invalid input\n";
                }

                //agency accounts validaate white space per line
                var account_emails_str_arr = jQuery('#account_emails').val().split('\n');
                if( jQuery.inArray("", account_emails_str_arr) !== -1 ){
                    error += "Account emails invalid input\n";
                }
            
            <?php	
            }
            ?>

            if(error!=""){
                swal('', error, 'error');
                return false;
            }

            if(submitcount==0){
                submitcount++;
                jQuery("#vad_form").submit();
                return false;
            }else{
                swal('','Form submission is in progress','error');
                return false;
            }

        })

        //API Update Integration
        jQuery(".btn_update_api_integ").click(function(){
            
            var obj = $(this);
            var api_integration_id = obj.parents('.api_fancybox').find('.api_integration_id').val();
            var agency_api_token_id = obj.parents('.api_fancybox').find('.agency_api_token_id').val();
            var connected_service = obj.parents('.api_fancybox').find('.edit_api_connected_service').val();
            var og_edit_api_connected_service = obj.parents('.api_fancybox').find('.og_edit_api_connected_service').val();
            var status = obj.parents('.api_fancybox').find('.edit_api_status').val();
            var og_edit_api_status = obj.parents('.api_fancybox').find('.og_edit_api_status').val();
            var error = "";
            
            if( connected_service == "" ){
                error += "Connected Service is required\n";
            }

            if(  status == 0 && agency_api_token_id != '' ){
                error += "Cannot Update Available to Connect to NO if agency access token exist. remove it first";
            }
            
            if(error != ""){
                swal('',error,'error');
                return false;
            }

            $('#load-screen').show();
            jQuery.ajax({
                    type: "POST",
                    url: "/agency/ajax_update_agency_api_integration",
                    dataType: 'json',
                    data: { 
                        api_integration_id: api_integration_id,
                        connected_service: connected_service,
                        og_edit_api_connected_service: og_edit_api_connected_service,
                        status: status,
                        og_edit_api_status: og_edit_api_status,
                        agency_id: <?php echo $agency_id; ?>

                    }
                }).done(function( ret ) {
                    if(ret.status){
                        $.fancybox.close();
                        $('#load-screen').hide(); //hide loader
                        swal({
                            title:"Success!",
                            text: " API Integration Successfully Updated",
                            type: "success",
                            showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                            timer: <?php echo $this->config->item('timer') ?>
                        });
                        setTimeout(function(){ location.reload(); }, <?php echo $this->config->item('timer') ?>);	
                    }
                });	

        });

        // delete api integration
        jQuery(".btn_delete").click(function(){
            
            var api_integration_id = $(this).attr('data-api_integration_id');
            var api_id = $(this).attr('data-api_id');
            var agency_id = <?php echo $agency_id; ?>;

            swal(
				{
					title: "",
					text: "This will delete this API integration. Proceed?",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-success",
					confirmButtonText: "Yes",
					cancelButtonText: "No, Cancel!",
					closeOnConfirm: false,
					closeOnCancel: true,
				},
				function(isConfirm){
					if(isConfirm){
						$('#load-screen').show(); //show loader
                        jQuery.ajax({
                            type: "POST",
                            url: "/agency/ajax_delete_agency_api_integration",
                            dataType: 'json',
                            data: { 
                                api_integration_id: api_integration_id,
                                api_id: api_id,
                                agency_id: agency_id
                            }
                        }).done(function( ret ) {
                            if(ret.status){
                                $('#load-screen').hide(); //hide loader
                                swal({
                                    title:"Success!",
                                    text: "API Integration Successfully Deleted",
                                    type: "success",
                                    showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                                    timer: <?php echo $this->config->item('timer') ?>
                                });
                                setTimeout(function(){ location.reload(); }, <?php echo $this->config->item('timer') ?>);	
                            }
                        });	
					}
				}
			);

        });

         //Delete API TOKEN
         jQuery(".remove_agency_token_btn").click(function(){
            
            var agency_api_token_id = $(this).attr('data-api_token_id');
            var api_id = $(this).attr('data-api_id');
            var agency_id = <?php echo $agency_id; ?>;

            swal(
				{
					title: "",
					text: "This will delete this API Token. Proceed?",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-success",
					confirmButtonText: "Yes",
					cancelButtonText: "No, Cancel!",
					closeOnConfirm: false,
					closeOnCancel: true,
				},
				function(isConfirm){
					if(isConfirm){
						$('#load-screen').show(); //show loader
                        jQuery.ajax({
                            type: "POST",
                            url: "/agency/ajax_delete_agency_api_token",
                            dataType: 'json',
                            data: { 
                                agency_api_token_id: agency_api_token_id,
                                api_id: api_id,
                                agency_id: <?php echo $agency_id; ?>
                            }
                        }).done(function( ret ) {
                            if(ret.status){
                                $('#load-screen').hide(); //hide loader
                                swal({
                                    title:"Success!",
                                    text: "API Token Successfully Deleted",
                                    type: "success",
                                    showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                                    timer: <?php echo $this->config->item('timer') ?>
                                });
                                setTimeout(function(){ location.reload(); }, <?php echo $this->config->item('timer') ?>);	
                            }
                        });	
					}
				}
			);

        });

        jQuery("#api_connected_service").change(function(){

            var obj = jQuery(this);
            var connected_service = obj.val();
            var agency_id = <?php echo $agency_id  ?>;

            jQuery("#load-screen").show();
            jQuery.ajax({
                    type: "POST",
                    url: "/agency/ajax_check_agency_api_integration_selected",
                    data: { 
                        agency_id: agency_id,
                        connected_service: connected_service
                    }
                }).done(function( ret ){
                    
                    jQuery("#load-screen").hide();
                    if( parseInt(ret) > 0 ){
                        swal('','API service already selected','error');
                        obj.find("option:eq(0)").prop("selected",true); // unselect
                    }

                });	

        });

        //Add new API
        $('#btn_save_api_integ').on('click',function(){

            var api = $('#api_connected_service').val();

            var err = "";
            var submitcount = 0 ;

            if(api==""){
                err+="Software must not be empty. \n";
            }

            if(err!=""){
                swal('',err,'error');
                return false;
            }

            if(submitcount==0){

                submitcount++;
                $('#add_api_form').submit();

            }else{
                swal('','Form submission is in progress.');
                return false;
            }

        })

        jQuery("#btn_unlink_connected_api_prop").click(function(){

            swal(
                {
                    title: "",
                    text: "This will unlink ALL property that are connected to API under this agency. Are you sure you want to proceed?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-success",
                    confirmButtonText: "Yes",
                    cancelButtonClass: "btn-danger",
                    cancelButtonText: "No, Cancel!",
                    closeOnConfirm: false,
                    closeOnCancel: true,
                },
                function(isConfirm){
                    if(isConfirm){
                        $('#load-screen').show(); //show loader
                        swal.close();
                                jQuery.ajax({
                                type: "POST",
                                url: "<?php echo base_url('/agency/ajax_unlink_connected_api_prop') ?>",
                                dataType: 'json',
                                data: {
                                    agency_id: <?php echo $agency_id; ?>
                                }
                                }).done(function(data){
                                    
                                    if(data.status){
                                        $('#load-screen').hide(); //hide loader
                                        swal({
                                            title:"Success!",
                                            text: "Connected PropertyMe Properties has been unlinked",
                                            type: "success",
                                            showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                                            timer: <?php echo $this->config->item('timer') ?>
                                        });
                                        setTimeout(function(){ location.reload(); }, <?php echo $this->config->item('timer') ?>);	     
                                    }
                                });
                            }
                    
                }
            );

        });

        /** API BILlABLE CHECKBOX */
        jQuery("#api_billable").change(function(){

            var node = jQuery(this);
            var api_billable = ( node.prop("checked") == true )?1:0;
            var og_api_billable = $('#og_api_billable').val();

            jQuery("#load-screen").show();
            jQuery.ajax({
                type: "POST",
                url: "/agency/ajax_api_billable_toggle",
                dataType: 'json',
                data: {
                    agency_id: <?php echo $agency_id; ?>,
                    api_billable: api_billable,
                    og_api_billable: og_api_billable
                }
            }).done(function( ret ) {
                if(ret.status){
                    jQuery("#load-screen").hide();
                    jQuery("#og_api_billable").val(ret.api_val);
                    jQuery("#api_billable_green_tick").show();
                    setTimeout(function(){ 
                        jQuery("#api_billable_green_tick").hide();
                    }, 2000);
                }else{
                    swal('','Error: Please contact admin.','error');
                }
            });	

        });

        jQuery("#no_bulk_match").change(function(){

            var node = jQuery(this);
            var no_bulk_match = ( node.prop("checked") == true )?1:0;
            var og_no_bulk_match = $("#og_no_bulk_match").val();

            jQuery("#load-screen").show();
            jQuery.ajax({
                type: "POST",
                url: "/agency/ajax_no_bulk_match_toggle",
                dataType: 'json',
                data: { 
                    agency_id: <?php echo $agency_id; ?>,
                    no_bulk_match: no_bulk_match,
                    og_no_bulk_match: og_no_bulk_match
                }
            }).done(function( ret ) {
                if(ret.status){
                    jQuery("#load-screen").hide();
                    jQuery("#og_no_bulk_match").val(ret.no_bulk_match_val);
                    jQuery("#no_bulk_match_green_tick").show();
                    setTimeout(function(){ 
                        jQuery("#no_bulk_match_green_tick").hide();
                    }, 2000);
                }
            });	

        });

        //Update landline
        jQuery("#btn_update_agency_landline").click(function(){
            var phone = $('#phone').val();
            var og_phone = $('#og_phone').val();
            var err = "";
            
            if(phone==""){
                err+="Office/Phone must not be empty. \n";
            }

            if(err!=""){
                swal('',err,'error');
                return false;
            }

            $('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/agency/ajax_update_agency",
                dataType: 'json',
                data: {
                    type: $(this).attr('id'),
                    agency_id: <?php echo $agency_id; ?>,
                    phone: phone
                }
            }).done(function( ret ) {	
                $('#load-screen').hide();
                if(ret.status){
                    $('#load-screen').hide();
                    swal({
                        title:"Success!",
                        text: "Update Successful",
                        type: "success",
                        showCancelButton: false,
                        confirmButtonText: "OK",
                        closeOnConfirm: false,  
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });
                    var full_url = "/agency/view_agency_details/<?php echo $agency_id; ?>";
                    setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
                }
            });

        })

        jQuery("#btn_update_agency_contact").click(function(){
            var ac_fname = $('#ac_fname').val();
            var og_ac_fname = $('#og_ac_fname').val();
            var ac_lname = $('#ac_lname').val();
            var og_ac_lname = $('#og_ac_lname').val();
            var ac_phone = $('#ac_phone').val();
            var og_ac_phone = $('#og_ac_phone').val();
            var ac_email = $('#ac_email').val();
            var og_ac_email = $('#og_ac_email').val();
            var err = "";
            
            if(ac_fname==""){
                err+="Agency contact first name must not be empty. \n";
            }

            if(err!=""){
                swal('',err,'error');
                return false;
            }

            $('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/agency/ajax_update_agency",
                dataType: 'json',
                data: {
                    type: $(this).attr('id'),
                    agency_id: <?php echo $agency_id; ?>,
                    ac_fname: ac_fname,
                    og_ac_fname: og_ac_fname,
                    ac_lname: ac_lname,
                    og_ac_lname: og_ac_lname,
                    ac_phone: ac_phone,
                    og_ac_phone: og_ac_phone,
                    ac_email: ac_email,
                    og_ac_email: og_ac_email
                }
            }).done(function( ret ) {	
                $('#load-screen').hide();
                if(ret.status){
                    $('#load-screen').hide();
                    swal({
                        title:"Success!",
                        text: "Update Successful",
                        type: "success",
                        showCancelButton: false,
                        confirmButtonText: "OK",
                        closeOnConfirm: false,  
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });
                    var full_url = "/agency/view_agency_details/<?php echo $agency_id; ?>";
                    setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
                }
            });

        })

        jQuery("#btn_update_agency_account_contact").click(function(){
            var acc_name = $('#acc_name').val();
            var og_acc_name = $('#og_acc_name').val();
            var acc_phone = $('#acc_phone').val();
            var og_acc_phone = $('#og_acc_phone').val();
           
            var err = "";
            
            if(acc_name==""){
                err+="Agency account first name must not be empty. \n";
            }

            if(err!=""){
                swal('',err,'error');
                return false;
            }

            $('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/agency/ajax_update_agency",
                dataType: 'json',
                data: {
                    type: $(this).attr('id'),
                    agency_id: <?php echo $agency_id; ?>,
                    acc_name: acc_name,
                    og_acc_name: og_acc_name,
                    acc_phone: acc_phone,
                    og_acc_phone: og_acc_phone
                }
            }).done(function( ret ) {	
                $('#load-screen').hide();
                if(ret.status){
                    $('#load-screen').hide();
                    swal({
                        title:"Success!",
                        text: "Update Successful",
                        type: "success",
                        showCancelButton: false,
                        confirmButtonText: "OK",
                        closeOnConfirm: false,  
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });
                    var full_url = "/agency/view_agency_details/<?php echo $agency_id; ?>";
                    setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
                }
            });

        })

        jQuery("#btn_update_agency_tenant").click(function(){
            var tdc_name = $('#tdc_name').val();
            var og_tdc_name = $('#og_tdc_name   ').val();
            var tdc_phone = $('#tdc_phone').val();
            var og_tdc_phone = $('#og_tdc_phone').val();
           
            var err = "";
            
            if(tdc_name==""){
                err+="Tenant name must not be empty. \n";
            }

            if(err!=""){
                swal('',err,'error');
                return false;
            }

            $('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/agency/ajax_update_agency",
                dataType: 'json',
                data: {
                    type: $(this).attr('id'),
                    agency_id: <?php echo $agency_id; ?>,
                    tdc_name: tdc_name,
                    og_tdc_name: og_tdc_name,
                    tdc_phone: tdc_phone,
                    og_tdc_phone: og_tdc_phone
                }
            }).done(function( ret ) {	
                $('#load-screen').hide();
                if(ret.status){
                    $('#load-screen').hide();
                    swal({
                        title:"Success!",
                        text: "Update Successful",
                        type: "success",
                        showCancelButton: false,
                        confirmButtonText: "OK",
                        closeOnConfirm: false,  
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });
                    var full_url = "/agency/view_agency_details/<?php echo $agency_id; ?>";
                    setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
                }
            });

        })

        jQuery("#btn_update_agency_notes").click(function(){
            var agency_specific_notes = $('#agency_specific_notes').val();
            var og_agency_specific_notes = $('#og_agency_specific_notes   ').val();
            var comment = $('#comment').val();
            var og_comment = $('#og_comment').val();

            $('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/agency/ajax_update_agency",
                dataType: 'json',
                data: {
                    type: $(this).attr('id'),
                    agency_id: <?php echo $agency_id; ?>,
                    agency_specific_notes: agency_specific_notes,
                    og_agency_specific_notes: og_agency_specific_notes,
                    comment: comment,
                    og_comment: og_comment
                }
            }).done(function( ret ) {	
                $('#load-screen').hide();
                if(ret.status){
                    $('#load-screen').hide();
                    swal({
                        title:"Success!",
                        text: "Update Successful",
                        type: "success",
                        showCancelButton: false,
                        confirmButtonText: "OK",
                        closeOnConfirm: false,  
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });
                    var full_url = "/agency/view_agency_details/<?php echo $agency_id; ?>";
                    setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
                }
            });

        })

        $('.btn-update-total-prop').on('click',function(){

            var total_prop = $('#total_prop').val();
            var og_total_prop = $('#og_total_prop').val();

            $('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/agency/update_total_properties",
                dataType: 'json',
                data: {
                    agency_id: <?php echo $agency_id; ?>,
                    total_prop: total_prop,
                    og_total_prop: og_total_prop
                }
            }).done(function( ret ) {	
                $('#load-screen').hide();
                if(ret.status){
                    $('#load-screen').hide();
                    swal({
                        title:"Success!",
                        text: "Update Successful",
                        type: "success",
                        showCancelButton: false,
                        confirmButtonText: "OK",
                        closeOnConfirm: false,  
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });
                    var full_url = "/agency/view_agency_details/<?php echo $agency_id; ?>";
                    setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
                }
            });

        })

        $('#btn-update-website').on('click',function(){

            var website = $('#website').val();
            var og_website = $('#og_website').val();

            $('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/agency/ajax_update_agency",
                dataType: 'json',
                data: {
                    type: $(this).attr('id'),
                    agency_id: <?php echo $agency_id; ?>,
                    website: website,
                    og_website: og_website
                }
            }).done(function( ret ) {	
                $('#load-screen').hide();
                if(ret.status){
                    $('#load-screen').hide();
                    swal({
                        title:"Success!",
                        text: "Update Successful",
                        type: "success",
                        showCancelButton: false,
                        confirmButtonText: "OK",
                        closeOnConfirm: false,  
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });
                    var full_url = "/agency/view_agency_details/<?php echo $agency_id; ?>";
                    setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
                }
            });	

        })

        $('#btn-update-joined-sats').on('click',function(){

            var joined_sats = $('#joined_sats').val();
            var og_joined_sats = $('#og_joined_sats').val();

            $('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/agency/ajax_update_agency",
                dataType: 'json',
                data: {
                    type: $(this).attr('id'),
                    agency_id: <?php echo $agency_id; ?>,
                    joined_sats: joined_sats,
                    og_joined_sats: og_joined_sats
                }
            }).done(function( ret ) {	
                $('#load-screen').hide();
                if(ret.status){
                    $('#load-screen').hide();
                    swal({
                        title:"Success!",
                        text: "Update Successful",
                        type: "success",
                        showCancelButton: false,
                        confirmButtonText: "OK",
                        closeOnConfirm: false,  
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });
                    var full_url = "/agency/view_agency_details/<?php echo $agency_id; ?>";
                    setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
                }
            });	

        })
        
        /** NEW CODES > AFTER DANS VAD VISUAL CHANGES */

        $('#agency_addresses').on('change',function(){

            var obj = $(this);
            var type = obj.val();
            $('#tbl_key_address_body').html('');
            $('#load-screen').show();

            jQuery.ajax({
                type: "POST",
                url: "/agency/ajax_check_agency_addresses_duplicate",
                dataType: 'json',
                data: {
                    agency_id: <?php echo $agency_id; ?>,
                    address_type: type
                }
            }).done(function( ret ) {	
                if(ret.status){
                    $('#ajax_address_div').empty();
                    $('#ajax_address_div').load('/agency/ajax_load_vad_addresses',{agency_id:<?php echo $row['agency_id'] ?>,type: type}, function(response, status, xhr){
                        $('#load-screen').hide();
                        $('#fancybox_agency_address .card').css('min-width','500px');
                        if(type==1){
                            $('#group-region').hide();
                            $('#btn_update_key_address').hide();
                            $('#btn_add_key_address').hide();
                            $('#tbl_key_address').hide();
                            $('#btn_update_agency_address').show().html('Update Mailing Address');
                            $('#btn_delete_agency_address').show().html('Delete Mailing Address');
                            
                        }else if(type==2){
                            $('#fancybox_agency_address').css('min-width','58.35%');
                            $('#group_form').hide();
                            $('#tbl_key_address').show();
                            var table_data = '';
                            $.each(ret.data, function (index, value) {
                                table_data += "<tr><td>"+value.address_1+"</td><td>"+value.address_2+"</td><td>"+value.address_3+"</td><td>"+value.state+"</td><td>"+value.postcode+"</td><td><div class='btn-group'><button type='button' class='btn btn-warning btn-edit' data-id='"+value.id+"'><span class='fa fa-edit'></span></button><button type='button' class='btn btn-danger btn-delete' data-id='"+value.id+"'><span class='fa fa-trash'></span></button></div></td></tr>";
                            });
                            $('#tbl_key_address_body').append(table_data);
                            $('#btn_delete_agency_address').hide();
                            $('#btn_update_agency_address').hide();
                            $('#btn_add_agency_address').hide();
                            $('#group-region').hide();
                            $('#btn_add_key_address').show();
                        }else{
                            $('#group-region').show();
                            $('#tbl_key_address').hide();
                            $('#btn_update_key_address').hide();
                            $('#btn_add_key_address').hide();
                            $('#btn_update_agency_address').show().html('Update');
                            $('#btn_delete_agency_address').hide();
                            $('#btn_add_agency_address').hide();
                        }
                        
                    });
                }else{
                    $('#ajax_address_div').empty();
                    $('#ajax_address_div').load('/agency/ajax_load_vad_addresses',{agency_id:<?php echo $row['agency_id'] ?>,type: type}, function(response, status, xhr){
                        $('#load-screen').hide();
                        $('#btn_delete_agency_address').hide();
                        $('#fancybox_agency_address .card').css('min-width','500px');
                        if(type==1){
                            $('#group-region').hide();
                            $('#tbl_key_address').hide();
                            $('#btn_update_key_address').hide();
                            $('#btn_add_key_address').hide();
                            $('#btn_update_agency_address').show().html('Add Mailing Address');
                        }else if(type==2){
                            $('#group-region').hide();
                            $('#group_form').hide();
                            $('#btn_update_agency_address').hide();
                            $('#btn_add_key_address').show();
                            $('#fancybox_agency_address').css('min-width','58.35%');
                            $('#tbl_key_address').show();
                            var table_data = '';
                            table_data += "<tr class='text-center'><td colspan='6'>No Address</td></tr>";
                            $('#tbl_key_address_body').append(table_data);
                        }else{
                            $('#group-region').show();
                            $('#tbl_key_address').hide();
                            $('#btn_update_key_address').hide();
                            $('#btn_add_key_address').hide();
                            $('#btn_update_agency_address').show().html('Update');
                        }
                        
                    });
                }
                if(type != 2){
                    $('#tbl_key_address').hide();
                    $('#btn_update_key_address').hide();
                    $('#btn_add_key_address').hide();
                }
                
            });	

        })

        $('#btn_delete_agency_address').on('click',function(){

            var type = $('#agency_addresses').val();

            $('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/agency/ajax_delete_agency_addresses",
                dataType: 'json',
                data: {
                    agency_id: <?php echo $agency_id; ?>,
                    address_type: type
                }
            }).done(function( ret ) {	
                
                if(ret.status){
                  
                    $('#load-screen').hide();
                    swal({
                        title:"Success!",
                        text: "Delete Successful",
                        type: "success",
                        showCancelButton: false,
                        confirmButtonText: "OK",
                        closeOnConfirm: false,  
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });
                    var full_url = "/agency/view_agency_details/<?php echo $agency_id; ?>";
                    setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);

                }
                
            });	

        })


        //Update fronchise group
        $('#from_other_company_btn_update').on('click',function(){

            var from_other_company = $("#from_other_company").val();
            var err = "";

            $('#load-screen').show();
                jQuery.ajax({
                type: "POST",
                url: "/agency/ajax_update_agency",
                dataType: 'json',
                data: {
                    type: $(this).attr('id'),
                    agency_id: <?php echo $agency_id; ?>,
                    from_other_company: from_other_company
                }
            }).done(function( ret ) {

                $('#load-screen').hide();
                swal({
                    title:"Success!",
                    text: "Update Successful",
                    type: "success",
                    showCancelButton: false,
                    confirmButtonText: "OK",
                    closeOnConfirm: false,  
                    showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                    timer: <?php echo $this->config->item('timer') ?>
                });
                var full_url = "/agency/view_agency_details/<?php echo $agency_id; ?>";
                setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
                
            });

        });


        $('body').on('click', '.btn-edit', function() {
            let id = $(this).data('id');
            $('#load-screen').show();

            jQuery.ajax({
                    type: "POST",
                    url: "/agency/get_key_address",
                    dataType: 'json',
                    data: { 
                        id: id
                    }
            }).done(function(data) {
            $('#load-screen').hide();

                let address = data.address_1 + ' ' + data.address_2 + ' ' + data.address_3;
                //$('#edit_id').val(id);
                $('#edit_id').val(data.id);
                $('#fullAdd').val(address);
                $('#og_fullAdd').val(address);
                $('#address_1').val(data.address_1);
                $('#address_2').val(data.address_2);
                $('#address_3').val(data.address_3);
                $('#state').val(data.state);
                $('#postcode').val(data.postcode);
                $('#group_form').show();
                $('#btn_update_key_address').show().text('Update Key Address');
            });	
        });

        $('body').on('click', '#btn_add_key_address', function() {
            $('#load-screen').show();
            $('#fullAdd').val('');
            $('#og_fullAdd').val('');
            $('#address_1').val('');
            $('#address_2').val('');
            $('#address_3').val('');
            $('#state').val('');
            $('#postcode').val('');
            $('#btn_update_key_address').show().text('Save Key Address');
            $('#load-screen').hide();
            $('#group_form').show();
        });

         // Add/Update Key Address
         $('body').on('click', '#btn_update_key_address', function(e) {
            e.preventDefault();
            var edit_id = $('#edit_id').val();
            var address_1 = $('#address_1').val();
            var address_2 = $('#address_2').val();
            var address_3 = $('#address_3').val();
            var state = $('#state').val();
            var postcode = $('#postcode').val();
            var postcode_region_name = $('#add_postcode_region_name').val();
            var fullAdd = $('#fullAdd').val();
            var address_type = 2;

            console.log('Add/Edit Key Address');
            
            var err = "";
            if(fullAdd == "" || address_1 == "" || address_2 == "" || address_3 == "" || state == "" || postcode == "" || postcode_region_name == ""){
                err+="Complete address is required";
            }

            if(err!=""){
                swal('',err,'error');
                return false;
            }

            $('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/agency/add_key_address",
                dataType: 'json',
                data: {
                    agency_id: <?php echo $agency_id; ?>,
                    edit_id: edit_id,
                    address_1: address_1,
                    address_2: address_2,
                    address_3: address_3,
                    state: state,
                    postcode: postcode,
                    fullAdd: fullAdd,
                    postcode_region_name: postcode_region_name,
                    address_type: address_type,
                }
            }).done(function( ret ) {	
                $('#load-screen').hide();
                console.log("Returned Data:");
                console.log(ret);

                if(ret.status){
                    if(ret.add){
                        var stat = "Successfully Added";
                        var title = "Success!";
                        var type = "success"
                    }
                    else if(ret.update){
                        var stat = "Successfully Updated";
                        var title = "Success!";
                        var type = "success"
                    }
                    $('#load-screen').hide();
                    swal({
                        title: title,
                        text: stat,
                        type: type,
                        showCancelButton: false,
                        confirmButtonText: "OK",
                        closeOnConfirm: false,  
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });
                    var full_url = "/agency/view_agency_details/<?php echo $agency_id; ?>";
                    setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
                }
                else{
                    if(ret.duplicate){
                        var stat = "You have entered a duplicate address, are you sure you want to proceed?";
                        var title = "Warning!";
                        var type = "error"
                    }

                    swal({
                    title: title,
                    text:  stat,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                    type: "warning",
                    showCancelButton: true,
                    cancelButtonClass: "btn-danger",
                    confirmButtonClass: "btn-success",
                    closeOnConfirm: false,
                    confirmButtonText: "Yes, Proceed",
                    cancelButtonText: "No, Cancel!",
                    closeOnCancel: true
                    }, function(isConfirm) {
                        if (isConfirm) {
                            location.reload();
                        }
                    });
                }
            });	
        });

        $('body').on('click', '.btn-delete', function() {
            let id = $(this).data('id');
            console.log('Delete: '+id);
            swal({
                title: "Are you sure?",
                text: "Once deleted, you will not be able to recover this Address!",
                icon: "danger",
                buttons: true,
                dangerMode: true,
                type: "error",
                confirmButtonClass: "btn-success"
                },
                
                function(isConfirm) {
					//location.reload();
                    $('#load-screen').show();
                    jQuery.ajax({
                        type: "POST",
                        url: "/agency/delete_keyAddress",
                        dataType: 'json',
                        data: {
                            id: id,
                            agency_id: <?php echo $agency_id; ?>
                        }
                    }).done(function( ret ) {	
                        if(ret.status){
                            $('#load-screen').hide();
                            swal({
                                title:"Success!",
                                text: "Delete Successful",
                                type: "success",
                                showCancelButton: false,
                                confirmButtonText: "OK",
                                closeOnConfirm: false,  
                                showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                                timer: <?php echo $this->config->item('timer') ?>
                            });
                            var full_url = "/agency/view_agency_details/<?php echo $agency_id; ?>";
                            setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
                        }
                    });	
				});
        })



        // high touch ajax save
        $("#priority").change(function(){
            var high_touch_dom = jQuery(this);
            var priority = ( high_touch_dom.prop("checked") == true )? 1 : 0;
            $(this).val(priority);
        });

        // $("#priority_reason").on("change keyup keydown hover", function() {
        //     if($(this).val().length > 0) {
        //         $(this).removeClass("error-text");
        //         $(".error_panel span").addClass("hide");
        //         $(".error_panel span").removeClass("show");
        //     } else {
        //         $(this).addClass("error-text");
        //         $(".error_panel span").addClass("show");
        //         $(".error_panel span").removeClass("hide");
        //     }
        // });
        
    });


     //------------GOOGLE ADDRESS AUTOCOMPLETE START
     var placeSearch, autocomplete;
    var componentForm2 = {
        route: {
            'type': 'long_name',
            'field': 'address_2'
        },
        administrative_area_level_1: {
            'type': 'short_name',
            'field': 'state'
        },
        postal_code: {
            'type': 'short_name',
            'field': 'postcode'
        }
    };

    function initAutocomplete() {

        <?php if( $this->config->item('country') ==1 ){ ?>
            var cntry = 'au';
        <?php }else{ ?>
            var cntry = 'nz';
        <?php } ?>

        var options = {
            types: ['geocode'],
            componentRestrictions: {country: cntry}
        };

        autocomplete = new google.maps.places.Autocomplete(
            (document.getElementById('fullAdd')),
            options
        );
        autocomplete.addListener('place_changed', fillInAddress);

    }

    function fillInAddress() {

        var place = autocomplete.getPlace();

        for (var i = 0; i < place.address_components.length; i++) {
            var addressType = place.address_components[i].types[0];
            if (componentForm2[addressType]) {
                var val = place.address_components[i][componentForm2[addressType].type];
                document.getElementById(componentForm2[addressType].field).value = val;
            }
        }

        // street name
        var ac = jQuery("#fullAdd").val();
        var ac2 = ac.split(" ");
        var street_number = ac2[0];
        console.log(street_number);
        jQuery("#address_1").val(street_number);

        // suburb
        jQuery("#address_3").val(place.vicinity);

        console.log(place);
    }
    //------------GOOGLE ADDRESS AUTOCOMPLETE END
</script>