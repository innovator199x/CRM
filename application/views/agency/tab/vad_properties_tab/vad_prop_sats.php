<div role="tabpanel" class="tab-pane fade <?php echo ($this->input->get('prop_type')=='1' || !$this->input->get('prop_type'))?'active show':'' ?>" id="vad_prop_tab_1">
						
						<div class="table-responsive">
							<table class="table table-hover main-table">
								<thead>
									<tr>
										<th>Address</th>
										<th>Status</th>
										<th>Property Manager</th>
										<th>Service</th>
										<th>
											<?php if(!empty($property)){ ?>
											<div class="checkbox" style="margin:0;">
												<input name="chk_all" type="checkbox" id="check-all">
												<label for="check-all">&nbsp;</label>
											</div>
											<?php } ?>
										</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach($property as $p_row){ ?>
										<tr>
											<td>
												<?php echo $this->gherxlib->crmLink('vpd',$p_row["property_id"],"{$p_row['address_1']} {$p_row['address_2']}, {$p_row['address_3']}, {$p_row['state']}") ?>
											</td>
											<td style='text-align: left;!important'>
											<?php
											if($p_row['is_nlm']==1){
												echo "<span class='text-red'>No Longer Managed</span>";
											}else{
												echo "<span class='text-green'>Active</span>";
											}
											?>
										</td>		
											
											
											<td>
												<?php echo "{$p_row['pm_fname']} {$p_row['pm_lname']}"; ?>
												<input type="hidden" name="pm" value="<?php echo $p_row['pm_id_new'] ?>">
											</td>

											<td>			
											<?php
												if(!empty($p_row['prop_service'])){
													foreach($p_row['prop_service'] as $tt){
														$job_icon_params = array(
															'service_type' => $tt['alarm_job_type_id'],
															'sevice_type_name' => $tt['type']
														);
														echo $this->system_model->display_job_icons($job_icon_params)."&nbsp;&nbsp;&nbsp;";		
													}
												}else{
													echo "No Service";
												}
											?>
											</td>
											
											<td>
												<div class="checkbox">
													<input class="prop_chk" name="prop_chk[]" type="checkbox" id="check-<?php echo $p_row["property_id"] ?>" value="<?php echo $p_row['property_id'] ?>" >
													<label for="check-<?php echo $p_row["property_id"] ?>">&nbsp;</label>
												</div>
											</td>
										</tr>
									<?php
									} ?>
								</tbody>
							</table>

							<div id="mbm_box">
								<div class="text-right">
									<p><strong>Are you assigning a <a href="#" data-val="change_pm" class="btn_assign">Property Manager</a> or <a href="#" data-val="change_agency" class="btn_assign">Changing Agency</a>?</strong></p>
								</div>
								<div class="gbox_main gbox_main_change_agency" style="display:none;">
									<div class="gbox">
										<select class="form-control" id="sel_agency">
											<option value="">Please select</option>
											<?php foreach($agency_list as $agency_row){ ?>
											<option value="<?php echo $agency_row['agency_id'] ?>"><?php echo $agency_row['agency_name'] ?></option>
											<?php } ?>
										</select>
										<div class="pm_box_v2" style="display:none;margin-top:5px;">
											<select class="form-control" id="pm_v2">
											</select>
										</div>
									</div>
									<div class="gbox">
										<button id="btn_change_agency" type="button" class="btn">Change Agency</button>
									</div>
								</div>

								<div class="gbox_main gbox_main_change_pm"  style="display:none;">
									<div class="gbox">
										<select class="form-control" id="sel_pm">
											<option value="">Please select</option>
											<?php foreach($all_pm as $pm_row){ ?>
											<option value="<?php echo $pm_row['agency_user_account_id'] ?>"><?php echo "{$pm_row['fname']} {$pm_row['lname']}" ?></option>
											<?php } ?>
										</select>
									</div>
									<div class="gbox">
										<button id="btn_assign_pm" type="button" class="btn">Assign Property Manager</button>
									</div>
								</div>
							</div>
						</div>

						<nav aria-label="Page navigation example" style="text-align:center">
							<?php echo $pagination; ?>
						</nav>

						<div class="pagi_count text-center">
							<?php echo $pagi_count; ?>
						</div>
					</div>


<script type="text/javascript">

	jQuery(document).ready(function(){

		
	})

</script>