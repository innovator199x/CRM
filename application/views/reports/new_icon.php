
<style>
    .col-mdd-3{
        max-width:15.5%;
    }
    .action_div{
        display: none;
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
        [
            'title' => 'All Icons',
            'link' => "/reports/view_icons"
        ],
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/reports/add_icon"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>
    <section>
        <div class="body-typical-body" id="zbody">
            <?php if ($this->session->flashdata('message')): ?>
                <div class="alert alert-<?php echo $this->session->flashdata('status'); ?>" role="alert">
                    <?php echo $this->session->flashdata('message'); ?>
                </div>
            <?php endif; ?>
            <?php
            echo validation_errors('<div class="alert alert-danger" role="alert">', '</div>');
            $form_attr = array(
                'id' => 'fyjcform',
                'class' => 'form add-icon-form'
            );
            echo form_open_multipart('/reports/add_icon_action_form_submit', $form_attr);
            ?>
            <div class="row">
                <div class="col-md-12 col-lg-5 columns">
                    <div class="form-row">
                        <label class="col-sm-3 form-control-label">Icon</label>
                        <div class="col-sm-9">
                            <p class="form-control-static">
                                <input type="file" class="form-control-input" id="iconfile" name="iconfile">
                            </p>
                        </div>
                    </div>
                    <div class="form-row">
                        <label class="col-sm-3 form-control-label">Page</label>
                        <div class="col-sm-9">
                            <p class="form-control-static">
                                <input type="text" class="form-control" id="page" name="page" value="">
                            </p>
                        </div>
                    </div>
                    <div class="form-row">
                        <label class="col-sm-3 form-control-label">Description</label>
                        <div class="col-sm-9">
                            <p class="form-control-static">
                                <input type="text" class="form-control" id="description" name="description" value="">
                            </p>
                        </div>
                    </div>
                    <div class="form-row">
                        <div lass="col-sm-9 offset-sm-3">
                            <button class="btn btn-primary submit-add-icon-btn" type="button">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </section>
</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

    <h4><?php echo $title; ?></h4>
    <p>
        This page shows enables adding new icon.
    </p>

</div>
<!-- Fancybox END -->


<script>
    jQuery(document).ready(function () {

        // add header validation
        jQuery(".submit-add-icon-btn").click(function (e) {
            e.preventDefault();
            var icon = jQuery("#iconfile").val();
            var description = jQuery("#description").val();
            var page = jQuery("#page").val();
            var error = "";
            var err = [];

            if (description == "") {
                error += "Description is required\n";
                err.push('Description is required');
            }
            if (icon == "") {
                error += "Icon is required\n";
                err.push('Icon is required');
            }
            $('#zbody .alert.alert-danger').remove();
            if (error != "") {
                //alert(error);

                err.forEach(element => {
                    $('#zbody').prepend('<div class="alert alert-danger" role="alert">' + element + '</div>');
                });
                return false
            } else {
                $(this).parents('form').submit();
            }

        });


    });
</script>