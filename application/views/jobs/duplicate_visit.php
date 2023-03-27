
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
echo form_open('/daily/duplicate_visit',$form_attr);
?>
    <div class="for-groupss row">
        <div class="col-md-10 columns">
            <div class="row">

                <div class="col-mdd-3">
                    <label for="vehicle_select">Agency</label>
                    <select id="agency_filter" name="agency_filter" class="form-control field_g2">
                        <option value="">All</option>
                        <?php 
                            foreach($agency_filter->result_array() as $row){
                                $sel = ($row['agency_id']==$this->input->get_post('agency_filter')) ? "selected" : NULL ;
                        ?>
                                <option <?php echo $sel; ?> value="<?php echo $row['agency_id'] ?>"><?php echo $row['agency_name'] ?></option>
                        <?php
                            }
                        ?>
                    </select>
                </div>

                <div class="col-md-1 columns">
                    <label class="col-sm-12 form-control-label">&nbsp;</label>
                    <button type="submit" class="btn btn-inline">Search</button>
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
                        <th>Completed Job type</th>
                        <th>Active Job Type</th>
                        <th>Active Job Status</th>
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

                        // get active jobs
                        $active_job_sql = $this->daily_model->get_duplicate_visit_active_jobs($d['property_id']);
                        $active_job_row = $active_job_sql->row();

                ?>
                        <tr <?php echo ($i%2==0)?'style="border-right: 1px solid #cccccc; background-color: #efefef;"':''; ?>>                                                    
                            <td><?php echo $this->gherxlib->crmLink('vjd',$d['jid'],$d['job_type']); ?></td>
                            <td><?php echo $this->gherxlib->crmLink('vjd',$active_job_row->jid,$active_job_row->job_type); ?></td>
                            <td><?php echo $active_job_row->status; ?></td>
                            <td>
                                <?php
                                // if job type is 'IC Upgrade' show IC upgrade icon
                                $show_ic_icon = ( $d['job_type'] == 'IC Upgrade' )?1:0;
                                ?>
                                <img src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($d['service'],'',$show_ic_icon); ?>" />
                            </td>
                            <td><?php echo $this->gherxlib->crmLink('vpd',$d['property_id'],$paddress); ?></td>
                            <td><?php echo $d['state']; ?></td>
                            <td><?php echo $this->gherxlib->crmLink('vad',$d['agency_id'],$d['agency_name'],'',$d['priority']); ?></td>
                        </tr>
                <?php                    
                        $i++;
                    }
                    
                }else{
                    echo "<tr><td colspan='7'>No Data</td></tr>";
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

<p>
    This page captures jobs where we are attending again unnecessarily.<br />

    <!--<h5>Main Query</h5>-->
    <?php
    echo "<pre>";
    echo $about_text;
    echo "</pre>";
    ?>
    <?php
    $about_us_text = "
    SELECT  
        j.`id`, 
        j.`job_type`, 
        j.`status` , 
        j.`property_id` , 
        j.`service`,

        p.`address_1` , 
        p.`address_2` , 
        p.`address_3`, 
        p.`state`, 
        p.`deleted`, 

        a.`agency_id`, 
        a.`agency_name`    
    FROM `jobs` as j
    LEFT JOIN `property` as p ON p.`property_id` = j.`property_id`
    LEFT JOIN `agency` as a ON a.`agency_id` = p.`agency_id`
    WHERE 
    j.`status` = 'Completed'
    AND j.`date` >= ( CURDATE( ) - INTERVAL 30 DAY)
    AND j.`assigned_tech` != 1 
    AND j.`assigned_tech` != 2    
    AND j.`del_job` =0
    AND p.`deleted` =0
    AND a.`status` = 'active'
    AND a.`country_id` = {country_id}
    AND j.`property_id` IN(
        SELECT DISTINCT j2.`property_id`
        FROM `jobs` as j2
        LEFT JOIN `property` as p2 ON p2.`property_id` = j2.`property_id`
        LEFT JOIN `agency` as a2 ON a2.`agency_id` = p2.`agency_id`
        WHERE ( j2.`job_type` = 'Yearly Maintenance' OR j2.`job_type` = 'Annual Visit' )
        AND (
            j2.`status` != 'Completed' 
            AND j2.`status` != 'Merged Certificates' 
            AND j2.`status` != 'Pre Completion' 
            AND j2.`status` != 'Cancelled' 
            AND j2.`status` != 'DHA'
            AND j2.`status` != 'Pending'
        ) 
        AND j2.`del_job` =0
        AND p2.`deleted` =0
        AND a2.`status` = 'active'
        AND a2.`country_id` = {country_id}    
    )        
    ";
    ?>
    <pre style="overflow: hidden;"><?php 
    //echo $about_us_text; 
    ?></pre>

   <!-- <h5>Sub/Inner Query</h5> -->
    <?php
    $about_us_text = "
    SELECT 
        j.`id` AS jid,
        j.`job_type`,
        j.`status`
    FROM `jobs` as j     
    WHERE ( j.`job_type` = 'Yearly Maintenance' OR j.`job_type` = 'Annual Visit' )
    AND (
        j.`status` != 'Completed' 
        AND j.`status` != 'Merged Certificates' 
        AND j.`status` != 'Pre Completion' 
        AND j.`status` != 'Cancelled' 
        AND j.`status` != 'DHA'
    ) 
    AND j.`property_id` = {property_id} 
    AND j.`del_job` =0       
    ";
    ?>
    <pre style="overflow: hidden;"><?php 
    //echo $about_us_text; 
    ?></pre>
</p>

</div>
<!-- Fancybox END -->

<script type="text/javascript">

    jQuery(document).ready(function(){

    })

</script>