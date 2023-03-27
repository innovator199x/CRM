<style>
#bot_func_btn_div,
#bot_func_highlight_row,
#bot_func_hide,
#bot_func_assign_dk,
#bot_func_en,
#bot_func_escalate,
#bot_func_change_tech,
#bot_func_mark_tech_sick,
#bot_func_remove_keys,
#bot_func_remove_suppliers,
.select_job_type_class,
.select_agency_jobs_class,
#add_key_div,
#add_supplier_div,
#region_filter_div,
#region_filter_div .state_div_chk,
#region_filter_div .region_div_chk,
#region_filter_div .sub_region_div_chk,
.EN_show_elem,
.a_address{
	display: none;
}

/* region filters - start */
#region_filter_div{
	padding: 1px 10px 1px 6px;
	position: absolute;
	top: 60px;
	display: none;
	z-index: 10;
	min-width: 129px;
	width: -moz-max-content;
}
#region_filter_div .state_div_chk {
	margin: 4px 0;
}
#region_filter_div .region_div {
	margin: 13px 0 0 24px;
}
#region_filter_div .sub_region_div_chk {
	margin: 13px 0 0 26px;
}
#region_filter_div .rf_select{
	font-weight: bold;
}
/* region filters - end */

#other_function_section .btn,
#select_function_btn_div .btn{
	margin-bottom: 5px;
}
#function_map_section .fa-map-marker{
	font-size: 26px;
}
#select_table_section .chk_col{
	width: 35px;
}
#run_status_tbl button.btn{
	width: 100%;
}
#bot_func_btn_div{
	position: fixed;
	bottom: 17%;
	right: 7%;
}
.details_icon{
	font-size: 24px;
}
.span_circle {
  height: 25px;
  width: 25px;
  background-color: #bbb;
  border-radius: 50%;
  display: inline-block;
}
#notes_timestamp_div {
  color: #00D1E5;
}
#tech_run_functions option.show_for_jobs,
#tech_run_functions option.show_for_keys,
#tech_run_functions option.show_for_supplier{
	display: none;
}
.hiddenJobs{
	background-color: #add8e6 !important;
    border: 1px solid #006df0;
}
#minimize_panel,
#maximize_panel{
	cursor:pointer;
}
#maximize_panel{
	display: none;
}
#display_multiselect {
	width: 180px;
	padding: 15px 1px 1px 7px;
}
.booked_icon{
	width: 20px;
}
.redCross{
	font-size: 30px;
}
</style>
<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => $title,
			'status' => 'active',
			'link' => $uri."/?tr_id={$tr_id}"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>


	<section class="tabs-section">
		
		<div class="tabs-section-nav tabs-section-nav-icons">
			<div class="tbl">
				<ul class="nav j_remember_tab" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" id="setup_tab" href="#nav_setup" role="tab" data-toggle="tab">
							<span class="nav-link-in">
								<i class="fa fa-wrench text-red"></i>								
								Setup
							</span>
						</a>
					</li>

					<?php
					// show only if tech run exist
					if( $has_tech_run == true ){ ?>
						<li class="nav-item">
							<a class="nav-link" id="details_tab" href="#nav_details" role="tab" data-toggle="tab">
								<span class="nav-link-in">
									<i class="fa fa-info-circle text-orange"></i>								
									Details
								</span>
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="functions_tab" href="#nave_functions" role="tab" data-toggle="tab">
								<span class="nav-link-in">
									<i class="fa fa-gears text-green"></i>							
									Functions
								</span>
							</a>
						</li>
					<?php
					}
					?>					
				</ul>
			</div>
		</div><!--.tabs-section-nav-->

		
		<div class="tab-content">

			<!-- SETUP CONTENT -->
			<div role="tabpanel" class="tab-pane fade active show" id="nav_setup">			        				

				<form id="jform" action="/tech_run/create_or_update" method="POST">

					<div class="row mb-3">
						<div class="col-4">
							<b>Date</b>
							<input name="date" id="date" class="form-control flatpickr" data-allow-input="true" type="text" value="<?php echo ( $this->system_model->isDateNotEmpty($tech_run_row->date) )?date('d/m/Y',strtotime($tech_run_row->date)):null; ?>" />								
						</div>
						<div class="col-4">
							<b>Starting Point</b>
							<select name="start_point" id="start_point" class="form-control">
								<option value="">---</option>
								<?php									
								foreach( $acco_sql->result() as $acco_row ){ ?>
									<option value="<?php echo $acco_row->accomodation_id; ?>" <?php echo ( $acco_row->accomodation_id == $tech_run_row->start )?'selected':null; ?>>
										<?php echo $acco_row->name; ?>
									</option>
								<?php
								}
								?>
							</select>
						</div>
						<div class="col-4">
							<b>Display on Calendar</b>
							<input name="calendar" id="calendar" class="form-control calendar" type="text" value="<?php echo $cal_row->region; ?>" />								
							<input type="hidden" name="calendar_id" id="calendar_id" value="<?php echo $cal_row->calendar_id; ?>" />							
						</div>
					</div>

					<div class="row mb-3">
						<div class="col-4">
							<b>Technician</b>
							<select name="assigned_tech" id="assigned_tech" class="form-control">
								<option value="">---</option>
								<?php
								foreach( $tech_sql->result() as $tech_row ){ ?>

									<option value="<?php echo $tech_row->StaffID; ?>" <?php echo ( $tech_row->StaffID == $tech_run_row->assigned_tech )?'selected':null; ?>>
										<?php echo $this->system_model->formatStaffName($tech_row->FirstName,$tech_row->LastName).( ( $tech_row->is_electrician == 1 )?' [E]':null ); ?>
									</option>

								<?php
								}
								?>
							</select>
						</div>
						<div class="col-4">
							<b>Ending Point</b>
							<select name="end_point" id="end_point" class="form-control">
								<option value="">---</option>
								<?php									
								foreach( $acco_sql->result() as $acco_row ){ ?>
									<option value="<?php echo $acco_row->accomodation_id; ?>" <?php echo ( $acco_row->accomodation_id == $tech_run_row->end )?'selected':null; ?>>
										<?php echo $acco_row->name; ?>
									</option>
								<?php
								}
								?>
							</select>
						</div>
						<div class="col-4">
							<b>Regions</b>

							<div id="region_filter_parent_div">
								<input type="text" name="region_filter" id='region_filter' class="form-control region_filter" placeholder="Search for Sub Region" autocomplete="off" />

								<div id="sub_region_tag_div" class="mt-2">
									<?php
									if( $has_tech_run == true ){

										if( $tech_run_row->sub_regions != '' ){

											// get sub region
											$sub_regions_sql = $this->db->query("
											SELECT 
												`sub_region_id`,
												`subregion_name`
											FROM `sub_regions`
											WHERE `sub_region_id` IN({$tech_run_row->sub_regions})
											");									
											foreach( $sub_regions_sql->result() as $sub_region ){ ?>

												<button type="button" class="btn btn-rounded btn-inline btn-primary sub_region_tag">
													<?php echo $sub_region->subregion_name; ?> 
													<input type="hidden" name="sub_region_ms_tag[]" value="<?php echo $sub_region->sub_region_id; ?>">
												</button>

											<?php
											}

										}										

									}									
									?>									
								</div>
								
								<div id="region_filter_div" class="box-typical region_filter_div">
								
									<div class="region_dp_header">	
										<?php										
										foreach( $dist_state_obj as $distinct_state_row ){ ?>
											<div class="checkbox state_div_chk">

												<input type="checkbox" id="chk_state_<?php echo $distinct_state_row->region_state; ?>" name="state_ms[]" class="state_ms" value="<?php echo $distinct_state_row->region_state; ?>">
												<label for="chk_state_<?php echo $distinct_state_row->region_state; ?>" class="rf_state_lbl"><?php echo $distinct_state_row->region_state; ?> (<?php echo $distinct_state_row->jcount; ?>)</label>

												<div class="region_div">
													<?php
													foreach( $distinct_state_row->region_arr_obj as $region_row ){ ?>
														<div class="checkbox region_div_chk">
															<input type="checkbox" id="chk_region_<?php echo $region_row->regions_id ?>" name="region_ms[]" class="region_ms" value="<?php echo $region_row->regions_id; ?>">													
															<label for="chk_region_<?php echo $region_row->regions_id; ?>" class="rf_region_lbl"><?php echo $region_row->region_name; ?> (<?php echo $region_row->jcount; ?>)</label>

															<div class="sub_region_div">
																<?php
																foreach( $region_row->sub_region_arr_obj as $sub_region_row ){ ?>
																	<div class="checkbox sub_region_div_chk">
																		<input type="checkbox" id="chk_sub_region_<?php echo $sub_region_row->sub_region_id; ?>" name="sub_region_ms[]" class="sub_region_ms" value="<?php echo $sub_region_row->sub_region_id; ?>">
																		<label for="chk_sub_region_<?php echo $sub_region_row->sub_region_id; ?>" class="rf_sub_region_lbl sub_region_ms_lbl"><?php echo $sub_region_row->subregion_name; ?> (<?php echo $sub_region_row->jcount; ?>)</label>
																	</div>
																<?php
																}
																?>
															</div>
															
														</div>
													<?php
													}
													?>
												</div>

											</div>
										<?php
										}
										?>
									</div>					
									
								</div>								
							</div>

						</div>
					</div>

					<div class="row mb-3">
						<div class="col-4">
							<b>Booking Staff</b>
							<select name="booking_staff" id="booking_staff" class="form-control">
								<option value="">---</option>
								<?php
								foreach( $booking_staff_sql->result() as $booking_staff_row ){ ?>
									<option value="<?php echo $booking_staff_row->StaffID ?>" <?php echo ( $booking_staff_row->StaffID == $cal_row->booking_staff )?'selected="selected"':'' ?>>
										<?php echo $this->system_model->formatStaffName($booking_staff_row->FirstName,$booking_staff_row->LastName); ?>
									</option>
								<?php
								}
								?>
							</select>
						</div>
						<div class="col-4">
							<b>Accomodation</b>
							<select name="accomodation" id="accomodation" class="form-control">
								<option value="">None</option>
								<option value="0" <?php echo ( is_numeric($cal_row->accomodation) && $cal_row->accomodation == 0 )?'selected':null; ?>>Required</option>
								<option value="2" <?php echo ( $cal_row->accomodation == 2 )?'selected':null; ?>>Pending</option>
								<option value="1" <?php echo ( $cal_row->accomodation == 1 )?'selected':null; ?>>Booked</option>
							</select>

							<div id="sel_acco" class="mt-3" style="display:<?php echo ( $cal_row->accomodation == 1 || $cal_row->accomodation == 2 )?'block':'none'; ?>;">
								<select name="accomodation_id" id="accomodation_id" class="form-control">
									<option value="">---</option>
									<?php									
									foreach( $acco_sql->result() as $acco_row ){ ?>
										<option value="<?php echo $acco_row->accomodation_id; ?>" <?php echo ( $acco_row->accomodation_id == $cal_row->accomodation_id )?'selected':null; ?>><?php echo $acco_row->name; ?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>
						<?php
						if( $has_tech_run == true ){ ?>

							<div class="col-4">
								<b>Stops Required</b>
								<div class="row">
									<div class="col-6">

										<button type="button" class="btn btn-primary" id="add_key_btn">Add Key</button>
										
										<div class="mt-2" id="add_key_div">

											<select id="keys_agency" class="form-control mt-2">
												<option value="">---</option>	
												<?php
												foreach( $sel_agency_jobs_sql->result() as $sel_agency_jobs_row ){ 												
													if( $sel_agency_jobs_row->agency_id > 0 ){
														
														// COPIED FROM OLD STR
														// display key address for agency that has it
														$agency_add_sql = $this->db->query("
														SELECT 
															a.`agency_name`,
															a.`agency_id`,
															agen_add.`id` AS agen_add_id,
															agen_add.`address_1` AS agen_add_street_num, 
															agen_add.`address_2` AS agen_add_street_name, 
															agen_add.`address_3` AS agen_add_suburb, 
															agen_add.`state` AS agen_add_state, 
															agen_add.`postcode` AS agen_add_postcode			
														FROM `agency_addresses` AS agen_add
														LEFT JOIN `agency` AS a ON agen_add.`agency_id` = a.`agency_id`
														WHERE agen_add.`agency_id` = {$sel_agency_jobs_row->agency_id}
														AND agen_add.`type` = 2
														");
														$key_add_num = 1;

														$check_address_str= "SELECT `agency_addresses`.`id`, a.`agency_id`, a.`agency_name`, agency_addresses.`address_1` AS agen_add_street_num, agency_addresses.`address_2` AS agen_add_street_name, agency_addresses.`address_3` AS agen_add_suburb  FROM `agency_addresses` JOIN `property_keys` ON `agency_addresses`.`id`=`property_keys`.`agency_addresses_id` JOIN `agency` AS a ON agency_addresses.`agency_id` = a.`agency_id` JOIN `jobs` ON `property_keys`.`property_id` = `jobs`.`property_id` WHERE agency_addresses.`agency_id`={$sel_agency_jobs_row->agency_id} AND agency_addresses.`type`=2 AND jobs.`date`=CURDATE() AND jobs.`status`='Booked' GROUP BY agency_addresses.`id`";
														$check_address_sql = $this->db->query($check_address_str);														

														//Count Key Address
														$count_address_str= "SELECT `agency_addresses`.`id` FROM `agency_addresses` JOIN `property_keys` ON `agency_addresses`.`id`=`property_keys`.`agency_addresses_id` JOIN `agency` AS a ON agency_addresses.`agency_id` = a.`agency_id` JOIN `jobs` ON `property_keys`.`property_id` = `jobs`.`property_id` WHERE agency_addresses.`agency_id`={$sel_agency_jobs_row->agency_id} AND agency_addresses.`type`=2 AND jobs.`date`=CURDATE() AND jobs.`status`='Booked' GROUP BY agency_addresses.`id`";
														$count_address_sql = $this->db->query($count_address_str);
														$count_address = $count_address_sql->num_rows();
														$count_check_address = $check_address_sql->num_rows();

														if( $check_address_sql->num_rows() > 0 && $count_address == $count_check_address ){

															foreach( $check_address_sql->result() as $check_address_row ){
																$agen_add_comb = "{$check_address_row->agen_add_street_num} {$check_address_row->agen_add_street_name}, {$check_address_row->agen_add_suburb}"; ?>
																	<option value='$check_address_row->agency_id' data-agency_addresses_id='$check_address_row->id'>{$check_address_row->agency_name} Key #{$key_add_num} {$agen_add_comb}</option>
																<?php
																$key_add_num++;
															}

														}else{ ?>

															<option value="<?php echo $sel_agency_jobs_row->agency_id; ?>"><?php echo $sel_agency_jobs_row->agency_name; ?></option>

															<?php
															// First National added list
															if( $sel_agency_jobs_row->agency_id == $fn_agency_main ){

																$fn_agency_sub_sql_str = "
																	SELECT `agency_id`, `agency_name`
																	FROM `agency`
																	WHERE `agency_id` IN({$fn_agency_sub_imp})
																";
																$fn_agency_sub_sql = $this->db->query($fn_agency_sub_sql_str);
																foreach( $fn_agency_sub_sql->result() as $fn_agency_sub_row ){ ?>
																	<option value="<?php echo $fn_agency_sub_row->agency_id; ?>"><?php echo $fn_agency_sub_row->agency_name; ?></option>
																<?php
																}
															}

															// // Vision Real Estate added list
															if( $sel_agency_jobs_row->agency_id == $vision_agency_main ){

																$vision_agency_sub_sql_str = "
																	SELECT `agency_id`, `agency_name`
																	FROM `agency`
																	WHERE `agency_id` IN({$vision_agency_sub_imp})
																";
																$vision_agency_sub_sql = $this->db->query($vision_agency_sub_sql_str);
																foreach( $vision_agency_sub_sql->result() as $vision_agency_sub_row ){ ?>
																	<option value="<?php echo $vision_agency_sub_row->agency_id; ?>"><?php echo $vision_agency_sub_row->agency_name; ?></option>
																<?php
																}
															}

															if( $agency_add_sql->num_rows() > 0 ){

																$key_add_num = 1;
																foreach( $agency_add_sql->result() as $agency_add_row ){
																
																// get agency address from `agency_addresses` table
																$agency_add_str = "{$agency_add_row->agen_add_street_num} {$agency_add_row->agen_add_street_name}, {$agency_add_row->agen_add_suburb}"; 
																?>
																	<option value="<?php echo $sel_agency_jobs_row->agency_id; ?>"><?php echo "{$sel_agency_jobs_row->agency_name} Key #{$key_add_num} {$agency_add_str}"; ?></option>
																<?php
																}
	
															}

														}																												

													}
												}
												?>																									
											</select>	

											<button type="button" class="btn btn-primary mt-2" id="add_key_submit_btn">Submit</button>

										</div>

									</div>
									<div class="col-6">

										<button type="button" class="btn btn-primary" id="add_supplier_btn">Add Supplier</button>

										<div class="mt-2" id="add_supplier_div">
											<select name="supplier" id="supplier" class="form-control">
												<option value="">---</option>
												<?php													
												foreach( $supp_sql->result() as $supp_row ){														
												?>
													<option value="<?php echo $supp_row->suppliers_id;  ?>"><?php echo $supp_row->company_name;  ?></option>
												<?php														
												}
												?>
											</select>
											<button type="button" class="btn btn-primary mt-2" id="add_supplier_submit_btn">Submit</button>
										</div>		

									</div>
								</div>

								<div class="row mt-3">
									<div class="col-12">
										<button type="submit" class="btn btn-primary" id="update_tech_run">Update Tech Run</button>	
									</div>
								</div>
								
							</div>							

						<?php
						}else{ ?>

							<div class="col-4">
								<b>Save</b>
								<div class="row">
									<div class="col">

										<input type="hidden" id="tr_already_exist" value="0" />
										<button type="submit" class="btn btn-primary" id="create_tech_run">Create Tech Run</button>																		

									</div>
								</div>
							</div>

						<?php
						}
						?>
											
					</div>

					<div class="row mb-3">

						<?php
						if( $has_tech_run == true ){ ?>

							<div class="col-4">

								<b>Display</b>

								<div id="display_multiselect" class="box-typical">
									<?php
									// get selected tech run job types
									$hide_job_types_arr  = [];		
									foreach( $hide_job_types_sql->result() as $hide_job_types_row ){
										$hide_job_types_arr[] = $hide_job_types_row->job_type;
									}
												
									foreach( $distinct_job_type_sql->result() as $index => $job_type_row ){ 																											
										?>
										<div class="checkbox">
											<input 
												type="checkbox" 
												class="jt_display_filter" id="job_type_<?php echo $index; ?>" 
												value="<?php echo $job_type_row->job_type; ?>" 
												<?php echo ( !in_array($job_type_row->job_type, $hide_job_types_arr) )?'checked':null; ?> 
											/>
											<label for="job_type_<?php echo $index; ?>"><?php echo $job_type_row->job_type; ?></label>													
										</div>	
										<?php									
									}
									?>								
								</div>

							</div>

						<?php
						}
						?>						
						
					</div>

					<input type="hidden" name="tr_id" id="tr_id" value="<?php echo $tr_id; ?>" />

				</form>


			</div><!--.tab-pane-->
			
			<?php
			// show only if tech run exist
			if( $has_tech_run == true ){ ?>

				<!-- DETAILS CONTENT -->
				<div role="tabpanel" class="tab-pane fade" id="nav_details">
				
					<div class="row">
						<div class="col-6">

							<section class="card card-blue-fill">
								<header class="card-header">Colour a Run</header>
								<div class="card-block">
								
									<table class="table colour_tbl">
										<tr>
											<th>Colour</th>
											<th>Time</th>
											<th>Jobs</th>
											<th>NO Keys</th>
											<th>Status</th>
										</tr>
										<?php
										foreach( $trr_color_sql->result() as $trr_color_row ){

										
										// get saved colour table
										$sql_colour_sql = $this->db->query("
										SELECT 
											`time`,
											`jobs_num`,
											`no_keys`,
											`booking_status`
										FROM `colour_table`
										WHERE `tech_run_id` = {$tr_id}
										AND `colour_id` = {$trr_color_row->tech_run_row_color_id}
										");
										
										$sql_colour_row = $sql_colour_sql->row();
										$ct_time = $sql_colour_row->time;
										$ct_jobs = $sql_colour_row->jobs_num;
										$ct_no_keys_chk = $sql_colour_row->no_keys;
										$ct_booking_status = $sql_colour_row->booking_status;										
										$isFullyBooked = 0;

										$status_dif_txt = '';
										if($ct_booking_status!=''){

											if($ct_booking_status=='FULL'){
												$status_dif_txt = "<span class='ct_full'>(FULL)</span>";
												$isFullyBooked = 1;
											}else{
												$status_dif_txt = $ct_booking_status;
											}

										}
										?>
											<tr id="ct_row_id_<?php echo $trr_color_row->tech_run_row_color_id; ?>" class="ct_row">
												<td style="background-color:<?php echo $trr_color_row->hex; ?>">
													<input type="hidden" class="ct_trrc_id" value="<?php echo $trr_color_row->tech_run_row_color_id; ?>" />
													<input type="hidden" class="ct_booked_job" value="0" />
													<input type="hidden" class="ct_fully_booked" value="<?php echo $isFullyBooked; ?>" />
												</td>
												<td><input type="text" class="form-control ct_time" value="<?php echo $ct_time; ?>" /></td>
												<td><input type="text" class="form-control ct_jobs" value="<?php echo $ct_jobs; ?>" /></td>
												<td>											
													<span class="checkbox">
														<input type="checkbox" id="ct_no_keys_chk_<?php echo $trr_color_row->tech_run_row_color_id; ?>" class="ct_no_keys_chk" <?php echo ($ct_no_keys_chk==1)?'checked="checked"':''; ?>>
														<label for="ct_no_keys_chk_<?php echo $trr_color_row->tech_run_row_color_id; ?>" class="chk_lbl"></label>
													</span>													
													<span class="fa fa-close text-danger redCross" style="<?php echo ($ct_no_keys_chk==1)?'display:inline;':'display:none;'; ?>"></span>
												</td>
												<td class="ct_status"><?php echo $status_dif_txt; ?></td>
											</tr>
										<?php
										}
										?>
									</table>

								</div>
							</section>	
							
							<section class="card card-blue-fill">
								<header class="card-header">Working Hours</header>
								<div class="card-block">
									<input type="text" class="form-control" id="working_hours" name="working_hours" value="<?php echo $tech_run_row->working_hours; ?>" />
								</div>
							</section>

							<section class="card card-blue-fill">
								<header class="card-header">Run Numbers</header>
								<div class="card-block">
									<table class="table colour_tbl">
										<tr>
											<th>Booked</th>
											<th>Door Knocks</th>
											<th>Billables</th>		
										</tr>
										<tr>
											<td><?php echo $tot_jobs_count; ?></td>
											<td><?php echo $tot_dk_count; ?></td>
											<td><?php echo $tot_bill_count; ?></td>
										</tr>
									</table>
								</div>
							</section>							
							
						</div>

						<div class="col-6">

							<section class="card card-blue-fill" id="other_function_section">
								<header class="card-header">Run Status</header>
								<div class="card-block">
		
									<table class="table table-borderless" id="run_status_tbl">
										<tr>
											<td><button type="button" data-tech_run-field="run_set" class="btn run_status <?php echo ( ( $tech_run_row->run_set == 1 )?'btn-success':'btn-primary' ); ?>">Run Set</button></td>
											<td><button type="button" data-tech_run-field="run_coloured" class="btn run_status <?php echo ( ( $tech_run_row->run_coloured == 1 )?'btn-success':'btn-primary' ); ?>">Run Coloured</button></td>
											<td><button type="button" data-tech_run-field="ready_to_book" class="btn run_status <?php echo ( ( $tech_run_row->ready_to_book == 1 )?'btn-success':'btn-primary' ); ?>">Ready to Book</button></td>
										</tr>
										<tr>
											<td><button type="button" data-tech_run-field="first_call_over_done" class="btn run_status <?php echo ( ( $tech_run_row->first_call_over_done == 1 )?'btn-success':'btn-primary' ); ?>">1st Call Over Done</button></td>
											<td><button type="button" data-tech_run-field="run_reviewed" class="btn run_status <?php echo ( ( $tech_run_row->run_reviewed == 1 )?'btn-success':'btn-primary' ); ?>">Run Reviewed</button></td>
											<td><button type="button" data-tech_run-field="finished_booking" class="btn run_status <?php echo ( ( $tech_run_row->finished_booking == 1 )?'btn-success':'btn-primary' ); ?>">2nd Call Over Done</button></td>
										</tr>
										<tr>
											<td><button type="button" data-tech_run-field="additional_call_over" class="btn run_status <?php echo ( ( $tech_run_row->additional_call_over == 1 )?'btn-success':'btn-primary' ); ?>">Extra Call Over</button></td>
											<td><button type="button" data-tech_run-field="additional_call_over_done" class="btn run_status <?php echo ( ( $tech_run_row->additional_call_over_done == 1 )?'btn-success':'btn-primary' ); ?>">Extra Call Over Done</button></td>
											<td><button type="button" data-tech_run-field="ready_to_map" class="btn run_status <?php echo ( ( $tech_run_row->ready_to_map == 1 )?'btn-success':'btn-primary' ); ?>">Run Ready to Map</button></td>
										</tr>
										<tr>
											<td><button type="button" data-tech_run-field="run_complete" class="btn run_status <?php echo ( ( $tech_run_row->run_complete == 1 )?'btn-success':'btn-primary' ); ?>">Run Mapped</button></td>
											<td><button type="button" data-tech_run-field="morning_call_over" class="btn run_status <?php echo ( ( $tech_run_row->morning_call_over == 1 )?'btn-success':'btn-primary' ); ?>">Morning Call Over</button></td>
											<td><button type="button" data-tech_run-field="no_more_jobs" class="btn run_status <?php echo ( ( $tech_run_row->no_more_jobs == 1 )?'btn-success':'btn-primary' ); ?>">FULL - No More Jobs</button></td>
										</tr>
									</table>
										
								</div>
							</section>

							<section class="card card-blue-fill">
								<header class="card-header">Booking Notes</header>
								<div class="card-block">

									<div class="row mb-2">
										<div class="col">
											<a target="_blank" id="agencyNotesLink" href="<?php echo $this->config->item("crm_link"); ?>/agency_booking_notes.php">
												<span class="text-danger">(IMPORTANT - Read Agency Notes)</span>
											</a>
										</div>
										<div class="col font-italic" id="notes_timestamp_div">
											<span id="updates_by"><?php echo $notes_updated_by; ?></span>
											<span id="updated_ts"><?php echo $notes_ts; ?></span>
										</div>
									</div>

									<textarea class="form-control addtextarea" name="notes" id="notes"><?php echo $tech_run_row->notes; ?></textarea>

								</div>
							</section>																																			

							<section class="card card-blue-fill">
								<header class="card-header">Sort</header>
								<div class="card-block">								

									<select name="sort" id="sort" class="form-control">
										<option value="">None</option>
										<option value="1">Colour</option>
										<option value="2">Street</option>
										<option value="3">Suburb</option>
									</select>

								</div>
							</section>

							<section class="card card-blue-fill" id="function_map_section">
								<header class="card-header">Map and Runsheet</header>
								<div class="card-block">
									<table class="table colour_tbl">
										<tr>
											<th>Map:</th>
											<td>
												<a target="_blank" href="/tech_run/map/?tr_id=<?php echo $tr_id; ?>">
													<span class="fa fa-map-marker text-primary"></span>
												</a>												
											</td>											
											<th>Runsheet: </th>	
											<td>
												<a target="_blank" href="/tech_run/run_sheet_admin/<?php echo $tr_id; ?>">
													<span class="fa fa-map-marker text-primary"></span>
												</a>
											</td>	
										</tr>						
									</table>
								</div>
							</section>
							
						</div>

					</div>
				
				</div><!--.tab-pane-->

				<!-- FUNCTION CONTENT -->
				<div role="tabpanel" class="tab-pane fade" id="nave_functions">
				
					<div class="row">

						<div class="col-6">

							<section class="card card-blue-fill">
								<header class="card-header">Booking Regions</header>
								<div class="card-block">
									<table class="table colour_tbl">
										<tr>
											<th>Regions you are booking:</th>
											<th></th>
											<th>Alternate Days: </th>		
										</tr>
										<?php

										if( $tech_run_row->sub_regions != '' ){

											// get sub region
											$sub_region_sql = $this->db->query("
											SELECT 
												r.`region_name`,

												sr.`sub_region_id`,
												sr.`subregion_name`																			
											FROM `sub_regions` AS sr
											LEFT JOIN `regions` AS r ON sr.`region_id` = r.`regions_id`
											WHERE sr.`sub_region_id` > 0
											AND sr.`sub_region_id` IN({$tech_run_row->sub_regions}) 
											AND sr.`active` = 1
											");

											if( $sub_region_sql->num_rows() > 0 ){
												?>

												<?php
												foreach( $sub_region_sql->result() as $sub_region_row ){

													// get all postcode that belong to a sub region
													$postcodes_imp = null;

													$postcode_sql = $this->db->query("
													SELECT pc.`postcode`
													FROM `postcode` AS pc
													LEFT JOIN `sub_regions` AS sr ON pc.`sub_region_id` = sr.`sub_region_id`
													LEFT JOIN `regions` AS r ON sr.`region_id` = r.`regions_id`
													WHERE pc.`id` > 0
													AND pc.`sub_region_id` = {$sub_region_row->sub_region_id}
													");

													$postcodes_arr = [];
													foreach ( $postcode_sql->result() as $postcode_row ) {
														$postcodes_arr[] = $postcode_row->postcode;
													}

													if( count($postcodes_arr) > 0 ){
														$postcodes_imp = implode(",", $postcodes_arr);
													}

													// todo: called in a function
													// get tech run row count
													$tech_run_rows_sql = $this->db->query("
													SELECT COUNT(trr.`tech_run_rows_id`) AS trr_count														
													FROM `tech_run_rows` AS trr
													LEFT JOIN `tech_run` AS tr ON trr.`tech_run_id` =  tr.`tech_run_id`
													LEFT JOIN `jobs` AS j ON ( trr.`row_id` = j.`id` AND trr.`row_id_type` = 'job_id' )  
													LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
													LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
													LEFT JOIN `tech_run_row_color` AS trr_hc ON trr.`highlight_color` = trr_hc.`tech_run_row_color_id`
													WHERE tr.`tech_run_id` = {$tr_id}
													AND tr.`country_id` = {$this->config->item('country')}
													AND j.`del_job` = 0
													AND p.`deleted` = 0
													AND a.`status` = 'active'
													AND a.`deleted` = 0													
													AND a.`country_id` = {$this->config->item('country')}
													AND ( 
														p.`is_nlm` = 0 OR 
														p.`is_nlm` IS NULL 
													)
													AND p.`postcode` IN ( {$postcodes_imp} ) 
													AND (
														j.`status` = 'To Be Booked'	
														OR j.`status` = 'Booked' 
														OR j.`status` = 'DHA'
														OR j.`status` = 'Escalate'
														OR j.`status` = 'On Hold' 
														OR j.`status` = 'Allocate'
													)
													AND ( 
														j.`assigned_tech` = {$tech_run_row->assigned_tech} 
														OR j.`assigned_tech` = 0
														OR j.`assigned_tech` IS NULL 
													) 
													AND(
														j.`date` = '{$tech_run_row->date}'
														OR j.`date` IS NULL
														OR j.`date` = '0000-00-00'
														OR j.`date` = ''
													)														
													");
													
													$trr_count =  $tech_run_rows_sql->row()->trr_count;
													?>
													<tr>
														<td><?php echo "{$sub_region_row->region_name}/{$sub_region_row->subregion_name}"; ?></td>
														<td>(<?php echo $trr_count; ?>)</td>
														<td>
															<?php

															// fetch all future STR
															$future_str_sql = $this->db->query("
															SELECT 
																`tech_run_id`,
																`sub_regions`,
																`date`
															FROM  `tech_run`
															WHERE `sub_regions` LIKE '%{$sub_region_row->sub_region_id}%'
															AND `date` > '".date('Y-m-d')."'
															AND `date` != '{$tech_run_row->date}'
															AND `country_id` = {$this->config->item('country')}
															");
															$fcount = 0;

															foreach( $future_str_sql->result() as $future_str_row ){

																$reg_arr = explode(",",$future_str_row->sub_regions);

																if( in_array($sub_region_row->sub_region_id, $reg_arr) ){

																	echo ($fcount!=0)?', ':'';

																	?>
																		<a href="<?php echo $this->config->item('crm_link'); ?>/set_tech_run.php?tr_id=<?php echo $future_str_row->tech_run_id ?>">
																			<?php echo date('D d/m',strtotime($future_str_row->date)); ?>
																		</a>
																	<?php
																	$fcount++;

																}else{
																	$no_set_date_flag = 1;
																}

															}

															if( $fcount==0 ){
																echo "No Days scheduled";
															}
															?>
														</td>
													</tr>
												<?php
												}

											}

										}
										?>
									</table>
								</div>
							</section>

							<section class="card card-blue-fill">
								<header class="card-header">Log</header>
								<div class="card-block">
									<table class="table colour_tbl">
										<tr>
											<th>Description</th>
											<th>Who</th>
											<th>Date</th>		
										</tr>
										<?php										
										foreach( $tech_run_logs_sql->result() as $trl_row ){ ?>
											<tr>
												<td><?php echo $trl_row->description; ?></td>
												<td><?php echo $this->system_model->formatStaffName($trl_row->FirstName, $trl_row->LastName); ?></td>
												<td><?php echo date('d/m/Y H:i',strtotime($trl_row->created)); ?></td>
											</tr>
										<?php
										}
										?>						
									</table>
								</div>
							</section>

						</div>

						<div class="col-6">

							<section class="card card-blue-fill">
								<header class="card-header">Select</header>
								<div class="card-block">																		
																	
									<div class="mt-2" id="select_function_btn_div">

										<table class="table table-borderless" id="select_table_section">
											<tr>
												<td class="chk_col">
													<span class="checkbox">
														<input type="checkbox" id="sel_job_type_chk">
														<label for="sel_job_type_chk"></label>
													</span>
												</td>
												<td>Select Job Type</td>
												<td class="select_job_type_class">
													<select name="select_job_type" id="select_job_type" class="form-control">
														<option value="">---</option>
														<?php
														foreach( $job_type_sql->result() as $job_type_row ){ ?>
															<option value="<?php echo $job_type_row->job_type; ?>"><?php echo $job_type_row->job_type; ?></option>
														<?php
														}
														?>
													</select>
												</td>
												<td class="select_job_type_class"><button type="button" class="btn btn-primary" id="select_job_type_btn">Select Job Type</button></td>
											</tr>
											<tr>
												<td class="chk_col">
													<span class="checkbox">
														<input type="checkbox" id="sel_agency_job_chk">
														<label for="sel_agency_job_chk"></label>
													</span>
												</td>
												<td>Select Agency Jobs</td>
												<td class="select_agency_jobs_class">
													<select name="select_agency_jobs" id="select_agency_jobs" class="form-control">
														<option value="">---</option>	
														<?php
														foreach( $sel_agency_jobs_sql->result() as $sel_agency_jobs_row ){ ?>
															<option value="<?php echo $sel_agency_jobs_row->agency_id; ?>"><?php echo $sel_agency_jobs_row->agency_name; ?></option>
														<?php
														}
														?>																									
													</select>
												</td>
												<td class="select_agency_jobs_class">
													<button type="button" class="btn btn-primary" id="select_agency_jobs_btn">Select Agency Jobs</button>
												</td>
											</tr>
											<tr>
												<td class="chk_col">
													<span class="checkbox">
														<input type="checkbox" id="sel_first_visit_chk">
														<label for="sel_first_visit_chk"></label>
													</span>
												</td>
												<td>Select First Visit</td>
											</tr>
											<tr>
												<td class="chk_col">
													<span class="checkbox">
														<input type="checkbox" id="sel_esc_jobs_chk">
														<label for="sel_esc_jobs_chk"></label>
													</span>
												</td>
												<td>Select Escalate Jobs</td>
											</tr>
											<tr>
												<td class="chk_col">
													<span class="checkbox">
														<input type="checkbox" id="sel_no_tenant_det_chk">
														<label for="sel_no_tenant_det_chk"></label>
													</span>
												</td>
												<td>Select No Tenants</td>
											</tr>
											<tr>
												<td class="chk_col">
													<span class="checkbox">
														<input type="checkbox" id="select_holiday_rent_chk">
														<label for="select_holiday_rent_chk"></label>
													</span>
												</td>
												<td>Select Holiday Rental</td>
											</tr>
											<tr>
												<td class="chk_col">
													<span class="checkbox">
														<input type="checkbox" id="select_uncoloured_chk">
														<label for="select_uncoloured_chk"></label>
													</span>
												</td>
												<td>Select Uncoloured</td>
											</tr>
										</table>

										
									</div>																										

								</div>
							</section>

							<section class="card card-blue-fill">
								<header class="card-header">Other</header>
								<div class="card-block">
									
									<button type="button" class="btn btn-success mt-2" id="en_btn">Entry Notice</button>
									<button type="button" class="btn btn-danger mt-2" id="delete_btn">Delete</button>
									<button type="button" class="btn mt-2">Refresh</button>
									<button 
										type="button" 
										class="btn <?php echo ( $tech_run_row->show_hidden == 1 )?'btn-warning':'btn-secondary'; ?> mt-2" 
										id="hidden_jobs_toggle_btn"
									> 
										<?php echo ( $tech_run_row->show_hidden == 1 )?'Hide':'Show'; ?> <span id="hiddenRowsCount_span">0</span> Hidden Jobs
									</button>
									<button type='button' class='btn mt-2' id="btn_display_distance">Display distance to agency</button>

								</div>
							</section>

						</div>

					</div>	

				</div><!--.tab-pane-->

			<?php
			}
			?>
			

		</div><!--.tab-content-->

	</section><!--.tabs-section-->
	
	<?php
	// show only if tech run exist
	if( $has_tech_run == true ){
	?>

		<table id="tbl_maps" class="table">
			<thead>
				<tr>	
					<th>#</th>
					<th>Colour</th>	
					<th>Details</th>
					<th>Deadline</th>
					<th>Age</th>
					<th>End Date</th>
					<th>Vacant</th>
					<th>Notes</th>
					<th>Time</th>
					<th>Job Status</th>
					<th>Job Type</th>
					<th>Service</th>
					<th>DK</th>				
					<th>Address</th>
					<th>Region</th>
					<th>Agency</th>
					<th>Job Comments</th>
					<th>Property Comments</th>
					<th class="EN_show_elem">Alarms Req.</th>
					<th>Preferred Time</th>		
					<th class="DTA_elem">Distance to agency</th>							
					<th class="EN_show_elem">Time</th>
					<th class="EN_show_elem">Keys/EN</th>
					<?php
					if( $tech_run_row->show_hidden == 1 ){ ?>
						<th class="hidden_elem">Hidden</th>
					<?php
					}
					?>
					<th class="chk_col">
						<span class="checkbox">
							<input type="checkbox" id="check-all" class="check-all">
							<label for="check-all" class="chk_lbl"></label>
						</span>
					</th>
				</tr>
			</thead>							
			<tbody>
				<tr class="nodrop nodrag">
					<td>1</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td>
						<?php echo $start_acco_row->name; ?><br />
						<?php echo $start_acco_row->phone; ?>
					</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td><img src="/images/tech_run/red_house_resized.png" /></td>
					<td></td>
					<td><?php echo $start_acco_row->address; ?></td>
					<td></td>
					<td></td>
					<td></td>	
					<td></td>
					<td class="EN_show_elem">&nbsp;</td>
					<td></td>								
					<td class="DTA_elem">&nbsp;</td>
					<td class="EN_show_elem">&nbsp;</td>
					<td class="EN_show_elem">&nbsp;</td>					
					<?php
					if( $tech_run_row->show_hidden == 1 ){ ?>
						<td class="hidden_elem">&nbsp;</td>
					<?php
					}
					?>					
					<td></td>
				</tr>
			<?php
			$ctr = 2;
			$hiddenRowsCount = 0;
			foreach( $tech_run_row_sql->result() as $tech_run_row_data ){  // job

				$bgcolor = null; // clear background color every row

				if( $tech_run_row_data->row_id_type == 'job_id' ){

					$show_row = true;
					$hiddenText = null;
					$isUnavailable = 0;
					$isHidden = 0;
					$isPriority = 0;
					$is_no_en = false;

					// filters
					// if job type is 240v Rebook and status is to be booked and the tech is not electrician then hide it
					if( 
						( $tech_run_row_data->job_type == '240v Rebook' || $tech_run_row_data->is_eo == 1 ) && 
						$tech_run_row_data->j_status == 'To Be Booked' && $tech_run_row->is_tech_elec == 0 
					){
						$hiddenText .= '240v<br />';
						$show_row = false;
					}else{
						$show_row = true;
					}

					if( $tech_run_row_data->hidden == 1 ){
						$hiddenText .= 'User<br />';
					}

					if( $tech_run_row_data->unavailable == 1 && $tech_run_row_data->unavailable_date == $tech_run_row->date ){

						$isUnavailable = 1;
						$hiddenText .= 'Unavailable<br />';
						
					}
		
					$startDate = date('Y-m-d',strtotime($tech_run_row_data->start_date));
		
					if( $tech_run_row_data->job_type == 'Lease Renewal' && ( $tech_run_row_data->start_date != "" && $tech_run_row->date < $startDate ) ){
						$hiddenText .= 'LR<br />';
					}
		
					if( $tech_run_row_data->job_type == 'Change of Tenancy' && ( $tech_run_row_data->start_date != "" && $tech_run_row->date < $startDate  ) ){
						$hiddenText .= 'COT<br />';
					}
		
					if( $tech_run_row_data->j_status == 'DHA' && ( $tech_run_row_data->start_date != "" && $tech_run_row->date < $startDate ) ){
						$hiddenText .= 'DHA<br />';
					}
		
					if( $tech_run_row_data->j_status == 'On Hold' && ( $tech_run_row_data->start_date != "" && $tech_run_row->date < $startDate ) ){
						$hiddenText .= 'On Hold<br />';
					}
		
					if( $tech_run_row_data->j_status == 'On Hold' && $tech_run_row_data->allow_upfront_billing == 1 ){
						$hiddenText .= 'Up Front Billing<br />';
					}
		
					// this job is for electrician only
					if( $tech_run_row_data->electrician_only == 1 && $tech_run_row->is_tech_elec == 0 ){
						$hiddenText .= 'Electrician Only<br />';
					}

					if( $tech_run_row->show_hidden == 0 && $hiddenText != "" && $tech_run_row_data->j_status != 'Booked' ){
						$show_row = false;
					}else{
						$show_row = true;
					}
					
					if( $hiddenText != "" ){

						$hiddenRowsCount++;
						//$bgcolor = "#ADD8E6";
						$isHidden = 1;

					}
		
					if( $tech_run_row->show_hidden == 1 && ( $tech_run_row_data->hidden == 1 || $isUnavailable == 1 ) ){
						$hideChk = 0;
					}else if( $tech_run_row->show_hidden == 1 ){
						$hideChk = 1;
					}else{
						$hideChk = 0;
					}
		
		
					// if property and agency is NO to EN
					if( $tech_run_row_data->no_en == 1 || ( is_numeric($tech_run_row_data->allow_en) && $tech_run_row_data->allow_en == 0 ) ){
						$is_no_en = true;
					}
				
					// priority jobs
					$isPriority = false;
					if(
						$tech_run_row_data->job_type == "Change of Tenancy" ||
						$tech_run_row_data->job_type == "Lease Renewal" ||
						$tech_run_row_data->job_type == "Fix or Replace" ||
						$tech_run_row_data->job_type == "240v Rebook" ||
						$tech_run_row_data->is_eo == 1 ||
						$tech_run_row_data->j_status == 'DHA' ||
						$tech_run_row_data->urgent_job == 1
					){
						$isPriority = true;
					}

					$ecalate_reason_str = $tech_run_row_data->j_status;
					$isEscalateJob = 0;

					if( $tech_run_row_data->j_status == 'Escalate' ){

						// get Escalate Reasons
						$escalate_sql = $this->db->query("
							SELECT *
							FROM `selected_escalate_job_reasons` AS sejr
							LEFT JOIN `escalate_job_reasons` AS ejr ON sejr.`escalate_job_reasons_id` = ejr.`escalate_job_reasons_id`
							WHERE sejr.`job_id` = {$tech_run_row_data->jid}
						");

						$escalate_arr = [];
						foreach( $escalate_sql->result() as $escalate_row ){
							$escalate_arr[] = $escalate_row->reason_short;
						}

						$ecalate_reason = implode("<br />",$escalate_arr);
						$ecalate_reason_str =  "<b class='text-danger'>{$ecalate_reason}</b>";
						$isEscalateJob = 1;

					}

					$tr_class_arr = [];
					$tr_class_arr[] = ( $tech_run_row_data->hex != '' )?'hasColor':'NoColor'; // colour
					$tr_class_arr[] = ( $isHidden != '' )?'hidden_elem hiddenJobs':null; // hidden jobs
					$tr_class_arr[] = ( $tech_run_row_data->holiday_rental == 1 )?'jrow_holiday_rental':null; // holiday/short term rental
					$tr_class_arr[] = ( $isEscalateJob == 1 )?'jrow_escalate_jobs':null; // escalate						
					$tr_class_arr[] = ( $tech_run_row_data->j_status == 'Booked' )?'jrow_escalate_jobs':null; // is booked		

					// first visit
					// exclude other supplier(1) and upfront bill(2)
					$completed_job_sql = $this->db->query("
						SELECT COUNT(id) AS j_count
						FROM `jobs`
						WHERE `property_id` = {$tech_run_row_data->property_id}
						AND `status` = 'Completed'
						AND `assigned_tech` NOT IN(1,2)
					");
					$completed_job_j_count = $completed_job_sql->row()->j_count;
					$tr_class_arr[] = ( $completed_job_j_count == 0 )?'jrow_first_visit':null;

					// no tenants, and not property vacant
					$has_tenants = false;
					if( $tech_run_row_data->property_id > 0 ){

						$pt_sql = $this->db->query("
						SELECT *
						FROM `property_tenants`
						WHERE `property_id` = {$tech_run_row_data->property_id}
						AND `active` = 1
						");					
						$has_tenants = ( $pt_sql->num_rows() > 0 )?true:false;

						if( $has_tenants == true ){

							$has_tenant_email = false;
							$has_tenant_mobile = false;
							foreach( $pt_sql->result() as $pt_row ){

								// check if tenant has valid email
								if( $pt_row->tenant_email != "" && filter_var($pt_row->tenant_email, FILTER_VALIDATE_EMAIL) ){
									$has_tenant_email = true;
								}
			
								// check if tenant has valid mobileg 
								if( $pt_row->tenant_mobile != "" ){
									$has_tenant_mobile = true;
								}

							}

						}

					}				
					$tr_class_arr[] = ( $completed_job_j_count == 0 && $tech_run_row_data->property_vacant == 0 )?'no_tenants':null;

					// if no tenant or tenant has no email and SMS, hide checkbox
					$tenant_has_no_email_and_mob = false;
					if( $has_tenant_email == false && $has_tenant_mobile == false ){
						//$hideChk = 1;
						$tenant_has_no_email_and_mob = true;
					}	

					// add all class
					$tr_class_imp = implode(' ',$tr_class_arr);	


					// row highlight color
					if( $tech_run_row_data->ts_completed == 1 ){
						$bgcolor = "#c2ffa7";
					}

					if( $tech_run_row_data->dnd_sorted == 0 ){
						$bgcolor = '#ffff8e';
					}

					
					if( $show_row == true ){						
					?>
					<tr 
						id="<?php echo $tech_run_row_data->tech_run_rows_id; ?>" 
						class="tech_run_row_tr <?php echo $tr_class_imp; ?>" 
						data-hlc_id="<?php echo $tech_run_row_data->highlight_color; ?>"
						style="background-color:<?php echo $bgcolor; ?>"
					>	
						<td><?php echo $ctr; ?></td>
						<td>
							<?php
							if( $tech_run_row_data->hex != '' ){ ?>
								<span class="span_circle" style="background-color:<?php echo $tech_run_row_data->hex; ?>"></span>
							<?php
							}
							?>						
						</td>
						<td>
							<?php
							$icons_arr = [];

							// green phone icon
							// 'phone call' logs
							$job_log_sql = $this->db->query("
							SELECT 
								jl.`eventdate`,
								jl.`eventtime`
							FROM job_log AS jl 
							LEFT JOIN staff_accounts AS sa ON jl.staff_id = sa.StaffID
							WHERE jl.`job_id` = {$tech_run_row_data->jid}
							AND jl.`deleted` = 0 
							AND jl.`eventdate` = '".date('Y-m-d')."'
							AND jl.`contact_type` = 'Phone Call'
							ORDER BY jl.`log_id` DESC 
							LIMIT 1
							");
							$job_log_row = $job_log_sql->row();

							$current_time = date("Y-m-d H:i:s");
							$job_log_time = date("Y-m-d H:i",strtotime("{$job_log_row->eventdate} {$job_log_row->eventtime}:00"));
							$last4hours = date("Y-m-d H:i",strtotime("-3 hours"));						

							if( $tech_run_row_data->j_status == 'To Be Booked' && $job_log_sql->num_rows() > 0 && ( $job_log_time >= $last4hours && $job_log_time <= $current_time ) ){														
								$icons_arr[] =  (object) [
									'src' => '/images/tech_run/green_phone.png',
									'title' => 'Phone Call'
								];
							}

							// first visit
							// exclude other supplier(1) and upfront bill(2)
							$first_visit_sql = $this->db->query("
								SELECT COUNT(id) AS j_count
								FROM `jobs`
								WHERE `property_id` = {$tech_run_row_data->property_id}
								AND `status` = 'Completed'
								AND `assigned_tech` != 1
								AND `assigned_tech` != 2
							");			
							$first_visit_row = $first_visit_sql->row();
				
							if( $first_visit_row->j_count == 0 ) { // first visit
								$icons_arr[] =  (object) [
									'src' => '/images/tech_run/first_icon2.png',
									'title' => 'First visit'
								];
							}
							
							// priority
							if( $isPriority == true ){
								$icons_arr[] =  (object) [
									'src' => '/images/tech_run/caution.png',
									'title' => 'Priority Jobs'
								];
							}

							// key acccess
							if( $tech_run_row_data->key_access_required == 1 && $tech_run_row_data->j_status == 'Booked' ){							
								$icons_arr[] =  (object) [
									'src' => '/images/tech_run/key_icon_green.png',
									'title' => 'Key Access Required'
								];
							}

							// check active tenants
							$active_tenant_sql = $this->db->query("
								SELECT COUNT(`property_tenant_id`) AS pt_count
								FROM `property_tenants`
								WHERE `property_id` = {$tech_run_row_data->property_id}
								AND `active` = 1
							");			
							$active_tenant_row = $active_tenant_sql->row();
				
							if( $active_tenant_row->pt_count == 0 ) { // no tenants
								$icons_arr[] =  (object) [
									'src' => '/images/tech_run/no_tenant.png',
									'title' => 'No Tenants'
								];
							}

							// age
							if(  $tech_run_row_data->age  ){														
								$icons_arr[] =  (object) [
									'src' => '/images/tech_run/bomb.png',
									'title' => '60+ days old'
								];
							}

							// service garage
							if( $tech_run_row_data->p_state == 'NSW' && $tech_run_row_data->service_garage == 1 ){							
								$icons_arr[] =  (object) [
									'src' => '/images/serv_img/service_garage_icon.png',
									'title' => 'Service Garage'
								];
							}

							
							// display icons
							foreach( $icons_arr as $icon ){ ?>
								<img src="<?php echo $icon->src; ?>" class="details_icon mr-1 mb-1" title="<?php echo $icon->title; ?>" />
							<?php
							}
							?>
						</td>
						<td><?php echo ( $tech_run_row_data->deadline >= 0 )?$tech_run_row_data->deadline:"<span class='text-danger'>{$tech_run_row_data->deadline}</span>"; ?></td>
						<td><?php echo $tech_run_row_data->age; ?></td>
						<td><?php echo ( $this->system_model->isDateNotEmpty($tech_run_row_data->due_date) )?date('d/m/Y',strtotime($tech_run_row_data->due_date)):null; ?></td>
						<td><?php echo ( $tech_run_row_data->property_vacant == 1 )?'Yes':'No'; ?></td>
						<td><?php echo $tech_run_row_data->tech_notes; ?></td>
						<td class="time_of_day_td">
							<?php
							if( $tech_run_row->run_complete == 1 ){ ?>

								<div class="time_of_day_div">
									<input type="text" class="time_of_day form-control" value="<?php echo $tech_run_row_data->time_of_day; ?>" />
								</div>

							<?php
							}else{
								echo $tech_run_row_data->time_of_day;
							}
							?>
						</td>
						<td>
							<?php 
							echo $ecalate_reason_str; 
							if( $tech_run_row_data->j_status == 'Booked' ){
							?>
								<img data-toggle="tooltip" title="Booked" class="booked_icon" src="/images/tech_run/check_icon2.png" />
							<?php
							}
							?>
						</td>
						<td>
							<a target="_blank" href="<?php echo $this->config->item('crm_link'); ?>/view_job_details.php?id=<?php echo $tech_run_row_data->jid; ?>&tr_tech_id=<?php echo $tech_run_row->assigned_tech; ?>&tr_date=<?php echo $tech_run_row->date; ?>&tr_booked_by=<?php echo $this->session->staff_id; ?>">
								<?php echo $tech_run_row_data->job_type ?>
							</a>
						</td>
						<td>
							<?php
							// display icons
							$job_icons_params = array(
								'job_id' => $tech_run_row_data->jid
							);
							echo $this->system_model->display_job_icons_v2($job_icons_params);
							?>
						</td>
						<td><?php echo ( $tech_run_row_data->door_knock == 1 )?'Yes':null; ?></td>								
						<td>
							<a target="_blank" href="<?php echo $this->config->item('crm_link'); ?>/view_property_details.php?id=<?php echo $tech_run_row_data->property_id; ?>">
								<span class="p_address"><?php echo $prop_address = "{$tech_run_row_data->p_address_1} {$tech_run_row_data->p_address_2}, {$tech_run_row_data->p_address_3} {$tech_run_row_data->p_state} {$tech_run_row_data->p_postcode}"; ?></span>
							</a>
						</td>
						<td><?php echo $tech_run_row_data->subregion_name; ?></td>
						<td>
							<a target="_blank" href="/agency/view_agency_details/<?php echo $tech_run_row_data->agency_id; ?>"><?php echo $agency_name = $tech_run_row_data->agency_name; ?></a>
							<span class="a_address"><?php echo "{$tech_run_row_data->a_address_1} {$tech_run_row_data->a_address_2}, {$tech_run_row_data->a_address_3} {$tech_run_row_data->a_state} {$tech_run_row_data->a_postcode}"; ?></span>
						</td>
						<td><?php echo $tech_run_row_data->j_comments; ?></td>
						<td><?php echo $tech_run_row_data->p_comments; ?></td>
						<td class="EN_show_elem" style="text-align: center;"><?php echo $tech_run_row_data->qld_new_leg_alarm_num; ?></td>
						<td><?php echo $tech_run_row_data->preferred_time; ?></td>
						<td class="DTA_elem distance_to_agency"></td>	
						<td class="EN_show_elem"><input type="text" class="form-control en_time" style="width: 64px; padding-left: 5px;" value="8.30-3.30"></td>	
						<td class="EN_show_elem"><?php echo ( $tech_run_row_data->key_allowed != 1 || $tech_run_row_data->no_keys == 1 || $tech_run_row_data->no_en == 1 )?'<img src="/images/cross_red.png" />':null; ?></td>
						<?php
						if( $tech_run_row->show_hidden == 1 ){ ?>
							<td class="hidden_elem"><?php echo $hiddenText; ?></td>
						<?php
						}
						?>						
						<td class="chk_col">


							<?php

							// no tenant icon
							if( $has_tenants == false ){ ?>

								<img
									data-toggle="tooltip" 
									title="No Tenants"
									class="no_tenant_icon EN_show_elem"
									data-prop_vacant="<?php echo $tech_run_row_data->property_vacant; ?>"
									data-start_date="<?php echo $tech_run_row_data->start_date; ?>"
									data-due_date="<?php echo $tech_run_row_data->due_date; ?>"
									style="cursor: pointer;"

									src="/images/tech_run/no_tenant.png"
								/>

							<?php
							}
							
							// no tenant mobile and email icon
							if( $tenant_has_no_email_and_mob == true ){ ?>
								<img class="invalid_en_icon EN_show_elem" data-toggle="tooltip" title="No tenant mobile and email, invalid for EN" style="cursor: pointer;" src="/images/tech_run/invalid_en.png" />
							<?php
							}

							// hide checkbox condition
							$hide_chk_on_en = ( $has_tenants == false || $tenant_has_no_email_and_mob == true )?true:false;
							?>

							<input type="hidden" class="row_id_type" value="<?php echo $tech_run_row_data->row_id_type; ?>" />
							<input type="hidden" class="job_id" value="<?php echo $tech_run_row_data->jid; ?>" />
							<input type="hidden" class="job_status" value="<?php echo $tech_run_row_data->j_status; ?>" />
							<input type="hidden" class="job_type" value="<?php echo $tech_run_row_data->job_type; ?>" />

							<input type="hidden" class="property_id" value="<?php echo $tech_run_row_data->property_id; ?>" />
							<input type="hidden" class="prop_address" value="<?php echo $prop_address; ?>" />
							<input type="hidden" class="prop_no_dk" value="<?php echo $tech_run_row_data->no_dk; ?>" />
							
							<input type="hidden" class="agency_id" value="<?php echo $tech_run_row_data->agency_id; ?>" />
							<input type="hidden" class="agency_name" value="<?php echo $agency_name; ?>" />
							<input type="hidden" class="agency_no_dk" value="<?php echo $tech_run_row_data->allow_dk; ?>" />
							<input type="hidden" class="sort_order_num" value="<?php echo $tech_run_row_data->sort_order_num; ?>" />
							<input type="hidden" class="row_type" value="job" />													

							<span class="checkbox <?php echo ( $hide_chk_on_en == true )?'hide_chk_on_en':null; ?>">
								<input 
									type="checkbox" 
									id="trr_chk-<?php echo $tech_run_row_data->tech_run_rows_id; ?>" 
									class="trr_chk" 
									data-row-type="job"
									value="<?php echo $tech_run_row_data->tech_run_rows_id; ?>" 
								/>
								<label for="trr_chk-<?php echo $tech_run_row_data->tech_run_rows_id; ?>"></label>
							</span>

						</td>		             								
					</tr>
				<?php
					$ctr++;
					}	

					//$job_row_arr[] = $tech_run_row_data;				
				}else if( $tech_run_row_data->row_id_type == 'keys_id' ){ // key 

					if( $tech_run_row_data->trk_action == "Pick Up" && $tech_run_row_data->trk_completed == 1 ){
						$bgcolor = "#c2ffa7";
					}									
					?>

					<tr style="background-color:<?php echo $bgcolor; ?>">
						<td><?php echo $ctr; ?></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td>
							<?php 
							if( $tech_run_row_data->trk_completed == 1 ){

								$kr_act = explode(" ",$tech_run_row_data->trk_action);
								$temp2 = ( $tech_run_row_data->trk_action == "Drop Off" )?'p':null;
								$temp = "{$kr_act[0]}{$temp2}ed";
								$action = "{$temp} {$kr_act[1]}";

							}else{
								$action = $tech_run_row_data->trk_action;
							}
							echo $action;
							?>
						</td>
						<td></td>
						<td></td>
						<td></td>
						<td><img src="/images/key_icon_green.png" /></td>
						<td></td>
						<td><?php echo "{$tech_run_row_data->key_a_address_1} {$tech_run_row_data->key_a_address_2}, {$tech_run_row_data->key_a_address_3}";  ?></td>
						<td></td>
						<td>
							<a target="_blank" href="/agency/view_agency_details/<?php echo $tech_run_row_data->key_a_agency_id; ?>">
								<?php echo $tech_run_row_data->key_a_agency_name;  ?>
							</a>
						</td>
						<td></td>
						<td class="EN_show_elem">&nbsp;</td>
						<td></td>
						<td></td>	
						<td class="DTA_elem">&nbsp;</td>					
						<td class="EN_show_elem">&nbsp;</td>
						<td class="EN_show_elem">&nbsp;</td>
						<?php
						if( $tech_run_row->show_hidden == 1 ){ ?>
							<td class="hidden_elem">&nbsp;</td>
						<?php
						}
						?>
						<td>
							
							<input type="hidden" class="row_type" value="key" />
							<input type="hidden" class="trk_id" value="<?php echo $tech_run_row_data->tech_run_keys_id; ?>" />

							<span class="checkbox">
								<input 
									type="checkbox" 
									id="trr_chk-<?php echo $tech_run_row_data->tech_run_rows_id; ?>" 
									class="trr_chk" 
									value="<?php echo $tech_run_row_data->tech_run_rows_id; ?>" 
									data-row-type="key"
								/>
								<label for="trr_chk-<?php echo $tech_run_row_data->tech_run_rows_id; ?>"></label>
							</span>

						</td>
					</tr>
					
				<?php
				$ctr++;
				}else if( $tech_run_row_data->row_id_type == 'supplier_id' ){ // supplier ?>

					<tr>
						<td><?php echo $ctr; ?></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td>Supplier</td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td><?php echo $tech_run_row_data->sup_address; ?></td>
						<td><?php echo $tech_run_row_data->company_name; ?></td>
						<td></td>
						<td class="EN_show_elem">&nbsp;</td>
						<td></td>						
						<td class="DTA_elem">&nbsp;</td>
						<td class="EN_show_elem">&nbsp;</td>
						<td class="EN_show_elem">&nbsp;</td>
						<?php
						if( $tech_run_row->show_hidden == 1 ){ ?>
							<td class="hidden_elem">&nbsp;</td>
						<?php
						}
						?>
						<td>

							<input type="hidden" class="row_type" value="supplier" />
							<input type="hidden" class="trs_id" value="<?php echo $tech_run_row_data->tech_run_suppliers_id; ?>" />

							<span class="checkbox">
								<input type="checkbox" 
									id="trr_chk-<?php echo $tech_run_row_data->tech_run_rows_id; ?>" 
									class="trr_chk" 
									value="<?php echo $tech_run_row_data->tech_run_rows_id; ?>" 
									data-row-type="supplier"
								/>
								<label for="trr_chk-<?php echo $tech_run_row_data->tech_run_rows_id; ?>"></label>
							</span>

						</td>
					</tr>
					
				<?php
				$ctr++;
				}
		
			}

			//print_r($job_row_arr);
			?>	
				<tr class="nodrop nodrag">
					<td><?php echo $ctr; ?></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td>
						<?php echo $end_acco_row->name; ?><br />
						<?php echo $end_acco_row->phone; ?>
					</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td><img src="/images/tech_run/red_house_resized.png" /></td>
					<td></td>
					<td><?php echo $end_acco_row->address; ?></td>
					<td></td>
					<td></td>
					<td></td>
					<td class="EN_show_elem">&nbsp;</td>
					<td></td>						
					<td class="DTA_elem">&nbsp;</td>
					<td class="EN_show_elem">&nbsp;</td>
					<td class="EN_show_elem">&nbsp;</td>
					<?php
					if( $tech_run_row->show_hidden == 1 ){ ?>
						<td class="hidden_elem">&nbsp;</td>
					<?php
					}
					?>
					<td></td>
				</tr>		
			</tbody>
		</table>
		<input type="hidden" id="hiddenRowsCount" value="<?php echo $hiddenRowsCount; ?>" />

	<?php
	}
	?>
	

</div>

<section class="card card-blue-fill" id="bot_func_btn_div">
	
	<header class="card-header">

		<span id="panel_text">Select Action</span>

		<span class="fa fa-minus float-right" id="minimize_panel"></span>

		<span class="fa fa-square-o float-right ml-3" id="maximize_panel"></span>

	</header>

	<div class="card-block" id="bot_func_btn_inner_div">
		
		<select id="tech_run_functions" class="form-control">
			<option value="">---</option>
			<option value="hide">Hide/Unhide</option>
			<option class="show_for_jobs" value="dk">Door Knocks</option>		
			<option class="show_for_jobs" value="highlight">Assign/Remove Colour</option>
			<option class="show_for_jobs" value="escalate">Escalate</option>
			<option class="show_for_jobs" value="change_tech">Change Tech</option>
			<option class="show_for_jobs" value="mark_tech_sick">Mark Tech Sick</option>
			<option class="show_for_keys" value="keys">Keys</option>
			<option class="show_for_supplier" value="suppliers">Supplier</option>
			<option class="EN_show_elem" value="en">Entry Notice</option>
		</select>

		<div id="bot_func_hide" class="mt-2">
			<button type="button" class="btn" id="hide_btn">Hide</button>
		</div>

		<div id="bot_func_assign_dk" class="mt-2">
			<button type="button" class="btn" id="assign_dk_btn">Assign Door Knock</button>
		</div>

		<div id="bot_func_en" class="mt-2">
			<button type="button" class="btn" id="issue_en_btn">Issue Entry Notice</button>
		</div>

		<div id="bot_func_escalate" class="mt-2">
			<button type="button" class="btn" id="escalate_jobs_btn">Escalate Jobs</button>
		</div>
		
		<div id="bot_func_highlight_row">
			<div class="mt-4">
				<select class="form-control mb-2" id="row_highlight_color">
					<option value="">---</option>
					<?php
					foreach( $trr_color_sql->result() as $trr_color_row ){ ?>
						<option value="<?php echo $trr_color_row->tech_run_row_color_id; ?>"><?php echo $trr_color_row->color; ?></option>
					<?php
					}
					?>
				</select>
				<button type="button" id="btn_assign_color" class="btn">Assign Color</button>
			</div>

			<div class="mt-4">
				<button type="button" id="btn_remove_color" class="btn">Remove Color</button>
			</div>
		</div>	

		<div id="bot_func_change_tech">
			<div class="mt-4">
				<select class="form-control mb-2" id="change_tech_dp">
					<option value="">-- Select --</option>
					<?php
					foreach( $tech_sql->result() as $tech_row ){ ?>

						<option value="<?php echo $tech_row->StaffID; ?>" <?php echo ( $tech_row->StaffID == $tech_run_row->assigned_tech )?'selected':null; ?>>
							<?php echo $this->system_model->formatStaffName($tech_row->FirstName,$tech_row->LastName).( ( $tech_row->is_electrician == 1 )?' [E]':null ); ?>
						</option>

					<?php
					}
					?>
				</select>
				<button type="button" class="btn" id="change_tech_update_btn">Update Tech</button>
			</div>
		</div>

		<div id="bot_func_mark_tech_sick" class="mt-2">
			<button type="button" class="btn" id="mark_tech_sick">Mark Tech Sick</button>
		</div>

		<div id="bot_func_remove_keys" class="mt-2">
			<button type="button" class="btn" id="remove_keys_btn">Remove Keys</button>
		</div>

		<div id="bot_func_remove_suppliers" class="mt-2">
			<button type="button" class="btn" id="remove_suppliers_btn">Remove Suppliers</button>
		</div>	

	</div>
</section>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" ><pre><code><?php echo $page_query; ?></code></pre></div>
<!-- Fancybox END -->

<script type="text/javascript">
function show_bottom_functions(){

	var ticked_count = jQuery(".trr_chk:checked").length;

	if( ticked_count > 0 ){

		// find ticked row count for jobs, key and supplier
		var jobs_row_ticked_count = jQuery('input.trr_chk[data-row-type="job"]:checked').length;
		var key_row_ticked_count = jQuery('input.trr_chk[data-row-type="key"]:checked').length;
		var supplier_row_ticked_count = jQuery('input.trr_chk[data-row-type="supplier"]:checked').length;

		// show jobs dropdown option
		if( jobs_row_ticked_count > 0 ){
			jQuery(".show_for_jobs").show();
		}else{
			jQuery(".show_for_jobs").hide();
		}

		// show key dropdown option
		if( key_row_ticked_count > 0 ){
			jQuery(".show_for_keys").show();
		}else{
			jQuery(".show_for_keys").hide();
		}

		// show supplier dropdown option
		if( supplier_row_ticked_count > 0 ){
			jQuery(".show_for_supplier").show();
		}else{
			jQuery(".show_for_supplier").hide();
		}

		jQuery("#bot_func_btn_div").show();		

	}else{

		// hide all dropdown options
		jQuery(".show_for_jobs").hide();

		jQuery("#bot_func_btn_div").hide();

	}

}

function update_tech_run_color_table(obj){

	var tr_id = '<?php echo $tr_id; ?>';

	var parent_tr = obj.parents("tr:first");

	var colour_id = parent_tr.find(".ct_trrc_id").val();
	var time = parent_tr.find(".ct_time").val();
	var jobs_num = parent_tr.find(".ct_jobs").val();
	var no_keys = parent_tr.find(".ct_no_keys_chk").prop("checked");
	var no_keys_fin = (no_keys==true)?1:0;
	var booked_jobs = parent_tr.find(".ct_booked_job").val();
	var status_dif = '';
	var isFullyBooked = 0;

	if( no_keys == true ){
		parent_tr.find(".redCross").show();
	}else{
		parent_tr.find(".redCross").hide();
	}


	// invoke ajax
	jQuery("#load-screen").show();
	jQuery.ajax({
		type: "POST",
		url: "/tech_run/set_colour_table",
		data: {
			tr_id: tr_id,
			colour_id: colour_id,
			time: time,
			jobs_num: jobs_num,
			no_keys: no_keys_fin,
			booked_jobs: booked_jobs
		}
	}).done(function( ret ){
		
		jQuery("#load-screen").hide();

		var status_dif = jobs_num-booked_jobs;
		var booking_status = getCTstatusReturnData(status_dif);

		if(booking_status=='FULL'){
			status_txt = '<span class="ct_full">FULL</span>';
			isFullyBooked = 1;
		}else{
			status_txt = '-'+status_dif;
		}

		parent_tr.find(".ct_status").html(status_txt);
		parent_tr.find(".ct_fully_booked").val(isFullyBooked);

		<?php
		if( $tech_run_row->run_complete != 1 && $tech_run_row->no_more_jobs != 1 ){ ?>
			hideFullyBookedJobs();
		<?php
		}
		?>

		
	});

}

function getCTstatusReturnData(status_dif){

	if( status_dif > 0 ){
		booking_status = '-'+status_dif;
	}else{
		booking_status = 'FULL';
	}
	return booking_status

}

// colour table: hide fully booked script
function hideFullyBookedJobs(){

	jQuery(".ct_fully_booked").each(function(){

		var ct_fully_booked_dom = jQuery(this);
		var parent_tr = ct_fully_booked_dom.parents("tr:first");

		var ct_trrc_id = parent_tr.find(".ct_trrc_id").val();
		var isFullyBooked = ct_fully_booked_dom.val();

		if( isFullyBooked == 1 ){
			jQuery('#tbl_maps tr[data-hlc_id="'+ct_trrc_id+'"]:not(".isBooked")').hide();
		}else{
			jQuery('#tbl_maps tr[data-hlc_id="'+ct_trrc_id+'"]:not(".isBooked")').show();
		}


	});

}

function countNumOfBookedJobsEachColor(){

	jQuery(".ct_booked_job").val(0); // clear them on load, bec shitty firefox autofills them on refresh

	jQuery(".isBooked").each(function(){

		var trrc_id = jQuery(this).find(".trrc_id").val();
		var booked_job = parseInt(jQuery("#ct_row_id_"+trrc_id).find(".ct_booked_job").val());
		var booked_tot = booked_job+1;
		jQuery("#ct_row_id_"+trrc_id).find(".ct_booked_job").val(booked_tot);

	});

}

function updateStatusColourTableBooked(){

	var tr_id = '<?php echo $tr_id; ?>';

	jQuery(".ct_jobs").each(function(){

		var ct_jobs_dom = jQuery(this);
		var parent_tr = ct_jobs_dom.parents("tr:first");

		var colour_id = parseInt(parent_tr.find(".ct_trrc_id").val());
		var time = parent_tr.find(".ct_time").val();
		var num_jobs = parseInt(parent_tr.find(".ct_jobs").val());
		var booked_job = parseInt(parent_tr.find(".ct_booked_job").val());
		var booking_status = '';
		var status_txt = '';
		var isFullyBooked = 0;



		if( time!='' ){

			// calculate status
			var status_dif = num_jobs-booked_job;

			var booking_status = getCTstatusReturnData(status_dif);

			if(booking_status=='FULL'){
				status_txt = '<span class="ct_full">FULL</span>';
				isFullyBooked = 1;
			}else{
				status_txt = '-'+status_dif;
			}


			// ajax
			jQuery.ajax({
				type: "POST",
				url: "tech_run/update_colour_table_status",
				data: {
					tr_id: tr_id,
					colour_id: colour_id,
					booking_status: booking_status
				}
			}).done(function( ret ){
				// function here
			});


			parent_tr.find(".ct_status").html(status_txt);
			parent_tr.find(".ct_fully_booked").val(isFullyBooked);

			<?php
			if( $tech_run_row->run_complete != 1 && $tech_run_row->no_more_jobs != 1 ){ ?>
				hideFullyBookedJobs();
			<?php
			}
			?>

		}


	});

}


var selected_sub_region_arr = [];
function sub_region_tick(sub_region_ms_dom){

	var is_sub_region_ms_ticked = sub_region_ms_dom.prop("checked");		
	var sub_region_ms =  sub_region_ms_dom.val();
	var sub_region_div_chk_dom = sub_region_ms_dom.parents("div.sub_region_div_chk");
	var sub_region_ms_lbl = sub_region_div_chk_dom.find('.sub_region_ms_lbl').text();

	var sub_region_tag_html = ''+
	'<button type="button" class="btn btn-rounded btn-inline btn-primary sub_region_tag sub_region_tag_btn_'+sub_region_ms+'">'+sub_region_ms_lbl+
		'<input type="hidden" name="sub_region_ms_tag[]" value="'+sub_region_ms+'" />'
		' <i class="fa fa-close"></i>'+
	'</button>'+
	'';

	if( is_sub_region_ms_ticked == true ){ // ticked
	
		if( sub_region_ms > 0 ){

			if( jQuery.inArray( sub_region_ms, selected_sub_region_arr ) == -1 ){
				
				selected_sub_region_arr.push(sub_region_ms);
				jQuery("#sub_region_tag_div").append(sub_region_tag_html);
									
			}

		}

	}else{ // unticked

					
		var index = selected_sub_region_arr.indexOf(sub_region_ms);
		if (index !== -1) {

			// remove sub region ID from array
			selected_sub_region_arr.splice(index, 1);

			// remove tag
			jQuery(".sub_region_tag_btn_"+sub_region_ms).remove();

		}

	}

	console.log(selected_sub_region_arr);

}

// hidden rows ccount script
function get_hidden_jobs_count(){
	
	var hiddenRowsCount = jQuery("#hiddenRowsCount").val();
	jQuery("#hiddenRowsCount_span").html(hiddenRowsCount);

}

// COPIED FROM OLD STR
// get unique agency from STR page
function getUniqueAgenciesFromTheList(){

	// get unique agency from the list
	var agencies = new Array();
	var ex_agencies = new Array();

	jQuery("#tbl_maps .agency_id").each(function(){
	var agency_id = jQuery(this).val();
	if( jQuery.inArray( agency_id, agencies ) == -1 ){
		agencies.push(parseInt(agency_id));
	}
	});

	<?php
	// add FN agencies
	if( count($fn_agency_sub) > 0 ){
		foreach( $fn_agency_sub as $fn_sub_agency_id ){ ?>
			agencies.push(parseInt(<?php echo $fn_sub_agency_id; ?>));
		<?php
		}
	}
	?>

	<?php
	// add vision agencies
	if( count($vision_agency_sub) > 0 ){
		foreach( $vision_agency_sub as $vision_sub_agency_id ){ ?>
			agencies.push(parseInt(<?php echo $vision_sub_agency_id; ?>));
		<?php
		}
	}
	?>

	//console.log("agencies: "+agencies);
	//console.log("ex_agencies: "+ex_agencies);

	// remove agency not in the list
	jQuery("#keys_agency option").each(function(index){

		var opt = jQuery(this);
		var agency_id = parseInt(opt.val());
		if( index>0 && jQuery.inArray( agency_id, agencies ) == -1 ){
			opt.remove();
		}

	});


}


function hide_close_un_selected_items(){

	var region_filter_div = jQuery("#region_filter_div");
	region_filter_div.find(".sub_region_ms:not(:checked):visible").parents(".sub_region_div_chk").hide(); // hide unticked sub regions
	region_filter_div.find(".region_ms:not(:checked):visible").parents(".region_div_chk").hide(); // hide unticked regions

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

				//row.find(".time").html(results[j].duration.text);
				row.find(".distance_to_agency").html(results[j].distance.text);

				/*
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
				*/

				address_index++;
			}
		}

	}

}

jQuery(document).ready(function(){

	<?php
	// new jobs found popup
	if( $new_jobs_count > 0){ ?>

		swal({
			title: "",
			text: "<?php echo $new_jobs_count ?> new jobs have been found",
			type: "warning",
			confirmButtonClass: "btn-success",
			showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
			timer: <?php echo $this->config->item('timer') ?>
		});

	<?php
	}
	// run on load
	if( $has_tech_run == true ){ ?>

		// get unique agency from STR page
		getUniqueAgenciesFromTheList();

		// hidden rows ccount script
		get_hidden_jobs_count();

		// count number of booked jobs
		countNumOfBookedJobsEachColor();

		// update colour table status
		//updateStatusColourTableBooked();

	<?php
	}	
	?>

	<?php
	// success message popup
	if( $this->session->flashdata('success') ==  true ){ ?>

		swal({
			title: "Success!",
			text: "Tech Run has been created successfully",
			type: "success",
			confirmButtonClass: "btn-success",
			showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
			timer: <?php echo $this->config->item('timer') ?>
		});

	<?php
	}	
	
	// deleted success
	if( $this->session->flashdata('delete_success') ==  true ){ ?>

		swal({
			title: "Success!",
			text: "Tech Run has been successfully deleted",
			type: "success",
			confirmButtonClass: "btn-success",
			showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
			timer: <?php echo $this->config->item('timer') ?>
		});

	<?php
	}		
	?>
	
	// select current tab
	if( localStorage.getItem('str_curren_tab') != '' ){
		jQuery("#"+localStorage.getItem('str_curren_tab') +"").click();
	}

	// datatable
	$('#tbl_maps').DataTable({
        
		/*
		'pageLength': 50,
		'lengthChange': true,
		"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]]
		*/

		"bPaginate": false // disable pagination

	});

	// accomodation hide/show toggle
	jQuery("#accomodation").change(function(){

		var opt = jQuery(this).val();
		if ( opt == 1 || opt == 2 ){
			jQuery("#sel_acco").show();
		}else{
			jQuery("#sel_acco").hide();
		}

	});

	// checkbox script
	// check all
	jQuery("#check-all").change(function(){

		var dom = jQuery(this);
		var is_ticked = dom.prop("checked");

		if( is_ticked == true ){
			jQuery(".trr_chk").prop("checked",true)
		}else{
			jQuery(".trr_chk").prop("checked",false)
		}

		show_bottom_functions();

	});

	// single checkbox
	jQuery("#tbl_maps").on('change','.trr_chk',function(){

		show_bottom_functions();

	});

	// bottom functions script
	jQuery("#tech_run_functions").change(function(){

		var opt = jQuery(this).val();
		
		jQuery("#bot_func_hide").hide();
		jQuery("#bot_func_assign_dk").hide();
		jQuery("#bot_func_en").hide();
		jQuery("#bot_func_highlight_row").hide();
		jQuery("#bot_func_escalate").hide();
		jQuery("#bot_func_change_tech").hide();
		jQuery("#bot_func_mark_tech_sick").hide();
		jQuery("#bot_func_remove_keys").hide();
		jQuery("#bot_func_remove_suppliers").hide();

		switch(opt){

			case 'hide':
				jQuery("#bot_func_hide").show();
			break; 

			case 'dk':
				jQuery("#bot_func_assign_dk").show();
			break;

			case 'highlight':
				jQuery("#bot_func_highlight_row").show();
			break; 

			case 'escalate':
				jQuery("#bot_func_escalate").show();
			break;

			case 'change_tech':
				jQuery("#bot_func_change_tech").show();
			break;

			case 'mark_tech_sick':
				jQuery("#bot_func_mark_tech_sick").show();
			break;	
			
			case 'keys':
				jQuery("#bot_func_remove_keys").show();
			break;

			case 'suppliers':
				jQuery("#bot_func_remove_suppliers").show();
			break;

			case 'en':
				jQuery("#bot_func_en").show();
			break;

		}

	});

	// minimize bottom process panel
	jQuery("#minimize_panel").click(function(){

		jQuery("#bot_func_btn_inner_div").hide();

		jQuery("#minimize_panel").hide();
		jQuery("#maximize_panel").show();		

		jQuery("#bot_func_btn_div").css('bottom','0');
		jQuery("#bot_func_btn_div").css('right','4%');
		jQuery("#bot_func_btn_div").css('opacity',0.5);
		
		

	});

	// show bottom process panel
	jQuery("#maximize_panel").click(function(){

		show_bottom_functions();

		jQuery("#bot_func_btn_inner_div").show();
		
		jQuery("#minimize_panel").show();
		jQuery("#maximize_panel").hide();

		jQuery("#bot_func_btn_div").css('bottom','17%');
		jQuery("#bot_func_btn_div").css('right','7%');
		jQuery("#bot_func_btn_div").css('opacity',1);
		

	});


	// select job type show/hide toggle
	jQuery("#sel_job_type_chk").change(function(){

		var is_ticked = jQuery(this).prop("checked");

		if( is_ticked == true ){
			jQuery(".select_job_type_class").show();
		}else{
			jQuery(".select_job_type_class").hide();

			// untick select job type
			jQuery("#select_job_type").val('');
			jQuery(".trr_chk:visible").prop("checked",false);
			show_bottom_functions();
			
		} 

	});

	// select job type show/hide toggle
	jQuery("#sel_agency_job_chk").change(function(){

		var is_ticked = jQuery(this).prop("checked");

		if( is_ticked == true ){
			jQuery(".select_agency_jobs_class").show();
		}else{
			jQuery(".select_agency_jobs_class").hide();

			// untick agency jobs
			jQuery("#select_agency_jobs").val('');
			jQuery(".trr_chk:visible").prop("checked",false);
			show_bottom_functions();

		} 

	});

	// add key show/hide toggle
	jQuery("#add_key_btn").click(function(){

		jQuery("#add_key_div").toggle();

	});

	// add supplier show/hide toggle
	jQuery("#add_supplier_btn").click(function(){

		jQuery("#add_supplier_div").toggle();

	});

	
	// region filter script
	jQuery("#region_filter_parent_div").on('keyup click',"#region_filter",function(){

		var region_filter = jQuery(this).val().trim().toLowerCase();

		if( region_filter != '' ){
						
			// default
			jQuery("#region_filter_div").show();
			hide_close_un_selected_items();
			
			jQuery(".sub_region_ms_lbl").each(function(){

				var sub_region_lbl_dom = jQuery(this);
				var sub_region = sub_region_lbl_dom.text().trim().toLowerCase();

				var sub_region_div_chk_dom = sub_region_lbl_dom.parents(".sub_region_div_chk:first");
				var region_div_chk_dom = sub_region_div_chk_dom.parents(".region_div_chk:first");
				var state_div_chk_dom = region_div_chk_dom.parents(".state_div_chk:first");
				
				var position = sub_region.search(region_filter);				
				
				if( position != -1 ){ // found
					
					state_div_chk_dom.show();
					region_div_chk_dom.show();
					sub_region_div_chk_dom.show();

				}

			});

		}else{

			jQuery("#region_filter_div").show();
			jQuery("#region_filter_div .state_div_chk").show();
			hide_close_un_selected_items();

		}			

	});

	// hide when clicking outside script
	jQuery(document).mouseup(function (e){

		var container = jQuery("#region_filter_div");
		if (!container.is(e.target) // if the target of the click isn't the container...
			&& container.has(e.target).length === 0) {
			container.hide();
		}

	});	

	jQuery(".state_ms").change(function(){

		var state_ms_dom = jQuery(this);
		var is_state_ms_ticked = state_ms_dom.prop("checked");	
		var state_div_chk_dom = state_ms_dom.parents(".state_div_chk:first");		

		if( is_state_ms_ticked == true ){
			state_div_chk_dom.find(".region_div_chk").show();
		}else{
			state_div_chk_dom.find(".region_div_chk").hide();
		}

	});
	
	jQuery("#region_filter_div").on("change",".region_ms",function(){

		var region_ms_dom = jQuery(this);
		var is_region_ms_ticked = region_ms_dom.prop("checked");	
		var region_div_chk_dom = region_ms_dom.parents(".region_div_chk:first");	

		if( is_region_ms_ticked == true ){ // ticked

			region_div_chk_dom.find(".sub_region_div .sub_region_ms").prop("checked",true);
			region_div_chk_dom.find(".sub_region_div .sub_region_ms").each(function(){

				var sub_region_ms_dom =  jQuery(this);	
				sub_region_tick(sub_region_ms_dom);					

			});

			region_div_chk_dom.find(".sub_region_div_chk").show();

		}else{ // untick

			region_div_chk_dom.find(".sub_region_div .sub_region_ms").prop("checked",false);
			region_div_chk_dom.find(".sub_region_div .sub_region_ms").each(function(){

				var sub_region_ms_dom =  jQuery(this);		
				var sub_region_ms =  sub_region_ms_dom.val();

				var index = selected_sub_region_arr.indexOf(sub_region_ms);
				if (index !== -1) {

					// remove sub region ID from array
					selected_sub_region_arr.splice(index, 1);

					// remove tag
					jQuery(".sub_region_tag_btn_"+sub_region_ms).remove();

				}

			});

			console.log(selected_sub_region_arr);

			region_div_chk_dom.find(".sub_region_div_chk").hide();			

		}

	});

	jQuery("#region_filter_div").on("click",".sub_region_ms",function(){

		var sub_region_ms_dom =  jQuery(this);	
		sub_region_tick(sub_region_ms_dom);

	});

	jQuery("#sub_region_tag_div").on("click",".sub_region_tag",function(){

		jQuery(this).remove();

	});

	// form validation
	jQuery("#jform").submit(function(){

		var date = jQuery("#date").val();
		var assigned_tech = jQuery("#assigned_tech").val();
		var start_point = jQuery("#start_point").val();
		var end_point = jQuery("#end_point").val();		
		var tr_already_exist = jQuery("#tr_already_exist").val();

		var error = '';

		if( date == ""){
			error += "Date is required\n";
		}

		if( assigned_tech == "" ){
			error += "Technician is required\n";
		}

		if( start_point == "" ){
			error += "Start point is required\n";
		}

		if( end_point == "" ){
			error += "End Point is required\n";
		}

		<?php
		// this validation only runs during tech run creation, not during update
		if( $has_tech_run == false ){ ?>

			if( tr_already_exist == 1 ){
				error += "This tech run already exist\n";
			}

		<?php
		}
		?>		

		if( error != "" ){			
			swal('',error,'error');
			return false;
		}else{			
			return true;
		}

	});

	// auto-select accomodation and call agent script
	jQuery("#assigned_tech").change(function(){

		var assigned_tech = jQuery("#assigned_tech").val();

		if( assigned_tech > 0 ){

			jQuery('#load-screen').show(); 
			jQuery.ajax({
				type: "POST",
				url: "/tech_run/get_accomodation_and_booking_staff",
				dataType: 'json',
				data: {
					assigned_tech: assigned_tech
				}
			}).done(function( ret ){

				jQuery('#load-screen').hide(); 

				var accomodation = parseInt(ret.accomodation);
				var call_agent = parseInt(ret.call_agent);

				jQuery("#booking_staff").val(call_agent);
				jQuery("#start_point").val(accomodation);
				jQuery("#end_point").val(accomodation);

			});

		}		

	});

	<?php
	// this validation only runs during tech run creation, not during update
	if( $has_tech_run == false ){ ?>

		// tech run already exist check
		jQuery("#date, #assigned_tech").change(function(){

			var date = jQuery("#date").val();
			var assigned_tech = jQuery("#assigned_tech").val();

			if( date != "" && assigned_tech > 0 ){

				jQuery("#load-screen").show();
				jQuery.ajax({
					type: "POST",
					url: "/tech_run/already_exist",
					data: {
						date: date,
						assigned_tech: assigned_tech
					}
				}).done(function( ret ){

					jQuery("#load-screen").hide();
					
					var tr_count = parseInt(ret);
					
					if( tr_count > 0 ){

						jQuery("#tr_already_exist").val(1);					
						swal('','This tech run already exist','error');

					}else{

						jQuery("#tr_already_exist").val(0);

					}

				});

			}

		});

	<?php
	}
	?>			

	// remove map routes
	jQuery("#delete_btn").click(function(){

		swal({
			title: "Warning!",
			text: "Are you sure you want to delete this tech run?",
			type: "warning",						
			showCancelButton: true,
			confirmButtonClass: "btn-success",
			confirmButtonText: "Yes, Continue",
			cancelButtonClass: "btn-danger",
			cancelButtonText: "No, Cancel!",
			closeOnConfirm: true,
			showLoaderOnConfirm: true,
			closeOnCancel: true
		},
		function(isConfirm) {

			if (isConfirm) {							  
				
				jQuery("#load-screen").show();
				window.location='/tech_run/delete/?tr_id=<?php echo $tr_id; ?>';			

			}

		});		

	});

	// tab remember selected script
	jQuery(".nav-link").click(function(){

		var nav_link_dom = jQuery(this);
		var tab_id = nav_link_dom.attr("id");
		localStorage.setItem('str_curren_tab', tab_id);

	});

	// select uncoloured
	jQuery("#select_uncoloured_chk").change(function(){

		var is_ticked = jQuery(this).prop("checked");

		if( is_ticked == true ){

			jQuery(".NoColor:visible").each(function(){
				jQuery(this).find(".trr_chk:visible").prop("checked",true);
			});

		}else{

			jQuery(".NoColor:visible").each(function(){
				jQuery(this).find(".trr_chk:visible").prop("checked",false);
			});

		}
		
		show_bottom_functions();

	});

	// select holiday/short term rental
	jQuery("#select_holiday_rent_chk").change(function(){

		var is_ticked = jQuery(this).prop("checked");

		if( is_ticked == true ){

			jQuery(".jrow_holiday_rental:visible").each(function(){
				jQuery(this).find(".trr_chk:visible").prop("checked",true);
			});

		}else{

			jQuery(".jrow_holiday_rental:visible").each(function(){
				jQuery(this).find(".trr_chk:visible").prop("checked",false);
			});

		}

		show_bottom_functions();

	});

	// select escalate job
	jQuery("#sel_esc_jobs_chk").change(function(){

		var is_ticked = jQuery(this).prop("checked");

		if( is_ticked == true ){

			jQuery(".jrow_escalate_jobs:visible").each(function(){
				jQuery(this).find(".trr_chk:visible").prop("checked",true);
			});

		}else{

			jQuery(".jrow_escalate_jobs:visible").each(function(){
				jQuery(this).find(".trr_chk:visible").prop("checked",false);
			});

		}

		show_bottom_functions();

	});


	// select escalate job
	jQuery("#sel_no_tenant_det_chk").change(function(){

		var is_ticked = jQuery(this).prop("checked");

		if( is_ticked == true ){

			jQuery(".no_tenants:visible").each(function(){
				jQuery(this).find(".trr_chk:visible").prop("checked",true);
			});

		}else{

			jQuery(".no_tenants:visible").each(function(){
				jQuery(this).find(".trr_chk:visible").prop("checked",false);
			});

		}

		show_bottom_functions();

	});


	// select job type
	jQuery("#select_job_type_btn").click(function(){

		var select_job_type = jQuery("#select_job_type").val();

		// untick by default
		jQuery(".trr_chk:visible").prop("checked",false);

		jQuery("input.job_type").each(function(){

			var job_type_dom = jQuery(this);
			var job_type = job_type_dom.val();
			var parent_td = job_type_dom.parents("td.chk_col");

			if( job_type == select_job_type ){

				parent_td.find(".trr_chk:visible").prop("checked",true);

			}			

		});

		show_bottom_functions();

	});


	// select job type
	jQuery("#select_agency_jobs_btn").click(function(){

		var select_agency_jobs = jQuery("#select_agency_jobs").val();

		// untick by default
		jQuery(".trr_chk:visible").prop("checked",false);

		jQuery("input.agency_id").each(function(){

			var agency_id_dom = jQuery(this);
			var agency_id = agency_id_dom.val();
			var parent_td = agency_id_dom.parents("td.chk_col");

			if( agency_id == select_agency_jobs ){

				parent_td.find(".trr_chk:visible").prop("checked",true);

			}			

		});

		show_bottom_functions();

	});

	// hide function
	jQuery("#hide_btn").click(function(){

		var tr_id = '<?php echo $tr_id; ?>';
		var trr_id_arr = [];
		var isBooked = false;

		jQuery(".trr_chk:checked:visible").each(function(){

			var trr_chk_dom = jQuery(this);
			var trr_id = trr_chk_dom.val();
			if( trr_id > 0 ){
				trr_id_arr.push(trr_id);
			}
			

			var jt = trr_chk_dom.parents("tr:first").find(".job_type").val();
			if( jt == "Booked" ){
				isBooked = true;
			}

		});

		if( isBooked == true ){

			swal('',"Booked jobs can't be hidden",'error');

		}else{

			if( trr_id_arr.length > 0 ){

				swal({
					html: true,
					title: "Warning!",
					text: "Are you sure you want to <b>Hide</b> all selected items?",
					type: "warning",						
					showCancelButton: true,
					confirmButtonClass: "btn-success",
					confirmButtonText: "Yes, Continue",
					cancelButtonClass: "btn-danger",
					cancelButtonText: "No, Cancel!",
					closeOnConfirm: true,
					showLoaderOnConfirm: true,
					closeOnCancel: true
				},
				function(isConfirm) {

					if (isConfirm) {							  
						
						$('#load-screen').show(); 
						jQuery.ajax({
							type: "POST",
							url: "/tech_run/hide_tech_run_rows",
							data: {
								tr_id: tr_id,
								trr_id_arr: trr_id_arr,
								operation: 'hide'
							}
						}).done(function( ret ){

							$('#load-screen').hide(); 
							location.reload();						

						});				

					}

				});	

			}			

		}

	});



	// Escalate jobs
	jQuery("#escalate_jobs_btn").click(function(){

		var job_id_arr = [];

		jQuery(".trr_chk:checked:visible").each(function(){

			var trr_chk_dom = jQuery(this);
			var parent_td = trr_chk_dom.parents("td.chk_col");
			var job_id = parent_td.find('.job_id').val();
			
			if( job_id > 0 ){
				job_id_arr.push(job_id);
			}
			

		});

		if( job_id_arr.length > 0 ){

			swal({
				html: true,
				title: "Warning!",
				text: "Are you sure you want to <b>Escalate</b> all selected items?",
				type: "warning",						
				showCancelButton: true,
				confirmButtonClass: "btn-success",
				confirmButtonText: "Yes, Continue",
				cancelButtonClass: "btn-danger",
				cancelButtonText: "No, Cancel!",
				closeOnConfirm: true,
				showLoaderOnConfirm: true,
				closeOnCancel: true
			},
			function(isConfirm) {

				if (isConfirm) {							  
					
					$('#load-screen').show(); 
					jQuery.ajax({
						type: "POST",
						url: "/tech_run/escalate_jobs",
						data: {
							job_id_arr: job_id_arr
						}
					}).done(function( ret ){

						$('#load-screen').hide(); 
						location.reload();						

					});				

				}

			});	

		}		

	});



	// assign color
	jQuery("#btn_assign_color").click(function(){

		var tr_id = '<?php echo $tr_id; ?>';
		var trr_id_arr = [];
		var trr_hl_color = jQuery("#row_highlight_color").val();
		var error = '';

		jQuery(".trr_chk:checked:visible").each(function(){

			var trr_chk_dom = jQuery(this);
			var trr_id = trr_chk_dom.val();
			if( trr_id > 0 ){
				trr_id_arr.push(trr_id);
			}

		});

		if( trr_hl_color == '' ){
			error += "Please select a colour\n";
		}

		if( error != '' ){

			swal('',error,'error');
			
		}else{

			if( trr_id_arr.length > 0 ){

				swal({
					html: true,
					title: "Warning!",
					text: "Are you sure you want to <b>Assign Color</b> all selected items?",
					type: "warning",						
					showCancelButton: true,
					confirmButtonClass: "btn-success",
					confirmButtonText: "Yes, Continue",
					cancelButtonClass: "btn-danger",
					cancelButtonText: "No, Cancel!",
					closeOnConfirm: true,
					showLoaderOnConfirm: true,
					closeOnCancel: true
				},
				function(isConfirm) {

					if (isConfirm) {							  
						
						$('#load-screen').show(); 
						jQuery.ajax({
							type: "POST",
							url: "/tech_run/highlight_row",
							data: {
								tr_id: tr_id,
								trr_id_arr: trr_id_arr,
								trr_hl_color: trr_hl_color
							}
						}).done(function( ret ){

							$('#load-screen').hide();
							location.reload();						

						});				

					}

				});	

			}

		}		

	});


	// remove color
	jQuery("#btn_remove_color").click(function(){

		var tr_id = '<?php echo $tr_id; ?>';
		var trr_id_arr = [];
		var trr_hl_color = jQuery("#row_highlight_color").val();
		var error = '';

		jQuery(".trr_chk:checked:visible").each(function(){

			var trr_chk_dom = jQuery(this);
			var trr_id = trr_chk_dom.val();
			if( trr_id > 0 ){
				trr_id_arr.push(trr_id);
			}

		});

		if( trr_id_arr.length > 0 ){

			swal({
				html: true,
				title: "Warning!",
				text: "Are you sure you want to <b>Remove Color</b> to all selected items?",
				type: "warning",						
				showCancelButton: true,
				confirmButtonClass: "btn-success",
				confirmButtonText: "Yes, Continue",
				cancelButtonClass: "btn-danger",
				cancelButtonText: "No, Cancel!",
				closeOnConfirm: true,
				showLoaderOnConfirm: true,
				closeOnCancel: true
			},
			function(isConfirm) {

				if (isConfirm) {							  
					
					$('#load-screen').show(); 
					jQuery.ajax({
						type: "POST",
						url: "/tech_run/remove_color",
						data: {
							tr_id: tr_id,
							trr_id_arr: trr_id_arr
						}
					}).done(function( ret ){

						$('#load-screen').hide();
						location.reload();						

					});				

				}

			});	

		}		

	});


	// Change Technician	
	jQuery("#change_tech_update_btn").click(function(){

		
		var tr_id = '<?php echo $tr_id; ?>';
		var trr_id_arr = [];		
		var error = '';
		var has_not_booked = false;

		var change_tech = jQuery("#change_tech_dp").val();
		
		// checkbox loop
		jQuery(".trr_chk:checked:visible").each(function(){

			var trr_chk_dom = jQuery(this);
			var trr_id = trr_chk_dom.val();

			var parent_td = trr_chk_dom.parents("td.chk_col");
			var parent_tr = trr_chk_dom.parents("tr.tech_run_row_tr:first");
			
			var row_id_type = parent_td.find('.row_id_type').val();
			var job_status = parent_td.find('.job_status').val();			
			
			if( row_id_type == 'job_id' && job_status == "Booked"  ){

				if( trr_id > 0 ){
					trr_id_arr.push(trr_id);
				}
				
			}else{

				has_not_booked = true;
				parent_tr.addClass('bg-warning');
				
			}

		});

		if( change_tech == '' ){
			error += "Please Pick Technician to update to.\n";
		}

		if( has_not_booked == true ){
			error += 'Row highlighted as yellow are jobs that are not "booked" so it cannot proceed with the change tech, please untick them or update job as "booked" refresh and then try again.\n';
		}

		if( error !='' ){
			swal('',error,'error');
		}else{

			if( trr_id_arr.length > 0 ){

				swal({
					html: true,
					title: "Warning!",
					text: "Are you sure you want to <b>Change Tech</b> to all selected items?",
					type: "warning",						
					showCancelButton: true,
					confirmButtonClass: "btn-success",
					confirmButtonText: "Yes, Continue",
					cancelButtonClass: "btn-danger",
					cancelButtonText: "No, Cancel!",
					closeOnConfirm: true,
					showLoaderOnConfirm: true,
					closeOnCancel: true
				},
				function(isConfirm) {

					if (isConfirm) {							  
						
						$('#load-screen').show(); 
						jQuery.ajax({
							type: "POST",
							url: "/tech_run/change_tech",
							data: {
								tr_id: tr_id,
								trr_id_arr: trr_id_arr,
								change_tech: change_tech
							}
						}).done(function( ret ){

							$('#load-screen').hide();
							location.reload();						

						});				

					}

				});	

			}

		}				

	});


	// assign DK
	jQuery("#assign_dk_btn").click(function(){

		var job_id_arr = [];
		var assigned_tech = '<?php echo $tech_run_row->assigned_tech; ?>';
		var date = '<?php echo $tech_run_row->date; ?>';
		var agency_no_dk_arr = [];
		var prop_no_dk_arr = [];

		jQuery(".trr_chk:checked:visible").each(function(){

			var trr_chk_dom = jQuery(this);
			var trr_id = trr_chk_dom.val();

			var parent_td = trr_chk_dom.parents("td.chk_col");
			var parent_tr = trr_chk_dom.parents("tr.tech_run_row_tr:first");

			var job_id = parent_td.find('.job_id').val();		
			var row_id_type = parent_td.find('.row_id_type').val();
			var prop_no_dk = parent_td.find('.prop_no_dk').val();
			var agency_no_dk = parent_td.find('.agency_no_dk').val();			
			var prop_address = parent_td.find('.prop_address').val();
			var agency_name = parent_td.find('.agency_name').val();
			var no_dk = false;
			
			
			if( row_id_type == 'job_id' && job_id > 0  ){					
				
				// property does not allow DK
				if( prop_no_dk == 1 ){

					prop_no_dk_arr.push(prop_address);
					no_dk = true;

				}
				
				// agency does not allow DK
				if( agency_no_dk == 0 ){ 
				
					agency_no_dk_arr.push(agency_name);
					no_dk = true;

				}

				
				if( no_dk == true ){
					
					parent_tr.addClass('bg-warning');

				}else{

					job_id_arr.push(job_id);

				}
				
				
			}								

		});

		var prop_no_dk_arr_unique = [...new Set(prop_no_dk_arr)];
		var agency_no_dk_arr_unique = [...new Set(agency_no_dk_arr)];

		if( prop_no_dk_arr_unique.length > 0 || agency_no_dk_arr_unique.length > 0 ){

			var error_txt = "Cannot proceed to process door knocks because these properties/agencies that are highlighted yellow does not allow it: \n";

			// property no DK
			if( prop_no_dk_arr_unique.length > 0 ){

				error_txt += "\nProperties: \n";
				prop_no_dk_arr_unique.forEach(function(prop){
					error_txt += prop+"\n";
				});

			}			
			
			// agency no DK
			if( prop_no_dk_arr_unique.length > 0 ){

				error_txt += "\nAgencies: \n";
				agency_no_dk_arr_unique.forEach(function(agency){				
					error_txt += agency+"\n";				
				});

			}
			
			swal('',error_txt,'error');

		}else{

			if(  job_id_arr.length > 0 ){

				swal({
					html: true,
					title: "Warning!",
					text: "Are you sure you want to <b>Door Knocks</b> all selected items?",
					type: "warning",						
					showCancelButton: true,
					confirmButtonClass: "btn-success",
					confirmButtonText: "Yes, Continue",
					cancelButtonClass: "btn-danger",
					cancelButtonText: "No, Cancel!",
					closeOnConfirm: true,
					showLoaderOnConfirm: true,
					closeOnCancel: true
				},
				function(isConfirm) {

					if (isConfirm) {							  
						
						$('#load-screen').show(); 
						jQuery.ajax({
							type: "POST",
							url: "/tech_run/assign_dk",
							data: {
								job_id_arr: job_id_arr,
								assigned_tech: assigned_tech,
								date: date
							}
						}).done(function( ret ){

							$('#load-screen').hide(); 
							location.reload();						

						});				

					}

				});	

			}

		}				

	});


	// Mark Tech Sick
	jQuery("#mark_tech_sick").click(function(){

		var job_id_arr = [];

		jQuery(".trr_chk:checked:visible").each(function(){

			var trr_chk_dom = jQuery(this);
			var parent_td = trr_chk_dom.parents("td.chk_col");
			var job_id = parent_td.find('.job_id').val();
			
			if( job_id > 0 ){
				job_id_arr.push(job_id);
			}			

		});

		if( job_id_arr.length > 0 ){

			swal({
				html: true,
				title: "Warning!",
				text: "Are you sure you want to <b>Mark Tech Sick</b> all selected items?",
				type: "warning",						
				showCancelButton: true,
				confirmButtonClass: "btn-success",
				confirmButtonText: "Yes, Continue",
				cancelButtonClass: "btn-danger",
				cancelButtonText: "No, Cancel!",
				closeOnConfirm: true,
				showLoaderOnConfirm: true,
				closeOnCancel: true
			},
			function(isConfirm) {

				if (isConfirm) {							  
					
					$('#load-screen').show(); 
					jQuery.ajax({
						type: "POST",
						url: "/tech_run/mark_tech_sick",
						data: {
							job_id_arr: job_id_arr
						}
					}).done(function( ret ){

						$('#load-screen').hide(); 
						location.reload();						

					});				

				}

			});	

		}		

	});


	// colour table
	jQuery(".ct_time, .ct_jobs, .ct_no_keys_chk").change(function(){

		var obj = jQuery(this);
		update_tech_run_color_table(obj)

	});


	// save notes
	jQuery("#notes").change(function(){

		var tr_id = '<?php echo $tr_id; ?>';
		var notes = jQuery(this).val();
		var notes_ts_div = jQuery("#notes_timestamp_div");

		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			url: "/tech_run/update_notes",
			dataType: 'json',
			data: {
				tr_id: tr_id,
				notes: notes
			}
		}).done(function( ret ){

			jQuery("#load-screen").hide();
			notes_ts_div.find('#updates_by').html(ret.notes_updated_by);
			notes_ts_div.find('#updated_ts').html(ret.notes_updated_ts);

		});

	});


	// sort by suburb 
	jQuery("#sort").change(function(){

		var tr_id = '<?php echo $tr_id; ?>';
		var sort = jQuery(this).val();
		var sort_by_txt = '';

		if( sort == 1 ){
			sort_by_txt = 'Colour';
		}else if( sort == 2 ){
			sort_by_txt = 'Street';
		}else if( sort == 3 ){
			sort_by_txt = 'Suburb';
		}	

		if( sort > 0 && tr_id > 0 ){

			swal({
				html: true,
				title: "Warning!",
				text: "Are you sure you want to sort list via <b>"+sort_by_txt+"</b>?",
				type: "warning",						
				showCancelButton: true,
				confirmButtonClass: "btn-success",
				confirmButtonText: "Yes, Continue",
				cancelButtonClass: "btn-danger",
				cancelButtonText: "No, Cancel!",
				closeOnConfirm: true,
				showLoaderOnConfirm: true,
				closeOnCancel: true
			},
			function(isConfirm) {

				if (isConfirm) {							  
					
					jQuery("#load-screen").show();
					jQuery.ajax({
						type: "POST",
						url: "/tech_run/sort",
						data: {
							tr_id: tr_id,
							sort_by: sort
						}
					}).done(function( ret ){
						
						jQuery("#load-screen").hide();
						location.reload();

					});	

				}

			});

		}			

	});


	// check calendar entry
	jQuery("#assigned_tech").change(function(){

		var assigned_tech = jQuery(this).val();
		var date = jQuery("#date").val();

		if( date!='' ){

			// invoke ajax
			jQuery("#load-screen").show();
			jQuery.ajax({
				type: "POST",
				url: "/tech_run/get_existing_calendar",
				dataType: 'json',
				data: {
					assigned_tech: assigned_tech,
					date: date
				}
			}).done(function( ret ){

				jQuery("#load-screen").hide();
				if( parseInt(ret.calendar_id) > 0 ){

					jQuery("#calendar_id").val(ret.calendar_id);
					jQuery("#calendar").val(ret.region);

				}

			});

		}


	});

	<?php
	// show only if tech run exist
	if( $has_tech_run == true ){ ?>

		// add key
		jQuery("#add_key_submit_btn").click(function(){

			var tr_id = '<?php echo $tr_id; ?>';
			var keys_agency = jQuery("#keys_agency").val();
			var error = "";

			if( keys_agency == "" ){
				error += "Agency is required\n";
			}

			if(error!=""){
				swal('',error,'error');
			}else{

				jQuery("#load-screen").show();
				jQuery.ajax({
					type: "POST",
					url: "/tech_run/add_key",
					data: {
						tr_id: tr_id,
						keys_agency: keys_agency,
						assigned_tech: '<?php echo $tech_run_row->assigned_tech; ?>',
						date: '<?php echo $tech_run_row->date; ?>'
					}
				}).done(function( ret ){

					jQuery("#load-screen").hide();
					location.reload();

				});

			}

		});

	<?php
	}
	?>	


	jQuery("#add_supplier_submit_btn").click(function(){

		var tr_id = '<?php echo $tr_id; ?>';
		var supplier = jQuery("#supplier").val();
		var error = "";

		if( supplier=="" ){
			error += "Supplier is required";
		}

		if(error!=""){
			swal('',error,'error');
		}else{

			jQuery("#load-screen").show();
			jQuery.ajax({
				type: "POST",
				url: "/tech_run/add_supplier",
				data: {
					tr_id: tr_id,
					supplier: supplier
				}
			}).done(function( ret ){
				
				jQuery("#load-screen").hide();
				location.reload();

			});

		}

	});


	jQuery(".run_status").click(function(){

		var tr_id = '<?php echo $tr_id; ?>';
		var assigned_tech = '<?php echo $tech_run_row->assigned_tech; ?>';
		var date = '<?php echo $tech_run_row->date; ?>';
		var booking_staff = jQuery("#booking_staff").val();

		var run_status_dom = jQuery(this);
		var tech_run_field = run_status_dom.attr("data-tech_run-field");
		var update_to = ( run_status_dom.hasClass('btn-success') == true )?0:1;
		var run_status_name = run_status_dom.text();

		var mark_text = ( update_to == 1 )?'mark':'unmark';


		swal({
				html: true,
				title: "Warning!",
				text: "This will "+mark_text+" run status as '"+run_status_name+"', proceed?",
				type: "warning",						
				showCancelButton: true,
				confirmButtonClass: "btn-success",
				confirmButtonText: "Yes, Continue",
				cancelButtonClass: "btn-danger",
				cancelButtonText: "No, Cancel!",
				closeOnConfirm: true,
				showLoaderOnConfirm: true,
				closeOnCancel: true
			},
			function(isConfirm) {

				if (isConfirm) {							  
					
					jQuery("#load-screen").show();
					jQuery.ajax({
						type: "POST",
						url: "/tech_run/status_update",
						data: {
							tr_id: tr_id,
							tech_run_field: tech_run_field,
							update_to: update_to,
							run_status_name: run_status_name,
							assigned_tech: assigned_tech,
							date: date,
							booking_staff: booking_staff
						}
					}).done(function( ret ){
						
						jQuery("#load-screen").hide();
						location.reload();

					});

				}

			});

		

	});


	// remove keys
	jQuery("#remove_keys_btn").click(function(){

		var tr_id = '<?php echo $tr_id; ?>';
		var trr_id_arr = [];
		var trk_id_arr = [];
		var has_not_key_ticked = false;

		jQuery(".trr_chk:checked:visible").each(function(){

			var trr_chk_dom = jQuery(this);
			var parent_tr = trr_chk_dom.parents("tr:first");

			var trr_id = trr_chk_dom.val();			
			var trk_id = parent_tr.find('.trk_id').val();
			var row_type = trr_chk_dom.attr("data-row-type");
			

			if( row_type == 'key' ){ // key
				
				if( trr_id > 0 ){
					trr_id_arr.push(trr_id);
				}	
				
				if( trk_id > 0 ){
					trk_id_arr.push(trk_id);
				}

			}else{ // not key

				has_not_key_ticked = true;	
				parent_tr.addClass('bg-warning');

			}
			

		});

		
		if( has_not_key_ticked == true ){
			swal('','Row higlighted yellow are not keys, please untick them','error');
		}else{

			if( trr_id_arr.length > 0 ){

				swal({
					html: true,
					title: "Warning!",
					text: "Are you sure you want to remove selected keys?",
					type: "warning",						
					showCancelButton: true,
					confirmButtonClass: "btn-success",
					confirmButtonText: "Yes, Continue",
					cancelButtonClass: "btn-danger",
					cancelButtonText: "No, Cancel!",
					closeOnConfirm: true,
					showLoaderOnConfirm: true,
					closeOnCancel: true
				},
				function(isConfirm) {

					if (isConfirm) {							  
						
						$('#load-screen').show(); 
						jQuery.ajax({
							type: "POST",
							url: "/tech_run/remove_keys",
							data: {
								tr_id: tr_id,
								trr_id_arr: trr_id_arr,
								trk_id_arr: trk_id_arr
							}
						}).done(function( ret ){

							$('#load-screen').hide(); 
							location.reload();						

						});				

					}

				});	

			}

		}			

	});


	// remove supplier
	jQuery("#remove_suppliers_btn").click(function(){

		var tr_id = '<?php echo $tr_id; ?>';
		var trr_id_arr = [];
		var trs_id_arr = [];
		var has_not_supplier_ticked = false;

		jQuery(".trr_chk:checked:visible").each(function(){

			var trr_chk_dom = jQuery(this);
			var parent_tr = trr_chk_dom.parents("tr:first");

			var trr_id = trr_chk_dom.val();			
			var trs_id = parent_tr.find('.trs_id').val();
			var row_type = trr_chk_dom.attr("data-row-type");
			

			if( row_type == 'supplier' ){ // supplier
				
				if( trr_id > 0 ){
					trr_id_arr.push(trr_id);
				}	
				
				if( trs_id > 0 ){
					trs_id_arr.push(trs_id);
				}

			}else{ // not key

				has_not_supplier_ticked = true;	
				parent_tr.addClass('bg-warning');

			}
			

		});

		
		if( has_not_supplier_ticked == true ){
			swal('','Row higlighted yellow are not supplier, please untick them','error');
		}else{

			if( trr_id_arr.length > 0 ){

				swal({
					html: true,
					title: "Warning!",
					text: "Are you sure you want to remove selected suppliers?",
					type: "warning",						
					showCancelButton: true,
					confirmButtonClass: "btn-success",
					confirmButtonText: "Yes, Continue",
					cancelButtonClass: "btn-danger",
					cancelButtonText: "No, Cancel!",
					closeOnConfirm: true,
					showLoaderOnConfirm: true,
					closeOnCancel: true
				},
				function(isConfirm) {

					if (isConfirm) {							  
						
						$('#load-screen').show(); 
						jQuery.ajax({
							type: "POST",
							url: "/tech_run/remove_supplier",
							data: {
								tr_id: tr_id,
								trr_id_arr: trr_id_arr,
								trs_id_arr: trs_id_arr
							}
						}).done(function( ret ){

							$('#load-screen').hide(); 
							location.reload();						

						});				

					}

				});	

			}

		}			

	});

	
	// invoke table DND
	jQuery("#tbl_maps").tableDnD({

		onDrop: function(table, row) {

			var job_id = jQuery.tableDnD.serialize({
				'serializeRegexp': null
			});

			jQuery("#load-screen").show();
			jQuery.ajax({
				type: "GET",
				url: "/tech_run/ajax_sort_tech_run/?tr_id=<?php echo $this->input->get_post('tr_id'); ?>&"+job_id
			}).done(function( ret ){

				jQuery("#load-screen").hide();

			});

		}

	});

	// hidden jobs toggle
	jQuery("#hidden_jobs_toggle_btn").click(function(){

		var tr_id = '<?php echo $tr_id; ?>';

		var btn_dom = jQuery(this);
		var show_hidden = ( btn_dom.hasClass("btn-secondary") == true )?1:0;

		swal({

			html: true,
			title: "Warning!",
			text: "This will show hidden jobs, continue?",
			type: "warning",						
			showCancelButton: true,
			confirmButtonClass: "btn-success",
			confirmButtonText: "Yes, Continue",
			cancelButtonClass: "btn-danger",
			cancelButtonText: "No, Cancel!",
			closeOnConfirm: true,
			showLoaderOnConfirm: true,
			closeOnCancel: true
		},
		function(isConfirm) {

			if (isConfirm) {							  
				
				$('#load-screen').show(); 
				jQuery.ajax({
					type: "POST",
					url: "/tech_run/hidden_jobs_toggle",
					data: {
						tr_id: tr_id,
						show_hidden: show_hidden
					}
				}).done(function( ret ){

					$('#load-screen').hide(); 
					location.reload();						

				});				

			}

		});	

	});



	// EN script
	jQuery("#en_btn").click(function(){

		var btn_dom = jQuery(this);
		var orig_btn_text = 'Entry Notice';

		if( btn_dom.text() == orig_btn_text ){

			jQuery(".EN_show_elem").show();
			jQuery(".hide_chk_on_en").hide();
			btn_dom.text('Cancel');

		}else{

			jQuery(".EN_show_elem").hide();
			jQuery(".hide_chk_on_en").show();
			btn_dom.text(orig_btn_text);

		}
		

	});

	<?php
	// show only if tech run exist
	if( $has_tech_run == true ){ ?> 

		// issue EN
		jQuery("#issue_en_btn").click(function(){

			var btn_dom = jQuery(this);
			
			var str_tech = '<?php echo $tech_run_row->assigned_tech; ?>';
			var str_tech_name = '<?php echo $this->system_model->formatStaffName($tech_run_row->tech_sa_fname,$tech_run_row->tech_sa_lname); ?>';
			var str_date = '<?php echo $tech_run_row->date; ?>';
			var trr_id_arr = [];
			var en_time_arr = [];

			jQuery(".trr_chk:checked:visible").each(function(){

				var trr_chk_dom = jQuery(this);
				var parent_tr = trr_chk_dom.parents("tr:first");

				var trr_id = trr_chk_dom.val();
				var en_time = parent_tr.find(".en_time").val();

				if( trr_id > 0 ){

					trr_id_arr.push(trr_id);					
					en_time_arr.push(en_time);
					
				}

			});

			if( trr_id_arr.length > 0 ){

				swal({

					html: true,
					title: "Warning!",
					text: "This will issue Entry Notice on selected jobs, continue?",
					type: "warning",						
					showCancelButton: true,
					confirmButtonClass: "btn-success",
					confirmButtonText: "Yes, Continue",
					cancelButtonClass: "btn-danger",
					cancelButtonText: "No, Cancel!",
					closeOnConfirm: true,
					showLoaderOnConfirm: true,
					closeOnCancel: true
					},
					function(isConfirm) {

					if (isConfirm) {							  
						
						$('#load-screen').show(); 
						jQuery.ajax({
							type: "POST",
							url: "/tech_run/issue_en",
							data: {						
								'trr_id_arr': trr_id_arr,
								'str_tech': str_tech,
								'str_tech_name': str_tech_name,
								'str_date': str_date,
								'en_time_arr': en_time_arr
							}
						}).done(function( ret ){

							$('#load-screen').hide(); 
							swal({
								title: "Success!",
								text: "Entry Notice has been Issued!",
								type: "success",
								confirmButtonClass: "btn-success",
								showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
								timer: <?php echo $this->config->item('timer') ?>
							});
							location.reload();						

						});				

					}

				});	

			}

		});

	<?php	
	}
	?>	


	// hidden jobs toggle
	jQuery(".jt_display_filter").change(function(){

		var tr_id = '<?php echo $tr_id; ?>';

		var btn_dom = jQuery(this);
		var is_ticked = ( btn_dom.prop("checked") == true )?1:0;
		var job_type = btn_dom.val();

		$('#load-screen').show(); 
		jQuery.ajax({
			type: "POST",
			url: "/tech_run/job_type_toggle",
			data: {
				tr_id: tr_id,
				is_ticked: is_ticked,
				job_type: job_type
			}
		}).done(function( ret ){

			$('#load-screen').hide(); 
			location.reload();						

		});	

	});		

	// hidden jobs toggle
	jQuery("#working_hours").change(function(){

		var tr_id = '<?php echo $tr_id; ?>';

		var btn_dom = jQuery(this);
		var working_hours = btn_dom.val();

		$('#load-screen').show(); 
		jQuery.ajax({
			type: "POST",
			url: "/tech_run/update_working_hours",
			data: {
				tr_id: tr_id,
				working_hours: working_hours
			}
		}).done(function( ret ){

			$('#load-screen').hide(); 
			location.reload();						

		});	

	});	

	// get distance
	jQuery("#btn_display_distance").click(function(){

		address_index = 1;
		tot_time = 0;
		tot_dis = 0;
		orig_dur = 0;

		jQuery(".p_address").each(function(index){

			var dom = jQuery(this);
			var row = dom.parents("tr:first");

			//var orig = dom.parents("tr:first").prev('tr').find('.address').html();
			var p_address = dom.text();
			//console.log('p_address :'+p_address);
			var a_address = row.find('.a_address').text();
			//console.log('a_address :'+a_address);
					
			setTimeout(function(){

				// dunno how to pass variables on callback functions
				calculateDistances(p_address,a_address,row);

			}, 1000);					

		});

	});

	// hidden jobs toggle
	jQuery(".time_of_day").change(function(){

		var time_of_day_dom = jQuery(this);
		var parent_tr = time_of_day_dom.parents("tr.tech_run_row_tr:first");

		var job_id = parent_tr.find('.job_id').val();
		var time_of_day = time_of_day_dom.val();

		jQuery('#load-screen').show(); 
		jQuery.ajax({
			type: "POST",
			url: "/tech_run/update_time_of_day",
			data: {
				job_id: job_id,
				time_of_day: time_of_day
			}
		}).done(function( ret ){

			jQuery('#load-screen').hide(); 
			//location.reload();						

		});	

	});	
	
});
</script>