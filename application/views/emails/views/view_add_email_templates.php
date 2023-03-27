<link rel="stylesheet" href="/inc/css/lib/clockpicker/bootstrap-clockpicker.min.css">
<style>
    .et_buttons{
        float: right;
    }
    .submitbtnImg {
        margin: 10px;
    }
    #et_div2 div .submitbtnImg {
        white-space: pre-wrap;
        min-height: 90%;
        min-width: 100%;
    }
    .tag_div {
        display: inline-block;
    }
    #et_body{
        height: 350px; 
    }
</style>
<div class="box-typical box-typical-padding">

    <?php
// breadcrumbs template
    $bc_items = array(
        array(
            'title' => 'Email Templates',
            'link' => "/email/view_email_templates"
        ),
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/email/view_add_templates"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>


    <section>
        <div class="body-typical-body">
            <div class="g_form">

                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="addproperty">
                                <form method="POST" id="template_form" action="/email/view_add_template_action_form_submit">
                                    <div class="row">
                                        <label class="addlabel">Template Name</label>
                                        <input required="required" type="text" class="form-control addinput template_name" name="template_name" id="template_name" />
                                    </div>

                                    <div class="row">
                                        <label class="addlabel">Subject</label>
                                        <input required="required" type="text" class="form-control addinput subject" name="subject" id="subject" />
                                    </div>

                                    <div class="row">
                                        <label class="addlabel">Type</label>
                                        <select required="required" id="temp_type" name="temp_type" class="form-control addinput temp_type">
                                            <option value="">--- Select ---</option>
                                            <?php
                                            if (count($template_types) > 0) {
                                                foreach ($template_types as $ett) {
                                                    ?>																
                                                    <option value="<?php echo $ett['email_templates_type_id'] ?>"><?php echo $ett['name'] ?></option>						
                                                    <?php
                                                }
                                            }
                                            ?>	
                                        </select>
                                    </div>

                                    <div class="row">
                                        <label class="addlabel">Body </label>
                                        <textarea required="required" name="et_body" id="et_body" class="form-control addtextarea et_body"></textarea>
                                    </div>

                                    <div class="row">
                                        <label class="addlabel">Call Centre</label>			
                                        <select required="required" name="show_to_call_centre" id="show_to_call_centre" class="form-control addinput">
                                            <option value="">----</option>
                                            <option value="1" <?php echo ( $et['show_to_call_centre'] == 1 ) ? 'selected="selected"' : ''; ?>>Yes</option>
                                            <option value="0" <?php echo ( $et['show_to_call_centre'] == 0 && is_numeric($et['show_to_call_centre']) ) ? 'selected="selected"' : ''; ?>>No</option>
                                        </select>
                                    </div>

                                    <div class="row et_buttons">
                                        <label class="addlabel">&nbsp;</label>
                                        <input type="hidden" id="email_var_target" />
                                        <button class="submitbtnImg btn" id="btn_submit" type="submit">
                                            Save
                                        </button>
                                        <button class="submitbtnImg  btn" id="btn_clear" type="button">
                                            Clear
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div id="et_div2" class="row">
                                <?php
                                if (count($template_tags) > 0) {
                                    foreach ($template_tags as $temp_tag) {
                                        ?>


                                        <div class="col-md-3">
                                            <button id="temp_tag_btn<?php echo $temp_tag['email_templates_tag_id'] ?>" class="submitbtnImg blue-btn email_variables_btn btn" id="btn_tag" type="button" title="<?php echo $temp_tag['tag'] ?>"><?php echo $temp_tag['tag_name'] ?></button>
                                        </div>

                                        <?php
                                    }
                                }
                                ?>		

                            </div>
                        </div>
                    </div>


                </div>
            </div>

            <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
            <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>

        </div>
    </section>

</div>



<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

    <h4>Add Email Templates</h4>
    <p>This page is used to add email templates.</p>

</div>
<!-- Fancybox END -->


<script type="text/javascript" src="/inc/js/lib/clockpicker/bootstrap-clockpicker.min.js"></script>
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







    });



</script>

<script>
    function typeInTextarea(el, newText) {
        // starting text highlight position
        var start = el.prop("selectionStart");
        //console.log("selection start: "+start+" \n");
        // end text highlight position
        var end = el.prop("selectionEnd");
        //console.log("selection start: "+end+" \n");
        // text area original text
        var text = el.val();
        // before text of the inserted location
        var before = text.substring(0, start);
        // after text of the inserted location
        var after = text.substring(end, text.length);
        // combine texts
        el.val(before + newText + after);
        // put text cursor at the end of the insertd tag
        el[0].selectionStart = el[0].selectionEnd = start + newText.length;
        // displat text cursor
        el.focus();
    }

    jQuery(document).ready(function () {

        jQuery(".email_variables_btn").on("click", function () {
            var tag = jQuery(this).attr("title");
            //var target =  jQuery(':focus');
            //console.log(target.val());
            var target = jQuery("#email_var_target").val();
            typeInTextarea(jQuery(target), tag);
            // console.log(tag);
            return false;
        });

        jQuery("#subject").click(function () {
            jQuery("#email_var_target").val("input#subject");
        });

        jQuery("#et_body").click(function () {
            jQuery("#email_var_target").val("textarea#et_body");
        });

        // clear
        jQuery("#btn_clear").click(function () {

            jQuery("#template_name, #subject, #et_body").val("");

        });


        jQuery("#template_form").submit(function () {

            var template_name = jQuery("#template_name").val();
            var subject = jQuery("#subject").val();
            var et_body = jQuery("#et_body").val();
            var temp_type = jQuery("#temp_type").val();
            var show_to_call_centre = jQuery("#show_to_call_centre").val();
            var error = "";

            if (template_name == "") {
                error += "Template Name is Required \n";
            }

            if (subject == "") {
                error += "Subject is Required \n";
            }

            if (temp_type == "") {
                error += "Template Type is Required \n";
            }

            if (et_body == "") {
                error += "Email Body is Required \n";
            }

            if (show_to_call_centre == "") {
                error += "Call Centre is Required \n";
            }

            if (error != "") {
                alert(error);
                return false;
            } else {
                return true;
            }

        });


    });
</script>