<style>
.tech_stocktake_div table{
    margin-bottom: 21px;
}
.qty,
.carton{
    width: 62px;
}
.vehicle{
    width: 153px
}
.stock_tbl{
    border: 1px solid #dee2e6;
}
</style>
<div class="box-typical box-typical-padding">

	<?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => $_SERVER['PHP_SELF']
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);
	?>

      

	<section>
		<div class="body-typical-body tech_stocktake_div" style="padding-top:25px;">


			<?php
	            $form_attr = array(
	                'id' => 'jform'
	            );
                echo form_open('/stock/update_tech_stock_process',$form_attr);
                
              
			?>


                    <div class="row">
                        <div class="col-md-12 col-lg-5 columns">                            

                            <table class="table stock_tbl">
                                <thead>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Carton</th>
                                    <th>Qty</th>
                                </thead>
                                <?php
                                foreach($getStocks->result_array() as $row){ ?>

                                    <!-- STOCK ITEMS -->
                                    <tr>
                                        <td>
                                            <input type="hidden" name="stocks[]" value="<?php echo $row['stocks_id']; ?>" />
                                            <?php echo $row['code'] ?>
                                        </td>
                                        <td>
                                            <?php echo $row['item'] ?>
                                        </td>
                                        <td>
                                            <?php echo $row['carton'] ?>
                                            <input type="hidden" class='carton' value="<?php echo $row['carton'] ?>" />
                                        </td>
                                        <td>
                                            <?php
                                            //SWITCH CONDITION HERE
                                            if( $tech_stock_id != "" ){ //view only
                                                $tsi_sql = $this->stock_model->getTechStockItems($tech_stock_id,$row['stocks_id']);
                                            }else{ // udpate (view update button)
                                                $tsi_sql = $this->stock_model->getLatestStocktake($staff_id,$row['stocks_id']);
                                            }
                                            $tsi = $tsi_sql->row_array();
                                            ?>    
                                            <input type="number" class="form-control qty" id="make" name="quantity[]" value="<?php echo $tsi['quantity'] ?>" >                                    
                                        </td>
                                    </tr>
                                <?php
                                }
                                ?>

                                <!-- VEHICLE -->
                                <tr>
                                    <td colspan="3">
                                        <label class="form-control-label"><strong>Vehicle</strong></label>
                                    </td>
                                    <td>
                                        <select name="vehicle" class="form-control vehicle">
                                            <option value="">----</option>

                                            <?php 
                                                
                                                //SWITCH CONDITION HERE
                                                if( $tech_stock_id != ""){ //view only
                                                    $sel_v_sql = $this->stock_model->getTechstockSelectedVehicle($tech_stock_id);
                                                
                                                }else{ 
                                                    
                                                    // udpate (view update button)
                                                    $staffVehicle_params = array('sel_query'=>'*', 'staff_id' => $staff_id);
                                                    $sel_v_sql = $this->stock_model->staffVehicle($staffVehicle_params);

                                                }

                                                $sel_v = $sel_v_sql->row_array();

                                                //SWITCH CONDITION HERE
                                                if($tech_stock_id!=""){ //view only
                                                    $staff_vehicle = $sel_v['vehicle'];
                                                }else{ // udpate (view update button)
                                                    $staff_vehicle = $sel_v['vehicles_id'];
                                                }

                                                $staffVehicle_params = array('sel_query'=>'*');
                                                $v_sql = $this->stock_model->staffVehicle($staffVehicle_params);

                                                foreach($v_sql->result_array() as $v){
                                            ?>
                                                    <option class="form-control" value="<?php echo $v['vehicles_id']; ?>" <?php echo ($v['vehicles_id']==$staff_vehicle)?'selected="selected"':''; ?>><?php echo $v['number_plate']; ?></option>
                                            <?php
                                                }
                                            ?>

                                    </select>
                                    </td>
                                </tr>
                            </table>

   


                            <?php 
                            //SWITCH CONDITION HERE
                            if( $tech_stock_id == "" && $staff_id > 0 ){ // udpate (view update button)
                            ?>
                                <div class="form-group row">                                    
                                    <div class="col-sm-8">
                                        <input type="hidden" name="staff_id" id="staff_id" value="<?php echo $staff_id; ?>" />
                                        <input type="submit" id="submit_btn" class="btn" name="submit" value="Submit" />
                                    </div>
                                </div> 
                            <?php
                            }
                            ?>
                    

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
        This page is used for Technicians to stocktake their vehicles
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
	
});
</script>