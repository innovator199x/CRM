<style>
	.date_div {
		width: auto;
		margin-right: 13px;
	}

	table.dataTable thead > tr > th {
		padding-left: 10px !important;
		padding-right: initial !important;
	}

	table.dataTable thead .sorting:after,
	table.dataTable thead .sorting_asc:after,
	table.dataTable thead .sorting_desc:after {
		left: 80px !important;
		right: auto !important;
	}
</style>
<div class="box-typical box-typical-padding">
	<?php 
		// breadcrumbs template
		$bc_items = array(
			array(
				'title' => 'Reports',
				'link' => "/reports"
			),
			array(
				'title' => $title,
				'status' => 'active',
				'link' =>  $uri
			)
		);
		$bc_data['bc_items'] = $bc_items;
		$this->load->view('templates/breadcrumbs', $bc_data);
	?>	
    <header class="box-typical-header">
		<form action="<?php $uri; ?>" method="post">
			<div class="box-typical box-typical-padding">
				<div class="for-groupss row">

					<div class="col-md-10 columns">
						
					</div>
                    
                    <div class="col-lg-2 col-md-12 columns">
                        <section class="proj-page-section float-right">
                            <div class="proj-page-attach">
                                <i class="fa fa-file-excel-o"></i>
                                <p class="name"><?php echo $title; ?></p>
                                <p>
                                    <a href="<?php echo $export_link; ?>" target="blank">
                                        Export
                                    </a>
                                </p>
                            </div>
                        </section>
                    </div>

				</div>
			</div>
		</form>
	</header>
	<div class="body-typical-body">
		<div class="table-responsive">
			<table class="table table-hover main-table table-striped" id="serverside-table">
				<thead>
					<tr>     
                        <th>Property Address</th>
                        <th>Agency Name</th>   
                        <th>
                            <div class="checkbox" style="margin:0;">
                                <input name="chk_all" type="checkbox" id="chk_all" />
                                <label for="chk_all"></label>
                            </div>
                        </th>		                       									                            						                           
					</tr>
					<?php
					foreach( $list->result() as $row ){ 
                        
                        // inner query 1
                        $inner_query_sql_str = "
                        SELECT COUNT(pel.`id`) AS pel_count
                        FROM `property_event_log` AS pel
                        WHERE pel.`property_id` = {$row->property_id}
                        AND pel.`event_type` = 'Property Sales Commission'
                        AND pel.`event_details` = 'Property Service <b>Smoke Alarms</b> marked <b>payable</b>'                        
                        AND DATE(pel.`log_date`) BETWEEN '2022-03-01' AND '2022-03-31' 	
                        ";
                        $inner_query_sql = $this->db->query($inner_query_sql_str);
                        $inner_query_row = $inner_query_sql->row();

                        // inner query 2
                        $inner_query_sql_str2 = "
                        SELECT COUNT(pel.`id`) AS pel_count
                        FROM `property_event_log` AS pel
                        WHERE pel.`property_id` = {$row->property_id}
                        AND pel.`event_type` = 'Property Service updated'
                        AND pel.`event_details` = 'Smoke Alarms Changed from SATS to DIY'                      
                        AND DATE(pel.`log_date`) BETWEEN '2022-03-01' AND '2022-03-31' 	
                        ";
                        $inner_query_sql2 = $this->db->query($inner_query_sql_str2);
                        $inner_query_row2 = $inner_query_sql2->row();

                        // is true to both logs check
                        if( $inner_query_row->pel_count > 0 && $inner_query_row2->pel_count > 0 ){                                                
                        ?>
                            <tr>
                                <td>
                                    <a href="<?php echo $this->config->item('crm_link'); ?>/view_property_details.php?id=<?php echo $row->property_id; ?>">
                                        <?php echo "{$row->address_1} {$row->address_2}, {$row->address_3} {$row->state} {$row->postcode}"; ?>
                                    </a>								
                                </td>
                                <td>
                                    <a href="/agency/view_agency_details/<?php echo $row->agency_id; ?>">
                                        <?php echo "{$row->agency_name}"; ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="checkbox" style="margin:0;">
                                        <input class="prop_id_chk" type="checkbox" id="chk_all_<?php echo $row->property_id; ?>" value="<?php echo $row->property_id; ?>" />
                                        <label for="chk_all_<?php echo $row->property_id; ?>"></label>
                                    </div>
                                </td>
                            </tr>

					    <?php
                        }
					}
					?>
				</thead>
				<tbody></tbody>
			</table>
            
            <div class="float-right">
                <button id="mark_non_payable_btn" type="button" class="btn">Mark Non-Payable</button>
            </div>	
            
		</div>
	</div>

    <!--
	<nav aria-label="Page navigation example" style="text-align:center">
		<?php echo $pagination; ?>
	</nav>

	<div class="pagi_count text-center">
		<?php echo $pagi_count; ?>
	</div>
    -->

</div>


<!-- Fancybox START -->
<!-- ABOUT TEXT -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >
	<h4><?php echo $title; ?></h4>
	<p>This page shows all properties that are not upgraded to the NEW QLD legislation</p>

	<pre><code style="line-height: 1.5;"><?php echo $main_query; ?></code></pre>
</div>
<script>
jQuery(document).ready(function(){	

    jQuery("#mark_non_payable_btn").click(function(){

        var mark_non_payable_btn_dom = jQuery(this);
        var parent_tr = mark_non_payable_btn_dom.parents("tr:first");

        var prop_id_arr = [];
        jQuery(".prop_id_chk:visible:checked").each(function(){

            var prop_id_chk_dom = jQuery(this);
            var prop_id_chk = prop_id_chk_dom.val();

            if( prop_id_chk > 0 ){
                prop_id_arr.push(prop_id_chk);
            }

        });

        if( prop_id_arr.length > 0 ){
            
            jQuery('#load-screen').show();
            jQuery.ajax({
                type: "POST",
                url: "/reports/ajax_mark_as_not_payable_bulk",
                data: { 	
                    prop_id_arr: prop_id_arr
                }
            }).done(function( ret ){
                                    
                jQuery('#load-screen').hide();
                location.reload();					

            });

        }		

    });	

	jQuery("#chk_all").change(function(){

		var chk_all_dom = jQuery(this);

		if( chk_all_dom.prop("checked") == true ){
			jQuery(".prop_id_chk:visible").prop("checked",true);
		}else{
			jQuery(".prop_id_chk:visible").prop("checked",false);
		}		

	});

});
</script>


