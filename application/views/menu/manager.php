<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/menu/manager"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">
			<?php
		$form_attr = array(
			'id' => 'jform'
		);
		echo form_open('menu/manager',$form_attr);
		?>
			<div class="for-groupss row">
				<div class="col-md-8 columns">
					<div class="row">


						<div class="col-mdd-3">
							<label>Page Status</label>
							<select name="page_display" class="form-control">
                                <option value="1" <?php echo ( $page_display == 1 )?'selected="selected"':''; ?>>Active</option>								
                                <option value="0" <?php echo ( is_numeric($page_display) && $page_display == 0 )?'selected="selected"':''; ?>>Inactive</option>								
                                <option value="-1" <?php echo ( $page_display == -1 )?'selected="selected"':''; ?>>ALL</option>															
							</select>
						</div>

													

						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<input type="submit" name="search_submit" value="Search" class="btn">
						</div>
						
					</div>

				</div>
			</div>
			</form>
		</div>
	</header>

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
				<table class="table table-hover main-table jmenu_table" id="jmenu_table">
					<thead>
						<tr class="nodrop nodrag">
                            <th class="menu_page_col">Menu/Page</th>
                            <?php
                            // classes
                            foreach( $staff_classes_arr as $row ){ ?>
                                <th><?php echo $row->ClassName; ?></th>
                            <?php
                            }
                            ?>                            
                            <th>Allowed Staff</th>
                            <th>Denied Staff</th>
                            <th>Edit</th>
						</tr>
					</thead>

					
                        <?php
                        // MENU
                        foreach( $menu_arr as $menu_row ){ ?>
                        <tbody class="menu_tbody">
                            <tr id="<?php echo $menu_row->menu_id ?>">
                                <td class="menu_expand_toggle menu_page_col">
                                    
                                    <span class="txt_lbl">
                                        <strong><?php echo $menu_row->menu_name; ?></strong>
                                        <span class="toggle_icon fa fa-sort-desc float-right"></span>
                                    </span>                               
                                    
                                </td>
                                <?php
                                foreach( $staff_classes_arr as $class_row ){ 
                                    
                                    $checkbox_id = "menu-{$menu_row->menu_id}-{$class_row->ClassID}";

                                    // is class ticked?
                                    $sel_query = 'COUNT(mpc.mpc_id) AS jcount';
                                    $perm_class = array(
                                        'sel_query' => $sel_query,
                                        'menu_id' => $menu_row->menu_id,
                                        'staff_class' => $class_row->ClassID,			
                                        'display_query' => 0
                                    );
                                    $perm_class_sql = $this->menu_model->get_menu_permission_class($perm_class);
                                    $perm_class_row = $perm_class_sql->row();
                                    $is_ticked = ( $perm_class_row->jcount > 0 )?true:false;

                                    ?>
                                    <td>
                                        <span class="checkbox">
                                            <input type="checkbox" id="check-<?php echo $checkbox_id ?>" class="req_chk menu_staff_class_chk" <?php echo ( $is_ticked == true )?'checked="checked"':null; ?> value="<?php echo $class_row->ClassID; ?>" />
                                            <label for="check-<?php echo $checkbox_id; ?>" class="chk_lbl"></label>
                                        </span>
                                        <span class="fa fa-check-circle text-green green_check"></span>
                                    </td>
                                <?php
                                }
                                ?> 
                                <td>
                                    <?php
                                    // get assigned staff count
                                    $sel_query = "COUNT(mpu.mpu_id) AS jcount";
                                    $params = array(
                                        'sel_query' => $sel_query,
                                        'active' => 1, 
                                        'menu' => $menu_row->menu_id,
                                        'denied' => 0,
                                        'display_query' => 0
                                    );
                                    $perm_sql = $this->menu_model->get_menu_permission_user($params);
                                    $perm_count = $perm_sql->row()->jcount;

                                    //$btn_txt = ( $perm_count > 0 )?"View ({$perm_count})":'Add';
                                    if( $perm_count > 0 ){
                                        $btn_txt = "View ({$perm_count})";
                                        $btn_class = 'btn-info';
                                    }else{
                                        $btn_txt = 'Add';
                                        $btn_class = null;
                                    }
                                    ?> 
                                    <button type="button" class="btn allow_staff_btn <?php echo $btn_class; ?>"><?php echo $btn_txt; ?></button>                                   
                                </td>
                                <td>
                                    <?php
                                    // get denied staff count
                                    $sel_query = "COUNT(mpu.mpu_id) AS jcount";
                                    $params = array(
                                        'sel_query' => $sel_query,
                                        'active' => 1, 
                                        'menu' => $menu_row->menu_id,
                                        'denied' => 1,
                                        'display_query' => 0
                                    );
                                    $perm_sql = $this->menu_model->get_menu_permission_user($params);
                                    $perm_count = $perm_sql->row()->jcount;

                                    //$btn_txt = ( $perm_count > 0 )?"View ({$perm_count})":'Add';
                                    if( $perm_count > 0 ){
                                        $btn_txt = "View ({$perm_count})";
                                        $btn_class = 'btn-info';
                                    }else{
                                        $btn_txt = 'Add';
                                        $btn_class = null;
                                    }
                                    ?> 
                                    <button type="button" class="btn deny_staff_btn <?php echo $btn_class; ?>"><?php echo $btn_txt; ?></button>   
                                </td>
                                <td>
                                    <button type="button" class="btn btn_update btn_update_menu">Update</button>

                                    <button type="button" class="btn btn_edit btn_edit_menu">Edit</button>
                                    <input type="hidden" class="menu_id" value="<?php echo $menu_row->menu_id ?>" />
                                    <input type="hidden" class="menu_name" value="<?php echo $menu_row->menu_name ?>" />
                                    <input type="hidden" class="active" value="<?php echo $menu_row->active ?>" />
                                    <input type="hidden" class="icon_class_new" value="<?php echo $menu_row->icon_class_new ?>" />
                                </td>
                            </tr>
                        </tbody>

                        <tbody class="pages_tbody">
                            <?php
                            // PAGES
                            $sel_query = "cp.crm_page_id, cp.page_name, cp.page_url, cp.menu, cp.active";
                            $params = array(
                                'sel_query' => $sel_query,
                                'active' => $status, 
                                'menu' => $menu_row->menu_id,
                                
                                'sort_list' => array(
                                    array(
                                        'order_by' => 'page_name',
                                        'sort' => 'ASC'
                                    )
                                ),

                                'display_query' => 0
                            );
                            $pages_sql = $this->menu_model->getPages($params);
                            foreach( $pages_sql->result() as $page_row ){ ?>
                                <tr class="pages_tr <?php echo ( is_numeric($page_row->active) && $page_row->active == 0 )?'deletedRowHL':''; ?>">        
                                    <td class="page_name_td">
                                        <span class="txt_lbl"><?php echo $page_row->page_name; ?></span>                                    
                                        <input type="hidden" class="crm_page_id" value="<?php echo $page_row->crm_page_id; ?>" />
                                    </td>
                                    <?php
                                    foreach( $staff_classes_arr as $class_row ){ 

                                        $checkbox_id = "pade-{$menu_row->menu_id}-{$page_row->crm_page_id}-{$class_row->ClassID}";

                                        // is class ticked?
                                        $sel_query = 'COUNT(cppc.cppc_id) AS jcount';
                                        $perm_class = array(
                                            'sel_query' => $sel_query,
                                            'page' => $page_row->crm_page_id,
                                            'staff_class' => $class_row->ClassID,			
                                            'display_query' => 0
                                        );
                                        $perm_class_sql = $this->menu_model->get_page_permission_class($perm_class);
                                        $perm_class_row = $perm_class_sql->row();
                                        $is_ticked = ( $perm_class_row->jcount > 0 )?true:false;

                                        ?>
                                        <td>
                                            <span class="checkbox">
                                                <input type="checkbox" id="check-<?php echo $checkbox_id ?>" class="req_chk page_staff_class_chk" <?php echo ( $is_ticked == true )?'checked="checked"':null; ?> value="<?php echo $class_row->ClassID; ?>" />
                                                <label for="check-<?php echo $checkbox_id; ?>" class="chk_lbl"></label>
                                            </span>
                                            <span class="fa fa-check-circle text-green green_check"></span>
                                        </td>
                                    <?php
                                    }
                                    ?> 
                                    <td>
                                        <?php
                                        // get assigned staff count
                                        $sel_query = "COUNT(cppu.cppu_id) AS jcount";
                                        $params = array(
                                            'sel_query' => $sel_query,
                                            'active' => 1, 
                                            'page' => $page_row->crm_page_id,
                                            'denied' => 0,
                                            'display_query' => 0
                                        );
                                        $perm_sql = $this->menu_model->get_page_permission_user($params);
                                        $perm_count = $perm_sql->row()->jcount;

                                        //$btn_txt = ( $perm_count > 0 )?"View ({$perm_count})":'Add';
                                        if( $perm_count > 0 ){
                                            $btn_txt = "View ({$perm_count})";
                                            $btn_class = 'btn-info';
                                        }else{
                                            $btn_txt = 'Add';
                                            $btn_class = null;
                                        }
                                        ?> 
                                        <button type="button" class="btn allow_staff_btn <?php echo $btn_class; ?>"><?php echo $btn_txt; ?></button>  
                                    </td>
                                    <td>
                                        <?php
                                        // get denied staff count
                                        $sel_query = "COUNT(cppu.cppu_id) AS jcount";
                                        $params = array(
                                            'sel_query' => $sel_query,
                                            'active' => 1, 
                                            'page' => $page_row->crm_page_id,
                                            'denied' => 1,
                                            'display_query' => 0
                                        );
                                        $perm_sql = $this->menu_model->get_page_permission_user($params);
                                        $perm_count = $perm_sql->row()->jcount;

                                        //$btn_txt = ( $perm_count > 0 )?"View ({$perm_count})":'Add';
                                        if( $perm_count > 0 ){
                                            $btn_txt = "View ({$perm_count})";
                                            $btn_class = 'btn-info';
                                        }else{
                                            $btn_txt = 'Add';
                                            $btn_class = null;
                                        }
                                        ?> 
                                        <button type="button" class="btn deny_staff_btn <?php echo $btn_class; ?>"><?php echo $btn_txt; ?></button>  
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn_update btn_update_page">Update</button>

                                        <button type="button" class="btn btn_edit btn_edit_page">Edit</button>
                                        <input type="hidden" class="page_id" value="<?php echo $page_row->crm_page_id ?>" />
                                        <input type="hidden" class="page_name" value="<?php echo $page_row->page_name ?>" />
                                        <input type="hidden" class="page_url" value="<?php echo $page_row->page_url ?>" />
                                        <input type="hidden" class="menu" value="<?php echo $page_row->menu ?>" />
                                        <input type="hidden" class="active" value="<?php echo $page_row->active ?>" />
                                    </td>
                                </tr>
                            <?php
                            }
                            ?> 
                        </tbody>
                        
                        <?php
                        }
                        ?> 
					

				</table>

				<div>
                    <button type="button" class="btn" id="add_menu_btn">Add Menu</button>
                    <button type="button" class="btn" id="add_page_btn">Add Page</button>
                    <button type="button" class="btn" id="sort_menu_btn">Sort Menu</button>
				</div>

			</div>

			<nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
			<div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
			

		</div>
	</section>

