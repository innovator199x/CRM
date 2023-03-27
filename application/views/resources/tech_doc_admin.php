<style>
.header_icon{
	width: 1%;
}
.header_name_col{
	width: 80%;
}
#upload_type_ul{
    list-style: outside none none; 
    margin-bottom: 25px;
}
#upload_type_ul li{
    padding-top: 10px;
}
#form_tech_document_add_file,
#form_tech_document_add_link,
#div_add_res,
#manage_header_div,
#add_header_div,
.btn_update_header_btn{
    display: none;
}

#div_add_res,
#manage_header_div,
#add_header_tbl{
    margin-top: 20px;
}

#add_header_tbl td{
    padding-right: 30px;
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
                foreach( $tech_doc_sql->result() as $tech_doc_row ){ ?>

                    <div class="main_resource_div">

                        <header class="box-typical-header strt-ui-header">
                            <div class="tbl-row">
                                <div class="tbl-cell tbl-cell-title">
                                    <h3>
                                        <span class="glyphicon glyphicon-map-marker"></span>
                                        <?php echo $tech_doc_row->name; ?>
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
                                                    <th class="header_name_col">Name</th>                                                        
                                                    <th class="header_date">Uploaded</th>
                                                    <th class="header_date">Delete</th>
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
                                                'header_id' => $tech_doc_row->tech_doc_header_id,

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
                                                    <td>
                                                        <input type="hidden" class="tech_doc_id" value="<?php echo $tech_doc->technician_documents_id; ?>" />
                                                        <button type="button" class="btn btn-danger btn_delete_tech_doc">Delete</button>
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

    <button type="button" class="btn" id="add_new_file_or_link_btn">Add New File or Link</button>
    <button type="button" class="btn" id="manage_header_btn">Manage Heading</button>
    

    <div id="div_add_res">
		
        <ul id="upload_type_ul">
            <li>
                <input type="radio" name="upload_option" id="upload_file_opt" class="float-left mr-3" value="1" /> 
                <label>Upload File</label>
            </li>
            <li>
                <input type="radio" name="upload_option" id="upload_link_opt" class="float-left mr-3" value="2" /> 
                <label>Upload Link</label>
            </li>
        </ul>
           
        <!-- ADD FILE -->
        <form id="form_tech_document_add_file" method="post" action="/resources/tech_doc_add_file" enctype="multipart/form-data">

            <div class="form-group row">
                <label class="col-sm-1 form-control-label" for="title">Heading</label>
                <div class="col-sm-3">
                    <select name="header" id="header" class="form-control">
                        <option value="">----</option>
                        <?php
                        foreach( $tech_doc_headers->result() as $tech_doc_row ){ ?>
                            <option value="<?php echo $tech_doc_row->tech_doc_header_id; ?>"><?php echo $tech_doc_row->name; ?></option>
                        <?php
                        }
                        ?>                                                
                    </select>
                </div>
            </select>
            </div> 

            <div class="form-group row">
                <label class="col-sm-1 form-control-label" for="file">File</label>            
                <div class="col-sm-3">
                    <input type="file" name="file" id="file" class="form-control file uploadfile submitbtnImg">
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-1 form-control-label" for="title">Title</label>
                <div class="col-sm-3">
                    <input type="text" name="title" id="title" class="form-control title">
                </div>
            </div>  

            <div class="form-group row">
                <label class="col-sm-1 form-control-label" for="title">&nbsp;</label>
                <div class="col-sm-3">
                    <button type="submit" class="btn" id="btn_upload_file">Upload</button>
                </div>
            </div>             

        </form>

        <!-- ADD URL -->
        <form id="form_tech_document_add_link" method="post" action="/resources/tech_doc_add_link">

            <div class="form-group row">
                <label class="col-sm-1 form-control-label" for="title">Heading</label>
                <div class="col-sm-3">
                    <select name="header" id="header" class="form-control">
                        <option value="">----</option>
                        <?php
                        foreach( $tech_doc_headers->result() as $tech_doc_row ){ ?>
                            <option value="<?php echo $tech_doc_row->tech_doc_header_id; ?>"><?php echo $tech_doc_row->name; ?></option>
                        <?php
                        }
                        ?>                                                
                    </select>  
                </div>           
            </div>

            <div class="form-group row">
                <label class="col-sm-1 form-control-label" for="url">URL</label>
                <div class="col-sm-3">
                    <input type="text" name="url" id="url" class="form-control url" />
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-1 form-control-label" for="title">Title</label>
                <div class="col-sm-3">
                    <input type="text" name="title" id="title" class="form-control title">
                </div>
            </div> 

            <div class="form-group row">
                <label class="col-sm-1 form-control-label" for="title">&nbsp;</label>
                <div class="col-sm-3">
                    <button type="submit" class="btn" id="btn_upload_link">Upload</button>
                </div>
            </div>            

        </form>

    </div>    


    <div id="manage_header_div">

        <table class="table table-hover main-table">
            <thead>
                <tr>
                    <th class="header_name_col">Name</th>
                    <th>Action</th>
                </tr>
            </thead>  
            <tbody>
            <?php
            foreach( $tech_doc_headers->result() as $tech_doc_row ){ ?>               
                <tr>
                    <td>
                        <span class="txt_lbl"><?php echo $tech_doc_row->name; ?></span>
                        <input type="text" class="form-control txt_hid header_name" value="<?php echo $tech_doc_row->name; ?>" />
                    </td>
                    <td>
                        <input type="hidden" class="tech_doc_header_id" value="<?php echo $tech_doc_row->tech_doc_header_id; ?>" />
                        <button type="button" class="btn edit_header_btn">Edit</button>
                        <button type="button" class="btn btn_update_header_btn">Update</button>
                        <button type="button" class="btn btn-danger delete_header_btn">Delete</button>
                    </td>
                </tr>
            <?php
            }
            ?>  
            </tbody>                                  
        </table>

        <button type="button" class="btn" id="add_new_header_btn">Add New</button>
        <div id="add_header_div">

            <form method="post" id="add_tech_doc_header_form" action="/resources/add_tech_doc_header">
                <table id="add_header_tbl">
                    <tr>
                        <td>
                            <span>Heading Name</span>
                        </td>
                        <td>
                            <input type="text" class="form-control" name="header_name" id="header_name" />
                        </td>
                        <td>
                            <button type="submit" class="btn" id="add_new_heading_btn">Add</button>
                        </td>
                    </tr>
                </table>                    
            </form>
           
        </div>

    </div>


</div>




<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
    <p>Lorem Ipsum</p>

</div>
<!-- Fancybox END -->

<script>
jQuery(document).ready(function(){

    <?php 
    if( $this->session->flashdata('tech_doc_add_success') == true ){ ?>
        swal({
            title: "Success!",
            text: "New Technician Document Added!",
            type: "success",
            confirmButtonClass: "btn-success",
            showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
            timer: <?php echo $this->config->item('timer') ?>
        });
    <?php 
    }
    ?>


    <?php 
    if( $this->session->flashdata('tech_doc_add_header_success') == true ){ ?>
        swal({
            title: "Success!",
            text: "Add Header Successful!",
            type: "success",
            confirmButtonClass: "btn-success",
            showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
            timer: <?php echo $this->config->item('timer') ?>
        });
    <?php 
    }
    ?>



    // Add file
    jQuery("#form_tech_document_add_file").submit(function(){

        var form_node = jQuery(this)
	
        var file = form_node.find("#file").val();
        var title = form_node.find("#title").val();	
        var header = form_node.find("#header").val();	

        var error = "";
        
        if( header == "" ){
            error += "Header is required\n";
        }
        if( file == "" ){
            error += "Please select file to upload\n";
        }
        if( title == "" ){
            error += "Title is required\n";
        }

        if( error != "" ){

            swal('',error,'error');
            return false;

        }else{
            return true;
        }
        
    });

    // Add URL
    jQuery("#form_tech_document_add_link").submit(function(){

        var form_node = jQuery(this)

        var url = form_node.find("#url").val();
        var title = form_node.find("#title").val();	
        var header = form_node.find("#header").val();	

        var error = "";

        if( header == "" ){
            error += "Header is required\n";
        }
        if( url == "" ){
            error += "URL is required\n";
        }
        if( title == "" ){
            error += "Title is required\n";
        }

        if( error != "" ){

            swal('',error,'error');
            return false;

        }else{
            return true;
        }

    });


    // Add header
    jQuery("#add_tech_doc_header_form").submit(function(){

        var form_node = jQuery(this)

        var header_name = form_node.find("#header_name").val();

        var error = "";

        if( header_name == "" ){
            error += "Header is required\n";
        }

        if( error != "" ){

            swal('',error,'error');
            return false;

        }else{
            return true;
        }

    });


    
    // add new toggle
	jQuery("#add_new_file_or_link_btn").click(function(){

        var btn_node = jQuery(this);
        var btn_txt = btn_node.text();
        var orig_btn_txt = 'Add New File or Link';
        var toggle_div = jQuery("#div_add_res");
        
        toggle_button(btn_node,orig_btn_txt,toggle_div);
        
    });

    // upload file
	jQuery("#upload_file_opt").click(function(){

        var btn_node = jQuery(this);
        var is_checked = btn_node.prop("checked");

        if( is_checked == true ){ // show

            jQuery("#form_tech_document_add_file").show();
            jQuery("#form_tech_document_add_link").hide();

        }     

    });


    // upload link
	jQuery("#upload_link_opt").click(function(){

        var btn_node = jQuery(this);
        var is_checked = btn_node.prop("checked");

        if( is_checked == true ){ // show

            jQuery("#form_tech_document_add_link").show();
            jQuery("#form_tech_document_add_file").hide();

        }    

    });

    // manage header
    jQuery("#manage_header_btn").click(function(){

        var btn_node = jQuery(this);
        var btn_txt = btn_node.text();
        var orig_btn_txt = 'Manage Heading';
        var toggle_div = jQuery("#manage_header_div");

        toggle_button(btn_node,orig_btn_txt,toggle_div);

    });
    
    // add new header
    jQuery("#add_new_header_btn").click(function(){

        var btn_node = jQuery(this);
        var btn_txt = btn_node.text();
        var orig_btn_txt = 'Add New';
        var toggle_div = jQuery("#add_header_div");

        toggle_button(btn_node,orig_btn_txt,toggle_div);

    });

    // inline edit
	jQuery(".edit_header_btn").click(function(){
    
        var btn_node = jQuery(this);
        var orig_btn_txt = "Edit";     
        var btn_update_class = '.btn_update_header_btn';        

        toggle_inline_edit(btn_node,orig_btn_txt,'Cancel',btn_update_class);

    });

    // delete documents
    jQuery(".btn_delete_tech_doc").click(function(){

        var btn_node = jQuery(this);
        var parents_row = btn_node.parents("tr:first");
        
        var tech_doc_id = parents_row.find(".tech_doc_id").val();        

        swal({
            title: "Warning!",
            text: "Are you sure you want to delete this file/link?",
            type: "warning",						
            showCancelButton: true,
            confirmButtonClass: "btn-success",
            confirmButtonText: "Yes, Continue",
            cancelButtonClass: "btn-danger",
            cancelButtonText: "No, Cancel!",
            closeOnConfirm: true,
            showLoaderOnConfirm: true,
            closeOnCancel: true
        },
        function(isConfirm) {

            if (isConfirm) {

                $('#load-screen').show();
                jQuery.ajax({
                    type: "POST",
                    url: "/resources/delete_tech_doc",
                    data: { 	
                        tech_doc_id: tech_doc_id
                    }
                }).done(function( ret ){
                                            
                    $('#load-screen').hide();	
                    swal({
                        title: "Success!",
                        text: "Technician Document Deleted!",
                        type: "success",
                        confirmButtonClass: "btn-success",
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });
                    setTimeout(function(){ window.location='/resources/tech_doc_admin'; }, <?php echo $this->config->item('timer') ?>);	                    
                    
               
                });

            }   

        });	

    });


    // delete header
    jQuery(".delete_header_btn").click(function(){

        var btn_node = jQuery(this);
        var parents_row = btn_node.parents("tr:first");
        
        var tech_doc_header_id = parents_row.find(".tech_doc_header_id").val();

        swal({
            title: "Warning!",
            text: "Are you sure you want to delete this header?",
            type: "warning",						
            showCancelButton: true,
            confirmButtonClass: "btn-success",
            confirmButtonText: "Yes, Continue",
            cancelButtonClass: "btn-danger",
            cancelButtonText: "No, Cancel!",
            closeOnConfirm: true,
            showLoaderOnConfirm: true,
            closeOnCancel: true
        },
        function(isConfirm) {

            if (isConfirm) {

                $('#load-screen').show();
                jQuery.ajax({
                    type: "POST",
                    url: "/resources/delete_tech_doc_header",
                    data: { 	
                        tech_doc_header_id: tech_doc_header_id
                    }
                }).done(function( ret ){
                                    
                    $('#load-screen').hide();	
                    swal({
                        title: "Success!",
                        text: "Header Deleted!",
                        type: "success",
                        confirmButtonClass: "btn-success",
                        showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                        timer: <?php echo $this->config->item('timer') ?>
                    });
                    setTimeout(function(){ window.location='/resources/tech_doc_admin'; }, <?php echo $this->config->item('timer') ?>);	                    

                });

            }            

        });	

    });

    // update header
	jQuery(".btn_update_header_btn").click(function(){

        var btn_node = jQuery(this);
        var parents_row = btn_node.parents("tr:first");

        var header_name = parents_row.find(".header_name").val();
        var tech_doc_header_id = parents_row.find(".tech_doc_header_id").val();

        $('#load-screen').show();
		jQuery.ajax({
			type: "POST",
			url: "/resources/update_tech_doc_header",
			data: { 	
                tech_doc_header_id: tech_doc_header_id,
				header_name: header_name				
			}
		}).done(function( ret ){
				
            $('#load-screen').hide();	
            swal({
				title: "Success!",
				text: "Header Updated!",
				type: "success",
				confirmButtonClass: "btn-success",
				showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
				timer: <?php echo $this->config->item('timer') ?>
			});
			setTimeout(function(){ window.location='/resources/tech_doc_admin'; }, <?php echo $this->config->item('timer') ?>);	

		});

    });


});
</script>



                    