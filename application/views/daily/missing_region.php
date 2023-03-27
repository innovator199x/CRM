
<div class="box-typical box-typical-padding">

<?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => "/daily/missing_region"
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);
?>


<section>
    <div class="body-typical-body">
        <div class="table-responsive">
            <table class="table table-hover main-table">
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>Agency</th>
                        <th>Postcode</th>
                    </tr>
                </thead>

                <tbody>

                <?php 
                $count = 0;
                if( $lists->num_rows() > 0 ){
                    foreach($lists->result_array() as $row){

                        // check if postcode exist on region postcode list
                        if( $row['p_postcode'] != '' ){

                            $check_postcode = $this->daily_model->check_postcode_exist_on_list($row['p_postcode']);

                            // it didnt find postcode on region postcode list
                            if( $check_postcode == false ){ 

                    ?>
                            <tr class="body_tr jalign_left">
                                <td>
                                    <?php echo $this->gherxlib->crmLink('vpd', $row['property_id'], "{$row['p_address_1']} {$row['p_address_2']} {$row['p_address_3']}, {$row['p_state']} {$row['postcode']}") ?>
                                </td>
                                <td>
                                    <?php echo $this->gherxlib->crmLink('vad', $row['agency_id'], $row['agency_name'],'',$row['priority']); ?>
                                </td>
                                <td>
                                    <?php echo $row['p_postcode']; ?>
                                </td>
                            </tr>
                    <?php
                            $count++;
                            }
                        }
                    }
                }else{ ?>
                    <tr><td colspan="3">There are no missing regions</td></tr>
                <?php    
                }


                $total_rows = $count;

                // update page total
                $page_tot_params = array(
                    'page' => $page_url,
                    'total' => $total_rows
                );
                $this->system_model->update_page_total($page_tot_params);
                ?>
              
                </tbody>

            </table>
        </div>

        <div>Total: <?php echo $count; ?></div>

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
<p>This page shows all properties located in a region we do not service.</p>
<pre>
<code>SELECT `p`.`property_id`, `p`.`address_1` AS `p_address_1`, `p`.`address_2` AS `p_address_2`, `p`.`address_3` AS `p_address_3`, `p`.`state` AS `p_state`, `p`.`postcode` AS `p_postcode`, `a`.`agency_id`, `a`.`agency_name`
FROM `property` AS `p`
LEFT JOIN `agency` AS `a` ON p.`agency_id` = a.`agency_id`
LEFT JOIN `agency_user_accounts` AS `aua` ON p.`pm_id_new` = aua.`agency_user_account_id`
WHERE `p`.`deleted` = 0
AND `a`.`status` = 'active'
ORDER BY `p`.`address_2` ASC, `p`.`address_1` ASC</code>
</pre>
</div>
<!-- Fancybox END -->

<script type="text/javascript">

jQuery(document).ready(function(){



})

</script>