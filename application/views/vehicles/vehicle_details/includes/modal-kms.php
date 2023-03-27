<div class="fancybox-form-container" id="fancybox-kms" style="display:none;">
    <form action="" method="post" id="kms-form">
        <section class="card card-blue-fill">
            <header class="card-header">Kilometres Details</header>
            <div class="card-body">
                <div class="card-block">
                <fieldset>
                    <legend>Kilometers</legend>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Kms</label>
                                        <input type="hidden" name="vehicle_id" value="<?= $vehicle->vehicles_id; ?>" required>
                                        <input type="text" class="form-control" name="kms" value="<?= $kms->kms; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Next Service</label>
                                        <input type="text" class="form-control" name="next_service" value="<?= $vehicle->next_service; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Updated</label>
                                        <input type="text" class="form-control" value="<?= $kms->kms_updated; ?>" disabled>
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