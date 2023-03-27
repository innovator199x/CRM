<?php


  	$export_links_params_arr = array(
		'date_from_filter' => $this->input->get_post('date_from_filter'),
		'date_to_filter' => $this->input->get_post('date_to_filter'),
		'sales_rep_filter' => $this->input->get_post('sales_rep_filter'),
		'state_filter' =>  $this->input->get_post('state_filter'),
		'get_sats'=> $this->input->get_post('get_sats'),
		'export' => 1
	);
	$export_link_params = "/reports/sales_activity/?".http_build_query($export_links_params_arr);



	# Create predefined date ranges
	$today = date('d/m/Y');

	$date_ranges = array();

	$date_ranges[] = array(
	'title' => 'All',
	'from' => 'all',
	'to' => 'all'
	);

	$date_ranges[] = array(
	'title' => 'Today',
	'from' => date('d/m/Y'),
	'to' => date('d/m/Y')
	);

	$date_ranges[] = array(
	'title' => 'Yesterday',
	'from' => date('d/m/Y', (strtotime('-1 days'))),
	'to' => date('d/m/Y', (strtotime('-1 days')))
	);

	$date_ranges[] = array(
	'title' => 'Last Week',
	'from' => date('d/m/Y', strtotime('previous week Monday') ),
	'to' => date('d/m/Y', strtotime('previous week Sunday') )
	);

	$date_ranges[] = array(
	'title' => 'Next Week',
	'from' => $today,
	'to' => date('d/m/Y', (strtotime('+7 days')))
	);



	$date_ranges[] = array(
	'title' => date("F",mktime(0,0,0, (date("n") - 1 + 12) % 12, 1)),
	'from' => date("01/m/Y",mktime(0,0,0, (date("n") - 1 + 12) % 12, 1)),
	'to' => date("t/m/Y",mktime(0,0,0, (date("n") - 1 + 12) % 12, 1))
	);


	$date_ranges[] = array(
	'title' => date("F",mktime(0,0,0, (date("n") - 2 + 12) % 12, 1)),
	'from' => date("01/m/Y",mktime(0,0,0, (date("n") - 2 + 12) % 12, 1)),
	'to' => date("t/m/Y",mktime(0,0,0, (date("n") - 2 + 12) % 12, 1))
	);

	$date_ranges[] = array(
	'title' => date("F",mktime(0,0,0, (date("n") - 3 + 12) % 12, 1)),
	'from' => date("01/m/Y",mktime(0,0,0, (date("n") - 3 + 12) % 12, 1)),
	'to' => date("t/m/Y",mktime(0,0,0, (date("n") - 3 + 12) % 12, 1))
	);






