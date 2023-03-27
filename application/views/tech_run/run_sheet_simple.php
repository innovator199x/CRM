<style>
.map_pins {
    width: 35px;
}
</style>
<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => "{$title}",
			'status' => 'active',
			'link' => $uri
		)
	);
    $bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">

			<div class="row">

				<div class="col-sm-12 text-center">

					<div class="float-left">
						<a href="/tech_run/run_sheet/<?php echo $tr_id; ?>">
							<button type="button" class="btn">Back to Full Version</button>		
						</a>
					</div>						

				</div>
			</div>

		</div>
	</header>

	<section>

		<div class="body-typical-body">
			<div class="table-responsive">
                <table id="tbl_maps" class="table main-table tds_tbl">
                    <thead>
                        <tr>
                            <th>Time</th>	
                            <th>&nbsp;</th>						
                            <th>Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <?php echo $accom_name; ?>
                            </td>
                            <td>
                                <img class="map_pins" src="/images/google_map/circle-pin-blue.png" />
                            </td>
                            <td>
                                <a href='//maps.apple.com/?q=<?php echo $start_agency_address; ?>'>
                                    <?php echo $start_agency_address; ?>
                                </a>
                            </td>
                        </tr>
                        <?php 
						$j = 2;
						$comp_count = 0;
						$jobs_count = 0;	
						
						if( isset($jr_list2) && $jr_list2->num_rows() > 0 ){
						
							foreach($jr_list2->result_array() as $row){
																					
                                // ROW IS JOBS
                                if( $row['row_id_type'] == 'job_id' ){

                                    $jr_sql = $this->tech_model->getJobRowData($row['row_id'],$this->config->item('country'));
                                    $row2 = $jr_sql->row_array();

                                    $bgcolor = null;

                                    // completed row bg color
                                    if($row2['ts_completed']==1){
                                        $bgcolor = "#c2ffa7";                                        
                                    }

                                    $paddress =  $row2['p_address_1']." ".$row2['p_address_2'].", ".$row2['p_address_3'];
                                    ?>                                    
                                    <tr style="background-color:<?php echo $bgcolor; ?>">
                                        <td>
                                            <?php echo $row2['time_of_day']; ?>
                                        </td>
                                        <td>
                                            <img class="map_pins" src="/images/google_map/pin-red.png" />
                                        </td>
                                        <td>
                                            <a href='//maps.apple.com/?q=<?php echo $paddress; ?>'>
                                                <?php echo $paddress; ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php                                    
                                // ROW IS KEYS
                                }else if( $row['row_id_type'] == 'keys_id' ){

                                    // KEYS
                                    $k_sql = $this->tech_model->getTechRunKeys($row['row_id']);
                                    $kr = $k_sql->row_array();

                                    // FIRST NATIONAL AGENCIES script
                                    $fn_agency_arr = $this->system_model->get_fn_agencies();
                                    $fn_agency_main = $fn_agency_arr['fn_agency_main'];
                                    $fn_agency_sub =  $fn_agency_arr['fn_agency_sub'];
                                    //$fn_agency_sub_imp = implode(",",$fn_agency_sub);

                                    // VISION REAL ESTATE script
									$vision_agency_arr = $this->system_model->get_vision_agencies();
									$vision_agency_main = $vision_agency_arr['vision_agency_main'];
									$vision_agency_sub =  $vision_agency_arr['vision_agency_sub'];
									//$vision_agency_sub_imp = implode(",",$vision_agency_sub);

                                    $nobk = $this->tech_model->getNumberOfBookedKeys($tech_id,$date,$this->config->item('country'),$kr['agency_id']);

                                    $agency_name = str_replace('*do not use*','',$kr['agency_name']);

                                    if( $nobk > 0 || in_array($kr['agency_id'],$fn_agency_sub) || in_array($kr['agency_id'],$vision_agency_sub) ){ // only show agency keys, that has remaining booked keys	

                                        // address
                                        if( $kr['agen_add_id'] > 0 ){ // key address
                                            
                                            $key_address = "{$kr['agen_add_street_num']} {$kr['agen_add_street_name']}, {$kr['agen_add_suburb']}";
        
                                        }else{ // default
        
                                            $key_address = "{$kr['address_1']} {$kr['address_2']}, {$kr['address_3']}";
        
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <?php echo $kr['agency_hours']; ?>
                                            </td>
                                            <td>
                                                <img class="map_pins" src="/images/google_map/circle-key-blue.png" />
                                            </td>
                                            <td>
                                                <a href='//maps.apple.com/?q=<?php echo $key_address; ?>'>
                                                    <?php echo $key_address; ?> <?php ($kr['agency_id']==4102)?'(IMPORTANT - Read Agency Notes)':null; ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php
                                    }                                                                               
                                
                                // ROW IS SUPPLIER
                                }else if( $row['row_id_type'] == 'supplier_id' ){

                                    // supplier
                                    $sup_sql = $this->tech_model->getTechRunSuppliers($row['row_id']);
                                    $sup = $sup_sql->row_array();

                                    if($sup['on_map']==1){
                                    ?>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>
                                            <img class="map_pins" src="/images/google_map/circle-key-blue.png" />
                                        </td>
                                        <td>
                                            <a href='//maps.apple.com/?q=<?php echo $sup['sup_address']; ?>'>
                                                <?php echo $sup['sup_address']; ?>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php                                                                        
                                    }										

                                }								
													
							} 

						}else{ 
							echo "<tr><td colspan='100%'>No Data</td></tr>";	
						}
                        ?>
                        <tr>
                            <td>
                                <?php echo $end_accom_name; ?>
                            </td>
                            <td>
                                <img class="map_pins" src="/images/google_map/circle-pin-blue.png">
                            </td>
                            <td>
                                <a href='//maps.apple.com/?q=<?php echo $end_agency_address; ?>' />
                                    <?php echo $end_agency_address; ?>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
			</div>

			

			<nav aria-label="Page navigation example" style="text-align:center">
				<?php echo $pagination; ?>
			</nav>

			<div class="pagi_count text-center">
				<?php echo $pagi_count; ?>
			</div>

		</div>
	</section>

</div>

<!-- Fancybox Start -->

<!-- about page -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>						
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4>Run Sheet</h4>
	<p>
	This page displays your run for the day
	</p>


</div>

<!-- Fancybox END -->



