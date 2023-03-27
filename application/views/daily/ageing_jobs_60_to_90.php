<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/jobs/ageing_jobs_60_to_90"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);

	
	$export_links_params_arr = array(
		'job_type_filter' => $this->input->get_post('job_type_filter'),
		'state_filter' => $this->input->get_post('state_filter'),
		'agency_filter' => $this->input->get_post('agency_filter'),
		'region_filter_state' => $this->input->get_post('region_filter_state'),
		'agency_priority_filter' => $this->input->get_post('agency_priority_filter')
	);
	$export_link_params = '/jobs/ageing_jobs_60_to_90/?export=1&'.http_build_query($export_links_params_arr);
	?>

	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">
			<?php
		$form_attr = array(
			'id' => 'jform'
		);
		echo form_open('/jobs/ageing_jobs_60_to_90',$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-md-9 columns">
					<div class="row">

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
							<label>Job Type</label>
							<select id="job_type_filter" name="job_type_filter" class="form-control">
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
							<label>Agency</label>
							<select id="agency_filter" name="agency_filter" class="form-control">
								<option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>
						</div>

						<div class="col-mdd-3">
                            <label for="ht_select">Agency Priority</label><span>
                            <select id="agency_priority_filter" name="agency_priority_filter" class="form-control field_g2">
                                <option value="" <?php echo ($this->input->get_post('agency_priority_filter') == "") ? "selected" : ""; ?>>ALL</option>
                                <option value="0" <?php echo ($this->input->get_post('agency_priority_filter') === "0") ? "selected" : ""; ?>>Regular</option>
                                <option value="1" <?php echo ($this->input->get_post('agency_priority_filter') === "1") ? "selected" : ""; ?>>HT</option>
                                <option value="2" <?php echo ($this->input->get_post('agency_priority_filter') === "2") ? "selected" : ""; ?>>VIP</option>
                                <option value="3" <?php echo ($this->input->get_post('agency_priority_filter') === "3") ? "selected" : ""; ?>>HWC</option>
                            </select>
                            <div class="mini_loader"></div>
                        </div>

						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<button type="submit" class="btn btn-inline">Search</button>
						</div>
						
					</div>

				</div>
				<div class="col-md-3 columns">
                        <section class="proj-page-section float-right">
                            <div class="proj-page-attach">
                                <i class="fa fa-file-excel-o"></i>
                                <p class="name">View Jobs 60-90</p>
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
	<p class="text-center"><strong><?php echo "<span class='text-green'>{$booked_count}</span>/{$total_rows} Booked" ?></strong></p>
	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<table class="table table-hover main-table">
					<thead>
						<tr>
							<th>Date</th>
							<th>Age</th>
							<th>Job Type</th>
							<th>Service</th>	
							<th>Address</th>
							<th><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></th>
							<th>Agency</th>
							<th>Allow EN</th>
							<th>Job#</th>
							<th>Preferred Time</th>
							<th>OOTH</th>
							<th>Booking</th>
							<th>Access Notes</th>	
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
						foreach($lists->result_array() as $list_item) {

						$params = array(
							'sel_query' => "sr.subregion_name as postcode_region_name, sr.sub_region_id as postcode_region_id",
							'postcode' => $list_item['p_postcode'],
						);
						$getRegion = $this->system_model->get_postcodes($params)->row();	
                        $row_bg = ($this->system_model->isDateNotEmpty($list_item['jdate'])?'red_hl':'');		

						?>
						<tr class="<?php echo $row_bg; ?>">
							<td><?php echo $this->customlib->isDateNotEmpty($list_item['jdate'])?date("d/m/Y",strtotime($list_item['jdate'])):''; ?></td>
							</td>
							<td>
							<?php 
								// Age
								$date1=date_create(date('Y-m-d',strtotime($list_item['jcreated'])));
								$date2=date_create(date('Y-m-d'));
								$diff=date_diff($date1,$date2);
								$age = $diff->format("%a");
								echo $age;
								//echo date("d/m/Y",strtotime($list_item['jcreated']));
							?>
							</td>
							<td><?php echo $this->gherxlib->getJobTypeAbbrv($list_item['job_type']); ?></td>
							<td><img data-toggle="tooltip" title="<?php echo $list_item['ajt_type'] ?>" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($list_item['jservice']); ?>" /></td>
							<td>
							<?php
								$prop_address = $list_item['p_address_1']." ".$list_item['p_address_2'].", ".$list_item['p_address_3'];
								echo $this->gherxlib->crmLink('vpd',$list_item['property_id'],$prop_address);
							?>
							</td>
							<td><?php echo $list_item['p_state']; ?></td>
							<td class="<?php echo ( $list_item['priority'] > 0 )?'j_bold':null; ?>">
							<?php echo $list_item['agency_name']." ".( ( $list_item['priority'] > 0 )?' ('.$list_item['abbreviation'].')':null ); ?>
							</td>
							<td><?php echo ( $list_item['allow_en'] == 1 )?'Yes':null; ?></td>
							<td><?php echo $this->gherxlib->crmLink('vjd',$list_item['jid'],$list_item['jid']);?></td>
							<td><?php echo $list_item['preferred_time']; ?></td>
							<td><?php echo ($list_item['out_of_tech_hours']==1)?'Yes':''; ?></td>
							<td>
                                <?php
                                $getStr = $this->system_model->getStrbyRegion($getRegion->postcode_region_id);
                                $fcount = 0;
                                foreach($getStr->result_array() as $str_row){
                                    $reg_arr = explode(",",$str_row['sub_regions']);
                                    if( in_array($getRegion->postcode_region_id, $reg_arr) ){
                                        echo ($fcount!=0)?', ':''; ?> 
                                        <a href="/tech_run/run_sheet_admin/<?php echo $str_row['tech_run_id'] ?>"><?php echo date('d/m',strtotime($str_row['date'])); ?></a><?php	
                                        $fcount++;
                                    }else{
                                        $no_set_date_flag = 1;
                                    }
                                }
                                ?>
                            </td>
							<td>
								<div class="pos-rel">
									<input data-jobid="<?php echo $list_item['jid'] ?>" class="form-control jcomments" type="text" name="jcomments" value="<?php echo $list_item['access_notes'] ?>">
									<i class="fa fa-check-circle text-green job-check-ok check_ok_ajax"></i>
								</div>
							</td>
							<td>
								<div class="checkbox">
									<input type="hidden" class="job_id" value="<?php echo $list_item['jid']; ?>" />
									<input class="chk_job" name="chk_job[]" type="checkbox" id="check-<?php echo $list_item["jid"] ?>" data-jobid="<?php echo $list_item["jid"]; ?>" value="<?php echo $list_item['jid']; ?>">
									<label for="check-<?php echo $list_item["jid"] ?>">&nbsp;</label>
								</div>
							</td>
						</tr>
					<?php }
					}else{
						echo "<tr><td colspan='12'>No Data</td></tr>";
					}
				 	?>
					</tbody>

				</table>
                
				<div class="float-right">
					<table id="assign_tech_tbl">
						<tr>
							<td>
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
							</td>
							<td><input name="assign_date" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="assign_date" type="text" placeholder="Date" ></td>
							<td><button id="assign_btn" type="button" class="btn">Assign</button></td>
						</tr>
					</table>
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

	<h4>Jobs 60-90 Days</h4>
	<p>
		This page shows all jobs between 60-90 days old
	</p>
	<pre>
