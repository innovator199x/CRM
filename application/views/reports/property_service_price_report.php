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
	<style>
	#add_property_variation_div,
	#apply_variation_div{
		display: none;
	}
	</style>
	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">
			<?php
			$form_attr = array(
				'id' => 'jform'
			);
			echo form_open($uri, $form_attr);
			?>
			<div class="for-groupss row">
				<div class="col-md-10 columns">
					<div class="row">

						<div class="col-md-2">
							<label>Agency</label>
							<select id="agency_filter" name="agency_filter" class="form-control" required>
								<option value="">---</option>
                                <?php								
                                foreach( $distinct_sql->result() as $agency_row ){ ?>
                                    <option value="<?php echo $agency_row->agency_id; ?>" <?php echo ( $agency_row->agency_id == $this->input->get_post('agency_filter') )?'selected':null; ?>><?php echo $agency_row->agency_name; ?></option>
                                <?php
                                }								
                                ?> 
							</select>
						</div>

                        <div class="col-md-2">
							<label>Service Type</label>
							<select id="service_type" name="service_type" class="form-control">
								<option value="">---</option>
                                <?php
                                foreach( $ajt_sql->result() as $ajt_row ){ ?>
                                    <option value="<?php echo $ajt_row->id; ?>" <?php echo ( $ajt_row->id == $this->input->get_post('service_type') )?'selected':null; ?>><?php echo $ajt_row->type; ?></option>
                                <?php
                                }
                                ?> 
							</select>
						</div>

                        <div class="col-md-2">
							<label>Comparison</label>
							<select id="comparison" name="comparison" class="form-control">
								<option value="">---</option>
                                <option value="1" <?php echo ( $this->input->get_post('comparison') == 1 )?'selected':null; ?>>Equal</option>
                                <option value="2" <?php echo ( $this->input->get_post('comparison') == 2 )?'selected':null; ?>>Not Equal</option>
							</select>
						</div>
                                                
                        <div class="col-md-2">
							<label>Service Price</label>
							<input type="text" name="price" class="form-control" value="<?php echo $this->input->get_post('price'); ?>" />
						</div>

						<div class="col-md-2 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<input type="submit" name="search" class="btn btn-inline" value="Search" />
						</div>

					</div>

				</div>

                <?php
                if( $this->input->get_post('search') ){ ?>
                    <div class="col-md-2 columns">
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
                <?php
                }
                ?>                

			</div>
			<?php echo form_close(); ?>
		</div>
	</header>

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<table class="table table-hover main-table">
					<thead>
						<tr>
							<th>Property ID</th>
							<th>Property Address</th>
							<th>Agency</th>
							<th>Service Type</th>
                            <th>
								Service Price
								<a data-toggle="tooltip" class="a_link <?php echo $sort ?>" href="<?php echo "{$uri}/?sort_header=1&order_by=ps.price&sort={$toggle_sort}&".http_build_query($pagi_links_params_arr); ?>">
									<em class="fa fa-sort-<?php echo $sort; ?>"></em>
								</a>
							</th>
							<th>
								Agency Price
								<a data-toggle="tooltip" class="a_link <?php echo $sort ?>" href="<?php echo "{$uri}/?sort_header=1&order_by=agen_serv.price&sort={$toggle_sort}&".http_build_query($pagi_links_params_arr); ?>">
									<em class="fa fa-sort-<?php echo $sort; ?>"></em>
								</a>
							</th>
							<th>
								<div class="checkbox" style="margin:0;">
									<input name="chk_all" type="checkbox" id="chk_all" />
									<label for="chk_all"></label>
								</div>
							</th>	
						</tr>
					</thead>

					<tbody>
                    <?php  					 
                    if( $this->input->get_post('search') && $list->num_rows() > 0 ){    

						$prop_price_arr = [];
                        foreach ($list->result() as $row) {							
                        ?>
                        <tr>
                            <td><?php echo $row->property_id; ?></td>	
                            <td>
                                <a target="_blank" href="<?php echo $this->config->item('crm_link'); ?>/view_property_details.php?id=<?php echo $row->property_id; ?>">
                                    <?php echo  "{$row->p_address_1} {$row->p_address_2}, {$row->p_address_3} {$row->p_state} {$row->p_postcode}"; ?>
                                </a>
                            </td>   
							<td>
								<a target="_blank" href="/agency/view_agency_details/<?php echo $row->agency_id; ?>/4"><?php echo $row->agency_name; ?></a>								
							</td>                         							
                            <td>
                                <?php
								// display icons
								$job_icons_params = array(
									'service_type' => $row->service_type,
									'sevice_type_name' => $row->ajt_type
								);
								echo $this->system_model->display_job_icons($job_icons_params);
								?>
                            </td>
                            <td>
								<?php 
								echo '$'.number_format($row->ps_price,2); 
								$prop_price_arr[] = $row->ps_price;
								?>
							</td> 
							<td>
								<?php 
								echo '$'.number_format($row->as_price,2); 								
								?>
							</td>
							<td>
								<div class="checkbox" style="margin:0;">
									<input class="ps_id_chk" type="checkbox" id="ps_id_chk_<?php echo $row->ps_id; ?>" value="<?php echo $row->ps_id; ?>" />
									<label for="ps_id_chk_<?php echo $row->ps_id; ?>"></label>
								</div>
							</td>	        							
                        </tr>
                        <?php
                        }   
                        
                    }else{
                        echo "<tr><td colspan='100%'>Please filter first before submitting</td></tr>";
                    }                                   
                    ?>
					</tbody>

				</table>				
			</div>


			<?php
			if( $this->input->get_post('agency_filter') > 0 ){ ?>


				<div class="row">

					<div class="col-4">	
						<button type='button' id="add_variation_btn" class='btn mb-3'>Add Variation</button>	

						<div id="add_property_variation_div">
							<form id="add_variation_form">
								<table class="table">
									<tr>
										<th>Amount</th>
										<td><input type="text" name="agency_price_variation_amount" id="agency_price_variation_amount" class="form-control" required /></td>
									</tr>
									<tr>
										<th>Type</th>
										<td>
											<select id="apv_type" class="form-control" required>
												<option value="">---</option>
												<option value="1">Discount</option>
												<option value="2" <?php echo ( $jv_row->type == 2 )?'selected':null; ?>>Surcharge</option>
											</select>
										</td>
									</tr>              
									<tr id="apv_tr">
										<th>Reason</th>
										<td>
											<select id="apv_reason" name="apv_reason"  class="form-control apv_reason" required>
												<option value="">---</option>
												<?php
												$adr_sql = $this->db->query("
												SELECT *
												FROM `agency_price_variation_reason`
												WHERE `active` = 1										
												ORDER BY `reason` ASC
												");
												foreach( $adr_sql->result() as $adr_row ){ ?>
													<option data-is_discount="<?php echo $adr_row->is_discount; ?>" value="<?php echo $adr_row->id; ?>" <?php echo ( $adr_row->id == $jv_row->reason )?'selected':null; ?>><?php echo $adr_row->reason; ?></option>
												<?php
												}
												?>                        
											</select>									
										</td>
									</tr>
									<tr>
										<th>Display On</th>
										<td>
											<select name="apv_display_on" class="apv_display_on form-control" id="apv_display_on">
												<option value="">---</option>
												<option value="1">Hide on all</option>
												<option value="2">Show on invoice only</option>
												<option value="3">Show on agency portal only</option>
												<option value="4">Show on invoice and agency portal </option>
											</select>
										</td>
									</tr>
									<tr>
										<td>&nbsp;</td>
										<td><button type='submit' id="property_variation_save_btn" class='btn'>Save Variation</button></td>
									</tr>
								</table> 
							</form>
						</div>       
					</div>

					<div class="col-4 text-center">
							<button type='button' id="update_rem_prop_btn" class='btn mb-3'>Update remaining properties</button>
							<button type='button' id="mark_as_completed" class='btn mb-3'>Mark as Completed</button>
					</div>

					<div id="apply_variation_div" class="col-4">
							
						<form id="apply_variation_form">
							<div class="float-right mb-3">
								<label class='more_tenant_label'>Agency Price Variation</label>
								<select name="agency_price_variation" id="agency_price_variation" class="form-control" required>
									<option value="">--- Select ---</option>	
									<?php
									// agency price variation
									$scope = 1; // property
									$apv_sql = $this->db->query("
									SELECT 
										apv.`id`,
										apv.`amount`,
										apv.`type`,
										apv.`reason` AS apv_reason,
										apv.`scope`,

										apvr.`reason` AS apvr_reason
									FROM `agency_price_variation` AS apv
									LEFT JOIN `agency_price_variation_reason` AS apvr ON apv.`reason` = apvr.`id`
									WHERE apv.`agency_id` = {$this->input->get_post('agency_filter')}                    
									AND apv.`active` = 1
									AND apv.`scope` = {$scope}
									ORDER BY 
										apv.`type` ASC, 
										apv.`scope` ASC,
										apvr.`reason` ASC
									");                        
									foreach( $apv_sql->result() as $apv_row ){ ?>
										<option value="<?php echo $apv_row->id; ?>">
											$<?php echo number_format($apv_row->amount, 2); ?> 
											(<?php echo ( $apv_row->type == 1 )?'Discount':'Surcharge';  ?> - <?php echo $apv_row->apvr_reason; ?>)
										</option>
									<?php
									}
									?>						
								</select>						
							</div>
						

							<div style="clear:both;"></div>

							<div class="float-right">
								<input type="hidden" name="agency_id" value="<?php echo $this->input->get_post('agency_filter'); ?>">
								<button type="submit" class="btn btn-inline" id="apply_variation_btn">Apply Variation</button>						
							</div>

						</form>

					</div>


				</div>
				

			<?php
			}

			if( $this->input->get_post('search') ){ ?>
				<nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
				<div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
			<?php
			}
			?>

		</div>
	</section>

</div>


<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;">

	<h4><?php echo $title; ?></h4>
	<p>
	lorem ipsum
	</p>

	<pre>
	<code><?php echo $page_query; ?></code>
	</pre>

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
function is_ticked(){

	if( jQuery(".ps_id_chk:visible:checked").length > 0 ){
		jQuery("#apply_variation_div").show();
	}else{
		jQuery("#apply_variation_div").hide();
	}

}
jQuery(document).ready(function() {

	<?php if( $this->session->flashdata('success_msg') != '' &&  $this->session->flashdata('status') == 'success' ){ ?>
		swal({
			title: "Success!",
			text: "<?= addslashes($this->session->flashdata('success_msg')) ?>",
			html: true,
			type: "success",
			confirmButtonClass: "btn-success",
			showConfirmButton: true,
		});
	<?php } ?>

	jQuery(".ps_id_chk").click(function(){

		is_ticked();

	});

	jQuery("#chk_all").change(function(){

		var chk_all_dom = jQuery(this);

		if( chk_all_dom.prop("checked") == true ){
			jQuery(".ps_id_chk:visible").prop("checked",true);			
		}else{
			jQuery(".ps_id_chk:visible").prop("checked",false);
		}	
		
		is_ticked();

	});

	jQuery("#add_variation_btn").click(function(){

		jQuery("#add_property_variation_div").toggle();

	});

	jQuery("#apv_type").change(function(){

		var apv_type = jQuery(this).val();

		if( apv_type == 1 ){ // discount

			jQuery("#apv_reason option[data-is_discount=1]").show(); // discount
			jQuery("#apv_reason option[data-is_discount=0]").hide(); // surcharge
			jQuery("#apv_display_on").val(4);
			
		}else{ // surcharge

			jQuery("#apv_reason option[data-is_discount=1]").hide(); // discount
			jQuery("#apv_reason option[data-is_discount=0]").show(); // surcharge
			jQuery("#apv_display_on").val(3);

		}

	});

	
	jQuery("#add_variation_form").submit(function (e) {

		e.preventDefault();

		var agency_id = parseInt(<?php echo $this->input->get_post('agency_filter'); ?>);
		var agency_price_variation_amount = jQuery("#agency_price_variation_amount").val();
		var apv_type = jQuery("#apv_type").val();
		var apv_reason = jQuery("#apv_reason").val();
		var apv_display_on = jQuery("#apv_display_on").val();		

		if ( parseInt(agency_id) > 0 && agency_price_variation_amount > 0 ) {					

			jQuery("#load-screen").show();
			jQuery.ajax({
				type: "POST",
				url: "/reports/save_agency_variation",
				data: {
					agency_id: agency_id,
					agency_price_variation_amount: agency_price_variation_amount,
					apv_type: apv_type,
					apv_reason: apv_reason,
					apv_display_on: apv_display_on					
				}
			}).done(function (ret) {

				jQuery("#load-screen").hide();
				swal({
					title: "Success!",
					text: "New Variation Added",
					type: "success",
					confirmButtonClass: "btn-success",
					showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
					timer: <?php echo $this->config->item('timer') ?>
				});	
				setTimeout(function(){ window.location='<?php echo "{$uri}?".http_build_query($pagi_links_params_arr); ?>'; }, <?php echo $this->config->item('timer') ?>);						
				
			});

		}

		return false;

	});


	jQuery("#apply_variation_form").submit(function (e) {

		e.preventDefault();

		var agency_id = parseInt(<?php echo $this->input->get_post('agency_filter'); ?>);
		var agency_price_variation = parseInt(jQuery("#agency_price_variation").val());			

		// get selected properties
		var ps_arr = [];
		jQuery(".ps_id_chk:checked").each(function(){

			var ps_id = parseInt(jQuery(this).val());			

			if( ps_id > 0 ){
				ps_arr.push(ps_id);
			}

		});

		if ( parseInt(agency_id) > 0 && agency_price_variation > 0 && ps_arr.length > 0 ) {					

			jQuery("#load-screen").show();
			jQuery.ajax({
				type: "POST",
				url: "/reports/save_property_variation",
				data: {
					agency_id: agency_id,
					agency_price_variation: agency_price_variation,					
					ps_arr: ps_arr
				}
			}).done(function (ret) {
				
				jQuery("#load-screen").hide();
				swal({
					title: "Success!",
					text: "Variation applied to selected properties",
					type: "success",
					confirmButtonClass: "btn-success",
					showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
					timer: <?php echo $this->config->item('timer') ?>
				});	
				setTimeout(function(){ window.location='<?php echo "{$uri}?".http_build_query($pagi_links_params_arr); ?>'; }, <?php echo $this->config->item('timer') ?>);						
				
				
			});

		}

		return false;

	});


	jQuery("#update_rem_prop_btn").click(function(){

		swal({
			title: "Warning!",
			text: "This will process all remaining properties, would you like to proceed?",
			type: "warning",						
			showCancelButton: true,
			confirmButtonClass: "btn-success",
			confirmButtonText: "Yes, Continue",
			cancelButtonClass: "btn-danger",
			cancelButtonText: "No, Cancel!",
			closeOnConfirm: true,
			showLoaderOnConfirm: true,
			closeOnCancel: true
		},
		function(isConfirm) {

			if (isConfirm) {							  
				
				window.location = '/reports/property_service_price_report/?process_remaining=1&<?php echo http_build_query($pagi_links_params_arr); ?>';				

			}

		});				

	});

	jQuery("#mark_as_completed").click(function(){

		var agency_id = parseInt(<?php echo $this->input->get_post('agency_filter'); ?>);

		if( agency_id > 0 ){

			swal({
				title: "Warning!",
				text: "This will mark agency as completed, would you like to proceed?",
				type: "warning",						
				showCancelButton: true,
				confirmButtonClass: "btn-success",
				confirmButtonText: "Yes, Continue",
				cancelButtonClass: "btn-danger",
				cancelButtonText: "No, Cancel!",
				closeOnConfirm: true,
				showLoaderOnConfirm: true,
				closeOnCancel: true
			},
			function(isConfirm) {

				if (isConfirm) {							  
					
					jQuery("#load-screen").show();
					jQuery.ajax({
						type: "POST",
						url: "/reports/mark_agency_completed_for_price_increase",
						data: {
							agency_id: agency_id
						}
					}).done(function (ret) {
						
						jQuery("#load-screen").hide();
						swal({
							title: "Success!",
							text: "Agency is now marked as completed",
							type: "success",
							confirmButtonClass: "btn-success",
							showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
							timer: <?php echo $this->config->item('timer') ?>
						});	
						setTimeout(function(){ window.location='/reports/property_service_price_report'; }, <?php echo $this->config->item('timer') ?>);											
						
					});			

				}

			});	

		}					

	});

});
</script>