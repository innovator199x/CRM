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
        'link' => "/jobs/booked_report"
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
        echo form_open('/jobs/booked_report',$form_attr);
        ?>
            <div class="for-groupss row">
                <div class="col-lg-10 col-md-12 columns">
                    <div class="row">

                       <div class="col-mdd-3">
							<label for="service_select">Service</label>
							<select id="service_filter" name="service_filter" class="form-control field_g2">
								<option value="">ALL</option>
                                <?php
                                foreach($service_filter->result_array() as $row){
                                    $selected = ($this->input->get_post('service_filter')==$row['id'])?'selected':'';
                                ?>
                                    <option <?php echo $selected; ?> value="<?php echo $row['id'] ?>"><?php echo $row['type'] ?></option>
                                <?php
                                }
                                ?>
							</select>
						</div>

                         <div class="col-mdd-3">
                            <label >Tech</label>
                            <select id="tech_filter" name="tech_filter" class="form-control field_g2">
                                <option value="">ALL</option>
                                 <?php
                                 foreach($tech_list as $row){
                                    $selected = ($this->input->get_post('tech_filter')==$row['StaffID'])?'selected':'';
                                ?>
                                   <option <?php echo $selected; ?> value="<?php echo $row['StaffID']; ?>" <?php echo ($this->input->get_post('tech_filter')==$row['StaffID'])?'selected="selected"':''; ?>>
                                        <?php 
                                            echo $this->system_model->formatStaffName($row['FirstName'],$row['LastName']).( ( $row['is_electrician'] == 1 )?' [E]':null ); 
                                        ?>
                                    </option>
                                <?php
                                 }
                                 ?>
                            </select>
                        </div>

                        <div class="col-md-2">
							<label for="phrase_select">Phrase</label>
							<input placeholder="ALL" type="text" name="search_filter" class="form-control" value="<?php echo $this->input->get_post('search_filter'); ?>" />
						</div>
                      
                        <div class="col-md-1 columns">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <input class="btn" type="submit" name="btn_search" value="Search">
                        </div>
                        
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
							<th>Date</th>
							<th>Job Type</th>
							<th>Price</th>
							<th>Service</th>
                            <th>Address</th>
                            <th>Tech</th>
                            <th>DK</th>
                            <th>Reason</th>
                            <th>Job #</th>
						</tr>
					</thead>

					<tbody>
                    <?php
                    if($lists->num_rows()>0){
                    foreach($lists->result_array() as $row){ 
                        $row_color = '';
						$reason = '';
                        $hide_ck = 0;
                        
                        // if completed 
						if($row['ts_completed']==1){
							$hide_ck = 1;
							$row_color = 'green_mark';
							//$reason .= "240v Rebook <br />";
						}
						
						// MUST BE THE LAST - not completed due to = job reason
						if( $row['job_reason_id']>0 && $row['ts_completed']==0 ){
							$hide_ck = 0;
							$row_color = 'yello_mark';
							$reason .= "{$row['jr_name']} <br />";
						}
                    ?>
                        <tr class="<?php echo $row_color; ?>" >

                            <td>
                            <input type="hidden" class="hid_job_id" name="hid_job_id" value="<?php echo $row['jid'] ?>">
                            
                            <?php echo ($this->system_model->isDateNotEmpty($row['jdate']))?date('d/m/Y', strtotime($row['jdate'])):NULL ?></td>
                           
                            <td>
                                    <span class="240v_jt_lbl">
                                    <?php 
                                        if($row['job_type']=='240v Rebook'){ 
                                        ?>
                                            <a class="btn_240v" href="javascript:void(0);"><?php echo $this->gherxlib->getJobTypeAbbrv($row['job_type']); ?></a>
                                        <?php
                                        }else{
                                            echo $this->gherxlib->getJobTypeAbbrv($row['job_type']); 
                                        }									
                                    ?>
                                    </span>	
                                        
                                        <select class="vw-jb-sel 240v_change_jt form-control" style="display:none; width: 125px;">
                                            <option value="Once-off" <?php echo ($row['job_type']=='Once-off')?'selected="selected"':''; ?>>Once-off</option>
                                            <option value="Change of Tenancy" <?php echo ($row['job_type']=='Change of Tenancy')?'selected="selected"':''; ?>>Change of Tenancy</option>
                                            <option value="Yearly Maintenance" <?php echo ($row['job_type']=='Yearly Maintenance')?'selected="selected"':''; ?>>Yearly Maintenance</option>
                                            <option value="Fix or Replace" <?php echo ($row['job_type']=='Fix or Replace')?'selected="selected"':''; ?>>Fix or Replace</option>
                                            <option selected="selected" value="240v Rebook" <?php echo ($row['job_type']=='240v Rebook')?'selected="selected"':''; ?>>240v Rebook</option>
                                            <option value="Lease Renewal" <?php echo ($row['job_type']=='Lease Renewal')?'selected="selected"':''; ?>>Lease Renewal</option>
                                        </select>
                            </td>
                           
                            <td>$<?php 
                            //echo $row['job_price']; 
                            echo number_format($this->system_model->price_ex_gst($row['job_price']),2);
                            ?></td>
                           
                            <td>
                                        <img data-toggle="tooltip" title="<?php echo $row['ajt_type'] ?>" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($row['jservice']); ?>" />
                            </td>
                           
                            <td>
                            <?php
                            $full_address = "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}";
                            echo $this->gherxlib->crmLink('vpd',$row['prop_id'],$full_address);
                            ?>
                            </td>
                           
                            <td>
                                        <?php
                                        $techName = $this->system_model->formatStaffName($row['staff_fname'],$row['staff_lname']);
                                        echo $this->gherxlib->crmlink('run_sheet_admin',$row['assigned_tech'],$techName);
                                        ?>
                            </td>
                           
                            <td><?php echo (($row['dk']==1)?'DK':''); ?></td>
                            
                            <td><?php echo $reason; ?></td>
                           
                            <td>
                            <?php echo $this->gherxlib->crmlink('vjd',$row['jid'],$row['jid']) ?>
                            </td>

                        </tr>
                    <?php } ?>

                    <tr>
						<td colspan="2"></td>
						<td style="text-align: left;">$<?php echo ($this->jobs_model->bkd_getPriceTotal($date)+$this->jobs_model->bkd_alarmPriceTotal($date)); ?></td>
						<td colspan="6"></td>
					</tr>

                    <?php }else{
                        echo "<tr><td colspan='9'>No Data</td></tr>";

                    } ?>
					</tbody>

				</table>
			</div>

