
<link rel="stylesheet" href="/inc/css/lib/datatables-net/datatables.min.css">
<link rel="stylesheet" href="/inc/css/separate/vendor/datatables-net.min.css">
<script src="/inc/js/lib/datatables-net/datatables.min.js"></script>
<style type="text/css">
.dataTables_paginate {
  display: inline-block;
}

.dataTables_paginate a {
  color: black;
  float: left;
  padding: 8px 16px;
  text-decoration: none;
  transition: background-color .3s;
  border: 1px solid #ddd;
}

.dataTables_paginate a.active {
  background-color: #4CAF50;
  color: white;
  border: 1px solid #4CAF50;
}
#pmeProp {
  /*table-layout: fixed;*/
  width: 100% !important;
}
.dataTables_filter {
   display: none;
}

#pmeProp tr th:nth-child(1){
       width: 35%;
}
#pmeProp tr th:nth-child(3){
       width: 15%;
}
#pmeProp tr th:nth-child(4){
       width: 15%;
}
#pmeProp tr th:nth-child(7){
       width: 3%;
}
.fa-lg:hover{
  color: #07da07;
}

.company_logo {
    margin: 30px 0;
	height: 70px;
}

.pme_main_div {
    border: solid 2px #044d66;
    background: #f5f8fa;	
}

