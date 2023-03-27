<style type="text/css">
	.col-mdd-3{
		max-width: 20.1%;
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
        'link' => "/jobs/todays_jobs"
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
    echo form_open('/jobs/todays_jobs',$form_attr);
    ?>
        <div class="for-groupss row">
            <div class="col-lg-10 col-md-12 columns">

               
                      <div class="row">


                        <div class="col-mdd-3">
                            <label for="phrase_select">Phrase</label>
                            <input type="text" placeholder="ALL" name="search_filter" class="form-control"  value="<?php echo $this->input->get_post('search_filter'); ?>" />
                        </div>

                        <div class="col-md-1 columns">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <button type="submit" class="btn btn-inline">Search</button>
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
                    <th>Address</th>
				
                    <th>Job ID</th>
                
                    <th>Job Status</th>
                
                    <th>Booked Date</th>
                    
                    <th>Agency</th>
                    
                    <th>Start Date</th>
                    
                    <th>End Date</th>
                    
                    <th style="width:20%">Job Comments</th>
                    
                    <th style="width:20%">Property Comments</th>
                    
                    <th>Preferred Time</th>
                       

                    </tr>
                </thead>

                <tbody>
                <?php 
                    if($lists->num_rows()>0){
                        foreach($lists->result_array() as $row){

                        $row_color = '';
						if( $row['j_status'] == 'Booked' ){
							$row_color = 'grey_rgb_bg';
						}else if($row['j_status']== 'Completed'){
                            $row_color = 'green_mark';
                        }
                ?>

                    <tr <?php echo "class='{$row_color}'" ?>>
                        <td>
                        <?php
                        $f_address = "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}";
                        echo $this->gherxlib->crmlink('vpd',$row['prop_id'], $f_address);
                        ?>
                        </td>
                        <td>
                        <?php
                         echo $this->gherxlib->crmlink('vjd',$row['jid'], $row['jid']);
                        ?>
                        </td>
                        <td>
                        <?php
                        echo $row['j_status'];
                        ?>
                        </td>
                        <td>
                            <?php 
                                echo ($this->system_model->isDateNotEmpty($row['j_date']))?date("d/m/Y",strtotime($row['j_date'])):NULL;
                            ?>
                        </td>
                        <td>
                            <?php
                                 echo $this->gherxlib->crmlink('vad',$row['a_id'], $row['agency_name'],'',$row['priority']);
                            ?>
                        </td>
                        <td>
                        <?php 
                         echo ($this->system_model->isDateNotEmpty($row['start_date']))?date("d/m/Y",strtotime($row['start_date'])):NULL;
                        ?>
                        </td>
                        <td>
                        <?php 
                         echo ($this->system_model->isDateNotEmpty($row['due_date']))?date("d/m/Y",strtotime($row['due_date'])):NULL;
                        ?>
                        </td>
                        <td>
                        <?php echo $row['j_comments'] ?>
                        </td>
                        <td>
                        <?php echo $row['p_comments'] ?>
                        </td>
                        <td>  <?php echo $row['preferred_time'] ?></td>
                    </tr>

                <?php
                     }
                    }else{
                        echo "<tr><td colspan='10'>No Data</td></tr>";
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

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

<h4><?php echo $title; ?></h4>
<p>This page displays all jobs that are booked in the system for today.</p>
<pre>
<code>SELECT `j`.`id` AS `jid`, `j`.`status` AS `j_status`, `j`.`service` AS `j_service`, `j`.`date` AS `j_date`, `j`.`job_price` AS `j_price`, `j`.`job_type` AS `j_type`, `j`.`urgent_job`, `j`.`start_date`, `j`.`due_date`, `j`.`comments` as `j_comments`, `j`.`preferred_time`, `p`.`property_id` AS `prop_id`, `p`.`address_1` AS `p_address_1`, `p`.`address_2` AS `p_address_2`, `p`.`address_3` AS `p_address_3`, `p`.`state` AS `p_state`, `p`.`postcode` AS `p_postcode`, `p`.`comments` AS `p_comments`, `a`.`agency_id` AS `a_id`, `a`.`agency_name` AS `agency_name`, `a`.`postcode` AS `a_postcode`
FROM `jobs` AS `j`
LEFT JOIN `property` AS `p` ON j.`property_id` = p.`property_id`
LEFT JOIN `agency` AS `a` ON  p.`agency_id` = a.`agency_id`
LEFT JOIN `job_type` AS `jt` ON j.`job_type` = jt.`job_type`
LEFT JOIN `alarm_job_type` AS `ajt` ON j.`service` = ajt.`id`
WHERE `j`.`del_job` = 0
AND `p`.`deleted` = 0
AND `a`.`status` = 'active'
AND `a`.`country_id` = 1
AND `j`.`date` = $today
ORDER BY `j`.`date` DESC
 LIMIT 50</code>
                </pre>

</div>
<!-- Fancybox END -->