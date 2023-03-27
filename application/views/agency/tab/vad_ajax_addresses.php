<?php  
if($row['address_1']!="" || $row['address_2']!="" || $row['address_3']!=""){
    $prop_full_add = "{$row['address_1']} {$row['address_2']} {$row['address_3']} {$row['state']} {$row['postcode']}"; 
}else{
    $prop_full_add = null; 
}

?>
<input type="hidden" id="agency_addresses_id" value="<?php echo $row['id'] ?>" />
<div class="default_address">
    <div id="group_form">
            <div class="form-group">
                <label class="form-label">Google Address Bar</label>
                <input type='text' name='fullAdd' id='fullAdd' class='form-control vw-pro-dtl-tnt short-fld'  value="<?php echo $prop_full_add; ?>" />
                <input type='hidden' name='edit_id' id="edit_id" />
                <input type='hidden' name='og_fullAdd' id='og_fullAdd' class='form-control vw-pro-dtl-tnt short-fld'  value="<?php echo $prop_full_add;?>" />
            </div>
            <div class="row">
                <div class="col-md-2 columns">
                    <div class="form-group">
                        <label class="form-label">No.</label>
                        <input type='text' name='address_1' id='address_1' value="<?php echo $row['address_1'] ?>" class='form-control vw-pro-dtl-tnt short-fld'>
                    </div>
                </div>
                <div class="col-md-4 columns">
                    <div class="form-group">
                        <label class="form-label">Street</label>
                        <input type='text' name='address_2' id='address_2' value="<?php echo $row['address_2'] ?>" class='form-control vw-pro-dtl-tnt long-fld streetinput'>
                    </div>
                </div>
                <div class="col-md-2 columns">
                    <div class="form-group">
                        <label class="form-label">Suburb</label>
                        <input type='text'  name='address_3' id='address_3' value="<?php echo $row['address_3'] ?>" class='form-control vw-pro-dtl-tnt big-fld'>
                    </div>
                </div>
                <div class="col-md-2 columns">
                    <div class="form-group">
                        <?php if($this->config->item('country') == 1){ ?>
                            <label class="form-label">State</label>
                            <select class="form-control" id="state" name="state">
                                <option value="">----</option>
                                <?php
                                foreach($getCountryState->result_array() as $state){ ?>
                                    <option value='<?php echo $state['state']; ?>' <?php echo ($state['state']==$row['state'])?'selected="selected"':''; ?>><?php echo $state['state']; ?></option>
                                <?php } ?>
                            </select>
                        <?php }else{?>
                            <label class="form-label">Region</label>
                            <input type='text' name='state' id='state' value='<?php echo $row['state']; ?>' class='addinput' />
                        <?php } ?>
                            <input type="hidden" name="og_state" id="og_state" value="<?php echo $row['state'] ?>">
                    </div>
                </div>
                <div class="col-md-2 columns">
                    <div class="form-group">
                        <label class="form-label">Poscode</label>
                        <input class="form-control" name='postcode' id='postcode' type="text" value="<?php echo $row['postcode']; ?>">
                    </div>
                </div>
            </div>
            <div class="row mb-5">
                <?php if(empty($row['id'])){ ?>
                    <div class="col-md-6 columns" id="group-region">
                    <div class="form-group">
                        <label class="form-label">
                                <?php echo $this->customlib->getDynamicRegionViaCountry($this->config->item('country')); ?>
                        </label>
                        <?php
                            if( $row['postcode_region_id']!="" ){ ?>
                                <input class="form-control" readonly="readonly" name='postcode_region_name' id='postcode_region_name' type="text" value="<?php echo $row['postcode_region_name']; ?>">
                                <input class="form-control" name='og_postcode_region_name' id='og_postcode_region_name' type="hidden" value="<?php echo $row['postcode_region_name']; ?>">
                            <?php	
                            }else{
                                echo "NO region set up for this postcode";
                            }
                        ?>
                    </div>
                </div>
                <?php } ?>
                <div class="col-md-12">
                    <label>&nbsp;</label>
                    <button type="button" id="btn_update_key_address" class="btn btn-primary pull-right">Save</button>
                </div>
            </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover table-bordered" id="tbl_key_address" display="none">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Street</th>
                    <th>Suburb</th>
                    <th>State</th>
                    <th>Postal code</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tbl_key_address_body">
            </tbody>
        </table>
    </div>
</div>

<script>

   //------------GOOGLE ADDRESS AUTOCOMPLETE START
   var placeSearch, autocomplete;
    var componentForm2 = {
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
        jQuery("#address_1").val(street_number);

        // suburb
        jQuery("#address_3").val(place.vicinity);

        console.log(place);
    }
    //------------GOOGLE ADDRESS AUTOCOMPLETE END

</script>

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAUHcKVPXD_kJQyPCC-bvTNEPsxC8LAUmA&callback=initAutocomplete&libraries=places" async defer></script>