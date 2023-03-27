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
			'link' => "/reports/api_unlinked_properties"
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
		echo form_open('/reports/api_unlinked_properties',$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-md-8 columns">
					<div class="row">

						<div class="col-md-3">
							<label>Agency</label>
							<select id="agency_filter" name="agency_filter" class="form-control" required>
								<option value="">---</option>
                                <?php														
                                foreach( $agency_filter_sql->result() as $agency_row ){ ?>
                                    <option value="<?php echo $agency_row->agency_id; ?>" <?php echo ( $agency_row->agency_id == $this->input->get_post('agency_filter') )?'selected':null; ?>><?php echo $agency_row->agency_name; ?></option>
                                <?php
                                }																
                                ?> 
							</select>
						</div>

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
                            <th>Address</th>
							<th>Agency</th>
							<th>API</th>
							<th>Action</th>
						</tr>
					</thead>

					<tbody>

                    <?php
					if( $list!==false ){
						$i = 1;	
						foreach( $list->result_array() as $row ){ 
							
							if( $row['api_id']==1 ){ ##PropertyMe
								$api_detail_url = "/property_me/property/{$row['property_id']}/{$row['agency_id']}";
							}elseif( $row['api_id']==2 ){ ##Tapi
								$api_detail_url = "#";
							}elseif( $row['api_id']==3 ){ ##Propety Tree
								$api_detail_url = "/property_tree/connection_details/{$row['property_id']}";
							}elseif( $row['api_id']==4 ){ ##Palace
								$api_detail_url = "/palace/property/{$row['property_id']}/{$row['agency_id']}";
							}elseif( $row['api_id']==5 ){ ##Console
								$api_detail_url = "/console/connection_details/{$row['property_id']}";
							}elseif( $row['api_id']==6 ){ ##ourtradie
								$api_detail_url = "/ourtradie/property/{$row['property_id']}/{$row['agency_id']}";
							}

							$crm_full_address = "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']} {$row['p_state']} {$row['postcode']}";
						?>
							
							<tr>
								<td><?php 
								//echo $this->gherxlib->crmLink('vpd',$row['property_id'], "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']} {$row['p_state']} {$row['postcode']}" ) 
								echo "<a target='_blank' href='{$api_detail_url}'>{$crm_full_address}</a>"
								?></td>
								<td><?php echo $row['agency_name'] ?></td>
								<td>
									<?php 
									
									echo $row['api_name'];
									
									?>
								</td>
								<td>
									<button type="button" class="btn btn-primary verify_nlm_btn crm_note_btn<?php echo $i; ?>">PNV</button>
									<input type="hidden" class='crm_prop_id' value="<?php echo $row['property_id']; ?>" />
									<input type="hidden" class='crm_full_address' value="<?php echo $crm_full_address; ?>" />
									<input type="hidden" class='agency_id' value="<?php echo $row['agency_id']; ?>" />
								</td>
							</tr>
							
						<?php 
						$i++;
					}
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
	<pre>
        <?php echo $last_query; ?>
    </pre>
</div>
<!-- Fancybox END -->

<script>
jQuery(document).ready(function(){

	// verify NLM
	jQuery(".verify_nlm_btn").click(function(){
		
		var obj = jQuery(this);
		var parent_tr = obj.parents("tr:first");

		var property_source = 1; // crm
		var property_id = parent_tr.find(".crm_prop_id").val();
		var property_address = parent_tr.find(".crm_full_address").val();
		var agency_id =  parent_tr.find(".agency_id").val();	
		var note = "Contact agency to PNV";
		
		jQuery('#load-screen').show(); 
		jQuery.ajax({
			url: "/property_me/bulk_connect_save_note",
			type: 'POST',
			data: { 
				'pnv_id': '',
				'property_source': property_source,
				'property_id': property_id,
				'property_address': property_address,
				'agency_id': agency_id,
				'note': note
			}
		}).done(function( ret ){
			
			jQuery('#load-screen').hide();
			parent_tr.remove();
			
		});					

	});

});
</script>