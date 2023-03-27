<style>
    .col-mdd-3{
        max-width:20%;
    }
    .btn_add_leave_link{
        margin-top:10px;
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
        'title' => $title,
        'status' => 'active',
        'link' => "/users/leave_requests"
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
		echo form_open('users/leave_requests',$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-md-8 columns">
					<div class="row">

						<div class="col-mdd-3">
							<label>Name</label>
							<select id="employee_filter" name="employee_filter" class="form-control">
								<option value="">ALL</option>
                                <?php
                                    foreach($employee->result_array() as $emp){
                                ?>
                                    <option <?php echo ($this->input->get_post('employee_filter') ==$emp['StaffID'])?'selected':'' ?> value="<?php echo $emp['StaffID']; ?>" <?php echo ($emp['StaffID']==$employee)?'selected="selected"':''; ?>><?php echo "{$emp['FirstName']} {$emp['LastName']}"; ?></option>
                                <?php
                                    }
                                ?>
							</select>
							
						</div>

						<div class="col-mdd-3">
							<label>Line Manager</label>
							<select id="line_manager_filter" name="line_manager_filter" class="form-control">
								<option value="">ALL</option>
                                <?php
                                    foreach($line_manager->result_array() as $lm){
                                ?>
                                        <option <?php echo ($this->input->get_post('line_manager_filter') ==$lm['StaffID'])?'selected':'' ?> value="<?php echo $lm['StaffID']; ?>" <?php echo ($lm['StaffID']==$line_manager)?'selected="selected"':''; ?>><?php echo "{$lm['FirstName']} {$lm['LastName']}"; ?></option>
                                <?php
                                    }
                                ?>
							</select>
						
						</div>

						<div class="col-mdd-3">
							<label>Status</label>
							<select id="status_filter" name="status_filter" class="form-control">
                                <option value="All" <?php echo ($this->input->get_post('status_filter')=='All')?'selected="selected"':''; ?>>All</option>
                                <option value="Pending" <?php echo ($this->input->get_post('status_filter')=='Pending')?'selected="selected"':''; ?>>Pending</option>
                                <option value="Approved" <?php echo ($this->input->get_post('status_filter')=='Approved')?'selected="selected"':''; ?>>Approved</option>
                                <option value="Denied" <?php echo ($this->input->get_post('status_filter')=='Denied')?'selected="selected"':''; ?>>Declined</option>
							</select>
							
						</div>

						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<input type="submit" name="search_submit" value="Search" class="btn">
						</div>
						
					</div>

				</div>
                <div class="col-md-4 columns text-right">
                     <a class="btn btn_add_leave_link" href="/users/leave_form">Add Leave Request</a>
                </div>
			</div>
			</form>
		</div>
	</header>

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<table class="table table-hover main-table">
					<thead>
						<tr>
							<th>Date of Request</th>
							<th>Employee</th>
							<th>Line Manager</th>
							<th>First Day of Leave</th>
							<th>Last Day of Leave</th>
							<th>Reason</th>
							<th>HR Approved</th>
							<th>Line Manager Approved</th>
							<th>Added to Calendar</th>
							<th>Staff notified in writing</th>
							<th>Status</th>
							<th>PDF</th>
							<th>Delete</th>
						</tr>
					</thead>

					<tbody>
                        <?php 
                            foreach($lists->result() as $row){

                        ?>

                            <tr>
                                <td>
                                    <a href="/users/leave_details/<?php echo $row->leave_id ?>"><?php echo $this->system_model->isDateNotEmpty($row->date)?$this->system_model->formatDate($row->date,'d/m/Y' ): NULL ?></a>
                                </td>
                                <td><?php echo $this->system_model->formatStaffName($row->emp_fname,$row->emp_lname); ?></td>
                                <td><?php echo $this->system_model->formatStaffName($row->lm_fname, $row->lm_lname); ?></td>
                                <td> <?php echo $this->system_model->isDateNotEmpty($row->lday_of_work)?$this->system_model->formatDate($row->lday_of_work,'d/m/Y' ): NULL ?></td>
                                <td> <?php echo $this->system_model->isDateNotEmpty($row->fday_back)?$this->system_model->formatDate($row->fday_back,'d/m/Y' ): NULL ?></td>
                                <td><?php echo $row->reason_for_leave; ?></td>
                                <td>
                                    <?php 
                                    $hlclass = '';
                                    $timestamp_str = '';
                                    if( is_numeric($row->hr_app) && $row->hr_app==1 ){
                                        $hlclass = 'approvedHL';
                                        $timestamp_str = ($this->system_model->isDateNotEmpty($row->hr_app_timestamp))? date('d/m/Y H:i',strtotime($row->hr_app_timestamp)) : NULL;
                                    }else if( is_numeric($row->hr_app) && $row->hr_app==0 ){
                                        $hlclass = 'pendingHL';
                                        $timestamp_str = ($this->system_model->isDateNotEmpty($row->hr_app_timestamp))? date('d/m/Y H:i',strtotime($row->hr_app_timestamp)) : NULL;
                                    }
                                    ?>
                                    <span class="txt_lbl <?php echo $hlclass; ?>"><?php echo $timestamp_str; ?></span>
                                </td>
                                <td>
                                    <?php 
                                    $hlclass = '';
                                    $timestamp_str = '';
                                    if( is_numeric($row->line_manager_app) && $row->line_manager_app==1 ){
                                        $hlclass = 'approvedHL';
                                        $timestamp_str = ($this->system_model->isDateNotEmpty($row->line_manager_app_timestamp))? date('d/m/Y H:i',strtotime($row->line_manager_app_timestamp)) : NULL;
                                    }else if( is_numeric($row->line_manager_app) && $row->line_manager_app==0 ){
                                        $hlclass = 'pendingHL';
                                        $timestamp_str = ($this->system_model->isDateNotEmpty($row->line_manager_app_timestamp))? date('d/m/Y H:i',strtotime($row->line_manager_app_timestamp)) : NULL;
                                    }
                                    ?>
                                    <span class="txt_lbl <?php echo $hlclass; ?>"><?php echo $timestamp_str; ?></span>
                                </td>
                                <td>
                                    <?php 
                                    $hlclass = '';
                                    $timestamp_str = '';
                                    if( is_numeric($row->added_to_cal) && $row->added_to_cal==1 ){
                                        $hlclass = 'approvedHL';
                                        $timestamp_str = date('d/m/Y H:i',strtotime($row->added_to_cal_timestamp));
                                    }else if( is_numeric($row->added_to_cal) && $row->added_to_cal_timestamp==0 ){
                                        $hlclass = 'pendingHL';
                                        $timestamp_str = date('d/m/Y H:i',strtotime($row->added_to_cal_timestamp));
                                    }
                                    ?>
                                    <span class="txt_lbl <?php echo $hlclass; ?>"><?php echo $timestamp_str; ?></span>
                                </td>
                                <td>
                                    <?php 
                                    $hlclass = '';
                                    $timestamp_str = '';
                                    if( is_numeric($row->staff_notified) && $row->staff_notified==1 ){
                                        $hlclass = 'approvedHL';
                                        $timestamp_str = date('d/m/Y H:i',strtotime($row->staff_notified_timestamp));
                                    }else if( is_numeric($row->staff_notified) && $row->staff_notified==0 ){
                                        $hlclass = 'pendingHL';
                                        $timestamp_str = date('d/m/Y H:i',strtotime($row->staff_notified_timestamp));
                                    }
                                    ?>
                                    <span class="txt_lbl <?php echo $hlclass; ?>"><?php echo $timestamp_str; ?></span>
                                </td>
                                <td>
                                    <?php
                                    switch($row->status){
                                        case 'Approved':
                                            $hl_class = 'approvedHLstatus text-green';
                                            $status_text = $row->status;
                                            $status_color = "green_mark";
                                        break;
                                        case 'Pending':
                                            $hl_class = 'pendingHLstatus text-red';
                                            $status_text = $row->status;
                                            $status_color = "grey_bg_v2";
                                        break;
                                        case 'Denied':
                                            $hl_class = 'deniedHLstatus text-red';
                                            $status_text = ucfirst('Declined');
                                            $status_color = "red_mark";
                                        break;
                                    }
                                    ?>
                                    <span class="txt_lbl <?php echo $hl_class; ?>" ><?php echo $status_text; ?></span>
                                </td>
                                <td style="padding:5px 0 0 10px;">
										<a target="_blank" href="/users/leave_details_pdf/<?php echo $row->leave_id ?>"><em class="font-icon font-icon-pdf" style="font-size:30px;color:#adb7be;"></em></a>
								</td>
                                <td>
                                <input type="hidden" class="leave_id" value="<?php echo $row->leave_id; ?>" />
                                <a href="javascript;;" class="link_delete">Delete</a>
                                </td>
                            </tr>

                        <?php
                            }
                        ?>
						
					</tbody>

				</table>
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
    This page displays all leave requests in the selected date range
	</p>

    <pre>
<code>SELECT `l`.`leave_id`, `l`.`date`, `l`.`lday_of_work`, `l`.`fday_back`, `l`.`reason_for_leave`, `l`.`hr_app`, `l`.`hr_app_timestamp`, `l`.`line_manager_app`, `l`.`line_manager_app_timestamp`, `l`.`added_to_cal`, `l`.`added_to_cal_timestamp`, `l`.`staff_notified`, `l`.`staff_notified_timestamp`, `l`.`status`, `sa_emp`.`StaffID` AS `emp_staff_id`, `sa_emp`.`FirstName` AS `emp_fname`, `sa_emp`.`LastName` AS `emp_lname`, `sa_emp`.`Email` AS `emp_email`, `sa_lm`.`StaffID` AS `sa_lm_staff_id`, `sa_lm`.`FirstName` AS `lm_fname`, `sa_lm`.`LastName` AS `lm_lname`, `sa_lm`.`Email` AS `lm_email`, `lma`.`FirstName` AS `lma_fname`, `lma`.`LastName` AS `lma_lname`, `hra`.`FirstName` AS `hra_fname`, `hra`.`LastName` AS `hra_lname`, `atc`.`FirstName` AS `atc_fname`, `atc`.`LastName` AS `atc_lname`, `sn`.`FirstName` AS `sn_fname`, `sn`.`LastName` AS `sn_lname`
FROM `leave` as `l`
LEFT JOIN `staff_accounts` as `sa_emp` ON `sa_emp`.`StaffID` = `l`.`employee`
LEFT JOIN `staff_accounts` as `sa_lm` ON `sa_lm`.`StaffID` = `l`.`line_manager`
LEFT JOIN `staff_accounts` as `lma` ON `lma`.`StaffID` = `l`.`line_manager_app_by`
LEFT JOIN `staff_accounts` as `hra` ON `hra`.`StaffID` = `l`.`hr_app_by`
LEFT JOIN `staff_accounts` as `atc` ON `atc`.`StaffID` = `l`.`added_to_cal_by`
LEFT JOIN `staff_accounts` as `sn` ON `sn`.`StaffID` = `l`.`staff_notified_by`
WHERE `l`.`active` = 1
AND `l`.`deleted` = 0
AND `l`.`leave_id` > 0
AND `l`.`country_id` = 1
AND `l`.`status` = 'Pending'
ORDER BY `l`.`date` DESC
LIMIT 50</code>
    </pre>

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
        <?php }else if(  $this->session->flashdata('status') &&  $this->session->flashdata('status') == 'error'  ){ ?>
            swal({
                title: "Error!",
                text: "<?php echo $this->session->flashdata('error_msg') ?>",
                type: "error",
                confirmButtonClass: "btn-danger"
            });
        <?php } ?>
        //success/error message sweel alert pop  end




        $('.link_delete').click(function(e){
            e.preventDefault();
            
            var leave_id = $(this).parents("tr:first").find(".leave_id").val();

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
                    function(isConfirm){
                        if(isConfirm){

                            $('#load-screen').show(); //show loader

                            // continue via ajax request
                            jQuery.ajax({
                                type: "POST",
                                url: '<?php echo base_url(); ?>users/ajax_delete_leave',
                                dataType: 'json',
                                data: { 
                                    leave_id: leave_id
                                }
                            }).done(function( ret ){	

                                    $('#load-screen').hide(); //hide loader		
                                    
                                    if(ret.status){
                                        //success popup				
                                        swal({
                                            title:"Success!",
                                            text: ret.msg,
                                            type: "success",
                                            showCancelButton: false,
                                            confirmButtonText: "OK",
                                            closeOnConfirm: false
                                        },function(isConfirm2){
                                            if(isConfirm2){ 
                                               location.reload();
                                            }
                                        });	

                                    }else{
                                        swal('','Server error please contact admin.','error');
                                    }	
                                

                            });	


                            }else{
                            return false;
                            }
                        
                    }
            );
            

        })
        
    });



</script>