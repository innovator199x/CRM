          
<style>
    #btn_add_div{
        text-align: left;
        margin-top: 10px;
    }
    #template_tbl th, #template_tbl td{
        text-align: left;
    }
    .colorItGreen{
        color: green;
    }
    .colorItRed{
        color: red;
    }
    .txt_hid, .btn_update_bn, .btn_cancel_bn, .btn_delete_bn{
        display:none;
    }
    #agency_booking_notes_div {
        clear: both; display: none;
    }
    .notes-column {
        width: 60%;
    }
    .agency-column {
        width: 20%;
    }

    .btn_delete_bn {
        margin-top:5px
    }
</style>

<div class="box-typical box-typical-padding">

    <?php
// breadcrumbs template
    $bc_items = array(
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/agency2/view_agency_booking_notes"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

    <header class="box-typical-header">

        <div class="box-typical box-typical-padding">

            <div class="for-groupss row">
                <div class="col-md-12 columns">
                    <div class="row">

                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <button class="addinput submitbtnImg btn" id="btn-add_agency_booking_notes" type="button" >
                                Add Notes
                            </button>
                        </div>	

                        <div class="col-md-10">
                            <form id="agency_booking_notes_form" action="/agency2/create_booking_notes_action_form_submit" method="POST">
                                <div id="agency_booking_notes_div" class="row">			
                                    <div class="col-md-2">
                                        <label class="addlabel">Agency</label>
                                        <select class="addinput agency_id form-control" name="agency_id" id="agency_id">
                                            <option value="">--- Select ---</option>
                                            <?Php
                                            foreach ($agency_list as $a) {
                                                ?>
                                                <option value="<?Php echo $a['agency_id'] ?>"><?Php echo $a['agency_name'] ?></option>
                                                <?Php
                                            }
                                            ?>

                                        </select>
                                    </div>

                                    <div class="col-md-8">
                                        <label class="addlabel">Notes</label>
                                        <input class="addinput agency_booking_notes form-control" name="agency_booking_notes" id="agency_booking_notes" type="text" placeholder="Enter Agency Booking Notes">
                                    </div>
                                    <div class="col-md-2">
                                        <label>&nbsp;</label>
                                        <button class="addinput submitbtnImg eagdtbt blue-btn btn-save_booking_note btn btn-success" id="btn-save_booking_note" type="submit">
                                            Save
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>	
                    </div>

                </div>
            </div>

        </div>
    </header>


    <section>
        <div class="body-typical-body">

            <?Php
            foreach ($agency_booking_notes as $agency_id => $agency) {
                $agency_name = $agency['name'];
                $data = $agency['data'];
                if (!count($data)) {
                    continue;
                }
                ?>
                <h2><?Php echo $agency_name; ?></h2>
                <div class="table-responsive">
                    <table class="table table-hover main-table">
                        <thead>
                            <tr class="toprow jalign_left">				
                                <th class="notes-column">Notes</th>
                                <th class="agency-column">Agency</th>
                                <th>Added By</th>
                                <th>Edit</th>
                            </tr>
                        </thead>

                        <tbody>                


                            <?Php
                            foreach ($data as $row) {
                                ?>
                                <tr>
                                    <td>
                                        <span class="txt_lbl"><?php echo $row['notes']; ?></span>
                                        <input type="text" class="txt_hid bn_notes form-control" value="<?php echo $row['notes']; ?>" />
                                    </td>
                                    <td>
                                        <?php echo $this->gherxlib->crmlink('vad', $agency_id, $agency_name) ?>
        <!--                                        <a href="view_agency_details.php?id=<?php //echo //$row['agency_id'];   ?>">
                                        <?php //echo $agency_name; ?>
        </a>-->
                                    </td>
                                    <td><?php echo $this->system_model->formatStaffName($row['FirstName'], $row['LastName']); ?></td>
                                    <td>
                                        <input type="hidden" class="bn_id" value="<?php echo $row['booking_notes_id']; ?>" />
                                        <button type="button" class="blue-btn submitbtnImg btn_update_bn btn btn-success">Update</button>
                                        <a href="javascript:void(0);" class="btn_del_vf btn_edit_bn btn">Edit</a>
                                        <button type="button" class="submitbtnImg btn_cancel_bn btn btn">Cancel</button>
                                        <button type="button" class="blue-btn submitbtnImg btn_delete_bn btn btn-danger">Delete</button>
                                    </td>

                                </tr>

                                <?php
                                $i++;
                            }
                            ?>
                        </tbody>

                    </table>
                </div>
                <?Php
            }
            ?>
        </div>
    </section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

    <h4><?php echo $title; ?></h4>
    <p>
        This page allows you to add, edit and delete Agency Booking Notes
    </p>

</div>
<!-- Fancybox END -->


<script type="text/javascript">

    jQuery(document).ready(function () {
        jQuery("#btn-add_agency_booking_notes").click(function () {

            jQuery("#agency_booking_notes_div").toggle();

        });


        jQuery(".btn_edit_bn").click(function () {

            jQuery(this).parents("tr:first").find(".btn_update_bn").show();
            jQuery(this).parents("tr:first").find(".btn_edit_bn").hide();
            jQuery(this).parents("tr:first").find(".btn_cancel_bn").show();
            jQuery(this).parents("tr:first").find(".btn_delete_bn").show();
            jQuery(this).parents("tr:first").find(".txt_hid").show();
            jQuery(this).parents("tr:first").find(".txt_lbl").hide();

        });

        jQuery(".btn_cancel_bn").click(function () {

            jQuery(this).parents("tr:first").find(".btn_update_bn").hide();
            jQuery(this).parents("tr:first").find(".btn_edit_bn").show();
            jQuery(this).parents("tr:first").find(".btn_cancel_bn").hide();
            jQuery(this).parents("tr:first").find(".btn_delete_bn").hide();
            jQuery(this).parents("tr:first").find(".txt_lbl").show();
            jQuery(this).parents("tr:first").find(".txt_hid").hide();

        });

        jQuery(".btn_update_bn").click(function () {

            var bn_id = jQuery(this).parents("tr:first").find(".bn_id").val();
            var bn_notes = jQuery(this).parents("tr:first").find(".bn_notes").val();
            var error = '';

            if (bn_notes == "") {
                error += "Notes is required";
            }

            if (error != "") {
                alert(error);
            } else {

                jQuery.ajax({
                    type: "POST",
                    url: "/agency2/update_booking_notes_action_ajax",
                    data: {
                        bn_id: bn_id,
                        bn_notes: bn_notes
                    }
                }).done(function (ret) {
                    window.location.reload();
                });

            }

        });

        jQuery(".btn_delete_bn").click(function () {

            var bn_id = jQuery(this).parents("tr:first").find(".bn_id").val();

            if (confirm("Are you sure you want to delete booking notes?")) {
                jQuery.ajax({
                    type: "POST",
                    url: "/agency2/delete_booking_notes_action_ajax",
                    data: {
                        bn_id: bn_id
                    }
                }).done(function (ret) {
                    window.location.reload();
                });
            }
        });

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


</script>
