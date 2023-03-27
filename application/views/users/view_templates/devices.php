<div role="tabpanel" class="tab-pane fade active show" id="devices-tab">

    <div class="row">
        <div class="col-sm-12">
            <div class="py-3">

                <section class="widget widget-reports">
                    <header class="widget-header widget-header-blue">
                        Device Accounts
                    </header>
                    <div class="widget-content">
                        <div class="form-row pt-2">
                            <div class="col-sm-12 col-lg-8">
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

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <button type="submit" class="btn btn-primary" id="button-user-update">Update</button>
        </div>
    </div>

</div>

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
});

</script>