
<style>
    .col-mdd-3{
        max-width:30.5%;
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
        'link' => "/users/user_manager"
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
        echo form_open('/users/user_manager',$form_attr);
        ?>
            <div class="for-groupss row">
                <div class="col-lg-10 col-md-12 columns">
                    <div class="row">

                        <div class="col-mdd-3">
                            <label for="agency_select">Agency</label>
                            <input type="text" name="agency_filter" class="form-control" value="<?php echo $this->input->get_post('agency_filter') ?>">
                        </div>

                        <div class="col-md-1 columns">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <input class="btn" type="submit" name="btn_search" value="Search">
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
				<table class="table table-hover main-table">
					<thead>
						<tr>
							<th>Agency</th>
							<th>Email</th>
							<th>Login</th>
							<th>Last Changed</th>
						</tr>
					</thead>

					<tbody>
                    <?php 
                      if($lists->num_rows()>0){
                        foreach($lists->result_array() as $row){

                            //get agency admin
                            $sel_query = "
                            aua.`email`,
                            aua.`password`,
                            aua.`reset_password_code_ts`,
                            aua.`password_changed_ts`,
                            a.`agency_id`,
                            a.`agency_name`
                            ";
                            $admin_row_params = array(
                                'sel_query' => $sel_query,
                                'agency' => $row['agency_id'],
                                'user_type' => 1,
                                'active' => 1,
                                'limit' => 1,
                                'offset' => 0,
                                'sort_list' => array(
                                    array(
                                        'order_by' => 'aua.agency_user_account_id',
                                        'sort' => 'DESC'
                                    )
                                ),
                                'display_query' => 0
                            );
                            $admin_row = $this->agency_model->getAgencyAdmin($admin_row_params)->row_array();
                    ?>
                            <tr>
                                <td><?php   echo $this->gherxlib->crmLink('vad',$row['agency_id'],$row['agency_name']); ?></td>
                                <td><?php echo $admin_row['email'] ?></td>
                                <td>
                                    <?php
                                    if( $row['initial_setup_done'] == 1 ){
                                        ?>
                                    
                                        <a href="<?php echo $this->config->item('agency_link') ?>?user=<?php echo $admin_row['email']; ?>&agency_id=<?php echo $row['agency_id']; ?>&pass=<?php echo $admin_row['password'] ?>&crm_login=1" target="_blank">
                                           <span style="font-size:20px;" class="fa fa-location-arrow"></span>
                                        </a>
                                    
                                    <?php	
                                    }else{ 
                                        echo "Set up not done";
                                    }
                                    ?>							
                                </td>
                              <td>
                                    <?php 
                                        echo ($this->system_model->isDateNotEmpty($admin_row['password_changed_ts'])==true)?date('d/m/Y H:i', strtotime($admin_row['password_changed_ts'])):NULL;
                                    ?>
                              </td>
                            </tr>
                    <?php
                        }
                    }else{
                        echo "<tr><td colspan='8'>No Data</td></tr>";
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

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
  Lorem ipsum...
	</p>

</div>
<!-- Fancybox END -->


<script type="text/javascript">

  

</script>