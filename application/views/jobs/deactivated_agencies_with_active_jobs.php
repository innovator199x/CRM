<style>
	.col-mdd-3{
		max-width:16.5%;
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
			'link' => "/jobs/deactivated_agencies_with_active_jobs"
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
		echo form_open('/jobs/deactivated_agencies_with_active_jobs',$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-md-8 columns">
					<div class="row">

						<div class="col-mdd-3">
							<label for="jobtype_select">Agency Status</label>
							<select id="a_status_filter" name="a_status_filter" class="form-control field_g2">
								<option value="all">ALL</option>
								<option value="deactivated" <?php echo ( $this->input->get_post('a_status_filter') == 'deactivated' )?'selected':null; ?>>Deactivated</option>
								<option value="target" <?php echo ( $this->input->get_post('a_status_filter') == 'target' )?'selected':null; ?>>Target</option>
							</select>							
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
							<select id="state_filter" name="state_filter" class="form-control">
								<option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>
						</div>

						<div class="col-mdd-3">
							<label for="date_select">Date</label>
							<input placeholder="ALL" name="date_filter" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $this->input->get_post('date_filter'); ?>">
						</div>

						<div class="col-md-3">
							<label for="phrase_select">Phrase</label>
							<input placeholder="ALL" type="text" name="search_filter" class="form-control" value="<?php echo $this->input->get_post('search_filter'); ?>" />
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
							<th>Job Type</th>
							<th>Service</th>
							<th>Price</th>
							<th>Address</th>
							<th><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></th>
							<th>Agency</th>
							<th style="width:380px;">Comments</th>
                            <th>Job#</th>
						</tr>
					</thead>

					<tbody>
						<?php
						if($lists->num_rows()>0){
						foreach($lists->result_array() as $list_item): 			
						?>
						<tr>
							<td>
                            <?php echo $this->gherxlib->getJobTypeAbbrv($list_item['j_type']);?>
							</td>
							<td>
								<img data-toggle="tooltip" title="<?php echo $list_item['ajt_type'] ?>" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($list_item['j_service']); ?>" />
							</td>
							<td>
                            <?php 
							//echo $list_item['j_price']; 
							echo number_format($this->system_model->price_ex_gst($list_item['j_price']),2);
							?>
							</td>
							<td>
							<?php /*
							<a href="<?php echo base_url('/properties/view_property_details')."/".$list_item["prop_id"]?>"><?php echo $list_item['p_address_1']." ".$list_item['p_address_2']." ".$list_item['p_address_3']; ?></a></td>
							*/ 

								$prop_address = $list_item['p_address_1']." ".$list_item['p_address_2'].", ".$list_item['p_address_3'];
								echo $this->gherxlib->crmLink('vpd',$list_item['prop_id'],$prop_address);
							?>
							<td>
							<?php echo $list_item['p_state']; ?>
							</td>
							<td class="<?php echo ( $list_item['priority'] > 0 )?'j_bold':null; ?>">
								<?php echo $list_item['agency_name']." ".( ( $list_item['priority'] > 0 )?' ('.$list_item['abbreviation'].')':null ); ?>
							</td>
							<td><?php echo $list_item['j_comments'] ?></td>
                            <td>
								<?php
								/*
								 echo '<a href="/jobs/view_job_details/'.$list_item["jid"].'">'.$list_item["jid"].'</a>'; 
								 */
								echo $this->gherxlib->crmLink('vjd',$list_item['jid'],$list_item['jid']);
								 ?>
                            </td>
						</tr>
						<?php endforeach;
						}else{
							echo "<tr><td colspan='9'>No Data</td></tr>";
						}
						?>
					</tbody>

				</table>
			</div>

			<nav aria-label="Page navigation example" style="text-align:center">
				<?php echo $pagination; ?>
			</nav>

			<div class="pagi_count text-center"><?php echo $pagi_count; ?></div>

		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4>Deactivated Agencies with Active Jobs</h4>
	<p>Jobs where the status is != completed && != cancelled where the agency status = deactivated</p>
	<pre>
<code><?php echo $sql_query; ?></code>
	</pre>

</div>
<!-- Fancybox END -->

<script type="text/javascript">


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


	jQuery(document).ready(function() { // Document ready start

		// run headler filter ajax
		run_ajax_job_filter();
		run_ajax_service_filter();
		run_ajax_state_filter();

	}) // Document ready end

</script>