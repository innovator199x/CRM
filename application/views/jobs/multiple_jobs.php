
<div class="box-typical box-typical-padding">

<?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => "/daily/multiple_jobs"
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
		echo form_open($uri,$form_attr);
		?>
			<div class="for-groupss row">

				<div class="col-md-10 columns">
					<div class="row">	

                        <div class="col-md-4">
							<label for="agency_select">State</label>
							<select id="state_filter" name="state_filter"  class="form-control field_g2">
                                <option value="">---</option>
                                <?php                                
                                foreach( $state_sql->result() as $state_row ){ ?>
                                    <option value="<?php echo $state_row->state; ?>" <?php echo ( $state_row->state == $this->input->get_post('state_filter') )?'selected="selected"':null; ?>>
                                        <?php echo $state_row->state; ?>
                                    </option>
                                <?php
                                }                                
                                ?>
							</select>							
						</div>

						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<input type="submit" name="search_submit" value="Search" class="btn">
						</div>
						
					</div>

				</div>

                <div class="col-md-2 columns">                    
                    <div class="checkbox-toggle float-right">                
                        <input type="checkbox" name="upfront_bill" id="upfront_bill" <?php echo ( $this->input->get_post('upfront_bill') == 1 )?'checked':null; ?> value="1" />
                        <label for="upfront_bill">Upfront Bill</label>                    
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
                    </tr>
                    <?php 
                        $dup_sql2 = $this->daily_model->getOtherMultipleJobs($d['property_id'],$d['id']);
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
                                <?php echo $this->gherxlib->crmLink('vad',$d2['agency_id'],$d2['agency_name'],'',$d2['priority']); ?>                               
                            </td>
                        </tr>

                <?php
                    }
                    $i++;

                

                     }
                }else{
                    echo "<tr><td colspan='8'>No Data</td></tr>";
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
.upfront_bill_icon{
    margin-left: 5px;
}
</style>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

<h4><?php echo $title; ?></h4>
<p>This page shows all properties with more than one active job.</p>
<pre>
<code>SELECT COUNT( j.`id` ) AS jcount, `j`.`id`, `j`.`job_type`, `j`.`status`, `j`.`property_id`, `j`.`service` AS `jservice`, `p`.`address_1`, `p`.`address_2`, `p`.`address_3`, `p`.`state`, `p`.`deleted`, `a`.`agency_name`, `a`.`agency_id`, `a`.`allow_upfront_billing`, `ajt`.`id` AS `ajt_id`, `ajt`.`type` AS `ajt_type`
FROM `jobs` as `j`
LEFT JOIN `alarm_job_type` AS `ajt` ON j.`service` = ajt.`id`
LEFT JOIN `property` as `p` ON `p`.`property_id` = `j`.`property_id`
LEFT JOIN `agency` as `a` ON `a`.`agency_id` = `p`.`agency_id`
WHERE `j`.`status` != 'Completed'
AND `j`.`status` != 'Cancelled'
AND `p`.`deleted` = 0
AND `a`.`status` = 'active'
AND `j`.`del_job` = 0
AND `a`.`country_id` = <?php echo COUNTRY ?> 
AND `a`.`allow_upfront_billing` =0
GROUP BY `j`.`property_id`
HAVING `jcount` >= 2
LIMIT 50</code>
</pre>
</div>
<!-- Fancybox END -->

<script type="text/javascript">

jQuery(document).ready(function(){

    // remember tab
    jQuery("#j_remember_tab .nav-link").click(function(){

        var node = jQuery(this);
        var nav_href = node.attr("href");	

        Cookies.set('multiple_jobs_remember_tab', nav_href);

    });

    // select remembered tab		
	if( Cookies.get('multiple_jobs_remember_tab') != undefined ){				

        jQuery('#j_remember_tab a[href="'+Cookies.get('multiple_jobs_remember_tab')+'"]').tab('show');

    }else{ // default	
                        
        jQuery('#j_remember_tab a[href="#regular_clients_tab"]').tab('show');

    }

    jQuery('#upfront_bill').on('change',function(){

        var checked = $(this).is(':checked');
        var mode = jQuery("#mode").val();

        if(checked){
            window.location = '/daily/multiple_jobs/?upfront_bill=1&state=<?php echo $this->input->get_post('state_filter') ?>';        
        }else{
            window.location = '/daily/multiple_jobs/?upfront_bill=0&state=<?php echo $this->input->get_post('state_filter') ?>';
        }

    });

})

</script>