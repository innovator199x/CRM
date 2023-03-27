
<style>
    .col-mdd-3{
        max-width:15.5%;
    }
    .action_div{
        display: none;
    }
    .heading {
        margin-top:30px;
    }
    button#add_agency {
        margin-top:30px;
        margin-bottom:30px;
    }
    label.addlabel {
        margin-top:20px;
    }
    label[for='submit'] {
        text-align: right;
    }
</style>

<div class="box-typical box-typical-padding">

    <?php
// breadcrumbs template
    $bc_items = array(
        array(
            'title' => 'Sales',
            'link' => "/sales"
        ), array(
            'title' => $title,
            'status' => 'active',
            'link' => "/agency2/view_add_prospects"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>
    <section>
        <div class="body-typical-body" id="zbody">
            <div class="col-md-6">
                <div class="">
                    <?Php
                    $form_attr = array(
                        'id' => 'form-add-prospect',
                        'class' => 'form'
                    );
                    echo form_open_multipart('/agency2/add_prospects_action_form_submit', $form_attr);
                    ?>
                    <div class="row">
                        <label class="addlabel" for="agency_name">Agency Name <span style="color:red">*</span></label>
                        <input required="" class="addinput form-control" type="text" name="agency_name" id="agency_name">
                    </div> 





                    <div class="row">
                        <label class="addlabel" for="fullAdd">Address</label>
                        <input class="addinput form-control" type="text" name="fullAdd" id="fullAdd" placeholder="Enter Address" />
                    </div>



                    <div class="row">
                        <label class="addlabel" for="street_number">Street Number</label>
                        <input class="addinput form-control" type="text" name="street_number" id="street_number">
                    </div> 
                    <div class="row">
                        <label class="addlabel" for="street_name">Street Name</label>
                        <input class="addinput form-control" type="text" name="street_name" id="street_name">
                    </div> 
                    <div class="row">
                        <label class="addlabel" for="suburb">Suburb</label>
                        <input class="addinput form-control" type="text" name="suburb" id="suburb">
                    </div>

                    <?php if (count($state_list)) { ?>
                        <div class="row">
                            <label class="addlabel" for="state">State <span style="color:red">*</span></label>
                            <select required="" class="addinput form-control" name="state" id="state">          
                                <option value="">----</option>
                                <?php
                                foreach ($state_list as $state) {
                                    ?>
                                    <option value='<?php echo $state['state']; ?>'><?php echo $state['state_full_name']; ?></option>
                                    <?php
                                }
                                ?>			  			 
                            </select>
                        </div>
                    <?php } else {
                        ?>


                        <div class="row">
                            <label class="addlabel" for="state">Region</label>
                            <input type="text" name="state" id="state" class="addinput form-control" />
                        </div>

                        <?php
                    }
                    ?>

                    <div class="row"> 
                        <label class="addlabel" for="postcode">Postcode <span style="color:red">*</span></label>
                        <input required="" class="addinput form-control" type="text" name="postcode" id="postcode" onkeypress="return numbersonly(event)" />
                    </div>



                    <div class="row">
                        <label class="addlabel" for="phone">Landline</label>
                        <input class="addinput form-control" type="text" name="phone" id="phone">
                    </div>






                    <div class="row">
                        <label class='addlabel' for='website'>Website</label>
                        <input class="addinput form-control" type="text" name="website" />        
                    </div>





                    <h2 class="heading">Agency Contact</h2>

                    <div class="row">
                        <label class='addlabel' for='totproperties'>Email</label>
                        <input class="addinput form-control" type="text" name="ac_email">
                    </div> 


                    <!-- agency status -->
                    <input type="hidden" name="agen_stat" value="target" />
                    <!-- Staff User: NZ Prospect -->	
                    <?php
                    if (strpos(URL, "dev") == false) { // LIVE
                        if ($this->config->item('country') == 1) { // AU
                            $salesrep = 2195;
                        } else if ($this->config->item('country') == 2) { // NZ
                            $salesrep = 2184;
                        }
                    } else { // DEV
                        if ($this->config->item('country') == 1) { // AU
                            $salesrep = 2141;
                        } else if ($this->config->item('country') == 2) { // NZ
                            $salesrep = 2140;
                        }
                    }
                    ?>
                    <input type="hidden" name="salesrep" value="<?php echo $salesrep; ?>" />




                    <label for="submit">
                        <button class="submitbtnImg btn btn-primary" type="submit" name="add_agency" id="add_agency">Add Agency</button>
                    </label>
                    </form>
                </div>
            </div>

        </div>
    </section>
</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

    <h4><?php echo $title; ?></h4>
    <p>
        This page enables you to add prospects
    </p>

</div>
<!-- Fancybox END -->

<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $this->config->item('gmap_api_key'); ?>&signed_in=true&libraries=places&callback=initAutocomplete" async defer></script>
<script>


// google map autocomplete
                            var placeSearch, autocomplete;

// test
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

                            function initAutocomplete() {
                                // Create the autocomplete object, restricting the search to geographical
                                // location types.

                                var options = {
                                    types: ['geocode'],
                                    componentRestrictions: {country: '<?php echo $this->gherxlib->get_country_iso(); ?>'}
                                };

                                autocomplete = new google.maps.places.Autocomplete(
                                        (document.getElementById('fullAdd')),
                                        options
                                        );

                                // When the user selects an address from the dropdown, populate the address
                                // fields in the form.
                                autocomplete.addListener('place_changed', fillInAddress);
                            }

// [START region_fillform]
                            function fillInAddress() {
                                // Get the place details from the autocomplete object.
                                var place = autocomplete.getPlace();

                                // test
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

                            }
// end google autocomplete

                            function toTitleCase(str) {
                                return str.replace(/\w\S*/g, function (txt) {
                                    return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
                                });
                            }


                            function getRegionViaPostcode(postcode) {

                                if (postcode != "") {
                                    jQuery.ajax({
                                        type: "POST",
                                        url: "ajax_getRegionViaPostCode.php",
                                        data: {
                                            postcode: postcode
                                        },
                                        dataType: 'json'
                                    }).done(function (ret) {
                                        //window.location="/main.php";
                                        jQuery("#postcode_region_name").val(ret.postcode_region_name);
                                        jQuery("#region").val(ret.postcode_region_id);
                                    });
                                }

                            }
                            jQuery(document).ready(function () {




                            });
</script>