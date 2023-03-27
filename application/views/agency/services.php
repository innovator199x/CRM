<style>
    .col-mdd-3{
        max-width:15.5%;
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
        'link' => "/agency/services"
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
        echo form_open('/agency/services',$form_attr);
        ?>
            <div class="for-groupss row">
                <div class="col-lg-10 col-md-12 columns">
                    <div class="row">


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
                            <label for="state"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></label>
                            <select id="state_filter" name="state_filter" class="form-control">
                                <option value="">ALL</option>
                            </select>
                            <div class="mini_loader"></div>
                        </div>

                        <div class="col-mdd-3">
                            <label for="agency_select">Sales Rep</label>
                            <select id="sales_rep_filter" name="sales_rep_filter" class="form-control field_g2">
                                <option value="">ALL</option>
                                <?php 
                                    foreach($salesrep->result_array() as $salesrep_row){
                                        $selected = ($this->input->get_post('sales_rep_filter')==$salesrep_row['salesrep'])?'selected':'';
                                ?>
                                    <option <?php echo $selected; ?> value="<?php echo $salesrep_row['salesrep'] ?>"><?php echo "{$salesrep_row['FirstName']} {$salesrep_row['LastName']}" ?></option>
                                <?php
                                    }
                                ?>
                            </select>
                            <div class="mini_loader"></div>
                        </div>

                        


                        <div class="col-mdd-3">
                            <label for="search">Phrase</label>
                            <input type="text" placeholder="ALL" name="search_filter" class="form-control" value="<?php echo $this->input->get_post('search_filter'); ?>" />
                        </div>


                        <div class="col-md-1 columns">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <input class="btn" type="submit" name="btn_search" value="Search">
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
							<th>Agency Name</th>
                            <?php 
                                
                                foreach($services->result_array() as $row){
                                ?>
                                    <th class="text-left">
                                        <img data-toggle="tooltip" title="<?php echo $row['type'] ?>" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($row['id']); ?>" />
                                    </th>
                                <?php
                                }
                            ?>
						</tr>
					</thead>

					<tbody>
                        <?php
                            foreach($lists->result_array() as $row){
                               
                        ?>

                            <tr>
                                <td>
                                    <?php
                                    echo $this->gherxlib->crmLink('vad',$row['a_id'],$row['a_name'],'',$row['priority']);
                                    ?>
                                </td>

                                <?php 

                                    foreach($services->result_array() as $row2){

                                        $agency_serv = $this->db->select('service_id,price')->where( array('agency_id'=> $row['a_id'], 'service_id' => $row2['id'] ) )->get('agency_services')->row_array();
                                        //echo $this->db->last_query();

                                        $price_var_params = array(
                                            'service_type' => $row2['id'],
                                            'agency_id' => $row['a_id']
                                        );
                                        $price_var_arr = $this->system_model->get_agency_price_variation($price_var_params);

                                        //echo $price_var_arr['price_breakdown_text'];
                                        
                                ?>
                                        <td class="text-left">
                                        
                                            <?php 
                                                if($agency_serv['service_id']!=""){
                                                    //echo "$".$agency_serv['price'];
                                                    echo $price_var_arr['price_text'];
                                                }else{
                                                    echo "&nbsp;";
                                                }
                                            ?>
                                          
                                        </td>
                                <?php
                                           
                                           
                                    }
                                ?>
                             
                            
                            </tr>

                        <?php
                            }
                       
                        ?>
                       
					</tbody>

				</table>
			</div>

		 <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
        <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
			

		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
    This page lists all agencies and the services they have active with pricing.
	</p>
    <pre>
<code>SELECT `a`.`agency_id` as `a_id`, `a`.`agency_name` as `a_name`, `a`.`state`, `a`.`postcode`, `a`.`status`, `a`.`tot_properties`
FROM `agency` AS `a`
LEFT JOIN `sub_regions` AS `sr` ON `sr`.`sub_region_id` = `a`.`postcode_region_id`
LEFT JOIN `countries` AS `c` ON a.`country_id` = c.`country_id`
WHERE `a`.`status` = 'active'
AND `a`.`country_id` = <?php echo COUNTRY ?> 
AND `a`.`agency_name` LIKE '%$agency_name%'
ORDER BY `a`.`agency_name` ASC
LIMIT 50</code>
 <pre>

</div>
<!-- Fancybox END -->


<script type="text/javascript">

    // state
    function run_ajax_state_filter(){
    var json_data = <?php echo $state_filter_json; ?>;
    var searched_val = '<?php echo $this->input->get_post('state_filter'); ?>';

    jQuery('#state_filter').next('.mini_loader').show();
    jQuery.ajax({
        type: "POST",
            url: "/sys/header_filters",
            data: { 
                rf_class: 'agency',
                header_filter_type: 'state',
                json_data: json_data,
                searched_val: searched_val
            }
        }).done(function( ret ){	
            jQuery('#state_filter').next('.mini_loader').hide();
            $('#state_filter').append(ret);
        });
    }


    jQuery(document).ready(function(){

        run_ajax_state_filter();



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
                    rf_class: 'agency',
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
                        rf_class: 'agency',
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
                        rf_class: 'agency',
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

        });

</script>