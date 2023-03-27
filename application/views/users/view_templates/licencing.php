<div role="tabpanel" class="tab-pane fade active show" id="licencing-tab">
    <div class="row">
        <div class="col-sm-12">
            <div class="py-3">
                <div class="row">
                    <div class="col-xs-12 col-lg-3">
                        <section class="widget widget-reports">
                            <header class="widget-header widget-header-blue">
                                Vehicle
                            </header>
                            <div class="widget-content">
                                <div class="form-row pt-2">
                                    <div class="col-12 pb-4">
                                        <?php if (!is_null($vehicle)): ?>
                                        <a href="#"><?= $vehicle['number_plate'] ?></a>
                                        <?php else: ?>
                                        <span>No vehicle(s) assigned yet</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="col-xs-12 col-lg-9">
                        <section class="widget widget-reports">
                            <header class="widget-header widget-header-blue">
                                Driver&apos;s Licence
                            </header>
                            <div class="widget-content">
                                <div class="form-row pt-2">
                                    <div class="col-sm-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="user-license_num">Licence No.</label>
                                            <input type="text" name="user[license_num]" id="user-license_num" class="form-control" value="<?= set_value('user[license_num]', $user['license_num']) ?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="user-licence_expiry">Licence Expiry</label>
                                            <?= sats_form_input_date([
                                                'variable' => $user,
                                                'post_var_name' => 'user',
                                                'post_field_key' => 'licence_expiry',
                                            ]) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <section class="widget widget-reports">
                    <header class="widget-header widget-header-blue">
                        Blue Card
                    </header>
                    <div class="widget-content">
                        <div class="form-row pt-2">
                            <div class="col-sm-6 col-lg-4">
                                <div class="form-group">
                                    <label for="user-blue_card_num">Blue Card No.</label>
                                    <input type="text" name="user[blue_card_num]" id="user-blue_card_num" class="form-control" value="<?= set_value('user[blue_card_num]', $user['blue_card_num']) ?>" />
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4">
                                <div class="form-group">
                                    <label for="user-blue_card_expiry">Blue Card Expiry</label>
                                    <?= sats_form_input_date([
                                        'variable' => $user,
                                        'post_var_name' => 'user',
                                        'post_field_key' => 'blue_card_expiry',
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="row">
                    <div class="col-xs-12 col-lg-3">
                        <section class="widget widget-reports">
                            <header class="widget-header widget-header-blue">
                                Electrical Licence
                            </header>
                            <div class="widget-content">
                                <div class="form-row pt-2">
                                    <div class="col-12 text-center">
                                        <?php
                                        if (!empty($user['electrical_license'])):
                                        ?>
                                        <img id="image-user_electrical_license"
                                            src="/images/electrical_license/<?= $user['electrical_license'] ?>"
                                            class="d-block mx-auto" style="max-width: 200px;" />
                                        <?php
                                        else:
                                        ?>
                                        <img id="image-user_electrical_license"
                                            class="d-block mx-auto" style="display: none; max-width: 200px;" />
                                        <?php
                                        endif;
                                        ?>
                                        <button type="button"
                                            id="button-user_electrical_license"
                                            class="btn btn-primary mt-2 mb-3"
                                        >Choose Photo</button>
                                        <input name="user_electrical_license" type="file" accept="image/*" class="d-none"/>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="col-xs-12 col-lg-9">
                        <section class="widget widget-reports">
                            <header class="widget-header widget-header-blue">
                                Electrical Licence
                            </header>
                            <div class="widget-content">
                                <div class="form-row pt-2">
                                    <div class="col-sm-12 col-lg-4">
                                        <div class="form-group">
                                            <label for="user-is_electrician">Electrician</label>
                                            <select name="user[is_electrician]" id="user-is_electrician" class="form-control" >
                                                <?php
                                                $isElectricianValue = set_value('user[is_electrician]', $user['is_electrician']);
                                                ?>
                                                <option value="1"
                                                    <?= set_select('user[is_electrician]', 1, $isElectricianValue == 1) ?>
                                                >Yes</option>
                                                <option value="0"
                                                    <?= set_select('user[is_electrician]', 0, $isElectricianValue == 0) ?>
                                                >No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="user-elec_license_num">Licence No.</label>
                                            <input type="text" name="user[elec_license_num]" id="user-elec_license_num" class="form-control" value="<?= set_value('user[elec_license_num]', $user['elec_license_num']) ?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-lg-4">
                                        <div class="form-group">
                                            <label for="user-elec_licence_expiry">Licence Expiry</label>
                                            <?= sats_form_input_date([
                                                'variable' => $user,
                                                'post_var_name' => 'user',
                                                'post_field_key' => 'elec_licence_expiry',
                                            ]) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>