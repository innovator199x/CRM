<style>
    .log_list_box table td, .log_list_box table th{
        padding:11px 30px 10px 7px;
    }
    .preferences_list_box .radio{
        margin-bottom: 0px;
    }
    div.checkbox{margin: 0px;}
   
    .ob_check_icon{
        font-size:20px;
    }
</style>

<div class="log_list_box">

    <div class="log_listing_old text-left">
                            
        <table class="table table-hover main-table table_log_listing_old table-sm">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Staff Member</th>
                    <th>Comments</th>
                    <th>Next Contact</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if(count($agency_logs)!=0){
                    foreach( $agency_logs as $a_log){ 
                ?>
                        <tr>
                            <td><?php echo date('d/m/Y',strtotime($a_log['eventdate'])); ?></td>
                            <td><?php echo $a_log['contact_type']; ?></td>					
                            <td><?php echo "{$a_log['FirstName']} {$a_log['LastName']}"; ?></td>					
                            <td><?php echo $a_log['comments']; ?></td>					
                            <td><?php echo ($this->system_model->isDateNotEmpty($a_log['next_contact']))?date("d/m/Y",strtotime($a_log['next_contact'])):''; ?></td>
                        </tr>
                <?php 
                    }
                }else{
                    echo "<tr><td colspan='5'>No Data</td></tr>";
                } 
                ?>
            </tbody>
        </table>
            <nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
            <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
            <p>&nbsp;</p>
    </div>

</div>

<script type="text/javascript">

        
    jQuery(document).ready(function(){

        // agency info marker update
	jQuery(".onboarding_id").click(function(){

        var obj = jQuery(this);
        var onboarding_id = obj.val();
        var agency_id = <?php echo $agency_id  ?>;
        var is_ticked = ( obj.prop("checked") == true )?1:0;
        var og_onboarding_id = obj.attr('data-og_onboarding_id');

        jQuery("#load-screen").show();
        jQuery.ajax({
                type: "POST",
                url: "/agency/ajax_update_agency_onboarding_selection",
                data: { 
                    agency_id: agency_id,
                    onboarding_id: onboarding_id,
                    is_ticked: is_ticked,
                    og_onboarding_id: og_onboarding_id
                },
                dataType: 'json'
            }).done(function( ret ){
                
                if( is_ticked == 1 ){
                    obj.parents("tr:first").find('.ob_check_icon').show();
                    obj.parents("tr:first").find('.ob_updated_by').html(ret.updated_by);
                    obj.parents("tr:first").find('.ob_updated_date').html(ret.updated_date);
                }else{
                    obj.parents("tr:first").find('.ob_check_icon').hide();
                    obj.parents("tr:first").find('.ob_updated_by').html('');
                    obj.parents("tr:first").find('.ob_updated_date').html('');
                }				
                jQuery("#load-screen").hide();

            });	

        });
        
    })

</script>