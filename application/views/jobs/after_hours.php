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
			'link' => "/jobs/after_hours/"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);

	
	$export_links_params_arr = array(
		'job_type_filter' => $this->input->get_post('job_type_filter'),
		'service_filter' => $this->input->get_post('service_filter'),
		'state_filter' =>  $this->input->get_post('state_filter'),
		'agency_filter' => $this->input->get_post('agency_filter'),
		'date_filter' => $this->input->get_post('date_filter'),
		'search_filter' => $this->input->get_post('search_filter'),
		'sub_region_ms' => $this->input->get_post('sub_region_ms')
	);
	$export_link_params = '/jobs/after_hours/?export=1&'.http_build_query($export_links_params_arr);
	?>

	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">
			<?php
		$form_attr = array(
			'id' => 'jform'
		);
		echo form_open('/jobs/after_hours',$form_attr);
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
							<select id="job_type_filter" name="job_type_filter" class="form-control field_g2">
								<option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>
						</div>

						<div class="col-mdd-3">
							<label for="service_select">Service</label>
							<select id="service_filter" name="service_filter" class="form-control field_g2">
								<option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>
						</div>

						<div class="col-mdd-3">
							<label for="service_select"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></label>
							<select id="state_filter" name="state_filter" class="form-control field_g2">
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
							<input name="date_filter" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text" placeholder="ALL" value="<?php echo $this->input->get_post('date_filter'); ?>">
						</div>

						<div class="col-md-2">
							<label for="phrase_select">Phrase</label>
							<input type="text" name="search_filter" class="form-control" placeholder="ALL" value="<?php echo $this->input->get_post('search_filter'); ?>" />
						</div>

						<div class="col-md-2">
							<label for="search">Electrician Only(EO)</label>
							<div class="checkbox" style="margin:0;">
								<input name="show_is_eo" type="checkbox" id="show_is_eo" value="1" <?php echo ( $this->input->get_post('show_is_eo') == 1 )?'checked':null; ?> />
								<label for="show_is_eo"></label>
							</div>
						</div>

						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<button type="submit" class="btn btn-inline">Search</button>
						</div>
						
					</div>

				</div>

				 <div class="col-lg-2 columns">
                    <section class="proj-page-section float-right">
                        <div class="proj-page-attach">
                            <i class="fa fa-file-excel-o"></i>
                            <p class="name"><?php echo $title; ?></p>
                            <p>
								<a href="<?php echo $export_link_params ?>">
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
							<th>Date</th>
							<th>Job Type</th>
							<th>Age</th>
							<th>Service</th>
							<th>Address</th>
							<th><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></th>
							<th><?php echo $this->gherxlib->getDynamicRegion($this->config->item('country')); ?></th>
							<th>Agency</th>
                            <th>Job#</th>
                            <th>Comments</th>
							<th>Preferred Time</th>
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
							if($lists->num_rows()>0){
						foreach($lists->result_array() as $list_item): 		

							$getRegion = $this->system_model->getRegion_v2($list_item['p_postcode'])->row();
						?>
						<tr class="tbl_list_tr">
							<td>
							<?php
								echo ($this->system_model->isDateNotEmpty($list_item['j_date']))?date('d/m/Y', strtotime($list_item['j_date'])):'';
							?>
							</td>
							<td>
                            <?php echo $this->gherxlib->getJobTypeAbbrv($list_item['j_type']); ?>
							</td>
							<td>
							<?php
									echo $this->gherxlib->getAge($list_item['j_created']);
								?>
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
							<?php
							/**
							 <a href="<?php echo base_url('/properties/view_property_details')."/".$row->prop_id?>"><?php echo $row->p_address_1." ".$row->p_address_2." ".$row->p_address_3; ?></a>
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
							<td>
							<?php 
									echo $getRegion->subregion_name;
							?>
							</td>
							
							<td class="<?php echo ( $list_item['priority'] > 0 )?'j_bold':null; ?>">
							<?php echo $list_item['agency_name']." ".( ( $list_item['priority'] > 0 )?' ('.$list_item['abbreviation'].')':null ); ?>
							</td>
                            <td>
							<?php
							/*
							 echo '<a href="'.base_url("/jobs/view_job_details/{$row->jid}").'">'.$row->jid.'</a>' 
							 */
							 ?>
							 <?php
							  echo $this->gherxlib->crmLink('vjd',$list_item['jid'],$list_item['jid']);
							 ?>
							   </td>
							<td>
								<?php echo $list_item['j_comments'] ?>
							</td>
							<td>
								<?php echo $list_item['preferred_time'] ?>
							</td>
							<td>
								<div class="checkbox">
									<input class="chk_job" name="chk_job[]" type="checkbox" id="check-<?php echo $list_item["jid"] ?>" data-jobid="<?php echo $list_item["jid"]; ?>" value="<?php echo $list_item['jid']; ?>">
									<label for="check-<?php echo $list_item["jid"] ?>">&nbsp;</label>
								</div>
								<input type="hidden" class="job_type" value="<?php echo $list_item['j_type']; ?>" />
                                <input type="hidden" class="is_eo" value="<?php echo $list_item['is_eo']; ?>" />
							</td>
						</tr>
						<?php endforeach;
							}else{
								echo "<tr><td colspan='11'>No Data</td></tr>";
							}
						?>
					</tbody>

				</table>
				<div id="mbm_box" class="text-right">
							<div class="gbox_main">
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
								<div class="gbox">
									<input name="assign_date" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="assign_date" type="text" placeholder="Date" >
								</div>
								<div class="gbox">
									<button id="assign_btn" type="button" class="btn">Assign</button>
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

	<h4>After Hours</h4>
	<p>
		This page will show all jobs that have requested the job to be done outside of 7am-3pm Monday to Friday. 
		For a job to appear here, you need to tick 'Outside of Tech Hours' on the 'Job Details' page.
	</p>
	<pre>
