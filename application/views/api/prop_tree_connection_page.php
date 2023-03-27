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
    border: solid 2px #044d66;
    background: #f5f8fa;	
}
.breadcrumb{
	margin-bottom: 0;
}
.crm_main_div{
	border-top: solid 2px white;
}
.pme_btn_color{
	background-color: #044d66 !important;
    border-color: #044d66 !important;

}
.pme_headings{
	color: #044d66 !important;
}
.remove_bottom_padding{
	padding-bottom: 0 !important;
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

<section>
    <?php 
        $tenList = json_decode($tenList);
        $lotList = json_decode($lotList);
        $conList = json_decode($conList);
    ?>
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
                                        <tr>
                                            <th>Street Number </th><td colspan="2"><?php echo $crm_prop->p_address_1; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Street Name</th><td colspan="2"><?php echo $crm_prop->p_address_2; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Suburb </th><td colspan="2"><?php echo $crm_prop->p_address_3; ?></td>
                                        </tr>
                                        <tr>
                                            <th>State</th><td colspan="2"><?php echo $crm_prop->p_state; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Postcode</th><td colspan="2"><?php echo $crm_prop->p_postcode; ?></td>
                                        </tr>
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

                                        <!--
                                        <button class="btn btn-primary btn_edit td1_id<?=$val['property_tenant_id']?>" data-id="<?=$val['property_tenant_id']?>">Edit</button>
                                        <button class="btn btn-danger btn_del td1_id<?=$val['property_tenant_id']?>" data-id="<?=$val['property_tenant_id']?>">Delete</button>
                                        <button class="btn btn-primary btn_save td2_id<?=$val['property_tenant_id']?>" style='display:none;' data-id="<?=$val['property_tenant_id']?>">Save</button>
                                        <button class="btn btn-danger btn_can td2_id<?=$val['property_tenant_id']?>" style='display:none;' data-id="<?=$val['property_tenant_id']?>">Cancel</button>
                                        -->

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
                        <div class="logo-container">
							<img src="/images/third_party/propertytree.png" class="company_logo pme_logo" style="width:250px;" />
							<!--
                            <a href="https://www.mrisoftware.com/au/" id="logo" class="header-svg-logo">                                                                                
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 12500 1000"><style type="text/css">.st0{fill:#5C5D5F;} .st1{fill:#BBD437;} .st2{fill:#044D66;}</style><path d="M59.6 892.3c0 7.1-1.9 12.8-5.6 17.1-3.8 4.4-9.3 7.2-16.7 8.4l24.5 35.5H47.9l-22.2-35h-15v35H0v-85.6h29.8c9.6 0 17 2.3 22.1 6.9 5.1 4.8 7.7 10.7 7.7 17.7zM29 911c6.4 0 11.3-1.6 14.6-4.7 3.3-3.1 5-7.3 5-12.4 0-5.1-1.6-9.2-4.8-12.2-3.2-3-7.9-4.6-13.9-4.6h-19V911H29zM91.4 867.8H144v9.2h-41.9v28.7h39.4v9.1h-39.4V944H144v9.4H91.4v-85.6zM195.3 867.8h13.1l28.7 85.6h-11.2l-7.3-21.8h-34l-7.3 21.8H166l29.3-85.6zm-7.9 55.4h28.4L201.7 881l-14.3 42.2zM273.3 944h38.1v9.4h-48.8v-85.6h10.7V944zM385.6 867.8h52.6v9.2h-41.9v28.7h39.5v9.1h-39.5V944h41.9v9.4h-52.6v-85.6zM516 888.7c-1.5-8.3-7.7-12.5-18.4-12.5-5.7 0-10.1 1.2-13.2 3.7-3.1 2.5-4.6 5.5-4.6 9s1 6.1 2.9 8c1.9 1.8 5 3.4 9.3 4.8l17.8 6.1c6.1 2.1 10.9 4.9 14.5 8.6 3.6 3.6 5.4 8.7 5.4 15.3s-2.8 12.1-8.4 16.6c-5.6 4.5-13.2 6.7-22.6 6.7-9.4 0-17.2-2.4-23.2-7.1-6-4.8-9.1-11.4-9.3-20.1h10.5c0 5.6 2 9.9 5.9 12.9 3.9 3 9 4.5 15.2 4.5s11.1-1.4 14.8-4.1c3.6-2.8 5.4-6 5.4-9.8 0-3.8-1.1-6.7-3.3-8.9-2.2-2.2-5.5-4-9.9-5.4l-15.1-5.6c-7.1-2.4-12.4-5.2-16.1-8.4-3.6-3.3-5.4-8-5.4-14.3 0-6.3 2.5-11.6 7.6-15.8 5.1-4.2 12.2-6.3 21.4-6.3s16.2 2 21 6.1c4.9 4 7.6 9.5 8.4 16.3H516zM545.2 867.8h66.5v9.2h-27.9v76.3h-10.7V877h-27.9v-9.2zM650.9 867.8H664l28.7 85.6h-11.2l-7.3-21.8h-34l-7.3 21.8h-11.3l29.3-85.6zm-7.9 55.4h28.4L657.3 881 643 923.2zM699.7 867.8h66.5v9.2h-27.9v76.3h-10.7V877h-27.9v-9.2zM788.8 867.8h52.6v9.2h-41.9v28.7H839v9.1h-39.4V944h41.9v9.4h-52.6v-85.6zM970.6 888.7c-1.5-8.3-7.7-12.5-18.4-12.5-5.7 0-10.1 1.2-13.2 3.7-3.1 2.5-4.6 5.5-4.6 9s1 6.1 2.9 8c1.9 1.8 5 3.4 9.3 4.8l17.8 6.1c6.1 2.1 10.9 4.9 14.5 8.6 3.6 3.6 5.4 8.7 5.4 15.3s-2.8 12.1-8.4 16.6c-5.6 4.5-13.2 6.7-22.6 6.7-9.4 0-17.2-2.4-23.2-7.1-6-4.8-9.1-11.4-9.3-20.1h10.5c0 5.6 2 9.9 5.9 12.9 3.9 3 9 4.5 15.2 4.5s11.1-1.4 14.8-4.1c3.6-2.8 5.4-6 5.4-9.8 0-3.8-1.1-6.7-3.3-8.9-2.2-2.2-5.5-4-9.9-5.4l-15.1-5.6c-7.1-2.4-12.4-5.2-16.1-8.4-3.6-3.3-5.4-8-5.4-14.3 0-6.3 2.5-11.6 7.6-15.8 5.1-4.2 12.2-6.3 21.4-6.3s16.2 2 21 6.1c4.9 4 7.6 9.5 8.4 16.3h-10.6zM1042.9 866.3c11.1 0 20 3.8 26.9 11.4 6.8 7.6 10.3 18.6 10.3 33.1s-3.4 25.4-10.2 32.9c-6.8 7.5-15.8 11.2-26.9 11.2s-20.1-3.8-26.9-11.4c-6.8-7.6-10.1-18.5-10.1-32.9 0-14.4 3.4-25.4 10.1-33 6.7-7.5 15.7-11.3 26.8-11.3zm0 9.7c-8 0-14.3 3-18.9 9-4.6 6-6.9 14.6-6.9 25.8 0 11.2 2.3 19.8 6.9 25.6 4.6 5.8 10.9 8.7 18.9 8.7 8 0 14.3-2.9 19-8.7 4.7-5.8 7-14.3 7-25.6 0-11.2-2.3-19.9-7-25.8-4.7-6-11-9-19-9zM1106 867.8h52v9.2h-41.2v28.4h38.4v9.3h-38.4v38.6H1106v-85.5zM1175.3 867.8h66.5v9.2h-27.9v76.3h-10.7V877h-27.9v-9.2zM1267.4 867.8l20.8 71.1 21.1-62.3h7.2l20.9 62.3 23.9-71.1h11.8l-30.2 85.6h-11.3l-18.6-54.8-19.3 54.8h-11.3l-27.1-85.6h12.1zM1411.8 867.8h13.1l28.7 85.6h-11.2l-7.3-21.8h-34l-7.3 21.8h-11.3l29.3-85.6zm-7.9 55.4h28.4l-14.1-42.2-14.3 42.2zM1535.1 892.3c0 7.1-1.9 12.8-5.6 17.1-3.8 4.4-9.3 7.2-16.7 8.4l24.5 35.5h-13.9l-22.2-35h-15v35h-10.7v-85.6h29.8c9.6 0 17 2.3 22.1 6.9 5.2 4.8 7.7 10.7 7.7 17.7zm-30.6 18.7c6.4 0 11.3-1.6 14.6-4.7 3.3-3.1 5-7.3 5-12.4 0-5.1-1.6-9.2-4.8-12.2-3.2-3-7.9-4.6-13.9-4.6h-19V911h18.1zM1561.5 867.8h52.6v9.2h-41.9v28.7h39.4v9.1h-39.4V944h41.9v9.4h-52.6v-85.6z" class="st0"></path><path d="M1320.5 157.5c-93.9 0-171.5 39.6-221.4 103.8l-.3-.5c-16.9-21.5-36.9-40.2-59.6-55.6v542.3h121V441.3c0-97.5 63-170.2 160.4-170.2 57.1 0 102.2 25.1 129.9 65.4V190c-36.9-20.8-80.7-32.5-130-32.5z" class="st1"></path><path d="M1005.4 445.6v301.9H883.7V441.3c0-97.5-63-170.3-160.4-170.3s-160.4 72.8-160.4 170.3v306.3H442.5V441.3c0-97.5-63-170.3-160.4-170.3-57.6 0-103.1 22.7-130.7 63.7-19.6 28.5-30.4 64.6-30.4 105v307.8H0V203.8c22.7 15.4 39.7 34.1 56.6 55.6l2 2.5c50-64.2 128.9-105.8 222.8-105.8 49.3 0 93 12.7 129.9 33.6 38.1 21.3 68.8 52.4 90.7 90.7 0 0 .5.8.7 1.2.2-.4.4-.8.6-1.2 43.1-75.1 120.1-122.7 220-122.7 166.1-.2 282.1 125 282.1 287.9z" class="st2"></path><circle cx="1550" cy="93.4" r="64.2" class="st1"></circle><path d="M1486 340.9v-140c22.1 15 127.3 81 127.3 188.6l.3 45.6-.7 312c-31.6-15-51.1-33.2-67.6-54.2 0 0-.4-.9-.6-.7-2.2-2.9-4.4-5.8-6.6-8.8-11.1-15.6-20.6-32.5-28.4-50.6-1.6-3.6-3-7.3-4.4-11-1.4-3.6-2.7-7.3-3.9-11-3.7-11.1-6.8-22.6-9.2-34.4-4.2-20.4-6.4-41.6-6.4-63.7l.2-171.8z" class="st2"></path></svg>
                            </a>
							-->
                        </div>
                    </div>
                    
                    <header class="box-typical-header">
                        <div class="tbl-row">
                            <div class="tbl-cell tbl-cell-title">
                                <h3 class="pme_headings"><span class="glyphicon glyphicon-map-marker"></span> Property Details</h3>
                            </div>
                        </div>
                    </header>

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
                                            <td>
                                                <?php

                                                    $api_address = $api_prop_data->address;

                                                    // street
                                                    if( $api_address->unit != '' && $api_address->street_number != '' ){
                                                        $street_unit_num = "{$api_address->unit}/{$api_address->street_number}";
                                                    }else if( $api_address->unit != '' ){
                                                        $street_unit_num = "{$api_address->unit}";
                                                    }else if( $api_address->street_number != '' ){
                                                        $street_unit_num = "{$api_address->street_number}";
                                                    }
                                                        
                                                    echo "{$street_unit_num} {$api_address->address_line_1}, {$api_address->suburb} {$api_address->state} {$api_address->post_code}";  

                                                ?>
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
										<tr>
                                            <th>Street Number </th><td colspan="2"><?php echo $street_unit_num; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Street Name</th><td colspan="2"><?php echo $api_address->address_line_1; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Suburb </th><td colspan="2"><?php echo $api_address->suburb; ?></td>
                                        </tr>
                                        <tr>
                                            <th>State</th><td colspan="2"><?php echo $api_address->state; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Postcode</th><td colspan="2"><?php echo $api_address->post_code; ?></td>
                                        </tr>	
                                    </tbody>

                                </table>
                            </td>
							<td class="text-center">
								<!--
								<a class="btn_link" href="javascript:void(0)" data-toggle="tooltip" title="Unlink this PMe property" data-id="<?=$lotList->Id?>">
								<i class="fa fa-chain-broken fa-lg"></i></a>	
								-->
								<img src="/images/link-green.png" class="link_icon btn_link" data-id="<?=$lotList->Id?>" />
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
								<th>Action</th>                               
                            </tr>
                        </thead>
                        <tbody>
                            <?php                             
                            foreach ( $contact_arr as $contact_obj ) { 
                            ?>
                                <tr>
                                    <td>
										<?php echo $contact_obj->first_name; ?>
										<input type="hidden" class="first_name" value="<?php echo $contact_obj->first_name; ?>" />
									</td>
									<td>
										<?php echo $contact_obj->last_name; ?>
										<input type="hidden" class="last_name" value="<?php echo $contact_obj->last_name; ?>" />
									</td>
                                    <td>
										<?php echo $contact_obj->mobile_phone_number; ?>
										<input type="hidden" class="mobile" value="<?php echo $contact_obj->mobile_phone_number; ?>" />
									</td>
									<td>
										<?php echo $contact_obj->phone_number; ?>
										<input type="hidden" class="landline" value="<?php echo $contact_obj->phone_number; ?>" />
									</td>
									<td>
										<?php echo $contact_obj->email_address; ?>
										<input type="hidden" class="email" value="<?php echo $contact_obj->email_address; ?>" />
									</td>  
									<td>
										<button type="button" class="addTenant btn btn-primary">
											<span class="">Save</span> 
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
	
</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4>PMe Tenant & PMe Property Details</h4>
	<p>This page shows PMe Tenant & PMe Property Details via PMe API</p>

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
		jQuery(".btn_edit_crm_tenant").click(function(){

			jQuery(this).parents("tr:first").find(".action_btn_div").hide();
			jQuery(this).parents("tr:first").find(".edit_btn_div").show();
			jQuery(this).parents("tr:first").find(".txt_lbl").hide();
			jQuery(this).parents("tr:first").find(".txt_hid").show();

		});

		// action button cancel 
		jQuery(".btn_cancel_crm_tenant").click(function(){

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
			  closeOnConfirm: false,
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
					/*
					if (ret.updateStat === true) {
						$('#tr_id'+id).remove();
						$('#tr_id1'+id).remove();
			            swal({
			                title: "Success!",
			                text: "Deleted Tenant",
			                type: "success",
			                confirmButtonClass: "btn-success"
			            });

						getNotMatch();
					}else {
			            swal({
			                title: "Error!",
			                text: "Something went wrong, contact dev.",
			                type: "error",
			                confirmButtonClass: "btn-danger"
			            });
					}
					*/
					location.reload();

				})
			  }
			});
		})
		


	// update tenant
	jQuery('.btn_save_crm_tenant').click(function() {

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
			
			/*
			if (ret.updateStat === true) {				
					swal({
							title: "Success!",
							text: "Updated Tenant",
							type: "success",
							confirmButtonClass: "btn-success"
					});
				// getNotMatch();
			}else {
				swal({
						title: "Error!",
						text: "Something went wrong, contact dev.",
						type: "error",
						confirmButtonClass: "btn-danger"
				});
			}
			*/
			
			location.reload();

		});

	});


		$(document).on('click', '.btn_link', function() {
			
			var crmId = <?php echo $property_id; ?>;
			
			swal({
			  title: "Are you sure?",
			  text: "You will unlink this PropertyTree property.",
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
					url: "/property_tree/ajax_function_unlink_property",
					type: 'POST',
					data: {
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
		});

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

		
		jQuery(".addTenant").click(function(){

			var property_id = <?php echo $property_id; ?>;
			var save_btn_dom = jQuery(this);
			var row_dom = save_btn_dom.parents("tr:first");					

			var first_name = row_dom.find(".first_name").val();
			var last_name = row_dom.find(".last_name").val();
			var mobile = row_dom.find(".mobile").val();
			var landline = row_dom.find(".landline").val();
			var email = row_dom.find(".email").val();

			var console_tenant_mobile_arr = [];
			var console_tenant_landline_arr = [];

			var error = '';

			/*
			row_dom.find(".select_phone_type").each(function(){

				var pt_dom = jQuery(this);
				var pt_dom_val = pt_dom.val();
				var phone_row_dom = pt_dom.parents("tr:first");
				var console_tenant_phone_number = phone_row_dom.find(".console_tenant_phone_number").val();	
				
				if( pt_dom_val == 1 ){ // mobile									
					console_tenant_mobile_arr.push(console_tenant_phone_number);
				}

				if( pt_dom_val == 2 ){ // landline					
					console_tenant_landline_arr.push(console_tenant_phone_number);
				}

			});

			var console_tenant_email = row_dom.find(".console_tenant_email:checked").val();

			if( console_tenant_mobile_arr.length > 1 ){
				error += "Can only select 1 mobile number per tenant\n";
			}

			if( console_tenant_landline_arr.length > 1 ){
				error += "Can only select 1 landline per tenant\n";
			}
			*/


			if( property_id > 0 ){

				if( error != '' ){ // error

					swal('',error,'error');

				}else{ // success

					$('#load-screen').show(); 
					$.ajax({
						url: "/property_me/ajax_function_tenants",
						type: 'POST',
						data: { 
							'property_id': property_id, 
							'tenant_firstname': first_name, 
							'tenant_lastname': last_name, 
							'tenant_mobile':  mobile, 
							'tenant_landline': landline, 
							'tenant_email': email, 
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
								location.reload();
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

				}				

			}			

		});
		


	})
</script>