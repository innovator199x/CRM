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
                            <th>Property</th>    
                            <th>Agency</th>  
                            <th>Subscription Date</th>  
                            <th>Subscription Source</th>    
                            <th>Action</th>
						</tr>
					</thead>

					<tbody>
                        <?php
                        if($lists->num_rows()>0){
                            foreach($lists->result() as $row){
                            ?>
                                <tr>        
                                    <td>
                                        <a href="<?php echo $this->config->item("crm_link"); ?>/view_property_details.php?id=<?php echo $row->property_id; ?>">
                                            <?php echo "{$row->p_address_1} {$row->p_address_2}, {$row->p_address_3}"; ?>
                                        </a>
                                    </td>
                                    <td>                         
                                        <a href="/agency/view_agency_details/<?php echo $row->agency_id; ?>">
                                            <?php echo $row->agency_name; ?>
                                        </a> 
                                    </td>   
                                    <td>
                                        <input 
                                            type="text" 
                                            class="flatpickr form-control flatpickr-input subscription_date" 
                                            data-allow-input="true" 
                                            value="<?php echo ( $this->system_model->isDateNotEmpty($row->subscription_date) == true )?$this->system_model->formatDate($row->subscription_date, 'd/m/Y'):null;  ?>" 
                                        />
                                    </td>   
                                    <td>
                                        <select id="subscription_source" class="form-control subscription_source">
                                            <option value="">---</option>
                                            <?php                                                           
                                            foreach( $subs_source_sql->result() as $subs_source_row ){                                   
                                            ?>
                                                <option value="<?php echo $subs_source_row->id; ?>" <?php echo ( $subs_source_row->id == $row->source )?'selected':null; ?>><?php echo $subs_source_row->source_name; ?></option>
                                            <?php
                                            }                                
                                            ?>
                                        </select>	
                                    </td>      
                                    <td>
                                        <input type="hidden" class="property_id" value="<?php echo $row->property_id; ?>" />
                                        <button type="button" class="btn save_prop_subs_btn">Save</button>
                                    </td>                                                                
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

    jQuery(".save_prop_subs_btn").click(function(){

        var dom = jQuery(this);
        var parent_tr = dom.parents("tr:first");

        var property_id = parent_tr.find(".property_id").val();
        var subscription_date = parent_tr.find(".subscription_date").val();
        var subscription_source = parent_tr.find(".subscription_source").val();
        var error = '';

        if( property_id > 0 ){

            if( subscription_date == '' ){
			    error += "Subscription Date is required\n";
		    }

            if( subscription_source == '' ){
			    error += "Subscription Source is required\n";
		    }

            if( error != '' ){ // error
                swal('',error,'error');
            }else{ // no error

                jQuery('#load-screen').show();
                jQuery.ajax({
                    type: "POST",
                    url: "/reports/property_subscription_save",
                    data: { 					
                        property_id: property_id,
                        subscription_date: subscription_date,
                        subscription_source: subscription_source
                    }
                }).done(function( ret ){
                        
                    jQuery('#load-screen').hide();
                    swal({
                        title: "Success!",
                        text: "Property Subscription Save Success",
                        type: "success",
                        confirmButtonClass: "btn-success",
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });
                    setTimeout(function(){ location.reload(); }, <?php echo $this->config->item('timer') ?>);	

                });

            }            

        }        

    });

});

</script>