<style>
.item_to_test_tbody ul li{
    padding: 5px 0;
}
.green_check_img,
.red_cross_img{
	width: 15px;
	position: relative;
	left: 3px;
	bottom: 1px;
}
.we_pass_col{
    display: none;
}
</style>
<table class="table main-table">

    <thead>
        <tr>
            <th>Do any taps on the premises leak?</th>
            <th id="leak_notes_th" style="display:<?php echo ( $job_row->property_leaks == 1 )?'block':'none'; ?>">Describe the leak location (Agency will see this note!!) </th>            
        </tr>
    </thead>

    <tbody>
    <tr>        
        <td>
            <input type="hidden" id="service_type" name="service_type" value="15" />
            <div class="radio">
                <input type="radio" name="property_leaks" class="form-contro property_leaks chk_yes" id="property_leaks_yes" data-db_table_field="property_leaks" data-db_table="jobs" <?php echo ( $job_row->property_leaks == 1 )?'checked':null; ?> value="1" /> 
                <label class="inline-block" for="property_leaks_yes">Yes</label> 
            </div>
            <div class="radio">
                <input type="radio" name="property_leaks" class="form-control property_leaks chk_no" id="property_leaks_no" data-db_table_field="property_leaks" data-db_table="jobs" <?php echo ( $job_row->property_leaks == 0 && is_numeric($job_row->property_leaks) )?'checked':null; ?> value="0" /> 
                <label class="inline-block" for="property_leaks_no">No</label> 
            </div>
        </td>        
        <td style="display:<?php echo ( $job_row->property_leaks == 1 )?'table-cell':'none'; ?>">            
            <textarea class="form-control leak_notes" name="leak_notes" id="leak_notes" data-db_table_field="leak_notes" data-db_table="jobs"><?php echo stripslashes($job_row->leak_notes); ?></textarea>
        </td>
    </tr> 
    </tbody>

</table>  

<h5 class="heading">Item to Test</h5>

<table class="table main-table">

    <thead>
        <tr>
            <th>DO TEST</th>
            <th colspan="2">DO NOT TEST</th>            
        </tr>
    </thead>

    <tbody class="item_to_test_tbody">
        <tr>        
            <td class="align-top">
                <ul>
                    <li>Shower Heads <img class="green_check_img" src="/images/green_check.png"></th></li>
                    <li>Kitchen Sink <img class="green_check_img" src="/images/green_check.png"></th></li>
                    <li>Bathroom Sink <img class="green_check_img" src="/images/green_check.png"></th></li>
                    <li>Toilets <img class="green_check_img" src="/images/green_check.png"></th></li>
                </ul>
            </td>        
            <td class="align-top">
                <ul>
                    <li>Bathtub <img  class="red_cross_img" src="/images/red_cross.png"></li>
                    <li>Washing Machine Tap <img  class="red_cross_img" src="/images/red_cross.png"></li>
                    <li>Outdoor Taps <img  class="red_cross_img" src="/images/red_cross.png"></li>
                    <li>Laundry Sink <img  class="red_cross_img" src="/images/red_cross.png"></li>
                </ul>
            </td> 
            <td>All toilets to be recorded</td>
        </tr> 
    </tbody>

</table>  

