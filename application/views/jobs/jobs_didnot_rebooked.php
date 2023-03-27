<style>
.main-table {
    border-left: 1px solid #dee2e6;
    border-right: 1px solid #dee2e6;
    border-bottom: 1px solid #dee2e6;
    margin-bottom: 20px;
}

.col-mdd-3 {
    -webkit-box-flex: 0;
    -ms-flex: 0 0 15.2%;
    flex: 0 0 15.2%;
    max-width: 15.2%;
    position: relative;
    width: 100%;
    min-height: 1px;
    padding-right: 15px;
    padding-left: 15px;
}
.upfront_bill_icon{margin-left: 5px;}
</style>

<div class="box-typical box-typical-padding">

<?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => "/daily/missed_jobs"
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);
?>

<header class="box-typical-header">
	<div class="box-typical box-typical-padding">
        <form action="./missed_jobs" method="post">
			<div class="for-groupss row">
				<div class="col-md-10 columns">
					<div class="row">	
                        <div class="col-md-3">
                            <label for="service_select"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></label>
							<select id="state_filter" name="state_filter" class="form-control">
								<option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>						
						</div>

                        <div class="col-md-3">
                            <label for="jobtype_select">Job Type</label>
                            <select id="job_type_filter" name="job_type_filter" class="form-control ">
                                <option value="">ALL</option>
                            </select>
                            <div class="mini_loader"></div>						
						</div>
                        <div class="col-md-3">
                            <label>Agency</label>
                            <select id="agency_filter" name="agency_filter" class="form-control">
                                <option value="">ALL</option>
                            </select>
                            <div class="mini_loader"></div>						
						</div>
						<div class="col-md-3 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<input type="submit" name="search_submit" value="Search" class="btn">
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
                        <th>Job #</th>
                        <th>Job Type</th>
                        <th>Status</th>
                        <th>Service</th>							
                        <th>Address</th>
                        <th><?php echo $this->gherxlib->getDynamicState($this->config->item('country')) ?></th>
                        <th>Agency Name</th>
                        <th>Date</th>
                    </tr>
                </thead>

                <tbody>
                    <?php 
                        if($lists->num_rows()>0){
                            $i = 0;
                            foreach($lists->result_array() as $d){
                                $paddress = "{$d['address_1']} {$d['address_2']}, {$d['address_3']}";
                    ?>
                    <tr <?php echo ($i%2==0)?'style="border-right: 1px solid #cccccc; background-color: #efefef;"':''; ?>>                        
                        <td><?php echo $this->gherxlib->crmLink('vjd',$d['id'],$d['id']); ?></td>
                        <td><?php echo $d['job_type']; ?></td>
                        <td><?php echo $d['status']; ?></td>
                        <td>
                            <?php
                            // display icons
                            $job_icons_params = array(
                                'service_type' => $d['jservice'],
                                'job_type' => $d['job_type'],
                                'sevice_type_name' => $d['ajt_type']
                            );
                            echo $this->system_model->display_job_icons($job_icons_params);
                            ?>                                                        
                        </td>
                        <td><?php echo $this->gherxlib->crmLink('vpd',$d['property_id'],$paddress); ?></td>
                        <td><?php echo $d['state']; ?></td>
                        <td>
                            <?php echo $this->gherxlib->crmLink('vad',$d['agency_id'],$d['agency_name'],'',$d['priority']); ?>                         
                        </td>
                        <td><?php echo $d['date']; ?></td>
                    </tr>
                        <?php 
                            $dup_sql2 = $this->daily_model->getMissedJobs($d['property_id'],$d['id']);
                            foreach($dup_sql2->result_array() as $d2){
                                $paddress = "{$d2['address_1']} {$d2['address_2']}, {$d2['address_3']}";
                        ?>
                        <tr <?php echo ($i%2==0)?'style="border-right: 1px solid #cccccc; background-color: #efefef;"':''; ?>>                            
                            <td><?php echo $this->gherxlib->crmLink('vjd',$d2['id'],$d2['id']); ?></td>
                            <td><?php echo $d2['job_type']; ?></td>
                            <td><?php echo $d2['status']; ?></td>
                            <td>	        
                                <?php
                                // display icons
                                $job_icons_params = array(
                                    'service_type' => $d2['jservice'],
                                    'job_type' => $d2['job_type'],
                                    'sevice_type_name' => $d2['ajt_type']
                                );
                                echo $this->system_model->display_job_icons($job_icons_params);
                                ?>                                                            
                            </td>
                            <td><?php echo $this->gherxlib->crmLink('vpd',$d2['property_id'],$paddress); ?></td>
                            <td><?php echo $d2['state']; ?></td>
                            <td>
                                <?php echo $this->gherxlib->crmLink('vad',$d2['agency_id'],$d2['agency_name']); ?>                               
                            </td>
                            <td><?php echo $d2['date']; ?></td>
                        </tr>

                <?php
                    } $i++; }
                } else { echo "<tr><td colspan='8'>No Data</td></tr>"; }
                ?>
                </tbody>
            </table>
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
<pre>
    <code><?php echo $last_query; ?></code>
</pre>
</div>
<!-- Fancybox END -->
<script>

jQuery(document).ready(function(){
    run_ajax_state_filter();
    run_ajax_job_filter();
    run_ajax_agency_filter();
})

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
</script>