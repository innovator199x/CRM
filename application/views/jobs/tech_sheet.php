<style>
    .radio{        
        float: left !important;
        margin-right: 10px;
        margin-bottom: 0;
    }

    input[type="number"],
    .rec_batt_exp, 
    .sa_expiry_add{
        width: 83px;
    }
    
    .sign_off_tab_bottom_table_col{
        width: 135px;
    }

    .borderless {
        border: none;
    }
    .borderless td, .borderless th {
        border: none;
    }
    #swms_table{
        display: none;
    }
    #view_swms_lbl{
        cursor: pointer;
    }
    .chk_yes:checked + label::after{
        background: #00e600 !important;	
    }
    .chk_no:checked + label::after{
        background: #ff0000 !important;	
    }
    /* added fixed height to tabs, bec of techsheet service icons */
    .nav-link-in {
        height: 42px;
    }

    /* adjust service type icons position */
    /*
    .service_type_icons {
        position: relative;
        bottom: 4px;
    }
    */
        
    .techsheet_main_div .fa{
        font-size: 20px;
    }
    .bg_color_red{
        background-color: #fb6067;
    }

    /* fa icons color */
    .techsheet_main_div .fa{
        color: #0082c6;
    }

    /* edit red icon */
    .techsheet_main_div .edit_icon_red{
        color: #fb6067;
        cursor: pointer;
    }
    .sweet-alert .text-muted {
        text-align: left;
    }
    .jred_border{
        border: 1px solid red !important;
    }
    .techsheet_validation_swal {
        width: auto !important;
    }
    .show_it{
        display: table-cell;
    }
    .hide_it{
        display: none;
    }
    .techsheet_tab_next,
    #update_alarm_discarded_btn{
        padding: 20px 50px;
    }
    .preferred_alarm_swal .text-muted{
        text-align: center !important;
    }
</style>
<div class="box-typical box-typical-padding techsheet_main_div">

	<?php 
	// breadcrumbs template
	$bc_items = [];
    
    if( $staff_class == 6 ){ // tech

        $back_link = "/tech_run/run_sheet/{$this->session->techsheet_tr_id}";
        //$back_link = "/tech_run/run_sheet/{$this->input->get_post('tr_id')}";
        $bc_items[] = array(
			'title' => 'Run Sheet',			
			'link' => $back_link
        );
        
    }else{ // staff

        $back_link = "{$this->config->item('crm_link')}/view_job_details.php?id={$this->input->get_post('job_id')}";
        $bc_items[] = array(
			'title' => 'Job Details',			
			'link' => $back_link
        );

    }

    // current page breadcrum link
    $bc_items[] = array(
        'title' => $title,
        'status' => 'active',
        'link' => $uri
    );

	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	
    

    <!-- main TABS -->
    <section class="tabs-section">
		
        <div class="tabs-section-nav tabs-section-nav-icons">
            <div class="tbl">
                <ul id="jmain_tab" class="nav j_remember_tab" role="tablist">
                    <li id="main_tab_arrival" class="nav-item" data-tab_step="1">
                        <a class="nav-link" href="#tab_arrival" role="tab" data-toggle="tab">
                            <span class="nav-link-in">                                
                                Arrival
                            </span>
                        </a>
                    </li>
                    <li id="main_tab_survey" class="nav-item" data-tab_step="2">
                        <a class="nav-link" href="#tab_survey" role="tab" data-toggle="tab">
                            <span class="nav-link-in">                                
                                Survey
                            </span>
                        </a>
                    </li>
                    <li id="main_tab_techsheet" class="nav-item" data-tab_step="3">
                        <a class="nav-link" href="#tab_techsheet" role="tab" data-toggle="tab">
                            <span class="nav-link-in">                                                
                                Tech Sheet
                                <?php //echo ( count($service_types_arr) == 1 )?'<img class="service_type_icons" src="/images/serv_img/'.$this->system_model->getServiceIcons($service_types_arr[0]).'" />':null; ?>
                                <img class="service_type_icons" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($job_row->j_service); ?>" />
                            </span>
                        </a>
                    </li>
                    <li id="main_tab_sign_off" class="nav-item" data-tab_step="4">
                        <a class="nav-link" href="#tab_sign_off" role="tab" data-toggle="tab">
                            <span class="nav-link-in">                                
                                Sign Off + Resources
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
       
        
        <div class="tab-content">

            <!-- arrival inner CONTENT -->
            <div role="tabpanel" class="tab-pane fade" id="tab_arrival">                
                <?php require_once(APPPATH."views/jobs/tech_sheet_arrival.php"); ?>                            	
            </div>

            <!-- survey TAB CONTENT -->
            <div role="tabpanel" class="tab-pane fade" id="tab_survey">                             
                <?php require_once(APPPATH."views/jobs/tech_sheet_survey.php"); ?>                                             
            </div>

            <!-- techsheet TAB CONTENT -->
            <div role="tabpanel" class="tab-pane fade" id="tab_techsheet">                           
                <?php require_once(APPPATH."views/jobs/tech_sheet_main.php"); ?>                                                  
            </div>

            <!-- sign off + resources TAB CONTENT -->
            <div role="tabpanel" class="tab-pane fade" id="tab_sign_off">                            	
                <?php require_once(APPPATH."views/jobs/tech_sheet_sign_off.php"); ?>                                             
            </div>

        </div>
       

    </section>


    <input type="hidden" id="job_id" value="<?php echo $this->input->get_post('job_id'); ?>" />
    <input type="hidden" id="p_state" value="<?php echo $job_row->p_state; ?>" />
    <input type="hidden" id="has_ss" value="<?php echo ( $has_ss == true )?1:0; ?>" />
    <input type="hidden" id="has_cw" value="<?php echo ( $has_cw == true )?1:0; ?>" />
    <input type="hidden" id="has_we" value="<?php echo  ( $has_we == true )?1:0; ?>" />
    <input type="hidden" id="j_date" value="<?php echo $job_row->j_date; ?>" />
    <input type="hidden" id="country_id" value="<?php echo $this->config->item('country'); ?>" />
    <input type="hidden" id="is_ic_service" value="<?php echo $is_ic_service; ?>" />
    <input type="hidden" id="utc_can_submit" value="1" />


