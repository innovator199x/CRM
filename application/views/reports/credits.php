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
            'link' =>  $uri
        )
    );
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	<?php 
	if( validation_errors() ){ ?>
		<div class="alert alert-danger">
		<?php echo validation_errors(); ?>
		</div>
	<?php
	}	
	?>

    
	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">
			<?php
		$form_attr = array(
			'id' => 'jform'
		);
		echo form_open($uri,$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-md-8 columns">
					<div class="row">


                        <div class="col-md-2">
							<label for="phrase_select">From</label>
							<input type="text" name="from" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="date_from"  value="<?php echo ( $this->input->get_post('from')!='' )?$this->input->get_post('from'):date('01/m/Y'); ?>" />
                        </div>
                        
                        <div class="col-md-2">
							<label for="phrase_select">To</label>
							<input type="text" name="to" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="date_to"  value="<?php echo ( $this->input->get_post('to')!='' )?$this->input->get_post('to'):date('t/m/Y'); ?>" />
						</div>


						<div class="col-md-2">
							<label>Agency</label>
							<select name="agency_filter" class="form-control">
                                <option value="">ALL</option>
                                <?php        
                                foreach( $agency_filter->result() as $agency ){ ?>
                                    <option value="<?php echo $agency->agency_id; ?>" <?php echo ( $agency->agency_id == $this->input->get_post('agency_filter') )?'selected="selected"':null; ?>><?php echo $agency->agency_name; ?></option>
                                <?php
                                }                                
                                ?>
							</select>
                        </div>
                        
                        <div class="col-md-2">
							<label>Reason</label>
							<select name="credit_reason_filter" class="form-control">
                                <option value="">ALL</option>
                                <?php                                
                                foreach( $cred_reason_filter->result() as $cred_reas ){ ?>
                                    <option value="<?php echo $cred_reas->credit_reason_id; ?>" <?php echo ( $cred_reas->credit_reason_id == $this->input->get_post('credit_reason_filter') )?'selected="selected"':null; ?>><?php echo $cred_reas->cr_reason; ?></option>
                                <?php
                                }
                                ?>
                                <option value="-1">Other</option>
							</select>
                        </div>
                        
                        <div class="col-md-2">
							<label for="phrase_select">Phrase</label>
							<input type="text" name="search" class="form-control" placeholder="ALL" value="<?php echo $this->input->get_post('search'); ?>" />
						</div>

						<div class="col-md-1 columns">
                            <label for="phrase_select">&nbsp;</label>
							<input type="submit" name="search_submit" value="Search" class="btn">
                        </div>
                        
					</div>

                </div>

                <!-- Financial Year - 01/07/2019 - 30/6/2020 --->
                <div class="col-lg-2">
                    <label for="phrase_select">&nbsp;</label>
                    <button type="button" id="financial_year_btn" class="btn">Financial Year</button>
                </div>
                                
                                
                <!-- DL ICONS START -->
				<div class="col-md-2 columns">
                    <section class="proj-page-section float-right">
                        <div class="proj-page-attach">
                            <i class="fa fa-file-excel-o"></i>
                            <p class="name"><?php echo $title; ?></p>
                            <p>
                                <a href="<?php echo $export_link ?>" target="blank">
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
            
				<table class="table table-hover main-table duplicate_users_tbl">

					<thead>
						<tr>    
                            <th>Credit Date</th>       
                            <th>Invoice #</th>							
                            <th>Property</th>
                            <th>Charges</th>      
                            <th>Credits</th>
                            <th>Balance</th>
                            <th>Reason</th>
						</tr>
					</thead>

					<tbody>
                    <?php
                    
					$i = 1;
                    if( $this->input->get_post('search_submit') == 'Search' && $list->num_rows() > 0 ){
                        foreach( $list->result() as $row ){ 

                              // append checkdigit to job id for new invoice number
                            $check_digit = $this->system_model->getCheckDigit(trim($row->jid));
                            $bpay_ref_code = "{$row->jid}{$check_digit}";                         

                            ?>
                            <tr>
                                <td>
                                    <?php echo ( $this->system_model->isDateNotEmpty($row->credit_date) == true )?$this->system_model->formatDate($row->credit_date,'d/m/Y'):''; ?>
                                </td>	
                                <td>
                                    <a href="<?php echo $this->config->item('crm_link') ?>/view_job_details.php?id=<?php echo $row->jid; ?>"><?php echo $bpay_ref_code; ?></a>
                                </td>   
                                <td>
                                    <a href="<?php echo $this->config->item('crm_link') ?>/view_property_details.php?id=<?php echo $row->property_id; ?>">
                                        <?php echo "{$row->p_address_1} {$row->p_address_2}, {$row->p_address_3}"; ?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo ( $row->invoice_amount > 0 )?'$'.number_format($row->invoice_amount,2):null; ?>
                                </td>
                                <td class="txt_red">
                                    <?php echo ( $row->credit_paid > 0 )?'-$'.number_format($row->credit_paid,2):null; ?>
                                </td>
                                <td>
                                    <?php echo ( $row->invoice_balance > 0 )?'$'.number_format($row->invoice_balance,2):null; ?>
                                </td>
                                <td>
                                    <?php echo ( $row->credit_reason == -1 )?'Other':$row->cr_reason; ?>
                                </td>                                            
                            </tr>
                        <?php
                        $i++;
                        }

                    }else{ ?>
                        <tr><td colspan="7">Press Search to display data</td></tr>
                    <?php
                    }   
                                          
                    ?>                    
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>TOTAL</th>
                            <th></th>			
                            <th></th>	
                            <th>$<?php echo number_format($tot_inv_amt,2); ?></th>
                            <th><?php echo ( $tot_inv_cred > 0 )?'<span class="txt_red">-$'.number_format($tot_inv_cred,2)."</span>":'$0.00'; ?></th>	
                            <th></th>
                            <th></th>		
                        </tr>
                    </tfoot>

				</table>		

			</div>

			<nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
			<div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
			

		</div>
	</section>

</div>


<!-- Fancybox START -->

<!-- ABOUT TEXT -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
    This page shows all credits based on date range selected
	</p>
    <pre>
<code>SELECT `j`.`id` AS `jid`, `j`.`date` AS `jdate`, `j`.`invoice_amount`, `j`.`invoice_payments`, `j`.`invoice_credits`, `j`.`invoice_balance`, `p`.`property_id`, `p`.`address_1` AS `p_address_1`, `p`.`address_2` AS `p_address_2`, `p`.`address_3` AS `p_address_3`, `p`.`state` AS `p_state`, `p`.`postcode` AS `p_postcode`, `a`.`agency_id`, `a`.`agency_name`, `inv_cred`.`credit_paid`, `inv_cred`.`credit_reason`, `inv_cred`.`credit_date`, `cred_reas`.`credit_reason_id`, `cred_reas`.`reason` AS `cr_reason`
FROM `jobs` AS `j`
LEFT JOIN `property` AS `p` ON j.`property_id` = p.`property_id`
LEFT JOIN `agency` AS `a` ON  p.`agency_id` = a.`agency_id`
INNER JOIN `invoice_credits` AS `inv_cred` ON j.`id` = inv_cred.`job_id`
LEFT JOIN `credit_reason` AS `cred_reas` ON inv_cred.`credit_reason` = cred_reas.`credit_reason_id`
WHERE `a`.`country_id` = <?php echo COUNTRY ?> 
AND 
`j`.`invoice_amount` >0
AND `j`.`status` = 'Completed'
AND j.`invoice_credits` > 0
AND 
(`inv_cred`.`credit_date` BETWEEN '$date_from' AND '$date_to' )
ORDER BY `j`.`date` ASC
LIMIT 50</code>
    </pre>

</div>




<style>
.fancybox-content {
    width: auto;
}
</style>

<!-- Fancybox END -->
<script>
jQuery(document).ready(function(){

    // Financial Year - 01/07/2019 - 30/06/2020 
    jQuery("#financial_year_btn").click(function(){

        jQuery("#date_from").val("01/07/2019");
        jQuery("#date_to").val("30/06/2020 ");

    });

});
</script>