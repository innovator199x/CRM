<div class="box-typical box-typical-padding">
	<?php 
		// breadcrumbs template
		$bc_items = array(
            array(
				'title' => $title,
				'status' => 'active',
				'link' => "/sms/send/?job_id=".$job_id
			)
		);
		$bc_data['bc_items'] = $bc_items;
        $this->load->view('templates/breadcrumbs', $bc_data);
        
        // get job data
        if( $job_id > 0 ){
            $job_row = $job_sql->row();
        }        
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
            echo form_open("/sms/send/?job_id={$job_id}",$form_attr);
		?>

        <div class="form-group row">

        

            <div class="col-sm-6" id="sms_temp_left_panel">

                <div class="form-group row">
                    <label class="col-sm-5 form-control-label">Send To</label>
                    <div class="col-sm-7">
                        <p class="form-control-static">
                            <input type="text" class="form-control" id="send_to" name="send_to" data-validation="[NOTEMPTY]" />
                        </p>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-5 form-control-label">Category</label>
                    <div class="col-sm-7">
                        <p class="form-control-static">
                            <select name="category" id="category" class="form-control" <?php echo ( $job_id > 0 )?'data-validation="[NOTEMPTY]"':null; ?>>
                                <option value="">--- Select ---</option>
                                <?php foreach( $sms_category as $row ): ?>
                                    <option value="<?php echo $row->category; ?>"><?php echo $row->category; ?></option>
                                <?php endforeach; ?>                       
                            </select>
                        </p>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-5 form-control-label">Template</label>
                    <div class="col-sm-7">
                        <p class="form-control-static">
                            <select name="template" id="template" class="form-control" <?php echo ( $job_id > 0 )?'data-validation="[NOTEMPTY]"':null; ?>>
                                <option value="">--- Select ---</option>
                                <?php foreach( $sms_templates_sql->result() as $row ): ?>
                                    <option value="<?php echo $row->sms_api_type_id; ?>"><?php echo $row->type_name; ?></option>
                                <?php endforeach; ?>                       
                            </select>
                        </p>
                    </div>
                </div>

                
                <div class="form-group row">
                    <label class="col-sm-5 form-control-label">Body</label>
                    <div class="col-sm-7">
                        <p class="form-control-static">
                            <textarea name="body" id="body" rows="10" class="form-control" data-validation="[NOTEMPTY]"></textarea>
                        </p>
                    </div>
                </div>        
            

                <div class="form-group row">

                    <label class="col-sm-5 form-control-label">&nbsp;</label>
                    <div class="col-sm-7">
                        <p class="form-control-static">                                                       
                            <button type="button" class="btn btn-danger" id="clear_btn">Clear</button>

                            <button type="button" class="btn btn-info" id="preview_btn">Preview</button>                            

                            <input type="hidden" name="job_id" value="<?php echo $job_id; ?>" />
                            <input type="hidden" id="sent_to_tenant_ids" name="sent_to_tenant_ids" />

                            <button type="submit" class="btn float-right" id="send_btn">Send</button> 
                        </p>
                    </div>
                    
                </div>

                <div class="form-group row">

                    <label class="col-sm-5 form-control-label">&nbsp;</label>
                    <div class="col-sm-7">

                        <table class="table">
                            <?php
                            if( $job_id > 0 ){ ?>
                                 <tr>
                                    <td>Booked With: </td><td><?php echo $job_row->booked_with; ?></td>
                                </tr>
                                <tr>
                                    <td>Is Private Agency?: </td><td><?php echo ( $this->system_model->getAgencyPrivateFranchiseGroups($job_row->franchise_groups_id) == true )?'Yes':'No' ?></td>
                                </tr>
                                <tr>
                                    <td>Is 240v Rebook?: </td><td><?php echo ( $job_row->job_type == '240v Rebook' )?'Yes':'No' ?></td>
                                </tr> 
                            <?php
                            }
                            ?>                                 
                            <tr>
                                <td>SMS Count: </td><td><span id="sms_count"></span></td>
                            </tr>
                            <tr>
                                <td>SMS Cost: </td><td><span id="sms_cost"></span></td>
                            </tr>                                                                                   
                        </table>
                    </div>
                    
                </div>

            </div>

            <div class="col-sm-6">

                <?php
                if( $job_id > 0 ){ ?>

                    <!-- TENANT TAGS -->
                    <div class="form-group row">
                        
                        <div id="tenants_btn_div" class="col-sm-4 tags_div">
                            <label class="text-center">TO:</label>
                            <?php
                            foreach( $tenants_sql->result() as $row ){ ?>

                                <button type="button" 
                                <?php echo ( $row->tenant_mobile == '' )?'disabled="disabled"':null; ?> 
                                class="btn tag_btn <?php echo ( $job_row->booked_with == $row->tenant_firstname )?'btn-warning':null; ?>" 
                                data-tenant_id="<?php echo $row->property_tenant_id; ?>"
                                data-tenant_mobile="<?php echo $row->tenant_mobile; ?>"                                    
                                data-tenant_name="<?php echo $row->tenant_firstname; ?>"
                                >
                                    <?php echo $row->tenant_firstname; ?><?php echo ( $row->tenant_mobile !='' )?" ({$row->tenant_mobile})":null; ?>
                                </button>
                                
                            <?php
                            }
                            ?>	
                        </div>
                    </div>

                <?php
                }

                if( $job_id > 0 ){ ?>

                    <!-- TAGS -->
                    <div class="form-group row">  

                        <div id="tags_btn_div" class="col-sm-4 tags_div">
                            <label class="text-center">TAGS:</label>
                            <button type="button" class="btn tag_btn" data-tag_val="{agency_name}">Agency Name</button>
                            <button type="button" class="btn tag_btn" data-tag_val="{tenant_name}">Tenant Name</button>
                            <button type="button" class="btn tag_btn" data-tag_val="{p_address}">Address</button>	
                            <button type="button" class="btn tag_btn" data-tag_val="{job_date}">Job date</button>
                            <button type="button" class="btn tag_btn" data-tag_val="{serv_name}">Service Type</button>
                            <button type="button" class="btn tag_btn" data-tag_val="{tenant_number}">SATS Tenant Line</button>
                        <!--<button type="button" class="btn tag_btn" data-tag_val="{your_agency}">Your agency</button>-->
                            <button type="button" class="btn tag_btn" data-tag_val="{en_link}">EN link</button>
                            <button type="button" class="btn tag_btn" data-tag_val="{time_of_day}">Time of day</button>
                            <button type="button" class="btn tag_btn" data-tag_val="{booked_with}">Booked With</button>
                            <button type="button" class="btn tag_btn" data-tag_val="{sats_domain}">SATS Domain</button>
                            <button type="button" class="btn tag_btn" data-tag_val="{sats_google_review}">SATS Google Review</button>
                        </div>

                    </div>

                <?php
                }
                ?>                

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
    
	<p id="template_preview_fb"></p>

