<div class="box-typical box-typical-padding">

	<?php
		// breadcrumbs template
		$bc_items = [
            [
				'title' => "Tech Regions",
				'status' => 'inactive',
				'link' => "/tech_regions/index",
            ],
            [
				'title' => $title,
				'status' => 'active',
				'link' => "/tech_regions/edit/{$techRegion["id"]}",
			],
        ];
		$bc_data['bc_items'] = $bc_items;
		$this->load->view('templates/breadcrumbs', $bc_data);
	?>


    <section>
        <div class="body-typical-body" style="padding-top:25px;">

            <?php if( validation_errors() ): ?>
                <div class="alert alert-danger">
                <?php echo validation_errors(); ?>
                </div>
            <?php endif; ?>

            <div class="container">

                <div class="form-row align-items-center my-2">
                    <div class="col-sm-4 col-md-2 text-right">
                        <label for="tech_region-name" class="font-weight-bold">Region Name</label>
                    </div>
                    <div class="col-sm-8 col-md-6">
                        <a data-auto-focus="false" data-fancybox data-src="#fancybox-tech_region-name" href="javascript:;">
                            <?= $techRegion["name"] ?>
                        </a>
                    </div>
                </div>

                <div class="form-row align-items-center my-2">
                    <div class="col-sm-4 col-md-2 text-right">
                        <label for="tech_region-breakpoint" class="font-weight-bold">Breakpoint</label>
                    </div>
                    <div class="col-sm-8 col-md-6">
                        <a data-auto-focus="false" data-fancybox data-src="#fancybox-tech_region-breakpoint" href="javascript:;">
                            <?= $techRegion["breakpoint"] ?>
                        </a>
                    </div>
                </div>

                <div class="form-row align-items-center my-2">
                    <div class="col-sm-4 col-md-2 text-right">
                        <label for="name" class="font-weight-bold">Included Regions</label>
                    </div>
                    <div class="col-sm-8 col-md-6">
                        <a data-auto-focus="false" data-fancybox data-src="#fancybox-tech_region-included_regions" href="javascript:;">
                            <?php
                            if (!empty($techRegion["regions"])) {
                                $includedRegionsText = implode(", ", array_map(function($region) {
                                    return $region["region_name"];
                                }, $techRegion["regions"]));
                            }
                            else {
                                $includedRegionsText = "No Data";
                            }
                            ?>
                            <?= $includedRegionsText ?>
                        </a>
                    </div>
                </div>

                <div class="form-row align-items-center my-2">
                    <div class="col-sm-4 col-md-2 text-right">
                        <label for="name" class="font-weight-bold">Assigned Technicians</label>
                    </div>
                    <div class="col-sm-8 col-md-6">
                        <a data-auto-focus="false" data-fancybox data-src="#fancybox-tech_region-assigned_technicians" href="javascript:;">
                            <?php
                            $includedTechniciansText = implode(", ", array_map(function($technician) {
                                return $this->system_model->formatStaffName($technician["FirstName"], $technician["LastName"]);
                            }, $assignedTechnicians));

                            if (empty($assignedTechnicians)) {
                                $includedTechniciansText = "No Data";
                            }
                            ?>
                            <?= $includedTechniciansText ?>
                        </a>
                    </div>
                </div>

            </div>

        </div>

    </section>

</div>