<?php
// get new alarms
$we_sql = $this->db->query("
SELECT 
    we.`water_efficiency_id`,
    we.`device`,
    we.`pass`,
    we.`location`,
    we.`note`,

    wed.`name` AS wed_name
FROM `water_efficiency` AS we
LEFT JOIN `water_efficiency_device` AS wed ON we.`device` = wed.`water_efficiency_device_id`
WHERE we.`job_id` = {$this->input->get_post('job_id')}
AND we.`active` = 1
");
if( $we_sql->num_rows() > 0 ){
    $row_total = 0;

    $query_water = $this->db->query("
    SELECT *
    FROM `water_efficiency`
    WHERE `job_id` = {$this->input->get_post('job_id')} AND pass IS NULL
    ");
    $row_total = $query_water->num_rows();
    
    ?>
    <table class="table main-table">

        <thead>
                
            <tr>
                <th>Device</th>		
                <th>Toilet Type / Is water flow less than 9L per minute?</th>
                <th>Location</th>
                <th>Note</th>
                <th>Delete</th>
            </tr>

        </thead>

        <tbody>
            <input type="hidden" id="required_radio" name="required_radio" value="<?php echo $row_total; ?>" />
            <input type="hidden" id="job_id" name="job_id" value="<?php echo $this->input->get_post('job_id'); ?>" />
            <?php 
            $x = 0;           
            foreach( $we_sql->result() as $we_row ){ 
                
                if( $we_row->device == 2 ){ // toilet
					$pass_yes = 'Dual';
					$pass_no = 'Single';										
				}else{					
					$pass_yes = 'Yes';
					$pass_no = 'No';
				}

                $required_radio = 0;
                if ($we_row->pass == '') {
                    $required_radio = 1;
                }

                ?>
                <tr>   
                    <td><?php echo $we_row->wed_name; ?></td>	
                    <td>
                        <div class="radio">
                            <input type="radio" name="we_pass[<?php echo $x; ?>]" id="we_pass_yes<?php echo $we_row->water_efficiency_id; ?>" class="chk_yes we_pass" data-db_table_field="pass" value="1" <?php echo (  $we_row->pass == 1 )?'checked':null; ?> />
                            <label class="inline-block" for="we_pass_yes<?php echo $we_row->water_efficiency_id; ?>"><?php echo $pass_yes; ?></label>
                        </div>
                        <div class="radio">
                            <input type="radio" name="we_pass[<?php echo $x; ?>]" id="we_pass_no<?php echo $we_row->water_efficiency_id; ?>" class="chk_no we_pass" data-db_table_field="pass" value="0" <?php echo (  $we_row->pass == 0 && is_numeric($we_row->pass) )?'checked':null; ?> />
                            <label class="inline-block" for="we_pass_no<?php echo $we_row->water_efficiency_id; ?>"><?php echo $pass_no; ?></label>
                        </div>
                    </td>
                    <td>
                        <input type="text" name="we_location" class="form-control we_location" value="<?php echo strtoupper($we_row->location); ?>" data-db_table_field="location" />
                    </td>
                    <td>
                        <input type="text" name="we_note" class="form-control we_note" value="<?php echo $we_row->note; ?>" data-db_table_field="note" />
                    </td>                                 
                    <td>
                        <input type="hidden" class="water_efficiency_id" value="<?php echo $we_row->water_efficiency_id; ?>">
                        <input type="hidden" class="we_device" value="<?php echo $we_row->device; ?>">
                        <button type="button" id="btn_delete" class="btn btn-danger delete_we_btn">Delete</button>
                    </td>
                </tr>
            <?php
            $x++;
            }
            ?>                         
            
        </tbody>                                  

    </table>
<?php
}else{ ?>
    <div class="alert alert-danger alert-no-border alert-close alert-dismissible fade show" role="alert">                       
        This Property has no Water Efficiency on file. Please add Water Efficiency below
    </div>
<?php
}
?>
                            


<button type="button" id="dispay_add_we_btn" class="btn">Add Water Efficiency</button>

<div class="row mt-3">

    <div class="col">
        Water Effeciency Tested
        <input type="number" id="we_items_tested" class="form-control d-inline we_items_tested" data-db_table_field="we_items_tested" data-db_table="jobs" value="<?php echo $job_row->we_items_tested; ?>" />
    </div>

</div>

<!-- ADD Water Efficiency -->							
<div id="add_we_fb" class="fancybox" style="display:none;" >

    <h4>Add Water Efficiency <span class="text-danger">(All Toilets must be recorded)</span></h4>

    <table id="add_we_tbl" class="table main-table">

        <thead>
                
            <tr>
                <th>Location</th>
                <th>Device</th>    
                <th class="we_pass_col">
                    <span class="we_pass_lbl">Pass</span>
                </th>
                <th>Note (If Needed)</th>           
            </tr>

        </thead>

        <tbody class="add_we_tbody">

            <tr class="we_tr">                    
                <td>
                    <input type="text" class="form-control we_location" />
                </td>
                <td>
                    <select class="form-control we_device">
                        <option value="">---</option>
                        <?php
                        // get WE data
                        $wed_sql = $this->db->query("
                        SELECT 
                            `water_efficiency_device_id`,
                            `name`
                        FROM `water_efficiency_device` 
                        WHERE `active` = 1
                        ");

                        foreach( $wed_sql->result() as $wed_row ){ ?>
                            <option value="<?php echo $wed_row->water_efficiency_device_id; ?>"><?php echo $wed_row->name; ?></option>
                        <?php
                        }
                        ?>                                                         
                    </select>	 
                </td>
                <td class="we_pass_col">

                    <!--
                    <ul class="we_radio">                         
                        <div class="radio">
                            <input type="radio" name="we_pass" id="we_pass_yes" class="chk_yes inline-block ts_cleaned we_pass we_pass_yes" data-db_table_field="ts_cleaned" value="1" />
                            <label class="inline-block we_pass_lbl_yes" for="we_pass_yes">Yes</label>
                        </div>
                        <div class="radio">
                            <input type="radio" name="we_pass" id="we_pass_no" class="chk_no inline-block ts_cleaned we_pass we_pass_no" data-db_table_field="ts_cleaned" value="0" />
                            <label class="inline-block we_pass_lbl_no" for="we_pass_no">No</label>
                        </div>
                    </ul>
                    -->

                    <select id="we_pass" class="form-control we_pass">
                        <option value="">---</option>                        
                        <option value="0" class="we_pass_lbl_no">NO</option>
                        <option value="1" class="we_pass_lbl_yes">Yes</option>                                                       
                    </select>

                </td>
                <td>
                    <textarea class="form-control we_notes"></textarea>
                </td>
            </tr>             
           
        </tbody>                                  

    </table>

    <button type="button" id="add_we_btn" class="btn btn-success">Add another Item</button>
    <button type="button" id="save_we_btn" class="btn float-right">Save and Close</button>	

</div>
<script>
function ts_ajax_water_efficiency_inline_update(dom){
    
    var parent_row = dom.parents("tr:first");
    var db_table_field = dom.attr("data-db_table_field");
    var db_table_value  = dom.val(); 
    var water_efficiency_id = parent_row.find(".water_efficiency_id").val();

    // job update
    if( water_efficiency_id > 0 ){

        //jQuery('#load-screen').show();
        jQuery(".techsheet_tab_next:visible").prop("disabled",true); // disable NEXT button

        jQuery.ajax({
            type: "POST",
            url: "/jobs/ajax_techsheet_water_efficiency_row_update",
            data: { 
                water_efficiency_id: water_efficiency_id,
                db_table_field: db_table_field,
                db_table_value: db_table_value,
                job_id: <?php echo $this->input->get_post('job_id'); ?>
            }
        }).done(function( ret ){

            //jQuery('#load-screen').hide(); 
            // location.reload();
            jQuery("#required_radio").val(ret);
            jQuery(".techsheet_tab_next:visible").prop("disabled",false); // enable NEXT button                            			

        });

    }

}
jQuery(document).ready(function(){

    // display add alarm form
    jQuery("#dispay_add_we_btn").click(function(){

        $.fancybox.open({
            src  : '#add_we_fb'
        });

    });

    // add more alarm to the form
    jQuery("#add_we_btn").click(function(){

        let we = jQuery(".add_we_tbody:last").clone();
        jQuery("#add_we_tbl").append(we);

    });

    // Add Water Effeciency
	jQuery("#save_we_btn").click(function(){
		
		var dom = jQuery(this); 
        var we_arr = [];

        var error_arr = [];
        jQuery(".we_tr").each(function(){

            dom = jQuery(this);
            
            var we_location = dom.find(".we_location").val();
            var we_device = dom.find(".we_device").val();
            var we_pass = dom.find(".we_pass").val();
            var we_notes = dom.find(".we_notes").val();            

            
            if( we_location == '' ){
                var error_txt = 'Location is required';
                if( error_arr.includes(error_txt) == false ){
                    error_arr.push(error_txt);
                }
                
            } 

            if( we_device == '' ){
                var error_txt = 'Device is required';
                if( error_arr.includes(error_txt) == false ){
                    error_arr.push(error_txt);
                }
                
            }   

            if( we_pass == '---' ){
                var error_txt = 'Water Flow is required';
                if( error_arr.includes(error_txt) == false ){
                    error_arr.push(error_txt);
                }
                
            }  
          
            json_data = {
                'we_location': we_location,
                'we_device': we_device,
                'we_pass': we_pass,
                'we_notes': we_notes
            }
            var json_str = JSON.stringify(json_data);

            we_arr.push(json_str);

        });  

        //console.log(we_arr); 

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
                url: "/jobs/ajax_add_water_effeciency",
                data: {
                    job_id: <?php echo $this->input->get_post('job_id'); ?>,                    
                    we_arr: we_arr
                }
            }).done(function (ret) {

                jQuery('#load-screen').hide();
                jQuery(".techsheet_tab_next:visible").prop("disabled",false); // enable NEXT button
                location.reload();

            });            
            

        }               
		
				
	});



    // delete water efficiency
    jQuery(".delete_we_btn").click(function(){

        var dom = jQuery(this);
        var parent_row = dom.parents("tr:first");

        var water_efficiency_id = parent_row.find(".water_efficiency_id").val();

        swal({
            title: "Warning!",
            text: "This will delete this water efficiency, do you want to continue?",
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
                    url: "/jobs/ajax_delete_techsheet_water_efficiency",
                    data: {
                        job_id: <?php echo $this->input->get_post('job_id'); ?>,                        
                        water_efficiency_id: water_efficiency_id
                    }
                }).done(function (ret) {

                    jQuery('#load-screen').hide();
                    jQuery(".techsheet_tab_next:visible").prop("disabled",false); // enable NEXT button
                    location.reload();

                });		

            }

        });	

    });


    // corded window inline ajax update        
    jQuery(".we_pass, .we_location, .we_note").change(function(){

        var dom = jQuery(this);         
        ts_ajax_water_efficiency_inline_update(dom);   

    });


    // pass/fail label toggle
	jQuery("#add_we_tbl").on("change",".we_device",function(){

        var node = jQuery(this);
        var parent_table = node.parents("#add_we_tbl:first");
        var parent = node.parents(".add_we_tbody:first");
        var we_device = node.val();

        console.log("we_device: "+we_device);

        if( we_device == 2 ){ // toilet

            parent_table.find(".we_pass_col").show();
            parent_table.find(".we_pass_lbl").html("Toilet Type");

            parent.find(".we_pass_lbl_yes").html("Dual");
            parent.find(".we_pass_lbl_no").html("Single");

        }else if( we_device == 1 || we_device == 3 ){
            
            parent_table.find(".we_pass_col").show();
            parent_table.find(".we_pass_lbl").html("Is water flow less than 9L per minute?");

            parent.find(".we_pass_lbl_yes").html("Yes");
            parent.find(".we_pass_lbl_no").html("No");

        }else{

            parent_table.find(".we_pass_col").hide();
            parent_table.find(".we_pass_lbl").html("Pass?");

            parent.find(".we_pass_lbl_yes").html("Yes");
            parent.find(".we_pass_lbl_no").html("No");

        }

    });


    // describe leak toggle
    jQuery(".property_leaks").click(function(){

        var node = jQuery(this);
        var property_leaks = node.val();

        console.log("property_leaks: "+property_leaks);

        if( property_leaks == 1 ){
            jQuery("#leak_notes").show();
            jQuery("#leak_notes_th").show();
        }else{
            jQuery("#leak_notes").hide();
            jQuery("#leak_notes_th").hide();
        }

    });
    

});
</script>