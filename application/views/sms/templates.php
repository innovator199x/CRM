<style>
.dataTables_filter{float:right;}
.nav-item{width:50%;}
</style>
<div class="box-typical box-typical-padding">

	<?php 
	$edit_permission = false;
    if($class_id == 2 || $class_id == 3 || $class_id == 9 ){
        $edit_permission = true;
    }
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/sms/templates"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>
	<?php if($edit_permission == false){ ?>
    <div class="alert alert-warning">
        <strong>Please Note;</strong> You don't have permission to Add/Edit SMS Templates.
    </div>
    <?php } ?>

	<div class="tabs-section-nav tabs-section-nav-icons">
        <div class="tbl">
            <ul class="nav" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#template">
                        <span class="nav-link-in"><i class="fa fa-info"></i> Templates</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#logs">
                        <span class="nav-link-in"><i class="fa fa-list"></i> Logs</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

	<div class="tab-content">
		<div class="tab-pane fade active show" id="template">
			<div class="box-typical-body mt-3">
				<header class="box-typical-header">
					<div class="box-typical box-typical-padding">
						<?php
						$form_attr = array(
							'id' => 'jform'
						);
						echo form_open('sms/templates',$form_attr);
						?>
							<div class="for-groupss row">
								<div class="col-md-12 columns">
									<div class="row">
										<div class="col-md-6">
											<?php if($edit_permission == true){ ?>
											<label class="form-control-label">&nbsp;</label>
												<button type="button" class="btn" id="add_template_btn">Add New</button>
											<?php } ?>
										</div>
										<div class="col-md-6">
											<div class="row pull-right">
												<div class="col-md-3 offset-md-3">
													<div class="form-group">
														<label>Display</label>
														<select name="status" class="form-control">
															<option value="-1" <?php echo ( $status == -1 )?'selected':''; ?>>ALL</option>
															<option value="1" <?php echo ( $status == 1 )?'selected':''; ?>>Active</option>								
															<option value="0" <?php echo ( is_numeric($status) && $status == 0 )?'selected':''; ?>>Inactive</option>								                                															
														</select>
													</div>
												</div>

												<div class="col-md-3">
													<div class="form-group">
														<label>Category</label>
														<select name="category" class="form-control">							
															<option value="">ALL</option>	
															<?php 
																foreach($category_filter->result() as $category_row){
																	if($category_row->category!=""){
																		$sel = ($category_row->category==$this->input->get_post('category')) ? "selected='true'" : NULL ;
															?>
																		<option <?php echo $sel; ?> value="<?php echo $category_row->category ?>"><?php echo $category_row->category ?></option>
															<?php } } ?>														
														</select>
													</div>
												</div>		
												<div class="col-md-3">
													<div class="form-group">
														<label>&nbsp;</label>
														<input type="submit" name="search_submit" value="Search" class="btn btn-rimary btn-block">
													</div>
												</div>
											</div>
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
				
					<table class="table table-hover main-table jmenu_table">

						<thead>
							<tr>
								<th class="temp_name_col">Template Name</th>                    
								<th class="desc_col">Category</th>
								<th>Status</th>
								<?php if($edit_permission == true){ ?>
									<th>Edit</th>
								<?php } ?>
							</tr>
						</thead>

						<tbody>
						<?php
						foreach( $list->result() as $row ){ ?>
							<tr>
								<td>
									<?php echo $row->type_name; ?>
								</td>
								<td>
									<?php echo $row->category; ?>
								</td>
								<td>
									<?php echo ( $row->active == 1 )?'<span style="color:green">Active<span>':'<span style="color:red">Inactive<span>'; ?>
								</td>
								<?php if($edit_permission == true){ ?>
									<td>
										<button type="button" class="btn btn_edit edit_template_btn">Edit</button>
										<input type="hidden" class='sms_api_type_id' value="<?php echo $row->sms_api_type_id; ?>" />
										<input type="hidden" class='type_name' value="<?php echo $row->type_name; ?>" />
										<input type="hidden" class='category' value="<?php echo $row->category; ?>" />
										<input type="hidden" class='body' value="<?php echo htmlspecialchars($row->body); ?>" />
										<input type="hidden" class='active' value="<?php echo $row->active; ?>" />
									</td>
								<?php } ?>
							</tr>
						<?php } ?>
						</tbody>
					</table>				
				</div>

				<nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
				<div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
				

			</div>
			</section>
			</div>
		</div>
		<div role="tabpanel" class="tab-pane fade" id="logs">
            <div class="box-typical-body mt-3">
                <table class="table table-bordered table-striped table-hover mt-2" id="logs_table" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Details</th>
                            <th>Staff</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
	</div>
	

