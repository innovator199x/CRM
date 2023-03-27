
<style>
    .col-mdd-3{
        max-width:15.5%;
    }
    .action_a, .action_div {
        color: #adb7be!important;
    }
    .btn_add_alarm{margin-top:13px;}
</style>

<div class="box-typical box-typical-padding">

    <?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => "/admin/alarm_guide"
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
echo form_open('/admin/alarm_guide',$form_attr);
?>
    <div class="for-groupss row">
        <div class="col-md-10 columns">
            <div class="row">
                <div class="col-md-2">
                    <label for="phrase_select">Phrase</label>
                    <input type="text" name="search" class="form-control" placeholder="ALL" value="<?php echo $this->input->get_post('search'); ?>" />
                </div>
                <div class="col-md-1 columns">
                    <label class="col-sm-12 form-control-label">&nbsp;</label>
                    <button type="submit" class="btn btn-inline">Search</button>
                </div>
            </div>
        </div>
        <div class="col-md-2 columns text-right">
            <a href="/admin/add_alarm" class="btn btn_add_alarm">Add Alarm</a>
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
                            <th>Make</th>
                            <th>Model</th>
                            <th>Power Type</th>
                            <th>Detection Type</th>
                            <th>Date Location</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php 
                            foreach($list->result_array() as $row){
                        ?>
                            <tr>
                                <td> <?php echo $row['make'] ?> </td>
                                <td> <a href="/admin/alarm_detail/<?php echo $row['smoke_alarm_id'] ?>"><?php echo $row['model'] ?></a> </td>
                                <td> <?php echo $row['power_type'] ?> </td>
                                <td> <?php echo $row['detection_type'] ?> </td>
                                <td> <?php echo $row['loc_of_date'] ?> </td>
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
    This page is a guide for our most common alarms. Refer to it when customers have questions.
	</p>
    <pre>
<code>SELECT *
FROM `smoke_alarms`
WHERE `smoke_alarm_id` > 0
AND `country_id` = <?php echo COUNTRY ?> 
ORDER BY `make` ASC
LIMIT 50</coe>
    </pre>

</div>
<!-- Fancybox END -->


<script type="text/javascript">

  jQuery(document).ready(function(){

      <?php if ($this->session->flashdata('status') && $this->session->flashdata('status') == 'success') { ?>
            swal({
                title: "Success!",
                text: "<?php echo $this->session->flashdata('success_msg') ?>",
                type: "success",
                confirmButtonClass: "btn-success",
                showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                timer: <?php echo $this->config->item('timer') ?>
            });
        <?php } else if ($this->session->flashdata('status') && $this->session->flashdata('status') == 'error') { ?>
                    swal({
                        title: "Error!",
                        text: "<?php echo $this->session->flashdata('error_msg') ?>",
                        type: "error",
                        confirmButtonClass: "btn-danger"
                    });
        <?php } ?>

       $(".fancybox_btn").fancybox({
            hideOnContentClick: false,
            hideOnOverlayClick: false
        });


  })


</script>
