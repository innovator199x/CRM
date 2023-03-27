
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
			'link' => "/palace/diary_palace/"
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

													$option_color = 'unset';
																						
													if( is_null($row['palace_diary_id'] || empty($row['palace_diary_id'])) ){
														$option_color =  'red';
													}

													if( $row['api_billable'] == 0 ){
														$option_color =  '#d4d4d4';
													}
												?>
													<option value="<?=$row['agency_id']?>" <?=isset($selected) ? ($row['agency_id'] == $selected) ? 'selected' : '' : ''?> style="color:<?=$option_color?>" data-api_billable="<?=$row['api_billable']?>"><?=$row['agency_name']?></option>
												<?php
												}
												?>
											</select>					
										</div>
									</fieldset>
								</div>

								<div class="col-sm-3">
										<label class="form-label semibold" for="exampleInput">Connected Diary Description</label>
										<div class="input-group mb-3">
										  <input type="text" class="form-control" id="suppName" disabled>
										</div>				
										
								</div>

								<div class="col-sm-6">
										<label class="form-label semibold" for="exampleInput">Connected Diary Id</label>
										<div class="input-group mb-3">
										  <input type="text" class="form-control" id="suppId" disabled>
										  <div class="input-group-append">
										    <button class="btn btn-outline-secondary btn-danger" type="button" id="removeSup">Remove</button>
										  </div>
										</div>				
										
								</div>

							</div>

							<p id="no_api_billable_div">See agency logs for more info</p>
							<div class="row">
								<div class="col-sm-12">
									<table class="table table-striped table-bordered api_table" >
										<thead>
											<tr>
												<td>Diary Group Description</td>
												<td>Diary Id</td>
												<td class="connect_col"></td>
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

<style>
#no_api_billable_div{
	display: none;
	color: red;
}
</style>
<script type="text/javascript">
	$(document).ready(function() {

 		$(".api_table").hide();
		// crm and Palace list ajax load
		jQuery("#agency_filter").change(function(){

			var node = jQuery(this);
			var option_selected =  node.find("option:selected"); // get selected option
			var api_billable = parseInt(option_selected.attr("data-api_billable")); // get api billable status

			$('.supp_table').DataTable().destroy();

			var agency_id = parseInt(node.val());
    		var table = $(".api_table tbody");

			if( agency_id > 0 ){

				// get crm list
				jQuery('#load-screen').show(); 
				jQuery.ajax({
					url: "/palace/get_palace_diary",
					type: 'POST',
					data: { 
						'agency_id': agency_id
					}
				}).done(function( ret ){
					var res = JSON.parse(ret);
					$("#suppId").val(res.diary == null || res.diary == "" ? "Not yet connected" : res.diary);
					$("#suppName").val(res.diaryName == null || res.diaryName == "" ? "Not yet connected" : res.diaryName.DiaryGroupDescription);
				 	table.empty();
		            $.each(res.palace, function (a, b) {
		            	var dis = (res.diary == b.DiaryGroupCode) ? 'disabled' : '';
		            	var btnSuc = "btn-primary";
		            	var btnTxt = "Connect";
		            	if (res.diary == b.DiaryGroupCode) {
		            		btnSuc = 'btn-success';
		            		btnTxt = 'Connected';
		            	}

		                table.append("<tr><td>" + b.DiaryGroupDescription + "</td>" +
		                	"<td>" + b.DiaryGroupCode + "</td>" +
		                    "<td class='connect_col'> <button type='button' class='btn con_diary "+btnSuc+"' id-attr='"+b.DiaryGroupCode+"' "+dis+" name-attr='"+b.DiaryGroupDescription+"'>"+btnTxt+"</button></td></tr>");
		            });
			 		$(".api_table").show();
					if ( $.fn.dataTable.isDataTable( '.api_table' ) ) {
					    table = $('.api_table').DataTable();
					}
					else {
					    table = $('.api_table').DataTable( {
							  "columnDefs": [
							    { "orderable": false, "targets": 2 }
							  ]
					    } );
					}


					if( api_billable == 0 ){
						jQuery(".connect_col").hide(); // connect column
						jQuery("#no_api_billable_div").show(); // api billable notes
					}else{
						jQuery(".connect_col").show(); 
						jQuery("#no_api_billable_div").hide();
					}
					jQuery('#load-screen').hide(); 


				});	

			}

		});

		jQuery(document).on("click","#removeSup",function(){

			var agencyId = $("#agency_filter").val();
			if (agencyId == 0) {
            	swal('','Please select an agency.','info');
			}else {
				swal({
					html:true,
					title: "Warning!",
					text: "You are about to remove Diary Id on this agency. Are you sure you want to continue?",
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
								url: "/palace/remove_diary_id_by_agency",
								type: 'POST',
								data: { 
									'agencyId': agencyId
								}
							}).done(function( crm_ret ){

								if (crm_ret == false) {
        							swal('','Please remove agency agent first.','error');
					                $('#load-screen').hide(); //hide loader
								}else {
									$("#suppId").val("Not yet connected");
									$("#suppName").val("Not yet connected");

					    			$("#agency_filter > option").each(function() {
					    				if ($(this).val() == agencyId) {
					    					$(this).css("color", "red");
					    				}
									});

						            $('.con_diary').each(function(i, obj) {
					            		if ($(this).prop('disabled', true)) {
					            			$(this).prop('disabled', false)
					            			$(this).removeClass('btn-success');
				            				$(this).text("Connect");
					            		}
									});
					                swal('','Successfully removed diary for this agency.','info');
					                $('#load-screen').hide(); //hide loader
								}
							});														
						}
					}
				);
			}

		})

		jQuery(document).on("click",".con_diary",function(){
			var but = $(this);
			var id = $(this).attr("id-attr");
			var name = $(this).attr("name-attr");
			var agencyId = $("#agency_filter").val();

			jQuery('#load-screen').show(); 
			jQuery.ajax({
				url: "/palace/update_diary_id_by_agency",
				type: 'POST',
				data: { 
					'id': id,
					'agencyId': agencyId
				}
			}).done(function( crm_ret ){

				if (crm_ret == false) {
        			swal('','Please add agency supplier id and agent id first.','error');
	                $('#load-screen').hide(); //hide loader
				}else {
					$("#suppId").val(id);
					$("#suppName").val(name);
	                swal('','Successfully updated diary for this agency.','info');
	                $('#load-screen').hide(); //hide loader

	    			but.addClass('btn-success');

	    			$("#agency_filter > option").each(function() {
	    				if ($(this).val() == agencyId) {
	    					$(this).css("color", "");
	    				}
					});

		            $('.con_diary').each(function(i, obj) {

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
		})

	})
</script>