<?php $this->load->view('emails/template/email_header.php') ?>


<!-- CONTENT START HERE -->

<p>
    Dear Accounts,
    <br/>
    <br/>
    The registration of vehicle <?php echo $old_plate; ?> has been changed to <?php echo $new_plate; ?>.
    <br/>
    <br/>
    Regards,    
    <br/>
    SATS Dev Team
</p>


<!-- CONTENT END HERE -->


<?php $this->load->view('emails/template/email_footer.php') ?>