// delete job/invoice
jQuery("#agen_pay_det_fb_div").on("click",".del_agen_pay_j_btn",function(){

    var del_btn_dom = jQuery(this);

    var agen_pay_det_tbl = del_btn_dom.parents(".agen_pay_det_tbl:first");
    var agency_payments_id = agen_pay_det_tbl.find(".agency_payments_id").val();
    
    var row = del_btn_dom.parents(".invoice_num_span");
    var ageny_pay_j_id = row.find(".ageny_pay_j_id").val();
    var jid = row.find(".jid").val();    


    // display fancybox
    var html_str =''+
    '<div class="rev_inv_div">'+
        '<p class="text-left">Are you sure you want to reverse this invoice?</p>'+
        '<textarea id="remove_invoice_reason'+ageny_pay_j_id+'" class="form-control" placeholder="Enter Reason"></textarea>'+
        '<p class="text-left mt-4">Would you like to transfer this amount?</p>'+
        '<div>'+
            '<select id="transfer_invoice'+ageny_pay_j_id+'" class="form-control transfer_invoice">'+	            
                '<option value="0">No</option>'+
                '<option value="1">Yes</option>'+																				
            '</select>'+
        '</div>'+
        '<div class="rev_inv_transfer_div">'+
            '<div class="text-left mt-4 row">'+
                '<div class="col-md-6">'+
                    '<input type="text" id="transfer_job_id'+ageny_pay_j_id+'" class="form-control transfer_job_id" placeholder="Job ID" />'+
                '</div>'+
                '<div class="col-md-6">'+
                    '<input type="text" id="transfer_amount'+ageny_pay_j_id+'" class="form-control transfer_amount" placeholder="Balance" readonly="readonly" />'+
                '</div>'+
            '</div>'+
        '</div>'+            
    '</div>';

    swal({
        html:true,
        title: "Warning!",
        text: html_str,
        type: "warning",						
        showCancelButton: true,
        confirmButtonClass: "btn-success",
        confirmButtonText: "Yes, Reverse",
        cancelButtonClass: "btn-danger",
        cancelButtonText: "No, Cancel!",
        closeOnConfirm: true,
        showLoaderOnConfirm: true,
        closeOnCancel: true,
        customClass: "rev_pay_confirm_swal"
    },
    function(isConfirm) {

        if (isConfirm) {	
            
            var remove_invoice_reason = jQuery("#remove_invoice_reason"+ageny_pay_j_id).val();	
            var transfer_invoice = jQuery("#transfer_invoice"+ageny_pay_j_id).val();
            var transfer_job_id = jQuery("#transfer_job_id"+ageny_pay_j_id).val();

            $('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/accounts/ajax_agency_payments_delete_jobs",
                data: { 	
                    ageny_pay_j_id: ageny_pay_j_id,
                    agency_payments_id: agency_payments_id,
                    jid: jid,
                    remove_invoice_reason: remove_invoice_reason,
                    transfer_invoice: transfer_invoice,
                    transfer_job_id: transfer_job_id
                }
                
            }).done(function(){

                $('#load-screen').hide();      
                location.reload();              

            });	                             
            

        }

    });
    		

});


// transfer invoice dropdown toggle
jQuery(document).on('change',".rev_inv_div .transfer_invoice",function(){

    var node = jQuery(this);

    var transfer_invoice = node.val();    
    var rev_inv_div = node.parents(".rev_inv_div:first");

    var transfer_job_id = rev_inv_div.find(".transfer_job_id").val();
    var transfer_amount = parseFloat(rev_inv_div.find(".transfer_amount").val());
    

    if( transfer_invoice == 1 ){ // Yes

        rev_inv_div.find('.rev_inv_transfer_div').show();

        // disable yes button if amount is 0
        if( transfer_amount > 0 && transfer_job_id != '' ){           
            jQuery(".rev_pay_confirm_swal:visible").find(".confirm").prop("disabled",false);            
        }else{
            jQuery(".rev_pay_confirm_swal:visible").find(".confirm").prop("disabled",true);
            jQuery(".rev_pay_confirm_swal:visible").find(".la-ball-fall").hide();
        }

    }else{ // No

        rev_inv_div.find('.rev_inv_transfer_div').hide();
        jQuery(".rev_pay_confirm_swal:visible").find(".confirm").prop("disabled",false);      
    }
    

});

