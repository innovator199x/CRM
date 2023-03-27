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

	<style>
	.separator {
		margin: 0 5px;
	}
	.bold_it{
		font-weight: bold;
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
				<div class="col-md-8 columns">
					<div class="row">	

                        <div class="col-md-4">
							<label for="agency_select">Agency</label>
							<select id="agency_filter" name="agency_filter"  class="form-control field_g2">
                                <option value="">ALL</option>
                                <?php								
                                foreach( $agency_sql->result() as $agency_row ){ ?>
                                    <option value="<?php echo $agency_row->agency_id; ?>" <?php echo ( $agency_row->agency_id == $this->input->get_post('agency_filter') )?'selected="selected"':null; ?>>
                                        <?php echo $agency_row->agency_name; ?>
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
			</div>
			</form>
		</div>
	</header>

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
            
				<table id="jtable" class="table table-hover main-table table-striped">

					<thead>
						<tr>    
                            <th>Address</th> 
							<th>Deactivated Date</th>                                        						                           
						</tr>
					</thead>

					<tbody>
					<?php   
					if( $this->input->get_post('agency_filter') > 0 ){		
						if( count($pme_arch_prop_dec) > 0 ){

							foreach ( $pme_arch_prop_dec as $key => $pme_row ){                                                 
								?>
								<tr>
									<td><?php echo $pme_row->AddressText; ?></td> 
									<td><?php echo date('d/m/Y',strtotime($pme_row->ArchivedOn)); ?></td>                           									                            
								</tr>
							<?php                    
							}  	

						}else{?>
							<tr><td colspan="2">Empty</td></tr>
						<?php							
						}		               						
					}else{ ?>
						<tr><td colspan="2">Please select agency to filter</td></tr>
					<?php
					}					                                                    
					?>
					</tbody>

				</table>		

			</div>

		</div>
	</section>

</div>


<!-- Fancybox START -->

<!-- ABOUT TEXT -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>lorem impsum</p>

</div>

<link rel="stylesheet" href="/inc/css/lib/datatables-net/datatables.min.css">
<link rel="stylesheet" href="/inc/css/separate/vendor/datatables-net.min.css">
<script src="/inc/js/lib/datatables-net/datatables.min.js"></script>
<link type="text/css" href="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.11/css/dataTables.checkboxes.css" rel="stylesheet" />
<script type="text/javascript" src="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.11/js/dataTables.checkboxes.min.js"></script>

<script>
jQuery(document).ready(function(){

    jQuery('#jtable').DataTable({
        
		'pageLength': 50,
		'lengthChange': true,
		"order": [[ 0, 'asc' ]]

	});

});
</script>

