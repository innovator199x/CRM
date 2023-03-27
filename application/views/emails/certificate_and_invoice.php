<?php $this->load->view('emails/template/email_header.php') ?>


<!-- CONTENT START HERE -->


<p>Dear <?php echo $agency_name_switch; ?>,</p>

<?php
// add attachment
if( $send_emails == 1 ){ ?>
    <p>Please find the attached <?php echo $email_txt ?> for the below property.</p>
<?php
}
?>

<p>
    <strong>Property Address</strong><br/>
    <span>
        <?php echo "{$address1} {$address2} <br/> {$address3} {$state} {$postcode}" ?>
    </span>
</p>

<p><?php echo isset($pdf_link_str) ? $pdf_link_str : ""; ?></p>
<p><?php echo isset($pdf_link_str2) ? $pdf_link_str2 : ""; ?></p>

<p>If you have any questions or we can be of further assistance please feel free to contact us on <?php echo $agent_number; ?> or <?php echo $outgoing_email; ?>.</p>

<p>Kind Regards,<br />SATS Team</p>

<!-- CONTENT END HERE -->


<?php $this->load->view('emails/template/email_footer.php') ?>