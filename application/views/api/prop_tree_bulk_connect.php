
<link rel="stylesheet" href="/inc/css/lib/datatables-net/datatables.min.css">
<link rel="stylesheet" href="/inc/css/separate/vendor/datatables-net.min.css">
<script src="/inc/js/lib/datatables-net/datatables.min.js"></script>
<link type="text/css" href="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.11/css/dataTables.checkboxes.css" rel="stylesheet" />
<script type="text/javascript" src="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.11/js/dataTables.checkboxes.min.js"></script>
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
/*
.dataTables_filter {
display: none; 
}
*/
.dataTables_paginate {
	float: left;
	margin-top: 40px !important;
}
.btn-square-icon {
    height: 60px !important;
}
.crmClass{
	padding-right: 0px !important;
}
.pmeClass{
	padding-left: 0px !important;
}
#crmProp_wrapper {
   padding-right: 0px !important;
}
#pmeProp_wrapper {
   padding-left: 0px !important;
}
.borderless-cell { 
	border: white !important;
	background-color: white !important;
} 
#crmProp {

    /*border-right: 1.5px solid rgb(222, 226, 230) !important;*/
}

#pmeProp {

    /*border-right: 1.5px solid rgb(222, 226, 230) !important;*/
}


.table-borderless > tbody > tr > td,
.table-borderless > tbody > tr > th,
.table-borderless > tfoot > tr > td,
.table-borderless > tfoot > tr > th,
.table-borderless > thead > tr > td,
.table-borderless > thead > tr > th {
   /*border-left: 1px solid #dee2e6;*/
}

.table-borderless > tbody > tr > td:nth-child(2),
.table-borderless > thead > tr > th:nth-child(2) { 
	/*border-right: 1px solid #dee2e6;*/
}

.table-borderless > tbody > tr:last-child > td { 
	/*border-bottom: 1px solid #dee2e6 !important;*/
}


.glyphicon-resize-horizontal{
    color: white;
}

.selected td:last-child .glyphicon-resize-horizontal {
	color: black !important;
}
.company_logo{
	height: 70px;
}
.company_logo_div{
	text-align: center !important;
}
#agency_filter{
	margin-right: 15px;
}
#bulkCon, 
#pme_main_div, 
.match_arrow{
	display:none;	
}

.chk_col {
    width: 25px;
    text-align: center;
}
.checkbox {
    margin: 0;
}
.chk_lbl{
	padding: 9px !important;
}

#crmProp th,
#crmProp td,
#pmeProp th,
#pmeProp td{
	height: 25px;
	padding: 5px;
}


#crmProp tr,
#pmeProp tr{
	cursor: move;
}

#crmProp tr:hover,
#pmeProp tr:hover {
	background-color: #ECEFF4;
}


.match_arrow{
    color: #46c35f;
}
.match_hl_bgcolor{
	background-color: #ECEFF4 !important;
	color: #000000 !important;
}
.dataTables_paginate {
    float: right;
    margin-top: 0 !important;
	padding-right: 17px;
}
.crmAdd{
	color: #0082c6;
}
#crmProp_filter{
	margin-right: 18px;
}

#bulkCon{
	border-radius: 3px;
	height: auto !important;
	width: auto;
	padding: 5.3px;
}

#numCon{
	position: absolute !important;
	right: -10px !important;
	top: -10px !important;
}
#crmProp,
#pmeProp{
	border-bottom: 1px solid #dee2e6;
	border-left: 1px solid #dee2e6;
}

#crmProp td,
#crmProp th,
#pmeProp td,
#pmeProp th{
	border-right: 1px solid #dee2e6;
	border-top: 1px solid #dee2e6 !important;
}

.col_crm_btn {
    width: 15% !important;
}

.col_pme_btn{
	width: 27% !important;
}
.col_pme_prop_chk {
	width: 4% !important;
}

#save_note_fb .note{
	width: 231px;
}

#btn_add_prop_div{
	margin: 5px 0 0 0;
	display: none;
}
.swal-dup_prop{
	width:auto;
}

.jFaded{
	opacity: 0.5;
}
#match_btn_div button{
	margin-right: 10px;
}
.possible_match_hl_bgcolor{
	background-color: #ff748c !important;
}
.probable_match_hl_bgcolor{
	background-color: #ecdf38 !important;
}
#pme_button_div{
	margin-bottom: 6px;
}
#btnPossibleMatch,
#btnPossibleMatch:hover{
	background-color: #ff748c !important;
	border-color: #ff748c !important;
}

#btnProbable,
#btnProbable:hover{
	background-color: #ecdf38 !important;
	border-color: #ecdf38 !important;
}

