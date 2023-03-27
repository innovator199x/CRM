<style>
    .col-mdd-3{
        max-width:15.5%;
    }
	#region_dp_div {
		position: absolute;
		top: 65px;
	}
</style>


<div class="box-typical box-typical-padding">

	<?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => "/properties/add_main_region"
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);
?>


        </header>

	<section>
		<div class="body-typical-body">
			

		<?php echo form_open('/properties/add_main_region', 'id=jform') ?>
				<div class="row" style="margin-top:40px;">
					<div class="col-md-5 columns">

						<div class="row form-group">
								<div class="col-md-3 columns">	<label  for='region_name'><?php echo $this->gherxlib->getDynamicRegion($this->config->item('country')); ?> Name</label></div>
								<div class="col-md-9 columns">	<input class='form-control' type="text" name='region_name' id='region_name' /></div>
						</div>
						
						<div class="row form-group">
							<div class="col-md-3 columns">	
								<label for='state'><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></label>
							</div>
							<div class="col-md-9 columns">	
								<input class='form-control' type="text" name='state' id='state' />
							</div>
						</div>
						
						<div class="row edt-reg-btn form-group">
							<div class="col-md-3 columns">	<label class='addlabel' for='state'>&nbsp;</label>	</div>
							<div class="col-md-9 columns">
								<input type="submit" value="Submit" name="submit" class="btn">
							</div>
						</div>
					</div>
				</div>
			</form>
			

		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
	lorem...
	</p>

</div>
<!-- Fancybox END -->


<script>
jQuery(document).ready(function(){

	//success/error message sweel alert pop  start
	<?php if( $this->session->flashdata('status') &&  $this->session->flashdata('status') == 'success' ){?>
		swal({
			title: "Success!",
			text: "<?php echo $this->session->flashdata('success_msg') ?>",
			type: "success",
			confirmButtonClass: "btn-success",
			showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
            timer: <?php echo $this->config->item('timer') ?>
		});
	<?php }else if(  $this->session->flashdata('status') &&  $this->session->flashdata('status') == 'error'  ){ ?>
		swal({
			title: "Error!",
			text: "<?php echo $this->session->flashdata('error_msg') ?>",
			type: "error",
			confirmButtonClass: "btn-danger"
		});
	<?php } ?>
	//success/error message sweel alert pop  end


	//form validation
	jQuery("#jform").submit(function(){
		
		var region_name = jQuery("#region_name").val();
		var state = jQuery("#state").val();
		var error = "";
		var sumbitcount = 0;
		
		if( region_name == "" ){
			error += "Region Name is Required\n";
		}
		
		if( state == "" ){
			error += "State is Required\n";
		}
		
		if(error!=""){
			swal('',error,'error');
			return false;
		}

		if(sumbitcount==0){
			sumbitcount++;
			$('#jform').submit();
			return false;
		}else{
			swal('','Form submission is in progress.','error');
			return false;
		}

		
		
	});



});
</script>
