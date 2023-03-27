<?php

	# Create predefined date ranges
	$today = date('d/m/Y');

	$date_ranges = array();

	$date_ranges[] = array(
	'title' => 'All',
	'from' => 'all',
	'to' => 'all'
	);

	$date_ranges[] = array(
	'title' => 'Today',
	'from' => date('d/m/Y'),
	'to' => date('d/m/Y')
	);

	$date_ranges[] = array(
	'title' => 'Yesterday',
	'from' => date('d/m/Y', (strtotime('-1 days'))),
	'to' => date('d/m/Y', (strtotime('-1 days')))
	);

	$date_ranges[] = array(
	'title' => 'Last Week',
	'from' => date('d/m/Y', strtotime('previous week Monday') ),
	'to' => date('d/m/Y', strtotime('previous week Sunday') )
	);

	$date_ranges[] = array(
	'title' => 'Next Week',
	'from' => $today,
	'to' => date('d/m/Y', (strtotime('+7 days')))
	);



	$date_ranges[] = array(
	'title' => date("F",mktime(0,0,0, (date("n") - 1 + 12) % 12, 1)),
	'from' => date("01/m/Y",mktime(0,0,0, (date("n") - 1 + 12) % 12, 1)),
	'to' => date("t/m/Y",mktime(0,0,0, (date("n") - 1 + 12) % 12, 1))
	);


	$date_ranges[] = array(
	'title' => date("F",mktime(0,0,0, (date("n") - 2 + 12) % 12, 1)),
	'from' => date("01/m/Y",mktime(0,0,0, (date("n") - 2 + 12) % 12, 1)),
	'to' => date("t/m/Y",mktime(0,0,0, (date("n") - 2 + 12) % 12, 1))
	);

	$date_ranges[] = array(
	'title' => date("F",mktime(0,0,0, (date("n") - 3 + 12) % 12, 1)),
	'from' => date("01/m/Y",mktime(0,0,0, (date("n") - 3 + 12) % 12, 1)),
	'to' => date("t/m/Y",mktime(0,0,0, (date("n") - 3 + 12) % 12, 1))
    );
    

    //for sales result
    $ajt_sql2 = $this->reports_model->getDynamicServices();
    $ajt_arr = [];
    foreach($ajt_sql2->result_array() as $ajt2){

        switch($ajt2['id']){
            case 8:
                $ajt_name = 'SA SS';
            break;
            case 9:
                $ajt_name = 'SA SS CW';
            break;
            case 11:
                $ajt_name = 'SA WM';
            break;
            case 13:
                $ajt_name = 'SA SS (IC)';
            break;
            case 14:
                $ajt_name = 'SA CW SS (IC)';
            break;
            default:
                $ajt_name = $ajt2['short_name'];
        }
        
        $ajt_arr[] = array(
            'id' => $ajt2['id'],
            'type' => $ajt2['type'],
            'short_name' => $ajt2['short_name'],
            'short_name_wspace' => $ajt_name
        );

    }
    $row_count = $ajt_sql2->num_rows();

    //for sales activity 
    $agency_logs_sql = $this->reports_model->getAgencyLogs();
    $al_arr = [];

    foreach($agency_logs_sql->result_array() as $al){
        $al_arr[] = $al['contact_type'];
    }
    $al_row_count = $agency_logs_sql->num_rows();



    // distint sales rep
    $sr_sql = $this->reports_model->distinct_salesrep($from,$to);
    $sales_arr = array();
    foreach($sr_sql->result_array() as $sr){

        $sales_result_tot = 0;
        $sales_arr[] = array(
            'saleperson_id' => $sr['salesrep'],
            'salesperson_name' => "{$sr['FirstName']} {$sr['LastName']}"
        );
    }

    // distint sales rep for p.is_sales only
    $sr_sql2 = $this->reports_model->distinct_salesrep_for_sales_property($from,$to,1);
    $sales_arr2 = array();
    foreach($sr_sql2->result_array() as $sr){

        $sales_result_tot = 0;
        $sales_arr2[] = array(
            'saleperson_id' => $sr['salesrep'],
            'salesperson_name' => "{$sr['FirstName']} {$sr['LastName']}"
        );

    }

    $arr_ic_sales_saleperson_id =  implode(",",array_column($sales_arr2,"saleperson_id"));

    $sales_result_overall_tot = 0;

