<style type="text/css">
	.col-mdd-3{
		max-width: 15.1%;
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
        'link' => "/jobs/urgent_jobs"
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);
?>

<header class="box-typical-header">

    <div class="box-typical box-typical-padding">
        <?php
    $form_attr = array(
        'id' => 'jform'
    );
    echo form_open('/jobs/urgent_jobs',$form_attr);
    ?>
        <div class="for-groupss row">
            <div class="col-lg-10 col-md-12 columns">

               
                      <div class="row">

                      <div class="col-mdd-3">
							<label for="jobtype_select">Job Type</label>
							<select id="job_type_filter" name="job_type_filter" class="form-control field_g2">
								<option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>
						</div>

                        <div class="col-mdd-3">
							<label for="service_select">Service</label>
							<select id="service_filter" name="service_filter" class="form-control field_g2">
								<option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>
						</div>

                        <div class="col-mdd-3">
							<label for="agency_select">Agency</label>
							<select id="agency_filter" name="agency_filter"  class="form-control field_g2">
								<option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>
						</div>	

                        <div class="col-mdd-3">
						
                        <div class="fl-left region_filter_main_div">
                            <label>	
                            <?php 
                                $defaultCountry = $this->config->item('country');
                                echo $this->customlib->getDynamicRegionViaCountry($defaultCountry); 
                            ?>:
                            </label>
                            <input type="text" name="region_filter_state" id='region_filter_state' class="form-control region_filter_state" placeholder="ALL" readonly="readonly" />
                            
                            <div id="region_dp_div" class="box-typical region_dp_div">
                            
                                <div class="region_dp_header">										
                                </div>
                                
                                <div class="region_dp_body">								
                                </div>
                                
                            </div>	
                            
                        </div>
                    
                    </div>	

                    <div class="col-mdd-3">
							<label for="date_select">Date</label>
							<input name="date_filter" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text" placeholder="ALL" value="<?php echo $this->input->get_post('date_filter'); ?>">
						</div>


                        <div class="col-mdd-3">
                            <label for="phrase_select">Phrase</label>
                            <input type="text" placeholder="ALL" name="search_filter" class="form-control"  value="<?php echo $this->input->get_post('search_filter'); ?>" />
                        </div>

                        <div class="col-md-1 columns">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <button type="submit" class="btn btn-inline">Search</button>
                        </div>
                    </div>
              
            </div>


        </div>
        </form>
    </div>
</header>

    
<section>
    <div class="body-typical-body">
        <div class="table-responsive">
            <table class="table table-hover main-table">
                <thead>
                    <tr>
                        <th>Age</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Job Type</th>
                        <th>Service</th>
                        <th>Address</th>
                        <th>Agency</th>
                        <th><?php echo $this->gherxlib->getDynamicRegion($this->config->item('country')); ?></th>
                        <th>Sub Region</th>
                        <th>Comments</th>
                        <th>Job #</th>
                    </tr>
                </thead>

                <tbody>
                <?php 
                    if($lists->num_rows()>0){
                        foreach($lists->result_array() as $row){
   
                            $getRegion = $this->system_model->getRegion_v2($row['p_postcode']);

                ?>

                    <tr>
                       <td>
                            <?php  
								echo $this->gherxlib->getAge($row['j_created']); 
							?>
                       </td>
                       <td>
							<?php 
								echo ($this->system_model->isDateNotEmpty($row['start_date']))?date('d/m/Y', strtotime($row['start_date'])):(($row['no_dates_provided']==1)?'N/A':''); 
							?>
						</td>
						<td>
							<?php 
								echo ($this->system_model->isDateNotEmpty($row['due_date']))?date('d/m/Y', strtotime($row['due_date'])):(($row['no_dates_provided']==1)?'N/A':''); 
							?>
						</td>
                        <td>
							<?php
								echo $this->gherxlib->getJobTypeAbbrv($row['j_type']);
							?>
						</td>
                        <td>
								<img data-toggle="tooltip" title="<?php echo $row['ajt_type'] ?>" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($row['j_service']); ?>" />
						</td>
                       <td>
                            <?php
								$prop_address = $row['p_address_1']." ".$row['p_address_2'].", ".$row['p_address_3'];
								echo $this->gherxlib->crmLink('vpd',$row['prop_id'],$prop_address);
							?>
                       </td>
                       <td>
                            <?php
                                echo $this->gherxlib->crmLink('vad',$row['a_id'],$row['agency_name'], '', $row['priority']);
                            ?>
                       </td>
                       <td>
                            <?php 
								echo $getRegion->row()->region_name;
							?>
                       </td>
                       <td>
                            <?php
								echo $getRegion->row()->subregion_name;
							?>
                       </td>
                       <td>
                            <?php 
                                echo $row['j_comments'];
                            ?>
                       </td>
                       <td>
                            <?php
                                echo $this->gherxlib->crmLink('vjd',$row['jid'],$row['jid']);
                            ?>
                       </td>
                    </tr>

                <?php
                     }
                    }else{
                        echo "<tr><td colspan='11'>No Data</td></tr>";
                    }
                
                ?>
                 
                </tbody>

            </table>

        </div>

        <nav class="text-center">
            <?php echo $pagination; ?>
        </nav>

        <div class="pagi_count text-center">
            <?php echo $pagi_count; ?>
        </div>

    </div>
