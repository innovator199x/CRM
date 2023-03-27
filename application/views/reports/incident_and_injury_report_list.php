
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
            'link' => "/users/incident_and_injury_report_list"
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
            echo form_open('/users/incident_and_injury_report_list', $form_attr);
            ?>
            <div class="for-groupss row">
                <div class="col-lg-12 col-md-12 columns">
                    <div class="row">
                        <div class="col-md-2">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <a href="/users/incident_and_injury_report" class="btn">Add Report</a>
                        </div>

                        <div class="col-md-2 offset-1">
                            <label for="date_select">Date From:</label>
                            <input name="date_from_filter" placeholder="ALL" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $this->input->get_post('date_from_filter') ?>">
                        </div>

                        <div class="col-md-2">
                            <label for="date_select">To:</label>
                            <input name="date_to_filter" placeholder="ALL" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $this->input->get_post('date_to_filter'); ?>">
                        </div>

                        <div class="col-md-2">

                            <label for="agency_select">Staff</label>
                            <select id="staff" name="staff" class="form-control field_g2">
                                <option <?php echo (empty($this->input->get_post('staff'))) ? 'selected' : ''; ?>  value="">ALL</option>
                                <option <?php echo ($this->input->get_post('staff') == 1) ? 'selected' : ''; ?> value="1">Active</option>
                                <option <?php echo ($this->input->get_post('staff') == '0') ? 'selected' : ''; ?> value="0">Inactive</option>
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
                            <th>Date of incident</th>
                            <th>Time of incident</th>
                            <th>Nature of incident</th>
                            <th>Location of incident</th>
                            <th>Describe the incident</th>
                            <th>Injured Person</th>
                            <th>PDF</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        foreach ($lists->result() as $row) {
                            ?>

                            <tr data-staffactive="<?php echo $row->active; ?>">
                                <td>
                                    <a href="/users/incident_and_injury_report_details/<?php echo $row->incident_and_injury_id; ?>">
                                        <?php echo $this->system_model->formatDate($row->datetime_of_incident, 'd/m/Y'); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo $this->system_model->formatDate($row->datetime_of_incident, 'H:i') ?>
                                </td>
                                <td>
                                    <?php
                                  /*  switch ($row->nature_of_incident) {
                                        case 1:
                                            $nature_of_incident2 = 'Near Miss';
                                            break;
                                        case 2:
                                            $nature_of_incident2 = 'First Aid';
                                            break;
                                        case 3:
                                            $nature_of_incident2 = 'Medical Treatment';
                                            break;
                                        case 4:
                                            $nature_of_incident2 = 'Car accident';
                                            break;
                                        case 5:
                                            $nature_of_incident2 = 'Property damage';
                                            break;
                                        case 6:
                                            $nature_of_incident2 = 'Incident report';
                                            break;
                                    }*/
                                    //echo $nature_of_incident2;
                                    echo $this->users_model->getNatureOfIncident($row->nature_of_incident);
                                    ?>
                                </td>
                                <td><?php echo $row->location_of_incident ?></td>
                                <td><?php echo $row->describe_incident ?></td>
                                <td><?php echo $row->ip_name ?></td>
                                <td style="padding:5px 0 0 10px;">
                                    <a data-toggle="tooltip" title="PDF" target="_blank" href="/users/incident_and_injury_report_pdf/<?php echo $row->incident_and_injury_id ?>"><em class="fa fa-file-pdf-o" style="font-size:30px;color:#0082c6;"></em></a>
                                </td>
                            </tr>

                            <?php
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

    <h4>Incident Summary</h4>
    <p>This page lists all incidents or near misses that are recorded.</p>

    <pre>
<code>SELECT `iai`.`incident_and_injury_id`, `iai`.`datetime_of_incident`, `iai`.`nature_of_incident`, `iai`.`location_of_incident`, `iai`.`describe_incident`, `iai`.`ip_name`, `iai`.`created_date`, `sa`.`active`
FROM `incident_and_injury` AS `iai`
LEFT JOIN `staff_accounts` AS `sa` ON `sa`.`StaffID` = `iai`.`reported_to`
LEFT JOIN `staff_accounts` AS `sa2` ON `sa2`.`StaffID` = `iai`.`created_by`
WHERE `iai`.`deleted` = 0
ORDER BY `iai`.`created_date` DESC
LIMIT 50</code>
                    </pre>

</div>
<!-- Fancybox END -->

<script type="text/javascript">

    jQuery(document).ready(function () {

  //success/error message sweel alert pop  start
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
    //success/error message sweel alert pop  end

    })

</script>