<div class="box-typical box-typical-padding">
<style>
    .font-icon-trash{
        color: #adb7be !important;
    }

    .font-icon-trash:hover{
        color: #e74c3c !important;
    }
    .proj-page-section {
        width: 150px;
    }
    </style>
    <?php
    // breadcrumbs template
    $bc_items = array(
        array(
            'title' => 'Passwords',
            'status' => 'active',
            'link' => "/admin/passwords"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>
    <section>
        <div class="body-typical-body">
            <div class="box-typical box-typical-padding">
                <!-- <div class="for-groupss row"> -->
                    <form id="jform" action="/admin/passwords">
                        <div class="row">
                            <div class="col-md-8 columns">                            
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="status_label">&nbsp;</label>
                                            <select name="status" id="status_filter" class="form-control" required>
                                                <option value="" disabled <?php echo ($this->input->get_post('status') ? '': 'selected');  ?>>Select Status</option>
                                                <option value="all" <?php echo ($this->input->get_post('status') == 'all' ? 'selected': '');  ?>>All</option>
                                                <option value="active" <?php echo ($this->input->get_post('status') == 'active' ? 'selected': '');  ?>>Active</option>
                                                <option value="inactive" <?php echo ($this->input->get_post('status') == 'inactive' ? 'selected': '');  ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>                            
                            </div>
                            <div class="col-md-4 columns">
                                <section class="proj-page-section float-right">
                                    <div class="proj-page-attach">
                                        <i class="fa fa-file-excel-o"></i>
                                        <p class="name"><?php echo $title; ?></p>
                                        <p>
                                            <a href="<?php echo $export_link_params ?>?export=1&status=<?= $this->input->get_post('status') ?>">
                                                Export
                                            </a>
                                        </p>
                                    </div>
                                </section>
                            </div>
                        </div>                        
                    </form>
                <!-- </div> -->
            </div>
            <div class="table-responsive">
                <table class="table table-hover main-table">
                    <thead>
                        <tr>
                            <th>Website</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Note</th>
                            <th>Status</th>
                            <th>Expiry Date</th>
                            <th>Last Upload</th>
                            <th>Edit</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                      <?php //print_r($regions); ?>
                        <?php foreach($accounts as $item): ?>
                        <tr>
                            <td>
                                <span class="txt_lbl"><?php echo $item->website; ?></span>
                            </td>
                            <td>
                                <span class="txt_lbl"><?php echo $item->email; ?></a></span>
                            </td>
                            <td>
                                <span class="txt_lbl"><?php echo $item->username; ?></a></span>
                            </td>
                            <td>
                                <span class="txt_lbl"><?php echo $item->password; ?></span>
                            </td>
                            <td>
                                <span class="txt_lbl"><?php echo $item->notes; ?></span>
                            </td>
                            <td>
                                <span class="txt_lbl"><?php echo ($item->status == 1? 'Active' : 'Inactive'); ?></span>
                            </td>
                            <td>
                                <span class="txt_lbl"><?php echo ( $this->system_model->isDateNotEmpty($item->expiry_date) )?date('d/m/Y',strtotime($item->expiry_date)):null; ?></span>
                            </td>
                            <td>
                                <span class="txt_lbl"><?php echo ( $this->system_model->isDateNotEmpty($item->last_updated) )?date('d/m/Y',strtotime($item->last_updated)):null; ?></span>
                            </td>
                            <td>
                                <a href='<?php echo "/admin/edit_passwords/".$item->site_accounts_id ?>' data-toggle="tooltip" title="" class="btn_edit action_a" data-original-title="Edit"><i class="font-icon font-icon-pencil"></i></a>
                            </td>
                            <td>
                                <a href='<?php echo "/admin/delete_passwords/".$item->site_accounts_id ?>' data-toggle="tooltip" title="" class="btn_delete_action_a" data-original-title="Delete"><i class="font-icon font-icon-trash"></i></a>
                            </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $links; ?></nav>
		    <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
            <button class="btn btn-inline"><a style="color: #fff;" href="<?php echo base_url(); ?>admin/add_passwords"><span class="fa fa-plus"></span> Add Website/Email</a></button>
        </div>
    </section>
</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;">

    <h4><?php echo $title; ?></h4>
    <p>
    Use this page to store company accounts and passwords for future reference.
    </p>

</div>
<!-- Fancybox END -->

<script>
$('document').ready(function(event) {

  // initAutocomplete();

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

    // filter status
    jQuery("#status_filter").change(function(){

        var status_filter_dom = jQuery(this);
        var status_filter = status_filter_dom.val();

        if( status_filter != '' ){

            jQuery("#jform").submit();

        }        

    });

});
</script>
