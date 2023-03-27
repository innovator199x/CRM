<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
    $bc_items = array(
        array(
            'title' => $title,
            'status' => 'active',
            'link' =>  $uri
        )
    );
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	<?php 
	if( validation_errors() ){ ?>
		<div class="alert alert-danger">
		<?php echo validation_errors(); ?>
		</div>
	<?php
	}	
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
				<div class="col-md-8 columns">
					<div class="row">


						<div class="col-mdd-3">
							<label>Agency</label>
							<select name="page_display" class="form-control">
                                <option value="">--- Select ---</option>																					
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
    -->

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
            
				<table class="table table-hover main-table jmenu_table">

					<thead>
						<tr>
                            <th>#</th>                    
                            <th>User</th>
                            <th>Agency</th>
							<th>Alternate Agency</th>
							<th>Action</th>
						</tr>
					</thead>

					<tbody>
					<?php
                    $i = 1;
					foreach( $list->result() as $row ){ ?>

						<tr>
                            <td>
								<?php echo $i; ?>
							</td>
							<td>
								<?php echo "{$row->fname} {$row->lname}"; ?>
							</td>
                            <td>
								<a href="/agency/view_agency_details/<?php echo $row->agency_id; ?>">
                                    <?php echo $row->agency_name; ?>
                                </a>
							</td>
							<td>

							
								<?php
								$new_val = $row->alt_agencies != "" ? $row->alt_agencies :  0;

								// get alternate agencies
								$sel_query = "
									a.`agency_id`,
									a.`agency_name`,
									a.`status`,
									a.`deleted`
								";
								$custom_where = "a.`agency_id` IN({$new_val})";
						
								$alt_agen_params = array(
									'sel_query' => $sel_query,
									'custom_where' => $custom_where,
									'a_deleted' => false,
						
									'sort_list' => array(
										array(
											'order_by' => 'a.`agency_name`',
											'sort' => 'ASC'
										)
									),
																
									'country_id' => $this->config->item('country'),			
									'display_query' => 0
								);
								$alt_agen_sql = $this->agency_model->get_agency($alt_agen_params);								
								?>
								<ul>
									<?php
									foreach( $alt_agen_sql->result() as $alt_agency ){ ?>
										<li>
											<a href="/agency/view_agency_details/<?php echo $alt_agency->agency_id; ?>">
												<?php echo $alt_agency->agency_name; ?>
											</a>
											<?php 
												if( $alt_agency->deleted==1 ){
													echo " <small class='text-red'>(Deleted)</small>";
												}
											?>
										</li>
									<?php
									}
									?>									
								</ul>
								
								<!-- EDIT CONNECTION FANCYBOX -->
								<a href="javascript:;" class="fb_trigger edit_connection_fb_link" data-fancybox data-src="#edit_connection_fb_<?php echo $row->agency_user_account_id ?>">Trigger the fancybox</a>							
								<div id="edit_connection_fb_<?php echo $row->agency_user_account_id ?>" class="fancybox edit_connection_fb_div alt_agency_div" style="display:none;" >

									<h4>Edit Connection</h4>

									<?php
									$form_attr = array(
										'class' => 'jform_multi_agency_users'
									);
									echo form_open('/agency/multi_agency_users',$form_attr);
									?>

										<table class="table edit_connection_tbl">
											<thead>
												<tr>
													<th>Agency</th>	
													<th>Action</th>		
												</tr>
											</thead>
											<tbody>
											<?php
											foreach( $alt_agen_sql->result() as $alt_agency ){ ?>
												<tr>
													<td><?php echo $alt_agency->agency_name; ?></td>	
													<td>														
														<button type="button" class="btn btn-danger btn_disconnect_agency">Disconnect</button>													
														<input type="hidden" class="agency_id existing_agency" value="<?php echo $alt_agency->agency_id ?>" />													
													</td>		
												</tr>	
											<?php
											}
											?>	
											</tbody>
										</table>

										<div class="alternate_agency_div"></div>

										<div>
											<button type="button" class="btn add_alt_agency_btn">Add</button>
											<button type="sumbit" class="btn save_alt_agency_btn">Save</button>
											<input type="hidden" name="user" class="user" value="<?php echo $row->agency_user_account_id ?>" />
											<input type="hidden" name="alt_agencies" class="alt_agencies" value="<?php echo $row->alt_agencies ?>" />
										</div>
										

									<?php
									echo form_close();
									?>

								</div>

							</td>
                            <td>
								<button type="button" class="btn edit_connection_btn">Edit Connection</button>                                                             
                            </td>
						</tr>
					<?php
                    $i++;
					}
					?>
					</tbody>

				</table>	

				<div>
                    <button type="button" class="btn" id="add_connection_btn">Add New Multi-Agency User Connection</button>
				</div>			

			</div>

			<nav id="pagi_links" aria-label="Page navigation example" style="text-align:center"><?php echo $pagination; ?></nav>
			<div id="pagi_count" class="pagi_count text-center"><?php echo $pagi_count; ?></div>
			

		</div>
	</section>

