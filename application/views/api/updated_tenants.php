<style>
    #updated_warning_sign{
        cursor: pointer;
    }
    .edit_tenant_hidden{
        display: none;
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
        'link' => $uri
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);

$export_links_params_arr = array(
    'date_from_filter' => $this->input->get_post('date_from_filter'),
    'date_to_filter' => $this->input->get_post('date_to_filter'),
    'tech_filter' =>  $this->input->get_post('tech_filter'),
    'reason_filter' =>  $this->input->get_post('reason_filter'),
    'job_type_filter' =>  $this->input->get_post('job_type_filter'),
    'date_filter' =>  $this->input->get_post('date')
);
$export_link_params = "/jobs/missed_jobs/?export=1&".http_build_query($export_links_params_arr);
?>

	<header class="box-typical-header">

        <div class="box-typical box-typical-padding">
            <?php
        $form_attr = array(
            'id' => 'jform'
        );
        echo form_open($uri,$form_attr);
        ?>
            <div class="for-groupss row">
                <div class="col-lg-8">
                    <div class="row">

                        <div class="col-md-3">
							<label>Agency</label>
							<select id="agency_filter" name="agency_filter" class="form-control">
								<option value="">---</option>
                                <?php                                                            
                                foreach( $agency_filter_sql->result() as $agency_row ){                                   
                                ?>
                                    <option 
                                        value="<?php echo $agency_row->agency_id; ?>" 
                                        <?php echo (  $agency_row->agency_id == $this->input->get_post('agency_filter') )?'selected':null; ?>
                                        class="<?php echo ( $agency_row->priority > 0 )?'j_is_bold':''; ?>"
                                    >
                                        <?php echo $agency_row->agency_name.( ( $agency_row->priority > 0 )?' ('.$agency_row->abbreviation.')':null ); ?>
                                    </option>
                                <?php
                                }                                     
                                ?>
							</select>							
						</div>

                        <div class="col-md-1 columns">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <input class="btn" type="submit" name="btn_search" value="Search" />
                        </div>
                        
                    </div>

                </div>

                <div class="col-lg-4 columns">
                    <label>PropertyMe API:</label>
                    <a target="_blank" href="/cronjobs/update_tenant_last_update_ts">
                        <button class="btn" type="button">Get Latest "Tenancy Updated On" record</button>
                    </a>                    
				</div>
                
                <!--
                <div class="col-lg-2 columns">
                    <section class="proj-page-section float-right">
                        <div class="proj-page-attach">
                            <i class="fa fa-file-excel-o"></i>
                            <p class="name"><?php echo $title; ?></p>
                            <p>
								<a href="<?php echo $export_link ?>">
									Export
								</a>
                            </p>
                        </div>
                    </section>
				</div>
                -->
                                    
                </div>
                </form>
            </div>

        </header>

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<table class="table table-hover main-table">
					<thead>
						<tr>    
                            <th>Property address</th>       
                            <th>Tenants</th>
                            <th>Agency</th>
                            <th>CRM last "Tenancy Updated On" record</th>
                            <th>PMe current/live "Tenancy Updated On" record</th>
						</tr>
					</thead>

					<tbody>
                        <?php                  
                        if( $this->input->get_post('btn_search') ){

                            $row_ctr = 1;
                            foreach($lists->result() as $row){

                                $tenants_need_update = false;
                                $crm_prop_last_updated = date('Y-m-d H:i:s',strtotime($row->last_updated_ts));
                                $pme_TenancyUpdatedOn = null;

                                // loop through agency
                                foreach( $pme_agency_arr as $pme_agency_data ){

                                    // pme property object
                                    $pme_prop_arr = $pme_agency_data->pme_prop;    
                                    
                                    if( $pme_agency_data->agency_id == $row->agency_id ){

                                        // loop through PMe properties
                                        foreach( $pme_prop_arr as $pme_prop_obj ){

                                            $pme_prop_last_updated = date('Y-m-d H:i:s',strtotime($pme_prop_obj->TenancyUpdatedOn));   
                                                                                               
                                            if( $pme_prop_obj->Id == $row->api_prop_id ){ // crm and PMe property match

                                                $pme_TenancyUpdatedOn = date('d/m/Y H:i',strtotime($pme_prop_obj->TenancyUpdatedOn));

                                                // check if property tenants is up to date
                                                $pt_sql_str = "
                                                SELECT COUNT(pt.`property_tenant_id`) AS pt_count
                                                FROM `property_tenants` AS pt
                                                LEFT JOIN `property` AS p ON pt.`property_id` = p.`property_id`
			                                    LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
                                                WHERE p.`property_id` = {$row->property_id}
                                                AND a.`agency_id` = {$row->agency_id}
                                                AND pt.`active` = 1
                                                AND ( 
                                                    pt.`modifiedDate` >= '{$pme_prop_last_updated}' OR 
                                                    pt.`createdDate` >= '{$pme_prop_last_updated}'
                                                )
                                                ";
                                                $pt_sql = $this->db->query($pt_sql_str);
                                                $pt_is_up_to_date = ( $pt_sql->row()->pt_count > 0 )?true:false;

                                                // last stored PMe updated date is out of date
                                                if( $pme_prop_last_updated > $crm_prop_last_updated ){ 

                                                    $tenants_need_update = true;

                                                }                                                        
                                                

                                            }                                                    

                                        }  
                                    
                                    }

                                }
                               
                                if( $pt_is_up_to_date == false ){
                            ?>
                                <tr id="row_id_<?php echo $row_ctr; ?>">        
                                    <td>
                                        <a href="<?php echo $this->config->item("crm_link"); ?>/view_property_details.php?id=<?php echo $row->property_id; ?>">
                                            <?php echo "{$row->p_address_1} {$row->p_address_2}, {$row->p_address_3}"; ?>
                                        </a>
                                    </td>  
                                    <td><button class="btn view_tenants_btn" type="button">View</button></td>                                          
                                   <td>                         
                                        <a href="/agency/view_agency_details/<?php echo $row->agency_id; ?>">
                                            <span class="<?php echo ( $row->priority > 0 )?'j_is_bold':''; ?>"><?php echo $row->agency_name.( ( $row->priority > 0 )?' ('.$row->abbreviation.')':null ); ?></span>
                                        </a> 
                                    </td>                            
                                    <td><?php echo ( $this->system_model->isDateNotEmpty($row->last_updated_ts) )?date('d/m/Y H:i',strtotime($row->last_updated_ts)):null; ?></td>                                                      
                                    <td>
                                        <span class="mr-2 <?php echo ( $tenants_need_update == true )?'text-danger':'text-success'; ?>"><?php echo $pme_TenancyUpdatedOn; ?></span>
                                        <?php
                                        if( $tenants_need_update == true ){ // out of date ?>
                                     
                                            <span 
                                                id='updated_warning_sign' 
                                                class='fa fa-warning text-warning' 
                                                data-toggle='tooltip' 
                                                title='Tenants need update'
                                            >
                                            </span>                                  
                                                            
                                        <?php                                          
                                        } 
                                        ?>  
                                        <input type="hidden" class="api_prop_id" value="<?php echo $row->api_prop_id; ?>" />  
                                        <input type="hidden" class="property_id" value="<?php echo $row->property_id; ?>" />   
                                        <input type="hidden" class="row_id" value="<?php echo $row_ctr; ?>" />                                                                           
                                    </td>                
                                </tr>
                            <?php
                                $row_ctr++;
                                }
                            }
                        }else{
                            echo "<tr><td colspan='100%'>Please Select Agency to filter</td></tr>";
                        }                   
                        ?>
					</tbody>

				</table>
			</div>

