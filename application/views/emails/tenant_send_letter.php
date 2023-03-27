<?php $this->load->view('emails/template/email_header.php') ?>


<!-- CONTENT START HERE -->

<p><?php echo date("F d, Y"); ?></p>

<p>
<?=$tenant_str?><br />
<?=$p_address_1?> <?=$p_address_2?><br />
<?=$p_address_3?> <?=$p_state?> <?=$p_postcode?><br />
</p>

<p>
Dear <?=$tenant_str?>
</p>

<p>
Recently your Landlord and <?=$agency_name?> engaged the services of <?=$trading_name?> (SATS) to service the 
<?=$type?> at the property you occupy.
</p>

<p>
The test will take approximately 5-30 minutes and is a mandatory requirement to ensure the safety of the people in 
your home and is at no cost to you. Please be advised that your power may be disrupted during this time. 
Any electrical devices that you feel may be affected should be disconnected from the power socket prior to our 
technician attending.
</p>

<p>
A representative from Smoke Alarm Testing Services will be in touch shortly to book in a schedule. You can be present during the testing or alternatively we will collect keys from <?=$agency_name?> to access the property.
</p>

<p>
You have the choice of:
</p>

<p>
Being at home during the testing or arrange to have someone to allow access.
</p>

<p>
Or
</p>

<p>
Allow us to obtain keys from <?=$agency_name?> to access the property.
</p>

<p>
If you have any questions in regards to this matter please contact your property manager or our office on <?=$tenant_number?>.
</p>

<p>
Yours Faithfully<br />
<img src="https://sats.com.au/images/<?=$email_signature?>" /><br />
Smoke Alarm Testing Services<br />
</p>

<p>Kind Regards,<br />SATS Team</p>

<!-- CONTENT END HERE -->


<?php $this->load->view('emails/template/email_footer.php') ?>