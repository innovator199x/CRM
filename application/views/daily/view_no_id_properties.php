          
<style>
    .jalign_left{
        text-align:left;
    }
    .txt_hid, .btn_update{
        display:none;
    }
</style>

<div class="box-typical box-typical-padding">

    <?php
// breadcrumbs template
    $bc_items = array(
        array(
            'title' => 'Daily',
            'link' => "/daily/"
        ),
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/daily/view_no_id_properties"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

    <section>
        <div class="body-typical-body">
            <div class="table-responsive">
                <table class="table table-hover main-table">
                    <thead>
                        <tr>
                            <th style="width:10%">Property Id</th>
                            <th style="width:55%">Property Name</th>
                            <th style="width:25%">Agency Id</th>
                            <th style="width:10%;text-align: center">Edit</th>
                        </tr>
                    </thead>

                    <tbody>                


                        <?Php
                        foreach ($properties as $row) {
                            ?>
                            <tr class="body_tr jalign_left">
                                <td>
                                    <span><?Php echo $this->gherxlib->crmLink('vpd', $row['property_id'], $row['property_id']); ?>
                                        <!--<a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo $row['property_id']; ?></a>-->
                                    </span>
                                    <input type="hidden" class="property_id" value="<?php echo $row['property_id']; ?>" />
                                </td>
                                <td>
                                    <span>
                                        <?Php echo $this->gherxlib->crmLink('vpd', $row['property_id'], "{$row['address_1']} {$row['address_2']} {$row['address_3']} {$row['state']}"); ?>
                                        <a href="/view_property_details.php?id=<?php echo $row['property_id']; ?>"><?php echo "{$row['address_1']} {$row['address_2']} {$row['address_3']} {$row['state']}"; ?></a>
                                    </span>
                                </td>
                                <td>
                                    <span class="txt_lbl"><?php echo $row['agency_id']; ?></span>

                                    <select class="txt_hid agency_id form-control">
                                        <?php foreach ($agencies as $a) { ?>
                                            <option value="<?php echo $a['agency_id']; ?>"><?php echo $a['agency_name']; ?></option>
                                            <?php
                                        }
                                        ?>									
                                    </select>
                                </td>						
                                <td>
                                    <button class="blue-btn submitbtnImg btn_update btn">Update</button>
                                    <a href="javascript:void(0);" class="btn_del_vf btn_edit">Edit</a>
                                    <button class="submitbtnImg btn_cancel btn btn-danger" style="display:none;">Cancel</button>	
                                </td>
                            </tr>

                            <?php
                            $i++;
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
        This page allows you to assign agency to properties
    </p>
    <pre>
<code>SELECT *
FROM `property`
WHERE `agency_id` =0
ORDER BY `tenant_ltr_sent` ASC
LIMIT 50</code>
    </pre>

</div>
<!-- Fancybox END -->


<script type="text/javascript">

    jQuery(document).ready(function () {
        jQuery(".btn_edit").click(function () {

            jQuery(this).parents("tr:first").find(".btn_update").show();
            jQuery(this).parents("tr:first").find(".btn_edit").hide();
            jQuery(this).parents("tr:first").find(".btn_cancel").show();
            jQuery(this).parents("tr:first").find(".btn_delete").show();
            jQuery(this).parents("tr:first").find(".txt_hid").show();
            jQuery(this).parents("tr:first").find(".txt_lbl").hide();

        });

        jQuery(".btn_cancel").click(function () {

            jQuery(this).parents("tr:first").find(".btn_update").hide();
            jQuery(this).parents("tr:first").find(".btn_edit").show();
            jQuery(this).parents("tr:first").find(".btn_cancel").hide();
            jQuery(this).parents("tr:first").find(".btn_delete").hide();
            jQuery(this).parents("tr:first").find(".txt_lbl").show();
            jQuery(this).parents("tr:first").find(".txt_hid").hide();

        });

        jQuery(".btn_update").click(function () {

            var property_id = jQuery(this).parents("tr:first").find(".property_id").val();
            var agency_id = jQuery(this).parents("tr:first").find(".agency_id").val();
            var error = "";

            if (agency_id == "") {
                error += "Please Select Agency\n";
            }




            if (error != "") {
                swal({
                    title: "Error!",
                    text: error,
                    type: "error",
                    confirmButtonClass: "btn-danger"
                });
            } else {

                jQuery.ajax({
                    type: "POST",
                    url: "/daily/assign_agency_action_ajax",
                    data: {
                        property_id: property_id,
                        agency_id: agency_id
                    }
                }).done(function (ret) {
                    console.log(ret);
                    window.location = "/daily/view_no_id_properties";
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
