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
            'link' =>  $uri
        )
    );
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	<?php 
	if( validation_errors() ){ ?>
		<div class="alert alert-danger">
		<?php echo validation_errors(); ?>
		</div>
	<?php
	}	
	?>

	<style>
	.separator {
		margin: 0 5px;
	}
	.bold_it{
		font-weight: bold;
	}
	</style>
    
	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">
			<?php
		$form_attr = array(
			'id' => 'jform'
		);
		echo form_open($uri,$form_attr);
		?>
			<div class="for-groupss row">

				<div class="col-md-10 columns">
					<div class="row">	

                        <div class="col-md-4">
							<label for="agency_select">Agency</label>
							<select id="agency_filter" name="agency_filter_arr[]"  class="form-control field_g2" multiple>                                
                                <?php                                
                                foreach( $distinct_agency_sql->result() as $agency_row ){ ?>
                                    <option value="<?php echo $agency_row->agency_id; ?>" <?php echo ( in_array($agency_row->agency_id, $this->input->get_post('agency_filter_arr')) )?'selected="selected"':null; ?>>
                                        <?php echo $agency_row->agency_name; ?>
                                    </option>
                                <?php
                                }                                
                                ?>
							</select>							
						</div>

						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<input type="submit" name="search_submit" value="Search" class="btn">
						</div>
						
					</div>

				</div>

				<!-- DL ICONS START -->
                <?php 
                $date = ($this->input->get_post('date')!="")?date('Y-m-d',$this->input->get_post('date')):NULL;
                 ?>
			    <div class="col-lg-2 col-md-12 columns">
                    <section class="proj-page-section float-right">
                        <div class="proj-page-attach">
                            <i class="fa fa-file-excel-o"></i>
                            <p class="name"><?php echo $title; ?></p>
                            <p>
								<a href="<?php echo $export_link; ?>" target="blank">
									Export
								</a>
                            </p>
                        </div>
                    </section>
				</div>
				<!-- DL ICONS END -->


			</div>
			</form>
		</div>
	</header>

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
            
				<table class="table table-hover main-table table-striped">

					<thead>
						<tr>    
                            <th>Address
								<a data-toggle="tooltip" class="a_link <?php echo $sort ?>" href="<?php echo "{$uri}/?sort_header=1&order_by=p.address_2&sort={$toggle_sort}&".http_build_query($header_link_params); ?>">
									<em class="fa fa-sort-<?php echo $sort; ?>"></em>
								</a>
							</th> 
                            <th>State</th> 
                            <th>Service</th>  
							<th>Recent Job
								<a data-toggle="tooltip" class="a_link <?php echo $sort ?>" href="<?php echo "{$uri}/?sort_header=1&order_by=j.id&sort={$toggle_sort}&".http_build_query($header_link_params); ?>">
									<em class="fa fa-sort-<?php echo $sort; ?>"></em>
								</a>
							</th> 
                            <th>Job Date
								<a data-toggle="tooltip" class="a_link <?php echo $sort ?>" href="<?php echo "{$uri}/?sort_header=1&order_by=j.date&sort={$toggle_sort}&".http_build_query($header_link_params); ?>">
									<em class="fa fa-sort-<?php echo $sort; ?>"></em>
								</a>
							</th>  
                            <th>Job Type
								<a data-toggle="tooltip" class="a_link <?php echo $sort ?>" href="<?php echo "{$uri}/?sort_header=1&order_by=j.job_type&sort={$toggle_sort}&".http_build_query($header_link_params); ?>">
									<em class="fa fa-sort-<?php echo $sort; ?>"></em>
								</a>
							</th> 
							<th>Agency
								<a data-toggle="tooltip" class="a_link <?php echo $sort ?>" href="<?php echo "{$uri}/?sort_header=1&order_by=a.agency_name&sort={$toggle_sort}&".http_build_query($header_link_params); ?>">
									<em class="fa fa-sort-<?php echo $sort; ?>"></em>
								</a>
							</th>                             						                           
						</tr>
					</thead>

					<tbody>
                    <?php                                      
					foreach( $list->result() as $row ){                                       
                    ?>
						<tr>
                            <td>
                                <a href='<?php echo $this->config->item("crm_link"); ?>/view_property_details.php?id=<?php echo $row->property_id; ?>'>
                                    <?php echo "{$row->address_1} {$row->address_2}, {$row->address_3}"; ?>
                                </a>
                            </td>
                            <td><?php echo $row->state; ?></td>	
							<td>
                                <?php
								// display icons
								$job_icons_params = array(
									'service_type' => $row->alarm_job_type_id
								);
								echo $this->system_model->display_job_icons($job_icons_params);
								?>
                            </td>	
                            <td>
                                <?php
                                if(  $row->jid > 0 ){ ?>
                                    <a href='<?php echo $this->config->item("crm_link"); ?>/view_job_details.php?id=<?php echo $row->jid; ?>'>
                                        #<?php echo $row->jid; ?>
                                    </a>
                                <?php
                                }
                                ?>                                
                            </td>
                            <td><?php echo ( $this->system_model->isDateNotEmpty($row->jdate) )?date('d/m/Y', strtotime($row->jdate)):null; ?></td>
                            <td><?php echo $row->job_type; ?></td>	
							<td class="<?php echo ( $row->priority > 0 )?'j_bold':null; ?>">
								<a href='/agency/view_agency_details/<?php echo $row->agency_id; ?>'>
									<?php echo $row->agency_name." ".( ( $row->priority > 0 )?' ('.$row->abbreviation.')':null ); ?>
                                </a>
							</td>								                            
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


<!-- Fancybox START -->

<!-- ABOUT TEXT -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>This page shows all properties that are not upgraded to the NEW QLD legislation</p>

	<pre>
		<code><?php echo $sql_query; ?></code>
	</pre>

</div>

