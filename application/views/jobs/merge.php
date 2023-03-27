<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/jobs/merge"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	<section>

		<div class="row">
			<div class="col-md-12 columns dark text-center">
				<div class="color-breadcrumb">
					<a href="#" <?php echo($email_stats[0]['result']==$email_stats[1]['result'])?'class="breadcrumb-active"':'class="bnt_email_all_certi_invoice"'; ?> >
						Email ALL Certificates/Invoices (<span lid="span_email_all_certi_invoice"><?php echo $email_stats[0]['result'] . "</span>/" . $email_stats[1]['result']; ?> Sent)
					</a>
					<a id="btn_sms_tenant" href="javascript:void(0);" <?php echo ($email_stats[1]['result']==$this->jobs_model->mergeJobSentSmsCount())?'class="breadcrumb-active"':''; ?>>SMS Tenants (<?php echo $this->jobs_model->mergeJobSentSmsCount(); ?>/<?php echo $email_stats[1]['result']; ?> Sent)</a>
					
		

					<a href="/export_myob.php">MYOB Export</a>
					<a href="/mark_completed.php" onclick="return confirm('Are you sure you want to mark all jobs as completed?');" <?php echo ($lists->num_rows()==0)?'class="breadcrumb-active"':''; ?>>Mark ALL Completed</a> 
				</div>
			</div>

		</div>

	</section>
	<header class="box-typical-header">
		<div class="box-typical box-typical-padding">
			<?php
		$form_attr = array(
			'id' => 'jform'
		);
		echo form_open('/jobs/merge',$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-md-8 columns">
					<div class="row">
						<div class="col-mdd-3">
							<label for="agency_select">Agency</label>
							<select id="agency_filter" name="agency_filter" class="form-control field_g2">
								<option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>
						</div>

						<div class="col-mdd-3">
							<label for="jobtype_select">Job Type</label>
							<select id="job_type_filter" name="job_type_filter" class="form-control field_g2">
								<option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>
						</div>

						<div class="col-mdd-3">
							<label for="service_select">Service</label>
							<select id="service_filter" name="service_filter" class="form-control field_g2">
								<option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>
						</div>

						<div class="col-mdd-3">
							<label for="service_select"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></label>
							<select id="state_filter" name="state_filter" class="form-control">
								<option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>
						</div>

						<div class="col-mdd-3">
							<label for="date_select">Date</label>
							<input placeholder="ALL" name="date_filter" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $this->input->get_post('date'); ?>">
						</div>

						<div class="col-mdd-3">
							<label for="phrase_select">Phrase</label>
							<input placeholder="ALL" type="text" name="search_filter" class="form-control" value="<?php echo $this->input->get_post('search'); ?>" />
						</div>

						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<button type="submit" class="btn btn-inline">Search</button>
						</div>
						
					</div>

				</div>
				<div class="col-md-4 columns text-right">
					<?php 

						$ue_sql = $this->db->select('cron_merged_cert')->from('crm_settings')->where(array('country_id'=>$this->config->item('country')))->get();
						$ue = $ue_sql->row_array();
						$ae_val = $ue['cron_merged_cert'];
						if( $ae_val==1 ){
							$ae_txt = 'Active';
							$ae_color = 'green';
							$is_checked = 'checked="checked"';
						}else{
							$ae_txt = 'Inactive';
							$ae_color = 'red';
							$is_checked = '';
						}					
					?>
					<div class="checkbox" style="margin-top:30px;">
					<input type="checkbox" id="chk_cron_merged_cert_toggle" <?php echo $is_checked; ?> /> 
						<label for="chk_cron_merged_cert_toggle"> <span style="color:<?php echo $ae_color; ?>">Auto Emails <?php echo $ae_txt; ?></span></label>
					</div>	

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
							<th>Date</th>
							<th>Job Type</th>
							<th>Age</th>
							<th>Service</th>
							<th>Price</th>
							<th>Address</th>
							<th><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></th>
							<th>Agency</th>
                            <th>Job#</th>
                            <th>Email Sent</th>
                            <th>SMS Sent</th>
						</tr>
					</thead>

					<tbody>
						<?php
							if($lists->num_rows()>0){
						foreach($lists->result_array() as $list_item): 			
						?>
						<tr>
							<td>
                            <?php echo $this->system_model->formatDate($list_item['j_date'],'d/m/Y'); ?>
							</td>
							<td>
                            <?php echo $this->gherxlib->getJobTypeAbbrv($list_item['j_type']); ?>
							</td>
							<td>
                            <?php
							echo $this->gherxlib->getAge($list_item['j_created']);
							?>
							</td>
							<td>
							<img data-toggle="tooltip" title="<?php echo $list_item['ajt_type'] ?>" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($list_item['j_service']); ?>" />
							</td>
							<td>
							<?php
							 $price =  $this->system_model->getJobAmountGrandTotal($list_item['jid'], $this->config->item('country')); 
							 echo "$".number_format($price,2);
							?>
							</td>
							<td>
							<?php 
							/*
							<a href="<?php echo base_url('/properties/view_property_details')."/".$list_item["prop_id"]?>"><?php echo $list_item['p_address_1']." ".$list_item['p_address_2']." ".$list_item['p_address_3']; ?></a>
							*/
							$prop_address = $list_item['p_address_1']." ".$list_item['p_address_2'].", ".$list_item['p_address_3'];
							echo $this->gherxlib->crmLink('vpd',$list_item['prop_id'],$prop_address);

							?>
							</td>
							<td>
							<?php echo $list_item['p_state']; ?>
							</td>
							<td>
                            <?php echo $list_item['agency_name']; ?>
                            </td>
                            <td>
							<?php
							/*
							 echo '<a href="'.base_url("/jobs/view_job_details/{$list_item['jid']}").'">'.$list_item['jid'].'</a>' 
							 */
							echo $this->gherxlib->crmLink('vjd',$list_item['jid'],$list_item['jid']);
							 ?>
                            </td>
                           <td>
							<?php
							if (stristr($list_item['account_emails'], "@")) {
								if ($list_item['client_emailed'] != NULL) {
									echo "<span class='text-green'>Yes</span>";
								} else {
									echo "<span class='text-red'>No</span>";
								}
							} else {
								echo "N/A";
							}
							?>
							</td>

							<td><?php echo ( date("Y-m-d",strtotime($list_item['sms_sent_merge']))==date("Y-m-d") )?"<span class='text-green'>Yes</span>":"<span class='text-red'>No</span>"; ?></td>
                            
						</tr>
						<?php endforeach;
							}else{
								echo "<tr><td colspan='11'>No Data</td></tr>";
							}
						?>
					</tbody>

				</table>
			</div>

			<nav class="text-center">
				<?php echo $pagination; ?>
			</nav>

			<div class="pagi_count text-center">
				<?php echo $pagi_count; ?>
			</div>

		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4>Merge Jobs</h4>
	<p>All jobs on this page have been done. Every 60 minutes on the hour certificates/invoices are emailed to agencies. When all jobs are completed for the day we 'SMS Tenants' asking for feedback. We then press 'MYOB Export' to export all jobs to upload into MYOB. Once all the above is completed we then press 'Mark All Completed' to remove all the jobs from this page and update their status to completed.</p>

</div>
<!-- Fancybox END -->

<script type="text/javascript">

// agency
function run_ajax_agency_filter(){

var json_data = <?php echo $agency_filter_json; ?>;
var searched_val = '<?php echo $this->input->get_post('agency_filter'); ?>';

jQuery('#agency_filter').next('.mini_loader').show();
jQuery.ajax({
	type: "POST",
		url: "/sys/header_filters",
		data: { 
			rf_class: 'jobs',
			header_filter_type: 'agency',
			json_data: json_data,
			searched_val: searched_val
		}
	}).done(function( ret ){	
		jQuery('#agency_filter').next('.mini_loader').hide();
		$('#agency_filter').append(ret);
	});
			
}

// job type	
function run_ajax_job_filter(){

	var json_data = <?php echo $job_type_filter_json; ?>;
	var searched_val = '<?php echo $this->input->get_post('job_type_filter'); ?>';

	jQuery('#job_type_filter').next('.mini_loader').show();
	jQuery.ajax({
		type: "POST",
			url: "/sys/header_filters",
			data: { 
				rf_class: 'jobs',
				header_filter_type: 'job_type',
				json_data: json_data,
				searched_val: searched_val
			}
		}).done(function( ret ){	
			jQuery('#job_type_filter').next('.mini_loader').hide();
			jQuery('#job_type_filter').append(ret);
		});
				
}

// service
function run_ajax_service_filter(){

var json_data = <?php echo $service_filter_json; ?>;
var searched_val = '<?php echo $this->input->get_post('service_filter'); ?>';

jQuery('#service_filter').next('.mini_loader').show();
jQuery.ajax({
	type: "POST",
		url: "/sys/header_filters",
		data: { 
			rf_class: 'jobs',
			header_filter_type: 'service',
			json_data: json_data,
			searched_val: searched_val
		}
	}).done(function( ret ){	
		jQuery('#service_filter').next('.mini_loader').hide();
		$('#service_filter').append(ret);
	});
			
}

// state
function run_ajax_state_filter(){

var json_data = <?php echo $state_filter_json; ?>;
var searched_val = '<?php echo $this->input->get_post('state_filter'); ?>';

jQuery('#state_filter').next('.mini_loader').show();
jQuery.ajax({
	type: "POST",
		url: "/sys/header_filters",
		data: { 
			rf_class: 'jobs',
			header_filter_type: 'state',
			json_data: json_data,
			searched_val: searched_val
		}
	}).done(function( ret ){	
		jQuery('#state_filter').next('.mini_loader').hide();
		$('#state_filter').append(ret);
	});
			
}

	$(document).ready(function(){ //doc ready start


		// auto email script
		jQuery("#chk_cron_merged_cert_toggle").change(function(){
			
			var cron_status  = ( jQuery(this).prop("checked")==true )?1:0;
			var cron_file = 'merged_email_all_cron';
			var db_field = 'cron_merged_cert';

			swal(
                    {
                        title: "",
                        text: "Are You Sure You Want to Continue?",
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
							url: "<?php echo base_url('/jobs/ajax_toggle_cron_on_off') ?>",
							dataType: 'json',
							data: { 
								cron_status: cron_status,
								cron_file: cron_file,			
								db_field: db_field
							}
							}).done(function(data){
								
								if(data.status){
									$('#load-screen').hide(); //hide loader
									swal({
										title:"Success!",
										text: "Auto Emails successfully updated",
										type: "success",
										showCancelButton: false,
										confirmButtonText: "OK",
										closeOnConfirm: false,
									},function(isConfirm){
										if(isConfirm){ 
											swal.close();
											location.reload();
										}
									});
								}else{
									swal.close();
									location.reload();
								}

							});

                        }
                        
                    }
            	);	
			
		});


		//Email All Certificates/Invoice
		$('.bnt_email_all_certi_invoice').click(function(e){
			e.preventDefault();
			
			swal(
                    {
                        title: "",
                        text: "Are you sure you want to email the invoices / certificates?",
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
							url: "<?php echo base_url('/jobs/email_all_certificates_and_invoices') ?>",
							dataType: 'json',
							data: { 
								staff_id: <?php echo $this->session->staff_id ?>,
							}
							}).done(function(data){
								
								if(data.status){
									$('#load-screen').hide(); //hide loader
									swal({
										title:"Success!",
										text: "Emails have been processed",
										type: "success",
										showCancelButton: false,
										confirmButtonText: "OK",
										closeOnConfirm: false,
									},function(isConfirm){
										if(isConfirm){ 
											swal.close();
											location.reload();
										}
									});
								}else{
									swal('','Server Error: Contact Admin','error');
								}

							});


                        }
                        
                    }
            	);	

		})

		// run headler filter ajax
		run_ajax_job_filter();
		run_ajax_service_filter();
		run_ajax_state_filter();
		run_ajax_agency_filter();


	}) //doc ready end

</script>