<style>
    .card-header{
        text-transform: uppercase;
    }
    .tt_mo{
        text-align: right;
        border-top: 1px solid #00a8ff;
        padding-top: 20px;
    }
    .price_div{
        display: none;
    }
    .add_agency_validation_swal {
        width: auto !important;
    }
</style>

<div class="box-typical-r box-typical-padding-r">

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


    <section class="box-typical box-typical-padding" style="padding-top:20px;padding-bottom:20px;">

        <!-- Form validation error msg -->
        <?php if( validation_errors() ){ ?>
        <div class="alert alert-danger alert-fill alert-close alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
            <?php echo validation_errors(); ?>
        </div>
        <?php } ?>
       <!-- Form validation error msg end -->

        <?php
        $form_attr = array(
            'id' => 'add_agency_form'
        );
        echo form_open('/agency/add_new_agency',$form_attr);
        ?>

        <!-- Agency Status -->
        <section class="card card-blue-fill">
                <header class="card-header">
                    Status
                </header>
                <div class="card-block">
                    <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Agency Status</label>
                        <select class="form-control" name="agen_stat" id="agen_stat">
                            <option value="">Please select</option>
                            <option value='active'>Active</option>
                            <option value='target'>Target</option>
                        </select>
                    </div>
                    </div>
                </div>
        </section>
        <!-- Agency Status end -->
        <div id="active_div" style="display:none">
            <div class="row">
                <div class="col-md-4">

                    <!-- Agency Details -->
                    <section class="card card-blue-fill">
                        <header class="card-header">
                            Agency Details
                        </header>

                        <div class="card-block">
                            <div class="form-group">
                                <label class="form-label">Agency Name <span class="text-red">*</span></label>
                                <input class="form-control" type="text" name="agency_name" id="agency_name">
                            </div>
                            <div class="form-group not_included">
                                <label class="form-label">Legal Name </label>
                                <input class="form-control not_included" type="text" name="legal_name" id="legal_name">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Franchise Group <span class="text-red">*</span></label>
                                <select id="franchise_group" name="franchise_group" class="form-control">
                                    <option value="">Please select</option>
                                    <?php
                                    $fg_sql = $this->agency_model->get_franchise_groups();
                                    foreach($fg_sql->result_array() as $fg){ ?>
                                        <option value="<?php echo $fg['franchise_groups_id'] ?>"><?php echo $fg['name'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group ">
                                <label class="form-label"><?php echo $this->config->item('country')==1?'ABN Number':'GST Number'; ?></label>
                                <input class="form-control" type="text" name="abn" id="abn" />
                            </div>
                            <div class="form-group ">
                                <label class="form-label">Address</label>
                                <input class="form-control" type="text" name="fullAdd" id="fullAdd" placeholder="Enter Address" />
                            </div>
                            <div class="form-group ">
                                <label class="form-label">Street Number</label>
                                <input class="form-control" type="text" name="street_number" id="street_number">
                            </div>
                            <div class="form-group ">
                                <label class="form-label">Street Name</label>
                                <input class="form-control" type="text" name="street_name" id="street_name">
                            </div>
                            <div class="form-group ">
                                <label class="form-label">Suburb</label>
                                <input class="form-control" type="text" name="suburb" id="suburb">
                            </div>
                            <?php if( $this->config->item('country')==1){ ?>
                            <div class="form-group ">
                                <label class="form-label">State <span class="text-red">*</span></label>
                                <select class="form-control" name="state" id="state">
                                    <option value="">----</option>
                                    <?php
                                    $state_sql =  $this->properties_model->getCountryState();
                                    foreach($state_sql->result_array() as $state){ ?>
                                        <option value='<?php echo $state['state']; ?>'><?php echo $state['state_full_name']; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <?php }else{ ?>
                                <div class="form-group ">
                                    <label class="form-label">Region</label>
                                    <input type="text" name="state" id="state" class="form-control" />
                                </div>
                            <?php } ?>

                            <div class="form-group ">
                                <label class="form-label">Postcode <span class="text-red">*</span></label>
                                <input class="form-control" type="text" name="postcode" id="postcode"/>
                            </div>
                            <div class="form-group ">
                                <label class="form-label">Landline </label>
                                <input class="form-control" type="text" name="phone" id="phone">
                            </div>
                            <div class="form-group ">
                                <label class="form-label">Total Properties </label>
                                <input class="form-control" type="text" name="totprop">
                            </div>
                            <div class="form-group ">
                                <label class="form-label">Agency Hours </label>
                                <input class="form-control" type="text" name="agency_hours" />
                            </div>
                            <div class="form-group ">
                                <label class="form-label">Comments </label>
                                <input class="form-control" type="text" name="comment" />
                            </div>
                            <div class="form-group ">
                                <label class="form-label">Agency Specific Notes </label>
                                <input class="form-control" type="text" name="agency_specific_notes" />
                            </div>
                            <div class="form-group ">
                                <label class="form-label">Website </label>
                                <input class="form-control" type="text" name="website" />
                            </div>
                            <div class="form-group target_only" style="display:none;">
                                <label class="form-label">Currently Using </label>
                                <select class="form-control" name="agency_using">
                                    <option value="">Please select</option>
                                    <?php
                                    $au_sql = $this->agency_model->getAgencyUsingByCountry();
                                    foreach($au_sql->result_array() as $au){
                                    ?>
                                        <option value="<?php echo $au['agency_using_id']; ?>"><?php echo $au['name']; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group ">
                                <label class="form-label">Agency Special Deal </label>
                                <textarea class="form-control" name="agency_special_deal" id="agency_special_deal"></textarea>
                            </div>
                        </div>
                    </section>
                    <!-- Agency Details end -->
                </div>


                <div class="col-md-4">
                    <!-- Agency contact -->
                    <section class="card card-blue-fill">
                        <header class="card-header">
                            Agency Contact
                        </header>
                        <div class="card-block">
                            <div class="form-group ">
                                <label class="form-label">First Name </label>
                                <input class="form-control" type="text" name="ac_fname">
                            </div>
                            <div class="form-group ">
                                <label class="form-label">Last Name </label>
                                <input class="form-control" type="text" name="ac_lname">
                            </div>
                            <div class="form-group ">
                                <label class="form-label">Landline </label>
                                <input class="form-control" type="text" name="ac_phone">
                            </div>
                            <div class="form-group ">
                                <label class="form-label">Email </label>
                                <input class="form-control" type="text" name="ac_email">
                            </div>
                            <div class="form-group ">
                                <label class="form-label">Accounts Name </label>
                                <input class="form-control" type="text" name="acc_name">
                            </div>
                            <div class="form-group ">
                                <label class="form-label">Accounts Phone </label>
                                <input class="form-control" type="text" name="acc_phone">
                            </div>
                        </div>
                    </section>
                    <!-- Agency contact end -->

                    <!-- Agency Email -->
                    <section class="card card-blue-fill">
                        <header class="card-header">
                            Agency Email
                        </header>
                        <div class="card-block">
                            <div class="form-group">
                                <label class='form-label' for='totproperties'>Agency Emails <strong>(Reports, Key Sheet)</strong> <br />(one per line) <span class="text-red">*</span></label>
                                <textarea class="form-control wider" name="agency_emails" id="agency_emails"></textarea>
                            </div>
                            <div class="form-group not_included">
                                <label class='form-label' for='totproperties'>Accounts Emails <strong>(Invoices, Certificates)</strong> <br />(one per line) <span style="color:red">*</span></label>
                                <textarea class="form-control wider" name="account_emails" id="account_emails"></textarea>
                            </div>
                        </div>
                    </section>
                    <!-- Agency Email end -->

                    <!-- Preferences -->
                    <section class="card card-blue-fill not_included">
                        <header class="card-header">
                            Preferences
                        </header>
                        <div class="card-block">

                            <div class="form-group row">
                                <div class="col-md-5"><label class="form-label">Individual Property Mangers Receive Certificate & Invoice? </label></div>
                                <div class="col-md-7">
                                    <div class="radio">
                                        <input type="radio" name="allow_indiv_pm_email_cc" id="allow_indiv_pm_email_cc1" value="1">
                                        <label for="allow_indiv_pm_email_cc1">Yes </label>
                                        &nbsp;
                                        <input type="radio" name="allow_indiv_pm_email_cc" id="allow_indiv_pm_email_cc2" value="0" checked="checked">
                                        <label for="allow_indiv_pm_email_cc2">No </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-5"><label class="form-label">Allow Entry Notice?</label></div>
                                <div class="col-md-7">
                                    <div class="radio">
                                        <input type="radio" name="allow_en" id="allow_en1" value="1">
                                        <label for="allow_en1">Yes </label>
                                        &nbsp;
                                        <input type="radio" name="allow_en" id="allow_en2" value="0">
                                        <label for="allow_en2">No </label>
                                        &nbsp;
                                        <input type="radio" name="allow_en" id="allow_en3" value="-1" checked="checked">
                                        <label for="allow_en3">No Response </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-5"><label class="form-label">All New Jobs Emailed to Agency? </label></div>
                                <div class="col-md-7">
                                    <div class="radio">
                                        <input type="radio" name="new_job_email_to_agent" id="new_job_email_to_agent1" value="1">
                                        <label for="new_job_email_to_agent1">Yes </label>
                                        &nbsp;
                                        <input type="radio" name="new_job_email_to_agent" id="new_job_email_to_agent2" value="0" checked="checked">
                                        <label for="new_job_email_to_agent2">No </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-5"><label class="form-label">Subscription Billing? </label></div>
                                <div class="col-md-7">
                                    <div class="radio">
                                        <input type="radio" name="allow_upfront_billing" id="allow_upfront_billing1" value="1">
                                        <label for="allow_upfront_billing1">Yes </label>
                                        &nbsp;
                                        <input type="radio" name="allow_upfront_billing" id="allow_upfront_billing2" value="0" checked="checked">
                                        <label for="allow_upfront_billing2">No </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <!-- Preferences end -->

                    <!-- Sales Rep -->
                    <section class="card card-blue-fill">
                        <header class="card-header">Sales Rep</header>
                        <div class="card-block">
                            <select name="salesrep" class="form-control" id="salesrep">
                                <option value="">-- Select a Sales Rep --</option>
                                <?php
                                    $agency_get_sales_rep = $this->agency_model->agency_get_sales_rep();
                                    foreach($agency_get_sales_rep->result_array() as $salesrep){ ?>
                                    <option value="<?php echo $salesrep['staff_accounts_id'] ?>"><?php echo $salesrep['FirstName'] .' '. $salesrep['LastName'] ?></option>
                                <?php
                                    }
                                ?>
                            </select>
                        </div>
                    </section>
                    <!-- Sales Rep end -->
                </div>

                <div class="col-md-4">

                    <!-- Alarms -->
                    <section class="card card-blue-fill not_included">
                        <header class="card-header">Alarms</header>
                        <div class="card-block">
                            <table id='custom_price_table' class="table main-table">
                                <thead>
                                    <tr>
                                        <th class="bg-red">Type</th>
                                        <th style="width:100px;" class="bg-red">Approved</th>
                                        <th style="width:125px;" class="bg-red">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $alarm_sql = $this->agency_model->get_alarms();
                                        $index = 0;
                                        foreach($alarm_sql->result_array() as $alarm){

                                            $alarm_240v_rf_brooks = 10; // 240v RF
                                            $alarm_240v_rf_cav = 14; // 240vRF(cav)
                                            $is_alarm_240vRF_EP = 22;

                                            $is_alarm_req_for_quotes = ( $alarm['alarm_pwr_id'] == $alarm_240v_rf_brooks || $alarm['alarm_pwr_id'] == $alarm_240v_rf_cav ||  $alarm['alarm_pwr_id'] == $is_alarm_240vRF_EP )?true:false;

                                        ?>
                                        <tr class="tr_main_tt">
                                        <td style="display:none;">
                                            <input type="hidden" class="hid_alarm_pwr_id" name="alarm_pwr_id[]" value="<?php echo $alarm['alarm_pwr_id']; ?>">
                                            <input type="hidden" name="alarm_is_approved[]" class="is_approved" value="0" />
                                            <input type="hidden" name="hid_alrm_pwr_name[]" class="hid_alrm_pwr_name" value="<?php echo $alarm['alarm_pwr']; ?>" />
                                        </td>
                                        <td>
                                            <?php echo $alarm['alarm_pwr']; ?>
                                            <?php echo ( $is_alarm_req_for_quotes == true )?'<strong style="color:red;">(Required for Quotes)</strong>':null; ?>
                                        </td>
                                        <td>
                                            <div class="checkbox">
                                                <input id="<?php echo 'check-'.$index ?>" name="alarm_approve[]" type="checkbox" class="alarm_approve approve <?php echo ( $is_alarm_req_for_quotes == true )?'alarm_req_for_quotes_approve_chk':null; ?>" value="<?php echo $index; ?>">
                                                <label for="<?php echo 'check-'.$index ?>"></label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="price_div">
                                                <div class="input-group">
                                                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                    <input type="text" name="alarm_price[]" class="form-control alarm_price price">
                                                </div>
                                            </div>
                                        </td>
                                        </tr>
                                        <?php
                                            $index++;
                                        }
                                        ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                    <!-- Alarms end -->

                    <!-- Services -->
                    <section class="card card-blue-fill not_included">
                        <header class="card-header">Services</header>
                        <div class="card-block">
                            <table id="custom_price_table" class="table main-table">
                                <thead>
                                    <tr>
                                    <th class="bg-red">Type</th>
                                    <th style="width:100px;" class="bg-red">Approved</th>
                                    <th style="width:125px;" class="bg-red">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $services_sql = $this->agency_model->get_services();
                                        $index = 0;
                                        foreach($services_sql->result_array() as $services){

                                        $sa_ic = 12; // Smoke Alarms (IC)
                                        $is_sa_ic = ( $services['id'] == $sa_ic )?true:false;

                                        ?>
                                    <tr>
                                    <td style="display:none;"><input type="hidden" name="service_id[]" value="<?php echo $services['id']; ?>">
                                        <input type="hidden" name="service_is_approved[]" class="is_approved" value="0"></td>
                                    <td>
                                        <?php echo $services['type']; ?>
                                        <?php echo ( $is_sa_ic == true )?'<strong style="color:red;">(Required for Quotes)</strong>':null; ?>
                                    </td>
                                    <td>
                                        <div class="checkbox">
                                            <input id="<?php echo 'service_approve-check-'.$index ?>" name="service_approve[]" type="checkbox" class="service_approve approve <?php echo ( $is_sa_ic == true )?'sa_ic_chk':null; ?>" value="<?php echo $index; ?>">
                                            <label for="<?php echo 'service_approve-check-'.$index ?>"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="price_div">
                                            <div class="input-group">
                                               <!-- <div class="input-group-prepend"><span class="input-group-text">$</span></div>-->
                                                <!--<input type="text" name="service_price[]" class="form-control service_price price">-->
                                                <?php  
                                                    $agency_default_service_price_where = array('active'=>1, 'service_type'=>$services['id']);
                                                    $agency_default_service_price_q = $this->db->select('price')->from('agency_default_service_price')->where($agency_default_service_price_where)->get()->row();
                                                    echo $agency_default_service_price_q->price;
                                                ?>
                                            </div>
                                        </div>
                                    </td>
                                    </tr>
                                    <?php
                                        $index++;
                                        }
                                        ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                    <!-- Services end -->

                    <!-- Services -->
                    <section class="card card-blue-fill not_included">
                        <header class="card-header">
                        Maintenance Program
                        </header>
                        <div class="card-block">
                            <div class="form-group">
                                <select name="maintenance" class="maintenance form-control" id="maintenance">
                                    <option value=''>None</option>
                                    <?php
                                    $m_sql = $this->agency_model->agency_get_maintenance();
                                    foreach($m_sql->result_array() as $m){?>
                                        <option value='<?php echo $m['maintenance_id']; ?>'><?php echo $m['name']; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>

                            <div id="maintenance_program_div" style="display:none;" class="addproperty">
                                <div class="form-group row">
                                    <div class="col-md-6"><label class="form-label">Apply Surcharge to all Invoices? </label></div>
                                    <div class="col-md-6">
                                        <div class="radio">
                                            <input type="radio" name="m_surcharge" id="m_surcharg1" value="1">
                                            <label for="m_surcharg1">Yes </label>
                                            &nbsp;
                                            <input type="radio" name="m_surcharge" id="m_surcharge2" value="0">
                                            <label for="m_surcharge2">No </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-6"><label class="form-label">Surcharge</label></div>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                            <input type="text" name="m_price" class="form-control m_price price" id="m_price">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-6"><label class="form-label">Display Message on all Invoices?</label></div>
                                    <div class="col-md-6">
                                        <div class="radio">
                                            <input type="radio" name="m_disp_surcharge" id="m_disp_surcharge1" value="1">
                                            <label for="m_disp_surcharge1">Yes </label>
                                            &nbsp;
                                            <input type="radio" name="m_disp_surcharge" id="m_disp_surcharge2" value="0">
                                            <label for="m_disp_surcharge2">No </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-6"><label class="form-label">Invoice Message</label></div>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input type="text" name="m_surcharge_msg" class="form-control" id="m_surcharge_msg">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <!-- Services end -->

                </div>

            </div>


            <!-- submit button -->
            <div class="row">
                <div class="col-md-12">
                    <div class="tt_mo">
                    <button class="btn" type="button" name="add_agency" id="add_agency"">Add Active Agency</button>
                    </div>
                </div>
            </div>
            <!-- submit button end -->

        </div>
        <!-- active_div end -->

        </form>

    </section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;">

    <h4><?php echo $title; ?></h4>
    <p>
    This page allows you to add new agency.
    </p>

</div>
<!-- Fancybox END -->

<script type="text/javascript">

    /** FLASH DATA SUCCESS MESSAGE */
    <?php if ($this->session->flashdata('status') && $this->session->flashdata('status') == 'success') { ?>
        swal({
            title: "Success!",
            text: "<?php echo $this->session->flashdata('success_msg') ?>",
            type: "success",
            confirmButtonClass: "btn-success",
            showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
            timer: <?php echo $this->config->item('timer') ?>
        });
    <?php } else if ($this->session->flashdata('status') && $this->session->flashdata('status') == 'error') { ?>
            swal({
                title: "Error!",
                text: "<?php echo $this->session->flashdata('error_msg') ?>",
                type: "error",
                confirmButtonClass: "btn-danger"
            });
    <?php } ?>
    /** FLASH DATA SUCCESS MESSAGE END */


    //------------GOOGLE ADDRESS AUTOCOMPLETE START
    var placeSearch, autocomplete;
    var componentForm2 = {
        route: {
            'type': 'long_name',
            'field': 'street_name'
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

    let current_state = jQuery('#state').val();
    jQuery("#state").change(function(){
        let state = jQuery(this).val();
        
        if(state!=""){
            if(state == 'QLD'){
                jQuery('#allow_upfront_billing1').prop('checked', true);
                jQuery('#allow_upfront_billing2').prop('checked', false);
                current_state = 'QLD';
            }
            
            if(current_state == 'QLD' && state != 'QLD'){
                jQuery('#allow_upfront_billing2').prop('checked', true);
                jQuery('#allow_upfront_billing1').prop('checked', false);
            }
            if(state != 'QLD'){
                current_state = state;
            }
        }
    });

    function initAutocomplete() {

        <?php if( $this->config->item('country') ==1 ){ ?>
            var cntry = 'au';
        <?php }else{ ?>
            var cntry = 'nz';
        <?php } ?>

        var options = {
            types: ['geocode'],
            componentRestrictions: {country: cntry}
        };

        autocomplete = new google.maps.places.Autocomplete(
            (document.getElementById('fullAdd')),
            options
        );
        autocomplete.addListener('place_changed', fillInAddress);

    }

    function fillInAddress() {

        var place = autocomplete.getPlace();

        for (var i = 0; i < place.address_components.length; i++) {
            var addressType = place.address_components[i].types[0];
            if (componentForm2[addressType]) {
                var val = place.address_components[i][componentForm2[addressType].type];
                document.getElementById(componentForm2[addressType].field).value = val;
            }
        }

        // street name
        var ac = jQuery("#fullAdd").val();
        var ac2 = ac.split(" ");
        var street_number = ac2[0];
        console.log(street_number);
        jQuery("#street_number").val(street_number);

        // suburb
        jQuery("#suburb").val(place.vicinity);

        console.log(place);

        var postcode = jQuery("#postcode").val();
        getRegionViaPostcode(postcode);

        var state = jQuery("#state").val();
        let current_state = jQuery('#state').val();
        var agen_stat = jQuery("#agen_stat").val();
        if( state == 'QLD' ){

            if(agen_stat=='active'){

                // disable 'required for quotes' auto-tick for QLD, bec 'required for quotes' alarm now has 2 options 240v RF brooks or cavius - instructed by Ben T.
                /*
                // state is QLD, auto tick 240v RF alarm and set price as 200
                var alarm_240v_rf_chk_node = jQuery(".alarm_240v_rf_chk");
                var parent_row = alarm_240v_rf_chk_node.parents("tr:first");
                alarm_240v_rf_chk_node.prop("checked",true); // tick it
                parent_row.find(".is_approved").val(1); // mark as approved
                parent_row.find(".price_div").show(); // show price div
                parent_row.find(".alarm_price").val(200); // set price
                */

                // state is QLD, auto tick SA IC service and set price as 119
                var sa_ic_chk_node = jQuery(".sa_ic_chk");
                var parent_row = sa_ic_chk_node.parents("tr:first");
                sa_ic_chk_node.prop("checked",true); // tick it
                parent_row.find(".is_approved").val(1); // mark as approved
                parent_row.find(".price_div").show(); // show price div
                parent_row.find(".service_price").val(119); // set price
                jQuery('#allow_upfront_billing1').prop('checked', true);
                jQuery('#allow_upfront_billing2').prop('checked', false);
                current_state = 'QLD';

            }

        }else{

            /*
            // state is QLD, auto tick 240v RF alarm and set price as 200
            var alarm_240v_rf_chk_node = jQuery(".alarm_240v_rf_chk");
            var parent_row = alarm_240v_rf_chk_node.parents("tr:first");
            alarm_240v_rf_chk_node.prop("checked",false); // tick it
            parent_row.find(".is_approved").val(0); // mark as approved
            parent_row.find(".price_div").hide(); // show price div
            parent_row.find(".alarm_price").val(''); // set price
            */

            // state is QLD, auto tick SA IC service and set price as 119
            var sa_ic_chk_node = jQuery(".sa_ic_chk");
            var parent_row = sa_ic_chk_node.parents("tr:first");
            sa_ic_chk_node.prop("checked",false); // tick it
            parent_row.find(".is_approved").val(0); // mark as approved
            parent_row.find(".price_div").hide(); // show price div
            parent_row.find(".service_price").val(''); // set price
        }

        if(current_state == 'QLD' && state != 'QLD'){
            jQuery('#allow_upfront_billing2').prop('checked', true);
            jQuery('#allow_upfront_billing1').prop('checked', false);
        }

        if(state != 'QLD'){
            current_state = state;
        }

    }
    //------------GOOGLE ADDRESS AUTOCOMPLETE END

    function getRegionViaPostcode(postcode){

        if( postcode!="" ){
            jQuery.ajax({
                type: "POST",
                url: "/agency/ajax_getRegionViaPostCode",
                data: {
                    postcode: postcode
                },
                dataType: 'json'
            }).done(function( ret ) {
                jQuery("#postcode_region_name").val(ret.postcode_region_name);
                jQuery("#region").val(ret.postcode_region_id);
            });
        }

    }

    //validate email
    function validate_email(email){
		var atpos = email.indexOf("@");
		var dotpos = email.lastIndexOf(".");
		if ( atpos<1 || dotpos<atpos+2 || dotpos+2>=email.length ){
		  return false
		}
	}

    // Document ready
    jQuery('document').ready(function(){

        // initAutocomplete(); //init google address autocomplete

        jQuery("#maintenance").change(function(){
            if(jQuery(this).val()!=""){
                jQuery("#maintenance_program_div").slideDown('slow');
            }else{
                jQuery("#maintenance_program_div").slideUp('slow');
            }
        });

        // require approve price script
        jQuery(".approve").click(function(){

            // postcode region auto fill script
            jQuery("#postcode").blur(function(){
                var postcode = jQuery(this).val();
                getRegionViaPostcode(postcode);
            });

            // is approved hidden value
            var state = jQuery(this).prop("checked");
            if(state==true){
                jQuery(this).parents("tr:first").find(".is_approved").val(1);
                jQuery(this).parents("tr:first").find(".price_div").show();
                // add req class for validation
                jQuery(this).parents("tr:first").find(".price").addClass("req");
            }else{
                jQuery(this).parents("tr:first").find(".is_approved").val(0);
                jQuery(this).parents("tr:first").find(".price").val("");
                jQuery(this).parents("tr:first").find(".price_div").hide();
                // add req class for validation
                jQuery(this).parents("tr:first").find(".price").removeClass("req");
            }
        });

        // agency status script
        jQuery("#agen_stat").click(function(){
            $('#load-screen').show(); //show loader
            if(jQuery(this).val()=='active'){
                setTimeout(() => {
                    jQuery("#active_div").show();
                    jQuery(".not_included").show();
                    jQuery(".target_only").hide();
                    jQuery("#add_agency").html("Add Active Agency");
                    $('#load-screen').hide(); //hide loader
                }, 300);
            }else if(jQuery(this).val()=='target'){
                $( ".approve" ).prop( "checked", false ); //removed services and alarms value for target
                $('.price_div').hide();
                $('.price').val('');
                setTimeout(() => {
                    jQuery("#active_div").show();
                    jQuery(".not_included").hide();
                    jQuery(".target_only").show();
                    jQuery("#add_agency").html("Add Target Agency");
                    $('#load-screen').hide(); //hide loader
                }, 300);
            }else{
                setTimeout(() => {
                    jQuery("#active_div").hide();
                    $('#load-screen').hide(); //hide loader
                }, 300);
            }
        });

        jQuery("#add_agency").click(function(){

            var agen_stat = jQuery("#agen_stat").val();
            var agency_name = jQuery("#agency_name").val();
            var franchise_group = jQuery("#franchise_group").val();
            var state = jQuery("#state").val();
            var postcode = jQuery("#postcode").val();
            var region = jQuery("#region").val();
            var agency_emails = jQuery("#agency_emails").val();
            var account_emails = jQuery("#account_emails").val();
            var country = jQuery("#country").val();
            var salesrep = jQuery("#salesrep").val();

            var error = "";
            var flag = 0;
            var submitcount = 0;

            if(agency_name==""){
                error += "Agency Name is required\n";
            }
            if(franchise_group==""){
                error += "Franchise Group is required\n";
            }

            if(state==""){
                var country_state = "<?php echo ($this->config->item('country')==1) ? 'State' : 'Region' ?>";
                error += country_state+" is required\n";
            }else{
                if(agen_stat=='active'){

                    // 'required for quotes' required validation
                    if( state == 'QLD' && jQuery(".alarm_req_for_quotes_approve_chk:checked").length == 0 ){
                        error += "At least one 'Required for Quotes' alarms must be approved\n";
                    }

                }
            }

            if(postcode==""){
                error += "Postcode is required\n";
            }else{
                if(postcode.length<4){
                    error += "Postcode cannot be less than 4 digit \n";
                }
            }

            if(country==""){
                error += "Country is required\n";
            }

            if(salesrep==""){
                error += "Sales rep is required\n";
            }

            if(agen_stat=='active'){

                // agency emails
                if(agency_emails==""){
                    error += "Agency emails are required\n";
                }else{
                    agency_e = agency_emails.split("\n");
                    var email_error = 0;
                    // loop through emails and validate them
                    for (var i=0; i < agency_e.length; i++){
                        // invalid email
                        if(validate_email(agency_e[i])==false){
                            email_error = 1;
                        }
                    }
                    if(email_error==1){
                        error += "One or more of Agency email is invalid format\n";
                    }
                }

                // account emails
                if(account_emails==""){
                    error += "Account emails is required\n";
                }else{
                    account_e = account_emails.split("\n");
                    var email_error = 0;
                    // loop through emails and validate them
                    for (var i=0; i < account_e.length; i++){
                        // invalid email
                        if(validate_email(account_e[i])==false){
                            email_error = 1;
                        }
                    }
                    if(email_error==1){
                        error += "One or more of Account email is invalid format\n";
                    }
                }

                // alarm required
                var alarms_ticked_num = jQuery(".alarm_approve:checked").length;
                if( alarms_ticked_num == 0 ){
                    error += "Alarm is required\n";
                }

                // alarm price
                jQuery(".alarm_price:visible").each(function(){
                    var alarm_price = jQuery(this).val();
                    var tt_alarm_id = $(this).parents('tr.tr_main_tt').find('.hid_alarm_pwr_id').val();
                    var alarm_name = $(this).parents('tr.tr_main_tt').find('.hid_alrm_pwr_name').val();
                   
                    //if( ( !jQuery.inArray(tt_alarm_id, <?php //echo json_encode($this->config->item('alarm_allowed_zero_price')) ?>) > 0 ) && (alarm_price == 0 || alarm_price == '') ){
                      //  error += "Alarm Price is required\n";
                    //}
                    if( ( jQuery.inArray(parseInt(tt_alarm_id), <?php echo json_encode($this->config->item('alarm_allowed_zero_price')) ?>) == -1 ) && (alarm_price == 0 || alarm_price == '') ){
                        
                        error += alarm_name+ " Alarm Price is required\n";

                    }
                });

                // service required
                var alarms_ticked_num = jQuery(".service_approve:checked").length;
                if( alarms_ticked_num == 0 ){
                    error += "Service is required\n";
                }

            }

            if(error!=""){
                //swal('',error,'error');

                swal({
                    title: "",                    
                    text: error,
                    type: "error",		
                    customClass: 'add_agency_validation_swal'
                });

                return false;
            }

            jQuery.ajax({
                type: "POST",
                url: "/agency/ajax_check_agency_duplicate",
                dataType: 'json',
                data: {
                    agency_name: agency_name
                }

            }).done(function( retval ) {
                if(retval.status){
                    swal('','Duplicate Agency Name','error');
                    return false;
                }else{
                    if(submitcount==0){
                        submitcount++;
                        jQuery("#add_agency_form").submit();
                        return false;
                    }else{
                        swal('','Form submission is in progress','error');
                        return false;
                    }
                }
            });

        });



    });
    //Docudment ready end


</script>