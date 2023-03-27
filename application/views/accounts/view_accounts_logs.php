
<style>
    .col-mdd-3{
        max-width:15.5%;
    }
    .action_div{
        display: none;
    }
    .being-removed{
        background: #721c24;
    }
    .txl_lbl img, .txt_hid img{
        max-width: 100%;
    }
    div.datepicker {
        z-index:9999 !important;
    }
    .colorItRed {
        color:red;
    }
    .border-right {
        border-right: 1px solid #dee2e6;
    }
</style>
<link rel="stylesheet" href="<?Php echo base_url(); ?>inc/js/lib/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" />

<div class="box-typical box-typical-padding">

    <?php
// breadcrumbs template
    $bc_items = array(
        array(
            'title' => "Accounts Logs",
            'status' => 'active',
            'link' => "/accounts/view_account_logs"
        ),
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

    <header class="box-typical-header">
        <div class="box-typical box-typical-padding">
            <div class="form-row">
                <div class="col-10">
                    <form method=POST action="/accounts/view_account_logs" class="form-row">


                        <div class="col">
                            <div class="form-row">
                                <div class="col">
                                    <label>From:</label>
                                    <input autocomplete="off"  type="label" name="dateFrom" value="<?Php echo $dateFrom ?>" class="flatpickr form-control flatpickr-input">
                                </div>
                                <div class="col">
                                    <label>To:</label>
                                    <input autocomplete="off" type="label" name="dateTo" value="<?Php echo $dateTo ?>" class="flatpickr form-control flatpickr-input">
                                </div>
                            </div>

                        </div>

                        <div class="col">
                            <label>Staff Member</label>
                            <select name="staff" class="form-control">
                                <option value="">ALL</option> 
                                <?Php
                                foreach ($staff_sql as $staffList) {
                                    ?>

                                    <option value="<?php echo $staffList['staff_id']; ?>" <?php echo ($staffList['staff_id'] == $staff) ? 'selected="selected"' : ''; ?>><?php echo $staffList['FirstName'] . " " . $staffList['LastName'] ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col">
                            <label>Agency</label><?Php // echo $this->session->staff_id; ?>
                            <select name="agency" class="form-control">
                                <option value="">ANY</option> 
                                <?Php
                                foreach ($agen_sql as $agen) {
                                    ?>

                                    <option value="<?php echo $agen['agency_id']; ?>" <?php echo ($agen['agency_id'] == $agency) ? 'selected="selected"' : ''; ?>><?php echo $agen['agency_name']; ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col">
                            <input type="hidden" name="search_flag" value="1" />
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <input  class="btn btn-inline" type="submit" value="Search">
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
                            <th>Date</th>
                            <th>Contact Type</th>
                            <th>Comments</th>
                            <th>Staff Member</th>
                            <th>Agency</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $i = 0;

                        if (count($logs) > 0) {
                            foreach ($logs as $row) {
                                ?>
                                <tr>
                                    <td><?php echo ($this->system_model->isDateNotEmpty($row['eventdate']) == true) ? $this->system_model->formatDate($row['eventdate'], 'd/m/Y') : ''; ?></td>						
                                    <td ><?php echo $row['contact_type']; ?></td>
                                    <td ><?php echo $row['comments']; ?></td>
                                    <td ><?php echo "{$row['FirstName']} {$row['LastName']}" ?></td>
                                    <td ><?php 
                                    echo $this->gherxlib->crmLink('vad', $row['agency_id'], $row['agency_name']);
//                                    echo $row['agency_name']; 
                                    ?></td>
                                </tr>

                                <?php
                                $i++;
                            }
                        } else {
                            ?>
                        <td colspan="5" align="left">Select Agency to display data</td>
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
        This page displays accounts related logs that can be filtered by Date, Agency, and Staff
    </p>
    <pre>
<code>SELECT `ael`.`date` as `eventdate`, `ael`.`comment` as `comments`, `ael`.`id` as `agency_event_log_id`, `sa`.`FirstName`, `sa`.`LastName`, `ael`.`next_contact`, `a`.`agency_name`, `a`.`agency_id`, `mlt`.`contact_type`
FROM `sales_report` AS `ael`
LEFT JOIN `staff_accounts` AS `sa` ON ael.`staff_id` = sa.`StaffID`
LEFT JOIN `agency` AS `a` ON ael.`agency_id` = a.`agency_id`
LEFT JOIN `main_log_type` AS `mlt` ON `mlt`.`main_log_type_id` = `ael`.`contact_type`
WHERE (`ael`.`contact_type` = 5 OR `ael`.`contact_type` = 11 OR `ael`.`contact_type` = 14)
ORDER BY `ael`.`date` DESC
LIMIT 50</code>
    </pre>

</div>
<!-- Fancybox END -->

<script src="<?Php echo base_url(); ?>inc/js/lib/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script>
    jQuery(document).ready(function () {

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