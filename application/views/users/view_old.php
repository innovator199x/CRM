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
		<?php echo $validationErrors; ?>
		</div>
	<?php
	}
	?>

	<?php if ($user['active'] == 0): ?>
	<div class="alert alert-warning" role="alert">
		This account is currently not active.
	</div>
	<?php endif; ?>

	<?php
	// no form on logs tab
	if ($tab != "logs"):
	?>
	<?= form_open_multipart(null, [
		'id' => 'form-user'
	]) ?>
	<?php endif; ?>
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
							<a class="nav-link <?= $tab == "devices" ? "active show" : "" ?>" href="/users/view/<?= $user["StaffID"] ?>/devices">
								<span class="nav-link-in">
									<i class="fa fa-laptop"></i>
									Devices Details
								</span>
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link <?= $tab == "permissions" ? "active show" : "" ?>" href="/users/view/<?= $user["StaffID"] ?>/permissions">
								<span class="nav-link-in">
									<i class="fa fa-unlock"></i>
									<span class="d-block">
										Permissions
									</span>
								</span>
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link <?= $tab == "licencing" ? "active show" : "" ?>" href="/users/view/<?= $user["StaffID"] ?>/licencing">
								<span class="nav-link-in">
									<i class="fa fa-info-circle"></i>
									Licencing Details
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
				$this->load->view("users/view_templates/{$tab}");
				?>

			</div>
		</div>
	<?php if ($tab != "logs"): ?>
		<div class="row">
			<div class="col-12">
				<button type="submit" class="btn btn-primary" id="button-user-update">Update</button>
			</div>
		</div>
	</form>
	<?php endif; ?>


</div>

<!-- Fancybox Start -->

<!-- ABOUT PAGE -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
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
</style>

<script>

$('document').ready((event) => {

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

	$('#button-user_profile_pic').on('click', function(evt) {
		$('input[name=user_profile_pic').click();
	});
	$('#button-user_electrical_license').on('click', function(evt) {
		$('input[name=user_electrical_license').click();
	});

	function processSelectedFile(input, $element) {
		if (input.files && input.files[0]) {
			var reader = new FileReader();

			reader.onload = function (e) {
				$element.attr('src', e.target.result).show();
			};

			reader.readAsDataURL(input.files[0]);
		}
	}

	$('input[name=user_profile_pic').on('change', function(evt) {
		processSelectedFile(this, $('#image-user_profile_pic'));
	});

	$('input[name=user_electrical_licence').on('change', function(evt) {
		processSelectedFile(this, $('#image-user_electrical_licence'));
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

		<?php endif; ?>

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
		text: "<?php echo $this->session->flashdata('success_msg') ?>",
		type: "success",
		confirmButtonClass: "btn-success",
		showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
		timer: <?php echo $this->config->item('timer') ?>
	});
<?php } else if ($this->session->flashdata('status') && $this->session->flashdata('status') == 'error') { ?>
	swal({
		title: "Error!",
		text: "<?php echo $this->session->flashdata('error_msg') ?>",
		type: "error",
		confirmButtonClass: "btn-danger"
	});
<?php } ?>

});

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
		autocomplete.addListener("place_changed", () => {
			var place = autocomplete.getPlace();

			$("#accommodation-address").val($("#user-address").val());
			$("#accommodation-lat").val(place.geometry.location.lat);
			$("#accommodation-lng").val(place.geometry.location.lng);
		});
	}
	catch(ex) {
		console.log(ex);
	}

try {
	var geocoder = new google.maps.Geocoder();
	geocoder.geocode({
		address: "<?= $user["address"] ?>",
	}, (result, status) => {
		if (status == "OK") {
			console.log(result);
			var geometry = result[0].geometry;
			var lat = geometry.location.lat();
			var lng = geometry.location.lng();

			$(".input-lat").val(lat);
			$(".input-lng").val(lng);
		}
	});
}
catch(ex) {
	console.log(ex);
}
}

</script>