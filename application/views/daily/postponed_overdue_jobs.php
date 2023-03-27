
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
        'link' => "/daily/postponed_overdue_jobs"
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
                    echo form_open('/daily/postponed_overdue_jobs', $form_attr);
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
                        <th class="check_all_td">
                            <div class="checkbox" style="margin:0;">
                                <input name="chk_all" type="checkbox" id="check-all">
                                <label for="check-all">&nbsp;</label>
                            </div>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                        foreach($lists->result_array() as $u){

                            $recent_job_sql = $this->daily_model->get_recent_created_job($u['property_id']);
                            $recent_job_sql_row = $recent_job_sql->row_array();

                    ?>
                            <tr class="body_tr jalign_left tbl_list_tr" <?php echo $bg_color; ?>>
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
                                <td>
                                    <div class="checkbox">
                                        <input class="chk_job" name="chk_job[]" type="checkbox" id="check-<?php echo $u["id"] ?>" data-jobid="<?php echo $u["id"]; ?>" data-propid="<?php echo $u['property_id'] ?>" value="<?php echo $u['id']; ?>">
                                        <label for="check-<?php echo $u["id"] ?>">&nbsp;</label>
                                    </div>
                                </td>
                        </tr>
                    <?php
                        }
                    ?>
                </tbody>

            </table>
            <div id="mbm_box" class="text-right">
                <div class="gbox_main">
                    <div class="gbox">
                        <input name="snooze_reason" class="form-control"  id="snooze_reason" type="text" placeholder="Snooze reason*" >
                    </div>
                    <div class="gbox">
                        <button id="snooze_btn" type="button" class="btn">Snooze</button>
                    </div>
                </div>
            </div>
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
<p>This page lists all NSW/ACT Jobs with TBB status that are 15 days or less until deadline.</p>

<pre>
<code>SELECT `p`.`property_id`, `p`.`address_1` AS `p_address1`, `p`.`address_2` AS `p_address2`, `p`.`address_3` AS `p_address3`, `p`.`state` AS `p_state`, `p`.`postcode` AS `p_postcode`, `p`.`retest_date`, `p`.`test_date`, `a`.`agency_id`, `a`.`agency_name`, `j`.`id` AS `id`, DATEDIFF(CURDATE(), p.`retest_date`) AS deadline
FROM `property` AS `p`
LEFT JOIN `agency` AS `a` ON p.`agency_id` = a.`agency_id`
LEFT JOIN `agency_user_accounts` AS `aua` ON p.`pm_id_new` = aua.`agency_user_account_id`
INNER JOIN `jobs` AS `j` ON p.`property_id` = j.`property_id`
INNER JOIN `property_services` AS `ps` ON ( p.`property_id` = ps.`property_id` AND ps.`service` = 1 AND ps.`alarm_job_type_id` != 6 )
WHERE `p`.`deleted` = 0
AND `a`.`status` = 'active'
AND `j`.`del_job` =0 AND `j`.`status` = 'To Be Booked' AND (`p`.`retest_date` != '' AND `p`.`retest_date` <= '$next_30_days' )
AND `a`.`franchise_groups_id` != 14
AND `p`.`retest_date` <= CURDATE() AND (CAST(p.postpone_due_job AS DATE) > '$today' AND `p`.`postpone_due_job` IS NOT NULL)
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


        $('#check-all').on('change',function(){
            var obj = $(this);
            var isChecked = obj.is(':checked');
            var divbutton = $('#mbm_box');
            if(isChecked){
                divbutton.show();
                $('.chk_job').prop('checked',true);
                $("tr.tbl_list_tr").addClass("yello_mark");
            }else{
                divbutton.hide();
                $('.chk_job').prop('checked',false);
                $("tr.tbl_list_tr").removeClass("yello_mark");
            }
        })

        $('.chk_job').on('change',function(){
            var obj = $(this);
            var isLength = $('.chk_job:checked').length;
            var divbutton = $('#mbm_box');
            if(obj.is(':checked')){
                divbutton.show();
                obj.parents('.tbl_list_tr').addClass('yello_mark');
            }else{
                obj.parents('.tbl_list_tr').removeClass('yello_mark');
                if(isLength<=0){
                    divbutton.hide();
                }
            }
        })

        jQuery("#snooze_btn").on('click',function(){

            var agay = [];
            var snooze_reason = $('#snooze_reason').val();

            jQuery(".chk_job:checked").each(function(){
                var obj_checkbox = $(this);
                var prop_id = obj_checkbox.attr('data-propid');
                var job_id = obj_checkbox.val();
                var json_data = {
                    prop_id: prop_id,
                    job_id: job_id
                }
                var json_str = JSON.stringify(json_data);
                agay.push(json_str);
            });
            
            //validation
            if(!snooze_reason.trim()){
                swal('','Snooze reason must not be empty.','error');
                return false;
            }

            $('#load-screen').show(); //show loader
            jQuery.ajax({
				type: "POST",
				url: "/daily/ajax_snooze",
				data: { 
                    agay: agay,
					snooze_reason: snooze_reason
				}
			}).done(function( ret ){
				$('#load-screen').hide(); //hide loader
				swal({
					title:"Success!",
					text: "Success",
					type: "success",
					showCancelButton: false,
					confirmButtonText: "OK",
					closeOnConfirm: false,
					showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
					timer: <?php echo $this->config->item('timer') ?>
				});
				setTimeout(function(){ window.location='/daily/overdue_nsw_jobs'; }, <?php echo $this->config->item('timer') ?>);
					
			});	

        })

})

</script>