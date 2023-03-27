
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
			'link' => "/palace/supplier_palace/"
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
													// if ((!is_null($row['palace_agent_id']) && !empty($row['palace_agent_id'])) && (!is_null($row['palace_diary_id']) && !empty($row['palace_diary_id']))) {
												?>
													<option value="<?=$row['agency_id']?>" <?=isset($selected) ? ($row['agency_id'] == $selected) ? 'selected' : '' : ''?> style="color:<?=(is_null($row['palace_supplier_id']) || empty($row['palace_supplier_id'])) ? 'red':''?>"><?=$row['agency_name']?></option>
												<?php
													// }
												}
												?>
											</select>					
										</div>
										<input type="hidden" id="agentId">
										<input type="hidden" id="diaryId">
									</fieldset>
								</div>

								<div class="col-sm-3">
										<label class="form-label semibold" for="exampleInput">Connected Supplier Name</label>
										<div class="input-group mb-3">
										  <input type="text" class="form-control" id="suppName" disabled>
										</div>				
										
								</div>

								<div class="col-sm-6">
										<label class="form-label semibold" for="exampleInput">Connected Supplier Id</label>
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
												<td>Reference</td>
												<td>Primary Person</td>
												<td>Supplier Id</td>
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
			$("#agentId").val("");
			$("#diaryId").val("");

			var agency_id = parseInt(jQuery(this).val());
    		var table = $(".supp_table tbody");

			if( agency_id > 0 ){

				// get crm list
				jQuery('#load-screen').show(); 
				jQuery.ajax({
					url: "/palace/get_palace_supplier",
					type: 'POST',
					data: { 
						'agency_id': agency_id
					}
				}).done(function( ret ){
					var res = JSON.parse(ret);
					$("#suppId").val(res.supp == null || res.supp == "" ? "Not yet connected" : res.supp);
					$("#suppName").val(res.suppName == null || res.suppName == "" ? "Not yet connected" : res.suppName.SupplierCompanyName);
					$("#agentId").val(res.agentId);
					$("#diaryId").val(res.diaryId);
				 	table.empty();
		            $.each(res.palace, function (a, b) {
		            	var dis = (res.supp == b.SupplierCode) ? 'disabled' : '';
		            	var btnSuc = "btn-primary";
		            	var btnTxt = "Connect";
		            	if (res.supp == b.SupplierCode) {
		            		btnSuc = 'btn-success';
		            		btnTxt = 'Connected';
		            	}

		                table.append("<tr><td>"+b.SupplierCompanyName+"</td>" +
		                	"<td>" + b.SupplierContactFirstName + " " +b.SupplierContactLastName +"</td>" +
		                	"<td>" + b.SupplierCode + "</td>" +
		                    "<td> <button type='button' class='btn con_supp "+btnSuc+"' id-attr='"+b.SupplierCode+"' "+dis+" name-attr='"+b.SupplierCompanyName+"'>"+btnTxt+"</button></td></tr>");
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
			var agentId = $("#agentId").val();
			var diaryId = $("#diaryId").val();

			if (agencyId == 0) {
            	swal('','Please select an agency.','info');
			}else {

				// if (agentId !== "" || diaryId !== "") {
        			// swal('','Please remove agency diary code and agent id first.','error');
				// }else {
					swal({
						html:true,
						title: "Warning!",
						text: "You are about to remove supplier Id on this agency. Are you sure you want to continue?",
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
									url: "/palace/remove_supplier_id_by_agency",
									type: 'POST',
									data: { 
										'agencyId': agencyId
									}
								}).done(function( crm_ret ){
									if (crm_ret == false) {
        								swal('','Please remove agency diary code and agent id first.','error');
						                $('#load-screen').hide(); //hide loader
									}else {
										$("#suppId").val("Not yet connected");
										$("#suppName").val("Not yet connected");

						    			$("#agency_filter > option").each(function() {
						    				if ($(this).val() == agencyId) {
						    					$(this).css("color", "red");
						    				}
										});

							            $('.con_supp').each(function(i, obj) {
						            		if ($(this).prop('disabled', true)) {
						            			$(this).prop('disabled', false)
						            			$(this).removeClass('btn-success');
					            				$(this).text("Connect");
						            		}
										});
						                swal('','Successfully removed supplier for this agency.','info');
						                $('#load-screen').hide(); //hide loader
									}
								});														
							}
						}
					);
				// }
			}

		})

		jQuery(document).on("click",".con_supp",function(){
			var but = $(this);
			var id = $(this).attr("id-attr");
			var name = $(this).attr("name-attr");
			var agencyId = $("#agency_filter").val();
			var agentId = $("#agentId").val();
			var diaryId = $("#diaryId").val();

			// if (agentId == "" || diaryId == "") {
            	// swal('','Please add agency diary code and agent id first.','error');
			// }else {
				jQuery('#load-screen').show(); 
				jQuery.ajax({
					url: "/palace/update_supplier_id_by_agency",
					type: 'POST',
					data: { 
						'id': id,
						'agencyId': agencyId
					}
				}).done(function( crm_ret ){

					if (crm_ret == false) {
            			swal('','Please add agency diary code and agent id first.','error');
		                $('#load-screen').hide(); //hide loader
					}else {
						$("#suppId").val(id);
						$("#suppName").val(name);
		                swal('','Successfully updated supplier for this agency.','info');
		                $('#load-screen').hide(); //hide loader

		    			but.addClass('btn-success');

		    			$("#agency_filter > option").each(function() {
		    				if ($(this).val() == agencyId) {
		    					$(this).css("color", "");
		    				}
						});

			            $('.con_supp').each(function(i, obj) {

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