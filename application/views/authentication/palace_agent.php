
<link rel="stylesheet" href="/inc/css/lib/datatables-net/datatables.min.css">
<link rel="stylesheet" href="/inc/css/separate/vendor/datatables-net.min.css">
<script src="/inc/js/lib/datatables-net/datatables.min.js"></script>
<link type="text/css" href="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.11/css/dataTables.checkboxes.css" rel="stylesheet" />
<script type="text/javascript" src="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.11/js/dataTables.checkboxes.min.js"></script>

<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/palace/agent_palace/"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>
	<section>
		<div class="body-typical-body">
			<div class="row">

				<!-- CRM -->
				<div class="col-sm-12">				

					<form>
						<div class="container-fluid">

							<div class="row">

								<div class="col-sm-3">
									<fieldset class="form-group">
										<label class="form-label semibold" for="exampleInput">Select Agency</label>
										<div class="btn-group">
											<select id="agency_filter" name="agency_filter" class="form-control">
												<option value="0">-- Select --</option>
												<?php 
												foreach ($agenList->result_array() as $row) {
												?>
													<option value="<?=$row['agency_id']?>" <?=isset($selected) ? ($row['agency_id'] == $selected) ? 'selected' : '' : ''?> style="color:<?=(is_null($row['palace_agent_id']) || empty($row['palace_agent_id'])) ? 'red':''?>"><?=$row['agency_name']?></option>
												<?php
												}
												?>
											</select>					
										</div>
										<input type="hidden" id="diaryId">
									</fieldset>
								</div>

								<div class="col-sm-3">
										<label class="form-label semibold" for="exampleInput">Connected Agent Name</label>
										<div class="input-group mb-3">
										  <input type="text" class="form-control" id="suppName" disabled>
										</div>				
										
								</div>

								<div class="col-sm-6">
										<label class="form-label semibold" for="exampleInput">Connected Agent Id</label>
										<div class="input-group mb-3">
										  <input type="text" class="form-control" id="suppId" disabled>
										  <div class="input-group-append">
										    <button class="btn btn-outline-secondary btn-danger" type="button" id="removeSup">Remove</button>
										  </div>
										</div>				
										
								</div>

							</div>

							<div class="row">
								<div class="col-sm-12">
									<table class="table table-striped table-bordered supp_table" >
										<thead>
											<tr>
												<td>Email</td>
												<td>Primary Person</td>
												<td>Agent Id</td>
												<td></td>
											</tr>
										</thead>
										<tbody>
										</tbody>
									</table>
								</div>
							</div>

						</div>
					</form>
					
				</div>
				
				
				

			</div>
		</div>
	</section>

</div>

