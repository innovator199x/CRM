<div class="text-left">

<div class="text-left">
    <a class="btn btm_current_statement" href="/accounts/statement_pdf/<?php echo $agency_id; ?>">Current Statement</a>
    <br/>
    <small class="text-red">Statement shows all invoices after 1/12/19</small>
    <p>&nbsp;</p>
</div>

<?php
 echo form_open("/agency/update_agency/{$agency_id}/{$tab}","id=vad_form");  
 $hidden_input_data_agency_id = array(
    'type'  => 'hidden',
    'name'  => 'agency_id',
    'id'    => 'agency_id',
    'value' => $agency_id,
    'class' => 'agency_id'
);
echo form_input($hidden_input_data_agency_id);
?>
    <div class="row">
        <div class="col-md-10 columns">
            <div class="form-group">
                <div class="card-blockss">
                    <?php $statements_agency_comments_ts =  ( $this->system_model->isDateNotEmpty($row['statements_agency_comments_ts']) ) ? date('d/m/Y H:i', strtotime($row['statements_agency_comments_ts'])) : ''; ?>
                    <label class="form-label">Statement Message (Appears on Agency Accounts Statement) &nbsp;<span class="statements_agency_comments_ts_span"><?php echo $statements_agency_comments_ts; ?></span></label>
                    <textarea class="form-control addtextarea formtextarea statements_agency_comments" title="Statement Agency Comments" name="statements_agency_comments" id="statements_agency_comments"><?php echo $row['statements_agency_comments'] ?></textarea>
                    <input type="hidden" name="statements_agency_comments_ts" value="<?php echo $row['statements_agency_comments_ts'] ?>" >
                    <input type="hidden" name="og_statements_agency_comments" value="<?php echo $row['statements_agency_comments']; ?>">
                </div>
            </div>
        </div>
        <div class="col-md-2 columns">
            <label class="form-label">&nbsp;</label>
            <button class="btn">Update</button>
        </div>
    </div>
</form>

<h4>Account New Logs</h4>
        <table class="table table-hover main-table">
        <thead>
            <tr>
                <th>Date</th>  
                <th>Time</th>  
                <th>Title</th>  
                <th>Who</th>  
                <th>Details</th>  
            </tr>
        </thead>
        <tbody>
            <?php foreach($new_logs as $row){ ?>
                
                <tr data-logid="<?php echo $row['log_id']; ?>">
                    <td>
                        <?php echo date('d/m/Y',strtotime($row['created_date'])); ?>
                    </td>
                    <td>
                        <?php echo date('H:i',strtotime($row['created_date'])); ?>
                    </td>
                    <td>
                        <?php echo $row['title_name']; ?>
                    </td>
                    <td>
                        <?php
                        if( $row['StaffID'] != '' ){ // sats staff
                            echo "{$row['FirstName']} {$row['LastName']}";
                        }else{ // agency portal users
                            echo "{$row['fname']} {$row['lname']}";
                        }
                        ?>
                    </td>
                    <td>
                        <?php 
                        $params = array(
                            'log_details' => $row['details'],
                            'log_id' => $row['log_id']
                        );								
                        echo $this->agency_model->parseDynamicLink_to_crm($params);
                        ?>
                    </td>
                </tr>

            <?php } ?>
           
        </tbody>
    </table>

    <nav aria-label="Page navigation example" style="text-align:center">
        <?php echo $pagination; ?>
    </nav>

    <div class="pagi_count text-center">
        <?php echo $pagi_count; ?>
    </div>

</div>