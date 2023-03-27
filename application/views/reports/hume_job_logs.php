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
					

				</div>

				<!-- DL ICONS START --> 
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
                            <th>Property Address</th> 																	                            						                           
						</tr>
					</thead>

					<tbody>
                    <?php                                      
					foreach( $list as $row ){                                       
                    ?>
						<tr>
                            <td>
                                <a href='<?php echo $this->config->item("crm_link"); ?>/view_property_details.php?id=<?php echo $row->property_id; ?>'>
                                    <?php echo "{$row->address_1} {$row->address_2}, {$row->address_3}"; ?>
                                </a>
                            </td>														
						</tr>
					<?php                    
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
	<p>This page shows all properties that are not upgraded to the NEW QLD legislation</p>
	<pre>
	<code><?php echo $page_query; ?></code>
	</pre>

</div>

