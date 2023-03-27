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
				'title' => "Add",
				'status' => 'active',
				'link' => "/tech_regions/add",
			],
        ];
		$bc_data['bc_items'] = $bc_items;
		$this->load->view('templates/breadcrumbs', $bc_data);
	?>


    <section>
        <div class="body-typical-body" style="padding-top:25px;">

            <?php
            if( validation_errors() ): ?>
                <div class="alert alert-danger">
                <?php echo validation_errors(); ?>
                </div>
            <?php
            endif;

            if ($this->session->flashdata("success")):
                $newTechRegion = $this->session->flashdata("new_tech_region");
            ?>
            <div class="alert alert-success" role="alert">
                Successfully added tech region: <a href="/tech_regions/edit/<?= $newTechRegion["tech_region_id"] ?>"><?= $newTechRegion["name"] ?></a>.
            </div>
            <?php
            elseif ($this->session->flashdata("success") === false):
                ?>
                <div class="alert alert-danger" role="alert">
                    Failed adding tech region.
                </div>
                <?php
            endif;
            ?>


            <?php
                $form_attr = [
                    "id" => "form-tech_region-add"
                ];
                echo form_open($page_redirect,$form_attr);
            ?>

            <div class="container">

                <div class="form-row align-items-center my-2">
                    <div class="col-sm-4 col-md-2 text-right">
                        <label for="tech_region-name" class="font-weight-bold">Region Name</label>
                    </div>
                    <div class="col-sm-8 col-md-6">
                        <input type="text" name="tech_region[name]" id="tech_region-name" required class="form-control" />
                    </div>
                </div>

                <div class="form-row align-items-center my-2">
                    <div class="col-sm-4 col-md-2 text-right">
                        <label for="tech_region-breakpoint" class="font-weight-bold">Breakpoint</label>
                    </div>
                    <div class="col-sm-8 col-md-6">
                        <input type="number" name="tech_region[breakpoint]" id="tech_region-breakpoint" required class="form-control" min="0" />
                    </div>
                </div>

                <div class="form-row align-items-center my-2">
                    <div class="col-sm-4 col-md-2 text-right">
                        <label for="name" class="font-weight-bold">Included Regions</label>
                    </div>
                    <div class="col-sm-8 col-md-6">
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
                                            <li>
                                                <div class="checkbox mb-1">
                                                    <?php
                                                    $disabled = "";
                                                    $assignedTechRegion = "";
                                                    if (!is_null($region["tech_region_name"])) {
                                                        $disabled = "disabled";
                                                        $assignedTechRegion = "( {$region["tech_region_name"] } )";
                                                    }
                                                    ?>
                                                    <input type="checkbox" class="region-checkbox"
                                                        name="tech_regions_regions[<?= $index ?>][region_id]"
                                                        id="tech_regions_regions-<?= $region["regions_id"] ?>"
                                                        value="<?= $region["regions_id"] ?>"
                                                        data-region_id="<?= $region["regions_id"] ?>"
                                                        data-region_name="<?= $region["region_name"] ?>"
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

                    <div class="col-sm-12 col-md-8 text-right mt-4">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>

            </div>

            </form>

        </div>

    </section>

</div>

<script type="text/javascript">
    $(document).ready(() => {
        const includedRegions = {};

        function renderIncludedRegionsText() {
            var a = Object.values(includedRegions).sort()
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
    });
</script>

<?php $this->load->view("tech_regions/css") ?>