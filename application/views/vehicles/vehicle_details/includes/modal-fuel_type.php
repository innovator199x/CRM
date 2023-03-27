<div class="fancybox-form-container" id="fancybox-fuel_type" style="display:none;">
    <form class="fancybox-form" method="post" data-tables='<?= json_encode(["vehicles" => ["_idValue" => $vehicle->vehicles_id, "_idField" => "vehicles_id"]]) ?>'>
        <section class="card card-blue-fill">
            <header class="card-header">Fuel Type</header>
            <div class="card-body">
                <div class="card-block">
                    <div class="form-group">
                        <label class="form-control-label">Fuel Type</label>
                        <select class="form-control editable-field" data-table="vehicles" data-field="fuel_type">
                            <option value="">----</option>
                            <option <?php if($vehicle->fuel_type == 'Unleaded'){ echo 'selected'; } ?> value="Unleaded">Unleaded</option>
                            <option <?php if($vehicle->fuel_type == 'Premium'){ echo 'selected'; } ?> value="Premium">Premium</option>
                            <option <?php if($vehicle->fuel_type == 'Diesel'){ echo 'selected'; } ?> value="Diesel"> Diesel</option>
                            <option <?php if($vehicle->fuel_type == 'LPG'){ echo 'selected'; } ?> value="LPG">LPG</option>
                        </select>
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