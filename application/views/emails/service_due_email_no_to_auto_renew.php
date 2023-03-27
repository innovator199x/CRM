<!-- CONTENT START HERE -->
<?php 
// no assigned PM
$no_assigned_pm_txt = "<span style='font-style: italic;'>No Assigned Property Manager</span>"; 
?>
<p>
    Hi There, <br />
    This is a courtesy to advise the below properties are due for service renewals here at Smoke Alarm Testing Services (SATS)
</p>

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

<p style="color: red; font-weight: bold;">
    <b>Please note: </b> We are under your strict instruction to NOT attend these properties as part of our Annual Maintenance program and 
    if we have not been granted permission to attend and complete works, SATS shall deem these properties non-compliant and accept no liability 
    pertaining to their compliance.
</p>

<p>We ask that you advise which properties are to continue SATS services and which are to be deactivated within our data base (if required).</p>

<p>
    Should you have any additional questions, please feel free to contact our friendly Customer Service Team on 1300 41 66 67 or please respond to 
    this email directly.
</p>

<br /><br />

<p>
    Have a lovely day ahead.<br />
    Kind Regards,<br />
    Smoke Alarm Testing Services
</p>	
<!-- CONTENT END HERE -->