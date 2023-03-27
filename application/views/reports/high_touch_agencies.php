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
        'link' => $uri
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);

$export_links_params_arr = array(
    'date_from_filter' => $this->input->get_post('date_from_filter'),
    'date_to_filter' => $this->input->get_post('date_to_filter'),
    'tech_filter' =>  $this->input->get_post('tech_filter'),
    'reason_filter' =>  $this->input->get_post('reason_filter'),
    'job_type_filter' =>  $this->input->get_post('job_type_filter'),
    'date_filter' =>  $this->input->get_post('date')
);
$export_link_params = "/jobs/missed_jobs/?export=1&".http_build_query($export_links_params_arr);
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

                        <!--
                        <div class="col-mdd-3">
                            <label for="search">From</label>
                            <input type="text" name="from_date_filter" class="form-control flatpickr flatpickr-input" data-allow-input="true" value="<?php echo ( $this->input->get_post('from_date_filter') != "" )?$this->input->get_post('from_date_filter'):null; ?>" />
                        </div>

                        <div class="col-mdd-3">
                            <label for="search">To</label>
                            <input type="text" name="to_date_filter" class="form-control flatpickr flatpickr-input" data-allow-input="true" value="<?php echo ( $this->input->get_post('to_date_filter') != "" )?$this->input->get_post('to_date_filter'):null; ?>" />
                        </div>
                        -->

                        <div class="col-md-3">
							<label>Agency</label>
							<select id="agency_filter" name="agency_filter" class="form-control">
								<option value="">---</option>
                                <?php                                                           
                                foreach( $agency_filter_sql->result() as $agency_row ){                                   
                                ?>
                                    <option value="<?php echo $agency_row->agency_id; ?>" <?php echo (  $agency_row->agency_id == $this->input->get_post('agency_filter') )?'selected':null; ?>>
                                        <?php echo $agency_row->agency_name; ?>
                                    </option>
                                <?php
                                }                                
                                ?>
							</select>							
						</div>

                        <div class="col-md-3">
							<label>Staff</label>
							<select id="staff_filter" name="staff_filter" class="form-control">
								<option value="">---</option>
                                <?php                                                           
                                foreach( $staff_filter->result() as $staff_row ){                                   
                                ?>
                                    <option value="<?php echo $staff_row->StaffID; ?>" <?php echo (  $staff_row->StaffID == $this->input->get_post('staff_filter') )?'selected':null; ?>>
                                        <?php echo $this->system_model->formatStaffName($staff_row->FirstName,$staff_row->LastName); ?>
                                    </option>
                                <?php
                                }                                
                                ?>
							</select>							
						</div>

                        <div class="col-md-3">
                            <label>High Touch</label>
                            <select id="high_touch_filter" name="high_touch_filter" class="form-control">
								<option value="">ALL</option>  
                                <option value="1" <?php echo ( $this->input->get_post('high_touch_filter') == 1 )?'selected':null; ?>>Yes</option>    
                                <option value="0" <?php echo ( $this->input->get_post('high_touch_filter') == 0 && is_numeric($this->input->get_post('high_touch_filter')) )?'selected':null; ?>>No</option>                         
							</select>
                        </div>

                        <div class="col-md-1 columns">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <input class="btn" type="submit" name="btn_search" value="Search">
                        </div>
                        
                    </div>

                </div>
                
                <!--
                <div class="col-lg-2 columns">
                    <section class="proj-page-section float-right">
                        <div class="proj-page-attach">
                            <i class="fa fa-file-excel-o"></i>
                            <p class="name"><?php echo $title; ?></p>
                            <p>
								<a href="<?php echo $export_link ?>">
									Export
								</a>
                            </p>
                        </div>
                    </section>
				</div>
                -->
                                    
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
                            <th>High Touch</th>    
                            <th>Log Details</th> 
                            <th>Staff</th>    
                            <th>Timestamp</th>        
						</tr>
					</thead>

					<tbody>
                        <?php
                        if($lists->num_rows()>0){
                            foreach($lists->result() as $row){
                            ?>
                                <tr>    
                                    <td>                         
                                        <a href="/agency/view_agency_details/<?php echo $row->agency_id; ?>">
                                            <?php echo $row->agency_name; ?>
                                        </a> 
                                    </td>  
                                    <td><?php echo ( $row->priority == 1 )?'<span class="text-success">Yes</span>':'<span class="text-danger">No</span>'; ?></td>
                                    <td><?php echo $row->details; ?></td>
                                    <td><?php echo $this->system_model->formatStaffName($row->staff_fname,$row->staff_lname); ?></td> 
                                    <td><?php echo date('d/m/Y H:i',strtotime($row->created_date)); ?></td>                                                                       
                                </tr>
                            <?php
                            }
                        }else{
                            echo "<tr><td colspan='100%'>No Data</td></tr>";
                        } 
                        ?>
					</tbody>

				</table>
			</div>

 <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
        <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >
<h4><?php echo $title; ?></h4>
<pre><code><?php echo $sql_query; ?></code></pre>
</div>
<!-- Fancybox END -->


<script type="text/javascript">

function dynamic_reason(apv_type){    

    if( apv_type == 1 ){ // discount

        jQuery("#apvr_filter option[data-is_discount=1]").show(); // discount
        jQuery("#apvr_filter option[data-is_discount=0]").hide(); // surcharge                  
        
    }else{ // surcharge

        jQuery("#apvr_filter option[data-is_discount=1]").hide(); // discount
        jQuery("#apvr_filter option[data-is_discount=0]").show(); // surcharge                    

    }

}

jQuery(document).ready(function(){

    //run_ajax_job_filter();

    //$("a.inline_fancybox").fancybox({});

    // filter reason on load
    var type_filter = jQuery("#type_filter").val();
    dynamic_reason(type_filter);

    // reason filter
    jQuery("#type_filter").change(function(){

        var apv_type = jQuery(this).val();       

        dynamic_reason(apv_type);

    });

});

</script>