</div>

<!-- Fancybox START -->

<!-- ADD Template -->
<a href="javascript:;" id="add_template_fb_link" class="fb_trigger" data-fancybox data-src="#add_template">Trigger the fancybox</a>							
<div id="add_template" class="fancybox" style="display:none;" >

	<h4>Add Template</h4>
    
	<?php
    $form_attr = array(
        'id' => 'jform_add_template'
    );
    echo form_open('/sms/add_template',$form_attr);
    ?>
    
	<div class="form-group row">

		<div class="col-sm-9">

			<div class="form-group row">
				<label class="col-sm-2 form-control-label">Name</label>
				<div class="col-sm-10">
					<p class="form-control-static">
						<input type="text" class="form-control type_name" name="type_name" data-validation="[NOTEMPTY]" />
					</p>
				</div>
			</div>

			<div class="form-group row">
				<label class="col-sm-2 form-control-label">Category</label>
				<div class="col-sm-10">
					<p class="form-control-static">
						<input type="text" class="form-control category" name="category" />
					</p>
				</div>
			</div>

			<div class="form-group row">
				<label class="col-sm-2 form-control-label">Body</label>
				<div class="col-sm-10">
					<p class="form-control-static">
						<textarea name="body" rows="4" class="form-control body" data-validation="[NOTEMPTY]"></textarea>
					</p>
				</div>
			</div>

			<div class="form-group row">
				<label class="col-sm-2 form-control-label"></label>
				<div class="col-sm-10">
					<p class="form-control-static">
						<button type="submit" class="btn" id="save_menu_btn">Save</button>
						<input type="hidden" class='sms_api_type_id' value="<?php echo $row->sms_api_type_id; ?>" />
					</p>
				</div>
			</div>

		</div>

		<div class="col-sm-3">
			<label class="col-sm-5 form-control-label">TAGS:</label>
			<div class="form-group row tags_div">
				<button type="button" class="btn tag_btn" data-tag_val="{agency_name}">Agency Name</button>
				<button type="button" class="btn tag_btn" data-tag_val="{p_address}">Address</button>	
				<button type="button" class="btn tag_btn" data-tag_val="{job_date}">Job date</button>
				<button type="button" class="btn tag_btn" data-tag_val="{serv_name}">Service Type</button>
				<button type="button" class="btn tag_btn" data-tag_val="{tenant_number}">SATS Tenant Line</button>
			<!--<button type="button" class="btn tag_btn" data-tag_val="{your_agency}">Your agency</button>-->
				<button type="button" class="btn tag_btn" data-tag_val="{en_link}">EN link</button>
				<button type="button" class="btn tag_btn" data-tag_val="{time_of_day}">Time of day</button>
				<button type="button" class="btn tag_btn" data-tag_val="{booked_with}">Booked With</button>
                <button type="button" class="btn tag_btn" data-tag_val="{sats_domain}">SATS Domain</button>
				<button type="button" class="btn tag_btn" data-tag_val="{link_upgrade_to_sell}">Link: Upgrade To Sell</button>

				<button type="button" class="btn tag_btn" data-tag_val="{agency_staff_fname}">Agency Staff First Name</button>
				<button type="button" class="btn tag_btn" data-tag_val="{agency_staff_2fa_code}">Agency Staff 2FA Code</button>
				<button type="button" class="btn tag_btn" data-tag_val="{agency_staff_device_used}">Agency Staff Device Used</button>
				<button type="button" class="btn tag_btn" data-tag_val="{agency_staff_browser_used}">Agency Staff Browser Used</button>
				<button type="button" class="btn tag_btn" data-tag_val="{agency_staff_ip}">Agency Staff IP</button>				
			</div>
		</div>

	</div>

    <?php
    echo form_close();
    ?>

