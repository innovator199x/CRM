<style>
    .col-mdd-3{
        max-width:15.5%;
    }
    .j_is_bold{
        font-weight:bold;
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

                        <div class="col-md-3">
							<label>Agency</label>
							<select id="agency_filter" name="agency_filter" class="form-control">
								<option value="">---</option>
                                <?php                                                           
                                foreach( $agency_filter_sql->result() as $agency_row ){                                   
                                ?>
                                    <option 
                                        value="<?php echo $agency_row->agency_id; ?>" 
                                        <?php echo (  $agency_row->agency_id == $this->input->get_post('agency_filter') )?'selected':null; ?>
                                        class="<?php echo ( $agency_row->priority > 0 )?'j_is_bold':''; ?>"
                                    >
                                        <?php echo $agency_row->agency_name.( ( $agency_row->priority > 0 )?' ('.$agency_row->abbreviation.')':null ); ?>
                                    </option>
                                <?php
                                }                                
                                ?>
							</select>							
						</div>

                        <div class="col-md-3">
							<label>Property status</label>
							<select id="ps_filter" name="ps_filter" class="form-control">
								<option value="">---</option>
                                <option value="1" <?php echo (  $this->input->get_post('ps_filter') == 1  )?'selected':null; ?>>Active</option>
                                <option value="2" <?php echo (  $this->input->get_post('ps_filter') == 2 )?'selected':null; ?>>Not Active</option>
							</select>							
						</div>

                        <div class="col-md-1 columns">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <input class="btn" type="submit" name="btn_search" value="Search" />
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
                            <th>
                                Property address
                                <a 
                                    data-toggle="tooltip" 
                                    class="a_link <?php echo $sort ?>" 
                                    href="<?php echo "/reports/properties_from_other_company/?sort_header=1&order_by=p.address_2&sort={$toggle_sort}&".http_build_query($header_link_params); ?>"
                                >
                                    <em class="fa fa-sort-<?php echo $sort; ?>"></em>
                                </a>
                            </th>      
                            <th>Service Type</th>
                            <th>Agency
                                <a 
                                    data-toggle="tooltip" 
                                    class="a_link <?php echo $sort ?>" 
                                    href="<?php echo "/reports/properties_from_other_company/?sort_header=1&order_by=a.agency_name&sort={$toggle_sort}&".http_build_query($header_link_params); ?>"
                                >
                                    <em class="fa fa-sort-<?php echo $sort; ?>"></em>
                                </a>
                            </th>                                
                            <th>
                                Property status
                                <a 
                                    data-toggle="tooltip" 
                                    class="a_link <?php echo $sort ?>" 
                                    href="<?php echo "/reports/properties_from_other_company/?sort_header=1&order_by=ps.service&sort={$toggle_sort}&".http_build_query($header_link_params); ?>"
                                >
                                    <em class="fa fa-sort-<?php echo $sort; ?>"></em>
                                </a>
                            </th>                             
                            <th>
                                Date of creation
                                <a 
                                    data-toggle="tooltip" 
                                    class="a_link <?php echo $sort ?>" 
                                    href="<?php echo "/reports/properties_from_other_company/?sort_header=1&order_by=pfoc.added_date&sort={$toggle_sort}&".http_build_query($header_link_params); ?>"
                                >
                                    <em class="fa fa-sort-<?php echo $sort; ?>"></em>
                                </a>
                            </th>
                            <th>
                                Date of deactivation (If Applicable)
                                <a 
                                    data-toggle="tooltip" 
                                    class="a_link <?php echo $sort ?>" 
                                    href="<?php echo "/reports/properties_from_other_company/?sort_header=1&sort_multiple=1&order_by=p.nlm_timestamp,ps.status_changed&sort={$toggle_sort}&".http_build_query($header_link_params); ?>"
                                >
                                    <em class="fa fa-sort-<?php echo $sort; ?>"></em>
                                </a>
                            </th>
						</tr>
					</thead>

					<tbody>
                        <?php                                              
                        if( $lists->num_rows() > 0 ){
                            foreach($lists->result() as $row){
                            ?>
                                <tr>    
                                    <td>
                                        <a href="<?php echo $this->config->item("crm_link"); ?>/view_property_details.php?id=<?php echo $row->property_id; ?>">
                                            <?php echo "{$row->p_address_1} {$row->p_address_2}, {$row->p_address_3}"; ?>
                                        </a>
                                    </td>
                                    <td>								
                                        <?php
                                        // display icons
                                        $job_icons_params = array(
                                            'service_type' => $row->service_type
                                        );
                                        echo $this->system_model->display_job_icons($job_icons_params);
                                        ?>
                                    </td>
                                    <td>                         
                                        <a href="/agency/view_agency_details/<?php echo $row->agency_id; ?>"><?php echo $row->agency_name; ?></a> 
                                    </td>                                        
                                    <td><?php echo ( $row->ps_service == 1 )?'Active':'Not Active'; ?></td>
                                    <td><?php echo ( $this->system_model->isDateNotEmpty($row->pfoc_added_date) )?date('d/m/Y',strtotime($row->pfoc_added_date)):null; ?></td>
                                    <td>
                                    <?php 
                                        if( $row->is_nlm == 1 && $this->system_model->isDateNotEmpty($row->nlm_timestamp) ){
                                            echo date('d/m/Y',strtotime($row->nlm_timestamp));
                                        }else{

                                             // get property service
                                            $ps_sql = $this->db->query("
                                            SELECT 
                                                ps.`service`,
                                                ps.`status_changed`
                                            FROM `property_services` AS ps
                                            INNER JOIN `property` AS p ON ps.`property_id` = p.`property_id`
                                            WHERE p.`property_id` = {$row->property_id}                
                                            ");

                                            $ps_service_count = $ps_sql->num_rows(); // all property service count

                                            $not_sats_count = 0;
                                            $last_status_changed_date = null;
                                            foreach( $ps_sql->result() as $ps_row ){

                                                if( $ps_row->service != 1 ){ // not service to SATS
                                                    $not_sats_count++;
                                                    $last_status_changed_date = $ps_row->status_changed;
                                                }

                                            }

                                            if( $ps_service_count == $not_sats_count ){ // all service is not serviced to SATS
                                                echo ( $this->system_model->isDateNotEmpty($last_status_changed_date) )?date('d/m/Y',strtotime($last_status_changed_date)):null;
                                            }

                                        }
                                        ?>
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