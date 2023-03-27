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
            'link' => "/admin/edit_subregion/{$this->uri->segment(3)}/{$this->uri->segment(4)}"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);

    ?>

    <section>
      <?php //print_r($subregions); ?>
      <div class="row">
        <form style="width: 100%;" method="POST" id="jform" action="<?php echo base_url(); ?>admin/update_subregion">
          <div class="col-sm-12">
            <section class="widget widget-reports">
            	<header class="widget-header widget-header-blue">
            		Region Details
            	</header>
            	<div class="widget-content">
            		<div class="form-row pt-2">
            			<div class="col-sm-6 col-lg-3">
            				<div class="form-group required">
            					<label for="country_name">Region Name</label>
            					<select class="form-control agency g_req" name="region" id="region" data-field="Agency">
                        <?php foreach($regions as $item): 
                            $region_tpmid = $item->regions_id;
                            $region_refid = $region_id;
                            if($region_tpmid == $region_refid){
                                $selected = 'selected="selected"';
                            }    
                            else{
                                $selected = '';
                            }
                        ?>
                            <option data-allow_pm="1" data-fg="22" data-load_api="1" value="<?php echo $item->regions_id; ?>" <?php echo $selected?>>
                                <?php echo $item->region_name; ?>                                 
                            </option>
                        <?php endforeach; ?>
                        </select>
            				</div>
            			</div>
            			<div class="col-sm-6 col-lg-3">
            				<div class="form-group">
            					<label class="form-control-label">Sub Region</label>
                      <select class="form-control agency g_req" name="subregion_id" id="subregion_id" data-field="Agency">
                        <?php foreach($subregion_name as $row): 
                            $region_tpmid = $row->sub_region_id;
                            $region_refid = $subregion_id_selected;
                            if($region_tpmid == $region_refid){
                                $selected = 'selected="selected"';
                            }    
                            else{
                                $selected = '';
                            }
                        ?>
                            <option data-allow_pm="1" data-fg="22" data-load_api="1" value="<?php echo $row->sub_region_id; ?>" <?php echo $selected?>>
                                <?php echo $row->subregion_name; ?>                                 
                            </option>
                        <?php endforeach; ?>
                        </select>
                    </div>
            			</div>
                        <div class="col-sm-6 col-lg-6">
            				<div class="form-group">
            					<label class="form-control-label">Postcodes</label>
                      <?php 
                        $i = 0;
                        $tmp_length = count($postcodes);
                        $length = $tmp_length - 1;
                      ?>
                      <?php
                        if(!empty($_SESSION['postcodes'])){
                          $pcode = $_SESSION['postcodes'];
                          $tp_len = count($pcode);
                          $pcode_len = $tp_len - 1;
                        ?>
                          <textarea name="postcode" id="notes" rows="6" class="form-control" data-validation="[NOTEMPTY]"><?php foreach($pcode as $key):if($i == $pcode_len){echo trim($pcode[$i]);}else{echo trim($pcode[$i]).", ";}$i++;endforeach; ?></textarea>
                        <?php }else{ ?>
                        <textarea name="postcode" id="notes" rows="6" class="form-control" data-validation="[NOTEMPTY]"><?php foreach($postcodes as $key):if($i == $length){echo trim($key->postcode);}else{echo trim($key->postcode).", ";}$i++;endforeach; ?></textarea>
                        <?php } ?>
                    </div>
            			</div>
            		</div>
            	</div>
            </section>
          </div>
          <div class="col-12">
            <a style="color: #fff;" href="<?php echo base_url(); ?>admin/delete_subregion/<?php echo $subregion_id_selected; ?>"><button type="button" class="btn btn-danger">Delete</button></a>
            <button class="btn btn-primary btn-success" onclick="editSubregionname()">Edit Subregion Name</button>
            <button style="float: right;" type="submit" class="btn btn-primary">Update</button>
          </div>
        </form>
      </div>
      <!-- Edit Subregion Name -->
      <br />
      <div id="editsubregionname" style="display: none;">
        <div class="row">
          <form style="width: 100%" method="POST" id="jform" action="<?php echo base_url(); ?>admin/edit_subregionname">
            <div class="col-sm-12">
              <section class="widget widget-reports">
                <header class="widget-header widget-header-blue">
                  Edit Subregion Name
                </header>
                <div class="widget-content">
                  <div class="form-row pt-2">
                    <div class="col-sm-6 col-lg-4">
                      <div class="form-group">
                        <label class="form-control-label">Sub Region</label>
                        <select class="form-control agency g_req" name="subregion_id" id="esubregion_id" data-field="Agency" onchange="myFunction()">
                          <?php foreach($subregion_name as $row): ?>
                            <option data-allow_pm="1" data-fg="22" data-load_api="1" value="<?php echo $row->sub_region_id; ?>">
                              <?php echo $row->subregion_name; ?>                                 
                            </option>
                          <?php endforeach; ?>
                          </select>
                      </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-control-label">Edit Sub Region Name</label>
                      <div class="form-group">
                        <input type="text" name="esubregion" id="esubregion" class="form-control" data-validation="[NOTEMPTY]" value=""/>
                        <input type="hidden" name="eid" id="eid" class="form-control" data-validation="[NOTEMPTY]" value=""/>
                        <input type="hidden" name="segment3" id="eid" class="form-control" data-validation="[NOTEMPTY]" value="<?php echo $this->uri->segment(3); ?>"/>
                        <input type="hidden" name="segment4" id="eid" class="form-control" data-validation="[NOTEMPTY]" value="<?php echo $this->uri->segment(4); ?>"/>
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
    Use this form to edit an existing sub region, and add or remove postcodes.
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
      'region': 'Region Name',
      'subregion': 'Subregion Name',
      'postcode': 'Postcode'
    }
	});
});
</script>

<script>
function editSubregionname() {
  var x = document.getElementById("editsubregionname");
  if (x.style.display === "block") {
    x.style.display = "none";
  } else {
    x.style.display = "block";
  }

  var select = document.getElementById('subregion_id');
  value = select.options[select.selectedIndex].value;
  document.getElementById("esubregion_id").value = value;

  var select1 = document.getElementById('subregion_id');
  value1 = select1.options[select.selectedIndex].text;
  document.getElementById("esubregion").value = value1;

  var select2 = document.getElementById("esubregion_id").value;
  document.getElementById("eid").value = select2;

}
</script>

<script>
function myFunction() {
  var x = document.getElementById("esubregion_id");
  var xy = document.getElementById("esubregion_id").value;
  var y = x.options[x.selectedIndex].text
  console.log(xy);
  //document.getElementById("esubregion").innerHTML = "You selected: " + x;
  document.getElementById("esubregion").value = y;
  document.getElementById("eid").value = xy;
}
</script>
