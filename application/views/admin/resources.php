
<link rel="stylesheet" href="/inc/css/separate/vendor/select2.min.css">
<style>
.popover-content{display:none;}
.font-icon{color:#00a8ff}
.resource_header_title{margin-bottom:10px;}
.res_hid_data{display:none;}
.edit_due_date{width:100%;}

.select2-container--arrow .select2-selection--multiple .select2-selection__choice, .select2-container--default .select2-selection--multiple .select2-selection__choice, .select2-container--white .select2-selection--multiple .select2-selection__choice{
    color: #fff;
    background: #919fa9;
    border: none;
    font-weight: 600;
    font-size: 1rem;
    padding: 0 2rem 0 .5rem;
    height: 26px;
    line-height: 26px;
    position: relative;
}
.select2-container--arrow .select2-results__option--highlighted[aria-selected], .select2-container--default .select2-results__option--highlighted[aria-selected], .select2-container--white .select2-results__option--highlighted[aria-selected]{
    color:#00a8ff;
}
.select2-container--arrow .select2-selection--multiple, .select2-container--default .select2-selection--multiple, .select2-container--white .select2-selection--multiple{
    border-color: #d8e2e7;
    min-height: 38px;
}
.select2-container--default .select2-selection--multiple .select2-selection__rendered{
    box-sizing: border-box;
    list-style: none;
    margin: 0;
    padding: 0 5px;
    width: 100%;
}
.select2-container--default.select2-container--focus .select2-selection--multiple{
    border-color:#c5d6de!important;
}
.btn_add_new, .btn_add_edit_heading{
    margin-top:13px;
}
.hide_div{display:none;}
.add_state_checkbox_div{float:left;margin-right:10px!important;}
.checkalldiv{margin-bottom:5px!important;}
.btn_del_rh{
    font-size: 28px;
    margin-top: 6px;
}
</style>
<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
    $bc_items = array(
        array(
            'title' => $title,
            'status' => 'active',
            'link' =>  $uri
        )
    );
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
    ?>

    <div class="alert alert-info alert-icon alert-close alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
            <i class="font-icon font-icon-warning"></i>
            <strong>Important</strong><br/>
            All Documents on this page are displayed on agency site. The title used will also be used on the agency site, so please pick the title carefully.
	</div>
    
    <header class="box-typical-header">

        <div class="box-typical box-typical-padding">
            <?php
        $form_attr = array(
            'id' => 'jform'
        );
        echo form_open('/admin/resources',$form_attr);
        ?>
            <div class="for-groupss row">
                <div class="col-md-8 columns">
                    <div class="row">

                        <div class="col-mdd-3">
                            <label for="service_select">State</label>
                            <select id="state_filter" name="state_filter" class="form-control field_g2">
                                <option value="">ALL</option>
                                <?php
                                    $state = $this->properties_model->getCountryState();
                                    foreach($state->result_array()as $row){
                                        $sel = ($this->input->get_post('state_filter')==$row['StateID'])? 'selected' : NULL;
                                ?>
                                        <option <?php echo $sel; ?> value="<?php echo $row['StateID'] ?>"><?php echo $row['state'] ?></option>
                                <?php
                                    }
                                ?>
                            </select>
                        </div>	

                        <div class="col-md-1 columns">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <button type="submit" class="btn btn-inline">Go</button>
                        </div>
                        
                    </div>

                </div>

                <div class="col-md-4 columns text-right">
                    <a href="#add_new_fancybox"  data-toggle="tooltip" class="btn fancybox_btn btn_add_new">Add New</a>
                    &nbsp;
                    <a href="#add_edit_heading_fancybox" data-toggle="tooltip" class="btn fancybox_btn btn_add_edit_heading">Add/Edit Heading</a>
                </div>
            </div>
            </form>
        </div>
    </header>

	<section>
		<div class="body-typical-body">

        <?php 
            foreach($get_resources_header->result_array() as $h_row){ 
                
            //get resources list
            $params = array(
                'sel_query' => "*, rh.name as h_name",
                'state_filter' => $this->input->post('state_filter'),
                'header_id' =>  $h_row['resources_header_id']
            );
            $resource_sql = $this->admin_model->jgetResourceList($params);
        
        ?>

        <h4 class="resource_header_title"><?php echo $h_row['name'] ?></h4>
        <table class="table table-hover main-table table-striped">
            <thead>
                <tr>    
                    <th style="width:450px;">Document Name/URL</th> 
                    <th style="width:350px;">Title on Agency Site</th> 
                    <?php
                        if($this->admin_model->ifCountryHasState()){
                    ?>
                            <th style="width:230px;">State</th>   
                    <?php
                        }
                    ?>                               						                           
                    <th>Due Date</th>                            						                           
                    <th>Last Updated</th>                            						                           
                    <th style="width:100px" class="text-center">Edit/Delete</th>                            						                                               						                           
                </tr>
            </thead>
            <tbody>
                <?php 
                    if($resource_sql->num_rows()>0){
                        foreach($resource_sql->result_array() as $row){
                ?>
                            <tr>
                                <td>
                                    <input type="hidden" class="type" value="<?php echo $row['type']; ?>" />

                                    <?php
                                    if($row['type']==1){ ?>
                                        <input type="hidden" class="del_path" value="<?php echo $row['path']; ?>/<?php echo $row['filename']; ?>" />
                                        <a href="<?php echo $agent_documents_path."".$row['filename']; ?>"><?php echo $row['filename']; ?></a>
                                    <?php	
                                    }else{ ?>
                                        <a href="<?php echo $row['url']; ?>"><?php echo $row['url']; ?></a>
                                    <?php	
                                    }
                                    ?>

                                </td>
                                <td>
                                    <span class="res_lbl"><?php echo $row['title']; ?></span>
                                </td>
                                <?php 
                                if($this->admin_model->ifCountryHasState()){
                                ?>
                                    <td>
                                        <div class="rowsss">
                                            <span class="res_lbl">
                                            <?php
                                                if($row['states']!=""){
                                                    $s_sql = $this->db->query("
                                                        SELECT *
                                                        FROM `states_def`
                                                        WHERE `StateID` IN({$row['states']})
                                                    ");
                                                    $s_arr = array();
                                                    foreach($s_sql->result_array() as $s){
                                                        $s_arr[] = $s['state'];
                                                    }
                                                    echo implode(',',$s_arr);
                                                }else{
                                                    echo "N/A";
                                                }																						
                                            ?>									
                                        </div>
                                    </td>
                                <?php
                                }
                                ?>
                                
                                <td>
                                    <?php 
                                    $due_date = ($this->system_model->isDateNotEmpty($row['due_date'])==true)?$this->system_model->formatDate($row['due_date'],'d/m/Y'):''
                                    ?>
                                    <span class="res_lbl">
                                        <?php echo $due_date; ?>
                                    </span>
                                </td>
                                <td><?php echo ($this->system_model->isDateNotEmpty($row['date'])==true)?$this->system_model->formatDate($row['date'],'d/m/Y'):''; ?></td>
                                <td class="edit_div text-center">
                                <a href="#edit_fancybox_<?php echo $row['resources_id'] ?>" data-toggle="tooltip" title="Edit" class="btn_edit action_a fancybox_btn"><i class="font-icon font-icon-pencil"></i></a>
                                | <a href="#" data-resourceid="<?php echo $row['resources_id'] ?>" title="Delete" class="btn_delelete_resources action_a"><i class="font-icon font-icon-trash"></i></a>
                                
                                <!-- EDIT FANCYBOX -->
                                
                                <div class="update_btn_div" style="display:none;width:400px;" id="edit_fancybox_<?php echo $row['resources_id'] ?>">
                                    <h4>Edit</h4>
                                    <div class="form-group">
                                        <label>Title on Agency Site</label>
                                        <input type="text" class="edit_title form-control" value="<?php echo $row['title']; ?>" />
                                    </div>    
                                    <div class="form-group">
                                        <label>State</label>
                                        <select class="select2 ttmoselect form-control edit_state" multiple="multiple" id="edit_state" name="edit_state">
                                        <?php
                                            foreach($state->result_array()as $edit_state_row){
                                                $sel_states = explode(",",$row['states']);
                                        ?>
                                                <option value="<?php echo $edit_state_row['StateID']; ?>" <?php echo (in_array($edit_state_row['StateID'],$sel_states))?'selected="selected"':''; ?>><?php echo $edit_state_row['state']; ?></option>
                                        <?php
                                            }
                                        ?>
                                        </select>
                                    </div>  
                                    <div class="form-group">
                                        <label>Due Date</label>
                                        <input class="flatpickr form-control flatpickr-input edit_due_date" data-allow-input="true" id="flatpickr" type="text" value="<?php echo $due_date; ?>">
                                    </div> 
                                    <div class="form-group">
                                        <label>Header</label>
                                        <select id="edit_heading" class="edit_heading form-control">
										    <option value="">Please Select</option>	
                                            <?php
                                                foreach($get_resources_header->result_array() as $header){
                                            ?>
                                                    <option class="form-control" value="<?php echo $header['resources_header_id']; ?>" <?php echo ($header['resources_header_id']==$row['resources_header_id'])?'selected="selected"':''; ?>><?php echo $header['name']; ?></option>
                                            <?php
                                                }
                                            ?>
									    </select>
                                    </div> 
                                    <div class="form-group">
                                        <input type="hidden" class="resources_id" value="<?php echo $row['resources_id']; ?>" />
                                        <button class="btn btn_update" type="button">Update</button>
                                    </div> 
                                </div>

                                <!-- EDIT FANCYBOX END -->
                                </td>
                            </tr>
                <?php
                        }
                    }else{
                        echo "<tr><td colspan='6'>Empty, no documents uploaded yet</td></tr>";
                    }
                ?>
            </tbody>
        </table>	


        <?php } ?>

        <!-- Add New Resrouces Fancybox -->
        <div id="add_new_fancybox" style="display:none;width:600px;">
            <h4>Add New</h4>
            <?php echo form_open_multipart('/admin/insert_new_resources', 'id=add_new_resources_form'); ?>
                <div class="form-group">
                        <label>Please select upload type</label>
                        <select class="form-control" id="sel_type" name="sel_type">
                            <option value="">Please Select</option>
                            <option value="1">Upload File</option>
                            <option value="2">Upload Link</option>
                        </select>
                </div>
                <div id="upload_file_div" class="hide_div">
                    <div class="form-group upload_type_file_div hide_div">
                        <label>File</label>
                        <input type="file" name="file" id="file" class="file uploadfile form-control">
                    </div>
                    <div class="form-group upload_type_link_div hide_div">
                        <label>Url</label>
                        <input type="text" name="url" id="url" class="url form-control">
                    </div>
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" id="title" class="title form-control">
                    </div>
                    <div class="form-group">
                        <label>Heading</label>
                        <select class="form-control" name="heading" id="heading">
                            <option value="">Please Select</option>
                                <?php
                                    foreach($get_resources_header->result_array() as $header){
                                ?>
                                        <option class="form-control" value="<?php echo $header['resources_header_id']; ?>" ><?php echo $header['name']; ?></option>
                                <?php
                                    }
                                ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="vsud-inner">
                            <label>States</label>
                            <div>
                                <div class="checkbox checkalldiv" style="margin:0;">
                                    <input name="chk_all" type="checkbox" id="check-all-checkbox-state">
                                    <label for="check-all-checkbox-state">All</label>
                                </div>
                            </div>
                            <?php
                                foreach($state->result_array()as $edit_state_row){
                            ?>
                                <div class="checkbox add_state_checkbox_div" style="margin:0;">
                                    <input id="tt_<?=$edit_state_row['StateID'];?>" type="checkbox"  name="states[]" class="states add_state_checkbox" value="<?=$edit_state_row['StateID'];?>">
                                    <label for="tt_<?=$edit_state_row['StateID'];?>"><?=$edit_state_row['state'];?></label>
                                </div>
                            <?php
                                }
                            ?>
                            <div style="clear:both;"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Due Date</label>
                        <input type="text" name="due_date" class="due_date flatpickr form-control flatpickr-input" id="due_date" data-allow-input="true">
                    </div>
                    <div class="form-group">
                        <button type="button" class="btn_upload_file btn">Upload</button>
                    </div>
                </div>
            </form>
        </div>
        <!-- Add New Resrouces Fancybox END -->

         <!-- Add/Edit Heading Fancybox -->
         <div id="add_edit_heading_fancybox" style="display:none;width:500px;">
                    <h4>Add/Edit Heading</h4>
                    
                    <?php 
                    echo form_open('/admin/resources_update_header','id=update_header_form');
                    foreach($this->admin_model->get_resources_header()->result_array() as $h){ 
                    ?>
                        <div class="form-group row rs_header_parent">
                            <div class="col-md-10">
                                <input type="hidden" class="rh_id" name="rh_id[]"  value="<?php echo $h['resources_header_id']; ?>">
                                <input type="text" class="fname form-control pm_name" name="edit_name[]" value="<?php echo $h['name']; ?>">	
                            </div>
                            <div class="col-md-2">
                                <a href="#" class="btn_del_rh"><i class="font-icon font-icon-close"></i></a>
                            </div>
                        </div>
                    <?php 
                    } 
                    ?>

                    <div class="form-group">
                    <input type="submit" class="btn" value="Update" name="btn_update_sr">
                    </div>
                    </form>

        </div>
         <!-- Add/Edit heading Fancybox End -->
           
		</div>
	</section>

</div>


<!-- Fancybox START -->

<!-- ABOUT TEXT -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
    This page manages documents available on the agency portal. 
	</p>

</div>

<script type="text/javascript" src="/inc/js/lib/summernote/summernote.min.js"></script>
<script type="text/javascript">

$(function(){

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

    //Update Resources
    jQuery(".btn_update").click(function(e){
        
        e.preventDefault();
        var resources_id = jQuery(this).parents(".update_btn_div").find(".resources_id").val();
        var title = jQuery(this).parents(".update_btn_div").find(".edit_title").val();
        var heading = jQuery(this).parents(".update_btn_div").find(".edit_heading").val();
        var state = jQuery(this).parents(".update_btn_div").find(".edit_state").val();
        var due_date = jQuery(this).parents(".update_btn_div").find(".edit_due_date").val();
        
        $('#load-screen').show();

        jQuery.ajax({
            type: "POST",
            url: "/admin/ajax_update_resources",
            dataType: 'json',
            data: { 
                resources_id: resources_id,
                title: title,
                heading: heading,
                state: state,
                due_date: due_date
            }
        }).done(function( ret ) {	
            $('#load-screen').hide(); //hide loader		

             if(ret.status){
                swal({
                    title:"Success!",
                    text: "Update Successful",
                    type: "success",
                    showCancelButton: false,
                    confirmButtonText: "OK",
                    closeOnConfirm: false,
                    showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                    timer: <?php echo $this->config->item('timer') ?>
                });	
                
                var full_url = window.location.href;
                setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);	
             }
        });		
		
	});

    //Delete Resources
    jQuery(".btn_delelete_resources").click(function(e){

            e.preventDefault();
			var resources_id = $(this).attr('data-resourceid');
			var type = jQuery(this).parents("tr:first").find(".type").val();
			var del_path = jQuery(this).parents("tr:first").find(".del_path").val();

            swal({
                title: "Warning!",
                text: "Are you sure you want to delete?",
                type: "warning",
                showCancelButton: true,
                cancelButtonText: "Cancel!",
                cancelButtonClass: "btn-danger",
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes",                       
                closeOnConfirm: false,
            },
            function(isConfirm) {
                if (isConfirm) {
                    $('#load-screen').show();
                    jQuery.ajax({
                        type: "POST",
                        url: "/admin/ajax_delete_resources",
                        dataType: 'json',
                        data: {
                            resources_id: resources_id,
                            type: type,
                            del_path: del_path
                        }
                    }).done(function( ret ) {	
                        $('#load-screen').hide();
                        if(ret.status){
                            swal({
                                title:"Success!",
                                text: "Delete Successful",
                                type: "success",
                                showCancelButton: false,
                                confirmButtonText: "OK",
                                closeOnConfirm: false,  
                                showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                                timer: <?php echo $this->config->item('timer') ?>
                            });	
                            
                            var full_url = window.location.href;
                            setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);	
                        }
                    });	
                }
            });		

	});

    $('#sel_type').on('change',function(){
        var thisval = $(this).val();
        if(thisval==1){
            $('#upload_file_div').show();
            $('.upload_type_file_div').show();
            $('.upload_type_link_div').hide();

            //required tweak
            $('#file').addClass('required');
            $('#url').removeClass('required');
        }else{
            $('#upload_file_div').show();
            $('.upload_type_link_div').show();
            $('.upload_type_file_div').hide();

            //required tweak
            $('#url').addClass('required');
            $('#file').removeClass('required');
        }
    })

    jQuery("#check-all-checkbox-state").click(function(){
        if(jQuery(this).prop("checked")==true){
            jQuery(this).parents(".vsud-inner").find(".states").prop("checked",true);
        }else{
            jQuery(this).parents(".vsud-inner").find(".states").prop("checked",false);
        }
	});

    $('.btn_upload_file').on('click',function(){
        var submitcount = 0;
        var papa = $(this).parents('#add_new_fancybox');
        var sel_type = papa.find('#sel_type').val();
        var file = papa.find('#file').val();
        var url = papa.find('#url').val();
        var title = papa.find('#title').val();
        var heading = papa.find('#heading').val();
        var states = papa.find('.add_state_checkbox').val();
        var due_date = papa.find('#due_date').val();

        var err = "";

        if(file=="" && papa.find('#file').hasClass('required')){
            err+="File must not be empty\n";
        }
        if(url=="" && papa.find('#url').hasClass('required')){
            err+="Url must not be empty\n";
        }
        if(title==""){
            err+="Title must not be empty\n";
        }
        if(heading==""){
            err+="Heading must not be empty\n";
        }
        
        if(err!=""){
            swal('',err,'error');
            return false;
        }
        
        if(submitcount==0){
            submitcount++;
            $('#add_new_resources_form').submit();
            return false;
        }else{
            swal('','Form submission is in progress','error');
            return false;
        }
        

    })

    // delete header
	jQuery(".btn_del_rh").click(function(e){
        e.preventDefault();
        var rh_id = jQuery(this).parents(".rs_header_parent").find(".rh_id").val();

        swal({
                title: "Warning!",
                text: "Are you sure you want to delete?",
                type: "warning",
                showCancelButton: true,
                cancelButtonText: "Cancel!",
                cancelButtonClass: "btn-danger",
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes",                       
                closeOnConfirm: false,
            },
            function(isConfirm) {
                if (isConfirm) {
                    $('#load-screen').show();
                    jQuery.ajax({
                        type: "POST",
                        url: "/admin/ajax_delete_resources_header",
                        dataType: 'json',
                        data: {
                            rh_id: rh_id
                        }
                    }).done(function( ret ) {	
                        $('#load-screen').hide();
                        if(ret.status){
                            swal({
                                title:"Success!",
                                text: "Delete Successful",
                                type: "success",
                                showCancelButton: false,
                                confirmButtonText: "OK",
                                closeOnConfirm: false,  
                                showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                                timer: <?php echo $this->config->item('timer') ?>
                            });	
                            
                            var full_url = window.location.href;
                            setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);	
                        }
                    });	
                }
            });		
	});
    
    
});
</script>

