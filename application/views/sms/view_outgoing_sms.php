<div class="box-typical box-typical-padding">

    <?php
    // breadcrumbs template
    $bc_items = array(
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/sms/view_outgoing_sms"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

    <header class="box-typical-header">
        <div class="box-typical box-typical-padding">
            <div class="form-row">
                <div class="col-md-8">
                    <form method=POST action="/sms/view_outgoing_sms" class="form-row">


                        <div class="col-md-4">
                            <div class="form-row">
                                <div class="col">
                                    <label>From:</label>
                                    <input autocomplete="off"  type="label" name="from_date" value="<?php echo $from_date; ?>" class="flatpickr form-control flatpickr-input">
                                </div>
                                <div class="col">
                                    <label>To:</label>
                                    <input autocomplete="off" type="label" name="to_date" value="<?php echo $to_date; ?>" class="flatpickr form-control flatpickr-input">
                                </div>
                            </div>

                        </div>

                        <div class="col-md-4">
                            <label>SMS Type</label>
                            <select name="sms_type" class="form-control">
                                <option value="">ALL</option>
                                <?Php
                                foreach ($sat_list->result_array() as $sat) {
                                    ?>

                                    <option value="<?php echo $sat['sms_api_type_id']; ?>" <?php echo ($sat['sms_api_type_id'] == $sms_type) ? 'selected="selected"' : ''; ?>><?php echo $sat['type_name']; ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Status</label>
                            <select name="cb_status" class="form-control">
                                <option value="">ALL</option>
                                <option value="pending" <?php echo ("pending" == $cb_status) ? 'selected="selected"' : ''; ?>>Pending</option>
                                <option value="delivered" <?php echo ("delivered" == $cb_status) ? 'selected="selected"' : ''; ?>>Delivered</option>
                                <option value="hard-bounce" <?php echo ("hard-bounce" == $cb_status) ? 'selected="selected"' : ''; ?>>Hard-bounce</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <input type="hidden" name="search_flag" value="1" />
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <input  class="btn btn-inline" type="submit" value="Search">
                        </div>

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
                    </div>
                </div>
                <div class="col-md-1">
                    <label class="col-sm-12 form-control-label">&nbsp;</label>
                    <a href="/sms/update_sms_credits_action_form_submit/?redirect=outgoing_sms">
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
                            <th>Sent By</th>
                            <th>SMS Type</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>To</th>
                            <th>Tenant</th>
                            <th>Message</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        foreach ($list->result_array() as $cr) {
                            $sent_by_name = ( $cr['sent_by'] == -3 ) ? "CRM" : "{$cr['FirstName']} {$cr['LastName']}";
                            ?>
                            <tr>						
                                <td><?php echo $sent_by_name; ?></td>
                                <td><?php echo $cr['type_name']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($cr['sas_created_date'])); ?></td>
                                <td><?php echo date('H:i', strtotime($cr['sas_created_date'])); ?></td>
                                <td class="mob_td"><?php echo $mob_num = '0' . substr($cr['sas_mobile'], 3); ?></td>	
                                <td>
                                    <?php
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
                                <td><?php echo $cr['message']; ?></td>
                                <td>
                                    <?php
                                    $status = "";
                                    $cb_status = $cr['cb_status'];
                                    if ($cb_status === "delivered") {
                                        $status = '<span style="color:green;text-transform:capitalize">' . $cb_status . '</span>';
                                    } else if ($cb_status === 'pending' || $cb_status === 'hard-bounce') {
                                        $status = '<span style="color:red;text-transform:capitalize">' . $cb_status . '</span>';
                                    }
                                    echo $status;
                                    ?>
                                    <input type="hidden" class="job_id" value="<?php echo $cr['job_id']; ?>" />
                                    <input type="hidden" class="sas_id" value="<?php echo $cr['sms_api_sent_id']; ?>" />
                                    <input type="hidden" class="tenant_name" value="<?php echo $tenant_name; ?>" />
                                    <input type="hidden" class="reply_msg" value="<?php echo $cr['response']; ?>" />
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
        This page shows all outgoing SMS
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

    });
</script>