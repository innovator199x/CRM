<style>
	.col-mdd-3{
		max-width: 12.1%;
	}
	.a_link.asc{
		top:3px;
	}
	.a_link.desc{
		top:-3px;
	}
	#assign_date{
		width:120px;
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
			<?php
		$form_attr = array(
			'id' => 'jform'
		);
		echo form_open($uri,$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-lg-10 col-md-12 columns">
					<div class="row">

						<div class="col-md-2">
							<label>Approved Alarm</label>
							<select id="preferred_alarm_id" name="preferred_alarm_id" class="form-control">
								<option value="">---</option>
								<option value="10" <?php echo ( $this->input->get_post('preferred_alarm_id') == 10 )?'selected':null; ?>>Brooks</option>
								<option value="14" <?php echo ( $this->input->get_post('preferred_alarm_id') == 14 )?'selected':null; ?>>Cavius</option>
								<option value="22" <?php echo ( $this->input->get_post('preferred_alarm_id') == 22 )?'selected':null; ?>>Emerald</option>
							</select>							
						</div>

						<div class="col-md-3">
							<label for="state"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></label>
							<select id="state_filter" name="state_filter" class="form-control">
								<option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>
						</div>

						<div class="col-md-2">
						
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
								<a href="<?php echo $export_link; ?>" target="blank">
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
				<table class="table main-table">
					<thead>
						<tr>			
							<th>Job Type</th>
							<th>Age</th>
							<th>Service</th>							
							<th>Address</th>							
							<th><?php echo $this->gherxlib->getDynamicRegion($this->config->item('country')); ?></th>
							<th>Agency</th>
							<th>Preferred Alarm</th>
                            <th>Required # of Alarms</th>  
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
                        if(count($jobs)>0){
                        foreach($jobs as $list_item){				

						?>
						<tr class="tbl_list_tr <?php echo $row_color; ?>">							
							<td>
								<?php echo $this->gherxlib->crmLink('vjd',$list_item['jid'],$this->gherxlib->getJobTypeAbbrv($list_item['j_type']));?></td>							
							</td>
                            <td>
                                <?php echo $list_item['age']; ?>
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
							<!-- <a href="<?php echo base_url('/properties/view_property_details')."/".$list_item["prop_id"]?>"><?php echo $list_item['p_address_1']." ".$list_item['p_address_2']." ".$list_item['p_address_3']; ?></a>	</td> -->
							<?php 
								$prop_address = $list_item['p_address_1']." ".$list_item['p_address_2'].", ".$list_item['p_address_3'];
								echo $this->gherxlib->crmLink('vpd',$list_item['prop_id'],$prop_address);
							?>		
                            <td>
								<?php
									echo $list_item['region']['subregion_name'];
								?>
							</td>
							<td class="<?php echo ( $list_item['priority'] > 0 )?'j_bold':null; ?>">
								<?php echo $list_item['agency_name']." ".( ( $list_item['priority'] > 0 )?' ('.$list_item['abbreviation'].')':null ); ?>
                            </td>
							<td><?php echo $list_item['pref_alarm_make']; ?></td>
							<td><?php echo $list_item['qld_new_leg_alarm_num']; ?></td> 
							<td>
								<input type="hidden" class="is_dk_allowed" value="<?php echo $list_item['allow_dk']; ?>" />
								<input type="hidden" class="agency_id" value="<?php echo $list_item['a_id']; ?>" />
								<input type="hidden" class="agency_name" value="<?php echo $list_item['agency_name']; ?>" />
								<input type="hidden" class="job_type" value="<?php echo $list_item['j_type']; ?>" />
								<input type="hidden" class="no_dk" value="<?php echo $list_item['no_dk']; ?>" />
								<input type="hidden" class="holiday_rental" value="<?php echo $list_item['holiday_rental']; ?>" />

								<div class="checkbox">
									<input class="chk_job" name="chk_job[]" type="checkbox" id="check-<?php echo $list_item["jid"] ?>" data-jobid="<?php echo $list_item["jid"]; ?>" value="<?php echo $list_item['jid']; ?>">
									<label for="check-<?php echo $list_item["jid"] ?>">&nbsp;</label>
								</div>		
								<input type="hidden" class="job_type" value="<?php echo $list_item['j_type']; ?>" />
                                <input type="hidden" class="is_eo" value="<?php echo $list_item['is_eo']; ?>" />						
							</td>                         
                            
						</tr>
						<?php 
						}
						
                        }else{
                            echo "<tr><td colspan='15'>No Data</td></tr>";
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

	<h4><?php echo $title; ?></h4>
	<p>This page shows jobs that are not yet completed and waiting to be booked</p>
	<ul>
		<li><span class="redRowBg">Red</span> = Alarm is 240v and EXPIRED. Technician needs to be an electrician</li>
	</ul>
	<br/>
	<pre>
<code>SELECT `j`.`id` AS `jid`, `j`.`status` AS `j_status`, `j`.`service` AS `j_service`, `j`.`created` AS `j_created`, `j`.`date` AS `j_date`, `j`.`start_date`, `j`.`due_date`, `j`.`comments` AS `j_comments`, `j`.`job_price` AS `j_price`, `j`.`job_type` AS `j_type`, `j`.`property_vacant`, `j`.`urgent_job`, `j`.`job_reason_id`, `j`.`is_eo`, `j`.`job_type` AS `j_type`, DATEDIFF(CURDATE(), Date(j.`created`)) AS age, `p`.`property_id` AS `prop_id`, `p`.`address_1` AS `p_address_1`, `p`.`address_2` AS `p_address_2`, `p`.`address_3` AS `p_address_3`, `p`.`state` AS `p_state`, `p`.`postcode` AS `p_postcode`, `p`.`comments` AS `p_comments`, `p`.`no_dk`, `p`.`holiday_rental`, `p`.`preferred_alarm_id`, `p`.`qld_new_leg_alarm_num`, `al_p`.`alarm_make` AS `pref_alarm_make`, `a`.`agency_id` AS `a_id`, `a`.`agency_name` AS `agency_name`, `a`.`phone` AS `a_phone`, `a`.`address_1` AS `a_address_1`, `a`.`address_2` AS `a_address_2`, `a`.`address_3` AS `a_address_3`, `a`.`state` AS `a_state`, `a`.`postcode` AS `a_postcode`, `a`.`trust_account_software`, `a`.`tas_connected`, `a`.`allow_dk`, `ajt`.`id` AS `ajt_id`, `ajt`.`type` AS `ajt_type`, `sa`.`is_electrician`
FROM `jobs` AS `j`
LEFT JOIN `property` AS `p` ON j.`property_id` = p.`property_id`
LEFT JOIN `agency` AS `a` ON  p.`agency_id` = a.`agency_id`
LEFT JOIN `job_type` AS `jt` ON j.`job_type` = jt.`job_type`
LEFT JOIN `alarm_job_type` AS `ajt` ON j.`service` = ajt.`id`
LEFT JOIN `staff_accounts` AS `sa` ON j.`assigned_tech` = sa.`StaffID`
LEFT JOIN `alarm_pwr` AS `al_p` ON p.`preferred_alarm_id` = al_p.`alarm_pwr_id`
WHERE `j`.`del_job` = 0
AND `p`.`deleted` = 0
AND `a`.`status` = 'active'
AND `a`.`country_id` = 1
AND `j`.`status` = 'To Be Booked'
AND `j`.`job_type` = 'IC Upgrade'
ORDER BY `j`.`date` ASC
LIMIT 50</code>
	</pre>

</div>
<!-- Fancybox END -->
<script>
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
	run_ajax_state_filter();
		
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

	// check all
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
		
	});

	// individual row checkbox
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

	});


	// move/assign to maps 
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
					setTimeout(function(){ window.location='/jobs/approved_alarm_numbers'; }, <?php echo $this->config->item('timer') ?>);
						
				});

			}				

		}		
				
	});

});
</script>