<div class="box-typical box-typical-padding">

	<?php
		// breadcrumbs template
		$bc_items = [
            [
				'title' => "Tech Regions",
				'status' => 'active',
				'link' => "/tech_regions/index",
            ],
        ];
		$bc_data['bc_items'] = $bc_items;
		$this->load->view('templates/breadcrumbs', $bc_data);
	?>


    <section>
        <div class="body-typical-body">

            <div class="text-right mb-2">
                <a class="btn btn-success" href="/tech_regions/reports">Tech Region Numbers</a>
                <a class="btn btn-primary" href="/tech_regions/add">Add Tech Region</a>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Region Name</th>
                            <th>Breakpoint</th>
                            <th>No. of Regions</th>
                            <th>No. of Techs</th>
                            <th>Regions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach($techRegions as $techRegion): ?>
                            <tr>
                                <td>
                                    <a href="/tech_regions/edit/<?= $techRegion["id"] ?>"><?= $techRegion["name"] ?></a>
                                </td>
                                <td class="text-right pr-2">
                                    <?= number_format($techRegion["breakpoint"], 0, ".", ",") ?>
                                </td>
                                <td class="text-right pr-2">
                                    <?= number_format($techRegion["count"], 0, ".", ",") ?>
                                </td>
                                <td class="text-right pr-2">
                                    <?= number_format($techRegion["technician_count"], 0, ".", ",") ?>
                                </td>
                                <td>
                                    <?= implode(", ", array_map(function($r) {
                                            return $r["region_name"];
                                        }, $techRegion["regions"])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-2">
                <div class="row">
                    <div class="col-6">
                        <h4 class="font-weight-bold mb-2">Unassigned Regions</h4>
                        <ol class="ml-4">
                            <?php foreach ($unassignedRegions as $region): ?>
                                <li><?= $region["region_name"] ?> (<?= $region["region_state"] ?>) </li>
                            <?php endforeach; ?>
                        </ol>
                    </div>

                    <div class="col-6">
                        <h4 class="font-weight-bold mb-2">Unassigned Technicians</h4>
                        <ol class="ml-4">
                            <?php foreach ($unassignedTechnicians as $technician): ?>
                                <li>
                                    <a href="/users/view/<?= $technician["StaffID"] ?>" target="_blank">
                                        <?= $technician["FirstName"] ?> <?= $technician["LastName"] ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
	This page is used to add and assess tech regions, before running the final tech regions report.
	</p>
</div>