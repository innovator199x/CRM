<?php
define('URL', $_SERVER['HTTP_HOST']);
if( strpos(URL,"dev")===false && strpos(URL,"localhost")===false){ // LIVE
    if ( strpos(URL,"nz") == false ) {
        $vjdTestLink = $this->system_model->old_crm_redirect("view_job_details.php?id=517618");
        $vpdTestLink = $this->system_model->old_crm_redirect("view_property_details.php?id=111018");
    }
    else {
        $vjdTestLink = $this->system_model->old_crm_redirect("view_job_details.php?id=420414");
        $vpdTestLink = $this->system_model->old_crm_redirect("view_property_details.php?id=165828");
    }
}
else {
    $vjdTestLink = $this->system_model->old_crm_redirect("view_job_details.php?id=827");
    $vpdTestLink = $this->system_model->old_crm_redirect("view_property_details.php?id=119");
}
?>

<div class="box-typical box-typical-padding">

    <?php

    // breadcrumbs template
    $bc_items = array(
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/benchmark/index"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

    <div id="mainContent" class="homepage">
	    <header class="box-typical-header">

            <div class="box-typical box-typical-padding">
			<?= form_open('/benchmark/index', [
                    'id' => 'page-load-form',
                    'method' => 'get',
                ]) ?>
                <div class="for-groupss row">
                    <div class="col-lg-10 col-md-10 columns">
                        <div class="row">
                            <div class="col-mdd-3">
                                <label for="agency_select">Date</label>
                                <input type="text" name="date" placeholder="ALL" class="flatpickr form-control flatpickr-input" value="<?= $this->input->get_post('date') ?>" />
                                <div class="mini_loader"></div>
                            </div>

                            <div class="col-mdd-3 columns">
                                <label class="col-sm-12 form-control-label">&nbsp;</label>
                                <button type="submit" class="btn btn-inline">Filter</button>
                            </div>
                            <div class="col-mdd-3 columns">
                                <label class="col-sm-12 form-control-label">&nbsp;</label>
                                <a href="<?= $vjdTestLink ?>" target="_blank"><button type="button" class="btn btn-inline">Sample VPD</button></a>
                            </div>
                            <div class="col-mdd-3 columns">
                                <label class="col-sm-12 form-control-label">&nbsp;</label>
                                <a href="<?= $vpdTestLink ?>" target="_blank"><button type="button" class="btn btn-inline">Sample VJD</button></a>
                            </div>
                        </div>
                    </div>
                </div>
            <?= form_close() ?>
            </div>


        </header>
        <table class="table table-hover main-table">
            <thead>
                <tr>
                    <th class="text-center">
                        Page
                    </th>
                    <th class="text-center">
                        Total Requests
                    </th>
                    <th class="text-center">
                        Avg (seconds)
                    </th>
                    <th class="text-center">
                        Min (seconds)
                    </th>
                    <th class="text-center">
                        Max (seconds)
                    </th>
                    <th>
                        &nbsp;
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($page_load_durations as $row):

                switch($row['page']) {
                    case 'VJD':
                        $url = $vjdTestLink;
                    break;
                    case 'VPD':
                        $url = $vpdTestLink;
                    break;
                    default:
                        $url = null;
                    break;
                }
                if ($url) {
                    $link = '<a href="'.$url.'" target="_blank">sample link</a>';
                }
                else {
                    $link = '';
                }
                ?>
                <tr>
                    <td><?= $row['page'] ?></td>
                    <td class="text-right"><?= $row['count'] ?></td>
                    <td class="text-right"><?= ($row['average']*0.001) ?></td>
                    <td class="text-right"><?= ($row['minimum']*0.001) ?></td>
                    <td class="text-right"><?= ($row['maximum']*0.001) ?></td>
                    <td><?= $link ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4>Page Benchmarks</h4>
	<p>
		This page shows data of page loading durations for selected pages for the given date (no date filter if date is empty).
	</p>

</div>
<!-- Fancybox END -->