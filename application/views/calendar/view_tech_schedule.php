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


/* keys lightbox css */
.agency_keys_lb{
	width: 80%;
}
.fancybox-content {
    max-width: unset;
}
.job_reason_div .form-control{
	margin: 5px 0;
}
.key_num_th{
	width: 15%;
}
.is_keys_picked_up_yes:checked + label::after,
.is_keys_returned_yes:checked + label::after{
	background: #00e600 !important;	
}
.is_keys_picked_up_no:checked + label::after,
.is_keys_returned_no:checked + label::after{
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
	width: 40%;
}
.signature_div {
    margin-bottom: 20px;
	display:none;
}
.number_of_keys {
    width: 70px;    
    float: left;
}

.agency_staff {
	width: 35.3%;
	float: left;
	margin-right: 5px;
}
#btn_clear_signature{
	display: none
}
.signature_svg_img{
	width: 150px
}
.ppe_icon {
	position: relative;
	left: 7px;
	bottom: 2px;
}
</style>
<div class="box-typical box-typical-padding">
    <?php 
 
        // breadcrumbs template
        $bc_items = array(
            array(
                'title' => $title,
                'status' => 'active',
                'link' => "/calendar/monthly_schedule_admin/{$tech_id}"
            )
        );
        $bc_data['bc_items'] = $bc_items;
        $bc_data['has_tech_version'] = 1;
		$bc_data['has_tech_version_url'] = "/calendar/monthly_schedule/{$tech_id}";
        $this->load->view('templates/breadcrumbs', $bc_data);
	?>
    <section>
		<div class="body-typical-body">

            <p>&nbsp;</p>
          

            <div class="row top_date_title">
                <div class="text-center col-md-12 columns"> 
                    <a href=<?php echo "/calendar/monthly_schedule_admin/{$tech_id}/?month={$prevmonth}&year={$prevyear}" ?>><span style="font-size:20px;" class="fa fa-chevron-circle-left"></span></a>  
                    &nbsp;&nbsp;
                    <?php echo "<h3 style='display:inline-block;' class='text-center'>{$current_month}</h3>" ?> 
                    &nbsp;&nbsp;
                    <a href="<?php echo "/calendar/monthly_schedule_admin/{$tech_id}/?month={$nextmonth}&year={$nextyear}" ?>"><span style="font-size:20px;" class="fa fa-chevron-circle-right"></span></a>
                </div>
            </div>


            <?php
                $colspan = ($staff!=6)?7:6;
                $counter = 1;
                $today = date('Y-m-d');
                for($i=1;$i<=$days_in_month;$i++){
                   
       
                 
                    
                
                        $jdate = "{$useyear}-{$usemonth}-{$i}";
                        $jdate2 = date('Y-m-d',strtotime($jdate));

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


<!-- pick up -->
<!--<a href="javascript:void(0);" id="pick_up_fb_trigger" class="fb_trigger agency_keys_lb_trigger jfancybox" data-fancybox data-src="#pick_up_fb">Trigger the fancybox</a>-->
<div id="pick_up_fb" class="jfancybox agency_keys_lb" style="display:none;">

	<h2 class="keys_lb_agency_name"></h2>
	
	<div id="pick_up_lb_content" class="agency_keys_lb_content"></div>

	<div class="second_div">			
		<div class="row">

			<div class="col-md-12">
				<input type="text" class="form-control agency_staff" placeholder="Agency Staff" />
				<input type="number" class="form-control number_of_keys" placeholder="No. of Keys" />
				<!--<button type='button' class='btn btn-success float-right' id="save_pick_up_btn">Save Pickup</button>-->
			</div>		

		</div>
	</div>


	<input type="hidden" class="trk_id" />
	<input type="hidden" class="tech_id" />
	<input type="hidden" class="date" />
	<input type="hidden" class="agency_id" />	

</div>


<!-- drop off -->
<!--<a href="javascript:void(0);" id="drop_off_fb_trigger" class="fb_trigger agency_keys_lb_trigger jfancybox" data-fancybox data-src="#drop_off_fb">Trigger the fancybox</a>-->							
<div id="drop_off_fb" class="jfancybox agency_keys_lb" style="display:none;" >

	<h2 class="keys_lb_agency_name"></h2>
	
	<div id="drop_off_lb_content" class="agency_keys_lb_content"></div>

	<div class="second_div">			
		<div class="row">

			<div class="col-md-12">
				<input type="text" class="form-control agency_staff" placeholder="Agency Staff" />
				<input type="number" class="form-control number_of_keys" placeholder="No. of Keys" />
			</div>	

		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
			<label class="mb-2">Signature: </label>
			<img class="signature_svg_img" />
		</div>
	</div>

	
	<div class="signature_div"> 
		<h1 style="text-align: center; color: red; font-style: italic;">Please sign on the line below</h1> 
		<div id="signature" style="border: 1px solid red; margin-bottom: 5px;"></div>													
	</div>
	
    <!--   
	<input type="hidden" class="trk_id" />
	<button type="button" class="btn btn-danger float-left" id="btn_clear_signature">Clear Signature</button>	
    <button type='button' class='btn btn-success float-right' id="save_drop_off_btn">Save Drop Off</button>
    -->

</div>

<!-- Fancybox END -->

<script>

// signature instance
var sigdiv = jQuery("#signature");

// clear signature
function clearSignature(sigdiv){
	sigdiv.jSignature("reset"); // clears the canvas and rerenders the decor on it.
}


// save signature
function saveSignature(sigdiv){
	// Getting signature as SVG and rendering the SVG within the browser. 
	// (!!! inline SVG rendering from IMG element does not work in all browsers !!!)
	// this export plugin returns an array of [mimetype, base64-encoded string of SVG of the signature strokes]
	var datapair = sigdiv.jSignature("getData", "svgbase64") 
	var i = new Image();
	var svg_txt = "data:" + datapair[0] + "," + datapair[1];
	i.src = svg_txt;
	return svg_txt;
}

// invoke digital signature
function invoke_jsignature(){
	
	sigdiv.jSignature({ 
		width: '100%',
		height: '250px'
	});

}


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
                parents.find(".vts_main_div").toggle();
            });	

        }		        
		
	});


    // pick up lightbox
	jQuery(".vts_main_div").on('click','.pick_up_btn',function(){

        console.log("pick up");

        
        // clear drop off lightbox
        jQuery("#drop_off_lb_content").html("");

        var pick_up_btn = jQuery(this);	
        var keys_fb_div = jQuery("#pick_up_fb");			

        var trk_id = pick_up_btn.attr("data-trk_id");
        var tech_id = pick_up_btn.attr("data-tech_id")
        var date = pick_up_btn.attr("data-date");
        var agency_id = pick_up_btn.attr("data-agency_id");
        var agency_name = pick_up_btn.attr("data-agency_name");
        var completed = pick_up_btn.attr("data-completed");		
        var agency_staff = pick_up_btn.attr("data-agency_staff");
        var number_of_keys = pick_up_btn.attr("data-number_of_keys");

        $('#load-screen').show(); 
        jQuery.ajax({
            type: "POST",
            url: "/tech_run/ajax_job_key_list",
            data: { 						
                tech_id: tech_id,
                date: date,
                agency_id: agency_id,

                key_action: 'Pick Up'
            }
        }).done(function( ret ){

            $('#load-screen').hide(); 

            keys_fb_div.find(".keys_lb_agency_name").html(agency_name);
            keys_fb_div.find(".trk_id").val(trk_id);
            keys_fb_div.find(".tech_id").val(tech_id);
            keys_fb_div.find(".date").val(date);
            keys_fb_div.find(".agency_id").val(agency_id);

            keys_fb_div.find(".agency_staff").val(agency_staff);
            keys_fb_div.find(".number_of_keys").val(number_of_keys);

            keys_fb_div.find(".agency_keys_lb_content").html(ret); // load pick up content
            //jQuery("#pick_up_fb_trigger").click(); // trigger lightbox

            $.fancybox.open({
                src  : '#pick_up_fb',
                touch : false
            }); 

        });	
        	

    });


    // drop off lightbox
    jQuery(".vts_main_div").on('click','.drop_off_btn',function(){

        console.log("drop off");

        
        // clear pick up lightbox
        jQuery("#pick_up_lb_content").html("");

        var pick_up_btn = jQuery(this);	
        var keys_fb_div = jQuery("#drop_off_fb");			

        var trk_id = pick_up_btn.attr("data-trk_id");
        var tech_id = pick_up_btn.attr("data-tech_id")
        var date = pick_up_btn.attr("data-date");
        var agency_id = pick_up_btn.attr("data-agency_id");
        var agency_name = pick_up_btn.attr("data-agency_name");
        var agency_staff = pick_up_btn.attr("data-agency_staff");
        var number_of_keys = pick_up_btn.attr("data-number_of_keys");		
        var signature_svg = pick_up_btn.attr("data-signature_svg");
        var refused_sig = pick_up_btn.attr("data-refused_sig");

        $('#load-screen').show(); 
        jQuery.ajax({
            type: "POST",
            url: "/tech_run/ajax_job_key_list",
            data: { 						
                tech_id: tech_id,
                date: date,
                agency_id: agency_id,
                                
                key_action: 'Drop Off'
            }
        }).done(function( ret ){

            $('#load-screen').hide(); 

            keys_fb_div.find(".keys_lb_agency_name").html(agency_name);
            keys_fb_div.find(".trk_id").val(trk_id);

            keys_fb_div.find(".agency_staff").val(agency_staff);
            keys_fb_div.find(".number_of_keys").val(number_of_keys);
            

            keys_fb_div.find(".agency_keys_lb_content").html(ret); // load pick up content
            //jQuery("#drop_off_fb_trigger").click(); // trigger lightbox

            $.fancybox.open({
                src  : '#drop_off_fb',
                touch : false,
                afterLoad : function(instance, current) {

                    if( signature_svg == '' ){ // singature is empty

                        keys_fb_div.find(".signature_div").show();
                        keys_fb_div.find("#btn_clear_signature").show();

                        jQuery("#signature").html("");
                        invoke_jsignature();

                    }else{ // has saved signature

                        keys_fb_div.find(".signature_div").hide();
                        keys_fb_div.find("#btn_clear_signature").hide();

                        jQuery("#signature").html("");
                        //invoke_jsignature();

                        keys_fb_div.find(".signature_svg_img").attr("src",signature_svg);

                    }					


                }
            }); 

            if( refused_sig == 1 ){
				keys_fb_div.find(".refused_sig").prop("checked",true);
			}else{
				keys_fb_div.find(".refused_sig").prop("checked",false);
			}       

        });	
        		

    });


})
</script>