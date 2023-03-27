<div class="box-typical box-typical-padding">
	<?php 
		// breadcrumbs template
		$bc_items = array(
            array(
				'title' => 'Reports',
				'link' => "/reports"
			),
            array(
				'title' => 'View Tools',
				'link' => "/vehicles/view_tools"
            ),
            array(
				'title' => 'Tool Details',
				'link' => "/vehicles/view_tool_details/{$this->uri->segment(3)}"
			),
			array(
				'title' => $title,
				'status' => 'active',
				'link' => "/vehicles/test_tag/{$tool_id}"
            )
            
		);
		$bc_data['bc_items'] = $bc_items;
		$this->load->view('templates/breadcrumbs', $bc_data);
	?>


	

	<section>
		<div class="body-typical-body" style="padding-top:25px;">


		<?php
            $form_attr = array(
                'id' => 'jform'
            );
            echo form_open("/vehicles/test_tag/{$tool_id}",$form_attr);
		?>

            <div class="col-md-5 columns">

                <div class="row form-group">
                
                        <label class="addlabel col-md-4 columns" style="margin-top:13px;">Date</label>
                        <div class="col-md-3 columns">
                            <input type="text"  class="flatpickr form-control flatpickr-input" name="date" id="date" value="<?php echo date('d/m/Y'); ?>" />
                        </div>
                   
                </div>

                <div class="row form-group">
                <label class="addlabel col-md-4">Test and tag completed</label>
                    <div class="col-md-8 columns">
                        <input type="radio" name="tnt_comp" value="1" style="width:auto; display:inline;" /> Yes &nbsp;
                        <input type="radio" name="tnt_comp" value="0" style="width:auto; display:inline;" /> No
                    </div>
                </div>
                <div class="row form-group">
                <label class="col-md-4">Comment</label>
                    <div class="col-md-8 columns">
                        <textarea class="addtextarea form-control" style="width: 328px; height: 150px; margin:0; padding: 7px;" name="comment"></textarea>
                    </div>
                </div>

                <div class="row form-group">
                    <label class="addlabel col-md-4 columns" style="color:red;margin-top:10px;">Next Inspection Due</label>
                    <div class="col-md-3 columns">
                        <input type="text"  class="form-control" name="inspection_due" id="inspection_due" value="<?php echo date('d/m/Y',strtotime("+ 6 months")); ?>" readonly="readonly" />
                    </div>
                </div>

                <div class="row" style="margin-top: 20px;margin-bottom:20px;">
                  <div class="col-md-12 columns">
                     <input type="hidden" name="tools_id" value="<?php echo $tool_id; ?>" />
                    <input type="submit" name="btn_add_test_tag" class="btn" id="btn_submit" style="float: left; width: auto;" value="Submit" />
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
   This page allows you to add Test and Tag.
	</p>

</div>
<!-- Fancybox END -->


<script type="text/javascript">

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



        // inspection due script
        jQuery("#date").change(function(){
            
            var date = formatToDateToYmd(jQuery(this).val());
            var insp_due = addMonth(new Date(date), 6);
            var insp_due2 = formatDate(insp_due);
            jQuery("#inspection_due").val(insp_due2);
            
        });
        


        



    })

</script>
