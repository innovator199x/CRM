<style>
	.col-mdd-3{
		max-width:12.5%;
	}
.toprow th{
    background:#00a8ff!important;
    color:#fff;
}
.jRightSideBorder{
	border-right:1px solid #d8e2e7;
}
.fixed-table-container{
	height:620px;
}
.btn_update{margin-bottom: 3px;}
table.awawaw .txt_hid{
	padding:3px;
}
#add_figure_fancybox{
	width:500px;
}
.icon_actions .fa{
	/**font-size: 17px; */
} 
.icon_actions .glyphicon{
	/**font-size: 14px; */
} 
.icon_actions .font-icon, .icon_actions .glyphicon{
	color:#adb7be;
}
.action_div a.btn_edit:hover .font-icon{
	color:#00a8ff;
}
.action_div a.btn_delete:hover .glyphicon{
	color:#fb6067;
}

#jobs_section_th,
#revenue_section_th{
	cursor: pointer;
}
.job_section,
.revenue_section{
	display:none;
}
.five_digits{
	width: 55px;
}
</style>
<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
        array(
			'title' => "Reports",
			'link' => "/reports"
		),
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/reports/figures"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<div class="fixed-table-container_tt">
				<div class="fixed-table-header-columns">
					<div>Please note:</div>
					<ul class="disc">
						<li>Figures prior to September 2021 are inclusive of GST</li>
						<li>Property numbers Prior to September 2021 are inclusive of DHA</li>
					</ul>
					<p></p>
					<table class="table table-hover main-table table-xs awawaw">
						<thead>
							<tr>
								<th colspan="3" align="center" class="jRightSideBorder text-center">MONTH</th>
								<th colspan="3" align="center" class="jRightSideBorder text-center">PROPERTIES</th>
								<th 
									id="jobs_section_th" 
									colspan="<?php //echo ($this->config->item('country')==1) ? '10' : '9'; ?>" 
									align="center" 
									class="jRightSideBorder text-center"
								>								
									JOBS
									<span class="fa fa-angle-double-right jobs_expand_icon"></span> 
								</th>
								<?php if( $this->config->item('country')==1 ){ ?>
								<!--<th align="center" class="jRightSideBorder text-center">UPGRADES</th>-->
								<?php } ?>
								<th colspan="3" align="center" class="jRightSideBorder text-center">SALES</th>
								<th 
									id="revenue_section_th" 
									colspan="<?php //echo ($this->config->item('country')==1) ? '5' : '4'; ?>" 
									align="center" 
									class="jRightSideBorder text-center"
								>
									REVENUE 
									<span class="fa fa-angle-double-right revenue_expand_icon"></span> 
								</th>							
								<th colspan="2" class="jRightSideBorder text-center">COMP. REVENUE</th>			
								<th colspan="3" align="center" class="jRightSideBorder text-center">TECHNICIANS</th>
								<th colspan="2" class="text-center">&nbsp;</td>
							</tr>
							<tr class="toprow jalign_left">
								<!-- MONTH -->
								<th>Month</th>
								<th>Year</th>
								<th class="jRightSideBorder">Days</th>
								
								<!-- Properties -->
								<th>Actual</th>
								<th>Last Mo.</th>
								<th class="jRightSideBorder">Net+/-</th>
								
								<!-- JOBS -->
								<th class="job_section">YM</th>
								<th class="job_section">O/Off</th>
								<th class="job_section">COT</th>
								<th class="job_section">LR</th>
								<th class="job_section">FR</th>
								<?php if( $this->config->item('country')==1 ){ ?>	
								<th class="job_section">Upgrade</th>	
								<?php } ?>	
								<th class="job_section">Annual</th>					
								<th class="job_section">Upfronts</th>
								<th class="job_section">240v Rebook</th>
								<th class="jRightSideBorder jobs_tot_col"><a data-toggle="tooltip" title="Upfronts & 240 Excluded">Total</a></th>
								<th class="jRightSideBorder job_section">O/S</th>														
								
								<!-- UPGRADES -->
								<?php if( $this->config->item('country')==1 ){ ?>	
								<!--<th class="jRightSideBorder">Income</th>		-->					
								<?php } ?>	
								
								<!-- SALES -->
								<th>New</th>
								<th>Renewed</th>
								<th class="jRightSideBorder">Lost</th>
								
								<!-- REVENUE -->
								<th class="revenue_section">Budget</th>
								<th class="revenue_section">Actual</th>
								<th class="revenue_section">Diff+/-</th>
								<?php if( $this->config->item('country')==1 ){ ?>	
								<th class="revenue_section">Upgrades</th>					
								<?php } ?>	
								<th class="jRightSideBorder daily_avg_col">Daily Avg</th>
								
								<!-- COMP. REVENUE -->
								<th>Prev. Year</th>
								<th class="jRightSideBorder">Diff</th>
								
								<!-- TECHNICIANS -->
								<th>Techs</th>
								<th>Daily Avg</th>
								<th class="jRightSideBorder">Mo. Avg</th>
								
								<th class="text-center" colspan="2">Action</td>
							</tr>
						</thead>

						<tbody>
						<?php 

							foreach($figure_sql->result_array() as $fig){
						?>
								<tr>
										<!-- MONTH -->
										<td>
											<span class="txt_lbl"><?php echo $months_arr[$fig['month']-1]; ?></span>											
											<select class="txt_hid form-control month" name="month" id="month" style="width:auto;">
												<option value="">--Select--</option>
												<?php
												foreach( $months_arr AS $index => $month ){ ?>
													<option value="<?php echo $index+1; ?>" <?php echo ($index+1==$fig['month'])?'selected="selected"':''; ?>><?php echo $month; ?></option>
												<?php	
												}
												?>
											</select>
										</td>
										<td>
											<span class="txt_lbl"><?php echo $fig['year']; ?></span>
											<input type="text" class="txt_hid form-control year" value="<?php echo $fig['year']; ?>" />
										</td>
										<td class="jRightSideBorder">
											<span class="txt_lbl"><?php echo ( $fig['working_days']!='' )?$fig['working_days']:''; ?></span>
											<input type="text" class="txt_hid form-control working_days" value="<?php echo $fig['working_days']; ?>" />
										</td>	



										<!-- PROPERTIES -->
										<td>
											<span class="txt_lbl"><?php echo ( $fig['p_actual']!='' )?number_format($fig['p_actual']):''; ?></span>
											<input type="text" class="txt_hid form-control p_actual" value="<?php echo $fig['p_actual']; ?>" />
										</td>
										<td>
											<span class="txt_lbl"><?php echo ( $fig['p_last_month']!='' )?number_format($fig['p_last_month']):''; ?></span>
											<input type="text" class="txt_hid form-control p_last_month" value="<?php echo $fig['p_last_month']; ?>" />
										</td>
										<td class="jRightSideBorder">
											<?php
											$diff = $fig['p_actual']-$fig['p_last_month'];
											$diff_fin = ($diff/$fig['p_last_month'])*100;
											?>
											<span class="txt_lbl" style="color:<?php echo ($diff_fin>0)?'green':'red'; ?>; font-weight: bold;">
												<?php echo (!is_nan(floor($diff_fin)))?floor($diff_fin):'0'; ?>%
											</span>
											<span class="txt_hid "><?php echo (!is_nan(floor($diff_fin)))?floor($diff_fin):'0'; ?>%</span>											
										</td>
												


										<!-- JOBS -->
										<td class="job_section">
											<span class="txt_lbl"><?php echo ( $fig['ym']!='' )?$fig['ym']:''; ?></span>
											<input type="text" class="txt_hid ym form-control" value="<?php echo $fig['ym']; ?>" />
										</td>
										<td class="job_section">
											<span class="txt_lbl"><?php echo ( $fig['of']!='' )?$fig['of']:''; ?></span>
											<input type="text" class="txt_hid of form-control" value="<?php echo $fig['of']; ?>" />
										</td>
										<td class="job_section">
											<span class="txt_lbl"><?php echo ( $fig['cot']!='' )?$fig['cot']:''; ?></span>
											<input type="text" class="txt_hid cot form-control" value="<?php echo $fig['cot']; ?>" />
										</td>
										<td class="job_section">
											<span class="txt_lbl"><?php echo ( $fig['lr']!='' )?$fig['lr']:''; ?></span>
											<input type="text" class="txt_hid lr form-control" value="<?php echo $fig['lr']; ?>" />
										</td>
										<td class="job_section">
											<span class="txt_lbl"><?php echo ( $fig['fr']!='' )?$fig['fr']:''; ?></span>
											<input type="text" class="txt_hid fr form-control" value="<?php echo $fig['fr']; ?>" />
										</td>	
										<?php if( $this->config->item('country')==1 ){ ?>
										<td class="job_section">
											<span class="txt_lbl"><?php echo ( $fig['upgrades']!='' )?$fig['upgrades']:''; ?></span>
											<input type="text" class="txt_hid upgrades form-control" value="<?php echo $fig['upgrades']; ?>" />
										</td>
										<?php } ?>
										<td class="job_section">
											<span class="txt_lbl"><?php echo ( $fig['annual']!='' )?$fig['annual']:''; ?></span>
											<input type="text" class="txt_hid annual form-control" value="<?php echo $fig['annual']; ?>" />
										</td>

										<td class="job_section">
											<span class="txt_lbl"><?php echo ( $fig['upfronts']!='' )?$fig['upfronts']:''; ?></span>
											<input type="text" class="txt_hid upfronts form-control" value="<?php echo $fig['upfronts']; ?>" />
										</td>

										<!-- 240v Rebook -->
										<td class="job_section">
											<span class="txt_lbl"><?php echo ( $fig['240v_rebook']!='' )?$fig['240v_rebook']:''; ?></span>
											<input type="text" class="txt_hid 240v_rebook form-control" value="<?php echo $fig['240v_rebook']; ?>" />
										</td>
										<!-- 240v Rebook edn -->

										<td class="jRightSideBorder jobs_tot_col">
											<?php $total_completed = $fig['ym']+$fig['of']+$fig['cot']+$fig['lr']+$fig['fr']+$fig['upgrades']+$fig['annual']; ?>
											<span class="txt_lbl"><strong><?php echo ( $total_completed>0 )?number_format($total_completed):''; ?></strong></span>								
											<span class="txt_hid"><?php echo $total_completed; ?></span>	
										</td>	

										<td class="jRightSideBorder job_section">
											<span class="txt_lbl"><?php echo $fig['jobs_not_comp']; ?></span>
											<input type="text" class="txt_hid jobs_not_comp form-control five_digits" value="<?php echo $fig['jobs_not_comp']; ?>" />
										</td>	



										<!-- UPGRADES -->
										<?php if( $this->config->item('country')==1 ){ ?>
										<!--<td class="jRightSideBorder">
											<span class="txt_lbl"><?php echo ( $fig['upgrades_income']>0 )?'$'.number_format($fig['upgrades_income']):''; ?></span>
											<input type="text" class="txt_hid upgrades_income form-control" value="<?php echo $fig['upgrades_income']; ?>" />
										</td>	-->						
										<?php } ?>

										<!-- SALES -->
										<td>
											<span class="txt_lbl"><?php echo ( $fig['new_sales']!='' )?$fig['new_sales']:''; ?></span>
											<input type="text" class="txt_hid new_sales form-control five_digits" value="<?php echo $fig['new_sales']; ?>" />
										</td>
										<td>
											<span class="txt_lbl"><?php echo ( $fig['renewals']!='' )?number_format($fig['renewals']):''; ?></span>
											<input type="text" class="txt_hid renewals form-control five_digits" value="<?php echo $fig['renewals']; ?>" />
										</td>
										<td class="jRightSideBorder">
											<span class="txt_lbl"><?php echo ( $fig['lost']!='' )?$fig['lost']:''; ?></span>
											<input type="text" class="txt_hid lost form-control five_digits" value="<?php echo $fig['lost']; ?>" />
										</td>
										

										<!-- REVENUE -->
										<td class="revenue_section">
											<span class="txt_lbl">$<?php echo number_format($fig['budget']); ?></span>
											<input type="text" class="txt_hid budget form-control" value="<?php echo $fig['budget']; ?>" />
										</td>
										<td class="revenue_section">
											<span class="txt_lbl" style="font-weight: bold;">$<?php echo number_format($fig['actual']); ?></span>
											<input type="text" class="txt_hid actual form-control" value="<?php echo $fig['actual']; ?>" />
										</td>
										<td class="revenue_section">
											<?php 
											$difference = $fig['actual']-$fig['budget'];
											?>
											<span class="txt_lbl" style="color:<?php echo ($difference>0)?'green':'red'; ?>;">$
											<?php echo number_format($difference); ?>
											</span>											
											<span class="txt_hid">$<?php echo number_format($difference); ?></span>	
										</td>

										<?php if( $this->config->item('country')==1 ){ ?>
										<td class="revenue_section">
											<span class="txt_lbl"><?php echo ( $fig['upgrades_income']>0 )?'$'.number_format($fig['upgrades_income']):''; ?></span>
											<input type="text" class="txt_hid upgrades_income form-control" value="<?php echo $fig['upgrades_income']; ?>" />
										</td>							
										<?php } ?>

										<td class="jRightSideBorder daily_avg_col">
											<span class="txt_lbl">$<?php  
											//$daily_avg = number_format($fig['actual']/$fig['working_days']); 
											$daily_avg = $fig['actual']/$fig['working_days']; 
											echo (!is_infinite($daily_avg))?number_format($daily_avg):'0';
											?></span>											
											<span class="txt_hid">$<?php echo (!is_infinite($daily_avg))?number_format($daily_avg):'0'; ?></span>	
										</td>


										<!-- COMP. REVENUE -->
										<td>
											<span class="txt_lbl">$<?php echo number_format($fig['prev_year']); ?></span>
											<input type="text" class="txt_hid prev_year form-control" value="<?php echo $fig['prev_year']; ?>" />
										</td>
										<td class="jRightSideBorder">
											<?php
											$diff = $fig['actual']-$fig['prev_year'];
											$diff_fin = ($diff/$fig['prev_year'])*100;
											?>
											<span class="txt_lbl" style="color:<?php echo ($diff_fin>0)?'green':'red'; ?>;">
											<?php echo (!is_nan(floor($diff_fin)) && !is_infinite(floor($diff_fin)) )?floor($diff_fin):'0'; ?>%
											</span>						
											<span class="txt_hid"><?php echo $diff_fin; ?></span>	
										</td>


										<!-- TECHNICIANS -->
										<td>	
											<span class="txt_lbl"><?php echo $fig['techs']; ?></span>
											<input type="text" class="txt_hid techs form-control" value="<?php echo $fig['techs']; ?>" />
										</td>
										<td class="jRightSideBorder">
											<?php $techs_daily_avg = $fig['actual']/($fig['techs'] * $fig['working_days']); ?>
											<span class="txt_lbl">$<?php echo (is_numeric($techs_daily_avg) && !is_infinite($techs_daily_avg)) ?number_format($techs_daily_avg, 2):'0.00'; ?></span>
											<span class="txt_hid">$<?php echo (is_numeric($techs_daily_avg) && !is_infinite($techs_daily_avg)) ?number_format($techs_daily_avg, 2):'0.00'; ?></span>																					
										</td>											
										<td class="jRightSideBorder">
											<?php $techs_monthly_avg = $fig['actual']/$fig['techs']; ?>
											<span class="txt_lbl">$<?php echo (is_numeric($techs_monthly_avg) && !is_infinite($techs_monthly_avg)) ? number_format($techs_monthly_avg, 2) : '0.00'; ?></span>
											<span class="txt_hid">$<?php echo (is_numeric($techs_monthly_avg) && !is_infinite($techs_monthly_avg)) ? number_format($techs_monthly_avg, 2) : '0.00'; ?></span>	
										</td>


										<td class="text-center action_div">
											<input type="hidden" class="figures_id" value="<?php echo $fig['figures_id']; ?>" readonly="readonly" />

											<a data-toggle="tooltip" title="Edit" href="javascript:void(0);" class="btn_del_vf icon_actions btn_edit"><span class="font-icon font-icon-pencil"></span></a>

											<button class="blue-btn btn btn-sm submitbtnImg btn_update" style="display:none;">Update</button>
											<button class="submitbtnImg btn btn-danger btn-sm btn_cancel" style="display:none;">Cancel</button>
										</td>
										<td data-toggle="tooltip" title="Delete" class="text-center action_div"><a href="#" class="btn_delete icon_actions"><span class="glyphicon glyphicon-trash"></span></a></td>

								</tr>
						<?php
							}
						?>

						</tbody>

					</table>

					<nav aria-label="Page navigation example" style="text-align:center">
						<?php echo $pagination; ?>
					</nav>

					<div class="pagi_count text-center">
						<?php echo $pagi_count; ?>
					</div>

				</div>
			
			</div>


		</div>
		<button href="#add_figure_fancybox" style="margin-top:15px;margin-bottom:20px;" class="btn btn_add_data fancybox_btn">Add Data</button>

		<!-- ADD FIGURE FORM -->
		<div style="display:none;" id="add_figure_fancybox">
			<h4>Add New Figures</h4>
			<?php echo form_open('/reports/add_figures', 'id=form_figures') ?>

					<div class="row form-group">
						<label class="addlabel col-md-3 columns" for="month">Month</label>
						<div class="col-md-9 columns">
							<select name="month"  id="month" class="form-control">
								<option value="">--Select--</option>
								<?php
								foreach( $months_arr AS $index=>$month ){ ?>
									<option value="<?php echo $index+1; ?>"><?php echo $month; ?></option>
								<?php	
								}
								?>
							</select>
						</div> 
					</div> 

					<div class="row form-group">
						<label class="addlabel col-md-3 columns" for="month">Year</label>
						<div class="col-md-9 columns">
							<input type="text" name="year" id="year" class="year form-control">
						</div> 
					</div> 

					<div class="row form-group">
						<label class="addlabel col-md-3 columns" for="month">Days</label>
						<div class="col-md-9 columns">
							<input type="text" name="working_days" id="working_days" class="working_days form-control">
						</div> 
					</div> 

					<div class="row form-group">
						<label class="addlabel col-md-3 columns" for="month">Actual (Properties)</label>
						<div class="col-md-9 columns">
						<input type="text" name="p_actual" id="p_actual" class="p_actual form-control">
						</div> 
					</div> 

					<div class="row form-group">
						<label class="addlabel col-md-3 columns" for="month">Last Month (Properties)</label>
						<div class="col-md-9 columns">
						<input type="text" name="p_last_month" id="p_last_month" class="p_last_month form-control">
						</div> 
					</div> 

					<div class="row form-group">
						<label class="addlabel col-md-3 columns" for="month">YM</label>
						<div class="col-md-9 columns">
						<input type="text" name="ym" id="ym" class="ym form-control">
						</div> 
					</div> 

					<div class="row form-group">
						<label class="addlabel col-md-3 columns" for="month">ONCE OFF</label>
						<div class="col-md-9 columns">
						<input type="text" name="of" id="of" class="of form-control">
						</div> 
					</div> 

					<div class="row form-group">
						<label class="addlabel col-md-3 columns" for="month">COT</label>
						<div class="col-md-9 columns">
						<input type="text" name="cot" id="cot" class="cot form-control">
						</div> 
					</div> 
					
					<?php if( $this->config->item('country') == 1 ){ ?>
					<div class="row form-group">
						<label class="addlabel col-md-3 columns" for="month">LR</label>
						<div class="col-md-9 columns">
						<input type="text" name="lr" id="lr" class="lr form-control">
						</div> 
					</div> 
					<?php } ?>

					<div class="row form-group">
						<label class="addlabel col-md-3 columns" for="month">FR</label>
						<div class="col-md-9 columns">
						<input type="text" name="fr" id="fr" class="fr form-control">
						</div> 
					</div> 
					<?php if( $this->config->item('country')==1 ){ ?>
					<div class="row form-group">
						<label class="addlabel col-md-3 columns" for="month">Upgrades</label>
						<div class="col-md-9 columns">
						<input type="text" name="upgrades" id="upgrades" class="upgrades form-control">
						</div> 
					</div> 
					
					<div class="row form-group">
						<label class="addlabel col-md-3 columns" for="month">Upgrades Income</label>
						<div class="col-md-9 columns">
						<input type="text" name="upgrades_income" id="upgrades_income" class="upgrades_income form-control">
						</div> 
					</div> 
					<?php } ?>

					<div class="row form-group">
						<label class="addlabel col-md-3 columns" for="month">Annual</label>
						<div class="col-md-9 columns">
						<input type="text" name="annual" id="annual" class="annual form-control">
						</div> 
					</div> 

					<div class="row form-group">
						<label class="addlabel col-md-3 columns" for="month">Upfronts</label>
						<div class="col-md-9 columns">
						<input type="text" name="upfronts" id="upfronts" class="upfronts form-control">
						</div> 
					</div> 	

					<div class="row form-group">
						<label class="addlabel col-md-3 columns" for="month">240v Rebook</label>
						<div class="col-md-9 columns">
						<input type="text" name="240v_rebook" id="240v_rebook_field" class="240v_rebook form-control">
						</div> 
					</div> 

					<div class="row form-group">
						<label class="addlabel col-md-3 columns" for="month">Not Completed</label>
						<div class="col-md-9 columns">
						<input type="text" name="jobs_not_comp" id="jobs_not_comp" class="jobs_not_comp form-control">
						</div> 
					</div> 

					<div class="row form-group">
						<label class="addlabel col-md-3 columns" for="month">New Sales</label>
						<div class="col-md-9 columns">
						<input type="text" name="new_sales" id="new_sales" class="new_sales form-control">
						</div> 
					</div> 

					<div class="row form-group">
						<label class="addlabel col-md-3 columns" for="month">Renewals</label>
						<div class="col-md-9 columns">
						<input type="text" name="renewals" id="renewals" class="renewals form-control">
						</div> 
					</div> 

					<div class="row form-group">
						<label class="addlabel col-md-3 columns" for="month">Lost</label>
						<div class="col-md-9 columns">
						<input type="text" name="lost" id="lost" class="lost form-control">
						</div> 
					</div>

					<div class="row form-group">
						<label class="addlabel col-md-3 columns" for="month">Budget</label>
						<div class="col-md-9 columns">
						<input type="text" name="budget" id="budget" class="budget form-control">
						</div> 
					</div> 
					
					<div class="row form-group">
						<label class="addlabel col-md-3 columns" for="month">Actual</label>
						<div class="col-md-9 columns">
						<input type="text" name="actual" id="actual" class="actual form-control">
						</div> 
					</div> 

					<div class="row form-group">
						<label class="addlabel col-md-3 columns" for="month">Prev. Year</label>
						<div class="col-md-9 columns">
						<input type="text" name="prev_year" id="prev_year" class="prev_year form-control">
						</div> 
					</div>

					<div class="row form-group">
						<label class="addlabel col-md-3 columns" for="month">Techs</label>
						<div class="col-md-9 columns">
						<input type="text" name="techs" id="techs" class="techs form-control" />
						</div> 
					</div>

					<div class="row form-group">
					<label class="addlabel col-md-3 columns" for="month">&nbsp;</label>
					<div class="col-md-9 columns">
						<input type="submit" value="Submit" class="btn">
					</div>
					</div>
			
			</form>
		</div>


	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
	Displays some important figures that we track on a monthly basis.
	</p>
	<pre>
