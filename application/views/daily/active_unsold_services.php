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
							<label>Status</label>
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
    -->

	<section>
		<div class="body-typical-body">
			<div class="table-responsive">
            
				<table class="table table-hover main-table jmenu_table">

					<thead>
						<tr>
                            <th>#</th>                    
                            <th>Service Type</th>
                            <th>Property ID</th>
							<th>Property Address</th>
                            <th>Active Property Services</th>
                            <th>Agency Name</th>
                            <th>Active Agency Services</th>
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
								<?php echo $row->short_name; ?>
							</td>
							<td>
								<?php echo $row->property_id; ?>
							</td>
                            <td>
								<a href="<?php echo $this->config->item('crm_link'); ?>/view_property_details.php?id=<?php echo $row->property_id; ?>">
                                    <?php echo "{$row->address_1} {$row->address_2} {$row->address_3} {$row->state} {$row->postcode}"; ?>
                                </a>
							</td>
                            <td>
                            <?php
                                // get active property services
                                $ps_sql_str = "
                                SELECT DISTINCT ajt.`id`, ajt.`short_name`
                                FROM `property_services` AS ps
                                LEFT JOIN `alarm_job_type` AS ajt ON ps.`alarm_job_type_id` = ajt.`id` 
                                WHERE ps.`property_id` = {$row->property_id}
                                AND ps.`service` = 1 
                                ORDER BY  ajt.`type` 
                                ";
                                $ps_sql = $this->db->query($ps_sql_str);

                                $ps_active_arr = [];
                                foreach( $ps_sql->result() as $ps ){ 
                                    $ps_active_arr[] = $ps->short_name;
                                }

                                echo implode(', ',$ps_active_arr);
                            ?>
                            </td>
                            <td class="<?php echo ( $row->priority > 0 )?'j_bold':null; ?>">
								<a href="/agency/view_agency_details/<?php echo $row->agency_id; ?>">
                                    <?php echo $row->agency_name." ".( ( $row->priority > 0 )?' ('.$row->abbreviation.')':null ); ?>
                                </a>
							</td>
                            <td>
                            <?php
                                // get active property services
                                $ps_sql_str = "
                                SELECT    
                                    agen_serv.`agency_services_id`, 

                                    ajt.`id`, 
                                    ajt.`type`, 
                                    ajt.`short_name`
                                FROM `agency_services` AS agen_serv
                                LEFT JOIN `alarm_job_type` AS ajt ON agen_serv.`service_id` = ajt.`id`
                                WHERE agen_serv.`agency_id` = {$row->agency_id}
                                ORDER BY ajt.`type` 
                                ";
                                $ps_sql = $this->db->query($ps_sql_str);

                                $ps_active_arr = [];
                                foreach( $ps_sql->result() as $ps ){ 
                                    $ps_active_arr[] = $ps->short_name;
                                }

                                echo implode(', ',$ps_active_arr);
                            ?>    
                            </td>
							<td>
								<button type="button" class="btn btn_update_to_nr">Update to No Response</button>
								<input type="hidden" class='property_services_id' value="<?php echo $row->property_services_id; ?>" />
							</td>
						</tr>
					<?php
                    $i++;
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
    This page shows properties that have an active service for a service that is inactive for an agency
	</p>
    <pre>
		<code><?php echo $sql_query; ?></code>
	</pre>

</div>

<!-- Fancybox END -->
<script>
jQuery(document).ready(function(){


    

	 // edit template
	 // fancybox trigger
	 jQuery(".btn_update_to_nr").click(function(){

        var obj = jQuery(this);
		var row = obj.parents("td:first");
		var property_services_id = row.find('.property_services_id').val();

        // confirm
        swal(
        {
            title: "",
            text: "Are you sure you want to update service to No Response?",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-success",
            confirmButtonText: "Yes",
            cancelButtonText: "No, Cancel!",
            closeOnConfirm: true,
            closeOnCancel: true,
        },
        function(isConfirm){

            if(isConfirm){
                
                $('#load-screen').show(); //show loader
                jQuery.ajax({
                type: "POST",
                url: "/daily/update_to_nr",
                dataType: 'json',
                data: {
                    property_services_id: property_services_id
                }
                }).done(function(data){
                    
                    $('#load-screen').hide(); //hide loader 
                    if( parseInt(data.success) == 1 ){                         
                        window.location='<?php echo $uri; ?>';
                    }

                });

            }

        });


    });

	

});
</script>