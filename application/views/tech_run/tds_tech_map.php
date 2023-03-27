<div class="box-typical box-typical-padding">

    <?php
// breadcrumbs template
$bc_items = array(
    array(
        'title' => "Run Sheet",
        'link' => "/tech_run/run_sheet/{$this->input->get_post('tr_id')}"
    ),
    array(
        'title' => $title,
        'status' => 'active',
        'link' => "{$uri}/?tr_id={$this->input->get_post('tr_id')}"
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);

?>
    <!--
	<header class="box-typical-header">
        <div class="box-typical box-typical-padding">
            <?php
            $form_attr = array(
                'id' => 'jform'
            );
            echo form_open($uri,$form_attr);
            ?>
                <div class="for-groupss row">
                    <div class="col-lg-10 col-md-12 columns">
                        <div class="row">


                            <div class="col-mdd-3">
                                    <label for="date_select">Date:</label>
                                    <input name="date_filter" placeholder="ALL" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo ( $this->input->get_post('date_filter')!= '' )?$this->input->get_post('date_filter'):null; ?>">
                            </div>

                            <div class="col-md-1 columns">
                                <label class="col-sm-12 form-control-label">&nbsp;</label>
                                <input class="btn" type="submit" name="btn_search" value="Search">
                            </div>

                        </div>
                    </div>
                </div>
            </form>
        </div>
    </header>
    -->


    <div id="map-canvas" style="width:100%;height:500px;border:1px solid #cccccc;"></div>

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<table id="tbl_maps" class="table table-hover table-striped main-table">
					<thead>
						<tr>
                            <th>#</th>
							<th>Status</th>
							<th>Service</th>
							<th>Age</th>
							<th>Details</th>
                            <th>Job Type</th>
                            <th>&nbsp;</th>
							<th>Ladder</th>
							<th>Address</th>
							<th>Key #</th>
                            <th>Notes</th>
                            <th>Booking Time</th>
                            <th>Agent</th>
						</tr>
					</thead>

					<tbody>

                        <tr class="nodrop nodrag">
                            <td>1</td>
                            <td><?php echo $accom_name; ?></td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td class="address"><?php echo $start_agency_address; ?></td>
                            <td>&nbsp;</td>
                            <td><?php echo $tech_mob1; ?></td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <?php
                            /*
                            $prop_address[$i]['address'] = $start_agency_address;
                            $prop_address[$i]['lat'] = $start_accom_lat;
                            $prop_address[$i]['lng'] = $start_accom_lng;
                            */

                            $prop_address[] = array(
                                'address' => $start_agency_address,
                                'lat' => $start_accom_lat,
                                'lng' => $start_accom_lng,
                                'is_accomodation' => 1,
                            );

                            $i++;
                            ?>
                        </tr>

						<?php
						$j = 2;
						$comp_count = 0;
						$jobs_count = 0;
						foreach($jr_list2->result_array() as $row){
						?>

							<?php

								// ROW IS JOBS
								if( $row['row_id_type'] == 'job_id' ){


                                        $jr_sql = $this->tech_model->getJobRowData($row['row_id'],$this->config->item('country'));
                                        $row2 = $jr_sql->row_array();

                                        $showRow = 1;


										if( $showRow ==1 ){


											$jobs_count++;

											//BG COLOUR
											$bgcolor = "#FFFFFF";
											if($row2['job_reason_id']>0){
												$bgcolor = "#fffca3";
											}else  if($row2['ts_completed']==1){
												$bgcolor = "#c2ffa7";
												$comp_count++;
											}

											$j_created = date("Y-m-d",strtotime($row2['created']));

											if( $row['dnd_sorted']==0 ){
												$bgcolor = '#FFFF00';
											}

											// color row pink if precomp jobs was moved to booked and is techsheet complete
											if( $row['precomp_jobs_moved_to_booked']==1 ){
												$bgcolor = 'pink';
											}

											// if job type is 'IC Upgrade' show IC upgrade icon
											$show_ic_icon = ( $row2['job_type'] == 'IC Upgrade' )?1:0;


											switch($row2['j_status']){
												case 'Merged Certificates':
													$jstatus_txt = 'Merged';
												break;
												case 'Pre Completion':
													$jstatus_txt = 'Pre Comp';
												break;
												default:
													$jstatus_txt = $row2['j_status'];
											}




											// job row data
											// job status
											$status_td = $jstatus_txt;

											// service
											$job_icons_params = array(
												'job_id' => $row2['jid']
											);
											$service_td = $this->system_model->display_job_icons_v2($job_icons_params);

											// age
											$age_td =  $this->gherxlib->getAge($j_created);

											// details
											$details_td = null;
											// if first visit
											if( $this->tech_model->check_prop_first_visit($row2['property_id']) == true   ){
												$fv = '<img src="/images/first_icon.png" class="row_icons" title="First visit" data-toggle="tooltip" /> ';
											}else{
												$fv = '';
											}
											$details_td .= $fv;


											//  if job type = COT, LR, FR, 240v or if marked Urgent
											if(
												$row2['job_type'] == "Change of Tenancy" ||
												$row2['job_type'] == "Lease Renewal" ||
												$row2['job_type'] == "Fix or Replace" ||
												$row2['job_type'] == "240v Rebook" ||
                                                $row2['is_eo'] == 1 ||
												$row2['urgent_job'] == 1
											){
												$details_td .= '<img src="/images/caution.png" class="row_icons" title="Priority Job" data-toggle="tooltip"/> ';
											}

											if( $row2['key_access_required'] == 1 && $row2['j_status']=='Booked' ){
												$details_td .= '<img src="/images/key_icon.png" class="row_icons" title="Key Access Required" data-toggle="tooltip" /> ';
											}


											$job_log_params = array(
												'sel_query' => "eventdate, eventtime",
												'job_id' => $row2['jid'],
												'eventdate' => date('Y-m-d'),
												'contact_type' => 'Phone Call'
											);
											$chk_logs_sql = $this->tech_model->getJobLogByJobId($job_log_params);
											$chk_log = $chk_logs_sql->row_array();

											$current_time = date("Y-m-d H:i:s");
											$job_log_time = date("Y-m-d H:i",strtotime("{$chk_log['eventdate']} {$chk_log['eventtime']}:00"));
											$last4hours = date("Y-m-d H:i",strtotime("-4 hours"));

											if(
												$row2['j_status']=='To Be Booked' && $chk_logs_sql->num_rows()>0 &&
												( $job_log_time >= $last4hours && $job_log_time <= $current_time )
											){
												$details_td .=  '<img src="/images/green_phone.png" class="row_icons" title="Phone Call" /> ';
											}

                                            if( $row2['p_state'] == 'NSW' && $row2['service_garage'] == 1 ){
												$details_td .= '<img src="/images/serv_img/service_garage_icon.png" class="row_icons" data-toggle="tooltip" title="Service Garage" />';
											}


											// job type
											$job_type_td =  $this->gherxlib->getJobTypeAbbrv($row2['job_type']);


											$cavi_orca_td = null;
                                            if( $this->config->item('country') == 1 ){ // preferred alarm, AU only 

                                                if( $row2['p_state'] == 'QLD' ){ // QLD only

                                                    if( $row['preferred_alarm_id'] > 0 ){

                                                        $num_qld_alarm_txt = ( $row['qld_new_leg_alarm_num'] > 0 )?" ({$row['qld_new_leg_alarm_num']})":null;
                                                        $cavi_orca_td = "{$row2['pref_alarm_make']}{$num_qld_alarm_txt}";

                                                    }

                                                }else{ // non-QLD

                                                    $use_short_name = true;
                                                    $cavi_orca_td = $this->system_model->display_free_emerald_or_paid_brooks($row2['agency_id'],$use_short_name);

                                                }
                                                

                                            }else if( $this->config->item('country') == 2 ){ // cavi/orca alarms, NZ only
                                                $cavi_orca_td = $this->system_model->display_orca_or_cavi_alarms($row2['agency_id']);
                                            }


											// ladder
											if( $row2['survey_ladder']!='' ){

												// 4ft was changed to 3ft. older data already 4ft so just change labels
												$survey_ladder = '';
												if($row2['survey_ladder']=='4FT'){
													$survey_ladder = '3FT';
												}else{
													$survey_ladder = $row2['survey_ladder'];
												}

												$survey_ladder_td = "
													<div class='left'><img src='/images/ladder.png' class='ladder_icon row_icons' />({$survey_ladder})</div>
												";
											}else{
												$survey_ladder_td = null;
											}


											// address
											$paddress =  $row2['p_address_1']." ".$row2['p_address_2'].", ".$row2['p_address_3'];
											$address_td = "
												<a href='http://maps.google.com/?q={$paddress}' target='_blank'>
													{$paddress}
												</a>
											";

											// key
											$key_td = "<span class='key_num_span'>".( ( $row2['key_number'] != '' )?$row2['key_number']:'No Key' )."</span>";

											if( $row2['key_access_required'] == 1 ){
												$key_td .= "<img class='key_icon row_icons' src='/images/key_icon.png' /> ";
												// if job is entry notice, show pdf link
												if( $row2['job_entry_notice']==1 ){
													$en_link_orig = "{$this->config->item('crm_link')}/view_entry_notice_new.php?letterhead=1&i={$row2['jid']}&m=".md5($row2['agency_id'].$row2['jid']); // orig link
													$key_td .= "
														<a target='_blank' href='{$en_link_orig}'>
															<span class='fa fa-file-pdf-o pdf_icon'></span>
														</a>
													";
												}
											}

											// notes
											$notes_td = $row2['tech_notes'];

											// booking time
											$time_td = "
											<div style='position: relative; bottom: 4px;'>
												{$row2['time_of_day']} ";

												if($row2['p_comments']!=''){
													$time_td .= "<img class='time_img img_pnotes row_icons' src='/images/notes.png' /> ";

												}

												if( $row2['call_before'] == 1 && $row2['call_before_txt'] != '' ){

													$time_td .= "<img class='time_img img_call_before row_icons' src='/images/red_phone2.png' title='Phone Call' /> ";
													$time_td .= "<span style='color:#b4151b;'>{$row2['call_before_txt']}</span>";
													$time_td .= "
													<div class='time_div_toggle booked_with_tenant_div'>";

														// tricky, need to get the booked with tenant phone
														$pt_params = array(
															'property_id' => $row2['property_id'],
															'active' => 1
														);
														$pt_sql = $this->gherxlib->getNewTenantsData($pt_params);

														$tenant_phone = '';
														foreach( $pt_sql as $pt_row  ){

															if( $pt_row->tenant_firstname == $row2['booked_with'] ){
																$tenant_phone = $pt_row->tenant_mobile;
															}

														}
														$time_td .= "{$row2['booked_with']} {$tenant_phone}";

													$time_td .= "
													</div>";

                                                }

                                                if($row2['p_comments']!=''){
													$time_td .= "<div class='time_div_toggle property_notes_div'>{$row2['p_comments']}</div>";
												}

											$time_td .= "
											</div>";


                                            // agent
                                            $agent_td = "
                                            <a href='javascript:void(0);' class='agency_name_link agency_td'>
                                                ".str_replace('*do not use*','',$row2['agency_name'])."<br />
                                                {$row2['phone']}
                                            </a>
                                            ";


											// row data
											$row_data_arr = array(
												'tr_id' => $row['tech_run_rows_id'],
												'tr_bg_color' => $bgcolor,

												'id_td' => $j,
												'status_td' => $status_td,
												'service_td' => $service_td,
												'age_td' => $age_td,
												'details_td' => $details_td,
												'job_type_td' => $job_type_td,
												'cavi_orca_td' => $cavi_orca_td,
												'survey_ladder_td' => $survey_ladder_td,
												'address_td' => $address_td,
												'key_td' => $key_td,
												'notes_td' => $notes_td,
												'time_td' => $time_td,
												'agent_td' => $agent_td
											);

											// job row view here
                                            $this->load->view('tech_run/tds_tech_map_row_list', $row_data_arr);

                                            /*
                                            // store it on property address array
                                            $prop_address[$i]['address'] = "{$row2['p_address_1']} {$row2['p_address_2']} {$row2['p_address_3']} {$row2['p_state']} {$row2['p_postcode']}, {$_SESSION['country_name']}";
                                            $prop_address[$i]['status'] = $row2['j_status'];
                                            $prop_address[$i]['created'] = date("Y-m-d",strtotime($row2['created']));
                                            $prop_address[$i]['urgent_job'] = $row2['urgent_job'];
                                            $prop_address[$i]['lat'] = $row2['p_lat'];
                                            $prop_address[$i]['lng'] = $row2['p_lng'];
                                            */
                                            $i++;

                                            $prop_address[] = array(
                                                'address' => "{$row2['p_address_1']} {$row2['p_address_2']} {$row2['p_address_3']} {$row2['p_state']} {$row2['p_postcode']}",
                                                'status' => $row2['j_status'],
                                                'created' => date("Y-m-d",strtotime($row2['created'])),
                                                'urgent_job' => $row2['urgent_job'],
                                                'highlight_color' => $row['highlight_color'],
                                                'lat' => $row2['p_lat'],
                                                'lng' => $row2['p_lng'],
                                                'trr_id' => $row['tech_run_rows_id']
                                            );

                                            $j++;


										}



								// ROW IS KEYS
								}else if( $row['row_id_type'] == 'keys_id' ){

									// KEYS
									$k_sql = $this->tech_model->getTechRunKeys($row['row_id']);
									$kr = $k_sql->row_array();

									// FIRST NATIONAL AGENCIES script
									$fn_agency_arr = $this->system_model->get_fn_agencies();
									$fn_agency_main = $fn_agency_arr['fn_agency_main'];
									$fn_agency_sub =  $fn_agency_arr['fn_agency_sub'];
                                    //$fn_agency_sub_imp = implode(",",$fn_agency_sub);

                                    // VISION REAL ESTATE script
									$vision_agency_arr = $this->system_model->get_vision_agencies();
									$vision_agency_main = $vision_agency_arr['vision_agency_main'];
									$vision_agency_sub =  $vision_agency_arr['vision_agency_sub'];
									//$vision_agency_sub_imp = implode(",",$vision_agency_sub);

								    $nobk = $this->tech_model->getNumberOfBookedKeys($tech_id,$date,$this->config->item('country'),$kr['agency_id']);

									if( $nobk > 0 || in_array($kr['agency_id'],$fn_agency_sub) || in_array($kr['agency_id'],$vision_agency_sub) ){ // only show agency keys, that has remaining booked keys

										// background color
										$bgcolor = ($kr['completed']==1)?'#c2ffa7':'#eeeeee';

										// pickup/drop off
										if($kr['completed']==1){
											$kr_act = explode(" ",$kr['action']);
											$temp2 = ($kr['action']=="Drop Off")?'p':'';
											$temp = "{$kr_act[0]}{$temp2}ed";
											$action = "{$temp} {$kr_act[1]}";
										}else{
											$action = $kr['action'];
										}
										$status_td = $action;

										// key
										$details_td = "<img src='/images/key_icon.png' class='row_icons'/>";

										// address                                    
                                        if( $kr['agen_add_id'] > 0 ){ // key address
                                            
                                            $key_address = "{$kr['agen_add_street_num']} {$kr['agen_add_street_name']}, {$kr['agen_add_suburb']}";
                                            $key_address_full = "{$kr['agen_add_street_num']} {$kr['agen_add_street_name']} {$kr['agen_add_suburb']} {$kr['agen_add_state']} {$kr['agen_add_postcode']}";
                                            $key_lat = $kr['agen_add_lat'];
                                            $key_lng = $kr['agen_add_lng'];
        
                                        }else{ // default
        
                                            $key_address = "{$kr['address_1']} {$kr['address_2']}, {$kr['address_3']}";
                                            $key_address_full = "{$kr['address_1']} {$kr['address_2']} {$kr['address_3']} {$kr['state']} {$kr['postcode']}";
                                            $key_lat = $kr['lat'];
                                            $key_lng = $kr['lng'];
                                        
                                        }
                                        
										$address_td = "{$key_address} ".(($kr['agency_id']==4102)?'(IMPORTANT - Read Agency Notes)':null);

										// time
										$time_td = $kr['agency_hours'];

										// agency
										$agent_td = $agent_td = "
                                        <a href='javascript:void(0);' class='agency_name_link'>".str_replace('*do not use*','',$kr['agency_name'])."</a>
                                        <input type='hidden' class='agency_address_txt' name='agency_address_txt' value='{$agency_address} \n{$kr['a_phone']}' />
                                        ";


										// row data
										$row_data_arr = array(
											'tr_id' => $row['tech_run_rows_id'],
											'tr_bg_color' => $bgcolor,

											'id_td' => $j,
											'status_td' => $status_td,
											'service_td' => null,
											'age_td' => null,
											'details_td' => $details_td,
											'job_type_td' => null,
											'cavi_orca_td' => null,
											'survey_ladder_td' => null,
											'address_td' => $address_td,
											'key_td' => null,
											'notes_td' => null,
											'time_td' => $time_td,
											'agent_td' => $agent_td
										);

										// job row view here
                                        $this->load->view('tech_run/tds_tech_map_row_list', $row_data_arr);

                                        /*
                                        // get gecode
                                        $prop_address[$i]['address'] = "{$kr['address_1']} {$kr['address_2']} {$kr['address_3']} {$kr['state']} {$kr['postcode']}, {$_SESSION['country_name']}";
                                        $prop_address[$i]['is_keys'] = 1;
                                        $prop_address[$i]['lat'] = $kr['lat'];
                                        $prop_address[$i]['lng'] = $kr['lng'];
                                        $i++;
                                        */

                                        $prop_address[] = array(
                                            'address' => $key_address_full,
                                            'is_keys' => 1,
                                            'lat' => $key_lat,
                                            'lng' => $key_lng,
                                            'trr_id' => $row['tech_run_rows_id']
                                        );

										$j++;

									}


								// ROW IS SUPPLIER
								}else if( $row['row_id_type'] == 'supplier_id' ){


									// supplier
									$sup_sql = $this->tech_model->getTechRunSuppliers($row['row_id']);
									$sup = $sup_sql->row_array();

									if($sup['on_map']==1){

										// address
										$address_td = $sup['sup_address'];

										// agent
										$agent_td = "
										<a href='javascript:void(0);' class='agency_name_link'>
											{$sup['company_name']}
										</a><br />
										{$sup['phone']}
										<input type='hidden' class='agency_address_txt' name='agency_address_txt' value='{$sup['sup_address']} \n{$sup['phone']}' />
										";

										// row data
										$row_data_arr = array(
											'tr_id' => $row['tech_run_rows_id'],
											'tr_bg_color' => '#eeeeee;',

											'id_td' => $j,
											'status_td' => 'Supplier',
											'service_td' => null,
											'age_td' => null,
											'details_td' => null,
											'job_type_td' => null,
											'cavi_orca_td' => null,
											'survey_ladder_td' => null,
											'address_td' => $address_td,
											'key_td' => null,
											'notes_td' => null,
											'time_td' => null,
											'agent_td' => $agent_td
										);

										// job row view here
                                        $this->load->view('tech_run/tds_tech_map_row_list', $row_data_arr);

                                        /*
                                        // get gecode
                                        $prop_address[$i]['address'] = $sup['sup_address'];
                                        $prop_address[$i]['is_keys'] = 1;
                                        $prop_address[$i]['lat'] = $sup['lat'];
                                        $prop_address[$i]['lng'] = $sup['lng'];
                                        */

                                        $i++;

                                        $prop_address[] = array(
                                            'address' => $sup['sup_address'],
                                            'is_supplier' => 1,
                                            'lat' => $sup['lat'],
                                            'lng' => $sup['lng'],
                                            'trr_id' => $row['tech_run_rows_id']
                                        );

										$j++;

									}


								}

							?>

						<?php


						}
						?>


						<!-- END QUERY HERE... -->
						<tr class="nodrop nodrag">
							<td><?php echo $j; ?></td>
							<td><?php echo $end_accom_name; ?></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td class="address"><?php echo $end_agency_address; ?></td>
							<td>&nbsp;</td>
							<td><?php echo $tech_mob1; ?></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
                            <?php
                            /*
                            $prop_address[$i]['address'] = $end_agency_address;
                            $prop_address[$i]['end_accom_lat'] = $start_accom_lat;
                            $prop_address[$i]['end_accom_lng'] = $start_accom_lng;
                            */

                            $prop_address[] = array(
                                'address' => "{$end_agency_address}",
                                'lat' => $end_accom_lat,
                                'lng' => $end_accom_lng,
                                'is_accomodation' => 1,
                            );

                            $i++;
                            ?>
						</tr>


					</tbody>

				</table>
            </div>


		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>Here is a list pin icons and its description: </p>
	<table class="table">
        <tr>
            <td><img src="/images/google_map/circle-pin-blue.png"></td>
            <td>- Accomodation (start or end)</td>
        </tr>
        <tr>
            <td><img src="/images/google_map/circle-key-blue.png"></td>
            <td>- Agency keys or supplier</td>
        </tr>
        <tr>
            <td><img src="/images/google_map/pin-green.png"></td>
            <td>- Job is "Completed"</td>
        </tr>
        <tr>
            <td><img src="/images/google_map/pin-orange.png"></td>
            <td>- Job is "To Be Booked"</td>
        </tr>
        <tr>
            <td><img src="/images/google_map/pin-purple.png"></td>
            <td>- Job is Urgent/Allocate/60+ Days old</td>
        </tr>
        <tr>
            <td><img src="/images/google_map/pin-red.png"></td>
            <td>- Job is "Booked"</td>
        </tr>
        <tr>
            <td><img src="/images/google_map/pin-black.png"></td>
            <td>- Jobs to be Hidden (Escalate/On Hold)</td>
        </tr>
    </table>

</div>



<style>
#btn_assign_color_div,
#start_end_main_div,
#keys_div,
.property_notes_div,
.key_num_span{
    display:  none;
}
.col-mdd-3{
    max-width:15.5%;
}
.jtable td, .jtable th {
    border-top: none;
    height: auto;
}
#map_functions{
    padding: 10px 0;
}
select.form-control {
    display: inline;
    width: auto;
}
img.img_pnotes,
img.key_icon {
    cursor: pointer;
}
</style>


