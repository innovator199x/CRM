<style>
.header_icon{
	width: 1%;
}
.header_name{
	width: 90%;
}
.resources_tbl .fa{
    font-size: 50px;
}
.resources_tbl .btn{
    width: 165px;
}
.no_data_lbl{
    overflow: hidden;
    text-align: center;
}
</style>
<div class="box-typical box-typical-padding">

    <?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "{$uri}/?header_id={$header_id}"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	<section>
		<div class="body-typical-body chops">

            <div class="row">
                <div class="col-lg-12">	

                    <?php
                    ///foreach( $header_sql->result() as $header ){ ?>

                        <div class="main_resource_div">

                            <!--
                            <header class="box-typical-header strt-ui-header">
                                <div class="tbl-row">
                                    <div class="tbl-cell tbl-cell-title">
                                        <h3>
                                            <span class="glyphicon glyphicon-map-marker"></span>
                                            <?php echo $header->name; ?>
                                        </h3>
                                    </div>
                                </div>
                            </header>
                            -->
                            

                                <section class="box-typical-123">		
                                    <div class="box-typical-body">
                                        <div class="table-responsive">

                                            <?php 
                                            if(!empty($all_admins_staffs) && !empty($all_techs_staffs)){ ?>
                                                <div class="tabs-section-nav tabs-section-nav-icons">
                                                    <div class="tbl">
                                                        <ul class="nav" role="tablist">
                                                            <li class="nav-item">
                                                                <a class="nav-link active show" href="#tab-1" role="tab" data-toggle="tab" >
                                                                    <span class="nav-link-in">
                                                                        <i class="fa fa-user-secret"></i>
                                                                        Techs
                                                                    </span>
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a class="nav-link" href="#tab-2" role="tab" data-toggle="tab" >
                                                                    <span class="nav-link-in">
                                                                        <span class="fa fa-user"></span>
                                                                        Admin
                                                                    </span>
                                                                </a>
                                                            </li>


                                                        </ul>
                                                    </div>
                                                </div><!--.tabs-section-nav-->

                                                <div class="tab-content">
                                                <!-- TECH TAB -->
                                                <div role="tabpanel" class="tab-pane fade active show" id="tab-1">
                                                    <table class="table table-hover main-table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Name</th>
                                                                <th>Email</th>
                                                                <th>Phone Number</th>
                                                                <th>Staff Position</th>
                                                                <th>Profile Pic</th>  
                                                                <th>State</th> 
                                                            </tr>
                                                        </thead>

                                                        <tbody>
                                                            <?php foreach($all_techs_staffs as $row): ?>
                                                                <tr>
                                                                    <td>
                                                                        <?php 
                                                                            echo $row->FirstName." ". $row->LastName;
                                                                        ?>
                                                                    </td>  
                                                                    <td>
                                                                        <?php 
                                                                            echo $row->Email;
                                                                        ?>
                                                                    </td>  
                                                                    <td>
                                                                        <?php 
                                                                            echo $row->ContactNumber;
                                                                        ?>
                                                                    </td>  
                                                                    <td>
                                                                        <?php 
                                                                            echo $row->sa_position;
                                                                        ?>
                                                                    </td>  
                                                                    <td>
                                                                        <?php 
                                                                            if(!empty($row->profile_pic)){
                                                                        ?>
                                                                            <img style="width: 50px" src="<?php echo base_url() ?>images/staff_profile/<?php echo $row->profile_pic; ?>" />
                                                                            <?php //echo $row->profile_pic; ?>
                                                                        <?php    }
                                                                        else{
                                                                            echo "No Photo Available";
                                                                        }
                                                                        ?>
                                                                    </td> 
                                                                    <td>
                                                                        <?php 
                                                                            echo $row->state_full_name;
                                                                        ?>
                                                                    </td>                                                     
                                                                </tr>
                                                            <?php endforeach; ?>
                                                            <?php
                                                                if(!empty($all_techs_staffs_null)){
                                                                    foreach($all_techs_staffs_null as $tnull): ?>
                                                                        <tr>
                                                                            <td>
                                                                                <?php 
                                                                                    echo $tnull->FirstName." ". $tnull->LastName;
                                                                                ?>
                                                                            </td>  
                                                                            <td>
                                                                                <?php 
                                                                                    echo $tnull->Email;
                                                                                ?>
                                                                            </td>  
                                                                            <td>
                                                                                <?php 
                                                                                    echo $tnull->ContactNumber;
                                                                                ?>
                                                                            </td>  
                                                                            <td>
                                                                                <?php 
                                                                                    echo $tnull->sa_position;
                                                                                ?>
                                                                            </td>  
                                                                            <td>
                                                                                <?php 
                                                                                    if(!empty($tnull->profile_pic)){
                                                                                ?>
                                                                                    <img style="width: 50px" src="<?php echo base_url() ?>images/staff_profile/<?php echo $tnull->profile_pic; ?>" />
                                                                                    <?php //echo $row->profile_pic; ?>
                                                                                <?php    }
                                                                                else{
                                                                                    echo "No Photo Available";
                                                                                }
                                                                                ?>
                                                                            </td> 
                                                                            <td>
                                                                                <?php 
                                                                                    echo $tnull->state_full_name;
                                                                                ?>
                                                                            </td>                                                     
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                            <?php
                                                                }
                                                            ?>						
                                                        </tbody>
                                                    </table>

                                                </div><!--.tab-pane-->
                                                <!-- TECH TAB END -->

                                                <!-- ADMIN TAB -->
                                                <div role="tabpanel" class="tab-pane fade" id="tab-2">
                                                    <table class="table table-hover main-table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Name</th>
                                                                <th>Email</th>
                                                                <th>Phone Number</th>
                                                                <th>Staff Position</th>
                                                                <th>Profile Pic</th>  
                                                                <th>State</th> 
                                                            </tr>
                                                        </thead>

                                                        <tbody>
                                                            <?php foreach($all_admins_staffs as $key): ?>
                                                                <tr>
                                                                    <td>
                                                                        <?php 
                                                                            echo $key->FirstName." ". $key->LastName;
                                                                        ?>
                                                                    </td>  
                                                                    <td>
                                                                        <?php 
                                                                            echo $key->Email;
                                                                        ?>
                                                                    </td>  
                                                                    <td>
                                                                        <?php 
                                                                            echo $key->ContactNumber;
                                                                        ?>
                                                                    </td>  
                                                                    <td>
                                                                        <?php 
                                                                            echo $key->sa_position;
                                                                        ?>
                                                                    </td>  
                                                                    <td>
                                                                        <?php 
                                                                            if(!empty($key->profile_pic)){
                                                                        ?>
                                                                            <img style="width: 50px" src="<?php echo base_url() ?>images/staff_profile/<?php echo $key->profile_pic; ?>" />
                                                                            <?php //echo $row->profile_pic; ?>
                                                                        <?php    }
                                                                        else{
                                                                            echo "No Photo Available";
                                                                        }
                                                                        ?>
                                                                    </td> 
                                                                    <td>
                                                                        <?php 
                                                                            echo $key->state_full_name;
                                                                        ?>
                                                                    </td>                                                     
                                                                </tr>
                                                            <?php endforeach; ?>	
                                                            <?php
                                                                if(!empty($all_admins_staffs_null)){
                                                                    foreach($all_admins_staffs_null as $anull): ?>
                                                                        <tr>
                                                                            <td>
                                                                                <?php 
                                                                                    echo $anull->FirstName." ". $anull->LastName;
                                                                                ?>
                                                                            </td>  
                                                                            <td>
                                                                                <?php 
                                                                                    echo $anull->Email;
                                                                                ?>
                                                                            </td>  
                                                                            <td>
                                                                                <?php 
                                                                                    echo $anull->ContactNumber;
                                                                                ?>
                                                                            </td>  
                                                                            <td>
                                                                                <?php 
                                                                                    echo $anull->sa_position;
                                                                                ?>
                                                                            </td>  
                                                                            <td>
                                                                                <?php 
                                                                                    if(!empty($anull->profile_pic)){
                                                                                ?>
                                                                                    <img style="width: 50px" src="<?php echo base_url() ?>images/staff_profile/<?php echo $anull->profile_pic; ?>" />
                                                                                    <?php //echo $row->profile_pic; ?>
                                                                                <?php    }
                                                                                else{
                                                                                    echo "No Photo Available";
                                                                                }
                                                                                ?>
                                                                            </td> 
                                                                            <td>
                                                                                <?php 
                                                                                    echo $anull->state_full_name;
                                                                                ?>
                                                                            </td>                                                     
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                            <?php
                                                                }
                                                            ?>			
                                                        </tbody>

                                                    </table>

                                                </div><!--.tab-pane-->
                                                <!-- ADMIN TAB EDN -->
                                                <!--
                                                <table class="table table-hover main-table resources_tbl">
                                                    <thead>
                                                        <tr>
                                                            <th>Name</th>
                                                            <th>Email</th>
                                                            <th>Phone Number</th>
                                                            <th>Staff Position</th>
                                                            <th>Profile Pic</th>  
                                                            <th>State</th>                                                       
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach($all_staffs as $row): ?>
                                                            <tr>
                                                                <td>
                                                                    <?php 
                                                                        echo $row->FirstName." ". $row->LastName;
                                                                    ?>
                                                                </td>  
                                                                <td>
                                                                    <?php 
                                                                        echo $row->Email;
                                                                    ?>
                                                                </td>  
                                                                <td>
                                                                    <?php 
                                                                        echo $row->ContactNumber;
                                                                    ?>
                                                                </td>  
                                                                <td>
                                                                    <?php 
                                                                        echo $row->sa_position;
                                                                    ?>
                                                                </td>  
                                                                <td>
                                                                    <?php 
                                                                        if(!empty($row->profile_pic)){
                                                                    ?>
                                                                        <img style="width: 50px" src="<?php echo base_url() ?>images/staff_profile/<?php echo $row->profile_pic; ?>" />
                                                                        <?php //echo $row->profile_pic; ?>
                                                                    <?php    }
                                                                    else{
                                                                        echo "No Photo Available";
                                                                    }
                                                                    ?>
                                                                </td> 
                                                                <td>
                                                                    <?php 
                                                                        echo $row->state_full_name;
                                                                    ?>
                                                                </td>                                                     
                                                            </tr>
                                                        <?php endforeach; ?>					
                                                    </tbody>
                                                </table> -->
                                            <?php }
                                            
                                            else if( $tech_doc_sql->num_rows() > 0 ){ ?>

                                                <table class="table table-hover main-table resources_tbl">
                                                    <thead>
                                                        <tr>
                                                            <th class="header_icon"></th>
                                                            <th class="header_name">Name</th>                                                        
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php                                              
                                                    foreach( $tech_doc_sql->result() as $tech_doc ){ 
                                                        
                                                        $tech_doc_params = array(
                                                            'type' => $tech_doc->type,
                                                            'path' => $tech_doc->path,
                                                            'filename' => $tech_doc->filename,
                                                            'url' => $tech_doc->url                                                     
                                                        );
                                                        $tech_doc_arr = $this->resources_model->get_dynamic_link_and_icon($tech_doc_params);

                                                        if( $header_id == $this->resources_model->get_resources_header_id('Forms') ){ // Forms
                                                        
                                                            $icon_td = null;
                                                            $name_td = '
                                                                <a class="chops" href="'.$tech_doc_arr['tech_doc_cont'].'">                                                        
                                                                    <button type="button" class="btn">'.$tech_doc->title.'</button>
                                                                </a>
                                                            ';  

                                                        }
                                                        else if( $header_id == $this->resources_model->get_resources_header_id('Contact List') ){ // Contact List
                                                        
                                                            $icon_td = null;
                                                            $name_td = '<p>TESTING</p>';  

                                                        }
                                                        else{ // default 

                                                            $icon_td = '
                                                                <a href="'.$tech_doc_arr['tech_doc_cont'].'" target="blank">
                                                                    <i class="fa fa-'.$tech_doc_arr['file_icon'].'"></i>
                                                                </a>
                                                            ';
                                                            $name_td = $tech_doc->title;   
                                                                                                        
                                                        }?>

                                                        <tr>
                                                            <td>
                                                                <?php echo $icon_td; ?>
                                                            </td>
                                                            <td>												                
                                                                <?php echo $name_td; ?>
                                                            </td>	                                                      
                                                        </tr>

                                                    <?php   
                                                    }
                                                    ?>					
                                                    </tbody>
                                                </table>
                                                
                                            <?php
                                            }else{ ?>
                                                <label class="no_data_lbl">No Data</label>
                                            <?php    
                                            }
                                            ?>                                            
                                        </div>
                                    </div>
                                </section>

                           
                            
                        </div>
                        
                    <?php
                    //}
                    ?>                                

                </div>
            </div>

		</div>
	</section>
</div>


<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
    <p><?php echo $about_page_text; ?></p>

</div>
<!-- Fancybox END -->


<script>

	$(document).ready(function() {

		//load selected tab on page load/refresh
		var sel_tab = localStorage.curr_tab;
		if(sel_tab!=""){
			$("a[href='" + sel_tab + "']").tab("show");
		}

		$(document.body).on("click", "a[data-toggle='tab']", function(event) {
			selected_tab = $(this).attr("href");
			localStorage.setItem("curr_tab", selected_tab);
		});

	});

</script>



                    