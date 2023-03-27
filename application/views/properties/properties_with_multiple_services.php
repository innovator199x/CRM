<style>
.col-mdd-3{
    max-width:16%;
}

.large-line{
    font-size: 25px;
}

</style>

<div class="box-typical box-typical-padding">

<?php
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/properties/properties_with_multiple_services"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);

?>

<header class="box-typical-header">
    <div class="box-typical box-typical-padding">

        <?php
            echo form_open('/properties/properties_with_multiple_services');
        ?>

        <div class="for-groups row">
            <div class="col-md-10 columns">
                <div class="row">

                    <div class="col-mdd-3">
                        <label for="state_filter"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></label>
                        <select id="state_filter" name="state_filter" class="form-control">
                            <option value="">ALL</option>
                            <?php 
                                // remove duplicate states
                                $temp = array_unique(array_column($states, 'state'));
                                $states = array_intersect_key($states, $temp);

                                foreach($states as $state):
                                    if ($state):
                                ?>
                                <option value="<?php echo $state->state; ?>"
                                <?php echo ($this->input->get_post('state_filter') == $state->state && $state->state) ?
                                        'selected="selected"':''; ?>>

                                <?php echo $state->state; ?>
                                </option>
                            <?php 
                                    endif;
                                endforeach; 
                            ?>
                        </select>

                    </div>

                    <div class="col-mdd-3">
                        <label>Agency</label>
                        <select id="agency_filter" name="agency_filter" class="form-control">
                            <option value="">ALL</option>
                            <?php 
                                  // remove duplicate states
                                  $temp = array_unique(array_column($agencies, 'agency'));
                                  $agencies = array_intersect_key($agencies, $temp);
                                
                                  foreach($agencies as $agency): 
                                      if($agency): 
                            ?>
                                <option value="<?php echo $agency->agency; ?>"
                                <?php echo ($this->input->get_post('agency_filter') ==  $agency->agency) ?
                                        'selected="selected"':''; ?>>

                                <?php echo $agency->agency; ?>
                                </option>
                            <?php     endif;
                                   endforeach; 
                            ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="phrase_select">Phrase</label>
                        <input type="text" name="search_filter" class="form-control" placeholder="ALL" value="<?php echo $this->input->get_post('search_filter'); ?>" />
                    </div>

                    <div class="col-md-1 columns">
                        <label class="col-sm-12 form-control-label">&nbsp;</label>
                        <button type="submit" class="btn">Search</button>
                    </div>

                </div>
            </div>
        </div>

        <?php echo form_close(); ?>

    </div>
</header>

    <section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<table class="table table-hover main-table">
					<thead>
						<tr>
							<th width="20%">Property Address</th>
                            <th width="25%">Active Property Services</th>
							<th width="20%">Agency</th>
                            <th width="35%">Active Agency Services</th>
					</thead>
					<tbody>

                           <?php foreach($properties_with_multiple_services as $row): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo $this->config->item("crm_link") ?>/view_property_details.php?id=<?php echo $row->property_id; ?>" target="_blank">
                                            <?php echo $row->address; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($row->property_services):
                                            
                                            $services = array_unique(explode(",", $row->property_services));
                                                foreach($services as $service):   
                                                    if ($service):
                                        ?>
                                            <img data-toggle="tooltip" title="<?php echo get_alarm_job_type_name($service); ?>" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($service); ?>" /> &nbsp;<span class="large-line">|</span>&nbsp;
                                        <?php       endif;
                                                endforeach;
                                              endif; 
                                        ?>
                                    </td>
                                    <td class="<?php echo ( $row->priority > 0 )?'j_bold':null; ?>">
                                        <a href="/agency/view_agency_details/<?php echo $row->agency_id; ?>" target="_blank">
                                            <?php echo $row->agency_name." ".( ( $row->priority > 0 )?' ('.$row->abbreviation.')':null ); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($row->agency_services):
                                            $services = array_unique(explode(",", $row->agency_services));
                                                foreach($services as $service):   
                                                    if ($service):
                                        ?>
                                            <img data-toggle="tooltip" title="<?php echo get_alarm_job_type_name($service); ?>" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($service); ?>" /> &nbsp;<span class="large-line">|</span>&nbsp;
                                        <?php       endif;
                                                endforeach;
                                              endif; 
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                    </tbody>
                    <tfoot>
                       
                    </tfoot>
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

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;">

    <h4><?php echo $title; ?></h4>
    <p>
        This page shows all properties where there is more than one service on SATS.
    </p>
    <pre>
<code>
SELECT CONCAT(p.address_1, ' ', `p`.`address_2`, ', ', `p`.`address_3`, ', ', p.state) address,
    `ps`.`property_id`,
    `pt`.`services` AS `property_services`,
    `a`.`agency_id`,
    `a`.`agency_name`,
    `asv`.`services` AS `agency_services`
FROM
    ((
    SELECT
        ps.property_id,
        COUNT(service) AS services
    FROM property_services ps
    WHERE ps.service = 1
    GROUP BY ps.property_id
    HAVING services > 1) AS ps)
JOIN `property` AS `p` ON `p`.`property_id` = `ps`.`property_id`
JOIN (
    SELECT
        ps.property_id ,
        GROUP_CONCAT(ps.alarm_job_type_id) AS services
    FROM property_services ps
    JOIN alarm_job_type AS aj ON aj.id = ps.alarm_job_type_id
    WHERE ps.service = 1
    GROUP BY
        ps.property_id
    HAVING (2 NOT IN (services) AND 6 NOT IN (services))OR 
        (2 NOT IN (services) AND 15 NOT IN (services)) ) AS pt ON
    `pt`.`property_id` = `p`.`property_id`
JOIN `agency` AS `a` ON `a`.`agency_id` = `p`.`agency_id`
JOIN (
    SELECT
        a.agency_id,
        GROUP_CONCAT(a.service_id) AS services
    FROM agency_services a
    GROUP BY agency_id) AS asv ON
    `a`.`agency_id` = `asv`.`agency_id`
WHERE
    (`p`.`is_nlm` IS NULL OR `p`.`is_nlm` = 0)
    AND `p`.`deleted` = 0
    AND `a`.`status` = 'active'
</code>
</pre>

</div>
<!-- Fancybox END -->

<script type="text/javascript">


$(document).ready(function() {

});

</script>