</div>

<!-- Fancybox Start -->

<!-- Unable to complete -->		
<div id="unable_to_complete_fb" class="fancybox w-75 h-50" style="display:none;" >

    <h4>Unable to complete</h4>

    <div class="w-100 h-75">
        <div class="row">

            <div class="col-md-12">
            <label>Not Completed Due To</label>
            <select id="jobs_not_comp_res" class="form-control">
                <option value="">---</option>	
                <?php
                foreach( $ncr_sql->result() as $ncr_row ){                                    
                ?>
                    <option 
                        value="<?php echo $ncr_row->job_reason_id; ?>" 
                        <?php echo ( $ncr_row->job_reason_id == $job_row->job_reason_id )?'selected':null ?> 
                        <?php echo ( $hide_option == true )?'style="display:none;"':null; ?>
                    >
                        <?php echo $ncr_row->name ?>
                    </option>    
                <?php                                    
                }
                ?>
            </select>							
            </div>                                      

        </div>  

        <div class="row mt-2 h-75">

            <div class="col-md-12">
            <label>Comment</label>        
                <textarea id="jobs_not_comp_com" class="form-control w-100 h-75"><?php echo $job_row->job_reason_comment; ?></textarea>                            
            </div>  

        </div>

        <div class="row mt-2 text-right">

            <div class="col-md">
            <button type="button" id="mark_job_not_completed_btn" class="btn">Submit</button>
            </div>
            
        </div>
    </div>

</div>

<!-- About Text -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>This pages records all data performed on the property by the Technician</p>

</div>					

<!-- Fancybox END -->


