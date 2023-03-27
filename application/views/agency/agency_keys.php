
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
        'link' => "/agency/agency_keys"
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
        echo form_open('/agency/agency_keys',$form_attr);
        ?>
            <div class="for-groupss row">
                <div class="col-lg-10 col-md-12 columns">
                    <div class="row">

                        


                      <div class="col-mdd-3">
							<label for="date_select">Date from:</label>
							<input name="date_from_filter" placeholder="ALL" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $from ?>">
						</div>

						<div class="col-mdd-3">
							<label for="date_select">to:</label>
							<input name="date_to_filter" placeholder="ALL" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $to ?>">
                        </div>

                        <div class="col-mdd-3">
                            <label for="agency_select">Agency</label>
                            <select id="agency_filter" name="agency_filter" class="form-control field_g2">
                                <option value="">ALL</option>
                               <?php
                               foreach($agency_filter->result_array() as $row){
                                ?>
                                <option value="<?php echo $row['agency_id'] ?>"><?php echo $row['agency_name'] ?></option>
                                <?php
                               }
                               ?>
                            </select>
                        </div>

                        <div class="col-mdd-3">
                            <label for="agency_select">Tech</label>
                            <select id="tech_filter" name="tech_filter" class="form-control field_g2">
                                <option value="">ALL</option>
                                <?php
                              
                               foreach($tech_filter->result_array() as $row){
                                ?>
                                <option value="<?php echo $row['StaffID'] ?>"><?php echo $this->system_model->formatStaffName($row['FirstName'], $row['LastName']) ?></option>
                                <?php
                               }
                        
                               ?>
                            </select>
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
							<th>Address</th>
							<th>Service</th>
							<th>Tech</th>
							<th>Job #</th>
                            <th>Booked With</th>
                            <th>Agency</th>
                            <th>Phone</th>
						</tr>
					</thead>

					<tbody>
                    <?php 
                      if($lists->num_rows()>0){
                        foreach($lists->result_array() as $row){

                        if($row['ts_completed']==1){
                            $row_color = 'style="background-color:#dfffa5;"';
                        }
                    ?>
                            <tr <?php echo $row_color; ?>>
                                <td><?php echo date('d/m/Y', strtotime($row['jdate'])) ?></td>
                                <td>
                                    <?php 
                                    $full_address = "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}";
                                    echo $this->gherxlib->crmLink('vpd',$row['property_id'],$full_address);
                                    ?>
                                </td>
                                <td>
								    <img data-toggle="tooltip" title="<?php echo $row['ajt_type'] ?>" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($row['j_service']); ?>" />
							    </td>
                                <td>
                                <?php 
                                    echo $this->system_model->formatStaffName($row['FirstName'], $row['LastName']);
                                ?>
                                </td>
                                <td>
                                <?php
                                    echo $this->gherxlib->crmLink('vjd',$row['jid'],$row['jid']);
                                ?>
                                </td>
                                <td>
                                <?php echo $row['booked_with'] ?>
                                </td>
                                <td>
                                <?php
                                       echo $this->gherxlib->crmLink('vad',$row['agency_id'],$row['agency_name'],'',$row['priority']);
                                ?>
                                </td>
                                <td><?php echo $row['a_phone'] ?></td>   
                        </tr>
                    <?php
                        }
                    }else{
                        echo "<tr><td colspan='8'>No Data</td></tr>";
                    }
                    ?>
                        
					</tbody>

				</table>
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
    This page displays all key jobs booked for the selected date range
	</p>

    <pre>
<code>SELECT `j`.`id` as `jid`, `j`.`created` as `jcreated`, `j`.`service` AS `jservice`, `j`.`status` AS `jstatus`, `j`.`date` AS `jdate`, `j`.`booked_with`, `j`.`service` as `j_service`, `j`.`ts_completed`, `p`.`property_id`, `p`.`address_1` AS `p_address_1`, `p`.`address_2` AS `p_address_2`, `p`.`address_3` AS `p_address_3`, `p`.`state` AS `p_state`, `jr`.`name` AS `jr_name`, `a`.`phone` AS `a_phone`, `a`.`agency_name`, `a`.`agency_id`, `a`.`phone` as `a_phone`, `ajt`.`id` AS `ajt_id`, `ajt`.`type` AS `ajt_type`, `sa`.`FirstName`, `sa`.`LastName`
FROM `jobs` as `j`
LEFT JOIN `property` as `p` ON `p`.`property_id` = `j`.`property_id`
LEFT JOIN `alarm_job_type` as `ajt` ON `ajt`.`id` = `j`.`service`
LEFT JOIN `agency` as `a` ON `a`.`agency_id` = `p`.`agency_id`
LEFT JOIN `job_reason` as `jr` ON `jr`.`job_reason_id` = `j`.`job_reason_id`
LEFT JOIN `staff_accounts` as `sa` ON `sa`.`StaffID` = `j`.`assigned_tech`
WHERE `p`.`deleted` = 0
AND `a`.`status` = 'active'
AND `j`.`del_job` = 0
AND `a`.`phone_call_req` = 1
AND `a`.`key_allowed` = 1
AND `j`.`key_access_required` = 1
AND `a`.`country_id` = <?php echo COUNTRY ?> 
AND `j`.`date` BETWEEN '$date_from' AND '$date_to'
ORDER BY `j`.`created` DESC
LIMIT 50</code>
    </pre>

</div>
<!-- Fancybox END -->


<script type="text/javascript">

  

</script>