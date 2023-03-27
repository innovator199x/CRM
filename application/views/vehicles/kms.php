
<style>
    .col-mdd-3{
        max-width:15.5%;
    }
    .action_div{
        display: none;
    }
    .being-removed{
        background: #721c24;
    }
    .add-icon-btn {
        margin-top: 25px;
    }
</style>

<div class="box-typical box-typical-padding">

    <?php
// breadcrumbs template
    $bc_items = array(
        array(
            'title' => 'Reports',
            'link' => "/reports"
        ),
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/vehicles/view_kms",
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

    <header class="box-typical-header">
        <div class="box-typical box-typical-padding">

            <?php
            $form_attr = array(
                'id' => 'jform'
            );
            echo form_open('/vehicles/view_kms', $form_attr);
            ?>
            <div class="for-groupss row">
                <div class="col-md-10 columns">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Vehicle</label>
                            <select name="vehicle" class="form-control">
                                <option value="">----</option>
                                <?php foreach ($vehicles->result() as $vhcl): ?>
                                    <option <?Php echo ($filter_vehicle === $vhcl->vehicles_id) ? 'selected="selected"' : "" ?> value="<?php echo $vhcl->vehicles_id; ?>">
                                        <?php echo $vhcl->number_plate; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Driver</label>
                            <select name="driver" class="form-control">
                                <option value="">----</option>
                                <?php foreach ($drivers->result() as $drvr): ?>
                                    <option <?Php echo ($filter_driver === $drvr->StaffID) ? 'selected="selected"' : "" ?> value="<?php echo $drvr->StaffID; ?>">
                                        <?php echo $drvr->FirstName . ' ' . $drvr->LastName; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-1 columns">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <input class="btn" type="submit" name="btn_search" value="Search">
                        </div>

                    </div>

                </div>
            </div>
            </form>
        </div>
    </header>

    <section>
        <div class="body-typical-body">
            <div class="table-responsive">
                <table class="table table-hover main-table">
                    <thead>
                        <tr>
                            <th>Driver</th>
                            <th>Vehicle</th>
                            <th>Kms</th>
                            <th>Updated</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        if ($kms->num_rows() > 0) {
                            foreach ($kms->result_array() as $row) {
                                ?>
                                <tr>
                                    <td>
                                        <span class="txt_lbl"><?php echo ($row['FirstName'] != "") ? $row['FirstName'] . ' ' . $row['LastName'] : '----'; ?></span>
                                        <input type="hidden" name="kms_id" class="kms_id" value="<?php echo $row['kms_id']; ?>" />
                                    </td>
                                    <td>
                                        <span class="txt_lbl"><?php echo ($row['number_plate'] != "") ? $row['number_plate'] : '----'; ?></span>
                                    </td>
                                    <td>
                                        <span class="txt_lbl"><?php echo ($row['kms'] != "") ? $row['kms'] : '----'; ?></span>
                                    </td>
                                    <td style="border-right: 1px solid #ccc;">
                                        <span class="txt_lbl"><?php echo ($row['kms_updated'] != "") ? date("d/m/Y", strtotime($row['kms_updated'])) : '----'; ?></span>
                                    </td>	
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='5'>No Data</td></tr>";
                        }
                        ?>

                    </tbody>

                </table>
            </div>

            <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
            <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>

        </div>
    </section>
</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

    <h4><?php echo $title; ?></h4>
    <p>
        This page displays all vehicles with their drivers and kms.
    </p>
    <pre>
<code>SELECT *
FROM `kms` AS `k`
INNER JOIN `vehicles` as `v` ON k.`vehicles_id` = v.`vehicles_id`
INNER JOIN `staff_accounts` as `sa` ON v.`StaffID` = sa.`StaffID`
ORDER BY `k`.`kms_updated` DESC
LIMIT 50</code>
    </pre>

</div>
<!-- Fancybox END -->


<script>

</script>