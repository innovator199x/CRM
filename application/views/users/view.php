<?php

$vaccineBrands = [
	"1" => "AstraZeneca",
	"2" => "Pfizer",
	"3" => "Moderna",
	"4" => "Janssen",
];

$locationAccess = "";

$accessibleCountries = [];
foreach($countryAccess as $ca) {
	if ($ca["status"] == "1") {
		$accessibleCountries[] = $ca["iso"];
	}
}

$accessibleStates = [];
foreach ($states as $state) {
	if ($state["selected"] == "1") {
		$accessibleStates[] = $state["state"];
	}
}
$locationAccess = implode(" | ", array_merge($accessibleCountries, $accessibleStates));

$driversLicence = [];
if ($user["driver_license_num"] && !empty($user["driver_license_num"])) {
	$driversLicence[] = $user["driver_license_num"];
}
if ($this->customlib->isDateNotEmpty($user["licence_expiry"])) {
	$driversLicence[] = $this->customlib->formatYmdToDmy($user["licence_expiry"], true);
}

if (!empty($driversLicence)) {
	$driversLicence = implode(" | ", $driversLicence);
}
else {
	$driversLicence = "No Data";
}

$address = "No Data";
if (!empty($user["address"])) {
	$address = $user["address"];
}

$callCentreAgent = "No Data";
if (!is_null($user["other_call_centre"]) && $user["other_call_centre"] != "0") {
	$callCentreAgent = "{$user["cc_firstname"]} {$user["cc_lastname"]}";
}

$blueCard = [];
if ($user["blue_card_num"] && !empty($user["blue_card_num"])) {
	$blueCard[] = $user["blue_card_num"];
}
if ($this->customlib->isDateNotEmpty($user["blue_card_expiry"])) {
	$blueCard[] = $this->customlib->formatYmdToDmy($user["blue_card_expiry"], true);
}

if (!empty($blueCard)) {
	$blueCard = implode(" | ", $blueCard);
}
else {
	$blueCard = "No Data";
}

$saPosition = "No Data";
if (!empty($user["sa_position"])) {
	$saPosition = $user["sa_position"];
}

$startDate = "No Data";
if ($this->customlib->isDateNotEmpty($user["start_date"])) {
	$startDate = $this->customlib->formatYmdToDmy($user["start_date"], true);
}

$electricalLicence = [];
if ($user["elec_license_num"] && !empty($user["elec_license_num"])) {
	$electricalLicence[] = $user["elec_license_num"];
}
if ($this->customlib->isDateNotEmpty($user["elec_licence_expiry"])) {
	$electricalLicence[] = $this->customlib->formatYmdToDmy($user["elec_licence_expiry"], true);
}

if (!empty($electricalLicence)) {
	$electricalLicence = implode(" | ", $electricalLicence);
}
else {
	$electricalLicence = "No Data";
}

$driverLicence = [];
if ($user["driver_license_num"] && !empty($user["driver_license_num"])) {
	$driverLicence[] = $user["driver_license_num"];
}
if ($this->customlib->isDateNotEmpty($user["driver_licence_expiry"])) {
	$driverLicence[] = $this->customlib->formatYmdToDmy($user["elec_licence_expiry"], true);
}

if (!empty($driverLicence)) {
	$driverLicence = implode(" | ", $driverLicence);
}
else {
	$driverLicence = "No Data";
}

$emergencyContact = [];
if (!empty($user["ice_name"])) {
	$emergencyContact[] = $user["ice_name"];
}
if (!empty($user["ice_phone"])) {
	$emergencyContact[] = $user["ice_phone"];
}
if (!empty($emergencyContact)) {
	$emergencyContact = implode(" | ", $emergencyContact);
}
else {
	$emergencyContact = "No Data";
}

$dateOfBirth = "No Data";
if ($this->customlib->isDateNotEmpty($user["dob"])) {
	$dateOfBirth = $this->customlib->formatYmdToDmy($user["dob"], true);
}

$contactNumber = "No Data";
if (!empty($user["ContactNumber"])) {
	$contactNumber = $user["ContactNumber"];
}

$vehiclesText = "No Data";
if (!empty($vehicles)) {
	$vehiclesText = [];
	foreach ($vehicles as $v) {
		$vehiclesText[] = $v["number_plate"];
	}
	$vehiclesText = implode(" | ", $vehiclesText);
}

$tableDetails = "No Data";
if (!empty($user["ipad_model_num"])) {
	$tableDetails = $user["ipad_model_num"];
}

$personalContactNumber = "No Data";
if (!empty($user["personal_contact_number"])) {
	$personalContactNumber = $user["personal_contact_number"];
}

$vaccinationText = "No Data";
if (!is_null($vaccinations) && !empty($vaccinations)) {
	$latestVaccination = $vaccinations[0];
	$vaccinationText = "{$vaccineBrands[$latestVaccination["vaccine_brand"]]} | " . $this->customlib->formatYmdToDmy($latestVaccination["completed_on"], true);
}

$workingDaysText = "No Data";
if (!empty($user["working_days"])) {
	$workingDaysText = implode(" | ", explode(",", $user["working_days"]));
}

$debitCard = "No Data";

$plantID = "No Data";
if (!empty($user["other_plant_id"])) {
	$plantID = $user["other_plant_id"];
}

$shirtSize = "No Data";
if (!empty($user["other_shirt_size"])) {
	$shirtSize = $user["other_shirt_size"];
}

$keyNumber = "No Data";
if (!empty($user["other_key_num"])) {
	$keyNumber = $user["other_key_num"];
}

$deviceAccountsText = "No Data";
if (!empty($deviceAccounts)) {
	$deviceAccountsText = count($deviceAccounts) . " account(s)";
}

$laptopDetail = "No Data";
if (!empty($user["laptop_make"])) {
	$laptopDetail = $user["laptop_make"];
}

?>


<div class="box-typical box-typical-padding">

	<?php
		// breadcrumbs template
		$bc_items = [
            [
				'title' => "SATS USERS",
				'status' => 'inactive',
				'link' => "/users/index",
			],
            [
				'title' => $title,
				'status' => 'active',
				'link' => $_SERVER['SELF'],
			],
        ];
		$bc_data['bc_items'] = $bc_items;
		$this->load->view('templates/breadcrumbs', $bc_data);
	?>


	<?php
	$validationErrors = validation_errors();
	if( $validationErrors ){ ?>
		<div class="alert alert-danger">
		<?= $validationErrors ?>
		</div>
	<?php
	}
	?>

	<?php if ($user['active'] == 0): ?>
	<div class="alert alert-warning" role="alert">
		This account is currently not active.
	</div>
	<?php endif ?>


	<div class="tabs-section">
		<div class="tabs-section-nav tabs-section-nav-icons">
			<div class="tbl">
				<ul class="nav" role="tablist">
					<li class="nav-item">
						<a class="nav-link <?= $tab == "personal" ? "active show" : "" ?>" href="/users/view/<?= $user["StaffID"] ?>/personal">
							<span class="nav-link-in">
								<i class="fa fa-user"></i>
								Personal Details
							</span>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link <?= $tab == "logs" ? "active show" : "" ?>" href="/users/view/<?= $user["StaffID"] ?>/logs">
							<span class="nav-link-in">
								<i class="fa fa-file"></i>
								Logs
							</span>
						</a>
					</li>
				</ul>
			</div>
		</div>

		<div class="tab-content">

			<?php
			if (in_array($tab, ["devices", "logs"])):

				$this->load->view("users/view_templates/{$tab}");

			else:
			?>

			<div class="box-typical-body">
				<div class="row mb-1">
					<div class="col-12 text-left">
						<div class="row">
							<div class="col-3">
								<label>Name</label>
								<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-name" href="javascript:;"><?= $user['FirstName'] ?> <?= $user['LastName'] ?></a>
							</div>

							<div class="col-3">
								<label>Email</label>
								<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-email" href="javascript:;"><?= $user['Email'] ?></a>
							</div>
							<div class="col-3">
								<label>Location Access</label>
								<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-location_access" href="javascript:;"><?= $locationAccess ?></a>
							</div>
							<div class="col-3">
								<label>Driver&apos;s Licence</label>
								<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-drivers_licence" href="javascript:;"><?= $driversLicence ?></a>
							</div>
						</div>

					</div>
				</div>
				<div class="row mb-1">
					<div class="col-12 text-left">
						<div class="row">
							<div class="col-3">
								<label>Address</label>
								<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-address" href="javascript:;" data-fancybox-helpers="{overlay:{closeClick:false}}"><?= $address ?></a>
							</div>

							<div class="col-3">
								<label>User ID</label>
								<a href="javascript:;" style="cursor: default;"><?= $user["StaffID"] ?></a>
							</div>

							<div class="col-3">
								<label>Call Centre</label>
								<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-call_centre" href="javascript:;"><?= $callCentreAgent ?></a>
							</div>

							<div class="col-3">
								<label>Blue Card</label>
								<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-blue_card" href="javascript:;"><?= $blueCard ?></a>
							</div>
						</div>
					</div>
				</div>
				<div class="row mb-1">
					<div class="col-12 text-left">
						<div class="row">
							<div class="col-3">
								<label>User Class</label>
								<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-class" href="javascript:;"><?= $user["sc_classname"] ?></a>
							</div>

							<div class="col-3">
								<label>Position</label>
								<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-position" href="javascript:;"><?= $saPosition ?></a>
							</div>

							<div class="col-3">
								<label>Start Date</label>
								<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-start_date" href="javascript:;"><?= $startDate ?></a>
							</div>

							<div class="col-3">
								<label>Electrical Licence</label>
								<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-electrical_licence" href="javascript:;"><?= $electricalLicence ?></a>
							</div>
						</div>
					</div>
				</div>
				<div class="row mb-1">
					<div class="col-12 text-left">
						<div class="row">
							<div class="col-3">
								<label>Emergency Contact</label>
								<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-emergency_contact" href="javascript:;"><?= $emergencyContact ?></a>
							</div>

							<div class="col-3">
								<label>Status</label>
								<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-status" href="javascript:;"><?= $user["active"] == "1" ? "Active" : "Inactive " ?></a>
							</div>

							<div class="col-3">
								<label>Date of Birth</label>
								<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-dob" href="javascript:;"><?= $dateOfBirth ?></a>
							</div>

							<div class="col-3">
								<label>Electrician</label>
								<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-electrical_licence" href="javascript:;"><?= $user["is_electrician"] == "1" ? "Yes" : "No" ?></a>
							</div>
						</div>
					</div>
				</div>
				<div class="row mb-1">
					<div class="col-12 text-left">
						<div class="row">
							<div class="col-3">
								<label>Mobile Phone</label>
								<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-phone" href="javascript:;"><?= $contactNumber ?></a>
							</div>

							<div class="col-3">
								<label>Vehicle</label>
								<a href="/vehicles/view_vehicles"><?= $vehiclesText ?></a>
							</div>
							<div class="col-3">
								<label>Password</label>
								<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-password" href="javascript:;">Click to Update</a>
							</div>
							<div class="col-3">
								<label>Tablet Details</label>
								<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-tablet" href="javascript:;"><?= $tableDetails ?></a>
							</div>
						</div>
					</div>
				</div>

				<div class="row mb-1">
					<div class="col-12 text-left">
						<div class="row">
							<div class="col-3">
								<label>Personal Contact Number</label>
								<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-personal_contact_number" href="javascript:;"><?= $personalContactNumber ?></a>
							</div>

							<div class="col-3">
								<label>COVID-19 Vaccination</label>
								<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-vaccination" href="javascript:;"><?= $vaccinationText ?></a>
							</div>
							<div class="col-3">
								<label>Working Days <?php if ($user["ClassID"] == 6) { echo "& Hours"; }?></label>
								<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-working_days" href="javascript:;"><?= $workingDaysText ?></a>
							</div>
							<div class="col-3">

								<label>Device Accounts</label>
								<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-device_accounts" href="javascript:;"><?= $deviceAccountsText ?></a>

							</div>
						</div>
					</div>
				</div>

				<div class="row">
                    <div class="col-xs-12 col-lg-4">
                        <section class="widget widget-reports">
                            <header class="widget-header widget-header-blue">
                                Photo (Max 3MB)
                            </header>
                            <div class="widget-content">
                                <div class="form-row pt-2">
                                    <div class="col-12 text-center">
										<form id="form-user-profile_pic" action="/users/upload_profile_pic" method="post" enctype="multipart/form-data">
											<input type="hidden" name="StaffID" value="<?= $user["StaffID"] ?>" />
											<?php
											if (!empty($user['profile_pic'])):
											?>
											<img width="100%" id="image-user_profile_pic" src="/images/staff_profile/<?= $user['profile_pic'] ?>"
												class="mx-auto w-100" style="max-width: 320px;" />
											<?php
											else:
											?>
											<img width="100%" id="image-user_profile_pic"
												class="mx-auto w-100" style="display: none; max-width: 320px;" />
											<?php
											endif;
											?>
											<div class="text-center mt-2 mb-3 mx-auto" style="max-width: 320px;" >
												<div class="d-flex w-100" style="align-items: center; justify-content: center;">
													<?php if (!empty($user["profile_pic"])): ?>
													<button type="button" class="btn btn-danger button-remove_photo" id="button-delete_user_profile_pic">Remove Photo</button>

													<div style="flex-grow: 1"></div>
													<?php endif; ?>

													<button type="button"
														id="button-user_profile_pic"
														class="btn btn-primary button-upload_photo"
														data-target="#file-user_profile_pic"
													>Choose Photo</button>

													<input type="file" accept="image/jpeg, image/png" class="d-none hidden-file" name="user_profile_pic" id="file-user_profile_pic" data-target="#image-user_profile_pic"/>
												</div>
											</div>
										</form>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="col-xs-12 col-lg-8">
                        <section class="widget widget-reports">
                            <header class="widget-header widget-header-blue">
                                Other Details
                            </header>
                            <div class="widget-content">
                                <div class="form-row pt-2">
                                    <div class="col-4 mb-2">
										<label>Plant ID</label>
                                    </div>
									<div class="col-8 mb-2">
										<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-plant_id" href="javascript:;"><?= $plantID ?></a>
									</div>
                                    <div class="col-4 mb-2">
										<label>Shirt Size</label>
                                    </div>
									<div class="col-8 mb-2">
										<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-shirt_size" href="javascript:;"><?= $shirtSize ?></a>
									</div>
                                    <div class="col-4 mb-2">
										<label>Key Number</label>
                                    </div>
									<div class="col-8 mb-2">
										<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-key_number" href="javascript:;"><?= $keyNumber ?></a>
									</div>
                                    <div class="col-4 mb-2">
										<label>Display on Weekly Sales Report</label>
                                    </div>
									<div class="col-8 mb-2">
										<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-weekly_sales_report" href="javascript:;"><?= $user["display_on_wsr"] == "1" ? "Yes" : "No" ?></a>
									</div>
                                    <div class="col-4 mb-2">
										<label>Receive Weekly Sales Report</label>
                                    </div>
									<div class="col-8 mb-2">
										<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-weekly_sales_report" href="javascript:;"><?= $user["recieve_wsr"] == "1" ? "Yes" : "No" ?></a>
									</div>
									<div class="col-4 mb-2">
										<label>Laptop Details</label>
                                    </div>
									<div class="col-8 mb-2">
										<a data-auto-focus="false" data-fancybox data-src="#fancybox-user-laptop" href="javascript:;"><?= $laptopDetail ?></a>
									</div>
                                </div>
                            </div>
                        </section>
                    </div>
				</div>
			</div>

			<?php endif; ?>
		</div>
	</div>

