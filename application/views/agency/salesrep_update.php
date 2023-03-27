<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/agency/salesrep_update"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>


    <div>
        <header class="box-typical-header">

            <div class="box-typical box-typical-padding">


            <div class="row" id="question_dp_div">
                <div class="col-md-12">
                    <label>Would you like to move ALL Agencies from one Sales Rep to Another?</label>
                    <select id="question_dp" name="question_dp" class="form-control col-md-1">
                        <option value="">SELECT</option>								
                        <option value="0" <?php echo ( $this->input->get_post('salesrep_filter') )?'selected="selected"':null; ?>>No</option>
                        <option value="1">Yes</option>						
                    </select>
                </div>									
            </div>

            <?php
            $form_attr = array(
                'id' => 'jform_update_all'
            );
            echo form_open('agency/salesrep_update_process/all',$form_attr);
            ?>
            <div class="row" id="update_salesrep_all_div">

                <div class="form-group col-md-2">
                    <label>From</label>
                    <select id="salesrep_from" name="salesrep_from" class="form-control salesrep_dp salesrep_from_dp">
                        <option value="">SELECT</option>								
                        <?php
                        foreach( $staff_accounts_sql->result() as $sa ){ ?>
                            <option value="<?php echo $sa->StaffID; ?>"><?php echo "{$sa->FirstName} {$sa->LastName}"; ?> (<?php echo ( $sa->active == 1 )?'active':'inactive' ?>)</option>
                        <?php
                        }
                        ?>					
                    </select>
                </div>

                <div class="form-group col-md-2">
                    <label>To</label>
                    <select id="salesrep_to" name="salesrep_to" class="form-control salesrep_dp salesrep_to_dp">
                        <option value="">SELECT</option>								
                        <?php
                        foreach( $staff_accounts_sql->result() as $sa ){ ?>
                            <option value="<?php echo $sa->StaffID; ?>"><?php echo "{$sa->FirstName} {$sa->LastName}"; ?> (<?php echo ( $sa->active == 1 )?'active':'inactive' ?>)</option>
                        <?php
                        }
                        ?>							
                    </select>
                </div>

                <div class="col-md-1 columns">
                    <label class="col-sm-12 form-control-label">&nbsp;</label>
                    <button type="button" class="btn btn_update">Update</button>
                </div>

            </div>
            <?php
            echo form_close();
            ?>


            <?php
            $form_attr = array(
                'id' => 'jform_search'
            );
            echo form_open('agency/salesrep_update',$form_attr);
            ?>
                <div id="update_salesrep_indiv_div" class="for-groupss row salerep_update_indiv">
                    <div class="col-md-12 columns">
                        <div class="row">

                            <div class="col-mdd-3">
                                <label>Salesrep</label>
                                <select id="salesrep_filter" name="salesrep_filter" class="form-control salesrep_filter">
                                    <option value="">ALL</option>								
                                    <?php
                                    foreach( $salesrep_filter_dp->result() as $sr ){ ?>
                                        <option value="<?php echo $sr->StaffID; ?>" <?php echo ( $sr->StaffID == $this->input->get_post('salesrep_filter') )?'selected="selected"':null; ?>><?php echo $this->system_model->formatStaffName($sr->FirstName,$sr->LastName); ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                                <div class="mini_loader"></div>
                            </div>										

                            <div class="col-md-1 columns">
                                <label class="col-sm-12 form-control-label">&nbsp;</label>
                                <input type="submit" name="search_submit" value="Search" class="btn">
                            </div>
                            
                        </div>

                    </div>
                </div>
            <?php
            echo form_close();
            ?>
            </div>
        </header>

        <section class='salerep_update_indiv'>
            <div class="body-typical-body">

                <?php
                $form_attr = array(
                    'id' => 'jform_update_indiv'
                );
                echo form_open('agency/salesrep_update_process/indiv',$form_attr);
                ?>

                <div class="table-responsive">

                    <table class="table table-hover main-table">

                        <thead>
                            <tr>
                                <th>Agency</th>
                                <th>Status</th>
                                <th>Salesrep</th>
                                <th class="check_all_td">
                                    <div class="checkbox" style="margin:0;">
                                        <input name="chk_all" type="checkbox" id="check-all">
                                        <label for="check-all">&nbsp;</label>
                                    </div>
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php  
                            if( $this->input->get_post('salesrep_filter') > 0 ){

                                foreach( $lists->result() as $index => $row ){ ?>
                                    <tr>
                                        <td>
                                            <a href="/agency/view_agency_details/<?php echo $row->agency_id; ?>">
                                                <?php echo $row->agency_name; ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php echo ucfirst($row->status); ?>
                                        </td>
                                        <td>
                                            <?php echo $this->system_model->formatStaffName($row->FirstName,$row->LastName); ?>
                                        </td>
                                        <td>
                                            <div class="checkbox" style="margin:0;">
                                                <input type="checkbox" name="chk_agency[]" id="chk_<?php echo $index; ?>" class="chk_agency" value="<?php echo $row->agency_id; ?>">
                                                <label for="chk_<?php echo $index; ?>">&nbsp;</label>
                                            </div>
                                        </td>
                                    </tr>
                                <?php
                                }

                            }else{ ?>
                                <tr>
                                    <td colspan="4">Press Search to display data</td>                    
                                </tr>
                            <?php    
                            }
                            ?>                                
                        </tbody>

                    </table>

                </div>

                <?php
                if( $this->input->get_post('salesrep_filter') > 0 ){ ?>

                    <div class="row" id="indiv_salesrep_update_div">

                        <div class="col-mdd-3">
                            <label>Update Salesrep To:</label>
                            <select id="salesrep_to" name="salesrep_to" class="form-control salesrep_to_dp">
                                <option value="">SELECT</option>								
                                <?php
                                foreach( $staff_accounts_sql->result() as $sa ){ ?>
                                    <option value="<?php echo $sa->StaffID; ?>"><?php echo "{$sa->FirstName} {$sa->LastName}"; ?> (<?php echo ( $sa->active == 1 )?'active':'inactive' ?>)</option>
                                <?php
                                }
                                ?>
                            </select>
                            <div class="mini_loader"></div>
                        </div>										

                        <div class="col-md-1 columns">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <input type="hidden" name="salesrep_from" class="salesrep_from_dp" value="<?php echo $this->input->get_post('salesrep_filter'); ?>" />
                            <button type="button" class="btn btn_update">Update</button>
                        </div>
                        
                    </div>

                <?php
                }
                ?> 
                

                <?php
                echo form_close();
                ?>

                

                <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
                <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
                

            </div>
        </section>
    </div>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
        Move Agencies form one Sales rep to another
	</p>

</div>
<!-- Fancybox END -->

<style>
.rf_select{
	font-weight: bold;
}

.approvedHLstatus {
    color: green;
    font-weight: bold;
}
.pendingHLstatus {
    color: red;
    font-style: italic;
}
.declinedHLstatus {
    color: red;
	font-weight: bold;
}
.more_infoHLstatus {
    color: #f37b53;
	font-weight: bold;
}


<?php
if( $this->input->get_post('salesrep_filter') ){ ?>
    .salerep_update_indiv
    {
        display: block;
    }
    #update_salesrep_all_div,
    #indiv_salesrep_update_div
    {
        display: none;
    }
<?php
}else{ ?>
    .salerep_update_indiv,
    #update_salesrep_all_div
    {
        display: none;
    }
<?php
}
?>


#update_salesrep_all_div,
#update_salesrep_indiv_div{
    margin-top: 25px;
}
</style>
<script>
jQuery(document).ready(function(){


    //success/error message sweel alert pop  start
    <?php 
    if( $this->session->flashdata('salesrep_update_success') &&  $this->session->flashdata('salesrep_update_success') == 1 ){ ?>
        swal({
            title: "Success!",
            text: "Salesrep Update Successful",
            type: "success",
            confirmButtonClass: "btn-success"
        });
    <?php 
    }
    ?>

    // check all
    jQuery("#check-all").click(function(){

        jQuery(".chk_agency").click();

    });

    jQuery(".chk_agency").click(function(){

        var num_ticked = jQuery(".chk_agency:checked").length;
 
        if( num_ticked > 0 ){
            jQuery("#indiv_salesrep_update_div").css('display','flex');
        }else{
            jQuery("#indiv_salesrep_update_div").hide();
        }

    });

    // question switch
    jQuery("#question_dp").change(function(){
        
        if( parseInt(jQuery(this).val()) == 1 ){ // yes
            jQuery("#update_salesrep_all_div").css('display','flex');
            jQuery(".salerep_update_indiv").hide();
        }else{ // no
            jQuery("#update_salesrep_all_div").hide();
            jQuery(".salerep_update_indiv").show();
        }

    });

    // salerep filter search
    jQuery("#jform_search").submit(function(){
    
        var salesrep_filter = jQuery("#jform_search .salesrep_filter").val();

        if( salesrep_filter != '' ){
            return true;
        }else{
        
            swal({
                title: "Required!",
                text: "Salesrep Filter is required",
                type: "warning",
                confirmButtonClass: "btn-success"
            });
            return false;

        }

        
    });
  
    // update all
    jQuery("#jform_update_all .btn_update").click(function(){
    
        var salesrep_from = jQuery("#jform_update_all .salesrep_from_dp").val();
        var salesrep_to = jQuery("#jform_update_all .salesrep_to_dp").val();

        if( salesrep_from != '' && salesrep_to != '' ){
            
            swal(
                {
                    title: "",
                    text: 'Are you sure you want to update Salerep?',
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-success",
                    confirmButtonText: "Yes, Update",
                    cancelButtonText: "No, Cancel!",
                    closeOnConfirm: false,
                    closeOnCancel: true,
                },
                function(isConfirm){

                    if(isConfirm){
                        jQuery("#jform_update_all").submit();
                    }
                    
                }
            );
            
        }else{
           
            swal({
                title: "Required!",
                text: "Salesrep From and To are required",
                type: "warning",
                confirmButtonClass: "btn-success"
            });

        }

        
    });

    // update individually
    jQuery("#jform_update_indiv .btn_update").click(function(){
    
    var salesrep_from = jQuery("#jform_update_indiv .salesrep_from_dp").val();
    var salesrep_to = jQuery("#jform_update_indiv .salesrep_to_dp").val();

    if( salesrep_from != '' && salesrep_to != '' ){
        
        swal(
            {
                title: "",
                text: 'Are you sure you want to update Salerep?',
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Update",
                cancelButtonText: "No, Cancel!",
                closeOnConfirm: false,
                closeOnCancel: true,
            },
            function(isConfirm){

                if(isConfirm){
                    jQuery("#jform_update_indiv").submit();
                }
                
            }
        );

    }else{
       
        swal({
            title: "Required!",
            text: "Salesrep From and To are required",
            type: "warning",
            confirmButtonClass: "btn-success"
        });

    }

    
});


	
});
</script>