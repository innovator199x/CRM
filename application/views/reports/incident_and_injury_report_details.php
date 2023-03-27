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
    .existin_photo_items img{
        height:33px;
        width:33px;
        border: 1px solid #ddd;
        padding: 3px;
    }
    li.existin_photo_items{
        padding-bottom:3px;
    }
    hr{margin:10px 0 10px 0;}
    .created-date-container {
        float: left; margin: 9px 0 0 6px; color: #00D1E5; font-size: 13px;
    }

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
            'link' => "/users/incident_and_injury_report_details/{$this->uri->segment(3)}"
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
                        'id' => 'incident_form_update'
                    );
                    echo form_open_multipart('/users/incident_and_injury_report_update_v2', $form_attr);
                    ?>

                    <!-- HIDDEN INPUTS -->
                    <input type="hidden" name="iai_id" value="<?php echo $incident_details_info->incident_and_injury_id ?>">

                    <div class="row">
                        <div class="col-md-6">
                    <section class="card card-blue-fill">
                                    <header class="card-header">EMPLOYEE DETAILS</header>
                                    <div class="card-block">

                                        <div class="form-group row">
                                            <label class="col-sm-3 form-control-label">Name <span class="required">*</span></label>
                                            <div class="col-sm-9">
                                                <input required="" value="<?php echo $incident_details_info->ip_name ?>" type="text"  class="form-control" name="ip_name" id="ip_name">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-3 form-control-label">Department</label>
                                            <div class="col-sm-9">
                                                <select class="form-control" name="department" id="department">
                                                    <option <?php echo ($incident_details_info->department=="") ? 'selected' : NULL; ?> value="">Please select</option>
                                                    <option <?php echo ($incident_details_info->department=="Field") ? 'selected' : NULL; ?> value="Field">Field</option>
                                                    <option <?php echo ($incident_details_info->department=="Customer Service") ? 'selected' : NULL; ?> value="Customer Service">Customer Service</option>
                                                    <option <?php echo ($incident_details_info->department=="Scheduling") ? 'selected' : NULL; ?> value="Scheduling">Scheduling</option>
                                                    <option <?php echo ($incident_details_info->department=="Sales") ? 'selected' : NULL; ?> value="Sales">Sales</option>
                                                    <option <?php echo ($incident_details_info->department=="Human Resources") ? 'selected' : NULL; ?> value="Human Resources">Human Resources</option>
                                                    <option <?php echo ($incident_details_info->department=="Operations") ? 'selected' : NULL; ?> value="Operations">Operations</option>
                                                </select>

                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-3 form-control-label">Phone Number <span class="required">*</span></label>
                                            <div class="col-sm-9">
                                                <input required="" value="<?php echo $incident_details_info->ip_tel_num ?>" type="text"  class="form-control" name="ip_tel_num" id="ip_tel_num"> 
                                            </div>
                                        </div>

                                    </div>
                            </section>

                            <section class="card card-blue-fill">
                                    <header class="card-header">DESCRIPTION OF INCIDENT</header>
                                    <div class="card-block">

                                        <div class="form-group">
                                            <label class="form-label">Location <span class="required">*</span></label>
                                            <input required="" value="<?php echo $incident_details_info->location_of_incident ?>" type="text"  class="form-control" name="loc_of_inci" id="loc_of_inci">
                                        </div>

                                       <div class="row">
                                            <div class="col-md-4">

                                                <div class="form-group">
                                                    <label class="form-label">Date <span class="required">*</span></label>
                                                    <input required="" type="text" class="form-control flatpickr" id="date_of_incident" name="date_of_incident" data-allow-input="true" value="<?php echo date('d/m/Y', strtotime($incident_details_info->datetime_of_incident)) ?>">
                                               </div>
                                               <div class="form-group">
                                                    <label class="form-label">Time <span class="required">*</span></label>
                                                    <input required="" value="<?php echo date('H:i', strtotime($incident_details_info->datetime_of_incident)) ?>" class="form-control clockpicker" name="time_of_incident" id="time_of_incident">
                                               </div>
                                               <div class="form-group">
                                                    <label class="form-label">Were the Police Notified?</label>

                                                    <div class="radio">
                                                        <input class="police_notified" name="police_notified" type="radio" id="police_notified_yes" value="1" <?php echo ($incident_details_info->were_the_police_notified==1)?"checked":NULL; ?> >                                        
                                                        <label for="police_notified_yes">Yes</label>
                                                        &nbsp;
                                                        &nbsp;
                                                        <input class="police_notified" name="police_notified" type="radio" id="police_notified_no" value="0" <?php echo ($incident_details_info->were_the_police_notified==0)?"checked":NULL; ?> >                                        
                                                        <label for="police_notified_no">No</label>
                                                    </div>
                                               </div>

                                            </div>

                                            <div class="col-md-8">
                                                
                                               <div class="form-group">
                                                    <label class="form-label">Incident Details in Full</label>
                                                    <textarea name="desc_inci" id="desc_inci" class="form-control desc_inci" style="height: 84px; margin:0px;"><?php echo $incident_details_info->describe_incident ?></textarea>
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
                                                        <input value="<?php echo $incident_details_info->witness_name ?>" type="text"  class="form-control" name="witness_name" id="ip_witness_namenoi">
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-sm-3 form-control-label">Phone Number</label>
                                                    <div class="col-sm-9">
                                                        <input value="<?php echo $incident_details_info->witness_contact ?>" type="text"  class="form-control" name="witness_contact" id="witness_contact">
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
                                                            <option <?php echo ($incident_details_info->reported_to == $staff['staff_accounts_id']) ? 'selected' : '' ?> value="<?php echo $staff['staff_accounts_id'] ?>"><?php echo "{$staff['FirstName']} {$staff['LastName']}"; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-sm-3 form-control-label">Phone Number</label>
                                                    <div class="col-sm-9">
                                                    <input type="text"  class="form-control" name="reported_to_phone" id="reported_to_phone" value="<?php echo $incident_details_info->reported_to_phone_number ?>">
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
                                                    <input class="injury_checkbox" name="injuury_type" type="radio" id="injury_0" value="" required <?php echo ( $incident_details_info->nature_of_incident == "" ) ? 'checked' : '' ?> >                                        
                                                    <label for="injury_0">None</label>
                                                </div>
                                                <div class="radio">
                                                    <input class="injury_checkbox" name="injuury_type" type="radio" id="injury_2" value="2"  <?php echo ( $incident_details_info->nature_of_incident == 2 ) ? 'checked' : '' ?> >                                        
                                                    <label for="injury_2">On-site First Aid</label>
                                                </div>
                                                <div class="radio">
                                                    <input class="injury_checkbox" name="injuury_type" type="radio" id="injury_3" value="3"  <?php echo ( $incident_details_info->nature_of_incident == 3 ) ? 'checked' : '' ?> >                                        
                                                    <label for="injury_3">Medical/Emergency Treatment</label>
                                                </div>
                                                <div class="radio">
                                                    <input class="injury_checkbox" name="injuury_type" type="radio" id="injury_5" value="5"  <?php echo ( $incident_details_info->nature_of_incident == 5 ) ? 'checked' : '' ?>>                                        
                                                    <label for="injury_5">Property Damage</label>
                                                </div>
                                                <div class="radio">
                                                    <input class="injury_checkbox" name="injuury_type" type="radio" id="injury_7" value="7"  <?php echo ( $incident_details_info->nature_of_incident == 7 ) ? 'checked' : '' ?>>                                        
                                                    <label for="injury_7">Theft</label>
                                                </div>
                                                <div class="radio">
                                                    <input class="injury_checkbox" name="injuury_type" type="radio" id="injury_8" value="8"  <?php echo ( $incident_details_info->nature_of_incident == 8 ) ? 'checked' : '' ?> >                                        
                                                    <label for="injury_8">Other, please explain:</label>
                                                </div>
                                                <div class="form-group">
                                                    <?php if($incident_details_info->nature_of_incident==8){
                                                        $style = "display:block;"; 
                                                    }else{
                                                        $style = "display:none;"; 
                                                    }
                                                        ?>
                                                        <textarea style="<?php echo $style; ?>" class="form-control injury_other_details"  name="injury_other_details"><?php echo  $incident_details_info->injury_type_other_details; ?></textarea>
                                                    
                                                    </div>
                                           </div>
                                           <div class="col-md-6">

                                               <div class="form-group">
                                                    <label class="form-label">Do you require further treatment?</label>
                                                    <div class="radio">
                                                        <input <?php echo ($incident_details_info->ip_further_treatment == 1) ? 'checked' : '' ?> class="further_treatment_checkbox" name="ip_fur_treat" type="radio" id="further_treatment_yes" value="1">                                        
                                                        <label for="further_treatment_yes">Yes</label>
                                                        &nbsp;&nbsp;
                                                        <input <?php echo ($incident_details_info->ip_further_treatment == 0) ? 'checked' : '' ?> class="further_treatment_checkbox" name="ip_fur_treat" type="radio" id="further_treatment_no" value="0">                                        
                                                        <label for="further_treatment_no">No</label>
                                                    </div>
                                               </div>
                                               <div class="form-group">
                                                <label class="form-label">If yes, please explain what treatment is required?</label>
                                                <textarea class="form-control" name="further_treatment_details"><?php echo  $incident_details_info->further_treatment_details?></textarea>
                                               </div>

                                           </div>

                                        </div>

                                    </div>
                            </section>
                                                        </div>
                                                        </div>



                    <!----------- Outcome ------------>
                   
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <div class="created-date-container">
                                <?Php
                                if ($incident_details_info->created_by !== null) {
                                    ?>
                                    Submitted By:<?Php echo "{$incident_details_info->FirstName} {$incident_details_info->LastName}";
                                    ?>
                                    <br />On: 
                                    <?Php
                                }
                                echo date('d/m/Y H:i', strtotime($incident_details_info->created_date));
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <div class="checkbox">
                                <input <?php echo ($incident_details_info->confirm_chk == 1) ? 'checked' : '' ?>  type="checkbox" required="" id="confirm_chk" name="confirm_chk" value="1">
                                <label class="toki" for="confirm_chk">I confirm that the information I have entered is true and correct to the best of my knowledge</label>
                            </div>
                        </div>
                    </div>
                    <!----------- Outcome END ------------>

                    <div class="form-group row">
                        <div class="col-sm-6">
                            <input type="submit" class="btn" value="Submit">
                        </div>
                    </div>

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

    <h4><?php echo $title; ?></h4>
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

        //Delete Incident Photo
        $('.delete_incident_photo').click(function (e) {
            e.preventDefault();
            var incident_photos_id = jQuery(this).parents("li.existin_photo_items").find(".incident_photos_id").val();

            swal(
                    {
                        title: "",
                        text: "Are you sure you want to delete?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-success",
                        confirmButtonText: "Yes",
                        cancelButtonText: "No, Cancel!",
                        closeOnConfirm: false,
                        closeOnCancel: true,
                    },
                    function (isConfirm) {
                        if (isConfirm) {

                            $('#load-screen').show(); //show loader

                            // continue via ajax request
                            jQuery.ajax({
                                type: "POST",
                                url: '<?php echo base_url(); ?>users/ajax_delete_incident_photo',
                                dataType: 'json',
                                data: {
                                    iai_id: <?php echo $iai_id ?>,
                                    incident_photos_id: incident_photos_id
                                }
                            }).done(function (ret) {

                                $('#load-screen').hide(); //hide loader		

                                if (ret.status) {
                                    //success popup				
                                    swal({
                                        title: "Success!",
                                        text: ret.json_msg,
                                        type: "success",
                                        showCancelButton: false,
                                        confirmButtonText: "OK",
                                        closeOnConfirm: false
                                    }, function (isConfirm2) {
                                        if (isConfirm2) {
                                            location.reload();
                                        }
                                    });

                                } else {
                                    swal('', 'Server error please contact admin.', 'error');
                                }


                            });


                        } else {
                            return false;
                        }

                    }

            );


        })



        //Incident Form Submit
        jQuery("#incident_form_update").submit(function () {

            var error = "";
            var submitCount = 0;



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


    })

</script>