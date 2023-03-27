<style>
.row_icons{
	width: 24px !important;
}
.pdf_icon{
	font-size: 24px;
	position: relative;
	top: 5px;
	left: 3px;
}

<?php
if( $is_email == true ){ ?>

	.tds_tbl{
		text-align: left;	
		width:100%; 
		border: 1px solid #efefef;
		
	}

	.tds_tbl th{
		background-color: #404041; 
		color: #ffffff; 
		padding: 5px;
	}

	.tds_tbl td{
		padding: 5px;	
	}

<?php
}
?>	
</style>
<table id="tbl_maps" class="table main-table tds_tbl" style="<?php echo ( $is_email == true )?'text-align: left; width:100%; border: 1px solid #efefef':''; ?>">
					<thead>
						<tr>
							<th style="<?php echo ( $is_email == true )?'background-color: #404041; color: #ffffff; padding: 5px;':null; ?>">Time</th>							
							<th style="<?php echo ( $is_email == true )?'background-color: #404041; color: #ffffff; padding: 5px;':null; ?>">Service</th>
							<th style="<?php echo ( $is_email == true )?'background-color: #404041; color: #ffffff; padding: 5px;':null; ?>">Details</th>
							<th style="<?php echo ( $is_email == true )?'background-color: #404041; color: #ffffff; padding: 5px;':null; ?>" class="preferred_alarm_col_th">Alarm</th>						
							<th style="<?php echo ( $is_email == true )?'background-color: #404041; color: #ffffff; padding: 5px;':null; ?>">Ladder</th>
							<th style="<?php echo ( $is_email == true )?'background-color: #404041; color: #ffffff; padding: 5px;':null; ?>">Address</th>
							<th style="<?php echo ( $is_email == true )?'background-color: #404041; color: #ffffff; padding: 5px;':null; ?>">Key #</th>
                            <th style="<?php echo ( $is_email == true )?'background-color: #404041; color: #ffffff; padding: 5px;':null; ?>">Notes</th>                           
							<th style="<?php echo ( $is_email == true )?'background-color: #404041; color: #ffffff; padding: 5px;':null; ?>">Agent</th>
							<th style="<?php echo ( $is_email == true )?'background-color: #404041; color: #ffffff; padding: 5px;':null; ?>">Age</th>
							<?php
							if( $show_completed_col == true ){ ?>
								<th style="<?php echo ( $is_email == true )?'background-color: #404041; color: #ffffff; padding: 5px;':null; ?>">Completed</th>
							<?php	
							}
							?>							
							<th style="<?php echo ( $is_email == true )?'background-color: #404041; color: #ffffff; padding: 5px;':null; ?>">&nbsp;</th>
						</tr>
					</thead>

					<tbody>

						<?php
						if( $accom_name != '' ){ ?>
							<tr>                          
								<td style="<?php echo ( $is_email == true )?'padding: 5px;':null; ?>"><?php echo $accom_name; ?></td>
								<td style="<?php echo ( $is_email == true )?'padding: 5px;':null; ?>">&nbsp;</td>
								<td style="<?php echo ( $is_email == true )?'padding: 5px;':null; ?>">&nbsp;</td>
								<td style="<?php echo ( $is_email == true )?'padding: 5px;':null; ?>">&nbsp;</td>
								<td style="<?php echo ( $is_email == true )?'padding: 5px;':null; ?>">&nbsp;</td>
								<td style="<?php echo ( $is_email == true )?'padding: 5px;':null; ?>">
									<a href="http://maps.google.com/?q=<?php echo $start_agency_address; ?>">
										<?php echo $start_agency_address; ?>
									</a>
								</td>
								<td style="<?php echo ( $is_email == true )?'padding: 5px;':null; ?>">&nbsp;</td>
								<td style="<?php echo ( $is_email == true )?'padding: 5px;':null; ?>"><?php echo $tech_mob1; ?></td>
								<td style="<?php echo ( $is_email == true )?'padding: 5px;':null; ?>">&nbsp;</td>
								<td style="<?php echo ( $is_email == true )?'padding: 5px;':null; ?>">&nbsp;</td>
								<?php
								if( $show_completed_col == true ){ ?>
									<td style="<?php echo ( $is_email == true )?'padding: 5px;':null; ?>">&nbsp;</td>		
								<?php	
								}
								?>								
								<td style="<?php echo ( $is_email == true )?'padding: 5px;':null; ?>">&nbsp;</td>						
							</tr>
						<?php
						}
						?>                        

						<?php 
						$j = 2;
						$comp_count = 0;
						$jobs_count = 0;	
						
						if( isset($jr_list2) && $jr_list2->num_rows() > 0 ){
						
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
											$jdate = date("Y-m-d",strtotime($row2['jdate']));
											$jstatus = $row2['j_status'];

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
											$status_td = null;
											if( $is_email == true ){
												$status_td = $jstatus_txt;
											}else{
												// This will fix the job attempted to import twice into MYOB
												if ($row2['j_status'] == 'Merged Certificates' || $row2['j_status'] == 'Completed') {
													# code...
													if ($row2['j_status'] == 'Merged Certificates') {
														$status = 'Merged';
													} else {
														$status = $row2['j_status'];
													}
													$status_td .= "
													<a href='#' >
														<button type='button' class='btn btn btn-danger' disabled>{$status}</button>
													</a>											
													"; 	
												} else {
													// techsheet CI
													$status_td .= "
													<a href='/jobs/tech_sheet/?job_id={$row2['jid']}&tr_id={$tr_id}'>
														<button type='button' class='btn'>Tech Sheet</button>
													</a>											
													"; 	
												}						
											}
											

											// service
											$job_icons_params = array(
												'job_id' => $row2['jid'],
												'display_in_email' => true
											);
											$service_td = $this->system_model->display_job_icons_v2($job_icons_params);
											

											// details
											$details_td = null;

											// if first visit
											if( $this->tech_model->check_prop_first_visit($row2['property_id']) == true   ){
												$fv = '<img src="'.$this->config->item('crmci_link').'/images/first_icon.png" class="row_icons" title="First visit" data-toggle="tooltip" /> ';
											}else{
												$fv = '';
											}											
											$details_td .= $fv;

											// check if there are alarms expired
											if( $this->system_model->findExpiredAlarm($row2['jid']) == true   ){
												$fv = '<img src="'.$this->config->item('crmci_link').'/images/expired_alarm.png" class="row_icons" title="Expired Alarm" data-toggle="tooltip" /> ';
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
												$details_td .= '<img src="'.$this->config->item('crmci_link').'/images/caution.png" class="row_icons" title="Priority Job" data-toggle="tooltip"/> ';
											}

											if( $row2['key_access_required'] == 1 && $row2['j_status']=='Booked' ){
												$details_td .= '<img src="'.$this->config->item('crmci_link').'/images/key_icon.png" class="row_icons" title="Key Access Required" data-toggle="tooltip" /> ';
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
												$details_td .=  '<img src="'.$this->config->item('crmci_link').'/images/green_phone.png" class="row_icons" title="Phone Call" /> ';
											}	

											if( $row2['p_state'] == 'NSW' && $row2['service_garage'] == 1 ){
												$details_td .= '<img src="/images/serv_img/service_garage_icon.png" class="row_icons" data-toggle="tooltip" title="Service Garage" />';
											}
										
											
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
													<div class='left'><img src='{$this->config->item('crmci_link')}/images/ladder.png' class='ladder_icon row_icons' />({$survey_ladder})</div>													
												";
											}else{
												$survey_ladder_td = null;
											}


											// property address 
											$street_view = null;		
											$address_td = null;						
											$paddress =  $row2['p_address_1']." ".$row2['p_address_2'].", ".$row2['p_address_3'];

											if( $is_email == true ){
												$address_td .= $paddress;
											}else{
												
												
												if( $row2['p_lat'] != '' && $row2['p_lng'] != '' ){
													$street_view = "
													<a data-fancybox data-caption='{$paddress}' href='https://maps.googleapis.com/maps/api/streetview?size=600x400&location={$row2['p_lat']},{$row2['p_lng']}&fov=60&pitch=0&key={$this->config->item('gmap_api_key')}'>	
														<img src='/images/camera_red.png' />			
													</a>
													";
												}

												$address_td .= "{$street_view} <a href='http://maps.google.com/?q={$paddress}'>{$paddress}</a>";
												
											}
											
											// requires PPE
											if( $row2['requires_ppe'] == 1 ){ 
												$address_td .=  "<img src='/images/ppe_icon.png' class='ppe_icon' />";
											}  

											// key
											$key_td = "<span class='key_num_span'>".( ( $row2['key_number'] != '' )?$row2['key_number']:'No Key' )."</span>";
									
											if( $row2['key_access_required'] == 1 ){ 
												$key_td .= " <img class='key_icon row_icons' src='{$this->config->item('crmci_link')}/images/key_icon.png' /> ";
												// if job is entry notice, show pdf link
												if( $row2['job_entry_notice']==1 && $is_email == false ){ 								
													$en_link_orig = "{$this->config->item('crm_link')}/view_entry_notice_new.php?letterhead=1&i={$row2['jid']}&m=".md5($row2['agency_id'].$row2['jid']); // orig link
													$key_td .= "
														<a target='_blank' href='{$en_link_orig}'>
															<span class='fa fa-file-pdf-o pdf_icon'></span>
														</a>
													";
												}
												?>
											<?php
											}
										
											// notes
											$notes_td = $row2['tech_notes'];

											// time
											$time_td = "
											<div style='position: relative; bottom: 4px;'>
												{$row2['time_of_day']} ";												
												
												if($row2['p_comments']!=''){
													$time_td .= "<img class='time_img img_pnotes row_icons' src='{$this->config->item('crmci_link')}/images/notes.png' /> ";
													
												}					
																																		
												if( $row2['call_before'] == 1 && $row2['call_before_txt'] != '' ){ 
																											
													$time_td .= "<img class='time_img img_call_before row_icons' src='{$this->config->item('crmci_link')}/images/red_phone2.png' title='Phone Call' /> ";
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
											$agency_address = "{$row2['a_address_1']} {$row2['a_address_2']} {$row2['a_address_3']} {$row2['a_postcode']}";
											if( $is_email == true ){
												$agent_td = str_replace('*do not use*','',$row2['agency_name']);
											}else{                                               
												$agent_td = "
												<a href='javascript:void(0);' class='agency_name_link'>".str_replace('*do not use*','',$row2['agency_name'])."</a>						
												<input type='hidden' class='agency_address_txt' name='agency_address_txt' value='{$agency_address} \n{$row2['a_phone']}' />
												";
											}
											

											// age
											//$age_td =  $this->gherxlib->getAge($j_created);
											
											if($row2['j_status'] == "Completed"){
												$date1 = date_create(date('Y-m-d', strtotime($jdate)));
												$date2 = date_create(date('Y-m-d', strtotime($j_created)));
												$diff = date_diff($date1, $date2);
												$age = abs($diff->format("%r%a"));
												$age_val = (((int) $age) != 0) ? $age : 0;

												//$age_td =  "Created: ".$j_created. "<br />Completed: ".$jdate."<br /><br />Status:".$jstatus."<br /><br />Age Value: ".$age_val;
												$age_td = $age_val;
											}
											else{
												$age_td =  $this->gherxlib->getAge($j_created);
											}
											
											// completed timestamp											
											$completed_ts = ( $this->system_model->isDateNotEmpty($row2['completed_timestamp']) == true ) ? $this->system_model->formatDate($row2['completed_timestamp'], 'H:i') : null;
											

											// row data										
											$row_data_arr = array(
												'tr_id' => $row['tech_run_rows_id'],
												'tr_bg_color' => $bgcolor,
												'td_class' => 'jstatus',
												
												'status_td' => $status_td,
												'service_td' => $service_td,											
												'details_td' => $details_td,												
												'cavi_orca_td' => $cavi_orca_td,
												'survey_ladder_td' => $survey_ladder_td,
												'address_td' => $address_td,
												'key_td' => $key_td,
												'notes_td' => $notes_td,
												'time_td' => $time_td,
												'agent_td' => $agent_td,
												'age_td' => $age_td,
												
												'show_completed_col' => $show_completed_col,
												'completed_ts' => $completed_ts
											);
										
											// job row view here
											$this->load->view('tech_run/tech_day_schedule_tech_row_list', $row_data_arr);											
									
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

										// VISION REAL ESTATE scrip
										$vision_agency_arr = $this->system_model->get_vision_agencies();
										$vision_agency_main = $vision_agency_arr['vision_agency_main'];
										$vision_agency_sub =  $vision_agency_arr['vision_agency_sub'];
										//$vision_agency_sub_imp = implode(",",$vision_agency_sub);

										$nobk = $this->tech_model->getNumberOfBookedKeys($tech_id,$date,$this->config->item('country'),$kr['agency_id']);
										$is_key_already_picked_up = $this->tech_model->is_key_already_picked_up($tech_id,$date,$kr['agency_id']);

										$agency_name = str_replace('*do not use*','',$kr['agency_name']);

										if( $nobk > 0 || $is_key_already_picked_up == true || in_array($kr['agency_id'],$fn_agency_sub) || in_array($kr['agency_id'],$vision_agency_sub) ){ // only show agency keys, that has remaining booked keys
											
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

											if( $kr['action'] == 'Pick Up' ){
												$key_btn_class = "pick_up_btn";
											}else{
												$key_btn_class = "drop_off_btn";
											}
											$status_td = "
											<button type='button' class='btn agency_keys_btn {$key_btn_class}'
											data-trk_id='{$kr['tech_run_keys_id']}'
											data-tech_id='{$tech_id}'
											data-date='{$date}'
											data-agency_id='{$kr['agency_id']}'
											data-agency_name='{$agency_name}'
											data-completed='{$kr['completed']}'
											data-completed_date='{$kr['completed_date']}'
											data-agency_staff='{$kr['agency_staff']}'
											data-number_of_keys='{$kr['number_of_keys']}'
											data-signature_svg='{$kr['signature_svg']}'
											data-refused_sig='{$kr['refused_sig']}'
											>
												{$action}
											</button>
											";

											// key
											$details_td = "<img class='row_icons' src='{$this->config->item('crmci_link')}/images/key_icon.png' />";

											// address
											if( $kr['agen_add_id'] > 0 ){ // key address
												
												$key_address = "{$kr['agen_add_street_num']} {$kr['agen_add_street_name']}, {$kr['agen_add_suburb']}";
			
											}else{ // default
			
												$key_address = "{$kr['address_1']} {$kr['address_2']}, {$kr['address_3']}";
			
											}												
											$address_td = "<a href='http://maps.google.com/?q={$key_address}'>{$key_address}</a> ".(($kr['agency_id']==4102)?'(IMPORTANT - Read Agency Notes)':null);									

											// time
											$time_td = $kr['agency_hours'];
											
											// agent
											$agency_address = "{$kr['address_1']} {$kr['address_2']} {$kr['address_3']} {$kr['state']} {$kr['postcode']}";
											if( $is_email == true ){
												$agent_td = str_replace('*do not use*','',$agency_name);
											}else{
												$agent_td = "
												<a href='javascript:void(0);' class='agency_name_link'>".str_replace('*do not use*','',$agency_name)."</a>						
												<input type='hidden' class='agency_address_txt' name='agency_address_txt' value='{$agency_address} \n{$kr['a_phone']}' />
												";
											}
											
							

											// row data										
											$row_data_arr = array(
												'tr_id' => $row['tech_run_rows_id'],
												'tr_bg_color' => $bgcolor,
												'td_class' => 'jstatus',
																							
												'service_td' => null,											
												'details_td' => $details_td,												
												'cavi_orca_td' => null,
												'survey_ladder_td' => null,
												'address_td' => $address_td,
												'key_td' => null,
												'notes_td' => null,
												'time_td' => $time_td,
												'agent_td' => $agent_td,
												'age_td' => null,
												'status_td' => $status_td,

												'show_completed_col' => $show_completed_col,
												'completed_ts' => null
											);
										
											// job row view here
											$this->load->view('tech_run/tech_day_schedule_tech_row_list', $row_data_arr);	

											$j++;

										}
									

									// ROW IS SUPPLIER
									}else if( $row['row_id_type'] == 'supplier_id' ){


										// supplier
										$sup_sql = $this->tech_model->getTechRunSuppliers($row['row_id']);
										$sup = $sup_sql->row_array();

										if($sup['on_map']==1){

											// address
											$address_td = "<a href='http://maps.google.com/?q={$sup['sup_address']}'>{$sup['sup_address']}</a>";

											// agent
											if( $is_email == true ){
												$agent_td = $sup['company_name'];
											}else{
												$agent_td = "
												<a href='javascript:void(0);' class='agency_name_link'>
													{$sup['company_name']}						
												</a><br />
												{$sup['phone']}					
												<input type='hidden' class='agency_address_txt' name='agency_address_txt' value='{$sup['sup_address']} \n{$sup['phone']}' />
												";
											}
											

											// row data
											$row_data_arr = array(
												'tr_id' => $row['tech_run_rows_id'],
												'tr_bg_color' => '#eeeeee;',
												'td_class' => null,

												'status_td' => 'Supplier',
												'service_td' => null,											
												'details_td' => null,												
												'cavi_orca_td' => null,
												'survey_ladder_td' => null,
												'address_td' => $address_td,
												'key_td' => null,
												'notes_td' => null,
												'time_td' => null,
												'agent_td' => $agent_td,
												'age_td' => null,
												
												'show_completed_col' => $show_completed_col,
												'completed_ts' => null
											);

											// job row view here
											$this->load->view('tech_run/tech_day_schedule_tech_row_list', $row_data_arr);
										
											$j++;
																			
										}
										

									}
								
								?>

							<?php
							
							
							} 

						}else{ 
							echo "<tr><td colspan='100%'>No Data</td></tr>";	
						}
						?>


						<!-- END QUERY HERE... -->
						<?php
						if( $end_accom_name != '' ){ ?>
						<tr class="nodrop nodrag">

							<td style="<?php echo ( $is_email == true )?'padding: 5px;':null; ?>"><?php echo $end_accom_name; ?></td>
							<td style="<?php echo ( $is_email == true )?'padding: 5px;':null; ?>">&nbsp;</td>
							<td style="<?php echo ( $is_email == true )?'padding: 5px;':null; ?>">&nbsp;</td>
							<td style="<?php echo ( $is_email == true )?'padding: 5px;':null; ?>">&nbsp;</td>
							<td style="<?php echo ( $is_email == true )?'padding: 5px;':null; ?>">&nbsp;</td>
							<td style="<?php echo ( $is_email == true )?'padding: 5px;':null; ?>">
							<a href="http://maps.google.com/?q=<?php echo $end_agency_address; ?>">
								<?php echo $end_agency_address; ?>
							</a>
							</td>
							<td style="<?php echo ( $is_email == true )?'padding: 5px;':null; ?>">&nbsp;</td>
							<td style="<?php echo ( $is_email == true )?'padding: 5px;':null; ?>"><?php echo $tech_mob1; ?></td>
							<td style="<?php echo ( $is_email == true )?'padding: 5px;':null; ?>">&nbsp;</td>
							<td style="<?php echo ( $is_email == true )?'padding: 5px;':null; ?>">&nbsp;</td>
							<?php
							if( $show_completed_col == true ){ ?>
								<td style="<?php echo ( $is_email == true )?'padding: 5px;':null; ?>">&nbsp;</td>		
							<?php	
							}
							?>	
							<td style="<?php echo ( $is_email == true )?'padding: 5px;':null; ?>">&nbsp;</td>

						</tr>
						<?php
						}
						?>						

					</tbody>

				</table>
				<!--  THIS IS FOR COMPLETED COUNT AND JOB COUNT START --> 
				<input type="hidden" id="jobs_count" value="<?php echo $jobs_count; ?>" />
				<input type="hidden" id="comp_count" value="<?php echo $comp_count; ?>" />
				<!--  THIS IS FOR COMPLETED COUNT AND JOB COUNT END --> 