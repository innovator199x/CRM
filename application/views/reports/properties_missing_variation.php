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
							<label>Service Type</label>
							<select id="service_type_filter" name="service_type_filter" class="form-control">
								<option value="">---</option>
                                <?php                                                                                      
                                foreach( $service_type_filter_sql->result() as $service_type_row ){                                   
                                ?>
                                    <option value="<?php echo $service_type_row->id; ?>" <?php echo (  $service_type_row->id == $this->input->get_post('service_type_filter') )?'selected':null; ?>>
                                        <?php echo $service_type_row->type; ?>
                                    </option>
                                <?php
                                }                                                              
                                ?>
							</select>							
						</div>

                        <div class="col-md-1 columns">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <input class="btn" type="submit" name="btn_search" value="Search" />
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
                            <th>Service type</th>    
                            <th>Agency Name</th>    
                            <th>Agency service price</th>
                            <th>Property address</th>  
                            <th>Property service price</th>                             
                            <th>Price difference</th>
						</tr>
					</thead>

					<tbody>
                        <?php                        
                        if( $this->input->get_post('btn_search') && $lists->num_rows() > 0 ){
                            foreach($lists->result() as $row){
                            ?>
                                <tr>        
                                    <td><?php echo $row->ajt_type; ?></td>
                                    <td>                         
                                        <a href="/agency/view_agency_details/<?php echo $row->agency_id; ?>">
                                            <span class="<?php echo ( $row->priority > 0 )?'j_is_bold':''; ?>"><?php echo $row->agency_name.( ( $row->priority > 0 )?' ('.$row->abbreviation.')':null ); ?></span>
                                        </a> 
                                    </td>   
                                    <td><?php echo '$'.number_format($row->as_price,2); ?></td>   
                                    <td>
                                        <a href="<?php echo $this->config->item("crm_link"); ?>/view_property_details.php?id=<?php echo $row->property_id; ?>">
                                            <?php echo "{$row->p_address_1} {$row->p_address_2}, {$row->p_address_3}"; ?>
                                        </a>
                                    </td>
                                    <td><?php echo '$'.number_format($row->ps_price,2); ?></td>                                    
                                    <td><?php echo '$'.number_format(($row->as_price-$row->ps_price),2); ?></td>                                                                 
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

<?php
if( $this->input->get_post('btn_search') ){ ?>

    <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
            <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
    </div>

<?php
}
?>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >
<h4><?php echo $title; ?></h4>
<pre><code><?php echo $sql_query; ?></code></pre>
</div>
<!-- Fancybox END -->