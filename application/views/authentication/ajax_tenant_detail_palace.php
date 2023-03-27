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
#load-screen2 {
	width: 100%;
	height: 100%;
	background: url("/images/preloader2.gif") no-repeat center center #fff;
	position: fixed;
	opacity: 0.7;
	display:none;
	z-index: 999999;
 	margin-top: -107px;
    margin-left: -271px;
}
table tr td {
	text-align: left;
}
table tr th {
	text-align: left;
}
.fa-lg:hover{
  color: #d01818;
}
.btn_save, 
.btn_can, 
.prop_more_info,
.edit_btn_div,
.txt_hid,
.comp_det_exp_up{
	display: none;
}
.company_logo{
	height: 70px;
	margin-top: 17px;
}
.company_logo_div{
	text-align: center !important;
}
.crm_tenant_action_div .font-icon {
    color: #adb7be;
		margin-right: 4px;
}
.crm_tenant_action_div .font-icon:hover {
    color: #00a8ff;
}
.crm_tenant_action_div .font-icon-trash:hover {
    color: #d01818;
}
.edit_btn_div .btn{
	width: 77px;
}
.edit_btn_div .btn_save_crm_tenant{
	margin-bottom: 3px;
}
.comp_det_exp_icon{
    width: 15px;
}
.link_icon{
	width: 19px;
	cursor:pointer;
}
.table td {
    height: 58px !important;
}
.pme_vpd_div .box-typical-header h3 {
    color: #00a8ff;
    font-size: 17px !important;
}
.box-typical-header .font-icon,
.box-typical-header .glyphicon {
    position: relative;
    top: 1px;
}
.light-grey-bg{
	background: #f6f8fa !important;
}
.pme_main_div {
    border: solid 2px #14cdeb;
    background: #f5f8fa;	
}
.breadcrumb{
	margin-bottom: 0;
}
.crm_main_div{
	border-top: solid 2px white;
}
.pme_btn_color{
	background-color: #14cdeb !important;
    border-color: #14cdeb !important;

}
.pme_headings{
	color: #14cdeb !important;
}
.remove_bottom_padding{
	padding-bottom: 0 !important;
}

.redRowBg1 {
    /*background-color: #e6000073;*/
}
</style>



<div id="load-screen2"></div>
<div class="box-typical box-typical-padding remove_bottom_padding">
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