<script type="text/javascript">
	$(document).ready(function() {

 		$(".supp_table").hide();
		// crm and Palace list ajax load
		jQuery("#agency_filter").change(function(){

			$('.supp_table').DataTable().destroy();
			$("#diaryId").val("");

			var agency_id = parseInt(jQuery(this).val());
    		var table = $(".supp_table tbody");

			if( agency_id > 0 ){

				// get crm list
				jQuery('#load-screen').show(); 
				jQuery.ajax({
					url: "/palace/get_palace_agent",
					type: 'POST',
					data: { 
						'agency_id': agency_id
					}
				}).done(function( ret ){
					var res = JSON.parse(ret);
					$("#suppId").val(res.agent == null || res.agent == "" ? "Not yet connected" : res.agent);
					$("#suppName").val(res.agentName == null || res.agentName == "" ? "Not yet connected" : res.agentName.AgentFullName);
					$("#diaryId").val(res.diaryId);
				 	table.empty();
		            $.each(res.palace, function (a, b) {
		            	var dis = (res.agent == b.AgentCode) ? 'disabled' : '';
		            	var btnSuc = "btn-primary";
		            	var btnTxt = "Connect";
		            	if (res.agent == b.AgentCode) {
		            		btnSuc = 'btn-success';
		            		btnTxt = 'Connected';
		            	}

		                table.append("<tr><td>"+b.AgentEmail1+"</td>" +
		                	"<td>" + b.AgentFullName + "</td>" +
		                	"<td>" + b.AgentCode + "</td>" +
		                    "<td> <button type='button' class='btn con_agent "+btnSuc+"' id-attr='"+b.AgentCode+"' "+dis+" name-attr='"+b.AgentFullName+"'>"+btnTxt+"</button></td></tr>");
		            });
			 		$(".supp_table").show();
					if ( $.fn.dataTable.isDataTable( '.supp_table' ) ) {
					    table = $('.supp_table').DataTable();
					}
					else {
					    table = $('.supp_table').DataTable( {
							  "columnDefs": [
							    { "orderable": false, "targets": 3 }
							  ]
					    } );
					}



					jQuery('#load-screen').hide(); 


				});	

			}

		});

		jQuery(document).on("click","#removeSup",function(){

			var agencyId = $("#agency_filter").val();
			var diaryId = $("#diaryId").val();
			if (agencyId == 0) {
            	swal('','Please select an agency.','info');
			}else {
				// if (diaryId !== "") {
            		// swal('','Please remove agency diary code first.','error');
				// }else {
					swal({
						html:true,
						title: "Warning!",
						text: "You are about to remove agent Id on this agency. Are you sure you want to continue?",
						type: "warning",						
						customClass: 'swal-dup_prop',

						showCancelButton: true,
						confirmButtonClass: "btn-success",
						confirmButtonText: "Yes",
						cancelButtonText: "Cancel!",
						cancelButtonClass: "btn-danger",
						closeOnConfirm: true,
						showLoaderOnConfirm: true,
						closeOnCancel: true							
					},
						function(isConfirm) {
							if (isConfirm) {
								jQuery('#load-screen').show(); 
								jQuery.ajax({
									url: "/palace/remove_agent_id_by_agency",
									type: 'POST',
									data: { 
										'agencyId': agencyId
									}
								}).done(function( crm_ret ){

									if (crm_ret == false) {
            							swal('','Please remove agency diary first.','error');
						                $('#load-screen').hide(); //hide loader
									}else {
										$("#suppId").val("Not yet connected");
										$("#suppName").val("Not yet connected");

						    			$("#agency_filter > option").each(function() {
						    				if ($(this).val() == agencyId) {
						    					$(this).css("color", "red");
						    				}
										});

							            $('.con_agent').each(function(i, obj) {
						            		if ($(this).prop('disabled', true)) {
						            			$(this).prop('disabled', false)
						            			$(this).removeClass('btn-success');
					            				$(this).text("Connect");
						            		}
										});
						                swal('','Successfully removed agent for this agency.','info');
						                $('#load-screen').hide(); //hide loader
									}
								});														
							}
						}
					);
				// }
			}

		})

		jQuery(document).on("click",".con_agent",function(){
			var but = $(this);
			var id = $(this).attr("id-attr");
			var name = $(this).attr("name-attr");
			var agencyId = $("#agency_filter").val();
			var diaryId = $("#diaryId").val();

			// if (diaryId == "") {
            	// swal('','Please add agency diary code first.','error');
			// }else {
				jQuery('#load-screen').show(); 
				jQuery.ajax({
					url: "/palace/update_agent_id_by_agency",
					type: 'POST',
					data: { 
						'id': id,
						'agencyId': agencyId
					}
				}).done(function( crm_ret ){
					if (crm_ret == false) {
            			swal('','Please add agency supplier id first.','error');
		                $('#load-screen').hide(); //hide loader
					}else {
						$("#suppId").val(id);
						$("#suppName").val(name);
		                swal('','Successfully updated agent for this agency.','info');
		                $('#load-screen').hide(); //hide loader

		    			but.addClass('btn-success');

		    			$("#agency_filter > option").each(function() {
		    				if ($(this).val() == agencyId) {
		    					$(this).css("color", "");
		    				}
						});

			            $('.con_agent').each(function(i, obj) {

			            		if ($(this).prop('disabled', true)) {
			            			$(this).prop('disabled', false);
			            			$(this).removeClass('btn-success');
		            				$(this).text("Connect");
			            		}
			            	if (id == $(this).attr("id-attr")) {
			            		$(this).prop('disabled', true);
		            			$(this).addClass('btn-success');
		            			$(this).text("Connected");
			            	}
						});
					}

				});
			// }
		})

	})
</script>