
                     <style>
                        .services_chkbox_wrapper label.btn.active {
                            background-color: #00a8ff !important;
                            border-color: #00a8ff !important;
                            color: #fff;
                        }
                        .services_chkbox_wrapper label.btn {
                            background: none;
                            color: #00a8ff;
                            border: 1px solid #00a8ff;
                        }
                     </style>
                        <?php 
                            $index = 0;
                            foreach($agency_services_list->result_array() as $row) {
                        ?>
              
                        <div class="row options_wrapper services_tr">
                        
                        <div style="display:none;">
                            <input type="hidden" name="alarm_job_type_id[]" value="<?php echo $row['id'] ?>">
                            <input type="hidden" name="price_changed[]" class="price_changed" value="0">	
						</div>
                            <div class="col-md-3">
                                <label><?php 
                                if($row['id']==14){
                                    echo "Bundle Smoke Alarm, Corded Window, Safety Switch (Interconnected) $".$row['price'];
                                }else if($row['id']==13){
                                    echo "Smoke Alarm & Safety Switch (Interconnected) $".$row['price'];
                                }else if($row['id']==12){
                                    echo "Smoke Alarms (Interconnected) $99.00"; //IC manual price for add_sales_properties
                                }else if($row['id']==9){
                                    echo "Bundle Smoke Alarm, Corded Window, Safety Switch $".$row['price'];
                                }else{
                                    echo $row['type']." $".$row['price'];
                                }
                                ?></label>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group services_chkbox_wrapper">
                                   <!-- <div class="btn-group" data-toggle="buttons">-->
                                    <div class="btn-group form-group">
                                        <label class="btn" style="display: none;;">
                                            <input name="service<?php echo $index ?>" class="serv_sats css-checkbox serv_status<?php echo $index ?>" checked="checked" id="main1radio<?php echo $index ?>" value="1" type="radio"> SATS
                                        </label>
                                                                                        
                                    </div>
                                    &nbsp;<button class="btn form-group btn-sm btn-danger change_price" type="button">Change Price</button>

                                    <div class="change_price_block" style="display:none;">
                                        <div class="row">
                                            <div class="col-md-2">
                                                <div class="form-control-wrapper form-control-icon-left">
                                                    <input type="text" name="price[]" class="price form-control" value="99.00">
                                                    <span class="font-icon">$</span>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <select name="price_reason[]" class="form-control price_reason" data-field="Change Price Reason">
                                                    <option value="">Select Reason *</option>
                                                    <option value="FOC">FOC</option>
                                                    <option value="Price match">Price match</option>
                                                    <option value="Multiple properties">Multiple properties</option>
                                                    <option value="Agents Property">Agents Property</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" name="price_details[]" class="tenantinput form-control price_details" placeholder="Details" data-field="Change Price Details">
                                            </div>
                                            <div class="col-md-3">
                                                <button type="button" class="btn btn-danger cancel_change_price">Cancel</button>
                                            </div>
                                        </div>
                                      
                                       
                                        
                                    </div>
                                   
                                    <span class='txt-info'></span>

                                </div>
                            </div>
                        </div>

                        <?php $index++; } ?>

 <input type="hidden" id="sats_info" name="sats_info" class="sats_info" value="01" />

                        <script type="text/javascript">

                            jQuery(document).ready(function(){

                                // show hide SATS info container tweak
                                jQuery(".serv_sats").change(function(){
                                        jQuery(".sats_info").val(1);
                                });	
                                    
                                jQuery(".serv_not_sats").change(function(){
                                    if( jQuery(".serv_sats:checked").length==0 ){
                                        jQuery(".sats_info").val(0);
                                    }		
                                });



                                // radio buttons custom toggle script hack/fixed
                                jQuery('.btn-group label.btn').click(function(){
                                    jQuery(this).parent('.btn-group').find('label').removeClass('active')
                                    jQuery(this).addClass('active');
                                })


                                $('.change_price').click(function(){
                                    $(this).parents('.services_chkbox_wrapper').find('.change_price').hide();
                                    $(this).parents('.services_chkbox_wrapper').find('.change_price_block').slideDown('slow');
                                    $(this).parents('.services_tr').find('.price_changed').val(1);

                                    //add required class
                                    $(this).parents('.services_chkbox_wrapper').find('.price_reason').addClass('g_req');
                                    //$(this).parents('.services_chkbox_wrapper').find('.price_details').addClass('g_req'); ##disable change price details required field

                                })

                                $('.cancel_change_price').click(function(){
                                    $(this).parents('.services_chkbox_wrapper').find('.change_price').show();
                                    $(this).parents('.services_chkbox_wrapper').find('.change_price_block').slideUp('slow');
                                    $(this).parents('.services_tr').find('.price_changed').val(0);

                                    //clear
                                    $(this).parents('.services_chkbox_wrapper').find('.price_reason').val('');
                                    $(this).parents('.services_chkbox_wrapper').find('.price_details').val('');

                                     //remove required class
                                     $(this).parents('.services_chkbox_wrapper').find('.price_reason').removeClass('g_req');
                                    $(this).parents('.services_chkbox_wrapper').find('.price_details').removeClass('g_req');
                                })



                            })

                        </script>
