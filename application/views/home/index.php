
<style>
	.widget_home_tot .number, .widget_home_tot .statistic strong{
		color: #343434;
		text-transform: capitalize;
	}

	.ribbon {
 position:absolute;
 top:10px;
 right:-1px;
 color:#fff;
 background:#00a8ff;
 padding:5px 12px 5px 9px;
 white-space:nowrap
}
.ribbon.right-top {
 top:10px;
 bottom:auto;
 right:15px;
 left:auto
}
.ribbon.right-top:after {
 position:absolute;
 left:-10px;
 top:0;
 display:block;
 content:'';
 width:0;
 height:0;
 border-style:solid;
 border-width:0 10px 17px 0;
 border-top-color:transparent!important;
 border-right-color:#00a8ff;
 border-bottom-color:transparent!important;
 border-left-color:transparent!important
}
.ribbon.right-top:before {
 position:absolute;
 left:-10px;
 bottom:0;
 display:block;
 content:'';
 width:0;
 height:0;
 border-style:solid;
 border-width:0 0 17px 10px;
 border-top-color:transparent!important;
 border-right-color:transparent!important;
 border-bottom-color:#00a8ff;
 border-left-color:transparent!important
}
.ribbon.right-bottom {
 top:auto;
 bottom:10px;
 right:-1px;
 left:auto
}
.ribbon.right-bottom:after {
 position:absolute;
 left:-10px;
 top:0;
 display:block;
 content:'';
 width:0;
 height:0;
 border-style:solid;
 border-width:0 10px 17px 0;
 border-top-color:transparent!important;
 border-right-color:#00a8ff;
 border-bottom-color:transparent!important;
 border-left-color:transparent!important
}
.ribbon.right-bottom:before {
 position:absolute;
 left:-10px;
 bottom:0;
 display:block;
 content:'';
 width:0;
 height:0;
 border-style:solid;
 border-width:0 0 17px 10px;
 border-top-color:transparent!important;
 border-right-color:transparent!important;
 border-bottom-color:#00a8ff;
 border-left-color:transparent!important
}
.ribbon.left-top {
 padding:5px 9px 5px 12px;
 top:10px;
 bottom:auto;
 right:auto;
 left:-1px
}
.ribbon.left-top:after {
 position:absolute;
 right:-10px;
 left:auto;
 top:0;
 display:block;
 content:'';
 width:0;
 height:0;
 border-style:solid;
 border-width:17px 10px 0 0;
 border-top-color:#00a8ff;
 border-right-color:transparent!important;
 border-bottom-color:transparent!important;
 border-left-color:transparent!important
}
.ribbon.left-top:before {
 position:absolute;
 right:-10px;
 left:auto;
 bottom:0;
 display:block;
 content:'';
 width:0;
 height:0;
 border-style:solid;
 border-width:17px 0 0 10px;
 border-top-color:transparent!important;
 border-right-color:transparent!important;
 border-bottom-color:transparent!important;
 border-left-color:#00a8ff
}
.ribbon.left-bottom {
 padding:5px 9px 5px 12px;
 top:auto;
 bottom:10px;
 right:auto;
 left:-1px
}
.ribbon.left-bottom:after {
 position:absolute;
 right:-10px;
 left:auto;
 top:0;
 display:block;
 content:'';
 width:0;
 height:0;
 border-style:solid;
 border-width:17px 10px 0 0;
 border-top-color:#00a8ff;
 border-right-color:transparent!important;
 border-bottom-color:transparent!important;
 border-left-color:transparent!important
}
.ribbon.left-bottom:before {
 position:absolute;
 right:-10px;
 left:auto;
 bottom:0;
 display:block;
 content:'';
 width:0;
 height:0;
 border-style:solid;
 border-width:17px 0 0 10px;
 border-top-color:transparent!important;
 border-right-color:transparent!important;
 border-bottom-color:transparent!important;
 border-left-color:#00a8ff
}
.ribbon.green {
 background-color:#4cc159
}
.ribbon.green:after,
.ribbon.green:before {
 border-color:#4cc159
}
.ribbon.purple {
 background-color:#00bbd4
}
.ribbon.purple:after,
.ribbon.purple:before {
 border-color:#00bbd4
}
.ribbon.yellow {
 background-color:#fdc006
}
.ribbon.yellow:after,
.ribbon.yellow:before {
 border-color:#fdc006
}
.ribbon.red {
 background-color:#f34135
}
.ribbon.red:after,
.ribbon.red:before {
 border-color:#f34135
}
.ribbon.transparent {
 background-color:rgba(255,255,255,.5)
}
.ribbon.transparent:after,
.ribbon.transparent:before {
 border-color:rgba(255,255,255,.5)
}
.green_success_box div.statistic{
	background: #4cc159;
}
.green_success_box div.statistic strong{
	color: #fff;;
}
.widget-weather-slider .widget-weather-item {
  float: left;
  height: 100%;
  min-height: 1px;
}
.booked_items .degrees{
	font-size: 46px;
	color: #00a8ff;
}
.greetings_box{
	padding: 25px 15px;
}
.greeting_panel{
	margin-bottom: 30px;
}
.greeting_panel header{
	border-bottom: solid 1px #d8e2e7;
}
.junderline_colored {
  color: red;
  text-decoration: underline;
}
.notice_box{
	margin: 15px;
	overflow: auto;
}
.booking_sched_box table tr th{
	/*padding: 8px 15px;*/
	margin-bottom: 30px;;
}
.tech-tr > td {
  display: flex;
  align-items: center;
}
.borderless td, .borderless th {
  border: none;
}
.center-text {
  justify-content: center;
}
.booking_sched_tt_text{
	float: left;
	margin-top: 3px;;
	margin-right: 28px;;
}
.booking_sched_box .panel-heading .fa{
	color: #e2013b;
}
.timezone_main_div{
	display: flex!important;
	padding: 7px!important;
}
.timezone_main_div .time_div{
	width: 20%;;
}
 </style>


 <div class="row">

	            <div class="col-xl-12">
	                <div class="row" id="sort_div_a">
						
						<?php if( in_array(1,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='1'>
							<a href="/jobs/to_be_booked" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('to-be-booked'); ?></div>
										<div class="caption color-blue">TO BE BOOKED</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('to-be-booked'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>
						
						<?php if( in_array(2,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='2'>
							<a href="/jobs/to_be_booked/?&updated_to_240v_rebook=1" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('240v-rebook'); ?></div>
										<div class="caption color-blue">240v Rebook</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('240v-rebook'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>
						
						<?php if( in_array(3,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='3'>
							<a href="/jobs/to_be_booked/?job_type_filter=Fix%20or%20Replace&custom_filter=j.status%20IN%20(%27To%20Be%20Booked%27,%27Allocate%27)&job_status_filter=-1" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('fix-or-replace'); ?></div>
										<div class="caption color-blue">Fix and Replace</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('fix-or-replace'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>
						
						<?php if( in_array(4,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='4'>
							<a href="/jobs/to_be_booked/?show_is_eo=1" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('electrician-only'); ?></div>
										<div class="caption color-blue">Electrician Only</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('electrician-only'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>
						
						<?php if( in_array(5,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='5'>
							<a href="/daily/overdue_nsw_jobs" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('nsw-overdue'); ?></div>
										<div class="caption color-blue">NSW Overdue</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('nsw-overdue'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>
						
						<?php if( in_array(6,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='6'>
								<a href="/jobs/dha" target="_blank">
									<section class="widget widget-simple-sm widget_home_tot">
										<div class="widget-simple-sm-statistic">
											<div class="number"><?php $this->users_model->get_main_count('dha-to-be-booked'); ?></div>
											<div class="caption color-blue">DHA To be booked</div>
										</div>
										<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('dha-to-be-booked'); ?></strong></div>
									</section>
								</a>
	                    </div><!--.col-->
						<?php } ?>
						
						<?php if( in_array(7,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='7'>
							<a href="/reports/dirty_address/" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('dirty-address'); ?></div>
										<div class="caption color-blue">Invalid Address</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('dirty-address'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>
						
						<?php if( in_array(8,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='8'>
							<a href="/daily/multiple_jobs" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('multiple-jobs'); ?></div>
										<div class="caption color-blue">Multiple Jobs</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('multiple-jobs'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>
						
						<?php if( in_array(9,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='9'>
							<a href="/daily/duplicate_visit">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('duplicate-visit'); ?></div>
										<div class="caption color-blue">Duplicate Visits</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('duplicate-visit'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(10,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='10'>
							<a href="/reports/properties_with_coordinates_errors" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('coordinate-errors'); ?></div>
										<div class="caption color-blue">Coordinate Errors</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('coordinate-errors'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(11,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='11'>
							<a href="/daily/active_unsold_services" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('unsold-services'); ?></div>
										<div class="caption color-blue">Active Unsold Services</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('unsold-services'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(12,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='12'>
							<a href="/daily/no_job_types" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('no-job-type'); ?></div>
										<div class="caption color-blue">No Job Type</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('no-job-type'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(13,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='13'>
							<a href="/daily/no_job_status" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('no-job-status'); ?></div>
										<div class="caption color-blue">No Job Status</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('no-job-status'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(14,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='14'>
							<a href="/reports/no_retest_date_property" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('no-retest-date'); ?></div>
										<div class="caption color-blue">No Retest Date</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('no-retest-date'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(15,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='15'>
							<a href="/daily/incorrectly_upgraded_properties" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('data-discrepancy'); ?></div>
										<div class="caption color-blue">Data Discrepancy</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('data-discrepancy'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(16,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='16'>
							<a href="/daily/unserviced" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('unserviced-properties'); ?></div>
										<div class="caption color-blue">Unserviced Properties</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('unserviced-properties'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(17,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='17'>
							<a href="/properties/properties_with_multiple_services" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('multiple-service'); ?></div>
										<div class="caption color-blue">Multiple Services</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('multiple-service'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(18,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='18'>
							<a href="/daily/view_no_active_job_properties" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('no-active-job'); ?></div>
										<div class="caption color-blue">No Active Job</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('no-active-job'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(19,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='19'>
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('dha-completed-last-365-days'); ?></div>
										<div class="caption color-blue">DHA completed (365 Days)</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('dha-completed-last-365-days'); ?></strong></div>
								</section>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(20,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='20'>
								<section class="widget widget-simple-sm widget_home_tot" data-isAmbot>
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('jobs-since-june-2021'); ?></div>
										<div class="caption color-blue">Total Jobs since June 2021</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('jobs-since-june-2021'); ?></strong></div>
								</section>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(21,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='21'>
							<a href="/agency/agency_audits" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('agency-audits-not-completed'); ?></div>
										<div class="caption color-blue">Agency Audits</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('agency-audits-not-completed'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(22,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='22'>
							<a href="#" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('new-agency-list'); ?></div>
										<div class="caption color-blue">New Agency Lists</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('new-agency-list'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(50,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='50'>
							<a href="/daily/active_properties_without_jobs" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('active-properties-without-jobs'); ?></div>
										<div class="caption color-blue">Active Properties Without Jobs</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('active-properties-without-jobs'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(32,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='32'>
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number">&nbsp;</div>
										<div class="caption color-blue">Local Times</div>
									</div>
									<div class="widget-simple-sm-bottom statistic timezone_main_div">
										<div class="time_div">
											QLD
											<br/>
											<?php 
											$date = new DateTime('Australia/Brisbane');
											echo $date->format('H:i');
											?>
										</div>

										<div class="time_div">
											NSW
											<br/>
											<?php 
											$date = new DateTime('Australia/Sydney');
											echo $date->format('H:i');
											?>
										</div>

										<div class="time_div">
											SA
											<br/>
											<?php 
											$date = new DateTime('Australia/Adelaide');
											echo $date->format('H:i');
											?>
										</div>

										<div class="time_div">
											PH
											<br/>
											<?php 
											$date = new DateTime('Asia/Manila');
											echo $date->format('H:i');
											?>
										</div>

										<div class="time_div">
											NZ
											<br/>
											<?php 
											$date = new DateTime('Pacific/Auckland');
											echo $date->format('H:i');
											?>
										</div>
										
									</div>
								</section>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(33,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='33'>
							<a href="<?php echo $this->config->item('crm_link') ?>/platform_invoicing.php?maintenance_program=14" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('dha-to-be-invoiced'); ?></div>
										<div class="caption color-blue">DHA to be Invoiced</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('dha-to-be-invoiced'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(34,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='34'>
							<a href="<?php echo $this->config->item('crm_link') ?>/platform_invoicing.php" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('platform-invoicing'); ?></div>
										<div class="caption color-blue">Platform Invoicing</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('platform-invoicing'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(35,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='35'>
							<a href="/sms/view_incoming_sms" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('incoming-sms'); ?></div>
										<div class="caption color-blue">Incoming SMS</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('incoming-sms'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(36,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='36'>
							<a href="/credit/credit_request_summary" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('credit-request'); ?></div>
										<div class="caption color-blue">Credit Request</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('credit-request'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(37,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='37'>
							<a href="/credit/refund_request_summary" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('refund-request'); ?></div>
										<div class="caption color-blue">Refund Request</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('refund-request'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(38,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='38'>
							<a href="/jobs/to_be_invoiced" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('to-be-invoiced'); ?></div>
										<div class="caption color-blue">To Be Invoiced</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('to-be-invoiced'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(39,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='39'>
							<a href="/jobs/new_jobs" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('new-jobs'); ?></div>
										<div class="caption color-blue">New Jobs</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('new-jobs'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(40,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='40'>
							<a href="/jobs/bne_to_call" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php $this->users_model->get_main_count('office-to-call'); ?></div>
										<div class="caption color-blue">Office to call</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('office-to-call'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(41,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='41'>
							<a href="/jobs/allocate" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php 
										//$this->users_model->get_main_count('to-be-allocated'); 
										echo $this->system_model->get_page_total('/jobs/allocate');
										?></div>
										<div class="caption color-blue">To be Allocated</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('to-be-allocated'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(42,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='42'>
							<a href="/daily/missing_region" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php 
										echo $this->users_model->get_main_count('missing-region'); 
										//echo $this->system_model->get_page_total('/daily/missing_region');
										?></div>
										<div class="caption color-blue">Missing Region</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('missing-region'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(43,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='43'>
							<a href="/properties/duplicate_properties" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php 
										echo $this->users_model->get_main_count('duplicate-properties'); 
										//echo $this->system_model->get_page_total('/properties/duplicate_properties');
										?></div>
										<div class="caption color-blue">Duplicate Properties</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('duplicate-properties'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(44,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='44'>
							<a href="/jobs/escalate" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php 
										//echo $this->system_model->get_page_total('/jobs/escalate');
										echo $this->users_model->get_main_count('escalate');
										?></div>
										<div class="caption color-blue">Escalated Jobs</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('escalate'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(45,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='45'>
							<a href="/daily/action_required_jobs" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php 
										echo $this->users_model->get_main_count('action-required');
										//echo $this->system_model->get_page_total('/daily/action_required_jobs');
										?></div>
										<div class="caption color-blue">Action Required</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('action-required'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(46,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='46'>
							<a href="/property_me/properties_needs_verification" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php 
										//echo $this->system_model->get_page_total('/property_me/properties_needs_verification');
										echo $this->users_model->get_main_count('properties-need-verification');
										?></div>
										<div class="caption color-blue">Properties need Verification</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('properties-need-verification'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(47,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='47'>
							<a href="/jobs/to_be_booked?is_sales=1" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php 
										//echo $this->system_model->get_page_total('/jobs/to_be_booked?is_sales=1');
										echo $this->users_model->get_main_count('sales-upgrade-to-be-booked');
										?></div>
										<div class="caption color-blue">Sales Upgrades To be Booked</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('sales-upgrades-to-be-booked'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(48,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='48'>
							<a href="#" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php 
										echo $this->users_model->get_main_count('ready-to-be-mapped');
										?></div>
										<div class="caption color-blue">Ready to be Mapped</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('ready-to-be-mapped'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>

						<?php if( in_array(49,$tt_rr) ){ ?>
						<div class="col-sm-3 tt_sort_a" data-val='49'>
							<a href="#" target="_blank">
								<section class="widget widget-simple-sm widget_home_tot">
									<div class="widget-simple-sm-statistic">
										<div class="number"><?php 
										echo $this->users_model->get_main_count('call-over-complete');
										?></div>
										<div class="caption color-blue">Call over complete</div>
									</div>
									<div class="widget-simple-sm-bottom statistic"><strong>Target - </strong><strong class="goal_num"><?php $this->users_model->get_main_goal('call-over-complete'); ?></strong></div>
								</section>
							</a>
	                    </div><!--.col-->
						<?php } ?>


	                </div><!--.row-->
	            </div><!--.col-->


	        </div><!--.row-->
	
	        <div class="row" id="sort_div_b">
				
				<?php if( in_array(23,$tt_rr2) ){ ?>
				<div class="col-xl-6 dahsboard-column tt_sort_b" data-val='23'>
					<!--<div class="row">

						<div class="col-xl-6 dahsboard-column"> -->

							<section class="box-typical box-typical-dashboard panel panel-default greeting_panel">	
								<header class="box-typical-header panel-heading">
									<h3 class="panel-title"> Greetings</h3>
								</header>
								<div class="box-typical-body panel-body">
									<div class="greetings_box">
									Good <span class='junderline_colored'>morning/afternoon</span>, Smoke Alarm Testing Services. You’re speaking with <?php echo $sa['FirstName']; ?><br /><br />
									Is there anything else I can help you with today?<br />
									Thanks <span class='junderline_colored'>NAME</span>. Have a great day
									</div>
								</div><!--.box-typical-body-->
							</section><!--.box-typical-dashboard-->

						<!--</div> -->

						<!--<div class="col-xl-6 dahsboard-column">&nbsp;</div>-->

					<!--</div>-->
				</div>
				<?php } ?>

				<?php if( in_array(24,$tt_rr2) ){ ?>
				<div class="col-xl-12 dahsboard-column tt_sort_b" data-val='24'>

					<section class="box-typical panel panel-default scrollable booking_sched_box" style="margin-bottom:30px;">	
						<header class="box-typical-header panel-heading">
							<h3 class="panel-title" style="float: left;margin-right: 26px;">Booking Schedule</h3>
							<span class="booking_sched_tt_text">Runs to check - Display only <?php echo $staff['booking_schedule_num']; ?> days</span>
							<span class="booking_sched_tt_text">Full Schedule <a href="/bookings/view_schedule" target="_blank"><em class="fa fa-external-link-square"></em></a></span>
							<span class="booking_sched_tt_text">CC Schedule <a href="<?php echo $this->config->item('crm_link') ?>/booking_schedule_cc.php" target="_blank"><em class="fa fa-external-link-square"></em></a></span>
							<span class="booking_sched_tt_text">Planner <a href="https://docs.google.com/spreadsheets/d/1XIcN5vF0cEm4qy0M3PEE06wcbPJOlt1-WJnmQ3EVNSg/edit#gid=14323913" target="_blank"><em class="fa fa-external-link-square"></em></a></span>
	                    </header>
						<div class="box-typical-body panel-body" style="height:528px;">
							<div id="tt_ajax_booking_schedl_box"></div>
						</div>
					</section>

				</div>
				<?php } ?>

				<!-- Recent Tickets -->
				<?php if( in_array(25,$tt_rr2) ){ ?>
				<div class="col-xl-6 dahsboard-column tt_sort_b" data-val='25'>

					<section class="box-typical box-typical-dashboard panel panel-default scrollable">
	                    <header class="box-typical-header panel-heading">
	                        <h3 class="panel-title">Recent tickets</h3>
	                    </header>
	                    <div class="box-typical-body panel-body">
	                        <table class="tbl-typical">
	                            <tr>
	                                <th><div>Status</div></th>
	                                <th><div>Subject</div></th>
	                                <th><div>Created By</div></th>
	                                <th align="center"><div>Date</div></th>
	                            </tr>

								<?php
									foreach( $recent_tickets->result_array() as $row ){
										$date = ( $this->system_model->isDateNotEmpty($row['date_created']) )?date('d/m/Y', strtotime($row['date_created'])):null;
								?>
	                            <tr>
	                                <td>
										<?php                        
                                       		switch( $row['ct_status'] ){
                                            case 1: // Pending                                                
                                                $status_class = "btn-primary-outline";
                                            break;
                                            case 2: // Declined                                                
                                                $status_class = "btn-secondary";
                                            break; 
                                            case 3: // In Progress                                                
                                                $status_class = "btn-outline-success";
                                            break;
                                            case 4: // Completed                                                
                                                $status_class = "btn-success";
                                            break;
                                            case 5: // QA                                                
                                                $status_class = "btn-info-outline";
                                            break;
                                            case 6: // More info required                                                
                                                $status_class = "btn-warning-outline";
                                            break;
                                            case 7: // Unable to Replicate                                                
                                                $status_class = "btn-danger-outline";
                                            break;                                           
                                        }          
                                        ?>
										<a href="/reports/ticket_details/?id=<?php echo $row['crm_task_id'] ?>"><button type="button" class="ticket_status btn btn-sm <?php echo $status_class; ?>"><?php echo $row['cts_status']; ?></button></a>
	                                </td>
	                                <td><?php echo $row['issue_summary']; ?></td>
									<td><?php echo $this->system_model->formatStaffName($row['FirstName'], $row['LastName']); ?></td>
	                                <td><?php echo $date; ?></td>	
	                            </tr>
								<?php } ?>
	                        </table>
	                    </div><!--.box-typical-body-->
	                </section><!--.box-typical-dashboard-->
					
				</div>
				<?php } ?>

				<?php if( in_array(26,$tt_rr2) ){ ?>
				<div class="col-xl-6 dahsboard-column tt_sort_b" data-val='26'>
					<section class="box-typical box-typical-dashboard panel panel-default scrollable">
	                    <header class="box-typical-header panel-heading">
	                        <h3 class="panel-title">Expense Statements</h3>
	                    </header>
	                    <div class="box-typical-body panel-body">
	                        <div class="contact-row-list">
	                           
							<table class="tbl-typical">

								<?php foreach( $expense_summary->result_array() as $exp_sum ){
								?>
									<tr style="border:none !important;">
										<td style="text-align:left;">
											<a href="/reports/view_expense_summary_details/?id=<?php echo $exp_sum['expense_summary_id'] ?>">
												<img src="/images/expense.png" />
											</a>
										</td>
										<td style="text-align:left;"><?php echo date('d/m/Y',strtotime($exp_sum['date'])); ?></td>
										<td style="text-align:left;"><?php echo "{$exp_sum['sa_fname']} {$exp_sum['sa_lname']}"; ?></td>
										<td style="text-align:left;">$<?php echo $exp_sum['total_amount']; ?></td>
									</tr>
								<?php
								} ?>
								

							</table>
	                           
	                        </div>
	                    </div><!--.box-typical-body-->
	                </section><!--.box-typical-dashboard-->
				</div>
				<?php } ?>

				<?php if( in_array(27,$tt_rr2) ){ ?>
				<div class="col-xl-6 dahsboard-column tt_sort_b" data-val='27'>
				
	                <section class="box-typical box-typical-dashboard panel panel-default scrollable">	
	                    <header class="box-typical-header panel-heading">
	                        <h3 class="panel-title">Cars</h3>
	                    </header>
	                    <div class="box-typical-body panel-body">
	                        <table class="tbl-typical">
								<?php
								foreach( $vehicles as $v ){
									$k = $v['kms'];
									$crm_ci_page = '/vehicles/view_vehicle_details/' . $v['vehicles_id'];
									$kms_left = $v['next_service'] - $k['kms'];
									$page_url = $crm_ci_page;

									if( $kms_left<=1000 || $v['serviced_booked']==1 ){
								?>
										<tr style="border:none !important;">
											<td style="text-align:left;"><a href="<?php echo $page_url; ?>"><img src="/images/<?php echo ($v['serviced_booked']==1)?'car_green.png':'car.png'; ?>" /></a></td>
											<td style="text-align:left;"><a href="<?php echo $page_url; ?>"><?php echo $v['number_plate'] ?></a></td>
											<td style="text-align:left;"><?php echo $this->system_model->formatStaffName($v['FirstName'],$v['LastName']); ?></td>
											<td style="text-align:left;">Service in <span style="<?php echo ($kms_left<0)?'color:red;':''; ?>"><?php echo $kms_left; ?></span> kms</td>
										</tr>
									<?php } 

									// 30 days before <rego expires>
									if( date('Y-m-d') >= date('Y-m-d',strtotime($v['rego_expires']."-30 days")) ){
									?>

									<tr style="border:none !important;">
										<td style="text-align:left;">
											<a href="<?php echo $page_url; ?>">
													<img src="/images/rego_icon.png" />
											</a>
										</td>
										<td style="text-align:left;">
											<a href="<?php echo $page_url; ?>"><?php echo $v['number_plate'] ?></a>
										</td>
										<td style="text-align:left;"><?php echo $this->system_model->formatStaffName($v['FirstName'],$v['LastName']); ?></td>
										<td style="text-align:left;">Rego due on <?php echo date('d/m/Y',strtotime($v['rego_expires'])); ?></td>
									</tr>

								<?php
									}
									if($v['country_id'] == 2){

									// 30 days before <wof expires>
									if( date('Y-m-d') >= date('Y-m-d',strtotime($v['WOF']."-30 days")) ){
										?>
	
										<tr style="border:none !important;">
											<td style="text-align:left;">
												<a href="<?php echo $page_url; ?>">
														<img src="/images/wof.png" />
												</a>
											</td>
											<td style="text-align:left;">
												<a href="<?php echo $page_url; ?>"><?php echo $v['number_plate'] ?></a>
											</td>
											<td style="text-align:left;"><?php echo $this->system_model->formatStaffName($v['FirstName'],$v['LastName']); ?></td>
											<td style="text-align:left;">WOF due on <?php echo date('d/m/Y',strtotime($v['WOF'])); ?></td>
										</tr>
	
									<?php
										}
									}
								}
								?>
	                        </table>
	                    </div><!--.box-typical-body-->
	                </section><!--.box-typical-dashboard-->
					
				</div>
				<?php } ?>

				<?php if( in_array(29,$tt_rr2) ){ ?>
				<div class="col-xl-6 dahsboard-column tt_sort_b" data-val='29'>
					<section class="box-typical box-typical-dashboard panel panel-default scrollable">
	                    <header class="box-typical-header panel-heading">
	                        <h3 class="panel-title">Leave Requests</h3>
	                    </header>
	                    <div class="box-typical-body panel-body">
	                       
						<table class="tbl-typical">
							<?php  foreach( $lists_leave_req->result_array() as $leave ){
								$crm_ci_page = "/users/leave_details/{$leave['leave_id']}";
								$leave_det_url = $crm_ci_page;
							?>
								<tr style="border:none !important;">
									<td style="text-align:left;"><a href="<?php echo $leave_det_url; ?>"><img src="/images/leave.png" /></a></td>
									<td style="text-align:left;"><?php echo date('d/m/Y',strtotime($leave['date'])); ?></td>
									<td style="text-align:left;"><?php echo "{$leave['emp_fname']} {$leave['emp_lname']}"; ?></td>
									<td style="text-align:left;"><?php echo $this->users_model->getLeaveType($leave['type_of_leave']); ?></td>
								</tr>
							<?php
							} ?>
						</table>

	                    </div><!--.box-typical-body-->
	                </section>
				</div>
				<?php } ?>

				<?php if( in_array(31,$tt_rr2) ){ ?>
				<div class="col-xl-6 dahsboard-column tt_sort_b" data-val='31'>
					<section class="box-typical box-typical-dashboard panel panel-default scrollable">
	                    <header class="box-typical-header panel-heading">
	                        <h3 class="panel-title">Staff Dates</h3>
	                    </header>
	                    <div class="box-typical-body panel-body">
	                       
						<table class="tbl-typical">
							<?php  foreach( $staff_dates->result_array() as $d ){ ## BDAY
								$dob_exp = explode("-", $d['dob']);
								$exp_dob = $dob_exp[2];
			
								$exp_toda = explode("-", date("Y-m-d"));
								$exp_today = $exp_toda[2];
			
								$dob_count_days = $exp_dob - $exp_today;
			
								if($dob_count_days<0){
									$new_dob_count_days = $dob_count_days + $num_days_of_month_days;
								}else{
									$new_dob_count_days = $dob_count_days;
								}
							?>
								<tr style="border:none !important;">
									<td style="text-align:left;"><img src="/images/gift.png" alt="" /></span></td>
									<td style="text-align:left;"><?php echo date("F jS",strtotime($d['dob'])) ?></td>
									<td style="text-align:left;"><?php echo $d['FirstName'] ?> <?php echo $d['LastName'] ?></td>
									<td style="text-align:left;">Birthday <?php echo ($new_dob_count_days==0)?"is <span style='color:red;'>Today</span>":"in {$new_dob_count_days} Days"; ?></td>
								</tr>
							<?php
							} ?>

							<?php foreach( $staff_anniv->result_array() as $d ){ ##ANNIV
								$anniv_exp = explode("-", $d['start_date']);
								$anniv_dob = $anniv_exp[2];
			
								$anniv_toda = explode("-", date("Y-m-d"));
								$anniv_today = $anniv_toda[2];
			
								$anniv_count_days = $anniv_dob - $anniv_today;
			
								if($anniv_count_days<0){
									$new_anniv_count_days = $anniv_count_days + $num_days_of_month_days;
								}else{
									$new_anniv_count_days = $anniv_count_days;
								}
							?>
								<tr style="border:none !important;">
									<td style="text-align:left;"><img style="height:32px;" src="/images/anniv_icons.png" alt="" /></span></td>
									<td style="text-align:left;"><?php echo date("F jS",strtotime($d['start_date'])) ?></td>
									<td style="text-align:left;"><?php echo $d['FirstName'] ?> <?php echo $d['LastName'] ?></td>
									<td style="text-align:left;">Anniversary <?php echo ($new_anniv_count_days==0)?"is <span style='color:red;'>Today</span>":"in {$new_anniv_count_days} Days"; ?></td>
								</tr>
							<?php
							} ?>

							<?php foreach( $blue_card->result_array() as $d ){
							?>
								<tr style="border:none !important;">
									<td style="text-align:left;"><img src="/images/rego_icon.png" alt="" /></span></td>
									<td style="text-align:left;"><?php echo date("F jS",strtotime($d['blue_card_expiry'])) ?></td>
									<td style="text-align:left;"><?php echo $d['FirstName'] ?> <?php echo $d['LastName'] ?></td>
									<td style="text-align:left;">Blue Card Expiry <?php echo ($d['rem_days']==0)?"is <span style='color:red;'>Today</span>":"in {$d['rem_days']} ".( ($d['rem_days'])==1?'Day':'Days' ); ?></td>
								</tr>
							<?php
							} ?>

							<?php foreach( $license_exp->result_array() as $d ){
							?>
								<tr style="border:none !important;">
									<td style="text-align:left;"><img src="/images/rego_icon.png" alt="" /></span></td>
									<td style="text-align:left;"><?php echo date("F jS",strtotime($d['licence_expiry'])) ?></td>
									<td style="text-align:left;"><?php echo $d['FirstName'] ?> <?php echo $d['LastName'] ?></td>
									<td style="text-align:left;">Licence Expiry <?php echo ($d['rem_days']==0)?"is <span style='color:red;'>Today</span>":"in {$d['rem_days']} ".( ($d['rem_days'])==1?'Day':'Days' ); ?></td>
								</tr>
							<?php
							} ?>

							<?php foreach( $electrical_licence->result_array() as $d ){
							?>
								<tr style="border:none !important;">
									<td style="text-align:left;"><img src="/images/rego_icon.png" alt="" /></span></td>
									<td style="text-align:left;"><?php echo date("F jS",strtotime($d['elec_licence_expiry'])) ?></td>
									<td style="text-align:left;"><?php echo $d['FirstName'] ?> <?php echo $d['LastName'] ?></td>
									<td style="text-align:left;">Electrical Licence Expiry <?php echo ($d['rem_days']==0)?"is <span style='color:red;'>Today</span>":"in {$d['rem_days']} ".( ($d['rem_days'])==1?'Day':'Days' ); ?></td>
								</tr>
							<?php
							} ?>

						</table>

	                    </div><!--.box-typical-body-->
	                </section>
				</div>
				<?php } ?>

				<?php if( in_array(28,$tt_rr2) ){ ?>
				<div class="col-xl-6 dahsboard-column tt_sort_b" data-val='28'>
					<section class="widget widget-weather">
							<div class="widget-weather-big" style="height:203px;">
								<div class="icon" style="line-height:203px;">
									<i class="fa fa-wrench"></i>
								</div>
								<div class="info" style="padding-top:62px;">
									<div class="degrees"><?php  $this->users_model->get_main_count('booked-jobs') ?></div>
									<div class="weather">Booked</div>
								</div>
							</div>
							<div class="widget-weather-content widget-weather-slider">

							<?php 
							$day_loop = 1;
							for( $i = 1; $i <= 4; $i++ ){
								$booked_date_ts = strtotime("+{$day_loop} days");
								$booking_day = date('l',$booked_date_ts);
								if( $booking_day == 'Saturday' ){ // +2 days to skip Sunday
									$day_loop += 2;
								}else{
									$day_loop++;
								}
							?>

								<div class="widget-weather-item booked_items">
									<div class="widget-weather-item-time"><?php echo date('l',$booked_date_ts) ?></div>
									<div class="widget-weather-item-info">
										<div class="degrees"><?php $this->users_model->get_main_count(date('l',$booked_date_ts)); ?></div>
									</div>
								</div>
							<?php } ?>

							</div>
					</section>
				</div>
				<?php } ?>

				<?php if( in_array(30,$tt_rr2) ){ ?>
				<div class="col-xl-6 dahsboard-column tt_sort_b" data-val='30'>		
					<section class="box-typical box-typical-dashboard panel panel-default greeting_panel">	
						<header class="box-typical-header panel-heading">
							<h3 style="float:left;" class="panel-title">Agency Noticeboard</h3>
							<div style="float:right;"><span><small>Last Updated: <?php echo date("d/m/Y @ H:i",strtotime($notice_board['date_updated'])); ?></small></span> <a href="<?php echo $this->config->item('crm_link') ?>/noticeboard.php">Update</a></div>
						</header>
						<div class="box-typical-body panel-body">
							<div class="notice_box">
							<?php
								echo $notice_board['notice'];
							?>
							</div>
						</div><!--.box-typical-body-->
					</section><!--.box-typical-dashboard-->
				</div><!--.col-->
				<?php } ?>


			</div>


<script src="/inc/js/lib/slick-carousel/slick.min.js"></script>
<script type="text/javascript">

	jQuery(document).ready(function(){

		//small boxes success tweak
		var html_content = "<div class='ribbon green right-top'><i class='fa fa-star'></i><span> Success</span></div>";
		$('.widget_home_tot').each(function(){
			var a = $(this).find('.number').text().trim();
			var b = $(this).find('.goal_num').text().trim();
			var isAmbot = $(this).attr('data-isAmbot');

			if (typeof isAmbot !== 'undefined' && isAmbot !== false) { //if has isAmbot attribute box that has count >= goal = show green success
				if( a >= b && ( a!="" && b!="" ) ){
					$(this).addClass('green_success_box');
					$(this).prepend(html_content);
				}
			}else{
				if( ( parseInt(a)==parseInt(b) ||  parseInt(a) < parseInt(b) ) && ( a!="" && b!="" ) ){
					$(this).addClass('green_success_box');
					$(this).prepend(html_content);
				}
			}

			
		})
		//small boxes success tweak end

		$('.widget-weather-slider').slick({
				arrows: false,
				dots: true,
				infinite: false,
				slidesToShow: 4,
				slidesToScroll: 1
			});

		$.ajax({
			url:'/bookings/view_schedule',
			type:'GET',
			success: function(data){
				$('#tt_ajax_booking_schedl_box').html($(data).find('#booking_sched_ajax_box').html());
			}
		});


		//on load sort div---------------
		//category 1 blocks sort tweak
		var myArray = <?php echo '['.implode(', ', $tt_rr).'];' ?>
		var elArray = [];
		$('.tt_sort_a').each(function() {
			elArray[$(this).attr('data-val')] = $(this);
		});
		$.each(myArray,function(index,value){
		$('#sort_div_a').append(elArray[value]); 
		});
		//category 1 blocks sort tweak end

		//category 2 blocks sort tweak
		var myArray2 = <?php echo '['.implode(', ', $tt_rr2).'];' ?>
		$('.tt_sort_b').each(function() {
			elArray[$(this).attr('data-val')] = $(this);
		});
		$.each(myArray2,function(index,value){
		$('#sort_div_b').append(elArray[value]); 
		});
		//category 2 blocks sort tweak end
		//on load sort div end---------------
		


	})

</script>