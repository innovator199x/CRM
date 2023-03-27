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
            'link' =>  $uri
        )
    );
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	<?php 
	if( validation_errors() ){ ?>
		<div class="alert alert-danger">
		<?php echo validation_errors(); ?>
		</div>
	<?php
	}	
	?>

	<style>
	.separator {
		margin: 0 5px;
	}
	.bold_it{
		font-weight: bold;
	}
	.date_div {
		width: auto;
		margin-right: 13px;
	}
	.txt_hid{
		display: none;
	}
	.txt_lbl{
		cursor: pointer;
	}
	</style>
    
	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">
			<?php
		$form_attr = array(
			'id' => 'jform'
		);
		echo form_open($uri,$form_attr);
		?>
			<div class="for-groupss row">

				<div class="col-md-10 columns">
					<div class="row">	

                        <div class="col-md-2">
							<label>Agency</label>
							<select id="agency_filter" name="agency_filter"  class="form-control">
                                <option value="">---</option>
                                <?php   	 								                            					                            
                                foreach( $agency_filter_sql->result() as $agency_filter_row ){ ?>
                                    <option value="<?php echo $agency_filter_row->agency_id; ?>" <?php echo ( $agency_filter_row->agency_id == $this->input->get_post('agency_filter') )?'selected="selected"':null; ?>>
                                        <?php echo $agency_filter_row->agency_name; ?>
                                    </option>
                                <?php
                                }  									                                						                           
                                ?>
							</select>							
						</div>

						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<input type="submit" name="search_submit" value="Search" class="btn">
						</div>
						
					</div>

				</div>

				<!-- DL ICONS START -->
                <?php 
                $date = ($this->input->get_post('date')!="")?date('Y-m-d',$this->input->get_post('date')):NULL;
                ?>
				<!--
			    <div class="col-lg-2 col-md-12 columns">
                    <section class="proj-page-section float-right">
                        <div class="proj-page-attach">
                            <i class="fa fa-file-excel-o"></i>
                            <p class="name"><?php echo $title; ?></p>
                            <p>
								<a href="<?php echo $export_link; ?>" target="blank">
									Export
								</a>
                            </p>
                        </div>
                    </section>
				</div>
				-->
				<!-- DL ICONS END -->


			</div>
			</form>
		</div>
	</header>
	

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
            
				<table class="table table-hover main-table table-striped">

					<thead>
						<tr>    
                            <th>Address</th> 
							<th>Latitude</th>	
							<th>Longitude</th>						
                            <th>Agency</th>                             												                            						                           
						</tr>
					</thead>

					<tbody>
                    <?php       
					if( $list->num_rows() > 0 ) {                              
						foreach( $list->result() as $row ){                                       
					?>
						<tr>
							<td>
								<a href='<?php echo $this->config->item("crm_link"); ?>/view_property_details.php?id=<?php echo $row->property_id; ?>'>
									<?php echo "{$row->address_1} {$row->address_2}, {$row->address_3}"; ?>
								</a>
								<input type="hidden" class="property_id" value="<?php echo $row->property_id; ?>">
							</td>
							<td>
								<span class="txt_lbl"><?php echo ( $row->lat != '' )?$row->lat:'Empty - click to edit'; ?></span>
								<input type="text" class="form-control txt_hid" data-coordinate="lat" value="<?php echo $row->lat; ?>">
							</td>
							<td>
								<span class="txt_lbl"><?php echo ( $row->lng != '' )?$row->lng:'Empty - click to edit'; ?></span>
								<input type="text" class="form-control txt_hid" data-coordinate="lng" value="<?php echo $row->lng; ?>">
							</td>
							<td class="<?php echo ( $row->priority > 0 )?'j_bold':null; ?>">
								<a href='/agency/view_agency_details/<?php echo $row->agency_id; ?>'>
									<?php echo $row->agency_name." ".( ( $row->priority > 0 )?' ('.$row->abbreviation.')':null ); ?>
								</a>
							</td>															                            
						</tr>
					<?php                    
						}   
					}else{
						echo "<tr><td colspan='100%'>No results found</td></tr>";
					}                                                                       
					?>

					</tbody>

				</table>		

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
	<p>This page shows properties with either NULL or empty latitude or longitude. It also shows properties with coordinates outside the expected country.</p>
<pre>
<code><?php echo $query_string; ?></pre>
</div>

<script>
jQuery(document).ready(function(){


	jQuery(".txt_lbl").click(function(){

		var txt_lbl_dom = jQuery(this);
		var parent_td = txt_lbl_dom.parents("td:first");
		
		txt_lbl_dom.hide(); // label
		parent_td.find(".txt_hid").show(); // hidden textfield
		
	});

	/*
	jQuery(".txt_hid").blur(function(){

		var txt_hid_dom = jQuery(this);
		var parent_td = txt_hid_dom.parents("td:first");

		txt_hid_dom.hide(); // hidden textfield
		parent_td.find(".txt_lbl").show(); // label

	});
	*/

	jQuery(".txt_hid").change(function(){

		var txt_hid_dom = jQuery(this);
		var parent_tr = txt_hid_dom.parents("tr:first");

		var coordinate = txt_hid_dom.attr("data-coordinate");
		var coord_val = txt_hid_dom.val();
		var property_id = parent_tr.find(".property_id").val();

		if( property_id > 0 && ( coordinate != '' && coord_val != '' ) ){

			$('#load-screen').show();
			jQuery.ajax({
				type: "POST",
				url: "/sys/ajax_update_property_coordinates",
				data: { 	
					property_id: property_id,
					coordinate: coordinate,
					coord_val: coord_val
				}
			}).done(function( ret ){
										
				$('#load-screen').hide();	

				parent_td.find(".txt_lbl").text(coord_val); 
				parent_td.find(".txt_lbl").show(); 		                  			
			
			});

		}		

	});

})
</script>

