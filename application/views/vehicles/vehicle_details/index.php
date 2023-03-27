<div class="box-typical box-typical-padding">
    <style>
        .flatpickr {
            width: 100% !important;
        }

        fieldset {
            margin-bottom: 20px;
        }

        .body-typical-body {
            padding-top: 25px !important;
        }

        .fancybox-content {
            padding: 0px !important;
        }

        .fancybox-content .card,
        .fancybox-content .card-header:first-child {
            border-radius: 0px !important;
        }

        .nav .nav-item {
            width: 50%;
        }

        .tab-content .row {
            margin-bottom: .5em;
        }

        .tab-content .row label {
            font-weight: bold;
            margin-bottom: .25em;
        }

        .widget .widget-content {
            border: solid 1px #d8e2e7;
            border-top: 0;
        }

        .widget .form-row {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }

        .widget .form-row:not(.vertical-checkboxes) .checkbox:first-child {
            padding-left: 0.5rem;
        }

        .fieldset-closable:not(.closed) .visibility-off {
            display: none;
        }

        .fieldset-closable.closed .visibility-on {
            display: none;
        }

        .table.border-horizontal tr {
            border-left: solid 1px #d8e2e7;
            border-right: solid 1px #d8e2e7;
        }

        .table.border-bottom {
            border-bottom: solid 1px #d8e2e7;
        }

        tbody tr.no-entries:not(:last-child) {
            display: none;
        }

        #form-user .form-tooltip-error {
            bottom: unset;
            top: -2em;
        }

        #form-user .form-tooltip-error ul {
            border: none;
        }

        #table-device_accounts .form-tooltip-error {
            bottom: 100%;
            top: unset;
        }

        .ui-autocomplete {
            z-index: 10000 !important;
        }

        .fancybox-form-container:not(.date-only) {
            min-width: 420px;
        }

        #fancybox-user-address input[type=search] {
            box-sizing: border-box;
        }

        .nav-tabs .nav-item.show .nav-link,
        .nav-tabs .nav-link.active {
            border-top: 4px solid #00a8ff;
            color: #00a8ff;
        }

    </style>
    <?php 
	// breadcrumbs template
	$bc_items = array(
        array(
			'title' => 'Vehicles',
			'link' => "/vehicles/view_vehicles"
		),
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/vehicles/view_vehicle_details/$vehicle->vehicles_id"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

    <div class="tabs-section">
        <div class="tabs-section-nav tabs-section-nav-icons">
            <div class="tbl">
                <ul class="nav" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#tab_vehicle_details">
                            <span class="nav-link-in"><i class="fa fa-info"></i> Details</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab_files_servicing">
                            <span class="nav-link-in"><i class="fa fa-list"></i> Servicing</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade active show" id="tab_vehicle_details">
                <div class="box-typical-body">
                    <div class="row mb-1">
                        <div class="col-12 text-left">
                            <div class="row">
                                <div class="col-md-3 col-xs-6">
                                    <label>Make | Model | Year</label>
                                    <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-make" href="javascript:;">
                                        <?php echo ($vehicle->make ? $vehicle->make : 'No Data'); ?> -
                                        <?php echo ($vehicle->model ? $vehicle->model : 'No Data'); ?> -
                                        <?php echo ($vehicle->year ? $vehicle->year : 'No Data'); ?>
                                    </a>
                                </div>
                                <div class="col-md-3 col-xs-6">
                                    <label>Transmission</label>
                                    <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-transmission" href="javascript:;">
                                        <?php echo ($vehicle->transmission ? $vehicle->transmission : 'No Data'); ?>
                                    </a>
                                </div>
                                <div class="col-md-3 col-xs-6">
                                    <label>VIN No.</label>
                                    <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-vin_no" href="javascript:;">
                                        <?php echo ($vehicle->vin_num ? $vehicle->vin_num : 'No Data'); ?>
                                    </a>
                                </div>
                                <div class="col-md-3 col-xs-6">
                                    <label>Plant ID</label>
                                    <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-plant_id" href="javascript:;">
                                        <?php echo ($vehicle->plant_id ? $vehicle->plant_id : 'No Data'); ?>
                                    </a>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 col-xs-6">
                                    <label>Purchase Date</label>
                                    <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-purchase-date" href="javascript:;">
                                        <?php echo ($vehicle->purchase_date ? $this->system_model->formatDate($vehicle->purchase_date,'d/m/Y') : 'No Data'); ?>
                                    </a>
                                </div>
                                <div class="col-md-3 col-xs-6">
                                    <label>Purchase Price</label>
                                    <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-purchase-price" href="javascript:;">
                                        <?php echo ($vehicle->purchase_price ? $vehicle->purchase_price : 'No Data'); ?>
                                    </a>
                                </div>
                                <div class="col-md-3 col-xs-6">
                                    <label>Fuel Card Number | Card PIN</label>
                                    <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-fuel_number_pin" href="javascript:;">
                                        <?php echo ($vehicle->fuel_card_num ? $vehicle->fuel_card_num : 'No Data'); ?> -
                                        <?php echo ($vehicle->fuel_card_pin ? $vehicle->fuel_card_pin: 'No Data'); ?>
                                    </a>
                                </div>
                                <div class="col-md-3 col-xs-6">
                                    <label>Kilometres</label>
                                    <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-kms" href="javascript:;">
                                        <?php echo ($kms->kms ? $kms->kms : 'No Data'); ?> -
                                        <?php echo ($kms->kms_updated ?  $kms->kms_updated : 'No Data'); ?> -
                                        <?php echo ($vehicle->next_service ?  $vehicle->next_service : 'No Data'); ?>
                                    </a>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 col-xs-6">
                                    <label>Warranty Expires</label>
                                    <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-warranty-expires" href="javascript:;">
                                        <?php echo ($vehicle->warranty_expires ? $this->system_model->formatDate($vehicle->warranty_expires,'d/m/Y') : 'No Data'); ?>
                                    </a>
                                </div>

                                <div class="col-md-3 col-xs-6">
                                    <label>Insurance Policy #</label>
                                    <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-insurance_policy" href="javascript:;">
                                        <?php echo ($vehicle->ins_pol_num ? $vehicle->ins_pol_num : 'No Data'); ?>
                                    </a>
                                </div>

                                <div class="col-md-3 col-xs-6">
                                    <label>Insurer</label>
                                    <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-insurer" href="javascript:;">
                                        <?php echo ($vehicle->insurer ? $vehicle->insurer : 'No Data'); ?>
                                    </a>
                                </div>

                                <div class="col-md-3 col-xs-6">
                                    <label>Policy Expires</label>
                                    <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-policy_expires" href="javascript:;">
                                        <?php echo ($vehicle->policy_expires ? $this->system_model->formatDate($vehicle->policy_expires,'d/m/Y') : 'No Data'); ?>
                                    </a>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 col-xs-6">
                                    <label>Number Plate</label>
                                    <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-number_plate" href="javascript:;">
                                        <?php echo ($vehicle->number_plate ? $vehicle->number_plate : 'No Data'); ?>
                                    </a>
                                </div>
                                <div class="col-md-3 col-xs-6">
                                    <label>Cust. Rego #</label>
                                    <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-cust_reg_num" href="javascript:;">
                                        <?php echo ($vehicle->cust_reg_num ? $vehicle->cust_reg_num : 'No Data'); ?>
                                    </a>
                                </div>
                                <div class="col-md-3 col-xs-6">
                                    <label>Rego Expires</label>
                                    <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-rego_expires" href="javascript:;">
                                        <?php echo ($vehicle->rego_expires ? $this->system_model->formatDate($vehicle->rego_expires,'d/m/Y') : 'No Data'); ?>
                                    </a>
                                </div>
                                <div class="col-md-3 col-xs-6">
                                    <label>Key Number</label>
                                    <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-key_number" href="javascript:;">
                                        <?php echo ($vehicle->key_number ? $vehicle->key_number : 'No Data'); ?>
                                    </a>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 col-xs-6">
                                    <label>Bank</label>
                                    <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-finance_bank" href="javascript:;">
                                        <?php echo ($vehicle->finance_bank ? $vehicle->finance_bank : 'No Data'); ?>
                                    </a>
                                </div>
                                <div class="col-md-3 col-xs-6">
                                    <label>Loan Number</label>
                                    <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-finance_loan_num" href="javascript:;">
                                        <?php echo ($vehicle->finance_loan_num ? $vehicle->finance_loan_num : 'No Data'); ?>
                                    </a>
                                </div>
                                <div class="col-md-3 col-xs-6">
                                    <label>Term (Months)</label>
                                    <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-finance_loan_terms" href="javascript:;">
                                        <?php echo ($vehicle->finance_loan_terms ? $vehicle->finance_loan_terms : 'No Data'); ?>
                                    </a>
                                </div>
                                <div class="col-md-3 col-xs-6">
                                    <label>Monthly $</label>
                                    <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-finance_monthly_repayments" href="javascript:;">
                                        <?php echo ($vehicle->finance_monthly_repayments ? $vehicle->finance_monthly_repayments : 'No Data'); ?>
                                    </a>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 col-xs-6">
                                    <label>Finance Start Date | End Date</label>
                                    <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-finance_start_end" href="javascript:;">
                                        <?php echo ($vehicle->finance_start_date ? $this->system_model->formatDate($vehicle->finance_start_date,'d/m/Y') : 'No Data'); ?> -
                                        <?php echo ($vehicle->finance_end_date ? $this->system_model->formatDate($vehicle->finance_end_date,'d/m/Y') : 'No Data'); ?>
                                    </a>
                                </div>
                                <div class="col-md-3 col-xs-6">
                                    <label>Toll Pass</label>
                                    <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-toll_pass" href="javascript:;">
                                        <?php echo ($vehicle->etag_num ? $vehicle->etag_num : 'No Data'); ?>
                                    </a>
                                </div>

                                <div class="col-md-3 col-xs-6">
                                    <label>Driver</label>
                                    <a data-auto-focus="false" data-fancybox data-src="#fancybox-driver" href="javascript:;">
                                        <?php echo ($driver->name ? $driver->name : 'No Data'); ?>
                                    </a>
                                </div>

                                <div class="col-md-3 col-xs-6">
                                    <label>Ownership</label>
                                    <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-vehicle_ownership" href="javascript:;">
                                        <?php echo ($vehicle->vehicle_ownership==1 ? 'Company' : 'Personal'); ?>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 col-xs-6">
                            <label>Serviced By</label>
                            <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-serviced_by" href="javascript:;">
                                <?php echo ($vehicle->serviced_by ? $vehicle->serviced_by : 'No Data'); ?>
                            </a>
                        </div>
                        <div class="col-md-3 col-xs-6">
                            <label>Roadside assistance Number</label>
                            <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-ra_num" href="javascript:;">
                                <?php echo ($vehicle->ra_num ? $vehicle->ra_num : 'No Data'); ?>
                            </a>
                        </div>
                        <div class="col-md-3 col-xs-6">
                            <label>Tech Vehicle</label>
                            <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-tech_vehicle" href="javascript:;">
                                <?php echo ($vehicle->tech_vehicle==1 ? 'Yes' : 'No'); ?>
                            </a>
                        </div>
                        <div class="col-md-3 col-xs-6">
                            <label>Fuel Type</label>
                            <a data-auto-focus="false" data-fancybox="" data-src="#fancybox-fuel_type" href="javascript:;">
                                <?php echo ($vehicle->fuel_type ? $vehicle->fuel_type : 'No Data'); ?>
                            </a>
                        </div>
                    </div>


                    <div class="row mb-1">
                        <div class="col-12 text-left">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <section class="widget widget-reports">
                                            <header class="widget-header widget-header-blue">Vehicle Image</header>
                                            <div class="widget-content">
                                                <div class="form-row pt-2">
                                                    <div class="col-12 text-center">
                                                        <form id="form-user-vehicle_pic" action="" method="post" enctype="multipart/form-data">
                                                            <input type="hidden" name="vehicles_id" id="vehicle_image_id" value="<?php echo $vehicle->vehicles_id; ?>" required>
                                                            <?php if (!empty($vehicle->image)): ?>
                                                            <img width="100%" id="image-vehicle_pic" src="/images/vehicle/<?php echo $vehicle->image; ?>" class="mx-auto w-100" style="max-width: 320px;" />
                                                            <?php else: ?>
                                                            <img width="100%" id="image-vehicle_pic" class="mx-auto w-100" style="display: none; max-width: 320px;" />
                                                            <?php endif; ?>
                                                            <div class="text-left mt-2 mb-3 mx-auto" style="max-width: 320px;">
                                                                <div class="d-flex w-100" style="align-items: center; justify-content: center;">
                                                                    <div id="hide_delete_btn" style="display: <?php echo ($vehicle->image ? 'block' : 'none');  ?>">
                                                                        <button type="button" class="btn btn-danger button-delete_vehicle_pic" id="button-delete_vehicle_pic">Remove Photo</button>
                                                                        <div style="flex-grow: 1"></div>
                                                                    </div>
                                                                    <button type="button" id="button-vehicle_pic" class="btn btn-primary ml-5 button-upload_photo" data-target="#file-vehicle_pic">Choose Photo</button>
                                                                    <input type="file" accept="image/jpeg, image/png" class="d-none hidden-file" name="vehicle_pic" id="file-vehicle_pic" data-target="#image-vehicle_pic" />
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </section>
                                        </div>

                                        <div class="col-md-6 mt-2">
                                            <div class="form-group">
                                                <a href="/pdf/vehicle_details?id=<?php echo $vehicle->vehicles_id; ?>" target="_blank" class="btn btn-info"><span class="fa fa-file-pdf-o"></span> Export Vehicle Details</a>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mt-2">
                                            <form class="fancybox-form" method="post" data-tables='<?= json_encode(["vehicles" => ["_idValue" => $vehicle->vehicles_id, "_idField" => "vehicles_id"]]) ?>'>
                                                <div class="input-group">
                                                    <div class="form-check-inline">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input editable-field check-field" name="serviced_booked" <?php echo ($vehicle->serviced_booked == 1 ? 'checked' : ''); ?> data-table="vehicles" data-field="serviced_booked">Service Booked
                                                        </label>
                                                    </div>
                                                    <div class="input-group-append">
                                                        <button type="submit" class="btn btn-primary">Update</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <section class="widget widget-reports">
                                        <header class="widget-header widget-header-blue">Tools</header>
                                        <table class="table table-bordered table-striped table-hover mt-2" id="tools_table" style="width:100%;">
                                            <thead>
                                                <tr>
                                                    <th>Item ID</th>
                                                    <th>Brand</th>
                                                    <th>Descriptions</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="tab_files_servicing">
                <div class="box-typical-body">
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <section class="widget widget-reports">
                                <header class="widget-header widget-header-blue">Servicing</header>
                                <form action="" method="post" id="service-form" enctype="multipart/form-data">
                                    <div class="row mt-2">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="file" class="form-control" name="files[]" id="log_files" placeholder="Log Files" multiple>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="hidden" class="form-control" name="vehicles_id" value="<?php echo $vehicle->vehicles_id;  ?>" required>
                                                <input type="text" class="flatpickr form-control flatpickr-input" name="log_date" id="log_date" value="<?php echo date('d/m/Y'); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="number" step="0.01" class="form-control" name="log_price" id="log_price" placeholder="Price" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <input type="tex" class="form-control" name="log_details" id="log_details" placeholder="Details" required>
                                                <div class="input-group-append">
                                                    <button class="btn btn-primary" type="submit" id="btn-add-servicing"><span class="fa fa-plus"></span> Log</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <div class="row">
                                    <div class="col-md-12">
                                        <table class="table table-bordered table-striped table-hover" id="servicing_table" style="width:100%;">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Name</th>
                                                    <th>Details</th>
                                                    <th>Price</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </section>

                        </div>
                        <div class="col-md-12">
                            <section class="widget widget-reports">
                                <header class="widget-header widget-header-blue">Files</header>
                                <div class="row mt-2" style="margin-bottom: 17px;">
                                    <div class="col-md-12">
                                        <form action="" method="post" enctype="multipart/form-data">
                                            <div class="input-group">
                                                <input type="hidden" name="vehicle_id" id="vehicle_file_id" value="<?php echo $vehicle->vehicles_id; ?>" required>
                                                <input type="file" class="form-control" name="file" id="vehicle_file" aria-describedby="button-file-upload" required>
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary" type="submit" id="button-file-upload"><span class="fa fa-plus"></span> Upload</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <table class="table table-bordered table-striped table-hover" id="files_table" style="width:100%;">
                                            <thead>
                                                <tr>
                                                    <th>Filename</th>
                                                    <th>Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Fancybox Start -->
<?php
 $this->load->view('vehicles/vehicle_details/includes/modal-make');
 $this->load->view('vehicles/vehicle_details/includes/modal-model');
 $this->load->view('vehicles/vehicle_details/includes/modal-year');
 $this->load->view('vehicles/vehicle_details/includes/modal-transmission');
 $this->load->view('vehicles/vehicle_details/includes/modal-vin_no');
 $this->load->view('vehicles/vehicle_details/includes/modal-plant_id');
 $this->load->view('vehicles/vehicle_details/includes/modal-purchase-date');
 $this->load->view('vehicles/vehicle_details/includes/modal-purchase-price');
 $this->load->view('vehicles/vehicle_details/includes/modal-warranty-expires');
 $this->load->view('vehicles/vehicle_details/includes/modal-insurance_policy');
 $this->load->view('vehicles/vehicle_details/includes/modal-insurer');
 $this->load->view('vehicles/vehicle_details/includes/modal-policy_expires');

 $this->load->view('vehicles/vehicle_details/includes/modal-number_plate');
 $this->load->view('vehicles/vehicle_details/includes/modal-cust_reg_num');
 $this->load->view('vehicles/vehicle_details/includes/modal-rego_expires');
 $this->load->view('vehicles/vehicle_details/includes/modal-key_number');

 $this->load->view('vehicles/vehicle_details/includes/modal-finance_bank');
 $this->load->view('vehicles/vehicle_details/includes/modal-finance_loan_num');
 $this->load->view('vehicles/vehicle_details/includes/modal-finance_loan_terms');
 $this->load->view('vehicles/vehicle_details/includes/modal-finance_monthly_repayments');
 $this->load->view('vehicles/vehicle_details/includes/modal-finance_start_end');

 $this->load->view('vehicles/vehicle_details/includes/modal-vehicle_ownership');
 $this->load->view('vehicles/vehicle_details/includes/modal-serviced_by');
 $this->load->view('vehicles/vehicle_details/includes/modal-ra_num');
 $this->load->view('vehicles/vehicle_details/includes/modal-tech_vehicle');

 $this->load->view('vehicles/vehicle_details/includes/modal-fuel_type');
 $this->load->view('vehicles/vehicle_details/includes/modal-fuel_number_pin');


 $this->load->view('vehicles/vehicle_details/includes/modal-toll_pass');
 $this->load->view('vehicles/vehicle_details/includes/modal-kms');
 $this->load->view('vehicles/vehicle_details/includes/modal-driver');
 $this->load->view('vehicles/vehicle_details/includes/modal-ownership');
 $this->load->view('vehicles/vehicle_details/includes/modal-files');
 ?>



<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the
    fancybox</a>
    <div id="about_page_fb" class="fancybox" style="display:none;">
    <h4>Vehicle Details</h4>
    <p>
        This page allows you to update vehicles and assign them to a user.
    </p>
</div>
<!-- Fancybox END -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.12/datatables.min.css" />
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.12/datatables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#servicing_table').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [[ 0, "desc" ]],
        "ajax": {
            "url": "<?php echo base_url('vehicles/get_vehicle_logs'); ?>",
            "dataType": "json",
            "type": "POST",
            "data": {
                '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>',
                'vehicles_id': '<?php echo $vehicle->vehicles_id; ?>'
            }
        },
        "columns": [{
                "data": "date",
                render: function (data, type, row) {
                    return moment(new Date(data).toString()).format('DD/MM/YYYY');
                }
            },
            {
                "data": "name"
            },
            {
                "data": "details"
            },
            {
                "data": "price"
            },
            {
                "data": "action",
                "orderable": false,
                "searcheable": false
            }
        ]
    });

    $('#tools_table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?php echo base_url('vehicles/datatable_tools'); ?>",
            "dataType": "json",
            "type": "POST",
            "data": {
                'vehicles_id': '<?php echo $vehicle->vehicles_id; ?>'
            }
        },
        "columns": [{
                "data": "item_id"
            },
            {
                "data": "brand"
            },
            {
                "data": "description"
            }
        ]
    });

    function log_files(id){
        $("#log_files_table").dataTable().fnDestroy()
        
        $("#modal-files").modal('show');
        $("#load-screen").hide();
        $('#log_files_table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "<?php echo base_url('vehicles/datatable_log_files'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": {
                    'vehicle_log_id': id
                },
            },
            "columns": [{
                    "data": "filename"
                },
                {
                    "data": "date",
                    render: function (data, type, row) {
                        return moment(new Date(data).toString()).format('DD/MM/YYYY');
                    }
                },
                {
                    "data": "action",
                    "orderable": false,
                    "searcheable": false
                }
            ]
        });
    }

    

    $('#files_table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?php echo base_url('vehicles/datatable_files'); ?>",
            "dataType": "json",
            "type": "POST",
            "data": {
                'vehicles_id': '<?php echo $vehicle->vehicles_id; ?>'
            }
        },
        "columns": [{
                "data": "filename"
            },
            {
                "data": "date",
                render: function (data, type, row) {
                    return moment(new Date(data).toString()).format('DD/MM/YYYY');
                }
            },
            {
                "data": "action",
                "orderable": false,
                "searcheable": false
            }
        ]
    });

    $("#servicing_table").on("click", '.btn-view-files', function(e) {
            e.preventDefault();
            $("#load-screen").show();
            var id = $(this).data('id');
            log_files(id);
        });

        //Delete Service
        $("#servicing_table").on("click", '.btn-delete-log', function() {
            var id = $(this).data('id');
            swal({
                title: "Are you sure?",
                text: "Once deleted, you will not be able to recover this Service!",
                type: "warning",
                showCloseButton: false,
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Remove",
                cancelButtonClass: "btn-primary",
                cancelButtonText: "No",
            }, function(response) {
                if (response) {
                    $("#load-screen").show();
                    $.ajax({
                        type: "delete",
                        url: "/vehicles/log_delete/" + id,
                        dataType: "json",
                        success: (response) => {
                            if (response.success) {
                                $('#servicing_table').DataTable().ajax.reload();
                                swal({
                                    title: "Success!",
                                    text: "Service has been deleted.",
                                    type: "success"
                                });
                            } else {
                                swal({
                                    title: "Error!",
                                    text: "Service File not deleted. Something went wrong.",
                                    type: "error",
                                    confirmButtonClass: "btn-danger",
                                });
                            }
                        }
                    }).always(() => {
                        $("#load-screen").hide();
                    })
                }
            });
        });

        //Delete Log Files Table
        $("#log_files_table").on("click", '.btn-delete-file', function() {
            var id = $(this).data('id');
            swal({
                title: "Warning!",
                text: "This will remove this file, do you want to continue??",
                type: "warning",
                showCloseButton: false,
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Remove",
                cancelButtonClass: "btn-primary",
                cancelButtonText: "No",
            }, function(response) {
                if (response) {
                    $("#load-screen").show();
                    $.ajax({
                        type: "delete",
                        url: "/vehicles/remove_log_file/" + id,
                        dataType: "json",
                        success: (response) => {
                            if (response.success) {
                                $('#log_files_table').DataTable().ajax.reload();
                                swal({
                                    title: "Success!",
                                    text: "File deleted.",
                                    type: "success"
                                });
                            } else {
                                swal({
                                    title: "Error!",
                                    text: "File not deleted. Something went wrong.",
                                    type: "error",
                                    confirmButtonClass: "btn-danger",
                                });
                            }
                        }
                    }).always(() => {
                        $("#load-screen").hide();
                    })
                }
            });
        });

        //Delete File
        $("#files_table").on("click", '.btn-delete-file', function() {
            var id = $(this).data('id');
            swal({
                title: "Warning!",
                text: "This will remove the vehicle's file, do you want to continue??",
                type: "warning",
                showCloseButton: false,
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Remove",
                cancelButtonClass: "btn-primary",
                cancelButtonText: "No",
            }, function(response) {
                if (response) {
                    $("#load-screen").show();
                    $.ajax({
                        type: "delete",
                        url: "/vehicles/remove_vehicle_file/" + id,
                        dataType: "json",
                        success: (response) => {
                            if (response.success) {
                                $('#files_table').DataTable().ajax.reload();
                                swal({
                                    title: "Success!",
                                    text: "Vehicle File deleted.",
                                    type: "success"
                                });
                            } else {
                                swal({
                                    title: "Error!",
                                    text: "Vehicle File not deleted. Something went wrong.",
                                    type: "error",
                                    confirmButtonClass: "btn-danger",
                                });
                            }
                        }
                    }).always(() => {
                        $("#load-screen").hide();
                    })
                }
            });
        });

        $("#button-file-upload").click(function(e) {
            e.preventDefault();
            var fd = new FormData();
            var files = $('#vehicle_file')[0].files;
            // Check file selected or not
            $('#load-screen').show();
            if (files.length > 0) {
                fd.append('file', files[0]);
                fd.append('vehicle_id', <?php echo $vehicle->vehicles_id;  ?>);
                $.ajax({
                    url: '/vehicles/vehicle_file_upload',
                    type: 'post',
                    data: fd,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $("#vehicle_file").val(null);
                        reload_table();
                        $('#load-screen').hide();
                        swal({
                            title: "Success!",
                            text: response.message,
                            type: "success"
                        });
                    },
                    error: function(error) {
                        $('#load-screen').hide();
                        swal({
                            title: "Error!",
                            text: error.message,
                            type: "error"
                        });
                    }
                });
            } else {
                $('#load-screen').hide();
                swal({
                    title: "Error!",
                    text: "Please select a file.",
                    type: "error"
                });
            }
        });

        $("#service-form").submit(function(e) {
            e.preventDefault();
            var fd = new FormData(this);
            // Check file selected or not
            $('#load-screen').show();
            $.ajax({
                url: '/vehicles/add_vehicle_servicing_script',
                type: 'post',
                data: fd,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#load-screen').hide();
                    reload_table();
                    $('#service-form')[0].reset();
                    swal({
                        title: "Success!",
                        text: response.message,
                        type: "success"
                    });
                },
                error: function(error) {
                    $('#load-screen').hide();
                    swal({
                        title: "Error!",
                        text: error.message,
                        type: "error"
                    });
                }
            });
        });

        $("#kms-form").submit(function(e) {
            e.preventDefault();
            var fd = new FormData(this);
            $('#load-screen').show();
            $.ajax({
                url: '/vehicles/update_vehicle_kilometers',
                type: 'post',
                data: fd,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#load-screen').hide();
                    swal({
                        title: "Success!",
                        text: response.message,
                        type: "success"
                    });
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                },
                error: function(error) {
                    $('#load-screen').hide();
                    swal({
                        title: "Error!",
                        text: error.message,
                        type: "error"
                    });
                }
            });
        });

    });

