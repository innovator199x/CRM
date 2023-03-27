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
            'link' =>  $uri
        )
    );
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	<?php 
	if( validation_errors() ){ ?>
		<div class="alert alert-danger">
		<?php echo validation_errors(); ?>
		</div>
	<?php
	}	
	?>

	<style>
	.separator {
		margin: 0 5px;
	}
	.bold_it{
		font-weight: bold;
	}
	.date_div {
		width: auto;
		margin-right: 13px;
	}
	</style>
    
	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">
			<?php
		$form_attr = array(
			'id' => 'jform'
		);
		echo form_open($uri,$form_attr);
		?>
			<div class="for-groupss row">

				<div class="col-md-10 columns">
					<div class="row">	

                        <div class="mx-2">
							<label for="agency_select">Sales Rep</label>
							<select id="salesrep_filter" name="salesrep_filter"  class="form-control field_g2">
                                <option value="">---</option>
                                <?php   								                            
                                foreach( $salesrep_filter_sql->result() as $salesrep_row ){ ?>
                                    <option value="<?php echo $salesrep_row->StaffID; ?>" <?php echo ( $salesrep_row->StaffID == $this->input->get_post('salesrep_filter') )?'selected="selected"':null; ?>>
                                        <?php echo "{$salesrep_row->sr_fname} {$salesrep_row->sr_lname}"; ?>
                                    </option>
                                <?php
                                }  								                           
                                ?>
							</select>							
						</div>

						<div class="mr-2">
							<label for="agency_select">Service Type</label>
							<select id="service_type_filter" name="service_type_filter"  class="form-control field_g2">
                                <option value="">---</option>
                                <?php  															                            
                                foreach( $service_type_filter_sql->result() as $service_type_row ){ ?>
                                    <option value="<?php echo $service_type_row->ajt_id; ?>" <?php echo ( $service_type_row->ajt_id == $this->input->get_post('service_type_filter') )?'selected="selected"':null; ?>>
                                        <?php echo $service_type_row->ajt_type; ?>
                                    </option>
                                <?php
                                }  																                           
                                ?>
							</select>							
						</div>

						<div class="date_div mr-2">
							<label for="date_select">From</label>
							<input name="status_changed_from" class="flatpickr form-control" data-allow-input="true" id="flatpickr" type="text" value="<?php echo $this->system_model->formatDate($status_changed_from,'d/m/Y') ?>" />
						</div>

						<div class="date_div mr-2">
							<label for="date_select">To</label>
							<input name="status_changed_to" class="flatpickr form-control" data-allow-input="true" id="flatpickr" type="text" value="<?php echo $this->system_model->formatDate($status_changed_to,'d/m/Y') ?>" />
                        </div>

						<!--		
						<div class="col-md-2">
							<label for="is_payable_filter">Payable</label>
							<select id="is_payable_filter" name="is_payable_filter"  class="form-control field_g2">
                                <option value="">---</option>
								<option value="1" <?php echo ( $this->input->get_post('is_payable_filter') == 1 )?'selected':null; ?>>Yes</option>
								<option value="0" <?php echo ( $this->input->get_post('is_payable_filter') == 0 && is_numeric($this->input->get_post('is_payable_filter')) )?'selected':null; ?>>No</option>                               
							</select>						
						</div>
						-->

						<div class="mr-2">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<input type="submit" name="search_submit" value="Search" class="btn">
						</div>

						
					</div>

				</div>

				<!-- DL ICONS START -->
                <?php 
                $date = ($this->input->get_post('date')!="")?date('Y-m-d',$this->input->get_post('date')):NULL;
                ?>
				<!--
			    <div class="col-lg-2 col-md-12 columns">
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
				-->
				<!-- DL ICONS END -->


			</div>
			</form>
		</div>
	</header>

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
            
				<table class="table table-hover main-table table-striped">

					<thead>
						<tr>    
                            <th>Address
							
								<a data-toggle="tooltip" class="a_link <?php echo $sort ?>" href="<?php echo "{$uri}/?sort_header=1&order_by=p.address_2&sort={$toggle_sort}&".http_build_query($header_link_params); ?>">
									<em class="fa fa-sort-<?php echo $sort; ?>"></em>
								</a>
							
							</th> 
							<th>Service</th>  
                            <th>Agency								
								<a data-toggle="tooltip" class="a_link <?php echo $sort ?>" href="<?php echo "{$uri}/?sort_header=1&order_by=a.agency_name&sort={$toggle_sort}&".http_build_query($header_link_params); ?>">
									<em class="fa fa-sort-<?php echo $sort; ?>"></em>
								</a>							
							</th> 
                            <th>Sales Rep</th> 
                            <th>Date								
								<a data-toggle="tooltip" class="a_link <?php echo $sort ?>" href="<?php echo "{$uri}/?sort_header=1&order_by=ps.status_changed&sort={$toggle_sort}&".http_build_query($header_link_params); ?>">
									<em class="fa fa-sort-<?php echo $sort; ?>"></em>
								</a>								
							</th>  
							<!--	
							<th>Payable</th>
							-->
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
					if( $list->num_rows() > 0 ){                                
						foreach( $list->result() as $row ){                                       
						?>
							<tr>
								<td>
									<a href='<?php echo $this->config->item("crm_link"); ?>/view_property_details.php?id=<?php echo $row->property_id; ?>'>
										<?php echo "{$row->address_1} {$row->address_2}, {$row->address_3}"; ?>
									</a>
								</td>
								<td>
									<?php
									// display icons
									$job_icons_params = array(
										'service_type' => $row->ajt_id
									);
									echo $this->system_model->display_job_icons($job_icons_params);
									?>
								</td>
								<td>
									<a href='/agency/view_agency_details/<?php echo $row->agency_id; ?>'>
										<?php echo $row->agency_name; ?>
									</a>
								</td>
								<td>
									<?php
									echo  "{$row->sr_fname} {$row->sr_lname}";
									?>
								</td>	
								<td><?php echo ( $this->system_model->isDateNotEmpty($row->status_changed) )?date('d/m/Y', strtotime($row->status_changed)):null; ?></td>							
								<!--
								<td>
									<select class="form-control is_payable">
										<option value="">---</option>
										<option value="1" <?php echo ( $row->is_payable == 1 )?'selected':null; ?>>Yes</option>
										<option value="0" <?php echo ( $row->is_payable == 0 && is_numeric($row->is_payable) )?'selected':null; ?>>No</option>                               
									</select>	
									<input type="hidden" class="ps_id" value="<?php echo $row->property_services_id; ?>" />			
								</td>
								-->
								<td>
									<div class="checkbox" style="margin:0;">
										<input class="ps_id_chk" type="checkbox" id="chk_all_<?php echo $row->property_services_id; ?>" value="<?php echo $row->property_services_id; ?>" />
										<label for="chk_all_<?php echo $row->property_services_id; ?>"></label>
									</div>
								</td>	
								
							</tr>
						<?php                    
						}  
					}else{
						echo "<tr><td colspan='100%'>No results found</td></tr>";
					}                                                                           
					?>

					</tbody>

				</table>	
				
				<div class="float-right">
					<button id="clear_status_changed" type="button" class="btn">Clear Status Changed</button>
				</div>				

			</div>

			<nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
			<div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
			

		</div>
	</section>

