
<style>
    .jalign_left{
        text-align:left;
    }
    .txt_hid, .action_div{
        display:none;
    }
    .yello_mark{
        background-color: #ffff9d;
    }
    .green_mark{
        background-color: #c2ffa7;
    }
    .response{	
        margin: 0; 
        height: 70px;
        padding: 8px;
    }
    .response_div,
    #managers_suggestion_div{
        display: none;
    }
    .submitbtnImg {
        margin:5px;
    }
    #manager_info_icon{
        cursor: pointer;
    }
    #search_managers,
    #managers_suggestion_div{
        width:200px;
    }

    #managers_ul li,
    .subscribe-btn{
        cursor:pointer;
    }

    .jlabel{
        display: inline-block;
        padding: .25em .4em;
        line-height: 1;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: .25rem;
    }
    .ticket_status,
    .ticket_priority{
        line-height: normal;
        padding: 0px 4px;
        pointer-events: none;        
    }
    .phrase {
        font-size: 14px;
        height: 39px;
    }
</style>
<link rel="stylesheet" href="<?Php echo base_url(); ?>inc/js/lib/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" />

<div class="box-typical box-typical-padding">

    <?php
// breadcrumbs template
    $bc_items = array(
        array(
            'title' => "CRM Support Ticket",
            'status' => 'active',
            'link' => "/reports/view_crm_tasks"
        ),
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

    <header class="box-typical-header">
        <div class="box-typical box-typical-padding">
            <div class="form-row">
                <div class="col-md-1">
                    <label class="col-sm-12 form-control-label">&nbsp;</label>
                    <button style="float: left" id="add_task_btn"  class="btn btn-inline btn" type="submit">Create Ticket</button>
                </div>

                <div class="col-md-2">                 
                    <label>Subscribe <i id="manager_info_icon" class="fa fa-question-circle" title='Add your name here if you would like to be subscribed to notifications for this page' data-toggle='tooltip' ></i></label>
                    <input type="text" id="search_managers" class="form-control" />
                    <div id="managers_suggestion_div"></div>
                    <div id="subscribed_managers_div" class="mt-2">
                        <?php
                        foreach( $crm_task_managers_sql->result() as $managers_row ){ 

                            $managers_full_name = $this->system_model->formatStaffName($managers_row->FirstName, $managers_row->LastName);
                        ?>
                            <label type="button" class="label label-success subscribe-btn btn-inline subscribed_manager remove_manager_btn"  data-subcribed_staff_id="<?php echo $managers_row->StaffID ?>"><?php echo $managers_full_name; ?> <i class="fa fa-close"></i></label>  
                        <?php
                        }
                        ?>                                              
                    </div>
                </div>

                <div class="col-md-9">
                    <form method=POST action="/reports/view_crm_tasks" class="form-row">

                        <div class="col">
                            <div class="form-row">
                                <div class="col">
                                    <label class="form-control-label">Phrase</label>
                                    <input type="text" name="phrase_filter" class="form-control phrase" placeholder="Search Phrase" value="<?php echo $this->input->get_post('phrase_filter'); ?>">
                                </div>       
                            </div>
                        </div>

                        <div class="col">
                            <label>Date Filter:</label>
                            <select name="date_filter_type" class="form-control">
                                <option value="">---</option>
                                <option value="1" <?php echo ($this->input->get_post('date_filter_type') == 1)?'selected':null; ?>>Created</option>
                                <option value="2" <?php echo ($this->input->get_post('date_filter_type') == 2)?'selected':null; ?>>Last Updated</option>
                                <option value="3" <?php echo ($this->input->get_post('date_filter_type') == 3)?'selected':null; ?>>Completed</option> 
                            </select>
                        </div>

                        <div class="col">
                            <div class="form-row">
                                <div class="col">
                                    <label>From:</label>
                                    <input type="text" name="date_from" class="flatpickr form-control flatpickr-input" data-allow-input="true" value="<?Php echo ( $this->input->get_post('date_from') !='' )?$this->system_model->formatDate($this->input->get_post('date_from'),'d/m/Y'):null; ?>" />
                                </div>
                            </div>

                        </div>

                        <div class="col">
                            <div class="form-row">
                                <div class="col">
                                    <label>To:</label>
                                    <input type="text" name="date_to" class="flatpickr form-control flatpickr-input" data-allow-input="true" value="<?Php echo ( $this->input->get_post('date_to') !='' )?$this->system_model->formatDate($this->input->get_post('date_to'),'d/m/Y'):null; ?>" />                                    
                                </div>         
                            </div>
                        </div>

                        <div class="col">
                            <label>Priority</label>
                            <select name="ticket_priority" class="form-control">
                                <option value="">---</option>
                                <option value="1" <?php echo ($this->input->get_post('ticket_priority') == 1)?'selected':null; ?>>Low</option>
                                <option value="2" <?php echo ($this->input->get_post('ticket_priority') == 2)?'selected':null; ?>>Medium</option>
                                <option value="3" <?php echo ($this->input->get_post('ticket_priority') == 3)?'selected':null; ?>>High</option> 
                            </select>
                        </div>
                        <div class="col">
                            <label>Status</label>
                            <select name="status" class="form-control">                                
                                <option value="">---</option>
                                <option value="all">ALL</option>
                                <?php
                                foreach( $crm_task_status_sql->result() as $crm_task_status_row ){ ?>
                                    <option value="<?php echo $crm_task_status_row->id; ?>" <?php echo ( $crm_task_status_row->id == $this->input->get_post('status') )?'selected':null; ?>><?php echo $crm_task_status_row->status; ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col">
                            <label>Topic</label>
                            <select name="help_topic" class="form-control">
                                <option value="">---</option>   
                                <?php
                                foreach( $crm_task_help_topic_sql->result() as $crm_task_help_topic_row ){ ?>
                                    <option value="<?php echo $crm_task_help_topic_row->id; ?>" <?php echo ( $crm_task_help_topic_row->id == $this->input->get_post('help_topic') )?'selected':null; ?>><?php echo $crm_task_help_topic_row->help_topic; ?></option>
                                <?php
                                }
                                ?>      
                                <option value="-1">Other</option>                     
                            </select>
                        </div>
                        <div class="col">
                            <label>Created By</label>
                            <select name="created_by" class="form-control">
                                <option value="">---</option>   
                                <?php
                                foreach( $distinct_created_by_sql->result() as $distinct_created_by_row ){ 

                                    $created_by_full_name = $this->system_model->formatStaffName($distinct_created_by_row->FirstName, $distinct_created_by_row->LastName);
                                ?>
                                    <option value="<?php echo $distinct_created_by_row->StaffID; ?>" <?php echo ( $distinct_created_by_row->StaffID == $this->input->get_post('created_by') )?'selected':null; ?>><?php echo $created_by_full_name; ?></option>
                                <?php
                                }
                                ?>                             
                            </select>
                        </div>

                        <div class="col">
                            <label>Assigned Dev</label>
                            <select name="assigned" class="form-control">
                                <option value="">---</option>   
                                <?php
                                foreach( $assigned_dev->result() as $ass_dev ){ 

                                    $assigned_full_name = $this->system_model->formatStaffName($ass_dev->FirstName, $ass_dev->LastName);
                                ?>
                                    <option value="<?php echo $ass_dev->StaffID; ?>" <?php echo ( $ass_dev->StaffID == $this->input->get_post('assigned') )?'selected':null; ?>><?php echo $assigned_full_name; ?></option>
                                <?php
                                }
                                ?>                             
                            </select>
                        </div>
                                                

                        <div class="col">
                            <input type="hidden" name="search_flag" value="1" />
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <input style="float: right" class="btn btn-inline btn-danger" type="submit" value="Search">
                        </div>

                    </form>   
                </div>
            </div>
        </div>
    </header>

    <section>
        <div class="body-typical-body">
            <div class="table-responsive">
                <table class="table table-hover main-table">
                    <thead>
                        <tr>
                            <th>Ticket ID</th>
                            <th>Date</th>
                            <th>Subject</th>
                            <th>Topic</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Assigned</th>
                            <th>Created By</th>
                            <th>Last Updated</th>
                            <th>Completed</th>
                            <th>View</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $i = 0;
                        //         echo "<pre>";
                        // var_dump($tasks); die();
                        //         echo "<pre>";
                        if (count($tasks) > 0) {
                            foreach ($tasks as $row) {
                                $date = ( $this->system_model->isDateNotEmpty($row['date_created']) )?date('d/m/Y', strtotime($row['date_created'])):null;
                                $page_link = $row['page_link'];
                                $describe_issue = $row['describe_issue'];
                                $user = $this->system_model->formatStaffName($row['FirstName'], $row['LastName']);
                                $response = $row['response'];
                                $status_name = "";                                                            
                                $priority_class = null;
                                ?>
                                <tr>
                                    <td><?php echo $row['crm_task_id']; ?></td>
                                    <td><?php echo $date; ?></td>	
                                    <td><?php echo $row['issue_summary']; ?></td>
                                    <td><?php echo ( $row['help_topic'] > 0 )?$row['ctht_help_topic']:'Other'; ?></td>
                                    <td>
                                        <?php                             
                                        // ticket priority
                                        $ticket_priority_txt = null;
                                        switch($row['ticket_priority']){
                                            case 1:
                                                $ticket_priority_txt = 'Low';
                                                $priority_class="btn-success";
                                            break;
                                            case 2:
                                                $ticket_priority_txt = 'Medium';
                                                $priority_class="btn-warning";
                                            break;
                                            case 3:
                                                $ticket_priority_txt = 'High';
                                                $priority_class="btn-danger";
                                            break;
                                        }                                        
                                        ?>
                                        <!--<span class="<?php echo $priority_class; ?>"><?php echo $ticket_priority_txt; ?></span> -->
                                        <button type="button" class="ticket_priority btn <?php echo $priority_class; ?>"><?php echo $ticket_priority_txt; ?></button>
                                    </td>
                                    <td>
                                        <?php 
                                        // status                              
                                        switch( $row['ct_status'] ){
                                            case 1: // Pending                                                
                                                $status_class = "btn-primary-outline";
                                            break;
                                            case 2: // Declined                                                
                                                $status_class = "btn-secondary";
                                            break; 
                                            case 3: // In Progress                                                
                                                $status_class = "btn-outline-success";
                                            break;
                                            case 4: // Completed                                                
                                                $status_class = "btn-success";
                                            break;
                                            case 5: // QA                                                
                                                $status_class = "btn-info-outline";
                                            break;
                                            case 6: // More info required                                                
                                                $status_class = "btn-warning-outline";
                                            break;
                                            case 7: // Unable to Replicate                                                
                                                $status_class = "btn-danger-outline";
                                            break;
                                            case 8:
                                                $status_class = "btn-secondary-outline";
                                            break;                                           
                                        }          
                                        ?>
                                        <!--<span class="jlabel" style="background-color: <?php echo $row['hex']; ?>;"><?php echo $row['cts_status']; ?></span>  -->
                                        <button type="button" class="ticket_status btn <?php echo $status_class; ?>"><?php echo $row['cts_status']; ?></button>
                                    </td>  
                                    <td>
                                        <?php 
                                        // get developers assigned on this task
                                        $crm_task_dev_sql = $this->db->query("
                                        SELECT 
                                            sa.`StaffID`,
                                            sa.`FirstName`,
                                            sa.`LastName`
                                        FROM `crm_task_details_devs` AS ctd_dev
                                        INNER JOIN `staff_accounts` AS sa ON ctd_dev.`dev_id` = sa.`StaffID`
                                        WHERE ctd_dev.`active` = 1
                                        AND sa.`active` = 1
                                        AND sa.`Deleted` = 0
                                        AND ctd_dev.`ticket_id` = {$row['crm_task_id']}            
                                        ");

                                        $assigned_dev_arr = [];
                                        foreach( $crm_task_dev_sql->result() as $crm_task_dev_row ){

                                            if( $crm_task_dev_row->StaffID > 0 ){
                                                $assigned_dev_arr[] = $this->system_model->formatStaffName($crm_task_dev_row->FirstName, $crm_task_dev_row->LastName);
                                            }

                                        }
                                        echo implode("<br /> ",$assigned_dev_arr);                                        
                                        ?>
                                    </td>
                                    <td><?php echo $this->system_model->formatStaffName($row['FirstName'], $row['LastName']); ?></td>
                                    <td><?php echo ( $this->system_model->isDateNotEmpty($row['last_updated_ts']) )?date('d/m/Y', strtotime($row['last_updated_ts'])):null ?></td>
                                    <td><?php echo ( $this->system_model->isDateNotEmpty($row['completed_ts']) )?date('d/m/Y', strtotime($row['completed_ts'])):null ?></td>                                    
                                    <td>
                                        <a href="/reports/ticket_details/?id=<?php echo $row['crm_task_id']; ?>">
                                            <button type="submit" class="btn" id="btn_details">
                                                Details
                                            </button>
                                        </a>                                        
                                    </td>                                                                 	
                                </tr>

                                <?php
                                $i++;
                            }
                        } else {
                            ?>
                        <td colspan="11" align="left">No Data</td>
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

<!--Fancybox Start--> 
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

    <h4><?php echo $title; ?></h4>
    <p>
        <pre>
            <code><?=$sql_query?></code>
        </pre>
    </p>

</div>
<!-- Fancybox END -->

<!--Fancybox Start--> 
<a href="javascript:;" id="add_task_link" class="fb_trigger" data-fancybox data-src="#add_task" style="display: none">Trigger the fancybox</a>							
<div id="add_task" class="fancybox" style="display:none; width: 50%;" >

    <h4>Create Ticket</h4>
    <div id="add_task_div" class="addproperty formholder">
        <form id="add_task_form" method="post" action="/reports/update_crm_task_action_form_submit" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add" />
            <div class="row mb-3">                
                <div class="col-md-6">
                    <label class="addlabel" for="title"><span class="text-danger">*</span> Topic</label>
                    <select id="help_topic" name="help_topic"  class="form-control field_g2" required>
                        <option value="">---</option>
                        <?php
                        foreach( $crm_task_help_topic_sql->result() as $crm_task_help_topic_row ){ ?>
                            <option value="<?php echo $crm_task_help_topic_row->id; ?>"><?php echo $crm_task_help_topic_row->help_topic; ?></option>
                        <?php
                        }
                        ?>
                        <option value="-1">Other</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="addlabel" for="title"><span class="text-danger">*</span> Ticket Priority</label>
                    <select id="ticket_priority" name="ticket_priority"  class="form-control field_g2" required>
                        <option value="">---</option>
                        <option value="1">Low</option>
                        <option value="2">Medium</option>
                        <option value="3">High</option>
                    </select>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col">
                    <label class="addlabel" for="title"><span class="text-danger">*</span> Issue Summary</label>
                    <input type='text' class="addinput form-control" name="issue_summary" id="issue_summary" required />
                </div>
            </div>    
                  
            <div class="row mb-3">
                <div class="col">
                    <label class="addlabel" for="title">Issue Details</label>
                    <textarea class="addtextarea form-control" name="describe_issue" id="describe_issue" style="height: 150px;"></textarea>
                </div>                
            </div>

            <div class="row mb-3">
                <div class="col">
                    <label class="addlabel" for="title">Page Link</label>
                    <input type='text' class="addinput form-control" name="page_link" id="page_link" />
                </div>
            </div>
           

            <div class="row screenshot_file_div mb-3">
                <!-- <div class="col">
                    <label class="addlabel" for="title">Screenshot</label>					
                    <input type='file' class="addinput screenshot form-control" name="screenshot" />
                </div>							 -->

                    <div class="col">
                        <label class="addlabel" for="title">Screenshot/File</label>		
                       		
                        <div class="d-flex align-items-center">
                        <input type='file' id="screenshot" class="addinput screenshot form-control" name="screenshot[]" multiple="" hidden />
                        <label class="btn btn-sm btn-success pr-2 mt-2 " for="screenshot">
                        <span class="d-flex align-items-center">
                            
                            Choose a file
                                <svg xmlns="http://www.w3.org/2000/svg" height="15px" width="15px" class="h-6 w-6 text-light mx-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                            </span>
                        </label>
                        <label for="screenshot" class="text-md text-muted mx-2 align-items-center" id="fileCounter"></label>
                        </div>

                        <div class="text-sm-left mt-2 mb-2" role="alert">
                                    <small>Hold down the <b>Ctrl</b> key and click any other file(s) to select multiple files. </small>
                                    </div>	
                    </div>							
            </div>
            <!--
            <div class="row">
                    <label class="addlabel" for="title"></label>
                    <button type="button" class="submitbtnImg" id="btn_add_file" style="float: left;">
                            <img class="inner_icon" src="images/button_icons/add-button.png">
                            Add
                    </button>
            </div>	
            -->
            <div class="row">
                <div class="col-md-6">
                    <button type="button" class="btn btn-warning mr-2 float-left" id="btn_reset">
                        Reset
                    </button>
                    <button type="button" class="btn btn-danger float-left" id="btn_cancel">
                        Cancel
                    </button>
                </div>
                <div class="col-md-6">
                    <button type="submit" class="submitbtnImg btn float-right" id="btn_save">
                        Create Ticket
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>
<!-- Fancybox END -->

<script src="<?Php echo base_url(); ?>inc/js/lib/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script>
    jQuery(document).ready(function () {
        jQuery('.fancybox-uploaded-screenshot').fancybox();
        jQuery('#add_task_btn').click(function () {
            jQuery('#add_task_link').click();
        });


        /*
         // multiple screenshot
         jQuery("#btn_add_file").click(function(){
         
         var last_photo_elem = jQuery(".screenshot_file_div:last");
         var photo_elem = last_photo_elem.clone();
         photo_elem.find(".screenshot").val("");
         last_photo_elem.after(photo_elem);
         
         });
         */

        //  screenshot file counter putting it on label 
        $("#screenshot").on("change", function(){  
            var numFiles = $(this)[0].files.length;
            $('#fileCounter').html('<b>'+numFiles+'</b> file(s) selected.');
        });

        // save response
        jQuery(".btn_response_send").click(function () {

            var ct_id = jQuery(this).parents("tr:first").find(".ct_id").val();
            var page_link = jQuery(this).parents("tr:first").find(".page_link").val();
            var describe_issue = jQuery(this).parents("tr:first").find(".describe_issue").val();
            var response = jQuery(this).parents("tr:first").find(".response").val();
            var email = jQuery(this).parents("tr:first").find(".email").val();
            var error = "";

            if (response == "") {
                error += "Response is required";
            }

            if (error != "") {
                alert(error);
            } else {

                jQuery.ajax({
                    type: "POST",
                    url: "/reports/update_crm_task_action_form_submit",
                    data: {
                        ct_id: ct_id,
                        page_link: page_link,
                        describe_issue: describe_issue,
                        response: response,
                        email: email,
                        action: 'response'
                    }
                }).done(function (ret) {
                    window.location.reload();
                });

            }

        });


        // response toggle
        jQuery(".btn_response").click(function () {

            var btn_txt = jQuery(this).find(".inner_icon_txt").html();
            var orig_btn_txt = 'Response';
            var orig_btn_icon = 'images/button_icons/email.png';
            var cancel_btn_icon = 'images/button_icons/cancel-button.png';

            if (btn_txt == orig_btn_txt) {
                jQuery(this).removeClass('blue-btn');
                jQuery(this).find(".inner_icon_txt").html('Cancel');
                jQuery(this).find(".inner_icon").attr("src", cancel_btn_icon)
                jQuery(this).parents("tr:first").find(".response_div").show();
            } else {
                jQuery(this).addClass('blue-btn');
                jQuery(this).find(".inner_icon_txt").html(orig_btn_txt);
                jQuery(this).find(".inner_icon").attr("src", orig_btn_icon)
                jQuery(this).parents("tr:first").find(".response_div").hide();
            }


        });


        // update
        jQuery(".btn_update").click(function () {

            var ct_id = jQuery(this).parents("tr:first").find(".ct_id").val();
            var page_link = jQuery(this).parents("tr:first").find(".page_link").val();
            var describe_issue = jQuery(this).parents("tr:first").find(".describe_issue").val();
            var response = jQuery(this).parents("tr:first").find(".response").val();
            var status = jQuery(this).parents("tr:first").find(".status").val();
            var error = "";

            /*
            if (page_link == "") {
                error += "Page Link is required\n";
            }

            if (describe_issue == "") {
                error += "Describe Page is required\n";
            }
            */

            if (error != "") {
                swal({
                    title: "Error!",
                    text: error,
                    type: "error",
                    confirmButtonClass: "btn-danger"
                });
            } else {

                jQuery.ajax({
                    type: "POST",
                    url: "/reports/update_crm_task_action_form_submit",
                    data: {
                        ct_id: ct_id,
                        page_link: page_link,
                        describe_issue: describe_issue,
                        response: response,
                        status: status,
                        action: 'update'
                    }
                }).done(function (ret) {
                    window.location.reload();
                });

            }

        });


        // delete script
        jQuery(".btn_delete").click(function () {

            var ct_id = jQuery(this).parents("tr:first").find(".ct_id").val();

            if (confirm("Are you sure you want to delete?")) {

                jQuery.ajax({
                    type: "POST",
                    url: "/reports/update_crm_task_action_form_submit",
                    data: {
                        ct_id: ct_id,
                        action: 'delete'
                    }
                }).done(function (ret) {
                    window.location.reload();
                });

            }
        });

        // inline edit toggle
        jQuery(".btn_edit").click(function () {

            var btn_txt = jQuery(this).html();

            jQuery(this).hide();

            if (btn_txt == 'Edit') {
                jQuery(this).parents("tr:first").find(".action_div").show();
                jQuery(this).parents("tr:first").find(".txt_hid").show();
                jQuery(this).parents("tr:first").find(".txt_lbl").hide();
            } else {
                jQuery(this).parents("tr:first").find(".action_div").hide();
            }

        });


        // cancel
        jQuery(".btn_cancel").click(function () {
            jQuery(this).parents("tr:first").find(".action_div").hide();
            jQuery(this).parents("tr:first").find(".txt_hid").hide();
            jQuery(this).parents("tr:first").find(".txt_lbl").show();
            jQuery(this).parents("tr:first").find(".btn_edit").show();
        });


        // add task toggle
        jQuery("#add_task_btn").click(function () {



            var btn_txt = $(this).html();
            var default_btn_txt = 'Create Ticket';
            var add_icon_src = 'images/button_icons/add-button.png';
            var cancel_icon_src = 'images/button_icons/cancel-button.png';

            if (btn_txt == default_btn_txt) {
                jQuery("#bulk_payment_details_div").show();
                jQuery(this).find(".inner_icon_span").html("Cancel");
                jQuery(this).find(".inner_icon").attr("src", cancel_icon_src);
                jQuery("#add_task_div").show();
            } else {
                jQuery("#bulk_payment_details_div").hide();
                jQuery(this).find(".inner_icon_span").html(default_btn_txt);
                jQuery(this).find(".inner_icon").attr("src", add_icon_src);
                jQuery("#add_task_div").hide();
            }

        });


        // validation
        jQuery("#add_task_form").submit(function () {

            var page_link = jQuery("#page_link").val();
            var describe_issue = jQuery("#describe_issue").val();
            var error = "";

            /*
            if (page_link == "") {
                error += "Page Link is required\n";
            }

            if (describe_issue == "") {
                error += "Describe Issue is required\n";
            }
            */

            if (error != "") {
                alert(error);
                return false
            } else {
                return true;
            }

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


        // close add ticket fancybox
        jQuery("#btn_reset").click(function(){

            var add_task_form = jQuery("#add_task_form");

            add_task_form.find("select").val('');
            add_task_form.find("input").val('');
            add_task_form.find("textarea").val('');

        });


        // close add ticket fancybox
        jQuery("#btn_cancel").click(function(){

            $.fancybox.close();
            
        });


        // suggest managers
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

            if( staff_id > 0 ){

                jQuery('#load-screen').show(); 
                jQuery.ajax({
                    type: "POST",
                    url: "/reports/subscribe_crm_task_managers",
                    data: {
                        staff_id: staff_id
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

        // remove subscribe managers        
        jQuery(".remove_manager_btn").click(function(){

            var remove_manager_btn_dom = jQuery(this);
            var subcribed_staff_id = remove_manager_btn_dom.attr("data-subcribed_staff_id");

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
                            url: "/reports/unsubscribe_crm_task_managers",
                            data: {
                                subcribed_staff_id: subcribed_staff_id
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