.crm-table-col-6 {
	padding-right: 0px !important;
}
.pme-table-col-6 {
	padding-left: 0px !important;
}
.not_add_prop_red_warning_text{
	display:none;
}
#hide_archived_pme_prop_btn,
#show_archived_pme_prop_btn{
	display:none
}
.pme_archived_row .pmeAdd{
	color: white;
}
.pme_archived_row{
	background-color: #dc3545 !important;	
}
.pme_hidden_row{
	background-color: #e5eb3b !important;	
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
			<div id="crm_pme_table_div" class="row">

				<!-- CRM -->
				<div class="col-sm-6 crmClass">				

					<form action="<?php echo $uri; ?>">
						<div class="container-fluid">

							<div class="row">

								<div class="col-sm-5">
									<fieldset class="form-group">
										<label class="form-label semibold" for="exampleInput">Select Agency</label>
										<div class="btn-group">
											<select id="agency_filter" name="agency_filter" class="form-control">
												<option value="0">-- Select --</option>
												<?php 
												foreach ($agenList->result_array() as $row) { ?>
													<option data-no_bulk_match="<?php echo $row['no_bulk_match'] ?>" value="<?=$row['agency_id']?>" <?=isset($selected) ? ($row['agency_id'] == $selected) ? 'selected' : '' : ''?>><?php echo ( ( $row['upc_count'] > 0 )?"({$row['upc_count']}) ":null ).$row['agency_name']." ". ( ( $row['no_bulk_match'] > 0 )?"(Shared) ":null );  ?></option>
												<?php
												}
												?>
											</select>					
										</div>
									</fieldset>
								</div>

								<div class="col-sm-5">
									<img src="/images/logo_login.png" class="company_logo sats_logo" />
								</div>

								<div class="col-sm-2">
									<label class="form-label semibold">&nbsp;</label>
									<button type="button" class="btn btn-primary btn-sm" id="btn_refresh">Refresh</button>																		
								</div>

								<!--
								<div class="col-sm-3">
									<label class="form-label semibold">&nbsp;</label>
									<button type="button" class="btn btn-primary btn-sm" id="btn_refresh">Refresh</button>	
									<a href="/cronjobs/pme_find_unmatched_properties" target="_blank">
										<button type="button" class="btn btn-primary btn-sm mt-2" id="btn_agency_ntp_count">Update Agency Count</button>
									</a>																	
								</div>
								-->
								
							</div>

						</div>
					</form>

					
				</div>

				
				
				
				<!-- PMe -->
				<div class="col-sm-6 pmeClass">							

					<div id="pme_main_div">

						<div class="container-fluid" id="pme_button_div">

							<div class="row">
								<div class="col-sm-8">
									<label class="form-label semibold">Find Match</label>
									<div class="btn-group" role="group" aria-label="Basic example">
										<button type="button" class="btn btn-match btn-primary btn-sm" id="btnMatch">1. Exact Match</button>
										<button type="button" class="btn btn-match btn-primary btn-sm" id="btnProbable">2. Probable Match</button>
										<button type="button" class="btn btn-match btn-primary btn-sm" id="btnPossibleMatch" style="z-index: 1; position: relative;">3. Possible Match</button>
										<!--button type="button" class="btn btn-secondary col-sm-4" id="btnClear">Clear</button>-->

										<button type="button" id="bulkCon" class="btn btn-success btn-sm" style="z-index: 1; position: relative;">			
											Connect
											<span class="label label-pill" id="numCon">0</span>
										</button>
									</div>
								</div>

								<div class="col-sm-4" style="z-index: 0; position: relative;">
									<img src="/images/third_party/propertytree.png" class="company_logo pme_logo" />
									<!--
									<div class="logo-container">
                                        <a href="https://www.mrisoftware.com/au/" id="logo" class="header-svg-logo">                                                                                
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3000 1000"><style type="text/css">.st0{fill:#5C5D5F;} .st1{fill:#BBD437;} .st2{fill:#044D66;}</style><path d="M59.6 892.3c0 7.1-1.9 12.8-5.6 17.1-3.8 4.4-9.3 7.2-16.7 8.4l24.5 35.5H47.9l-22.2-35h-15v35H0v-85.6h29.8c9.6 0 17 2.3 22.1 6.9 5.1 4.8 7.7 10.7 7.7 17.7zM29 911c6.4 0 11.3-1.6 14.6-4.7 3.3-3.1 5-7.3 5-12.4 0-5.1-1.6-9.2-4.8-12.2-3.2-3-7.9-4.6-13.9-4.6h-19V911H29zM91.4 867.8H144v9.2h-41.9v28.7h39.4v9.1h-39.4V944H144v9.4H91.4v-85.6zM195.3 867.8h13.1l28.7 85.6h-11.2l-7.3-21.8h-34l-7.3 21.8H166l29.3-85.6zm-7.9 55.4h28.4L201.7 881l-14.3 42.2zM273.3 944h38.1v9.4h-48.8v-85.6h10.7V944zM385.6 867.8h52.6v9.2h-41.9v28.7h39.5v9.1h-39.5V944h41.9v9.4h-52.6v-85.6zM516 888.7c-1.5-8.3-7.7-12.5-18.4-12.5-5.7 0-10.1 1.2-13.2 3.7-3.1 2.5-4.6 5.5-4.6 9s1 6.1 2.9 8c1.9 1.8 5 3.4 9.3 4.8l17.8 6.1c6.1 2.1 10.9 4.9 14.5 8.6 3.6 3.6 5.4 8.7 5.4 15.3s-2.8 12.1-8.4 16.6c-5.6 4.5-13.2 6.7-22.6 6.7-9.4 0-17.2-2.4-23.2-7.1-6-4.8-9.1-11.4-9.3-20.1h10.5c0 5.6 2 9.9 5.9 12.9 3.9 3 9 4.5 15.2 4.5s11.1-1.4 14.8-4.1c3.6-2.8 5.4-6 5.4-9.8 0-3.8-1.1-6.7-3.3-8.9-2.2-2.2-5.5-4-9.9-5.4l-15.1-5.6c-7.1-2.4-12.4-5.2-16.1-8.4-3.6-3.3-5.4-8-5.4-14.3 0-6.3 2.5-11.6 7.6-15.8 5.1-4.2 12.2-6.3 21.4-6.3s16.2 2 21 6.1c4.9 4 7.6 9.5 8.4 16.3H516zM545.2 867.8h66.5v9.2h-27.9v76.3h-10.7V877h-27.9v-9.2zM650.9 867.8H664l28.7 85.6h-11.2l-7.3-21.8h-34l-7.3 21.8h-11.3l29.3-85.6zm-7.9 55.4h28.4L657.3 881 643 923.2zM699.7 867.8h66.5v9.2h-27.9v76.3h-10.7V877h-27.9v-9.2zM788.8 867.8h52.6v9.2h-41.9v28.7H839v9.1h-39.4V944h41.9v9.4h-52.6v-85.6zM970.6 888.7c-1.5-8.3-7.7-12.5-18.4-12.5-5.7 0-10.1 1.2-13.2 3.7-3.1 2.5-4.6 5.5-4.6 9s1 6.1 2.9 8c1.9 1.8 5 3.4 9.3 4.8l17.8 6.1c6.1 2.1 10.9 4.9 14.5 8.6 3.6 3.6 5.4 8.7 5.4 15.3s-2.8 12.1-8.4 16.6c-5.6 4.5-13.2 6.7-22.6 6.7-9.4 0-17.2-2.4-23.2-7.1-6-4.8-9.1-11.4-9.3-20.1h10.5c0 5.6 2 9.9 5.9 12.9 3.9 3 9 4.5 15.2 4.5s11.1-1.4 14.8-4.1c3.6-2.8 5.4-6 5.4-9.8 0-3.8-1.1-6.7-3.3-8.9-2.2-2.2-5.5-4-9.9-5.4l-15.1-5.6c-7.1-2.4-12.4-5.2-16.1-8.4-3.6-3.3-5.4-8-5.4-14.3 0-6.3 2.5-11.6 7.6-15.8 5.1-4.2 12.2-6.3 21.4-6.3s16.2 2 21 6.1c4.9 4 7.6 9.5 8.4 16.3h-10.6zM1042.9 866.3c11.1 0 20 3.8 26.9 11.4 6.8 7.6 10.3 18.6 10.3 33.1s-3.4 25.4-10.2 32.9c-6.8 7.5-15.8 11.2-26.9 11.2s-20.1-3.8-26.9-11.4c-6.8-7.6-10.1-18.5-10.1-32.9 0-14.4 3.4-25.4 10.1-33 6.7-7.5 15.7-11.3 26.8-11.3zm0 9.7c-8 0-14.3 3-18.9 9-4.6 6-6.9 14.6-6.9 25.8 0 11.2 2.3 19.8 6.9 25.6 4.6 5.8 10.9 8.7 18.9 8.7 8 0 14.3-2.9 19-8.7 4.7-5.8 7-14.3 7-25.6 0-11.2-2.3-19.9-7-25.8-4.7-6-11-9-19-9zM1106 867.8h52v9.2h-41.2v28.4h38.4v9.3h-38.4v38.6H1106v-85.5zM1175.3 867.8h66.5v9.2h-27.9v76.3h-10.7V877h-27.9v-9.2zM1267.4 867.8l20.8 71.1 21.1-62.3h7.2l20.9 62.3 23.9-71.1h11.8l-30.2 85.6h-11.3l-18.6-54.8-19.3 54.8h-11.3l-27.1-85.6h12.1zM1411.8 867.8h13.1l28.7 85.6h-11.2l-7.3-21.8h-34l-7.3 21.8h-11.3l29.3-85.6zm-7.9 55.4h28.4l-14.1-42.2-14.3 42.2zM1535.1 892.3c0 7.1-1.9 12.8-5.6 17.1-3.8 4.4-9.3 7.2-16.7 8.4l24.5 35.5h-13.9l-22.2-35h-15v35h-10.7v-85.6h29.8c9.6 0 17 2.3 22.1 6.9 5.2 4.8 7.7 10.7 7.7 17.7zm-30.6 18.7c6.4 0 11.3-1.6 14.6-4.7 3.3-3.1 5-7.3 5-12.4 0-5.1-1.6-9.2-4.8-12.2-3.2-3-7.9-4.6-13.9-4.6h-19V911h18.1zM1561.5 867.8h52.6v9.2h-41.9v28.7h39.4v9.1h-39.4V944h41.9v9.4h-52.6v-85.6z" class="st0"></path><path d="M1320.5 157.5c-93.9 0-171.5 39.6-221.4 103.8l-.3-.5c-16.9-21.5-36.9-40.2-59.6-55.6v542.3h121V441.3c0-97.5 63-170.2 160.4-170.2 57.1 0 102.2 25.1 129.9 65.4V190c-36.9-20.8-80.7-32.5-130-32.5z" class="st1"></path><path d="M1005.4 445.6v301.9H883.7V441.3c0-97.5-63-170.3-160.4-170.3s-160.4 72.8-160.4 170.3v306.3H442.5V441.3c0-97.5-63-170.3-160.4-170.3-57.6 0-103.1 22.7-130.7 63.7-19.6 28.5-30.4 64.6-30.4 105v307.8H0V203.8c22.7 15.4 39.7 34.1 56.6 55.6l2 2.5c50-64.2 128.9-105.8 222.8-105.8 49.3 0 93 12.7 129.9 33.6 38.1 21.3 68.8 52.4 90.7 90.7 0 0 .5.8.7 1.2.2-.4.4-.8.6-1.2 43.1-75.1 120.1-122.7 220-122.7 166.1-.2 282.1 125 282.1 287.9z" class="st2"></path><circle cx="1550" cy="93.4" r="64.2" class="st1"></circle><path d="M1486 340.9v-140c22.1 15 127.3 81 127.3 188.6l.3 45.6-.7 312c-31.6-15-51.1-33.2-67.6-54.2 0 0-.4-.9-.6-.7-2.2-2.9-4.4-5.8-6.6-8.8-11.1-15.6-20.6-32.5-28.4-50.6-1.6-3.6-3-7.3-4.4-11-1.4-3.6-2.7-7.3-3.9-11-3.7-11.1-6.8-22.6-9.2-34.4-4.2-20.4-6.4-41.6-6.4-63.7l.2-171.8z" class="st2"></path></svg>
                                        </a>
                                    </div>
									-->
								</div>
							</div>

							
							<div class="row">
								<div class="col">
									<button type="button" class="btn btn-primary btn-sm mt-2" id="hide_archived_pme_prop_btn">Hide Deactivated</button> 
									<button type="button" class="btn btn-primary btn-sm mt-2" id="show_archived_pme_prop_btn">Show Deactivated</button> 
									<button type="button" class="btn btn-primary btn-sm mt-2" id="show_all_hidden_prop_btn">Show ALL hidden</button>		
									<button type="button" class="btn btn-primary btn-sm mt-2" id="cancel_show_all_hidden_prop_btn">Cancel Show ALL hidden</button>							
								</div>
							</div>
							

						</div>

						
					
					</div>
			
				</div>

			</div>
		</div>
	</section>
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12 col-md-6">&nbsp;</div>
			<div class="col-sm-12 col-md-6">
				<div class="not_add_prop_red_warning_text">
					<div class="text-red text-center alert alert-danger alert-icon alert-close alert-dismissible fade show" role="alert">
						<i class="font-icon font-icon-warning"></i>
						If no match found, do not add these properties to CRM.
					</div>
				</div>
			</div>
		</div>
	</div>
<div class="row">
	<div class="col-sm-6 crm-table-col-6">
		<!-- load PME here -->
		<div id="crm_table_div"></div>
	</div>
	<div class="col-sm-6 pme-table-col-6">
		<!-- load PME here -->
		<div id="pme_table_div"></div>
	</div>
</div>

</div>
<!-- Fancybox Start -->

<!-- About Page - START -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4>Bulk Connect</h4>
	<p>This page allows you to connect CRM properties to PME properties.</p>

</div>
<!-- About Page - END -->

<!-- PMe more details - START -->
<a href="javascript:;" id="pme_details_fb_link" class="fb_trigger" data-fancybox data-src="#pme_details_fb">Trigger the fancybox</a>							
<div id="pme_details_fb" class="fancybox" style="display:none;" >

	<h4>PropertyMe Property Details</h4>

	<table class="table table-striped table-bordered">
		<tbody id="pme_prop_details_tbl_fb">
			<tr>
				<th>Unit</th>
				<td class="pme_addr_unit_td"></td>
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
			<tr>
				<th>Country</th>
				<td class="pme_addr_country_td"></td>
			</tr>
			<tr>
				<th>Building Name</th>
				<td class="pme_addr_bldg_name_td"></td>
			</tr>
			<tr>
				<th>Mailbox Name</th>
				<td class="pme_addr_mailbox_td"></td>
			</tr>
			<tr>
				<th>Latitude</th>
				<td class="lat_td"></td>
			</tr>
			<tr>
				<th>Longitude</th>
				<td class="lng_td"></td>
			</tr>
			<tr>
				<th>Text</th>
				<td class="pme_addr_text_td"></td>
			</tr>
			<tr>
				<th>Reference</th>
				<td class="pme_addr_reference_td"></td>
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



<!-- Add Note - START -->
<a href="javascript:;" id="save_note_fb_link" class="fb_trigger" data-fancybox data-src="#save_note_fb">Trigger the fancybox</a>							
<div id="save_note_fb" class="fancybox" style="display:none;" >

	<h4>Save Note</h4>											
	<p>
		<textarea class="form-control note"></textarea>												
	</p>

	<input type="hidden" class="pnv_id" />
	<input type="hidden" class="sv_property_source" />
	<input type="hidden" class="sv_property_id" />
	<input type="hidden" class="sv_property_address" />
	<input type="hidden" class="sv_agency_id" />
	<input type="hidden" class="note_btn_class" />

	<p><button type="button" class="btn btn-primary btn_save_note">Save</button></p>

</div>
<!-- Add Note - END -->


<!-- Add Note - START -->
<a href="javascript:;" id="view_note_fb_link" class="fb_trigger" data-fancybox data-src="#view_note_fb">Trigger the fancybox</a>							
<div id="view_note_fb" class="fancybox" style="display:none;" >

	<h4>View Note</h4>											
	<p class="pnv_notes"></p>

</div>
<!-- Add Note - END -->



<!-- Fancybox END -->



<script type="text/javascript">
var bar = new ldBar(".jlbar", {
	"value": 0 
});

// set loading bar
function setLbar(i){	
	bar.set(i);	
}


// remember agency 
function remember_agency(){

	var agency_id = jQuery("#agency_filter").val();
	Cookies.set('bulk_connect_rem_agen', agency_id);

}

function match_counter(clear){

	// match counter
	if( clear == 1 ){ // clear
		var countSelec = 0;
	}else{
		var countSelec = jQuery(".chk_prop:checked").length;
	}
	
	if (countSelec > 0) {
		$("#bulkCon").addClass("btn-success")
		$("#numCon").addClass("label-danger")
		$("#bulkCon").show();
	}else{
		$("#bulkCon").removeClass("btn-success")
		$("#numCon").removeClass("label-danger")
		$("#bulkCon").hide();
	}
	
	$("#numCon").html(countSelec)	

}

function show_hide_add_propery_btn(){

	var prop_count = jQuery(".pme_prop_chk:checked").length;	
	if( prop_count > 0 ){
		//jQuery("#btn_add_prop").show();
		jQuery("#btn_add_prop_div").show();
	}else{
		//jQuery("#btn_add_prop").hide();
		jQuery("#btn_add_prop_div").hide();
	}

} 

function save_note(pnv_id,property_source,property_id,property_address,agency_id,note,note_btn_class){

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

		if( property_source == 1 ){ // crm
			jQuery("."+note_btn_class).addClass("jFaded");
			jQuery("."+note_btn_class).prop('disabled', true);
			jQuery("."+note_btn_class).html('Pending Verification');
		}else if( property_source == 2 ){ // Pme	

			// change button to view		
			jQuery("."+note_btn_class).html('View');	
			jQuery("."+note_btn_class).removeClass('btn_note');	
			jQuery("."+note_btn_class).parents("td:first").find(".pnv_id").val(ret);
			jQuery("."+note_btn_class).addClass("jFaded");
			jQuery("."+note_btn_class).addClass('view_note_btn');	

		}
		
		$.fancybox.close();				
					

	});	

}
 
