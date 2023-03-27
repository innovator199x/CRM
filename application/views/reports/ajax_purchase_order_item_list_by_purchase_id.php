
<table class="table_ss gio_aa_table table-hover_ss main-table_ss" style="margin-bottom:15px;">
    <tr>
        <th>Code</td>
        <th>Item</th>
        <th>Carton</th>
        <th>Price</th>
        <th>Qty</th>
        <th>Total</th>
    </tr>
    <?php
        if($poi_sql->num_rows()>0){
            foreach( $poi_sql->result_array() as $poi ) { ?>
                <tr class="<?php echo ( $poi['quantity'] > 0 )?'':'fadeOutText'; ?>">
                    <td>
                        <input type="hidden" name="purchase_order_item_id[]" value="<?php echo $poi['purchase_order_item_id']; ?>" />
                        <input type="hidden" name="stocks_id[]" value="<?php echo $poi['stocks_id']; ?>" />
                        <input type="text" class="form-control code read-only" name="code[]" style="width: 135px;" readonly="readonly" value="<?php echo $poi['code']; ?>" />
                    </td>
                    <td><input type="text" class="form-control item read-only" name="item[]" style="width: 280px;" readonly="readonly" value="<?php echo $poi['item']; ?>" /></td>
                    <td><input type="text" class="form-control carton read-only" name="carton[]" style="width: 90px;" readonly="readonly" value="<?php echo $poi['carton']; ?>" /></td>
                    <td>
                        <input type="text" class="form-control price_lbl read-only" style="width: 90px;" readonly="readonly" value="$<?php echo number_format($poi['price'],2); ?>" />
                        <input type="hidden" name="price[]" class="price" value="<?php echo $poi['price']; ?>" />
                    </td>
                    <td><input type="text" class="form-control qty" name="qty[]" style="width: 65px;" value="<?php echo $poi['quantity']; ?>" /></td>
                    <td>
                        <input type="text" class="form-control total_lbl read-only" style="width: 100px;" readonly="readonly" value="$<?php echo $poi['total']; ?>" />
                        <input type="hidden" name="total[]" class="total" value="<?php echo $poi['total']; ?>" />
                    </td>
                </tr>
        <?php	
            }
        }else{
            echo "<tr><td colspan='5'>No Data Found</td></tr>";
        }
        ?>	
</table>

<script type="text/javascript">


    jQuery(document).on("keyup",".qty",function(){
		
        var qty = jQuery(this).val();
        var price = jQuery(this).parents("tr:first").find(".price").val();
        var total = price*qty;
        
        if($.isNumeric(qty)){
            if( qty>0 ){

            jQuery(this).parents("tr:first").removeClass("fadeOutText");
            jQuery(this).parents("tr:first").find(".total_lbl").val("$"+total.toFixed(2));

            }else{
                
                jQuery(this).parents("tr:first").addClass("fadeOutText");
                jQuery(this).parents("tr:first").find(".total_lbl").val("$00.00");

            }
        }else{
            swal('','Invalid input','error');
            jQuery(this).val(0);
            jQuery(this).parents("tr:first").addClass("fadeOutText");
        }
       
        
        jQuery(this).parents("tr:first").find(".total").val(total.toFixed(2));
       
        
   });


</script>