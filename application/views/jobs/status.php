

<style>
.statistic-box a{
    color:#fff;
    display:block;
}
.statistic-box{
    margin-bottom:15px;
}
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
            'link' => "/jobs/status"
        )
    );
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>


    <?php
    
    $to_be_booked = "
    <a href='/jobs/to_be_booked'>
    <i class='user-sprites icon-tobooked'>&nbsp;</i>
    <div class='status'>".$this->jobs_model->get_job_count('To Be Booked')."</div>
    <div class='head-info visibletext allhd'>To Be Booked</div>	
    </a>
    ";

    //tbb
    $to_be_booked_2 = "
    <a href='/jobs/to_be_booked'>
    <div class='status number'>".number_format($this->jobs_model->get_job_count('To Be Booked'))."</div>
    <div class='caption'>To Be Booked</div>	
    </a>
    ";

    $renewals = "
        <a href='/jobs/service_due'>
        <i class='user-sprites icon-renewal'>&nbsp;</i>
    <div class='status'>".$this->jobs_model->get_job_count('Pending')."</div>
    <div class='head-info'>Renewals <?php echo date('F'); ?></div>	
    </a>
    ";

    $renewals_2 = "
        <a href='/jobs/service_due'>
        <div class='status number'>".number_format($this->jobs_model->get_job_count('Pending'))."</div>
        <div class='caption'>Renewals <?php echo date('F'); ?></div>	
        </a>
    ";

    $rebooks_240v = "
    <a href='/jobs/to_be_booked?job_type_filter=240v Rebook'>
        <i class='user-sprites icon-reebooks'>&nbsp;</i>
        <div class='status'>".$this->jobs_model->get_job_count('To Be Booked','240v Rebook')."</div>
        <div class='head-info visibletext allhd'>240v Rebooks</div>
    </a>
    ";

    $rebooks_240v_2 = "
    <a href='/jobs/to_be_booked?job_type_filter=240v Rebook'>
        <div class='number'>".number_format($this->jobs_model->get_job_count('To Be Booked','240v Rebook'))."</div>
        <div class='caption'>240v Rebooks</div>
    </a>
    ";

    $cot = "
    <a href='/jobs/to_be_booked?job_type_filter=".urlencode('cot & lr')."'>
        <i class='user-sprites icon-ctltr'>&nbsp;</i>
    <div class='status'>".$this->jobs_model->get_job_count('To Be Booked','Change of Tenancy','','Lease Renewal')."</div>
    <div class='head-info'>COT & LR</div>	
    </a>
    ";

    $cot_2 = "
    <a href='/jobs/cot'>
        <div class='number'>".number_format($this->jobs_model->get_job_count('To Be Booked','Change of Tenancy','','Lease Renewal'))."</div>
        <div class='caption'>COT & LR</div>	
    </a>
    ";

    $fnr = "
    <a href='/jobs/to_be_booked?job_type_filter=Fix or Replace'>
        <i class='user-sprites icon-fix'>&nbsp;</i>
    <div class='status'>".$this->jobs_model->get_job_count('To Be Booked','fix or replace')."</div>
    <div class='head-info visibletext allhd'>Fix and Replace</div>	
  
    </a>
    ";

    $fnr_2 = "
    <a href='/jobs/to_be_booked?job_type_filter=Fix or Replace'>
    <div class='number'>".number_format($this->jobs_model->get_job_count('To Be Booked','fix or replace'))."</div>
    <div class='caption'>Fix and Replace</div>	
    </a>
    ";

    $urg = "
   <a href='/jobs/to_be_booked?is_urgent=1'>
   	 <i class='user-sprites icon-blank'>&nbsp;</i>
	 <div class='status'>".$this->jobs_model->get_urgent_count()."</div>
	 <div class='head-info'>Urgent</div>	
	</a>
    ";
    
    $urg_2 = "
   <a href='/jobs/to_be_booked?is_urgent=1'>
	 <div class='number'>".number_format($this->jobs_model->get_urgent_count())."</div>
	 <div class='caption'>Urgent</div>	
	</a>
    ";
    
    // action required
	$ar = "
    <a href='#'>
         <i class='user-sprites icon-blank'>&nbsp;</i>
      <div class='status'>".$this->jobs_model->get_job_count('Action Required')."</div>
      <div class='head-info'>Action Required</div>	
     </a>
     ";

     // action required
     $ar_url = $this->config->item('crm_link')."/action_required_jobs.php";
	$ar_2 = "
    <a href=".$ar_url.">
      <div class='number'>".number_format($this->jobs_model->get_job_count('Action Required'))."</div>
      <div class='caption'>Action Required</div>	
     </a>
     ";

     // allocate
     $allocate = "
    <a href='/jobs/allocate'>
         <i class='user-sprites icon-blank'>&nbsp;</i>
      <div class='status'>".$this->jobs_model->get_job_count('Allocate')."</div>
      <div class='head-info'>Allocate</div>	
     </a>
     ";

     // allocate
     $allocate_2 = "
    <a href='/jobs/allocate'>
      <div class='number'>".number_format($this->jobs_model->get_job_count('Allocate'))."</div>
      <div class='caption'>Allocate</div>	
     </a>
     ";

    $booked = "
    <a href='#'>
        <i class='user-sprites icon-booked'>&nbsp;</i>
    <div class='status'>".$this->jobs_model->get_job_count('Booked')."</div>
    <div class='head-info'>Booked</div>	
    </a>
    ";

    //booked jobs
    $booked_jobs_url = $this->config->item('crm_link')."/booked_jobs.php";
    $booked_2 = "
    <a href=".$booked_jobs_url.">
    <div class='number'>".number_format($this->jobs_model->get_job_count('Booked'))."</div>
    <div class='caption'>Booked</div>	
    </a>
    ";

    $pre_completed = "
    <a href='#'>
        <i class='user-sprites icon-completed'>&nbsp;</i>
    <div class='status'>".$this->jobs_model->get_job_count('Pre Completion')."</div>
    <div class='head-info visibletext allhd'>Pre Completed</div>	
    <div class='head-info hiddentext allhd'>Pre Compl</div>
    </a>
    ";

    //pre completed
    $pre_completed_url = $this->config->item('crm_link')."/precompleted_jobs.php";
    $pre_completed_2 = "
    <a href=".$pre_completed_url.">
    <div class='number'>".number_format($this->jobs_model->get_job_count('Pre Completion'))."</div>
    <div class='caption'>Pre Completed</div>	
    </a>
    ";

    $send_letters = "
    <a href='#'>
        <i class='user-sprites icon-send'>&nbsp;</i>
    <div class='status'>".$this->jobs_model->get_job_count('Send Letters','',0)."</div>
    <div class='head-info visibletext allhd'>Send Letters</div>	
    <div class='head-info hiddentext allhd'>Send Letter</div>	
    </a>
    ";


    $sen_letter_url = $this->config->item('crm_link')."/send_letter_jobs.php";
    $send_letters_2 = "
    <a href=".$sen_letter_url.">
        <div class='number'>".number_format($this->jobs_model->get_job_count('Send Letters','',0))."</div>
        <div class='caption'>Send Letters</div>	
    </a>
    ";

    $merged = "
    <a href='#'>
        <i class='user-sprites icon-merged'>&nbsp;</i>
    <div class='status'>".$this->jobs_model->get_job_count('Merged Certificates')."</div>
    <div class='head-info visibletext allhd'>Merged Certificates</div>
    <div class='head-info hiddentext allhd'>Merged</div>	
    </a>
    ";


    //merge jobs
    $merge_url = $this->config->item('crm_link')."/merged_jobs.php";
    $merged_2 = "
    <a href=".$merge_url.">
        <div class='number'>".number_format($this->jobs_model->get_job_count('Merged Certificates'))."</div>
        <div class='caption'>Merged Certificates</div>
    </a>
    ";
   

    $tbi = "
    <a href='#'>
        <i class='user-sprites icon-merged'>&nbsp;</i>
    <div class='status'>".$this->jobs_model->get_job_count('To Be Invoiced')."</div>
    <div class='head-info visibletext allhd'>To Be Invoiced</div>
    <div class='head-info hiddentext allhd'>To Be Invoiced</div>	
    </a>
    ";

    //tbi
    $tbi_url = $this->config->item('crm_link')."/to_be_invoiced_jobs.php";
    $tbi_2 = "
    <a href=".$tbi_url.">
        <div class='number'>".number_format($this->jobs_model->get_job_count('To Be Invoiced'))."</div>
        <div class='caption'>To Be Invoiced</div>
    </a>
    ";

    
   $completed = "
   <a href='/view_jobs.php?status=completed'>
   	 <i class='user-sprites icon-renewal'>&nbsp;</i>
	 <div class='status'>".$this->jobs_model->get_job_count('Completed')."</div>
	 <div class='head-info'>Renewals <?php echo date('F'); ?></div>	
	</a>
    ";

    


    // agency total
    $arr = $this->system_model->getHomeTotals(); 
    $atotal = "
        <a href='#'>
        <i class='user-sprites icon-user'>&nbsp;</i>
        <div class='status'>$arr[2]</div>
        <div class='head-info'>Agencies</div>	
        </a>
    ";

    //agency total
    $agency_url = $this->config->item('crm_link')."/view_agencies.php";
    $atotal_2 = "
        <a href=".$agency_url.">
        <div class='number'>".number_format($arr[2])."</div>
        <div class='caption'>Agencies</div>	
        </a>
    ";

    
    ?>

	<section>
		<div class="body-typical-body">
		
        <div class="row">

            <!------ First COL ---->
            <div class="col-lg-3 columns">

                <?php 

                    $ajt_query = $this->db->get_where('alarm_job_type', array('active'=>1));

                    foreach ($ajt_query->result_array() as $ajt){

                        switch($ajt['id']){
                            case 2:
                                $color = 'deepred';
                                $icon = 'properties';
                                $txt = 'fdv';
                                $hidtxt = 'fdh';
                            break;
                            case 5:
                                $color = 'sprop';
                                $icon = 'swtchprop';
                                $txt = 'frdv';
                                $hidtxt = 'frdh';						
                            break;
                            case 6:
                                $color = 'lggreen';
                                $icon = 'cwprop';	
                                $txt = 'sdv';
                                $hidtxt = 'sdh';
                            break;
                            case 7:	
                                $color = 'pparty';
                                $icon = 'poolprop';
                                $txt = 'tdv';
                                $hidtxt = 'tdh';
                            break;
                            case 8:	
                                $color = 'sass';
                                $icon = 'properties';
                                $txt = 'fdv';
                                $hidtxt = 'fdh';
                            break;
                            case 11:	
                                $color = 'sawm';
                                $icon = 'properties';
                                $txt = 'fdv';
                                $hidtxt = 'fdh';
                            break;
                            default:
                                $color = 'color_purple';
                                $icon = 'properties';
                                $txt = 'fdv';
                                $hidtxt = 'fdh';
                        }


                        ?>


                            <?php
                            if($this->config->item('country')==2){
                             if($ajt['id']==2){ 
                            ?>
                           <div class="statistic-box red">
                                <div>
                                    <a href='#'>
                                        <div class='number'><?php echo number_format($this->jobs_model->get_services_total($ajt['id'])); ?></div>
                                        <div class='caption'><?php echo $ajt['type'] ?></div>	
                                    </a>
                                </div>
                            </div>
                            <?php } }else{
                                ?>
                                <div class="statistic-box red">
                                <div>
                                    <a href='#'>
                                        <div class='number'><?php echo number_format($this->jobs_model->get_services_total($ajt['id'])); ?></div>
                                        <div class='caption'><?php echo $ajt['type'] ?></div>	
                                    </a>
                                </div>
                                </div>
                            <?php
                            }?>


                            <?php if($ajt['id']==2){ 
                            ?>
                            
                            <?php if($this->config->item('country')==1){ ?>

                            <div class="statistic-box red">
                                <div>
                                    <a href='#'>
                                        <div class='number'><?php echo number_format($this->jobs_model->dha_count()); ?></div>
                                        <div class=caption>DHA Total</div>	
                                    </a>
                                </div>
                            </div>
                                
                            <?php }}?>


                <?php
                    }

                ?>

            </div>


            <!------ SECOND COL ---->
            <div class="col-lg-3 columns">
                

                <div class="statistic-box green">
                    <div>
                    <?php echo $to_be_booked_2; ?>
                    </div>
                </div>
                <div class="statistic-box green">
                <div>
					<?php echo $renewals_2; ?>
                    </div>
				</div>
				<div class="statistic-box green">
                    <div>
                        <?php echo $rebooks_240v_2; ?>
                    </div>
				</div>
				<div class="statistic-box green">
                    <div>
					<?php echo $cot_2; ?>
				    </div>
				</div>
				<div class="statistic-box green">
                    <div>
					<?php echo $fnr_2; ?>
				    </div>
				</div>
				<div class="statistic-box green">
                    <div>
					<?php echo $urg_2; ?>
				    </div>
				</div>
				<div class="statistic-box green">
                    <div>
					<?php echo $ar_2; ?>
				    </div>
				</div>
				<div class="statistic-box green">
                    <div>
					<?php echo $allocate_2; ?>
				    </div>
				</div>

                <div class="statistic-box green">
                    <div>
                        <a href='/jobs/to_be_booked/?job_type_filter=IC Upgrade'>
                            <div class='number'><?php echo $ic_upgrade_count; ?></div>
                            <div class='caption'>IC Upgrade</div>
                        </a>
                    </div>
				</div>
				<div class="statistic-box green">
                    <div>
                        <a href='/jobs/approved_alarm_numbers/?preferred_alarm_id=10'>
                            <div class='number'><?php echo $brooks_upgrade_count; ?></div>
                            <div class='caption'>Brooks Upgrades</div>
                        </a>
                    </div>
				</div>
                

            </div>



            <!------ Third COL ---->
            <div class="col-lg-3 columns">
                    
                <div class="statistic-box green">
                    <div>
                        <a href='/jobs/on_hold'>
                            <div class='number'><?php echo number_format($this->jobs_model->get_job_count('On Hold')) ?></div>
                            <div class='caption'>On Hold</div>
                        </a>
                    </div>
				</div>
				<div class="statistic-box green">
                    <div>
					<?php echo $send_letters_2; ?>
				    </div>
				</div>
				<div class="statistic-box green">
                    <div>
					<?php echo $booked_2; ?>
				    </div>
				</div>
				<div class="statistic-box green">
                    <div>
					<?php echo $pre_completed_2; ?>
				    </div>			
				</div>			
				<div class="statistic-box green">
                    <div>
                        <?php echo $merged_2; ?>
                    </div>
				</div>
				<div class="statistic-box green">
                    <div>
                        <a href='/jobs/dha'>
                            <div class='number'><?php echo number_format($this->jobs_model->get_job_count('DHA')) ?></div>
                            <div class='caption'>DHA</div>
                        </a>
                    </div>
				</div>
				
                <div class="statistic-box green">
                    <div>
					<?php echo $tbi_2; ?>
				    </div>
				</div>
				
				<div class="statistic-box green">
                    <div>
                        <a href='/jobs/escalate'>
                            <div class='number'><?php echo number_format($this->jobs_model->get_job_count('Escalate')) ?></div>
                            <div class='caption'>Escalate</div>
                        </a>
                    </div>
				</div>

                <div class="statistic-box green">
                    <div>
                        <a href='/jobs/approved_alarm_numbers/?preferred_alarm_id=14'>
                            <div class='number'><?php echo $cavius_upgrade_count; ?></div>
                            <div class='caption'>Cavius Upgrades</div>
                        </a>
                    </div>
				</div>
                <div class="statistic-box green">
                    <div>
                        <a href='/jobs/approved_alarm_numbers/?preferred_alarm_id=22'>
                            <div class='number'><?php echo $emerald_upgrade_count; ?></div>
                            <div class='caption'>Emerald Planet Upgrades</div>
                        </a>
                    </div>
				</div>								
            
            </div>
                
            <!-- FOURTH COL -->
             <div class="col-lg-3 columns">
             
                    <div class="statistic-box yellow">
                        <div>
                            <?php echo $atotal_2; ?>
                        </div>
                    </div>
                    
                    <div class="statistic-box yellow">
                        <div>
                                <a href='/agency/view_target_agencies'>
                                <div class='number'>
                                <?php
                                $ta_sql = $this->db->query("
                                    SELECT COUNT(`agency_id`) as jcount
                                    FROM `agency`
                                    WHERE `status` = 'target'
                                    AND `country_id` = {$this->config->item('country')}
                                ");
                                $ta = $ta_sql->row_array();
                                echo number_format($ta['jcount']);
                                ?>
                                </div>
                                <div class='caption'>Target Agencies</div>	
                                </a>
                        </div>
                    </div>

                     <div class="statistic-box yellow">
                        <div>
                           <a href="<?php echo $this->config->item('crm_link') ?>/view_deactivated_agencies.php">
                                <div class="number"><?php echo number_format($inactive_agency_count) ?></div>
                                <div class="caption">Inactive Agencies</div>
                           </a>
                        </div>
                    </div>

             </div>

        </div>

		</div>
	</section>

</div>


<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
    <p>This page shows an overall snapshot of some key statistics.</p>

</div>
<!-- Fancybox END -->

