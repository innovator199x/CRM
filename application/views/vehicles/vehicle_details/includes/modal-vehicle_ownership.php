<div class="fancybox-form-container" id="fancybox-vehicle_ownership" style="display:none;">
    <form class="fancybox-form" method="post"
        data-tables='<?= json_encode(["vehicles" => ["_idValue" => $vehicle->vehicles_id, "_idField" => "vehicles_id"]]) ?>'>
        <section class="card card-blue-fill">
            <header class="card-header">Ownership Details</header>
            <div class="card-body">
                <div class="card-block">
                    <div class="form-group">
                        <label class="form-control-label">Ownership</label>
                        <select class="form-control editable-field" data-table="vehicles" data-field="vehicle_ownership"
                            required>
                            <option value="1" <?php if($vehicle->vehicle_ownership == 1){ echo 'selected'; } ?>>Company
                            </option>
                            <option value="2" <?php if($vehicle->vehicle_ownership == 2){ echo 'selected'; } ?>>Personal
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