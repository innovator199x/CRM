
<div class="box-typical box-typical-padding">

<?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => "/daily/view_unserviced_for_cron"
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);
?>

<header class="box-typical-header">

<div class="box-typical box-typical-padding">

    <div class="for-groups row">
        <div class="col-md-9 columns">
            <button id="btnMarkUnserviced" type="button" class="btn">Mark Properties as Unserviced</span>
            </button>
        </div>
        <div class="col-md-3 columns">
            <?php
                $ue_sql = $this->db->select('cron_mark_unservice')->from('crm_settings')->where(array('country_id'=>$this->config->item('country')))->get();
                $ue = $ue_sql->row_array();
                $ae_val = $ue['cron_mark_unservice'];
                if( $ae_val==1 ){
                    $ae_txt = 'Active';
                    $ae_color = 'green';
                    $is_checked = 'checked="checked"';
                }else{
                    $ae_txt = 'Inactive';
                    $ae_color = 'red';
                    $is_checked = '';
                }              
            ?>
            <div class="checkbox" style="margin-top: 10px;">
            <input type="checkbox" id="chk_cron_unserviced_toggle" <?php echo $is_checked; ?> /> 
                <label for="chk_cron_unserviced_toggle"> <span style="color:<?php echo $ae_color; ?>">Auto Mark <?php echo $ae_txt; ?></span></label>
            </div> 
        </div>
    </div>
</div>

</header>

<section>
    <div class="body-typical-body">
        <div class="table-responsive" id="markDiv" style="display: none;">
            <p>Result: marked <span id="countRes">0</span> properties as unserviced.</p>
            <p>Click <a href="<?php echo $this->config->item('crmci_link') ?>/daily/unserviced">here</a> to view the unserviced properties.</p>
        </div>
    </div>
</section>

</div>


<style>
.main-table {
    border-left: 1px solid #dee2e6;
    border-right: 1px solid #dee2e6;
    border-bottom: 1px solid #dee2e6;
    margin-bottom: 20px;
}

.col-mdd-3 {
    -webkit-box-flex: 0;
    -ms-flex: 0 0 15.2%;
    flex: 0 0 15.2%;
    max-width: 15.2%;

    position: relative;
    width: 100%;
    min-height: 1px;
    padding-right: 15px;
    padding-left: 15px;
}
</style>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

<h4><?php echo $title; ?></h4>
<p>This page mark properties as unserviced.</p>

</div>
<!-- Fancybox END -->

<script type="text/javascript">

jQuery(document).ready(function(){

        $('#btnMarkUnserviced').click(function(e){
            e.preventDefault();

            swal({
                html:true,
                title: "",
                text: "Are you sure you want to proceed?",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes",
                cancelButtonClass: "btn-danger",
                cancelButtonText: "No, Cancel!",
                closeOnConfirm: false,
                closeOnCancel: true,
            },
            function(isConfirm){
                if(isConfirm){

                    $('#load-screen').show(); 
                    swal.close();

                    jQuery.ajax({
                        method: 'post',
                        processData: false,
                        contentType: false,
                        cache: false,
                        dataType: 'json',
                        data: { 
                                staff_id: <?php echo $this->session->staff_id ?>,
                            },
                        url: "/daily/unserviced_for_cron",
                    }).done(function( crm_ret ){
                        if(crm_ret.status){
                            $('#load-screen').hide(); //hide loader
                            swal({
                                title:"Success!",
                                text: "Properties have been marked as unserviced.",
                                type: "success",
                                showCancelButton: false,
                                showConfirmButton: false,
                                confirmButtonText: "OK",
                                closeOnConfirm: false,
                                closeOnConfirm: false,
                                allowOutsideClick: false,
                                timer: 3000
                            },function(isConfirm){
                                swal.close();
                                $("#markDiv").show();
                                $("#countRes").html(crm_ret.countRes);
                            });
                        }else{
                            swal('','All appropriate properties have already been marked as unserviced.','info');
                            $('#load-screen').hide(); //hide loader
                        }

                    }); 
                }
            });
        })

        jQuery("#chk_cron_unserviced_toggle").change(function(){
            
            var cron_status  = ( jQuery(this).prop("checked")==true )?1:0;
            var cron_file = 'merged_email_all_cron';
            var db_field = 'cron_mark_unservice';

            swal(
                    {
                        title: "",
                        text: "Are You Sure You Want to Continue?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-success",
                        confirmButtonText: "Yes",
                        cancelButtonClass: "btn-danger",
                        cancelButtonText: "No, Cancel!",
                        closeOnConfirm: false,
                        closeOnCancel: true,
                    },
                    function(isConfirm){
                        if(isConfirm){

                            $('#load-screen').show(); //show loader
                            swal.close();

                            jQuery.ajax({
                            type: "POST",
                            url: "<?php echo base_url('/jobs/ajax_toggle_cron_on_off') ?>",
                            dataType: 'json',
                            data: { 
                                cron_status: cron_status,
                                cron_file: cron_file,           
                                db_field: db_field
                            }
                            }).done(function(data){
                                
                                if(data.status){
                                    $('#load-screen').hide(); //hide loader
                                    swal({
                                        title:"Success!",
                                        text: "Auto Emails successfully updated",
                                        type: "success",
                                        showCancelButton: false,
                                        confirmButtonText: "OK",
                                        closeOnConfirm: false,
                                    },function(isConfirm){
                                        if(isConfirm){ 
                                            swal.close();
                                            location.reload();
                                        }
                                    });
                                }else{
                                    swal.close();
                                    location.reload();
                                }

                            });

                        }else{
                            if(jQuery("#chk_cron_unserviced_toggle").is(":checked")){
                                $('#chk_cron_unserviced_toggle').prop('checked', false); 
                               
                            }else{
                                $('#chk_cron_unserviced_toggle').prop('checked', true); 
                            }
                        }
                        
                    }
                );  
            
        });

})

</script>