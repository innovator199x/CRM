<!-- CONTENT START HERE -->
<?php 
// no assigned PM
$no_assigned_pm_txt = "<span style='font-style: italic;'>No Assigned Property Manager</span>"; 
?>
<p>Dear <?php echo $agency_name; ?>,</p>
<p>
Please find the list of properties that require key access for <?php echo $key_access_date; ?><br />
Should there be any problems please contact us on <?php echo $agent_number; ?> or <?php echo $outgoing_email; ?>
</p>

<br />
<h2 style="color:red;">Keys to be collected for <?php echo $key_access_date; ?></h2>

<table style="width:100%; border: 1px solid #efefef;">
<tr>
    <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Job Date</b></td>
    <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Address</b></td>
    <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Key Number</b></td>
    <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Authorised by</b></td>
    <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Technician</b></td>
    <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Property Manager</b></td>
</tr>
<?php
// get pending jobs
$rowcount = 0;
foreach( $job_sql->result() as $job_row ){

    // property address
    $paddress = "{$job_row->p_address_1} {$job_row->p_address_2}, {$job_row->p_address_3}";

    // get property managers
    if( $job_row->agency_user_account_id > 0 ){
        $pm_name = "{$job_row->pm_fname} {$job_row->pm_lname}";
    }else{
        $pm_name = $no_assigned_pm_txt;
    } 

    // property key number
    if( in_array($agency_id, $spec_agency) ){
        $key_number = str_repeat("*", strlen($job_row->key_number));
    }else{
        $key_number = $job_row->key_number;
    }

    // technician
    $tech_name = $job_row->tech_fname;
    

?>
    <tr style="background-color:<?php echo ($rowcount % 2 == 0 ? null : "#efefef") ?>">
        <td style="padding: 5px;"><?php echo date('d/m/Y',strtotime($job_row->jdate)); ?></td>
        <td style="padding: 5px;"><?php echo $paddress; ?></td>
        <td style="padding: 5px;"><?php echo $key_number; ?></td>
        <td style="padding: 5px;"><?php echo $job_row->key_access_details; ?></td>
        <td style="padding: 5px;"><?php echo $tech_name; ?></td>
        <td style="padding: 5px;"><?php echo $pm_name; ?></td>
    </tr>
<?php

    // insert job logs            
    $log_title = 56; // Key access    
    $job_log = "Key access email for " . $key_access_date . " sent to: <strong>{$sent_to_imp}</strong>";                        
    $log_params = array(
        'title' => $log_title,
        'details' => $job_log,
        'display_in_vjd' => 1,
        'auto_process' => 1,
        'job_id' => $job_row->jid
    );
    $this->system_model->insert_log($log_params);  

    $rowcount++;

}
?>
</table>

<p style='color:red;'>
PLEASE NOTE: This is an automated email and we do not read any replies. If you need to contact us then please contact us on <?php echo $agent_number; ?> or <?php echo $outgoing_email; ?>.
</p>

<br />

<p>
    Kind Regards<br />
    Smoke Alarm Testing Services.
</p>	
<!-- CONTENT END HERE -->