</div>



<!-- EDIT Template -->
<a href="javascript:;" id="edit_template_fb_link" class="fb_trigger" data-fancybox data-src="#edit_template_fb">Trigger the fancybox</a>							
<div id="edit_template_fb" class="fancybox" style="display:none;" >

	<h4>Edit Template</h4>
    
	<?php
    $form_attr = array(
        'id' => 'jform_edit_template'
    );
    echo form_open('/sms/update_template',$form_attr);
    ?>

	<div class="form-group row">

		<div class="col-sm-9">
    
			<div class="form-group row">
				<label class="col-sm-2 form-control-label">Name</label>
				<div class="col-sm-10">
					<p class="form-control-static">
						<input type="text" class="form-control type_name" name="type_name" data-validation="[NOTEMPTY]" />
					</p>
				</div>
			</div>

			<div class="form-group row">
				<label class="col-sm-2 form-control-label">Category</label>
				<div class="col-sm-10">
					<p class="form-control-static">
						<input type="text" class="form-control category" name="category" />
					</p>
				</div>
			</div>

			<div class="form-group row">
				<label class="col-sm-2 form-control-label">Body</label>
				<div class="col-sm-10">
					<p class="form-control-static">
						<textarea name="body" rows="4" class="form-control body" data-validation="[NOTEMPTY]"></textarea>
					</p>
				</div>
			</div>

			<div class="form-group row">
				<label class="col-sm-2 form-control-label">Active</label>
				<div class="col-sm-10">
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
				<label class="col-sm-2 form-control-label"></label>
				<div class="col-sm-10">
					<p class="form-control-static">
						<button type="submit" class="btn" id="update_menu_btn">Update</button>
						<input type="hidden" name="sms_api_type_id" class='sms_api_type_id' value="<?php echo $row->sms_api_type_id; ?>" />
					</p>
				</div>
			</div>

		</div>

		<div class="col-sm-3">
			<label class="col-sm-5 form-control-label">TAGS:</label>
			<div class="form-group row tags_div">
				<button type="button" class="btn tag_btn" data-tag_val="{agency_name}">Agency Name</button>
				<button type="button" class="btn tag_btn" data-tag_val="{p_address}">Address</button>	
				<button type="button" class="btn tag_btn" data-tag_val="{job_date}">Job date</button>
				<button type="button" class="btn tag_btn" data-tag_val="{serv_name}">Service Type</button>
				<button type="button" class="btn tag_btn" data-tag_val="{tenant_number}">SATS Tenant Line</button>
				<!--<button type="button" class="btn tag_btn" data-tag_val="{your_agency}">Your agency</button>-->
				<button type="button" class="btn tag_btn" data-tag_val="{en_link}">EN link</button>
				<button type="button" class="btn tag_btn" data-tag_val="{time_of_day}">Time of day</button>
				<button type="button" class="btn tag_btn" data-tag_val="{booked_with}">Booked With</button>
                <button type="button" class="btn tag_btn" data-tag_val="{sats_domain}">SATS Domain</button>
				<button type="button" class="btn tag_btn" data-tag_val="{link_upgrade_to_sell}">Link: Upgrade To Sell</button>

				<button type="button" class="btn tag_btn" data-tag_val="{agency_staff_fname}">Agency Staff First Name</button>
				<button type="button" class="btn tag_btn" data-tag_val="{agency_staff_2fa_code}">Agency Staff 2FA Code</button>
				<button type="button" class="btn tag_btn" data-tag_val="{agency_staff_device_used}">Agency Staff Device Used</button>
				<button type="button" class="btn tag_btn" data-tag_val="{agency_staff_browser_used}">Agency Staff Browser Used</button>
				<button type="button" class="btn tag_btn" data-tag_val="{agency_staff_ip}">Agency Staff IP</button>	
			</div>
		</div>

	</div>

    <?php
    echo form_close();
    ?>

