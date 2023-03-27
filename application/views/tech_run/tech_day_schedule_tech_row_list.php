<tr id="<?php echo $tr_id; ?>" style="background-color:<?php echo $tr_bg_color; ?>">
    <td>
        <?php echo $time_td; ?>
    </td>    
    <td>
        <?php echo $service_td; ?>
    </td>
    <td>
        <?php echo $details_td; ?>
    </td>
    <td class="preferred_alarm_col_td">
        <?php echo $cavi_orca_td; ?>
    </td>
    <td>
        <?php echo $survey_ladder_td; ?>
    </td>
    <td>
        <?php echo $address_td; ?>
    </td>
    <td>
        <?php echo $key_td; ?>
    </td>
    <td>
        <?php echo $notes_td; ?>
    </td>    
    <td>
        <?php echo $agent_td; ?>
    </td>    
    <td>
        <?php echo $age_td; ?>
    </td> 
    <?php
    if( $show_completed_col == true ){ ?>
        <td>
            <?php echo $completed_ts; ?>
        </td>
    <?php
    }
    ?>    
    <td class="<?php echo $td_class; ?> text-right">																																																								
        <?php echo $status_td; ?>
    </td>	        
</tr>