</div>


<!-- Fancybox Start -->


<!-- ADD MENU -->
<a href="javascript:;" id="add_menu_fb_link" class="fb_trigger" data-fancybox data-src="#add_menu_fb">Trigger the fancybox</a>							
<div id="add_menu_fb" class="fancybox" style="display:none;" >

	<h4>Add Menu</h4>
    
	<?php
    $form_attr = array(
        'id' => 'jform_add_menu'
    );
    echo form_open('/menu/add_menu',$form_attr);
    ?>
    
        <div class="form-group row">
            <label class="col-sm-5 form-control-label">Menu Name</label>
            <div class="col-sm-7">
                <p class="form-control-static">
                    <input type="text" class="form-control" id="menu_name" name="menu_name" data-validation="[NOTEMPTY]" />
                </p>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-5 form-control-label"></label>
            <div class="col-sm-7">
                <p class="form-control-static">
                    <button type="submit" class="btn" id="save_menu_btn">Save</button>
                </p>
            </div>
        </div>

    <?php
    echo form_close();
    ?>

</div>

<!-- EDIT MENU -->
<a href="javascript:;" id="edit_menu_fb_link" class="fb_trigger" data-fancybox data-src="#edit_menu_fb">Trigger the fancybox</a>							
<div id="edit_menu_fb" class="fancybox" style="display:none;" >

	<h4>EDIT Menu</h4>
    
	<?php
    $form_attr = array(
        'id' => 'jform_edit_menu'
    );
    echo form_open('/menu/update_menu',$form_attr);
    ?>
    
        <div class="form-group row">
            <label class="col-sm-5 form-control-label">Menu Name</label>
            <div class="col-sm-7">
                <p class="form-control-static">
                    <input type="text" class="form-control menu_name" name="menu_name" data-validation="[NOTEMPTY]" />
                </p>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-5 form-control-label">Active</label>
            <div class="col-sm-7">
                <p class="form-control-static">
                    <select name="active" class="form-control active" data-validation="[NOTEMPTY]">
                        <option value="">SELECT</option>								
                        <option value="0">No</option>	
                        <option value="1">Yes</option>													
                    </select>
                </p>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-5 form-control-label">Icon Class</label>
            <div class="col-sm-7">
                <p class="form-control-static">
                    <input type="text" class="form-control icon_class_new" name="icon_class_new" data-validation="[NOTEMPTY]" />
                </p>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-5 form-control-label"></label>
            <div class="col-sm-7">
                <p class="form-control-static">
                    <button type="submit" class="btn" id="update_menu_btn">Update</button>
                    <input type="hidden" class="menu_id" name="menu_id" />
                </p>
            </div>
        </div>

    <?php
    echo form_close();
    ?>

