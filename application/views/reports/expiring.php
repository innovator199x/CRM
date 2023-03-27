<style>
    .col-mdd-3{
        max-width:15.5%;
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
        'link' => "/reports/expiring"
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);
?>

	

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<table class="table table-hover main-table" style="font-size:12px;">
					<thead>
						<tr>
                            <th>Months</th>
                            
                            <?php
                                foreach($alarm_pwr->result_array() as $row){
                                    echo "<th>{$row['alarm_pwr']}</th>";
                                    echo "<th>{$row['alarm_pwr']} $</th>";

                                }
                            ?>

                            <th>TOTAL</th>
						</tr>
					</thead>

					<tbody>
                    <?php
					// months, loop for 11 months
					$num_months = ($_GET['num_months']!="")?$_GET['num_months']-1:0;
					for($i=0;$i<=$num_months;$i++){ ?>
					<tr class="body_tr jalign_left">
						<td><?php echo date("F Y",strtotime("+{$i} month")); ?></td>
						<?php					
						$a_sql = $alarm_pwr;
						$x = 0;
						$tot_mon = 0;
						foreach($a_sql->result_array() as $a){ 									
							$is_bat = ($a['alarm_pwr_id']==6)?1:0;
							?>
							<td><?php echo $ea = $this->reports_model->get_expiring_alarm($a['alarm_pwr_id'],date("Y-m-1",strtotime("+{$i} month")),$is_bat,$this->config->item('country')); ?></td>		
							<td data-alarmprice_ex="<?php echo $a['alarm_price_ex']; ?>" data-ea="<?php echo $ea?>">$<?php echo $ea2 = number_format(($a['alarm_price_ex']*$ea),2); ?></td>
							<?php
							$eae2_v2 = $ea*$a['alarm_price_ex']; //by gherx> new total snippet (don't use number_format)
							$tot[$x] += $ea;
							$tot2[$x] += $ea2;
							$tot2_v2[$x] += $eae2_v2; //gherx
							$tot_mon += $ea2;
							$tot_tt += $eae2_v2; //gherx
							$x++;
						}					
						?>
						<td data-tot_tt="<?php echo $tot_tt ?>">$<?php echo number_format($tot_tt,2); ?></td>
					</tr>
					<?php
					}
					?>	
                      
                       
                      <tr class="body_tr jalign_left" style="background-color:#DDDDDD">
						<td>
							<strong>TOTAL</strong>
						</td>
						<?php
						$tot_ae = 0;
						foreach($tot as $index=>$val){ 
						?>
						<td><?php echo $val; ?></td>
						<td>$<?php echo $tot_ae = number_format($tot2_v2[$index],2); ?></td>					
						<?php
						$tot_ae2 += $tot_ae;
						$tot_ae2_v2 += $tot2_v2[$index]; //gherx
						}					
						?>
						<td>$<?php echo number_format($tot_ae2_v2,2); ?></td>
					</tr>
					</tbody>

				</table>
			</div>

		<?php
			if($_GET['num_months']==""){ ?>
                <div class="text-center" style="margin-top:15px;">
				<a href="/reports/expiring?num_months=12">
					<button class="btn" id="btn_assign" type="button">Load 12 months</button>
				</a>	
                </div>
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
	<p>
	This page displays all alarms due to expire in the selected date range
	</p>
	<pre>
<code>SELECT count( a.`alarm_id` ) AS jcount
FROM `alarm` AS a
LEFT JOIN `jobs` AS j ON a.`job_id` = j.`id`
LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
LEFT JOIN `agency` AS agen ON agen.`agency_id` = p.`agency_id`
WHERE j.`status` = 'Completed'		
AND (j.`date` BETWEEN '{$last_year}-{$this_month}-01' AND '{$last_year}-{$this_month}-{$max_day}')
AND p.`deleted` = 0
AND agen.`status` = 'active'
AND j.`del_job` = 0
AND agen.`country_id` = 1
AND a.`expiry` = '2021'
AND j.`job_type` = 'Yearly Maintenance'
AND a.`alarm_power_id` = 1</code>
	</pre>

</div>
<!-- Fancybox END -->


<script>

     jQuery(document).ready(function(){

         $("a.inline_fancybox").fancybox({});

     });

</script>