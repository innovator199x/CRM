<link rel="stylesheet" href="/inc/css/separate/vendor/select2.min.css">
<style>
	.allocate-check-ok{
		position: absolute;
		right: 5px;
		top: 9px;
		font-size: 20px;
		display:none;
	}
	.select2-container--arrow .select2-selection--multiple .select2-selection__choice, .select2-container--default .select2-selection--multiple .select2-selection__choice, .select2-container--white .select2-selection--multiple .select2-selection__choice{
		color: #fff;
		background: #919fa9;
		border: none;
		font-weight: 600;
		font-size: 1rem;
		padding: 0 2rem 0 .5rem;
		height: 26px;
		line-height: 26px;
		position: relative;
	}
	.select2-container--arrow .select2-results__option--highlighted[aria-selected], .select2-container--default .select2-results__option--highlighted[aria-selected], .select2-container--white .select2-results__option--highlighted[aria-selected]{
		color:#00a8ff;
	}
	.select2-container--arrow .select2-selection--multiple, .select2-container--default .select2-selection--multiple, .select2-container--white .select2-selection--multiple{
		border-color: #d8e2e7;
		min-height: 38px;
	}
	.select2-container--default .select2-selection--multiple .select2-selection__rendered{
		box-sizing: border-box;
		list-style: none;
		margin: 0;
		padding: 0 5px;
		width: 100%;
	}
	.select2-container--default.select2-container--focus .select2-selection--multiple{
		border-color:#c5d6de!important;
	}
	#btn_assign_to{margin-top:16px;}
	.select2-selection__choice{
		margin-top:6px!important;
	}
	
