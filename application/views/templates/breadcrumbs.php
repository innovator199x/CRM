<nav class="top_breadcrumb" aria-label="breadcrumb">
    <ol class="breadcrumb">
    <?php
     // get logged user staff class
    $logged_user_class_id = $this->system_model->getStaffClassID();
    if( $logged_user_class_id == 6 ){ // tech ?>
        <li class="breadcrumb-item"><a href="/home/index">HOME</a></li>
    <?php
    }else{ ?>
        <li class="breadcrumb-item"><a href="/home/index">HOME</a></li>
    <?php
    }
    ?>    
    <?php
    foreach( $bc_items as $bc_row ){ ?>
        <li class="breadcrumb-item <?php echo $bc_row['status']; ?>"><a href="<?php echo $bc_row['link']; ?>"><?php echo $bc_row['title']; ?></a></li>
    <?php
    }
    ?>
    </ol>


    <?php    
    if( $logged_user_class_id != 6 ){ // dont show for tech
         if($has_tech_version==1){
    ?>
        <a class="tech_verion_page_link" href="<?php echo $has_tech_version_url; ?>"><span class="fa fa-truck"></span> Technician View</a>
    <?php
        }else if($has_admin_version==1){
    ?>
         <a class="tech_verion_page_link" href="<?php echo $has_admin_version_url; ?>"><span class="fa fa-user"></span> Admin View</a>
    <?php
        }
    }
    ?>

    <?php
    // VTS quick links
    if( $vts_quick_links == true ){ ?>

        
        <div class="vts_calendar_link">
            <a href=<?php echo "{$uri}/{$tech_id}/?month={$prevmonth}&year={$prevyear}" ?>>
                <span class="fa fa-chevron-circle-left"></span>
            </a>
            <?php echo $current_month; ?>             
            <a href="<?php echo "{$uri}/{$tech_id}/?month={$nextmonth}&year={$nextyear}" ?>">
                <span class="fa fa-chevron-circle-right"></span>
            </a>
        </div>
           
       
        
    <?php
    }
    ?>   
    


    <a id="about_page_link" class="top_about_page" href="javascript:void(0);" ><i class="fa fa-question-circle"></i> About Page</a>
    
</nav>