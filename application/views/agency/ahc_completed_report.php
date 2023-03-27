<?php
	//$job_type_arr = [];
/*	foreach( $job_types->result() as $job_type ){


		$job_type_arr[] = array(
			'full' => trim($job_type->job_type),
			'short' => trim($job_type->abbrv),
			'tot' => 0,
			'tot_age' => 0
		);
		

	}	*/

	//echo "<pre>";
	//print_r($job_type_arr);
	//echo "</pre>";

	if($this->config->item('country')==2){ // NZ REMOVED LR

		$job_type_arr = array(
			array(
				'full'=>'Yearly Maintenance',
				'short'=>'YM',
				'tot'=>0,
				'tot_age'=>0
			),
			array(
				'full'=>'Change of Tenancy',
				'short'=>'COT',
				'tot'=>0,
				'tot_age'=>0
			),
			array(
				'full'=>'Fix or Replace',
				'short'=>'FR',
				'tot'=>0,
				'tot_age'=>0
			),
			array(
				'full'=>'Once-off',
				'short'=>'ONCE OFF',
				'tot'=>0,
				'tot_age'=>0
			),
			array(
				'full'=>'Annual Visit',
				'short'=>'Annual',
				'tot'=>0,
				'tot_age'=>0
			)
		);

	}else{ //AU

		$job_type_arr = array(
			array(
				'full'=>'Yearly Maintenance',
				'short'=>'YM',
				'tot'=>0,
				'tot_age'=>0
			),
			array(
				'full'=>'Change of Tenancy',
				'short'=>'COT',
				'tot'=>0,
				'tot_age'=>0
			),
			array(
				'full'=>'Fix or Replace',
				'short'=>'FR',
				'tot'=>0,
				'tot_age'=>0
			),
				array(
				'full'=>'Lease Renewal',
				'short'=>'LR',
				'tot'=>0,
				'tot_age'=>0
			),
			array(
				'full'=>'Once-off',
				'short'=>'ONCE OFF',
				'tot'=>0,
				'tot_age'=>0
			),
			array(
				'full'=>'Annual Visit',
				'short'=>'Annual',
				'tot'=>0,
				'tot_age'=>0
			)
		);

		if( $ajt_id == 0){ //Add IC Upgrade 
			$ic_arr = 	array(
				'full'=>'IC Upgrade',
				'short'=>'IC Upgrade',
				'tot'=>0,
				'tot_age'=>0
			);
			array_push($job_type_arr, $ic_arr);
		}
	}

	?>

