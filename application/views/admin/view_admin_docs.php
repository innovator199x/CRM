          
<style>
    .col-md-3{
        max-width:15.5%;
    }
    .action_a, .action_div {
        color: #adb7be!important;
    }
    .add_new_doc_link,.add_edit_header_link {
        display: none;
    }
    td > a > span.glyphicon-trash {
        color: #adb7be!important;
    }
    td > a > span.glyphicon-trash:hover {
        color: red !important;
    }
</style>

<div class="box-typical box-typical-padding">

    <?php
// breadcrumbs template
    $bc_items = array(
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/admin/view_admin_docs"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

    <header class="box-typical-header">

        <div class="box-typical box-typical-padding">
            <?php
           /* $form_attr = array(
                'id' => 'jform'
            );
            echo form_open('/admin/accomodation', $form_attr);
            */
            ?>
            <div class="for-groupss row">
                <div class="col-md-10 columns">
                    <div class="row">

                        <div class="col-md-3">
                            <button type="button" class="btn" id="new_doc" onclick="$('#add_new_doc_link').click();">Add New</button>
                        </div>	

                        <div class="col-md-3">
                            <button type="button" class="btn" id="new_doc" onclick="$('#add_edit_header_link').click();">Add/Edit Heading</button>
                        </div>	
                    </div>

                </div>
            </div>
           <!-- </form> -->
        </div>
    </header>


    <section>
        <div class="body-typical-body">

            <?Php
            foreach ($admin_docs as $headers) {
                $header_name = $headers['name'];
                $docs = $headers['docs'];
                if (!count($docs)) {
                    continue;
                }
                ?>
                <h2><?Php echo $header_name; ?></h2>
                <div class="table-responsive">
                    <table class="table table-hover main-table">
                        <thead>
                            <tr>
                                <th style="width:45%">Document Name</th>
                                <th style="width:45%">Title</th>
                                <th style="width:5%">Uploaded</th>
                                <th style="width:5%;text-align: center">Delete</th>
                            </tr>
                        </thead>

                        <tbody>                


                            <?Php
                            foreach ($docs as $row) {
                                ?>
                                <tr>
                                    <td>
                                        <input type="hidden" class="admin_doc_id" value="<?php echo $row['admin_documents_id']; ?>" />
                                        <input type="hidden" class="del_file" value="<?php echo $row['path']; ?>/<?php echo $row['filename']; ?>" />
                                        <?php
                                        if ((int) $row['type'] == 2) {
                                            $delete_link = "/admin/view_admin_docs/?delete=" . $row['admin_documents_id'];
                                            $delete_message = "Are you sure you want to delete?";

                                            $parsed = parse_url($row['url']);
                                            if (empty($parsed['scheme'])) {
                                                $urlStr = '//' . ltrim($row['url'], '/');
                                            } else {
                                                $urlStr = $row['url'];
                                            }
                                            ?>
                                            <a href="<?php echo $urlStr; ?>"><?php echo $row['url']; ?></a>

                                            <?php
                                        } else {
                                            $file = '' . $row['path'] . '/' . str_replace(' ', '_', $row['filename']);
                                            if (!file_exists($file)) {
                                                $country_folder = "/" . strtolower($this->gherxlib->get_country_iso());
                                                $folder = "admin_documents{$country_folder}";
                                                $file = $this->config->item("crm_link") . "/$folder/".$row['filename'];
                                                $delete_link = $this->config->item("crm_link") . "/admin_doc.php";
                                                $delete_message = "This entry has been created using the old version and can only be deleted using the old version. You will be redirected to the old version to perform this action. do you want to continue?";
                                            } else {
                                                $file = "/$file";
                                                $delete_link = "/admin/view_admin_docs/?delete=" . $row['admin_documents_id'] . "&file=" . $file;
                                                $delete_message = "Are you sure you want to delete?";
                                            }
                                            ?>
                                            <a href="<?Php echo $file ?>"><?php echo $row['filename']; ?></a>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo $row['title'] ?></td>							
                                    <td><?php echo date("d/m/Y", strtotime($row['date'])) ?></td>
                                    <td style="text-align: center">
                                        <a data-message="<?Php echo $delete_message; ?>" href="<?Php echo $delete_link; ?>" class="btn_del_vf btn_delete"><span class="glyphicon glyphicon-trash"></span></a>
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
        This page allows you to add and delete internal SATS documents
    </p>

</div>
<!-- Fancybox END -->
<!-- Fancybox Start -->
<a href="javascript:;" id="add_new_doc_link" class="add_new_doc_link" data-fancybox data-src="#add_new_doc">Trigger the fancybox</a>							
<div id="add_new_doc" class="fancybox" style="display:none;" >

    <h4>Add new internal doc</h4>
    <div class="row">
        <div style="padding-top: 20px; display: block;" id="div_staff" class="addproperty formholder div_add_res">


            <ul style="list-style: outside none none; margin-bottom: 25px;">
                <li>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="upload_option" id="res_opt_1" value="1">
                        <label class="form-check-label" for="res_opt_1">
                            Upload File
                        </label>
                    </div>

                </li>
                <li>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="upload_option" id="res_opt_2" value="2">
                        <label class="form-check-label" for="res_opt_2">
                            Upload Link
                        </label>
                    </div>
                </li>
            </ul>



            <!-- UPLOAD FILE FORM -->
            <form id="form_admin_document" method="post" action="/admin/add_internal_doc_action_form_submit" enctype="multipart/form-data" style="display:none;">
                <div class="row">
                    <div class="col-md-12">
                        <label class="addlabel" for="title">Heading</label>
                        <select name="header" class="form-control">
                            <option value="">----</option>
                            <?Php
                            foreach ($admin_docs as $header_id => $value) {
                                ?>
                                <option value="<?Php echo $header_id ?>"><?Php echo $value['name']; ?></option>
                                <?Php
                            }
                            ?>
                        </select>
                    </div>
                </div> 
                <div class="row">
                    <div class="col-md-12">
                        <label class="addlabel" for="file">File</label>
                        <input type="file" name="file" id="file" class="fname uploadfile submitbtnImg form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label class="addlabel" for="title">Title</label>
                        <input type="text" name="title" id="title" class="fname form-control">
                    </div>
                </div>         				
                <div style="padding-top: 15px; text-align:left;" class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn" id="btn_upload">Upload</button>
                    </div>
                </div>
            </form>
            <!-- UPLOAD FILE FORM END -->

            <!-- UPLOAD LINK FORM -->
            <form id="form_admin_doc_upload_link" method="post" action="/admin/add_internal_doc_link_action_form_submit" enctype="multipart/form-data" style="display: none;">
                <div class="row">
                    <div class="col-md-12">
                        <label class="addlabel" for="heading">Heading</label>
                        <select name="header" class="form-control">
                            <option value="">----</option>
                            <?Php
                            foreach ($admin_docs as $header_id => $value) {
                                ?>
                                <option value="<?Php echo $header_id ?>"><?Php echo $value['name']; ?></option>
                                <?Php
                            }
                            ?>
                        </select>           
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label class="addlabel" for="file">URL</label>
                        <input type="text" name="url" id="url" class="url form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label class="addlabel" for="title">Title</label>
                        <input type="text" name="title" id="title_link" class="title form-control">
                    </div>
                </div>

                <div style="padding-top: 15px; text-align:left;" class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn" id="btn_upload_link">Upload</button>
                    </div>
                </div>
            </form>
            <!-- UPLOAD LINK FORM END -->


        </div>
    </div>

</div>
<!-- Fancybox END -->

<!-- Fancybox Start -->
<a href="javascript:;" id="add_edit_header_link" class="add_edit_header_link" data-fancybox data-src="#add_edit_header">Trigger the fancybox</a>							
<div id="add_edit_header" class="fancybox" style="display:none;" >

    <h4> Add/Edit heading </h4>
    <div class="row">
        <div style="padding-top: 20px; display: block;" id="div_staff" class="addproperty formholder div_add_res">
            <div class="add_header_container" style="display: none">
                <form method="post" action="/admin/add_internal_doc_header_action_form_submit">
                    <div class="row">
                        <div class="col-md-12">
                            <label>New Header</label>
                            <input type="text" name="header_name" class="form-control" />
                        </div>
                    </div>
                    <div class="row" style="margin-top: 10px">
                        <div class="col-md-12">
                            <button class="btn" type="submit">Add</button>
                            <button class="btn btn-danger edit-header-btn"type="button">Back</button>
                        </div>
                    </div>

                </form>
            </div>
            <div class="edit_header_container">
                <form method="post" action="/admin/edit_internal_doc_header_action_form_submit">
                    <style>#pm_table tr td{ border: 1px solid transparent;}</style>
                    <button class="btn add-header-btn" type="button">Add Header</button>
                    <table id="pm_table" style="width: auto; margin-top: 15px; margin-bottom: 15px;">
                        <tbody>
                            <?php foreach ($admin_docs as $header => $value) { ?>
                                <tr>
                                    <td>
                                        <input type="text" class="fname pm_name form-control" name="edit_name[<?Php echo $header ?>]" value="<?php echo $value['name']; ?>">	
                                    </td>
                                    <td>
                                        <button type="submit" name="delete" value="<?Php echo $header ?>" class="submitbtnImg btn btn_del_sr">X</button>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>										
                        </tbody>	
                    </table>
                    <?php if (count($admin_docs) > 0) { ?>
                        <input type="submit" class="submitbtnImg blue-btn btn" name="btn_update_sr btn" value="Update" />
                        <?php
                    }
                    ?>					
                    <button class="btn-danger btn" onclick="parent.$.fancybox.close();">Close</button>
                </form>
            </div>

        </div>
    </div>

</div>
<!-- Fancybox END -->


<script type="text/javascript">

    jQuery(document).ready(function () {
        jQuery("#res_opt_1").click(function () {

            jQuery("#form_admin_document").slideDown();
            jQuery("#form_admin_doc_upload_link").slideUp();
        });
        jQuery("#res_opt_2").click(function () {

            jQuery("#form_admin_doc_upload_link").slideDown();
            jQuery("#form_admin_document").slideUp();
        });
        $('.add-header-btn').click(function () {
            $('.add_header_container').show();
            $('.edit_header_container').hide();
        });
        $('.edit-header-btn').click(function () {
            $('.add_header_container').hide();
            $('.edit_header_container').show();
        });

        jQuery(".btn_delete").click(function () {
            var message = $(this).data()['message'];
            if (confirm(message)) {
                return true;
            } else {
                return false;
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
