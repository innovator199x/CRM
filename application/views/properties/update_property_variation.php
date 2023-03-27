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
				<div class="col-md-8 columns">
					<div class="row">

						<div class="col-md-3">
							<label>Agency</label>
							<select id="agency_filter" name="agency_filter" class="form-control">
								<option value="">ALL</option>
								<?php
								foreach ( $agency_sql->result() as $agency_row ) { ?>
									<option 
                                        value="<?php echo $agency_row->agency_id; ?>" 
                                        <?php echo ($agency_row->agency_id == $this->input->get_post('agency_filter')) ? 'selected' : null; ?>>
                                            <?php echo $agency_row->agency_name; ?>
                                    </option>
								<?php
							    }
							    ?>
							</select>
						</div>

						<div class="col-md-2 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<button type="submit" class="btn btn-inline">Search</button>
						</div>

					</div>

				</div>

                <div class="col-md-4 columns">
                    <section class="proj-page-section float-right">
                        <div class="proj-page-attach">
                            <i class="fa fa-file-excel-o"></i>
                            <p class="name"><?php echo $title; ?></p>
                            <p>
								<a href="<?php echo $export_link ?>">
									Export
								</a>
                            </p>
                        </div>
                    </section>
				</div>
			</div>
			<?php echo form_close(); ?>
		</div>
	</header>

	<section>
		<div class="body-typical-body">
            <?php
			$form_attr = array(
				'id' => 'apply_property_variation_form'
			);
			echo form_open('/properties/apply_property_variation', $form_attr);
			?>
			<div class="table-responsive">
				<table class="table table-hover main-table">
					<thead>
						<tr>
							<th>Address</th>
							<th>Service Price</th>
							<th>Current variation</th>
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
                    if( $this->input->get_post('agency_filter') > 0 && $property_sql->num_rows() > 0 ){ 

                        foreach ($property_sql->result() as $row) {
                            ?>
                            <tr>
                                <td>
                                    <a href="<?php echo $this->config->item('crm_link'); ?>/view_property_details.php?id=<?php echo $row->property_id; ?>">
                                        <?php echo  "{$row->address_1} {$row->address_2}, {$row->address_3} {$row->state} {$row->postcode}"; ?>
                                    </a>
                                </td>								
                                <td>
                                    <?php
                                    $ps_sql_str = "
                                    SELECT 
                                        `property_services_id`,
                                        `price`
                                    FROM `property_services`
                                    WHERE `property_id` = {$row->property_id}
                                    ";
                                    $ps_sql = $this->db->query($ps_sql_str);                    

                                    if( $ps_sql->num_rows() == 1 ){
                                        $ps_row = $ps_sql->row();
                                        echo '$'.number_format($ps_row->price, 2);
                                    }else if( $ps_sql->num_rows() > 1 ){
                                        echo "Refer to Property";
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    // get property variation								
                                    $pv_sql = $this->db->query("
                                    SELECT 
                                        pv.`agency_price_variation`,

                                        apv.`amount`,
                                        apv.`type`,

                                        apvr.`reason`
                                    FROM `property_variation` AS pv
                                    LEFT JOIN `agency_price_variation` AS apv ON pv.`agency_price_variation` =  apv.`id`
	                                LEFT JOIN `agency_price_variation_reason` AS apvr ON apv.`reason` = apvr.`id`
                                    WHERE pv.`property_id` = {$row->property_id}                    
                                    AND pv.`active` = 1
                                    ");                                    
                                    if( $pv_sql->num_rows() > 0 ){
                                        $pv_row = $pv_sql->row();
                                        echo "\$".( number_format($pv_row->amount, 2) )." ( ".( ( $pv_row->type == 1 )?'Discount':'Surcharge' )." - {$pv_row->reason} )";
                                    }else{
                                        echo "No variation applied";
                                    }
                                    ?>
                                </td>
                                <td>
                                    <div class="checkbox">
                                        <input class="chk_property_id" name="property_arr[]" type="checkbox" id="check-<?php echo $row->property_id; ?>" value="<?php echo $row->property_id; ?>">
                                        <label for="check-<?php echo $row->property_id; ?>">&nbsp;</label>
                                    </div>
                                </td>								
                            </tr>
                        <?php
                        }

                    }else{
                        echo "<tr><td colspan='100%'>Please filter by agency first</td></tr>";
                    }                    
					?>
					</tbody>

				</table>
			</div>

            <?php 
            if( $this->input->get_post('agency_filter') > 0 ){
            ?>
            <div id="apply_variation_div">
                
                <div class="float-right mb-3">
                    <label class='more_tenant_label'>Agency Price Variation</label>
                    <select name="agency_price_variation" id="agency_price_variation" class="form-control">
                        <option value="">--- Select ---</option>	
                        <?php
                        // agency price variation
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
                        AND apv.`scope` = 1
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
                    <button type="button" class="btn btn-inline" id="apply_variation_btn">Apply Variation</button>						
                </div>

            </div>
            <?php
            }
            ?>
            <?php echo form_close(); ?>

			<nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
			<div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>

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
    #apply_variation_div,
    #apply_variation_btn{
        display: none;
    }
</style>
<script>
    function show_apply_variation(){

        if( jQuery(".chk_property_id:checked").length > 0 ){
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

        jQuery("#check-all").change(function(){

            var dom = jQuery(this);
            var is_ticked = dom.prop("checked");

            if( is_ticked == true ){
                jQuery(".chk_property_id").prop("checked",true);
            }else{
                jQuery(".chk_property_id").prop("checked",false);
            }

            show_apply_variation();
            
        });

        jQuery(".chk_property_id").change(function(){

            show_apply_variation();

        });

        jQuery("#agency_price_variation").change(function(){

            var dom = jQuery(this);
            var agency_price_variation = dom.val();

            if( agency_price_variation > 0 ){
                jQuery("#apply_variation_btn").show();
            }else{
                jQuery("#apply_variation_btn").hide();
            }

        });

        jQuery("#apply_variation_btn").click(function(){

            swal({
                title: "",
                text: "Selected price variation will be applied to the selected properties, Do you want to continue?",
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
                    
                    jQuery("#apply_property_variation_form").submit();

                }

            });	

        });

	});
</script>