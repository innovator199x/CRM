<div class="fancybox-form-container" id="fancybox-finance" style="display:none;">
    <form class="fancybox-form" method="post" data-tables='<?= json_encode(["vehicles" => ["_idValue" => $vehicle->vehicles_id, "_idField" => "vehicles_id"]]) ?>'>
        <section class="card card-blue-fill">
            <header class="card-header">Finance</header>
            <div class="card-body">
                <div class="card-block">
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
