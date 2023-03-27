<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
    $bc_items = array(
        array(
            'title' => $title,
            'status' => 'active',
            'link' =>  $uri
        )
    );
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	<?php 
	if( validation_errors() ){ ?>
		<div class="alert alert-danger">
		<?php echo validation_errors(); ?>
		</div>
	<?php
	}	
	?>

	<style>
	.separator {
		margin: 0 5px;
	}
	.bold_it{
		font-weight: bold;
	}
    .prop_details{
        display:none;
    }
	</style>
    
    
	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">
			<?php
		$form_attr = array(
			'id' => 'jform'
		);
		echo form_open($uri,$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-md-8 columns">
					<div class="row">	

                        <div class="col-md-5">
							<label for="agency_select">Office (Agency)</label>
							<select id="office_id_filter" name="office_id_filter"  class="form-control">
                                <option value="">ALL</option>
                                <?php 
                                foreach( $agency_filter->result() as $agency_row ) { ?>
                                    <option value="<?php echo $agency_row->office_id; ?>" <?php echo (  $agency_row->office_id == $this->input->get_post('office_id_filter') )?'selected':null;  ?>><?php echo $agency_row->agency_name; ?></option>
                                <?php
                                }
                                ?>
							</select>							
						</div>

						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<input type="submit" name="search_submit" value="Search" class="btn">
						</div>
						
					</div>

				</div>
			</div>
			</form>
		</div>
	</header>
    

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
  
                <table class="table main-table">
                    <tr>
                        <th>Event Type</th>
                        <th>Address</th>    
                        <th>Tenants</th>                    
                        <th>Date</th>
                        <th>CRM Linked</th>
                        <th>Action</th>
                    </tr>
                    <?php
                    if( $webhooks_data_sql->num_rows() > 0 ){
                        foreach( $webhooks_data_sql->result() as $webhooks_row ){ 
                        
                            $json_dec = json_decode($webhooks_row->json);     

                            $event_obj = $json_dec->event;
                            $rel_res_obj = $event_obj->relatedResources;
                            $prop_comp_obj = $rel_res_obj->propertyCompliance;
                            $manage_agree_obj = $rel_res_obj->managementAgreement;
                            $landlords_obj_arr = $manage_agree_obj->landlords;
                            $ten_agree_arr_obj = $rel_res_obj->tenantAgreements;
                            $prop_obj = $rel_res_obj->property;      
                            $portfolio_obj = $rel_res_obj->portfolio;
                            $users_arr_obj = $rel_res_obj->users;
                            $address_obj = $prop_obj->address;

                            $event_id = $json_dec->eventId;
                            $office_id = $json_dec->officeId;
                            $event_type = $json_dec->eventType;
                            $console_prop_id = $prop_obj->propertyId;  

                            $prop_comp_proc_obj = $event_obj->propertyComplianceProcess;
                            $prop_comp_proc_id = $prop_comp_proc_obj->propertyComplianceProcessId;  
                            
                            $last_updated_date_time = date('Y-m-d H:i:s',strtotime($event_obj->lastUpdatedDateTime));   
                            
                            // get landlords
                            $landlords_arr = [];
                            foreach( $landlords_obj_arr as $landlords_obj ){
                                $landlords_arr[] = $landlords_obj->contactId;
                            }
                            
                        ?>

                        <tr>                                        
                            <td><?php echo str_replace('_',' ',ucwords(strtolower($webhooks_row->event_type))); ?></td>
                            <td><?php echo $prop_obj->displayName; ?></td>

                            <td>
                            <table class="table">
                                <tr>
                                    <th colspan="2">Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                </tr>
                            <?php                            
                            foreach( $rel_res_obj->contacts as $contacts_obj ){ 

                                $contact_id = $contacts_obj->contactId;

                                if( !in_array($contact_id,$landlords_arr) ){ // exclude landlords
                                
                                $person_det_obj = $contacts_obj->personDetail;
                                $phones_arr_obj = $contacts_obj->phones;
                                $emails_arr_obj = $contacts_obj->emails;
                                ?>                                        
                                    <tr>                                        
                                        <td><?php echo $person_det_obj->firstName; ?></td>
                                        <td><?php echo $person_det_obj->lastName; ?></td>
                                        <td>
                                            <table clas="table">
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Number</th>
                                                    <th>Primary</th>                                                        
                                                </tr>
                                                <?php
                                                foreach( $phones_arr_obj as $phones_obj ){ ?>
                                                    <tr>
                                                        <td><?php echo ucwords(strtolower($phones_obj->type)); ?></td>
                                                        <td><?php echo $phones_obj->phoneNumber; ?></td>
                                                        <td>
                                                            <?php echo ( $phones_obj->is_primary == 1 )?'<span class="text-success">Yes</span>':'<span class="text-danger">No</span>'; ?>
                                                        </td>                                                                
                                                    </tr>
                                                <?php
                                                }											
                                                ?>											
                                            </table>
                                        </td>
                                        <td>
                                            <table clas="table">
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Email</th>
                                                    <th>Primary</th>                                                        
                                                </tr>
                                                <?php
                                                foreach ( $emails_arr_obj as $emails_obj ){ ?>
                                                    <tr>
                                                        <td><?php echo ucwords(strtolower($emails_obj->type)); ?></td>
                                                        <td>
                                                            <?php echo $emails_obj->emailAddress; ?>															
                                                        </td>
                                                        <td>
                                                            <?php echo ( $emails_obj->is_primary == 1 )?'<span class="text-success">Yes</span>':'<span class="text-danger">No</span>'; ?>
                                                        </td>                                                                
                                                    </tr>
                                                <?php
                                                }											
                                                ?>	
                                            </table>
                                        </td>
                                    </tr>
                                <?php   
                                } 
                            } 
                            ?>
                            </table>
                            </td>                            
                            <td><?php echo date("d/m/Y H:i",strtotime($webhooks_row->cwd_date)) ?></td>
                            <td>
                                <?php
                                if( $webhooks_row->crm_prop_id != '' ){ ?>
                                    <a 
                                        href="<?php echo $this->config->item('crm_link'); ?>/view_property_details.php?id=<?php echo $webhooks_row->crm_prop_id; ?>" 
                                        target="_blank"
                                    >
                                        <button type="button" class="btn btn-primary">View</button>
                                    </a>
                                <?php
                                }
                                ?> 
                            </td>
                            <td>
                                <input type="hidden" class="cwd_id" value="<?php echo $webhooks_row->cwd_id; ?>">
                                <button type="button" class="btn hide_btn">Hide</button>
                            </td>
                        </tr>
                        <?php                
                        }
                    }else{ ?>
                        <tr>
                            <td colspan="100%">Empty</td>
                        </tr>
                    <?php
                    }
                    ?>
                </table>		       						

			</div>

			<nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
			<div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
			

		</div>
	</section>

</div>


<!-- Fancybox START -->

<!-- ABOUT TEXT -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
<pre><code><?php echo $page_query; ?></code></pre>

</div>
<script>
jQuery(document).ready(function(){

    jQuery(".hide_btn").click(function(){

        var dom = jQuery(this);
        var parent_td = dom.parents("td:first");
        var cwd_id = parent_td.find(".cwd_id").val();

        if( cwd_id > 0 ){

            swal({
                title: "Warning!",
                text: "This will hide this tenant info, Do you want to continue?",
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
                        url: "/console/deactivate_webhook_data",
                        data: { 	
                            cwd_id: cwd_id
                        }
                    }).done(function( ret ){
                                            
                        $('#load-screen').hide();	
                        location.reload();

                    });							

                }

            });				            

        }        

    });
    
});
</script>

