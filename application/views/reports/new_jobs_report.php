<?php


    $export_links_params_arr = array(
        'date_from_filter' => $this->input->get_post('date_from_filter'),
        'date_to_filter' => $this->input->get_post('date_to_filter'),
        'state_filter' =>  $this->input->get_post('state_filter'),
        'get_sats'=> 1
    );
    $export_link_params = "/jobs/export_new_jobs_report/?".http_build_query($export_links_params_arr);


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


<?php
	if($bntPost || $this->input->get_post('get_sats')==1){

		$export = [];
		/*$jt_arr = [];
		foreach($jt_arr_sql as $jt){ 
			$jt_arr[] = array('job_type'=> $jt['job_type']);
		}*/

		//echo $this->gherxlib->printa($sr_sql->result_array());

		if(!empty($sr_sql)){
		foreach($sr_sql->result_array() as $sr){
					
			$salesrep = '';
			
			// job types
			$jt_count = [];
			$jt_tot = 0;
			foreach( $jt_arr_sql as $job_type ){

				$sel_query = " COUNT(j.`id`) AS jcount ";
				$params = array(
					'sel_query' => $sel_query,
					'agency_id' => $sr['agency_id'],
					'job_type' => $job_type['job_type'],
					'from' => $from,
					'to' => $to,
					'state' => $this->input->get_post('state_filter')
				);
				$serv_ret = $this->jobs_model->get_num_services($params)->row()->jcount;


				$jt_count[] = ($serv_ret>0)?$serv_ret:'';
				$jt_tot += $serv_ret;
			}
			

			// total new
			$total_new = ($jt_tot>0)? $jt_tot:'';
			
			// total amount
			$tot_jp = $this->jobs_model->getJobPriceTotal_v2($sr['agency_id'],$from,$to); 
			$total_amount = ($tot_jp>0)?$tot_jp:0;
			
			// deleted
			$deleted = $this->jobs_model->get_deleted($sr['agency_id'],1,$from,$to)->num_rows();
			$deleted_tot = ($deleted>0)?$deleted:'';
			
			// net 
			$net = ($jt_tot-$deleted_tot); 
			
			// Added by Agency
			$add_by_agency = $this->jobs_model->getAddedByAgency($sr['agency_id'],$from,$to); 
			$added_by_agency = ($add_by_agency>0)?$add_by_agency:'';
			
			// added by SATS
			$add_by_sats = $this->jobs_model->getAddedBySats($sr['agency_id'],$from,$to); 
			$added_by_sats = ($add_by_sats>0)?$add_by_sats:'';
			
			// salesrep
			$salesrep_sql = $this->jobs_model->this_getAgencySalesRep($sr['salesrep']);
			$salesrep = $salesrep_sql->row_array();
			
			$export[] = array(
				'agency_id' => $sr['agency_id'],
				'agency' => $sr['agency_name'],
				'state' => $sr['state'],
				'job_type_count' => $jt_count,
				'total_new' => $total_new,
				'total_amount' => $total_amount,
				'deleted_tot' => $deleted_tot,
				'net' => $net,
				'added_by_agency' => $added_by_agency,
				'added_by_sats' => $added_by_sats,
				'salesrep' => "{$salesrep['FirstName']} {$salesrep['LastName']}",
				'priority' => $sr['priority']
			);
		}
	}


	}


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
			'link' => "/jobs/new_jobs_report"
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
		echo form_open('/jobs/new_jobs_report',$form_attr);
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
                            <label >State</label>
                            <select id="state_filter" name="state_filter" class="form-control">
								<option value="">ALL</option>
								<option value="NSW" <?php echo ($state=='NSW')?'selected="selected"':''; ?>>NSW</option>
								<option value="VIC" <?php echo ($state=='VIC')?'selected="selected"':''; ?>>VIC</option>
								<option value="QLD" <?php echo ($state=='QLD')?'selected="selected"':''; ?>>QLD</option>
								<option value="ACT" <?php echo ($state=='ACT')?'selected="selected"':''; ?>>ACT</option>
								<option value="TAS" <?php echo ($state=='TAS')?'selected="selected"':''; ?>>TAS</option>
								<option value="SA" <?php echo ($state=='SA')?'selected="selected"':''; ?>>SA</option>
								<option value="WA" <?php echo ($state=='WA')?'selected="selected"':''; ?>>WA</option>
								<option value="NT" <?php echo ($state=='NT')?'selected="selected"':''; ?>>NT</option>
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

                 <div class="col-lg-3 columns">
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
	if($bntPost || $this->input->get_post('get_sats')==1){
	
		
?>
    
	<section>
		<div class="body-typical-body">

            <div class="report_head">
                <h4>Report <?php echo $from; ?> to <?php echo $to; ?></h4>

                <?php
                # Alert that viewing Staff or Tech indivudal reports if neded, and offer to reset
                if(is_int($staff_id))
                {
                    echo "<div class='success'>Currently viewing statistics for staff member: " . $staff_details['FirstName'] . " " . $staff_details['LastName'] . " " . $report->generateLink(array('from' => $from, 'to' => $to, 'title' => 'back to all')) . "</div>";
                }
                if(is_int($tech_id))
                {
                    echo "<div class='success'>Currently viewing statistics for technician: " . $tech_details['FirstName'] . " " . $tech_details['last_name'] . " " . $report->generateLink(array('from' => $from, 'to' => $to, 'title' => 'back to all')) . "</div>";
                }
                ?>

                <p>This report shows totals that are SATS to service. These numbers do not include totals for properties that are marked DIY, No Response or Other Provider</p>
            </div>

			<div class="table-responsive">

                    <table class="table table-hover main-table table_border text-center">
                        <thead>
                            <tr>
								<th style="text-align: left;">Agency</th>
								<th>State</th>
								<?php
								// job type
								foreach( $jt_arr_sql as $jt_row ){				
								?>
									<th><?php 
									echo $this->gherxlib->getJobTypeAbbrv($jt_row['job_type']); 
									?></th>
								<?php
								}
								?>	
								<th>Total New</th>
								<th>Total $</th>
								<th>Deleted</th>
								<th>Net</th>
								<th>Added By Agency</th>
								<th>Added By SATS</th>		
                            </tr>
                        </thead>

                        <tbody>
                         
							<?php 

								$serv_tot_gt = array();
								$tot_jb_tot = 0;
								

								if(!empty($export)){
								foreach( $export as $exp_row ){
							?>
								<tr>
									<td style="text-align: left;">
									
									<?php echo $this->gherxlib->crmlink('vad',$exp_row['agency_id'],$exp_row['agency'],'',$exp_row['priority'] ) ?>
									
									</td>	
									<td><?php echo $exp_row['state']; ?></td>
									<?php
									//$jt_sql = jgetAllJobTypes();
									$gross_tot = 0;
									$i = 0;
									// job types
									foreach( $exp_row['job_type_count'] as  $jtc_count ){ ?>
										<td><?php echo $jtc_count; ?></td>
									<?php
										$serv_tot_gt[$i] += $jtc_count;
										$i++;
									}						
									?>
									<td><?php echo $exp_row['total_new']; ?></td>	
									<td><?php echo '$'.number_format($exp_row['total_amount'],2); ?></td>
									<td><?php echo $exp_row['deleted_tot']; ?></td>
									<td><?php echo $exp_row['net']; ?></td>
									<td><?php echo $exp_row['added_by_agency']; ?></td>
									<td><?php echo $exp_row['added_by_sats']; ?></td>
									<?php 								
										
									?>
								</tr>
							<?php	
								$total_new_gt += $exp_row['total_new'];
								$total_amount_gt += $exp_row['total_amount'];
								$deleted_tot_gt += $exp_row['deleted_tot'];
								$net_gt += $exp_row['net'];
								$added_by_agency_gt += $exp_row['added_by_agency'];
								$added_by_sats_gt += $exp_row['added_by_sats'];
								
							
							}
							?>	


                          <tr bgcolor="#f6f8fa">
							<td style="text-align: left;"><strong>TOTAL</strong></td>
							<td>&nbsp;</td>
							<?php
							foreach($serv_tot_gt as $val){ ?>
								<td><strong><?php echo ($val>0)?$val:''; ?></strong></td>
							<?php
							}
							?>
							<td><strong><?php echo ($total_new_gt>0)?$total_new_gt:''; ?></strong></td>
							<td><strong><?php echo '$'.number_format($total_amount_gt,2); ?></strong></td>
							<td><strong><?php echo ($deleted_tot_gt>0)?$deleted_tot_gt:''; ?></strong></td>
							<td><strong><?php echo ($net_gt>0)?$net_gt:''; ?></strong></td>	
							<td><strong><?php echo ($added_by_agency_gt>0)?$added_by_agency_gt:''; ?> <?php echo ($added_by_agency_gt>0)?'('.number_format((($added_by_agency_gt/$total_new_gt)*100)).'%)':''; ?></strong></td>
							<td><strong><?php echo ($added_by_sats_gt>0)?$added_by_sats_gt:''; ?> <?php echo ($added_by_sats_gt>0)?'('.number_format((($added_by_sats_gt/$total_new_gt)*100)).'%)':''; ?></strong></td>
						</tr>

						<?php
								}else{
									echo "<tr><td class='text-left' colspan='100%'>No Data</td></tr>";
								}
						?>



                        </tbody>

                    </table>

					
                
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

	<h4><?php echo $title; ?></h4>
	<p>This page displays all new jobs created in the system for the selected date range.</p>
	<p>Total are exclusive of GST.</p>
	<pre>
<code>SELECT DISTINCT(a.`agency_id`), `a`.`agency_name`, `a`.`salesrep`, `a`.`state`
FROM `jobs` as `j`
LEFT JOIN `property` as `p` ON `p`.`property_id` = `j`.`property_id`
LEFT JOIN `agency` as `a` ON `a`.`agency_id` = `p`.`agency_id`
WHERE `j`.`del_job` = 0
AND `a`.`status` = 'active'
AND `a`.`country_id` = 1
AND  CAST(j.`created` AS DATE) BETWEEN $from AND $to</code>
	</pre>

</div>
<!-- Fancybox END -->