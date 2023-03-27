<style>
#found_old_notes_div{
	display:none;
}
.row_hl {
    background: #dee2e6 !important;
}
.a_link.asc{
    top:3px;
}
.a_link.desc{
    top:-3px;
}
.fa-sort-up:before, .fa-sort-asc:before {
    content: "\f0de";
}
</style>
<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/jobs/escalate"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	<!-- old Notes found -->
	<div id="found_old_notes_div" class="row">
		<div class="col-md">
		Found Old Notes. Clear? <button type="button" id="clear_old_notes_btn" class="btn btn-inline">Yes</button>
		</div>
	</div>

	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">
			<?php
		$form_attr = array(
			'id' => 'jform'
		);
		echo form_open('/jobs/escalate',$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-md-9 columns">
					<div class="row">

						<div class="col-mdd-3">
							<label>Agency</label>
							<select id="agency_filter" name="agency_filter" class="form-control">
								<option value="">ALL</option>
							</select>
						</div>

						<div class="col-mdd-3">
							<label><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></label>
							<select id="state_filter" name="state_filter" class="form-control field_g2">
								<option value="">ALL</option>
							</select>
						</div>

						<div class="col-mdd-3" style="min-width:195px;">
							<label for="service_select">Trust Account Software</label>
							<select name="tsa_filter" class="form-control">
								<option value="">Any</option>
								<?php								
								// trust account software
								$tas_sql = $this->db->query("
								SELECT *
								FROM `trust_account_software`
								WHERE `active` = 1
								");								

								foreach( $tas_sql->result() as $tsa_row ){ ?>
									<option value="<?php echo $tsa_row->trust_account_software_id; ?>" <?php echo ( $this->input->get_post('tsa_filter') == $tsa_row->trust_account_software_id )?'selected="selected"':''; ?>><?php echo $tsa_row->tsa_name; ?></option>
								<?php
								}
								?>
								<option value="-1" <?php echo ( $row['trust_account_software'] == -1 )?'selected="selected"':''; ?>>Other</option>
							</select>
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

						<div class="col-md-3">
							<label for="phrase_select">Phrase</label>
							<input type="text" name="search" class="form-control" placeholder="ALL" value="<?php echo $this->input->get_post('search'); ?>" />
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
				<table class="table table-hover main-table escalate_tbl">
					<thead>
						<tr>
							<?php 
							//count total jobs
							foreach($lists->result_array() as $row): 	

							//get job count
							$tt_count_q_params = array(
								'sel_query' => "COUNT(j.id) as j_count",
								'p_deleted' => 0,
								'a_status' => 'active',
								'del_job' => 0,
								'agency_filter' => $row['a_id'],
								'job_status' => "Escalate",
								'country_id' => COUNTRY,
								'display_query' => 0
							);
							$tt_count_q = $this->jobs_model->get_jobs($tt_count_q_params)->row();

							$total_jobs += $tt_count_q->j_count;
							endforeach;
							?>
							<th>Jobs <span class="label label-custom label-pill label-danger bubble_count"><?=$total_jobs?></span></th>
							<th>Agency</th>
							<th>Phone</th>
							<th>Left Message</th>
							<th>Emailed</th>
							<th>Notes</th>
							<th>
								Last Updated
								<a data-toggle="tooltip" class="a_link <?php echo $sort ?>" href="<?php echo "/jobs/escalate/?sort_header=1&order_by=last_updated&sort={$toggle_sort}&".http_build_query($header_link_params); ?>">
									<em class="fa fa-sort-<?php echo $sort; ?>"></em>
								</a>
							</th>
							<th>Save Notes</th>
							<th>Trust Accounting Software</th>
                            <th>API Connected</th>         
						</tr>
					</thead>

					<tbody>
						<?php
						if($lists->num_rows()>0){
						foreach($lists->result_array() as $row): 	

							//get escalate_agency_info
							$jparams = array(
								'country_id' => $this->config->item('country'),
								'agency_filter' => $row['a_id'],
								'date' => date('Y-m-d')
							);
							$eai_sql = $this->gherxlib->getEscalateAgencyInfo($jparams)->row_array();

							/*
							//get staff infor 
							$staff_info_params = array(
								'sel_query' => 'FirstName, LastName',
								'staff_id' => $row['esclate_notes_last_updated_by']
							);
							$staff_info = $this->gherxlib->getStaffInfo($staff_info_params)->row_array();
							*/

							// get staff        
							$params = array( 
								'sel_query' => '
									sa.`StaffID`,
									sa.`FirstName`, 
									sa.`LastName`,
									sa.`active`
								',
								
								'staff_id' => $row['esclate_notes_last_updated_by'],
								'display_query' => 0
							);
							
							// get user details
							$staff_accounts_sql = $this->staff_accounts_model->get_staff_accounts($params);
							$staff_accounts_row = $staff_accounts_sql->row();

							//Escalate notes tweak
							if($row['save_notes']==1){
								$escalate_notes = $row['escalate_notes'];
								$escalate_notes_ts = ( $row['escalate_notes']!='' && $row['escalate_notes_ts']!="" )?date('d/m/Y H:i',strtotime($row['escalate_notes_ts'])):'';	
								$esclate_notes_last_updated_by = ($row['escalate_notes']!='' && $row['esclate_notes_last_updated_by']!=NULL )? $this->system_model->formatStaffName($staff_accounts_row->FirstName,$staff_accounts_row->LastName): '';

							}else{
								$escalate_notes = $eai_sql['notes'];
								$escalate_notes_ts = ( $eai_sql['notes']!='' && $eai_sql['notes_timestamp']!="" )?date("d/m/Y H:i",strtotime($eai_sql['notes_timestamp'])):'';
								$esclate_notes_last_updated_by = "";
							}

							$row_class = null;
							if( $eai_sql['left_message'] == 1 || $eai_sql['emailed'] == 1 || $row['save_notes'] ){
								$row_class = 'row_hl';
							}					
							
							//get job count
							$tt_count_q_params = array(
								'sel_query' => "COUNT(j.id) as j_count",
								'p_deleted' => 0,
								'a_status' => 'active',
								'del_job' => 0,
								'agency_filter' => $row['a_id'],
								'job_status' => "Escalate",
								'country_id' => COUNTRY,
								'display_query' => 0
							);
							$tt_count_q = $this->jobs_model->get_jobs($tt_count_q_params)->row();


						?>
						<tr class="<?php echo $row_class; ?>">
							<td>
								<?php //echo $row['jcount']; ?>
								<?php echo $tt_count_q->j_count ?>
								<input type="hidden" class="agency_id" value="<?php echo $row['a_id']; ?>" />
							</td>

							<td>
							<?php 
								/*
								<a data-toggle="tooltip" title="View Agency" href="/agency/view_agency_details/<?php echo $row['a_id'] ?>"><i class="fa fa-building-o"></i></a> 
								*/

								$hClass = ( $row["priority"] > 0 )?"j_bold":null;
								$hLabel = ( $row["priority"] > 0 )?"(".$row["abbreviation"].")":null;

								echo $this->gherxlib->crmLink('vad',$row['a_id'],'<i class="fa fa-building-o"></i>');

								?>	
								
								&nbsp;
								<?php echo '<a class="'.$hClass.'" data-toggle="tooltip" title="View Escalate Jobs" href="/jobs/escalate_jobs/'.$row['a_id'].'">'.$row['agency_name']." ".$hLabel.'</a>'; ?>
							</td>

							<td>
								<?php echo $row['a_phone'] ?>
							</td>

							<td>
								<div class="checkbox">
									<input data-esc-field="left_message" <?php echo ($eai_sql['left_message']==1)?'checked="checked"':'' ?> class="chk_left_message esc_info" name="chk_left_message" type="checkbox" id="chk_left_message-<?php echo $row["a_id"] ?>" data-agencyid="<?php echo $row["a_id"]; ?>">
									<label for="chk_left_message-<?php echo $row["a_id"] ?>">&nbsp;</label>
									<i style="display:none;" class="fa fa-check-circle text-green green_check"></i>
								</div>
							</td>
							
							<td>
								<div class="checkbox">
									<input data-esc-field="emailed" <?php echo ($eai_sql['emailed']==1)?'checked="checked"':'' ?> class="chk_emailed esc_info" name="chk_emailed" type="checkbox" id="chk_emailed-<?php echo $row["a_id"] ?>" data-agencyid="<?php echo $row["a_id"]; ?>">
									<label for="chk_emailed-<?php echo $row["a_id"] ?>">&nbsp;</label>
									<i style="display:none;" class="fa fa-check-circle text-green green_check"></i>
								</div>
							</td>

							<td>
								<div class="pos-rel">
									<input data-esc-field="notes" type="text" class="form-control notes esc_info" name="agency_escalate_notes" value="<?php echo $escalate_notes; ?>">
									<i class="fa fa-check-circle text-green green_check check_ok_ajax"></i>
								</div>
							</td>

							<td>
							<span class="last_update_ts">
								<?php 
								echo $escalate_notes_ts ."<br/>";
								echo "<span class='text-red'>";
								echo $esclate_notes_last_updated_by;
								echo "</span>";
								 ?>
								</span>
                            </td>

                            <td>
								<div class="checkbox">
									<input <?php echo ($row['save_notes']==1)?'checked="checked"':'' ?> class="chk_save_notes" name="escalate_save_notes_check" type="checkbox" id="escalate_save_notes_check-<?php echo $row["a_id"] ?>" data-agencyid="<?php echo $row["a_id"]; ?>">
									<label for="escalate_save_notes_check-<?php echo $row["a_id"] ?>">&nbsp;</label>
									<i style="display:none;" class="fa fa-check-circle text-green save-notes-check-ok green_check"></i>
								</div>
								
							</td>
							
							<td>
								<?php
									if($row['trust_account_software']!=0){ 
								?>
										<a data-fancybox="fancybox_tas" data-src="#fancybox_tas<?php echo $row['a_id'] ?>" href="javascript:void(0);" class="tas_link"><?php echo $row['tsa_name']; ?></a>
								<?php 	
								}else{
								?>
									<button data-fancybox="fancybox_tas" data-src="#fancybox_tas<?php echo $row['a_id'] ?>" type="button" class="btn btn-sm blue-btn tas_link">
										Edit
									</button>
								<?php
								}
								?>

								<div id="fancybox_tas<?php echo $row['a_id'] ?>" class="tas_hid_elem fancybox" style="display:none;" >
									<h4>Update Trust Accounting Software</h4>
									<div class="form-group">
										<select class="tas_dp form-control">
											<option value="">Any</option>
											<?php								
											// trust account software
											$tas_sql = $this->db->query("
											SELECT *
											FROM `trust_account_software`
											WHERE `active` = 1
											");								
											foreach( $tas_sql->result() as $tsa_row ){ ?>
												<option value="<?php echo $tsa_row->trust_account_software_id; ?>" <?php echo ( $row['trust_account_software'] == $tsa_row->trust_account_software_id )?'selected="selected"':''; ?>><?php echo $tsa_row->tsa_name; ?></option>
											<?php
											}
											?>
											<option value="-1" <?php echo ( $row['trust_account_software'] == -1 )?'selected="selected"':''; ?>>Other</option>
										</select>
									</div>
								
									<div class="form-group text-right">
										<button type="button" class="btn btn-danger btn-sm tas_cancel btn-colose-fancybox"> 
											Cancel
										</button>
										<button data-agencyid="<?php echo $row['a_id'] ?>" type="button" class="btn btn-sm tas_update">
											Update
										</button>
									</div>
								 </div>



							</td>

							<?php
							// API		
							$sel_query = "
								agen_api_tok.`agency_api_token_id`,		
								agen_api_tok.`api_id`,

								agen_api.`api_name`
							";
							$api_token_params = array(
								'sel_query' => $sel_query,
								'active' => 1,
								'agency_id' => $row['a_id'],
								'display_query' => 0
							);
							$api_token_sql = $this->api_model->get_agency_api_tokens($api_token_params);
							$api_token_row =  $api_token_sql->row();
							?>
							<td>							
								<span class="txt_green">
									<?php							
									echo $api_token_row->api_name;						
									?>
								</span>
							</td>			
						</tr>
						<?php endforeach;
						}else{
							echo "<tr><td colspan='10'>No Data</td></tr>";
						}
						 ?>
					</tbody>

				</table>
			</div>

			<nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>

			<div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>

		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4>Escalate</h4>
	<p>This page shows any jobs that we require further assistance from the agency to be able to book the job for service. For a job to appear on this page it will have required a status change to ‘escalate’ via View Job Details.</p>
<pre>
<code><?php echo $sql_query; ?></code>
</pre>
</div>
<!-- Fancybox END -->

<script type="text/javascript">

	function updateAgencyEscalateNotes(obj){
		
		var agency_id = obj.parents("tr:first").find(".agency_id").val();
		var save_notes_chk_status = obj.parents("tr:first").find(".chk_save_notes").prop('checked');
		var escalate_notes = obj.parents("tr:first").find(".notes").val();
		
		// if checkbox 
		var save_notes_chk = (save_notes_chk_status==true)?1:0;
		
		$('#load-screen').show(); //show loader

		jQuery.ajax({
			type: "POST",
			url: "/jobs/ajax_update_agency_save_notes",
			dataType: 'json',
			data: { 
				agency_id: agency_id,
				save_notes_chk: save_notes_chk,
				escalate_notes: escalate_notes
			}
		}).done(function( ret ){
			if(ret.status){
				$('#load-screen').hide(); //hide loader
				var update_by = "<span class='text-red'>"+ret.update_by+"</span>";
				obj.parents("tr:first").find(".last_update_ts").html(ret.date_ts+"<br/>"+update_by);
				obj.parents("tr:first").css('background-color','#eeeeee');
				obj.parents("td:first").find(".green_check").show();
				//fadeout timer
				setTimeout(function(){ 
					obj.parents("td:first").find(".green_check").fadeOut();
				}, 3000);
			}
		});
		
	}


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

/*
// maintenance
function run_ajax_maint_prog_filter(){

var json_data = <?php echo $maintenance_filter_json; ?>;
var searched_val = '<?php echo $this->input->get_post('maint_prog_filter'); ?>';

jQuery('#maint_prog_filter').next('.mini_loader').show();
jQuery.ajax({
	type: "POST",
		url: "/sys/header_filters",
		data: { 
			rf_class: 'jobs',
			header_filter_type: 'maint_prog',
			json_data: json_data,
			searched_val: searched_val
		}
	}).done(function( ret ){	
		jQuery('#maint_prog_filter').next('.mini_loader').hide();
		$('#maint_prog_filter').append(ret);
	});
			
}
*/



jQuery(document).ready(function(){

	// run headler filter ajax
	run_ajax_agency_filter();
	run_ajax_state_filter();
	//run_ajax_maint_prog_filter();
	

	// tick row higlight
	jQuery(".escalate_tbl input[type='checkbox']").change(function(){

		var obj = jQuery(this);
		var is_ticked = obj.prop("checked");

		if( is_ticked == true ){
			obj.parents("tr:first").addClass("row_hl");
		}


	});
	

	$('.btn-colose-fancybox').click(function(){
		$.fancybox.close();
	});
	

	jQuery(".chk_save_notes").click(function(){
	
		var obj = jQuery(this);
		updateAgencyEscalateNotes(obj);
		
		
	});


	$('.tas_update').on('click',function(){
		var obj = $(this);
		var tas_id = obj.parents('.fancybox').find('.tas_dp').val();
		var agency_id = obj.attr('data-agencyid');
		
		$.fancybox.close();
		$('#load-screen').show(); //show loader
				
		jQuery.ajax({
			type: "POST",
			url: "/jobs/ajax_update_agency_tas",
			data: { 
				agency_id: agency_id,
				tas_id: tas_id
			}
		}).done(function( ret ){
			$('#load-screen').hide(); //hide loader
			$.fancybox.close();	
			swal({
				title:"Success!",
				text: "Updated Successfully",
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


	jQuery(".esc_info").change(function(){
	
		var obj = jQuery(this);
		
		// if checkbox 
		if( obj.attr('type')=='checkbox' ){
			
			if( obj.prop('checked')==true ){
				obj.val(1);
			}else{
				obj.val(0);
			}
			
		}
		
		var chk_save_notes = obj.parents("tr:first").find(".chk_save_notes").prop('checked');
		var agency_id = obj.parents("tr:first").find(".agency_id").val();
		var eai_field = obj.attr("data-esc-field");		
		var eai_val = obj.val();
		
		$('#load-screen').show(); //show loader
		jQuery.ajax({
			type: "POST",
			url: "/jobs/ajax_insert_escalate_agency_info",
			data: { 
				agency_id: agency_id,
				eai_field: eai_field,
				eai_val: eai_val
			}
		}).done(function( ret ){	

			//if(ret.result){
				$('#load-screen').hide(); //hide loader
				obj.parents("tr:first").find(".last_update_ts").html(ret.date_ts);
				obj.parents("tr:first").css('background-color','#eeeeee');
				obj.parents("td:first").find(".green_check").show();
				//fadeout timer
				setTimeout(function(){ 
					obj.parents("td:first").find(".green_check").fadeOut();
				}, 3000);
			//}
			
		});
		
		// save notes checkbox is checked, should save notes to agency
		if( chk_save_notes==true ){ 
			updateAgencyEscalateNotes(obj);
		}
	
	});


		





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
		

});
</script>