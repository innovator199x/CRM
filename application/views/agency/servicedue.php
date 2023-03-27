
<style>
    .col-mdd-3{
        max-width:15.5%;
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
        'link' => "/agency/servicedue"
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);


$export_links_params_arr = array(
    'state' => $this->input->get_post('state'),
    'sales_rep' => $this->input->get_post('sales_rep'),
    'region' => $this->input->get_post('region'),
    'search' => $this->input->get_post('search'),
);
$export_link_params = '/agency/servicedue/?export=1&'.http_build_query($export_links_params_arr);

?>

	<header class="box-typical-header">

        <div class="box-typical box-typical-padding">
            <?php
        $form_attr = array(
            'id' => 'jform'
        );
        echo form_open('/agency/servicedue',$form_attr);
        ?>
            <div class="for-groupss row">
                <div class="col-lg-10 col-md-12 columns">
                    <div class="row">

                        <div class="col-mdd-3">
                            <label for="agency_select">State</label>
                            <select id="state" name="state" class="form-control field_g2">
                                <option value="">ALL</option>
                                <?php 
                                    foreach($state_filter->result_array() as $row){
                                        $sel = ($row['state']==$this->input->get_post('state')) ? 'selected="selected"' : NULL;
                                ?>
                                        <option <?php echo $sel; ?> value="<?php echo $row['state'] ?>"><?php echo $row['state'] ?></option>
                                <?php
                                    }
                                ?>
                            </select>
                        </div>

                        <div class="col-mdd-3">
                            <label for="agency_select">Sales Rep</label>
                            <select id="sales_rep" name="sales_rep" class="form-control field_g2">
                                <option value="">ALL</option>
                                <?php 
                                    foreach($salesrep_filter->result_array() as $row){
                                        $sel = ($row['StaffID']==$this->input->get_post('sales_rep')) ? 'selected="selected"' : NULL;
                                ?>
                                        <option <?php echo $sel; ?> value="<?php echo $row['StaffID'] ?>"><?php echo $row['FirstName'] ." ".$row['LastName'] ?></option>
                                <?php
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="col-mdd-3">
                            <label for="agency_select">Region</label>
                            <select id="region" name="region" class="form-control field_g2">
                                <option value="">ALL</option>
                                <?php 
                                    foreach($region_filter->result_array() as $row){
                                    if($row['agency_region_id'] != NULL){
                                    $sel = ($row['agency_region_id']==$this->input->get_post('region')) ? 'selected="selected"' : NULL;
                                ?>
                                        <option <?php echo $sel; ?>  value="<?php echo $row['agency_region_id'] ?>"><?php echo $row['agency_region_name'] ?></option>
                                <?php
                                    }
                                    }
                                ?>
                            </select>
                        </div>

                         <div class="col-mdd-3">
                            <label for="search">Phrase</label>
                            <input type="text" placeholder="ALL" name="search" class="form-control" value="<?php echo $this->input->get_post('search'); ?>" />
                        </div>

                       
                        <div class="col-md-1 columns">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <input class="btn" type="submit" name="btn_search" value="Search">
                        </div>
                        
                    </div>

                </div>        

                <div class="col-lg-2 col-md-12 columns">
                    <section class="proj-page-section float-right">
                        <div class="proj-page-attach">
                            <i class="fa fa-file-excel-o"></i>
                            <p class="name"><?php echo $title; ?></p>
                            <p>
								<a href="<?php echo $export_link_params ?>" target="blank">
									Export
								</a>
                            </p>
                        </div>
                    </section>
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
							<th>Agency Name</th>
							<th>Sales Rep</th>
							<th>Service Due</th>
						</tr>
					</thead>

					<tbody>
                        <?php 
                            foreach($lists->result_array() as $row){
                        ?>
                                 <tr>
                                   <td>
                                       <?php echo $this->gherxlib->crmlink('vad', $row['agency_id'], $row['agency_name'],'',$row['priority']) ?>
                                   </td>
                                   <td>
									<?php echo "{$row['FirstName']} {$row['LastName']}" ?>
								    </td>
                                    <td style="border-right: 1px solid #ccc;">
									<?php echo $row['jcount']; ?>
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

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
    This page shows all agencies that have jobs in service due waiting to be processed.
	</p>
    <pre>
<code>SELECT count(j.`id`) AS jcount, `a`.`agency_id`, `a`.`agency_name`, `sa`.`FirstName`, `sa`.`LastName`
FROM `jobs` AS `j`
LEFT JOIN `property` as `p` ON `p`.`property_id` = `j`.`property_id`
LEFT JOIN `agency` as `a` ON `a`.`agency_id` = `p`.`agency_id`
LEFT JOIN `staff_accounts` as `sa` ON `sa`.`StaffID` = `a`.`salesrep`
LEFT JOIN `agency_regions` as `ar` ON `ar`.`agency_region_id` = `a`.`agency_region_id`
WHERE `j`.`status` = 'Pending'
AND `a`.`status` = 'active'
AND `p`.`deleted` = 0
AND `j`.`del_job` = 0
AND `a`.`country_id` = 1
GROUP BY `a`.`agency_id`
ORDER BY `jcount` DESC
LIMIT 50</code>
    </pre>

</div>
<!-- Fancybox END -->


<script type="text/javascript">

  

</script>