</div>

<!-- Fancybox START -->

<!-- ABOUT TEXT -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
	This page holds info on users linked to multiple agencies.
	</p>
	<pre>
<code>SELECT `aua`.`agency_user_account_id`, `aua`.`fname`, `aua`.`lname`, `aua`.`email`, `aua`.`photo`, `aua`.`active`, `aua`.`alt_agencies`, `auat`.`user_type_name`, `auat`.`agency_user_account_type_id`, `a`.`agency_id`, `a`.`agency_name`
FROM `agency_user_accounts` AS `aua`
LEFT JOIN `agency_user_account_types` AS `auat` ON aua.`user_type` = auat.`agency_user_account_type_id`
LEFT JOIN `agency` AS `a` ON aua.`agency_id` = a.`agency_id`
WHERE `aua`.`alt_agencies` IS NOT NULL
ORDER BY `a`.`agency_name` ASC, `aua`.`fname` ASC, `aua`.`lname` ASC</code>
	</pre>

</div>

<!-- ADD CONNECTION -->
<a href="javascript:;" id="add_connection_fb_link" class="fb_trigger" data-fancybox data-src="#add_connection_fb">Trigger the fancybox</a>							
<div id="add_connection_fb" class="fancybox alt_agency_div" style="display:none;" >

	<h4>Add Multi-Agency User Connection</h4>
    
	<?php
    $form_attr = array(
        'class' => 'jform_multi_agency_users'
    );
    echo form_open('/agency/multi_agency_users',$form_attr);
    ?>

		<div class="form-group row">
            <label class="col-sm-5 form-control-label">Agency</label>
            <div class="col-sm-7">
                <p class="form-control-static">
                    <select id="agency" class="form-control existing_agency" data-validation="[NOTEMPTY]">
                        <option value="">SELECT</option>								
                        <?php
                        foreach( $agency_sql->result() as $agency ){ ?>
                            <option value="<?php echo $agency->agency_id ?>"><?php echo $agency->agency_name ?></option>
                        <?php
						} 						
                        ?>														
                    </select>
                </p>
            </div>
        </div>
    
		<div id="add_multi_agency_hid_div">				

			<div class="form-group row">
				<label class="col-sm-5 form-control-label">User</label>
				<div class="col-sm-7">
					<p class="form-control-static">
						<select id="user" name="user" class="form-control" data-validation="[NOTEMPTY]">
							<option value="">SELECT</option>																			
						</select>
					</p>
				</div>
			</div>

			<div id="add_multi_agency_hid_div2">	

				<h5>Alternate Agency</h5>

				<div class="alternate_agency_div"></div>

				<div>
					<button type="button" class="btn add_alt_agency_btn">Add</button>
					<button type="sumbit" class="btn save_alt_agency_btn">Save</button>
				</div>

			</div>
        
		</div>

    <?php
    echo form_close();
    ?>

</div>



<style>
#add_multi_agency_hid_div,
#add_multi_agency_hid_div2,
.save_alt_agency_btn{
	display: none;
}
.fa-remove{
	font-size: 25px;
	position: relative;
	top: 4px;
}
.fancybox-content {
    width: 30%;
}
</style>

