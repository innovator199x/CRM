
<style>
    .addproperty input, .addproperty select {
        width: 350px;
    }
    .addproperty label {
        width: 230px;
    }
    .tbl_chkbox td{
        text-align: left;
    }

    .tbl_chkbox tr{
        border: none !important;
    }

    .tbl_chkbox tr.tr_last_child{
        border-bottom: medium none !important;
    }
    .chkbox {
        width: auto !important;
    }
    .chk_div{
        float: left;
    }
    .chk_div input, .chk_div span{
        float: left;
    }
    .chk_div input{
        margin-top: 3px;
    }
    .chk_div span{
        margin: 0 5px 0 5px;
    }
    textarea.description{
        height: 79px;
        margin: 0;
        width: 340px;
    }
    input#amount{
        display: inline;
        margin-left: 4px;
        width: 338px;
    }

    table#expense_tbl td, table#expense_tbl th{
        text-align: left;
    }
    .exp_sum_stat_span, .lm_name_span{
        cursor: pointer;
    }
    .exp_sum_status, .line_manager{
        display: none;
    }
    .datepicker {
        z-index:999 !important;
    }
    .approvedHLstatus {
        color: green;
        font-weight: bold;
    }
    .pendingHLstatus {
        color: red;
        font-style: italic;
    }
    .declinedHLstatus {
        color: red;
        font-weight: bold;
    }
    .table-responsive {
        overflow-x: hidden;
    }
    select.exp_sum_status{
        width: 128px;
    }
</style>
<link rel="stylesheet" href="<?Php echo base_url(); ?>inc/js/lib/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" />

<div class="box-typical box-typical-padding">

    <?php
