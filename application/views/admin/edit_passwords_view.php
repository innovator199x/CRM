<div class="box-typical box-typical-padding">
    <?php
    if( validation_errors() ){ ?>
        <div class="alert alert-danger">
        <?php echo validation_errors(); ?>
        </div>
    <?php } ?>
    <style>
      .flatpickr {
        width: 100% !important;
      }
    </style>

    <?php
    // breadcrumbs template
    $bc_items = array(
        array(
            'title' => 'Passwords',
            'status' => '',
            'link' => "/admin/passwords"
        ),
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/admin/edit_passwords/{$this->uri->segment(3)}"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

    <section>
      <?php //print_r($account); ?>
      <div class="row">
        <form style="width: 100%" method="POST" id="jform" action="<?php echo base_url(); ?>admin/update_passwords">
          <div class="col-sm-12">
            <section class="widget widget-reports">
            	<header class="widget-header widget-header-blue">
            		Account Details
            	</header>
            	<div class="widget-content">
              <?php foreach($account as $item): ?>
            		<div class="form-row pt-2">
                  <div class="col-sm-6 col-lg-4">
                      <div class="form-group">
                          <label class="form-control-label">Website</label>
                          <input type="text" class="form-control" id="website" name="website" value="<?php echo $item->website; ?>">
                          <input type="hidden" class="form-control" id="password_id" name="password_id" value="<?php echo $account['0']->site_accounts_id; ?>">
                      </div>
                  </div>
                  <div class="col-sm-6 col-lg-4">
                      <div class="form-group">
                          <label class="form-control-label">Email</label>
                          <input type="text" class="form-control" id="email" name="email" value="<?php echo $item->email; ?>">
                      </div>
                  </div>
                  <div class="col-sm-6 col-lg-4">
                      <div class="form-group">
                          <label class="form-control-label">Username</label>
                          <input type="text" class="form-control" id="username" name="username" data-validation="[NOTEMPTY]" value="<?php echo $item->username; ?>">
                      </div>
                  </div>
                  <div class="col-sm-6 col-lg-4">
                      <div class="form-group">
                          <label class="form-control-label">Password</label>
                          <input type="text" class="form-control" id="password" name="password" data-validation="[NOTEMPTY]" value="<?php echo $item->password; ?>">
                      </div>
                  </div>
            			<div class="col-sm-6 col-lg-4">
            				<div class="form-group">
            					<label class="form-control-label">Expired Date</label>                   
            					<input style="width: 200px;" name="expired_date" class="flatpickr form-control flatpickr-input active" data-allow-input="true" id="flatpickr" type="text" value="<?php echo ( $this->system_model->isDateNotEmpty($item->expiry_date) )?date('d/m/Y',strtotime($item->expiry_date)):null; ?>">
            				</div>
            			</div>
                  <div class="col-sm-6 col-lg-4">
                      <div class="form-group required">
                          <label for="country_name">Status</label>
                          <select name="status" id="stats" class="form-control" data-validation="[NOTEMPTY]">
                              <option value="" disabled>Select Status</option>
                              <option value="1" <?php echo ($item->status == 1? 'selected' : ''); ?>>Active</option>
                              <option value="0" <?php echo ($item->status == 0? 'selected' : ''); ?>>Inactive</option>
                          </select>
                      </div>
            			</div>
            		</div>
                <div class="form-row pt-2">
                <div class="col-md-12">
            				<div class="form-group required">
            					<label for="country_name">Notes</label>
            					<textarea name="notes" id="notes" rows="4" class="form-control"><?php echo $item->notes; ?></textarea>
            				</div>
            			</div>
                </div>
              <?php endforeach; ?>
            	</div>
            </section>
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary">Submit</button>
          </div>
        </form>
      </div>
    </section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;">

    <h4><?php echo $title; ?></h4>
    <p>
    This page is used to edit an existing password.
    </p>

</div>
<!-- Fancybox END -->

<style>
  .form-tooltip-error{right: 100px !important;}
</style>

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

<script>
jQuery(document).ready(function(){
  // jquery form validation
	jQuery('#jform').validate({
		submit: {
			settings: {
				inputContainer: '.form-group',
				errorListClass: 'form-tooltip-error'
			}
		},
    labels: {
      'expired_date': 'Expired Date'
    }
	});
});
</script>