</div>

<!-- Fancybox Start -->

<div class="fancybox-form-container" id="fancybox-user-name" style="display:none;">
	<form class="fancybox-form" data-tables='<?= json_encode(["staff_accounts" => ["_idValue" => $user["StaffID"], "_idField" => "StaffID"]]) ?>'>
		<section class="card card-blue-fill">
			<header class="card-header">User Name</header>
			<div class="card-block">
				<div class="form-group">
					<label class="form-label">First Name</label>
					<input type="text" class="form-control editable-field" required data-table="staff_accounts" data-field="FirstName" value="<?= $user['FirstName'] ?>">
					<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="FirstName" value="<?= $user['FirstName'] ?>">
				</div>
				<div class="form-group">
					<label class="form-label">Last Name</label>
					<input type="text" class="form-control editable-field" required data-table="staff_accounts" data-field="LastName" value="<?= $user['LastName'] ?>">
					<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="LastName" value="<?= $user['LastName'] ?>">
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container" id="fancybox-user-email" style="display:none;">
	<form class="fancybox-form" data-tables='<?= json_encode(["staff_accounts" => ["_idValue" => $user["StaffID"], "_idField" => "StaffID"]]) ?>'>
		<section class="card card-blue-fill">
			<header class="card-header">Email</header>
			<div class="card-block">
				<div class="form-group">
					<input type="text" class="form-control editable-field" required data-table="staff_accounts" data-field="Email" value="<?= $user['Email'] ?>">
					<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="Email" value="<?= $user['Email'] ?>">
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container different" id="fancybox-user-location_access" style="display:none;">
	<form action="/users/update_location_access" method="post" class="fancybox-form different" data-tables='<?= json_encode(["staff_accounts" => ["_idValue" => $user["StaffID"], "_idField" => "StaffID"]]) ?>'>
		<input type="hidden" name="StaffID" value="<?= $user["StaffID"] ?>">
		<section class="card card-blue-fill">
			<header class="card-header">Location Access</header>
			<?php
			$countryAbbr = [
				1 => "AU",
				2 => "NZ",
			];
			$countryAccessWithDefaultValues = [
				0 => [
					"staff_accounts_id" => $user["StaffID"],
					"country_id" => 1,
					"default" => $this->config->item("country") == 1,
					"status" => $this->config->item("country") == 1,
				],
				1 => [
					"staff_accounts_id" => $user["StaffID"],
					"country_id" => 2,
					"default" => $this->config->item("country") == 2,
					"status" => $this->config->item("country") == 2,
				],
			];
			$countryAccessToUse = [];
			$countryIds = array_column($countryAccess, "country_id");
			foreach($countryAccessWithDefaultValues as &$ca1) {
				$key = array_search($ca1["country_id"], $countryIds);
				if ($key != false) {
					$countryAccessToUse[] = $countryAccess[$key];
				}
				else {
					$countryAccessToUse[] = $ca1;
				}
			}
			?>
			<div class="card-block">
				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label>Country</label>
						</div>
						<div class="col-8">
							<?php foreach($countryAccessToUse as $index => $ca): ?>
								<span class="checkbox d-inline-block mb-2 mr-2">
									<input
										type="hidden" name="country_access[<?= $index ?>][staff_accounts_id]" value="<?= $user['StaffID'] ?>"
									/>
									<input
										type="hidden" name="country_access[<?= $index ?>][country_id]" value="<?= $ca["country_id"] ?>"
									/>
									<input
										type="hidden" name="country_access[<?= $index ?>][default]" value="<?= $ca["default"] ?>"
									/>
									<input
										type="hidden" name="country_access[<?= $index ?>][status]" value="0"
									/>
									<input
										type="checkbox" name="country_access[<?= $index ?>][status]" id="country_access-<?= $index ?>-status"
										value="1"
										<?= set_checkbox("country_access[{$index}][status]", 1, set_value("country_access[{$index}][status]", $ca["status"]) == 1 || $this->config->item("country") == $ca["country_id"]) ?>
									/>
									<label for="country_access-<?= $index ?>-status"><?= $countryAbbr[$ca["country_id"]] ?></label>
								</span>
							<?php endforeach ?>
						</div>
					</div>
				</div>


				<?php if (!empty($states)): ?>
				<div class="form-group">

					<div class="form-row pt-2" id="states-row">
						<div class="col-4">
							<label>States</label>
						</div>
						<div class="col-8">
							<?php
							$countryId = $this->config->item('country');
							foreach ($states as $index => $state):
								$checked = set_checkbox("states[{$index}][selected]", 1, $state['selected'] == 1);
							?>
								<span class="checkbox d-inline mr-2">
									<?php if($state['selected'] == "1"): ?>
										<input
											type="hidden"
											name="og_states[<?= $index ?>]"
											value="<?= $state['StateID'] ?>"
										/>
									<?php endif ?>
									<input
										type="hidden"
										name="states[<?= $index ?>][StateID]"
										value="<?= $state['StateID'] ?>"
									/>
									<input
										type="hidden"
										name="states[<?= $index ?>][selected]"
										value="0"
									/>
									<input
										type="checkbox"
										name="states[<?= $index ?>][selected]"
										id="states-<?= $index ?>-selected"
										<?= $checked ?>
										value="1"
										class="states-checkbox"
									/>
									<label for="states-<?= $index ?>-selected">
										<?= $state['state'] ?>
									</label>
								</span>
							<?php
							endforeach;
							?>
						</div>
					</div>
				</div>
				<?php endif ?>
			</div>
		</section>

		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container" id="fancybox-user-drivers_licence" style="display:none;">
	<!--<form class="fancybox-form" data-tables='<?= json_encode(["staff_accounts" => ["_idValue" => $user["StaffID"], "_idField" => "StaffID"]]) ?>'> -->
	<form action="/users/update_driver_license" method="post" enctype="multipart/form-data" class="fancybox-form different" data-tables='<?= json_encode(["staff_accounts" => ["_idValue" => $user["StaffID"], "_idField" => "StaffID"]]) ?>'>
		<input type="hidden" name="StaffID" value="<?= $user["StaffID"] ?>">
		<section class="card card-blue-fill">
			<header class="card-header">Driver&apos;s Licence</header>
			<div class="card-block">
				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label>Licence Number</label>
						</div>
						<?php 
							//print_r($user);
						?>
						<div class="col-8">
							<?php
								if(empty($user['driver_license_num'])){
									$license_val = "";
								}
								else{
									$license_val = $user['driver_license_num'];
								}
							?>
							<input type="text" name="user[driver_license_num]" class="form-control editable-field" data-table="staff_accounts" data-field="driver_license_num" value="<?= $license_val; ?>">
							<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="driver_license_num" value="<?= $license_val; ?>">
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label>Licence Expiry</label>
						</div>
						<div class="col-8">
							<?= sats_form_input_date([
								'variable' => $user,
								'post_var_name' => 'user',
								'post_field_key' => 'licence_expiry',
								'other_classes' => 'date-field editable-field',
								'other_props' => 'data-table="staff_accounts" data-field="licence_expiry"',
							]) ?>
							<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="licence_expiry" value="<?= $this->customlib->formatYmdToDmy($user['licence_expiry'], true) ?>">
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label>Licence Image</label>
						</div>
						<div class="col-8 text-center">
							<div class="mx-auto" style="max-width: 320px;">
								<?php
								if (!empty($user['driver_license'])):
									$style = "align-items: center; justify-content: center;";
								?>
								<img id="image-user_driver_license"
									src="/images/driver_license/<?= $user['driver_license'] ?>"
									class="w-100 mx-auto" style="max-height: 320px;" />
								<?php
								else:
									$style = "align-items: center;";
								?>
								<img id="image-user_driver_license"
									class="w-100 mx-auto" style="display: none; max-height: 320px;" />
								<?php
								endif;
								?>
								<div class="d-flex mt-2 mb-3 w-100" style="<?php echo $style; ?>">
									<button type="button" class="btn btn-danger button-remove_photo d-none" id="button-delete_user_driver_license">Remove Photo</button>

									<div id="spacer-delete_user_driver_license" class="d-none" style="flex-grow: 1"></div>

									<button type="button"
										id="button-user_driver_license"
										class="btn btn-primary button-upload_photo"
										data-target="#file-user_driver_license"
									>Choose Photo</button>

									<input type="file" accept="image/jpeg, image/png" class="d-none hidden-file" name="user_driver_license" id="file-user_driver_license" data-target="#image-user_driver_license"/>

									<input type="hidden" name="user[driver_license]" id="hidden-user_driver_license" value="<?= $user['driver_license'] ?>" />
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container different" id="fancybox-user-address" style="display:none;">
	<form class="fancybox-form different" action="/users/update_address" method="post">
		<input type="hidden" name="StaffID" value="<?= $user["StaffID"] ?>">
		<input type="hidden" name="accommodation_id" value="<?= $user["accomodation_id"] ?>" />
		<input type="hidden" name="ClassID" value="<?= $user["ClassID"] ?>" />
		<section class="card card-blue-fill">
			<header class="card-header">Address</header>
			<div class="card-block">
				<div class="form-row align-items-center">

					<?php if ($user["ClassID"] != 6): ?>
					<div class="col-12">
						<input type="text" class="form-control pac-target-input" name="user[address]" id="user-address" placeholder="Enter a location" autocomplete="off" value="<?= $user['address'] ?>">
					</div>

					<?php else: ?>

					<div class="col-4 mb-1">
						<label for="user-address">Address</label>
					</div>
					<div class="col-8 mb-1">
						<input type="search" class="form-control pac-target-input" name="user[address]" id="user-address" placeholder="Enter a location" autocomplete="off" value="<?= $user['address'] ?>">
					</div>

					<div class="col-4 mb-1">
						<label for="user-address">Street Number</label>
					</div>
					<div class="col-8 mb-1">
						<input type="text" name="accommodation[street_number]" id="street_number" class="form-control" value="<?=$accommodation["street_number"]; ?>" />
					</div>

					<div class="col-4 mb-1">
						<label for="user-address">Street Name</label>
					</div>
					<div class="col-8 mb-1">
						<input type="text" name="accommodation[street_name]" id="street_name" class="form-control" value="<?=$accommodation["street_name"]; ?>" />
					</div>

					<div class="col-4 mb-1">
						<label for="user-address">Suburb</label>
					</div>
					<div class="col-8 mb-1">
						<input type="text" name="accommodation[suburb]" id="suburb" class="form-control" value="<?=$accommodation["suburb"]; ?>" />
						<input type="hidden" id="locality" />
						<input type="hidden" id="sublocality_level_1" />
					</div>

					<div class="col-4 mb-1">
						<label for="user-address">State</label>
					</div>
					<div class="col-8 mb-1">
						<!--<input type="text" name="accommodation[state]" id="state" class="form-control" value="<?=$accommodation["state"]; ?>" />-->
						<select id="state" name="state" class="form-control">  
							<option value="">----</option>							                  
							<?php
							//states dropdown filter
							$state_query = $this->db->select('state')
							->from('states_def')
							->where('country_id', $this->config->item('country'))
							->order_by('state','ASC')
							->get();
							foreach( $state_query->result() as $state_row ){ ?>
								<option value="<?php echo $state_row->state; ?>" <?php echo (  $state_row->state == $accommodation["state"] )?'selected="selected"':''; ?>><?php echo $state_row->state; ?></option>		
							<?php
							}
							?>
						</select>
					</div>

					<div class="col-4 mb-1">
						<label for="user-address">Postcode</label>
					</div>
					<div class="col-8 mb-1">
						<input type="text" name="accommodation[postcode]" id="postcode" class="form-control" value="<?=$accommodation["postcode"]; ?>" />
					</div>

					<!--
					<div class="col-4 with-address-only my-1 d-none">
						<label>State</label>
					</div>
					<div class="col-8 with-address-only my-1 d-none pl-3">
						<span id="display-address-state"></span>
					</div>
					-->

					<div class="col-4 with-address-only my-1 d-none">
						<label>Region</label>
					</div>
					<div class="col-8 with-address-only my-1 d-none pl-3">
						<span id="display-address-region"></span>
					</div>

					<div class="col-4 with-address-only my-1 d-none">
						<label>Sub Region</label>
					</div>
					<div class="col-8 with-address-only my-1 d-none pl-3">
						<span id="display-address-subregion"></span>
					</div>
					
					<!--
					<div class="col-4 with-address-only my-1 d-none address-postcodes">
						<label>Postcode</label>
					</div>
					<div class="col-8 with-address-only my-1 d-none pl-3 address-postcodes">
						<span id="display-address-postcode"></span>
					</div>
					-->

					<?php if ($accommodation): ?>
					<input type="hidden" name="accommodation[name]" id="accommodation-name" value="<?= $user["FirstName"] ?> <?= $user["LastName"] ?>" />
					<input type="hidden" name="accommodation[address]" id="accommodation-address" value="<?= $user["address"] ?>" />
					<input type="hidden" name="accommodation[lat]" id="accommodation-lat" value="<?= $accommodation["lat"] ?>" class="input-lat" />
					<input type="hidden" name="accommodation[lng]" id="accommodation-lng" value="<?= $accommodation["lng"] ?>" class="input-lng" />
					<input type="hidden" name="accommodation[postcode]" id="accommodation-postcode" value="<?= $accommodation["postcode"] ?>" class="input-postcode" />
					<input type="hidden" name="accommodation[assigned_region]" id="accommodation-assigned_region" value="<?= $accommodation["assigned_region"] ?>" />
					<?php else: ?>
					<input type="hidden" name="accommodation[name]" id="accommodation-name" value="<?= $user["FirstName"] ?> <?= $user["LastName"] ?>" />
					<input type="hidden" name="accommodation[address]" id="accommodation-address" value="<?= $user["address"] ?>" />
					<input type="hidden" name="accommodation[lat]" id="accommodation-lat" value="0.0" class="input-lat" />
					<input type="hidden" name="accommodation[lng]" id="accommodation-lng" value="0.0" class="input-lng" />
					<input type="hidden" name="accommodation[postcode]" id="accommodation-postcode" value="" class="input-postcode" />
					<input type="hidden" name="accommodation[assigned_region]" id="accommodation-assigned_region" value="" />

					<input type="hidden" name="accommodation[area]" id="accommodation-area" value="1 Staff" />
					<input type="hidden" name="accommodation[rate]" id="accommodation-rate" value="0.0" />
					<input type="hidden" name="accommodation[comment]" id="accommodation-comment" value="STAFF" />
					<?php endif; ?>

					<div class="col-4 my-1">
						<label>Assigned Region</label>
					</div>
					<div class="col-8 my-1">
						<input type="text" class="form-control" id="input-accommodation-assigned_region" style="max-width: 240px;" value="<?= $accommodation["assigned_region_name"] ?? "" ?>" />

						<!-- <input type="hidden" name="accommodation[assigned_region_type]" id="accommodation-assigned_region_type" /> -->
					</div>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container" id="fancybox-user-call_centre" style="display:none;">
	<form class="fancybox-form" data-tables='<?= json_encode(["staff_accounts" => ["_idValue" => $user["StaffID"], "_idField" => "StaffID"]]) ?>'>
		<section class="card card-blue-fill">
			<header class="card-header">Call Centre</header>
			<div class="card-block">
				<div class="form-group">
					<select class="form-control editable-field" data-table="staff_accounts" data-field="other_call_centre" >
						<option value="">---Select---</option>
						<?php foreach ($callCentres as $callCentre): ?>
						<option value="<?php
							echo $callCentre['StaffID'];
						?>" <?= set_select('user[other_call_centre]', $callCentre['StaffID'], $user['other_call_centre'] == $callCentre['StaffID']) ?>>
							<?php echo $this->system_model->formatStaffName($callCentre['FirstName'], $callCentre['LastName']); ?>
						</option>
						<?php endforeach; ?>
					</select>
					<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="Email" value="<?= $user['Email'] ?>">
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container" id="fancybox-user-blue_card" style="display:none;">
	<form class="fancybox-form" data-tables='<?= json_encode(["staff_accounts" => ["_idValue" => $user["StaffID"], "_idField" => "StaffID"]]) ?>'>
		<section class="card card-blue-fill">
			<header class="card-header">Blue Card</header>
			<div class="card-block">
				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label>Blue Card Number</label>
						</div>
						<div class="col-8">
							<input type="text" class="form-control editable-field" data-table="staff_accounts" data-field="blue_card_num" value="<?= $user['blue_card_num'] ?>">
							<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="blue_card_num" value="<?= $user['blue_card_num'] ?>">
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label>Blue Card Expiry</label>
						</div>
						<div class="col-8">
							<?= sats_form_input_date([
								'variable' => $user,
								'post_var_name' => 'user',
								'post_field_key' => 'blue_card_expiry',
								'other_classes' => 'date-field editable-field',
								'other_props' => 'data-table="staff_accounts" data-field="blue_card_expiry"',
							]) ?>
							<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="blue_card_expiry" value="<?= $this->customlib->formatYmdToDmy($user['blue_card_expiry'], true) ?>">
						</div>
					</div>
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container" id="fancybox-user-class" style="display:none;">
	<form class="fancybox-form" data-tables='<?= json_encode(["staff_accounts" => ["_idValue" => $user["StaffID"], "_idField" => "StaffID"]]) ?>'>
		<section class="card card-blue-fill">
			<header class="card-header">User Class</header>
			<div class="card-block">
				<div class="form-group">
					<select class="form-control editable-field" data-table="staff_accounts" data-field="ClassID">
						<?php foreach ($staffClasses as $staffClass): ?>
						<option
							value="<?= $staffClass['ClassID'] ?>"
							<?= set_select('user[ClassID]', $staffClass['ClassID'], $user['ClassID'] == $staffClass['ClassID']) ?>
						><?= $staffClass['ClassName'] ?></option>
						<?php endforeach; ?>
					</select>
					<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="ClassID" value="<?= $user['ClassID'] ?>">
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container" id="fancybox-user-position" style="display:none;">
	<form class="fancybox-form" data-tables='<?= json_encode(["staff_accounts" => ["_idValue" => $user["StaffID"], "_idField" => "StaffID"]]) ?>'>
		<section class="card card-blue-fill">
			<header class="card-header">Position</header>
			<div class="card-block">
				<div class="form-group">
					<input type="text" class="form-control editable-field" data-table="staff_accounts" data-field="sa_position" value="<?= $user['sa_position'] ?>">
					<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="sa_position" value="<?= $user['sa_position'] ?>">
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container date-only" id="fancybox-user-start_date" style="display:none;">
	<form class="fancybox-form" data-tables='<?= json_encode(["staff_accounts" => ["_idValue" => $user["StaffID"], "_idField" => "StaffID"]]) ?>'>
		<section class="card card-blue-fill">
			<header class="card-header">Start Date</header>
			<div class="card-block">
				<div class="form-group">
					<?= sats_form_input_date([
						'variable' => $user,
						'post_var_name' => 'user',
						'post_field_key' => 'start_date',
						'other_classes' => 'date-field editable-field',
						'other_props' => 'data-table="staff_accounts" data-field="start_date"',
					]) ?>
					<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="start_date" value="<?= $this->customlib->formatYmdToDmy($user['start_date'], true) ?>">
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container different" id="fancybox-user-electrical_licence" style="display:none;">
	<form action="/users/update_electrical_license" method="post" enctype="multipart/form-data" class="fancybox-form different" data-tables='<?= json_encode(["staff_accounts" => ["_idValue" => $user["StaffID"], "_idField" => "StaffID"]]) ?>'>
		<input type="hidden" name="StaffID" value="<?= $user["StaffID"] ?>">
		<section class="card card-blue-fill">
			<header class="card-header">Electrical Licence</header>
			<div class="card-block">
				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label>Electrician</label>
						</div>
						<div class="col-8">
							<select name="user[is_electrician]" class="form-control editable-field" data-table="staff_accounts" data-field="is_electrician" >
								<option value="1"
									<?= set_select('user[is_electrician]', 1, $user['is_electrician'] == 1) ?>
								>Yes</option>
								<option value="0"
									<?= set_select('user[is_electrician]', 0, $user['is_electrician'] == 0) ?>
								>No</option>
							</select>
							<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="is_electrician" value="<?= $user['is_electrician'] ?>">
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label>Licence Image</label>
						</div>
						<div class="col-8 text-center">
							<div class="mx-auto" style="max-width: 320px;">
								<?php
								if (!empty($user['electrical_license'])):
								?>
								<img id="image-user_electrical_license"
									src="/images/electrical_license/<?= $user['electrical_license'] ?>"
									class="w-100 mx-auto" style="max-height: 320px;" />
								<?php
								else:
								?>
								<img id="image-user_electrical_license"
									class="w-100 mx-auto" style="display: none; max-height: 320px;" />
								<?php
								endif;
								?>
								<div class="d-flex mt-2 mb-3 w-100" style="align-items: center; justify-content: center;">
									<button type="button" class="btn btn-danger button-remove_photo d-none" id="button-delete_user_electrical_license">Remove Photo</button>

									<div id="spacer-delete_user_electrical_license" class="d-none" style="flex-grow: 1"></div>

									<button type="button"
										id="button-user_electrical_license"
										class="btn btn-primary button-upload_photo"
										data-target="#file-user_electrical_license"
									>Choose Photo</button>

									<input type="file" accept="image/jpeg, image/png" class="d-none hidden-file" name="user_electrical_license" id="file-user_electrical_license" data-target="#image-user_electrical_license"/>

									<input type="hidden" name="user[electrical_license]" id="hidden-user_electrical_license" value="<?= $user['electrical_license'] ?>" />
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label>Licence Number</label>
						</div>
						<div class="col-8">
							<input type="text" name="user[elec_license_num]" class="form-control editable-field" data-table="staff_accounts" data-field="elec_license_num" value="<?= $user['elec_license_num'] ?>">
							<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="elec_license_num" value="<?= $user['elec_license_num'] ?>">
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label>Licence Expiry</label>
						</div>
						<div class="col-8">
							<?= sats_form_input_date([
								'variable' => $user,
								'post_var_name' => 'user',
								'post_field_key' => 'elec_licence_expiry',
								'other_classes' => 'date-field editable-field',
								'other_props' => 'data-table="staff_accounts" data-field="elec_licence_expiry"',
							]) ?>
							<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="elec_licence_expiry" value="<?= $this->customlib->formatYmdToDmy($user['elec_licence_expiry'], true) ?>">
						</div>
					</div>
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container" id="fancybox-user-emergency_contact" style="display:none;">
	<form class="fancybox-form" data-tables='<?= json_encode(["staff_accounts" => ["_idValue" => $user["StaffID"], "_idField" => "StaffID"]]) ?>'>
		<section class="card card-blue-fill">
			<header class="card-header">Emergency Contact</header>
			<div class="card-block">
				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label class="form-label">Contact Name</label>
						</div>
						<div class="col-8">
							<input type="text" class="form-control editable-field" data-table="staff_accounts" data-field="ice_name" value="<?= $user['ice_name'] ?>">
							<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="ice_name" value="<?= $user['ice_name'] ?>">
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label class="form-label">Phone Number</label>
						</div>
						<div class="col-8">
							<input type="text" class="form-control editable-field" data-table="staff_accounts" data-field="ice_phone" value="<?= $user['ice_phone'] ?>">
							<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="ice_phone" value="<?= $user['ice_phone'] ?>">
						</div>
					</div>
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container" id="fancybox-user-status" style="display:none;">
	<form class="fancybox-form different" data-tables='<?= json_encode(["staff_accounts" => ["_idValue" => $user["StaffID"], "_idField" => "StaffID"]]) ?>'>
		<section class="card card-blue-fill">
			<header class="card-header">Status</header>
			<div class="card-block">
				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-12">
							<select class="form-control editable-field" data-table="staff_accounts" data-field="active" id="user-status" >
								<?php
									$userActive = $user['active'];
								?>
								<option value="1"
									<?= set_select('user[active]', 1, $userActive == 1) ?>
								>Active</option>
								<option value="0"
									<?= set_select('user[active]', 0, $userActive == 0) ?>
								>Inactive</option>
							</select>
							<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="active" value="<?= $user['active'] ?>">
						</div>
					</div>
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container date-only" id="fancybox-user-dob" style="display:none;">
	<form class="fancybox-form" data-tables='<?= json_encode(["staff_accounts" => ["_idValue" => $user["StaffID"], "_idField" => "StaffID"]]) ?>'>
		<section class="card card-blue-fill">
			<header class="card-header">Date of Birth</header>
			<div class="card-block">
				<div class="form-group">
					<?= sats_form_input_date([
						'variable' => $user,
						'post_var_name' => 'user',
						'post_field_key' => 'dob',
						'other_classes' => 'date-field editable-field',
						'other_props' => 'data-table="staff_accounts" data-field="dob"',
					]) ?>
					<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="dob" value="<?= $this->customlib->formatYmdToDmy($user['dob'], true) ?>">
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container" id="fancybox-user-phone" style="display:none;">
	<form class="fancybox-form" data-tables='<?= json_encode(["staff_accounts" => ["_idValue" => $user["StaffID"], "_idField" => "StaffID"]]) ?>'>
		<section class="card card-blue-fill">
			<header class="card-header">Phone</header>
			<div class="card-block">
				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label class="form-label">Contact Number</label>
						</div>
						<div class="col-8">
							<input type="text" class="form-control editable-field" data-table="staff_accounts" data-field="ContactNumber" value="<?= $user['ContactNumber'] ?>">
							<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="ContactNumber" value="<?= $user['ContactNumber'] ?>">
						</div>
					</div>
				</div>

				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label class="form-label">Model No.</label>
						</div>
						<div class="col-8">
							<input type="text" class="form-control editable-field" data-table="staff_accounts" data-field="phone_model_num" value="<?= $user['phone_model_num'] ?>">
							<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="phone_model_num" value="<?= $user['phone_model_num'] ?>">
						</div>
					</div>
				</div>

				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label class="form-label">Serial No.</label>
						</div>
						<div class="col-8">
							<input type="text" class="form-control editable-field" data-table="staff_accounts" data-field="phone_serial_num" value="<?= $user['phone_serial_num'] ?>">
							<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="phone_serial_num" value="<?= $user['phone_serial_num'] ?>">
						</div>
					</div>
				</div>

				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label class="form-label">IMEI</label>
						</div>
						<div class="col-8">
							<input type="text" class="form-control editable-field" data-table="staff_accounts" data-field="phone_imei" value="<?= $user['phone_imei'] ?>">
							<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="phone_imei" value="<?= $user['phone_imei'] ?>">
						</div>
					</div>
				</div>

				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label class="form-label">PIN</label>
						</div>
						<div class="col-8">
							<input type="number" class="form-control editable-field" min="0" max="9999999999" data-table="staff_accounts" data-field="phone_pin" value="<?= $user['phone_pin'] ?>">
							<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="phone_pin" value="<?= $user['phone_pin'] ?>">
						</div>
					</div>
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container" id="fancybox-user-password" style="display:none;">
	<form action="/users/update_password" autocomplete="off" method="post" class="fancybox-form different" data-tables='<?= json_encode(["staff_accounts" => ["_idValue" => $user["StaffID"], "_idField" => "StaffID"]]) ?>'>
		<input type="hidden" name="StaffID" value="<?= $user["StaffID"] ?>">
		<section class="card card-blue-fill">
			<header class="card-header">Password</header>
			<div class="card-block">

				<div class="form-group">
					<div class="form-row">
						<div class="col-4">
							<label class="form-label">Current Password</label>
						</div>
						<div class="col-8">
							<input type="text" name="user[CurrentPassword]" autocomplete="off" id="user-CurrentPassword" class="form-control editable-field" required value="<?= $password ?>">
							<input style="display: none" type="text" name="currentpassword" autocomplete="off">
						</div>
					</div>
				</div>

				<div class="form-group">
					<div class="form-row">
						<div class="col-4">
							<label class="form-label">New Password</label>
						</div>
						<div class="col-8">	
							<input type="text" name="user[NewPassword]" autocomplete="off" id="user-NewPassword" class="form-control editable-field" required value="">
							<input style="display: none" type="text" name="newpassword" autocomplete="off">
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="form-row">
						<div class="col-4">
							<label class="form-label">Confirm Password</label>
						</div>
						<div class="col-8">
							<input type="text" id="user-ConfirmPassword" class="form-control editable-field" required value="" autocomplete="off">
							<input style="display: none" type="text" name="confirmpassword" autocomplete="off">
						</div>
					</div>
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container" id="fancybox-user-tablet" style="display:none;">
	<form class="fancybox-form" data-tables='<?= json_encode(["staff_accounts" => ["_idValue" => $user["StaffID"], "_idField" => "StaffID"]]) ?>'>
		<section class="card card-blue-fill">
			<header class="card-header">Tablet</header>
			<div class="card-block">
				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label class="form-label">Model No.</label>
						</div>
						<div class="col-8">
							<input type="text" class="form-control editable-field" data-table="staff_accounts" data-field="ipad_model_num" value="<?= $user['ipad_model_num'] ?>">
							<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="ipad_model_num" value="<?= $user['ipad_model_num'] ?>">
						</div>
					</div>
				</div>

				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label class="form-label">Serial No.</label>
						</div>
						<div class="col-8">
							<input type="text" class="form-control editable-field" data-table="staff_accounts" data-field="ipad_serial_num" value="<?= $user['ipad_serial_num'] ?>">
							<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="ipad_serial_num" value="<?= $user['ipad_serial_num'] ?>">
						</div>
					</div>
				</div>

				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label class="form-label">IMEI</label>
						</div>
						<div class="col-8">
							<input type="text" class="form-control editable-field" data-table="staff_accounts" data-field="ipad_imei" value="<?= $user['ipad_imei'] ?>">
							<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="ipad_imei" value="<?= $user['ipad_imei'] ?>">
						</div>
					</div>
				</div>

				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label class="form-label">Pre Paid Service No.</label>
						</div>
						<div class="col-8">
							<input type="text" class="form-control editable-field" data-table="staff_accounts" data-field="ipad_prepaid_serv_num" value="<?= $user['ipad_prepaid_serv_num'] ?>">
							<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="ipad_prepaid_serv_num" value="<?= $user['ipad_prepaid_serv_num'] ?>">
						</div>
					</div>
				</div>

				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label class="form-label">Data Expiry Date</label>
						</div>
						<div class="col-8">
							<?= sats_form_input_date([
								'variable' => $user,
								'post_var_name' => 'user',
								'post_field_key' => 'ipad_expiry_date',
								'other_classes' => 'date-field editable-field',
								'other_props' => 'data-table="staff_accounts" data-field="ipad_expiry_date"',
							]) ?>
							<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="ipad_expiry_date" value="<?= $this->customlib->formatYmdToDmy($user['ipad_expiry_date'], true) ?>">
						</div>
					</div>
				</div>

				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label class="form-label">PIN</label>
						</div>
						<div class="col-8">
							<input type="number" class="form-control editable-field" min="0" max="9999999999" data-table="staff_accounts" data-field="tablet_pin" value="<?= $user['tablet_pin'] ?>">
							<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="tablet_pin" value="<?= $user['tablet_pin'] ?>">
						</div>
					</div>
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container" id="fancybox-user-personal_contact_number" style="display:none;">
	<form class="fancybox-form" data-tables='<?= json_encode(["staff_accounts" => ["_idValue" => $user["StaffID"], "_idField" => "StaffID"]]) ?>'>
		<section class="card card-blue-fill">
			<header class="card-header">Personal Contact Number</header>
			<div class="card-block">
				<div class="form-group">
					<input type="text" class="form-control editable-field" data-table="staff_accounts" data-field="personal_contact_number" value="<?= $user['personal_contact_number'] ?>">
					<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="personal_contact_number" value="<?= $user['personal_contact_number'] ?>">
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>
<div class="fancybox-form-container different" id="fancybox-user-vaccination" style="display:none;">
	<section class="card card-blue-fill">
		<header class="card-header">COVID-19 Vaccination</header>
		<div class="card-block">
			<div class="table-responsive">
				<table class="table table-striped table-bordered" id="table-vaccinations">
					<thead>
						<tr>
							<th>Vaccine Brand</th>
							<th>Completed On</th>
							<th>Valid Until</th>
							<th>Image</th>
							<th>&nbsp;</th>
							<th>&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($vaccinations as $vaccination): ?>
						<tr data-vaccination="<?= htmlspecialchars(json_encode($vaccination)) ?>">
							<td><?= $vaccineBrands[$vaccination["vaccine_brand"]] ?></td>
							<td><?= $this->customlib->formatYmdToDmy($vaccination["completed_on"], true) ?></td>
							<td><?= $this->customlib->formatYmdToDmy($vaccination["valid_till"], true) ?></td>
							<td>
								<?php if (!is_null($vaccination["certificate_image"]) && $vaccination["certificate_image"] != ""): ?>
									<a data-fancybox class="fancybox" data-src="/uploads/vaccination_certificates/<?= $vaccination["certificate_image"] ?>" href="javascript:;">Click Here</a>
								<?php else: ?>
									No Image
								<?php endif; ?>
							</td>
							<td>
								<button type="button" class="btn btn-success button-edit_vaccination">Edit</button>
							</td>
							<td>
								<button type="button" class="btn btn-danger button-delete_vaccination">Delete</button>
							</td>
						</tr>
						<?php endforeach; ?>

						<?php if (empty($vaccinations)): ?>
						<tr>
							<td colspan="100%" class="text-center">
								--- No Vaccination Details ---
							</td>
						</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

			<button class="btn btn-primary mt-2" id="button-add_vaccination"
				data-auto-focus="false" data-fancybox data-src="#fancybox-vaccination-add"
			>Add</button>
		</div>
	</section>