// breadcrumbs template
    $bc_items = array(
        array(
            'title' => 'Reports',
            'link' => "/reports"
        ),
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/reports/view_expense_summary"
        ),
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

    <header class="box-typical-header">
        <div class="box-typical box-typical-padding">
            <div class="form-row">
                <div class="col-9">
                    <form method="GET" action="/reports/view_expense_summary" class="form-row">


                        <div class="col-3">
                            <div class="form-row">
                                <div class="col">
                                    <label>From:</label>
                                    <input autocomplete="off"  type="label" name="from_date" value="<?Php echo $from_date ?>" class="flatpickr form-control flatpickr-input" data-allow-input="true">
                                </div>
                                <div class="col">
                                    <label>To:</label>
                                    <input autocomplete="off" type="label" name="to_date" value="<?Php echo $to_date ?>" class="flatpickr form-control flatpickr-input" data-allow-input="true">
                                </div>
                            </div>

                        </div>

                        <div class="col-mdd-3">
                            <label>Name</label>
                            <select name="employee" class="form-control">
                                <option value="">All</option>
                                <?Php
                                foreach ($employees->result_array() as $emp) {
                                    ?>

                                    <option value="<?php echo $emp['employee']; ?>" <?php echo ($emp['employee'] == $employee) ? 'selected="selected"' : ''; ?>><?php echo $am['agency_name']; ?> <?php echo "{$emp['sa_fname']} {$emp['sa_lname']}" ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-mdd-3">
                            <label>Line Manager:</label>
                            <select name="line_manager_search" class="form-control">
                                <option value="">All</option>
                                <?Php
                                foreach ($staffAccounts as $staff) {
                                    ?>
                                    <option value="<?php echo $staff['staff_accounts_id'] ?>" <?php echo ( $staff['staff_accounts_id'] == $line_manager_search ) ? 'selected="selected"' : ''; ?>><?php echo $staff['FirstName'] . ' ' . $staff['LastName']; ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-mdd-3">
                            <label>With fund source:</label>
                            <select name="with_fund_source" id="with_fund_source" class="form-control">
                                <option value="">All</option>
                                <option <?php echo ($this->input->get_post('with_fund_source') == 1) ? 'selected="selected"' : ''; ?> value="1">Company Card</option>	
                                <option <?php echo ($this->input->get_post('with_fund_source') == 2) ? 'selected="selected"' : ''; ?> value="2">Personal Card</option>
                                <option <?php echo ($this->input->get_post('with_fund_source') == 5) ? 'selected="selected"' : ''; ?> value="5">Cash</option>
                            </select>
                        </div>
                        <div class="col-mdd-3">
                            <label>Status:</label>
                            <select name="filt_sum_status" class="filt_sum_status form-control">
                                <option value="-2">All</option>
                                <option value="-1" <?php echo ($filt_sum_status == -1) ? 'selected="selected"' : ''; ?>>Pending</option>
                                <option value="1" <?php echo ($filt_sum_status == 1) ? 'selected="selected"' : ''; ?>>Approved</option>
                                <option value="0" <?php echo ( is_numeric($filt_sum_status) && $filt_sum_status == 0) ? 'selected="selected"' : ''; ?>>Declined</option>
                            </select>
                        </div>

                        <div class="col-1">
                            <input type="hidden" name="search_flag" value="1" />
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <input  class="btn btn-inline" type="submit" value="Search">
                        </div>

                    </form>   
                </div>
                <div class="col-3">
                    <div class="form-row">
                        <div class="col">
                            <label class="col-sm-12 form-control-label" style="">&nbsp;</label>
                            <a style="float: right;" class="btn btn-danger add-icon-btn" href="/reports/view_add_expense/" role="button">Add Expense</a>
                        </div>
                        <div class="col">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <a style="float:right;" class="btn btn-danger add-icon-btn" 
                            <?Php
                            $href_export_link = "/reports/export_expense_summary?";
                            $params = [
                                "from_date=$from_date",
                                "to_date=$to_date",
//                            "employee=$employee",
//                            "line_manager_search=$line_manager_search",
//                            "filt_sum_status=$filt_sum_status"
                            ];
                            $href_export_link .= join("&", $params);
                            ?>
                               href="<?Php echo $href_export_link ?>" role="button">Export</a>
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
                            <th>Submitted</th>
                            <th>Due to Staff</th>
                            <th>Total Amount</th>
                            <th>Name</th>
                            <th>Entered By</th>
                            <th style="width:221px">Line Manager</th>
                            <th>Who</th>
                            <th>Status</th>
                            <th>PDF</th>
                            <th>Date Processed</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $i = 0;
                        $total = 0;
                        if (count($expense_summary) > 0) {
                            foreach ($expense_summary->result_array() as $exp_sum) {
                                // grey alternation color
                                $row_color = ($i % 2 == 0) ? "" : "style='background-color:#eeeeee;'";
                                $i++;
                                ?>
                                <tr class="body_tr jalign_left" <?php echo $row_color; ?>>

                                    <td>
                                        <input type="hidden" name="expense_summary_id[]" class="exp_sum_id" value="<?php echo $exp_sum['expense_summary_id']; ?>" />
                                        <a href="/reports/view_expense_summary_details/?id=<?php echo $exp_sum['expense_summary_id']; ?>">
                                            <?php echo date('d/m/Y', strtotime($exp_sum['date'])); ?>
                                        </a>
                                    </td>	
                                    <td>
                                        <?php 
                                            $total_due_staff = $this->expensesummary_model->get_expense_claim_total($exp_sum['expense_summary_id']);
                                            echo (!empty($total_due_staff))? "$".$total_due_staff : NULL;
                                         ?>
                                    </td>					
                                    <td >
                                        <?php
                                        $exp_tot = $this->system_model->sumExpense($exp_sum['expense_summary_id']);
                                        echo '$' . number_format($exp_tot, 2, '.', ',');
                                        $total += $exp_tot;
                                        ?>          
                                    </td>

                                    <td>
                                        <?php echo "{$exp_sum['sa_fname']} {$exp_sum['sa_lname']}"; ?>
                                    </td>			
                                    <td>
                                        <?php echo $this->system_model->getEnteredBy($exp_sum['expense_summary_id']); ?>
                                    </td>

                                    <td>
                                        <span class="txt_lbl lm_name_span"><?php echo ($exp_sum['line_manager'] > 0) ? "{$exp_sum['lm_fname']} {$exp_sum['lm_lname']}" : '<span style="color:#5050c2;">Assign</span>'; ?></span>
                                        <select class="line_manager form-control">
                                            <option value="">--- Select ---</option>
                                            <?php
                                            foreach ($staffAccounts as $staff) {
                                                ?>
                                                <option value="<?php echo $staff['staff_accounts_id'] ?>" <?php echo ( $staff['staff_accounts_id'] == $exp_sum['line_manager'] ) ? 'selected="selected"' : ''; ?>><?php echo $staff['FirstName'] . ' ' . $staff['LastName']; ?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                    </td>							
                                    <td><?php echo "{$exp_sum['sa_who_fname']} {$exp_sum['sa_who_lname']}"; ?></td>
                                    <td>
                                        <?php
                                        $exp_sum_status_txt = '';
                                        $exp_sum_status_class = '';
                                        if ((int) $exp_sum['exp_sum_status'] == 1) {
                                            $exp_sum_status_txt = 'Approved';
                                            $exp_sum_status_class = 'approvedHLstatus';
                                        } else if (is_numeric($exp_sum['exp_sum_status']) && $exp_sum['exp_sum_status'] == 0) {
                                            $exp_sum_status_txt = 'Declined';
                                            $exp_sum_status_class = 'declinedHLstatus';
                                        } else {
                                            $exp_sum_status_txt = 'Pending';
                                            $exp_sum_status_class = 'pendingHLstatus';
                                        }
                                        ?>
                                        <span class="txt_lbl exp_sum_stat_span <?php echo $exp_sum_status_class; ?>"><?php echo $exp_sum_status_txt; ?></span>
                                        <select class="exp_sum_status form-control">
                                            <option value="">--- Select ---</option>
                                            <option value="1" <?php echo ( $exp_sum['exp_sum_status'] == 1 ) ? 'selected="selected"' : ''; ?>>Approved</option>
                                            <option value="0" <?php echo ( is_numeric($exp_sum['exp_sum_status']) && $exp_sum['exp_sum_status'] == 0 ) ? 'selected="selected"' : ''; ?>>Declined</option>
                                        </select>
                                    </td>
                                    <td>
                                        <a target="_blank" href="/reports/view_expense_summary_pdf/?exp_sum_id=<?php echo $exp_sum['expense_summary_id']; ?>">
                                            <i style="color:#0082c6" class="fa fa-file-pdf-o"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="javascript:void(0);" class="link_date_reim" <?php echo ($exp_sum['date_reimbursed'] != '') ? 'style="display:none;"' : ''; ?>>+ADD</a>
                                        <input autocomplete="off" class="date_reimbursed flatpickr form-control flatpickr-input" data-allow-input="true" style="<?php echo ($exp_sum['date_reimbursed'] != '') ? '' : 'display:none'; ?>" value="<?php echo ($exp_sum['date_reimbursed'] != '') ? date('d/m/Y', strtotime($exp_sum['date_reimbursed'])) : ''; ?>" />
                                    </td>


                                </tr>

                                <?php
                                $i++;
                            }
                            ?>
                            <tr>
                                <td><strong>TOTAL</strong></td>
                                <td></td>
                                <td><?Php echo '$' . number_format($total, 2, '.', ','); ?></td>
                                <td colspan="8"></td>
                            </tr>
                            <?Php
                        } else {
                            ?>
                        <td colspan="9" align="left">No Data</td>
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
        This page displays statements for a given date range
    </p>
    <pre>
<code>SELECT `exp_sum`.`expense_summary_id`, `exp_sum`.`date`, `exp_sum`.`date_reimbursed`, `exp_sum`.`exp_sum_status` AS `exp_sum_status`, `sa`.`FirstName` AS `sa_fname`, `sa`.`LastName` AS `sa_lname`, `sa_who`.`FirstName` AS `sa_who_fname`, `sa_who`.`LastName` AS `sa_who_lname`, `lm`.`FirstName` AS `lm_fname`, `lm`.`LastName` AS `lm_lname`, `lm`.`StaffId` as `line_manager`
FROM `expense_summary` AS `exp_sum`
LEFT JOIN `staff_accounts` AS `sa` ON exp_sum.`employee` = sa.`StaffID`
LEFT JOIN `staff_accounts` AS `sa_who` ON exp_sum.`who` = sa_who.`StaffID`
LEFT JOIN `staff_accounts` AS `lm` ON exp_sum.`line_manager` = lm.`StaffID`
LEFT JOIN `expenses` AS `exp` ON exp.`expense_summary_id` = exp_sum.`expense_summary_id`
WHERE `exp_sum`.`active` = 1
AND `exp_sum`.`deleted` =0
AND `exp_sum`.`country_id` = <?php echo COUNTRY ?>
AND  (`exp_sum`.`exp_sum_status` IS NULL )
GROUP BY `exp_sum`.`expense_summary_id`
ORDER BY `exp_sum`.`date` DESC
LIMIT 50</code>
    </pre>

</div>
<!-- Fancybox END -->

<script src="<?Php echo base_url(); ?>inc/js/lib/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script>
    jQuery(document).ready(function () {



        jQuery(".line_manager").change(function () {

            var line_manager = jQuery(this).val();
            var exp_sum_id = jQuery(this).parents("tr:first").find(".exp_sum_id").val();
            if (exp_sum_id != '') {

                jQuery.ajax({
                    type: "POST",
                    url: "/reports/update_exp_summary_action_ajax",
                    data: {
                        exp_sum_id: exp_sum_id,
                        line_manager: line_manager,
                        action: "update_exp_sum_line_manager"
                    }
                }).done(function (ret) {
                    console.log(ret);
                    location.reload();
                });
            }

        });
        jQuery(".exp_sum_status").change(function () {

            var exp_sum_status = jQuery(this).val();
            var exp_sum_id = jQuery(this).parents("tr:first").find(".exp_sum_id").val();

            if (exp_sum_id != '') {

                jQuery.ajax({
                    type: "POST",
                    url: "/reports/update_exp_summary_action_ajax",
                    data: {
                        exp_sum_id: exp_sum_id,
                        exp_sum_status: exp_sum_status,
                        action: "update_exp_sum_status"
                    }
                }).done(function (ret) {
                    console.log(ret);
                    location.reload();
                });
            }

        });
        jQuery(".lm_name_span").click(function () {

            jQuery(this).hide();
            jQuery(this).parents("tr:first").find(".line_manager").show();
        });
        jQuery(".exp_sum_stat_span").click(function () {

            jQuery(this).hide();
            jQuery(this).parents("tr:first").find(".exp_sum_status").show();
        });
        jQuery(".link_date_reim").click(function () {

            jQuery(this).hide();
            jQuery(this).parents("tr:first").find(".date_reimbursed").show();
        });
        jQuery(".date_reimbursed").change(function () {

            var date_reimbursed = jQuery(this).val();
            var exp_sum_id = jQuery(this).parents("tr:first").find(".exp_sum_id").val();
            if (date_reimbursed != '') {

                jQuery.ajax({
                    type: "POST",
                    url: "/reports/update_exp_summary_action_ajax",
                    data: {
                        exp_sum_id: exp_sum_id,
                        date_reimbursed: date_reimbursed,
                        action: "update_date_reimbursed"
                    }
                }).done(function (ret) {
                    console.log(ret);
                    location.reload();
                });
            }

        });
    });</script>
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