$(document).ready(function() {


	// hide deactivated toggle
	var hide_pme_archived_prop = Cookies.get('hide_pme_archived_prop');
	if( hide_pme_archived_prop == 1 ){
		jQuery("#hide_archived_pme_prop_btn").hide();
		jQuery("#show_archived_pme_prop_btn").show();
	}else{
		jQuery("#hide_archived_pme_prop_btn").show();
		jQuery("#show_archived_pme_prop_btn").hide();
	}

	// show all hidden api property toggle
	var show_all_hidden_prop = Cookies.get('show_all_hidden_prop_pme');
	if( show_all_hidden_prop == 1 ){
		jQuery("#show_all_hidden_prop_btn").hide();
		jQuery("#cancel_show_all_hidden_prop_btn").show();
	}else{
		jQuery("#show_all_hidden_prop_btn").show();
		jQuery("#cancel_show_all_hidden_prop_btn").hide();
	}

	// PMe property check all
	jQuery("#pme_table_div").on("change","#pme_prop_chk_all",function(){

		var is_checked = jQuery(this).prop("checked");
		
		if( is_checked == true ){
			jQuery(".pme_prop_chk:visible").prop("checked",true);
		}else{
			jQuery(".pme_prop_chk:visible").prop("checked",false);
		}

		show_hide_add_propery_btn();
		

	});

	// PMe property individual checkbox
	jQuery("#pme_table_div").on("change",".pme_prop_chk",function(){
		show_hide_add_propery_btn();
	});


	function clear_notes_field(){

		jQuery("#save_note_fb .sv_property_source").val('');	
		jQuery("#save_note_fb .sv_property_id").val('');	
		jQuery("#save_note_fb .sv_property_address").val('');	
		jQuery("#save_note_fb .sv_agency_id").val('');	
		jQuery("#save_note_fb .note").val();	
		
		jQuery("#save_note_fb .note_btn_class").val('');	
	}

	// add crm note
	jQuery("#crm_table_div").on("click",".btn_note",function(){

		// clear notes first
		clear_notes_field();

		// prefill data
		var property_source = 1; // crm
		
		var property_id = jQuery(this).parents("td:first").find(".crm_prop_id").val();
		var property_address = jQuery(this).parents("td:first").find(".crm_full_address").val();
		var agency_id = jQuery("#agency_filter").val();	
		var note = "Contact agency to verfiy NLM";

		var note_btn_class = jQuery(this).parents("td:first").find(".note_btn_class").val();

		jQuery("#save_note_fb .sv_property_source").val(property_source);	
		jQuery("#save_note_fb .sv_property_id").val(property_id);	
		jQuery("#save_note_fb .sv_property_address").val(property_address);	
		jQuery("#save_note_fb .sv_agency_id").val(agency_id);	
		jQuery("#save_note_fb .note").val(note);

		jQuery("#save_note_fb .note_btn_class").val(note_btn_class);		

		// pop-up lightbox
		jQuery("#save_note_fb_link").click();

	});

	// add crm note
	jQuery("#pme_table_div").on("click",".btn_note",function(){

		// clear notes first
		clear_notes_field();

		// prefill data
		var property_source = 2; // pme

		var property_id = jQuery(this).parents("td:first").find(".pme_prop_id").val();
		var property_address = jQuery(this).parents("td:first").find(".pme_full_address").val();
		var is_archived = jQuery(this).parents("td:first").find(".is_archived").val();
		var agency_id = jQuery("#agency_filter").val();	
		var note = "";

		var note_btn_class = jQuery(this).parents("td:first").find(".note_btn_class").val();

		jQuery("#save_note_fb .sv_property_source").val(property_source);	
		jQuery("#save_note_fb .sv_property_id").val(property_id);	
		jQuery("#save_note_fb .sv_property_address").val(property_address);	
		jQuery("#save_note_fb .sv_agency_id").val(agency_id);	

		if( is_archived == 1 ){
			note = "Deactivated";
		}
		jQuery("#save_note_fb .note").val(note);

		jQuery("#save_note_fb .note_btn_class").val(note_btn_class);		

		// pop-up lightbox
		jQuery("#save_note_fb_link").click();

	});

	
	// view pnv note
	jQuery("#pme_table_div").on("click",".view_note_btn",function(){
		
		var pnv_id = jQuery(this).parents("td:first").find(".pnv_id").val();

		jQuery.ajax({
			url: "/property_me/ajax_get_pnv_note",
			type: 'POST',
			data: { 
				'pnv_id': pnv_id
			}
		}).done(function( ret ){
			
			jQuery('#load-screen').hide(); 	
							
			// pop-up lightbox
			jQuery("#view_note_fb .pnv_notes").html(ret);	
			jQuery("#view_note_fb_link").click();						
						

		});	

		

	});
	

	

	// save note
	jQuery(".btn_save_note").click(function(){
		
		var pnv_id = jQuery("#save_note_fb .pnv_id").val();
		var property_source = jQuery("#save_note_fb .sv_property_source").val();
		var property_id = jQuery("#save_note_fb .sv_property_id").val();
		var property_address = jQuery("#save_note_fb .sv_property_address").val();
		var agency_id = jQuery("#save_note_fb .sv_agency_id").val();
		var note = jQuery("#save_note_fb .note").val();

		var note_btn_class = jQuery("#save_note_fb .note_btn_class").val();
		
		save_note(pnv_id,property_source,property_id,property_address,agency_id,note,note_btn_class);
		

	});


	// verify NLM
	jQuery("#crm_table_div").on("click",".verify_nlm_btn",function(){
			
		var obj = jQuery(this);
		var property_source = 1; // crm
		var property_id = obj.parents("td:first").find(".crm_prop_id").val();
		var property_address = obj.parents("td:first").find(".crm_full_address").val();
		var agency_id =  jQuery("#agency_filter").val();	
		var note = "Contact agency to verfiy NLM";

		var note_btn_class = obj.parents("td:first").find(".note_btn_class").val();
		console.log("note_btn_class: "+note_btn_class);
		
		save_note('',property_source,property_id,property_address,agency_id,note,note_btn_class);		

	});
	

	// refresh button
	jQuery("#btn_refresh").click(function(){
		location.reload();
	});


	// search both table script
	jQuery("#crm_table_div").on("keyup","#crmProp_filter input",function(){

		var search = jQuery(this).val();
		pmeTable.search(search).draw();

	});

	jQuery("#pme_table_div").on("keyup","#pmeProp_filter input",function(){

		var search = jQuery(this).val();
		crmTable.search(search).draw();

	});

	// PMe property details
	jQuery("#pme_table_div").on("click",".pmeAdd",function(){		

		// pre fill PMe property data
		var pme_addr_unit = jQuery(this).parents("tr:first").find(".pme_addr_unit").val();
		var pme_addr_number = jQuery(this).parents("tr:first").find(".pme_addr_number").val();
		var pme_addr_street = jQuery(this).parents("tr:first").find(".pme_addr_street").val();
		var pme_addr_suburb = jQuery(this).parents("tr:first").find(".pme_addr_suburb").val();
		var pme_addr_postalcode = jQuery(this).parents("tr:first").find(".pme_addr_postalcode").val();
		var pme_addr_state = jQuery(this).parents("tr:first").find(".pme_addr_state").val();

		var pme_addr_country = jQuery(this).parents("tr:first").find(".pme_addr_country").val();
		var pme_addr_bldg_name = jQuery(this).parents("tr:first").find(".pme_addr_bldg_name").val();
		var pme_addr_mailbox = jQuery(this).parents("tr:first").find(".pme_addr_mailbox").val();
		var lat = jQuery(this).parents("tr:first").find(".lat").val();
		var lng = jQuery(this).parents("tr:first").find(".lng").val();
		var pme_addr_text = jQuery(this).parents("tr:first").find(".pme_addr_text").val();
		var pme_addr_reference = jQuery(this).parents("tr:first").find(".pme_addr_reference").val();
		var tenants_contact_id = jQuery(this).parents("tr:first").find(".tenants_contact_id").val();
		var agency_id = jQuery("#agency_filter").val();
		
		var pme_table_fb = jQuery("#pme_prop_details_tbl_fb");		

		// prefill property details
		pme_table_fb.find(".pme_addr_unit_td").html(pme_addr_unit);
		pme_table_fb.find(".pme_addr_number_td").html(pme_addr_number);
		pme_table_fb.find(".pme_addr_street_td").html(pme_addr_street);
		pme_table_fb.find(".pme_addr_suburb_td").html(pme_addr_suburb);
		pme_table_fb.find(".pme_addr_postalcode_td").html(pme_addr_postalcode);
		pme_table_fb.find(".pme_addr_state_td").html(pme_addr_state);

		pme_table_fb.find(".pme_addr_country_td").html(pme_addr_country);
		pme_table_fb.find(".pme_addr_bldg_name_td").html(pme_addr_bldg_name);
		pme_table_fb.find(".pme_addr_mailbox_td").html(pme_addr_mailbox);
		pme_table_fb.find(".lat_td").html(lat);
		pme_table_fb.find(".lng_td").html(lng);
		pme_table_fb.find(".pme_addr_text_td").html(pme_addr_text);
		pme_table_fb.find(".pme_addr_reference_td").html(pme_addr_reference);


		// get tenants
		if( tenants_contact_id != '' ){
			
			jQuery('#load-screen').show(); 		
			jQuery.ajax({
				url: "/property_me/ajax_get_pme_tenants",
				type: 'POST',
				data: { 
					'agency_id': agency_id,
					'tenants_contact_id': tenants_contact_id

				},
				dataType: 'json'
			}).done(function( ret ){
				
				jQuery('#load-screen').hide(); 

				var contact_persons = ret.ContactPersons;
				var tenant_row_str = '';

				for( var i=0; i < contact_persons.length; i++ ){
					tenant_row_str += 
					'<tr>'+
						'<td>'+contact_persons[i].FirstName+'</td>'+
						'<td>'+contact_persons[i].LastName+'</td>'+
						'<td>'+contact_persons[i].CellPhone+'</td>'+
						'<td>'+contact_persons[i].HomePhone+'</td>'+
						'<td>'+contact_persons[i].Email+'</td>'+
					'</tr>';			
				}

				// populate tenants
				jQuery(".pme_prop_tenants_tbl_fb").html(''); // clear first
				jQuery(".pme_prop_tenants_tbl_fb").append(tenant_row_str);					

			});	
			
		}else{
			// clear tenants
			jQuery(".pme_prop_tenants_tbl_fb").html('');
		}		


		// pop-up lightbox
		jQuery("#pme_details_fb_link").click();

		

	});


	// bulk add property function
	jQuery("#pme_table_div").on("click","#btn_add_prop",function(){

		var prop_count = jQuery(".pme_prop_chk:checked").length;

		// confirm add PMe property on crm
		swal({
			title: "Are you sure?",
			text: "You are about to add "+prop_count+" selected properties.",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-success",
			confirmButtonText: "Yes, Continue",
			cancelButtonText: "No, Cancel!",
			cancelButtonClass: "btn-danger",
			closeOnConfirm: true,
			showLoaderOnConfirm: true,
			closeOnCancel: true
		},
		function(isConfirm) {
			
			if (isConfirm) { // yes		
								
				var agency_id =  jQuery("#agency_filter").val();
				var pme_prop_arr = [];
				var dup_html = '';

				// loop through PMe properties
				jQuery(".pme_prop_chk:checked").each(function(){

					var obj = jQuery(this);	
					var row = 	obj.parents("tr:first");		
					var pme_full_address = row.find(".pme_full_address").val();
					var pme_addr_unit = row.find(".pme_addr_unit").val();
					var pme_addr_number = row.find(".pme_addr_number").val();	
					var pme_addr_street = row.find(".pme_addr_street").val();
					var pme_addr_suburb = row.find(".pme_addr_suburb").val();
					var pme_addr_state = row.find(".pme_addr_state").val();
					var pme_addr_postalcode = row.find(".pme_addr_postalcode").val();

					var lat = row.find(".lat").val();
					var lng = row.find(".lng").val();

					var api_prop_id = row.find(".api_prop_id").val();
					var key_number = row.find(".key_number").val();
					var tenants_contact_id = row.find(".tenants_contact_id").val();	
					var owner_contact_id = row.find(".owner_contact_id").val();

					var json_data = { 
						'pme_full_address': pme_full_address, 
						'pme_addr_unit': pme_addr_unit,
						'pme_addr_number': pme_addr_number,
						'pme_addr_street': pme_addr_street,
						'pme_addr_suburb': pme_addr_suburb,
						'pme_addr_state': pme_addr_state,
						'pme_addr_postalcode': pme_addr_postalcode,
						'lat': lat,
						'lng': lng,
						'api_prop_id': api_prop_id,
						'key_number': key_number,
						'tenants_contact_id': tenants_contact_id,
						'owner_contact_id': owner_contact_id,
					}
					var json_str = JSON.stringify(json_data);
					
					pme_prop_arr.push(json_str);											

				});	

				if( pme_prop_arr.length > 0 ){

					// ajax add property
					jQuery('#load-screen').show(); 
					jQuery.ajax({
						url: "/property_tree/bulk_connect_add_property",
						type: 'POST',
						data: { 
							'agency_id': agency_id,
							'pme_prop_arr': pme_prop_arr
						},
						dataType: 'json'
					}).done(function( ret ){
						
						jQuery('#load-screen').hide();
						
						if( ret.length == 0 ){
							
							swal({
								title: "Success!",
								text: "Add Property Successful",
								type: "success",
								confirmButtonClass: "btn-success",
								showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
								timer: <?php echo $this->config->item('timer') ?>
							});

							// remove rows via loop
							jQuery(".pme_prop_chk:checked").each(function(){

								var obj2 = jQuery(this);	
								var row2 = 	obj2.parents("tr:first");
								// remove rows
								pmeTable.rows(row2).remove().draw();

							});
							

						}else{
							
							if( ret.length > 0 ){

								// swal html markup
								dup_html = '<div class="dup_prop_div">Properties that could be added have been added.<br />'+ 
								'Some properties were skipped due to being duplicates in the system <br /><br />'+
								'<ul>';

								for( var i=0; i<ret.length; i++ ){
									dup_html += ''+
									'<li>'+
										'<a href="/property_me/property/'+ret[i].dup_property_id+'/'+ret[i].dup_agency_id+'" target="_blank">'+
											ret[i].dup_property_address+										
										'</a>'+
										' On <a href="/agency/view_agency_details/'+ret[i].dup_agency_id+'">'+ret[i].dup_agency_name+'</a>'+
									'</li>';
								}
								dup_html +='</ul></div>';							
								
								// swal
								swal({
									html:true,
									title: "Success!",
									text: dup_html,
									type: "success",
									confirmButtonClass: "btn-primary",
									customClass: 'swal-dup_prop'						
								});

								// remove rows
								// loop selected properties
								var remove_rows = [];
								jQuery(".pme_prop_chk:checked").each(function(){

									var obj2 = jQuery(this);	
									var row2 = 	obj2.parents("tr:first");
									var pme_prop_id2 = row2.find(".pme_prop_id").val();
									var has_duplicate = false;
																	
									// loop returned duplicate properties
									for( var i=0; i<ret.length; i++ ){
										if( pme_prop_id2 == ret[i].pme_prop_id ){ // if duplicate
											has_duplicate = true;
										}									
									}
									
									if( has_duplicate == false ){ // add row that has no duplicate for removal
										remove_rows.push(row2);
									}


								});

								for( var i=0; i<remove_rows.length; i++ ){							
									// remove rows
									pmeTable.rows(remove_rows[i]).remove().draw();
								}


							}														
							
						}									

					});	

				}						

				
			}
			
		});		


	});



	// invididual add property function
	jQuery("#pme_table_div").on("click",".btn_add_prop_indiv",function(){

		var agency_id =  jQuery("#agency_filter").val();
		var pme_prop_arr = [];
		var crm_prop_id_arr2 = [];
		var pme_prop_id_arr2 = [];					

		var obj = jQuery(this);	
		var row = 	obj.parents("tr:first");		
		var pme_full_address = row.find(".pme_full_address").val();
		var pme_addr_unit = row.find(".pme_addr_unit").val();
		var pme_addr_number = row.find(".pme_addr_number").val();	
		var pme_addr_street = row.find(".pme_addr_street").val();
		var pme_addr_suburb = row.find(".pme_addr_suburb").val();
		var pme_addr_state = row.find(".pme_addr_state").val();
		var pme_addr_postalcode = row.find(".pme_addr_postalcode").val();

		var lat = row.find(".lat").val();
		var lng = row.find(".lng").val();

		var api_prop_id = row.find(".api_prop_id").val();
		var pme_prop_id = row.find(".pme_prop_id").val();
		var key_number = row.find(".key_number").val();
		var tenants_contact_id = row.find(".tenants_contact_id").val();	
		var owner_contact_id = row.find(".owner_contact_id").val();

		var note_btn_class = row.find(".note_btn_class").val();	

		var json_data = { 
			'pme_full_address': pme_full_address, 
			'pme_addr_unit': pme_addr_unit,
			'pme_addr_number': pme_addr_number,
			'pme_addr_street': pme_addr_street,
			'pme_addr_suburb': pme_addr_suburb,
			'pme_addr_state': pme_addr_state,
			'pme_addr_postalcode': pme_addr_postalcode,
			'lat': lat,
			'lng': lng,
			'api_prop_id': api_prop_id,
			'key_number': key_number,
			'tenants_contact_id': tenants_contact_id,
			'owner_contact_id': owner_contact_id,
		}
		var json_str = JSON.stringify(json_data);
		
		pme_prop_arr.push(json_str);	

		if( pme_prop_arr.length > 0 ){

			// ajax add property
			jQuery('#load-screen').show(); 
			jQuery.ajax({
				url: "/property_tree/bulk_connect_add_property",
				type: 'POST',
				data: { 
					'agency_id': agency_id,
					'pme_prop_arr': pme_prop_arr,
					'connect_deleted_nlm_prop' : 1
				},
				dataType: 'json'
			}).done(function( ret ){

				jQuery('#load-screen').hide();				
											
				if( ret.length == 0 ){
							
					swal({
						title: "Success!",
						text: "Add Property Successful",
						type: "success",
						confirmButtonClass: "btn-success",
						showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
						timer: <?php echo $this->config->item('timer') ?>
					});

					// remove rows
					pmeTable.rows(row).remove().draw()

				}else{
					
					// duplicate crm property
					var crm_prop_id = ret[0].dup_property_id;

					// if duplicate property is deleted and under the same agency
					if( ret[0].dup_prop_deleted == 1 && ret[0].dup_agency_id == agency_id ){

						// swal html markup
						dup_html = ''+
						'<ul>';
						dup_html += ''+
							'<li>'+
								'<a href="<?php echo $this->config->item('crm_link'); ?>/view_property_details.php?id='+ret[0].dup_property_id+'" target="_blank">'+
									ret[0].dup_property_address+										
								'</a>'+								
							'</li>';
						dup_html +='</ul><br />'+
						'This property already exists with this agency and is deleted/NLM. Would you like to connect it anyways?'+				
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

									crm_prop_id_arr2.push(crm_prop_id); // crm property
									pme_prop_id_arr2.push(api_prop_id);	// pme property				  									
									
									// connect them
									$('#load-screen').show(); 
									$.ajax({
										//url: "/console/bulk_connect_all",
										url: "/property_tree/bulk_connect_all",
										type: 'POST',
										data: { 
											'agency_id': <?=isset($selected) ? $selected : "1"?>,
											'crmArr' : crm_prop_id_arr2,
											'pmeArr' : pme_prop_id_arr2
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

											// remove rows
											pmeTable.rows(row).remove().draw()

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

					}else{ // other duplicate scenario will use note

						if( ret.length > 0 ){

							var this_agency = '';
							var sel_agency_id =  jQuery("#agency_filter").val();
							var dup_html = '';
							var crm_prop_id = ret[0].dup_property_id;

							if( ret[0].dup_prop_deleted == 1 ){ //new changes > move nlm property to current if already nlm from other agency
								
								crm_prop_id_arr2.push(crm_prop_id);
								pme_prop_id_arr2.push(api_prop_id);	

								dup_html = '<div class="dup_prop_div">'+
								'<ul>';
								dup_html += ''+
									'<li>'+
										'<a href="/palace/property/'+ret[0].dup_property_id+'/'+ret[0].dup_agency_id+'" target="_blank">'+
											ret[0].dup_property_address+										
										'</a>'+								
									'</li>';
								if( ret[0].dup_agency_id == agency_id ){
									this_agency = 'this agency';
								}else{
									this_agency = '<a href="<?php echo base_url(); ?>agency/view_agency_details/'+ret[0].dup_agency_id+'">'+ret[0].dup_agency_name+'</a>';
								}

								dup_html +='</ul><br />'+
								'This property is currently NLM with '+this_agency+'<br /><br />'+							
								'Click Add to continue.'+							
								'</div>';
								
								swal({
									html:true,
									title: "Warning!",
									text: dup_html,
									type: "warning",						
									customClass: 'swal-dup_prop',

									showCancelButton: true,
									confirmButtonClass: "btn-primary",
									confirmButtonText: "Add",
									cancelButtonText: "Cancel!",
									cancelButtonClass: "btn-danger",
									closeOnConfirm: true,
									showLoaderOnConfirm: true,
									closeOnCancel: true							
								},
									function(isConfirm) {
										if (isConfirm) {		

											//add property via ajax
											$('#load-screen').show(); 
											$.ajax({
												url: "/property_tree/ajax_bulk_move_nlm_property",
												type: 'POST',
												data: { 
													'old_agency_id': ret[0].dup_agency_id,
													'sel_agency_id': sel_agency_id,
													'property_id_arr' : crm_prop_id_arr2,
													'pmeArr' : pme_prop_id_arr2
												}
											}).done(function( ret ){
												ret = JSON.parse(ret);
												$('#load-screen').hide(); 

												if( ret.status===true ){

													swal({
														title: "Success!",
														text: "The properties are now moved/added and reactivated.",
														type: "success",
														confirmButtonClass: "btn-success",
														showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
														timer: <?php echo $this->config->item('timer') ?>
													});

													location.reload();

												}else{

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
								
							}else{

								// add textarea notes
								dup_html = '<div class="dup_prop_div">'+
								'<ul>';
								dup_html += ''+
									'<li>'+
										'<a href="<?php echo $this->config->item('crm_link'); ?>/view_property_details.php?id='+ret[0].dup_property_id+'" target="_blank">'+
											ret[0].dup_property_address+										
										'</a>'+									
									'</li>';

								if( ret[0].dup_agency_id == agency_id ){
									this_agency = 'this agency';
								}else{
									this_agency = '<a href="/agency/view_agency_details/'+ret[0].dup_agency_id+'">'+ret[0].dup_agency_name+'</a>';
								}

								dup_html +='</ul><br />'+
								'This property already exists with '+this_agency+'<br /><br />'+														
								'</div>';

								// swal save note
								swal({
									html:true,
									title: "Warning!",
									text: dup_html,
									type: "warning",						
									customClass: 'swal-dup_prop',
									
									confirmButtonClass: "btn-primary",								
									closeOnConfirm: true,
									showLoaderOnConfirm: true												
								});

							}													

						}						

					}					
					
					
				}									

			});	

		}

	});

	

	// auto select remembered agency script
	var remember_agency_id = parseInt(Cookies.get('bulk_connect_rem_agen'));
	if(  remember_agency_id > 0  ){

		setTimeout(function(){ 			             
			jQuery("#agency_filter").val(remember_agency_id).change();                              
		}, 1000); 

	}    


	jQuery("#crm_table_div").on("click",".crmAdd",function(){

		var crm_prop_link = jQuery(this).attr("data-crm_prop_link");		
		window.open(crm_prop_link, '_blank');

	});

	
	jQuery("#crm_table_div").on("change",".chk_prop",function(){

		var is_ticked = jQuery(this).prop("checked");
		var row = jQuery(this).parents("tr:first");
		var row_index = row.index()+1;
		var pme_row = jQuery("#pme_table_div tr");

		if( is_ticked == true ){

			// crm
			row.addClass("match_hl_bgcolor");
			row.find(".match_arrow").show();

			// pme
			pme_row.eq(row_index).addClass("match_hl_bgcolor");
			pme_row.eq(row_index).find(".match_arrow").show();

		}else{

			// crm
			row.removeClass("match_hl_bgcolor");
			row.find(".match_arrow").hide();

			// pme
			pme_row.eq(row_index).removeClass("match_hl_bgcolor");
			pme_row.eq(row_index).find(".match_arrow").hide();

		}

		// match_counter
		match_counter();
		

	});

	// crm and Pme list ajax load
	jQuery("#agency_filter").change(function(){

		var country_id = "<?php echo COUNTRY ?>";
		var no_bulk_match = $(this).find(':selected').attr('data-no_bulk_match');

		// match_counter
		match_counter(1);

		// remember agency using cookie
		remember_agency();

		var agency_id = parseInt(jQuery(this).val());

		if( agency_id > 0 ){

			// get crm list
			jQuery('#load-screen').show(); 
			jQuery.ajax({
				url: "/property_tree/ajax_bulk_connect_get_crm_list",
				type: 'POST',
				data: { 
					'agency_id': agency_id
				}
			}).done(function( crm_ret ){

				// load crm properties
				jQuery("#crm_table_div").html(crm_ret);	

				// get PMe properties				
				jQuery('#load-screen').show(); 
				jQuery.ajax({
					url: "/property_tree/ajax_bulk_connect_get_api_list",
					type: 'POST',
					data: { 
						'agency_id': agency_id,
						'hide_pme_archived_prop': hide_pme_archived_prop,
						'show_all_hidden_prop':show_all_hidden_prop
					}
				}).done(function( pme_ret ){

					// load PMe properties
					jQuery('#load-screen').hide(); 
					jQuery("#pme_table_div").html(pme_ret);
					jQuery("#pme_main_div").show();

					//added by Gherx > show warning red text in div if all Haris, Elite and LJ Hooker Trinity Beach agency selected (AU agency only)
					/*
					if(country_id==1){ //AU Only
						var agency_array = [1961,6203,6974,5718,6386];
						if(jQuery.inArray(agency_id, agency_array) !== -1){
							$('.not_add_prop_red_warning_text').show();
						}else{
							$('.not_add_prop_red_warning_text').hide();
						}
					}
					*/
					if(no_bulk_match==1){
						$('.not_add_prop_red_warning_text').show();
					}else{
						$('.not_add_prop_red_warning_text').hide();
					}
					//add by Gherx end >

				});	


			});	

			

		}

	});


	// Exact Match
	$("#btnMatch").click(function() {

        console.log("EXACT MATCH:");

		//jQuery('#load-screen').show(); 
		jQuery(".loading-bar-div").show();

		var crm_total = crmTable.rows().count();
		var pme_total = pmeTable.rows().count();
		var total_prop = crm_total*pme_total;


		$("#pmeProp tr").each(function() {
		   if ($(this).hasClass('possible_match_hl_bgcolor') || $(this).hasClass('probable_match_hl_bgcolor')) {
		   		$(this).find('td:eq(2)').html("0"); 
				pmeTable.row($(this)).invalidate();
		   		$(this).removeClass('possible_match_hl_bgcolor');
		   		$(this).removeClass('probable_match_hl_bgcolor');
		   		$(this).removeClass('match_hl_bgcolor');
		   }
		});
		$("#crmProp tr").each(function() {
		   if ($(this).hasClass('possible_match_hl_bgcolor') || $(this).hasClass('probable_match_hl_bgcolor')) {
		   		$(this).find('td:eq(3)').html("0"); 
				crmTable.row($(this)).invalidate();
		   		$(this).removeClass('possible_match_hl_bgcolor');
		   		$(this).removeClass('probable_match_hl_bgcolor');
		   		$(this).removeClass('match_hl_bgcolor');
		   }
		});

		setTimeout(function(){ 

			// clear all selection
			jQuery(".chk_prop").prop("checked",false);
			jQuery(".pme_prop_chk").prop("checked",false);

			// loop CRM table
			var match_index = 1;
			var match_limit = 50;
			var loop_count = 1;
			var percent = 0;
			var pme_matched_prop_arr = [];

			crmTable.rows().every(function(index, element) {	

				var crm_node = $(this.node()); // object
				var data = $(this.data()); // row column

				// crm
				var crmData = data[1];			
				//var desired1 = crmData.replace(/[^\w\s]/gi, '')
				//var prep = desired1.replace(/,/g, " ").trim();
				//var newstrCrmData = prep.replace(/ +?/g, '');

				var prep = crmData.replace(/,/g, ''); // remove comma
				var prep2 = prep.replace(/  +/g, ''); // remove space
				var prep3 = clearStreetName(prep2).trim();
				var newstrCrmData_rep = prep3;

				// loop PMe table
				pmeTable.rows().every(function(index2, element2) {					

					var pme_node = $(this.node());	// object
					var data2 = $(this.data()); // row column
					
					// pme
					var pmeData = data2[0];
					//var desired2 = pmeData.replace(/[^\w\s]/gi, '')
					//var prep = desired2.replace(/,/g, " ").trim();
					//var newstrPmeData = prep.replace(/ +?/g, '');	
					
					var prep = pmeData.replace(/,/gi, ''); // remove comma
					var prep2 = prep.replace(/  +/g, ''); // remove space
					var prep3 = clearStreetName(prep2).trim();
					var newstrPmeData_rep = prep3;

					// regex remove characters that are not alpha numeric
					//var newstrCrmData_rep = clearStreetName(newstrCrmData).replace(/\s+/g,' ').trim().toLowerCase();
					//var newstrPmeData_rep = clearStreetName(newstrPmeData).replace(/\s+/g,' ').trim().toLowerCase();

                    console.log("newstrCrmData_rep: "+newstrCrmData_rep+" - newstrPmeData_rep: "+newstrPmeData_rep);

					// match					
					if ( ( newstrCrmData_rep != '' &&  newstrPmeData_rep != '' ) && ( newstrCrmData_rep == newstrPmeData_rep ) && pme_node.hasClass( "match_hl_bgcolor" ) == false && match_index <= match_limit ) {

						if( pme_matched_prop_arr.includes(newstrPmeData_rep) == false ){ // avoid duplicates

							// tick checkbox
							crm_node.find(".chk_prop").prop("checked",true);
							// show match arrow
							crm_node.find(".match_arrow").css('display','inline'); // .show() wont work don't know why :(

							// add row higlight
							crm_node.addClass("match_hl_bgcolor");
							pme_node.addClass("match_hl_bgcolor");	
							pme_matched_prop_arr.push(newstrPmeData_rep);										

							// invalidate() - important to run or sort will not fuction after editing column
							crm_node.find(".sort_index").html(match_index);
							crmTable.row(crm_node).invalidate(); 

							pme_node.find(".sort_index").html(match_index);
							pmeTable.row(pme_node).invalidate();
							
							match_index++;

						}						

					}		

					// get percent
					percent = Math.round(loop_count / total_prop * 100);
					if( percent % 10 == 0 ){									
						setLbar(percent);
					}					
					loop_count++;			
				

				});						

			});

			//jQuery('#load-screen').hide(); 	
			jQuery(".loading-bar-div").hide();

			// sort to push match items to top
			crmTable.order([[ 3, 'desc' ], [ 1, 'asc' ]]).draw();
			pmeTable.order([[ 2, 'desc' ], [ 0, 'asc' ]]).draw();


			// match counter
			match_counter();

			// match counter message
			var countSelec = jQuery(".chk_prop:checked").length;
			if (countSelec <= 0) {
				swal({
					title: "Info!",
					text: "Found 0 Match.",
					type: "info",
					confirmButtonClass: "btn-primary"
				});
			}


		}, 1000);


		
			
	});


	// Probable Match
	$("#btnProbable").click(function() {

		console.log("Probable MATCH:");

		//jQuery('#load-screen').show(); 
		jQuery(".loading-bar-div").show();

		var crm_total = crmTable.rows().count();
		var pme_total = pmeTable.rows().count();
		var total_prop = crm_total*pme_total;

		setTimeout(function(){ 

			// clear all selection
			jQuery(".chk_prop").prop("checked",false);
			jQuery(".match_arrow").css('display','none');

			$("#pmeProp tr").each(function() {
			   if ($(this).hasClass('possible_match_hl_bgcolor') || $(this).hasClass('match_hl_bgcolor')) {
			   		$(this).find('td:eq(2)').html("0"); 
					pmeTable.row($(this)).invalidate();
			   		$(this).removeClass('possible_match_hl_bgcolor');
			   		$(this).removeClass('match_hl_bgcolor');
			   }
			});
			$("#crmProp tr").each(function() {
			   if ($(this).hasClass('possible_match_hl_bgcolor') || $(this).hasClass('match_hl_bgcolor')) {
			   		$(this).find('td:eq(3)').html("0"); 
					crmTable.row($(this)).invalidate();
			   		$(this).removeClass('possible_match_hl_bgcolor');
			   		$(this).removeClass('match_hl_bgcolor');
			   }
			});

			// loop CRM table
			var match_index = 1;
			var match_limit = 50;
			var loop_count = 1;
			var percent = 0;
			var pme_matched_prop_arr = [];

			crmTable.rows().every(function(index, element) {	

				var crm_node = $(this.node()); // object
				var data = $(this.data()); // row column

				// crm
				var crmData = data[1];			
				var desired1 = crmData.replace(/[^\w\s]/gi, '')
				var newstrCrmData = desired1.replace(/ +?/g, '');

				// loop PMe table
				pmeTable.rows().every(function(index2, element2) {					

					var pme_node = $(this.node());	// object
					var data2 = $(this.data()); // row column
					
					// pme
					var pmeData = data2[0];
					var desired2 = pmeData.replace(/[^\w\s]/gi, '')
					var newstrPmeData = desired2.replace(/ +?/g, '');													
					var newstrCrmData_rep_exact = clearStreetName(crmData).replace(/\s+/g,' ').trim().toLowerCase();
					var newstrPmeData_rep_exact = clearStreetName(pmeData).replace(/\s+/g,' ').trim().toLowerCase();

					if (newstrCrmData_rep_exact !== newstrPmeData_rep_exact) {
					
						// regex remove characters that are not alpha numeric
						var newstrCrmData_rep = clearStreetName(crmData).replace(/\s+/g,' ').trim().toLowerCase().replace(/[^a-z0-9]/gi, ' ');
						var newstrPmeData_rep = clearStreetName(pmeData).replace(/\s+/g,' ').trim().toLowerCase().replace(/[^a-z0-9]/gi, ' ');

						var newstrCrmData_rep = newstrCrmData_rep.replace(' of','');
						var newstrPmeData_rep = newstrPmeData_rep.replace(' of','');

						console.log("newstrCrmData_rep: "+newstrCrmData_rep+" - newstrPmeData_rep: "+newstrPmeData_rep);

						// match					
						if ( ( newstrCrmData_rep != '' &&  newstrPmeData_rep != '' ) && ( newstrCrmData_rep == newstrPmeData_rep ) && pme_node.hasClass( "probable_match_hl_bgcolor" ) == false && match_index <= match_limit ) {

							if( pme_matched_prop_arr.includes(newstrPmeData_rep) == false ){ // avoid duplicates

								// tick checkbox
								// crm_node.find(".chk_prop").prop("checked",true);

								// show match arrow
								// crm_node.find(".match_arrow").css('display','inline'); // .show() wont work don't know why :(

								// add row higlight
								crm_node.addClass("probable_match_hl_bgcolor");
								pme_node.addClass("probable_match_hl_bgcolor");
								pme_matched_prop_arr.push(newstrPmeData_rep);										

								// invalidate() - important to run or sort will not fuction after editing column
								crm_node.find(".sort_index").html(match_index);
								crmTable.row(crm_node).invalidate(); 

								pme_node.find(".sort_index").html(match_index);
								pmeTable.row(pme_node).invalidate();
								
								match_index++;


							}						

						}		

						// get percent
						percent = Math.round(loop_count / total_prop * 100);
						if( percent % 10 == 0 ){									
							setLbar(percent);
						}					
						loop_count++;	
					}		

				});						

			});

			//jQuery('#load-screen').hide(); 	
			jQuery(".loading-bar-div").hide();

			// sort to push match items to top
			crmTable.order([[ 3, 'desc' ], [ 1, 'asc' ]]).draw();
			pmeTable.order([[ 2, 'desc' ], [ 0, 'asc' ]]).draw();


			// match counter
			match_counter();

			// match counter message
			var countSelec = jQuery(".probable_match_hl_bgcolor").length;
			if( countSelec > 0 ) {
				swal({
					title: "Info!",
					text: "There are some Probable Matches. Please manually connect them",
					type: "info",
					confirmButtonClass: "btn-primary"
				});
			}


		}, 1000);


		
			
	})


	// Possible Match
	$("#btnPossibleMatch").click(function() {

        console.log("POSSIBLE MATCH:");

		//jQuery('#load-screen').show(); 
		jQuery(".loading-bar-div").show();

		var crm_total = crmTable.rows().count();
		var pme_total = pmeTable.rows().count();
		var total_prop = crm_total*pme_total;

		$("#pmeProp tr").each(function() {
		   if ($(this).hasClass('probable_match_hl_bgcolor') || $(this).hasClass('match_hl_bgcolor')) {
		   		$(this).find('td:eq(2)').html("0"); 
				pmeTable.row($(this)).invalidate();
		   		$(this).removeClass('probable_match_hl_bgcolor');
		   		$(this).removeClass('match_hl_bgcolor');
		   }
		});
		$("#crmProp tr").each(function() {
		   if ($(this).hasClass('probable_match_hl_bgcolor') || $(this).hasClass('match_hl_bgcolor')) {
		   		$(this).find('td:eq(3)').html("0"); 
				crmTable.row($(this)).invalidate();
		   		$(this).removeClass('probable_match_hl_bgcolor');
		   		$(this).removeClass('match_hl_bgcolor');
		   }
		});

		setTimeout(function(){ 

			// clear all selection
			jQuery(".chk_prop").prop("checked",false);
			jQuery(".match_arrow").css('display','none');

			// loop CRM table
			var match_index = 1;
			var match_limit = 50;
			var loop_count = 1;
			var percent = 0;
			var pme_matched_prop_arr = [];

			crmTable.rows().every(function(index, element) {	

				var crm_node = $(this.node()); // object
				var data = $(this.data()); // row column

				// crm
				var crmData = data[1];			
				var desired1 = crmData.replace(/[^\w\s]/gi, '')
				var newstrCrmData = desired1.replace(/ +?/g, '');

				var crm_addr_street_num = crm_node.find(".crm_addr_street_num").val();							
				var crm_addr_street_name = clearStreetName(crm_node.find(".crm_addr_street_name").val());				
				var crm_addr_state = crm_node.find(".crm_addr_state").val();

				//var crm_prop_add = crm_addr_street_num+" "+crm_addr_street_name;
				var crm_prop_add = crm_addr_street_name;
				var crm_prop_add_fin = crm_prop_add.replace(/\s+/g,' ').trim().toLowerCase().replace(/[^a-z0-9]/gi, ' ');

				// loop PMe table
				pmeTable.rows().every(function(index2, element2) {					

					var pme_node = $(this.node());	// object
					var data2 = $(this.data()); // row column
					
					// pme
					var pmeData = data2[0];
					var desired2 = pmeData.replace(/[^\w\s]/gi, '')
					var newstrPmeData = desired2.replace(/ +?/g, '');													

					var pme_addr_unit = pme_node.find(".pme_addr_unit").val();
					var pme_addr_number = pme_node.find(".pme_addr_number").val();
					var street_arr = [];

					/*
					// join unit and streen num
					if( pme_addr_unit !='' ){						
						street_arr.push(pme_addr_unit);
					}
					if( pme_addr_number !='' ){
						street_arr.push(pme_addr_number);						
					}

					var street_num_unit_and_num = street_arr.join(" ");
					*/
								

					var pme_addr_street = clearStreetName(pme_node.find(".pme_addr_street").val());
					var pme_addr_state = pme_node.find(".pme_addr_state").val();

					//var pme_prop_add = street_num_unit_and_num+" "+pme_addr_street;
					var pme_prop_add = pme_addr_street;
					var pme_prop_add_fin = pme_prop_add.replace(/\s+/g,' ').trim().toLowerCase().replace(/[^a-z0-9]/gi, ' ');

                    console.log("crm_prop_add_fin: "+crm_prop_add_fin+" - pme_prop_add_fin: "+pme_prop_add_fin);						
					
					// match					
					if ( ( crm_prop_add_fin != '' &&  pme_prop_add_fin != '' ) && ( crm_prop_add_fin == pme_prop_add_fin ) && match_index <= match_limit ) {

						
						//console.log("crm_prop_add_fin: "+crm_prop_add_fin+" - pme_prop_add_fin: "+pme_prop_add_fin);	
						//console.log("Matched Found!");
						
						if( pme_matched_prop_arr.includes(pme_prop_add_fin) == false ){ // avoid duplicates

							// tick checkbox
							//crm_node.find(".chk_prop").prop("checked",true);
							// show match arrow
							//crm_node.find(".match_arrow").css('display','inline'); // .show() wont work don't know why :(

							// add row higlight
							//crm_node.addClass("match_hl_bgcolor");
							crm_node.addClass("possible_match_hl_bgcolor");
							//pme_node.addClass("match_hl_bgcolor");	
							pme_node.addClass("possible_match_hl_bgcolor");
							//pme_matched_prop_arr.push(pme_prop_add_fin);										

							// invalidate() - important to run or sort will not fuction after editing column
							crm_node.find(".sort_index").html(match_index);
							crmTable.row(crm_node).invalidate(); 

							pme_node.find(".sort_index").html(match_index);
							pmeTable.row(pme_node).invalidate();
							
							match_index++;

						}						

					}		

					// get percent
					percent = Math.round(loop_count / total_prop * 100);
					if( percent % 10 == 0 ){									
						setLbar(percent);
					}					
					loop_count++;			
				

				});						

			});

			//jQuery('#load-screen').hide(); 	
			jQuery(".loading-bar-div").hide();

			// sort to push match items to top
			crmTable.order([[ 3, 'desc' ], [ 1, 'asc' ]]).draw();
			pmeTable.order([[ 2, 'desc' ], [ 0, 'asc' ]]).draw();


			// match counter
			match_counter();

			// match counter message
			var countSelec = jQuery(".possible_match_hl_bgcolor").length;
			if( countSelec > 0 ) {
				swal({
					title: "Info!",
					text: "There are some Possible Matches. Please manually connect them",
					type: "info",
					confirmButtonClass: "btn-primary"
				});
			}


		}, 1000);

			
	});


	// Clear
	$("#btnClear").click(function() {

		jQuery(".chk_prop:checked").prop("checked",false);
		jQuery(".match_hl_bgcolor").removeClass("match_hl_bgcolor");
		jQuery(".match_arrow:visible").hide();

		/*
		$("#numCon").html(0)
		//$("#numCon").removeClass("label-success")
		//$("#numCon").addClass("label-danger")
		*/

		match_counter(1);

	});

	// Bulk Connect
	$("#bulkCon").click(function() {

		var numCon = parseInt($("#numCon").html());
			
		var crm_prop_id_arr = [];
		
		jQuery("#crmProp .match_hl_bgcolor").each(function(){

			var crm_prop_id = jQuery(this).find(".crm_prop_id").val();
			crm_prop_id_arr.push(crm_prop_id);

		});


		var pme_prop_id_arr = [];
		jQuery("#pmeProp .match_hl_bgcolor").each(function(){

			var pme_prop_id = jQuery(this).find(".pme_prop_id").val();
			pme_prop_id_arr.push(pme_prop_id);

		});


		if (
				crm_prop_id_arr == undefined ||
				pme_prop_id_arr == undefined ||
				( crm_prop_id_arr.length !== pme_prop_id_arr.length )
			) {
			swal({
				title: "Error!",
				text: "Number of selected properties does not match.",
				type: "error",
				confirmButtonClass: "btn-danger"
			});
		}else {

			if (numCon > 0) {
				
				swal({
					title: "Are you sure?",
					text: "You are about to connect the matched CRM properties and PropertyTree properties below.",
					type: "warning",
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
						
						$('#load-screen').show(); 
						$.ajax({
							url: "/property_tree/bulk_connect_all",
							type: 'POST',
							data: { 
								'agency_id': <?=isset($selected) ? $selected : "1"?>,
								'crmArr' : crm_prop_id_arr,
								'pmeArr' : pme_prop_id_arr
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

								//location.reload();
								crmTable.rows('.match_hl_bgcolor').remove().draw();
								pmeTable.rows('.match_hl_bgcolor').remove().draw();

								// match_counter
								match_counter();

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
				});
				

			}else {
				swal({
					title: "Warning!",
					text: "No matched properties.",
					type: "error",
					confirmButtonClass: "btn-success"
				})
			}

		}
		

	});


	// hide deactivated PMe
	jQuery("#hide_archived_pme_prop_btn").click(function(){

		Cookies.set('hide_pme_archived_prop', 1);
		location.reload();
		
	});


	// show deactivated PMe
	jQuery("#show_archived_pme_prop_btn").click(function(){

		Cookies.set('hide_pme_archived_prop', 0);
		location.reload();

	});

	// show all hidden API property
	jQuery("#show_all_hidden_prop_btn").click(function(){

		Cookies.set('show_all_hidden_prop_pme', 1);
		location.reload();

	});

	// cancel show all hidden API property
	jQuery("#cancel_show_all_hidden_prop_btn").click(function(){

		Cookies.set('show_all_hidden_prop_pme', 0);
		location.reload();

	});

	// hide API properties
	jQuery("#pme_table_div").on("click",".btn_hide_api_prop",function(){

		var dom = jQuery(this);		
		var row = dom.parents("tr:first");			

		var agency_id = jQuery("#agency_filter").val();
		var api_prop_id = row.find(".api_prop_id").val();

		var api_prop_id_arr = [];

		if( api_prop_id != '' ){			
			api_prop_id_arr.push(api_prop_id);
		}	

		if( api_prop_id_arr.length > 0 ){

			swal({
				title: "Hide Property",
				text: "Are you sure you want to hide this property?",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-success",
				confirmButtonText: "Yes, Hide it!",
				cancelButtonText: "No, Cancel!",
				cancelButtonClass: "btn-danger",
				closeOnConfirm: true,
				showLoaderOnConfirm: true,
				closeOnCancel: true
			},
			function(isConfirm) {

				if (isConfirm) {							  
					
					$('#load-screen').show(); 
					var hide_it = 1;

					$.ajax({
						url: "/agency_api/hide_api_property_toggle",
						type: 'POST',
						data: { 
							'agency_id': agency_id,
							'api_prop_id_arr' : api_prop_id_arr,
							'hide_it': hide_it						
						}
					}).done(function( ret ){
												
						$('#load-screen').hide(); 	
						//row.remove();
						pmeTable.row( row ).remove().draw();				
					
					});							

				}
			});
			
		}		

	});


	// hide API properties
	jQuery("#pme_table_div").on("click",".btn_unhide_api_prop",function(){

		var dom = jQuery(this);		
		var row = dom.parents("tr:first");			

		var agency_id = jQuery("#agency_filter").val();
		var api_prop_id = row.find(".api_prop_id").val();

		var api_prop_id_arr = [];

		if( api_prop_id != '' ){			
			api_prop_id_arr.push(api_prop_id);
		}	

		if( api_prop_id_arr.length > 0 ){

			swal({
				title: "Unhide Property",
				text: "Are you sure you want to unhide this property?",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-success",
				confirmButtonText: "Yes, Unhide it!",
				cancelButtonText: "No, Cancel!",
				cancelButtonClass: "btn-danger",
				closeOnConfirm: true,
				showLoaderOnConfirm: true,
				closeOnCancel: true
			},
			function(isConfirm) {

				if (isConfirm) {							  
					
					$('#load-screen').show(); 
					var hide_it = 0;
					
					$.ajax({
						url: "/agency_api/hide_api_property_toggle",
						type: 'POST',
						data: { 
							'agency_id': agency_id,
							'api_prop_id_arr' : api_prop_id_arr,
							'hide_it': hide_it						
						}
					}).done(function( ret ){
												
						$('#load-screen').hide(); 	
						location.reload();			
					
					});							

				}
			});
			
		}		

	});


	// hide API properties by BULK
	jQuery("#pme_table_div").on("click","#btn_hide_prop_bulk",function(){			

		var agency_id = jQuery("#agency_filter").val();		
		var api_prop_id_arr = [];		

		var prop_count = jQuery(".api_prop_chk:checked:visible").length;

		// loop through PMe properties
		jQuery(".api_prop_chk:checked:visible").each(function(){

			var obj = jQuery(this);	
			var row = 	obj.parents("tr:first");	

			var api_prop_id = row.find(".api_prop_id").val();			

			if( api_prop_id != '' ){						
				api_prop_id_arr.push(api_prop_id);
			}												

		});		

		if( api_prop_id_arr.length > 0 ){

			swal({
				title: "Hide Property",
				text: "You are about to hide "+prop_count+" selected properties.",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-success",
				confirmButtonText: "Yes, Hide it!",
				cancelButtonText: "No, Cancel!",
				cancelButtonClass: "btn-danger",
				closeOnConfirm: true,
				showLoaderOnConfirm: true,
				closeOnCancel: true
			},
			function(isConfirm) {

				if (isConfirm) {							  
					
					$('#load-screen').show(); 
					var hide_it = 1;

					$.ajax({
						url: "/agency_api/hide_api_property_toggle",
						type: 'POST',
						data: { 
							'agency_id': agency_id,
							'api_prop_id_arr' : api_prop_id_arr,
							'hide_it': hide_it								
						}
					}).done(function( ret ){
												
						$('#load-screen').hide(); 	
						location.reload();										
					
					});							

				}
			});
			
		}		

	});


	// unhide API properties by BULK
	jQuery("#pme_table_div").on("click","#btn_unhide_prop_bulk",function(){			

		var agency_id = jQuery("#agency_filter").val();		
		var api_prop_id_arr = [];		

		var prop_count = jQuery(".api_prop_chk:checked:visible").length;

		// loop through PMe properties
		jQuery(".api_prop_chk:checked:visible").each(function(){

			var obj = jQuery(this);	
			var row = 	obj.parents("tr:first");	

			var api_prop_id = row.find(".api_prop_id").val();			

			if( api_prop_id != '' ){						
				api_prop_id_arr.push(api_prop_id);
			}												

		});		

		if( api_prop_id_arr.length > 0 ){

			swal({
				title: "Unhide Property",
				text: "You are about to unhide "+prop_count+" selected properties.",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-success",
				confirmButtonText: "Yes, Unhide it!",
				cancelButtonText: "No, Cancel!",
				cancelButtonClass: "btn-danger",
				closeOnConfirm: true,
				showLoaderOnConfirm: true,
				closeOnCancel: true
			},
			function(isConfirm) {

				if (isConfirm) {							  
					
					$('#load-screen').show(); 
					var hide_it = 0;

					$.ajax({
						url: "/agency_api/hide_api_property_toggle",
						type: 'POST',
						data: { 
							'agency_id': agency_id,
							'api_prop_id_arr' : api_prop_id_arr,
							'hide_it': hide_it								
						}
					}).done(function( ret ){
												
						$('#load-screen').hide(); 	
						location.reload();										
					
					});							

				}

			});
			
		}		

	});


});

</script>