<!-- Fancybox END -->
<script>
jQuery(document).ready(function(){

	//success/error message sweel alert pop  start
    <?php 
    if( $this->session->flashdata('add_mauc_success') &&  $this->session->flashdata('add_mauc_success') == 1 ){ ?>
        swal({
            title: "Success!",
            text: "New Multi-Agency User Connection Created",
            type: "success",
            confirmButtonClass: "btn-success"
        });
    <?php 
    }
    ?>

	<?php 
    if( $this->input->get_post('disconnected') &&  $this->input->get_post('disconnected') == 1 ){ ?>
        swal({
            title: "Success!",
            text: "Agency Disconnected",
            type: "success",
            confirmButtonClass: "btn-success"
        });
    <?php 
    }
    ?>


	// remove alt agency row
	jQuery(".alternate_agency_div").on("click",".fa-remove",function(){

		var num_alt_agency = jQuery(this).parents(".alternate_agency_div:first").find(".alt_agency_row").length;
		if( num_alt_agency == 1 ){
			jQuery(this).parents(".alt_agency_div:first").find(".save_alt_agency_btn").hide();
		}
		jQuery(this).parents(".alt_agency_row:first").remove();

	});


	// disconnect connected agency
	jQuery(".btn_disconnect_agency").click(function(){

		var agency_id = jQuery(this).parents("td:first").find('.agency_id').val();
		var alt_agencies = jQuery(this).parents(".jform_multi_agency_users").find('.alt_agencies').val();
		var user = jQuery(this).parents(".jform_multi_agency_users").find('.user').val();
		

		// confirm disconnect agency
		swal({
			title: "Warning!",
			text: "Are you sure you want to disconnect this agency?",
			type: "warning",
			showCancelButton: true,
			cancelButtonText: "Cancel!",
			confirmButtonClass: "btn-warning",
			confirmButtonText: "Yes",                       
			closeOnConfirm: true
		},
		function(isConfirm) {
			
			if (isConfirm) { // yes				

				jQuery("#load-screen").show();
				jQuery.ajax({
					type: "POST",
					url: "/agency/disconnect_alt_agency",
					data: { 	
						agency_id: agency_id,					
						alt_agencies: alt_agencies,													
						user: user
					}
				}).done(function( ret ){	
					
					jQuery("#load-screen").hide();
					window.location='<?php echo $uri ?>/?disconnected=1';

				});
				
				
			}
			
		});

	});	


	// agency script
	jQuery("#agency").change(function(){

		var agency_id = jQuery(this).val();

		if( agency_id != '' ){

			jQuery("#load-screen").show();
			jQuery.ajax({
				type: "POST",
				url: "/agency/get_users",
				data: { 
					agency_id: agency_id
				}
			}).done(function( ret ){
				jQuery("#load-screen").hide();	
				jQuery("#user").html(ret);
			});
			
			jQuery("#add_multi_agency_hid_div").show();

		}else{

			jQuery("#add_multi_agency_hid_div").hide();

		}

	});


	// user script
	jQuery("#add_connection_fb").on('change','#user',function(){

		if( jQuery(this).val() != '' ){
			jQuery("#add_multi_agency_hid_div2").show();
		}else{
			jQuery("#add_multi_agency_hid_div2").hide();
		}

	});


	// add agency connection
	jQuery(".add_alt_agency_btn").click(function(){

		var obj = jQuery(this);
		var container = obj.parents(".jform_multi_agency_users:first");
		var existing_agency = container.find('.existing_agency');

		var existing_agency_arr = [];
		existing_agency.each(function(){

			var agency_id = jQuery(this).val();
			existing_agency_arr.push(agency_id);			

		});

		jQuery("#load-screen").show();
		jQuery.ajax({
			type: "POST",
			url: "/agency/add_agency_row",
			data: { 	
				existing_agency_arr: existing_agency_arr
			}
		}).done(function( ret ){	
			
			jQuery("#load-screen").hide();
			container.find(".alternate_agency_div").append(ret);
			container.find(".save_alt_agency_btn").show();

		});


	});
	
	// fancybox trigger

    // add connection
    jQuery("#add_connection_btn").click(function(){
        jQuery("#add_connection_fb_link").click();
    });

	// edit connection
    jQuery(".edit_connection_btn").click(function(){
        jQuery(this).parents("tr:first").find(".edit_connection_fb_link").click();
    });


	// add multi-agency connection validation
	jQuery(".jform_multi_agency_users").submit(function(){

		var alt_agency_arr = [];

		jQuery(this).find(".alt_agency_arr").each(function(){

			var alt_agency_id = parseInt(jQuery(this).val());

			if( alt_agency_id > 0 ){
				alt_agency_arr.push(alt_agency_id);
			}			

		});
		
		if(  alt_agency_arr.length != jQuery(".alt_agency_arr").length ){

			swal({
				title: "Warning!",
				text: 'Alternate Agency is Required',
				type: "warning"
			});

			return false;

		}else{

			return true;

		}		
		
	});

});
</script>