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
            'link' => "/daily/view_no_active_job_properties"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

    <section>
        <div class="box-typical box-typical-padding">
            <div class="col-md-12">
                <a href="/daily/view_no_active_job_properties<?php echo ($this->input->get('show_all') == "1") ? "" : "?show_all=1" ?>" >
                    <button class="btn btn-inline" type="button" >
                        <?php echo ($this->input->get('show_all') == 1) ? 'Hide Acknowledged' : 'Show All' ?>
                    </button>
                </a>
            </div>
        </div>
    </section>


    <section>
        <div class="body-typical-body">
            <div class="table-responsive">
                <table class="table table-hover main-table">
                    <thead>
                        <tr>
                            <th width="100"><b>Property ID</b></th>
                            <th><b>Address</b></th>
                            <th width="70"><b>Service</b></th>
                            <th><b>Agency</b></th>
                            <th width="100"><b>Created</b></th>
                            <th width="100"><b>Action</b></th>
                        </tr>
                    </thead>

                    <tbody>                


                        <?Php
                        foreach ($properties as $row) {
                            ?>
                            <tr class="body_tr jalign_left">
                                <td>
                                    <span><?Php echo $this->gherxlib->crmLink('vpd', $row['property_id'], $row['property_id']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span>
                                        <?Php echo $this->gherxlib->crmLink('vpd', $row['property_id'], "{$row['address_1']} {$row['address_2']}, {$row['address_3']} {$row['state']}"); ?>
                                    </span>
                                </td>
                                <td>							
								    <?php
                                    // display icons
                                    $job_icons_params = array(
                                        'service_type' => $row['j_service'],
                                        'job_type' => $row['j_type'],
                                        'sevice_type_name' => $row['ajt_type']
                                    );
                                    echo $this->system_model->display_job_icons($job_icons_params);
                                    ?>
                                </td>
                                <td>
                                    <span class="txt_lbl">
                                        <?Php echo $this->gherxlib->crmLink('vad', $row['agency_id'], "{$row['agency_name']}",'',$row['priority']); ?>
                                    </span>

                                </td>
                                <td> <?Php echo (($row['created'] != "") ? date('d/m/Y', strtotime($row['created'])) : ''); ?></td>
                                <td><input type="checkbox" class="is_acknowledge" name="tick_box" data-property-id="<?php echo $row['property_id']; ?>" value="<?php echo $row['hidden']; ?>" <?php echo ($row['hidden'] == 1) ? "checked"  :  ""; ?> /></td>
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
<p>This page catches properties without an active job, or without a recently completed YM.</p>
<p>User will be able to click the tick box in Action column to hide job properties.</p>
<p>User will be able to filter `Show All` and `Hide Acknowledged`.</p>
<pre><code><?php echo $sql_query; ?></code></pre>

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

        
        jQuery(".is_acknowledge").on('click', function() {
            var property_id = jQuery(this).attr('data-property-id');
            console.log(property_id);

            var acknowledge_val = ( jQuery(this).prop("checked" ) == true ) ? 1 : 0;
            jQuery(this).val(acknowledge_val);

            jQuery('#load-screen').show();

            jQuery.ajax({
                type: "POST",
                url: "<?php echo site_url(); ?>ajax/daily_ajax/ajax_is_acknowledge_update",
                dataType: 'json',
                data: {
                    property_id:property_id,
                    acknowledge: acknowledge_val
                }
            }).done(function(response) {                
                if (response) {
                    setInterval(() => {
                        $('#load-screen').hide();
                    }, 1000);     
                }
                location.reload();
            });
        });

        // jQuery("#filter_properties").on('click', function(e) 
        // {
        //     e.preventDefault();

        //     var is_show_attr = jQuery(this).attr('data-acknowledge');
        //     var is_show = jQuery(this).val();

        //     if (is_show_attr == "show_acknowledge") {
        //         jQuery(this).attr('data-acknowledge', 'hide_acknowledge');
        //         jQuery(this).text("Hide Acknowledged");
        //         jQuery(this).val(0);
        //     } else {
        //         jQuery(this).attr('data-acknowledge', 'show_acknowledge');
        //         jQuery(this).text("Show All");
        //         jQuery(this).val(null);
        //     }

        //     jQuery.ajax({
        //         type: "POST",
        //         url: "<?php echo site_url(); ?>ajax/daily_ajax/show_no_active_job_properties",
        //         dataType: 'json',
        //         data: {
        //             is_show: is_show,
        //             is_show_attr: is_show_attr
        //         }
        //     }).done(function(response) {
        //         console.log(response);
        //         // if (response) {
        //         //     setInterval(() => {
        //         //         $('#load-screen').hide();
        //         //     }, 1000);     
        //         // }
        //         // location.reload();
        //     });
            
        // });

    });


</script>