</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

<h4><?php echo $title; ?></h4>
<p>This page displays all jobs that are marked urgent and not completed.</p>
<pre>
<code>SELECT `j`.`id` AS `jid`, `j`.`status` AS `j_status`, `j`.`service` AS `j_service`, `j`.`created` AS `j_created`, `j`.`date` AS `j_date`, `j`.`comments` AS `j_comments`, `j`.`job_price` AS `j_price`, `j`.`job_type` AS `j_type`, `j`.`start_date`, `j`.`due_date`, `j`.`no_dates_provided`, `j`.`bne_to_call_notes`, `p`.`property_id` AS `prop_id`, `p`.`address_1` AS `p_address_1`, `p`.`address_2` AS `p_address_2`, `p`.`address_3` AS `p_address_3`, `p`.`state` AS `p_state`, `p`.`postcode` AS `p_postcode`, `p`.`comments` AS `p_comments`, `p`.`deleted` AS `p_deleted`, `a`.`agency_id` AS `a_id`, `a`.`agency_name` AS `agency_name`, `a`.`phone` AS `a_phone`, `a`.`address_1` AS `a_address_1`, `a`.`address_2` AS `a_address_2`, `a`.`address_3` AS `a_address_3`, `a`.`state` AS `a_state`, `a`.`postcode` AS `a_postcode`, `jt`.`abbrv`, `ajt`.`id` AS `ajt_id`, `ajt`.`type` AS `ajt_type`
FROM `jobs` AS `j`
LEFT JOIN `property` AS `p` ON j.`property_id` = p.`property_id`
LEFT JOIN `agency` AS `a` ON  p.`agency_id` = a.`agency_id`
LEFT JOIN `job_type` AS `jt` ON j.`job_type` = jt.`job_type`
LEFT JOIN `alarm_job_type` AS `ajt` ON j.`service` = ajt.`id`
WHERE `j`.`del_job` = 0
AND `p`.`deleted` = 0
AND `a`.`status` = 'active'
AND `a`.`country_id` = 1
AND `j`.`status` = 'To Be Booked'
AND `j`.`urgent_job` = 1
ORDER BY `j`.`created` DESC
LIMIT 50</code>
</pre>

</div>
<!-- Fancybox END -->


