<?php $this->load->view('emails/template/email_header.php') ?>


<!-- CONTENT START HERE -->


<style>
    .table_list{

    }
</style>

<table style="width:100%">
    <tr>
        <td>
            <strong><?php echo $agency_info['agency_name'] ?></strong><br/>
            <?php echo " {$agency_info['address_1']} {$agency_info['address_2']} <br/> {$agency_info['address_3']} {$agency_info['state']} {$agency_info['postcode']}" ?>
        </td>
        <td style="text-align:right;">
            <span style="color:red;font-size:17px;">STATEMENT</span> Current as of <?php echo date('d/m/Y') ?>
        </td>
    </tr>
</table>

<table class="table_list" style="width:100%;">
    <thead>
        <tr>
            <th style="border:1px solid #cccccc">Date</th>
            <th style="border:1px solid #cccccc">Reference</th>
            <?php
            if( $fg_id == $compass_fg_id ){ ?>
                <th style="border:1px solid #cccccc">Index No.</th>
            <?php
            }
            ?>
            <th style="border:1px solid #cccccc">Description</th>
            <th style="border:1px solid #cccccc">Charges</th>
            <th style="border:1px solid #cccccc">Payments</th>
            <th style="border:1px solid #cccccc">Credits</th>
            <th style="border:1px solid #cccccc">Balance</th>
        </tr>
    </thead>
    <tbody>
        <?php 

            $balance_tot = 0;
            $not_overdue = 0;
            $overdue_31_to_60 = 0;
            $overdue_61_to_90 = 0;
            $overdue_91_more = 0;

            $balance_tot_agen_pay_rem = 0;
            $not_overdue_agen_pay_rem = 0;
            $overdue_31_to_60_agen_pay_rem = 0;
            $overdue_61_to_90_agen_pay_rem = 0;
            $overdue_91_more_agen_pay_rem = 0;

            $fg_id = $agency_info['franchise_groups_id'];
            if($this->config->item('country')==1){ // AU			
                $compass_fg_id = 39;                    
            }


            foreach( $statement_data->result_array() as $row ){
                
                $jdate = ( $this->system_model->isDateNotEmpty($row['jdate']) )?date('d/m/Y',strtotime($row['jdate'])):'';
                $check_digit = $this->gherxlib->getCheckDigit(trim($row['jid']));
                $bpay_ref_code = "{$row['jid']}{$check_digit}";	
                $p_address = "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}";
                $invoice_amount = number_format($row['invoice_amount'],2);
                $invoice_payments = number_format($row['invoice_payments'],2);
                $invoice_credits = number_format($row['invoice_credits'],2);

                $balance_tot += $row['invoice_balance'];
                $invoice_balance = number_format($row['invoice_balance'],2);
                
                if( $invoice_payments > 0 ){
                    $invoice_payments_str = '$'.$invoice_payments;
                }else{
                    $invoice_payments_str = '';
                }
                
                if( $invoice_credits > 0 ){
                    $invoice_credits_str = '-$'.$invoice_credits;
                }else{
                    $invoice_credits_str = '';
                }

                // Age
                $date1=date_create(date('Y-m-d',strtotime($row['jdate'])));
                $date2=date_create(date('Y-m-d'));
                $diff=date_diff($date1,$date2);
                $age = $diff->format("%r%a");
                $age_val = (((int)$age)!=0)?$age:0;

                if( $age_val <= 30 ){ // not overdue, within 30 days
                    $not_overdue += $row['invoice_balance'];
                }else if( $age_val >= 31 && $age_val <= 60 ){ // overdue, within 31 - 60 days
                    $overdue_31_to_60 += $row['invoice_balance'];
                }else if( $age_val >= 61 && $age_val <= 90 ){ // overdue, within 61 - 90 days
                    $overdue_61_to_90 += $row['invoice_balance'];
                }else if( $age_val >= 91 ){ // overdue over 91 days or more
                    $overdue_91_more += $row['invoice_balance'];
                }
                
                
        ?>

                <tr>
                    <td style="border:1px solid #cccccc"><?php echo $jdate; ?></td>
                    <td style="border:1px solid #cccccc"><?php echo $bpay_ref_code; ?></td>
                    <?php
                    if( $fg_id == $compass_fg_id ){ ?>
                        <td style="border:1px solid #cccccc"><?php echo  $row['compass_index_num']; ?></td>
                    <?php
                    }
                    ?>
                    <td style="border:1px solid #cccccc"><?php echo $p_address; ?></td>
                    <td style="border:1px solid #cccccc">$<?php echo $invoice_amount; ?></td>
                    <td style="border:1px solid #cccccc"><?php echo $invoice_payments_str; ?></td>
                    <td style="border:1px solid #cccccc"><?php echo $invoice_credits_str; ?></td>
                    <td style="border:1px solid #cccccc">$<?php echo $invoice_balance; ?></td>
                </tr>

        <?php
            }

            // agency payments listing
            $sel_query = "
            agen_pay.agency_payments_id,
            agen_pay.date,
            agen_pay.amount,
            agen_pay.reference,
            agen_pay.payment_type,
            agen_pay.allocated,
            agen_pay.remaining,
            agen_pay.bank_deposit,
            agen_pay.remittance,

            pt.payment_type_id,
            pt.pt_name
            ";

            $custom_filter = "
            agen_pay.bank_deposit = 1         
            AND agen_pay.remaining > 0";
            $agen_pay_params = array(
                'sel_query' => $sel_query,     
                'custom_filter' => $custom_filter,                           
                'agency_id' => $agency_id,
                'join_table' => array('agency_payments_agencies'),                                
                'display_query' => 0
            );
            $agen_pay_sql = $this->remittance_model->get_agency_payments($agen_pay_params);

            foreach($agen_pay_sql->result() as $index => $agen_pay_row){

                $agency_pay_date = date("d/m/Y",strtotime($agen_pay_row->date));

                // Age
                $date1 = date_create(date('Y-m-d', strtotime($agen_pay_row->date)));
                $date2 = date_create(date('Y-m-d'));
                $diff = date_diff($date1, $date2);
                $age = $diff->format("%r%a");
                $age_val = (((int) $age) != 0) ? $age : 0;

                $balance_tot_agen_pay_rem += $agen_pay_row->remaining;

                if ($age_val <= 30) { // not overdue, within 30 days
                    $not_overdue_agen_pay_rem += $agen_pay_row->remaining;
                } else if ($age_val >= 31 && $age_val <= 60) { // overdue, within 31 - 60 days
                    $overdue_31_to_60_agen_pay_rem += $agen_pay_row->remaining;
                } else if ($age_val >= 61 && $age_val <= 90) { // overdue, within 61 - 90 days
                    $overdue_61_to_90_agen_pay_rem += $agen_pay_row->remaining;
                } else if ($age_val >= 91) { // overdue over 91 days or more
                    $overdue_91_more_agen_pay_rem += $agen_pay_row->remaining;
                }
            
                $agency_pay_description = "Payment received {$agency_pay_date}. Please email a remittance advice to {$this->config->item('sats_accounts_email')}";               
                ?>

                <tr>
                    <td style="border:1px solid #cccccc"><?php echo $agency_pay_date; ?></td>
                    <td style="border:1px solid #cccccc"><?php echo $agen_pay_row->reference; ?></td>
                    <td style="border:1px solid #cccccc" colspan="2"><?php echo $agency_pay_description; ?></td>
                    <td style="border:1px solid #cccccc"><?php echo '-$'.$agen_pay_row->amount; ?></td>
                    <td style="border:1px solid #cccccc"></td>
                    <td style="border:1px solid #cccccc"></td>
                    <td style="border:1px solid #cccccc"><?php echo '-$'.$agen_pay_row->remaining; ?></td>
                </tr>

            <?php
            }
        ?>
    </tbody>
