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
  
                <table class="table table-hover main-table">

                    <thead>
                        <tr>    
                            <th>Address</th>    
                            <th>CRM Linked</th>
                            <th>Details</th>                                   						                           
                        </tr>
                    </thead>

                        <?php
                        foreach( $console_prop_sql->result() as $console_prop_row ){ 

                            /*
                            // check if event ID exist
                            $whd_sql = $this->db->query("
                            SELECT *
                            FROM `console_webhooks_data` 
                            WHERE `console_prop_id` = '{$console_prop_row->console_prop_id}'     
                            ORDER BY `last_updated_date_time` DESC  
                            ");
                            $whd_row = $whd_sql->row();  

                            if( $whd_row->json != '' ){

                                $json_dec = json_decode($whd_row->json);

                                $event_obj = $json_dec->event;
                                $rel_res_obj = $event_obj->relatedResources;
                                $prop_comp_obj = $rel_res_obj->propertyCompliance;
                                $manage_agree_obj = $rel_res_obj->managementAgreement;
                                $ten_agree_arr_obj = $rel_res_obj->tenantAgreements;
                                $prop_obj = $rel_res_obj->property;      
                                $portfolio_obj = $rel_res_obj->portfolio;
                                $users_arr_obj = $rel_res_obj->users;
                                $address_obj = $prop_obj->address;
                        
                                $event_id = $json_dec->eventId;
                                $office_id = $json_dec->officeId;
                                $event_type = $json_dec->eventType;
                                $console_prop_id = $prop_obj->propertyId;    

                            }       
                            */
                            ?>

                            
                            <tbody class="prop_tbody">

                                <tr>
                                    <td><?php echo $console_prop_row->full_address; ?></td>
                                    <td>
                                        <?php
                                        if( $console_prop_row->crm_prop_id != '' ){ ?>
                                            <a href="<?php echo $this->config->item('crm_link'); ?>/view_property_details.php?id=<?php echo $console_prop_row->crm_prop_id; ?>" target="_blank">
                                                <button type="button" class="btn btn-primary">View</button>
                                            </a>
                                        <?php
                                        }
                                        ?>                                        
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-primary show_details_btn">Show</button>
                                        <!--
                                        <button type="button" class="btn btn-primary">Add Tenant</button>
                                        <button type="button" class="btn btn-primary">Add Job</button>
                                        -->
                                    </td>										                            
                                </tr>

                                <tr class="prop_details">
                                    <td colspan="100%">
                                                
                                        <table class="table main-table">

                                            <tr>
                                                <th>Address</th>   
                                                <th>Other Info</th>    
                                                <th>Tenancy Agreement</th>
                                                <th>Users</th>                                 
                                            </tr>
                                            <tr>

                                                <td class="align-top">
                                                    <table class="table">

                                                        <tr>    
                                                            <th>Unit Number</th><td><?php echo $console_prop_row->unit_num; ?></td> 
                                                        </tr>
                                                        <tr>
                                                            <th>Street Number</th><td><?php echo $console_prop_row->street_num; ?></td>	  
                                                        </tr>
                                                        <tr> 
                                                            <th>Street Name</th><td><?php echo $console_prop_row->street_name; ?></td> 
                                                        </tr>
                                                        <tr>
                                                            <th>Street Type</th> <td><?php echo $console_prop_row->street_type; ?></td>
                                                        </tr>
                                                        <tr> 
                                                            <th>Suburb</th><td><?php echo $console_prop_row->suburb; ?></td> 
                                                        </tr>
                                                        <tr>
                                                            <th>Postcode</th><td><?php echo $console_prop_row->postcode; ?></td> 
                                                        </tr>
                                                        <tr>
                                                            <th>State</th><td><?php echo $console_prop_row->state; ?></td>                               						                           
                                                        </tr>

                                                    </table>
                                                </td>

                                                <td class="align-top">                                 
                                                    <table class="table">

                                                        <tr>    
                                                            <th>Compliance Notes</th><td><?php echo $console_prop_row->compliance_notes; ?></td> 
                                                        </tr>                                                                                                                  
                                                        <tr>    
                                                            <th>Key Number</th><td><?php echo $console_prop_row->key_number; ?></td> 
                                                        </tr> 
                                                        <tr>    
                                                            <th>Access Details</th><td><?php echo $console_prop_row->access_details; ?></td> 
                                                        </tr>
                                                        <tr>    
                                                            <th>Property Type</th><td><?php echo ucwords(strtolower($console_prop_row->property_type)); ?></td> 
                                                        </tr>                                                       
                                                        <tr>    
                                                            <th>Expiry Date</th><td><?php echo ( $console_prop_row->expiry_date != '' )?date('Y-m-d',strtotime($console_prop_row->expiry_date)):null; ?></td> 
                                                        </tr>
                                                        <tr>    
                                                            <th>Last Inspection</th><td><?php echo ( $console_prop_row->last_inspection != '' )?date('Y-m-d',strtotime($console_prop_row->last_inspection)):null; ?></td> 
                                                        </tr>                                               
                                                        <tr>    
                                                            <th>QLD 2020 Compliance</th><td><?php echo ( $console_prop_row->qld_2020_comp == 1 )?'<span class="text-success">Yes</span>':'<span class="text-danger">No</span>'; ?></td> 
                                                        </tr>                                                                                      

                                                    </table>
                                                </td>

                                                <td class="align-top">
                                                    <?php

                                                    if( $console_prop_row->console_prop_id > 0 ){

                                                        // get console tenant agreements                
                                                        $this->db->select('*');
                                                        $this->db->from('console_tenant_agreements'); 
                                                        $this->db->where('console_prop_id', $console_prop_row->console_prop_id);
                                                        $this->db->where('active', 1);
                                                        $cta_sql = $this->db->get();

                                                        foreach( $cta_sql->result() as $cta_row ){                                                                                                                                                                        
                                                        ?>
                                                            <table class="table">
                                                                <tr>
                                                                    <th>Lease Name</th><td><?php echo $cta_row->lease_name; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Inaugural Date</th>
                                                                    <td><?php echo ( $cta_row->inaugural_date !='' )?date('Y-m-d',strtotime($cta_row->inaugural_date)):null; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Start Date</th>
                                                                    <td><?php echo ( $cta_row->start_date !='' )?date('Y-m-d',strtotime($cta_row->start_date)):null; ?></td>
                                                                </tr>  
                                                                <tr>
                                                                    <th>End Date</th>
                                                                    <td><?php echo ( $cta_row->end_date != '' )?date('Y-m-d',strtotime($cta_row->end_date)):null; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Vacating Date</th>
                                                                    <td><?php echo ( $cta_row->vacating_date !=''  )?date('Y-m-d',strtotime($cta_row->vacating_date)):null; ?></td>
                                                                </tr>                                                                                                                      
                                                            </table>
                                                        <?php
                                                        }
                                                        
                                                    }                                                   
                                                    ?>
                                                </td> 

                                                <td class="align-top">                                 
                                                    <?php
                                                    if( $console_prop_row->console_prop_id ){

                                                        // get console tenant agreements                
                                                        $this->db->select('*');
                                                        $this->db->from('console_users'); 
                                                        $this->db->where('console_prop_id', $console_prop_row->console_prop_id);
                                                        $this->db->where('active', 1);
                                                        $cu_sql = $this->db->get();

                                                        foreach( $cu_sql->result() as $cu_row ){                                                             
                                                        ?>
                                                            <table class="table">
                                                                <tr>
                                                                    <th>First Name</th><td><?php echo $cu_row->first_name; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Last Name</th><td><?php echo $cu_row->last_name; ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Last Name</th><td><?php echo $cu_row->email; ?></td>
                                                                </tr>
                                                            </table>
                                                        <?php
                                                        }
                                                        
                                                    }                                                    
                                                    ?>
                                                </td>

                                            </tr>

                                        </table>

                                        <table class="table main-table mb-5">

                                            <tr>
                                                <th colspan="100%">Tenants</th>
                                            </tr>

                                            <tr>    
                                                <th>First Name</th>
                                                <th>Last Name</th>
                                                <th>Phone</th>
                                                <th>Email</th>
                                            </tr>  
                                            
                                            <?php
                                            if( $console_prop_row->console_prop_id > 0 ){

                                                // get console tenants                  
                                                $this->db->select('*');
                                                $this->db->from('console_property_tenants AS cpt');
                                                $this->db->join('console_properties AS cp', 'cpt.`console_prop_id` = cp.`console_prop_id`', 'inner');
                                                $this->db->where('cp.console_prop_id', $console_prop_row->console_prop_id);
                                                $this->db->where('cpt.active', 1);
                                                $console_tenant_sql = $this->db->get();
                                                
                                                foreach( $console_tenant_sql->result() as $console_tenant_row ){ ?>

                                                    <tr class="<?php echo ( $console_tenant_row->new_tenants_ts != '' )?'bg-success':null;  ?>">
                                                        <td class="<?php echo ( $console_tenant_row->first_name_updated_ts != '' )?'bg-warning':null;  ?>"><?php echo $console_tenant_row->first_name; ?></td>
                                                        <td class="<?php echo ( $console_tenant_row->last_name_updated_ts != '' )?'bg-warning':null;  ?>"><?php echo $console_tenant_row->last_name; ?></td>
                                                        <td>
                                                            <table clas="table">
                                                                <tr>
                                                                    <th>Type</th>
                                                                    <th>Number</th>
                                                                    <th>Primary</th>                                                        
                                                                </tr>
                                                                <?php
                                                                if( $console_tenant_row->contact_id > 0 ){

                                                                    // get tenants phone                
                                                                    $this->db->select('*');
                                                                    $this->db->from('console_property_tenant_phones AS cpt_phones');
                                                                    $this->db->join('console_property_tenants AS cpt', 'cpt_phones.contact_id = cpt.contact_id', 'inner');											
                                                                    $this->db->where('cpt.contact_id', $console_tenant_row->contact_id);
                                                                    $this->db->where('cpt_phones.active', 1);
                                                                    $cpt_phone_sql = $this->db->get();												

                                                                    foreach ( $cpt_phone_sql->result() as $cpt_phone_row ){ ?>
                                                                        <tr>
                                                                            <td><?php echo ucwords(strtolower($cpt_phone_row->type)); ?></td>
                                                                            <td>
                                                                                <?php echo $cpt_phone_row->number; ?>
                                                                                <input type="hidden" class="console_tenant_phone_number" value="<?php echo $cpt_phone_row->number; ?>" />
                                                                            </td>
                                                                            <td>
                                                                                <?php echo ( $cpt_phone_row->is_primary == 1 )?'<span class="text-success">Yes</span>':'<span class="text-danger">No</span>'; ?>
                                                                            </td>                                                                
                                                                        </tr>
                                                                    <?php
                                                                    }

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
                                                                if( $console_tenant_row->contact_id > 0 ){

                                                                    // get tenants email                
                                                                    $this->db->select('*');
                                                                    $this->db->from('console_property_tenant_emails AS cpt_emails');
                                                                    $this->db->join('console_property_tenants AS cpt', 'cpt_emails.contact_id = cpt.contact_id', 'inner');											
                                                                    $this->db->where('cpt.contact_id', $console_tenant_row->contact_id);
                                                                    $this->db->where('cpt_emails.active', 1);
                                                                    $cpt_emails_sql = $this->db->get();												

                                                                    foreach ( $cpt_emails_sql->result() as $cpt_emails_row ){ ?>
                                                                        <tr>
                                                                            <td><?php echo ucwords(strtolower($cpt_emails_row->type)); ?></td>
                                                                            <td>
                                                                                <?php echo $cpt_emails_row->email; ?>															
                                                                            </td>
                                                                            <td>
                                                                                <?php echo ( $cpt_emails_row->is_primary == 1 )?'<span class="text-success">Yes</span>':'<span class="text-danger">No</span>'; ?>
                                                                            </td>                                                                
                                                                        </tr>
                                                                    <?php
                                                                    }

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

                                        <?php
                                        if( $console_prop_row->console_prop_id > 0 ){

                                            // get console property                
                                            $this->db->select('
                                            cwd.`id` AS cwd_id,
                                            cwd.`event_type`,
                                            cwd.`json`,
                                            cwd.`date` AS cwd_date
                                            ');
                                            $this->db->from('console_webhooks_data AS cwd');  
                                            $this->db->where('cwd.`active`', 1);      
                                            if( $this->input->get_post('office_id_filter') > 0 ){            
                                                $this->db->where('cwd.office_id', $this->input->get_post('office_id_filter'));
                                            }
                                            $this->db->where('cwd.`console_prop_id`', $console_prop_row->console_prop_id);
                                            $this->db->order_by('cwd.`date`', 'DESC');
                                            $webhooks_data_sql = $this->db->get(); 
                                            if( $webhooks_data_sql->num_rows() > 0 ){
                                            ?>
                                                <h5>Webhooks: </h5>
                                                <table class="table mb-3">
                                                    <tr>
                                                        <th>Event Type</th>
                                                        <th>Content</th>
                                                        <th>Date</th>                                                   
                                                    </tr>
                                                    <?php                                            
                                                    foreach( $webhooks_data_sql->result() as $webhooks_row ){ ?>
                                                        <tr>
                                                            <td><?php echo str_replace('_',' ',ucwords(strtolower($webhooks_row->event_type))); ?></td>
                                                            <td>
                                                                <input type="hidden" class="cwd_id" value=<?php echo $webhooks_row->cwd_id ?> />
                                                                <button type="button" class="btn view_webhook_data_btn">View</button>
                                                            </td>
                                                            <td><?php echo date("d/m/Y H:i",strtotime($webhooks_row->cwd_date)) ?></td>        
                                                        </tr>
                                                    <?php
                                                    }
                                                    ?>                                            
                                                </table>
                                            <?php
                                            }

                                        }
                                        ?>

                                    </td>
                                </tr>

                            </tbody>

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
	<p>This page shows all properties that are not upgraded to the NEW QLD legislation</p>
	<pre>
		<code>
SELECT
p.`property_id`,
p.`address_1`,
p.`address_2`,
p.`address_3`,
p.`state`,
p.`postcode`,
p.`qld_new_leg_alarm_num`,

a.`agency_id`,
a.`agency_name`
FROM `property` AS p
LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
WHERE p.`qld_new_leg_alarm_num` > 0
AND p.`prop_upgraded_to_ic_sa` != 1
AND p.`state` = 'QLD'
AND p.`deleted` = 0
LIMIT 0, 50</code>
	</pre>

</div>

<div id="display_webhook_data_breakdown_fb" class="fancybox" style="display:none;" >test</div>
<script>
jQuery(document).ready(function(){

    jQuery(".show_details_btn").click(function(){

        var show_details_btn_dom = jQuery(this);
        show_details_btn_dom.parents(".prop_tbody").find(".prop_details").toggle();

    });

    // load webhook data breakdown
    jQuery(".view_webhook_data_btn").click(function(){

        var dom = jQuery(this);
        var parent_td = dom.parents("td:first");
        var cwd_id = parent_td.find(".cwd_id").val();

        if( cwd_id > 0 ){

            $('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/console/display_webhook_data_breakdown",
                data: { 	
                    cwd_id: cwd_id
                }
            }).done(function( ret ){
                    
                $('#load-screen').hide();
                jQuery("#display_webhook_data_breakdown_fb").html(ret)
                jQuery.fancybox.open({
                    src  : '#display_webhook_data_breakdown_fb'
                })	

            });

        }        	

    });
    
});
</script>

