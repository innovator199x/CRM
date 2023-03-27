<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
    $bc_items = array(
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


						<div class="col-md-3">
							<label>Agency</label>
							<select name="agency_filter" class="form-control">
								<option value="">--- Select ---</option>	
								<?php
								foreach( $agency_filter->result() as $agency ){ ?>
									<option value="<?php echo $agency->agency_id; ?>" <?php echo (  $agency->agency_id == $this->input->get_post('agency_filter') )?'selected="selected"':null; ?>>
										<?php echo $agency->agency_name; ?>
									</option>	
								<?php					
								}
								?>
							</select>
						</div>	
						
						<div class="col-md-3">
							<label>Address</label>
							<input type="text" name="search_p_address" class="form-control" placeholder="Text" value="<?php echo ($this->input->get_post('search_p_address')) ? $this->input->get_post('search_p_address') :'' ?>">
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
   

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
            
				<table class="table table-hover main-table linked_prop_tbl">

					<thead>
						<tr>            
							<th>Property</th> 
							<th>Linked</th>
                            <th>API Property ID</th> 
                            <th>Agency</th>  
                            <th>Active</th>                      				
						</tr>
					</thead>

					<tbody>
					<?php                    
					$i = 1;
					$apiConnectedIds = array('pme_prop_id', 'palace_prop_id', 'ourtradie_prop_id', 'pt_prop_id');	
					foreach( $list->result() as $row ){ 
                    $crm_full_address = "{$row->p_address_1} {$row->p_address_2}, {$row->p_address_3} {$row->p_state} {$row->p_postcode}";
                	$countApiConnected = 0;
                	$linkedApi = "";
                	$linkId = "";
                    foreach ($apiConnectedIds as $ids) {
                    	if ($ids == "pme_prop_id") {
                    		if (!is_null($row->$ids) && !empty($row->$ids)) {
                    			$linkedApi = "PMe";
                    			$countApiConnected++;
                    			$linkId = $row->$ids;
                    		}
                    	}else if ($ids == "palace_prop_id") {
                    		if (!is_null($row->$ids) && !empty($row->$ids)) {
                    			$linkedApi = "Palace";
                    			$countApiConnected++;
                    			$linkId = $row->$ids;
                    		}
                    	}else if ($ids == "ourtradie_prop_id") {
                    		if (!is_null($row->$ids) && !empty($row->$ids)) {
                    			$linkedApi = "OurTradie";
                    			$countApiConnected++;
                    			$linkId = $row->$ids;
                    		}
                    	}else if ($ids == "pt_prop_id") {
                    		if (!is_null($row->$ids) && !empty($row->$ids)) {
                    			$linkedApi = "PropertyTree";
                    			$countApiConnected++;
                    			$linkId = $row->$ids;
                    		}
                    	}
    				}				
                    ?>
						<tr>   
							<td>
                                <a href='<?php echo $this->config->item('crm_link') ?>/view_property_details.php?id=<?php echo $row->property_id; ?>' target='_blank'>
                                    <?php echo $crm_full_address; ?>
                                </a>
							</td>
							<td>
								<?php 
									if ($countApiConnected == 0) {
										echo '<span class="text-red">No</span>';
									}else if ($countApiConnected == 1) {
										echo '<span class="text-green">'.$linkedApi.'</span>';
									}else if ($countApiConnected == 2) {
										echo '<span class="text-red">Error</span>';
									}
								?>
							</td>                                                                     
                            <td>
								<?php 
									if ($countApiConnected == 0) {
										echo '';
									}else if ($countApiConnected == 1) {
										echo $linkId;
									}else if ($countApiConnected == 2) {
										echo 'Too many API links';
									}
								?>
							</td>         
                            <td>
								<a href="/agency/view_agency_details/<?php echo $row->agency_id; ?>" target="_blank">
                                    <?php echo $row->agency_name; ?>
                                </a>
                            </td>
                            <td>
								<?php echo ( $row->deleted == 1 )?'<span class="text-red">No</span>':'<span class="text-green">Yes</span>' ?>
							</td>
						</tr>
					<?php
                    $i++;
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
	<p>
		This page shows Properties that need some attention to remove them from Bulk Patch Page
	</p>
	<pre><code><?php echo $last_query; ?></code></pre>

</div>
<!-- Fancybox END -->



<style>
.fancybox-content {
    width: auto;
}
</style>
<script>
jQuery(document).ready(function(){
	

});
</script>

