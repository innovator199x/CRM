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
    <!--
	<header class="box-typical-header">
		<form action="<?php $uri; ?>" method="post">
			<div class="box-typical box-typical-padding">
				<div class="for-groupss row">
					<div class="col-md-10 columns">
						<div class="row">	

							<div class="ml-2">
								<label for="agency_filter">Agency</label>
								<select id="agency_filter" name="agency_filter"  class="form-control">                                
									<option value="">---</option>
                                    <?php
                                    /*
                                    foreach( $distinct_agency_sql->result() as $distinct_agency_row ){ ?>
                                        <option value="<?php echo $distinct_agency_row->agency_id; ?>" <?php echo ( $distinct_agency_row->agency_id == $this->input->get_post('agency_filter') )?'selected':null;  ?>>
                                            <?php echo $distinct_agency_row->agency_name; ?>
                                        </option>
                                    <?php
                                    }
                                    */
                                    ?>
								</select>							
							</div>

                            <div class="ml-2">
								<label for="job_status_filter">Job Status</label>
								<select id="job_status_filter" name="job_status_filter"  class="form-control">                                
									<option value="">---</option>
                                    <option value="Completed" <?php echo ( $this->input->get_post('job_status_filter') == 'Completed' )?'selected':null;  ?>>Completed</option>
                                    <option value="Cancelled" <?php echo ( $this->input->get_post('job_status_filter') == 'Cancelled' )?'selected':null;  ?>>Cancelled</option>
								</select>							
							</div>

							<div class="ml-2">
								<label class="col-sm-12 form-control-label">&nbsp;</label>
								<input type="submit" name="search_submit" id="search_submit" value="Search" class="btn">
							</div>				
						</div>
					</div>
				</div>
			</div>
		</form>
	</header>
    -->

	<div class="body-typical-body">
		<div class="table-responsive">
			<table class="table table-hover main-table table-striped" id="serverside-table">
				<thead>
					<tr>    
                        <th>Company</th>
                        <th>Address</th>
                        <th>Email</th>
                        <th>Phone</th>                        
                        <th>Connect to Agency</th>
                        <th>Connect</th>                              									                            						                           
					</tr>
					<?php   
                    $country_txt = ( $this->config->item('country') == 1 )?'AUSTRALIA':'NEW ZEALAND';                      
					foreach( $json_dec_arr as $comp_auth_obj ){ 

                        if( !in_array($comp_auth_obj->key,$current_auth_keys_arr) ){ // hide already connected
                        
                            $json_obj = $this->property_tree_model->get_agency_using_auth_key($comp_auth_obj->key);                        
                            $address_obj = $json_obj->address;

                            // AU and NZ are in 1 accounts, so it needs country filter to display correct data per country
                            if( $address_obj->country == $country_txt ){

                                // street
                                if( $address_obj->unit != '' && $address_obj->street_number != '' ){
                                    $street_unit_num = "{$address_obj->unit}/{$address_obj->street_number}";
                                }else if( $address_obj->unit != '' ){
                                    $street_unit_num = "{$address_obj->unit}";
                                }else if( $address_obj->street_number != '' ){
                                    $street_unit_num = "{$address_obj->street_number}";
                                }

                                $agency_address = "{$street_unit_num} {$address_obj->address_line_1} {$address_obj->suburb} {$address_obj->state} {$address_obj->post_code}";    
                                ?>

                                <tr>
                                    <td class="pt_agency_name"><?php echo $comp_auth_obj->company_name; ?></td>
                                    <td><?php echo $agency_address ?></td>
                                    <td><?php echo $json_obj->email_address; ?></td>
                                    <td><?php echo $json_obj->phone_number; ?></td>                                                       
                                    <td>
                                        <select class="form-control agency">                                
                                            <option value="">---</option>     
                                            <?php
                                            foreach( $agency_sql->result() as $agency_row ){ ?>
                                                <option value="<?php echo $agency_row->agency_id; ?>"><?php echo $agency_row->agency_name; ?></option>
                                            <?php
                                            }
                                            ?>                               
                                        </select>	
                                    </td>
                                    <td>
                                        <input type="hidden" class="auth_key" value="<?php echo $comp_auth_obj->key; ?>" />
                                        <button type="button" class="btn btn_connect">Connect</button>
                                    </td>
                                </tr>

                            <?php	
                            }
                        }			
                    }                    
					?>
				</thead>
				<tbody></tbody>
			</table>	
		</div>
	</div>

	<nav aria-label="Page navigation example" style="text-align:center">
		<?php echo $pagination; ?>
	</nav>

	<div class="pagi_count text-center">
		<?php echo $pagi_count; ?>
	</div>

</div>


<!-- Fancybox START -->
<!-- ABOUT TEXT -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >
	<h4><?php echo $title; ?></h4>
	<p>Lorem Ipsum</p>
</div>
<script>
jQuery(document).ready(function(){
    			
    jQuery(".btn_connect").click(function(){

        var btn_connect_dom = jQuery(this); 
        var parent_tr = btn_connect_dom.parents("tr:first");

        var agency = parent_tr.find(".agency").val();
        var auth_key = parent_tr.find(".auth_key").val(); 
        var pt_agency_name = parent_tr.find(".pt_agency_name").text();         
        var agency_name = parent_tr.find(".agency option:selected").text();
        
        if( agency > 0 ){

            if( confirm( "This will connect Property Tree "+pt_agency_name+" to "+agency_name+". Proceed?" ) ){           
            
                if( agency > 0 && auth_key != '' ){

                    jQuery('#load-screen').show(); 
                    jQuery.ajax({
                        url: "/property_tree/ajax_connect_agency",
                        type: 'POST',
                        data: { 
                            'agency': agency,
                            'auth_key': auth_key
                        }
                    }).done(function( pme_ret ){

                        // load PMe properties
                        jQuery('#load-screen').hide(); 
                        location.reload();                               

                    });

                }            

            }

        }else{

            alert("Please select agency to connect");

        }        
        
    });
    
});
</script>