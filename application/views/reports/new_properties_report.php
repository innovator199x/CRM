<?php

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
			'link' => "/reports/new_properties_report"
		)
	);
	$bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    
    $export_links_params_arr = array(
        'date_from_filter' => $this->input->get_post('date_from_filter'),
        'date_to_filter' => $this->input->get_post('date_to_filter'),
        'state_filter' =>  $this->input->get_post('state_filter'),
        'agency_filter' =>  $this->input->get_post('agency_filter'),
        'get_sats' => $this->input->get_post('get_sats')
    );
    $export_link_params = "/reports/new_properties_report/?export=1&".http_build_query($export_links_params_arr);

	?>

	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">
			<?php
		$form_attr = array(
			'id' => 'jform'
		);
		echo form_open('/reports/new_properties_report',$form_attr);
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
							<label for="date_select">State</label>
                            <select name="state_filter" id="state_filter" class="form-control">
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

                        <div class="col-mdd-3">
                            <label for="date_select">Agency</label>
                            <select name="agency_filter" id="agency_filter" class="form-control">
                                <option value="">ALL</option> 
                                <?php 
                                    foreach($agency_filter->result_array() as $agency_filter_row){
                                        $agen_sel = ($this->input->get_post('agency_filter')==$agency_filter_row['agency_id']) ? 'selected' : NULL;
                                ?>
                                    <option <?php echo $agen_sel; ?> value="<?php echo $agency_filter_row['agency_id'] ?>"><?php echo $agency_filter_row['agency_name'] ?></option>
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
                <?php if($this->input->get_post('btnGetStats')){
                ?>
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
                <?php
                } ?>
                 
                
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


                    <table class="table table-hover main-table table_border">
                        <thead>
                            <tr>
                                <th>Agency</th>
                                <th>State</th>
                                <?php
                                $ajt_sql2 = $this->reports_model->getDynamicServices();
                                    foreach($ajt_sql2->result_array() as $ajt_sql2_row){
                                      
                                ?>

                                    <?php 
                                        if( $this->config->item('country')==2 ){ //IF NZ DISPLAY ONLY SMOKE ALARM SERVICES
                                            if($ajt_sql2_row['id']==2){
                                    ?>
                                              <th class="text-center"><img data=ajtid="<?php echo $ajt_sql2_row['id'] ?>" data-toggle="tooltip" title="<?php echo $ajt_sql2_row['type'] ?>" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($ajt_sql2_row['id']); ?>" /></th>
                               
                                    <?php
                                            }
                                        }else{ //AU display all services
                                    ?>
                                              <th class="text-center"><img data=ajtid="<?php echo $ajt_sql2_row['id'] ?>" data-toggle="tooltip" title="<?php echo $ajt_sql2_row['type'] ?>" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($ajt_sql2_row['id']); ?>" /></th>
                                    <?php
                                        } 
                                    ?>
                                  
                               
                               <?php
                                        
                                    }
                                ?>	
                                <th class="text-center">Total New</th>
                                <th class="text-center">Deleted</th>
                                <th class="text-center">Net</th>
                                <th class="text-center">By SATS</th>
                                <th class="text-center">By Agency</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php
                                foreach($get_activity->result_array() as $sr){
                            ?>
                                    <tr>
                                        <td>
                                            <?php echo $this->gherxlib->crmLink('vad',$sr['agency_id'],"{$sr['agency_name']}"); ?>
                                        </td>
                                        <td><?php echo $sr['state'] ?></td>


                                        <?php
                                            $gross_tot = 0;
                                            $i = 0;
            
                                            foreach($ajt_sql2->result_array() as $ajt2){

                                                $sa_params = array(
                                                    'sel_query' => " COUNT(ps.`property_services_id`) as serv_count ",
                                                    'agency_id' => $sr['agency_id'],
                                                    'alarm_job_type_id' => $ajt2['id'],
                                                    'country_id' => $this->config->item('country'),
                                                    'date_from_filter' =>  $from,
                                                    'date_to_filter' => $to,
                                                    'display_query' => 0
                                                );
                                                $sa = $this->reports_model->get_property_services($sa_params)->row()->serv_count;
                                                
                                                if( $this->config->item('country')==2){  //IF NZ DISPLAY ONLY SMOKE ALARM SERVICES
                                                    if($ajt2['id']==2){
                                                    echo "<td class='text-center'>";
                                                        if($sa>0){
                                                            echo $sa;
                                                        }
                                                     echo "</td>";
                                                    }

                                                } else{ //AU display all services

                                                    echo "<td class='text-center'>";
                                                        if($sa>0){
                                                            echo $sa;
                                                        }
                                                    echo "</td>";
                                                }
                                                
                                               
                                                    

                                                $gross_tot += $sa;
                                                $serv_tot[$i] += $sa;
                                                $i++;               
                                            }
                                        ?>

                                        
                                        <!-- new total -->
                                        <td class="text-center">
                                            <?php 
                                                if($gross_tot>0){
                                                    echo $gross_tot;
                                                }
                                            ?>
                                        </td>
                                        
                                        <!-- deleted -->
                                        <td class="text-center">
                                            <?php 
                                            $del_params = array(
                                                'sel_query' => " COUNT(ps.`property_services_id`) as del_serv_count ",
                                                'agency_id' => $sr['agency_id'],
                                                'date_from_filter' =>  $from,
                                                'date_to_filter' => $to
                                            );
                                            $deleted = $this->reports_model->get_deleted_services($del_params)->row()->del_serv_count;
                                           
                                            if($deleted>0){
                                                echo '<span class="text-red">'.$deleted.'</span>';
                                            }
                                            ?>
                                            
                                        </td>
                                        
                                        <!-- Net -->
                                        <td class="text-center">
                                        <?php 
                                            $net = ($gross_tot-$deleted); 
                                            echo ($net<0)?'<span style="color:red">'.$net.'</span>':$net;
                                        ?>
                                        </td>

                                        <!-- BY Sats -->
                                        <td class="text-center">
                                            <?php 
                                                $add_by_sats_params = array(
                                                    'sel_query' => " COUNT(ps.`property_services_id`) as added_sats_count ",
                                                    'agency_id' => $sr['agency_id'],
                                                    'date_from_filter' =>  $from,
                                                    'date_to_filter' => $to
                                                );
                                                $add_by_sats = $this->reports_model->getAddedBySats($add_by_sats_params)->row()->added_sats_count;
                                                
                                                if($add_by_sats>0){
                                                    echo $add_by_sats;
                                                }
                                            ?>
                                        </td>

                                        <!-- By Agency -->
                                        <td class="text-center">
                                          <?php 
                                                $add_by_agency_params = array(
                                                    'sel_query' => " COUNT(ps.`property_services_id`) as added_agency_count ",
                                                    'agency_id' => $sr['agency_id'],
                                                    'date_from_filter' =>  $from,
                                                    'date_to_filter' => $to
                                                );
                                                $add_by_agency = $this->reports_model->getAddedByAgency($add_by_agency_params)->row()->added_agency_count;
                                                if($add_by_agency>0){
                                                    echo $add_by_agency;
                                                }
                                            ?>
                                        </td>

                                        
                                    </tr>

                            <?php

                                //SET VARIABLES FOR TOTALS
                                $add_by_sats_tot += $add_by_sats;
                                $add_by_agency_tot += $add_by_agency;
                                $gross_tot_tot += $gross_tot;
                                $deleted_tot += $deleted;
                                $sats_del_tot += $sats_del;
                                $net_total_tot += $net;		
                                //SET VARIABLES FOR TOTALS END

                                } 
                            ?>
                            
                            <!-- TOTAL START HERE -->
                            <tr>
                            <td><strong>Total</strong></td>
                            <td>&nbsp;</td>


                            <!-- service count -->
                            <?php
                            $awts = 0;
                            foreach($serv_tot as $val){ ?>

                                <?php
                                if( $this->config->item('country')==2){
                                   
                                    if($awts == 0){
                                ?>
                                        <td class="text-center"><strong><?php echo ($val>0)?$val:''; ?></strong></td>
                                <?php
                                    }

                                }else{
                                    ?>
                                    <td class="text-center"><strong><?php echo ($val>0)?$val:''; ?></strong></td>
                                    <?php
                                }
                                ?>
                              
                            <?php
                            $awts++;
                            }
                            ?>

                            <td class="text-center"><strong><?php echo ($gross_tot_tot>0)?$gross_tot_tot:''; ?></strong></td>
                            <td class="text-center"><strong><?php echo ($deleted_tot>0)?$deleted_tot:''; ?></strong></td>
                            <td class="text-center"><strong><?php echo ($net_total_tot>0)?$net_total_tot:''; ?></strong></td>
                            
                            <td class="text-center"><strong><?php echo ($add_by_sats_tot>0)?$add_by_sats_tot:''; ?> (<?php echo ($add_by_sats_tot>0)?(is_numeric(number_format((($add_by_sats_tot/$gross_tot_tot)*100))))?number_format((($add_by_sats_tot/$gross_tot_tot)*100)).'%':'':''; ?>)</strong></td>
                            <td class="text-center"><strong><?php echo ($add_by_agency_tot>0)?$add_by_agency_tot:''; ?> (<?php echo ($add_by_sats_tot>0)?(is_numeric(number_format((($add_by_agency_tot/$gross_tot_tot)*100))))?number_format((($add_by_agency_tot/$gross_tot_tot)*100)).'%':'':''; ?>)</strong>
                            </td>

                            </tr>
                            <!-- TOTAL END HERE -->

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

	<h4>New Properties Report</h4>
	<p>This page shows all new properties added into the system based on the selected date range.</p>

    <?php
    $sales_commission_ver = ( $this->input->get_post('ver') != '' )?$this->input->get_post('ver'):$this->config->item('sales_commission_ver');
    ?>
    <pre>
        <code>
SELECT DISTINCT(a.`agency_id`), `a`.`agency_name`, `a`.`state`
FROM `property_services` as `ps`
LEFT JOIN `property` as `p` ON `p`.`property_id` = `ps`.`property_id`
LEFT JOIN `agency` as `a` ON `a`.`agency_id` = `p`.`agency_id`
WHERE `a`.`country_id` = 1
AND CAST(ps.`status_changed` AS DATE) >= $date_from
AND CAST(ps.`status_changed` AS DATE) <= $date_to
AND (`p`.`is_nlm` IS NULL OR `p`.`is_nlm` = 0 )
<?php
if( $sales_commission_ver == 'new' ){
    echo 'ps.`is_payable` = 1';
}else{
    echo 'ps.`service` = 1';
}
?>
        </code>
    </pre>

</div>
<!-- Fancybox END -->
