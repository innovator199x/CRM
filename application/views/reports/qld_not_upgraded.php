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

	<style>
	.separator {
		margin: 0 5px;
	}
	.bold_it{
		font-weight: bold;
	}
	</style>
    
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

                        <div class="col-md-4">
							<label for="agency_select">Agency</label>
							<select id="agency_filter" name="agency_filter"  class="form-control field_g2">
                                <option value="">ALL</option>
                                <?php
                                foreach( $distinct_agency_sql->result() as $agency_row ){ ?>
                                    <option value="<?php echo $agency_row->agency_id; ?>" <?php echo ( $agency_row->agency_id == $this->input->get_post('agency_filter') )?'selected="selected"':null; ?>>
                                        <?php echo $agency_row->agency_name; ?>
                                    </option>
                                <?php
                                }
                                ?>
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
            
				<table class="table table-hover main-table table-striped">

					<thead>
						<tr>    
                            <th>Address</th> 
                            <th>Agency</th>   
							<th>Quote Amount</th>                                						                           
						</tr>
					</thead>

					<tbody>
                    <?php                    
					foreach( $list->result() as $row ){ 
                        
                        $p_address = "{$row->address_1} {$row->address_2}, {$row->address_3}";

                        // quote amount
                        $quote_qty = $row->qld_new_leg_alarm_num;
                        $price_240vrf = $this->system_model->get240vRfAgencyAlarm($row->agency_id);
                        $quote_price = ( $price_240vrf > 0 )?$price_240vrf:200;
                        $quote_total = $quote_price*$quote_qty;
                        
                        ?>
						<tr>
                            <td>
                                <a href='<?php echo $this->config->item("crm_link"); ?>/view_property_details.php?id=<?php echo $row->property_id; ?>'>
                                    <?php echo $p_address; ?>
                                </a>
                            </td>
							<td class="<?php echo ( $row->priority > 0 )?'j_bold':null; ?>">
									<a href="/agency/view_agency_details/<?php echo $row->agency_id; ?>">
										<?php echo $row->agency_name." ".( ( $row->priority > 0 )?' ('.$row->abbreviation.')':null ); ?>
									</a>
								</td>
							<td>$<?php echo number_format($quote_total,2); ?></td>										                            
						</tr>
					<?php                    
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
	<p>This page shows all properties that are not upgraded to the NEW QLD legislation</p>
	<pre>
		<code>
SELECT
p.`property_id`,
p.`address_1`,
p.`address_2`,
p.`address_3`,
p.`state`,
p.`postcode`,
p.`qld_new_leg_alarm_num`,

a.`agency_id`,
a.`agency_name`
FROM `property` AS p
LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
WHERE p.`qld_new_leg_alarm_num` > 0
AND p.`prop_upgraded_to_ic_sa` != 1
AND p.`state` = 'QLD'
AND p.`deleted` = 0
AND ( p.`is_nlm` = 0 OR p.`is_nlm` IS NULL )
LIMIT 0, 50</code>
	</pre>

</div>

