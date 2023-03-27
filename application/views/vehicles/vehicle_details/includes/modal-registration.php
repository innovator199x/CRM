<div class="fancybox-form-container" id="fancybox-registration" style="display:none;">
    <form class="fancybox-form" method="post" data-tables='<?= json_encode(["vehicles" => ["_idValue" => $vehicle->vehicles_id, "_idField" => "vehicles_id"]]) ?>'>
        <section class="card card-blue-fill">
            <header class="card-header">Registration</header>
            <div class="card-body">
                <div class="card-block">
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
