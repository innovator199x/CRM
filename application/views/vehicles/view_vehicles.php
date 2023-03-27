<?php
  $export_links_params_arr = array(
	'job_type_filter' => $this->input->get_post('job_type_filter'),
	'service_filter' => $this->input->get_post('service_filter'),
	'state_filter' =>  $this->input->get_post('state_filter'),
	'date_filter' => $this->input->get_post('date_filter'),
	'search_filter' => $this->input->get_post('search_filter'),
	'page' => $this->input->get_post('page'),
	'driver' => $this->input->get_post('driver')
);
$export_link_params = '/vehicles/export_view_vehicle/?'.http_build_query($export_links_params_arr);
?>
<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
.submitbtnImg {
	background-color: #b4151b;
	font-family: Arial, sans-serif;
	color: #FFFFFF;
	font-size: 16px;
	border: #dc9b9d;
	border-radius: 5px;
	padding: 5px;
	cursor: pointer;
	box-shadow: none;
	-webkit-appearance: none;
}
.blue-btn {
    background-color: #00AEEF;
    color: #FFFFFF;
}
</style>
<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => "Reports",
			'link' => "/reports"
		),array(
			'title' => $title,
			'status' => 'active',
			'link' => "/vehicles/view_vehicles"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">
			<div class="for-groupss row">

				<div class="col-md-10 columns">

				<?php
				$form_attr = array(
					'id' => 'jform'
				);
				echo form_open('/vehicles/view_vehicles',$form_attr);
				?>
				<div class="row">

					<div class="col-mdd-3">
						<label for="agency_select">Driver</label>
						<select id="driver" name="driver" class="form-control">
							<option value="">ALL</option>
							<?php foreach($driver->result_array() as $row){ 
								$sel = ($this->input->get_post('driver')== $row['StaffID']) ? "selected" : NULL;
								if($row['StaffID']!=""){?>
								<option <?php echo $sel; ?> value="<?php echo $row['StaffID'] ?>"><?php echo "{$row['FirstName']} {$row['LastName']}"; ?></option>
							<?php }} ?>
						</select>
					</div>
					<div class="col-md-1 columns">
						<label class="col-sm-12 form-control-label">&nbsp;</label>
						<button type="submit" class="btn btn-inline">Search</button>
					</div>
					<span style="margin-top:25px;">* All spare/sold/disposed vehicles to be assigned to ‘other supplier’</span>
				</div>
				</form>

				</div>

				<div class="col-md-2 columns">
				
					<a class="btn btn-danger float-right" role="button" href="<?php echo $export_link_params ?>" >Export</a>

					<a style="margin-right:10px;" class="btn btn-danger float-right" href="<?=isset($_GET['page']) ? '/vehicles/view_vehicles' : '/vehicles/view_vehicles?page=all'?>" role="button"><?=isset($_GET['page']) ? 'View Active' : 'View All'?></a>
				</div>

			</div>
		</div>
	</header>

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<table id="vehicle-table" class="table table-hover main-table">
					<thead>
						<tr>
							<th>Plant ID</th>
							<th>Make</th>
							<th>Model</th>
							<th>Transmission</th>
							<th>Key #</th>
							<th>Vin #</th>
							<th>Number Plate</th>
                            <th>Driver</th>
                            <th>Kms</th>
                            <th>Kms Update</th>
                            <th>Next Service</th>
                            <th>Rego Expires</th>
                            <?php if($this->config->item('country')==2){
								echo "<th>WOF</th>";
							} ?>
                            <th>Tech Vehicle</th>
							<th>Ownership</th>
                            <th>Active</th>
                            <th>Edit</th>
						</tr>
					</thead>

					<tbody>
						<?php 
							foreach($lists->result_array() as $list_item) {		
						?>
						<tr>
							<td>
								<span class="txt_lbl"><?php echo  $list_item['v_plantID']; ?></span>
								<input type="text" name="plant_id" class="txt_hid plant_id form-control" value="<?php echo $list_item['v_plantID']; ?>" />
							</td>
							<td>
								<span class="txt_lbl"><?php echo $list_item['v_make']?></span>
								<input type="text" name="make" class="txt_hid make form-control" value="<?php echo $list_item['v_make']; ?>" />
								<input type="hidden" name="vehicles_id" class="vehicles_id form-control" value="<?php echo $list_item['v_id']; ?>" />
							</td>
							<td>
								<span class="txt_lbl"><?php echo $list_item['v_model']; ?></span>
								<input type="text" name="model" class="txt_hid model form-control" value="<?php echo $list_item['v_model']; ?>" />
							</td>
							<td>
								<span class="txt_lbl"><?php echo $list_item['transmission']; ?></span>
								<select class="form-control txt_hid transmission" id="transmission" name="transmission" >
									<option <?php echo ($list_item['transmission']=="") ? 'selected' : NULL; ?> value="">Please Select Transmission</option>
									<option <?php echo ($list_item['transmission']=="Auto") ? 'selected' : NULL; ?> value="Auto">Auto</option>
									<option <?php echo ($list_item['transmission']=="Manual") ? 'selected' : NULL; ?> value="Manual">Manual</option>
								</select>
							</td>
							<td>
								<span class="txt_lbl"><?php echo $list_item['v_keynumber']; ?></span>
								<input type="text" name="key_number" class="txt_hid key_number form-control" value="<?php echo $list_item['v_keynumber']; ?>" />
							</td>
							<td>
								<span class="txt_lbl"><?php  echo $list_item['v_vinnum'];?></span>
								<input type="text" name="vin_num" class="txt_hid vin_num form-control" value="<?php echo $list_item['v_vinnum']; ?>" />
							</td>
							<td>
								<span class="txt_lbl">
									<a href="/vehicles/view_vehicle_details/<?php echo $list_item['v_id']; ?>"><?php echo $list_item['v_numberplate']; ?></a>
								</span>
								<input type="text" name="make number_plate" class="txt_hid number_plate form-control" value="<?php echo $list_item['v_numberplate']; ?>" />
							</td>
                            <td>
								<span class="txt_lbl"><?php echo $list_item['sa_firstname']." ".$list_item['sa_lastname']; ?></span>

								<select name="staff_id" class="txt_hid staff_id form-control">
									<option value="">----</option>
									<?php
										$tech = $this->system_model->getDriver();
										foreach($tech->result_array() as $row){
									?>
									<option value="<?php echo $row['StaffID']; ?>" <?php echo ($list_item['StaffID']==$row['StaffID'])?'selected="selected"':''; ?>><?php echo $row['FirstName'].' '.$row['LastName']; ?></option>
									<?php
										}
									?>
								</select>
								<input type="hidden" value="<?php echo $list_item['StaffID']; ?>" id="hid_og_driver">
							</td>	

							<?php 
							
							$params = array('v_id' => $list_item['v_id']);
							$datas = $this->vehicles_model->get_kms($params); 
							if (count($datas) <= 0) { ?>
	                            <td>
									<span class="txt_lbl"></span>
									<input type="text" name="kms" class="txt_hid kms form-control" value="" />
								</td>
	                            <td>
									<span class="txt_lbl"></span>
									<span class="txt_hid "></span>
								</td>
							<?php
							}
							foreach($datas as $data1) {
							?>
	                            <td>
									<span class="txt_lbl"><?php  echo $data1['kms']; ?></span>
									<input type="text" name="kms" class="txt_hid kms form-control" value="<?php echo $data1['kms']; ?>" />
								</td>
	                            <td>
									<span class="txt_lbl"><?php echo date("d/m/Y",strtotime($data1['kms_updated'])); ?></span>
									<span class="txt_hid "><?php echo  date("d/m/Y",strtotime($data1['kms_updated'])); ?></span>
								</td>
							<?php 
							}
							?>

                            <td>
								<span class="txt_lbl"><?php  echo $list_item['v_nextservice']; ?></span>
								<input type="text" name="next_service" class="txt_hid next_service form-control" value="<?php echo $list_item['v_nextservice']; ?>" />
							</td>

                            <td>
								<span class="txt_lbl"><?php echo date("d/m/Y",strtotime($list_item['v_regoexpires'])); ?></span>
								<span class="txt_hid ">
								<!-- <input type="text" name="rego_expires" class="txt_hid datepicker rego_expires" value="<?php echo  date("d/m/Y",strtotime($list_item['rego_expires'])); ?>" /> -->
								<input name="rego_expires" placeholder="ALL" class="flatpickr form-control flatpickr-input txt_hid rego_expires" type="text"  value="<?php echo  date("d/m/Y",strtotime($list_item['v_regoexpires'])); ?>">
								</span>
							</td>

							<?php 
								if($this->config->item('country')==2){ //Display WOF if NZ
							?>
							<td>
							<?php 
							$wofDate =  ( $this->system_model->isDateNotEmpty($list_item['WOF']) )?date('d/m/Y', strtotime($list_item['WOF'])):NULL;
							echo $wofDate;
							?>
							<input name="wof" class="flatpickr form-control flatpickr-input txt_hid wof" type="text"  value="<?php echo $wofDate; ?>">
							</td>
							<?php } ?>

                            <td>
								<span class="txt_lbl"><?php  echo ($list_item['v_techvehicle']==1)?'Yes':'No'; ?></span>
								<select class="txt_hid tech_vehicle form-control" name="tech_vehicle" id="tech_vehicle">
									<option value="">--Select--</option>
									<option value="1" <?php echo ($list_item['tech_vehicle']==1)?'selected="selected"':''; ?>>Yes</option>
									<option value="0" <?php echo ($list_item['tech_vehicle']==0)?'selected="selected"':''; ?>>No</option>
								</select>
							</td>
							<td>
								<span class="txt_lbl">
									<?php
										if( $list_item['vehicle_ownership'] == 1 ){
											$vehicle_ownership = 'Company';
										}else if( $list_item['vehicle_ownership'] == 2 ){
											$vehicle_ownership = 'Personal';
										}else{
											$vehicle_ownership = null;
										}  
										echo $vehicle_ownership; 
									?>
								</span>
								<select class="txt_hid vehicle_ownership form-control" name="vehicle_ownership" id="vehicle_ownership">
									<option value="">--Select--</option>
									<option value="1" <?php echo ($list_item['vehicle_ownership']==1)?'selected="selected"':''; ?>>Company</option>
									<option value="2" <?php echo ($list_item['vehicle_ownership']==2)?'selected="selected"':''; ?>>Personal</option>
								</select>
							</td>
                            <td>
								<span class="txt_lbl"><?php  echo ($list_item['v_active']==1)?'Active':'Inactive';?></span>
								<select class="txt_hid active form-control" name="active" id="active" style="width: auto !important;">
									<option value="">--Select--</option>
									<option value="1" <?php echo ($list_item['v_active']==1)?'selected="selected"':''; ?>>Active</option>
									<option value="0" <?php echo ($list_item['v_active']==0)?'selected="selected"':''; ?>>Inactive</option>
								</select>
							</td>
                            <td>
								<button style="margin-bottom:2px;" class="blue-btn btn btn_update">Update</button>
								<a href="javascript:void(0);" class="btn_del_vf btn_edit">Edit</a>
								<button class="btn-danger btn btn_cancel" style="display:none;">Cancel</button>
							</td>
						</tr>
						<?php } ?>
					</tbody>

				</table>
			</div>

			<nav class="text-center">
				<?php echo $pagination; ?>
			</nav>

			<div class="pagi_count text-center">
				<?php echo $pagi_count; ?>
			</div>
		
			<div class="row">
				<div class="col-md-3">
                <label class="col-sm-12 form-control-label">&nbsp;</label>
				<a class="btn btn-danger" href="/vehicles/add_vehicle" role="button">Add Vehicle</a>
				</div>
			</div>

		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;">

	<h4>View Vehicles</h4>
	<p>This page lists all vehicles in the fleet. Active and Inactive.</p>
	<pre>
<code>SELECT v.`plant_id` AS v_plantID, v.`vehicles_id` AS v_id, v.`make` AS v_make, v.`model` AS v_model, v.`key_number` AS v_keynumber, v.`vin_num` AS v_vinnum, v.`number_plate` AS v_numberplate, v.`rego_expires` AS v_regoexpires, v.`rego_expires` AS v_regoexpires, v.`tech_vehicle` AS v_techvehicle, v.`active` AS v_active, v.`next_service` AS v_nextservice, v.WOF, v.vehicle_ownership, sa.`FirstName` AS sa_firstname, sa.`LastName` AS sa_lastname, sa.`StaffID`
FROM `vehicles` AS `v`
LEFT JOIN `staff_accounts` AS `sa` ON sa.`StaffID` = v.`StaffID`
WHERE `v`.`country_id` =  <?php echo COUNTRY ?> 
AND `v`.`active` = 1
ORDER BY `v`.`plant_id` ASC</code>
</pre>

</div>
<!-- Fancybox END -->
<script>
jQuery(document).ready(function(){

	$('#vehicle-table').DataTable({
        "paging":   false,
        "info":     false,
		"searching": false,
		"columnDefs": [
			{ "type": "html-num-fmt", "orderable": true, "targets": 0 },
			{ "orderable": false, "targets": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15]}
		]
    });

	jQuery(".btn_edit").click(function(){
	
		jQuery(this).parents("tr:first").find(".btn_update").show();
		jQuery(this).parents("tr:first").find(".btn_edit").hide();
		jQuery(this).parents("tr:first").find(".btn_cancel").show();
		jQuery(this).parents("tr:first").find(".btn_delete").show();
		jQuery(this).parents("tr:first").find(".txt_hid").show();
		jQuery(this).parents("tr:first").find(".txt_lbl").hide();
	
	});	
	
	jQuery(".btn_cancel").click(function(){
		
		jQuery(this).parents("tr:first").find(".btn_update").hide();
		jQuery(this).parents("tr:first").find(".btn_edit").show();
		jQuery(this).parents("tr:first").find(".btn_cancel").hide();
		jQuery(this).parents("tr:first").find(".btn_delete").hide();
		jQuery(this).parents("tr:first").find(".txt_lbl").show();
		jQuery(this).parents("tr:first").find(".txt_hid").hide();	
		
	});
	
	jQuery(".btn_update").click(function(){
	
		var vehicles_id = jQuery(this).parents("tr:first").find(".vehicles_id").val();
		var make = jQuery(this).parents("tr:first").find(".make").val();
		var model = jQuery(this).parents("tr:first").find(".model").val();
		var plant_id = jQuery(this).parents("tr:first").find(".plant_id").val();
		var key_number = jQuery(this).parents("tr:first").find(".key_number").val();
		var vin_num = jQuery(this).parents("tr:first").find(".vin_num").val();
		var number_plate = jQuery(this).parents("tr:first").find(".number_plate").val();
		var staff_id = jQuery(this).parents("tr:first").find(".staff_id").val();
		var og_staff_id = jQuery(this).parents("tr:first").find("#hid_og_driver").val();
		var tech_vehicle = jQuery(this).parents("tr:first").find(".tech_vehicle").val();		
		var kms = jQuery(this).parents("tr:first").find(".kms").val();
		var next_service = jQuery(this).parents("tr:first").find(".next_service").val();
		var active = jQuery(this).parents("tr:first").find(".active").val();	
		var rego_expires = jQuery(this).parents("tr:first").find(".rego_expires").val();
		var wof = jQuery(this).parents("tr:first").find(".wof").val();
		var vehicle_ownership = jQuery(this).parents("tr:first").find(".vehicle_ownership").val();
		var transmission = jQuery(this).parents("tr:first").find(".transmission").val();
		var error = "";
		
		if(number_plate==""){
			error += "Number Plate is required";
		}

		if(vehicle_ownership==""){
			error += "Ownership is required";
		}
		
		if(error!=""){
			swal('',error,'error');
		}else{
				
			jQuery.ajax({
				type: "POST",
				url: "/vehicles/ajax_update_vehicles",
				data: { 
					vehicles_id: vehicles_id,
					make: make,
					model: model,
					plant_id: plant_id,
					key_number: key_number,
					vin_num: vin_num,
					number_plate: number_plate,
					staff_id: staff_id,
					og_staff_id: og_staff_id,
					kms: kms,
					next_service: next_service,
					tech_vehicle: tech_vehicle,
					active: active,
					rego_expires: rego_expires,
					wof: wof,
					vehicle_ownership: vehicle_ownership,
					transmission: transmission
				}
			}).done(function( ret ) {
				console.log(ret)
				if (ret === "1") {
					swal({
						title:"Success!",
						text: "Vehicle update successfully",
						type: "success",
						showCancelButton: false,
						confirmButtonText: "OK",
						closeOnConfirm: false,

					},function(isConfirm){
					if(isConfirm){ 
						location.reload();
						}
					});
				}else {
					swal('','No data has been changed','error');
					return false;
				}
			});				
			
		}		
		
	});
	
	jQuery(".btn_delete").click(function(){
	
		var vehicles_id = jQuery(this).parents("tr:first").find(".vehicles_id").val();
	
		if(confirm("Are you sure you want to delete")){
			jQuery.ajax({
				type: "POST",
				url: "ajax_delete_vehicle.php",
				data: { 
					vehicles_id: vehicles_id,
				}
			}).done(function( ret ){
				window.location = "/view_vehicles.php";
			});	
		}
	});

	$('.staff_id').on('change', function() {
		var obj = $(this);
		var thisval = $(this).val();
		$('#load-screen').show();
		jQuery.ajax({
			type: "POST",
			url: "/vehicles/ajax_duplicate_vehicle_user",
			dataType: "json",
			data: { 
				staffid: thisval,
			}
		}).done(function( ret ){
			$('#load-screen').hide();
			if(ret.status==true){
				swal('','Technician has already been assigned to a vehicle','error');
				obj.find('option:first').prop('selected', 'selected');
				return false;
			}
		});	

	});
	
	

});
</script>