// enable/disable reverse payment confirm button
jQuery(document).on('change',".rev_inv_div .transfer_job_id",function(){

    var transfer_job_id = jQuery(this).val();         
    var agen_pay_det_tbl = jQuery(".agen_pay_det_tbl:visible");
    var agency_dom = agen_pay_det_tbl.find('.ageny_pay_a_edit_agency');

    var agency_arr = [];
    agency_dom.each(function(){ 
        var agency_id = jQuery(this).val();       
        agency_arr.push(agency_id);
    });


    if( transfer_job_id != '' ){     
       
        $('#load-screen').show();
        jQuery.ajax({
            type: "POST",
            url: "/accounts/ajax_get_tranfered_to_jobs_invoice_balance",
            data: { 	
                job_id: transfer_job_id,
                agency_arr: agency_arr
            }
            
        }).done(function(ret){

            $('#load-screen').hide();  
            
            if( ret != '' ){

                jQuery(".rev_pay_confirm_swal:visible").find(".confirm").prop("disabled",false);
                jQuery(".rev_inv_div .transfer_amount").val(ret); 

            }
                   

        });

    }else{  

        jQuery(".rev_pay_confirm_swal:visible").find(".confirm").prop("disabled",true);
        jQuery(".rev_pay_confirm_swal:visible").find(".la-ball-fall").hide();

    }

});


// bank deposit inline update
jQuery(".inline_bank_deposit").change(function(){

    var node = jQuery(this);
    var parent_div = node.parents("tr:first");

    var agency_payments_id = parent_div.find(".main_agen_pay_id").val();
    var bank_deposit = node.val();
    var reference = parent_div.find(".main_reference").val();

    $('#load-screen').show();
    jQuery.ajax({
        type: "POST",
        url: "/accounts/ajax_agen_pay_bank_deposit_inline_update",
        data: { 	
            agency_payments_id: agency_payments_id,
            bank_deposit: bank_deposit,
            reference: reference
        }
        
    }).done(function(ret){

        $('#load-screen').hide();      
        location.reload();    

    });

});


// remittance inline update
jQuery(".inline_remittance").change(function(){

    var node = jQuery(this);
    var parent_div = node.parents("tr:first");

    var agency_payments_id = parent_div.find(".main_agen_pay_id").val();
    var remittance = node.val();
    var reference = parent_div.find(".main_reference").val();

    $('#load-screen').show();
    jQuery.ajax({
        type: "POST",
        url: "/accounts/ajax_agen_pay_remittance_inline_update",
        data: { 	
            agency_payments_id: agency_payments_id,
            remittance: remittance,
            reference: reference
        }
        
    }).done(function(ret){

        $('#load-screen').hide();      
        location.reload();    

    });

});


// reverse agency payments
jQuery("#agen_pay_det_fb_div").on("click",".reverse_payment_btn",function(){

    var btn_dom = jQuery(this);
    var row = btn_dom.parents("tr:first");

    var agen_pay_det_tbl = btn_dom.parents(".agen_pay_det_tbl:first");

    var agency_payments_id = agen_pay_det_tbl.find(".agency_payments_id").val();    
    var reference = agen_pay_det_tbl.find(".reference").val();
    var amount = agen_pay_det_tbl.find(".amount").val();   
    var date = agen_pay_det_tbl.find(".date").val();


    // display fancybox
    var html_str =''+
    '<div class="rev_pay_div">'+
        '<p class="text-left">Are you sure you want to reverse payment?</p>'+      
        '<textarea id="rev_pay_reason'+agency_payments_id+'" class="form-control" placeholder="Enter Reason"></textarea>'+        
    '</div>';
    

    swal({
        html:true,
        title: "Warning!",
        text: html_str,
        type: "warning",						
        showCancelButton: true,
        confirmButtonClass: "btn-success",
        confirmButtonText: "Yes, Reverse",
        cancelButtonClass: "btn-danger",
        cancelButtonText: "No, Cancel!",
        closeOnConfirm: true,
        showLoaderOnConfirm: true,
        closeOnCancel: true,
        customClass: "rev_pay_confirm_swal"
    },
    function(isConfirm) {

        if (isConfirm) {		
            
            var rev_pay_reason = jQuery("#rev_pay_reason"+agency_payments_id).val();	
            
            $('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/accounts/ajax_reverse_agency_payments",
                data: { 	                        
                    agency_payments_id: agency_payments_id,
                    reference: reference,
                    amount: amount,
                    date: date,
                    rev_pay_reason: rev_pay_reason
                }
                
            }).done(function( ret ){

                $('#load-screen').hide();  
                location.reload();                   

            });	

        }

    });		

});


