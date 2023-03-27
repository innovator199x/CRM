<?php

if($this->config->item('country')==2){ // NZ REMOVED LR

    $job_type_arr = array(
        array(
            'full'=>'Yearly Maintenance',
            'short'=>'YM',
            'tot'=>0,
            'tot_age'=>0
        ),
        array(
            'full'=>'Change of Tenancy',
            'short'=>'COT',
            'tot'=>0,
            'tot_age'=>0
        ),
        array(
            'full'=>'Fix or Replace',
            'short'=>'FR',
            'tot'=>0,
            'tot_age'=>0
        ),
        array(
            'full'=>'Once-off',
            'short'=>'ONCE OFF',
            'tot'=>0,
            'tot_age'=>0
        ),
        array(
            'full'=>'Annual Visit',
            'short'=>'Annual',
            'tot'=>0,
            'tot_age'=>0
        )
    );

}else{ //AU

    $job_type_arr = array(
        array(
            'full'=>'Yearly Maintenance',
            'short'=>'YM',
            'tot'=>0,
            'tot_age'=>0
        ),
        array(
            'full'=>'Change of Tenancy',
            'short'=>'COT',
            'tot'=>0,
            'tot_age'=>0
        ),
        array(
            'full'=>'Fix or Replace',
            'short'=>'FR',
            'tot'=>0,
            'tot_age'=>0
        ),
            array(
            'full'=>'Lease Renewal',
            'short'=>'LR',
            'tot'=>0,
            'tot_age'=>0
        ),
        array(
            'full'=>'Once-off',
            'short'=>'ONCE OFF',
            'tot'=>0,
            'tot_age'=>0
        ),
        array(
            'full'=>'Annual Visit',
            'short'=>'Annual',
            'tot'=>0,
            'tot_age'=>0
        )
    );

    if( $ajt_id == 0){ //Add IC Upgrade 
        $ic_arr = 	array(
            'full'=>'IC Upgrade',
            'short'=>'IC Upgrade',
            'tot'=>0,
            'tot_age'=>0
        );
        array_push($job_type_arr, $ic_arr);
    }
}

?>


