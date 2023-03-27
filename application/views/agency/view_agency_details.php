
<style>
    .tab-content{
        border: solid 1px #d8e2e7;
        border-top:0px;
        padding: 30px 15px 0px 15px;
    }
    .card{
        text-align:left;
    }
    p.form-control-static{
        margin-bottom: 0px;
    }
    .statements_agency_comments_ts_span{
        color: #00d1e5;
        font-style: italic;
    }
    .card-red-fill{
        border: 1px solid #dc3545;
    }
    .card-red-fill header.card-header{
        background: #dc3545;
        color: #fff;
    }
    .vad_cta_box .btn span{
        display: inline!important;
        margin-right: 10px!important;
        color:#fff!important;
    }
    .vad_cta_box{
        margin-top: 20px;
    }
    .vad_cta_box .btn{
        margin-bottom:10px;
    }
    .portal_user_table td,  .portal_user_table th{
        text-align: left;
    }
    .cta .btn span{
        display: inline-block!important;
        color:#fff!important;
    }
    .pagi_count{
        margin-bottom:10px;
    }
    .action_div{
        width: 70px;
    }
    .action_div a{
        display: inline-block;
    }
    .card-blue-fill .card-header{
        padding:8px 12px;
    }
    .tt_boxes{
        padding-right:30px;
    }
    .tt_boxes label{
        margin-bottom:5px;
        font-weight:700;
    }
    .text-capitalize{
        text-transform:capitalize;
    }
    .fancybox-content{
        min-width:500px;
    }
    .btn_del:hover .fa, .btn_delete:hover .glyphicon{
        color:#dc3545;
    }
    .clear_b{clear:both;}
    .th_div label{
        font-weight:700px;
        margin-bottom:5px;
    }
    .th_div span.fa,.th_div .font-icon{
        font-weight:700;
        color:#00a8ff!important;
    }
    .table-no-border{
        border:none;
    }
    .table-no-border td{
        border:none;
    }
    .table-no-border th{
        border:none;
        background:#fff!important;
    }
   /* .active_s{
        background: #fff!important;
    }
    .active_s a{
        border-bottom:0px!important;
    }*/
</style>

<div class="box-typical box-typical-padding">
<?php 
    // breadcrumbs template
    $bc_items = array(
        array(
            'title' => 'View Agencies',
            'link' => "/agency/view_agencies"
        ),
        array(
            'title' => $title,
            'status' => 'active',
            'link' => "/agency/view_agency_details/{$this->uri->segment(3)}"
        )
    );
    $bc_data['bc_items'] = $bc_items;
    $this->load->view('templates/breadcrumbs', $bc_data);
