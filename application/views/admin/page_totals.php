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

    <!--
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
							<label>Agency</label>
							<select name="page_display" class="form-control">
                                <option value="">--- Select ---</option>																					
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
    -->

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
            
				<table class="table table-hover main-table table-striped">

					<thead>
						<tr>    
                            <th>Page</th>                             
                            <th>Total</th>                                  						                           
						</tr>
					</thead>

					<tbody>
					<?php
					foreach( $page_total_sql->result() as $page_total_row ){ ?>
						<tr>
                            <td><?php echo $page_total_row->page; ?></td>		                            
                            <td><?php echo $page_total_row->total; ?></td>													                            
						</tr>
					<?php                    
                    }                                       
					?>
					</tbody>

				</table>		

            </div>
            
            <button type="button" id="update_page_total_btn" class="btn">Update Page Total Now</button>           

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

<script>
jQuery(document).ready(function(){

    jQuery("#update_page_total_btn").click(function(){

        swal({
            title: "Warning!",
            text: "This will update the current page bubble count? Do you want to continue?",
            type: "warning",						
            showCancelButton: true,
            confirmButtonClass: "btn-success",
            confirmButtonText: "Yes, Continue",
            cancelButtonClass: "btn-danger",
            cancelButtonText: "No, Cancel!",
            closeOnConfirm: true,
            showLoaderOnConfirm: true,
            closeOnCancel: true
        },
        function(isConfirm) {

            if (isConfirm) {							  
                
                window.location='/admin/update_page_totals';				

            }

        });	


    });    
    
});
</script>