</div>



<!-- ADD PAGE -->
<a href="javascript:;" id="add_page_fb_link" class="fb_trigger" data-fancybox data-src="#add_page_fb">Trigger the fancybox</a>							
<div id="add_page_fb" class="fancybox" style="display:none;" >

	<h4>Add Page</h4>
    
	<?php
    $form_attr = array(
        'id' => 'jform_add_page'
    );
    echo form_open('/menu/add_page',$form_attr);
    ?>
    
        <div class="form-group row">
            <label class="col-sm-5 form-control-label">Menu</label>
            <div class="col-sm-7">
                <p class="form-control-static">
                    <select id="menu" name="menu" class="form-control" data-validation="[NOTEMPTY]">
                        <option value="">SELECT</option>								
                        <?php
                        foreach( $menu_arr as $menu_row ){ ?>
                            <option value="<?php echo $menu_row->menu_id ?>"><?php echo $menu_row->menu_name ?></option>
                        <?php
                        } 
                        ?>														
                    </select>
                </p>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-5 form-control-label">Page Name</label>
            <div class="col-sm-7">
                <p class="form-control-static">
                    <input type="text" class="form-control" id="page_name" name="page_name" data-validation="[NOTEMPTY]" />
                </p>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-5 form-control-label">Page URL</label>
            <div class="col-sm-7">
                <p class="form-control-static">
                    <input type="text" class="form-control" id="page_url" name="page_url" data-validation="[NOTEMPTY]" />
                </p>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-5 form-control-label"></label>
            <div class="col-sm-7">
                <p class="form-control-static">
                    <button type="submit" class="btn" id="save_menu_btn">Save</button>
                </p>
            </div>
        </div>

    <?php
    echo form_close();
    ?>

