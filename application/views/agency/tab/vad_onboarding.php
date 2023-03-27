<style>
    .log_list_box table td, .log_list_box table th{
        padding:11px 30px 10px 7px;
    }
    .preferences_list_box .radio{
        margin-bottom: 0px;
    }
    div.checkbox{margin: 0px;}
    .vad_add_event_div{margin-top:30px;margin-bottom:30px;}
    #ss_status{width:165px;display:none;}
    .add_to_snapshot_label{float:left;}
    .ob_check_icon{
        font-size:20px;
    }
</style>

<div class="log_list_box">

    <table class="table_no_border text-left">

        <tr>
            <th>&nbsp;</th>
            <th>Info</th>
            <th style="padding-left:10px;padding-right:10px;" class="text-center">Done</th>
            <th>Timestamp</th>
        </tr>
        <?php 
            $index = 0;
            foreach($agency_onboarding as $on_brdng_row){
        ?>
            <tr>
                <td style="padding-right:0px;">
                    <div class="checkbox">
                        <input data-og_onboarding_id="<?php echo $on_brdng_row['onboarding_selected_id']; ?>" type="checkbox" id="<?php echo $index.'_check' ?>"  class="onboarding_id"value="<?php echo $on_brdng_row['onboarding_id']; ?>" <?php echo ( count($on_brdng_row['onboarding_selected_id']) > 0 )?'checked="checked"':null; ?>  />	
                        <label for="<?php echo $index.'_check' ?>">&nbsp;</label>
                    </div>
                </td>
                <td>
                    <?php echo $on_brdng_row['name']; ?>
                </td>
                <td class="text-center" style="padding:0px;">
                    <div class="ob_check_icon_box">
                   
                        <span style=" <?php echo ($on_brdng_row['onboarding_selected_id']!="") ? null : 'display:none;' ?>" class="ob_check_icon fa fa-check-circle text-green"></span>
                  
                    </div>
                </td>
                <td>
                    <span class="ob_updated_by">
                        <?php echo ($on_brdng_row['onboarding_selected_id']!="") ? $on_brdng_row['onboarding_updated_by']: NULL; ?>
                    </span>
                    <span class="ob_updated_date">
                        <?php echo ($on_brdng_row['onboarding_selected_id']!="") ? $on_brdng_row['onboarding_updated_date']: NULL; ?>
                    </span>
                </td>
            </tr>
        <?php
            $index++;
            }
        ?>
       
    </table>

    

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

        $('.btn_add_log_event').click(function(){
            swal('','Add to old agency event log \n Not needed because CI has new log table?','warning');
            return false;
        })

        
    })

</script>