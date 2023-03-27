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
    .table_data tr td{
       border-right:1px solid #eee;
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
    

    .fixTableHead {
      overflow-y: auto;
      height: 300px;
    }
    .fixTableHead thead th {
      position: sticky;
      top: 0;
      border-right:1px solid #eee;
    }
    .table_top_head {
      position: sticky;
      top: 0;
    }
    table {
      border-collapse: collapse;        
      width: 100%;
    }
    th,
    td {
      padding: 8px 15px;
      /* border: 2px solid #529432; */
    }
    th {
      background: #ABDD93;
    }
</style>
<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => "Agency Health Check",
			'status' => 'active',
			'link' => ""
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>




	<section>
		<div class="body-typical-body">

			<div class="table-responsive">

                <div class="row" style="margin-right: 0px;">
                    <div class="col">
                    <div class="table_top_head text-left text-center">Snapshot</div>
                        <table class="table table-hover main-table kpi_snapshot_table">
                            <tr>
                                <td><strong>Total Properties</strong></td>
                                <td><?php echo number_format($total_prop); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Total Active Services</strong></td>
                                <td><?php echo number_format($prop_active_services); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Joined SATS</strong></td>
                                <td><?php echo ( $joined_sats!='' )?date('d/m/Y',strtotime($joined_sats)):''; ?></td>
                            </tr>
                            <tr>
                                <td><strong>API's</strong></td>
                                <td>
                                <?php
                                $i = 0;
                                $len = count($api->result_array());
                                if ($len == 0) {
                                    echo 'No API Connected';
                                } else{
                                    foreach($api->result_array() as $row){ 	
                                        echo $row['api_name'];
                                        if ($i == $len - 1) {
                                        
                                        } else {
                                            echo ', ';
                                        }
                                        $i++;
                                    }
                                }			
                                ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>New Properties (Last 30 days)</strong></td>
                                <td><?php echo number_format($prop_new_30); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Deactivated Properties (Last 30 days)</strong></td>
                                <td><?php echo number_format($prop_dec_30); ?></td>
                            </tr>
                        </table>
                    </div>

                    <div class="col">
                    <div class="table_top_head text-left text-center">Job Data</div>
                        <table class="table table-hover main-table kpi_snapshot_table">
                            <tr>
                                <td><strong>Send Letters</strong></td>
                                <td><?php echo number_format($jobs_send_letters); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Booked</strong></td>
                                <td>
                                    <a target="_blank" href="/jobs/booked/?agency_filter=<?php echo $agency_id; ?>">
                                        <?php echo number_format($jobs_booked); ?>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>On Hold</strong></td>
                                <td>
                                    <a target="_blank" href="/jobs/on-hold/?agency_filter=<?php echo $agency_id; ?>">
                                        <?php echo number_format($jobs_on_hold); ?>
                                    </a>                                    
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Escalate</strong></td>
                                <td>
                                    <a target="_blank" href="/jobs/escalate_jobs/<?php echo $agency_id; ?>">
                                        <?php echo number_format($jobs_escalate); ?>
                                    </a> 
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Allocate</strong></td>
                                <td>
                                    <a target="_blank" href="/jobs/allocate/?agency_filter=<?php echo $agency_id; ?>">
                                        <?php echo number_format($jobs_allocate); ?>
                                    </a> 
                                </td>
                            </tr>
                            <tr>
                                <td><strong>To Be Booked</strong></td>
                                <td>
                                    <a target="_blank" href="/jobs/to_be_booked/?agency_filter=<?php echo $agency_id; ?>">
                                        <?php echo number_format($jobs_to_be_booked); ?>
                                    </a> 
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Merged Certificates</strong></td>
                                <td><?php echo number_format($jobs_merged); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Completed (Last 30 days)</strong></td>
                                <td><?php echo number_format($jobs_completed); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Missed Jobs (Last 30 days)</strong></td>
                                <td><?php echo number_format($lists->num_rows()); ?></td>
                            </tr>
                            <?php if ($state == 'QLD') { ?>
                            <tr>
                                <td><strong>QLD - Failed Jobs (Last 30 days)</strong></td>
                                <td><?php echo number_format($jobs_failed_qld); ?></td>
                            </tr>
                            <?php } elseif ($state == 'NSW') { ?>
                            <tr>
                                <td><strong>NSW (Failed Jobs)</strong></td>
                                <td><?php echo number_format($jobs_failed_nsw); ?></td>
                            </tr>
                            <?php } ?>
                        </table>
                    </div>
                </div>
                

                <div class="table-responsive" style="height: 0px;">
                
			</div>


			</div>


		</div>
	</section><br>

    <section>
        <div class="body-typical-body">
            <div class="table-responsive">
            <div class="table_top_head text-left text-center">Missed Jobs (Last 30 Days)</div>
            <div class="fixTableHead">
				<table class="table table-hover main-table table_data">
                    <thead style="text-align:center;">
						<tr>
							<th>Date</th>
                            <th>Property</th>
                            <th>Reason</th>
                            <th>Comments</th>
						</tr>
					</thead>

					<tbody>

                        <?php
                            if($lists->num_rows()>0){
                            foreach($lists->result_array() as $row){

                                $full_prop_address = "{$row['address_1']} {$row['address_2']}, {$row['address_3']}";
                        ?>

                                <tr>
                                    <td>
                                    <?php  	echo ($row['jnc_date_created']!="")?date("d/m/Y",strtotime($row['jnc_date_created'])):''; ?>
                                    </td>
                                    <td>                         
                                        <a href="<?php echo $this->config->item('crm_link') ?>/view_property_details.php?id=<?php echo $row['property_id']; ?>">
                                            <?php echo $full_prop_address; ?>
                                        </a> 
                                    </td>
                                    <td> 
                                    <?php 
                                    echo $row['jr_name'];

                                    ?>
                                    </td>
                                    <td><?php echo $row['reason_comment']; ?></td>
                                </tr>

                        <?php
                            }
                        
                        ?>

                        <?php }else{
                            echo "<tr><td>No Data</td><td></td><td></td><td></td></tr>";
                        } ?>
                      
                       
					</tbody>

				</table>

            </div>
            </div>


        </div>
    </section> <br>
    
    <section>
        <div class="body-typical-body">
            <div class="table-responsive">
                <div class="table_top_head text-left text-center">Tenant Feedback (Last 30 Days)</div>
                <div class="fixTableHead">
                <table class="table table-hover main-table table_data">

                    <thead style="text-align:center;">
                        <tr>                            
                            <th>Date</th>
                            <th style="display: none;">From</th>
                            <th>Address</th>
                            <th>Tenant</th>
                            <th>Message</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        if( $list->num_rows() > 0 ){

                            foreach ($list->result_array() as $cr) {     
                                $full_prop_address = "{$cr['address_1']} {$cr['address_2']}, {$cr['address_3']}";                       
                                ?>
                                <tr>                                
                                    <td><?php echo date('d/m/Y', strtotime($cr['sar_created_date'])); ?></td>
                                    <td style="display: none;" class="mob_td"><?php echo $mob_num = '0'.substr($cr['sar_mobile'],2); ?></td>
                                    <td>                         
                                        <a href="<?php echo $this->config->item('crm_link') ?>/view_property_details.php?id=<?php echo $cr['property_id']; ?>">
                                            <?php echo $full_prop_address; ?>
                                        </a> 
                                    </td>
                                    <td>
                                    <?php
                                        // get tenants
                                        $sel_query = "
                                        pt.`property_tenant_id`,
                                        pt.`tenant_firstname`,
                                        pt.`tenant_lastname`,
                                        pt.`tenant_mobile`,
                                        pt.`tenant_email`
                                        ";
                                        $params = array(
                                        'sel_query' => $sel_query,
                                        'property_id' => $cr['property_id'],
                                        'pt_active' => 1,
                                        'display_query' => 0
                                        );
                                        $pt_sql = $this->properties_model->get_property_tenants($params);
                                        if( $pt_sql->num_rows() > 0 ){
    
                                            // loop through tenants
                                            foreach($pt_sql->result() as $pt_row){
                                                $tenants_num = str_replace(' ', '', trim($pt_row->tenant_mobile));
                                                if( $tenants_num != '' && $tenants_num == $mob_num ){
                                                    $tenant_name = $pt_row->tenant_firstname;
                                                }
                                            }
                                        }
    
                                        echo $tenant_name;
                                        ?>
                                    </td>
                                    <td><?php echo $cr['response']; ?></td>
                                </tr>
                                <?php
                            }

                        }else{ ?>
                            <tr><td>No Results</td><td></td><td></td><td></td></tr>
                        <?php
                        }                        
                        ?>
                    </tbody>

                </table>
                </div>

            </div>


        </div>
    </section><br>

    <section>
        <?php
        $ahc_data['from'] = date('Y-m-01');
        $ahc_data['to'] = date('Y-m-t');
        $ahc_data['ajt_id'] = 0;
        $ahc_data['agency_id'] = $agency_id;
        $this->load->view('agency/ahc_completed_report', $ahc_data);
         ?>
    </section>

    

</div>



<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4>Agency Health Check</h4>
	<p>
    This page show all data for agency.</p>
    
</div>
<!-- Fancybox END -->

<script src="/inc/js/lib/ladda-button/spin.min.js"></script>
<script src="/inc/js/lib/ladda-button/ladda.min.js"></script>	

