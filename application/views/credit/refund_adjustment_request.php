<style>
#adjustment_request_div h4{
    margin-top: 70px;
}
.checkbox .chk_lbl {
    position: relative;
    top: 4px;
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
				'title' => 'Refund Request Summary',
				'link' => "/credit/refund_request_summary"
            ),
            array(
				'title' => $title,
				'status' => 'active',
				'link' => "/credit/refund_request"
			)
		);
		$bc_data['bc_items'] = $bc_items;
		$this->load->view('templates/breadcrumbs', $bc_data);
	?>


	

	<section>
		<div class="body-typical-body" style="padding-top:25px;">


        <?php 
        if( validation_errors() ){ ?>
            <div class="alert alert-danger">
            <?php echo validation_errors(); ?>
            </div>
        <?php
        }	
        ?>


		<?php
            $form_attr = array(
                'id' => 'jform'
            );
            echo form_open('/credit/refund_request',$form_attr);
		?>

        <div class="row" id="adjustment_request_div">
            <div class="col-md-12 col-lg-6 columns">


                <h4 style="margin-top:0;">Question to ask agent/caller</h4>

                 <div class="form-group row adjustment_type_div">
                    <label class="col-sm-5 form-control-label">Adjustment type  <span class="color-red">*</span></label>
                    <div class="col-sm-7">
                        <select name="adjustment_type_decoy" id="adjustment_type_decoy" class="form-control" disabled='true'>                       
                            <option value="1">Refund</option>                                             
                        </select>
                        <input type="hidden" id="adjustment_type" name="adjustment_type" value="1"> 
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-5 form-control-label">Has this invoice been paid?  <span class="color-red">*</span></label>
                    <div class="col-sm-7">
                        <select name="invoice_paid" id="invoice_paid" class="form-control" data-validation="[NOTEMPTY]">
                            <option value="">--- Select ---</option>
                            <option value="0">No</option>
                            <option value="1">Yes</option>                       
                        </select>
                    </div>
                </div>
                
                <!--
                <div class="form-group row refund_request_div">
                    <label class="col-sm-5 form-control-label">Refund Request?  <span class="color-red">*</span></label>
                    <div class="col-sm-7">
                        <select name="refund_request" id="refund_request" class="form-control">
                            <option value="1">Yes</option>                        
                        </select>
                    </div>
                </div>-->

                <div class="form-group row refund_details_div">
                    <label class="col-sm-5 form-control-label">Bank details for refund <span class="color-red">*</span></label>
                    <div class="col-sm-7">
                        <textarea name="refund_bank_details" id="refund_bank_details" rows="4" class="form-control" data-validation="[NOTEMPTY]"></textarea>
                    </div>
                </div>
            

            <h4>Job Info</h4>
            <div class="form-group row">
                <label class="col-sm-5 form-control-label">Job Number <span class="color-red">*</span></label>
                <div class="col-sm-7">
                    <input type="text" class="form-control" id="job_id" name="job_id" data-validation="[NOTEMPTY]" />
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-5 form-control-label">Invoice # <span class="color-red">*</span></label>
                <div class="col-sm-7">
                    <input type="text" class="form-control" id="invoice_num" name="invoice_num" readonly="readonly" data-validation="[NOTEMPTY]" />
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-5 form-control-label">Amount <span class="color-red">*</span></label>
                <div class="col-sm-7">
                    <input type="text" class="form-control" id="amount" name="amount" readonly="readonly" data-validation="[NOTEMPTY]" />
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-5 form-control-label">Balance <span class="color-red">*</span></label>
                <div class="col-sm-7">
                    <input type="text" class="form-control" id="balance" name="balance" readonly="readonly" data-validation="[NOTEMPTY,V<0]" />
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-5 form-control-label">Agency <span class="color-red">*</span></label>
                <div class="col-sm-7">
                    <input type="text" class="form-control" id="agency" name="agency" readonly="readonly" data-validation="[NOTEMPTY]" />
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-5 form-control-label">Staff <span class="color-red">*</span></label>
                <div class="col-sm-7">
                    <select name="staff" id="staff" class="form-control" data-validation="[NOTEMPTY]">
                        <option value="">--- Select ---</option>
                        <?php
                        foreach( $staff_sql->result() as $staff_row ){ ?>
                            <option value="<?php echo $staff_row->StaffID; ?>" 
                            <?php echo ( $staff_row->StaffID == $this->session->staff_id )?'selected="selected"':null; ?>
                            >
                                <?php echo $this->system_model->formatStaffName($staff_row->FirstName,$staff_row->LastName); ?>
                            </option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-5 form-control-label">Reason for refund request <span class="color-red">*</span></label>
                <div class="col-sm-7">
                    <textarea name="adjustment_reason" id="adjustment_reason" rows="4" class="form-control" data-validation="[NOTEMPTY]"></textarea>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-5 form-control-label">Refund value request <span class="color-red">*</span></label>
                <div class="col-sm-7">
                    <input type="text" class="form-control" id="adjustment_val_req" name="adjustment_val_req" data-validation="[NOTEMPTY,NUMERIC]" />
                </div>
            </div>
                        
            <div style="display:none;">
                <h4>Requirements from SATS staff</h4>

                <div class="form-group row">

                    <label class="col-sm-12 form-control-label">
                        <span class="checkbox">
                            <input type="checkbox" id="check-1" class="req_chk">
                            <label for="check-1" class="chk_lbl"></label>
                        </span>
                        Attach to property file copy of email
                    </label>
                    
                </div> 

                <div class="form-group row">
                
                    <label class="col-sm-12 form-control-label">
                        <span class="checkbox">
                            <input type="checkbox" id="check-2" class="req_chk">
                            <label for="check-2" class="chk_lbl"></label>
                        </span>
                        Is this a duplicate job? If so have you changed the incorrect job so it doesn't auto renew?
                    </label>
                    
                </div>      

                <div class="form-group row">

                    <label class="col-sm-12 form-control-label">
                        <span class="checkbox">
                            <input type="checkbox" id="check-3" class="req_chk">
                            <label for="check-3" class="chk_lbl"></label>
                        </span>
                        Have you checked the work order to ensure all information is correct if applicable?
                    </label>
                    
                </div>     
            </div>

            <div class="form-group row">
			
				<div class="col-sm-1">
                    <input type="submit" name="btn_submit" id="btn_submit" class="btn" value="Submit">
                </div>
                
            </div>


            </form>

            
                        

            </div>
		</div>


		</div>
	</section>

</div>



<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
    This page is used to create a refund request.
	</p>

</div>
<!-- Fancybox END -->

<script>
jQuery(document).ready(function(){



    
    //success/error message sweel alert pop  start
    <?php 
    if( $this->session->flashdata('new_credit_request') &&  $this->session->flashdata('new_credit_request') == 1 ){ ?>
        swal({
            title: "Success!",
            text: "Adjustment Request Created",
            type: "success",
            confirmButtonClass: "btn-success"
        });
    <?php 
    }
    ?>



    // requirements from SATS check
   /* jQuery(".req_chk").change(function(){

        var req_chk_num = jQuery(".req_chk:checked").length;

        if( req_chk_num == 3 ){
            jQuery("#btn_submit").show();
        }else{
            jQuery("#btn_submit").hide();
        }

    });*/


    // Has this invoice been paid?
    /*jQuery("#invoice_paid").change(function(){

        var opt = jQuery(this).val();

        if( opt == 1 ){ 

            jQuery("#refund_request option[value='"+opt+"']").prop("selected",true);
            jQuery("#adjustment_request_div .refund_request_div").css('display','flex');
            jQuery("#adjustment_request_div .refund_details_div").css('display','flex');

        }else{ 

            jQuery("#refund_request option[value='"+opt+"']").prop("selected",true);
            jQuery("#refund_details").val('');
            jQuery("#adjustment_request_div .refund_request_div").css('display','none');
            jQuery("#adjustment_request_div .refund_details_div").css('display','none');

        }
        
    });*/

    // Refund Request? 
    jQuery("#refund_request").change(function(){

        var opt = jQuery(this).val();

        if( opt == 1 ){ // yes

            jQuery("#adjustment_request_div .refund_details_div").css('display','flex');

        }else{ 

            jQuery("#refund_details").val('');
            jQuery("#adjustment_request_div .refund_details_div").css('display','none');

        }

    });

	// jquery form validation
	jQuery('#jform').validate({
		submit: {
			settings: {
				inputContainer: '.form-group',
				errorListClass: 'form-tooltip-error'
			}
		},
		labels: {
			'job_id': 'Job Number',
			'invoice_num': 'Invoice Number',
			'adjustment_reason': 'Reason for adjustment',
            'adjustment_val_req': 'Adjustment value request',
            'invoice_paid': '"Has this invoice been paid?" field'
		}
	});

    // check existing credit request
    jQuery('#job_id').blur(function(){
        
        var job_id  = jQuery(this).val();

        if( job_id != '' ){

            jQuery("#load-screen").show();        
            jQuery.ajax({
                type: "POST",
                url: "/credit/get_job_data",
                data: { 
                    job_id: job_id
                },
                dataType: 'json'
            }).done(function( ret ){
                
                //console.log(ret);
                
                if(ret.alreadyExist==1){
                
                    jQuery("#invoice_num").val('');
                    jQuery("#amount").val('');
                    jQuery("#agency").val('');

                    swal({
                        title: "Warning!",
                        text: "Adjustment request already exists for this job, do you want to create a new one?",
                        type: "warning",
                        showCancelButton: true,
                        cancelButtonText: "Continue!",
                        confirmButtonClass: "btn-warning",
                        confirmButtonText: "Show Me Request",                       
                        closeOnConfirm: true
                    },
                    function(isConfirm) {
                        
                        if (isConfirm) { // yes

                            // continue with the link
                            window.location = '/credit/request_details/'+ret.cr_id+'?type=credit';
                            
                        }else{ //continue add duplicae refund request
                            jQuery("#invoice_num").val(ret.invoice_num);
                            jQuery("#amount").val(ret.amount); //removed dollar sign
                            jQuery("#agency").val(ret.agency);
                            jQuery("#balance").val(ret.balance);
                        }
                        
                    });
            
                }else if(ret.invoice_num==null){
                    empty_field();
                }else if(ret.amount<0){
                    swal('','You are unable to submit a refund request on this job as it has no cost.','error');
                    empty_field();
                }else if(ret.balance>=ret.amount){ //credit balance applicable only in refund_requst form/page
                    swal('','You are unable to submit a refund request on this invoice as it does not have a credit balance.','error');
                    empty_field();
                }else if(ret.invoice_num!=null){
        
                    jQuery("#invoice_num").val(ret.invoice_num);
                    jQuery("#amount").val(ret.amount); //removed dollar sign
                    jQuery("#agency").val(ret.agency);
                    jQuery("#balance").val(ret.balance);
                
                }
                jQuery("#load-screen").hide();            
                
            });

        }
        
    });


});

function empty_field(){
    jQuery("#invoice_num").val('');
    jQuery("#amount").val('');
    jQuery("#agency").val('');
    jQuery("#balance").val('');
}

</script>