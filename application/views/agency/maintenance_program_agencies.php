
<style>
    .col-mdd-3{
        max-width:25.5%;
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
        'link' => "/agency/maintenance_program_agencies"
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
        echo form_open('/agency/maintenance_program_agencies',$form_attr);
        ?>
            <div class="for-groupss row">
                <div class="col-lg-10 col-md-12 columns">
                    <div class="row">

                        <div class="col-mdd-3">
                            <label for="agency_select">Agency</label>
                            <input type="text" name="agency_filter" class="form-control" value="<?php echo $this->input->get_post('agency_filter') ?>">
                        </div>

                         <div class="col-mdd-3">
                           <label>Software</label>
                            <select id="software_filter" name="software_filter" class="form-control">
                                <option value="">ALL</option>
                                <?php 
                                    foreach($software_filter->result_array() as $row){
                                        $selected = ($this->input->get_post('software_filter')== $row['maintenance_id'])?'selected="true"':NULL;
                                ?>
                                    <option <?php echo $selected; ?> value="<?php echo $row['maintenance_id'] ?>"><?php echo $row['name'] ?></option>
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
				<table class="table table-hover main-table">
					<thead>
						<tr>
							<th>Agency</th>
							<th>Software</th>
							<th>Surcharge</th>
							<th>Message</th>
						</tr>
					</thead>

					<tbody>
                        <?php 
                            foreach($lists->result_array() as $row){
                        ?>
                                 <tr>
                                    <td><?php echo $this->gherxlib->crmlink('vad', $row['agency_id'], $row['agency_name'],'',$row['priority']); ?></td>
                                    <td><?php echo $row['m_name'] ?></td>
                                    <td><?php echo ($row['price']>0)?$row['price']:NULL ?></td>
                                    <td><?php echo $row['surcharge_msg'] ?></td>
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
    This page displays all Agencies who are using 3rd Party Maintenance software.
	</p>
    <pre>
<code>SELECT `a`.`agency_id`, `a`.`agency_name`, `m`.`name` as `m_name`, `am`.`price`, `am`.`surcharge_msg`
FROM `agency_maintenance` as `am`
LEFT JOIN `agency` as `a` ON `a`.`agency_id` = `am`.`agency_id`
LEFT JOIN `maintenance` as `m` ON `m`.`maintenance_id` = `am`.`maintenance_id`
WHERE `a`.`status` = 'active'
AND `am`.`status` = 1
AND `m`.`status` = 1
ORDER BY `a`.`agency_name` ASC
LIMIT 50</code>
    </pre>

</div>
<!-- Fancybox END -->


<script type="text/javascript">

  

</script>