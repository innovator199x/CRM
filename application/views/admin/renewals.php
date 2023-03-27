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


						<div class="col-mdd-3">
							<label>From</label>							
							<input type="text" data-allow-input="true" name="from" class="form-control from flatpickr" value="<?php echo $from; ?>" />
						</div>
						
						<div class="col-mdd-3">
							<label>To</label>							
							<input type="text" data-allow-input="true" name="to" class="form-control to flatpickr" value="<?php echo $to; ?>" />
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
  
	<div class="row my-2">

		<div class="text-center col-md">

			Quick Links

			<?php
			for( $i=0; $i<=6; $i++ ){ 
				$prev_month_ts = strtotime("-{$i} month"); 
				$ql_start_date = date('01/m/Y',$prev_month_ts);
				$ql_end_date = date('t/m/Y',$prev_month_ts);
			?>
			
				<span class="separator">|</span> 
				<a href="/admin/renewals?from=<?php echo $ql_start_date; ?>&to=<?php echo $ql_end_date; ?>">
					<span class="<?php echo ( $ql_start_date == $from && $ql_end_date == $to )?'bold_it':null; ?>"><?php echo date("F",$prev_month_ts); ?></span>
				</a>
			<?php
			}
			?>


		</div>

	</div>	

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
            
				<table class="table table-hover main-table table-striped">

					<thead>
						<tr>    
                            <th>Date</th> 
                            <th>Jobs Created</th>   
							<th>Renewal Type</th>                                						                           
						</tr>
					</thead>

					<tbody>
					<?php
					foreach( $list->result() as $row ){ ?>
						<tr>
                            <td><?php echo date("d/m/Y",strtotime($row->date)); ?></td>
                            <td><?php echo $row->num_jobs_created; ?></td>	
							<td><?php echo ucfirst($row->rt_name); ?></td>									                            
						</tr>
					<?php                    
                    }                                       
					?>

					<tfoot>
						<tr>
							<td class="bold_it">TOTAL</td>
							<td><?php echo $job_created_count; ?></td>
							<td></td>
						</tr>
					</tfoot>

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
    Displays the amount of Yearly Maintenance Jobs created by the cron for the given month
	</p>

</div>

