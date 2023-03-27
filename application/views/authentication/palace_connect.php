
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
    border: solid 2px #14cdeb;
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
			'link' => "/property_me/property/{$this->uri->segment(3)}/{$this->uri->segment(4)}"
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
				  		$prop_full_add = "{$propDet->address_1} {$propDet->address_2} {$propDet->address_3} {$propDet->state} {$propDet->postcode}";
			  		 ?>
			  			<div class="row">
							<div class="col-lg-8">
					  			<div class="row">
									<div class="col-lg-12">
										<fieldset class="form-group">
											<label class="form-label semibold" for="add1">Address Text</label>
											<input type="hidden" id="add0" value="<?=$propDet->property_id?>">
											<div class="input-group">
												<input type="text" class="form-control crm_full_address" id="add1" placeholder="Address Text" value="<?=$prop_full_add?>">
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
											<input type="text" class="form-control" id="add2" placeholder="No." value="<?=$propDet->address_1?>">
										</fieldset>
									</div>
									<div class="col-lg-4">
										<fieldset class="form-group">
											<label class="form-label semibold" for="add3">Street</label>
											<input type="text" class="form-control" id="add3" placeholder="Street" value="<?=$propDet->address_2?>">
										</fieldset>
									</div>
									<div class="col-lg-2">
										<fieldset class="form-group">
											<label class="form-label semibold" for="add4">Suburb</label>
											<input type="text" class="form-control" id="add4" placeholder="Suburb" value="<?=$propDet->address_3?>">
										</fieldset>
									</div>
									<div class="col-lg-2">
										<fieldset class="form-group">
											<label class="form-label semibold" for="add5">State</label>
											<input type="text" class="form-control" id="add5" placeholder="State" value="<?=$propDet->state?>">
										</fieldset>
									</div>
									<div class="col-lg-2">
										<fieldset class="form-group">
											<label class="form-label semibold" for="add6">Postcode</label>
											<input type="text" class="form-control" id="add6" placeholder="Postcode" value="<?=$propDet->postcode?>">
										</fieldset>
									</div>
									
								</div>
							</div>
							<div class="col-lg-4">
								<fieldset class="form-group">
									<label class="form-label semibold" for="add7">Property Notes</label>
									<textarea class="form-control" placeholder="Notes" style="height: 120px;"><?=$propDet->comments?></textarea>
								</fieldset>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-12">
								<small class="text-muted">*This CRM property is not yet linked to any Palace property, kindly link to a Palace property below.</small>
							</div>
						</div>
			  		<?php
				  	} else { ?>
			  			<div class="row">
							<div class="col-lg-12">
								No property is associated with the property id <?=$this->uri->segment(3)?>
							</div>
						</div>
				  	<?php
				  	}
				  	?>

					<?php 
					if (isset($palaceList)) { 
					?>
					    <div id="pmeDetId" class="pme_main_div" style="display: <?=isset($propDet) ? "" : "none"?>">
						<img src="/images/third_party/Palace.png" class="company_logo pme_logo" />
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
										<th>State</th>
										<th>Postal Code</th>
										<th>Status</th>
										<th>Link to CRM</th>
									</tr>
								</thead>
								<tbody>
									<?php 
										foreach ($palaceList as $row) { 

			                                if (trim($row->PropertyUnit) != "") {
			                                    $addUnit = $row->PropertyUnit . "/";
			                                }else {
			                                    $addUnit = "";
                                			}

											$fullAdd = $addUnit.$row->PropertyAddress1 . " " . $row->PropertyAddress2 . ", " . $row->PropertyAddress3 . " " . $row->PropertyAddress4 . " " . $row->PropertyPostCode ;
									?>
											<tr >
												<td class="api_address_tenant_det_btn"><?=str_replace(',','',$fullAdd)?></td>
												<td style="text-align: center;"><?=$addUnit.$row->PropertyAddress1?></td>
												<td><?=$row->PropertyAddress2?></td>
												<td><?=$row->PropertyAddress3?></td>
												<td style="text-align: center;"><?=$row->PropertyAddress4?></td>
												<td style="text-align: center;"><?=$row->PropertyPostCode?></td>
												<td style="text-align: center;"><?php echo ( $row->PropertyArchived == 'true' )?'<span class="fa fa-close text-red" data-toggle="tooltip" title="Archived"></span>':'<span class="fa fa-check text-green"></span>'; ?></td>
												<td style="text-align: center;">
													<a class="btn_link" href="javascript:void(0)" data-toggle="tooltip" title="Link to this property" dat-id="<?=$row->PropertyCode?>">
													<i class="fa fa-chain fa-lg"></i></a>	

													<input type="hidden" class="api_prop_id" value="<?=$row->PropertyCode?>">
													<input type="hidden" class="owner_contact_id" value="<?php echo $row->PropertyOwnerCode ?>">
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
						<table id="pmeProp1" class="display table table-striped table-bordered" cellspacing="0" width="100%">
							<thead>
								<tr>
									<th>Address</th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<th>Address</th>
								</tr>
							</tfoot>
							<tbody>
								<tr>
									<td>No Palace Data</td>
								</tr>
							</tbody>
						</table>
					<?php
					}
					?>
				  </div>
				</div>
		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4>Palace Tenant & Palace Property Details</h4>
	<p>This page shows Palace Tenant & Palace Property Details via Palace API</p>

</div>
<!-- Fancybox END -->


<!-- PMe more details - START -->
<a href="javascript:;" id="pme_details_fb_link" class="fb_trigger" data-fancybox data-src="#pme_details_fb">Trigger the fancybox</a>							
<div id="pme_details_fb" class="fancybox" style="display:none;" >

	<h4>Palace Property Details</h4>

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
				<th>Mobile</th>
				<th>Landline</th>
				<th>Email</th>
			</tr>
		</thead>
		<tbody class="pme_prop_tenants_tbl_fb">			
		</tbody>
	</table>

</div>
<!-- PMe more details - END -->

<script type="text/javascript">

function search_pme_datatable(address){

	jQuery('#pmeProp').DataTable().search( address, false, true ).draw();

}

function isEmpty(obj) {
	for(var key in obj) {
		if(obj.hasOwnProperty(key))
			return false;
	}
	return true;
}
	 
jQuery(document).ready(function() {
	

	var table = $('#pmeProp').DataTable({
		"ordering": false,
		"lengthChange": false
	});

	var crm_full_address = clearStreetName(jQuery(".crm_full_address").val()).trim();
	var crm_full_address = crm_full_address.replace("  ", " ");
	jQuery("#datatable_pme_search").val(crm_full_address);
	search_pme_datatable(crm_full_address);

	jQuery("#datatable_pme_search").keyup(function(){

		var address = jQuery(this).val();
		search_pme_datatable(address);
	});



	$(document).on('click', '.btn_link', function() {
		var palaceId = $(this).attr('dat-id');
		var crmId = $("#add0").val();
		swal({
			title: "Are you sure?",
			text: "You will link this Palace property to the CRM property above.",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-success",
			confirmButtonText: "Yes, link it!",
			cancelButtonText: "No, cancel!",
			closeOnConfirm: false,
			closeOnCancel: true,
			showLoaderOnConfirm: true
		},
		function(isConfirm) {
			if (isConfirm) {
			$('#load-screen').show(); 
			$.ajax({
				url: "/palace/ajax_function_link_property",
				type: 'POST',
				data: { 
					'palaceId': palaceId,
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
						confirmButtonClass: "btn-success"
					},
					function(isConfirm) {
						location.reload();
					});
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

		var palace_prop_id = jQuery(this).parents("tr:first").find(".api_prop_id").val();
		var agency_id =  <?php echo $propDet->agency_id ?>;	
		var owner_contact_id = jQuery(this).parents("tr:first").find(".owner_contact_id").val();

		// prefill property details
		var pme_table_fb = jQuery("#pme_prop_details_tbl_fb");
		pme_table_fb.find(".pme_address_td").html(address);
		pme_table_fb.find(".pme_addr_number_td").html(number);
		pme_table_fb.find(".pme_addr_street_td").html(street);
		pme_table_fb.find(".pme_addr_suburb_td").html(suburb);
		pme_table_fb.find(".pme_addr_postalcode_td").html(state);
		pme_table_fb.find(".pme_addr_state_td").html(postcode);

		if( palace_prop_id != '' ){
			
			jQuery('#load-screen').show(); 		
			jQuery.ajax({
				url: "/palace/get_all_tenant_by_prop_code_via_json",
				type: 'POST',
				data: { 
					'palace_id': palace_prop_id,
					'agency_id': agency_id,
					'api_owner_code': owner_contact_id
				}
			}).done(function( ret ){
				
				jQuery('#load-screen').hide(); 

				var tenant_row_str = '';
                var pme_data_json = JSON.parse(ret);

	            if (typeof pme_data_json.tenants !== 'undefined') {
	                for( var i=0; i<pme_data_json.tenants.length; i++ ){
	                	var TenantFirstName = "";
						var TenantLastName = "";
						var TenantPhoneMobile = "";
						var TenantPhoneHome = "";
						var TenantEmail = "";
	                	if (!isEmpty(pme_data_json.tenants[i].TenancyTenants.DetailedTenant.TenantFirstName)) {
	                		TenantFirstName = pme_data_json.tenants[i].TenancyTenants.DetailedTenant.TenantFirstName
	                	}
						if (!isEmpty(pme_data_json.tenants[i].TenancyTenants.DetailedTenant.TenantLastName)) {
							TenantLastName = pme_data_json.tenants[i].TenancyTenants.DetailedTenant.TenantLastName
						}
						if (!isEmpty(pme_data_json.tenants[i].TenancyTenants.DetailedTenant.TenantPhoneMobile)) {
							TenantPhoneMobile = pme_data_json.tenants[i].TenancyTenants.DetailedTenant.TenantPhoneMobile
						}
						if (!isEmpty(pme_data_json.tenants[i].TenancyTenants.DetailedTenant.TenantPhoneHome)) {
							TenantPhoneHome = pme_data_json.tenants[i].TenancyTenants.DetailedTenant.TenantPhoneHome
						}
						if (!isEmpty(pme_data_json.tenants[i].TenancyTenants.DetailedTenant.TenantEmail)) {
							TenantEmail = pme_data_json.tenants[i].TenancyTenants.DetailedTenant.TenantEmail
						}
						tenant_row_str += 
						`<tr>
							<td>`+TenantFirstName+`</td>
							<td>`+TenantLastName+`</td>
							<td>`+TenantPhoneMobile+`</td>
							<td>`+TenantPhoneHome+`</td>
							<td>`+TenantEmail+`</td>
						</tr>`;
	                }
	            }
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