<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
        array(
			'title' => 'View Vehicles',
			'link' => "/vehicles/view_vehicles"
		),
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/vehicles/add_vehicle"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

      

	<section>
		<div class="body-typical-body" style="padding-top:25px;">

			<?php 

				if ($this->input->get_post('success') === "true") {
					echo "<div class='text-center' style='margin-top: -25px;'><strong style='color:#fa424a;'>Successfully added vehicle!</strong></dev>";
				}else if ($this->input->get('success') === "false") {
					echo "<div class='text-center' style='margin-top: -25px;'><strong style='color:#fa424a;'>Something went wrong please contact devs!</strong></dev>";
				}

			?>

			<?php
	            $form_attr = array(
	                'id' => 'jform'
	            );
            	echo form_open('/vehicles/add_vehicle_script',$form_attr);
			?>
		        <div class="row">
		        	<div class="col-md-12 col-lg-5 columns">

				         <div class="form-group row">
				            <label class="col-sm-3 form-control-label">Make</label>
				            <div class="col-sm-9">
				                <p class="form-control-static"><input type="text" class="form-control" id="make" name="make" ></p>
				            </div>
						</div>

				         <div class="form-group row">
				            <label class="col-sm-3 form-control-label">Model</label>
				            <div class="col-sm-9">
				                <p class="form-control-static"><input type="text" class="form-control" id="model" name="model" ></p>
				            </div>
						</div>


						<div class="form-group row">
				            <label class="col-sm-3 form-control-label">Transmission</label>
				            <div class="col-sm-9">
				                <p class="form-control-static">
									<select class="form-control" id="transmission" name="transmission" >
										<option value="">Please Select Transmission</option>
										<option value="Auto">Auto</option>
										<option value="Manual">Manual</option>
									</select>
								</p>
				            </div>
						</div>

				        <div class="form-group row">
				            <label class="col-sm-3 form-control-label">Year</label>
				            <div class="col-sm-9">
				                <p class="form-control-static">
				                    <select name="year" id="year" class="form-control">
										<option value="">----</option>
										<?php
										$year =  range (2035,2005);
										foreach($year as $val){ ?>
											<option value="<?php echo $val; ?>"><?php echo $val; ?></option>
										<?php
										}
										?>
				                    </select>
				                </p>
				            </div>
						</div>

				         <div class="form-group row">
				            <label class="col-sm-3 form-control-label">Number Plate</label>
				            <div class="col-sm-9">
				                <p class="form-control-static"><input type="text" class="form-control" id="number_plate" name="number_plate" ></p>
				            </div>
						</div>

				         <div class="form-group row">
				            <label class="col-sm-3 form-control-label">Rego Expires<span style="color:red">*</span></label>
				            <div class="col-sm-9">
				                <p class="form-control-static"><input type="text" class="flatpickr form-control flatpickr-input" id="rego_expires" name="rego_expires" ></p>
				            </div>
						</div>
						
						<?php 
							if($this->config->item('country')!=1){
						?>
							<div class="form-group row">
								<label class="col-sm-3 form-control-label">WOF<span style="color:red">*</span></label>
								<div class="col-sm-9">
									<p class="form-control-static"><input type="text" class="flatpickr form-control flatpickr-input" id="wof" name="wof" ></p>
								</div>
							</div>
						<?php
							}
						?>
						

				         <div class="form-group row">
				            <label class="col-sm-3 form-control-label">Key Number</label>
				            <div class="col-sm-9">
				                <p class="form-control-static"><input type="text" class="form-control" id="key_number" name="key_number" ></p>
				            </div>
						</div>

				         <div class="form-group row">
				            <label class="col-sm-3 form-control-label">Warranty Expires</label>
				            <div class="col-sm-9">
				                <p class="form-control-static"><input type="text" class="flatpickr form-control flatpickr-input" id="warranty_expires" name="warranty_expires" ></p>
				            </div>
						</div>

				         <div class="form-group row">
				            <label class="col-sm-3 form-control-label">Fuel Type</label>
				            <div class="col-sm-9">
				                <p class="form-control-static">
				                    <select name="fuel_type" id="fuel_type" class="form-control">
										<option value="">----</option>
										<option value="Unleaded">Unleaded</option>	
										<option value="Premium">Premium</option>
										<option value="Diesel">Diesel</option>
										<option value="LPG">LPG</option>
				                    </select>
				                </p>
				            </div>
						</div>

				         <div class="form-group row">
				            <label class="col-sm-3 form-control-label">eTag Number</label>
				            <div class="col-sm-9">
				                <p class="form-control-static"><input type="text" class="form-control" id="etag_num" name="etag_num" ></p>
				            </div>
						</div>

				         <div class="form-group row">
				            <label class="col-sm-3 form-control-label">Serviced By</label>
				            <div class="col-sm-9">
				                <p class="form-control-static"><input type="text" class="form-control" id="serviced_by" name="serviced_by" ></p>
				            </div>
						</div>

				         <div class="form-group row">
				            <label class="col-sm-3 form-control-label">Fuel Card Number</label>
				            <div class="col-sm-9">
				                <p class="form-control-static"><input type="text" class="form-control" id="fuel_card_num" name="fuel_card_num" ></p>
				            </div>
						</div>

				         <div class="form-group row">
				            <label class="col-sm-3 form-control-label">Fuel Card Pin</label>
				            <div class="col-sm-9">
				                <p class="form-control-static"><input type="text" class="form-control" id="fuel_card_pin" name="fuel_card_pin" ></p>
				            </div>
						</div>

				         <div class="form-group row">
				            <label class="col-sm-3 form-control-label">Purchase Date</label>
				            <div class="col-sm-9">
				                <p class="form-control-static"><input type="text" class="flatpickr form-control flatpickr-input" id="purchase_date" name="purchase_date" ></p>
				            </div>
						</div>

				         <div class="form-group row">
				            <label class="col-sm-3 form-control-label">Purchase Price</label>
				            <div class="col-sm-9">
				                <p class="form-control-static">
				                <div style="display: block; float: left; margin-top: 7px; position: absolute; margin-left: -15px;">$</div><input type="text" class="form-control" id="purchase_price" name="purchase_price" >
				                </p>
				            </div>
						</div>

				         <div class="form-group row">
				            <label class="col-sm-3 form-control-label">Roadside assistance Number</label>
				            <div class="col-sm-9">
				                <p class="form-control-static"><input type="text" class="form-control" id="ra_num" name="ra_num" ></p>
				            </div>
						</div>

				         <div class="form-group row">
				            <label class="col-sm-3 form-control-label">Insurance Policy #</label>
				            <div class="col-sm-9">
				                <p class="form-control-static"><input type="text" class="form-control" id="ins_pol_num" name="ins_pol_num" ></p>
				            </div>
						</div>

				         <div class="form-group row">
				            <label class="col-sm-3 form-control-label">Policy Expires</label>
				            <div class="col-sm-9">
				                <p class="form-control-static"><input type="text" class="flatpickr form-control flatpickr-input" id="pol_exp" name="pol_exp" ></p>
				            </div>
						</div>

				         <div class="form-group row">
				            <label class="col-sm-3 form-control-label">VIN No.</label>
				            <div class="col-sm-9">
				                <p class="form-control-static"><input type="text" class="form-control" id="vin_num" name="vin_num" ></p>
				            </div>
						</div>

				         <div class="form-group row">
				            <label class="col-sm-3 form-control-label">Plant ID</label>
				            <div class="col-sm-9">
				                <p class="form-control-static"><input type="text" class="form-control" id="plant_id" name="plant_id" ></p>
				            </div>
						</div>

				         <div class="form-group row">
				            <label class="col-sm-3 form-control-label">Tech Vehicle</label>
				            <div class="col-sm-9">
				                <p class="form-control-static">
				                    <select name="tech_vehicle" id="tech_vehicle" class="form-control">
										<option value="">--Select--</option>
										<option value="1">Yes</option>
										<option value="0">No</option>
				                    </select>
				                </p>
				            </div>
						</div>

				        <div class="form-group row">
				            <label class="col-sm-3 form-control-label">Assign to SATS User<span style="color:red">*</span></label>
				            <div class="col-sm-9">
				                <p class="form-control-static">
				                    <select name="StaffID" id="staff_id" class="form-control">
				                      <option value="">Unassigned</option>
										<?php 
										foreach ($staff_info as $row) { ?>
											<option value="<?php echo $row['staff_accounts_id']; ?>"><?php echo $row['FirstName'].' '.$row['LastName']; ?></option>
										<?php
										}
										?>
				                    </select>
				                </p>
				            </div>
						</div>


						<div class="form-group row">
				            <label class="col-sm-3 form-control-label">Ownership<span style="color:red">*</span></label>
				            <div class="col-sm-9">
				                <p class="form-control-static">
				                    <select name="vehicle_ownership" id="vehicle_ownership" class="form-control vehicle_ownership">		
										<option value="1">Company</option>	
										<option value="2">Personal</option>
				                    </select>
				                </p>
				            </div>
						</div>

				         <div class="form-group row">
				         <label class="col-sm-3 form-control-label">&nbsp;</label>
				            <div class="col-sm-9">
				                <p class="form-control-static"><input type="submit" name="btn_add_vehicle" id="btn_add_vehicle" class="btn" value="Add Vehicle"></p>
				            </div>
						</div>

					</div>
				</div>
        	</form>
        </div>
	</section>

</div>


<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4>Add Vehicle</h4>
	<p>
	This page allows you to add new vehicles and assign them to a user.
	</p>

</div>
<!-- Fancybox END -->
<script>
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

	jQuery("#btn_add_vehicle").click(function(){
	
		var rego_expires = jQuery("#rego_expires").val();
		var staff_id = jQuery("#staff_id").val();
		var wof = jQuery("#wof").val();
		var vehicle_ownership = jQuery("#vehicle_ownership").val();
		var error = "";
		
		if(rego_expires==""){
			error += "Rego expiry date is required\n";
		}
		if(wof==""){
			error += "WOF is required\n";
		}
		if(staff_id==""){
			error += "SATS user is required\n";
		}
		if( vehicle_ownership == '' ){
			error += "Ownership required\n";
		}
		
		if(error!=""){
			swal('',error,'error');
			return false;
		}else{
			jQuery("#frm_vehicle").submit();
		}
		
	});

	$('#staff_id').on('change', function() {
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