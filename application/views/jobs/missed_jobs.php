<style>
    .col-mdd-3{
        max-width:15.5%;
    }
    .ht_class_bold {
        font-weight: bold;
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
        'link' => "/jobs/missed_jobs"
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
        echo form_open('/jobs/missed_jobs',$form_attr);
        ?>
            <div class="for-groupss row">
                <div class="col-lg-10 col-md-12 columns">
                    <div class="row">


                        <div class="col-mdd-3">
                            <label for="search">Date From</label>
                            <input type="text" placeholder="ALL" name="date_from_filter" class="form-control flatpickr" value="<?php echo ($this->input->get_post('date_from_filter')!="")?$this->system_model->formatDate($this->input->get_post('date_from_filter'),'d/m/Y'):date('d/m/Y') ?>" />
                        </div>

                          <div class="col-mdd-3">
                            <label for="search">Date To</label>
                            <input type="text" placeholder="ALL" name="date_to_filter" class="form-control flatpickr" value="<?php echo ($this->input->get_post('date_to_filter')!="")?$this->system_model->formatDate($this->input->get_post('date_to_filter'),'d/m/Y'):date('d/m/Y') ?>" />
                        </div>

                         <div class="col-mdd-3">
                            <label >Tech</label>
                            <select id="tech_filter" name="tech_filter" class="form-control field_g2">
                                <option value="">ALL</option>
                                 <?php
                                 foreach($tech_list->result_array() as $row){
                                     $selected = ($this->input->get_post('tech_filter')==$row['StaffID'])?'selected="true"':NULL;
                                     if(!empty($row['StaffID'])){
                                ?>
                                   <option <?php echo $selected; ?> value="<?php echo $row['StaffID']; ?>" <?php echo ($this->input->get_post('tech_filter')==$row['StaffID'])?'selected="selected"':''; ?>>
                                        <?php 
                                            echo $this->system_model->formatStaffName($row['FirstName'],$row['LastName']).( ( $row['is_electrician'] == 1 )?' [E]':null ); 
                                        ?>
                                    </option>
                                <?php
                                 }
                                }
                                 ?>
                            </select>
                        </div>
                        <div class="col-mdd-3">
                            <label>Reason</label>
                            <select id="reason_filter" name="reason_filter" class="form-control">
                                <option value="">ALL</option>
                               <?php
                               
                               foreach($reason_list->result_array() as $row){
                                   $selected = ($row['job_reason_id']==$this->input->get_post('reason_filter'))?'selected':'';
                                ?>

                                <option <?php echo $selected; ?> value="<?php echo $row['job_reason_id'] ?>"><?php echo $row['name'] ?></option>
                                <?php

                               }
                               
                               ?>
                            </select>
                        </div>

                        <div class="col-mdd-3">
							<label>Job Type</label>
							<select id="job_type_filter" name="job_type_filter" class="form-control">
								<option value="">ALL</option>
                                <?php                               
                               foreach( $job_type_sql_filter->result() as $job_row ){                                   
                                ?>
                                    <option value="<?php echo $job_row->job_type; ?>" <?php echo (  $job_row->job_type == $this->input->get_post('job_type_filter') )?'selected':null; ?>>
                                        <?php echo $job_row->job_type; ?>
                                    </option>
                                <?php
                                }
                                ?>
							</select>							
						</div>


                        <div class="col-mdd-3">
							<label>Agency</label>
							<select id="agency_filter" name="agency_filter" class="form-control">
								<option value="">ALL</option>
                                <?php                               
                               foreach( $agency_filter_sql->result() as $agency_row ){                                   
                                ?>
                                    <option value="<?php echo $agency_row->agency_id; ?>" <?php echo (  $agency_row->agency_id == $this->input->get_post('agency_filter') )?'selected':null; ?> class="<?php echo ($agency_row->priority == 1) ? 'ht_class_bold': ''; ?>">
                                        <?php echo $agency_row->agency_name; ?> <?php echo ($agency_row->priority == 1) ? "HT" : ""; ?>
                                    </option>
                                <?php
                                }
                                ?>
							</select>							
						</div>

                        <div class="col-mdd-3">
                            <label for="search">Include Doorknocks</label>
                            <div class="checkbox text-center txt-red">
                                <input type="checkbox" id="include_dk" name="include_dk" value="1" <?php echo (  $this->input->get_post('include_dk') == 1 )?'checked':null; ?> />   
                                <label for="include_dk"></label>                             
                            </div>
                        </div>
                      

                        <div class="col-md-1 columns">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <input class="btn" type="submit" name="btn_search" value="Search">
                        </div>
                        
                    </div>

                </div>

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
							<th>Date</th>
                            <th>Time</th>
							<th>Age</th>
							<th>Price</th>
							<th>Technician</th>
                            <th>Property</th>
                            <th>Agency</th>
                            <th>Reason</th>
                            <th>Comments</th>
                            <th>DK</th>
                            <th>Job Type</th>
						</tr>
					</thead>

					<tbody>

                        <?php
                            if($lists->num_rows()>0){
                            foreach($lists->result_array() as $row){

                                $full_prop_address = "{$row['address_1']} {$row['address_2']}, {$row['address_3']}";
                        ?>

                                <tr>
                                    <td>
                                    <?php  	echo ($row['jnc_date_created']!="")?date("d/m/Y",strtotime($row['jnc_date_created'])):''; ?>
                                    </td>
                                    <td>
                                    <?php  	echo ($row['jnc_date_created']!="")?date("H:i",strtotime($row['jnc_date_created'])):''; ?>
                                    </td>
                                    <td><?php echo $this->gherxlib->getAge($row['jcreated']);  ?></td>
                                   
                                    <td>
                                    <?php 
                                    //echo "$".number_format($row['job_price'],2);
                                    echo number_format($this->system_model->price_ex_gst($row['job_price']),2);
                                    $tot_price += $this->system_model->price_ex_gst($row['job_price'])
                                    ?>
                                    </td>

                                    <td>
                                    <?php
                                    echo $this->system_model->formatStaffName($row['jl_staff_fname'], $row['jl_staff_lname']);
                                    ?>
                                    </td>
                                    <td>                         
                                        <a href="<?php echo $this->config->item('crm_link') ?>/view_property_details.php?id=<?php echo $row['property_id']; ?>">
                                            <?php echo $full_prop_address; ?>
                                        </a> 
                                    </td>
                                    <td>
                                    <?php echo $this->gherxlib->crmLink('vad',$row['agency_id'],$row['agency_name'],'',$row['priority']); ?></td>
                                    <td>
                                    <?php 
                                    // contact type
                                    // echo (strpos($row['contact_type'],"DK"))?str_replace("DK",'',$row['contact_type']):$row['contact_type'];
                                    echo $row['jr_name'];

                                    ?>
                                    </td>
                                    <td><?php echo $row['reason_comment']; ?></td>
                                    <td><?php echo ( $row['jnc_door_knock'] == 1 )?'<span class="text-success">Yes</span>':null; ?></td>
                                    <td>
                                        <a href="<?php echo $this->config->item('crm_link') ?>/view_job_details.php?id=<?php echo $row['jid']; ?>">
                                            <?php echo $row['job_type']; ?>
                                        </a>                                        
                                    </td>
                                </tr>

                        <?php
                            }
                        
                        ?>
                        <tr style="background:#f6f8fa;">
                        <td colspan="3"><strong>Total Price</strong></td>
                        <td colspan="8"><strong><?php echo "$".number_format($tot_price,2); ?></strong></td>
                        </tr>

                        <?php }else{
                            echo "<tr><td colspan='11'>No Data</td></tr>";
                        } ?>
                      
                       
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
	<p>
    This page displays all jobs our techs missed in the selected date range
	</p>
    <p>Price are exclusive of GST.</p>
    <pre><code><?php echo $sql_query; ?></code></pre>

</div>
<!-- Fancybox END -->


<script type="text/javascript">

jQuery(document).ready(function(){

    run_ajax_job_filter();

    $("a.inline_fancybox").fancybox({});

});

</script>