<link rel="stylesheet" href="/inc/css/lib/ladda-button/ladda-themeless.min.css">
<style>
    .col-mdd-3{
        max-width:15%;
    }
    .table_top_head h4{
        margin:0;
    }
    table.main-table{
        margin-bottom:30px;
    }
    .table_top_head{border-top:1px solid #fff;}
    .table_top_head .carret{
        position: absolute;
        right: 10px;
        top: 9px;
        font-size: 24px;
    }
    .ladda-button.disabled, .ladda-button:disabled{
        opacity: .65!important;
        background:#16b4fc!important;
        border:1px solid #16b4fc;
    }
    .ladda-button.disabled, .ladda-button:disabled{
        color:#fff;
    }
    button.sales_rep_cta{
        position: relative;
        width: 100%;
        text-align: left;
        padding:7px 10px;
    }
    .ladda-button[data-style="expand-right"] .ladda-spinner{
        right:25px;
    }
    .ladda-button .ladda-spinner{
        height:20px;
        width:20px;
    }
    .ctal_ad_edit_div{
        padding-top:17px;
        padding-left:10px;
    }
    .esr_block{
        margin-bottom:10px;
    }
    .ajax_shimpox{padding:20px 0px;}
    .month-selection{width: 100%}
</style>

<div class="box-typical box-typical-padding">

    <?php
    // breadcrumbs template
    $bc_items = array(
        array(
            'title' => 'Reports',
            'link' => "/reports"
        ),
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/jobs/completed_report"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

    <header class="box-typical-header">

        <div class="box-typical box-typical-padding">


            <div class="for-groupss row">
                <div class="text-left col-md-4 columns">

                    <?php // echo $this->customlib->generateLink($prev_day, $staff_filter); ?>

                </div>
                <div class="col-md-4 columns">
                <?php 
                            $form_attr = array(
                                'id' => 'jform'
                            );
                            echo form_open("jobs/completed_report/?date_from_filter=" .
                            $from . "&date_to_filter=" . $to . "&get_sats=1",$form_attr); 
                        ?>
                    <div class="row">



                                <input type="hidden" class="from" name="from" value="<?php echo $from; ?>" />
                                <input type="hidden" class="to" name="to" value="<?php echo $to; ?>" />
                                <input type="hidden" class="ajt_id" name="ajt_id" value="0" />

                            <div class="col-md-5">
                                <label>Month: </label>
                                <select id="month-filter-input" class="form-control">
                                    <?Php
                                    for ($i = 1; $i <= 12; $i++) {
                                        $dateObj = DateTime::createFromFormat('!m', $i);
                                        $monthName = $dateObj->format('F');
                                        $currentMonth = date("m");
                                        $selected = "";
                                        if (isset($_GET['date_from_filter'])) {
                                            $from_date = date_parse_from_format("Y-m-d", $_GET['date_from_filter']);
                                            $selectedMonth = $from_date['month'];
                                            if ((int) $selectedMonth === (int) $i) {
                                                $selected = 'selected="selected" ';
                                            }
                                        } else {
                                            if ((int) $currentMonth === (int) $i) {
                                                $selected = 'selected="selected" ';
                                            }
                                        }

                                        echo '<option ' . $selected . 'value="' . $i . '">' . $monthName . '</option>';
                                    }
                                    ?>
                                </select>

                            </div>


                            <div class="col-md-4">
                                <label>Year: </label>
                                <?Php
                                $maxYear = date("Y");
                                $minYear = $maxYear - 20;
                                ?>
                                <input id="year-filter-input" class="form-control" type="number" max="<?Php echo $maxYear ?>" min="<?Php echo $minYear ?>" value="<?Php echo $maxYear ?>" />
                            </div>

                            <div class="col-md-3">
                                <label>&nbsp;</label>
                                    <?Php
                                    $currentDate = date('Y-m-d');
                                    ?>

                                    <!--<input type="hidden" name="date_from_filter" id="date_from_filter" value="<?Php echo date() ?>">-->
                                    <!--<input type="hidden" name="date_to_filter" id="date_to_filter">-->
                                    <button type="button" class="btn filter-month">Filter</button>
                                    <!-- <input type="submit" name="search_submit" value="Search" class="btn"> -->


                            </div>
  

                    </div>
                    </form>  
                </div>
                <div class="text-right col-md-4 columns">

                    <?php // echo $this->customlib->generateLink($next_day, $staff_filter);  ?>


                </div>
            </div>

        </div>
    </header>

    <section>
        
        <div class="body-typical-body">
            <div class="table-responsive" style="overflow-x: hidden">

                <!-- ALL services -->
                <!-- <div class="serv_div table_top_head text-left" style="padding:0;">
                    <input type="hidden" class="job_type_id" value="<?php echo $row['id']; ?>" />
                    <button data-id="0" type="submit" class="btn ladda-button sales_rep_cta" data-style="expand-right" style="position:relative;width:100%;text-align:left;">
                        <h4 class="ladda-label">All Services</h4><span class="fa fa-caret-right carret"></span>
                    </button>

                    <input type="hidden" class="from" value="<?php echo $from; ?>" />
                    <input type="hidden" class="to" value="<?php echo $to; ?>" />
                    <input type="hidden" class="ajt_id" value="0" />
                </div>
                <div style="display:none;" class="ajax_shimpox"></div> -->

            <!-- ========================= -->

            <div class="row">
                

                    

                        <div class="col-lg-12 columns">

                            <div style="text-align:left;" class="table_top_head">Days to Complete</div>

                            <table class="table table-hover main-table table_border">
                                <thead>
                                    <tr>
                                        <th>&nbsp;</th>

                                        <?php
                                        foreach($job_type_arr as $row){
                                            echo "<td class='text-center' style='font-weight: bold'> {$row['short']} </td>";
                                        }

                                        if( $ajt_id == 0){ // Add 240v Rebook
                                            echo "<td class='text-center' style='font-weight: bold;'>240v Rebook</td>";
                                        }
                                        ?>
                                    
                                        <th class="text-center">EO
                                        <th class="text-center">Upfront Jobs
                                        <th class="text-center">Total
                                            <input type="hidden" value="<?php echo $tot = $this->jobs_model->getCompletedCount($this->input->post('from'),$this->input->post('to'),$this->input->post('ajt_id'),'',$this->config->item('country'),''); ?>" />
                                    
                                            <?php
                                            $ra_job_type_count_2_total_params = array(
                                                'sel_query' => "COUNT(DISTINCT j.id ) AS num_jobs, j.job_type",
                                                // 'from_date' => $this->input->post('from'),
                                                // 'to_date' => $this->input->post('to'),
                                                // 'ajt_id' => $this->input->post('ajt_id'),
                                                'from_date' => $from,
                                                'to_date' => $to,
                                                'ajt_id' => $ajt_id,
                                                'job_type_change' => 1
                                            );
                                                $rebook_total_without_min_max_filte_q =  $this->system_model->ra_job_type_count_2($ra_job_type_count_2_total_params)->row_array();  
                                                $tot_rebook =  $rebook_total_without_min_max_filte_q['num_jobs'];
                                            ?>

                                            <?php
                                                $upfront_job_type_count_2_total_params = array(
                                                'sel_query' => "COUNT(DISTINCT j.id ) AS num_jobs, j.job_type",
                                                // 'from_date' => $this->input->post('from'),
                                                // 'to_date' => $this->input->post('to'),
                                                // 'ajt_id' => $this->input->post('ajt_id'),
                                                'from_date' => $from,
                                                'to_date' => $to,
                                                'ajt_id' => $ajt_id,
                                                'assigned_tech' => 2
                                                );
                                                $upfront_total_without_min_max_filte_q =  $this->system_model->ra_job_type_count_2($upfront_job_type_count_2_total_params)->row_array();  
                                                $tot_upfront =  $upfront_total_without_min_max_filte_q['num_jobs'];
                                            ?>

                                            <?php
                                                $eo_job_type_count_2_total_params = array(
                                                'sel_query' => "COUNT(DISTINCT j.id ) AS num_jobs, j.job_type",
                                                // 'from_date' => $this->input->post('from'),
                                                // 'to_date' => $this->input->post('to'),
                                                // 'ajt_id' => $this->input->post('ajt_id'),
                                                'from_date' => $from,
                                                'to_date' => $to,
                                                'ajt_id' => $ajt_id,
                                                'is_eo' => 1
                                                );
                                                $eo_total_without_min_max_filte_q =  $this->system_model->ra_job_type_count_2($eo_job_type_count_2_total_params)->row_array();  
                                                //echo $this->db->last_query();
                                                $tot_eo =  $eo_total_without_min_max_filte_q['num_jobs'];
                                            ?>

                                        </th>
                                        <th class="text-center">Total %</th>
                                    </tr>
                                </thead>

                                <? if($display_data): ?>
                                <tbody>
                                    <?php

                                        $green = '#e0fde0';
                                        $orange = '#ffedcc';
                                        $red = '#ffe5e5';
                                        $red2 = '#ffb2b2';
                                        $red3 = '#ff6666';
                                        $age=array(
                                            array("min"=>0,"max"=>3,'bg_color'=>$green),
                                            array("min"=>4,"max"=>7,'bg_color'=>$green),
                                            array("min"=>8,"max"=>14,'bg_color'=>$orange),
                                            array("min"=>15,"max"=>30,'bg_color'=>$orange),
                                            array("min"=>31,"max"=>60,'bg_color'=>$red),
                                            array("min"=>61,"max"=>90,'bg_color'=>$red),
                                            array("min"=>91,"max"=>120,'bg_color'=>$red),
                                            array("min"=>121,"max"=>150,'bg_color'=>$red2),
                                            array("min"=>151,"max"=>180,'bg_color'=>$red2),
                                            array("min"=>181,"max"=>181,'bg_color'=>$red3)
                                        );							

                                        $yt_tot = 0;
                                        $cot_tot = 0;
                                        $fr_tot = 0;
                                        $lr_tot = 0;
                                        $oo_tot = 0;
                                        $tot_sm_tot = 0;
                                        $grand_total = 0;
                                        $grand_tot_percent = 0;

                                        foreach($age as $val){ 
                                            $tot_sm = 0;
                                    
                                    ?>
                                    <tr style="background-color: <?php echo $val['bg_color']; ?>;">
                                        <td class="f_col">
                                                <?php
                                                if($val['min']==$val['max']){
                                                    echo "{$val['min']}+";
                                                }else{
                                                    echo "{$val['min']}-{$val['max']}";
                                                }
                                                ?>
                                        </td>

                                        <?php
                                        // job types
                                        foreach( $job_type_arr as $index=>$job_type ){ 						
                                        ?>							
                                            <td class="text-center chops1">
                                                <?php 
                                                    echo $jt_count = $this->jobs_model->daysToComplete($from,$to,$ajt_id,$job_type['full'],$val['min'],$val['max'],$this->config->item('country')); 
                                                    // echo $jt_count = $this->jobs_model->daysToComplete($this->input->post('from'),$this->input->post('to'),$this->input->post('ajt_id'),$job_type['full'],$val['min'],$val['max'],$this->config->item('country')); 
                                                    //echo "<br /><br />";
                                                    //echo $this->db->last_query();
                                                ?>
                                            </td>
                                        <?php
                                            $job_type_arr[$index]['tot'] += $jt_count;	
                                            $tot_sm += $jt_count;
                                        }
                                        ?>	

                                        <?php 

                                        if( $ajt_id == 0){ // Add 240v Rebook
                                            $rebook_240v_count_params = array(
                                                'sel_query' => "COUNT(DISTINCT j.id ) AS num_jobs, j.job_type",
                                                // 'from_date' => $this->input->post('from'),
                                                // 'to_date' => $this->input->post('to'),
                                                // 'ajt_id' => $this->input->post('ajt_id'),
                                                'from_date' => $from,
                                                'to_date' => $to,
                                                'ajt_id' => $ajt_id,
                                                'job_type_change' => 1,
                                                'min' => $val['min'],
                                                'max' => $val['max']
                                            );
                                            $ra_job_type_count_2_q = $this->system_model->ra_job_type_count_2($rebook_240v_count_params)->row_array();
                                            //echo $this->db->last_query();
                                        ?>
                                            <td class="text-center chops">
                                                <?php 
                                                    echo $ra_job_type_count_2_q['num_jobs'];
                                                    $rebook_240v_tot_completed += $ra_job_type_count_2_q['num_jobs'];
                                                ?>
                                            </td>
                                        <?php
                                        }
                                        ?>

                                        <?php 

                                        if( $ajt_id == 0){ // Add EO Jobs
                                            $eo_jobs_count_params = array(
                                                'sel_query' => "COUNT(DISTINCT j.id ) AS num_jobs, j.job_type",
                                                // 'from_date' => $this->input->post('from'),
                                                // 'to_date' => $this->input->post('to'),
                                                // 'ajt_id' => $this->input->post('ajt_id'),
                                                'from_date' => $from,
                                                'to_date' => $to,
                                                'ajt_id' => $ajt_id,
                                                'is_eo' => 1,
                                                'min' => $val['min'],
                                                'max' => $val['max']
                                            );
                                            $eo_jobs__count_2_q = $this->system_model->ra_job_type_count_2($eo_jobs_count_params)->row_array();
                                            //echo $this->db->last_query();
                                            //exit();

                                        ?>
                                            <td class="text-center chops">
                                                <?php 
                                                    echo $eo_jobs__count_2_q['num_jobs'];
                                                    $eo_jobs_tot_completed += $eo_jobs__count_2_q['num_jobs'];
                                                ?>
                                            </td>
                                        <?php
                                        }
                                        ?>

                                        <?php 

                                        if( $ajt_id == 0){ // Upfront Jobs
                                            $up_jobs_count_params = array(
                                                'sel_query' => "COUNT(DISTINCT j.id ) AS num_jobs, j.job_type",
                                                // 'from_date' => $this->input->post('from'),
                                                // 'to_date' => $this->input->post('to'),
                                                // 'ajt_id' => $this->input->post('ajt_id'),
                                                'from_date' => $from,
                                                'to_date' => $to,
                                                'ajt_id' => $ajt_id,
                                                'assigned_tech' => 2,
                                                'min' => $val['min'],
                                                'max' => $val['max']
                                            );
                                            $up_jobs__count_2_q = $this->system_model->ra_job_type_count_2($up_jobs_count_params)->row_array();
                                            //echo $this->db->last_query();
                                            //exit();

                                        ?>
                                            <td class="text-center chops">
                                                <?php 
                                                    echo $up_jobs__count_2_q['num_jobs'];
                                                    $up_jobs_tot_completed += $up_jobs__count_2_q['num_jobs'];
                                                ?>
                                            </td>
                                        <?php
                                        }
                                        ?>

                                        <td class="text-center total">
                                            <?php 
                                            echo $tot_sm_merge =  $tot_sm;
                                            //echo $tot_sm_merge =  $tot_sm+$ra_job_type_count_2_q['num_jobs'];
                                            //echo $tot_sm;
                                            //$grand_total += $tot_sm_merge;
                                            //$tot2 = $tot+$tot_rebook;
                                            $grand_total += $tot_sm;
                                            $tot2 = $tot;
                                            ?>
                                        </td>

                                        <td class="text-center chops">
                                            <?php 
                                            
                                                if( $tot2 > 0 ){
                                                    $tot_percent = number_format((($tot_sm_merge/$tot2)*100), 2, '.', ''); 
                                                }else{
                                                    $tot_percent = 0;
                                                }									
                                                echo "{$tot_percent}%";
                                                //echo (is_nan($tot_percent))?$tot_percent:0.00 ."%";
                                                $grand_tot_percent += $tot_percent;
                                            ?> 
                                        </td>	

                                    </tr>

                                    <?php } ?>
                                    <tr style="background-color:#DDDDDD">

                                            <td class="f_col"><strong>TOTAL COMPLETED</strong></td>		
                                            <?php
                                            // job types
                                            foreach( $job_type_arr as $index=>$job_type ){ 
                                            $yt_tot_age = 0;
                                            ?>
                                                <td class="text-center">
                                                    <strong><?php echo $job_type['tot']; ?></strong>
                                                    <?php
                                                    $asql = $this->jobs_model->getCompletedCount($from,$to,$ajt_id,$job_type['full'],$this->config->item('country'),1);
                                                    // $asql = $this->jobs_model->getCompletedCount($this->input->post('from'),$this->input->post('to'),$this->input->post('ajt_id'),$job_type['full'],$this->config->item('country'),1);
                                                    //echo $this->db->last_query();

                                                    foreach($asql->result_array() as $a){
                                                        $date1=date_create($a['jcreated']);
                                                        $date2=date_create($a['date']);
                                                        $diff=date_diff($date1,$date2);
                                                        $yt_tot_age += $diff->format("%a");
                                                    }
                                                    ?>
                                                    <?php //echo $yt_tot_age; ?>
                                                    <input type="hidden" value="<?php echo $job_type_arr[$index]['tot_age'] = $yt_tot_age; ?>" />
                                                </td>
                                            <?php	
                                            }
                                            ?>	

                                            <?php 

                                                if( $ajt_id == 0){ // Add 240v Rebook Total Completed
                                            ?>
                                                    <td class="text-center" style="font-weight: bold">
                                                        <?php echo $rebook_240v_tot_completed; ?>

                                                        <?php
                                                            $ra_job_type_count_2_total_age_params = array(
                                                                'sel_query' => " DISTINCT(j.id), CAST( j.`created` AS DATE ) AS jcreated, j.`date`",
                                                                // 'sel_query' => "CAST( j.`created` AS DATE ) AS jcreated, j.`date`",
                                                                // 'from_date' => $this->input->post('from'),
                                                                // 'to_date' => $this->input->post('to'),
                                                                // 'ajt_id' => $this->input->post('ajt_id'),
                                                                'from_date' => $from,
                                                                'to_date' => $to,
                                                                'ajt_id' => $ajt_id,
                                                                'job_type_change' => 1
                                                            );
                                                            $rebook_tot_age_q =  $this->system_model->ra_job_type_count_2($ra_job_type_count_2_total_age_params)->result_array();  
                                                            foreach($rebook_tot_age_q as $b){
                                                                $r_date1=date_create($b['jcreated']);
                                                                $r_date2=date_create($b['date']);
                                                                $r_diff=date_diff($r_date1,$r_date2);
                                                                $rebook_tot_age += $r_diff->format("%a");

                                                                
                                                            }
                                                        ?>
                                                <input type="hidden" value="<?php echo $rebook_tot_age; ?>" />
                                                </td>
                                            <?php
                                                }
                                            ?>
                                            
                                            <?php 
                                                if( $ajt_id == 0){ // Add EO jobs Total Completed
                                            ?>
                                                    <td class="text-center" style="font-weight: bold">
                                                        <?php echo $eo_jobs_tot_completed; ?>

                                                        <?php
                                                            $eo_job_type_count_2_total_age_params = array(
                                                                'sel_query' => "CAST( j.`created` AS DATE ) AS jcreated, j.`date`",
                                                                // 'from_date' => $this->input->post('from'),
                                                                // 'to_date' => $this->input->post('to'),
                                                                // 'ajt_id' => $this->input->post('ajt_id'),
                                                                'from_date' => $from,
                                                                'to_date' => $to,
                                                                'ajt_id' => $ajt_id,
                                                                'is_eo' => 1
                                                            );
                                                            $eo_jobs_tot_age_q =  $this->system_model->ra_job_type_count_2($eo_job_type_count_2_total_age_params)->result_array();  
                                                            //echo $this->db->last_query();

                                                            foreach($eo_jobs_tot_age_q as $b){
                                                                $r_date1=date_create($b['jcreated']);
                                                                $r_date2=date_create($b['date']);
                                                                $r_diff=date_diff($r_date1,$r_date2);
                                                                $eo_jobs_tot_age += $r_diff->format("%a");
                                                            }
                                                        ?>
                                                        <input type="hidden" value="<?php echo $eo_jobs_tot_age; ?>" />
                                                    </td>
                                            <?php
                                                }
                                            ?>

                                            <?php 
                                                if( $ajt_id == 0){ // Add Upfront jobs Total Completed
                                            ?>
                                                <td class="text-center" style="font-weight: bold">
                                                    <?php echo $up_jobs_tot_completed; ?>

                                                    <?php
                                                        $up_job_type_count_2_total_age_params = array(
                                                            'sel_query' => "CAST( j.`created` AS DATE ) AS jcreated, j.`date`",
                                                            // 'from_date' => $this->input->post('from'),
                                                            // 'to_date' => $this->input->post('to'),
                                                            // 'ajt_id' => $this->input->post('ajt_id'),
                                                            'from_date' => $from,
                                                            'to_date' => $to,
                                                            'ajt_id' => $ajt_id,
                                                            'assigned_tech' => 2
                                                        );
                                                        $up_jobs_tot_age_q =  $this->system_model->ra_job_type_count_2($up_job_type_count_2_total_age_params)->result_array();  
                                                        //echo $this->db->last_query();

                                                        foreach($up_jobs_tot_age_q as $b){
                                                            $r_date1=date_create($b['jcreated']);
                                                            $r_date2=date_create($b['date']);
                                                            $r_diff=date_diff($r_date1,$r_date2);
                                                            $up_jobs_tot_age += $r_diff->format("%a");
                                                        }
                                                    ?>
                                                    <input type="hidden" value="<?php echo $up_jobs_tot_age; ?>" />
                                                </td>
                                            <?php
                                                }
                                            ?>

                                            <td class="text-center grandtotal"><strong><?php echo $grand_total; ?></strong></td>
                                            <td class="text-center">
                                                <strong>
                                                    <?php 
                                                    if($grand_tot_percent > 100){
                                                        $grand_tot_percent = 100;
                                                    }
                                                    echo $grand_tot_percent; 
                                                    ?> %
                                                </strong>
                                            </td>
                                    </tr>
                                </tbody>
                                <? else: ?>
                                    <?= "<tr><td colspan='13'>Press 'Filter' to display data</td></tr>"; ?>
                                <? endif; ?>
                            </table>
                        </div>
                        <? if($display_data): ?>
                        <div  class="col-lg-12 columns">
                            <div style="text-align:left;" class="table_top_head">Average Days to Complete</div>
                            <table class="table table-hover main-table table_border">
                                <thead>
                                    <tr>
                                        <th>&nbsp;</th>
                                        <?php
                                        // job types
                                        foreach( $job_type_arr as $job_type ){ ?>
                                            <th class="text-center"><?php echo $job_type['short'] ?></th>
                                        <?php	
                                        }
                                        ?>
                                        <?php if( $ajt_id == 0){
                                            echo "<th class='text-center'>240v Rebook</th>";
                                            echo "<th class='text-center'>EO</th>";
                                            echo "<th class='text-center'>Upfront Jobs</th>";
                                        } ?>
                                    </tr>
                                </thead>
                                
                                <tbody>
                                    <tr>	
                                        <?php $ctr = count($age); ?>
                                        <td class="f_col">Average Days</td>

                                        <?php
                                        // job types
                                        foreach( $job_type_arr as $job_type ){ ?>
                                            <td class="text-center">
                                                <?php 
                                                echo (!is_nan($job_type['tot_age']/$job_type['tot']))?number_format(($job_type['tot_age']/$job_type['tot']), 2):'0.00'; 
                                                ?>
                                            </td>
                                        <?php	
                                            }
                                        ?>

                                        <?php if( $ajt_id == 0){ ?>
                                            <td class="text-center">
                                                <?php echo (!is_nan($rebook_tot_age/$tot_rebook))?number_format(($rebook_tot_age/$tot_rebook), 2):'0.00'; ?>
                                            </td>
                                        <?php
                                            }
                                        ?>

                                        <?php if( $ajt_id == 0){ ?>
                                            <td class="text-center">
                                                <?php echo (!is_nan($eo_jobs_tot_age/$tot_eo))?number_format(($eo_jobs_tot_age/$tot_eo), 2):'0.00'; ?>
                                            </td>
                                        <?php
                                            }
                                        ?>

                                        <?php if( $ajt_id == 0){ ?>
                                            <td class="text-center">
                                                <?php echo (!is_nan($up_jobs_tot_age/$tot_upfront))?number_format(($up_jobs_tot_age/$tot_upfront), 2):'0.00'; ?>
                                            </td>
                                        <?php
                                            }
                                        ?>

                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <? endif; ?>



            </div>

            <!-- ========================= -->

                <?php
                // Alarm Job Types
                foreach ($ajt->result_array() as $row) {

                    if ($this->config->item('country') == 2) { //NZ removed other services
                        if ($row['id'] == 2) {
                            ?>
                            <!--
                            <div class="serv_div table_top_head text-left" style="padding:0;">
                                <input type="hidden" class="job_type_id" value="<?php echo $row['id']; ?>" />
                                <button data-id="<?php echo $row['id'] ?>" type="submit" class="btn ladda-button sales_rep_cta" data-style="expand-right" style="position:relative;width:100%;text-align:left;">
                                    <h4 class="ladda-label"><?php echo $row['type'] ?></h4><span class="fa fa-caret-right carret"></span>
                                </button>

                                <input type="hidden" class="from" value="<?php echo $from; ?>" />
                                <input type="hidden" class="to" value="<?php echo $to; ?>" />
                                <input type="hidden" class="ajt_id" value="<?php echo $row['id']; ?>" />
                            </div>
                            <div style="display:none;" class="ajax_shimpox"></div>
                            -->

                            <?php
                        }
                    } else { //AU display all services
                        ?>
                        <!--
                        <div class="serv_div table_top_head text-left" style="padding:0;">
                            <input type="hidden" class="job_type_id" value="<?php echo $row['id']; ?>" />
                            <button data-id="<?php echo $row['id'] ?>" type="submit" class="btn ladda-button sales_rep_cta" data-style="expand-right" style="position:relative;width:100%;text-align:left;">
                                <h4 class="ladda-label"><?php echo $row['type'] ?></h4><span class="fa fa-caret-right carret"></span>
                            </button>

                            <input type="hidden" class="from" value="<?php echo $from; ?>" />
                            <input type="hidden" class="to" value="<?php echo $to; ?>" />
                            <input type="hidden" class="ajt_id" value="<?php echo $row['id']; ?>" />
                        </div>
                        <div style="display:none;" class="ajax_shimpox"></div>
                        -->
                        <?php
                    }
                }
                ?>

            </div>



        </div>
    </section>



</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

    <h4><?php echo $title; ?></h4>
    <p>This page displays statistics on jobs that are completed.</p>
<pre><code>SELECT count( j.`id` ) AS jtot
FROM `jobs` AS j
LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
WHERE j.`status` = 'Completed'
AND a.`country_id` = 1
AND p.`deleted` = 0
AND a.`status` = 'active'
AND j.`del_job` = 0
AND j.`service` = 2
AND j.`job_type` = 'Yearly Maintenance' 
AND (
j.`date` 
BETWEEN $from_date
AND $to_date
)
AND ( CAST( j.`created` AS DATE ) BETWEEN DATE_SUB( j.`date` , INTERVAL 3 DAY ) AND DATE_SUB( j.`date` , INTERVAL 0 DAY ) )</code>
    </pre>

</div>
<!-- Fancybox END -->

<script src="/inc/js/lib/ladda-button/spin.min.js"></script>
<script src="/inc/js/lib/ladda-button/ladda.min.js"></script>	
<script type="text/javascript">

    jQuery(document).ready(function () {

        $('button.filter-month').on('click', function () {
            var month = $('#month-filter-input').val();
            var year = $('#year-filter-input').val();
            var fromDate = new Date(month + '/01/' + year);
            var toDate = new Date(fromDate.getFullYear(), fromDate.getMonth() + 1, 0);
            var param_from_date = year + '-' + (fromDate.getMonth() + 1) + '-01';
            var param_to_date = year + '-' + (toDate.getMonth() + 1) + '-' + toDate.getDate();
            var redirect_link = "/jobs/completed_report/?date_from_filter=" +
                    param_from_date + "&date_to_filter=" + param_to_date + "&get_sats=1" + "&display_data=true";
            console.log(redirect_link);
            window.location = redirect_link;
        });


        // $('.sales_rep_cta').on('click', function (e) {
        //     e.preventDefault();
        //     var l = Ladda.create(this);
        //     var obj = $(this);
        //     var id = obj.attr('data-id')
        //     var target_div = obj.parents('.table_top_head').next('.ajax_shimpox');
        //     var isActive = obj.attr('data-active');


        //     var from = obj.parents(".serv_div").find(".from").val();
        //     var to = obj.parents(".serv_div").find(".to").val();
        //     var ajt_id = obj.parents(".serv_div").find(".ajt_id").val();


        //     obj.toggleClass('snap_active');


        //     if (obj.hasClass('snap_active')) {
        //         obj.find('.carret').removeClass('fa-caret-right').addClass('fa-caret-down');
        //         target_div.slideDown();
        //     } else {
        //         obj.find('.carret').removeClass('fa-caret-down').addClass('fa-caret-right');
        //         target_div.slideUp();
        //     }


        //     if (isActive != 1) {

        //         l.start();
        //         target_div.load('/jobs/ajax_completed_report', {
        //             ajt_id: ajt_id, from: from, to: to
        //         }, function (response, status, xhr) {
        //             l.stop();
        //             target_div.slideDown();
        //             obj.attr('data-active', 1);
        //         }
        //         );

        //     }


        // })


    })

</script>