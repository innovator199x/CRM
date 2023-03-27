<style>
    .col-mdd-3{
        max-width:15.5%;
    }
</style>

<?php
  $export_links_params_arr = array(
	'date_from_filter' => $this->input->get_post('date_from_filter'),
	'date_to_filter' => $this->input->get_post('date_to_filter'),
	'reason_filter' => $this->input->get_post('reason_filter'),
    'state_filter' =>  $this->input->get_post('state_filter')
);
$export_link_params = '/reports/discarded_alarms/?export=1&'.http_build_query($export_links_params_arr);
?>
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
        'link' => "/reports/discarded_alarms"
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
        echo form_open('/reports/discarded_alarms',$form_attr);
        ?>
            <div class="for-groupss row">


                <div class="col-md-10 columns">
                    <div class="row">


                        <div class="col-mdd-3">
                            <label for="search">From</label>
                            <input type="text" placeholder="ALL" name="date_from_filter" class="form-control flatpickr" value="<?php echo date('d/m/Y', strtotime($date_from_filter)) ?>" />
                        </div>

                         <div class="col-mdd-3">
                            <label for="search">To</label>
                            <input type="text" placeholder="ALL" name="date_to_filter" class="form-control flatpickr" value="<?php echo  date('d/m/Y', strtotime($date_to_filter)) ?>" />
                        </div>
                       

                         <div class="col-mdd-3">
                            <label for="agency_select">Reason</label>
                            <select id="reason_filter" name="reason_filter" class="form-control field_g2">
                                <option value="">ALL</option>
                              <?php 
                              foreach($reason_filter->result_array() as $row){
                                  $selected = ($row['id']==$this->input->get_post('reason_filter'))?'selected':NULL
                                ?>
                                    <option <?php echo $selected; ?> value="<?php echo $row['id'] ?>"><?php echo $row['reason'] ?></option>
                                <?php
                              }
                              ?>
                            </select>
                        </div>

                        <div class="col-mdd-3">
                            <label for="state"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></label>
                            <select id="state_filter" name="state_filter" class="form-control">
                                <option value="">ALL</option>
                            </select>
                            <div class="mini_loader"></div>
                        </div>

                      

                        <div class="col-md-1 columns">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <input class="btn" type="submit" name="btn_search" value="Search">
                        </div>
                        
                    </div>

                </div>

                <!-- DL ICONS START -->
				<div class="col-md-2 columns">
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
				<!-- DL ICONS END -->
                
             
                                    
                </div>
                </form>
                <div class="for-groupss row quickLinksDiv">
                
                    <div class="text-center col-md-12 columns">

                        Quick Links | <a href="<?php echo $paramsToday ?>">Today</a> | <a href="<?php echo $paramsThisMonth ?>">This Month</a> | <a href="<?php echo $paramsNextMonth ?>">Last Month</a>

                    </div>
                </div>
            </div>

            

        </header>

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<table class="table table-hover main-table">
					<thead>
						<tr>
							<th>Job Type</th>
                            <th>Job ID</th>
							<th>Job Date</th>
                            <th>Technicians</th>
							<th>Make</th>
							<th>Model</th>
							<th>Type</th>
                            <th>Power</th>
                            <th>Expiry</th>
                            <th>Reason</th>
                            <th>RFC</th>
                            <th>State</th>
						</tr>
					</thead>

					<tbody>
                        <?php 
                            if($lists->num_rows()>0){
                                foreach($lists->result_array() as $row){
                        ?>

                                <tr>
                                    <td>
                                        <?php echo $this->gherxlib->crmlink('vjd',$row['job_id'],$row['job_type']) ?>
                                    </td>
                                    <td><?php echo $row['job_id'] ?></td>
                                    <td>
                                    <?php echo ($this->system_model->isDateNotEmpty($row['date']))?$this->system_model->formatDate($row['date'],'d/m/Y'):NULL ?>
                                    </td>
                                    <td>
                                    <?php echo $row['FirstName'].' '.$row['LastName']; ?>
                                    </td>
                                    <td>
                                    <?php echo $row['make'] ?>
                                    </td>
                                    <td>
                                    <?php echo $row['model'] ?>
                                    </td>
                                    <td>
                                    <?php echo $row['alarm_type'] ?>
                                    </td>
                                    <td><?php echo $row['alarm_pwr'] ?></td>
                                    <td><?php echo $row['expiry'] ?></td>
                                    <td><?php echo $row['reason'] ?></td>
                                    <td><?php echo ($row['ts_required_compliance']==1)?'Yes':'No' ?></td>
                                    <td><?php echo $row['state'] ?></td>
                                </tr>

                        <?php
                                }
                            }else{
                                echo "<tr><td colspan='9'>No Data</td></tr>";
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
    This page displays data on all alarms we have removed from properties.
	</p>
<pre>
<code><?=$query_string?></code></pre>

</div>
<!-- Fancybox END -->


<script>

// state
function run_ajax_state_filter(){

var json_data = <?php echo $state_filter_json; ?>;
var searched_val = '<?php echo $this->input->get_post('state_filter'); ?>';

jQuery('#state_filter').next('.mini_loader').show();
jQuery.ajax({
	type: "POST",
		url: "/sys/header_filters",
		data: { 
			rf_class: 'jobs',
			header_filter_type: 'state',
			json_data: json_data,
			searched_val: searched_val
		}
	}).done(function( ret ){	
		jQuery('#state_filter').next('.mini_loader').hide();
		$('#state_filter').append(ret);
	});
			
}



jQuery(document).ready(function(){

    run_ajax_state_filter();

    


})


</script>
