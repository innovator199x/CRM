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
	?>

    <!--
	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">
			<?php
		$form_attr = array(
			'id' => 'jform'
		);
		echo form_open($uri,$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-md-8 columns">
					<div class="row">


						<div class="col-mdd-3">
							<label>Status</label>
							<select name="page_display" class="form-control">
                                <option value="1" <?php echo ( $page_display == 1 )?'selected="selected"':''; ?>>Active</option>
                                <option value="0" <?php echo ( is_numeric($page_display) && $page_display == 0 )?'selected="selected"':''; ?>>Inactive</option>
                                <option value="-1" <?php echo ( $page_display == -1 )?'selected="selected"':''; ?>>ALL</option>
							</select>
						</div>



						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<input type="submit" name="search_submit" value="Search" class="btn">
						</div>

					</div>

				</div>
			</div>
			</form>
		</div>
	</header>
    -->

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">

				<table class="table table-hover table-striped main-table message_tbl">

					<thead>
						<tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Other Participants</th>
                            <th>Last Message</th>

                            <th class="text-right">

								<div class="checkbox-toggle show_all_div float-right">
									<input type="checkbox" id="show_all_chk" <?php echo ( $this->input->get_post('show_all') == 1 )?'checked':null; ?> />
									<label for="show_all_chk">Show ALL</label>
								</div>

								<button type="button" id="read_all_btn" class="btn float-right">Mark ALL as READ</button>

							</th>

						</tr>
					</thead>

					<tbody>
                    <?php
                    $i = 1;
					foreach( $msg_sql->result() as $msg ){ ?>
						<tr>
                            <td>
                                <?php echo date("d/m/Y",strtotime($msg->date)) ?>
                            </td>
                            <td>
                                <?php echo date("H:i",strtotime($msg->date)) ?>
                            </td>
                            <td>
								<ul>
									<li>
										<?php
										// other participants
										$sel_query = "
											sa.`FirstName`,
											sa.`LastName`,
											sa.`profile_pic`
										";
										$custom_where = "mg.`staff_id` != {$this->session->staff_id}";

										$mg_params = array(
											'sel_query' => $sel_query,
											'message_header_id' => $msg->message_header_id,
											'custom_where' => $custom_where,

											'sort_list' => array(
												array(
													'order_by' => 'sa.`FirstName`',
													'sort' => 'ASC',
												),
												array(
													'order_by' => 'sa.`LastName`',
													'sort' => 'ASC',
												)
											),

											'display_query' => 0
										);
										$mg_sql = $this->messages_model->get_message_group($mg_params);

										foreach( $mg_sql->result() as $mg ){ ?>
											<li>
												<img id="message_pic" src="/images/<?php echo $this->system_model->getAvatar($mg->profile_pic); ?>"  class='profile_pic_small border border-info'  />
												<?php echo "{$mg->FirstName} {$mg->LastName}"; ?>
											</li>
										<?php
										}
										?>
									</li>
								</ul>
							</td>
                            <td colspan="2">

							    <div class="float-left">
									<a href="<?php echo "/messages/convo/?id={$msg->message_header_id}"; ?>">
										<?php echo $msg->message; ?>
									</a>
							    </div>

								<?php

								// get last read message
								$last_read_by_str = "
									SELECT m.`message_id`
									FROM `message_read_by` AS mrb
									LEFT JOIN `message` AS m ON mrb.`message_id` = m.`message_id`
									WHERE m.`message_header_id` = {$msg->message_header_id}
									AND mrb.`staff_id` = {$this->session->staff_id}
									ORDER BY `message_id` DESC
									LIMIT 1
								";
								$last_read_by_sql = $this->db->query($last_read_by_str);

								$last_read_by = $last_read_by_sql->row();
								$last_read_msg_id = $last_read_by->message_id;

								if( $last_read_by_sql->num_rows() > 0 ){ // if user have ready by data (at least seen the convo)

									// count number of new messages from last read
									$read_by_str = "
										SELECT COUNT(m.`message_id`) AS mcount
										FROM `message` AS m
										WHERE m.`message_header_id` = {$msg->message_header_id}
										AND m.`message_id` > {$last_read_msg_id}
									";
									$read_by_sql = $this->db->query($read_by_str);
									$new_message_count = $read_by_sql->row()->mcount;

								}else{ // no ready by data (has not checked convo)

									// get all messages count
									$all_msg_str = "
										SELECT COUNT(m.`message_id`) AS mcount
										FROM `message` AS m
										WHERE m.`message_header_id` = {$msg->message_header_id}
									";
									$all_msg_sql = $this->db->query($all_msg_str);
									$new_message_count = $all_msg_sql->row()->mcount;

								}

								// count bubble
								if( $new_message_count > 0 ){ ?>
									<div class="new_msg_bubble float-left"><?php echo $new_message_count; ?></div>
								<?php
								}
								?>
							</td>
						</tr>
					<?php
                    $i++;
                    }
					?>
					</tbody>

				</table>

			</div>

			<div class="row">
				<div class="col-md-12">
					<a href="/messages/create">
						<button type="button" class="btn btn_create_msg">Create Message</button>
					</a>
				</div>
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
	<p>
    Displays messages between participants
	</p>

</div>

<!-- Fancybox END -->

<style>
.new_msg_bubble {
	background-color: #00aff0;
	width: 20px;
	height: 20px;
	padding: 4px;
	border-radius: 100%;
	position: relative;
	top: -1px;
	left: 11px;
	color:white;
	text-align: center;
	font-size: 12px;
	font-weight: bold;
}
.message_tbl .checkbox-toggle {
	position: relative;
    margin: 7px 10px;
}
</style>

<script>
jQuery(document).ready(function(){

	// show all toggle
	jQuery("#show_all_chk").click(function(){

		var is_ticked = jQuery(this).prop("checked");

		if( is_ticked == true ){ // show all
			window.location="/messages/index/?show_all=1";
		}else{ // show only unread
			window.location="/messages/index";
		}

	});

	jQuery("#read_all_btn").click(function(){

		swal({
			title: "Warning!",
			text: "Are you sure you want to mark ALL unread message as READ?",
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

				window.location="/messages/mark_as_read_all";

			}

		});


	});

});
</script>