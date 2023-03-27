<div class="fancybox-form-container" id="fancybox-fuel_number_pin" style="display:none;">
    <form class="fancybox-form" method="post" data-tables='<?= json_encode(["vehicles" => ["_idValue" => $vehicle->vehicles_id, "_idField" => "vehicles_id"]]) ?>'>
        <section class="card card-blue-fill">
            <header class="card-header">Fuel Card Number/PIN</header>
            <div class="card-body">
                <div class="card-block">
                    <div class="form-group">
                        <label class="form-control-label">Fuel Card Number</label>
                        <input type="text" class="form-control editable-field" data-table="vehicles" data-field="fuel_card_num" value="<?= $vehicle->fuel_card_num ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-control-label">Fuel Card PIN</label>
                        <input type="text" class="form-control editable-field" data-table="vehicles" data-field="fuel_card_pin" value="<?= $vehicle->fuel_card_pin ?>">
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