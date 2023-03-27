<div class="table-responsive">
            <table class="table table-hover main-table">
                <thead>
                    <tr>                        
                        <th>Job #</th>
                        <th>Job Type</th>
                        <th>Status</th>
                        <th>Service</th>							
                        <th>Address</th>
                        <th><?php echo $this->gherxlib->getDynamicState($this->config->item('country')) ?></th>
                        <th>Agency Name</th>
                    </tr>
                </thead>

                <tbody>
                    
                <?php 
                if($lists->num_rows()>0){
                    $i = 0;
                    foreach($lists->result_array() as $d){

                        if( 
                            ( $current_tab == 'regular_clients' && $d['allow_upfront_billing'] != 1 ) || 
                            ( $current_tab == 'upfront_clients' && $d['allow_upfront_billing'] == 1 ) 
                        ){

                        $paddress = "{$d['address_1']} {$d['address_2']}, {$d['address_3']}";
                ?>
                    <tr <?php echo ($i%2==0)?'style="border-right: 1px solid #cccccc; background-color: #efefef;"':''; ?>>                        
                        <td><?php echo $this->gherxlib->crmLink('vjd',$d['id'],$d['id']); ?></td>
                        <td><?php echo $d['job_type']; ?></td>
                        <td><?php echo $d['status']; ?></td>
                        <td>
                            <?php
                            // display icons
                            $job_icons_params = array(
                                'service_type' => $d['jservice'],
                                'job_type' => $d['job_type'],
                                'sevice_type_name' => $d['ajt_type']
                            );
                            echo $this->system_model->display_job_icons($job_icons_params);
                            ?>                                                        
                        </td>
                        <td><?php echo $this->gherxlib->crmLink('vpd',$d['property_id'],$paddress); ?></td>
                        <td><?php echo $d['state']; ?></td>
                        <td>
                            <?php echo $this->gherxlib->crmLink('vad',$d['agency_id'],$d['agency_name']); ?>                         
                        </td>
                    </tr>
                    <?php 
                        $dup_sql2 = $this->daily_model->getOtherMultipleJobs($d['property_id'],$d['id']);
                        foreach($dup_sql2->result_array() as $d2){

                            $paddress = "{$d2['address_1']} {$d2['address_2']}, {$d2['address_3']}";
                    ?>
                        
                        <tr <?php echo ($i%2==0)?'style="border-right: 1px solid #cccccc; background-color: #efefef;"':''; ?>>                            
                            <td><?php echo $this->gherxlib->crmLink('vjd',$d2['id'],$d2['id']); ?></td>
                            <td><?php echo $d2['job_type']; ?></td>
                            <td><?php echo $d2['status']; ?></td>
                            <td>	        
                                <?php
                                // display icons
                                $job_icons_params = array(
                                    'service_type' => $d2['jservice'],
                                    'job_type' => $d2['job_type'],
                                    'sevice_type_name' => $d2['ajt_type']
                                );
                                echo $this->system_model->display_job_icons($job_icons_params);
                                ?>                                                            
                            </td>
                            <td><?php echo $this->gherxlib->crmLink('vpd',$d2['property_id'],$paddress); ?></td>
                            <td><?php echo $d2['state']; ?></td>
                            <td>
                                <?php echo $this->gherxlib->crmLink('vad',$d2['agency_id'],$d2['agency_name']); ?>                               
                            </td>
                        </tr>

                <?php
                    }
                    $i++;

                }

                     }
                }else{
                    echo "<tr><td colspan='8'>No Data</td></tr>";
                }
                ?>
                    
                </tbody>

            </table>
        </div>

        <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
        <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>