
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
        'link' => "/reports/cron_report"
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
        echo form_open('/reports/cron_report',$form_attr);
        ?>
            <div class="for-groupss row">
                <div class="col-lg-10 col-md-12 columns">
                    <div class="row">

                        <div class="col-mdd-3">
                            <label for="a">Cron Type</label>
                            <select id="cron_type_filter" name="cron_type_filter" class="form-control field_g2">
                                <option value="">ALL</option>
                                <?php 
                                    foreach($cron_type->result_array() as $row){
                                        $sel = ($this->input->get_post('cron_type_filter')==$row['cron_type_id'])?'selected="true"':NULL;
                                ?>
                                        <option <?php echo $sel ?> value="<?php echo $row['cron_type_id'] ?>"><?php echo $row['type_name'] ?></option>
                                <?php
                                    }
                                ?>
                            </select>
                        </div>

                        <div class="col-mdd-3">
                                <label for="date_select">Date from:</label>
                                <input name="date_from_filter" placeholder="ALL" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $from ?>">
                        </div>

                        <div class="col-mdd-3">
                            <label for="date_select">to:</label>
                            <input name="date_to_filter" placeholder="ALL" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $to ?>">
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
							<th>Name</th>
							<th>Description</th>
							<th>Day</th>
							<th>Date</th>
							<th>Time</th>
                            <th>Triggered By</th>
						</tr>
					</thead>

					<tbody>
                        <?php
                            if($lists->num_rows() > 0){
                            foreach($lists->result_array() as $row){
                        ?>
                            <tr>
                               <td><?php echo $row['type_name'] ?></td>
                               <td><?php echo $row['description'] ?></td>
                               <td><?php echo date("l",strtotime($row['started'])); ?></td>
                               <td><?php echo date("d/m/Y",strtotime($row['started'])); ?></td>
                               <td>
                                <?php 
                                    switch($this->config->item('country')){
                                        case 1:
                                            $local_time = "AEST";
                                            $plus2h = "";
                                        break;
                                        case 2:
                                            $local_time = "NZST";
                                            $plus2h = " +2 hours";
                                        break;
                                    }	
                                    echo date("H:i",strtotime($row['started'].$plus2h))." ".$local_time; 
                                    ?>
                               </td>
                               <td>
                               <?php 
                               if( $row['triggered_by'] == -1 ){
                                   $triggered_by = 'CRON';
                               }else{
                                    $triggered_by = $this->system_model->formatStaffName($row['FirstName'],$row['LastName']);
                               }
                               echo $triggered_by;
                               ?>
                               </td>
                             </tr>
                        <?php   
                            }
                        }else{
                            echo "<tr><td colspan='6'>No Data</td></tr>";
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
    This page shows all Crons (Automated scripts) that have been run during the selected time period.
	</p>
    <pre>
<code>SELECT `cl`.`log_id`, `cl`.`started`, `cl`.`triggered_by`, `ct`.`cron_type_id`, `ct`.`type_name`, `ct`.`description`, `sa`.`StaffID`, `sa`.`FirstName`, `sa`.`LastName`
FROM `cron_log` AS `cl`
LEFT JOIN `cron_types` as `ct` ON `ct`.`cron_type_id` = `cl`.`type_id`
LEFT JOIN `staff_accounts` AS `sa` ON cl.`triggered_by` = sa.`StaffID`
WHERE `cl`.`country_id` = 1
AND `ct`.`active` = 1
AND CAST( cl.`started` AS Date ) BETWEEN '$date_from' AND '$date_to' 
ORDER BY `cl`.`started` DESC
LIMIT 50</code>
    </pre>

</div>
<!-- Fancybox END -->


<script type="text/javascript">

  

</script>