</div>


<!-- EDIT PAGE -->
<a href="javascript:;" id="edit_page_fb_link" class="fb_trigger" data-fancybox data-src="#edit_page_fb">Trigger the fancybox</a>							
<div id="edit_page_fb" class="fancybox" style="display:none;" >

	<h4>Edit Page</h4>
    
	<?php
    $form_attr = array(
        'id' => 'jform_edit_page'
    );
    echo form_open('/menu/update_page',$form_attr);
    ?>
    
        <div class="form-group row">
            <label class="col-sm-5 form-control-label">Menu</label>
            <div class="col-sm-7">
                <p class="form-control-static">
                    <select name="menu" class="form-control menu" data-validation="[NOTEMPTY]">
                        <option value="">SELECT</option>								
                        <?php
                        foreach( $menu_arr as $menu_row ){ ?>
                            <option value="<?php echo $menu_row->menu_id ?>"><?php echo $menu_row->menu_name ?></option>
                        <?php
                        } 
                        ?>														
                    </select>
                </p>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-5 form-control-label">Page Name</label>
            <div class="col-sm-7">
                <p class="form-control-static">
                    <input type="text" class="form-control page_name" name="page_name" data-validation="[NOTEMPTY]" />
                </p>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-5 form-control-label">Page URL</label>
            <div class="col-sm-7">
                <p class="form-control-static">
                    <input type="text" class="form-control page_url" name="page_url" data-validation="[NOTEMPTY]" />
                </p>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-5 form-control-label">Active</label>
            <div class="col-sm-7">
                <p class="form-control-static">
                    <select name="active" class="form-control active" data-validation="[NOTEMPTY]">
                        <option value="">SELECT</option>								
                        <option value="0">No</option>	
                        <option value="1">Yes</option>													
                    </select>
                </p>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-5 form-control-label"></label>
            <div class="col-sm-7">
                <p class="form-control-static">
                    <button type="submit" class="btn" id="update_menu_btn">Update</button>
                    <input type="hidden" class="page_id" name="page_id" />
                </p>
            </div>
        </div>

    <?php
    echo form_close();
    ?>

