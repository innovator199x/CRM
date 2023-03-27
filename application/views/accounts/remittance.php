<style>
.col-mdd-3{
	max-width:15.5%;
}
.agency_filter_tbl,
.payment_details_table,
.close_invoices_tbl{
	width:auto;
	border:none;
}
.agency_filter_tbl tr td,
.payment_details_table tr td,
.close_invoices_tbl tr td{
	border:none;
	padding-left:0;
	vertical-align: top;
}
#close_invoices_div{
	display: none;
}
.ci_address{
	width: 25%;
}
.close_invoices_tbl{		
	width:auto;
}
.ok_tick{
	display: none;
	margin-left: 3px;
}
.inactive_agency_td{
	display: none;
}
.show_it{
	display: block;
}
.agency{
    width: 250px;
    height: 38px;
}
.agency:focus{   
    width: auto; 
    height: 300px;
}
.about_td{
	padding-left: 18px !important;
}
#mbm_box{
	display: none;
	padding: 5px;
	border: 2px solid #d8e2e7;
	width: 216px;
	float: right;
	position: fixed;
	bottom: 46px;
	right: 46px;
	z-index: 99999;		
}
#open_closed_invoice_btn{
	margin: 15px 0;
	float: left;
}
#about_ul .list-group-item{
	padding: 10px;
	margin-left: 21px;
	top: 15px;
	width: 650px;
}
#agency_payment_summary{
	margin: 20px 0 0 27px;	
}
#pagination_div{
	margin: 10px 0;
}
.bulk_pay_det_td{
	display: none;
}
</style>

<?php
  $export_links_params_arr = array(
	'date_from_filter' => $this->input->get_post('date_from_filter'),
	'date_to_filter' => $this->input->get_post('date_to_filter'),
	'reason_filter' => $this->input->get_post('reason_filter'),
    'state_filter' =>  $this->input->get_post('state_filter')
);
$export_link_params = '/reports/discarded_alarms/?export=1&'.http_build_query($export_links_params_arr);
?>
<div class="box-typical box-typical-padding">

	

	<?php 
