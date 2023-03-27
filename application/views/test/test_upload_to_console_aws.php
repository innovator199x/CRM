<style>
    .col-mdd-3{
        max-width:20%;
    }
    #leave_form{
        margin-top:50px;
    }
    .flatpickr{width:100%!important}
</style>
<div class="box-typical box-typical-padding">

	<?php 
    // breadcrumbs template
    $bc_items = array(
        array(
            'title' => $title,
            'status' => 'active',
            'link' => $uri
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

	<section>
		<div class="body-typical-body">
            <?php echo form_open_multipart('/console/test_upload_to_console_aws','id=console_aws_upload_form'); ?>

             

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Upload <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <input  type="file" name="file" id="file" class="form-control"/>
                    </div>
                </div>               

                 <div class="form-group row">
                    <label class="col-sm-2 form-control-label">&nbsp;</label>
                    <div class="col-sm-3 text-right">
                    <input type="submit" class="btn" id="btn_add_leave" name="btn_add_leave" value="Submit">
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
    Lorem ipsum...
	</p>

</div>
<!-- Fancybox END -->