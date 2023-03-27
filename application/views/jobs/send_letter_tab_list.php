<section>
    <div class="body-typical-body">
        <div class="table-responsive">
            <table class="table table-hover main-table jtable_list">
                <thead>
                    <tr>
                        <th>Added By</th>
                        <th>Job Type</th>
                        <th>Service</th>
                        <th>Price</th>
                        <th>Address</th>                        
                        <th>Agency</th>	
                        <th style="width:20%;">Job Notes</th>
                        <th style="width:15%;">Property Notes</th>
                        <th>Details</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Job#</th>
                        <th class="check_all_th">
                            <div class="checkbox checkbox_all email_chk_all" style="margin:0;">
                                <input type="checkbox" id="<?php echo $current_tab ?>_chk_email_all" class="chk_email_all" />
                                <label for="<?php echo $current_tab ?>_chk_email_all">&nbsp;</label>
                            </div>
                        </th>
                        <th class="check_all_th">
                            <div class="checkbox checkbox_all sms_chk_all" style="margin:0;">
                                <input type="checkbox" id="<?php echo $current_tab ?>_chk_sms_all" class="chk_sms_all" />
                                <label for="<?php echo $current_tab ?>_chk_sms_all">&nbsp;</label>
                            </div>
                        </th>
                        <th class="check_all_th">
                            <div class="tbl-tp-name colorwhite bold">
                                <div class="checkbox checkbox_all no_tenant_chk_all" style="margin:0;">
                                    <input type="checkbox" id="<?php echo $current_tab ?>_chk_no_tenant_all" class="chk_no_tenant_all" />
                                    <label for="<?php echo $current_tab ?>_chk_no_tenant_all">&nbsp;</label>
                                </div>
                            </div>
                        </th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    if($lists->num_rows()>0){

                        // var_dump($lists->result_array());
                        $i = 0;
                        foreach($lists->result_array() as $list_item){ 	

                            $row_color = null;										
                            $has_tenants = false;
                            $has_tenant_email = 0;
                            $has_mobile_num = 0;

                            $has_conditions = false;																				

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
                                'property_id' => $list_item['property_id'],
                                'pt_active' => 1,
                                'display_query' => 0
                            );
                            $pt_sql = $this->properties_model->get_property_tenants($params);

                            if( $pt_sql->num_rows() > 0 ){

                                foreach($pt_sql->result() as $pt_row){	

                                    // check if it has tenants
                                    if(  $pt_row->tenant_firstname != "" || $pt_row->tenant_lastname != ""  ){
                                        $has_tenants = true;
                                    }
                                    
                                    // check if there is at least 1 tenant email
                                    if( $pt_row->tenant_email != ""  ){
                                        $has_tenant_email = 1;
                                    }
                                    
                                    // check if there is at least 1 tenant mobile
                                    if( $pt_row->tenant_mobile != "" ){
                                        $has_mobile_num = 1;
                                    }
                                }
                                
                            }else{

                                $has_tenants = false;

                            }
                            
                            
                            if($list_item['property_vacant']==1 || $has_tenants == false) {
                                $is_no_tenants = 1;
                            }else{
                                $is_no_tenants = 0;
                            }																				
                            
                            // job or property comments
                            if( $list_item['j_comments'] != "" || $list_item['p_comments'] != "" ){
                                $row_color = "yellowRowBg";
                                $has_conditions = true;
                            }	

                            // urgent jobs
                            if($list_item['urgent_job']==1){
                                $row_color = "greenRowBg";
                            }

                            // has conditions
                            if( ( $current_tab == 'ar_tab' && $has_conditions == true ) || ( $current_tab == 'nar_tab' && $has_conditions == false ) ){ 

                        ?>
                        <tr class="body_tr jalign_left <?php echo $row_color; ?> <?php echo ($is_no_tenants==1)?'no_tenants_row':'tenant_present_row'; ?> <?php echo ($has_tenant_email==1)?'has_tenants_email_row':'no_tenants_email_row'; ?> <?php echo ($has_mobile_num==1)?'has_mobile_num_row':'no_mobile_num_row'; ?>">
                            <td>
                                <?php echo $this->gherxlib->getWhoCreatedSendLetters($list_item['property_id']); ?>
                            </td>
                            <td>
                                <?php echo $this->gherxlib->getJobTypeAbbrv($list_item['job_type']); ?>
                            </td>
                            <td>												
                                <?php
                                // display icons
                                $job_icons_params = array(
                                    'service_type' => $list_item['jservice'],
                                    'job_type' => $list_item['job_type'],
                                    'sevice_type_name' => $list_item['ajt_type']
                                );
                                echo $this->system_model->display_job_icons($job_icons_params);
                                ?>
                            </td>
                            <td>
                                <?php echo '$'.number_format($this->system_model->price_ex_gst($list_item['job_price']),2); ?>
                            </td>
                            <td>										
                                <?php 
                                $prop_address = "{$list_item['p_address_1']} {$list_item['p_address_2']}, {$list_item['p_address_3']} {$list_item['p_state']}";
                                echo $this->gherxlib->crmLink('vpd',$list_item['property_id'],$prop_address);
                                ?>
                            </td>                           
                            <td>                                
                                <?php 
                                $prop_address = "{$list_item['p_address_1']} {$list_item['p_address_2']}, {$list_item['p_address_3']} {$list_item['p_state']}";
                                echo $this->gherxlib->crmLink('vad',$list_item['agency_id'],$list_item['agency_name'],'',$list_item['priority']);
                                ?>
                            </td>
                            <td>
                                <?php echo $list_item['j_comments'] ?>
                            </td>
                            <td>
                                <?php echo $list_item['p_comments'] ?>
                            </td>
                            <td>
                                <?php 
                                if( $list_item['holiday_rental']==1 ){ ?>
                                    <img data-toggle="tooltip" title="Short Term Rental" class="holiday_rental" src="/images/row_icons/holiday_coloured.png" />
                                <?php	
                                }
                                ?>							
                                <?php 
                                    if( $is_no_tenants == 1 ){ ?>
                                        <img data-toggle="tooltip" title="No Tenants" class="no_tenant_icon" style="cursor: pointer;" src="/images/row_icons/no_tenant_coloured.png" />
                                <?php	
                                    }
                                ?>
                            </td>
                            <td>
                                <?php echo ( $this->system_model->isDateNotEmpty($list_item['start_date']) == true )?date('d/m/Y',strtotime($list_item['start_date'])):null; ?>
                            </td>
                            <td>										
                                <?php echo ( $this->system_model->isDateNotEmpty($list_item['due_date']) == true )?date('d/m/Y',strtotime($list_item['due_date'])):null; ?>
                            </td>						
                            <td>
                                <?php echo $this->gherxlib->crmLink('vjd',$list_item['jid'],$list_item['jid']);?>
                            </td>
                            <td>
                                <?php
                                if( $has_tenant_email == 1 ){ ?>
                                    
                                    <div class="tbl-tp-name colorwhite bold" style="float: left">
                                        <div class="checkbox" style="margin:0;">
                                            <input type="checkbox" id="e_<?php echo $list_item['jid']; ?>" class="job_id email_chk" value="<?php echo $list_item['jid']; ?>" />
                                            <label class="inline_checkbox lbl_email_chk_all" for="e_<?php echo $list_item['jid']; ?>">&nbsp;</label>
                                        </div>
                                    </div>
                                    <img data-toggle="tooltip" title="Email" class="email_it" src="/images/row_icons/mail_colored.png" />

                                <?php
                                }
                                ?>								
                            </td>
                            <td>
                                <?php
                                if( $has_mobile_num == 1 && $has_tenant_email!=1 ){ ?>									
                                    
                                    <div class="tbl-tp-name colorwhite bold" style="float: left">
                                        <div class="checkbox" style="margin:0;">
                                            <input type="checkbox" id="s_<?php echo $list_item['jid']; ?>" class="job_id_sms sms_chk" value="<?php echo $list_item['jid']; ?>" />
                                            <label class="inline_checkbox lbl_sms_chk_all" for="s_<?php echo $list_item['jid']; ?>">&nbsp;</label>
                                        </div>
                                    </div>
                                    <img data-toggle="tooltip" title="SMS" class="sms_it" style="width: 20px; cursor: pointer; width:24px; float: left" src="/images/row_icons/sms_colored.png" />

                                <?php
                                }
                                ?>								
                            </td>
                            <td>
                                <?php
                                if( $is_no_tenants == 1 ){ ?>

                                    <div class="tbl-tp-name colorwhite bold" style="float: left">
                                        <div class="checkbox" style="margin:0;">
                                            <input type="checkbox" id="n_<?php echo $list_item['jid']; ?>" class="maps_chk_box no_tenants_chk" value="<?php echo $list_item['jid']; ?>" />
                                            <label class="inline_checkbox lbl_no_tenant_chk_all" for="n_<?php echo $list_item['jid']; ?>">&nbsp;</label>
                                        </div>
                                    </div>
                                    <img data-toggle="tooltip" title="No Tenants" class="no_tenant_icon" style="cursor: pointer; float: left" src="/images/row_icons/no_tenant_coloured.png" />
                                    
                                <?php	
                                }
                                ?>	
                                <input type="hidden" class="hid_job_id" value="<?php echo $list_item['jid']; ?>" />
                            </td>							
                            <td>
                                <?php
                                echo ($list_item['jcreated']!="")?date("H:i",strtotime($list_item['jcreated'])):'';
                                ?>
                            </td>	
                        </tr>
                    <?php 
                            }
                        $i++; 
                        }

                    }else{
                        echo "<tr><td colspan='16'>No Data</td></tr>";
                    }
                    ?>
                </tbody>

            </table>
            

        </div>

    

    </div>
</section>