?>
<style>
	.col-mdd-3{
		max-width:15%;
	}
    #mark_all_btn{
        display: none;
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
			'link' => "/reports/sales_report"
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
		echo form_open('/reports/sales_report',$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-md-12 columns">
					<div class="row">

                        <div class="col-md-6">

                            <div class="float-left mr-3">
                                <label for="date_select">Report from:</label>
                                <input name="date_from_filter" placeholder="ALL" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $from ?>">
                            </div>

                            <div class="float-left mr-3">
                                <label for="date_select">to:</label>
                                <input name="date_to_filter" placeholder="ALL" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $to ?>">
                            </div>
                            

                            <div class="float-left mr-3">
                                <label class="col-sm-12 form-control-label">&nbsp;</label>
                                
                                <?php if(is_int($staff_id)): ?>
                                <input type="hidden" name="sid" value="<?php echo $staff_id; ?>" class="submitbtnImg">	
                                <?php endif; ?>	
                                <?php if(is_int($tech_id)): ?>
                                <input type="hidden" name="tid" value="<?php echo $tech_id; ?>" class="submitbtnImg">	
                                <?php endif; ?>

                                <input type="hidden" name="get_sats" value="1" />
                                <input type="submit" class="btn" value="Get Stats" name="btnGetStats" >
                            </div>

                        </div>

                        <div class="col-md-6">
                            <?php             
                            // only show to dev, ben and sir dan and only show on sales commission new version         
                            if( in_array( $this->session->staff_id, $this->system_model->big_3() ) && $sales_commission_ver == 'new' ){ ?>

                                <div class="float-right">
                                    <label class="col-sm-12 form-control-label">&nbsp;</label>
                                    <button type="button" class="btn btn-success" id="process_btn">Process</button>
                                    <button type="button" class="btn" id="mark_all_btn">Mark all as commission paid</button>
                                </div>

                            <?php    
                            }
                            ?>  
                        </div>

					</div>

                </div>
                
            </div>
            
            <div class="for-groupss row quickLinksDiv">
        <div class="text-left col-md-3 columns">

           <?php echo $this->customlib->generateLink($prev_day, $staff_filter); ?>

        </div>
        <div class="text-center col-md-6 columns">

           Quick Links&nbsp;|&nbsp;
		    <?php foreach($date_ranges as $index=>$range): ?>
				<?php echo $this->customlib->generateLink($range, $staff_filter); ?>
				<? if($index < sizeof($date_ranges) - 1): ?>
				&nbsp;|&nbsp;
				<? endif; ?>		
			<?php endforeach; ?>	

        </div>
        <div class="text-right col-md-3 columns">
			
			<?php echo $this->customlib->generateLink($next_day, $staff_filter); ?>


        </div>
            </div>
			</form>
		</div>
	</header>
<?php 
	if($this->input->get_post('btnGetStats') || $this->input->get_post('get_sats')==1 || $this->input->get_post('ver') != '' ){
	
		
?>
    
	<section>
		<div class="body-typical-body">

            <div class="report_head">
                <h4>Report <?php echo $from; ?> to <?php echo $to; ?></h4>

                <?php
                # Alert that viewing Staff or Tech indivudal reports if neded, and offer to reset
                if(is_int($staff_id))
                {
                    echo "<div class='success'>Currently viewing statistics for staff member: " . $staff_details['FirstName'] . " " . $staff_details['LastName'] . " " . $report->generateLink(array('from' => $from, 'to' => $to, 'title' => 'back to all')) . "</div>";
                }
                if(is_int($tech_id))
                {
                    echo "<div class='success'>Currently viewing statistics for technician: " . $tech_details['FirstName'] . " " . $tech_details['last_name'] . " " . $report->generateLink(array('from' => $from, 'to' => $to, 'title' => 'back to all')) . "</div>";
                }
                ?>

                <p>This report shows totals that are SATS to service. These numbers do not include totals for properties that are marked DIY, No Response or Other Provider</p>
            </div>

			<div class="table-responsive">


                <!-- SALES RESULT TABLE -->
                <div id="search_result">
                    <div class="table_top_head">Sales Results</div>
                    <table class="table table-hover main-table table_border">
                        <thead>
                            <tr>
                                <th>Staff</th>

                                <?php 
                                    foreach($ajt_arr as $row){
                                        if($this->config->item('country')==2){
                                            if($row['id']==2){
                                                echo "<th>{$row['short_name_wspace']}</th>";
                                            }
                                        }else{
                                            echo "<th>{$row['short_name_wspace']}</th>";
                                        }
                                    }
                                ?>

                                <th>Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                                foreach( $sales_arr as $sales ){
                                    $sales_result_tot = 0;
                            ?>

                                    <tr>
                                        <td>
                                            <?php  echo $sales['salesperson_name']; ?>
                                        </td>
                                        <?php
                                        foreach( $ajt_arr as $ajt ){ 
                                            if($this->config->item('country')==2){ //NZ removed other servcies
                                                if($ajt['id']==2){
                                            ?>
                                            <td>
                                                <?php 
                                                $sa_query = $this->reports_model->get_num_services($sales['saleperson_id'],$ajt['id'],$from,$to,$this->config->item('country'),1);
                                                $sa = $sa_query->row()->p_count;
                                                echo ($sa>0)?$sa:'';
                                                $sales_result_tot += $sa;
                                                ?>
                                            </td>
                                        <?php	
                                                }
                                            }else{ //AU display all services
                                        ?>
                                                 <td>
                                                    <?php 
                                                    $sa_query = $this->reports_model->get_num_services($sales['saleperson_id'],$ajt['id'],$from,$to,$this->config->item('country'),1);
                                                    $sa = $sa_query->row()->p_count;
                                                    echo ($sa>0)?$sa:'';
                                                    $sales_result_tot += $sa;
                                                ?>
                                            </td>
                                        <?php
                                            }
                                        }
                                        ?>
                                        <td>
                                        <?php
                                            echo ( $sales_result_tot>0 )?$sales_result_tot:'';
                                            $sales_result_overall_tot += $sales_result_tot;
                                        ?>
                                        </td>
                                    </tr>

                            <?php
                                }
                            ?>
                            <tr style="background:#f6f8fa;">
                                <td><strong>Total</trong></td> 
                                <?php
                                $awaw_ctn = 0;
                                    foreach( $ajt_arr as $ajt ){ 
                                    if($this->config->item('country')==2){
                                    if($awaw_ctn==0){
                                    ?>
                                    <td>&nbsp;</td>
                                <?php	
                                    }
                                    }else{
                                        echo " <td>&nbsp;</td>";
                                    }
                                $awaw_ctn++;
                                    }
                                ?>
                                <td><strong><?php echo $sales_result_overall_tot; ?></strong></td> 
                            </tr>
                        </tbody>

                    </table>
                </div>
                 <!-- SALES RESULT TABLE END -->
                
                <p>&nbsp;</p>
                
                <?php if(COUNTRY==1){ ?>
                 <!-- IC is_sales RESULT TABLE -->
                 <div id="search_result">
                    <div class="table_top_head">Sales</div>
                    <table class="table table-hover main-table table_border">
                        <thead>
                            <tr>
                                <th>Staff</th>
                                <th>Sales Upgrade</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                                foreach( $sales_arr2 as $sales ){
                                    if($sales['saleperson_id']>0){

                                    $sales_result_tot2 = 0;
                            ?>

                                    <tr>
                                        <td>
                                            <?php 
                                             echo $sales['salesperson_name']; 
                                             ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $tt_sales = $this->reports_model->get_num_services_for_IC_sales( $sales['saleperson_id'],$from,$to); 
                                            $tt_sales_cnt = $tt_sales->row()->p_count;

                                            echo ($tt_sales_cnt>0)?$tt_sales_cnt:'';
                                            $tt_sales_cnt_total += $tt_sales_cnt;
                                            ?>

                                        </td>
                                    </tr>

                            <?php
                                }
                                }
                            ?>
                            <thead style="background:#f6f8fa;">
                                <th>Total</th>
                                <th><?php echo $tt_sales_cnt_total; ?></th>
                            </thead>
                        </tbody>

                    </table>
                </div>
                <!-- IC is_sales RESULT TABLE END -->
                <p>&nbsp;</p>
                <?php } ?>

                <!-- SALES ACTIVITY TABLE -->
                <div class="table_top_head">Sales Activity</div>
				<table class="table table-hover main-table table_border">
					<thead>
						<tr>
							<th>Staff</th>
                            <?php
                            foreach( $get_sales_report_sql as $al ){ ?>
                                <th data-contact_type_id = <?php   echo $al['contact_type_id']; ?>><?php echo $al['contact_type_name']; ?></th>
                            <?php	
                            }
                            ?>
						</tr>
					</thead>

					<tbody>

                    <?php
                        foreach($sales_report_staff as $salesrep){
                    ?>

                        <tr>
                         <td data-staffID="<?php echo $salesrep['staff_id'] ?>"><?php echo "{$salesrep['FirstName']} {$salesrep['LastName']}" ?></td>

                            <?php
                                foreach( $get_sales_report_sql as $al ){ 
                            ?>
                                   <td>
                                        <?php 
                                  
                                      $sales_report_count_params = array(
                                        'sel_query' => "COUNT(sr.id) as sales_report_count, sr.contact_type as contact_type",
                                        'contact_type' => $al['contact_type_id'],
                                        'staff_id'=> $salesrep['staff_id'],
                                        'custom_where' => $date_filter_str
                                    );
                                    $sales_report_count_sql = $this->reports_model->get_sales_report($sales_report_count_params)->row_array();
                                    echo $sales_report_count_sql['sales_report_count'];
                                        ?>
                                    </td>
                            <?php	
                                }
                            ?>

                        </tr>

                    <?php
                        }
                    ?>
					
					</tbody>

				</table>
                 <!-- SALES ACTIVITY TABLE END -->



			</div>


		</div>
	</section>

<?php
	}else{
		echo "<h3>Press 'Get Stats' to Display Results</h3>";
	}
?>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4>Sales Report</h4>
	<p>This report shows all sales results for the given date period selected.</p>
<pre>
<code>SELECT DISTINCT(sr.contact_type) as contact_type_id, `mlt`.`contact_type` as `contact_type_name`
FROM `sales_report` as `sr`
LEFT JOIN `main_log_type` as `mlt` ON `mlt`.`main_log_type_id`=`sr`.`contact_type`
WHERE `sr`.`active` = 1
GROUP BY `sr`.`contact_type`</code>
</pre>
</div>
<!-- Fancybox END -->
<script>
jQuery(document).ready(function(){

    // hide/show "Mark all as commission paid" button 
    jQuery("#process_btn").click(function(){

        jQuery("#mark_all_btn").toggle();

    });

    // process button
    jQuery("#mark_all_btn").click(function(){

        var from = '<?php echo $from; ?>';
        var to = '<?php echo $to; ?>'; 
        var sales_commission_ver = '<?php echo $sales_commission_ver; ?>';       

        swal({
            title: "Warning!",
            text: "Are you sure you want to mark all properties as commission paid?",
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
                jQuery.ajax({
                    type: "POST",
                    url: "/reports/process_mark_all_property_as_paid_commission",
                    data: {
                        from: from,
                        to: to,
                        sales_commission_ver: sales_commission_ver
                    }
                }).done(function( ret ){
                                            
                    $('#load-screen').hide();	
                    swal({
                        title: "Success!",
                        text: "Property commission marked as paid successful!",
                        type: "success",
                        confirmButtonClass: "btn-success",
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });                       
                   
                });						

            }

        });		        

    });

});
</script>
