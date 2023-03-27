<style>
    .col-mdd-3{
        max-width:15.5%;
    }
	#region_dp_div {
		position: absolute;
		top: 65px;
	}
	.grey_bgcolor{
		background: #f6f8fa
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
        'link' => "/reports/region_numbers"
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
        echo form_open("/reports/region_numbers?exclude_dha={$exclude_dha}",$form_attr);
        ?>
            <div class="for-groupss row">


                <div class="col-lg-9 col-md-12 columns">
                    <div class="row">


                        <div class="col-mdd-3">
                            <label for="state"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></label>
                            <select id="state_filter" name="state_filter" class="form-control">
                                <option value="">ALL</option>
                                <?php 
                                foreach($state_filter->result_array() as $row){									
                                ?>
									<option 
										value="<?php echo $row['region_state'] ?>" 
										<?php echo ( $row['region_state']==$this->input->get_post('state_filter') )?'selected':null; ?>
									>
										<?php echo $row['region_state'] ?>
									</option>
                                <?php 
                                } ?>
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

                        <div class="col-md-1 columns">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <input class="btn" type="submit" name="btn_search" value="Search">
                        </div>
                        
                    </div>

				</div>
				<div class="col-lg-3 col-md-12 columns">
					<div class="checkbox-toggle" style="margin-top:30px;margin-bottom:30px;">
						<?php $exclude_dh_sel = ( is_numeric($exclude_dha) && $exclude_dha == 0) ? 'checked="true"' : NULL; ?>
						<input <?php echo $exclude_dh_sel ?> type="checkbox" id="check-toggle-dh">
						<label for="check-toggle-dh" style="font-size:18px;">Include DHA Agencies</label>
						<input type="hidden" id="mode" value="<?php echo $exclude_dha ?>">
					</div>	
				<div>
                                                    
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
							<th><?php  echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></th>
							<th><?php  echo $this->gherxlib->getDynamicRegion($this->config->item('country')); ?></th>
							<th>Sub Region</th>
							<th style="width:30%">Postcodes</th>
                            <th>Properties</th>
                            <th>Service Price</th>
                            <th>Average</th>
						</tr>
					</thead>

					<tbody>
						<?php 
						if($this->input->get_post('btn_search')){

                            foreach($postcode_sql_res as $postcode_row){

							$p_count = $postcode_row->property_service_count;
							$tot_ps_price = $postcode_row->property_service_price;
							?>
								<tr>
									<td><?php echo $postcode_row->region_state; ?></td>
									<td>
										<a href="/reports/edit_main_region/<?=$postcode_row->regions_id;?>">
											<?=$postcode_row->region_name;?>
										</a>
									</td>	
									<td><?=$postcode_row->subregion_name;?></td>
									<td><?=$postcode_row->postcode;?></td>	
									<td><?=$p_count;?></td>
									<td>
										<?php 
										//echo ( $tot_ps_price > 0 )?'$'.number_format($tot_ps_price,2):null; 
										echo ( $tot_ps_price > 0 )?'$'.number_format($this->system_model->price_ex_gst($tot_ps_price),2):null;
										?>
									</td>
									<td>
										<?php 
											$average_price = ($tot_ps_price/$p_count); 
											//echo ( $average_price > 0 )?'$'.number_format($average_price, 2, '.', ''):null;	
											echo ( $average_price > 0 )?'$'.number_format($this->system_model->price_ex_gst($average_price),2):null;			
										?>
									</td>
								</tr>
							<?php								
							}								
							?>

							<tr>
								<td colspan='100%'>&nbsp;</td>
							</tr>

							<tr class="grey_bgcolor">
								<td colspan='4'><b>PAGE total:</b></td>
								<td><strong><?php echo $prop_count_total; ?></strong></td>
								<td>
									<strong>
										<?php 
										//echo ( $tot_ps_price_total > 0 )?'$'.number_format($tot_ps_price_total,2):null; 
										echo ( $tot_ps_price_total > 0 )?'$'.number_format($this->system_model->price_ex_gst($tot_ps_price_total),2):null;
										?>
									</strong>
								</td>
								<td>
									<strong>
										<?php 
										//echo ( $average_price_fin > 0 )?'$'.number_format($average_price_fin,2, '.', ''):null; 
										echo ( $average_price_fin > 0 )?'$'.number_format($this->system_model->price_ex_gst($average_price_fin),2):null;
										?>
									</strong>
								</td>
							</tr>							
							
							<tr>
								<td colspan='100%'>&nbsp;</td>
							</tr>

							<tr class="grey_bgcolor">
								<td colspan='4'><b>FULL total:</b></td>
								<td><strong><?php echo $prop_count_total_full_totals; ?></strong></td>
								<td>
									<strong>
										<?php 
										//echo ( $tot_ps_price_total_full_totals > 0 )?'$'.number_format($tot_ps_price_total_full_totals,2):null; 
										echo ( $tot_ps_price_total_full_totals > 0 )?'$'.number_format($this->system_model->price_ex_gst($tot_ps_price_total_full_totals),2):null;										
										?>
									</strong>
								</td>
								<td>
									<strong>
										<?php 
										//echo ( $average_price_fin_full_totals > 0 )?'$'.number_format($average_price_fin_full_totals,2, '.', ''):null; 
										echo ( $average_price_fin_full_totals > 0 )?'$'.number_format($this->system_model->price_ex_gst($average_price_fin_full_totals),2):null;
										?>
									</strong>
								</td>
							</tr>							

						<?php 
						}else{
							echo "<tr><td colspan='7'>Select Region Above</td></tr>";
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
	<p>
	This page shows a breakdown of Properties in each region.
	</p>

	<p>Service Price are exclusive of GST.</p>

	<p>
		<strong>Region query</strong>
		<pre>
<code>SELECT `r`.`regions_id`, `r`.`region_name`, `r`.`region_state`, `sr`.`sub_region_id`, `sr`.`subregion_name`, `pc`.`postcode`
FROM `postcode` AS `pc`
LEFT JOIN `sub_regions` AS `sr` ON pc.`sub_region_id` = sr.`sub_region_id`
LEFT JOIN `regions` AS `r` ON sr.`region_id` = r.`regions_id`
ORDER BY `r`.`region_state` ASC, `r`.`region_name` ASC
LIMIT 50</code>
		</pre>
	</p>
	<p>
		<strong>Properties and price per postcode query</strong>
		<pre>
<code>SELECT COUNT( DISTINCT ps.`property_id` ) AS ps_count, SUM( ps.`price` ) AS ps_price, `p`.`postcode`
FROM `property_services` AS `ps`
LEFT JOIN `property` AS `p` ON ps.`property_id` = p.`property_id`
LEFT JOIN `agency` AS `a` ON  p.`agency_id` = a.`agency_id`
WHERE `ps`.`service` = 1
AND `p`.`deleted` = 0
AND `a`.`status` = 'active'
AND `a`.`country_id` = 1
AND `p`.`postcode` IN($postcodes_array)
GROUP BY `p`.`postcode`</code>
		</pre>
	</p>

</div>
<!-- Fancybox END -->


<script>
jQuery(document).ready(function(){

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
				rf_class: 'regions',
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
					rf_class: 'regions',
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
					rf_class: 'regions',
					region_filter_json: region_filter_json
				}
			}).done(function( ret ){
				
				jQuery("#load-screen").hide();
				obj.parents(".region_div_chk:first").find(".sub_region_div").html(ret);


				// auto tick all sub region
				obj.parents(".region_div_chk:first").find(".sub_region_ms").click();

				
				// searched
				var sub_region_ms_json_num = sub_region_ms_json.length;
				if( sub_region_ms_json_num > 0 ){				
					for( var i=0; i < sub_region_ms_json_num; i++ ){

						var is_ticked = obj.parents(".region_div_chk:first").find(".sub_region_ms[value='"+sub_region_ms_json[i]+"']").prop("checked");
						if( is_ticked == false ){
							obj.parents(".region_div_chk:first").find(".sub_region_ms[value='"+sub_region_ms_json[i]+"']").click();
						}						

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


	 $('#check-toggle-dh').on('change',function(){

		var checked = $(this).is(':checked');
		var mode = jQuery("#mode").val();

		if(checked){
			window.location.href = '/reports/region_numbers/?btn_search=1&exclude_dha=0&<?php echo http_build_query($params_url_arr) ?>';
		}else{
			window.location.href = '/reports/region_numbers/?btn_search=1&exclude_dha=1&<?php echo http_build_query($params_url_arr) ?>';
		}
		

	 });




});
</script>