.pme_logo {
    margin-left: 9px;
}
.api_address_tenant_det_btn{
	cursor: pointer;
}
</style>
<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => $title,
			'status' => 'active',
			'link' => $uri
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>
	<section>
		<div class="body-typical-body">
				<div class="row">
				  <div class="col-sm-12">
				  <img src="/images/logo_login.png" class="company_logo sats_logo" />
				  	<?php 
				  	if (isset($propDet)) {
				  		$prop_full_add = "{$propDet->p_address_1} {$propDet->p_address_2} {$propDet->p_address_3} {$propDet->p_state} {$propDet->p_postcode}";
			  		 ?>
			  			<div class="row">
							<div class="col-lg-8">
					  			<div class="row">
									<div class="col-lg-12">
										<fieldset class="form-group">
											<label class="form-label semibold" for="add1">Address Text</label>
											<input type="hidden" id="add0" value="<?php echo $propDet->property_id?>">
											<div class="input-group">
												<input type="text" class="form-control crm_full_address" id="add1" placeholder="Address Text" value="<?php echo $prop_full_add?>">
											      <button class="btn btn-primary" id="addSearch" type="button" style="display: none;"><i class="fa fa-search"></i>
											      </button>
										    </div>
										</fieldset>
									</div>
								</div>
					  			<div class="row">
									<div class="col-lg-2">
										<fieldset class="form-group">
											<label class="form-label semibold" for="add2">No.</label>
											<input type="text" class="form-control" id="add2" placeholder="No." value="<?php echo $propDet->p_address_1?>">
										</fieldset>
									</div>
									<div class="col-lg-4">
										<fieldset class="form-group">
											<label class="form-label semibold" for="add3">Street</label>
											<input type="text" class="form-control" id="add3" placeholder="Street" value="<?php echo $propDet->p_address_2?>">
										</fieldset>
									</div>
									<div class="col-lg-2">
										<fieldset class="form-group">
											<label class="form-label semibold" for="add4">Suburb</label>
											<input type="text" class="form-control" id="add4" placeholder="Suburb" value="<?php echo $propDet->p_address_3?>">
										</fieldset>
									</div>
									<div class="col-lg-2">
										<fieldset class="form-group">
											<label class="form-label semibold" for="add5">
												<?php
												if ($this->config->item('country') == 1) {
													echo "State";
												}else {
													echo "Region";
												}
												?>
											</label>
											<input type="text" class="form-control" id="add5" placeholder="State" value="<?php echo $propDet->p_state?>">
										</fieldset>
									</div>
									<div class="col-lg-2">
										<fieldset class="form-group">
											<label class="form-label semibold" for="add6">Postcode</label>
											<input type="text" class="form-control" id="add6" placeholder="Postcode" value="<?php echo $propDet->p_postcode?>">
										</fieldset>
									</div>
									
								</div>
							</div>
							<div class="col-lg-4">
								<fieldset class="form-group">
									<label class="form-label semibold" for="add7">Property Notes</label>
									<textarea class="form-control" placeholder="Notes" style="height: 120px;"><?php echo $propDet->comments?></textarea>
								</fieldset>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-12">
								<small class="text-muted">*This CRM property is not yet linked to any PropertyTree property, kindly link to a PropertyTree property below.</small>
							</div>
						</div>
			  		<?php
				  	} else { ?>
			  			<div class="row">
							<div class="col-lg-12">
								No property is associated with the property id <?php echo $this->uri->segment(3)?>
							</div>
						</div>
				  	<?php
				  	}
				  	?>

					<?php 
					if (isset($prop_tree_list)) { 
					?>
					    <div id="pmeDetId" class="pme_main_div" style="display: <?php echo isset($propDet) ? "" : "none"?>">

                            <div clas="row company_logo_div" style="text-align: left; margin: 17px;">									
                                <div class="logo-container">
									<img src="/images/third_party/propertytree.png" class="company_logo pme_logo" />
									<!--
                                    <a href="https://www.mrisoftware.com/au/" id="logo" class="header-svg-logo">                                                                                
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 12500 1000"><style type="text/css">.st0{fill:#5C5D5F;} .st1{fill:#BBD437;} .st2{fill:#044D66;}</style><path d="M59.6 892.3c0 7.1-1.9 12.8-5.6 17.1-3.8 4.4-9.3 7.2-16.7 8.4l24.5 35.5H47.9l-22.2-35h-15v35H0v-85.6h29.8c9.6 0 17 2.3 22.1 6.9 5.1 4.8 7.7 10.7 7.7 17.7zM29 911c6.4 0 11.3-1.6 14.6-4.7 3.3-3.1 5-7.3 5-12.4 0-5.1-1.6-9.2-4.8-12.2-3.2-3-7.9-4.6-13.9-4.6h-19V911H29zM91.4 867.8H144v9.2h-41.9v28.7h39.4v9.1h-39.4V944H144v9.4H91.4v-85.6zM195.3 867.8h13.1l28.7 85.6h-11.2l-7.3-21.8h-34l-7.3 21.8H166l29.3-85.6zm-7.9 55.4h28.4L201.7 881l-14.3 42.2zM273.3 944h38.1v9.4h-48.8v-85.6h10.7V944zM385.6 867.8h52.6v9.2h-41.9v28.7h39.5v9.1h-39.5V944h41.9v9.4h-52.6v-85.6zM516 888.7c-1.5-8.3-7.7-12.5-18.4-12.5-5.7 0-10.1 1.2-13.2 3.7-3.1 2.5-4.6 5.5-4.6 9s1 6.1 2.9 8c1.9 1.8 5 3.4 9.3 4.8l17.8 6.1c6.1 2.1 10.9 4.9 14.5 8.6 3.6 3.6 5.4 8.7 5.4 15.3s-2.8 12.1-8.4 16.6c-5.6 4.5-13.2 6.7-22.6 6.7-9.4 0-17.2-2.4-23.2-7.1-6-4.8-9.1-11.4-9.3-20.1h10.5c0 5.6 2 9.9 5.9 12.9 3.9 3 9 4.5 15.2 4.5s11.1-1.4 14.8-4.1c3.6-2.8 5.4-6 5.4-9.8 0-3.8-1.1-6.7-3.3-8.9-2.2-2.2-5.5-4-9.9-5.4l-15.1-5.6c-7.1-2.4-12.4-5.2-16.1-8.4-3.6-3.3-5.4-8-5.4-14.3 0-6.3 2.5-11.6 7.6-15.8 5.1-4.2 12.2-6.3 21.4-6.3s16.2 2 21 6.1c4.9 4 7.6 9.5 8.4 16.3H516zM545.2 867.8h66.5v9.2h-27.9v76.3h-10.7V877h-27.9v-9.2zM650.9 867.8H664l28.7 85.6h-11.2l-7.3-21.8h-34l-7.3 21.8h-11.3l29.3-85.6zm-7.9 55.4h28.4L657.3 881 643 923.2zM699.7 867.8h66.5v9.2h-27.9v76.3h-10.7V877h-27.9v-9.2zM788.8 867.8h52.6v9.2h-41.9v28.7H839v9.1h-39.4V944h41.9v9.4h-52.6v-85.6zM970.6 888.7c-1.5-8.3-7.7-12.5-18.4-12.5-5.7 0-10.1 1.2-13.2 3.7-3.1 2.5-4.6 5.5-4.6 9s1 6.1 2.9 8c1.9 1.8 5 3.4 9.3 4.8l17.8 6.1c6.1 2.1 10.9 4.9 14.5 8.6 3.6 3.6 5.4 8.7 5.4 15.3s-2.8 12.1-8.4 16.6c-5.6 4.5-13.2 6.7-22.6 6.7-9.4 0-17.2-2.4-23.2-7.1-6-4.8-9.1-11.4-9.3-20.1h10.5c0 5.6 2 9.9 5.9 12.9 3.9 3 9 4.5 15.2 4.5s11.1-1.4 14.8-4.1c3.6-2.8 5.4-6 5.4-9.8 0-3.8-1.1-6.7-3.3-8.9-2.2-2.2-5.5-4-9.9-5.4l-15.1-5.6c-7.1-2.4-12.4-5.2-16.1-8.4-3.6-3.3-5.4-8-5.4-14.3 0-6.3 2.5-11.6 7.6-15.8 5.1-4.2 12.2-6.3 21.4-6.3s16.2 2 21 6.1c4.9 4 7.6 9.5 8.4 16.3h-10.6zM1042.9 866.3c11.1 0 20 3.8 26.9 11.4 6.8 7.6 10.3 18.6 10.3 33.1s-3.4 25.4-10.2 32.9c-6.8 7.5-15.8 11.2-26.9 11.2s-20.1-3.8-26.9-11.4c-6.8-7.6-10.1-18.5-10.1-32.9 0-14.4 3.4-25.4 10.1-33 6.7-7.5 15.7-11.3 26.8-11.3zm0 9.7c-8 0-14.3 3-18.9 9-4.6 6-6.9 14.6-6.9 25.8 0 11.2 2.3 19.8 6.9 25.6 4.6 5.8 10.9 8.7 18.9 8.7 8 0 14.3-2.9 19-8.7 4.7-5.8 7-14.3 7-25.6 0-11.2-2.3-19.9-7-25.8-4.7-6-11-9-19-9zM1106 867.8h52v9.2h-41.2v28.4h38.4v9.3h-38.4v38.6H1106v-85.5zM1175.3 867.8h66.5v9.2h-27.9v76.3h-10.7V877h-27.9v-9.2zM1267.4 867.8l20.8 71.1 21.1-62.3h7.2l20.9 62.3 23.9-71.1h11.8l-30.2 85.6h-11.3l-18.6-54.8-19.3 54.8h-11.3l-27.1-85.6h12.1zM1411.8 867.8h13.1l28.7 85.6h-11.2l-7.3-21.8h-34l-7.3 21.8h-11.3l29.3-85.6zm-7.9 55.4h28.4l-14.1-42.2-14.3 42.2zM1535.1 892.3c0 7.1-1.9 12.8-5.6 17.1-3.8 4.4-9.3 7.2-16.7 8.4l24.5 35.5h-13.9l-22.2-35h-15v35h-10.7v-85.6h29.8c9.6 0 17 2.3 22.1 6.9 5.2 4.8 7.7 10.7 7.7 17.7zm-30.6 18.7c6.4 0 11.3-1.6 14.6-4.7 3.3-3.1 5-7.3 5-12.4 0-5.1-1.6-9.2-4.8-12.2-3.2-3-7.9-4.6-13.9-4.6h-19V911h18.1zM1561.5 867.8h52.6v9.2h-41.9v28.7h39.4v9.1h-39.4V944h41.9v9.4h-52.6v-85.6z" class="st0"></path><path d="M1320.5 157.5c-93.9 0-171.5 39.6-221.4 103.8l-.3-.5c-16.9-21.5-36.9-40.2-59.6-55.6v542.3h121V441.3c0-97.5 63-170.2 160.4-170.2 57.1 0 102.2 25.1 129.9 65.4V190c-36.9-20.8-80.7-32.5-130-32.5z" class="st1"></path><path d="M1005.4 445.6v301.9H883.7V441.3c0-97.5-63-170.3-160.4-170.3s-160.4 72.8-160.4 170.3v306.3H442.5V441.3c0-97.5-63-170.3-160.4-170.3-57.6 0-103.1 22.7-130.7 63.7-19.6 28.5-30.4 64.6-30.4 105v307.8H0V203.8c22.7 15.4 39.7 34.1 56.6 55.6l2 2.5c50-64.2 128.9-105.8 222.8-105.8 49.3 0 93 12.7 129.9 33.6 38.1 21.3 68.8 52.4 90.7 90.7 0 0 .5.8.7 1.2.2-.4.4-.8.6-1.2 43.1-75.1 120.1-122.7 220-122.7 166.1-.2 282.1 125 282.1 287.9z" class="st2"></path><circle cx="1550" cy="93.4" r="64.2" class="st1"></circle><path d="M1486 340.9v-140c22.1 15 127.3 81 127.3 188.6l.3 45.6-.7 312c-31.6-15-51.1-33.2-67.6-54.2 0 0-.4-.9-.6-.7-2.2-2.9-4.4-5.8-6.6-8.8-11.1-15.6-20.6-32.5-28.4-50.6-1.6-3.6-3-7.3-4.4-11-1.4-3.6-2.7-7.3-3.9-11-3.7-11.1-6.8-22.6-9.2-34.4-4.2-20.4-6.4-41.6-6.4-63.7l.2-171.8z" class="st2"></path></svg>
                                    </a>
									-->
                                </div>
                            </div>

						    <div class="container-fluid">
								<div class="row">
									<div class="col-sm-4">
										<label class="form-label semibold">Address Search:</label>
										<input type="text" class="form-control" id="datatable_pme_search" placeholder="Search Address" />
									</div>
									<div class="col-sm-4">
										<label class="form-label semibold">&nbsp;</label>
										<?php
											$t_params = array(
												'property_id' => $propDet->property_id,
												'property_source' => 1
											);
											if( $this->api_model->if_notes_already_exist_in_pnv($t_params)==true ){
												echo '<button class="btn" disabled>Pending Verification</button>';
											}else{
												echo '<butotn class="btn pnv_verify_button">Property Needs Verification</button>';
											}
										?>
									</div>
								</div>
						    </div>
							<table id="pmeProp" class="display table table-striped table-bordered table-hover" >
								<thead>
									<tr>
										<th>Address Text</th>
										<th>Number</th>
										<th>Street</th>
										<th>Suburb</th>
										<th>
											<?php
											if ($this->config->item('country') == 1) {
												echo "State";
											}else {
												echo "Region";
											}
											?>
										</th>
										<th>Postal Code</th>
										<th>Status</th>
										<th>Link to CRM</th>
									</tr>
								</thead>
								<tbody>
									<?php 
									
										foreach ( $prop_tree_list as $key => $address_obj_row ) { 

                                            $api_prop_id = $address_obj_row->id;
                                            $address_obj = $address_obj_row->address;
                                            
                                            // street
                                            if( $address_obj->unit != '' && $address_obj->street_number != '' ){
                                                $street_unit_num = "{$address_obj->unit}/{$address_obj->street_number}";
                                            }else if( $address_obj->unit != '' ){
                                                $street_unit_num = "{$address_obj->unit}";
                                            }else if( $address_obj->street_number != '' ){
                                                $street_unit_num = "{$address_obj->street_number}";
                                            }                                                                                            

											$fullAdd = "{$street_unit_num} {$address_obj->address_line_1}, {$address_obj->suburb} {$address_obj->state} {$address_obj->post_code}";    
									?>
											<tr >
												<td class="api_address_tenant_det_btn"><?php echo str_replace(',','',$fullAdd)?></td>
												<td style="text-align: center;"><?php echo $street_unit_num?></td>
												<td><?php echo $address_obj->address_line_1?></td>
												<td><?php echo $address_obj->suburb?></td>
												<td style="text-align: center;"><?php echo $address_obj->state?></td>
												<td style="text-align: center;"><?php echo $address_obj->post_code?></td>
												<td style="text-align: center;">
													<?php 
														// if inactive, display archived or deleted
														if( $address_obj_row->archived == true || $address_obj_row->deleted == true ){
															
															if( $address_obj_row->archived == true ){
																$status_text = 'Archived';
															}else if( $address_obj_row->deleted == true ){
																$status_text = 'Deleted';
															}

															$status_icon = '<span class="fa fa-close text-red" data-toggle="tooltip" title="'.$status_text.'"></span>';
															
														}else{
															$status_icon = '<span class="fa fa-check text-green"></span>';
														}

														echo $status_icon;
													?>
												</td>
												<td>
													<a class="btn_link" href="javascript:void(0)" data-toggle="tooltip" title="Link to this property" dat-id="<?php echo $api_prop_id?>">
													<i class="fa fa-chain fa-lg"></i></a>	

													<input type="hidden" class="tenants_contact_id" value='<?php echo $address_obj_row->tenancy;  ?>' />
												</td>
											</tr>
									<?php
										}
									?>
								</tbody>
							</table>
				    	</div>
					<?php
					} else { ?>
						<div class="mt-3">No PropertyTree Data</div>
					<?php
					}
					?>
				  </div>
				</div>
		</div>
	</section>


	<!-- PMe more details - START -->
