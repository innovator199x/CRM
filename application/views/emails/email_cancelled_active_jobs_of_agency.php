<h3>Cancelled Active Jobs of <?php echo $agency_name; ?></h3>
<table style="width:100%; border: 1px solid #efefef;">
    <tr>
        <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Job ID</b></td>
        <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Job Type</b></td> 
        <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Job Status</b></td>  
        <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Property Address</b></td>                      
    </tr>    
    <?php                
    foreach( $job_sql->result() as $job_row ){        
    ?>
    <tr>
        <td style="padding: 5px;">
            <a href="<?php echo $this->config->item("crm_link"); ?>/view_job_details.php?id=<?php echo $job_row->jid; ?>">
                <?php echo $job_row->jid; ?>
            </a>
        </td>
        <td style="padding: 5px;"><?php echo $job_row->job_type; ?></td>
        <td style="padding: 5px;"><?php echo $job_row->jstatus; ?></td>
        <td style="padding: 5px;"><?php echo "{$job_row->address_1} {$job_row->address_2} {$job_row->address_3}, {$job_row->state} {$job_row->postcode}"; ?></td>        
    </tr>
    <?php
    }    
    ?>
</table>