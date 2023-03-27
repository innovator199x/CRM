          
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
    td.has-delete {
        text-align: center;
    }
    label.statelabel {
        display: initial;
    }
    input.states {
        margin-left: 10px;
    }
</style>

<div class="box-typical box-typical-padding">

    <?php
// breadcrumbs template
    $bc_items = array(
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/sales/view_sales_document"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

    <header class="box-typical-header">

        <div class="box-typical box-typical-padding">
            <div class="for-groupss row">
                <div class="col-md-10 columns">
                    <div class="row">

                        <div class="col-md-3">
                            <button type="button" class="btn" id="new_doc" onclick="$('#add_new_doc_link').click();">Add New</button>
                        </div>	
                    </div>

                </div>
            </div>

        </div>
    </header>


    <section>
        <div class="body-typical-body">

            <h2><?Php //echo $header_name;                    ?></h2>
            <div class="table-responsive">
                <table class="table table-hover main-table">
                    <thead>
                        <tr>
                            <th style="width:40%">Document Name</th>
                            <th style="width:30%">Title</th>
                            <th style="width:10%">State</th>
                            <th style="width:10%">Uploaded</th>
                            <th style="width:15%;text-align: center">Delete</th>
                        </tr>
                    </thead>

                    <tbody>                


                        <?Php
                        foreach ($docs as $row) {
                            ?>
                            <tr class="body_tr jalign_left">
                                <td>
                                    <?Php
                                    $file = '' . $row['path'] . '/' . str_replace(' ', '_', $row['filename']);
                                    if (!file_exists($file)) {
                                        $country_folder = "/" . strtolower($this->gherxlib->get_country_iso());
                                        $folder = "sales_documents{$country_folder}";
                                        $file = $this->config->item("crm_link") . "/$folder/" . $row['filename'];
                                        $delete_link = $this->config->item("crm_link") . "/sales_documents.php";
                                        $delete_message = "This entry has been created using the old version and can only be deleted using the old version. You will be redirected to the old version to perform this action. do you want to continue?";
                                    } else {
                                        $file = "/$file";
                                        $delete_link = "/sales/delete_sales_document_action_form_submit/?sales_document_id=" . $row['sales_documents_id'] . "&file=" . $file;
                                        $delete_message = "Are you sure you want to delete?";
                                    }
                                    ?>
                                    <a href="<?Php echo $file ?>"><?php echo $row['filename']; ?></a>
                                </td>
                                <td><?php echo $row['title'] ?></td>
                                <td>
                                    <?php
                                    if ($row['states'] != "") {
                                        $s_arr = array();
                                        $state_ids = explode(",", $row['states']);
//                                        var_dump($states_def);
                                        foreach ($state_ids as $s_id) {
                                            foreach ($states_def as $states) {

                                                if ((int) $states['StateID'] === (int) $s_id) {
                                                    $s_arr[] = $states['state'];
                                                }
                                            }
                                        }
                                        echo implode(', ', $s_arr);
                                    } else {
                                        echo "N/A";
                                    }
                                    ?>
                                </td>
                                <td><?php echo date("d/m/Y", strtotime($row['date'])) ?></td>
                                <td class="has-delete">
                                    <a data-message="<?Php echo $delete_message; ?>" data-href="<?Php echo $delete_link; ?>" class="btn_del_vf btn_delete"><span class="glyphicon glyphicon-trash"></span></a>
                                </td>
                            <tr>

                                <?php
                                $i++;
                            }
                            ?>
                    </tbody>

                </table>
            </div>

        </div>
    </section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

    <h4><?php echo $title; ?></h4>
    <p>
        This page allows you to add and delete Sales Documents
    </p>

</div>
<!-- Fancybox END -->
<!-- Fancybox Start -->
<a href="javascript:;" id="add_new_doc_link" class="add_new_doc_link" data-fancybox data-src="#add_new_doc">Trigger the fancybox</a>							
<div id="add_new_doc" class="fancybox" style="display:none;" >

    <h4>Add Sales Document</h4>
    <div class="row">
        <div style="padding-top: 20px; display: block;" id="div_staff" class="addproperty formholder div_add_res">
            <!-- UPLOAD FILE FORM -->
            <form id="form_sales_document" method="post" action="/sales/add_sales_document_action_form_submit" enctype="multipart/form-data">
                <div class="row">
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
                <?php
                if (count($states_def)) {
                    ?>
                    <div class="row">
                        <div class="col-md-12">
                            <label class="addlabel">States</label>
                            <div class="vsud-inner">
                                <? foreach ($states_def as $data) { ?>
                                    <input type="checkbox"  name="states[]" class="states" value="<?php echo $data['StateID']; ?>">
                                    <label for="<?php echo $data['StateID']; ?>" class="statelabel"><?php echo $data['state']; ?></label>  
                                <? } ?>
                            </div>
                        </div>

                    </div>
                    <?php
                }
                ?>                
                <div style="padding-top: 15px; text-align:left;" class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn" id="btn_upload">Upload</button>
                    </div>
                </div>
            </form>
            <!-- UPLOAD FILE FORM END -->

        </div>
    </div>

</div>
<!-- Fancybox END -->


<script type="text/javascript">

    jQuery(document).ready(function () {
        jQuery(".btn_delete").click(function (e) {
            var btnDelete = $(this);
            var message = $(this).data()['message'];
            swal({
                title: "Warning!",
                text: message,
                type: "warning",
                showCancelButton: true,
                cancelButtonText: "Cancel!",
                confirmButtonClass: "btn-success",
                cancelButtonClass: "btn-danger",
                confirmButtonText: "Yes"
            }, function (isConfirm) {

                if (!isConfirm) {
                    return false;
                } else {
                    location = btnDelete.data()['href']
                    $('#load-screen').show(); //show loader
                }

            });
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
