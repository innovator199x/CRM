<div class="fancybox-form-container" id="fancybox-make" style="display:none;">
    <form class="fancybox-form" method="post" data-tables='<?= json_encode(["vehicles" => ["_idValue" => $vehicle->vehicles_id, "_idField" => "vehicles_id"]]) ?>'>
        <section class="card card-blue-fill">
            <header class="card-header">Details</header>
            <div class="card-body">
                <div class="card-block">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Make</label>
                                <input type="text" class="form-control editable-field" data-table="vehicles" data-field="make" value="<?= $vehicle->make ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
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
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Model</label>
                                <input type="text" class="form-control editable-field" data-table="vehicles" data-field="model" value="<?= $vehicle->model ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Plant ID</label>
                                <input type="text" class="form-control editable-field" data-table="vehicles" data-field="plant_id" value="<?= $vehicle->plant_id ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Year</label>
                                <select class="form-control editable-field" data-table="vehicles" data-field="year">
                                    <option value="">----</option>
                                    <?php $year =  range (2035,2005); foreach($year as $val){ ?>
                                    <option value="<?php echo $val; ?>" <?php if($val==$vehicle->year){ echo 'selected'; } ?>><?php echo $val; ?></option><?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">VIN No</label>
                                <input type="text" class="form-control editable-field" data-table="vehicles" data-field="vin_num" value="<?= $vehicle->vin_num ?>">
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