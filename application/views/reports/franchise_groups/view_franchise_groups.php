
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
    .txl_lbl img, .txt_hid img{
        max-width: 100%;
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
            'link' => "/reports/view_franchise_groups"
        ),
    );
    if ($this->uri->segment(3)) {
        $bc_items[] = [
        'title' => "Franchise Groups",
        'status' => 'active',
        'link' => "/reports/view_add_franchise_groups/".$this->uri->segment(3)
        ];
    }
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

    <header class="box-typical-header">
        <div class="box-typical box-typical-padding">
            <div class="for-groupss row">
                <div class="col-md-2">
                    <a class="btn btn-danger add-icon-btn" href="/reports/view_add_franchise_groups" role="button">Add Franchise Groups</a>
                </div>
                <div class="col-md-10">
                    <a style="float:right;" class="btn btn-danger add-icon-btn" href="/reports/export_franchise_group" role="button">Export</a>
                </div>
            </div>
        </div>
    </header>

    <section>
        <div class="body-typical-body">
            <div class="table-responsive">
                <table class="table table-hover main-table">
                    <thead>
                        <tr>
                            <th>Franchise Group</th>
                            <th>Number of Offices</th>
                            <th>Edit</th>
                        </tr>
                    </thead>

                    <tbody>
<?php
if ($franchise_groups->num_rows() > 0) {
    foreach ($franchise_groups->result_array() as $row) {
        ?>
                                <tr>
                                    <td>
                                        <span class="txt_lbl lbl_page"><a href="/reports/view_franchise_groups/<?Php echo $row['franchise_groups_id']; ?>"><?php echo $row['name']; ?></a></span>
                                        <input type="text" style="width: 95%;" class="txt_hid name" value="<?php echo $row['name']; ?>" />
                                    </td>
                                    <td>
                                        <span class="txt_lbl lbl_page"><?php echo $row['agency_count']; ?></span>
                                    </td>
                                    <td>
                                        <a href="javascript:void(0);" class="btn_del_vf btn_edit">Edit</a>
                                        <div class="action_div">
                                            <form class="post-submit" method="post" action="/reports/update_franchise_groups_action_form_submit">
                                                <input type="hidden" name="name" style="width: 95%;" class="edit_txt_hid name" value="<?php echo $row['name']; ?>" />
                                                <button class="blue-btn submitbtnImg btn_update btn-success btn" title="Update">
                                                    <img class="inner_icon fk" src="<?php echo base_url('images/button_icons/save-button.png'); ?>"> 
                                                    Update
                                                </button>							
                                                <button class="blue-btn submitbtnImg btn_delete btn btn-danger" title="Delete" type="button">
                                                    <img class="inner_icon u" src="<?php echo base_url('images/button_icons/cancel-button.png'); ?>"> 
                                                    Delete
                                                </button>
                                                <button class="submitbtnImg btn_cancel btn btn-default" type="button" title="Cancel">
                                                    <img class="inner_icon vd" src="<?php echo base_url('images/button_icons/back-to-tech.png'); ?>"> 
                                                    Cancel
                                                </button>
                                                <input type="hidden" name="franchise_groups_id" class="franchise_groups_id" value="<?php echo $row['franchise_groups_id']; ?>" />
                                            </form>
                                        </div>							
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

 <!--Fancybox Start--> 
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

    <h4><?php echo $title; ?></h4>
    <p>
        This page shows all Franchise Groups.
    </p>

    <pre>
<code>SELECT `fg`.`franchise_groups_id`, `fg`.`name`, (SELECT COUNT(*) FROM agency a WHERE a.franchise_groups_id=fg.franchise_groups_id AND `status` = 'active') as `agency_count`
FROM `franchise_groups` AS `fg`
WHERE `fg`.`franchise_groups_id` >0
AND `fg`.`country_id` = <?php echo COUNTRY ?> 
ORDER BY `fg`.`name` ASC
LIMIT 50</code>
    </pre>

</div>
<!-- Fancybox END  -->


<script>
    jQuery(document).ready(function () {

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

        // update
        jQuery(".btn_update").click(function () {

            var $this = jQuery(this);
            var name = jQuery(this).parents("tr:first").find(".name").val();
            jQuery(this).parents("form.post-submit").find('.name').val(name);
            jQuery(this).parents("form.post-submit").submit();


            return;

            var error = "";

            if (description == "") {
                error += "Description is required";
            }

            if (page == "") {
                error += "Page is required";
            }

            if (error != "") {
                alert(error);
            } else {

                jQuery.ajax({
                    type: "POST",
                    url: "/reports/update_icon",
                    data: {
                        icon_id: icon_id,
                        description: description,
                        page: page
                    }
                }).done(function (ret) {
                    window.location = "/icons.php?update_success=1";
                    var $resp = jQuery.parseJSON(ret);
                    swal({
                        title: $resp.title,
                        text: $resp.message,
                        type: $resp.type
                    });
                    if ($resp.status) {
                        $this.parents("tr:first").find('.lbl_desc').html($this.parents("tr:first").find(".txt_hid.description").val());
                        $this.parents("tr:first").find('.lbl_page').html($this.parents("tr:first").find(".txt_hid.page").val());
                        $this.parents("tr:first").find(".action_div").hide();
                        $this.parents("tr:first").find(".btn_edit").show();
                        $this.parents("tr:first").find("input.txt_hid").removeClass('form-control').hide();
                        $this.parents("tr:first").find("span.txt_hid").hide();
                        $this.parents("tr:first").find(".txt_lbl").show();
                    }

                });

            }

        });


        // delete script
        jQuery(".btn_delete").click(function () {

            var $this = jQuery(this);
            var franchise_groups_id = jQuery(this).parents("tr:first").find(".franchise_groups_id").val();
            swal({
                title: "Trying to delete.",
                text: "Are you sure you want to delete?",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes",
                cancelButtonText: "No",
                closeOnConfirm: false,
                closeOnCancel: true
            }, function (isConfirm) {
                if (isConfirm) {
                    jQuery.ajax({
                        type: "POST",
                        url: "/reports/delete_franchise_groups_action_ajax",
                        data: {
                            'franchise_groups_id': franchise_groups_id
                        }
                    }).done(function (ret) {
                        //window.location = "/view_vehicles.php";
                        var $resp = jQuery.parseJSON(ret);
                        if ($resp.status === true) {
                            $this.parents('tr').addClass('being-removed');
                            setTimeout(() => {
                                $this.parents('tr').removeClass('being-removed').remove();
                            }, 500);
                        }


                        swal({
                            title: $resp.title,
                            text: $resp.message,
                            type: $resp.type,
                            showConfirmButton: false
                        });
                        setTimeout(() => {
                            swal.close();
                        }, 2000);
                    });
                }
            });
            // if(confirm("Are you sure you want to delete")){



            // }
        });

        // inline edit
        jQuery(".btn_edit").click(function () {

            var btn_txt = jQuery(this).html();

            jQuery(this).hide();

            if (btn_txt == 'Edit') {
                jQuery(this).parents("tr:first").find(".action_div").show();
                jQuery(this).parents("tr:first").find("input.txt_hid").addClass('form-control').show();
                jQuery(this).parents("tr:first").find("span.txt_hid").show();
                jQuery(this).parents("tr:first").find(".txt_lbl").hide();
            } else {
                jQuery(this).parents("tr:first").find(".action_div").hide();
            }


        });


        // cancel script
        jQuery(".btn_cancel").click(function () {
            jQuery(this).parents("tr:first").find(".action_div").hide();
            jQuery(this).parents("tr:first").find(".btn_edit").show();
            jQuery(this).parents("tr:first").find("input.txt_hid").removeClass('form-control').hide();
            jQuery(this).parents("tr:first").find("span.txt_hid").hide();
            jQuery(this).parents("tr:first").find(".txt_lbl").show();
        });


        // add icons show/hide script
        jQuery("#add_icon_btn").click(function () {

            jQuery("#add_icon_div").show();

        });

        // add header validation
        jQuery("#add_icon_form").submit(function () {

            var icon = jQuery("#icon").val();
            var description = jQuery("#description").val();
            var error = "";

            if (icon == "") {
                error += "Icon is required\n";
            }

            if (description == "") {
                error += "Description is required\n";
            }

            if (error != "") {
                alert(error);
                return false
            } else {
                return true;
            }

        });


    });
</script>