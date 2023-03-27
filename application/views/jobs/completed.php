<?php
  $export_links_params_arr = array(
	'agency_filter' => $this->input->get_post('agency_filter'),
	'job_type_filter' => $this->input->get_post('job_type_filter'),
	'service_filter' => $this->input->get_post('service_filter'),
	'state_filter' =>  $this->input->get_post('state_filter'),
	'dateFrom_filter' => $this->input->get_post('dateFrom_filter'),
	'dateTo_filter' => $this->input->get_post('dateTo_filter'),
	'search_filter' => $this->input->get_post('search_filter'),
	'sub_region_ms' => $this->input->get_post('sub_region_ms'),
	'show_is_eo' => $this->input->get_post('show_is_eo'),
	'updated_to_240v_rebook' => $this->input->get_post('updated_to_240v_rebook'),
	'is_sales' => $this->input->get_post('is_sales')
);
$export_link_params = '/jobs/view_jobs_export/?status=completed&'.http_build_query($export_links_params_arr);
?>
<style>
	.col-mdd-3{
		max-width: 10%;
	}
	.region_div,
	.sub_region_div{
		display: none;
	}
	.show_it{
		display: block !important;
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
			'link' => "/jobs/completed"
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
		echo form_open('/jobs/completed',$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-md-10 columns">
					<div class="row">

						<div class="col-mdd-3">
							<label>Agency</label>
							<select id="agency_filter" name="agency_filter" class="form-control">
								<option value="">ALL</option>
								<?php
								foreach( $agency_filter_sql->result() as $agency_row ){ ?>
									<option value="<?php echo $agency_row->agency_id; ?>" <?php echo ( $agency_row->agency_id == $this->input->get_post('agency_filter') )?'selected':null;  ?>>										
										<?php echo "{$agency_row->agency_name}".( ( $this->input->get_post('isServiceDuePage') == 1 && $agency_row->auto_renew == 0 )?'(No Auto Renew)':null ); ?>							
									</option>
								<?php
								}
								?>								
							</select>							
						</div>

						<div class="col-mdd-3">
							<label>Job Type</label>
							<select id="job_type_filter" name="job_type_filter" class="form-control">
								<option value="">ALL</option>
								<?php
								foreach( $job_type_filter->result() as  $job_type_row ){ ?>
									<option value="<?php echo $job_type_row->job_type; ?>" <?php echo ( $job_type_row->job_type == $this->input->get_post('job_type_filter') )?'selected':null;  ?>><?php echo $job_type_row->job_type; ?></option>
								<?php		
								}
								?>
							</select>							
						</div>

						<div class="col-mdd-3">
							<label>Service</label>
							<select id="service_filter" name="service_filter" class="form-control">
								<option value="">ALL</option>
								<?php
								foreach( $service_filter_sql->result() as  $service_row ){ ?>
									<option value="<?php echo $service_row->id; ?>" <?php echo ( $service_row->id == $this->input->get_post('service_filter') )?'selected':null;  ?>><?php echo $service_row->type; ?></option>
								<?php		
								}
								?>
							</select>							
						</div>
						
						<div class="col-mdd-3">
							<label for="state"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></label>
							<select id="state_filter" name="state_filter" class="form-control ">
								<option value="">ALL</option>
								<?php
								foreach( $state_filter_sql->result() as  $state_row ){ ?>
									<option value="<?php echo $state_row->state; ?>" <?php echo ( $state_row->state == $this->input->get_post('state_filter') )?'selected':null;  ?>><?php echo $state_row->state; ?></option>
								<?php		
								}
								?>
							</select>							
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

										<?php
										// state
										foreach( $state_filter_sql->result() as $state_row ){ 
											
											$is_present = in_array($state_row->state, $this->input->get_post('state_ms'));
											
											?>
											<div class="checkbox state_div">
												<input type="checkbox" id="chk_state_<?php echo $state_row->state; ?>" name="state_ms[]" class="state_ms" value="<?php echo $state_row->state; ?>" <?php echo (  $is_present == true )?'checked':null; ?> />
												<label for="chk_state_<?php echo $state_row->state; ?>" class="rf_state_lbl"><?php echo $state_row->state; ?></label>
												<div class="region_div <?php echo ( $is_present == true )?'show_it':null; ?>">

													<?php	
													if( $state_row->state != '' ){

														// main region
														$sel_query = "r.`regions_id`, r.`region_name`";
														$params = array(
															'sel_query' => $sel_query,
															'region_state' => $state_row->state,
															'r_status' => 1,
															'sort_list' => array(
																array(
																	'order_by' => 'r.`region_name`',
																	'sort' => 'ASC',
																)
															),
															'display_query' => 0
														);
														$regions_sql = $this->system_model->getMainRegion($params);
														foreach ($regions_sql->result() as $index => $region){ 
															
															$is_present = in_array($region->regions_id, $this->input->get_post('region_ms'));

															?>
															<div class="checkbox region_div_chk <?php echo ( $is_present == true )?'show_it':null; ?>">

																<input type="checkbox" id="chk_region_<?php echo $region->regions_id; ?>" name="region_ms[]" class="region_ms" value="<?php echo $region->regions_id; ?>" <?php echo ( $is_present == true )?'checked':null; ?> />
																<label for="chk_region_<?php echo $region->regions_id; ?>" class="rf_region_lbl"><?php echo $region->region_name; ?></label>

																<div class="sub_region_div <?php echo ( $is_present == true )?'show_it':null; ?>">
																	<?php
																	if( $region->regions_id > 0 ){

																		// sub regions
																		$sel_query = "sr.sub_region_id as postcode_region_id, sr.subregion_name as postcode_region_name";
																		$params = array(
																			'sel_query' => $sel_query,
																			'main_region_id' => $region->regions_id,
																			'sort_list' => array(
																				array(
																					'order_by' => 'sr.`subregion_name`',
																					'sort' => 'ASC',
																				)
																			),
																			'display_query' => 0
																		);
																		$sub_regions_sql = $this->system_model->getSubRegion($params);
																		foreach ($sub_regions_sql->result() as $index => $sub_region){ 
																			
																			$is_present = in_array($sub_region->postcode_region_id, $this->input->get_post('sub_region_ms')); 

																			?>
																			<div class="checkbox sub_region_div_chk <?php echo ( $is_present == true )?'show_it':null; ?>">
																				<input type="checkbox" id="chk_sub_region_<?php echo $sub_region->postcode_region_id; ?>" name="sub_region_ms[]" class="sub_region_ms" value="<?php echo $sub_region->postcode_region_id; ?>"  <?php echo (  $is_present == true )?'checked':null; ?> />
																				<label for="chk_sub_region_<?php echo $sub_region->postcode_region_id; ?>" class="rf_sub_region_lbl"><?php echo $sub_region->postcode_region_name; ?></label>
																			</div>
																		<?php
																		}

																	}																
																	?>																
																</div>

															</div>
														<?php
														}

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

						<div class="col-mdd-3">
							<label for="date_select">Date From</label>
							<input placeholder="ALL" name="dateFrom_filter" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $this->input->get_post('dateFrom_filter'); ?>">
						</div>

						<div class="col-mdd-3">
							<label for="date_select">Date To</label>
							<input placeholder="ALL" name="dateTo_filter" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $this->input->get_post('dateTo_filter'); ?>">
						</div>

						<div class="col-mdd-3">
							<label for="phrase_select">Phrase</label>
							<input placeholder="ALL" type="text" name="search_filter" class="form-control" value="<?php echo $this->input->get_post('search_filter'); ?>" />
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
							<!--<button type="submit" name="search_submit" class="btn">Search</button>-->
							<input type="submit" name="search_submit" class="btn" value="Search">
						</div>
						
					</div>

				</div>

				<!-- DL ICONS START -->
				<?php
                if( $this->input->get_post('search_submit') ){
                ?>
				<div class="col-md-2 columns">
					<section class="proj-page-section float-right">
						<div class="proj-page-attach">
							<i class="fa fa-file-excel-o"></i>
							<p class="name"><?php echo $title; ?></p>
							<p>
								<a href="<?php echo $export_link ?>">
									Export
								</a>						
							</p>
						</div>
					</section>
				</div>
				<?php
				}
				?>
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
							<th>Date</th>
							<th>Job Type</th>
							<th>Service</th>
							<th>Price (excl. GST)</th>
							<th>Price (incl. GST)</th>
							<th>Address</th>
							<th><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></th>
							<th>Agency</th>
							<th>Job#</th>
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
						if($this->input->get_post('search_submit')){
						
							foreach( $lists->result_array() as $list_item ){	
							?>
							<tr>
								<td><?php echo $this->system_model->formatDate($list_item['j_date'],'d/m/Y'); ?></td>
								<td>
									<?php 
									echo $this->gherxlib->getJobTypeAbbrv($list_item['j_type']); 
									// empty, OS and UB
									if( $list_item['assigned_tech'] == 1 || $list_item['assigned_tech'] == 2 ){ ?>
										<img data-toggle="tooltip" title="No Technician" src="<?php echo $this->config->item('crm_link') ?>/images/no_tech.png" class="j_icons no_tenants_icon" />
									<?php	
									}
									?>
								</td>
								<td>									
									<?php
									// display icons
									$job_icons_params = array(
										'job_id' => $list_item['jid']
									);
									echo $this->system_model->display_job_icons_v2($job_icons_params);
									?>
								</td>
								<td>$<?php echo number_format($this->system_model->price_ex_gst($list_item['j_price'] / 1.1),2);?></td>
								<td>$<?php echo number_format($this->system_model->price_ex_gst($list_item['j_price']),2);?></td>
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
								<td>
									<div class="checkbox">
										<input class="chk_job" name="chk_job[]" type="checkbox" id="check-<?php echo $list_item["jid"] ?>" data-jobid="<?php echo $list_item["jid"]; ?>">
										<label for="check-<?php echo $list_item["jid"] ?>">&nbsp;</label>
									</div>
								</td>
							</tr>
							<?php 
							}	

						}else{
							echo "<tr><td colspan='9'>Press Search to display data</td></tr>";
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

	<h4>Completed Jobs</h4>
	<p>This page shows jobs that are completed in the system</p>
	<p>Price are exclusive of GST.</p>
<pre>
<code><?php echo $page_query; ?></code>
</pre>

</div>
<!-- Fancybox END -->

<script type="text/javascript">
jQuery(document).ready(function(){ //document ready start	
	

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

		
	// state checkbox
	jQuery('.region_dp_div').on('click','.state_ms',function(){
		
		var chk_dom = jQuery(this);
		var is_checked = chk_dom.prop("checked");
		var parent = chk_dom.parents(".state_div:first");

		if( is_checked == true ){
			parent.find(".region_div").show();
		}else{
			parent.find(".region_div").hide();
		}			
				
	});


	// main region checkbox
	jQuery('.region_dp_div').on('click','.region_ms',function(){
		
		var chk_dom = jQuery(this);
		var is_checked = chk_dom.prop("checked");
		var parent = chk_dom.parents(".region_div_chk:first");

		if( is_checked == true ){
			parent.find(".sub_region_div").show();
		}else{
			parent.find(".sub_region_div").hide();
		}			
				
	});				
	

}) //document ready end
</script>