</div>



<!-- ALLOW STAFF -->
<a href="javascript:;" id="allow_staff_fb_link" class="fb_trigger" data-fancybox data-src="#allow_staff_fb">Trigger the fancybox</a>							
<div id="allow_staff_fb" class="fancybox staff_fb_div" style="display:none;" >

	<h4>Allowed Staff</h4>
    
	<?php
    $form_attr = array(
        'id' => 'jform_add_menu'
    );
    echo form_open('/menu/allow_deny_staff',$form_attr);
    ?>

        <div class="existing_staff_div">            
        </div>

        <div class="mini_loader_v2"></div>
    
        <div class="add_staff_div"></div>

        <div class="form-group row">
            <div class="col-sm-12">
                <p class="form-control-static text-right">
                    <button type="button" class="btn add_sa_btn btn-success">Add</button>
                    <button type="submit" class="btn sav_sa_btn save_allowed_staff_btn">Save</button>
                </p>
            </div>
        </div>

        <input type="hidden" id="menu_id" name="menu_id" /> 
        <input type="hidden" id="page_id" name="page_id" />
        <input type="hidden" id="denied" name="denied" value="0" />

    <?php
    echo form_close();
    ?>

</div>


<!-- DENY STAFF -->
<a href="javascript:;" id="deny_staff_fb_link" class="fb_trigger" data-fancybox data-src="#deny_staff_fb">Trigger the fancybox</a>							
<div id="deny_staff_fb" class="fancybox staff_fb_div" style="display:none;" >

	<h4>Denied Staff</h4>
    
	<?php
    $form_attr = array(
        'id' => 'jform_add_menu'
    );
    echo form_open('/menu/allow_deny_staff',$form_attr);
    ?>

        <div class="mini_loader_v2"></div>
    
        <div class="existing_staff_div">            
        </div>
        
        <div class="add_staff_div"></div>

        <div class="form-group row">
            <div class="col-sm-12">
                <p class="form-control-static text-right">
                    <button type="button" class="btn add_sa_btn btn-success">Add</button>
                    <button type="submit" class="btn sav_sa_btn save_denied_staff_btn">Save</button>
                </p>
            </div>
        </div>

        <input type="hidden" id="menu_id" name="menu_id" /> 
        <input type="hidden" id="page_id" name="page_id" />
        <input type="hidden" id="denied" name="denied" value="1" />

    <?php
    echo form_close();
    ?>

</div>



<!-- ABOUT TEXT -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
        The big brown fox, jumps over the lazy dog
	</p>

</div>


<div id="sort_menu_fb" class="jfancybox sort_menu_fb" style="display:none;">

	<h2>Sort Menu</h2>
    
    <table class="table table-hover" id="sort_menu_tbl">    
    <?php
    // MENU
    foreach( $menu_arr as $menu_row ){ ?>
    <tr id="<?php echo $menu_row->menu_id ?>">
        <td><?php echo $menu_row->menu_name; ?></td>                     
        <td>               
            <input type="hidden" class="menu_id" value="<?php echo $menu_row->menu_id ?>" />
            <input type="hidden" class="menu_name" value="<?php echo $menu_row->menu_name ?>" />
            <input type="hidden" class="active" value="<?php echo $menu_row->active ?>" />
            <input type="hidden" class="icon_class_new" value="<?php echo $menu_row->icon_class_new ?>" />
        </td>
    </tr>
    <?php
    }   
    ?>  
    </table>

</div>


<!-- Fancybox END -->

<style>
.pages_tbody,
.btn_update{
    display: none;
}
.page_name_td{
    padding-left: 30px !important;
}
.fa-sort-desc{
    position: relative;
    left: 10px;
    bottom: 3px;
}
.fa-sort-asc{
    position: relative;
    left: 10px;
    top: 3px;
}
.menu_expand_toggle{
    cursor: pointer;
}
th,td{
    text-align: center;
}
.menu_page_col,
.page_name_td{
    text-align: left;
}
.jmenu_table button{
    width: 85px;
}
.fancybox-container{
    z-index: 999 !important;
}
#load-screen {
    z-index: 99999 !important;
}
.mini_loader_v2{
    margin: auto;
    margin-bottom: 24px;
}
.green_check {
    display: none;
    position: relative;
    bottom: 4px;
}
</style>
<script>

