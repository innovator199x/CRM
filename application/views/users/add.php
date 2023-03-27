<div class="box-typical box-typical-padding">

	<?php
		// breadcrumbs template
		$bc_items = [
            [
				'title' => "SATS USERS",
				'status' => 'inactive',
				'link' => "/users/index",
            ],
            [
				'title' => $title,
				'status' => 'active',
				'link' => $page_redirect
			],
        ];
		$bc_data['bc_items'] = $bc_items;
		$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	<section>
		<div class="body-typical-body" style="padding-top:25px;">


        <?php
        if( validation_errors() ){ ?>
            <div class="alert alert-danger">
            <?php echo validation_errors(); ?>
            </div>
        <?php
        }
        ?>


		<?php
            $form_attr = array(
                'id' => 'jform'
            );
            echo form_open($page_redirect,$form_attr);
		?>

        <div class="form-group row">



            <div class="col-sm-10" id="sms_temp_left_panel">

            <div class="form-group row">
                    <label class="col-sm-2 form-control-label">First Name <span class="color-red">*</span></label>
                    <div class="col-sm-6">
                        <p class="form-control-static">
                            <input type="text" class="form-control" id="fname" name="fname" data-validation="[NOTEMPTY]" value="<?= set_value('fname', '') ?>" />
                        </p>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Last Name <span class="color-red">*</span></label>
                    <div class="col-sm-6">
                        <p class="form-control-static">
                            <input type="text" class="form-control" id="lname" name="lname" data-validation="[NOTEMPTY]" value="<?= set_value('lname', '') ?>" />
                        </p>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Address</label>
                    <div class="col-sm-6">
                        <p class="form-control-static">
                            <input type="search" class="form-control" id="address" name="address" value="<?= set_value('address', '') ?>" autocomplete="address" />
                            <input type="hidden" name="accommodation[address]" id="accommodation-address" />
                            <input type="hidden" name="accommodation[lat]" id="accommodation-lat" />
                            <input type="hidden" name="accommodation[lng]" id="accommodation-lng" />
                            <input type="hidden" name="accommodation[postcode]" id="accommodation-postcode" />
                        </p>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Birthday</label>
                    <div class="col-sm-6">
                        <p class="form-control-static">
                            <input type="date" name="birthday" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="birthday" value="<?= set_value('birthday', '') ?>" autocomplete="off" />
                        </p>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Debit Card No.</label>
                    <div class="col-sm-6">
                        <p class="form-control-static">
                            <input type="text" class="form-control" id="debit_card" name="debit_card" value="<?= set_value('debit_card', '') ?>" />
                        </p>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Email <span class="color-red">*</span></label>
                    <div class="col-sm-6">
                        <p class="form-control-static">
                            <input type="email" class="form-control" id="email" name="email" data-validation="[NOTEMPTY,EMAIL]" value="<?= set_value('email', '') ?>" />
                            <?php echo form_error('email', '<small class="text-danger">', '</small>'); ?>
                        </p>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Password<span class="color-red">*</span></label>
                    <div class="col-sm-6">
                        <p class="form-control-static">
                            <input type="password" class="form-control" id="password" name="password" autocomplete="new-password" data-validation="[NOTEMPTY]" />
                        </p>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Confirm Password <span class="color-red">*</span></label>
                    <div class="col-sm-6">
                        <p class="form-control-static">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" autocomplete="new-password" data-validation="[NOTEMPTY,V==password]" />
                        </p>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Phone</label>
                    <div class="col-sm-6">
                        <p class="form-control-static">
                            <input type="text" class="form-control" id="phone" name="phone" value="<?= set_value('phone', '') ?>" />
                        </p>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Job Title</label>
                    <div class="col-sm-6">
                        <p class="form-control-static">
                            <input type="text" class="form-control" id="job_title" name="job_title" value="<?= set_value('job_title', '') ?>" />
                        </p>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">User Class <span class="color-red">*</span></label>
                    <div class="col-sm-6">
                        <p class="form-control-static">
                            <select name="user_class" id="user_class" class="form-control" data-validation="[NOTEMPTY]">
                                <option value="">--- Select ---</option>
                                <?php
                                foreach( $staff_classes_sql->result() as $class_row ){ ?>
                                    <option value="<?php echo $class_row->ClassID; ?>" <?= set_select('user_class', $class_row->ClassID, false) ?>><?php echo $class_row->ClassName; ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </p>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Start Date</label>
                    <div class="col-sm-6">
                        <p class="form-control-static">
                            <input name="start_date" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="start_date" type="text" value="<?= set_value('start_date', '') ?>" />
                        </p>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Country Access <span class="color-red">*</span></label>
                    <div class="col-sm-9">
                        <span class="checkbox d-inline-block mb-2">
                            <input
                                type="hidden" name="country_access[0][status]" value="0"
                            />
                            <input
                                type="hidden" name="country_access[0][country_id]" value="1"
                            />
                            <input
                                type="hidden" name="country_access[0][default]" value="<?= $this->config->item("country") == 1 ?>"
                            />
                            <input
                                type="checkbox" name="country_access[0][status]" id="country_access-0-status"
                                value="1"
                                <?= set_checkbox("country_access[0][status]", 1, set_value("country_access[0][status]", $formData['country_access'][0]['status']) == 1 || $this->config->item("country") == 1) ?>
                            />
                            <label for="country_access-0-status">AU</label>
                        </span>
                        <span class="checkbox d-inline-block mb-2">
                            <input
                                type="hidden" name="country_access[1][status]" value="0"
                            />
                            <input
                                type="hidden" name="country_access[1][country_id]" value="2"
                            />
                            <input
                                type="hidden" name="country_access[1][default]" value="<?= $this->config->item("country") == 2 ?>"
                            />
                            <input
                                type="checkbox" name="country_access[1][status]" id="country_access-1-status"
                                value="1"
                                <?= set_checkbox("country_access[1][status]", 1, set_value("country_access[1][status]", $formData['country_access'][1]['status']) == 1 || $this->config->item("country") == 2) ?>
                            />
                            <label for="country_access-1-status">NZ</label>
                        </span>
                    </div>
                </div>
                <?php
                if( $this->config->item('country') == 1 ){ // AU ?>

                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">State <span class="color-red">*</span></label>
                        <div class="col-sm-9">

                            <?php
                            $states_sql = $this->system_model->getStateViaCountry();
                            foreach( $states_sql->result() as $index => $row_state ){ ?>

                                <span class="checkbox d-inline">
                                    <input type="checkbox" name="states[<?= $index ?>]" id="state-<?php echo $row_state->StateID; ?>" class="req_chk menu_staff_class_chk" value="<?php echo $row_state->StateID; ?>" <?= set_checkbox("states[{$index}]", $row_state->StateID, false) ?> />
                                    <label for="state-<?php echo $row_state->StateID; ?>" class="chk_lbl"></label>
                                </span>
                                <label class="form-control-static chk_lbl_txt d-inline"><?php echo $row_state->state; ?></label>

                            <?php
                            }
                            ?>

                        </div>
                    </div>

                <?php
                }
                ?>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Working Days</label>
                    <div class="col-sm-9">

                        <span class="checkbox d-inline">
                            <input type="checkbox" name="working_days_select_all" id="working_days_select_all" class="chk_lbl" />
                            <label class="form-control-static chk_lbl_txt d-inline" for="working_days_select_all">Select ALL</label>
                        </span>

                        <?php
                        $days_arr = [];
                        $days_arr[] = array(
                            'long' => "Monday",
                            'short' => "Mon",
                        );
                        $days_arr[] = array(
                            'long' => "Tuesday",
                            'short' => "Tue",
                        );
                        $days_arr[] = array(
                            'long' => "Wednesday",
                            'short' => "Wed",
                        );
                        $days_arr[] = array(
                            'long' => "Thursday",
                            'short' => "Thu",
                        );
                        $days_arr[] = array(
                            'long' => "Friday",
                            'short' => "Fri",
                        );
                        $days_arr[] = array(
                            'long' => "Saturday",
                            'short' => "Sat",
                        );
                        $days_arr[] = array(
                            'long' => "Sunday",
                            'short' => "Sun",
                        );
                        foreach( $days_arr as $index => $day_arr ){

                        $day_long_name = $day_arr['long'];
                        $day_short_name = $day_arr['short'];
                        ?>

                            <span class="checkbox d-inline mr-3">
                                <input type="checkbox" name="working_days[<?= $index ?>]" id="day-<?php echo $index ?>" class="req_chk menu_staff_class_chk working_days-checkbox" value="<?php echo $day_short_name; ?>" <?= set_checkbox("working_days[{$index}]", $day_short_name, false) ?> />
                                <label for="day-<?php echo $index; ?>" class="chk_lbl"></label>
                            </span>
                            <label class="form-control-static chk_lbl_txt d-inline"><?php echo $day_long_name ?></label>

                        <?php
                        }
                        ?>

                    </div>
                </div>



                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">&nbsp;</label>
                    <div class="col-sm-9">
                        <p class="form-control-static">
                            <button type="submit" class="btn" id="btn_submit">Submit</button>
                        </p>
                    </div>
                    <label class="col-sm-11 form-control-label">&nbsp;</label>

                </div>

            </div>


		</div>

        <?php
        echo form_close();
        ?>


		</div>

	</section>

</div>



<!-- Fancybox Start -->

<!-- ABOUT PAGE -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
		The big brown fox, jumps over the lazy dog
	</p>

</div>

<!-- Fancybox END -->

<style>
.chk_lbl{
    position: relative;
    top: 4px;
    padding: 8px !important;
}
.chk_lbl_txt{
    margin-right: 15px;
}
#jform input[type=search] {
    box-sizing: border-box;
}
</style>


