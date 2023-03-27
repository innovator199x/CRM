<style>
    .api_checkbox_box{
        margin-top:10px;
    }
    .api_fancybox{width:300px;}
    .api_billable_checkbox_section, .no_bulk_match_green_tick_checkbox_section{
        margin-right:10px;
    }
    .clear_b{clear:both;}
</style>
<div class="text-left">

       <table class="table table-hover main-table">
        <thead>
            <tr>
                <th>Software</th>
                <th>Available to Connect</th>
                <th>API Active</th>
                <th>Marker Name</th>
                <th>Marker ID</th>
                <th>Edit</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($api) > 0){
             foreach($api as $api_row){ ?>
                <tr>
                    <td>
                        <?php echo $api_row['api_name'] ?>
                    </td>
                    <td>
                        <?php echo ( $api_row['active'] == 1 )?'<span class="text-green">Yes</span>':'<span class="text-red">No</span>'; ?>
                    </td>
                    <td>
                        <?php 
                            echo ( $api_row['agency_api_token_id'] > 0 )?'<span style="color:green;">Yes</span>':'<span style="color:red;">No</span>';
                        ?>
                    </td>
                    <td>
                        <?php 
                            if ($api_row['agency_api_id'] == 1) { // PME
                                $contact_json = $this->agency_model->agency_api_get_contact($agency_id, $row['pme_supplier_id']);
                                echo $contact_json->Contact->Reference;
                            }else if($api_row['agency_api_id'] == 4){ // Palace
                                $palace_diary_json = $this->palace_model->get_palace_diary_by_id($agency_id, $row['palace_diary_id']);
                                echo $palace_diary_json[0]->DiaryGroupDescription;
                            }
                        ?>
                    </td>
                    <td>
                        <?php 
                            if ($api_row['agency_api_id'] == 1) { // PMe
                                echo $row['pme_supplier_id'];
                            } else if ($api_row['agency_api_id'] == 4){ // Palace
                                echo $row['palace_diary_id'];
                            }
                        ?>
                    </td>
                    <td class="action_div">
                        <a data-toggle="tooltip" title=""  data-fancybox data-src="#api_edit_facybox_<?php echo $api_row['api_integration_id']; ?>" class="btn_edit fancybox_btn action_a" data-original-title="Edit"><i class="font-icon font-icon-pencil"></i></a> | 
                        <a data-api_token_id="<?php echo $api_row['agency_api_token_id']; ?>" data-agency_id="<?php echo $agency_id; ?>" data-api_integration_id="<?php echo $api_row['api_integration_id'] ?>" data-api_id="<?php echo $api_row['connected_service'] ?>" data-toggle="tooltip" title="" class="<?php echo ( $api_row['agency_api_token_id'] > 0 ) ? 'remove_agency_token_btn' : 'btn_delete' ?> action_a" data-original-title="<?php echo ( $api_row['agency_api_token_id'] > 0 ) ? 'Remove API Token' : 'Remove API' ?>"><span class="glyphicon glyphicon-trash"></span></a>
                        
                        <div style="display:none;" class="api_fancybox" id="api_edit_facybox_<?php echo $api_row['api_integration_id']; ?>">
                            <h4>Edit <?php echo $api_row['api_name'] ?></h4>
                            <div class="form-group">
                                <label class="form-label">Software</label>
                                <select name="edit_api_connected_service" title="Connected Service" class="edit_api_connected_service form-control">
                                    <option value="">----</option>		
                                    <?php foreach($agency_api as $agency_api_row){ ?>
                                        <option value="<?php echo $agency_api_row['agency_api_id']; ?>" <?php echo ( $agency_api_row['agency_api_id'] == $api_row['connected_service'] )?'selected="selected"':''; ?>>
                                            <?php echo $agency_api_row['api_name']; ?>
                                        </option>
                                    <?php  } ?>									
                                </select>
                                <input type="hidden" name="og_edit_api_connected_service" class="og_edit_api_connected_service" value="<?php echo $api_row['connected_service']; ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Available to Connect</label>
                                    <select name="edit_api_status" title="Connected Service" class="edit_api_status form-control">								
                                        <option value="1" <?php echo ( $api_row['active'] == 1 )?'selected="selected"':''; ?>>Yes</option>
                                        <option value="0" <?php echo ( $api_row['active'] == 0 )?'selected="selected"':''; ?>>No</option>																					
                                    </select>
                                    <input type="hidden" name="og_edit_api_status" class="og_edit_api_status" value="<?php echo $api_row['active']; ?>">
                            </div>
                            <div class="form-group">
                                <input type="hidden" class="api_integration_id" value="<?php echo $api_row['api_integration_id'] ?>">
                                <input type="hidden" class="api_id" value="<?php echo $api_row['connected_service'] ?>">
                                <input type="hidden" class="agency_api_token_id" value="<?php echo $api_row['agency_api_token_id'] ?>">
                                <button class="btn btn_update_api_integ">Update</button>
                                <button data-fancybox-close="" class="btn btn-danger">Cancel</button>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php 
            } }else{
                echo "<tr><td colspan='6'>No Data</td></tr>";
            } ?>
        </tbody>
    </table>

    <div class="vad_cta_box">
        <button data-fancybox data-src="#add_api_fancybox" class="btn">Add API integration</button>
        <br/>
        <?php 
            if(in_array($this->session->staff_id, $this->config->item('allowed_people_to_pme_unlink'))){
        ?>
                  <button id="btn_unlink_connected_api_prop" class="btn btn-danger">Unlink Connected API Properties</button>
        <?php
            }
        ?>
      
        <!-- Add API fancybox -->
        <div style="display:none" id="add_api_fancybox">
            <h4>Add API integration</h4>
            <?php echo form_open("/agency/add_agency_api_integration","id=add_api_form"); ?>
                <div class="form-group">
                    <label class="form-label">Software</label>
                    <select name="connected_service" id="api_connected_service" title="Connected Service" class="form-control connected_service">
                        <option value="">----</option>
                        <?php foreach($agency_api as $agency_api_row){ ?>
                            <option value="<?php echo $agency_api_row['agency_api_id']; ?>" >
                                <?php echo $agency_api_row['api_name']; ?>
                            </option>
                        <?php  } ?>		
                    </select>
                </div>
                <div class="form-group">
                    <button type="button" id="btn_save_api_integ" class="btn">Save</button>
                    <button data-fancybox-close="" class="btn btn-danger">Cancel</button>
                    <input type="hidden" name="agency_id" value="<?php echo $agency_id; ?>" />
                </div>
            </form>
        </div>
        <!-- Add API fancybox end -->

    </div>
    <div class="api_checkbox_box">
        <div class="form-group">
            <div class="left api_billable_checkbox_section">
                <div class="checkbox">
                    <?php
                        $is_api = ($row['api_billable']==1)? "checked='checked'" : null ;
                    ?>
                    <input <?php echo ( $this->system_model->can_edit_vad_api() == true )?null:"disabled='disabled'"; ?> <?php echo $is_api; ?> class="prop_chk" name="api_billable" type="checkbox" id="api_billable" value="1">
                    <label for="api_billable">API Billable?</label>
                </div>
                <input type="hidden" name="og_api_billable" id="og_api_billable" class="og_api_billable" value="<?php echo $row['api_billable']; ?>"/>
            </div>
            <div> <span style="display:none;" id="api_billable_green_tick" class="fa fa-check-square text-green"></span></div>
        </div>
        <div class="clear_b"></div>
        <div class="form-group">
            <div class="left no_bulk_match_green_tick_checkbox_section">
                <div class="checkbox">
                    <?php
                        $is_no_bulk_match = ($row['no_bulk_match']==1)? "checked='checked'" : null ;
                    ?>
                    <input  <?php echo ( $this->system_model->can_edit_vad_api() == true )?null:"disabled='disabled'"; ?> <?php echo $is_no_bulk_match; ?> class="prop_chk" name="no_bulk_match" type="checkbox" id="no_bulk_match" value="1">
                    <label for="no_bulk_match">Generate warning on bulk match?</label>
                </div>
                <input type="hidden" name="og_no_bulk_match" id="og_no_bulk_match" class="og_no_bulk_match" value="<?php echo $row['no_bulk_match']; ?>"/>
            </div>
            <div> <span style="display:none;" id="no_bulk_match_green_tick" class="fa fa-check-square text-green"></span></div>
        </div>
        <div class="clear_b"></div>
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

        jQuery("#api_connected_service").change(function(){

            var obj = jQuery(this);
            var connected_service = obj.val();
            var agency_id = <?php echo $agency_id  ?>;

            jQuery("#load-screen").show();
            jQuery.ajax({
                    type: "POST",
                    url: "/agency/ajax_check_agency_api_integration_selected",
                    data: { 
                        agency_id: agency_id,
                        connected_service: connected_service
                    }
                }).done(function( ret ){
                    
                    jQuery("#load-screen").hide();
                    if( parseInt(ret) > 0 ){
                        swal('','API service already selected','error');
                        obj.find("option:eq(0)").prop("selected",true); // unselect
                    }

                });	

        });

        
        $('#btn_save_api_integ').on('click',function(){

            var api = $('#api_connected_service').val();

            var err = "";
            var submitcount = 0 ;

            if(api==""){
                err+="Software must not be empty. \n";
            }

            if(err!=""){
                swal('',err,'error');
                return false;
            }

            if(submitcount==0){

                submitcount++;
                $('#add_api_form').submit();

            }else{
                swal('','Form submission is in progress.');
                return false;
            }

        })

        jQuery("#btn_unlink_connected_api_prop").click(function(){

            swal(
				{
					title: "",
					text: "This will unlink ALL property that are connected to API under this agency. Are you sure you want to proceed?",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-success",
					confirmButtonText: "Yes",
					cancelButtonText: "No, Cancel!",
					closeOnConfirm: false,
					closeOnCancel: true,
				},
				function(isConfirm){
					if(isConfirm){
						$('#load-screen').show(); //show loader
						swal.close();
								jQuery.ajax({
								type: "POST",
								url: "<?php echo base_url('/agency/ajax_unlink_connected_api_prop') ?>",
								dataType: 'json',
								data: {
									agency_id: <?php echo $agency_id; ?>
								}
								}).done(function(data){
									
									if(data.status){
										$('#load-screen').hide(); //hide loader
                                        swal({
                                            title:"Success!",
                                            text: "Connected PropertyMe Properties has been unlinked",
                                            type: "success",
                                            showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                                            timer: <?php echo $this->config->item('timer') ?>
                                        });
                                        setTimeout(function(){ location.reload(); }, <?php echo $this->config->item('timer') ?>);	     
									}
								});
							}
					
				}
			);

        });

        /** API BILlABLE CHECKBOX */
        jQuery("#api_billable").change(function(){

            var node = jQuery(this);
            var api_billable = ( node.prop("checked") == true )?1:0;
            var og_api_billable = $('#og_api_billable').val();

            jQuery("#load-screen").show();
            jQuery.ajax({
                type: "POST",
                url: "/agency/ajax_api_billable_toggle",
                dataType: 'json',
                data: {
                    agency_id: <?php echo $agency_id; ?>,
                    api_billable: api_billable,
                    og_api_billable: og_api_billable
                }
            }).done(function( ret ) {
                if(ret.status){
                    jQuery("#load-screen").hide();
                    jQuery("#og_api_billable").val(ret.api_val);
                    jQuery("#api_billable_green_tick").show();
                    setTimeout(function(){ 
                        jQuery("#api_billable_green_tick").hide();
                    }, 2000);
                }else{
                    swal('','Error: Please contact admin.','error');
                }
            });	

        });

        jQuery("#no_bulk_match").change(function(){

            var node = jQuery(this);
            var no_bulk_match = ( node.prop("checked") == true )?1:0;
            var og_no_bulk_match = $("#og_no_bulk_match").val();

            jQuery("#load-screen").show();
            jQuery.ajax({
                type: "POST",
                url: "/agency/ajax_no_bulk_match_toggle",
                dataType: 'json',
                data: { 
                    agency_id: <?php echo $agency_id; ?>,
                    no_bulk_match: no_bulk_match,
                    og_no_bulk_match: og_no_bulk_match
                }
            }).done(function( ret ) {
                if(ret.status){
                    jQuery("#load-screen").hide();
                    jQuery("#og_no_bulk_match").val(ret.no_bulk_match_val);
                    jQuery("#no_bulk_match_green_tick").show();
                    setTimeout(function(){ 
                        jQuery("#no_bulk_match_green_tick").hide();
                    }, 2000);
                }
            });	

        });


        // delete api integration
        jQuery(".btn_delete").click(function(){
            
            var api_integration_id = $(this).attr('data-api_integration_id');
            var api_id = $(this).attr('data-api_id');
            var agency_id = <?php echo $agency_id; ?>;

            swal(
				{
					title: "",
					text: "This will delete this API integration. Proceed?",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-success",
					confirmButtonText: "Yes",
					cancelButtonText: "No, Cancel!",
					closeOnConfirm: false,
					closeOnCancel: true,
				},
				function(isConfirm){
					if(isConfirm){
						$('#load-screen').show(); //show loader
                        jQuery.ajax({
                            type: "POST",
                            url: "/agency/ajax_delete_agency_api_integration",
                            dataType: 'json',
                            data: { 
                                api_integration_id: api_integration_id,
                                api_id: api_id,
                                agency_id: agency_id
                            }
                        }).done(function( ret ) {
                            if(ret.status){
                                $('#load-screen').hide(); //hide loader
                                swal({
                                    title:"Success!",
                                    text: "API Integration Successfully Deleted",
                                    type: "success",
                                    showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                                    timer: <?php echo $this->config->item('timer') ?>
                                });
                                setTimeout(function(){ location.reload(); }, <?php echo $this->config->item('timer') ?>);	
                            }
                        });	
					}
				}
			);

        });

        //Delete API TOKEN
        jQuery(".remove_agency_token_btn").click(function(){
            
            var agency_api_token_id = $(this).attr('data-api_token_id');
            var api_id = $(this).attr('data-api_id');
            var agency_id = <?php echo $agency_id; ?>;

            swal(
				{
					title: "",
					text: "This will delete this API Token. Proceed?",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-success",
					confirmButtonText: "Yes",
					cancelButtonText: "No, Cancel!",
					closeOnConfirm: false,
					closeOnCancel: true,
				},
				function(isConfirm){
					if(isConfirm){
						$('#load-screen').show(); //show loader
                        jQuery.ajax({
                            type: "POST",
                            url: "/agency/ajax_delete_agency_api_token",
                            dataType: 'json',
                            data: { 
                                agency_api_token_id: agency_api_token_id,
                                api_id: api_id,
                                agency_id: <?php echo $agency_id; ?>
                            }
                        }).done(function( ret ) {
                            if(ret.status){
                                $('#load-screen').hide(); //hide loader
                                swal({
                                    title:"Success!",
                                    text: "API Token Successfully Deleted",
                                    type: "success",
                                    showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                                    timer: <?php echo $this->config->item('timer') ?>
                                });
                                setTimeout(function(){ location.reload(); }, <?php echo $this->config->item('timer') ?>);	
                            }
                        });	
					}
				}
			);

        });

        jQuery(".btn_update_api_integ").click(function(){
            
            var obj = $(this);
            var api_integration_id = obj.parents('.api_fancybox').find('.api_integration_id').val();
            var agency_api_token_id = obj.parents('.api_fancybox').find('.agency_api_token_id').val();
            var connected_service = obj.parents('.api_fancybox').find('.edit_api_connected_service').val();
            var og_edit_api_connected_service = obj.parents('.api_fancybox').find('.og_edit_api_connected_service').val();
            var status = obj.parents('.api_fancybox').find('.edit_api_status').val();
            var og_edit_api_status = obj.parents('.api_fancybox').find('.og_edit_api_status').val();
            var error = "";
            
            if( connected_service == "" ){
                error += "Connected Service is required\n";
            }

            if(  status == 0 && agency_api_token_id != '' ){
                error += "Cannot Update Available to Connect to NO if agency access token exist. remove it first";
            }
            
            if(error != ""){
                swal('',error,'error');
                return false;
            }

            $('#load-screen').show();
            jQuery.ajax({
                    type: "POST",
                    url: "/agency/ajax_update_agency_api_integration",
                    dataType: 'json',
                    data: { 
                        api_integration_id: api_integration_id,
                        connected_service: connected_service,
                        og_edit_api_connected_service: og_edit_api_connected_service,
                        status: status,
                        og_edit_api_status: og_edit_api_status,
                        agency_id: <?php echo $agency_id; ?>

                    }
                }).done(function( ret ) {
                    if(ret.status){
                        $.fancybox.close();
                        $('#load-screen').hide(); //hide loader
                        swal({
                            title:"Success!",
                            text: " API Integration Successfully Updated",
                            type: "success",
                            showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                            timer: <?php echo $this->config->item('timer') ?>
                        });
                        setTimeout(function(){ location.reload(); }, <?php echo $this->config->item('timer') ?>);	
                    }
                });	

        });

        jQuery(".edit_api_connected_service").change(function(){

            var obj = jQuery(this);
            var connected_service = obj.val();
            var orig_connected_service = obj.parents('.api_fancybox').find('.api_id').val();
            var agency_id = <?php echo $agency_id  ?>;

            jQuery("#load-screen").show();
            jQuery.ajax({
                    type: "POST",
                    url: "/agency/ajax_check_agency_api_integration_selected",
                    data: { 
                        agency_id: agency_id,
                        connected_service: connected_service
                    }
                }).done(function( ret ){
                    
                    jQuery("#load-screen").hide();
                    if( parseInt(ret) > 0 ){
                        if(connected_service!=orig_connected_service){
                            swal('','API service already exist','error');
                            obj.find('option[value='+orig_connected_service+']').prop("selected",true);
                        }   
                    }

                });	

        });
        


    })

</script>