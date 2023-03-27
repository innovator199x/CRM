
<style>
	.col-mdd-3{
		max-width:15%;
	}
	.top_inputs_div{
		border-top:1px solid #eee;padding-top:10px;margin-top:10px;
	}
	.top_inputs_div .form-control-label{
		padding-top:12px;
	}
	.todaysBgColor{
		background: #dfffa5;
	}
	.greyRowBgColor{
		background-color: #ececec;
	}
	.update_elem{
		display:none;
	}
	ul.bullets li{
		list-style-type: disc;
		margin-left: 15px;
		padding-bottom: 10px;
	}
	.statistics_tbl{
		display: none;
		margin-top:15px;
	}
	.instruct_div ol{
		margin-left:15px;
		padding-left:0px;
		margin-top:30px;
	}
	.instruct_div ol li{
		line-height: 24px;
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
			'link' => "/reports/daily_figures"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">
			
            <div class="for-groupss row quickLinksDiv2">
       
				<div class="text-center col-md-12 columns">

				Quick Links&nbsp;

					<?php
					for( $i=6; $i>=1; $i-- ){ 
						$prev_month_ts = strtotime("{$start_date} -{$i} month"); 
						$ql_start_date = date('Y-m-01',$prev_month_ts);
						$ql_end_date = date('Y-m-t',$prev_month_ts);
					?>
					
						| &nbsp; 
						<a href="/reports/daily_figures?from_date=<?php echo $ql_start_date; ?>&end_date=<?php echo $ql_end_date; ?>">
								<span <?php echo ($ql_start_date==$start_date)?'style="font-weight: bold;"':''; ?>><?php echo date("F",$prev_month_ts); ?></span>
							</a>
					<?php
					}
					?>

					<?php
						// current month
						$filtered_month_ts = strtotime($start_date); 
						$ql_start_date = date('Y-m-01',$filtered_month_ts);
						$ql_end_date = date('Y-m-t',$filtered_month_ts);
					?>
						| 
						<a href="/reports/daily_figures?from_date=<?php echo date('Y-m-01') ?>&end_date=<?php echo date('Y-m-t') ?>">
							<span <?php echo ($ql_start_date==$start_date)?'style="font-weight: bold;"':''; ?>><?php echo date("F",$filtered_month_ts); ?></span>
						</a>

					<?php
						// next month
						$next_month_ts = strtotime("{$start_date} +1 month"); 
						$nm_start_date = date('Y-m-01',$next_month_ts);
						$nm_end_date = date('Y-m-t',$next_month_ts);
					?>
						| 
						<a href="/reports/daily_figures?from_date=<?php echo $nm_start_date; ?>&end_date=<?php echo $nm_end_date; ?>">
							<span <?php echo ($nm_start_date==$start_date)?'class="bold_this"':''; ?>><?php echo date("F",$next_month_ts); ?></span>
						</a>

				</div>
       
			</div>
			
			<div class="for-groupss row top_inputs_div form-inline">
				
				<div class="col-md-3 columns">
					Budget:&nbsp;&nbsp;
					<input type="text" id="budget" class="budget_fields_elem form-control" value="<?php echo $df['budget']; ?>" >
				</div>
				<div class="col-md-3 columns">
					Working Days:&nbsp;&nbsp;
					<input style="width:70px;" type="text" id="working_days" class="budget_fields_elem form-control" value="<?php echo $df['working_days']; ?>">
				</div>
				<div class="col-md-2 columns">Days Worked: <?php echo $todays_working_day; ?></div>
				<div class="col-md-2 columns">Working Days Left: <?php echo ($df['working_days']-$todays_working_day); ?></div>
				<div class="col-md-2 columns"><button  data_daily_figure_id="<?php echo $df['daily_figure_id']; ?>" id="btn_save" type="button" class="btn">Save</button><div>
				
			</div>
			

		</div>
	</header>

	<section>
		<div class="body-typical-body">
			<div class="row">

				<div class="col-md-7 columns">
					<div class="table-responsive">
					<table class="table table-hover main-table">
						<thead>
							<tr>    
								<th class="noBorderTop">Date</th>
								<th class="noBorderTop text-center">Working Day</th>
								<th class="noBorderTop jtextalignleft">Day</th>
								<th class="noBorderTop">Sales <?php echo ( $this->input->get_post('from_date') == '' || $this->input->get_post('from_date') >= date('Y-m-01') )?'(EX GST)':'(INC GST)'; ?></th>
								<th class="noBorderTop">Techs</th>
								<th style="width:170px;" class="noBorderTop">Jobs</th>		
								<th class="noBorderTop">Avg. Jobs</th>
								<th class="noBorderTop">Avg. $ Jobs</th>
								<th class="noBorderTop">MTD Sales</th>	
							</tr>
						</thead>

						<tbody>

						<?php
						
							// current date
							$curr_date = $start_date; 
							$todays_sale = 0;
							$mtd_sales = 0;


							while( $curr_date <= $end_date ){
							
							
								
								$bgcolorClass = '';
								// get current date timestamp
								$curr_date_ts = strtotime($curr_date);
								
								// get Daily Figures data per Date
								$dfpd_sql = $this->figure_model->getDailyFiguresPerDate(date('Y-m-d',$curr_date_ts));
								$dfpd = $dfpd_sql->row_array();
								
								// day full textual display
								$day_txt = date('l',$curr_date_ts);
								
								// today
								$is_today = ( $curr_date==date('Y-m-d') )?1:0;
								
								if( $is_today==1 ){
									$bgcolorClass = 'todaysBgColor';
									$todays_sale = $dfpd['sales'];
								}else{
									if( $day_txt=='Sunday' || $day_txt=='Saturday' ){
										$bgcolorClass = 'greyRowBgColor';
									}
								}
								
								
						?>


							<tr class="<?php echo $bgcolorClass; ?>">

									<td>
										<a href="javascript:void(0);" class="date_link">
											<?php echo date('d/m/Y',$curr_date_ts); ?>
										</a>
										<input type="hidden" class="date" value="<?php echo date('Y-m-d',$curr_date_ts); ?>" />
									</td>
									
									<td class="text-center">	
										<span class="display_elem"><?php echo ($dfpd['working_day']>0)?$dfpd['working_day']:''; ?></span>
										<input type="text" class="working_day update_elem form-control" value="<?php echo $dfpd['working_day']; ?>" />
									</td>

									<td class="jtextalignleft"><?php echo $day_txt; ?></td>

									<td>
										<span class="display_elem"><?php echo ($dfpd['sales']>0)?'$'.number_format($dfpd['sales'], 2):''; ?></span>
										<input type="text" class="sales update_elem form-control" value="<?php echo $dfpd['sales']; ?>" />
										<input type="hidden" class="sales_ub_os_only" />
										<input type="hidden" class="sales_exc_ic_up" />
										<input type="hidden" class="sales_ic_up_only" />
									</td>

									<td>
										<span class="display_elem"><?php echo ($dfpd['techs']>0)?$dfpd['techs']:''; ?></span>
										<input type="text" class="techs update_elem form-control" value="<?php echo $dfpd['techs']; ?>" />
									</td>

									<td style="width:150px;">
										<span class="display_elem"><?php echo ($dfpd['jobs']>0)?$dfpd['jobs']:''; ?></span>
										<input style="width:90px;margin-right:3px;" type="text" class="jobs update_elem form-control" value="<?php echo $dfpd['jobs']; ?>" />
										<input type="hidden" class="jobs_exc_ub_os" />

										<input type="hidden" class="dfpd_id form-control" value="<?php echo $dfpd['daily_figure_per_date_id']; ?>" />
										<?php
										// today
										if( $is_today==1 ){ ?>
											<a style="color:#fff;" data-toggle="tooltip" title="Fetch Data" class="update_elem btn_fetch_data btn" id="" data-date="<?php echo date('Y-m-d',$curr_date_ts); ?>"> <span style="font-size:18px;" class="fa fa-refresh"></span> </a>
										<?php	
										}
										?>
										<a data-toggle="tooltip" title="Update" class="btn_update btn" style="display:none;color:#fff;">Save</a>						
									</td>

										<td>
											<span class="average">
											<?php 
											$average = round($dfpd['jobs']/$dfpd['techs']); 
											echo ($average>0)?$average:'';
											?>
											</span>
										</td>
										<td>
											<span class="average">
											<?php 
											$average2 = $dfpd['sales']/$dfpd['jobs']; 
											echo ($average2>0)?'$'.number_format($average2, 2):'';
											?>
											</span>
										</td>
										<td>
											<?php 
											if($dfpd['sales']>0){
												$mtd_sales += $dfpd['sales'];
											}				
											?>
											<span class="display_elem"><?php echo ($dfpd['sales']>0)?'$'.number_format($mtd_sales, 2):''; ?></span>
										</td>

							</tr>

						
						<?php 
							$curr_date = date('Y-m-d',strtotime($curr_date.'+ 1 day'));	
							} 
						?>


						</tbody>
					</table>

					
					</div>
					<ul class="bullets">
						<li>Outstanding Jobs = Number of Jobs EXCEPT (On Hold,Pending,Booked, Completed,Cancelled)</li>
						<li>Outstanding Value = Value of Jobs EXCEPT (On Hold,Pending,Booked, Completed,Cancelled)</li>
					</ul>
				</div>

				<div class="col-md-5 columns">
								
						<div>
							<input type="hidden" id="from" class="from" value="<?php echo $from; ?>" />
							<input type="hidden" id="to" class="to" value="<?php echo $to; ?>" />
							<input type="hidden" id="df_working_days" class="df_working_days" value="<?php echo $df['working_days']; ?>" />
							<input type="hidden" id="df_budget" class="df_budget" value="<?php echo $df['budget']; ?>" />
							<input type="hidden" id="mtd_sales" class="mtd_sales" value="<?php echo $mtd_sales; ?>" />
							<button type="button" class="btn btn_statistics_tbl">Fetch Data</button>	
						</div>
						<div class="statistics_tbl"></div>
						<div style="text-align: left;" class="instruct_div">	
							<ol>
								<li>Click Todays Date, Fetch Data, Update</li>
								<li>
								<?php 			
									// MAILTO
									if($this->config->item('country')==1){
										$mailto_to = 'figures@sats.com.au';
										$mailto_subject = rawurlencode("Daily figures for ".date('d/m/Y',strtotime($date))." SATS AU");
									}else if($this->config->item('country')==2){
										$mailto_to = 'figures@sats.co.nz';
										$mailto_subject = rawurlencode("Daily figures for ".date('d/m/Y',strtotime($date))." SATS NZ");
									}			
								?>
								<a href="mailto:<?php echo $mailto_to; ?>?Subject=<?php echo $mailto_subject; ?>">CLICK HERE</a> to email figures
								</li>
								<li>Export from <a href="<?php echo $this->config->item('crmci_link') ?>/jobs/merged_jobs" target="__blank">Merged Certificates</a></li>
								<li>
								<?php 			
								// MAILTO
								if($this->config->item('country')==1){
									$mailto_to = 'accounts@sats.com.au';
									$mailto_subject = rawurlencode("MYOB Import for ".date('d/m/Y',strtotime($date))." SATS AU");
								}else if($this->config->item('country')==2){
									$mailto_to = 'accounts@sats.co.nz';
									$mailto_subject = rawurlencode("MYOB Import for ".date('d/m/Y',strtotime($date))." SATS NZ");
								}			
								?>
								<a href="mailto:<?php echo $mailto_to; ?>?Subject=<?php echo $mailto_subject; ?>">CLICK HERE</a> to email Export to Accounts
							</li>
							<li>Mark All Jobs Completed</li>
							</ol>
						</div>

				</div>

			</div>
			

		

		</div>
	</section>


</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>This page captures and stores daily statistics</p>
<pre><code>SELECT *
FROM `daily_figures_per_date`
WHERE `date` = '2021-08-01'
AND `country_id` = 1</code>
<pre>

</div>
<!-- Fancybox END -->


<script type="text/javascript">



jQuery(document).ready(function(){

	// update toggle
	jQuery(".date_link").click(function(e){

		e.preventDefault();
		
		var date = jQuery(this).parents("tr:first").find(".date").val();
		
		jQuery(this).parents("tr:first").find(".display_elem").toggle();
		jQuery(this).parents("tr:first").find(".update_elem").toggle();
		
		if( date!='<?php echo $date ?>' ){
			jQuery(this).parents("tr:first").find(".btn_update").toggle();
		}		
		
	});


	// fetch data ajax
	jQuery(".btn_fetch_data").click(function(){
		
		var obj = jQuery(this);
		var date = jQuery(this).attr("data-date");
		
		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			url: "/reports/ajax_daily_figures_fetch_data",
			dataType: 'json',
			data: {
				date: date
			}
		}).done(function( ret ){			
			jQuery("#load-screen").hide();
			obj.parents("tr:first").find(".sales").val(ret.sales);
			obj.parents("tr:first").find(".sales_ub_os_only").val(ret.sales_ub_os_only);
			obj.parents("tr:first").find(".sales_exc_ic_up").val(ret.sales_exc_ic_up);
			obj.parents("tr:first").find(".sales_ic_up_only").val(ret.sales_ic_up_only);
			obj.parents("tr:first").find(".techs").val(ret.techs);
			obj.parents("tr:first").find(".jobs").val(ret.jobs);
			obj.parents("tr:first").find(".jobs_exc_ub_os").val(ret.jobs_exc_ub_os);
			obj.hide();
			obj.parents("tr:first").find(".btn_update").show();
		});	
		
	});

	// update daily figures per date
	jQuery(".btn_update").click(function(){
		
		var obj = jQuery(this);
		var dfpd_id = obj.parents("tr:first").find(".dfpd_id").val();	
		
		var working_day = obj.parents("tr:first").find(".working_day").val();
		var date = obj.parents("tr:first").find(".date").val();	
		var techs = obj.parents("tr:first").find(".techs").val();
		var jobs = obj.parents("tr:first").find(".jobs").val();
		var jobs_exc_ub_os = obj.parents("tr:first").find(".jobs_exc_ub_os").val();
		var sales = obj.parents("tr:first").find(".sales").val();
		var sales_ub_os_only = obj.parents("tr:first").find(".sales_ub_os_only").val();
		var sales_exc_ic_up = obj.parents("tr:first").find(".sales_exc_ic_up").val();
		var sales_ic_up_only = obj.parents("tr:first").find(".sales_ic_up_only").val();

		var err="";
		if(working_day=="" || working_day==0){
			err+="Working Day cannot be empty."
		}

		if(err!=""){
			swal('',err,'error');
			return false;
		}
		
		jQuery.ajax({
			type: "POST",
			url: "/reports/ajax_update_daily_figures_per_date",
			data: {
				dfpd_id:dfpd_id,
				working_day: working_day,
				date: date,
				techs: techs,
				jobs: jobs,
				jobs_exc_ub_os: jobs_exc_ub_os,
				sales: sales,
				sales_ub_os_only: sales_ub_os_only,
				sales_exc_ic_up: sales_exc_ic_up,
				sales_ic_up_only: sales_ic_up_only
			}
		}).done(function( ret ){	

			swal({
				title:"Success!",
				text: "Update Success",
				type: "success",
				showConfirmButton: false
			});	
			window.location="/reports/daily_figures";
			
		});
		
	});

	// save budget and working days
	jQuery("#btn_save").click(function(){
		
		var budget = jQuery("#budget").val();	
		var working_days = jQuery("#working_days").val();
		
		jQuery.ajax({
			type: "POST",
			url: "/reports/ajax_update_daily_figures",
			data: {
				df_id: '<?php echo $df['daily_figure_id']; ?>',
				month: '<?php echo $start_date ?>',
				budget: budget,
				working_days: working_days
			}
		}).done(function( ret ){

			swal({
				title:"Success!",
				text: "Update Success",
				type: "success",
				showCancelButton: false,
				confirmButtonText: "OK",
				closeOnConfirm: false,
				showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
				timer: <?php echo $this->config->item('timer') ?>
			});	

			setTimeout(function(){
				window.location="/reports/daily_figures";
            },  <?php echo $this->config->item('timer') ?>);

		});
		
	});


	// statistics table
	// update daily figures per date
	jQuery(".btn_statistics_tbl").click(function(){
		
		var from = jQuery("#from").val();
		var to = jQuery("#to").val();
		var df_working_days = jQuery("#df_working_days").val();
		var mtd_sales = jQuery("#mtd_sales").val();
		var df_budget = jQuery("#df_budget").val();
		
		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			url: "/reports/ajax_daily_figures_statistics",
			data: {
				mtd_sales: mtd_sales,
				df_budget: df_budget,
				df_working_days: df_working_days,
				from: from,
				to: to
			}
		}).done(function( ret ){

			jQuery("#load-screen").hide();
			jQuery(".statistics_tbl").html(ret);
			jQuery(".statistics_tbl").show();
		});
		
	});



})

</script>
