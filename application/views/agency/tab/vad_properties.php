<div class="text-left"><header class="box-typical-header">

		<div class="box-typical box-typical-padding">
			<?php
		$form_attr = array(
			'id' => 'jform'
		);
		echo form_open("/agency/view_agency_details/{$agency_id}/$tab?prop_type={$prop_type}",$form_attr);


		$export_links_params_arr = array(
			'search_filter' => $this->input->post('search_filter'),
			'prop_status' => $this->input->post('prop_status'),
			'service_type' => $this->input->post('service_type'),
			'pm_id' => $this->input->post('pm_id'),
			'prop_type' => $prop_type
		);
		$export_link_params = "/agency/view_agency_details/{$agency_id}/{$tab}?export=1&".http_build_query($export_links_params_arr);
		?>
			<div class="for-groupss row">
				<div class="col-md-9 columns">
					<div class="row">					
						<div class="col-md-2">
							<label for="phrase_select">Phrase</label>
							<input type="text" name="search_filter" class="form-control" placeholder="ALL" value="<?php echo $this->input->get_post('search_filter'); ?>" />
                        </div>
                        
                        <div class="col-md-2">
							<label for="agency_select">Property Status</label>
							<select id="prop_status" name="prop_status"  class="form-control field_g2">
                                <option value="-1">ALL</option>
							    <option value="0" <?php echo($this->input->get_post('prop_status')!=""&&$this->input->get_post('prop_status')==0)?'selected="selected"':''; ?>>Active</option>
							    <option value="1" <?php echo($this->input->get_post('prop_status')!=""&&$this->input->get_post('prop_status')==1)?'selected="selected"':''; ?>>Inactive</option>
							</select>
						</div>

						  <div class="col-md-3">
							<label for="service_type">Service Type</label>
							<select id="service_type" name="service_type"  class="form-control field_g2">
                                <option value="">ALL</option>
								  <?php foreach($agency_services as $agency_services_row) { ?>
									<option <?php echo ($this->input->get_post('service_type')==$agency_services_row['ajt_id']) ? "selected='true'" : null; ?> value="<?php echo $agency_services_row['ajt_id'] ?>"><?php echo $agency_services_row['type'] ?></option>
								  <?php } ?>
							</select>
						</div>

						<div class="col-md-3">
							<label for="service_type">Property Manager</label>
							<select id="pm_id" name="pm_id"  class="form-control field_g2">
                                <option value="">ALL</option>
                                <option  <?php echo ($this->input->get_post('pm_id')=='0') ? "selected='true'" : null; ?> value="0">No PM assigned</option>
								  <?php foreach($pm_list->result_array() as $pm_row) { 
									  if($pm_row['pm_fname']!="" && $pm_row['pm_id_new']!=""){
									?>
									<option <?php echo ($this->input->get_post('pm_id')==$pm_row['pm_id_new']) ? "selected='true'" : null; ?> value="<?php echo $pm_row['pm_id_new'] ?>"><?php echo "{$pm_row['pm_fname']} {$pm_row['pm_lname']}" ?></option>
								  <?php }} ?>
							</select>
						</div>

						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<button type="submit" class="btn btn-inline">Search</button>
						</div>
					</div>
                </div>
                
                <div class="col-md-3 columns">
                    <section class="proj-page-section float-right">
                        <div class="proj-page-attach">
                            <i class="fa fa-file-excel-o"></i>
                            <p class="name">Properties</p>
                            <p>
								<a href="<?php echo $export_link_params; ?>" target="blank">
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

			<section class="tabs-section">

            	<div class="tabs-section-nav tabs-section-nav-icons">
					<div class="tbl">
						<ul class="nav prop_nav" role="tablist">
							<li class="nav-item">
								<a class="nav-link <?php echo ($prop_type==1 || !$prop_type)?'active':'not-active' ?>"  href="/agency/view_agency_details/<?php echo $agency_id.'/'.$tab ?>?prop_type=1">
									<span class="nav-link-in">
										<i class="fa fa-calendar-check-o"></i>
											Annual Service
									</span>
								</a>
							</li>	
							<li class="nav-item">
								<a class="nav-link <?php echo ($prop_type==3)?'active':'not-active' ?>"  href="/agency/view_agency_details/<?php echo $agency_id.'/'.$tab ?>?prop_type=3">
									<span class="nav-link-in">
										<i class="fa fa-calendar-times-o"></i>
											Once-off Service
									</span>
								</a>
							</li>
							<li class="nav-item red">
								<a  class="nav-link <?php echo ($prop_type==2)?'active':'not-active' ?>"   href="/agency/view_agency_details/<?php echo $agency_id.'/'.$tab ?>?prop_type=2">
									<span class="nav-link-in">
										<span class="fa fa-hourglass-end"></span>
										Not Serviced by SATS
									</span>
								</a>
							</li>	
						</ul>
					</div>
				</div>

				<div class="tab-content">
					<?php 
						if($prop_type==1){ ##Load Sats page/tab

							$this->load->view('/agency/tab/vad_properties_tab/vad_prop_sats.php');

						}elseif($prop_type==2){ ##Load Non Sats page/tab

							$this->load->view('/agency/tab/vad_properties_tab/vad_prop_nonsats.php');

						}elseif($prop_type==3){ ##Load Onceoff

							$this->load->view('/agency/tab/vad_properties_tab/vad_prop_onceoff.php');

						}
					?>
				</div>
               
        	</section>

			

		</div>
    </section>
	</div>
	<script type="text/javascript">
		jQuery(document).ready(function(){

			//check all toggle tweak
			$('#check-all').on('change',function(){
				var obj = $(this);
				var isChecked = obj.is(':checked');
				var divbutton = $('#mbm_box');
				if(isChecked){
					divbutton.show();
					$('.prop_chk').prop('checked',true);
				}else{
					divbutton.hide();
					$('.prop_chk').prop('checked',false);
				}
			})

			//check sing checkbox toggle tweak
			$('.prop_chk').on('change',function(){
				var obj = $(this);
				var isLength = $('.prop_chk:checked').length;
				var divbutton = $('#mbm_box');
				if(isLength>0){
					divbutton.show();
				}else{
					divbutton.hide();
				}
			})


			/**Change Agency */
			$('#btn_change_agency').on('click', function(){

				var agency = $('#sel_agency').val();
				var pm = $('#pm_v2').val();

				var props = [];
				jQuery(".prop_chk:checked").each(function(){
					props.push(jQuery(this).val());	
				});

				var error = "";
				var submitCount = 0;

				if(agency==""){
					error+="Agency must not be empty\n";
				}

				if(error!=""){
					swal('',error,'error');
					return false;
				}

				// invoke ajax
				jQuery("#load-screen").show();
				jQuery.ajax({
					type: "POST",
					url: "/agency/ajax_update_property_agency",
					dataType: 'json',
					data: { 
						current_agency: <?php echo $agency_id; ?>,
						new_agency: agency,
						props: props,
						pm: pm
					}
				}).done(function( ret ){
					if(ret.status){
						jQuery("#load-screen").hide();
						swal({
							title:"Success!",
							text: "Changed Agency Successful",
							type: "success",
							showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
							timer: <?php echo $this->config->item('timer') ?>
						});
						setTimeout(function(){ location.reload(); }, <?php echo $this->config->item('timer') ?>);	
					}
				});	

			})


			$('#btn_assign_pm').on('click', function(){

				var pm = $('#sel_pm').val();
				var props = [];
				jQuery(".prop_chk:checked").each(function(){
					props.push(jQuery(this).val());	
				});
				
				var error = "";

				if(pm==""){
					error+="Property Manager must not be empty\n";
				}

				if(error!=""){
					swal('',error,'error');
					return false;
				}

				jQuery("#load-screen").show();
				jQuery.ajax({
					type: "POST",
					url: "/agency/assign_pm",
					dataType: 'json',
					data: { 
						agency_id: <?php echo $agency_id; ?>,
						props: props,
						pm: pm
					}
				}).done(function( ret ){
					if(ret.status){
						jQuery("#load-screen").hide();
						swal({
							title:"Success!",
							text: "Assign PM Successful",
							type: "success",
							showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
							timer: <?php echo $this->config->item('timer') ?>
						});
						setTimeout(function(){ location.reload(); }, <?php echo $this->config->item('timer') ?>);	
					}
				});	

			})

			$('.btn_assign').click(function(e){
				e.preventDefault();
				
				var type = $(this).attr('data-val');
				
				if(type=='change_pm'){
					$('.gbox_main_change_pm').show();
					$('.gbox_main_change_agency').hide();
				}else if(type=='change_agency'){
					$('.gbox_main_change_agency').show();
					$('.gbox_main_change_pm').hide();
				}

			})

			$('#sel_agency').change(function(){

				var agency_id = $(this).val();

				if( $(this).val()!="" ){

					//request ajax > get agency PM
					jQuery("#load-screen").show();
					jQuery.ajax({
						type: "POST",
						url: "/property_ajax/property_mod/get_property_manager_by_agency_id",
						data: {
							agency_id: agency_id
						}
					}).done(function( ret ){
						jQuery("#load-screen").hide();
						$('#pm_v2').html(''); //clear pm dropdwon first
						$('#pm_v2').append(ret);
						//$('#pm_v2 option:first').text('No PM assigned');

						//show PM dropdown
						$('.pm_box_v2').show();
					});	

				}else{
					$('.pm_box_v2').hide();
				}
				
			})
			

		});
	</script>