<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/jobs/platform_invoicing"
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
		echo form_open('/jobs/platform_invoicing',$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-lg-10 col-md-12 columns">
					<div class="row">

						<div class="col-mdd-3">
							<label for="date_select">Date</label>
							<input name="date_filter" placeholder="ALL" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $this->input->get_post('date_filter'); ?>">
						</div>

						<div class="col-mdd-3">
							<label for="phrase_select">Phrase</label>
							<input type="text" placeholder="ALL" name="search_filter" class="form-control"  value="<?php echo $this->input->get_post('search_filter'); ?>" />
						</div>

						<div class="col-mdd-5">
							<label for="jobtype_select">Maintenance Program:</label>
							<select name="maint_prog_filter" id="maint_prog_filter" class="form-control">
								<option value="">ALL</option>							
							</select>
							<div class="mini_loader"></div>
						</div>

						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<button type="submit" class="btn btn-inline" id="btn_search">Search</button>
						</div>

						<div class='fl-left' style="float:left; padding-top: 28px; padding-left: 30px;">
							Login: <a style="margin:0!important;" target="__blank" href="https://my.mmgr.com.au/index.php/site/login">Maintenance Manager</a>
						</div>

					</div>
				</div>

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
							<th>Software</th>
							<th>Agency</th>
							<th>MITM/Work Order</th>
							<th>Invoice Number</th>
							<th>Address</th>
							<th>Inv. Amount</th>
							<th>Invoice/Cert</th>
							<th class="leftGreyBorder">Invoice</th>
							<th class="leftGreyBorder">Quote Amount</th>
							<th>QLD Upgrade Quote</th>
							<th>Needs Processing</th>
							<th>
								<div class="checkbox" style="margin:0;">
									<input name="chk_all" type="checkbox" id="check-all">
									<label for="check-all">&nbsp;</label>
								</div>
							</th>
						</tr>
					</thead>

					<tbody>
						<?php
						if($lists->num_rows()>0){
						$i = 0;
						
						/*
						date('Y-m-d',strtotime($row['mm_need_proc_inv_emailed'])) == date('Y-m-d') || 
						date('Y-m-d',strtotime($row['client_emailed'])) == date('Y-m-d')
						*/

						foreach($lists->result_array() as $list_item): 	
						$params = array(
							'sel_query' => "sr.subregion_name as postcode_region_name, sr.sub_region_id as postcode_region_id",
							'postcode' => $list_item['p_postcode'],
						);
						$getRegion = $this->system_model->get_postcodes($params)->row();

						// grey alternation color
						$row_color = ($i%2==0)?"style='background-color:#eeeeee;'":"";
						

						?>
						<tr class="body_tr jalign_left" <?php echo $row_color; ?>>
							
							<td><?php echo ($this->customlib->isDateNotEmpty($list_item['jdate'])==true)?$this->customlib->formatDate($list_item['jdate'],'d/m/Y'):''; ?></td>
							<td>
								<?php echo ( $this->customlib->isDHAagenciesV2($list_item['franchise_groups_id'])==true )?'DHA':$list_item['m_name'];  ?>
								<input type="hidden" class="main_prog" value="<?php echo $list_item['m_name']; ?>" />
							</td>
							<td>
								<?php /*
								<a href="/view_agency_details.php?id=<?php echo $list_item['agency_id']; ?>"><?php echo $list_item['agency_name']; ?></a>
								*/
								echo $this->gherxlib->crmLink('vad',$list_item['a_id'],$list_item['agency_name']);
								?>

							</td>
							<td><?php echo $list_item['work_order']; ?></td>
							<td>
								<?php /*
								<a href="/view_job_details.php?id=<?php echo $list_item['jid']; ?>"><?php echo $this->customlib->getInvoiceNumber($list_item['jid']); ?></a>
								*/?>

								<?php
									$invoiceNum = $this->customlib->getInvoiceNumber($list_item['jid']);
									 echo $this->gherxlib->crmLink('vjd',$list_item['jid'],$invoiceNum);
								?>

							</td>
							
							
							<td>
							<?php
							/*
							<a href="/view_property_details.php?id=<?php echo $list_item['property_id']; ?>"><?php echo "{$list_item['p_address_1']} {$list_item['p_address_2']}, {$list_item['p_address_3']}"; ?></a>
							*/
							?>
							<?php
								$prop_address = $list_item['p_address_1']." ".$list_item['p_address_2'].", ".$list_item['p_address_3'];
								echo $this->gherxlib->crmLink('vpd',$list_item['property_id'],$prop_address);
							?>
							</td>
							
							
							<td>$<?php echo number_format($this->customlib->getInvoiceTotal($list_item['jid']),2); ?></td>
							
							<!-- combined -->
							<td>
								<?php	
								if( $list_item['ts_completed'] == 1 ){ ?>
									<div <?php echo ( $this->customlib->isDHAagenciesV2($list_item['franchise_groups_id'])==true )?'style="display:none;"':''; ?>>
										<a target="blank" href="<?php echo base_url(); ?>pdf/view_invoice/?job_id=<?php echo $list_item['jid']; ?>&page=invoicing"><img src="/images/pdf.png" /></a>
										<a target="blank" style="margin-left: 10px; float: right;" href="<?php echo base_url(); ?>pdf/view_invoice/?job_id=<?php echo $list_item['jid']; ?>&page=invoicing&output_type=D"><img src="/images/download_icon.png" /></a>
										<img src="/images/email_green.png" data-job_id="<?php echo $list_item['jid']; ?>" data-pdf_type="invoice_cert" class="btn_email_agency <?php echo ( date('Y-m-d',strtotime($list_item['mm_need_proc_inv_emailed'])) == date('Y-m-d') )?'fadeIt':''; ?>" />
									</div>
								<?php
								}								
								?>							
							</td>
							
							<!-- invoice -->
							<td class="leftGreyBorder">
								<div <?php echo ( $this->customlib->isDHAagenciesV2($list_item['franchise_groups_id'])==true )?'style="display:none;"':''; ?>>
									<a target="blank" href="<?php echo base_url(); ?>pdf/view_invoice/?job_id=<?php echo $list_item['jid']; ?>&page=invoicing"><img src="/images/pdf.png" /></a>
									<a target="blank" style="margin-left: 10px; float: right;" href="<?php echo base_url(); ?>pdf/view_invoice/?job_id=<?php echo $list_item['jid']; ?>&page=invoicing&output_type=D"><img src="/images/download_icon.png" /></a>
									<img src="/images/email_green.png" data-job_id="<?php echo $list_item['jid']; ?>" data-pdf_type="invoice" class="btn_email_agency <?php echo ( date('Y-m-d',strtotime($list_item['mm_need_proc_inv_emailed'])) == date('Y-m-d') )?'fadeIt':''; ?>" />
									
								</div>
							</td>
							
							<?php
							if( $list_item['p_state']=='QLD' ){

								if( $list_item['prop_upgraded_to_ic_sa'] == 1 ){ ?>
									<td class="leftGreyBorder">Upgraded</td>
									<td>N/A</td>
								<?php
								}else{ ?>
								
								
									<!-- THIS IS HARDCODED; needs to be changed to dynamic sooner -->
									<?php
									$quote_qty = $list_item['qld_new_leg_alarm_num'];
									$price_240vrf = $this->customlib->get240vRfAgencyAlarm($list_item['a_id']);
									$quote_price = ( $price_240vrf > 0 )?$price_240vrf:200;
									$quote_total = $quote_price*$quote_qty;
									
									// QUOTE
									if( $quote_total > 0 ){ ?>
										<td class="leftGreyBorder">	
											<?php echo "$".number_format($quote_total,2);

												$has_brooks_quote = false;
												$has_cavius_quote = false;

												$agency_id = $list_item['a_id'];
	
												//quote pdf
											?>			
										</td>										
										<td>
											<?php 
												$this->db->select('COUNT(`agency_alarm_id`) AS agen_al_count');
												$this->db->from('agency_alarms');
												$this->db->where('agency_id', $agency_id);
												$this->db->where('alarm_pwr_id', 10);
												$query = $this->db->get();
												$result = $query->row();
												$check_brooks = $result->agen_al_count;

												if( $check_brooks > 0 ){
													$qt = "brooks";
													$qt_brooks = 1;
												}	

												if($qt_brooks == 1){
											?>
												<div <?php echo ( $this->customlib->isDHAagenciesV2($list_item['franchise_groups_id'])==true )?'style="display:none;"':''; ?>>
													<!--<a target="blank" href="view_quote.php?job_id=<?php //echo $list_item['jid']; ?>"><img src="/images/pdf.png" /></a> -->
													<!--<a target="blank" style="margin-left: 10px;" href="view_quote.php?job_id=<?php //echo $list_item['jid']; ?>&output_type=D"><img src="/images/download_icon.png" /></a> -->
													<a target="blank" href="<?php echo base_url(); ?>pdf/view_quote/?job_id=<?php echo $list_item['jid']; ?>&page=quote&qt=brooks"><img src="/images/pdf.png" /></a>
													<a target="blank" style="margin-left: 10px;" href="<?php echo base_url(); ?>pdf/view_quote/?job_id=<?php echo $list_item['jid']; ?>&page=quote&qt=brooks&output_type=D"><img src="/images/download_icon.png" /></a>
													<img src="/images/email_green.png" data-job_id="<?php echo $list_item['jid']; ?>" data-pdf_type="quote" data-qt_type="brooks" class="btn_email_agency <?php echo ( date('Y-m-d',strtotime($list_item['qld_upgrade_quote_emailed'])) == date('Y-m-d') )?'fadeIt':''; ?>" />
												</div>
											<?php
												}

												$this->db->select('COUNT(`agency_alarm_id`) AS agen_al_count');
												$this->db->from('agency_alarms');
												$this->db->where('agency_id', $agency_id);
												$this->db->where('alarm_pwr_id', 22);
												$query1 = $this->db->get();
												$result1 = $query1->row();
												$check_emerald = $result1->agen_al_count;
												
												if( $check_emerald > 0 ){
													$qt = "emerald";
													$qt_emerald = 1;
												}

												if($qt_emerald == 1){
											?>
												<div <?php echo ( $this->customlib->isDHAagenciesV2($list_item['franchise_groups_id'])==true )?'style="display:none;"':''; ?>>
													<!--<a target="blank" href="view_quote.php?job_id=<?php //echo $list_item['jid']; ?>"><img src="/images/pdf.png" /></a> -->
													<!--<a target="blank" style="margin-left: 10px;" href="view_quote.php?job_id=<?php //echo $list_item['jid']; ?>&output_type=D"><img src="/images/download_icon.png" /></a> -->
													<a target="blank" href="<?php echo base_url(); ?>pdf/view_quote/?job_id=<?php echo $list_item['jid']; ?>&page=quote&qt=emerald"><img src="/images/pdf.png" /></a>
													<a target="blank" style="margin-left: 10px;" href="<?php echo base_url(); ?>pdf/view_quote/?job_id=<?php echo $list_item['jid']; ?>&page=quote&qt=emerald&output_type=D"><img src="/images/download_icon.png" /></a>
													<img src="/images/email_green.png" data-job_id="<?php echo $list_item['jid']; ?>" data-pdf_type="quote" data-qt_type="emerald" class="btn_email_agency <?php echo ( date('Y-m-d',strtotime($list_item['qld_upgrade_quote_emailed'])) == date('Y-m-d') )?'fadeIt':''; ?>" />
												</div>
											<?php
												}
											?>
										</td>
									<?php	
									}else{ ?>
										<td class="leftGreyBorder">N/A</td>
										<td>N/A</td>
									<?php
									}
									?>
															
									
								
								<?php
								}

							?>
								
							
							<?php
							}else{ ?>
							
								<td class="leftGreyBorder">N/A</td>
								<td>N/A</td>
							
							<?php
							}
							?>
							
							
							
							<td>

								<div class="checkbox" style="margin:0;">
									<input type="checkbox" id="nd_<?=$list_item['jid']?>" class="np_chk" <?php echo ($list_item['dha_need_processing']==1)?'checked="checked"':''; ?> value="<?php echo $list_item['jid']; ?>" />
									<label for="nd_<?=$list_item['jid']?>">&nbsp;</label>
								</div>
							
							</td>

							<td>
								
								<div class="checkbox" style="margin:0;">
									<input type="checkbox" id="nd2_<?=$list_item['jid']?>" class="np_chk2" value="<?php echo $list_item['jid']; ?>" attra="<?=$list_item['dha_need_processing']?>" attrb="<?=$list_item['m_name']?>" data-invoice-cert="<?=$list_item['ts_completed']?>" data-quote="<?=$quote_total?>" />
									<label for="nd2_<?=$list_item['jid']?>">&nbsp;</label>
								</div>
							
							</td>
							
									
						</tr>

					<?php $i++; endforeach; ?>
					<?php
						}else{
							echo "<tr><td colspan='12'>No Data</td></tr>";
						}
							?>
					</tbody>

				</table>                
				<div id="mbm_box" class="text-right">
					<div class="gbox_main">
						<div class="gbox">
							<button id="send_clear_btn" type="button" class="btn">Send and Clear</button>
						</div>
					</div>
				</div>
			
			</div>

			<nav class="text-center">
				<?php echo $pagination; ?>
			</nav>

			<div class="pagi_count text-center">
				<?php echo $pagi_count; ?>
			</div>

		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>This page shows any jobs that we have successfully completed but are under the maintenance program “Maintenance Manager, Our Tradie etc” and require us to upload into their system. Once processed, the jobs will no longer appear on this page.
</p>

</div>
<!-- Fancybox END -->

<style>
	.btn_email_agency {
    cursor: pointer;
    margin-left: 5px;
	}
	.fadeIt {
		opacity: 0.5;
	}
</style>

<script>

// maintenance
function run_ajax_maint_prog_filter(){

var json_data = <?php echo $maintenance_filter_json; ?>;
var searched_val = '<?php echo $this->input->get_post('maint_prog_filter'); ?>';

jQuery('#maint_prog_filter').next('.mini_loader').show();
jQuery.ajax({
	type: "POST",
		url: "/sys/header_filters",
		data: { 
			rf_class: 'jobs',
			header_filter_type: 'maint_prog',
			json_data: json_data,
			searched_val: searched_val
		}
	}).done(function( ret ){	
		jQuery('#maint_prog_filter').next('.mini_loader').hide();
		$('#maint_prog_filter').append(ret);
	});
			
}

jQuery(document).ready(function(){

	// run headler filter ajax
	run_ajax_maint_prog_filter();

	// move/assign to maps 
	jQuery("#send_clear_btn").on('click',function(){
		
		var job_id = new Array();
		var attra = new Array();
		var attrb = new Array();
		var attrc = new Array();
		var attrd = new Array();
		
		//push job_id array
		jQuery(".np_chk2:checked").each(function(){
			job_id.push(jQuery(this).val());
			attra.push(jQuery(this).attr("attra"));
			attrb.push(jQuery(this).attr("attrb"));
			attrc.push(jQuery(this).attr("data-invoice-cert"));
			attrd.push(jQuery(this).attr("data-quote"));
		});

		$('#load-screen').show(); //show loader
		jQuery.ajax({
			type: "POST",
			url: "/jobs_ajax/dpc_mod/send_and_clear",
			data: { 
				job_id: job_id,
				attra : attra,
				attrb : attrb,
				data_invoice_cert : attrc,
				data_quote : attrd
			}
		}).done(function( ret ){
			$('#load-screen').hide(); //hide loader
			return false;
			
			swal({
				title:"Success!",
				text: "clear success",
				type: "success",
				showCancelButton: false,
				confirmButtonText: "OK",
				closeOnConfirm: false,

			},function(isConfirm){
			if(isConfirm){ 
				location.reload();
				}
			});
				
		});	
				
	});

	$('#check-all').on('change',function(){
		var obj = $(this);
		var isChecked = obj.is(':checked');
		var divbutton = $('#mbm_box');
		if(isChecked){
			divbutton.show();
			$('.np_chk2').prop('checked',true);
		}else{
			divbutton.hide();
			$('.np_chk2').prop('checked',false);
		}
	})

	$('.np_chk2').on('change',function(){
		var obj = $(this);
		var isLength = $('.np_chk2:checked').length;
		var divbutton = $('#mbm_box');
		if(isLength>0){
			divbutton.show();
		}else{
			divbutton.hide();
		}
	})
	
	jQuery(".btn_email_agency").click(function(){
		
		var job_id = jQuery(this).attr("data-job_id");
		var pdf_type = jQuery(this).attr("data-pdf_type");
		var qt_type = jQuery(this).attr("data-qt_type");

		swal({
	      title: "Confirm",
	      text: "Are you sure you sure you want to continue?",
	      type: "info",
	      showCancelButton: true,
	      showLoaderOnConfirm: true,
	    }, function () {
			$.ajax({
	            type: "POST",
	            url: '<?php echo base_url(); ?>jobs_ajax/dpc_mod/ajax_dha_precomp_email_agency_accounts',
				data: { 
					job_id: job_id,
					pdf_type: pdf_type,
					qt_type: qt_type
				}
			}).done(function(res){

				if (res == 1) {
        			location.reload();
				}else {
					swal('Email not sent','Something went wrong, kindly contact dev team.','error');
					return false;
				}
			});	
	    });	
				
	});
	
	
	
	
	jQuery(".np_chk").change(function(){
		
		var state = jQuery(this).prop('checked');
		var job_id = jQuery(this).val();
		var main_prog = jQuery(this).parents("tr:first").find('.main_prog').val();
		
		var dha_need_processing = (state==true)?1:0;

		$.ajax({
            type: "POST",
            url: '<?php echo base_url(); ?>jobs_ajax/dpc_mod/ajax_update_dha_need_processing',
			data: { 
				job_id: job_id,
				dha_need_processing: dha_need_processing,
				main_prog: main_prog
			}
		}).done(function(res){
			jQuery("#btn_search").click();
		});	

	});
	
});
</script>