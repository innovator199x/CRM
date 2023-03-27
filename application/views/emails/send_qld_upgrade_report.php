<h4>Properties yet to be upgraded, as of <?php echo date('d/m/Y') ?>:</h4>
<table style="width:100%; border: 1px solid #efefef;">
<tr>
    <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>No. Properties</b></td>
    <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Avg. Invoice</b></td>
    <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Total</b></td>
</tr>
<td style="padding: 5px;"><?php echo $qld_prop_upgrade_count; ?></td>
<td style="padding: 5px;">$<?php echo $average_inv_amount; ?></td>
<td style="padding: 5px;">$<?php echo number_format( ($qld_prop_upgrade_count*$average_inv_amount), 2 ); ?></td>
</table>