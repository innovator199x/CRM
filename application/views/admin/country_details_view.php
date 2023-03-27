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
            'title' => 'Countries',
            'status' => '',
            'link' => "/admin/countries"
        ),
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/admin/country_details/{$this->uri->segment(3)}"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);

    ?>

    <section>
      <?php //print_r($details); ?>
      <div class="row">
        <form method="POST" id="jform" action="<?php echo base_url(); ?>admin/updateCountry">
          <div class="col-sm-12">
            <section class="widget widget-reports">
            	<header class="widget-header widget-header-blue">
            		Country
            	</header>
            	<div class="widget-content">
            		<div class="form-row pt-2">
            			<div class="col-sm-6 col-lg-4">
            				<div class="form-group required">
            					<label for="country_name">Country Name</label>
            					<input type="text" name="name" id="name" class="form-control" data-validation="[NOTEMPTY]" value="<?php echo $details[0]->country; ?>">
                      <input type="hidden" name="country_id" value="<?php echo $details[0]->country_id; ?>">
            				</div>
            			</div>
            			<div class="col-sm-6 col-lg-4">
            				<div class="form-group">
            					<label class="form-control-label">Country Code</label>
            					<input type="text" class="form-control" id="iso" name="iso" data-validation="[NOTEMPTY]" value="<?php echo $details[0]->iso; ?>">
            				</div>
            			</div>
            		</div>
            	</div>
            </section>
            <section class="widget widget-reports">
            	<header class="widget-header widget-header-blue">
            		Phone
            	</header>
            	<div class="widget-content">
            		<div class="form-row pt-2">
            			<div class="col-sm-6 col-lg-4">
            				<div class="form-group required">
            					<label for="country-Agent">Agent Number</label>
            					<input type="text" name="agent_number" id="country-Agent" class="form-control" required="" data-validation="[NOTEMPTY]" value="<?php echo $details[0]->agent_number; ?>">
            				</div>
            			</div>
                  <div class="col-sm-6 col-lg-4">
                    <div class="form-group required">
                      <label for="country-Tenant">Tenant Number</label>
                      <input type="text" name="tenant_number" id="country-Tenant" class="form-control" required="" data-validation="[NOTEMPTY]" value="<?php echo $details[0]->tenant_number; ?>">
                    </div>
                  </div>
            		</div>
            	</div>
            </section>
            <section class="widget widget-reports">
            	<header class="widget-header widget-header-blue">
            		Legal
            	</header>
              <div class="widget-content">
                <div class="form-row pt-2">
                  <div class="col-sm-6 col-lg-4">
                    <div class="form-group required">
                      <label for="country-Trading">Trading Name</label>
                      <input type="text" name="trading_name" id="country-Trading" class="form-control" required="" data-validation="[NOTEMPTY]" value="<?php echo $details[0]->trading_name; ?>">
                    </div>
                  </div>
                  <div class="col-sm-6 col-lg-4 required">
                    <div class="form-group">
                      <label for="country-Address">Address</label>
                      <input type="text" name="company_address" id="country-Address" class="form-control" required="" data-validation="[NOTEMPTY]" value="<?php echo $details[0]->company_address; ?>">
                    </div>
                  </div>
                  <div class="col-sm-6 col-sm-offset-6 col-lg-4 required">
                    <div class="form-group">
                      <label for="country-Outgoingemail">Outgoing Email Address</label>
                      <input type="text" name="outgoing_email" id="country-Outgoingemail" class="form-control" required="" data-validation="[NOTEMPTY,EMAIL]" value="<?php echo $details[0]->outgoing_email; ?>">
                    </div>
                  </div>
                </div>
              </div>
            </section>
            <section class="widget widget-reports">
            	<header class="widget-header widget-header-blue">
            		Bank
            	</header>
              <div class="widget-content">
                <div class="form-row pt-2">
                  <div class="col-sm-6 col-lg-4">
                    <div class="form-group required">
                      <label for="country-Bank">Bank</label>
                      <input type="text" name="bank" id="country-Bank" class="form-control" required="" data-validation="[NOTEMPTY]" value="<?php echo $details[0]->bank; ?>">
                    </div>
                  </div>
                  <div class="col-sm-6 col-lg-4">
                    <div class="form-group required">
                      <label for="country-Acname">AC Name</label>
                      <input type="text" name="ac_name" id="country-Acname" class="form-control" required="" data-validation="[NOTEMPTY]" value="<?php echo $details[0]->ac_name; ?>">
                    </div>
                  </div>
                  <div class="col-sm-6 col-lg-4 required">
                    <div class="form-group">
                      <label for="country-Acnumber">AC Number</label>
                      <input type="text" name="ac_number" id="country-Acnumber" class="form-control" required="" data-validation="[NOTEMPTY]" value="<?php echo $details[0]->ac_number; ?>">
                    </div>
                  </div>
                  <div class="col-sm-6 col-lg-4 required">
                    <div class="form-group">
                      <label for="country-Abn">ABN</label>
                      <input type="text" name="abn" id="country-Abn" class="form-control" required="" data-validation="[NOTEMPTY]" value="<?php echo $details[0]->abn; ?>">
                    </div>
                  </div>
                  <div class="col-sm-6 col-sm-offset-6 col-lg-4 required">
                    <div class="form-group">
                      <label for="country-Bsb">BSB</label>
                      <input type="text" name="bsb" id="country-Bsb" class="form-control" required="" data-validation="[NOTEMPTY]" value="<?php echo $details[0]->bsb; ?>">
                    </div>
                  </div>
                </div>
              </div>
            </section>
            <section class="widget widget-reports">
            	<header class="widget-header widget-header-blue">
            		Social
            	</header>
              <div class="widget-content">
                <div class="form-row pt-2">
                  <div class="col-sm-6 col-sm-offset-6 col-lg-4 required">
                    <div class="form-group">
                      <label for="country-Web">Web</label>
                      <input type="text" name="web" id="country-Web" class="form-control" required="" data-validation="[NOTEMPTY]" value="<?php echo $details[0]->web; ?>">
                    </div>
                  </div>
                  <div class="col-sm-6 col-lg-4">
                    <div class="form-group required">
                      <label for="country-Facebook">Facebook</label>
                      <input type="text" name="facebook" id="country-Facebook" class="form-control" required="" data-validation="[NOTEMPTY]" value="<?php echo $details[0]->facebook; ?>">
                    </div>
                  </div>
                  <div class="col-sm-6 col-lg-4 required">
                    <div class="form-group">
                      <label for="country-Twitter">Twitter</label>
                      <input type="text" name="twitter" id="country-Twitter" class="form-control" required="" data-validation="[NOTEMPTY]" value="<?php echo $details[0]->twitter; ?>">
                    </div>
                  </div>
                  <div class="col-sm-6 col-sm-offset-6 col-lg-4 required">
                    <div class="form-group">
                      <label for="country-Instagram">Instagram</label>
                      <input type="text" name="instagram" id="country-Instagram" class="form-control" required="" data-validation="[NOTEMPTY]" value="<?php echo $details[0]->instagram; ?>">
                    </div>
                  </div>
                </div>
              </div>
            </section>
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary">Update</button>
          </div>
        </form>
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
      'name': 'Country Name',
      'iso': 'Country Code',
      'agent_number': 'Agent Number',
      'tenant_number': 'Tenant Number',
      'email_signature': 'Email Signature',
      'letterhead_footer': 'letterhead Footer',
      'trading_name': 'Trading Name',
      'company_address': 'Address',
      'outgoing_email': 'Outgoing Email Address',
      'bank': 'Bank',
      'ac_name': 'AC Name',
      'abn': 'ABN',
      'ac_number': 'AC Number',
      'bsb': 'BSB',
      'web': 'Web',
      'facebook': 'Facebook',
      'twitter': 'Twitter',
      'instagram': 'Instagram'
    }
	});
});
</script>
