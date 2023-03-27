<style>
	.col-mdd-3{
		max-width: 12.1%;
	}
	.a_link.asc{
		top:3px;
	}
	.a_link.desc{
		top:-3px;
	}
	#assign_date{
		width:120px;
	}
	#mbm_box .gbox_main {
		float: none;
	}
	#mbm_box .gbox_main .gbox {
		float: right;		
	}
	#on_hold_div{
		display: none;
	}

	.blueRowBg{
		background-color: #9dd3f7;
	}
	code{line-height:1.3;}
</style>
<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/jobs/to_be_booked"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">
			<?php
		$form_attr = array(
			'id' => 'jform'
		);
		echo form_open('/jobs/to_be_booked',$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-lg-10 col-md-12 columns">
					<div class="row">

						<div class="col-mdd-3">
							<label>Agency</label>
							<select id="agency_filter" name="agency_filter" class="form-control">
								<option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>
						</div>

						<div class="col-mdd-3">
							<label>Job Type</label>
							<select id="job_type_filter" name="job_type_filter" class="form-control">
								<option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>
						</div>

						<div class="col-mdd-3">
							<label>Service</label>
							<select id="service_filter" name="service_filter" class="form-control">
								<option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>
						</div>

						<div class="col-mdd-3">
							<label for="state"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></label>
							<select id="state_filter" name="state_filter" class="form-control">
								<option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>
						</div>

						<div class="col-mdd-3">
						
							<div class="fl-left region_filter_main_div">
								<label>	
								<?php 
									$defaultCountry = $this->config->item('country');
									echo $this->customlib->getDynamicRegionViaCountry($defaultCountry); 
								?>:
								</label>
								<input type="text" name="region_filter_state" id='region_filter_state' class="form-control region_filter_state" placeholder="ALL" readonly="readonly" />
								
								<div id="region_dp_div" class="box-typical region_dp_div">
								
									<div class="region_dp_header">										
									</div>
									
									<div class="region_dp_body">								
									</div>
									
								</div>	
								
							</div>
							
						</div>

						<div class="col-mdd-3">
							<label for="date_select">Date</label>
							<input name="date_filter" placeholder="ALL" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text" value="<?php echo $this->input->get_post('date_filter'); ?>">
						</div>
						<div class="col-mdd-3">
                            <label for="ht_select">Agency Priority</label><span>
                            <select id="agency_priority_filter" name="agency_priority_filter" class="form-control field_g2">
                                <option value="" <?php echo ($this->input->get_post('agency_priority_filter') == "") ? "selected" : ""; ?>>ALL</option>
                                <option value="0" <?php echo ($this->input->get_post('agency_priority_filter') === "0") ? "selected" : ""; ?>>Regular</option>
                                <option value="1" <?php echo ($this->input->get_post('agency_priority_filter') === "1") ? "selected" : ""; ?>>HT</option>
                                <option value="2" <?php echo ($this->input->get_post('agency_priority_filter') === "2") ? "selected" : ""; ?>>VIP</option>
                                <option value="3" <?php echo ($this->input->get_post('agency_priority_filter') === "3") ? "selected" : ""; ?>>HWC</option>
                            </select>
                            <div class="mini_loader"></div>
                        </div>

						<div class="col-mdd-3">
							<label for="search">Phrase</label>
							<input type="text" placeholder="ALL" name="search_filter" class="form-control"  value="<?php echo $this->input->get_post('search_filter'); ?>" />
						</div>

						<div class="col-mdd-3">
							<label for="search">E/0</label>
							<div class="checkbox" style="margin:0;">
								<input name="show_is_eo" type="checkbox" id="show_is_eo" value="1" <?php echo ( $this->input->get_post('show_is_eo') == 1 )?'checked':null; ?> />
								<label for="show_is_eo"></label>
							</div>
						</div>

						<div class="col-mdd-3">
							<label for="search">240v</label>
							<div class="checkbox" style="margin:0;">
								<input name="updated_to_240v_rebook" type="checkbox" id="updated_to_240v_rebook" value="1" <?php echo ( $this->input->get_post('updated_to_240v_rebook') == 1 )?'checked':null; ?> />
								<label for="updated_to_240v_rebook"></label>
							</div>
						</div>

						<div class="col-mdd-3">
							<label for="search">Sales</label>
							<div class="checkbox" style="margin:0;">
								<input name="is_sales" type="checkbox" id="is_sales" value="1" <?php echo ( $this->input->get_post('is_sales') == 1 )?'checked':null; ?> />
								<label for="is_sales"></label>
							</div>
						</div>

						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<button type="submit" class="btn btn-inline">Search</button>
						</div>
						
					</div>

				</div>
                
                <!-- DL ICONS START -->
                <?php 
                $date = ($this->input->get_post('date')!="")?date('Y-m-d',$this->input->get_post('date')):NULL;
                 ?>
			    <div class="col-lg-2 col-md-12 columns">
                    <section class="proj-page-section float-right">
                        <div class="proj-page-attach">
                            <i class="fa fa-file-excel-o"></i>
                            <p class="name"><?php echo $title; ?></p>
                            <p>
								<a href="<?php echo $export_link; ?>" target="blank">
									Export
								</a>
                            </p>
                        </div>
                    </section>
				</div>
				<!-- DL ICONS END -->
                                    
                </div>
                </form>
			</div>
		
	</header>

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<table class="table table-hover main-table">
					<thead>
						<tr>
							<th style="width:75px;">Date&nbsp;
							<a data-toggle="tooltip" class="a_link <?php echo $sort ?>" href="<?php echo "/jobs/to_be_booked/?sort_header=1&order_by=j.date&sort={$toggle_sort}&".http_build_query($header_link_params); ?>">
								<em class="fa fa-sort-<?php echo $sort; ?>"></em>
							</a>
							</th>
							<th style="width:75px;">Deadline&nbsp;
								<a data-toggle="tooltip" class="a_link <?php echo $sort ?>" href="<?php echo "/jobs/to_be_booked/?sort_header=1&order_by=deadline&sort={$toggle_sort}&".http_build_query($header_link_params); ?>">
									<em class="fa fa-sort-<?php echo $sort; ?>"></em>
								</a>
							</th>
							<th>Job Type</th>
							<th>Age&nbsp;
								<a data-toggle="tooltip" class="a_link <?php echo $sort ?>" href="<?php echo "/jobs/to_be_booked/?sort_header=1&order_by=age&sort={$toggle_sort}&".http_build_query($header_link_params); ?>">
									<em class="fa fa-sort-<?php echo $sort; ?>"></em>
								</a>
							</th>
							<th>Service</th>
							<th>Price</th>
							<th>Address</th>
							<th><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></th>
							<th><?php echo $this->gherxlib->getDynamicRegion($this->config->item('country')); ?></th>
							<th>Agency</th>
							<th>Job#</th>
                            <th>Last Contact</th>
                            <th>
								End Date
								<a data-toggle="tooltip" class="a_link <?php echo $sort ?>" href="<?php echo "/jobs/to_be_booked/?sort_header=1&order_by=j.due_date&sort={$toggle_sort}&".http_build_query($header_link_params); ?>">
									<em class="fa fa-sort-<?php echo $sort; ?>"></em>
								</a>
							</th>
							<th>Vacant</th>
							<th>DK</th>
                            <th class="check_all_td">
								<div class="checkbox" style="margin:0;">
									<input name="chk_all" type="checkbox" id="check-all">
									<label for="check-all">&nbsp;</label>
								</div>
							</th>
						</tr>
					</thead>

					<tbody>
                        <?php 
                        if(count($jobs)>0){
                        foreach($jobs as $list_item){
							
							
							$row_color = '';
                            // if alarms 240v or 240vli are expired
                            if( $this->system_model->findExpired240vAlarm($list_item['jid']) == true || $list_item['is_eo'] == 1 ){	
								$row_color = "redRowBg";			
                            }
                            
							// urgent jobs							
                            if($list_item['urgent_job']==1){
                                $row_color = "greenRowBg";
                            }
                            
                            // jobs not completed
                            if($list_item['job_reason_id']>0){
                                $row_color = "yellowRowBg";
                            }

							if($list_item['property_jobs_count'] == 1){
                                $row_color = "blueRowBg";
                            }

						?>
						<tr class="tbl_list_tr <?php echo $row_color; ?>">
							<td>
							<?php
							echo ($this->system_model->isDateNotEmpty($list_item['j_date']))?date('d/m/Y', strtotime($list_item['j_date'])):'';
							?>
							</td>
							<td><?php echo ( $list_item['deadline'] > 0 )?$list_item['deadline']:'<span class="text-danger" data-toggle="tooltip" title="Overdue" >+'.($list_item['deadline']*-1).'</span>'; ?></td>
							<td>
							<?php echo $this->gherxlib->getJobTypeAbbrv($list_item['j_type']); ?>
							</td>
                            <td>
                                <?php echo $list_item['age']; ?>
                            </td>
							<td>								
								<?php
								// display icons
								$job_icons_params = array(
									'service_type' => $list_item['j_service'],
									'job_type' => $list_item['j_type'],
									'sevice_type_name' => $list_item['ajt_type'],
									'is_eo' => $list_item['is_eo'],
								);
								echo $this->system_model->display_job_icons($job_icons_params);
								?>
							</td>
							<td>
                            <?php echo '$'.number_format($this->system_model->price_ex_gst($list_item['j_price']),2); ?>
							</td>
							<td>
							<!-- <a href="<?php echo base_url('/properties/view_property_details')."/".$list_item["prop_id"]?>"><?php echo $list_item['p_address_1']." ".$list_item['p_address_2']." ".$list_item['p_address_3']; ?></a>	</td> -->
							<?php 
								$prop_address = $list_item['p_address_1']." ".$list_item['p_address_2'].", ".$list_item['p_address_3'];
								echo $this->gherxlib->crmLink('vpd',$list_item['prop_id'],$prop_address);
							?>
							<td>
							<?php echo $list_item['p_state']; ?>
							</td>
                            <td>
								<?php
									echo $list_item['region']['subregion_name'];
								?>
							</td>
							<td class="<?php echo ( $list_item['priority'] > 0 )?'j_bold':null; ?>">
							<?php echo $list_item['agency_name']." ".( ( $list_item['priority'] > 0 )?' ('.$list_item['abbreviation'].')':null ); ?>
                            </td>
                          
                            <!-- <td>
							<?php 
							echo '<a href="/jobs/view_job_details/'.$list_item["jid"].'">'.$list_item["jid"].'</a>';
							?>
                            </td> -->
                            <td>
							<?php echo $this->gherxlib->crmLink('vjd',$list_item['jid'],$list_item['jid']);?></td>
                            <td>
								<?php
									//$lc_sql = $this->gherxlib->getLastContact($list_item['jid']);	
									//$lc = $lc_sql->row_array();
									//echo ($this->system_model->isDateNotEmpty($lc['eventdate']))?date("d/m/Y",strtotime($lc['eventdate'])):'';

									$old_log = $list_item['last_contact_old'];
									$new_log = $list_item['last_contact'];

									$tmp_newlog = explode(" ", $new_log);
									$latest_log = $tmp_newlog[0];
									
									if($old_log > $latest_log){
										echo date("d/m/Y",strtotime($old_log));
										//echo $old_log;
									}
									else{
										echo date("d/m/Y",strtotime($latest_log));
										//echo $latest_log;
									}
									//echo ($this->system_model->isDateNotEmpty($list_item['last_contact']))?date("d/m/Y",strtotime($list_item['last_contact'])):''
								?>
							</td>
                            <td>
							<?php
								echo ( ( $list_item['j_type']=='Change of Tenancy' || $list_item['j_type']=='Lease Renewal' ) && $this->system_model->isDateNotEmpty($list_item['due_date']) )?'<span '.( ( $list_item['due_date'] >= date('Y-m-d') )?'style="color:red;"':'' ).'>'.date('d/m/Y',strtotime($list_item['due_date'])).'</span>':'';
							?>
							</td>
							<td><?php echo ($list_item['property_vacant']==1)? "YES":''; ?></td>
							<td>
								<?php 
								// property no DK, Short Term Rental and agency allow dk preference.
								echo ( $list_item['no_dk']==1 || $list_item['holiday_rental'] == 1 || ( is_numeric($list_item['allow_dk']) && $list_item['allow_dk'] == 0 ) )?'NO':''; 								
								?>								
							</td>
                            <td>

								<input type="hidden" class="is_dk_allowed" value="<?php echo $list_item['allow_dk']; ?>" />
								<input type="hidden" class="agency_id" value="<?php echo $list_item['a_id']; ?>" />
								<input type="hidden" class="agency_name" value="<?php echo $list_item['agency_name']; ?>" />
								<input type="hidden" class="job_type" value="<?php echo $list_item['j_type']; ?>" />
								<input type="hidden" class="no_dk" value="<?php echo $list_item['no_dk']; ?>" />
								<input type="hidden" class="holiday_rental" value="<?php echo $list_item['holiday_rental']; ?>" />
								<input type="hidden" class="is_eo" value="<?php echo $list_item['is_eo']; ?>" />

								<div class="checkbox">
									<input class="chk_job" name="chk_job[]" type="checkbox" id="check-<?php echo $list_item["jid"] ?>" data-jobid="<?php echo $list_item["jid"]; ?>" value="<?php echo $list_item['jid']; ?>">
									<label for="check-<?php echo $list_item["jid"] ?>">&nbsp;</label>
								</div>
							</td>
                            
						</tr>
						<?php 
						}
						
                        }else{
                            echo "<tr><td colspan='15'>No Data</td></tr>";
                        }
                        ?>
					</tbody>

				</table>
                <div id="mbm_box" class="text-right">
				
					
							<div class="gbox_main">


								<div id="main_function_buttons_div">

									<div class="gbox">
										<button id="btn_place_jobs_on_hold" type="button" class="btn">Place Jobs On Hold</button>
									</div>	

									<div class="gbox">
										<button id="btn_os_call_over" type="button" class="btn btn-danger">OS Call Over</button>
									</div>

									<div class="gbox">
										<button id="btn_create_rebook" type="button" class="btn btn-danger">Rebook</button>
									</div>

									<div class="gbox">
										<button id="btn_assign_dk" type="button" class="btn btn-danger">Doorknock</button>
									</div>
									
									<div class="gbox">
										<button id="assign_btn" type="button" class="btn">Assign</button>
									</div>																																		

									<div class="gbox">
										<input name="assign_date" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="assign_date" type="text" placeholder="Date" >
									</div>

									<div class="gbox">
										<select id="maps_tech" class="form-control">
											<option value="">Please select Tech</option>
											<?php
												$params = array(
													'sel_query'=> "sa.StaffID, sa.FirstName, sa.LastName, sa.is_electrician, sa.active as sa_active",
												);
												$tech = $this->system_model->getTech($params);
												foreach($tech->result_array() as $row){
											?>
												<option value="<?php echo $row['StaffID'] ?>" data-isElectrician="<?php echo $row['is_electrician']; ?>">
												<?php 
													echo $this->system_model->formatStaffName($row['FirstName'],$row['LastName']).( ( $row['is_electrician'] == 1 )?' [E]':null ); 
												?>
												</option>
											<?php
												}
											?>
										</select>
									</div>


								</div>


								
								<div id="on_hold_div">

									<div class="gbox">
										<button id="btn_place_on_hold_cancel" type="button" class="btn">Cancel</button>										
									</div>

									<div class="gbox">
										<button id="btn_place_on_hold_save" type="button" class="btn">Save</button>										
									</div>

									<div class="gbox">										
										<input name="on_hold_end" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="on_hold_end" type="text" placeholder="End Date" >
									</div>

									<div class="gbox">
										<input name="on_hold_start" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="on_hold_start" type="text" placeholder="Start Date" >										
									</div>								

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

	<h4><?php echo $title; ?></h4>
	<p>This page shows jobs that are not yet completed and waiting to be booked</p>
	<ul>
		<li class="mb-2"><span class="redRowBg">Red</span> = Alarm is 240v and EXPIRED. Technician needs to be an electrician</li>
		<li><span class="blueRowBg">Blue</span> = First Visit</li>
	</ul>
<br/>
<pre>
<code><?php echo $sql_query; ?></code>
</pre>
</div>
<!-- Fancybox END -->


<script type="text/javascript">

// agency
function run_ajax_agency_filter(){
var json_data = <?php echo $agency_filter_json; ?>;
var searched_val = '<?php echo $this->input->get_post('agency_filter'); ?>';

jQuery('#agency_filter').next('.mini_loader').show();
jQuery.ajax({
	type: "POST",
		url: "/sys/header_filters",
		data: { 
			rf_class: 'jobs',
			header_filter_type: 'agency',
			json_data: json_data,
			searched_val: searched_val
		}
	}).done(function( ret ){	
		jQuery('#agency_filter').next('.mini_loader').hide();
		$('#agency_filter').append(ret);
	});
			
}

// job type	
function run_ajax_job_filter(){

	var json_data = <?php echo $job_type_filter_json; ?>;
	var searched_val = '<?php echo $this->input->get_post('job_type_filter'); ?>';

	jQuery('#job_type_filter').next('.mini_loader').show();
	jQuery.ajax({
		type: "POST",
			url: "/sys/header_filters",
			data: { 
				rf_class: 'jobs',
				header_filter_type: 'job_type',
				json_data: json_data,
				searched_val: searched_val
			}
		}).done(function( ret ){	
			jQuery('#job_type_filter').next('.mini_loader').hide();
			jQuery('#job_type_filter').append(ret);
		});
				
}

// service
function run_ajax_service_filter(){

var json_data = <?php echo $service_filter_json; ?>;
var searched_val = '<?php echo $this->input->get_post('service_filter'); ?>';

jQuery('#service_filter').next('.mini_loader').show();
jQuery.ajax({
	type: "POST",
		url: "/sys/header_filters",
		data: { 
			rf_class: 'jobs',
			header_filter_type: 'service',
			json_data: json_data,
			searched_val: searched_val
		}
	}).done(function( ret ){	
		jQuery('#service_filter').next('.mini_loader').hide();
		$('#service_filter').append(ret);
	});
			
}

// state
function run_ajax_state_filter(){

var json_data = <?php echo $state_filter_json; ?>;
var searched_val = '<?php echo $this->input->get_post('state_filter'); ?>';

jQuery('#state_filter').next('.mini_loader').show();
jQuery.ajax({
	type: "POST",
		url: "/sys/header_filters",
		data: { 
			rf_class: 'jobs',
			header_filter_type: 'state',
			json_data: json_data,
			searched_val: searched_val
		}
	}).done(function( ret ){	
		jQuery('#state_filter').next('.mini_loader').hide();
		$('#state_filter').append(ret);
	});
			
}

jQuery(document).ready(function(){

	// run headler filter ajax
	run_ajax_job_filter();
	run_ajax_service_filter();
	run_ajax_state_filter();
	run_ajax_agency_filter();
		
	// region filter selection, cant trigger without the timeout, dunno why :( 
	<?php
	if( !empty($this->input->get_post('sub_region_ms')) ){ ?>
		setTimeout(function(){ 
			jQuery("#region_filter_state").click();
		 }, 500);		
	<?php
	}
	?>
	
	// region filter click
	jQuery('.region_filter_main_div').on('click','.region_filter_state',function(){
		
		var obj  = jQuery(this);
		var state_chk = obj.prop("checked");
		var region_filter_json = <?php echo $region_filter_json; ?>;
		var state_ms_json = <?php echo $state_ms_json; ?>;
		
		jQuery("#load-screen").show();
		
		jQuery.ajax({
			type: "POST",
			url: "/sys/getRegionFilterState",
			data: { 
				rf_class: 'jobs',
				region_filter_json: region_filter_json
			}
		}).done(function( ret ){
			
			jQuery("#load-screen").hide();
			jQuery(".region_dp_header").html(ret);
			
			// searched
			var state_ms_json_num = state_ms_json.length;
			if( state_ms_json_num > 0 ){				
				for( var i=0; i < state_ms_json_num; i++ ){
					jQuery("#region_dp_div .state_ms[value='"+state_ms_json[i]+"']").click();
				}
			}
			
			
		});
				
	});
	
	// state click
	jQuery('.region_dp_div').on('click','.state_ms',function(){
		
		var obj  = jQuery(this);
		var state = obj.val();
		var state_chk = obj.prop("checked");
		var region_filter_json = <?php echo $region_filter_json; ?>;
		var region_ms_json = <?php echo $region_ms_json; ?>;
		
		if(state_chk==true){
			
			obj.parents(".state_div:first").find(".rf_state_lbl").addClass("rf_select");
			jQuery("#load-screen").show();
			
			jQuery.ajax({
				type: "POST",
				url: "/sys/getMainRegion",
				data: { 
					state: state,
					rf_class: 'jobs',
					region_filter_json: region_filter_json
				}
			}).done(function( ret ){
				
				jQuery("#load-screen").hide();
				obj.parents(".state_div:first").find(".region_div").html(ret);

				// searched
				var region_ms_json_num = region_ms_json.length;
				if( region_ms_json_num > 0 ){				
					for( var i=0; i < region_ms_json_num; i++ ){
						obj.parents(".state_div:first").find(".region_ms[value='"+region_ms_json[i]+"']").click();
					}
				}
				
			});
			
		}else{
			obj.parents(".state_div:first").find(".rf_state_lbl").removeClass("rf_select");
			obj.parents(".state_div:first").find(".region_div").html('');			
		}	
				
	});
	
	
	// region click
	jQuery('.region_dp_div').on('click','.region_ms',function(){
		
		var obj  = jQuery(this);
		var region_id = obj.val();
		var state_chk = obj.prop("checked");
		var region_filter_json = <?php echo $region_filter_json; ?>;
		var sub_region_ms_json = <?php echo $sub_region_ms_json; ?>;
		
		if(state_chk==true){
			
			obj.parents(".region_div_chk:first").find(".rf_region_lbl").addClass("rf_select");
			jQuery("#load-screen").show();
			
			jQuery.ajax({
				type: "POST",
				url: "/sys/getSubRegion",
				data: { 
					region_id: region_id,
					rf_class: 'jobs',
					region_filter_json: region_filter_json
				}
			}).done(function( ret ){
				
				jQuery("#load-screen").hide();
				obj.parents(".region_div_chk:first").find(".sub_region_div").html(ret);

				// searched
				var sub_region_ms_json_num = sub_region_ms_json.length;
				if( sub_region_ms_json_num > 0 ){				
					for( var i=0; i < sub_region_ms_json_num; i++ ){
						obj.parents(".region_div_chk:first").find(".sub_region_ms[value='"+sub_region_ms_json[i]+"']").click();
					}
				}
				
			});
			
			
		}else{
			obj.parents(".region_div_chk:first").find(".rf_region_lbl").removeClass("rf_select");
			obj.parents(".region_div_chk:first").find(".sub_region_div").html('');
		}	
				
	});
	
	// sub region 
	jQuery('.region_dp_div').on('click','.sub_region_ms',function(){
		
		var obj  = jQuery(this);
		var region_id = obj.val();
		var state_chk = obj.prop("checked");
		
		if(state_chk==true){			
			obj.parents(".sub_region_div_chk:first").find(".rf_sub_region_lbl").addClass("rf_select");			
		}else{
			obj.parents(".sub_region_div_chk:first").find(".rf_sub_region_lbl").removeClass("rf_select");
		}	
				
	});

	$('#check-all').on('change',function(){
		var obj = $(this);
		var isChecked = obj.is(':checked');
		var divbutton = $('#mbm_box');
		if(isChecked){
			divbutton.show();
			$('.chk_job').prop('checked',true);
			$("tr.tbl_list_tr").addClass("yello_mark");
		}else{
			divbutton.hide();
			$('.chk_job').prop('checked',false);
			$("tr.tbl_list_tr").removeClass("yello_mark");
		}
	})

	$('.chk_job').on('change',function(){
		var obj = $(this);
		var isLength = $('.chk_job:checked').length;
		var divbutton = $('#mbm_box');

		if(obj.is(':checked')){
			divbutton.show();
			obj.parents('.tbl_list_tr').addClass('yello_mark');
		}else{

			obj.parents('.tbl_list_tr').removeClass('yello_mark');

			if(isLength<=0){
				divbutton.hide();
			}

		}

	})

	// move/assign to maps 
	jQuery("#assign_btn").on('click',function(){
		
		var job_id = new Array();
		var tech_id = jQuery("#maps_tech").val();
		var is_tech_electrician = jQuery("#maps_tech option:selected").attr("data-isElectrician");
		var date = jQuery("#assign_date").val();
		var checkLength = $('.chk_job:checked').length;		
		var for_elec_only = false;

		var error = "";
	
		//push job_id array
		jQuery(".chk_job:checked").each(function(){

			var job_chk_dom = jQuery(this);
			var parents_tr = job_chk_dom.parents("tr:first");
			var job_type = parents_tr.find(".job_type").val();
			var is_eo = parents_tr.find(".is_eo").val();			

			// 240v Rebook Jobs or Electrician Only(EO)		
			if( job_type == '240v Rebook' || is_eo == 1 ){
				for_elec_only = true;
			}
			
			job_id.push(jQuery(this).val());

		});



		// validations
		if(checkLength == 0){
			error += "Please select/tick Job\n";
		}

		if(tech_id==""){
			error += "Tech must not be empty\n";
		}

		if(date==""){
			error += "Date must not be empty\n";
		}

		// 240v Rebook or Electrician Only(EO) check
		if( tech_id > 0 && is_tech_electrician != 1 && for_elec_only == true ){ 		
			error += "Cannot assign 240v Rebook or Electrician Only(EO) job to non Electrician\n";
		}

		if( error != "" ){

			swal('',error,'error');
			return false;
			
		}else{

			if( job_id.length > 0 ){

				$('#load-screen').show(); //show loader
				jQuery.ajax({
					type: "POST",
					url: "/jobs/ajax_move_to_maps",
					data: { 
						job_id: job_id,
						tech_id: tech_id,
						date: date
					}
				}).done(function( ret ){
					$('#load-screen').hide(); //hide loader
					swal({
						title:"Success!",
						text: "Assigned success",
						type: "success",
						showCancelButton: false,
						confirmButtonText: "OK",
						closeOnConfirm: false,
						showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
						timer: <?php echo $this->config->item('timer') ?>

					});
					setTimeout(function(){ window.location='/jobs/to_be_booked'; }, <?php echo $this->config->item('timer') ?>);
						
				});	

			}			

		}		
				
	});


	// assign to DK
	jQuery("#btn_assign_dk").click(function(){
	
		var job_id = new Array();
		var tech_id = jQuery("#maps_tech").val();
		var is_tech_electrician = jQuery("#maps_tech option:selected").attr("data-isElectrician");
		var date = jQuery("#assign_date").val();
		var checkLength = $('.chk_job:checked').length;
		var agency_id_arr = new Array(); 
		var agency_arr = new Array(); 		
		var no_dk_arr = [];
		var holiday_rental_arr = [];
		var for_elec_only = false;

		var error = '';
		
		
		jQuery(".chk_job:checked").each(function(){

			var job_chk_dom = jQuery(this);
			var parents_tr = job_chk_dom.parents("tr:first");

			var job_type = parents_tr.find(".job_type").val();
			var is_eo = parents_tr.find(".is_eo").val();	

			var is_dk_allowed = parents_tr.find(".is_dk_allowed").val();
			if( parseInt(is_dk_allowed) ==1 ){
				job_id.push(job_chk_dom.val());
			}else{
				var agency_id = parents_tr.find(".agency_id").val();
				var agency_name = parents_tr.find(".agency_name").val();
				if(jQuery.inArray(agency_id,agency_id_arr)===-1){
					agency_id_arr.push(agency_id);
					agency_arr.push(agency_name);
				}
			}	

			// 240v Rebook Jobs
			var job_type = parents_tr.find(".job_type").val();
			if( job_type == '240v Rebook' || is_eo == 1 ){
				for_elec_only = true;
			}

			// no DK
			var no_dk = parents_tr.find(".no_dk").val();			
			if( parseInt(no_dk) ==1 ){
				no_dk_arr.push(job_chk_dom.val());
			}	

			// Short Term Rental
			var holiday_rental = parents_tr.find(".holiday_rental").val();			
			if( parseInt(holiday_rental) == 1 ){
				holiday_rental_arr.push(job_chk_dom.val());
			}		
			
		});
		
		
		//validate
		if(checkLength == 0){					
			error += "Please select/tick Job\n";
		}
		if( tech_id=='' ){					
			error += "Tech must not be empty\n";
		}
		if( date=='' ){			
			error += "Date must not be empty\n";
		}

		// 240v Rebook or Electrician Only(EO) check
		if( tech_id > 0 && is_tech_electrician != 1 && for_elec_only == true ){ 		
			error += "Cannot assign 240v Rebook or Electrician Only(EO) job to non Electrician\n";
		}

		if( no_dk_arr.length > 0 ){
			error += "Some Properties has not allowed DK, please unselect them\n";
		}

		if( holiday_rental_arr.length > 0 ){
			error += "Some Properties has Short Term Rental, please unselect them\n";
		}


		if( error !='' ){ // errors

			swal('',error,'error');
			return false;

		}else{

			
			// if job greater than 0
			if( job_id.length>0 ){

				
					if(agency_arr.length>0){ //array has agency that is not allowed DK
						
						var msg = "These agencies are not allowed Dks: \n\n";
						for(var i=0;i<agency_arr.length;i++){
							msg += agency_arr[i]+" \n";
						}
						msg += "\n";
						msg += "Other jobs will be added as DKs \n";
						msg += "Press OK to continue";
						
						swal(
							{
								title: "",
								text: msg,
								type: "warning",
								showCancelButton: true,
								confirmButtonClass: "btn-success",
								confirmButtonText: "OK",
								cancelButtonText: "No, Cancel!",
								closeOnConfirm: false,
								closeOnCancel: true,
							},
							function(isConfirm){
								if(isConfirm){

									swal.close();
									$('#load-screen').show(); //show loader
									
									// continue via ajax request
									jQuery.ajax({
										type: "POST",
										url: '<?php echo base_url(); ?>jobs_ajax/to_be_booked/ajax_to_be_booked_assign_dk',
										dataType: 'json',
										data: { 
											job_id: job_id,
											tech_id: tech_id,
											date: date
										}
									}).done(function( ret ){	
										$('#load-screen').hide(); //hide loader		
										if(ret.status){
											//success popup				
											swal({
													title:"Success!",
													text: "Assigned success",
													type: "success",
													showCancelButton: false,
													confirmButtonText: "OK",
													closeOnConfirm: false,
													showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
													timer: <?php echo $this->config->item('timer') ?>

												});
												setTimeout(function(){ window.location='/jobs/to_be_booked'; }, <?php echo $this->config->item('timer') ?>);
										}else{
											swal('','Server error please contact admin.','error');
										}

									});	
									
								}else{
									swal.close();
								}
								
							}
							
						);
					
				}else{ // array has no agency fo not DK - countinue process
					
						$('#load-screen').show(); //show loader
						jQuery.ajax({
							type: "POST",
							url: '<?php echo base_url(); ?>jobs_ajax/to_be_booked/ajax_to_be_booked_assign_dk',
							dataType: 'json',
							data: { 
								job_id: job_id,
								tech_id: tech_id,
								date: date
							}
						}).done(function( ret ){
							$('#load-screen').hide(); //hide loader		
							if(ret.status){
								//success popup				
								swal({
									title:"Success!",
									text: "Assigned success",
									type: "success",
									showCancelButton: false,
									confirmButtonText: "OK",
									closeOnConfirm: false,
									showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
									timer: <?php echo $this->config->item('timer') ?>

								});	
								setTimeout(function(){ window.location='/jobs/to_be_booked'; }, <?php echo $this->config->item('timer') ?>);
							}else{
								swal('','Server error please contact admin.','error');
							}
						
						});
				}
				
			}else{ // no job id
				swal('','All jobs selected are non DK by agency','error');
			}
			

		}
		
				
	});


	
	// OS Call Over ajax script
	jQuery("#btn_os_call_over").click(function(){
	
		var job_id_arr = new Array();
		var tech_id = jQuery("#maps_tech").val();		
		var date = jQuery("#assign_date").val();	
		var job_chk_dom = jQuery('.chk_job:checked:visible');	
		var job_chk_length = job_chk_dom.length;	

		var error = '';		
		
		// loop through jobs
		job_chk_dom.each(function(){

			var job_id = jQuery(this).val();
			if( job_id > 0 ){
				job_id_arr.push(job_id);
			}			
			
		});		
		
		// validate
		if(job_chk_length == 0){					
			error += "Please select/tick Job\n";
		}
		if( tech_id=='' ){					
			error += "Tech must not be empty\n";
		}
		if( date=='' ){			
			error += "Date must not be empty\n";
		}		

		if( error !='' ){ // errors

			swal('',error,'error');
			return false;

		}else{
			
			$('#load-screen').show(); //show loader
			jQuery.ajax({
				type: "POST",
				url: '/jobs/ajax_update_os_call_over',				
				data: { 
					job_id_arr: job_id_arr,
					tech_id: tech_id,
					date: date
				}
			}).done(function( ret ){

				$('#load-screen').hide(); //hide loader		
				location.reload();
			
			});					

		}
		
				
	});
	



	// REBOOK
	jQuery("#btn_create_rebook").click(function(){
		var job_id = new Array();
		var checkLength = $('.chk_job:checked').length;
		
		//validate
		if(checkLength == 0){
			swal('','Please select/tick Job','error');
			return false;
		}

		jQuery(".chk_job:checked").each(function(){
			job_id.push(jQuery(this).val());
		});

		swal(
			{
				title: "",
				text: "Are you sure you want to continue?",
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
					
					// continue via ajax request
					jQuery.ajax({
						type: "POST",
						url: '<?php echo base_url(); ?>jobs_ajax/to_be_booked/ajax_rebook_script',
						dataType: 'json',
						data: { 
							job_id: job_id,
							is_240v: 0
						}
					}).done(function( ret ){	

							$('#load-screen').hide(); //hide loader		
							
							if(ret.status){
								//success popup				
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

								setTimeout(function(){ window.location='/jobs/to_be_booked'; }, <?php echo $this->config->item('timer') ?>);

							}else{
								swal('','Server error please contact admin.','error');
							}	
						

					});	
					
					
				}else{
					return false;
				}
				
			}
						
		);

		
	});


	jQuery("#btn_place_jobs_on_hold").click(function(){

		jQuery("#main_function_buttons_div").hide();
		jQuery("#on_hold_div").show();

	});

	jQuery("#btn_place_on_hold_cancel").click(function(){

		jQuery("#main_function_buttons_div").show();
		jQuery("#on_hold_div").hide();

	});


	// REBOOK
	jQuery("#btn_place_on_hold_save").click(function(){

		var job_id_arr = [];	
		var on_hold_start = jQuery("#on_hold_start").val();
		var on_hold_end = jQuery("#on_hold_end").val();
		var error = '';			

		jQuery(".chk_job:checked:visible").each(function(){

			var job_id = jQuery(this).val();
			if( job_id > 0 ){

				job_id_arr.push(job_id);

			}			

		});

		// validate
		if( job_id_arr.length == 0 ){
			error += "Please select/tick Job\n";			
		}

		if( on_hold_start == '' ){
			error += "On Hold Start Date is required\n";			
		}

		/*
		if( on_hold_end == '' ){
			error += "On Hold End Date is required\n";			
		}
		*/

		if( error != '' ){ // has error

			swal('',error,'error');
			return false;
					
		}else{ // no error, proceed

			swal(
				{
					title: "",
					text: "Are you sure you want to continue?",
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
						
						// continue via ajax request
						jQuery.ajax({
							type: "POST",
							url: '/jobs/save_multiple_jobs_on_hold',
							data: { 
								job_id_arr: job_id_arr,
								on_hold_start: on_hold_start,
								on_hold_end: on_hold_end								
							}
						}).done(function( ret ){	

							$('#load-screen').hide(); //hide loader																		
							swal({
								title:"Success!",
								text: "Save On Hold Successful!",
								type: "success",
								showCancelButton: false,
								confirmButtonText: "OK",
								closeOnConfirm: false,
								showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
								timer: <?php echo $this->config->item('timer') ?>
							});	

							setTimeout(function(){ window.location='/jobs/to_be_booked'; }, <?php echo $this->config->item('timer') ?>);							

						});	
						
						
					}
					
				}
							
			);

		}		
		
	});


});
</script>