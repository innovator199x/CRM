<link rel="stylesheet" href="/inc/css/lib/clockpicker/bootstrap-clockpicker.min.css">
<style>
    .et_buttons{
        float: right;
    }
    .submitbtnImg {
        margin:10px;
    }
    .tag_div {
        display: inline-block;
    }
    div.attachment-container {
        margin-top:10px;
    }
    .attachment-label{
        height: 40px;
        margin-top: 20px;
    }
    #et_attachment_icon {
        color: #9f9f9f;
        font-size: 33px;
        margin: 10px;

    }
    #et_body{
        height: 350px; 
        margin: 0; 
        padding: 8px;
    }
</style>
<style>
    .grey-btn{
        background-color: #dedede;
    }
    .tag_div .grey-btn:hover{
        background-color: #dedede;
    }
    #btn_et_send:hover{
        background-color: #00AEEF;
    }
    #et_attachment_icon{
        float: left;
        cursor: pointer
    }
    .et_attachment_checkbox{
        float: left !important;
        width: auto !important;
        margin-right: 5px !important;
        position: relative; 
        bottom: 3px
    }
    .et_attachment_icon_div{
        float: left; 
        margin-top: 4px; 
        margin-right: 10px; 
        margin-bottom: 30px;
    }
    #et_preview{
        text-align: left; 
        margin-top: 4px; 
        padding-left: 120px;
    }

    .fadeIt{
        opacity: 0.5;
    }

    #preview_email_temp_div{
        width: 500px;
        text-align: left;
        padding: 22px;
    }
    .prev_et_body_lbl{
        vertical-align: top;
    }
    #preview_email_temp_div .td_lbl{
        font-weight: bold;
    }
    #bottom_text{
        margin-top: 30px;
        display: none;
    }
</style>
<div class="box-typical box-typical-padding">

    <?php
