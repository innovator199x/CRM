<div class="fancybox-form-container" id="fancybox-driver" style="display:none;">
    <form class="fancybox-form" method="post" data-tables='<?= json_encode(["vehicles" => ["_idValue" => $vehicle->vehicles_id, "_idField" => "vehicles_id"]]) ?>'>
        <section class="card card-blue-fill">
            <header class="card-header">Driver Details</header>
            <div class="card-body">
                <div class="card-block">
                    <div class="form-group">
                        <label class="form-control-label">Assign to SATS Userr</label>
                        <select class="form-control editable-field" id="staff_id" data-table="vehicles" data-field="StaffID">
                            <option value="">Unassigned</option>
                                <?php foreach ($staff_info as $row) { ?>
                                    <option value="<?php echo $row['staff_accounts_id']; ?>" <?php if($row['staff_accounts_id']==$vehicle->StaffID){ echo 'selected'; } ?>> <?php echo $row['FirstName'].' '.$row['LastName']; ?></option> 
                                <?php } ?>
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
