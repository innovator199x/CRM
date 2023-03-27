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
            'title' => 'Booking Regions',
            'status' => '',
            'link' => "/admin/view_regions"
        ),
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/admin/edit_region/{$this->uri->segment(3)}"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);

    ?>

    <section>
      <?php //print_r($regions); ?>
      <div class="row">
        <form style="width: 100%" method="POST" id="jform" action="<?php echo base_url(); ?>admin/update_region">
          <div class="col-sm-12">
            <section class="widget widget-reports">
            	<header class="widget-header widget-header-blue">
            		Region Details
            	</header>
            	<div class="widget-content">
            		<div class="form-row pt-2">
            			<div class="col-sm-6 col-lg-6">
            				<div class="form-group required">
            					<label for="country_name">Region Name</label>
            					<input type="text" name="name" id="name" class="form-control" data-validation="[NOTEMPTY]" value="<?php echo $regions[0]->region_name; ?>">
                      <input type="hidden" name="region_id" value="<?php echo $regions[0]->regions_id; ?>">
            				</div>
            			</div>
            			<div class="col-sm-6 col-lg-6">
            				<div class="form-group">
                      <?php
                        if($country_id == 2){
                          $label = "District";
                        }
                        else{
                          $label = "State";
                        }
                      ?>
                      <label class="form-control-label"><?php echo $label; ?></label>
            				  <select class="form-control agency g_req" name="state" id="state" data-field="Agency">
                        <?php foreach($state as $row): 
                            $region_tpmid = $row->region_state;
                            $region_refid = $regions[0]->region_state;
                            if($region_tpmid == $region_refid){
                                $selected = 'selected="selected"';
                            }    
                            else{
                                $selected = '';
                            }
                        ?>
                            <option data-allow_pm="1" data-fg="22" data-load_api="1" value="<?php echo $row->region_state; ?>" <?php echo $selected?>>
                                <?php echo $row->region_state; ?>                                 
                            </option>
                        <?php endforeach; ?>
                        </select>
                    </div>
            			</div>
            		</div>
            	</div>
            </section>
          </div>
          <div class="col-sm-6 col-lg-12">
            <a href="<?php echo base_url(); ?>admin/delete_region/<?php echo $regions[0]->regions_id; ?>" class="btn btn-primary btn-danger">Delete</a>
            <button class="btn btn-primary btn-success" onclick="addSubregionname()">Add Subregion Name</button>
            <button style="float:right;" type="submit" class="btn btn-primary">Update</button>
          </div>
        </form>
      </div>
      <br />
      <!-- Edit Subregion Name -->
      <br />
      <div id="addsubregionname" style="display: none;">
        <div class="row">
          <form style="width: 100%" method="POST" id="jform" action="<?php echo base_url(); ?>admin/add_subregion">
            <div class="col-sm-12">
              <section class="widget widget-reports">
                <header class="widget-header widget-header-blue">
                  Add New Subregion
                </header>
                <div class="widget-content">
                  <div class="form-row pt-2">
                    <div class="col-sm-6 col-lg-6">
                      <div class="form-group">
                        <label class="form-control-label">Sub Region</label>
                        <input type="text" name="esubregion" id="esubregion" class="form-control" data-validation="[NOTEMPTY]" value=""/>
                        <input type="hidden" name="eregion_id" id="eregion" class="form-control" data-validation="[NOTEMPTY]" value="<?php echo $this->uri->segment(3); ?>"/>
                      </div>
                    </div>
                  </div>
                </div>
              </section>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-primary">Save</button>
            </div>
          </form>
        </div>
      </div>  
      <!-- END Edit Subregion Name -->
    </section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;">

    <h4><?php echo $title; ?></h4>
    <p>
    Use this form to edit an existing region.
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
      'name': 'Region Name',
      'state': 'State'
    }
	});
});
</script>

<script>
function addSubregionname() {
  var x = document.getElementById("addsubregionname");
  if (x.style.display === "block") {
    x.style.display = "none";
  } else {
    x.style.display = "block";
  }

}
</script>
