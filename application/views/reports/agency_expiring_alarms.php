
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
                                <label>Agency <span class="color-red">*</span></label>
                                <select class="form-control" id="agency_filter" name="agency_filter">
                                    <option value="">---</option>
                                    <?php
                                    foreach ($agency_filter_sql->result() as $agency_filter_row) {                                        
                                    ?>
                                        <option value="<?php echo $agency_filter_row->agency_id ?>" <?php echo ( $agency_filter_row->agency_id == $this->input->get_post('agency_filter') )?'selected':null; ?>><?php echo $agency_filter_row->agency_name; ?></option>
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
                            <th class="first_col">Power Type</th> 
                            <th class="second_col">Quantity</th>                                       
						</tr>
                    </thead>

                    <tbody>
                    <?php
                    $al_qty_total = 0;                    
                    if( $this->input->get_post('agency_filter') > 0 && $this->input->get_post('alarm_expiry') != '' ){
                        if( $list_sql->num_rows() > 0 ){                           
                            foreach( $list_sql->result() as $row ){ ?>
                                <tr>
                                    <td class="first_col"><?php echo $row->alarm_pwr; ?></td>
                                    <td class="second_col"><?php echo $row->al_qty; ?></td>                                    
                                </tr>
                            <?php
                            // add total
                            $al_qty_total += $row->al_qty;                            
                            } ?>                        
                        <?php
                        }else{ ?>
                            <tr><td colspan='2'>Empty</td></tr>
                        <?php
                        }
                    }else{ ?>
                        <tr><td colspan='2'>Please Filter by Agency and Expiry Year before Submitting</td></tr>
                    <?php
                    }
                    ?>
                    <tr>
                        <td class="first_col"><b>TOTAL</b></td>
                        <td class="second_col"><b><?php echo $al_qty_total; ?></b></td>                        
                    </tr>
                    </tbody>

                </table>



                
               
                    <?php
                    if( $this->input->get_post('agency_filter') > 0 && $this->input->get_post('alarm_expiry') != '' ){ ?>
                        <h5>Agency Alarms</h5>
                        <table class="table table-hover table-striped main-table">

                            <thead>
                                <tr>	
                                    <th class="first_col">Power Type</th> 
                                    <th class="second_col">Price</th>                                       
                                </tr>
                            </thead>

                            <tbody>
                            <?php
                                if( $agen_al_sql->num_rows() > 0 ){                           
                                    foreach( $agen_al_sql->result() as $agen_al_row ){ ?>
                                        <tr>
                                            <td class="first_col"><?php echo $agen_al_row->alarm_pwr; ?></td>
                                            <td class="second_col">$<?php echo $agen_al_row->price; ?></td>                                    
                                        </tr>
                                    <?php
                                    }                             
                                }else{ ?>
                                    <tr><td colspan='2'>Empty</td></tr>
                                <?php
                                } ?>
                            </tbody>

                        </table>
                    <?php
                    }
                    ?>
                    



                
            </div>

        <!--
		<nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
        <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
        -->

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
SELECT al_pwr.`alarm_pwr`, al.`alarm_power_id`, COUNT(al.`alarm_id`) AS al_qty
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
    AND j_inner.`assigned_tech` != 1
    AND j_inner.`assigned_tech` != 2
    GROUP BY j_inner.`property_id` DESC

) AS j3 ON ( j.property_id = j3.property_id AND j.date = j3.latest_date )
LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id` 
LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`                 
WHERE j.`del_job` = 0
AND p.`deleted` = 0
AND a.`status` = 'active'
AND al.`ts_discarded` = 0    
AND j.`assigned_tech` != 1
AND j.`assigned_tech` != 2    
AND a.`agency_id` = {$agency_filter}
AND al.`expiry` = '{$alarm_expiry}'         
GROUP BY al.`alarm_power_id`
</pre>
    </p>  

    <p>
        <b>Second Table Query:</b><br />
<pre style="overflow: hidden;">
SELECT agen_al.`price`, al_pwr.`alarm_pwr`
FROM `agency_alarms` AS agen_al    
LEFT JOIN `alarm_pwr` AS al_pwr ON agen_al.`alarm_pwr_id` = al_pwr.`alarm_pwr_id`              
WHERE agen_al.`agency_id` = {$agency_filter}
";     
</pre>
	</p>

</div>
<!-- Fancybox END -->
<script>
jQuery(document).ready(function(){

    jQuery("#jform").submit(function(){

        var agency_filter = jQuery("#agency_filter").val();
        var alarm_expiry = jQuery("#alarm_expiry").val();
        var error = '';

        if( agency_filter == '' ){
            error += 'Agency field is required\n';
        }

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
