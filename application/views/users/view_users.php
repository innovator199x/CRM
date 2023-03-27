
<style>
	.main-table {
		border-left: 1px solid #dee2e6;
		border-right: 1px solid #dee2e6;
		border-bottom: 1px solid #dee2e6;
		margin-bottom: 20px;
	}

	.col-mdd-3 {
		-webkit-box-flex: 0;
		-ms-flex: 0 0 15.2%;
		flex: 0 0 15.2%;
		max-width: 15.2%;

		position: relative;
		width: 100%;
		min-height: 1px;
		padding-right: 15px;
		padding-left: 15px;
	}
</style>

<div class="box-typical box-typical-padding">

	<?php
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => "/users"
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);

$export_links_params_arr_tech = array(
	'state_filter_tech' => $this->input->get_post('state_filter_tech'),
	'search_filter_tech' => $this->input->get_post('search_filter_tech')
);
$export_link_params_tech = '/users/index/?export=1&tab=1&'.http_build_query($export_links_params_arr_tech);

$export_links_params_arr_admin = array(
	'class_fil' => $this->input->get_post('class_fil'),
	'state_filter_admin' => $this->input->get_post('state_filter_admin'),
	'search_filter' => $this->input->get_post('search_filter')
);
$export_link_params_admin = '/users/index/?export=1&tab=2&'.http_build_query($export_links_params_arr_admin);