// reverse agency payments
jQuery("#agen_pay_det_fb_div").on("click",".agen_pay_partial_refund_btn",function(){

    var btn_dom = jQuery(this);
    var row = btn_dom.parents("tr:first");

    var agen_pay_det_tbl = btn_dom.parents(".agen_pay_det_tbl:first");

    var agency_payments_id = agen_pay_det_tbl.find(".agency_payments_id").val();    
    var reference = agen_pay_det_tbl.find(".reference").val();
    var amount = agen_pay_det_tbl.find(".amount").val();
    var remaining = agen_pay_det_tbl.find(".remaining").val();   
    var date = agen_pay_det_tbl.find(".date").val();


    // display fancybox
    var html_str =''+
    '<div class="partial_refund_swal_div">'+
        '<p class="text-left">Are you sure you want to partial refund?</p>'+      
        '<input type="text" id="partial_refund_amount'+agency_payments_id+'" class="form-control partial_refund_amount" placeholder="Enter Amount" />'+        
    '</div>';
    

    swal({
        html:true,
        title: "Warning!",
        text: html_str,
        type: "warning",						
        showCancelButton: true,
        confirmButtonClass: "btn-success",
        confirmButtonText: "Yes, Proceed",
        cancelButtonClass: "btn-danger",
        cancelButtonText: "No, Cancel!",
        closeOnConfirm: true,
        showLoaderOnConfirm: true,
        closeOnCancel: true,
        customClass: "partial_refund_confirm_swal"
    },
    function(isConfirm) {                    

        if (isConfirm) {		
                        
            var partial_refund_amount = jQuery("#partial_refund_amount"+agency_payments_id).val();	
            
            $('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/accounts/ajax_partial_refund_agency_payments",
                data: { 	                        
                    agency_payments_id: agency_payments_id,
                    reference: reference,
                    amount: amount,
                    date: date,
                    partial_refund_amount: partial_refund_amount
                }
                
            }).done(function( ret ){

                $('#load-screen').hide();  
                location.reload();                   

            });	

        }

    });	
    jQuery(".partial_refund_confirm_swal:visible").find(".confirm").prop("disabled",true);
    jQuery(".partial_refund_confirm_swal:visible").find(".la-ball-fall").hide();	

    

});

// transfer invoice dropdown toggle
jQuery(document).on('change',".partial_refund_swal_div .partial_refund_amount",function(){

    var node = jQuery(this);
    var partial_refund_amount = parseFloat(node.val()); 
       
    var agen_pay_det_tbl = jQuery(".agen_pay_det_tbl:visible");
    var remaining = parseFloat(agen_pay_det_tbl.find('.remaining').val());
    
    // disable yes button if amount is 0
    if( partial_refund_amount <= remaining ){ // enable yes button   
        jQuery(".partial_refund_confirm_swal:visible").find(".confirm").prop("disabled",false);            
    }else{ // disable yes button    
        jQuery(".partial_refund_confirm_swal:visible").find(".confirm").prop("disabled",true);
        jQuery(".partial_refund_confirm_swal:visible").find(".la-ball-fall").hide();
    }
    

});


// remove agency rows
jQuery(".agen_pay_det_tbl").on("click",".remove_agen_pay_a_btn",function(){
    var remove_btn_dom = jQuery(this);
    var row = remove_btn_dom.parents("tr:first");
    row.remove();
});

