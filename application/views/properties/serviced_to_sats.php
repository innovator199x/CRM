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
			'link' => "/properties/serviced_to_sats"
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
			echo form_open('properties/serviced_to_sats', $form_attr);
			?>
			<div class="for-groupss row">
				<div class="col-md-8 columns">
					<div class="row">

						<div class="col-mdd-3">
							<label>From</label>
							<input name="from_filter" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text" placeholder="ALL" value="<?php echo ($from_filter != '') ? date('d/m/Y', strtotime($from_filter)) : date('01/m/Y'); ?>">
						</div>

						<div class="col-mdd-3">
							<label>To</label>
							<input name="to_filter" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text" placeholder="ALL" value="<?php echo ($to_filter != '') ? date('d/m/Y', strtotime($to_filter)) : date('t/m/Y'); ?>>">
						</div>

						<div class="col-mdd-3">
							<label>Salesrep</label>
							<select id="salerep_filter" name="salerep_filter" class="form-control">
								<option value="">ALL</option>
								<?php
								foreach ($salesrep_filter->result() as $sr) { ?>
									<option value="<?php echo $sr->StaffID; ?>" <?php echo ($sr->StaffID == $this->input->get_post('salerep_filter')) ? 'selected="selected"' : null; ?>><?php echo $this->system_model->formatStaffName($sr->FirstName, $sr->LastName); ?></option>
								<?php
							}
							?>
							</select>
						</div>

						<div class="col-md-2 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<button type="submit" class="btn btn-inline">Search</button>
						</div>

					</div>

				</div>

				<div class="col-md-4">
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
							<th>Property Address</th>
							<th>Agency</th>
							<th>Service Type</th>
							<th>Status Changed</th>
							<th>Property Status</th>
							<th>Salesrep</th>
						</tr>
					</thead>

					<tbody>
						<?php

						foreach ($lists->result() as $row) {
							?>
							<tr>
								<td>
									<a href="<?php echo $this->config->item('crm_link'); ?>/view_property_details.php?id=<?php echo $row->property_id; ?>">
										<?php echo  "{$row->address_1} {$row->address_2}, {$row->address_3} {$row->state} {$row->postcode}"; ?>
									</a>
								</td>
								<td class="<?php echo ( $row->priority > 0 )?'j_bold':null; ?>">
									<a href="/agency/view_agency_details/<?php echo $row->agency_id; ?>">
										<?php echo $row->agency_name." ".( ( $row->priority > 0 )?' ('.$row->abbreviation.')':null ); ?>
									</a>
								</td>
								<td>
									<?php echo $row->type; ?>
								</td>
								<td>
									<?php echo date('d/m/Y', strtotime($row->status_changed)); ?>
								</td>
								<td>
									<?php echo ($row->deleted == 1) ? '<span class="text-danger">Inactive</span>' : 'Active';  ?>
								</td>
								<td>
									<?php echo $this->system_model->formatStaffName($row->FirstName, $row->LastName); ?>
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


<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;">

	<h4><?php echo $title; ?></h4>
	<p>
	This page shows properties updated to SATS service in the last month.
	</p>

	<pre>
	<code><?php echo $page_query; ?></code>
	</pre>

</div>
<!-- Fancybox END -->


<style>
	.main-table {
		border-left: 1px solid #dee2e6;
		border-right: 1px solid #dee2e6;
		border-bottom: 1px solid #dee2e6;
		margin-bottom: 20px;
	}

	.col-mdd-3 {
		-webkit-box-flex: 0;
		-ms-flex: 0 0 15.2%;
		flex: 0 0 15.2%;
		max-width: 15.2%;

		position: relative;
		width: 100%;
		min-height: 1px;
		padding-right: 15px;
		padding-left: 15px;
	}
</style>
<script>
	jQuery(document).ready(function() {


	});
</script>