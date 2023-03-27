<style>
	.mx-2{
		max-width: 22.3%;
	}
	#salesmarker{
		margin-top: 10px;
    	transform: scale(1.5);
	}
	.j_icons {
		margin-right: 0px;
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
		echo form_open($uri,$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-md-10 columns">
					<div class="row">

						<div class="mx-2">
							<label>Agency</label>
							<select id="agency_filter" name="agency_filter" class="form-control">
								<option value="">ALL</option>
								<?php
								foreach( $agency_filter_sql->result() as $agency_row ){ ?>
									<option value="<?php echo $agency_row->agency_id; ?>" <?php echo ( $agency_row->agency_id == $this->input->get_post('agency_filter') )?'selected="selected"':null; ?>><?php echo $agency_row->agency_name; ?></option>
								<?php
								}
								?>
							</select>
							<div class="mini_loader"></div>
						</div>

						<div class="mx-2">
							<label for="date_select">Date From</label>
							<input placeholder="ALL" name="dateFrom_filter" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $this->input->get_post('dateFrom_filter'); ?>">
						</div>

						<div class="mx-2">
							<label for="date_select">Date To</label>
							<input placeholder="ALL" name="dateTo_filter" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $this->input->get_post('dateTo_filter'); ?>">
						</div>

						<div class="mx-2">
							<label for="phrase_select">Phrase</label>
							<input placeholder="ALL" type="text" name="search_filter" class="form-control" value="<?php echo $this->input->get_post('search_filter'); ?>" />
						</div>
						
						<div class="mx-2">
							<label for="alarm_brand_filter">Alarm Brand</label>
							<input placeholder="ALL" type="text" name="alarm_brand_filter" class="form-control" value="<?php echo $this->input->get_post('alarm_brand_filter'); ?>" />
						</div>						

						<div class="mx-2">
							<label for="phrase_select">State</label>
							<select id="state_filter" name="state_filter" class="form-control">
								<option value="">ALL</option>
								<?php
								foreach( $state_filter_sql->result() as $state_row ){ ?>
									<option value="<?php echo $state_row->state; ?>" <?php echo ( $state_row->state == $this->input->get_post('state_filter') )?'selected="selected"':null; ?>><?php echo $state_row->state; ?></option>
								<?php
								}
								?>								
							</select>
						</div>

						<div class="mx-2">
							<label for="phrase_select">Sales Upgrade</label>						
							<div class="checkbox">
								<input type="checkbox" name="sales" id="salesmarker" class="form-control" value="1" <?php if(isset($_POST['sales'])) echo "checked='checked'"; ?>>
								<label for="salesmarker">&nbsp;</label>
							</div>
						</div>

						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<!--<button type="submit" name="search_submit" class="btn">Search</button>-->
							<input type="submit" name="search_submit" class="btn" value="Search">
						</div>
						
					</div>

				</div>

                <!-- DL ICONS START -->
        
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
							<th>
								Date
								<a 
									data-toggle="tooltip" 
									class="a_link <?php echo $sort ?>" 
									href="<?php echo "/jobs/completed_ic_upgrade/?sort_header=1&order_by=j.date&sort={$toggle_sort}&".http_build_query($header_link_params); ?>"
								>
									<em class="fa fa-sort-<?php echo $sort; ?>"></em>
								</a>
							</th>
							<th>Service</th>
							<th>Price EX. GST</th>
							<th>Alarm Cost EX. GST</th>							
                            <th>Address</th>
							<th><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></th>
							<th>
								Agency
								<a 
									data-toggle="tooltip" 
									class="a_link <?php echo $sort ?>" 
									href="<?php echo "/jobs/completed_ic_upgrade/?sort_header=1&order_by=a.agency_id&sort={$toggle_sort}&".http_build_query($header_link_params); ?>"
								>
									<em class="fa fa-sort-<?php echo $sort; ?>"></em>
								</a>
							</th>	
							<th>Alarms Installed</th>		
						</tr>
					</thead>

					<tbody>
						<?php
						if($this->input->get_post('search_submit')){
						
							foreach( $lists as $list_item ){	
							?>
							<tr>
								<td>
									<a href="<?php echo $this->config->item("crm_link"); ?>/view_job_details.php?id=<?php echo $list_item['jid']; ?>">
										<?php echo $this->system_model->formatDate($list_item['j_date'],'d/m/Y'); ?>
									</a>								
								</td>
								<td>									
									<?php
									// display icons
									$job_icons_params = array(
										'job_id' => $list_item['jid']
									);
									echo $this->system_model->display_job_icons_v2($job_icons_params);
									?>
								</td>
								<td>
									<?php 
									//echo number_format($list_item['invoice_amount'],2); 
									echo ( $list_item['invoice_amount'] > 0 )?'$'.number_format($this->system_model->price_ex_gst($list_item['invoice_amount']),2):null;
									?>
								</td>
								<td>
									<?php 
									//echo $list_item['cost_of_alarms']; 									
									//echo ( $list_item['cost_of_alarms'] > 0 )?'$'.number_format($this->system_model->price_ex_gst($list_item['cost_of_alarms']),2):null;
									echo ( $list_item['cost_of_alarms_ex_gst'] > 0 )?'$'.number_format($list_item['cost_of_alarms_ex_gst'],2):null;
									?>
								</td>								
								<td>

								<?php
									$prop_address = $list_item['p_address_1']." ".$list_item['p_address_2'].", ".$list_item['p_address_3'];
									echo $this->gherxlib->crmLink('vpd',$list_item['prop_id'],$prop_address);
								?>
							
                                </td>
								<td>
								<?php echo $list_item['p_state']; ?>
								</td>
								<td class="<?php echo ( $list_item['priority'] > 0 )?'j_bold':null; ?>">
									<a href="/agency/view_agency_details/<?php echo $list_item['a_id']; ?>">
									<?php echo $list_item['agency_name']." ".( ( $list_item['priority'] > 0 )?' ('.$list_item['abbreviation'].')':null ); ?>
									</a>
                                </td>
								<td><?php echo $list_item['alarm_power_used']; ?></td>
							</tr>
							<?php 
							}	
							?>
							<tr>
								<td colspan="3"><b>TOTAL JOBS: <?php echo $total_rows; ?></b></td>
								<td>
									<b>
										<?php 
										//echo number_format($invoice_amount_tot,2);
										//echo ( $invoice_amount_tot > 0 )?'$'.number_format($this->system_model->price_ex_gst($invoice_amount_tot),2):null;
									 	foreach($job_tot_row as $invoice_amount_tot_row){
											 $invoice_amount_tot += $this->system_model->price_ex_gst($invoice_amount_tot_row['invoice_amount']);
										 }

										 echo "$".number_format($invoice_amount_tot,2);
										 ?>
									</b>
								</td>
								<td colspan="6">
									<b>
										<?php 
										//echo number_format($total_cost_of_alarms,2);
										//echo ( $total_cost_of_alarms > 0 )?'$'.number_format($this->system_model->price_ex_gst($total_cost_of_alarms),2):null;
										echo ( $cost_of_alarms_tot_ex > 0 )?'$'.number_format($cost_of_alarms_tot_ex,2):null;
										?>
									</b>
								</td>
							</tr>
						<?php
						}else{
							echo "<tr><td colspan='100%'>Press Search to display data</td></tr>";
						}
						?>
					</tbody>

				</table>
				
			</div>

			<?php
			if( $this->input->get_post('search_submit') ){ ?>
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
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>This report needs to show any actual upgrades completed regardless if client with sats any longer. All prices are exclusive of GST.</p>
<pre>
<code><?php echo $query_string ?></code>
</pre>
</div>
<!-- Fancybox END -->