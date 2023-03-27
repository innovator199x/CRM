

<style>
    .flatpickr{
        width:155px;
    }
</style>
<div style="width:450px;">


<?php

echo ($cal_id!="")?"<h3>Edit Entry</h3>":"<h3>Add New Entry</h3>"; ?>


<?php echo form_open('/calendar/add_calendar_entry_static') ?>

    <div class="form-group">
        <input type="hidden" name="cal_id" value="<?php echo $row_cal['calendar_id'] ?>">
        <input type="hidden" name="cal_staff_id" value="<?php echo $row_cal['staff_id'] ?>">
        <label>Staff *</label>

        <?php if($cal_id!=""){ // cal_id not null > edit lightbox ?>

        <select id="staff_id" name="staff_id" class="form-control">
            <option value="">Please Select</option>
            <?php
                foreach($staff_list->result_array() as $row){
            ?>
                    <option <?php echo ( $row_cal['staff_id'] ==  $row['StaffID'] )?'selected':'' ?> value="<?php echo $row['StaffID'] ?>"><?php echo "{$row['FirstName']} {$row['LastName']}" ?></option>
            <?php 
                }
            ?>
        </select>

        <?php }else{ // cal_id is empty > add lightbox ?>

            <select class="form-control" name='staff_id[]' id='staff_id' multiple='multiple' style='height: 150px;'>
                <option value='-1' id='all_staff_dp' style='color: red;'>ALL STAFF</option>
                <?php
                foreach($staff_list->result_array() as $row){
                ?>
                        <option <?php echo ( $row_cal['staff_id'] ==  $row['StaffID'] )?'selected':'' ?> value="<?php echo $row['StaffID'] ?>"><?php echo "{$row['FirstName']} {$row['LastName']}" ?></option>
                <?php 
                    }
                ?>
            </select>
            
        <?php } ?>

    </div>


    <div class="form-group">
        <label>Start Date *</label>
        <input name="start_date" data-enable-time="true" data-time_24hr="true" class="flatpickr form-control flatpickr-input" data-allow-input="true"  type="text" value="<?php echo $start_date_data ?>">
    </div>

    <div class="form-group">
        <label>Finish Date</label>
        <input name="finish_date" data-enable-time="true" data-time_24hr="true" class="flatpickr form-control flatpickr-input" data-allow-input="true"  type="text" value="<?php echo $finish_date_data ?>">
    </div>
    <?php
        $class_id = $this->system_model->getStaffClassID();
    // add == 1 for the add_new_entry page only
    if ($add == 1) {
        // global access
        if ($class_id == 2) {
        ?>

        <div class="form-group" id="div_leave" style="display: none;">
            <label>Region / Type of Leave *</label>
            <input type="text" class="form-control" name="leave_type" value="<?php echo $row_cal['region'] ?>">
        </div>
        <?php } else { ?>
        <div class="form-group" id="div_leave" style="display: none;">
            <label>Region / Type of Leave *</label>
            <select name="leave_type" id="leave_type" class="form-control" onchange="other_leave_type(this.value)">
                <option <?php if($row_cal['region'] == 'Sick') { echo 'selected';} ?> value="Sick">Sick</option>
                <option <?php if($row_cal['region'] == 'Annual Leave') { echo 'selected';} ?> value="Annual Leave">Annual Leave</option>
                <option <?php if($row_cal['region'] == 'LWOP') { echo 'selected';} ?> value="LWOP">LWOP</option>
                <option <?php if($row_cal['region'] == 'Toil') { echo 'selected';} ?> value="Toil">Toil</option>
                <option <?php if($row_cal['region'] == 'Bday Leave') { echo 'selected';} ?> value="Bday Leave">Bday Leave</option>
                <option <?php if($row_cal['region'] == 'WorkCover') { echo 'selected';} ?> value="WorkCover">WorkCover</option>
                <option <?php if($row_cal['region'] == 'Other') { echo 'selected';} ?> value="Other">Other</option>
                <option <?php if($row_cal['region'] != 'Sick' || $row_cal['region'] != 'Annual Leave' || $row_cal['region'] != 'LWOP' || $row_cal['region'] != 'Toil' || $row_cal['region'] != 'Bday Leave' || $row_cal['region'] != 'WorkCover' || $row_cal['region'] != 'Other') { echo 'selected';} ?> value="<?php echo $row_cal['region']; ?>"><?php echo $row_cal['region']; ?></option>
            </select><br>
            <input type="text" style="display: none;" class="form-control" id="leave_type_other" name="leave_type_other" value="">
        </div>
        <?php }
    } else {
        if ($class_id == 2) {
    ?>
        <div class="form-group">
            <label>Region / Type of Leave *</label>
            <input type="text" class="form-control" name="leave_type" value="<?php echo $row_cal['region'] ?>">
        </div>
    <?php } else { ?>
        <div class="form-group">
            <label>Region / Type of Leave *</label>
            <select name="leave_type" id="leave_type" class="form-control" onchange="other_leave_type(this.value)">
                <option <?php if($row_cal['region'] == 'Sick') { echo 'selected';} ?> value="Sick">Sick</option>
                <option <?php if($row_cal['region'] == 'Annual Leave') { echo 'selected';} ?> value="Annual Leave">Annual Leave</option>
                <option <?php if($row_cal['region'] == 'LWOP') { echo 'selected';} ?> value="LWOP">LWOP</option>
                <option <?php if($row_cal['region'] == 'Toil') { echo 'selected';} ?> value="Toil">Toil</option>
                <option <?php if($row_cal['region'] == 'Bday Leave') { echo 'selected';} ?> value="Bday Leave">Bday Leave</option>
                <option <?php if($row_cal['region'] == 'WorkCover') { echo 'selected';} ?> value="WorkCover">WorkCover</option>
                <option <?php if($row_cal['region'] == 'Other') { echo 'selected';} ?> value="Other">Other</option>
                <option <?php if($row_cal['region'] != 'Sick' || $row_cal['region'] != 'Annual Leave' || $row_cal['region'] != 'LWOP' || $row_cal['region'] != 'Toil' || $row_cal['region'] != 'Bday Leave' || $row_cal['region'] != 'WorkCover' || $row_cal['region'] != 'Other') { echo 'selected';} ?> value="<?php echo $row_cal['region']; ?>"><?php echo $row_cal['region']; ?></option>
            </select><br>
            <input type="text" style="display: none;" class="form-control" id="leave_type_other" name="leave_type_other" value="">
        </div>
    <?php } } ?>
    <div class="form-group">
        <label style="display:inline-block;"><input type="checkbox" onchange="check_leave()" name="marked_as_leave" value="1" <?php echo ($row_cal['marked_as_leave']==1)?'checked="checked"':'' ?> >&nbsp;Leave</label>
    </div>

     <div class="form-group">
        <label>Booking Staff</label>
        <select name="booking_staff" id="booking_staff" class="form-control">
        <option value="">Please Select</option>
            <?php
                foreach($staff_list->result_array() as $row){
            ?>
                  
                    <option <?php echo ( $row_cal['booking_staff'] ==  $row['StaffID'] )?'selected':'' ?> value="<?php echo $row['StaffID'] ?>"><?php echo "{$row['FirstName']} {$row['LastName']}" ?></option>
            <?php 
                }
            ?>
        </select>
    </div>

    <div class="form-group">
        <label>Details</label>
      <textarea name="details" class="form-control"><?php echo $row_cal['details'] ?></textarea>
    </div>


     <div class="form-group">
        <label><input <?php echo ($row_cal['accomodation']=="" || $row_cal['accomodation']=== NULL)?'checked="checked"':'' ?> name="accomodation" type="radio" value="">&nbsp;No Accomodation</label>
        <label><input <?php echo ($row_cal['accomodation']=='0')?'checked="checked"':'' ?> name="accomodation" type="radio" value="0">&nbsp;Accommodation Required</label>
        <label><input <?php echo ($row_cal['accomodation']==2)?'checked="checked"':'' ?> name="accomodation" type="radio" value="2">&nbsp;Accommodation Pending</label>
        <label><input <?php echo ($row_cal['accomodation']==1)?'checked="checked"':'' ?> name="accomodation" type="radio" value="1">&nbsp;Accommodation Booked</label>
    </div>

     <div class="form-group accomodation_drop" style="display:none;">
                <label>Accomodation</label>
                <select name="accomodation_id" class="form-control">
                   <?php
                    $acco_params = array(
                        'sel_query' => "accomodation_id, name",
                        'sort_list' => array(
                            array(
                                'order_by' => 'name',
                                'sort' => 'ASC'
                            )
                        )
                    );
                    $accomodation_query  = $this->calendar_model->getAccomodation($acco_params);

                    foreach($accomodation_query->result_array() as $row){
                    ?>
                        <option <?php echo ( $row['accomodation_id'] == $row_cal['accomodation_id'] )?'selected="true"':'';  ?> value="<?php echo $row['accomodation_id'] ?>"><?php echo $row['name'] ?></option>
                    <?php
                    }
                   ?>

                
                </select>
     </div>

    <?php if (!$cal_id){ ?>
     <div class="form-group">
                    
        <div class="checkbox">
            <input type="checkbox" name="send_ical" id="check-ical" value="1">
            <label for="check-ical">Send iCalendar</label>
        </div>

     </div>
    <?php } ?>


    <div class="form-group">
                <?php if($cal_id){
                ?>
                    <button id="edit_entry" type="button" name="update_event" class="btn">Update</button>
                    &nbsp;
                    <button id="delete_entry" type="button" name="delete_entry" class="btn btn-danger">Delete</button>
                <?php
                }else{
                    ?>
                    <button id="add_entry" type="button" name="add_event" class="btn">Add Event</button>
                    <?php
                } ?>
        
    </div>