<!-- Fancybox END -->

<?php
/*
echo "<pre>";
print_r($prop_address);
echo "</pre>";
*/
?>

<script>

    // display marker radius
    function display_marker_radius(position,shadow_color){

        if( shadow_color > 0 ){

            switch(parseInt(shadow_color)){
                // Blue
                case 1:
                    var radius_color = '#00AEEF';
                break;
                // Green
                case 2:
                    var radius_color = '#00ae4d';
                break;
                // Orange
                case 3:
                    var radius_color = '#f15a22';
                break;
                // Pink
                case 4:
                    var radius_color = '#9c163e';
                break;
                // Purple
                case 5:
                    var radius_color = '#9b30ff';
                break;
                // Yellow
                case 6:
                    var radius_color = '#FFFF00';
                break;
            }

            // Add the circle for this city to the map.
            var cityCircle = new google.maps.Circle({
                strokeColor: radius_color,
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: radius_color,
                fillOpacity: 0.20,
                map: map,
                center: position,
                radius: 500
            });

        }

    }

    // get marker icon
    function get_marker_icon(image){

        // custom icon
        var icon = {
            url: image,
            labelOrigin: new google.maps.Point(20,16)
        };

        return icon;
    }

    // add markers
    function add_marker(position,popupcontent,icon,trr_id,prop_index,hide_label=false,is_job){

        if( prop_index > 0 && hide_label == false ){

            var pin_number = prop_index+1;
            var label_txt = pin_number.toString(); // convert to string

            var label_options = {
                text: label_txt,
                fontWeight: "bold",
                color: 'black',
                fontSize: '12px'
            };

        }

        // add marker
        var beachMarker = new google.maps.Marker({
            position: position,
            map: map,
            icon: icon,
            label: label_options
        });


        marker_data = {
            marker:beachMarker,
            address:popupcontent,
            coordinates:position,
            trr_id:trr_id,
            orig_icon:icon,
            is_job: is_job
        }
        markersArray.push(marker_data);

        // pop up window
        jAddPopUpWindow(beachMarker,popupcontent);


    }


    // pop up window
    function jAddPopUpWindow(beachMarker,contentString){

        var infowindow = new google.maps.InfoWindow({
            content: contentString
        });

        google.maps.event.addListener(beachMarker, 'click', function() {
            infowindow.open(map,beachMarker);
        });

    }


    // distance
    function calculateDistances(start,destination,row) {

        var service = new google.maps.DistanceMatrixService();
        service.getDistanceMatrix(
        {
            origins: [start],
            destinations: [destination],
            travelMode: google.maps.TravelMode.DRIVING,
            unitSystem: google.maps.UnitSystem.METRIC,
            avoidHighways: false,
        avoidTolls: false
        }, function(response, status){
            distance_callback(response,status,row)
        });

    }

    function distance_callback(response, status,row) {

        var jtext = "";

        if (status != google.maps.DistanceMatrixStatus.OK) {

            alert('Error was: ' + status);

        }else{

            var origins = response.originAddresses;
            var destinations = response.destinationAddresses;

            for (var i = 0; i < origins.length; i++) {
                var results = response.rows[i].elements;

                for (var j = 0; j < results.length; j++) {


                    jtext = ' From: '+origins[i] + ' - To: ' + destinations[j]
                    + ' | Distance: ' + results[j].distance.text + ' | Duration: '
                    + results[j].duration.text + ' - Distance value : '+results[j].duration.value+'\n';
                    //console.log(jtext);

                    row.find(".time").html(results[j].duration.text);
                    row.find(".distance").html(results[j].distance.text);

                    tot_time += parseFloat(results[j].duration.text);
                    tot_dis += parseFloat(results[j].distance.text);
                    orig_dur += results[j].duration.value;

                    var totalSec = orig_dur;
                    var hours = parseInt( totalSec / 3600 ) % 24;
                    var minutes = parseInt( totalSec / 60 ) % 60;
                    var seconds = totalSec % 60;
                    var time_str = "";
                    if(hours==0){
                        time_str = minutes+" mins";
                    }else{
                        time_str = hours+" hours "+minutes+" mins";
                    }
                    jQuery("#tot_time").html(time_str);
                    //jQuery("#tot_time").html(tot_time+" mins");
                    jQuery("#tot_dis").html(tot_dis.toFixed(1)+" km");

                    address_index++;
                }
            }

        }

    }

    function deleteOverlays() {
        for (var i = 0; i < markersArray.length; i++) {
            markersArray[i].setMap(null);
        }
        markersArray = [];
    }

    /*
    function display_non_accomodation_marker(address_lat_lng,address_obj,prop_index){

        var jdate = new Date(address_obj['created']);
        var last_60_day = new Date('<?php echo date("Y-m-d",strtotime("-60 days")); ?>');

        var hide_marker_label = false;
        var is_job = false;

        if( parseInt(address_obj['is_keys']) == 1 || parseInt(address_obj['is_supplier']) == 1 ){ // keys and supplier

            image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/circle-key-blue.png';
            hide_marker_label = true;

        }else{ // jobs

            if( ( address_obj['status'] == 'To Be Booked' && ( parseInt(address_obj['urgent_job']) == 1 || jdate < last_60_day ) ) || address_obj['status'] == 'Allocate' ){
                image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/pin-purple.png';
            }else if(address_obj['status'] == 'To Be Booked'){
                image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/pin-orange.png';
            }else if(address_obj['status'] == 'Booked'){
                image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/pin-red.png';
            }else if( address_obj['status'] == 'On Hold' || address_obj['status'] == 'On Hold - COVID' || address_obj['status'] == 'Escalate' ){
                image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/pin-black.png';
            }else{
                image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/pin-green.png';
            }

            is_job = true;

        }

        var icon = get_marker_icon(image);

        // add markers
        add_marker(address_lat_lng,address_obj['address'],icon,address_obj['trr_id'],prop_index,hide_marker_label,is_job);

    }
    */

    function display_marker(address_lat_lng,address_obj,prop_index){

       var jdate = new Date(address_obj['created']);
       var last_60_day = new Date('<?php echo date("Y-m-d",strtotime("-60 days")); ?>');

       var hide_marker_label = false;
       var is_job = false;

       if( parseInt(address_obj['is_accomodation']) == 1 ){ // accomodation

           image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/circle-pin-blue.png';
           hide_marker_label = true;

       }else if( parseInt(address_obj['is_keys']) == 1 || parseInt(address_obj['is_supplier']) == 1 ){ // keys and supplier

           image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/circle-key-blue.png';
           hide_marker_label = true;

       }else{ // jobs

           if( ( address_obj['status'] == 'To Be Booked' && ( parseInt(address_obj['urgent_job']) == 1 || jdate < last_60_day ) ) || address_obj['status'] == 'Allocate' ){
               image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/pin-purple.png';
           }else if(address_obj['status'] == 'To Be Booked'){
               image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/pin-orange.png';
           }else if(address_obj['status'] == 'Booked'){
               image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/pin-red.png';
           }else if( address_obj['status'] == 'On Hold' || address_obj['status'] == 'On Hold - COVID' || address_obj['status'] == 'Escalate' ){
               image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/pin-black.png';
           }else{
               image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/pin-green.png';
           }

           is_job = true;

       }

       var icon = get_marker_icon(image);

       // add markers
       add_marker(address_lat_lng,address_obj['address'],icon,address_obj['trr_id'],prop_index,hide_marker_label,is_job);

   }

    var directionsService;

    function initGoogleAPI() {
        directionsService = new google.maps.DirectionsService();

        // generate map
        run_google_map();
    }


    // variables
    var markersArray = [];
    var map;
    var distances = "";
    var icon = '';
    var image;
    var jcount = 1;
    var address_index = 1;

    var tot_time = 0;
    var tot_dis = 0;
    var orig_dur = 0;

    <?php
    // convert PHP address array to js array
    $js_array = json_encode($prop_address);
    ?>
    var prop_address = <?php echo $js_array; ?>

    /*
    function run_google_map() {

        var center = new google.maps.LatLng(prop_address[0]['lat'], prop_address[0]['lng']);

        // instantiate map properties
        var mapOptions = {
            zoom: 13,  // zoom - 0 for maxed out out of earth
            center: center,
            gestureHandling: 'greedy'
        }

        // create the map
        map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);

        var i = 1;
        var max_prop = prop_address.length;
        var last_prop_index = (max_prop-1);
        var first_index = 0;
        var last_index = 9;

        var prop_index = 0;


        //  set interval
        timer = setInterval(function(){

            // if last index is reach stop interval calls
            if(parseInt(prop_index)==parseInt(last_prop_index)){

                // clear timer
                clearInterval(timer);

            }else{


                // distance
                var start_dis = new google.maps.LatLng(prop_address[prop_index]['lat'], prop_address[prop_index]['lng']);
                var end_dis = new google.maps.LatLng(prop_address[prop_index+1]['lat'], prop_address[prop_index+1]['lng']);
                //calculateDistances(start_dis,end_dis);


                var startObj = prop_address[prop_index];
                var start = new google.maps.LatLng(startObj['lat'], startObj['lng']);


                // home point, index = 0
                if(parseInt(prop_index)==0){

                    var image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/circle-pin-blue.png';
                    var icon = get_marker_icon(image);
                    add_marker(start,startObj['address'],icon,startObj['trr_id'],prop_index,true);

                }else{ // start

                    // display markers
                    display_non_accomodation_marker(start,startObj,prop_index);

                }




                // add shadow marker
                if( startObj['highlight_color']!=null ){
                    display_marker_radius(start,startObj['highlight_color'],'shadow marker');
                }

                ++prop_index;

                // way points
                var wp = [];

                // 2nd and second to the last addresses for waypoints
                var second = first_index+1;
                var second_to_the_last = last_index-1;

                // if only one address left on the next 10 batch, there's no way point. assign it to end point
                if((last_prop_index-prop_index)!=1){

                    var y = 1;
                    while( prop_index!=last_prop_index && y<=8 ){

                        // distance
                        var start_dis = new google.maps.LatLng(prop_address[prop_index]['lat'], prop_address[prop_index]['lng']);
                        var end_dis = new google.maps.LatLng(prop_address[prop_index+1]['lat'], prop_address[prop_index+1]['lng']);
                        //calculateDistances(start_dis,end_dis);


                        var wpObj = prop_address[prop_index];
                        var wp_loc = new google.maps.LatLng(wpObj['lat'], wpObj['lng']);
                        wp.push({
                            'location': wp_loc,
                            'stopover':true
                        });


                        // display marker
                        display_non_accomodation_marker(wp_loc,wpObj,prop_index);

                        // add markers
                        //add_marker(wp_loc,wpObj['address'],icon,wpObj['trr_id'],prop_index,hide_marker_label);

                        // add shadow marker
                        if( wpObj['highlight_color']!=null ){
                            display_marker_radius(wp_loc,wpObj['highlight_color'],'shadow marker');
                        }

                        ++prop_index;
                        ++y;

                    }

                }

                var endObj = prop_address[prop_index];
                var end = new google.maps.LatLng(endObj['lat'], endObj['lng']);

                // end point
                if(prop_index==last_prop_index){

                    var image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/circle-pin-blue.png';
                    var icon = get_marker_icon(image);

                    // add markers
                    add_marker(end,endObj['address'],icon,endObj['trr_id'],prop_index,true);

                    // add shadow marker
                    if( endObj['highlight_color']!=null ){
                        display_marker_radius(end,endObj['highlight_color'],'shadow marker');
                    }

                }


                // instantiate direction object
                var directionsDisplay = new google.maps.DirectionsRenderer({
                    'suppressMarkers': true
                });


                // direction options
                var request = {
                    origin: start,
                    destination: end,
                    waypoints: wp,
                    travelMode: google.maps.TravelMode.DRIVING,
                    unitSystem: google.maps.UnitSystem.METRIC
                };

                // invoke direction
                directionsService.route(request, function(response, status) {
                    if (status == google.maps.DirectionsStatus.OK) {
                        directionsDisplay.setDirections(response);
                        directionsDisplay.setMap(map);
                    }
                });

                i++;
                first_index=(first_index+9);
				last_index=(last_index+9);

            }

        }, 1000);



    }
    */

    var delayFactor = 0;
    function generate_waypoints(wp_arr){

        var wp = [];

        if( wp_arr.length >= 2 ){

            // split array to start, end and waypoints
            for( let i = 0; i < wp_arr.length; i++ ){

                if( i == 0 ){ // start
                    var start = wp_arr[i];
                }else if( i == (wp_arr.length-1) ){ // end
                    var end = wp_arr[i];
                }else{
                    wp.push({
                        'location': wp_arr[i],
                        'stopover':true
                    });
                }

            }


            console.log("Start: ");
            console.log(start);
            console.log("Way points: ");
            console.log(wp);
            console.log("End: ");
            console.log(end);


            // instantiate direction object
            var directionsDisplay = new google.maps.DirectionsRenderer({
                'suppressMarkers': true
            });


            // direction options
            var request = {
                origin: start,
                destination: end,
                waypoints: wp,
                travelMode: google.maps.TravelMode.DRIVING,
                unitSystem: google.maps.UnitSystem.METRIC
            };

            // invoke direction
            directionsService.route(request, function(response, status) {

                if (status == google.maps.DirectionsStatus.OK) {

                    directionsDisplay.setDirections(response);
                    directionsDisplay.setMap(map);

                }else if (status === google.maps.DirectionsStatus.OVER_QUERY_LIMIT) {

                    delayFactor++;
                    setTimeout(function () {
                        generate_waypoints(wp_arr);
                    }, delayFactor * 1000);

                }

            });

        }

    }

    function run_google_map() {

        var center = new google.maps.LatLng(prop_address[0]['lat'], prop_address[0]['lng']);

        // instantiate map properties
        var mapOptions = {
            zoom: 13,  // zoom - 0 for maxed out out of earth
            center: center,
            gestureHandling: 'greedy'
        }

        // create the map
        map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);

        // loop through address
        var wp_limit = 25; // way point batch limit
        var wp_arr = [];
        for (var prop_index = 0; prop_index < prop_address.length; prop_index++) {

            var address_obj = prop_address[prop_index];
            var address_lat_lng = new google.maps.LatLng(address_obj['lat'], address_obj['lng']);

            display_marker(address_lat_lng,address_obj,prop_index); // display marker/pins

            // add shadow radius
            if( address_obj['highlight_color'] != null ){
                display_marker_radius(address_lat_lng,address_obj['highlight_color']);
            }

            // WAYPOINTS
            wp_arr.push(address_lat_lng); // add to waypoints stack

            if( wp_arr.length == wp_limit ){ // process per waypoint batch limit

                if( wp_arr.length >= 2  ){ // at least 2 address for start and end

                    generate_waypoints(wp_arr); // generate way points

                    wp_arr = []; // clear way points
                    wp_arr.push(address_lat_lng); // store last waypoint as start on the next waypoint batch

                }

            }else if( prop_index == (prop_address.length-1) ){ // reach the last address

                if( wp_arr.length >= 2 ){ // at least 2 address for start and end

                    generate_waypoints(wp_arr); // generate way points
                    wp_arr = [];

                }

            }

        }


    }

    jQuery(document).ready(function(){
        // notes toggle
        jQuery(".img_pnotes").click(function(){

            jQuery(this).parents("tr:first").find(".property_notes_div").toggle();

        });

        // key num toggle
        jQuery(".key_icon").click(function(){
            jQuery(this).parents("tr:first").find(".key_num_span").toggle();
        });

        // display agency or supplier name
        jQuery(".agency_name_link").click(function(){

            var agency_address = jQuery(this).parents("td:first").find(".agency_address_txt").val();
            swal({
                title:"",
                text: agency_address,
                type: "info",
                showCancelButton: false,
                confirmButtonText: "OK",
                closeOnConfirm: true,
                showConfirmButton: true
            });

        });

    });
</script>