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
		<div class="body-typical-body" style="padding-top:25px;">


        <?php 
        if( $attachment_error != '' ){ ?>
            <div class="alert alert-danger">
                <?php echo $attachment_error; ?>
            </div>
        <?php
        }	
        ?>


		<?php
            $form_attr = array(
                'id' => 'jform'
            );
            echo form_open_multipart('/email/send_email_script',$form_attr);
		?>

        <div class="form-group row">

        

            <div class="col-sm-6" id="sms_temp_left_panel">

                <div class="form-group row">
                    <label class="col-sm-3 form-control-label">From</label>
                    <div class="col-sm-9">
                        <p class="form-control-static">
                            <input type="text" class="form-control" id="from" name="from" value="<?php echo $this->config->item('sats_info_email'); ?>" />
                        </p>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 form-control-label">To</label>
                    <div class="col-sm-9">
                        <p class="form-control-static">
                            <input type="text" class="form-control" id="to" name="to" />
                        </p>
                    </div>
                </div>
            

                <div class="form-group row">
                    <label class="col-sm-3 form-control-label">CC</label>
                    <div class="col-sm-9">
                        <p class="form-control-static">
                            <input type="text" class="form-control" id="cc" name="cc" />
                        </p>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 form-control-label">Category</label>
                    <div class="col-sm-9">
                        <p class="form-control-static">
                            <select name="category" id="category" class="form-control">
                                <option value="">--- Select ---</option>
                                <?php foreach( $email_category as $email_temp_row ): ?>
                                    <option value="<?php echo $email_temp_row->email_templates_type_id; ?>"><?php echo $email_temp_row->name; ?></option>
                                <?php endforeach; ?>  
                            </select>
                        </p>
                    </div>
                </div>    
                
                <div class="form-group row">
                    <label class="col-sm-3 form-control-label">Template</label>
                    <div class="col-sm-9">
                        <p class="form-control-static">
                            <select name="email_type" id="email_type" class="form-control">
                                <option value="">--- Select ---</option>
                                <?php
                                foreach( $email_temp_sql->result() as $email_temp_row ){ ?>
                                    <option value="<?php echo $email_temp_row->email_templates_id; ?>"><?php echo $email_temp_row->template_name; ?></option>
                                <?php
                                }
                                ?>                       
                            </select>
                        </p>
                    </div>
                </div>  

                <div class="form-group row">
                    <label class="col-sm-3 form-control-label">Subject</label>
                    <div class="col-sm-9">
                        <p class="form-control-static">
                            <input type="text" class="form-control" id="subject" name="subject" />
                        </p>
                    </div>
                </div>

                
                <div class="form-group row">
                    <label class="col-sm-3 form-control-label">Body</label>
                    <div class="col-sm-9">
                        <p class="form-control-static">
                            <textarea name="body" id="body" rows="10" class="form-control" data-validation="[NOTEMPTY]"></textarea>
                        </p>
                    </div>
                </div>   

                <div class="form-group row">

                    <label class="col-sm-3 form-control-label">Attachment <i class="fa fa-paperclip inner_icon ml-2" id="attachment_icon"></i></label>

                    <div class="col-sm-9">                        

                        <div id="attachment_div">
                            <div class="d-flex flex-row mb-3">
                                <input type="file" class="form-control" id="custom_attach" name="custom_attach" />
                            </div>

                            <div class="d-flex flex-row">
                                <div class="checkbox mr-2">
                                    <input type="checkbox" id="attach_invoice" name="attach_invoice" value="1" />
                                    <label for="attach_invoice">Invoice</label>
                                </div>
                                <div class="checkbox mr-2">
                                    <input type="checkbox" id="attach_cert" name="attach_cert" value="1" />
                                    <label for="attach_cert">Certificate</label>
                                </div>
                                <div class="checkbox mr-2">
                                    <input type="checkbox" id="attach_combined" name="attach_combined" value="1" />
                                    <label for="attach_combined">Combined</label>
                                </div>

                                <?php
                                if( $job_row->p_state == 'QLD' ){ ?>

                                    <div class="checkbox mr-2">
                                        <input type="checkbox" id="brooks_quote" name="brooks_quote" value="1" />
                                        <label for="brooks_quote">Brooks Quote</label>
                                    </div>  

                                    <div class="checkbox mr-2">
                                        <input type="checkbox" id="cavius_quote" name="cavius_quote" value="1" />
                                        <label for="cavius_quote">Cavius Quote</label>
                                    </div>

                                    <div class="checkbox mr-2">
                                        <input type="checkbox" id="combined_quote" name="combined_quote" value="1" />
                                        <label for="combined_quote">Combined Quote</label>
                                    </div>

                                <?php    
                                }
                                ?>                                 

                                <div class="checkbox mr-2" id="attach_mark_as_copy_div">
                                    <input type="checkbox" id="attach_mark_as_copy" name="attach_mark_as_copy" value="1" />
                                    <label for="attach_mark_as_copy">Mark as Copy</label>
                                </div>
                            </div>
                        </div>

                       
                    </div>

                </div>     
            

                <div class="form-group row">

                    <label class="col-sm-3 form-control-label">&nbsp;</label>
                    <div class="col-sm-9">
                        <p class="form-control-static">                                                       
                            <button type="button" class="btn btn-danger" id="clear_btn">Clear</button>
                            <button type="button" class="btn btn-info" id="preview_btn">Preview</button>
                            <input type="hidden" name="job_id" value="<?php echo $this->input->get_post('job_id'); ?>" />
                            <input type="hidden" id="sent_to_tenant_ids" name="sent_to_tenant_ids" />

                            <button type="submit" class="btn float-right" id="send_btn">Send</button> 
                        </p>
                    </div>
                    
                </div>     

            </div>

            <div class="col-sm-6">
            
                <!-- TENANT TAGS -->
                <div class="form-group row">  

                    <div class="col-sm-5 tags_div">

                        <label class="text-center mb-2">TENANTS:</label>

                        <?php
                        if (count($tenants_sql) > 0) {
                            foreach ( $tenants_sql->result() as $index => $tenant_row ) {
                            $tenant = "{$tenant_row->tenant_firstname} {$tenant_row->tenant_lastname}";
                        ?>
                            <button type="button"
                                class="btn tag_btn <?php echo ( $job_row->booked_with == $tenant_row->tenant_firstname )?'btn-warning':null; ?>" 
                                data-tag_val="{tenant_<?php echo ($index+1); ?>}"                             
                                data-tenant_id="<?php echo $tenant_row->property_tenant_id; ?>"
                                data-email="<?php echo $tenant_row->tenant_email; ?>"                                    
                                data-tenant_name="<?php echo $tenant_row->tenant_firstname; ?>"
                            >
                                <?php echo $tenant; ?>
                            </button>                               
                        <?php
                            }
                        }
                        ?>	

                    </div>

                </div>

                <!-- AGENCY EMAILS -->
                <div class="form-group row">  

                    <div class="col-sm-5 tags_div">
                        <label class="text-center mb-2">AGENCY EMAILS:</label>
                        <button type="button" 
                            class="btn tag_btn" 
                            data-email="<?php echo $agency_emails_imp; ?>"
                            data-tag_val=""
                        >
                            Agency (General)
                        </button>
                        <button type="button" 
                            class="btn tag_btn" 
                            data-email="<?php echo $account_emails_imp; ?>"
                            data-tag_val=""
                        >
                            Agency (Accounts)
                        </button>	

                        <?php if($landlord_emai!=""){ ?>
                        <button type="button" 
                            class="btn tag_btn" 
                            data-email="<?php echo $landlord_emai; ?>"
                            data-tag_val=""
                        >
                            Landlord
                        </button>	
                        <?php } ?>

                    </div>

                </div>

                
                <!-- TAGS -->              
                <div class="form-group row">  

                    <div class="col-sm-5 tags_div">
                        <label class="text-center mb-2">TAGS:</label>
                        <?php
                        if (count($template_tags_sql) > 0) {
                            foreach ( $template_tags_sql->result() as $template_tags_row ) {
                        ?>
                            <button type="button" class="btn tag_btn" 
                                data-tag_val="<?php echo $template_tags_row->tag; ?>"
                                <?php
                                if( $template_tags_row->tag == '{landlord_email}' ){ ?>
                                    data-email="<?php echo $job_row->landlord_email; ?>"
                                <?php
                                }
                                ?>                                
                            >
                                <?php echo $template_tags_row->tag_name; ?>
                            </button>                               
                        <?php
                            }
                        }
                        ?>	

                    </div>

                </div>

                <input type="hidden" id="email_to_target" value="to" />
               

            </div>


                  

  
		</div>

        <?php
            echo form_close();
            ?> 


		</div>
	</section>

