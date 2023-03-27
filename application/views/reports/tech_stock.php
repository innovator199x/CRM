<style>
    .col-mdd-3{
        max-width:15.5%;
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
        'link' => "/stock/tech_stock"
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
        echo form_open('/stock/tech_stock',$form_attr);
        ?>
            <div class="for-groupss row">


                <div class="col-lg-10 col-md-12 columns">
                    <div class="row">


                        <div class="col-mdd-3">
							<label>Date From</label>
							<input name="date_from_filter" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text" placeholder="ALL" value="<?php echo $this->input->get_post('date_from_filter'); ?>">
						</div>
                        <div class="col-mdd-3">
							<label>To</label>
							<input name="date_to_filter" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text" placeholder="ALL" value="<?php echo $this->input->get_post('date_to_filter'); ?>">
						</div>


                        <div class="col-mdd-3">
                           <label>Technician</label>
                            <select id="tech_filter" name="tech_filter" class="form-control">
                                <option value="">ALL</option>
                                <?php 
                                    foreach($tech->result_array() as $row){
                                        $sel = ($row['staff_id']==$this->input->get_post('tech_filter'))?'selected="true"':NULL;
                                ?>
                                    <option <?php echo $sel; ?> value="<?php echo $row['staff_id'] ?>"><?php echo $row['FirstName']." ".$row['LastName'] ?></option>
                                <?php
                                    }
                                ?>
                            </select>
                        </div>

                          <div class="col-mdd-3">
                           <label>Vehicle</label>
                            <select id="vech_filter" name="vech_filter" class="form-control">
                                <option value="">ALL</option>
                                <?php 
                                    foreach($vech->result_array() as $row){
                                        $sel = ($row['vehicle']==$this->input->get_post('vech_filter'))?'selected="true"':NULL;
                                ?>
                                    <option <?php echo $sel; ?> value="<?php echo $row['vehicle'] ?>"><?php echo $row['number_plate'] ?></option>
                                <?php
                                    }
                                ?>
                            </select>
                        </div>

                     

                        <div class="col-md-1 columns">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <input class="btn" type="submit" name="btn_search" value="Search">
                        </div>
                        
                    </div>

                </div>

                <!-- DL ICONS START --> 
			    <div class="col-lg-2 col-md-12 columns">
                    <section class="proj-page-section float-right">
                        <div class="proj-page-attach">
                            <i class="fa fa-file-excel-o"></i>
                            <p class="name"><?php echo $title; ?></p>
                            <p>
								<a href="<?php echo $export_link; ?>" target="blank">
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
						<th>Technician</th>
						<th>Vehicle</th>
						<th>Day</th>
						<th>Date</th>
                        <?php 
                        foreach($getStocks->result_array() as $s){
                        ?>
                         <th><?php echo $s['display_name']; ?></th>
                        <?php
                        }
                        
                        ?>
						<th>Details</th>
						</tr>
					</thead>

					<tbody>

                        <?php
                        
                            foreach($lists->result_array() as $row){
                        ?>

                                <tr>
                                    <td>
                                        <?php echo $this->system_model->formatStaffName($row['FirstName'], $row['LastName']); ?>
                                    </td>
                                    <td>
                                        <?php
                                            echo $row['number_plate']
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                            echo  date("l",strtotime($row['date']));
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                             echo date("d/m/Y H:i",strtotime($row['date']));
                                        ?>
                                    </td>
                                    <?php
                                     
                                        foreach( $getStocks->result_array() as $s ){ 
                                    ?>
                                            <td>
                                                <?php
                                                $ts_sql2 = $this->stock_model->getTechStockItems($row['tech_stock_id'],$s['stocks_id']);
                                                $ts2 = $ts_sql2->row_array();
                                                $ts2['quantity'];
                                                $tot_array[$s['stocks_id']] = $tot_array[$s['stocks_id']]+$ts2['quantity'];
                                                ?>
                                                <span class="txt_lbl <?php 
                                                echo ( 
                                                    ( $s['stocks_id']==7 && $ts2['quantity']<250 ) ||
                                                    ( $s['stocks_id']==2 && $ts['electrician']==0 && $ts2['quantity']<40 ) ||
                                                    ( $s['stocks_id']==1 && $ts['electrician']==1 && $ts2['quantity']<40 ) ||
                                                    ( $s['stocks_id']==4 && $ts['electrician']==1 && $ts2['quantity']<15 ) ||
                                                    ( $s['stocks_id']==5 && $ts2['quantity']<10 )
                                                )?'jRedColorBold':''; 
                                                ?>">	
                                                <?php echo $ts2['quantity']; ?>
                                                </span>
                                            </td>
                                        <?php	
                                        }
                                        ?>		
                                    <td>
                                    <a href="/stock/update_tech_stock/<?php echo $row['staff_id'] ?>/<?php echo $row['tech_stock_id'] ?>">More</a></td>
                                </tr>

                        <?php
                            }
                        ?>
						<tr class="aviw_drop-h" style="background:#f6f8fa;">
				<td colspan="4" style="text-align:left;"><strong>TOTAL</strong></td>
				<?php
				foreach($tot_array as $val){ ?>
					<td style="text-align:left;"><strong><?php echo $val; ?></strong></td>
				<?php	
				}
				?>
				<td>&nbsp;</td>
			</tr>


            <tr class="aviw_drop-h" style="background:#f6f8fa;">
				<td colspan="4" style="text-align:left;"><strong>VALUE</strong></td>
				<?php
                $stocks_sql_arr = $getStocks->result_array();
                $i = 0;
				foreach($tot_array as $val){ 

                    $total_stock_qty = $val;
                    $stock_price = $stocks_sql_arr[$i]['price'];
                    $stock_total_amount = $stock_price*$total_stock_qty;

                    ?>
					<td style="text-align:left;"><strong><?php echo ( $stock_total_amount > 0 )?'$'.number_format($stock_total_amount,2):null; ?></strong></td>
				<?php	
                $i++;
				}
				?>
				<td>&nbsp;</td>
			</tr>

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
	This page displays all stock takes for technicians
	</p>
    <pre>
<code>SELECT `sa`.`FirstName`, `sa`.`LastName`, `ts_main`.`date`, `ts_main`.`tech_stock_id`, `ts_main`.`staff_id`, `v`.`number_plate`, `sa`.`is_electrician`
FROM `tech_stock` as `ts_main`
LEFT JOIN `staff_accounts` as `sa` ON `sa`.`StaffID` = `ts_main`.`staff_id`
LEFT JOIN `vehicles` as `v` ON `v`.`vehicles_id` = `ts_main`.`vehicle`
INNER JOIN (SELECT MAX(  `date` ) AS latestDate,  `vehicle` FROM  `tech_stock` WHERE  `country_id` = 1 GROUP BY  `vehicle`) as ts ON `ts`.`vehicle` = `ts_main`.`vehicle` AND `ts`.`latestDate` = `ts_main`.`date`
WHERE `ts_main`.`country_id` = 1
AND `v`.`active` = 1
ORDER BY `ts_main`.`date` DESC</code>
    </pre>

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