<!-- <code>SELECT *, `j`.`id` AS `jid`, `j`.`created` AS `jcreated`, `j`.`date` AS `jdate`, `j`.`service` AS `jservice`, `p`.`property_id`, `p`.`address_1` AS `p_address_1`, `p`.`address_2` AS `p_address_2`, `p`.`address_3` AS `p_address_3`, `p`.`state` AS `p_state`, `p`.`postcode` AS `p_postcode`
FROM `jobs` AS `j`
LEFT JOIN `property` AS `p` ON j.`property_id` = p.`property_id`
LEFT JOIN `agency` AS `a` ON  p.`agency_id` = a.`agency_id`
LEFT JOIN `alarm_job_type` AS `ajt` ON j.`service` = ajt.`id`
WHERE `j`.`del_job` = 0
AND `p`.`deleted` = 0
AND `a`.`status` = 'active'
AND `a`.`country_id` = <?php echo COUNTRY ?>
AND (`j`.`status` = 'To Be Booked' OR `j`.`status` = 'Pre Completion' OR `j`.`status` = 'Booked' OR `j`.`status` = 'Escalate') AND CAST(j.`created` AS DATE) BETWEEN '<?php echo date('Y-m-d', strtotime("-90 days")); ?>' AND '<?php echo date('Y-m-d', strtotime("-60 days")); ?>' AND `p`.`holiday_rental` != 1
ORDER BY `j`.`created` ASC
LIMIT 50</code> -->
<code><?php echo $sql_query; ?></code>
	</pre>

</div>
<!-- Fancybox END -->

<style>
table#assign_tech_tbl{
	display: none;
}
table#assign_tech_tbl td{
	padding-right: 8px;
}
</style>

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


	function show_assign_tech_div(){

		var ticked_count = jQuery(".chk_job:checked").length;

		if( ticked_count > 0 ){
			jQuery("#assign_tech_tbl").show();
		}else{
			jQuery("#assign_tech_tbl").hide();
		}

	}


	jQuery(document).ready(function(){

		// run headler filter ajax
		run_ajax_job_filter();
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

		jQuery(".jcomments").on('change',function(){
			var obj = jQuery(this);
			var jcomments = obj.val();
			var job_id = obj.attr('data-jobid');
			
			$('#load-screen').show(); //show loader

			// ajax call	
			jQuery.ajax({
				type: "POST",
				url: "/jobs/updateJobAccessNotes",
				data: { 
					job_id: job_id,
					j_comments: jcomments
				}
			}).done(function( ret ){
				$('#load-screen').hide(); //hide loader
				// show tick
				obj.parents("tr:first").find(".job-check-ok").fadeIn();
				// fade
				setTimeout(function(){ 
					obj.parents("tr:first").find(".job-check-ok").fadeOut();
				}, 3000);
			});
		});


		// checkbox script
		// check all
		jQuery("#check-all").change(function(){

			var dom = jQuery(this);
			var is_ticked = dom.prop("checked");

			if( is_ticked == true ){
				jQuery(".chk_job").prop("checked",true)
			}else{
				jQuery(".chk_job").prop("checked",false)
			}

			show_assign_tech_div();

		});

		// single checkbox
		jQuery(".chk_job").change(function(){

			show_assign_tech_div();

		});

		// copied from TBB
		// assign techs to jobs
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



			// validations
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
						setTimeout(function(){ window.location='/jobs/ageing_jobs_60_to_90'; }, <?php echo $this->config->item('timer') ?>);
							
					});	

				}			

			}		
			
		});


	})
</script>