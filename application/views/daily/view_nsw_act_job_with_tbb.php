
<style>
.total_box{
    margin-top: 18px;
}
</style>
<div class="box-typical box-typical-padding">

<?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => "/daily/view_nsw_act_job_with_tbb"
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);
?>

 <header class="box-typical-header">
        <div class="box-typical box-typical-padding">

        <div class="for-groupss row">
            <div class="col-md-6 columns">
                <?php
                $form_attr = array(
                    'id' => 'nlm_reports',
                    'class' => ''
                );
                echo form_open('/daily/view_nsw_act_job_with_tbb', $form_attr);
                ?>
                
                
                        <div class="row">
                    
                            <div class="col-md-4 columns">
                                <label>Agency</label>
                                <select class="form-control" id="agency_filter" name="agency_filter">
                                    <option value="">All</option>
                                    <?php
                                    foreach ($agency_filter->result_array() as $row) {
                                        $agency_sel = ($this->input->get_post('agency_filter')==$row['agency_id']) ? 'selected="true"' :NULL;
                                    ?>
                                        <option <?php echo $agency_sel; ?> value="<?php echo $row['agency_id'] ?>"><?php echo $row['agency_name'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3 columns">
                                <label class="col-sm-12 form-control-label">&nbsp;</label>
                                <button type="submit" class="btn btn-inline" name="submitFilter">Search</button>
                            </div>

                        </div>

                <?php
                echo form_close();
                ?>
                </div>
               <!-- <div class="col-md-3 columns"> <div class="total_box text-right"><h5><?php echo $overdue_tot; ?> jobs are overdue</h5></div></div>
                <div class="col-md-3 columns"> <div class="total_box"><h5><?php echo $overdue_30days_tot; ?> jobs are due in less than 30 days</h5></div></div>
                                -->
               </div>

        </div>

    </header>

<section>
    <div class="body-typical-body">
        <div class="table-responsive">
            <table class="table table-hover main-table table-striped">
                <thead>
                    <tr>
                        <th>Deadline</th>
                        <th>Retest Date</th>
                        <th>Property Address</th>
                        <th>Agency</th>
                        <th>Active Job Status</th>
                        <th>Active Job Age</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                        foreach($lists->result_array() as $u){

                            $recent_job_sql = $this->daily_model->get_recent_created_job($u['property_id']);
                            $recent_job_sql_row = $recent_job_sql->row_array();

                            // $now = time();
                            // $your_date = strtotime($u['test_date']);
                            // $datediff = $now - $your_date;

                            // $deadline = 365-(round($datediff / (60 * 60 * 24)));
                            // if ($deadline > 15) {
                            //     continue;
                            // }
                    ?>
                            <tr class="body_tr jalign_left" <?php echo $bg_color; ?>>
                                <td>
                                    <?php
                                        // get deadline age
                                        $retest_date_ts = date_create(date('Y-m-d', strtotime($u['retest_date'])));
                                        $today_ts = date_create(date('Y-m-d'));
                                        $diff = date_diff($today_ts,$retest_date_ts);
                                        $age = $diff->format("%r%a");
                                        $age_val = (((int) $age) != 0) ? $age : 0; 
                                        
                                        echo ( $age_val >= 0 )?$age_val:"<span class='text-red'>{$age_val}</span>";
                                    ?>
                                </td>

                              <td>
                                <?php echo ($this->system_model->isDateNotEmpty($u['retest_date']) == true) ? $this->system_model->formatDate($u['retest_date'], 'd/m/Y') : NULL; ?>
                                </td>
                                
								<td>
									<span class="txt_lbl">
                                        <?php echo $this->gherxlib->crmLink('vpd',$u['property_id'],"{$u['p_address1']} {$u['p_address2']}, {$u['p_address3']} {$u['p_state']} {$u['p_postcode']}"); ?>
									</span>
                                </td>
                                
                                <td><?php echo $this->gherxlib->crmLink('vad',$u['agency_id'],$u['agency_name'],'',$u['priority']); ?></td>

                                <td>
                                    <span class="txt_lbl">
                                        <?php 
                                         echo $this->gherxlib->crmLink('vjd',$recent_job_sql_row['id'],$recent_job_sql_row['status']);
                                        ?>
                                    </span>
                                </td>

                                <td>
                                    <?php 
                                        $created =  $recent_job_sql_row['created'];
                                        $date1 = date_create(date('Y-m-d', strtotime($created)));
                                        $date2 = date_create(date('Y-m-d'));
                                        $diff = date_diff($date1, $date2);
                                        $age = $diff->format("%r%a");

                                        if($recent_job_sql->num_rows()>0){
                                            $age_val = (((int) $age) != 0) ? $age : 0;
                                        }else{
                                            $age_val = NULL;
                                        }

                                        echo $age_val;
                                    ?>
                                </td>

                              
                        </tr>
                    <?php
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
<p>This page lists all NSW/ACT Jobs with TBB status that are 15 days or less until deadline.

</p>
<pre>
<code>SELECT `p`.`property_id`, `p`.`address_1` AS `p_address1`, `p`.`address_2` AS `p_address2`, `p`.`address_3` AS `p_address3`, `p`.`state` AS `p_state`, `p`.`postcode` AS `p_postcode`, `p`.`retest_date`, `p`.`test_date`, `a`.`agency_id`, `a`.`agency_name`, `j`.`id` AS `id`, DATEDIFF(CURDATE(), p.`retest_date`) AS deadline
FROM `property` AS `p`
LEFT JOIN `agency` AS `a` ON p.`agency_id` = a.`agency_id`
LEFT JOIN `agency_user_accounts` AS `aua` ON p.`pm_id_new` = aua.`agency_user_account_id`
INNER JOIN `jobs` AS `j` ON p.`property_id` = j.`property_id`
INNER JOIN `property_services` AS `ps` ON ( p.`property_id` = ps.`property_id` AND ps.`service` = 1 AND ps.`alarm_job_type_id` != 6 )
WHERE `p`.`deleted` = 0
AND `a`.`status` = 'active'
AND `p`.`state` = 'NSW'
AND `j`.`status` = 'To Be Booked' AND (`p`.`retest_date` != '' AND `p`.`retest_date` <= '$$next_30_days' )
AND `a`.`franchise_groups_id` != 14
AND (`p`.`retest_date` > NOW() AND `p`.`retest_date` < DATE_ADD(NOW(), INTERVAL 30 DAY ))
GROUP BY `p`.`property_id`
ORDER BY `p`.`retest_date` ASC, `p`.`address_2` ASC
LIMIT 100</code>
</pre>

</div>
<!-- Fancybox END -->

<script type="text/javascript">

jQuery(document).ready(function(){

        // $('#btnMarkUnserviced').click(function(e){
        //     e.preventDefault();

        //     swal({
        //         html:true,
        //         title: "",
        //         text: "Are you sure you want to proceed?",
        //         type: "warning",
        //         showCancelButton: true,
        //         confirmButtonClass: "btn-success",
        //         confirmButtonText: "Yes",
        //         cancelButtonClass: "btn-danger",
        //         cancelButtonText: "No, Cancel!",
        //         closeOnConfirm: false,
        //         closeOnCancel: true,
        //     },
        //     function(isConfirm){
        //         if(isConfirm){

        //             $('#load-screen').show(); 
        //             swal.close();

        //             jQuery.ajax({
        //                 method: 'post',
        //                 processData: false,
        //                 contentType: false,
        //                 cache: false,
        //                 dataType: 'json',
        //                 data: { 
        //                         staff_id: <?php echo $this->session->staff_id ?>,
        //                     },
        //                 url: "/daily/unserviced_for_cron",
        //             }).done(function( crm_ret ){
        //                 if(crm_ret.status){
        //                     $('#load-screen').hide(); //hide loader
        //                     swal({
        //                         title:"Success!",
        //                         text: "Properties have been marked as unserviced.",
        //                         type: "success",
        //                         showCancelButton: false,
        //                         showConfirmButton: false,
        //                         confirmButtonText: "OK",
        //                         closeOnConfirm: false,
        //                         closeOnConfirm: false,
        //                         allowOutsideClick: false,
        //                         timer: 3000
        //                     },function(isConfirm){
        //                         swal.close();
        //                         location.reload();
        //                     });
        //                 }else{
        //                     swal('','All appropriate properties have already been marked as unserviced.','info');
        //                     $('#load-screen').hide(); //hide loader
        //                 }

        //             }); 
        //         }
        //     });
        // })

})

</script>