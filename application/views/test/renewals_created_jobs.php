
<style>
    .col-mdd-3{
        max-width:15.5%;
    }
    .jtable td, .jtable th {
        border-top: none;
        height: auto;
    }
</style>

<div class="box-typical box-typical-padding">

    <?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => $uri
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);

?>
    <!--
	<header class="box-typical-header">
        <div class="box-typical box-typical-padding">
            <?php
            $form_attr = array(
                'id' => 'jform'
            );
            echo form_open($uri,$form_attr);
            ?>
                <div class="for-groupss row">
                    <div class="col-lg-10 col-md-12 columns">
                        <div class="row">


                            <div class="col-mdd-3">
                                    <label for="date_select">Date:</label>
                                    <input name="date_filter" placeholder="ALL" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo ( $this->input->get_post('date_filter')!= '' )?$this->input->get_post('date_filter'):null; ?>">
                            </div>

                            <div class="col-md-1 columns">
                                <label class="col-sm-12 form-control-label">&nbsp;</label>
                                <input class="btn" type="submit" name="btn_search" value="Search">
                            </div>
                            
                        </div>
                    </div>                                                                   
                </div>
            </form>
        </div>
    </header>
    -->
    

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<table class="table table-hover table-striped main-table">
					<thead>
						<tr>	
                            <th>Job ID</th> 
                            <th>Service</th>
                            <th>Details</th>
                            <th>Ladder</th>         
                            <th>Job Type</th>
                            <th>Job Status</th>	
                            <th>Job Date</th>                                           
                            <th>
                                <div class="checkbox" style="margin:0;">
                                    <input name="chk_all" type="checkbox" id="check-all" class="check-all">
                                    <label for="check-all">&nbsp;</label>
                                </div>
                            </th>
						</tr>
					</thead>

					<tbody>
                        <?php
                        
                        if($job_sql->num_rows() > 0){
                            foreach($job_sql->result() as $job_row){
                            $p_address = "{$job_row->p_address_1} {$job_row->p_address_2}, {$job_row->p_address_3}";
                            $is_first_visit = $this->tech_model->checkfirstVisit($job_row->jid,$job_row->j_service);
                            if( $is_first_visit == true ){
                        ?>
                            <tr>    
                                <td>
                                    <a href="<?php echo "{$this->config->item('crm_link')}/view_job_details.php?id={$job_row->jid}"; ?>">
                                        <?php echo $job_row->jid; ?>
                                    </a>                    
                                </td>
                                <td>
                                    <?php
                                    // display icons
                                    $job_icons_params = array(
                                        'service_type' => $job_row->j_service,
                                        'job_type' => $job_row->job_type,
                                        'sevice_type_name' => $job_row->ajt_type
                                    );
                                    echo $this->system_model->display_job_icons($job_icons_params);
                                    ?>
                                </td>
                                <td>
                                   <?php
                                    // if first visit
                                    if( $is_first_visit == true   ){ ?>
                                        <img src="/images/first_icon.png" class="jicon" style="width: 16px; margin-right: 7px; cursor:pointer;" title="First visit" data-toggle="tooltip" />
                                    <?php
                                    }
                                   ?>
                                </td>
                                <td>
                                <?php
                                    if( $job_row->survey_ladder!='' ){ 
                                    
                                        // 4ft was changed to 3ft. older data already 4ft so just change labels
                                        $survey_ladder = '';
                                        if($job_row->survey_ladder=='4FT'){
                                            $survey_ladder = '3FT';
                                        }else{
                                            $survey_ladder = $job_row->survey_ladder;
                                        }
                                    
                                    ?>
                                    
                                        <div class="left"><img src="/images/ladder.png" class="ladder_icon" /></div>
                                        <div class="left" style="margin-top: 6px;">(<?php echo $survey_ladder; ?>)</div>
                                    
                                    <?php
                                    }
                                    ?>	
                                </td>  
                                <td>
                                    <?php echo $job_row->job_type; ?>
                                </td>
                                <td>
                                    <?php echo $job_row->j_status; ?>
                                </td>
                                <td>
                                    <?php echo ($this->system_model->isDateNotEmpty($job_row->j_date) == true) ? $this->system_model->formatDate($job_row->j_date, 'd/m/Y') : ''; ?>
                                </td>                                 
                                <td>
                                    <?php
                                    if( $is_first_visit == true ){ ?>

                                        <span class="checkbox">
                                            <input class="chk_job" name="chk_job[]" type="checkbox" id="check-<?php echo $job_row->jid; ?>" value="<?php echo $job_row->jid; ?>">
                                            <label for="check-<?php echo $job_row->jid; ?>">&nbsp;</label>
                                        </span>

                                    <?php
                                    }
                                    ?>                                   
                                </td>                                                                                                
                            </tr>
                        <?php 
                                }  
                            }
                        }else{ ?>
                            <tr><td colspan='2'>No Data</td></tr>
                        <?php    
                        }     
                                      
                        ?>                 
					</tbody>

				</table>
            </div>
            
            <div id="fuction_buttons_div" class="text-right">
					
                <button type="button" id="syn_alarms_btn" class="btn blue-btn submitbtnImg">			
                    Sync techsheet
                </button>	
                               

            </div>

        <!--
		<nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
        <div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
        -->

		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
    This page shows all On Hold jobs, both regular and for COVID-19 reasons                    
	</p>

</div>
<!-- Fancybox END -->


<script>
jQuery(document).ready(function(){


    // inline 240v rebook 
		jQuery("#syn_alarms_btn").click(function(){
			
			var job_id_arr = new Array();
			jQuery(".chk_job:checked").each(function(){
				job_id_arr.push(jQuery(this).val());
			});

            if( job_id_arr.length > 0 ){

                    swal(
                    {
                        title: "",
                        text: "Are you sure you want to Sync Techsheet?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonClass: "btn-success",
                        confirmButtonText: "Yes",
                        cancelButtonText: "No, Cancel!",
                        closeOnConfirm: false,
                        closeOnCancel: true,
                    },
                    function(isConfirm){
                        if(isConfirm){

                            //swal.close();
                            $('#load-screen').show(); //show loader
                            
                            jQuery.ajax({
                                type: "POST",
                                url: "/test/ajax_sync_alarms",
                                data: { 
                                    job_id_arr: job_id_arr
                                }
                            }).done(function( ret ){

                                
                                $('#load-screen').hide(); //hide loader
                                swal({
                                    title:"Success!",
                                    text: "Alarms Sync Success",
                                    type: "success",
                                    showCancelButton: false,
                                    showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                                    timer: <?php echo $this->config->item('timer') ?>

                                });
                                setTimeout(function(){ window.location='<?php echo $uri; ?>'; }, <?php echo $this->config->item('timer') ?>);	
                                
                                
                                    
                            });	

                        }else{
                            return false;
                        }
                        
                    }
                    
                );

            }else{

                swal({
                    title:"Warning!",
                    text: "Please select items to process",
                    type: "warning",
                    showCancelButton: false,
                    showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                    timer: <?php echo $this->config->item('timer') ?>

                });                

            }
			
						
			
		});


        // check all toggle
		jQuery("#check-all").click(function(){

            if(jQuery(this).prop("checked")==true){
                jQuery(".chk_job:visible").prop("checked",true);
            }else{
                jQuery(".chk_job:visible").prop("checked",false);
            }

        });
       
    
});
</script>