<code>SELECT *
FROM `figures`
WHERE `country_id` = <?php echo COUNTRY ?> 
ORDER BY `year` DESC, `month` DESC</code><pre>

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
				confirmButtonClass: "btn-success",
				showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
				timer: <?php echo $this->config->item('timer') ?>
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


		 $(".fancybox_btn").fancybox({
            hideOnContentClick: false,
            hideOnOverlayClick: false
        });

		$('.btn_cancel').click(function(e){
			e.preventDefault();
			$(this).hide();
			jQuery(this).parents("tr:first").find(".txt_hid").hide();
			jQuery(this).parents("tr:first").find(".txt_lbl").show();
			jQuery(this).parents("tr:first").find(".btn_update").hide();
			jQuery(this).parents("tr:first").find(".btn_edit").show();
		})


		$('.btn_edit').click(function(e){
			e.preventDefault();

			jQuery(this).parents("tr:first").find(".txt_hid").show();
			jQuery(this).parents("tr:first").find(".txt_lbl").hide();
			
			$(this).hide();
			jQuery(this).parents("tr:first").find(".btn_update").show();
			jQuery(this).parents("tr:first").find(".btn_cancel").show();

		})


		//Delete Figure
		$('.btn_delete').click(function(e){
			e.preventDefault();

			var figures_id = jQuery(this).parents("tr:first").find(".figures_id").val();

			 swal(
                {
                    title: "",
                    text: 'Are you sure you want to delete?',
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-success",
                    confirmButtonText: "Yes, Update",
                    cancelButtonText: "No, Cancel!",
                    closeOnConfirm: false,
                    closeOnCancel: true,
                },
                function(isConfirm){

                    if(isConfirm){

                        jQuery("#load-screen").show();
						jQuery.ajax({
							type: "POST",
							url: "/reports/ajax_delete_figures",
							data: { 
							figures_id: figures_id
						}
						}).done(function( ret ){

							jQuery("#load-screen").hide();
							swal({
								title:"Success!",
								text: "Figure successfully deleted",
								type: "success",
								showCancelButton: false,
								confirmButtonText: "OK",
								closeOnConfirm: false,
								showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
								timer: <?php echo $this->config->item('timer') ?>
							});	
							
							var full_url = window.location.href;
							setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);	

						});

                    }
                    
                }
            );

		})

		
		//UPDATE FIGURE
		jQuery(".btn_update").click(function(e){
			e.preventDefault();
	
			var figures_id = jQuery(this).parents("tr:first").find(".figures_id").val();
			var month = jQuery(this).parents("tr:first").find(".month").val();
			var year = jQuery(this).parents("tr:first").find(".year").val();
			var working_days = jQuery(this).parents("tr:first").find(".working_days").val();
			
			var p_actual = jQuery(this).parents("tr:first").find(".p_actual").val();
			var p_last_month = jQuery(this).parents("tr:first").find(".p_last_month").val();
			
			var ym = jQuery(this).parents("tr:first").find(".ym").val();
			var of = jQuery(this).parents("tr:first").find(".of").val();
			var cot = jQuery(this).parents("tr:first").find(".cot").val();
			var lr = jQuery(this).parents("tr:first").find(".lr").val();
			var fr = jQuery(this).parents("tr:first").find(".fr").val();
			var upgrades = jQuery(this).parents("tr:first").find(".upgrades").val();
			var upgrades_income = jQuery(this).parents("tr:first").find(".upgrades_income").val();
			var jobs_not_comp = jQuery(this).parents("tr:first").find(".jobs_not_comp").val();
			var annual = jQuery(this).parents("tr:first").find(".annual").val();
			var upfronts = jQuery(this).parents("tr:first").find(".upfronts").val();
			var rebook_240v = jQuery(this).parents("tr:first").find(".240v_rebook").val();
			
			var new_sales = jQuery(this).parents("tr:first").find(".new_sales").val();
			var renewals = jQuery(this).parents("tr:first").find(".renewals").val();
			var lost = jQuery(this).parents("tr:first").find(".lost").val();
				
			var budget = jQuery(this).parents("tr:first").find(".budget").val();
			var actual = jQuery(this).parents("tr:first").find(".actual").val();
			
			var prev_year = jQuery(this).parents("tr:first").find(".prev_year").val();
			var techs = jQuery(this).parents("tr:first").find(".techs").val();
			
			var error = "";
			
			if(month==""){
				error += "Month cannot be empty\n";
			}
			
			if(error!=""){
				swal('',error,'error');
			}

			 swal(
                {
                    title: "",
                    text: 'Are you sure you want to update?',
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-success",
                    confirmButtonText: "Yes, Update",
                    cancelButtonText: "No, Cancel!",
					cancelButtonClass: "btn-danger",
                    closeOnConfirm: false,
                    closeOnCancel: true,
                },
				function(isConfirm){
					if(isConfirm){

						jQuery("#load-screen").show();
						jQuery.ajax({
							type: "POST",
							url: "/reports/ajax_update_figures",
							data: { 
								figures_id: figures_id,
								
								month : month,
								year: year,
								working_days: working_days,
								
								p_actual: p_actual,
								p_last_month: p_last_month,
								
								ym: ym,
								of: of,
								cot: cot,
								lr: lr,
								fr: fr,
								upgrades: upgrades,
								upgrades_income: upgrades_income,
								jobs_not_comp: jobs_not_comp,
								annual: annual,
								upfronts: upfronts,
								rebook_240v: rebook_240v,
								
								new_sales: new_sales,
								renewals: renewals,
								lost: lost,
								
								budget: budget,
								actual: actual,
								
								prev_year: prev_year,
								techs: techs
							}
						}).done(function( ret ) {

							jQuery("#load-screen").hide();
							swal({
								title:"Success!",
								text: "Figure successfully updated",
								type: "success",
								showCancelButton: false,
								confirmButtonText: "OK",
								closeOnConfirm: false,
								showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
								timer: <?php echo $this->config->item('timer') ?>
							});	
							
							var full_url = window.location.href;
							setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);	

						});		

					}
				}
				
				);
					
		});

		// opportunity validation check
		jQuery("#form_figures").submit(function(event){
			
			var month = jQuery("#form_figures #month").val();
			var year = jQuery("#form_figures #year").val();		
			var error = "";
			
			if( month=="" ){
				error += "Month must not be empty\n";
			}
			
			if( year=="" ){
				error += "Year must not be empty\n";
			}
					
			if(error!=""){
				swal('',error,'error');
				return false;
			}else{
				return true;
			}
		});

		// job section hide/show toggle script
		jQuery("#jobs_section_th").click(function(){

			var jobs_section_th_dom = jQuery(this);

			var colspan = jobs_section_th_dom.attr("colspan"); 

			if( colspan > 0 ){ // hide

				var job_header_colspan = 0;
				jobs_section_th_dom.attr("colspan",job_header_colspan); // remove colspan

				// show double right icon
				jQuery(".jobs_expand_icon").removeClass("fa-angle-double-left");
				jQuery(".jobs_expand_icon").addClass("fa-angle-double-right");

				jQuery(".jobs_tot_col").addClass('jRightSideBorder'); // add right border
				jQuery(".job_section").hide();

			}else{ // show

				var job_header_colspan = <?php echo ($this->config->item('country')==1) ? '11' : '10'; ?>;
				jobs_section_th_dom.attr("colspan",job_header_colspan); // add colspan

				// show double right left
				jQuery(".jobs_expand_icon").removeClass("fa-angle-double-right");
				jQuery(".jobs_expand_icon").addClass("fa-angle-double-left");
				
				jQuery(".jobs_tot_col").removeClass('jRightSideBorder'); // remove right border
				jQuery(".job_section").show();

			}
			

		});


		// revenue section hide/show toggle script
		jQuery("#revenue_section_th").click(function(){

			var revenue_section_th_dom = jQuery(this);

			var colspan = revenue_section_th_dom.attr("colspan"); 

			if( colspan > 0 ){ // hide

				var job_header_colspan = 0;
				revenue_section_th_dom.attr("colspan",job_header_colspan); // remove colspan

				// show double right icon
				jQuery(".revenue_expand_icon").removeClass("fa-angle-double-left");
				jQuery(".revenue_expand_icon").addClass("fa-angle-double-right");

				jQuery(".daily_avg_col").addClass('jRightSideBorder'); // add right border
				jQuery(".revenue_section").hide();

			}else{ // show

				var job_header_colspan = <?php echo ($this->config->item('country')==1) ? '5' : '4'; ?>;;
				revenue_section_th_dom.attr("colspan",job_header_colspan); // add colspan

				// show double right left
				jQuery(".revenue_expand_icon").removeClass("fa-angle-double-right");
				jQuery(".revenue_expand_icon").addClass("fa-angle-double-left");
				
				jQuery(".daily_avg_col").removeClass('jRightSideBorder'); // remove right border
				jQuery(".revenue_section").show();

			}


		});


    }) //doc ready end

</script>