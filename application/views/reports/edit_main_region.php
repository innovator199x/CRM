
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
			'link' => "/reports/edit_main_region/".$region_id
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>
    <section>
    <div class="body-typical-body" style="padding-top:30px;">
      
          
            <?php echo form_open('/reports/edit_main_region/'.$region_id, 'id=edit_main_region_form'); ?>

            <div class="row">
                <div class="col-md-12 col-lg-5 columns">
                        <div class="form-group row">
                            <label class="col-md-3 form-control-label"><?php echo  $this->gherxlib->getDynamicRegion($this->config->item('country')) ?> Name</label>
                            <input class="form-control col-md-9" tye="text" name="region_name" value="<?php echo $region['region_name'] ?>">
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 form-control-label"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')) ?></label>
                            <input class="form-control col-md-9" tye="text" name="state" value="<?php echo $region['region_state'] ?>">
                        </div>
                        <div class="form-group row">
                        <label class="col-md-3 form-control-label">&nbsp;</label>
                            <input class="btn" type="submit" name="btn_edit_region" value="Update">
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