<?php
if( $this->input->get_post('btn_search') ){ ?>

    <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
            <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
    </div>

<?php
}
?>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >
<h4><?php echo $title; ?></h4>
<pre><code><?php echo $sql_query; ?></code></pre>
</div>

<div id="view_tenants_fb"></div>
<!-- Fancybox END -->
<script>
jQuery(document).ready(function(){

    jQuery(".view_tenants_btn").click(function(){

        var btn_dom = jQuery(this);
        var parent_tr = btn_dom.parents("tr:first");
        var view_tenants_fb = jQuery("#view_tenants_fb");

        var property_id = parent_tr.find(".property_id").val();
        var row_id = parent_tr.find(".row_id").val();

        if( property_id > 0 ){

            jQuery('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/property_me/display_tenants",
                data: { 
                    property_id: property_id
                }
            }).done(function( ret ){
                    
                jQuery('#load-screen').hide();      
                view_tenants_fb.html(ret);   
                view_tenants_fb.find("#row_id").val(row_id);
                
                // launch fancybox
                $.fancybox.open({
                    src  : '#view_tenants_fb'
                });

            });

        }
        

    });

    // add PMe tenant
    jQuery("#view_tenants_fb").on('click', '.add_pme_tenant', function() {

        var add_pme_tenant_ob = jQuery(this);
        var parent_lb = add_pme_tenant_ob.parents("div#view_tenants_fb");   
        var parent_tr = add_pme_tenant_ob.parents("tr:first");

        var property_id = parent_lb.find("#property_id").val();  
        var fname = parent_tr.find(".pme_tenant_fname").text();
        var lname = parent_tr.find(".pme_tenant_lname").text();
        var mobile = parent_tr.find(".pme_tenant_mobile").text();
        var landline = parent_tr.find(".pme_tenant_landline").text();
        var email = parent_tr.find(".pme_tenant_email").text();

        if( property_id > 0 ){

            jQuery('#load-screen').show();
            jQuery.ajax({
                url: "/property_me/ajax_function_tenants",
                type: 'POST',
                data: { 
                    'property_id': property_id,
                    'tenant_firstname' : fname, 
                    'tenant_lastname' : lname, 
                    'tenant_mobile' : mobile, 
                    'tenant_landline' : landline, 
                    'tenant_email' : email, 
                    'active': 1 
                }
            }).done(function( ret ){

                ret = JSON.parse(ret);
                jQuery('#load-screen').hide();
                
                if (ret.isExist === true) {
                    
                    swal({
                        title: "",
                        text: "There is a tenant with the same First Name and Last Name already.",
                        type: "error",
                        confirmButtonClass: "btn-success"
                    });

                }else {

                    if (ret.insertStat === true) {                    

                        //location.reload();

                        // change + icon to check icon
                        add_pme_tenant_ob.removeClass('fa-plus');
                        add_pme_tenant_ob.addClass('fa-check');

                        // change color to green
                        add_pme_tenant_ob.addClass('text-success');

                        var new_row = ''+
                        '<tr>'+
                            '<td>'+
                                '<span class="edit_tenant_display crm_tenant_fname_lbl">'+fname+'</span>'+
                                '<input type="text" class="form-control crm_tenant_fname edit_tenant_hidden" value="'+fname+'" />'+
                            '</td>'+
                            '<td>'+
                                '<span class="edit_tenant_display crm_tenant_lname_lbl">'+lname+'</span>'+
                                '<input type="text" class="form-control crm_tenant_lname edit_tenant_hidden" value="'+lname+'" />'+
                            '</td>'+
                            '<td>'+
                                '<span class="edit_tenant_display crm_tenant_mobile_lbl">'+mobile+'</span>'+
                                '<input type="text" class="form-control crm_tenant_mobile edit_tenant_hidden" value="'+mobile+'" />'+
                            '</td>'+
                            '<td>'+
                                '<span class="edit_tenant_display edit_tenant_hidden_lbl">'+landline+'</span>'+
                                '<input type="text" class="form-control crm_tenant_landline edit_tenant_hidden" value="'+landline+'" />'+
                            '</td>'+
                            '<td>'+
                                '<span class="edit_tenant_display crm_tenant_email_lbl">'+email+'</span>'+
                                '<input type="text" class="form-control crm_tenant_email edit_tenant_hidden" value="'+email+'" />'+
                            '</td>'+
                            '<td class="text-center">'+
                                '<a href="javascript:void(0)"><span class="fa fa-trash text-danger mr-1 delete_crm_tenant"></span></a>'+
                                '<a href="javascript:void(0)"><span class="fa fa-pencil text-warning edit_crm_tenant"></span></a>'+
                                '<input type="hidden" class="pt_id" value="'+ret.insertId+'" />'+
                            '</td>'+
                        '</tr>';
                        jQuery("#crm_tenants_table_body").append(new_row);

                    }else {

                        swal({
                            title: "Error!",
                            text: "Something went wrong contact devs.",
                            type: "error",
                            confirmButtonClass: "btn-success"
                        });

                    }

                }

            });	

        }                        

    });

    // delete CRM tenants
    jQuery("#view_tenants_fb").on('click', '.delete_crm_tenant', function() {

        var add_pme_tenant_ob = jQuery(this);
        var parent_lb = add_pme_tenant_ob.parents("div#view_tenants_fb");   
        var parent_tr = add_pme_tenant_ob.parents("tr:first");

        var property_id = parent_lb.find("#property_id").val();  
        var pt_id = parent_tr.find(".pt_id").val(); 

        if( property_id > 0 && pt_id > 0 ){
           
            swal({
                title: "",
                text: "Are you sure you want to delete tenant?",
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
                                                                        
                    jQuery('#load-screen').show(); 
                    jQuery.ajax({
                        url: "/property_me/delete_tenant",
                        type: 'POST',
                        data: { 
                            'property_id': property_id,
                            'pt_id': pt_id
                        }
                    }).done(function( ret ){

                        jQuery('#load-screen').hide();
                        parent_tr.remove();
                        
                    });	

                }

            });	

        }                                    

    });

    // edit CRM tenant
    jQuery("#view_tenants_fb").on('click', '.edit_crm_tenant', function() {

        var edit_crm_tenant_ob = jQuery(this);
        var parent_lb = edit_crm_tenant_ob.parents("div#view_tenants_fb");   
        var parent_tr = edit_crm_tenant_ob.parents("tr:first");
        var is_save_btn = edit_crm_tenant_ob.hasClass("fa-save"); // check for save button

        var property_id = parent_lb.find("#property_id").val();  
        var pt_id = parent_tr.find(".pt_id").val();

        var fname = parent_tr.find(".crm_tenant_fname").val(); 
        var lname = parent_tr.find(".crm_tenant_lname").val(); 
        var mobile = parent_tr.find(".crm_tenant_mobile").val(); 
        var landline = parent_tr.find(".crm_tenant_landline").val(); 
        var email = parent_tr.find(".crm_tenant_email").val();         

        if( is_save_btn == true && property_id > 0 && pt_id > 0 ){

            swal({
                title: "",
                text: "This will update changes to tenants, proceed?",
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
                                                                        
                    if( property_id > 0 ){
                        
                        jQuery('#load-screen').show(); 
                        jQuery.ajax({
                            url: "/property_me/update_tenant",
                            type: 'POST',
                            data: { 
                                'property_id': property_id,
                                'pt_id': pt_id,
                                'fname': fname,
                                'lname': lname,
                                'mobile': mobile,
                                'landline': landline,
                                'email': email
                            }
                        }).done(function( ret ){

                            jQuery('#load-screen').hide();
                            
                            parent_tr.find(".crm_tenant_fname_lbl").text(fname); 
                            parent_tr.find(".crm_tenant_lname_lbl").text(lname); 
                            parent_tr.find(".crm_tenant_mobile_lbl").text(mobile); 
                            parent_tr.find(".crm_tenant_landline_lbl").text(landline); 
                            parent_tr.find(".crm_tenant_email_lbl").text(email); 

                            // change save icon to edit icon
                            edit_crm_tenant_ob.removeClass('fa-save');
                            edit_crm_tenant_ob.addClass('fa-pencil');

                            // change color
                            edit_crm_tenant_ob.removeClass('text-info');
                            edit_crm_tenant_ob.addClass('text-warning');
                            
                            parent_tr.find(".edit_tenant_hidden").hide();
                            parent_tr.find(".edit_tenant_display").show();
                            
                        });	

                    }

                }

            });	

        }else{ // default edit icon

            // change edit icon to save icon
            edit_crm_tenant_ob.removeClass('fa-pencil');
            edit_crm_tenant_ob.addClass('fa-save');

            // change color
            edit_crm_tenant_ob.removeClass('text-warning');
            edit_crm_tenant_ob.addClass('text-info');
            
            parent_tr.find(".edit_tenant_display").hide();
            parent_tr.find(".edit_tenant_hidden").show();

        }

    });

    // marked as checked
    jQuery("#view_tenants_fb").on('click', '#mark_as_checked', function() {

        var mark_as_checked_btn_dom = jQuery(this);  
        var parent_lb = mark_as_checked_btn_dom.parents("div#view_tenants_fb");   
        var parent_tr = mark_as_checked_btn_dom.parents("tr:first");

        var property_id = parent_lb.find("#property_id").val();  
        var row_id = parent_lb.find("#row_id").val(); 

        if( property_id > 0 ){
        
            swal({
                title: "",
                text: "This will clear the property without further changes, proceed?",
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
                                                                        
                    jQuery('#load-screen').show(); 
                    jQuery.ajax({
                        url: "/property_me/mark_as_checked",
                        type: 'POST',
                        data: { 
                            'property_id': property_id
                        }
                    }).done(function( ret ){

                        jQuery('#load-screen').hide();
                        jQuery("#row_id_"+row_id).remove();
                        $.fancybox.close();
                        
                    });	

                }

            });	

        }                                    

    });

});
</script>