</div>

<!-- ABOUT PAGE -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
        This page is used to SMS tenants.<br />
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
#preview_btn{
    display: none;
}
</style>
<script>
jQuery(document).ready(function(){

    <?php 
    if( $this->session->flashdata('sms_sent') &&  $this->session->flashdata('sms_sent') == 1 ){ ?>
        swal({
            title: "Success!",
            text: "SMS sent",
            type: "success",
            confirmButtonClass: "btn-success"
        });
    <?php 
    }
    ?>     


    // tenants tag
    jQuery("#tenants_btn_div .tag_btn").on("click", function() {

        var obj = jQuery(this);
        var tenant_id = obj.attr("data-tenant_id");	
        var tenant_name = obj.attr("data-tenant_name");	
        var current_tenant_name = jQuery("input#send_to").val();
        var current_tenant_id = jQuery("input#sent_to_tenant_ids").val();

        if( current_tenant_name != '' ){
            jQuery("input#send_to").val(current_tenant_name+'; '+tenant_name);
            jQuery("input#sent_to_tenant_ids").val(current_tenant_id+':'+tenant_id);
        }else{
            jQuery("input#send_to").val(tenant_name);
            jQuery("input#sent_to_tenant_ids").val(+tenant_id);
        }
        

    });

    // tags
    jQuery("#tags_btn_div .tag_btn").on("click", function() {

        var obj = jQuery(this);
        var tag = obj.attr("data-tag_val");	
        var target = jQuery("textarea#body");

        typeInTextarea(jQuery(target), tag);

    });

    // clear
    jQuery("#clear_btn").click(function(){

        jQuery("#send_to").val('');
        jQuery("#template").val('');
        jQuery("#body").html('');
        jQuery("#sent_to_tenant_ids").val('');

    });

    // load template
    jQuery('#template').change(function(){
            
        var template  = jQuery(this).val();
        var job_id = '<?php echo $job_id; ?>';

        if( template != '' ){
            
            jQuery("#load-screen").show();        
            jQuery.ajax({
                type: "POST",
                url: "/sms/get_template",
                data: { 
                    template: template,
                    job_id: job_id
                },
                dataType: 'json'
            }).done(function( ret ){

                jQuery("#sms_count").html(ret.sms_count);
                jQuery("#sms_cost").html(ret.sms_cost);
                        
                jQuery("textarea#body").html(ret.template_body);
                jQuery("#preview_btn").show();
                jQuery("#load-screen").hide();
                
            });   

        }else{
            jQuery("#preview_btn").hide();
        }          
                
    });

    jQuery('#body').change(function(){
            
        var job_id  = '<?php echo $job_id; ?>';
        var unparsed_template  = jQuery("textarea#body").val();
    
        jQuery.ajax({
            type: "POST",
            url: "/sms/parse_tags",
            data: { 
                job_id: job_id,
                unparsed_template: unparsed_template
            },
            dataType: 'json'
        }).done(function( ret ){
        
            jQuery("#sms_count").html(ret.sms_count);
            jQuery("#sms_cost").html(ret.sms_cost);
            
        });       
                
    });
    

    // parse template tags
    jQuery('#preview_btn').click(function(){
            
        var job_id  = '<?php echo $job_id; ?>';
        var unparsed_template  = jQuery("textarea#body").val();
        var tenant_id = $("#sent_to_tenant_ids").val();

        jQuery("#load-screen").show();        
        jQuery.ajax({
            type: "POST",
            url: "/sms/parse_tags",
            data: { 
                job_id: job_id,
                unparsed_template: unparsed_template,
                tenant_id: tenant_id
            },
            dataType: 'json'
        }).done(function( ret ){
        
            jQuery("#template_preview_fb").html(ret.parsed_template);               
            jQuery("#preview_template_fb_link").click();
            jQuery("#load-screen").hide();      

            
                jQuery("#template_preview_fb").html(ret.parsed_template);               
                jQuery("#preview_template_fb_link").click();
                jQuery("#load-screen").hide();      
                
        }); 
    });  


    // jquery form validation
	jQuery('#jform').validate({
		submit: {
			settings: {
				inputContainer: '#sms_temp_left_panel .form-group',
				errorListClass: 'form-tooltip-error'
			}
		},
		labels: {
			'send_to': 'Send To',
			'template': 'Template',
            'body': 'Body'
		}
	});

    //Number Validation
    <?php if($country_id == 1 && $this->config->item('yabbr_switch') == 1){ ?>
    jQuery('#send_to').keyup(function(e){
        e.preventDefault()
        var send_to = jQuery("#send_to").val();
        var job_id = "<?php echo $job_id; ?>";
        if(job_id == ''){
            if(send_to != ''){
                var formData = new FormData();
                formData.append('send_to', send_to);
                jQuery("#load-screen").show();
                $('#number_status').removeClass('badge badge-success').removeClass('badge badge-danger');
                jQuery.ajax({
                    type: "POST",
                    url: "/sms/validate_number",
                    data: formData,
                    dataType:'json',
                    processData: false,
                    contentType: false,
                    cache: false,
                }).done(function( ret ){
                    console.log(ret);
                    if(ret.status == 'OK'){
                        $('#number_status').removeClass('d-none').addClass('badge badge-success').text(ret.status);
                        $('#send_btn').attr('disabled', false);
                    } else {
                        $('#number_status').removeClass('d-none').addClass('badge badge-danger').text(ret.status);
                        $('#send_btn').attr('disabled', true);
                    }
                    
                    jQuery("#load-screen").hide();
                }).fail(function() {
                    $('#send_btn').attr('disabled', true);
                    jQuery("#load-screen").hide();
                    swal({
                        title: "Error!",
                        text: "Got Some error, please check",
                        type: "error",
                        confirmButtonClass: "btn-danger"
                    });
                });  
            } else {
                $('#number_status').removeClass('d-none').addClass('badge badge-danger').text('Phone Number is required!');
                $('#send_btn').attr('disabled', true);
            }
        }      
    });
    <?php } ?>


    <?php
    // tenant autofill
    if( $this->input->get_post('tenant_id') > 0 ){ ?>        
        jQuery("#tenants_btn_div .tag_btn[data-tenant_id='<?php echo $this->input->get_post('tenant_id'); ?>']").click();
    <?php
    }   
    ?>

    // Filter sms template based on category
    $("#category").on('change', function() {
        var category = $(this).val();

        $("#load-screen").show();
        $.ajax({
            type: "POST",
            url: "<?php echo site_url(); ?>ajax/sms_ajax/auto_populate_sms_template",
            data: {
                category: category,
                job_id: <?= $job_id ?>
            },
            dataType:'json',
            cache: false,
        }).done(function( response ){
            var data = JSON.parse(JSON.stringify(response));
            $("#load-screen").hide();

            if (data.success == true) {
                var category_name = "<option value=''>--- Select ---</option>";
                $.each(response.data, function (key, value) {
                    category_name += '<option value="' + value.sms_api_type_id + '">' + value.type_name + '</option>';
                });
                $("#template").html(category_name)
            } else {
                var category_name = "<option value=''>--- Select ---</option>";
                $.each(response.data, function (key, value) {
                    category_name += '<option value="' + value.sms_api_type_id + '">' + value.type_name + '</option>';
                });
                $("#template").html(category_name)
            }
            
        })
    });

});
</script>