// breadcrumbs template
$bc_items = array(
    array(
		'title' => 'Accounts',
		'link' => "/accounts/view_statements"
	),
    array(
        'title' => $title,
        'status' => 'active',
        'link' => "/accounts/receipting"
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);
?>

	<header class="box-typical-header">

        <div class="box-typical box-typical-padding">
			<div class="for-groupss row">
				<div class="col-md-12">
					<?php
						$form_attr = array(
							'id' => 'jform'
						);
						echo form_open('/accounts/receipting',$form_attr);
					?>
				
							
							<table class="table agency_filter_tbl">	
								<tr>
									<td class="col1">From<br />
										<input type="text" data-allow-input="true" placeholder="ALL" name="date_from_filter" class="form-control date_from_to j_date_width" value="<?php echo $this->input->get_post('date_from_filter') ?>" />
									</td>
									<td class="col1">To<br />
										<input type="text" data-allow-input="true" placeholder="ALL" name="date_to_filter" class="form-control date_from_to j_date_width"  value="<?php echo $this->input->get_post('date_to_filter') ?>" />
									</td>
									<td class="col1">Active Agency:<br />
										<select name="agency[]" id="active_agency" class="form-control agency" multiple>																			
											<?php 
											foreach($active_agency_filter->result_array() as $agency_filter_row){
											?>
												<option <?php echo ( in_array($agency_filter_row['agency_id'],$agency_filter) ) ? 'selected' : ''; ?> value="<?php echo $agency_filter_row['agency_id'] ?>"><?php echo $agency_filter_row['agency_name'] ?></option>
											<?php
											}
											?>
										</select>
									</td>
	
									<td class="col1">Phrase<br />
										<input type="text" name="search" class="form-control" placeholder="Text" value="<?php echo ($this->input->get_post('search')) ? $this->input->get_post('search') :'' ?>">
									</td>
									<td class="col1">&nbsp;<br />
										<input class="btn" type="submit" name="btn_search" value="Search">
									</td>
									<td>
										<h5 id="agency_payment_summary" class="heading"><span class="txt_red"><?php echo $agency_pay_count; ?></span> Payments totalling <span class="txt_red">$<?php echo number_format($agency_pay_rem_sum,2); ?></span> ready to be Allocated</h5>							
									</td>
							

								</tr>	
							</table>

						</form>
					</div>
				</div>


				<?php echo form_close(); ?>


				<?php
				// BULK PAYMENTS section
				if( $this->input->get_post('agency_payments_id') > 0 ){ ?>

				<div class="row">

					<div class="col-md-6">

						<!-- Closed Invoice -->
						<div class='fl-left row' id="close_invoices_div">
							
							<div class="col-md-12 columns" style="margin-top:15px;">
								<h5 class="heading">Prior Invoice</h5>

									
									<table class="table close_invoices_tbl">														
										<tr>
											<td class="date_td">
												From<br />
												<input type="text" data-allow-input="true" id="cl_inv_from" class="form-control flatpickr j_date_width" placeholder="ALL" value="<?php echo date("01/m/Y"); ?>" />
											</td>					
											<td class="date_td">
												To<br />
												<input type="text" data-allow-input="true" id="cl_inv_to" class="form-control flatpickr j_date_width" placeholder="ALL" value="<?php echo date("t/m/Y"); ?>" />
											</td>

											<td class="ci_address">
												Address:<br />
												<input type="text" id="cl_inv_address" class="form-control" placeholder="Property Address" />		
											</td>
											
											<td>Phrase<br />
												<input type="text" id="cl_inv_phrase" class="form-control" placeholder="Text" />
											</td>
											<td class="text-center">
			
												<br />
												<div class="checkbox-toggle" data-toggle="tooltip" title="Include Closed Invoice">
													<input type="checkbox" id="include_closed_inv" />
													<label for="include_closed_inv"></label>
												</div>

											</td>									
											<td>
												&nbsp;<br />
												<button type='button' class='btn' id="closed_invoice_search_btn">
													<span class="inner_icon_span">Search</span>
												</button>
											</td>
										</tr>
									</table>							

							</div>

													
						</div>

						
						<div style="clear:both;"></div>
								
						<div class='fl-left'>
							<button type='button' class='btn' id="open_closed_invoice_btn" style="margin-top:15px;">
								<span class="inner_icon_span">Prior Invoices</span>
							</button>
						</div>			

					</div>


					<div class="col-md-6">

						<div class='fl-left row' id="bulk_payment_details_div">											
							<div class="col-md-12 columns" style="margin-top:15px;">
								<h5 class="heading">Bulk Payment Details</h5>

								<table class="table payment_details_table" style="border:none;" >						
									
									<tr>
										<td class="col1 bulk_pay_det_td">Agency Payments<br />										
											<select name="agency_payments" id="agency_payments" class="form-control">										
												<option value="">----</option>
												<?php			
												foreach( $agen_pay_sql->result() as $agen_pay ){																						
												?>
													<option value="<?php echo $agen_pay->agency_payments_id; ?>" 
														<?php echo ( $agen_pay->agency_payments_id == $this->input->get_post('agency_payments_id')  )?'selected="selected"':null; ?>
														data-date="<?php echo date('d/m/Y',strtotime($agen_pay->date)); ?>"
														data-remaining="<?php echo $agen_pay->remaining; ?>"
														data-payment_type="<?php echo $agen_pay->payment_type; ?>"
														data-reference="<?php echo $agen_pay->reference; ?>"
													>
														<?php echo "{$agen_pay->reference} - \${$agen_pay->amount}" ?>
													</option>
												<?php
												}										
												?>
											</select>
										</td>

										<td class="col1 bulk_pay_det_td">Payment Date:<br />
											<input  data-allow-input="true" type="text" name="payment_date" id="payment_date" class="flatpickr vw-jb-inpt payment_date form-control j_date_width" value="<?php echo date('d/m/Y'); ?>" />
											<input type="hidden" id="agen_pay_date" value="<?php echo ( $agen_pay_date != '' )?date('d/m/Y',strtotime($agen_pay_date)):date('d/m/Y'); ?>" />
										</td>
								
										<td class="col1 bulk_pay_det_td">Amount Paid:<br />
											<input style="width:100px;" type="text" name="amount_paid" id="amount_paid" class="addinput vw-jb-inpt form-control" value="<?php echo $agen_pay_remaining ?>" />
											<input name="orig_amount_paid" type="hidden" id="orig_amount_paid" class="addinput vw-jb-inpt orig_amount_paid" value="<?php echo $agen_pay_remaining ?>" >
										</td>
							
										<td class="col1 bulk_pay_det_td">Type of Payment:<br />										
											<select name="type_of_payment" id="type_of_payment" class="form-control">										
												<?php			
												foreach( $pt_arr->result_array() as $pt ){											
													$default_selected_dr = ( $agen_pay_payment_type != '' )?$agen_pay_payment_type:5;
												?>
													<option value="<?php echo $pt['payment_type_id']; ?>" <?php echo ( $pt['payment_type_id'] == $default_selected_dr  )?'selected="selected"':null; ?>><?php echo $pt['pt_name'] ?></option>
												<?php
												}										
												?>
											</select>
										</td>

										<td class="col1 bulk_pay_det_td">Payment Reference:<br />
											<input type="text" class="form-control payment_reference" id="payment_reference" name="payment_reference" value="<?php echo $agen_pay_reference; ?>" />
										</td>
							
										
										<td class="col1 bulk_pay_det_td"><br />
											<button type='button' class='btn btn-danger' id="btn_clear" style="margin:0;">
												CLEAR
											</button>
										</td>
										
										<td>
											<div>
												<span class="amount_alloc_main_div text-red">
													$<span id="amount_alloc">0</span>
												</span>
												/	
												$<span id="bulk_amount"></span>	
												<span>Remaining</span> 	
												$<span id="remaining_val"></span> 							
											</div>
											<input type="hidden" id="agen_pay_amount" value="<?php echo $agen_pay_amount; ?>" />
										</td>

										<td>
											<span class="font-icon font-icon-ok ok_tick" id="bulk_pay_tot_tick"></span>
										</td>
									


									</tr>

								</table>
							</div>	
						</div>

					</div>


				</div>
				
				<?php
				}
				?>


				
								


            </div>

		</header>
		

	

	<section>
		<div class="body-typical-body">
			<div class="table-responsive" style="overflow: hidden;">

				
			<?php 	$this->load->view('accounts/agency_payment_list.php'); ?>







				






				<?php
				if( $this->input->get_post('agency_payments_id') > 0 ){ ?>

					<table class="table table-hover main-table" id="invoice_table">
						<thead>
							<tr>
								<th>Invoice Date</th>
								<th>Invoice #</th>
								<th>Property Address</th>
								<th>Agency</th>
								<th>Amount</th>
								<th>Balance</th>
								<th>Amount Paid</th>
								<th>Allocation Date</th>                            
								<th>Payment Reference</th>
								<th>&nbsp;</th>
							</tr>
						</thead>

						<tbody id="inv_tbl_tbody">
							<?php 
								$chckCounter = 1;
							if( $btn_search ){
								if( $plist->num_rows() > 0 ){
									foreach($plist->result_array() as $row){

										$check_digit = $this->system_model->getCheckDigit(trim($row['jid']));
										$bpay_ref_code = "{$row['jid']}{$check_digit}";																			
								?>
							
							<tr class="body_tr" data-toggle="tooltip">
									<td><?php echo ($this->system_model->isDateNotEmpty($row['jdate'])==true)?$this->system_model->formatDate($row['jdate'],'d/m/Y'):''; ?></td>	
									<td> <?php echo $this->gherxlib->crmlink('vjd',$row['jid'],$bpay_ref_code); ?> </td>	
									<td> <?php echo $this->gherxlib->crmlink('vpd',$row['property_id'],"{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}"); ?> </td>	
									<td> 
										<?php echo $this->gherxlib->crmlink('vad',$row['a_id'],$row['agency_name']); ?>
										<input type="hidden" class="agency_id" value="<?php echo $row['a_id']; ?>">
									</td>	
									<td>
										<strong>
											$<?php
											echo $amount = number_format($row['invoice_amount'],2)
											?>
										</strong>
										<input type="hidden" class="amount" value="<?php echo $amount; ?>" />
									</td>
									<td>
										<em style="color:red;">
											$<?php
											echo $balance = number_format($row['invoice_balance'],2)
											?>
										</em>
										<input type="hidden" class="balance" value="<?php echo $balance; ?>" />
									</td>
									<td style="border-left: 1px solid #cccccc"><input type="text" class="form-control payment_fields amount_paid" style="width: 75px" disabled /></td>
									<td><input data-allow-input="true" type="text" class="form-control flatpickr payment_fields payment_date" disabled /></td>								
									<td>
										<input type="text" class="payment_fields form-control payment_reference" disabled />
									</td>
									<td>
										<div class="checkbox" style="margin:0;">
											<input class="job_chk job_chk_oi" name="chk_all" type="checkbox" id="checkbox_<?php echo $chckCounter ?>" value="<?php echo $row['jid']; ?>">
											<label for="checkbox_<?php echo $chckCounter ?>">&nbsp;</label>
										</div>
									</td>
							</tr>

							<?php       
										$chckCounter++;
											}
									}else{ ?>
									<tr>
										<td colspan="10">There are no results for the above search</td>
									</tr>
									<?php
									}	
								}else{ ?>
								<tr>
									<td colspan="10">Use search above to display results</td>
								</tr>
								<?php
								}
							?>
						</tbody>
					</table>

				<?php
				}	
				?>
				
            </div>
			
		<div id="pagination_div">	
		<?php
		if( $this->input->get_post('agency_payments_id') > 0 ){ ?>

			<nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
			<div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>

		<?php
		}
		?>
		</div>
			

		</div>
	</section>


	<div class="save_div" id="mbm_box">
		<button type="button" id="btn_save" class="btn">Apply Payment</button>
		<button type="button" id="btn_clear_list" class="btn btn-danger">Clear</button>
	</div>

	</div style="clear:both;"></div>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
    This page shows all payments ready to be allocated
	</p>
	<pre>
<code>SELECT `j`.`id` AS `jid`, `j`.`status` AS `jstatus`, `j`.`service` AS `jservice`, `j`.`created` AS `jcreated`, `j`.`date` AS `jdate`, `j`.`comments` AS `j_comments`, `j`.`invoice_amount`, `j`.`invoice_balance`, `p`.`property_id`, `p`.`address_1` AS `p_address_1`, `p`.`address_2` AS `p_address_2`, `p`.`address_3` AS `p_address_3`, `p`.`state` AS `p_state`, `p`.`postcode` AS `p_postcode`, `p`.`comments` AS `p_comments`, `p`.`compass_index_num`, `a`.`agency_id` AS `a_id`, `a`.`agency_name`, `a`.`phone` AS `a_phone`, `a`.`address_1` AS `a_address_1`, `a`.`address_2` AS `a_address_2`, `a`.`address_3` AS `a_address_3`, `a`.`state` AS `a_state`, `a`.`postcode` AS `a_postcode`, `a`.`account_emails`, `a`.`agency_emails`, `a`.`franchise_groups_id`
FROM `jobs` as `j`
LEFT JOIN `property` as `p` ON `p`.`property_id` = `j`.`property_id`
LEFT JOIN `agency` as `a` ON `a`.`agency_id` = `p`.`agency_id`
WHERE `j`.`id` > 0
AND             
`j`.`invoice_balance` >0 AND 
`j`.`status` = 'Completed' AND 
`a`.`status` != 'target' AND
`j`.`assigned_tech` != 1
AND (
j.`date` >= '<?php echo $this->config->item('accounts_financial_year') ?>'   OR
j.`unpaid` = 1)  
ORDER BY `j`.`id` ASC
LIMIT 50A</code>
	</pre>

</div>
<!-- Fancybox END -->


<script>
var is_agency_payments = false;	

function compute_total(){

	var bulk_amount = Number(jQuery("#bulk_amount").html());
	var remaining = 0;

	var amount_alloc = 0;
	jQuery(".amount_paid").each(function(){
		var row_amount_paid = Number(jQuery(this).val());
		amount_alloc += row_amount_paid;
	});


	jQuery("#amount_alloc").html(amount_alloc.toFixed(2));
	var remaining = bulk_amount-amount_alloc;
	jQuery("#remaining_val").html(remaining.toFixed(2));
	var job_chk_count = jQuery(".job_chk:checked").length;

	if(  bulk_amount == amount_alloc && job_chk_count > 0  ){

		jQuery("#bulk_pay_tot_tick").show();
		jQuery(".amount_alloc_main_div").removeClass('text-red');
		jQuery(".amount_alloc_main_div").addClass('text-green');
		jQuery("#mbm_box").show();

	}else if(  bulk_amount > amount_alloc && is_agency_payments == true && job_chk_count > 0  ){

		jQuery("#bulk_pay_tot_tick").hide();
		jQuery(".amount_alloc_main_div").addClass('text-red');
		jQuery(".amount_alloc_main_div").removeClass('text-green');
		jQuery("#mbm_box").show();

	}else{		

		jQuery("#bulk_pay_tot_tick").hide();
		jQuery(".amount_alloc_main_div").addClass('text-red');
		jQuery(".amount_alloc_main_div").removeClass('text-green');
		jQuery("#mbm_box").hide();
	}


}


function save_bulk_payments(){

	var active_agency = jQuery("#active_agency").val();
	var inactive_agency = jQuery("#inactive_agency").val();

	// join to agency array
	var multi_agency = active_agency.concat(inactive_agency);

	var bulk_amount_paid = Number(jQuery("#bulk_amount").html());
	var bulk_rows_amount_paid = Number(jQuery("#amount_alloc").html());
	var agen_pay_amount = Number(jQuery("#agen_pay_amount").val());
	var agen_pay_date = jQuery("#agen_pay_date").val();
	
	var bulk_pay_type = jQuery("#type_of_payment").val();
	var bulk_pay_ref = jQuery("#payment_reference").val();	

	var remaining_val = Number(jQuery("#remaining_val").html());


	// success
	var payments_arr = []; 
	jQuery(".job_chk:checked").each(function(){
			
		var this_row = jQuery(this).parents("tr:first");
		var ip_id = this_row.find(".ip_id").val();
		var payment_date = this_row.find(".payment_date").val();
		var amount_paid = this_row.find(".amount_paid").val();
		var orig_amount_paid = this_row.find(".orig_amount_paid").val();		
		var edited = this_row.find(".edited").val();
		var job_id = jQuery(this).val();
		var payment_reference = this_row.find(".payment_reference").val();
		var agency_id = this_row.find(".agency_id").val();

		// wrap on json
		var json_data = { 
			'job_id': job_id,
			'payment_date': payment_date, 
			'amount_paid': amount_paid,
			'type_of_payment': bulk_pay_type,
			'ip_id': ip_id,
			'orig_amount_paid': orig_amount_paid,
			'edited': edited,
			'payment_reference': payment_reference,			
			'agency_id': agency_id			
		}
		var json_str = JSON.stringify(json_data);
		
		payments_arr.push(json_str);			

	});


	if( payments_arr.length > 0 ){

		var agency_payments_id = '<?php echo $this->input->get_post('agency_payments_id'); ?>';

		jQuery('#load-screen').show();
		jQuery.ajax({
			type: "POST",
			url: "/accounts/ajax_save_remittance",
			data: { 					
				payments_arr: payments_arr,
				multi_agency: multi_agency,
				bulk_amount_paid: bulk_amount_paid,
				bulk_rows_amount_paid: bulk_rows_amount_paid,
				bulk_pay_type: bulk_pay_type,
				bulk_pay_ref: bulk_pay_ref,
				remaining_val: remaining_val,
				agency_payments_id: agency_payments_id,
				agen_pay_amount: agen_pay_amount,
				agen_pay_date: agen_pay_date
			}
		}).done(function( ret ){
				
			jQuery('#load-screen').hide();
			swal({
				title: "Success!",
				text: "Payment Success",
				type: "success",
				confirmButtonClass: "btn-success",
				showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
				timer: <?php echo $this->config->item('timer') ?>
			});
			setTimeout(function(){ window.location='/accounts/receipting'; }, <?php echo $this->config->item('timer') ?>);	

		});

	}	


}



function run_bulk_amount_paid(){

	var bulk_payment_details_div = jQuery("#bulk_payment_details_div");

	var amount_paid = Number(bulk_payment_details_div.find("#amount_paid").val());
	bulk_payment_details_div.find("#bulk_amount").html(amount_paid.toFixed(2));
	bulk_payment_details_div.find("#remaining_val").html(amount_paid.toFixed(2));
	
	compute_total();

}


jQuery(document).ready(function(){	

	<?php
	if( $agen_pay_remaining > 0 ){ ?>
		is_agency_payments = true;
		run_bulk_amount_paid();
	<?php		
	}
	?>



	


	// auto select, auto fill bulk payment details field
	jQuery("#agency_payments").change(function(){

		var select_dp = jQuery(this);
		var selected_option = select_dp.find(":selected");

		var date = selected_option.attr("data-date");
		var remaining = selected_option.attr("data-remaining");
		var payment_type = selected_option.attr("data-payment_type");
		var reference = selected_option.attr("data-reference");

		// auto fill bulk payment details
		var pay_det_tbl = jQuery(".payment_details_table");		
		pay_det_tbl.find("#payment_date").val(date);
		pay_det_tbl.find("#amount_paid").val(remaining);
		pay_det_tbl.find("#type_of_payment").val(payment_type);
		pay_det_tbl.find("#payment_reference").val(reference);

	});



	// limit to only pick dates starting from financial year
	jQuery(".date_from_to").flatpickr({
		minDate: "<?php echo $this->config->item('accounts_financial_year'); ?>",
		dateFormat: "d/m/Y",
	});


	jQuery("#show_inactive_agency_chk").change(function(){

		is_checked = jQuery(this).prop("checked");
		if( is_checked == true ){
			jQuery(".inactive_agency_td").show();
		}else{
			jQuery(".inactive_agency_td").hide();
		}

	});


	jQuery("#closed_invoice_search_btn").click(function(){

		var active_agency = jQuery("#active_agency").val();
		var inactive_agency = jQuery("#inactive_agency").val();
		
		// join to agency array
		var multi_agency = active_agency.concat(inactive_agency);
		
		var cl_inv_address = jQuery("#cl_inv_address").val();
		var cl_inv_from = jQuery("#cl_inv_from").val();
		var cl_inv_to = jQuery("#cl_inv_to").val();
		var cl_inv_phrase = jQuery("#cl_inv_phrase").val();
		var include_closed_inv = ( jQuery("#include_closed_inv").prop("checked") == true )?1:0;

		$('#load-screen').show();
		jQuery.ajax({
			type: "POST",
			url: "/accounts/ajax_get_closed_invoice",
			data: { 	
				multi_agency: multi_agency,
				p_address: cl_inv_address,
				from: cl_inv_from,
				to: cl_inv_to,
				phrase: cl_inv_phrase,
				include_closed_inv: include_closed_inv
			}
		}).done(function( ret ){
				
			
			jQuery("#invoice_table #inv_tbl_tbody").append(ret);

			// clear same jobs appearing
			jQuery(".job_chk_oi").each(function(){

				var node = jQuery(this);
				var job_id = node.val();				

				jQuery(".job_chk_ci").each(function(){

					var node2 = jQuery(this);
					var job_id2 = jQuery(this).val();

					if( job_id == job_id2 ){
						node2.parents("tr:first").remove();
					}				

				});


			});

			$('#load-screen').hide();	

		});	

	});

  
    jQuery("#inv_tbl_tbody").on('change','.job_chk',function(){

		// dome/node
		var obj = jQuery(this);
		var this_row = obj.parents("tr:first");

		var bulk_payment_details_div = jQuery("#bulk_payment_details_div");
		
		var job_chk_state = obj.prop("checked");		
		var payment_date = bulk_payment_details_div.find("#payment_date").val();
		var bulk_amount_paid = bulk_payment_details_div.find("#amount_paid").val();		
		var amount = this_row.find(".amount").val();
		var balance = Number(this_row.find(".balance").val());
		var bulk_amount = Number(bulk_payment_details_div.find("#bulk_amount").html());
		var amount_alloc = Number(bulk_payment_details_div.find("#amount_alloc").html());
		var remaining_val = Number(bulk_payment_details_div.find("#remaining_val").html());
		//var payment_details_div = jQuery("#bulk_payment_details_div:visible").length;		
		var payment_reference = bulk_payment_details_div.find("#payment_reference").val();
				
		// remove tooltip hover
		this_row.tooltip('dispose');	
	
		if( bulk_amount_paid > 0 ){

			var row_amount_paid = ( remaining_val > balance )?balance:remaining_val;

			// clear highlights
			this_row.removeClass('j_new_row_bg');
			this_row.removeClass('j_row_selected');
			this_row.removeClass('red_hl');	
			

			if( job_chk_state == true ){ // checked

				// enable
				this_row.find(".amount_paid").prop("disabled",false);
				this_row.find(".payment_date").prop("disabled",false);		
				this_row.find(".payment_reference").prop("disabled",false);					
				
				if( row_amount_paid == balance ){

					// add green highlight
					this_row.addClass('j_row_selected');					
					// remove tooltip hover
					this_row.remove('dispose');	

				}else{

					// add red highlight					
					this_row.addClass('red_hl');						
					// display tooltip on hover
					this_row.attr('data-original-title', 'Amount paid is different to balance owing');
					this_row.tooltip();

				}									

				// prefill rows
				this_row.find(".amount_paid").val(row_amount_paid.toFixed(2));
				this_row.find(".payment_date").val(payment_date);								
				this_row.find(".payment_reference").val(payment_reference);						
				
			}else{ //unchecked

				// enable
				this_row.find(".amount_paid").prop("disabled",true);
				this_row.find(".payment_date").prop("disabled",true);		
				this_row.find(".payment_reference").prop("disabled",true);	
				
				
				// remove red highlight
				this_row.removeClass('red_hl');
				this_row.removeClass('j_row_selected');

				// put back closed invoice yellow highlight on untick
				is_closed_inv = this_row.hasClass("closed_inv_row");
				if( is_closed_inv == true ){
					this_row.addClass('j_new_row_bg');
				}

				// clear
				this_row.find(".amount_paid").val("");
				this_row.find(".payment_date").val("");								
				this_row.find(".payment_reference").val("");								

			}

			compute_total();

		}else{

			obj.prop("checked",false);

			swal({
				title: "Warning!",
				text: "Bulk Amount Paid is required",
				type: "warning",
				confirmButtonClass: "btn-success",
				showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
				timer: <?php echo $this->config->item('timer') ?>
			});		
								

		}				
		
	});

	// allocated date script
	jQuery("#inv_tbl_tbody").on('change','.payment_date',function(){

		var node = jQuery(this);
		var allocation_date = node.val();
		jQuery("#payment_date").val(allocation_date);

	});

	jQuery("#inv_tbl_tbody").on('change','.amount_paid',function(){

		// dome/node
		var obj = jQuery(this);
		var this_row = obj.parents("tr:first");

		var balance = Number(this_row.find(".balance").val());
		var row_amount_paid = Number(this_row.find(".amount_paid").val());

		// remove tooltip hover
		this_row.tooltip('dispose');	

		// clear highlights
		this_row.removeClass('j_new_row_bg');
		this_row.removeClass('red_hl');	
		this_row.removeClass('j_row_selected');	
		

		if( row_amount_paid == balance ){	

			// add green highlight
			this_row.addClass('j_row_selected');					
			// remove tooltip hover			
			this_row.tooltip('dispose');				

		}else{
						
			// add red highlight			
			this_row.addClass('red_hl');	
			// display tooltip on hover			
			this_row.attr('data-original-title', 'Amount paid is different to balance owing');
			this_row.tooltip();	
		}

		compute_total();

	});

    jQuery("#open_closed_invoice_btn").click(function(){	

        var btn_txt = jQuery(this).find(".inner_icon_span").html();
        var default_btn_txt = 'Prior Invoices';
        var add_icon_src ='images/button_icons/add-button.png';
        var cancel_icon_src = 'images/button_icons/cancel-button.png';

        if( btn_txt == default_btn_txt ){
            jQuery("#close_invoices_div").show();	
            jQuery(this).find(".inner_icon_span").html("Cancel");
            jQuery(this).find(".inner_icon").attr("src",cancel_icon_src);
        }else{
            jQuery("#close_invoices_div").hide();	
            jQuery(this).find(".inner_icon_span").html(default_btn_txt);
            jQuery(this).find(".inner_icon").attr("src",add_icon_src);
        }

    });

    // clear script
	jQuery("#btn_clear").click(function(){

		var bulk_payment_details_div = jQuery("#bulk_payment_details_div");
		
		bulk_payment_details_div.find("#payment_date").val("");
		bulk_payment_details_div.find("#amount_paid").val("");
		bulk_payment_details_div.find("#type_of_payment").val("");
		bulk_payment_details_div.find("#bulk_amount").html("0");
	
		compute_total();
		
	});

    jQuery("#amount_paid").change(function(){
		
		run_bulk_amount_paid();
		
	});

	// clear script
	jQuery("#btn_clear_list").click(function(){

		list_tbl = jQuery("#invoice_table");
		list_tbl.find(".body_tr").removeClass('j_row_selected');	
		list_tbl.find(".amount_paid").val("");
		list_tbl.find(".payment_date").val("");		
		list_tbl.find(".payment_reference").val("");
		list_tbl.find(".job_chk").prop("checked",false);	

		// compute total		
		compute_total();
		
	});

	// save payment details
	jQuery("#btn_save").click(function( btn_save_event ){		

		var btn_save_dom = jQuery( this );

		var pd_row_count = jQuery(".job_chk:checked").length;
		var i = 1;
		var pd_empty = 0;
		var ap_empty = 0;
		var top_empty = 0;
		var error = '';
		
		// validation, rushing change to json if possible
		jQuery(".job_chk:checked").each(function(){

			// dom/node
			var node = jQuery(this);
			var this_row = node.parents("tr:first");			
			
			var ip_id = this_row.find(".ip_id").val();
			var payment_date = this_row.find(".payment_date").val();
			var amount_paid = this_row.find(".amount_paid").val();
			var orig_amount_paid = this_row.find(".orig_amount_paid").val();			
			var payment_reference = this_row.find(".payment_reference").val();	

			if( payment_date == '' ){
				pd_empty = 1;
				this_row.find(".payment_date").addClass('redBorder');
			}
			
			if( amount_paid == '' ){
				ap_empty = 1;
				this_row.find(".amount_paid").addClass('redBorder');
			}				
			
		});
		
		
		if( pd_empty == 1 ){
			error += "Payment Date is required\n";
		}
		
		if( ap_empty == 1 ){
			error += "Amount Paid is required\n";
		}
		
		if( top_empty == 1 ){
			error += "Type of Payment is required\n";
		}
		
		
		if( error != '' ){ // error
			swal('',error,'error');
		}else{


			var bulk_amount_paid =  Number(jQuery("#bulk_amount").html());
			var bulk_amount_alloc = Number(jQuery("#amount_alloc").html());			

			if( bulk_amount_paid == bulk_amount_alloc ){  
										
				save_bulk_payments();

			}else if( bulk_amount_paid < bulk_amount_alloc ){  				

				swal({
					title: "Warning!",
					text: "Total amount to pay cannot exceed bulk amount",
					type: "warning",						
					showConfirmButton: true,						
					confirmButtonClass: "btn-success",

				});	

			}else if( bulk_amount_paid > bulk_amount_alloc ){

				swal({
					title: "Warning!",
					text: "Bulk Amount paid exceeds the total amount to pay? Do you want to continue?",
					type: "warning",						
					showCancelButton: true,
					confirmButtonClass: "btn-success",
					confirmButtonText: "Yes, Continue",
					cancelButtonClass: "btn-danger",
					cancelButtonText: "No, Cancel!",
					closeOnConfirm: true,
					showLoaderOnConfirm: true,
					closeOnCancel: true
				},
				function(isConfirm) {

					if (isConfirm) {							  
						
						$('#load-screen').show(); 
						save_bulk_payments();						

					}

				});										

			}			
		
		}
		
		btn_save_dom.off( btn_save_event ); // prevent save button from submitting twice
		
	});



})
/** END DOC READY */
</script>
