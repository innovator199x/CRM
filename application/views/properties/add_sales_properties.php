<style>
#btnAddProperty {
    width: 100%;
    border-radius: 0;
}
.ptabs .invalid{
    border: 1px solid #dc3545;
}
.hr_div .input-group > .form-control {
width: 1% !important;
}
.ok_tick {
    color: #46c35f !important;
}
.active_api_span{
    margin-right: 18px;
}
.flatpickr{
    width:155px;
}
#other_supplier_job{
    width:155px;
}
#prop_upgraded_to_ic_sa_div,
#service_garage_div{
    display: none;
}
.tenant_priority {
    width: 18px;
    height: 18px;
    margin: auto;
    margin-top: 10px;
}
</style>
<link rel="stylesheet" href="/inc/css/separate/elements/steps.min.css">
<div class="box-typical box-typical-padding">

    <?php
    // breadcrumbs template
    $bc_items = array(
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/properties/add_sales_properties"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>


    <section class="box-typical steps-numeric-block col-lg-12s">

        <div class="steps-numeric-header">
            <div class="steps-numeric-header-in">
                <ul class="steps_ul">
                    <li class="step active" id="step1">
                        <div class="item"><span class="num">1</span><span class="title-text">Agency</span><span class="font-icon font-icon-ok step-icon-finish"></span></div>
                    </li>
                    <li class="step" id="step2">
                        <div class="item"><span class="num">2</span><span class="title-text">Property Address</span><span class="font-icon font-icon-ok step-icon-finish"></span></div>
                    </li>
                    <li class="step" id="step3">
                        <div class="item"><span class="num">3</span><span class="title-text">Services</span><span class="font-icon font-icon-ok step-icon-finish"></span></div>
                    </li>
                    <li class="step" id="step4">
                        <div class="item"><span class="num">4</span><span class="title-text">Additional Info</span><span class="font-icon font-icon-ok step-icon-finish"></span></div>
                    </li>
                    <li class="step" id="step5">
                        <div class="item"><span class="num">5</span><span class="title-text">Tenant Details</span><span class="font-icon font-icon-ok step-icon-finish"></span></div>
                    </li>
                    <li class="step" id="step6">
                        <div class="item"><span class="num">6</span><span class="title-text">Comments</span><span class="font-icon font-icon-ok step-icon-finish"></span></div>
                    </li>
                </ul>
            </div>
        </div>
        <?php echo form_open_multipart('properties/add_sales_properties_form',array('id'=>'add_property_form')); ?>
       <div id="loader_block" style="display:none;"> <div id="div_loader"></div></div>
        <div class="steps-numeric-inner">

             <!-------------------------------------------------------GROUP 1--------------------------------------------->
            <div class="ptabs" id="group_1" style="display:block;">

                <div class="row">
                    <div class="col-md-5">
                        <header class="steps-numeric-title">Select Agency</header>
                    </div>
                    <div class="col-md-3">
                    </div>
                    <div class="col-md-4 available_api_div">
                      
                    </div>
                </div>

                <div class="row">

                    <div class="col-md-3">

                        <div class="form-group">
                        <input type="hidden" name="propertyme_prop_id" id="propertyme_prop_id">
                        <select class="form-control agency g_req" name="agency" id="agency" data-field="Agency">
                            <option value="">Please Select</option>
                            <?php
                                foreach($agency_list->result_array() as $row){
                                ?>
                                    <option
                                        data-allow_pm="<?php echo $row['allow_indiv_pm'] ?>"
                                        data-fg="<?php echo $row['franchise_groups_id'] ?>"
                                        data-load_api="<?php echo $row['load_api'] ?>"
                                        value="<?php echo $row['agency_id'] ?>"
                                    >
                                        <?php echo $row['agency_name'] ?>
                                    </option>
                                <?php
                                }
                            ?>
                        </select>
                        </div>

                    </div>

                </div>

            </div>


              <!-------------------------------------------------------GROUP 2--------------------------------------------->
             <div class="ptabs" id="group_2" style="display:none;">

                <header class="steps-numeric-title">Search Address</header>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <input id="fullAdd" type="text" class="form-control" data-field="Address" id="inputPassword" placeholder="Type in the address: e.g. 500 George st" autocomplete="off" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div id="duplicate_prop_msg"></div>
                        </div>
                    </div>
                </div>

                <!-- PMe Properties -->
                <div class="api_main_div">
                </div>


                <div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-2">
                                <div class="form-group ">
                                    <input  data-field="Street No." id="address_1" name="address_1" type="text" class="form-control  g_req" autocomplete="off" placeholder="Street No. *">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group ">
                                    <input  data-field="Street Name"  id="address_2" name="address_2" type="text" class="form-control  g_req" autocomplete="off" placeholder="Street Name *">
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group ">
                                    <input  data-field="Suburb"  id="address_3" name="address_3" type="text" class="form-control  g_req" autocomplete="off" placeholder="Suburb *">
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group ">
                                    <?php if($this->config->item('country') == 1){ ?>

                                        <select  data-field="State" id="state" name="state" class="form-control select2-arrow manual select2-no-search-arrow g_req">
                                            <option value="">SELECT</option>
                                            <?php
                                                $state = $this->properties_model->getCountryState();
                                                foreach($state->result_array()as $row){
                                            ?>
                                                    <option value="<?php echo $row['state'] ?>"><?php echo $row['state'] ?></option>
                                            <?php
                                                }
                                            ?>
                                        </select>

                                   <?php  }else{ ?>

                                    <input  data-field="Region" placeholder="Region" class="form-control g_req" type="text" id="state" name="state" >

                                    <?php } ?>

                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group ">
                                    <input data-field="Postcode"  id="postcode" name="postcode" type="text" class="form-control g_req" autocomplete="off" placeholder="Postcode *">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-------------------------------------------------------GROUP 3--------------------------------------------->
            <div class="ptabs" id="group_3" style="display:none;">

                <header class="steps-numeric-title">Services</header>

                <div class="row">
                    <div class="col-lg-12 col-md-12 tbl_service">
                        <div id="services_ajax_block">
                            <!-- LOAD SERVICES AJAX HERE... -->
                        </div>
                    </div>

                </div>

                <div>
                    <p>&nbsp;</p>
                </div>
              

            </div>


            <!-------------------------------------------------------GROUP 4--------------------------------------------->
            <div class="ptabs" id="group_4" style="display:none">

                <div class="hr_div">

                    <!--------------------- Property Vacant -->
                    <div class="row current_vacant">
                        <div class="col-md-12">
                            <div class="row">

                                <div class="col-md-3"><label>Property Vacant?</label></div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <select name="prop_vacant" id="prop_vacant" class="form-control g_req" data-field="Property Vacant">
                                                <option value="">Please select</option>
                                                <option value="0">No</option>
                                                <option value="1">Yes</option>
                                        </select>
                                                <span class="font-icon font-icon-ok check-input-ok"></span>
                                    </div>
                                </div>

                                
                            </div>
                        </div>


                    </div>
                    
                    <div class="row vacant_from_to" style="display: none;">
                        <div class="col-md-3"><label id="datepicker_txt"></label></div>
                        <div class="col-md-3">
                                
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <div class="input-group date flatpickr" data-wrap="true" >
                                            <input data-input data-field="Vacant/Date From"  type="text" class="form-control" name="vacant_from" id="datepicker_vacantF" placeholder="Vacant From" data-date-format="d-m-Y">
                                            <span class="input-group-append" data-toggle >
                                                    <span class="input-group-text"><i class="font-icon font-icon-calend"></i></span>
                                            </span>
                                        </div>

                                    </div>
                                </div>
                                <div class="col-lg-6">

                                    <div class="form-group">

                                        <div class="input-group date flatpickr" data-wrap="true">
                                            <input   data-input  data-field="Vacant/Date To"  type="text" class="form-control" name="vacant_to" id="datepicker_vacantT" placeholder="Vacant To" data-date-format="d-m-Y">
                                            <span class="input-group-append" data-toggle>
                                                        <span class="input-group-text"><i class="font-icon font-icon-calend"></i></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div> 
                    </div>

                    <!-------------------- NEW TENANCY -->
                    <div class="row new_tenancy" style="display: none;">
                        <div class="col-md-7">
                            <div class="row">
                                <div class="col-md-6"><label>Is this a New Tenancy?</label></div>
                                <div class="col-md-6">
                                    <div class="form-group ">
                                        <select disabled="" data-validation="[NOTEMPTY]" data-validation-label="New Tenancy"  name="is_new_tent" id="is_new_tent" class="form-control">
                                            <option value="">Please select</option>
                                            <option value="0">No</option>
                                            <option value="1">Yes</option>
                                        </select>
                                        <span class="font-icon font-icon-ok check-input-ok"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="row new_tenant_start" style="display: none;">
                                <div class="col-md-9">
                                    <div class="form-group>">
                                        <div class="input-group date flatpickr" data-wrap="true">
                                            <input disabled="" data-validation="[NOTEMPTY]" data-validation-label="New Tenancy Starts" data-input type="text" class="form-control" name="new_ten_start" id="new_ten_start" placeholder="New Tenancy Starts" data-date-format="d-m-Y">
                                            <span class="input-group-append" data-toggle>
                                                <span class="input-group-text"><i class="font-icon font-icon-calend"></i></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div>

                    <div class="row">
                        <div class="col-lg-3">
                            <div class="row">
                                <div class="col-md-7"> <label>More Details</label></div>
                            </div>
                        </div>
                        <div class="col-lg-9">
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="form-group ">
                                        <input type="text" class="form-control" id="key_number" name="key_number" placeholder="Key #">
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group ">
                                        <input type="text" class="form-control" id="workorder_num" name="workorder_num" placeholder="Work Order #">
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group ">
                                        <input type="text" class="form-control" id="lockbox_code" name="lockbox_code" placeholder="Lockbox Code" />
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group ">
                                        <input type="text" class="form-control" id="alarm_code" name="alarm_code" placeholder="House Alarm Code">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="row">
                                <div class="col-md-8"><label>Landlord</label></div>
                            </div>
                        </div>
                        <div class="col-lg-9">
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="form-group ">
                                        <input type="text" data-field="Landlord First Name" class="form-control field_g2 g_req" name="landlord_firstname" id="landlord_firstname" placeholder="First Name *">
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group ">
                                        <input type="text"  data-field="Landlord Last Name" class="form-control field_g2 g_req" name="landlord_lastname" id="landlord_lastname" placeholder="Last Name *">
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="form-group ">
                                        <input type="text"  data-field="Landlord Mobile" class="form-control field_g2 tenant_mobile__tt tenant_mobile g_req" name="landlord_mobile" id="landlord_mobile" placeholder="Mobile *">
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="form-group ">
                                        <input type="text" data-field="Landline Mobile" class="form-control field_g2 phone-with-code-area-mask-input" name="landlord_landline" id="landlord_landline" placeholder="Landline">
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="form-group ">
                                        <input type="text" data-field="Email Address" class="form-control field_g2 g_req" name="landlord_email" id="landlord_email" placeholder="Email Address">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div id="dha_agencies_fields" style="display:none;">
                            <!-- Sheet Notes -->
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="row">
                                        <div class="col-md-8"><label>Run Sheet Notes</label></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <input class="form-control" type="text" name="tech_notes" id="tech_notes">
                                    </div>
                                </div>
                            </div>
                            <!-- Start/End Dates -->
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="row">
                                        <div class="col-md-8"><label>Start Date/End Date</label></div>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <input name="start_date" class="flatpickr form-control flatpickr-input" data-allow-input="true" type="text" placeholder="Start Date" >
                                </div>
                                <div class="col-lg-2">

                                    <input name="due_date" class="flatpickr form-control flatpickr-input" data-allow-input="true" type="text" placeholder="End Date" >
                                </div>
                            </div>
                    </div>



                </div>
            </div>



             <!-------------------------------------------------------GROUP 5--------------------------------------------->
             <div class="ptabs row" id="group_5" style="display:none;">
                <div class="col-lg-12">
                    <header class="steps-numeric-title tenant-title">Tenants</header>
                </div>
                <div class="card-block">
                <div id="tenants-block">
                    <div class="form-group row">
                                <div class="col-lg-1"><label class="bold">Primary Contact</label></div>
                                <div class="col-lg-2"><label class="bold">First Name</label></div>
                                <div class="col-lg-2"><label class="bold">Last Name</label></div>
                                <div class="col-lg-2"><label class="bold">Mobile</label> </div>
                                <div class="col-lg-2"><label class="bold">Phone</label></div>
                                <div class="col-lg-3"><label class="bold">Email Address</label></div>
                    </div>

                    <!-- Tenant row -->
                    <div class="tenants-block-row"></div>
                </div>

                    <button id="add_tenant_row" class="btn btn-inline btn-primary-outline" type="button"><apan class="glyphicon glyphicon-plus"></apan> Tenant</button>

                </div>
            </div>


             <!-------------------------------------------------------GROUP 6--------------------------------------------->
             <div class="ptabs row" id="group_6" style="display:none;">
                    <div class="col-lg-12">


                            <div class="row pm_div">
                                    <div class="col-lg-7">
                                        <header class="steps-numeric-title">Sales Agent</header>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                        <select  data-field="Property Manager" id="pm" name="property_manager" class="form-control g_req">

                                                        </select>
                                                        <input type="hidden" class="hid_allow_pm" name="hid_allow_pm" value="0">
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                            </div>

                            <!--
                            <div class="row">
                                <div class="col-lg-7">
                                <p>&nbsp;</p>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <header class="steps-numeric-title">Email address of where invoice is to be sent (Landlord or Agent)</header>
                                        </div>
                                        <div class="form-group col-lg-6">
                                            <input class="form-control" type="text" name="alt_email" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            -->


                            <!-- COMPASS INDEX -->
                            <!--
                            <div class="row" id="compass_index_num_div" style="display:none;">
                                <div class="col-lg-7">
                                <p>&nbsp;</p>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <header class="steps-numeric-title">Compass Index Number</header>
                                        </div>
                                        <div class="form-group col-lg-6">
                                            <input class="form-control" type="text" name="compass_index_num" id="compass_index_num">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            -->

                            <!-- COMPASS INDEX END -->
                                    <!--
                            <div class="row">
                                <div class="col-lg-7">
                                <p>&nbsp;</p>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <header class="steps-numeric-title">File Upload</header>
                                        </div>
                                        <div class="form-group col-lg-6">
                                        <input type="file" id="fileupload" name="fileupload" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                                   -->
                            


                            <div class="row">
                                <p>&nbsp;</p>
                                <div class="col-lg-12">
                                    <header class="steps-numeric-title">Work Order Notes</header>
                                </div>
                                <div class="form-group col-lg-7">
                                    <textarea class="form-control" rows="5" id="workorder_notes" name="workorder_notes"></textarea>
                                </div>
                            </div>
                    </div>
            </div>




        </div>

        <div class="container-fluid prev_next_div">
            <div class="row">

                    <div class="col">
                        <button type="button" onclick="nextPrev(-1)" id="prevBtn"  class="btn">← PREVIOUS</button>
                    </div>
                    <div class="col">
                        <button type="button" onclick="nextPrev(1)" id="nextBtn"  class="btn btn_add_property_next btn_force_to_blue">NEXT→</button>

                        <input type="hidden" name="pm_passed_agency_id" value="<?php echo $pm_passed_agency_id; ?>" />
                        <input type="hidden" name="pm_prop_id" value="<?php echo $pm_prop_id; ?>" />
                        <input type="hidden" id="selected_pme_prop_id" name="selected_pme_prop_id" />
                        <input type="hidden" id="selected_palace_prop_id" name="selected_palace_prop_id" value=""/>
                        <input type="hidden" id="selected_ot_prop_id" name="selected_ot_prop_id" value=""/>
                        <input type="hidden" name="btnAddProperty" value="1" />
                        <button type="submit" id="btnAddProperty" class="btn">ADD PROPERTY</button>
                        <!--<input class="btn btn-inline btn-success color-green" style="display:none;" type="submit" name="btnAddProperty" id="btnAddProperty" value="ADD PROPERTY">-->
                    </div>
            </div>
        </div>

        </form>
    </section>
</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;">

    <h4><?php echo $title; ?></h4>
    <p>
    This page allows you to add new properties to the system.
    </p>

</div>
<!-- Fancybox END -->

<style>
.available_api_div,
.remember_agency_div,
.pme_prop_found_tick,
.api_main_div{
    display: none;
}

#pme_prop_tbl_wrapper{
    margin: 0 0 30px 0;
}
.pme_active_col,
.pme_action_col{
    width: 6% !important;
}
.txt_red{
    color: red !important;
}

