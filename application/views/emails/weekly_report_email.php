<!-- CONTENT START HERE -->
<?php 
// no assigned PM
$no_assigned_pm_txt = "<span style='font-style: italic;'>No Assigned Property Manager</span>"; 
?>
<p>Dear <?php echo $agency_name; ?>,</p>
<p>
    Please find the report below on jobs that are booked or recently completed.<br />
    Please email us if you have any enquiries.
</p>

<?php
if( $completed_sql->num_rows() > 0 ){ ?>
    <h4>Completed Jobs</h4>
    <table style="width:100%; border: 1px solid #efefef;">
    <tr>
        <td style="background-color: #404041; color: #ffffff; padding: 5px; width: 100px;"><b>Date</b></td>
        <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Address</b></td>
        <td style="background-color: #404041; color: #ffffff; padding: 5px; width: 205px;"><b>Property Manager</b></td>
    </tr>
    <?php
    // get pending jobs
    $rowcount = 0;
    foreach( $completed_sql->result() as $completed_row ){

        // property address
        $paddress = "{$completed_row->p_address_1} {$completed_row->p_address_2}, {$completed_row->p_address_3}";

        // get property managers
        if( $completed_row->agency_user_account_id > 0 ){
            $pm_name = "{$completed_row->pm_fname} {$completed_row->pm_lname}";
        }else{
            $pm_name = $no_assigned_pm_txt;
        }  
    ?>
        <tr style="background-color:<?php echo ($rowcount % 2 == 0 ? null : "#efefef") ?>">
            <td style="padding: 5px; width: 100px;"><?php echo date('d/m/Y',strtotime($completed_row->jdate)); ?></td>
            <td style="padding: 5px;"><?php echo $paddress; ?></td>
            <td style="padding: 5px; width: 205px;"><?php echo $pm_name; ?></td>
        </tr>
    <?php
        $rowcount++;
    }
    ?>
    </table>
<?php
}
?>

<?php
if( $booked_sql->num_rows() > 0 ){ ?>
    <h4>Booked Jobs</h4>
    <table style="width:100%; border: 1px solid #efefef;">
    <tr>
        <td style="background-color: #404041; color: #ffffff; padding: 5px; width: 100px;"><b>Date</b></td>
        <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Address</b></td>
        <td style="background-color: #404041; color: #ffffff; padding: 5px; width: 205px;"><b>Property Manager</b></td>
    </tr>
    <?php
    // get pending jobs
    $rowcount = 0;
    foreach( $booked_sql->result() as $booked_row ){

        // property address
        $paddress = "{$booked_row->p_address_1} {$booked_row->p_address_2}, {$booked_row->p_address_3}";
        
        // get property managers
        if( $booked_row->agency_user_account_id > 0 ){
            $pm_name = "{$booked_row->pm_fname} {$booked_row->pm_lname}";
        }else{
            $pm_name = $no_assigned_pm_txt;
        } 
    ?>
        <tr style="background-color:<?php echo ($rowcount % 2 == 0 ? null : "#efefef") ?>">
            <td style="padding: 5px; width: 100px;"><?php echo date('d/m/Y',strtotime($booked_row->jdate)); ?></td>
            <td style="padding: 5px;"><?php echo $paddress; ?></td>
            <td style="padding: 5px; width: 205px;"><?php echo $pm_name; ?></td>
        </tr>
    <?php
        $rowcount++;
    }
    ?>
    </table>
<?php
}
?>


<br /><br />

<p>
    Kind Regards<br />
    Smoke Alarm Testing Services.
</p>	
<!-- CONTENT END HERE -->