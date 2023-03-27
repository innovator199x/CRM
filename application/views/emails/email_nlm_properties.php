<h3>NLM properties of <?php echo $agency_name; ?></h3>
<table style="width:100%; border: 1px solid #efefef;">
    <tr>
        <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Property ID</b></td>
        <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Property Address</b></td>                      
    </tr>    
    <?php                
    foreach( $nlm_sql->result() as $row ){        
    ?>
    <tr>
        <td style="padding: 5px;">
            <a href="<?php echo $this->config->item("crm_link"); ?>/view_property_details.php?id=<?php echo $row->property_id; ?>">
                <?php echo $row->property_id; ?>
            </a>
        </td>
        <td style="padding: 5px;"><?php echo "{$row->address_1} {$row->address_2} {$row->address_3}, {$row->state} {$row->postcode}"; ?></td>        
    </tr>
    <?php
    }    
    ?>
</table>