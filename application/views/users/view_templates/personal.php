
<div role="tabpanel" class="tab-pane fade active show" id="personal-details-tab">

    <div class="row">
        <div class="col-sm-12">
            <div class="py-3">
                <section class="widget widget-reports">
                    <header class="widget-header widget-header-blue">
                        Name
                    </header>
                    <div class="widget-content">
                        <div class="form-row pt-2">
                            <div class="col-sm-6 col-lg-4">
                                <div class="form-group required">
                                    <label for="user-FirstName">First Name</label>
                                    <input type="text" name="user[FirstName]" id="user-FirstName" class="form-control" required data-validation="[NOTEMPTY]" value="<?= set_value('user[FirstName]', $user['FirstName']) ?>" />
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 required">
                                <div class="form-group">
                                    <label for="user-LastName">Last Name</label>
                                    <input type="text" name="user[LastName]" id="user-LastName" class="form-control" required data-validation="[NOTEMPTY]" value="<?= set_value('user[LastName]', $user['LastName']) ?>" />
                                </div>
                            </div>
                            <div class="col-sm-6 col-sm-offset-6 col-lg-4 required">
                                <div class="form-group">
                                    <label for="user-Email">Email</label>
                                    <input type="text" name="user[Email]" id="user-Email" class="form-control" required data-validation="[NOTEMPTY,EMAIL]" value="<?= set_value('user[Email]', $user['Email']) ?>" />
                                    <?php echo form_error('user[Email]', '<small class="text-danger">', '</small>'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="widget widget-reports">
                    <header class="widget-header widget-header-blue">
                        Other IDs
                    </header>
                    <div class="widget-content">
                        <div class="form-row pt-2">
                            <div class="col-sm-6 col-lg-3">
                                <div class="form-group">
                                    <label for="user-StaffID">ID No.</label>
                                    <input type="text" readonly class="form-control" value="<?= $user['StaffID'] ?>" />
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="form-group">
                                    <label for="user-other_key_num">Key No.</label>
                                    <input type="text" name="user[other_key_num]" id="user-other_key_num" class="form-control" value="<?= set_value('user[other_key_num]', $user['other_key_num']) ?>" />
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="form-group">
                                    <label for="user-other_plant_id">Plant ID</label>
                                    <input type="text" name="user[other_plant_id]" id="user-other_plant_id" class="form-control" value="<?= set_value('user[other_plant_id]', $user['other_plant_id']) ?>" />
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="form-group">
                                    <label for="user-other_shirt_size">Shirt Size</label>
                                    <input type="text" name="user[other_shirt_size]" id="user-other_shirt_size" class="form-control" value="<?= set_value('user[other_shirt_size]', $user['other_shirt_size']) ?>" />
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="row">
                    <div class="col-xs-12 col-lg-6">
                        <section class="widget widget-reports">
                            <header class="widget-header widget-header-blue">
                                Dates
                            </header>
                            <div class="widget-content">
                                <div class="form-row pt-2">
                                    <div class="col-sm-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="user-start_date">Start Date</label>
                                            <?= sats_form_input_date([
                                                'variable' => $user,
                                                'post_var_name' => 'user',
                                                'post_field_key' => 'start_date',
                                            ]) ?>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="user-dob">Date of Birth</label>
                                            <?= sats_form_input_date([
                                                'variable' => $user,
                                                'post_var_name' => 'user',
                                                'post_field_key' => 'dob',
                                            ]) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="col-xs-12 col-lg-6">
                        <section class="widget widget-reports">
                            <header class="widget-header widget-header-blue">
                                Debit Card
                            </header>
                            <div class="widget-content">
                                <div class="form-row pt-2">
                                    <div class="col-sm-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="user-debit_card_num">Card no.</label>
                                            <input type="text" name="user[debit_card_num]" id="user-debit_card_num" class="form-control" value="<?= set_value('user[debit_card_num]', $user['debit_card_num']) ?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="user-debit_expiry_date">Expiry Date</label>

                                            <?= sats_form_input_date([
                                                'variable' => $user,
                                                'post_var_name' => 'user',
                                                'post_field_key' => 'debit_expiry_date',
                                            ]) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-lg-6">
                        <section class="widget widget-reports">
                            <header class="widget-header widget-header-blue">
                                Address
                            </header>
                            <div class="widget-content">
                                <div class="form-row pt-2">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="user-address">Full Address</label>
                                            <input type="text" name="user[address]" id="user-address"
                                                class="form-control pac-target-input"
                                                value="<?= set_value('user[address]', $user['address']) ?>"
                                                placeholder="Enter a location"
                                                autocomplete="off"
                                            />
                                            <?php if ($accommodation): ?>
                                            <input type="hidden" name="accommodation[name]" id="accommodation-name" value="<?= $user["FirstName"] ?> <?= $user["LastName"] ?>" />
                                            <input type="hidden" name="accommodation[address]" id="accommodation-address" value="<?= $user["address"] ?>" />
                                            <input type="hidden" name="accommodation[lat]" id="accommodation-lat" value="<?= $accommodation["lat"] ?>" class="input-lat" />
                                            <input type="hidden" name="accommodation[lng]" id="accommodation-lng" value="<?= $accommodation["lng"] ?>" class="input-lng" />
                                            <?php else: ?>
                                            <input type="hidden" name="accommodation[name]" id="accommodation-name" value="<?= $user["FirstName"] ?> <?= $user["LastName"] ?>" />
                                            <input type="hidden" name="accommodation[area]" id="accommodation-area" value="1 Staff" />
                                            <input type="hidden" name="accommodation[address]" id="accommodation-address" value="<?= $user["address"] ?>" />
                                            <input type="hidden" name="accommodation[rate]" id="accommodation-rate" value="0.0" />
                                            <input type="hidden" name="accommodation[comment]" id="accommodation-comment" value="STAFF" />
                                            <input type="hidden" name="accommodation[lat]" id="accommodation-lat" value="0.0" class="input-lat" />
                                            <input type="hidden" name="accommodation[lng]" id="accommodation-lng" value="0.0" class="input-lng" />
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="col-xs-12 col-lg-6">
                        <section class="widget widget-reports">
                            <header class="widget-header widget-header-blue">
                                COVID-19 Vaccination
                            </header>
                            <div class="widget-content">
                                <div class="form-row pt-2">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="vaccination-vaccine_brand">Vaccine Brand</label>
                                            <select name="vaccination[vaccine_brand]" id="vaccination-vaccine_brand" class="form-control">
                                                <option value="">Not Vaccinated</option>
                                                <?php

                                                // add more vaccine brands later
                                                $vaccineBrands = [
                                                    "1" => "AstraZeneca",
                                                    "2" => "Pfizer",
                                                    "3" => "Moderna",
                                                    "4" => "Janssen",
                                                ];
                                                ?>

                                                <?php
                                                    $vaccineSelected = set_value('vaccination[vaccine_brand]', $latestVaccination['vaccine_brand'] ?? "");
                                                ?>
                                                <?php foreach ($vaccineBrands as $vaccineBrandId => $vaccineBrand): ?>
                                                <option value="<?= $vaccineBrandId ?>"
                                                    <?= set_select('vaccination[vaccine_brand]', $vaccineBrandId, $vaccineSelected == $vaccineBrandId) ?>>
                                                    <?= $vaccineBrand ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="hidden" name="vaccination[StaffID]" value="<?= $user["StaffID"] ?>" />
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="vaccination-completed_on">Completed On</label>
                                            <?= sats_form_input_date([
                                                'variable' => $latestVaccination,
                                                'post_var_name' => 'vaccination',
                                                'post_field_key' => 'completed_on',
                                            ]) ?>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-lg-3">
                        <section class="widget widget-reports">
                            <header class="widget-header widget-header-blue">
                                Photo
                            </header>
                            <div class="widget-content">
                                <div class="form-row pt-2">
                                    <div class="col-12 text-center">
                                        <?php
                                        if (!empty($user['profile_pic'])):
                                        ?>
                                        <img width="100%" id="image-user_profile_pic" src="/images/staff_profile/<?= $user['profile_pic'] ?>"
                                            class="d-block mx-auto" style="max-width: 200px;" />
                                        <?php
                                        else:
                                        ?>
                                        <img width="100%" id="image-user_profile_pic"
                                            class="d-block mx-auto" style="display: none; max-width: 200px;" />
                                        <?php
                                        endif;
                                        ?>
                                        <button type="button"
                                            id="button-user_profile_pic"
                                            class="btn btn-primary mt-2 mb-3"
                                        >Choose Photo</button>
                                        <input name="user_profile_pic" type="file" accept="image/*" class="d-none"/>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="col-xs-12 col-lg-6">
                        <section class="widget widget-reports">
                            <header class="widget-header widget-header-blue">
                                In Case of Emergency
                            </header>
                            <div class="widget-content">
                                <div class="form-row pt-2">
                                    <div class="col-sm-6 col-lg-8">
                                        <div class="form-group">
                                            <label for="user-ice_name">Name</label>
                                            <input type="text" name="user[ice_name]" id="user-ice_name" class="form-control" value="<?= set_value('user[ice_name]', $user['ice_name']) ?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="user-ice_phone">Phone</label>
                                            <input type="text" name="user[ice_phone]" id="user-ice_phone" class="form-control" value="<?= set_value('user[ice_phone]', $user['ice_phone']) ?>" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="col-xs-12 col-lg-3">
                        <section class="widget widget-reports">
                            <header class="widget-header widget-header-blue">
                                Others
                            </header>
                            <div class="widget-content">
                                <div class="form-row pt-2 vertical-checkboxes">
                                    <div class="col-12">
                                        <span class="checkbox d-block mb-2">
                                            <input
                                                type="hidden" name="user[display_on_wsr]" value="0"
                                            />
                                            <input
                                                type="checkbox" name="user[display_on_wsr]" id="user-display_on_wsr"
                                                value="1"
                                                <?= set_checkbox("user[display_on_wsr]", 1, set_value("user[display_on_wsr]", $user['display_on_wsr']) == 1) ?>
                                            />
                                            <label for="user-display_on_wsr">Display on Weekly Sales Report</label>
                                        </span>

                                        <span class="checkbox d-block mb-2">
                                            <input
                                                type="hidden" name="user[recieve_wsr]" value="0"
                                            />
                                            <input
                                                type="checkbox" name="user[recieve_wsr]" id="user-recieve_wsr"
                                                value="1"
                                                <?= set_checkbox("user[recieve_wsr]", 1, set_value("user[recieve_wsr]", $user['recieve_wsr']) == 1) ?>
                                            />
                                            <label for="user-recieve_wsr">Receive Weekly Sales Report</label>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <section class="widget widget-reports">
                    <header class="widget-header widget-header-blue">
                        Working Days
                    </header>
                    <div class="widget-content">
                        <div class="form-row pt-2">
                            <?php
                            $dayNames = [
                                'Mon' => 'Monday',
                                'Tue' => 'Tuesday',
                                'Wed' => 'Wednesday',
                                'Thu' => 'Thursday',
                                'Fri' => 'Friday',
                                'Sat' => 'Saturday',
                                'Sun' => 'Sunday',
                            ];
                            ?>
                            <?php foreach($workingDays as $abbr => $selected): ?>
                                <span class="checkbox d-inline mr-2">
                                    <?php
                                    $checked = set_checkbox("working_days[{$abbr}]", 1, $selected == 1);
                                    ?>
                                    <input
                                        type="hidden" name="working_days[<?= $abbr ?>]" value="0"
                                    />
                                    <input
                                        type="checkbox" name="working_days[<?= $abbr ?>]" id="working_days-<?= $abbr ?>"
                                        value="1"
                                        <?= $checked ?>
                                        class="working_days-checkbox"
                                    />
                                    <label for="working_days-<?= $abbr ?>"><?= $dayNames[$abbr] ?></label>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

</div>