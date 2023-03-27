<style>
	.col-mdd-3{
		max-width: 14%;
	}
	.new_tenant_inline_section{width:900px;}
	.new_tenant_fields_box{
		margin-bottom: 10px;
	}
	tr.add_new_tenant_plus_btn td{
		border:0px;
	}
	.add_tenan_new_row_btn{
		margin-bottom:3px;
	}
	.div_change_tbb{
		margin-left:40px!important;
	}
</style>
<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => 'escalate',
			'link' => "/jobs/escalate"
		),
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/jobs/escalate_jobs/{$this->uri->segment(3)}"
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
		echo form_open('/jobs/escalate_jobs/'.$this->uri->segment(3),$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-md-10 columns">
					<div class="row">

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
							<label for="reason_filter">Reason</label>
							<select id="reason_filter" name="reason_filter" class="form-control">
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
							<label for="date">Date</label>
							<input name="date" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text" placeholder="ALL" value="<?php echo $this->input->get_post('date'); ?>">
						</div>

						<div class="col-md-2">
							<label for="phrase_select">Phrase</label>
							<input type="text" name="search" class="form-control" placeholder="ALL" value="<?php echo $this->input->get_post('search'); ?>" />
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
                                <a href="/jobs/export_escalate_jobs?<?php echo "jobType={$this->input->get_post('jobType')}&service={$this->input->get_post('service')}&reason={$this->input->get_post('reason')}&region={$this->input->get_post('region')}&agency_id={$this->uri->segment(3)}&date={$this->input->get_post('date')}&search={$this->input->get_post('search')}" ?>" target="blank">
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
				<table class="table table-hover main-table" id="sortTable">
					<thead>
						<tr>
							<th>Age</th>
							<th class="no-sort">Job Type</th>
							<th class="no-sort">Service</th>
							<th class="no-sort">Address</th>
							<th class="no-sort">Agency</th>
							<th class="no-sort">Phone</th>
							<th class="no-sort">Job#</th>
							<th class="no-sort">Last Contact</th>
							<th >Reason</th>
							<th class="no-sort">Response</th>
							<th class="no-sort" style="text-align: center;">STR</th>
							<th class="no-sort">Connected Via</th>
							<th class="no-sort">Show</th>
							<th class="check_all_td no-sort">
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
							
							$tr_bgcolor = "";
							$today = date('Y-m-d');
							$status_changed_timestamp = date('Y-m-d', strtotime($list_item['status_changed_timestamp']));
							if($status_changed_timestamp == $today){ //recently added escalate > today
								$tr_bgcolor = "#fffca3";
							}
						?>
						<tr class="escalate_tr" style="background-color:<?php echo $tr_bgcolor ?>" data-aa="<?php echo $list_item['status_changed_timestamp'] ?>">
							<td>
								<?php echo $this->gherxlib->getAge($list_item['j_created']); ?>
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
								<?php 
								/*
								echo "<a href='/properties/view_property_details/".$list_item['prop_id']."'>".$list_item['p_address_1']." ".$list_item['p_address_2']." ".$list_item['p_address_3'].'</a>' 
								*/
								$prop_address = $list_item['p_address_1']." ".$list_item['p_address_2'].", ".$list_item['p_address_3'];
								echo $this->gherxlib->crmLink('vpd',$list_item['prop_id'],$prop_address);
								?>
							</td>
							<td>
                            	<?php echo $list_item['agency_name']; ?>
							</td>
							<td>
								<?php echo $list_item['a_phone']; ?>
							</td>
							<td>
								<?php
								/*
								 echo '<a href="/jobs/view_job_details/'.$list_item["jid"].'">'.$list_item["jid"].'</a>'; 
								 */
								echo $this->gherxlib->crmLink('vjd',$list_item['jid'],$list_item['jid']);
								 ?>
							</td>
							<td>
								<?php  
									$lc = $this->gherxlib->getLastContact($list_item['jid'])->row_array();
									echo ( $this->system_model->isDateNotEmpty($lc['eventdate']) )?$this->system_model->formatDate($lc['eventdate'],'d/m/Y'):'';
								?>
							</td>
							<td>
								<?php
									 $getEscReason =  $this->gherxlib->getEscalateReason($list_item['jid']); 
									 foreach($getEscReason->result_array()as $row){
								?>
										<img class="escalate_icons" data-toggle="tooltip" title="<?php echo $row['reason']; ?>" src="/images/escalate_jobs/<?php echo $row['icon']; ?>" />
										<p style="display:none;"><?= $row['reason']?></p>
								<?php 		 
									 }
								?>

							</td>
							
                            <td>
							<?php
							if($list_item['agency_approve_en']==1){
								$approv_txt = 'Allow';
								$approv_clr = 'green';
							}else if( is_numeric($list_item['agency_approve_en']) && $list_item['agency_approve_en']==0){
								$approv_txt = 'Deny';
								$approv_clr = 'red';
							}else{
								$approv_txt = '';
								$approv_clr = '';
							}
							?>
							<span style="color:<?php echo $approv_clr; ?>"><?php echo $approv_txt; ?></span>
							</td>

							<td>

								<?php 

									$jparams = array(
										'sel_query' => "tr.date as tr_date,tr.tech_run_id",
										'join_table' => array('tech_run_rows','staff_accounts','jobs','property','agency'),
										'row_id_type' => 'job_id',
										'job_id' => $list_item['jid'],
										// 'date'=> date('Y-m-d'),
										'date_onwards' => 1,
										'hidden'=>0,
										'del_job'=>0,
										'tr_country_id' => $this->config->item('country'),
										'a_country_id'=> $this->config->item('country')
									);
									$tech_run_sql = $this->system_model->getTech_run($jparams);

									$tr_links_arr = array();
									if( $tech_run_sql->num_rows()>0 ){

										foreach($tech_run_sql->result_array() as $other_str){
											$tr_links_arr[] = '<a href="/tech_run/run_sheet_admin/'.$other_str['tech_run_id'].'">'.date('d/m',strtotime($other_str['tr_date'])).'</a>';	
										}

									}

									echo implode(", ",$tr_links_arr);

								?>

							</td>
							<td>
								<?php 
							
									 // Pme supplier check
									 if( $list_item['pme_prop_id'] != ''  ){
										echo "<a target='_blank' href='{$this->config->item('crm_link')}/view_property_details.php?id={$list_item['prop_id']}&amp;tab=1'><img height='30' class='reason_icon' src='/images/third_party/Pme.png' /></a>";                
									}
						
									// Palace API check
									if( $list_item['palace_prop_id'] != '' ){
										echo  "<a target='_blank' href='{$this->config->item('crm_link')}/view_property_details.php?id={$list_item['prop_id']}&amp;tab=1'><img height='30' class='reason_icon' src='/images/third_party/Palace.png' /></a>";                
									}

								?>
							</td>	
							<td class="escalate_td">
								<input class="prop_id" type="hidden" value="<?php echo $list_item['prop_id'] ?>">
								<button  type="button" class="btn btn-sm escalate_show_btn">SHOW</button>


								<!-- Fancybox Start -->
								<a href="javascript:;" class="fb_trigger" style="display:none;" data-fancybox data-src="#fancy_gherx<?php echo $list_item['jid'] ?>">Trigger the fancybox</a>
							
								<div id="fancy_gherx<?php echo $list_item['jid'] ?>" class="fancybox" style="display:none;" >

									<div class="loader_wrapper_pos_rel">
										<div class="loader_block_v2" style="display: none;"> <div id="div_loader"></div></div>
										<h4>Tenants</h4>
										<div class="tenants_ajax_box" style="width:900px;"></div>

										<div class="new_tenant_inline_section">
											<form id="new_tenants_form">
												<button id="plus_new_tenant_btn" class="btn btn-inline btn-danger-outline" type="button">
													<span class="glyphicon glyphicon-plus"></span> <span class="btn_inline_text">Tenant</span>
												</button>
												<div class="new_tenant_fields_box" style="display:none;">
													<table class="table tenant_table">
														<thead>
															<tr>
																<th>First Name</th>
																<th>Last Name</th>
																<th>Mobile</th>
																<th>Landline</th>
																<th>Email</th>
																<th>&nbsp;</th>
															</tr>
														</thead>
														<tbody>
															<tr class="tenants-row">
																<td>
																	<div class="form-group"><input placeholder="First Name" data-validation="[NOTEMPTY]" data-validation-label="First Name" type="text" class="form-control new_tenant_fname" name="new_tenant_fname[]"></div>
																</td>
																<td>
																	<div class="form-group"><input placeholder="Last Name" type="text" class="form-control new_tenant_lname" name="new_tenant_lname[]"></div>
																</td>
																<td>
																	<div class="form-group"><input  type="text" class="form-control tenant_mobile new_tenant_mobile" name="new_tenant_mobile[]"></div>
																</td>
																<td>
																	<div class="form-group"><input type="text" class="form-control phone-with-code-area-mask-input new_tenant_landline" name="new_tenant_landline[]"></div>
																</td>
																<td>
																	<div class="form-group"><input placeholder="Email"  type="text" class="form-control new_tenant_email" name="new_tenant_email[]"></div>
																</td>
																<td>&nbsp;</td>
															</tr>

															<tr class="add_new_tenant_plus_btn">
																<td colspan="6">
																<div class="add_tenan_new_row_section">
																	<a class="add_tenan_new_row_btn btn btn-sm" href="#" class="btn btn-sm"><span class="glyphicon glyphicon-plus"></span> Tenant</a>
																</div>
																</td>
															</tr>
														</tbody>
													</table>
												
												</div>
											</form>
										</div>

									</div>

									<div>
										<button data-prop_id="<?php echo $list_item['prop_id'] ?>" data-job_id="<?php echo $list_item['jid'] ?>" type="button" class="btn btn_process"><i class="fa fa-check-square-o"></i> Process</button>
									</div>

								</div>
								<!-- Fancybox END -->


							</td>
							<td>
								<input type="hidden" class="job_type" value="<?php echo $list_item['j_type']; ?>" />
								<input type="hidden" class="is_eo" value="<?php echo $list_item['is_eo']; ?>" />
								<div class="checkbox">
									<input class="chk_job" name="chk_job[]" type="checkbox" id="check-<?php echo $list_item["jid"] ?>" value="<?php echo $list_item['jid']; ?>">
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
								<div class="gbox div_change_tbb">
									<button id="btn_change_to_tbb" type="button" class="btn">Change to TBB</button>
								</div>
							</div>
				</div>
			</div>

			<nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>

			<div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>

		</div>
	</section>

</div>
<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4>Escalate Jobs</h4>
	<p>All jobs in this page require us to get further information before we can proceed. Eg We need updated tenant details or Tenant will not allow us to enter etc</p>
<pre><code><?php echo $sql_query; ?></code></pre>
</div>
<!-- Fancybox END -->

<script type="text/javascript">

	function getTenants(obj) {

		var property_id = obj.parents("td.escalate_td:first").find(".prop_id").val();
		var tenants_block = obj.parents("td.escalate_td:first").find(".tenants_ajax_box");
		var loader_block = obj.parents("td.escalate_td:first").find(".loader_block_v2");
		var fb_trigger = obj.parents("td.escalate_td:first").find(".fb_trigger");

		// clear all tenants div
		$('.tenants_ajax_box').empty();

		//load tenants ajax box (via ajax)
		tenants_block.load('/properties/get_tenants_ajax_no_add_tenant_section', {
				prop_id: property_id
			}, function(response, status, xhr) {

				$('.loader_block_v2').hide();
				$('[data-toggle="tooltip"]').tooltip(); //init tooltip
				phone_mobile_mask(); //init phone/mobile mask
				mobile_validation(); //init mobile validation
				phone_validation(); //init phone validation
				add_validate_tenant(); //init new tenant validation

				fb_trigger.click(); // trigger fancybox popup
				$('#load-screen').hide();
			}

		);

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

	// escalate reason
	function run_ajax_esc_reason_filter(){

	var json_data = <?php echo $reason_filter_json; ?>;
	var searched_val = '<?php echo $this->input->get_post('reason_filter'); ?>';

	jQuery('#reason_filter').next('.mini_loader').show();
	jQuery.ajax({
		type: "POST",
			url: "/sys/header_filters",
			data: { 
				rf_class: 'jobs',
				header_filter_type: 'escalate_reason',
				json_data: json_data,
				searched_val: searched_val
			}
		}).done(function( ret ){	
			jQuery('#reason_filter').next('.mini_loader').hide();
			$('#reason_filter').append(ret);
		});
				
	}


	  function insertNewTenantRow(obj){

		var htm_content = '<tr class="tenants-row">'+
		'<td>'+
		'<div class="form-group"><input placeholder="First Name" data-validation="[NOTEMPTY]" data-validation-label="First Name" type="text" class="form-control new_tenant_fname" name="new_tenant_fname[]"></div>' +
		'</td>'+
		'<td>'+
		'<div class="form-group"><input placeholder="Last Name" type="text" class="form-control new_tenant_lname" name="new_tenant_lname[]"></div>' +
		'</td>'+
		'<td>'+
		'<div class="form-group"><input  type="text" class="form-control tenant_mobile new_tenant_mobile" name="new_tenant_mobile[]"></div>' +
		'</td>'+
		'<td>'+
		'<div class="form-group"><input type="text" class="form-control phone-with-code-area-mask-input new_tenant_landline" name="new_tenant_landline[]"></div>' +
		'</td>'+
		'<td>'+
		'<div class="form-group"><input placeholder="Email"  type="text" class="form-control new_tenant_email" name="new_tenant_email[]"></div>' +
		'</td>'+
		'<td>'+
		'<a data-toggle="tooltip" title="Remove" class="del_tenant_row" href="#"><span class="font-icon font-icon-trash"></span></a>' +
		'</td>'+
		'</tr>';
		obj.parents('.fancybox-content').find('.add_new_tenant_plus_btn').before(htm_content);
		phone_mobile_mask();
		//mobile_validation();
		//phone_validation();

		}



	jQuery(document).ready(function() {


		// run headler filter ajax
		run_ajax_job_filter();
		run_ajax_service_filter();
		run_ajax_esc_reason_filter();

		// load tenant section via ajax
        jQuery('.escalate_show_btn').click(function(e) {

			e.preventDefault();

			var obj = jQuery(this);
			var prop_id = obj.parents('.escalate_td').find('input.prop_id').val();
			var tenant_box = obj.parents('.escalate_td').find(".tenants_ajax_box");
			
			//show looader
			$('#load-screen').show();

			getTenants(obj);
			//reset(obj);

		});


		jQuery(".btn_process").click(function(){
		
			var obj = jQuery(this);
			
			var job_id = obj.attr('data-job_id');
			var prop_id = obj.attr('data-prop_id');
			
			var tenants_arr = [];

			obj.parents(".fancybox-content").find(".new_tenant_fname").each(function () {

				var obj2 = jQuery(this)
                var row = obj2.parents(".tenants-row");

				var new_tenant_fname = row.find(".new_tenant_fname").val();
				var new_tenant_lname = row.find(".new_tenant_lname").val();
				var new_tenant_mobile = row.find(".new_tenant_mobile").val();
				var new_tenant_landline = row.find(".new_tenant_landline").val();
				var new_tenant_email = row.find(".new_tenant_email").val();
				
				if(new_tenant_fname!="" || new_tenant_lname!=""){
					var json_data = {
						new_tenant_fname: new_tenant_fname,
						new_tenant_lname: new_tenant_lname,
						new_tenant_mobile: new_tenant_mobile,
						new_tenant_landline: new_tenant_landline,
						new_tenant_email: new_tenant_email
					};
					
					var json_str = JSON.stringify(json_data);
					tenants_arr.push(json_str);
				}
				
			})

			
			
			
			// invoke ajax
			jQuery.ajax({
				type: "POST",
				url: "/jobs/ajax_process_escalate_jobs",
				data: { 
					job_id: job_id,
					prop_id: prop_id,
					tenants_arr:tenants_arr
				}
			}).done(function( ret ){
				
				swal({
					title:"Success!",
					text: "Process Success",
					type: "success",
					showCancelButton: false,
					confirmButtonText: "OK",
					closeOnConfirm: false,

				},function(isConfirm){
				if(isConfirm){ 
					location.reload();
					}
				});
				
			});
		
		
		});

		

		// REGION FILTER START
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


		// region filter selection, cant trigger without the timeout, dunno why :( 
		<?php
		if( !empty($this->input->get_post('sub_region_ms')) ){ ?>
			setTimeout(function(){ 
				jQuery("#region_filter_state").click();
			}, 500);		
		<?php
		}
		?>

		// toogle new tenant div/fields
		$(document).on('click','#plus_new_tenant_btn',function(e){
			e.preventDefault();
			e.stopImmediatePropagation();
			var obj = $(this);
			var btnName = obj.find('.btn_inline_text');
			var btnIcon = obj.find('.glyphicon');
			obj.parents('.fancybox-content').find('.new_tenant_fields_box').slideToggle(function(){
					if(btnName.html()=="Tenant"){
						btnName.html("Cancel");
						btnIcon.removeClass('glyphicon-plus').addClass('glyphicon-minus');
					}else{
						btnName.html("Tenant");
						btnIcon.removeClass('glyphicon-minus').addClass('glyphicon-plus');
					}
			});
		});

		//add new tenant row
		$('.add_tenan_new_row_btn').on('click',function(e){
			var obj = $(this);
			e.preventDefault();   
			insertNewTenantRow(obj);
		})

		 // DELETE tenants row
		 jQuery(document).on('click','.del_tenant_row',function(e){
			e.preventDefault();
			var obj = $(this);
			obj.parents('.tenants-row').remove();
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
		});

		$('.chk_job').on('change',function(){
			var obj = $(this);
			var isLength = $('.chk_job:checked').length;
			var divbutton = $('#mbm_box');

			if(obj.is(':checked')){
				divbutton.show();
			}else{
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
						setTimeout(function(){ window.location='/jobs/escalate_jobs/<?php echo $this->uri->segment(3); ?>'; }, <?php echo $this->config->item('timer') ?>);
							
					});	

				}				

			}	

		});
		
		//Changed to TBB
		$('#btn_change_to_tbb').click(function(e){
			var job_id = new Array();
			var checkLength = $('.chk_job:checked').length;
			var error = "";

			//push job_id array
			jQuery(".chk_job:checked").each(function(){
				job_id.push(jQuery(this).val());
			});
			
			//validation
			if(checkLength == 0){
				error += "Please select/tick Job\n";
			}

			if( error != "" ){
				swal('',error,'error');
				return false;
			}else{
				$('#load-screen').show(); //show loader
				jQuery.ajax({
					type: "POST",
					url: "/jobs/ajax_move_to_maps",
					data: {
						job_id: job_id,
						page_type: 'escalate'
					}
				}).done(function( ret ){
					$('#load-screen').hide(); //hide loader
					swal({
						title:"Success!",
						text: "Changed to TBB success",
						type: "success",
						showCancelButton: false,
						confirmButtonText: "OK",
						closeOnConfirm: false,
						showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
						timer: <?php echo $this->config->item('timer') ?>

					});
					setTimeout(function(){ window.location='/jobs/escalate_jobs/<?php echo $this->uri->segment(3); ?>'; }, <?php echo $this->config->item('timer') ?>);
						
				});	
			}
		})

		$('#sortTable').DataTable({

			"ordering": true,
			columnDefs: [{
				orderable: false,
				targets: "no-sort"
			}],
			"paging": false,
			"info": false,
			"searching": false


		});

	});			

</script>