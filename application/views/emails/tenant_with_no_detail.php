<?php $this->load->view('emails/template/email_header.php') ?>


<!-- CONTENT START HERE -->


<p>Dear <?=$agency_name?>,</p>
<p><?=$prop_address?> is now in our system ready for booking.</p>
<p>Any questions please feel free to contact us on <?=$agent_number?></p>
<br/>


<p>Kind Regards,<br />SATS Team</p>

<!-- CONTENT END HERE -->


<?php $this->load->view('emails/template/email_footer.php') ?>