<?php

//$toggle_sort = ( $sort == 'asc' )?'desc':'asc';	

  $export_links_params_arr = array(
	'date_from' => $this->input->get_post('date_from'),
	'date_to' => $this->input->get_post('date_to'),
	'agency_filter' => $this->input->get_post('agency_filter'),
	'search_filter' =>  $this->input->get_post('search_filter')
);
$export_link_params = '/properties/export_deleted_properties/?'.http_build_query($export_links_params_arr);
?>
<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/properties/duplicate_properties"
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
echo form_open('/properties/duplicate_properties',$form_attr);
?>
	<div class="for-groupss row">
		<div class="col-lg-10 col-md-10 columns">
			<div class="row">


				<div class="col-mdd-3">
					<label for="agency_filter">Agency</label>
					<select id="agency_filter" name="agency_filter" class="form-control field_g2">
						<option value="">ALL</option>
						<?php 
							foreach($agency_filter->result_array() as $row){
							$selected =  ($this->input->get_post('agency_filter')==$row['agency_id'])?'selected':NULL;
						?>
							<option <?php echo $selected; ?> value="<?php echo $row['agency_id'] ?>"><?php echo "{$row['agency_name']}" ?></option>
						<?php
							}
						?>
					</select>
				</div>

		
				<div class="col-md-1 columns">
					<label class="col-sm-12 form-control-label">&nbsp;</label>
					<input class="btn" type="submit" name="btn_search" value="Search">
				</div>
				
			</div>

		</div>


		<div class="col-lg-2 columns">
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
		</form>
	</div>

</header>

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<table class="table table-hover main-table">
					<thead>
						<tr>
							<th>Property ID</th>
							<th>Address</th>
							<th>Suburb</th>
							<th>Postcode</th>
							<th>State</th>							
							<th>Agency Name</th>
                            <th>Status</th>
						</tr>
					</thead>

					<tbody>
						<?php 
						 	$i = 0;
							foreach($lists->result_array() as $d){
						?>
								 <tr <?php echo ($i%2==0)?'style="border-right: 1px solid #cccccc; background-color: #efefef;"':''; ?>>
									<td>
										<?php echo $this->gherxlib->crmLink('vpd',$d['property_id'], $d['property_id']); ?>
									</td>
									<td><?php echo "{$d['address_1']} {$d['address_2']}"; ?></td>
									<td><?php echo $d['address_3']; ?></td>
									<td><?php echo $d['postcode']; ?></td>
									<td><?php echo $d['state']; ?></td>
									<td>
										<?php echo $this->gherxlib->crmLink('vad',$d['agency_id'],$d['agency_name']); ?>
									</td>
										<td><?php echo ($d['deleted']==1)?'<span class="text-red">Inactive</span>':'<span class="text-green">Active</span>'; ?></td>
									</tr>
								
								<?php
								$dup_sql2 = $this->properties_model->jGetOtherDupProp($d['property_id'],$d['address_1'],$d['address_2'],$d['address_3'],$d['state'],$d['postcode']);
								
								if(!empty($dup_sql2)){
									foreach($dup_sql2->result_array() as $d2){
									?>

										<tr <?php echo ($i%2==0)?'style="border-right: 1px solid #cccccc; background-color: #efefef;"':''; ?>>
											<td>
												<?php echo $this->gherxlib->crmLink('vpd',$d2['property_id'], $d2['property_id']); ?>
											</td>
											<td><?php echo "{$d2['address_1']} {$d2['address_2']}"; ?></td>
											<td><?php echo $d2['address_3']; ?></td>
											<td><?php echo $d2['postcode']; ?></td>
											<td><?php echo $d2['state']; ?></td>
											<td>
												<?php echo $this->gherxlib->crmLink('vad',$d2['agency_id'],$d2['agency_name']); ?>
											</td>
											<td><?php echo ($d2['deleted']==1)?'<span class="text-red">Inactive</span>':'<span class="text-green">Active</span>'; ?></td>
										</tr>
					
									<?php
									}
								}
								?>
						<?php
						 $i++;
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

	<h4><?php echo $title; ?></h4>
	<p>This page shows properties in our system that are possible duplicates</p>
<pre>
	<!--
<code>SELECT `p`.`property_id`, `p`.`address_1`, `p`.`address_2`, `p`.`address_3`, `p`.`state`, `p`.`postcode`, `p`.`deleted`, COUNT( * ) AS jcount, `a`.`agency_id`, `a`.`agency_name`
FROM `property` as `p`
LEFT JOIN `agency` as `a` ON `a`.`agency_id` = `p`.`agency_id`
WHERE `p`.`address_1` != ''
AND `p`.`address_2` != ''
AND `p`.`address_3` != ''
AND `p`.`is_sales` = 0
AND `a`.`country_id` = <?php echo COUNTRY ?> 
GROUP BY TRIM( p.`address_1` ), TRIM( p.`address_2` ), TRIM( p.`address_3` ), TRIM( p.`state` ), TRIM( p.`postcode` )
HAVING `jcount` > 1
LIMIT 50</code>
-->

<?php echo $last_query; ?>
</pre>
</div>
<!-- Fancybox END -->

<script type="text/javascript">

	jQuery(document).ready(function(){

	

	})

</script>