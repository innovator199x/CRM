<div class="box-typical box-typical-padding">

    <?php
    // breadcrumbs template
    $bc_items = array(
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/sms/view_job_feedback_sms"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

    <header class="box-typical-header">
        <div class="box-typical box-typical-padding">
            <div class="form-row">

                <div class="col-md-10">
                    <form method=POST action="/sms/view_job_feedback_sms" class="form-row">


                        <div class="mr-3">
                            <label>From:</label>
                            <input autocomplete="off"  type="label" name="from_date" value="<?php echo $from_date; ?>" class="flatpickr form-control flatpickr-input">
                        </div>
                        <div class="mr-3">
                            <label>To:</label>
                            <input autocomplete="off" type="label" name="to_date" value="<?php echo $to_date; ?>" class="flatpickr form-control flatpickr-input">
                        </div>
                 
                        <div class="mr-3">
                            <label>Tech</label>
                            <select name="tech" class="form-control">
                                <option value="">ALL</option>
                                <?Php
                                $tech = $this->input->get_post('tech');
                                foreach ($tech_list->result_array() as $row) {
                                    ?>
                                    <option value="<?php echo $row['assigned_tech']; ?>" <?php echo ( $row['assigned_tech'] == $tech ) ? 'selected="selected"' : ''; ?>><?php echo $this->system_model->formatStaffName($row['FirstName'], $row['LastName']); ?></option>
                                    <?Php
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mr-3">
                            <input type="hidden" name="search_flag" value="1" />
                            <label>&nbsp;</label>
                            <input class="btn btn-inline" type="submit" value="Search">
                        </div>
                    </form>
                </div>

                <!-- DL ICONS START --> 
			    <div class="col-lg-2 col-md-12 columns">
                    <section class="proj-page-section float-right">
                        <div class="proj-page-attach">
                            <i class="fa fa-file-excel-o"></i>
                            <p class="name"><?php echo $title; ?></p>
                            <p>
								<a href="<?php echo $export_link; ?>" target="blank">
									Export
								</a>
                            </p>
                        </div>
                    </section>
				</div>
				<!-- DL ICONS END -->

            </div>
    </header>
    <section>
        <div class="body-typical-body">
            <div class="table-responsive">

                <table class="table table-hover main-table jmenu_table">

                    <thead>
                        <tr>                            
                            <th>Job ID</th>
                            <th>Tenant</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Message</th>
                            <th>Technician</th>
                            <th style="text-align: center;">GR SMS Sent</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        if( $list->num_rows() > 0 ){

                            foreach ($list->result_array() as $cr) {                            
                                ?>
                                <tr>                                
                                    <td>
                                        <a href="<?php echo $this->config->item('crm_link'); ?>/view_job_details.php?id=<?php echo $cr['job_id']; ?>">
                                            <?php echo $cr['job_id']; ?>
                                        </a>
                                    </td>
                                    <td>
                                    <?php
                                        $mob_num = '0'.substr($cr['sar_mobile'],2);
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
                                        ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($cr['sar_created_date'])); ?></td>
                                    <td><?php echo date('H:i', strtotime($cr['sar_created_date'])); ?></td>
                                    <td><?php echo $cr['response']; ?></td>
                                    <td>
                                       <?Php echo "{$cr['at_FirstName']} {$cr['at_LastName']}" ?>
                                    </td>
                                    <td style="text-align: center;"><input type="checkbox" <?php if($cr['sms_replied_to'] == 1){ echo 'checked';} ?> value="<?php echo $cr['sms_api_replies_id']; ?>" onchange="sms_status(this.value)"></td>
                                </tr>
                                <?php
                            }

                        }else{ ?>
                            <tr><td colspan="100%">No Results</td></tr>
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
       This page displays SMS replies about our Service
    </p>
    <pre><code><?php echo $last_query; ?></code></pre>

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
</style>
<script>

    function sms_status(value){
        // alert(value);
        jQuery("#load-screen").show();
        jQuery.ajax({
            type: "POST",
            url: "/sms/sms_replied_to_update",
            data: {
                sms_api_replies_id: value
            }
        }).done(function (ret) {
            jQuery("#load-screen").hide();
            //window.location="/incoming_sms.php";
            // location.reload();
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

            if (confirm("Are you sure you want to continue?")) {

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

        jQuery(".btn_process").click(function () {

            var obj = jQuery(this);
            var job_id = obj.parents("tr:first").find(".job_id").val();
            var sar_id = obj.parents("tr:first").find(".sar_id").val();
            var sas_id = obj.parents("tr:first").find(".sas_id").val();
            var message_id = obj.parents("tr:first").find(".message_id").val();
            var tenant_name = obj.parents("tr:first").find(".tenant_name").val();
            var reply_msg = obj.parents("tr:first").find(".reply_msg").val();
            var btn_type = obj.attr("data-btn_type");
            var sms_type = obj.parents("tr:first").find(".sms_type").val();

            //console.log("button type: "+btn_type);

            if (confirm("Are you sure you want to continue?")) {

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
                    //window.location="/incoming_sms.php";
                    //location.reload();
                });

                // disable button to prevent double processing
                obj.parents("tr:first").find(".btn_insert_n_open_job").addClass('fadeIt');
                obj.parents("tr:first").find(".btn_insert_n_open_job").attr('disabled', 'disabled');
                obj.parents("tr:first").find(".btn_process").addClass('fadeIt');
                obj.parents("tr:first").find(".btn_process").attr('disabled', 'disabled');

            }

        });

    });
</script>