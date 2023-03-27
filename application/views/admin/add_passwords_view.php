<div class="box-typical box-typical-padding">
    <?php
    if( validation_errors() ){ ?>
        <div class="alert alert-danger">
        <?php echo validation_errors(); ?>
        </div>
    <?php
    }
    ?>

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
            'link' => "/admin/add_passwords"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);

    ?>

    <section>
      <?php //print_r($subregions); ?>
      <div class="row">
        <form style="width: 100%" method="POST" id="jform" action="<?php echo base_url(); ?>admin/add_passwords">
          <div class="col-sm-12">
            <section class="widget widget-reports">
            	<header class="widget-header widget-header-blue">
            		Account Details
            	</header>
            	<div class="widget-content">
            		<div class="form-row pt-2">
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group">
                                <label class="form-control-label">Website</label>
                                <input type="text" class="form-control" id="website" name="website" value="">
                            </div>
            			</div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group">
                                <label class="form-control-label">Email</label>
                                <input type="text" class="form-control" id="email" name="email" value="">
                            </div>
            			</div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group">
                                <label class="form-control-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" data-validation="[NOTEMPTY]" value="">
                            </div>
            			</div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group">
                                <label class="form-control-label">Password</label>
                                <input type="text" class="form-control" id="password" name="password" data-validation="[NOTEMPTY]" value="">
                            </div>
            			</div>
            			<div class="col-sm-6 col-lg-4">
            				<div class="form-group required">
            					<label for="country_name">Notes</label>
            					<textarea name="notes" id="notes" rows="4" class="form-control"></textarea>
            				</div>
            			</div>
            			<div class="col-sm-6 col-lg-4">
            				<div class="form-group">
            					<label class="form-control-label">Expiry Date</label>
            					<input style="width: 200px;" name="expired_date" class="flatpickr form-control flatpickr-input active" data-allow-input="true" id="flatpickr" type="text" value="">
            				</div>
            			</div>
            		</div>
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
    This page is used to add a new password.
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
      'expired_date': 'Expiry Date'
    }
	});
});
</script>