// breadcrumbs template
    $bc_items = array(
        array(
            'title' => 'Email Templates',
            'link' => "/email/view_email_template"
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
                        <div class="col-md-4">
                            <div>

                                <div class="row email_to_row_div">
                                    <label class="addlabel">
                                        From:						
                                    </label>
                                    <input type="text" name="from_email" class="addinput et_from form-control" id="et_from" value="<?php echo $this->config->item('sats_info_email'); ?>" />
                                </div>

                                <div class="row email_to_row_div">
                                    <label class="addlabel">
                                        To:
                                    </label>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <div id="et_to_icon" data-target-id="et_to" class="input-group-text inner_icon email_to_icon"><i class="fa fa-users"></i></div>
                                        </div>
                                        <input type="text" name="to_email" class="addinput et_to form-control" id="et_to" style="padding-left: 40px;" value="<?php echo $to_email; ?>" />
                                    </div>

                                </div>

                                <div class="row email_to_row_div">
                                    <label class="addlabel">
                                        CC:
                                    </label>
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <div id="et_cc_icon" data-target-id="et_cc" class="input-group-text inner_icon email_to_icon "><i class="fa fa-users"></i></div>
                                        </div>
                                        <input type="text" name="cc_email" class="addinput et_cc form-control" id="et_cc" style="padding-left: 40px;" />
                                    </div>

                                </div>

                                <div class="row">
                                    <label class="addlabel">Subject:</label>
                                    <input type="text" class="addinput subject form-control" name="subject" id="et_subject" />
                                </div>

                                <div class="row">
                                    <label class="addlabel">Body:</label>
                                    <textarea class="addtextarea et_body form-control" id="et_body"  name="body"></textarea>
                                </div>			

                                <div class="row attachment-container">
                                    <label class="addlabel attachment-label">
                                        Attachment: 
                                    </label>

                                    <div>
                                        <div class="et_attachment_icon_div">
                                            <i class="fa fa-paperclip inner_icon" id="et_attachment_icon"></i>
                                        </div>
                                        <div id="et_attachment_hid_div" style="display:none;">
                                            <div id="et_attachment_file" class="row">													
                                                <div class="col-md-6">
                                                    <input type="checkbox" name="job_pdf[]" value="inv" class="addinput et_attachment_checkbox" id="pdf_inv_chk" /> <span class="attachment_job_pdf fadeIt">Invoice</span> 
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="checkbox" name="job_pdf[]" value="cert" class="addinput et_attachment_checkbox" id="pdf_cert_chk" /> <span class="attachment_job_pdf fadeIt">Certificate</span> 
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="checkbox" name="job_pdf[]" value="comb" class="addinput et_attachment_checkbox" id="pdf_comb_chk" /> <span class="attachment_job_pdf fadeIt">Combined</span>  
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="checkbox" name="marked_as_copy" value="1" class="addinput et_attachment_checkbox" /> <span class="fadeIt">Mark as Copy</span>  
                                                </div>								
                                            </div>
                                            <div id="browse_file">
                                                <input type="file" name="et_file_upload" id="et_file" class="addinput et_file" />
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="row">

                                    <label class="addlabel">&nbsp;</label>

                                    <div style="float: left;">								

                                        <input type="hidden" id="email_var_target" />

                                        <input type="hidden" id="email_temp_id" />

                                        <a id="preview_email_temp_link" href="#preview_email_temp_div">
                                            <button type="button" id="btn_et_preview" class="submitbtnImg grey-btn btn">
                                                <span id="btn_et_preview_icon">Preview</span>
                                            </button>
                                        </a>

                                        <button type="button" id="btn_et_clear" class="submitbtnImg grey-btn btn" style="margin-right: 20px;">
                                            Clear
                                        </button>


                                        <input type="hidden" name="job_id" value="<?php echo $job_id; ?>" />

                                    </div>


                                    <div style="float: right;">	
                                        <button type="submit" id="btn_et_send" class="submitbtnImg grey-btn btn" style="margin-right: 33px;">
                                            Send
                                        </button>
                                    </div>

                                </div>



                            </div>
                        </div>
                        <div class="col-md-5 offset-1">
                            <div id="et_div2">

                                <div id="send_to_div" style="display:none;">
                                    <h2 class="heading tag_header_div" id="send_to_header">Send To:</h2>
                                    <?php
                                    $pt_i = 1;
                                    $pt = $pt;
                                    foreach ($pt as $pt_r) {
                                        $pt_row = (array) $pt_r;
                                        $tl_tenants[] = array(
                                            'email' => $pt_row['tenant_email'],
                                            'firstname' => $pt_row['tenant_firstname']
                                        );
                                        ?>

                                        <div class="tag_div">
                                            <button class="btn submitbtnImg <?= ( $pt_row['tenant_email'] != '' ) ? 'green-btn' : 'grey-btn'; ?> email_variables_btn et_to_emails" id="btn_tag" data-email-to="<?= $pt_row['tenant_email'] ?>" type="button">
                                                <?= $pt_row['tenant_firstname'] ?><br />
                                                <span style="font-size:12px !important; margin-left:25px;">{tenant_<?= $pt_i ?>}</span>
                                            </button>
                                        </div>

                                        <?php
                                        $pt_i++;
                                    }

//print_r($tl_tenants);
                                    ?>

                                    <?php
                                    /*
                                      $PMTenantsList = $sats_query->getTenantsFromPM_Job($_REQUEST['job_id'])['ContactPersons'];
                                      $ptl_tenants = [];
                                      if($PMTenantsList){
                                      $ptl_count=0;
                                      foreach($PMTenantsList as $ptl){
                                      $ptl_count++;
                                      $ptl_tenants[] = array('email' => $ptl['Email'], 'firstname' => $ptl['FirstName']);
                                      ?>
                                      <div class="tag_div">
                                      <button class="submitbtnImg <?=( $ptl['Email'] != '' ) ? 'green-btn':'grey-btn';?> email_variables_btn et_to_emails" id="btn_tag" data-email-to="<?=$ptl['Email']?>" type="button">
                                      <img class="inner_icon" src="images/left-arrow.png">
                                      (PM) <?=$ptl['FirstName']?><br />
                                      <span style="font-size:12px !important; margin-left:25px;">{pm_tenant_<?=$ptl_count?>}</span>
                                      </button>
                                      </div>
                                      <?php	}
                                      }
                                     */
                                    ?>

                                    <textarea name="tl_tenants_arr" style="display:none;"><?= serialize($tl_tenants) ?></textarea>
                                    <textarea name="ptl_tenants_arr" style="display:none;"><?= serialize($ptl_tenants) ?></textarea>

                                    <div class="tag_div">
                                        <button class=" btn submitbtnImg <?php echo ( $agency_emails_exp != '' ) ? 'green-btn' : 'grey-btn'; ?> email_variables_btn et_to_emails" id="btn_tag" type="button" data-email-to="<?php echo implode(';', $agency_emails_exp); ?>">
                                            Agency (General)
                                        </button>
                                    </div>
                                    <div class="tag_div">
                                        <button class="btn submitbtnImg <?php echo ( $account_emails_exp != '' ) ? 'green-btn' : 'grey-btn'; ?> email_variables_btn et_to_emails" id="btn_tag" type="button" data-email-to="<?php echo implode(';', $account_emails_exp); ?>">
                                            Agency (Accounts)
                                        </button>
                                    </div>
                                    <input type="hidden" id="email_to_target" value="et_to" />


                                    <h2 class="heading tag_header_div" style="margin-top: 0px;  margin-top: 35px;">Email Templates:</h2>
                                    <?php
                                    if (count($email_temp_list) > 0) {
                                        ?>
                                        <select class="form-control" name="et_id" id="email_template_select">
                                            <option value="">--- Select ---</option>
                                            <?php foreach ($email_temp_list as $email_temp) { ?>
                                                <option value="<?php echo $email_temp['email_templates_id'] ?>"><?php echo $email_temp['template_name'] ?></option>					
                                            <?php }
                                            ?>
                                        </select>
                                        <?php
                                    }
                                    ?>	


                                    <div id="bottom_text">
                                        TEXT HERE
                                    </div>

                                </div>





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
    <p>This page is used to send email templates.</p>

</div>
<!-- Fancybox END -->
<div style="display:none;">
    <div id="preview_email_temp_div">
        <table class="table">
            <tr>
                <td class="td_lbl">From:</td><td class="prev_et_from"></td>
            </tr>
            <tr>
                <td class="td_lbl">To:</td><td class="prev_et_to"></td>
            </tr>
            <tr>
                <td class="td_lbl">CC:</td><td class="prev_et_cc"></td>
            </tr>
            <tr>
                <td class="td_lbl">Subject:</td><td class="prev_et_subj"></td>
            </tr>
            <tr>
                <td class="td_lbl prev_et_body_lbl">Body:</td><td class="prev_et_body"></td>
            </tr>
        </table>
    </div>
</div>



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
    jQuery(document).ready(function () {


        // fancy box
        jQuery("#preview_email_temp_link").fancybox();


<?php
// display right panel, if email TO: exist
if ($this->input->get_post('to_email') != '') {
    ?>
            setTimeout(function () {

                jQuery("#et_to_icon").click();
                jQuery("#btn_et_send").removeClass("grey-btn");
                jQuery("#btn_et_send").addClass("blue-btn");

            }, 1000);
    <?php
}
?>



        jQuery(".et_attachment_checkbox").click(function () {

            jQuery(this).parents("div:first").find(".attachment_job_pdf").removeClass('fadeIt');

        });


        // change email to target script
        jQuery("#et_to, #et_cc").click(function () {

            var myid = jQuery(this).attr("id");
            jQuery("#email_to_target").val(myid);

        });

        jQuery("#et_to_icon").click(function () {

            var myid = jQuery(this).attr("data-target-id");
            jQuery("#email_to_target").val(myid);
            jQuery("#send_to_header").html("Send To:");
            jQuery("#send_to_div").show();

        });

        jQuery("#et_cc_icon").click(function () {

            var myid = jQuery(this).attr("data-target-id");
            jQuery("#email_to_target").val(myid);
            jQuery("#send_to_header").html("Send CC To:");
            jQuery("#send_to_div").show();

        });

        // toggle send To div
        jQuery("#et_subject, #et_body").click(function () {
            //jQuery("#send_to_div").hide();		
        });

        // repopulate email tags to field
        jQuery(".et_to_emails").click(function () {

            var email = jQuery(this).attr("data-email-to");
            var target = jQuery("#email_to_target").val();

            if (email != '') {
                jQuery("#" + target).val(email);
                jQuery("#btn_et_send").removeClass("grey-btn");
                jQuery("#btn_et_send").addClass("blue-btn");
            } else {
                jQuery("#" + target).val("");
                jQuery("#btn_et_send").removeClass("blue-btn");
                jQuery("#btn_et_send").addClass("grey-btn");
            }

        });


        // attachment toggle script
        jQuery("#et_attachment_icon").click(function () {

            jQuery("#et_attachment_hid_div").toggle();

        });


        // preview email templates
        jQuery("#btn_et_preview").click(function () {

            var obj = jQuery(this);
            var prev_btn = jQuery("#btn_et_preview_icon").html();
            var et_from = jQuery("#et_from").val();
            var to = jQuery("#et_to").val();
            var cc = jQuery("#et_cc").val();
            var subject = jQuery("#et_subject").val();
            var body = jQuery("#et_body").val();
            var body2 = body.replace(/(?:\r\n|\r|\n)/g, '<br />');
            var error = "";

            if (body == "") {
                error += " Select Template to preview\n ";
            }

            if (error != "") {
               swal({
                title: "Error!",
                text: error,
                type: "error",
                confirmButtonClass: "btn-danger"
            });
                return false;
            } else {

                // parse body tags
                jQuery("#load-screen").show();
                jQuery.ajax({
                    type: "POST",
                    url: "/email/preview_email_template_action_ajax",
                    data: {
                        subject: subject,
                        body: body2,
                        job_id: '<?php echo $this->input->get_post('job_id'); ?>'
                    },
                    dataType: 'json'
                }).done(function (ret) {

                    jQuery("#load-screen").hide();
                    //jQuery("#preview_email_temp_div").html(ret);
                    jQuery(".prev_et_from").html(et_from);
                    jQuery(".prev_et_to").html(to);
                    jQuery(".prev_et_cc").html(cc);
                    jQuery(".prev_et_subj").html(ret.subject);
                    jQuery(".prev_et_body").html(ret.body);
                });

            }

        });



        // send email templates
        jQuery("#jform").submit(function () {

            var et_from = jQuery("#et_from").val();
            var to = jQuery("#et_to").val();
            var body = jQuery("#et_body").val();
            var error = '';

            if (et_from == "") {
                error += "Email From is required\n";
            }

            if (to == "") {
                error += "Email To is required\n";
            }

            if (body == "") {
                error += "Email Body is required\n";
            }



            if (error != "") {
                alert(error);
                return false;
            } else {
                return true;
            }

        });


        // clear
        jQuery("#btn_et_clear").click(function () {

            window.location = "send_email_template.php?job_id=<?php echo $job_id ?>";

        });

        // load email template
        jQuery("#email_template_select").change(function () {

            var et_id = jQuery(this).val();

            jQuery("#load-screen").show();
            jQuery.ajax({
                type: "POST",
                url: "/email/get_email_template_by_id_action_ajax",
                data: {
                    et_id: et_id
                },
                dataType: 'json'
            }).done(function (ret) {

                var ptl_tenants = '<?= count($ptl_tenants) ?>';
                var i;
                var pmTenant = '';
                for (i = 1; i <= ptl_tenants; i++) {
                    pmTenant += "{pm_tenant_" + i + "}\n";
                }

                var tl_tenants = '<?= count($tl_tenants) ?>';
                var x;
                var activeTenant = '';
                for (x = 1; x <= tl_tenants; x++) {
                    activeTenant += "{tenant_" + x + "}\n";
                }

                // console.log(activeTenant);
                // console.log(pmTenant);

                // console.log(ret.body);

                var bodyEmail = ret.body;
                activeTenantTags = bodyEmail.replace('{active_tenants}', activeTenant);
                bodyEmailFiltered = activeTenantTags.replace('{pm_tenants}', pmTenant);

                jQuery("#load-screen").hide();
                jQuery("#email_temp_id").val(ret.email_templates_id);
                jQuery("#et_subject").val(ret.subject);
                jQuery("#et_body").val(bodyEmailFiltered);

                if (parseInt(et_id) == 35) { // "Permission to collect keys (email AGENT and TENANT)" template
                    jQuery("#bottom_text").show();
                    jQuery("#bottom_text").html("<strong style='color:red;'>YOU MUST CC AGENCY EMAIL</strong>");
                } else {
                    jQuery("#bottom_text").hide();
                    jQuery("#bottom_text").html("");
                }



            });

        });

    });
</script>