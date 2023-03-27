<?php 


	
	# Display dates in dd/mm/yyyy
	$to_display = $this->gherxlib->convertDateAus($to);
	$from_display = $this->gherxlib->convertDateAus($from);

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

td.highlighted {
   	background-color: #e0f2c0;	
}
.au_bg_color {
    /*background-color: #000080 !important;*/
    background-color: #00a8ff !important;
    color: white;
}
.nz_bg_color {
    /*background-color: #000000 !important;*/
    background-color: #00a8ff !important;
    color: white;
}

tr td {
	text-align: center;
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
			'link' => "/reports/report_admin"
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
		echo form_open('/reports/report_admin',$form_attr);
			?>
				<div class="for-groupss row">
					<div class="col-md-12 columns">
						<div class="row">

							<div class="col-mdd-3">
								<label for="date_select">Report from:</label>
								<input name="date_from_filter" placeholder="ALL" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?=isset($from) ? $from : "" ?>">
							</div>

							<div class="col-mdd-3">
								<label for="date_select">to:</label>
								<input name="date_to_filter" placeholder="ALL" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?=isset($to) ? $to : "" ?>">
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
								<button type="submit" class="btn btn-inline">Get Stats</button>
							</div>

						</div>
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
	if( $getSats == 1 ){ ?>
		<?php
			$countries = array(
				1=>'au',
				2=>'nz');
			$country_id = $this->config->item('country');
			$country_iso = $this->config->item('country') == 1 ? 'au' : 'nz';
		?>
		<?php
			// foreach($countries AS $country_id=>$country_iso){ 
				?>
				<h5 class="m-t-lg with-border">Type Breakdown</h5>
				<table id="table-sm" class="table table-bordered table-hover">
					<thead>
						<tr>	
							<?php

							$jobType = array('Yearly Maintenance' => 0, 
										'Change of Tenancy' => 0, 
										'Lease Renewal' => 0, 
										'Annual Visit'=>0,
										'IC Upgrade'=>0,
										'Fix or Replace' => 0, 
										'Once-off' => 0
									);
					        $jobCount = $this->system_model->ra_job_type_count($from,$to,'',array_keys($jobType),$country_id);
					        $outArr = $jobCount->result_array();

					    	foreach ($outArr as $out) {
					    		if (in_array($out['job_type'], array_keys($jobType))) {
					    			$jobType[$out['job_type']] = $out['num_jobs'];
					    		}
					    	}

							foreach ($jobType as $key => $value) {
							?>
								<th class="<?php echo $country_iso; ?>_bg_color"><?=$key?></th>
							<?php
							}
						 	?>
							<th class="<?php echo $country_iso; ?>_bg_color">240v Rebook</th>
							<th class="<?php echo $country_iso; ?>_bg_color">Total</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<?php
							foreach ($jobType as $key => $value) { 
							?>

								<td data-key="<?php echo $key; ?>" style="text-align: center;"><?php echo ( $value > 0 )?$value:null; ?></td>
								
							<?php
							}
						 	?>
							 <td data-key="<?php echo 'dynamic_240_rebook'; ?>" style="text-align: center;">
								<?php 
									$ra_job_type_count_2_params = array(
										'from_date' =>  $from,
										'to_date' => $to
									);
									$rebook_q = $this->system_model->ra_job_type_count_2($ra_job_type_count_2_params)->row_array(); ##get 240 rebook marked only
									$tt_merge = array('tt'=> $rebook_q['num_jobs']);
									echo ($rebook_q['num_jobs']>0)?$rebook_q['num_jobs']:null;
								?>
							</td>

						 	<?php
							 $tt_awaw = array_merge($jobType,$tt_merge); ## merge 240v rebook count
							 $type_tot = array_sum(array_values($tt_awaw));
							?>

							<td class='<?php echo ($type_tot > 0 ? "highlighted" : "no_result");?>' style="text-align: center;"><?php echo $type_tot; ?></td>
						</tr>
					</tbody>
				</table>

				<div class="row">
				  <div class="col-sm-6">
				<h5 class="m-t-lg with-border">Tech Completed Jobs</h5>
				<table id="table-sm" class="table table-bordered table-hover">
						<thead>
							<tr>
								<th class="<?php echo $country_iso; ?>_bg_color" width='210'>Tech Name</th>
								<th class="<?php echo $country_iso; ?>_bg_color">Completed Jobs</th>
								<th class="<?php echo $country_iso; ?>_bg_color">DKs Achieved</th>
							</tr>
						</thead>
						<tbody>
							<?php 
								$tech = $this->reports_model->getTechCompletedJobs($from, $to, $country_id);
								foreach ($tech as $val) {
							?>
								<tr>
									<td><?php echo "{$val['FirstName']} {$val['LastName']}"; ?></td>
									<td align='center' class="highlighted"><?php echo $comp_j = $val['num_jobs']; ?></td>
									<td>
									<?php 
									$dk_count = $this->reports_model->ra_dk_completed($from,$to,$val['StaffID'],$country_id);
									echo ( $dk_count > 0 )?$dk_count:null;
									?>
									</td>
								</tr>	
							<?php 
								} 
							?>
						</tbody>
					</tr>
				</table>  
			  </div>
			  <div class="col-sm-6">
				<h5 class="m-t-lg with-border">Staff Booked Jobs</h5>  
				<table id="table-sm" class="table table-bordered table-hover">
						<thead>
							<tr>
								<th class="<?php echo $country_iso; ?>_bg_color" width='210'>Staff Name</th>
								<th class="<?php echo $country_iso; ?>_bg_color">Booked</th>
								<th class="<?php echo $country_iso; ?>_bg_color">Entry Notices</th>
								<th class="<?php echo $country_iso; ?>_bg_color">Door Knocks</th>
								<th class="<?php echo $country_iso; ?>_bg_color">Total Booked</th>
							</tr>
						</thead>
						<tbody>
							<?php 
							$staff_sql = $this->reports_model->getStaffBookedJobs($from, $to, $country_id);
							foreach ($staff_sql as $staff) {
								?>
									<tr>
										<td>
											<?php echo "{$staff['FirstName']} {$staff['LastName']}"; ?>
										</td>
										<td>
											<?php
												$sbnon_endk_sql = $this->reports_model->getStaffNoEnNoDKBookedJobs($from,$to,$staff['StaffID'],$country_id); 
												echo ( $sbnon_endk_sql[0]['num_jobs'] > 0 )?$sbnon_endk_sql[0]['num_jobs']:null;
											?>
										</td>
										<td class="staff_booked_total">
											<?php
												$sben_sql = $this->reports_model->getStaffENBookedJobs($from,$to,$staff['StaffID'],$country_id); 
												echo ( $sben_sql[0]['num_jobs'] > 0 )?$sben_sql[0]['num_jobs']:null;
											?>
										</td>
										<td>
											<?php 
											$sbdk_sql = $this->reports_model->getStaffDKBookedJobs($from,$to,$staff['StaffID'],$country_id); 
											echo ( $sbdk_sql[0]['num_jobs'] > 0 )?$sbdk_sql[0]['num_jobs']:null;
											?>
										</td>
										<td class="highlighted">
											<?php echo ( $staff['num_jobs'] > 0 )?$staff['num_jobs']:null; ?>
										</td>
									</tr>	
								<?php	
								//}
							} 
							?>
						</tbody>
					</tr>
				</table>
  </div>
</div>
				   


				 

		<?php	
			// }
	}else{ ?>
		<h2 style="text-align:left;">Press 'Get Stats' to Display Results</h2>
	<?php	
	}
	?>	
	    
		

	</div>

	</div>

	<br class="clearfloat" />

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4>Report Admin</h4>
	<p>This report shows a breakdown of results for the given date period selected.</p>

	<p>
		<strong>Type Breakdown Query</strong></br>
		<pre>
<code>SELECT COUNT(j.id) AS num_jobs, `j`.`job_type`
FROM `jobs` AS `j`
LEFT JOIN `property` as `p` ON `j`.`property_id` = `p`.`property_id`
LEFT JOIN `agency` as `a` ON `p`.`agency_id` = `a`.`agency_id`
WHERE `j`.`date` >= '$date_from'
AND `j`.`date` <= '$date_to'
AND `p`.`deleted` = 0
AND `a`.`status` = 'active'
AND `j`.`del_job` = 0
AND `a`.`country_id` = <?php echo COUNTRY ?> 
AND `j`.`status` = 'Completed'
AND j.job_type IN('Yearly Maintenance', 'Change of Tenancy', 'Lease Renewal', '240v Rebook', 'Fix or Replace', 'Once-off', 'None Selected')
GROUP BY `j`.`job_type`</code></pre>
	</p>

	<p>
		<strong>Tech Completed Jobs Query</strong></br>
		<pre>
<code>SELECT COUNT(j.`id`) AS num_jobs, j.`assigned_tech`, sa.`StaffID`, sa.FirstName, sa.LastName 
FROM jobs AS j
LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
WHERE j.`date` BETWEEN '$date_from' AND '$date_to'
AND p.`deleted` =0
AND a.`status` = 'active'
AND j.`del_job` = 0
AND a.`country_id` = <?php echo COUNTRY ?> 
AND j.`status` = 'Completed'
AND j.`assigned_tech` IS NOT NULL
AND j.`assigned_tech` > 1
GROUP BY j.`assigned_tech`
ORDER BY sa.FirstName, sa.LastName </code></pre>
	</p>

	<p>
		<strong>Staff Booked Jobs Query</strong></br>
		<pre>
<code>SELECT COUNT(j.id) AS num_jobs, sa.`StaffID`, sa.FirstName, sa.LastName  
FROM jobs AS j
LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
LEFT JOIN `staff_accounts` AS sa ON j.`booked_by` = sa.`StaffID`
WHERE j.`date` >= '$date_from' 
AND j.`date` <= '$date_to'
AND p.`deleted` =0
AND a.`status` = 'active'
AND j.`del_job` = 0
AND a.`country_id` =  <?php echo COUNTRY ?> 
AND ( j.`booked_by` != 0 AND j.`booked_by` IS NOT NULL )
GROUP BY j.`booked_by`
ORDER BY sa.FirstName, sa.LastName</code>
		</pre>
	</p>


	

</div>
<!-- Fancybox END -->
