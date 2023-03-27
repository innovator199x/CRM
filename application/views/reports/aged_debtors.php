<div class="box-typical box-typical-padding">
<style>

.age_debtors_agency_name_header{
    margin-top:30px;
    margin-bottom:10px;
}
.col-mdd-3{
    max-width:37%;
}

</style>
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
    echo form_open("reports/aged_debtors",$form_attr);
    ?>
        <div class="for-groupss row">
            <div class="col-md-8 columns">
                <div class="row">

                    <div class="col-mdd-3">
                        <label>Agency</label>
                        <select name="agency_filter" class="form-control">
                            <?php 
                                if( $this->config->item('country')==1 && ENVIRONMENT=='production' ){
                                }else{
                            ?>
                                      <option value="">***Please Select***</option>
                            <?php
                                }
                            ?>

                            <?php
                             foreach($agency_filter->result_array() as $row_filter){ 
                             $sel = ( $row_filter['agency_id']== $this->input->get_post('agency_filter') ) ? 'selected="true"' : NULL;
                            ?>
                                <option <?php echo $sel; ?> value="<?php echo $row_filter['agency_id'] ?>"><?php echo $row_filter['agency_name'] ?></option>    
                            <?php } ?>
                        </select>
                    </div>	

                    <div class="col-md-1 columns">
                        <label class="col-sm-12 form-control-label">&nbsp;</label>
                        <input type="submit" name="search_submit" value="Search" class="btn">
                    </div>
                    
                </div>
            </div>

            <?php
                if( $this->input->get_post('search_submit') && $this->input->get_post('agency_filter')!="" ){ ?>

                    <div class="col-md-4 columns">

                        <div class="col-md-12 columns text-right">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            
                            <section class="proj-page-section pdf_header_section">
                                <div class="proj-page-attach pdf_header_div">
                                    <i class="fa fa-file-excel-o"></i>
                                    <p class="name"><?php echo $title; ?></p>
                                    <p>
                                       <!-- <a href="/reports/aged_debtors_export/?pdf=1&output_type=I&agency_filter=<?php echo $this->input->get_post('agency_filter'); ?>"                                            
                                            target="blank"
                                        >
                                            View
                                        </a>
                                        -->

                                        <a href="/reports/aged_debtors_export/?pdf=1&output_type=D&agency_filter=<?php echo $this->input->get_post('agency_filter'); ?>">
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
          


                <?php
                    if( $this->input->get_post('search_submit') && $this->input->get_post('agency_filter')!="" ){
                  //foreach($list->result_array() as $agency_row){ 
                ?>
                <?php // echo "<h4 class='age_debtors_agency_name_header'>{$agency_row['agency_name']}</h4>"; ?>
				<table class="table table-hover main-table duplicate_users_tbl">

					<thead>
						<tr>         
                            <th width="30%">Property Address</th>    
                            <th>Date</th>   
                            <th width="11%">Invoice #</th>       
                            <th idth="11%">Current</th>       
                            <th idth="11%">1-30 days <span class="txt_red">OVERDUE</span></th>							
                            <th idth="11%">31-60 days <span class="txt_red">OVERDUE</span></th>
                            <th idth="11%">61+ days <span class="txt_red">OVERDUE</span></th>
                            <th idth="11%">Total Amount Due</th>
						</tr>
					</thead>

					<tbody>
                        <?php 

                           

                                //---------get unpaid joblist by agency id

                                 // main query
                                $sel_query = "
                                    a.`agency_name`, 
                                    a.`agency_id`,

                                    j.`id` as j_id,
                                    j.`invoice_balance`,
                                    j.date as j_date,

                                    p.`address_1` as p_address_1,
                                    p.`address_2` as p_address_2,
                                    p.`address_3` as p_address_3,
                                    p.`state` as p_state,
                                    p.`postcode` as p_postcode,
                                ";

                                // static financial year 
                                $financial_year = $this->config->item('accounts_financial_year');
                                // get unpaid jobs and exclude 0 job price
                                $custom_where = "`j`.`invoice_balance` >0
                                            AND `j`.`status` = 'Completed'
                                            AND a.`status` != 'target'
                                            AND (
                                                    j.`date` >= '$financial_year' OR
                                                    j.`unpaid` = 1	
                                            )
                                ";
                                $main_params = array(
                                    'sel_query' => $sel_query,
                                    'custom_where' => $custom_where,
                                    'agency_filter' => $this->input->get_post('agency_filter'),
                                    'country_id' => $country_id,
                                    'sort_list' => array(
                                        array(
                                            'order_by' => 'j.date',
                                            'sort' => 'DESC'
                                        )
                                    ),
                                    'display_query' => 0
                                );

                                $list2 = $this->jobs_model->get_jobs($main_params);

                                $current_tot = 0;
                                $overdue_1_to_30_tot = 0;
                                $overdue_31_to_60_tot = 0;
                                $overdue_61_tot = 0;
                                $total_amount_due_tot = 0;

                                foreach($list2->result_array() as $row){


                                    $check_digit = $this->gherxlib->getCheckDigit(trim($row['j_id']));
                                    $bpay_ref_code = "{$row['j_id']}{$check_digit}";
                        ?>
                                    <tr>
                                        <td><?php echo "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']} {$row['p_state']} {$row['p_postcode']}" ?></td>
                                        <td><?php echo $this->system_model->formatDate($row['j_date'], 'd/m/Y') ?></td>
                                        <td><?php echo $bpay_ref_code; ?></td>
                                        <td>
                                            <?php
                                            $having = "DateDiff <= 1";
                                            $job_params = array(
                                                'agency_id' => $row['agency_id'],
                                                'having' => $having,
                                                'job_id' => $row['j_id'],
                                                'display_query' => 0
                                            );
                                            $current = $this->jobs_model->getTotalUnpaidAmount_by_jobid($job_params);
                                            echo '$'.number_format($current,2);
                                            ?> 
                                        </td> 
                                        <td>  
                                            <?php
                                            $having_1_30 = "DateDiff BETWEEN 1 AND 30";
                                            $job_params_1_30 = array(
                                                'agency_id' => $row['agency_id'],
                                                'job_id' => $row['j_id'],
                                                'having' => $having_1_30,
                                                'display_query' => 0
                                            );
                                            $overdue_1_to_30 = $this->jobs_model->getTotalUnpaidAmount_by_jobid($job_params_1_30);
                                            echo '$'.number_format($overdue_1_to_30,2);
                                            ?>                               
                                        </td>
                                        <td>  
                                            <?php
                                            $having_31_60 = "DateDiff BETWEEN 31 AND 60";
                                            $job_params_31_60 = array(
                                                'agency_id' => $row['agency_id'],
                                                'job_id' => $row['j_id'],
                                                'having' => $having_31_60,
                                                'display_query' => 0
                                            );
                                            $overdue_31_to_60 = $this->jobs_model->getTotalUnpaidAmount_by_jobid($job_params_31_60);
                                            echo '$'.number_format($overdue_31_to_60,2);
                                            ?>                               
                                        </td>
                                        <td>  
                                        <?php
                                        $having_61 = "DateDiff >= 61";
                                        $job_params_61 = array(
                                            'agency_id' => $row['agency_id'],
                                            'job_id' => $row['j_id'],
                                            'having' => $having_61,
                                            'display_query' => 0
                                        );
                                        $overdue_61 = $this->jobs_model->getTotalUnpaidAmount_by_jobid($job_params_61);
                                        echo '$'.number_format($overdue_61,2);
                                        ?>                               
                                    </td>
                                    <td>
                                        <?php
                                            $total_amount_due = $row['invoice_balance'];
                                            echo $total_amount_due;
                                        ?>
                                    </td>
                                    </tr>
                        <?php
                                    //totals
                                    $current_tot += $current;
                                    $overdue_1_to_30_tot += $overdue_1_to_30;
                                    $overdue_31_to_60_tot += $overdue_31_to_60;
                                    $overdue_61_tot += $overdue_61;
                                    $total_amount_due_tot += $total_amount_due;

                                }

                        ?>
                        

                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3">TOTAL</th>			
                            <th>$<?php echo number_format($current_tot,2); ?></th>					
                            <th>$<?php echo number_format($overdue_1_to_30_tot,2); ?></th>					
                            <th>$<?php echo number_format($overdue_31_to_60_tot,2); ?></th>					
                            <th>$<?php echo number_format($overdue_61_tot,2); ?></th>					
                            <th>$<?php echo number_format($total_amount_due_tot,2); ?></th>					
                           									
                        </tr>
                    
                    </tfoot>

				</table>		

                <?php 
               // }
                }else{
                    echo "Press search to display data.";
                } ?>

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
    This shows reports on aged debts per agency and can be exported to a spreadsheet.
	</p>
    <pre>
<code>SELECT `a`.`agency_name`, `a`.`agency_id`, `j`.`id` as `j_id`, `j`.`invoice_balance`, `j`.`date` as `j_date`, `p`.`address_1` as `p_address_1`, `p`.`address_2` as `p_address_2`, `p`.`address_3` as `p_address_3`, `p`.`state` as `p_state`, `p`.`postcode` as `p_postcode`
FROM `jobs` AS `j`
LEFT JOIN `property` AS `p` ON j.`property_id` = p.`property_id`
LEFT JOIN `agency` AS `a` ON  p.`agency_id` = a.`agency_id`
WHERE `a`.`agency_id` = '1448'
AND `j`.`invoice_balance` >0
AND `j`.`status` = 'Completed'
AND `a`.`status` != 'target'
AND (
j.`date` >= '<?php echo $this->config->item('accounts_financial_year'); ?>' OR
j.`unpaid` = 1	
)
ORDER BY `j`.`date` DESC</code>
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