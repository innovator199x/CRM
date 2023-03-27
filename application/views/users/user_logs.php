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
				'title' => "User Logs",
				'status' => 'active',
				'link' => $_SERVER['SELF'],
			],
        ];
		$bc_data['bc_items'] = $bc_items;
		$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	<h3 class="mt-4">User Logs</h3>

	<table class="table table-hover border-horizontal border-bottom mt-2">
		<thead>
			<tr>
				<th style="width: 136px;">Date</th>
				<th class="w-25">Who</th>
				<th class="w-auto">Details</th>
				<th style="width: 100px;">Delete</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($logs as $userLog): ?>
			<tr>
				<td style="width: 136px;">
					<?= $this->customlib->formatYmdhisToDmy($userLog['date'], true) ?>
				</td>
				<td class="w-25">
					<?= $this->system_model->formatStaffName($userLog['FirstName'], $userLog['LastName']) ?>
				</td>
				<td class="w-auto">
					<?= $userLog['details'] ?>
				</td>
				<td style="width: 100px;" class="text-center">
					<button class="btn btn-danger delete_user_log" data-log_id="<?= $userLog['user_log_id'] ?>">Delete</button>
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
		This contains a paginated version of the user logs.
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
	$('.delete_user_log').on('click', function(evt) {
		var theButton = $(this);
		console.log(theButton.data('log_id'));
		if(confirm("Are you sure you want to delete")){
			jQuery.ajax({
				type: "POST",
				url: "/users/ajax_delete_user_log",
				data: {
					user_log_id: theButton.data('log_id')
				},
				dataType: "json",
				success: function( ret ){
					window.location.reload();
				}
			});
		}
		evt.preventDefault();
		return false;
	});
});
</script>