</div>

<div class="fancybox" id="fancybox-vaccination-add" style="display: none;">
	<form class="fancybox-form different" id="form-add_vaccination" action="/users/add_vaccination" method="post" enctype="multipart/form-data">
		<input type="hidden" name="vaccination[StaffID]" id="hidden-add_vaccination-StaffID" value="<?= $user["StaffID"] ?>" />
		<section class="card card-blue-fill">
			<header class="card-header">Add Vaccination</header>
			<div class="card-block">
				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label for="select-add_vaccination-vaccine_brand">Vaccine Brand</label>
						</div>
						<div class="col-8">
							<select name="vaccination[vaccine_brand]" id="select-add_vaccination-vaccine_brand" class="form-control">
								<?php foreach ($vaccineBrands as $vaccineBrandId => $vaccineBrand): ?>
								<option value="<?= $vaccineBrandId ?>"><?= $vaccineBrand ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				</div>

				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label for="date-add_vaccination-completed_on">Completed On</label>
						</div>
						<div class="col-8">
							<input type="text" name="vaccination[completed_on]" id="date-add_vaccination-completed_on"
								class="form-control flatpickr flatpickr-input" data-allow-input="true" style="width: 125px;"
								autocomplete="off" />
						</div>
					</div>
				</div>

				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label for="date-add_vaccination-valid_till">Valid Till</label>
						</div>
						<div class="col-8">
							<input type="text" name="vaccination[valid_till]" id="date-add_vaccination-valid_till"
								class="form-control flatpickr flatpickr-input" data-allow-input="true" style="width: 125px;"
								autocomplete="off" />
						</div>
					</div>
				</div>

				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label for="file-add_vaccination-certificate_image">Certificate Image</label>
						</div>
						<div class="col-8">
							<div style="max-width: 320px">
								<img id="image-add_vaccination-vaccination_certificate_image"
									class="w-100 mx-auto" style="display: none; max-height: 320px;" />

								<div class="d-flex mt-2 mb-3 w-100" style="align-items: center; justify-content: center;">
									<button type="button" class="btn btn-danger button-remove_photo d-none" id="button-add_vaccination-delete_certificate_image">Remove Photo</button>

									<div id="spacer-add_vaccination-delete_certificate_image" class="d-none" style="flex-grow: 1"></div>

									<button type="button"
										id="button-add_vaccination-vaccination_certificate_image"
										class="btn btn-primary button-upload_photo"
										data-target="#file-add_vaccination-certificate_image"
									>Choose Image</button>

									<input type="file" class="d-none hidden-file" accept="image/jpeg, image/png" name="vaccination_certificate_image"
										id="file-add_vaccination-certificate_image" data-target="#image-add_vaccination-vaccination_certificate_image" />
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Save</button></div>
	</form>
