<style>
    .col-mdd-3{
        max-width:15.5%;
    }
    .mailer_dl_div{
        margin-right:10px;
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
        'link' => "/agency/view_agencies_and_services"
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);


$export_links_params_arr = array(
    'state_filter' => $this->input->get_post('state_filter'),
    'sales_rep_filter' => $this->input->get_post('sales_rep_filter'),
    'sub_region_ms' => $this->input->get_post('sub_region_ms'),
    'search_filter' => $this->input->get_post('search_filter'),
);
$export_link_params = '/agency/view_agencies_and_services/?export=1&export_type=a&'.http_build_query($export_links_params_arr);

$mailerexport_links_params_arr = array(
    'state_filter' => $this->input->get_post('state_filter'),
    'sales_rep_filter' => $this->input->get_post('sales_rep_filter'),
    'sub_region_ms' => $this->input->get_post('sub_region_ms'),
    'search_filter' => $this->input->get_post('search_filter'),
);
$mailerexport_link_params = '/agency/view_agencies_and_services/?export=1&export_type=b&'.http_build_query($mailerexport_links_params_arr);


?>

    <header class="box-typical-header">

<div class="box-typical box-typical-padding">
    <?php
$form_attr = array(
    'id' => 'jform'
);
echo form_open('/agency/view_agencies_and_services',$form_attr);
?>
    <div class="for-groupss row">
        <div class="col-md-8 columns">
            <div class="row">
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
                    <label for="search">Phrase</label>
                    <input type="text" placeholder="ALL" name="search_filter" class="form-control" value="<?php echo $this->input->get_post('search_filter'); ?>" />
                </div>
                <div class="col-md-1 columns">
                    <label class="col-sm-12 form-control-label">&nbsp;</label>
                    <input class="btn" type="submit" name="btn_search" value="Search">
                </div>
            </div>
        </div>

            <div class="col-md-4 columns">
                <section class="proj-page-section float-right">
                    <div class="proj-page-attach">
                        <i class="fa fa-file-excel-o"></i>
                        <p class="name"><?php echo $title; ?></p>
                        <p>
                            <a href="<?php echo $export_link_params ?>" target="blank">
                                Export
                            </a>
                        </p>
                    </div>
                </section>
                <section class="proj-page-section float-right mailer_dl_div">
                    <div class="proj-page-attach">
                        <i class="fa fa-file-excel-o"></i>
                        <p class="name">Mailer</p>
                        <p>
                            <a href="<?php echo $mailerexport_link_params ?>" target="blank">
                                 Export
                            </a>
                        </p>
                    </div>
                </section>
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
                            <th>ABN Number</th>
                            <th>Phone</th>
                            <th>Contact</th>
                            <th>Last Contact</th>
                            <th>Sales Rep</th>
                            <th><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></th>
                            <th><?php echo $this->gherxlib->getDynamicRegion($this->config->item('country')); ?></th>
                            <th><i class="font-icon font-icon-home" style="font-size:20px;"></i></th>
                            <?php
                                foreach($alarmJobTypes as $ajt){
                            ?>
                                    <th><img src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($ajt['id']); ?>" /></th>
                            <?php
                                }
                            ?>
						</tr>
					</thead>

					<tbody>
                        <?php
                                foreach ($agencies as $row) {

                        ?>
                            <tr>
                                <td>
                                    <?php
                                    echo $this->gherxlib->crmLink('vad',$row['a_id'],$row['a_name'],'',$row['priority']);
                                    ?>
                                </td>

                                <td><?php echo $row['abn'] ?></td>

                                <td><?php echo $row['phone'] ?></td>

                                <td><?php echo "{$row['contact_first_name']} {$row['contact_last_name']}"; ?></td>

                                <td>
                                    <?php
                                     $where_tt = array(
                                        'agency_id' => $row['a_id'],
                                        'display_in_vad' => 1
                                    );
                                     $log_sql = $this->db->select('*')->from('logs')->where($where_tt)->order_by('created_date','desc')->limit(1)->get();
                                     //$log_sql = $this->db->select('*')->from('agency_event_log')->where('agency_id', $row['a_id'])->order_by('eventdate','desc')->limit(1)->get();
                                     $log_row = $log_sql->row_array();
                                    ?>
                                    <?php 
                                        echo ($log_row['display_in_vad']!="")?"{$this->gherxlib->crmLink('vad',$row['a_id'],date("d/m/Y",strtotime($log_row['created_date'])))}":''; 
                                       // echo ($log_row['eventdate']!="")?"{$this->gherxlib->crmLink('vad',$row['a_id'],date("d/m/Y",strtotime($log_row['eventdate'])))}":''; 
                                    ?>
                                </td>

                                <td>
                                    <?php echo $this->system_model->formatStaffName($row['FirstName'], $row['LastName']); ?>
                                </td>

                                <td><?php echo $row['state'] ?></td>
                                
                                <td>
                                    <?php 
                                        echo $row['region']['subregion_name'];
                                    ?>
                                </td>
                                <td>
                                    <?php
                                        echo $row['tot_properties'];
                                        $prop_tot += intval($row['tot_properties']);
                                    ?>
                                    
                                </td>

                                <?php 
                                    $i = 0;
                                    foreach($alarmJobTypes as $ajt){
                                        $count = $row[ 'ajt_counts' ][ $ajt[ 'id' ] ];
                                        echo "<td align='center'>";		
                                        //echo $serv_count = $this->system_model->getServiceCount($row['a_id'],$ajt['id']);
                                        echo ($count>0)?$count:'0';
                                        echo "</td>";
                                        $serv_total[$i] += intval($count);
                                        $i++;
                                    }
                                ?>

                            </tr>
                         
                        <?php
                            }
                       
                        ?>
                           <tr>
                                <td align='center' colspan="8"><b>Total</b></td>
                                <td align='center'><b><?php echo $prop_tot; ?></b></td>
                                <?php 
                                foreach($serv_total as $val){ ?>
                                    <td align='center'><b><?php echo $val; ?></b></td>
                                <?php
                                }
                                ?>
                            </tr>
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
    This page displays all active agencies.
	</p>
    <pre>
<code>SELECT `a`.`contact_first_name`, `a`.`contact_last_name`, `a`.`phone`, `a`.`abn`, `a`.`agency_name` as `a_name`, `a`.`address_1`, `a`.`address_2`, `a`.`address_3`, `a`.`state`, `a`.`postcode`, `a`.`status`, `a`.`agency_id` as `a_id`, `a`.`tot_properties`, `a`.`legal_name`, `a`.`account_emails`, `a`.`agency_emails`, `a`.`contact_phone`, `a`.`contact_email`, `a`.`tot_properties`, `a`.`country_id`, `a`.`send_emails`, `a`.`send_combined_invoice`, `a`.`send_entry_notice`, `a`.`require_work_order`, `a`.`auto_renew`, `a`.`key_allowed`, `a`.`key_email_req`, `a`.`trust_account_software`, `sa`.`FirstName`, `sa`.`LastName`, `sr`.`subregion_name` as `postcode_region_name`
FROM `agency` AS `a`
LEFT JOIN `sub_regions` AS `sr` ON `sr`.`sub_region_id` = `a`.`postcode_region_id`
LEFT JOIN `staff_accounts` AS `sa` ON a.`salesrep` = sa.`StaffID`
LEFT JOIN `countries` AS `c` ON a.`country_id` = c.`country_id`
WHERE `a`.`status` = 'active'
AND `a`.`country_id` = <?php echo COUNTRY ?> 
ORDER BY `a`.`agency_name` ASC
LIMIT 50</code>
    </pre>

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