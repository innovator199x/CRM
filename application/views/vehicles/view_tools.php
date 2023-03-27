<div class="box-typical box-typical-padding">
	<?php 
		// breadcrumbs template
		$bc_items = array(
			array(
				'title' => "Reports",
				'link' => "/reports"
			),
			array(
				'title' => $title,
				'status' => 'active',
				'link' => "/vehicles/view_tools"
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
		echo form_open('vehicles/view_tools',$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-md-10 columns">
					<div class="row">
							<div class="col-mdd-3">
										<label for="item_select">Item:</label>
										<select id="item_filter" name="item_filter" class="form-control field_g2 select2-photo">
											<option value="">All</option>
											<?php
												foreach($item_filter->result_array() as $item_row){
											?>
													<option value="<?php echo $item_row['item'] ?>"  <?php echo ($this->input->get_post('item_filter')==$item_row['item'])?"selected='selected'":'' ?> ><?php echo $item_row['item_name'] ?></option>
											<?php 
												}
											?>
										</select>
							</div>

							<div class="col-mdd-3">
										<label for="vehicle_select">Vehicle:</label>
										<select id="vehicle_filter" name="vehicle_filter" class="form-control field_g2 select2-photo">
											<option value="">All</option>
											<?php
												foreach($vehicle_filter->result_array() as $row){
													if(!empty($row['vehicles_id'])){
											?>
													<option value="<?php echo $row['vehicles_id']; ?>"  <?php echo ($this->input->get_post('vehicle_filter')==$row['vehicles_id'])?"selected='selected'":'' ?> ><?php echo $row['number_plate']; ?></option>
											<?php
													}
												}
											?>
										</select>
							</div>

							<div class="col-md-3">
								<label for="phrase_select">Phrase</label>
								<input type="text" id="search_filter" name="search_filter" class="form-control" placeholder="All" value="<?php echo $this->input->get_post('search_filter'); ?>" />
							</div>

							<div class="col-md-1 columns">
								<label class="col-sm-12 form-control-label">&nbsp;</label>
								<button type="submit" class="btn btn-inline">Go</button>
							</div>

					</div>
				</div>
				<div class="col-md-2 columns text-right">
							<div style="height:68px;" class="flex_box flex_box_vr_center flex_box_hr_right">
								<a class="btn btn-danger" href="/vehicles/add_tools" role="button">Add Tools</a>
							</div>
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
							<th>Item</th>
							<th>Item ID</th>
							<th>Brand</th>
							<th>Description</th>
							<th>Purchase Date</th>
							<th>Purchase Price</th>
                            <th>Vehicle</th>
                            <th>Last Inspection</th>
                            <th>Next Inspection</th>
						</tr>
					</thead>

					<tbody>
						<?php foreach($lists->result_array() as $list_item){
						

							//last and nex inspection tweak
							if( $list_item['item']==1 || $list_item['item']==2 || $list_item['item']==4 ){
								$jparams = array(
									'item' => $list_item['item'],
									'tools_id' => $list_item['tools_id']
								);
								

								$tools2_sql = $this->system_model->getToolsLastInspection($jparams);
								if( $tools2_sql->num_rows()>0 ){
									$tools2 = $tools2_sql->row_array();
									
									// Age
									$next_insp_last_30days = date('Y-m-d',strtotime($tools2['inspection_due'].' -30 days'));  
									$today = date('Y-m-d');
									
									$last_insp = $tools2['date'];
									$last_insp2 = ( $this->system_model->isDateNotEmpty($last_insp) )?$this->system_model->formatDate($last_insp,'d/m/Y'):'';
									$next_insp = $tools2['inspection_due'];
									$next_insp2 = ( $this->system_model->isDateNotEmpty($next_insp) )?$this->system_model->formatDate($next_insp,'d/m/Y'):'';
								}else{
									$last_insp2 = '';
									$next_insp2 = '';
								}
								
							}	

						?>
						<tr>
							<td>
								<?php echo  $list_item['ti_itemname']; ?>
							</td>

                            <td>
								<?php
								// echo $this->gherxlib->crmLink('tools',$list_item['tools_id'],$list_item['t_itemid']);
								 ?>
								 <a href="/vehicles/view_tool_details/<?php echo $list_item['tools_id'] ?>"><?php echo $list_item['t_itemid']; ?></a>
							</td>

                            <td>
								<?php echo  $list_item['t_brand']; ?>
							</td>

                            <td>
								<?php echo  $list_item['t_description']; ?>
							</td>

                            <td>
								<?php echo  date('d/m/Y', strtotime($list_item['t_purchase_date'])); ?>
							</td>

                            <td>
								<?php echo  "$".$list_item['t_purchase_price']; ?>
							</td>

                            <td>
								<?php echo  $list_item['number_plate']; ?>
							</td>

                            <td>
							<?php	
									echo $last_insp2;
							?>
							</td>

                            <td>
								<span <?php echo ( ($today>=$next_insp_last_30days) || ($today>=$next_insp) )?'class="text-red"':''; ?>><?php echo $next_insp2; ?></span>
							</td>
							
						</tr>

						<?php } ?>

					</tbody>

				</table>
			</div>

			<nav aria-label="Page navigation example" style="text-align:center">
				<?php echo $pagination; ?>
			</nav>

			<div class="pagi_count text-center">
				<?php echo $pagi_count; ?>
			</div>

					

		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4>View Tools</h4>
	<p>
	This page shows all tools in the system and which vehicle they are assigned to.
	</p>
	<pre>
<code>SELECT `t`.`item_id` AS `t_itemid`, `t`.`item`, `t`.`purchase_date` AS `t_purchase_date`, `t`.`purchase_price` AS `t_purchase_price`, `t`.`brand` AS `t_brand`, `t`.`description` AS `t_description`, `t`.`tools_id`, `ti`.`item_name` AS `ti_itemname`, `v`.`number_plate`
FROM `tools` AS `t`
LEFT JOIN `tool_items` AS `ti` ON t.`item` = ti.`tool_items_id`
LEFT JOIN `vehicles` AS `v` ON t.`assign_to_vehicle` = v.`vehicles_id`
WHERE `t`.`country_id` = <?php echo COUNTRY ?> 
ORDER BY `t`.`item_id` ASC
LIMIT 50</code>
	</pre>

</div>
<!-- Fancybox END -->

