<div class="box-typical box-typical-padding">

    <?php
    // breadcrumbs template
    $bc_items = array(
        array(
            'title' => "CRM Support Ticket",        
            'link' => "/reports/view_crm_tasks"
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

    <style>
    .j_file_icon{
        font-size: 31px;
        position: relative;
        top: 9px;
    }
    </style>

    <section>
        <div class="body-typical-body">                   

            <div class="row">
                <div class="col-md-7">
                    <form action="/reports/ticket_response/" enctype="multipart/form-data"  method="post">
                        <table class="table">  
                            <tr>
                                <td>Issue Summary:</td>
                                <td><input type='text' class="addinput form-control" name="issue_summary" id="issue_summary" value="<?php echo $crm_task_row->issue_summary; ?>" /></td>
                            </tr>                          
                            <tr>
                                <td>Help Topic:</td>
                                <td>                                    
                                     <select id="help_topic" name="help_topic"  class="form-control field_g2" required>
                                        <option value="">---</option>
                                        <?php
                                        foreach( $crm_task_help_topic_sql->result() as $crm_task_help_topic_row ){ ?>
                                            <option value="<?php echo $crm_task_help_topic_row->id; ?>" <?php echo ( $crm_task_help_topic_row->id == $crm_task_row->help_topic )?'selected':null; ?>><?php echo $crm_task_help_topic_row->help_topic; ?></option>
                                        <?php
                                        }
                                        ?>
                                        <option value="-1" <?php echo ( $crm_task_row->help_topic == -1 )?'selected':null; ?>>Other</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Ticket Priority:</td>
                                <td>
                                    <select id="ticket_priority" name="ticket_priority"  class="form-control field_g2" required>
                                        <option value="">---</option>
                                        <option value="1" <?php echo ( $crm_task_row->ticket_priority == 1 )?'selected':null; ?>>Low</option>
                                        <option value="2" <?php echo ( $crm_task_row->ticket_priority == 2 )?'selected':null; ?>>Medium</option>
                                        <option value="3" <?php echo ( $crm_task_row->ticket_priority == 3 )?'selected':null; ?>>High</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Ticket ID:</td>
                                <td><?php echo $crm_task_row->crm_task_id; ?></td>
                            </tr>
                            <tr>
                                <td>Page Link:</td>
                                <td>                       
                                    <input type='text' class="addinput form-control" name="page_link" id="page_link" value="<?php echo $crm_task_row->page_link; ?>" />
                                </td>
                            </tr>
                            <tr>
                                <td>Issue Details:</td>
                                <td>                                    
                                    <!--<textarea class="addtextarea form-control" name="describe_issue" id="describe_issue" style="height: 150px;"><?php // echo $crm_task_row->describe_issue; ?></textarea>-->
                                    <?php echo nl2br($crm_task_row->describe_issue); ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Created On:</td>
                                <td><?php echo ( $this->system_model->isDateNotEmpty($crm_task_row->date_created) )?date('d/m/Y H:i',strtotime($crm_task_row->date_created)):null; ?></td>
                            </tr>
                            <tr>
                                <td>Created By:</td>
                                <td>
                                    <?php      
                                    if( $crm_task_row->requested_by > 0 ){

                                        $staff = $this->gherxlib->getStaffInfo(['staff_id' => $crm_task_row->requested_by])->row_array();
                                        echo $logged_user_fullname = $this->system_model->formatStaffName($staff['FirstName'], $staff['LastName']);

                                    }                                                                 
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Last Updated On:</td>
                                <td><?php echo ( $this->system_model->isDateNotEmpty($crm_task_row->last_updated_ts) )?date('d/m/Y H:i',strtotime($crm_task_row->last_updated_ts)):null; ?></td>
                            </tr>
                            <tr>
                                <td>Last Updated By:</td>
                                <td>
                                    <?php      
                                    if( $crm_task_row->last_updated_by > 0 ){

                                        $staff = $this->gherxlib->getStaffInfo(['staff_id' => $crm_task_row->last_updated_by])->row_array();
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
                                            if ($crm_task_row->screenshot[0] == ",") {
                                                $image_path_url = substr($crm_task_row->screenshot, 1);
                                            } else {
                                                $image_path_url = $crm_task_row->screenshot;
                                            }

                                            $screenshotString = $image_path_url;
                                            $screenshotArray = explode(',', $screenshotString);
                                        ?>
                                        <?php  if ( $image_path_url != ''): ?>
                                                
                                                <?php foreach($screenshotArray as $index => $screenshot): 
                                                    
                                                    $image_path = "{$_SERVER['DOCUMENT_ROOT']}/{$screenshot}";
                                                    $image_type = exif_imagetype($image_path);

                                                    if( $image_type != false ){ // image ?>

                                                        <a href="/<?php echo str_replace(' ', '_', $screenshot); ?>" class="m-2 fancybox-uploaded-screenshot d-flex " style="overflow:hidden; max-width:32px;" >
                                                                                                        
                                                            <div 
                                                                class="border" 
                                                                style="
                                                                    background-image:   url('/<?php echo str_replace(' ', '_', $screenshot); ?>');
                                                                    border-radius:      1em;
                                                                    height:             32px;
                                                                    -moz-border-radius: 1em;
                                                                    width:              32px;
                                                                    background-size:cover;
                                                                "
                                                            >
                                                            </div>

                                                        </a>


                                                    <?php
                                                    }else{ // non-image ?>

                                                        <a href="/<?php echo str_replace(' ', '_', $screenshot); ?>" class="mr-2 ml-1"><span class="fa fa-file-o j_file_icon"></span></a>

                                                    <?php
                                                    }                                                       
                                                    ?>                                                                                                    

                                                <?php endforeach; ?>
                                                
                                        <?php endif; ?>
                                            
                                    </div> 
                                </td>
                            </tr>
                            <tr>
                                <td>Status</td>
                                <td>
                                    <select name="status" class="form-control">
                                        <option value="">---</option>
                                        <?php
                                        foreach( $crm_task_status_sql->result() as $crm_task_status_row ){ ?>
                                            <option value="<?php echo $crm_task_status_row->id; ?>" <?php echo ( $crm_task_status_row->id == $crm_task_row->status )?'selected':null; ?>><?php echo $crm_task_status_row->status; ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Assigned Dev</td>
                                <td>                                    
                                    <input type="text" id="search_devs" class="form-control" />
                                    <div id="devs_suggestion_div"></div>
                                    <div id="subscribed_devs_div" class="mt-2">
                                        <?php                                        
                                        foreach( $crm_task_dev_sql->result() as $dev_row ){ 

                                            $managers_full_name = $this->system_model->formatStaffName($dev_row->FirstName, $dev_row->LastName);
                                        ?>
                                            <label type="button" class="label label-info subscribe-btn btn-inline subscribed_dev remove_dev_btn"  data-subcribed_dev_id="<?php echo $dev_row->StaffID ?>"><?php echo $managers_full_name; ?> <i class="fa fa-close"></i></label>  
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
                                    <input type="hidden" name="crm_task_id" value="<?php echo $crm_task_row->crm_task_id; ?>" />
                                    <button type="submit" class="btn float-right" id="btn_submit">Submit</button>
                                </td>
                            </tr>             
                        </table>
                    </form>
                </div>
                <div class="col-md-5">

                                    
                    <div class="row">

                        <div class="col-md-6">                 
                            <label>Subscribe to this ticket <i id="manager_info_icon" class="fa fa-question-circle" title='Subscribed users will get notifications' data-toggle='tooltip' ></i></label>
                            <input type="text" id="search_managers" class="form-control" />
                            <div id="managers_suggestion_div"></div>
                            <div id="subscribed_managers_div" class="mt-2">
                                <?php
                                foreach( $crm_task_details_sub_users_sql->result() as $sub_users_row ){ 

                                    $managers_full_name = $this->system_model->formatStaffName($sub_users_row->FirstName, $sub_users_row->LastName);
                                ?>
                                    <label type="button" class="label label-success subscribe-btn btn-inline subscribed_manager remove_manager_btn"  data-subcribed_staff_id="<?php echo $sub_users_row->StaffID ?>"><?php echo $managers_full_name; ?> <i class="fa fa-close"></i></label>  
                                <?php
                                }
                                ?>                                              
                            </div>
                        </div>  

                    </div>

                    

                    <div class="row mt-3">

                        <div class="col">
                            <h5>Activity:</h5> 
                        
                            <div class="scrollit">               
                                <table class="table" id="activity_tbl">
                                    <tr>
                                        <th>Who</th>
                                        <th>Activity</th>
                                        <th>Timestamp</th>
                                    </tr>
                                    <?php
                                    // get crm task logs
                                    foreach( $crm_tasks_log_sql->result() as $crm_tasks_log_row ){ ?>
                                    <tr>
                                        <td>
                                            <?php 
                                            $staff = $this->gherxlib->getStaffInfo(['staff_id' => $crm_tasks_log_row->created_by])->row_array();
                                            echo $logged_user_fullname = $this->system_model->formatStaffName($staff['FirstName'], $staff['LastName']); 
                                            ?>
                                        </td>
                                        <td><?php echo nl2br($crm_tasks_log_row->log_text); ?></td>
                                        <td><?php echo ( $this->system_model->isDateNotEmpty($crm_tasks_log_row->created) )?date('d/m/Y H:i',strtotime($crm_tasks_log_row->created)):null; ?></td>
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
// get file extention
function get_file_type(file){

    return file.split('.').pop();

}

// check if file is image
function is_image(file_type) {

    const image_type_arr = ['gif', 'jpeg', 'jpg', 'png'];

    if( jQuery.inArray(file_type, image_type_arr) !== -1 ){
        return true;
    }else{
        return false;
    }
    
} 

function disable_lightbox_for_non_image(){

    jQuery("#activity_tbl .fancybox-uploaded-screenshot").each(function(){

        var logs_dom = jQuery(this);
        var link = logs_dom.attr("href");
        
        var file_type = get_file_type(link);
        var is_image_check = is_image(file_type);

        if( is_image_check == false ){

            // disable fancybox click popup event
            logs_dom.unbind('click.fb-start');            
    
        }

    });

}

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
    jQuery("#search_managers").keyup(function(){

        var search_managers_dom = jQuery(this);
        var search_managers = search_managers_dom.val();
        var ticket_id = <?php echo $crm_task_row->crm_task_id; ?>

        if( search_managers != '' ){

            jQuery('#load-screen').show(); 
            jQuery.ajax({
                type: "POST",
                url: "/reports/suggest_crm_task_details_sub_users",
                data: {
                    search_managers: search_managers,
                    ticket_id: ticket_id
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
        var ticket_id = <?php echo $crm_task_row->crm_task_id; ?>

        if( staff_id > 0 && ticket_id > 0 ){

            jQuery('#load-screen').show(); 
            jQuery.ajax({
                type: "POST",
                url: "/reports/subscribe_crm_task_details_sub_users",
                data: {
                    staff_id: staff_id,
                    ticket_id: ticket_id
                }
            }).done(function (ret) {
                
                jQuery('#load-screen').hide();  
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



    // search developers    
    jQuery("#search_devs").keyup(function(){

        var search_devs_dom = jQuery(this);
        var search_devs = search_devs_dom.val();
        var ticket_id = <?php echo $crm_task_row->crm_task_id; ?>

        if( search_devs != '' ){

            jQuery('#load-screen').show(); 
            jQuery.ajax({
                type: "POST",
                url: "/reports/suggest_crm_task_details_devs",
                data: {
                    search_devs: search_devs,
                    ticket_id: ticket_id
                }
            }).done(function (ret) {

                jQuery('#load-screen').hide(); 
                jQuery("#devs_suggestion_div").show();
                jQuery("#devs_suggestion_div").html(ret);

            });

        }        

    });

    // subscribe managers
    jQuery("#devs_suggestion_div").on('click','#devs_ul .dev_user_id',function(){

        var devs_user_id_dom = jQuery(this);
        var dev_id = devs_user_id_dom.attr("data-dev_id");
        var ticket_id = <?php echo $crm_task_row->crm_task_id; ?>

        if( dev_id > 0 && ticket_id > 0 ){

            jQuery('#load-screen').show(); 
            jQuery.ajax({
                type: "POST",
                url: "/reports/subscribe_crm_task_details_dev",
                data: {
                    dev_id: dev_id,
                    ticket_id: ticket_id
                }
            }).done(function (ret) {
                
                jQuery('#load-screen').hide();  
                window.location.reload();                    

            });

        }            

    });

    // hide when clicking outside script
    jQuery(document).mouseup(function (e){

        var container = jQuery("#devs_suggestion_div");
        if (!container.is(e.target) // if the target of the click isn't the container...
            && container.has(e.target).length === 0) {
            container.hide();
        }

    });


    // remove subscribe managers        
    jQuery(".remove_manager_btn").click(function(){

        var remove_manager_btn_dom = jQuery(this);
        var subcribed_staff_id = remove_manager_btn_dom.attr("data-subcribed_staff_id");
        var ticket_id = <?php echo $crm_task_row->crm_task_id; ?>

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
        var subcribed_dev_id = remove_dev_btn_dom.attr("data-subcribed_dev_id");
        var ticket_id = <?php echo $crm_task_row->crm_task_id; ?>

        swal({
            title: "",
            text: "Are you sure want to remove this developer from this ticket?",
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
                                    
                if( subcribed_dev_id > 0 ){

                    jQuery('#load-screen').show(); 
                    jQuery.ajax({
                        type: "POST",
                        url: "/reports/unsubscribe_crm_task_details_dev",
                        data: {
                            subcribed_dev_id: subcribed_dev_id,
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

    // activity logs pop up lightbox for image script
    disable_lightbox_for_non_image();
    
});
</script>