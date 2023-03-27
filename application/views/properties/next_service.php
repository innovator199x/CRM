
<style>
    .col-mdd-3{
        max-width:15.5%;
    }
    .jtable td, .jtable th {
        border-top: none;
        height: auto;
    }
    .btn_show_job_details_box{
        margin-top: 15px;
    }
    .checkbox_box{
        margin-top:25px;
    }
    .atay_2{padding:0!important;margin:0!important;}
    table.awo tr td{border:0px;}
    .proj-page-attach i{font-size: 2.400rem !important;}
    .proj-page-attach{padding: 8px 0px 0 40px !important;}
    .flatpickr{width: 110px !important}
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

$export_links_params_arr = array(
    'agency_filter' => $this->input->get_post('agency_filter'),
    'state_filter' => $this->input->get_post('state_filter'),
    'date_filter_to'   => $this->input->get_post('date_filter_to'),
	'date_filter_from' => $this->input->get_post('date_filter_from')
);
$export_link_params = '/properties/next_service/?export=1&'.http_build_query($export_links_params_arr);
?>
    
    <header class="box-typical-header">
        <div class="box-typical box-typical-padding">

        <?php
            $tmp_dateto = $this->input->get_post('date_filter_to');
            $pos_dateto = strpos($tmp_dateto, '-');

            $tmp_datefrom = $this->input->get_post('date_filter_from');
            $pos_datefrom = strpos($tmp_datefrom, '-');

            if($pos_dateto !== false){
                $date_to = date("d/m/Y", strtotime($tmp_dateto));
            }
            else{
                $date_to = $tmp_dateto;
            }

            if($pos_datefrom !== false){
                $date_from = date("d/m/Y", strtotime($tmp_datefrom));
            }
            else{
                $date_from = $tmp_datefrom;
            }
        ?>
        <div class="for-groupss row">
            <div class="col-md-8 columns">
                <?php
                $form_attr = array(
                    'id' => 'nlm_reports',
                    'class' => ''
                );
                echo form_open('properties/next_service', $form_attr);
                ?>
                
                
                        <div class="row">
                    
                            <div class="col-md-2 columns">
                                <label>Agency</label>
                                <select class="form-control" id="agency_filter" name="agency_filter">
                                    <option value="">All</option>
                                    <?php
                                    foreach ($agency_filter->result_array() as $row) {
                                        $agency_sel = ($this->input->get_post('agency_filter')==$row['agency_id']) ? 'selected="true"' :NULL;
                                    ?>
                                        <option <?php echo $agency_sel; ?> value="<?php echo $row['agency_id'] ?>"><?php echo $row['agency_name'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-2 columns">
                                <label>State</label>
                                <select class="form-control" id="state_filter" name="state_filter">
                                    <option value="">All</option>
                                    <?php
                                    foreach ($state_filter->result_array() as $row) {
                                        $state_sel = ($this->input->get_post('state_filter')==$row['state']) ? 'selected="true"' :NULL;
                                    ?>
                                        <option <?php echo $state_sel; ?> value="<?php echo $row['state'] ?>"><?php echo $row['state'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-2 columns">
                                <label for="date_select">From Date</label>
                                <input name="date_filter_from" placeholder="" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text" value="<?php echo $date_from; ?>" autocomplete="off">
                            </div>
                            <div class="col-md-2 columns">
                                <label for="date_select">To Date</label>
                                <input name="date_filter_to" placeholder="" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr1" type="text" value="<?php echo $date_to; ?>" autocomplete="off">
                            </div>
                            <div class="col-md-3 columns">
                                <label class="col-sm-12 form-control-label">&nbsp;</label>
                                <button type="submit" class="btn btn-inline" name="submitFilter">Search</button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 columns">
                                <div class="row">
                                    <div class="col-md-6 columns">
                                        <label>Show Only</label>
                                        <select class="form-control" id="job_status_filter" name='job_status_filter'>
                                            <option value="">All</option>
                                            <option value='To Be Booked'>To Be Booked</option>
                                            <option value='Send Letters'>Send Letters</option>
                                            <option value='On Hold'>On Hold</option>											
                                            <option value='Booked'>Booked</option>
                                            <option value='Pre Completion'>Pre Completion</option>
                                            <option value='Merged Certificates'>Merged Certificates</option>   
                                            <option value='Completed'>Completed</option>  
                                            <option value='Pending'>Pending</option> 
                                            <option value='Cancelled'>Cancelled</option>
                                            <option value='Action Required'>Action Required</option>
                                            <option value='DHA'>DHA</option>
                                            <option value='To Be Invoiced'>To Be Invoiced</option>
                                            <option style='color:red;' value='Escalate'>Escalate **</option>
                                            <option style='color:red;' value='Allocate'>Allocate **</option>	
                                        </select>
                                    </div>
                                    <div class="col-md-4 columns">
                                        <div class="btn_show_job_details_box"><button type="button" class="btn btn_show_job_details">Show Job Details</button></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 columns">
                                <div class="row">
                                    <div class="col-md-6 columns">
                                        <label>Exclude Agency</label>
                                        <select class="form-control" id="ex_agency_filter" name="ex_agency_filter">
                                            <option value="">All</option>
                                            <?php
                                            foreach ($agency_filter->result_array() as $row) {
                                                $agency_sel = ($this->input->get_post('agency_filter')==$row['agency_id']) ? 'selected="true"' :NULL;
                                            ?>
                                                <option <?php echo $agency_sel; ?> value="<?php echo $row['agency_id'] ?>"><?php echo $row['agency_name'] ?></option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 columns">
                                        <label class="col-sm-12 form-control-label">&nbsp;</label>
                                        <button type="button" id="exclude_agency_btn" class="btn btn-inline">Exclude</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                
            
                <?php
                echo form_close();
                ?>
                </div>
                <!--
                <div class="col-md-2 columns text-right">  
                    <div class="checkbox checkbox_box">
                        <input class="include_booked_jobs" id="include_booked_jobs" name="include_booked_jobs" type="checkbox">
                        <label for="include_booked_jobs">Include booked jobs</label>
                    </div>
                </div>
                -->
                <div class="col-md-4 columns">
                    <div class="row">
                        <div class="col-md-12 columns">
                            <div class="proj-page-attach" style="float: right;">
                                <i class="fa fa-file-excel-o"></i>
                                <p class="name"><?php echo $title; ?></p>
                                <p>
                                    <a href="<?php echo $export_link_params ?>" target="blank">
                                        Export
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 columns">
                            <div id="display_tags" style="float: right;">
                                <?php
                                foreach( $nsea_sql->result() as $nsea_row ){ ?>
                                    <label class="col-sm-12 form-control-label">&nbsp;</label>
                                    <button type="button" class="btn btn-inline remove_ex_agency_btn"><?php echo $nsea_row->agency_name; ?></button>
                                    <input type="hidden" name="ex_agency_id[]" class="ex_agency_id" value="<?php echo $nsea_row->agency_id; ?>" /> 
                                    <input type="hidden" class="nsea_id" value="<?php echo $nsea_row->nsea_id; ?>" /> 
                                <?php
                                }
                                ?>                     
                            </div>
                        </div>                
                    </div>
                </div>
        </div>

    </header>
    

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<table class="table table-hover table-striped main-table">
					<thead>
						<tr>	
                            <th>Deadline</th>
                            <th>Retest Date</th>
                            <th>Property Address</th>	
                            <th>Agency</th>	   
                            <th style="width:200px;">Active Job Status</th>
                            <th style="width:150px;">Active Job Age</th>                         	                            
						</tr>
					</thead>

					<tbody>
                        <?php
                        
                        if($property_sql->num_rows() > 0){
                            foreach($property_sql->result() as $property_row){
                            $p_address = "{$property_row->p_address_1} {$property_row->p_address_2}, {$property_row->p_address_3}  {$property_row->p_state}   {$property_row->p_postcode}";
                        ?>
                            <tr class="aw_row" data-prowid="<?php echo $property_row->property_id ?>" data-jobid="<?php echo $property_row->j_id; ?>">     
                            <td>
                                <?php 
                                    // get deadline age
                                    $retest_date_ts = date_create(date('Y-m-d', strtotime($property_row->retest_date)));
                                    $today_ts = date_create(date('Y-m-d'));
                                    $diff = date_diff($today_ts,$retest_date_ts);
                                    $age = $diff->format("%r%a");
                                    $age_val = (((int) $age) != 0) ? $age : 0; 
                                    
                                    echo ( $age_val >= 0 )?$age_val:"<span class='text-red'>{$age_val}</span>";
                                    ?>
                                </td>
                                <td>
                                    <?php echo ($this->system_model->isDateNotEmpty($property_row->retest_date) == true) ? $this->system_model->formatDate($property_row->retest_date, 'd/m/Y') : ''; ?>
                                </td>                                         
                                <td>
                                    <input type="hidden" class="hid_prop_id" name="hid_prop_id" value="<?php echo $property_row->property_id; ?>">
                                    <a href="<?php echo "{$this->config->item('crm_link')}/view_property_details.php?id={$property_row->property_id}"; ?>">
                                        <?php echo $p_address; ?>
                                    </a>                    
                                </td> 
                                <td>
                                    <a href="<?php echo "/agency/view_agency_details/{$property_row->agency_id}"; ?>">
                                        <?php echo $property_row->agency_name; ?>
                                    </a>                    
                                </td>   
                                <td class="atay_2" colspan="2"></td>
                               <!-- <td class="atay"></td>
                                <td class="atay_2"></td>-->
                            </tr>
                        <?php   
                            }
                        }else{ ?>
                            <tr><td colspan='6'>No Data</td></tr>
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
	<p>This page shows the next service due date on a property, based on the date of the most recently completed job. <br/>
    Defence Housing jobs are currently excluded. <br/>
    If a property’s most recently completed job is a Once-Off job, opening the property and then refreshing the page should remove this property from the list.<br/>
    The “Show Job Details” button gets the most recently created job on a property, and shows the job’s status and age.<br/>
    On page load, properties with booked jobs appear, but will be hidden when you ‘Show Job Details’. If you’d like to see properties with booked jobs, please use the check box. 
    </p>

<br/>
<br/>
QUERY:
<br/>

<pre>
    <code><?=$sql_query?></code>
</pre>

</div>
<!-- Fancybox END -->


<script>
jQuery(document).ready(function(){
       
        $('.btn_show_job_details').click(function(){

           $('.aw_row').each(function(){
                var obj = $(this);   
                var prop_id = obj.attr('data-prowid');
                var job_type_td = 'aw_'+prop_id;
                var job_status_filter = $('#job_status_filter');
              //obj.find('.atay').load('/properties/ajax_get_recent_created_job_type',{prop_id:prop_id}, function(response, status, xhr){});
                obj.find('.atay_2').load('/properties/ajax_get_recent_created_job_age',{prop_id:prop_id}, function(response, status, xhr){

                    var obj2 = $(this);
                    var row_stat = obj2.find('.awo').attr('data-jobstatus');
                    
                    /*
                    if( !$('#include_booked_jobs').is(':checked')){
                        if(row_stat=='Booked' || job_status_filter.val()=="Booked"){
                            obj2.parent('.aw_row').hide();
                        }else if(  row_stat != job_status_filter.val() && job_status_filter.val()!="" ){
                            obj2.parent('.aw_row').hide();
                        }else{
                            obj2.parent('.aw_row').show();
                        }
                    }else{
                        if(row_stat=='Booked' && job_status_filter.val()=="Booked"){
                            obj2.parent('.aw_row').show();
                        }else if(  row_stat != job_status_filter.val() && job_status_filter.val()!="" ){
                            obj2.parent('.aw_row').hide();
                        }else{
                            obj2.parent('.aw_row').show();
                        }
                    } */

                    if(  row_stat != job_status_filter.val() && job_status_filter.val()!="" ){
                        obj2.parent('.aw_row').hide();
                    }else{
                        obj2.parent('.aw_row').show();
                    }
                   
                });
           })
           
        });


        // add agency to excluded filter
        jQuery("#exclude_agency_btn").click(function(){


            var ex_agency = jQuery("#ex_agency_filter").val();            

            if( ex_agency > 0 ){

                $('#load-screen').show();
                jQuery.ajax({
                    type: "POST",
                    url: "/properties/add_next_service_exclude_agency",
                    data: { 	
                        ex_agency: ex_agency
                    }
                }).done(function( ret ){
                                        
                    $('#load-screen').hide();	
                    location.reload();

                });
                
            }
            
            

        });


        // remove agency from excluded filter
        jQuery(".remove_ex_agency_btn").click(function(){

            var node = jQuery(this);
            var parent_div = node.parents("div#display_tags")
            var nsea_id = parent_div.find(".nsea_id").val();
            console.log("nsea_id: "+nsea_id);

            if( nsea_id > 0 ){

                swal({
					title: "Warning!",
					text: "This will remove this agency from the excluded filter? Do you want to continue?",
					type: "warning",						
					showCancelButton: true,
					confirmButtonClass: "btn-success",
					confirmButtonText: "Yes, Continue",
					cancelButtonClass: "btn-danger",
					cancelButtonText: "No, Cancel!",
					closeOnConfirm: true,
					showLoaderOnConfirm: true,
					closeOnCancel: true
				},
				function(isConfirm) {

					if (isConfirm) {							  
						
						$('#load-screen').show();
                        jQuery.ajax({
                            type: "POST",
                            url: "/properties/remove_next_service_exclude_agency",
                            data: { 	
                                nsea_id: nsea_id
                            }
                        }).done(function( ret ){
                                                
                            $('#load-screen').hide();	
                            location.reload();

                        });				

					}

				});	

                

            }
            

        });
        

    
});
</script>