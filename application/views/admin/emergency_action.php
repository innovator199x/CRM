
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
        'link' => $uri
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);

?>

	

	<section>
		<div class="body-typical-body">	

                <h4>CRON list</h4>

                <div>
                    <div class="checkbox-toggle float-right">                
                        <input type="checkbox" id="all_cron-chk-toggle" <?php echo ( $disable_all_crons == 1 )?'checked':null; ?> />
                        <label for="all_cron-chk-toggle">Disable all CRON</label>                    
                    </div>

                    <div class="checkbox-toggle float-left">               
                        <input type="checkbox" id="agency_portal-chk-toggle" <?php echo ( $agency_portal_mm == 1 )?'checked':null; ?> />
                        <label for="agency_portal-chk-toggle">Agency Portal - Maintenance Mode</label>                    
                    </div>
                </div>

                <div class="table-responsive">
				<table class="table table-hover main-table">
					<thead>
						<tr>	                                    
							<th>Cron Name</th>
							<th>Description</th>
                            <th>Active</th>
						</tr>
					</thead>

					<tbody>
                        <?php
                        
                        if($cron_sql->num_rows() > 0){
                            foreach($cron_sql->result() as $index => $cron_row){
                        ?>
                            <tr>                                                         
                                <td><?php echo $cron_row->type_name; ?></td>
                                <td><?php echo $cron_row->description; ?></td>
                                <td class="action_col">
                                                                        
                                    
                                    <div class="checkbox-toggle">                
                                        <input 
                                            type="checkbox" 
                                            id="indiv_cron-chk-toggle-<?php echo $index; ?>" 
                                            class="indiv_cron-chk-toggle"                                            
                                            <?php echo ( $cron_row->active_cron == 1 && $disable_all_crons == 0 )?'checked':null; ?>                                             
                                        />
                                        <label for="indiv_cron-chk-toggle-<?php echo $index; ?>"></label>                    
                                    </div>    
                                    

                                    <?php  //echo ( $cron_row->active_cron == 1 && $disable_all_crons == 0 )?'<span class="txt_green">Active</span>':'<span class="txt_red">Inactive</span>' ?>

                                    <input type="hidden" class="cron_type_id" value="<?php echo $cron_row->cron_type_id; ?>" />
                                    <input type="hidden" class="cron_type_name" value="<?php echo $cron_row->type_name; ?>" />

                                </td>
                             </tr>
                        <?php   
                            }
                        }else{ ?>
                            <tr><td colspan='4'>No Data</td></tr>
                        <?php    
                        }                        
                        ?>
                 
					</tbody>

				</table>
			</div>           

            <input type="hidden" name="mode" id="mode" value="<?php echo $m['mode']; ?>" />
               
		
          
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

    // agency portal - maintenance modeajax_agency_maintenance_mode_toggle
    jQuery("#agency_portal-chk-toggle").change(function(){

        var node = jQuery(this);
        var agency_portal_mm = ( node.prop("checked") == true )?1:0;        

        if( agency_portal_mm == 1 ){
            var activated_txt = 'ACTIVATE';
            var activated_txt2 = 'activated';
        }else{
            var activated_txt = 'DEACTIVATE';
            var activated_txt2 = 'deactivated';
        }

        swal({
            title: "Warning!",
            text: "This will "+activated_txt+" agency portal maintenance mode? Do you want to continue?",
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
                
                $('#load-screen').show();
                jQuery.ajax({
                    type: "POST",
                    url: "/admin/ajax_agency_maintenance_mode_toggle",
                    data: { 	
                        agency_portal_mm: agency_portal_mm
                    }
                }).done(function( ret ){
                    
                    $('#load-screen').hide();            
                    swal({
                        title: "Success!",
                        text: "Agency Maintenance Mode "+activated_txt,
                        type: "success",
                        confirmButtonClass: "btn-success",
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    }); 
                    setTimeout(function(){ window.location='<?php echo $uri; ?>'; }, <?php echo $this->config->item('timer') ?>);                                       

                });						

            }else{
                
                if( agency_portal_mm == 1 ){
                    node.prop("checked",false);
                }else{
                    node.prop("checked",true);
                }
                

            }

        });										                            

    });


    // Disable ALL cron toggle    
    jQuery("#all_cron-chk-toggle").change(function(){

        var node = jQuery(this);
        var disable_all_crons = ( node.prop("checked") == true )?1:0;

        if( disable_all_crons == 1 ){
            var disable_txt = 'DISABLE';
            var disable_txt2 = 'disabled';
        }else{
            var disable_txt = 'ENABLE';
            var disable_txt2 = 'enabled';
        }
       

        swal({
            title: "Warning!",
            text: "This will "+disable_txt+" ALL CRONS? Do you want to continue?",
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
                
                $('#load-screen').show();
                jQuery.ajax({
                    type: "POST",
                    url: "/admin/ajax_disable_all_crons_toggle",
                    data: { 	
                        disable_all_crons: disable_all_crons
                    }
                }).done(function( ret ){
                    
                    
                    $('#load-screen').hide();            
                    swal({
                        title: "Success!",
                        text: "All Crons are now "+disable_txt2,
                        type: "success",
                        confirmButtonClass: "btn-success",
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });  
                    setTimeout(function(){ window.location='<?php echo $uri; ?>'; }, <?php echo $this->config->item('timer') ?>);
                                                         

                });						

            }else{
                
                if( disable_all_crons == 1 ){
                    node.prop("checked",false);
                }else{
                    node.prop("checked",true);
                }
                

            }

        });										                            

    });


    // Disable individual cron toggle    
    jQuery(".indiv_cron-chk-toggle").change(function(){

        var node = jQuery(this);
        var parent_row = node.parents("tr:first");

        var cron_status = ( node.prop("checked") == true )?1:0;
        var cron_type_id = parent_row.find(".cron_type_id").val();
        var cron_type_name = parent_row.find(".cron_type_name").val();

        if( cron_status == 1 ){ // enabled
            var disable_txt = 'ENABLE';
            var disable_txt2 = 'enabled';
        }else{ // disabled            
            var disable_txt = 'DISABLE';
            var disable_txt2 = 'disabled';
        }

        swal({
            title: "Warning!",
            text: "This will "+disable_txt+" "+cron_type_name+" CRON? Do you want to continue?",
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
                                
                $('#load-screen').show();
                jQuery.ajax({
                    type: "POST",
                    url: "/admin/ajax_disable_indiv_crons_toggle",
                    data: { 	
                        cron_type_id: cron_type_id,
                        cron_status: cron_status
                    }
                }).done(function( ret ){
                                   
                    window.location='<?php echo $uri; ?>';                                                        

                });	                				

            }else{
                
                if( cron_status == 1 ){
                    node.prop("checked",false);
                }else{
                    node.prop("checked",true);
                }
                

            }

        });										                            

    });


});
</script>
