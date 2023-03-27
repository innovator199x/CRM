<?php

    $export_links_params_arr = array(
        'region_filter_state' =>  $this->input->get_post('region_filter_state'),
        'agency_filter' => $this->input->get_post('agency_filter'),
        'state_filter' => $this->input->get_post('state_filter'),
        'sub_region_ms' => $this->input->get_post('sub_region_ms'),
		'search' => $this->input->get_post('search'),
		'date_from_filter' => $this->input->get_post('date_from_filter'),
		'date_to_filter' => $this->input->get_post('date_to_filter'),
        'get_sats' => 1
    );
    $export_link_params = '/jobs/export_future_pendings_v2/?'.http_build_query($export_links_params_arr);

?>
<style>
	.col-mdd-3{
		max-width:15%;
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
            'title' => 'Test',
            'link' => '#'
        ),
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/jobs/future_pendings_v2"
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
		echo form_open('/jobs/future_pendings_v2',$form_attr);
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
							<label>Agency</label>
							<select id="agency_filter" name="agency_filter" class="form-control">
								<option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>
						</div>
                        

                        <div class="col-mdd-3">
							<label for="service_select"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></label>
							<select id="state_filter" name="state_filter" class="form-control field_g2">
								<option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>
						</div>

                        	<div class="col-mdd-3">
							<label for="phrase_select">Phrase</label>
							<input type="text" name="search" class="form-control" placeholder="Text" value="<?php echo $this->input->get_post('search'); ?>" />
						</div>

						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>

							<input type="hidden" name="get_sats" value="1" />
							<input type="submit" class="btn" value="Get Stats" name="btnGetStats" >
						</div>
						
					</div>

                </div>
                
                <div class="col-lg-3 col-md-12 columns">
                    <section class="proj-page-section float-right">
                        <div class="proj-page-attach">
                            <i class="fa fa-file-excel-o"></i>
                            <p class="name"><?php echo $title; ?></p>
                            <p>
								<a href="<?php echo $export_link_params ?>">
									Export
								</a>
                            </p>
                        </div>
                    </section>
				</div>
            </div>
            
            <div class="for-groupss row quickLinksDiv">
        <div class="text-left col-md-3 columns">

           <?php echo $this->customlib->generateLink($prev_day, $staff_filter); ?>

        </div>
        <div class="text-center col-md-6 columns">

           Quick Links&nbsp;

		    <?php
			for($i=0;$i<=3;$i++){ 
				$m = date("F",strtotime("{$from} +{$i} month")); 
				$from_link = date("Y-m-01",strtotime("{$from} +{$i} month"));
				$to_link = date("Y-m-t",strtotime("{$from} +{$i} month"));
			?>
			
				| &nbsp; <a href="/jobs/future_pendings_v2?date_from_filter=<?php echo $from_link; ?>&date_to_filter=<?php echo $to_link; ?>&get_sats=1" <?php echo ($from_link==$from)?'style="font-weight: bold;"':''; ?>><?php echo $m; ?></a>&nbsp;
			
			<?php
			}
			?>

        </div>
        <div class="text-right col-md-3 columns">
			
			<?php echo $this->customlib->generateLink($next_day, $staff_filter); ?>


        </div>
            </div>
			</form>
		</div>
	</header>
<?php 
	if($this->input->get_post('btnGetStats') || $this->input->get_post('get_sats')==1){
	
		
?>
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
						<?php
							if($lists->num_rows()>0){
							foreach($lists->result_array() as $row){
						?>

								<tr>
									<td><?php echo $row['property_id'] ?></td>
                                    <td><?php echo ($this->system_model->isDateNotEmpty($row['prop_created_date']))?$this->system_model->formatDate($row['prop_created_date'],'d/m/Y'):NULL;  ?></td>
									<td>
                                    <?php
										$f_address = "{$row['p_address1']} {$row['p_address2']}, {$row['p_address3']} {$row['p_state']} {$row['p_postcode']}";
										echo $this->gherxlib->crmlink('vpd',$row['property_id'],$f_address);
                                    ?>
                                    </td>
                                    <td><?php echo $row['agency_id'] ?></td>
									<td><?php 
										echo $this->gherxlib->crmlink('vad',$row['agency_id'], $row['agency_name']);
									?></td>
                                    <td>
                                        <img data-toggle="tooltip" title="<?php echo $row['ajt_type'] ?>" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($row['j_service']); ?>" />
                                    </td>
                                    <td>
                                        $<?php echo $row['ps_price']; ?>
                                    </td>
                                    <td><?php echo $this->system_model->formatDate($row['jdate'],'d/m/Y') ?></td>
									<td>	<?php echo date("F Y",strtotime($row['jdate'].' +1 year')); ?></td>
                                    <td data-a="<?php echo $ls['assigned_tech'] ?>">
                                 
									<?php 
									
									//$ls = $this->gherxlib->get_last_service_row($row['property_id'])->row_array();

									echo $this->jobs_model->get_last_visit_per_property($row['property_id']);
                                  

                                  //echo   $this->system_model->formatDate($ls['date'],'d/m/Y');
                                    ?>
                                    
                                    </td>
								</tr>

						<?php
							}}else{
								echo "<tr><td colspan='9'>No Data</td></tr>";
							}
						?>
					</tbody>

				</table>
			</div>

			<nav class="text-center">
				<?php echo $pagination; ?>
			</nav>

			<div class="pagi_count text-center">
				<?php echo $pagi_count; ?>
			</div>

		</div>
	</section>

<?php
	}else{
		echo "<h3>Press 'Get Stats' to Display Results</h3>";
	}
?>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>This page displays all new jobs that will have a YM job created for in the selected date range.</p>

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



jQuery(document).ready(function(){


// run headler filter ajax
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

})

</script>
