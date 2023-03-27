<!-- CONTENT START HERE -->
<?php 
// no assigned PM
$no_assigned_pm_txt = "<span style='font-style: italic;'>No Assigned Property Manager</span>"; 
?>
<p>Dear <?php echo $agency_name; ?>,</p>

<!--<p>With only <?php echo $days_remaining; ?> days until the new legislation, you currently have <?php echo "{$row_count} ".( ( $row_count > 1 )?'properties':'property' ); ?> that require an upgrade. If any properties listed below have been upgraded, please let us know.</p>-->

<p>
    The below properties are noted in our database as not yet being upgraded. If any property listed below has already been upgraded 
    OR you would like for SATS to upgrade the property to Interconnected Smoke Alarms, please log into the SATS portal then follow this 
    link: <a href="https://<?php echo ( ENVIRONMENT == 'production' )?'agency':'agencydev'; ?>.sats.com.au/reports/qld_upgrade">SATS Agency Portal</a>
</p>

<p>From here you can view and approve quotes or advise SATS that the property has already been upgraded.</p>

<br /><br />
<table style="width:100%; border: 1px solid #efefef;">
<tr>    
    <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Address</b></td>
    <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Property Manager</b></td>
</tr>
<?php
// get pending jobs
$rowcount = 0;
foreach( $ps_sql->result() as $ps_row ){

    // IF property has IC Upgrade job where status != cancelled / deleted → Don’t include in list -> from ben's notes
    $job_sql_str = "
    SELECT COUNT(`id`) AS j_count
    FROM `jobs` 
    WHERE `property_id` = {$ps_row->property_id}
    AND `job_type` = 'IC Upgrade'
    AND `status` != 'Cancelled'
    AND `del_job` = 0
    ";
    //echo "<br />";
    $job_sql = $this->db->query($job_sql_str);
    $j_count = $job_sql->row()->j_count;

    // property address
    $paddress = "{$ps_row->p_address_1} {$ps_row->p_address_2}, {$ps_row->p_address_3}";
    
    // get property managers
    if( $ps_row->agency_user_account_id > 0 ){
        $pm_name = "{$ps_row->pm_fname} {$ps_row->pm_lname}";
    }else{
        $pm_name = $no_assigned_pm_txt;
    }

    if( $j_count == 0 ){

?>
    <tr style="background-color:<?php echo ($rowcount % 2 == 0 ? null : "#efefef") ?>">        
        <td style="padding: 5px;"><?php echo $paddress; ?></td>
        <td style="padding: 5px;"><?php echo $pm_name; ?></td>
    </tr>
<?php
    }
    $rowcount++;
}
?>
</table>

<p>*NB - the above list can also be downloaded either via excel or PDF from the Agency Portal</p>

<br /><br />

<p>
    Kind Regards<br />
    Smoke Alarm Testing Services.
</p>	
<!-- CONTENT END HERE -->