<style type="text/css">
	.dataTables_paginate {
  display: inline-block;
}

.dataTables_paginate a {
  color: black;
  float: left;
  padding: 8px 16px;
  text-decoration: none;
  transition: background-color .3s;
  border: 1px solid #ddd;
}

.dataTables_paginate a.active {
  background-color: #4CAF50;
  color: white;
  border: 1px solid #4CAF50;
}
.inline_hid{
	display:none;
}
</style>
					<table class="table" id="myTable">
						<?php 
						$list = json_decode($list);
						?>
						<thead>
							<tr>
								<th>Id</th>
								<th>AddressText</th>
								<th>Notes</th>
								<!--
								<th>Action</th>
								-->	
							</tr>	
						</thead>
								<tbody>
							<?php if ($list == false) {

							} else {
								foreach($list as $row)
								{
								?>
									<tr>
										<td>
											<?=$row->Id?>
											<input type="hidden" class="prop_id" value="<?php echo $row->Id; ?>" />
										</td>
										<td><?=$row->AddressText?></td>
										<td>
											<label class="inline_lbl"><?php echo $row->Notes; ?></label>
											<!--<input type="text" class="form-control form-control-sm notes inline_hid" value="<?php echo $row->Notes; ?>" />-->
										</td>
										<!--	
										<td>
											<button class="btn edit_btn">
												<span class="btn_lbl">Edit</span>
											</button>
										</td>	
										-->								
									</tr>
								<?php
								}
							} 
							 ?>
								</tbody>
					</table>
			<!--
				<nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $links; ?></nav>
				<div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
			-->


<script type="text/javascript">
	$(document).ready(function() {
		
    	$('#myTable').DataTable( {
		  "lengthChange": false, "ordering": false
		});
    	
		// edit 
		jQuery(".edit_btn").click(function(){

			var btn_txt = jQuery(this).find(".btn_lbl").html();
			var orig_btn = 'Edit';

			if( btn_txt == orig_btn ){

				jQuery(this).parents("tr:first").find(".inline_lbl").hide();
				jQuery(this).parents("tr:first").find(".inline_hid").show();
				jQuery(this).find(".btn_lbl").html('Cancel');

			}else{

				jQuery(this).parents("tr:first").find(".inline_lbl").show();
				jQuery(this).parents("tr:first").find(".inline_hid").hide();
				jQuery(this).find(".btn_lbl").html(orig_btn);

			}

		});

		/*
		// update note
		jQuery(".notes").change(function(){

			var prop_id = jQuery(this).parents("tr:first").find(".prop_id").val();
			var notes = jQuery(this).val();

			if( prop_id != '' && notes != '' ){

				jQuery('#load-screen').show(); //show loader
				jQuery.ajax({
					type: "POST",
					url: "/property_me/update_property_notes",
					data: { 
						prop_id: prop_id,
						notes: notes
					}
				}).done(function( ret ){

					jQuery('#load-screen').hide(); 				 
						
				});	

			}			

		});
		*/

	})
</script>