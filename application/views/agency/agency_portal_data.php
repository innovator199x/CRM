<style>
    .col-mdd-3{
        max-width:15.5%;
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
        'link' => "/agency/agency_portal_data"
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
        echo form_open('/agency/agency_portal_data',$form_attr);
        ?>
            <div class="for-groupss row">


                <div class="col-lg-10 col-md-12 columns">
                    <div class="row">


                        <div class="col-mdd-3">
                           <label>Agency</label>
                            <select id="agency_filter" name="agency_filter" class="form-control">
                                <option value="">ALL</option>
                                <?php
                                    foreach($agency_filter->result_array() as $row){
                                        if($row['agency_id']!=""){
                                        $selected = ($row['agency_id']==$this->input->get_post('agency_filter'))?'selected="true"':NULL;
                                ?>
                                        <option <?php echo $selected; ?> value="<?php echo $row['agency_id'] ?>"><?php echo $row['agency_name'] ?></option>
                                <?php
                                    }}
                                ?>
                            </select>
                        </div>

                          <div class="col-mdd-3">
                           <label>User</label>
                            <select id="user_filter" name="user_filter" class="form-control">
                                <option value="">ALL</option>
                                <?php
                                    foreach($user_filter->result_array() as $row){
                                        if($row['agency_user_account_id']!=""){
                                        $selected = ($row['agency_user_account_id']==$this->input->get_post('user_filter'))?'selected="true"':NULL;
                                ?>
                                        <option <?php echo $selected; ?> value="<?php echo $row['agency_user_account_id'] ?>"><?php echo $row['fname'].", ".$row['lname'] ?></option>
                                <?php
                                    }}
                                ?>
                            </select>
                        </div>

                          <div class="col-mdd-3">
							<label>From</label>
							<input name="date_from_filter" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text" placeholder="ALL" value="<?php echo $this->input->get_post('date_from_filter'); ?>">
						</div>
                        <div class="col-mdd-3">
							<label>To</label>
							<input name="date_to_filter" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text" placeholder="ALL" value="<?php echo $this->input->get_post('date_to_filter'); ?>">
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
						<th>User</th>
						<th>Date</th>
						<th>Logged in</th>
						<th>IP</th>
						</tr>
					</thead>

					<tbody>

                    <?php

                        foreach($lists->result_array() as $row){
                    ?>

                        <tr>
                            <td class="<?php echo ( $row['priority'] > 0 )?'j_bold':null; ?>">
                                <?php echo $row['agency_name']." ".( ( $row['priority'] > 0 )?' ('.$row['abbreviation'].')':null ); ?>
                            </td>
                            <td><?php echo "{$row['fname']} {$row['lname']}"; ?></td>
                            <td><?php echo ( $this->system_model->isDateNotEmpty($row['date_created']) )?date('d/m/Y',strtotime($row['date_created'])):''; ?></td>
                            <td><?php echo ( $this->system_model->isDateNotEmpty($row['date_created']) )?date('H:i',strtotime($row['date_created'])):''; ?></td>													
						    <td><?php echo $row['ip']; ?></td>	
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

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
	This page displays all log ins to the Agency portal.
	</p>
    <pre>
<code>SELECT `aul`.`agency_user_login_id`, `aul`.`ip`, `aul`.`date_created`, `aua`.`agency_user_account_id`, `aua`.`fname`, `aua`.`lname`, `a`.`agency_id`, `a`.`agency_name`
FROM `agency_user_logins` as `aul`
LEFT JOIN `agency_user_accounts` as `aua` ON `aua`.`agency_user_account_id` = `aul`.`user`
LEFT JOIN `agency` as `a` ON `a`.`agency_id` = `aua`.`agency_id`
WHERE `aul`.`agency_user_login_id` > 0
ORDER BY `aul`.`date_created` DESC
LIMIT 50</code>
    </pre>

</div>
<!-- Fancybox END -->


<script>
jQuery(document).ready(function(){

	//success/error message sweel alert pop  start
	<?php if( $this->session->flashdata('status') &&  $this->session->flashdata('status') == 'success' ){?>
		swal({
			title: "Success!",
			text: "<?php echo $this->session->flashdata('success_msg') ?>",
			type: "success",
			confirmButtonClass: "btn-success"
		});
	<?php }else if(  $this->session->flashdata('status') &&  $this->session->flashdata('status') == 'error'  ){ ?>
		swal({
			title: "Error!",
			text: "<?php echo $this->session->flashdata('error_msg') ?>",
			type: "error",
			confirmButtonClass: "btn-danger"
		});
	<?php } ?>
	//success/error message sweel alert pop  end




});
</script>