?>
    <div class="vad_box">
        
        <?php if( $row['deleted']==1 ){ ?>
        <div class="text-center alert alert-danger">Agency Deleted</div>
        <?php } ?>

        <section class="tabs-section">
            <div class="tabs-section-nav tabs-section-nav-icons">
                <div class="tbl">
                    <ul class="nav" role="tablist">
                        <li class="nav-item">
                            <a  class="nav-link <?php echo ($this->uri->segment(4)==1 || $this->uri->segment(4)=="")?'active':'not-active' ?>" href="/agency/view_agency_details/<?php echo $agency_id ?>/1">
                                <span class="nav-link-in">
                                    <i class="font-icon font-icon-user"></i>
                                    Agency Details
                                </span>
                            </a>
                        </li>
                        <!--<li class="nav-item red">
                            <a  class="nav-link <?php echo ($this->uri->segment(4)==2)?'active':'not-active' ?>" href="/agency/view_agency_details/<?php echo $agency_id ?>/2">
                                <span class="nav-link-in">
                                    <span class="font-icon font-icon-user"></span>
                                    Contact Details
                                </span>
                            </a>
                        </li> -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($this->uri->segment(4)==3)?'active':'not-active' ?>" href="/agency/view_agency_details/<?php echo $agency_id ?>/3">
                                <span class="nav-link-in">
                                    <i class="font-icon font-icon-user"></i>
                                    Portal Users
                                </span>
                            </a>
                        </li>	
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($this->uri->segment(4)==4)?'active':'not-active' ?>" href="/agency/view_agency_details/<?php echo $agency_id ?>/4">
                                <span class="nav-link-in">
                                    <i class="fa fa-dollar"></i>
                                    Pricing
                                </span>
                            </a>
                        </li>	
                        <li class="nav-item">
                            <a data-url="<?php echo $type_onceOff ?>" class="nav-link <?php echo ($this->uri->segment(4)==5)?'active':'not-active' ?>" href="/agency/view_agency_details/<?php echo $agency_id ?>/5">
                                <span class="nav-link-in">
                                    <i class="font-icon font-icon-cogwheel"></i>
                                    Preferences
                                </span>
                            </a>
                        </li>	
                        <li class="nav-item">
                            <a data-url="<?php echo $type_onceOff ?>" class="nav-link <?php echo ($this->uri->segment(4)==12)?'active':'not-active' ?>" href="/agency/view_agency_details/<?php echo $agency_id ?>/12">
                                <span class="nav-link-in">
                                    <i class="font-icon font-icon-cogwheel"></i>
                                    Onboarding
                                </span>
                            </a>
                        </li>	
                        <li class="nav-item">
                            <a  class="nav-link <?php echo ($this->uri->segment(4)==6)?'active':'not-active' ?>" href="/agency/view_agency_details/<?php echo $agency_id ?>/6">
                                <span class="nav-link-in">
                                    <i class="font-icon font-icon-list-square"></i>
                                    Logs
                                </span>
                            </a>
                        </li>	
                        <li class="nav-item">
                            <a  class="nav-link <?php echo ($this->uri->segment(4)==7)?'active':'not-active' ?>" href="/agency/view_agency_details/<?php echo $agency_id ?>/7">
                                <span class="nav-link-in">
                                    <i class="font-icon font-icon-list-square"></i>
                                    New Logs
                                </span>
                            </a>
                        </li>	
                        <li class="nav-item">
                            <a  class="nav-link <?php echo ($this->uri->segment(4)==8)?'active':'not-active' ?>" href="/agency/view_agency_details/<?php echo $agency_id ?>/8">
                                <span class="nav-link-in">
                                    <i class="font-icon font-icon-page"></i>
                                    Files
                                </span>
                            </a>
                        </li>	
                        <li class="nav-item">
                            <a  class="nav-link <?php echo ($this->uri->segment(4)==9)?'active':'not-active' ?>" href="/agency/view_agency_details/<?php echo $agency_id ?>/9?prop_type=1">
                                <span class="nav-link-in">
                                    <i class="font-icon font-icon-build"></i>
                                    Properties
                                </span>
                            </a>
                        </li>	
                        <li class="nav-item">
                            <a  class="nav-link <?php echo ($this->uri->segment(4)==10)?'active':'not-active' ?>" href="/agency/view_agency_details/<?php echo $agency_id ?>/10">
                                <span class="nav-link-in">
                                    <i class="font-icon font-icon-user"></i>
                                    Accounts
                                </span>
                            </a>
                        </li>	
                       <!-- <li class="nav-item">
                            <a  class="nav-link <?php echo ($this->uri->segment(4)==11)?'active':'not-active' ?>" href="/agency/view_agency_details/<?php echo $agency_id ?>/11">
                                <span class="nav-link-in">
                                    <i class="fa fa-bar-chart"></i>
                                    API
                                </span>
                            </a>
                        </li>	-->
                    </ul>
                </div>

                <div class="tab-content">
                    <?php 
                        ## Tab content/page switching > load relevant tab
                        ?>

                       
                        
                        <?php
                        $hidden_input_data_agency_id = array(
                                'type'  => 'hidden',
                                'name'  => 'agency_id',
                                'id'    => 'agency_id',
                                'value' => $agency_id,
                                'class' => 'agency_id'
                        );
                        $hidden_input_data_fields_edited = array(
                            'type'  => 'hidden',
                            'name'  => 'fields_edited',
                            'id'    => 'fields_edited',
                            'value' => '',
                            'class' => 'fields_edited'
                        );

                        if(!$tab || $tab==1){
                            $staff_id = $this->session->staff_id;

                            $data['access'] = $this->agency_model->getStaffAccess($staff_id);
                            $data['user_ctype'] = $data['access'][0]->ClassName;
                            $data['priority_fullname'] = $agency_priority;

                            $this->load->view('/agency/tab/vad_agency_details.php', $data);

                        }elseif($tab==2){ //Removed and moved to First tab as per Dan's request
                            echo form_open("/agency/update_agency/{$agency_id}/{$tab}","id=vad_form");
                            echo form_input($hidden_input_data_agency_id);

                            $this->load->view('/agency/tab/vad_contact_details.php');

                            echo form_close();
                        }elseif($tab==3){

                            $this->load->view('/agency/tab/vad_portal_users.php');
                            
                        }elseif($tab==4){
                            //echo form_open("/agency/update_agency/{$agency_id}/{$tab}","id=vad_form");
                            //echo form_input($hidden_input_data_agency_id);
                            //echo form_input($hidden_input_data_fields_edited);

                            $this->load->view('/agency/tab/vad_pricing.php');

                            echo form_close();
                        }elseif($tab==5){
                            echo form_open("/agency/update_agency/{$agency_id}/{$tab}","id=vad_form");
                            echo form_input($hidden_input_data_agency_id);
                            //echo form_input($hidden_input_data_fields_edited);

                            $this->load->view('/agency/tab/vad_preferences.php');

                            echo form_close();
                        }elseif($tab==6){
                            $this->load->view('/agency/tab/vad_log.php');
                        }elseif($tab==7){
                            $this->load->view('/agency/tab/vad_new_log.php');
                        }elseif($tab==8){
                            $this->load->view('/agency/tab/vad_files.php');
                        }elseif($tab==9){
                            $this->load->view('/agency/tab/vad_properties.php');
                        }elseif($tab==10){
                            $this->load->view('/agency/tab/vad_accounts.php');
                        }elseif($tab==11){
                            $this->load->view('/agency/tab/vad_API.php');
                        }elseif($tab==12){
                            $this->load->view('/agency/tab/vad_onboarding.php');
                        }
                    ?>
                </div>
        </section>
    </div>
