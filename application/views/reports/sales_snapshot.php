<link rel="stylesheet" href="/inc/css/lib/ladda-button/ladda-themeless.min.css">
<style>
	.col-mdd-3{
		max-width:15%;
	}
    .table_top_head h4{
        margin:0;
    }
    table.main-table{
        margin-bottom:30px;
    }
    .table_top_head{border-top:1px solid #fff;}
    .table_top_head .carret{
        position: absolute;
        right: 10px;
        top: 9px;
        font-size: 24px;
    }
    .ladda-button.disabled, .ladda-button:disabled{
        opacity: .65!important;
        background:#16b4fc!important;
        border:1px solid #16b4fc;
    }
    .ladda-button.disabled, .ladda-button:disabled{
        color:#fff;
    }
    button.sales_rep_cta{
        position: relative;
        width: 100%;
        text-align: left;
        padding:7px 10px;
    }
    .ladda-button[data-style="expand-right"] .ladda-spinner{
        right:25px;
    }
    .ladda-button .ladda-spinner{
        height:20px;
        width:20px;
    }
    .ctal_ad_edit_div{
        padding-top:17px;
        padding-left:10px;
    }
    .esr_block{
        margin-bottom:10px;
    }
</style>
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
			'link' => "/reports/sales_snapshot"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>


    <?php
            $export_link_params = "/reports/export_sales_snapshot";
    ?>



	<header class="box-typical-header">
        <div class="box-typical box-typical-paddingss" style="padding-bottom:10px;padding-right:10px;">
            <div class="row">
                <div class="col-md-9 columns">
                <div class="ctal_ad_edit_div">
                    <button id="btn_add_opportunity" href="#add_opportunity_fancybox" class="btn btn-danger fancybox_btn">Add Opportunity</button>
                    <!--<button id="btn_add_sales_rep" href="#add_sales_fancybox" class="btn fancybox_btn">Add Sales Rep</button>-->
                    <!--<button id="btn_edit_sales_rep" href="#edit_sales_fancybox" class="btn fancybox_btn">Edit Sales Rep</button>-->
                </div>
                </div>
                     <div class="col-md-3 columns">
                        <section class="proj-page-section float-right">
                            <div class="proj-page-attach">
                                <i class="fa fa-file-excel-o"></i>
                                <p class="name"><?php echo $title; ?></p>
                                <p>
                                    <a href="<?php echo $export_link_params ?>">
                                        Export
                                    </a>
                                </p>
                            </div>
                        </section>
                    </div>
            </div>
		</div>
	</header>

	<section>
		<div class="body-typical-body">

			<div class="table-responsive">

                <?php
                
                    foreach($snapshot_sales_rep_list->result() as $row_rep){
                ?>

                    <div class="table_top_head text-left" style="padding:0;">
                    <input type="hidden" class="ss_sales_rep_id" value="<?php echo $row_rep->sales_snapshot_sales_rep_id; ?>" />
                        <button data-id="<?php echo $row_rep->sales_snapshot_sales_rep_id ?>" type="submit" class="btn ladda-button sales_rep_cta" data-style="expand-right" style="position:relative;width:100%;text-align:left;">
                            <h4 class="ladda-label"><?php echo "{$row_rep->first_name} {$row_rep->last_name}" ?></h4><span class="fa fa-caret-right carret"></span>
                        </button>
                       
                    </div>
                    <div style="display:none;" class="ajax_shimpox"></div>

                 <?php
                    }
                ?>
                    


			</div>


		</div>
	</section>


    <!-- ADD OPPORTUNITY FANCYBOX -->
    <div style="display:none;" id="add_opportunity_fancybox">
    <?php
		$form_attr = array(
			'id' => 'snapshot_form'
		);
		echo form_open('/reports/add_opportunity',$form_attr);
		?>
                <h4>Add Opportunity</h4>

                <div class="form-group">
                    <label>Agency</label>
                    <select class="form-control snap_add_agency" name="snap_add_agency" id="snap_add_agency">
                        <option value="">----</option>
                        <?php 
                        foreach($agency_list->result() as $tt){
                            $agency_list_selected = ($tt->agency_id == $row->agency_id) ? 'selected="selected"' : NULL;
                            echo "<option {$agency_list_selected} value='{$tt->agency_id}'>{$tt->a_name} ({$tt->status})</option>";
                        }
                        ?>
                    </select>
                </div>

                 <div class="form-group">
                    <label>Properties</label>
                        <input type="text" class="form-control" name="snap_add_properties" id="snap_add_properties">
                </div>

                 <div class="form-group">
                    <label>Status</label>
                    <select class="form-control snap_status" name="snap_add_status" id="snap_add_status" >
                    <option value="">----</option>
                        <?php
                            foreach($sales_snapshot_status_list->result() as $sales_snapshot_status_list_row){
                            $sales_snapshot_status_list_selected = ($sales_snapshot_status_list_row->sales_snapshot_status_id == $row->ss_status_id) ? 'selected="selected"' : NULL;
                        ?>

                                <option <?php echo $sales_snapshot_status_list_selected; ?> value="<?php echo $sales_snapshot_status_list_row->sales_snapshot_status_id; ?>"><?php echo $sales_snapshot_status_list_row->name; ?></option>
                        <?php
                            }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">

                    <label>Sales Rep</label>
                        <select class="form-control snap_sales_rep" name="snap_add_sales_rep" id="snap_add_sales_rep">
                            <option value="">----</option>

                            <?php
                                foreach($all_sales_rep_staff->result() as $snapshot_reps_row){
                                $sales_reps_selected = ($sales_snapshot_sales_rep_id == $snapshot_reps_row->sales_snapshot_sales_rep_id) ? 'selected="selected"' : NULL;
                            ?>
                                <option <?php echo $sales_reps_selected ?>  value="<?php echo $snapshot_reps_row->sales_snapshot_sales_rep_id ?>"><?php echo "{$snapshot_reps_row->first_name} {$snapshot_reps_row->last_name}" ?></option>
                            <?php
                                }
                            ?>

                        </select>
                </div>
                
                <div class="form-group">
                    <label>Details</label>
                   <textarea name="snap_add_details" id="snap_add_details" class="form-control"></textarea>
                </div>
                <div class="form-group">
                        <div class="checkbox" style="margin:0;">
                            <input name="snap_insert_agency_log" type="checkbox" id="snap_insert_agency_log" value="1">
                            <label for="snap_insert_agency_log">Insert Agency log</label>
                        </div>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn" value="Submit">
                </div>
        </form>
    </div>
    <!-- ADD OPPORTUNITY FANCYBOX END -->


    <!-- ADD SALES REP FANCYBOX -->
    <!--
    <div style="display:none;" id="add_sales_fancybox">
        <h4>Add Sales Rep</h4>
         <?php
         /*
            $form_attr = array(
                'id' => 'snapshot_add_salesrep_form'
            );
            echo form_open('/reports/add_sales_snapshot_sales_rep',$form_attr);
            */
		?>
            <div class="form-group">
                <label>First Name</label>
                <input type="text" class="form-control" name="sales_rep_fname" id="sales_rep_fname" style="min-width:400px;">
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" class="form-control" name="sales_rep_lname" id="sales_rep_lname">
            </div>
            <div class="form-group">
                <input type="submit" class="btn" id="btn_add_sales_rep" name="btn_add_sales_rep" value="Submit">
            </div>

        </form>
    
    </div>
    -->
     <!-- ADD SALES REP FANCYBOX END -->





</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4>Sales Snapshot</h4>
	<p>This page shows an overview of what the sales team are working on and the likeliness that they will come on-board and when.</p>
    <pre>
        <code><?= $last_query; ?></code>
    </pre>
</div>
<!-- Fancybox END -->


<script src="/inc/js/lib/ladda-button/spin.min.js"></script>
<script src="/inc/js/lib/ladda-button/ladda.min.js"></script>	
<script type="text/javascript">

    jQuery(document).ready(function() {

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

    
        $(".fancybox_btn").fancybox({
            hideOnContentClick: false,
            hideOnOverlayClick: false
        });


        $('.sales_rep_cta').on('click',function(e){
            e.preventDefault();
            var l = Ladda.create(this);
            var obj = $(this);
            var id  = obj.attr('data-id')
            var target_div = obj.parents('.table_top_head').next('.ajax_shimpox');
            var isActive = obj.attr('data-active');
           

            obj.toggleClass('snap_active');
            
          
            if(obj.hasClass('snap_active')){
                obj.find('.carret').removeClass('fa-caret-right').addClass('fa-caret-down');
                target_div.slideDown();
            }else{
                obj.find('.carret').removeClass('fa-caret-down').addClass('fa-caret-right');
                target_div.slideUp();
            }
            
           
            if(isActive!=1){
                l.start();
                target_div.load('/reports/ajax_get_sales_snapshot', {
                    sales_snapshot_sales_rep_id: id
                    }, function(response, status, xhr) {
                        l.stop();
                        target_div.slideDown();
                        obj.attr('data-active',1);
                    }
                 );
            } 
           

        })


        //add opportunity
        $('#snapshot_form').submit(function(){

            var agency  = $(this).find('#snap_add_agency').val();
            var salesrep  = $(this).find('#snap_add_sales_rep').val();
            var error = "";
            var submitCount = 0;

           if(agency==""){
                error += "Agency must not be empty\n";
            }
            if(salesrep==""){
                error += "Sales Rep must not be empty\n";
            }
            
            if(error!=""){
                swal('',error,'error');
                return false;
            }

            if(submitCount==0){
                submitCount++;
                jQuery(this).submit();
                return false;
            }else{
                swal('','Submission in progress','error');
                return false;
            }


        })

        //add Sales Rep
        $('#snapshot_add_salesrep_form').submit(function(){

            var fname  = $(this).find('#sales_rep_fname').val();
            var lname  = $(this).find('#sales_rep_lname').val();
            var error = "";
            var submitCount = 0;

            if(fname==""){
                error += "First Name must not be empty\n";
            }
            if(lname==""){
                error += "Last Name must not be empty\n";
            }

            if(error!=""){
                swal('',error,'error');
                return false;
            }

            if(submitCount==0){
                submitCount++;
                jQuery(this).submit();
                return false;
            }else{
                swal('','Submission in progress','error');
                return false;
            }


        })

        // delete salesrep
        jQuery(".btn_del_snap_sales_rep").click(function(e){
            e.preventDefault();
            
            // get current salesrep with opportunity
            var current_salesrep = [];
            var i = 0;
            jQuery(".ss_sales_rep_id").each(function(){			
                current_salesrep[i] = parseInt(jQuery(this).val());
                i++;
            });
            
            // salesrep id
            var ss_sr_id = parseInt(jQuery(this).parents(".esr_del_block").find(".ss_sr_id").val());

            
            // prevent deleting salesrep with active opportunity
            if( jQuery.inArray( ss_sr_id, current_salesrep )>-1 ){
                swal('','Cannot Delete Salesrep with Active Opportunities','error')
                return false;
            }else{
                
                swal(
                    {
                        title: "",
                        text: "Are you sure you want to delete?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-success",
                        confirmButtonText: "Yes",
                        cancelButtonText: "No, Cancel!",
                        closeOnConfirm: false,
                        closeOnCancel: true,
                    },
                    function(isConfirm){
                        if(isConfirm){

							$('#load-screen').show(); //show loader
							//swal.close();

							jQuery.ajax({
                                type: "POST",
                                url: "<?php echo base_url('/reports/ajax_delete_snapshot_sales_rep') ?>",
                                dataType: 'json',
                                data: { 
                                    ss_sr_id: ss_sr_id,
                                    current_salesrep: current_salesrep
                            }
							}).done(function(res){
								if(res.status){
                                    
									$('#load-screen').hide(); //hide loader

									swal({
										title:"Success!",
										text: res.msg,
										type: "success",
										showCancelButton: false,
										confirmButtonText: "OK",
										closeOnConfirm: false,
									},function(isConfirm){
										if(isConfirm){ 
											swal.close();
											location.reload();
										}
									});

								}else{
									swal.close();
									location.reload();
								}

							});

                        }else{
                            return false;
                        }
                        
                    }
            	);	

            }
            
        });



        




    });


</script>
