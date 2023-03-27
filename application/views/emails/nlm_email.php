<?php $this->load->view('emails/template/email_header.php') ?>


<!-- CONTENT START HERE -->

<p>
    Dear Accounts,
    <br/>
    <br/>
    <a href="<?php echo $this->config->item('crm_link'); ?>/view_property_details.php?id=<?php echo $prop_id; ?>"><?php echo$prop_name; ?></a> has been marked NLM.
    <br/>
    <br/>
    Please confirm billing related to this property.
    <br/>
    <br/>
    Regards,    
    <br/>
    SATS Dev Team
</p>


<!-- CONTENT END HERE -->


<?php $this->load->view('emails/template/email_footer.php') ?>