<script>
// google map address autocomplete API
var autocomplete;

function initAutocomplete() {
    // Create the autocomplete object, restricting the search to geographical
    // location types.

    <?php if(  $this->config->item('country') ==1 ){ ?>
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

    var input = document.getElementById('address');

    autocomplete = new google.maps.places.Autocomplete(input, options);

    // When the user selects an address from the dropdown, populate the address
    // fields in the form.
    //autocomplete.addListener('place_changed', fillInAddress);

    autocomplete.addListener("place_changed", () => {
        var place = autocomplete.getPlace();

        $("#accommodation-address").val(place.formatted_address);
        $("#accommodation-lat").val(place.geometry.location.lat);
        $("#accommodation-lng").val(place.geometry.location.lng);

        var postcode = "";

        for (var ac of place.address_components) {
            try {
                if (ac.types.includes("postal_code")) {
                    postcode = ac.long_name;
                    break;
                }
            }
            catch(e) {}
        }

        $("#accommodation-postcode").val(postcode);
    });

}

jQuery(document).ready(function(){
    // initAutocomplete();

<?php if( $this->session->flashdata('add_user_success') &&  $this->session->flashdata('add_user_success') == 1 ){ ?>
        swal({
            title: "Success!",
            text: "<?= addslashes($this->session->flashdata('message')) ?>",
            html: true,
            type: "success",
            confirmButtonClass: "btn-success",
            showConfirmButton: true,
        });
<?php } else if ($this->session->flashdata('status') && $this->session->flashdata('status') == 'error') { ?>
	swal({
		title: "Error!",
		text: "<?php echo $this->session->flashdata('error_msg') ?>",
		type: "error",
		confirmButtonClass: "btn-danger"
	});
<?php } ?>

    // jquery form validation
	jQuery('#jform').validate({
		submit: {
			settings: {
				inputContainer: '#sms_temp_left_panel .form-group',
				errorListClass: 'form-tooltip-error'
			}
		},
		labels: {
			'fname': 'First Name',
			'lname': 'Last Name',
            'email': 'Email',
            'password': 'password',
            'confirm_password': 'Confirm Password',
            'user_clas': 'User Class'
		}
	});


    // tick all script
	jQuery("#working_days_select_all").change(function(){

        var dom = jQuery(this);
        var chk_sel_all = dom.prop("checked");
        if( chk_sel_all == true ){
            jQuery(".working_days-checkbox").prop("checked",true);
        }else{
            jQuery(".working_days-checkbox").prop("checked",false);
        }

    });


});
</script>