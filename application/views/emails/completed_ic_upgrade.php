<?php
$th_css = 'text-align:left; border-bottom: none; padding-top: 10px; padding-bottom: 9px; background: #f6f8fa;';
$td_css = 'padding: 10px 0; text-align:left;';
?>
<table class="table table-hover main-table" style="width:100%;">

    <thead>
        <tr>
            <th style="<?php echo $th_css; ?>">Date</th>
            <th style="<?php echo $th_css; ?>">Job Type</th>            
            <th style="<?php echo $th_css; ?>">Price</th>
            <th style="<?php echo $th_css; ?>">Address</th>
            <th style="<?php echo $th_css; ?>">Prop. Created</th>
            <th style="<?php echo $th_css; ?>"><?php echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></th>
            <th style="<?php echo $th_css; ?>">Agency</th>			
        </tr>
    </thead>

    <tbody>
    <?php
    foreach( $lists->result_array() as $list_item ){	
    ?>
    <tr>
        <td style="<?php echo $td_css; ?>">
            <?php echo $this->system_model->formatDate($list_item['j_date'],'d/m/Y'); ?>
        </td>
        <td style="<?php echo $td_css; ?>">
            <?php 
            echo $this->gherxlib->getJobTypeAbbrv($list_item['j_type']);             
            ?>
        </td>
        <td style="<?php echo $td_css; ?>">
            $<?php echo number_format($list_item['invoice_amount'],2); ?>
        </td>
        <td style="<?php echo $td_css; ?>">
            <?php
            echo $list_item['p_address_1']." ".$list_item['p_address_2'].", ".$list_item['p_address_3'];            
            ?>
        </td>
        <td style="<?php echo $td_css; ?>">
            <?php echo $this->system_model->formatDate($list_item['p_created'],'d/m/Y'); ?>                                
        </td>
        <td style="<?php echo $td_css; ?>">
            <?php echo $list_item['p_state']; ?>
        </td>
        <td style="<?php echo $td_css; ?>">
            <?php echo $list_item['agency_name']; ?>
        </td>
    </tr>
    <?php 
    }	
    ?>
    <tr>
        <td colspan="2"><b>TOTAL JOBS: <?php echo $total_rows; ?></b></td>
        <td colspan="5"><b>$<?php echo number_format($invoice_amount_tot,2); ?></b></td>
    </tr>
    </tbody>

</table>