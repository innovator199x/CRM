
<style>
    .col-mdd-3{
        max-width:15.5%;
    }
    .action_a, .action_div {
        color: #adb7be!important;
    }
</style>

<div class="box-typical box-typical-padding">

    <?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => "/admin/agency_site_maintenance_mode"
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);

?>

	

	<section>
		<div class="body-typical-body">
			<div class="table-responsive text-center" style="min-height:300px;padding-top:30px;">

            	<?php
                if($m['mode']==1){ ?>
                    <p><img src="/images/under-maintenance.png" /></p>
                <?php	
                }else{ ?>
                    <p>This page allows you to block access to the Agency Portal whilst doing maintenance.</p>
                <?php	
                }
                ?>

                <div class="checkbox-toggle" style="margin-top:30px;margin-bottom:30px;">
               
                    <input type="checkbox" id="check-toggle-1">
                    <label for="check-toggle-1" style="font-size:18px;">Maintenance Mode</label>
                    
                </div>

		        <input type="hidden" name="mode" id="mode" value="<?php echo $m['mode']; ?>" />
               
			</div>
          
		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
    This page allows you to block access to the Agency Portal whilst doing maintenance.
	</p>

</div>
<!-- Fancybox END -->


<script type="text/javascript">

  jQuery(document).ready(function(){

      <?php if ($this->session->flashdata('status') && $this->session->flashdata('status') == 'success') { ?>
            swal({
                title: "Success!",
                text: "<?php echo $this->session->flashdata('success_msg') ?>",
                type: "success",
                confirmButtonClass: "btn-success",
                showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                timer: <?php echo $this->config->item('timer') ?>
            });
        <?php } else if ($this->session->flashdata('status') && $this->session->flashdata('status') == 'error') { ?>
                    swal({
                        title: "Error!",
                        text: "<?php echo $this->session->flashdata('error_msg') ?>",
                        type: "error",
                        confirmButtonClass: "btn-danger"
                    });
        <?php } ?>
        

        var isundermaitenance = $('#mode').val();
        if(isundermaitenance==1){
            $('#check-toggle-1').prop('checked',true);
        }else{
            $('#check-toggle-1').prop('checked',false);
        }

        $('#check-toggle-1').on('change',function(){
            var checked = $(this).is(':checked');
            var mode = jQuery("#mode").val();
            if(checked) {
                
                swal({
                    title: "Warning!",
                    text: "Are you sure you want to proceed?",
                    type: "warning",
                    showCancelButton: true,
                    cancelButtonText: "Cancel!",
                    confirmButtonClass: "btn-success",
                    cancelButtonClass: "btn-danger",
                    confirmButtonText: "Yes",                       
                    closeOnConfirm: false,
                },
                function(isConfirm) {
                    
                    if (!isConfirm) {
                        $('#check-toggle-1').prop( "checked", false );
                        return false;
                    }else{

                        $('#load-screen').show(); //show loader
                        jQuery.ajax({
                            type: "POST",
                            url: "/admin/ajax_switch_agency_site_maintenance_mode",
                            data: { 
                                mode: mode
                            }
                        }).done(function( ret ) {

                            $('#load-screen').hide(); //hide loader
                            swal({
                                title:"Success!",
                                text: "Update Successfull",
                                type: "success",
                                showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                                timer: <?php echo $this->config->item('timer') ?>
                            });	
                            
                            var full_url = window.location.href;
                            setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);	

                        });	

                    }

                });

            } else{
                
                swal({
                    title: "Warning!",
                    text: "Are you sure you want to proceed?",
                    type: "warning",
                    showCancelButton: true,
                    cancelButtonText: "Cancel!",
                    confirmButtonClass: "btn-warning",
                    confirmButtonText: "Yes",                       
                    closeOnConfirm: false,
                },
                function(isConfirm) {
                    
                    if (!isConfirm) {
                        $('#check-toggle-1').prop( "checked", true );
                        return false;
                    }else{
                        
                        $('#load-screen').show(); //show loader
                        jQuery.ajax({
                            type: "POST",
                            url: "/admin/ajax_switch_agency_site_maintenance_mode",
                            data: { 
                                mode: mode
                            }
                        }).done(function( ret ) {

                            $('#load-screen').hide(); //hide loader
                            swal({
                                title:"Success!",
                                text: "Update Successfull",
                                type: "success",
                                showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                                timer: <?php echo $this->config->item('timer') ?>
                            });	
                            
                            var full_url = window.location.href;
                            setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);	

                        });	

                    }

                });

            }
        });
       

  })



</script>
