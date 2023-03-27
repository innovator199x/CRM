<div class="box-typical box-typical-padding">

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
                <div class="for-groupss row">
                    <div class="col-md-8 columns">
                        <div class="row">
                            <div class="col-md-2">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <button class="btn btn-inline"><a style="color: #fff;" href="<?php echo base_url(); ?>admin/add_passwords">Add Website/Email</a></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover main-table">
                    <thead>
                        <tr>
                            <th>Address</th>
                            <th>Agency</th>
                            <th>Edit</th>
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
                                <a href='<?php echo "/admin/edit_passwords/".$item->site_accounts_id ?>' data-toggle="tooltip" title="" class="btn_edit action_a" data-original-title="Edit"><i class="font-icon font-icon-pencil"></i></a>
                            </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
          <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $links; ?></nav>
			    <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
        </div>
    </section>
</div>

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

});
</script>
