<?php
// breakdown json

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

// property compliance
$expiry_date = ( $prop_comp_obj->expiryDate != '' )?date('Y-m-d',strtotime($prop_comp_obj->expiryDate)):null;
$last_ins_date = ( $prop_comp_obj->lastInspectionDate != '' )?date('Y-m-d',strtotime($prop_comp_obj->lastInspectionDate)):null;
?>
<table class="tabke">

    <tr>
        <th>Address</th>    
        <th>Other Info</th>    
        <th>Tenancy Agreement</th>
        <th>Users</th>
    </tr>
    <tr>                                        
        <td class="align-top pr-3">
            <table class="table">
                <tr>    
                    <th>Unit Number</th><td><?php echo $address_obj->unitNumber; ?></td> 
                </tr>
                <tr>
                    <th>Street Number</th><td><?php echo $address_obj->streetNumber; ?></td>	  
                </tr>
                <tr> 
                    <th>Street Name</th><td><?php echo $address_obj->streetName; ?></td> 
                </tr>
                <tr>
                    <th>Street Type</th> <td><?php echo $address_obj->streetType; ?></td>
                </tr>
                <tr> 
                    <th>Suburb</th><td><?php echo $address_obj->suburb; ?></td> 
                </tr>
                <tr>
                    <th>Postcode</th><td><?php echo $address_obj->postCode; ?></td> 
                </tr>
                <tr>
                    <th>State</th><td><?php echo $address_obj->stateCode; ?></td>                               						                           
                </tr>
            </table>
        </td>
        <td class="align-top pr-3">
            <table class="table">
                <tr>    
                    <th>Compliance Notes</th><td><?php echo $prop_comp_obj->compliance_notes; ?></td> 
                </tr>                                                                                                                  
                <tr>    
                    <th>Key Number</th><td><?php echo $prop_obj->keyNumber; ?></td> 
                </tr> 
                <tr>    
                    <th>Access Details</th><td><?php echo $prop_obj->access_details; ?></td> 
                </tr>
                <tr>    
                    <th>Property Type</th><td><?php echo ucwords(strtolower($prop_obj->property_type)); ?></td> 
                </tr>                                                       
                <tr>    
                    <th>Expiry Date</th><td><?php echo ( $expiry_date != '' )?date('d/m/Y',strtotime($expiry_date)):null; ?></td> 
                </tr>
                <tr>    
                    <th>Last Inspection</th><td><?php echo ( $last_ins_date != '' )?date('d/m/Y',strtotime($last_ins_date)):null; ?></td> 
                </tr>                                               
                <tr>    
                    <th>QLD 2020 Compliance</th><td><?php echo ( $prop_obj->has2022LegislationCompliance == 1 )?'<span class="text-success">Yes</span>':'<span class="text-danger">No</span>'; ?></td> 
                </tr>                                                                                      
            </table>
        </td>
        <td class="align-top pr-3">
            <?php
            // console tenants agreement
            foreach( $ten_agree_arr_obj as $ten_agree_obj ){ 
                
                $lease_obj = $ten_agree_obj->lease;                                                                        
                ?>

                <table class="table mb-3">
                    <tr>
                        <th>Lease Name</th><td><?php echo $ten_agree_obj->leaseName; ?></td>
                    </tr>
                    <tr>
                        <th>Inaugural Date</th>
                        <td><?php echo ( $lease_obj->inauguralDate !='' )?date('d/m/Y',strtotime($lease_obj->inauguralDate)):null; ?></td>
                    </tr>
                    <tr>
                        <th>Start Date</th>
                        <td><?php echo ( $lease_obj->startDate !='' )?date('d/m/Y',strtotime($lease_obj->startDate)):null; ?></td>
                    </tr>  
                    <tr>
                        <th>End Date</th>
                        <td><?php echo ( $lease_obj->endDate != '' )?date('d/m/Y',strtotime($lease_obj->endDate)):null; ?></td>
                    </tr>
                    <tr>
                        <th>Vacating Date</th>
                        <td><?php echo ( $lease_obj->vacating_date !=''  )?date('d/m/Y',strtotime($lease_obj->vacating_date)):null; ?></td>
                    </tr>                                                                                                                      
                </table>

            <?php
            }
            ?>                                
        </td>
        <td class="align-top">
            <?php
            foreach( $users_arr_obj as $users_obj ){ ?>
                <table class="table">
                    <tr>
                        <th>First Name</th><td><?php echo $users_obj->firstName; ?></td>
                    </tr>
                    <tr>
                        <th>Last Name</th><td><?php echo $users_obj->lastName; ?></td>
                    </tr>
                    <tr>
                        <th>Last Name</th><td><?php echo $users_obj->email; ?></td>
                    </tr>
                </table>
            <?php
            }
            ?>
        </td>                            
    </tr>

</table>

<h5 class="mt-3">Tenants: </h5>
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