<div class="row">

    <div class="col-lg-12 columns">
	<?php
		$ra_job_type_count_2_total_params = array(
		'sel_query' => "COUNT(DISTINCT j.id ) AS num_jobs, j.job_type",
		'from_date' => $from,
		'to_date' => $to,
		'ajt_id' => $ajt_id,
		'job_type_change' => 1,
		'ahc_agency_id' => $agency_id
		);
		$rebook_total_without_min_max_filte_q =  $this->system_model->ra_job_type_count_2($ra_job_type_count_2_total_params)->row_array();  
		$tot_rebook =  $rebook_total_without_min_max_filte_q['num_jobs'];
		?>

		<?php
			$upfront_job_type_count_2_total_params = array(
			'sel_query' => "COUNT(DISTINCT j.id ) AS num_jobs, j.job_type",
			'from_date' => $from,
			'to_date' => $to,
			'ajt_id' => $ajt_id,
			'assigned_tech' => 2,
			'ahc_agency_id' => $agency_id
			);
			$upfront_total_without_min_max_filte_q =  $this->system_model->ra_job_type_count_2($upfront_job_type_count_2_total_params)->row_array();  
			$tot_upfront =  $upfront_total_without_min_max_filte_q['num_jobs'];
		?>

		<?php
			$eo_job_type_count_2_total_params = array(
			'sel_query' => "COUNT(DISTINCT j.id ) AS num_jobs, j.job_type",
			'from_date' => $from,
			'to_date' => $to,
			'ajt_id' => $ajt_id,
			'is_eo' => 1,
			'ahc_agency_id' => $agency_id
			);
			$eo_total_without_min_max_filte_q =  $this->system_model->ra_job_type_count_2($eo_job_type_count_2_total_params)->row_array();  
			//echo $this->db->last_query();
			$tot_eo =  $eo_total_without_min_max_filte_q['num_jobs'];
	?>
		<?php
		$green = '#e0fde0';
		$orange = '#ffedcc';
		$red = '#ffe5e5';
		$red2 = '#ffb2b2';
		$red3 = '#ff6666';
		$age=array(
			array("min"=>0,"max"=>3,'bg_color'=>$green),
			array("min"=>4,"max"=>7,'bg_color'=>$green),
			array("min"=>8,"max"=>14,'bg_color'=>$orange),
			array("min"=>15,"max"=>30,'bg_color'=>$orange),
			array("min"=>31,"max"=>60,'bg_color'=>$red),
			array("min"=>61,"max"=>90,'bg_color'=>$red),
			array("min"=>91,"max"=>120,'bg_color'=>$red),
			array("min"=>121,"max"=>150,'bg_color'=>$red2),
			array("min"=>151,"max"=>180,'bg_color'=>$red2),
			array("min"=>181,"max"=>181,'bg_color'=>$red3)
		);							
		foreach($age as $val){ 
			$tot_sm = 0;
		// job types
		foreach( $job_type_arr as $index=>$job_type ){ 						
		?>							
			<td class="text-center chops1">
				<?php 
					$jt_count = $this->jobs_model->daysToComplete($from,$to,$ajt_id,$job_type['full'],$val['min'],$val['max'],$this->config->item('country'),$agency_id); 
				?>
			</td>
		<?php
			$job_type_arr[$index]['tot'] += $jt_count;	
		}
	}
		?>	
	<?php
		// job types
		foreach( $job_type_arr as $index=>$job_type ){ 
		$yt_tot_age = 0;
		?>
			<td class="text-center">
				<?php
				$asql = $this->jobs_model->getCompletedCount($from,$to,$ajt_id,$job_type['full'],$this->config->item('country'),1,$agency_id);
				//echo $this->db->last_query();

				foreach($asql->result_array() as $a){
					$date1=date_create($a['jcreated']);
					$date2=date_create($a['date']);
					$diff=date_diff($date1,$date2);
					$yt_tot_age += $diff->format("%a");
				}
				?>
				<?php //echo $yt_tot_age; ?>
				<input type="hidden" value="<?php echo $job_type_arr[$index]['tot_age'] = $yt_tot_age; ?>" />
			</td>
		<?php	
		}
		?>	

		<?php 

			if( $ajt_id == 0){ // Add 240v Rebook Total Completed
			?>
				<td class="text-center" style="font-weight: bold">

					<?php
						$ra_job_type_count_2_total_age_params = array(
							'sel_query' => " DISTINCT(j.id), CAST( j.`created` AS DATE ) AS jcreated, j.`date`",
							// 'sel_query' => "CAST( j.`created` AS DATE ) AS jcreated, j.`date`",
							'from_date' => $from,
							'to_date' => $to,
							'ajt_id' => $ajt_id,
							'job_type_change' => 1,
							'ahc_agency_id' => $agency_id
						);
						$rebook_tot_age_q =  $this->system_model->ra_job_type_count_2($ra_job_type_count_2_total_age_params)->result_array();  
						foreach($rebook_tot_age_q as $b){
							$r_date1=date_create($b['jcreated']);
							$r_date2=date_create($b['date']);
							$r_diff=date_diff($r_date1,$r_date2);
							$rebook_tot_age += $r_diff->format("%a");

							
						}
					?>
					<input type="hidden" value="<?php echo $rebook_tot_age; ?>" />
				</td>
			<?php
			}
			?>
		
		<?php 

			if( $ajt_id == 0){ // Add EO jobs Total Completed
			?>
				<td class="text-center" style="font-weight: bold">

					<?php
						$eo_job_type_count_2_total_age_params = array(
							'sel_query' => "CAST( j.`created` AS DATE ) AS jcreated, j.`date`",
							'from_date' => $from,
							'to_date' => $to,
							'ajt_id' => $ajt_id,
							'is_eo' => 1,
							'ahc_agency_id' => $agency_id
						);
						$eo_jobs_tot_age_q =  $this->system_model->ra_job_type_count_2($eo_job_type_count_2_total_age_params)->result_array();  
						//echo $this->db->last_query();

						foreach($eo_jobs_tot_age_q as $b){
							$r_date1=date_create($b['jcreated']);
							$r_date2=date_create($b['date']);
							$r_diff=date_diff($r_date1,$r_date2);
							$eo_jobs_tot_age += $r_diff->format("%a");
						}
					?>
					<input type="hidden" value="<?php echo $eo_jobs_tot_age; ?>" />
				</td>
			<?php
			}
			?>

		<?php 

		if( $ajt_id == 0){ // Add Upfront jobs Total Completed
		?>
			<td class="text-center" style="font-weight: bold">

				<?php
					$up_job_type_count_2_total_age_params = array(
						'sel_query' => "CAST( j.`created` AS DATE ) AS jcreated, j.`date`",
						'from_date' => $from,
						'to_date' => $to,
						'ajt_id' => $ajt_id,
						'assigned_tech' => 2,
						'ahc_agency_id' => $agency_id
					);
					$up_jobs_tot_age_q =  $this->system_model->ra_job_type_count_2($up_job_type_count_2_total_age_params)->result_array();  
					//echo $this->db->last_query();

					foreach($up_jobs_tot_age_q as $b){
						$r_date1=date_create($b['jcreated']);
						$r_date2=date_create($b['date']);
						$r_diff=date_diff($r_date1,$r_date2);
						$up_jobs_tot_age += $r_diff->format("%a");
					}
				?>
				<input type="hidden" value="<?php echo $up_jobs_tot_age; ?>" />
			</td>
		<?php
		}
		?>
	
    <div style="text-align:left;" class="table_top_head">Average Days to Complete</div>
        <table class="table table-hover main-table table_border">
            <thead>
            <tr>
            <th>&nbsp;</th>
            <?php
			// job types
			foreach( $job_type_arr as $job_type ){ ?>
				<th class="text-center"><?php echo $job_type['short'] ?></th>
			<?php	
			}
			?>
			<?php if( $ajt_id == 0){
				echo "<th class='text-center'>240v Rebook</th>";
				echo "<th class='text-center'>EO</th>";
				echo "<th class='text-center'>Upfront Jobs</th>";
			 } ?>
            </tr>
            </thead>
            <tbody>
            <tr>	
			<?php $ctr = count($age); ?>
			<td class="f_col">Average Days</td>
			<?php
			// job types
			foreach( $job_type_arr as $job_type ){ ?>
				<td class="text-center">
					<?php 
					echo (!is_nan($job_type['tot_age']/$job_type['tot']))?round(($job_type['tot_age']/$job_type['tot'])):'0'; 
					?>
				</td>
			<?php	
			}
			?>
			<?php if( $ajt_id == 0){ ?>
				<td class="text-center">
					<?php echo (!is_nan($rebook_tot_age/$tot_rebook))?round(($rebook_tot_age/$tot_rebook)):'0'; ?>
				</td>
			<?php
			}
			?>

			<?php if( $ajt_id == 0){ ?>
				<td class="text-center">
					<?php echo (!is_nan($eo_jobs_tot_age/$tot_eo))?round(($eo_jobs_tot_age/$tot_eo)):'0'; ?>
				</td>
			<?php
			}
			?>

			<?php if( $ajt_id == 0){ ?>
				<td class="text-center">
					<?php echo (!is_nan($up_jobs_tot_age/$tot_upfront))?round(($up_jobs_tot_age/$tot_upfront)):'0'; ?>
				</td>
			<?php
			}
			?>

		</tr>
            </tbody>
        </table>
    </div>

	<div  class="col-lg-12 columns">
        <div style="text-align:left;" class="table_top_head">Days to Complete</div>

        <table class="table table-hover main-table table_border">
        <thead>
            <tr>
            <th>&nbsp;</th>
           <?php
           foreach($job_type_arr as $row){
               echo "<td class='text-center' style='font-weight: bold'> {$row['short']} </td>";
           }

		   if( $ajt_id == 0){ // Add 240v Rebook
			echo "<td class='text-center' style='font-weight: bold;'>240v Rebook</td>";
		   }
           ?>
		   
		   <th class="text-center">EO
		   <th class="text-center">Upfront Jobs
           <th class="text-center">Total
			<input type="hidden" value="<?php echo $tot = $this->jobs_model->getCompletedCount($from,$to,$ajt_id,'',$this->config->item('country'),'',$agency_id); ?>" />
		
		   <?php
		   
			?>

		</th>
			<th class="text-center">Total %</th>
            </tr>
            </thead>
            <tbody>
            <?php

            $green = '#e0fde0';
            $orange = '#ffedcc';
            $red = '#ffe5e5';
            $red2 = '#ffb2b2';
            $red3 = '#ff6666';
            $age=array(
                array("min"=>0,"max"=>3,'bg_color'=>$green),
                array("min"=>4,"max"=>7,'bg_color'=>$green),
                array("min"=>8,"max"=>14,'bg_color'=>$orange),
                array("min"=>15,"max"=>30,'bg_color'=>$orange),
                array("min"=>31,"max"=>60,'bg_color'=>$red),
                array("min"=>61,"max"=>90,'bg_color'=>$red),
                array("min"=>91,"max"=>120,'bg_color'=>$red),
                array("min"=>121,"max"=>150,'bg_color'=>$red2),
                array("min"=>151,"max"=>180,'bg_color'=>$red2),
                array("min"=>181,"max"=>181,'bg_color'=>$red3)
            );							

            $yt_tot = 0;
            $cot_tot = 0;
            $fr_tot = 0;
            $lr_tot = 0;
            $oo_tot = 0;
            $tot_sm_tot = 0;
            $grand_total = 0;
            $grand_tot_percent = 0;

            foreach($age as $val){ 
                $tot_sm = 0;
            
            ?>
                <tr style="background-color: <?php echo $val['bg_color']; ?>;">
                    <td class="f_col">
							<?php
							if($val['min']==$val['max']){
								echo "{$val['min']}+";
							}else{
								echo "{$val['min']}-{$val['max']}";
							}
							?>
					</td>

                            <?php
							// job types
							foreach( $job_type_arr as $index=>$job_type ){ 						
							?>							
								<td class="text-center chops1">
									<?php 
										echo $jt_count = $this->jobs_model->daysToComplete($from,$to,$ajt_id,$job_type['full'],$val['min'],$val['max'],$this->config->item('country'),$agency_id); 
										//echo "<br /><br />";
										//echo $this->db->last_query();
									?>
								</td>
							<?php
								$job_type_arr[$index]['tot'] += $jt_count;	
								$tot_sm += $jt_count;
							}
							?>	

							<?php 

							if( $ajt_id == 0){ // Add 240v Rebook
								$rebook_240v_count_params = array(
									'sel_query' => "COUNT(DISTINCT j.id ) AS num_jobs, j.job_type",
									'from_date' => $from,
									'to_date' => $to,
									'ajt_id' => $ajt_id,
									'job_type_change' => 1,
									'min' => $val['min'],
									'max' => $val['max'],
									'ahc_agency_id' => $agency_id
								);
								$ra_job_type_count_2_q = $this->system_model->ra_job_type_count_2($rebook_240v_count_params)->row_array();
								//echo $this->db->last_query();
							?>
								<td class="text-center chops">
									<?php 
										echo $ra_job_type_count_2_q['num_jobs'];
										$rebook_240v_tot_completed += $ra_job_type_count_2_q['num_jobs'];
								 	?>
								 </td>
							<?php
							}
							?>

							<?php 

							if( $ajt_id == 0){ // Add EO Jobs
								$eo_jobs_count_params = array(
									'sel_query' => "COUNT(DISTINCT j.id ) AS num_jobs, j.job_type",
									'from_date' => $from,
									'to_date' => $to,
									'ajt_id' => $ajt_id,
									'is_eo' => 1,
									'min' => $val['min'],
									'max' => $val['max'],
									'ahc_agency_id' => $agency_id
								);
								$eo_jobs__count_2_q = $this->system_model->ra_job_type_count_2($eo_jobs_count_params)->row_array();
								//echo $this->db->last_query();
								//exit();

							?>
								<td class="text-center chops">
									<?php 
										echo $eo_jobs__count_2_q['num_jobs'];
										$eo_jobs_tot_completed += $eo_jobs__count_2_q['num_jobs'];
									?>
								</td>
							<?php
							}
							?>

							<?php 

							if( $ajt_id == 0){ // Upfront Jobs
								$up_jobs_count_params = array(
									'sel_query' => "COUNT(DISTINCT j.id ) AS num_jobs, j.job_type",
									'from_date' => $from,
									'to_date' => $to,
									'ajt_id' => $ajt_id,
									'assigned_tech' => 2,
									'min' => $val['min'],
									'max' => $val['max'],
									'ahc_agency_id' => $agency_id
								);
								$up_jobs__count_2_q = $this->system_model->ra_job_type_count_2($up_jobs_count_params)->row_array();
								//echo $this->db->last_query();
								//exit();

							?>
								<td class="text-center chops">
									<?php 
										echo $up_jobs__count_2_q['num_jobs'];
										$up_jobs_tot_completed += $up_jobs__count_2_q['num_jobs'];
									?>
								</td>
							<?php
							}
							?>

                            <td class="text-center total">
							<?php 
							echo $tot_sm_merge =  $tot_sm;
							//echo $tot_sm_merge =  $tot_sm+$ra_job_type_count_2_q['num_jobs'];
							//echo $tot_sm;
							//$grand_total += $tot_sm_merge;
							//$tot2 = $tot+$tot_rebook;
							$grand_total += $tot_sm;
							$tot2 = $tot;
							?>
							</td>
							<td class="text-center chops">
								<?php 
								
									if( $tot2 > 0 ){
										$tot_percent = number_format((($tot_sm_merge/$tot2)*100), 2, '.', ''); 
									}else{
										$tot_percent = 0;
									}									
                                   	echo "{$tot_percent}%";
                                    //echo (is_nan($tot_percent))?$tot_percent:0.00 ."%";
									$grand_tot_percent += $tot_percent;
								?> 
							</td>	

                </tr>

            <?php } ?>
            <tr style="background-color:#DDDDDD">
						<td class="f_col"><strong>TOTAL COMPLETED</strong></td>		
						<?php
						// job types
						foreach( $job_type_arr as $index=>$job_type ){ 
						$yt_tot_age = 0;
						?>
							<td class="text-center">
								<!-- Divide by 2 cause cause in the Average Days to Complete loop about daysToComplete which is only in the Days to Complete. -->
								<strong><?php echo $job_type['tot']/2; ?></strong>
								<!--  -->
							</td>
						<?php	
						}
						?>	

						<?php 

							if( $ajt_id == 0){ // Add 240v Rebook Total Completed
							?>
								<td class="text-center" style="font-weight: bold">
									<?php echo $rebook_240v_tot_completed; ?>
								</td>
							<?php
							}
							?>
						
						<?php 

							if( $ajt_id == 0){ // Add EO jobs Total Completed
							?>
								<td class="text-center" style="font-weight: bold">
									<?php echo $eo_jobs_tot_completed; ?>
								</td>
							<?php
							}
							?>

						<?php 

						if( $ajt_id == 0){ // Add Upfront jobs Total Completed
						?>
							<td class="text-center" style="font-weight: bold">
								<?php echo $up_jobs_tot_completed; ?>
							</td>
						<?php
						}
						?>

						<td class="text-center grandtotal"><strong><?php echo $grand_total; ?></strong></td>
						<td class="text-center">
						<strong>
							<?php 
							if($grand_tot_percent > 100){
								$grand_tot_percent = 100;
							}
							echo $grand_tot_percent; 
							?> %
						</strong>
						</td>
					</tr>
            </tbody>
        </table>
    
    </div>
    

</div>