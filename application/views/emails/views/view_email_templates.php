          
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
    #form_search{
        float: right;
    }

    .dataTables_filter{
        float: right;
    }

    .nav-item{
        width: 50%;
    }
</style>

<div class="box-typical box-typical-padding">

    <?php
// breadcrumbs template
    $edit_permission = false;
    if($class_id == 2 || $class_id == 3 || $class_id == 9 || $class_id == 10){
        $edit_permission = true;
    }


    $bc_items = array(
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/email/view_email_templates"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

    <?php if($edit_permission == false){ ?>
    <div class="alert alert-warning">
        <strong>Please Note;</strong> You don't have permission to Add/Edit Email Templates.
    </div>
    <?php } ?>

        <div class="tabs-section-nav tabs-section-nav-icons">
            <div class="tbl">
                <ul class="nav" role="tablist">
                    <li class="nav-item">
                        <!-- <a class="nav-link active" data-toggle="tab" href="#home"> -->
                        <a class="nav-link <?= $tab == "template" ? "active show" : "" ?>" href="/email/view_email_templates/template">
                            <span class="nav-link-in"><i class="fa fa-info"></i> Templates</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <!-- <a class="nav-link" data-toggle="tab" href="#menu1"> -->
                        <a class="nav-link <?= $tab == "logs" ? "active show" : "" ?>" href="/email/view_email_templates/logs">
                            <span class="nav-link-in"><i class="fa fa-list"></i> Logs</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Tab panes -->
        <div class="tab-content">
            <?php
			if (in_array($tab, ["template", "logs"])):
                $this->load->view("emails/views/tabs/{$tab}");
			else:
                $this->load->view("emails/views/tabs/template");
            endif; 
            ?>
        </div>
</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

    <h4><?php echo $title; ?></h4>
    <p>
        This page allows you to view Email TEmplates
    </p>

</div>
<!-- Fancybox END -->
<!-- Fancybox Start -->
<a href="javascript:;" id="add_new_doc_link" class="add_new_doc_link" data-fancybox data-src="#add_new_doc">Trigger the fancybox</a>							

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