?>
<style>
	.col-mdd-3{
		max-width:15%;
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
			'link' => "/reports/sales_activity"
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
		echo form_open('/reports/sales_activity',$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-md-9 columns">
					<div class="row">

						<div class="col-mdd-3">
							<label for="date_select">Report from:</label>
							<input name="date_from_filter" placeholder="ALL" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $from ?>">
						</div>

						<div class="col-mdd-3">
							<label for="date_select">to:</label>
							<input name="date_to_filter" placeholder="ALL" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $to ?>">
                        </div>
                        
                        <div class="col-mdd-3">
							<label for="date_select">Sales Rep</label>
                            <select name="sales_rep_filter" id="sales_rep_filter" class="form-control">

                                <option value="">ALL</option>
                                <?php
                                    $salesRepQuery = $this->system_model->getSalesRep();
                                    foreach($salesRepQuery->result_array() as $row){
                                ?>
                                        <option value="<?php echo $row['staff_accounts_id'] ?>" <?php echo ($row['staff_accounts_id']==$this->input->get_post('sales_rep_filter'))?'selected="selected"':'' ?> ><?php echo $row['FirstName']." ".$row['LastName'] ?></option>
                                <?php
                                    }
                                ?>

                            </select>

                        </div>

                          <div class="col-mdd-3">
							<label for="date_select">State</label>
                            <select name="state_filter" id="state_filter" class="form-control">

                                <option value="">ALL</option>
                                <?php
                                    $stateQuery = $this->system_model->getAllAgencyState();
                                    foreach($stateQuery->result_array() as $row){
                                ?>
                                        <option value="<?php echo $row['state'] ?>" <?php echo ($row['state']==$this->input->get_post('state_filter'))?'selected="selected"':'' ?> ><?php echo $row['state'] ?></option>
                                <?php
                                    }
                                ?>
                            </select>

                        </div>

						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							
							<?php if(is_int($staff_id)): ?>
							<input type="hidden" name="sid" value="<?php echo $staff_id; ?>" class="submitbtnImg">	
							<?php endif; ?>	
							<?php if(is_int($tech_id)): ?>
							<input type="hidden" name="tid" value="<?php echo $tech_id; ?>" class="submitbtnImg">	
							<?php endif; ?>

							<input type="hidden" name="get_sats" value="1" />
							<input type="submit" class="btn" value="Get Stats" name="btnGetStats" >
						</div>
						
					</div>

                </div>
                
                <div class="col-lg-3 col-md-12 columns">
                    <section class="proj-page-section float-right">
                        <div class="proj-page-attach">
                            <i class="fa fa-file-excel-o"></i>
                            <p class="name"><?php echo $title; ?></p>
                            <p>
								<a href="<?php echo $export_link_params ?>">
									Export
								</a>
                            </p>
                        </div>
                    </section>
				</div>
            </div>
            
            <div class="for-groupss row quickLinksDiv">
        <div class="text-left col-md-3 columns">

           <?php echo $this->customlib->generateLink($prev_day, $staff_filter); ?>

        </div>
        <div class="text-center col-md-6 columns">

           Quick Links&nbsp;|&nbsp;
		    <?php foreach($date_ranges as $index=>$range): ?>
				<?php echo $this->customlib->generateLink($range, $staff_filter); ?>
				<? if($index < sizeof($date_ranges) - 1): ?>
				&nbsp;|&nbsp;
				<? endif; ?>		
			<?php endforeach; ?>	

        </div>
        <div class="text-right col-md-3 columns">
			
			<?php echo $this->customlib->generateLink($next_day, $staff_filter); ?>


        </div>
            </div>
			</form>
		</div>
	</header>
<?php 
	if($this->input->get_post('btnGetStats') || $this->input->get_post('get_sats')==1){
	
		
?>
	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<table class="table table-hover main-table">
					<thead>
						<tr>
							<th>Date</th>
							<th>Sales Rep</th>
							<th>Type</th>
							<th>Agency</th>
							<th>Status</th>
							<th>Comment</th>
							<th>Next Contact</th>
						</tr>
					</thead>

					<tbody>
						<?php
							if($lists->num_rows()>0){
							foreach($lists->result_array() as $row){
						?>

								<tr>
									<td><?php echo date('d/m/Y', strtotime($row['eventdate'])) ?></td>
									<td><?php echo "{$row['FirstName']} {$row['LastName']}" ?></td>
									<td><?php echo $row['contact_type'] ?></td>
									<td class="<?php echo ( $row['priority'] > 0 )?'j_bold':null; ?>">
										<?php echo $row['agency_name']." ".( ( $row['priority'] > 0 )?' ('.$row['abbreviation'].')':null ); ?>
									</td>
									<td><?php echo $row['a_status'] ?></td>
									<td><?php echo $row['ael_comments'] ?></td>
									<td><?php echo ( $this->system_model->isDateNotEmpty($row['next_contact']) )?date('d/m/Y',strtotime($row['next_contact'])):''; ?></td>
								</tr>

						<?php
							}}else{
								echo "<tr><td colspan='7'>No Data</td></tr>";
							}
						?>
					</tbody>

				</table>
			</div>

			<nav class="text-center">
				<?php echo $pagination; ?>
			</nav>

			<div class="pagi_count text-center">
				<?php echo $pagi_count; ?>
			</div>

		</div>
	</section>

<?php
	}else{
		echo "<h3>Press 'Get Stats' to Display Results</h3>";
	}
?>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4>Sales Activity Report</h4>
	<p>This report shows all sales related activity for the given date period selected.</p>
<pre>
<code>SELECT `ael`.`comment` AS `ael_comments`, `a`.`status` AS `a_status`, `ael`.`date` as `eventdate`, `mlt`.`contact_type`, `ael`.`next_contact`, `sa`.`LastName`, `sa`.`FirstName`, `a`.`agency_name`
FROM `sales_report` as `ael`
LEFT JOIN `agency` as `a` ON `a`.`agency_id` = `ael`.`agency_id`
LEFT JOIN `staff_accounts` as `sa` ON `sa`.`StaffID` = `ael`.`staff_id`
LEFT JOIN `main_log_type` as `mlt` ON `mlt`.`main_log_type_id`=`ael`.`contact_type`
WHERE `sa`.`deleted` = 0
AND `sa`.`active` = 1
AND `sa`.`ClassID` = 5
AND `a`.`country_id` = <?php echo COUNTRY ?> 
AND `ael`.`date` >= '$date_from'
AND `ael`.`date` <= '$date_to'
 LIMIT 50</code>
</pre>
</div>
<!-- Fancybox END -->
