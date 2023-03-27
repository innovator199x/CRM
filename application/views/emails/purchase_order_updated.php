<?php $this->load->view('emails/template/email_header.php') ?>


<p>
    <strong><?php echo $trading_name; ?></strong><br/>
    <strong>PO Box <?php echo $company_address; ?></strong><br/>
    <strong>A.B.N <?php echo $abn; ?></strong>

</p>


<table style="width:100%;">

    	<tr>
            <td><strong>Purchase Order No.:</strong></td>
            <td><?php echo $purchase_order_num; ?></td>
        </tr>
        <tr>
            <td><strong>Date:</strong></td>
            <td><?php echo $date; ?></td>
        </tr>
        <tr>
            <td><strong>Supplier Name:</strong></td>
            <td><?php echo $supplier_name; ?></td>
        </tr>
        <tr>
            <td><strong>Supplier Address:</strong></td>
			<td><?php echo $supplier_address; ?></td>
        </tr>
        <tr>
        <td><strong>Supplier Email:</strong></td>
			<td><?php echo $supplier_email; ?></td>
        </tr>

        <?php if(!empty($code_arr)){ ?>
        <tr>
            <td colspan="2">
                <table id="tbl_item" style="border-collapse: collapse; float: left;">
                    <tr style="background-color: #eeeeee;">
                        <td style="border: 1px solid; padding: 5px;">Code</td>
                        <td style="border: 1px solid; padding: 5px;">Item</td>
                        <td style="border: 1px solid; padding: 5px;">Price</td>
                        <td style="border: 1px solid; padding: 5px;">Qty</td>
                        <td style="border: 1px solid; padding: 5px;">Total</td>
                    </tr>
                    <?php 
                        foreach($code_arr as $index=>$code){
                    ?>

                            <tr>
                                <td style="border: 1px solid; padding: 5px;"><?php echo $code; ?></td>
                                <td style="border: 1px solid; padding: 5px;"><?php echo $item_arr[$index]; ?></td>
                                <td style="border: 1px solid; padding: 5px;"><?php echo $price_arr[$index]; ?></td>
                                <td style="border: 1px solid; padding: 5px;"><?php echo $qty_arr[$index]; ?></td>
                                <td style="border: 1px solid; padding: 5px;"><?php echo $total_arr[$index]; ?></td>
                            </tr>

                    <?php
                        }
                    ?>
                </table>
            </td>
        </tr>
        <?php } ?>
<tr><td>&nbsp;</td></tr>

        <tr>
            <td><strong>Order Notes:</strong></td>
            <td><?php echo $item_note; ?></td>
        </tr>
        <tr>
            <td><strong>Deliver to:</strong></td>
            <td><?php echo $deliver_to_name; ?></td>
        </tr>
        <tr>
            <td><strong>Delivery Address:</strong></td>
            <td><?php echo $delivery_address; ?></td>
        </tr>
        <tr>
            <td><strong>Receiver Email:</strong></td>
            <td><?php echo $reciever_email; ?></td>
        </tr>
        <tr>
            <td><strong>Sales Agreement number:</strong></td>
            <td><?php echo $sales_agreement_number; ?></td>
        </tr>
        <tr>
            <td><strong>Delivery Comments:</strong></td>
            <td><?php echo $comments; ?></td>
        </tr>
        <tr>
            <td><strong>Ordered By:</strong></td>
            <td><?php echo $ordered_by_full_name; ?></td>
        </tr>
        <tr>
            <td><strong>Ordered by Email:</strong></td>
            <td><?php echo $order_by_email; ?></td>
        </tr>
        <tr>
            <td><strong>Receiver Phone:</strong></td>
            <td><?php echo $receiver_phone; ?></td>
        </tr>

</table>




<?php $this->load->view('emails/template/email_footer.php') ?>