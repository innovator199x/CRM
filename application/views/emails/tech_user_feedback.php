<table style="width:100%; border: 1px solid #efefef;">
    <tr>
        <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Date</b></td>
        <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Tenant</b></td> 
        <td style="background-color: #404041; color: #ffffff; padding: 5px;"><b>Message</b></td>                
    </tr>    
    <?php                
    foreach( $feedback_sql->result() as $feedback_row ){
        $mob_num = '0'.substr($feedback_row->sar_mobile,2)
    ?>
    <tr>
        <td style="padding: 5px;"><?php echo date('d/m/Y', strtotime($feedback_row->sar_created_date)) ?></td>
        <td style="padding: 5px;">
        <?php
            // get tenants
            $sel_query = "
            pt.`property_tenant_id`,
            pt.`tenant_firstname`,
            pt.`tenant_lastname`,
            pt.`tenant_mobile`,
            pt.`tenant_email`
            ";
            $params = array(
            'sel_query' => $sel_query,
            'property_id' => $feedback_row->property_id,
            'pt_active' => 1,
            'display_query' => 0
            );
            $pt_sql = $this->properties_model->get_property_tenants($params);
            if( $pt_sql->num_rows() > 0 ){

                // loop through tenants
                foreach($pt_sql->result() as $pt_row){
                    $tenants_num = str_replace(' ', '', trim($pt_row->tenant_mobile));
                    if( $tenants_num != '' && $tenants_num == $mob_num ){
                        $tenant_name = $pt_row->tenant_firstname;
                    }
                }
            }

            echo $tenant_name;
            ?>
        </td>
        <td style="padding: 5px;"><?php echo $feedback_row->response; ?></td>
    </tr>
    <?php
    }    
    ?>
</table>