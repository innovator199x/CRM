<?php
// get alarm power for existing alarms(excluding batteries)
$alam_pwr_for_existing_alarm_sql = $this->db->query("
SELECT `alarm_pwr_id`, `alarm_pwr`, `is_li`, `is_240v` 
FROM `alarm_pwr`
WHERE `alarm_pwr_id` != 6
ORDER BY `alarm_pwr`
");

if( $job_row->agency_id > 0 ){

    // get alarm power for new alarms
    $alam_pwr_for_new_alarm_sql = $this->db->query("
        SELECT ap.`alarm_pwr_id`, ap.`alarm_pwr`, ap.`is_li`, ap.`is_240v` 
        FROM `agency_alarms` AS aa
        LEFT JOIN `alarm_pwr` AS ap ON aa.`alarm_pwr_id` = ap.`alarm_pwr_id`
        WHERE aa.`agency_id` = {$job_row->agency_id}
        ORDER BY `alarm_pwr`
    ");

}            

// get existing alarms
$existing_alarms_sql = $this->db->query("
SELECT 
    al.`alarm_id`,
    al.`ts_position`,
    al.`alarm_type_id`,    
    al.`make`,
    al.`model`,
    al.`alarm_power_id`,
    al.`ts_required_compliance`,
    al.`ts_expiry`,
    al.`ts_added`,
    al.`expiry`,
    al.`ts_db_rating`,
    al.`ts_fixing`,
    al.`ts_cleaned`,
    al.`ts_newbattery`,
    al.`ts_testbutton`,
    al.`ts_visualind`,
    al.`ts_meetsas1851`,
    al.`ts_discarded`,
    al.`ts_discarded_reason`,
    al.`rec_batt_exp`,
    al.`new`,
    al.`ts_alarm_sounds_other`
FROM alarm AS al 
WHERE al.job_id = {$this->input->get_post('job_id')}
AND al.`new` != 1
ORDER BY al.alarm_id ASC
");
$existing_alarms_arr = $existing_alarms_sql->result();

// get new alarms
$new_alarms_sql = $this->db->query("
SELECT 
    al.`alarm_id`,
    al.`ts_position`,
    al.`alarm_type_id`,    
    al.`make`,
    al.`model`,
    al.`alarm_power_id`,
    al.`ts_required_compliance`,
    al.`ts_expiry`,
    al.`expiry`,
    al.`ts_db_rating`,
    al.`alarm_reason_id`,
    al.`rec_batt_exp`,
    al.`new`
FROM alarm AS al 
WHERE al.job_id = {$this->input->get_post('job_id')}
AND al.`new` = 1
ORDER BY al.alarm_id ASC
");
$new_alarms_arr = $new_alarms_sql->result();

// get alarm type
$alarm_type_sql = $this->db->query("
SELECT `alarm_type_id`, `alarm_type` 
FROM `alarm_type` 
WHERE `alarm_job_type_id` = 2
ORDER BY `alarm_type` DESC
");

// get alarm reason
$alarm_reason_sql = $this->db->query("
SELECT `alarm_reason_id`, `alarm_reason` 
FROM `alarm_reason` 
WHERE `alarm_job_type_id` = 2 
ORDER BY `alarm_reason` ASC
");

// get alarm reason
$alarm_discarded_reason_sql = $this->db->query("
SELECT `id`, `reason`
FROM `alarm_discarded_reason`
WHERE `active` = 1
ORDER BY `reason` ASC 
");
?>
<style>
.fancybox-content {
    max-width: 78%;
}
.preferred_alarm_div{
    position: relative;
    top: 5px;
    left: 15px;
}
</style>
  

<div id="alarms_listing_div">
<?php
if( count($existing_alarms_arr) > 0 ){
?>
    <h5 class="float-left">Existing Alarms</h5>
    <?php
    if( $this->config->item('country') == 1 ){ //AU

        if( $job_row->p_state == 'QLD' ){ // QLD

            if( $job_row->preferred_alarm_id > 0 ){
            ?>

                <div class="preferred_alarm_div text-danger float-left">USE <?php echo strtoupper($job_row->pref_alarm_make); ?> ALARMS</div>
    
            <?php
            }
            
        }else{ // non-QLD
        ?>
            <div class="preferred_alarm_div text-danger float-left">USE <?php echo $this->system_model->display_free_emerald_or_paid_brooks($job_row->agency_id); ?> ALARMS</div>
        <?php
        }

    }else{ // NZ ?>
        <div class="preferred_alarm_div text-danger float-left">USE <?php echo $this->system_model->display_orca_or_cavi_alarms($job_row->agency_id); ?> ALARMS</div>
    <?php
    }    
    ?>    
    <div class="float-none"></div>

    <table class="table main-table table-hover existing_alarm_tbl">

        <thead>
            <tr>
                <th></th>
                <?php
                foreach( $existing_alarms_arr as $index => $existing_alarm ){ ?>
                    <th>Alarm <?php echo ($index+1); ?> </th>
                <?php    
                }
                ?>
            </tr>
        </thead>
        
        <tbody>
                
            <tr>
                <th>Position</th>
                <?php
                foreach( $existing_alarms_arr as $index => $existing_alarm ){ ?>
                    <td>
                        <input type="text" name="sa_position" class="form-control sa_position" data-db_table_field="ts_position" data-col="<?php echo $existing_alarm->alarm_id; ?>" value="<?php echo strtoupper($existing_alarm->ts_position); ?>" />
                    </td>
                <?php    
                }
                ?>
            </tr>
            <tr>
                <th>Type</th>
                <?php
                foreach( $existing_alarms_arr as $index => $existing_alarm ){ ?>
                    <td>
                        <select name="sa_type" class="form-control sa_type" data-db_table_field="alarm_type_id" data-col="<?php echo $existing_alarm->alarm_id; ?>">
                            <option value="">---</option> 
                            <?php
                            foreach( $alarm_type_sql->result() as $al_type ){ ?>
                                <option value="<?php echo $al_type->alarm_type_id ?>" <?php echo ( $al_type->alarm_type_id == $existing_alarm->alarm_type_id )?'selected':null; ?>><?php echo $al_type->alarm_type; ?></option>
                            <?php
                            }
                            ?>                     
                        </select>
                    </td>
                <?php    
                }
                ?>
            </tr>
            <tr>
                <th>Make</th>
                <?php
                foreach( $existing_alarms_arr as $index => $existing_alarm ){ ?>
                    <td><?php echo strtoupper($existing_alarm->make); ?></td>
                <?php    
                }
                ?>
            </tr>
            <tr>
                <th>Model</th>
                <?php
                foreach( $existing_alarms_arr as $index => $existing_alarm ){ ?>
                    <td><?php echo strtoupper($existing_alarm->model); ?></td>
                <?php    
                }
                ?> 
            </tr>
            <tr>           
                <th>Power</th>
                <?php
                foreach( $existing_alarms_arr as $index => $existing_alarm ){ ?>
                    <td>
                        <select name="sa_power" class="form-control sa_power" data-db_table_field="alarm_power_id" data-col="<?php echo $existing_alarm->alarm_id; ?>">
                            <option value="">---</option>   
                            <?php
                            foreach( $alam_pwr_for_existing_alarm_sql->result() as $alam_pwr_for_existing_alarm_row ){ ?>
                                <option value="<?php echo $alam_pwr_for_existing_alarm_row->alarm_pwr_id ?>" <?php echo ( $alam_pwr_for_existing_alarm_row->alarm_pwr_id == $existing_alarm->alarm_power_id )?'selected':null; ?> data-is_li="<?php echo $alam_pwr_for_existing_alarm_row->is_li; ?>" data-is_240v="<?php echo $alam_pwr_for_existing_alarm_row->is_240v; ?>"><?php echo $alam_pwr_for_existing_alarm_row->alarm_pwr; ?></option>
                            <?php
                            }
                            ?>                 
                        </select>
                    </td>
                <?php    
                }
                ?>
            </tr>
            <tr>
                <th>Required for Compliance</th>
                <?php
                foreach( $existing_alarms_arr as $index => $existing_alarm ){ ?>
                    <td>
                        <div class="radio">
                            <input type="radio" name="ts_required_compliance<?php echo $existing_alarm->alarm_id; ?>" id="ts_required_compliance_yes<?php echo $existing_alarm->alarm_id; ?>" class="chk_yes inline-block sa_rfc sa_rfc<?php echo $existing_alarm->alarm_id; ?>" data-db_table_field="ts_required_compliance" data-col="<?php echo $existing_alarm->alarm_id; ?>" value="1" <?php echo (  $existing_alarm->ts_required_compliance == 1 )?'checked':null; ?> />
                            <label class="inline-block" for="ts_required_compliance_yes<?php echo $existing_alarm->alarm_id; ?>">Yes</label>
                        </div>
                        <div class="radio">
                            <input type="radio" name="ts_required_compliance<?php echo $existing_alarm->alarm_id; ?>" id="ts_required_compliance_no<?php echo $existing_alarm->alarm_id; ?>" class="chk_no inline-block sa_rfc sa_rfc<?php echo $existing_alarm->alarm_id; ?>" data-db_table_field="ts_required_compliance" data-col="<?php echo $existing_alarm->alarm_id; ?>" value="0" <?php echo (  $existing_alarm->ts_required_compliance == 0 && is_numeric($existing_alarm->ts_required_compliance) )?'checked':null; ?> />
                            <label class="inline-block" for="ts_required_compliance_no<?php echo $existing_alarm->alarm_id; ?>">No</label>
                        </div>
                    </td>
                <?php    
                }
                ?> 
            </tr>
            <tr>     
                <th>Alarm Expiry (YYYY)</th>   
                <?php
                foreach( $existing_alarms_arr as $index => $existing_alarm ){ ?>
                    <td>                        
                        <input type="number" name="sa_ts_expiry" class="form-control sa_ts_expiry sa_ts_expiry<?php echo $existing_alarm->alarm_id; ?>" value="<?php echo $existing_alarm->ts_expiry; ?>" data-db_table_field="ts_expiry" data-col="<?php echo $existing_alarm->alarm_id; ?>" />
                        <input type="hidden" class="sa_expiry_hid" value="<?php echo $existing_alarm->expiry; ?>" data-db_table_field="expiry" data-col="<?php echo $existing_alarm->alarm_id; ?>" />
                    
                        <span class="expiry_dont_match_span"></span>
                    </td>
                <?php    
                }
                ?>
            </tr>

            <tr>
                <th>Battery Expiry (MM/YY)</th>
                <?php
                foreach( $existing_alarms_arr as $index => $existing_alarm ){ 
                $rec_batt_exp =  ( $existing_alarm->rec_batt_exp != '' )?date("m",strtotime($existing_alarm->rec_batt_exp)).date("y",strtotime($existing_alarm->rec_batt_exp)):null;    
                ?>
                    <td>
                        <input type="text" pattern="[0-9\/]*" inputmode="numeric" class="form-control rec_batt_exp" value="<?php echo $rec_batt_exp; ?>" data-col="<?php echo $existing_alarm->alarm_id; ?>" data-db_table_field="rec_batt_exp" />
                        <input type="hidden" class="rec_batt_exp_full" value="<?php echo $existing_alarm->rec_batt_exp; ?>" data-col="<?php echo $existing_alarm->alarm_id; ?>" />
                    </td>
                <?php    
                }
                ?>
            </tr>

            <tr>    
                <th>dB Reading (Minimum 85dB at 3 metres)</th>
                <?php
                foreach( $existing_alarms_arr as $index => $existing_alarm ){ ?>
                    <td>
                        <input type="number" name="sa_db" class="form-control sa_db" value="<?php echo $existing_alarm->ts_db_rating; ?>" data-db_table_field="ts_db_rating" data-col="<?php echo $existing_alarm->alarm_id; ?>" />
                    </td>
                <?php    
                }
                ?>
            </tr>
           
            <tr>           
                <th>Securely Fixed</th>
                <?php
                foreach( $existing_alarms_arr as $index => $existing_alarm ){ ?>
                    <td>
                        <div class="radio">
                            <input type="radio" name="ts_fixing<?php echo $existing_alarm->alarm_id; ?>" id="ts_fixing_yes<?php echo $existing_alarm->alarm_id; ?>" class="chk_yes inline-block ts_fixing ts_fixing<?php echo $existing_alarm->alarm_id; ?>" data-db_table_field="ts_fixing" data-col="<?php echo $existing_alarm->alarm_id; ?>" value="1" <?php echo (  $existing_alarm->ts_fixing != '' )?( ( $existing_alarm->ts_fixing == 1 )?'checked':null ):'checked'; ?> />
                            <label class="inline-block" for="ts_fixing_yes<?php echo $existing_alarm->alarm_id; ?>">Yes</label>
                        </div>
                        <div class="radio">
                            <input type="radio" name="ts_fixing<?php echo $existing_alarm->alarm_id; ?>" id="ts_fixing_no<?php echo $existing_alarm->alarm_id; ?>" class="chk_no inline-block ts_fixing ts_fixing<?php echo $existing_alarm->alarm_id; ?>" data-db_table_field="ts_fixing" data-col="<?php echo $existing_alarm->alarm_id; ?>" value="0" <?php echo (  $existing_alarm->ts_fixing == 0 && is_numeric($existing_alarm->ts_fixing) )?'checked':null; ?> />
                            <label class="inline-block" for="ts_fixing_no<?php echo $existing_alarm->alarm_id; ?>">No</label>
                        </div>
                    </td>
                <?php    
                }
                ?>
            </tr>
            <tr>
                <th>Cleaned</th>
                <?php
                foreach( $existing_alarms_arr as $index => $existing_alarm ){ ?>
                    <td>
                        <div class="radio">
                            <input type="radio" name="ts_cleaned<?php echo $existing_alarm->alarm_id; ?>" id="ts_cleaned_yes<?php echo $existing_alarm->alarm_id; ?>" class="chk_yes inline-block ts_cleaned ts_cleaned<?php echo $existing_alarm->alarm_id; ?>" data-db_table_field="ts_cleaned" data-col="<?php echo $existing_alarm->alarm_id; ?>" value="1" <?php echo (  $existing_alarm->ts_cleaned != '' )?( ( $existing_alarm->ts_cleaned == 1 )?'checked':null ):'checked'; ?> />
                            <label class="inline-block" for="ts_cleaned_yes<?php echo $existing_alarm->alarm_id; ?>">Yes</label>
                        </div>
                        <div class="radio">
                            <input type="radio" name="ts_cleaned<?php echo $existing_alarm->alarm_id; ?>" id="ts_cleaned_no<?php echo $existing_alarm->alarm_id; ?>" class="chk_no inline-block ts_cleaned ts_cleaned<?php echo $existing_alarm->alarm_id; ?>" data-db_table_field="ts_cleaned" data-col="<?php echo $existing_alarm->alarm_id; ?>" value="0" <?php echo (  $existing_alarm->ts_cleaned == 0 && is_numeric($existing_alarm->ts_cleaned) )?'checked':null; ?> />
                            <label class="inline-block" for="ts_cleaned_no<?php echo $existing_alarm->alarm_id; ?>">No</label>
                        </div>
                    </td>
                <?php    
                }
                ?>
            </tr>
            <tr>
                <th>Battery Tested and Replaced if Required (Where replaceable)</th>
                <?php
                foreach( $existing_alarms_arr as $index => $existing_alarm ){ ?>
                    <td>
                        <div class="radio">
                            <input type="radio" name="ts_newbattery<?php echo $existing_alarm->alarm_id; ?>" id="ts_newbattery_yes<?php echo $existing_alarm->alarm_id; ?>" class="chk_yes inline-block ts_newbattery ts_newbattery<?php echo $existing_alarm->alarm_id; ?>" data-db_table_field="ts_newbattery" data-col="<?php echo $existing_alarm->alarm_id; ?>" value="1" <?php echo (  $existing_alarm->ts_newbattery != '' )?( ( $existing_alarm->ts_newbattery == 1 )?'checked':null ):'checked'; ?> />
                            <label class="inline-block" for="ts_newbattery_yes<?php echo $existing_alarm->alarm_id; ?>">Yes</label>
                        </div>
                        <div class="radio">
                            <input type="radio" name="ts_newbattery<?php echo $existing_alarm->alarm_id; ?>" id="ts_newbattery_no<?php echo $existing_alarm->alarm_id; ?>" class="chk_no inline-block ts_newbattery ts_newbattery<?php echo $existing_alarm->alarm_id; ?>" data-db_table_field="ts_newbattery" data-col="<?php echo $existing_alarm->alarm_id; ?>" value="0" <?php echo (  $existing_alarm->ts_newbattery == 0 && is_numeric($existing_alarm->ts_newbattery)  )?'checked':null; ?> />
                            <label class="inline-block" for="ts_newbattery_no<?php echo $existing_alarm->alarm_id; ?>">No</label>
                        </div>
                    </td>
                <?php    
                }
                ?>
            </tr>
            <tr>
                <th>Test Button Working</th>
                <?php
                foreach( $existing_alarms_arr as $index => $existing_alarm ){ ?>
                    <td>
                        <div class="radio">
                            <input type="radio" name="ts_testbutton<?php echo $existing_alarm->alarm_id; ?>" id="ts_testbutton_yes<?php echo $existing_alarm->alarm_id; ?>" class="chk_yes inline-block ts_testbutton ts_testbutton<?php echo $existing_alarm->alarm_id; ?>" data-db_table_field="ts_testbutton" data-col="<?php echo $existing_alarm->alarm_id; ?>" value="1" <?php echo (  $existing_alarm->ts_testbutton != '' )?( ( $existing_alarm->ts_testbutton == 1 )?'checked':null ):'checked'; ?> />
                            <label class="inline-block" for="ts_testbutton_yes<?php echo $existing_alarm->alarm_id; ?>">Yes</label>
                        </div>
                        <div class="radio">
                            <input type="radio" name="ts_testbutton<?php echo $existing_alarm->alarm_id; ?>" id="ts_testbutton_no<?php echo $existing_alarm->alarm_id; ?>" class="chk_no inline-block ts_testbutton ts_testbutton<?php echo $existing_alarm->alarm_id; ?>" data-db_table_field="ts_testbutton" data-col="<?php echo $existing_alarm->alarm_id; ?>" value="0" <?php echo (  $existing_alarm->ts_testbutton == 0 && is_numeric($existing_alarm->ts_testbutton)  )?'checked':null; ?> />
                            <label class="inline-block" for="ts_testbutton_no<?php echo $existing_alarm->alarm_id; ?>">No</label>
                        </div>
                    </td>
                <?php    
                }
                ?>
            </tr>
            <tr>
                <th>Visual Indicators Working</th>
                <?php
                foreach( $existing_alarms_arr as $index => $existing_alarm ){ ?>
                    <td>
                        <div class="radio">
                            <input type="radio" name="ts_visualind<?php echo $existing_alarm->alarm_id; ?>" id="ts_visualind_yes<?php echo $existing_alarm->alarm_id; ?>" class="chk_yes inline-block ts_visualind ts_visualind<?php echo $existing_alarm->alarm_id; ?>" data-db_table_field="ts_visualind" data-col="<?php echo $existing_alarm->alarm_id; ?>" value="1" <?php echo (  $existing_alarm->ts_visualind != '' )?( ( $existing_alarm->ts_visualind == 1 )?'checked':null ):'checked'; ?> />
                            <label class="inline-block" for="ts_visualind_yes<?php echo $existing_alarm->alarm_id; ?>">Yes</label>
                        </div>
                        <div class="radio">
                            <input type="radio" name="ts_visualind<?php echo $existing_alarm->alarm_id; ?>" id="ts_visualind_no<?php echo $existing_alarm->alarm_id; ?>" class="chk_no inline-block ts_visualind ts_visualind<?php echo $existing_alarm->alarm_id; ?>" data-db_table_field="ts_visualind" data-col="<?php echo $existing_alarm->alarm_id; ?>" value="0" <?php echo (  $existing_alarm->ts_visualind == 0 && is_numeric($existing_alarm->ts_visualind) )?'checked':null; ?> />
                            <label class="inline-block" for="ts_visualind_no<?php echo $existing_alarm->alarm_id; ?>">No</label>
                        </div>
                    </td>
                <?php    
                }
                ?>
            </tr>

            <?php
            // only show on IC service type
            if( $is_ic_service == 1 ){ ?>

                <tr>
                    <th>Does Alarm sound all other alarms?</th>
                    <?php
                    foreach( $existing_alarms_arr as $index => $existing_alarm ){ ?>
                        <td>
                            <div class="radio">
                                <input type="radio" name="ts_alarm_sounds_other<?php echo $existing_alarm->alarm_id; ?>" id="ts_alarm_sounds_other_yes<?php echo $existing_alarm->alarm_id; ?>" class="chk_yes inline-block ts_alarm_sounds_other ts_alarm_sounds_other<?php echo $existing_alarm->alarm_id; ?>" data-db_table_field="ts_alarm_sounds_other" data-col="<?php echo $existing_alarm->alarm_id; ?>" value="1" <?php echo (  $existing_alarm->ts_alarm_sounds_other != '' )?( ( $existing_alarm->ts_alarm_sounds_other == 1 )?'checked':null ):'checked'; ?> />
                                <label class="inline-block" for="ts_alarm_sounds_other_yes<?php echo $existing_alarm->alarm_id; ?>">Yes</label>
                            </div>
                            <div class="radio">
                                <input type="radio" name="ts_alarm_sounds_other<?php echo $existing_alarm->alarm_id; ?>" id="ts_alarm_sounds_other_no<?php echo $existing_alarm->alarm_id; ?>" class="chk_no inline-block ts_alarm_sounds_other ts_alarm_sounds_other<?php echo $existing_alarm->alarm_id; ?>" data-db_table_field="ts_alarm_sounds_other" data-col="<?php echo $existing_alarm->alarm_id; ?>" value="0" <?php echo (  $existing_alarm->ts_alarm_sounds_other == 0 && is_numeric($existing_alarm->ts_alarm_sounds_other) )?'checked':null; ?> />
                                <label class="inline-block" for="ts_alarm_sounds_other_no<?php echo $existing_alarm->alarm_id; ?>">No</label>
                            </div>
                        </td>
                    <?php    
                    }
                    ?>
                </tr>

            <?php
            }
            ?>            


            <tr>          
                <th>Meets AS 3786:2014</th>
                <?php
                foreach( $existing_alarms_arr as $index => $existing_alarm ){ ?>
                    <td>
                        <div class="radio">
                            <input type="radio" name="ts_meetsas1851<?php echo $existing_alarm->alarm_id; ?>" id="ts_meetsas1851_yes<?php echo $existing_alarm->alarm_id; ?>" class="chk_yes inline-block ts_meetsas1851 ts_meetsas1851<?php echo $existing_alarm->alarm_id; ?>" data-db_table_field="ts_meetsas1851" data-col="<?php echo $existing_alarm->alarm_id; ?>" value="1" <?php echo (  $existing_alarm->ts_meetsas1851 != '' )?( ( $existing_alarm->ts_meetsas1851 == 1 )?'checked':null ):'checked'; ?> />
                            <label class="inline-block" for="ts_meetsas1851_yes<?php echo $existing_alarm->alarm_id; ?>">Yes</label>
                        </div>
                        <div class="radio">
                            <input type="radio" name="ts_meetsas1851<?php echo $existing_alarm->alarm_id; ?>" id="ts_meetsas1851_no<?php echo $existing_alarm->alarm_id; ?>" class="chk_no inline-block ts_meetsas1851 ts_meetsas1851<?php echo $existing_alarm->alarm_id; ?>" data-db_table_field="ts_meetsas1851" data-col="<?php echo $existing_alarm->alarm_id; ?>" value="0" <?php echo (  $existing_alarm->ts_meetsas1851 == 0 && is_numeric($existing_alarm->ts_meetsas1851) )?'checked':null; ?> />
                            <label class="inline-block" for="ts_meetsas1851_no<?php echo $existing_alarm->alarm_id; ?>">No</label>
                        </div>
                    </td>
                <?php    
                }
                ?>
            </tr>
            <tr>
                <th>Discarded</th>
                <?php                
                foreach( $existing_alarms_arr as $index => $existing_alarm ){ ?>
                    <td>                      

                        <input type="hidden" class="ts_discarded" value="<?php echo $existing_alarm->ts_discarded; ?>" data-col="<?php echo $existing_alarm->alarm_id; ?>" />
                        <input type="hidden" class="ts_discarded_reason ts_discarded_reason_hid" value="<?php echo $existing_alarm->ts_discarded_reason; ?>" data-col="<?php echo $existing_alarm->alarm_id; ?>" />
                        <input type="hidden" class="alarm_id" value="<?php echo $existing_alarm->alarm_id; ?>" data-col="<?php echo $existing_alarm->alarm_id; ?>">
                        <input type="hidden" class="is_new" value="<?php echo $existing_alarm->new; ?>" data-col="<?php echo $existing_alarm->alarm_id; ?>" />
                        <input type="hidden" class="alarm_number" value="<?php echo ($index+1); ?>" data-col="<?php echo $existing_alarm->alarm_id; ?>" />

                        <button type="button" class="btn <?php echo ( $existing_alarm->ts_discarded == 1 )?'btn-danger':null; ?> edit_discarded_alarm">
                            <?php echo ( $existing_alarm->ts_discarded == 1 )?'DISCARDED':'DISCARD'; ?>
                        </button>
                    </td>
                <?php    
                }
                ?>
            </tr>
            <!--
            <tr>
                <th>Delete</th>
                <?php
                foreach( $existing_alarms_arr as $index => $existing_alarm ){ ?>
                    <td class="job_details_td">
                        <input type="hidden" class="alarm_id" value="<?php echo $existing_alarm->alarm_id; ?>" data-col="<?php echo $existing_alarm->alarm_id; ?>">
                        <button type="button" id="btn_delete" class="btn btn-danger delete_sa_btn">Delete</button>
                    </td>
                <?php    
                }
                ?>
            </tr>
            -->

        </tbody>

        

    </table>
<?php
}else{ ?>
    <!--
    <div class="alert alert-danger alert-no-border alert-close alert-dismissible fade show" role="alert">                       
        This Property has no EXISTING Smoke Alarms on file. Please add Smoke Alarms below
    </div>
    -->
<?php
}

if( count($new_alarms_arr) > 0 ){
?>
    <h5>New Alarms</h5>
    <table class="table main-table table-hover new_alarm_tbl">

        <thead>
            <tr>
                <th></th>
                <?php
                foreach( $new_alarms_arr as $index => $new_alarm_row ){ ?>
                    <th>Alarm <?php echo ($index+1); ?> </th>
                <?php    
                }
                ?>
            </tr>
        </thead>
        
        <tbody>
                
            <tr>
                <th>Position</th>
                <?php
                foreach( $new_alarms_arr as $index => $new_alarm_row ){ ?>
                    <td>
                        <input type="text" name="sa_position" class="form-control sa_position" data-db_table_field="ts_position" data-col="<?php echo $new_alarm_row->alarm_id; ?>" value="<?php echo strtoupper($new_alarm_row->ts_position); ?>" />
                    </td>
                <?php    
                }
                ?>
            </tr>
            <tr>
                <th>Type</th>
                <?php
                foreach( $new_alarms_arr as $index => $new_alarm_row ){ ?>
                    <td>
                        <select name="sa_type" class="form-control sa_type" data-db_table_field="alarm_type_id" data-col="<?php echo $new_alarm_row->alarm_id; ?>">
                            <option value="">---</option> 
                            <?php
                            foreach( $alarm_type_sql->result() as $al_type ){ ?>
                                <option value="<?php echo $al_type->alarm_type_id ?>" <?php echo ( $al_type->alarm_type_id == $new_alarm_row->alarm_type_id )?'selected':null; ?>><?php echo $al_type->alarm_type; ?></option>
                            <?php
                            }
                            ?>                     
                        </select>
                    </td>
                <?php    
                }
                ?>
            </tr>
            <tr>
                <th>Make</th>
                <?php
                foreach( $new_alarms_arr as $index => $new_alarm_row ){ ?>
                    <td><?php echo strtoupper($new_alarm_row->make); ?></td>
                <?php    
                }
                ?>
            </tr>
            <tr>
                <th>Model</th>
                <?php
                foreach( $new_alarms_arr as $index => $new_alarm_row ){ ?>
                    <td><?php echo strtoupper($new_alarm_row->model); ?></td>
                <?php    
                }
                ?> 
            </tr>
            <tr>           
                <th>Power</th>
                <?php
                foreach( $new_alarms_arr as $index => $new_alarm_row ){ ?>
                    <td>
                        <select name="sa_power" class="form-control sa_power" data-db_table_field="alarm_power_id" data-col="<?php echo $new_alarm_row->alarm_id; ?>">
                            <option value="">---</option>   
                            <?php
                            foreach( $alam_pwr_for_new_alarm_sql->result() as $alam_pwr_for_new_alarm_row ){ ?>
                                <option value="<?php echo $alam_pwr_for_new_alarm_row->alarm_pwr_id ?>" <?php echo ( $alam_pwr_for_new_alarm_row->alarm_pwr_id == $new_alarm_row->alarm_power_id )?'selected':null; ?> data-is_li="<?php echo $alam_pwr_for_new_alarm_row->is_li; ?>" data-is_240v="<?php echo $alam_pwr_for_new_alarm_row->is_240v; ?>"><?php echo $alam_pwr_for_new_alarm_row->alarm_pwr; ?></option>
                            <?php
                            }
                            ?>                  
                        </select>
                    </td>
                <?php    
                }
                ?>
            </tr>
            <tr>
                <th>Required for Compliance</th>
                <?php
                foreach( $new_alarms_arr as $index => $new_alarm_row ){ ?>
                    <td>
                        <div class="radio">
                            <input type="radio" name="ts_required_compliance<?php echo $new_alarm_row->alarm_id; ?>" id="ts_required_compliance_yes<?php echo $new_alarm_row->alarm_id; ?>" class="chk_yes inline-block sa_rfc sa_rfc<?php echo $new_alarm_row->alarm_id; ?>" data-db_table_field="ts_required_compliance" data-col="<?php echo $new_alarm_row->alarm_id; ?>" value="1" <?php echo (  $new_alarm_row->ts_required_compliance == 1 )?'checked':null; ?> />
                            <label class="inline-block" for="ts_required_compliance_yes<?php echo $new_alarm_row->alarm_id; ?>">Yes</label>
                        </div>
                        <div class="radio">
                            <input type="radio" name="ts_required_compliance<?php echo $new_alarm_row->alarm_id; ?>" id="ts_required_compliance_no<?php echo $new_alarm_row->alarm_id; ?>" class="chk_no inline-block sa_rfc sa_rfc<?php echo $new_alarm_row->alarm_id; ?>" data-db_table_field="ts_required_compliance" data-col="<?php echo $new_alarm_row->alarm_id; ?>" value="0" <?php echo (  $new_alarm_row->ts_required_compliance == 0 && is_numeric($new_alarm_row->ts_required_compliance) )?'checked':null; ?> />
                            <label class="inline-block" for="ts_required_compliance_no<?php echo $new_alarm_row->alarm_id; ?>">No</label>
                        </div>
                    </td>
                <?php    
                }
                ?> 
            </tr>
            <tr>     
                <th>Alarm Expiry (YYYY)</th>   
                <?php
                foreach( $new_alarms_arr as $index => $new_alarm_row ){ ?>
                    <td>
                        <!--<input type="number" name="sa_expiry" class="form-control sa_expiry" value="<?php echo $new_alarm_row->expiry; ?>" data-db_table_field="expiry" data-col="<?php echo $new_alarm_row->alarm_id; ?>" />-->
                        
                        <input type="number" name="sa_ts_expiry" class="form-control sa_ts_expiry <?php echo $new_alarm_row->alarm_id; ?>" value="<?php echo $new_alarm_row->ts_expiry; ?>" data-db_table_field="ts_expiry" data-col="<?php echo $new_alarm_row->alarm_id; ?>" />
                        <input type="hidden" class="sa_expiry_hid" value="<?php echo $new_alarm_row->expiry; ?>" data-db_table_field="expiry" data-col="<?php echo $new_alarm_row->alarm_id; ?>" />
                        <span class="expiry_dont_match_span"></span>

                    </td>
                <?php    
                }
                ?>
            </tr>

            <tr>
                <th>Battery Expiry (MM/YY)</th>
                <?php
                foreach( $new_alarms_arr as $index => $new_alarm_row ){ 
                    $rec_batt_exp =  ( $new_alarm_row->rec_batt_exp != '' )?date("m",strtotime($new_alarm_row->rec_batt_exp)).date("y",strtotime($new_alarm_row->rec_batt_exp)):null;    
                    ?>
                    <td>
                        <input type="text" pattern="[0-9\/]*" inputmode="numeric" class="form-control rec_batt_exp" value="<?php echo $rec_batt_exp; ?>" data-col="<?php echo $new_alarm_row->alarm_id; ?>" data-db_table_field="rec_batt_exp" />                        
                        <input type="hidden" class="rec_batt_exp_full" value="<?php echo $new_alarm_row->rec_batt_exp; ?>" data-col="<?php echo $new_alarm_row->alarm_id; ?>" />
                    </td>
                <?php    
                }
                ?>
            </tr> 

            <tr>    
                <th>dB Reading (Minimum 85dB at 3 metres)</th>
                <?php
                foreach( $new_alarms_arr as $index => $new_alarm_row ){ ?>
                    <td>
                        <input type="number" name="sa_db" class="form-control sa_db" value="<?php echo $new_alarm_row->ts_db_rating; ?>" data-db_table_field="ts_db_rating" data-col="<?php echo $new_alarm_row->alarm_id; ?>" />
                    </td>
                <?php    
                }
                ?>
            </tr>
           
            <tr>           
                <th>Reason</th>
                <?php
                foreach( $new_alarms_arr as $index => $new_alarm_row ){ ?>
                    <td>
                        <select name="sa_reason" class="form-control sa_reason" data-db_table_field="alarm_reason_id" data-col="<?php echo $new_alarm_row->alarm_id; ?>">
                            <option value="">---</option>
                            <?php
                            foreach( $alarm_reason_sql->result() as $al_res ){ ?>
                                <option value="<?php echo $al_res->alarm_reason_id ?>" <?php echo ( $al_res->alarm_reason_id == $new_alarm_row->alarm_reason_id )?'selected':null; ?>><?php echo $al_res->alarm_reason; ?></option>
                            <?php
                            }
                            ?>                                                         
                        </select>	                                             
                    </td> 
                <?php    
                }
                ?>
            </tr>          
            <tr>
                <th>Delete</th>
                <?php
                foreach( $new_alarms_arr as $index => $new_alarm_row ){ ?>
                    <td class="job_details_td">

                        <input type="hidden" class="alarm_id" value="<?php echo $new_alarm_row->alarm_id; ?>" data-col="<?php echo $new_alarm_row->alarm_id; ?>">
                        <input type="hidden" class="is_new" value="<?php echo $new_alarm_row->new; ?>" data-col="<?php echo $new_alarm_row->alarm_id; ?>" />
                        <input type="hidden" class="alarm_number" value="<?php echo ($index+1); ?>" data-col="<?php echo $new_alarm_row->alarm_id; ?>" />
                        
                        <button type="button" id="btn_delete" class="btn btn-danger delete_sa_btn">Delete</button>

                    </td>
                <?php    
                }
                ?>
            </tr>

        </tbody>

        

    </table>
<?php
}else{ ?>
    <!--
    <div class="alert alert-danger alert-no-border alert-close alert-dismissible fade show" role="alert">                       
        This Property has no NEW Smoke Alarms on file. Please add Smoke Alarms below
    </div>
    -->
<?php
}
?>
</div>

<?php
//if( $this->tech_model->check_prop_first_visit($job_row->property_id) == true ){ // first visit

    if( $this->config->item('country') == 1 ){ // AU

        if( $job_row->p_state == 'QLD' ){ // QLD 
            
            if( $job_row->preferred_alarm_id > 0 ){
                $num_qld_alarm_txt = ( $job_row->qld_new_leg_alarm_num > 0 )?"{$job_row->qld_new_leg_alarm_num} ":null;
            ?>
                <p>This upgrade job is approved for <?php echo "{$num_qld_alarm_txt}{$job_row->pref_alarm_make}"; ?> alarms, if you require more, please call the office</p>                                
            <?php
            }

        }else{ // non-QLD ?>                                            
            <p>This property uses <?php echo $this->system_model->display_free_emerald_or_paid_brooks($job_row->agency_id); ?> alarms. </p>                             
        <?php    
        }
    
    }else{ // NZ ?>
        <p>This upgrade job is approved for <?php echo $this->system_model->display_orca_or_cavi_alarms($job_row->agency_id); ?> alarms, if you require more, please call the office</p>
    <?php
    }
    
//}                                    
?> 

<button type="button" id="dispay_add_alarm_btn" class="btn">Add Smoke Alarms</button><br /><br />
<table class="table main-table">
     
    <thead>
        <tr>
            <th>Batteries Installed</th>
            <th><input type="number" id="ts_batteriesinstalled" class="form-control ts_batteriesinstalled" data-db_table_field="ts_batteriesinstalled" data-db_table="jobs" value="<?php echo $job_row->ts_batteriesinstalled; ?>" /></th>
            <th>Items Tested</th>
            <th><input type="number" id="ts_items_tested" class="form-control ts_items_tested" data-db_table_field="ts_items_tested" data-db_table="jobs" value="<?php echo $job_row->ts_items_tested; ?>" /></th>
            <th>Alarms Installed</th>
            <th><input type="number" id="ts_alarmsinstalled" class="form-control ts_alarmsinstalled" data-db_table_field="ts_alarmsinstalled" data-db_table="jobs" value="<?php echo $job_row->ts_alarmsinstalled; ?>" /></th>
        </tr>      
    </thead>                         

</table>

<!-- ADD Smoke Alarms -->							
<div id="add_sa_fb" class="fancybox" style="display:none;" >

    <h4>Add Smoke Alarm</h4>

    <div id="add_smoke_alarm_div">

        <table class="table main-table add_sa_main_tbl">

            <tbody class="add_sa_tbody">

                <tr class="alarm_tr"> 
                    <th>New?</th>                   
                    <td>
                        <select class="form-control sa_new_add">
                            <option value="">---</option>
                            <option value='1'>New</option>
                            <option value='0'>Existing</option>                                                           
                        </select>	                                             
                    </td> 
                    <th>RFC?</th> 
                    <td>
                        <span>
                            <input type="radio" name="sa_rfc_add1" class="chk_yes inline-block sa_rfc_add" value="1" /> Yes
                        </span>  
                        <span>
                            <input type="radio" name="sa_rfc_add1" class="chk_yes inline-block sa_rfc_add" value="0" /> No
                        </span>                        
                    </td> 
                </tr>
                <tr class="alarm_tr"> 
                    <th>Power</th>
                    <td>
                        <select class="form-control sa_power_add">
                            <option value="">---</option>                                                          
                        </select>	                                             
                    </td> 
                    <th>Type</th>
                    <td>
                        <select class="form-control sa_type_add">
                            <option value="">---</option>
                            <?php
                            foreach( $alarm_type_sql->result() as $al_type ){ ?>
                                <option value="<?php echo $al_type->alarm_type_id ?>"><?php echo $al_type->alarm_type; ?></option>
                            <?php
                            }
                            ?>                                                           
                        </select>	                                             
                    </td>
                </tr>
                <tr class="alarm_tr"> 
                    <th>Position</th>               
                    <td>
                        <input type="text" class="form-control sa_position_add" />
                    </td>
                    <th>Make</th>
                    <td>
                        <input type="text" class="form-control sa_make_add" />
                    </td>
                </tr>
                <tr class="alarm_tr">
                    <th>Model</th>
                    <td>
                        <input type="text" class="form-control sa_model_add" />
                    </td>
                    <th>Alarm Expiry (YYYY)</th>
                    <td>
                        <input type="number" class="form-control sa_expiry_add" />
                    </td>
                </tr>              
                <tr class="alarm_tr sa_reason_add_tr">
                    <th colspan="2">Reason</th>
                    <td colspan="2">
                        <select class="form-control sa_reason_add">
                            <option value="">---</option>
                            <?php
                            foreach( $alarm_reason_sql->result() as $al_res ){ ?>
                                <option value="<?php echo $al_res->alarm_reason_id ?>"><?php echo $al_res->alarm_reason; ?></option>
                            <?php
                            }
                            ?>                                                         
                        </select>	                                             
                    </td> 
                </tr>             
            
            </tbody>                                  

        </table>


    </div>    

    <button type="button" id="add_sa_btn" class="btn btn-success float-left">Add another alarm</button>
    <button type="button" id="save_sa_btn" class="btn float-right">Save and close</button>	

</div>


<!-- discarded -->							
<div id="edit_discarded_alarm_fb" class="fancybox" style="display:none;" >

    <h4>Discarded</h4>

    <div class="p-3">
        <div>
            <div class="radio">
                <input type="radio" name="ts_discarded" id="ts_discarded_yes" class="chk_yes inline-block ts_discarded" value="1" />
                <label class="inline-block" for="ts_discarded_yes">Yes</label>
            </div>
            <div class="radio">
                <input type="radio" name="ts_discarded" id="ts_discarded_no" class="chk_no inline-block ts_discarded" value="0" />
                <label class="inline-block" for="ts_discarded_no">No</label>
            </div>
            <div class="clearfix"></div>
        </div>    

        <div class="ts_discarded_reason_div mt-2" style="display:none;">
            <select name="ts_discarded_reason" class="form-control ts_discarded_reason">
                <option value="">---</option>
                <?php
                foreach( $alarm_discarded_reason_sql->result() as $al_disc_res ){ ?>
                    <option value="<?php echo $al_disc_res->id ?>"><?php echo $al_disc_res->reason; ?></option>
                <?php
                }
                ?>                                                         
            </select>
        </div>
    </div>

    <input type="hidden" class="alarm_id" />

    <div class="text-center mt-2">
        <button type="button" id="update_alarm_discarded_btn" class="btn mt-3">Update</button>
    </div>

</div>

<!-- Fancybox END -->

<style>
.expiry_dont_match_inner_span{
    cursor: pointer;
}
.update_expiry_swal{
    width: auto !important;
}
</style>
<script>
function ts_ajax_smoke_alarm_inline_update(dom){

    var parent_table = dom.parents("table.main-table");      
    var parent_row = dom.parents("tr:first");
    var db_table_field = dom.attr("data-db_table_field");
    var db_table_value  = dom.val(); 
       
    var col = dom.attr("data-col"); // table column
    var alarm_id = jQuery("input.alarm_id[data-col='"+col+"']").val(); // get alarm ID by table column
    
    // job update
    if( alarm_id > 0 ){

        //jQuery('#load-screen').show();
        jQuery(".techsheet_tab_next:visible").prop("disabled",true); // disable NEXT button

        jQuery.ajax({
            type: "POST",
            url: "/jobs/ajax_techsheet_smoke_alarm_row_update",
            data: { 
                alarm_id: alarm_id,
                db_table_field: db_table_field,
                db_table_value: db_table_value
            }
        }).done(function( ret ){

            //jQuery('#load-screen').hide();  
            jQuery(".techsheet_tab_next:visible").prop("disabled",false); // enable NEXT button                           			

        });

    }    

}

jQuery(document).ready(function(){

    // discarded
    jQuery(".edit_discarded_alarm").click(function(){

        var node = jQuery(this);
        var parent_td = node.parents("td:first");

        var alarm_id = parent_td.find(".alarm_id").val();
        var ts_discarded = parent_td.find(".ts_discarded").val(); 
        var ts_discarded_reason = parent_td.find(".ts_discarded_reason").val(); 
         
        var lb_id = '#edit_discarded_alarm_fb'; // lightbox ID
        var lb_div = jQuery(lb_id); // lightbox div  

         if( ts_discarded == 1 ){
            lb_div.find("#ts_discarded_yes").prop("checked",true);
            lb_div.find(".ts_discarded_reason_div").show();
        }else if( ts_discarded == 0 ){
            lb_div.find("#ts_discarded_no").prop("checked",true);
            lb_div.find(".ts_discarded_reason_div").hide();
        }      

        lb_div.find(".ts_discarded_reason").val(ts_discarded_reason);
        lb_div.find(".alarm_id").val(alarm_id);
        

        jQuery.fancybox.open({
            src  : lb_id
        });


    });

    jQuery("#edit_discarded_alarm_fb #ts_discarded_yes").click(function(){
        jQuery('#edit_discarded_alarm_fb').find(".ts_discarded_reason_div").show();
    });

    jQuery("#edit_discarded_alarm_fb #ts_discarded_no").click(function(){
        jQuery('#edit_discarded_alarm_fb').find(".ts_discarded_reason_div").hide();
    });


    
    jQuery("#update_alarm_discarded_btn").click(function(){

        var lb_id = '#edit_discarded_alarm_fb'; // lightbox ID
        var lb_div = jQuery(lb_id); // lightbox div 

        var alarm_id = lb_div.find('.alarm_id').val();
        var ts_discarded = lb_div.find('.ts_discarded:checked').val();
        var ts_discarded_reason = lb_div.find('.ts_discarded_reason').val(); 
        var error = "";
        
        // job update
        if( alarm_id > 0 ){
           
            // validation
            if( ts_discarded == 1 && ( ts_discarded_reason == '' || ts_discarded_reason == null ) ){
                error += "Discarded Reason is required.\n";
            }

            if( error !='' ){ // has error msg

                swal('',error,'error');

            }else{

                
                jQuery('#load-screen').show();
                jQuery(".techsheet_tab_next:visible").prop("disabled",true); // disable NEXT button

                jQuery.ajax({
                    type: "POST",
                    url: "/jobs/ajax_update_smoke_alarm_discarded_and_reason",
                    data: { 
                        alarm_id: alarm_id,
                        ts_discarded: ts_discarded,
                        ts_discarded_reason: ts_discarded_reason
                    }
                }).done(function( ret ){

                    jQuery('#load-screen').hide();  
                    jQuery(".techsheet_tab_next:visible").prop("disabled",false); // enable NEXT button

                    var reload = 1; // reload after ajax save
                    save_existing_alarm_unique_radios(reload);                    
                    //$.fancybox.close();                          			

                });
                

            }            

        }    

    });
    


    // display add alarm form
    jQuery("#dispay_add_alarm_btn").click(function(){

        $.fancybox.open({
            src  : '#add_sa_fb',
            touch: false // disable panning/swiping
        });

    });

    // add more alarm to the form
    jQuery("#add_sa_btn").click(function(){

        var add_sa_fb_dom = jQuery("#add_sa_fb");

        // get last alarm table
        let alarm = jQuery(".add_sa_main_tbl:last");

        // get selection
        var sa_new_add = alarm.find(".sa_new_add").val();
        var sa_rfc_add = alarm.find(".sa_rfc_add").val();
        var sa_power_add = alarm.find(".sa_power_add").val();
        var sa_type_add = alarm.find(".sa_type_add").val();
        var sa_reason_add = alarm.find(".sa_reason_add").val();

        // clone
        let alarm_clone = alarm.clone();

        // increment checkbox ID to make the bootstrap checkbox to work
        var sa_rfc_add_count = add_sa_fb_dom.find(".add_sa_main_tbl").length;

        alarm_clone.find(".sa_rfc_add").attr("name","sa_rfc_add"+(sa_rfc_add_count+1));

        // insert previous values to clone
        alarm_clone.find(".sa_new_add").val(sa_new_add);        

        // RFC radio prefill
        if( sa_rfc_add == 1 ){
            alarm_clone.find(".sa_rfc_add[value='1']").prop("checked",true); // YES
        }else if( sa_rfc_add == 0 ){
            alarm_clone.find(".sa_rfc_add[value='0']").prop("checked",true); // NO
        }

        alarm_clone.find(".sa_power_add").val(sa_power_add);
        alarm_clone.find(".sa_type_add").val(sa_type_add);
        alarm_clone.find(".sa_position_add").val(''); // clear location/position
        alarm_clone.find(".sa_reason_add").val(sa_reason_add);        

        // append new alarm table
        jQuery("#add_smoke_alarm_div").append(alarm_clone);

    });

    
    // new/existing switch script
    jQuery("#add_smoke_alarm_div").on("change",".sa_new_add",function(){
    
        var sa_new_dom = jQuery(this);
        var parent_table = sa_new_dom.parents("table.add_sa_main_tbl:first");

        var is_new = sa_new_dom.val();

        // only show reason on new alarms
        if( is_new == 1 ){
            parent_table.find('.sa_reason_add_tr').show();
        }else{
            parent_table.find('.sa_reason_add_tr').hide();
        }    

        // get dynamic power type
        jQuery('#load-screen').show();
        jQuery(".techsheet_tab_next:visible").prop("disabled",true); // disable NEXT button

        jQuery.ajax({
            type: "POST",
            url: "/jobs/get_dynamic_alarm_power",
            data: { 
                agency_id: <?php echo $job_row->agency_id; ?>,
                is_new: is_new
            }
        }).done(function( ret ){

            jQuery('#load-screen').hide();
            jQuery(".techsheet_tab_next:visible").prop("disabled",false); // enable NEXT button

            parent_table.find(".sa_power_add").html('<option value="">---</option>'+ret);

        });	    

    });

    // alarm power autofill for new alarms
	jQuery("#add_smoke_alarm_div").on("change",".sa_power_add",function(){
		
		var dom = jQuery(this);
        var parent_table = dom.parents("table.add_sa_main_tbl:first");

		var alarm_pwr_id = dom.val();       
		var is_new = parent_table.find(".sa_new_add").val();
		
		// only for new alarm
		if( is_new == 1 ){
			
            jQuery('#load-screen').show();
            jQuery(".techsheet_tab_next:visible").prop("disabled",true); // disable NEXT button

			jQuery.ajax({
				type: "POST",
				url: "/jobs/ajax_get_alarm_power_details",
				data: { 
					alarm_pwr_id: alarm_pwr_id
				},
				dataType: "json"
			}).done(function( ret ){

                jQuery('#load-screen').hide();
                jQuery(".techsheet_tab_next:visible").prop("disabled",false); // enable NEXT button

                parent_table.find(".sa_type_add option").each(function(){										
                    if( parseInt(jQuery(this).val()) == parseInt(ret.alarm_type_id) ){
                        jQuery(this).prop('selected',true);
                    }		                    			                    
                });

				parent_table.find(".sa_make_add").val(ret.alarm_make);
				parent_table.find(".sa_model_add").val(ret.alarm_model);				

			});	
			
		}
				
	});


    // Add new alarm
	jQuery("#save_sa_btn").click(function(){
		
		var dom = jQuery(this); 
        var parent = dom.parents("#add_sa_fb");
        var alarms_arr = [];

        var error_arr = [];
        parent.find("table.add_sa_main_tbl").each(function(){

            var this_table = jQuery(this);
            
            var sa_new = this_table.find(".sa_new_add").val();            
            var sa_rfc = this_table.find(".sa_rfc_add:checked").val();      

            var sa_power_dom = this_table.find(".sa_power_add option:selected");
            var sa_power = sa_power_dom.val();            
            var is_li = sa_power_dom.attr("data-is_li");                     

            var sa_type = this_table.find(".sa_type_add").val();
            var sa_position = this_table.find(".sa_position_add").val();
            var sa_make = this_table.find(".sa_make_add").val();
            var sa_model = this_table.find(".sa_model_add").val();
            var sa_expiry = this_table.find(".sa_expiry_add").val();
            var sa_reason = this_table.find(".sa_reason_add").val();
            
            if( sa_rfc == '' || sa_rfc == undefined ){
                var error_txt = 'RFC is required';
                if( error_arr.includes(error_txt) == false ){
                    error_arr.push(error_txt);
                }
                
            } 

            if( sa_power == '' ){
                var error_txt = 'Power is required';
                if( error_arr.includes(error_txt) == false ){
                    error_arr.push(error_txt);
                }
                
            }  

            if( sa_type == '' ){
                var error_txt = 'Type is required';
                if( error_arr.includes(error_txt) == false ){
                    error_arr.push(error_txt);
                }
                
            }  

            if( sa_position == '' ){
                var error_txt = 'Position is required';
                if( error_arr.includes(error_txt) == false ){
                    error_arr.push(error_txt);
                }
                
            }

            if( sa_make == '' ){
                var error_txt = 'Make is required';
                if( error_arr.includes(error_txt) == false ){
                    error_arr.push(error_txt);
                }
                
            } 

            if( sa_model == '' ){
                var error_txt = 'Model is required';
                if( error_arr.includes(error_txt) == false ){
                    error_arr.push(error_txt);
                }
                
            }   

            if( jQuery.isNumeric(sa_expiry) == false || sa_expiry.length != 4 ){
                var error_txt = 'Please enter expiry year like: 2022';
                if( error_arr.includes(error_txt) == false ){                   
                    error_arr.push(error_txt);
                }                    
            }
            
           

            // if new alarm
            if( sa_reason == '' && sa_new == 1 ){
                var error_txt = 'Reason is required';
                if( error_arr.includes(error_txt) == false ){
                    error_arr.push(error_txt);
                }
                
            }
            
            
          
            json_data = {
                'sa_new': sa_new,
                'sa_rfc': sa_rfc,
                'sa_power': sa_power,
                'sa_type': sa_type,
                'sa_position': sa_position,
                'sa_make': sa_make,
                'sa_model': sa_model,
                'sa_expiry': sa_expiry,                
                'sa_reason': sa_reason
            }
            var json_str = JSON.stringify(json_data);

            alarms_arr.push(json_str);

        });  

        //console.log(alarms_arr); 

        if( error_arr.length > 0 ){
            
            error_str = '';
            for( var i = 0; i < error_arr.length; i++ ){
                error_str += error_arr[i]+"\n";
            }

            swal('',error_str,'error');
            
        }else{

            
            jQuery('#load-screen').show();
            jQuery(".techsheet_tab_next:visible").prop("disabled",true); // disable NEXT button

            jQuery.ajax({
                type: "POST",
                url: "/jobs/ajax_add_smoke_alarms",
                data: {
                    job_id: <?php echo $this->input->get_post('job_id'); ?>,
                    agency_id: <?php echo $job_row->agency_id; ?>,
                    alarms_arr: alarms_arr
                }
            }).done(function (ret) {

                jQuery('#load-screen').hide();
                jQuery(".techsheet_tab_next:visible").prop("disabled",false); // enable NEXT button
                location.reload();

            });
            
                        

        }               
		
				
	});


    // delete smoke alarm
    jQuery(".delete_sa_btn").click(function(){

        var dom = jQuery(this);
        var parent_row = dom.parents("td.job_details_td");
		 
		var alarm_id = parent_row.find(".alarm_id").val();

        swal({
            title: "Warning!",
            text: "This will delete this alarm, do you want to continue?",
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
                
                jQuery('#load-screen').show();
                jQuery(".techsheet_tab_next:visible").prop("disabled",true); // disable NEXT button
                
                jQuery.ajax({
                    type: "POST",
                    url: "/jobs/ajax_delete_techsheet_smoke_alarm",
                    data: {
                        job_id: <?php echo $this->input->get_post('job_id'); ?>,                        
                        alarm_id: alarm_id
                    }
                }).done(function (ret) {

                    jQuery('#load-screen').hide();
                    jQuery(".techsheet_tab_next:visible").prop("disabled",false); // enable NEXT button
                    location.reload();

                });		

            }

        });	

    });


    // smoke alarm inline ajax update      
    var elem_select = ".sa_rfc, .sa_power, .sa_type, .sa_position, .sa_make, .sa_model, .sa_ts_expiry, .sa_expiry, .sa_db, .sa_reason";           
    jQuery(elem_select).change(function(){

        var dom = jQuery(this);
        var field_name = dom.attr("data-db_table_field");
        var field_val = dom.val();

        if( field_name == 'ts_expiry' && field_val == '' ){
            swal('','Alarm Expiry is required','error');
        }else{
            ts_ajax_smoke_alarm_inline_update(dom);
        }                             

    });


    // alarm power autofill for new alarms
	jQuery(document).on("change",".rec_batt_exp",function(){
		
		var dom = jQuery(this);   
        var parents_td = dom.parents("td:first");    

		var alarm_id = dom.attr("data-col");        
        var rec_batt_exp = dom.val();      		
				
		if( alarm_id > 0 ){
			
            //jQuery('#load-screen').show();
            jQuery(".techsheet_tab_next:visible").prop("disabled",true); // disable NEXT button

			jQuery.ajax({
				type: "POST",
				url: "/jobs/ajax_techsheet_smoke_alarm_batt_exp_update",
				data: { 
					alarm_id: alarm_id,
                    rec_batt_exp: rec_batt_exp
				}
			}).done(function( ret ){

                //jQuery('#load-screen').hide();  
                jQuery(".techsheet_tab_next:visible").prop("disabled",false); // enable NEXT button

                parents_td.find(".rec_batt_exp_full").val(ret);             				

			});	
			
		}
				
    });
    

    // input mask
    jQuery(".rec_batt_exp").inputmask("99/99"); 

    jQuery(document).on("change",".rec_batt_exp",function(){
		
		var dom = jQuery(this);   
        var rec_batt_exp = dom.val(); 
        
        var rec_batt_exp_month = rec_batt_exp.substring(0, 2)        

        if( rec_batt_exp_month > 12 ){
            swal('','Invalid Month','error');
            dom.addClass('border-danger');    
        }else{
            dom.removeClass('border-danger'); 
        }
        
				
    });


    // ts_expiry and expiry update
    jQuery(document).on("click",".expiry_dont_match_inner_span",function(){

        var selected_expiry_dom = jQuery(this);
        var selected_expiry = selected_expiry_dom.text();
        var parent_td = selected_expiry_dom.parents("td:first");

        var sa_ts_expiry_dom = parent_td.find(".sa_ts_expiry");
        var ts_expiry = sa_ts_expiry_dom.val(); // ts_expiry
        var alarm_id = sa_ts_expiry_dom.attr("data-col"); // alarm_id        
        var expiry_hid = parent_td.find('.sa_expiry_hid').val();

        var swal_html_txt = "<p>Last visit this alarm was <b class='text-primary'>"+expiry_hid+"</b> and you entered the alarm as <b class='text-danger'>"+ts_expiry+"</b>.<br />";
        swal_html_txt += "This will update our records and conflict with a previous issued compliance report.</p>";
        swal_html_txt += "<p class='text-center mt-2'>Are you sure <b class='text-danger'>"+selected_expiry+"</b> is correct?<p>";

        swal({
            title: "Warning!",
            html: true,
            text: swal_html_txt,
            type: "warning",	
            customClass: 'update_expiry_swal',					
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
                
                if( alarm_id > 0 && selected_expiry != '' ){

                    //jQuery('#load-screen').show();
                    jQuery(".techsheet_tab_next:visible").prop("disabled",true); // disable NEXT button

                    jQuery.ajax({
                        type: "POST",
                        url: "/jobs/ajax_techsheet_update_expiry_and_ts_expiry",
                        data: { 
                            alarm_id: alarm_id,
                            selected_expiry: selected_expiry
                        }
                    }).done(function( ret ){

                        jQuery('#load-screen').hide(); 
                        jQuery(".techsheet_tab_next:visible").prop("disabled",false); // enable NEXT button
                        
                        parent_td.find('.sa_ts_expiry').val(selected_expiry);  
                        parent_td.find('.sa_expiry_hid').val(selected_expiry); 

                        // clear
                        parent_td.find('.expiry_dont_match_span').html('');  
                        parent_td.find('.sa_ts_expiry').removeClass('border-danger'); 
                        
                        //location.reload();                         			

                    });	

                }                

            }

        });

    });


});
</script>