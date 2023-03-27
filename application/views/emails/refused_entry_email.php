<!-- CONTENT START HERE -->
<table style="width:100%; border: 1px solid #efefef;">
    <tr>
        <td><b>Not completed due to: </b></td>
        <td style="padding: 5px;">Refused Entry</td>
    </tr>
    <tr>
        <td><b>Address: </b></td>
        <td style="padding: 5px;"><?php echo $p_address; ?></td>
    </tr>

    <?php
    if( $property_id > 0 ){

        // get tenants 
        $sel_query = "
            pt.`property_tenant_id`,
            pt.`tenant_firstname`,
            pt.`tenant_lastname`,
            pt.`tenant_mobile`
        ";
        $params = array(
            'sel_query' => $sel_query,
            'property_id' => $property_id,
            'pt_active' => 1,
            'display_query' => 0
        );
        $pt_sql = $this->properties_model->get_property_tenants($params);
        if( $pt_sql->num_rows() > 0 ){
            foreach($pt_sql->result() as $index => $pt_row){ ?>
                <tr>
                    <td><b>Tenant <?php echo $index+1; ?>: </b></td>
                    <td style="padding: 5px;"><?php echo "{$pt_row->tenant_firstname} {$pt_row->tenant_lastname}"; ?></td>
                </tr>
            <?php
            }      

        }          
    }
    ?>
    

</table>
<!-- CONTENT END HERE -->