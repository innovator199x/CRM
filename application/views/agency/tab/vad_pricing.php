<style>
    .fadedText {
        opacity: 0.5;
    }
    .price_input_box{width:120px;}
    .circle{
        list-style-type: circle!important;
        margin-left:20px!important;
    }
    .font-icon-pencil:hover {
        color: #00a8ff !important;
    }

    #prop_variation_filter > label > input {
        width: 63%;
    }
</style>

<div class="row">
    <div class="col-md-6 columns">

    <section class="card card-blue-fill">
            <header class="card-header">Services</header>
            <div class="card-block">

                <table class="table table-hover main-table vad_pricing_table text-left table-no-border">
                    <thead>
                        <tr>
                            <th>Services</th>
                            <th>Price</th>
                            <th class="text-center">Approved</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            // get price increase excluded agency
                            $piea_sql = $this->db->query("
                            SELECT *
                            FROM `price_increase_excluded_agency`
                            WHERE `agency_id` = {$agency_id}                  
                            AND (
                                `exclude_until` >= '".date('Y-m-d')."' OR
                                `exclude_until` IS NULL
                            )
                            ");  

                            $is_price_increase_excluded = ( $piea_sql->num_rows() > 0 )?1:0;

                            foreach($services as $service_row){ 
                            $sa_ic = 12; // Smoke Alarms (IC)
                            $is_sa_ic = ( $service_row['id'] == $sa_ic )?true:false;    
                            $agencySelected = (!empty($service_row['agency_services_service_id']))?true:false;
                                
                            if($agencySelected){
                        ?>

                        <tr>
                            <td>
                                <?php echo $service_row['type'] ?>
                                <?php if( $is_sa_ic == true ){ ?>
                                    <strong style="color:red;">(Required for Quotes)</strong>
                                <?php } ?>
                                <?php if( $is_sa_ic == true && is_numeric($service_row['agency_services_price']) && $service_row['agency_services_price']==0 ){  ?>
                                    <span style="color:red; margin-left: 20px;">$119</span>
                                <?php } ?>

                                &nbsp;&nbsp;<img data-toggle="tooltip" title="<?php echo $service_row['type'] ?>" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($service_row['id']); ?>" />
                            </td>
                            <td>
                                <?php                    
                                if( $is_price_increase_excluded == 1 ){ // orig price
                                    echo  "$".$service_row['agency_services_price'];   
                                }else{ // new price, price variation

                                    $price_var_params = array(
                                        'service_type' => $service_row['id'],
                                        'agency_id' => $agency_id
                                    );
                                    $price_var_arr = $this->system_model->get_agency_price_variation($price_var_params);
                                    echo $price_var_arr['price_breakdown_text'];

                                }                                                                
                                ?>
                            </td>
                            <td class="text-center"><span class="fa fa-check-circle text-green"></span></td>
                        </tr>

                        <?php } } ?>
                    </tbody>
                </table>

                <div class="text-left"><a data-fancybox data-src="#services_fancybox" class="btn" href="#">Add/Edit Services</a></div>

            </div>
        </section>
        
        <!-- SERVICES FANCYBOX -->
        <div style="display:none" id="services_fancybox">
            <?php 
                echo form_open("/agency/update_agency/{$agency_id}/{$tab}","class=vad_form"); 
                
                $hidden_input_data_agency_id = array(
                    'type'  => 'hidden',
                    'name'  => 'agency_id',
                    'id'    => 'agency_id',
                    'value' => $agency_id,
                    'class' => 'agency_id'
                );
                echo form_input($hidden_input_data_agency_id);
            ?>
                <section class="card card-blue-fill">
                    <header class="card-header">Services</header>
                    <div class="card-block">
                            <table class="table table-hover main-table vad_pricing_table text-left table-no-border">
                                <thead>
                                    <tr>
                                        <th>Services</th>
                                        <th <?php echo ( $is_price_increase_excluded == 1 )?null:'style="display:none"'; ?>>Price</th>
                                        <th>Approved</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php 
                                    $index = 0;
                                    foreach($services as $service_row){ 
                                        $sa_ic = 12; // Smoke Alarms (IC)
                                        $is_sa_ic = ( $service_row['id'] == $sa_ic )?true:false;    
                                        $agencySelected = (!empty($service_row['agency_services_service_id']))?true:false;
                                ?>
                                    <tr class="service_tr <?php echo ($agencySelected==true)?null:'fadedText'; ?> <?php echo ( $is_sa_ic == true )?'sa_ic_row':null; ?>" value="<?php echo $index; ?>">
                                        <td>
                                            <?php echo $service_row['type'] ?>
                                            
                                            <?php if( $is_sa_ic == true ){ ?>
                                                <strong style="color:red;">(Required for Quotes)</strong>
                                            <?php } ?>
                                            <?php if( $is_sa_ic == true && is_numeric($service_row['agency_services_price']) && $service_row['agency_services_price']==0 ){  ?>
                                                <span style="color:red; margin-left: 20px;">$119</span>
                                            <?php } ?>

                                            &nbsp;&nbsp;<img data-toggle="tooltip" title="<?php echo $service_row['type'] ?>" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($service_row['id']); ?>" />
                                        </td>
                                        <td <?php echo ( $is_price_increase_excluded == 1 )?null:'style="display:none"'; ?>>
                                                <div class="input-group price_input_box">
                                                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                    <input type="text" class="form-control service_price" name="service_price[]" value="<?php echo  $service_row['agency_services_price']?>" />
                                                </div>
                                        </td>
                                        <td>
                                                <div class="checkbox">
                                                    <input type="checkbox" id="<?php echo $index.'_check' ?>" name="agency_service_approve[]" class="approve agency_service_approve" <?php echo ($agencySelected==true)?'checked="checked"':''; ?> value="<?php echo $index; ?>" />	
                                                    <label for="<?php echo $index.'_check' ?>">&nbsp;</label>
                                                </div>
                                            <input type="hidden" name="service_name[]" class="service_name" value="<?php echo $service_row['type']; ?>" />
                                            <input type="hidden" name="services_checked[]" class="services_checked" value="<?php echo ($agencySelected==true)?1:0; ?>" />
                                            <input type="hidden" name="agency_service_checked_orig[]" class="agency_service_checked_orig" value="<?php echo ($agencySelected==true)?1:0; ?>" />
                                            <input type="hidden" name="service_id[]" class="service_id" value="<?php echo $service_row['id']; ?>" />
                                            <input type="hidden" name="agency_service_approve_orig[]" class="agency_service_approve_orig" value="<?php echo ($agencySelected==true)?$index:null; ?>" />
                                            <input type="hidden" name="agency_service_orig_price[]" class="agency_service_orig_price" value="<?php echo ($service_row['agency_services_price']>0) ? $service_row['agency_services_price'] : 0 ; ?>" />                                            
                                        </td>
                                    </tr>
                                <?php $index++; } ?>
                                </tbody>
                            </table>
                    </div>
                </section>
                <input type="hidden" name="is_price_increase_excluded" id="is_price_increase_excluded" class="is_price_increase_excluded" value="<?php echo $is_price_increase_excluded; ?>" />
                <div class="text-right"><button type="submit" name="btn-submit-services" value="btn-submit-services" class="btn btn-update">Update Services</button></div>
            </form>
        </div>

    </div>

    <div class="col-md-6 columns">

        <section class="card card-blue-fill">
            <header class="card-header">Alarms</header>
            <div class="card-block">
                <table class="table table-hover main-table vad_pricing_table text-left table-no-border">
                    <thead>
                        <tr>
                            <th>Alarms</th>
                            <th>Price</th>
                            <th class="text-center">Approved</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            foreach($alarms as $alarm){ 
                                $alarm_240v_rf_default_price = 200.00;
                                $alarm_240vRFCav_default_price = 60.00;
                                $alarm_240v_rf = 10; // 240v RF	
                                $alarm_240vRFCav = 14; // 240v RF CAV	
                                $is_alarm_240v_rf = ( $alarm['alarm_pwr_id'] == $alarm_240v_rf )?true:false;
                                $is_alarm_240vRFCavf = ( $alarm['alarm_pwr_id'] == $alarm_240vRFCav )?true:false;
                                $is_alarm_240vRF_EP = ( $alarm['alarm_pwr_id'] == 22 )?true:false; # 240VRF(EP)
                                $agencySelected = (!empty($alarm['agency_alarm_id']))?true:false;      
                                if($agencySelected){
                        ?>
                            <tr>
                                <td>

                                <?php       
                                            if(COUNTRY==1){ // AU
                                                $display_alarm_make = array(10,12);
                                                if( in_array($alarm['alarm_pwr_id'],$display_alarm_make) ){
                                                    echo "{$alarm['alarm_pwr']}".( ( $alarm['alarm_make'] != '' )?" ({$alarm['alarm_make']})":null ); 
                                                }else{
                                                    echo $alarm['alarm_pwr']; 
                                                }
                                            } else {
                                                echo $alarm['alarm_pwr']; 
                                            }

                                            if($row['state']=="QLD"){ //show QLD only
                                                if( $is_alarm_240v_rf == true ){  ?>
                                                    <strong style="color:red;">(Required for Quotes)</strong>
                                                <?php	
                                                }elseif($is_alarm_240vRFCavf==true){
                                                ?>
                                                    <strong style="color:red;">(Required for Quotes)</strong>
                                                <?php
                                                }elseif($is_alarm_240vRF_EP==true){
                                                ?>
                                                    <strong style="color:red;">(Required for Quotes)</strong>
                                                <?php
                                                }
                                            }

                                            if( $is_alarm_240v_rf == true && is_numeric($alarm['price']) && $alarm['price']==0 ){ ?>
                                                <span style="color:red; margin-left: 20px;"><?php echo "${$alarm_240v_rf_default_price}" ?></span>
                                            <?php	
                                            }
                                            ?>

                                </td>
                                <td>
                                    <?php 
                                    ##required alarms default price
                                    if($is_alarm_240v_rf==true){
                                        if($agencySelected!=true){
                                            $new_alarm_price = $alarm_240v_rf_default_price;
                                        }else{
                                            $new_alarm_price = $alarm['price'];
                                        }
                                        
                                    }elseif($is_alarm_240vRFCavf==true){
                                        if($agencySelected!=true){
                                            $new_alarm_price = $alarm_240vRFCav_default_price;
                                        }else{
                                            $new_alarm_price = $alarm['price'];
                                        }
                                    }else{
                                        $new_alarm_price = $alarm['price'];
                                    }
                                    ##required alarms default price end
                                    echo "$".$new_alarm_price;                                    
                                    ?>
                                </td>
                                <td class="text-center"><span class="fa fa-check-circle text-green"></span></td>
                            </tr>
                       <?php }} ?>
                    </tbody>
                </table>
                <div class="text-left"><a data-fancybox data-src="#alarms_fancybox" class="btn" href="#">Add/Edit Alarms</a></div>
            </div>
        </section>
       
        <div style="display:none;" id="alarms_fancybox">
                <div class="row">
                    <div class="col-md-8">

                    <?php 
                        echo form_open("/agency/update_agency/{$agency_id}/{$tab}","class=vad_form"); 
                        
                        $hidden_input_data_agency_id = array(
                            'type'  => 'hidden',
                            'name'  => 'agency_id',
                            'id'    => 'agency_id',
                            'value' => $agency_id,
                            'class' => 'agency_id'
                        );
                        echo form_input($hidden_input_data_agency_id);
                    ?>
                        <section class="card card-blue-fill">
                            <header class="card-header">Alarms</header>
                            <div class="card-block">
                                <table class="table table-hover main-table vad_pricing_table text-left table-no-border">
                                    <thead>
                                        <tr>
                                            <th>Alarms</th>
                                            <th>Price</th>
                                            <th>Approved</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            $index2 = 0;
                                            foreach($alarms as $alarm){ 
                                                $alarm_240v_rf_default_price = 200.00;
                                                $alarm_240vRFCav_default_price = 60.00;
                                                $alarm_240vRF_EP_price = 100.00;
                                                $alarm_240v_rf = 10; // 240v RF	
                                                $alarm_240vRFCav = 14; // 240v RF CAV	
                                                $is_alarm_240v_rf = ( $alarm['alarm_pwr_id'] == $alarm_240v_rf )?true:false;
                                                $is_alarm_240vRFCavf = ( $alarm['alarm_pwr_id'] == $alarm_240vRFCav )?true:false;
                                                $is_alarm_240vRF_EP = ( $alarm['alarm_pwr_id'] == 22 )?true:false; # 240VRF(EP)
                                                $agencySelected = (!empty($alarm['agency_alarm_id']))?true:false;      
                                        ?>
                                            <tr class="alarms_tr <?php echo ($agencySelected==true)?null:'fadedText'; ?> <?php echo ( $is_alarm_240v_rf == true )?'alarm_240v_rf_row':null; ?> <?php echo ($is_alarm_240v_rf==true || $is_alarm_240vRFCavf==true) ? 'is_alarm_req_for_quotes_row' : NULL ; ?>" value="<?php echo $index2; ?>">
                                                <td class="priceAlarmCol">
                                                    <?php 
                                                    if(COUNTRY==1){ // AU
                                                        $display_alarm_make = array(10,12);
                                                        if( in_array($alarm['alarm_pwr_id'],$display_alarm_make) ){
                                                            echo "{$alarm['alarm_pwr']}".( ( $alarm['alarm_make'] != '' )?" ({$alarm['alarm_make']})":null ); 
                                                        }else{
                                                            echo $alarm['alarm_pwr']; 
                                                        }
                                                    } else {
                                                        echo $alarm['alarm_pwr']; 
                                                    }
                                                    if($row['state']=="QLD"){ //show QLd only
                                                        if( $is_alarm_240v_rf == true ){  ?>
                                                            <strong style="color:red;">(Required for Quotes)</strong>
                                                        <?php	
                                                        }elseif($is_alarm_240vRFCavf==true){
                                                        ?>
                                                            <strong style="color:red;">(Required for Quotes)</strong>
                                                        <?php
                                                        }elseif($is_alarm_240vRF_EP==true){
                                                        ?>
                                                            <strong style="color:red;">(Required for Quotes)</strong>
                                                        <?php
                                                        }
                                                    }

                                                    if( $is_alarm_240v_rf == true && is_numeric($alarm['price']) && $alarm['price']==0 ){ ?>
                                                        <span style="color:red; margin-left: 20px;"><?php echo "${$alarm_240v_rf_default_price}" ?></span>
                                                    <?php	
                                                    }
                                                    ?>
                                                </td>
                                                <td class="price_div">
                                                    <?php 
                                                    ##required alarms default price
                                                    if($is_alarm_240v_rf==true){
                                                        if($agencySelected!=true){
                                                            $new_alarm_price = $alarm_240v_rf_default_price;
                                                        }else{
                                                            $new_alarm_price = $alarm['price'];
                                                        }
                                                        
                                                    }elseif($is_alarm_240vRFCavf==true){
                                                        if($agencySelected!=true){
                                                            $new_alarm_price = $alarm_240vRFCav_default_price;
                                                        }else{
                                                            $new_alarm_price = $alarm['price'];
                                                        }
                                                    }elseif($is_alarm_240vRF_EP==true){
                                                        if($agencySelected!=true){
                                                            $new_alarm_price = $alarm_240vRF_EP_price;
                                                        }else{
                                                            $new_alarm_price = $alarm['price'];
                                                        }
                                                    }else{
                                                        $new_alarm_price = $alarm['price'];
                                                    }
                                                    ##required alarms default price end
                                                    ?>

                                                    <div class="input-group price_input_box">
                                                        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                        <input type="text" name="alarm_price[]" class="form-control alarm_price" value="<?php echo $new_alarm_price; ?>" />	
                                                    </div>																	
                                                </td>
                                                <td>
                                                    <div class="checkbox">
                                                        <input type="checkbox" id="<?php echo $index2.'_check2' ?>" name="agency_alarm_approve[]" class="approve agency_alarm_approve" <?php echo ($agencySelected==true)?'checked="checked"':''; ?> value="<?php echo $index2; ?>" />	
                                                        <label for="<?php echo $index2.'_check2' ?>">&nbsp;</label>
                                                    </div>	
                                                    <input type="hidden" name="alarm_id[]" class="alarm_id" value="<?php echo $alarm['alarm_pwr_id']; ?>" />
                                                    <input type="hidden" name="agency_alarms_orig_price[]" class="agency_alarms_orig_price" value="<?php echo $alarm['price']; ?>" />
                                                    <input type="hidden" name="alarm_name[]" class="alarm_name" value="<?php echo $alarm['alarm_pwr']; ?>" />
                                                    <input type="hidden" name="alarm_checked[]" class="alarm_checked" value="<?php echo ($agencySelected==true)?1:0; ?>" />
                                                    <input type="hidden" name="alarm_orig[]" class="alarm_orig" value="<?php echo ($agencySelected==true)?1:0; ?>" />
                                                </td>	
                                            </tr>
                                        <?php $index2++; } ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                        <div class="text-right"><button type="submit" name="btn-submit-alarms" value="btn-submit-alarms" class="btn btn-update-alarm-pricing">Update Alarms</button></div>

                    </form>

                    </div>
                    <div class="col-md-4">

                        <section class="card card-red-fill">
                            <header class="card-header"><?php echo (COUNTRY==1) ? $row['state'] : 'New Zealand' ?> Alarm Guide</header>
                            <div class="card-block">
                                
                                 <?php 
                                 if(COUNTRY==1){
                                    ?>
                                    <div style="margin-bottom:15px;">
                                        <strong>FREE ALARMS</strong>
                                        <ul class="disc">
                                            <?php
                                                if(empty($free_alarms)){
                                                    echo "None after 1/1/2022";
                                                }
                                                foreach($free_alarms as $fal){
                                                ?>
                                                    <li>
                                                    <?php
                                                        echo $fal->alarm_pwr;
                                                    ?>
                                                    </li>
                                                <?php 
                                                }
                                            ?>
                                        
                                            <!--
                                            <li>9v(EP)</li>
                                            <li>240v(EP)</li>
                                            -->
                                        </ul>
                                    </div>

                                    <div>
                                        <strong>PAID ALARMS</strong>
                                        <ul class="disc">
                                        <?php
                                            if(empty($paid_alarms)){
                                                echo "No Paid Alarms";
                                            }
                                            foreach($paid_alarms as $pal){
                                            ?>
                                                <li>
                                                <?php
                                                    echo $pal->alarm_pwr;
                                                ?>
                                                </li>
                                            <?php 
                                            }
                                        ?>
                                            <!--
                                            <li>9v</li>
                                            <li>240v</li>
                                            -->
                                        </ul>
                                    </div>
                                <?php } 
                                else{
                                    ?>
                                    <!-- New Zealand -->
                                    <div style="margin-bottom:15px;">
                                        <strong>FREE ALARMS</strong>
                                        <ul class="disc">
                                            <?php
                                                if(empty($free_alarms)){
                                                    echo "No Free Alarms";
                                                }
                                                foreach($free_alarms as $fal){
                                                ?>
                                                    <li>
                                                    <?php
                                                        echo $fal->alarm_pwr;
                                                    ?>
                                                    </li>
                                                <?php 
                                                }
                                            ?>
                                            <!--
                                            <li>3vLi(Orc)</li>
                                            <li>240v</li>
                                            -->
                                        </ul>
                                    </div>

                                    <div>
                                        <strong>PAID ALARMS</strong>
                                        <ul class="disc">
                                            <?php
                                                if(empty($paid_alarms)){
                                                    echo "No Paid Alarms";
                                                }
                                                foreach($paid_alarms as $pal){
                                                ?>
                                                    <li>
                                                    <?php
                                                        echo $pal->alarm_pwr;
                                                    ?>
                                                    </li>
                                                <?php 
                                                }
                                            ?>
                                            <!--
                                            <li>3vLi</li>
                                            <li>240v</li>
                                            -->
                                        </ul>
                                    </div>
                                    <?php
                                   }
                                 ?>
                                

                            </div>
                        </section>

                    </div>
                </div>
           
        </div>

    </div>
</div>
<div class="row text-left">
    <div class="col-md-6 columns">
        <section class="card card-blue-fill">
            <header class="card-header">Agency Special Pricing</header>

            <div class="row ml-2">

                <div class="card-block col-md-6">
                    <div class="form-group tt_boxes">
                        <label class="form-control-label">Agency Special Deal</label>
                        <a data-auto-focus="false" data-fancybox="" data-src="#vad_deals_and_discount" href="javascript:;"><textarea readonly class="form-control" rows="5"><?php echo ($row['agency_special_deal']!="")?$row['agency_special_deal']:"No Data"; ?></textarea></a>                 
                    </div>
                    <!-- <div class="form-group tt_boxes">
                        <label class="form-control-label">Multi-owner Discount</label>
                        <a data-auto-focus="false" data-fancybox="" data-src="#vad_deals_and_discount" href="javascript:;"><?php echo ($row['multi_owner_discount']!="")?$row['multi_owner_discount']:"No Data"; ?></a>                 
                    </div> -->
                </div>

                <div class="card-block col-md-6">
                    <div class="form-group tt_boxes">
                        <label class="form-control-label">Agency Exclusion Status</label>     
                        <?php
                        // price increase exclude check
                        $piea_sql = $this->db->query("
                        SELECT `exclude_until`
                        FROM `price_increase_excluded_agency`
                        WHERE `agency_id` = {$agency_id}
                        ");

                        $pie_txt = null;
                        if( $piea_sql->num_rows() > 0 ){ // if agency excluded

                            $piea_row = $piea_sql->row();

                            if( $this->system_model->isDateNotEmpty($piea_row->exclude_until) ){  // has excluded until
                                $pie_txt = 'Excluded until '.date('d/m/Y', strtotime($piea_row->exclude_until));
                            }else{
                                $pie_txt = 'Excluded permanently';
                            }

                        }else{
                            $pie_txt = 'Not Excluded';
                        }

                        echo $pie_txt;
                        ?>               
                    </div>
                    <div class="form-group tt_boxes">
                        <label class="form-control-label">Price Increase Status</label>   
                        <?php
                        // Agency price increase completed check
                        $aci_sql = $this->db->query("
                        SELECT `agency_completed`
                        FROM `agency_completed_increase`
                        WHERE `agency_id` = {$agency_id}
                        ");

                        $aci_txt = null;
                        if( $aci_sql->num_rows() > 0 ){ // if exist

                            $aci_row = $aci_sql->row();

                            if( $aci_row->agency_completed == 1 ){  // if completed
                                $aci_txt = 'Fully Completed';
                            }else{
                                $aci_txt = 'Agency Services Complete';
                            }

                        }else{
                            $aci_txt = 'Not Updated';
                        }

                        echo $aci_txt;
                        ?>                
                    </div>
                </div>

            </div>            

        </section>
    </div>
    <!-- Deals and Discount Fancybox -->                                      
    <div style="display:none" id="vad_deals_and_discount">
        <?php 
            echo form_open("/agency/update_agency/{$agency_id}/{$tab}","class=vad_form"); 
            
            $hidden_input_data_agency_id = array(
                'type'  => 'hidden',
                'name'  => 'agency_id',
                'id'    => 'agency_id',
                'value' => $agency_id,
                'class' => 'agency_id'
            );
            echo form_input($hidden_input_data_agency_id);
        ?> 
            <section class="card card-blue-fill">
                <header class="card-header">Agency Special Pricing</header>
                <div class="card-block">
                    <div class="form-group">
                        <label class="form-label"><strong>Agency Special Deal</strong></label>
                        <textarea title="Agency Special Deal" name='agency_special_deal' id='agency_special_deal' class='form-control formtextarea'><?php echo $row['agency_special_deal']; ?></textarea>
                        <input type="hidden" name="og_agency_special_deal" value="<?php echo $row['agency_special_deal']; ?>">
                    </div>
                    <!-- <div class="form-group">
                        <label class="form-label"><strong>Multi-owner Discount</strong></label>
                        <div class="input-group price_input_box">
                            <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                            <input type="text" name="multi_owner_discount" class="form-control multi_owner_discount" value="<?php echo $row['multi_owner_discount']; ?>" />	
                            <input type="hidden" name="og_multi_owner_discount" class="form-control" value="<?php echo $row['multi_owner_discount']; ?>" />	
                        </div>			
                    </div> -->
                </div>
            </section>
            <div class="text-right"><button type="submit" name="btn-submit-especial-deal" value="btn-submit-especial-deal" class="btn">Update Details</button></div>
        </form>
    </div>
    


    <div id="vad_agency_service_price_variation" class="col-md-6 columns">
        <section class="card card-blue-fill">
            
            <header class="card-header">Agency Service Price Variation</header>

            <div class="card-block row">
                <div class="col">          
                    <table class="table table-hover">
                        <tr>
                            <th>Amount</th>    
                            <th>Type</th>                       
                            <th>Scope</th>
                            <th>Reason</th>
                            <th>Expiry</th>
                            <th>Action</th>
                        </tr>
                        <?php
                        // agency price variation
                        $apv_sql = $this->db->query("
                        SELECT 
                            apv.`id` AS apv_id,
                            apv.`amount`,
                            apv.`type` AS apv_type,
                            apv.`reason` AS apv_reason,
                            apv.`scope`,
                            apv.`expiry`,

                            apvr.`reason` AS apvr_reason,

                            dv.`display_on`,

                            ajt.`type` AS ajt_type,
                            ajt.`short_name`
                        FROM `agency_price_variation` AS apv
                        LEFT JOIN `agency_price_variation_reason` AS apvr ON apv.`reason` = apvr.`id`
                        LEFT JOIN `display_variation` AS dv ON ( apv.`id` = dv.`variation_id` AND dv.`type` = 1 )
                        LEFT JOIN `alarm_job_type` AS ajt ON ( apv.`scope` = ajt.`id` AND apv.`scope` >= 2 )
                        WHERE apv.`agency_id` = {$agency_id}                    
                        AND apv.`active` = 1
                        ORDER BY 
                            apv.`type` ASC, 
                            apv.`scope` ASC,
                            apvr.`reason` ASC
                        ");                        
                        foreach( $apv_sql->result() as $apv_row ){ ?>
                            <tr <?php echo (  $this->system_model->isDateNotEmpty($apv_row->expiry) && $apv_row->expiry < date('Y-m-d') )?'style="text-decoration: line-through;"':null; ?>>
                                <td>$<span class="apv_amount"><?php echo number_format($apv_row->amount, 2); ?></span></td>
                                <td>
                                    <?php echo ( $apv_row->apv_type == 1 )?'Discount':'Surcharge';  ?>
                                    <input type="hidden" class="apv_type" value="<?php echo $apv_row->apv_type; ?>" />
                                </td>
                                <td>
                                    <?php 
                                    //echo ( $apv_row->scope == 1 )?'Agency':'Property';  

                                    if( $apv_row->scope == 0 && is_numeric($apv_row->scope) ){
                                        echo 'Agency';
                                    }else if( $apv_row->scope == 1 ){
                                        echo 'Property';
                                    }else{
                                        echo "{$apv_row->short_name} Service";
                                    }
                                    ?>
                                    <input type="hidden" class="apv_scope" value="<?php echo $apv_row->scope; ?>" />
                                </td>
                                <td>
                                    <?php echo $apv_row->apvr_reason; ?>
                                    <input type="hidden" class="apv_reason" value="<?php echo $apv_row->apv_reason; ?>" />
                                </td>
                                <td class="apv_expiry"><?php echo ( $this->system_model->isDateNotEmpty($apv_row->expiry) )?date('d/m/Y', strtotime($apv_row->expiry)):null; ?></td>
                               <td>                    
                                    <input type="hidden" class="apv_display_on" value="<?php echo $apv_row->display_on; ?>" />
                                    <input type="hidden" class="apv_id" value="<?php echo $apv_row->apv_id; ?>" />
                                    <a href='javascript:void(0);' data-toggle="tooltip" title="Edit">
                                        <i class="font-icon font-icon-pencil apv_edit float-left mr-2"></i>
                                    </a>
                                    <a href='javascript:void(0);' data-toggle="tooltip" title="Delete">
                                        <i class="font-icon font-icon-trash apv_delete float-left mr-2"></i>
                                    </a>
                                </td>                                
                            </tr>
                        <?php
                        }
                        ?>
                    </table>
                </div>
            </div> 
            
            <div class="card-block row">
                <div class="col">
                    <!--<button type="button" id="update_price_variation" class="btn btn-update">Update Price Variation</button>-->
                    <button type="button" id="add_price_variation" class="btn add_price_variation">Add Price Variation</button>
                </div>
            </div>
        </section>
        

    </div>
    <div id="vad_agency_service_price_variation" class="col-md-6 columns">
    </div>
    <div id="vad_agency_service_price_variation" class="col-md-6 columns">
        <section class="card card-blue-fill">
            
            <header class="card-header">Property Price Variation</header>
            <div class="card-block row">
                <div class="col">
                    <?php 
                        $total_applied = $variation_sql->num_rows();
                    ?>
                    <div class="row">
                        <label style="margin-left: 10px">Total amount of Properties with a variation applied:</label>
                        <a style="margin-left: 5px" data-auto-focus="false" data-fancybox="" data-src="#variation_applied" href="javascript:;">
                            <?=number_format($total_applied); ?>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Deals and Discount Fancybox end -->
</div>
                                    
<div style="display:none" id="variation_applied">
        <section class="card card-blue-fill">
            <header class="card-header">Properties with a Variation Applied</header>
            <div class="col-md-12">
                <div class="card-block">
                    <table id="prop_variation" class="display table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>Properties Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            foreach( $variation_sql->result() as $var_row ){ 
                                $prop_address = trim("{$var_row->address_1} {$var_row->address_2}, {$var_row->address_3} {$var_row->state} {$var_row->postcode}");
                            ?>
                            <tr>
                                <td><a href="<?php echo $this->config->item('crm_link'); ?>/view_property_details.php?id=<?php echo $var_row->property_id ?>"><?=$prop_address; ?></a></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
           
        </section>
        <script>
        $(document).ready(function () {
            $('#prop_variation').DataTable();
        });
        </script>
</div>

<div id="agency_price_variation_fb" style="display:none;">
    <!-- <h5 id="apv_header">Add Price Variation</h5> -->
    
    <form id="add_agency_variation_form">

    <section class="card card-blue-fill">
        <header class="card-header" id="apv_header">Add Price Variation</header>   
        
        <div class="card-block">
            <table class="table">
                <tr>
                    <th>Amount</th>
                    <td><input type="text" name="agency_price_variation_amount" id="agency_price_variation_amount" class="form-control" value="<?php echo ( $jv_row->amount > 0 )?number_format($jv_row->amount, 2):null; ?>" required /></td>
                </tr>
                <tr>
                    <th>Type</th>
                    <td>
                        <select id="apv_type" class="form-control" required>
                            <option value="">---</option>
                            <option value="1">Discount</option>
                            <option value="2" <?php echo ( $jv_row->type == 2 )?'selected':null; ?>>Surcharge</option>
                        </select>
                    </td>
                </tr>   
                <tr>
                    <th>Scope</th>
                    <td>
                        <select id="apv_scope" class="form-control" required>
                            <option value="">---</option>
                            <option value="0">Agency</option>
                            <option value="1">Property</option>
                            <option value="" disabled readonly>--- Service Types ---</option>
                            <?php
                            $ajt_sql = $this->db->query("
                            SELECT *
                            FROM `alarm_job_type`
                            WHERE `active` = 1
                            ORDER BY `type` ASC
                            ");
                            foreach( $ajt_sql->result() as $ajt_row ){ ?>
                                <option value="<?php echo $ajt_row->id; ?>"><?php echo $ajt_row->type; ?></option>
                            <?php
                            }
                            ?>   
                        </select>
                    </td>
                </tr>             
                <tr>
                    <th>Reason</th>
                    <td>
                        <select id="apv_reason" name="apv_reason"  class="form-control apv_reason" required>
                            <option value="">---</option>
                            <?php
                            $adr_sql = $this->db->query("
                            SELECT *
                            FROM `agency_price_variation_reason`
                            WHERE `active` = 1										
                            ORDER BY `reason` ASC
                            ");
                            foreach( $adr_sql->result() as $adr_row ){ ?>                           
                                <option data-is_discount="<?php echo $adr_row->is_discount; ?>" value="<?php echo $adr_row->id; ?>" <?php echo ( $adr_row->id == $jv_row->reason )?'selected':null; ?>><?php echo $adr_row->reason; ?></option>
                            <?php
                            }
                            ?>                        
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Display On</th>
                    <td>
                        <?php
                        // put back alger's code
                        $disply_on_where_in = array('3','6','7'); ## Display on Agency Portal, Invoice, Invoice & Agency Portal
                        $this->db->select('id,location');
                        $this->db->from('display_on');
                        $this->db->where('active',1);
                        $this->db->where_in('id',$disply_on_where_in);
                        $disply_on_q = $this->db->get();
                        ?>
                        <select name="apv_display_on" class="apv_display_on form-control" id="apv_display_on">
                            <option value="">---</option>
                            <?php foreach( $disply_on_q->result_array() as $tt_row_tt ){ ?>
                                <option value="<?php echo $tt_row_tt['id'] ?>"><?php echo $tt_row_tt['location'] ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Expiry</th>
                    <td>
                        <input type="text" name="apv_expiry" id="apv_expiry" class="form-control apv_expiry" />
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>
                        <input type="hidden" id="apv_id" />                    
                        <button type='submit' id="agency_price_variation_submit" class='btn float-right'>Submit</button>
                    </td>
                </tr>
            </table> 
        </div>
    </section>
        
    </form>       
</div>    



<script type="text/javascript">

    function clear_add_edit_variation_lightbox(){

        var lightbox = jQuery("#agency_price_variation_fb");

        // re-enable, disabled field 
        lightbox.find("#agency_price_variation_amount").prop("readonly",false);
        lightbox.find("#apv_type").prop("disabled",false);
        lightbox.find("#apv_scope").prop("disabled",false);
        lightbox.find("#apv_expiry").prop("disabled",false);

        // clear
        lightbox.find("#apv_id").val("");                 
        lightbox.find("#agency_price_variation_amount").val("");
        lightbox.find("#apv_type").val("");
        lightbox.find("#apv_scope").val("");
        lightbox.find("#apv_reason").val("");
        lightbox.find("#apv_display_on").val("");
        lightbox.find("#apv_expiry").val("");

    }

    jQuery(document).ready(function(){
        
        // custom datepicker, do not show past date
		jQuery('#apv_expiry').flatpickr({
			dateFormat: "d/m/Y",
			locale: {
				firstDayOfWeek: 1
			},
            minDate: "today"
		});

        // require approve price script
        jQuery(document).on("click",".approve",function(){
            
            // is approved hidden value
            var state = jQuery(this).prop("checked");
            if(state==true){				
                jQuery(this).parents("tr:first").removeClass("fadedText");
            }else{	
                jQuery(this).parents("tr:first").addClass("fadedText");			
            }

        });

        $('.btn-update').click(function(){
                var agency_status = "<?php echo $row['status'] ?>";
                var error="";
                var submitcount = 0;
                var is_price_increase_excluded = jQuery("#$is_price_increase_excluded").val();

                //QLD IC UPGRADE VALIDATION
                <?php
                if( $row['state'] == 'QLD' ){ ?>
                
                    if( agency_status =='active' ){
                      
                        jQuery(".vad_pricing_table .agency_service_approve").each(function(){
                        
                            var isChecked = jQuery(this).prop("checked");
                            var id = jQuery(this).parents("td:first").find(".service_id").val();
                            var alarm_ic_upgrade_price = jQuery(this).parents("tr.service_tr").find(".service_price").val();

                            if( is_price_increase_excluded == 1 ){

                                if( id == 12 && isChecked == false ){ //  Smoke Alarms (IC) 
                                    error += "Smoke Alarms (IC) is required\n";
                                }
                                if(id == 12 && isChecked && (alarm_ic_upgrade_price=="" || alarm_ic_upgrade_price <=0)){
                                    error += "Invalid Smoke Alarms (IC) price\n";
                                }

                            }                                                    
                            
                        });

                    }

                <?php
                }
                ?>

                if(error!=""){
                    swal('',error,'error');
                    return false;
                }

                if(submitcount==0){
                    submitcount++;
                    return;
                }else{
                    swal('','Form submission is in progress','error');
                    return false;
                }

            })

            $('.btn-update-alarm-pricing').click(function(){
                var agency_status = "<?php echo $row['status'] ?>";
                var error="";
                var submitcount = 0;

                //QLD IC UPGRADE VALIDATION
                <?php
                if( $row['state'] == 'QLD' ){ ?>
                
                    if( agency_status =='active' ){
                      
                        var is_alarm_req_for_quotes_row_node = jQuery(".is_alarm_req_for_quotes_row"); // 'required for quotes' alarms
                        var agency_alarm_approve_node = is_alarm_req_for_quotes_row_node.find('.agency_alarm_approve:checked');
                        var agency_price_is_empty = false;
				        var agency_price_is_zero = false;

                        is_alarm_req_for_quotes_row_node.each(function(){

                            var alarm_req_for_quotes_row_node = jQuery(this);
                            var agen_price = alarm_req_for_quotes_row_node.find('.alarm_price').val(); // alarm price					

                            if( agen_price != '' ){ // not empty
                                
                                if( !(agen_price > 0) ){
                                    agency_price_is_zero = true;
                                }

                            }else{ // empty
                                agency_price_is_empty = true;
                            }					

                        });

                        if( agency_price_is_empty == true ){
                            error += "'Required for Quotes' alarms price is required\n";
                        }

                        if( agency_price_is_zero == true ){
                            error += "'Required for Quotes' alarms price must be greater than $0\n";
                        }

                        if( agency_alarm_approve_node.length == 0  ){ 
                                error += "At least one 'Required for Quotes' alarms must be approved\n";
                        }

                    }

                <?php
                }
                ?>

                if(error!=""){
                    swal('',error,'error');
                    return false;
                }

                if(submitcount==0){
                    submitcount++;
                    return;
                }else{
                    swal('','Form submission is in progress','error');
                    return false;
                }

            })

            jQuery(".agency_alarm_approve").change(function(){
               
                if($(this).is(":checked")){
                    jQuery(this).parents("tr:first").find(".alarm_checked").val(1);
                }else{
                    jQuery(this).parents("tr:first").find(".alarm_checked").val(0);
                }
                
            })

            jQuery(".agency_service_approve").change(function(){
               
               if($(this).is(":checked")){
                   jQuery(this).parents("tr:first").find(".services_checked").val(1);
               }else{
                   jQuery(this).parents("tr:first").find(".services_checked").val(0);
               }
               
           })

           /*
           jQuery("#update_price_variation").click(function(){

                var agency_id = <?php echo $agency_id; ?>;
                var apv_discount_amount =  jQuery("#apv_discount_amount").val();
                var apv_discount_reason =  jQuery("#apv_discount_reason").val();
                var apv_surcharge_amount =  jQuery("#apv_surcharge_amount").val();
                var apv_surcharge_reason =  jQuery("#apv_surcharge_reason").val();

                jQuery("#load-screen").show();		
                jQuery.ajax({
                    type: "POST",
                    url: "/agency/update_price_variation",
                    data: { 
                        agency_id: agency_id,
                        apv_discount_amount: apv_discount_amount,
                        apv_discount_reason: apv_discount_reason,
                        apv_surcharge_amount: apv_surcharge_amount,
                        apv_surcharge_reason: apv_surcharge_reason
                    }
                }).done(function( ret ){
                    
                    jQuery("#load-screen").hide();     
                    location.reload();                                                                
                    
                });
            
           });
           */

            clear_add_edit_variation_lightbox(); // clear on load

            // add variation
            jQuery("#add_price_variation").click(function(){

                jQuery("#apv_header").text('Add Price Variation');

                // clear
                clear_add_edit_variation_lightbox();

                jQuery("#agency_price_variation_submit").text("Submit");
                            
                // launch fancybox
                $.fancybox.open({
                    src  : '#agency_price_variation_fb'
                });

            });

            // edit variations
            jQuery(".apv_edit").click(function(){

                jQuery("#apv_header").text('Edit Price Variation');
            
                // clear
                clear_add_edit_variation_lightbox();

                var apv_edit_dom = jQuery(this);
                var parent_tr = apv_edit_dom.parents("tr:first");

                var apv_id = parent_tr.find(".apv_id").val();

                var apv_amount = parent_tr.find(".apv_amount").text();
                var apv_type = parent_tr.find(".apv_type").val();
                var apv_scope = parent_tr.find(".apv_scope").val();
                var apv_reason = parent_tr.find(".apv_reason").val();

                var apv_display_on = parent_tr.find(".apv_display_on").val();
                
                var apv_expiry = parent_tr.find(".apv_expiry").text();

                jQuery("#apv_id").val(apv_id); // set apv ID

                var lightbox = jQuery("#agency_price_variation_fb");
                lightbox.find("#agency_price_variation_amount").val(apv_amount);
                lightbox.find("#agency_price_variation_amount").prop("readonly",true);
                lightbox.find("#apv_type").val(apv_type);
                lightbox.find("#apv_type").prop("disabled",true);
                lightbox.find("#apv_scope").val(apv_scope);
                lightbox.find("#apv_scope").prop("disabled",true);
                lightbox.find("#apv_reason").val(apv_reason);
                
                lightbox.find("#apv_display_on").val(apv_display_on);

                let today = new Date('<?php echo date('Y-m-d'); ?>');
                let expiry = new Date(formatToDateToYmd(apv_expiry));
                if( apv_expiry != '' && ( expiry.getTime() <= today.getTime() ) ){
                    lightbox.find("#apv_expiry").prop("disabled",true);
                }
                lightbox.find("#apv_expiry").val(apv_expiry);

                jQuery("#agency_price_variation_submit").text("Update");
                
                // launch fancybox
                $.fancybox.open({
                    src  : '#agency_price_variation_fb'
                });                

            });

            jQuery("#apv_type").change(function(){

                var apv_type = jQuery(this).val();

                if( apv_type == 1 ){ // discount

                    jQuery("#apv_reason option[data-is_discount=1]").show(); // discount
                    jQuery("#apv_reason option[data-is_discount=0]").hide(); // surcharge                  
                    
                }else{ // surcharge

                    jQuery("#apv_reason option[data-is_discount=1]").hide(); // discount
                    jQuery("#apv_reason option[data-is_discount=0]").show(); // surcharge                    

                }

            });

            // update job price variation
            jQuery("#add_agency_variation_form").submit(function (e) {

                e.preventDefault();

                var lightbox = jQuery("#agency_price_variation_fb");
                
                var agency_id = <?php echo $agency_id; ?>;
                var agency_var_amount = lightbox.find("#agency_price_variation_amount").val();
                var agency_var_type = lightbox.find("#apv_type").val();
                var agency_var_type_text = lightbox.find("#apv_type option:selected").text();
                var agency_var_scope = lightbox.find("#apv_scope").val();
                var agency_var_scope_text = lightbox.find("#apv_scope option:selected").text();

                var apv_display_on = lightbox.find("#apv_display_on").val();
                var apv_display_on_text = lightbox.find("#apv_display_on option:selected").text();

                var apv_reason = lightbox.find("#apv_reason").val();
                var apv_reason_text = lightbox.find("#apv_reason option:selected").text();

                var apv_expiry = lightbox.find("#apv_expiry").val();

                var apv_id = lightbox.find("#apv_id").val();
                
                if (parseInt(agency_id) > 0 && agency_var_amount > 0) {

                    jQuery("#load-screen").show();
                    jQuery.ajax({
                        type: "POST",
                        url: "/agency/update_agency_price_variation",
                        data: {
                            agency_id: agency_id,
                            agency_var_amount: agency_var_amount,
                            agency_var_type: agency_var_type,
                            agency_var_type_text: agency_var_type_text,
                            agency_var_reason: apv_reason,
                            agency_var_reason_text: apv_reason_text,
                            agency_var_scope: agency_var_scope,
                            agency_var_scope_text: agency_var_scope_text,
                            apv_expiry: apv_expiry,

                            apv_display_on: apv_display_on,
                            apv_display_on_text: apv_display_on_text,

                            apv_id: apv_id                         
                        }
                    }).done(function (ret) {

                        jQuery("#load-screen").hide();
                        //location.reload();

                        var swal_txt = ( apv_id > 0 )?'Variation Update Successful':'New Variation Added';

                        swal({
                            title: "Success!",
                            text: swal_txt,
                            type: "success",
                            confirmButtonClass: "btn-success",
                            showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                            timer: <?php echo $this->config->item('timer') ?>
                        });	
                        setTimeout(function(){ window.location='/agency/view_agency_details/'+agency_id+'/4'; }, <?php echo $this->config->item('timer') ?>);						

                    });

                }
                

                return false;

            });

            jQuery(".apv_delete").click(function (e) {

                var agency_id = <?php echo $agency_id; ?>;

                var apv_delete_dom = jQuery(this);
                var parent_tr = apv_delete_dom.parents("tr:first");

                var apv_id = parent_tr.find(".apv_id").val();

                swal({
					title: "Warning!",
					text: "Are you sure you want to delete variation?",
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
                        
                        if( apv_id > 0 ){

                            jQuery("#load-screen").show();
                            jQuery.ajax({
                                type: "POST",
                                url: "/agency/delete_agency_price_variation",
                                data: {
                                    apv_id: apv_id,
                                    agency_id: agency_id                          
                                }
                            }).done(function (ret) {

                                jQuery("#load-screen").hide();
                                swal({
                                    title: "Success!",
                                    text: 'Delete Successful',
                                    type: "success",
                                    confirmButtonClass: "btn-success",
                                    showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                                    timer: <?php echo $this->config->item('timer') ?>
                                });	
                                setTimeout(function(){ window.location='/agency/view_agency_details/'+agency_id+'/4'; }, <?php echo $this->config->item('timer') ?>);						

                            });	

                        }																	

					}

				});	
            
            });            


    })

</script>