<?php $this->load->view('emails/template/email_header.php') ?>


<!-- CONTENT START HERE -->

<p>Dear Accounts,</p>

<p><?php echo $user; ?> has created a new <?php echo $e_form; ?> request. Please go to Reports / <?php echo $e_form; ?> Request to see it or <a href="<?php echo $item_url; ?>">click here to see details.</a></p>

<p>Kind Regards,<br />SATS Team</p>

<!-- CONTENT END HERE -->


<?php $this->load->view('emails/template/email_footer.php') ?>