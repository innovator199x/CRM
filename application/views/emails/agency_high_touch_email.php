<?php $this->load->view('emails/template/email_header.php') ?>

<!-- CONTENT START HERE -->

<p>Dear Info and Sales,</p>

<p style="text-indent: 25px;"><strong><?= $agency_name ?></strong> has just been <strong><?= $priority ?></strong> as <strong><?= $abb ?></strong> by <strong><?= $staff->FirstName; ?></strong>.</p>

<div>Regards,</div>
<div>The IT Team</div>

<!-- CONTENT END HERE -->

<?php $this->load->view('emails/template/email_footer.php') ?>