<div class="fancybox-form-container" id="fancybox-tech_region-name" style="display:none;">
	<form class="fancybox-form" data-tables='<?= json_encode(["tech_regions" => ["_idValue" => $techRegion["id"], "_idField" => "id"]]) ?>'>
		<section class="card card-blue-fill">
			<header class="card-header">Region Name</header>
			<div class="card-block">
				<div class="form-group">
					<input type="text" class="form-control editable-field" required data-table="tech_regions" data-field="name" value="<?= $techRegion['name'] ?>" />
					<input type="hidden" class="form-control original-field" data-table="tech_regions" data-field="name" value="<?= $techRegion['name'] ?>" />
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container" id="fancybox-tech_region-breakpoint" style="display:none;">
	<form class="fancybox-form" data-tables='<?= json_encode(["tech_regions" => ["_idValue" => $techRegion["id"], "_idField" => "id"]]) ?>'>
		<section class="card card-blue-fill">
			<header class="card-header">Breakpoint</header>
			<div class="card-block">
				<div class="form-group">
					<input type="number" class="form-control editable-field" required data-table="tech_regions" data-field="breakpoint" value="<?= $techRegion['breakpoint'] ?>" />
					<input type="hidden" class="form-control original-field" data-table="tech_regions" data-field="breakpoint" value="<?= $techRegion['breakpoint'] ?>" />
				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<div class="fancybox-form-container fancybox-refreshable" id="fancybox-tech_region-included_regions" style="display:none;">
	<form class="fancybox-form different" method="post" id="form-update_regions" action="/tech_regions/update_regions">
		<section class="card card-blue-fill">
			<header class="card-header">Included Regions</header>
			<div class="card-block">
				<div class="form-group">

                    <input type="hidden" name="tech_region_id" value="<?= $techRegion["id"] ?>" />

                    <div class="scrollview">
                        <ul>
                            <?php foreach($states as $state): ?>
                            <li class="state-group">
                                <a class="region-collapse collapsed d-block" href="#regions-<?= $state["StateID"] ?>" data-toggle="collapse" data-target="#regions-<?= $state["StateID"] ?>" aria-expanded="false">
                                    <i class="fa fa-angle-right" style="color: black"></i>
                                    <i class="fa fa-angle-down" style="color: black"></i>

                                    <?= $state["state"] ?>
                                </a>
                                <ul class="collapse ml-2" id="regions-<?= $state["StateID"] ?>">
                                    <?php foreach($state["regions"] as $index => $region): ?>
                                        <?php
                                        $checked = "";
                                        foreach ($techRegion["regions"] as $r) {
                                            if ($r["region_id"] == $region["regions_id"]) {
                                                $checked = "checked";
                                                break;
                                            }
                                        }
                                        ?>
                                        <li>
                                            <div class="checkbox mb-1">
                                                <?php
                                                $disabled = "";
                                                $assignedTechRegion = "";
                                                if (!is_null($region["tech_region_name"]) && $region["tech_region_id"] != $techRegion["id"]) {
                                                    $disabled = "disabled";
                                                    $assignedTechRegion = "( {$region["tech_region_name"] } )";
                                                }
                                                ?>
                                                <input type="checkbox" class="region-checkbox"
                                                    name="tech_regions_regions[<?= $index."_".$region["regions_id"] ?>][region_id]"
                                                    id="tech_regions_regions-<?= $region["regions_id"] ?>"
                                                    value="<?= $region["regions_id"] ?>"
                                                    data-region_id="<?= $region["regions_id"] ?>"
                                                    data-region_name="<?= $region["region_name"] ?>"
                                                    <?= $checked ?>
                                                    <?= $disabled ?>
                                                />
                                                <label for="tech_regions_regions-<?= $region["regions_id"] ?>" class="region-checkbox-label" >
                                                    <?= $region["region_name"] ?> <?= $assignedTechRegion ?>
                                                </label>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <p id="included_region_textarea" class="mt-2"></p>

				</div>
			</div>
		</section>
		<div class="text-right"><button type="submit" class="btn btn-primary update-button">Update</button></div>
	</form>
</div>

<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
	This page is used to edit the given tech region, by altering the region name, breakpoint, included regions, or assigned technicians.
	</p>
</div>

