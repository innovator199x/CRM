
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
    .line_manager_div{
        display: none;
    }
    .add_expense_modal_trigger {
        display: none
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
            'link' => "/reports/view_expense_summary"
        ),
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/reports/view_add_expense"
        ),
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>
    <form class="col-12" id="submit_expense_form" method="POST" action="/reports/add_expense_summary_action_form_submit">
        <header class="box-typical-header">
            <div class="box-typical box-typical-padding">
                <div class="form-row">
                    <div class="col-12">
                        <div class="form-row">
                            <div class="col-md-2">
                                <label class="col-sm-12 form-control-label" style="">&nbsp;</label>
                                <a id="btn-show-add-expense-modal" style="cursor: pointer" class="btn btn-danger add-icon-btn btn-inline" role="button">Add Expense</a>
                            </div>
                            <div class="col-md-4 offset-md-6">
                                <div class="row">
                                    <?Php
                                    if (count($exp_sql) > 0) {
                                        ?>
                                        <div class="col-md-4 ">
                                            <div class="row line_manager_div">
                                                <label class="lm_lbl">Line Manager</label>
                                                <select class="form-control" name="line_manager" id="line_manager" style="float: right;">
                                                    <option value="">--- Select ---</option>
                                                    <?php
                                                    foreach ($staff_accounts as $staff) {
                                                        ?>
                                                        <option value="<?php echo $staff['staff_accounts_id'] ?>"><?php echo $staff['FirstName'] . ' ' . $staff['LastName']; ?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="col-sm-12 form-control-label" style="">&nbsp;</label>
                                            <button class="btn-inline btn" id="btn_submit_expense" type="button">
                                                <span class="btn_submit_expense_span">Confirm</span>
                                            </button>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="col-sm-12 form-control-label" style="">&nbsp;</label>
                                            <button type="button" class="btn-inline btn" id="btn_clear">
                                                Clear
                                            </button>
                                        </div>
                                        <?Php
                                    }
                                    ?>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </header>
        <section>
            <div class="body-typical-body">
                <div class="form-row">

                    <div class="col-12" style=""><div class="table-responsive">
                            <?Php
//                var_dump($expenses->result_array());
                            ?>
                            <table class="table table-hover main-table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date of Purchase</th>
                                        <th>Card Used</th>
                                        <th>Supplier</th>
                                        <th>Description</th>
                                        <th>Account</th>
                                        <th>Entered By</th>
                                        <th>Amount</th>
                                        <th>Net Amt</th>
                                        <th>GST</th>
                                        <th>Gross Amt</th>
                                        <th>Image</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php
                                    $i = 0;
                                    $amount_tot = 0;
                                    if (count($exp_sql) > 0) {
                                        foreach ($exp_sql as $exp) {
                                            // grey alternation color
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
                                                       ><?Php echo date('d/m/Y', strtotime($exp['date'])); ?></a>
                                                    <input type="hidden" name="expense_id[]" value="<?php echo $exp['expense_id']; ?>" />

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
                                                $amount_tot += $exp['amount'];
                                                ?>
                                                <td>$<?Php echo number_format($net_amount, 2) ?></td>
                                                <td>$<?Php echo number_format($gst, 2) ?></td>
                                                <td>$<?Php echo $exp['amount'] ?></td>
                                                <td>
                                                    <?Php
                                                    $file_path = str_replace(" ", "_", $exp['receipt_image']);
                                                    if (file_exists(FCPATH . $file_path)) {
                                                        echo '<a target="_blank" href="/' . $file_path . '">' . '<img style="width:24px;" src="/images/' . (($exp["file_type"] == 'image') ? 'file-img.png' : 'pdf.png') . '" />' . '</a>';
                                                    } else {
                                                        echo $this->gherxlib->crmLink('uploads_expenses', $exp['receipt_image'], '<img style="width:24px;" src="/images/' . (($exp["file_type"] == 'image') ? 'file-img.png' : 'pdf.png') . '" />');
                                                    }
//                                                    echo $this->gherxlib->crmLink('uploads_expenses', $exp['receipt_image'], '<img style="width:24px;" src="/images/' . (($exp["file_type"] == 'image') ? 'file-img.png' : 'pdf.png') . '" />');
                                                    ?>
                                                </td>


                                            </tr>

                                            <?php
                                        }
                                        $i++;
                                        $gst = $this->expensesummary_model->getDynamicGST($amount_tot, $this->config->item('country'));
                                        $total_net_amount = $amount_tot - $gst;
                                        ?>
                                        <tr style="font-weight: bolder">
                                            <td colspan="5"></td>
                                            <td>Total</td>
                                            <td>$<?Php echo number_format($amount_tot, 2) ?></td>
                                            <td>$<?Php echo number_format($total_net_amount, 2) ?></td>
                                            <td>$<?Php echo number_format($gst, 2) ?></td>
                                            <td>$<?Php echo number_format($amount_tot, 2) ?></td>
                                            <td></td>
                                        </tr>
                                        <?Php
                                    } else {
                                        ?>
                                    <td colspan="11" align="left">No Data</td>
                                    <?php
                                }
                                ?>

                                </tbody>

                            </table>
                        </div></div>
                </div>

            </div>
        </section>
        <input type="hidden" name="employee" value="<?php echo $exp_user[0]['emp_staff_id']; ?>" />
        <input type="hidden" name="total_amount" value="<?php echo $amount_tot; ?>" />
    </form>
</div>

<!--Fancybox Start--> 
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

    <h4><?php echo $title; ?></h4>
    <p>
        This page displays list of expenses for the selected statement
    </p>
    <pre><code><?php echo $last_query; ?></code></pre>

</div>
<a href="javascript:;" id="add_expense_modal_trigger" class="add_expense_modal_trigger" data-fancybox data-src="#add_expense_modal">Trigger the Add Expense Modal</a>							
<div id="add_expense_modal" class="fancybox" style="display:none;" >
    <h4>Add Expense Form</h4>
    <div class="col-md-12" style="border: 1px solid #dee2e6;padding-top: 5px; padding-bottom:5px;">
        <form id="jform" enctype="multipart/form-data" action="/reports/add_expense_action_form_submit" method="POST">
            <div class="form-row">
                <div class="col-md-6">
                    <label class="addlabel">Name</label>
                    <?php
                    if (count($exp_user) > 0) {
                        ?>

                        <input type="text" readonly="readonly" class="addinput form-control employe_name" name="employe_name" id="employe_name" value="<?php echo "{$exp_user[0]['emp_fname']} {$exp_user[0]['emp_lname']}"; ?>" />
                        <input type="hidden" id="employee" name="employee" value="<?php echo $exp_user[0]['emp_staff_id']; ?>" />

                    <?php } else {
                        ?>

                        <select class="form-control" name="employee" id="employee">
                            <option value="">----</option>
                            <?php
                            foreach ($staff_accounts as $sa) {
//                                var_dump($sa['staff_accounts_id'],$loggedin_staff_id,$sa['staff_accounts_id'] == $loggedin_staff_id);
                                ?>
                                <option value="<?php echo $sa['staff_accounts_id'] ?>" <?php echo ($sa['staff_accounts_id'] == $loggedin_staff_id) ? 'selected="selected"' : ''; ?>><?php echo "{$sa['FirstName']} {$sa['LastName']}"; ?></option>
                                <?php
                            }
                            ?>
                        </select>

                        <?php
                    }
                    ?>

                </div>

                <div class="col-md-6">
                    <label class="addlabel">Date of Purchase</label>
                    <input type="text"  class="addinput flatpickr form-control flatpickr-input datepicker" name="date" id="date" value="<?php echo date("d/m/Y"); ?>" />
                </div>


                <div class="col-md-6">
                    <label class="addlabel">Card Used</label>
                    <select class="form-control" name="card" id="card">
                        <option value="1">Company Card</option>	
                        <option value="2">Personal Card</option>
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
                        <input type="number" step=".01" class="addinput form-control amount" id="amount" name="amount">
                    </div>
                </div>


                <div class="col-md-12">
                    <label class="addlabel">Receipt</label>
                    <div>
                        <input type="file" capture="camera" name="receipt_image" id="receipt_image" class="addinput form-control receipt" />
                    </div>
                </div>
                <div class="col-md-12">
                    <label class="addlabel">&nbsp;</label>
                    <div>
                        <input type="checkbox" class="addinput" name="confirm_chk" id="confirm_chk" style="width:auto; float: left;" value="1" />
                        <span style="margin: 5px 0 0 10px;">
                            I declare that the attached purchase was made in line with company policies and was an expense incurred as part of me performing my role
                        </span>
                    </div>
                </div>
                <div class="col-md-12">
                    <label class="addlabel">&nbsp;</label>
                    <button class="submitbtnImg btn" id="btn_submit" type="submit" style="float: right;">
                        Submit
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>


<a href="javascript:;" id="edit_expense_modal_trigger" class="edit_expense_modal_trigger" data-fancybox data-src="#edit_expense_modal">Trigger the Add Expense Modal</a>							
<div id="edit_expense_modal" class="fancybox" style="display:none;" >
    <h4>Edit Expense Form</h4>
    <div class="col-md-12" style="border: 1px solid #dee2e6;padding-top: 5px; padding-bottom:5px;">
        <form id="jform1" enctype="multipart/form-data" action="/reports/update_expense_action_form_submit" method="POST">
            <div class="form-row">
                <div class="col-md-6">
                    <label class="addlabel">Name</label>


                    <input type="text" readonly="readonly" class="addinput form-control employe_name" name="employe_name" id="employe_name" value="" />
                    <input type="hidden" id="employee" name="employee" value="" />
                    <input type="hidden" id="expense_id" name="expense_id" value="" />
                    <input type="hidden" id="expense_summary_id" name="expense_summary_id" value="" />
                    <input type="hidden" id="redirect_url" name="redirect_url" value="<?Php echo base_url(uri_string()); ?>" />


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
                        <input type="number" step=".01" class="addinput form-control amount" id="amount" name="amount">
                    </div>
                </div>


                <div class="col-md-12">
                    <label class="addlabel">Receipt</label>
                    <div>
                        <input type="file" capture="camera" name="receipt_image" id="receipt_image" class="addinput form-control receipt" />
                    </div>
                </div>
                <div class="col-12">
                    <label class="addlabel">Entered By</label>
                    <input type="text" readonly="readonly" class="addinput form-control description" name="entered_by" id="entered_by"/>
                </div>
                <div class="col-md-3 offset-9">
                    <label class="addlabel">&nbsp;</label>
                    <button class="submitbtnImg btn" id="btn_submit" type="submit" style="">
                        Update
                    </button>
                    <button class="submitbtnImg btn btn-danger" name="delete" value="delete" id="btn_submit" type="submit" style="float: right;">
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
            $('#edit_expense_modal #entered_by').val(data.entered_by);
            return false;
        });
