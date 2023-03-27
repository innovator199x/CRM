<?php
// get new alarms
$cw_sql = $this->db->query("
    SELECT `corded_window_id`, `location`, `num_of_windows`
    FROM `corded_window`
    WHERE `job_id` = {$this->input->get_post('job_id')}
    ORDER BY `location`
");
if( $cw_sql->num_rows() > 0 ){ ?>
    <table class="table main-table">

        <thead>
                
            <tr>
                <th>Location</th>
                <th>Number of windows</th>                    
                <th>Delete</th>
            </tr>

        </thead>

        <tbody>

            <?php            
            foreach( $cw_sql->result() as $cw_row ){ ?>
                <tr>    
                    <td>
                        <input type="text" name="cw_location" id="cw_location" class="form-control cw_location" value="<?php echo strtoupper($cw_row->location); ?>" data-db_table_field="location" />
                    </td>
                    <td>
                        <input type="text" name="num_of_windows" id="num_of_windows" class="form-control num_of_windows" value="<?php echo $cw_row->num_of_windows; ?>" data-db_table_field="num_of_windows" />
                    </td>                                 
                    <td>
                        <input type="hidden" class="corded_window_id" value="<?php echo $cw_row->corded_window_id; ?>">
                        <button type="button" id="btn_delete" class="btn btn-danger delete_cw_btn">Delete</button>
                    </td>
                </tr>
            <?php
            }
            ?>                         
            
        </tbody>                                  

    </table>
<?php
}else{ ?>
    <div class="alert alert-danger alert-no-border alert-close alert-dismissible fade show" role="alert">                       
        This Property has no Corded Window on file. Please add Corded Window below
    </div>
<?php
}
?>

<button type="button" id="dispay_add_cw_btn" class="btn">Add Window</button>

<div class="row mt-3">

    <div class="col">
        Corded Windows Tested
        <input type="number" id="cw_items_tested" class="form-control d-inline cw_items_tested" data-db_table_field="cw_items_tested" data-db_table="jobs" value="<?php echo $job_row->cw_items_tested; ?>" />
    </div>

</div>


<!-- ADD Safety Switch -->							
<div id="add_cw_fb" class="fancybox" style="display:none;" >

    <h4>Add Corded Window</h4>

    <table id="add_cw_tbl" class="table main-table">

        <thead>
                
            <tr>
                <th>Location</th>
                <th>Number of windows</th>               
            </tr>

        </thead>

        <tbody class="add_cw_tbody">

            <tr class="cw_tr">                    
                <td>
                    <input type="text" class="form-control cw_location" />
                </td>
                <td>
                    <input type="number" class="form-control cw_num_of_windows" />
                </td>
            </tr>             
           
        </tbody>                                  

    </table>

    <button type="button" id="add_cw_btn" class="btn btn-success">Add another Window</button>
    <button type="button" id="save_cw_btn" class="btn float-right">Save and Close</button>	

</div>
<script>
function ts_ajax_corded_window_inline_update(dom){
    
    var parent_row = dom.parents("tr:first");
    var db_table_field = dom.attr("data-db_table_field");
    var db_table_value  = dom.val(); 
    var corded_window_id = parent_row.find(".corded_window_id").val();

    // job update
    if( corded_window_id > 0 ){

        //jQuery('#load-screen').show();
        jQuery(".techsheet_tab_next:visible").prop("disabled",true); // disable NEXT button

        jQuery.ajax({
            type: "POST",
            url: "/jobs/ajax_techsheet_corded_window_row_update",
            data: { 
                corded_window_id: corded_window_id,
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

    // display add alarm form
    jQuery("#dispay_add_cw_btn").click(function(){

        $.fancybox.open({
            src  : '#add_cw_fb'
        });

    });

    // add more alarm to the form
    jQuery("#add_cw_btn").click(function(){

        let cw = jQuery(".add_cw_tbody:last").clone();
        cw.find(".cw_location").val(''); // clear location/position
        cw.find(".cw_num_of_windows").val(''); // clear Number of windows

        jQuery("#add_cw_tbl").append(cw);

    });

    // Add Safety Switch
	jQuery("#save_cw_btn").click(function(){
		
		var dom = jQuery(this); 
        var cw_arr = [];

        var error_arr = [];
        jQuery(".cw_tr").each(function(){

            dom = jQuery(this);
            
            var cw_location = dom.find(".cw_location").val();
            var cw_num_of_windows = dom.find(".cw_num_of_windows").val();            
            
            if( cw_location == '' ){
                var error_txt = 'Location is required';
                if( error_arr.includes(error_txt) == false ){
                    error_arr.push(error_txt);
                }
                
            } 
           
            json_data = {
                'cw_location': cw_location,
                'cw_num_of_windows': cw_num_of_windows
            }
            var json_str = JSON.stringify(json_data);

            cw_arr.push(json_str);

        });  

        //console.log(error_arr); 

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
                url: "/jobs/ajax_add_corded_window",
                data: {
                    job_id: <?php echo $this->input->get_post('job_id'); ?>,                    
                    cw_arr: cw_arr
                }
            }).done(function (ret) {

                jQuery('#load-screen').hide();
                jQuery(".techsheet_tab_next:visible").prop("disabled",false); // enable NEXT button
                location.reload();

            });
            
            

        }               
		
				
	});



    // delete corded window
    jQuery(".delete_cw_btn").click(function(){

        var dom = jQuery(this);
        var parent_row = dom.parents("tr:first");

        var corded_window_id = parent_row.find(".corded_window_id").val();

        swal({
            title: "Warning!",
            text: "This will delete this corded window, do you want to continue?",
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
                    url: "/jobs/ajax_delete_techsheet_corded_window",
                    data: {
                        job_id: <?php echo $this->input->get_post('job_id'); ?>,                        
                        corded_window_id: corded_window_id
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
    jQuery(".cw_location, .num_of_windows").change(function(){

        var dom = jQuery(this);         
        ts_ajax_corded_window_inline_update(dom);   

    });
    

});
</script>