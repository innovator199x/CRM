
<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/property_me/upload_invoice/"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>
	<section>
		<div class="body-typical-body">
			<div id="crm_pme_table_div" class="row">

				<!-- CRM -->
				<div class="col-sm-12 crmClass">				

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
												foreach ($agenList->result_array() as $row) { ?>
													<option value="<?=$row['agency_id']?>" <?=isset($selected) ? ($row['agency_id'] == $selected) ? 'selected' : '' : ''?>><?=$row['agency_name']?></option>
												<?php
												}
												?>
											</select>					
										</div>
									</fieldset>
								</div>

								<div class="col-sm-6">
									<fieldset class="form-group">
										<label class="form-label semibold" for="exampleInput">Connected Properties 
											<!-- - 
											(<a id="selectAll" href="#">select all</a>) (<a id="deselectAll" href="#">deselect all</a>) --> 
										</label>
										<select id="prop_filter" name="prop_filter" class="form-control"  size="3" multiple style="width: 500px;">
											<option value="0">-- Select Agency --</option>
										</select>	
									</fieldset>
								</div>

								<div class="col-sm-3">
									<fieldset class="form-group">
										<label class="form-label semibold" for="exampleInput">Select File</label>
										<div class="btn-group">
											<input type="file" id="filePDF" name="test" accept=".pdf"/>		
										</div>
									</fieldset>
								</div>

							</div>

							<div class="row">
								<div class="col-sm-6">
									<fieldset class="form-group">
										<button type="button" id="btn-upload" class="btn btn-primary">Upload File</button>
									</fieldset>
								</div>
							</div>

							<div class="row">
								<div class="col-sm-12">
									<fieldset class="form-group">
										<label class="form-label semibold" for="exampleInput">API Response:</label>
										<textarea class="form-control" rows="10" id="apiResponse" disabled=""></textarea>	
									</fieldset>
								</div>
							</div>

						</div>
					</form>

					<!-- load PME here -->
					<div id="crm_table_div"></div>
					
				</div>

				
				
				

			</div>
		</div>
	</section>

</div>

<script type="text/javascript">
	$(document).ready(function() {

		// crm and Pme list ajax load
		jQuery("#agency_filter").change(function(){

			var agency_id = parseInt(jQuery(this).val());

			if( agency_id > 0 ){

				// get crm list
				jQuery('#load-screen').show(); 
				jQuery.ajax({
					url: "/property_me/ajax_get_connected_prop",
					type: 'POST',
					data: { 
						'agency_id': agency_id
					}
				}).done(function( crm_ret ){
					var res = JSON.parse(crm_ret);
					var option = "";
					$.each(res, function(i, item) {
						option += "<option value='"+res[i].propertyme_prop_id+"'>"+res[i].address_1
					    +" "+ res[i].address_2
					    +" "+ res[i].address_3
					    +" "+ res[i].state
					    +" "+ res[i].postcode+"</option>";
					});
					$("#prop_filter").html(option);
					jQuery('#load-screen').hide(); 


				});	

			}

		});

		$("#selectAll").on("click", function() {
	        $('#prop_filter option').prop('selected', true);
		})

		$("#deselectAll").on("click", function() {
	        $('#prop_filter option').prop('selected', false);
		})

		$("#btn-upload").on("click", function() {
			var agencyId = $("#agency_filter").val();
	        var brands = $('#prop_filter option:selected');
	        var selected = [];
	        $(brands).each(function(index, brand){
	            selected.push([$(this).val()]);
	        });

	        var file_data = $('#filePDF').prop('files')[0];   
		    var form_data = new FormData();                  
		    form_data.append('file', file_data);
		    form_data.append('agencyId', agencyId);
		    form_data.append('selected', selected);

		    var proceed = true;

		    if ($('#filePDF').get(0).files.length === 0) {
    			swal({
					title: "Upload Failed!",
					text: "No PDF file selected",
					type: "warning",
					confirmButtonClass: "btn-danger"
				});
		    	proceed = false;
			}

		    if (selected.length <= 0) {
    			swal({
					title: "Upload Failed!",
					text: "Select property/properties",
					type: "warning",
					confirmButtonClass: "btn-danger"
				});
		    	proceed = false;
		    }

		    if (agencyId == "0") {
    			swal({
					title: "Upload Failed!",
					text: "Please select agency",
					type: "warning",
					confirmButtonClass: "btn-danger"
				});
		    	proceed = false;
		    }


			if (proceed) {
				jQuery(".loading-bar-div").show();
				jQuery.ajax({
		            method: 'post',
		            processData: false,
		            contentType: false,
		            cache: false,
		            data: form_data,
		            enctype: 'multipart/form-data',
					url: "/palace/ajax_upload_file",
				}).done(function( crm_ret ){
					jQuery(".loading-bar-div").hide();
					var res = JSON.parse(crm_ret);

					var string = "";
					string += "\nagency: " + res.agencyId
					string += "\nproperties: " + res.selected
					string += "\nfile: " + res.file
					string += "\nPalace Reply: " + res.err

					$("#apiResponse").val("sucessfully uploaded: "+ string);

				});	
			}

		})
	})
</script>