// form validation
        jQuery("#jform").submit(function (e) {
            e.preventDefault();
            var form = this;
            var error = "";

            // Leave Request Form
            var employee = $(this).find('#employee').val();
            var date = $(this).find("#date").val();
            var supplier = $(this).find("#supplier").val();
            var description = $(this).find("#description").val();
            var account = $(this).find("#account").val();
            var amount = $(this).find("#amount").val();
            var receipt_image = $(this).find("#receipt_image").val();
            var card = $(this).find("#card").val();


            //console.log(line_manager_app);


            // The Incident
            if (employee == "") {
                error += "Name is required\n";
            }
            if (date == "") {
                error += "Date of Purchase is required\n";
            }
            if (supplier == "") {
                error += "Supplier is required\n";
            }
            if (description == "") {
                error += "Description is required\n";
            }
            if (account == "") {
                error += "Account is required\n";
            }
            if (amount == "") {
                error += "Amount is required\n";
            }
            if (receipt_image == "") {
                error += "Reciept Image is required\n";
            }

            if (jQuery("#confirm_chk").prop("checked") == false) {
                error += "Please confirm checkbox";
            }


            if (error != "") {
                swal({
                    title: "Error!",
                    text: error,
                    type: "error",
                    confirmButtonClass: "btn-danger"
                });
                return false;
            } else {
                if(card==2 || card == 5){ //if card Personal Card OR Cash > show popup msg
                    swal({
                        title: "Warning!",
                        text: "Please confirm you used personal funds for this purchase",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-success",
                        cancelButtonClass: "btn-danger",
                        confirmButtonText: "Yes, Submit!",
                        cancelButtonText: "No, Cancel!",
                        closeOnConfirm: true,
                        closeOnCancel: true,
                        showLoaderOnConfirm: true
                    },
                    function(atay) {
                        if (atay) { // yes			
                            form.submit();
                        }
                    });
                }else{
                    form.submit();
                }
            }

        });

        jQuery("#btn_submit_expense").click(function () {

            var btn_txt_span = jQuery(this).find('.btn_submit_expense_span');
            var bnt_txt = btn_txt_span.html();

            if (bnt_txt == 'Confirm') {

                jQuery(".line_manager_div").show();
                btn_txt_span.html("Submit");

            } else if (bnt_txt == 'Submit') {

                var line_manager = jQuery("#line_manager").val();
                var error = '';

                if (line_manager == '') {
                    error += 'Line Manager is Required\n';
                }

                if (error != '') {

                    swal({
                        title: "Error!",
                        text: error,
                        type: "error",
                        confirmButtonClass: "btn-danger"
                    });
                } else {

                    swal({
                        title: "Hold on!",
                        text: "This will submit expense statement. Do you want to proceed",
                        type: "warning",
                        confirmButtonClass: "btn-success",
                        cancelButtonClass: "btn-danger",
                        showConfirmButton: true,
                        showCancelButton: true,
                        confirmButtonText: "Yes",
                        cancelButtonText: "No"
                    }, function (isConfirm) {
                        if (isConfirm) {
                            jQuery("#submit_expense_form").submit();
                        }
                    });
                }

            }


        });
    });</script>
<script>
    jQuery(document).ready(function () {
        $("#btn-show-add-expense-modal").click(function () {
            jQuery("#add_expense_modal_trigger").click();
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
    });

</script>