<script type="text/javascript">

                    // service
                    function run_ajax_service_filter(){

                    var json_data = <?php echo $service_filter_json; ?>;
                    var searched_val = '<?php echo $this->input->get_post('service_filter'); ?>';

                    jQuery('#service_filter').next('.mini_loader').show();
                    jQuery.ajax({
                        type: "POST",
                            url: "/sys/header_filters",
                            data: { 
                                rf_class: 'jobs',
                                header_filter_type: 'service',
                                json_data: json_data,
                                searched_val: searched_val
                            }
                        }).done(function( ret ){	
                            jQuery('#service_filter').next('.mini_loader').hide();
                            $('#service_filter').append(ret);
                        });
                                
                    }

                // agency
                function run_ajax_agency_filter(){

                var json_data = <?php echo $agency_filter_json; ?>;
                var searched_val = '<?php echo $this->input->get_post('agency_filter'); ?>';

                jQuery('#agency_filter').next('.mini_loader').show();
                jQuery.ajax({
                    type: "POST",
                        url: "/sys/header_filters",
                        data: { 
                            rf_class: 'jobs',
                            header_filter_type: 'agency',
                            json_data: json_data,
                            searched_val: searched_val
                        }
                    }).done(function( ret ){	
                        jQuery('#agency_filter').next('.mini_loader').hide();
                        $('#agency_filter').append(ret);
                    });
                            
                }

                // job type	
                function run_ajax_job_filter(){

                    var json_data = <?php echo $job_type_filter_json; ?>;
                    var searched_val = '<?php echo $this->input->get_post('job_type_filter'); ?>';

                    jQuery('#job_type_filter').next('.mini_loader').show();
                    jQuery.ajax({
                        type: "POST",
                            url: "/sys/header_filters",
                            data: { 
                                rf_class: 'jobs',
                                header_filter_type: 'job_type',
                                json_data: json_data,
                                searched_val: searched_val
                            }
                        }).done(function( ret ){	
                            jQuery('#job_type_filter').next('.mini_loader').hide();
                            jQuery('#job_type_filter').append(ret);
                        });
                                
                }

                jQuery(document).ready(function(){

                    // run headler filter ajax
                    run_ajax_job_filter();
                    run_ajax_service_filter();
                    run_ajax_agency_filter();
                    


                    // region filter selection, cant trigger without the timeout, dunno why :( 
                        <?php
                    if( !empty($this->input->get_post('sub_region_ms')) ){ ?>
                        setTimeout(function(){ 
                            jQuery("#region_filter_state").click();
                            }, 500);		
                    <?php
                    }
                    ?>

                    // region filter click
                    jQuery('.region_filter_main_div').on('click','.region_filter_state',function(){
                            
                            var obj  = jQuery(this);
                            var state_chk = obj.prop("checked");
                            var region_filter_json = <?php echo $region_filter_json; ?>;
                            var state_ms_json = <?php echo $state_ms_json; ?>;
                            
                            jQuery("#load-screen").show();
                            
                            jQuery.ajax({
                                type: "POST",
                                url: "/sys/getRegionFilterState",
                                data: { 
                                    rf_class: 'jobs',
                                    region_filter_json: region_filter_json
                                }
                            }).done(function( ret ){
                                
                                jQuery("#load-screen").hide();
                                jQuery(".region_dp_header").html(ret);
                                
                                // searched
                                var state_ms_json_num = state_ms_json.length;
                                if( state_ms_json_num > 0 ){				
                                    for( var i=0; i < state_ms_json_num; i++ ){
                                        jQuery("#region_dp_div .state_ms[value='"+state_ms_json[i]+"']").click();
                                    }
                                }
                                
                                
                            });
                                    
                        });
                        
                        // state click
                        jQuery('.region_dp_div').on('click','.state_ms',function(){
                            
                            var obj  = jQuery(this);
                            var state = obj.val();
                            var state_chk = obj.prop("checked");
                            var region_filter_json = <?php echo $region_filter_json; ?>;
                            var region_ms_json = <?php echo $region_ms_json; ?>;
                            
                            if(state_chk==true){
                                
                                obj.parents(".state_div:first").find(".rf_state_lbl").addClass("rf_select");
                                jQuery("#load-screen").show();
                                
                                jQuery.ajax({
                                    type: "POST",
                                    url: "/sys/getMainRegion",
                                    data: { 
                                        state: state,
                                        rf_class: 'jobs',
                                        region_filter_json: region_filter_json
                                    }
                                }).done(function( ret ){
                                    
                                    jQuery("#load-screen").hide();
                                    obj.parents(".state_div:first").find(".region_div").html(ret);

                                    // searched
                                    var region_ms_json_num = region_ms_json.length;
                                    if( region_ms_json_num > 0 ){				
                                        for( var i=0; i < region_ms_json_num; i++ ){
                                            obj.parents(".state_div:first").find(".region_ms[value='"+region_ms_json[i]+"']").click();
                                        }
                                    }
                                    
                                });
                                
                            }else{
                                obj.parents(".state_div:first").find(".rf_state_lbl").removeClass("rf_select");
                                obj.parents(".state_div:first").find(".region_div").html('');			
                            }	
                                    
                        });
                        
                        
                        // region click
                        jQuery('.region_dp_div').on('click','.region_ms',function(){
                            
                            var obj  = jQuery(this);
                            var region_id = obj.val();
                            var state_chk = obj.prop("checked");
                            var region_filter_json = <?php echo $region_filter_json; ?>;
                            var sub_region_ms_json = <?php echo $sub_region_ms_json; ?>;
                            
                            if(state_chk==true){
                                
                                obj.parents(".region_div_chk:first").find(".rf_region_lbl").addClass("rf_select");
                                jQuery("#load-screen").show();
                                
                                jQuery.ajax({
                                    type: "POST",
                                    url: "/sys/getSubRegion",
                                    data: { 
                                        region_id: region_id,
                                        rf_class: 'jobs',
                                        region_filter_json: region_filter_json
                                    }
                                }).done(function( ret ){
                                    
                                    jQuery("#load-screen").hide();
                                    obj.parents(".region_div_chk:first").find(".sub_region_div").html(ret);

                                    // searched
                                    var sub_region_ms_json_num = sub_region_ms_json.length;
                                    if( sub_region_ms_json_num > 0 ){				
                                        for( var i=0; i < sub_region_ms_json_num; i++ ){
                                            obj.parents(".region_div_chk:first").find(".sub_region_ms[value='"+sub_region_ms_json[i]+"']").click();
                                        }
                                    }
                                    
                                });
                                
                                
                            }else{
                                obj.parents(".region_div_chk:first").find(".rf_region_lbl").removeClass("rf_select");
                                obj.parents(".region_div_chk:first").find(".sub_region_div").html('');
                            }	
                                    
                        });
                        
                        // sub region 
                        jQuery('.region_dp_div').on('click','.sub_region_ms',function(){
                            
                            var obj  = jQuery(this);
                            var region_id = obj.val();
                            var state_chk = obj.prop("checked");
                            
                            if(state_chk==true){			
                                obj.parents(".sub_region_div_chk:first").find(".rf_sub_region_lbl").addClass("rf_select");			
                            }else{
                                obj.parents(".sub_region_div_chk:first").find(".rf_sub_region_lbl").removeClass("rf_select");
                            }	
                                    
                        });


                })

</script>