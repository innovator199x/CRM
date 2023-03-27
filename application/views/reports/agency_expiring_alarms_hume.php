<style>
.first_col{
    width: 50%;
}
.second_col{
    border-left: 1px solid #dee2e6;
}
</style>

<div class="box-typical box-typical-padding">

    <?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => $uri
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
                    <div class="col-lg-10 col-md-12 columns">
                        <div class="row">

                            <div class="col-md-4 columns">
                                <label>Property Manager</label>
                                <select class="form-control" id="pm_filter" name="pm_filter">
                                    <option value="">---</option>
                                    <?php                                    
                                    foreach ( $pm_sql->result() as $pm_row ) {                                        
                                    ?>
                                        <option value="<?php echo $pm_row->aua_id ?>" <?php echo ( $pm_row->aua_id == $this->input->get_post('pm_filter') )?'selected':null; ?>><?php echo "{$pm_row->fname} {$pm_row->lname}"; ?></option>
                                    <?php
                                    }                           
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-2 columns">
                                <label>Expiry Year <span class="color-red">*</span></label>
                                <select class="form-control" id="alarm_expiry" name="alarm_expiry">
                                    <option value="">---</option>                                   
                                    <?php                                    
                                    $year_to = date('Y',strtotime('+10 years'));                                    
                                    $year_from = date('Y');                                     
                                    foreach ( range( $year_from, $year_to ) as $year ) { ?>
                                        <option value="<?php echo $year; ?>" <?php echo ( $year == $this->input->get_post('alarm_expiry') )?'selected':null; ?>><?php echo $year; ?></option>                                    
                                    <?php 
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-1 columns">
                                <label class="col-sm-12 form-control-label">&nbsp;</label>
                                <input class="btn" type="submit" name="btn_search" value="Search">
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

				<table class="table table-hover table-striped main-table">

					<thead>
						<tr>	
                            <th>Address</th> 
                            <th>Property Manager</th>    
                            <th>Total Number of alarms in property</th>
                            <th>9v expiring</th>
                            <th>240v expiring</th>                                   
						</tr>
                    </thead>

                    <tbody>
                    <?php    
                    if( $this->input->get_post('alarm_expiry') !='' ){               
                        if( $list_sql->num_rows() > 0 ){                           
                            foreach( $list_sql->result() as $row ){ ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo $this->config->item('crm_link'); ?>/view_property_details.php?id=<?php echo $row->property_id; ?>">
                                            <?php echo "{$row->p_street_num} {$row->p_street_name} {$row->p_suburb} {$row->p_state}{$row->p_postcode} "; ?>
                                        </a>
                                    </td>
                                    <td><?php echo "{$row->pm_fname} {$row->pm_lname}"; ?></td>
                                    <td><?php echo $row->al_qty; ?></td>   
                                    <td><?php echo $row->al_9v_count; ?></td>
                                    <td><?php echo $row->al_240v_count; ?></td>                             
                                </tr>
                            <?php                    
                            } ?>                        
                        <?php
                        }else{ ?>
                            <tr><td colspan='5'>Empty</td></tr>
                        <?php
                        }
                    }else{ ?>
                        <tr><td colspan='5'>Please Filter by Expiry Year before Submitting</td></tr>
                    <?php
                    }               
                    ?>
                    <tr>
                        <td colspan="2"><b>TOTAL</b></td>
                        <td><b><?php echo $tot_exp_al; ?></b></td>   
                        <td><b><?php echo $tot_exp_9v; ?></b></td>  
                        <td><b><?php echo $tot_exp_240v; ?></b></td>                       
                    </tr>
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
    
	<p>This page shows all alarms due to expire in the selected year for the selected agency.<p>

    <p>
        <b>First Table Query:</b><br />
<pre style="overflow: hidden;">
SELECT 
    COUNT(al.`alarm_id`) AS al_qty,
    COUNT(CASE WHEN al.`alarm_power_id` = 1 THEN al.`alarm_id` END) AS al_9v_count,
    COUNT(CASE WHEN al.`alarm_power_id` = 2 THEN al.`alarm_id` END) AS al_240v_count,

    p.`property_id`, 
    p.`address_1` AS p_street_num, 
    p.`address_2` AS p_street_name, 
    p.`address_3` AS p_suburb,
    p.`state` AS p_state,
    p.`postcode` AS p_postcode,
    p.`pm_id_new`,

    pm.`agency_user_account_id` AS aua_id,
    pm.`fname` AS pm_fname,
    pm.`lname` AS pm_lname
FROM `alarm` AS al
LEFT JOIN `alarm_pwr` AS al_pwr ON al.`alarm_power_id` = al_pwr.`alarm_pwr_id`
LEFT JOIN `jobs` AS j ON al.`job_id` = j.`id`       
INNER JOIN (

    SELECT j_inner.property_id, MAX(j_inner.date) AS latest_date
    FROM `jobs` AS j_inner
    LEFT JOIN `property` AS p_inner ON j_inner.`property_id` = p_inner.`property_id` 
    LEFT JOIN `agency` AS a_inner ON p_inner.`agency_id` = a_inner.`agency_id`
    WHERE j_inner.`del_job` = 0
    AND p_inner.`deleted` = 0 
    AND a_inner.`status` = 'active'
    AND j_inner.`status` = 'Completed'
    AND j_inner.`assigned_tech` NOT IN(1,2)  
    AND a_inner.`agency_id` = {$agency_id} 
    GROUP BY j_inner.`property_id` DESC

) AS j3 ON ( j.property_id = j3.property_id AND j.date = j3.latest_date )                  
LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`  
LEFT JOIN `agency_user_accounts` AS pm ON p.`pm_id_new` = pm.`agency_user_account_id`               
WHERE j.`del_job` = 0
AND p.`deleted` = 0
AND a.`status` = 'active'
AND al.`ts_discarded` = 0  
AND j.`assigned_tech` NOT IN(1,2)                
AND a.`agency_id` = {$agency_id} 
AND al.`expiry` = '{$alarm_expiry}'            
GROUP BY p.`property_id`   
</pre>
	</p>

</div>
<!-- Fancybox END -->
<script>
jQuery(document).ready(function(){

    jQuery("#jform").submit(function(){

        var alarm_expiry = jQuery("#alarm_expiry").val();
        var error = '';

        if( alarm_expiry == '' ){
            error += 'Alarm Expiry field is required\n';
        }


        if( error != '' ){

            swal('',error,'error');
            return false;

        }else{
            return true;
        }        

    });

});
</script>
