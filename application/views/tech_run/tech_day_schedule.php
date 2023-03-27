<style>
	.col-mdd-3{
		max-width:12.5%;
		
	}
    .atoa .ato{
        padding-left: 10px;
        padding-right: 10px;
    }
    .ato_input{width:115px;}
    .ato_a{padding-right:10px;padding-top:10px;}
    .ato_a, .ato_input{
        float:left;
    }
    .ato_text{padding-top:10px;}
	.jobs-completed_block{
		padding-top:10px;
	}
	.jobs-completed_block span#jobs_count_span{
		color:#b4151b;
	}
	.jobs-completed_block span#jobs_completed_count_span{
		color:green;
	}
	.top_more_info_box{
		margin-bottom:15px;
	}
	.time_div_toggle{
		display:none;
	}
	.key_num_span{
		display: none;
	}
	.row_icons{
		width: 24px !important;
	}
	.pdf_icon{
		font-size: 24px;
		position: relative;
		top: 5px;
		left: 3px;
	}
	/* about text */
	.about_page_li li {
		padding-top: 15px;
	}
	.about_page_li {
		margin-bottom: 20px;
	}
	.about_page_li .row_icons{
		width: 20px;
	}
</style>
<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => "{$title}",
			'status' => 'active',
			'link' => "/tech_run/run_sheet_admin/{$this->uri->segment(3)}"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$bc_data['has_tech_version'] = 1;
	$bc_data['has_tech_version_url'] = "/tech_run/run_sheet/{$this->uri->segment(3)}";	
	$bc_data['staff_classID'] = $staff_classID;	
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">
			<div class="for-groupss row">
				<div class="col-md-4 columns atoa">
                        <div class="row">
                            <div class="ato ato_text left">
                            <?php echo $kms['number_plate'] ?>
                            </div>
                            <div class="ato left">
                                <div class="ato_a">KMS: </div> <input type="number" name="kms" id="kms" class="form-control ato_input" value="<?php echo $kms['kms'] ?>">
                            </div>

                            <div class="ato left">
								<input type="hidden" id="vehicles_id" value="<?php echo $v['vehicles_id']; ?>" />
								<button type="button" class="btn btn-kms">Submit Kms</button>
                            </div>

							<div class="left ato" style="padding-left:0;font-size:14px;color:#00D1E5;">
								<label>Updated</label>
								<span><?php echo date('d/m/Y', strtotime($kms['kms_updated'])) ?></span>
							</div>
                        </div>
				</div>


                <div class="col-md-2 columns atoa">
					
					<div class="left ato"><a class="btn" href="/stock/update_tech_stock/<?php echo $staff['StaffID'] ?>">Stocktake</a></div>
					<div class="left ato" style="padding-left:0;font-size:14px;color:#00D1E5;">

						<?php if($ts['date']!=""){ ?>
							<label>Updated</label>
							<span><?php echo date('d/m/Y', strtotime($ts['date'])) ?></span>
						<?php } ?>

					</div>

				</div>

                <div class="col-md-2 columns">
					<div class="left">

						<?php 
						//$trk_old_link = "{$this->config->item('crm_link')}/tech_run_keys.php?tech_id={$tech_id}&date={$this->system_model->formatDate($date)}&tr_id={$tr_id}";
						?>
						<!--
						<a class="btn" href="<?php echo $trk_old_link; ?>">KEYS</a>
						-->

						<a href="/tech_run/keys/?tr_id=<?php echo $tr_id ?>">
							<button type="button" class="btn">KEYS</button>
						</a>

					
						<?php
						if( ENVIRONMENT != 'production' ){ // DEV only ?>
							<a href="/reports/tech_tracking/?tech_id=<?php echo $tech_id; ?>&tr_id=<?php echo $tr_id ?>">
								<button type="button" class="btn">Location</button>
							</a>
						<?php	
						} 
						?>	
						
						<a href="/tech_run/available_dk_admin/?tr_id=<?php echo $tr_id; ?>">
							<button type="button" class="btn">DKs</button>		
						</a>
					
				
					</div>
				</div>

				<?php
				// display tech break
				if( $tb_sql->num_rows() > 0 ){
					$tb_row = $tb_sql->row();
					$lunch_break_ts = "<span class='text-green'>".date('H:i',strtotime($tb_row->tech_break_start))."</span>";
				}else{
					$lunch_break_ts = "<span class='text-red'>Not taken yet</span>";
				}
				?>
				 <div class="col-md-4 columns text-right">
						<div class="jobs-completed_block">							
							<span class="mr-3">Lunch Break: <?php echo $lunch_break_ts; ?></span>
							<span>Jobs Completed (<span id="jobs_completed_count_span"><?php $comp_count ?></span>/<span id="jobs_count_span"><?php echo $jr_count ?></span>)</span>
						</div>
				 </div>
			</div>
		</div>
	</header>

	<section>


		<div class="top_more_info_box">
						
				<div class="row">
						<div class="col-md-4 columns">
							<div class="tr_date_block" style="padding-top:5px;">
								<?php echo $this->system_model->formatDate($date,'d/m/Y') ?>
							</div>
						</div>
						<div class="col-md-8 columns text-right">
							
							<span style="background-color: pink; display: inline-block;margin-left: 5px; float: right; margin-right: 0px;padding:3px 10px;">ERROR on Tech sheet</span>
							<span style="background-color: #fffca3; display: inline-block;margin-left: 5px; float: right; margin-right: 5px;padding:3px 10px;">Unable to Complete</span>
							<span style="background-color: #c2ffa7; display: inline-block;margin-left: 50px; float: right; margin-right: 5px;padding:3px 10px;">Completed</span>
						
							<div style="float:right;padding-top:5px;">
								<?php
								$dev_str = (ENVIRONMENT!="production")?'_dev':'';
								$map_base_url = ($this->config->item('country')==1) ? 'http://smokealarmregistrar.com.au' : 'http://eyecapture.com.au';
								$map_url = "{$map_base_url}/tech_run{$dev_str}.php?api_key=sats123&tr_id={$tr_id}&country_id={$this->config->item('country')}"
					
								?>
	
								<span class="mr-2">
									<a href="/tech_run/run_sheet_admin_map/?tr_id=<?php echo $tr_id; ?>">
										Map: <span class="fa fa-map-marker" style="font-size:18px;"></span>
									</a>
								</span>

								<span>
									<a href="<?php echo $this->config->item('crm_link'); ?>/set_tech_run.php?tr_id=<?php echo $tr_id; ?>">
										STR: <span class="fa fa-puzzle-piece" style="font-size:18px;"></span>
									</a>
								</span>
								
							</div>

							<div style="float:right;padding-top:7px;padding-right:35px;">
									<div class="checkbox" style="margin:0;">
										<input name="sortbatch" type="checkbox" id="sortbatch">
										<label for="sortbatch">Sort by Batch</label>
									</div>
							</div>
						
						</div>
				</div>

		</div>


		<div class="body-typical-body">
			<div class="table-responsive">
				<table  id="tbl_maps"class="table table-hover main-table tds_tbl">
					<thead>
						<tr class="nodrop nodrag">
							<th>#</th>
							<th>Status</th>
							<th>Service</th>
							<th>Age</th>
							<th>Details</th>
							<th>Job Type</th>
							<th class="preferred_alarm_col_th">Alarm</th>
							<th>Ladder</th>
							<th>Address</th>
							<th>Key #</th>
                            <th>Notes</th>
                            <th>Time</th>
							<th>Agent</th>
							<th>Completed</th>
							<th>									
								<div class="checkbox" style="margin:0;">
									<input name="chk_all" type="checkbox" id="check-all">
									<label for="check-all">&nbsp;</label>
								</div>									
							</th>							
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
                            <td><?php echo $start_agency_address; ?></td>
                            <td>&nbsp;</td>
                            <td><?php echo $tech_mob1; ?></td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
							<td>&nbsp;</td>
                            <td>&nbsp;</td>                         
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

									if( $this->system_model->isDateNotEmpty( $row2['jdate'] ) ){

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
											$status_td = "
											<a href='{$this->config->item('crm_link')}/view_job_details.php?id={$row['jid']}'>{$jstatus_txt}</a>
											";

											// service
											$job_icons_params = array(
												'job_id' => $row['jid'],
												'job_type' =>$row2['job_type']
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
												<a href='{$this->config->item('crm_link')}/view_property_details.php?id={$row2['property_id']}'>
													{$paddress}
												</a>
											";

											// key
											$key_td = "<span class='key_num_span'>{$row2['key_number']}</span>";
									
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
											<a href='/agency/view_agency_details/{$row2['agency_id']}' class='agency_td'>
												".str_replace('*do not use*','',$row2['agency_name'])."<br />
												{$row2['phone']}
											</a>
											";

											// completed 
											$completed_td = (($row['completed_timestamp']!="")?date("H:i",strtotime($row['completed_timestamp'])):'');

											// checkbox
											$checkbox_td = "
											<div class='checkbox' style='margin:0;'>
												<input name='check_box[]' class='check_box' type='checkbox' id='check_{$row['tech_run_rows_id']}' value='{$row['jid']}'>
												<label for='check_{$row['tech_run_rows_id']}'>&nbsp;</label>
											</div>
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
												'agent_td' => $agent_td,
												'completed_td' => $completed_td,
												'checkbox_td' => $checkbox_td
											);
										
											// job row view here
											$this->load->view('tech_run/tech_day_schedule_row_list', $row_data_arr);
										
											$j++;

										}

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
        
                                        }else{ // default
        
                                            $key_address = "{$kr['address_1']} {$kr['address_2']}, {$kr['address_3']}";                                                            
                                        }

										$address_td = "{$key_address} ".(($kr['agency_id']==4102)?'(IMPORTANT - Read Agency Notes)':null);									

										// time
										$time_td = $kr['agency_hours'];
										
										// agency
										$agent_td = $this->gherxlib->crmlink('vad',$kr['agency_id'],str_replace('*do not use*','',$kr['agency_name']))."<br />{$kr['phone']}";
												
										// completed
										$completed_td = ($kr['completed_date']!="")?date("H:i",strtotime($kr['completed_date'])):null;

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
											'agent_td' => $agent_td,
											'completed_td' => $completed_td,
											'checkbox_td' => null
										);

										// job row view here
										$this->load->view('tech_run/tech_day_schedule_row_list', $row_data_arr);
								
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
											'agent_td' => $agent_td,
											'completed_td' => null,
											'checkbox_td' => null
										);

										// job row view here
										$this->load->view('tech_run/tech_day_schedule_row_list', $row_data_arr);
									
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
							<td><?php echo $end_agency_address; ?></td>
							<td>&nbsp;</td>
							<td><?php echo $tech_mob1; ?></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>							
							<td>&nbsp;</td>
                            <td>&nbsp;</td>
						</tr>


					</tbody>

				</table>
				
				<!--  THIS IS FOR COMPLETED COUNT AND JOB COUNT START --> 
				<input type="hidden" id="jobs_count" value="<?php echo $jobs_count; ?>" />
				<input type="hidden" id="comp_count" value="<?php echo $comp_count; ?>" />
				<!--  THIS IS FOR COMPLETED COUNT AND JOB COUNT END --> 

				<div id="mbm_box" class="text-right" style="display: none;">
							<div class="gbox_main">
								<!-- 
								<div class="gbox form-group">
									<input type="text" name="job_time" id="job_time" class="form-control" placeholder="Time">
								</div>
								
								<div class="gbox form-group">
									<button id="btn_set_time" type="button" class="btn">Set Time</button>
								</div>
								-->
								<div class="gbox form-group">
									<button id="btn_rebook" type="button" class="btn btn-danger">Rebook</button>
								</div>
							</div>
				</div>
			</div>

			

			<nav aria-label="Page navigation example" style="text-align:center">
				<?php echo $pagination; ?>
			</nav>

			<div class="pagi_count text-center">
				<?php echo $pagi_count; ?>
			</div>

		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<?php $this->load->view('tech_run/tech_day_schedule_tech_about_page'); ?>

