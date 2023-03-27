<style>
	.col-mdd-3{
		max-width:12.5%;
		
	}
</style>
<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/jobs/on_hold"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);


	$export_links_params_arr = array(
		'agency_filter' => $this->input->get_post('agency_filter'),
		'job_type_filter' => $this->input->get_post('job_type_filter'),
		'service_filter' => $this->input->get_post('service_filter'),
		'state_filter' => $this->input->get_post('state_filter'),
		'date_filter' => $this->input->get_post('date_filter'),
		'search' => $this->input->get_post('search_filter'),
		'sub_region_ms' => $this->input->get_post('sub_region_ms'),
		'job_status' => $this->input->post('job_status')
	);
	$export_link_params = '/jobs/on_hold/?export=1&'.http_build_query($export_links_params_arr);
	?>

	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">
			<?php
		$form_attr = array(
			'id' => 'jform'
		);
		echo form_open('/jobs/on-hold',$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-md-10 columns">
					<div class="row">

						<div class="col-mdd-3">
							<label for="agency_select">Agency</label>
							<select id="agency_filter" name="agency_filter"  class="form-control field_g2">
								<option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>
						</div>	

						<div class="col-mdd-3">
							<label for="jobtype_select">Job Type</label>
							<select id="job_type_filter" name="job_type_filter" class="form-control">
								<option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>
						</div>

						<div class="col-mdd-3">
							<label for="service_select">Service</label>
							<select id="service_filter" name="service_filter" class="form-control">
								<option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>
						</div>

						<div class="col-mdd-3">
							<label for="service_select">Status</label>
							
							<select id="job_status" name="job_status" class="form-control">
								<option <?php echo ($this->input->post('job_status')=="On Hold")? 'selected' : NULL; ?> value="On Hold">On Hold</option>
								<option <?php echo ($this->input->post('job_status')=="On Hold - COVID")? 'selected' : NULL; ?>  value="On Hold - COVID">On Hold - COVID</option>
							</select>
							<div class="mini_loader"></div>
						</div>

						<div class="col-mdd-3">
							<label for="service_select"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></label>
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
							<input name="date_filter" placeholder="ALL" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $this->input->get_post('date'); ?>">
						</div>

						<div class="col-mdd-3">
							<label for="phrase_select">Phrase</label>
							<input type="text" placeholder="ALL" name="search_filter" class="form-control"  value="<?php echo $this->input->get_post('search_filter'); ?>" />
						</div>

						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<button type="submit" class="btn btn-inline">Search</button>
						</div>
						
					</div>

				</div>

			
				 <div class="col-lg-2 col-md-12 columns">
				 <button id="btn_run_cron_disabled" style="margin-top:18px;" type="button" class="btn" disabled="disabled">Run Cron</button>
                    <section class="proj-page-section float-right">
                        <div class="proj-page-attach">
                            <i class="fa fa-file-excel-o"></i>
                            <p class="name"><?php echo $title; ?></p>
                            <p>
								<a href="<?php echo $export_link_params ?>" target="blank">
									Export
								</a>
                            </p>
                        </div>
                    </section>
				</div>
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
							<th>Start Date</th>
							<th>Age</th>
							<th><?php echo $this->gherxlib->getDynamicRegion($this->config->item('country')); ?></th>
							<th>Job Type</th>
							<th>Service</th>
							<th>Address</th>
							<th><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></th>
							<th>Agency</th>
							<th>Comments</th>
                            <th>Job#</th>
                            <th>Last Contact</th>
							<th>
								<div class="checkbox" style="margin:0;">
									<input name="chk_all" type="checkbox" id="check-all">
									<label for="check-all">&nbsp;</label>
								</div>
							</th>

						</tr>
					</thead>

					<tbody>
						<?php
						if($lists->num_rows()>0){
						foreach($lists->result_array() as $list_item): 	
						
						$getRegion = $this->system_model->getRegion_v2($list_item['p_postcode'])->row();

						?>

						<?php
							$tdf_start_date = date("Y-m-d",strtotime($list_item['start_date']." -1 days"));		
						
							// yellow
							$row_color = "yellowRowBg";
							
							// white
							if( date("Y-m-d")>=$tdf_start_date ){
								$row_color = "whiteRowBg";
							}
							
							// urgent jobs
							// green
							if($list_item['urgent_job']==1){
								$row_color = "greenRowBg";
							}
							
							// jobs not completed
							// yellow
							if($list_item['job_reason_id']>0){
								$row_color = "yellowRowBg";
							}
						?>
						<tr class="<?php echo $row_color; ?>">
							<td>
							<?php 
							echo ($this->system_model->isDateNotEmpty($list_item['start_date']))?$this->system_model->formatDate($list_item['start_date'],'d/m/Y'):'';
							?>
							</td>
							<td>
								<?php
									echo $this->gherxlib->getAge($list_item['j_created']);
								?>
							</td>
							<td>
								<?php
									echo $getRegion->subregion_name;
								?>
							</td>
							<td>
								<?php echo $this->gherxlib->getJobTypeAbbrv($list_item['j_type']); ?>
							</td>
							<td>								
								<?php
								// display icons
								$job_icons_params = array(
									'service_type' => $list_item['j_service'],
									'job_type' => $list_item['j_type'],
									'sevice_type_name' => $list_item['ajt_type']
								);
								echo $this->system_model->display_job_icons($job_icons_params);
								?>
							</td>
							<td>
							<?php /*
							<a href="<?php echo base_url('/properties/view_property_details')."/".$list_item["prop_id"]?>"><?php echo $list_item['p_address_1']." ".$list_item['p_address_2']." ".$list_item['p_address_3']; ?></a>
							*/
							?>
							<?php
							$prop_address = $list_item['p_address_1']." ".$list_item['p_address_2'].", ".$list_item['p_address_3'];
							echo $this->gherxlib->crmLink('vpd',$list_item['prop_id'],$prop_address);
							?>
							</td>
							<td>
							<?php echo $list_item['p_state']; ?>
							</td>
							<td class="<?php echo ( $list_item['priority'] > 0 )?'j_bold':null; ?>">
							<?php echo $list_item['agency_name']." ".( ( $list_item['priority'] > 0 )?' ('.$list_item['abbreviation'].')':null ); ?>
							</td>
							<td>
								<?php echo $list_item['j_comments'] ?>
                            </td>
                            <td>
							<?php
							/* echo '<a href="'.base_url("/jobs/view_job_details/{$list_item['jid']}").'">'.$list_item['jid'].'</a>' 
							*/
							echo $this->gherxlib->crmLink('vjd',$list_item['jid'],$list_item['jid']);
							?>
                            </td>
							<td>
								<?php
									$lastContact =  $this->gherxlib->getLastContact($list_item['jid'])->row_array();
									echo ($this->system_model->isDateNotEmpty($lastContact['eventdate']))?$this->system_model->formatDate($lastContact['eventdate'],'d/m/Y'):'';
								?>
							</td>
							<td>
								<div class="checkbox">
									<input class="chk_job" name="chk_job[]" type="checkbox" id="check-<?php echo $list_item["jid"] ?>" data-jobid="<?php echo $list_item["jid"]; ?>" value="<?php echo $list_item["jid"]; ?>">
									<label for="check-<?php echo $list_item["jid"] ?>">&nbsp;</label>
								</div>
							</td>
						</tr>
					<?php endforeach;
						}else{
							echo "<tr><td colspan='12'>No Data</td></tr>";
						}
							?>
					</tbody>

				</table>
				<div id="mbm_box" class="text-right">
							<div class="gbox_main">
								<div class="gbox">
									<select class="form-control" id="maps_tech">
										<option value="">Please select Tech</option>
										<?php
											$params = array(
												'sel_query'=> "sa.StaffID, sa.FirstName, sa.LastName, sa.is_electrician, sa.active as sa_active",
											);
											$tech = $this->system_model->getTech($params);
											foreach($tech->result_array() as $row){
										?>
											<option value="<?php echo $row['StaffID'] ?>">
											<?php 
												echo $this->system_model->formatStaffName($row['FirstName'],$row['LastName']).( ( $row['is_electrician'] == 1 )?' [E]':null ); 
											?>
											</option>
										<?php
											}
										?>
									</select>
								</div>
								<div class="gbox">
									<input name="assign_date" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="assign_date" type="text" placeholder="Date" >
								</div>
								<div class="gbox">
									<button id="assign_btn" type="button" class="btn">Assign</button>
								</div>
							</div>
				</div>
			</div>

			<nav class="text-center">
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

	<h4>On Hold</h4>
	<p>
	This page shows any jobs that are approved for service but at a future date. To prevent the job from being booked before an agency wants it completed, it is placed “On Hold” with an allocated date. 1 day prior to the allocated date, the job is automatically removed from “On Hold” and into “To Be Booked”. 
</p>
<pre>
<code><?=$last_query;?></code>
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
		run_ajax_agency_filter();
		run_ajax_job_filter();
		run_ajax_service_filter();
		run_ajax_state_filter();

		
		
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
			}else{
				divbutton.hide();
				$('.chk_job').prop('checked',false);
			}
		})

		$('.chk_job').on('change',function(){
			var obj = $(this);
			var isLength = $('.chk_job:checked').length;
			var divbutton = $('#mbm_box');
			if(isLength>0){
				divbutton.show();
			}else{
				divbutton.hide();
			}
		})

		// move to maps 
		jQuery("#assign_btn").on('click',function(){
			
			var job_id = new Array();
			var tech_id = jQuery("#maps_tech").val();
			var date = jQuery("#assign_date").val();

			if(tech_id==""){
				swal('','Tech must not be empty','error');
				return false;
			}
			if(date==""){
				swal('','Date must not be empty','error');
				return false;
			}
			
			jQuery(".chk_job:checked").each(function(){
				job_id.push(jQuery(this).val());
			});

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
				setTimeout(function(){ window.location='/jobs/on_hold'; }, <?php echo $this->config->item('timer') ?>);	
			});	
					
		});

		// filter search selection

		<?php
		if( $this->input->get_post('jobType') != '' ){ ?>
				jQuery("#job_type_filter").click();

		<?php
		}
		?>

		<?php
		if( $this->input->get_post('service') != '' ){ ?>
				jQuery("#service_filter").click();

		<?php
		}
		?>

		<?php
		if( $this->input->get_post('state') != '' ){ ?>
				jQuery("#state_filter").click();

		<?php
		}
		?>

		//run cron
		$('#btn_run_cron').click(function(){
			swal(
                    {
                        title: "",
                        text: "Are You Sure You Want to Continue?",
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
							swal.close();

							jQuery.ajax({
							type: "POST",
							url: "<?php echo base_url('/cronjobs/cron_on_hold_jobs_move_for_booking_au') ?>",
							dataType: 'json',
							data: { 
								cron_status: cron_status,
								cron_file: cron_file,			
								db_field: db_field
							}
							}).done(function(data){
								
								

							});

                        }
                        
                    }
            	);	
		})


	})
</script>