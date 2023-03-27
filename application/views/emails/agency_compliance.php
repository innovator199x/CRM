<!-- CONTENT START HERE -->

<p>Dear <?php echo $agency_name; ?>,</p>
<p>
We need compliance help with the following properties.<br />
Below is a list of properties that we need help with:
</p>


<table width="100%">
<tr>
    <td width="320"><b>Address</b></td>
    <td><b>Property Serviced Last 12 Months</b></td>
    <td><b>Last Battery Change</b></td>    
    <td><b>Next scheduled</b></td>
</tr>
<?php
// get pending jobs
$rowcount = 0;
foreach( $compliance_sql->result() as $compliance_row ){

    // property address
    $paddress = "{$compliance_row->p_address_1} {$compliance_row->p_address_2}, {$compliance_row->p_address_3}";

    // last service            
    $sel_query = "
        j.`id` AS jid, 
        j.`date`
    ";

    $custom_where = "
    (
        j.`status` = 'Completed' OR 
        j.`status` = 'Merged Certificates'
    )
    AND (
        j.`assigned_tech` != 1
        OR j.`assigned_tech` IS NULL
    )
    AND (
        j.`assigned_tech` != 2
        OR j.`assigned_tech` IS NULL
    )
    ";

    $job_params = array(
        'sel_query' => $sel_query,
        'custom_where' => $custom_where,
        
        'del_job' => 0,
        'p_deleted' => 0,
        'a_status' => 'active',

        'property_id' => $compliance_row->property_id,                
        'country_id' => $country_id,

        'limit' => 1,
        'offset' => 0,

        'sort_list' => array(	
            array(
                'order_by' => 'j.`date`',
                'sort' => 'DESC'
            )
        ),
                    
        'display_query' => 0
    );

    $job_sql = $this->jobs_model->get_jobs($job_params); 
    $job_row = $job_sql->row();
    

?>
    <tr style="background-color:<?php echo ($rowcount % 2 == 0 ? "#efefef" : "") ?>">        
        <td><?php echo $paddress; ?></td>
        <td></td>        
        <td>
            <?php            
            echo ( $job_row->date != '' )?date('d/m/Y',strtotime($job_row->date)):null;
            ?>
        </td>
        <td></td>
    </tr>
<?php
    $rowcount++;
}
?>
</table>

<br />

<p>
    Can you please <a href="<?php echo $agency_portal_link; ?>">log in</a> to our Agency Portal and go to the 'Help Needed' page to view these properties and follow the instructions.<br />
    If you need any help or have questions, please contact our office on <?php echo $agent_number; ?> and speak with one of our friendly staff members.
</p>	

<br />

<p>
    Kind Regards<br />
    Smoke Alarm Testing Services.
</p>	
<!-- CONTENT END HERE -->