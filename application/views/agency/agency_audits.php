<style>
    .col-mdd-3{
        max-width:15.5%;
    }
    #add_audit_list_div, .edit_fancybox{
        width:400px;
    }
    .readonly{
            background-color: #e9ecef!important;
    }
</style>

<div class="box-typical box-typical-padding">

    <?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => "/agency/agency_audits"
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);

?>

	<header class="box-typical-header">
        <div class="box-typical box-typical-padding">
            <?php
            $form_attr = array(
                'id' => 'jform'
            );
            echo form_open('/agency/agency_audits',$form_attr);
            ?>
                <div class="for-groupss row">
                    <div class="col-lg-10 col-md-12 columns">
                        <div class="row">
                            <div class="col-mdd-3">
                                <label for="agency_select">Submitted By</label>
                                <select id="submitted_by_filter" name="submitted_by_filter" class="form-control field_g2">
                                    <option value="">ALL</option>
                                    <?php 
                                        foreach($submitted_by_filter->result_array() as $row){ 
                                            $sel = ($this->input->get_post('submitted_by_filter')==$row['ad_submitted_by']) ? 'selected="true"' : NULL ;
                                    ?>
                                                <option <?php echo $sel; ?> value="<?php echo $row['ad_submitted_by'] ?>"><?php echo $this->system_model->formatStaffName($row['sb_FirstName'], $row['sb_LastName']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-mdd-3">
                                <label for="agency_select">Status</label>
                                <select id="status_filter" name="status_filter" class="form-control field_g2">
                                    <?php 
                                        foreach($status_filter->result_array() as $row){ 
                                            if( $this->input->get_post('status_filter') == "" ){
                                                $sel = (1==$row['status']) ? 'selected="true"' : NULL ;
                                            }else{
                                                $sel = ($this->input->get_post('status_filter')==$row['status']) ? 'selected="true"' : NULL ;
                                            }
                                    ?>
                                                <option <?php echo $sel; ?> value="<?php echo $row['status'] ?>"><?php echo $this->agency_model->getStatusName($row['status']) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-1 columns">
                                <label class="col-sm-12 form-control-label">&nbsp;</label>
                                <input class="btn" type="submit" name="btn_search" value="Search">
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </header>

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<table class="table table-hover main-table table-striped">
					<thead>
						<tr>
							<th>Date Submitted</th>
							<th>Agency Name</th>
							<th>Submitted By</th>
							<th>Assigned To</th>
							<th>Comments</th>
                            <th>Status</th>
                            <th>Target Completion Date</th>
                            <th>Action</th>
						</tr>
					</thead>

					<tbody>
                        <?php 
                        if( count($list) > 0 ){
                            foreach($lists->result_array() as $row){
                                $ad_date_created = ( $this->system_model->isDateNotEmpty($row['ad_date_created']) )?date('d/m/Y',strtotime($row['ad_date_created'])):'';
                                $ad_comp_date = ( $this->system_model->isDateNotEmpty($row['completion_date']) )?date('d/m/Y',strtotime($row['completion_date'])):'';
                                $agency_name = $row['agency_name'];
                                $submitted_by = $this->system_model->formatStaffName($row['sb_FirstName'],$row['sb_LastName']);
                                $assigned_to = $this->system_model->formatStaffName($row['at_FirstName'],$row['at_LastName']);
                                $ad_comments = $row['ad_comments'];
                                
                                $response = $row['response'];
                                $status_name = $this->agency_model->getStatusName($row['ad_status']);
                        ?>
                            <tr>
                                <td><?php echo $ad_date_created; ?></td>
                                <td><?php echo $agency_name; ?></td>
                                <td><?php echo $submitted_by; ?></td>
                                <td><?php echo $assigned_to; ?></td>
                                <td><?php echo $ad_comments; ?></td>
                                <td><?php echo $status_name; ?></td>
                                <td><?php echo $ad_comp_date; ?></td>
                                <td class="action_div">
                                    <a data-id="<?php echo $row['agency_audit_id'] ?>" data-fancybox data-toggle="tooltip" title="" data-src="#edit_fancybox_<?php echo $row['agency_audit_id'] ?>" href="javascript:;" class="btn_edit fancybox_btn action_a" data-original-title="Edit"><i class="font-icon font-icon-pencil"></i></a> | 
                                    <a data-id="<?php echo $row['agency_audit_id'] ?>" data-toggle="tooltip" title="" href="javascript:void(0)" class="btn_delete action_a" data-original-title="Delete"><span class="glyphicon glyphicon-trash"></span></a>

                                    <div id="edit_fancybox_<?php echo $row['agency_audit_id'] ?>" style="display:none;" class="edit_fancybox edit_fancybox_div">
                                        <h4>Edit Agency Audit</h4>
                                        <div class="form-group">
                                            <label>Date Submitted</label>
                                            <input type="text" readonly='true' class="form-control readonly" value="<?php echo $ad_date_created; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Agency Name</label>
                                            <select class="row_agency_name form-control">
                                                <option value="">Please Select</option>	
                                                <?php
                                                foreach( $agency_list->result_array() as $a ){ ?>
                                                    <option value="<?php echo $a['agency_id']; ?>" <?php echo ( $a['agency_id'] == $row['agency_id'] )?'selected="selected"':''; ?>><?php echo $a['agency_name']; ?></option>
                                                <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Submitted By</label>
                                            <input type="text" readonly='true' class="form-control readonly" value="<?php echo $submitted_by; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Assigned To</label>
                                            <select class="row_assigned_to form-control">
                                                <option value="">Please Select</option>	
                                                <?php
                                                foreach( $staff_list->result_array() as $sa ){ ?>
                                                    <option value="<?php echo $sa['StaffID']; ?>" <?php echo ( $sa['StaffID'] == $row['at_staff_id'] )?'selected="selected"':''; ?>><?php echo $this->system_model->formatStaffName($sa['FirstName'],$sa['LastName']); ?></option>
                                                <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Comments</label>
                                            <textarea name="row_comments"  class="form-control row_comments"><?php echo $ad_comments; ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select class="row_ad_status form-control">		
                                                <option value="1" <?php echo ($row['ad_status'] == 1)?'selected="selected"':''; ?>>Pending</option>
                                                <option value="3" <?php echo ($row['ad_status'] == 3)?'selected="selected"':''; ?>>In Progress</option>
                                                <option value="4" <?php echo ($row['ad_status'] == 4)?'selected="selected"':''; ?>>Completed</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Target Completion Date</label>
                                            <input type="text" class="row_completion_date form-control flatpickr flatpickr-input" value="<?php echo  $ad_comp_date?>" >
                                        </div>
                                        <div class="form-group">
                                            <input type="hidden" class="au_id" value="<?php echo $row['agency_audit_id']; ?>" />
                                            <button class="btn btn_update">Update</button>
                                        </div>
                                    </div>
                                </td>
                            </tr> 
                        <?php
                            }
                        }else{
                            echo "<tr><td colspan='8'>No results found</td></tr>";
                        } 
                        ?>
					</tbody>

                </table>
			</div>
            
            <div>
                <a data-fancybox href="javascript:;" data-src="#add_audit_list_div" class="btn">Add List<a>
                <div id="add_audit_list_div" style="display:none;">
                    <h4>Add Agency Audit</h4>
                    <div class="form-group">
                        <label>Agency</label>
                        <select class="form-control" name="agency_id" id="agency_id">
                            <option value="">Please Select</option
                            <?php 
                                foreach($agency_list->result_array() as $row){
                            ?>
                                     <option value="<?php echo $row['agency_id'] ?>"><?php echo $row['agency_name'] ?></option>
                            <?php
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Comments</label>
                        <textarea name="comments" id="comments" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Added By</label>
                        <select name="added_by" id="added_by" class="form-control">
                            <option value="">Please Select</option>
                            <?php 
                                foreach($staff_list->result_array() as $row){
                            ?>
                                     <option <?php echo ($this->session->staff_id ==  $row['StaffID']) ? 'selected="true"' :NULL;  ?> value="<?php echo $row['StaffID'] ?>"><?php echo $this->system_model->formatStaffName($row['FirstName'], $row['LastName']) ?></option>
                            <?php
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="button" class="btn" id="btn_save">Save</button>
                    </div>
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
	<p>
    This page assists in auditing an agency
	</p>
    <pre>
<code>SELECT `ad`.`agency_audit_id`, `ad`.`date_created` AS `ad_date_created`, `ad`.`submitted_by`, `ad`.`comments` AS `ad_comments`, `ad`.`status` AS `ad_status`, `ad`.`completion_date`, `a`.`agency_id`, `a`.`agency_name`, `sb`.`StaffID` AS `sb_staff_id`, `sb`.`FirstName` AS `sb_FirstName`, `sb`.`LastName` AS `sb_LastName`, `at`.`StaffID` AS `at_staff_id`, `at`.`FirstName` AS `at_FirstName`, `at`.`LastName` AS `at_LastName`
FROM `agency_audits` AS `ad`
LEFT JOIN `agency` as `a` ON `a`.`agency_id` = `ad`.`agency_id`
LEFT JOIN `staff_accounts` as `sb` ON `sb`.`StaffID` = `ad`.`submitted_by`
LEFT JOIN `staff_accounts` as `at` ON `at`.`StaffID` = `ad`.`assigned_to`
WHERE `ad`.`active` = 1
AND `ad`.`status` = 1
ORDER BY `ad`.`date_created` DESC
LIMIT 50</code>
    </pre>

</div>
<!-- Fancybox END -->


<script type="text/javascript">

    $(document).ready(function(){

        // delete script
        jQuery(".btn_delete").click(function(){
            var au_id = jQuery(this).attr('data-id');

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
                
                if (isConfirm) { // yes			
                    $('#load-screen').show(); //show loader
                    jQuery.ajax({
                        type: "POST",
                        url: "/agency/ajax_delete_agency_audit",
                        dataType: 'json',
                        data: { 
                            au_id: au_id
                        }
                    }).done(function( retval ) {
                        if(retval.status){
                            $('#load-screen').hide(); //hide loader
                            swal({
                                title:"Success!",
                                text: "Agency Audit Successfully Deleted",
                                type: "success",
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

        //add agency audit
        jQuery("#btn_save").click(function(){

            var agency_id = $(this).parents('#add_audit_list_div').find('#agency_id').val();
            var comments = $(this).parents('#add_audit_list_div').find('#comments').val();
            var added_by = $(this).parents('#add_audit_list_div').find('#added_by').val();

            $('#load-screen').show(); //show loader
            jQuery.ajax({
                type: "POST",
                url: "/agency/ajax_add_agency_audit",
                dataType: 'json',
                data: { 
                    agency_id: agency_id,
                    comments: comments,
                    added_by: added_by
                }
            }).done(function( retval ) {
                if(retval.status){
                    $('#load-screen').hide(); //hide loader
                    swal({
                        title:"Success!",
                        text: "Agency Audit Successfully Added",
                        type: "success",
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });	
                        var full_url = window.location.href;
                        setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);	
                }
            });	

        })

        //edit/update audit
        jQuery(".btn_update").click(function(){

            var au_id = jQuery(this).parents(".edit_fancybox_div").find(".au_id").val();
            var agency_id = jQuery(this).parents(".edit_fancybox_div").find(".row_agency_name").val();
            var assigned_to = jQuery(this).parents(".edit_fancybox_div").find(".row_assigned_to").val();
            var ad_comments = jQuery(this).parents(".edit_fancybox_div").find(".row_comments").val();
            var ad_status = jQuery(this).parents(".edit_fancybox_div").find(".row_ad_status").val();
            var ad_comp_date = jQuery(this).parents(".edit_fancybox_div").find(".row_completion_date").val();

            jQuery.ajax({
				type: "POST",
				url: "/agency/ajax_update_agency_audit",
                dataType: 'json',
				data: { 
					au_id: au_id,
					agency_id: agency_id,
					assigned_to: assigned_to,
					ad_comments: ad_comments,
					ad_status: ad_status,
					ad_comp_date: ad_comp_date
				}
			}).done(function( retval ) {
				if(retval.status){
                    $('#load-screen').hide(); //hide loader
                    swal({
                        title:"Success!",
                        text: "Agency Audit Successfully Updated",
                        type: "success",
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });	
                        var full_url = window.location.href;
                        setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);	
                }
			});	

        })

    })

</script>