// delete agency
jQuery("#agen_pay_det_fb_div").on("click",".del_agen_pay_a_btn",function(){

    var del_btn_dom = jQuery(this);
    var row = del_btn_dom.parents("tr:first");
    var ageny_pay_a_id = row.find(".ageny_pay_a_id").val();
    var agency_id = row.find(".ageny_pay_a_edit_agency").val(); 

    var agen_pay_det_tbl = del_btn_dom.parents(".agen_pay_det_tbl:first");
    var num_agency = agen_pay_det_tbl.find(".agen_pay_a_tbl .agency_pay_det_agency_row").length; 
    var agency_payments_id = agen_pay_det_tbl.find(".agency_payments_id").val();

   
    var amount = agen_pay_det_tbl.find(".amount").val();  
    var reference = agen_pay_det_tbl.find(".reference").val();
    var date = agen_pay_det_tbl.find(".date").val();

    // prevent removing all agency
    if( num_agency > 1 ){
        
        swal({
            title: "Warning!",
            text: "Are you sure you want to remove this agency?",
            type: "warning",						
            showCancelButton: true,
            confirmButtonClass: "btn-success",
            confirmButtonText: "Yes, Remove",
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
                    url: "/accounts/ajax_agency_payments_delete_agencies",
                    data: { 	
                        ageny_pay_a_id: ageny_pay_a_id,
                        agency_id: agency_id,
                        amount: amount,
                        reference: reference,
                        date: date,
                        agency_payments_id: agency_payments_id
                    }
                    
                }).done(function( ret ){

                    $('#load-screen').hide();
                    row.remove();        

                });	

            }

        });	
        

    }else{

        swal({
            title: "Warning!",
            text: 'Cannot remove last agency',
            type: "warning",						
            showConfirmButton: true,						
            confirmButtonClass: "btn-success"
        });	

    }

    

});

// edit agency payment, add agency
jQuery("#agen_pay_det_fb_div").on("click",".add_agency_icon",function(){

    var agen_pay_update_btn_dom = jQuery(this);
    var agen_pay_det_tbl = agen_pay_update_btn_dom.parents(".agen_pay_det_tbl:first");

    $('#load-screen').show();
    jQuery.ajax({
        type: "POST",
        url: "/accounts/ajax_get_active_or_all_agency",
        data: { 	
            show_inactive: 1
        }
        
    }).done(function( ret ){

        $('#load-screen').hide(); 
        var row = ''+
        '<tr>'+
            '<td class="add_agency_td">'+ 
                '<select class="form-control ageny_pay_a_new_agency">'+		
                    '<option value="">----</option>'+
                    ret+									                   
                '</select>'+
            '</td>'+    
            '<td class="remove_col">'+                                                                
                '<a href="javascript:void(0);" class="remove_link">'+
                    '<span class="fa fa-remove remove_agen_pay_a_btn"></span>'+
                '</a>'+
            '</td>'+
        '</tr>';

        agen_pay_det_tbl.find(".add_agency_tr").before(row);                 

    });	

          

});

