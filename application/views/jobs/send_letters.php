<?php
  $export_links_params_arr = array(
	'job_type_filter' => $this->input->get_post('job_type_filter'),
	'service_filter' => $this->input->get_post('service_filter'),
	'state_filter' =>  $this->input->get_post('state_filter'),
	'date_filter' => $this->input->get_post('date_filter'),
	'search_filter' => $this->input->get_post('search_filter')
);
$export_link_params = '/jobs/new_jobs/?status=sendletters&'.http_build_query($export_links_params_arr);
?>
<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => $title,
			'status' => 'active',
			'link' => $uri
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	<header class="box-typical-header">

		<!--<div style="text-align: left; margin-left:20px;">
			<ul style="list-style-type:disc;">
				<li><img title="No Tenants" src="/images/row_icons/no_tenant_coloured.png" style="width: 20px;"  /> (No Tenant Details) Job will be moved to 'Escalate’</li>
				<li><img src="/images/row_icons/mail_colored.png" style="width: 20px;" /> (Tenant has email address) Tenant will be emailed an introduction email</li>
				<li><img src="/images/row_icons/sms_colored.png" style="width: 20px;" /> (Tenant has a mobile number) Tenant will be SMS'd introduction and Agent emailed</li> 
				<li><span style="background-color:#ffff9d;">Yellow Highlight</span>, read the job comments then process manually by entering job</li>											
				<li><strong>No Icon</strong> Click <i>'Export'</i>, perform mail merge, click <i>'Mark Letters sent'</i> to Email Agent</li>											
			</ul>
		</div> -->

		<div class="box-typical box-typical-padding">
			<?php
		$form_attr = array(
			'id' => 'jform'
		);
		echo form_open('/jobs/new_jobs',$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-md-9 columns">
					<div class="row">

					

						<div class="col-mdd-3">
							<label for="jobtype_select">Job Type</label>
							<select id="job_type_filter" name="job_type_filter" class="form-control">
								<option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>
						</div>

						<div class="col-mdd-3">
							<label for="service_select">Service</label>
							<select id="service_filter" name="service_filter" class="form-control">
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
							<input name="date_filter" placeholder="ALL" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $this->input->get_post('date_filter'); ?>">
						</div>

						<div class="col-mdd-3">
							<label for="phrase_select">Phrase</label>
							<input type="text" placeholder="ALL" name="search_filter" class="form-control"  value="<?php echo $this->input->get_post('search_filter'); ?>" />
						</div>

						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<button type="submit" class="btn btn-inline">Search</button>
						</div>
					</div>
				</div>

                <!-- DL ICONS START -->
                <?php 
                $date = ($this->input->get_post('date_filter')!="")?date('Y-m-d',$this->input->get_post('date_filter')):NULL;
                 ?>
			    <div class="col-md-3 columns">


					

                    <section class="proj-page-section float-right">
                        <div class="proj-page-attach">
                            <i class="fa fa-file-excel-o"></i>
                            <p class="name"><?php echo $title; ?></p>
                            <p>
								<a href="/jobs/new_jobs/?export=1" target="blank">
									Export
								</a>
                            </p>
                        </div>
					</section>
					
					<!--
					<div class="auto_email_div float-right">
						<?php
							// get crm settings
							$crm_sql = $this->system_model->getCrmSettings([
								"sel_str" => "cron_send_letters"
							]);
							$crm_row = $crm_sql->row();
					
							if( $crm_row->cron_send_letters == 1 ){
								$ae_txt = 'Active';
								$ae_color = 'green';
								$is_checked = 'checked="checked"';
							}else{
								$ae_txt = 'Inactive';
								$ae_color = 'red';
								$is_checked = '';
							}					
						?>	

						<div class="checkbox" style="margin:0;">
							<input type="checkbox" id="chk_cron_send_letter_toggle" <?php echo $is_checked; ?> />
							<label for="chk_cron_send_letter_toggle">&nbsp;</label>
							<span style="color:<?php echo $ae_color; ?>">Auto Process <?php echo $ae_txt; ?></span>
						</div>
					</div>
					-->

					
				</div>
				<!-- DL ICONS END -->

			</div>
			</form>
		</div>
	</header>


	<!-- TABS START -->
    <section class="tabs-section">

        <!--.tabs-section-nav start-->
        <div class="tabs-section-nav tabs-section-nav-icons">
            <div class="tbl">
                <ul class="nav" role="tablist" id="main-tab">
                    <li class="nav-item">
                        <a class="nav-link active" href="#ar_tab" role="tab" data-toggle="tab">
                            <span class="nav-link-in">
                                <i class="fa fa-wrench"></i>
                                Action Required
                                <span id="ar_tab_count" class="label label-pill label-danger">0</span>
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#nar_tab" role="tab" data-toggle="tab">
                            <span class="nav-link-in">
                                <span class="fa fa-share-alt"></span>
                                No Action Required
                                <span id="nar_tab_count" class="label label-pill label-danger">0</span>
                            </span>
                        </a>
                    </li>                    
                </ul>
            </div>
		</div><!--.tabs-section-nav end-->
		
		<div class="tab-content">

			<!-- Action Required(yellow) TAB CONTENT -->
			<div role="tabpanel" class="tab-pane fade in active show" id="ar_tab">
								
				<?php 
				$inner_view_data['current_tab'] = 'ar_tab';
				$this->load->view('jobs/send_letter_tab_list',$inner_view_data); 
				?>

			</div>

			<!-- No Action Required(white) CONTENT -->
			<div role="tabpanel" class="tab-pane fade" id="nar_tab">
				
				<?php 
				$inner_view_data['current_tab'] = 'nar_tab';
				$this->load->view('jobs/send_letter_tab_list',$inner_view_data); 
				?>

			</div>	

			

			<div class="gbox_main">

				<div style="clear:both;"></div>
				
				<div id="no_tenants_div" class="send_letter_buttons float-right">
					<button id="btn_no_tenants" class="btn btn-inline blue-btn btn_no_tenants submitbtnImg blue-btn" type="button">						
						No Tenant Details
					</button>
				</div>
				<div id="send_tenants_div" class="send_letter_buttons float-right">	
					<button id="btn_send_tenants" class="btn btn-inline blue-btn btn_send_tenants submitbtnImg blue-btn" type="button">						
						Email Tenant
					</button>
				</div>
				<div id="send_sms_div" class="send_letter_buttons float-right">			
					<button id="btn_send_tenants_sms" class="btn btn-inline blue-btn btn_send_tenants_sms submitbtnImg blue-btn" type="button">			
						SMS Tenant
					</button>
				</div>

				<div style="clear:both;"></div>

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

	<h4><?php echo $title; ?></h4>
	<p>
	This page displays all new jobs that come into the system that have never been in the system before. This page notifies the tenant via SMS or Email of who SATS is to prepare them before we attempt to book the job in.
	</p>
	<ul class="about_page_li">
		<li><img title="No Tenants" src="/images/row_icons/no_tenant_coloured.png" style="width: 20px;"> (No Tenant Details) Job will be moved to 'Escalate’</li>
		<li><img src="/images/row_icons/mail_colored.png" style="width: 20px;"> (Tenant has email address) Tenant will be emailed an introduction email</li>
		<li><img src="/images/row_icons/sms_colored.png" style="width: 20px;"> (Tenant has a mobile number) Tenant will be SMS'd introduction and Agent emailed</li> 
		<li><span class="yellowRowBg">Yellow Highlight</span>, read the job comments then process manually by entering job</li>	
		<li><span class="greenRowBg">Green Highlight</span>, This job is marked 'Urgent'</li>											
		<li><strong>No Icon</strong> Click <i>'Export'</i>, perform mail merge, click <i>'Mark Letters sent'</i> to Email Agent</li>											
	</ul>

	<br/>
	<pre>
<code>SELECT `j`.`id` AS `jid`, `j`.`job_type`, `j`.`status` AS `jstatus`, `j`.`service` AS `jservice`, `j`.`created` AS `jcreated`, `j`.`date` AS `jdate`, `j`.`job_price`, `j`.`start_date`, `j`.`due_date`, `j`.`comments` AS `j_comments`, `j`.`assigned_tech`, `j`.`property_vacant`, `j`.`urgent_job`, `p`.`property_id`, `p`.`address_1` AS `p_address_1`, `p`.`address_2` AS `p_address_2`, `p`.`address_3` AS `p_address_3`, `p`.`state` AS `p_state`, `p`.`postcode` AS `p_postcode`, `p`.`comments` AS `p_comments`, `p`.`holiday_rental`, `a`.`agency_id`, `a`.`agency_name`, `ajt`.`type` AS `ajt_type`
FROM `jobs` AS `j`
LEFT JOIN `property` AS `p` ON j.`property_id` = p.`property_id`
LEFT JOIN `agency` AS `a` ON  p.`agency_id` = a.`agency_id`
LEFT JOIN `alarm_job_type` AS `ajt` ON j.`service` = ajt.`id`
WHERE `j`.`del_job` = 0
AND `p`.`deleted` = 0
AND `a`.`status` = 'active'
AND `a`.`country_id` = <?php echo COUNTRY ?> 
AND `j`.`status` = 'Send Letters'
ORDER BY `j`.`urgent_job` DESC, `j`.`job_type` ASC, `p`.`address_3` ASC
LIMIT 50</code>
	</pre>

</div>
<!-- Fancybox END -->

<style>
.send_letter_buttons,
.checkbox_all{
	display: none;
}
.about_page_li li{
	padding: 5px 0;
}
.inline_checkbox {
    position: relative !important;
	top: 4px;
	padding: 0 15px 0 0 !important;
}
.check_all_th{
	width: 54px;
}
.auto_email_div{
	margin: 31px 22px 0 0;
}
.email_it{	
	cursor: pointer; 
	width:24px; 
	float: left
}
</style>
<script>


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

$(document).ready(function(){

	// run headler filter ajax
	run_ajax_job_filter();
	run_ajax_service_filter();
	run_ajax_state_filter();



	// AR tab count script
	var ar_tab_count = jQuery("#ar_tab .body_tr").length;
	jQuery("#ar_tab_count").html(ar_tab_count);

	// NAR tab count script
	var nar_tab_count = jQuery("#nar_tab .body_tr").length;
	jQuery("#nar_tab_count").html(nar_tab_count);

	
	// auto email script
	$("#chk_cron_send_letter_toggle").change(function(){
		
		var cron_send_letters = ( $(this).prop("checked") == true )?1:0;
		
		swal({
			title: "Warning!",
			text: "Are you sure you want to continue?",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-success",
			confirmButtonText: "Yes",
			cancelButtonClass: "btn-danger",
			cancelButtonText: "No, Cancel!",
			closeOnConfirm: true,
			showLoaderOnConfirm: true,
			closeOnCancel: true
		}, function () {
			
			$.ajax({
				type: "POST",
				url: "/cronjobs/ajax_send_letters_cron_toggle",        		
				data: { 
					cron_send_letters: cron_send_letters
				}
			}).done(function(res){
				window.location="/jobs/new_jobs";	
			});

		});		
		
	});
	
	//SMS


	
	
	
	
	// inline send tenant email
	$(".email_it").click(function(){
		
		var job_id_arr = new Array();
		var job_id = $(this).parents("tr:first").find(".hid_job_id").val();
		job_id_arr.push(job_id);

		swal({
			title: "Warning!",
			text: "Are you sure you want to email the tenants?",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-success",
			confirmButtonText: "Yes",
			cancelButtonClass: "btn-danger",
			cancelButtonText: "No, Cancel!",
			closeOnConfirm: true,
			showLoaderOnConfirm: true,
			closeOnCancel: true
	    }, function () {
			
			$.ajax({
				type: "POST",
				url: "/jobs/ajax_send_letters_email_tenant",        		
				data: { 
					job_id_arr: job_id_arr
				}
			}).done(function(res){
				window.location="/jobs/new_jobs";	
			});

	    });	
		
	});
	
	// inline send not tenant email
	$(".no_tenant_icon").click(function(){
		
		var job_id_arr = new Array();
		var job_id = $(this).parents("tr:first").find(".hid_job_id").val();
		job_id_arr.push(job_id);
		
		swal({
			title: "Warning!",
			text: "Are you sure you want to mark no tenant details?",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-success",
			confirmButtonText: "Yes",
			cancelButtonClass: "btn-danger",
			cancelButtonText: "No, Cancel!",
			closeOnConfirm: true,
			showLoaderOnConfirm: true,
			closeOnCancel: true
	    }, function () {
			
			$.ajax({
				type: "POST",
				url: "/jobs/ajax_send_letters_no_tenant_email_to_agency",        		
				data: { 
					job_id_arr: job_id_arr
				}
			}).done(function(res){
				window.location="/jobs/new_jobs";	
			});

	    });	
		
	});
	
	// inline send tenant sms
	$(".sms_it").click(function(){
		
		var job_id_arr = new Array();
		var job_id = $(this).parents("tr:first").find(".hid_job_id").val();
		job_id_arr.push(job_id);
		
		swal({
			title: "Warning!",
			text: "Are you sure you want to sms the tenants?",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-success",
			confirmButtonText: "Yes",
			cancelButtonClass: "btn-danger",
			cancelButtonText: "No, Cancel!",
			closeOnConfirm: true,
			showLoaderOnConfirm: true,
			closeOnCancel: true
	    }, function () {
			
			$.ajax({
				type: "POST",
				url: "/jobs/ajax_send_letters_sms_tenant",        		
				data: { 
					job_id_arr: job_id_arr
				}
			}).done(function(res){
				window.location="/jobs/new_jobs";	
			});		

	    });	
		
	});
	
	// send email in BULK
	$("#btn_send_tenants").click(function(){
		
		var job_id_arr = new Array();
		$(".email_chk:visible:checked").each(function(){
			var job_id = $(this).val();
			job_id_arr.push(job_id);
		});

		swal({		  
			title: "Warning!",
			text: "Are you sure you want to email the tenants?",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-success",
			confirmButtonText: "Yes",
			cancelButtonClass: "btn-danger",
			cancelButtonText: "No, Cancel!",
			closeOnConfirm: true,
			showLoaderOnConfirm: true,
			closeOnCancel: true
	    }, function () {
			
			$.ajax({
				type: "POST",
				url: "/jobs/ajax_send_letters_email_tenant",        		
				data: { 
					job_id_arr: job_id_arr
				}
			}).done(function(res){
				window.location="/jobs/new_jobs";	
			});		

	    });			
		
	});
	
	// send sms in BULK
	$("#btn_send_tenants_sms").click(function(){
		
		var job_id_arr = new Array();
		$(".sms_chk:visible:checked").each(function(){
			var job_id = $(this).val();
			job_id_arr.push(job_id);
		});

		swal({		  
			title: "Warning!",
			text: "Are you sure you want to sms the tenants?",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-success",
			confirmButtonText: "Yes",
			cancelButtonClass: "btn-danger",
			cancelButtonText: "No, Cancel!",
			closeOnConfirm: true,
			showLoaderOnConfirm: true,
			closeOnCancel: true
	    }, function () {
			
			$.ajax({
				type: "POST",
				url: "/jobs/ajax_send_letters_sms_tenant",        		
				data: { 
					job_id_arr: job_id_arr
				}
			}).done(function(res){
				window.location="/jobs/new_jobs";	
			});				

	    });
		
	});
	
	// send no tenant email in BULK
	$("#btn_no_tenants").click(function(){
		
		var job_id_arr = new Array();
		$(".no_tenants_chk:visible:checked").each(function(){
			var job_id = $(this).val();
			job_id_arr.push(job_id);
		});

		swal({		  
			title: "Warning!",
			text: "Are you sure you want to mark no tenant details?",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-success",
			confirmButtonText: "Yes",
			cancelButtonClass: "btn-danger",
			cancelButtonText: "No, Cancel!",
			closeOnConfirm: true,
			showLoaderOnConfirm: true,
			closeOnCancel: true
	    }, function () {
			
			$.ajax({
				type: "POST",
				url: "/jobs/ajax_send_letters_no_tenant_email_to_agency",        		
				data: { 
					job_id_arr: job_id_arr
				}
			}).done(function(res){
				window.location="/jobs/new_jobs";	
			});	


	    });	
		
	});


	
	
	// Email tenant
	// check all toggle for Email Column
	$(".chk_email_all").click(function(){

	  if($(this).prop("checked")==true){
		$("#sl_tble .has_tenants_email_row").addClass("j_row_selected");
		$(".email_chk:visible").prop("checked",true);
		$("#send_tenants_div").show();
	  }else{
		$("#sl_tble .has_tenants_email_row").removeClass("j_row_selected");
		$(".email_chk:visible").prop("checked",false);
		$("#send_tenants_div").hide();
	  }
	  
	});
	
	// email tenant toggle hide/show 
	$(".email_chk").click(function(){

	  var chked = $(".email_chk:checked").length;
	  
	  if($(this).prop("checked")==true){
		   $(this).parents("tr:first").addClass("j_row_selected");
	  }else{
		   $(this).parents("tr:first").removeClass("j_row_selected");
	  }	 
	  
	  if(chked>0){
		$("#send_tenants_div").show();
	  }else{
		$("#send_tenants_div").hide();
	  }

	});


	// SMS TENANT
	// check all toggle for sms Column
	$(".chk_sms_all").click(function(){

		if($(this).prop("checked")==true){
			$("#sl_tble .has_mobile_num_row").addClass("j_row_selected");
			$(".sms_chk:visible").prop("checked",true);
			$("#send_sms_div").show();
		}else{
			$("#sl_tble .has_mobile_num_row").removeClass("j_row_selected");
			$(".sms_chk:visible").prop("checked",false);
			$("#send_sms_div").hide();
		}

	});

	// SMS toggle hide/show send sms button
	$(".sms_chk").click(function(){

		var chked = $(".sms_chk:checked").length;

		if($(this).prop("checked")==true){
			$(this).parents("tr:first").addClass("j_row_selected");
		}else{
			$(this).parents("tr:first").removeClass("j_row_selected");
		}


		if(chked>0){
		$("#send_sms_div").show();
		}else{
		$("#send_sms_div").hide();
		}

	});


	// NO TENANT
	// check all toggle
	$(".chk_no_tenant_all").click(function(){
  
	if($(this).prop("checked")==true){
		$("#sl_tble .no_tenants_row").addClass("j_row_selected");
		$(".no_tenants_chk:visible").prop("checked",true);
		$("#no_tenants_div").show();
	}else{
		$("#sl_tble .no_tenants_row").removeClass("j_row_selected");
		$(".no_tenants_chk:visible").prop("checked",false);
		$("#no_tenants_div").hide();
	}
	
	});

	// toggle hide/show remove button
	$(".no_tenants_chk").click(function(){

	var chked = $(".no_tenants_chk:checked").length;
	
	if($(this).prop("checked")==true){
		$(this).parents("tr:first").addClass("j_row_selected");
	}else{
		$(this).parents("tr:first").removeClass("j_row_selected");
	}
	
	if(chked>0){
		$("#no_tenants_div").show();
	}else{
		$("#no_tenants_div").hide();
	}

	});


	// email
	// make checkall visible if at least one email checkbox
	if( $("#ar_tab .lbl_email_chk_all").length > 0 ){
		$("#ar_tab .jtable_list .email_chk_all").show();
	}

	// make checkall visible if at least one email checkbox
	if( $("#nar_tab .lbl_email_chk_all").length > 0 ){
		$("#nar_tab .jtable_list .email_chk_all").show();
	}

	// SMS
	// make checkall visible if at least one email checkbox
	if( $("#ar_tab .lbl_sms_chk_all").length > 0 ){
		$("#ar_tab .jtable_list .sms_chk_all").show();
	}

	// make checkall visible if at least one email checkbox
	if( $("#nar_tab .lbl_sms_chk_all").length > 0 ){
		$("#nar_tab .jtable_list .sms_chk_all").show();
	}

	// not tenant
	// make checkall visible if at least one email checkbox
	if( $("#ar_tab .lbl_no_tenant_chk_all").length > 0 ){
		$("#ar_tab .jtable_list .no_tenant_chk_all").show();
	}

	// make checkall visible if at least one email checkbox
	if( $("#nar_tab .lbl_no_tenant_chk_all").length > 0 ){
		$("#nar_tab .jtable_list .no_tenant_chk_all").show();
	}
	
	
});
</script>