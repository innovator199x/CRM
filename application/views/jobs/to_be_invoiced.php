<style type="text/css">
	.col-mdd-3{
		max-width: 12.1%;
	}
	#jtable_wrapper{
		padding-right: 0px !important;
    	padding-left: 0px !important;
	}
	div.dataTables_wrapper div.dataTables_paginate ul.pagination li.disabled{display: none;}
	div.dataTables_wrapper div.dataTables_paginate{text-align: center !important; margin-top: 20px !important;}
	div.dataTables_wrapper div.dataTables_info{text-align: center !important}
	th.create_job > div > a > span {display: none !important}
	th.create_invoice > div > a > span {display: none !important}
	.table td{padding: 0px 5px 0px 5px !important;}
	.table a {top: 0px !important;}
	.tip_message {margin-top: 10px;}
</style>
<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/jobs/to_be_invoiced"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	<header class="box-typical-header" style="padding-bottom: 30px;">

		<div class="box-typical box-typical-padding">
			<?php
		$form_attr = array(
			'id' => 'jform'
		);
		echo form_open('/jobs/to_be_invoiced',$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-lg-9 col-md-10 columns">

					<div class="row">

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
							<label for="service_select"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></label>
							<select id="state_filter" name="state_filter" class="form-control">
								<option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>
						</div>

						<div class="col-mdd-3">
							<label for="agency_select">Agency</label>
							<select id="agency_filter" name="agency_filter" class="form-control field_g2">
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
							<input name="date_filter" placeholder="ALL"  class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $this->input->get_post('date_filter'); ?>">
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

				<div class="col-lg-3 col-md-2 columns">
					<ol class="tip_message">
						<li>First create a job if required</li>
						<li>Create invoice. Property will disappear off screen</li>
					</ol>
				</div>


			</div>
			</form>
		</div>
	</header>
	
	
	<section>
		<div class="body-typical-body">
			<div class="table-responsive" style="overflow-y: hidden;">
				
				<table id="jtable" class="table table-hover main-table">
				<!--<table id="jtable" class="table table-hover main-table table-striped">-->

                    <thead>
						<tr>    
							<th>Invoice Date</th>
							<th>Subscription Date</th>
							<th>Job Type</th>
							<th>Age</th>
							<th>Service</th>
							<th>Price</th>
							<th>Address</th>
							<th><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></th>
							<th>Region</th>	
							<th>Agency</th>	
							<th>Last Job</th>
							<th>Last Job Type</th>
							<th class="create_job">
								<div class="checkbox create_job" style="margin:0;">
									<input type="checkbox" id="create_job_check_all" />
									<label for="create_job_check_all">&nbsp;</label>
									Create Job
								</div>
								<!-- <div class="tbl-tp-name colorwhite bold">
									<input type="checkbox" id="create_job_check_all" />
									Create Job
								</div> -->
							</th>
							<th class="create_invoice">
								<div class="checkbox create_invoice" style="margin:0;">
									<input type="checkbox" id="invoice_check_all" />
									<label for="invoice_check_all">&nbsp;</label>
									Invoice
								</div>
								<!-- <div class="tbl-tp-name colorwhite bold">
									<input type="checkbox" id="invoice_check_all" />
									Invoice
								</div> -->
							</th>
						</tr>
					</thead>

					<tbody>
					<?php
						if($lists->num_rows()>0){
						// var_dump($lists->result_array());
						$i = 0;
						$total = 0;
						foreach($lists->result_array() as $key => $list_item): 

						$params = array(
							'postcode_region_postcodes' => $list_item['p_postcode'],
						);
						$getRegion = $this->system_model->getRegion($params)->row();

						$row_color = null;

						/*
						// grey alternation color
						$row_color = ($i%2==0)?"style='background-color:#eeeeee;'":"";
						
						// if alarms 240v or 240vli are expired
						if( $list_item['jservice']==2 ){
							$a_sql = $this->customlib->getAlarm($list_item['jid']);
							if(count($a_sql) > 0){
								$row_color = "style='background-color:#FFCCCB;'";
								
							}
						}
						
						// urgent jobs
						if($list_item['urgent_job']==1){
							$row_color = "style='background-color:#2CFC03;'";
						}
						
						// jobs not completed
						if($list_item['job_reason_id']>0){
							$row_color = "style='background-color:#ffff9d;'";
						}
						*/

						// compare job date to subscription date
						// job date
						$job_date_day = date("d",strtotime($list_item['jdate']));
						$job_date_month = date("m",strtotime($list_item['jdate']));
						$ddmm_format_job_date = "{$job_date_day}/{$job_date_month}";

						// subscription date
						$prop_sub_sql = $this->db->query("
						SELECT `subscription_date`
						FROM `property_subscription`
						WHERE `property_id` = {$list_item["property_id"]}
						");
						$prop_sub_row = $prop_sub_sql->row();
						$prop_sub_day = date("d",strtotime($prop_sub_row->subscription_date));
						$prop_sub_month = date("m",strtotime($prop_sub_row->subscription_date));
						$ddmm_format_prop_sub_date = "{$prop_sub_day}/{$prop_sub_month}";

						if( 
							$this->system_model->isDateNotEmpty($prop_sub_row->subscription_date) == true && 
							( $ddmm_format_job_date != $ddmm_format_prop_sub_date )
						){
							$row_color = "style='background-color:#fac3c373;'";
						}

						?>
						<tr class="body_tr jalign_left" <?php echo $row_color; ?>>
						<!--<tr class="body_tr jalign_left">-->
							<td>
								<?php echo ($list_item['jdate']!="" && $list_item['jdate']!="0000-00-00")?date("d/m/Y",strtotime($list_item['jdate'])):''; ?>
							</td>
							<td>
								<?php echo ($list_item['subscription_date']!="" && $list_item['subscription_date']!="0000-00-00")?date("d/m",strtotime($list_item['subscription_date'])):'?'; ?>
							</td>
							<td>
								<a href="<?php echo $this->config->item("crm_link"); ?>/view_job_details.php?id=<?php echo $list_item["jid"]; ?>" target="_blank">
									<?php echo getJobTypeAbbrv($list_item['job_type']); ?>
								</a>								
							</td>
							<td>
								<?php
									// Age
									$date1=date_create(date('Y-m-d',strtotime($list_item['jcreated'])));
									$date2=date_create(date('Y-m-d'));
									$diff=date_diff($date1,$date2);
									$age = $diff->format("%r%a");
									$age_val = (((int)$age)!=0)?$age:0;
									echo $age_val;
									$age_val_tot += $age_val;
								?>
							</td>
							<td>
								<img src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($list_item['jservice']); ?>" />
							</td>
							<td>
								<?php 
									echo '$'.$list_item['job_price'];
									$total = $total + $list_item['job_price'];
								?>
							</td>
							<td>
								<?php 
									$prop_address = $list_item['p_address_1']." ".$list_item['p_address_2'].", ".$list_item['p_address_3'];
									echo $this->gherxlib->crmLink('vpd',$list_item["property_id"],$prop_address);
								?>
							</td>
							<td>
								<?php echo $list_item['p_state']; ?>
							</td>
							<td>
								<?php
									// Region		
									$pr = $this->customlib->getPostCodeRegName($list_item['p_postcode'], $this->config->item('country'));
									echo $pr[0]->postcode_region_name;
								?>
							</td>
							<td class="<?php echo ( $list_item['priority'] > 0 )?'j_bold':null; ?>">
								<?php echo $list_item['agency_name']." ".( ( $list_item['priority'] > 0 )?' ('.$list_item['abbreviation'].')':null ); ?>
							</td>

							<?php
							$last_job_sql = $this->customlib->getLastCompletedJob($list_item['property_id']);
							$last_job_date = $last_job_sql[0]->jdate;
							$last_job_type = $last_job_sql[0]->job_type;
							?>

							<td><?php echo ( $this->customlib->isDateNotEmpty($last_job_date) )?date('d/m/Y',strtotime($last_job_date)).( ($last_job_sql[0]->assigned_tech ==1)?' <strong>(OS)</strong>':null ):''; ?></td>	
							<td><?php echo getJobTypeAbbrv($last_job_type); ?></td>
							<td>
								<?php
								if( $this->system_model->isDateNotEmpty($prop_sub_row->subscription_date) == true ){ ?>

									<div class="checkbox" style="margin:0;">
										<input id="check-<?php echo $list_item["jid"] ?>" type="checkbox" class="create_job_chk" value="<?php echo $list_item['jid']; ?>" <?php echo ($last_job_sql[0]->assigned_tech ==1)?'style="display:none;"':null; ?> />
										<label for="check-<?php echo $list_item["jid"] ?>">&nbsp;</label>
									</div>

								<?php
								}
								?>															
							</td>
							<td>
								<?php
								if( $this->system_model->isDateNotEmpty($prop_sub_row->subscription_date) == true ){ ?>

									<div class="checkbox" style="margin:0;">
										<input id="check2-<?php echo $list_item["jid"] ?>"  type="checkbox" class="invoice_chk_box" value="<?php echo $list_item['jid']; ?>" />
										<label for="check2-<?php echo $list_item["jid"] ?>">&nbsp;</label>
									</div>

								<?php
								}
								?>																	
								<input type="hidden" class="hid_job_id" value="<?php echo $list_item['jid']; ?>" />
								<input type="hidden" class="property_id" value="<?php echo $list_item['property_id']; ?>" />
								<input type="hidden" class="ajt_id" value="<?php echo $list_item['jservice']; ?>" />
								<input type="hidden" class="is_dk_allowed" value="<?php echo $list_item['allow_dk']; ?>" />
								<input type="hidden" class="agency_id" value="<?php echo $list_item['agency_id']; ?>" />
							</td>	
						</tr>
						<?php $i++; endforeach; ?>
						<tfoot>
							<tr>
								<td colspan="3"></td>
								<td colspan="2">
								<?php 
									echo floor($age_val_tot/$i); 
								?>
								</td>
								<td colspan="10">
								<?php echo "<strong>$" . number_format($total, 2) . "</strong>"; ?>
								</td>
								<td colspan="100%"></td>
							</tr>
						</tfoot>
						<?php
							}else{
								echo "<tr><td colspan='13'>No Data</td></tr>";
							}
						?>
					</tbody>
				</table>
				<div style="margin-top: 15px; float: right; display:none;" id="invoice_btn_div">
					<button type="button" id="btn_do_invoice" class="btn btn-inline blue-btn submitbtnImg">Invoice</button>
				</div>
				
				<div style="margin-top: 15px; float: right; display:none;" id="create_job_btn_div">
					<button type="button" id="btn_create_job" class="btn btn-inline blue-btn submitbtnImg">Create Job</button>
				</div>
			</div>

			<div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
		</div>
	</section>
</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4>To Be Invoiced</h4>
	<!--<p>This page shows any jobs that are on an Annual subscription and due for billing. The technician will not complete these jobs, they are automatically invoiced to the agent for payment and once processed will moved into ‘Merged jobs’ with SMS marked as sent (although not actually sent)</p>-->
	<p>This page is where we invoice all jobs for properties that are on subscription billing. All agencies that are on subscription billing will have their jobs come into this page whereby we will process daily.</p>

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


$(document).ready(function() {

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
		
	// filter search selection
	<?php
	if( $this->input->get_post('agency_filter') != '' ){ ?>
			jQuery("#agency_filter").click();

	<?php
	}
	?>

	<?php
	if( $this->input->get_post('job_type_filter') != '' ){ ?>
			jQuery("#job_type_filter").click();

	<?php
	}
	?>

	<?php
	if( $this->input->get_post('service_filter') != '' ){ ?>
			jQuery("#service_filter").click();

	<?php
	}
	?>

	<?php
	if( $this->input->get_post('state_filter') != '' ){ ?>
			jQuery("#state_filter").click();

	<?php
	}
	?>
	


    // invoice script
    $("#btn_do_invoice").click(function() {

		swal({
			title: "Warning!",
			text:  "Are you sure you want to create invoice?",
			icon: "warning",
			buttons: true,
			dangerMode: true,
			type: "warning",
			showCancelButton: true,
			cancelButtonClass: "btn-danger",
			confirmButtonClass: "btn-success",
			closeOnConfirm: false,
			confirmButtonText: "Yes, Proceed",
			cancelButtonText: "No, Cancel!",
			closeOnCancel: true
			}, function(isConfirm) {
				if (isConfirm) {
					swal.close();
					$('#load-screen').show();

					//console.log("HERE!");
					//location.reload();
					var job_id = new Array();
					$(".invoice_chk_box:checked").each(function() {
						job_id.push($(this).val());
					});

					$.ajax({
						type: "POST",
						url: '<?php echo base_url(); ?>jobs_ajax/tbi_mod/ajax_do_invoice',
						data: {
							job_id: job_id
						}
						}).done(function(ret) {
						//window.location = "to_be_invoiced_jobs.php";
						//location.reload();
						setTimeout(function(){  // Beginning of code that should run AFTER the timeout
						swal({
							title: "Success!",
							text: "Invoice successfully created!",
							type: "success",
							confirmButtonClass: "btn-success",
							showConfirmButton: false,
							timer: <?php echo $this->config->item('timer') ?>
						});
						$('#load-screen').hide();
						location.reload();
					}, 5000);

					});
				}
			});
		/*
        if (confirm("Are you sure you want to continue?") == true) {

            var job_id = new Array();
            $(".invoice_chk_box:checked").each(function() {
                job_id.push($(this).val());
            });

            console.log(job_id);
			
            $.ajax({
				type: "POST",
				url: '<?php echo base_url(); ?>jobs_ajax/tbi_mod/ajax_do_invoice',
				data: {
					job_id: job_id
				}
				}).done(function(ret) {
				//window.location = "to_be_invoiced_jobs.php";
				location.reload();

            });

        }
		*/

    });


    // invoice script
    $("#btn_create_job").click(function() {

		swal({
			title: "Warning!",
			text:  "Are you sure you want to create job?",
			icon: "warning",
			buttons: true,
			dangerMode: true,
			type: "warning",
			showCancelButton: true,
			cancelButtonClass: "btn-danger",
			confirmButtonClass: "btn-success",
			closeOnConfirm: false,
			confirmButtonText: "Yes, Proceed",
			cancelButtonText: "No, Cancel!",
			closeOnCancel: true
			}, function(isConfirm) {
				if (isConfirm) {
					swal.close();
					$('#load-screen').show();

					console.log("HERE!");
					//location.reload();
					var item_count = $(".create_job_chk:checked").length;
					var i = 0;

					$(".create_job_chk:checked").each(function() {

						var job_id = $(this).val();
						var property_id = $(this).parents("tr:first").find(".property_id").val();
						var agency_id = $(this).parents("tr:first").find(".agency_id").val();
						var ajt_id = $(this).parents("tr:first").find(".ajt_id").val();
									
						$.ajax({
							type: "POST",
							url: '<?php echo base_url(); ?>jobs_ajax/tbi_mod/ajax_create_job',
							data: { 
								property_id: property_id,
								alarm_job_type_id: ajt_id,
								job_type: 'Annual Visit',
								price: 0,
								staff_id: <?php echo $this->session->staff_id; ?>,
								agency_id: agency_id
							},
							success: function(ret){  
								i++;
								if (i == item_count) {
									console.log("Jobs Created!");
									//window.location = "to_be_invoiced?create_job_success=1";
								}
							}
						});	

					});
					
					setTimeout(function(){  // Beginning of code that should run AFTER the timeout
						swal({
							title: "Success!",
							text: "Jobs successfully created!",
							type: "success",
							confirmButtonClass: "btn-success",
							showConfirmButton: false,
							timer: <?php echo $this->config->item('timer') ?>
						});
						$('#load-screen').hide();
					}, 5000);
				}
			});
		
		/*
        if (confirm("Are you sure you want to create job?") == true) {}
		*/
    });

    $("#invoice_check_all").click(function() {

        if ($(this).prop("checked") == true) {
            $(".invoice_chk_box:visible").prop("checked", true);
            $(".invoice_chk_box:visible").parents("tr").addClass("yello_mark");
            $("#invoice_btn_div").show();
        } else {
            $(".invoice_chk_box:visible").prop("checked", false);
            $(".invoice_chk_box:visible").parents("tr").removeClass("yello_mark");
            $("#invoice_btn_div").hide();
        }

    });

    $(".invoice_chk_box").click(function() {

        var chked = $(".invoice_chk_box:checked").length;

        if ($(this).prop("checked") == true) {
            $(this).parents("tr:first").addClass("yello_mark");
        } else {
            $(this).parents("tr:first").removeClass("yello_mark");
        }


        if (chked > 0) {
            $("#invoice_btn_div").show();
        } else {

            $("#invoice_btn_div").hide();
        }

    });

    $("#create_job_check_all").click(function() {
        if ($(this).prop("checked") == true) {
            $(".create_job_chk:visible").prop("checked", true);
            $(".create_job_chk:visible").parents("tr").addClass("yello_mark");
            //$("#create_job_btn_div").show();
        } else {
            $(".create_job_chk:visible").prop("checked", false);
            $(".create_job_chk:visible").parents("tr").removeClass("yello_mark");
            //$("#create_job_btn_div").hide();
        }

        var chked = $(".create_job_chk:checked").length;

        if (chked > 0) {
            $("#create_job_btn_div").show();
        } else {

            $("#create_job_btn_div").hide();
        }

    });

    $(".create_job_chk").click(function() {

        var chked = $(".create_job_chk:checked").length;

        if ($(this).prop("checked") == true) {
            $(this).parents("tr:first").addClass("yello_mark");
        } else {
            $(this).parents("tr:first").removeClass("yello_mark");
        }


        if (chked > 0) {
            $("#create_job_btn_div").show();
        } else {

            $("#create_job_btn_div").hide();
        }

    });

	$("body").on("change", "#jtable_length select", function() {
		$("#pagi_count").css("display", "none");
	});

	$("body").on("click", ".sorting", function() {
		$("#pagi_count").css("display", "none");
	});

	//Replace string Items to Entries by default
	const replaceText = $("#pagi_count").text();
  	const newText = replaceText.replace("Items", "Entries");
	$("#pagi_count").text(newText);

});
</script>

<script>
jQuery(document).ready(function(){
	var page = 0;
	
	// var table = $('#jtable').DataTable();
	var table = $('#jtable').DataTable({
        
		// 'pageLength': 10,
		"lengthMenu": [10, 25, 50, 100, 250],
		'lengthChange': true,
		"order": [[ 0, 'asc' ]],
		'columnDefs': [
			{
				'targets': [11,12],
				'orderable': false
			}
		],
		'pagingType': 'simple_numbers',
		"sDom": '<"H"lr><"clear">t<"F"pi>',
		"language": {
			"paginate": {
				"previous": "Prev"
			}
		}, 
		"searching": false
	});
  
	table.on('order', function() {
		if (table.page() !== page) {
		table.page(page).draw('page');
		}
	});
	
	table.on('page', function() {
		page = table.page();
	});

});
</script>

<?php
	function getJobTypeAbbrv($jt){
	
		// job type
		switch($jt){
			case 'Once-off':
				$jt = 'Once-off';
			break;
			case 'Change of Tenancy':
				$jt = 'COT';
			break;
			case 'Yearly Maintenance':
				$jt = 'YM';
			break;
			case 'Fix or Replace':
				$jt = 'FR';
			break;
			case '240v Rebook':
				$jt = '240v';
			break;
			case 'Lease Renewal':
				$jt = 'LR';
			break;
		}
		return $jt;
		
	}
?>