<style>
    .col-mdd-3{
        max-width:20%;
    }
    .add_purchase_order_btn{
        margin-top: 10px;
    }
</style>
<div class="box-typical box-typical-padding">

    <?php
    $export_links_params_arr = array(
        'date_from' => $date_from,
        'date_to' => $date_to,
        'supplier_filter' => $supplier
    );
    $export_link_params = '/reports/purchase_order/?export=1&'.http_build_query($export_links_params_arr);
    ?>

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
        'link' => "/reports/purchase_order"
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);
?>

	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">
			<?php
		$form_attr = array(
			'id' => 'jform'
		);
		echo form_open('/reports/purchase_order',$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-md-6 columns">
					<div class="row">

						<div class="col-mdd-3">
							<label>Date From</label>
                            <input name="date_from" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text" placeholder="ALL" value="<?php echo $date_from; ?>">
						</div>

					<div class="col-mdd-3">
							<label>Date To</label>
                            <input name="date_to" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text" placeholder="ALL" value="<?php echo $date_to; ?>">
						</div>

						<div class="col-mdd-3">
							<label>Supplier</label>
							<select id="supplier_filter" name="supplier_filter" class="form-control">
                                <option value="">ALL</option>
                                <?php
                                    foreach($supplier_list as $row){
                                        $selected_supp = ($row['suppliers_id']==$supplier)?'selected="selected"':NULL;
                                ?>
                                        <option <?php echo $selected_supp; ?> value="<?php echo $row['suppliers_id'] ?>"><?php echo $row['company_name'] ?></option>
                                <?php
                                    }
                                ?>
							</select>
						</div>

						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<input type="submit" name="search_submit" value="Search" class="btn">
						</div>
						
					</div>

                </div>
                <div class="col-md-4 columns text-right">
                    <a href="/reports/add_purchase_order" class="btn btn-danger add_purchase_order_btn">Add Purchase Order</a>
                </div>
                 <!-- DL ICONS START -->
				<div class="col-md-2 columns">
                    <section class="proj-page-section float-right">
                        <div class="proj-page-attach">
                            <i class="fa fa-file-excel-o"></i>
                            <p class="name"><?php echo $title; ?></p>
                            <p>
                                <a href="<?php echo $export_link_params ?>" target="blank">
                                    Export
                                </a>
                                
                            </p>
                        </div>
                    </section>
				</div>
				<!-- DL ICONS END -->
			</div>
			</form>
		</div>
	</header>

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<table class="table table-hover main-table">
					<thead>
						<tr>
							<th>Date</th>
							<th>PO No.</th>
							<th>Supplier</th>
							<th>Deliver to</th>
                            <th>Order Total</th>
                            <th>Agency</th>
							<th>Problem</th>
						</tr>
					</thead>

					<tbody>
                       <?php 
                            foreach($lists->result_array() as $row){
                        ?>

                                   <tr>
                                        <td>
                                            <a href="/reports/purchase_order_details/<?php echo $row['po_id']  ?>"><?php echo $this->system_model->formatDate($row['po_date'],'d/m/Y') ?></a>
                                        </td>
                                        <td>
                                            <span class="txt_lbl"><?php echo $row['purchase_order_num']; ?></span>
                                        </td>
                                        <td>
                                            <span class="txt_lbl"><?php echo $row['sup_company_name']; ?></span>
                                        </td>
                                      
                                        <td>
                                        <span class="txt_lbl"><?php echo $this->system_model->formatStaffName($row['dt_fname'], $row['dt_lname']); ?></span>
                                        </td>
                                        <td>
                                            <?php
                                                if( $row['po_suppliers_id'] == $this->purchase_model->getDynamicHandyManID() ){ // if supplier is handyman
                                                    
                                                    $poi_tot = $row['po_invoice_total'];	
                                                    
                                                }else{
                                                    
                                                    $jparams = array(
                                                        'sel_query' => "SUM(poi.`total`) AS poi_total",
                                                        'purchase_order_id' => $row['po_id']
                                                    );
                                                    $poi_sql = $this->purchase_model->getPurchaseOrderItem($jparams);
                                                    $poi = $poi_sql->row_array();
                                                    $poi_tot = $poi['poi_total'];
                                                    
                                                }

                                                echo ($poi_tot>0)?'$'.number_format($poi_tot,2):'';
                                            ?>
                                        </td>

                                        <td data-agency_id = "<?php echo $row['agency_id'] ?>" class="<?php echo ( $row['priority'] > 0 )?'j_bold':null; ?>">
                                            <span class="txt_lbl"><?php echo $row['a_name']." ".( ( $row['priority'] > 0 )?' ('.$row['abbreviation'].')':null ); ?></span>
                                        </td>
                                        <td>
                                           <span class="txt_lbl"><?php echo ( $row['po_suppliers_id'] == $this->purchase_model->getDynamicHandyManID() )?$row['item_note']:NULL; ?></span>
                                        </td>
                                    </tr>

                        <?php
                            }
                       ?>

                            <?php if($lists->num_rows()>0){ ?>
                        <tr>
                            
                            <?php
                            //GRAND TOTAL
                            // get non handyman supplier total
                            $jparams_grand_tot = array(
                                'sel_query' => "SUM(poi.`total`) AS poi_total",
                                'supplier_id' => $supplier,
                                'filterDate' =>  array(
                                    'from' => $date_from_2,
                                    'to' => $date_to_2
                                ),
                            );
                            $poi_sql_grand_tot = $this->purchase_model->getPurchaseOrderItem($jparams_grand_tot);
                            $poi_grand_tot = $poi_sql_grand_tot->row_array();
                            $supp_tot = $poi_grand_tot['poi_total'];

                            // get handyman supplier total
                            $aw_params = array(
                                'sel_query' => 'invoice_total',
                                'supplier_id' => $this->purchase_model->getDynamicHandyManID(),
                                'filterDate' =>  array(
                                    'from' => $date_from_2,
                                    'to' => $date_to_2
                                ),
                            );
                            $poi_sql_d = $this->purchase_model->getPurchaseOrder($aw_params);
                            $poi_3 = $poi_sql_d->row_array();
						    $non_supp_tot = $poi_3['invoice_total'];
                            
                            ?>
                            <td colspan="4"><strong>TOTAL</strong></td>
                            <td colspan="3">
                            <strong><?php  echo "$".number_format(($supp_tot+$non_supp_tot),2); ?></strong></td>
                        </tr>
                        <?php } ?>
					</tbody>

				</table>
			</div>

		 <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
        <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
			

		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
    This page lists all purchase orders in the selected date period
	</p>
    <pre>
<code>SELECT `po`.`purchase_order_id` as `po_id`, `po`.`date` as `po_date`, `po`.`purchase_order_num`, `po`.`suppliers_id` as `po_suppliers_id`, `po`.`item_note`, `po`.`invoice_total` as `po_invoice_total`, `sup`.`address` AS `sup_address`, `sup`.`email` AS `sup_email`, `sup`.`company_name` as `sup_company_name`, `sa`.`FirstName` AS `dt_fname`, `sa`.`LastName` AS `dt_lname`, `sa`.`address` AS `dt_address`, `sa`.`Email` AS `dt_email`, `sa2`.`FirstName` AS `ob_fname`, `sa2`.`LastName` AS `ob_lname`, `sa2`.`Email` AS `ob_email`, `a`.`agency_name` as `a_name`, `a`.`agency_id`
FROM `purchase_order` as `po`
LEFT JOIN `agency` as `a` ON `a`.`agency_id` = `po`.`agency_id`
LEFT JOIN `suppliers` as `sup` ON `sup`.`suppliers_id` = `po`.`suppliers_id`
LEFT JOIN `staff_accounts` as `sa` ON `sa`.`StaffID` = `po`.`deliver_to`
LEFT JOIN `staff_accounts` as `sa2` ON `sa2`.`StaffID` = `po`.`ordered_by`
WHERE `po`.`active` = 1
AND `po`.`deleted` = 0
AND `po`.`country_id` = 1
AND  `po`.`date` BETWEEN '$date_from' AND '$date_to' 
ORDER BY `po`.`date` DESC
LIMIT 50</code>
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
                confirmButtonClass: "btn-success"
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