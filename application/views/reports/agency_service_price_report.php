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
			echo form_open($uri, $form_attr);
			?>
			<div class="for-groupss row">
				<div class="col-md-10 columns">
					<div class="row">

                        <div class="col-md-2">
							<label>Service Type</label>
							<select id="service_type" name="service_type" class="form-control" required>
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
							<select id="comparison" name="comparison" class="form-control" required>
								<option value="">---</option>
                                <option value="1" <?php echo ( $this->input->get_post('comparison') == 1 )?'selected':null; ?>>Equal</option>
                                <option value="2" <?php echo ( $this->input->get_post('comparison') == 2 )?'selected':null; ?>>Not Equal</option>
							</select>
						</div>
                                                
                        <div class="col-md-2">
							<label>Service Price</label>
							<input type="text" name="price" class="form-control" value="<?php echo $this->input->get_post('price'); ?>" required />
						</div>

						<div class="col-md-2">
							<label>State</label>
							<select id="comparison" name="state" class="form-control">
								<option value="">---</option>
								<option value="NSW" <?php echo ($this->input->get_post('state')=='NSW')?'selected':null; ?>>NSW</option>
								<option value="VIC" <?php echo ($this->input->get_post('state')=='VIC')?'selected':null; ?>>VIC</option>
								<option value="QLD" <?php echo ($this->input->get_post('state')=='QLD')?'selected':null; ?>>QLD</option>
								<option value="ACT" <?php echo ($this->input->get_post('state')=='ACT')?'selected':null; ?>>ACT</option>
								<option value="TAS" <?php echo ($this->input->get_post('state')=='TAS')?'selected':null; ?>>TAS</option>
								<option value="SA" <?php echo ($this->input->get_post('state')=='SA')?'selected':null; ?>>SA</option>
								<option value="WA" <?php echo ($this->input->get_post('state')=='WA')?'selected':null; ?>>WA</option>
								<option value="NT" <?php echo ($this->input->get_post('state')=='NT')?'selected':null; ?>>NT</option>                                
							</select>
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
							<th>Agency ID</th>
							<th>Agency Name</th>
							<th>Agency Address</th>
							<th>Service Type</th>
                            <th>Service Price</th>	
						</tr>
					</thead>

					<tbody>
                    <?php   
                    if( $this->input->get_post('search') && $list->num_rows() > 0 ){    

                        foreach ($list->result() as $row) {
                        ?>
                        <tr id="tr_row_<?php echo $row->agency_id; ?>">
                            <td><?php echo $row->agency_id; ?></td>
                            <td>
                                <a class="update_service_price_fb_trigger" data-agency_id="<?php echo $row->agency_id; ?>" href="javascript:void(0)">
                                    <?php echo  $row->agency_name; ?>
                                </a>
                            </td>
							<td>
								<a target="_blank" href="/agency/view_agency_details/<?php echo $row->agency_id; ?>/4">
									<?php echo  "{$row->a_address_1} {$row->a_address_2}, {$row->a_address_3} {$row->a_state} {$row->a_postcode}"; ?>
								</a>								
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
                            <td><?php echo '$'.number_format($row->price,2); ?></td>         							
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

<!-- SERVICES FANCYBOX -->
<div id="update_service_price_fb" class="fancybox" style="display:none;"></div>
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

    jQuery(".update_service_price_fb_trigger").click(function(){

        var dom = jQuery(this);
        var agency_id = dom.attr("data-agency_id");

        if( agency_id > 0 ){

            jQuery("#load-screen").show();		
			jQuery("#update_service_price_fb").html(''); // clear first

            jQuery.ajax({
                type: "POST",
                url: "/reports/get_agency_services_lightbox",
                data: { 				
                    agency_id: agency_id
                }
            }).done(function( ret ){
                
                jQuery("#load-screen").hide();					
				jQuery("#update_service_price_fb").html(ret);
				
				//init datepicker
				jQuery('.flatpickr').flatpickr({
					dateFormat: "d/m/Y",
					locale: {
						firstDayOfWeek: 1
					}
				});
                
            });

            // launch fancybox
            $.fancybox.open({
                src  : '#update_service_price_fb'
            });

        }        

    });

	jQuery("#update_service_price_fb").on("change",".new_service_price",function(){

		var new_service_price_dom = jQuery(this);
		var new_service_price = new_service_price_dom.val();
		var parent_lb = new_service_price_dom.parents("div#update_service_price_fb:first");
		var parent_tr = new_service_price_dom.parents("tr:first");
        var agency_id = parent_lb.find("#agency_id").val();
		var service_type = parent_tr.find(".service_type").val(); 

		if( agency_id > 0 ){

			jQuery("#load-screen").show();		

			jQuery.ajax({
				type: "POST",
				url: "/reports/get_variation_amount",
				dataType: "json",
				data: { 				
					agency_id: agency_id,
					service_type: service_type,
					new_service_price: new_service_price
				}
			}).done(function( json ){
				
				jQuery("#load-screen").hide();					
				parent_tr.find(".variation_amount").val(json.variation_amount);
				parent_tr.find(".dynamic_price").val(json.dynamic_price);	
				parent_tr.find(".apv_reason").html(json.apvr_option_str);
				parent_tr.find(".apv_type").val(json.apv_type);	

				if( parseInt(json.variation_amount) == 0 ){
					parent_tr.find(".apv_reason").prop("required",false);
					//parent_tr.find(".display_on").prop("required",false);
				}else{					
					parent_tr.find(".apv_reason").prop("required",true);
					//parent_tr.find(".display_on").prop("required",true);
				}
				
			});

		}  

	});


	jQuery("#update_service_price_fb").on("submit","#variation_form",function(e){

		var service_type_arr = [];
		var new_service_price_arr = [];
		var variation_amount_arr = [];
		var apv_type_arr = [];
		var apv_reason_arr = [];
		var display_on_arr = [];
		var apv_expiry_arr = [];
		var current_price_arr = [];
		
		var agency_id = jQuery("#agency_id").val();

		jQuery(".service_type").each(function(){

			var service_type_dom = jQuery(this);
			var service_type = parseInt(service_type_dom.val());
			var parent_tr = service_type_dom.parents("tr:first");

			var new_service_price = parent_tr.find(".new_service_price").val();
			var variation_amount = parent_tr.find(".variation_amount").val();
			var apv_type = parent_tr.find(".apv_type").val();
			var apv_reason = parent_tr.find(".apv_reason").val();
			var display_on = parent_tr.find(".display_on").val();
			var apv_expiry = parent_tr.find(".apv_expiry").val();
			var current_price = parent_tr.find(".current_price").val();			

			if( service_type > 0 ){
				
				service_type_arr.push(service_type);
				new_service_price_arr.push(new_service_price);
				variation_amount_arr.push(variation_amount);
				apv_type_arr.push(apv_type);
				apv_reason_arr.push(apv_reason);
				display_on_arr.push(display_on);
				apv_expiry_arr.push(apv_expiry);
				current_price_arr.push(current_price);

			}			

		});

		e.preventDefault();

		jQuery("#load-screen").show();		
		jQuery.ajax({
			type: "POST",
			url: "/reports/save_agency_price_variation",
			dataType: "json",
			data: { 				
				agency_id: agency_id,
				service_type_arr: service_type_arr,
				new_service_price_arr: new_service_price_arr,
				variation_amount_arr: variation_amount_arr,
				apv_type_arr: apv_type_arr,
				apv_reason_arr: apv_reason_arr,
				display_on_arr: display_on_arr,
				apv_expiry_arr: apv_expiry_arr,
				current_price_arr: current_price_arr
			}
		}).done(function( json ){
						
			jQuery("#load-screen").hide();	
			jQuery("#tr_row_"+agency_id).remove();
			swal({
				title: "Success!",
				text: "Save Successful",
				type: "success",
				confirmButtonClass: "btn-success",
				showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
				timer: <?php echo $this->config->item('timer') ?>
			});			
			$.fancybox.close();
			
		});

		return false;
		

	});


});
</script>