.prev_next_div .col{
    padding: 0;
}
#prevBtn,
#nextBtn{
    width: 100%;
    border-radius: 0;
}
.btn_add_property_next{
    background: #46c35f;
    color: #fff !important;
    border: solid 1px #46c35f;
}
.btn_add_property_next:hover {
    background: #46c35f;
    color: #fff !important;
    border: solid 1px #46c35f;
}
.btn_add_property_next.btn_force_to_blue{
    background-color: #00a8ff!important;
    color: #fff!important;
    border: solid 1px #00a8ff!important;
}
.prev_next_div .disabled:hover {
    background-color: #dbe4ea !important;
    border-color: #dbe4ea !important;
    color: ##dbe4ea !important;
}

#prevBtn{
    background: #ffffff;
    color: #6c7a86 !important;
    border: solid 1px #d8e2e7;
}
#prevBtn:hover {
    background: #ffffff;
    color: #6c7a86 !important;
    border: solid 1px #d8e2e7;
}
.api_main_div{
    border: solid 2px #14cdeb;
    padding: 26px;
    margin: 10px 0;
    background: #f5f8fa;
}
.pme_logo{
    width: 230px;
}
.pme_prop_select_btn {
    background-color: #14cdeb;
    border: #14cdeb;
}
.green_tick{
    margin-left: 7px;
    color: #46c35f !important;
    width: 25px;
}
.icon_red_x{
    width: 25px;
}