</div>

<div class="fancybox" id="fancybox-vaccination-edit" style="display: none;">
	<form class="fancybox-form different" id="form-edit_vaccination" action="/users/edit_vaccination" method="post" enctype="multipart/form-data">
		<input type="hidden" name="vaccination_id" id="hidden-edit_vaccination-vaccination_id" value="" />
		<section class="card card-blue-fill">
			<header class="card-header">Edit Vaccination</header>
			<div class="card-block">
				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label for="select-edit_vaccination-vaccine_brand">Vaccine Brand</label>
						</div>
						<div class="col-8">
							<select name="vaccination[vaccine_brand]" id="select-edit_vaccination-vaccine_brand" class="form-control">
								<?php foreach ($vaccineBrands as $vaccineBrandId => $vaccineBrand): ?>
								<option value="<?= $vaccineBrandId ?>"><?= $vaccineBrand ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				</div>

				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label for="date-edit_vaccination-completed_on">Completed On</label>
						</div>
						<div class="col-8">
							<input type="text" name="vaccination[completed_on]" id="date-edit_vaccination-completed_on"
								class="form-control flatpickr flatpickr-input" data-allow-input="true" style="width: 125px;"
								autocomplete="off" />
						</div>
					</div>
				</div>

				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label for="date-edit_vaccination-valid_till">Valid Till</label>
						</div>
						<div class="col-8">
							<input type="text" name="vaccination[valid_till]" id="date-edit_vaccination-valid_till"
								class="form-control flatpickr flatpickr-input" data-allow-input="true" style="width: 125px;"
								autocomplete="off" />
						</div>
					</div>
				</div>

				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label for="file-edit_vaccination-certificate_image">Certificate Image</label>
						</div>
						<div class="col-8">
							<div style="max-width: 320px">
								<img id="image-edit_vaccination-vaccination_certificate_image"
									class="w-100 mx-auto" style="display: none; max-height: 320px;" />

								<div class="d-flex mt-2 mb-3 w-100" style="align-items: center; justify-content: center;">
									<button type="button" class="btn btn-danger button-remove_photo d-none" id="button-edit_vaccination-delete_certificate_image">Remove Photo</button>

									<div id="spacer-edit_vaccination-delete_certificate_image" class="d-none" style="flex-grow: 1"></div>

									<button type="button"
										id="button-edit_vaccination-vaccination_certificate_image"
										class="btn btn-primary button-upload_photo"
										data-target="#file-edit_vaccination-certificate_image"
									>Choose Image</button>

									<input type="file" class="d-none hidden-file" accept="image/jpeg, image/png" name="vaccination_certificate_image"
										id="file-edit_vaccination-certificate_image" data-target="#image-edit_vaccination-vaccination_certificate_image" />
									<input type="hidden" name="vaccination[certificate_image]" id="hidden-edit_vaccination-certificate_image" value="" />
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container different" id="fancybox-user-working_days" style="display:none;">
	<form class="fancybox-form different" action="/users/update_workdays" method="post">
		<input type="hidden" name="StaffID" value="<?= $user["StaffID"] ?>">
		<input type="hidden" name="ClassID" value="<?= $user["ClassID"] ?>">
		<section class="card card-blue-fill">
			<header class="card-header">Working Days <?php if ($user["ClassID"] == 6) { echo "& Hours"; }?></header>
			<div class="card-block">
				<div class="form-group">

					<span class="checkbox d-inline mr-3">
						<input type="checkbox" name="working_days_select_all" id="working_days_select_all" />
						<label class="form-control-static chk_lbl_txt d-inline" for="working_days_select_all">Select ALL</label>
					</span>

					<?php
					$dayNames = [
						'Mon' => 'Monday',
						'Tue' => 'Tuesday',
						'Wed' => 'Wednesday',
						'Thu' => 'Thursday',
						'Fri' => 'Friday',
						'Sat' => 'Saturday',
						'Sun' => 'Sunday',
					];
					?>
					<?php foreach($workingDays as $abbr => $selected): ?>
						<span class="checkbox d-inline mr-2">
							<?php
							$checked = set_checkbox("working_days[{$abbr}]", 1, $selected == 1);
							?>
							<input
								type="hidden" name="working_days[<?= $abbr ?>]" value="0"
							/>
							<input
								type="checkbox" name="working_days[<?= $abbr ?>]" id="working_days-<?= $abbr ?>"
								value="1"
								<?= $checked ?>
								class="working_days-checkbox"
							/>
							<label for="working_days-<?= $abbr ?>"><?= $dayNames[$abbr] ?></label>
						</span>
					<?php endforeach; ?>
					<?php if ($user["ClassID"] == 6) { ?>
					<br><br>
					<label>Working Hours:</label>
					<?php
						$this->db->select('working_hours');
						$this->db->from('tech_working_hours');
						$this->db->where('staff_id', $user["StaffID"]);
						$query = $this->db->get();
						$result = $query->row();
					?>
					<input type="hidden" class="form-control" value="<?= ($result->working_hours != '')? '1':'0'; ?>" name="has_already_working_hours" id="has_already_working_hours"/>
					<input type="text" class="form-control" value="<?=$result->working_hours;?>" name="working_hours" id="working_hours"/>
					<?php } ?>
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container different" id="fancybox-user-device_accounts" style="display:none;">
	<form class="fancybox-form different" action="/users/update_device_accounts" method="post">
		<input type="hidden" name="StaffID" value="<?= $user["StaffID"] ?>">
		<section class="card card-blue-fill">
			<header class="card-header">Device Accounts</header>
			<div class="card-block">
				<div class="form-row pt-2">
					<div class="col-12">
						<table class="table table-bordered table-stripe mb-2" id="table-device_accounts">
							<thead>
								<tr>
									<th>Account Type</th>
									<th>Email/Username</th>
									<th>Password</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								<tr class="no-entries">
									<td colspan="100%" class="text-center">
										-- No Accounts --
									</td>
								</tr>
							</tbody>
						</table>

						<div class="text-right mb-2">
							<button type="button" class="btn btn-primary" id="button-add_account">
								<i class="fa fa-plus-circle"></i> Account
							</button>
						</div>
					</div>
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container" id="fancybox-user-plant_id" style="display:none;">
	<form class="fancybox-form" data-tables='<?= json_encode(["staff_accounts" => ["_idValue" => $user["StaffID"], "_idField" => "StaffID"]]) ?>'>
		<section class="card card-blue-fill">
			<header class="card-header">Plant ID</header>
			<div class="card-block">
				<div class="form-group">
					<input type="text" class="form-control editable-field" data-table="staff_accounts" data-field="other_plant_id" value="<?= $user['other_plant_id'] ?>">
					<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="other_plant_id" value="<?= $user['other_plant_id'] ?>">
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container" id="fancybox-user-shirt_size" style="display:none;">
	<form class="fancybox-form" data-tables='<?= json_encode(["staff_accounts" => ["_idValue" => $user["StaffID"], "_idField" => "StaffID"]]) ?>'>
		<section class="card card-blue-fill">
			<header class="card-header">Shirt Size</header>
			<div class="card-block">
				<div class="form-group">
					<input type="text" class="form-control editable-field" data-table="staff_accounts" data-field="other_shirt_size" value="<?= $user['other_shirt_size'] ?>">
					<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="other_shirt_size" value="<?= $user['other_shirt_size'] ?>">
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container" id="fancybox-user-key_number" style="display:none;">
	<form class="fancybox-form" data-tables='<?= json_encode(["staff_accounts" => ["_idValue" => $user["StaffID"], "_idField" => "StaffID"]]) ?>'>
		<section class="card card-blue-fill">
			<header class="card-header">Key Number</header>
			<div class="card-block">
				<div class="form-group">
					<input type="text" class="form-control editable-field" data-table="staff_accounts" data-field="other_key_num" value="<?= $user['other_key_num'] ?>">
					<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="other_key_num" value="<?= $user['other_key_num'] ?>">
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container" id="fancybox-user-weekly_sales_report" style="display:none;">
	<form class="fancybox-form" data-tables='<?= json_encode(["staff_accounts" => ["_idValue" => $user["StaffID"], "_idField" => "StaffID"]]) ?>'>
		<section class="card card-blue-fill">
			<header class="card-header">Weekly Sales Report</header>
			<div class="card-block">
				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label>Display on Weekly Sales Report</label>
						</div>
						<div class="col-8">
							<select class="form-control editable-field" data-table="staff_accounts" data-field="display_on_wsr" >
								<option value="1"
									<?= set_select('user[display_on_wsr]', 1, $user['display_on_wsr'] == 1) ?>
								>Yes</option>
								<option value="0"
									<?= set_select('user[display_on_wsr]', 0, $user['display_on_wsr'] == 0) ?>
								>No</option>
							</select>
							<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="active" value="<?= $user['active'] ?>">
						</div>
					</div>
					<div class="form-row align-items-center">
						<div class="col-4">
							<label>Receive Weekly Sales Report</label>
						</div>
						<div class="col-8">
							<select class="form-control editable-field" data-table="staff_accounts" data-field="recieve_wsr" >
								<option value="1"
									<?= set_select('user[recieve_wsr]', 1, $user['recieve_wsr'] == 1) ?>
								>Yes</option>
								<option value="0"
									<?= set_select('user[recieve_wsr]', 0, $user['recieve_wsr'] == 0) ?>
								>No</option>
							</select>
							<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="active" value="<?= $user['active'] ?>">
						</div>
					</div>
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container" id="fancybox-user-laptop" style="display:none;">
	<form class="fancybox-form" data-tables='<?= json_encode(["staff_accounts" => ["_idValue" => $user["StaffID"], "_idField" => "StaffID"]]) ?>'>
		<section class="card card-blue-fill">
			<header class="card-header">Laptop</header>
			<div class="card-block">
				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label class="form-label">Device Make</label>
						</div>
						<div class="col-8">
							<input type="text" class="form-control editable-field" data-table="staff_accounts" data-field="laptop_make" value="<?= $user['laptop_make'] ?>">
							<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="laptop_make" value="<?= $user['laptop_make'] ?>">
						</div>
					</div>
				</div>

				<div class="form-group">
					<div class="form-row align-items-center">
						<div class="col-4">
							<label class="form-label">Serial No.</label>
						</div>
						<div class="col-8">
							<input type="text" class="form-control editable-field" data-table="staff_accounts" data-field="laptop_serial_num" value="<?= $user['laptop_serial_num'] ?>">
							<input type="hidden" class="form-control original-field" data-table="staff_accounts" data-field="laptop_serial_num" value="<?= $user['laptop_serial_num'] ?>">
						</div>
					</div>
				</div>

			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<!-- TEMPLATES -->

