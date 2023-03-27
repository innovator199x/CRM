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
				'link' => "/tech_regions/reports",
            ],
        ];
		$bc_data['bc_items'] = $bc_items;
		$this->load->view('templates/breadcrumbs', $bc_data);
	?>

    <header class="box-typical-header">
        <div class="box-typical box-typical-padding">
            <div class="row">	
                <div class="col-md-2">
                    <label for="service_select"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></label>
                    <select id="state_filter" name="state_filter" class="form-control">
                        <option value="">ALL</option>
                        <?php foreach($states AS $state){
                            echo "<option value='".$state['name']."'>".$state['name']."</option>";
                        } ?>
                    </select>					
                </div>
            </div>
        </div>
    </header>

    <section>
        <div class="body-typical-body">

            <div id="container-table-data" class="table-responsive d-none">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Region Name</th>
                            <th>Breakpoint</th>
                            <th>Properties</th>
                            <th>Non Completed</th>
                            <th>No. of Techs</th>
                            <th>Props. Per Tech</th>
                            <th>Surplus Capacity</th>
                            <th>Techs Needed</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php /*

                        <?php foreach($techRegions as $techRegion): ?>
                            <tr>
                                <td>
                                    <a href="/tech_regions/edit/<?= $techRegion["id"] ?>"><?= $techRegion["name"] ?></a>
                                </td>
                                <td class="text-right pr-2">
                                    <?= number_format($techRegion["breakpoint"], 0, ".", ",") ?>
                                </td>
                                <td class="text-right pr-2">
                                    <?= number_format($techRegion["property_count"], 0, ".", ",") ?>
                                </td>
                                <td class="text-right pr-2">
                                    <?= number_format($techRegion["job_count"], 0, ".", ",") ?>
                                </td>
                                <td class="text-right pr-2">
                                    <?= number_format($techRegion["technician_count"], 0, ".", ",") ?>
                                </td>
                                <?php
                                $propsPerTechColor = "";
                                if ($techRegion["technician_count"] > 0) {
                                    $propsPerTech = $techRegion["property_count"] / $techRegion["technician_count"];
                                    if ($propsPerTech > $techRegion["breakpoint"]) {
                                        $propsPerTechColor = "background-color: #FFCCCB";
                                    }
                                    $propsPerTech = number_format($propsPerTech, 0, ".", ",");
                                }
                                else {
                                    $propsPerTech = "--";
                                }
                                ?>
                                <td class="text-right pr-2" style="<?= $propsPerTechColor ?>">
                                    <?= $propsPerTech ?>
                                </td>
                                <td class="text-right pr-2" style="">
                                    <?php
                                    if ($propsPerTech != "--") {
                                        $surplus = (($techRegion["breakpoint"] * $techRegion["technician_count"]) - ($propsPerTech * $techRegion["technician_count"]));
                                        $surplus = number_format($surplus, 0, ".", ",");
                                    }
                                    else {
                                        $surplus = "--";
                                    }
                                    ?>
                                    <?= $surplus ?>
                                </td>
                                <?php
                                $techsNeededColor = "";
                                if ($techRegion["techs_needed"] > 0) {
                                    $techsNeededColor = "background-color: #FFCCCB";
                                }
                                ?>
                                <td class="text-right pr-2" style="<?= $techsNeededColor ?>">
                                    <a data-auto-focus="false" data-fancybox data-src="#fancybox-tech_region-techs_needed-<?= $techRegion["id"] ?>" href="javascript:;">
                                        <?= number_format($techRegion["techs_needed"], 0, ".", ",") ?>
                                    </a>

                                    <div class="fancybox-form-container" id="fancybox-tech_region-techs_needed-<?= $techRegion["id"] ?>" style="display:none;">
                                        <form class="fancybox-form form-techs_needed">
                                            <section class="card card-blue-fill">
                                                <header class="card-header">Techs Needed</header>
                                                <div class="card-block">
                                                    <div class="form-group">
                                                        <input type="hidden" name="tech_region_id", value="<?= $techRegion['id'] ?>">
                                                        <input type="number" name="techs_needed" class="form-control text-right" required value="<?= $techRegion['techs_needed'] ?>" />
                                                    </div>
                                                </div>
                                            </section>
                                            <div class="text-right"><button type="submit" class="btn btn-primary update-techs_needed">Update</button></div>
                                        </form>
                                    </div>

                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <tr>
                            <th>Total:</th>
                            <td>&nbsp;</td>
                            <td class="text-right pr-2">
                                <?= array_reduce($techRegions, function($total, $techRegion) {
                                    return $total + $techRegion["property_count"];
                                }, 0)
                                ?>
                            </td>
                            <td class="text-right pr-2">
                                <?= array_reduce($techRegions, function($total, $techRegion) {
                                    return $total + $techRegion["job_count"];
                                }, 0)
                                ?>
                            </td>
                            <td class="text-right pr-2">
                                <?= array_reduce($techRegions, function($total, $techRegion) {
                                    return $total + $techRegion["technician_count"];
                                }, 0)
                                ?>
                            </td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td class="text-right pr-2">
                                <?= array_reduce($techRegions, function($total, $techRegion) {
                                    return $total + $techRegion["techs_needed"];
                                }, 0)
                                ?>
                            </td>
                        </tr>

                        */ ?>
                    </tbody>
                </table>
            </div>
            <div id="container-table-display" class="text-center p-4">
                <button type="button" class="btn btn-primary" id="button-display_data">Display Data</button>
            </div>

        </div>
    </section>
