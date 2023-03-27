<style>
    .col-mdd-3{
        max-width:20%;
    }
    #leave_form{
        margin-top:50px;
    }
    .flatpickr{width:100%!important}
    .tticon{
        font-size: 18px;
    }
    .hide_div{
        display:none;
    }
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
            'link' => "/users/leave_details/{$this->uri->segment(3)}"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

	<section>
		<div class="body-typical-body">
            <?php echo form_open('/users/update_leave_details/'.$this->uri->segment(3),'id=leave_details_form'); ?>
                <h4 class="m-t-lg with-border">Leave Request Form</h4>
                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Date<span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <input type="text" readonly="true" name="date" id="date" value="<?php echo $this->system_model->formatDate($row['date'],'d/m/Y') ?>" class="form-control">
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Name<span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <select  class="form-control" name="employee" id="employee">
                            <option value="">Please select</option>
                            <?php
                            foreach($staff->result_array() as $row_staff){
                            ?>
                            <option <?php echo ($row_staff['StaffID'] == $row['emp_staff_id'])? "selected" : NULL ?> value="<?php echo $row_staff['StaffID'] ?>"><?php echo "{$row_staff['FirstName']} {$row_staff['LastName']}" ?></option>
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
                            <option <?php echo ($row['type_of_leave']==1)? 'selected' : NULL ?> value="1">Annual</option>	
                            <option <?php echo ($row['type_of_leave']==2)? 'selected' : NULL ?> value="2">Personal(sick)</option>
                            <option <?php echo ($row['type_of_leave']==3)? 'selected' : NULL ?> value="3">Personal(carer's)</option>
                            <option <?php echo ($row['type_of_leave']==4)? 'selected' : NULL ?> value="4">Compassionate</option>
                            <option <?php echo ($row['type_of_leave']==5)? 'selected' : NULL ?> value="5">Cancel Previous Leave</option>
                            <option <?php echo ($row['type_of_leave']==-1)? 'selected' : NULL ?> value="-1">Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="<?php echo ($row['type_of_leave']!=2) ? 'hide_div' :NULL ?>" id="backup_leave_div">
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">If you have no more sick leave, would you like to use <span class="text-red">*</span></label>
                        <div class="col-sm-3">
                            <select  class="form-control <?php echo ($row['type_of_leave']==2)?'g_validate':NULL; ?>" name="backup_leave" id="backup_leave">
                                <option value="">Please select</option>
                                <option  <?php echo ($row['backup_leave']==1)? 'selected' : NULL ?> value="1">Annual leave</option>	
                                <option  <?php echo ($row['backup_leave']==2)? 'selected' : NULL ?> value="2">Leave without pay</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">First Day of Leave <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <input data-allow-input="true"  type="text" name="lday_of_work" id="lday_of_work" class="flatpickr form-control flatpickr-input" value="<?php echo $this->system_model->formatDate($row['lday_of_work'],'d/m/Y') ?>"/>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Last Day of Leave <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <input data-allow-input="true"  type="text" name="fday_back" id="fday_back" class="flatpickr form-control flatpickr-input" value="<?php echo $this->system_model->formatDate($row['fday_back'],'d/m/Y') ?>"/>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Number of days <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <input  type="text" name="num_of_days" id="num_of_days" class="form-control" value="<?php echo $row['num_of_days'] ?>"/>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Reason for Leave <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <textarea  class="form-control reason_for_leave" name="reason_for_leave" id="reason_for_leave"><?php echo $row['reason_for_leave'] ?></textarea>
                    </div>
                </div>
                
                <h4 class="m-t-lg with-border">Office Use Only</h4>
                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Line Manager <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <select  class="form-control" name="line_manager" id="line_manager">
                            <option  value="">Please select</option>
                            <?php
                            foreach($staff->result_array() as $row_lm){
                            ?>
                            <option <?php echo ($row_lm['StaffID'] == $row['sa_lm_staff_id'])? "selected" : NULL ?> value="<?php echo $row_lm['StaffID'] ?>"><?php echo "{$row_lm['FirstName']} {$row_lm['LastName']}" ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">HR Approved </label>
                    <div class="col-sm-3">
                        <?php 
                        // timestamp
                        $hlclass = '';
                        $timestamp_str = '';
                        $updated_by = '';
                        if( is_numeric($row['hr_app']) && $row['hr_app']==1 ){
                            $hlclass = 'text-green';
                            $timestamp_str = ($this->system_model->isDateNotEmpty($row['hr_app_timestamp']))? date('d/m/Y H:i',strtotime($row['hr_app_timestamp'])) : NULL;
                            $updated_by = "{$row['hra_fname']} {$row['hra_lname']}";
                        }else if( is_numeric($row['hr_app']) && $row['hr_app']==0 ){
                            $hlclass = 'text-red';
                            $timestamp_str = ($this->system_model->isDateNotEmpty($row['hr_app_timestamp']))? date('d/m/Y H:i',strtotime($row['hr_app_timestamp'])) : NULL;
                            $updated_by = "{$row['hra_fname']} {$row['hra_lname']}";
                        }
                        ?>
                        <div class="row">
                            <div class="col-md-2 columns">
                                <div class="radio">
                                    <input type="radio" class="chkbox hr_app" name="hr_app" id="radio-1" value="1" <?php echo ( is_numeric($row['hr_app']) && $row['hr_app']==1 )?'checked="checked"':NULL; ?>>
                                    <label for="radio-1">Yes </label>
                                </div>
                            </div>
                            <div class="col-md-2 columns">
                                <div class="radio">
                                    <input type="radio" class="chkbox hr_app" name="hr_app" id="radio-0" value="0" <?php echo ( is_numeric($row['hr_app']) && $row['hr_app']==0 )?'checked="checked"':NULL; ?>>
                                    <label for="radio-0">No </label>
                                </div>
                            </div>
                            <div class="col-md-8 columns">
                                <small class="<?php echo $hlclass; ?>"><?php echo "{$timestamp_str} {$updated_by}"; ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Line Manager Approved </label>
                    <div class="col-sm-3">
                        <?php 
                        // timestamp
                        $hlclass = '';
                        $timestamp_str = '';
                        $updated_by = '';
                        if( is_numeric($row['line_manager_app']) && $row['line_manager_app']==1 ){
                            $hlclass = 'text-green';
                            $timestamp_str = ($this->system_model->isDateNotEmpty($row['line_manager_app_timestamp']))? date('d/m/Y H:i',strtotime($row['line_manager_app_timestamp'])) : NULL;
                            $updated_by = "{$row['lma_fname']} {$row['lma_lname']}";
                        }else if( is_numeric($row['line_manager_app']) && $row['line_manager_app']==0 ){
                            $hlclass = 'text-red';
                            $timestamp_str = ($this->system_model->isDateNotEmpty($row['line_manager_app_timestamp']))? date('d/m/Y H:i',strtotime($row['line_manager_app_timestamp'])) : NULL;
                            $updated_by = "{$row['lma_fname']} {$row['lma_lname']}";
                        }
                        ?>
                        <div class="row">
                            <div class="col-md-2 columns">
                                <div class="radio">
                                    <input type="radio" class="chkbox line_manager_app" name="line_manager_app" id="line_manager_app-1" value="1" <?php echo ( is_numeric($row['line_manager_app']) && $row['line_manager_app']==1 )?'checked="checked"':NULL; ?>>
                                    <label for="line_manager_app-1">Yes </label>
                                </div>
                            </div>
                            <div class="col-md-2 columns">
                                <div class="radio">
                                    <input type="radio" class="chkbox line_manager_app" name="line_manager_app" id="line_manager_app-0" value="0" <?php echo ( is_numeric($row['line_manager_app']) && $row['line_manager_app']==0 )?'checked="checked"':NULL; ?>>
                                    <label for="line_manager_app-0">No </label>
                                </div>
                            </div>
                            <div class="col-md-8 columns">
                                <small class="<?php echo $hlclass; ?>"><?php echo "{$timestamp_str} {$updated_by}"; ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                 <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Added to Calendar </label>
                    <div class="col-sm-3">
                        <?php 
                        // timestamp
                        $hlclass = '';
                        $timestamp_str = '';
                        $updated_by = '';
                        if( is_numeric($row['added_to_cal']) && $row['added_to_cal']==1 ){
                            $hlclass = 'text-green';
                            $timestamp_str = date('d/m/Y H:i',strtotime($row['added_to_cal_timestamp']));
                            $updated_by = "{$row['atc_fname']} {$row['atc_lname']}";
                        }else{
                            if($row['added_to_cal_timestamp']!=""){
                                $hlclass = 'text-red';
                                //$timestamp_str = date('d/m/Y H:i',strtotime($row['added_to_cal_timestamp']));
                                //$updated_by = "{$row['atc_fname']} {$row['atc_lname']}";
                                $timestamp_str = null;
                                $updated_by = null;
                            }				
                        }
                        ?>
                        <div class="row">
                            <div class="col-md-4 columns">
                                <div class="checkbox">
                                    <input type="checkbox" style="height: auto;" class="addinput chkbox added_to_cal" name="added_to_cal" id="added_to_cal" value="1" <?php echo ( is_numeric($row['added_to_cal']) && $row['added_to_cal']==1 )?'checked="checked"':NULL; ?> />
                                    <label for="added_to_cal">&nbsp;&nbsp;</label>
                                    <a href="/calendar/add_new_entry"><span class="fa fa-calendar tticon"></span></a>
                                    <input type="hidden" name="added_to_cal_changed" id="added_to_cal_changed" value="<?php echo $row['added_to_cal'] ?>" />
                                </div>
                            </div>
                            <div class="col-md-8 columns">
                                <small class="<?php echo $hlclass; ?>"><?php echo "{$timestamp_str} {$updated_by}"; ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Staff notified in writing </label>
                    <div class="col-sm-3">
                        <?php 
                        // timestamp
                        $hlclass = '';
                        $timestamp_str = '';
                        $updated_by = '';
                        if( is_numeric($row['staff_notified']) && $row['staff_notified']==1 ){
                            $hlclass = 'text-green';
                            $timestamp_str = date('d/m/Y H:i',strtotime($row['staff_notified_timestamp']));
                            $updated_by = "{$row['sn_fname']} {$row['sn_lname']}";
                        }else{
                            if($row['staff_notified_timestamp']!=""){
                                $hlclass = 'text-red';
                                //$timestamp_str = date('d/m/Y H:i',strtotime($row['staff_notified_timestamp']));
                                //$updated_by = "{$row['sn_fname']} {$row['sn_lname']}";				
                                $timestamp_str = null;
                                $updated_by = null;	
                            }				
                        }

                        //get login staff for MAIL TO
                        $staff_params = array('sel_query' => 'sa.FirstName, sa.LastName','staff_id'=>$this->session->staff_id);
                        $staff_row = $this->gherxlib->getStaffInfo($staff_params)->row_array();

                        // MAIL TO
                        $mail_to_subject = 'Leave request';
                        $mail_to_cc = "{$row['lm_email']}";
                        $employee_name = "{$row['emp_fname']} {$row['emp_lname']}";
                        $logged_user = "{$staff_row['FirstName']} {$staff_row['LastName']}";

                        $mailto_body = "Hi {$employee_name}
                        
                        Your leave request has been approved!

                        First Day of leave: {$this->system_model->formatDate($row['lday_of_work'],'d/m/Y')}
                        Last day of Leave: {$this->system_model->formatDate($row['fday_back'],'d/m/Y')}
                        Number of Days: {$row['num_of_days']}
                        Reason for leave: {$row['reason_for_leave']}

                        Regards,

                        {$logged_user}
                        ";
                        ?>
                        <div class="row">
                            <div class="col-md-4 columns">
                                <div class="checkbox">
                                    <input type="checkbox" class="addinput chkbox staff_notified" name="staff_notified" id="staff_notified" value="1" <?php echo ( is_numeric($row['staff_notified']) && $row['staff_notified']==1 )?'checked="checked"':NULL; ?>>
                                    <label for="staff_notified">&nbsp;&nbsp;</label>
                                    <a href="mailto:<?php echo $row['emp_email']; ?>?cc=<?php echo $mail_to_cc; ?>&Subject=<?php echo $mail_to_subject; ?>&body=<?php echo rawurlencode($mailto_body); ?>">
                                        <span class="fa fa-envelope tticon"></span>
                                    </a>
                                    <input type="hidden" name="staff_notified_changed" id="staff_notified_changed" value="<?php echo $row['staff_notified'] ?>" />
                                </div>
                            </div>
                            <div class="col-md-8 columns">
                                <small class="<?php echo $hlclass; ?>"><?php echo "{$timestamp_str} {$updated_by}"; ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                 <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Comments </label>
                    <div class="col-sm-3">
                        <textarea class="form-control comments" name="comments" id="comments"><?php echo $row['comments']; ?></textarea>
                    </div>
                </div>

                 <div class="form-group row">
                    <label class="col-sm-2 form-control-label">&nbsp;</label>
                    <div class="col-sm-3 text-right">
                    <input type="submit" class="btn" id="btn_update_leave" name="btn_update_leave" value="Update">
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
	<p>This page displays the details and progress of a leave request</p>

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

        $('#leave_details_form').submit(function(){

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

    });




</script>