</style>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedcolumns/3.2.6/css/fixedColumns.dataTables.min.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>

<!--<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $this->config->item('gmap_api_key'); ?>&libraries=places&callback=initAutocomplete" async defer></script>-->

<script type="text/javascript">


    function clearAddressDetailsAndLoadedDataFromAPi(){

        // address
        // street number
        jQuery("#address_1").val('');
        // street name
        jQuery("#address_2").val('');
        // suburb
        jQuery("#address_3").val('');
        // state
        jQuery("#state").val('');
        // postcode
        jQuery("#postcode").val('');

        // key number
        jQuery("#key_number").val('');

        // tenants
        jQuery(".tenants-row").remove();

        // landlord
        jQuery("#landlord_firstname").val('');
        jQuery("#landlord_lastname").val('');
        jQuery("#landlord_mobile").val('');
        jQuery("#landlord_landline").val('');
        jQuery("#landlord_email").val('');

    }


    function removeOtherRowsIfConnected(){

        var connected_num = jQuery(".green_tick:visible").length;

        if( connected_num > 0 ){

            jQuery("#pme_prop_tbl tr.pme_prop_row:visible").each(function(){

                var row = jQuery(this);
                var connected = row.find(".green_tick:visible").length;

                if( connected == 0 ){
                    row.hide();
                }

            });

        }

    }

    function restoreAllHiddenRows(){

        jQuery("#pme_prop_tbl tr.pme_prop_row").show();

    }


    function toggleNextButton(status){

        <?php
        if( $enable_PMe == true ){
        ?>

            if( status == 1 ){ // enable next
                jQuery(".btn_add_property_next").prop("disabled",false);
                jQuery(".btn_add_property_next").removeClass("disabled");
            }else{ // disable next
                jQuery(".btn_add_property_next").prop("disabled",true);
                jQuery(".btn_add_property_next").addClass("disabled");
            }

        <?php
        }
        ?>


    }

    function insertNewTenantRow(){

        var htm_content = '<div class="row tenants-row">'+
        '<div class="col-sm-10 col-lg-12">'+
        '<div class="row tenants_div">'+
        '<div class="col-lg-1"><div class="form-group"><input type="checkbox" class="form-control tenant_priority" name="tenant_priority[]" value="0"></div></div>'+
        '<div class="col-lg-2"><div class="form-group"><input type="text" class="form-control tenant_firstname" name="tenant_firstname[]" placeholder="First Name"></div></div>'+
        '<div class="col-lg-2"><div class="form-group"><input type="text" class="form-control tenant_lastname"  name="tenant_lastname[]" placeholder="Last Name" ></div></div>'+
        '<div class="col-lg-2"><div class="form-group"><input type="text" class="form-control tenant_mobile"  name="tenant_mob[]" ></div></div>'+
        '<div class="col-lg-2"><div class="form-group"><input type="text" class="form-control tenant_ph phone-with-code-area-mask-input"  name="tenant_ph[]" ></div></div>'+
        '<div class="col-lg-2"><div class="form-group"><input type="text" class="form-control tenant_email"  name="tenant_email[]" placeholder="Email Address"></div></div>'+
        '<div class="col-lg-1"> <a data-toggle="tooltip" title="Remove" class="del_tenant_row" href="#"><span class="font-icon font-icon-trash"></span></a> </div>'+
        '</div>'+
        '</div>'+
        '</div>';
        $('#tenants-block').append(htm_content);
        phone_mobile_mask();
        mobile_validation();
        phone_validation();

    }

    // remember agency
    function remember_agency(){

        var agency_id = jQuery("#agency").val();
        var remember_agency_id = ( jQuery("#remember_agency").prop("checked") == true )?agency_id:0;

        Cookies.set('remember_agency', remember_agency_id);
    }



    function load_agency_api(agency_id){

        // load API with their activate buttons
        jQuery.ajax({
            type: "POST",
            url: "/properties/getAgencyIntegratedAPI",
            data: {
                agency_id: agency_id
            }
        }).done(function(ret){

            jQuery("#api_buttons_div").html(ret);

            // jQuery(".api_buttons").each(function(){

            //     var api_id_btn = jQuery(this).attr("data-api_id");

            //     if( api_id_btn == 1 ){ // PMe

            //         jQuery(this).click(); // auto select if PMe
            //         var button_txt = jQuery(this).html(); // get PMe Name from button
            //         jQuery(this).remove(); // remove button

            //         // replace with a text and a check icon
            //         jQuery("#api_buttons_div").prepend('<span class="active_api_span">'+button_txt+' <span class="font-icon font-icon-ok ok_tick"></span></span>');

            //     }

            //     if( api_id_btn == 4 ){ // PMe

            //         jQuery(this).click(); // auto select if PMe
            //         var button_txt = jQuery(this).html(); // get PMe Name from button
            //         jQuery(this).remove(); // remove button

            //         // replace with a text and a check icon
            //         jQuery("#api_buttons_div").prepend('<span class="active_api_span">'+button_txt+' <span class="font-icon font-icon-ok ok_tick"></span></span>');

            //     }


            // });


        });

    }



    jQuery('document').ready(function(){

        // initAutocomplete();


        // auto select remembered agency script
        var remember_agency_id = parseInt(Cookies.get('remember_agency'));
        if(  remember_agency_id > 0  ){

            setTimeout(function(){

                jQuery("#remember_agency").prop("checked",true);
                jQuery("#agency").val(remember_agency_id).change();

            }, 1000);


        }


        // load PMe on address fields
        jQuery(".api_main_div").on("click",".pme_prop_select_btn",function(){

            var select_btn = jQuery(this);
            var container = select_btn.parents("td:first");

            var pme_prop_id = container.find('.pme_prop_id').val();
            var street_unit = container.find('.pme_addr_unit').val();
            var street_num = container.find('.pme_addr_number').val();
            var street_name = container.find('.pme_addr_street').val();
            var suburb = container.find('.pme_addr_suburb').val();
            var state = container.find('.pme_addr_state').val();
            var postcode = container.find('.pme_addr_postalcode').val();
            var owner_contact_id = container.find('.pme_prop_owner_contact_id').val();
            var tenants_contact_id = container.find('.pme_prop_tenants_contact_id').val();
            var api_platform = container.find('.api_platform').val();
            var api_owner_code = container.find('.api_owner_code').val();

            var street_arr = [];
            var msg = '';

            var key = container.find('.pme_addr_key').val();
            var street = '';
            var agency_id = jQuery("#agency").val();

            // confirm disconnect agency
            swal({
                title: "Matched",
                text: "Do you want to connect this Property?",
                type: "success",
                showCancelButton: true,
                cancelButtonText: "Cancel!",
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes",
                closeOnConfirm: true
            },
            function(isConfirm) {

                if (isConfirm) { // yes

                    jQuery("#selected_pme_prop_id").val("");
                    jQuery("#selected_palace_prop_id").val("");

                     // join unit and streen num
                    if( street_unit !='' ){
                        street_arr.push(street_unit);
                    }
                    if( street_num !='' ){
                        street_arr.push(street_num);
                    }
                    street = street_arr.join('/');

                    if (parseInt(api_platform) == 1) {
                        jQuery("#selected_pme_prop_id").val(pme_prop_id);
                    }
                    if (parseInt(api_platform) == 4) {
                        jQuery("#selected_palace_prop_id").val(pme_prop_id);
                    }

                    // repopulate address
                    jQuery("#address_1").val(street);
                    jQuery("#address_2").val(getStreetAbrvFullName(street_name));
                    jQuery("#address_3").val(suburb);
                    jQuery("#state").val(state);
                    jQuery("#postcode").val(postcode);

                    toggleNextButton(1); // enable next button


                    // additional info
                    // key #
                    if( key != '' ){
                        jQuery("#key_number").val(key);
                        msg += "Key Number Loaded\n";
                    }

                    if (parseInt(api_platform) == 1) {
                        // load tenants
                        jQuery("#load-screen").show();
                        jQuery.ajax({
                            type: "POST",
                            url: "/properties/get_pme_data",
                            data: {
                                tenants_contact_id: tenants_contact_id,
                                owner_contact_id: owner_contact_id,
                                agency_id: agency_id
                            }
                        }).done(function(ret){

                            jQuery("#load-screen").hide();

                            // $('#pme_prop_tbl > tbody  > tr').each(function(index, tr) {
                            //     var tdc = $(this).find("td:nth-child(3)");
                            //     if (parseInt(tdc.find("button").attr('sel-id')) == 1) {
                            //         tdc.find("button").prop('disabled', false);
                            //     }
                            // });

                            if( ret != '' ){

                                // parse json string to js object
                                var pme_data_json = JSON.parse(ret);


                                if (typeof pme_data_json.tenants !== 'undefined') {
                                    // parse tenant json
                                    var tenant_json = JSON.parse(pme_data_json.tenants);
                                    $('#tenants-block').html("");

                                    // loop tenants
                                    if( tenant_json.ContactPersons.length > 0 ){

                                        for( var i=0; i<tenant_json.ContactPersons.length; i++ ){

                                            // pre-fill tenants details
                                            insertNewTenantRow();
                                            jQuery(".tenant_firstname:last").val(tenant_json.ContactPersons[i].FirstName);
                                            jQuery(".tenant_lastname:last").val(tenant_json.ContactPersons[i].LastName);
                                            jQuery(".tenant_mobile:last").val(tenant_json.ContactPersons[i].CellPhone);
                                            jQuery(".tenant_ph:last").val(tenant_json.ContactPersons[i].HomePhone);
                                            jQuery(".tenant_email:last").val(tenant_json.ContactPersons[i].Email);

                                        }

                                        msg += "Tenants Loaded\n";

                                    }
                                }

                                if (typeof pme_data_json.landlord !== 'undefined') {
                                    // parse landlord json
                                    var landlord_json = JSON.parse(pme_data_json.landlord);

                                    // landlord
                                    if( landlord_json.ContactPersons.length > 0 ){

                                        for( var i=0; i<landlord_json.ContactPersons.length; i++ ){

                                            // pre-fill tenants details
                                            jQuery("#landlord_firstname").val(landlord_json.ContactPersons[i].FirstName);
                                            jQuery("#landlord_lastname").val(landlord_json.ContactPersons[i].LastName);
                                            /*
                                            jQuery("#landlord_mobile").val(landlord_json.ContactPersons[i].CellPhone);
                                            jQuery("#landlord_landline").val(landlord_json.ContactPersons[i].HomePhone);
                                            jQuery("#landlord_email").val(landlord_json.ContactPersons[i].Email);
                                            */

                                        }

                                        msg += "Landlord Loaded\n";

                                    }
                                }
                                // $(select_btn).prop('disabled', true);

                            }


                            if( msg != '' ){
                                swal({
                                    title: "Additional Info Found!",
                                    text: msg,
                                    type: "warning"
                                });
                            }


                        });
                    }

                    if (parseInt(api_platform) == 4) {
                        // load tenants
                        jQuery("#load-screen").show();
                        jQuery.ajax({
                            type: "POST",
                            url: "/palace/get_all_tenant_by_prop_code_via_json",
                            data: {
                                palace_id: pme_prop_id,
                                agency_id: agency_id,
                                api_owner_code: api_owner_code
                            }
                        }).done(function(ret){

                            jQuery("#load-screen").hide();
                            // $('#pme_prop_tbl > tbody  > tr').each(function(index, tr) {
                            //     var tdc = $(this).find("td:nth-child(3)");
                            //     if (parseInt(tdc.find("button").attr('sel-id')) == 4) {
                            //         tdc.find("button").prop('disabled', false);
                            //     }
                            // });

                            if( ret != '' ){

                                var pme_data_json = JSON.parse(ret);
                                $('#tenants-block').html("");

                                if (typeof pme_data_json.tenants !== 'undefined') {
                                    if (pme_data_json.tenants.length > 0) {
                                        for( var i=0; i<pme_data_json.tenants.length; i++ ){
                                            insertNewTenantRow();
                                            jQuery(".tenant_firstname:last").val(jQuery.isEmptyObject(pme_data_json.tenants[i].TenancyTenants.DetailedTenant.TenantFirstName) ? "" : pme_data_json.tenants[i].TenancyTenants.DetailedTenant.TenantFirstName);
                                            jQuery(".tenant_lastname:last").val(jQuery.isEmptyObject(pme_data_json.tenants[i].TenancyTenants.DetailedTenant.TenantLastName) ? "" : pme_data_json.tenants[i].TenancyTenants.DetailedTenant.TenantLastName);
                                            jQuery(".tenant_mobile:last").val(jQuery.isEmptyObject(pme_data_json.tenants[i].TenancyTenants.DetailedTenant.TenantPhoneMobile) ? "" : pme_data_json.tenants[i].TenancyTenants.DetailedTenant.TenantPhoneMobile);
                                            jQuery(".tenant_ph:last").val(jQuery.isEmptyObject(pme_data_json.tenants[i].TenancyTenants.DetailedTenant.TenantPhoneHome) ? "" : pme_data_json.tenants[i].TenancyTenants.DetailedTenant.TenantPhoneHome);
                                            jQuery(".tenant_email:last").val(jQuery.isEmptyObject(pme_data_json.tenants[i].TenancyTenants.DetailedTenant.TenantEmail) ? "" : pme_data_json.tenants[i].TenancyTenants.DetailedTenant.TenantEmail);
                                        }
                                        msg += "Tenants Loaded\n";
                                    }
                                }

                                if (typeof pme_data_json.landlord !== 'undefined') {
                                    if (pme_data_json.landlord.length > 0) {
                                        for( var i=0; i<pme_data_json.landlord.length; i++ ){
                                            jQuery("#landlord_firstname").val(jQuery.isEmptyObject(pme_data_json.landlord[i].OwnerFirstName) ? "" : pme_data_json.landlord[i].OwnerFirstName);
                                            jQuery("#landlord_lastname").val(jQuery.isEmptyObject(pme_data_json.landlord[i].OwnerLastName) ? "" : pme_data_json.landlord[i].OwnerLastName);
                                            /*
                                            jQuery("#landlord_mobile").val(jQuery.isEmptyObject(pme_data_json.landlord[i].CellPhone) ? "" : pme_data_json.landlord[i].CellPhone);
                                            jQuery("#landlord_landline").val(jQuery.isEmptyObject(pme_data_json.landlord[i].OwnerPhoneHome) ? "" : pme_data_json.landlord[i].OwnerPhoneHome);
                                            jQuery("#landlord_email").val(jQuery.isEmptyObject(pme_data_json.landlord[i].OwnerEmail1) ? "" : pme_data_json.landlord[i].OwnerEmail1);
                                            */
                                        }
                                        msg += "Landlord Loaded\n";
                                    }
                                }
                                // $(select_btn).prop('disabled', true);

                            }

                            if( msg != '' ){
                                swal({
                                    title: "Additional Info Found!",
                                    text: msg,
                                    type: "warning"
                                });
                            }


                        });
                    }

                }

            });


        });


        // load Ourtradie on address fields
        jQuery(".api_main_div").on("click",".ot_prop_select_btn",function(){

        var select_btn = jQuery(this);
        var container = select_btn.parents("td:first");

        var ot_prop_id = container.find('.ot_prop_id').val();
        //alert(ot_prop_id);
        //return false;

        var address = container.find('.ot_addr_unit').val();
        var suburb = container.find('.ot_addr_suburb').val();
        var state = container.find('.ot_addr_state').val();
        var postcode = container.find('.ot_addr_postalcode').val();

        var agency_name = container.find('.ot_agency_contact_name').val();
        var agency_email = container.find('.ot_agency_contact_email').val();
        var agency_mobile = container.find('.ot_agency_contact_mobile').val();

        var tenant_name = container.find('.ot_tenant_contact_name').val();
        var tenant_email = container.find('.ot_tenant_contact_email').val();
        var tenant_mobile = container.find('.ot_tenant_contact_mobile').val();

        var api_platform = container.find('.api_platform').val();

        var street_arr = [];
        var msg = '';

        var key = container.find('.pme_addr_key').val();
        var street = '';
        var agency_id = jQuery("#agency").val();

            // confirm disconnect agency
            swal({
                title: "Matched",
                text: "Do you want to connect this Property?",
                type: "success",
                showCancelButton: true,
                cancelButtonText: "Cancel!",
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes",
                closeOnConfirm: true
            },
            function(isConfirm) {

                if (isConfirm) { // yes

                    jQuery("#selected_pme_prop_id").val("");
                    jQuery("#selected_palace_prop_id").val("");
                    jQuery("#selected_ot_prop_id").val("");

                    if (parseInt(api_platform) == 1) {
                        jQuery("#selected_pme_prop_id").val(pme_prop_id);
                    }
                    if (parseInt(api_platform) == 4) {
                        jQuery("#selected_palace_prop_id").val(pme_prop_id);
                    }
                    if (parseInt(api_platform) == 6) {
                        jQuery("#selected_ot_prop_id").val(ot_prop_id);
                    }

                    //  TODO: testing
                    //console.log('===== ot_prop_id: ', ot_prop_id)
                    jQuery("#selected_ot_prop_id").val(ot_prop_id);

                    // repopulate address
                    jQuery("#address_1").val(address);
                    jQuery("#address_3").val(suburb);
                    jQuery("#state").val(state);
                    jQuery("#postcode").val(postcode);

                    toggleNextButton(1); // enable next button


                    // additional info
                    // key #
                    if( key != '' ){
                        jQuery("#key_number").val(key);
                        msg += "Key Number Loaded\n";
                    }

                    if (parseInt(api_platform) == 6) {
                        // load tenants
                        jQuery("#load-screen").show();

                        jQuery(".tenant_firstname:last").val(tenant_name);
                        jQuery(".tenant_mobile:last").val(agency_mobile);
                        jQuery(".tenant_email:last").val(agency_email);

                        jQuery("#landlord_firstname").val(agency_name);
                        jQuery("#landlord_mobile").val(agency_mobile);
                        jQuery("#landlord_email").val(agency_email);

                        jQuery("#load-screen").hide();

                        if( msg != '' ){
                            swal({
                                title: "Additional Info Found!",
                                text: msg,
                                type: "warning"
                            });
                        }

                        var ourtradie_tenants_json = jQuery('.ot_tenants_lists').val()

                        if (typeof ourtradie_tenants_json !== 'undefined') {
                            // parse tenant json
                            var tenant_json = JSON.parse(ourtradie_tenants_json);
                            $('#tenants-block').find('.tenants-block-row').html("");

                            // loop tenants
                            if( tenant_json.length > 0 ){

                                for( var i=0; i<tenant_json.length; i++ ){

                                    // pre-fill tenants details
                                    insertNewTenantRow();
                                    jQuery(".tenant_firstname:last").val(tenant_json[i].Name);
                                    jQuery(".tenant_mobile:last").val(tenant_json[i].Mobile);
                                    jQuery(".tenant_email:last").val(tenant_json[i].Email);

                                }
                                msg += "Tenants Loaded\n";

                            }
                        }


                    }

                }

            });

        });



         //success/error message sweel alert pop  start
        <?php if($this->session->userdata('gherx_msg')){ ?>
            swal({
                title: "Success!",
                text: '<?php echo str_replace("'", "\'", $this->session->userdata('gherx_msg')) ?>',
                type: "success",
                html: true
            });
        <?php
        unset($_SESSION['gherx_msg']);
        }else if($this->session->userdata('gherx_error_msg')){
        ?>
            swal({
                    title: "Error!",
                    text: "<?php echo $this->session->userdata('gherx_error_msg') ?>",
                    type: "error",
                    confirmButtonClass: "btn-danger"
                });
        <?php
        unset($_SESSION['gherx_error_msg']);
        }
        ?>
        //success/error message sweel alert pop  end


        //currently vacant
        jQuery('#prop_vacant').change(function(){
            var thisVal = jQuery(this).val();
            //if(thisVal == '1'){
            if(thisVal != ''){
                jQuery('.vacant_from_to').show();

                if(thisVal==1){
                    jQuery("#datepicker_txt").text('When is the property vacant from and to?'); // dynamic text
                    $('#datepicker_vacantF').attr('placeholder','Vacant From *').addClass('g_req');
                    $('#datepicker_vacantT').attr('placeholder','Vacant To *').addClass('g_req');
                }else if(thisVal==0){
                    jQuery("#datepicker_txt").text('When does the job need to be completed by?'); // dynamic text
                    $('#datepicker_vacantF').attr('placeholder','Start Date *').addClass('g_req');;
                    $('#datepicker_vacantT').attr('placeholder','End Date *').addClass('g_req');;
                }
            }else{
                jQuery('.vacant_from_to').hide();
                jQuery('#datepicker_vacantF').val('').removeClass('g_req');
                jQuery('#datepicker_vacantT').val('').removeClass('g_req');
            }
        })

        //Other Supplier Job Tweak
        /* remove by gherx > removed field 
        $('#other_supplier_job').change(function(){

            var thisval = $(this).val();

            if(thisval==1){ //Yes
                $('.other_supplier_job_date_box').slideDown();
            }else{ //No
                $('.other_supplier_job_date_box').slideUp();
                $('#other_supplier_job_date').val("");
            }

        })
        */


        // add/delete tenant buttons -------------------------------------------
        jQuery('#add_tenant_row').on('click',function(e){

            e.preventDefault();
            /*
            var rowLength = $('#tenants-block').find('.tenants-row:visible');
            $('#tenants-block').find('.tenants-row:visible').next('.tenants-row:hidden').show();
            if(rowLength.length == 3){
                $('#add_tenant_row').hide();
            }*/
            insertNewTenantRow();
        });

        // DELETE tenants row
        jQuery(document).on('click','.del_tenant_row',function(e){

            e.preventDefault();
            var obj = $(this);
            obj.parents('.tenants-row').remove();

        });
        // add tenant buttons END -------------------------------------------




        // remember agency
        jQuery("#remember_agency").change(function(){

            remember_agency();

        });


        //ON AGENCY DROPDOWN CHANGED
        $('#agency').change(function(){

            //ENABLE this when PMe is ready
            var agency_id = $(this).val();
            var fg = $(this).find('option:selected').attr('data-fg');
            var allow_pm = $(this).find('option:selected').attr('data-allow_pm');
            var load_api = $(this).find('option:selected').attr('data-load_api');
            var compass_fg = <?php echo $compass_fg; ?>;

            if( agency_id > 0 ){

                // show remember agency
                jQuery(".remember_agency_div").show();

                // show load api
               /* no used on this page
               if( load_api == 1 ){
                    jQuery("#load_api_chk").prop("checked",true);
                }else{
                    jQuery("#load_api_chk").prop("checked",false);
                }*/


                <?php
                if( $enable_PMe == true ){
                ?>
                jQuery(".available_api_div").show();
                <?php
                }
                ?>

                //if allow pm - display property manager dropdown
                if(allow_pm == 1){
                    get_pm(agency_id);
                    $('.pm_div').show();
                    $('.hid_allow_pm').val(1);
                }else{
                    $('.pm_div').hide();
                    $('.hid_allow_pm').val(0);
                }

                //hide/show compass index
                if(fg == compass_fg){
                    jQuery("#compass_index_num_div").show();
                }else{
                    jQuery("#compass_index_num_div").hide();
                }

                //check dha
                check_dha(fg);

                //get agency services
                get_agency_services(agency_id);

                <?php
                if( $enable_PMe == true ){
                ?>
                    if( agency_id > 0 && load_api == 1 ){

                       // load_agency_api(agency_id);

                    }
                <?php
                }
                ?>

            }else{
                jQuery(".available_api_div").hide();
                jQuery(".remember_agency_div").hide();
            }

            remember_agency();

        });


        // toggle load api
        jQuery("#load_api_chk").change(function(){

            var agency_id = jQuery("#agency").val();
            var load_api = ( jQuery(this).prop("checked") == true )?1:0;

            // load API buttons
            jQuery.ajax({
                type: "POST",
                url: "/properties/ajax_toggle_load_api",
                data: {
                    agency_id: agency_id,
                    load_api: load_api
                }
            }).done(function(ret){

                location.reload();

            });

        });


        // toggle added from property list
        jQuery("#added_from_property_list").change(function(){
            if (jQuery("#added_from_property_list").prop("checked")){
                jQuery("#added_from_property_list").val("1");
            } else {
                jQuery("#added_from_property_list").val("0");
            }
        });


        // load Pme
        jQuery("#api_buttons_div").on("click",".api_buttons",function(){

            var agency_id = jQuery("#agency").val();
            var this_but = jQuery(this);
            var api_id = parseInt(this_but.attr("data-api_id"));

            jQuery("#load-screen").show();

            if (api_id == 1) {
                var api_url = "/properties/ajax_get_pme_properties";
            }else if (api_id == 4) {
                var api_url = "/properties/ajax_get_palace_properties";
            }
            else if (api_id == 6) {
                //alert(api_id);
                //return false;
                var api_url = "/properties/ajax_get_ourtradie_properties";
            }
            jQuery.ajax({
                type: "POST",
                url: api_url,
                data: {
                    agency_id: agency_id,
                    api_id: api_id
                }
            }).done(function(ret){
                //console.log(ret);
                //return false;

                jQuery("#load-screen").hide();
                jQuery(".api_main_div").html("");
                jQuery(".api_main_div").html(ret);

                var button_txt = this_but.html();
                jQuery(".active_api_span").remove();

                jQuery(".api_buttons").each(function(){
                    var api_id_btn = jQuery(this).attr("data-api_id");
                    if( api_id_btn == api_id ){
                        var button_txt = jQuery(this).html();
                        jQuery(this).hide();
                    }else {
                        jQuery(this).show();
                    }
                });
                jQuery("#api_buttons_div").prepend('<span class="active_api_span">'+button_txt+' <span class="font-icon font-icon-ok ok_tick"></span></span>');



            });


        });


        /* disable by gherx > removed short term rental dropdown
        jQuery("#holiday_rental").change(function(){

            var holiday_rental_dom = jQuery(this);
            var holiday_rental = holiday_rental_dom.val();

            // only show on NSW
            if( jQuery("#state").val() == 'NSW' ){

                if( holiday_rental == 1 ){
                    jQuery("#service_garage_div").show();
                }else{
                    jQuery("#service_garage").prop("checked",false);
                    jQuery("#service_garage_div").hide();
                }

            }


        });
        */

        //address autocomplete prevent submit when click keyboard enter button
        $('#fullAdd').keydown(function (e) {
            if (e.which == 13) return false;
        });

        //tenant priority change checkbox value
        $("body").on('click', '.tenant_priority', function() {
            if ($(this).is(":checked") == true) {
                $(this).val(1);
                $(this).attr('checked', 'checked');
            } else {
                $(this).val(0);
                $(this).removeAttr('checked');
            }
        });

    });
    //DOCUMENT READY END





    //check DHA agencies
    function check_dha(fg_id){
        jQuery.ajax({
            type: "POST",
            url: "/property_ajax/property_mod/ajax_check_dha_agencies",
            data: {
                fg_id: fg_id
            }
        }).done(function(ret){

            var is_dha_agency = parseInt(ret);

            if( is_dha_agency == 1 ){
                jQuery("#dha_agencies_fields").show();
            }else{
                jQuery("#dha_agencies_fields").hide();
            }

        });
    }


    //check private fg
    function check_private_fg(fg_id){

        jQuery.ajax({
                type: "POST",
                url: "/property_ajax/property_mod/ajax_check_private_fg",
                data: {
                    fg_id: fg_id
                }
            }).done(function(ret){

                var is_dha_private = parseInt(ret);

                if( is_dha_private == 1 ){
                    jQuery("#is_private_fg").val(1);
                    jQuery(".ll_req").show();
                }else{
                    jQuery(".ll_req").hide();
                    jQuery("#is_private_fg").val(0);
                }

            });

    }

    function get_pm(agency_id){

        jQuery.ajax({
            type: "POST",
            url: "/property_ajax/property_mod/get_property_manager_by_agency_id",
            data: {
                agency_id: agency_id
            }
        }).done(function(ret){
            $('#pm').html(ret);
        });

    }

    function get_agency_services(agency_id){

        <?php if(!$this->session->flashdata('status')){ ?>
            $('#load-screen').show();
        <?php } ?>

        jQuery.ajax({
            type: "POST",
            url: "/property_ajax/property_mod/get_agency_services_for_sales_property",
            data: {
                agency_id: agency_id
            }
        }).done(function(ret){
            $('#services_ajax_block').html(ret);
            $('#load-screen').hide();
        });

    }


     //GOOGLE AUTO COMPLETE--------------------------------------------
     // google map autocomplete
    var placeSearch, autocomplete;

    // test
    var componentForm = {
        route: {
            'type': 'long_name',
            'field': 'address_2'
        },
        administrative_area_level_1: {
            'type': 'short_name',
            'field': 'state'
        },
        postal_code: {
            'type': 'short_name',
            'field': 'postcode'
        }
    };

    function initAutocomplete() {

        //console.log('autocomplete start!');
        // Create the autocomplete object, restricting the search to geographical
        // location types.

        <?php if( $this->config->item('country') ==1 ){ ?>
            var cntry = 'au';
        <?php }else{ ?>
            var cntry = 'nz';
        <?php } ?>


        var options = {
            types: ['geocode'],
            componentRestrictions: {
                country: cntry
            }
        };

        var input = document.getElementById('fullAdd');

        autocomplete = new google.maps.places.Autocomplete(input, options);

        // When the user selects an address from the dropdown, populate the address
        // fields in the form.
        autocomplete.addListener('place_changed', fillInAddress);

    }

    function fillInAddress() {

        restoreAllHiddenRows();
        clearAddressDetailsAndLoadedDataFromAPi();

        if( typeof table !== 'undefined'){
            // show PMe table
            jQuery(".api_main_div").show();
        }


        // Get the place details from the autocomplete object.
        var place = autocomplete.getPlace();

        console.log('google address api returned object:');
        console.log(place);

        // prefill address
        var address_bits_long_obj = {};
        var address_bits_short_obj = {};
        for (var i = 0; i < place.address_components.length; i++) {

            var addressType = place.address_components[i].types[0];
            var val_long = place.address_components[i]['long_name'];
            var val_short = place.address_components[i]['short_name'];

            address_bits_long_obj[addressType] = val_long;
            address_bits_short_obj[addressType] = val_short;

        }

        console.log('long address:');
        console.log(address_bits_long_obj);

        console.log('short address:');
        console.log(address_bits_short_obj);

        /*
        cannot rely on google address street, it returns incorrect/incomplete street number.
        example:
         10/2 Yulestar St - autocomplete suggestion
         2 Yulestar St - goole address returned object
        */
        // street number
        var fullAdd = jQuery("#fullAdd").val();
        var ac2 = fullAdd.split(" ");
        var street_number_full = ac2[0]; // using split address

        var street_number_full_replace = street_number_full.replace(/-/gi, " "); // remove -
        var street_number_full_replace = street_number_full_replace.replace(/\//gi, " "); // remove /
        var street_number_full_split = street_number_full_replace.split(" ");

        //property data
        var complete_address = '';

        //var street_unit =  address_bits_long_obj.subpremise;
        //var street_number =  address_bits_long_obj.street_number;

        var street_unit =  street_number_full_split[0];
        var street_number =  street_number_full_split[1];

        var street_name = clearStreetName(address_bits_long_obj.route);
        var suburb = place.vicinity;
        var state = address_bits_short_obj.administrative_area_level_1;
        var postcode = address_bits_long_obj.postal_code;
        var dup_msg = '';
        var selected_agency_id = $('#agency').val();
        var api_platform = jQuery(".api_platform").val();
        console.log("API Platform: "+api_platform);

        /*
        cannot use google address returned object, some address has different suburb and postcode but points to the same address, must be border issues
        example:
        10/2 Yulestar St Hamilton QLD 4007
        10/2 Yulestar St, Albion QLD 4010
        same property, trying search that up on google map
        */

        if(api_platform == 6){
            var fullAdd_replace = ''
            if (fullAdd && fullAdd.split(',').length) fullAdd_replace = fullAdd.split(',')[0]
            var fullAdd_trim = fullAdd_replace.trim();
            var fullAdd_cleared = (clearStreetName(fullAdd_trim)).trim();
        }

        else{
            var fullAdd_replace = fullAdd.replace(/,/gi, ""); // remove commas
            fullAdd_replace = fullAdd_replace.replace(/Australia/gi, ""); // remove country
            fullAdd_replace = fullAdd_replace.replace(/New Zealand/gi, ""); // remove country
            //fullAdd_replace = fullAdd_replace.replace(/QLD/gi, ""); // remove QLD state
            fullAdd_replace = fullAdd_replace.replace(/-/gi, "/"); // replace - to /
            var fullAdd_trim = fullAdd_replace.trim();
            var fullAdd_cleared = (clearStreetName(fullAdd_trim)).trim();
        }

        var search_result_count = 0;
        var dt_page_info = '';

        console.log("fullAdd cleared: "+fullAdd_cleared);


        if( fullAdd_cleared != '' ){

            if( typeof table !== 'undefined'){

                 // search using datatable
                table.search(fullAdd_cleared).draw(); // datatable search
                removeOtherRowsIfConnected();
                dt_page_info = table.page.info()
                search_result_count = dt_page_info.recordsDisplay;

            }



            //console.log("search result: "+JSON.stringify(dt_page_info))
            //console.log("recordsDisplay: "+search_result_count);

            // duplicate property check
            jQuery("#load-screen").show();
            jQuery.ajax({
                type: "POST",
                url: "/properties/check_property_duplicate",
                data: {
                    complete_address: complete_address,
                    address_1: street_number_full,
                    address_2: address_bits_long_obj.route,
                    address_3: suburb,
                    state: state,
                    postcode: postcode
                },
                dataType: 'json',
                success: function(data){

                    jQuery("#load-screen").hide();
                    var crm_link = "<?php echo $this->config->item('crm_link') ?>/view_property_details.php?id="+data.property_id;
                    var agency_txt = '';

                    if(data.match == 1){ // duplicate found

                        if(data.agency_id == selected_agency_id){ //duplicate property is is already in your agency
                            agency_txt = 'this agency';
                        }else{ // duplicate property is in another agency
                            agency_txt = data.agency_name;
                        }

                        jQuery("#duplicate_prop_msg").html('<a href="javascript:void(0);" class="txt_red"><span class="fa fa-exclamation-triangle"></span></a> This property already exists with '+agency_txt+'. <a target="_blank" href="'+crm_link+'" class="txt_red">View Property Here</a>');

                        toggleNextButton(0); // disable next button

                    }else{  // not duplicate


                        jQuery("#duplicate_prop_msg").html(''); // clear duplicate msg

                        if( search_result_count > 0 ){ // has PMe properties

                            jQuery(".pme_prop_found_tick").show();
                            toggleNextButton(0); // disable next button

                        }else{ // no PMe properties

                            // street number
                            jQuery("#address_1").val(street_number_full);
                            // street name
                            jQuery("#address_2").val(address_bits_long_obj.route);
                            // suburb
                            jQuery("#address_3").val(place.vicinity);
                            // state
                            jQuery("#state").val(address_bits_short_obj.administrative_area_level_1);
                            // postcode
                            jQuery("#postcode").val(address_bits_long_obj.postal_code);
                            toggleNextButton(1);

                        }

                    }
                },
                error: function(xhr, status, error) {
                    console.log(status, error);

                    jQuery("#load-screen").hide();

                    swal({
                        title: "Something went wrong!",
                        text: "Unknown error occurred. Please try again.",
                        type: "warning"
                    });
                }
            });

        }


    }
    //GOOGLE AUTO COMPLETE END--------------------------------------------



    // TABBING SCRIPT -------------------------------------------------------
    var currentTab = 0; // Current tab is set to be the first tab (0)
    showTab(currentTab); // Display the current tab

    function showTab(n) {
        // This function will display the specified tab of the form ...

        var x = document.getElementsByClassName("ptabs");


        //var satsVal = document.getElementById('sats_info').value;
        var satsVal = jQuery("#sats_info").val();


        x[n].style.display = "block";

        // ... and fix the Previous/Next buttons:
        if (n == 0) {
           $('#prevBtn').css('pointer-events','none');
        } else {
            $('#prevBtn').css('pointer-events','');
        }

        if (n == (x.length - 1)) {
            //document.getElementById("nextBtn").innerHTML = "Submit";
            document.getElementById("nextBtn").style.display = "none";
            document.getElementById("btnAddProperty").style.display = 'inline';
        } else {
            document.getElementById("nextBtn").setAttribute('style','display:block');
            document.getElementById("btnAddProperty").style.display = 'none';
        }

        if(n == 3 && satsVal == 0){
            //go diretly to step 6 (skip 4 and 5)
            document.getElementById("nextBtn").style.display = 'none';
            document.getElementById("btnAddProperty").style.display = 'inline';
            document.getElementById('group_6').style.display = 'block';
            document.getElementById('group_4').style.display = 'none';
            document.getElementById('group_5').style.display = 'none';
        }else{
            document.getElementById('group_6').style.display = 'none';
        }

        if(n==5){
            document.getElementById('group_6').style.display = 'block';
        }

        if (n==1) {
            var fullAdd = jQuery("#fullAdd").val();
                if (fullAdd !== "") {
                toggleNextButton(1);
                var fullAdd_replace = fullAdd.replace(/,/gi, ""); // remove commas
                fullAdd_replace = fullAdd_replace.replace(/Australia/gi, ""); // remove country
                fullAdd_replace = fullAdd_replace.replace(/New Zealand/gi, ""); // remove country
                fullAdd_replace = fullAdd_replace.replace(/-/gi, "/"); // replace - to /
                var fullAdd_trim = fullAdd_replace.trim();
                var fullAdd_cleared = (clearStreetName(fullAdd_trim)).trim();
                if( typeof table !== 'undefined'){
                    table.search(fullAdd_cleared).draw();
                    removeOtherRowsIfConnected();
                    dt_page_info = table.page.info()
                    search_result_count = dt_page_info.recordsDisplay;
                }
            }
        }

        if (n==0) { toggleNextButton(1); }

        // ... and run a function that displays the correct step indicator:
        fixStepIndicator(n)
    }

    function nextPrev(n) {

        // Property upgraded to meet QLD 2022 Legislation visibility toggle
        var state = jQuery("#state").val();

        // This function will figure out which tab to display
        var x = document.getElementsByClassName("ptabs");
        // Exit the function if any field in the current tab is invalid:
        if (n == 1 && !validateForm(currentTab)) return false;
        // Hide the current tab:
        x[currentTab].style.display = "none";
        // Increase or decrease the current tab by 1:
        currentTab = currentTab + n;
        // if you have reached the end of the form... :
        if (currentTab >= x.length) {
            //...the form gets submitted:
            //document.getElementById("regForm").submit();
            return false;
        }

        //console.log("currentTab: "+currentTab);
        if( currentTab == 1 ){ // if step 1, disable button on load
            toggleNextButton(0);
        }

        // Otherwise, display the correct tab:
        showTab(currentTab);
    }

    function validateForm(n) {
        // This function deals with validation of the form fields

        var x, y, i, valid = true, errmsg="";
        x = document.getElementsByClassName("ptabs");
        y = x[currentTab].getElementsByClassName("g_req");

        // A loop that checks every input field in the current tab:
        for (i = 0; i < y.length; i++) {
            // If a field is empty...
            if (y[i].value == "") {
            // add an "invalid" class to the field:
            y[i].className += " invalid";
            // and set the current valid status to false:

                var gh = y[i].getAttribute('data-field');
                errmsg += gh+" must not be empty \n";

            valid = false;
            }else{
                y[i].classList.remove("invalid");
            }
        }

        if(errmsg!=""){
            swal('',errmsg,'error');
            valid = false;
        }

        // If the valid status is true, mark the step as finished and valid:
        if (valid) {

            //set variable if tab is services

            //var satsVal = document.getElementById('sats_info').value;
            var satsVal = jQuery("#sats_info").val();

            if(n == 2 && satsVal ==0){
                document.getElementsByClassName("step")[3].className += " finish";
                document.getElementsByClassName("step")[4].className += " finish";
                ///alert(n);
            }


            document.getElementsByClassName("step")[currentTab].className += " finish";
        }
        return valid; // return the valid status
    }

    function fixStepIndicator(n) {
        // This function removes the "active" class of all steps...
        //var satsVal = document.getElementById('sats_info').value;
        var satsVal = jQuery("#sats_info").val();
        var i, x = document.getElementsByClassName("step");
        for (i = 0; i < x.length; i++) {
            x[i].className = x[i].className.replace(" active", "");
        }
        //... and adds the "active" class to the current step:
       // x[n].className += " active";


        if(n == 3 && satsVal == 0){
            x[5].className += " active";
        }else{
            x[n].className += " active";
        }

        //remove finish class
        if(n==2){
            jQuery('.steps_ul li:nth-child(4)').removeClass('finish');
            jQuery('.steps_ul li:nth-child(5)').removeClass('finish');
        }else if(n == 1){
            jQuery('.steps_ul li:nth-child(3)').removeClass('finish');
        }

        //iff back to 4th tab and sats is =1 > removed 5th finish class
         if(n==3 && satsVal ==1){
            jQuery('.steps_ul li:nth-child(5)').removeClass('finish');
        }

    }
    // TABBING SCRIPT END -------------------------------------------------------




</script>