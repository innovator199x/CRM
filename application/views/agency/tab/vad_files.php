<style>
    .delfile, .caf_delete{font-size:20px;margin:0px;padding:0px;}
    .btn_del{
        font-size:20px;
    }
    td.td_del{margin-top:0px;margin-bottom:0px;width:100px;text-align:center;}
    .upload_form_div form input.upload_input{
        margin-top: 14px;
        padding-bottom: 10px;
        margin-right: 10px;
        float:left;
    }
    .upload_form_div{display:none;}
</style>
<div class="text-left files_div">
    <div class="row">
        <div class="col-md-6 columns">
                <section class="card card-blue-fill">
                    <header class="card-header">Agency Files</header>
                    <div class="card-block">
                        <table class="table table-hover main-table table_agency_files table-no-border">
                            <thead>
                                <tr>
                                    <th> File Name</th>
                                    <th width="100px" class="text-center">Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($property_files)!=0){ 
                                    foreach($property_files as $file){
                                ?>
                                    <tr>
                                        <td><a target="_blank" href="/uploads/agency_files/<?php echo $agency_id ?>/<?php echo $file ?>"><?php echo $file; ?></a></td>
                                        <td class="td_del"><a class="delfile btn_del" data-toggle="tooltip" title="Delete" href="#" data-agencyid="<?php echo $agency_id; ?>" data-file="<?php echo $file ?>"><span class="fa fa-trash"></span></a></td>
                                    </tr>
                                <?php
                                    }
                                }else{
                                    echo "<tr><td colspan='2'>This Agency Has No Uploaded Files. Upload One Below</td></tr>";
                                } ?>
                            
                            </tbody>
                        </table>
                        <div class="vad_cta_box text-right"><button class="btn_add_upload btn">Add File</button></div>
                        <div class="upload_form_div right">
                            <form action="/agency/vad_upload_agency_file" enctype="multipart/form-data" method="post">
                                <input type="hidden" name="agency_id" value="<?php echo $agency_id ?>">
                                <input type="file" id="fileupload" name="fileupload" class="submitbtnImg upload_input"> 						
                                <button style="float: left; margin-top: 5px;" class="addinput btn submitbtnImg eagdtbt btn_upload_now" id="btn_upload_now_agency_file" type="submit">Upload Now</button>
                            </form>
                        </div>
                    </div>
                </section>
        </div>

        <div class="col-md-6 columns">
            <section class="card card-blue-fill">
                <header class="card-header">Contractor Appointment Form</header>
                <div class="card-block">
                    <table class="table table-hover main-table table_contractor_appointment_form table-no-border">
                        <thead>
                            <tr>
                                <th>File Name</th>
                                <th width="100px" class="text-center">Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($getContractorAppointment->num_rows() > 0){
                                foreach($getContractorAppointment->result_array() as $ca){
                            ?>
                                    <tr>
                                        <td><a href="/<?php echo $ca['file_path']; ?>" target="_blank"><?php echo $ca['file_name']; ?></a></td>		
                                        <td class="td_del"><a class="caf_delete btn_del" data-file_name="<?php echo $ca['file_name']; ?>" data-agency_id="<?php echo $agency_id; ?>" data-ca_id="<?php echo $ca['contractor_appointment_id']; ?>" data-toggle="tooltip" title="Delete" href="#"><span class="fa fa-trash"></span></a></td>
                                    </tr>
                            <?php
                                }
                            }else{
                                echo "<tr><td colspan='2'>This Agency Has No Uploaded Files. Upload One Below</td></tr>";
                            } ?>
                        </tbody>
                    </table>
                    <div class="vad_cta_box text-right"><button class="btn_add_upload btn">Add Form</button></div>
                    <div class="upload_form_div right">
                        <form action="/agency/upload_contractor_appointment" enctype="multipart/form-data" method="post">
                            <input type="hidden" name="agency_id" value="<?php echo $agency_id ?>">
                            <input type="file" id="upload_cont_app_frm" name="upload_cont_app_frm" class="submitbtnImg upload_input"> 						
                            <button style="float: left; margin-top: 5px;" class="addinput btn submitbtnImg eagdtbt btn_upload_now" id="btn_upload_now_contractor_appointment_form" type="submit">Upload Now</button>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 columns">
            <section class="card card-blue-fill">
                <header class="card-header">Agency Specific Brochures</header>
                <div class="card-block">
                    <table class="table table-hover main-table table_contractor_appointment_form table-no-border">
                        <thead>
                            <tr>
                                <th>File Name</th>
                                <th width="100px" class="text-center">Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($agency_specific_brochures->num_rows() > 0){
                                foreach($agency_specific_brochures->result_array() as $asb_row){
                            ?>
                                    <tr>
                                        <td><a href="/<?php echo $asb_row['file_path']; ?>" target="_blank"><?php echo $asb_row['file_name']; ?></a></td>		
                                        <td class="td_del"><a class="asb_delete btn_del" data-file_name="<?php echo $asb_row['file_name']; ?>" data-agency_id="<?php echo $agency_id; ?>" data-asb_id="<?php echo $asb_row['agency_specific_brochures_id']; ?>" data-toggle="tooltip" title="Delete" href="#"><span class="fa fa-trash"></span></a></td>
                                    </tr>
                            <?php
                                }
                            }else{
                                echo "<tr><td colspan='2'>This Agency Has No Uploaded Files. Upload One Below</td></tr>";
                            } ?>
                        </tbody>
                    </table>
                    <div class="vad_cta_box text-right"><button class="btn_add_upload btn">Add Brochures</button></div>
                    <div class="upload_form_div right">
                        <form action="/agency/upload_agency_specific_brochures" enctype="multipart/form-data" method="post">
                            <input type="hidden" name="agency_id" value="<?php echo $agency_id ?>">
                            <input type="file" id="upload_brochures" name="upload_brochures" class="submitbtnImg upload_input"> 						
                            <button style="float: left; margin-top: 5px;" class="addinput btn submitbtnImg eagdtbt btn_upload_now" id="btn_upload_now_specific_brochures" type="submit">Upload Now</button>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </div>

