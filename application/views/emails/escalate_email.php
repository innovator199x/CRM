<!-- CONTENT START HERE -->
<?php 
// no assigned PM
$no_assigned_pm_txt = "<span style='font-style: italic;'>No Assigned Property Manager</span>"; 
?>
<p>Dear <?php echo $agency_name; ?>,</p>
<p>
    We need help in being able to access the following properties
</p>

<p>
    Below is a list of properties that we need help with:
</p>
<br />


<table style="width:100%; border: 1px solid #efefef;">
<tr>
    <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Address</b></td>
    <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Property Manager</b></td>
</tr>
<?php
// get pending jobs
$rowcount = 0;
foreach( $escalate_sql->result() as $escalate_row ){

    // property address
    $paddress = "{$escalate_row->p_address_1} {$escalate_row->p_address_2}, {$escalate_row->p_address_3}";
    
    // get property managers
    if( $escalate_row->agency_user_account_id > 0 ){
        $pm_name = "{$escalate_row->pm_fname} {$escalate_row->pm_lname}";
    }else{
        $pm_name = $no_assigned_pm_txt;
    }

?>
    <tr style="background-color:<?php echo ($rowcount % 2 == 0 ? null : "#efefef") ?>">        
        <td style="padding: 5px;"><?php echo $paddress; ?></td>
        <td style="padding: 5px;"><?php echo $pm_name; ?></td>
    </tr>
<?php
    $rowcount++;
}
?>
</table>

<br />

<p>
    Can you please <a href="<?php echo $agency_portal_link; ?>">log in</a> to our Agency Portal and go to the 'Help Needed' page to view these properties and follow the instructions.   
</p>

<p>    
    If you need any help or have questions, please contact our office on <?php echo $agent_number; ?> and speak with one of our friendly staff members.
</p>

<br />

<p>
    Kind Regards<br />
    Smoke Alarm Testing Services.
</p>	
<!-- CONTENT END HERE -->