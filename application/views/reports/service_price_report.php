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
							<label>Service Source</label>
							<select id="service_source" name="service_source" class="form-control" required>
								<option value="">---</option>
                                <option value="1" <?php echo ( $this->input->get_post('service_source') == 1 )?'selected':null; ?>>Agency</option>
                                <option value="2" <?php echo ( $this->input->get_post('service_source') == 2 )?'selected':null; ?>>Property</option>
							</select>
						</div>

						<div id="agency_filter_div" class="col-md-2">
							<label>Agency</label>
							<select id="agency_filter" name="agency_filter" class="form-control">
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

						<div class="col-md-2 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<button type="submit" name="search" class="btn btn-inline">Search</button>
						</div>

					</div>

				</div>

                <?php
                if( $this->input->get_post('service_source') > 0 ){ ?>
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
							<th>
                                <?php
                                if( $this->input->get_post('service_source') == 1 ){ // agency
                                    $header = "Agency ID";
                                }else if( $this->input->get_post('service_source') == 2 ){ // property
                                    $header = "Property ID";
                                }else{
                                    $header = "ID";
                                }
                                echo $header;
                                ?>
                            </th>
							<th>
                                <?php
                                if( $this->input->get_post('service_source') == 1 ){ // agency
                                    $header = "Agency Name";
                                }else if( $this->input->get_post('service_source') == 2 ){ // property
                                    $header = "Property Address";
                                }else{
                                    $header = "Address";
                                }
                                echo $header;
                                ?>
                            </th>
							<th>Service Type</th>
                            <th>Service Price</th>	
						</tr>
					</thead>

					<tbody>
                    <?php   
                    if( $this->input->get_post('service_source') > 0 && $list->num_rows() > 0 ){    

                        foreach ($list->result() as $row) {
                        ?>
                        <tr>
                            <td>                               
                                <?php
                                if( $this->input->get_post('service_source') == 1 ){ // agency
                                    echo $row->agency_id;
                                }else if( $this->input->get_post('service_source') == 2 ){ // property
                                    echo $row->property_id;
                                }                                
                                ?>
                            </td>	
                            <td>                               
                                <?php
                                if( $this->input->get_post('service_source') == 1 ){ // agency ?>
                                    <a href="/agency/view_agency_details/<?php echo $row->agency_id; ?>">
                                        <?php echo  $row->agency_name; ?>
                                    </a>                                    
                                <?php
                                }else if( $this->input->get_post('service_source') == 2 ){ // property ?>
                                    <a href="<?php echo $this->config->item('crm_link'); ?>/view_property_details.php?id=<?php echo $row->property_id; ?>">
                                        <?php echo  "{$row->address_1} {$row->address_2}, {$row->address_3} {$row->state} {$row->postcode}"; ?>
                                    </a>
                                <?php
                                }                                
                                ?>
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
			if( $this->input->get_post('service_source') > 0 ){ ?>
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
   	#agency_filter_div{
        display: none;
    }
</style>
<script>

function agency_filter_required_toggle(){

	var service_source = jQuery("#service_source").val();	

	if( service_source == 1 ){ // agency

		jQuery("#agency_filter").prop("required",false)
		jQuery("#agency_filter_div").hide();

	}else if( service_source == 2 ){ // property

		jQuery("#agency_filter").prop("required",true)
		jQuery("#agency_filter_div").show();

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

	// on load
	agency_filter_required_toggle();
	
	jQuery("#service_source").change(function(){

		agency_filter_required_toggle();

	});
	

});
</script>