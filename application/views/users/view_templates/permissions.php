<div role="tabpanel" class="tab-pane fade active show" id="permissions-tab">

    <div class="row">
        <div class="col-sm-12">
            <div class="py-3">

                <?php if ($accommodation): ?>
                <input type="hidden" name="accommodation[accomodation_id]" value="<?= $accommodation["accomodation_id"] ?>" />
                <?php endif; ?>
                <input type="hidden" name="accommodation[address]" value="<?= $user["address"] ?>" />
                <input type="hidden" name="accommodation[lat]" class="input-lat" />
                <input type="hidden" name="accommodation[lng]" class="input-lng" />
                <input type="hidden" name="accommodation[name]" value="<?= $user["FirstName"] ?> <?= $user["LastName"] ?>" />
                <input type="hidden" name="accommodation[area]" value="1 Staff" />
                <input type="hidden" name="accommodation[rate]" value="0.0" />
                <input type="hidden" name="accommodation[comment]" value="STAFF" />

                <section class="widget widget-reports">
                    <header class="widget-header widget-header-blue">
                        CRM
                    </header>
                    <div class="widget-content">
                        <div class="form-row pt-2">
                            <div class="col-sm-6 col-lg-4">
                                <div class="form-group">
                                    <label for="user-">Status</label>
                                    <select name="user[active]" class="form-control">
                                        <?php
                                            $userActive = set_value('user[active]', $user['active']);
                                        ?>
                                        <option value="1"
                                            <?= set_select('user[active]', 1, $userActive == 1) ?>
                                        >Active</option>
                                        <option value="0"
                                            <?= set_select('user[active]', 0, $userActive == 0) ?>
                                        >Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <div class="form-group">
                                    <label for="user-">Class</label>
                                    <select name="user[ClassID]" class="form-control">
                                        <?php
                                            $classID = set_value('user[ClassID]', $user['ClassID']);
                                        ?>
                                        <?php foreach ($staffClasses as $staffClass): ?>
                                        <option
                                            value="<?= $staffClass['ClassID'] ?>"
                                            <?= set_select('user[ClassID]', $staffClass['ClassID'], $classID == $staffClass['ClassID']) ?>
                                        ><?= $staffClass['ClassName'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <div class="form-group">
                                    <label for="user-sa_position">Position</label>
                                    <input type="text" name="user[sa_position]" id="user-sa_position" class="form-control" value="<?= set_value('user[sa_position]', $user['sa_position']) ?>" />
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <div class="form-group">
                                    <label for="user-other_call_centre">Call Centre</label>
                                    <select name="user[other_call_centre]" class="form-control">
                                        <option value="">---Select---</option>
                                        <?php
                                            $callCentreStaffID = set_value('user[other_call_centre]', $user['other_call_centre']);
                                        ?>
                                        <?php foreach ($callCentres as $callCentre): ?>
                                        <option value="<?php
                                            echo $callCentre['StaffID'];
                                        ?>" <?= set_select('user[other_call_centre]', $callCentre['StaffID'], $callCentreStaffID == $callCentre['StaffID']) ?>>
                                            <?php echo $this->system_model->formatStaffName($callCentre['FirstName'], $callCentre['LastName']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="widget widget-reports fieldset-closable closed">
                    <header class="widget-header widget-header-blue">
                        Update Password
                        <i class="fa fa-caret-square-o-left visibility-off float-right"></i>
                        <i class="fa fa-caret-square-o-down visibility-on float-right"></i>
                    </header>
                    <div class="widget-content visibility-on">
                        <div class="form-row pt-2 align-items-center">
                            <div class="col-sm-6 col-lg-4">
                                <div class="form-group">
                                    <label for="user-Password">New Password</label>
                                    <input type="password" name="user[NewPassword]" id="user-NewPassword" autocomplete="new-password" class="form-control" value="" />
                                    <?php echo form_error('user[NewPassword]', '<small class="text-danger">', '</small>'); ?>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <div class="form-group">
                                    <label for="user-ConfirmPassword">Confirm Password</label>
                                    <input type="password" name="user[ConfirmPassword]" id="user-ConfirmPassword" autocomplete="new-password" class="form-control" value="" data-validation="V==user[NewPassword]" data-validation-message="Confirm Password does not match New Password" />
                                    <?php echo form_error('user[ConfirmPassword]', '<small class="text-danger">', '</small>'); ?>
                                </div>
                            </div>
                            <div class="col-sm-12 col-lg-4 text-center text-lg-right align-middle">
                                <a class="fancybox-clicker" href="#update_password_about_fb" style="font-size: 1.5em;">
                                    <i class="fa fa-question-circle"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="row">
                    <div class="col-4">
                        <section class="widget widget-reports states-fieldset mb-2" >
                            <header class="widget-header widget-header-blue">
                                Country
                            </header>
                            <div class="widget-content">
                                <div class="form-row pt-2" id="states-row">
                                    <?php
                                    $countryAbbr = [
                                        1 => "AU",
                                        2 => "NZ",
                                    ];
                                    $countryAccessWithDefaultValues = [
                                        0 => [
                                            "staff_accounts_id" => $user["StaffID"],
                                            "country_id" => 1,
                                            "default" => $this->config->item("country") == 1,
                                            "status" => $this->config->item("country") == 1,
                                        ],
                                        1 => [
                                            "staff_accounts_id" => $user["StaffID"],
                                            "country_id" => 2,
                                            "default" => $this->config->item("country") == 2,
                                            "status" => $this->config->item("country") == 2,
                                        ],
                                    ];
                                    $countryAccessToUse = [];
                                    $countryIds = array_column($countryAccess, "country_id");
                                    foreach($countryAccessWithDefaultValues as &$ca1) {
                                        $key = array_search($ca1["country_id"], $countryIds);
                                        if ($key != false) {
                                            $countryAccessToUse[] = $countryAccess[$key];
                                        }
                                        else {
                                            $countryAccessToUse[] = $ca1;
                                        }
                                    }
                                    ?>
                                    <?php foreach($countryAccessToUse as $index => $ca): ?>
                                        <span class="checkbox d-inline-block mb-2 mr-2">
                                            <input
                                                type="hidden" name="country_access[<?= $index ?>][staff_accounts_id]" value="<?= $user['StaffID'] ?>"
                                            />
                                            <input
                                                type="hidden" name="country_access[<?= $index ?>][country_id]" value="<?= $ca["country_id"] ?>"
                                            />
                                            <input
                                                type="hidden" name="country_access[<?= $index ?>][default]" value="<?= $ca["default"] ?>"
                                            />
                                            <input
                                                type="hidden" name="country_access[<?= $index ?>][status]" value="0"
                                            />
                                            <input
                                                type="checkbox" name="country_access[<?= $index ?>][status]" id="country_access-<?= $index ?>-status"
                                                value="1"
                                                <?= set_checkbox("country_access[{$index}][status]", 1, set_value("country_access[{$index}][status]", $ca["status"]) == 1 || $this->config->item("country") == $ca["country_id"]) ?>
                                            />
                                            <label for="country_access-<?= $index ?>-status"><?= $countryAbbr[$ca["country_id"]] ?></label>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </section>
                    </div>
                    <?php if (!empty($states)): ?>
                        <div class="col-8">
                            <section class="widget widget-reports states-fieldset mb-2" >
                                <header class="widget-header widget-header-blue">
                                    States
                                </header>
                                <div class="widget-content">
                                    <div class="form-row pt-2" id="states-row">
                                    <?php
                                    $countryId = $this->config->item('country');
                                    foreach ($states as $index => $state):
                                        $checked = set_checkbox("states[{$index}][selected]", 1, $state['selected'] == 1);
                                    ?>
                                        <span class="checkbox d-inline mr-2">
                                            <?php if($state['selected'] == 1): ?>
                                                <input
                                                    type="hidden"
                                                    name="og_states[<?= $index ?>]"
                                                    value="<?= $state['StateID'] ?>"
                                                />
                                            <?php endif; ?>
                                            <input
                                                type="hidden"
                                                name="states[<?= $index ?>][StateID]"
                                                value="<?= $state['StateID'] ?>"
                                            />
                                            <input
                                                type="hidden"
                                                name="states[<?= $index ?>][selected]"
                                                value="0"
                                            />
                                            <input
                                                type="checkbox"
                                                name="states[<?= $index ?>][selected]"
                                                id="states-<?= $index ?>-selected"
                                                <?= $checked ?>
                                                value="1"
                                                class="states-checkbox"
                                            />
                                            <label for="states-<?= $index ?>-selected">
                                                <?= $state['state'] ?>
                                            </label>
                                        </span>
                                    <?php
                                    endforeach;
                                    ?>
                                    </div>
                                </div>
                            </section>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>