</div>
<!-- Fancybox END -->


<script type="text/javascript" src="https://crmdev.sats.com.au/js/jquery.tablednd_0_5.js"></script>
<script type="text/javascript">


		jQuery(document).ready(function(){


			//LOAD FUNCTIONS ON LOAD START

			jobsCompletedCount();

			//LOAD FUNCTIONS ON LOAD END




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



			$('#check-all').on('change',function(){
				var obj = $(this);
				var isChecked = obj.is(':checked');
				var divbutton = $('#mbm_box');
				if(isChecked){
					divbutton.show();
					$('.check_box').prop('checked',true);
				}else{
					divbutton.hide();
					$('.check_box').prop('checked',false);
				}
			})

			$('.check_box').on('change',function(){
				var obj = $(this);
				var isLength = $('.check_box:checked').length;
				var divbutton = $('#mbm_box');
				if(isLength>0){
					divbutton.show();
				}else{
					divbutton.hide();
				}
			})


			$('#btn_set_time').click(function(e){
				e.preventDefault();
				
				var job_id = new Array();
				jQuery(".check_box:checked").each(function(){
					job_id.push(jQuery(this).val());
				});
				var job_time = jQuery("#job_time").val();
				var checkbox_legth = $('.check_box:checked').length;

				var err = "";

				if(checkbox_legth<=0){
					err += "Atleast 1 job must be selected \n";
				}

				if(job_time==""){
					err += "Time must not be empty";
				}

				if(err!=""){
					swal('',err,'error');
					return false;
				}

				$('#load-screen').show(); //show loader

				jQuery.ajax({
					type: "POST",
					url: "/tech_run/ajax_update_job_time",
					data: { 
						job_id: job_id,
						job_time: job_time
					}
				}).done(function( ret ) {
					$('#load-screen').hide(); //hide loader
					swal({
						title:"Success!",
						text: "Time update success",
						type: "success",
						showCancelButton: false,
						confirmButtonText: "OK",
						closeOnConfirm: false,
						showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
						timer: <?php echo $this->config->item('timer') ?>

					});
					setTimeout(function(){ window.location='/tech_run/run_sheet_admin/<?php echo $tr_id; ?>'; }, <?php echo $this->config->item('timer') ?>);	
				});	

			})



			$('#btn_rebook').click(function(e){
				e.preventDefault();
				
				var job_id = new Array();
				jQuery(".check_box:checked").each(function(){
					job_id.push(jQuery(this).val());
				});
				var checkbox_legth = $('.check_box:checked').length;

				var err = "";

				if(checkbox_legth<=0){
					err += "Atleast 1 job must be selected \n";
				}

				if(err!=""){
					swal('',err,'error');
					return false;
				}

				swal(
                    {
                        title: "",
                        text: "Are You Sure You Want to Continue?",
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

							jQuery.ajax({
							type: "POST",
							url: "<?php echo base_url('/tech_run/ajax_rebook_script') ?>",
							dataType: 'json',
							data: { 
								job_id: job_id,
								is_240v: 0
							}
							}).done(function(data){
							
								if(data.status){

									$('#load-screen').hide(); //hide loader
									swal({
										title:"Success!",
										text: "Rebook success",
										type: "success",
										showCancelButton: false,
										confirmButtonText: "OK",
										closeOnConfirm: false,
										showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
										timer: <?php echo $this->config->item('timer') ?>

									});
									setTimeout(function(){ window.location='/tech_run/run_sheet_admin/<?php echo $tr_id; ?>'; }, <?php echo $this->config->item('timer') ?>);	
									
								}

							});

                        }
                        
                    }
            	);

			})


			//KMS
			$('.btn-kms').click(function(){

				var kms = $('#kms').val();
				var vehicles_id = $('#vehicles_id').val();

				var err = "";
				
				if(kms==""){
					err += "KMS must not be empty \n";
				}

				if(err!=""){
					swal('',err,'error');
					return false;
				}

				  $('#load-screen').show();
				jQuery.ajax({
					type: "POST",
					url: "/tech_run/ajax_add_kms",
					dataType: 'json',
					data: { 
						kms: kms,
						vehicles_id: vehicles_id
					}
					}).done(function( ret ) {
						$('#load-screen').hide();	

						if(ret.status){

							swal({
								title:"Success!",
								text: "KMS Successfully Added",
								type: "success",
								showCancelButton: false,
								confirmButtonText: "OK",
								closeOnConfirm: false,
								showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
								timer: <?php echo $this->config->item('timer') ?>
							});	
							
							var full_url = window.location.href;
							setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);	

						}else{

                        	swal('','Server error please contact admin.','error');

                    	}	

					});	

				})




				//DRAG AND DROP TR
				jQuery("#tbl_maps").tableDnD({
					onDrop: function(table, row) {
						var job_id = jQuery.tableDnD.serialize({
							'serializeRegexp': null
						});

						var sortbatch = $('#sortbatch:checked').length;

						jQuery.ajax({
							method: "GET",
							url: "/tech_run/ajax_sort_tech_run?tr_id=<?php echo $tr_id; ?>&"+job_id
							}).done(function( ret ) {	
								if(sortbatch>0){
									return false;
								}else{
									window.location='/tech_run/run_sheet_admin/<?php echo $tr_id; ?>';	
								}
							console.log('sort success');
						});	
						
					}
				});

				//sortbatch event tweak
				$('#sortbatch').change(function(){
					var asdshimpox = $('#sortbatch:checked').length;

					if(asdshimpox>0){
						return false;
					}else{
						window.location='/tech_run/run_sheet_admin/<?php echo $tr_id; ?>';	
					}
				})


				jQuery(".img_call_before").click(function(){
					jQuery(this).parents("tr:first").find(".booked_with_tenant_div").toggle();
				});

				// key num toggle
				jQuery(".key_icon").click(function(){
					jQuery(this).hide();
					jQuery(this).parents("tr:first").find(".key_num_span").show();
				});
				

				<?php
					if( $hasTechRun == true ){ ?>
						getTechRunNewLists(1);
					<?php	
					}
				?>				
				

		}) //doc ready end





		// jobs completed count script
		function jobsCompletedCount(){
			var comp_count = jQuery("#comp_count").val();
			var jobs_count = jQuery("#jobs_count").val();
			
			jQuery("#jobs_completed_count_span").html(comp_count);
			jQuery("#jobs_count_span").html(jobs_count);
		}



		function getTechRunNewLists(gao){

			$('#load-screen').show(); 
			jQuery.ajax({
				type: "POST",
				url: "/tech_run/ajax_tech_run_get_new_list",
				data: { 
					tr_id: '<?php echo $tr_id; ?>',
					tech_id: '<?php echo $tech_id; ?>',
					date: '<?php echo $date; ?>',
					sub_regions: '<?php echo $sub_regions; ?>',
					get_assigned_only: gao
				}
			}).done(function( ret ){
				
				$('#load-screen').hide(); 
				//console.log('new jobs: '+ret);
				var msg = '';
				
				if(parseInt(ret)>0){
					swal(
						{
							title: "",
							text: "New Jobs Found!\nWe are refreshing the page",
							type: "warning",
							showCancelButton: false,
							confirmButtonText: "OK",
							closeOnConfirm: false,
							showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
							timer: <?php echo $this->config->item('timer') ?>
						}			
					);

					var full_url = window.location.href;
					setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);	

				}else{
					//msg = 'No New Jobs Found';
				}
				
			});

		}



</script>



