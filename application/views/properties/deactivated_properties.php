<?php

//$toggle_sort = ( $sort == 'asc' )?'desc':'asc';	

  $export_links_params_arr = array(
	'date_from' => $this->input->get_post('date_from'),
	'date_to' => $this->input->get_post('date_to'),
	'agency_filter' => $this->input->get_post('agency_filter'),
	'search_filter' =>  $this->input->get_post('search_filter')
);
//$export_link_params = '/properties/export_deleted_properties/?'.http_build_query($export_links_params_arr);
$export_link_params = '/properties/deactivated_properties/?export=1&'.http_build_query($export_links_params_arr);
?>
<style>
	.btn.btn-default {
    background-color: red;
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
			'link' => "/properties/deactivated_properties"
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
		echo form_open('properties/deactivated_properties',$form_attr);
		?>
			<div class="for-groupss row">

						
						
				<div class="col-md-8 columns">
					<div class="row">
					<div class="col-md-2">
							<label for="date_select">From</label>
							<input name="date_from" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text" placeholder="All" value="<?php echo $this->input->get_post('date_from'); ?>">
						</div>

						<div class="col-md-2">
							<label for="date_select">To</label>
							<input name="date_to" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr_2" type="text" placeholder="All" value="<?php echo $this->input->get_post('date_to'); ?>">
						</div>

						<div class="col-md-3">
							<label for="agency_select">Agency</label>
							<select name="agency_filter"  class="form-control field_g2 select2-photo">
								<option value="">All</option>
								<?php foreach($agency_filter->result_array() as $list): ?>
								<option value="<?php echo $list['agency_id']; ?>" <?php echo ( $list['agency_id']==$this->input->get_post('agency_filter')
									)?'selected="selected"':''; ?>>
									<?php echo $list['agency_name']; ?>
								</option>
								<?php endforeach ?>
							</select>
						</div>

						<div class="col-md-3">
							<label for="phrase_select">Phrase</label>
							<input type="text" name="search_filter" class="form-control" placeholder="All" value="<?php echo $this->input->get_post('search_filter'); ?>" />
						</div>

						<div class="col-md-2 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<button type="submit" class="btn btn-inline">Search</button>
						</div>
						
					</div>

				</div>

				   <!-- DL ICONS START -->
			    <div class="col-md-4 columns">
                    <section class="proj-page-section float-right">
                        <div class="proj-page-attach">
                            <i class="fa fa-file-excel-o"></i>
                            <p class="name"><?php echo $title; ?></p>
                            <p>
								<a href="<?php echo $export_link_params ?>" >
									Export
								</a>
                            </p>
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
							<th>NLM Date</th>
							<th>Address</th>
							<th>State</th>
							<th>Agency</th>
							<th>Reason</th>
							<th>Deactivated By</th>							
							<th>Restore</th>
						</tr>
					</thead>

					<tbody>
						<?php foreach($lists->result_array() as $list_item): 			
						?>
						<tr>
							<td>
								<?php echo ($this->system_model->isDateNotEmpty($list_item['nlm_timestamp']))?$this->system_model->formatDate($list_item['nlm_timestamp'],'d/m/Y'):NULL ?>
							</td>
							<td>
								<?php echo $this->gherxlib->crmLink('vpd',$list_item['property_id'] , "{$list_item['p_address_1']} {$list_item['p_address_2']}, {$list_item['p_address_3']}" ); ?>
							</td>
							<td>
								<?php echo $list_item['p_state']; ?>
							</td>
							<td>
								<?php echo $this->gherxlib->crmLink('vad',$list_item['a_id'],"{$list_item['agency_name']}",'',$list_item['priority']); ?>
							</td>
							<td>
								<?php echo $list_item['p_reason']; ?>
							</td>
							<td>
								<?php  echo ($list_item['a_deleted']==1)?"Agency":"SATS"; ?>
							</td>							
							<td><a data-id="<?php echo $list_item['property_id'] ?>" class="btn btn-sm btn_restore_property" href="#">Click to Restore</a></td>
						</tr>
						<?php endforeach ?>
					</tbody>

				</table>
			</div>

			<nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
			<div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>

		</div>
	</section>

</div>


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

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4>Deactivated Properties</h4>
	<p>This page shows all properties that are inactive in our system. They have been deleted for various reasons.</p>

	<pre>
		<code>
SELECT `p`.`property_id`, `p`.`address_1` AS `p_address_1`, `p`.`address_2` AS `p_address_2`, `p`.`address_3` AS `p_address_3`, `p`.`state` AS `p_state`, `p`.`postcode` AS `p_postcode`, `p`.`comments` AS `p_comments`, `p`.`deleted_date`, `p`.`agency_deleted` AS `a_deleted`, `a`.`agency_id` AS `a_id`, `a`.`agency_name` AS `agency_name`, `a`.`phone` AS `a_phone`
FROM `property` AS `p`
LEFT JOIN `agency` AS `a` ON p.`agency_id` = a.`agency_id`
LEFT JOIN `agency_user_accounts` AS `aua` ON p.`pm_id_new` = aua.`agency_user_account_id`
WHERE `p`.`deleted` = 1
AND `a`.`status` = 'active'
ORDER BY `p`.`deleted_date` DESC
LIMIT 50
</code>
	</pre>

</div>
<!-- Fancybox END -->

<script type="text/javascript">

	jQuery(document).ready(function(){

		$('.btn_restore_property').click(function(e){
			e.preventDefault();
			var obj = $(this);
			var prop_id = obj.attr('data-id');
			
			if(prop_id!=""){

				swal(
                    {
                        title: "",
                        text: "Are you sure you want to restore this property?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-success",
                        confirmButtonText: "Yes",
                        cancelButtonText: "No, Cancel!",
                        closeOnConfirm: false,
                        closeOnCancel: true,
                    },
                    function(isConfirm){
                        if(isConfirm){

							$('#load-screen').show(); //show loader
							swal.close();

							jQuery.ajax({
                                type: "POST",
                                url: "<?php echo base_url('/properties/restore_property') ?>",
                                dataType: 'json',
                                data: { 
                                    prop_id: prop_id
                            }
							}).done(function(res){
								if(res.status){ //true
                                    
									$('#load-screen').hide(); //hide loader

									swal({
										title:"Success!",
										text: res.msg,
										type: "success",
										showCancelButton: false,
										confirmButtonText: "OK",
										closeOnConfirm: false,
									},function(isConfirm){
										if(isConfirm){ 
											swal.close();
											location.reload();
										}
									});

								}else{
									$('#load-screen').hide(); //hide loader
									swal('',res.msg,'error');
								}

							});

                        }else{
                            return false;
                        }
                        
                    }
            	);	

			}else{
				swal('','Error: Null Property ID','error');
				return false;
			}

		})

	})

</script>