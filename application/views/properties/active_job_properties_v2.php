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
				<div class="col-md-9 columns">
					<div class="row">
					
						<div class="col-md-3">
							<label for="agency_select">Agency</label>
							<select name="agency_filter" id="agency_filter" class="form-control agency_filter">
								<option value="">ALL</option>							
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

						<div class="col-md-3">
							<label for="phrase_select">Phrase</label>
							<input type="text" name="search" class="form-control" placeholder="Text" value="<?php echo $this->input->get_post('search'); ?>" />
						</div>

						<div class="col-md-2 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<button type="submit" class="btn btn-inline">Search</button>
						</div>
						
					</div>

				</div>


				<!-- DL ICONS START -->
                <div class="col-lg-3 col-md-12 columns">
                    <section class="proj-page-section float-right">
                        <div class="proj-page-attach">
                            <i class="fa fa-file-excel-o"></i>
                            <p class="name"><?php echo $title; ?></p>
                            <ul>
								<?php
								$split_by = 10000; // split by 10k
								$split = ceil($total_rows/$split_by); 
								$offset = 0;
								for( $i=0; $i<$split; $i++ ){ 																		
									?>
									<li>
										<a href="<?php echo $export_link ?>&offset=<?php echo $offset; ?>&limit=<?php echo $split_by; ?>" target="blank">
											Export part <?php echo $i+1; ?>
										</a>
									</li>
								<?php
								$offset += $split_by;
								}
								?>								
							</ul>
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
				<table class="table table-hover main-table">
					<thead>
						<tr>
							<th>Property ID</th>
							<th>Date Added</th>
							<th>Address</th>							
							<th>Agency ID</th>
							<th>Agency</th>
							<th>Service</th>
							<th>Amount</th>
							<th>Last Billed</th>
							<th>Next Service Due</th>
							<th>Last Visit</th>
						</tr>
					</thead>

					<tbody>
						<?php foreach($lists->result() as $row){ 			
						?>
						<tr>
							<td><?php echo $row->property_id; ?></td>
							<td><?php echo ($this->system_model->isDateNotEmpty($row->created))?date('d/m/Y', strtotime($row->created)):null; ?></td>
							<td>
								<a href="<?php echo $this->config->item('crm_link'); ?>/view_property_details.php?id=<?php echo $row->property_id; ?>">
									<?php echo  "{$row->p_address_1} {$row->p_address_2}, {$row->p_address_3} {$row->p_state} {$row->p_postcode}"; ?>
								</a>
							</td>			
							<td><?php echo $row->agency_id; ?></td>						
							<td>
								<a href="/agency/view_agency_details/<?php echo $row->agency_id; ?>">
									<?php echo $row->agency_name; ?>
								</a>
							</td>							
							<td>
                            	<img data-toggle="tooltip" title="<?php echo $row->ajt_type ?>" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($row->ajt_id); ?>" />
							</td>
							<td>
								$<?php echo $row->ps_price; ?>
							</td>
							<?php
							// last YM completed
							$job_sql = $this->db->query("
								SELECT `id`, `date`
								FROM `jobs`
								WHERE `property_id` = {$row->property_id}
								AND `job_type` = 'Yearly Maintenance'
								AND `status` = 'Completed'
								AND `del_job` = 0
								ORDER BY `date` DESC
								LIMIT 1
							");
							$job_row = $job_sql->row();							
							?>
							<td><?php echo ( $this->system_model->isDateNotEmpty($job_row->date) )?$this->system_model->formatDate($job_row->date,'d/m/Y'):null; ?></td>
							<td><?php echo ( $this->system_model->isDateNotEmpty($job_row->date) )?date("F Y",strtotime($job_row->date.' +1 year')):null; ?></td>
							<td><?php echo $this->jobs_model->get_last_visit_per_property($row->property_id) ?></td>
						</tr>
						<?php } ?>
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

	<h4><?php echo $title; ?></h4>
	<p>
		This page displays all properties that are active in the system and requires SATS to service them. 
		This page does NOT include DIY, Other Supplier etc
	</p>

</div>
<!-- Fancybox END -->


<style>
.main-table {
	border-left: 1px solid #dee2e6;
	border-right: 1px solid #dee2e6;
	border-bottom: 1px solid #dee2e6;
	margin-bottom: 20px;
}

.col-mdd-3 {
	-webkit-box-flex: 0;
	-ms-flex: 0 0 15.2%;
	flex: 0 0 15.2%;
	max-width: 15.2%;

	position: relative;
	width: 100%;
	min-height: 1px;
	padding-right: 15px;
	padding-left: 15px;
}
.proj-page-attach{
	height: auto;
}
</style>
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
			rf_class: 'property',
			header_filter_type: 'agency',
			json_data: json_data,
			searched_val: searched_val
		}
	}).done(function( ret ){	
		jQuery('#agency_filter').next('.mini_loader').hide();
		$('#agency_filter').append(ret);
	});
			
}

jQuery(document).ready(function(){

	// run headler filter ajax
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
					rf_class: 'property',
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
						rf_class: 'property',
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
						rf_class: 'property',
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

});

</script>