// edit agency payment update
jQuery("#agen_pay_det_fb_div").on("click",".agen_pay_update_btn",function(){

    var agen_pay_update_btn_dom = jQuery(this);
    var agen_pay_det_tbl = agen_pay_update_btn_dom.parents(".agen_pay_det_tbl:first");

    var agency_payments_id = agen_pay_det_tbl.find(".agency_payments_id").val();
    var reference = agen_pay_det_tbl.find(".reference").val();
    var date = agen_pay_det_tbl.find(".date").val();
    var amount = agen_pay_det_tbl.find(".amount").val();
    var payment_type = agen_pay_det_tbl.find(".payment_type").val();  
    var allocated = agen_pay_det_tbl.find(".allocated").val(); 
    var bank_deposit = agen_pay_det_tbl.find(".bank_deposit").val(); 
    var remittance = agen_pay_det_tbl.find(".remittance").val();    
    var bank_deposit_is_edited = agen_pay_det_tbl.find(".bank_deposit_is_edited").val();
    var remittance_is_edited = agen_pay_det_tbl.find(".remittance_is_edited").val();

    // new agency
    var ageny_pay_a_new_agency = agen_pay_det_tbl.find(".ageny_pay_a_new_agency");
    var agency_new_arr = [];
    ageny_pay_a_new_agency.each(function(){
        var agency_id = jQuery(this).val();
        agency_new_arr.push(agency_id);
    });


    // existing agency
    var ageny_pay_a_id_dom = agen_pay_det_tbl.find(".ageny_pay_a_id");

    var ageny_pay_a_id_arr = [];
    var edit_agency_arr = [];
    var orig_agency_arr = [];
    var is_edited_arr = [];

    ageny_pay_a_id_dom.each(function(){

        var node = jQuery(this);    
        var parent_tr = node.parents(".agency_pay_det_agency_row_tr");

        var ageny_pay_a_id = node.val();
        var agency_id = parent_tr.find(".ageny_pay_a_edit_agency").val();
        var orig_agency = parent_tr.find(".ageny_pay_a_orig_agency").val();
        var is_edited = parent_tr.find(".ageny_pay_a_agency_is_edited").val();       

        ageny_pay_a_id_arr.push(ageny_pay_a_id);
        edit_agency_arr.push(agency_id);
        orig_agency_arr.push(orig_agency);
        is_edited_arr.push(is_edited);

    });


    var error = "";

    if( date == "" ){
        error += "Date is required.\n"
    }

    if( amount == "" ){
        error += "Amount is required.\n"
    }

    if( payment_type == "" ){
        error += "Payment Type is required.\n"
    }

    if( reference == "" ){
        error += "Reference is required.\n"
    }

    if( error != '' ){

        swal({
            title: "Warning!",
            text: error,
            type: "warning",						
            showConfirmButton: true,						
            confirmButtonClass: "btn-success",

        });	

    }else{

        $('#load-screen').show();
        jQuery.ajax({
            type: "POST",
            url: "/accounts/ajax_update_agency_payments",
            data: { 	
                agency_payments_id: agency_payments_id,

                date: date,
                amount: amount,
                payment_type: payment_type,
                reference: reference,
                allocated: allocated,    
                bank_deposit: bank_deposit,
                remittance: remittance,

                bank_deposit_is_edited: bank_deposit_is_edited,
                remittance_is_edited: remittance_is_edited,

                ageny_pay_a_id_arr: ageny_pay_a_id_arr,
                agency_new_arr: agency_new_arr,
                edit_agency_arr: edit_agency_arr,
                orig_agency_arr: orig_agency_arr,
                is_edited_arr: is_edited_arr               
            }
        }).done(function( ret ){

            $('#load-screen').hide();       
            swal({
                title: "Success!",
                text: "Update Successful",
                type: "success",
                confirmButtonClass: "btn-success",
                showConfirmButton: false,
                timer: 2000
            });
            setTimeout(function(){ location.reload(); }, 2000);	
                    

        });

    }       

});

// update is edited marker to 1 if edited
jQuery("#agen_pay_det_fb_div").on("change",".agen_pay_det_tbl .ageny_pay_a_edit_agency",function(){

    var agency_dp = jQuery(this);
    var parent_td = agency_dp.parents(".agency_pay_det_agency_row_tr");

    parent_td.find(".ageny_pay_a_agency_is_edited").val(1);


});

// update is bank deposited edited marker to 1 if edited
jQuery("#agen_pay_det_fb_div").on("change",".agen_pay_det_tbl .bank_deposit",function(){

    var agency_dp = jQuery(this);
    var parent_tbl = agency_dp.parents(".agen_pay_det_tbl");

    parent_tbl.find(".bank_deposit_is_edited").val(1);

});

// update is remittance edited marker to 1 if edited
jQuery("#agen_pay_det_fb_div").on("change",".agen_pay_det_tbl .remittance",function(){

    var agency_dp = jQuery(this);
    var parent_tbl = agency_dp.parents(".agen_pay_det_tbl");

    parent_tbl.find(".remittance_is_edited").val(1);

});



// edit button fancybox trigger
jQuery(".edit_btn").click(function(){

    var edit_btn_dom = jQuery(this);
    var row = edit_btn_dom.parents("tr:first");

    //row.find(".agen_pay_det_fb_link").click();

    var agen_pay_id = row.find(".main_agen_pay_id").val();

    $('#load-screen').show();
    jQuery.ajax({
        type: "POST",
        url: "/accounts/ajax_edit_agency_payments_lb",
        data: { 	
            agen_pay_id: agen_pay_id
        }
        
    }).done(function(ret){

        $('#load-screen').hide(); 
        jQuery("#agen_pay_det_fb_div").html(ret);     
        row.find(".agen_pay_det_fb_link").click();
        //location.reload();            

    });	 

});