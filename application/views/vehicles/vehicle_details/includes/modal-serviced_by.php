<div class="fancybox-form-container" id="fancybox-serviced_by" style="display:none;">
    <form class="fancybox-form" method="post"
        data-tables='<?= json_encode(["vehicles" => ["_idValue" => $vehicle->vehicles_id, "_idField" => "vehicles_id"]]) ?>'>
        <section class="card card-blue-fill">
            <header class="card-header">Serviced By</header>
            <div class="card-body">
                <div class="card-block">

                    <div class="form-group">
                        <label class="form-control-label">Serviced By</label>
                        <input type="text" class="form-control editable-field" data-table="vehicles"
                            data-field="serviced_by" value="<?= $vehicle->serviced_by ?>">
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary update-button">Update</button>
                    </div>
                </div>
            </div>
        </section>
    </form>
</div>