<script type="text/javascript" src="/inc/js/techsheet_validation.js"></script>
<script>
function ts_ajax_inline_update(dom){
           
    var db_table_field = dom.attr("data-db_table_field");
    //var db_table_value  = dom.val(); 
    if( dom.hasClass("swms_chk") == true ){ // needs to be 1 or 0 for SWMS checkbox
        var db_table_value  = ( dom.prop("checked") == true )?1:0; 
    }else{
        var db_table_value  = dom.val(); 
    }   
    var db_table = dom.attr("data-db_table");    

    if( db_table == 'jobs' ){

        var job_id = <?php echo $this->input->get_post('job_id'); ?>; 

        // job update
        if( job_id > 0 ){

            //jQuery('#load-screen').show();
            jQuery(".techsheet_tab_next:visible").prop("disabled",true); // disable NEXT button

            jQuery.ajax({
                type: "POST",
                url: "/jobs/ajax_techsheet_inline_update",
                data: { 
                    job_id: job_id,
                    db_table_field: db_table_field,
                    db_table_value: db_table_value,
                    db_table: db_table
                }
            }).done(function( ret ){

                //jQuery('#load-screen').hide();  
                jQuery(".techsheet_tab_next:visible").prop("disabled",false); // enable NEXT button

                // reload page if leak notes, to refresh appended notes
                if( db_table_field == 'leak_notes' ){ 
                    location.reload();
                }                           			

            });

        }

    }else if( db_table == 'property' ){

        var property_id = <?php echo $job_row->property_id; ?>;   

        // property update
        if( property_id > 0 ){

            //jQuery('#load-screen').show();
            jQuery(".techsheet_tab_next:visible").prop("disabled",true); // disable NEXT button

            jQuery.ajax({
                type: "POST",
                url: "/jobs/ajax_techsheet_inline_update",
                data: { 
                    property_id: property_id,
                    db_table_field: db_table_field,
                    db_table_value: db_table_value,
                    db_table: db_table
                }
            }).done(function( ret ){

                //jQuery('#load-screen').hide(); 
                jQuery(".techsheet_tab_next:visible").prop("disabled",false); // enable NEXT button                            			

            });

        }   


    }else if( db_table == 'nsw_pro_comp' ){

        var property_id = <?php echo $job_row->property_id; ?>;   

        // property update
        if( property_id > 0 ){

            //jQuery('#load-screen').show();
            jQuery(".techsheet_tab_next:visible").prop("disabled",true); // disable NEXT button

            jQuery.ajax({
                type: "POST",
                url: "/jobs/ajax_techsheet_inline_update",
                data: { 
                    property_id: property_id,
                    db_table_field: db_table_field,
                    db_table_value: db_table_value,
                    db_table: db_table
                }
            }).done(function( ret ){

                //jQuery('#load-screen').hide(); 
                jQuery(".techsheet_tab_next:visible").prop("disabled",false); // enable NEXT button                            			

            });

        }   


    }    

}

// remember main tab
function remember_main_tab(link_node){

    var nav_href = link_node.attr("href");	

    var nav_link = link_node.parent(".nav-item");
    var main_tab_step_index = nav_link.attr("data-tab_step");    

    Cookies.set('tech_sheet_remember_tab_job_<?php echo $this->input->get_post('job_id'); ?>', nav_href); 
    Cookies.set('tech_sheet_main_tab_step_index_job_<?php echo $this->input->get_post('job_id'); ?>', main_tab_step_index);    

}

// remember service tab
function remember_service_tab(link_node){

    var nav_href = link_node.attr("href");	
    Cookies.set('tech_sheet_remember_tab2_job_<?php echo $this->input->get_post('job_id'); ?>', nav_href);

}

