<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}

.green_check{
	display:none;
	width: 30px;
	margin-top: 5px;
}

.region_name, .sub_region_name {
    width: 200px;
}
.region_state{
	width:50px;
}

.jtable .header th{
	padding-left: 7px;
}

#jtable_inner{
	border-collapse: separate;
}
#jtable_inner tr td{border:none;}
</style>
<?php

//$toggle_sort = ( $sort == 'asc' )?'desc':'asc';	

  $export_links_params_arr = array(
	'date_from' => $this->input->get_post('date_from'),
	'date_to' => $this->input->get_post('date_to'),
	'agency_filter' => $this->input->get_post('agency_filter'),
	'search_filter' =>  $this->input->get_post('search_filter')
);
$export_link_params = '/properties/duplicate_postcode/?'.http_build_query($export_links_params_arr);
?>
<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/properties/duplicate_postcode"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>


	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<table class="table table-hover main-table">
					<thead>
						<tr>
						<th style="width: 100px;">Postcode</th>	
							<th class="region_state"> <?php echo $this->gherxlib->getDynamicState($this->config->item('country')) ?></th>
							<th class="region_name"> <?php echo $this->gherxlib->getDynamicRegion($this->config->item('country')) ?></th>
							<th class="sub_region_name">Sub Region</th>
							<th>Postcodes</th>							
						</tr>
					</thead>

					<tbody>

						<?php
						$count = 0;
						if(!empty($duplicate)){
							foreach($duplicate as $pc){
						?>

								<tr>
									<td align="left"><a href="/properties/view_regions/<?php echo $pc; ?>"><?php echo $pc; ?></a></td>
									<td colspan="4">
										<?php

										$getRegion_params = array(
											'sel_query' => "sr.subregion_name as postcode_region_name, sr.sub_region_id as postcode_region_id",
											'postcode' => $pc,
											'sort_list' => array(
												array(
													'order_by' => 'r.region_state',
													'sort' => 'ASC'
												)
											)
										);
										$regions = $this->system_model->get_postcodes($getRegion_params);

										?>
										<table class="table-left tbl-fr-red" id="jtable_inner">
											
										
											
											<?php


											// fetch the current row into the array $row
											foreach($regions->result_array() as $region){
												?>
												<tr>
												
													<td class="region_state"><?=$region['region_state'];?></td>
													<td class="region_name"><?=$region['region_name'];?></td>	
													<td class="sub_region_name"><?=$region['postcode_region_name'];?></td>
													<td>
														<?php 
														//echo str_replace(",",", ",$region['postcode_region_postcodes']);
														echo $region['postcode'] ."&nbsp;";
														?>
													</td>									

												</tr>
											
										<? } ?>						
									</table>
									</td>
								</tr>

						<?php
							$count++;
							}
						}else{
							echo "<tr><td colspan='5'>There are no duplicate postcodes</td></tr>";
						}
						?>

					</tbody>

				</table>
			</div>

			<div>Total: <?php echo $count; ?></div>

			<?php
			$total_rows = $count;

			// update page total
			$page_tot_params = array(
				'page' => $page_url,
				'total' => $total_rows
			);
			$this->system_model->update_page_total($page_tot_params);
			?>

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
	<p>This page will find if the same postcode has been added in multiple regions</p>

</div>
<!-- Fancybox END -->

<script type="text/javascript">

	jQuery(document).ready(function(){

	

	})

</script>