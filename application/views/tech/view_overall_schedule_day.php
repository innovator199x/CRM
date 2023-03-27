<style>
	.col-mdd-3{
		max-width:12.5%;	
	}
    .top_date_heading{
        margin-top:20px;
    }
</style>
<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/tech/view_overall_schedule_day?date={$this->input->get_post('date')}"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">
		
			<div class="for-groupss row">
				<div class="col-md-10 columns">
                    <div class="top_date_heading">

						<?php if($job_count!=0){ ?>
						Total Jobs for <?php echo $datebooked; ?> is: <?php echo $job_count; ?>
						<?php }else{ ?>
							<span class="text-red">No Bookings for Today!</span>
						<?php } ?>
					</div>
				</div>

                 <!-- DL ICONS START -->
				 <?php 
                $date = ($this->input->get_post('date')!="")?date('Y-m-d',$this->input->get_post('date')):NULL;
                 ?>
			    <div class="col-lg-2 col-md-12 columns">
                    <section class="proj-page-section float-right">
                        <div class="proj-page-attach" style="margin:0;">
                            <i class="fa fa-file-excel-o"></i>
                            <p class="name"><?php echo $title; ?></p>
                            <p>
                                <a href="/tech/view_overall_schedule_day?export=1&date=<?php echo $this->input->get_post('date') ?> ">
                                    Export
                                </a>
                            </p>
                        </div>
                    </section>
				</div>
				<!-- DL ICONS END -->

			</div>
		</div>
	</header>

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<table class="table table-hover main-table">
					<thead>
						<tr>
							<th>#</th>
							<th>Service</th>
							<th>Status</th>
							<th>Address</th>
							<th>Key</th>
							<th>Notes</th>
							<th>Time</th>
							<th>Name</th>
						</tr>
					</thead>

					<tbody>
						
						<?php 
							$counter = 1;
							foreach($job_query->result_array() as $row){
						?>
						<tr>
							<td><?php echo $counter; ?></td>
							<td><?php echo $row['type']; ?></td>
							<td><?php echo $this->gherxlib->crmlink('vjd',$row['id'],$row['status']) ?></td>
							<td>
								<?php
								$full_address = "{$row['address_1']} {$row['address_2']} {$row['address_3']}";
								echo $this->gherxlib->crmlink('vpd',$row['property_id'],$full_address);
								?>
							</td>
							<td><?php echo $row['key_number']; ?></td>
							<td><?php echo $row['tech_notes']; ?></td>
							<td><?php echo $row['time_of_day']; ?></td>
							<td><?php echo $row['FirstName']; ?></td>
							</tr>
						<?php
							$counter++;
							}
						?>
						
					</tbody>

				</table>
			</div>


		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
	This page shows all tech jobs that are booked
	</p>

</div>
<!-- Fancybox END -->

<script type="text/javascript">


jQuery(document).ready(function(){





}) //doc ready end

</script>