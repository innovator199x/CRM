<style>
    .flatpickr{
        width:100%;
    }
</style>
<div class="box-typical box-typical-padding">

<?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => "/daily/action_required_jobs"
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);
?>

<header class="box-typical-header">

    <div class="box-typical box-typical-padding">

        <div class="for-groupss row">
            <div class="col-md-10 columns">
                <?php
                $form_attr = array(
                    'id' => 'jform'
                );
                echo form_open('/daily/action_required_jobs',$form_attr);
                ?>
                    <div class="row">
                        
                        <div class="col-mdd-3">
                                    <label for="vehicle_select">Job Type</label>
                                    <select id="job_type_filter" name="job_type_filter" class="form-control field_g2 select2-photo">
                                        <option value="">All</option>
                                    </select>
                        </div>

                        <div class="col-mdd-3">
                            <label for="vehicle_select">Agency</label>
                            <select id="agency_filter" name="agency_filter" class="form-control field_g2">
							    <option value="">ALL</option>
							</select>
							<div class="mini_loader"></div>
                        </div>

                             <div class="col-mdd-3">
                                    <label for="vehicle_select">Service</label>
                                    <select id="service_filter" name="service_filter" class="form-control field_g2 select2-photo">
                                        <option value="">All</option>
                                    </select>
                        </div>

                        <div class="col-mdd-3">
                                    <label for="vehicle_select"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></label>
                                    <select id="state_filter" name="state_filter" class="form-control field_g2 select2-photo">
                                        <option value="">All</option>
                                    </select>
                        </div>

                        <div class="col-mdd-3">
							<label for="date_select">Date</label>
							<input placeholder="ALL" name="date_filter" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo $this->input->get_post('date_filter'); ?>">
						</div>
                        
                        <div class="col-mdd-3">
							<label for="phrase_select">Phrase</label>
							<input placeholder="ALL" type="text" name="search_filter" class="form-control" value="<?php echo $this->input->get_post('search_filter'); ?>" />
						</div>
                        

                        <div class="col-md-1 columns">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <button type="submit" class="btn btn-inline">Search</button>
                        </div>

                    </div>
                </form>
            </div>
        </div>

    </div>

</header>

<section>
    <div class="body-typical-body">
        <div class="table-responsive">
            <table class="table table-hover main-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Job Type</th>
                        <th>Age</th>
                        <th>Service</th>
                        <th>Price</th>
                        <th>Address</th>
                        <th><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></th>
                        <th>Agency</th>
                        <th>Comments</th>
                        <th>Job #</th>
                        <th>Last Contact</th>
                    </tr>
                </thead>

                <tbody>

                    <?php 
                        foreach($lists->result_array() as $row){
                    ?>
                            <tr>
							
                                    <td>
                                        <input type="hidden" name="job_id" class="job_id" value="<?php echo $row['jid']; ?>">
                                        <?php echo ($row['jdate']!="" && $row['jdate']!="0000-00-00")?date("d/m/Y",strtotime($row['jdate'])):''; ?>
                                    </td>
                                    
                                    <td><?php echo $this->gherxlib->getJobTypeAbbrv($row['job_type']); ?></td>
                                    
                                    <td>
                                        <?php
                                        // Age
                                        $jcreated=date_create($row['jcreated']);
                                        $todays_date=date_create(date('Y-m-d'));
                                        $diff=date_diff($jcreated,$todays_date);
                                        $age = $diff->format("%r%a");
                                        echo (((int)$age)!=0)?$age:0;
                                        ?>
                                        </td>
                                        
                                        <td><img src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($row['jservice']); ?>" /></td>
                                        <td><?php echo $row['job_price']; ?></td>
                                        
                                        <td>
                                            <?php
                                                $prop_address = "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}";
                                                echo $this->gherxlib->crmLink('vpd',$row['property_id'],$prop_address);
                                             ?>
                                        </td>
                                        
                                        <td><?php echo $row['p_state']; ?></td>
                                        <td><?php echo $row['agency_name']; ?></td>
                                        <td>
                                            <div class="pos-rel">
                                                <input type="text" class="form-control comments" name="comments" value="<?php echo $row['j_comments']; ?>">
                                                <i class="fa fa-check-circle text-green green_check check_ok_ajax"></i>
                                            </div>
                                        </td>
                                        <td>
                                            <?php echo $this->gherxlib->crmLink('vjd', $row['jid'], $row['jid']); ?>
                                        </td>							
                                        <td>
                                        <?php
                                        $lc_sql = $this->gherxlib->getLastContact($row['jid']);	
                                        $lc = $lc_sql->row_array();
                                        
                                        echo ( $lc['eventdate']!="" && $lc['eventdate']!="0000-00-00 00:00:00" )?date("d/m/Y",strtotime($lc['eventdate'])):'';
                                        ?>
                                    </td>
                                
                            </tr>
                    <?php
                        }
                    ?>

                </tbody>

            </table>
        </div>

        <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
        <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>

    </div>
