<?php
$first_col = 'style="text-align: left; padding-top: 10px; border-right: 1px solid #dee2e6; width:30%;"';
$second_col = 'style="text-align: left; padding-top: 10px; padding-left:10px;"';
$td_css = 'style="text-align: left; padding-top: 10px;"';
$th_css = 'style="text-align: left; padding-top: 10px;"';
$th_heading_css = 'style="text-align: left; padding: 10px 0;"';
?>
<tr>
    <th <?php echo ( $is_email == true )?$first_col:null; ?>>Name</th>	
    <td <?php echo ( $is_email == true )?$second_col:null; ?>><?php echo $staff_name; ?></td>			
</tr>  
<tr>
    <th <?php echo ( $is_email == true )?$first_col:null; ?>>Position</th>	
    <td <?php echo ( $is_email == true )?$second_col:null; ?>><?php echo $staff_position; ?></td>			
</tr> 
<tr>
    <th <?php echo ( $is_email == true )?$first_col:null; ?>>Report for date range</th>
    <td <?php echo ( $is_email == true )?$second_col:null; ?>>
        <?php 
        $start_of_week = date('d/m/Y',strtotime('monday this week')); 
        $end_of_week = date('d/m/Y',strtotime('friday this week'));  

        echo "{$start_of_week} to {$end_of_week}";
        ?>
        </td>
</tr>
<tr>
    <th <?php echo ( $is_email == true )?$first_col:null; ?>>Recent KMs</th>	
    <td <?php echo ( $is_email == true )?$second_col:null; ?> id="recent_kms"><?php echo $recent_kms; ?></td>			
</tr>  
<tr>
    <td <?php echo ( $is_email == true )?$first_col:null; ?> ></td>
    <td <?php echo ( $is_email == true )?$second_col:null; ?>></td>				
</tr>

                        
<tr>
    <th <?php echo ( $is_email == true )?$first_col:null; ?> class="jheading" <?php echo ( $is_email == true )?$th_heading_css:null; ?>>THIS WEEK SCHEDULE:</th> 
    <th <?php echo ( $is_email == true )?$second_col:null; ?>></th>                                   
</tr>                                                            
        
<tr>
    <td <?php echo ( $is_email == true )?$first_col:null; ?>>Monday <?php echo date('d/m/Y',strtotime('monday this week')); ?></td>
    <td <?php echo ( $is_email == true )?$second_col:null; ?> id="this_week_monday_cal"><?php echo $this_week_monday_cal; ?></td>
</tr>
<tr>
    <td <?php echo ( $is_email == true )?$first_col:null; ?>>Tuesday <?php echo date('d/m/Y',strtotime('tuesday this week')); ?></td>
    <td <?php echo ( $is_email == true )?$second_col:null; ?> id="this_week_tuesday_cal"><?php echo $this_week_tuesday_cal; ?></td>
</tr>
<tr>
    <td <?php echo ( $is_email == true )?$first_col:null; ?>>Wednesday <?php echo date('d/m/Y',strtotime('wednesday this week')); ?></td>
    <td <?php echo ( $is_email == true )?$second_col:null; ?> id="this_week_wednesday_cal"><?php echo $this_week_wednesday_cal; ?></td>
</tr>
<tr>
    <td <?php echo ( $is_email == true )?$first_col:null; ?>>Thursday <?php echo date('d/m/Y',strtotime('thursday this week')); ?></td>
    <td <?php echo ( $is_email == true )?$second_col:null; ?> id="this_week_thursday_cal"><?php echo $this_week_thursday_cal; ?></td>
</tr> 
<tr>
    <td <?php echo ( $is_email == true )?$first_col:null; ?>>Friday <?php echo date('d/m/Y',strtotime('friday this week')); ?></td>
    <td <?php echo ( $is_email == true )?$second_col:null; ?> id="this_week_friday_cal"><?php echo $this_week_friday_cal; ?></td>
</tr> 
<tr>
    <td <?php echo ( $is_email == true )?$first_col:null; ?> ></td>
    <td <?php echo ( $is_email == true )?$second_col:null; ?>></td>				
</tr>                        


<tr>
    <th <?php echo ( $is_email == true )?$first_col:null; ?> class="jheading" <?php echo ( $is_email == true )?$th_heading_css:null; ?>>NEXT WEEK SCHEDULE:</th>      
    <th <?php echo ( $is_email == true )?$second_col:null; ?>></th>                              
</tr>                                


<tr>
    <td <?php echo ( $is_email == true )?$first_col:null; ?>>Monday <?php echo date('d/m/Y',strtotime('monday next week')); ?></td>
    <td <?php echo ( $is_email == true )?$second_col:null; ?> id="next_week_monday_cal"><?php echo $next_week_monday_cal; ?></td>
</tr>
<tr>
    <td <?php echo ( $is_email == true )?$first_col:null; ?>>Tuesday <?php echo date('d/m/Y',strtotime('tuesday next week')); ?></td>
    <td <?php echo ( $is_email == true )?$second_col:null; ?> id="next_week_tuesday_cal"><?php echo $next_week_tuesday_cal; ?></td>
