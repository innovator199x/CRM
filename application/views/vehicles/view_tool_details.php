<div class="box-typical box-typical-padding">
	<?php 
		// breadcrumbs template
		$bc_items = array(
            array(
				'title' => 'Reports',
				'link' => "/reports"
			),
            array(
				'title' => 'View Tools',
				'link' => "/vehicles/view_tools"
			),
			array(
				'title' => $title,
				'status' => 'active',
				'link' => "/vehicles/view_tool_details/{$this->uri->segment('3')}"
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
            echo form_open('/vehicles/update_tool',$form_attr);
		?>

        <div class="row">
        <div class="col-md-12 col-lg-5 columns">

        <div class="form-group row">
            <label class="col-sm-3 form-control-label">Item</label>
            <div class="col-sm-9">
            <p class="form-control-static"><input readonly="true" type="text" class="form-control" id="item" name="item" value="<?php echo $t['item_name'] ?>" ></p>
            </div>
		</div>


        <div class="form-group row">
            <label class="col-sm-3 form-control-label">Item ID</label>
            <div class="col-sm-9">
                <p class="form-control-static"><input type="text" class="form-control" id="item_id" name="item_id" value="<?php echo $t['item_id'] ?>" ></p>
            </div>
		</div>

        <div class="form-group row">
            <label class="col-sm-3 form-control-label">Brand</label>
            <div class="col-sm-9">
                <p class="form-control-static">
                <select id="brand" name="brand" class="form-control brand brand_dp" >
				<option value="">Please Select</option>				
				<option value="Gorilla" <?php echo ($t['brand']=='Gorilla')?'selected="selected"':''; ?>>Gorilla</option>
                <option value="Rhino" <?php echo ($t['brand']=='Rhino')?'selected="selected"':''; ?>>Rhino</option>
                <option value="werner" <?php echo ($t['brand']=='werner')?'selected="selected"':''; ?>>werner</option>
			</select>
                </p>
            </div>
		</div>

         <div class="form-group row">
            <label class="col-sm-3 form-control-label">Description</label>
            <div class="col-sm-9">
                <p class="form-control-static">
                <select id="description" name="description" class="form-control description description_dp">
				<option value="">Please Select</option>				
				<option value="3FT Single sided ladder" <?php echo ($t['description']=='3FT Single sided ladder')?'selected="selected"':''; ?>>3FT Single sided ladder</option>
                <option value="3FT Double sided ladder" <?php echo ($t['description']=='3FT Double sided ladder')?'selected="selected"':''; ?>>3FT Double sided ladder</option>
                <option value="4FT Single sided ladder" <?php echo ($t['description']=='4FT Single sided ladder')?'selected="selected"':''; ?>>4FT Single sided ladder</option>
                <option value="4FT Double sided ladder" <?php echo ($t['description']=='4FT Double sided ladder')?'selected="selected"':''; ?>>4FT Double sided ladder</option>
                <option value="6FT Single sided ladder" <?php echo ($t['description']=='6FT Single sided ladder')?'selected="selected"':''; ?>>6FT Single sided ladder</option>				
                <option value="6FT Double sided ladder" <?php echo ($t['description']=='6FT Double sided ladder')?'selected="selected"':''; ?>>6FT Double sided ladder</option>
                <option value="8FT Single sided ladder" <?php echo ($t['description']=='8FT Single sided ladder')?'selected="selected"':''; ?>>8FT Single sided ladder</option>
                <option value="8FT Double sided ladder" <?php echo ($t['description']=='8FT Double sided ladder')?'selected="selected"':''; ?>>8FT Double sided ladder</option>
                <option value="10FT Single sided ladde" <?php echo ($t['description']=='10FT Single sided ladder')?'selected="selected"':''; ?>>10FT Single sided ladder</option>
                <option value="10FT Double sided ladder" <?php echo ($t['description']=='10FT Double sided ladder')?'selected="selected"':''; ?>>10FT Double sided ladder</option>
			</select>
                </p>
            </div>
		</div>

         <div class="form-group row">
            <label class="col-sm-3 form-control-label">Purchase Date</label>
            <div class="col-sm-9">
                <p class="form-control-static"><input type="text" class="flatpickr form-control flatpickr-input" id="purchase_date" name="purchase_date" value="<?php echo $this->system_model->formatDate($t['purchase_date'],'d/m/Y')  ?>" ></p>
            </div>
		</div>

         <div class="form-group row">
            <label class="col-sm-3 form-control-label">Purchase Price</label>
            <div class="col-sm-9">
                <p class="form-control-static"><input type="text" class="form-control" id="purchase_price" name="purchase_price" value="<?php echo $t['purchase_price'] ?>" ></p>
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
                         <option <?php echo ( $row['vehicles_id'] == $t['assign_to_vehicle'] )?'selected="selected"':''; ?>  value="<?php echo $row['vehicles_id'] ?>"><?php echo $row['number_plate'] ?> - <?php echo $this->system_model->formatStaffName($row['FirstName'],$row['LastName']) ?> </option> 
                   <?php
                     }
                   ?>
                    </select>
                </p>
            </div>
		</div>

         <div class="form-group row">
         <label class="col-sm-3 form-control-label">&nbsp;</label>
            <div class="col-sm-9" data-itemsim="<?php echo $t['item']; ?>">
                <p class="form-control-static">
                    <input type="hidden" name="tools_id" value="<?php echo $this->uri->segment(3); ?>" />
                    <input type="submit" name="btn_add_tools" id="btn_add_tools" class="btn" value="Update"> &nbsp;&nbsp; 

                    <?php 
                        if($t['item']==1){ ?>
                            <a class="btn" href="/vehicles/ladder_check/<?php echo $tool_id; ?>">Add Ladder Check</a>
                        <?php
                        }
                        ?>	
                        <?php 
                        if($t['item']==2){ ?>
                            <a class="btn" href="/vehicles/test_tag/<?php echo $tool_id; ?>">Add Test and Tag</a>
                        <?php
                        }
                        ?>
                        <?php 
                        if($t['item']==4){ ?>
                            <a class="btn" href="/vehicles/lockout_kit_check/<?php echo $tool_id; ?>">Add Lockout Check</a>
                        <?php	
                        }
                    ?>

                </p>
            </div>
		</div>



        </form>
                
            <!-- LADDER CHECK LIST -->
            <?php
             if($t['item']==1){
            if($lc_sql->num_rows()>0){ ?>

            <div style="text-align: left;margin-bottom:30px;">
            <hr/>
                <h3 class="heading">Ladder Check</h3>
                <table id="tbl_ladder" class="tbl-sd table table-hover main-table table-xs">
                    <thead>
                <tr class="toprow">
                    <th>Date</th>
                    <th>Next Inspection Due</th>
                </tr>
                    </thead>
                    <tbody>
                <?php 
                    foreach($lc_sql->result_array() as $lc){
                ?>
                       <tr class="body_tr">
                            <td><a href="/vehicles/ladder_check_details/<?php echo $lc['ladder_check_id'] ?>/<?php echo $tool_id ?>"><?php echo date('d/m/Y',strtotime($lc['date'])); ?></a></td>
                            <td><?php echo ($lc['inspection_due']!='')?date('d/m/Y',strtotime($lc['inspection_due'])):''; ?></td>
                        </tr>
                <?php
                    }
                ?>
                    </tbody>
                </table>
            </div>

            <?php }} ?>


            <!-- TEST AND TAG LIST -->
            <?php 
            if($t['item']==2){
            if($tnt_sql->num_rows()>0){ ?>

                <div style="text-align: left;margin-bottom:30px;">
                <hr/>
                    <h3 class="heading">Test & Tag</h3>
                    <table id="tbl_ladder" class="tbl-sd table table-hover main-table table-xs">
                        <thead>
                    <tr class="toprow">
                        <th>Date</th>
                        <th>Next Inspection Due</th>
                    </tr>
                        </thead>
                        <tbody>
                    <?php 
                        foreach($tnt_sql->result_array() as $tnt){
                    ?>
                        <tr class="body_tr">
                            <td><a href="/vehicles/test_tag_details/<?php echo $tnt['test_and_tag_id']; ?>/<?php echo $tool_id; ?>"><?php echo date('d/m/Y',strtotime($tnt['date'])); ?></a></td>
                            <td><?php echo ($tnt['inspection_due']!="")?date('d/m/Y',strtotime($tnt['inspection_due'])):''; ?></td>
                        </tr>
                    <?php
                        }
                    ?>
                        </tbody>
                    </table>
                </div>

            <?php } } ?>


             <!-- Lockout Kit -->
             <?php
              if($t['item']==4){
             if($lkc_sql->num_rows()>0){ ?>

            <div style="text-align: left;margin-bottom:30px;">
            <hr/>
                <h3 class="heading">Lockout kit check</h3>
                <table id="tbl_ladder" class="tbl-sd table table-hover main-table table-xs">
                    <thead>
                <tr class="toprow">
                    <th>Date</th>
                    <th>Next checklist Due</th>
                </tr>
                    </thead>
                    <tbody>
                <?php 
                    foreach($lkc_sql->result_array() as $lkc){
                ?>
                    <tr class="body_tr">
                        <td><a href="/vehicles/lockout_kit_check_details/<?php echo $lkc['lockout_kit_check_id']; ?>/<?php echo $tool_id; ?>"><?php echo date('d/m/Y',strtotime($lkc['date'])); ?></a></td>
                        <td><?php echo ($lkc['inspection_due']!="")?date('d/m/Y',strtotime($lkc['inspection_due'])):''; ?></td>
                    </tr>
                <?php
                    }
                ?>
                    </tbody>
                </table>
            </div>

            <?php } } ?>
					

		</div>
		</div>


		</div>
	</section>

</div>



<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
    This page allows you to add and edit tools
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


        


        



    })

</script>
