function tab_step_validation(required_radio){

    var next = jQuery('#jmain_tab .active').parents("li.nav-item").next('li.nav-item');

    if(next.length){

        var job_id = jQuery("#job_id").val();
        var current_tab = jQuery("#jmain_tab .nav-item a.active .nav-link-in").text().trim();   
        var can_continue = true; 
        var no_alarms = false;
        var p_state = jQuery("#p_state").val();        
        var is_ic_service = jQuery("#is_ic_service").val();
        var prop_upgraded_to_ic_sa_survey = jQuery(".prop_upgraded_to_ic_sa_survey:checked").val();
        var qld_new_leg_alarm_num = jQuery("#qld_new_leg_alarm_num").val();

        // radio validation
        var has_empty_rfc = false;
        var has_empty_ts_fixing = false;
        var has_empty_ts_cleaned = false;
        var has_empty_ts_newbattery = false;
        var has_empty_ts_testbutton = false;
        var has_empty_ts_visualind = false;
        var has_empty_ts_meetsas1851 = false; 
        var has_empty_sa_reason = false;
        var has_empty_ts_alarm_sounds_other = false;
        
        var is_no_to_ts_fixing = false;
        var is_no_to_ts_cleaned = false;
        var is_no_to_ts_newbattery = false;
        var is_no_to_ts_testbutton = false;
        var is_no_to_ts_visualind = false;
        var is_no_to_ts_meetsas1851 = false; 
        var is_no_to_sa_reason = false;
        var is_no_to_ts_alarm_sounds_other = false;

        var error = '';           

        if( current_tab == 'Survey' ){

            var entry_gained_via = jQuery("#entry_gained_via").val();      
            var survey_numlevels = jQuery("#survey_numlevels").val();                
            var ps_number_of_bedrooms = jQuery("#ps_number_of_bedrooms").val();  
            var survey_ceiling = jQuery(".survey_ceiling:checked").val(); 
            var survey_numalarms = jQuery("#survey_numalarms").val(); 
            var ts_safety_switch = jQuery("#ts_safety_switch").val();
            var entry_gained_other_text = jQuery("#entry_gained_other_text").val();
            var survey_ladder = jQuery(".survey_ladder:checked").val();

            if( entry_gained_via == '' ){
                error += '<li>Entry Gained is required</li>';
                can_continue = false;
            }                    

            if( survey_numlevels == '' || survey_numlevels == 0 ){
                error += '<li>Levels in Property is required</li>';
                can_continue = false;
            }

            if( ps_number_of_bedrooms == '' || ps_number_of_bedrooms == 0 ){
                error += '<li>Number of Bedrooms is required</li>';
                can_continue = false;
            }

            if( survey_ceiling == '' || survey_ceiling == undefined ){
                error += '<li>Ceiling Type is required</li>';
                can_continue = false;
            }

            if( survey_ladder == '' || survey_ladder == undefined ){
                error += '<li>Ladder height is required.</li>';
                can_continue = false;
            }

            if( survey_numalarms == '' ){

                error += '<li>Current Number of Alarms is required</li>';
                can_continue = false;

            }else{

                if(  survey_numalarms == 0  ){
                    error += '<li>This Property has no alarms?</li>';
                    can_continue = true;
                } 

            }

        
            // QLD ONLY
            if( p_state == 'QLD' ){                                   

                if( qld_new_leg_alarm_num == '' ){
                    error += '<li>Total Number of alarms required to meet NEW legislation is required</li>';
                    can_continue = false;
                }


            }


            if( entry_gained_via == -1 && entry_gained_other_text == '' ){
                error += '<li>Entry Gained "Other" Reason is required</li>';
                can_continue = false;
            }
            

        }


        if( current_tab == 'Tech Sheet' ){

            var inner_service_type_tab = jQuery("#service_type_tab .nav-item a.active .nav-link-in").text().trim();        

            // SA TAB
            var survey_alarmspositioned = jQuery(".survey_alarmspositioned:checked").val();  
            var survey_minstandard = jQuery(".survey_minstandard:checked").val(); 
            var ts_batteriesinstalled = jQuery("#ts_batteriesinstalled").val(); 
            var ts_items_tested = jQuery("#ts_items_tested").val();
            var ts_alarmsinstalled = jQuery("#ts_alarmsinstalled").val(); 
            var property_leaks_yes = jQuery("#property_leaks_yes:checked").val(); 
            var property_leaks_no = jQuery("#property_leaks_no:checked").val();  
            var service_type = jQuery("#service_type").val();  

            var all_alarm_count = jQuery("#alarms_listing_div .alarm_id").length;    
            var ts_discarded_count = jQuery(".existing_alarm_tbl .ts_discarded[value=1]").length;  // discarded  
            var sa_reason = jQuery("#sa_reason").val();

            var all_alarm = jQuery("#alarms_listing_div .alarm_id");  // all alarms  
            var ts_not_discarded_count = 0;
            var alarms_installed_with_batt = 0;

            // all alarms
            all_alarm.each(function(){

                var alarm_id_dom = jQuery(this);
                var alarm_id = alarm_id_dom.val();   
                
                var sa_type = jQuery(".sa_type[data-col='"+alarm_id+"']").val(); // alarm type
                var ts_discarded = jQuery("input.ts_discarded[data-col='"+alarm_id+"']").val(); // discarded                              
                var is_li = jQuery("select.sa_power[data-col='"+alarm_id+"'] option:selected").attr('data-is_li'); // is LI alarms?
                var is_240v = jQuery("select.sa_power[data-col='"+alarm_id+"'] option:selected").attr('data-is_240v'); // is 240v alarms?               
                var sa_ts_expiry = jQuery(".sa_ts_expiry[data-col='"+alarm_id+"']").val(); // alarm expiry 
                var is_new = jQuery(".is_new[data-col='"+alarm_id+"']").val(); // is new?
                var is_new_txt = ( is_new == 1 )?'NEW':'EXISTING';
                var alarm_number = jQuery(".alarm_number[data-col='"+alarm_id+"']").val(); // alarm number on header

                var country_id = jQuery("#country_id").val();                                            

                if( ts_discarded != 1  ){ // non-discarded non-LI alarm power 


                    if( country_id == 2 ){ // NZ

                        // sa_type: 1 - Ionisation, 20 - Ion Interconnected 
                        var alar_type_name_txt = '';
                        if( ( sa_type == 1 || sa_type == 20 ) && sa_ts_expiry >= '2026' ){ 
                            
                            if( sa_type == 1 ){
                                alar_type_name_txt = 'Ionisation';
                            }else if( sa_type == 20 ){
                                alar_type_name_txt = 'Ion Interconnected';
                            }

                            error += '<li>'+is_new_txt+' <b>Alarm '+alarm_number+'</b> needs to be removed due to <b>being '+alar_type_name_txt+'</b> and <b>installed after 2016</b>.</li>';    
                            can_continue = false;

                        }

                        /* commented out as per Ben's request Nov 17 2022
                        if( ( is_240v != 1 && is_li != 1 ) && sa_ts_expiry >= '2026' ){     

                            error += '<li>'+is_new_txt+' <b>Alarm '+alarm_number+'</b> needs to be removed due to <b>being not being 240v or Li</b> and <b>installed after 2016</b>.</li>';    
                            can_continue = false;

                        }
                        */

                    }

                    // // commented as ben instruction to remove "Property already upgraded to interconnected alarms? (QLD ONLY)" on survey tab
                    /*
                    // If QLD and IC upgraded                   
                    if( p_state == 'QLD' && prop_upgraded_to_ic_sa_survey == 1 ){ 

                        // not interconnected alarm type: 19 - P/E Interconnected, 20 - Ion Interconnected
                        if( sa_type != 19 && sa_type != 20 ){

                            error += '<li>This property is marked as <b>upgraded</b>, but '+is_new_txt+' <b>Alarm '+alarm_number+'</b> is not an <b>interconnected</b> type, we will have to re-attend.</li>';        
    
                        }

                    }
                    */

                   

                    //  --- RADIO questions ---
                    
                    // RFC
                    var elem_dom = jQuery(".sa_rfc"+alarm_id);
                    if( elem_dom.length > 0 ){

                        var elem_sel_dom = jQuery(".sa_rfc"+alarm_id+":checked");
                        var parent_td = elem_dom.parents("td");
                        var elem_sel_val = elem_sel_dom.val();
                        
                        if( elem_sel_val == undefined ){

                            has_empty_rfc = true;
                            parent_td.addClass('jred_border');     

                        }else{

                            parent_td.removeClass('jred_border');
                            
                        }  

                    }
                    

                    // Securely Fixed
                    var elem_dom = jQuery(".ts_fixing"+alarm_id);
                    if( elem_dom.length > 0 ){

                        var elem_sel_dom = jQuery(".ts_fixing"+alarm_id+":checked");
                        var parent_td = elem_dom.parents("td");
                        var elem_sel_val = elem_sel_dom.val();
                        
                        if( elem_sel_val == undefined ){

                            has_empty_ts_fixing = true;
                            parent_td.addClass('jred_border');     

                        }else{
                        
                            if( elem_sel_val == 0 ){
                                is_no_to_ts_fixing = true;
                                parent_td.addClass('jred_border');   
                            }else{
                                parent_td.removeClass('jred_border');
                            }

                        } 

                    }
                    

                    // Cleaned
                    var elem_dom = jQuery(".ts_cleaned"+alarm_id);
                    if( elem_dom.length > 0 ){

                        var elem_sel_dom = jQuery(".ts_cleaned"+alarm_id+":checked");
                        var parent_td = elem_dom.parents("td");
                        var elem_sel_val = elem_sel_dom.val();
                        
                        if( elem_sel_val == undefined ){

                            has_empty_ts_cleaned = true;
                            parent_td.addClass('jred_border');     

                        }else{
                            
                            if( elem_sel_val == 0 ){
                                is_no_to_ts_cleaned = true;
                                parent_td.addClass('jred_border');   
                            }else{
                                parent_td.removeClass('jred_border');
                            }

                        }

                    }
                    

                    // Battery Tested and Replaced if Required (Where replaceable)
                    var elem_dom = jQuery(".ts_newbattery"+alarm_id);
                    if( elem_dom.length > 0 ){

                        var elem_sel_dom = jQuery(".ts_newbattery"+alarm_id+":checked");
                        var parent_td = elem_dom.parents("td");
                        var elem_sel_val = elem_sel_dom.val();
                        
                        if( elem_sel_val == undefined ){

                            has_empty_ts_newbattery = true;
                            parent_td.addClass('jred_border');     

                        }else{
                            
                            if( elem_sel_val == 0 ){
                                is_no_to_ts_newbattery = true;
                                parent_td.addClass('jred_border');   
                            }else{
                                parent_td.removeClass('jred_border');
                            }

                        }

                    }
                    

                    // Test Button Working
                    var elem_dom = jQuery(".ts_testbutton"+alarm_id);
                    if( elem_dom.length > 0 ){

                        var elem_sel_dom = jQuery(".ts_testbutton"+alarm_id+":checked");
                        var parent_td = elem_dom.parents("td");
                        var elem_sel_val = elem_sel_dom.val();
                        
                        if( elem_sel_val == undefined ){

                            has_empty_ts_testbutton = true;
                            parent_td.addClass('jred_border');     

                        }else{
                            
                            if( elem_sel_val == 0 ){
                                is_no_to_ts_testbutton = true;
                                parent_td.addClass('jred_border');   
                            }else{
                                parent_td.removeClass('jred_border');
                            }

                        } 
                        
                    }
                    
                    
                    // Visual Indicators Working
                    var elem_dom = jQuery(".ts_visualind"+alarm_id);
                    if( elem_dom.length > 0 ){

                        var elem_sel_dom = jQuery(".ts_visualind"+alarm_id+":checked");
                        var parent_td = elem_dom.parents("td");
                        var elem_sel_val = elem_sel_dom.val();
                        
                        if( elem_sel_val == undefined ){

                            has_empty_ts_visualind = true;
                            parent_td.addClass('jred_border');     

                        }else{
                            
                            if( elem_sel_val == 0 ){
                                is_no_to_ts_visualind = true;
                                parent_td.addClass('jred_border');   
                            }else{
                                parent_td.removeClass('jred_border');
                            }

                        } 
                        
                    }

                    // IC service type only
                    if( is_ic_service == 1 ){

                        // Does Alarm sound all other alarms?
                        var elem_dom = jQuery(".ts_alarm_sounds_other"+alarm_id);
                        if( elem_dom.length > 0 ){

                            var elem_sel_dom = jQuery(".ts_alarm_sounds_other"+alarm_id+":checked");
                            var parent_td = elem_dom.parents("td");
                            var elem_sel_val = elem_sel_dom.val();
                            
                            if( elem_sel_val == undefined ){

                                has_empty_ts_alarm_sounds_other = true;
                                parent_td.addClass('jred_border');                                     

                            }else{
                                
                                if( elem_sel_val == 0 ){

                                    // commented as ben instruction to remove "Property already upgraded to interconnected alarms? (QLD ONLY)" on survey tab
                                    /*
                                    // If QLD and IC upgraded                   
                                    if( p_state == 'QLD' && prop_upgraded_to_ic_sa_survey == 1 ){

                                        //is_no_to_ts_alarm_sounds_other = true;
                                        parent_td.addClass('jred_border');                                     
                                        error += '<li>You have said that '+is_new_txt+' <b>Alarm '+alarm_number+'</b> doesn’t sound other alarms, we will have to re-attend</li>';                                        

                                    } 
                                    */                                   
                                    
                                }else{
                                    parent_td.removeClass('jred_border');
                                }

                            } 
                            
                        }

                    }                    
                    

                    // Meets AS 3786:2014
                    var elem_dom = jQuery(".ts_meetsas1851"+alarm_id);
                    if( elem_dom.length > 0 ){

                        var elem_sel_dom = jQuery(".ts_meetsas1851"+alarm_id+":checked");
                        var parent_td = elem_dom.parents("td");
                        var elem_sel_val = elem_sel_dom.val();
                        
                        if( elem_sel_val == undefined ){

                            has_empty_ts_meetsas1851 = true;
                            parent_td.addClass('jred_border');     

                        }else{
                            
                            if( elem_sel_val == 0 ){
                                is_no_to_ts_meetsas1851 = true;
                                parent_td.addClass('jred_border');   
                            }else{
                                parent_td.removeClass('jred_border');
                            }

                        }
                        
                    }


                    ts_not_discarded_count++; // not discarded count    

                    if( is_li != 1 ){
                        alarms_installed_with_batt++; // alarm with instealled batteries count
                    } 

                }

            });  


            // RFC empty
            if( has_empty_rfc == true ){
                error += '<li>Required for Compliance option is required.</li>';    
                can_continue = false;                 
            }
            
            // Securely Fixed
            if( has_empty_ts_fixing == true ){
                error += '<li>Securely Fixed option is required.</li>';    
                can_continue = false;                 
            }

            //  Securely Fixed = NO
            if( is_no_to_ts_fixing == true ){
                error += '<li>Securely Fixed option is set to NO.</li>';    
                can_continue = true;                 
            }

            // Cleaned
            if( has_empty_ts_cleaned == true ){
                error += '<li>Cleaned option is required.</li>';    
                can_continue = false;                 
            }

            // Cleaned = NO
            if( is_no_to_ts_cleaned == true ){
                error += '<li>Cleaned option is set to NO.</li>';    
                can_continue = true;                 
            }
            
            // Battery Tested and Replaced if Required (Where replaceable)
            if( has_empty_ts_newbattery == true ){
                error += '<li>Battery Tested option is required.</li>';    
                can_continue = false;                 
            }

            // Battery Tested and Replaced if Required (Where replaceable) = NO
            if( is_no_to_ts_newbattery == true ){
                error += '<li>Battery Tested option is set to NO.</li>';    
                can_continue = true;                 
            }

            // Test Button Working
            if( has_empty_ts_testbutton == true ){
                error += '<li>Test Button Working option is required.</li>';    
                can_continue = false;                 
            }

            // Test Button Working = NO
            if( is_no_to_ts_testbutton == true ){
                error += '<li>Test Button Working option is set to NO.</li>';    
                can_continue = true;                 
            }

            // Visual Indicators Working
            if( has_empty_ts_visualind == true ){
                error += '<li>Visual Indicators Working option is required.</li>';    
                can_continue = false;                 
            }

            // Does Alarm sound all other alarms?
            if( has_empty_ts_alarm_sounds_other == true ){
                error += '<li>Does Alarm sound all other alarms? option is required.</li>';    
                can_continue = false;                 
            }

            // Visual Indicators Working = NO
            if( is_no_to_ts_visualind == true ){
                error += '<li>Visual Indicators Working option is set to NO.</li>';    
                can_continue = true;                 
            }

            // Meets AS 3786:2014
            if( has_empty_ts_meetsas1851 == true ){
                error += '<li>Meets AS 3786:2014 option is required.</li>';    
                can_continue = false;                 
            }

            // Meets AS 3786:2014 = NO
            if( is_no_to_ts_meetsas1851 == true ){
                error += '<li>Meets AS 3786:2014 option is set to NO.</li>';    
                can_continue = true;                 
            }

            // 15 = water efficiency
            if (service_type == 15) {
                if (property_leaks_yes == '1' || property_leaks_no == '0') {
                } else {
                    error += '<li>Taps on the Premises Leak is Required.</li>';  
                    can_continue = false;    
                }

                if (required_radio == 0) {
                } else {
                    error += '<li>Water Flow is Required.</li>';  
                    can_continue = false;    
                }
            }
            
            //  alarms required               
            if( all_alarm_count == 0   ){

                error += '<li>You are submitting without Smoke Alarms, please provide a reason.</li>';    
                no_alarms = true;          

            }else{

                var new_alarm_count = jQuery(".new_alarm_tbl .alarm_id").length;
                // clear red border
                jQuery("#ts_batteriesinstalled").removeClass('border-danger');
                jQuery("#ts_items_tested").removeClass('border-danger');
                jQuery("#ts_alarmsinstalled").removeClass('border-danger');
                
                if( ts_batteriesinstalled == '' ){
                    jQuery("#ts_batteriesinstalled").addClass('border-danger');
                    error += '<li>Batteries Installed is required</li>';
                    can_continue = false;
                }else{
                    
                    //  Batteries Installed      
                    console.log("alarms_installed_with_batt: "+alarms_installed_with_batt);
                    console.log("ts_batteriesinstalled: "+ts_batteriesinstalled);

                    if( p_state == 'NSW' || p_state == 'ACT' ){ // only on NSW and ACT state

                        if( ts_batteriesinstalled != alarms_installed_with_batt ){
                            jQuery("#ts_batteriesinstalled").addClass('border-danger');
                            error += '<li>Batteries Installed needs to equal to total number of Non-Discarded and non-LI alarms.</li>';
                        }

                    }
                    
                    
                    
                }  

                if( ts_items_tested == '' ){
                    jQuery("#ts_items_tested").addClass('border-danger');
                    error += '<li>Items Tested is required</li>';
                    can_continue = false;
                }else{
                                
                    //  Items Tested                      
                    if( ts_items_tested != all_alarm_count ){
                        jQuery("#ts_items_tested").addClass('border-danger');
                        error += '<li>Items Tested needs to equal to total number of alarms.</li>';
                    }  
                    

                }

                if( ts_alarmsinstalled == '' ){
                    jQuery("#ts_alarmsinstalled").addClass('border-danger');
                    error += '<li>Alarms Installed is required.</li>';
                    can_continue = false;
                }else{

                    //  Alarms Installed   
                    if( ts_alarmsinstalled != new_alarm_count ){
                        jQuery("#ts_alarmsinstalled").addClass('border-danger');
                        error += '<li>Alarms Installed needs to equal to total number of alarms.</li>';
                    }

                } 

            }                        


            // expiry validation
            var date_obj = new Date();
            var current_year = date_obj.getFullYear();  
                        
            var has_expired_alarm = false;
            var has_expiring_in_a_year_battery = false;
            var has_invalid_month = false;
            var has_low_db_reading = false;
            var expiry_dont_match_last_job = false;
            var is_empty =  false;
            

            jQuery("input.sa_ts_expiry").each(function(){

                var ts_expiry_dom = jQuery(this);
                var ts_expiry = ts_expiry_dom.val();
                var parent_td = ts_expiry_dom.parents("td:first");
                var expiry_hid = parent_td.find('.sa_expiry_hid').val();        

                var has_error = false;               

                var alarm_id = ts_expiry_dom.attr("data-col"); // alarm_id
                var ts_discarded = jQuery("input.ts_discarded[data-col='"+alarm_id+"']").val(); // discarded
                var ts_discarded_reason_hid = jQuery("input.ts_discarded_reason_hid[data-col='"+alarm_id+"']").val(); // discarded reason

                if( ts_discarded_reason_hid != 5 ){ // validte only alarm discarded reason NOT "alarm missing"

                    if( ts_expiry != '' ){

                        if( ts_discarded != 1 ){ // validate only non-discarded alarm

                            // if expired                
                            if( current_year >= ts_expiry  ){
                                has_expired_alarm = true;   
                                has_error = true;                 
                            }

                        }                        
                                
                        // compare expiry to ts_expiry                            
                        if( ts_expiry != expiry_hid ){
                            expiry_dont_match_last_job = true; 
                            parent_td.find('.expiry_dont_match_span').html("Expiry dates don't match! Which is the correct date?<br /> <span class='expiry_dont_match_inner_span text-primary'>"+expiry_hid+"</span> OR <span class='expiry_dont_match_inner_span text-red'>"+ts_expiry+"</span>?");
                            has_error = true; 
                        }else{
                            parent_td.find('.expiry_dont_match_span').html(""); // clear
                        }                                                           
                        
                    }else{
                        has_error = true;
                        is_empty = true;
                    } 

                }
                
                
                if( has_error == true ){
                    ts_expiry_dom.addClass('border-danger');    
                }else{
                    ts_expiry_dom.removeClass('border-danger'); 
                }                           
                                

            });

            // expired alarm
            jQuery(".unable_to_complete_ts_tab").hide(); // reset to default
            if( has_expired_alarm == true ){
                error += '<li>Alarms need Replacing.</li>';  
                can_continue = false;     
                // show UTC button
                jQuery(".unable_to_complete_ts_tab").show();
            }

            if( is_empty == true ){
                error += "<li>Alarm Expiry is required.</li>"; 
                can_continue = false;      
            }


            if( expiry_dont_match_last_job == true ){
                error += "<li><b class='text-danger'>Expiry dates don't match! Please Verify the Expiry Date.</b></li>"; 
                can_continue = false;      
            }

            // battery expiry
            var today = new Date(); 
            var rec_batt_exp_is_empty = false;
            jQuery("input.rec_batt_exp").each(function(){

                var rec_batt_exp_dom = jQuery(this);
                var parent_td = rec_batt_exp_dom.parents("td:first");

                var rec_batt_exp = rec_batt_exp_dom.val();
                var rec_batt_exp_full = parent_td.find('.rec_batt_exp_full').val();         

                var rec_batt_exp_date_prev_month = new Date(rec_batt_exp_full);       
                //var edited_ts  = rec_batt_exp_date_prev_month.setMonth(rec_batt_exp_date_prev_month.getMonth()-12);         
                var edited_ts  = rec_batt_exp_date_prev_month.setMonth(rec_batt_exp_date_prev_month.getMonth()-11);
                //var edited_ts  = rec_batt_exp_date_prev_month.setDate(rec_batt_exp_date_prev_month.getDate()-365);
                var edited_date = new Date(edited_ts);  
                console.log("edited_date:"+edited_date);
                var has_error = false;  

                var alarm_id = rec_batt_exp_dom.attr("data-col"); // alarm_id
                var ts_discarded = jQuery("input.ts_discarded[data-col='"+alarm_id+"']").val(); // discarded
                var is_li = jQuery("select.sa_power[data-col='"+alarm_id+"'] option:selected").attr('data-is_li');  // if LI no batteries

                if( ts_discarded != 1 && is_li != 1 ){ // validate only non-discarded alarm and non-li

                    if( rec_batt_exp_full != '' ){

                        var rec_batt_exp_split = rec_batt_exp.split("/");
                        var rec_batt_exp_month = rec_batt_exp_split[0];
                        
                        if( ! ( rec_batt_exp_month >= 1 && rec_batt_exp_month <= 12 ) ){ // invalid month
        
                            has_invalid_month = true;
                            has_error = true; 
        
                        }else{
        
                            // if expired                
                            if( today > edited_date ){
        
                                has_expiring_in_a_year_battery = true;
                                has_error = true;     
        
                            }
        
                        }                
        
                    }else{                    
                        has_error = true;
                        rec_batt_exp_is_empty = true;
                    }
                    
                    if( has_error == true ){
                        rec_batt_exp_dom.addClass('border-danger');    
                    }else{
                        rec_batt_exp_dom.removeClass('border-danger'); 
                    }
                
                }                        

            });

            
            // expired batteries
            if( has_expiring_in_a_year_battery == true ){

                //var d = new Date();
                var j_date = jQuery("#j_date").val();
                var d = new Date(j_date);
                
                d.setFullYear(d.getFullYear() + 1);
                //d.setDate(d.getDate() + 365);

                var year = d.getFullYear().toString().substr(-2);
                var month = '' + (d.getMonth() + 1);
                //var day = d.getDate();

                if (month.length < 2){
                    month = '0' + month;
                }            
                
                error += '<li>Battery expiry should be at least '+month+'/'+year+'</li>';    
                can_continue = true;   
            }

            // expired batteries
            if( has_invalid_month == true ){
                error += '<li>Battery Date format is invalid.</li>';  
                can_continue = false;                   
            }

            // expired batteries
            if( rec_batt_exp_is_empty == true ){
                error += '<li>Battery expiry is required.</li>';  
                can_continue = false;                   
            }

            var has_empty_sa_db = false;
            jQuery(".sa_db").each(function(){

                var sa_db_dom = jQuery(this);
                var sa_db = sa_db_dom.val();

                var alarm_id = sa_db_dom.attr("data-col"); // alarm_id
                var ts_discarded = jQuery("input.ts_discarded[data-col='"+alarm_id+"']").val(); // discarded 

                if( ts_discarded != 1 ){ // validate only non-discarded alarm

                    if( sa_db == '' ){

                        has_empty_sa_db = true;                 
                        sa_db_dom.addClass('border-danger');  
                        
                    }else{

                        if( sa_db <= 84 ){      

                            has_low_db_reading = true;                 
                            sa_db_dom.addClass('border-danger');  
        
                        }else{
                            sa_db_dom.removeClass('border-danger');                        
                        }

                    }                

                }

            });

            // dB reading
            if( has_empty_sa_db == true ){
                error += '<li>dB Reading is Required.</li>';    
                can_continue = false;                 
            }

            // low dB reading
            if( has_low_db_reading == true ){
                error += '<li>Alarm has low dB Reading. Please replace.</li>';    
                can_continue = true;                 
            }

            
            
            // Reason
            jQuery(".sa_reason").each(function(){

                var elem_dom = jQuery(this);
                var elem_val = elem_dom.val();        

                if( elem_val == '' ){      

                    has_empty_sa_reason = true;                 
                    elem_dom.addClass('border-danger');  

                }else{
                    elem_dom.removeClass('border-danger');                
                }
            });

            // Reason
            if( has_empty_sa_reason == true ){
                error += '<li>New Alarm install Reason is Required.</li>';    
                can_continue = false;                 
            }
            


            
            
            // Safety Switch tab
            if( jQuery("#has_ss").val() == 1 ){
            
                var ts_safety_switch  = jQuery(".ts_safety_switch:checked").val();
                var ss_list_count = jQuery("#ss_table_listing .safety_switch_id").length;
                var ss_image_hid  = jQuery("#ss_image_hid").val();        
                var ss_quantity = jQuery("#ss_quantity").val(); 
                var ss_location_main = jQuery(".ss_location_main").val();  
                var ss_items_tested = jQuery(".ss_items_tested").val();       
                
                // prev devs set YES = 2, i dont know why
                if( ts_safety_switch == 2 && ss_quantity > 0 && ss_list_count == 0 ){
                    error += '<li>At least one Safety Switch is required.</li>';  
                    can_continue = false;                  
                }

                // YES - 2
                if( ts_safety_switch == '' || ts_safety_switch == undefined ){
                    error += '<li>Fusebox Viewed is required.</li>';  
                    can_continue = false;                  
                }else{
                  
                    if( ts_safety_switch == 2 && ( ss_quantity == '' || ss_quantity == undefined ) ){ 
                        error += '<li>SS Quantity is required.</li>';  
                        can_continue = false;   
                    }

                }            
                
                if( ts_safety_switch == 2 && ss_image_hid == '' ){
                    error += '<li>Switch Board Image is required.</li>';  
                    can_continue = false;                  
                }                
                
                if( ts_safety_switch == 2 && ( ss_location_main == '' || ss_location_main == undefined ) ){
                    error += '<li>Switchboard Location is required.</li>';  
                    can_continue = false;                  
                }              
                
                // Reason
                var has_empty_ss_test = false;
                jQuery(".ss_test").each(function(){

                    var elem_dom = jQuery(this);
                    var elem_val = elem_dom.val();        

                    if( elem_val == '' ){      

                        has_empty_ss_test = true;                 
                        elem_dom.addClass('border-danger');  

                    }else{
                        elem_dom.removeClass('border-danger');                
                    }
                });

                // Reason
                if( has_empty_ss_test == true ){
                    error += '<li>Safety Switch Test is required.</li>';    
                    can_continue = false;                 
                }

                // Safety Switches Tested
                if( ss_items_tested == '' || ss_items_tested == undefined ){
                    jQuery("#ss_items_tested").addClass('border-danger');
                    error += '<li>Amount of Safety Switches Tested is required.</li>';    
                    can_continue = false; 
                }else{
                    jQuery("#ss_items_tested").removeClass('border-danger');
                }
            
            }


            
            // Corded Window tab
            if( jQuery("#has_cw").val() == 1 ){ 
                            
                var we_toilet = jQuery(".corded_window_id").length; // toilet
                var cw_items_tested = jQuery("#cw_items_tested").val();
                    
                // at least 1 toilet is required
                if( we_toilet == 0 ){
                    error += '<li>At least one Corded Window is required.</li>';  
                    can_continue = false;                  
                }

                if( cw_items_tested == '' || cw_items_tested == undefined ){
                    jQuery("#cw_items_tested").addClass('border-danger');
                    error += '<li>Amount of Corded Windows is required</li>';
                    can_continue = false;
                }else{
                    jQuery("#cw_items_tested").removeClass('border-danger');
                }
            
            }
            

            // Water Effeciency tab
            if( jQuery("#has_we").val() == 1 ){ 
                            
                var we_toilet = jQuery(".we_device[value=2]").length; // toilet
                var we_items_tested = jQuery("#we_items_tested").val();
                    
                // at least 1 toilet is required
                if( we_toilet == 0 ){
                    error += '<li>At least one Toilet is required.</li>';  
                    can_continue = false;                  
                }

                if( we_items_tested == ''|| we_items_tested == undefined ){
                    jQuery("#we_items_tested").addClass('border-danger');
                    error += '<li>Amount of Water Effeciency is required</li>';
                    can_continue = false;
                }else{
                    jQuery("#we_items_tested").removeClass('border-danger');
                }
            
            }
            

        }

        /*
        var ret_obj = {
            error: error,
            can_continue: can_continue,
            no_alarms: no_alarms
        };

        return ret_obj;
        */
        
        //return error;

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
                                            job_id: job_id,
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

}