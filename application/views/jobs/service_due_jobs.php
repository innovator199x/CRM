<?php
  $export_links_params_arr = array(
	'job_type_filter' => $this->input->get_post('job_type_filter'),
	'service_filter' => $this->input->get_post('service_filter'),
	'state_filter' => $this->input->get_post('state_filter'),
	'region_filter_state' =>  $this->input->get_post('region_filter_state'),
	'agency_filter' => $this->input->get_post('agency_filter'),
	'date_filter' => $this->input->get_post('date_filter'),
	'sub_region_ms' => $this->input->get_post('sub_region_ms'),
	'search_filter' => $this->input->get_post('search_filter')
);
$export_link_params = '/jobs/export_service_due_jobs/?status=completed&'.http_build_query($export_links_params_arr);
?>
<style>
	.col-mdd-3{
		max-width: 12.1%;
	}
	pre{
		line-height: normal;
	}
</style>

<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/jobs/service_due"
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
		echo form_open('/jobs/service_due',$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-lg-10 col-md-12 columns">
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
							<label for="state"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></label>
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
							<input name="date_filter" placeholder="ALL" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text" value="<?php echo $this->input->get_post('date'); ?>">
						</div>

						<div class="col-mdd-3">
							<label for="search">Phrase</label>
							<input type="text" placeholder="ALL" name="search_filter" class="form-control" value="<?php echo $this->input->get_post('search_filter'); ?>" />
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
								<a href="<?php echo $export_link_params ?>" target="blank">
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

	<?php 

		if( $this->session->flashdata('nlm_chk_flag')==1){
			
			echo "<div class='text-center'><strong style='color:#fa424a;'>These Properties cannot be NLM because it has active jobs:</strong><ul style='margin-bottom:10px;'>";
				foreach( $this->session->flashdata('propArray') as $prop_data ){
					echo "<li><a href='/properties/view_property_details/{$prop_data['prop_id']}'>{$prop_data['prop_name']}</a></li>";
				}
			echo "</ul></div>";
		}
		
	?>

		<?php echo form_open(base_url('/jobs/update_pending_jobs'),"id=service_due_form"); ?>
			<div class="table-responsive">
				<table class="table table-hover main-table">
					<thead>
						<tr>
							<th>Age</th>
							<th>Job Type</th>
							<th>Service</th>
							<th>Price</th>
							<th>Address</th>
							<th><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></th>
							<th>Agency</th>
                            <th>Job#</th>
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
						?>
						<tr>
							<td>
								<?php 	echo $this->gherxlib->getAge($list_item['j_created']);  ?>
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
                            <?php echo "$".$list_item['j_price']; ?>
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
							<td class="<?php echo ( $list_item['priority'] > 0 )?'j_bold':null; ?>">
							<?php echo $list_item['agency_name']." ".( ( $list_item['priority'] > 0 )?' ('.$list_item['abbreviation'].')':null ); ?>
                            </td>
                            <!-- <td>
                            <?php echo '<a href="/jobs/view_job_details/'.$list_item["jid"].'">'.$list_item["jid"].'</a>'; ?>
                            </td> -->

                            <td>
							<?php echo $this->gherxlib->crmLink('vjd',$list_item['jid'],$list_item['jid']);?>
							</td>
                            <td>
								<input type="hidden" name="prop_id[]" value="<?php echo $list_item['prop_id'] ?>">
								<div class="checkbox">
									<?php
										if($list_item['auto_renew']!=0){
									?>
										<input class="chk_job" name="chk_job[]" type="checkbox" id="check-<?php echo $list_item["jid"] ?>" data-jobid="<?php echo $list_item["jid"]; ?>" value="<?php echo $list_item["jid"]; ?>">
									<?php 
										}
									?>
										<label for="check-<?php echo $list_item["jid"] ?>">&nbsp;</label>
								</div>
							</td>
                            
						</tr>
                        <?php endforeach;
                        }else{
                            echo "<tr><td colspan='9'>No Data</td></tr>";
                        }
                        ?>
					</tbody>

				</table>
                <div id="mbm_box" class="text-right nlm_div">	
					<input type="hidden" id="action" name="action" value="">
					<!-- <button data-value="No Longer Manage" id="noLongerManage_btn" type="button" class="btn">No Longer Manage</button> -->
					<button data-value="No Longer Manage" id="noLongerManage" type="button" class="btn">No Longer Manage</button>
					<br>
					<div class="nlm_reason_div" style="display: none; width: 230px; float: right; margin-top: 5px">

						<select name="reason_they_left" class="form-control mb-2 reason_they_left">
							<option value="">---Select Reason---</option>       
							<?php
							// get leaving reason                                                
							$lr_sql = $this->db->query("
							SELECT *
							FROM `leaving_reason`
							WHERE `active` = 1
							AND `display_on` IN(2,4)
							ORDER BY `reason` ASC
							");   
							foreach( $lr_sql->result() as $lr_row ){ ?>
								<option value="<?php echo $lr_row->id; ?>"><?php echo $lr_row->reason; ?></option> 
							<?php
							}                                         
							?> 
							<option value="-1">Other</option> 
						</select>

						<textarea class="form-control addtextarea mb-2 other_reason" style="display: none;" name="other_reason" placeholder="Other Reason"></textarea>

						
						<button class="btn btn-sm btn-success verify_nlm_btn_v2" id="noLongerManage_btn" data-value="No Longer Manage">Submit</button>&nbsp;
					

					</div>
				</div>
			</div>
			</form>

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

	<h4>Service Due Jobs</h4>
	<p>
	This page shows jobs that are for that have a status of Pending. 
	These jobs are waiting to be approved by the agency or will be auto-renewed on the first day of the month
	</p>
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
				searched_val: searched_val,
				isServiceDuePage: 1
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

		//success/error message sweel alert pop  start
		<?php if( $this->session->flashdata('status') &&  $this->session->flashdata('status') == 'success' ){?>
            swal({
                title: "Success!",
                text: "<?php echo $this->session->flashdata('success_msg') ?>",
                type: "success",
                confirmButtonClass: "btn-success",
				showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                timer: <?php echo $this->config->item('timer') ?>
            });
        <?php }else if(  $this->session->flashdata('status') &&  $this->session->flashdata('status') == 'error'  ){ ?>
            swal({
                title: "Error!",
                text: "<?php echo $this->session->flashdata('error_msg') ?>",
                type: "error",
                confirmButtonClass: "btn-danger"
            });
        <?php } ?>
        //success/error message sweel alert pop  end

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

		//show textarea other reason
		$('.reason_they_left').on('change',function(){	
			var reason_they_left = $('.reason_they_left').val();
			if( reason_they_left == -1 ){
				jQuery(".other_reason").show();
			}else{
				jQuery(".other_reason").hide();
			}            
		});

		//show the list of dropdown reason
		$('#noLongerManage').click(function(e){
			
			var nlm_process_btn_dom = jQuery(this);
			var nlm_div = nlm_process_btn_dom.parents(".nlm_div");

			nlm_div.find(".nlm_reason_div").toggle();
		})

		$('#noLongerManage_btn').click(function(e){
			e.preventDefault();
			var obj = $(this);
			var form = $('#service_due_form');
			var checkLength = form.find('.chk_job:checked').length;
			var btnVal = obj.attr('data-value');
			var reason_they_left = $('.reason_they_left').val();
			var other_reason = $('.other_reason').val();
			var error = '';

			//set action
			$('#action').val(btnVal);
		
			//validation
			if(checkLength == 0){
				swal('','Please select Job/Property','error');
				return false;
			}
			// validation
			if( reason_they_left == '' ){
				error += "'Reason They Left' is required\n";
				swal('','Reason They Left is required');
				return false;
			}else{
				if( reason_they_left == -1 && other_reason == '' ){
					error += "'Other Reason' is required\n";
					swal('','Other Reason\ is required');
					return false;
				}
			} 

			if( error != "" ){ // error

			swal('', error, 'error'); 

			}else{
			
				swal(
						{
							title: "",
							text: "Are you sure you want to mark properties NLM?",
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
								swal.close();
								$('#load-screen').show(); //show loader
								form.submit();
								return false;
							}else{
								return false;
							}
							
						}
						
					);
			}
			
			

			
		})

	})
</script>