</div>



<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
    This page displays agency detail.
	</p>

</div>
<!-- Fancybox END -->


<script type="text/javascript">
    jQuery(document).ready(function(){

         //success/error message sweel alert pop  start
        <?php 
        if( $this->session->flashdata('update_agency_success') &&  $this->session->flashdata('update_agency_success') == 1 ){ 
    
        ?>
        var msg = "<?php echo $this->session->flashdata('update_agency_success_msg') ?>";
            swal({
                html: true,
                title: "Success!",
                text: msg,
                type: "success",
                confirmButtonClass: "btn-success"
            });
        <?php 
        }
        ?>

        <?php 
            if( $this->session->flashdata('update_not_free_error') &&  $this->session->flashdata('update_not_free_error') == 1 ){ 
        
            ?>
            var msg = "<?php echo $this->session->flashdata('update_not_free_msg') ?>";
                swal({
                    html: true,
                    title: "Error!",
                    text: msg,
                    type: "error",
                    confirmButtonClass: "btn-error"
                });
            <?php 
            }
        ?>

        // field edited script, to know what field is edited to be included in the logs, WIP try and get all fields
        /*jQuery(".form-control").change(function(){
            var fields_edited = jQuery("#fields_edited").val();
            var field = jQuery(this).attr("title");
            if(fields_edited.search(field)==-1){
                console.log('already exist');
                var comb = fields_edited+","+field;
                jQuery("#fields_edited").val(comb);
            }
        });*/


    })
</script>