</script>
<script>
    $('document').ready((event) => {
        var shouldRefresh = false;

        var closeCallback = () => {
            if (shouldRefresh) {
                window.location.reload();
            }
        };

        $(".fancybox-form-container.different").on("click", ".fancybox-button", closeCallback);
        $(".fancybox-bg").on("click", (evt) => {
            console.log("closer clicked", evt);
            closeCallback();
        });

        $(".fancybox-form-container").not(".different").on("click", ".fancybox-button", () => {
            $(".fancybox-form").find(".original-field").each((index, field) => {
                var $field = $(field);

                $field.siblings(`[data-field="${$field.data("field")}"]`).val($field.val());

            });
        });

        $(".fancybox-form").not(".different").on("submit", submitFancybox);

    });

    function compileData(form) {
        var tablesData = form.data("tables");
        var requestData = {};
        Object.entries(tablesData).forEach(([table, value]) => {
            requestData[table] = {
                ...value,
                fields: {},
            };
        });

        form.find(".editable-field").each((i, f) => {
            var editableField = $(f);

            var table = editableField.data("table");
            var field = editableField.data("field");
            var value = editableField.val();

            if (editableField.is(".date-field")) {
                var m = moment(value, "DD/MM/YYYY");
                if (!m.isValid()) {
                    m = moment(value, "YYYY-MM-DD");
                    if (m.isValid()) {
                        value = m.format("YYYY-MM-DD");
                    }
                } else {
                    value = m.format("YYYY-MM-DD");
                }

            }

            if (editableField.is(".check-field")) {
                if(editableField.is(':checked')){
                    value = 1; 
                } else {
                    value = 0; 
                }
            }

            requestData[table].fields[field] = value;
        });

        return requestData;
    }

    const submitFancybox = (evt) => {
        evt.preventDefault();
        $('#load-screen').show();

        var form = $(evt.target);
        const requestData = compileData(form);

        jQuery.ajax({
            type: "POST",
            url: "/vehicles/ajax_update_fields",
            data: requestData,
            dataType: "json",
            success: (response) => {
                if (response.success) {
                    swal({
                        title: "Success!",
                        text: "Update successful.",
                        type: "success",
                        showCancelButton: false,
                        confirmButtonText: "OK",
                        closeOnConfirm: false,
                        showConfirmButton: <?= $this->config->item('showConfirmButton') ?>,
                        timer: <?= $this->config->item('timer') ?>,
                    }, () => {
                        window.location.reload();
                    });
                } else {
                    swal({
                        title: "Error!",
                        text: "Update failed.",
                        type: "error",
                        showCancelButton: false,
                        confirmButtonText: "OK",
                        closeOnConfirm: false,
                        showConfirmButton: true,
                    });
                }
            },
            error: (e, r) => {
                console.log(e, r);
                swal({
                    title: "Error!",
                    text: "Update failed.",
                    type: "error",
                    showCancelButton: false,
                    confirmButtonText: "OK",
                    closeOnConfirm: false,
                    showConfirmButton: <?= $this->config->item('showConfirmButton') ?>,
                    timer: <?= $this->config->item('timer') ?>,
                });
            },
        }).always(() => {
            $('#load-screen').hide();
        });
        return false;
    };


    jQuery(document).ready(function() {
        //success/error message sweet alert pop  start
        <?php if ($this->session->flashdata('status') && $this->session->flashdata('status') == 'success') { ?>
        swal({
            title: "Success!",
            text: "<?php echo $this->session->flashdata('success_msg') ?>",
            type: "success",
            confirmButtonClass: "btn-success",
            showConfirmButton: <?php echo $this->config->item('showConfirmButton'); ?>,
            timer: <?php echo $this->config->item('timer') ?>
        });
        
        <?php } else if ($this->session->flashdata('status') && $this->session->flashdata('status') =='error') { ?>
        swal({
            title: "Error!",
            text: "<?php echo $this->session->flashdata('error_msg') ?>",
            type: "error",
            confirmButtonClass: "btn-danger"
        });
        <?php } ?>
        //success/error message sweel alert pop  end

        jQuery(".btn-delete-file3").click(function() {
            var id = $(this).data('id');
            console.log('Delete 3: ' + id);
        });

        jQuery("#btn_update_vehicle").click(function() {
            var rego_expires = jQuery("#rego_expires").val();
            var staff_id = jQuery("#staff_id").val();
            var wof = jQuery("#wof").val();
            var vehicle_ownership = jQuery("#vehicle_ownership").val();
            var error = "";

            if (rego_expires == "") {
                error += "Rego expiry date is required\n";
            }
            if (wof == "") {
                error += "WOF is required\n";
            }
            if (staff_id == "") {
                error += "SATS user is required\n";
            }
            if (vehicle_ownership == '') {
                error += "Ownership required\n";
            }

            if (error != "") {
                swal('', error, 'error');
                return false;
            } else {
                jQuery("#frm_vehicle").submit();
            }

        });

        jQuery("#btn_update_insurance").click(function() {
            var error = "";

            if (error != "") {
                swal('', error, 'error');
                return false;
            } else {
                jQuery("#frm_vehicle").submit();
            }
        });

        jQuery("#btn-add-servicing").click(function() {
            var log_date = jQuery("#log_date").val();
            var log_price = jQuery("#log_price").val();
            var log_details = jQuery("#log_details").val();

            var error = "";

            if (log_date == "") {
                error += "Date is required\n";
            }

            if (log_price == "") {
                error += "Price is required\n";
            }

            if (log_details == "") {
                error += "Details is required\n";
            }

            if (error != "") {
                swal('', error, 'error');
                return false;
            } else {
                jQuery("#frm_vehicle").submit();
            }
        });
        $('#staff_id').on('change', function() {
            var obj = $(this);
            var thisval = $(this).val();
            $('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/vehicles/ajax_duplicate_vehicle_user",
                dataType: "json",
                data: {
                    staffid: thisval,
                }
            }).done(function(ret) {
                $('#load-screen').hide();
                if (ret.status == true) {
                    swal('', 'Technician has already been assigned to a vehicle', 'error');
                    obj.find('option:first').prop('selected', 'selected');
                    return false;
                }
            });
        });

        //Change Vehicle Pic
        function processSelectedFile(input, $element) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $element.attr('src', e.target.result).show();
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        $('.button-upload_photo').on("click", (evt) => {
            var target = $(evt.target).data("target");
            $(target).click();
        });

        $(".hidden-file").on("change", (evt) => {
            const input = evt.target;
            var imageTarget = $(input).data("target");
            processSelectedFile(input, $(imageTarget));
        });

        $('#file-vehicle_pic').on('change', function(e) {
            e.preventDefault();
            $('#load-screen').show();
            var fd = new FormData();
            var files = $(this)[0].files;
            var vehicles_id = $('#vehicle_image_id').val();
        
        // Check file selected or not
        if(files.length > 0 ){
           fd.append('vehicle_pic',files[0]);
           fd.append('vehicles_id',vehicles_id);
           $.ajax({
              url: '/vehicles/upload_vehicle_pic',
              type: 'post',
              data: fd,
              contentType: false,
              processData: false,
              success: function(response){
                $('#load-screen').hide();
                $("#image-vehicle_pic").attr("src","/images/vehicle/". response);
                $('#hide_delete_btn').show();
                swal({
                    title: "Success!",
                    text: "Vehicle picture updated.",
                    type: "success",
                    confirmButtonClass: "btn-success",
                });
              },error: function(error){
                $('#hide_delete_btn').hide();
                $('#load-screen').hide();
                console.log(error);
                swal({
                    title: "Error!",
                    text: "Vehicle not uploaded, Please try again.",
                    type: "error",
                    confirmButtonClass: "btn-warning",
                });
              },
           });
        }else{
            $('#load-screen').hide();
                swal({
                    title: "Error!",
                    text: "Please select a file.",
                    type: "error",
                    confirmButtonClass: "btn-danger",
                });
            }

        });

        //Delete Vehicle Picture
        $("#button-delete_vehicle_pic").on("click", (evt) => {
            swal({
                title: "Warning!",
                text: "This will remove the vehicle's picture, do you want to continue??",
                type: "warning",
                showCloseButton: false,
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Remove",
                cancelButtonClass: "btn-primary",
                cancelButtonText: "No",
            }, function(response) {
                if (response) {
                    $("#load-screen").show();
                    $.ajax({
                        type: "delete",
                        url: "/vehicles/remove_vehicle_pic/<?= $vehicle->vehicles_id; ?>",
                        dataType: "json",
                        success: (response) => {
                            if (response.success) {
                                $("#image-vehicle_pic").attr("src","").val('');;
                                
                                $('#hide_delete_btn').hide();
                                swal({
                                    title: "Success!",
                                    text: "Vehicle picture deleted.",                                                                            
                                    type: "success",
                                    confirmButtonClass: "btn-success",
                                });
                            } else {
                                swal({
                                    title: "Error!",
                                    text: "Vehicle picture not deleted. Something went wrong.",
                                    type: "error",
                                    confirmButtonClass: "btn-danger",
                                });
                            }
                        }
                    }).always(() => {
                        $("#load-screen").hide();
                    })
                }
            });
        });
    });

    function reload_table() {
        $('#servicing_table').DataTable().ajax.reload();
        $('#tools_table').DataTable().ajax.reload();
        $('#files_table').DataTable().ajax.reload();
    }

</script>
