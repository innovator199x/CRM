<style>
    .col-mdd-3{
        max-width:15.5%;
    }
    .paghot .col-mdd-2{
        margin-left:15px;
        width:14%;
    }
</style>

<?php
  $export_links_params_arr = array(
    'agency_filter' => $this->input->get_post('agency_filter'),
	'state_filter' => $this->input->get_post('state_filter'),
	'sales_rep_filter' =>  $this->input->get_post('sales_rep_filter'),
	'sub_region_ms' => $this->input->get_post('sub_region_ms'),
	'search_filter' => $this->input->get_post('search_filter'),
	'using_filter' => $this->input->get_post('using_filter')
);
$export_link_params = '/agency/view_target_agencies/?export=1&'.http_build_query($export_links_params_arr);
?>

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
        'link' => "/agency/view_target_agencies"
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
        echo form_open('/agency/view_target_agencies',$form_attr);
        ?>
            <div class="for-groupss row">
                <div class="col-lg-10 col-md-12 columns">
                    <div class="row paghot">

                        
                        <div class="col-mdd-2">
							<label for="agency_select">Agency</label>
							<select id="agency_filter" name="agency_filter" class="form-control field_g2">
                            <option value="">ALL</option>
                            <?php 
                                foreach($agency_filter_list->result_array() as $agency_row){
                            ?>
                                    <option value="<?php echo $agency_row['agency_id'] ?>"><?php echo $agency_row['agency_name'] ?></option>
                            <?php
                                }
                            ?>
							</select>
						</div>

                        <div class="col-mdd-2">
                            <label for="state"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></label>
                            <select id="state_filter" name="state_filter" class="form-control">
                                <option value="">ALL</option>
                            </select>
                            <div class="mini_loader"></div>
                        </div>

                        <div class="col-mdd-2">
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

                        <div class="col-mdd-2">
                        
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


                        <div class="col-mdd-2">
                            <label for="search">Phrase</label>
                            <input type="text" placeholder="ALL" name="search_filter" class="form-control" value="<?php echo $this->input->get_post('search_filter'); ?>" />
                        </div>

                            <div class="col-mdd-2">
                            <label for="agency_select">Using</label>
                            <select id="using_filter" name="using_filter" class="form-control">
                                <option value="">ALL</option>

                                <?php 
                                    foreach($agency_using->result() as $using_row){
                                    $selected = ($this->input->get_post('using_filter')==$using_row->agency_using_id)?'selected':'';
                                ?>
                                    <option <?php echo $selected; ?> value="<?php echo $using_row->agency_using_id ?>"><?php echo $using_row->name ?></option>
                                <?php
                                    }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-1 columns">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <input class="btn" type="submit" name="btn_search" value="Search">
                        </div>
                        
                    </div>

                </div>
                
                <!-- DL ICONS START -->
                <?php 
                $date = ($this->input->get_post('date')!="")?date('Y-m-d',$this->input->get_post('date')):NULL;
                ?>
                <div class="col-lg-2 col-md-12 columns">
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
                </div>
                <!-- DL ICONS END -->
                                    
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
							<th>Address</th>
							<th><?php echo $this->gherxlib->getDynamicState($defaultCountry); ?></th>
							<th><?php echo $this->customlib->getDynamicRegionViaCountry($defaultCountry);  ?></th>
							<th>Properties</th>
							<th>Last Contact</th>
							<th>Next Contact</th>
							<th>Sales Only</th>
						</tr>
					</thead>

					<tbody>
                        <?php
                            if($this->input->get_post('btn_search')){
                            foreach($lists as $row){

                                $getRegion = $this->system_model->getRegion_v2($row['postcode'])->row();
                        ?>

                            <tr>
                                <td>
                                    <?php
                                    echo $this->gherxlib->crmLink('vad',$row['a_id'],$row['a_name'],'',$row['priority']);
                                    ?>
                                </td>
                                <td>
                                    <?php
                                        $address = ($row['address_1']!="")? "{$row['address_1']} {$row['address_2']}, {$row['address_3']}":NULL;
                                        echo $address;
                                    ?>
                                </td>
                                <td>
                                    <?php echo $row['state']; ?>
                                </td>
                                <td>
                                    <?php 
                                        echo $getRegion->subregion_name;
                                    ?>
                                </td>
                               
                                <td><?php echo $row['tot_properties']; ?></td>
                                <td>
                                    <?php
                                       // $lastContact_query  = $this->agency_model->get_agency_last_contact($row['a_id'])->row_array();
                                        //$lc = ($this->system_model->isDateNotEmpty($lastContact_query['eventdate']))?$this->system_model->formatDate($lastContact_query['eventdate'],'d/m/Y'):NULL;
                                       // echo $lc;

                                       echo ($this->system_model->isDateNotEmpty($row['last_contact']))?date("d/m/Y",strtotime($row['last_contact'])):''
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    
                                    $nexContact = $this->agency_model->get_agency_next_contact($row['a_id'])->row_array();
                                    $nexContact = $nexContact['next_contact'];

                                    $nexContact_sr = $this->agency_model->get_agency_next_contact_sr($row['a_id'])->row_array();
                                    $nexContact_sr = $nexContact_sr['next_contact'];

                                    if ($nexContact > $nexContact_sr){
                                        $next_contact = $nexContact;
                                    }
                                    else{
                                        $next_contact = $nexContact_sr;
                                    }

                                    //$nc = ($this->system_model->isDateNotEmpty($nexContact['next_contact']))?$this->system_model->formatDate($nexContact['next_contact'],'d/m/Y'):NULL;
                                    $nc = ($this->system_model->isDateNotEmpty($next_contact))?$this->system_model->formatDate($next_contact,'d/m/Y'):NULL;
                                    echo $nc;
                                
                                    /*
                                    if(!empty($row['next_contact'])){
                                        echo date("d/m/Y",strtotime($row['next_contact']));
                                    }
                                    */
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    $query = $this->db->select('marker_id')->from('agency_markers')->where('agency_id', $row['a_id'])->where('marker_id', '2')->get()->row_array();
                                    $marker_id = $query['marker_id'];
                                    ?>
                                    <input type="checkbox" id="<?php echo $row['a_id']; ?>" name="marker_id" value="1" onclick="is_sales_target(this.id)" <?php echo ( $marker_id == 2 )?'checked="checked"':null; ?> />
                                </td>   
                            </tr>

                        <?php
                            }
                        }else{
                            echo "<tr><td colspan='8'>Please press search to display results</td></tr>";
                        }
                        ?>
                       
					</tbody>

				</table>
			</div>
<?php 
  if($this->input->get_post('btn_search')){
?>
		 <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
        <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
			
<?php
  }
?>
		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
    This page displays all agencies that have a status of 'Target'
	</p>
    <pre>
        <code><?php echo $sql_query; ?></code>
    </pre>
</div>
<!-- Fancybox END -->


<script type="text/javascript">

    function is_sales_target(id){
    jQuery.ajax({
        type: "POST",
            url: "/agency/is_sales_target",
            data: { 
                agency_id: id
            }
        }).done(function( ret ){	
        });
    }

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