<template id="template-vaccination_row">
<tr>
	<td class="column-vaccine_brand"></td>
	<td class="column-completed_on"></td>
	<td class="column-valid_till"></td>
	<td class="column-certificate_image">
	</td>
	<td>
		<button type="button" class="btn btn-success button-edit_vaccination">Edit</button>
	</td>
	<td>
		<button type="button" class="btn btn-danger button-delete_vaccination">Delete</button>
	</td>
</tr>
</template>

<!-- ABOUT PAGE -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?= $title ?></h4>
	<p>
		This page stores all information relating to users
	</p>

</div>
<div id="update_password_about_fb" class="fancybox" style="display:none;" >

	<h4>Password Pattern</h4>
	<p>
		First Name, Last Initial, Current Year
		<br/>
		<br/>
		<span class="ml-4">eg. <b>DanielK2021</b></span>
	</p>

</div>

<!-- Fancybox END -->

<style>
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
</style>

<script>

var deviceAccounts = <?= json_encode($deviceAccounts); ?>;
var deviceAccountIndex = 0;


function renderDeviceAccount(deviceAccount, index) {
	var tr = $("<tr>");
	var accountTypeTd = $("<td>");
	if (deviceAccount.id) {
		accountTypeTd.append($("<input>").prop("type", "hidden").prop("name", `device_accounts[${index}][id]`).val(deviceAccount.id));
	}

	accountTypeTd.append($("<input>").prop("type", "hidden").prop("name", `device_accounts[${index}][staff_id]`).val(<?= $user['StaffID'] ?>));

	if (deviceAccount.account_type) {
		accountTypeTd.append($(`<span>${deviceAccount.account_type}</span>`));
		$("<input>").prop("type", "hidden")
			.prop("name", `device_accounts[${index}][account_type]`)
			.val(deviceAccount.account_type).appendTo(accountTypeTd);
	}
	else {
		var accountTypesDropdown = $("<select>")
			.prop("name", `device_accounts[${index}][account_type]`)
			.addClass("form-control");
		accountTypesDropdown.append($("<option>").val("Apple ID").html("Apple ID"));
		accountTypesDropdown.append($("<option>").val("Google Play").html("Google Play"));
		accountTypesDropdown.append($("<option>").val("Zoom").html("Zoom"));
		accountTypesDropdown.append($("<option>").val("DiviPay").html("DiviPay"));
		accountTypesDropdown.append($("<option>").val("CRM").html("CRM"));
		accountTypesDropdown.append($("<option>").val("Email").html("Email"));
		accountTypeTd.append(accountTypesDropdown);
	}


	tr.append(accountTypeTd);

	var accountIdentifierTd = $("<td>");
	var accountIdentifierInput = $("<input>")
		.prop("type", "text")
		.prop("name", `device_accounts[${index}][account_identifier]`)
		.prop("required", "required")
		.addValidation("NOTEMPTY")
		.addClass("form-control");
	if (deviceAccount.account_identifier) {
		accountIdentifierInput.val(deviceAccount.account_identifier);

		$("<input>").prop("type", "hidden")
			.prop("name", `device_accounts[${index}][og_account_identifier]`)
			.val(deviceAccount.account_identifier).appendTo(accountIdentifierTd);
	}
	var accountIdentifierDiv = $("<div>")
		.addClass("form-group mb-0")
		.append(accountIdentifierInput);
	accountIdentifierTd.append(accountIdentifierDiv);

	tr.append(accountIdentifierTd);

	var accountPasswordTd = $("<td>");
	var accountPasswordInput = $("<input>")
		.prop("type", "text")
		.prop("name", `device_accounts[${index}][account_password]`)
		.prop("required", "required")
		.addValidation("NOTEMPTY")
		.addClass("form-control");
	if (deviceAccount.account_password) {
		accountPasswordInput.val(deviceAccount.account_password);

		$("<input>").prop("type", "hidden")
			.prop("name", `device_accounts[${index}][og_account_password]`)
			.val(deviceAccount.account_password).appendTo(accountPasswordTd);
	}
	var accountPasswordDiv = $("<div>")
		.addClass("form-group mb-0")
		.append(accountPasswordInput);
	accountPasswordTd.append(accountPasswordDiv);

	tr.append(accountPasswordTd);

	var actionsTd = $("<td>").addClass("text-center");

	var deleter = $("<a>").prop("href", "#").addClass("text-danger").append($("<i>").addClass("fa fa-trash"));
	deleter.on('click', (evt) => {
		evt.preventDefault();
		accountIdentifierInput.removeValidation("NOTEMPTY");
		accountPasswordInput.removeValidation("NOTEMPTY");

		if (deviceAccount.id) {
			accountTypeTd.append($("<input>").prop("type", "hidden").prop("name", `device_accounts[${index}][will_delete]`).val(1));
			tr.hide();
		}
		else {
			tr.remove();
		}

		return false;
	});

	actionsTd.append(deleter);

	tr.append(actionsTd);

	return tr;
}

