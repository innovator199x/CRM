<style>
	.date_div {
		width: auto;
		margin-right: 13px;
	}

	table.dataTable thead > tr > th {
		padding-left: 10px !important;
		padding-right: initial !important;
	}

	table.dataTable thead .sorting:after,
	table.dataTable thead .sorting_asc:after,
	table.dataTable thead .sorting_desc:after {
		left: 80px !important;
		right: auto !important;
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
				'link' =>  $uri
			)
		);
		$bc_data['bc_items'] = $bc_items;
		$this->load->view('templates/breadcrumbs', $bc_data);
	?>
	<header class="box-typical-header">
		<form action="<?php $uri; ?>" method="post">
			<div class="box-typical box-typical-padding">
				<div class="for-groupss row">
					<div class="col-md-10 columns">
						<div class="row">	

							<div class="ml-2">
								<label for="agency_filter">Agency</label>
								<select id="agency_filter" name="agency_filter"  class="form-control">                                
									<option value="">---</option>
                                    <?php
                                    foreach( $distinct_agency_sql->result() as $distinct_agency_row ){ ?>
                                        <option value="<?php echo $distinct_agency_row->agency_id; ?>" <?php echo ( $distinct_agency_row->agency_id == $this->input->get_post('agency_filter') )?'selected':null;  ?>>
                                            <?php echo $distinct_agency_row->agency_name; ?>
                                        </option>
                                    <?php
                                    }
                                    ?>
								</select>							
							</div>

                            <div class="ml-2">
								<label for="job_status_filter">Job Status</label>
								<select id="job_status_filter" name="job_status_filter"  class="form-control">                                
									<option value="">---</option>
                                    <option value="Completed" <?php echo ( $this->input->get_post('job_status_filter') == 'Completed' )?'selected':null;  ?>>Completed</option>
                                    <option value="Cancelled" <?php echo ( $this->input->get_post('job_status_filter') == 'Cancelled' )?'selected':null;  ?>>Cancelled</option>
								</select>							
							</div>

							<div class="ml-2">
								<label class="col-sm-12 form-control-label">&nbsp;</label>
								<input type="submit" name="search_submit" id="search_submit" value="Search" class="btn">
							</div>				
						</div>
					</div>
				</div>
			</div>
		</form>
	</header>
	<div class="body-typical-body">
		<div class="table-responsive">
			<table class="table table-hover main-table table-striped" id="serverside-table">
				<thead>
					<tr>    
                        <th>Job ID</th>
                        <th>Job Type</th>
                        <th>Job Status</th>
                        <th>Property Address</th>
                        <th>Agency Name</th>                          									                            						                           
					</tr>
					<?php
					foreach( $list->result() as $row ){ ?>

						<tr>
                            <td>
                                <a href="<?php echo $this->config->item('crm_link'); ?>/view_job_details.php?id=<?php echo $row->jid; ?>">
									<?php echo $row->jid; ?>
								</a>
                            </td>
                            <td><?php echo $row->job_type; ?></td>
                            <td><?php echo $row->jstatus; ?></td>
							<td>
								<a href="<?php echo $this->config->item('crm_link'); ?>/view_property_details.php?id=<?php echo $row->property_id; ?>">
									<?php echo "{$row->address_1} {$row->address_2}, {$row->address_3} {$row->state} {$row->postcode}"; ?>
								</a>								
							</td>

							<td class="<?php echo ( $row->priority > 0 )?'j_bold':null; ?>">
									<a href="/agency/view_agency_details/<?php echo $row->agency_id; ?>">
										<?php echo $row->agency_name." ".( ( $row->priority > 0 )?' ('.$row->abbreviation.')':null ); ?>
									</a>
								</td>
						</tr>

					<?php
					}
					?>
				</thead>
				<tbody></tbody>
			</table>	
		</div>
	</div>

	<nav aria-label="Page navigation example" style="text-align:center">
		<?php echo $pagination; ?>
	</nav>

	<div class="pagi_count text-center">
		<?php echo $pagi_count; ?>
	</div>

</div>


<!-- Fancybox START -->
<!-- ABOUT TEXT -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >
	<h4><?php echo $title; ?></h4>
	<p>This page shows all properties that are not upgraded to the NEW QLD legislation</p>

	<pre><code style="line-height: 1.5;"><?php echo $main_query; ?></code></pre>
</div>


