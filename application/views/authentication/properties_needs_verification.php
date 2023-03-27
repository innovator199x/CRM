<style>
.move_prop_to_div,
.move_prop_process_btn{
	display:none;
}
.duplicate_users_tbl .btn{
    width: 145px;
}
.fancybox-content {
    width: auto;
}
.attached_prop_div{
	margin: 25px 0;
}
.properties_need_verification_tbl .col_action{
	width: 20%;
}
.col_action button {
	width: 122px;
	margin-bottom: 5px;
}
.edit_hid{
	display:none;
}
.select_action{
	width: 130px;
}
</style>
<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
    $bc_items = array(
        array(
            'title' => $title,
            'status' => 'active',
            'link' =>  $uri
        )
    );
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	<?php 
	if( validation_errors() ){ ?>
		<div class="alert alert-danger">
		<?php echo validation_errors(); ?>
		</div>
	<?php
	}	
	?>

	<?php 
		$export_links_params_arr = array(
			'agency_filter' => $this->input->get_post('agency_filter')
		);
		$export_link_params = '/property_me/properties_needs_verification?export=1&'.http_build_query($export_links_params_arr);
	?>

    
	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">
			<?php
		$form_attr = array(
			'id' => 'jform'
		);
		echo form_open($uri,$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-md-10 columns">
					<div class="row">
						<div class="col-mdd-3">
							<label>Agency</label>
							<select name="agency_filter" class="form-control">
								<option value="">--- Select ---</option>	
								<?php
								foreach( $agency_filter->result() as $agency ){ ?>
									<option value="<?php echo $agency->agency_id; ?>" <?php echo (  $agency->agency_id == $this->input->get_post('agency_filter') )?'selected="selected"':null; ?>>
										<?php echo $agency->agency_name; ?>
									</option>	
								<?php					
								}
								?>
							</select>
						</div>	
						<div class="col-mdd-3">
							<label>Note</label>
							<select name="note_filter" class="form-control">
								<option value="">--- Select ---</option>	
								<?php
								$query = "
								SELECT 
									DISTINCT(`note`)
								FROM `properties_needs_verification` AS pnv
								LEFT JOIN `property` AS `p` ON pnv.`property_id` = p.`property_id` AND pnv.`property_source`=1
								INNER JOIN `agency` AS `a` ON CASE WHEN pnv.`property_source`= 1 THEN p.`agency_id` = a.`agency_id` WHEN ( pnv.`property_source`= 2 OR pnv.`property_source`= 3 OR pnv.`property_source`= 7) THEN pnv.`agency_id` = a.`agency_id` END
								LEFT JOIN `agency_priority` as `aght` ON `a`.`agency_id` = `aght`.`agency_id`
								LEFT JOIN `api_property_data` AS `apd` ON p.`property_id` = apd.`crm_prop_id`
								LEFT JOIN `property_services` AS `ps` ON ( p.`property_id` = ps.`property_id` AND ps.`service` = 1 )
								WHERE `pnv`.`active` = 1
								AND `pnv`.`ignore_issue` = 0
								AND (`p`.`is_nlm` =0 OR `p`.`is_nlm` IS NULL) AND (`apd`.`api_prop_id` = '' OR `apd`.`api_prop_id` IS NULL) ORDER BY note
								";
								$p_verification = $this->db->query($query);
								foreach( $p_verification->result() as $p_row ){ ?>
									<option value="<?php echo $p_row->note; ?>" <?php echo (  $p_row->note == $this->input->get_post('note_filter') )?'selected="selected"':null; ?>>
										<?php echo $p_row->note; ?>
									</option>	
								<?php					
								}
								?>
							</select>
						</div>												
						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<input type="submit" name="search_submit" value="Search" class="btn">
						</div>
					</div>
				</div>

				<!-- DL ICONS START -->
                <?php 
                 ?>
			    <div class="col-md-2 columns">
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
			<div class="table-responsive" style="overflow-x: unset;">
            
				<table class="table table-hover main-table properties_need_verification_tbl">

					<thead>
						<tr>    
                            <th>Source</th>
                            <th>Property</th> 
							<th>Current service</th>
                            <th>Agency</th>       
                            <th>Note</th>			
							<th>Last Contact Info</th>				
							<th class="text-center">Action
								<div class="checkbox" style="margin:0;">
									<input name="chk_all" type="checkbox" id="check-all">
									<label for="check-all">&nbsp;</label>
								</div>
							</th>				
						</tr>
					</thead>

					<tbody>
					<?php
                    
					$i = 1;
					$chk_count = 1;
					foreach( $list->result() as $row ){ 

						$p_address = null;
						$crm_full_address = null;

						if(  $row->property_source == 1 ){ // crm

							// sales property
							$sales_txt = ( $row->is_sales == 1 )?'(Sales)':null;

							$crm_full_address = "{$row->p_address_1} {$row->p_address_2}, {$row->p_address_3} {$row->p_state} {$row->p_postcode} {$sales_txt}";

							$p_address = "
							<a href='{$this->config->item('crm_link')}/view_property_details.php?id={$row->property_id}' target='_blank'>
								{$crm_full_address}
							</a>";

						}else if(  $row->property_source == 2 ){	// PMe
											
							$pme_params = array(
								'agency_id' => $row->agency_id,
								'prop_id' => $row->property_id
							);
					
							$pme_prop_json = $this->pme_model->get_property($pme_params);				
							$pme_prop_json_dec = json_decode($pme_prop_json);		
							$p_address = $pme_prop_json_dec->AddressText;

						}else if(  $row->property_source == 3 ){	// palace
											
							$palace_params = array(
								'agency_id' => $row->agency_id,
								'palace_prop_id' => $row->property_id
							);							
					
							$palace_prop_json = $this->palace_model->get_property($palace_params);				
							$palace_prop_json_dec = json_decode($palace_prop_json);						

							$p_address = "{$palace_prop_json_dec->PropertyUnit} {$palace_prop_json_dec->PropertyAddress1} {$palace_prop_json_dec->PropertyAddress2} {$palace_prop_json_dec->PropertyAddress3} {$palace_prop_json_dec->PropertyFeatures->PropertyPostCode} {$palace_prop_json_dec->PropertyAddress4}";

						}else if(  $row->property_source == 7 ){	// ourtradie
											
							$ourtradie_params = array(
								'agency_id' => $row->agency_id,
								'ot_prop_id' => $row->property_id
							);							
							//print_r($ourtradie_params);

							$ot_prop_json = $this->ourtradie_model->getApiproperty($ourtradie_params);
			 
							//print_r($ot_prop_json);
							
							$p_address = $ot_prop_json['Address1'].",".$ot_prop_json['Address2']."&nbsp;".$ot_prop_json['State']."&nbsp;".$ot_prop_json['Postcode'];
							/*
							$pme_prop_json_dec = json_decode($pme_prop_json);		
							$p_address = $pme_prop_json_dec->AddressText;
							*/

						}
						
						// property that has was moved to another agency will return empty address and should not show on this list
						//if( $p_address != '' ){ 
						?>
						<tr class="tbl_list_tr" id="row<?php echo $row->pnv_id ?>">                        
                            <td>
                                <?php 
								//echo ( $row->property_source == 1 )?'CRM':'PMe'; 
								$property_source_txt = null;
								switch( $row->property_source ){

									case 1:
										$property_source_txt = 'CRM';
									break;
										
									case 2:
										$property_source_txt = 'PMe';
									break;

									case 3:
										$property_source_txt = 'Palace';
									break;

									case 7:
										$property_source_txt = 'Ourtradie';
									break;

								}
								echo $property_source_txt;
								?>
							</td>
                            <td>
                                <?php echo $p_address; ?>
							</td>
							<td>
								<?php
								if( $row->alarm_job_type_id > 0 ){ // display icons

									$job_icons_params = array(
										'service_type' => $row->alarm_job_type_id
									);
									echo $this->system_model->display_job_icons($job_icons_params);
									
								}								
								?>
							</td>
                            <td class="<?php echo ( $row->priority > 0 )?'j_bold':null; ?>">
								<a href="/agency/view_agency_details/<?php echo $row->agency_id; ?>" target="_blank">
                                    <?php echo $row->agency_name." ".( ( $row->priority > 0  )?' ('.$row->abbreviation.')':null ); ?>
                                </a>
							</td>
                            <td>
								<label class="edit_lbl<?php echo $row->pnv_id ?>">
									<?php echo $row->note; ?>
								</label>
								<input type="text" style="display: none;" class="form-control edit_note<?php echo $row->pnv_id ?> edit_hid<?php echo $row->pnv_id ?>" value="<?php echo $row->note; ?>" />
                            </td>	  
							<td>
								<textarea class="form-control last_contact_info"><?php echo $row->last_contact_info; ?></textarea>
							</td>       
							<td>
								<input type="hidden" class="pnv_row_id" value="<?= $row->pnv_id; ?>"/>
								<input type="hidden" class="pnv_id<?php echo $row->pnv_id ?>" value="<?php echo $row->pnv_id; ?>" />   
								<?php if( $row->property_source == 1 ){ // crm ?>
									<input type="hidden" class="crm_prop_id<?php echo $row->pnv_id ?>" value="<?php echo $row->property_id; ?>" />
								<?php										
								}else if( $row->property_source == 2 ){ // Pme ?>
									<input type="hidden" class="pme_prop_id<?php echo $row->pnv_id ?>" value="<?php echo $row->property_id; ?>" />
								<?php										
								}else if( $row->property_source == 3 ){ // palace ?>
									<input type="hidden" class="palace_prop_id<?php echo $row->pnv_id ?>" value="<?php echo $row->property_id; ?>" />
								<?php										
								}else if( $row->property_source == 7 ){ // ourtradie ?>
									<input type="hidden" class="ot_prop_id<?php echo $row->pnv_id ?>" value="<?php echo $row->property_id; ?>" />
								<?php										
								} 
								?>
								<input type="hidden" class="agency_id<?php echo $row->pnv_id ?>" value="<?php echo $row->agency_id; ?>" />
								<input type="hidden" class="agency_name<?php echo $row->pnv_id ?>" value="<?php echo $row->agency_name; ?>" />								
								<input type="hidden" class="crm_full_address<?php echo $row->pnv_id ?>" value="<?php echo $crm_full_address; ?>" />
								<input type="hidden" class="property_source<?php echo $row->pnv_id ?>" value="<?php echo $row->property_source; ?>" />
								<input type="hidden" class="is_nlm<?php echo $row->pnv_id ?>" value="<?php echo $row->is_nlm; ?>" />
								<input type="hidden" class="is_sales<?php echo $row->pnv_id ?>" value="<?php echo $row->is_sales; ?>" />

								<!-- <select class="form-control select_action float-left mr-2">

									<option value="">----</option>

									<option value="1">Delete Note</option>	
									<option value="2">Edit Note</option>	

									<?php
									if( $row->property_source == 1 ){ // CRM 

										if( $row->is_nlm != 1 ){ // not NLM
										?>
											<option value="3">NLM Property</option>
										<?php
										}
										
										if( $row->is_sales != 1 ){ ?>
											<option value="6">Mark as Sales</option>
										<?php
										}
											
									}else{ // PMe ?>

										<option value="4">Connect</option>
										<option value="5">Ignore Issue</option>

									<?php
									}
									?>	

								</select>

								<button type="button" class="btn btn_execute float-left mr-2">Execute</button> -->
								<div class="checkbox">
									<input class="chk_job" name="chk_job[]" type="checkbox" id="check-<?php echo $row->pnv_id ?>" data-jobid="<?php echo $row->pnv_id; ?>" value="<?php echo $row->pnv_id; ?>">
									<label for="check-<?php echo $row->pnv_id ?>">&nbsp;</label>
								</div>

								<!-- <div class="nlm_reason_div" style="display: none; width: 230px; margin-top: 5px">

								<select name="reason_they_left" class="form-control mb-2 reason_they_left">
									<option value="">---Select Reason---</option>       
									<?php
									// get leaving reason                                                
									$lr_sql = $this->db->query("
									SELECT *
									FROM `leaving_reason`
									WHERE `active` = 1
									AND `display_on` IN(2,4)
									ORDER BY `reason` ASC
									");   
									foreach( $lr_sql->result() as $lr_row ){ ?>
										<option value="<?php echo $lr_row->id; ?>"><?php echo $lr_row->reason; ?></option> 
									<?php
									}                                         
									?> 
									<option value="-1">Other</option> 
								</select>

								<textarea class="form-control addtextarea mb-2 other_reason" style="display: none;" name="other_reason" placeholder="Other Reason"></textarea> -->



								</div>

								<?php
								if( $row->propertyme_prop_id != '' ){ ?>
									<img src="/images/link-green.png" class="link_icon btn_link float-left" />
								<?php
								}
								?>
								
								
							</td>				                            
						</tr>
						<?php
						$i++;
                    }                    
					?>
					</tbody>

				</table>		
				<div id="mbm_box_show" class="text-right" style="float: right;">
					<button type="button" class="btn float-left mr-2" disabled>Execute</button>
				</div>
				<div id="mbm_box" class="text-right" style="float: right;">
					<select class="form-control select_action float-left mr-2">
						<option value="">----</option>
						<option value="1">Delete Note</option>	
						<option value="2">Edit Note</option>	
						<option value="3">NLM Property</option>
						<option value="6">Mark as Sales</option>
						<option style="display: none;" class="is_connect" value="4">Connect</option>
						<!-- <option value="5">Ignore Issue</option> -->
						<option value="7">Snooze</option>
					</select>

					<button type="button" class="btn btn_execute float-left mr-2">Execute</button>
					<div class="gbox" style="display: none;">
                        <input style="margin-top: 50px" name="snooze_reason" class="form-control"  id="snooze_reason" type="text" placeholder="Snooze reason*" >
                    </div>
					<div class="nlm_reason_div" style="display: none; width: 230px; margin-top: 50px">

						<select name="reason_they_left" class="form-control mb-2 reason_they_left">
							<option value="">---Select Reason---</option>       
							<?php
							// get leaving reason                                                
							$lr_sql = $this->db->query("
							SELECT *
							FROM `leaving_reason`
							WHERE `active` = 1
							AND `display_on` IN(2,4)
							ORDER BY `reason` ASC
							");   
							foreach( $lr_sql->result() as $lr_row ){ ?>
								<option value="<?php echo $lr_row->id; ?>"><?php echo $lr_row->reason; ?></option> 
							<?php
							}                                         
							?> 
							<option value="-1">Other</option> 
						</select>

						<textarea class="form-control addtextarea mb-2 other_reason" style="display: none;" name="other_reason" placeholder="Other Reason"></textarea>
				</div>
			</div>

			<nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
			<div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
			

		</div>
	</section>

</div>


<!-- Fancybox START -->

<!-- ABOUT TEXT -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
		This page shows Properties that need some attention to remove them from Bulk Patch Page
	</p>
	<pre>
		<code><?=$last_query?></code>
	</pre>

</div>
<!-- Fancybox END -->

<script>

function delete_note(pnv_id,pnv){

	if( pnv_id > 0 ){

		jQuery('#load-screen').show(); 
		jQuery.ajax({
			url: "/property_me/ajax_delete_pnv",
			type: 'POST',
			data: { 
				'pnv_id': pnv_id
			}
		}).done(function( ret ){	
			jQuery('#load-screen').hide(); 		
			// location.reload();
			// node.parents("tr:first").remove();	
			// document.getElementById("row"+pnv).remove();						

		});					
				
	}
}

function edit_note(select_action,pnv){	

	if( select_action == 2 ){
		$(".edit_lbl"+pnv).hide();
		$(".edit_hid"+pnv).show();
		$(".nlm_reason_div").hide();	
		$(".gbox").hide();			
	} else if(select_action == 3){
		$(".nlm_reason_div").show();	
		$(".edit_lbl"+pnv).show();
		$(".edit_hid"+pnv).hide();	
		$(".gbox").hide();
	} else if(select_action == 7){
		$(".gbox").show();	
		$(".nlm_reason_div").hide();	
		$(".edit_lbl"+pnv).show();
		$(".edit_hid"+pnv).hide();	
	} else{
		$(".edit_lbl"+pnv).show();
		$(".gbox").hide();
		$(".edit_hid"+pnv).hide();	
		$(".nlm_reason_div").hide();			          
	}

}


function update_note(pnv_id,note,pnv){

	if( pnv_id > 0 && note != '' ){

		jQuery('#load-screen').show(); 
		jQuery.ajax({
			url: "/property_me/ajax_update_note",
			type: 'POST',
			data: { 
				'pnv_id': pnv_id,
				'note': note
			}
		}).done(function( ret ){
			
			jQuery('#load-screen').hide(); 	

			edit_note('',pnv);		
			$(".edit_lbl"+pnv).text(note);
			$(".select_action"+pnv).val('');								

		});	

	}	

}


function mark_as_sales(crm_prop_id,pnv_id,pnv){

	if( crm_prop_id > 0 && pnv_id > 0 ){

		jQuery('#load-screen').show(); 
		jQuery.ajax({
			url: "/property_me/ajax_property_to_sales",
			type: 'POST',
			data: { 
				'property_id': crm_prop_id,
				'pnv_id': pnv_id
			}
		}).done(function( ret ){
			
			jQuery('#load-screen').hide(); 		
			// document.getElementById("row"+pnv).remove();								
			// node.parents("tr:first").remove();						

		});	

	}	

}

function snooze_property(crm_prop_id,pnv_id,pnv,snooze_reason){

// alert(crm_prop_id+" | "+pnv_id+" | "+pnv);
	if( crm_prop_id > 0 && pnv_id > 0 ){
		jQuery('#load-screen').show(); 
		jQuery.ajax({
			url: "/property_me/pnv_ajax_snooze",
			type: 'POST',
			data: { 
				'property_id': crm_prop_id,
				'pnv_id': pnv_id,
				'snooze_reason': snooze_reason
			}
		}).done(function( ret ){
			
			jQuery('#load-screen').hide(); 		
			// document.getElementById("row"+pnv).remove();								
			// node.parents("tr:first").remove();						

		});	
	}	

}


function nlm_property(crm_prop_id,pnv_id,crm_full_address,pnv,reason_they_left,other_reason){		
	
	// alert(crm_prop_id);
	if( crm_prop_id > 0 && pnv_id > 0 ){

		// confirm add PMe property on crm
		// swal({
		// 	title: "Are you sure?",
		// 	text: "You are about to NLM "+crm_full_address,
		// 	type: "warning",
		// 	showCancelButton: true,
		// 	confirmButtonClass: "btn-success",
		// 	confirmButtonText: "Yes, NLM",
		// 	cancelButtonText: "No, Cancel!",
		// 	cancelButtonClass: "btn-danger",
		// 	closeOnConfirm: true,
		// 	showLoaderOnConfirm: true,
		// 	closeOnCancel: true
		// },
		// function(isConfirm) {
			
		// 	if (isConfirm) { // yes	

				// single process but still needs to be wrapped on an array 
				// bec NLM function was updated to process multiple properties
				var crm_prop_id_arr = []; // clear
				crm_prop_id_arr.push(crm_prop_id);

				if( crm_prop_id_arr.length > 0 ){

					// get crm list
					jQuery('#load-screen').show(); 
					jQuery.ajax({
						url: "/property_me/nlm_property",
						type: 'POST',
						data: { 
							'property_id_arr': crm_prop_id_arr,
							'pnv_id': pnv_id,
							'reason_they_left': reason_they_left,
							'other_reason': other_reason
						},
						dataType: 'json'
					}).done(function( ret_json ){
						
						jQuery('#load-screen').hide(); 
						// cannot NLM bec has active jobs
						if( ret_json.cannot_nlm_prop_id_arr.length > 0 ){

							var nlm_msg_txt = '';
							jQuery.each(ret_json.cannot_nlm_address_arr, function( index, value ) {								
								nlm_msg_txt = value+' cannot be NLM bec it has active jobs';								
							});		

							swal({
								title: "Info!",
								text: nlm_msg_txt,
								type: "info",
								confirmButtonClass: "btn-primary"
							});																							

						}else{

							// document.getElementById("row"+pnv).remove();	
							// node.parents("tr:first").remove();

						}
																		
					});

				}																	
				
			// }
			
	// 	});

	}	

}

function ignore_issue(pnv_id,pnv){	

	if( pnv_id > 0 ){
		
		// swal({
		// 	title: "Are you sure?",
		// 	text: "You are about to ignore issue",
		// 	type: "warning",
		// 	showCancelButton: true,
		// 	confirmButtonClass: "btn-success",
		// 	confirmButtonText: "Yes, Ignore",
		// 	cancelButtonText: "No, Cancel!",
		// 	cancelButtonClass: "btn-danger",
		// 	closeOnConfirm: true,
		// 	showLoaderOnConfirm: true,
		// 	closeOnCancel: true
		// },
		// function(isConfirm) {
			
		// 	if (isConfirm) { // yes		
				
				jQuery('#load-screen').show(); 
				jQuery.ajax({
					url: "/property_me/ajax_ignore_issue",
					type: 'POST',
					data: { 
						'pnv_id': pnv_id
					}
				}).done(function( ret ){
					
					jQuery('#load-screen').hide(); 					
					//location.reload();	
					// document.getElementById("row"+pnv).remove();
					// node.parents("tr:first").remove();						

				});								

				
		// 	}
			
		// });

	}

}

function connect_pme(pnv_id,pme_prop_id,agency_id,agency_name,pnv){

	jQuery('#load-screen').show(); 
	jQuery.ajax({
		url: "/property_me/get_pme_property",
		type: 'POST',
		data: { 
			'agency_id': agency_id,
			'pme_prop_id' : pme_prop_id
		}
	}).done(function( pme_api_ret ){
							
		jQuery('#load-screen').hide(); 
		json = JSON.parse(pme_api_ret);		

		var pme_prop_arr = [];

		var pme_addr_unit = json.Address.Unit;
		var pme_addr_number = json.Address.Number;
		var pme_addr_street = json.Address.Street;
		var pme_addr_suburb = json.Address.Suburb;
		var pme_addr_state = json.Address.State;
		var pme_addr_postalcode = json.Address.PostalCode;
		
		var json_data = { 						
			'pme_addr_unit': pme_addr_unit,
			'pme_addr_number': pme_addr_number,
			'pme_addr_street': pme_addr_street,
			'pme_addr_suburb': pme_addr_suburb,
			'pme_addr_state': pme_addr_state,
			'pme_addr_postalcode': pme_addr_postalcode,
		}					

		var json_str = JSON.stringify(json_data);
		pme_prop_arr.push(json_str);

		var disable_add = 1; // disable the add property function, only trigger the property exist check

		if( pme_prop_arr.length > 0 ){

			// ajax add property
			jQuery('#load-screen').show(); 
			jQuery.ajax({
				url: "/property_me/bulk_connect_add_property",
				type: 'POST',
				data: { 
					'agency_id': agency_id,
					'pme_prop_arr': pme_prop_arr,
					'disable_add': disable_add
				},
				dataType: 'json'
			}).done(function( ret ){

				jQuery('#load-screen').hide();

				if( ret.length > 0 ){

					// swal html markup
					dup_html = ''+
					'<ul>';
					dup_html += ''+
						'<li>'+
							'<a href="/property_me/property/'+ret[0].dup_property_id+'/'+ret[0].dup_agency_id+'" target="_blank">'+
								ret[0].dup_property_address+										
							'</a>'+								
						'</li>';
					dup_html +='</ul><br />'+
					'This property already exists with <a href="/agency/view_agency_details/'+ret[0].dup_agency_id+'">'+ret[0].dup_agency_name+'</a><br /><br />'+													
					'Would you like to connect it and move to <a href="/agency/view_agency_details/'+agency_id+'">'+agency_name+'</a>?'+
					'</div>';

					// swal confirm dialog
					swal({
						html:true,
						title: "Warning!",
						text: dup_html,
						type: "warning",							
						customClass: 'swal-dup_prop',

						showCancelButton: true,
						confirmButtonClass: "btn-success",
						confirmButtonText: "Yes, Connect it!",
						cancelButtonText: "No, Cancel!",
						cancelButtonClass: "btn-danger",
						closeOnConfirm: true,
						showLoaderOnConfirm: true,
						closeOnCancel: true								
					},						
						function(isConfirm) {
							if (isConfirm) {		
										
								var crm_prop_id = ret[0].dup_property_id;
								// connect them
								$('#load-screen').show(); 
								$.ajax({
									url: "/property_me/ajax_pnv_connect_pme_prop",
									type: 'POST',
									data: { 
										'agency_id': agency_id,
										'from_agency_name': ret[0].dup_agency_name,
										'to_agency_name': agency_name,
										'crm_prop_id' : crm_prop_id,
										'pme_prop_id' : pme_prop_id,
										'pnv_id' : pnv_id
									}
								}).done(function( pnv_connect_ret ){
															
									$('#load-screen').hide(); 
									pnv_connect_json = JSON.parse(pnv_connect_ret);
									
									if (pnv_connect_json.updateStat === true) {

										// row.remove();
										// document.getElementById("row"+pnv).remove();
										
										swal({
											title: "Success!",
											text: "The property is now linked",
											type: "success",
											confirmButtonClass: "btn-success",
											showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
											timer: <?php echo $this->config->item('timer') ?>
										});


									}else {
										swal({
											title: "Error!",
											text: "Something went wrong, contact dev.",
											type: "error",
											confirmButtonClass: "btn-danger"
										});
									}														

								});									
														

							}
						}
					
					);

				}

			});

		}				
												
	});

}


function connect_palace(pnv_id,palace_prop_id,agency_id,agency_name,pnv){

	jQuery('#load-screen').show(); 
	jQuery.ajax({
		url: "/property_me/get_palace_property",
		type: 'POST',
		data: { 
			'agency_id': agency_id,
			'palace_prop_id' : palace_prop_id
		}
	}).done(function( pme_api_ret ){
							
		jQuery('#load-screen').hide(); 
		json = JSON.parse(pme_api_ret);		

		var pme_prop_arr = [];

		var pme_addr_unit = json.PropertyUnit;
		var pme_addr_number = json.PropertyAddress1;
		var pme_addr_street = json.PropertyAddress2;
		var pme_addr_suburb = json.PropertyAddress3;
		var pme_addr_state = json.PropertyAddress4;
		var pme_addr_postalcode = json.PropertyFeatures.PropertyPostCode;
		
		var json_data = { 						
			'pme_addr_unit': pme_addr_unit,
			'pme_addr_number': pme_addr_number,
			'pme_addr_street': pme_addr_street,
			'pme_addr_suburb': pme_addr_suburb,
			'pme_addr_state': pme_addr_state,
			'pme_addr_postalcode': pme_addr_postalcode,
		}					

		var json_str = JSON.stringify(json_data);
		pme_prop_arr.push(json_str);

		var disable_add = 1; // disable the add property function, only trigger the property exist check
		
		//console.log(pme_prop_arr);		
		
		if( pme_prop_arr.length > 0 ){

			// ajax add property
			jQuery('#load-screen').show(); 
			jQuery.ajax({
				url: "/palace/bulk_connect_add_property",
				type: 'POST',
				data: { 
					'agency_id': agency_id,
					'pme_prop_arr': pme_prop_arr,
					'disable_add': disable_add
				},
				dataType: 'json'
			}).done(function( ret ){

				jQuery('#load-screen').hide();

				if( ret.length > 0 ){

					// swal html markup
					dup_html = ''+
					'<ul>';
					dup_html += ''+
						'<li>'+
							'<a href="/palace/property/'+ret[0].dup_property_id+'/'+ret[0].dup_agency_id+'" target="_blank">'+
								ret[0].dup_property_address+										
							'</a>'+								
						'</li>';
					dup_html +='</ul><br />'+
					'This property already exists with <a href="/agency/view_agency_details/'+ret[0].dup_agency_id+'">'+ret[0].dup_agency_name+'</a><br /><br />'+													
					'Would you like to connect it and move to <a href="/agency/view_agency_details/'+agency_id+'">'+agency_name+'</a>?'+
					'</div>';

					// swal confirm dialog
					swal({
						html:true,
						title: "Warning!",
						text: dup_html,
						type: "warning",							
						customClass: 'swal-dup_prop',

						showCancelButton: true,
						confirmButtonClass: "btn-success",
						confirmButtonText: "Yes, Connect it!",
						cancelButtonText: "No, Cancel!",
						cancelButtonClass: "btn-danger",
						closeOnConfirm: true,
						showLoaderOnConfirm: true,
						closeOnCancel: true								
					},						
						function(isConfirm) {
							if (isConfirm) {		
										
								var crm_prop_id = ret[0].dup_property_id;
								// connect them
								$('#load-screen').show(); 
								$.ajax({
									url: "/property_me/ajax_pnv_connect_palace_prop",
									type: 'POST',
									data: { 
										'agency_id': agency_id,
										'from_agency_name': ret[0].dup_agency_name,
										'to_agency_name': agency_name,
										'crm_prop_id' : crm_prop_id,
										'palace_prop_id' : palace_prop_id,
										'pnv_id' : pnv_id
									}
								}).done(function( pnv_connect_ret ){
															
									$('#load-screen').hide(); 
									pnv_connect_json = JSON.parse(pnv_connect_ret);
									
									if (pnv_connect_json.updateStat === true) {

										// row.remove();
										// document.getElementById("row"+pnv).remove();
										
										swal({
											title: "Success!",
											text: "The property is now linked",
											type: "success",
											confirmButtonClass: "btn-success",
											showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
											timer: <?php echo $this->config->item('timer') ?>
										});


									}else {
										swal({
											title: "Error!",
											text: "Something went wrong, contact dev.",
											type: "error",
											confirmButtonClass: "btn-danger"
										});
									}														

								});									
														

							}
						}
					
					);

				}

			});

		}	
					
												
	});

}

function connect_ourtradie(pnv_id,ot_prop_id,agency_id,agency_name,pnv){

	//alert("TESTING");
	//return false;

	jQuery('#load-screen').show(); 
	jQuery.ajax({
		//console.log("TEST");
		//return false;

		url: "/ourtradie/get_ourtradie_property",
		type: 'POST',
		data: { 
			'agency_id': agency_id,
			'ot_prop_id' : ot_prop_id
		}
	}).done(function( ot_api_ret ){
		//console.log(ot_api_ret);
		//return false;

		jQuery('#load-screen').hide(); 
		json = JSON.parse(ot_api_ret);
		//json = JSON.stringify(ot_api_ret);		

		//console.log(json);
		//return false;

		var ot_prop_arr = [];

		var ot_addr_address1 = json.Address1;
		var ot_addr_address2 = json.Address2;
		var ot_addr_suburb   = json.Suburb;
		var ot_addr_state    = json.State;
		var ot_addr_postalcode = json.Postcode;

		//alert(ot_addr_address1);
		//return false;
		
		var json_data = { 						
			'ot_full_address': ot_addr_address1 + ot_addr_address2,
			'ot_addr_suburb':   ot_addr_suburb,
			'ot_addr_state':    ot_addr_state,
			'ot_addr_postalcode': ot_addr_postalcode,
		}					

		var json_str = JSON.stringify(json_data);
		ot_prop_arr.push(json_str);

		var disable_add = 1; // disable the add property function, only trigger the property exist check
								
		if( ot_prop_arr.length > 0 ){

			// ajax add property
			jQuery('#load-screen').show(); 
			jQuery.ajax({
				url: "/ourtradie/bulk_connect_add_property",
				type: 'POST',
				data: { 
					'agency_id': agency_id,
					'ot_prop_arr': ot_prop_arr,
					'disable_add': disable_add
				},
				dataType: 'json'
			}).done(function( ret ){

				//console.log(ret);
				//return false;

				jQuery('#load-screen').hide();

				if( ret.length > 0 ){

					// swal html markup
					dup_html = ''+
					'<ul>';
					dup_html += ''+
						'<li>'+
							'<a href="/ourtradie/property/'+ret[0].dup_property_id+'/'+ret[0].dup_agency_id+'" target="_blank">'+
								ret[0].dup_property_address+										
							'</a>'+								
						'</li>';
					dup_html +='</ul><br />'+
					'This property already exists with <a href="<?php echo $this->config->item('crm_link'); ?>/view_agency_details.php?id='+ret[0].dup_agency_id+'">'+ret[0].dup_agency_name+'</a><br /><br />'+													
					'Would you like to connect it and move to <a href="<?php echo $this->config->item('crm_link'); ?>/view_agency_details.php?id='+agency_id+'">'+agency_name+'</a>?'+
					'</div>';

					// swal confirm dialog
					swal({
						html:true,
						title: "Warning!",
						text: dup_html,
						type: "warning",							
						customClass: 'swal-dup_prop',

						showCancelButton: true,
						confirmButtonClass: "btn-success",
						confirmButtonText: "Yes, Connect it!",
						cancelButtonText: "No, Cancel!",
						cancelButtonClass: "btn-danger",
						closeOnConfirm: true,
						showLoaderOnConfirm: true,
						closeOnCancel: true								
					},						
						function(isConfirm) {
							if (isConfirm) {		
										
								var crm_prop_id = ret[0].dup_property_id;
								// connect them
								$('#load-screen').show(); 
								$.ajax({
									url: "/ourtradie/ajax_pnv_connect_pme_prop",
									type: 'POST',
									data: { 
										'agency_id': agency_id,
										'from_agency_name': ret[0].dup_agency_name,
										'to_agency_name': agency_name,
										'crm_prop_id' : crm_prop_id,
										'ot_prop_id' : ot_prop_id,
										'pnv_id' : pnv_id
									}
								}).done(function( pnv_connect_ret ){
									//console.log(pnv_connect_ret);
									//return false;

									$('#load-screen').hide(); 
									pnv_connect_json = JSON.parse(pnv_connect_ret);
									
									if (pnv_connect_json.updateStat === true) {

										// row.remove();
										// document.getElementById("row"+pnv).remove();
										
										swal({
											title: "Success!",
											text: "The property is now linked",
											type: "success",
											confirmButtonClass: "btn-success",
											showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
											timer: <?php echo $this->config->item('timer') ?>
										});


									}else {
										swal({
											title: "Error!",
											text: "Something went wrong, contact dev.",
											type: "error",
											confirmButtonClass: "btn-danger"
										});
									}														

								});									
														

							}
						}
					
					);

				}

			});

		}				
												
	});

}

jQuery(document).ready(function(){

	$('#check-all').on('change',function(){
		var obj = $(this);
		var isChecked = obj.is(':checked');
		var divbutton = $('#mbm_box');

		if(isChecked){
			divbutton.show();
			$('#mbm_box_show').hide();
			$('.chk_job').prop('checked',true);
			$("tr.tbl_list_tr").addClass("yello_mark");
		}else{
			divbutton.hide();
			$('#mbm_box_show').show();
			$('.chk_job').prop('checked',false);
			$("tr.tbl_list_tr").removeClass("yello_mark");
		}
	})

	$('.chk_job').on('change',function(){
		var obj = $(this);
		var isLength = $('.chk_job:checked').length;
		var checkVal = $('.chk_job:checked');
		var divbutton = $('#mbm_box');

		for (let i = 0; i < checkVal.length; i++) {
		const checkbox = checkVal[i];
			var property_source  = $('.property_source'+checkbox.value).val();
			if (property_source != 1) {
				$('.is_connect').show();
			} else {
				$('.is_connect').hide();
			}
		}

		if(obj.is(':checked')){
			divbutton.show();
			$('#mbm_box_show').hide();
			obj.parents('.tbl_list_tr').addClass('yello_mark');
		}else{
			obj.parents('.tbl_list_tr').removeClass('yello_mark');

			if(isLength<=0){
				divbutton.hide();
				$('#mbm_box_show').show();
			}

		}
	})

	// select action dropdown
	jQuery(".select_action").change(function(){

		var node = jQuery(this);
		var select_action = node.val();
		var isLength = $('.chk_job:checked').length;
		var pnv = $('.chk_job:checked');

		var values = [];
		for (var i = 0; i < pnv.length; i++) {
			pnv[i].checked = true;
			values.push(pnv[i].value);
		}

		for (let i = 0; i < values.length; i++) {
			var pnv = values[i];
			edit_note(select_action,pnv);
		}

	});

	//show textarea other reason
	jQuery(".reason_they_left").change(function(){

	var node = jQuery(this);
	var select_action = node.val();

	show_other(select_action);

	});

	function show_other(select_action){	
		if( select_action == -1 ){
			$(".other_reason").show();
		}else{
			$(".other_reason").hide();
		}  
	}


	// execute selected action
	jQuery(".btn_execute").click(function(){

		// alert("TEST");
		var pnv = $('.chk_job:checked');
		var select_action = $('.select_action').val();


		delete_notes = 1;
		var values = [];
		for (var i = 0; i < pnv.length; i++) {
			pnv[i].checked = true;
			values.push(pnv[i].value);
		}

		for (let i = 0; i < values.length; i++) {
			var pnv = values[i];

		var pnv_id = $(".pnv_id"+pnv).val();		
		var crm_prop_id = $(".crm_prop_id"+pnv).val();
		var crm_full_address = $(".crm_full_address"+pnv).val();

		var pme_prop_id = $('.pme_prop_id'+pnv).val(); // PMe Prop ID
		var palace_prop_id = $('.palace_prop_id'+pnv).val(); // palace Prop ID
		var ot_prop_id = $('.ot_prop_id'+pnv).val(); // ourtradie Prop ID
		var agency_id = $('.agency_id'+pnv).val();
		var agency_name = $('.agency_name'+pnv).val();
		var property_source = $('.property_source'+pnv).val();

		var note = $('.edit_note'+pnv).val();


		var reason_they_left = $('.reason_they_left').val();
		var other_reason = $('.other_reason').val();

		var is_nlm = $('.is_nlm'+pnv).val();
		var is_sales = $('.is_sales'+pnv).val();

		// validation
		if (select_action == 3) {
			if( reason_they_left == '' ){
				swal('','Reason They Left is required');
				return false;
			}else{
				if( reason_they_left == -1 && other_reason == '' ){
					swal('','Other Reason\ is required');
					return false;
				}
			} 
		}

		var snooze_reason = $('#snooze_reason').val();

		if (select_action == 7) {
			if( snooze_reason == '' ){
				swal('','Snooze reason is required');
				return false;
			}
		}


		if( select_action > 0 ){

			if( select_action == 1 ){ // Delete Note
				
				delete_note(pnv_id,pnv);
		
			}else if( select_action == 2 ){ // Edit note

				update_note(pnv_id,note,pnv);

			}else if( select_action == 3 ){ // NLM property

				if (is_nlm != 1) {
					nlm_property(crm_prop_id,pnv_id,crm_full_address,pnv,reason_they_left,other_reason);
				} else {
					swal({
						title: "Info!",
						text: 'Property '+crm_full_address+' is already No Longer Managed!',
						type: "info",
						confirmButtonClass: "btn-primary"
					});
					return false;
				}
				

			}else if( select_action == 4 ){ // Connect	
				if( property_source == 2 ){ // PME			
					//alert("TEST");
					//return false;		
					connect_pme(pnv_id,pme_prop_id,agency_id,agency_name,pnv);
				}else if( property_source == 3 ){ // Palace					
					connect_palace(pnv_id,palace_prop_id,agency_id,agency_name,pnv);
				}else if( property_source == 7 ){ // Ourtradie	
					//alert("TEST");
					//return false;				
					connect_ourtradie(pnv_id,ot_prop_id,agency_id,agency_name,pnv);
				}	

			}else if( select_action == 5 ){ // Ignore Issue

				ignore_issue(pnv_id,pnv);

			}else if( select_action == 6 ){ // mark property as sales
				if (is_sales != 1) {
					mark_as_sales(crm_prop_id,pnv_id,pnv);
				} else {
					swal({
						title: "Info!",
						text: 'Property '+crm_prop_id+' is already Sales Property!',
						type: "info",
						confirmButtonClass: "btn-primary"
					});
					return false;
				}

			} else if( select_action == 7 ){ // Snooze Property

				snooze_property(crm_prop_id,pnv_id,pnv,snooze_reason);

			}

		}
		else{

			swal({
				title: "Info!",
				text: 'Please select action to execute',
				type: "info",
				confirmButtonClass: "btn-primary"
			});
			
		}

		}

		if (select_action != 4) {
			swal({
			title:"Success!",
			type: "success",
			showCancelButton: false,
			confirmButtonText: "OK",
			closeOnConfirm: false,
			showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
			timer: <?php echo $this->config->item('timer') ?>

		});
		setTimeout(function(){ window.location='/property_me/properties_needs_verification'; }, <?php echo $this->config->item('timer') ?>);
		} else {
			location.reload();	
		}	

	});

	
	jQuery(".last_contact_info").change(function(){

		var dom = jQuery(this);
		var parent_tr = dom.closest("tr");

		var pnv_id = parent_tr.find(".pnv_row_id").val();
		var last_contact_info = parent_tr.find(".last_contact_info").val();

		console.log(pnv_id);
		console.log(last_contact_info);

		jQuery('#load-screen').show(); 
		jQuery.ajax({
			url: "/property_me/save_pnv_last_contact_info",
			type: 'POST',
			data: { 
				'pnv_id': pnv_id,
				'last_contact_info': last_contact_info
			}
		}).done(function( ret ){
			
			jQuery('#load-screen').hide();

		});

	});


});
</script>

