
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
    .edit_expense_modal_trigger {
        display: none
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
            'title' => 'Expense Summary',
            'link' => "/reports/view_expense_summary/"
        ),
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/reports/view_expense_summary_details/?id={$this->input->get('id')}"
        ),
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>
    <section>
        <div class="body-typical-body">
            <div class="table-responsive">
                <table class="table table-hover main-table table-striped">
                    <thead>
                        <tr>
                            <th>Date of Purchase</th>
                            <th>Card Used</th>
                            <th>Supplier</th>
                            <th>Description</th>
                            <th>Account</th>
                            <th>Entered By</th>
                            <th>Gross Amt</th>
                            <th>GST</th>
                            <th>Net Amt</th>
                            <th>Image</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $i = 0;

                        if (count($expenses->result_array()) > 0) {
                            $total = 0;
                            foreach ($expenses->result_array() as $exp) {
                                // grey alternation color
                                $total += $exp['amount'];
                                $row_color = ($i % 2 == 0) ? "" : "style='background-color:#eeeeee;'";
                                $i++;
                                ?>
                                <tr class="body_tr jalign_left" <?php echo $row_color; ?>>
                                    <td>
                                        <a href="#" class="show-edit-expense-modal"
                                           data-expense_id="<?Php echo $exp['expense_id']; ?>"
                                           data-expense_summary_id="<?Php echo $exp['expense_summary_id']; ?>"
                                           data-date="<?Php echo date('m/d/Y', strtotime($exp['date'])); ?>"
                                           data-card_id="<?Php echo $exp['card']; ?>"
                                           data-employee="<?Php echo $exp['emp_staff_id']; ?>"
                                           data-supplier="<?Php echo $exp['supplier']; ?>"
                                           data-description="<?Php echo $exp['description']; ?>"
                                           data-expense_account_id="<?Php echo $exp['expense_account_id']; ?>"
                                           data-account_name="<?Php echo $exp['account_name']; ?>"
                                           data-entered_by="<?Php echo "{$exp['eb_fname']} {$exp['eb_lname']}"; ?>"
                                           data-amount="<?Php echo $exp['amount']; ?>"
                                           data-upload_file="<?Php echo $exp['receipt_image']; ?>"
                                           ><?Php echo date('d/m/Y', strtotime($exp['date'])); ?></a>
                                           <?Php
//                                           echo $this->gherxlib->crmLink('expense_details', $exp['expense_id'], date('d/m/Y', strtotime($exp['date'])));
                                           ?>
                                    </td>
                                    <td><?Php echo $this->expensesummary_model->getExpenseCards($exp['card']) ?></td>
                                    <td><?Php echo $exp['supplier'] ?></td>
                                    <td><?Php echo $exp['description'] ?></td>
                                    <td><?Php echo $exp['account_name'] ?></td>
                                    <td><?Php echo "{$exp['eb_fname']} {$exp['eb_lname']}" ?></td>
                                    <td>$<?Php echo $exp['amount'] ?></td>
                                    <?Php
                                    // get dynamic GST based on country
                                    $gst = $this->expensesummary_model->getDynamicGST($exp['amount'], $this->config->item('country'));
                                    $net_amount = $exp['amount'] - $gst;
                                    ?>
                                    <td>$<?Php echo number_format($gst, 2) ?></td>
                                    <td>$<?Php echo number_format($net_amount, 2) ?></td>
                                    <td>

                                        <?Php
                                        $file = pathinfo($exp['receipt_image']);
                                        $filename = str_replace([" ", "."], "_", $file['filename']);
                                        $file_path = $file['dirname'] . "/" . $filename . "." . $file['extension'];
                                        if (file_exists(FCPATH . $file_path)) {
                                            echo '<a target="_blank" href="/' . $file_path . '">' . '<img style="width:24px;" src="/images/' . (($exp["file_type"] == 'image') ? 'file-img.png' : 'pdf.png') . '" />' . '</a>';
                                        } else {
                                            echo $this->gherxlib->crmLink('uploads_expenses', $exp['receipt_image'], '<img style="width:24px;" src="/images/' . (($exp["file_type"] == 'image') ? 'file-img.png' : 'pdf.png') . '" />');
                                        }
                                        ?>
                                    </td>


                                </tr>

                                <?php
                                $i++;
                            }
                            $gst = $this->expensesummary_model->getDynamicGST($total, $this->config->item('country'));
                            $total_net_amount = $total - $gst;
                            ?>
                            <tr style="font-weight: bolder">
                                <td colspan="5"></td>
                                <td>Total</td>
                                <td>$<?Php echo number_format($total, 2) ?></td>
                                <td>$<?Php echo number_format($gst, 2) ?></td>
                                <td>$<?Php echo number_format($total_net_amount, 2) ?></td>
                                <td>
                                    <a href="/reports/view_expense_summary_details/?id=<?php echo $this->input->get_post('id'); ?>&bulk_download=1">Bulk Download</a>
                                </td>
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
        This page displays list of expenses for the selected statement
    </p>

</div>

<a href="javascript:;" id="edit_expense_modal_trigger" class="edit_expense_modal_trigger" data-fancybox data-src="#edit_expense_modal">Trigger the Add Expense Modal</a>							
<div id="edit_expense_modal" class="fancybox" style="display:none;" >
    <h4>Edit Expense Form</h4>
    <div class="col-md-12" style="border: 1px solid #dee2e6;padding-top: 5px; padding-bottom:5px;">
        <form id="jform" enctype="multipart/form-data" action="/reports/update_expense_action_form_submit" method="POST">
            <div class="form-row">
                <div class="col-md-6">
                    <label class="addlabel">Name</label>


                    <input type="text" readonly="readonly" class="addinput form-control employe_name" name="employe_name" id="employe_name" value="" />
                    <input type="hidden" id="employee" name="employee" value="" />
                    <input type="hidden" id="expense_id" name="expense_id" value="" />
                    <input type="hidden" id="expense_summary_id" name="expense_summary_id" value="" />
                    <input type="hidden" id="redirect_url" name="redirect_url" value="<?Php echo base_url(uri_string()) . "/?id=" . $exp['expense_summary_id']; ?>" />


                </div>

                <div class="col-md-6">
                    <label class="addlabel">Date of Purchase</label>
                    <input type="text"  class="addinput flatpickr form-control flatpickr-input datepicker" name="date" id="date" />
                </div>


                <div class="col-md-6">
                    <label class="addlabel">Card Used</label>
                    <select class="form-control" name="card" id="card">
                        <option value="1">Company Card</option>	
                        <option value="2">Personal Card</option>
                        <option value="3">AU Main Card</option>
                        <option value="4">NZ Main Card</option>
                        <option value="5">Cash</option>
                    </select>
                </div>


                <div class="col-md-6">
                    <label class="addlabel">Supplier</label>
                    <input type="text"  class="addinput form-control supplier" name="supplier" id="supplier" />
                </div>

                <div class="col-12">
                    <label class="addlabel">Description</label>
                    <input type="text"  class="addinput form-control description" name="description" id="description" placeholder="Eg. Lunch whilst away in Dubbo" />
                </div>

                <div class="col-md-6">
                    <label class="addlabel">Account</label>
                    <select class="form-control" name="account" id="account">
                        <option value="">----</option>
                        <?php
                        foreach ($accounts as $supp) {
                            ?>
                            <option value="<?php echo $supp['expense_account_id'] ?>"><?php echo $supp['account_name']; ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>


                <div class="col-md-6">
                    <label class="addlabel">Amount</label>
                    <div class="input-group">

                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="text" class="addinput form-control amount" id="amount" name="amount">
                        <input type="hidden" class="addinput form-control amount_readonly" id="amount_readonly" name="amount_readonly">
                    </div>
                </div>


                <div class="col-md-12">
                    <label class="addlabel">Receipt</label>
                    <div><small>(Current File: <span class="model_current_file"></span>)</small></div>
                    <div>
                        <input type="file" capture="camera" name="receipt_image" id="receipt_image" class="addinput form-control receipt" />
                    </div>
                </div>
                <div class="col-12">
                    <label class="addlabel">Entered By</label>
                    <input type="text" readonly="readonly" class="addinput form-control description" name="entered_by" id="entered_by"/>
                </div>
                <div class="col-md-12">
                    <label class="addlabel">&nbsp;</label>
                    <button class="submitbtnImg btn right" id="btn_submit" type="submit" style="">
                        Update
                    </button>
                    <button class="submitbtnImg btn btn-danger left" name="delete" value="delete" id="btn_submit" type="submit">
                        Delete
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
        $(".show-edit-expense-modal").click(function () {
            jQuery("#edit_expense_modal_trigger").click();
            var data = $(this).data();
            $('#edit_expense_modal #employe_name').val(data.entered_by);
            $('#edit_expense_modal #employee').val(data.employee);
            $('#edit_expense_modal #expense_id').val(data.expense_id);
            $('#edit_expense_modal #expense_summary_id').val(data.expense_summary_id);
            var date_fp = flatpickr('#edit_expense_modal #date', {dateFormat: "d/m/Y"});
            date_fp.setDate(new Date(data.date), true);
            $('#edit_expense_modal #card').val(data.card_id);
            $('#edit_expense_modal #supplier').val(data.supplier);
            $('#edit_expense_modal #description').val(data.description);
            $('#edit_expense_modal #account').val(data.expense_account_id);
            $('#edit_expense_modal #amount').val(data.amount);
            $('#edit_expense_modal #amount_readonly').val(data.amount);
            $('#edit_expense_modal #entered_by').val(data.entered_by);
            $('#edit_expense_modal .model_current_file').html(data.upload_file);
            return false;
        });


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
                    window.location = "/reports/view_expense_summary";
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
                    window.location = "/reports/view_expense_summary";
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
                    window.location = "/reports/view_expense_summary";
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