<a href="javascript:;" id="pme_details_fb_link" class="fb_trigger" data-fancybox data-src="#pme_details_fb">Trigger the fancybox</a>							
<div id="pme_details_fb" class="fancybox" style="display:none;" >

	<h4>PropertyTree Property Details</h4>

	<table class="table table-striped table-bordered">
		<tbody id="pme_prop_details_tbl_fb">
			<tr>
				<th>Address</th>
				<td class="pme_address_td"></td>
			</tr>
			<tr>
				<th>Number</th>
				<td class="pme_addr_number_td"></td>
			</tr>
			<tr>
				<th>Street</th>
				<td class="pme_addr_street_td"></td>
			</tr>
			<tr>
				<th>Suburb</th>
				<td class="pme_addr_suburb_td"></td>
			</tr>
			<tr>
				<th>Postal Code</th>
				<td class="pme_addr_postalcode_td"></td>
			</tr>
			<tr>
				<th>State</th>
				<td class="pme_addr_state_td"></td>
			</tr>
		</tbody>
	</table>

	<h4 style="margin-top: 33px;">Tenants</h4>

	<!-- tenants -->
	<table class="table table-striped table-bordered">
		<thead>
			<tr>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Email</th>
				<th>Mobile</th>
			</tr>
		</thead>
		<tbody class="pme_prop_tenants_tbl_fb">			
		</tbody>
	</table>

