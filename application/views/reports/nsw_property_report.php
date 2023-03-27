
<style>
.btn_agency_table_join{
    margin-top:8px;
}
.btn_show_job_details{
    margin-top:15px;
}
.atay_2{padding:0!important;margin:0!important;}
table.awo tr td{border:0px;}
</style>
<div class="box-typical box-typical-padding">

<?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => "/reports/no_retest_date"
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);
?>


<header class="box-typical-header">
    <div class="box-typical box-typical-padding">
        <div class="for-groupss row">
            <div class="col-md-8 columns"><a href="/reports/no_retest_date?joinAgencyTable=yes" class="btn btn_agency_table_join">Join Agency Table</a></div>
            
            <div class="col-md-4 columns">
                <div class="row">
                    <div class="col-md-6 columns">
                        <label>Show Without</label>
                        <select class="form-control" id="job_type_filter" name='job_type_filter'>
                            <option value="">Please Select</option>
                            <?php 
                                foreach($job_type->result_array() as $job_type_row){
                            ?>
                                     <option value="<?php echo $job_type_row['job_type'] ?>"><?php echo $job_type_row['job_type'] ?></option>
                            <?php
                                }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6 columns">
                        <div class="btn_show_job_details_box"><button type="button" class="btn btn_show_job_details">Show Job Details</button></div>
                    </div>
                </div>
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
                       <!-- <th>Deadline</th> -->
                        <th>Address</th>
                        <th style="width:200px;">Job Type</th>
                        <th style="width:150px;">Job Age</th>
                      <!--  <th>Job ID</th>
                        <th>Job Date</th> -->
                    </tr>
                </thead>

                <tbody>
                    <?php
                        foreach($lists->result_array() as $u){

                        // $now = time();
                        // $your_date = strtotime($u['test_date']);
                        // $datediff = $now - $your_date;

                        // $deadline = 365-(round($datediff / (60 * 60 * 24)));
                        // if ($deadline > 15) {
                        //     continue;
                        // }
                    ?>
                            <tr class="body_tr jalign_left aw_row" data-prowid="<?php echo $u['property_id'] ?>" <?php echo $bg_color; ?>>
								<td>
									<span class="txt_lbl">
                                        <?php 
                                        $prop_address = $u['p_address1']." ".$u['p_address2'].", ".$u['p_address3']." ".$u['p_state']." ".$u['p_postcode'];
                                        echo $this->gherxlib->crmLink('vpd',$u['property_id'],$prop_address);
                                        ?>
									</span>
                                </td>
                                <td class="atay_2" colspan="2"></td>
                               <!-- <td>
                                    <span class="txt_lbl">
                                        <?php 
                                        echo $this->gherxlib->crmLink('vjd',$u['id'],$u['id']);
                                        ?>
                                    </span>
                                </td>
                                <td><?=$u['status']?></td>
                                <td><?=$this->system_model->formatDate($u['job_date'],'d/m/Y')?></td> -->
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
<p>This page finds properties without a retest date, so we prevent missing compliance deadlines.<br/><br/>
</p>

<pre>
    <code>
<!-- SELECT DISTINCT(p.property_id) as property_id, `p`.`address_1` as `p_address1`, `p`.`address_2` as `p_address2`, `p`.`address_3` as `p_address3`, `p`.`state` as `p_state`, `p`.`postcode` as `p_postcode`
FROM `property` as `p`
LEFT JOIN `property_services` as `ps` ON `ps`.`property_id` = `p`.`property_id`
WHERE `p`.`deleted` = 0
AND `ps`.`service` = 1
AND `p`.`retest_date` IS NULL
AND (p.is_nlm IS NULL OR p.is_nlm = 0)
LIMIT 50 -->
<?php echo $sql_query; ?>
    </code>
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



        $('.btn_show_job_details').click(function(){

            var err="";
            var job_type = $('#job_type_filter').val();
            if(job_type==""){
                err +="Please select Job Type";
            }
            if(err!=""){
                swal('',err,'error');
                return false;
            }

            $('.aw_row').each(function(){
                var obj = $(this);   
                var prop_id = obj.attr('data-prowid');
                var job_type_filter = $('#job_type_filter');

                obj.find('.atay_2').load('/reports/ajax_get_last_completed_job',{prop_id:prop_id}, function(response, status, xhr){

                    var obj2 = $(this);
                    var row_stat = obj2.find('.awo').attr('data-jobtype');

                    if(  row_stat != job_type_filter.val() ){
                        obj2.parent('.aw_row').show();
                    }else{
                        obj2.parent('.aw_row').hide();
                    }

                });
            })
            
        });

})

</script>