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
			'link' => "/reports/dirty_address"
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
		echo form_open('/reports/dirty_address',$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-md-8 columns">
					<div class="row">
					<!--
						<div class="col-md-3">
							<label for="agency_select">Agency</label>
							<select name="agency_filter" id="agency_filter" class="form-control agency_filter">
								<option value="">ALL</option>							
							</select>
						</div>
                        -->

						<div class="col-md-3">
							<label for="phrase_select">Phrase</label>
							<input type="text" name="search" class="form-control" placeholder="Text" value="<?php echo $this->input->get_post('search'); ?>" />
						</div>

						<div class="col-md-2 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<button type="submit" class="btn btn-inline">Search</button>
						</div>
						
					</div>

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
                            <th>ID</th>
							<th>Full Address</th>
							<th>No.</th>
							<th>Street</th>
							<th>Suburb</th>
							<th><?php echo $this->gherxlib->getDynamicState($this->config->item('country')) ?></th>
							<th>Postcode</th>
							<th>Agency</th>
							<th>Status</th>
							<th>&nbsp;</th>
						</tr>
					</thead>

					<tbody>
						<?php 
						$cnt = 1;
						if($lists->num_rows()>0){
                            foreach($lists->result_array() as $row){


							if($row['postcode'] == "" || $row['postcode']===NULL){
								$post_col_bg = "redRowBg";
							}else{
								NULL;
							}
							
							if( strtoupper($row['address_3']) == $row['address_3'] ){
								$suburb_col_bg = "redRowBg";
							}else if($row['address_3']=="" || $row['address_3'] == NULL){ //empty address
								$suburb_col_bg = "redRowBg";
							}else if( strpos($row['address_3'],',') ){ // comma
								$suburb_col_bg = "redRowBg";
							}else if($this->config->item('country')==2 && $row['address_3']=="Auckland"){ //NZ > catch auckland in suburb/address_3
									$suburb_col_bg = "redRowBg auckland";
							}else{
								NULL;
							}

							//state
							if($row['state']== "" || $row['state']===NULL){
								$state_col_bg = "redRowBg";
							}else if($this->config->item('country')==2 && strtoupper($row['state'])==$row['state']){
								$state_col_bg = "redRowBg nz_uppercase_state";
							}else if(

								$this->config->item('country')==2 && 
								(
									$row['state']!='Auckland' || 
									$row['state']!='Bay of Plenty' || 
									$row['state']!='Canterbury' || 
									$row['state']!='Northland' || 
									$row['state']!='Otago' || 
									$row['state']!='Waikato' || 
									$row['state']!='Whangarei' || 
									$row['state']!='Manawatu-Wanganui' || 
									$row['state']!='Southland' || 
									$row['state']!='Wellington' || 
									$row['state']!='Gisborne' || 
									$row['state']!='Taranaki' || 
									$row['state']!="Hawke's Bay" || 
									$row['state']!="Hawke’s Bay" ||
									$row['state'] != "Nelson" ||
									$row['state'] != "Manawatu" ||
									$row['state'] != "Tasman" ||
									$row['state'] != "Nelson"
								) 

							){
								$state_col_bg = "redRowBg";
							}else{
								NULL;
							}

							// postcode is less that 4
							if( strlen($row['postcode']) < 4  ){
								$post_col_bg = "redRowBg";
							}else{
								NULL;
							}


                        ?>
                                <tr data-cnt="<?php echo $cnt; ?>">
                                    <td><?php echo $this->gherxlib->crmlink('vpd',$row['property_id'],$row['property_id']) ?></td>
									
									<td>
										<?php echo "{$row['address_1']} {$row['address_2']}, {$row['address_3']} {$row['state']} {$row['postcode']}" ?>
									</td>
									
									<td class="<?php echo ($row['address_1'] == '' || (strpos($row['address_1'],'-') == true && strpos($row['address_1'],'/') == true  ) ) ? 'redRowBg' : 'ew' ?>">
										<?php echo $row['address_1']  ?>

									</td>

                                    <td class="<?php echo ( strtoupper($row['address_2']) == $row['address_2'] || strpos($row['address_2'],'-') !== false || strpos($row['address_2'],',') !== false ) ? 'redRowBg' : 'ew' ?>">
										<?php echo $row['address_2']  ?>
									</td>

									<td class="<?php echo $suburb_col_bg; ?>"><?php echo $row['address_3']  ?></td>
                                    <td class="<?php echo $state_col_bg; ?>" ><?php echo $row['state']  ?></td>
									<td class="<?php echo $post_col_bg; ?>"><?php echo $row['postcode']  ?></td>
                                    <td><?php echo $this->gherxlib->crmlink('vad',$row['a_id'],$row['agency_name'],'',$row['priority'])  ?></td>
									<td><?php echo ($row['deleted'] == 1) ? "<span class='text-red'>Inactive</span>" : "<span class='text-green'>Active</span>"  ?></td>
									<td>
										<div class="checkbox">
											<input class="chk_job" name="chk_job[]" type="checkbox" id="check-<?php echo $row["property_id"] ?>" data-prop-id="<?php echo $row["property_id"]; ?>" value="<?php echo $row["property_id"]; ?>">
											<label for="check-<?php echo $row["property_id"] ?>">&nbsp;</label>
										</div>
									</td>
                                </tr>
						<?php
						$cnt++;
							}
						}else{
							echo "<tr><td colspan='10'>There are no dirty addresses to be found</td></tr>";
						}
                        ?>


                          
					</tbody>

				</table>
				<div id="mbm_box" class="text-right"><button class="btn" id="btn_ignore_dirty_address">Ignore Dirty Address</button></div>
			</div>

			<nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
			<div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>

		</div>
	</section>

</div>


<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
	This page finds addresses that are not clean and need to run through the google address bar to normalize them
	</p>

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

	jQuery(document).ready(function(){

		//check sing checkbox toggle tweak
		$('.chk_job').on('change',function(){
			var obj = $(this);
			var isLength = $('.chk_job:checked').length;
			var divbutton = $('#mbm_box');
			if(isLength>0){
				divbutton.show();
			}else{
				divbutton.hide();
			}
		})

		$('#btn_ignore_dirty_address').click(function(){
			var prop_id = new Array();
			jQuery(".chk_job:checked").each(function(){
				prop_id.push(jQuery(this).val());
			});

			$('#load-screen').show(); //show loader
			
			jQuery.ajax({
				type: "POST",
				url: "/reports/ignore_dirty_address",
				data: { 
					prop_id: prop_id
				}
			}).done(function( ret ){
				$('#load-screen').hide(); //hide loader
				swal({
					title:"Success!",
					text: "Update success",
					type: "success",
					showCancelButton: false,
					confirmButtonText: "OK",
					closeOnConfirm: false,
					showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                	timer: <?php echo $this->config->item('timer') ?>

				});
				setTimeout(function(){ window.location='/reports/dirty_address'; }, <?php echo $this->config->item('timer') ?>);	
			});	

		})

	});

</script>