<input type="hidden" id="add0" value="<?=$propId?>">
<?php if (isset($tenant_json_dec)) { ?>
	<section>
		<div class="body-typical-body">
				<div class="row pme_vpd_div">



				
				<div class="col-md-6 crm_main_div">


					<!-- CRM Property Details -->
					<div class="col-md-12">
						<div clas="row company_logo_div" style="text-align: center;">
							<img src="/images/logo_login.png" class="company_logo sats_logo" />
						</div>

						<header class="box-typical-header">
							<div class="tbl-row">
								<div class="tbl-cell tbl-cell-title">
									<h3><span class="glyphicon glyphicon-map-marker"></span> Property Details</h3>
								</div>
							</div>
						</header>

						<table class="table table-striped table-bordered " id="myTable">
							<thead>
								<tr>
									<th colspan="3">Address</th>
								</tr>
							</thead>
							<tbody>
							<tr>
								<td>
									<table class="table"> 

										<tbody>
										<tr>
											<th>Full Address</td>
											<td>
												<?php echo "{$crm_prop->p_address_1} {$crm_prop->p_address_2}, {$crm_prop->p_address_3} {$crm_prop->p_state} {$crm_prop->p_postcode}" ?>
											</td>
											<td class="toggle_display_icon_td text-center">
												<a href="javascript:void;">
													<img src="/images/expand-down.png" class="comp_det_exp_icon comp_det_exp_down" />
													<img src="/images/expand-up.png" class="comp_det_exp_icon comp_det_exp_up" />
												</a>
											</td>
										</tr>
										</tbody>

										<tbody class="prop_more_info">
										<?php
											$add = array('Number','Street','Suburb','PostalCode','State','Country','Latitude','Longitude','Text');
											foreach ($add as $value) { 
											
												$val = null;
												switch( $value ){
													case 'Number':
														$val = $crm_prop->p_address_1;
													break;
													case 'Street':
														$val = $crm_prop->p_address_2;
													break;
													case 'Suburb':
														$val = $crm_prop->p_address_3;
													break;
													case 'PostalCode':
														$val = $crm_prop->p_postcode;
													break;
													case 'State':
														$val = $crm_prop->p_state;
													break;
													case 'Country':
														$val = $crm_prop->country_name;
													break;
													case 'Latitude':
														$val = $crm_prop->lat;
													break;
													case 'Longitude':
														$val = $crm_prop->lng;
													break;
													case 'Text':
														$val = "{$crm_prop->p_address_1} {$crm_prop->p_address_2}, {$crm_prop->p_address_3} {$crm_prop->p_state} {$crm_prop->p_postcode}";
													break;														
												}

											?>
												<tr>
													<th><?php echo $value; ?></th>
													<td colspan="2"><?php echo $val; ?></td>
												</tr>
											<?php
											}
										?>
										</tbody>

									</table>
								</td>				
							</tbody>
						</table>
					</div>



					<!-- CRM Tenant Details -->
					<div class="col-md-12">
						
						<header class="box-typical-header">
							<div class="tbl-row">
								<div class="tbl-cell tbl-cell-title">
									<h3><span class="font-icon font-icon-users"></span> Tenant Details</h3>
								</div>
							</div>
						</header>

						<table class="table table-hover main-table crm_ten_det_tbl" id="crmTebabtTable1" style="display: none;" >
							<thead>
								<tr>
									<th>First Name</th>
									<th>Last Name</th>
									<th>Mobile</th>
									<th>Landline</th>
									<th>Email</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								if (isset($crmTenant)) {
									foreach ($crmTenant as $val) {
								?>
									<tr id="tr_id1<?=$val['property_tenant_id']?>">
										<td id="firstame_span1<?=$val['property_tenant_id']?>"><?=$val['tenant_firstname']?></td>
										<td id="lastname_span1<?=$val['property_tenant_id']?>"><?=$val['tenant_lastname']?></td>
										<td id="mobile_span1<?=$val['property_tenant_id']?>"><?=$val['tenant_mobile']?></td>
										<td id="landline_span1<?=$val['property_tenant_id']?>"><?=$val['tenant_landline']?></td>
										<td id="email_span1<?=$val['property_tenant_id']?>"><?=$val['tenant_email']?></td>
									</tr>
								<?php
									}	
								}else { ?>
									<tr>
										<td colspan="5" align="center">No Property Connected</td>
									</tr>
								<?php
								}
								?>
							</tbody>
						</table>
						<table class="table table-hover main-table" id="crmTebabtTable">
							<thead>
								<tr>
									<th>First Name</th>
									<th>Last Name</th>
									<th>Mobile</th>
									<th>Landline</th>
									<th>Email</th>
									<th width="17%">Action</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								if (isset($crmTenant)) {
									foreach ($crmTenant as $val) {
								?>
									<tr id="tr_id<?=$val['property_tenant_id']?>">
										<td>
											<span class="txt_lbl"><?=$val['tenant_firstname']?></span>
											<input type="text" class="form-control txt_hid tenant_firstname" placeholder="firstname" value="<?=$val['tenant_firstname']?>">
										</td>
										<td>
											<span class="txt_lbl"><?=$val['tenant_lastname']?></span>
											<input type="text" class="form-control txt_hid tenant_lastname" placeholder="lastname" value="<?=$val['tenant_lastname']?>">
										</td>
										<td>
											<span class="txt_lbl"><?=$val['tenant_mobile'];?></span>
											<input type="text" class="form-control txt_hid tenant_mobile" placeholder="mobile" value="<?=$val['tenant_mobile'];?>">
										</td>
										<td>
											<span class="txt_lbl"><?=$val['tenant_landline']?></span>
											<input type="text" class="form-control txt_hid tenant_landline" placeholder="landline" value="<?=$val['tenant_landline']?>">
										</td>
										<td>
											<span class="txt_lbl"><?=$val['tenant_email']?></span>
											<input type="text" class="form-control txt_hid tenant_email" placeholder="email" value="<?=$val['tenant_email']?>">
										</td>
										<td class="crm_tenant_action_div">

											<div class="action_btn_div">
												<a href="javascript:void(0);" data-toggle="tooltip" title="" data-original-title="Edit">
													<span class="font-icon font-icon-pencil btn_edit_crm_tenant btn_edit"></span>
												</a>											
												<a href="javascript:void(0);" data-toggle="tooltip" title="" data-original-title="Remove">
													<span class="font-icon font-icon-trash btn_delete_crm_tenant btn_del" data-id="<?=$val['property_tenant_id']?>"></span>
												</a>
											</div>

											<div class="edit_btn_div">
												<button class="btn btn-primary btn_save_crm_tenant" data-id="<?=$val['property_tenant_id']?>">Save</button>
												<button class="btn btn-danger btn_cancel_crm_tenant" data-id="<?=$val['property_tenant_id']?>">Cancel</button>
												<input type="hidden" class="property_tenant_id" value="<?php echo $val['property_tenant_id']; ?>" />
											</div>

										</td>
									</tr>
								<?php
									}	
								}else { ?>
									<tr>
										<td colspan="5" align="center">No Property Connected</td>
									</tr>
								<?php
								}
								?>
							</tbody>
						</table>
				  	</div>

									

				</div>



	

				<div class="col-md-6 pme_main_div">


					<!-- PMe Property Details -->
					<div class="col-md-12">
						<div clas="row company_logo_div" style="text-align: center;">									
							<img src="/images/third_party/Palace.png" class="company_logo pme_logo" />
						</div>
						
						<header class="box-typical-header">
							<div class="tbl-row">
								<div class="tbl-cell tbl-cell-title">
									<h3 class="pme_headings"><span class="glyphicon glyphicon-map-marker"></span> Property Details</h3>
								</div>
							</div>
						</header>
						<?php 
							$fullAdd = $lotList->PropertyAddress1 . " " . $lotList->PropertyAddress2 . ", " . $lotList->PropertyAddress3 . " " . $lotList->PropertyAddress4 . " " . $lotList->PropertyPostCode;
						?>
						<table class="table table-striped table-bordered " id="myTable">
							<thead>
								<tr>
									<th>Address</th>
									<th class="text-center">Link/Unlink</th>
								</tr>
							</thead>
							<tbody>
							<tr>
								<td>
									<table class="table"> 

										<tbody>
											<tr>
												<th>Full Address</td>
												<td><?=$fullAdd?></td>
												<td class="toggle_display_icon_td text-center">
													<a href="javascript:void;">
														<img src="/images/expand-down.png" class="comp_det_exp_icon comp_det_exp_down" />
														<img src="/images/expand-up.png" class="comp_det_exp_icon comp_det_exp_up" />
													</a>
												</td>
											</tr>
										</tbody>

										<tbody class="prop_more_info">
										<?php
											$add = array('Number','Street','Suburb','PostalCode','State','Country','Latitude','Longitude','Text');
											foreach ($add as $value) { 
												switch( $value ){
													case 'Number':
														$val = $lotList->PropertyAddress1;
													break;
													case 'Street':
														$val = $lotList->PropertyAddress2;
													break;
													case 'Suburb':
														$val = $lotList->PropertyAddress3;
													break;
													case 'PostalCode':
														$val = $lotList->PropertyPostCode;
													break;
													case 'State':
														$val = $lotList->PropertyAddress4;
													break;
													case 'Country':
														$val = "";
													break;
													case 'Latitude':
														$val = "";
													break;
													case 'Longitude':
														$val = "";
													break;
													case 'Text':
														$val = $lotList->PropertyName;
													break;														
												}

											?>
												<tr>
													<th><?php echo $value; ?></th>
													<td colspan="2"><?php echo $val; ?></td>
												</tr>
											<?php
											}
										?>
										</tbody>

									</table>
								</td>
								<td class="text-center">
									<!--
									<a class="btn_link" href="javascript:void(0)" data-toggle="tooltip" title="Unlink this PMe property" data-id="<?=$lotList->Id?>">
									<i class="fa fa-chain-broken fa-lg"></i></a>	
									-->
									<img src="/images/link-green.png" class="link_icon btn_link" data-id="<?=$lotList->PropertyCode?>" />
								</td>						
							</tbody>
						</table>
					</div>
					

					<!-- PMe Tenant Details -->
				  	<div class="col-md-12">
						
						<header class="box-typical-header">
							<div class="tbl-row">
								<div class="tbl-cell tbl-cell-title">
									<h3 class="pme_headings"><span class="font-icon font-icon-users"></span> Tenant Details</h3>
								</div>
							</div>
						</header>

						<table class="table table-hover main-table" id="pmeTenantTable">
							<thead>
								<tr>
									<th>First Name</th>
									<th>Last Name</th>
									<th>Mobile</th>
									<th>Landline</th>
									<th>Email</th>
									<th class="text-center">Action</th>
								</tr>
							</thead>
							<tbody>
								<?php 
								foreach ($tenant_json_dec as $tenant_json_data ) { 

									$palace_tenant_obj_row = $tenant_json_data->TenancyTenants[0];
								?>
									<tr
									class="<?php 
									if (isset($crmTenant)) {
										foreach ($crmTenant as $val1) {
											$TenantFirstName = empty((array)$palace_tenant_obj_row->TenantFirstName) ? "" : $palace_tenant_obj_row->TenantFirstName;
											$TenantLastName = empty((array)$palace_tenant_obj_row->TenantLastName) ? "" : $palace_tenant_obj_row->TenantLastName;
											$TenantPhoneMobile = empty((array)$palace_tenant_obj_row->TenantPhoneMobile) ? "" : $palace_tenant_obj_row->TenantPhoneMobile;
											$TenantPhoneHome = empty((array)$palace_tenant_obj_row->TenantPhoneHome) ? "" : $palace_tenant_obj_row->TenantPhoneHome;
											$TenantEmail = empty((array)$palace_tenant_obj_row->TenantEmail) ? "" : $palace_tenant_obj_row->TenantEmail;
														if ($val1['tenant_firstname'] == $TenantFirstName) {
																if ($val1['tenant_lastname'] == $TenantLastName) {
																	if ($val1['tenant_mobile'] == $TenantPhoneMobile) {
																		if($val1['tenant_landline'] == $TenantPhoneHome) {
																			if($val1['tenant_email'] == $TenantEmail){
																				echo "redRowBg1 patternId";
																			}
																		}
																	}
																}
														}
										}
									}
									?>">
										<td><?=$palace_tenant_obj_row->TenantFirstName?></td>
										<td><?=$palace_tenant_obj_row->TenantLastName?></td>
										<td><?=$palace_tenant_obj_row->TenantPhoneMobile?></td>
										<td><?=$palace_tenant_obj_row->TenantPhoneHome?></td>
										<td><?=$palace_tenant_obj_row->TenantEmail?></td>
										<td class="text-center">
											<i class="fa fa-question-circle possibleIcon" style="font-size:22px;color:#5dca73; vertical-align: middle; display: none;" data-toggle="tooltip" data-placement="left" title="Possible New Tenant"></i>
												<span class="possibleIcon2" style="margin-left: 15px;">&nbsp;</span>
													<button type="button" class="addTenant btn btn-primary pme_btn_color" dat-first="<?=$palace_tenant_obj_row->TenantFirstName?>" dat-last="<?=$palace_tenant_obj_row->TenantLastName?>" dat-mob="<?=$palace_tenant_obj_row->TenantPhoneMobile?>" dat-land="<?=$palace_tenant_obj_row->TenantPhoneHome?>" dat-email="<?=$palace_tenant_obj_row->TenantEmail?>" dat-prop-id="<?=$propId?>">
														<span class="">Add to CRM</span> 
													</button>
										</td>
									</tr>
								<?php
								}
								?>
							</tbody>
						</table>
					</div>
					  
					

				</div>								

					

					
					
				</div>
		</div>
	</section>
<?php } ?>
	
