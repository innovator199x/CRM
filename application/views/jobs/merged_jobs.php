<div class="box-typical box-typical-padding">
    
    <style>
        .merge_jobs_buttons{
            margin-bottom: 15px;
        }
        #btn_process{
            margin-bottom:15px;
        }
        #btnMarkCompleted {
            float: right;
        }
        #process_div{
            display:none;
        }

        table th a, a:focus, a:hover {
            color: #343434;
        }

        /* Table sort indicators */

        table th a.sortable {
          position: relative;
          cursor: pointer;
        }

        table th a.sortable::after {
          font-family: FontAwesome;
          content: "\f0dc";
          position: block;
          /*left: 40px;*/
          margin-left: 5px;
          color: #999;
          /*bottom: 10px;*/
        }

        table th a.sortable.asc::after {
          content: "\f0d8";
          /*bottom: 10px;*/
        }

        table th a.sortable.desc::after {
          content: "\f0d7";
          /*bottom: 10px;*/
        }

        table th a.sortable:hover::after {
          color: #333;
        }
    </style>

    <?php 
    // breadcrumbs template
    $bc_items = array(
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/jobs/merged_jobs"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>


    <!-- TABS START -->
    <section class="tabs-section">

        <!--.tabs-section-nav start-->
        <div class="tabs-section-nav tabs-section-nav-icons">
            <div class="tbl">
                <ul class="nav" role="tablist" id="main-tab">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $isMergeTab=="true" ? "active" : "" ?>" href="#tabs-1-tab-1" role="tab" data-toggle="tab">
                            <span class="nav-link-in">
                                <i class="fa fa-wrench"></i>
                                Merged Jobs
                                <span class="label label-pill label-danger"><?php echo $total_rows; ?></span>
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $isPmeTab=="true" ? "active" : "" ?>" href="#tabs-1-tab-2" role="tab" data-toggle="tab">
                            <span class="nav-link-in">
                                <span class="fa fa-share-alt"></span>
                                API
                                <span class="label label-pill label-danger">
                                    <?php 
                                        // $cntConnProp = 0;
                                        // if($lists->num_rows()>0){
                                        //     foreach($lists->result_array() as $list_item) {
                                        //         if ($list_item['propertyme_prop_id'] !== NULL) {
                                        //             $cntConnProp++;
                                        //         }
                                        //     }
                                        // }
                                        // echo $cntConnProp;
                                    echo $listsPmeRow;
                                    ?>
                                </span>
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $isDhaTab=="true" ? "active" : "" ?>" href="#tabs-1-tab-3" role="tab" data-toggle="tab">
                            <span class="nav-link-in">
                                <i class="fa fa-wrench"></i>
                                DHA
                                <span class="label label-pill label-danger">0</span>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div><!--.tabs-section-nav end-->

        <div class="tab-content">

            <!-- tab 1 start -->
            <div role="tabpanel" class="tab-pane fade <?php echo $isMergeTab=="true" ? "in active show" : "" ?>" id="tabs-1-tab-1">
                <header class="box-typical-header">
                    <div class="box-typical box-typical-padding">
                    
                        <?php
                    $form_attr = array(
                        'id' => 'jform'
                    );
                    echo form_open('/jobs/merged_jobs',$form_attr);
                    ?>
                        <div class="for-groupss row">
                            <div class="col-md-8 columns">
                                <div class="row">
                                    <div class="col-mdd-3">
                                        <label for="agency_select">Agency</label>
                                        <select id="agency_filter" name="agency_filter" class="form-control field_g2">
                                            <option value="">ALL</option>
                                        </select>
                                        <div class="mini_loader"></div>
                                    </div>

                                    <div class="col-mdd-3">
                                        <label for="jobtype_select">Job Type</label>
                                        <select id="job_type_filter" name="job_type_filter" class="form-control field_g2">
                                            <option value="">ALL</option>
                                        </select>
                                        <div class="mini_loader"></div>
                                    </div>

                                    <div class="col-mdd-3">
                                        <label for="service_select">Service</label>
                                        <select id="service_filter" name="service_filter" class="form-control field_g2">
                                            <option value="">ALL</option>
                                        </select>
                                        <div class="mini_loader"></div>
                                    </div>

                                    <div class="col-mdd-3">
                                        <label for="service_select"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></label>
                                        <select id="state_filter" name="state_filter" class="form-control">
                                            <option value="">ALL</option>
                                        </select>
                                        <div class="mini_loader"></div>
                                    </div>

                                    <div class="col-mdd-3">
                                        <label for="date_select">Date</label>
                                        <input placeholder="ALL" name="date_filter" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $this->input->get_post('date'); ?>">
                                    </div>

                                    <div class="col-mdd-3">
                                        <label for="phrase_select">Phrase</label>
                                        <input placeholder="ALL" type="text" name="search_filter" class="form-control" value="<?php echo $this->input->get_post('search_filter'); ?>" />
                                    </div>
                                    <input type="hidden" name="isPmeTab" value="false">
                                    <input type="hidden" name="isMergeTab" value="true">
                                    <input type="hidden" name="isDhaTab" value="false">
                                    <div class="col-md-1 columns">
                                        <label class="col-sm-12 form-control-label">&nbsp;</label>
                                        <button type="submit" class="btn btn-inline">Search</button>
                                    </div>
                                    
                                </div>

                            </div>

                            <div class="col-md-1 columns">
                                    <div class="col-md-1 columns">
                                        <label class="col-sm-12 form-control-label">&nbsp;</label>
                                        <button id="btn_process" class="btn btn-success" type="button">Process</button>
                                    </div>
                            </div>

                            <!--
                            <div class="col-md-3 columns text-right">
                                <?php 

                                    $ue_sql = $this->db->select('cron_merged_cert, cron_merge_sms')->from('crm_settings')->where(array('country_id'=>$this->config->item('country')))->get();
                                    $ue = $ue_sql->row_array();
                                    $ae_val = $ue['cron_merged_cert'];
                                    if( $ae_val==1 ){
                                        $ae_txt = 'Active';
                                        $ae_color = 'green';
                                        $is_checked = 'checked="checked"';
                                    }else{
                                        $ae_txt = 'Inactive';
                                        $ae_color = 'red';
                                        $is_checked = '';
                                    }         
                                    $ae_val = $ue['cron_merge_sms'];
                                    if( $ae_val==1 ){
                                        $ae_txt_sms = 'Active';
                                        $ae_color_sms = 'green';
                                        $is_checked_sms = 'checked="checked"';
                                    }else{
                                        $ae_txt_sms = 'Inactive';
                                        $ae_color_sms = 'red';
                                        $is_checked_sms = '';
                                    }               
                                ?>
                                <div class="checkbox" style="margin-top: 10px;">
                                <input type="checkbox" id="chk_cron_merged_cert_toggle" <?php echo $is_checked; ?> /> 
                                    <label for="chk_cron_merged_cert_toggle"> <span style="color:<?php echo $ae_color; ?>">Auto Emails <?php echo $ae_txt; ?></span></label>
                                </div>  
                                <div class="checkbox" >
                                <input type="checkbox" id="chk_cron_merged_sms_toggle" <?php echo $is_checked_sms; ?> /> 
                                    <label for="chk_cron_merged_sms_toggle"> <span style="color:<?php echo $ae_color_sms; ?>">Auto SMS <?php echo $ae_txt_sms == "Inactive" ? $ae_txt_sms . "&nbsp;" : $ae_txt_sms ."&nbsp;&nbsp;&nbsp;"; ?> </span></label>
                                </div>  

                            </div>
                            -->

                        </div>
                        </form>
                    </div>

                <section class="merge_jobs_buttons">
                    <div class="row">
                        <!-- <div class="col-md-12 columns"><button id="btn_process" class="btn" type="button">Process</button></div> -->
                        <div class="col-md-12 columns">
                            <div id="process_div">
                                <button type="button" id="iden_btn_email" <?php echo($email_stats[0]['result']==$email_stats[1]['result'])?'class="breadcrumb-active btn btn-success email_invoice_btn"':'class="bnt_email_all_certi_invoice btn email_invoice_btn"'; ?>>Email Certs/Invoices (<span id="span_email_all_certi_invoice"><?php echo $email_stats[0]['result'] . "</span>/<span id='adjustToBeEmail'></span>" ?> Sent)</button>

                            <button type="button"  <?php echo($listsPmeRowSent==$listsPmeRow)?'class="breadcrumb-active btn btn-success post_api_invoice_btn"':'class="bnt_pme_all_certi_invoice btn post_api_invoice_btn"'; ?>>Push API Certs/Invoices (<span lid="span_pme_all_certi_invoice"><?php echo $listsPmeRowSent . "</span>/" . $listsPmeRow; ?> Sent)</button>
                            
                                <button type="button" class="breadcrumb-active btn btn_sms_tenant" id="iden_btn_sms_tenant">                                    
                                    SMS Sent: <?php echo $this->jobs_model->merge_sms_sent_count(); ?> Normal; <?php echo $this->jobs_model->merge_api_sms_sent_count(); ?> API
                                </button>
                                <input type="hidden" id="smsMergeJobCount" value="<?php echo $email_stats[1]['result']; ?>">
                                <a href="<?=base_url() . "jobs/export_myob"?>" class="btn">MYOB Export</a>
                                <button id="btnMarkCompleted" type="button" <?php echo ($lists->num_rows()==0)?'class="breadcrumb-active btn btn-success"':'class="btn"'; ?>>Mark ALL Completed</button>
                            </div>
                        </div>
                    </div>
                </section>
                </header>
                <section>
                    <div class="body-typical-body">
                        <div class="table-responsive">
                            <table class="table table-hover main-table mergedJobsTable">
                                <thead>
                                    <?php 
                                        $sort = ($sort == 'DESC') ? 'ASC' : 'DESC';
                                    ?>
                                    <tr>
                                        <th><a href="<?=$page_search_url?>&order_by=j.date&sort=<?=$sort?>" class="sortable <?php 
                                            if($order_by == 'j.date') {
                                                    $is_sort = ($sort == 'ASC') ? 'desc' : 'asc';
                                                    echo $is_sort;
                                            }else {
                                                echo '';
                                            }
                                        ?>">Date</a></th>
                                        <th><a href="<?=$page_search_url?>&order_by=j.job_type&sort=<?=$sort?>" class="sortable <?php 
                                            if($order_by == 'j.job_type') {
                                                    $is_sort = ($sort == 'ASC') ? 'desc' : 'asc';
                                                    echo $is_sort;
                                            }else {
                                                echo '';
                                            }
                                        ?>">Job Type</a></th>
                                        <th><a href="<?=$page_search_url?>&order_by=j.created&sort=<?=$sort?>" class="sortable <?php 
                                            if($order_by == 'j.created') {
                                                    $is_sort = ($sort == 'ASC') ? 'asc' : 'desc';
                                                    echo $is_sort;
                                            }else {
                                                echo '';
                                            }
                                        ?>">Age</a></th>
                                        <th><a href="<?=$page_search_url?>&order_by=j.service&sort=<?=$sort?>" class="sortable <?php 
                                            if($order_by == 'j.service') {
                                                    $is_sort = ($sort == 'ASC') ? 'desc' : 'asc';
                                                    echo $is_sort;
                                            }else {
                                                echo '';
                                            }
                                        ?>">Service</a></th>
                                        <th><a href="<?=$page_search_url?>&order_by=j.job_price&sort=<?=$sort?>" class="sortable <?php 
                                            if($order_by == 'j.job_price') {
                                                    $is_sort = ($sort == 'ASC') ? 'desc' : 'asc';
                                                    echo $is_sort;
                                            }else {
                                                echo '';
                                            }
                                        ?>">Price</a></th>
                                        <th><a href="<?=$page_search_url?>&order_by=p.address_1&sort=<?=$sort?>" class="sortable <?php 
                                            if($order_by == 'p.address_1') {
                                                    $is_sort = ($sort == 'ASC') ? 'desc' : 'asc';
                                                    echo $is_sort;
                                            }else {
                                                echo '';
                                            }
                                        ?>">Address</a></th>
                                        <th><a href="<?=$page_search_url?>&order_by=p.state&sort=<?=$sort?>" class="sortable <?php 
                                            if($order_by == 'p.state') {
                                                    $is_sort = ($sort == 'ASC') ? 'desc' : 'asc';
                                                    echo $is_sort;
                                            }else {
                                                echo '';
                                            }
                                        ?>"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></a></th>
                                        <th><a href="<?=$page_search_url?>&order_by=a.agency_name&sort=<?=$sort?>" class="sortable <?php 
                                            if($order_by == 'a.agency_name') {
                                                    $is_sort = ($sort == 'ASC') ? 'desc' : 'asc';
                                                    echo $is_sort;
                                            }else {
                                                echo '';
                                            }
                                        ?>">Agency</a></th>
                                        <th><a href="<?=$page_search_url?>&order_by=j.id&sort=<?=$sort?>" class="sortable <?php 
                                            if($order_by == 'j.id') {
                                                    $is_sort = ($sort == 'ASC') ? 'desc' : 'asc';
                                                    echo $is_sort;
                                            }else {
                                                echo '';
                                            }
                                        ?>">Job#</a></th>
                                        <th class="text-center"><a href="<?=$page_search_url?>&order_by=j.client_emailed&sort=<?=$sort?>" class="sortable <?php 
                                            if($order_by == 'j.client_emailed') {
                                                    $is_sort = ($sort == 'ASC') ? 'desc' : 'asc';
                                                    echo $is_sort;
                                            }else {
                                                echo '';
                                            }
                                        ?>">Invoice Sent</a></th>
                                        <th class="text-center"><a href="<?=$page_search_url?>&order_by=j.sms_sent_merge&sort=<?=$sort?>" class="sortable <?php 
                                            if($order_by == 'j.sms_sent_merge') {
                                                    $is_sort = ($sort == 'ASC') ? 'desc' : 'asc';
                                                    echo $is_sort;
                                            }else {
                                                echo '';
                                            }
                                        ?>">SMS Sent</a></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php
                                        if($lists->num_rows()>0){
                                            $countNotSms = 0;
                                            $countNoEmail = 0;
                                        
                                        /*
                                        echo "<pre>";
                                        print_r($lists_all->result_array());
                                        echo "<pre />";
                                        */
                                        
                                    foreach ($lists_all->result_array() as $val) { 

                                        $has_blue_tick = false;

                                        if (stristr($val['account_emails'], "@")) {
                                            if ($val['client_emailed'] != NULL) {} else {

                                                // PMe billable check
                                                $CI =& get_instance();
                                                $CI->load->model('/Pme_model');
                                                $pt_sql = $CI->Pme_model->check_if_present_in_pme_tab($val['jid']);

                                                if ($pt_sql) {
                                                    //$countNoEmail++;
                                                    $has_blue_tick = true;
                                                }
                                                
                                                // check if agency has maintenance program
                                                $agency_has_mm = $this->system_model->check_agency_has_mm($val["a_id"]);                                                
                                                if( $agency_has_mm == true ){
                                                    //$countNoEmail++;
                                                    $has_blue_tick = true;
                                                }  

                                                // exclude free invoice
                                                if( $val['exclude_free_invoices'] == 1 && $val['invoice_amount'] == 0 ){
                                                    $has_blue_tick = true;
                                                }

                                                if( $has_blue_tick == true ){
                                                    $countNoEmail++;
                                                }

                                            }
                                        } else {
                                        }

                                        $pt_params = array( 
                                            'property_id' => $val['prop_id'],
                                            'active' => 1
                                        );

                                        $CI =& get_instance();
                                        $CI->load->model('/inc/job_functions_model');
                                        $pt_sql = $CI->job_functions_model->getNewTenantsData($pt_params);
                                        $doNotSms = false;
                                        foreach($pt_sql->result_array() as $pt_row){
                                            if( $pt_row['tenant_mobile'] != "" && $pt_row['tenant_firstname'] == $val['booked_with'] ){ 
                                                $doNotSms = false;
                                                break;
                                            }else {
                                                if($val['booked_with'] == "Agent" && $val['door_knock'] == 1 ) {
                                                    $doNotSms = false;
                                                    break;
                                                }else {
                                                    $doNotSms = true;
                                                }
                                            }
                                        }

                                        if ($val['assigned_tech'] == 1  || $val['assigned_tech'] == 2 ) {
                                            $doNotSms = true;
                                        }
                                        if ($doNotSms) {
                                            $countNotSms++;
                                        }
                                    }
                                    
                                    foreach($lists->result_array() as $list_item):          
                                    ?>
                                    <tr class="merge_rows normal_tab_rows <?php echo ( $list_item['sms_sent_merge'] !='' )?'sms_sent_flag':null; ?>">
                                        <td>
                                        <?php 
                                        if ($list_item['j_date'] == "0000-00-00") {
                                            echo "";
                                        }else {
                                            echo $this->system_model->formatDate($list_item['j_date'],'d/m/Y'); 
                                        }
                                        ?>
                                        </td>
                                        <td>
                                        <?php echo $this->gherxlib->getJobTypeAbbrv($list_item['j_type']); ?>
                                        </td>
                                        <td>
                                        <?php
                                        echo $this->gherxlib->getAge($list_item['j_created']);
                                        ?>
                                        </td>
                                        <td>
                                        <img data-toggle="tooltip" title="<?php echo $list_item['ajt_type'] ?>" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($list_item['j_service']); ?>" />
                                        </td>
                                        <td>
                                        <?php
                                       // $price =  $this->system_model->getJobAmountGrandTotal($list_item['jid'], $this->config->item('country')); 
                                        //echo "$".number_format($price,2);
                                        //echo '$'.number_format($this->system_model->price_ex_gst($price),2);

                                        $new_price_var_param = array(
                                            'service_type' => $list_item['j_service'],
                                            'job_id' => $list_item['jid'],
                                            'property_id' => $list_item['prop_id']
                                        );
                                        $new_price = $this->system_model->get_job_variations_v2($new_price_var_param);

                                        $merge_price_ex_gst = number_format($this->system_model->price_ex_gst($new_price['total_price_including_variations']),2);
                                        echo '$'.$merge_price_ex_gst;
                                        ?>
                                        </td>
                                        <td>
                                        <?php 
                                        /*
                                        <a href="<?php echo base_url('/properties/view_property_details')."/".$list_item["prop_id"]?>"><?php echo $list_item['p_address_1']." ".$list_item['p_address_2']." ".$list_item['p_address_3']; ?></a>
                                        */
                                        $prop_address = $list_item['p_address_1']." ".$list_item['p_address_2'].", ".$list_item['p_address_3'];
                                        echo $this->gherxlib->crmLink('vpd',$list_item['prop_id'],$prop_address);

                                        ?>
                                        </td>
                                        <td>
                                        <?php echo $list_item['p_state']; ?>
                                        </td>
                                        <td>
                                            <a href='/agency/view_agency_details/<?=$list_item["a_id"]?>'><?php echo $list_item['agency_name']; ?></a>
                                        </td>
                                        <td>
                                        <?php
                                        /*
                                        echo '<a href="'.base_url("/jobs/view_job_details/{$list_item['jid']}").'">'.$list_item['jid'].'</a>' 
                                        */
                                        echo $this->gherxlib->crmLink('vjd',$list_item['jid'],$list_item['jid']);
                                        ?>
                                        </td>
                                    <td class="text-center chops1">
                                        <?php
                                            $reason_email = [];
                                            if (stristr($list_item['account_emails'], "@")) {
                                                if ($list_item['client_emailed'] != NULL) {
                                                    echo "<span class='text-green fa fa-check'></span>";
                                                } else {                         

                                                    // check if agency has maintenance program
                                                    $agency_has_mm = $this->system_model->check_agency_has_mm($list_item["a_id"]);
                                                    if( $agency_has_mm == true ){
                                                        $reason_email[] = 'Needs processing on MM Precom';
                                                    } 
                                                    
                                                    // exclude free invoice
                                                    if( $list_item['exclude_free_invoices'] == 1 && $list_item['invoice_amount'] == 0 ){
                                                        $reason_email[] = 'Free Invoice';
                                                    }

                                                    if( count($reason_email) > 0 ){
                                                        $reason_email_imp = implode(" and ", $reason_email);
                                                        echo "<span class='text-blue fa fa-check' style='color: #0747a6' data-toggle='tooltip' data-placement='top' title='{$reason_email_imp}'>&nbsp;</span>";
                                                    }else{
                                                        echo "<span class='text-grey fa fa-check'></span>";
                                                    }

                                                }
                                            } else {
                                                echo "N/A";
                                            }
                                        ?>
                                        </td>

                                        <td class="text-center">

                                            <?php 
                                                $doNotSms = false;    
                                                $reasonSms = array();                                                

                                                $pt_params = array( 
                                                    'property_id' => $list_item['prop_id'],
                                                    'active' => 1
                                                 );

                                                $CI =& get_instance();
                                                $CI->load->model('/inc/job_functions_model');
                                                $pt_sql = $CI->job_functions_model->getNewTenantsData($pt_params);                                               

                                                if( $pt_sql->num_rows() > 0 ){

                                                    foreach($pt_sql->result_array() as $pt_row){
                                                        if( $pt_row['tenant_mobile'] != "" && $pt_row['tenant_firstname'] == $list_item['booked_with'] ){ 
                                                            $doNotSms = false;
                                                            break;
                                                        }else {
                                                            if($list_item['booked_with'] == "Agent" && $list_item['door_knock'] == 1 ) {
                                                                $doNotSms = false;
                                                                break;
                                                            }else {
                                                                $doNotSms = true;
                                                            }
                                                        }
                                                    }
                                                    
                                                }else{
                                                    array_push($reasonSms, "No Tenants");
                                                    $doNotSms = true;
                                                }

                                                if ($list_item['assigned_tech'] == 1) {
                                                    array_push($reasonSms, "Tech assigned to Other Supplier");
                                                    $doNotSms = true;
                                                }
                                                if ($list_item['assigned_tech'] == 2) {
                                                    array_push($reasonSms, "Tech assigned to Upfront Bill");
                                                    $doNotSms = true;
                                                }                                                      


                                                if ($doNotSms) {
                                                    array_push($reasonSms, "Tenants does not have # and job not book with tenant");
                                                    $resonString = implode(" and ", $reasonSms);
                                                    echo "<span class='text-blue fa fa-check sms_tick_blue' style='color: #0747a6' data-toggle='tooltip' data-placement='top' title='{$resonString}'>&nbsp;</span>";
                                                    // $countNotSms++;
                                                }else {
                                                    echo ( date("Y-m-d",strtotime($list_item['sms_sent_merge']))==date("Y-m-d") )?"<span class='text-green fa fa-check sms_tick_green'>&nbsp;</span>":"<span class='text-grey fa fa-check sms_tick_grey'>&nbsp;</span>";
                                                }
                                            ?>

                                        </td>
                                        
                                    </tr>
                                    <?php endforeach;
                                    ?>
                                    <tr>
                                        <td colspan="2" style="text-align: center;"><strong>TOTAL</strong></td>
                                        <td style="text-align:left;">
                                           &nbsp;
                                        </td>
                                        <td>&nbsp;</td>
                                        <td style="text-align:left;">
                                        <?php
                                         //echo '$'.number_format($this->system_model->price_ex_gst($final_total),2); 
                                        echo '$'.number_format($mer_total_price_ex_gst,2); 
                                        ?>
                                        </td>
                                        <td colspan="6"></td>
                                    </tr>
                                    <?php
                                        }else{
                                            echo "<tr><td colspan='11'>No Data</td></tr>";
                                        }
                                    ?>
                                </tbody>

                            </table>
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
            <!-- tab 1 end -->

            <!-- tab 2 start -->
            <div role="tabpanel" class="tab-pane fade <?php echo $isPmeTab=="true" ? "in active show" : "" ?>" id="tabs-1-tab-2">
                
                <section class="merge_jobs_buttons">
                    <div class="row">
                        <div class="col-md-9 columns">
                            <button type="button"  <?php echo($listsPmeRowSent==$listsPmeRow)?'class="breadcrumb-active btn btn-success"':'class="bnt_pme_all_certi_invoice btn"'; ?>>Push API Certs/Invoices (<span lid="span_pme_all_certi_invoice"><?php echo $listsPmeRowSent . "</span>/" . $listsPmeRow; ?> Sent)</button>
                        </div>
                        
                        <!--
                        <div class="col-md-3 columns text-right">
                            <?php 

                                $ue_sql = $this->db->select('cron_pme_upload')->from('crm_settings')->where(array('country_id'=>$this->config->item('country')))->get();
                                $ue = $ue_sql->row_array();
                                $ae_val = $ue['cron_pme_upload'];
                                if( $ae_val==1 ){
                                    $ae_txt = 'Active';
                                    $ae_color = 'green';
                                    $is_checked = 'checked="checked"';
                                }else{
                                    $ae_txt = 'Inactive';
                                    $ae_color = 'red';
                                    $is_checked = '';
                                }                    
                            ?>
                            <div class="checkbox" style="margin-top: 10px;">
                            <input type="checkbox" id="chk_cron_pme_upload_toggle" <?php echo $is_checked; ?> /> 
                                <label for="chk_cron_pme_upload_toggle"> <span style="color:<?php echo $ae_color; ?>">Auto Upload <?php echo $ae_txt; ?></span></label>
                            </div>  

                        </div>
                        -->

                    </div>
                </section>
                <header class="box-typical-header">
                    <div class="box-typical box-typical-padding">
                    
                        <?php
                    $form_attr = array(
                        'id' => 'jform'
                    );
                    echo form_open('/jobs/merged_jobs',$form_attr);
                    ?>
                        <div class="for-groupss row">
                            <div class="col-md-8 columns">
                                <div class="row">
                                    <div class="col-mdd-3">
                                        <label for="agency_select_pme">Agency</label>
                                        <select id="agency_filter_pme" name="agency_filter_pme" class="form-control field_g2">
                                            <option value="">ALL</option>
                                        </select>
                                        <div class="mini_loader_pme"></div>
                                    </div>

                                    <div class="col-mdd-3">
                                        <label for="jobtype_select_pme">Job Type</label>
                                        <select id="job_type_filter_pme" name="job_type_filter_pme" class="form-control field_g2">
                                            <option value="">ALL</option>
                                        </select>
                                        <div class="mini_loader_pme"></div>
                                    </div>

                                    <div class="col-mdd-3">
                                        <label for="service_select_pme">Service</label>
                                        <select id="service_filter_pme" name="service_filter_pme" class="form-control field_g2">
                                            <option value="">ALL</option>
                                        </select>
                                        <div class="mini_loader_pme"></div>
                                    </div>

                                    <div class="col-mdd-3">
                                        <label for="service_select_pme"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></label>
                                        <select id="state_filter_pme" name="state_filter_pme" class="form-control">
                                            <option value="">ALL</option>
                                        </select>
                                        <div class="mini_loader_pme"></div>
                                    </div>

                                    <div class="col-mdd-3">
                                        <label for="date_select_pme">Date</label>
                                        <input placeholder="ALL" name="date_filter_pme" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $this->input->get_post('date'); ?>">
                                    </div>

                                    <div class="col-mdd-3">
                                        <label for="phrase_select">Phrase</label>
                                        <input placeholder="ALL" type="text" name="search_filter_pme" class="form-control" value="<?php echo $this->input->get_post('search_filter_pme'); ?>" />
                                    </div>
                                    <input type="hidden" name="isPmeTab" value="true">
                                    <input type="hidden" name="isMergeTab" value="false">
                                    <input type="hidden" name="isDhaTab" value="false">
                                    <div class="col-md-1 columns">
                                        <label class="col-sm-12 form-control-label">&nbsp;</label>
                                        <button type="submit" class="btn btn-inline">Search</button>
                                    </div>
                                    
                                </div>

                            </div>
                            <div class="col-md-4 columns text-right">
                                <?php 

                                    // $ue_sql = $this->db->select('cron_merged_cert')->from('crm_settings')->where(array('country_id'=>$this->config->item('country')))->get();
                                    // $ue = $ue_sql->row_array();
                                    // $ae_val = $ue['cron_merged_cert'];
                                    // if( $ae_val==1 ){
                                    //     $ae_txt = 'Active';
                                    //     $ae_color = 'green';
                                    //     $is_checked = 'checked="checked"';
                                    // }else{
                                    //     $ae_txt = 'Inactive';
                                    //     $ae_color = 'red';
                                    //     $is_checked = '';
                                    // }                   
                                ?>
                                <!-- <div class="checkbox" style="margin-top:30px;">
                                <input type="checkbox" id="chk_cron_merged_cert_toggle" <?php echo $is_checked; ?> /> 
                                    <label for="chk_cron_merged_cert_toggle"> <span style="color:<?php echo $ae_color; ?>">Auto Send to PMe <?php echo $ae_txt; ?></span></label>
                                </div> -->  

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
                                    <?php 
                                        $sort_pme = ($sort_pme == 'DESC') ? 'ASC' : 'DESC';
                                    ?>
                                    <tr>
                                        <th><a href="<?=$page_search_url?>&order_by_pme=j.date&sort_pme=<?=$sort_pme?>" class="sortable <?php 
                                            if($order_by_pme == 'j.date') {
                                                    $is_sort = ($sort_pme == 'ASC') ? 'desc' : 'asc';
                                                    echo $is_sort;
                                            }else {
                                                echo '';
                                            }
                                        ?>">Date</a></th>
                                        <th><a href="<?=$page_search_url?>&order_by_pme=j.job_type&sort_pme=<?=$sort_pme?>" class="sortable <?php 
                                            if($order_by_pme == 'j.job_type') {
                                                    $is_sort = ($sort_pme == 'ASC') ? 'desc' : 'asc';
                                                    echo $is_sort;
                                            }else {
                                                echo '';
                                            }
                                        ?>">Job Type</a></th>
                                        <th><a href="<?=$page_search_url?>&order_by_pme=j.created&sort_pme=<?=$sort_pme?>" class="sortable <?php 
                                            if($order_by_pme == 'j.created') {
                                                    $is_sort = ($sort_pme == 'ASC') ? 'asc' : 'desc';
                                                    echo $is_sort;
                                            }else {
                                                echo '';
                                            }
                                        ?>">Age</a></th>
                                        <th><a href="<?=$page_search_url?>&order_by_pme=j.service&sort_pme=<?=$sort_pme?>" class="sortable <?php 
                                            if($order_by_pme == 'j.service') {
                                                    $is_sort = ($sort_pme == 'ASC') ? 'desc' : 'asc';
                                                    echo $is_sort;
                                            }else {
                                                echo '';
                                            }
                                        ?>">Service</a></th>
                                        <th><a href="<?=$page_search_url?>&order_by_pme=j.job_price&sort_pme=<?=$sort_pme?>" class="sortable <?php 
                                            if($order_by_pme == 'j.job_price') {
                                                    $is_sort = ($sort_pme == 'ASC') ? 'desc' : 'asc';
                                                    echo $is_sort;
                                            }else {
                                                echo '';
                                            }
                                        ?>">Price</a></th>
                                        <th><a href="<?=$page_search_url?>&order_by_pme=p.address_1&sort_pme=<?=$sort_pme?>" class="sortable <?php 
                                            if($order_by_pme == 'p.address_1') {
                                                    $is_sort = ($sort_pme == 'ASC') ? 'desc' : 'asc';
                                                    echo $is_sort;
                                            }else {
                                                echo '';
                                            }
                                        ?>">Address</a></th>
                                        <th><a href="<?=$page_search_url?>&order_by_pme=p.state&sort_pme=<?=$sort_pme?>" class="sortable <?php 
                                            if($order_by_pme == 'p.state') {
                                                    $is_sort = ($sort_pme == 'ASC') ? 'desc' : 'asc';
                                                    echo $is_sort;
                                            }else {
                                                echo '';
                                            }
                                        ?>"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></a></th>
                                        <th><a href="<?=$page_search_url?>&order_by_pme=a.agency_name&sort_pme=<?=$sort_pme?>" class="sortable <?php 
                                            if($order_by_pme == 'a.agency_name') {
                                                    $is_sort = ($sort_pme == 'ASC') ? 'desc' : 'asc';
                                                    echo $is_sort;
                                            }else {
                                                echo '';
                                            }
                                        ?>">Agency</a></th>
                                        <th><a href="<?=$page_search_url?>&order_by_pme=j.id&sort_pme=<?=$sort_pme?>" class="sortable <?php 
                                            if($order_by_pme == 'j.id') {
                                                    $is_sort = ($sort_pme == 'ASC') ? 'desc' : 'asc';
                                                    echo $is_sort;
                                            }else {
                                                echo '';
                                            }
                                        ?>">Job#</a></th>
                                        <th class="text-center"><a href="<?=$page_search_url?>&order_by_pme=api_logs.status&sort_pme=<?=$sort_pme?>" class="sortable <?php 
                                            if($order_by_pme == 'api_logs.status') {
                                                    $is_sort = ($sort_pme == 'ASC') ? 'desc' : 'asc';
                                                    echo $is_sort;
                                            }else {
                                                echo '';
                                            }
                                        ?>">Invoice Sent</a></th>
                                        <th class="text-center"><a href="<?=$page_search_url?>&order_by=j.sms_sent_merge&sort=<?=$sort?>" class="sortable <?php 
                                            if($order_by == 'j.sms_sent_merge') {
                                                    $is_sort = ($sort == 'ASC') ? 'desc' : 'asc';
                                                    echo $is_sort;
                                            }else {
                                                echo '';
                                            }
                                        ?>">SMS Sent</a></th>
                                    </tr>
                                </thead>


                                <tbody>
                                    <?php
                                        if($listsPme->num_rows()>0){
                                            
                                            /*
                                            echo "<pre>";
                                            print_r($listsPme->result_array());
                                            echo "<pre />";
                                            */
                                            
                                    $total_api_price = 0;
                                    foreach($listsPme->result_array() as $list_item):  

                                    ?>
                                    <tr class="merge_rows api_tab_rows pme-table <?php echo ( $list_item['sms_sent_merge'] !='' )?'sms_sent_flag':null; ?>">
                                        <td>
                                        <?php 
                                        if ($list_item['j_date'] == "0000-00-00") {
                                            echo "";
                                        }else {
                                            echo $this->system_model->formatDate($list_item['j_date'],'d/m/Y');
                                        }
                                        ?>
                                        </td>
                                        <td>
                                        <?php echo $this->gherxlib->getJobTypeAbbrv($list_item['j_type']); ?>
                                        </td>
                                        <td>
                                        <?php
                                        echo $this->gherxlib->getAge($list_item['j_created']);
                                        ?>
                                        </td>
                                        <td>
                                        <img data-toggle="tooltip" title="<?php echo $list_item['ajt_type'] ?>" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($list_item['j_service']); ?>" />
                                        </td>
                                        <td>
                                        <?php
                                        //$price =  $this->system_model->getJobAmountGrandTotal($list_item['jid'], $this->config->item('country')); 
                                        //echo "$".number_format($price,2);
                                        //echo '$'.number_format($this->system_model->price_ex_gst($price),2);

                                        $new_price_var_param = array(
                                            'service_type' => $list_item['j_service'],
                                            'job_id' => $list_item['jid'],
                                            'property_id' => $list_item['prop_id']
                                        );
                                        $new_price = $this->system_model->get_job_variations_v2($new_price_var_param);

                                        $new_api_price_ex_gst = number_format($this->system_model->price_ex_gst($new_price['total_price_including_variations']),2);
                                        echo '$'.$new_api_price_ex_gst;
                                        $total_api_price += $new_api_price_ex_gst;
                                        ?>
                                        </td>
                                        <td>
                                        <?php 
                                        /*
                                        <a href="<?php echo base_url('/properties/view_property_details')."/".$list_item["prop_id"]?>"><?php echo $list_item['p_address_1']." ".$list_item['p_address_2']." ".$list_item['p_address_3']; ?></a>
                                        */

                                        if( $list_item['pme_prop_id'] != '' && $list_item['pme_api'] == 1 ) { // PMe
                                            $bubTxt = ' <span class="badge badge-primary">PropertyMe</span>';
                                        }else if( $list_item['palace_prop_id'] != '' && $list_item['palace_api'] == 4 ) { // palace
                                            $bubTxt = ' <span class="badge badge-primary">Palace</span>';
                                        }else if( !is_null($list_item['crm_prop_id']) && $list_item['crm_prop_id'] !== '' ) { // console
                                            $bubTxt = ' <span class="badge badge-primary">Console</span>';
                                        }else if( !is_null($list_item['api']) && $list_item['api'] !== '' && $list_item['api'] == 6) { // ourtradie
                                            $bubTxt = ' <span class="badge badge-primary">OurTradie</span>';
                                        }
                                        else {
                                            $bubTxt = ' <span class="badge badge-primary">Error</span>';
                                        }

                                        $prop_address = $list_item['p_address_1']." ".$list_item['p_address_2'].", ".$list_item['p_address_3'] . $bubTxt;
                                        echo $this->gherxlib->crmLink('vpd',$list_item['prop_id'],$prop_address);

                                        ?>
                                        </td>
                                        <td>
                                        <?php echo $list_item['p_state']; ?>
                                        </td>
                                        <td>
                                            <a href='/agency/view_agency_details/<?=$list_item["a_id"]?>'><?php echo $list_item['agency_name']; ?></a>
                                        </td>
                                        <td>
                                        <?php
                                        /*
                                        echo '<a href="'.base_url("/jobs/view_job_details/{$list_item['jid']}").'">'.$list_item['jid'].'</a>' 
                                        */
                                        echo $this->gherxlib->crmLink('vjd',$list_item['jid'],$list_item['jid']);
                                        ?>
                                        </td>
                                        <td class="text-center chops">
                                            <?php 
                                                // $CI =& get_instance();
                                                // $CI->load->model('/inc/pdf_template');
                                                // if ($CI->pdf_template->check_api_logs_by_JobId($list_item['jid'])) {

                                                if( $list_item['pme_prop_id'] != '' && $list_item['pme_api'] == 1 ) { // PMe
                                                    $invoice_upload = $list_item['is_pme_invoice_upload'];
                                                    $bill_create = $list_item['is_pme_bill_create'];
                                                }else if( $list_item['palace_prop_id'] != '' && $list_item['palace_api'] == 4 ) { // palace
                                                    $invoice_upload = $list_item['is_palace_invoice_upload'];
                                                    $bill_create = $list_item['is_palace_bill_create'];
                                                }else if( !is_null($list_item['crm_prop_id']) && $list_item['crm_prop_id'] !== '' ) { // console
                                                    $invoice_upload = $list_item['api_inv_uploaded'];
                                                    $bill_create = $list_item['api_cert_uploaded'];
                                                }else {
                                                    $invoice_upload = "";
                                                    $bill_create = "";
                                                }

                                                
                                                
                                                if ($invoice_upload == 1 || $bill_create == 1){
                                                    echo '<span class="text-green fa fa-check chops"></span>';
                                                }else{
                                                    //echo "<span class='text-grey fa fa-check'></span>";
                                          
                                                    $reason_email = []; 
                                                    
                                                    // exclude free invoice
                                                    if( $list_item['exclude_free_invoices'] == 1 && $list_item['invoice_amount'] == 0 ){
                                                        $reason_email[] = 'Free Invoice';
                                                    }

                                                    if( count($reason_email) > 0 ){
                                                        $reason_email_imp = implode(" and ", $reason_email);
                                                        echo "<span class='text-blue fa fa-check' style='color: #0747a6' data-toggle='tooltip' data-placement='top' title='{$reason_email_imp}'>&nbsp;</span>";
                                                    }else{
                                                        echo "<span class='text-grey fa fa-check'></span>";
                                                    }

                                                }
                                            ?>
                                           
                                        </td>
                                        <td class="text-center">

                                            <?php 
                                                $doNotSms = false;                                            
                                                $reasonSms = array();
                                                
                                                $pt_params = array( 
                                                    'property_id' => $list_item['prop_id'],
                                                    'active' => 1
                                                 );

                                                $CI =& get_instance();
                                                $CI->load->model('/inc/job_functions_model');
                                                $pt_sql = $CI->job_functions_model->getNewTenantsData($pt_params);
                                              
                                                if( $pt_sql->num_rows() > 0 ){

                                                    foreach($pt_sql->result_array() as $pt_row){
                                                        if( $pt_row['tenant_mobile'] != "" && $pt_row['tenant_firstname'] == $list_item['booked_with'] ){ 
                                                            $doNotSms = false;
                                                            break;
                                                        }else {
                                                            if($list_item['booked_with'] == "Agent" && $list_item['door_knock'] == 1 ) {
                                                                $doNotSms = false;
                                                                break;
                                                            }else {
                                                                $doNotSms = true;                                                               
                                                            }
                                                        }
                                                    }

                                                }else{
                                                    array_push($reasonSms, "No Tenants");
                                                    $doNotSms = true;
                                                }    
                                                
                                                
                                                if ($list_item['assigned_tech'] == 1) {
                                                    array_push($reasonSms, "Tech assigned to Other Supplier");
                                                    $doNotSms = true;
                                                }
                                                if ($list_item['assigned_tech'] == 2) {
                                                    array_push($reasonSms, "Tech assigned to Upfront Bill");
                                                    $doNotSms = true;
                                                }                                          


                                                if ($doNotSms) {     
                                                    array_push($reasonSms, "Tenants does not have # and job not book with tenant");                                               
                                                    $resonString = implode(" and ", $reasonSms);
                                                    echo "<span class='text-blue fa fa-check sms_tick_blue' style='color: #0747a6' data-toggle='tooltip' data-placement='top' title='{$resonString}'>&nbsp;</span>";
                                                    // $countNotSms++;
                                                }else {
                                                    echo ( date("Y-m-d",strtotime($list_item['sms_sent_merge']))==date("Y-m-d") )?"<span class='text-green fa fa-check sms_tick_green'>&nbsp;</span>":"<span class='text-grey fa fa-check sms_tick_grey'>&nbsp;</span>";
                                                }
                                            ?>

                                        </td>
                                        
                                    </tr>
                                    <?php endforeach;
                                    ?>
                                    <tr>
                                        <td colspan="2" style="text-align: center;"><strong>TOTAL</strong></td>
                                        <td style="text-align:left;">
                                            &nbsp;
                                        </td>
                                        <td>&nbsp;</td>
                                        <td style="text-align:left;">

                                        <?php 
                                       // echo '$'.number_format($this->system_model->price_ex_gst($final_total_pme),2);
                                       echo '$'.number_format($mer_total_price_ex_gst_api,2);
                                        ?>
                                                
                                    </td>
                                        <td colspan="6"></td>
                                    </tr>
                                    <?php
                                        }else{
                                            echo "<tr><td colspan='11'>No Data</td></tr>";
                                        }
                                    ?>
                                </tbody>

                            </table>
                        </div>

                        <nav class="text-center">
                            <?php echo $paginationPme; ?>
                        </nav>

                        <div class="pagi_count text-center">
                            <?php echo $pagi_count_pme; ?>
                        </div>

                    </div>
                </section>
            </div>
            <!-- tab 2 end -->

            <!-- tab 3 start -->
            <div role="tabpanel" class="tab-pane fade <?php echo $isDhaTab=="true" ? "in active show" : "" ?>" id="tabs-1-tab-3">DHA</div>
            <!-- tab 3 end -->
        </div><!--.tab-content-->

    </section><!--.tabs-section-->
    <!-- TABS END -->


</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>                          
<div id="about_page_fb" class="fancybox" style="display:none;" >

    <h4>Merged Jobs</h4>
    <p>This page displays all jobs that have been completed and are awaiting export, sms, api push and email</p>
    <p>SMS is sent automatically every 60 Minutes</p>
    <p>Certificates are emailed automatically every 60 Minutes</p>
    <p>*59 minute mark, 7am - 6pm NSW time</p>
    <pre>
<code>SELECT `j`.`id` AS `jid`, `j`.`status` AS `j_status`, `j`.`service` AS `j_service`, `j`.`created` AS `j_created`, `j`.`date` AS `j_date`, `j`.`comments` AS `j_comments`, `j`.`job_price` AS `j_price`, `j`.`job_type` AS `j_type`, `j`.`at_myob`, `j`.`sms_sent_merge`, `j`.`client_emailed`, `j`.`booked_with`, `j`.`assigned_tech`, `j`.`job_entry_notice`, `j`.`door_knock`, `j`.`invoice_amount`, `j`.`invoice_balance`, `p`.`property_id` AS `prop_id`, `p`.`address_1` AS `p_address_1`, `p`.`address_2` AS `p_address_2`, `p`.`address_3` AS `p_address_3`, `p`.`state` AS `p_state`, `p`.`postcode` AS `p_postcode`, `p`.`comments` AS `p_comments`, `p`.`propertyme_prop_id`, `a`.`agency_id` AS `a_id`, `a`.`agency_name` AS `agency_name`, `a`.`phone` AS `a_phone`, `a`.`address_1` AS `a_address_1`, `a`.`address_2` AS `a_address_2`, `a`.`address_3` AS `a_address_3`, `a`.`state` AS `a_state`, `a`.`postcode` AS `a_postcode`, `a`.`trust_account_software`, `a`.`tas_connected`, `a`.`send_emails`, `a`.`account_emails`, `a`.`exclude_free_invoices`, `ajt`.`id` AS `ajt_id`, `ajt`.`type` AS `ajt_type`
FROM `jobs` AS `j`
LEFT JOIN `property` AS `p` ON j.`property_id` = p.`property_id`
LEFT JOIN `agency` AS `a` ON  p.`agency_id` = a.`agency_id`
LEFT JOIN `job_type` AS `jt` ON j.`job_type` = jt.`job_type`
LEFT JOIN `alarm_job_type` AS `ajt` ON j.`service` = ajt.`id`
WHERE `j`.`del_job` = 0
AND `p`.`deleted` = 0
AND `a`.`status` = 'active'
AND `a`.`country_id` = <?php echo COUNTRY ?> 
AND `j`.`status` = 'Merged Certificates'
AND `j`.`id` NOT IN('$exclude_jobs_with_pme_connect')</code>
</pre>
</div>
<!-- Fancybox END -->

<script type="text/javascript">

// agency
function run_ajax_agency_filter(){

var json_data = <?php echo $agency_filter_json; ?>;
var searched_val = '<?php echo $this->input->get_post('agency_filter'); ?>';

jQuery('#agency_filter').next('.mini_loader').show();
jQuery.ajax({
    type: "POST",
        url: "/sys/header_filters",
        data: { 
            rf_class: 'jobs',
            header_filter_type: 'agency',
            json_data: json_data,
            searched_val: searched_val
        }
    }).done(function( ret ){    
        jQuery('#agency_filter').next('.mini_loader').hide();
        $('#agency_filter').append(ret);
    });
            
}

// agency pme
function run_ajax_agency_filter_pme(){

var json_data = <?php echo $agency_filter_json; ?>;
var searched_val = '<?php echo $this->input->get_post('agency_filter_pme'); ?>';

jQuery('#agency_filter_pme').next('.mini_loader_pme').show();
jQuery.ajax({
    type: "POST",
        url: "/sys/header_filters",
        data: { 
            rf_class: 'jobs',
            header_filter_type: 'agency',
            json_data: json_data,
            searched_val: searched_val
        }
    }).done(function( ret ){    
        jQuery('#agency_filter_pme').next('.mini_loader_pme').hide();
        $('#agency_filter_pme').append(ret);
    });
            
}

// job type 
function run_ajax_job_filter(){

    var json_data = <?php echo $job_type_filter_json; ?>;
    var searched_val = '<?php echo $this->input->get_post('job_type_filter'); ?>';

    jQuery('#job_type_filter').next('.mini_loader').show();
    jQuery.ajax({
        type: "POST",
            url: "/sys/header_filters",
            data: { 
                rf_class: 'jobs',
                header_filter_type: 'job_type',
                json_data: json_data,
                searched_val: searched_val
            }
        }).done(function( ret ){    
            jQuery('#job_type_filter').next('.mini_loader').hide();
            jQuery('#job_type_filter').append(ret);
        });
                
}

// job type pme
function run_ajax_job_filter_pme(){

    var json_data = <?php echo $job_type_filter_json; ?>;
    var searched_val = '<?php echo $this->input->get_post('job_type_filter_pme'); ?>';

    jQuery('#job_type_filter_pme').next('.mini_loader_pme').show();
    jQuery.ajax({
        type: "POST",
            url: "/sys/header_filters",
            data: { 
                rf_class: 'jobs',
                header_filter_type: 'job_type',
                json_data: json_data,
                searched_val: searched_val
            }
        }).done(function( ret ){    
            jQuery('#job_type_filter_pme').next('.mini_loader_pme').hide();
            jQuery('#job_type_filter_pme').append(ret);
        });
                
}

// service
function run_ajax_service_filter(){

var json_data = <?php echo $service_filter_json; ?>;
var searched_val = '<?php echo $this->input->get_post('service_filter'); ?>';

jQuery('#service_filter').next('.mini_loader').show();
jQuery.ajax({
    type: "POST",
        url: "/sys/header_filters",
        data: { 
            rf_class: 'jobs',
            header_filter_type: 'service',
            json_data: json_data,
            searched_val: searched_val
        }
    }).done(function( ret ){    
        jQuery('#service_filter').next('.mini_loader').hide();
        $('#service_filter').append(ret);
    });
            
}

// service pme
function run_ajax_service_filter_pme(){

var json_data = <?php echo $service_filter_json; ?>;
var searched_val = '<?php echo $this->input->get_post('service_filter_pme'); ?>';

jQuery('#service_filter_pme').next('.mini_loader_pme').show();
jQuery.ajax({
    type: "POST",
        url: "/sys/header_filters",
        data: { 
            rf_class: 'jobs',
            header_filter_type: 'service',
            json_data: json_data,
            searched_val: searched_val
        }
    }).done(function( ret ){    
        jQuery('#service_filter_pme').next('.mini_loader_pme').hide();
        $('#service_filter_pme').append(ret);
    });
            
}

// state
function run_ajax_state_filter(){

var json_data = <?php echo $state_filter_json; ?>;
var searched_val = '<?php echo $this->input->get_post('state_filter'); ?>';

jQuery('#state_filter').next('.mini_loader').show();
jQuery.ajax({
    type: "POST",
        url: "/sys/header_filters",
        data: { 
            rf_class: 'jobs',
            header_filter_type: 'state',
            json_data: json_data,
            searched_val: searched_val
        }
    }).done(function( ret ){    
        jQuery('#state_filter').next('.mini_loader').hide();
        $('#state_filter').append(ret);
    });
            
}

// state pme
function run_ajax_state_filter_pme(){

var json_data = <?php echo $state_filter_json; ?>;
var searched_val = '<?php echo $this->input->get_post('state_filter_pme'); ?>';

jQuery('#state_filter_pme').next('.mini_loader_pme').show();
jQuery.ajax({
    type: "POST",
        url: "/sys/header_filters",
        data: { 
            rf_class: 'jobs',
            header_filter_type: 'state',
            json_data: json_data,
            searched_val: searched_val
        }
    }).done(function( ret ){    
        jQuery('#state_filter_pme').next('.mini_loader_pme').hide();
        $('#state_filter_pme').append(ret);
    });
            
}

    $(document).ready(function(){ //doc ready start
        var smsMergeJobCount = parseInt($("#smsMergeJobCount").val());

        var countNotSms = <?php echo isset($countNotSms) ? $countNotSms : 0?>;
        var countNoEmail = <?php echo isset($countNoEmail) ? $countNoEmail : 0?>;
        
        var count_sms_tenant = <?php echo isset($count_sms_tenant) ? $count_sms_tenant : 0?>;
        var count_email_invoice = <?php echo isset($count_email_invoice) ? $count_email_invoice : 0?>;

        var countAllToBeSent = "<?=$email_stats[1]['result']?>";
        var countAllEmailToBeSent = "<?=$email_stats[0]['result']?>";

        //$("#adjustToBeSms").html(countAllToBeSent);

        // get SMS green and grey ticks count
        var sms_tick_green = jQuery(".sms_tick_green").length;
        var sms_tick_grey = jQuery(".sms_tick_grey").length;
        var sms_ready_total = sms_tick_green+sms_tick_grey;
        $("#adjustToBeSms").html(sms_ready_total);

        var sms_sent = parseInt(jQuery("#span_sms_all_certi_invoice").text());
        if( sms_sent == sms_ready_total ){
            jQuery("#iden_btn_sms_tenant").addClass('btn-success');
            jQuery("#iden_btn_sms_tenant").removeClass('btn_sms_tenant');            
        }

        $("#adjustToBeEmail").html(countAllToBeSent);

        //$("#span_sms_all_certi_invoice").html(countNotSms+count_sms_tenant);
        $("#span_email_all_certi_invoice").html(parseInt(countAllEmailToBeSent) + parseInt(countNoEmail));

        var totalSentSms = countNotSms+count_sms_tenant;
        var totalSentEmail = countNoEmail+count_email_invoice;

        if (totalSentSms >= countAllToBeSent) {
            $("#iden_btn_sms_tenant").removeClass("btn_sms_tenant");
            $("#iden_btn_sms_tenant").addClass("breadcrumb-active btn btn-success");
            //$("#span_sms_all_certi_invoice").html(countAllToBeSent);
        }

        if ((parseInt(countAllEmailToBeSent) + parseInt(countNoEmail)) >= countAllToBeSent) {
            $("#iden_btn_email").removeClass("bnt_email_all_certi_invoice");
            $("#iden_btn_email").addClass("breadcrumb-active btn btn-success");
        }
        
        $('a[data-toggle="tab"]').on('click', function(e) {
            window.localStorage.setItem('activeTab', $(this).attr('href'));
        });
        var activeTab = window.localStorage.getItem('activeTab');
        if (activeTab) {
            $('#main-tab a[href="' + activeTab + '"]').tab('show');
            // window.localStorage.removeItem("activeTab");
        }
        var searchParams = new URLSearchParams(window.location.search)
        if (searchParams.has('process')) {
            $('#process_div').toggle('slow');
        }

        jQuery("#chk_cron_merged_sms_toggle").change(function(){
            
            var cron_status  = ( jQuery(this).prop("checked")==true )?1:0;
            var cron_file = 'merged_email_all_cron';
            var db_field = 'cron_merge_sms';

            swal(
                    {
                        title: "",
                        text: "Are You Sure You Want to Continue?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-success",
                        confirmButtonText: "Yes",
                        cancelButtonClass: "btn-danger",
                        cancelButtonText: "No, Cancel!",
                        closeOnConfirm: false,
                        closeOnCancel: true,
                    },
                    function(isConfirm){
                        if(isConfirm){

                            $('#load-screen').show(); //show loader
                            swal.close();

                            jQuery.ajax({
                            type: "POST",
                            url: "<?php echo base_url('/jobs/ajax_toggle_cron_sms_on_off') ?>",
                            dataType: 'json',
                            data: { 
                                cron_status: cron_status,
                                cron_file: cron_file,           
                                db_field: db_field
                            }
                            }).done(function(data){
                                
                                if(data.status){
                                    $('#load-screen').hide(); //hide loader
                                    swal({
                                        title:"Success!",
                                        text: "Auto SMS successfully updated",
                                        type: "success",
                                        showCancelButton: false,
                                        confirmButtonText: "OK",
                                        closeOnConfirm: false,
                                    },function(isConfirm){
                                        if(isConfirm){ 
                                            swal.close();
                                            location.reload();
                                        }
                                    });
                                }else{
                                    swal.close();
                                    location.reload();
                                }

                            });

                        }else{
                            if(jQuery("#chk_cron_merged_sms_toggle").is(":checked")){
                                $('#chk_cron_merged_sms_toggle').prop('checked', false); 
                               
                            }else{
                                $('#chk_cron_merged_sms_toggle').prop('checked', true); 
                            }
                        }
                        
                    }
                );  
            
        });

        // auto email script
        jQuery("#chk_cron_merged_cert_toggle").change(function(){
            
            var cron_status  = ( jQuery(this).prop("checked")==true )?1:0;
            var cron_file = 'merged_email_all_cron';
            var db_field = 'cron_merged_cert';

            swal(
                    {
                        title: "",
                        text: "Are You Sure You Want to Continue?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-success",
                        confirmButtonText: "Yes",
                        cancelButtonClass: "btn-danger",
                        cancelButtonText: "No, Cancel!",
                        closeOnConfirm: false,
                        closeOnCancel: true,
                    },
                    function(isConfirm){
                        if(isConfirm){

                            $('#load-screen').show(); //show loader
                            swal.close();

                            jQuery.ajax({
                            type: "POST",
                            url: "<?php echo base_url('/jobs/ajax_toggle_cron_on_off') ?>",
                            dataType: 'json',
                            data: { 
                                cron_status: cron_status,
                                cron_file: cron_file,           
                                db_field: db_field
                            }
                            }).done(function(data){
                                
                                if(data.status){
                                    $('#load-screen').hide(); //hide loader
                                    swal({
                                        title:"Success!",
                                        text: "Auto Emails successfully updated",
                                        type: "success",
                                        showCancelButton: false,
                                        confirmButtonText: "OK",
                                        closeOnConfirm: false,
                                    },function(isConfirm){
                                        if(isConfirm){ 
                                            swal.close();
                                            location.reload();
                                        }
                                    });
                                }else{
                                    swal.close();
                                    location.reload();
                                }

                            });

                        }else{
                            if(jQuery("#chk_cron_merged_cert_toggle").is(":checked")){
                                $('#chk_cron_merged_cert_toggle').prop('checked', false); 
                               
                            }else{
                                $('#chk_cron_merged_cert_toggle').prop('checked', true); 
                            }
                        }
                        
                    }
                );  
            
        });

        jQuery("#chk_cron_pme_upload_toggle").change(function(){
            
            var cron_status  = ( jQuery(this).prop("checked")==true )?1:0;
            var db_field = 'cron_pme_upload';

            swal(
                    {
                        title: "",
                        text: "Are You Sure You Want to Continue?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-success",
                        confirmButtonText: "Yes",
                        cancelButtonClass: "btn-danger",
                        cancelButtonText: "No, Cancel!",
                        closeOnConfirm: false,
                        closeOnCancel: true,
                    },
                    function(isConfirm){
                        if(isConfirm){

                            $('#load-screen').show(); //show loader
                            swal.close();

                            jQuery.ajax({
                            type: "POST",
                            url: "<?php echo base_url('/property_me/ajax_pme_upload_toggle_cron_on_off') ?>",
                            dataType: 'json',
                            data: { 
                                cron_status: cron_status,       
                                db_field: db_field
                            }
                            }).done(function(data){
                                
                                if(data.status){
                                    $('#load-screen').hide(); //hide loader
                                    swal({
                                        title:"Success!",
                                        text: "Auto Upload successfully updated",
                                        type: "success",
                                        showCancelButton: false,
                                        confirmButtonText: "OK",
                                        closeOnConfirm: false,
                                    },function(isConfirm){
                                        if(isConfirm){ 
                                            swal.close();
                                            location.reload();
                                        }
                                    });
                                }else{
                                    swal.close();
                                    location.reload();
                                }

                            });

                        }else{
                            if(jQuery("#chk_cron_pme_upload_toggle").is(":checked")){
                                $('#chk_cron_pme_upload_toggle').prop('checked', false); 
                               
                            }else{
                                $('#chk_cron_pme_upload_toggle').prop('checked', true); 
                            }
                        }
                        
                    }
                );  
            
        });

        //Email All Certificates/Invoice
        $('.bnt_email_all_certi_invoice').click(function(e){
            e.preventDefault();
            
            swal(
                    {
                        title: "",
                        text: "Are you sure you want to email the invoices / certificates?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-success",
                        confirmButtonText: "Yes",
                        cancelButtonClass: "btn-danger",
                        cancelButtonText: "No, Cancel!",
                        closeOnConfirm: false,
                        closeOnCancel: true,
                    },
                    function(isConfirm){
                        if(isConfirm){

                            $('#load-screen').show(); //show loader
                            swal.close();

                            jQuery.ajax({
                            type: "POST",
                            url: "<?php echo base_url('/jobs/email_all_certificates_and_invoices') ?>",
                            dataType: 'json',
                            data: { 
                                staff_id: <?php echo $this->session->staff_id ?>,
                            }
                            }).done(function(data){
                                
                                if(data.status){
                                    $('#load-screen').hide(); //hide loader
                                    
                                    error_prop_str = '';
                                    if( data.error_prop.length > 0  ){

                                        error_prop_str += "<p>Emails have been processed</p>";
                                        error_prop_str += "<p>However, the following properties are Private and have no landlord email</p>"; 
                                        error_prop_str += "<ul>";
                                        for( var i = 0; i < data.error_prop.length; i++ ){

                                            error_prop_str += "<li>"+data.error_prop+"</li>";

                                        }
                                        error_prop_str += "</ul>";

                                        swal({
                                            html: true,
                                            title:"",
                                            text: error_prop_str,
                                            type: "warning",
                                            showCancelButton: false,
                                            confirmButtonText: "OK",
                                            closeOnConfirm: false,
                                        },function(isConfirm){
                                            if(isConfirm){ 
                                                swal.close();
                                                location.reload();
                                            }
                                        });
                                        
                                    }else{

                                        swal({
                                            title:"Success!",
                                            text: "Emails have been processed",
                                            type: "success",
                                            showCancelButton: false,
                                            showConfirmButton: false,
                                            confirmButtonText: "OK",
                                            closeOnConfirm: false,
                                            closeOnConfirm: false,
                                            allowOutsideClick: false,
                                            timer: 3000
                                        },function(isConfirm){
                                            swal.close();
                                            // location.reload();
                                            window.location.href = window.location.href + "?process=true";
                                        });

                                    }
                                                                        
                                }else{
                                    swal('','All appropriate jobs have already been sent an Email.','info');
                                    $('#load-screen').hide(); //hide loader
                                }

                            });


                        }
                        
                    }
                );  

        })

        $("#btn_process").click(function(e){
            e.preventDefault();
            $('#process_div').toggle('slow');
        })


        // run headler filter ajax
        run_ajax_job_filter();
        run_ajax_service_filter();
        run_ajax_state_filter();
        run_ajax_agency_filter();

        run_ajax_agency_filter_pme();
        run_ajax_job_filter_pme();
        run_ajax_service_filter_pme();
        run_ajax_state_filter_pme();



        $('.bnt_pme_all_certi_invoice').click(function(e){
            e.preventDefault();
            
            swal(
                    {
                        title: "",
                        text: "Are you sure you want to send the invoices / certificates to API?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-success",
                        confirmButtonText: "Yes",
                        cancelButtonClass: "btn-danger",
                        cancelButtonText: "No, Cancel!",
                        closeOnConfirm: false,
                        closeOnCancel: true,
                    },
                    function(isConfirm){
                        if(isConfirm){

                                $('#load-screen').show(); 
                                swal.close();

                                jQuery.ajax({
                                    method: 'post',
                                    processData: false,
                                    contentType: false,
                                    cache: false,
                                    data: { 
                                            staff_id: <?php echo $this->session->staff_id ?>,
                                        },
                                    url: "/jobs/merged_jobs_send_api_invoices",
                                }).done(function( crm_ret ){
                                    var res = JSON.parse(crm_ret);

                                    $('#load-screen').hide(); //hide loader

                                    
                                    if (typeof res.msg !== 'undefined') {
                                       // $('#load-screen').hide(); //hide loader
                                        swal({
                                            title:"Success!",
                                            text: res.msg,
                                            type: "success",
                                            showCancelButton: false,
                                            confirmButtonText: "OK",
                                            closeOnConfirm: false,
                                        },function(isConfirm){
                                            if(isConfirm){ 
                                                swal.close();
                                                location.reload();
                                            }
                                        });
                                    }else {
                                        if (!res.err) {
                                            //$('#load-screen').hide(); //hide loader
                                            swal({
                                                title:"Success!",
                                                text: "Invoices/Bills have been sent to API",
                                                type: "success",
                                                showCancelButton: false,
                                                showConfirmButton: false,
                                                confirmButtonText: "OK",
                                                closeOnConfirm: false,
                                                allowOutsideClick: false,
                                                timer: 3000
                                            },function(isConfirm){
                                                swal.close();
                                                location.reload();
                                            });
                                        }else {
                                           //$('#load-screen').hide(); //hide loader
                                            swal({
                                                title:"Warning!",
                                                text: "Some of the invoices has not been sent to API.",
                                                type: "warning",
                                                showCancelButton: false,
                                                confirmButtonText: "OK",
                                                closeOnConfirm: false,
                                            },function(isConfirm){
                                                if(isConfirm){ 
                                                    swal.close();
                                                    location.reload();
                                                }
                                            });
                                        }
                                    }
                                    

                                }); 

                         


                        }
                        
                    }
                );  
        })

        $('.btn_sms_tenant').click(function(e){
            e.preventDefault();
            
            swal(
                    {
                        title: "",
                        text: "Are you sure you want to continue?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-success",
                        confirmButtonText: "Yes",
                        cancelButtonClass: "btn-danger",
                        cancelButtonText: "No, Cancel!",
                        closeOnConfirm: false,
                        closeOnCancel: true,
                    },
                    function(isConfirm){
                        if(isConfirm){

                                $('#load-screen').show(); 
                                swal.close();

                                jQuery.ajax({
                                    method: 'post',
                                    processData: false,
                                    contentType: false,
                                    cache: false,
                                    data: { 
                                            staff_id: <?php echo $this->session->staff_id ?>,
                                        },
                                    url: "/jobs/merged_jobs_sms_send",
                                }).done(function( crm_ret ){
                                    var res = JSON.parse(crm_ret);
                                    
                                    if(res.status){
                                        $('#load-screen').hide(); //hide loader
                                        swal({
                                            title:"Success!",
                                            text: "SMS sent",
                                            type: "success",
                                            showCancelButton: false,
                                            confirmButtonText: "OK",
                                            closeOnConfirm: false,
                                            showConfirmButton: false,
                                            allowOutsideClick: false,
                                            timer: 3000
                                        },function(isConfirm){
                                            swal.close();
                                            // location.reload();
                                            window.location.href = window.location.href + "?process=true";
                                        });
                                    }else{
                                        swal('','All appropriate jobs have already been sent an SMS.','info');
                                        $('#load-screen').hide(); //hide loader
                                    }

                                }); 

                        }
                        
                    }
                );  
        })

        $("input:file").change(function (e){
            var fileId = $(this).attr('id');
            fileId = fileId.replace('filePDF','');
            var fileName = e.target.files[0].name;
            var agencyId = $(this).attr('agencyId');
            var propId = $(this).attr('propId');
            var cntRow = $(this).attr('cntRow');
            var jId = $(this).attr('jId');

            swal(
                    {
                        title: "",
                        text: "Are You Sure You Want to upload "+fileName+"?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-success",
                        confirmButtonText: "Yes",
                        cancelButtonClass: "btn-danger",
                        cancelButtonText: "No, Cancel!",
                        closeOnConfirm: false,
                        closeOnCancel: true,
                    },
                    function(isConfirm){
                        if(isConfirm){
                            swal.close();

                            var file_data = $('#filePDF'+cntRow).prop('files')[0];   
                            var form_data = new FormData();                  
                            form_data.append('file', file_data);
                            form_data.append('agencyId', agencyId);
                            form_data.append('selected', propId);
                            form_data.append('jId', jId);

                            var proceed = true;

                            if ($('#filePDF'+cntRow).get(0).files.length === 0) {
                                swal({
                                    title: "Upload Failed!",
                                    text: "No PDF file selected",
                                    type: "warning",
                                    confirmButtonClass: "btn-danger"
                                });
                                proceed = false;
                            }


                            if (proceed) {
                                jQuery(".loading-bar-div").show();
                                jQuery.ajax({
                                    method: 'post',
                                    processData: false,
                                    contentType: false,
                                    cache: false,
                                    data: form_data,
                                    enctype: 'multipart/form-data',
                                    url: "/property_me/ajax_upload_file",
                                }).done(function( crm_ret ){
                                    jQuery(".loading-bar-div").hide();
                                    var res = JSON.parse(crm_ret);
                                    if (!res.err) {
                                        var string = "";
                                        string += "\nagency: " + res.agencyId
                                        string += "\nproperties: " + res.selected
                                        string += "\nfile: " + res.file

                                        swal({
                                            title: "Upload Sucess!",
                                            text: "Invoice sucessfully uploaded.",
                                            type: "success",
                                            confirmButtonClass: "btn-success"
                                        });

                                        $("#labelPdf"+fileId).hide();
                                        $("#checkPdf"+fileId).show();
                                    }else {
                                        swal({
                                            title: "Upload Failed!",
                                            text: "something wen't wrong on the uploading function...",
                                            type: "warning",
                                            confirmButtonClass: "btn-success"
                                        });
                                    }

                                }); 
                            }
                        }else{

                        }
                        
                    }
                )

        });

        $('#btnMarkCompleted').click(function(e){
           
            var email_invoice_btn = jQuery(".email_invoice_btn");
            var post_api_invoice_btn = jQuery(".post_api_invoice_btn");
            var append_txt = '';

            if( !( email_invoice_btn.hasClass("btn-success") == true && post_api_invoice_btn.hasClass("btn-success") == true ) ){
                var append_txt = "Not ALL invoices have been sent. ";
            }


            swal({
                title: "Warning!",
                text: append_txt+"Are you sure you want to mark all jobs as completed?",
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
                        url: "/jobs/mark_completed"
                    }).done(function( ret ){
                            
                        $('#load-screen').hide();
                        location.reload();                       	

                    });
                    
                                            

                }

            });	

        });



    }) //doc ready end

</script>