// save questions unique to existing alarm 
function save_existing_alarm_unique_radios(reload=0){

    jQuery(".existing_alarm_tbl .alarm_id").each(function(){

        var alarm_id_dom = jQuery(this);
        var alarm_id = alarm_id_dom.val();   
        
        // Securely Fixed
        var ts_fixing = jQuery(".ts_fixing"+alarm_id+":checked").val();
        // Cleaned
        var ts_cleaned = jQuery(".ts_cleaned"+alarm_id+":checked").val();
        // Battery Tested and Replaced if Required (Where replaceable)
        var ts_newbattery = jQuery(".ts_newbattery"+alarm_id+":checked").val();
        // Test Button Working
        var ts_testbutton = jQuery(".ts_testbutton"+alarm_id+":checked").val();
        // Visual Indicators Working
        var ts_visualind = jQuery(".ts_visualind"+alarm_id+":checked").val();
        // Meets AS 3786:2014
        var ts_meetsas1851 = jQuery(".ts_meetsas1851"+alarm_id+":checked").val();
        
        //jQuery('#load-screen').show();
        //jQuery(".techsheet_tab_next:visible").prop("disabled",true); // disable NEXT button

        jQuery.ajax({
            type: "POST",
            url: "/jobs/ajax_save_existing_alarm_questions",
            data: { 
                alarm_id: alarm_id,
                
                ts_fixing: ts_fixing,
                ts_cleaned: ts_cleaned,
                ts_newbattery: ts_newbattery,
                ts_testbutton: ts_testbutton,
                ts_visualind: ts_visualind,
                ts_meetsas1851: ts_meetsas1851
            }
        }).done(function( ret ){

            //jQuery('#load-screen').hide(); 
            //jQuery(".techsheet_tab_next:visible").prop("disabled",false); // enable NEXT button    
            
            if( reload == 1 ){
                location.reload(); 
            }

        });

    });

}

