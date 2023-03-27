
<div class="box-typical box-typical-padding">
<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => 'Reports',
			'link' => "/reports"
        ),
        array(
			'title' => 'Region Numbers',
			'link' => "/reports/region_numbers"
		),
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/reports/edit_region/".$region_id
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>
    <section>
    <div class="body-typical-body" style="padding-top:30px;">
      
          
            <?php echo form_open('/reports/edit_region/'.$region_id, 'id=edit_main_region_form'); ?>

            <div class="row">
                <div class="col-md-12 col-lg-5 columns">
                        <div class="form-group row">
                            <label class="col-md-3 form-control-label"><?php echo  $this->gherxlib->getDynamicRegion($this->config->item('country')) ?></label>
                            <select class="form-control col-md-9" name="region_name">
                                <option value="">Please select</option>
                                <?php 
                                foreach($lists_region->result_array() as $row){
                                ?>
                                    <option <?php echo  ( $row['regions_id']==$lists->row()->region )?'selected="true"':NULL ?> value="<?php echo $row['regions_id'] ?>"><?php echo $row['region_name'] ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 form-control-label">Sub Region</label>
                            <input class="form-control col-md-9" tye="text" name="sub_region" value="<?php echo $lists->row()->postcode_region_name ?>">
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 form-control-label">Postcodes <br/><small>(Seperate with commas)</small></label>
                            <?php
                                    foreach($lists->result_array() as $postcode_row){
                                        $pc[] = $postcode_row['postcode_region_postcodes'];
                                    }
                                ?>
                                
                            <textarea class="form-control col-md-9" name="postcode"><?php echo implode(",",$pc); ?></textarea>
                        </div>
                        <div class="form-group row">
                        <label class="col-md-3 form-control-label">&nbsp;</label>
                            <input class="btn" type="submit" name="btn_save_region" value="Save Region">
                        </div>
                </div>
            </div>

            </form>

      
    </div>
    </section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

<h4><?php echo $title; ?></h4>
<p>
Lorem ipsum
</p>

</div>
<!-- Fancybox END -->





<script>
jQuery(document).ready(function(){

	//success/error message sweel alert pop  start
	<?php if( $this->session->flashdata('status') &&  $this->session->flashdata('status') == 'success' ){?>
		swal({
			title: "Success!",
			text: "<?php echo $this->session->flashdata('success_msg') ?>",
			type: "success",
			confirmButtonClass: "btn-success",
			showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
            timer: <?php echo $this->config->item('timer') ?>
		});
	<?php }else if(  $this->session->flashdata('status') &&  $this->session->flashdata('status') == 'error'  ){ ?>
		swal({
			title: "Error!",
			text: "<?php echo $this->session->flashdata('error_msg') ?>",
			type: "error",
			confirmButtonClass: "btn-danger"
		});
	<?php } ?>
	//success/error message sweel alert pop  end



});
</script>