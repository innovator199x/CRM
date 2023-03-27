 <style>
.service_type_icon_tab_link{
    cursor: pointer;
}
.unable_to_complete_ts_tab{
    display: none;
}
</style>
 <!-- service type TABS -->
 <section class="tabs-section">
		
    <div class="tabs-section-nav tabs-section-nav-icons">
        <div id="service_type_tab" class="tbl <?php echo ( count($service_types_arr) == 1 )?'d-none':null; ?>">
            <ul class="nav j_remember_tab2" role="tablist">
                <?php
                // only show tabs on bundle
                //if( $is_bundle_serv == true ){

                    // service type 2 Smoke Alarms, 12 Smoke Alarms IC
                    $service_type = 2; // used on the service icons on the bottom, only needs 1 so we use 2 - SA instead
                    if( in_array(2, $service_types_arr) || in_array(12, $service_types_arr) ){ ?>
                        <li class="nav-item">
                            <a class="nav-link" href="#tab_sa" role="tab" data-toggle="tab" data-tab_service_type_id="<?php echo $service_type; ?>">
                                <span class="nav-link-in">
                                    <img class="service_type_icons" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons(2); ?>" />
                                    Smoke Alarms
                                </span>
                            </a>
                        </li>
                    <?php
                    }

                    $service_type = 5; // Safety Switch
                    if( in_array($service_type, $service_types_arr) ){ ?>
                        <li class="nav-item">
                            <a class="nav-link" href="#tab_ss" role="tab" data-toggle="tab" data-tab_service_type_id="<?php echo $service_type; ?>">
                                <span class="nav-link-in">
                                    <img class="service_type_icons" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons(5); ?>" />
                                    Safety Switch
                                </span>
                            </a>
                        </li>
                    <?php
                    }

                    $service_type = 6; // Corded Window
                    if( in_array($service_type, $service_types_arr) ){ ?>
                        <li class="nav-item">
                            <a class="nav-link" href="#tab_cw" role="tab" data-toggle="tab" data-tab_service_type_id="<?php echo $service_type; ?>">
                                <span class="nav-link-in">                
                                    <img class="service_type_icons" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons(6); ?>" />
                                    Corded Window
                                </span>
                            </a>
                        </li>
                    <?php
                    }
    
                    $service_type = 15; // Water Effeciency
                    if( in_array($service_type, $service_types_arr) ){ ?>
                        <li class="nav-item">
                            <a class="nav-link" href="#tab_we" role="tab" data-toggle="tab" data-tab_service_type_id="<?php echo $service_type; ?>">
                                <span class="nav-link-in">
                                    <img class="service_type_icons" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons(15); ?>" />
                                    Water Effeciency
                                </span>
                            </a>
                        </li>
                    <?php
                    }

                //}
                ?>                                                                                           
            </ul>
        </div>
    </div>
    
    
    <div class="tab-content">

        <?php
        // service type 2 Smoke Alarms, 12 Smoke Alarms IC
        $service_type = 2; // used on the service icons on the bottom, only needs 1 so we use 2 - SA instead
        if( in_array(2, $service_types_arr) || in_array(12, $service_types_arr) ){ ?>
            <!-- Smoke Alarms TAB CONTENT -->
            <div role="tabpanel" class="tab-pane fade" id="tab_sa">                
                <?php require_once(APPPATH."views/jobs/tech_sheet_sa.php"); ?>
            </div>
        <?php
        }

        $service_type = 5; // Safety Switch
        if( in_array($service_type, $service_types_arr) ){ ?>
            <!-- Safety Switch TAB CONTENT -->
            <div role="tabpanel" class="tab-pane fade" id="tab_ss">                                            
                <?php require_once(APPPATH."views/jobs/tech_sheet_ss.php"); ?>                          
            </div>
        <?php
        }

        $service_type = 6; // Corded Window
        if( in_array($service_type, $service_types_arr) ){ ?>
            <!-- Corded Window TAB CONTENT -->
            <div role="tabpanel" class="tab-pane fade" id="tab_cw">                                             
                <?php require_once(APPPATH."views/jobs/tech_sheet_cw.php"); ?>		
            </div>
        <?php
        }

        $service_type = 15; // Water Effeciency
        if( in_array($service_type, $service_types_arr) ){ ?>
            <!-- Water Effeciency TAB CONTENT -->
            <div role="tabpanel" class="tab-pane fade" id="tab_we">                                            
                <?php require_once(APPPATH."views/jobs/tech_sheet_we.php"); ?>					
            </div>
        <?php
        }
        ?>        

    </div>

</section>





<div class="row">

    <div class="col-md-4 text-left">
        <button type="button" class="btn btn-success techsheet_tab_prev">Previous</button>	 
        <button type="button" id="unable_to_complete_btn" class="btn btn-danger unable_to_complete_btn unable_to_complete_ts_tab">Unable to complete Job</button>                   	
    </div>

    <div class="col-md-4 text-center pt-2">

        <div class="col-md">
        <?php
        foreach( $service_types_arr as $service_type ){ ?>

            <img class="service_type_icons service_type_icon_tab_link mx-2" data-tab_service_type_id="<?php echo $service_type; ?>" src="/images/serv_img/<?php echo $this->system_model->getServiceIcons($service_type); ?>" />	  

        <?php    
        }
        ?>
        </div>

    </div>

    <div class="col-md-4 text-right">
        <button type="button" class="btn techsheet_tab_next">Next</button>                  	
    </div>

</div> 

<script>
jQuery(document).ready(function(){

    // next tab
    jQuery('.service_type_icon_tab_link').click(function(){
       
        var node = jQuery(this);

        var service_type_id = node.attr("data-tab_service_type_id");
        //console.log('service_type_id: '+service_type_id);

        if( service_type_id > 0 ){

            var link_node =  jQuery("#service_type_tab .nav-link[data-tab_service_type_id='"+service_type_id+"']");        
            remember_service_tab(link_node);
            link_node.tab('show');

        }        

    });

})
</script>