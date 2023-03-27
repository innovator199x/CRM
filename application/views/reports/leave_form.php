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
            'title' => 'Reports',
            'link' => "/reports"
        ),
        array(
            'title' => 'Leave Summary',
            'link' => "/users/leave_requests"
        ),
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/users/leave_form"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

	<section>
		<div class="body-typical-body">
            <?php echo form_open('/users/leave_form','id=leave_form'); ?>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Name<span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <select  class="form-control" name="employee" id="employee">
                            <option value="">Please select</option>
                            <?php
                            foreach($staff->result_array() as $row){
                            ?>
                            <option <?php echo ($row['StaffID'] == $this->session->staff_id)? "selected" : NULL ?> value="<?php echo $row['StaffID'] ?>"><?php echo "{$row['FirstName']} {$row['LastName']}" ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Type of Leave<span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <select  class="form-control" name="type_of_leave" id="type_of_leave">
                            <option value="">Please select</option>
                            <option value="1">Annual</option>	
                            <option value="2">Personal(sick)</option>
                            <option value="3">Personal(carer's)</option>
                            <option value="4">Compassionate</option>
                            <option value="5">Cancel Previous Leave</option>
                            <option value="-1">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row" id="backup_leave_div" style="display:none;">
                    <label class="col-sm-2 form-control-label">If you have no more sick leave, would you like to use <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <select  class="form-control" name="backup_leave" id="backup_leave">
                            <option value="">Please select</option>
                            <option value="1">Annual leave</option>	
				            <option value="2">Leave without pay</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">First Day of Leave <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <input  type="text" name="lday_of_work" id="lday_of_work" class="flatpickr form-control flatpickr-input" data-allow-input="true"/>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Last Day of Leave <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <input  type="text" name="fday_back" id="fday_back" class="flatpickr form-control flatpickr-input" data-allow-input="true"/>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Number of days <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <input  type="text" name="num_of_days" id="num_of_days" class="form-control"/>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Reason for Leave <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <textarea  class="form-control reason_for_leave" name="reason_for_leave" id="reason_for_leave"></textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Line Manager <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <select  class="form-control" name="line_manager" id="line_manager">
                            <option  value="">Please select</option>
                            <?php
                            foreach($staff->result_array() as $row){
                            ?>
                            <option value="<?php echo $row['StaffID'] ?>"><?php echo "{$row['FirstName']} {$row['LastName']}" ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">&nbsp;</label>
                    <div class="col-sm-3">
                        <div class="checkbox" style="margin:0;">
                            <input  name="confirm_chk" type="checkbox" id="confirm_chk">
                            <label for="confirm_chk">I understand that on submitting this form it is only a request for leave and leave is not granted until I receive confirmation from SATS </label>
                        </div>
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

<script type="text/javascript">


    jQuery(document).ready(function(){

        //success/error message sweel alert pop  start
        <?php if( $this->session->flashdata('status') &&  $this->session->flashdata('status') == 'success' ){?>
            swal({
                title: "Success!",
                text: "<?php echo $this->session->flashdata('success_msg') ?>",
                type: "success",
                confirmButtonClass: "btn-success"
            });
            unset($_SESSION);
        <?php }else if(  $this->session->flashdata('status') &&  $this->session->flashdata('status') == 'error'  ){ ?>
            swal({
                title: "Error!",
                text: "<?php echo $this->session->flashdata('error_msg') ?>",
                type: "error",
                confirmButtonClass: "btn-danger"
            });
            unset($_SESSION);
        <?php } ?>
        //success/error message sweel alert pop  end


        $('#type_of_leave').change(function(){
        if( $(this).val()==2 ){
            $('#backup_leave_div').show();
            $('#backup_leave').addClass('g_validate');
        }else{
            $('#backup_leave_div').hide();
            $('#backup_leave').val(""); //empty backup_leave dropdown
            $('#backup_leave').removeClass('g_validate');
        }
        })

        $('#leave_form').submit(function(){

            var error = "";
            var submitcount = 0;
            
            // Leave Request Form
            var employee = jQuery("#employee").val();
            var type_of_leave = jQuery("#type_of_leave").val();
            var lday_of_work = jQuery("#lday_of_work").val();
            var fday_back = jQuery("#fday_back").val();
            var num_of_days = jQuery("#num_of_days").val();
            var reason_for_leave = jQuery("#reason_for_leave").val();
            var line_manager = jQuery("#line_manager").val();
            var backup_leave = jQuery("#backup_leave").val();
            
            //date tweak
            var lday_of_work_a =  lday_of_work.split("/");
            var lday_of_work_new_date = new Date(lday_of_work_a[2], lday_of_work_a[1], lday_of_work_a[0]);
            
            var fday_back_a =  fday_back.split("/");
            var fday_back_a_new_date = new Date(fday_back_a[2], fday_back_a[1], fday_back_a[0]);
            

            if( employee == "" ){
                error += "Name is required\n";
            }
            if( type_of_leave == "" ){
                error += "Type of Leave is required\n";
            }
            if( lday_of_work == "" ){
                error += "Last Day of Work is required\n";
            }
            if( fday_back == "" ){
                error += "First Day Back is required\n";
            }
            if( fday_back_a_new_date < lday_of_work_new_date ){
                error += "Last Day of Leave must not be less than First Day of Leave\n";
            }
            if( num_of_days == "" ){
                error += "Number of days is required\n";
            }
            if( reason_for_leave == "" ){
                error += "Reason for Leave is required\n";
            }
            if( line_manager == "" ){
                error += "Line Manager is required\n";
            }

            if( jQuery("#backup_leave").hasClass('g_validate') && backup_leave==""){
                error += "If you have no more sick leave, would you like to use is required\n";
            }
            
            if(  jQuery("#confirm_chk").prop("checked")==false ){
                error += "Please tick the confirm box to proceed\n";
            }

            if(error!=""){
                swal('',error,'error');
                return false;
            }

            if(submitcount==0){
                submitcount++;
                $(this).submit();
                return false;
            }else{
                swal('','Submission in progress','error');
            }

        })

    });

   


</script>