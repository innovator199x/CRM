
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
            'title' => 'Accounts',
            'link' => "/accounts/view_statements"
        ),
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/accounts/view_statements"
        ),
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

    <header class="box-typical-header">
        <div class="box-typical box-typical-padding">
            <div class="form-row">
                <div class="col-10">
                    <form method=POST action="/accounts/view_statements" class="form-row">


                        <div class="col">
                            <div class="form-row">
                                <div class="col">
                                    <label>From:</label>
                                    <input autocomplete="off"  type="label" name="from" value="" class="flatpickr form-control flatpickr-input">
                                </div>
                                <div class="col">
                                    <label>To:</label>
                                    <input autocomplete="off" type="label" name="to" value="" class="flatpickr form-control flatpickr-input">
                                </div>
                            </div>

                        </div>

                        <div class="col">
                            <label>Agency</label>
                            <select name="agency_id" class="form-control">
                                <option value="">----</option>
                                <?Php
                                foreach ($agencies as $am) {
                                    ?>

                                    <option class="status-<?Php echo $am['status']; ?>" value="<?php echo $am['agency_id']; ?>" <?php echo ($am['agency_id'] == $agency_id) ? 'selected="selected"' : ''; ?>><?php echo $am['agency_name']; ?> <?php echo ( $am['status'] == 'deactivated' ) ? '(INACTIVE)' : ''; ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col">
                            <label>Phrase:</label>
                            <input type="text" value="<?Php echo ($phrase !== null) ? $phrase : "" ?>" size="10" name="phrase" class="addinput form-control">
                        </div>

                        <div class="col">
                            <label>&nbsp;</label>
                            <input type="hidden" name="search_flag" value="1" />                           
                            <input  class="btn btn-inline" type="submit" value="Search">
                        </div>

                    </form>   
                </div>
                <div class="col-2">
                    <?php if ($search_flag == 1) { ?>

                                                                        <!--                                    <a href="statement_list_pdf.php?<?php echo $params; ?>" target="_blank" style="margin-top: 15px;float:right;">-->
                        <!--<form target="_blank" action="/accounts/pdfexport_statement" method="POST" >
                            <input type="hidden" name="from" value="<?Php echo $from ?>">
                            <input type="hidden" name="to" value="<?Php echo $to ?>">
                            <input type="hidden" name="agency_id" value="<?Php echo $agency_id ?>">
                            <input type="hidden" name="phrase" value="<?Php echo $phrase ?>">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <button type='submit' class='btn btn-inline' id="btn_display_statement">
                                <img class="inner_icon" src="/images/button_icons/pdf_white.png">
                                Display Statement
                            </button>
                        </form>-->

                        <label>&nbsp;</label>
                        <a target="_blank" href="/accounts/pdfexport_statement/?a=<?php echo rawurlencode($this->encryption_model->encrypt($agency_id)); ?>&f=<?php echo rawurlencode($this->encryption_model->encrypt($from)); ?>&t=<?php echo rawurlencode($this->encryption_model->encrypt($to)); ?>&p=<?php echo rawurlencode($this->encryption_model->encrypt($phrase)); ?>">
                            <button type='submit' class='btn btn-inline' id="btn_display_statement">
                                <img class="inner_icon" src="/images/button_icons/pdf_white.png">
                                Display Statement
                            </button>
                        </a>

                        <?php
                    }
                    ?>
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
                            <th>Invoice Date</th>
                            <th>Due Date</th>
                            <th>Invoice/Job</th>
                            <th>Property</th>
                            <th>Charges</th>
                            <th>Payments</th>
                            <th>Refunds</th>
                            <th>Credits</th>
                            <th>Balance</th>

                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $i = 0;

                        if (count($statements) > 0) {
                            foreach ($statements as $row) {

                                // grey alternation color
                                $row_color = ($i % 2 == 0) ? "" : "style='background-color:#eeeeee;'";

                                // append checkdigit to job id for new invoice number
                                $check_digit = $this->system_model->getCheckDigit(trim($row['jid']));
                                $bpay_ref_code = "{$row['jid']}{$check_digit}";

                                // address
                                $p_address = "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}";

                                $invoice_amount = number_format($row['invoice_amount'], 2);
                                $invoice_payments = number_format($row['invoice_payments'], 2);
                                $invoice_refunds = number_format($row['invoice_refunds'], 2);
                                $invoice_credits = number_format($row['invoice_credits'], 2);

                                $balance_tot += $row['invoice_balance'];
                                $invoice_balance = number_format($row['invoice_balance'], 2);

                                // payments
                                if ($invoice_payments > 0) {
                                    $invoice_payments_str = '$' . $invoice_payments;
                                } else {
                                    $invoice_payments_str = '';
                                }

                                // refunds
                                if ($invoice_refunds > 0) {
                                    $invoice_refunds_str = "<span class='colorItRed'>-\${$invoice_refunds}</span>";
                                } else {
                                    $invoice_refunds_str = '';
                                }

                                // credits
                                if ($invoice_credits > 0) {
                                    $invoice_credits_str = "<span class='colorItRed'>-\${$invoice_credits}</span>";
                                } else {
                                    $invoice_credits_str = '';
                                }


                                // Age
                                $date1 = date_create(date('Y-m-d', strtotime($row['jdate'])));
                                $date2 = date_create(date('Y-m-d'));
                                $diff = date_diff($date1, $date2);
                                $age = $diff->format("%r%a");
                                $age_val = (((int) $age) != 0) ? $age : 0;
                                if ($age > 30 && $invoice_balance > 0) {
                                    $row_color = "style='background-color:#fac3c373;'";
                                }

                                $due_date = date_create(date('Y-m-d', strtotime($row['jdate'] . " +30 days")));

                                if ($age_val <= 30) { // not overdue, within 30 days
                                    $not_overdue += $row['invoice_balance'];
                                } else if ($age_val >= 31 && $age_val <= 60) { // overdue, within 31 - 60 days
                                    $overdue_31_to_60 += $row['invoice_balance'];
                                } else if ($age_val >= 61 && $age_val <= 90) { // overdue, within 61 - 90 days
                                    $overdue_61_to_90 += $row['invoice_balance'];
                                } else if ($age_val >= 91) { // overdue over 91 days or more
                                    $overdue_91_more += $row['invoice_balance'];
                                }
                                ?>
                                <tr class="body_tr jalign_left" <?php echo $row_color; ?>>

                                    <td><?php echo ($this->system_model->isDateNotEmpty($row['jdate']) == true) ? $this->system_model->formatDate($row['jdate'], 'd/m/Y') : ''; ?></td>						
                                    <td ><?php echo $due_date->format('d/m/Y'); //echo $this->system_model->formatDate($due_date, 'd/m/Y');           ?></td>

                                    <td>
                                        <!--<a href="view_job_details.php?id=<?php echo $row['jid']; ?>"><?php echo $bpay_ref_code; ?></a>-->


                                        <?Php echo $this->gherxlib->crmLink('view_combined', $row['jid'], '<i style="color:#0082c6" class="fa fa-file-pdf-o"></i>'); ?> | 
                                        <?Php echo $this->gherxlib->crmLink('vjd', $row['jid'], $bpay_ref_code); ?>
                                    </td>			
                                    <td>

                                                                                                                                <!--<a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"; ?></a>-->
                                        <?Php
//                                        var_dump($row['property_id']);
                                        echo $this->gherxlib->crmLink('vpd', $row['property_id'], "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}");
                                        ?>
                                    </td>

                                    <td>$<?php echo $invoice_amount; ?></td>							
                                    <td><?php echo $invoice_payments_str; ?></td>
                                    <td><?php echo $invoice_refunds_str; ?></td>
                                    <td><?php echo $invoice_credits_str; ?></td>
                                    <td>$<?php echo $invoice_balance; ?></td>


                                </tr>

                                <?php
                                $i++;
                            }
                        } else {
                            ?>
                        <td colspan="9" align="left">Select Agency to display data</td>
                        <?php
                    }
                    ?>

                    </tbody>

                </table>

                <table class="table table-hover main-table">
                    <thead>
                        <tr>
                            <th>0-30 days (Not Overdue)</th>
                            <th>31-60 days OVERDUE</th>
                            <th>61-90 days OVERDUE</th>
                            <th>91+ days OVERDUE</th>
                            <th>Total Amount Due</th>
                        </tr>
                    </thead>
                    <?Php
                    $overdue_color = "style='background-color:#fac3c373;'";
                    ?>

                    <tr class="toprow jalign_left">
                        <td class="border-right"><?php echo '$' . number_format($not_overdue, 2); ?></td>
                        <td <?Php echo (number_format($overdue_31_to_60, 2) > 0) ? $overdue_color : "" ?> class="border-right"><?php echo '$' . number_format($overdue_31_to_60, 2); ?></td>
                        <td <?Php echo (number_format($overdue_61_to_90, 2) > 0) ? $overdue_color : "" ?> class="border-right"><?php echo '$' . number_format($overdue_61_to_90, 2); ?></td>
                        <td <?Php echo (number_format($overdue_91_more, 2) > 0) ? $overdue_color : "" ?> class="border-right"><?php echo '$' . number_format($overdue_91_more, 2); ?></td>
                        <td><?php echo '$' . number_format($balance_tot, 2); ?></td>
                    </tr>
                </table>

                <?php
                if( $search_flag == 1 ){ ?>

                    <h5>Agency Payments</h5>
                    <table class="table table-hover main-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Description</th>
                                <th>Charges</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach($agen_pay_sql->result() as $index => $agen_pay_row){ 
                            
                            $agency_pay_date = date("d/m/Y",strtotime($agen_pay_row->date));

                            ?>
                            <tr>
                                <td><?php echo $agency_pay_date; ?></td>
                                <td><?php echo $agen_pay_row->reference; ?></td>
                                <td><?php echo "Payment received {$agency_pay_date}. Please email a remittance advice to {$this->config->item('sats_accounts_email')}"; ?></td>
                                <td><?php echo '$'.$agen_pay_row->amount; ?></td>
                                <td><?php echo '$'.$agen_pay_row->remaining; ?></td>      
                            </tr> 
                        <?php
                        }
                        ?>                                     
                        </tbody> 
                    </table>

                <?php
                }
                ?>               


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
<code>SELECT `j`.`id` AS `jid`, `j`.`status` AS `jstatus`, `j`.`service` AS `jservice`, `j`.`created` AS `jcreated`, `j`.`date` AS `jdate`, `j`.`comments` AS `j_comments`, `j`.`invoice_amount`, `j`.`invoice_payments`, `j`.`invoice_refunds`, `j`.`invoice_credits`, `j`.`invoice_balance`, `j`.`property_id`, `p`.`address_1` AS `p_address_1`, `p`.`address_2` AS `p_address_2`, `p`.`address_3` AS `p_address_3`, `p`.`state` AS `p_state`, `p`.`postcode` AS `p_postcode`, `p`.`comments` AS `p_comments`, `p`.`compass_index_num`, `a`.`agency_id` AS `a_id`, `a`.`phone` AS `a_phone`, `a`.`address_1` AS `a_address_1`, `a`.`address_2` AS `a_address_2`, `a`.`address_3` AS `a_address_3`, `a`.`state` AS `a_state`, `a`.`postcode` AS `a_postcode`, `a`.`account_emails`, `a`.`agency_emails`, `a`.`franchise_groups_id`, `a`.`statements_agency_comments`
FROM `jobs` `j`
LEFT JOIN `property` `p` ON `j`.`property_id`=`p`.`property_id`
LEFT JOIN `agency` `a` ON `p`.`agency_id`=`a`.`agency_id`
WHERE 
`j`.`invoice_balance` !=0
AND `j`.`status` = 'Completed'
AND `a`.`status` != 'target'
AND (
j.`date` >= '<?php echo $this->config->item('accounts_financial_year'); ?>' OR
j.`unpaid` = 1	
)
AND  `j`.`id` >0 AND `p`.`agency_id` = '$agency_id' 
ORDER BY `j`.`date` ASC
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