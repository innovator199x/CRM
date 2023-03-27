<style>
.header_icon{
	width: 1%;
}
.header_name{
	width: 90%;
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

	<section>
		<div class="body-typical-body">

            <div class="row">
                <div class="col-lg-12">	

                    <?php
                    foreach( $header_sql->result() as $header ){ ?>

                        <div class="main_resource_div">

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

                            

                                <section class="box-typical-123">		
                                    <div class="box-typical-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover main-table">
                                                <thead>
                                                    <tr>
                                                        <th class="header_icon"></th>
                                                        <th class="header_name">Name</th>
                                                        <th class="header_date">Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php
                                                // get list items
                                                    $sel_query = "
                                                    td.`technician_documents_id`,
                                                    td.`type`,
                                                    td.`path`,
                                                    td.`filename`,
                                                    td.`title`,  
                                                    td.`url`,
                                                    td.`date`                      
                                                ";
                                                
                                                $params = array(
                                                    'sel_query' => $sel_query,
                                                    'country_id' => $this->config->item('country'),
                                                    'header_id' => $header->tech_doc_header_id,

                                                    'sort_list' => array(
                                                        array(
                                                            'order_by' => 'td.`title`',
                                                            'sort' => 'ASC',
                                                        )
                                                    ),

                                                    'display_query' => 0
                                                );
                                                $tech_doc_sql = $this->resources_model->get_tech_doc($params);

                                                foreach( $tech_doc_sql->result() as $tech_doc ){ 
                                                    
                                                    $tech_doc_params = array(
                                                        'type' => $tech_doc->type,
                                                        'path' => $tech_doc->path,
                                                        'filename' => $tech_doc->filename,
                                                        'url' => $tech_doc->url                                                     
                                                    );
                                                    $tech_doc_arr = $this->resources_model->get_dynamic_link_and_icon($tech_doc_params);

                                                    ?>
                                                    <tr>
                                                        <td>
                                                            <a href="javascript:void(0);" target="blank">
                                                                <i class="fa fa-<?php echo $tech_doc_arr['file_icon']; ?>"></i>
                                                            </a>
                                                        </td>
                                                        <td>												                
                                                            <a href="<?php echo $tech_doc_arr['tech_doc_cont']; ?>" target="_blank">
                                                                <?php echo $tech_doc->title; ?>
                                                            </a>
                                                        </td>	
                                                        <td>
                                                            <?php echo date('d/m/Y',strtotime($tech_doc->date)); ?>
                                                        </td>
                                                    </tr>	
                                                <?php
                                                }
                                                ?>					
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </section>

                           
                            
                        </div>
                        
                    <?php
                    }
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
    <p>Lorem Ipsum</p>

</div>
<!-- Fancybox END -->



                    