</style>
<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/jobs/allocate"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);

	$export_links_params_arr = array(
		'agency_filter' => $this->input->get_post('agency_filter'),
		'job_type_filter' => $this->input->get_post('job_type_filter'),
		'added_by_filter' => $this->input->get_post('added_by_filter'),
		'search_filter' => $this->input->get_post('search_filter'),
		'region_ms' => $this->input->get_post('region_ms'),
		'sub_region_ms' => $this->input->get_post('sub_region_ms')
	);
	$export_link_params = '/jobs/allocate/?export=1&'.http_build_query($export_links_params_arr);
	?>

	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">
			<div class="for-groupss row">
			
				<div class="col-md-8 columns">
				<?php
			$form_attr = array(
				'id' => 'jform'
			);
			echo form_open('/jobs/allocate',$form_attr);
			?>
					<div class="row">
						<div class="col-mdd-3">
							<label for="agency_select">Agency</label>
							<select id="agency_filter" name="agency_filter" class="form-control field_g2">
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
								<label for="jobtype_select">Job Type</label>
								<select id="job_type_filter" name="job_type_filter" class="form-control">
								<option value="">ALL</option>
								</select>
								<div class="mini_loader"></div>
						</div>

						<div class="col-mdd-3">
								<label for="jobtype_select">Added By</label>
								<select id="added_by_filter" name="added_by_filter" class="form-control">
								<option value="">ALL</option>
								<?php 
								$query_added = $this->db->query("
								SELECT
									alloc_by.`FirstName` AS alloc_by_fname, 
									alloc_by.`LastName` AS alloc_by_lname,
									alloc_by.StaffID
								FROM `jobs` AS j INNER JOIN staff_accounts AS alloc_by ON j.`allocated_by` = alloc_by.`StaffID`
								GROUP BY alloc_by.FirstName, alloc_by.LastName
								");
								foreach( $query_added->result() as $added_row ){
								?>
								 <option value="<?php echo $added_row->StaffID; ?>" <?php echo ( $added_row->StaffID == $this->input->get_post('added_by_filter') )?'selected':null; ?>><?php echo $added_row->alloc_by_fname.' '.$added_row->alloc_by_lname; ?></option>
								<?php } ?>
								</select>
						</div>

						<div class="col-mdd-3">
								<label>Hide jobs from</label>
								<select id="allocated_by_filter" name="allocated_by_filter" class="form-control">
									<option value="">Nobody</option>
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

						<div class="col-md-2">
							<label for="search_filter">Phrase</label>
							<input placeholder="ALL" type="text" name="search_filter" class="form-control" value="<?php echo $this->input->get_post('search_filter'); ?>" />
						</div>

						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<button type="submit" class="btn btn-inline">Search</button>
						</div>
						
					</div>
					</form>
				</div>

				<div class="col-lg-4 col-md-12 columns">
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

				
				<div class="col-md-4 columns">

				<div class="row">
					<div class="col-md-9 columns">
					<label>Assigned To:</label>
						<select class="select2 ttmoselect form-control" multiple="multiple" id="ttnimo" name="ttnimo">
							<?php
							//Global Settings
							$globalParams = array('country_id'=>$this->config->item('country'));
							$globalSettings = $this->gherxlib->getGlobalSettings($globalParams)->row();
							$personel_explode = explode(',',$globalSettings->allocate_personnel); //comma separated to array
							//Staff Info
							$params = array(
								'sort_list' => array(
									array(
										'order_by' => 'FirstName',
										'sort' => 'ASC',
									),
								)
							);
							$stafflist = $this->gherxlib->getStaffInfo($params);
							foreach($stafflist->result() as $row):
							?>
								<option value="<?php echo $row->StaffID; ?>" <?php echo (in_array($row->StaffID, $personel_explode))?'selected="selected"':'' ?>>
								<?php echo $this->gherxlib->formatStaffName($row->FirstName, $row->LastName); ?>
								</option>
							<?php 
							endforeach;
							?>
						</select>
					</div>
					<div class="col-md-3 columns"><button class="btn" id="btn_assign_to" type="button">Assign</button></div>
				</div>
					<div>
	
					</div>
				</div>
			</div>
			
		</div>
	</header>

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<table class="table table-hover main-table">
					<thead>
						<tr>
							<th>Date</th>
							<th>Time</th>
							<th>Added By</th>
							<th>Age</th>
							<th>Job Type</th>
							<th>Property Address</th>
							<th><?php echo $this->gherxlib->getDynamicRegion($this->config->item('country')); ?></th>
							<th>Sub Region</th>
							<th>Deadline</th>
							<th>Notes</th>
							<th>Response</th>
						</tr>
					</thead>

					<tbody>
						<?php 
							if($lists->num_rows()>0){
							foreach($lists->result_array() as $row): 	

								$getRegion = $this->system_model->getRegion_v2($row['p_postcode']);
						?>
						<tr>
							<td>
							<?php 
								echo ($this->system_model->isDateNotEmpty($row['allocate_timestamp']))?date('d/m/Y', strtotime($row['allocate_timestamp'])):""; 
							?>
							</td>
							<td>
							<?php 
								echo ($this->system_model->isDateNotEmpty($row['allocate_timestamp']))?date('H:i', strtotime($row['allocate_timestamp'])):""; 
							?>
							</td>
							<td>
							<?php
							//echo $this->gherxlib->getAllocatedBy($row['allocated_by']);
							echo $this->system_model->formatStaffName($row['alloc_by_fname'],$row['alloc_by_lname']);
							?>
							</td>
							<td>
								<?php 	echo $this->gherxlib->getAge($row['j_created']);  ?>
							</td>
							<td>
								<?php
								/*
								echo '<a href="'.base_url("/jobs/view_job_details/{$row['jid']}").'">'.$row['j_type'].'</a>'
								*/
								echo $this->gherxlib->crmLink('vjd',$row['jid'],$row['j_type']);
								?>

							</td>
							<td>
							<?php /*
								<a href="<?php echo base_url('/properties/view_property_details')."/".$row["prop_id"]?>"><?php echo $row['p_address_1']." ".$row['p_address_2']." ".$row['p_address_3']; ?></a>
							*/?>
								<?php 
									$prop_address = $row['p_address_1']." ".$row['p_address_2'].", {$row['p_address_3']} {$row['p_state']}";
									echo $this->gherxlib->crmLink('vpd',$row['prop_id'],$prop_address);
								?>
								</td>
							<td>
								<?php 
									echo $getRegion->row()->region_name;
								?>
							</td>
							<td>
								<?php
								echo $getRegion->row()->subregion_name;
								?>
							</td>
							<td>
								<?php
								$current_timeday = date('Y-m-d H:i:s');
								$deadline = $this->gherxlib->getAllocateDeadLine($row['allocate_opt'],$row['allocate_timestamp']);
								?>
								<span <?php echo ( $current_timeday>=$deadline && $deadline!='' )?'style="color:red;"':''; ?>><?php echo ($deadline!='')?date('d/m/Y H:i',strtotime($deadline)):''; ?></span>
							</td>
							<td>
								<?php echo $row['allocate_notes'] ?>
							</td>
							<td>
								
									<div class="pos-rel">
										<textarea  data-allocatedBy="<?php echo $row['allocated_by'] ?>" data-jobID="<?php echo $row['jid'] ?>" style="height:38px;min-width:150px;" class="form-control response" name="response" ><?php echo $row['allocate_response']; ?></textarea>
										<i class="fa fa-check-circle text-green allocate-check-ok"></i>
									</div>
							
							</td>
						</tr>
							<?php endforeach;
							}else{
								echo "<tr><td colspan='11'>No Data</td></tr>";
							}
						?>
					</tbody>

				</table>
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

	<h4>Allocate</h4>
	<p>
		This page shows jobs that require an answer from the scheduler to know when we can service. Notes come from the 'Job Details' page when the job status is 'Allocate'. 
		The scheduler uses the response field to reply with a suitable date and time to the person who added the job in allocate.
	</p>
	<pre>
<code>SELECT `j`.`id` AS `jid`, `j`.`status` AS `j_status`, `j`.`service` AS `j_service`, `j`.`created` AS `j_created`, `j`.`date` AS `j_date`, `j`.`comments` AS `j_comments`, `j`.`job_price` AS `j_price`, `j`.`job_type` AS `j_type`, `j`.`allocate_notes`, `j`.`allocate_response`, `j`.`allocated_by`, `j`.`allocate_timestamp`, `j`.`allocate_opt`, `p`.`property_id` AS `prop_id`, `p`.`address_1` AS `p_address_1`, `p`.`address_2` AS `p_address_2`, `p`.`address_3` AS `p_address_3`, `p`.`state` AS `p_state`, `p`.`postcode` AS `p_postcode`, `p`.`comments` AS `p_comments`, `a`.`agency_id` AS `a_id`, `a`.`agency_name` AS `agency_name`, `a`.`phone` AS `a_phone`, `a`.`address_1` AS `a_address_1`, `a`.`address_2` AS `a_address_2`, `a`.`address_3` AS `a_address_3`, `a`.`state` AS `a_state`, `a`.`postcode` AS `a_postcode`, `a`.`trust_account_software`, `a`.`tas_connected`, `ajt`.`id` AS `ajt_id`, `ajt`.`type` AS `ajt_type`, `alloc_by`.`FirstName` AS `alloc_by_fname`, `alloc_by`.`LastName` AS `alloc_by_lname`
FROM `jobs` AS `j`
LEFT JOIN `property` AS `p` ON j.`property_id` = p.`property_id`
LEFT JOIN `agency` AS `a` ON  p.`agency_id` = a.`agency_id`
LEFT JOIN `job_type` AS `jt` ON j.`job_type` = jt.`job_type`
LEFT JOIN `alarm_job_type` AS `ajt` ON j.`service` = ajt.`id`
LEFT JOIN `staff_accounts` AS `alloc_by` ON j.`allocated_by` = alloc_by.`StaffID`
WHERE `j`.`del_job` = 0
AND `p`.`deleted` = 0
AND `a`.`status` = 'active'
AND `a`.`country_id` = <?php echo COUNTRY ?> 
AND `j`.`status` = 'Allocate'
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

	// job type	
	function run_ajax_allocated_by_filter(){

	var json_data = <?php echo $allocated_by_filter_json; ?>;
	var searched_val = '<?php echo $this->input->get_post('allocated_by_filter'); ?>';

	jQuery('#allocated_by_filter').next('.mini_loader').show();
	jQuery.ajax({
		type: "POST",
			url: "/sys/header_filters",
			data: { 
				rf_class: 'jobs',
				header_filter_type: 'allocated_by',
				json_data: json_data,
				searched_val: searched_val
			}
		}).done(function( ret ){	
			jQuery('#allocated_by_filter').next('.mini_loader').hide();
			jQuery('#allocated_by_filter').append(ret);
		});
				
	}

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

	jQuery(document).ready(function(){ //doc ready start

		// run headler filter ajax
		run_ajax_job_filter();
		run_ajax_state_filter();
		run_ajax_agency_filter();
		run_ajax_allocated_by_filter();

		
		/*$('#allocate_personnel').on('change',function(){

			$('#load-screen').show(); //show loader

			var staffID = $(this).val();
			jQuery.ajax({
				type: "POST",
				url: "/jobs/ajax_update_allocate_personnel",
				data: { 
					staff_id: staffID
				}
			}).done(function( ret ){
				$('#load-screen').hide(); //hide loader
				swal('','User successfully assigned','success');
			});
		})*/

		$('.response').on('change',function(){ 

			var current_logged_user = "<?php echo $this->session->staff_id; ?>";
			var allocate_personnel = "<?php echo $globalSettings->allocate_personnel; ?>";
			var allocate_personnel_split = new Array();
			var allocate_personnel_split = allocate_personnel.split(","); // comma separated to array

			var obj = $(this);
			var response = obj.val();
			var job_id = obj.attr('data-jobID')
			var allocated_by = obj.attr('data-allocatedBy')
			

			if($.inArray(current_logged_user, allocate_personnel_split) > -1){
				$('#load-screen').show(); //show loader
				jQuery.ajax({
				type: "POST",
					url: "/jobs/ajax_update_allocate_response",
					data: { 
						job_id: job_id,
						response: response,
						allocated_by: allocated_by
					}
				}).done(function( ret ){	
					$('#load-screen').hide(); //hide loader
					obj.parents("tr:first").find(".allocate-check-ok").show();
					//fadeout timer
					setTimeout(function(){ 
						obj.parents("tr:first").find(".allocate-check-ok").fadeOut();
					}, 5000);
				});
			}else{
				swal('','Only Assigned User can respond to this page','error');
				return false;
			}

		})

		$('.btn_submit_response').on('click', function(){

			var current_logged_user = "<?php echo $this->session->staff_id; ?>";
			var allocate_personnel = "<?php echo $globalSettings->allocate_personnel; ?>";
			var allocate_personnel_split = new Array();
			var allocate_personnel_split = allocate_personnel.split(","); // comma separated to array

			var obj = $(this);
			var response = obj.parents('.response_fancybox').find('.response_fancy').val();
			var job_id = obj.parents('.response_fancybox').find('.response_fancy').attr('data-jobID');
			var allocate_opt = obj.parents('.response_fancybox').find('.response_fancy').attr('data-allocate_opt');

			/*if(allocate_opt==3){ //to be booked with agent
				var allocated_by = obj.parents('.response_fancybox').find('.staff_notify').val();
			}else{
				var allocated_by = obj.parents('.response_fancybox').find('.response').attr('data-allocatedBy');
			}*/

			var allocated_by = obj.parents('.response_fancybox').find('.staff_notify').val();
			
			var err = "";
			if($.inArray(current_logged_user, allocate_personnel_split) > -1){
				if(response==""){
					err+="Response must not be empty.\n";
				}

				if(allocate_opt==3){ //to be booked with agent
					if(allocated_by==""){
						err+="Staff must not be empty.\n";
					}
				}
			}else{
				err+="Only Assigned User can respond to this page.\n";
			}

			if(err!=""){
				swal('',err,'error');
				return false;
            }

			$('#load-screen').show(); //show loader
			jQuery.ajax({
				type: "POST",
					url: "/jobs/ajax_update_allocate_response",
					data: { 
						job_id: job_id,
						response: response,
						allocated_by: allocated_by,
						allocate_opt: allocate_opt
					}
				}).done(function( ret ){	
					$('#load-screen').hide(); //hide loader
					$.fancybox.close(); //close fancybox
					//success popup				
					swal({
						title:"Success!",
						text: "Response successfully updated.",
						type: "success",
						showCancelButton: false,
						confirmButtonText: "OK",
						closeOnConfirm: false,
						showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
						timer: <?php echo $this->config->item('timer') ?>
                    });	
					var full_url = window.location.href;
                    setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);
			});

			
		})


		

		// region filter selection, cant trigger without the timeout, dunno why :( 
			<?php
		if( !empty($this->input->get_post('sub_region_ms')) ){ ?>
			setTimeout(function(){ 
				jQuery("#region_filter_state").click();
			}, 500);		
		<?php
		}
		?>



	//REGION FILTER AJAX
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
				obj.parents(".sub_region_div_chk:first").find(".rf_sub_region_lbl").addClass("rf_selNect");			
			}else{
				obj.parents(".sub_region_div_chk:first").find(".rf_sub_region_lbl").removeClass("rf_select");
			}	
					
		});

		$('#btn_assign_to').click(function(){
			var staffID = $('.ttmoselect').val();
			if(staffID==""){
				swal('','Please select staff','error');
				return false;
			}
			
			$('#load-screen').show(); //show loader

			jQuery.ajax({
				type: "POST",
				url: "/jobs/ajax_update_allocate_personnel",
				data: { 
					staff_id: staffID
				}
			}).done(function( ret ){
				$('#load-screen').hide(); //hide loader
				swal('','User successfully assigned','success');
			});

		})

		


	}) //doc ready end
</script>