jQuery(document).ready(function(){

    // reset UTC submit flag
    jQuery("#utc_can_submit").val(1);

    // remember main tab
    jQuery(".j_remember_tab .nav-link").click(function(e){

        //e.preventDefault();            
        return false;

    });    

    // select remembered tab
    if( Cookies.get('tech_sheet_remember_tab_job_<?php echo $this->input->get_post('job_id'); ?>') != undefined ){					
        jQuery('.j_remember_tab a[href="'+Cookies.get('tech_sheet_remember_tab_job_<?php echo $this->input->get_post('job_id'); ?>')+'"]').tab('show');
    }else{ // default			
        jQuery('.j_remember_tab a:eq(0)').tab('show');
    }




    // remember service tab
    jQuery(".j_remember_tab2 .nav-link").click(function(){

        var link_node = jQuery(this);
        remember_service_tab(link_node)

    });

    // select remembered tab
    if( Cookies.get('tech_sheet_remember_tab2_job_<?php echo $this->input->get_post('job_id'); ?>') != undefined ){					
        jQuery('.j_remember_tab2 a[href="'+Cookies.get('tech_sheet_remember_tab2_job_<?php echo $this->input->get_post('job_id'); ?>')+'"]').tab('show');
    }else{ // default			
        jQuery('.j_remember_tab2 a:eq(0)').tab('show');
    }


    // INLINE AJAX UPDATES ------------
    // tenant update
    jQuery(".tenant_update").change(function(){

        var dom = jQuery(this);
        var parent = dom.parents("tr:first");        

        var pt_id = parent.find(".pt_id").val();        

        var db_table_field = dom.attr("data-db_table_field");
        var db_table_value  = dom.val();    

        if( pt_id > 0 ){

            jQuery('#load-screen').show();
            jQuery(".techsheet_tab_next:visible").prop("disabled",true); // disable NEXT button

            jQuery.ajax({
                type: "POST",
                url: "/jobs/ajax_techsheet_update_tenants",
                data: { 
                    pt_id: pt_id,
                    db_table_field: db_table_field,
                    db_table_value: db_table_value
                }
            }).done(function( ret ){

                jQuery('#load-screen').hide(); 
                jQuery(".techsheet_tab_next:visible").prop("disabled",false); // enable NEXT button                     			

            });

        }        	

    });


    // inline ajax job update            
    var elem_select = "#alarm_code, #survey_numlevels, .survey_ladder, .survey_ceiling, #ps_number_of_bedrooms, #j_comments,"; 
    elem_select += ".ts_safety_switch, .ts_safety_switch_reason, #survey_numalarms,"; 
    elem_select += "#swms_heights, #swms_uv_protection, #swms_asbestos, #swms_powertools, #swms_animals, #swms_live_circuit,";    
    elem_select += "#swms_covid_19, #ts_batteriesinstalled, #ts_items_tested, #ts_alarmsinstalled, .survey_alarmspositioned,";    
    elem_select += ".survey_minstandard, #entry_gained_via, .property_leaks, #leak_notes, .prop_upgraded_to_ic_sa_survey, #qld_new_leg_alarm_num,";    
    elem_select += ".ss_location, #ss_quantity, #entry_gained_other_text, #ss_items_tested, #cw_items_tested, #we_items_tested,"; 
    elem_select += ".short_term_rental_compliant, #nsw_leg_num_alarms, .service_garage, #req_heat_alarm"; 
    jQuery(elem_select).change(function(){

        var dom = jQuery(this);         
        ts_ajax_inline_update(dom);   

    }); 

    // SUBMIT COMPLETED TECHSHEET
    jQuery("#btn_comp_ts").click(function(){

        console.log("Bugo");

        var ts_techconfirm = jQuery(".ts_techconfirm:checked").val();
        var prop_comp_with_state_leg = jQuery(".prop_comp_with_state_leg:checked").val();
        var prop_upgraded_to_ic_sa = jQuery(".prop_upgraded_to_ic_sa:checked").val();
        var ts_batteriesinstalled = jQuery("#ts_batteriesinstalled").val();
        var ts_items_tested = jQuery("#ts_items_tested").val();
        var ts_alarmsinstalled = jQuery("#ts_alarmsinstalled").val();      
        var ts_safety_switch  = jQuery(".ts_safety_switch:checked").val();
       
        var ts_discarded_count = jQuery(".ts_discarded[value=1]").length;  // discarded
        var new_alarm_count = jQuery(".new_alarm_tbl .alarm_id").length;
        var ss_list_count = jQuery("#ss_table_listing .safety_switch_id").length;
        var swms_chk = jQuery(".swms_chk:checked").length;
        var we_toilet = jQuery(".we_device[value=2]").length; // toilet
        var repair_notes = jQuery("#repair_notes").val();

        var error = '';        
        
        if( ts_techconfirm == null ){
            error += 'Please confirm if all items on the above checklist have been completed and all Appliances noted have been Inspected and Maintained as per Manufacturers Recommendations and the Australian Standards.\n';            
        }

        if( prop_comp_with_state_leg == null ){
            error += 'Please select if property compliant with current state legislation.\n';            
        }

        if( swms_chk == 0 ){
            error += 'You must mark at least one SWMS.\n';            
        }

        <?php
        // QLD ONLY
        if( $job_row->p_state == 'QLD' ){ ?>

            if( prop_upgraded_to_ic_sa == null ){
                error += 'Please select if property meet QLD NEW Legislation.\n';                
            }            

        <?php
        }

        // Fix and Replace
        if( $job_row->job_type == 'Fix or Replace' ){ ?>

            if( repair_notes == '' ){
                error += 'Please leave repair or job notes.\n';   
            } 

        <?php
        }
        ?>   
            

        if( error !='' ){ // has error msg

            swal('',error,'error');

        }else{ // empty
            jQuery('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/jobs/submit_tech_sheet",
                data: { 
                    job_id: <?php echo $this->input->get_post('job_id'); ?>,
                    ts_techconfirm: ts_techconfirm,
                    prop_comp_with_state_leg: prop_comp_with_state_leg,
                    prop_upgraded_to_ic_sa: prop_upgraded_to_ic_sa
                }
            }).done(function( ret ){

                jQuery('#load-screen').hide();  
                //location.reload();   

                window.location="<?php echo $back_link ?>";                         			

            });        

        }

    });


    // SWMS visibility toggle
    jQuery("#view_swms_lbl").click(function(){

        jQuery("#swms_table").toggle();

    });

    
    // prev tab
    jQuery('.techsheet_tab_prev').click(function(){

        var prev = jQuery('#jmain_tab .active').parents("li.nav-item").prev('li.nav-item');

        if(prev.length){

            var link_node =  prev.find('a.nav-link');
            remember_main_tab(link_node);
            //link_node.trigger('click');
            link_node.tab('show');

        }

    });

    // next tab
    jQuery('.techsheet_tab_next').click(function(){
        var service_type = jQuery("#service_type").val();  
        if (service_type == 15) {
            var required_radio = jQuery("#required_radio").val();
        } else {
            var required_radio = '';
        }
        tab_step_validation(required_radio);

        /*
        var next = jQuery('#jmain_tab .active').parents("li.nav-item").next('li.nav-item');

        if(next.length){

            var ret_obj = tab_step_validation();
            //var error = ( ret_obj.error != '' )?"<ul style='list-style-type: disc;'>"+ret_obj.error+"</ul>":ret_obj.error;
            var error = ret_obj.error;
            var can_continue = ret_obj.can_continue;
            var no_alarms = ret_obj.no_alarms;

          
            if( error != '' ){

                if( can_continue == true ){

                    if( no_alarms == true ){
                        error += '<br /><p><textarea id="no_alam_reason" class="form-control"></textarea></p>';
                    }else{
                        error += "<br /><p>Continue Anyway?</p>";
                    }
                    

                    swal({
                        title: "",
                        html: true,
                        text: error,
                        type: "error",		
                        customClass: 'techsheet_validation_swal',				
                        showCancelButton: true,
                        confirmButtonClass: "btn-success",
                        confirmButtonText: "Yes, Continue",
                        cancelButtonClass: "btn-danger",
                        cancelButtonText: "No, Cancel!",
                        closeOnConfirm: true,
                        showLoaderOnConfirm: true,
                        closeOnCancel: true
                    },
                    function(isConfirm) {

                        if (isConfirm) {							  
                            
                            if( can_continue == true ){

                                jQuery('#load-screen').show();
                                var link_node =  next.find('a.nav-link');     
                                       
                                remember_main_tab(link_node); // remember tab
                                save_existing_alarm_unique_radios();

                                // save no alarm reason if it the reason textarea exist
                                var no_alam_reason_dom = jQuery("#no_alam_reason").length;
                                if( no_alam_reason_dom > 0 ){

                                    var no_alam_reason = jQuery("#no_alam_reason").val();

                                    if( no_alam_reason != '' ){

                                        // save no alarm reason
                                        jQuery.ajax({
                                            type: "POST",
                                            url: "/jobs/ajax_save_no_alarm_reason",
                                            data: { 
                                                job_id: <?php echo $this->input->get_post('job_id'); ?>,
                                                no_alam_reason: no_alam_reason
                                            }
                                        }).done(function( ret ){
                                            location.reload();             
                                        });

                                    }
                                    
                                    
                                }else{

                                    //link_node.trigger('click');
                                    link_node.tab('show');
                                    jQuery('#load-screen').hide();

                                }

                                

                            }				

                        }

                    });	
                    
                }else{

                    //swal('',error,'error');
                    swal({
                        title: "",
                        html: true,
                        text: error,
                        type: "error",						
                        customClass: 'techsheet_validation_swal'
                    });	

                }
                

            }else{

                if( can_continue == true ){

                    jQuery('#load-screen').show();
                    var link_node =  next.find('a.nav-link');            
                    remember_main_tab(link_node);
                    save_existing_alarm_unique_radios();

                    //link_node.trigger('click');
                    link_node.tab('show');
                    jQuery('#load-screen').hide();

                }                                            

            } 
                                               

        }
        */

    });

   
    // tenants edit
    // inline edit
	jQuery(".edit_tenant_icon").click(function(){

        var btn_node = jQuery(this);
        var parents_row = btn_node.parents("tr:first");
    
        parents_row.find(".txt_hid").show();
        parents_row.find(".txt_lbl").hide();

    });

    // Unable to complete
    jQuery(".unable_to_complete_btn").click(function(){

        jQuery.fancybox.open({
            src  : '#unable_to_complete_fb'
        });

    });

    // mark job as not completed ajax script
    jQuery("#mark_job_not_completed_btn").click(function(){

        var dom = jQuery(this);
        var jobs_not_comp_res = jQuery("#jobs_not_comp_res").val();
        var jobs_not_comp_com = jQuery("#jobs_not_comp_com").val();
        var error = '';

        if( jobs_not_comp_res == '' ){
            error += "Job not completed reason dropdown is required\n";
        }

        if( error != '' ){
            swal('',error,'error');
        }else{

            if( jQuery("#utc_can_submit").val() == 1 ){ // prevent double click

                jQuery("#utc_can_submit").val(0); // disable UTC button

                jQuery('#load-screen').show();
                jQuery(".techsheet_tab_next:visible").prop("disabled",true); // disable NEXT button

                jQuery.ajax({
                    type: "POST",
                    url: "/jobs/ajax_mark_job_not_completed",
                    data: { 
                        job_id: <?php echo $this->input->get_post('job_id'); ?>,
                        jobs_not_comp_res: jobs_not_comp_res,
                        jobs_not_comp_com: jobs_not_comp_com
                    }
                }).done(function( ret ){

                    jQuery('#load-screen').hide();
                    jQuery(".techsheet_tab_next:visible").prop("disabled",false); // enable NEXT button
                    //location.reload();    

                    window.location="<?php echo $back_link ?>";              			

                });	
            
            }            

        }		

    });

    <?php
    if( $staff_class != 6 ){ // non-tech 
    ?>

        // main tab FREE moving between tabs for staff, without triggering validation
        jQuery("#jmain_tab .nav-link").click(function(){

            var link_node =  jQuery(this);     
            remember_main_tab(link_node); // remember tab
            link_node.tab('show');

        });

    <?php    
    }
    ?>   
    
    
    <?php
    if( $this->config->item('country') == 1 ){ // AU

        // preferred_alarm pop up
        if( $job_row->p_state == 'QLD' ){ 

            if( $job_row->preferred_alarm_id > 0 ){
                ?>
                var job_id = <?php echo $this->input->get_post('job_id'); ?>;

                if( Cookies.get('stop_preferred_alarm_popup_job_'+job_id) == undefined ){


                    <?php
                    if( $job_row->job_type == 'IC Upgrade' ){ 
                        
                        $num_qld_alarm_txt = ( $job_row->qld_new_leg_alarm_num > 0 )?"{$job_row->qld_new_leg_alarm_num} ":null;
                        
                        ?>
                        var pref_alarm_txt = 'Use <?php echo $num_qld_alarm_txt.''.strtoupper($job_row->pref_alarm_make); ?> alarms on this job.';
                    <?php    
                    }else{ ?>
                        var pref_alarm_txt = 'This property uses <?php echo strtoupper($job_row->pref_alarm_make); ?> alarms.';
                    <?php
                    }
                    ?>

                    swal({
                        title: "Warning!",            
                        text: pref_alarm_txt,
                        type: "warning",						
                        customClass: 'preferred_alarm_swal'
                    });	
                    Cookies.set('stop_preferred_alarm_popup_job_'+job_id, 1); // to avoid annoying the techs

                }        
            <?php
            }

        }else{ //  non-QLD
        ?>

            var job_id = <?php echo $this->input->get_post('job_id'); ?>;
            if( Cookies.get('stop_preferred_alarm_popup_job_'+job_id) == undefined ){

                var pref_alarm_txt = 'This property uses <?php echo $this->system_model->display_free_emerald_or_paid_brooks($job_row->agency_id); ?> alarms.';

                swal({
                    title: "Warning!",            
                    text: pref_alarm_txt,
                    type: "warning",						
                    customClass: 'preferred_alarm_swal'
                });	
                Cookies.set('stop_preferred_alarm_popup_job_'+job_id, 1); // to avoid annoying the techs

            }  

        <?php    
        }
        
    }    
    ?>



});


// enter key go to next item
// register jQuery extension
jQuery.extend(jQuery.expr[':'], {
    focusable: function (el, index, selector) {
        return $(el).is('a, button, :input, [tabindex]');
    }
});

$(document).on('keypress', 'input[type="text"],input[type="number"],select', function (e) {
    if (e.which == 13) {
        e.preventDefault();
        // Get all focusable elements on the page
        var $canfocus = $(':focusable');
        var index = $canfocus.index(this) + 1;
        if (index >= $canfocus.length) index = 0;
        $canfocus.eq(index).focus();
    }
});
</script>