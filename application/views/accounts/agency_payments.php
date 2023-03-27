<style>
.col-mdd-3{
	max-width:15.5%;
}
.agency_filter_tbl{
	width:auto;
	border:none;
}
.agency_filter_tbl tr td,
.add_payment_tbl tr td{
	border:none;
	padding-left:0;
	vertical-align: top;
}
#close_invoices_div{
	display: none;
}
.ci_address{
	width: 418px;
}
.ok_tick{
	display: none;
	position: relative;
	top: 15px;		
	left: 3px;
}
.inactive_agency_td{
	display: none;
}
.show_it{
	display: block;
}
.phrase_td{
	padding-left: 50px !important;
}
.about_td{
	padding-left: 18px !important;
}
.agency_ul li {
    border: none;
    margin: 3px 0;
}
#add_payment_tbl .agency{
    width: auto;    
}
.amount_td{
    width: 100px;
}
</style>


<div class="box-typical box-typical-padding">

	<?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => 'Accounts',
        'link' => "/accounts/view_statements"
    ),
    array(
        'title' => $title,
        'status' => 'active',
        'link' => $uri
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);
?>

	<header class="box-typical-header">
        <div class="box-typical box-typical-padding">
            <div class="col-md-12">
                <?php
                $form_attr = array(
                    'id' => 'jform'
                );
                echo form_open($uri,$form_attr);
                ?>                                
                    <table class="table agency_filter_tbl">	
                        <tr>
                            <td class="col1">Agency:<br />
                                <select name="agency[]" id="active_agency" class="form-control agency" multiple>										
                                    <?php 
                                    foreach($active_agency_filter->result_array() as $agency_filter_row){
                                    ?>
                                        <option <?php echo ( in_array($agency_filter_row['agency_id'],$this->input->get_post('agency')) ) ? 'selected' : ''; ?> value="<?php echo $agency_filter_row['agency_id'] ?>"><?php echo $agency_filter_row['agency_name'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </td>
                            <td class="col1">From<br />
                                <input type="text" data-allow-input="true" placeholder="ALL" name="from" class="form-control from date_from_to flatpickr" value="<?php echo $this->input->get_post('from') ?>" />
                            </td>
                            <td class="col1">To<br />
                                <input type="text" data-allow-input="true" placeholder="ALL" name="to" class="form-control to date_from_to flatpickr" value="<?php echo $this->input->get_post('to') ?>" />
                            </td>
                            <td class="col1 amount_td">Amount<br />
                                <input type="text" name="amount" class="form-control amount"  value="<?php echo $this->input->get_post('amount'); ?>" />
                            </td>
                            <td class="col1">Payment Type<br />
                                <select name="payment_type" id="payment_type" class="form-control">	
                                    <option value="">---</option>
                                    <?php			
                                    foreach( $pt_arr->result_array() as $pt ){                                        
                                    ?>
                                        <option value="<?php echo $pt['payment_type_id']; ?>"><?php echo $pt['pt_name'] ?></option>
                                    <?php
                                    }										
                                    ?>																					
                                </select>
                            </td>																	
                            <td class="col1">Reference<br />
                                <input type="text" name="reference" class="form-control" placeholder="Text" value="<?php echo ($this->input->get_post('reference')) ? $this->input->get_post('reference') :'' ?>">
                            </td>

                            <td class="col1">&nbsp;<br />
                                <input class="btn" type="submit" name="btn_search" value="Search">
                            </td>

                            <td class="col1 text-center">
                                Include Closed Payments<br />
                                <div class="checkbox-toggle">
                                    <input type="checkbox" id="include_closed_pay" name="include_closed_pay" value="1" <?php echo ( $include_closed_pay == 1 )?'checked="checked"':''; ?> />
                                    <label for="include_closed_pay"></label>
                                </div>
                            </td>


                            <td class="col1">
                                &nbsp;<br />
                                <button class="btn btn-danger" type="button" id="add_payments_btn">ADD Payment</button>
                            </td>
                           
                        </tr>	
                    </table>
                </form>
            </div>                            
           

        </div>
    </header>

	<?php 	$this->load->view('accounts/agency_payment_list.php'); ?>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="add_payment_fb_link" class="fb_trigger jfancybox" data-src="#add_payment_fb">Trigger the fancybox</a>							
<div id="add_payment_fb" class="fancybox" style="display:none;" >

	<h4>Add Payment</h4>
	<table id="add_payment_tbl" class="table add_payment_tbl">					
        <tbody>
            <tr>            
                <td colspan="3">Reference<br />
                    <input type="text" class="form-control reference" placeholder="Text" value="<?php echo ($this->input->get_post('search')) ? $this->input->get_post('search') :'' ?>">
                </td>
            </tr>	                        
            <tr>
                <td colspan="3"><label id="add_agencies_lbl">Agencies</label><br />
                    <select id="add_pay_agency" class="form-control add_pay_agency agency" multiple>										
                        <?php 
                        foreach($active_agency_filter->result_array() as $agency_filter_row){
                        ?>
                            <option <?php echo ( in_array($agency_filter_row['agency_id'],$this->input->get_post('agency')) ) ? 'selected' : ''; ?> value="<?php echo $agency_filter_row['agency_id'] ?>"><?php echo $agency_filter_row['agency_name'] ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </td>                           
            </tr>
            <tr>                
                <td colspan="3">Show inactive Agencies<br />
                    <div class="checkbox">
                        <input type="checkbox" name="show_inactive_agency" id="show_inactive_agency_chk" />
                        <label for="show_inactive_agency_chk">&nbsp;</label>
                    </div>
                </td>  
            </tr>
            <tr>            
                <td class="flatpickr_td">Date<br />
                    <input type="text" data-allow-input="true" class="form-control jflatpickr date" value="<?php echo $this->input->get_post('date_from_filter') ?>" />
                </td>
                <td class="amount_td">Amount<br />
                    <input type="text" class="form-control amount"  value="<?php echo $this->input->get_post('date_to_filter') ?>" />
                </td>
                <td>Payment Type<br />
                    <select class="form-control payment_type">	
                        <option value="">---</option>
                        <?php			
                        foreach( $pt_arr->result_array() as $pt ){
                            $default_selected_dr = ($pt['payment_type_id']==5) ? "selected='selected'" : NULL;
                        ?>
                            <option <?php echo $default_selected_dr; ?> value="<?php echo $pt['payment_type_id']; ?>"><?php echo $pt['pt_name'] ?></option>
                        <?php
                        }										
                        ?>									                                  
                    </select>
                </td>	
            </tr>        
            <tr>
                <td>
                    Bank Deposit<br />
                    <select class="form-control bank_deposit">
                        <option value="0">No</option>
                        <option value="1" selected="selected">Yes</option>                    
                    </select>	
                </td>

                <td>
                    Remittance<br />
                    <select class="form-control remittance">
                        <option value="0">No</option>
                        <option value="1">Yes</option>  
                        <option value="2">Not Needed</option>                  
                    </select>
                </td>            
                
                <td class="col1 text-right">
                    &nbsp;<br />
                    <button class="btn btn-primary" type="button" id="add_payment_btn">Save</button>
                </td>							
            </tr>
        </tbody>
    </table>             

</div>
<!-- Fancybox END -->


<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
    This page is to record all payments into SATS. We record if we payment is visible in bank account OR we have received a remittance
	</p>
    <pre>
<code>SELECT `agen_pay`.`agency_payments_id`, `agen_pay`.`date`, `agen_pay`.`amount`, `agen_pay`.`reference`, `agen_pay`.`payment_type`, `agen_pay`.`allocated`, `agen_pay`.`remaining`, `agen_pay`.`bank_deposit`, `agen_pay`.`remittance`, `pt`.`payment_type_id`, `pt`.`pt_name`
FROM `agency_payments` AS `agen_pay`
LEFT JOIN `payment_types` AS `pt` ON agen_pay.`payment_type` = pt.`payment_type_id`
INNER JOIN `agency_payments_agencies` AS `agen_pay_a` ON agen_pay.`agency_payments_id` = agen_pay_a.`agency_payments_id`
LEFT JOIN `agency` AS `a` ON agen_pay_a.`agency_id` = a.`agency_id`
WHERE `agen_pay`.`remaining` >0
GROUP BY `agen_pay_a`.`agency_payments_id`
ORDER BY `agen_pay`.`date` DESC
LIMIT 50</code>
    </pre>

</div>
<!-- Fancybox END -->



<script>
jQuery(document).ready(function(){

    //init datepicker
    jQuery('.jflatpickr').flatpickr({
        dateFormat: "d/m/Y",
        maxDate: "today"
    });

    jQuery('.jfancybox').fancybox({
        // Options will go here
        keyboard: false
    });

    // open edit agency payments lightbox
    <?php 
    if( $this->input->get_post('agency_payments_id') > 0 && $this->input->get_post('open_edit_lb') == 1 ){ ?>
        jQuery(".agency_payments_id[value='<?php echo $this->input->get_post('agency_payments_id'); ?>']").parents("td.action_td").find(".edit_btn").click();
    <?php
    }
    ?>
    

    // add agency payment, show inactive checkbox script
    jQuery("#show_inactive_agency_chk").change(function(){

        var show_inactive = ( jQuery(this).prop("checked") == true )?1:0; 

        var add_agencies_lbl_txt = ( show_inactive == 1 )?'Agencies (Active & Inactive)':'Agencies';

        $('#load-screen').show();
        jQuery.ajax({
            type: "POST",
            url: "/accounts/ajax_get_active_or_all_agency",
            data: { 	
                show_inactive: show_inactive
            }
            
        }).done(function( ret ){

            $('#load-screen').hide(); 
            jQuery("#add_agencies_lbl").html(add_agencies_lbl_txt);
            jQuery("#add_pay_agency").html(ret);                   

        });	

    });

    // add payment lightbox
    jQuery("#add_payments_btn").click(function(){

        jQuery("#add_payment_fb_link").click();

    });


    jQuery("#add_payment_tbl .payment_type").change(function(){

        var node = jQuery(this);
        var payment_type = node.val();

        // if Bpay or EFT
        if( payment_type == 1 || payment_type == 5 ){
            jQuery("#add_payment_tbl .bank_deposit").val(1); // select bank deposit yes
        }else{
            jQuery("#add_payment_tbl .bank_deposit").val(0); // select bank deposit no
        }

    });
 

    // limit to only pick dates starting from financial year
	jQuery(".date_from_to").prop("readonly",false);
    jQuery(".date_from_to").removeAttr("readonly");


    jQuery("#add_payment_btn").click(function(){

        var table_dom = jQuery("#add_payment_tbl");

        var add_pay_agency = jQuery("#add_pay_agency").val();

        var date = table_dom.find(".date").val();
        var amount = table_dom.find(".amount").val();
        var payment_type = table_dom.find(".payment_type").val();
        var reference = table_dom.find(".reference").val();
        var bank_deposit = table_dom.find(".bank_deposit").val();
        var remittance = table_dom.find(".remittance").val();

        var error = "";

        if( add_pay_agency.length == 0 ){
            error += "Agency is required.\n"
        }

        if( date == "" ){
            error += "Date is required.\n"
        }else{
        
            console.log("date: "+date);
            var date_split = date.split("/");

            // accepts ISO format same as database for Date object
            var date_fin = date_split[2]+"-"+date_split[1]+"-"+date_split[0]; 
            console.log("date_fin: "+date_fin);

            var date_input = new Date(date_fin);
            console.log("d1: "+date_input.toDateString());

            var date_now = new Date(); // now
            console.log("d2: "+date_now.toDateString());

            if( date_input > date_now ){
                error += "Cannot enter a future date\n";
            }

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
                url: "/accounts/ajax_save_agency_payments",
                data: { 	
                    multi_agency: add_pay_agency,

                    date: date,
                    amount: amount,
                    payment_type: payment_type,
                    reference: reference,
                    bank_deposit: bank_deposit,
                    remittance: remittance
                }
            }).done(function( ret ){

                $('#load-screen').hide();            
                swal({
                    title: "Success!",
                    text: "Payment Success",
                    type: "success",
                    confirmButtonClass: "btn-success",
                    showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                    timer: <?php echo $this->config->item('timer') ?>
                });
                setTimeout(function(){ window.location='<?php echo $uri; ?>'; }, <?php echo $this->config->item('timer') ?>);	            

            });

        }
        

    });
    

	

});
</script>
