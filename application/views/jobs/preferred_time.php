
<style>
    .col-mdd-3{
        max-width:15.5%;
    }
    .jtable td, .jtable th {
        border-top: none;
        height: auto;
    }
    pre code{
        line-height: 21px;
    }
</style>

<div class="box-typical box-typical-padding">

    <?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => $uri
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);

$export_links_params_arr = array(
	'state_filter' => $this->input->get_post('state_filter'),
	'agency_filter' => $this->input->get_post('agency_filter'),
	'key_access_details' => $this->input->get_post('key_access_details'),
	'key_access_required' => $this->input->get_post('key_access_required'),
	'sub_region_ms' => $this->input->get_post('sub_region_ms')

);
$export_link_params = '/jobs/preferred_time/?export=1&'.http_build_query($export_links_params_arr);
?>
    <header class="box-typical-header">
        <div class="box-typical box-typical-padding">
            <?php
            $form_attr = array(
                'id' => 'jform'
            );
            echo form_open('/jobs/preferred_time',$form_attr);
            ?>
            <div class="for-groupss row">
							<div class="col-md-10 columns">
									<div class="row">
										<div class="col-mdd-3">
											<label>Agency</label>
											<select id="agency_filter" name="agency_filter" class="form-control">
												<option value="">Any</option>
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
											<label for="service_select"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></label>
											<select id="state_filter" name="state_filter" class="form-control">
												<option value="">ALL</option>
											</select>
											<div class="mini_loader"></div>
										</div>

										<div class="col-mdd-3">
											<label>Key Access</label>
											<select id="key_access_required" name="key_access_required" class="form-control">
												<option value="">Any</option>
												<option value="1" <?php echo ($this->input->get_post('key_access_required') == '1' ) ? 'selected' : '';?>>Yes</option>
												<option value="0" <?php echo ($this->input->get_post('key_access_required') == '0' ) ? 'selected' : '';?>>No</option>
											</select>
										</div>

										<div class="col-mdd-3">
											<label>Key Access Comment</label>
											<input type="text" class="form-control" name="key_access_details" id="key_access_details" value="<?php echo $this->input->get_post('key_access_details'); ?>" placeholder="Text">
										</div>

										<div class="col-mdd-3">
												<label for="search">Preferred Time:</label>
												<div class="sort_time">
														<input id="preferred_time" type="text" class="form-control" name="preferred_time_filter" value="<?= $this->input->post('preferred_time_filter') ?>"/>
												</div>
										</div>

										<div class="col-md-1 columns">
												<label class="col-sm-12 form-control-label">&nbsp;</label>
												<button type="submit" class="btn btn-inline">Search</button>
										</div>
							</div>
						</div>
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
            </div>
            </form>
        </div>
    </header>
    

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<table class="table table-hover table-striped main-table">
					<thead>
						<tr>	
								<th>Age</th>
								<th>Job Type</th>
								<th>Job Status</th>	
								<th>
									Preferred Time
									<a data-toggle="tooltip" class="a_link <?php echo $sort ?>" href="<?php echo "/jobs/preferred_time?sort_header=1&order_by=preferred_time&sort={$toggle_sort}"; ?>">
										<em class="fa fa-sort-<?php echo $sort; ?>"></em>
								</a>
								</th>
								<th>Property Address</th>	
								<th>State</th>	
								<th>Agency</th>		
						</tr>
					</thead>

					<tbody>
                        <?php
                        if($job_sql->num_rows() > 0){
                            foreach($job_sql->result() as $job_row){
                            $p_address = "{$job_row->p_address_1} {$job_row->p_address_2}, {$job_row->p_address_3}";
                        ?>
                            <tr>
                            <td>
							<?php 
								// Age
								$date1=date_create(date('Y-m-d',strtotime($job_row->jcreated)));
								$date2=date_create(date('Y-m-d'));
								$diff=date_diff($date1,$date2);
								$age = $diff->format("%a");
								echo $age;
							?>
							</td>            
                                <td>
                                    <a href="<?php echo "{$this->config->item('crm_link')}/view_job_details.php?id={$job_row->jid}"; ?>">
                                        <?php echo $job_row->job_type; ?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo $job_row->j_status; ?>
                                </td>  
                                <td>
                                    <?php echo $job_row->preferred_time; ?>
                                </td>       
                                <td>
                                    <a href="<?php echo "{$this->config->item('crm_link')}/view_property_details.php?id={$job_row->property_id}"; ?>">
                                        <?php echo $p_address; ?>
                                    </a>                    
                                </td> 
                                <td>
                                    <?php echo $job_row->p_state; ?>
                                </td> 
                                <td class="<?php echo ( $job_row->priority > 0 )?'j_bold':null; ?>">
                                    <a href="<?php echo "/agency/view_agency_details/{$job_row->agency_id}"; ?>">
                                        <?php echo $job_row->agency_name." ".( ( $job_row->priority > 0 )?' ('.$job_row->abbreviation.')':null ); ?>
                                    </a>                    
                                </td>                                                                                                  
                            </tr>
                        <?php   
                            }
                        }else{ ?>
                            <tr><td colspan='6'>No Data</td></tr>
                        <?php    
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
	<h4><?php echo $title; ?></h4>
<pre><code> <?php echo $sql_query; ?></code></pre>
</div>
<!-- Fancybox END -->


<script>
jQuery(document).ready(function(){
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
});

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
</script>