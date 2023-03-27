<style>
#adjustment_request_div h4{
    margin-top: 70px;
}

#adjustment_request_div .refund_request_div,
#adjustment_request_div .refund_details_div,
#adjustment_request_div #btn_submit{
    display: none;
}
.checkbox .chk_lbl {
    position: relative;
    top: 4px;
}
#send_to{
    height: 300px;
}
</style>
<div class="box-typical box-typical-padding">
	<?php 
	// breadcrumbs template
	$bc_items = array(
        array(
			'title' => 'Messages',
			'status' => 'active',
			'link' => '/messages'
		),
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
		<div class="body-typical-body" style="padding-top:25px;">


        <?php 
        if( validation_errors() ){ ?>
            <div class="alert alert-danger">
            <?php echo validation_errors(); ?>
            </div>
        <?php
        }	
        ?>


		<?php
            $form_attr = array(
                'id' => 'jform'
            );
            echo form_open($uri,$form_attr);
		?>

        <div class="row" id="adjustment_request_div">
            <div class="col-md-12 col-lg-6 columns">
            
    
            <div class="form-group row">
                <label class="col-sm-5 form-control-label">Send To <span class="color-red">*</span></label>
                <div class="col-sm-7">
                    <p class="form-control-static">
                    <select name="send_to[]" id="send_to" class="form-control send_to" data-validation="[NOTEMPTY]" multiple>
                        <?php
                        foreach( $staff_sql->result() as $staff_row ){ ?>
                            <option value="<?php echo $staff_row->StaffID; ?>">
                                <?php echo $this->system_model->formatStaffName($staff_row->FirstName,$staff_row->LastName); ?>
                            </option>
                        <?php                       
                        }
                        ?>
                    </select>
                    </p>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-5 form-control-label">Message <span class="color-red">*</span></label>
                <div class="col-sm-7">
                    <textarea name="message" id="message" rows="4" class="form-control" data-validation="[NOTEMPTY]"></textarea>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-5 form-control-label"></label>
                <div class="col-sm-7 text-center">
                <button type="submit" class="btn btn_send">Send</button>

                </div>
            </div>


            </form>

            
                        

            </div>
		</div>


		</div>
	</section>

</div>



<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
		The big brown fox, jumps over the lazy dog
	</p>

</div>
<!-- Fancybox END -->

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
			'send_to[]': 'Send To',
			'message': 'Message'
		}
	});

});
</script>