</div>

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


        $('.btn_add_upload').on('click', function(){
            var kini = $(this);
            kini.parents('.vad_cta_box').next('.upload_form_div').toggle('slow');
        })


        $('.delfile').on('click', function(e){

            e.preventDefault();

            var agency_id = $(this).attr('data-agencyid');
            var file = $(this).attr('data-file');

            swal({
			  title: "Delete",
			  text: "Delete Agency File?",
			  type: "warning",
			  showCancelButton: true,
			  confirmButtonClass: "btn-danger",
			  confirmButtonText: "Yes!",
			  cancelButtonText: "No, cancel!",
			  closeOnConfirm: true,
			  closeOnCancel: true
			},	function(isConfirm) {
                if(isConfirm){

                    jQuery("#load-screen").show();

                    jQuery.ajax({
                        type: "POST",
                        url: "/agency/ajax_vad_del_agency_file",
                        dataType: 'json',
                        data: { 
                            agency_id: agency_id,
                            file: file
                        }
                    }).done(function( ret ){

                        if(ret.status){
                            jQuery("#load-screen").hide();

                            swal({
                                title:"Success!",
                                text: "Agency File Successfully Deleted",
                                type: "success",
                                showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                                timer: <?php echo $this->config->item('timer') ?>
                            });

                            setTimeout(function(){ location.reload(); }, <?php echo $this->config->item('timer') ?>);	

                        }
                       
                    });

                }
            });

        })

        $('.caf_delete').on('click', function(e){

            e.preventDefault();

            var ca_id = jQuery(this).attr("data-ca_id");
            var agency_id = jQuery(this).attr("data-agency_id");
            var file_name = jQuery(this).attr("data-file_name");

            swal({
            title: "Delete",
            text: "Delete Contractor Appointment Form?",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Yes!",
            cancelButtonText: "No, cancel!",
            closeOnConfirm: true,
            closeOnCancel: true
            },	function(isConfirm) {
                if(isConfirm){

                    jQuery("#load-screen").show();

                    jQuery.ajax({
                        type: "POST",
                        url: "/agency/ajax_delete_caf",
                        dataType: 'json',
                        data: { 
                            ca_id: ca_id,
                            agency_id: agency_id,
                            file_name: file_name
                        }
                    }).done(function( ret ){

                        if(ret.status){

                            jQuery("#load-screen").hide();

                            swal({
                                title:"Success!",
                                text: "Contractor Appointment Form Successfully Deleted",
                                type: "success",
                                showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                                timer: <?php echo $this->config->item('timer') ?>
                            });

                            setTimeout(function(){ location.reload(); }, <?php echo $this->config->item('timer') ?>);	

                        }
                    
                    });

                }
            });

        })

        /** agency file upload validation */
        $('#btn_upload_now_agency_file').on('click', function(){
            var file = $('#fileupload').val();

            if(file==""){
                swal('','Agency File must not be empty','error');
                return false;
            }else{
                return true;
            }
        })

         /** contractor appointment form upload validation */
         $('#btn_upload_now_contractor_appointment_form').on('click', function(){
            var file = $('#upload_cont_app_frm').val();

            if(file==""){
                swal('','Contractor Appointment Form must not be empty','error');
                return false;
            }else{
                return true;
            }
        })

        /** specific brochures upload validation */
        $('#btn_upload_now_specific_brochures').on('click', function(){
            var file = $('#upload_brochures').val();

            if(file==""){
                swal('','Agency Specific Brochure must not be empty','error');
                return false;
            }else{
                return true;
            }
        })

        /**Delete Agency Specific Brochures */
        $('.asb_delete').on('click', function(e){

            e.preventDefault();

            var asb_id = jQuery(this).attr("data-asb_id");
            var agency_id = jQuery(this).attr("data-agency_id");
            var file_name = jQuery(this).attr("data-file_name");

            swal({
            title: "Delete",
            text: "Delete Agency Specific Brochure?",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Yes!",
            cancelButtonText: "No, cancel!",
            closeOnConfirm: true,
            closeOnCancel: true
            },	function(isConfirm) {
                if(isConfirm){

                    jQuery("#load-screen").show();

                    jQuery.ajax({
                        type: "POST",
                        url: "/agency/ajax_delete_asb",
                        dataType: 'json',
                        data: { 
                            asb_id: asb_id,
                            agency_id: agency_id,
                            file_name: file_name
                        }
                    }).done(function( ret ){

                        if(ret.status){

                            jQuery("#load-screen").hide();

                            swal({
                                title:"Success!",
                                text: "Agency Specific Brochure Successfully Deleted",
                                type: "success",
                                showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                                timer: <?php echo $this->config->item('timer') ?>
                            });

                            setTimeout(function(){ location.reload(); }, <?php echo $this->config->item('timer') ?>);	

                        }
                    
                    });

                }
            });

        })


    })
</script>