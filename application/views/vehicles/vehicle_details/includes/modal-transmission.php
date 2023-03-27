<div class="fancybox-form-container" id="fancybox-transmission" style="display:none;">
    <form class="fancybox-form" method="post" data-tables='<?= json_encode(["vehicles" => ["_idValue" => $vehicle->vehicles_id, "_idField" => "vehicles_id"]]) ?>'>
        <section class="card card-blue-fill">
            <header class="card-header">Transmission</header>
            <div class="card-body">
                <div class="card-block">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">Transmission</label>
                                <select class="form-control editable-field" data-table="vehicles" data-field="transmission" required>
                                    <option value="">Please Select Transmission</option>
                                    <option value="Auto" <?php if($vehicle->transmission == 'Auto'){ echo 'selected'; } ?>>Auto </option>
                                    <option value="Manual" <?php if($vehicle->transmission == 'Manual'){ echo 'selected'; } ?>>Manual</option>
                                </select>
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