function getAllowedStaff(menu_id,page_id){
    
    jQuery(".mini_loader_v2").show();
    jQuery.ajax({
            type: "POST",
            url: "/menu/get_allowed_denied_staff",
            data: { 
                menu_id: menu_id,
                page_id: page_id,
                denied: 0
            }
        }).done(function( ret ) {
            
            jQuery(".mini_loader_v2").hide();
            jQuery(".existing_staff_div").html(ret);

        });	

}


function getDeniedStaff(menu_id,page_id){
    
    jQuery(".mini_loader_v2").show();
    jQuery.ajax({
            type: "POST",
            url: "/menu/get_allowed_denied_staff",
            data: { 
                menu_id: menu_id,
                page_id: page_id,
                denied: 1
            }
        }).done(function( ret ) {

            jQuery(".mini_loader_v2").hide();
            jQuery(".existing_staff_div").html(ret);

        });	

}

jQuery(document).ready(function(){

	//success/error message sweel alert pop  start
    <?php 
    if( $this->session->flashdata('new_menu_success') &&  $this->session->flashdata('new_menu_success') == 1 ){ ?>
        swal({
            title: "Success!",
            text: "New Menu Created",
            type: "success",
            confirmButtonClass: "btn-success",
            showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
            timer: <?php echo $this->config->item('timer') ?>
        });
        setTimeout(function(){ window.location='<?php echo $uri; ?>'; }, <?php echo $this->config->item('timer') ?>);
    <?php 
    }
    ?>

    <?php 
    if( $this->session->flashdata('update_menu_success') &&  $this->session->flashdata('update_menu_success') == 1 ){ ?>
        swal({
            title: "Success!",
            text: "Menu Updated",
            type: "success",
            confirmButtonClass: "btn-success",
            showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
            timer: <?php echo $this->config->item('timer') ?>
        });
        setTimeout(function(){ window.location='<?php echo $uri; ?>'; }, <?php echo $this->config->item('timer') ?>);
    <?php 
    }
    ?>

    <?php 
    if( $this->session->flashdata('new_page_success') &&  $this->session->flashdata('new_page_success') == 1 ){ ?>
        swal({
            title: "Success!",
            text: "New Page Created",
            type: "success",
            confirmButtonClass: "btn-success",
            showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
            timer: <?php echo $this->config->item('timer') ?>
        });
        setTimeout(function(){ window.location='<?php echo $uri; ?>'; }, <?php echo $this->config->item('timer') ?>);
    <?php 
    }
    ?>

    <?php 
    if( $this->session->flashdata('update_page_success') &&  $this->session->flashdata('update_page_success') == 1 ){ ?>
        swal({
            title: "Success!",
            text: "Page Updated",
            type: "success",
            confirmButtonClass: "btn-success",
            showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
            timer: <?php echo $this->config->item('timer') ?>
        });
        setTimeout(function(){ window.location='<?php echo $uri; ?>'; }, <?php echo $this->config->item('timer') ?>);
    <?php 
    }
    ?>

    <?php 
    if( $this->session->flashdata('staff_allowed_success') &&  $this->session->flashdata('staff_allowed_success') == 1 ){ ?>
        swal({
            title: "Success!",
            text: "Staff Allowed",
            type: "success",
            confirmButtonClass: "btn-success",
            showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
            timer: <?php echo $this->config->item('timer') ?>
        });
        setTimeout(function(){ window.location='<?php echo $uri; ?>'; }, <?php echo $this->config->item('timer') ?>);
    <?php 
    }
    ?>

<?php 
    if( $this->session->flashdata('staff_denied_success') &&  $this->session->flashdata('staff_denied_success') == 1 ){ ?>
        swal({
            title: "Success!",
            text: "Staff Denied",
            type: "success",
            confirmButtonClass: "btn-success",
            showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
            timer: <?php echo $this->config->item('timer') ?>
        });
        setTimeout(function(){ window.location='<?php echo $uri; ?>'; }, <?php echo $this->config->item('timer') ?>);
    <?php 
    }
    ?>

    // menu toggle
    jQuery(".menu_expand_toggle").click(function(){

        var obj = jQuery(this);
		var toggle_icon = obj.find(".toggle_icon");
		
		var collapse = toggle_icon.hasClass("fa-sort-desc");
		
		if( collapse == true ){ // expand
            toggle_icon.removeClass('fa-sort-desc');
			toggle_icon.addClass("fa-sort-asc");
            toggle_icon.parents(".menu_tbody:first").next(".pages_tbody").show();
            
		}else{ // collapse
            toggle_icon.removeClass('fa-sort-asc');
			toggle_icon.addClass("fa-sort-desc");
            toggle_icon.parents(".menu_tbody:first").next(".pages_tbody").hide();
		}

    });


    // fancybox trigger
    // add menu
    jQuery("#add_menu_btn").click(function(){
        jQuery("#add_menu_fb_link").click();
    });

    // add page
    jQuery("#add_page_btn").click(function(){
        jQuery("#add_page_fb_link").click();
    });

    // allow staff
    jQuery(".allow_staff_btn").click(function(){

        // insert ID on allow staff lightbox
        // Menu Id
        var menu_id = jQuery(this).parents("tr:first").find(".menu_id").val();
        jQuery("#allow_staff_fb #menu_id").val(menu_id);
        // Page ID
        var page_id = jQuery(this).parents("tr:first").find(".page_id").val();
        jQuery("#allow_staff_fb #page_id").val(page_id);

        jQuery("#allow_staff_fb .existing_staff_div").html('');
        getAllowedStaff(menu_id,page_id);

        
        jQuery(".add_staff_div").html('');
        jQuery("#allow_staff_fb_link").click();
    });

    // allow staff
    jQuery(".deny_staff_btn").click(function(){

        // insert ID on allow staff lightbox
        // Menu Id
        var menu_id = jQuery(this).parents("tr:first").find(".menu_id").val();
        jQuery("#deny_staff_fb #menu_id").val(menu_id);
        // Page ID
        var page_id = jQuery(this).parents("tr:first").find(".page_id").val();
        jQuery("#deny_staff_fb #page_id").val(page_id);

        jQuery("#allow_staff_fb .existing_staff_div").html('');
        getDeniedStaff(menu_id,page_id);

        
        jQuery(".add_staff_div").html('');
        jQuery("#deny_staff_fb_link").click();
    });


    




    // jquery form validation
    // add menu
	jQuery('#jform_add_menu').validate({
		submit: {
			settings: {
				inputContainer: '.form-group',
				errorListClass: 'form-tooltip-error'
			}
		},
		labels: {
			'menu_name': 'Menu Name'
		}
	});

    // add page
	jQuery('#jform_add_page').validate({
		submit: {
			settings: {
				inputContainer: '.form-group',
				errorListClass: 'form-tooltip-error'
			}
		},
		labels: {
			'page_name': 'Page Name',
            'page_url': 'Page URL'
		}
	});


    // edit menu lightbox
	jQuery(".btn_edit_menu").click(function(){

        var obj = jQuery(this);
        var row = obj.parents("td:first");
        
        var menu_id = row.find('.menu_id').val();
        var menu_name = row.find('.menu_name').val();
        var active = row.find('.active').val();
        var icon_class_new = row.find('.icon_class_new').val();
       
        // repopulate data
        jQuery("#edit_menu_fb .menu_id").val(menu_id);
        jQuery("#edit_menu_fb .menu_name").val(menu_name);
        jQuery("#edit_menu_fb .active option[value="+active+"]").prop("selected",true);
        jQuery("#edit_menu_fb .icon_class_new").val(icon_class_new);

        jQuery("#edit_menu_fb_link").click();

	});

    // edit page lightbox
	jQuery(".btn_edit_page").click(function(){

        var obj = jQuery(this);
        var row = obj.parents("td:first");
        
        var page_id = row.find('.page_id').val();
        var page_name = row.find('.page_name').val();
        var page_url = row.find('.page_url').val();
        var menu = row.find('.menu').val();
        var active = row.find('.active').val();
       
        // repopulate data
        jQuery("#edit_page_fb .page_id").val(page_id);
        jQuery("#edit_page_fb .page_name").val(page_name);
        jQuery("#edit_page_fb .page_url").val(page_url);
        jQuery("#edit_page_fb .menu option[value="+menu+"]").prop("selected",true);
        jQuery("#edit_page_fb .active option[value="+active+"]").prop("selected",true);

        jQuery("#edit_page_fb_link").click();

	});
	
	
	// cancel
	jQuery(".btn_cancel").click(function(){
		jQuery(this).parents("tr:first").find(".action_div").hide();
		jQuery(this).parents("tr:first").find(".txt_hid").hide();
		jQuery(this).parents("tr:first").find(".txt_lbl").show();
		jQuery(this).parents("tr:first").find(".btn_edit").show();		
	});


    // add staff
    jQuery(".add_sa_btn").click(function(){

        var existing_staff_arr = [];
        jQuery(this).parents(".staff_fb_div:first").find(".staff_id").each(function(){
            var staff_id = jQuery(this).val();
            existing_staff_arr.push(staff_id);
        });

        console.log(existing_staff_arr);

        var dp_str = ''
        +'<div class="form-group row">'
            +'<div class="col-sm-12">'
                +'<p class="form-control-static">'
                    +'<select name="staff_account[]" class="form-control staff_account" data-validation="[NOTEMPTY]">'
                        +'<option value="">SELECT</option>';
                            <?php
                            foreach( $staff_accounts_arr as $sa_row ){ ?>

                                console.log("index of result "+existing_staff_arr.indexOf(parseInt(<?php echo $sa_row->StaffID; ?>)));
                                if( existing_staff_arr.indexOf("<?php echo $sa_row->StaffID; ?>") == -1 ){
                                    dp_str += '<option value="<?php echo $sa_row->StaffID ?>"><?php echo $this->system_model->formatStaffName($sa_row->FirstName,$sa_row->LastName); ?></option>';
                                }
                            <?php
                            } 
                            ?>																				
                    dp_str += '</select>'
                +'</p>'
            +'</div>'
        +'</div>';

        jQuery(this).parents(".staff_fb_div:first").find('.add_staff_div').append(dp_str);

    });




    // update menu staff class permission
	jQuery(".menu_staff_class_chk").change(function(){
		
		var obj = jQuery(this);
		var row = obj.parents("tr:first");
		
		var allow = ( obj.prop("checked") == true )?1:0;
		var staff_class = obj.val();
		var menu_id = row.find('.menu_id').val();
		
		jQuery.ajax({
			type: "POST",
			url: "/menu/menu_staff_class_permission_update",
			data: { 
				staff_class: staff_class,
				menu_id: menu_id,	
				allow: allow
			}
		}).done(function( ret ){	
			obj.parents("td:first").find('.green_check').fadeIn();
		});
		
		
	});
	
	
	
	// update page staff class permission
	jQuery(".page_staff_class_chk").change(function(){
		
		var obj = jQuery(this);
		var row = obj.parents("tr:first");
		
		var allow = ( obj.prop("checked") == true )?1:0;
		var staff_class = obj.val();
		var page_id = row.find('.page_id').val();
		
		jQuery.ajax({
			type: "POST",
			url: "/menu/page_staff_class_permission_update",
			data: { 
				staff_class: staff_class,
				page_id: page_id,	
				allow: allow
			}
		}).done(function( ret ){
			obj.parents("td:first").find('.green_check').fadeIn();
		});
		
		
	});



    // remove staff from allowed/denied menu permission
	jQuery(".existing_staff_div").on('click','.btn_remove_user_permission',function(){
		
		var obj = jQuery(this);
        var row = obj.parents("div.row:first");
        
        var mpu_id = row.find('.mpu_id').val();
        var cppu_id = row.find('.cppu_id').val();

        // sweet alert confirm
        swal(
            {
                title: "",
                text: 'Are you sure you want to remove user?',
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Continue",
                cancelButtonClass: "btn-danger",
                cancelButtonText: "No, Cancel",
                closeOnConfirm: false,
                closeOnCancel: true,
            },
            function(isConfirm){

                if(isConfirm){
                    
                    jQuery.ajax({
                        type: "POST",
                        url: "/menu/remove_user_permission",
                        data: { 
                            mpu_id: mpu_id,
                            cppu_id: cppu_id			
                        }
                    }).done(function( ret ){	
                        location.reload();
                    });

                }
                
            }
        );
		
		
	});


    // invoke table DND
    jQuery("#sort_menu_tbl").tableDnD({

        onDrop: function(table, row) {

            var job_id = jQuery.tableDnD.serialize({
                'serializeRegexp': null
            });

            console.log(job_id);
            
            
            jQuery("#load-screen").show();
            jQuery.ajax({
                type: "GET",
                url: "/menu/ajax_sort_menu/?sort_menu=1&"+job_id
            }).done(function( ret ){
                jQuery("#load-screen").hide();
                location.reload();
            });
            

        }

    });

    jQuery("#sort_menu_btn").click(function(){

        $.fancybox.open({
            src  : '#sort_menu_fb',
            touch : false
        }); 

    });
   

	
});
</script>