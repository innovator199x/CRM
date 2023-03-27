
<style>
    .col-mdd-3{
        max-width:25.5%;
    }
    .ajax-status-check-ok{
        position: absolute;
        right: 5px;
        top: 9px;
        font-size: 20px;
        display: none;
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
        'link' => "/agency/subscription_billing_agencies"
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
        echo form_open('/agency/subscription_billing_agencies',$form_attr);
        ?>
            <div class="for-groupss row">
                <div class="col-lg-10 col-md-12 columns">
                    <div class="row">

                        <div class="col-mdd-3">
                            <label for="agency_select">Agency</label>
                            <input type="text" name="agency_filter" class="form-control" value="<?php echo $this->input->get_post('agency_filter') ?>">
                        </div>

                          <div class="col-mdd-3">
                            <label for="Subscription">Subscription</label>
                          <select class="form-control" name="subscription_filter">
                            <option value="">ALL</option>
                            <option value="1" <?php echo ( $this->input->get_post('subscription_filter') == 1 )?'selected="selected"':null; ?>>Yes</option> 
                            <option value="0" <?php echo ( is_numeric($this->input->get_post('subscription_filter')) && $this->input->get_post('subscription_filter') == 0 )?'selected="selected"':null; ?>>No</option> 
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
							<th>Subscription</th>
							<th>Notes</th>
							<th>Timestamp</th>
							<th>Who</th>
						</tr>
					</thead>

					<tbody>
                    <?php 
                        foreach($lists->result_array() as $row){
                    ?>
                             <tr>
                                <td><?php echo $this->gherxlib->crmLink('vad',$row['agency_id'], $row['agency_name'],'',$row['priority']) ?></td>
                                <td><?php echo ( $row['allow_upfront_billing'] == 1 )?'<span class="text-green">Yes</span>':'<span class="text-red">No</span>'; ?></td>
                                <td>
                                    <div class="pos-rel">
                                        <input type="text" name="subscription_notes" class="form-control subscription_notes" value="<?php echo $row['subscription_notes']; ?>" />	
                                        <input type="hidden" name="agency_id" class="addinput agency_id" value="<?php echo $row['agency_id']; ?>" />
                                        <i class="fa fa-check-circle text-green ajax-status-check-ok"></i>
                                    </div>
                                </td>
                                <td>
                                    <?php echo ($this->system_model->isDateNotEmpty($row['subscription_notes_update_ts']))?date("d/m/Y H:i",strtotime($row['subscription_notes_update_ts'])):NULL ?>
                                </td>
                                <td><?php echo $this->system_model->formatStaffName($row['snub_fname'],$row['snub_lname']); ?></td>
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
    This page shows all Agencies they are on the subsription billing model.
	</p>
    <pre>
<code>SELECT `a`.`agency_id`, `a`.`agency_name`, `a`.`allow_upfront_billing`, `a`.`subscription_notes`, `a`.`subscription_notes_update_ts`, `a`.`subscription_notes_update_by`, `snub`.`FirstName` AS `snub_fname`, `snub`.`LastName` AS `snub_lname`
FROM `agency` AS `a`
LEFT JOIN `staff_accounts` as `snub` ON `snub`.`StaffID` = `a`.`subscription_notes_update_by`
WHERE `a`.`status` = 'active'
AND `a`.`country_id` = <?php echo COUNTRY ?> 
ORDER BY `a`.`allow_upfront_billing` DESC
LIMIT 50</code>
    </pre>

</div>
<!-- Fancybox END -->


<script type="text/javascript">

     jQuery(document).ready(function(){

         jQuery(".subscription_notes").change(function(){

            var obj = jQuery(this);
            var subscription_notes = obj.val();
            var agency_id = obj.parents("tr:first").find(".agency_id").val();
            
            $('#load-screen').show(); //show loader
            jQuery.ajax({
                type: "POST",
                url: "/agency/ajax_update_agency_subscription_notes",
                data: { 
                    agency_id: agency_id,
                    subscription_notes: subscription_notes                
                }
            }).done(function( ret ){
                $('#load-screen').hide(); //hide loader
                obj.parents("tr:first").find(".ajax-status-check-ok").fadeIn();
                //fadeout timer
                setTimeout(function(){ 
                    obj.parents("tr:first").find(".ajax-status-check-ok").fadeOut();
                }, 5000);
            });

        });

     })
  

</script>