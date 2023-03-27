
<style>
    .col-mdd-3{
        max-width:15.5%;
    }
    .action_a, .action_div {
        color: #adb7be!important;
    }
    .btn_add_alarm{margin-top:13px;}
    #add_alarm_form{margin-top:30px;}
</style>

<div class="box-typical box-typical-padding">

    <?php 
    // breadcrumbs template
    $bc_items = array(
        array(
            'title' => 'Alarm Guide',
            'status' => 'active',
            'link' => "/admin/alarm_guide"
        ),
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/admin/add_alarm"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);

    ?>
	
	<section>
		<div class="body-typical-body">
            <?php echo form_open_multipart('/admin/add_alarm','id=add_alarm_form'); ?>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Make <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <input type="text" class="form-control" name="make" id="make">
                    </div>
                </div>

                 <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Model <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <input type="text" class="form-control" name="model" id="model">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Power Type <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <select  class="form-control power_type" name="power_type" id="power_type">
                            <option  value="">Please select</option>
                            <option value="1">3v</option>
                            <option value="2">3vli</option>
                            <option value="3">9v</option>
                            <option value="4">9vli</option>
                            <option value="5">240v</option>
                            <option value="6">240vli</option>
                        </select>
                    </div>
                </div>

                 <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Detection Type <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <select  class="form-control detection_type" name="detection_type" id="detection_type">
                            <option  value="">Please select</option>
                            <option value="1">Photo-Electric</option>
				            <option value="2">Ionisation</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Expiry/ Manufacture Date <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <select  class="form-control expiry_manuf_date" name="expiry_manuf_date" id="expiry_manuf_date">
                            <option value="">Please select</option>
                            <option value="1">Expiry</option>
				            <option value="0">Manufacture</option>
                        </select>
                    </div>
                </div>

                 <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Location of Date <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <input type="text" class="form-control loc_of_date" name="loc_of_date" id="loc_of_date">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Removable Battery <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <select  class="form-control remove_battery" name="remove_battery" id="remove_battery">
                            <option  value="">Please select</option>
                            <option value="1">Yes</option>
				            <option value="0">No</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Hush Button <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <select  class="form-control hush_button" name="hush_button" id="hush_button">
                            <option  value="">Please select</option>
                            <option value="1">Yes</option>
				            <option value="0">No</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Common faults <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <textarea  class="form-control common_faults" name="common_faults" id="common_faults"></textarea>
                    </div>
                </div>

                 <div class="form-group row">
                    <label class="col-sm-2 form-control-label">How to Remove Alarm <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <textarea  class="form-control how_to_rem_al" name="how_to_rem_al" id="how_to_rem_al"></textarea>
                    </div>
                </div>

                 <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Additional Notes <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <textarea  class="form-control adntl_notes" name="adntl_notes" id="adntl_notes"></textarea>
                    </div>
                </div>

                 <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Front Image <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <input type="file" class="form-control" name="front_image" id="front_image" capture="camera" accept="image/*">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Rear Image 1 <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <input type="file" class="form-control" name="rear_image_1" id="rear_image_1" capture="camera" accept="image/*">
                    </div>
                </div>

                 <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Rear Image 2 <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <input type="file" class="form-control" name="rear_image_2" id="rear_image_2" capture="camera" accept="image/*">
                    </div>
                </div>

                  <div class="form-group row">
                    <label class="col-sm-2 form-control-label">&nbsp;</label>
                    <div class="col-sm-3 text-right">
                        <input type="submit" class="btn" id="btn_add_alarm" name="btn_add_alarm" value="Submit">
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
    This page allows you to add new smoke alarm.
	</p>

</div>
<!-- Fancybox END -->


<script type="text/javascript">

  jQuery(document).ready(function(){

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

       $(".fancybox_btn").fancybox({
            hideOnContentClick: false,
            hideOnOverlayClick: false
        });

        $('#add_alarm_form').submit(function(){
            
            var make = $('#make').val();
            var model = $('#model').val();
            var power_type = $('#power_type').val();
            var detection_type = $('#detection_type').val();
            var expiry_manuf_date = $('#expiry_manuf_date').val();
            var loc_of_date = $('#loc_of_date').val();
            var remove_battery = $('#remove_battery').val();
            var hush_button = $('#hush_button').val();
            var common_faults = $('#common_faults').val();
            var how_to_rem_al = $('#how_to_rem_al').val();
            var adntl_notes = $('#adntl_notes').val();
            var front_image = $('#front_image').val();
            var rear_image_1 = $('#rear_image_1').val();
            var rear_image_2 = $('#rear_image_2').val();

            var err = "";
            var submitcount=0;

            if(make==""){
                err+="Make must not be empty\n";
            }
            if(model==""){
                err+="Model must not be empty\n";
            }
            if(power_type==""){
                err+="Power Type must not be empty\n";
            }
            if(detection_type==""){
                err+="Detection Type must not be empty\n";
            }
            if(expiry_manuf_date==""){
                err+="Expirty / Manufacture Date must not be empty\n";
            }
            if(loc_of_date==""){
                err+="Location of Date must not be empty\n";
            }
            if(remove_battery==""){
                err+="Removable Battery must not be empty\n";
            }
            if(hush_button==""){
                err+="Hush Button must not be empty\n";
            }
            if(common_faults==""){
                err+="Common faults must not be empty\n";
            }
            if(how_to_rem_al==""){
                err+="How to Remove Alarm must not be empty\n";
            }
            if(adntl_notes==""){
                err+="Additional Notes must not be empty\n";
            } 
            if(front_image==""){
                err+="Front Image must not be empty\n";
            }
            if(rear_image_1==""){
                err+="Rear Image 1 must not be empty\n";
            }
            if(rear_image_2==""){
                err+="Rear Image 2 must not be empty\n";
            }
            

            if(err!=""){
                swal('',err,'error');
                return false;
            }

            if(submitcount==0){
                submitcount++;
                $(this).submit();
                return false;
            }else{
                swal('','Form submit still in progress','error');
            }

        });



  })


</script>
