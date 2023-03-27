<div class="box-typical box-typical-padding">

	<?php
		// breadcrumbs template
		$bc_items = [
            [
				'title' => "SATS USERS",
				'status' => 'inactive',
				'link' => "/users/index",
			],
            [
				'title' => "User Details",
				'status' => 'inactive',
				'link' => "/users/view/{$id}",
			],
            [
				'title' => "System Logs",
				'status' => 'active',
				'link' => $_SERVER['SELF'],
			],
        ];
		$bc_data['bc_items'] = $bc_items;
		$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	<h3 class="mt-4">System Logs</h3>
	<div class="table-responsive">
		<table class="table table-hover border-horizontal border-bottom ">
			<thead>
				<tr>
					<th>Date</th>
					<th>Staff</th>
					<th>Title</th>
					<th>Details</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $logs  as $index => $row ): ?>
				<tr>
					<td><?php echo date('d/m/Y H:i',strtotime($row->created_date)); ?></td>
					<td>
						<img
							class="profile_pic_small border border-info mr-1"
							src="/images/<?= $this->system_model->getAvatar($row->photo) ?>"
							data-toggle="tooltip" title="<?= $row->FirstName ?>"
						/>
						<?= $this->system_model->formatStaffName($row->FirstName, $row->LastName) ?>
					</td>
					<td>
						<?php echo $row->title_name; ?>
					</td>
					<td>
						<?php
						echo $this->jcclass->parseDynamicLink2($row);
						?>
						<input type="hidden" class="log_id" value="<?php echo $row->log_id; ?>" />
					</td>
				</tr>
				<?php endforeach; ?>
				<?php if (empty($logs)): ?>
				<tr>
					<td colspan="4" class="text-center">
						-- No Entries --
					</td>
				</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

	<nav style="text-align:center">
		<?= $this->pagination->create_links(); ?>
	</nav>

</div>

<!-- Fancybox Start -->

<!-- ABOUT PAGE -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
		This contains a paginated version of the system logs related to the staff.
	</p>

</div>

<!-- Fancybox END -->

<style>
.table.border-horizontal tr {
	border-left: solid 1px #d8e2e7;
	border-right: solid 1px #d8e2e7;
}
.table.border-bottom {
	border-bottom: solid 1px #d8e2e7;
}
</style>

<script>

$('document').ready(function(event) {
});
</script>