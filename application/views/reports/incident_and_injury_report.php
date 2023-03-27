<link rel="stylesheet" href="/inc/css/lib/clockpicker/bootstrap-clockpicker.min.css">
<style>
    .g_form label{padding-top:7px;}
    h4.m-t-lg{
        margin-top:40px;
    }
    .checkbox input + label.toki::before, .checkbox input + label.toki::after{
        margin-top:3px;
    }
    .gherx_custom_file{margin-bottom:5px;}
    span.required{color:red}
</style>
<div class="box-typical box-typical-padding">

    <?php
// breadcrumbs template
    $bc_items = array(
        array(
            'title' => 'Incident Summary',
            'link' => "/users/incident_and_injury_report_list"
        ),
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/users/incident_and_injury_report"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>


    <section>
        <div class="body-typical-body">
            <div class="g_form">

                <div class="col-md-12">

                    <?php
                    $form_attr = array(
                        'id' => 'incident_form'
                    );
                    echo form_open_multipart('/users/incident_and_injury_report_script_v2', $form_attr);
                    ?>


                    <div class="row">
                        <div class="col-md-6">

                            <section class="card card-blue-fill">
                                    <header class="card-header">EMPLOYEE DETAILS</header>
                                    <div class="card-block">

                                        <div class="form-group row">
                                            <label class="col-sm-3 form-control-label">Name <span class="required">*</span></label>
                                            <div class="col-sm-9">
                                            <?php 
                                                $staffparams = array(
                                                    'sel_query' => 'sa.StaffID, sa.FirstName, sa.LastName',
                                                    'sort_list' => array(
                                                        array(
                                                            'order_by' => 'sa.FirstName',
                                                            'sort' => 'ASC'
                                                        )
                                                    )
                                                );
                                                $staff  = $this->gherxlib->getStaffInfo($staffparams);
                                            ?>
                                                <!-- <input required="" type="text"  class="form-control" name="ip_name" id="ip_name"> -->
                                                <select  class="form-control" name="ip_name" id="ip_name">
                                                <?php
                                                foreach($staff->result_array() as $row){
                                                ?>
                                                <option <?php echo ($row['StaffID'] == $this->session->staff_id)? "selected" : NULL ?> value="<?php echo $row['FirstName']." ".$row['LastName']; ?>"><?php echo "{$row['FirstName']} {$row['LastName']}" ?></option>
                                                <?php
                                                }
                                                ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-3 form-control-label">Department</label>
                                            <div class="col-sm-9">
                                                <select class="form-control" name="department" id="department">
                                                    <option value="">Please select</option>
                                                    <option value="Field">Field</option>
                                                    <option value="Customer Service">Customer Service</option>
                                                    <option value="Scheduling">Scheduling</option>
                                                    <option value="Sales">Sales</option>
                                                    <option value="Human Resources">Human Resources</option>
                                                    <option value="Operations">Operations</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-3 form-control-label">Phone Number <span class="required">*</span></label>
                                            <div class="col-sm-9">
                                                <input required="" type="text"  class="form-control" name="ip_tel_num" id="ip_tel_num">
                                            </div>
                                        </div>

                                    </div>
                            </section>

                            <section class="card card-blue-fill">
                                    <header class="card-header">DESCRIPTION OF INCIDENT</header>
                                    <div class="card-block">

                                        <div class="form-group">
                                            <label class="form-label">Location <span class="required">*</span></label>
                                            <input required="" type="text"  class="form-control" name="loc_of_inci" id="loc_of_inci">
                                        </div>

                                       <div class="row">
                                            <div class="col-md-4">

                                                <div class="form-group">
                                                    <label class="form-label">Date <span class="required">*</span></label>
                                                    <input required type="text" data-max-date="today" class="form-control flatpickr" id="date_of_incident" name="date_of_incident" data-allow-input="true" required="" style="width:100%;">
                                               </div>
                                               <div class="form-group">
                                                    <label class="form-label">Time <span class="required">*</span></label>
                                                    <input required="" class="form-control clockpicker" name="time_of_incident" id="time_of_incident">
                                               </div>
                                               <div class="form-group">
                                                    <label class="form-label">Were the Police Notified?</label>

                                                    <div class="radio">
                                                        <input class="police_notified" name="police_notified" type="radio" id="police_notified_yes" value="1">                                        
                                                        <label for="police_notified_yes">Yes</label>
                                                        &nbsp;
                                                        &nbsp;
                                                        <input class="police_notified" name="police_notified" type="radio" id="police_notified_no" value="0">                                        
                                                        <label for="police_notified_no">No</label>
                                                    </div>
                                               </div>

                                            </div>

                                            <div class="col-md-8">
                                                
                                               <div class="form-group">
                                                    <label class="form-label">Incident Details in Full</label>
                                                    <textarea name="desc_inci" id="desc_inci" class="form-control desc_inci" style="min-height:220px;"></textarea>
                                               </div>

                                            </div>
                                       </div>
<hr/>
                                       <div class="row">

                                           <div class="col-md-6">
                                               <p><strong>Witness Details</strong></p>

                                               <div class="form-group row">
                                                    <label class="col-sm-3 form-control-label">Name</label>
                                                    <div class="col-sm-9">
                                                    <input type="text"  class="form-control" name="witness_name" id="ip_witness_namenoi">
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-sm-3 form-control-label">Phone Number</label>
                                                    <div class="col-sm-9">
                                                    <input type="text"  class="form-control" name="witness_contact" id="witness_contact">
                                                    </div>
                                                </div>

                                           </div>

                                           <div class="col-md-6">
                                                <p><strong>To Whom was this Incident Reported?</strong></p>

                                                <div class="form-group row">
                                                    <label class="col-sm-3 form-control-label">Name</label>
                                                    <div class="col-sm-9">
                                                  <!--  <input type="text"  class="form-control" name="reported_to_name" id="reported_to_name">-->
                                                    <?php
                                                    // for global and full access
                                                    // sarah gutherie - 2226
                                                    $staff_sql = $this->db->query("
                                                    SELECT DISTINCT(ca.`staff_accounts_id`), sa.`FirstName`, sa.`LastName`
                                                    FROM staff_accounts AS sa
                                                    INNER JOIN `country_access` AS ca ON (
                                                        sa.`StaffID` = ca.`staff_accounts_id` 
                                                        AND ca.`country_id` ={$this->config->item('country')}
                                                    )
                                                    WHERE sa.deleted =0
                                                    AND sa.active =1											
                                                    AND (
                                                        sa.`ClassID` = 2 OR 
                                                        sa.`ClassID` = 9 OR
                                                        sa.`StaffID` = 2226
                                                    )
                                                    ORDER BY sa.`FirstName`
                                                    ");
                                                    ?>
                                                    <select class="form-control" name="reported_to" id="reported_to">
                                                        <option value="">---</option>
                                                        <?php foreach ($staff_sql->result_array() as $staff) { ?>
                                                            <option value="<?php echo $staff['staff_accounts_id'] ?>"><?php echo "{$staff['FirstName']} {$staff['LastName']}"; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-sm-3 form-control-label">Phone Number</label>
                                                    <div class="col-sm-9">
                                                    <input type="text"  class="form-control" name="reported_to_phone" id="reported_to_phone">
                                                    </div>
                                                </div>
                                           </div>

                                       </div>

                                    </div>
                            </section>

                            <section class="card card-blue-fill">
                                    <header class="card-header">INJURY TYPE</header>
                                    <div class="card-block">

                                        <div class="row">

                                           <div class="col-md-6">
                                                <div class="radio">
                                                    <input class="injury_checkbox" name="injuury_type" type="radio" id="injury_0" value="" required>                                        
                                                    <label for="injury_0">None</label>
                                                </div>
                                                <div class="radio">
                                                    <input class="injury_checkbox" name="injuury_type" type="radio" id="injury_2" value="2">                                        
                                                    <label for="injury_2">On-site First Aid</label>
                                                </div>
                                                <div class="radio">
                                                    <input class="injury_checkbox" name="injuury_type" type="radio" id="injury_3" value="3">                                        
                                                    <label for="injury_3">Medical/Emergency Treatment</label>
                                                </div>
                                                <div class="radio">
                                                    <input class="injury_checkbox" name="injuury_type" type="radio" id="injury_5" value="5">                                        
                                                    <label for="injury_5">Property Damage</label>
                                                </div>
                                                <div class="radio">
                                                    <input class="injury_checkbox" name="injuury_type" type="radio" id="injury_7" value="7">                                        
                                                    <label for="injury_7">Theft</label>
                                                </div>
                                                <div class="radio">
                                                    <input class="injury_checkbox" name="injuury_type" type="radio" id="injury_8" value="8">                                        
                                                    <label for="injury_8">Other, please explain:</label>
                                                </div>
                                                <div class="form-group">
                                                        <textarea style="display:none;" class="form-control injury_other_details"  name="injury_other_details"></textarea>
                                                </div>
                                           </div>
                                           <div class="col-md-6">

                                               <div class="form-group">
                                                    <label class="form-label">Do you require further treatment?</label>
                                                    <div class="radio">
                                                        <input class="further_treatment_checkbox" name="ip_fur_treat" type="radio" id="further_treatment_yes" value="1">                                        
                                                        <label for="further_treatment_yes">Yes</label>
                                                        &nbsp;&nbsp;
                                                        <input class="further_treatment_checkbox" name="ip_fur_treat" type="radio" id="further_treatment_no" value="0">                                        
                                                        <label for="further_treatment_no">No</label>
                                                    </div>
                                               </div>
                                               <div class="form-group">
                                                <label class="form-label">If yes, please explain what treatment is required?</label>
                                                <textarea class="form-control" name="further_treatment_details"></textarea>
                                               </div>

                                           </div>

                                        </div>

                                    </div>
                            </section>

                            <div>

                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <div class="checkbox">
                                            <input type="checkbox" id="confirm_chk" required="" name="confirm_chk" value="1">
                                            <label class="toki" for="confirm_chk">I confirm that the information I have entered is true and correct to the best of my knowledge<span class="required">*</span></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <input type="submit" class="btn" value="Submit">
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>


                  
                    <!----------- The Incident ------------>
                      <!--
                    <h4 class="m-t-lg with-border">The Incident</h4>

                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Date of incident<span class="required">*</span></label>
                        <div class="col-sm-3">
                            <input type="text" data-max-date="today" class="form-control flatpickr" id="date_of_incident" name="date_of_incident" data-allow-input="true" required="">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Time of incident<span class="required">*</span></label>
                        <div class="col-sm-3">
                            <input required="" class="form-control clockpicker" name="time_of_incident" id="time_of_incident">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Nature of incident<span class="required">*</span></label>
                        <div class="col-sm-3">
                            <select required="" class="form-control" name="nature_of_incident" id="nature_of_incident">
                                <option value="">----</option>
                                <option value="1">Near Miss</option>	
                                <option value="2">First Aid</option>
                                <option value="3">Medical Treatment</option>
                                <option value="4">Car accident</option>	
                                <option value="5">Property damage</option>
                                <option value="6">Incident report</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Location of incident<span class="required">*</span></label>
                        <div class="col-sm-3">
                            <input required="" type="text"  class="form-control" name="loc_of_inci" id="loc_of_inci">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Describe the incident</label>
                        <div class="col-sm-3">
                            <textarea name="desc_inci" id="desc_inci" class="form-control desc_inci" style="height: 84px; margin:0px;"></textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Photo of Incident</label>
                        <div class="col-sm-3">
                            <div class="gherx_custom_file">
                                <span class="btn btn-sm btn-rounded btn-file">
                                    <i class="font-icon font-icon-cloud-upload-2"></i>
                                    <span class="gherx_file_name">Choose file</span>
                                    <input class="gherx_file_input" type="file" name="photo_of_incident[]" >
                                </span>
                            </div>

                            <div style="margin-top:15px;"><button type="button" class="btn btn-sm btn_plus_photo"><i class="fa fa-plus"></i> Additional File</button></div>
                        </div>
                    </div>
                -->
                    <!----------- The Incident END ------------>


                    <!----------- Injured Person Details ------------>
                    <!--
                    <h4 class="m-t-lg with-border">Injured Person Details</h4>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Name<span class="required">*</span></label>
                        <div class="col-sm-3">
                            <input required="" type="text"  class="form-control" name="ip_name" id="ip_name">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Address</label>
                        <div class="col-sm-3">
                            <input type="text"  class="form-control" name="ip_address" id="ip_address">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Occupation</label>
                        <div class="col-sm-3">
                            <input type="text"  class="form-control" name="ip_occu" id="ip_occu">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Date of birth</label>
                        <div class="col-sm-3">
                            <input type="text"  class="form-control flatpickr" name="ip_dob" id="ip_dob">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Telephone number<span class="required">*</span></label>
                        <div class="col-sm-3">
                            <input required="" type="text"  class="form-control" name="ip_tel_num" id="ip_tel_num">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Employer</label>
                        <div class="col-sm-3">
                            <input type="text"  class="form-control" name="ip_employer" id="ip_employer">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Nature of Injury</label>
                        <div class="col-sm-3">
                            <input type="text"  class="form-control" name="ip_noi" id="ip_noi">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Location of Injury</label>
                        <div class="col-sm-3">
                            <input type="text"  class="form-control" name="ip_loi" id="ip_loi">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Onsite treatment</label>
                        <div class="col-sm-3">
                            <select class="form-control" name="ip_onsite_treatment" id="ip_onsite_treatment">
                                <option value="">----</option>
                                <option value="1">Yes</option>	
                                <option value="0">No</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Further treatment required?</label>
                        <div class="col-sm-3">
                            <select class="form-control" name="ip_fur_treat" id="ip_fur_treat">
                                <option value="">----</option>
                                <option value="1">Yes</option>	
                                <option value="0">No</option>
                            </select>
                        </div>
                    </div>
                -->
                    <!----------- Injured Person Details END ------------>


                    <!----------- Witness Details ------------>
                    <!--
                    <h4 class="m-t-lg with-border">Witness Details</h4>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Name</label>
                        <div class="col-sm-3">
                            <input type="text"  class="form-control" name="witness_name" id="ip_witness_namenoi">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Contact Number</label>
                        <div class="col-sm-3">
                            <input type="text"  class="form-control" name="witness_contact" id="witness_contact">
                        </div>
                    </div>
                -->
                    <!----------- Witness Details END ------------>



                    <!----------- Outcome ------------>
                    <!--
                    <h4 class="m-t-lg with-border">Outcome</h4>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Time lost due to injury</label>
                        <div class="col-sm-3">
                            <select class="form-control" name="loss_time_injury" id="loss_time_injury">
                                <option value="">----</option>
                                <option value="1">Yes</option>	
                                <option value="0">No</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">Who was the incident reported to?</label>
                        <div class="col-sm-3">
                            <?php
                            // for global and full access
                            // sarah gutherie - 2226
                         /*   $staff_sql = $this->db->query("
                        SELECT DISTINCT(ca.`staff_accounts_id`), sa.`FirstName`, sa.`LastName`
                        FROM staff_accounts AS sa
                        INNER JOIN `country_access` AS ca ON (
                            sa.`StaffID` = ca.`staff_accounts_id` 
                            AND ca.`country_id` ={$this->config->item('country')}
                        )
                        WHERE sa.deleted =0
                        AND sa.active =1											
                        AND (
                            sa.`ClassID` = 2 OR 
                            sa.`ClassID` = 9 OR
                            sa.`StaffID` = 2226
                        )
                        ORDER BY sa.`FirstName`
                        ");
                        */
                            ?>
                            <select class="form-control" name="reported_to" id="reported_to">
                                <option value="">---</option>
                                <?php //foreach ($staff_sql->result_array() as $staff) { ?>
                                    <option value="<?php // echo $staff['staff_accounts_id'] ?>"><?php // echo "{$staff['FirstName']} {$staff['LastName']}"; ?></option>
                                <?php// } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">&nbsp;</label>
                        <div class="col-sm-3">
                            <div class="checkbox">
                                <input type="checkbox" id="confirm_chk" required="" name="confirm_chk" value="1">
                                <label class="toki" for="confirm_chk">I confirm that the information I have entered is true and correct to the best of my knowledge<span class="required">*</span></label>
                            </div>
                        </div>
                    </div>
                                -->
                    <!----------- Outcome END ------------>
<!--
                    <div class="form-group row">
                        <label class="col-sm-2 form-control-label">&nbsp;</label>
                        <div class="col-sm-3">
                            <input type="submit" class="btn" value="Submit">
                        </div>
                    </div>
                    -->

                    <?php echo form_close(); ?>

                </div>
            </div>

            <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
            <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>

        </div>
    </section>

</div>



<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

    <h4>Incident Form</h4>
    <p>This page is used to record all incidents or near misses that happen whilst working.</p>

</div>
<!-- Fancybox END -->


<script type="text/javascript" src="/inc/js/lib/clockpicker/bootstrap-clockpicker.min.js"></script>
<script type="text/javascript">

    jQuery(document).ready(function () {

         //success/error message sweel alert pop  start
        <?php if ($this->session->flashdata('status') && $this->session->flashdata('status') == 'success') { ?>
                    swal({
                        title: "Success!",
                        html: true,
                        text: "<?php echo $this->session->flashdata('success_msg') ?> <br/></br/> <a target='blank' href='/users/incident_and_injury_report_pdf/<?php echo $this->session->flashdata('incident_id') ?>'>Download PDF</a>",
                        type: "success",
                        confirmButtonClass: "btn-success"
                    });
        <?php } else if ($this->session->flashdata('status') && $this->session->flashdata('status') == 'error') { ?>
                    swal({
                        title: "Error!",
                        text: "<?php echo $this->session->flashdata('error_msg') ?>",
                        type: "error",
                        confirmButtonClass: "btn-danger"
                    });
        <?php } ?>
        //success/error message sweel alert pop  end


        $('.clockpicker').clockpicker({
            autoclose: true,
            donetext: 'Done',
            'default': 'now'
        });


        //custom file tweak
        jQuery('.gherx_file_input').on('change', function (e) {
            var input = $(this);
            var fileName = input.val().split('\\').pop();
            input.parents('.gherx_custom_file').find('.gherx_file_name').html(fileName);
        })


        //plub phhoto clone tweak
        jQuery('.btn_plus_photo').click(function (e) {
            e.preventDefault();
            var obj = $(this);

            var last_photo_elem = jQuery(".gherx_custom_file:last");
            var photo_elem = last_photo_elem.clone(true);
            photo_elem.find(".gherx_file_name").html("Choose file"); //default file name label
            last_photo_elem.after(photo_elem);
        })


        //Incident Form Submit
        jQuery("#incident_form").submit(function () {

            var error = "";
            var submitCount = 0;


            $.each($('input.gherx_file_input[type=file]'), function (i, item) {
                if ($(item).get(0).files.length > 0) {
                    var file_type = $(item).get(0).files[0].type;
                    var file_size = $(item).get(0).files[0].size;
                    var validImageTypes = ['image/gif', 'image/jpeg', 'image/png'];
                    if (file_size > 0 && !validImageTypes.includes(file_type)) {
                        error += "Please attach image files only";
                        return false;
                    }
                }

            });

            if (error != "") {
                swal('', error, 'error');
                return false;
            }


            if (submitCount == 0) {
                submitCount++;
                jQuery(this).submit();
            } else {
                swal('', 'Submission in progress', 'error');
            }


        });

        $('.injury_checkbox').change(function(){
            var sel = $(this).val();

            if(sel==8){
                $('.injury_other_details').show();
            }else{
                $('.injury_other_details').hide();
            }
            
        })




    });



</script>