$('document').ready((event) => {

	<?php if (!empty($user['electrical_license'])): ?>
	$("#button-delete_user_electrical_license").removeClass("d-none");
	$("#spacer-delete_user_electrical_license").removeClass("d-none");
	<?php endif; ?>

	<?php if (!empty($user['driver_license'])): ?>
	$("#button-delete_user_driver_license").removeClass("d-none");
	$("#spacer-delete_user_driver_license").removeClass("d-none");
	<?php endif; ?>

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
				}
				else {
					value = m.format("YYYY-MM-DD");
				}

			}

			requestData[table].fields[field] = value;
		});

		return requestData;
	}

	const submitFancybox = (evt) => {

		$('#load-screen').show();

		evt.preventDefault();

		var form = $(evt.target);

		const requestData = compileData(form);

		jQuery.ajax({
			type: "POST",
			url: "/users/ajax_update_fields",
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
				}
				else {
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

	$("#fancybox-user-status .fancybox-form").on("submit", (evt) => {
		swal({
			title: "Warning!",
			text: "This will change the user's status. Do you want to continue?",
			type: "warning",
			showConfirmButton: true,
			showCancelButton: true,
			confirmButtonText: 'Yes',
			cancelButtonText: 'No',
			confirmButtonClass: "btn-success",
			cancelButtonClass: 'btn-danger',
		}, () => {
			submitFancybox(evt);
		});

		evt.preventDefault();
		evt.stopPropagation();
		return false;
	});

	$(".fancybox-form").not(".different").on("submit", submitFancybox);

	$("#fancybox-user-location_access").on("change", "input[type=checkbox]", (evt) => {
		shouldRefresh = true;
	});

	$("#user-address").on("input", (evt) => {
		shouldRefresh = true;
	});
	$("#user-status").on("change", (evt) => {
		shouldRefresh = true;
	});

	$("#fancybox-user-electrical_licence").on("change", "input, select", (evt) => {
		shouldRefresh = true;
	});

	$("#fancybox-user-password .fancybox-form").on("submit", (evt) => {
		var newPassword = $("#user-NewPassword").val();
		var confirmPassword = $("#user-ConfirmPassword").val();
		var hasError = false;

		if (newPassword.length != 0 && newPassword.length < 4) {
			swal({
				title: "Error!",
				text: "New Password field should be at least 4 characters.",
				type: "error",
				confirmButtonClass: "btn-danger"
			});
			hasError = true;
		}
		else if (newPassword != confirmPassword) {
			swal({
				title: "Error!",
				text: "Confirm Password field does not match New Password field.",
				type: "error",
				confirmButtonClass: "btn-danger"
			});
			hasError = true;
		}

		if (hasError) {
			evt.preventDefault();
			return false;
		}
		return true;
	});

	var vaccineBrands = {
		1: "AstraZeneca",
		2: "Pfizer",
		3: "Moderna",
		4: "Janssen",
	};

	$("#form-add_vaccination").on("submit", (evt) => {
		shouldRefresh = true;

		evt.preventDefault();

		var formData = new FormData(evt.target);

		$("#load-screen").show();

		$.ajax({
			url: "/users/add_vaccination",
			type: "POST",
			data: formData,
			cache: false,
			dataType: "json",
			contentType: false,
			processData: false,
			success: (data) => {
				if (data.success) {
					swal({
						title: "Success!",
						text: "Vaccination data has been saved.",
						type: "success",
						confirmButtonClass: "btn-success",
					});

					var vaccination = data.vaccination;

					var template = $($("#template-vaccination_row").html());
					template.data("vaccination", vaccination);

					template.find(".column-vaccine_brand").html(vaccineBrands[vaccination.vaccine_brand]);

					template.find(".column-completed_on").html((new Date(Date.parse(vaccination.completed_on))).toLocaleDateString("en-AU"));
					if (vaccination.valid_till && vaccination.valid_till != "") {
						template.find(".column-valid_till").html((new Date(Date.parse(vaccination.valid_till))).toLocaleDateString("en-AU"));
					}

					if (vaccination.certificate_image && vaccination.certificate_image != "") {
						template.find(".column-certificate_image").html(
							$(`<a data-fancybox class="fancybox" data-src="/uploads/vaccination_certificates/${vaccination.certificate_image}" href="javascript:;">Click Here</a>`)
						);
					}
					else {
						template.find(".column-certificate_image").html("No Image");
					}

					$("#table-vaccinations tbody").append(template);

					$.fancybox.close();
				}
				else {
					swal({
						title: "Error!",
						text: "Vaccination data not saved. Something went wrong.",
						type: "error",
						confirmButtonClass: "btn-danger",
					});
				}
			},
			error: (e, r) => {
				console.log(e, r);
				swal({
					title: "Error!",
					text: "Something went wrong.",
					type: "error",
					confirmButtonClass: "btn-danger",
				});
			},
		}).always(() => {
			$("#load-screen").hide();
		});

		return false;
	});

	var targetRow = null;

	$("#table-vaccinations").on("click", ".button-edit_vaccination", (evt) => {
		var $editButton = $(evt.target);
		var $row = $editButton.closest("tr");
		var vaccination = $row.data("vaccination");

		$("#hidden-edit_vaccination-vaccination_id").val(vaccination.vaccination_id);
		$("#select-edit_vaccination-vaccine_brand").val(vaccination.vaccine_brand);

		var parsedCompletedOn = new Date(Date.parse(vaccination.completed_on));
		var formattedCompletedOn = parsedCompletedOn.toLocaleDateString("en-AU");

		$("#date-edit_vaccination-completed_on").val(formattedCompletedOn);

		var formattedValidTill = "";
		console.log("vt", vaccination.valid_till);
		if (vaccination.valid_till && vaccination.valid_till != "") {
			var parsedValidTill = new Date(Date.parse(vaccination.valid_till));
			formattedValidTill = parsedValidTill.toLocaleDateString("en-AU");
		}

		$("#date-edit_vaccination-valid_till").val(formattedValidTill);

		if (vaccination.certificate_image && vaccination.certificate_image != "") {
			$("#button-edit_vaccination-delete_certificate_image").removeClass("d-none");
			$("#spacer-edit_vaccination-delete_certificate_image").removeClass("d-none");
			$("#image-edit_vaccination-vaccination_certificate_image").prop("src", `/uploads/vaccination_certificates/${vaccination.certificate_image}`);
			$("#image-edit_vaccination-vaccination_certificate_image").show();
		}
		else {
			$("#button-edit_vaccination-delete_certificate_image").addClass("d-none");
			$("#spacer-edit_vaccination-delete_certificate_image").addClass("d-none");
			$("#image-edit_vaccination-vaccination_certificate_image").hide();
		}

		$.fancybox.open($("#fancybox-vaccination-edit"));

		targetRow = $row;
	});

	$("#form-edit_vaccination").on("submit", (evt) => {
		shouldRefresh = true;

		evt.preventDefault();

		var formData = new FormData(evt.target);

		$("#load-screen").show();

		$.ajax({
			url: "/users/edit_vaccination",
			type: "POST",
			data: formData,
			cache: false,
			dataType: "json",
			contentType: false,
			processData: false,
			success: (data) => {
				console.log("response", data);
				if (data.success) {
					swal({
						title: "Success!",
						text: "Vaccination data has been saved.",
						type: "success",
						confirmButtonClass: "btn-success",
					});
					var vaccination = data.vaccination;

					var template = $($("#template-vaccination_row").html());
					template.data("vaccination", vaccination);

					template.find(".column-vaccine_brand").html(vaccineBrands[vaccination.vaccine_brand]);

					template.find(".column-completed_on").html((new Date(Date.parse(vaccination.completed_on))).toLocaleDateString("en-AU"));
					if (vaccination.valid_till && vaccination.valid_till != "") {
						template.find(".column-valid_till").html((new Date(Date.parse(vaccination.valid_till))).toLocaleDateString("en-AU"));
					}

					if (vaccination.certificate_image && vaccination.certificate_image != "") {
						template.find(".column-certificate_image").html(
							$(`<a data-fancybox class="fancybox" data-src="/uploads/vaccination_certificates/${vaccination.certificate_image}" href="javascript:;">Click Here</a>`)
						);
					}
					else {
						template.find(".column-certificate_image").html("No Image");
					}

					targetRow.replaceWith(template);

					$.fancybox.close();
				}
				else {
					swal({
						title: "Error!",
						text: "Vaccination data not saved. Something went wrong.",
						type: "error",
						confirmButtonClass: "btn-danger",
					});
				}
			},
			error: (e, r) => {
				swal({
					title: "Error!",
					text: "Something went wrong.",
					type: "error",
					confirmButtonClass: "btn-danger",
				});
			},
		}).always(() => {
			$("#load-screen").hide();
		});

		return false;
	});

	$("#table-vaccinations").on("click", ".button-delete_vaccination", (evt) => {

		swal({
			title: "Warning!",
			text: "This will delete the vaccination data. Do you want to continue?",
			type: "warning",
			showConfirmButton: true,
			showCancelButton: true,
			confirmButtonText: 'Yes',
			cancelButtonText: 'No',
			confirmButtonClass: "btn-danger",
			cancelButtonClass: 'btn-primary',
		}, () => {
			shouldRefresh = true;

			var $deleteButton = $(evt.target);
			var $row = $deleteButton.closest("tr");
			var vaccination = $row.data("vaccination");

			$("#load-screen").show();

			$.ajax({
				url: "/users/delete_vaccination/" + vaccination.vaccination_id,
				type: "DELETE",
				dataType: "json",
				success: (data) => {
					if (data.success) {
						swal({
							title: "Success!",
							text: "Vaccination data has been deleted.",
							type: "success",
							confirmButtonClass: "btn-success",
						});

						$row.remove();
					}
					else {
						swal({
							title: "Error!",
							text: "Vaccination data not deleted. Something went wrong.",
							type: "error",
							confirmButtonClass: "btn-danger",
						});
					}
				},
				error: (e, r) => {
					console.log(e, r);
					swal({
						title: "Error!",
						text: "Something went wrong.",
						type: "error",
						confirmButtonClass: "btn-danger",
					});
				},
			}).always(() => {
				$("#load-screen").hide();
			});
		});

	});

	$("#button-delete_user_profile_pic").on("click", (evt) => {
		swal({
			title: "Warning!",
			text: "This will remove the user's profile picture, do you want to continue??",
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
					url: "/users/remove_profile_pic/<?= $user["StaffID"] ?>",
					dataType: "json",
					success: (response) => {
						if (response.success) {
							swal({
								title: "Success!",
								text: "Profile picture deleted.",
								type: "success",
								confirmButtonClass: "btn-success",
							}, () => {
								window.location.reload();
							});
						}
						else {
							swal({
								title: "Error!",
								text: "Profile picture not deleted. Something went wrong.",
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

	$("#button-delete_user_electrical_license").on("click", (evt) => {
		swal({
			title: "Warning!",
			text: "This will remove the user's electrical license image, do you want to continue??",
			type: "warning",
			showCloseButton: false,
			showCancelButton: true,
			confirmButtonClass: "btn-danger",
			confirmButtonText: "Remove",
			cancelButtonClass: "btn-primary",
			cancelButtonText: "No",
		}, function(response) {
			if (response) {
				$("#button-delete_user_electrical_license").addClass("d-none");
				$("#spacer-delete_user_electrical_license").addClass("d-none");
				$("#hidden-user_electrical_license").val("");
				$("#file-user_electrical_license").val(null);
				$("#image-user_electrical_license").hide();
			}
		});
	});

	$("#button-delete_user_driver_license").on("click", (evt) => {
		swal({
			title: "Warning!",
			text: "This will remove the user's driver license image, do you want to continue??",
			type: "warning",
			showCloseButton: false,
			showCancelButton: true,
			confirmButtonClass: "btn-danger",
			confirmButtonText: "Remove",
			cancelButtonClass: "btn-primary",
			cancelButtonText: "No",
		}, function(response) {
			if (response) {
				$("#button-delete_user_driver_license").addClass("d-none");
				$("#spacer-delete_user_driver_license").addClass("d-none");
				$("#hidden-user_driver_license").val("");
				$("#file-user_driver_license").val(null);
				$("#image-user_driver_license").hide();
			}
		});
	});

	$("#file-user_electrical_license").on("change", (evt) => {
		$("#button-delete_user_electrical_license").removeClass("d-none");
		$("#spacer-delete_user_electrical_license").removeClass("d-none");
	});

	$("#file-add_vaccination-certificate_image").on("change", (evt) => {
		$("#button-add_vaccination-delete_certificate_image").removeClass("d-none");
		$("#spacer-add_vaccination-delete_certificate_image").removeClass("d-none");
	});

	$("#button-add_vaccination-delete_certificate_image").on("click", () => {
		swal({
			title: "Warning!",
			text: "This will remove the certificate image, do you want to continue??",
			type: "warning",
			showCloseButton: false,
			showCancelButton: true,
			confirmButtonClass: "btn-danger",
			confirmButtonText: "Remove",
			cancelButtonClass: "btn-primary",
			cancelButtonText: "No",
		}, function(response) {
			if (response) {
				$("#button-add_vaccination-delete_certificate_image").addClass("d-none");
				$("#spacer-add_vaccination-delete_certificate_image").addClass("d-none");
				$("#file-add_vaccination-certificate_image").val(null);
				$("#image-add_vaccination-vaccination_certificate_image").hide();
			}
		});
	});

	$("#file-edit_vaccination-certificate_image").on("change", (evt) => {
		$("#button-edit_vaccination-delete_certificate_image").removeClass("d-none");
		$("#spacer-edit_vaccination-delete_certificate_image").removeClass("d-none");
	});

	$("#button-edit_vaccination-delete_certificate_image").on("click", () => {
		swal({
			title: "Warning!",
			text: "This will remove the certificate image, do you want to continue??",
			type: "warning",
			showCloseButton: false,
			showCancelButton: true,
			confirmButtonClass: "btn-danger",
			confirmButtonText: "Remove",
			cancelButtonClass: "btn-primary",
			cancelButtonText: "No",
		}, function(response) {
			if (response) {
				$("#button-edit_vaccination-delete_certificate_image").addClass("d-none");
				$("#spacer-edit_vaccination-delete_certificate_image").addClass("d-none");
				$("#hidden-edit_vaccination-certificate_image").val("");
				$("#file-edit_vaccination-certificate_image").val(null);
				$("#image-edit_vaccination-vaccination_certificate_image").hide();
			}
		});
	});

	var changesMade = false;

	$("#form-user input").on('keydown', function(evt) {
		if (evt.which == 13) {
			evt.preventDefault();
			return false;
		}
	});

	$("#form-user").on('change', "input, select, textarea", function(evt) {
		changesMade = true;
	});

	$(".nav-link").on("click", (evt) => {
		if (changesMade) {
			var url = $(evt.currentTarget).prop("href");
			swal({
				title: "Warning!",
				text: "Some changes are made, would you like to save current changes?",
				type: "warning",
				showCloseButton: false,
				showCancelButton: true,
                confirmButtonClass: "btn-success",
				confirmButtonText: "Update",
				cancelButtonClass: "btn-danger",
				cancelButtonText: "No",
			}, function(response) {
				if (response) {
					$("#form-user").append($(`<input type="hidden" name="redirect" value="${url}" />`));
					$("#button-user-update").click();
				}
				else {
					changesMade = false;
					window.location = url;
				}
			});
			evt.preventDefault;
			return false;
		}
		return true;
	});

    // jquery form validation
	$.validate({
		submit: {
			settings: {
				form: '#form-user',
				inputContainer: '#form-user .form-group',
				errorListClass: 'form-tooltip-error',
			}
		},
		labels: {
			'user[FirstName]': 'First Name',
			'user[LastName]': 'Last Name',
            'user[Email]': 'Email',
            'user[NewPassword]': 'New Password',
            'user[ConfirmPassword]': 'Confirm Password',
            'user[dob]': 'Date of Birth'
		},
	});
	$.validate({
		submit: {
			settings: {
				form: '#form-user_logs',
				inputContainer: '#form-user_logs .form-group',
				errorListClass: 'form-tooltip-error',
			}
		},
		labels: {
			'date': 'Date',
			'details': 'Details',
		},
		debug: true,
	});

	$('.fancybox-clicker').fancybox();

	function processSelectedFile(input, $element) {
		if (input.files && input.files[0]) {
			var reader = new FileReader();

			reader.onload = function (e) {
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

	$('#file-user_profile_pic').on('change', function(evt) {
		$('#load-screen').show();
		setTimeout(() => {
			$(evt.target).closest("form").submit();

			$('#load-screen').hide();
		}, 3000);
	});

	$('.delete_user_log').on('click', function(evt) {
		var theButton = $(this);
		console.log(theButton.data('log_id'));
		if(confirm("Are you sure you want to delete")){
			jQuery.ajax({
				type: "POST",
				url: "/users/ajax_delete_user_log",
				data: {
					user_log_id: theButton.data('log_id')
				},
				dataType: "json",
				success: function( ret ){
					window.location.reload();
				}
			});
		}
		evt.preventDefault();
		return false;
	});

	$('.fieldset-closable .widget-header').on('click', function(evt) {
		$('.fieldset-closable').toggleClass("closed");
	});

	$('#form-user').on('submit', function (se) {
		var hasError = false;

		<?php if ($tab == "permissions"): ?>
		var newPassword = $('#user-NewPassword').val();
		var confirmPassword = $('#user-ConfirmPassword').val();

		if (newPassword.length != 0 && newPassword.length < 4) {
			alert("New Password field should be at least 4 characters.");
			hasError = true;
		}
		else if (newPassword != confirmPassword) {
			alert("Confirm Password field does not match New Password field.");
			hasError = true;
		}

		<?php endif ?>

		if (hasError) {
			se.preventDefault();
			return false;
		}

		return true;
	});

	// initAutocomplete();

<?php if ($this->session->flashdata('status') && $this->session->flashdata('status') == 'success') { ?>
	swal({
		title: "Success!",
		text: "<?= $this->session->flashdata('success_msg') ?>",
		type: "success",
		confirmButtonClass: "btn-success",
		showConfirmButton: <?= $this->config->item('showConfirmButton') ?>,
		timer: <?= $this->config->item('timer') ?>
	});
<?php } else if ($this->session->flashdata('status') && $this->session->flashdata('status') == 'error') { ?>
	swal({
		title: "Error!",
		text: "<?= $this->session->flashdata('error_msg') ?>",
		type: "error",
		confirmButtonClass: "btn-danger"
	});
<?php } ?>

	var $assignedRegionInput = $("#input-accommodation-assigned_region");
	try {
		$assignedRegionInput.autocomplete({
			minLength: 2,
			source: (request, response) => {
				$.ajax({
					url: `/tech_regions/find_assignable_regions?term=${request.term}`,
					dataType: "json",
					success: response,
				});
			},
			focus: (event, ui) => {
				$assignedRegionInput.val( ui.item.name );
				return false;
			},
			select: (event, ui) => {
				$("#accommodation-assigned_region").val(ui.item.id);
				// $("#accommodation-assigned_region_type").val(ui.item.type);
				return false;
			},
		})
		.autocomplete("instance")._renderItem = (ul, item) => {
			return $("<li>")
				.append(`<div>${item.name}</div>`)
				.appendTo(ul);
		};
	}
	catch(ex) {

	}

    var deviceAccountsTable = $("#table-device_accounts");
    $.each(deviceAccounts, (index, deviceAccount) => {
        console.log(index, deviceAccount);
        var index = deviceAccountIndex;
        var tr = renderDeviceAccount(deviceAccount, deviceAccountIndex);
        deviceAccountIndex++;

        deviceAccountsTable.find("tbody").append(tr);
    });

    $('#button-add_account').on('click', () => {
        var tr = renderDeviceAccount({}, deviceAccountIndex);
        deviceAccountIndex++;

        deviceAccountsTable.find("tbody").append(tr);
    });
	
	// tick all script
	jQuery("#working_days_select_all").change(function(){

		var dom = jQuery(this);
		var chk_sel_all = dom.prop("checked");
		if( chk_sel_all == true ){
			jQuery(".working_days-checkbox").prop("checked",true);
		}else{
			jQuery(".working_days-checkbox").prop("checked",false);
		}

	});	

});

// google map autocomplete
var autocomplete;

// google address prefill
var componentForm2 = {

	route: {
	'type': 'long_name',
	'field': 'street_name'
	},
	locality: {
	'type': 'long_name',
	'field': 'locality'
	},
	sublocality_level_1: {
	'type': 'long_name',
	'field': 'sublocality_level_1'
	},
	administrative_area_level_1: {
	'type': 'short_name',
	'field': 'state'
	},
	postal_code: {
	'type': 'short_name',
	'field': 'postcode'
	}

};

function initAutocomplete() {
	<?php if( $this->config->item('country') ==1 ){ ?>
		var cntry = 'au';
	<?php }else{ ?>
		var cntry = 'nz';
	<?php } ?>

	var options = {
		types: ['geocode'],
		componentRestrictions: {
			country: cntry
		}
	};

	var input = document.getElementById('user-address');
	$(input).on('keydown', function(evt) {
		if (evt.which == 13 && $('.pac-container:visible').length) {
			evt.preventDefault();
			return false;
		}
	});

	try {
		autocomplete = new google.maps.places.Autocomplete(input, options);

		var processPlace = (place) => {
			console.log("place", place);

			$("#accommodation-address").val($("#user-address").val());
			$("#accommodation-lat").val(place.geometry.location.lat);
			$("#accommodation-lng").val(place.geometry.location.lng);

			var postcode = "";

			for (var ac of place.address_components) {
				try {
					if (ac.types.includes("postal_code")) {
						postcode = ac.long_name;
						break;
					}
				}
				catch(e) {}
			}

			$("#accommodation-postcode").val(postcode);

			if (postcode != "") {
				$.ajax({
					url: `/regions/postcode_info/${postcode}`,
					type: "get",
					dataType: "json",
					success: (data) => {
						console.log("result", data);
						if (data.success) {
							for (var field in data.info) {
								$(`#display-address-${field}`).html(data.info[field]);
							}

							$(".with-address-only").removeClass("d-none");
						}
						else {
							$(".with-address-only").addClass("d-none");

							$(`.address-postcodes`).removeClass("d-none");
							$(`#display-address-postcode`).html(postcode);
						}
					},
				});
			}

		};

		autocomplete.addListener("place_changed", () => {
			var place = autocomplete.getPlace();
			processPlace(place);
		});

        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({"address": input.value }, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK && results[0]) {
				processPlace(results[0]);
            }
        });
	}
	catch(ex) {
		console.log(ex);
	}


	autocomplete.addListener('place_changed', fillInAddress);

}


function fillInAddress() {	

	// Get the place details from the autocomplete object.
	var place = autocomplete.getPlace();

	// test
	for (var i = 0; i < place.address_components.length; i++) {

		var addressType = place.address_components[i].types[0];
		if (componentForm2[addressType]) {

			var val = place.address_components[i][componentForm2[addressType].type];
			document.getElementById(componentForm2[addressType].field).value = val;

		}

	}

	// street name
	var ac = jQuery("#user-address").val();
	var ac2 = ac.split(" ");
	var street_number = ac2[0];
	jQuery("#street_number").val(street_number);

	// get suburb from locality or sublocality
	var sublocality_level_1 = jQuery("#sublocality_level_1").val();
	var locality = jQuery("#locality").val();

	var suburb = ( sublocality_level_1 != '' )?sublocality_level_1:locality;
	jQuery("#suburb").val(suburb);

	// get suburb from google object 'vicinity'
	if( jQuery("#suburb").val() == '' ){
	jQuery("#suburb").val(place.vicinity);
	}        

}

</script>