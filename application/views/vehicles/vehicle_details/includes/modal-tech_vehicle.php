<div class="fancybox-form-container" id="fancybox-tech_vehicle" style="display:none;">
    <form class="fancybox-form" method="post"
        data-tables='<?= json_encode(["vehicles" => ["_idValue" => $vehicle->vehicles_id, "_idField" => "vehicles_id"]]) ?>'>
        <section class="card card-blue-fill">
            <header class="card-header">Tech Vehicle</header>
            <div class="card-body">
                <div class="card-block">
                    <div class="form-group">
                        <label class="form-control-label">Tech Vehicle</label>
                        <select class="form-control editable-field" data-table="vehicles" data-field="tech_vehicle"
                            required>
                            <option value="">--Select--</option>
                            <option value="1" <?php if($vehicle->tech_vehicle == 1){ echo 'selected'; } ?>>Yes
                            </option>
                            <option value="0" <?php if($vehicle->tech_vehicle == 0){ echo 'selected'; } ?>>No
                            </option>
                        </select>
                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary update-button">Update</button>
                    </div>
                </div>
            </div>
        </section>
    </form>
</div>