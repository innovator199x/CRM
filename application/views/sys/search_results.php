<div class="box-typical box-typical-padding">

    <?php
    // breadcrumbs template
    $bc_items = array(
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/sys/search_results"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
    ?>

    <header class="box-typical-header">

        <div class="box-typical box-typical-padding">
            <?php
            $form_attr = array(
                'id' => 'jform'
            );
            echo form_open('sys/search_results', $form_attr);
            ?>
            <div class="for-groupss row">
                <div class="col-md-8 columns">
                    <div class="row">

                        <div class="col-mdd-3">
                            <select id="search_type" name="search_type" class="form-control">
                                <option value="1" <?php echo ( $this->input->get_post('search_type') == 1 ) ? 'selected="selected"' : null; ?>>Job ID</option>
                                <option value="2" <?php echo ( $this->input->get_post('search_type') == 2 ) ? 'selected="selected"' : null; ?>>Property ID</option>
                                <option value="3" <?php echo ( $this->input->get_post('search_type') == 3 ) ? 'selected="selected"' : null; ?>>Phone</option>
                                <option value="4" <?php echo ( $this->input->get_post('search_type') == 4 ) ? 'selected="selected"' : null; ?>>Address</option>
                                <option value="5" <?php echo ( $this->input->get_post('search_type') == 5 ) ? 'selected="selected"' : null; ?>>Landlord</option>
                                <option value="6" <?php echo ( $this->input->get_post('search_type') == 6 ) ? 'selected="selected"' : null; ?>>Agency</option>
                                <option value="7" <?php echo ( $this->input->get_post('search_type') == 7 ) ? 'selected="selected"' : null; ?>>Building Name</option>
                            </select>
                            <div class="mini_loader"></div>
                        </div>

                        <div class="col-mdd-3">
                            <input type="text" name="search_val" class="form-control" value="<?php echo $this->input->get_post('search_val'); ?>" />
                        </div>

                        <div class="col-md-1 columns">
                            <input type="submit" name="search_submit" value="Go" class="btn">
                        </div>

                    </div>

                </div>
            </div>
            </form>
        </div>
    </header>


    <?php
    // job ID
    if ($this->input->get_post('search_type') == 1) {
        ?>

        <section>
            <div class="body-typical-body">
                <div class="table-responsive">
                    <table class="table table-hover main-table table-striped">
                        <thead>
                            <tr>
                                <th>Job ID</th>
                                <th>Job Type</th>
                                <th>Status</th>
                                <th>Property ID</th>
                                <th>Address</th>                            
                                <th>Agency</th>	
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            if ($search_data) {

                                foreach ($search_data->result() as $row) {

                                    // sales property
                                    $sales_txt = ( $row->is_sales == 1 )?'(Sales)':null;

                                    ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo $this->config->item('crm_link'); ?>/view_job_details.php?id=<?php echo $row->jid; ?>">
                                                <?php echo $row->jid; ?>
                                            </a>
                                        </td>
                                        <td><?php echo $row->job_type; ?></td>
                                        <td><?php echo $row->jstatus; ?></td>
                                        <td>
                                            <a href="<?php echo $this->config->item('crm_link'); ?>/view_property_details.php?id=<?php echo $row->property_id; ?>">
                                                <?php echo $row->property_id; ?>
                                            </a>
                                        </td>
                                        <td><?php echo "{$row->p_address_1} {$row->p_address_2}, {$row->p_address_3} {$sales_txt}"; ?></td>                                    
                                        <td>
                                            <a href="/agency/view_agency_details/<?php echo $row->agency_id; ?>">
                                                <?php echo $row->agency_name; ?>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>						
                        </tbody>

                    </table>
                </div>


            </div>
        </section>

        <?php
    }
    ?>	




    <?php
    // property ID
    if ($this->input->get_post('search_type') == 2) {    
    ?>

        <section>
            <div class="body-typical-body">
                <div class="table-responsive">
                    <table class="table table-hover main-table table-striped">
                        <thead>
                            <tr>
                                <th>Property ID</th>
                                <th>Job Id</th>
                                <th>Address</th>
                                <th>Job Type</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Invoice Amount</th>
                                <th>Balance</th>
                                <th>Agency</th>	
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            if ($search_data) {

                                foreach ($search_data->result() as $row) {

                                    // sales property
                                    $sales_txt = ( $row->is_sales == 1 )?'(Sales)':null;
                                    ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo $this->config->item('crm_link'); ?>/view_property_details.php?id=<?php echo $row->property_id; ?>">
                                                <?php echo $row->property_id; ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?Php
                                            if ((int) $row->jid === 0) {
                                                echo "No Job #";
                                            }
                                            ?>
                                            <a href="<?php echo $this->config->item('crm_link'); ?>/view_job_details.php?id=<?php echo $row->jid; ?>">
                                                <?php echo $row->jid; ?>
                                            </a>
                                        </td>
                                        <td><?php echo "{$row->p_address_1} {$row->p_address_2}, {$row->p_address_3} {$sales_txt}"; ?></td>                                    
                                        <td><?Php echo $row->jtype; ?></td>
                                        <td> <?Php
                                            $status = $row->jstatus;
                                            if ($status === null) {
                                                $status = "--";
                                            }
                                            echo $status;
                                            ?></td>
                                        <td><?php echo ($this->system_model->isDateNotEmpty($row->jdate)==true)?$this->system_model->formatDate($row->jdate,'d/m/Y'):''; ?></td>
                                        <td> <?Php
                                            $amount = $row->invoice_amount;
                                            if ($amount === null) {
                                                $amount = "--";
                                            } else {
                                                $amount="$".$amount;
                                            }
                                            echo $amount
                                            ?></td>
                                        <td> <?Php
                                            $balance = $row->jbalance;
                                            if ($balance === null) {
                                                $balance = "--";
                                            } else {
                                                $balance="$".$balance;
                                            }
                                            echo $balance
                                            ?></td>
                                        <td>
                                            <a href="/agency/view_agency_details/<?php echo $row->agency_id; ?>">
                                                <?php echo $row->agency_name; ?>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>						
                        </tbody>

                    </table>
                </div>


            </div>
        </section>

        <?php
    }
    ?>

<?php
    // property ID
    if ($this->input->get_post('search_type') == 7) {    
    ?>

        <section>
            <div class="body-typical-body">
                <div class="table-responsive">
                    <table class="table table-hover main-table table-striped">
                        <thead>
                            <tr>
                                <th>Property ID</th>
                                <th>Building Name</th>
                                <th>Address</th>
                                <th>Agency</th>
                                <th>Agent Name</th>  
                                <th>Land Lord</th>
                                <th>Land Lord Email</th>
                                <th>Tenant</th>
                                <th>Date</th>                              	
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            if ($search_data) {

                                foreach ($search_data->result() as $row) {

                                    // sales property
                                    $sales_txt = ( $row->is_sales == 1 )?'(Sales)':null;
                                    ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo $this->config->item('crm_link'); ?>/view_property_details.php?id=<?php echo $row->property_id; ?>">
                                                <?php echo $row->property_id; ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php echo $row->building_name;?>
                                        </td>
                                        <td><?php echo "{$row->p_address_1} {$row->p_address_2}, {$row->p_address_3} {$sales_txt}"; ?></td>                                    
                                        <td><?php echo $row->agency_name; ?></td>
                                        <td><?php echo $row->agentname; ?></td>
                                        <td> <?php echo $row->p_landlord_firstname;?></td>
                                        <td> <?php echo $row->p_landlord_email;?></td>
                                        <td><?php echo $row->p_tenant_firstname; ?></td>
                                        <td><?php echo $row->pdate; ?></td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>						
                        </tbody>

                    </table>
                </div>


            </div>
        </section>

        <?php
    }
    ?>




    <?php
// phone and address
    if ($this->input->get_post('search_type') == 3 || $this->input->get_post('search_type') == 4) {
        ?>

        <section>
            <div class="body-typical-body">
                <div class="table-responsive">
                    <table class="table table-hover main-table table-striped">
                        <thead>
                            <tr>
                                <th rowspan="2">Address</th>
                                <?php
                                $num_tenants = 4;
                                for ($i = 1; $i <= $num_tenants; $i++) {
                                    ?>
                                    <th colspan=3>Tenant <?php echo $i ?></th>
                                    <?php
                                }
                                ?>			                            		
                            </tr>
                            <tr>
                                <?php
                                $num_tenants = 4;
                                for ($i = 1; $i <= $num_tenants; $i++) {
                                    ?>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>Landline</th>
                                    <?php
                                }
                                ?>	
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            if ($search_data) {

                                foreach ($search_data->result() as $row) {

                                    // sales property
                                    $sales_txt = ( $row->is_sales == 1 )?'(Sales)':null;                                    
                                    ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo $this->config->item('crm_link'); ?>/view_property_details.php?id=<?php echo $row->property_id; ?>">
                                                <?php echo "{$row->p_address_1} {$row->p_address_2}, {$row->p_address_3} {$sales_txt}"; ?>
                                            </a>
                                        </td>
                                        <?php
                                        $sel_query = "
                                        pt.`tenant_firstname`,
                                        pt.`tenant_lastname`,
                                        pt.`tenant_mobile`,
                                        pt.`tenant_landline`
                                    ";

                                        $params = array(
                                            'sel_query' => $sel_query,
                                            'property_id' => $row->property_id,
                                            'custom_where' => 'pt.active=1',
                                            'limit' => 4,
                                            'offset' => 0,
                                            'display_query' => 0
                                        );
//                                        if ($this->input->get('debug') === 'debug') {
//                                            $params['display_query'] = 1;
//                                        }
                                        $pt_sql = $this->properties_model->get_property_tenants($params);

                                        $tent_name = [];
                                        $tenant_mobile = [];
                                        $tenant_landline = [];
                                        foreach ($pt_sql->result() as $pt_row) {

                                            $tent_name[] = trim($pt_row->tenant_firstname) . " " . trim($pt_row->tenant_lastname);
                                            $tenant_mobile[] = trim($pt_row->tenant_mobile);
                                            $tenant_landline[] = trim($pt_row->tenant_landline);
                                        }
                                        for ($pt_i = 0; $pt_i < $num_tenants; $pt_i++) {
                                            ?>
                                            <td><?php echo $tent_name[$pt_i]; ?></td>
                                            <td><?php echo $tenant_mobile[$pt_i]; ?></td>
                                            <td><?php echo $tenant_landline[$pt_i]; ?></td>
                                            <?php
                                        }
                                        ?>		
                                    </tr>
                                    <?php
                                }
                            }
                            ?>						
                        </tbody>

                    </table>
                </div>


            </div>
        </section>

        <?php
    }
    ?>	



    <?php
// landlord
    if ($this->input->get_post('search_type') == 5) {
        
        ?>

        <section>
            <div class="body-typical-body">
                <div class="table-responsive">
                    <table class="table table-hover main-table table-striped">
                        <thead>
                            <tr>
                                <th rowspan="2">Address</th>
                                <th colspan=4 style="text-align: center;">Landlord</th>		                            		
                            </tr>
                            <tr>
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>Landline</th>
                                <th>Email</th>	
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            if ($search_data) {

                                foreach ($search_data->result() as $row) {

                                    // sales property
                                    $sales_txt = ( $row->is_sales == 1 )?'(Sales)':null;
                                    ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo $this->config->item('crm_link'); ?>/view_property_details.php?id=<?php echo $row->property_id; ?>">
                                                <?php echo "{$row->p_address_1} {$row->p_address_2}, {$row->p_address_3} {$sales_txt}"; ?>
                                            </a>
                                        </td>
                                        <td><?php echo "{$row->landlord_firstname} {$row->landlord_lastname}"; ?></td>
                                        <td><?php echo $row->landlord_mob; ?></td>
                                        <td><?php echo $row->landlord_ph; ?></td>
                                        <td><?php echo $row->landlord_email; ?></td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>						
                        </tbody>

                    </table>
                </div>


            </div>
        </section>

        <?php
    }
    ?>


      <?php
    // agency
    if ($this->input->get_post('search_type') == 6) {
        
        ?>

        <section>
            <div class="body-typical-body">
                <div class="table-responsive">
                    <table class="table table-hover main-table table-striped">
                        <thead>
                            <tr>
                                <th>Agency</th>
                                <th>Address</th>		                            		
                            </tr>                            
                        </thead>

                        <tbody>
                            <?php
                            if ($search_data) {
                                foreach ($search_data->result() as $row) {                             
                                ?>
                                    <tr>
                                        <td>
                                            <a href="/agency/view_agency_details/<?php echo $row->agency_id; ?>">
                                                <?php echo $row->agency_name; ?>
                                            </a>
                                        </td>
                                        <td><?php echo "{$row->address_1} {$row->address_2}, {$row->address_3} {$row->state} {$row->postcode}"; ?></td>                                    
                                    </tr>
                                <?php
                                }
                            }
                            ?>						
                        </tbody>

                    </table>
                </div>


            </div>
        </section>

        <?php
    }
    ?>		



</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

    <h4>Search Results</h4>
    <pre><code>
        <?=$last_query; ?>
    </code></pre>

</div>
<!-- Fancybox END -->

<style>
    .rf_select{
        font-weight: bold;
    }
</style>
<script>




    jQuery(document).ready(function () {



    });
</script>