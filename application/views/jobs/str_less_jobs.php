
<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/daily/str_less_jobs"
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
							<th>Date</th>
							<th>Job Type</th>
							<th>Tech</th>
							<th>Address</th>
							<th>Agency</th>							
							<th>Job #</th>
						</tr>
					</thead>

					<tbody>
                        
					<?php 
					$counter = 0;
                    if($lists->num_rows()>0){
                        foreach($lists->result_array() as $row){

                            if( !$this->daily_model->findJobsOnSTR($row['jid']) ){

                    ?>
                        <tr class="body_tr jalign_left">
							<td><?php echo ( $row['jdate']!='' && $row['jdate']!='0000-00-00' )?date("d/m/Y",strtotime($row['jdate'])):''; ?></td>
							<td><?php echo $this->gherxlib->getJobTypeAbbrv($row['job_type']); ?></td>
							<td>
								<?php echo "{$row['FirstName']} {$row['LastName']}"; ?>
							</td>
							<td>
                                <?php
                                    $p_full_address = "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}";
                                    echo $this->gherxlib->crmLink('vpd',$row['property_id'],$p_full_address);
                                ?>
                            </td>
							<td>
                                <?php  echo $this->gherxlib->crmLink('vad',$row['agency_id'],$row['agency_name'],'',$row['priority']); ?>
							</td>
							<td>
                                <?php  echo $this->gherxlib->crmLink('vjd',$row['jid'],$row['jid']); ?>
                            </td>							
						</tr>
					<?php
						 $counter++;
						} 
					}
                    }else{
                        echo "<tr><td colspan='6'>No Data</td></tr>";
					}
					

					$total_rows = $counter;

					// update page total
					$page_tot_params = array(
						'page' => $page_url,
						'total' => $total_rows
					);
					$this->system_model->update_page_total($page_tot_params);
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
	<p>This page shows all jobs not allocated to a tech run.</p>
<pre>
<code>SELECT `j`.`id` AS `jid`, `j`.`created` AS `jcreated`, `j`.`date` AS `jdate`, `j`.`service` AS `jservice`, `j`.`job_type`, `p`.`property_id`, `p`.`address_1` AS `p_address_1`, `p`.`address_2` AS `p_address_2`, `p`.`address_3` AS `p_address_3`, `p`.`state` AS `p_state`, `p`.`postcode` AS `p_postcode`, `a`.`agency_id`, `a`.`agency_name`, `sa`.`FirstName`, `sa`.`LastName`
FROM `jobs` as `j`
LEFT JOIN `property` as `p` ON `p`.`property_id` = `j`.`property_id`
LEFT JOIN `agency` as `a` ON `a`.`agency_id` = `p`.`agency_id`
LEFT JOIN `staff_accounts` as `sa` ON `sa`.`StaffID` = `j`.`assigned_tech`
WHERE `j`.`status` = 'Booked'
AND `j`.`date` = '$next_2_days'</code>
</pre>
</div>
<!-- Fancybox END -->

<script type="text/javascript">

	jQuery(document).ready(function(){

	

	})

</script>