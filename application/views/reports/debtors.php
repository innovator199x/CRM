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
		echo form_open("/reports/debtors/?is_search=1",$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-md-8 columns">
					<div class="row">


						<div class="col-md-3">
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
							<label for="phrase_select">Phrase</label>
							<input type="text" name="search_agency" class="form-control" placeholder="ALL" value="<?php echo $this->input->get_post('search_agency'); ?>" />
						</div>

						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<input type="submit" name="search_submit" value="Search" class="btn">
                        </div>
                        
					</div>

                </div>

                <?php
                if( $this->input->get_post('search_submit') == 'Search' ){ ?>

                    <div class="col-md-4 columns">

                        <div class="col-md-12 columns text-right">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            
                            <section class="proj-page-section pdf_header_section">
                                <div class="proj-page-attach pdf_header_div">
                                    <i class="font-icon font-icon-pdf"></i>
                                    <p class="name"><?php echo $title; ?></p>
                                    <p>
                                        <a href="
                                            <?php echo $uri; ?>/?pdf=1&output_type=I&agency_filter=<?php echo $this->input->get_post('agency_filter'); ?>&search_agency=<?php echo $this->input->get_post('search_agency'); ?>"                                            
                                            target="blank"
                                        >
                                            View
                                        </a>

                                        <a href="<?php echo $uri; ?>/?pdf=1&output_type=D&agency_filter=<?php echo $this->input->get_post('agency_filter'); ?>&search_agency=<?php echo $this->input->get_post('search_agency'); ?>">
                                            Download
                                        </a>        
                                    </p>
                                </div>
                            </section>
                        </div>                        

                    </div>

                <?php
                }
                ?>
                
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
                            <th>Agency Name</th>       
                            <th>0-30 days (Not Overdue)</th>							
                            <th>31-60 days <span class="txt_red">OVERDUE</span></th>
                            <th>61-90 days <span class="txt_red">OVERDUE</span></th>
                            <th>91+ days <span class="txt_red">OVERDUE</span></th>
                            <th>Total Amount Due</th>
						</tr>
					</thead>

					<tbody>
					<?php
					$i = 1;
                    $chk_count = 1;

                    // total
                    $not_overdue_tot = 0;
                    $overdue_31_to_60_tot = 0;
                    $overdue_61_to_90_tot = 0;
                    $overdue_91_more_tot = 0;
                    $invoice_balance_tot = 0;

                    // percentage
                    $not_overdue_perc = 0;
                    $overdue_31_to_60_perc = 0;
                    $overdue_61_to_90_perc = 0;
                    $overdue_91_more_perc = 0;
                    $total_amount_due_perc = 0;

                    if( $this->input->get_post('search_submit') == 'Search' || $this->input->get_post('is_search')==1 ){
                        foreach( $list->result() as $row ){ ?>
                            <tr>
                                <td>
                                    <?php echo $this->gherxlib->crmLink('vad',$row->agency_id,$row->agency_name,'',$row->priority); ?>
                                </td>	
                                <td>
                                    <?php
                                    $having = "DateDiff <= 30";
                                    $job_params = array(
                                        'agency_id' => $row->agency_id,
                                        'having' => $having,
                                        'display_query' => 0
                                    );
                                    $not_overdue = $this->jobs_model->getTotalUnpaidAmount($job_params);
                                    echo '$'.number_format($not_overdue,2);
                                    ?> 
                                </td> 
                                <td>  
                                    <?php
                                    $having = "DateDiff BETWEEN 31 AND 60";
                                    $job_params = array(
                                        'agency_id' => $row->agency_id,
                                        'having' => $having,
                                        'display_query' => 0
                                    );
                                    $overdue_31_to_60 = $this->jobs_model->getTotalUnpaidAmount($job_params);
                                    echo '$'.number_format($overdue_31_to_60,2);
                                    ?>                               
                                </td>
                                <td>  
                                    <?php
                                    $having = "DateDiff BETWEEN 61 AND 90";
                                    $job_params = array(
                                        'agency_id' => $row->agency_id,
                                        'having' => $having,
                                        'display_query' => 0
                                    );
                                    $overdue_61_to_90 = $this->jobs_model->getTotalUnpaidAmount($job_params);
                                    echo '$'.number_format($overdue_61_to_90,2);
                                    ?>                               
                                </td>
                                <td>  
                                    <?php
                                    $having = "DateDiff >= 91";
                                    $job_params = array(
                                        'agency_id' => $row->agency_id,
                                        'having' => $having,
                                        'display_query' => 0
                                    );
                                    $overdue_91_more = $this->jobs_model->getTotalUnpaidAmount($job_params);
                                    echo '$'.number_format($overdue_91_more,2);
                                    ?>                               
                                </td>
                                <td>  
                                    <?php                                   
                                    echo '$'.number_format($row->invoice_balance_tot,2);
                                    ?>                               
                                </td>                         
                            </tr>
                        <?php

                        // get total
                        $not_overdue_tot += $not_overdue;
                        $overdue_31_to_60_tot += $overdue_31_to_60;
                        $overdue_61_to_90_tot += $overdue_61_to_90;
                        $overdue_91_more_tot += $overdue_91_more;
                        $invoice_balance_tot += $row->invoice_balance_tot;                        

                        $i++;
                        }

                        // get percentage
                        $not_overdue_perc = ($not_overdue_tot / $invoice_balance_tot) * 100;
                        $overdue_31_to_60_perc = ($overdue_31_to_60_tot / $invoice_balance_tot) * 100;
                        $overdue_61_to_90_perc = ($overdue_61_to_90_tot / $invoice_balance_tot) * 100;
                        $overdue_91_more_perc = ($overdue_91_more_tot / $invoice_balance_tot) * 100;
                        $total_amount_due_perc = 100;

                    }else{ ?>
                        <tr><td colspan="6">Press Search to display data</td></tr>
                    <?php
                    }                            
                    ?>                    
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>TOTAL</th>
                            <th>$<?php echo number_format($not_overdue_tot,2); ?></th>			
                            <th>$<?php echo number_format($overdue_31_to_60_tot,2); ?></th>	
                            <th>$<?php echo number_format($overdue_61_to_90_tot,2); ?></th>
                            <th>$<?php echo number_format($overdue_91_more_tot,2); ?></th>
                            <th>$<?php echo number_format($invoice_balance_tot,2); ?></th>			
                        </tr>
                        <tr>
                            <th>Ageing Percentage</th>
                            <th><?php echo number_format($not_overdue_perc); ?>%</th>			
                            <th><?php echo number_format($overdue_31_to_60_perc); ?>%</th>
                            <th><?php echo number_format($overdue_61_to_90_perc); ?>%</th>
                            <th><?php echo number_format($overdue_91_more_perc); ?>%</th>
                            <th><?php echo number_format($total_amount_due_perc); ?>%</th>
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
    This report shows agency debt amounts by amount overdue and debt age.
	</p>

    <pre>
<code>SELECT SUM(j.`invoice_balance`) AS invoice_balance_tot, `a`.`agency_name`, `a`.`agency_id`
FROM `jobs` AS `j`
LEFT JOIN `property` AS `p` ON j.`property_id` = p.`property_id`
LEFT JOIN `agency` AS `a` ON  p.`agency_id` = a.`agency_id`
WHERE `a`.`country_id` = 1
AND `j`.`invoice_balance` >0
AND `j`.`status` = 'Completed'
AND `a`.`status` != 'target'
AND (
j.`date` >= '<?php echo $this->config->item('accounts_financial_year'); ?>' OR
j.`unpaid` = 1
)
GROUP BY `a`.`agency_id`
ORDER BY `a`.`agency_name` ASC
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


});
</script>