</table>

<div><p> <?php echo $row['statements_agency_comments']; ?> </p></div>

<table class="table_list" style="width:100%;">
    <thead>
        <tr>
            <th style="border:1px solid #cccccc">0-30 days (Not Overdue)</th>
            <th style="border:1px solid #cccccc">31-60 days OVERDUE</th>
            <th style="border:1px solid #cccccc">61-90 days OVERDUE</th>
            <th style="border:1px solid #cccccc">1+ days OVERDUE</th>
            <th style="border:1px solid #cccccc">Total Amount Due</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="border:1px solid #cccccc"><?php echo "$".number_format(($not_overdue-$not_overdue_agen_pay_rem),2) ?></td>
            <td style="border:1px solid #cccccc"><?php echo "$".number_format(($overdue_31_to_60-$overdue_31_to_60_agen_pay_rem),2) ?></td>
            <td style="border:1px solid #cccccc"><?php echo "$".number_format(($overdue_61_to_90-$overdue_61_to_90_agen_pay_rem),2) ?></td>
            <td style="border:1px solid #cccccc"><?php echo "$".number_format(($overdue_91_more-$overdue_91_more_agen_pay_rem),2) ?></td>
            <td style="border:1px solid #cccccc"><?php echo "$".number_format(($balance_tot-$balance_tot_agen_pay_rem),2) ?></td>
        </tr>
    </tbody>
</table>

<?php 
//get statements_generic_note from crm_settings table
$statements_generic_note_query = $this->db->select('statements_generic_note')->from('crm_settings')->where('country_id', $this->config->item('country'))->get();
$statements_generic_note_query_row = $statements_generic_note_query->row_array();
$statement_agency_comments = strip_tags($statements_generic_note_query_row['statements_generic_note']);
?>
<div><p> <?php echo $statement_agency_comments; ?> </p></div>


<!-- CONTENT END HERE -->


<?php $this->load->view('emails/template/email_footer.php') ?>