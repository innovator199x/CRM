<!-- CONTENT START HERE -->
<?php 
// no assigned PM
$no_assigned_pm_txt = "<span style='font-style: italic;'>No Assigned Property Manager</span>"; 
?>
<p>Dear <?php echo $agency_name; ?>,</p>

<!-- Switch email content text here based agency api connected or not -->
<?php if( $agency_api_tokens_q->num_rows()>0 ){ ## Agency is conntect to API ?> 

    <p>Please <a href='<?php echo $agency_portal_link; ?>'>log in</a>  to our Agency Portal and go to the 'Service Due' page to view properties that are now due for service.</p>
    <p>Please note, because we have an active API connection with your Agency, our Team will adjust any updated tenant details required.
    <p>If you no longer manage the property, then mark the checkbox and 'click' NO LONGER MANAGE. These changes can be done in multiple ways.</p>
    <p style='color:red;'>Any properties that are still in 'Service Due' by the 15th of the month, will automatically be renewed to fulfill our obligations to your landlords.

    <?php
    if( $agency_state == 'NSW' ){ ?>
    <p>When you process the properties due for service, IF we identify that a property needs to be serviced within the next 60 days (to meet legislative requirements) we will send the job straight to booking (previously this job was ‘on hold’ until the beginning of the following month).</p>
    <?php
    }
    ?>
    <p>To view our Step-by-Step video on how to process properties that are due for service please click <a href='<?php echo $youtube_link ?>'>HERE</a>.</p>
    <p>If you need any help or have questions, please contact our office on <?php echo $agent_number; ?> and speak with one of our friendly staff members.</p>

<?php }else{ ## Agency is NOT connected to any API ?> 

    <p>Please <a href='<?php echo $agency_portal_link; ?>'>log in</a> to our Agency Portal and go to the 'Service Due' page to view properties that are now due for service.</p>
    <p>If the tenant details are correct and you still manage the property then mark the check box and 'click' CREATE JOB.</p>
    <p>If you no longer manage the property then mark the check box and 'click' NO LONGER MANAGE. These changes can be done in multiples.</p>
    <?php
    if( $agency_state == 'NSW' ){ ?>
    <p>When you process the properties due for service, IF we identify that a property needs to be serviced within the next 60 days (to meet legislative requirements) we will send the job straight to booking (previously this job was ‘on hold’ until the beginning of the following month).</p>
    <?php
    }
    ?>
    <?php
    if( $agency_auto_renew == 1 ){
    ?>
    <p style='color:red;'>Any properties that are still in 'Service Due' by the 15th of the month, will automatically be renewed to fulfil our obligations to your Landlords.</p>
    <?php
    }else{ ?>
    You are currently set up to not auto-renew the properties so SATS will not attend the property until instructed to by your agency. SATS will not take any responsibility in fulfilling our obligations to your Landlord because you have asked us to not attend unless instructed.";
    <?php
    } ?>
    <p>To view our Step by Step video on how to process properties that are due for service please click <a href='<?php echo $youtube_link ?>'>HERE</a>.</p>
    <p>If you need any help or have questions, please contact our office on <?php echo $agent_number; ?> and speak with one of our friendly staff members.</p>

<?php } ?>

<br /><br />
<table style="width:100%; border: 1px solid #efefef;">
<tr>
    <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Month Due</b></td>
    <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Address</b></td>
    <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Property Manager</b></td>
</tr>
<?php
// get pending jobs
$rowcount = 0;
foreach( $pending_sql->result() as $pending_row ){

    // property address
    $paddress = "{$pending_row->p_address_1} {$pending_row->p_address_2}, {$pending_row->p_address_3}";
    
    // get property managers
    if( $pending_row->agency_user_account_id > 0 ){
        $pm_name = "{$pending_row->pm_fname} {$pending_row->pm_lname}";
    }else{
        $pm_name = $no_assigned_pm_txt;
    }

?>
    <tr style="background-color:<?php echo ($rowcount % 2 == 0 ? null : "#efefef") ?>">
        <td style="padding: 5px;"><?php echo date('F',strtotime($pending_row->start_date)); ?></td>
        <td style="padding: 5px;"><?php echo $paddress; ?></td>
        <td style="padding: 5px;"><?php echo $pm_name; ?></td>
    </tr>
<?php
    $rowcount++;
}
?>
</table>

<br /><br />

<p>
    Kind Regards<br />
    Smoke Alarm Testing Services.
</p>	
<!-- CONTENT END HERE -->