<div class="fancybox-form-container fancybox-refreshable" id="fancybox-tech_region-assigned_technicians" style="display:none;">
    <section class="card card-blue-fill">
        <header class="card-header">Assigned Technicians</header>
        <div class="card-block">
            <div class="form-group">
                <input type="text" class="form-control" id="input-assign_technician" placeholder="Type tech name to assign" value="" />

                <div id="container-assigned_technicians">
                    <?php foreach($assignedTechnicians as $technician): ?>
                        <div class="assigned_technician" data-accommodation_id="<?= $technician["accomodation_id"] ?>">
                            <a href="/users/view/<?= $technician["StaffID"] ?>" target="_blank"><?= "{$technician["FirstName"]} {$technician["LastName"]}" ?></a>
                            <i class="fa fa-times"></i>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">

    $(document).ready(() => {
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
                url: "/tech_regions/ajax_update_fields",
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

        $(".fancybox-form").not(".different").on("submit", submitFancybox);

        $("#form-update_regions").on("submit", (evt) => {
            if ($(".region-checkbox:checked").length == 0) {
                evt.preventDefault();

                swal({
                    title: "Error!",
                    text: "Select at least one region to include.",
                    type: "error",
                    showCancelButton: false,
                    confirmButtonText: "OK",
                    closeOnConfirm: false,
                    showConfirmButton: true,
                });

                return false;
            }
            return true;
        });

        const includedRegions = {};

        function renderIncludedRegionsText() {
            var a = Object.values(includedRegions).sort();
            $("#included_region_textarea").text(a.join(", "));
        }

        $("#form-tech_region-add").on("submit", (evt) => {
            if (Object.keys(includedRegions).length == 0) {
                swal({
                    title: "Error!",
                    text: "Please select a region to include.",
                    type: "error",
                    showConfirmButton: true,
                    confirmButtonClass: "btn-success",

                });
                evt.preventDefault();
                return false;
            }

            return true;
        });

        $(".region-checkbox").on("change", (evt) => {
            var $element = $(evt.target);
            var checked = $element.is(":checked");
            if (checked) {
                includedRegions[$element.data("region_id")] = $element.data("region_name");
            }
            else {
                delete includedRegions[$element.data("region_id")];
            }

            renderIncludedRegionsText();
        });

        $(".region-checkbox:checked").each((index, element) => {
            const $element = $(element);

            const regionId = $element.data("region_id");
            const regionName = $element.data("region_name");

            includedRegions[regionId] = regionName;
        });

        renderIncludedRegionsText();



        var shouldRefreshOnClose = false;
        var $assignTechnicianInput = $("#input-assign_technician");

        $assignTechnicianInput.autocomplete({
            minLength: 2,
            source: (request, response) => {
                $.ajax({
                    url: `/tech_regions/ajax_find_assignable_technician?term=${request.term}&tech_region_id=<?= $techRegion["id"] ?>`,
                    dataType: "json",
                    success: response,
                });
            },
            focus: (event, ui) => {
                $assignTechnicianInput.val( ui.item.name );
                return false;
            },
            select: (event, ui) => {
                const item = ui.item;

                const techRegionId = <?= $techRegion["id"] ?>;

                $assignTechnicianInput.prop("disabled", true);

                $.ajax({
                    type: "post",
                    url: "/tech_regions/ajax_assign_technician",
                    data: {
                        staff_id: item.StaffId,
                        accommodation_id: item.accomodation_id,
                        tech_region_id: techRegionId,
                    },
                    dataType: "json",
                    success: (data) => {
                        const div = $("<div/>")
                            .addClass("assigned_technician")
                            .data("accommodation_id", data.accommodation_id)
                            .append(`<a href="/users/view/{item.StaffID}" target="_blank">${item.FirstName} ${item.LastName}</a>`)
                            .append(`<i class="fa fa-times"></i>`);

                        $("#container-assigned_technicians").append(div);
                        $assignTechnicianInput.val("");
                        shouldRefreshOnClose = true;
                    },
                    error: (e) => {
                        console.log("error", e);
                    },
                })
                .always(() => {
                    $assignTechnicianInput.prop("disabled", false);
                });

                return false;
            },
        })
        .autocomplete("instance")._renderItem = (ul, item) => {
            return $("<li>")
                .append(`<div class="px-2 my-1">${item.name}</div>`)
                .appendTo(ul);
        };

        var activeDelete = [];
        $("#container-assigned_technicians").on("click", ".assigned_technician .fa-times", (evt) => {
            const $self = $(evt.currentTarget);
            const $parent = $self.parent();

            const accommodationId = $parent.data("accommodation_id");
            if (!activeDelete.includes(accommodationId)) {
                activeDelete.push(accommodationId);

                $.ajax({
                    url: "/tech_regions/ajax_unassign_technician",
                    type: "post",
                    data: {
                        accommodation_id: accommodationId,
                    },
                    success: () => {
                        $parent.remove();
                        shouldRefreshOnClose = true;
                    },
                    error: () => {

                    },
                }).always(() => {
                    activeDelete = activeDelete.filter((a) => a != accommodationId);
                });
            }
        });

        $(".fancybox-refreshable").on("click", ".fancybox-close-small", (evt) => {
            if (shouldRefreshOnClose) {
                window.location.reload();
            }
        });
    });
</script>

<?php $this->load->view("tech_regions/css") ?>