</div>


<!-- Fancybox START -->

<!-- ABOUT TEXT -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>This page shows all properties that are not upgraded to the NEW QLD legislation</p>
	<pre>
	<code><?php echo $page_query; ?></code>
	</pre>

</div>
<script>
jQuery(document).ready(function(){

	jQuery(".is_payable").change(function(){

		var is_payable_dom = jQuery(this);
		var parent_tr = is_payable_dom.parents("tr:first");
		
		var ps_id = parent_tr.find('.ps_id').val();
		var is_payable = is_payable_dom.val();

		if( ps_id > 0  && is_payable != '' ){

			jQuery('#load-screen').show();
			jQuery.ajax({
				type: "POST",
				url: "/reports/ajax_update_is_payable",
				data: { 	
					ps_id: ps_id,
					is_payable: is_payable
				}
			}).done(function( ret ){
									
				jQuery('#load-screen').hide();					

			});

		}		

	});

	jQuery("#clear_status_changed").click(function(){

		var is_payable_dom = jQuery(this);
		var parent_tr = is_payable_dom.parents("tr:first");

		var ps_id_arr = [];
		jQuery(".ps_id_chk:visible:checked").each(function(){

			var ps_id_chk_dom = jQuery(this);
			var ps_id_chk = ps_id_chk_dom.val();

			if( ps_id_chk > 0 ){
				ps_id_arr.push(ps_id_chk);
			}

		});

		if( ps_id_arr.length > 0 ){
			
			jQuery('#load-screen').show();
			jQuery.ajax({
				type: "POST",
				url: "/reports/ajax_clear_status_change",
				data: { 	
					ps_id_arr: ps_id_arr
				}
			}).done(function( ret ){
									
				jQuery('#load-screen').hide();
				location.reload();					

			});

		}		

	});	

	jQuery("#chk_all").change(function(){

		var chk_all_dom = jQuery(this);
		
		if( chk_all_dom.prop("checked") == true ){
			jQuery(".ps_id_chk:visible").prop("checked",true);
		}else{
			jQuery(".ps_id_chk:visible").prop("checked",false);
		}		

	});

});
</script>
