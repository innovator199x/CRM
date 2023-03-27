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
            
				<table class="table table-hover main-table duplicate_users_tbl">

					<thead>
						<tr>    
                            <th>#</th> 
                            <th>Service Type</th>       
                            <th>Job Created Date</th>							
                            <th>Job Deleted</th>
                            <th>Property Address</th>
                            <th>Property Deleted</th>
                            <th>Agency Name</th>
							<th>Agency Status</th>
						</tr>
					</thead>

					<tbody>
					<?php
                    
					$i = 1;
					$chk_count = 1;
					if( $list->num_rows() > 0 ){
						foreach( $list->result() as $row ){ ?>
							<tr>
								<td>
									<?php echo $i; ?>
								</td>
								<td>
									<a href="<?php echo $this->config->item('crm_link'); ?>/view_job_details.php?id=<?php echo $row->jid; ?>">
										<?php echo $row->type; ?>
									</a>
								</td>
								<td>
									<?php echo ( $this->system_model->isDateNotEmpty($row->jcreated) )?date('d/m/Y', strtotime($row->jcreated)):null; ?>
								</td>							
								<td>
									<?php echo ( $row->del_job == 1 )?'<span style="color:red">Yes</span>':'<span style="color:green">No</span>'; ?>                
								</td>
								<td>
									<a href="<?php echo $this->config->item('crm_link'); ?>/view_property_details.php?id=<?php echo $row->property_id; ?>">
										<?php echo "{$row->address_1} {$row->address_2} {$row->address_3} {$row->state} {$row->postcode}"; ?>
									</a>
								</td>
								<td>
									<?php echo ( $row->pdeleted == 1 )?'<span style="color:red">Yes</span>':'<span style="color:green">No</span>'; ?>
								</td>	
								<td class="<?php echo ( $row->priority > 0 )?'j_bold':null; ?>">
									<a href="/agency/view_agency_details/<?php echo $row->agency_id; ?>">
										<?php echo $row->agency_name." ".( ( $row->priority > 0 )?' ('.$row->abbreviation.')':null ); ?>
									</a>
								</td>
								<td>
									<?php echo $row->a_status; ?>
								</td>						                            
							</tr>
						<?php
						$i++;
						}
					}else{
						echo "<tr><td colspan='8'>No results found</td></tr>";
					}                
					?>
					</tbody>

				</table>		

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
   This page display no job status.
	</p>
	<pre>
<code>SELECT 
j.`id` AS jid,
j.`del_job`,
j.`date` AS jdate,
j.`created` AS jcreated,
ajt.`id`,
ajt.`type`, 
p.`property_id`,
p.`address_1`,
p.`address_2`,
p.`address_3`,
p.`state`,
p.`postcode`,
p.`deleted` AS pdeleted,
a.`agency_id`,
a.`agency_name`,
a.`status` AS a_status
FROM `jobs` AS j 
LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
WHERE (
j.`status` = '' OR
j.`status` IS NULL
)
ORDER BY j.`created` DESC</code>
	</pre>

</div>




<style>
.move_prop_to_div,
.move_prop_process_btn{
	display:none;
}
.duplicate_users_tbl .btn{
    width: 145px;
}
.fancybox-content {
    width: auto;
}
.attached_prop_div{
	margin: 25px 0;
}
</style>

<!-- Fancybox END -->
<script>
function show_hide_move_btn(container){

	var num_ticked = container.find('.prop_id_chk:checked').length;
	var move_to_user = container.find('.move_to_user').val();

	if( move_to_user != '' && num_ticked > 0 ){
		container.find(".move_prop_process_btn").show();
	}else{
		container.find(".move_prop_process_btn").hide();
	}
}

jQuery(document).ready(function(){


	//success/error message sweel alert pop  start
    <?php 
    if( $this->session->flashdata('move_to_user') &&  $this->session->flashdata('move_to_user') == 1 ){ ?>
        swal({
            title: "Success!",
            text: "Properties Successfuly Moved",
            type: "success",
            confirmButtonClass: "btn-success"
        });
    <?php 
    }
    ?>

	//success/error message sweel alert pop  start
    <?php 
    if( is_numeric($this->input->get_post('active')) ){ 
		$status_txt = ( $this->input->get_post('active') == 1 )?'Deactivated':'Activated';
	?>	

		swal({
            title: "Success!",
            text: "User <?php echo $status_txt; ?>",
            type: "success",
            confirmButtonClass: "btn-success"
        });	
        
    <?php 
    }
    ?>


	// activate or deactivate user
	jQuery(".toggle_user_status").click(function(){

		var aua_id = jQuery(this).parents("td.action_td:first").find(".aua_id").val();
		var active = jQuery(this).attr("data-aua_active");
		var status_txt = ( active == 1 )?'Deactivate':'Activate';

		// confirm move user
		swal({
			title: "Warning!",
			text: "Are you sure you want to "+status_txt+" User?",
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
					url: "/agency/toggle_user_status",
					data: { 
						aua_id: aua_id,
						active: active
					}
				}).done(function( ret ){
					jQuery("#load-screen").hide();
					window.location='/agency/duplicate_users/?active='+active;
				});
			}
			
		});

	});


	// move user
	jQuery(".move_prop_process_btn").click(function(){

		var obj = jQuery(this);
		var container = obj.parents(".move_prop_div:first");

		// confirm move user
		swal({
			title: "Warning!",
			text: "Are you sure you want to move properties to this user?",
			type: "warning",
			showCancelButton: true,
			cancelButtonText: "Cancel!",
			confirmButtonClass: "btn-warning",
			confirmButtonText: "Yes",                       
			closeOnConfirm: true
		},
		function(isConfirm) {
			
			if (isConfirm) { // yes				

				container.find('.jform_move_properties').submit();
								
			}
			
		});


	});


	// check all
	jQuery(".check-all").change(function(){

		var obj = jQuery(this);
		var container = obj.parents(".move_prop_div:first");

		if( jQuery(this).prop("checked") == true ){
			container.find(".prop_id_chk").prop("checked",true);
		}else{
			container.find(".prop_id_chk").prop("checked",false);
		}

		show_hide_move_btn(container);

	});

	// individual checkbox
	jQuery(".prop_id_chk").change(function(){

		var obj = jQuery(this);
		var container = obj.parents(".move_prop_div:first");

		show_hide_move_btn(container);

	});

	// agency script
	jQuery(".agency").change(function(){

		var obj = jQuery(this);
		var container = obj.parents(".move_prop_div:first");
		
		var agency_id = obj.val();
		var exclude_id = container.find(".exclude_id").val();

		if( agency_id != '' ){

			jQuery("#load-screen").show();
			jQuery.ajax({
				type: "POST",
				url: "/agency/get_users",
				data: { 
					agency_id: agency_id,
					display_user_id: 1,
					exclude_id: exclude_id
				}
			}).done(function( ret ){	
				jQuery("#load-screen").hide();
				container.find(".move_to_user").html(ret);
				container.find(".move_prop_to_div").css('display','flex');
			});
			
			container.find(".move_prop_process_div").show();

		}else{

			container.find(".move_prop_process_div").hide();

		}

	});

	// user script
	jQuery(".move_prop_process_div").on('change','.move_to_user',function(){

		var obj = jQuery(this);
		var user_id = obj.val()
		var container = obj.parents(".move_prop_div:first");

		show_hide_move_btn(container);

	});

	// fancybox trigger
    // add connection
    jQuery(".move_properties_btn").click(function(){
        jQuery(this).parents("td:first").find(".move_prop_fb").click();
    });

});
</script>