<div class="text-center" style="padding-bottom:20px;">

<a href="/jobs/booked_report?day=<?php echo date('d'); ?>&month=<?php echo date('m'); ?>&year=<?php echo date('Y'); ?>">Today ($<?php echo number_format($this->jobs_model->bkd_getPriceTotal($date)+$this->jobs_model->bkd_alarmPriceTotal($date)) ?>)</a> |
		<?php
		for($i=1;$i<=3;$i++){ 
		$day = date('d',strtotime("+{$i} days"));
		$month = date('m',strtotime("+{$i} days"));
		$year = date('Y',strtotime("+{$i} days"));
        $dynamic_date = date('Y-m-d',strtotime("+{$i} days"));
		?>
			<a href="/jobs/booked_report?day=<?php echo $day; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>"><?php echo date('M d',strtotime("+{$i} days")); ?> ($<?php echo number_format($this->jobs_model->bkd_getPriceTotal($dynamic_date)+$this->jobs_model->bkd_alarmPriceTotal($dynamic_date)) ?>)</a> <?php echo ($i<3)?'|':''; ?>
		<?php	
		}
		?>

</div>


 <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
        <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>

		
			

		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
    This page displays all jobs that are booked in the system
	</p>
    <p>Price are exclusive of GST.</p>
    <pre>
<code>SELECT `j`.`id` AS `jid`, `j`.`created` AS `jcreated`, `j`.`service` AS `jservice`, `jr`.`name` AS `jr_name`, `j`.`status` AS `jstatus`, `j`.`date` AS `jdate`, `j`.`ts_completed`, `j`.`job_reason_id`, `j`.`job_type`, `j`.`job_price`, `j`.`assigned_tech`, `j`.`door_knock` as `dk`, `p`.`property_id` as `prop_id`, `p`.`address_1` AS `p_address_1`, `p`.`address_2` AS `p_address_2`, `p`.`address_3` AS `p_address_3`, `p`.`state` AS `p_state`, `ajt`.`type` as `ajt_type`, `sa`.`FirstName` as `staff_fname`, `sa`.`LastName` as `staff_lname`
FROM `jobs` as `j`
LEFT JOIN `property` as `p` ON `p`.`property_id` = `j`.`property_id`
LEFT JOIN `alarm_job_type` as `ajt` ON `ajt`.`id` = `j`.`service`
LEFT JOIN `agency` as `a` ON `a`.`agency_id` = `p`.`agency_id`
LEFT JOIN `job_reason` as `jr` ON `jr`.`job_reason_id` = `j`.`job_reason_id`
LEFT JOIN `staff_accounts` as `sa` ON `sa`.`StaffID` = `j`.`assigned_tech`
WHERE `p`.`deleted` = 0
AND `a`.`status` = 'active'
AND `j`.`del_job` = 0
AND `a`.`country_id` = 1
AND `j`.`date` = '2021-08-10'
ORDER BY `j`.`date` ASC
LIMIT 50</code>
    </pre>

</div>
<!-- Fancybox END -->


<script>



     jQuery(document).ready(function(){


            $("a.inline_fancybox").fancybox({});



            // toggle 240v job type dropdown
            jQuery(".btn_240v").click(function(){
                
                jQuery(this).parents("tr:first").find(".240v_change_jt").toggle();
                
            });


            // update 240v job type
            jQuery(".240v_change_jt").change(function(){
                
                var job_id = jQuery(this).parents("tr:first").find(".hid_job_id").val();
                var job_type = jQuery(this).val();
                

                swal(
				{
					title: "",
					text: "Are you sure you want to update?",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-success",
					confirmButtonText: "Yes",
					cancelButtonText: "No, Cancel!",
					closeOnConfirm: false,
					closeOnCancel: true,
				},
				function(isConfirm){
					if(isConfirm){
						$('#load-screen').show(); //show loader
						swal.close();
								jQuery.ajax({
								type: "POST",
								url: "/jobs/ajax_update_job_type",
								dataType: 'json',
								data: {
                                    job_id: job_id,
                                    job_type: job_type
                                }
								}).done(function(data){
									
									if(data.status){
										$('#load-screen').hide(); //hide loader
										swal({
											title:"Success!",
											text: data.msg,
											type: "success",
											showCancelButton: false,
											confirmButtonText: "OK",
											closeOnConfirm: false,
										},function(isConfirm){
										   if(isConfirm){ 
											   swal.close();
											   location.reload();
											   }
										});
									}else{
									   swal.close();
									   location.reload();
									}
								});
							}
					
                    }
                );

                
            });

     });

</script>