<?php $this->load->view('emails/template/email_header.php') ?>


<!-- CONTENT START HERE -->


<h3>Leave Request</h3>

<table style='margin:0;width:100%;'>
	<tr>
		<td>Date</td><td><?php echo date('d/m/Y',strtotime($today)) ?></td>
	</tr>
	<tr style="background-color:#efefef">
		<td>Name</td><td><?php echo $employee_name; ?></td>
	</tr>
	<tr>
		<td>Type of Leave</td><td><?php echo $type_of_leave; ?></td>
	</tr>
	<tr style="background-color:#efefef">
		<td>Backup Leave</td><td><?php echo $tol_str; ?></td>
	</tr>
	<tr>
		<td>First Day of Leave</td><td><?php echo $lday_of_work; ?></td>
	</tr>
	<tr style="background-color:#efefef">
		<td>Last Day of Leave</td><td><?php echo $fday_back; ?></td>
	</tr>
	<tr>
		<td>Number of days</td><td><?php echo $num_of_days; ?></td>
	</tr>
	<tr style="background-color:#efefef">
		<td>Reason for Leave</td><td><?php echo $reason_for_leave; ?></td>
	</tr>
	<tr>
		<td>Line Manager</td><td><?php echo $lm_name; ?></td>
	</tr>
</table>

<p>Kind Regards,<br />SATS Team</p>

<!-- CONTENT END HERE -->


<?php $this->load->view('emails/template/email_footer.php') ?>