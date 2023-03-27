<style>
.table_top_head{
    width:110px;
    cursor: pointer;
    margin-top: 3px;
}
.vts_main_div,
.key_num_span,
.time_div_toggle,
.vts_collapse_div{
    display: none; 
}
tr.ts_1{
    background-color:#c2ffa7;
}
.vts_list_section{
    margin-bottom: 20px;
}

/* TDS tech css */
img.img_pnotes,
img.key_icon {
    cursor: pointer;
}

/* keys lightbox css */
.job_reason,
.reason_comment{
    width: 257px;
}
.agency_keys_lb{
    width: 60%;
}
.job_reason_div .form-control{
    margin: 5px 0;
}
.key_num_th{
    width: 15%;
}
.is_keys_picked_up_yes:checked + label::after{
    background: #00e600 !important;	
}
.is_keys_picked_up_no:checked + label::after{
    background: #ff0000 !important;	
}
.agency_keys_lb .second_div .row{
    margin-bottom: 14px;
}
.agency_keys_lb .second_div label {
    position: relative;
    top: 11px;
}
.paddress_th{
    width: 40%;
}
.keys_picked_up_th{
    width: 26%;
}
.tds_tbl button.btn{
    width: 112px !important;
}    
.vts_calendar_link{
    position: absolute;
    right: 235px;
    top: 13px;
}

/* about text */
.about_page_li li {
    padding-top: 15px;
}
.about_page_li {
    margin-bottom: 20px;
}
.about_page_li .row_icons{
	width: 20px;
}
.today_highlight{
    background-color: #00e600 !important;
}
</style>
<div class="box-typical box-typical-padding">
    <?php 
 
        // breadcrumbs template
        $bc_items = array(
            array(
                'title' => $title,
                'status' => 'active',
                'link' => "/calendar/monthly_schedule/{$tech_id}"
            )
        );
        $bc_data['bc_items'] = $bc_items;

        if($staff!=6){ //admin link if not tech
            $bc_data['has_admin_version'] = 1;
            $bc_data['has_admin_version_url'] = "/calendar/monthly_schedule_admin/{$tech_id}";
        }
       
        $this->load->view('templates/breadcrumbs', $bc_data);
    ?>
    
        


    <section>
		<div class="body-typical-body">                           

        <?php
        $counter = 1;
        $today = date('Y-m-d');
        for($i=1;$i<=$days_in_month;$i++){

         
            $jdate = "{$useyear}-{$usemonth}-{$i}";
            $jdate2 = date('Y-m-d',strtotime($jdate));
            $today = date('Y-m-d');

            // date label
            $s_full_date = "{$useyear}-{$usemonth}-{$i}";
            $day_suffix = date('S', strtotime($s_full_date));
            $tr_day = date('D', strtotime($s_full_date));
            ?>
            <div class="row vts_row_div">
                
                <div class="col-sm-12">

                    <div class="table_top_head vts_date float-left mr-2 <?php echo ( $jdate2 == $today )?'today_highlight':null; ?>">                   
                        <span><?php echo "{$tr_day} {$i}{$day_suffix}"; ?></span>
                        <span class="fa carret fa-caret-right"></span>
                    </div>


                    <input type="hidden" class="tech_id" value="<?php echo $tech_id; ?>" />
                    <input type="hidden" class="date" value="<?php echo $jdate2; ?>" />


                    <div class="vts_main_div"></div>
                    

                </div>

            </div>


            

            <?php


            $counter++;

        }

        ?>


            

		</div>
	</section>

</div>

<!-- Fancybox Start -->

<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4>Run Sheet</h4>
	<p>
	This shows your future and past schedules
	</p>
	<ul class="about_page_li">

		<?php
		foreach( $serv_type_sql->result() as $serv_type_row ){ ?>
			<li>
				<?php
				// service icons
				$job_icons_params = array(
					'service_type' => $serv_type_row->id
				);
				echo $this->system_model->display_job_icons($job_icons_params);
				?>
				- This job is a <?php echo $serv_type_row->type; ?> service
			</li>
		<?php
		}
		?>
																									
	</ul>


	<ul class="about_page_li">

		<li><img class="row_icons" title="This job is a first visit" src="/images/first_icon.png"> - This job is a first visit</li>
		<li><img class="row_icons" title="This job is entry via key access" src="/images/key_icon.png"> - This job is entry via key access</li>

		<li><img class="row_icons" title="This job is a Priority" src="/images/caution.png"> - This job is a Priority</li>			
		<li><img class="row_icons" title="This is the ladder required for this job" src="/images/ladder.png"> - This is the ladder required for this job</li>
		<li><img class="row_icons" title="This job has notes to be read before starting" src="/images/notes.png"> - This job has notes to be read before starting</li>
		<li><img class="row_icons" title="Call tenant before the job" src="/images/red_phone2.png"> - Call tenant before the job</li>
		<li><img class="row_icons" title="No Tenants" src="/images/serv_img/upgrade_colored.png"> - This is an upgrade to interconnected job (NEW QLD Legislation)</li>
		<li><img class="row_icons" title="No Tenants" src="/images/240v_colored.png"> - This job requires an electrician</li>
		<li><img class="row_icons" title="No Tenants" src="/images/fr_colored.png"> - This is a Repair job</li>

	</ul>

	<ul class="about_page_li">	

		<li><span style="background-color: pink;">Pink Highlight</span> - ERROR on Tech sheet</li>	
		<li><span style="background-color: #fffca3;">Yellow Highlight</span> - Unable to Complete</li>	
		<li><span style="background-color: #c2ffa7;">Green Highlight</span> - Completed</li>	
		<li><span style="background-color: #ffff00;">Bright Yellow Highlight</span> - Job is yet to be mapped</li>
																									
	</ul>

</div>

<!-- Fancybox END -->


<script>
jQuery(document).ready(function(){    

    jQuery(".img_call_before").click(function(){
        jQuery(this).parents("tr:first").find(".booked_with_tenant_div").toggle();
    });

    // key num toggle
    jQuery(".key_icon").click(function(){					
        jQuery(this).parents("tr:first").find(".key_num_span").toggle();
    });

    // mark run complete 
	jQuery(".vts_date").click(function(){

        var node = jQuery(this);
        var parents = node.parents(".vts_row_div:first");

        var carret = parents.find(".carret");        

        if( carret.hasClass('fa-caret-right') == true ){  // expand                      

            var tech_id = parents.find(".tech_id").val();
            var date = parents.find(".date").val();

            if( tech_id > 0 && date != '' ){

                jQuery('#load-screen').show();
                jQuery.ajax({
                    type: "POST",
                    url: "/calendar/ajax_get_tech_run_list",
                    data: { 
                        tech_id: tech_id,
                        date: date
                    }
                }).done(function( ret ) {

                    jQuery('#load-screen').hide();

                    parents.find(".vts_main_div").html(ret);
                    parents.find(".vts_main_div").show(); // show list

                });	

                // toggle arrow
                carret.removeClass('fa-caret-right').addClass('fa-caret-down');

            }	           

        }else{  // collapse              
            
            // hide list
            parents.find(".vts_main_div").hide();
            // toggle arrow
            carret.removeClass('fa-caret-down').addClass('fa-caret-right');

        }					        
		
	});

})
</script>