</div>
<!-- PMe more details - END -->

</div>
<script type="text/javascript">

function search_pme_datatable(address){

	jQuery('#pmeProp').DataTable().search( address, false, true ).draw();

}
	 
jQuery(document).ready(function() {
	

	var table = $('#pmeProp').DataTable({
		"ordering": false,
		"lengthChange": false
	});

	var crm_full_address = clearStreetName(jQuery(".crm_full_address").val()).trim();
	jQuery("#datatable_pme_search").val(crm_full_address);
	search_pme_datatable(crm_full_address);

	jQuery("#datatable_pme_search").keyup(function(){

		var address = jQuery(this).val();
		search_pme_datatable(address);
	});



	$(document).on('click', '.btn_link', function() {
		var pt_prop_id = $(this).attr('dat-id');
		var crmId = $("#add0").val();
		swal({
			title: "Are you sure?",
			text: "This will link this PropertyTree property to the CRM property above.",
			type: "warning",
			showCancelButton: true,			
			confirmButtonClass: "btn-success",
			confirmButtonText: "Yes, link it!",
			cancelButtonClass: "btn-danger",
			cancelButtonText: "No, Cancel!",
			closeOnConfirm: false,
			closeOnCancel: true,
			showLoaderOnConfirm: true
		},
		function(isConfirm) {
			if (isConfirm) {
			$('#load-screen').show(); 
			$.ajax({
				url: "/property_tree/ajax_function_link_property",
				type: 'POST',
				data: { 
					'pt_prop_id': pt_prop_id,
					'crmId': crmId
				}
			}).done(function( ret ){
				ret = JSON.parse(ret);
				$('#load-screen').hide(); 
				if (ret.updateStat === true) {
					swal({
						title: "Success!",
						text: "The properties are now linked.",
						type: "success",
						confirmButtonClass: "btn-success",
						showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                		timer: <?php echo $this->config->item('timer') ?>
					});
					var full_url = window.location.href;
                    setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);	
				}else {
					swal({
						title: "Error!",
						text: "Something went wrong, contact dev.",
						type: "error",
						confirmButtonClass: "btn-danger"
					});
				}
			})
			}
		});
	})

	$('.pnv_verify_button').on('click',function(){
		
		var pnv_id = "";
		var property_source = 1; // crm
		var property_id = <?php echo $propDet->property_id ?>;
		var property_address = $('.crm_full_address').val();
		var agency_id =  <?php echo $propDet->agency_id ?>;	
		var note = "Property Needs Verification";

		jQuery('#load-screen').show(); 
		jQuery.ajax({
			url: "/property_me/bulk_connect_save_note",
			type: 'POST',
			data: { 
				'pnv_id': pnv_id,
				'property_source': property_source,
				'property_id': property_id,
				'property_address': property_address,
				'agency_id': agency_id,
				'note': note
			}
		}).done(function( ret ){
			
			jQuery('#load-screen').hide(); 	
			swal({
				title:"Success!",
				text: "Submit success",
				type: "success",
				showCancelButton: false,
				confirmButtonText: "OK",
				closeOnConfirm: false,
				showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
				timer: <?php echo $this->config->item('timer') ?>

			});
			setTimeout(function(){location.reload(); }, <?php echo $this->config->item('timer') ?>);				

		});	

	})

	$('.api_address_tenant_det_btn').click(function(){

		var obj = jQuery(this);	
		var row = 	obj.parents("tr:first");	

		var address = $('#add1').val();
		var number = $('#add2').val();
		var street = $('#add3').val();
		var suburb = $('#add4').val();
		var state = $('#add5').val();
		var postcode = $('#add6').val();

		// prefill property details
		var pme_table_fb = jQuery("#pme_prop_details_tbl_fb");
		pme_table_fb.find(".pme_address_td").html(address);
		pme_table_fb.find(".pme_addr_number_td").html(number);
		pme_table_fb.find(".pme_addr_street_td").html(street);
		pme_table_fb.find(".pme_addr_suburb_td").html(suburb);
		pme_table_fb.find(".pme_addr_postalcode_td").html(state);
		pme_table_fb.find(".pme_addr_state_td").html(postcode);

		var agency_id =  <?php echo $propDet->agency_id ?>;	
		var tenants_contact_id = row.find(".tenants_contact_id").val();	

		if( tenants_contact_id != '' ){

			jQuery('#load-screen').show(); 		
			jQuery.ajax({
				url: "/property_tree/get_property_tree_tenancy",
				type: 'POST',
				data: { 
					'agency_id': agency_id,
					'tenancy_id': tenants_contact_id

				},
				dataType: 'json'
			}).done(function( ret ){
				
				jQuery('#load-screen').hide(); 

				var tenant_row_str = '';

				tenant_row_str += 
				'<tr>'+
					'<td>'+ret.fname+'</td>'+
					'<td>'+ret.lname+'</td>'+
					'<td>'+ret.email+'</td>'+
					'<td>'+ret.phone+'</td>'+
				'</tr>';			
				
				// populate tenants
				jQuery(".pme_prop_tenants_tbl_fb").html(''); // clear first
				jQuery(".pme_prop_tenants_tbl_fb").append(tenant_row_str);			

			});	

		}


		// pop-up lightbox
		jQuery("#pme_details_fb_link").click();
	})

});
</script>