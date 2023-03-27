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
	#nlm_btn{
		margin: 15px;	
		display: none;	
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
							<label for="state_select">Agency</label>
							<form method="POST" action="<?php echo base_url(); ?>reports/api_tenancy_info">
							<select id="agency_filter" name="agency_filter"  class="form-control field_g2">
                                <option value="">---</option>
                                <?php							
                                foreach( $agencies as $row ){ ?>
                                    <option value="<?php echo $row->agency_id; ?>" <?php echo ( $row->agency_id == $this->input->get_post('agency_filter') )?'selected="selected"':null; ?>>
                                        <?php echo $row->agency_name; ?>
                                    </option>
								<?php								
								}								
                                ?>
							</select>	
							</form>						
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
							<th>Property Address</th>
                            <th>Tenancy Start</th>														
							<th>Agreement Start</th>   
							<th>Agreement End</th> 
							<th>Tenancy End</th> 
							<th>Termination</th>
							<th>Break Lease</th>
						</tr>
					</thead>

					<tbody>
						<?php
							//print_r($tenancy);
						?>

						<?php foreach($tenancy as $info): ?>
						<tr>
							<?php
								if($info->ApiID == 1){
									$api = "property_me";
									$prop_id = $info->PropID;
									$agency_id = $info->AgencyID;
								}
							?>
							<td><a href="<?php echo base_url(); ?><?php echo $api; ?>/property/<?php echo $prop_id; ?>/<?php echo $agency_id; ?>"><?php echo $info->LotAddress; ?></a></td>
							<td><?php echo $info->TenancyStart; ?></td>
							<td><?php echo $info->AgreementStart; ?></td>
							<td><?php echo $info->AgreementEnd; ?></td>
							<td><?php echo $info->TenancyEnd; ?></td>
							<td><?php echo $info->Termination; ?></td>
							<td><?php echo $info->BreakLease; ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>

				</table>	
			
				<?php
				if( count($crm_prop_arr) > 0 ){ ?>
					<button type="button" id="nlm_btn" class="btn float-right">No Longer Managed</button>	
				<?php
				}
				?>							

            </div>


		</div>
	</section>

</div>


<!-- Fancybox START -->

<!-- ABOUT TEXT -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>This page shows api tenancy info from API.</p>
</div>

<link rel="stylesheet" href="/inc/css/lib/datatables-net/datatables.min.css">
<link rel="stylesheet" href="/inc/css/separate/vendor/datatables-net.min.css">
<script src="/inc/js/lib/datatables-net/datatables.min.js"></script>
<!--
<link type="text/css" href="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.11/css/dataTables.checkboxes.css" rel="stylesheet" />
<script type="text/javascript" src="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.11/js/dataTables.checkboxes.min.js"></script>
-->

<script>
jQuery(document).ready(function(){

	// datatable
    jQuery('#jtable').DataTable({
        
		'pageLength': 50,
		'lengthChange': true,
		"order": [[ 0, 'asc' ]],
		'columnDefs': [
			{
				'targets': [3],
				'orderable': false
			}
		],

	});

});
</script>