?>


	<?php if ($this->input->get('link_success') == 1): ?>
		<div class="alert alert-success" role="alert">
		CI Link Successful
		</div>
	<?php endif; ?>

	<section class="tabs-section">
				<div class="tabs-section-nav tabs-section-nav-icons">
					<div class="tbl">
						<ul class="nav" role="tablist">
							<li class="nav-item">
								<a class="nav-link active show" href="#tab-1" role="tab" data-toggle="tab" >
									<span class="nav-link-in">
										<i class="fa fa-user-secret"></i>
										Techs
									</span>
								</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="#tab-2" role="tab" data-toggle="tab" >
									<span class="nav-link-in">
										<span class="fa fa-user"></span>
										Admin
									</span>
								</a>
							</li>


						</ul>
					</div>
				</div><!--.tabs-section-nav-->

				<div class="tab-content">
					<!-- TECH TAB -->
					<div role="tabpanel" class="tab-pane fade active show" id="tab-1">

						<div class="box-typical box-typical-padding">

							<!-- TECH FILTER -->
							<?php
							$form_attr = array(
								'id' => 'jform'
							);
							echo form_open("/users?show_all={$this->input->get_post('show_all')}",$form_attr);
							?>
								<div class="for-groupss row">
									<div class="col-md-8 columns">
										<div class="row">

											<div class="col-md-3">
												<label for="search">State</label>
												<select name="state_filter_tech" t class="form-control field_g2">
													<option value="">All</option>
													<?php
														foreach($state_filter as $state_row){
															$sel = ($this->input->get_post('state_filter_tech')==$state_row['state']) ? 'selected="true"' :NULL;
													?>
															<option <?php echo $sel; ?> value="<?php echo $state_row['state'] ?>"><?php echo  $state_row['state']; ?></option>
													<?php
														}
													?>
												</select>
											</div>

											<div class="col-md-3">
												<label for="search">Search</label>
												<input type="text" name="search_filter_tech" class="form-control" placeholder="ALL" value="<?php echo $this->input->get_post('search_filter_tech'); ?>" />
											</div>

											<div class="col-md-2 columns">
												<label class="col-sm-12 form-control-label">&nbsp;</label>
												<button type="submit" class="btn btn-inline">Search</button>
											</div>

										</div>
									</div>
									<div class="col-lg-4 col-md-12 columns">
										<section class="proj-page-section float-right">
											<div class="proj-page-attach">
												<i class="fa fa-file-excel-o"></i>
												<p class="name"><?php echo $title; ?></p>
												<p>
													<a href="<?php echo $export_link_params_tech ?>" target="blank">
														Export
													</a>
												</p>
											</div>
										</section>
									</div>
								</div>
								</form>
								<!-- TECH FILTER END -->

							</div>

						<table class="table table-hover main-table table-striped">
							<thead>
								<tr>
									<th>Name</th>
									<th>Position</th>
									<th>Working Hours</th>
									<th>Schedule</th>
									<th>Phone</th>
									<th>Email</th>
									<th>Vehicle</th>
									<th>Ipad Service</th>
									<th>Class</th>
									<th>ID</th>
									<th>Status</th>
								</tr>
							</thead>

							<tbody>
								<?php foreach($lists_tech->result_array() as $list_item):
								?>
								<tr>
									<td>
										<?php if( in_array($logged_user_class, $allowed_user_class_to_edit_arr) || in_array($logged_user_id, $allowed_user_to_edit_arr) ): ?>
											<a href='<?php echo "/users/view/{$list_item['sa_staffid']}" ?>' data-toggle="tooltip" title="" class="btn_edit" data-original-title="Edit" style="color: #0082c6;">
												<?php echo  $list_item['sa_firstname']." ".$list_item['sa_lastname']; ?>
											</a>
										<?php else: ?>
											<?php echo  $list_item['sa_firstname']." ".$list_item['sa_lastname']; ?>
										<?php endif; ?>
									</td>
									<td>
										<?php echo $list_item['sa_position']?>
									</td>
									<td>
										<?php
										$this->db->select('working_hours');
										$this->db->from('tech_working_hours');
										$this->db->where('staff_id', $list_item["sa_staffid"]);
										$query = $this->db->get();
										$result = $query->row();
										echo $result->working_hours;
										 ?>
									</td>
									<td>
									<?php
									$crm_ci_page = "/calendar/monthly_schedule_admin/{$list_item['sa_staffid']}";
									?>
										<a href="<?php echo $crm_ci_page ?>">
											View Schedule
										</a>
									</td>
									<td>
										<?php echo $list_item['contactNum']; ?>
									</td>
									<td>
										<?php echo $list_item['sa_email']; ?>
									</td>
									<td>
										<?php
										$vehicle_params = array(
											'sel_query' => "v.number_plate, v.vehicles_id",
											'staff_id' => $list_item['sa_staffid']
										);
										$vehicle = $this->vehicles_model->get_vehicles($vehicle_params);
										$v_sql = $vehicle->row_array();

										echo $this->gherxlib->crmLink('vehicle',$v_sql['vehicles_id'], $v_sql['number_plate']);
										?>
									</td>
									<td>
										<?php echo $list_item['sa_ipad_prepaid_serv_num']; ?>
									</td>
									<td>
										<?php echo $list_item['sc_classname']; ?>
									</td>
									<td>
										<?php echo $list_item['sa_staffid']; ?>
									</td>

									<td>
										<?php echo ($list_item['sa_active']==1)?'<span class="text-green">Active</span>':'<span class="text-red">Inactive</span>'; ?>
									</td>
								</tr>
								<?php endforeach ?>
							</tbody>

						</table>

					</div><!--.tab-pane-->
					<!-- TECH TAB END -->

					<!-- ADMIN TAB -->
					<div role="tabpanel" class="tab-pane fade" id="tab-2">

					<div class="box-typical box-typical-padding">

						<!-- ADMIN FILTER -->
						<?php
						$form_attr = array(
							'id' => 'jform'
						);
						echo form_open("/users?show_all={$this->input->get_post('show_all')}",$form_attr);
						?>
							<div class="for-groupss row">
								<div class="col-md-8 columns">
									<div class="row">
										<div class="col-md-3">
											<label for="search">State</label>
											<select name="state_filter_admin" t class="form-control field_g2">
												<option value="">All</option>
												<?php
													foreach($state_filter as $state_row){
														$sel = ($this->input->get_post('state_filter_admin')==$state_row['state']) ? 'selected="true"' :NULL;
												?>
														<option <?php echo $sel; ?> value="<?php echo $state_row['state'] ?>"><?php echo  $state_row['state']; ?></option>
												<?php
													}
												?>
											</select>
										</div>
										<div class="col-md-3">
											<label for="class_select">Access</label>
											<select name="class_fil" t class="form-control field_g2 select2-photo">
												<option value="">All</option>
												<?php
												 foreach($class_filter->result_array() as $list){
												if(!empty($list['sc_classID'])){
												?>
												<option value="<?php echo $list['sc_classID']; ?>" <?php echo ( $list['sc_classID']==$this->input->get_post('class_fil')
													)?'selected="selected"':''; ?>>
													<?php echo $list['sc_classname']; ?>
												</option>
												<?php }} ?>
											</select>
										</div>

										<div class="col-md-3">
											<label for="search">Search</label>
											<input type="text" name="search_filter" class="form-control" placeholder="ALL" value="<?php echo $this->input->get_post('search_filter'); ?>" />
										</div>

										<div class="col-md-2 columns">
											<label class="col-sm-12 form-control-label">&nbsp;</label>
											<button type="submit" class="btn btn-inline">Search</button>
										</div>
									</div>
								</div>
								<div class="col-lg-4 col-md-12 columns">
									<section class="proj-page-section float-right">
										<div class="proj-page-attach">
											<i class="fa fa-file-excel-o"></i>
											<p class="name"><?php echo $title; ?></p>
											<p>
												<a href="<?php echo $export_link_params_admin ?>" target="blank">
													Export
												</a>
											</p>
										</div>
									</section>
								</div>
							</div>
							</form>
							<!-- ADMIN FILTER END -->

						</div>

						<table class="table table-hover main-table table-striped">
							<thead>
								<tr>
									<th>Name</th>
									<th>Position</th>
									<th>Phone</th>
									<th>Email</th>
									<th>Class</th>
									<th>ID</th>
									<th>Status</th>
									<th>Technicians</th>
								</tr>
							</thead>

							<tbody>
								<?php foreach($lists_admin->result_array() as $list_item):
								?>
								<tr>
									<td>
										<?php if( in_array($logged_user_class, $allowed_user_class_to_edit_arr) || in_array($logged_user_id, $allowed_user_to_edit_arr) ): ?>
											<a href='<?php echo "/users/view/{$list_item['sa_staffid']}" ?>' data-toggle="tooltip" title="" class="btn_edit" data-original-title="Edit" style="color: #0082c6;">
												<?php echo  $list_item['sa_firstname']." ".$list_item['sa_lastname']; ?>
											</a>
										<?php else: ?>
											<?php echo  $list_item['sa_firstname']." ".$list_item['sa_lastname']; ?>
										<?php endif; ?>
									</td>
									<td>
										<?php echo $list_item['sa_position']?>
									</td>
									<td>
										<?php echo $list_item['contactNum']; ?>
									</td>
									<td>
										<?php echo $list_item['sa_email']; ?>
									</td>
									<td>
										<?php echo $list_item['sc_classname']; ?>
									</td>
									<td>
										<?php echo $list_item['sa_staffid']; ?>
									</td>

									<td>
										<?php echo ($list_item['sa_active']==1)?'<span class="text-green">Active</span>':'<span class="text-red">Inactive</span>'; ?>
									</td>
									<td>

										<?php
											if( $list_item['sc_classID'] == 7 || $list_item['sc_classID'] == 8 ){

												$tech_params = array(
													'sel_query' => "sa.StaffID, sa.FirstName, sa.LastName",
													'sa_deleted' => 0,
													'sa_active' => 1,
													'class_filter'=>6,
													'assigned_cc' => $list_item['sa_staffid'],
													'sort_list' => array(
														array(
															'order_by' => 'sa.FirstName',
															'sort' => 'ASC',
														),
													),
												);
												$ass_tech_sql = $this->users_model->get_users($tech_params);
										?>

												<ol class="assigned_tech_ol">
												<?php
												foreach( $ass_tech_sql->result_array() as $ass_tech_row ){ ?>
													<li>
														<?php echo $this->system_model->formatStaffName($ass_tech_row['FirstName'],$ass_tech_row['LastName']);
														 ?>
													</li>
												<?php
												}
												?>
											<ol>

										<?php
											}
										?>


									</td>
								</tr>
								<?php endforeach ?>
							</tbody>

						</table>

					</div><!--.tab-pane-->
					<!-- ADMIN TAB EDN -->
				</div><!--.tab-content-->
				<div  style="margin-top:15px;">
					<?php
						$display_user_btn = ( $this->input->get_post('show_all')==1 ) ? 'Show Active Users Only' : 'Display All Users';
						$display_user_url = ( $this->input->get_post('show_all')==1 ) ? '/users' : '/users?show_all=1';
					?>
					<a class="btn" href="<?php echo $display_user_url; ?>"><?php echo $display_user_btn; ?></a>&nbsp;<a class="btn btn-danger" href="/users/add">Add User</a>
				</div>
			</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;" >
	<h4><?php echo $title; ?></h4>
	<p>
	Displays all SATS users
	</p>
</div>
<!-- Fancybox END -->

<script>

	$(document).ready(function() {

		//load selected tab on page load/refresh
		var sel_tab = localStorage.curr_tab;
		if(sel_tab!=""){
			$("a[href='" + sel_tab + "']").tab("show");
		}

		$(document.body).on("click", "a[data-toggle='tab']", function(event) {
			selected_tab = $(this).attr("href");
			localStorage.setItem("curr_tab", selected_tab);
		});

	});

</script>