<table style="width:100%; border: 1px solid #efefef;">

    <tr>
        <td style="background-color: #404041; color: #ffffff; padding: 5px;"><?php echo ( $this->config->item('country') == 1 )?'State':'Region'; ?></td>      
        <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Properties</b></td>             
        <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Service Price Total</b></td>
        <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Average</b></td>
    </tr>
    
    <?php    
    $service_price_tot = 0;    
    $property_count_tot = 0;    
    $average_price_tot = 0;
    foreach( $states_sql->result() as $states_row ){  
        
        // get postcode per state
        $postcode_sql_str = "
        SELECT pc.`postcode`
        FROM `postcode` AS pc        
        LEFT JOIN `sub_regions` AS sr ON pc.`sub_region_id` = sr.`sub_region_id`      
        LEFT JOIN `regions` AS r ON sr.`region_id` = r.`regions_id`
        WHERE pc.`deleted` = 0
        AND r.`country_id` = {$country_id}              
        AND r.`region_state` = '".$this->db->escape_str($states_row->region_state)."'
        ";
        $postcode_sql = $this->db->query($postcode_sql_str);

        // combine postcode
        $postcode_arr = [];
        foreach( $postcode_sql->result() as $postcode_row ){
            $postcode_arr[] = $postcode_row->postcode;
        }

        $postcode_imp = implode(",",$postcode_arr);
        
        ?>
        <tr>

            <td style="padding: 5px;"><?php echo $states_row->region_state; ?></td>              

            <td style="padding: 5px;">
            
                <?php 
                // property count                  
                $params = array(
                    'postcode_arr' => $postcode_arr,
                    'exc_dha' => true,
                );
                $property_count = $this->reports_model->get_sats_serviced_property_count_via_postcode($params);

                echo number_format($property_count);

                $property_count_tot += $property_count;
                ?>

            </td> 

            <?php
            // service price sum 
            $params = array(
                'postcode_arr' => $postcode_arr,
                'exc_dha' => true,
            );
            $ps_price_tot = $this->reports_model->get_sats_serviced_property_service_price_sum_via_postcode($params);
            $average_price = ($ps_price_tot/$property_count);            

            // total exc GST
            $service_price_tot += $ps_price_tot;         
            ?>
            <td style="padding: 5px;">

                <?php                                      
                echo ( $ps_price_tot > 0 )?'$'.number_format($this->system_model->price_ex_gst($ps_price_tot),2):null;
                ?>

            </td>  
            <td style="padding: 5px;">

                <?php                            
                echo ( $average_price > 0 )?'$'.number_format($this->system_model->price_ex_gst($average_price),2):null;                
                ?>

            </td>                       

        </tr>
    <?php
    }
    ?>

    <!-- TOTAL -->
    <?php    
    $average_price_tot = ($service_price_tot/$property_count_tot);
    ?>
    <tr>
        <td style="padding: 5px;">Invoice Price Ex GST</td>      

        <td>
            <b><?php echo number_format($property_count_tot); ?></b>
        </td>

        <td style="padding: 5px;">        
            <b>
                <?php                 
                echo ( $service_price_tot > 0 )?'$'.number_format($this->system_model->price_ex_gst($service_price_tot),2):null; 
                ?>
            </b>
        </td>

        <td style="padding: 5px;">          
            <b>
                <?php                 
                echo ( $average_price_tot > 0 )?'$'.number_format($this->system_model->price_ex_gst($average_price_tot),2):null; 
                ?>
            </b>
        </td>

    </tr>

</table>