<style>
.statistic-box a{
    color:#fff;
    display:block;
}
.statistic-box{
    margin-bottom:15px;
}
.statistic-box .box-icon{
    font-size:56px; 
}
.statistic-box div a{
    padding: 17px 0px;
}
.box-icon {
    margin-top: 18px;
}
.fa-exclamation-triangle{
    color: red;
}
</style>
<div class="box-typical box-typical-padding">


	<section>
		<div class="body-typical-body">
		
        <div class="row">

            <div class="col-sm-4 columns">

                <div class="statistic-box green">
                    <div>     
                        <a href="<?php echo ( $tr_id > 0 ) ? "/tech_run/run_sheet/{$tr_id}" : 'javascript:no_tech_run();'; ?>">
                        <div class='box-icon'><span class="fa fa-file-text"></span></div>
                            <div class='caption'>Run Sheet</div>
                        </a>
                    </div>
                </div>

            </div>

            <div class="col-sm-4 columns">

                <div class="statistic-box red">
                    <div>
                        <a href="/calendar/monthly_schedule/<?php echo $this->session->staff_id; ?>">
                        <div class='box-icon'><span class="fa fa-calendar"></span></div>
                            <div class='caption'>Monthly Schedule</div>	
                        </a>
                    </div>
                </div>

            </div>


            <div class="col-sm-4 columns">

                <div class="statistic-box yellow">
                    <div>
                        <a href="/messages">
                        <div class='box-icon'><span class="fa fa-wechat"></span></div>
                            <div class='caption'>
                                Messages 
                                <?php
                                if( $msg_count > 0 ){ ?>
                                    <span class="fa fa-exclamation-triangle"></span>                                    
                                <?php
                                }
                                ?>                                
                            </div>	
                        </a>
                    </div>
                </div>

            </div>

            

            

            

        </div>

        <div class="row">

            <div class="col-sm-4 columns">

                <div class="statistic-box purple">
                    <div>
                        <a href="/stock/update_tech_stock/<?php echo $this->session->staff_id; ?>">
                        <div class='box-icon'><span class="fa fa-edit"></span></div>
                            <div class='caption'>Stocktake</div>	
                        </a>
                    </div>
                </div>

            </div> 


            <div class="col-sm-4 columns">

                <div class="statistic-box red">
                    <div>
                        <a href="/calendar/my_calendar">
                            <div class='box-icon'><span class="fa fa-calendar"></span></div>
                            <div class='caption'>My Calendar</div>	
                        </a>
                    </div>
                </div>

            </div>
            
            
            <div class="col-sm-4 columns">

                <div class="statistic-box yellow">
                    <div>
                        <a href="/resources">
                        <div class='box-icon'><span class="fa fa-file-text"></span></div>
                            <div class='caption'>Resources</div>	
                        </a>
                    </div>
                </div>

            </div>

        </div>

        <div class="row">

            <div class="col-sm-4 columns">

                <div id="kms_update" class="statistic-box purple">
                    <div>
                        <a href="javascript:void(0);">
                        <div class='box-icon'><span class="fa fa-car"></span></div>
                            <div class='caption'>KMS</div>	
                        </a>
                    </div>
                </div>

            </div>   


            <div class="col-sm-4 columns">

                <div class="statistic-box red">
                    <div>
                        <a href="/resources/section/?header_id=<?php echo $this->resources_model->get_resources_header_id('Multi-Lingual'); ?>">
                            <div class='box-icon'><span class="fa fa-globe"></span></div>
                            <div class='caption'>Multi-Lingual</div>	
                        </a>
                    </div>
                </div>

            </div>  
            

            <div class="col-sm-4 columns">

                <div class="statistic-box yellow">
                    <div>
                        <a href="/resources/section/?header_id=<?php echo $this->resources_model->get_resources_header_id('SWMS'); ?>">
                            <div class='box-icon'><span class="fa fa-check-square"></span></div>
                            <div class='caption'>SWMS</div>	
                        </a>
                    </div>
                </div>

            </div>         

        </div>


        <div class="row">

            <div class="col-sm-4 columns">

                <div class="statistic-box purple">
                    <div>
                        <a href="/resources/section/?header_id=<?php echo $this->resources_model->get_resources_header_id('Contact List'); ?>">
                        <div class='box-icon'><span class="fa fa-file-text"></span></div>
                            <div class='caption'>Contact List</div>	
                        </a>
                    </div>
                </div>

            </div>

            <div class="col-sm-4 columns">

            </div>     

            <div class="col-sm-4 columns">

                <div id="kms_update" class="statistic-box yellow">
                    <div>
                        <a href="/resources/section/?header_id=<?php echo $this->resources_model->get_resources_header_id('Forms'); ?>">
                        <div class='box-icon'><span class="fa fa-file-text"></span></div>
                            <div class='caption'>Forms</div>	
                        </a>
                    </div>
                </div>

            </div>            

        </div>


		</div>
	</section>

</div>


<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
    <p>This page shows an overall snapshot of some key statistics.</p>

</div>
<!-- Fancybox END -->

<script>
jQuery(document).ready(function(){
	
     //success/error message sweel alert pop  start
     <?php if( $this->session->flashdata('status') &&  $this->session->flashdata('status') == 'success' ){?>
        swal({
            title: "Success!",
            text: "<?php echo $this->session->flashdata('success_msg') ?>",
            type: "success",
            confirmButtonClass: "btn-success",
            showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
            timer: <?php echo $this->config->item('timer') ?>
        });
    <?php }else if(  $this->session->flashdata('status') &&  $this->session->flashdata('status') == 'error'  ){ ?>
        swal({
            title: "Error!",
            text: "<?php echo $this->session->flashdata('error_msg') ?>",
            type: "error",
            confirmButtonClass: "btn-danger"
        });
    <?php } ?>
    //success/error message sweel alert pop  end


	
	
});
</script>

