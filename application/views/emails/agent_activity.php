<!-- CONTENT START HERE -->

<?php
if( $dist_agency_sql->num_rows() > 0 ){ ?>

<table style="width:100%; border: 1px solid #efefef;">
    <tr>
        <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Agency</b></td>
        <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Added By SATS</b></td>
        <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Added By Agency</b></td>
        <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Gained</b></td>
        <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Lost</b></td>
        <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Net</b></td>
    </tr>
    <?php
    $rowcount = 0;
    $serv_tot_arr = [];
    $i = 0;
    foreach( $dist_agency_sql->result() as $agency_row ){
    ?>
        <tr style="background-color:<?php echo ($rowcount % 2 == 0 ? null : "#efefef") ?>">        
            <td style="padding: 5px;"><?php echo $agency_row->agency_name; ?></td> 
            <td style="padding: 5px;">
                <?php
                // added by SATS if added_by user ID exist
                $pt_sql_str = "
                    SELECT COUNT(pt.`id`) AS pt_count
                    FROM `properties_tracked` AS pt 
                    LEFT JOIN `property` AS p ON pt.`property_id` = p.`property_id`
                    LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                    WHERE a.`agency_id` = {$agency_row->agency_id}
                    AND pt.`date` BETWEEN '{$from}' AND '{$to}'
                    AND pt.`gained_or_lost` = 1
                    AND p.`added_by` > 0
                ";
                $pt_sql = $this->db->query($pt_sql_str);
                $pt_row = $pt_sql->row();
                echo $added_by_sats_count = $pt_row->pt_count; 
                ?>
            </td>           
            <td style="padding: 5px;">
                <?php
                // added by Agency if added_by user ID doesn't exist
                $pt_sql_str = "
                    SELECT COUNT(pt.`id`) AS pt_count
                    FROM `properties_tracked` AS pt 
                    LEFT JOIN `property` AS p ON pt.`property_id` = p.`property_id`
                    LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                    WHERE a.`agency_id` = {$agency_row->agency_id}
                    AND pt.`date` BETWEEN '{$from}' AND '{$to}'
                    AND pt.`gained_or_lost` = 1
                    AND p.`added_by` <= 0
                ";
                $pt_sql = $this->db->query($pt_sql_str);
                $pt_row = $pt_sql->row();
                echo $added_by_agency_count = $pt_row->pt_count; 
                ?>
            </td>
            <td style="padding: 5px;">
                <?php
                // property gained
                $pt_sql_str = "
                    SELECT COUNT(pt.`id`) AS pt_count
                    FROM `properties_tracked` AS pt 
                    LEFT JOIN `property` AS p ON pt.`property_id` = p.`property_id`
                    LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                    WHERE a.`agency_id` = {$agency_row->agency_id}
                    AND pt.`date` BETWEEN '{$from}' AND '{$to}'
                    AND pt.`gained_or_lost` = 1
                ";
                $pt_sql = $this->db->query($pt_sql_str);
                $pt_row = $pt_sql->row();
                echo $total_new = $pt_row->pt_count;                
                ?>
            </td>
            <td style="padding: 5px;">
                <?php
                // property lost
                $pt_sql_str = "
                    SELECT COUNT(pt.`id`) AS pt_count
                    FROM `properties_tracked` AS pt 
                    LEFT JOIN `property` AS p ON pt.`property_id` = p.`property_id`
                    LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                    WHERE a.`agency_id` = {$agency_row->agency_id}
                    AND pt.`date` BETWEEN '{$from}' AND '{$to}'
                    AND pt.`gained_or_lost` = 2
                ";
                $pt_sql = $this->db->query($pt_sql_str);
                $pt_row = $pt_sql->row();
                echo $deleted_count = $pt_row->pt_count;                
                ?>
            </td>            
            <td style="padding: 5px;text-align:center;">
                <?php 
                    $net = $total_new-$deleted_count; 
                    echo ( $net < 0 )?'<span style="color:red">'.$net.'</span>':$net;
                ?>
            </td>
        </tr>
    <?php
        // total
        $added_by_sats_count_tot += $added_by_sats_count;        
        $added_by_agency_count_tot += $added_by_agency_count;
        $total_new_tot += $total_new;
        $deleted_tot += $deleted_count;        
        $net_tot += $net;	
        
        $rowcount++;
    }

    // percentage 
    $added_by_sats_percentage = ($added_by_sats_count_tot/$total_new_tot)*100;
    $added_by_agency_percentage = ($added_by_agency_count_tot/$total_new_tot)*100;
    ?>
    <tr style="background-color:#efefef">
        <td style="padding: 5px;"><strong>TOTAL</strong></td>
        <td style="padding: 5px;"><?php echo ( $added_by_sats_count_tot > 0 )?$added_by_sats_count_tot:null; ?> <?php echo ( $added_by_sats_percentage > 0 )?'('.number_format($added_by_sats_percentage, 2, '.', '').'%)':null; ?></td>
        <td style="padding: 5px;"><?php echo ( $added_by_agency_count_tot > 0 )?$added_by_agency_count_tot:null; ?> <?php echo ( $added_by_agency_percentage > 0 )?'('.number_format($added_by_agency_percentage, 2, '.', '').'%)':null; ?></td>
        <td style="padding: 5px;"><?php echo ( $total_new_tot > 0 )?$total_new_tot:null; ?></td>
        <td style="padding: 5px;"><?php echo ( $deleted_tot > 0 )?$deleted_tot:null; ?></td>
        <td style="padding: 5px;text-align:center;">
            <?php
            echo ( $net_tot < 0 )?'<span style="color:red">'.$net_tot.'</span>':$net_tot;    
            ?>
        </td>
    </tr>
</table>

<?php
}
?>

<!-- CONTENT END HERE -->