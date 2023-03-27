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
			'link' => "/credit/credit_request_summary"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);

	$export_links_params_arr = array(
		'from_filter' => $this->input->get_post('from_filter'),
		'to_filter' => $this->input->get_post('to_filter'),
		'req_by_filter' => $this->input->get_post('req_by_filter'),
		'result_filter' => $this->input->get_post('result_filter'),
		'agency_filter' => $this->input->get_post('agency_filter'),
		'search_filter' => $this->input->get_post('search_filter')
	);
	$export_link_params = '/credit/credit_request_summary/?export=1&'.http_build_query($export_links_params_arr);
	?>

	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">
			<?php
		$form_attr = array(
			'id' => 'jform'
		);
		echo form_open('credit/credit_request_summary',$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-md-8 columns">
					<div class="row">

                        <div class="col-mdd-3">
							<label>From</label>
							<input name="from_filter" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text" placeholder="ALL" value="<?php echo $this->input->get_post('from_filter'); ?>">
						</div>

						<div class="col-mdd-3">
							<label>To</label>
							<input name="to_filter" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text" placeholder="ALL" value="<?php echo $this->input->get_post('to_filter'); ?>">
						</div>

						<div class="col-mdd-3">
							<label>Requested By</label>
							<select id="req_by_filter" name="req_by_filter" class="form-control">
								<option value="">ALL</option>								
								<?php
								foreach( $rb_filter_dp->result() as $rb ){ ?>
									<option value="<?php echo $rb->StaffID; ?>" <?php echo ( $rb->StaffID == $this->input->get_post('req_by_filter') )?'selected="selected"':null; ?>><?php echo $this->system_model->formatStaffName($rb->FirstName,$rb->LastName); ?></option>
								<?php
								}
								?>
							</select>
							<div class="mini_loader"></div>
						</div>

						<div class="col-mdd-3">
							<label>Result</label>
							<select id="result_filter" name="result_filter" class="form-control field_g2">
								<option value="pending" <?php echo ( $this->input->get_post('result_filter')=='pending' )?'selected="selected"':''; ?>>Pending</option>
								<option value="1" <?php echo ( $this->input->get_post('result_filter')==1 )?'selected="selected"':''; ?>>Accepted</option>	
								<option value="0" <?php echo ( is_numeric($this->input->get_post('result_filter')) && $this->input->get_post('result_filter')==0 )?'selected="selected"':''; ?>>Declined</option>
								<option value="2" <?php echo ( is_numeric($this->input->get_post('result_filter')) && $this->input->get_post('result_filter')==2 )?'selected="selected"':''; ?>>More info needed</option>
								<option value="3" <?php echo ( is_numeric($this->input->get_post('result_filter')) && $this->input->get_post('result_filter')==3 )?'selected="selected"':''; ?>>Not Applicable</option>
								<option value="ALL" <?php echo ( $this->input->get_post('result_filter')=='ALL' )?'selected="selected"':''; ?>>ALL</option>
							</select>
							<div class="mini_loader"></div>
						</div>

						<div class="col-mdd-3">
							<label>Agency</label>
							<select id="agency_filter" name="agency_filter" class="form-control">
								<option value="">ALL</option>
								<?php
								foreach( $agency_filter_dp->result() as $agency ){ ?>
									<option value="<?php echo $agency->agency_id; ?>" <?php echo ( $agency->agency_id == $this->input->get_post('agency_filter') )?'selected="selected"':null; ?>><?php echo $agency->agency_name; ?></option>
								<?php
								}
								?>
							</select>
							<div class="mini_loader"></div>
						</div>	
						
						<div class="col-mdd-3">
							<label for="search">Phrase</label>
							<input type="text" name="search_filter" class="form-control" placeholder="Job ID" value="<?php echo $this->input->get_post('search_filter'); ?>" />
						</div>

						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<input type="submit" name="search_submit" value="Search" class="btn">
						</div>
						
					</div>

				</div>

				<div class="col-md-4 columns">
                    <section class="proj-page-section float-right">
                        <div class="proj-page-attach">
                            <i class="fa fa-file-excel-o"></i>
                            <p class="name">Credit Request</p>
                            <p>
								<a href="<?php echo $export_link_params ?>" target="blank">
                                        Export
                                </a>
                            </p>
                        </div>
                    </section>
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
							<th style="width:132px;">Date of Request</th>
							<th style="width:80px;">Invoice #</th>
							<th style="width:71px;">Amount</th>
							<th style="width:110px;">CR Requested</th>
							<th style="width:238px">Agency</th>
							<th style="width:116px">Requested By</th>
							<th style="width:217px">Reason</th>
							<th style="width:72px">Result</th>
							<th style="width:240px">Comments</th>
							<th style="width:130px">Date Processed</th>
							<th style="width:76px">Credited</th>
							<th>Who</th>
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
                        foreach( $lists->result() as $row ){
                        ?>
                        <tr>
                            <td>
                                <a href="/credit/request_details/<?php echo $row->credit_request_id; ?>?type=credit">
                                    <?php echo ( $this->system_model->isDateNotEmpty($row->date_of_request) )?date('d/m/Y',strtotime($row->date_of_request)):null; ?>
                                </a>
                            </td>
                            <td>
                                <?php
                                // append checkdigit to job id for new invoice number
                                $check_digit = $this->system_model->getCheckDigit(trim($row->job_id));
                                $bpay_ref_code = "{$row->job_id}{$check_digit}";
                                ?>
                                <a href="<?php echo $this->config->item('crm_link'); ?>/view_job_details.php?id=<?php echo $row->job_id; ?>">
                                    <?php echo $bpay_ref_code; ?> 
                                </a>
                            </td>
                            <td>
                                $<?php echo number_format($row->invoice_amount,2); ?> 
							</td>
							<td>
                                $<?php echo number_format($row->adjustment_val_req,2); ?> 
							</td>
							<td class="<?php echo ( $row->priority == 1 )?'j_bold':null; ?>">
                                <a href="/agency/view_agency_details/<?php echo $row->agency_id; ?>">
									<?php echo $row->agency_name." ".( ( $row->priority > 0 )?' ('.$row->abbreviation.')':null ); ?>
                                </a>
                            </td>
                            <td>
                                <?php echo $this->system_model->formatStaffName($row->rb_fname,$row->rb_lname); ?>
                            </td>
                            <td>
                                <?php echo $row->cr_reason; ?>
                            </td>
                            <td>
                                <?php 
                                if( $row->result == 1 ){
                                    echo '<label class="approvedHLstatus">Accepted</label>';
                                }else if( is_numeric($row->result) && $row->result == 0 ){
                                    echo '<label class="declinedHLstatus">Declined</label>';
                                }else if( is_numeric($row->result) && $row->result == 2 ){
                                    echo '<label class="more_infoHLstatus">More info needed</label>';
                                }else{
                                    echo '<label class="pendingHLstatus">Pending</label>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php echo  $row->cr_comments; ?>
                            </td>
                            <td>
                                <?php echo ( $this->system_model->isDateNotEmpty($row->date_processed) )?date('d/m/Y',strtotime($row->date_processed)):null; ?>
                            </td>	
                            <td>
                                <?php echo  ( $row->amount_credited>0 )?'$'.$row->amount_credited:null; ?>
                            </td>
                            <td>
                                <?php echo $this->system_model->formatStaffName($row->who_fname,$row->who_lname); ?>
							</td>
							
							<td>
							<div class="checkbox">
									<input class="chk_credit" name="chk_credit[]" type="checkbox" id="check-<?php echo $row->credit_request_id ?>" data-credit_request_id="<?php echo $row->credit_request_id; ?>" value="<?php echo $row->credit_request_id; ?>">
									<label for="check-<?php echo $row->credit_request_id ?>">&nbsp;</label>
								</div>
							</td>
                        </tr>
                        <?php 
                        } 
                        ?>
					</tbody>

					<tfoot>
						<td colspan="2">
							<strong>TOTAL</strong>
						</td>
						<td>$<?php echo number_format($total_sum->tot_inv_amount,2); ?></td>
						<td colspan="7"></td>
						<td>$<?php echo number_format($total_sum->tot_am_cred,2); ?></td>
						<td></td>
						<td></td>
					</tfoot>

				</table>
						
			</div>

			<div class="row">
				<div class="col-md-6 columns">
					<a href="/credit/credit_request">
						<button type="button" class="btn">Create Credit Request</button>
					</a>
				</div>

				<div class="col-md-6 columns">
					<div id="mbm_box" class="text-right">
						<div class="gbox_main" style="margin-bottom:0px;">
							<div class="gbox">
								<div class="input-group">
									<div class="input-group-prepend"><span class="input-group-text">$</span></div>
									<input type="text" class="form-control" id="amount_credited" placeholder="Amount Credited">
								</div>
							</div>
							<div class="gbox">
								<button id="btn_submit" type="button" class="btn">Submit</button>
							</div>
						</div>
					</div>

				</div>
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
	Displays all credit requests.
	</p>

	<pre>
<code>SELECT `cr`.`credit_request_id`, `cr`.`job_id`, `cr`.`date_of_request`, `cr`.`requested_by`, `cr`.`reason` AS `cr_reason`, `cr`.`result`, `cr`.`comments` AS `cr_comments`, `cr`.`date_processed`, `cr`.`amount_credited`, `cr`.`who`, `cr`.`adjustment_val_req`, `j`.`invoice_amount`, `a`.`agency_id`, `a`.`agency_name`, `rb`.`StaffID` AS `rb_staff_id`, `rb`.`FirstName` AS `rb_fname`, `rb`.`LastName` AS `rb_lname`, `who`.`StaffID` AS `who_staff_id`, `who`.`FirstName` AS `who_fname`, `who`.`LastName` AS `who_lname`
FROM `credit_requests` AS `cr`
LEFT JOIN `jobs` AS `j` ON cr.`job_id` = j.`id`
LEFT JOIN `property` AS `p` ON j.`property_id` = p.`property_id`
LEFT JOIN `agency` AS `a` ON p.`agency_id` = a.`agency_id`
LEFT JOIN `staff_accounts` AS `rb` ON cr.`requested_by` = rb.`StaffID`
LEFT JOIN `staff_accounts` AS `who` ON cr.`who` = who.`StaffID`
WHERE `cr`.`deleted` = 0
AND `cr`.`active` = 1
AND `cr`.`country_id` = <?php echo COUNTRY ?> 
AND `cr`.`result` IS NULL
AND (`cr`.`adjustment_type` != 1 OR `cr`.`adjustment_type` IS NULL)
ORDER BY `cr`.`date_of_request` DESC
LIMIT 50</code>
	</pre>

</div>
<!-- Fancybox END -->

<style>
.rf_select{
	font-weight: bold;
}

.approvedHLstatus {
    color: green;
    font-weight: bold;
}
.pendingHLstatus {
    color: red;
    font-style: italic;
}
.declinedHLstatus {
    color: red;
	font-weight: bold;
}
.more_infoHLstatus {
    color: #f37b53;
	font-weight: bold;
}
.flatpickr{
	width:100%;
}
</style>
<script>
jQuery(document).ready(function(){

	//success/error message sweel alert pop  start
    <?php 
    if( $this->session->flashdata('credit_request_deleted') &&  $this->session->flashdata('credit_request_deleted') == 1 ){ ?>
        swal({
            title: "Success!",
            text: "Adjustment Request Deleted",
            type: "success",
            confirmButtonClass: "btn-success",
			showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
			timer: <?php echo $this->config->item('timer') ?>
        });
    <?php 
    }
    ?>

	//success/error message sweel alert pop  start
    <?php 
    if( $this->session->flashdata('credit_request_updated') &&  $this->session->flashdata('credit_request_updated') == 1 ){ ?>
        swal({
            title: "Success!",
            text: "Adjustment Request Updated",
            type: "success",
            confirmButtonClass: "btn-success",
			showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
			timer: <?php echo $this->config->item('timer') ?>
        });
    <?php 
    }
    ?>

		$('#check-all').on('change',function(){
			var obj = $(this);
			var isChecked = obj.is(':checked');
			var divbutton = $('#mbm_box');
			if(isChecked){
				divbutton.show();
				$('.chk_credit').prop('checked',true);
			}else{
				divbutton.hide();
				$('.chk_credit').prop('checked',false);
			}
		})

		$('.chk_credit').on('change',function(){
			var obj = $(this);
			var isLength = $('.chk_credit:checked').length;
			var divbutton = $('#mbm_box');
			if(isLength>0){
				divbutton.show();
			}else{
				divbutton.hide();
			}
		})

		jQuery("#btn_submit").on('click',function(){
			
			var credit_request_id = new Array();
			var amount_credited = jQuery("#amount_credited").val();

			var err = "";

			if(amount_credited==""){
				err +="Amount Credited must not be empty\n";
			}

			if(isNaN(amount_credited / 1) == true){
				err +="Please input a number, without a dollar sign.\n";
			}

			if(err!=""){
				swal('',err,'error');
				return false;
			}
			
			jQuery(".chk_credit:checked").each(function(){
				credit_request_id.push(jQuery(this).val());
			});

			$('#load-screen').show(); //show loader
			jQuery.ajax({
				type: "POST",
				url: "/credit/ajax_credit_request_summary_update_amount_credited",
				data: { 
					credit_request_id: credit_request_id,
					amount_credited: amount_credited
				}
			}).done(function( ret ){
				$('#load-screen').hide(); //hide loader
				swal({
					title:"Success!",
					text: "Request Success",
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
	
});
</script>