</tr>
<tr>
    <td <?php echo ( $is_email == true )?$first_col:null; ?>>Wednesday <?php echo date('d/m/Y',strtotime('wednesday next week')); ?></td>
    <td <?php echo ( $is_email == true )?$second_col:null; ?> id="next_week_wednesday_cal"><?php echo $next_week_wednesday_cal; ?></td>
</tr>
<tr>
    <td <?php echo ( $is_email == true )?$first_col:null; ?>>Thursday <?php echo date('d/m/Y',strtotime('thursday next week')); ?></td>
    <td <?php echo ( $is_email == true )?$second_col:null; ?> id="next_week_thursday_cal"><?php echo $next_week_thursday_cal; ?></td>
</tr> 
<tr>
    <td <?php echo ( $is_email == true )?$first_col:null; ?>>Friday <?php echo date('d/m/Y',strtotime('friday next week')); ?></td>
    <td <?php echo ( $is_email == true )?$second_col:null; ?> id="next_week_friday_cal"><?php echo $next_week_friday_cal; ?></td>
</tr> 
<tr>
    <td <?php echo ( $is_email == true )?$first_col:null; ?>></td>	
    <td <?php echo ( $is_email == true )?$second_col:null; ?>></td>		
</tr>   



<tr>
    <th <?php echo ( $is_email == true )?$first_col:null; ?> class="jheading" <?php echo ( $is_email == true )?$th_heading_css:null; ?>>THIS WEEKS ACTIVITY:</th>  
    <th <?php echo ( $is_email == true )?$second_col:null; ?>></th>                                  
</tr>
    

<?php
// sales activity 
$start_of_week = strtotime('monday this week'); 
$end_of_week = strtotime('friday this week');  

$sql_filter_str = null;
if( $start_of_week != '' && $end_of_week != '' ){

    $from = date("Y-m-d",$start_of_week);
    $to = date("Y-m-d",$end_of_week);
    //$sql_filter_str = "AND ael.`eventdate` BETWEEN '{$from}' AND '{$to}'";
    $sql_filter_str = "sr.`date` BETWEEN '{$from}' AND '{$to}'";
    
}
/*
// allowed agency log titles to display
$allowed_log_titles = 
array(
    "'Cold Call'",
    "'Cold Call In'",
    "'Conference'",
    "'E-mail'",            
    "'Follow up'",
    "'Happy Call'",
    "'Mailout'",
    "'Meeting'",
    "'Other'",
    "'Pack Sent'",
    "'Phone Call'",            
    "'Pop In'"
);

// combined via comma

$allowed_log_titles_imp = implode(',',$allowed_log_titles); 

$sql_str = "
    SELECT ael.`contact_type`, count(ael.`agency_event_log_id`) AS jcount
    FROM `agency_event_log` AS ael
    LEFT JOIN `agency` AS a ON ael.`agency_id` = a.`agency_id`            
    WHERE ael.`staff_id` = {$staff_id}
    AND a.`country_id` = {$this->config->item('country')}      
    AND ael.`contact_type` IN({$allowed_log_titles_imp})          
    {$sql_filter_str}
    GROUP BY ael.`contact_type`
    ORDER BY ael.`contact_type` ASC
";
$agency_logs_sql = $this->db->query($sql_str);       
*/

##AL > new query from new table (sales_report)

$sales_report_count_params = array(
    'sel_query' => "COUNT(sr.id) as jcount, sr.contact_type as contact_type_id,  mlt.contact_type as contact_type",
    'contact_type' => $al['contact_type_id'],
    'staff_id'=> $staff_id,
    'custom_where' => $sql_filter_str,
    'group_by' => 'sr.contact_type'
);
$agency_logs_sql = $this->reports_model->get_sales_report($sales_report_count_params);

##AL > new query from new table (sales_report) end

$account_log_tr = null;
if( $agency_logs_sql->num_rows() > 0 ){

    foreach( $agency_logs_sql->result() as $agency_logs_row ){ ?>        
        <tr>
            <td <?php echo ( $is_email == true )?$first_col:null; ?>><?php echo $agency_logs_row->contact_type; ?></td>
            <td <?php echo ( $is_email == true )?$second_col:null; ?>><?php echo $agency_logs_row->jcount; ?></td>
        </tr>
    <?php
    }

}else{ ?>
    <tr>
        <td <?php echo ( $is_email == true )?$first_col:null; ?>>Empty</td>	
        <td <?php echo ( $is_email == true )?$second_col:null; ?>></td>		
    </tr>
<?php
}
?>


<tr>
    <td <?php echo ( $is_email == true )?$first_col:null; ?>></td>	
    <td <?php echo ( $is_email == true )?$second_col:null; ?>></td>		
</tr>   

<tr>
    <th <?php echo ( $is_email == true )?$first_col:null; ?> class="jheading" <?php echo ( $is_email == true )?$th_heading_css:null; ?>>RESULTS:</th>                                    
    <th <?php echo ( $is_email == true )?$second_col:null; ?>></th>
</tr> 

