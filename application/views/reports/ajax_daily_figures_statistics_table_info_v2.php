<style>
.jgreyed_out{
    background: #f6f8fa;
}
</style>
<h4>*NEW</h4>
<table class="table main-table table-xs table-bordered" style="width:350px;">

    <!-- JOBS -->
    <thead>
        <tr>
            <th style="padding:5px;" class="text-center" colspan="2">JOBS</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Jobs Completed</strong></td>
            <td>
                <?php
                /*$jobs_sql_str = "
                    SELECT COUNT(`id`) AS jcount
                    FROM `jobs`
                    WHERE `status` = 'Merged Certificates'
                    AND `del_job` = 1
                    AND `assigned_tech` NOT IN(1,2)
                    AND `job_type` != 'IC Upgrade'
                ";
                $job_sql = $this->db->query($jobs_sql_str);
                echo $job_sql->row()->jcount;*/
                $custom_where = "j.status = 'Merged Certificates' AND j.assigned_tech NOT IN(1,2) AND j.job_type!='IC Upgrade'";
                $jobsparams = array(
                    'sel_query' => "COUNT(j.id) AS jcount",
                    'custom_where_arr' => array($custom_where),
                    'p_deleted' => 0,
                    'del_job' => 1,
                    'a_status' => 'active',
                    'country_id' => COUNTRY
                );
                $job_sql = $this->jobs_model->get_jobs($jobsparams);
                echo $job_sql->row()->jcount;
                ?>
            </td>
        </tr>
        <?php if($this->config->item('country')==1){ ?>
            <tr>
                <td><strong>Upgrades Completed</strong></td>
                <td><?php echo $total_upgrade_completed2; ?></td>
            </tr>            
        <?php } ?>
        <tr>
            <td><strong>Total Jobs Completed</strong></td>
            <td>
                <?php
                /*$jobs_sql_str = "
                    SELECT COUNT(`id`) AS jcount
                    FROM `jobs`
                    WHERE `status` = 'Merged Certificates'
                    AND `del_job` = 1
                    AND `assigned_tech` NOT IN(1,2)                    
                ";
                $job_sql = $this->db->query($jobs_sql_str);
                echo $job_sql->row()->jcount;*/
                $custom_where_2 = "j.status = 'Merged Certificates' AND j.assigned_tech NOT IN(1,2)";
                $jobsparams_2 = array(
                    'sel_query' => "COUNT(j.id) AS jcount",
                    'custom_where_arr' => array($custom_where_2),
                    'p_deleted' => 0,
                    'del_job' => 1,
                    'a_status' => 'active',
                    'country_id' => COUNTRY
                );
                $job_sql2 = $this->jobs_model->get_jobs($jobsparams_2);
                echo $job_sql2->row()->jcount;
                ?>
            </td>
        </tr>
        <tr class="jgreyed_out">
            <td><strong>Average Jobs per Day</strong></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Days Worked</strong></td>
            <td><?php echo $working_day; ?></td>
        </tr>
        <tr>
            <td><strong>Average Jobs per Tech</strong></td>
            <td>
                <?php      
                    echo (is_nan(round($jobs_exc_ub_os/$techs)))?'0':round($jobs_exc_ub_os/$techs);                     
                ?>
            </td>
        </tr>
        <tr class="jgreyed_out">
            <td><strong>Working Days Complete</strong></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Booked jobs until EOM</strong></td>
            <td><?php echo $eom_booked; ?></td>
        </tr>
        <tr>
            <td><strong>Outstanding Jobs</strong></td>
            <td><?php echo $os_jobs['jcount']; ?></td>
        </tr>
        <tr>
            <td><strong>Average Age (Completed)</strong></td>
            <td><?php echo $average_completed; ?></td>
        </tr>
        <tr>
            <td><strong>Average Age (Not Completed)</strong></td>
            <td><?php 	echo (is_numeric(number_format(($aanc_sum_age/$aanc_jcount)))) ? number_format(($aanc_sum_age/$aanc_jcount), 2, '.', '').' days' : '0' ; ?></td>
        </tr>
    </tbody>  
    
    
    <!-- PROPERTIES -->
    <thead>
        <tr>
            <th style="padding:5px;" class="text-center" colspan="2">PROPERTIES</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Properties</strong></td>
            <td>
                <?php 
                $prop_row = $this->reports_model->get_total_properties_excluding_dha()->row();
                echo $prop_row->p_count;
                ?>
            </td>
        </tr>
        <tr class="jgreyed_out">
            <td><strong>Sales</strong></td>
            <td></td>
        </tr>
        <tr class="jgreyed_out">
            <td><strong>Lost</strong></td>
            <td></td>
        </tr>        
    </tbody>


    <!-- INCOME -->
    <thead>
        <tr>
            <th style="padding:5px;" class="text-center" colspan="2">INCOME</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Subs Income</strong></td>
            <td><?php echo '$'.number_format($sales_ub_os_only,2,'.',','); ?></td>
        </tr>
        <tr>
            <td><strong>Jobs Income</strong></td>
            <td><?php echo '$'.number_format($sales_exc_ic_up,2,'.',','); ?></td>
        </tr>
        <tr>
            <td><strong>Upgrades Income</strong></td>
            <td><?php echo '$'.number_format($sales_ic_up_only,2,'.',','); ?></td>
        </tr> 
        <tr>
            <td><strong>Total Income</strong></td>
            <td><?php echo '$'.number_format($sales,2,'.',','); ?></td>
        </tr>
        <tr>
            <td><strong>Average $ Per Tech Income</strong></td>
            <td>$<?php echo (is_numeric(($sales/$techs))) ? number_format(($sales/$techs),2,'.',',') : '0.00'; ?></td>
        </tr>
        <tr>
            <td><strong>Average Subs income</strong></td>
            <td>$<?php echo (is_numeric(($sales_ub_os_only/$sales_ub_os_only_count))) ? number_format(($sales_ub_os_only/$sales_ub_os_only_count),2,'.',',') : '0.00'; ?></td>
        </tr>
        <tr>
            <td><strong>Average Upgrade income</strong></td>
            <td>$<?php echo (is_numeric(($sales_ic_up_only/$sales_ic_up_only_count))) ? number_format(($sales_ic_up_only/$sales_ic_up_only_count),2,'.',',') : '0.00'; ?></td>
        </tr>
        <tr>
            <td><strong>Average Total income</strong></td>
            <td>$<?php echo (is_numeric(($sales/$sales_count))) ? number_format(($sales/$sales_count),2,'.',',') : '0.00'; ?></td>
        </tr>
        <tr class="jgreyed_out">
            <td><strong>Average Daily Income</strong></td>
            <td></td>
        </tr>       
    </tbody>


    <!-- ACCOUNTS -->
    <!--
    <thead>
        <tr>
            <th style="padding:5px;" class="text-center" colspan="2">ACCOUNTS</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Payments</strong></td>
            <td></td>
        </tr>
        <tr>
            <td><strong>Credits</strong></td>
            <td></td>
        </tr>               
    </tbody>
    -->

</table>