</div>

<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
	This report assists in analysing the amount of techs in a region verse the amount of properties, for purposes of hiring or reassigning technicians.
	</p>
</div>

<script>
    $(document).ready(() => {
        var techRegions = [];
        $(document).on("submit", ".form-techs_needed",(evt) => {
            const formData = new FormData(evt.target);

            const $form = $(evt.target);

            $('#load-screen').show();

            $.ajax({
                type: "POST",
                url: "/tech_regions/ajax_update_techs_needed",
                data: formData,
                processData: false,
                contentType: false,
                success: () => {
                    const techRegionId = $form.data("tech_region_id");
                    const newVal = $(`#input-techs_needed-${techRegionId}`).val();
                    const element = $(`#text-techs_needed-${techRegionId}`).data("techs_needed", newVal).text(newVal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                    if (newVal > 0) {
                        element.parent().css("background-color", "#FFCCCB");
                    }
                    else {
                        element.parent().css("background-color", "");
                    }

                    var totalTechsNeeded = 0;
                    $(".techs_needed").each((i, e) => {
                        const element = $(e);
                        const value = parseInt(element.data("techs_needed"));
                        totalTechsNeeded += value;
                    });

                    $("#total-techs_needed").text(totalTechsNeeded.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                },
                error: () => {
                }
            }).always(() => {
                $.fancybox.close();
                $('#load-screen').hide();
            });

            evt.preventDefault();
            return false;
        });

        $("#button-display_data").on("click", (evt) => {
            getData();
        })
        $("#state_filter").on("change", (evt) => {
            getData();
        })

        function getData(){
            $("#container-table-data table tbody").html('');
            $("#load-screen").show();
            let state_filter = $('#state_filter').val();
            $.ajax({
                url: "/tech_regions/ajax_reports_data",
                type: "post",
                data: {
                    state_filter: state_filter,
                },
                timeout: 0,
                success: (data) => {
                    techRegions = data.tech_regions;
                    totalLength = techRegions.length;

                    var tableBody = $("#container-table-data table tbody");

                    techRegions.forEach((techRegion) => {
                        const tr = $("<tr>");

                        const td1 = $("<td>")
                        td1.append(
                            $("<a>")
                            .prop("href", `/tech_regions/edit/${techRegion.id}`)
                            .text(techRegion.name)
                        );

                        const td2 = $("<td>").addClass("text-right pr-2");
                        td2.text(techRegion.breakpoint.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));

                        const td3 = $("<td>").addClass("text-right pr-2");
                        td3.text(techRegion.property_count.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));

                        const td4 = $("<td>").addClass("text-right pr-2");
                        td4.text(techRegion.job_count.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));

                        const td5 = $("<td>").addClass("text-right pr-2");
                        td5.text(techRegion.technician_count.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));

                        var propsPerTech = NaN;
                        const td6 = $("<td>").addClass("text-right pr-2");
                        if (techRegion.technician_count > 0) {
                            propsPerTech = techRegion.property_count / techRegion.technician_count;
                            if (propsPerTech > techRegion.breakpoint) {
                                td6.css("background-color", "#FFCCCB");
                            }
                            td6.text(propsPerTech.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                        }
                        else {
                            td6.text("--");
                        }

                        const td7 = $("<td>").addClass("text-right pr-2");
                        if (!isNaN(propsPerTech)) {
                            const surplus = (techRegion.breakpoint * techRegion.technician_count) - (propsPerTech * techRegion.technician_count);
                            td7.text(surplus.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                        }
                        else {
                            td7.text("--");
                        }

                        const td8 = $("<td>").addClass("text-right pr-2");

                        if (techRegion.techs_needed > 0) {
                            td8.css("background-color", "#FFCCCB");
                        }

                        const td8Trigger = $(`<a data-auto-focus="false" data-fancybox data-src="#fancybox-tech_region-techs_needed-${techRegion.id}" class="techs_needed" id="text-techs_needed-${techRegion.id}" href="javascript:;">`);
                        td8Trigger.data("techs_needed", techRegion.techs_needed);
                        td8Trigger.text(techRegion.techs_needed.replace(/\B(?=(\d{3})+(?!\d))/g, ","));

                        const td8Box = $(`
                            <div class="fancybox-form-container" id="fancybox-tech_region-techs_needed-${techRegion.id}" style="display:none;">
                                <form class="fancybox-form form-techs_needed" data-tech_region_id="${techRegion.id}">
                                    <section class="card card-blue-fill">
                                        <header class="card-header">Techs Needed</header>
                                        <div class="card-block">
                                            <div class="form-group">
                                                <input type="hidden" name="tech_region_id", value="${techRegion.id}">
                                                <input type="number" name="techs_needed" id="input-techs_needed-${techRegion.id}" class="form-control text-right" required value="${techRegion.techs_needed}" min="0" />
                                            </div>
                                        </div>
                                    </section>
                                    <div class="text-right"><button type="submit" class="btn btn-primary update-techs_needed">Update</button></div>
                                </form>
                            </div>
                        `);
                        td8.append(td8Trigger).append(td8Box);

                        tr.append(td1)
                            .append(td2)
                            .append(td3)
                            .append(td4)
                            .append(td5)
                            .append(td6)
                            .append(td7)
                            .append(td8);

                        tableBody.append(tr);
                    });

                    const totalTr = $("<tr>");
                    totalTr.append($("<th>").text("Total:"));
                    totalTr.append($("<th>").text(""));

                    const totalPropertiesTd = $("<th>").addClass("text-right pr-2");
                    totalPropertiesTd.text(techRegions.reduce((total, techRegion) => {
                        return total + techRegion.property_count;
                    }, 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                    totalPropertiesTd.appendTo(totalTr);

                    const totalJobCountTd = $("<th>").addClass("text-right pr-2");
                    totalJobCountTd.text(techRegions.reduce((total, techRegion) => {
                        return total + techRegion.job_count;
                    }, 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                    totalJobCountTd.appendTo(totalTr);

                    const totalTechniciansCountTd = $("<th>").addClass("text-right pr-2");
                    totalTechniciansCountTd.text(techRegions.reduce((total, techRegion) => {
                        return total + techRegion.technician_count;
                    }, 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                    totalTechniciansCountTd.appendTo(totalTr);

                    
                    const totalpropsPerTechCountTd = $("<th>").addClass("text-right pr-2");
                   
                   /* totalpropsPerTechCountTd.text("(Avg.) "+techRegions.reduce((total, techRegion) => {
                        return (total + (techRegion.property_count / techRegion.technician_count)) / totalLength;
                    }, 0).toFixed(0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));*/
                    var property_count_total = techRegions.reduce((total, techRegion) => {
                        return total + techRegion.property_count;
                    }, 0).toFixed(0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")

                    var technician_count_total = techRegions.reduce((total, techRegion) => {
                        return total + techRegion.technician_count;
                    }, 0).toFixed(0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")

                    totalpropsPerTechCountTd.text("(Avg.) "+property_count_total / technician_count_total );
                    totalpropsPerTechCountTd.appendTo(totalTr);
                    

                    const totalsurplusCountTd = $("<th>").addClass("text-right pr-2");
                    totalsurplusCountTd.text(techRegions.reduce((total, techRegion) => {
                        return total + ((techRegion.breakpoint * techRegion.technician_count) - ((techRegion.property_count / techRegion.technician_count) * techRegion.technician_count));
                    }, 0).toFixed(0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                    totalsurplusCountTd.appendTo(totalTr);

                    const totalTechsNeededCountTd = $("<th>").addClass("text-right pr-2").prop("id", "total-techs_needed");
                    totalTechsNeededCountTd.text(techRegions.reduce((total, techRegion) => {
                        return total + parseInt(techRegion.techs_needed);
                    }, 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                    totalTechsNeededCountTd.appendTo(totalTr);

                    totalTr.appendTo(tableBody);
                },
            }).always(() => {
                $("#container-table-display").hide();
                $("#container-table-data").removeClass("d-none");

                $("#load-screen").hide();
            });
        }
    });
</script>