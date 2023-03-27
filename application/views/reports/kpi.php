<link rel="stylesheet" href="/inc/css/lib/ladda-button/ladda-themeless.min.css">
<style>
	.col-mdd-3{
		max-width:15%;
	}
    .table_top_head h4{
        margin:0;
    }
    table.main-table{
        margin-bottom:30px;
    }
    .kpi_snapshot_table tr td{
        width:50%;
    }
    .kpi_snapshot_table tr td:first-child{
       border-right:1px solid #eee;
       text-align:center;
    }
    .kpi_snapshot_table tr td:last-child{
        text-align:center;
    }
    .kpis_awts{
        text-align:center;
    }
    .ladda-button.disabled, .ladda-button:disabled{
        opacity: .65!important;
        background:#16b4fc!important;
        border:1px solid #16b4fc;
    }
    #display_sales_result_btn, #display_bookings_btn{
        margin-bottom:15px;
    }

    #sales_report_div{
       display:none;
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
			'title' => "KPI<span style='text-transform: lowercase'>s</span>",
			'status' => 'active',
			'link' => "/reports/kpi"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>




	<section>
		<div class="body-typical-body">

			<div class="table-responsive">

                <div class="table_top_head text-left text-center">Snapshot</div>
                <table class="table table-hover main-table kpi_snapshot_table">
                    <tr>
                        <td><strong>Total Properties</strong></td>
                        <td><?php echo number_format($total_prop->p_count); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Total Agencies</strong></td>
                        <td><?php echo number_format($agency_count); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Outstanding Jobs</strong></td>
                        <td><?php echo number_format($outstanding_jobs->row()->j_count); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Outstanding Value</strong></td>
                        <td>
                            <?php 
                            //echo "$".number_format($outstanding_value->row()->j_sum_price, 2); 

                            $os_value = $outstanding_value->row()->j_sum_price;
                            echo ( $os_value > 0 )?'$'.number_format($this->system_model->price_ex_gst($os_value),2):null;
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Average Age (Not Completed)</strong></td>
                        <td>
                            <?php 
                                $sum_age = $average_age->row()->sum_age;
                                $j_count = $average_age->row()->j_count;
                                echo number_format(($sum_age/$j_count), 2, '.', '').' days';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Average Age (Completed this Month)</strong></td>
                        <td><?php 

                            $sum_completed_age = ($average_age_completed->row()->sum_completed_age!=NULL)?$average_age_completed->row()->sum_completed_age:0;
                            $jcount = $average_age_completed->row()->j_count;	
                            $dev = number_format(($sum_completed_age/$jcount), 2, '.', '');				
                            echo $dev.' days';

                        ?></td>
                    </tr>
                </table>


                <div class="kpis_awts">
                   <!-- <button type="submit" id="display_bookings_btn" class="btn ladda-button" data-style="zoom-in">
				<span class="ladda-label">Display Bookings</span>
			</button>-->
                    <div id="bookings_div"></div>
                </div>

               <div class="kpis_awts">
                    <button type="submit" id="display_sales_result_btn" class="btn ladda-button" data-style="zoom-in">
                        <span class="ladda-label">Display Sales Result</span>
                    </button>
                    <div id="sales_report_div"></div>
                </div>


			</div>


		</div>
	</section>



</div>



<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4>KPIs</h4>
	<p>This page gives a quick snapshot of Key Performance Indicators (KPIs).</p>

    <p><strong>Snapshot</strong><br/>
<pre>
<code>SELECT COUNT(DISTINCT(p.`property_id`)) AS p_count, p.`property_id`
FROM `property_services` AS ps
LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
WHERE ps.`service` =1
AND p.`deleted` =0
AND (`p`.`is_nlm` =0 OR `p`.`is_nlm` IS NULL)
AND a.`status` = 'active'
AND a.`country_id` = 1</code>
</pre>
</p>
<p>
   <strong> Sales Result</strong><br/>
    <pre>
<code>SELECT COUNT(ps.`property_services_id`) AS p_count
FROM `property_services` AS ps
LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
WHERE a.`salesrep` =2070
AND ps.`alarm_job_type_id` =2
AND ps.`service` = 1
AND a.`country_id` = 1
AND CAST(ps.`status_changed` AS DATE) BETWEEN 'date('Y-m-01')' AND 'date('Y-m-t')'
AND (
p.`is_nlm` IS NULL 
OR p.`is_nlm` = 0
)</code>
</pre>
</p>
</div>
<!-- Fancybox END -->

<script src="/inc/js/lib/ladda-button/spin.min.js"></script>
<script src="/inc/js/lib/ladda-button/ladda.min.js"></script>	

<script type="text/javascript">


    function showBooking(){

          var booking_result_div  = $('#bookings_div');

          booking_result_div.load('/reports/ajax_get_bookings', {
                country_id: <?php echo $this->config->item('country') ?>
                }, function(response, status, xhr) {
                    console.log('booking load success');
                }
            );

    }

    jQuery(document).ready(function() {
      
        showBooking();

        // load Booking via ajax
        $('#display_bookings_btn').click(function(e){
            e.preventDefault();
            
            var obj = $(this);
            var booking_result_div  = $('#bookings_div');

            var l = Ladda.create(this);
            l.start();

            //load tenants ajax box (via ajax)
            booking_result_div.load('/reports/ajax_get_bookings', {
                country_id: <?php echo $this->config->item('country') ?>
                }, function(response, status, xhr) {
                    l.stop();
                    booking_result_div.slideDown();
                    obj.hide();
                }
            );

            $(this).unbind();

        })

        // load Sales Result via ajax
        $('#display_sales_result_btn').click(function(e){
            e.preventDefault();
            
            var obj = $(this);
            var sales_result_div  = $('#sales_report_div');

            var l = Ladda.create(this);
            l.start();

            //load tenants ajax box (via ajax)
            sales_result_div.load('/reports/ajax_get_sales_result', {
                country_id: <?php echo $this->config->item('country') ?>
                }, function(response, status, xhr) {
                    l.stop();
                    sales_result_div.slideDown();
                    obj.hide();
                }
            );

            $(this).unbind();

        })



    });

</script>