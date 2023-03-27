<div class="fancybox-form-container" id="fancybox-irf" style="display:none;">
    <form class="fancybox-form" method="post" data-tables='<?= json_encode(["vehicles" => ["_idValue" => $vehicle->vehicles_id, "_idField" => "vehicles_id"]]) ?>'>
        <section class="card card-blue-fill">
            <header class="card-header">Insurance/ Rego / Finance</header>
            <div class="card-body">
                <div class="card-block">
                    <fieldset class="mt-3">
                        <legend>Registration</legend>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-control-label">Number Plate</label>
                                    <input type="text" class="form-control editable-field" data-table="vehicles" data-field="number_plate" value="<?= $vehicle->number_plate ?>">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-control-label">Cust. Rego #</label>
                                    <input type="text" class="form-control editable-field" data-table="vehicles" data-field="cust_reg_num" value="<?= $vehicle->cust_reg_num ?>">
                                </div>
                            </div>

                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-control-label">Rego Expires<span style="color:red">*</span></label>
                                    <input type="text" class="form-control flatpickr flatpickr-input date-field editable-field" data-table="vehicles" data-field="rego_expires" value="<?php echo $this->system_model->formatDate($vehicle->rego_expires,'d/m/Y');  ?>">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-control-label">Key Number</label>
                                    <input type="text" class="form-control editable-field" data-table="vehicles" data-field="key_number" value="<?= $vehicle->key_number ?>">
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Insurance</legend>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-control-label">Insurance Policy #</label>
                                    <input type="text" class="form-control editable-field" data-table="vehicles" data-field="ins_pol_num" value="<?= $vehicle->ins_pol_num ?>">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-control-label">Insurer</label>
                                    <input type="text" class="form-control editable-field" data-table="vehicles" data-field="insurer" value="<?= $vehicle->insurer ?>">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-control-label">Policy Expires</label>
                                    <input type="text" class="form-control flatpickr flatpickr-input date-field editable-field" data-table="vehicles" data-field="policy_expires" value="<?php echo $this->system_model->formatDate($vehicle->policy_expires,'d/m/Y');  ?>">
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Finance</legend>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-control-label">Bank</label>
                                    <input type="text" class="form-control editable-field" data-table="vehicles" data-field="finance_bank" value="<?= $vehicle->finance_bank ?>">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-control-label">Loan Number</label>
                                    <input type="text" class="form-control editable-field" data-table="vehicles" data-field="finance_loan_num" value="<?= $vehicle->finance_loan_num ?>">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-control-label">Term (Months)</label>
                                    <input type="text" class="form-control editable-field" data-table="vehicles" data-field="finance_loan_terms" value="<?= $vehicle->finance_loan_terms ?>">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-control-label">Monthly $</label>
                                    <input type="text" class="form-control editable-field" data-table="vehicles" data-field="finance_monthly_repayments" value="<?= $vehicle->finance_monthly_repayments ?>">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-control-label">Start Date</label>
                                    <input type="text" class="form-control flatpickr flatpickr-input date-field editable-field" data-table="vehicles" data-field="finance_start_date" value="<?= $this->system_model->formatDate($vehicle->finance_start_date,'d/m/Y'); ?>">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-control-label">End Date</label>
                                    <input type="text" class="form-control flatpickr flatpickr-input date-field editable-field" data-table="vehicles" data-field="finance_end_date" value="<?= $this->system_model->formatDate($vehicle->finance_end_date,'d/m/Y'); ?>">
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary update-button">Update</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </form>
</div>