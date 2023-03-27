<div class="box-typical box-typical-padding">

    <?php
    // breadcrumbs template
    $bc_items = array(
        array(
            'title' => "CRM Complaints",        
            'link' => "/reports/view_complaints"
        ),
        array(
			'title' => 'Details',
			'status' => 'active',
			'link' => $uri
		)
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

    <section>
        <div class="body-typical-body">                   

            <div class="row">
                <div class="col-md-7">
                    <form action="/reports/complaints_response/" enctype="multipart/form-data"  method="post">
                        <table class="table">  
                            <tr>
                                <td>Issue Summary:</td>
                                <td><input type='text' class="addinput form-control" name="issue_summary" id="issue_summary" value="<?php echo $complaints_row->issue_summary; ?>" /></td>
                            </tr>                          
                            <tr>
                                <td>Complaints Topic:</td>
                                <td>                                    
                                     <select id="comp_topic" name="comp_topic"  class="form-control field_g2" required>
                                        <option value="">---</option>
                                        <?php
                                        foreach( $complaints_topic_sql->result() as $complaints_topic_row ){ ?>
                                            <option value="<?php echo $complaints_topic_row->comp_topic_id; ?>" <?php echo ( $complaints_topic_row->comp_topic_id == $complaints_row->comp_topic )?'selected':null; ?>><?php echo $complaints_topic_row->comp_topic; ?></option>
                                        <?php
                                        }
                                        ?>
                                        <option value="-1" <?php echo ( $complaints_row->comp_topic == -1 )?'selected':null; ?>>Other</option>
                                    </select>
                                </td>
                            </tr>
                            <!-- <tr>
                                <td>Ticket Priority:</td>
                                <td>
                                    <select id="ticket_priority" name="ticket_priority"  class="form-control field_g2" required>
                                        <option value="">---</option>
                                        <option value="1" <?php echo ( $complaints_row->ticket_priority == 1 )?'selected':null; ?>>Low</option>
                                        <option value="2" <?php echo ( $complaints_row->ticket_priority == 2 )?'selected':null; ?>>Medium</option>
                                        <option value="3" <?php echo ( $complaints_row->ticket_priority == 3 )?'selected':null; ?>>High</option>
                                    </select>
                                </td>
                            </tr> -->
                            <tr>
                                <td>Complaints ID:</td>
                                <td><?php echo $complaints_row->comp_id; ?></td>
                            </tr>
                            <tr>
                                <td>Issue Details:</td>
                                <td>                                    
                                    <!--<textarea class="addtextarea form-control" name="describe_issue" id="describe_issue" style="height: 150px;"><?php // echo $complaints_row->describe_issue; ?></textarea>-->
                                    <?php echo nl2br($complaints_row->describe_issue); ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Created On:</td>
                                <td><?php echo ( $this->system_model->isDateNotEmpty($complaints_row->date_created) )?date('d/m/Y H:i',strtotime($complaints_row->date_created)):null; ?></td>
                            </tr>
                            <tr>
                                <td>Created By:</td>
                                <td>
                                    <?php      
                                    if( $complaints_row->requested_by > 0 ){

                                        $staff = $this->gherxlib->getStaffInfo(['staff_id' => $complaints_row->requested_by])->row_array();
                                        echo $logged_user_fullname = $this->system_model->formatStaffName($staff['FirstName'], $staff['LastName']);

                                    }                                                                 
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Last Updated On:</td>
                                <td><?php echo ( $this->system_model->isDateNotEmpty($complaints_row->last_updated_ts) )?date('d/m/Y H:i',strtotime($complaints_row->last_updated_ts)):null; ?></td>
                            </tr>
                            <tr>
                                <td>Last Updated By:</td>
                                <td>
                                    <?php      
                                    if( $complaints_row->last_updated_by > 0 ){

                                        $staff = $this->gherxlib->getStaffInfo(['staff_id' => $complaints_row->last_updated_by])->row_array();
                                        echo $logged_user_fullname = $this->system_model->formatStaffName($staff['FirstName'], $staff['LastName']);

                                    }                                                                  
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Screenshot</td>
                                <td>
                                    <div class="d-flex flex-row">
                                        <?php
                                        // copied from listing page
                                        if ( $complaints_row->screenshot != '' ) {
                                            $screenshotString = $complaints_row->screenshot;
                                            $screenshotArray = explode(',', $screenshotString);

                                            foreach($screenshotArray as $screenshot){
                                                

                                                    ?>
                                                        <a href="/<?php echo str_replace(' ', '_', $screenshot); ?>" class="m-2 fancybox-uploaded-screenshot d-flex " style="overflow:hidden; max-width:32px;" >
                                                            <!-- <i class="fa fa-camera" aria-hidden="true" style="font-size: 32px;"></i> -->

                                                        <div class="border" style="
                                                        background-image:   url('/<?php echo str_replace(' ', '_', $screenshot); ?>');
                                                        border-radius:      1em;
                                                        height:             32px;
                                                        -moz-border-radius: 1em;
                                                        width:              32px;
                                                        background-size:cover;"
                                                        ></div>
                                                        </a>

                                                    <?php
            
                                                }

                                            }
                                            ?>
                                            
                                    </div> 
                                </td>
                            </tr>
                            <tr>
                                <td>Status</td>
                                <td>
                                    <select name="status" class="form-control">
                                        <option value="">---</option>
                                        <?php
                                        foreach( $complaints_status_sql->result() as $complaints_status_row ){ ?>
                                            <option value="<?php echo $complaints_status_row->id; ?>" <?php echo ( $complaints_status_row->id == $complaints_row->status )?'selected':null; ?>><?php echo $complaints_status_row->status; ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Assigned Manager</td>
                                <td>                                    
                                    <input type="text" id="search_managers" class="form-control" />
                                    <div id="managers_suggestion_div"></div>
                                    <div id="subscribed_devs_div" class="mt-2">
                                        <?php                                        
                                       foreach( $managers_sql->result() as $managers_row ){ 

                                           $managers_full_name = $this->system_model->formatStaffName($managers_row->FirstName, $managers_row->LastName);
                                        ?>
                                            <!-- <label type="button" class="label label-info subscribe-btn btn-inline subscribed_dev"  data-subcribed_dev_id="<?php echo $managers_row->StaffID ?>"><?php echo $managers_full_name; ?></label>   -->
                                            <label type="button" class="label label-info subscribe-btn btn-inline subscribed_dev remove_dev_btn"  data-subcribed_dev_id="<?php echo $managers_row->StaffID ?>"><?php echo $managers_full_name; ?> <i class="fa fa-close"></i></label>  
                                        <?php
                                        }                                        
                                        ?>                                              
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Assigned Agency</td>
                                <td>                                    
                                    <!-- <input type="text" id="search_managers" class="form-control" />
                                    <div id="managers_suggestion_div"></div> -->
                                    <div id="subscribed_devs_div" class="mt-2">
                                        <?php                                        
                                       foreach( $agency_sql->result() as $agency_row ){ 

                                        //    $managers_full_name = $this->system_model->formatStaffName($managers_row->FirstName, $managers_row->LastName);
                                        ?>
                                            <!-- <label type="button" class="label label-info subscribe-btn btn-inline subscribed_dev"  data-subcribed_dev_id="<?php echo $managers_row->StaffID ?>"><?php echo $managers_full_name; ?></label>   -->
                                            <label type="button" class="label label-info subscribe-btn btn-inline subscribed_dev"  data-subcribed_dev_id="<?php echo $agency_row->id ?>">
                                            <a href="/agency/view_agency_details/<?php echo $agency_row->agency_id; ?>" target="_blank" style="color: white;">
                                                <?php echo $agency_row->agency_name; ?>
                                            </a>
                                            </label>  
                                        <?php
                                        }                                        
                                        ?>                                              
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Response</td>
                                <td>
                                    <textarea class="addtextarea form-control" name="response" id="response"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td>Add Files/Screenshot</td>
                                <td class="">
                                    
                                     <div class="text-sm-left mt-2 mb-2" role="alert">
                                    <small>Hold down the <b>Ctrl</b> key and click any other file(s) to select multiple files. </small>
                                    </div>
                                    <div class="d-flex mb-3 flex-row d-flex align-items-center">
                                    
                                    <input type='file' id="screenshot" class=" screenshot form-control" name="screenshot[]" multiple="" hidden />
                                    <label class="btn btn-sm btn-success pr-2 " for="screenshot">
                                    <span class="d-flex align-items-center">
                                        
                                        Choose a file
                                            <svg xmlns="http://www.w3.org/2000/svg" height="15px" width="15px" class="h-6 w-6 text-light mx-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                            </svg>
                                        </span>
                                    </label>
                                    <label for="screenshot" class="text-md text-muted mx-2 align-items-center" id="fileCounter"></label>
                                    </div>

                                    
                                </td>
                            </tr>     
                            <tr>
                                <td></td>
                                <td>
                                    <input type="hidden" name="comp_id" value="<?php echo $complaints_row->comp_id; ?>" />
                                    <button type="submit" class="btn float-right" id="btn_submit">Submit</button>
                                </td>
                            </tr>             
                        </table>
                    </form>
                </div>
                <div class="col-md-5">

                                    
                    

                    

                    <div class="row mt-3">

                        <div class="col">
                            <h5>Activity:</h5> 
                        
                            <div class="scrollit">               
                                <table class="table">
                                    <tr>
                                        <th>Who</th>
                                        <th>Activity</th>
                                        <th>Timestamp</th>
                                    </tr>
                                    <?php
                                    // get crm task logs
                                    foreach( $complaints_log_sql->result() as $complaints_log_row ){ ?>
                                    <tr>
                                        <td>
                                            <?php 
                                            $staff = $this->gherxlib->getStaffInfo(['staff_id' => $complaints_log_row->created_by])->row_array();
                                            echo $logged_user_fullname = $this->system_model->formatStaffName($staff['FirstName'], $staff['LastName']); 
                                            ?>
                                        </td>
                                        <td><?php echo nl2br($complaints_log_row->log_text); ?></td>
                                        <td><?php echo ( $this->system_model->isDateNotEmpty($complaints_log_row->created) )?date('d/m/Y H:i',strtotime($complaints_log_row->created)):null; ?></td>
                                    </tr>
                                    <?php
                                    }
                                    ?>                         
                                </table> 
                            </div>
                        </div>

                    </div>
                    
                   
    
            </div>            

        </div>
    </section>      
</div>
<style>
.scrollit{
    overflow:scroll;
    height:800px;
}    
.subscribe-btn,
#managers_ul li,
#devs_ul li{
    cursor: pointer;
}
</style>
<script>
jQuery(document).ready(function(){

      //  screenshot file counter putting it on label 
      $("#screenshot").on("change", function(){  
            var numFiles = $(this)[0].files.length;
            $('#fileCounter').html('<b>'+numFiles+'</b> file(s) selected.');
        });


    <?php if ($this->session->flashdata('status') && $this->session->flashdata('status') == 'success') { ?>
        swal({
            title: "Success!",
            text: "<?php echo $this->session->flashdata('success_msg') ?>",
            type: "success",
            confirmButtonClass: "btn-success",
            showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
            timer: <?php echo $this->config->item('timer') ?>
        });
    <?php } else if ($this->session->flashdata('status') && $this->session->flashdata('status') == 'error') { ?>
        swal({
            title: "Error!",
            text: "<?php echo $this->session->flashdata('error_msg') ?>",
            type: "error",
            confirmButtonClass: "btn-danger"
        });
    <?php } ?>

    jQuery('.fancybox-uploaded-screenshot').fancybox();

    // suggest managers
    // jQuery("#search_managers").keyup(function(){

    //     var search_managers_dom = jQuery(this);
    //     var search_managers = search_managers_dom.val();
    //     var ticket_id = <?php echo $complaints_row->comp_id; ?>

    //     if( search_managers != '' ){

    //         jQuery('#load-screen').show(); 
    //         jQuery.ajax({
    //             type: "POST",
    //             url: "/reports/suggest_crm_task_details_sub_users",
    //             data: {
    //                 search_managers: search_managers,
    //                 ticket_id: ticket_id
    //             }
    //         }).done(function (ret) {

    //             jQuery('#load-screen').hide(); 
    //             jQuery("#managers_suggestion_div").show();
    //             jQuery("#managers_suggestion_div").html(ret);

    //         });

    //     }        

    // });

    // subscribe managers
    // jQuery("#managers_suggestion_div").on('click','#managers_ul .managers_user_id',function(){

    //     var managers_user_id_dom = jQuery(this);
    //     var staff_id = managers_user_id_dom.attr("data-staff_id");
    //     var ticket_id = <?php echo $complaints_row->comp_id; ?>

    //     if( staff_id > 0 && ticket_id > 0 ){

    //         jQuery('#load-screen').show(); 
    //         jQuery.ajax({
    //             type: "POST",
    //             url: "/reports/subscribe_crm_task_details_sub_users",
    //             data: {
    //                 staff_id: staff_id,
    //                 ticket_id: ticket_id
    //             }
    //         }).done(function (ret) {
                
    //             jQuery('#load-screen').hide();  
    //             window.location.reload();                    

    //         });

    //     }            

    // });

    // hide when clicking outside script
    // jQuery(document).mouseup(function (e){

    //     var container = jQuery("#managers_suggestion_div");
    //     if (!container.is(e.target) // if the target of the click isn't the container...
    //         && container.has(e.target).length === 0) {
    //         container.hide();
    //     }

    // });



    // search developers    
    jQuery("#search_managers").keyup(function(){

        var search_managers_dom = jQuery(this);
        var search_managers = search_managers_dom.val();

        if( search_managers != '' ){

            jQuery('#load-screen').show(); 
            jQuery.ajax({
                type: "POST",
                url: "/reports/suggest_crm_task_managers",
                data: {
                    search_managers: search_managers
                }
            }).done(function (ret) {

                jQuery('#load-screen').hide(); 
                jQuery("#managers_suggestion_div").show();
                jQuery("#managers_suggestion_div").html(ret);

            });

        }            

    });

    // subscribe managers
    jQuery("#managers_suggestion_div").on('click','#managers_ul .managers_user_id',function(){

        var managers_user_id_dom = jQuery(this);
        var staff_id = managers_user_id_dom.attr("data-staff_id");
        var comp_id = <?php echo $complaints_row->comp_id; ?>

        if( staff_id > 0 && comp_id > 0 ){

            jQuery('#load-screen').show(); 
            jQuery.ajax({
                type: "POST",
                url: "/reports/subscribe_complaints_details_manager",
                data: {
                    staff_id: staff_id,
                    comp_id: comp_id
                }
            }).done(function (ret) {
                
                jQuery('#load-screen').hide();
                // console.log(staff_id);  
                window.location.reload();                    

            });

        }            

    });

    // hide when clicking outside script
    jQuery(document).mouseup(function (e){

        var container = jQuery("#managers_suggestion_div");
        if (!container.is(e.target) // if the target of the click isn't the container...
            && container.has(e.target).length === 0) {
            container.hide();
        }

    });


    // remove subscribe managers        
    jQuery(".remove_manager_btn").click(function(){

        var remove_manager_btn_dom = jQuery(this);
        var subcribed_staff_id = remove_manager_btn_dom.attr("data-subcribed_staff_id");
        var ticket_id = <?php echo $complaints_row->comp_id; ?>

        swal({
            title: "",
            text: "Are you sure want to remove user as subscribed to notifications?",
            type: "warning",						
            showCancelButton: true,
            confirmButtonClass: "btn-success",
            confirmButtonText: "Yes, Continue",
            cancelButtonClass: "btn-danger",
            cancelButtonText: "No, Cancel!",
            closeOnConfirm: true,
            showLoaderOnConfirm: true,
            closeOnCancel: true
        },
        function(isConfirm) {

            if (isConfirm) {							  
                                    
                if( subcribed_staff_id > 0 ){

                    jQuery('#load-screen').show(); 
                    jQuery.ajax({
                        type: "POST",
                        url: "/reports/unsubscribe_crm_task_details_sub_users",
                        data: {
                            subcribed_staff_id: subcribed_staff_id,
                            ticket_id: ticket_id
                        }
                    }).done(function (ret) {
                        
                        jQuery('#load-screen').hide();  
                        window.location.reload();                    

                    });

                }                   					

            }

        });	                       

    });

    // remove assigned developers       
    jQuery(".remove_dev_btn").click(function(){

        var remove_dev_btn_dom = jQuery(this);
        var staff_id = remove_dev_btn_dom.attr("data-subcribed_dev_id");
        var comp_id = <?php echo $complaints_row->comp_id; ?>

        swal({
            title: "",
            text: "Are you sure want to remove this manager from this complains ticket?",
            type: "warning",						
            showCancelButton: true,
            confirmButtonClass: "btn-success",
            confirmButtonText: "Yes, Continue",
            cancelButtonClass: "btn-danger",
            cancelButtonText: "No, Cancel!",
            closeOnConfirm: true,
            showLoaderOnConfirm: true,
            closeOnCancel: true
        },
        function(isConfirm) {

            if (isConfirm) {							  
                                    
                if( staff_id > 0 ){

                    jQuery('#load-screen').show(); 
                    jQuery.ajax({
                        type: "POST",
                        url: "/reports/unsubscribe_complaints_details_manager",
                        data: {
                            staff_id: staff_id,
                            comp_id: comp_id
                        }
                    }).done(function (ret) {
                        
                        jQuery('#load-screen').hide();  
                        window.location.reload();                    

                    });

                }                   					

            }

        });	                       

    });
    
});
</script>