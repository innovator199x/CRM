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
			'link' => "/properties/active_properties"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);

	$export_links_params_arr = array(
		'agency_filter' => $this->input->get_post('agency_filter'),
		'holiday_rental' => $this->input->get_post('holiday_rental'),
		'search' => $this->input->get_post('search'),
		'state_ms' => $this->input->get_post('state_ms'),
		'region_ms' => $this->input->get_post('region_ms'),
		'sub_region_ms' => $this->input->get_post('sub_region_ms'),
		'state_filter' => $this->input->get_post('state_filter')
	);
	$export_link_params = '/properties/active_properties/?export=1&'.http_build_query($export_links_params_arr);
	?>

	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">
			<?php
		$form_attr = array(
			'id' => 'jform'
		);
		echo form_open('properties/active_properties',$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-md-11 columns">
					<div class="row">
					
						<div class="col-md-2">
							<label for="agency_select">Agency</label>
							<select name="agency_filter" id="agency_filter" class="form-control agency_filter">
								<option value="">ALL</option>							
							</select>
						</div>

						<div class="col-mdd-2">
							<label for="state"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></label>
							<select id="state_filter" name="state_filter" class="form-control ">
								<option value="">ALL</option>
								<option <?php echo ($this->input->get_post('state_filter')==-1) ? 'selected' : NULL; ?> value="-1">Empty</option>
								<?php
								foreach( $state_filter_sql->result() as  $state_row ){
									if($state_row->state!=""){
								?>
									<option value="<?php echo $state_row->state; ?>" <?php echo ( $state_row->state == $this->input->get_post('state_filter') )?'selected':null;  ?>><?php echo $state_row->state; ?></option>
								<?php	
									}	
								}
								?>
							</select>							
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
							<label for="agency_select">Short Term Rental</label>
							<div class="checkbox" data-toggle="tooltip" title="Short Term Rental">
								<input type="checkbox" name="holiday_rental" id="holiday_rental" value="1" <?php echo ( $this->input->get_post('holiday_rental') == 1 )?'checked':null; ?> />
								<label for="holiday_rental"></label>
							</div>
						</div>

						<div class="col-md-2">
							<label for="phrase_select">Phrase</label>
							<input type="text" name="search" class="form-control" placeholder="Text" value="<?php echo $this->input->get_post('search'); ?>" />
						</div>

						<div class="col-md-2 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<button type="submit" class="btn btn-inline">Search</button>
						</div>
						
					</div>

				</div>

				<div class="col-lg-1 col-md-12 columns">
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
							<th>Address</th>
							<th>
								<?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?>
							</th>
							<?php 				
							// get service type
							foreach( $service_types->result() as $ajt ){ 

								if($this->config->item('country')==2){ //NZ removed other services
									if($ajt->id==2){
							?>							
								<th>
									<img data-toggle="tooltip" title="<?php echo $ajt->type ?>" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($ajt->id); ?>" />
								</th>							
							<?php
									}
								}else{
							?>
									<th>
										<img data-toggle="tooltip" title="<?php echo $ajt->type ?>" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($ajt->id); ?>" />
									</th>	
							<?php
								}
							}							
							?>
							<th>Agency</th>
						</tr>
					</thead>

					<tbody>
						<?php foreach($lists->result() as $row){ 			
						?>
						<tr>
							<td>
								<?php
									$tmp_url = base_url();
									$c_url = substr($tmp_url, 0, -1);
									//echo $c_url;
									if($c_url == "https://crmdevci.sats.com.au"){
										$redirect = 1;
									}
									if($c_url == "https://crmci.sats.com.au"){
										$redirect = 2;
									}
								?>
								<a href="<?php echo $this->config->item('crm_link'); ?>/view_property_details.php?id=<?php echo $row->property_id ?>&r=<?php echo $redirect?>">
									<?php echo  $row->p_address_1." ".$row->p_address_2.", ".$row->p_address_3; ?>
								</a>
							</td>
							<td>
								<?php echo $row->p_state; ?>
							</td>
							<?php 				
							// get service type
							foreach( $service_types->result() as $ajt ){ 

								$psts_array = array(
									'property_id' => $row->property_id,
									'ajt_id' => $ajt->id
								);


								if($this->config->item('country')==2){ //NZ removed other services
									if( $ajt->id ==2){
							?>							
								<td>
									<?php 
									
									echo $this->system_model->getServiceTypeStatus($psts_array);
									?>
								</td>							
							<?php
									}
								}else{ //AU display all services
							?>
									<td>
										<?php 
										echo $this->system_model->getServiceTypeStatus($psts_array);
										?>
									</td>	
							<?php
								}						
							}							
							?>
							<td class="<?php echo ( $row->priority > 0 )?'j_bold':null; ?>">
								<a href="/agency/view_agency_details/<?php echo $row->agency_id; ?>">
									<?php echo $row->agency_name." ".( ( $row->priority > 0 )?' ('.$row->abbreviation.')':null ); ?>
								</a>
							</td>
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
		This page shows all jobs active in the system regardless of whether we service or not. ie. DIY, No response etc
		<br/>
		<pre>
			<code><?php echo $sql_query; ?></code>
		</pre>
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