<form>
</div>




<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script type="text/javascript">
    <?php
    if ($add == 1) {
    ?>
    check_leave();
    function check_leave(){
        if($('[name="marked_as_leave"]').prop('checked')){
            $('#div_leave').show();
        } else {
            $('#div_leave').hide();
        }
    }
    <?php } ?>

    function other_leave_type(value){
        // alert(value);
        if (value == 'Other') {
            $('#leave_type_other').show();
        } else {
            $('#leave_type_other').hide();
        }
    }

    jQuery(document).ready(function() {

        $('[name="accomodation"]').on('change', function(){

            var acc = $(this).val();
            
            if(acc==2 || acc==1){
                $('.accomodation_drop').slideDown();
            }else{
                $('.accomodation_drop').slideUp();
            }
            

        })
        
        if( $('[name="accomodation"]:checked').val()==1 ||  $('[name="accomodation"]:checked').val()==2){
            $('.accomodation_drop').show();
        }else{
            $('.accomodation_drop').hide();
        }

        //init datepicker
        jQuery('.flatpickr').flatpickr({
                    dateFormat: "d/m/Y H:i",
                    locale: {
                        firstDayOfWeek: 1
                    }
                });

        
        //EDIT
         $('#edit_entry').on('click',function(){

            <?php 
            if( isset($cal_id) && !empty($cal_id) ){
            ?>
                var cal_id = <?php echo $cal_id; ?>;
            <?php
            }
            ?>

            var staff_id = $('[name="staff_id"]').val();
            var start_date = $('[name="start_date"]').val();
            var finish_date = $('[name="finish_date"]').val();
            var leave_type = $('[name="leave_type"]').val();
            var marked_as_leave = $('[name="marked_as_leave"]').prop('checked');
            var booking_staff = $('[name="booking_staff"]').val();
            var details = $('[name="details"]').val();
            var accomodation = $('[name="accomodation"]:checked').val();
            var accomodation_id = $('[name="accomodation_id"]').val();
            var send_ical = $('[name="send_ical"]').prop('checked');
            
            var start_date_time_split = start_date.split(" ");
            var start_date_a = start_date_time_split[0];
            var start_date_2 =  start_date_a.split("/");
            var new_start_date = new Date(start_date_2[2], start_date_2[1], start_date_2[0]);

             var finish_date_split = finish_date.split(" ");
            var finish_date_a = finish_date_split[0];
            var finish_date_2 =  finish_date_a.split("/");
            var new_finish_date = new Date(finish_date_2[2], finish_date_2[1], finish_date_2[0]);

            if (leave_type == 'Other') {
                leave_type = $('[name="leave_type_other"]').val();
            }

            if(new_finish_date < new_start_date ){
                swal('','Finish date must be greater than start date','error');
                return false;
            }else{
                $('#load-screen').show(); //hide loader	
                jQuery.ajax({
                type: "POST",
                url: '<?php echo base_url(); ?>calendar/add_calendar_entry_static_process_ajax',
                dataType: 'json',
                data: { 
                type: 'update',
                cal_id: cal_id,
                staff_id: staff_id,
                start_date: start_date,
                finish_date: finish_date,
                leave_type: leave_type,
                marked_as_leave: marked_as_leave,
                booking_staff: booking_staff,
                details: details,
                accomodation: accomodation,
                accomodation_id: accomodation_id
            }
            }).done(function( ret ){	

                $('#load-screen').hide(); //hide loader		
                
                if(ret.status){
                    //success popup				
                    swal({
                        title:"Success!",
                        text: "Update Success",
                        type: "success",
                        showCancelButton: false,
                        confirmButtonText: "OK",
                        closeOnConfirm: false,
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });	
                    
                    setTimeout(function(){
                        $.fancybox.close();
                    },  <?php echo $this->config->item('timer') ?>);

                    //REFETCH EVENTS
                        $('#calendar').fullCalendar('refetchEvents');
                        $('.fc-popover').hide();


                }else{
                    swal('','Server error please contact admin.','error');
                }	
            

            });	
            }

        })


        //Add New Event
        $('#add_entry').on('click',function(){

             var staff_id_arr = new Array();
            $('#staff_id :selected').each(function(i, selected) {
                staff_id_arr[i] = $(selected).val();
            });

            var staff_id = $('[name="staff_id"]').val();
            var start_date = $('[name="start_date"]').val();
            var finish_date = $('[name="finish_date"]').val();
            var leave_type = $('[name="leave_type"]').val();
            var marked_as_leave = $('[name="marked_as_leave"]').prop('checked');
            var booking_staff = $('[name="booking_staff"]').val();
            var details = $('[name="details"]').val();
            var accomodation = $('[name="accomodation"]:checked').val();
            var accomodation_id = $('[name="accomodation_id"]').val();
            var send_ical = $('[name="send_ical"]').prop('checked');

            if (leave_type == 'Other') {
                leave_type = $('[name="leave_type_other"]').val();
            }
            var err = "";
            if(staff_id==""){
                err += "Staff must not be empty \n";
            }
            if(start_date==""){
                err += "Date must not be empty \n";
            }
            if($('[name="marked_as_leave"]').prop('checked')){
                if(leave_type==""){
                    err += "Region/Type of Leave must not be empty \n";
                }
            } 
            if(err!=""){
            swal('',err,'error');
            return false;
            }
            
            $('#load-screen').show(); //hide loader		

            jQuery.ajax({
            type: "POST",
            url: '<?php echo base_url(); ?>calendar/add_calendar_entry_static_process_ajax',
            dataType: 'json',
            data: { 
                type: 'add',
                staff_id: staff_id_arr,
                start_date: start_date,
                finish_date: finish_date,
                leave_type: leave_type,
                marked_as_leave: marked_as_leave,
                booking_staff: booking_staff,
                details: details,
                accomodation: accomodation,
                accomodation_id: accomodation_id,
                send_ical: send_ical
            }
            }).done(function( ret ){	

                $('#load-screen').hide(); //hide loader		
                
                if(ret.status){

                    //success popup				
                    swal({
                        title:"Success!",
                        text: "Successfully Added New Event",
                        type: "success",
                        showCancelButton: false,
                        confirmButtonText: "OK",
                        closeOnConfirm: false,
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });	
                    
                    setTimeout(function(){
                        $.fancybox.close();
                    },  <?php echo $this->config->item('timer') ?>);

                    //REFETCH EVENTS
                    $('#calendar').fullCalendar('refetchEvents');
                    $('.fc-popover').hide();


                }else{
                    swal('','Server error please contact admin.','error');
                }	
            

            });	

        })


        //DELETE ENTRY
        $('#delete_entry').click(function(){

            var cal_id = $('[name="cal_id"]').val();

            swal(
                    {
                        title: "",
                        text: "Are You Sure You Want to Delete Entry?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-success",
                        confirmButtonText: "Yes",
                        cancelButtonText: "No, Cancel!",
                        cancelButtonClass: "btn-danger",
                        closeOnConfirm: false,
                        closeOnCancel: true,
                    },
                    function(isConfirm){
                        if(isConfirm){

							$('#load-screen').show(); //show loader

							jQuery.ajax({
							type: "POST",
							url: "<?php echo base_url('/calendar/ajax_delete_calendar') ?>",
							dataType: 'json',
							data: { 
								calendar_id: cal_id
							}
							}).done(function(ret){

                                if(ret.status){
                                    $('#load-screen').hide(); //show loader
                                    //success popup				
                                    swal({
                                        title:"Success!",
                                        text: "Successfully Deleted",
                                        type: "success",
                                        showCancelButton: false,
                                        confirmButtonText: "OK",
                                        closeOnConfirm: false,
                                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                                        timer: <?php echo $this->config->item('timer') ?>
                                    });	
                                    
                                    setTimeout(function(){
                                        $.fancybox.close();
                                    },  <?php echo $this->config->item('timer') ?>);

                                    //REFETCH EVENTS
                                    $('#calendar').fullCalendar('refetchEvents');
                                    $('.fc-popover').hide();
                                }

							});

                        }
                        
                    }
            	);	

        })

        // select ALL STAFF
        jQuery("#all_staff_dp").click(function(){
            jQuery("#staff_id option").prop("selected",true);
        });



        //STAFF / BOOKING STAFF TWEAK
        /**
        var default_staff_id = $('#staff_id option:selected').val();
        var tech_onload = parseInt(default_staff_id);
        ajax_get_tech_call_centre(tech_onload); //get corresponding tech on load

        $('#staff_id').on('change', function(){ //get corresponding tech on dropdown change

            var tech = parseInt(jQuery(this).val());
            jQuery("#booking_staff option").prop("selected",false);

            ajax_get_tech_call_centre(tech);

        })
        */
         //STAFF / BOOKING STAFF TWEAK END


    }); //document ready end



    function ajax_get_tech_call_centre(tech){

        if(tech!=""){

            $('#load-screen').show(); 

            jQuery.ajax({
                type: "POST",
                url: "/calendar/ajax_get_tech_call_centre",
                dataType: 'json',
                data: { 				
                    tech: tech
                }
            }).done(function( ret ){
                var call_centre = parseInt(ret.other_call_centre);
                var accomodation_id = parseInt(ret.accomodation_id);
                
                
                // call centre
                jQuery("#booking_staff option").each(function(){
                    
                    if( jQuery(this).val()==call_centre ){
                        jQuery(this).prop("selected",true);
                    }
                    
                });
                
                $('#load-screen').hide(); 
                
            });	

            }

    }
        
        

 


</script>