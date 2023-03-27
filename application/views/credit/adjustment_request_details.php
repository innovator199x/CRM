<style>
#adjustment_request_div h4{
    margin-top: 70px;
}
.checkbox .chk_lbl {
    position: relative;
    top: 4px;
}
.curr_pdf_file{
    padding-bottom:8px;
}
</style>
<div class="box-typical box-typical-padding">
	<?php 
        // breadcrumbs template
        if($type=="credit"){
            $bc_title = "Credit Request Summary";
            $bc_url = "/credit/credit_request_summary";
            $bc_type = 'credit';
            $bc_title_active_title = "Credit Request Details";
        }else{
            $bc_title = "Refund Request Summary";
            $bc_url = "/credit/refund_request_summary";
            $bc_type = 'refund';
            $bc_title_active_title = "Refund Request Details";
        }

		$bc_items = array(
            array(
				'title' => 'Reports',
				'link' => "/reports"
            ),
			array(
				'title' => $bc_title,
				'link' => $bc_url
            ),
            array(
				'title' => $bc_title_active_title,
				'status' => 'active',
				'link' => "/credit/request_details/{$cr_id}?type={$bc_type}"
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
            echo form_open_multipart("/credit/request_details/{$cr_id}?type={$bc_type}",$form_attr);
		?>

        <div class="row" id="adjustment_request_div">
            <div class="col-md-12 col-lg-6 columns">


            <h4 style="margin-top:0;">Question to ask agent/caller</h4>

            <div class="form-group row">
                <label class="col-sm-5 form-control-label">Has this invoice been paid?  <span class="color-red">*</span></label>
                <div class="col-sm-7">
                    <select name="invoice_paid" id="invoice_paid" class="form-control" data-validation="[NOTEMPTY]">
                        <option value="">--- Select ---</option>
                        <option value="0" <?php echo ( is_numeric($cr_row->invoice_paid) && $cr_row->invoice_paid == 0 )?'selected="selected"':null; ?>>No</option>
                        <option value="1" <?php echo ( is_numeric($cr_row->invoice_paid) && $cr_row->invoice_paid == 1 )?'selected="selected"':null; ?>>Yes</option>                       
                    </select>
                </div>
            </div>

            <div class="form-group row refund_request_div">
                <label class="col-sm-5 form-control-label">Refund Request?  <span class="color-red">*</span></label>
                <div class="col-sm-7">
                    <select name="refund_request" id="refund_request" class="form-control">
                        <?php 
                        if($type == "credit"){
                        ?>
                              <option value="0" <?php echo ( is_numeric($cr_row->refund_request) && $cr_row->refund_request == 0 )?'selected="selected"':null; ?>>No</option>
                        <?php
                        }elseif($type == "refund"){
                        ?>
                            <option value="1" <?php echo ( is_numeric($cr_row->refund_request) && $cr_row->refund_request == 1 )?'selected="selected"':null; ?>>Yes</option>      
                        <?php
                        } 
                        ?>         
                    </select>
                </div>
            </div>
            
            <?php
            //show only if refund
            if($type=='refund'){
            ?>
                <div class="form-group row refund_details_div">
                    <label class="col-sm-5 form-control-label">Bank details for refund <span class="color-red">*</span></label>
                    <div class="col-sm-7">
                        <textarea name="refund_bank_details" id="refund_bank_details" rows="4" class="form-control" data-validation="[NOTEMPTY]"><?php echo $cr_row->refund_bank_details; ?></textarea>
                    </div>
                </div>
            <?php
            } 
            ?>
            
            <h4>Job Info</h4>
            <div class="form-group row">
                <label class="col-sm-5 form-control-label">Job Number <span class="color-red">*</span></label>
                <div class="col-sm-7">
                    <input type="text" class="form-control" id="job_id" name="job_id" data-validation="[NOTEMPTY]" readonly="readonly" value="<?php echo $cr_row->job_id ?>" />
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-5 form-control-label">Invoice # <span class="color-red">*</span></label>
                <div class="col-sm-7">
                    <input type="text" class="form-control" id="invoice_num" name="invoice_num" readonly="readonly" data-validation="[NOTEMPTY]" value="<?php echo $invoice_num ?>"  />
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-5 form-control-label">Amount <span class="color-red">*</span></label>
                <div class="col-sm-7">
                    <input type="text" class="form-control" id="amount" name="amount" readonly="readonly" data-validation="[NOTEMPTY]" value="<?php echo $cr_row->invoice_amount ?>" />
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-5 form-control-label">Agency</label>
                <div class="col-sm-7">
                <input type="text" class="form-control" id="agency" name="agency" readonly="readonly" value="<?php echo $cr_row->agency_name ?>" />
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-5 form-control-label">Staff <span class="color-red">*</span></label>
                <div class="col-sm-7">
                    <input type="text" class="form-control" id="requested_by_name" name="requested_by_name" readonly="readonly" data-validation="[NOTEMPTY]"  value="<?php echo "{$cr_row->rb_fname} {$cr_row->rb_lname}" ?>" />   
                </div>
            </div>

            <?php
            if( $type == "credit" ){ ?>

                <div class="form-group row refund_request_div">
                    <label class="col-sm-5 form-control-label">Reason for adjustment? <span class="color-red">*</span></label>
                    <div class="col-sm-7">
                        <select name="reason_for_adjustment" id="reason_for_adjustment" class="form-control reason_for_adjustment" data-validation="[NOTEMPTY]">
                            <option value="">--- Select ---</option>    
                            <?php 
                            foreach( $cred_req_adj_res_sql->result() as $cred_req_adj_res_row ){ ?>
                                <option value="<?php echo $cred_req_adj_res_row->id; ?>" <?php echo ( $cred_req_adj_res_row->id == $cr_row->reason_for_adjustment )?'selected':''; ?>><?php echo $cred_req_adj_res_row->reason; ?></option>    
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>

            <?php
            }
            ?>
            

            <div class="form-group row">
                <?php
                    $reason_adjustment_label = ($type=='refund') ? 'Reason for refund' : 'Reason';
                ?>
                <label class="col-sm-5 form-control-label"><?php echo $reason_adjustment_label; ?> <span class="color-red">*</span></label>
                <div class="col-sm-7">
                    <textarea name="adjustment_reason" id="adjustment_reason" rows="4" class="form-control" data-validation="[NOTEMPTY]"><?php echo $cr_row->cr_reason; ?></textarea>
                </div>
            </div>

            <div class="form-group row">
                <?php
                    $adjustment_value_req_label = ($type=='refund') ? 'Refund value request' : 'Adjustment value request';
                ?>
                <label class="col-sm-5 form-control-label"><?php echo $adjustment_value_req_label; ?> <span class="color-red">*</span></label>
                <div class="col-sm-7">
                    <input type="text" class="form-control" id="adjustment_val_req" name="adjustment_val_req" data-validation="[NOTEMPTY,NUMERIC]" value="<?php echo $cr_row->adjustment_val_req; ?>" />
                </div>
            </div>

            <h4>Accounts Use Only</h4>

            <div class="form-group row">
                <label class="col-sm-5 form-control-label">Result</label>
                <div class="col-sm-7">
                    <select name="result" id="result" class="form-control">
                        <option value="pending">Pending</option>  
                        <option value="1" <?php echo ( $cr_row->result == 1 )?'selected="selected"':''; ?>>Accept</option>	
                        <option value="0" <?php echo ( is_numeric($cr_row->result) && $cr_row->result==0 )?'selected="selected"':''; ?>>Decline</option>
                        <option value="2" <?php echo ( is_numeric($cr_row->result) && $cr_row->result==2 )?'selected="selected"':''; ?>>More info needed</option>
                        <option value="3" <?php echo ( is_numeric($cr_row->result) && $cr_row->result==3 )?'selected="selected"':''; ?>>Not Applicable</option>
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-5 form-control-label">Amount Credited <span class="color-red amount_credited_asterisk">*</span></label>
                <div class="col-sm-7">
                    <input type="text" class="form-control" id="amount_credited" name="amount_credited" data-validation="[NOTEMPTY]" value="<?php echo $cr_row->amount_credited; ?>" />
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-5 form-control-label">Date Processed</label>
                <div class="col-sm-7">
                    <input name="date_processed" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text" value="<?php echo ( $this->system_model->isDateNotEmpty($cr_row->date_processed) )?date('d/m/Y',strtotime($cr_row->date_processed)):null; ?>" />
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-5 form-control-label">Comments</label>
                <div class="col-sm-7">
                    <textarea name="comments" id="comments" rows="4" class="form-control"><?php echo $cr_row->cr_comments; ?></textarea>
                </div>
            </div>

            <?php if($type=='refund'){ //show upload pdf's only when refund ?>
            <?php 
                $is_accepted = ( $cr_row->result == 1 ) ? true : false;
            ?>
            <h4>PDF Attachment</h4>
            <div class="form-group row">
                <label class="col-sm-5 form-control-label"><!--<input type="checkbox">--> Proof of Payment <span class="text-red proof_payment_asterisk"><?php ##echo (($is_accepted) ? ($cr_row->proof_of_payment_pdf=="") ? '*' :NULL : NULL); ?></span><br/><small class="text-red">(Uploading new one will replaced the current file)</small></label>
                <div class="col-sm-7">
                    <?php
                        if($cr_row->proof_of_payment_pdf!=""){ //show link whenk has pdf
                    ?>
                            <div class="curr_pdf_file"><a target="_blank" href="<?php echo "/uploads/request_summary_files/".$cr_row->proof_of_payment_pdf ?>">View current Proof of Payment pdf...</a></div>
                    <?php
                        }
                    ?>
                    <input <?php ##echo  (($is_accepted) ? ($cr_row->proof_of_payment_pdf=="") ? 'data-validation="[NOTEMPTY]"' : NULL : NULL ); ?>  type="file" name="payment_pdf_upload_input" id="payment_pdf_upload_input" class="form-control">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-5 form-control-label"><!--<input type="checkbox">-->Proof of Allocation <span class="text-red proof_allocation_asterisk"><?php ##echo (($is_accepted) ? ($cr_row->proof_of_allocation_pdf=="") ? '*' :NULL : NULL); ?></span><br/><small class="text-red">(Uploading new one will replaced the current file)</small></label>
                <div class="col-sm-7">
                     <?php
                        if($cr_row->proof_of_allocation_pdf!=""){ //show link whenk has pdf
                    ?>
                            <div class="curr_pdf_file"><a target="_blank" href="<?php echo "/uploads/request_summary_files/".$cr_row->proof_of_allocation_pdf ?>">View current Proof of Allocation pdf...</a></div>
                    <?php
                        }
                    ?>
                    <input <?php ##echo (($is_accepted) ? ($cr_row->proof_of_allocation_pdf=="") ? 'data-validation="[NOTEMPTY]"' : NULL : NULL); ?>  type="file" name="allocation_pdf_upload_input" id="allocation_pdf_upload_input" class="form-control">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-5 form-control-label"><!--<input type="checkbox">--> Email trail <span class="text-red email_trail_asterisk"><?php echo (($is_accepted) ? ($cr_row->email_trail_pdf=="") ? '*' :NULL : NULL); ?></span><br/><small class="text-red">(Uploading new one will replaced the current file)</small></label>
                <div class="col-sm-7">
                    <?php
                        if($cr_row->email_trail_pdf!=""){ //show link whenk has pdf
                    ?>
                            <div class="curr_pdf_file"><a target="_blank" href="<?php echo "/uploads/request_summary_files/".$cr_row->email_trail_pdf ?>">View current Email trail  pdf...</a></div>
                    <?php
                        }
                    ?>
                <input <?php echo (($is_accepted) ?  'data-validation="[NOTEMPTY]"' : NULL ); ?> type="file" name="trail_pdf_upload_input" id="trail_pdf_upload_input" class="form-control">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-5 form-control-label"><!--<input type="checkbox">--> Other <br/><small class="text-red">(Uploading new one will replaced the current file)</small></label>
                <div class="col-sm-7">
                    <?php
                        if($cr_row->other_pdf!=""){ //show link whenk has pdf
                    ?>
                            <div class="curr_pdf_file"><a target="_blank" href="<?php echo "/uploads/request_summary_files/".$cr_row->other_pdf ?>">View current Other  pdf...</a></div>
                    <?php
                        }
                    ?>
                    <input type="file" name="other_pdf_upload_input" class="form-control">
                </div>
            </div>
            <?php } ?>

            <div class="form-group row" style="margin-top:30px;">
                <label class="col-sm-5 form-control-label">&nbsp;</label>
                <div class="col-sm-7">
                    <input type="hidden" name="prop_address" value="<?php echo "{$cr_row->p_address_1} {$cr_row->p_address_2}, {$cr_row->p_address_3} {$cr_row->p_state} {$cr_row->p_postcode}"; ?>" />
                    <input type="hidden" name="rb_email" value="<?php echo $cr_row->rb_email; ?>" />
                    <input type="hidden" name="job_id" value="<?php echo $cr_row->job_id; ?>" />
                    <span>
                        <input type="submit" name="btn_update" id="btn_update" class="btn" value="Update" />
                    </span>
                    <?php
                    if( $this->system_model->getStaffClassID() == 2 ){ // global users only ?>
                        <span>
                            <button type="button" class="btn btn-danger" id="btn_delete">Delete</button>
                        </span>
                    <?php
                    }
                    ?>                                        
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

    <?php 
    if($type == 'refund'){
        $about_title = "Refund Request Details";
        $about_text = "This page is used for assessing refund requests.";
    }else{
        $about_title = "Credit Request Details";
        $about_text = " This page is used for assessing credit requests.";
    }
    ?>
	<h4><?php echo $about_title; ?></h4>
	<p>
	<?php echo $about_text; ?>
	</p>

</div>
<!-- Fancybox END -->

<script>
jQuery(document).ready(function(){

    //success/error message sweel alert pop  start
    <?php 
    if( $this->session->flashdata('credit_request_updated') &&  $this->session->flashdata('credit_request_updated') == 1 ){ ?>
        swal({
            title: "Success!",
            text: "Adjustment Request Updated",
            type: "success",
            confirmButtonClass: "btn-success"
        });
    <?php 
    }
    ?>


    // Has this invoice been paid?
    /*jQuery("#invoice_paid").change(function(){

        var opt = jQuery(this).val();

        if( opt == 1 ){ // yes

            jQuery("#refund_request option[value='"+opt+"']").prop("selected",true);
            jQuery("#adjustment_request_div .refund_request_div").css('display','flex');
            jQuery("#adjustment_request_div .refund_details_div").css('display','flex');

        }else{ 

            jQuery("#refund_request option[value='"+opt+"']").prop("selected",true);
            jQuery("#refund_details").val('');
            jQuery("#adjustment_request_div .refund_request_div").css('display','none');
            jQuery("#adjustment_request_div .refund_details_div").css('display','none');

        }
        
    });
    */

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
            'invoice_paid': '"Has this invoice been paid?" field',
            'amount_credited': 'Amount Credited'
            
		}
	});

    
    jQuery("#btn_delete").click(function(){

        swal({
			title: "Delete",
			text: "Are you sure you want to delete?",
			type: "warning",
			showCancelButton: true,
            cancelButtonText: "No, Cancel!",
            confirmButtonText: "Yes, Proceed",
			confirmButtonClass: "btn-danger"
		},
        function(isConfirm) {
            
            if (isConfirm) { // yes

                window.location = '/credit/delete/<?php echo $cr_id; ?>?type=<?php echo $bc_type; ?>';
                
            }
            
        });

    });

    //add custom tweak for refund type
    <?php if($type=='refund'){ ?>
        
        // on load
        var taetok_var = $('#result').val();
        taetok(taetok_var);
        
        // on change
        $('#result').change(function(){

            var thisval = $(this).val();
            var payment_pdf_upload_input = $('#payment_pdf_upload_input').val();
            var allocation_pdf_upload_input = $('#allocation_pdf_upload_input').val();
            var trail_pdf_upload_input = $('#trail_pdf_upload_input').val();

            taetok(thisval);
            
            if(thisval==1){
               // $('.proof_payment_asterisk').html('*');
                //$('.proof_allocation_asterisk').html('*');
                $('.email_trail_asterisk').html('*');
                
                if(trail_pdf_upload_input==""){
                    $('#trail_pdf_upload_input').attr("data-validation","[NOTEMPTY]");
                }

                /*if(allocation_pdf_upload_input==""){
                    $('#allocation_pdf_upload_input').attr("data-validation","[NOTEMPTY]");
                }*/
               
            }else{
                $('.email_trail_asterisk').html('');
                $('#trail_pdf_upload_input').removeAttr('data-validation');

                /*$('.proof_allocation_asterisk').html('');
                $('#allocation_pdf_upload_input').removeAttr('data-validation');*/
            }

        })

    <?php } ?>


    <?php if($type=='credit'){ ?>
        
        // on change
        $('#result').change(function(){
            var thisval = $(this).val();
            taetok(thisval);
        })

        //on load
        var taetok_var = $('#result').val();
        taetok(taetok_var);

     <?php } ?>

     function taetok(val){
         if(val!=1){
            $('#amount_credited').removeAttr('data-validation');
            $('.amount_credited_asterisk').html('');
         }else{
            $('#amount_credited').attr("data-validation","[NOTEMPTY]");
            $('.amount_credited_asterisk').html('*');
         }
     }


});
</script>