<div class="fancybox-form-container" id="fancybox-year" style="display:none;">
    <form class="fancybox-form" method="post" data-tables='<?= json_encode(["vehicles" => ["_idValue" => $vehicle->vehicles_id, "_idField" => "vehicles_id"]]) ?>'>
        <section class="card card-blue-fill">
            <header class="card-header">Year</header>
            <div class="card-body">
                <div class="card-block">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">Year</label>
                                <select class="form-control editable-field" data-table="vehicles" data-field="year">
                                    <option value="">----</option>
                                    <?php $year =  range (2035,2005); foreach($year as $val){ ?>
                                    <option value="<?php echo $val; ?>" <?php if($val==$vehicle->year){ echo 'selected'; } ?>><?php echo $val; ?></option><?php } ?>
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