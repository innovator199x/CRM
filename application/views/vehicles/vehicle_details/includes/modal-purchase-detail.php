<div class="fancybox-form-container" id="fancybox-purchase-detail" style="display:none;">
    <form class="fancybox-form" method="post" data-tables='<?= json_encode(["vehicles" => ["_idValue" => $vehicle->vehicles_id, "_idField" => "vehicles_id"]]) ?>'> 
        <section class="card card-blue-fill">
            <header class="card-header">Purchase Details</header>
            <div class="card-body">
                <div class="card-block">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-control-label">Purchase Date</label>
                                <input type="text" class="form-control flatpickr flatpickr-input date-field editable-field" data-table="vehicles" data-field="purchase_date" value="<?php echo $this->system_model->formatDate($vehicle->purchase_date,'d/m/Y');  ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-control-label">Purchase Price</label>
                                <input type="text" class="form-control editable-field" data-table="vehicles" data-field="purchase_price" value="<?= $vehicle->purchase_price ?>">
                            </div>
                         </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-control-label">Warranty Expires</label>
                                <input type="text" class="form-control flatpickr flatpickr-input date-field editable-field" data-table="vehicles" data-field="warranty_expires" value="<?php echo $this->system_model->formatDate($vehicle->warranty_expires,'d/m/Y');  ?>">
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