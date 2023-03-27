
<div class="box-typical box-typical-padding">

<?php
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => "/reports"
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);
?>

<section>
    <div class="body-typical-body">

        <div class="row">



            <div class="col-md-6 columns">

                <!-- Property Reports  -->
                <section class="widget widget-reports">
                    <header class="widget-header widget-header-blue">
                        <em class="fa fa-home"></em> Property Reports 
                    </header>
                    <div>
                        <ul>
                            <li><a href="/properties/active_job_properties">Active Job Properties</a></li>
                            <li><a href="/properties/active_properties">Active Properties</a></li>
                            <li><a href="/reports/active_property_yet_to_visit">Active Property, yet to Visit</a></li>
                            <li><a href="/properties/api_workorders">API Workorders</a></li>
                            <li><a href="/properties/deactivated_properties">Inactive Properties</a></li>
                            <li><a href="/reports/most_recent_job_property_report">Most Recent Job Property Report</a></li>
                            <li><a href="/reports/new_properties_report">New Properties Report</a></li>
                            <li><a href="/reports/no_retest_date">No Retest Date</a></li>
                            <li><a href="/reports/properties_from_other_company">Properties From Other Companies</a></li>
                            <li><a href="/reports/properties_missing_variation">Properties Missing Variation</a></li>                                                      
                            <li><a href="/reports/property_gained_and_lost">Property Gained and Lost</a></li>
                            <li><a href="/properties/serviced_to_sats">Property Services Updated to SATS</a></li>
                            <li><a href="/reports/property_variation">Property Variation</a></li>                                                                                                                    
                            <li><a href="/reports/subscription_date_report">Subscription Date Report</a></li>
                            <li><a href="/reports/qld_not_upgraded">QLD Properties Requiring Upgrade</a></li> 
                            <li><a href="/properties/update_property_variation">Update Property Variation</a></li>      
                            
                        </ul>
                    </div>
                </section>
                <!-- Property Reports END  -->


                <!-- Employee Reports   -->            
                <section class="widget widget-reports">
                    <header class="widget-header widget-header-blue">
                        <em class="fa fa-user"></em> Employee Reports 
                    </header>
                    <div>
                        <ul>    
                            <li><a href="/users/incident_and_injury_report_list">Incident Summary</a></li>                  
                            <li><a href="/users/leave_requests">Leave Summary</a></li> 
                            <li><a href="/reports/leave_report">Leave Report</a></li> 
                            <?php
                            $filter_user_arr = [];
                            if( $this->config->item('country') == 1 ){ // AU

                                /*
                                2070 - Developer testing
                                2025 - Daniel                                    
                                2287 - Ben Taylor
                                */
                    
                                $filter_user_arr = array(2070, 2025, 2287);
                    
                            }else if( $this->config->item('country') == 2 ){ // NZ
                    
                                /*
                                2070 - Developer testing
                                2025 - Daniel
                                2231 - Ben Taylor  
                                */
                    
                                $filter_user_arr = array(2070, 2025, 2231);
                    
                            }
                            if( in_array($this->session->staff_id, $filter_user_arr) ){ ?>
                                <li><a href="/reports/user_logins">User Logins</a></li>                                                          
                            <?php
                            }
                            ?>  
                            <li><a href="<?php echo $this->config->item('crm_link') ?>/view_sats_users.php">View Users (OLD)</a></li>                                       
                        </ul>
                    </div>
                </section>            
                <!-- Employee Reports   END  -->


                <!-- Employee Reports   -->
                <section class="widget widget-reports">
                    <header class="widget-header widget-header-blue">
                        <em class="fa fa-pencil-square-o"></em>  Job Reports 
                    </header>
                    <div>
                        <ul>               
                            <li><a href="/reports/agency_api_logs">Agency API Logs</a></li>                       
                            <li><a href="/jobs">All Jobs</a></li>                        
                            <li><a href="/jobs/booked">Booked Jobs</a></li>                        
                            <li><a href="/jobs/booked_report">Booked Report</a></li>                        
                            <li><a href="/jobs/cancelled">Cancelled Jobs</a></li>                        
                            <li><a href="/jobs/completed">Completed Jobs</a></li>                                 
                            <li><a href="/jobs/completed_ic_upgrade">Completed IC Upgrade Jobs</a></li> 
                            <li><a href="/reports/compliance_report_nsw_act">Compliance Report (NSW, ACT & SA)</a></li>
                            <li><a href="/reports/compliance_report_qld">Compliance Report (QLD)</a></li>
                            <li><a href="/reports/daily_figures">Daily Figures</a></li>   
                            <li><a href="/jobs/deleted">Deleted Jobs</a></li>    
                            <li><a href="/reports/jobs_by_agency">Jobs By Agency</a></li>  
                            <li><a href="/jobs/image_booked">Image - Booked Jobs</a></li>  
                            <li><a href="/jobs/image_completed">Image - Completed Jobs</a></li>      
                            <li><a href="/jobs/image_on_hold">Image - On Hold</a></li>    
                            <li><a href="/jobs/new_jobs_report">New Jobs Report</a></li>        
                            <li><a href="/jobs/future_pendings">Service Due (<?php echo strtoupper(date('F', strtotime("+1 month"))) ?>)</a></li>   
                            <li><a href="/agency/servicedue">Service Due Report</a></li>     
                            <li><a href="/jobs/todays_jobs">Todays Jobs</a></li>        
                            <li><a href="/jobs/urgent_jobs">Urgent Jobs</a></li>       
                            <li><a href="/jobs/vacant">Vacant Jobs</a></li>       
                            <li><a href="/jobs/deactivated_agencies_with_active_jobs">Deactivated Agencies with Active Jobs</a></li>        
                        </ul>
                    </div>
                </section>

                <!-- Employee Reports   END  -->


                <!--  Operations Report  -->
                <section class="widget widget-reports">
                    <header class="widget-header widget-header-blue">
                        <em class="fa fa-pencil-square-o"></em> Operations Report 
                    </header>
                    <div>
                        <ul>       
                            <li><a href="/jobs/approved_alarm_numbers">Approved Alarm Numbers</a></li>     
                            <li><a href="/jobs/completed_report">Completed Jobs Report</a></li> 
                            <li><a href="/reports/contractors">Contractors</a></li>                                               
                            <li><a href="/reports/discarded_alarms">Discarded Alarms</a></li>                                                
                            <li><a href="/reports/expiring">Expiring Alarms</a></li>   
                            <li><a href="/reports/installed_alarms">Installed Alarms</a></li>       
                            <li><a href="/reports/key_tracking">Key Tracking Report</a></li>                                      
                            <li><a href="/reports/kpi">KPIs</a></li>
                            <li><a href="/jobs/missed_jobs">Missed Jobs</a></li>
                            <li><a href="/reports/purchase_order">Purchase Orders</a></li>
                            <li><a href="/reports/region_numbers">Region Numbers</a></li>
                            <li><a href="/jobs/status">Status Report</a></li>                                                                       
                            <li><a href="/stock/stock_items">Stock Items</a></li>
                            <li><a href="/stock/suppliers">Suppliers</a></li>
                            <li><a href="/reports/tech_break_report">Tech Break Report</a></li>
                            <li><a href="/stock/tech_stock">Tech Stock Report</a></li>
                            <li><a href="/daily/postponed_overdue_jobs">Postponed Overdue Jobs</a></li>   
                        </ul>
                    </div>
                </section>
                <!--  Operations Report End  -->



            </div>


            <!-- Accounts Reports  -->
            <div class="col-md-6 columns">

                <section class="widget widget-reports">
                    <header class="widget-header widget-header-blue">
                        <em class="fa fa-pencil-square-o"></em> Accounts Reports 
                    </header>
                    <div>
                        <ul>
                            <li><a href="/reports/aged_debtors">Aged Debtors Report</a></li>
                            <li><a href="/credit/credit_request_summary">Credit Request Summary</a></li>
                            <li><a href="/reports/credits">Credits Summary Report</a></li>
                            <li><a href="/reports/debtors">Debtors Report</a></li>
                            <li><a href="reports/view_expense_summary">Expense Claims Summary</a></li>
                            <!--<li><a href="<?php // echo $this->config->item('crm_link') ?>/expense_summary.php">Expense Claims Summary (OLD)</a></li>-->
                            <li><a href="/jobs/invoiced_jobs">Invoiced Jobs</a></li>                                                                                                                                                               
                            <li><a href="/properties/nlm_properties">NLM Properties</a></li>
                            <li><a href="/reports/payments">Payment Summary Report</a></li> 
                            <li><a href="/credit/refund_request_summary">Refund Request Summary</a></li>                                                               
                            <li><a href="/reports/overdue_invoices">Overdue Invoices</a></li>                                                               
                        </ul>
                    </div>
                </section>   


                <section class="widget widget-reports">
                    <header class="widget-header widget-header-blue">
                        <em class="fa fa-pencil-square-o"></em> Vehicle Reports 
                    </header>
                    <div>
                        <ul>
                            <li><a href="/vehicles/view_kms">KMS Report</a></li>
                            <li><a href="/vehicles/view_tools">View Tools</a></li>
                            <li><a href="/vehicles/view_vehicles">View Vehicles</a></li>
                        </ul>
                    </div>
                </section>


                <section class="widget widget-reports">
                    <header class="widget-header widget-header-blue">
                        <em class="fa fa-pencil-square-o"></em> General Reports 
                    </header>
                    <div>
                        <ul>
                            <li><a href="/reports/report_admin">Admin Report</a></li>
                            <li><a href="/reports/agency_service_price_report">Agency Service Price Report</a></li>
                            <li><a href="/reports/cron_report">Cron Report</a></li>
                            <li><a href="/reports/figures">Figures</a></li>   
                            <li><a href="/reports/view_icons">Icons</a></li>                            
                            <li><a href="/reports/property_service_price_report">Property Service Price Report</a></li>
                            <li><a href="/reports/user_logs">User Logs</a></li>
                        </ul>
                    </div>
                </section>

                <section class="widget widget-reports">
                    <header class="widget-header widget-header-blue">
                        <em class="fa fa-building-o"></em> Agency Reports 
                    </header>
                    <div>
                        <ul>

                            <li><a href="/agency/view_agencies_and_services">Agencies and Services</a></li> 
                            <li><a href="/reports/agency_expiring_alarms">Agency Expiring Alarms</a></li>
                            <li><a href="/reports/api_tenancy_info">API Tenancy Info</a></li>
                            <?php
                            if( $this->config->item('country') == 1 ){ // AU only ?>
                                <li><a href="/reports/agency_expiring_alarms_hume">Agency Expiring Alarms - Hume</a></li>
                            <?php
                            }
                            ?>                                                                
                            <li><a href="/agency/agency_keys">Agency Keys</a></li>
                            <li><a href="/agency/agency_portal_data">Agency Portal Data</a></li>
                            <li><a href="/agency/services">Agency Services</a></li>
                            <li><a href="/agency/view_deactivated_agencies">Deactivated Agencies</a></li>                                        
                            <!--<li><a href="<?php // echo $this->config->item('crm_link')  ?>/franchise_groups.php">Franchise Groups (OLD)</a></li>-->
                            <li><a href="/reports/view_franchise_groups">Franchise Groups</a></li>
                            <li><a href="/reports/high_touch_agencies">High Touch Agencies</a></li>
                            <?php
                            if( $this->config->item('country') == 1 ){ // AU only ?>
                                <li><a href="/agency/hume_job_logs">Hume Job Logs</a></li>
                            <?php
                            }
                            ?>                                 
                            <li><a href="/agency/maintenance_program_agencies">Maintenance Program Agencies</a></li>
                            <li><a href="/agency/non_auto_renew_agencies">Non Auto Renew Agencies</a></li>
                            <li><a href="/agency/view_price_increase_excluded_agencies">Price Increase Excluded Agencies</a></li>
                            <li><a href="/agency/subscription_billing_agencies">Subscription Billing</a></li>
                            <li><a href="/agency/trust_account_software">Trust Account Software</a></li>                                                                                              

                        </ul>
                    </div>
                </section>

                <section class="widget widget-reports">
                    <header class="widget-header widget-header-blue">
                        <em class="fa fa-pencil-square-o"></em> Sales Reports  
                    </header>
                    <div>
                        <ul>
                            <li><a href="/agency/view_all_agencies">All Agencies</a></li> 
                            <?php
                            $filter_user_arr = [];
                            $display_all = false;

                            if( ENVIRONMENT == 'production' ){ // live

                                if( $this->config->item('country') == 1 ){ // AU

                                    /*
                                    2070 - Developer testing
                                    2025 - Daniel                                    
                                    2287 - Ben Taylor
                                    2364 - Alex
                                    2395 - Antony
                                    */
                        
                                    $filter_user_arr = array(2070, 2025, 2287, 2364, 2395);
                        
                                }else if( $this->config->item('country') == 2 ){ // NZ
                        
                                    /*
                                    2070 - Developer testing
                                    2025 - Daniel
                                    2231 - Ben Taylor 
                                    2271 - Alex
                                    2273 - Antony 
                                    */
                        
                                    $filter_user_arr = array(2070, 2025, 2231, 2271, 2273);
                        
                                }

                            }else{ // dev

                                $display_all = true;

                            }                                
                            if( in_array($this->session->staff_id, $filter_user_arr) || $display_all == true ){ ?>
                                <li><a href="/reports/property_commissions">Property Commissions</a></li>                                                      
                            <?php
                            }
                            ?>                                  
                            <li><a href="/reports/sales_activity">Sales Activity</a></li>
                            <li><a href="/reports/sales_report">Sales Report</a></li>
                            <li><a href="/reports/weekly_sales_report">Sales Rep Report</a></li>
                            <li><a href="/reports/sales_snapshot">Sales Snapshot</a></li>
                            <li><a href="/agency/view_target_agencies">Target Agencies</a></li>                                                                                                 
                        </ul>
                    </div>
                </section>

                <!-- TEST -->
                <?php
                /*
                  if( in_array($this->session->staff_id, $testers_arr) ){ // ?>

                  <section class="widget widget-reports">
                  <header class="widget-header widget-header-blue">
                  <em class="fa fa-pencil-square-o"></em> TEST
                  </header>
                  <div>
                  <ul>
                  <li><a href="/reports/subscription_dates">Subscription Dates</a></li>
                  </ul>
                  </div>
                  </section>

                  <?php
                  }
                 */
                ?>            


            </div>
        </div>




    </div>
</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

<h4>Reports</h4>
<p>
    This page show all reports menu/item in classifications.
</p>

</div>
<!-- Fancybox END -->