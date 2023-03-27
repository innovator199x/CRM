

<style>
.carton,
.carton_edit{
    width: 72px;
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
			'link' => "/stock/stock_items"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>
    <section>
    <div class="body-typical-body">
        <div class="table-responsive">
            <table class="table table-hover main-table">
                <thead>
                    <tr>
                       <th>Code</th>
                       <th>Item</th>
                       <th>Display Name</th>
                       <th>Carton</th>
                       <th>Price EX GST</th>
                       <th>Supplier</th>
                       <th>Show on Report</th>
                       <th>Weekly Stocktake</th>
                       <th>Daily Stocktake</th>
                       <th>Active</th>
                       <th>Edit</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                        foreach($lists->result_array() as $row){
                    ?>
                              <tr>
                                <td>
                                    <?php echo $row['code']; ?>
                                   
                                </td>
                                <td>
                                    <?php echo $row['item']; ?>
                                </td>
                                <td>
                                    <?php echo $row['display_name']; ?>
                                </td>
                                <td>
                                    <?php echo $row['carton']; ?>
                                </td>
                                <td>
                                    $<?php echo number_format($row['price'],2); ?>
                                </td>
                                <td>
                                    <?php echo $row['company_name']; ?>
                                </td>
                                <td>
                                    <?php echo ($row['display']==1)?'<span class="text-green">Yes</span>':'<span class="text-red">No</span>'; ?>
                                </td>
                                <td>
                                    <?php echo ($row['show_on_stocktake']==1)?' <span class="text-green">Yes </span>':'<span class="text-red">No</span>'; ?>
                                </td>
                                <td>
                                    <?php echo ($row['is_alarm']==1)?' <span class="text-green">Yes </span>':'<span class="text-red">No</span>'; ?>
                                </td>
                                <td>
                                    <?php echo ($row['status']==1)?'<span class="text-green">Yes</span>':' <span class="text-red">No </span>'; ?>
                                </td>
                                <td class="edit_td">
                                    
                                    <a class="inline_fancybox" data-toggle="tooltip" title="Edit" style="color:#adb7be;" href="#data<?php echo $row['stocks_id']; ?>"><span class="font-icon font-icon-pencil"></span></a>


                                    <!--- UPDATE FANCYBOX START -->
                                    <div style="display:none;" class="snapshot_edit_box" id="data<?php echo $row['stocks_id']; ?>">
                                        <h4>Edit</h4>
                                        

                                        <div style="width:350px;">

                                                    <?php
                                                        $form_attr = array(
                                                            'class' => 'stock_form_edit_form',
                                                            'id'=> 'stock_form_edit_'.$row['stocks_id']
                                                        );
                                                        echo form_open('/stock/edit_tech_stock_process',$form_attr);
                                                    ?>
                                                        <div class="form-group">
                                                            <label>Code</label>
                                                            <input type="text" name="code_edit" id="code_edit" class="code_edit form-control" value="<?php echo $row['code'] ?>">
                                                            <input type="hidden" name="stocks_id" class="stocks_id" value="<?php echo $row['stocks_id']; ?>" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Item</label>
                                                            <input type="text" name="item_edit" id="item_edit" class="item_edit form-control" value="<?php echo $row['item'] ?>">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Display Name</label>
                                                            <input type="text" name="display_name_edit" id="display_name_edit" class="display_name_edit form-control" value="<?php echo $row['display_name'] ?>">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Carton</label>
                                                            <input type="number" name="carton_edit" id="carton_edit" class="carton_edit form-control" value="<?php echo $row['carton'] ?>">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Price</label>
                                                            <input type="text" name="price_edit" id="price_edit" class="price_edit form-control" value="<?php echo $row['price'] ?>">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Supplier</label>
                                                            <select name="supplier_edit" id="supplier_edit" class="supplier_edit form-control">
                                                                <option value="">----</option>	
                                                                <?php
                                                                
                                                                $params = array(
                                                                    'sel_query' => "suppliers_id, company_name",
                                                                    'sort_list' => array(
                                                                    array(
                                                                        'order_by' => '`company_name`',
                                                                        'sort' => 'ASC'
                                                                    )
                                                                    )
                                                                );
                                                                $sup_sql = $this->stock_model->getSupplier($params);
                                                                foreach( $sup_sql->result_array() as $sup ){ 
                                                                $op_selected = ($row['suppliers_id']==$sup['suppliers_id'])?'selected="true"':NULL;
                                                                ?>
                                                                    <option <?php echo $op_selected; ?> value="<?php echo $sup['suppliers_id']; ?>"><?php echo $sup['company_name']; ?></option>
                                                                <?php	
                                                                }
                                                                ?>
                                                            </select>	
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="checkbox">
                                                                <input <?php echo ($row['display']==1)?'checked="true"':NULL ?> class="display_edit" name="display_edit" type="checkbox" id="check1_edit<?php echo $row['stocks_id'] ?>" value="1">
                                                                <label for="check1_edit<?php echo $row['stocks_id'] ?>">Show on Report</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="checkbox">
                                                                <input  <?php echo ($row['show_on_stocktake']==1)?'checked="true"':NULL ?> class="show_on_stocktake_edit" name="show_on_stocktake_edit" type="checkbox" id="check2_editd<?php echo $row['stocks_id'] ?>" value="1">
                                                                <label for="check2_editd<?php echo $row['stocks_id'] ?>">Weekly Stocktake</label>
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <div class="checkbox">
                                                                <input  <?php echo ($row['is_alarm']==1)?'checked="true"':NULL ?> class="is_alarm_edit" name="is_alarm_edit" type="checkbox" id="check3_editd<?php echo $row['stocks_id'] ?>" value="1">
                                                                <label for="check3_editd<?php echo $row['stocks_id'] ?>">Daily Stocktake</label>
                                                            </div>
                                                        </div>

                                                         <div class="form-group">
                                                            <label>Status</label>
                                                            <select name="status_edit" class="form-control">
                                                                <option <?php echo ($row['status']==1)?"selected='true'":NULL  ?> value="1">Yes</option>
                                                                <option <?php echo ($row['status']==0)?"selected='true'":NULL  ?> value="0">No</option>
                                                            </select>
                                                        </div>

                                                        <div class="form-group">
                                                                <input type="submit" value="Update" class="btn btn_update">
                                                        </div>


                                                    </form>

                                                </div>

                                    </div>
                                    <!--- UPDATE FANCYBOX END -->
                                </td>
                            </tr>
                    <?php
                        }
                    ?>
                  
                    
                </tbody>

            </table>
        </div>

    <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
    <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
        
    <div>
    <button type="button" href="#add_new" class="btn inline_fancybox">Add New</button>
    
                        <div style="display:none;" id="add_new" style="width:300px!important;">
                        <h4>Add New</h4>

                        <div style="width:350px;">

                            <?php
                                $form_attr = array(
                                    'id' => 'stock_form'
                                );
                                echo form_open('/stock/add_tech_stock_process',$form_attr);
		                    ?>
                                <div class="form-group">
                                    <label>Code</label>
                                    <input type="text" name="code" id="code" class="code form-control">
                                </div>
                                <div class="form-group">
                                    <label>Item</label>
                                    <input type="text" name="item" id="item" class="item form-control">
                                </div>
                                <div class="form-group">
                                    <label>Display Name</label>
                                    <input type="text" name="display_name" id="display_name" class="display_name form-control">
                                </div>
                                <div class="form-group">
                                    <label>Carton</label>
                                    <input type="number" name="carton" id="carton" class="carton form-control" />
                                </div>
                                <div class="form-group">
                                    <label>Price</label>
                                    <input type="text" name="price" id="price" class="price form-control">
                                </div>
                                <div class="form-group">
                                    <label>Supplier</label>
                                    <select name="supplier" id="supplier" class="form-control">
                                        <option value="">----</option>	
                                        <?php
                                        $params = array(
                                            'sel_query' => "suppliers_id, company_name",
                                            'sort_list' => array(
                                               array(
                                                'order_by' => '`company_name`',
                                                'sort' => 'ASC'
                                               )
                                            )
                                        );
                                        $sup_sql = $this->stock_model->getSupplier($params);
                                        foreach( $sup_sql->result_array() as $sup ){ ?>
                                            <option value="<?php echo $sup['suppliers_id']; ?>"><?php echo $sup['company_name']; ?></option>
                                        <?php	
                                        }
                                        ?>
                                    </select>	
                                </div>
                                <div class="form-group">
                                    <div class="checkbox">
                                        <input class="display" name="display" type="checkbox" id="check-1" value="1">
                                        <label for="check-1">Show on Report</label>
								    </div>
                                </div>
                                <div class="form-group">
                                    <div class="checkbox">
                                        <input class="show_on_stocktake" name="show_on_stocktake" type="checkbox" id="check-2" value="1">
                                        <label for="check-2">Show on Stocktake</label>
								    </div>
                                </div>
                                <div class="form-group">
                                        <input type="submit" value="Submit" class="btn">
                                </div>


                            </form>

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
<p>
This page lists all items and their pricing. We can also choose to display the item on reports and/or stocktake.
</p>
<pre>
<code>SELECT `s`.`stocks_id`, `s`.`code`, `s`.`item`, `s`.`display_name`, `s`.`price`, `s`.`display`, `s`.`show_on_stocktake`, `s`.`status`, `s`.`carton`, `s`.`is_alarm`, `sup`.`company_name`, `sup`.`suppliers_id`
FROM `stocks` as `s`
LEFT JOIN `suppliers` as `sup` ON `sup`.`suppliers_id` = `s`.`suppliers_id`
WHERE `s`.`country_id` = 1
ORDER BY `s`.`code` ASC</code>
</pre>

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


            $(".inline_fancybox").fancybox({
                'hideOnContentClick': true,
                'width': 500,
                'height': 'auto',
                'autoSize': false,
                'autoDimensions':false
            });


            $('#stock_form').submit(function(){

                    var counter = 0;
                    var code = jQuery("#code").val();		
                    var item = jQuery("#item").val();
                    var price = jQuery("#price").val();
                    var error = "";

                    if($.trim(code).length == 0){
                        error += "Code must not be empty\n";
                    }

                    if($.trim(item).length == 0){
                        error += "Item must not be empty\n";
                    }

                    if($.trim(price).length == 0){
                        error += "Price must not be empty";
                    }else{
                        if(is_numeric(price)==false){
                            error += "Price format invalid";
                        }
                    }
                    
                    if(error!=""){
                        swal('',error,'error');
                        return false;
                    }
                    
                    if(counter==0){
                        counter++;
                        $(this).submit();
                        return false;
                    }else{
                        swal('','Submission in progress','error');
                    }
                    

            })


            $('.btn_update').on('click',function(e){
                e.preventDefault();

                var counter = 0;
                var form  = jQuery(this).parents(".stock_form_edit_form").attr('id');
                
                var stocks_id = jQuery(this).parents(".stock_form_edit_form").find(".stocks_id").val();
                var code = jQuery(this).parents(".stock_form_edit_form").find(".code_edit").val();
                var item = jQuery(this).parents(".stock_form_edit_form").find(".item_edit").val();
                var display_name = jQuery(this).parents(".stock_form_edit_form").find(".display_name_edit").val();
                var price = jQuery(this).parents(".stock_form_edit_form").find(".price_edit").val();
                var display = jQuery(this).parents(".stock_form_edit_form").find(".display_edit").val();
                var status = jQuery(this).parents(".stock_form_edit_form").find(".status_edit").val();
                var supplier = jQuery(this).parents(".stock_form_edit_form").find(".supplier_edit").val();
                var show_on_stocktake = jQuery(this).parents(".stock_form_edit_form").find(".show_on_stocktake_edit").val();
                var is_alarm_edit = jQuery(this).parents(".stock_form_edit_form").find(".is_alarm_edit").val();		
                var error = "";


                if($.trim(code).length == 0){
                    error += "Code must not be empty\n";
                }

                if($.trim(item).length == 0){
                    error += "Item must not be empty\n";
                }

                if($.trim(price).length == 0){
                    error += "Price must not be empty";
                }else{
                    if(is_numeric(price)==false){
                        error += "Price format invalid";
                    }
                }
                
                if(error!=""){
                    swal('',error,'error');
                    return false;
                }

                if(counter==0){
                        counter++;
                        $('#'+form).submit();
                        return false;
                    }else{
                        swal('','Submission in progress','error');
                    }
                

            })


    })
</script>