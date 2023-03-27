<style>
.col-mdd-3{
	max-width: 20%;
}
.txt_hid, .btn_update, .tenant_details_row{
	display:none;
}
.jTabBubble {
	font-size: 12px !important;
	position: relative !important;
	bottom: 2px !important;
	background-color:#fa424a!important;
}
.tabs-section .nav-item .fa{
	font-size:18px;
}
#rebook_div{
	display:none;
	text-align: right;
}
.job_type_dp_hid{
	display: none;
}	
.reason_icon{
	width: 100px;
}
.action_td .btn,
.action_td .chk_job_lbl{
	margin-top: 6px;
}
#refresh_btn,
#sms_div,
select#sms_type,
select#email_type,
#send_sms_or_email_btn,
#rebook_after_sms_or_email_btn{
	display: none;
}
select#sms_type,
select#email_type,
#send_sms_btn{
	width: auto;
}
#sms_type{
	display: inline;
	width: auto;
}
.sms_check_all_lbl,
.sms_check_all_div{
	margin: 0;
	display: inline !important;
}
.sms_check_all_div{
	width: 17px;
}
</style>
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

		<div class="box-typical box-typical-padding">
			<?php
		$form_attr = array(
			'id' => 'jform'
		);
		echo form_open('/jobs/pre_completion',$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-md-12 columns">
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
							<label for="date_select">Date</label>
							<input name="date_filter" placeholder="ALL" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $this->input->get_post('date'); ?>">
						</div>

						<div class="col-mdd-3">
							<label for="phrase_select">Phrase</label>
							<input type="text" placeholder="ALL" name="search_filter" class="form-control" value="<?php echo $this->input->get_post('search'); ?>" />
						</div>

						<div class="col-mdd-3">
							<label>Not Completed Reason</label>
							<select id="jobs_not_comp_res" name="jobs_not_comp_res" class="form-control">
								<option value="">---</option>	
								<?php
								foreach( $ncr_sql->result() as $ncr_row ){ ?>
									<option value="<?php echo $ncr_row->job_reason_id; ?>" <?php echo ( $ncr_row->job_reason_id == $this->input->get_post('jobs_not_comp_res') )?'selected':null ?>><?php echo $ncr_row->name ?></option>
								<?php
								}
								?>
							</select>
						</div>

						<div class="col-mdd-3">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<button type="submit" class="btn btn-inline">Search</button>							
						</div>

						<div class="col-mdd-3">	
							<label class="col-sm-12 form-control-label">&nbsp;</label>						
							<button type="button" id="refresh_btn" class="btn btn-inline">Refresh</button>
						</div>
						
					</div>

				</div>
	


			</div>
			</form>
		</div>
	</header>

	<section class="tabs-section">
		
		<div class="tabs-section-nav tabs-section-nav-icons">
			<div class="tbl">
				<ul class="nav j_remember_tab" role="tablist">
					<li class="nav-item">
						<a class="nav-link" href="#unable_to_complete" role="tab" data-toggle="tab">
							<span class="nav-link-in">
								<i class="fa fa-close text-red"></i>
								<span id="unable_to_complete_count" class="label label-pill label-danger jTabBubble">0</span>
								Unable to Complete
							</span>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#verify_details" role="tab" data-toggle="tab">
							<span class="nav-link-in">
								<i class="fa fa-question text-orange"></i>
								<span id="verify_details_count" class="label label-pill label-danger jTabBubble">0</span>
								Verify Data
							</span>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#good_to_go" role="tab" data-toggle="tab">
							<span class="nav-link-in">
								<i class="fa fa-check text-green"></i>
								<span id="good_to_go_count" class="label label-pill label-danger jTabBubble">0</span>
								Auto Process
							</span>
						</a>
					</li>
					<li class="nav-item <?php echo $this->config->item('country') == 1 ? '' : 'd-none';?>">
						<a class="nav-link" href="#sales_upgrades" role="tab" data-toggle="tab">
							<span class="nav-link-in">
								<i class="fa fa-usd text-green"></i>
								<span id="sales_upgrade_count" class="label label-pill label-danger jTabBubble">0</span>
								Sales Upgrades
							</span>
						</a>
					</li>
				</ul>
			</div>
		</div><!--.tabs-section-nav-->

		
		<div class="tab-content">

			<!-- YELLOW TAB CONTENT -->
			<div role="tabpanel" class="tab-pane fade" id="unable_to_complete">
			
				<?php 
				$inner_view_data['current_tab'] = 'unable_to_complete';
				$this->load->view('jobs/pre_completion_tab_list',$inner_view_data); 
				?>

				<div id="sms_div" class="text-right">
					
					<button type="button" id="sms_btn" class="btn inline">SMS</button>
					<select name="sms_type" id="sms_type" class="form-control inline">
						<option value="">--- Select SMS template ---</option>
						<?php
						foreach( $sms_templates_sql->result() as $row ){ ?>
							<option value="<?php echo $row->sms_api_type_id; ?>"><?php echo $row->type_name; ?></option>
						<?php
						}
						?>                       
					</select>
			
					<button type="button" id="email_btn" class="btn inline">Email</button>
					<select name="email_type" id="email_type" class="form-control inline">
						<option value="">--- Select Email Template ---</option>
						<?php
						foreach( $email_templates->result() as $row ){ ?>
							<option value="<?php echo $row->email_templates_id; ?>"><?php echo $row->template_name; ?></option>
						<?php
						}
						?>                       
					</select>
					<button type="button" id="send_sms_or_email_btn" class="btn inline">Select Template</button>
					<button type="button" id="rebook_after_sms_or_email_btn" class="btn btn-danger">Rebook</button><br />
					
			
				</div>

			</div><!--.tab-pane-->

			<!-- GREEN TAB CONTENT -->
			<div role="tabpanel" class="tab-pane fade" id="verify_details">
			
				<?php 
				$inner_view_data['current_tab'] = 'verify_details';
				$this->load->view('jobs/pre_completion_tab_list',$inner_view_data); 
				?>
			
			</div><!--.tab-pane-->

			<!-- WHITE TAB CONTENT -->
			<div role="tabpanel" class="tab-pane fade" id="good_to_go">
			
				<?php 
				$inner_view_data['current_tab'] = 'good_to_go';
				$this->load->view('jobs/pre_completion_tab_list',$inner_view_data); 
				?>				

			</div><!--.tab-pane-->

			<!-- SALES UPGRADES -->
			<div role="tabpanel" class="tab-pane fade" id="sales_upgrades">
			
				<?php 
				$inner_view_data['current_tab'] = 'sales_upgrades';
				$this->load->view('jobs/pre_completion_tab_list',$inner_view_data); 
				?>				

			</div>

		</div><!--.tab-content-->

	</section><!--.tabs-section-->


	<div id="rebook_div">
					
		<button type="button" id="btn_create_240v_rebook" class="btn btn-danger submitbtnImg">			
			Create 240v Rebook
		</button>
		<button type="button" id="btn_create_rebook" class="btn btn-danger submitbtnImg">			
			Create Rebook
		</button>
		
		<button type="button" id="btn_move_to_merged" class="btn blue-btn submitbtnImg">			
			Move to Merged
		</button>	

	</div>
	
	

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				
			</div>


			<div class="pagi_count text-center">
				<?php echo $pagi_count; ?>
			</div>

		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4>Pre Completion</h4>
	<p>
	This page shows any jobs that the technician has attended to, whether the job was able to be completed or not, it will appear on this page before moving onto the next step (e.g.: Merged, To Be Booked, Action Required etc). 
	</p>
	<ul>
		<li><span class="green_mark">Green</span> = Job completed but job needs to be opened to verify work done</li>
		<li><span class="yello_mark">Yellow</span> = Job not completed for some reason</li>
		<li>White = OK to proceed - Nothing to verify (Auto processed every 5 mintues)</li>
	</ul>
	<br/>
	<pre><code><?php echo $last_query; ?></code></pre>

</div>
<!-- Fancybox END -->

<script type="text/javascript">

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

	function rebook_ajax(job_id){

		swal(
			{
				title: "",
				text: "Are you sure you want to Rebook?",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-success",
				confirmButtonText: "Yes, Continue",
				cancelButtonClass: "btn-danger",
				cancelButtonText: "No, Cancel!",
				closeOnConfirm: false,
				closeOnCancel: true,
			},
			function(isConfirm){
				if(isConfirm){

					swal.close();
					$('#load-screen').show(); //show loader							
					jQuery.ajax({
						type: "POST",
						url: "/jobs/ajax_rebook_script",
						data: { 
							job_id: job_id,
							is_240v: 0
						}
					}).done(function( ret ){

						$('#load-screen').hide(); //hide loader
						swal({
							title:"Success!",
							text: "Rebook Successful",
							type: "success",
							showCancelButton: false,
							showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
							timer: <?php echo $this->config->item('timer') ?>

						});	
						setTimeout(function(){ window.location='<?php echo $uri; ?>'; }, <?php echo $this->config->item('timer') ?>);								
							
					});	

				}else{
					return false;
				}
				
			}
			
		);

	}
	
	$(document).ready(function(){
		
		// run headler filter ajax
		run_ajax_job_filter();
		run_ajax_service_filter();

		// remember tab
		jQuery(".j_remember_tab .nav-link").click(function(){

			var node = jQuery(this);
			var nav_href = node.attr("href");	

			Cookies.set('precomp_remember_tab', nav_href);

		});

		// select remembered tab		
		if( Cookies.get('precomp_remember_tab') != undefined ){				

			// hide move to merge button on UTC tab
			if( Cookies.get('precomp_remember_tab') == '#unable_to_complete' ){ 
				jQuery("#btn_move_to_merged").hide();
			}

			jQuery('.j_remember_tab a[href="'+Cookies.get('precomp_remember_tab')+'"]').tab('show');

		}else{ // default	
						
			jQuery("#btn_move_to_merged").hide(); // hide move to merge button on UTC tab
			jQuery('.j_remember_tab a[href="#unable_to_complete"]').tab('show');

		}
		
		// Unable to complete tab count script
		var unable_to_complete = jQuery("#unable_to_complete .body_tr").length;
		jQuery("#unable_to_complete_count").html(unable_to_complete);

		// Verify details tab count script
		var verify_details = jQuery("#verify_details .body_tr").length;
		jQuery("#verify_details_count").html(verify_details);

		// Good to go tab count script
		var good_to_go = jQuery("#good_to_go .body_tr").length;
		jQuery("#good_to_go_count").html(good_to_go);
		
		// Sales upgrade tab count script
		var good_to_go = jQuery("#sales_upgrades .body_tr").length;
		jQuery("#sales_upgrade_count").html(good_to_go);

		// bulk move to merge
		jQuery("#btn_move_to_merged").click(function(){

			var job_id = new Array();

			jQuery(".chk_job:checked").each(function(){
				job_id.push(jQuery(this).val());
			});

			swal(
                    {
                        title: "",
                        text: "Are you sure you want to move jobs to Merged Certificates?",
                        type: "warning",
                        showCancelButton: true,
						confirmButtonClass: "btn-success",
						confirmButtonText: "Yes, Continue",
						cancelButtonClass: "btn-danger",
						cancelButtonText: "No, Cancel!",
                        closeOnConfirm: false,
                        closeOnCancel: true,
                    },
                    function(isConfirm){
                        if(isConfirm){

							swal.close();
							$('#load-screen').show(); //show loader							
							jQuery.ajax({
								type: "POST",
								url: "/jobs/ajax_move_to_merged",
								data: { 
									job_id: job_id,
									is_240v: 0
								}
							}).done(function( ret ){

								$('#load-screen').hide(); //hide loader
								swal({
									title:"Success!",
									text: "Move to Merged success",
									type: "success",
									showCancelButton: false,									
									showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
									timer: <?php echo $this->config->item('timer') ?>

								});	
								setTimeout(function(){ window.location='<?php echo $uri; ?>'; }, <?php echo $this->config->item('timer') ?>);								
									
							});	

                        }else{
							return false;
						}
                        
                    }
					
            	);
		
		});


		// bulk rebook 
		jQuery("#btn_create_rebook").click(function(){

			var job_id = new Array();

			jQuery(".chk_job:checked").each(function(){
				job_id.push(jQuery(this).val());
			});

			if( job_id.length > 0 ){
				rebook_ajax(job_id);
			}
			

		});


		// rebook after SMS or Email
		jQuery("#rebook_after_sms_or_email_btn").click(function(){

			var job_id = new Array();

			jQuery(".sms_or_email_chk_job:checked:visible").each(function(){
				job_id.push(jQuery(this).val());
			});

			if( job_id.length > 0 ){
				rebook_ajax(job_id);
			}

		});


		// bulk 240v rebook 
		jQuery("#btn_create_240v_rebook").click(function(){

			var job_id = new Array();

			jQuery(".chk_job:checked").each(function(){
				job_id.push(jQuery(this).val());
			});

			swal(
				{
					title: "",
					text: "Are you sure you want to 240v Rebook?",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-success",
					confirmButtonText: "Yes, Continue",
					cancelButtonClass: "btn-danger",
					cancelButtonText: "No, Cancel!",
					closeOnConfirm: false,
					closeOnCancel: true,
				},
				function(isConfirm){
					if(isConfirm){

						swal.close();
						$('#load-screen').show(); //show loader							
						jQuery.ajax({
							type: "POST",
							url: "/jobs/ajax_rebook_script",
							data: { 
								job_id: job_id,
								is_240v: 1
							}
						}).done(function( ret ){

							$('#load-screen').hide(); //hide loader
							swal({
								title:"Success!",
								text: "240v Rebook Successful",
								type: "success",
								showCancelButton: false,
								showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
								timer: <?php echo $this->config->item('timer') ?>

							});
							setTimeout(function(){ window.location='<?php echo $uri; ?>'; }, <?php echo $this->config->item('timer') ?>);									
								
						});	

					}else{
						return false;
					}
					
				}
				
			);

		});

		
		// inline rebook 
		jQuery(".btn_no_show_rebook").click(function(){
			
			var obj = jQuery(this);
			var hid_job_id = obj.parents("tr:first").find(".hid_job_id").val();
			var job_id = new Array();
			
			job_id.push(hid_job_id);
			
			swal(
                    {
                        title: "",
                        text: "Are you sure you want to create a Rebook?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-success",
						confirmButtonText: "Yes, Continue",
						cancelButtonClass: "btn-danger",
						cancelButtonText: "No, Cancel!",
                        closeOnConfirm: false,
                        closeOnCancel: true,
                    },
                    function(isConfirm){
                        if(isConfirm){

							swal.close();
							$('#load-screen').show(); //show loader
							
							jQuery.ajax({
								type: "POST",
								url: "/jobs/ajax_rebook_script",
								data: { 
									job_id: job_id,
									is_240v: 0
								}
							}).done(function( ret ){

								$('#load-screen').hide(); //hide loader
								swal({
									title:"Success!",
									text: "Job Rebooked",
									type: "success",
									showCancelButton: false,
									showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
									timer: <?php echo $this->config->item('timer') ?>

								});
								setTimeout(function(){ window.location='<?php echo $uri; ?>'; }, <?php echo $this->config->item('timer') ?>);
									
							});	

                        }else{
							return false;
						}
                        
                    }
					
            	);
			
			
		});



		// inline 240v rebook 
		jQuery(".btn_no_show_240v_rebook").click(function(){
			
			var obj = jQuery(this);
			var hid_job_id = obj.parents("tr:first").find(".hid_job_id").val();
			var job_id = new Array();
			
			job_id.push(hid_job_id);
			
			swal(
				{
					title: "",
					text: "Are you sure you want to create a 240v Rebook?",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-success",
					confirmButtonText: "Yes, Continue",
					cancelButtonClass: "btn-danger",
					cancelButtonText: "No, Cancel!",
					closeOnConfirm: false,
					closeOnCancel: true,
				},
				function(isConfirm){
					if(isConfirm){

						swal.close();
						$('#load-screen').show(); //show loader
						
						jQuery.ajax({
							type: "POST",
							url: "/jobs/ajax_rebook_script",
							data: { 
								job_id: job_id,
								is_240v: 1
							}
						}).done(function( ret ){

							$('#load-screen').hide(); //hide loader
							swal({
								title:"Success!",
								text: "Job Rebooked",
								type: "success",
								showCancelButton: false,
								showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
								timer: <?php echo $this->config->item('timer') ?>

							});
							setTimeout(function(){ window.location='<?php echo $uri; ?>'; }, <?php echo $this->config->item('timer') ?>);	
								
						});	

					}else{
						return false;
					}
					
				}
				
			);			
			
		});




		// inline 240v rebook 
		jQuery(".btn_no_show_sms").click(function(){
			
			var obj = jQuery(this);
			var job_id = obj.parents("tr:first").find(".hid_job_id").val();			
			var job_id = obj.parents("tr:first").find(".hid_job_id").val();	
			var job_id = obj.parents("tr:first").find(".hid_job_id").val();				
			
			swal(
				{
					title: "",
					text: "Are you sure you want to send No Show SMS?",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-success",
					confirmButtonText: "Yes, Continue",
					cancelButtonClass: "btn-danger",
					cancelButtonText: "No, Cancel!",
					closeOnConfirm: false,
					closeOnCancel: true,
				},
				function(isConfirm){
					if(isConfirm){

						swal.close();
						$('#load-screen').show(); //show loader
						
						jQuery.ajax({
							type: "POST",
							url: "/jobs/ajax_precomp_send_now_show_sms",
							data: { 
								job_id: job_id
							}
						}).done(function( ret ){

							$('#load-screen').hide(); //hide loader
							
							swal({
								title:"Success!",
								text: "SMS No Show Sent",
								type: "success",
								showCancelButton: false,
								showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
								timer: <?php echo $this->config->item('timer') ?>

							});
							setTimeout(function(){ window.location='<?php echo $uri; ?>'; }, <?php echo $this->config->item('timer') ?>);
								
						});	

					}else{
						return false;
					}
					
				}
				
			);			
			
		});
		



		// check all toggle
		jQuery(".check-all").click(function(){

			var check_all = jQuery(this);
			var parent_table = check_all.parents(".jtable:first");
			
			if(jQuery(this).prop("checked")==true){

				parent_table.find(".chk_job:visible").prop("checked",true);
				var chked = parent_table.find(".chk_job:visible:checked").length;
				
				if( chked > 0 ){
					jQuery("#rebook_div").show();
				}		
				
			}else{
				parent_table.find(".chk_job:visible").prop("checked",false);
				jQuery("#rebook_div").hide();
			}
		
		});
		
		// toggle hide/show remove button
		jQuery(".chk_job").click(function(){	

			var chked = jQuery(".chk_job:checked").length;						
			
			if(chked>0){
				jQuery("#rebook_div").show();
			}else{
				jQuery("#rebook_div").hide();
			}

		});


		// toggle 240v job type dropdown
		jQuery(".btn_240v").click(function(){
			
			jQuery(this).parents("tr:first").find(".job_type_update").toggle();
			jQuery(this).parents("tr:first").find(".job_type_dp_hid").toggle();
			
		});
		
		// update 240v job type
		jQuery(".job_type_dp_hid").change(function(){			
			
			var job_id = jQuery(this).parents("tr:first").find(".hid_job_id").val();
			var job_type = jQuery(this).val();
			
			jQuery.ajax({
				type: "POST",
				url: "/jobs/ajax_update_job_type",
				data: { 
					job_id: job_id,
					job_type: job_type
				},
				dataType: 'json'
			}).done(function( ret ){

				//window.location="/jobs/pre_completion";

				// show refresh button
				jQuery("#refresh_btn").show();
				
			});	
			
		});

		// refresh button
		jQuery("#refresh_btn").click(function(){

			location.reload();

		});


		// hide move to merge button on UTC tab
		jQuery(".nav .nav-link").click(function(){

			var nav_link_dom = jQuery(this);
			var nav_link = nav_link_dom.attr("href");
			
			if( nav_link == '#unable_to_complete' ){ 
				jQuery("#btn_move_to_merged").hide();
			}else{
				jQuery("#btn_move_to_merged").show();
			}

		});


		// SMS check all toggle
		jQuery(".sms_check-all").click(function(){

			var check_all = jQuery(this);
			var parent_table = check_all.parents(".jtable:first");

			if( jQuery(this).prop("checked") == true ){

				parent_table.find(".sms_or_email_chk_job:visible").prop("checked",true);
				var chked = parent_table.find(".sms_or_email_chk_job:visible:checked").length;
				
				if( chked > 0 ){
					jQuery("#sms_div").show();
				}		
				
			}else{

				parent_table.find(".sms_or_email_chk_job:visible").prop("checked",false);
				jQuery("#sms_div").hide();

			}

		});

		
		// SMS check 
		jQuery(".sms_or_email_chk_job").click(function(){	

			var chked = jQuery(".sms_or_email_chk_job:checked").length;						

			if( chked > 0 ){
				jQuery("#sms_div").show();
			}else{
				jQuery("#sms_div").hide();
			}

		});

		// show SMS template
		jQuery("#sms_btn").click(function(){

			jQuery("select#sms_type").show();

		});

		// SMS template selection dynamic button text
		jQuery("select#sms_type").change(function(){

			var sms_type_dom = jQuery(this);
			var sms_type = sms_type_dom.val();
			var email_type = jQuery("select#email_type").val();

			if( sms_type > 0 ){ // SMS selected
				
				if( email_type > 0 ){ // Email template selected
					jQuery("#send_sms_or_email_btn").text("Send SMS & Email");
				}else{ // Email template empty
					jQuery("#send_sms_or_email_btn").text("Send SMS");
				}

				jQuery("#send_sms_or_email_btn").show();

			}else{ // SMS empty

				if( email_type > 0 ){ // Email template selected
					jQuery("#send_sms_or_email_btn").text("Send Email");
					jQuery("#send_sms_or_email_btn").show();
				}else{ // Email template empty
					jQuery("#send_sms_or_email_btn").text("Select Template");
					jQuery("#send_sms_or_email_btn").hide();
				}

			}

			
			

		});
		
		// show email template
		jQuery("#email_btn").click(function(){

			jQuery("select#email_type").show();

		});

		// email template selection dynamic button text
		jQuery("select#email_type").change(function(){
			
			var email_type_dom = jQuery(this);
			var email_type = email_type_dom.val();
			var sms_type = jQuery("select#sms_type").val();

			if( email_type > 0 ){ // Email template selected

				if( sms_type > 0 ){ // SMS selected
					jQuery("#send_sms_or_email_btn").text("Send SMS & Email");
				}else{ // SMS empty
					jQuery("#send_sms_or_email_btn").text("Send Email");
				}

				jQuery("#send_sms_or_email_btn").show();

			}else{

				if( sms_type > 0 ){ // SMS selected
					jQuery("#send_sms_or_email_btn").text("Send SMS");
					jQuery("#send_sms_or_email_btn").show();
				}else{ // SMS empty
					jQuery("#send_sms_or_email_btn").text("Select Template");
					jQuery("#send_sms_or_email_btn").hide();
				}

			}

		});

		
		// send SMS
		jQuery("#send_sms_or_email_btn").click(function(){

			var job_id_arr = [];
			var sms_type = jQuery("#sms_type").val();
			var email_type = jQuery("#email_type").val();
			var error = '';

			// SMS checkbox
			jQuery(".sms_or_email_chk_job:visible:checked").each(function(){

				var dom = jQuery(this);
				var job_id = dom.val();
				job_id_arr.push(job_id);

			});
			
			// SMS template validation
			if( sms_type == '' && email_type == '' ){ 				
				error += 'Please Select SMS template to be used\n';
			}

			if( error != '' ){ // error

				swal('',error,'error');

			}else{ // success

				if( job_id_arr.length > 0 && ( sms_type > 0 || email_type > 0 ) ){

					$('#load-screen').show(); //show loader							
					jQuery.ajax({
						type: "POST",
						url: "/jobs/send_sms_or_email",
						data: { 
							job_id_arr: job_id_arr,
							sms_type: sms_type,
							email_type: email_type
						}
					}).done(function( ret ){
						
						$('#load-screen').hide(); //hide loader	
						
						var success_text = '';
						if( sms_type > 0 && email_type > 0 ){
							success_text = 'SMS and Email';
						}else if( sms_type > 0 ){
							success_text = 'SMS';
						}else if( email_type > 0 ){
							success_text = 'Email';
						}
						
						swal({
							title:"Success!",
							text: success_text,
							type: "success",
							showCancelButton: false,									
							showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
							timer: <?php echo $this->config->item('timer') ?>

						});	

						jQuery("#rebook_after_sms_or_email_btn").show();
						//setTimeout(function(){ window.location='<?php echo $uri; ?>'; }, <?php echo $this->config->item('timer') ?>);																			
							
					});	

				}

			}
		
		});
		


	});

</script>