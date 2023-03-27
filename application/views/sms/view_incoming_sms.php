<div class="box-typical box-typical-padding">

    <?php
    // breadcrumbs template
    $bc_items = array(
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/sms/view_incoming_sms"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

    <header class="box-typical-header">
        <div class="box-typical box-typical-padding">
            <div class="form-row">
                <div class="col-md-8">
                    <form method=POST action="/sms/view_incoming_sms" class="form-row">
                        <div class="col-md-3">
                            <div class="form-row">
                                <div class="col-md-6">
                                    <label>From:</label>
                                    <input autocomplete="off"  type="label" name="from_date" value="<?php echo $from_date; ?>" class="flatpickr form-control flatpickr-input">
                                </div>
                                <div class="col-md-6">
                                    <label>To:</label>
                                    <input autocomplete="off" type="label" name="to_date" value="<?php echo $to_date; ?>" class="flatpickr form-control flatpickr-input">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>SMS Type</label>
                                    <select name="sms_type" class="form-control">
                                        <option value="" selected="selected">ALL</option>
                                        <?Php
                                        foreach ($sat_list->result_array() as $sat) {
                                            ?>

                                            <option value="<?php echo $sat['sms_api_type_id']; ?>" <?php echo ($sat['sms_api_type_id'] == $sms_type) ? 'selected="selected"' : ''; ?>><?php echo $sat['type_name']; ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label>Sent By</label>
                                    <select name="sent_by" class="form-control">
                                        <option value="" selected="selected">ALL</option>
                                        <?Php
                                        $sent_by = $this->input->get_post('sent_by');
                                        foreach ($sent_by_list->result_array() as $row) {
                                            ?>
                                            <option value="<?php echo $row['sent_by']; ?>" <?php echo ( $row['sent_by'] == $sent_by ) ? 'selected="selected"' : ''; ?>><?php echo $this->system_model->formatStaffName($row['FirstName'], $row['LastName']); ?></option>
                                            <?Php
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="phrase">Phrase</label>
                                    <input type="text" class="form-control" name="phrase" placeholder="Phrase" value="<?php echo $this->input->get_post('phrase'); ?>" />
                                </div>
                            </div>
                        </div>

                        

                        <div class="col-md-4">        
                            <div class="left">
                                <input type="hidden" name="search_flag" value="1" />
                                <label class="col-sm-12 form-control-label">&nbsp;</label>
                                <input class="btn btn-inline" type="submit" value="Search">
                            </div>
                            <div class="left">
                                <label class="col-sm-12 form-control-label">&nbsp;</label>
                                <a href="/sms/view_incoming_sms/<?Php echo ((int) $this->input->get('show_all') == 1) ? "" : "?show_all=1" ?>" >
                                    <button class="btn btn-inline" type="button" >
                                        <?php echo ($this->input->get('show_all') == 1) ? 'Unread Only' : 'Display ALL' ?>
                                    </button>
                                </a>
                            </div>
                        </div>
                        <!--  <div class="col-md-2"> -->


<!--                                <input class="form-check-input" type="checkbox" value="" id="sms_replies_chk_main" name="show_all">
<label class="form-check-label" for="sms_replies_chk_main" id="sms_chk_hdr">
                        <?php // echo ($show_all==1)?'Display Unread Only':'Display ALL'  ?>
</label>-->

                        <!-- </div> -->

                    </form>   
                </div>
                <div class="col-md-3">
                    <?Php
                    $cs = $crm_setting->result_array()[0];
                    ?>
                    <div class="fl-left">
                        <label class="col-sm-12 form-control-label">&nbsp;</label>
                        <label style="margin-right: 9px;">
                            <span style="color:red;"><?php echo $cs['sms_credit']; ?></span> 
                            SMS Credits - Last Updated: <span class="timestampTextColor"><?php echo date('d/m/Y H:i', strtotime($cs['sms_credit_update_ts'])); ?></span> 
                        </label>	
                        <br />
                        <a href="https://docs.google.com/spreadsheets/d/10Cgv97hCq7AMtTyTLGskFITo-M9UmpWZecvmP5tDXSU/edit?usp=sharing">
                            <span class="badge badge-primary">Sales Document</span>
                        </a>
                    </div>
                </div>
                <div class="col-md-1">
                    <label class="col-sm-12 form-control-label">&nbsp;</label>
                    <a href="/sms/update_sms_credits_action_form_submit/?redirect=incoming_sms">
                        <button type="button" class="btn" id="btn_check_credit" style="float:right;width:auto;">
                            Check Credit
                        </button>
                    </a>
                </div>
            </div>
    </header>
    <section>
        <div class="body-typical-body">
            <div class="table-responsive">

                <table class="table table-hover main-table jmenu_table">

                    <thead>
                        <tr>
                            <th style="width:60px">Job ID</th>
                            <th>Sent By</th>
                            <th>SMS Type</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>From</th>
                            <th>Tenant</th>
                            <th style="width:40%">Message</th>
                            <th>STR</th>
                            <th></th>
                            <th>Unread</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        foreach ($list->result_array() as $cr) {
                            $sent_by_name = ( $cr['sent_by'] == -3 ) ? "CRM" : "{$this->system_model->formatStaffName($cr['FirstName'], $cr['LastName'])}";
                            ?>
                            <tr>
                                <td><?php echo ($cr['message_id']!="") ? $this->gherxlib->crmLink('vjd',$cr['job_id'],$cr['job_id']) : "";?></td>
                                <td><?php echo ($cr['message_id']!="") ? $sent_by_name : ""; ?></td>
                                <td><?php echo ($cr['message_id']!="") ? $cr['type_name'] : ""; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($cr['sar_created_date'])); ?></td>
                                <td><?php echo date('H:i', strtotime($cr['sar_created_date'])); ?></td>
                                <td class="mob_td"><?php echo $mob_num = '0'.substr($cr['sar_mobile'],2);	?></td>	
                                <td>
                                    <?php
                                    $tenant_name = '( Unable to display tenant - not linked to a job )'; // default text for troubleshooting
                                    if( $cr['property_id'] > 0 ){

                                        // get tenants 
                                        $sel_query = "
                                        pt.`property_tenant_id`,
                                        pt.`tenant_firstname`,
                                        pt.`tenant_lastname`,
                                        pt.`tenant_mobile`,
                                        pt.`tenant_email`
                                        ";
                                        $params = array(
                                        'sel_query' => $sel_query,
                                        'property_id' => $cr['property_id'],
                                        'pt_active' => 1,
                                        'display_query' => 0
                                        );
                                        $pt_sql = $this->properties_model->get_property_tenants($params);
                                        if( $pt_sql->num_rows() > 0 ){

                                            // loop through tenants
                                            foreach($pt_sql->result() as $pt_row){
                                                $tenants_num = str_replace(' ', '', trim($pt_row->tenant_mobile));							
                                                if( $tenants_num != '' && $tenants_num == $mob_num ){
                                                    $tenant_name = $pt_row->tenant_firstname;
                                                }
                                            }
                                        }

                                        echo $tenant_name;

                                    }                                    
                                    ?>
                                </td>
                                <td><?php echo $cr['response']; ?></td>
                                <td>
                                    <?php
                                    if( $cr['message_id']!="" ){

                                  
                                    if( $cr['job_id'] > 0 ){ ?>

                                        <button type="button" class="submitbtnImg blue-btn btn_show_str btn">Show STR</button>
                                        <span class="display_str_div"></span>

                                    <?php
                                    }
                                    if($cr['type_name'] == "Sales upgrade"){ ?>
                                        <a href="mailto:salesupgrades@sats.com.au?Subject=Sales Upgrade SMS Response&body=<?php echo $cr['response']; ?>"><button type="button" class="submitbtnImg blue-btn btn">Email</button></a>
                                    <?php } 
                                      }
                                    ?>                                    
                                </td>
                                <td class="td_process_btn">
                                    <?php
                                    if( $cr['message_id']!="" ){
                                    if( $cr['job_id'] > 0 ){

                                        // SMS (Thank You) or SMS Reply (Booking Confirmed) or SMS (Reminder)
                                        if ($cr['sms_api_type_id'] == 18 || $cr['sms_api_type_id'] == 16 || $cr['sms_api_type_id'] == 19) {
                                            ?>
                                            <button type="button" class="submitbtnImg btn_function btn_process btn btn-danger" data-btn_type='process'>Process</button>
                                            <?php
                                        }
                                        // No Answer (Yes/No SMS Reply)
                                        //echo $this->gherxlib->crmLink('vjd', $cr['job_id'], '<button type="button" class="submitbtnImg btn_function btn_insert_n_open_job btn btn-danger" data-btn_type="insert_n_open_ob">Insert & Open Job</button>');
                                        ?>
                                        <a class="insert_n_open_job_link" href='javascript:void(0);'>
                                            <button type="button"  class="submitbtnImg btn_function btn_insert_n_open_job btn btn-danger" data-btn_type="insert_n_open_ob">Insert & Open Job</button>
                                        </a>
                                        <?php
                                        //}
                                        ?>	
                                        <?php
                                        // SMS block
                                        if ($cr['saved'] == 1) { // if sms already sent today
                                            $disabled_txt = 'disabled="disabled"';
                                            $add_class = 'jfadeIt';
                                        } else {
                                            $disabled_txt = '';
                                            $add_class = '';
                                        }

                                    }
                                    }
                                    ?>
                    <!--<button type="button" <?php echo $disabled_txt; ?> class="submitbtnImg <?php echo $add_class; ?> btn_function btn_save" data-btn_type='save'>Save</button>-->
                                </td>
                                <td>
                                    <input type="checkbox" class="sms_replies_chk" <?php echo ($cr['unread'] == 1) ? 'checked="checked"' : ''; ?> />
                                    <input type="hidden" class="job_id" value="<?php echo $cr['job_id']; ?>" />
                                    <input type="hidden" class="message_id" value="<?php echo $cr['message_id']; ?>" />
                                    <input type="hidden" class="sas_id" value="<?php echo $cr['sms_api_sent_id']; ?>" />
                                    <input type="hidden" class="sar_id" value="<?php echo $cr['sms_api_replies_id']; ?>" />
                                    <input type="hidden" class="tenant_name" value="<?php echo $tenant_name; ?>" />
                                    <input type="hidden" class="reply_msg" value="<?php echo $cr['response']; ?>" />
                                    <input type="hidden" class="sms_type" value="<?php echo $cr['sms_type']; ?>" />		
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
</div>




</div>

<!-- Fancybox START -->
<!-- ABOUT TEXT -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

    <h4><?php echo $title; ?></h4>
    <p>
        This page shows all incoming SMS
    </p>
    <p>*To search for a phone number using the 'Phrase' search, please remove the '0' from the start of the number.</p>
    <pre>
        <code><?php echo $sql_query; ?></code>
    </pre>
</div>

<!-- Fancybox END -->

<style>
    .fancybox-content {
        width: 50%;
    }
    .temp_name_col {
        width: 30%;
    }
    .desc_col {
        width: 57%;
    }
    .tags_div button{
        margin-bottom: 5px;
        width: 84%;
    }
    .td_process_btn button {
        margin: 2px 0;
    }
</style>
<script>
function process_sms_replies(obj){

    var job_id = obj.parents("tr:first").find(".job_id").val();
    var sar_id = obj.parents("tr:first").find(".sar_id").val();
    var sas_id = obj.parents("tr:first").find(".sas_id").val();
    var message_id = obj.parents("tr:first").find(".message_id").val();
    var tenant_name = obj.parents("tr:first").find(".tenant_name").val();
    var reply_msg = obj.parents("tr:first").find(".reply_msg").val();
    var btn_type = obj.attr("data-btn_type");
    var sms_type = obj.parents("tr:first").find(".sms_type").val();

    
    jQuery("#load-screen").show();
    jQuery.ajax({
        type: "POST",
        url: "/sms/process_sms_api_replies_action_ajax",
        data: {
            job_id: job_id,
            sar_id: sar_id,
            sas_id: sas_id,
            message_id: message_id,
            tenant_name: tenant_name,
            reply_msg: reply_msg,
            sms_type: sms_type
        }
    }).done(function (ret) {

        jQuery("#load-screen").hide();  

        // disable button to prevent double processing
        obj.parents("tr:first").find(".btn_insert_n_open_job").addClass('fadeIt');
        obj.parents("tr:first").find(".btn_insert_n_open_job").attr('disabled', 'disabled');
        obj.parents("tr:first").find(".btn_process").addClass('fadeIt');
        obj.parents("tr:first").find(".btn_process").attr('disabled', 'disabled');

        if( obj.hasClass("insert_n_open_job_link") == true ){
            var url = "<?php echo $this->config->item("crm_link"); ?>/view_job_details.php?id="+job_id;        
            window.open(url, "_blank"); 
        }  

    });

}

jQuery(document).ready(function () {

<?php if ($this->session->flashdata('update_template_success') && $this->session->flashdata('update_template_success') == 1) { ?>
        swal({
            title: "Success!",
            text: "Template Updated",
            type: "success",
            confirmButtonClass: "btn-success"
        });
<?php
}
?>

<?php if ($this->session->flashdata('add_template_success') && $this->session->flashdata('add_template_success') == 1) { ?>
        swal({
            title: "Success!",
            text: "Template Added",
            type: "success",
            confirmButtonClass: "btn-success"
        });
<?php
}
?>

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

    jQuery(".sms_replies_chk").change(function () {

        var obj = jQuery(this);
        var chk_state = obj.prop("checked");
        var sar_id = obj.parents("tr:first").find(".sar_id").val();

        if (chk_state == true) {
            var unread = 1;
        } else {
            var unread = 0;
        }

        swal({
            title: "Warning!",
            text: "Are you sure you want to continue?",
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
                
                jQuery("#load-screen").show();
                jQuery.ajax({
                    type: "POST",
                    url: "/sms/toggle_sms_replies_action_ajax",
                    data: {
                        sar_id: sar_id,
                        unread: unread
                    }
                }).done(function (ret) {
                    jQuery("#load-screen").hide();
                    //window.location="/incoming_sms.php";
                    location.reload();
                });				

            }

        });
        

    });

    jQuery(".btn_show_str").click(function () {

        var obj = jQuery(this);
        var job_id = obj.parents("tr:first").find(".job_id").val();

        jQuery("#load-screen").show();
        jQuery.ajax({
            type: "POST",
            url: "/sms/get_job_future_str_action_ajax",
            data: {
                job_id: job_id
            }
        }).done(function (ret) {
            obj.hide();
            obj.parents("tr:first").find(".display_str_div").html(ret);
            jQuery("#load-screen").hide();
        });

    });

    // process
    jQuery(".btn_process").click(function () {

        var obj = jQuery(this);       
        swal({
            title: "Warning!",
            text: "Are you sure you want to continue?",
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
                
                process_sms_replies(obj);					

            }

        });  

    });

    // Insert & Open Job
    jQuery(".insert_n_open_job_link").click(function () {

        var obj = jQuery(this);    

        swal({
            title: "Warning!",
            text: "Are you sure you want to continue?",
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
                
                process_sms_replies(obj);                					

            }else{

                return false;

            }

        });

    });

});
</script>