</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4>Palace Tenant & Palace Property Details</h4>
	<p>This page shows Palace Tenant & Palace Property Details via Palace API</p>

</div>
<!-- Fancybox END -->

<script type="text/javascript">
	function getNotMatch() {
	        $('#crmTebabtTable1 tbody tr').each(function(){
	        var row1 = $(this);
	        var left_cols1 = $(this).find("td").eq(0).html();
	        var left_cols2 = $(this).find("td").eq(1).html();
	        var left_cols3 = $(this).find("td").eq(2).html();
	        var left_cols4 = $(this).find("td").eq(3).html();
	        var left_cols5 = $(this).find("td").eq(4).html();

	        $('#pmeTenantTable tbody tr').each(function(){
	        	var row2 = $(this);
		        var right_cols1 = $(this).find("td").eq(0).html();
		        var right_cols2 = $(this).find("td").eq(1).html();
		        var right_cols3 = $(this).find("td").eq(2).html();
		        var right_cols4 = $(this).find("td").eq(3).html();
		        var right_cols5 = $(this).find("td").eq(4).html();

	             if (left_cols1 == right_cols1 && left_cols2 == right_cols2 && left_cols3 == right_cols3 && left_cols4 == right_cols4 && left_cols5 == left_cols5) {
						row2.addClass('redRowBg1');
	             }

	         });
			$('#pmeTenantTable > tbody  > tr').each(function() {
			var quesIcon = $(this).find('.possibleIcon');
			var quesIcon2 = $(this).find('.possibleIcon2');
				if ($(this).hasClass("redRowBg1")) {
					$(this).removeClass("redRowBg");
					$(this).removeClass("redRowBg1");
					quesIcon.hide();
					quesIcon2.show();
					if ($(this).hasClass("patternId")) {
						// $(this).addClass("redRowBg");
						$(this).removeClass("patternId");
						quesIcon.show();
						quesIcon2.hide();
					}
				}else {
					// $(this).addClass("redRowBg");
					quesIcon.show();
					quesIcon2.hide();
				}
			});
	      });
	}

	// document is ready/loaded
	$(document).ready(function() {


		// action button edit 
    	jQuery(document).on("click",".btn_edit_crm_tenant",function() {

			jQuery(this).parents("tr:first").find(".action_btn_div").hide();
			jQuery(this).parents("tr:first").find(".edit_btn_div").show();
			jQuery(this).parents("tr:first").find(".txt_lbl").hide();
			jQuery(this).parents("tr:first").find(".txt_hid").show();

		});

		// action button cancel 
    	jQuery(document).on("click",".btn_cancel_crm_tenant",function() {

			jQuery(this).parents("tr:first").find(".action_btn_div").show();
			jQuery(this).parents("tr:first").find(".edit_btn_div").hide();
			jQuery(this).parents("tr:first").find(".txt_lbl").show();
			jQuery(this).parents("tr:first").find(".txt_hid").hide();

		});


		// expand down
		jQuery(".comp_det_exp_down").click(function(){

			jQuery(".prop_more_info").fadeIn();
			jQuery(".comp_det_exp_down").hide();
			jQuery(".comp_det_exp_up").show();

		});

		// expand up
		jQuery(".comp_det_exp_up").click(function(){

			jQuery(".prop_more_info").fadeOut();
			jQuery(".comp_det_exp_down").show();
			jQuery(".comp_det_exp_up").hide();

		});

		$('#load-screen').hide();
		$('#pmeTenantTable > tbody  > tr').each(function() {
			if ($(this).hasClass('redRowBg1')) {}else {

				jQuery(this).addClass("light-grey-bg");

				// $(this).addClass("redRowBg");
				var quesIcon = $(this).find('.possibleIcon');
				var quesIcon2 = $(this).find('.possibleIcon2');
				quesIcon.show();
				quesIcon2.hide();
			}
		});
		$(document).on('click', '.btn_del', function() {
			var id = $(this).attr('data-id');
			swal({
			  title: "Deactivate",
			  text: "Are you sure you want to Deactivate tenant?",
			  type: "warning",
			  showCancelButton: true,
			  confirmButtonClass: "btn-danger",
			  confirmButtonText: "Yes, Deactivate!",
			  cancelButtonText: "No, cancel!",
			  closeOnConfirm: true,
			  closeOnCancel: true
			},
			function(isConfirm) {
			  if (isConfirm) {
				$('#load-screen2').show(); 
				$.ajax({
					url: "/property_me/ajax_function_tenants_delete",
					type: 'POST',
					data: { 
						'tenant_id': id
					}
				}).done(function( ret ){
					ret = JSON.parse(ret);
					$('#load-screen2').hide(); 
					if (ret.updateStat === true) {
						var firstT = $('#tr_id'+ret.updatedId).find("td:nth-child(1)").text().trim();
						var lastT = $('#tr_id'+ret.updatedId).find("td:nth-child(2)").text().trim();
						$('#pmeTenantTable > tbody  > tr').each(function() {
								var first = $(this).find("td:nth-child(1)").text().trim();
								var last = $(this).find("td:nth-child(2)").text().trim();
								if (firstT == first && lastT == last) {
									var quesIcon = $(this).find('.possibleIcon');
									var quesIcon2 = $(this).find('.possibleIcon2');
									quesIcon2.hide();
									quesIcon.show();
								}
						});

						$('#tr_id'+ret.updatedId).remove();
					}else {
			            swal({
			                title: "Error!",
			                text: "Something went wrong contact devs.",
			                type: "error",
			                confirmButtonClass: "btn-success"
			            });
					}
				})
			  }
			});
		})
		


	// update tenant
	jQuery(document).on('click', '.btn_save_crm_tenant', function() {

		var obj =  jQuery(this);
		var parent_row = obj.parents("tr:first");
		var property_tenant_id = parent_row.find(".property_tenant_id").val();
		var firstname = parent_row.find('.tenant_firstname').val();
		var lastname = parent_row.find('.tenant_lastname').val();
		var mobile = parent_row.find('.tenant_mobile').val();
		var landline = parent_row.find('.tenant_landline').val();
		var email = parent_row.find('.tenant_email').val();

		jQuery('#load-screen').show(); 
		jQuery.ajax({
			url: "/property_me/ajax_function_tenants_edit",
			type: 'POST',
			data: { 
				'tenant_id': property_tenant_id, 
				'tenant_firstname' : firstname, 
				'tenant_lastname' : lastname, 
				'tenant_mobile' : mobile, 
				'tenant_landline' : landline, 
				'tenant_email' : email, 
				'active': 1 
			}
		}).done(function( ret ){
			
			ret = JSON.parse(ret);
			jQuery('#load-screen').hide(); 
			
	        var newRow = `
				<td>
					<span class="txt_lbl">`+firstname+`</span>
					<input type="text" class="form-control txt_hid tenant_firstname" placeholder="firstname" value="`+firstname+`">
				</td>
				<td>
					<span class="txt_lbl">`+lastname+`</span>
					<input type="text" class="form-control txt_hid tenant_lastname" placeholder="lastname" value="`+lastname+`">
				</td>
				<td>
					<span class="txt_lbl">`+mobile+`</span>
					<input type="text" class="form-control txt_hid tenant_mobile" placeholder="mobile" value="`+mobile+`">
				</td>
				<td>
					<span class="txt_lbl">`+landline+`</span>
					<input type="text" class="form-control txt_hid tenant_landline" placeholder="landline" value="`+landline+`">
				</td>
				<td>
					<span class="txt_lbl">`+email+`</span>
					<input type="text" class="form-control txt_hid tenant_email" placeholder="email" value="`+email+`">
				</td>
				<td class="crm_tenant_action_div">

					<div class="action_btn_div">
						<a href="javascript:void(0);" data-toggle="tooltip" title="" data-original-title="Edit">
							<span class="font-icon font-icon-pencil btn_edit_crm_tenant btn_edit"></span>
						</a>											
						<a href="javascript:void(0);" data-toggle="tooltip" title="" data-original-title="Remove">
							<span class="font-icon font-icon-trash btn_delete_crm_tenant btn_del" data-id="`+property_tenant_id+`"></span>
						</a>
					</div>

					<div class="edit_btn_div">
						<button class="btn btn-primary btn_save_crm_tenant" data-id="`+property_tenant_id+`">Save</button>
						<button class="btn btn-danger btn_cancel_crm_tenant" data-id="`+property_tenant_id+`">Cancel</button>
						<input type="hidden" class="property_tenant_id" value="`+property_tenant_id+`" />
					</div>

				</td>
			`;

	        $('#tr_id'+property_tenant_id).html(newRow);

			jQuery('#tr_id'+property_tenant_id).find(".action_btn_div").show();
			jQuery('#tr_id'+property_tenant_id).find(".edit_btn_div").hide();
			jQuery('#tr_id'+property_tenant_id).find(".txt_lbl").show();
			jQuery('#tr_id'+property_tenant_id).find(".txt_hid").hide();

		});

	});


		$(document).on('click', '.btn_link', function() {
			var palaceId = $(this).attr('data-id');
			var crmId = $("#add0").val();
			swal({
			  title: "Are you sure?",
			  text: "You will unlink this Palace property.",
			  type: "warning",
			  showCancelButton: true,
			  confirmButtonClass: "btn-danger",
			  confirmButtonText: "Yes, unlink it!",
			  cancelButtonText: "No, cancel!",
			  closeOnConfirm: false,
			  closeOnCancel: true,
			  showLoaderOnConfirm: true
			},
			function(isConfirm) {
			  if (isConfirm) {
				$('#load-screen').show(); 
				$.ajax({
					url: "/palace/ajax_function_unlink_property",
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
			                text: "The properties are now unlinked.",
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
		$(document).on('click', '.btn_edit', function() {
			var id = $(this).attr('data-id');
			$('.td1_id'+id).hide();
			$('.td2_id'+id).show();
			$('#tenant_firstname'+id).show();
			$('#tenant_lastname'+id).show();
			$('#tenant_mobile'+id).show();
			$('#tenant_landline'+id).show();
			$('#tenant_email'+id).show();
			$('#firstame_span'+id).hide();
			$('#lastname_span'+id).hide();
			$('#mobile_span'+id).hide();
			$('#landline_span'+id).hide();
			$('#email_span'+id).hide();
		});
		$(document).on('click', '.btn_can', function() {
			var id = $(this).attr('data-id');
			$('.td1_id'+id).show();
			$('.td2_id'+id).hide();
			$('#tenant_firstname'+id).hide();
			$('#tenant_lastname'+id).hide();
			$('#tenant_mobile'+id).hide();
			$('#tenant_landline'+id).hide();
			$('#tenant_email'+id).hide();
			$('#tenant_email'+id).hide();
			$('#firstame_span'+id).show();
			$('#lastname_span'+id).show();
			$('#mobile_span'+id).show();
			$('#landline_span'+id).show();
			$('#email_span'+id).show();
		});
		$(document).on('click', '.addTenant', function() {
			// $(this).parent().parent('tr').removeClass('redRowBg')
			var addtr = $(this).parent().parent('tr');
			var quesIcon = addtr.find('.possibleIcon');
			var quesIcon2 = addtr.find('.possibleIcon2');
			quesIcon.hide();
			quesIcon2.show();
			<?php if (isset($crmTenant)) { ?>
				$('#load-screen').show(); 
				var datpropid =$(this).attr('dat-prop-id');
				var datfirst = $(this).attr('dat-first');
				var datlast = $(this).attr('dat-last');
				var datmob = $(this).attr('dat-mob');
				var datland = $(this).attr('dat-land');
				var datemail = $(this).attr('dat-email');

				$.ajax({
					url: "/property_me/ajax_function_tenants",
					type: 'POST',
					data: { 
						'property_id': datpropid, 
						'tenant_firstname' : datfirst, 
						'tenant_lastname' : datlast, 
						'tenant_mobile' : datmob, 
						'tenant_landline' : datland, 
						'tenant_email' : datemail, 
						'active': 1 
					}
				}).done(function( ret ){
					ret = JSON.parse(ret);
					$('#load-screen').hide();
					

					if (ret.isExist === true) {
			            swal({
			                title: "Warning!",
			                text: "There is a tenant with the same First Name and Last Name already.",
			                type: "error",
			                confirmButtonClass: "btn-success"
			            });
					}else {
						if (ret.insertStat === true) {

					        var newRow = `
									<tr id="tr_id`+ret.insertId+`">
										<td>
											<span class="txt_lbl">`+datfirst+`</span>
											<input type="text" class="form-control txt_hid tenant_firstname" placeholder="firstname" value="`+datfirst+`">
										</td>
										<td>
											<span class="txt_lbl">`+datlast+`</span>
											<input type="text" class="form-control txt_hid tenant_lastname" placeholder="lastname" value="`+datlast+`">
										</td>
										<td>
											<span class="txt_lbl">`+datmob+`</span>
											<input type="text" class="form-control txt_hid tenant_mobile" placeholder="mobile" value="`+datmob+`">
										</td>
										<td>
											<span class="txt_lbl">`+datland+`</span>
											<input type="text" class="form-control txt_hid tenant_landline" placeholder="landline" value="`+datland+`">
										</td>
										<td>
											<span class="txt_lbl">`+datemail+`</span>
											<input type="text" class="form-control txt_hid tenant_email" placeholder="email" value="`+datemail+`">
										</td>
										<td class="crm_tenant_action_div">

											<div class="action_btn_div">
												<a href="javascript:void(0);" data-toggle="tooltip" title="" data-original-title="Edit">
													<span class="font-icon font-icon-pencil btn_edit_crm_tenant btn_edit"></span>
												</a>											
												<a href="javascript:void(0);" data-toggle="tooltip" title="" data-original-title="Remove">
													<span class="font-icon font-icon-trash btn_delete_crm_tenant btn_del" data-id="`+ret.insertId+`"></span>
												</a>
											</div>

											<div class="edit_btn_div">
												<button class="btn btn-primary btn_save_crm_tenant" data-id="`+ret.insertId+`">Save</button>
												<button class="btn btn-danger btn_cancel_crm_tenant" data-id="`+ret.insertId+`">Cancel</button>
												<input type="hidden" class="property_tenant_id" value="`+ret.insertId+`" />
											</div>

										</td>
									</tr>`;

					        $("#crmTebabtTable").append(newRow);

							// location.reload();


						}else {
				            swal({
				                title: "Error!",
				                text: "Something went wrong contact devs.",
				                type: "error",
				                confirmButtonClass: "btn-success"
				            });
						}
					}




				});
			<?php
			}else { ?>
	            swal({
	                title: "Error!",
	                text: "Sorry, no property is connected to this PMe property",
	                type: "error",
	                confirmButtonClass: "btn-danger"
	            });
			<?php
			} ?>

		})
	})
</script>