<div class="box-typical box-typical-padding">
	<?php 
		// breadcrumbs template
		$bc_items = array(
            array(
				'title' => 'View Tools',
				'link' => "/vehicles/view_tools"
			),
			array(
				'title' => $title,
				'status' => 'active',
				'link' => "/vehicles/add_tools"
            )
            
		);
		$bc_data['bc_items'] = $bc_items;
		$this->load->view('templates/breadcrumbs', $bc_data);
	?>


	

	<section>
		<div class="body-typical-body" style="padding-top:25px;">


		<?php
            $form_attr = array(
                'id' => 'jform'
            );
            echo form_open('/vehicles/add_tools',$form_attr);
		?>

        <div class="row">
        <div class="col-md-12 col-lg-5 columns">

        <div class="form-group row">
            <label class="col-sm-3 form-control-label">Item</label>
            <div class="col-sm-9">
                <p class="form-control-static">
                <select name="item" id="item" class="form-control">
                <option value="">Please Select</option>
                    <?php
                    foreach($item_dropdown->result_array() as $row){
                    ?>
                        <option value="<?php echo $row['item'] ?>" ><?php echo $row['item_name'] ?></option>
                    <?php 
                    }
                    ?>
                </select>
                </p>
            </div>
		</div>


        <div class="form-group row">
            <label class="col-sm-3 form-control-label">Item ID</label>
            <div class="col-sm-9">
                <p class="form-control-static"><input type="text" class="form-control" id="item_id" name="item_id" ></p>
            </div>
		</div>

        <div class="form-group row">
            <label class="col-sm-3 form-control-label">Brand</label>
            <div class="col-sm-9">
                <p class="form-control-static">
                <input type="text"  class="form-control addinput brand brand_input" name="brand_input" />
                <select name="brand_dp" class="form-control brand brand_dp"  style="display:none;">
				<option value="">Please Select</option>				
				<option value="GORILLA">GORILLA</option>
				<option value="RHINO">RHINO</option>
				<option value="WERNER">WERNER</option>
			</select>
                </p>
            </div>
		</div>

         <div class="form-group row">
            <label class="col-sm-3 form-control-label">Description</label>
            <div class="col-sm-9">
                <p class="form-control-static">
                <input type="text"  class="form-control addinput description description_input" name="description_input" />
                <select name="description_dp" class="form-control description description_dp" style="display:none;">
				<option value="">Please Select</option>				
				<option value="3FT Single sided ladder">3FT Single sided ladder</option>
				<option value="3FT Double sided ladder">3FT Double sided ladder</option>
				<option value="4FT Single sided ladder">4FT Single sided ladder</option>
				<option value="4FT Double sided ladder">4FT Double sided ladder</option>
				<option value="6FT Single sided ladder">6FT Single sided ladder</option>				
				<option value="6FT Double sided ladder">6FT Double sided ladder</option>
				<option value="8FT Single sided ladder">8FT Single sided ladder</option>
				<option value="8FT Double sided ladder">8FT Double sided ladder</option>
				<option value="10FT Single sided ladde">10FT Single sided ladder</option>
				<option value="10FT Double sided ladder">10FT Double sided ladder</option>
			</select>
                </p>
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
                <p class="form-control-static"><input type="text" class="form-control" id="purchase_price" name="purchase_price" ></p>
            </div>
		</div>

        <div class="form-group row">
            <label class="col-sm-3 form-control-label">Assign to Vehicle</label>
            <div class="col-sm-9">
                <p class="form-control-static">
                    <select name="assign_to_vehicle" id="assign_to_vehicle" class="form-control">
                      <option value="">Please Select</option>
                      <?php 
                     
                        foreach($assign_vehicle_dropdown->result_array() as $row){
                      ?>
                            <option value="<?php echo $row['vehicles_id'] ?>"><?php echo $row['number_plate'] ?> - <?php echo $this->system_model->formatStaffName($row['FirstName'],$row['LastName']) ?> </option> 
                      <?php
                        }
                      ?>
                    </select>
                </p>
            </div>
		</div>

         <div class="form-group row">
         <label class="col-sm-3 form-control-label">&nbsp;</label>
            <div class="col-sm-9">
                <p class="form-control-static"><input type="submit" name="btn_add_tools" id="btn_add_tools" class="btn" value="Add Tools"></p>
            </div>
		</div>



        </form>

		
					

		</div>
		</div>


		</div>
	</section>

</div>



<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4>Add Tools</h4>
	<p>
    This page allows you to add new tools and assign them to a vehicle.
	</p>

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


        	jQuery("#item").change(function(){
		
                var item = jQuery(this).val();
                if( item==1 ){
                    // brand
                    jQuery(".brand_dp").show();
                    jQuery(".brand_input").hide();
                    // description
                    jQuery(".description_dp").show();
                    jQuery(".description_input").hide();
                }else{
                    // brand
                    jQuery(".brand_dp").hide();
                    jQuery(".brand_input").show();
                    // description
                    jQuery(".description_dp").hide();
                    jQuery(".description_input").show();
                }
                
            });


            jQuery("#jform").submit(function(){
	
                var item = jQuery("#item").val();
                var item_id = jQuery("#item_id").val();
                var error = "";
                var submitCount = 0;
                
                if(item==""){
                    error += "Item must not be empty\n";
                }
                if(item_id==""){
                    error += "Item ID must not be empty\n";
                }
                
                if(error!=""){
                   swal('',error,'error');
                   return false;
                }

                if(submitCount==0){
                    submitCount++;
                    jQuery(this).submit();
                }else{
                    swal('','Submission in progress','error');
                }


                
            });



    })

</script>
