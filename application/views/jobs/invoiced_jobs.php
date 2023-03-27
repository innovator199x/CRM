<?php
  $export_links_params_arr = array(
	'agency_filter' => $this->input->get_post('agency_filter'),
	'job_type_filter' => $this->input->get_post('job_type_filter'),
	'service_filter' => $this->input->get_post('service_filter'),
	'state_filter' =>  $this->input->get_post('state_filter'),
	'dateFrom_filter' => $this->input->get_post('dateFrom_filter'),
	'dateTo_filter' => $this->input->get_post('dateTo_filter'),
	'search_filter' => $this->input->get_post('search_filter'),
	'sub_region_ms' => $this->input->get_post('sub_region_ms')
);
$export_link_params = '/jobs/view_jobs_export/?status=completed&'.http_build_query($export_links_params_arr);
?>
<style>
	.col-mdd-3{
		max-width: 11.3%;
	}
</style>
<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => 'Reports',
			'link' => "/reports"
		),
		array(
			'title' => $title,
			'status' => 'active',
			'link' => $uri
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	
	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">
		
			<div class="for-groupss row">
			
				<div class="col-md-10 columns">
				<?php
				$form_attr = array(
					'id' => 'jform'
				);
				echo form_open($uri,$form_attr);
				?>
					<div class="row">

						<div class="col-mdd-3">
							<label for="date_select">Date From</label>
							<input placeholder="ALL" name="dateFrom_filter" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $this->input->get_post('dateFrom_filter'); ?>">
						</div>

						<div class="col-mdd-3">
							<label for="date_select">Date To</label>
							<input placeholder="ALL" name="dateTo_filter" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $this->input->get_post('dateTo_filter'); ?>">
						</div>

						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>		
							<input type="submit" name="search_submit" class="btn" value="Search">
						</div>
						
						
					</div>
					<?php echo form_close(); ?>
			</div>		
			
			
			 <!-- DL ICONS START -->
			 <div class="col-md-2 columns">
                    <section class="proj-page-section float-right">
                        <div class="proj-page-attach">
                            <i class="fa fa-file-excel-o"></i>
                            <p class="name"><?php echo $title; ?></p>
                            <p>
                                <a href="<?php echo $export_link ?>" target="blank">
                                    Export
                                </a>
                                
                            </p>
                        </div>
                    </section>
                </div>
                <!-- DL ICONS END -->
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
							<th>Service</th>
							<th>Invoice Amount</th>
							<th>Address</th>
							<th><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></th>
							<th>Agency</th>
							<th>Job#</th>
							<!--
							<th>
								<div class="checkbox" style="margin:0;">
									<input name="chk_all" type="checkbox" id="check-all">
									<label for="check-all">&nbsp;</label>
								</div>
							</th>
							-->
						</tr>
					</thead>

					<tbody>
						<?php
						if($this->input->get_post('search_submit')){

							foreach( $lists->result_array() as $list_item ){	
							?>
							<tr>
								<td>
								<?php echo $this->system_model->formatDate($list_item['j_date'],'d/m/Y'); ?>
								</td>
								<td>
								<?php echo $this->gherxlib->getJobTypeAbbrv($list_item['j_type']); ?>
								</td>
								<td>
															<img data-toggle="tooltip" title="<?php echo $list_item['ajt_type'] ?>" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($list_item['j_service']); ?>" />
								</td>
								<td>
															$<?php 
															//echo $list_item['invoice_amount']; 
															echo number_format($this->system_model->price_ex_gst($list_item['invoice_amount']),2);
															?>
								</td>
								<td>

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
								<?php /*
								echo '<a href="/jobs/view_job_details/'.$list_item["jid"].'">'.$list_item["jid"].'</a>'; 
								*/
								echo $this->gherxlib->crmLink('vjd',$list_item['jid'],$list_item['jid']);
								?>
								</td>
								<!--
								<td>
									<div class="checkbox">
										<input class="chk_job" name="chk_job[]" type="checkbox" id="check-<?php echo $list_item["jid"] ?>" data-jobid="<?php echo $list_item["jid"]; ?>">
										<label for="check-<?php echo $list_item["jid"] ?>">&nbsp;</label>
									</div>
								</td>
								-->
							</tr>
							<?php 
							}	

						}else{
							echo "<tr><td colspan='8'>Press Search to display data</td></tr>";
						}
						
						
						if( $this->input->get_post('search_submit') ){ ?>
							<tr>
								<td colspan="8">TOTAL INVOICE AMOUNT: <strong>$<?php echo number_format($invoice_amount_tot,2); ?></strong></td>
							</tr>
						<?php
						}
						
						?>						
					</tbody>

				</table>
		
				<div id="mbm_box" class="text-right"><button id="moveBackToMerge_btn" type="button" class="btn">Move back to Merged</button></div>
			</div>

			<?php		
			
			if( $this->input->get_post('search_submit') ){ ?>
				<nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
				<div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
			<?php
			}	
				
			?>

		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>This page shows all jobs that are completed and invoiced. Dummy (Other Supplier) Jobs are hidden from report. Some old jobs (Prior to 2017) may not be accurate unless the job is updated and the invoice amount is stored.</p>
	<p>Invoice Amount are exclusive of GST.</p>
	<pre>
<code>SELECT `j`.`id` AS `jid`, `j`.`service` AS `j_service`, `j`.`date` AS `j_date`, `j`.`job_price` AS `j_price`, `j`.`job_type` AS `j_type`, `j`.`invoice_amount`, `p`.`property_id` AS `prop_id`, `p`.`address_1` AS `p_address_1`, `p`.`address_2` AS `p_address_2`, `p`.`address_3` AS `p_address_3`, `p`.`state` AS `p_state`, `p`.`postcode` AS `p_postcode`, `a`.`agency_id` AS `a_id`, `a`.`agency_name` AS `agency_name`
FROM `jobs` AS `j`
INNER JOIN `property` AS `p` ON j.`property_id` = p.`property_id` 
INNER JOIN `agency` AS `a` ON p.`agency_id` = a.`agency_id` 
WHERE `j`.`status` = 'Completed'
AND (
j.`assigned_tech` != 1
OR j.`assigned_tech` IS NULL
)
AND CAST(j.`date` AS Date) >= '<?php echo $this->config->item('accounts_financial_year'); ?>'
ORDER BY j.`date` ASC
LIMIT 0, 50 </code>
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

jQuery(document).ready(function(){ //document ready start

	/*
	// preselect header filters on search
	var searched_agency_filter_val = '<?php echo $this->input->get_post('agency_filter'); ?>';
	var searched_job_type_filter_val = '<?php echo $this->input->get_post('job_type_filter'); ?>';
	var searched_service_filter_val = '<?php echo $this->input->get_post('service_filter'); ?>';
	var searched_state_filter_val = '<?php echo $this->input->get_post('state_filter'); ?>';

	if( searched_agency_filter_val != '' ){
		run_ajax_agency_filter();
	}

	if( searched_job_type_filter_val != '' ){
		run_ajax_job_filter();
	}

	if( searched_service_filter_val != '' ){
		run_ajax_service_filter();
	}

	if( searched_state_filter_val != '' ){
		run_ajax_state_filter();
	}

	// header filters load on click	
	// agency
	jQuery("#agency_filter").click(function(e){

		var agency_filter = jQuery("#agency_filter option:eq(1)").val();
		if( agency_filter == null ){
			run_ajax_agency_filter();
		}

	});

	// job type
	jQuery("#job_type_filter").click(function(e){

		var job_type_filter = jQuery("#job_type_filter option:eq(1)").val();
		if( job_type_filter == null ){
			run_ajax_job_filter();
		}

	});

	// service
	jQuery("#service_filter").click(function(e){

		var service_filter = jQuery("#service_filter option:eq(1)").val();
		if( service_filter == null ){
			run_ajax_service_filter();
		}

	});

	// state
	jQuery("#state_filter").click(function(e){
		
		var state_filter = jQuery("#state_filter option:eq(1)").val();
		if( state_filter == null ){
			run_ajax_state_filter();
		}

	});
	*/
	
	

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
	
	$('#moveBackToMerge_btn').on('click',function(e){
		e.preventDefault();
		var job_id = new Array();
		$('.chk_job:checked').each(function(){
			var thisVal = $(this).attr('data-jobid');
			job_id.push(thisVal);
		})
		swal(
				{
					title: "",
					text: "Are you sure you want to move jobs to Merged Certificates?",
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
								url: "<?php echo base_url('/jobs/move_to_merge') ?>",
								dataType: 'json',
								data: {
									job_id:job_id,
								}
								}).done(function(data){
									
									if(data.status){
										$('#load-screen').hide(); //hide loader
										swal({
											title:"Success!",
											text: "Merge Certificates Successful",
											type: "success",
											showCancelButton: false,
											confirmButtonText: "OK",
											closeOnConfirm: false,
										},function(isConfirm){
										   if(isConfirm){ 
											   swal.close();
											   location.reload();
											   }
										});
									}else{
									   swal.close();
									   location.reload();
									}
								});
							}
					
				}
			);

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
				obj.parents(".sub_region_div_chk:first").find(".rf_sub_region_lbl").addClass("rf_select");			
			}else{
				obj.parents(".sub_region_div_chk:first").find(".rf_sub_region_lbl").removeClass("rf_select");
			}	
					
		});

	

}) //document ready end
</script>