</div>


<!-- ABOUT TEXT -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
	This page can show, update and add an SMS template.
	</p>
<pre><code><?php echo $last_query; ?></code></pre>

</div>

<!-- Fancybox END -->

<style>
.fancybox-content {
	width: 50%;
}
.temp_name_col {
    width: 30%;
}
.desc_col {
    width: 57%;
}
.tags_div button{
	margin-bottom: 5px;
	width: 84%;
}
</style>
<script>
jQuery(document).ready(function(){
	$('#logs_table').DataTable({
        "order": [[ 2, "desc" ]],
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?php echo base_url('sms/datatable_logs'); ?>",
            "dataType": "json",
            "type": "POST"
        },
        "columns": [{
                "data": "details"
            },
            {
                "data": "name"
            },
            {
                "data": "created_date"
            }
        ]
    });

	<?php 
    if( $this->session->flashdata('update_template_success') &&  $this->session->flashdata('update_template_success') == 1 ){ ?>
        swal({
            title: "Success!",
            text: "Template Updated",
            type: "success",
            confirmButtonClass: "btn-success"
        });
    <?php 
    }
    ?>

	<?php 
    if( $this->session->flashdata('add_template_success') &&  $this->session->flashdata('add_template_success') == 1 ){ ?>
        swal({
            title: "Success!",
            text: "Template Added",
            type: "success",
            confirmButtonClass: "btn-success"
        });
    <?php 
    }
    ?>

    // add template
	// fancybox trigger
    jQuery("#add_template_btn").click(function(){
        jQuery("#add_template_fb_link").click();
    });

	// jquery form validation
	jQuery('#jform_add_template').validate({
		submit: {
			settings: {
				inputContainer: '.form-group',
				errorListClass: 'form-tooltip-error'
			}
		},
		labels: {
			'template_name': 'Template Name',
			'category': 'Category',
			'body': 'body'
		}
	});


	 // edit template
	 // fancybox trigger
	 jQuery(".edit_template_btn").click(function(){

		var obj = jQuery(this);
		var row = obj.parents("td:first");

		var sms_api_type_id = row.find('.sms_api_type_id').val();
		var type_name = row.find('.type_name').val();
		var category = row.find('.category').val();
		var body = row.find('.body').val();
		var active = row.find('.active').val();

		// repopulate data
		jQuery("#edit_template_fb .sms_api_type_id").val(sms_api_type_id);
		jQuery("#edit_template_fb .type_name").val(type_name);
		jQuery("#edit_template_fb .category").val(category);
		jQuery("#edit_template_fb .body").html(body);
		jQuery("#edit_template_fb .active option[value="+active+"]").prop("selected",true);

        jQuery("#edit_template_fb_link").click();
    });

	// jquery form validation
	// add
	jQuery('#jform_add_template').validate({
		submit: {
			settings: {
				inputContainer: '.form-group',
				errorListClass: 'form-tooltip-error'
			}
		},
		labels: {
			'template_name': 'Template Name',
			'body': 'Body',
		}
	});

	// edit
	jQuery('#jform_edit_template').validate({
		submit: {
			settings: {
				inputContainer: '.form-group',
				errorListClass: 'form-tooltip-error'
			}
		},
		labels: {
			'template_name': 'Template Name',
			'body': 'Body',
			'active': 'active'
		}
	});

	

	jQuery(".tag_btn").on("click", function() {

		var obj = jQuery(this);
		var tag = obj.attr("data-tag_val");
		var panel = obj.parents(".fancybox");		
		var target = panel.find("textarea.body");

		typeInTextarea(jQuery(target), tag);
		
	});

});
</script>