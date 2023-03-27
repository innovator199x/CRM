<?php
$from = date('Y-m-01');
$to = date('Y-m-t');

//for sales result
$ajt_sql2 = $this->reports_model->getDynamicServices();
$ajt_arr = [];
foreach ($ajt_sql2->result_array() as $ajt2) {

    switch ($ajt2['id']) {
        case 8:
            $ajt_name = 'SA SS';
            break;
        case 9:
            $ajt_name = 'SA SS CW';
            break;
        case 11:
            $ajt_name = 'SA WM';
            break;
        case 12:
            $ajt_name = 'SA (IC)';
            break;
        case 13:
            $ajt_name = 'SA SS (IC)';
            break;
        case 14:
            $ajt_name = 'SA CW SS (IC)';
            break;
        default:
            $ajt_name = $ajt2['short_name'];
    }

    $ajt_arr[] = array(
        'id' => $ajt2['id'],
        'type' => $ajt2['type'],
        'short_name' => $ajt2['short_name'],
        'short_name_wspace' => $ajt_name
    );
}
$row_count = $ajt_sql2->num_rows();

// distint sales rep
$sr_sql = $this->reports_model->distinct_salesrep($from, $to);
$sales_arr = array();
foreach ($sr_sql->result_array() as $sr) {

    $sales_result_tot = 0;
    $sales_arr[] = array(
        'saleperson_id' => $sr['salesrep'],
        'salesperson_name' => "{$sr['FirstName']} {$sr['LastName']}"
    );
}


$sales_result_overall_tot = 0;
?>
<!-- SALES RESULT TABLE -->
<div id="search_result">
    <div class="table_top_head">Sales Results</div>
    <table class="table table-hover main-table table_border">
        <thead>
            <tr>
                <th>Staff</th>

                <?php
                foreach ($ajt_arr as $row) {
                    if ($this->config->item('country') == 2) { //NZ ONLY > removed other services
                        if ($row['id'] == 2) {
                            echo "<th>{$row['short_name_wspace']}</ht>";
                        }
                    } else { //AU ONLY > display all services
                        echo "<th>{$row['short_name_wspace']}</ht>";
                    }
                }
                ?>

                <th>Total</th>
            </tr>
        </thead>

        <tbody>
<?php
foreach ($sales_arr as $sales) {
    if($sales['saleperson_id']>0){
    $sales_result_tot = 0;
    ?>

                <tr>
                    <td data-id="<?php echo $sales['saleperson_id']; ?>">
    <?php echo $sales['salesperson_name']; ?>
                    </td>
                        <?php
                        foreach ($ajt_arr as $ajt) {
                            if ($this->config->item('country') == 2) { //AU remove other services
                                if ($ajt['id'] == 2) {
                                    ?>
                                <td>
                                <?php
                                $sa_query = $this->reports_model->get_num_services($sales['saleperson_id'], $ajt['id'], $from, $to, $this->config->item('country'),1);
                                $sa = $sa_query->row()->p_count;
                                echo ($sa > 0) ? $sa : '';
                                $sales_result_tot += $sa;
                                ?>
                                </td>
                                    <?php
                                }
                            } else {
                                ?>
                            <td>
                            <?php
                            $sa_query = $this->reports_model->get_num_services($sales['saleperson_id'], $ajt['id'], $from, $to, $this->config->item('country'),1);
                            $sa = $sa_query->row()->p_count;
                            echo ($sa > 0) ? $sa : '';
                            $sales_result_tot += $sa;
                            ?>
                            </td> 
                                <?php
                            }
                        }
                        ?>
                    <td>
                    <?php
                    echo ( $sales_result_tot > 0 ) ? $sales_result_tot : '';
                    $sales_result_overall_tot += $sales_result_tot;
                    ?>
                    </td>
                </tr>

                        <?php
                    }}
                    ?>
            <tr style="background:#f6f8fa;">
                <td><strong>Total</trong></td> 
            <?php
            $awaw_cnt = 0;
            foreach ($ajt_arr as $ajt) {
                if ($this->config->item('country') == 2) {
                    if ($awaw_cnt == 0) {
                        ?>

                            <td>&nbsp;</td>
                            <?php
                        }
                    } else {
                        echo " <td>&nbsp;</td>";
                    }
                    $awaw_cnt++;
                }
                ?>
                <td><strong><?php echo $sales_result_overall_tot; ?></strong></td> 
            </tr>
        </tbody>

    </table>
</div>
<!-- SALES RESULT TABLE END -->