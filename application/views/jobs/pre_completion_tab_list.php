<table class="table table-hover main-table jtable">
    <thead>
        <tr>
            <th>Age</th>
            <th>
                <a 
                href="<?php echo $page_search_url; ?>&order_by=j.date&sort=<?=$change_sort_to?>" 
                class="jsortable <?php echo ( $order_by == 'j.date' )?$sort:null; ?>"
                >Date</a>
            </th>
            <th>Last YM</th>
            <th>
                <a 
                href="<?php echo $page_search_url; ?>&order_by=j.job_type&sort=<?=$change_sort_to?>" 
                class="jsortable <?php echo ( $order_by == 'j.job_type' )?$sort:null; ?>"
                >Job Type</a>
            </th>
            <th>
                <a 
                href="<?php echo $page_search_url; ?>&order_by=j.job_price&sort=<?=$change_sort_to?>" 
                class="jsortable <?php echo ( $order_by == 'j.job_price' )?$sort:null; ?>"
                >Price</a>
            </th>
            <th>Service</th>
            <th>
                <a 
                href="<?php echo $page_search_url; ?>&order_by=j.job_price&sort=<?=$change_sort_to?>" 
                class="jsortable <?php echo ( $order_by == 'j.job_price' )?$sort:null; ?>"
                >Address</a>
            </th>
            <th>Tech</th>
            <th>DK</th>
            <th>Reason</th>
            <th>Comments</th>
            <th>Job#</th>
            <th class="check_all_td">
                <div class="checkbox  check_all_div <?php echo $current_tab; ?>_check_all_div" style="margin:0;">
                    <input name="chk_all" type="checkbox" id="<?php echo $current_tab; ?>_check-all" class="check-all">
                    <label for="<?php echo $current_tab; ?>_check-all">&nbsp;</label>
                </div>
            </th>                       
            <th>&nbsp;</th>
            <?php
            if( $current_tab == 'unable_to_complete' && $this->input->get_post('jobs_not_comp_res') > 0 ){ ?>
                <th class="text-center">
                    <!--<span class="sms_check_all_lbl">SMS</span>-->
                    <div class="checkbox  sms_check_all_div <?php echo $current_tab; ?>_sms_check_all_div">
                        <input name="sms_chk_all" type="checkbox" id="<?php echo $current_tab; ?>sms_check-all" class="sms_check-all">
                        <label for="<?php echo $current_tab; ?>sms_check-all">&nbsp;</label>
                    </div>
                </th>
            <?php
            }
            ?>             
        </tr>
    </thead>
    <tbody>
        <?php
                    // echo "<pre>";
                    // var_dump($lists);
                    // die();
        if(count($lists)>0){
        foreach($lists as $list_item){	

            /*echo "====DATA in VIEW";
            echo "<pre>";
            print_r($list_item);
            echo "</pre>";
            */
            //exit();

            $row_color = '';
			$reason = '';
            $hide_ck = 0;
            $allow_inline_job_type_update = false;
            $reason_icon = '';
            $today = date('Y-m-d');

			// Expiry Dates don't match
			if( $this->system_model->isAlarmExpiryDatesMatch($list_item['jid'])==true ){
				$hide_ck = 1;
				$row_color = 'green_mark';
				$reason .= "Expiry Dates Don't Match <br />";             
			}

			// hide for FG: Compass Housing
			if( $list_item['franchise_groups_id'] != 39 && $this->config->item('country')== 1 ){
				
				// Job is $0 and YM
				if( $this->system_model->isJobZeroPrice_Ym($list_item['jid'])==true ){
					$hide_ck = 1;
					$row_color = 'green_mark';
					$reason .= "Job is $0 and YM <br />";
				}
				
			}

			// New Alarms Installed
			if( $this->system_model->isJobHasNewAlarm($list_item['jid'])==true ){
				$hide_ck = 1;
				$row_color = 'green_mark';
				$reason .= "New Alarms Installed <br />";
			}

			// get SA IC price
            $sa_ic_price = $this->system_model->getIcAlarmAgencyService($list_item['a_id']);
			if( $list_item['prop_upgraded_to_ic_sa'] == 1 && $list_item['j_type'] == 'Yearly Maintenance' && $list_item['j_price'] != $sa_ic_price  ){
				$hide_ck = 1;
				$row_color = 'green_mark';
				$reason .= "IC Job not \${$sa_ic_price}<br />";
			}

			// if IC updgrade
			if($list_item['j_type']=='IC Upgrade'){
				$hide_ck = 1;
				$row_color = 'green_mark';

                //$reason .= "Job type can't be IC upgrade, make job Once Off<br />";
                $reason .= "IC Upgrade job, verify data<br />";				
			}            			

			// Property has Expired Alarms
			if( $this->system_model->isPropertyAlarmExpired($list_item['jid'],$list_item['prop_id'])==true ){
				$hide_ck = 1;
				$row_color = 'green_mark';
				$reason .= "Expired Alarms <br />";
			}

			// COT FR and LR price must be 0
			if( $this->system_model->CotLrFrPriceMustBeZero($list_item['jid'])==true ){
                $allow_inline_job_type_update = true;
				$hide_ck = 1;
				$row_color = 'green_mark';
				$reason .= $this->gherxlib->getJobTypeAbbrv($list_item['j_type'])." must be $0 <br />";
			}

			// If 240v has 0 price
			if( $this->system_model->is240vPriceZero($list_item['jid'])==true ){
				$hide_ck = 1;
				$row_color = 'green_mark';
				$reason .= " Check Job Type <br />";
			}

			// if 240v rebook
			if($list_item['j_type']=='240v Rebook'){
				$hide_ck = 1;
				$row_color = 'green_mark';
				$reason .= "240v Rebook <br />";
			}

			// If discarded alarm is not equal to new alarm
			if( $this->system_model->isMissingAlarms($list_item['jid'])==true ){
				$hide_ck = 1;
				$row_color = 'green_mark';
				$reason .= " Discarded Alarms don't match Installed Alarms <br />";
			}
			
			// If NO alarms, exclude CW
			if( $this->system_model->isNoAlarms($list_item['jid'])==true && $list_item['j_service']!=6 ){
				$hide_ck = 1;
				$row_color = 'green_mark';
				$reason .= " No installed Alarms <br />";
			}

			// If job date is not today and empty
			if( $list_item['j_date'] == '' && $list_item['j_date'] != date("Y-m-d") ){
				$hide_ck = 1;
				$row_color = 'green_mark';
				$reason .= " Check Job Date <br />";
            }
            
            // If tech missing
			if( $list_item['assigned_tech'] == '' ){
				$hide_ck = 1;
				$row_color = 'green_mark';
				$reason .= " Tech missing <br />";
            }

			// If Job notes is present
			$tech_notes_pres_flag = 0;
			if( $list_item['tech_comments']!='' ){
				$hide_ck = 1;
				$row_color = 'green_mark';
				$reason .= " Check Tech notes <br />";
				$tech_notes_pres_flag = 1;
            }	
            
            // If Repair Notes is present
			$repair_notes_pres_flag = 0;
			if( $list_item['repair_notes']!='' ){
				$hide_ck = 1;
				$row_color = 'green_mark';
				$reason .= " Check Repair notes <br />";
				$repair_notes_pres_flag = 1;
			}

            /*
			// if franchise group = private
			if( $this->system_model->getAgencyPrivateFranchiseGroups($list_item['franchise_groups_id']) == true ){
				$hide_ck = 1;
				$row_color = 'green_mark';
				$reason .= " Payment Required Before Processing <br />";
            }
            */

			// If Urgent
			if( $list_item['urgent_job']==1 ){
				$hide_ck = 1;
				$row_color = 'green_mark';
				$reason .= " Urgent or Out of Scope <br />";
			}

			//  if SS has any switched that are marked failed
			if( $this->system_model->isSSfailed($list_item['jid'])==true ){
				$hide_ck = 1;
				$row_color = 'green_mark';
				$reason .= " Safety Switch Failed <br />";
			}

			//  if SS has any switched that are marked failed
			if( $this->system_model->isSafetySwitchServiceTypes($list_item['j_service'])==true && $list_item['ss_quantity']=='' ){
				$hide_ck = 1;
				$row_color = 'green_mark';
				$reason .= "Safety Switch Quantity is blank<br />";
            }

            // ts_safety_switch = 1 is Fusebox Viewed = NO, wierd i know right? 
            if( 
                $this->system_model->isSafetySwitchServiceTypes($list_item['j_service']) == true && 
                $list_item['ts_safety_switch'] == 1  && 
                ( is_numeric($list_item['ts_safety_switch_reason']) && $list_item['ts_safety_switch_reason'] == 0 ) 
            ){
				$hide_ck = 1;
				$row_color = 'green_mark';
				$reason .= " No Switch<br />";
			}
            

            //IF Property upgraded = No
            //And alarms required for upgrade is not greater than number of bedrooms by at least 1

            // total required alarm for qld upgrade
            $tot_req_al_for_qld = $list_item['qld_new_leg_alarm_num'] - $list_item['ps_number_of_bedrooms'];

			if( (  $list_item['p_state'] == 'QLD' && is_numeric($list_item['prop_upgraded_to_ic_sa']) && $list_item['prop_upgraded_to_ic_sa'] == 0 ) && $tot_req_al_for_qld < 1 ){
				$hide_ck = 1;
				$row_color = 'green_mark';
				$reason .= "Verify Quote Alarms<br />";
            }
            
            // if QLD property is upgrade to IC OR MSW property is both short term rental and compliant with NSW legislation and service type is not IC            
            if(  
                (
                    ( $list_item['p_state'] == 'QLD' && $list_item['prop_upgraded_to_ic_sa'] == 1 ) ||
                    ( $list_item['p_state'] == 'NSW' && $list_item['holiday_rental'] == 1 && $list_item['short_term_rental_compliant'] == 1 )

                ) &&
                !in_array( $list_item['j_service'],$ic_services) 
            ){
				$hide_ck = 1;
				$row_color = 'green_mark';
				$reason .= "Update Service Type to IC<br />";
            }        
            

            // if job has interconnected alarms and QLD number of required alarms > 0 
            if(  $list_item['p_state'] == 'QLD' && $list_item['prop_upgraded_to_ic_sa'] == 1 && $list_item['qld_new_leg_alarm_num'] > 0 ){
				$hide_ck = 1;
				$row_color = 'green_mark';
				$reason .= "Total Alarms Required Should be 0<br />";
			}			          
            
            // Pme supplier check
            if( $list_item['pme_prop_id'] == '' && $list_item['pme_supplier_id'] != '' ){
                $p_id = $list_item['prop_id'];
                $agency = $this->jobs_model->getAgencyId($p_id);
                //echo $this->db->last_query();
                
                $a_id = $agency[0]->agency_id;
                $pr_id = $list_item['prop_id'];

                $hide_ck = 1;
				$row_color = 'green_mark';
                $reason .= "
                <br />
                <a href='/property_me/property/$pr_id/$a_id'>
                    <span class='badge badge-primary'>PropertyMe</span>
                </a>
                <br />
                <br />
                ";  
                //$reason .= "<img class='reason_icon' src='/images/third_party/Pme.png' /> Needs PMe Link<br />"; 
            }

            // Palace API check
            if( $list_item['palace_prop_id'] == '' && $list_item['palace_diary_id'] != '' ){

                $p_id = $list_item['prop_id'];
                $agency = $this->jobs_model->getAgencyId($p_id);
                //echo $this->db->last_query();
                
                $a_id = $agency[0]->agency_id;
                $pr_id = $list_item['prop_id'];

                $hide_ck = 1;
				$row_color = 'green_mark';
                $reason .= "
                <br />
                <a href='/palace/property/$pr_id/$a_id'>
                    <span class='badge badge-primary'>Palace</span>
                </a>
                <br />
                <br />
                ";         
            }

             // Get agency
             $getAgency = "
             SELECT *
             FROM `agency_api_tokens`
             WHERE `agency_id` = {$list_item['a_id']} AND api_id = 3
             ";
             $agency = $this->db->query($getAgency);

            //  echo "<pre>";
            //  var_dump($list_item);
            //  die();
            
             // Check if property tree is connected to agency but not linked
            if( $list_item['ptree_prop_id'] == '' && $agency->num_rows() > 0){
                $prop_id = $list_item['prop_id'];
                $hide_ck = 1;
				$row_color = 'green_mark';
                $reason .= "
                <br />
                <a href='/property_tree/connection_details/$prop_id'>
                    <span class='badge badge-primary'>PropertyTree</span>
                </a>
                <br />
                <br />
                ";         
            }
            
            // empty ts_expiry on alarms 
            if( $current_tab == 'verify_details' && $list_item['al_count'] > 0 ){
                $hide_ck = 1;
				$row_color = 'green_mark';
                $reason .= "Tech Sheet expiry is missing<br />";   
            }

            // empty expiry on alarms 
            if( $current_tab == 'verify_details' && $list_item['empty_expiry_count'] > 0 ){
                $hide_ck = 1;
				$row_color = 'green_mark';
                $reason .= "Alarm missing expiry date<br />";   
            }

            // techsheet "Is this Property compliant with current State Legislation?" checkbox to NO
            if( is_numeric($list_item['prop_comp_with_state_leg']) && $list_item['prop_comp_with_state_leg'] == 0 ){

                $hide_ck = 1;
                $row_color = 'green_mark';
                $reason .= "Property marked not compliant<br />"; 

            }

            // // techsheet "Does this property meet QLD NEW Legislation?" checkbox to NO
            if( ( is_numeric($list_item['prop_upgraded_to_ic_sa']) && $list_item['prop_upgraded_to_ic_sa'] == 0 ) && $list_item['p_state'] == 'QLD' ){

                $hide_ck = 1;
                $row_color = 'green_mark';
                $reason .= "Property marked not 2022 compliant<br />"; 

            }

            // check for 0 priced new alarm
            $alarm_sql_str = "
            SELECT `alarm_id` AS al_count
            FROM `alarm`
            WHERE `job_id` = {$list_item['jid']}
            AND `new` = 1            
            AND `alarm_price` = 0
            ANd `ts_discarded` = 0
            ";
            $alarm_sql = $this->db->query($alarm_sql_str);

            if( $alarm_sql->row()->al_count > 0 ){

                // Short Term Rental
                if( $list_item['holiday_rental'] == 1 ){

                    $hide_ck = 1;
                    $row_color = 'green_mark';
                    $reason .= "Check alarm pricing, short term rentals should have paid alarms<br />"; 

                }  
                
                // Ic Upgrade
                if( $list_item['j_type']=='IC Upgrade' ){

                    $hide_ck = 1;
                    $row_color = 'green_mark';
                    $reason .= "Check alarm pricing, IC Upgrades should have paid alarms<br />"; 

                }

            }

            // check alarm
            $alarm_sql_str = "
            SELECT al.`alarm_id` AS al_count
            FROM `alarm` AS al
            LEFT JOIN `alarm_pwr` AS al_pwr ON al.`alarm_power_id` = al_pwr.`alarm_pwr_id`
            LEFT JOIN `jobs` AS j ON al.`job_id` = j.`id`
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            WHERE j.`id` = {$list_item['jid']}
            AND al_pwr.`alarm_make` = 'Brooks'
            AND al.`new` = 1
            ANd al.`ts_discarded` = 1
            AND al.`expiry` > '".date('Y')."'
            ";
            $alarm_sql = $this->db->query($alarm_sql_str);
            $al_count = $alarm_sql->row()->al_count;

            if( $al_count > 0 ){
                $hide_ck = 1;
                $row_color = 'green_mark';
                $reason .= "POSSIBLE WARRANTY ALARM $0<br />";                   
            }

            // Yellow marks takes the highest priority
			// MUST BE THE LAST - not completed due to = job reason			
			if( $list_item['job_reason_id']>0 ){
				
				// if 'No Keys EVER'(11) or 'keys dont work'(5) or 'no show'(1) hide checkbox, show red sms icon
				if( $list_item['job_reason_id']==11 || $list_item['job_reason_id']==5 || $list_item['job_reason_id']==1 ){

					$hide_ck = 1;
                    $reason_icon .= '<img src="/images/row_icons/red_sms.png" /> ';
                    
                }else if( $list_item['job_reason_id']==2  || $this->gherxlib->isDHAagencies($list_item['a_id'])==true ){ // 240v rebook(2) OR DHA agencies
                    
                    $hide_ck = 1;
                    
				}else if( $list_item['job_reason_id'] == 3 || $list_item['job_reason_id'] == 4  ){ // Fire Panel or Alarm System hide checkbox
                    
                    $hide_ck = 1;
                    
				}else if( $list_item['job_reason_id'] == 17 || $list_item['job_reason_id'] == 29  ){ // 'No Longer Managed by Agent' or 'Dangerous situation' 
                    $hide_ck = 1;                    
				}else{ // default checkbox state for this if block
					$hide_ck = 0;
				}
				
				
				// 'No Longer Managed by Agent'(17) or 'Property Vacant'(18)
				if( $list_item['job_reason_id']==17 || $list_item['job_reason_id']==18 ){

					$reason_icon .= '<img src="/images/red_phone.png" /> ';
                }	
                					
                $reason .= "{$reason_icon}{$list_item['jr_name']} <br />";
                $row_color = 'yello_mark';
				
			}

			// if not completed, key access and reason is not 'no keys at agency' (sir Dan says this is the highest priority)
			if( $list_item['key_access_required']==1 && $list_item['ts_completed']==0 && $list_item['job_reason_id']!=11 ){

				$hide_ck = 1;
				$row_color = 'yello_mark';
                //$reason .= "Verify keys have been returned before Rebooking<br />";
                
            }
            
            // DHA check
			if( $this->system_model->ifDHAAgencies($list_item['jid'])==true  && $list_item['ts_completed']==0 ){
				$hide_ck = 1;
				$row_color = 'yello_mark';
				$reason .= " DHA Property <br />";
            }

            // find 0 priced brooks 240v RF(10) and 3vLiRF(12) in non-QLD state
            if( $this->config->item('country') == 1 ){ // AU only
                
                $alarm_sql_str = "
                SELECT al.`alarm_id` AS al_count
                FROM `alarm` AS al
                LEFT JOIN `alarm_pwr` AS al_pwr ON al.`alarm_power_id` = al_pwr.`alarm_pwr_id`
                LEFT JOIN `jobs` AS j ON al.`job_id` = j.`id`
                LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
                WHERE j.`id` = {$list_item['jid']}
                AND al.`alarm_power_id` IN(10,12)
                AND al.`alarm_price` = 0
                AND p.`state` != 'QLD'
                AND al.new = 1
                ANd al.ts_discarded = 0
                ";
                $alarm_sql = $this->db->query($alarm_sql_str);
                $al_count = $alarm_sql->row()->al_count;

                if( $al_count > 0 ){
                    $hide_ck = 1;
                    $row_color = 'green_mark';
                    $reason .= "Used Brooks for free alarms<br />";                   
                }

            }  

            if( 
                ( $current_tab == 'unable_to_complete' && $row_color == 'yello_mark' && $list_item['is_sales'] != 1 ) || 
                ( $current_tab == 'verify_details' && $row_color == 'green_mark' && $list_item['is_sales'] != 1 ) ||
                ( $current_tab == 'good_to_go' && ( $row_color != 'yello_mark' && $row_color != 'green_mark') && $list_item['is_sales'] != 1 ) ||
                ( $current_tab == 'sales_upgrades' && $list_item['is_sales'] == 1  )                
            ){

            // get booked with name and mobile
            $bwt_arr = $this->system_model->findBookedWithTenantNumber($list_item['jid']);    
			

        ?>
        <tr class="body_tr <?php echo $row_color; ?>">
            <td>
            <?php echo $this->gherxlib->getAge($list_item['j_created']); ?>
            </td>
            <td>
            <?php echo ($this->system_model->isDateNotEmpty($list_item['j_date']))?$this->system_model->formatDate($list_item['j_date'],'d/m/Y'):'' ?>
            </td>
            <td>
            <?php echo $this->system_model->pcjGetLastYMCompletedDate($list_item['prop_id'],$list_item['j_service']) ?>
            </td>
            <td>
                <span class="job_type_update">                                    
                    <?php 
                    if( $allow_inline_job_type_update == true ){                         
                    ?>
                        <a class="btn_240v" href="javascript:void(0);">
                            <?php echo $this->gherxlib->getJobTypeAbbrv($list_item['j_type']); ?>
                        </a>
                    <?php
                    }else{
                        echo $this->gherxlib->getJobTypeAbbrv($list_item['j_type']); 
                    }									
                    ?>
                </span>	      
                <select class="form-control job_type_dp_hid">
                    <option value="">----</option>
                    <option value="Once-off" <?php echo ($list_item['j_type']=='Once-off')?'selected="selected"':''; ?>>Once-off</option>
                    <option value="Change of Tenancy" <?php echo ($list_item['j_type']=='Change of Tenancy')?'selected="selected"':''; ?>>Change of Tenancy</option>
                    <option value="Yearly Maintenance" <?php echo ($list_item['j_type']=='Yearly Maintenance')?'selected="selected"':''; ?>>Yearly Maintenance</option>
                    <option value="Fix or Replace" <?php echo ($list_item['j_type']=='Fix or Replace')?'selected="selected"':''; ?>>Fix or Replace</option>
                    <option value="240v Rebook" <?php echo ($list_item['j_type']=='240v Rebook')?'selected="selected"':''; ?>>240v Rebook</option>
                    <option value="Lease Renewal" <?php echo ($list_item['j_type']=='Lease Renewal')?'selected="selected"':''; ?>>Lease Renewal</option>
                </select>              
            </td>
            <td>
            <?php 
            //echo "$".number_format($this->system_model->getJobAmountGrandTotal($list_item['jid'],$this->config->item('country')),2) 
            echo '$'.number_format($this->system_model->price_ex_gst($this->system_model->getJobAmountGrandTotal($list_item['jid'],$this->config->item('country'))),2);
            ?>
            </td>
            <td>
            <img data-toggle="tooltip" title="<?php echo $list_item['ajt_type'] ?>" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($list_item['j_service']); ?>" />
            </td>
            <td>
            <!-- <a href="<?php echo base_url('/properties/view_property_details')."/".$list_item["prop_id"]?>"><?php echo $list_item['p_address_1']." ".$list_item['p_address_2']." ".$list_item['p_address_3']; ?></a> -->
            <?php 
                $prop_address = $list_item['p_address_1']." ".$list_item['p_address_2'].", ".$list_item['p_address_3'];
                echo $this->gherxlib->crmLink('vpd',$list_item['prop_id'],$prop_address);
            ?>
            </td>
            
            <td>
            <?php
            
                $tech_params = array('tech'=>$list_item['assigned_tech'], 'date'=> $list_item['j_date']);                
                $get_tech = $this->system_model->getTech_run($tech_params);                
                $tr = $get_tech->row_array();

                if( $tr['tech_run_id'] > 0 ){ ?>
                     <a href="/tech_run/run_sheet_admin/<?php echo $tr['tech_run_id']; ?>">
                        <?php echo $this->system_model->formatStaffName($list_item['FirstName'],$list_item['LastName']); ?>
                    </a>                
                <?php
                }else{
                    echo 'No tech run for this staff';
                }

            
            ?>               
            </td>

            <td><?php echo (($list_item['door_knock']==1)?'DK':''); ?></td>
            <td><?php echo $reason; ?></td>
            <td>
                <?php 
                if( $tech_notes_pres_flag==1 ){
                    echo stripslashes($list_item['tech_comments']);
                }if( $repair_notes_pres_flag==1 ){
                    echo stripslashes($list_item['repair_notes']);
                }else{
                    echo stripslashes($list_item['job_reason_comment']);
                }																
                ?>
            </td>
            <!-- <td><?php echo '<a href="'.base_url("/jobs/view_job_details/{$list_item['jid']}").'">'.$list_item['jid'].'</a>' ?></td> -->
            
            <td>
            <?php echo $this->gherxlib->crmLink('vjd',$list_item['jid'],$list_item['jid']);?>
            </td>
            <td class="action_td">
                <?php
                // if dha and not completed
                if( $this->gherxlib->isDHAagencies($list_item['a_id'])==true && $list_item['job_reason_id']>0 ){ ?>
                    <button type="button" class="submitbtnImg btn_dha_rebook btn">DHA Rebook</button>
                <?php
                }else{ 
                        // no show
                        $show_rebook = 0;		
                        if( $list_item['job_reason_id']==1 ){ 
                        
                            // SMS block
                            if( date('Y-m-d',strtotime($list_item['sms_sent_no_show'])) == date('Y-m-d') ){ // if sms already sent today
                                $disabled_txt = 'disabled="disabled"';
                                $add_class = 'jfadeIt';
                                $hide_ck = 0;
                            }else{
                                $disabled_txt = '';
                                $add_class = '';
                            }
                            $show_rebook = 1;
                        }
                        
                        // door knock and not completed
                        if( $list_item['door_knock']==1 && $list_item['job_reason_id']>0 ){
                            $show_rebook = 1;
                        }

                        if( $list_item['urgent_job'] == 1 || $list_item['job_priority'] == 1  ){

                            if( $row_color != '' ){ // only hide checkbox if green or yellow highlight, not white
                                $hide_ck = 1;
                            }
                            

                        }else{
                            
                            // if no show and has booked with tenant mobile
                            if( $list_item['job_reason_id']==1 && $bwt_arr['booked_with_tent_num'] != '' ){ ?>
                                <button type="button"  <?php echo $disabled_txt; ?> class="btn blue-btn submitbtnImg btn_no_show_sms btn-sm <?php echo $add_class; ?>">SMS</button><br />
                            <?php
                            }
                                                        
                                                                
                            // 240v rebook
                            if( $list_item['job_reason_id']==2 ){  
                            ?>
                                <button type="button" class="btn btn-danger submitbtnImg btn_no_show_240v_rebook btn-sm">240v Rebook</button><br />
                            <?php
                            }else{
                                
                                if( $show_rebook==1 ){ ?>
                                    <button type="button" class="btn btn-danger submitbtnImg btn_no_show_rebook btn-sm">Rebook</button><br />
                                <?php
                                }                                

                            }                            
                            
                        }	
                        
                        
                        
                }	
                
                if( $hide_ck != 1 ){ ?>
                    <div class="checkbox">
                        <input class="chk_job" name="chk_job[]" type="checkbox" id="check-<?php echo $list_item["jid"] ?>" data-jobid="<?php echo $list_item["jid"]; ?>" value="<?php echo $list_item["jid"] ?>">
                        <label for="check-<?php echo $list_item["jid"] ?>" class="chk_job_lbl">&nbsp;</label>
                    </div>
                <?php								
                } 															
                ?>
                <input type="hidden" class="hid_job_id" value="<?php echo $list_item['jid']; ?>" />
                <input type="hidden" class="hid_prop_id" value="<?php echo $list_item['prop_id']; ?>" />               
                <input type="hidden" class="booked_with_tent_num form-control" value="<?php echo $bwt_arr['booked_with_tent_num']; ?>" />
                <input type="hidden" class="booked_with_tent_fname form-control" value="<?php echo $bwt_arr['booked_with_tent_fname']; ?>" />
                <?php
                // private FG
                if( $this->system_model->getAgencyPrivateFranchiseGroups($list_item['franchise_groups_id']) == true ){ 
                    $landlord_txt = 'your landlord';
                }else{
                    $landlord_txt = 'your agency';
                }
                ?>                
            </td>            
            <td>
            <?php
            echo ($list_item['completed_timestamp']!="")?date("H:i",strtotime($list_item['completed_timestamp'])):'';
            ?>
            </td>
            <?php
            if( $current_tab == 'unable_to_complete' && $this->input->get_post('jobs_not_comp_res') > 0 ){ ?>
                <td class="text-center">
                    <div class="checkbox">
                        <input class="sms_or_email_chk_job" name="sms_or_email_chk_job[]" type="checkbox" id="sms_check-<?php echo $list_item["jid"] ?>" data-jobid="<?php echo $list_item["jid"]; ?>" value="<?php echo $list_item["jid"] ?>">
                        <label for="sms_check-<?php echo $list_item["jid"] ?>" class="sms_or_email_chk_job_lbl">&nbsp;</label>
                    </div>
                </td>
            <?php
            }
            ?> 
        </tr>
        <?php 

                    // only used for cron
                    if(  $current_tab == 'good_to_go' && ( $row_color != 'yello_mark' && $row_color != 'green_mark' ) && $move_to_merge == 1 ){

                        if( $list_item['jid'] > 0 ){

                            // update job to merged                        
                            $this->db->query("
                                UPDATE `jobs`
                                SET `status` = 'Merged Certificates'
                                WHERE `id` = {$list_item['jid']}
                            ");

                            // insert job logs            
                            $log_title = 27; // Merged Certificates
                            $job_log = "Job status updated from <strong>Pre Completion</strong> to <strong>Merged Certificates</strong>"; 
                            $staff_id = -3; // CRON

                            $log_params = array(
                                'title' => $log_title,
                                'details' => $job_log,
                                'display_in_vjd' => 1,
                                'created_by_staff' => $staff_id,
                                'auto_process' => 1,
                                'job_id' => $list_item['jid']
                            );
                            $this->system_model->insert_log($log_params); 

                            // insert job compliance table
                            if( $this->system_model->isDateNotEmpty($list_item['retest_date']) ){

                                $insert_data = array(
                                    'job_id' => $list_item['jid'],
                                    'retest_date' => $list_item['retest_date']
                                );                            
                                $this->db->insert('job_compliance', $insert_data);

                            }                            

                        }                        

                    }


                }
            }            
        }else{
            echo "<tr><td colspan='14'>No Data</td></tr>";
        }
        ?>
    </tbody>
</table>