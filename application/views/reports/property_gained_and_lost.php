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
		<form action="/reports/property_gained_and_lost" method="post">
			<div class="box-typical box-typical-padding">
				<div class="for-groupss row">
					<div class="col-md-10 columns">
						<div class="row">	
							<div class="date_div ml-2">
								<label for="status_changed_from">From</label>
								<input name="status_changed_from" id="status_changed_from" class="flatpickr form-control" data-allow-input="true" id="flatpickr" type="text" value="<?php echo $this->system_model->formatDate($status_changed_from,'d/m/Y') ?>" />
							</div>
							<div class="date_div ml-2">
								<label for="status_changed_to">To</label>
								<input name="status_changed_to" id="status_changed_to" class="flatpickr form-control" data-allow-input="true" id="flatpickr" type="text" value="<?php echo $this->system_model->formatDate($status_changed_to,'d/m/Y') ?>" />
							</div>
							<div class="ml-2">
								<label for="view_type">View Type</label>
								<select id="view_type" name="view_type"  class="form-control field_g2">                                
									<option value="1" <?php echo ( $this->input->get_post('view_type') ==1 )?'selected="selected"':null; ?>>Gained</option>								
									<option value="2" <?php echo ( $this->input->get_post('view_type') ==2 )?'selected="selected"':null; ?>>Lost</option>
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
                        <th>Address
							<a data-toggle="tooltip" class="a_link <?php echo $sort ?>" href="<?php echo "{$uri}/?sort_header=1&order_by=address_2&sort={$toggle_sort}&status_changed_from={$status_changed_from}&status_changed_to={$status_changed_to}&view_type={$view_type}"; ?>">
								<em class="fa fa-sort-<?php echo $sort; ?>"></em>
							</a>
						</th> 						
                        <th>							
							Agency
							<a data-toggle="tooltip" class="a_link <?php echo $sort ?>" href="<?php echo "{$uri}/?sort_header=1&order_by=agency_name&sort={$toggle_sort}&status_changed_from={$status_changed_from}&status_changed_to={$status_changed_to}&view_type={$view_type}"; ?>">
								<em class="fa fa-sort-<?php echo $sort; ?>"></em>
							</a>
						</th>     
						<th>							
							Reason
						</th>                        									                            						                           
					</tr>
					<?php
					foreach( $list->result() as $row ){ ?>

						<tr>
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
							<td>
								<?php echo "{$row->reason}"; ?>
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

	<pre><code><?php echo $page_query ?></code></pre>
</div>

<script>
$(document).ready(function() {
	/*
	var table = $('#serverside-table').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [[ 0, "asc" ]],
		"bFilter": false,
		"pageLength": 50,
		"deferRender": true,
        "ajax": {
            "url": "<?php echo base_url('reports/gained_lost_serverside'); ?>",
            "dataType": "json",
            "type": "POST",
			"data": function ( d ) {
				return $.extend( {}, d, {
				"status_changed_from": $("#status_changed_from").val(),
				"status_changed_to": $("#status_changed_to").val(),
				"view_type": $("#view_type").val()
				} );
			}
        },
        "columns": [
            {
                "data": "address_1"
            },
            {
                "data": "agency_name"
            }
        ]
    });
    
    // Redraw the table based on the custom input
    $('#search_submit').bind("click", function(){
        table.draw();
    });
	*/
});
</script>
