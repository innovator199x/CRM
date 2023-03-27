
<style>
    .col-mdd-3{
        max-width:30.5%;
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
        'link' => "/agency/non_auto_renew_agencies"
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
        echo form_open('/agency/non_auto_renew_agencies',$form_attr);
        ?>
            <div class="for-groupss row">
                <div class="col-lg-10 col-md-12 columns">
                    <div class="row">

                        <div class="col-mdd-3">
                            <label for="agency_select">Agency</label>
                            <input type="text" name="agency_filter" class="form-control" value="<?php echo $this->input->get_post('agency_filter') ?>">
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
							<th>Contact Name</th>
							<th>Contact Number</th>
						</tr>
					</thead>

					<tbody>
                        <?php
                            foreach($lists->result_array() as $row){
                        ?>
                                 <tr>
                                    <td><?php echo $this->gherxlib->crmLink('vad', $row['agency_id'], $row['agency_name'], '', $row['priority']) ?></td>
                                    <td><?php echo $row['tenant_details_contact_name'] ?></td>
                                    <td><?php echo $row['tenant_details_contact_phone'] ?></td>
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
    This page shows all Agencies who do not allow auto renew and we need to contact to seek premission to renew their properties.
	</p>
    <pre>
<code>SELECT `a`.`agency_id`, `a`.`agency_name`, `a`.`status`, `a`.`tenant_details_contact_name`, `a`.`tenant_details_contact_phone`
FROM `agency` AS `a`
WHERE `a`.`status` = 'active'
AND `a`.`auto_renew` =0
AND `a`.`agency_name` LIKE '%%'
ORDER BY `a`.`agency_name` ASC
LIMIT 50</code>
    </pre>

</div>
<!-- Fancybox END -->


<script type="text/javascript">

  

</script>