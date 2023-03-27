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
	.date_div {
		width: auto;
		margin-right: 13px;
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

				<div class="col-md-10 columns">
					<div class="row">	

                        <div class="col-md-2">
							<label for="agency_select">User</label>
							<select id="crm_user_filter" name="crm_user_filter"  class="form-control">
                                <option value="">---</option>
                                <?php   	                                					                            
                                foreach( $crm_user_filter_sql->result() as $crm_user_filter_row ){ ?>
                                    <option value="<?php echo $crm_user_filter_row->StaffID; ?>" <?php echo ( $crm_user_filter_row->StaffID == $this->input->get_post('crm_user_filter') )?'selected="selected"':null; ?>>
                                        <?php echo "{$crm_user_filter_row->FirstName} {$crm_user_filter_row->LastName}"; ?>
                                    </option>
                                <?php
                                }  	                                						                           
                                ?>
							</select>							
						</div>


						<div class="date_div">
							<label for="date_select">Date</label>
							<input name="date_filter" class="flatpickr form-control" data-allow-input="true" id="flatpickr" type="text" value="<?php echo $this->input->get_post('date_filter'); ?>" />
						</div>

						<div class="col-md-1 columns">
							<label class="col-sm-12 form-control-label">&nbsp;</label>
							<input type="submit" name="search_submit" value="Search" class="btn">
						</div>
						
					</div>

				</div>

				<!-- DL ICONS START -->
                <?php 
                $date = ($this->input->get_post('date')!="")?date('Y-m-d',$this->input->get_post('date')):NULL;
                ?>
				<!--
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
				-->
				<!-- DL ICONS END -->


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
                            <th>User</th> 							
                            <th>IP</th>     
                            <th>Date</th>  
                            <th>Time</th>                      												                            						                           
						</tr>
					</thead>

					<tbody>
                    <?php                                                       
					foreach( $crm_user_sql->result() as $crm_user_row ){                                       
                    ?>
						<tr>
                            <td>
                                <a href="/users/view/<?php echo $crm_user_row->StaffID; ?>" target="_blank">
                                    <?php echo "{$crm_user_row->FirstName} {$crm_user_row->LastName}"; ?>
                                </a>
                            </td>
                            <td><?php echo $crm_user_row->ip; ?></td>
							<td><?php echo ( $this->system_model->isDateNotEmpty($crm_user_row->date_created) )?date('d/m/Y', strtotime($crm_user_row->date_created)):''; ?></td>															                            
                            <td><?php echo ( $this->system_model->isDateNotEmpty($crm_user_row->date_created) )?date('H:i', strtotime($crm_user_row->date_created)):''; ?></td>
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

</div>

