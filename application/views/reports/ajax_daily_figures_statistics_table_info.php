<?php 
 $tt_from = date("Y-m-d",strtotime("-7 days"));
 $tt_to = date("Y-m-d",strtotime("-1 days"));

 $start_month = date('Y-m-01'); //start month
 $end_month = date('Y-m-t'); // end month
?>
<table class="table main-table table-xs table-bordered" style="width:350px;">
    <thead>
        <tr>
            <th style="padding:5px;" class="text-center" colspan="2">Today in <?php echo $country; ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Income</strong></td>
            <td><strong><?php echo '$'.number_format($sales,2,'.',','); ?></strong></td>
        </tr>
        <tr>
            <td><strong>Average Jobs per Tech</strong></td>
            <td><?php echo (is_nan(round($jobs/$techs)))?'0':round($jobs/$techs); ?></td>
        </tr>
        <tr>
            <td><strong>Average $ per Tech</strong></td>
            <td>$<?php echo (is_numeric(($sales/$techs))) ? number_format(($sales/$techs),2,'.',',') : '0.00'  ?>	</td>
        </tr>
        <tr>
        <td><strong>Total Techs Worked</strong></td>
            <td><?php echo $techs; ?></td>
        </tr>

        <?php if($this->config->item('country')==1){ ?>
            <tr>
            <td><strong>IC Upgrade Jobs</strong></td>
                <td><?php echo number_format($total_upgrade_completed); //gherx : exclude dha ?></td>
            </tr>
            <tr>
                <td><strong>Sales Upgrade Jobs</strong></td>
                <td><?php echo number_format($total_upgrade_completed_sales); ?></td>
            </tr>
            <?php } ?>

            <tr>
                <td><strong>Total Jobs Completed</strong></td>
                <td><?php echo number_format($total_jobs_completed); ?></td>
            </tr>
            
            <?php if($this->config->item('country')==1){ ?>
            <tr>
            <td><strong>Total Upgrade Income</strong></td>
                <td><?php echo '$'.number_format($up_tot,2); //gherx: exclude dha ?></td>
            </tr>
        <?php } ?>

    </tbody>

    <thead>
        <tr>
            <th style="padding:5px;" class="text-center" colspan="2">Month to Date</th>
        </tr>
    </thead>

    <tbody>
        <tr>
            <td><strong>Income</strong></td>
            <td><strong>$<?php echo number_format($mtd_sales,2,'.',','); ?></strong></td>
        </tr>
        <tr>
            <td><strong>Monthly Target</strong></td>
            <td>$<?php echo number_format($df_budget,2,".",","); ?></td>
        </tr>
        <tr>
            <td><strong>Distance to Target</strong></td>
            <td>
                <?php 
                $dtt = $df_budget-$mtd_sales;
                echo '$'.number_format($dtt,2,".",","); 
                ?>
            </td>
        </tr>
        <tr>
            <td><strong>Working Days Left</strong></td>
            <td><?php echo $working_days_left = ($df_working_days-$working_day); ?></td>
        </tr>

        <?php if($this->config->item('country')==1){ ?>
            <tr>
                <td><strong>Total Upgrades Completed</strong></td>
                <td><?php echo $total_upgrade_completed2; ?></td>
            </tr>
            <tr>
                <td><strong>Total Upgrade Income</strong></td>
                <td><?php echo "$".number_format($this->system_model->price_ex_gst($up_tot2),2); ?></td>
            </tr>
        <?php } ?>

        <tr>
            <td><strong>Daily AVG Required</strong></td>
            <td><strong>$<?php echo is_nan($dtt/$working_days_left) ? '0.00' :  number_format($dtt/$working_days_left,2,".",","); ?></strong></td>
        </tr>
        <tr>
            <td><strong>Days Worked</strong></td>
            <td><?php echo $working_day; ?></td>
        </tr>
        <tr>
            <td><strong>Daily Average</strong></td>
            <td>$<?php echo is_nan($mtd_sales/$working_day) ? '0.00' : number_format($mtd_sales/$working_day,2,".",","); ?></td>
        </tr>
        <tr>
            <td><strong>Booked Jobs until EOM</strong></td>
            <td><?php echo $eom_booked; ?></td>
        </tr>
        <tr>
            <td><strong>Average Age (Completed)</strong></td>
            <td><?php echo $average_completed; ?></td>
        </tr>

        <tr>
            <td><strong>Total Jobs Completed</strong></td>
            <td><?php echo $total_jobs_completed_mont_to_date; ?></td>
        </tr>
    </tbody>

     <thead>
        <tr>
            <th style="padding:5px;" class="text-center" colspan="2">Statistics</th>
        </tr>
    </thead>

    <tbody>

        <tr>
            <td><strong>Total Gained (<?php echo date('F') ?>)</strong></td>
            <td>
                <?php     
                // gained      
                /* ##### Commented this tracking method to instead use Properties Gained and Lost, as per DK's request on 18th of March.
                $ps_sql_str = "
                SELECT COUNT(DISTINCT(p.property_id)) AS p_count, p.property_id
                FROM property_services AS ps
                LEFT JOIN property AS p ON ps.property_id = p.property_id	
                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
                WHERE ps.service = 1    
                AND ps.is_payable = 1 
                AND p.is_sales != 1
                AND DATE(ps.status_changed) = '".date('Y-m-d')."'
                ";
                */
                $ps_sql_str = "
                    SELECT COUNT(pt.`id`) AS p_count
                    FROM `properties_tracked` AS pt 
                    LEFT JOIN `property` AS p ON pt.`property_id` = p.`property_id`
                    LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                    WHERE pt.`date` BETWEEN '{$start_month}' AND '{$end_month}'
                    AND pt.`gained_or_lost` = 1
                ";
                $ps_sql = $this->db->query($ps_sql_str);
                $ps_sql_row = $ps_sql->row();
                echo $ps_sql_row->p_count;               
                ?>
            </td>
        </tr>
        <tr>
            <td><strong>Total Lost (<?php echo date('F') ?>)</strong></td>
            <td>
                <?php                
                // lost    
                /* ##### Commented this tracking method to instead use Properties Gained and Lost, as per DK's request on 18th of March. 
                $ps_sql_str = "
                SELECT COUNT(DISTINCT(p.property_id)) AS p_count, p.property_id
                FROM property_services AS ps
                INNER JOIN property AS p ON ps.property_id = p.property_id	
                LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id` 
                WHERE (
                    p.`property_id` NOT IN(

                        SELECT DISTINCT(p_inner.`property_id`)
                        FROM property_services AS ps_inner
                        INNER JOIN property AS p_inner ON ps_inner.property_id = p_inner.property_id	
                        WHERE ps_inner.service = 1
        
                    )
                    AND DATE(ps.`status_changed`) = '".date('Y-m-d')."'
                ) OR (
                    
                    (
                        p.`deleted` = 1 AND
                        DATE(p.`deleted_date`) = '".date('Y-m-d')."'
                    ) OR (
                        p.`is_nlm` = 1 AND
                        DATE(p.`nlm_timestamp`) = '".date('Y-m-d')."'
                    ) 

                )
                AND p.is_sales != 1
                ";      
                */    
                $ps_sql_str = "
                    SELECT COUNT(pt.`id`) AS p_count
                    FROM `properties_tracked` AS pt 
                    LEFT JOIN `property` AS p ON pt.`property_id` = p.`property_id`
                    LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                    WHERE pt.`date` BETWEEN '{$start_month}' AND '{$end_month}'
                    AND pt.`gained_or_lost` = 2
                ";          
                $ps_sql = $this->db->query($ps_sql_str);
                $ps_sql_row = $ps_sql->row();
                echo $ps_sql_row->p_count;         
                ?>
            </td>
        </tr>

        <tr>
            <td><strong>Total Properties</strong></td>
            <td>
                <?php 
                $kpi_total_query = $this->reports_model->kpi_getTotalPropertyCount()->row_array();
                echo $kpi_total_query['p_count'];
                ?>
            </td>
        </tr>
        <tr>
            <td><strong>Outstanding Jobs</strong></td>
            <td><?php echo $os_jobs['jcount']; ?></td>
        </tr>
        <tr>
            <td><strong>Outstanding Value</strong></td>
            <td><?php echo "$".number_format( $this->system_model->price_ex_gst($ov_jobs['j_price']),2); ?></td>
        </tr>
        <tr>
            <td><strong>Average Age (Not Completed)</strong></td>
            <td><?php 	echo (is_numeric(number_format(($aanc_sum_age/$aanc_jcount)))) ? number_format(($aanc_sum_age/$aanc_jcount), 2, '.', '').' days' : '0' ; ?></td>
        </tr>
    </tbody>



</table>