</section>

</div>


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
</style>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

<h4><?php echo $title; ?></h4>
<p>This page shows all jobs with the status, “Action required.</p>
<pre>
<code>SELECT *, `j`.`id` AS `jid`, `j`.`status` AS `jstatus`, `j`.`service` AS `jservice`, `j`.`created` AS `jcreated`, `j`.`date` AS `jdate`, `j`.`comments` AS `j_comments`, `p`.`address_1` AS `p_address_1`, `p`.`address_2` AS `p_address_2`, `p`.`address_3` AS `p_address_3`, `p`.`state` AS `p_state`, `p`.`postcode` AS `p_postcode`, `p`.`comments` AS `p_comments`, `a`.`agency_id` AS `a_id`, `a`.`phone` AS `a_phone`, `a`.`address_1` AS `a_address_1`, `a`.`address_2` AS `a_address_2`, `a`.`address_3` AS `a_address_3`, `a`.`state` AS `a_state`, `a`.`postcode` AS `a_postcode`, `a`.`trust_account_software`, `a`.`tas_connected`, `jr`.`name` AS `jr_name`, `sa`.`FirstName`, `sa`.`LastName`, `aua`.`agency_user_account_id`, `aua`.`fname` AS `pm_fname`, `aua`.`lname` AS `pm_lname`, `aua`.`email` AS `pm_email`, `ajt`.`id` AS `ajt_id`, `ajt`.`type` AS `ajt_type`
FROM `jobs` AS `j`
LEFT JOIN `property` AS `p` ON j.`property_id` = p.`property_id`
LEFT JOIN `agency` AS `a` ON  p.`agency_id` = a.`agency_id`
LEFT JOIN `job_reason` AS `jr` ON j.`job_reason_id` = jr.`job_reason_id`
LEFT JOIN `staff_accounts` AS `sa` ON j.`assigned_tech` = sa.`StaffID`
LEFT JOIN `alarm_job_type` AS `ajt` ON j.`service` = ajt.`id`
LEFT JOIN `agency_user_accounts` AS `aua` ON p.`pm_id_new` = aua.`agency_user_account_id`
WHERE `j`.`del_job` = 0
AND `p`.`deleted` = 0
AND `a`.`status` = 'active'
AND `a`.`country_id` = <?php echo COUNTRY; ?> 
AND `j`.`status` = 'Action Required'
LIMIT 50</code>
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

    //service filter
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

    // state filter
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

jQuery(document).ready(function(){

    run_ajax_agency_filter();
    run_ajax_job_filter();
    run_ajax_service_filter();
    run_ajax_state_filter();

    //comments field edit > ajax
    jQuery('.comments').change(function(e){
        var obj = jQuery(this);
        var job_id = obj.parents("tr:first").find(".job_id").val();
        var comments = obj.parents("tr:first").find(".comments").val();
        
        $('#load-screen').show(); //show loader
		jQuery.ajax({
			type: "POST",
			url: "/daily/ajax_edit_action_required_jobs_comments",
            dataType: 'json',
			data: { 
				job_id: job_id,
				comments: comments
			}
		}).done(function( ret ){	
			if(ret.status){
				$('#load-screen').hide(); //hide loader
				obj.parents("td:first").find(".green_check").show();
				//fadeout timer
				setTimeout(function(){ 
					obj.parents("td:first").find(".green_check").fadeOut();
				}, 3000);
			}
		});
    })
        

})

</script>