</div>



<!-- Fancybox Start -->

<!-- TEMPLATE PREVIEW -->
<a href="javascript:;" id="preview_template_fb_link" class="fb_trigger" data-fancybox data-src="#preview_template_fb" data-options='{"touch" : false}'>Trigger the fancybox</a>							
<div id="preview_template_fb" class="fancybox" style="display:none;" >

	<h4>Preview</h4>
    
	<table class="table main-table">
        <tr>
            <th>Subject:</th>
            <td id="prev_subject"></td>
        </tr>
        <tr>
            <th>Body:</th>
            <td id="prev_body"></td>
        </tr>
    </table>

</div>

<!-- ABOUT PAGE -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
        This page is used to email tenants.<br />
        Bracketed tags, example [date] and [time] must be manually edited.
	</p>
    <ul>
		<li><span class="btn-warning">Orange</span> = Tenant is the tenant that is booked with</li>
        <li></li>
	</ul>

</div>
<!-- Fancybox END -->

<style>
.fancybox-content {
	width: 30%;
}
.tags_div button{
	margin-bottom: 5px;
	width: 100%;
}
#attachment_div,
#attach_mark_as_copy_div{
    display: none;
}
#attachment_icon{
    color: #00a8ff;;
    cursor: pointer;
}
.colorItRed{
	color: red;
}
#prev_body{
    line-height: 22px;
}
</style>
<script>
jQuery(document).ready(function(){

    <?php 
    // success message
    if( $this->session->flashdata('send_email_success') == 1 ){ ?>
        swal({
            title: "Success!",
            text: "Email sent",
            type: "success",
            confirmButtonClass: "btn-success"
        });
    <?php 
    }
    ?>  

    // email field to target from email tags
    jQuery("#to,#cc,#body").click(function(){
		
		var id = jQuery(this).attr("id");
		jQuery("#email_to_target").val(id);			
		
	});

    // display email tags to field
	jQuery(".tag_btn").click(function(){
		
        var tag_btn_dom = jQuery(this);
        var target = jQuery("#email_to_target").val();
	
        if( target == 'body' ){ // insert tags to body

            var tag = tag_btn_dom.attr("data-tag_val");	
            var target_txtarea = jQuery("textarea#body");
            
            typeInTextarea(jQuery(target_txtarea), tag);

        }else{ // insert email into TO field and CC

            var email = tag_btn_dom.attr("data-email");		  

            if( email != '' ){
                jQuery("#"+target).val(email);			
            }else{
                jQuery("#"+target).val("");		
            }	

        }						
		
	});


    // load email template
	jQuery("#email_type").change(function(){
		
		var template_id = jQuery(this).val();

        if( template_id > 0 ){

            jQuery("#load-screen").show();
            jQuery.ajax({
                type: "POST",
                url: "/email/get_email_template",
                data: { 
                    template_id: template_id
                },
                dataType: 'json'
            }).done(function( ret ){
                jQuery("#load-screen").hide();
                
                // set from field to accounts email
                var subject_str = ret.template_name;
                var string_to_find = 'Accounts -';   
                if(subject_str.indexOf(string_to_find) != -1){				
                    jQuery("#from").val('<?php echo $this->config->item('sats_accounts_email'); ?>');
                }else if(ret.temp_type == 5){	

                    <?php if( $this->config->item('country') == 1 ){ // AU ?>
                        jQuery("#from").val('operations@sats.com.au');
                    <?php
                    }else if( $this->config->item('country') == 2 ){ // NZ ?>
                        jQuery("#from").val('<?php echo $this->config->item('sats_info_email'); ?>');
                    <?php
                    } ?>

                }else{
                    jQuery("#from").val('<?php echo $this->config->item('sats_info_email'); ?>'); //Default Email
                }
                jQuery("#subject").val(ret.subject);
                jQuery("#body").val(ret.body);
                //jQuery("#preview_btn").show();
            });	
        }else{
            jQuery("#subject").val('');
            jQuery("#body").val('');
            //jQuery("#preview_btn").hide();

        }				
		
	});

    // Preview Email Template( Parse Tags )
    jQuery("#preview_btn").click(function(){
				
        var job_id = <?php echo $this->input->get_post('job_id'); ?>;
        var subject = jQuery("#subject").val();
        var body = jQuery("#body").val();            
        
        if( body !='' ){

            var body2 = body.replace(/(?:\r\n|\r|\n)/g, '<br />'); // convert new lines to HTML <br />

            // parse body tags
            jQuery("#load-screen").show();
            jQuery.ajax({			
                type: "POST",			
                url: "/email/preview_email_template",			
                data: {
                    job_id: job_id,
                    subject: subject,
                    body: body2                   
                },
                dataType: 'json'		
            }).done(function( ret ) {	
            
                jQuery("#load-screen").hide(); 
                
                // load template data
                jQuery("#prev_subject").html(ret.subject);
                jQuery("#prev_body").html(ret.body);

                // open lightbox
                jQuery.fancybox.open({
                    src  : '#preview_template_fb'
                });
                
            });

        }
                    
    });

    // show attachment section
    jQuery("#attachment_icon").click(function(){
    
        var visible_attachment_div = jQuery("#attachment_div:visible");

        if( visible_attachment_div.length > 0 ){ // already shown
            jQuery("#attachment_div").hide();
        }else{
            jQuery("#attachment_div").show();
        }        

    });

    <?php
    // tenant autofill
    if( $this->input->get_post('tenant_id') > 0 ){ ?>        
        jQuery(".tag_btn[data-tenant_id='<?php echo $this->input->get_post('tenant_id'); ?>']").click();
    <?php
    }   
    ?>   

    // clear
    jQuery("#clear_btn").click(function(){

        jQuery("#subject").val('');
        jQuery("#body").val('');      

    });


    // send email templates
	jQuery("#jform").submit(function(){
		
		var from = jQuery("#from").val();
		var to = jQuery("#to").val();
        var subject = jQuery("#subject").val();
		var body = jQuery("#body").val();
		var error = '';
		
		if( from == "" ){
			error += "Email From is required\n";
		}
		
		if( to == "" ){
			error += "Email To is required\n";
		}

        if( subject == "" ){
			error += "Subject is required\n";
		}
		
		if( body == "" ){
			error += "Email Body is required\n";
		}
		
		
		
		if( error != "" ){
			
            swal('',error,'error');
			return false;

		}else{
			return true;						
		}
					
	});

    
    // only show mark as copy checkbox to invoice and certficate checkboxes
    jQuery("#attach_invoice, #attach_combined").change(function(){

        if( jQuery("#attach_invoice:checked, #attach_combined:checked").length > 0 ){
            jQuery("#attach_mark_as_copy_div").show();
        }else{
            jQuery("#attach_mark_as_copy_div").hide();
        }

    });

    // Filter sms template based on category
    jQuery("#category").on('change', function() {
        var templates_type_id = $(this).val();
        var job_id = <?php echo $this->input->get_post('job_id'); ?>;
        $("#load-screen").show();

        $.ajax({
            type: "POST",
            url: "<?php echo site_url(); ?>ajax/emails_ajax/auto_populate_emails_template",
            data: {
                templates_type_id: templates_type_id,
                job_id: job_id
            },
            dataType:'json',
            cache: false,
        }).done(function( response ){
            var email_templates_id = "";
            var data = JSON.parse(JSON.stringify(response));

            $("#load-screen").hide();
            
            if (data.success == true) {
                email_templates_id = "<option value=''>--- Select ---</option>";
                $.each(response.data, function (key, value) {
                    email_templates_id += '<option value="' + value.email_templates_id + '">' + value.template_name + '</option>';
                });
                $("#email_type").html(email_templates_id);
            } else {
                email_templates_id = "<option value=''>--- Select ---</option>";
                $.each(response.data, function (key, value) {
                    email_templates_id += '<option value="' + value.email_templates_id + '">' + value.template_name + '</option>';
                });
                $("#email_type").html(email_templates_id);
            }
            
        })
    });
	

});
</script>