<code>SELECT `j`.`id` AS `jid`, `j`.`status` AS `j_status`, `j`.`service` AS `j_service`, `j`.`created` AS `j_created`, `j`.`date` AS `j_date`, `j`.`start_date`, `j`.`due_date`, `j`.`comments` AS `j_comments`, `j`.`job_price` AS `j_price`, `j`.`job_type` AS `j_type`, `j`.`property_vacant`, `j`.`urgent_job`, `j`.`job_reason_id`, `j`.`preferred_time`, `j`.`is_eo`, `j`.`job_type` AS `j_type`, `p`.`property_id` AS `prop_id`, `p`.`address_1` AS `p_address_1`, `p`.`address_2` AS `p_address_2`, `p`.`address_3` AS `p_address_3`, `p`.`state` AS `p_state`, `p`.`postcode` AS `p_postcode`, `p`.`comments` AS `p_comments`, `a`.`agency_id` AS `a_id`, `a`.`agency_name` AS `agency_name`, `a`.`phone` AS `a_phone`, `a`.`address_1` AS `a_address_1`, `a`.`address_2` AS `a_address_2`, `a`.`address_3` AS `a_address_3`, `a`.`state` AS `a_state`, `a`.`postcode` AS `a_postcode`, `a`.`trust_account_software`, `a`.`tas_connected`, `ajt`.`id` AS `ajt_id`, `ajt`.`type` AS `ajt_type`
FROM `jobs` AS `j`
LEFT JOIN `property` AS `p` ON j.`property_id` = p.`property_id`
LEFT JOIN `agency` AS `a` ON  p.`agency_id` = a.`agency_id`
LEFT JOIN `job_type` AS `jt` ON j.`job_type` = jt.`job_type`
LEFT JOIN `alarm_job_type` AS `ajt` ON j.`service` = ajt.`id`
WHERE `j`.`del_job` = 0
AND `p`.`deleted` = 0
AND `a`.`status` = 'active'
AND `a`.`country_id` = <?php echo COUNTRY ?> 
AND `j`.`out_of_tech_hours` = 1
AND (`j`.`status` = 'To Be Booked' OR `j`.`status` = 'Escalate' OR `j`.`status` = 'Booked' )
ORDER BY `a`.`agency_name` ASC
LIMIT 50</code>
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

		//validations
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
						date: date,
						page_type: "after_hours"
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
					setTimeout(function(){ window.location='/jobs/after_hours'; }, <?php echo $this->config->item('timer') ?>);
						
				});

			}				

		}		
				
	});



}) //doc ready end

</script>