<tr>
    <td <?php echo ( $is_email == true )?$first_col:null; ?>>This Week</td>	
    <td <?php echo ( $is_email == true )?$second_col:null; ?> id="this_week_sales_res"><?php echo $this_week_sales_res; ?></td>                          
</tr> 

<tr>
    <td <?php echo ( $is_email == true )?$first_col:null; ?>>This Month</td>	
    <td <?php echo ( $is_email == true )?$second_col:null; ?> id="this_month_sales_res"><?php echo $this_month_sales_res; ?></td>                                 
</tr> 

<tr>
    <td <?php echo ( $is_email == true )?$first_col:null; ?>></td>	
    <td <?php echo ( $is_email == true )?$second_col:null; ?>></td>		
</tr>                            

<tr>
    <th colspan="2" class="jheading" <?php echo ( $is_email == true )?$th_heading_css:null; ?>>SALES SNAPSHOT:</th>                                    
</tr>             

<tr>
    <td colspan="2" id="sales_snap_tr">

        <?php
        // sales snapshot
        $to_sales_snap = date('Y-m-d'); // today
        $from_sales_snap = date('Y-m-d',strtotime("-30 days")); 
        
        $sales_snap_sql = $this->db->query("
        SELECT 
            ss.`date`,
            ss.`properties`,
            ss.`details`,
            ss.`sales_snapshot_status_id`,
            
            a.`agency_name`,

            sr.sub_region_id as postcode_region_id,
            sr.subregion_name as postcode_region_name,
            
            sss.`name` AS status_name

        FROM `sales_snapshot` AS ss
        LEFT JOIN `agency` AS a ON ss.`agency_id` = a.`agency_id`
        LEFT JOIN `sub_regions` AS sr ON sr.sub_region_id = a.postcode_region_id
        LEFT JOIN `sales_snapshot_status` AS sss ON ss.`sales_snapshot_status_id` = sss.`sales_snapshot_status_id`
        WHERE `sales_snapshot_sales_rep_id` = {$staff_id} 
        AND CAST(ss.`date` AS Date) BETWEEN '{$from_sales_snap}' AND '$to_sales_snap'
        ");
        if( $sales_snap_sql->num_rows() > 0 ){ ?>
            <table class='table table-hover main-table' <?php echo ( $is_email == true )?'style="width:100%"':null; ?>>

                <thead>
                    <tr>
                        <th <?php echo ( $is_email == true )?$th_css:null; ?>>Date</th>
                        <th <?php echo ( $is_email == true )?$th_css:null; ?>>Agency</th>
                        <th <?php echo ( $is_email == true )?$th_css:null; ?>>Properties</th>
                        <th <?php echo ( $is_email == true )?$th_css:null; ?>><?php echo $this->customlib->getDynamicRegionViaCountry($country ); ?></th>
                        <th <?php echo ( $is_email == true )?$th_css:null; ?>>Status</th>
                        <th <?php echo ( $is_email == true )?$th_css:null; ?>>Details</th>                
                    </tr>
                </thead>

                <?php
                $total = 0;            
                foreach( $sales_snap_sql->result() as $sales_snap_row ){

                    $date = ( $this->system_model->isDateNotEmpty($sales_snap_row->date) )? date('d/m/Y', strtotime($sales_snap_row->date)) : null;
                    $region = ($sales_snap_row->postcode_region_id!='') ? $sales_snap_row->postcode_region_name : null;

                    // if Rolling Out, color it green and bold
                    if( $sales_snap_row->sales_snapshot_status_id == 3 ){
                        $sales_snap_td_val = "<b style='color:#5dca73;'>{$sales_snap_row->status_name}</b>";
                    }else{
                        $sales_snap_td_val = $sales_snap_row->status_name;
                    }
                    ?>
                        <tr>
                            <td class='snap_date'><?php echo $date; ?></td>
                            <td class='snap_agency'><?php echo $sales_snap_row->agency_name; ?></td>
                            <td class='snap_property'><?php echo $sales_snap_row->properties; ?></td>
                            <td class='snap_region'><?php echo $region; ?></td>
                            <td class='snap_status'><?php echo $sales_snap_td_val; ?></td>
                            <td class='snap_details'>
                                <?php 
                                // nl2br and pregmatch doesnt seem to work
                                echo str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"<br/>",$sales_snap_row->details); 
                                ?>
                            </td>
                        </tr>
                    <?php
                    $total += $sales_snap_row->properties;  
                }
                ?>

                <tr>
                    <td <?php echo ( $is_email == true )?$td_css:null; ?>><b>TOTAL</b></td>
                    <td <?php echo ( $is_email == true )?$td_css:null; ?>></td>
                    <td <?php echo ( $is_email == true )?$td_css:null; ?>><b><?php echo $total; ?></b></td>
                    <td <?php echo ( $is_email == true )?$td_css:null; ?>></td>
                    <td <?php echo ( $is_email == true )?$td_css:null; ?>></td>
                    <td <?php echo ( $is_email == true )?$td_css:null; ?>></td>
                </tr>

            </table>
        <?php    
        }else{ ?>
            <tr>
                <td colspan="2">Empty</td>	                
            </tr>
        <?php
        }
        ?>   
        

    </td>
</tr>  