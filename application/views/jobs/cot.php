<style>
	.col-mdd-3{
		max-width: 12.5%;
	}
</style>
<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/jobs/cot"
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
		echo form_open('/jobs/cot',$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-lg-10 col-md-10 columns">


					<div class="row">

						<div class="col-mdd-3">
							<label for="agency_select">Agency</label>
							<select id="agency_filter" name="agency_filter" class="form-control field_g2">
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
							<label for="state"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></label>
							<select id="state_filter" name="state_filter" class="form-control">
								<option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>
						</div>

						
						<!-- State or Region -->
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
							<input placeholder="ALL" name="date_filter" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $this->input->get_post('date_filter'); ?>">
					    </div>

						<div class="col-md-2">
							<label for="phrase_select">Phrase</label>
							<input placeholder="ALL" type="text" name="search_filter" class="form-control"  value="<?php echo $this->input->get_post('search_filter'); ?>" />
					    </div>

						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<button type="submit" class="btn btn-inline">Search</button>
					    </div>
						
					</div>

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
							<th>End Date</th>
							<th>Date</th>
							
							<th><?php echo $this->customlib->getDynamicRegionViaCountry($this->config->item('country')); ?></th>
							<th style="width:10%;">Booking</th>
						
							<th>Job Type</th>
							
							<th>
								<div class="tbl-tp-name colorwhite bold">Service</div>
								<a href="<?php echo $_SERVER['PHP_SELF'] ?>?sort=j.service&order_by=<?php echo ($_REQUEST['order_by']=='ASC')?'DESC':'ASC'; ?>&job_type=<?php echo $job_type; ?>&service=<?php echo $service; ?>&date=<?php echo $date; ?>&phrase=<?php echo $phrase; ?>"> 
									<div class="arw-std-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?> arrow-<?php echo ( $order_by=='ASC' )?'up':'dwn'; ?>-<?php echo ($sort=='j.service')?'active':''; ?>"></div>
								</a>
							</th>
							
							<th>Address</th>
					
							<th><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></th>
							<th>Agency</th>
							<th style="width:20%">Comments</th>
							<th>Job #</th>
							
							<th>
								<div class="checkbox" style="margin:0;">
									<input type="checkbox" id="maps_check_all" />
									<label for="maps_check_all">&nbsp;</label>
								</div>
							</th>
						</tr>
					</thead>

					<tbody>
						<?php
						if($lists->num_rows()>0){
						$i = 0;
						foreach($lists->result_array() as $list_item): 	
						$params = array(
							'sel_query' => "sr.subregion_name as postcode_region_name, sr.sub_region_id as postcode_region_id",
							'postcode' => $list_item['p_postcode'],
						);
						$getRegion = $this->system_model->get_postcodes($params)->row();
						
						
						$tdf_start_date = date("Y-m-d",strtotime($list_item['start_date']." -3 days"));
						$tdf_end_date = date("Y-m-d",strtotime($list_item['due_date']." -3 days")); 
						
						$row_color = "yellowRowBg";
						
						if( date("Y-m-d")>=$tdf_start_date ){
							$row_color = "whiteRowBg";
						}
						
						if( $this->system_model->isDateNotEmpty($list_item['due_date']) && date("Y-m-d")>=$tdf_end_date ){
							$row_color = "greenRowBg";
						}
						
						// urgent jobs
						if($list_item['urgent_job']==1){
							$row_color = "greenRowBg";
						}
						
						// jobs not completed
						if($list_item['job_reason_id']>0){
							$row_color = "yellowRowBg";
						}
						
						// if start/end date is empty
						if( !$this->system_model->isDateNotEmpty($list_item['start_date']) && !$this->system_model->isDateNotEmpty($list_item['due_date']) && $list_item['no_dates_provided'] == 0 ){
							$row_color = "redRowBg";
						}
						
						// if start/end date is empty and N/A
						if( !$this->system_model->isDateNotEmpty($list_item['start_date']) && !$this->system_model->isDateNotEmpty($list_item['due_date']) && $list_item['no_dates_provided'] == 1 ){
							$row_color = "whiteRowBg";
						}
						

						?>
						<tr class="body_tr jalign_left <?php echo $row_color; ?>">
							
							<td><?php echo ($list_item['start_date']!="" && $list_item['start_date']!="0000-00-00" && $list_item['start_date']!="1970-01-01" )?date("d/m/Y",strtotime($list_item['start_date'])):(($list_item['no_dates_provided']==1)?'<div style="text-align: center;">N/A</div>':''); ?></td>
							<td><?php echo ($list_item['due_date']!="" && $list_item['due_date']!="0000-00-00" && $list_item['due_date']!="1970-01-01" )?date("d/m/Y",strtotime($list_item['due_date'])):(($list_item['no_dates_provided']==1)?'<div style="text-align: center;">N/A</div>':''); ?></td>
						
							
							<td><?php echo ($list_item['jdate']!="" && $list_item['jdate']!="0000-00-00")?date("d/m/Y",strtotime($list_item['jdate'])):''; ?></td>
							
							<td>
							<?php 
								echo $getRegion->postcode_region_name;
							?>
							</td>
							
							<td>
                                <?php
                                $getStr = $this->system_model->getStrbyRegion($getRegion->postcode_region_id);
                                $fcount = 0;
                                foreach($getStr->result_array() as $str_row){

                                    $reg_arr = explode(",",$str_row['sub_regions']);

                                    if( in_array($getRegion->postcode_region_id, $reg_arr) ){
									
                                        echo ($fcount!=0)?', ':'';
                                        
                                ?> 
                                        <a href="/tech_run/run_sheet_admin/<?php echo $str_row['tech_run_id'] ?>"><?php echo date('d/m',strtotime($str_row['date'])); ?></a><?php	
                                        $fcount++;
                                        
                                        }else{
                                            $no_set_date_flag = 1;
                                        }

                                }
                                ?>
							</td>
							
							<td><?php echo $this->gherxlib->getJobTypeAbbrv($list_item['job_type']); ?></td>
							
							<td><img src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($list_item['jservice']); ?>" /></td>
						
							
							<td>
							<?php /*
							<a href="/view_property_details.php?id=<?php echo $list_item['property_id']; ?>"><?php echo "{$list_item['p_address_1']} {$list_item['p_address_2']}, {$list_item['p_address_3']}"; ?></a>
							*/
							
							$prop_address = $list_item['p_address_1']." ".$list_item['p_address_2'].", ".$list_item['p_address_3'];
							echo $this->gherxlib->crmLink('vpd',$list_item['property_id'],$prop_address);
							
							?>
							</td>
							
							<td><?php echo $list_item['p_state']; ?></td>
							<td><?php echo $list_item['agency_name']; ?></td>
							<td><?php echo $list_item['comments']; ?></td>
							<td>
							
							<?php
							/*
							<a href="view_job_details.php?id=<?php echo $list_item['jid']; ?>"><?php echo $list_item['jid']; ?></a>
							*/
							echo $this->gherxlib->crmLink('vjd',$list_item['jid'],$list_item['jid']);
							?>
							</td>							
							
							<td>
								<div class="checkbox" style="margin:0;">
									<input type="checkbox" class="maps_chk_box" value="<?php echo $list_item['jid']; ?>" id="check-<?php echo $list_item["jid"] ?>" />
									<label for="check-<?php echo $list_item["jid"] ?>">&nbsp;</label>
								</div>

								<input type="hidden" class="hid_job_id" value="<?php echo $list_item['jid']; ?>" />
							</td>
									
						</tr>

					<?php $i++; endforeach; ?>
					<?php
						}else{
							echo "<tr><td colspan='13'>No Data</td></tr>";
						}
							?>
					</tbody>

				</table>

				<div id="mbm_box" class="text-right" style="margin-bottom: 15px;">
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
						<button type="button" id="btn_assign" class="btn btn-inline blue-btn submitbtnImg">Assign</button>
					    </div>
					</div>
				</div>
			
			</div>

			<nav class="text-center">
				<?php echo $pagination; ?>
			</nav>

			<div class="pagi_count text-center"><?php echo $pagi_count; ?></div>

		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4>COT Jobs</h4>
	<p>This page shows COT and LR jobs that are not yet completed</p>
	<ul>
		<li><span class="greenRowBg">Green</span> = Urgent</li>
		<li><span class="yellowRowBg">Yellow</span> = Can't do yet due to dates</li>
		<li>White = We can do now. Dates are within range</li>
		<li><span class="redRowBg">Red</span> = Overdue. We are outside of dates. (Or no dates given)</li>
	</ul>

</div>
<!-- Fancybox END -->

<script>

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

$(document).ready(function(){

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
				obj.parents(".sub_region_div_chk:first").find(".rf_sub_region_lbl").addClass("rf_select");			
			}else{
				obj.parents(".sub_region_div_chk:first").find(".rf_sub_region_lbl").removeClass("rf_select");
			}	
					
		});
	
	
	// check all toggle
	$("#maps_check_all").click(function(){
  
	  if($(this).prop("checked")==true){
		$(".maps_chk_box:visible").prop("checked",true);
		$("#mbm_box").show();
	  }else{
		$(".maps_chk_box:visible").prop("checked",false);
		$("#mbm_box").hide();
	  }
	  
	});
	
	// toggle hide/show remove button
	$(".maps_chk_box").click(function(){

	  var chked = $(".maps_chk_box:checked").length;
	  
	  if(chked>0){
		$("#mbm_box").show();
	  }else{
		$("#mbm_box").hide();
	  }

	});
	
	// move to maps 
	$("#btn_assign").click(function(){
		
		var job_id = new Array();
		var tech_id = $("#maps_tech").val();
		var date = $("#assign_date").val();

		//tech and date validation
		if(tech_id==""){
			swal('','Tech must not be empty','error');
			return false;
		}
		if(date==""){
			swal('','Date must not be empty','error');
			return false;
		}
		
		$(".maps_chk_box:checked").each(function(){
			job_id.push($(this).val());
		});

		$('#load-screen').show(); 
        $.ajax({
            type: "POST",
            url: '<?php echo base_url(); ?>jobs_ajax/cot_mod/ajax_move_to_maps',
			data: { 
				job_id: job_id,
				tech_id: tech_id,
				date: date
			},
            success: function(json){ 
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
				setTimeout(function(){ window.location='/jobs/cot'; }, <?php echo $this->config->item('timer') ?>);
            }
        });
				
	});
	
});
</script>