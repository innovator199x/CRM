<style>
    .col-mdd-3{
        max-width:15.5%;
    }
	#region_dp_div {
		position: absolute;
		top: 65px;
	}
    .btn_edit_pencil{
        border: none;
        float: left;
        margin-right: 4px;
        background: no-repeat;
        color: #adb7be;
    }
</style>


<div class="box-typical box-typical-padding">

	<?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => "/properties/view_regions/{$this->uri->segment(3)}"
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
        echo form_open("/properties/view_regions/{$this->uri->segment(3)}",$form_attr);
        ?>
            <div class="for-groupss row">

<!--
                <div class="col-lg-8 col-md-12 columns">
                    <div class="row">



                       <div class="col-md-3">
							<label for="phrase_select">Phrase</label>
							<input type="text" name="search" class="form-control" placeholder="Text" value="<?php echo $this->input->get_post('search'); ?>" />
						</div>

                        <div class="col-md-1 columns">
                            <label class="col-sm-12 form-control-label">&nbsp;</label>
                            <input class="btn" type="submit" name="btn_search" value="Search">
                        </div>
                        
                    </div>

                </div>-->
                <div class="col-lg-4 col-md-12 columns">
                    <div style="margin-top:8px">
                        <a href="/properties/add_main_region" class="btn">Add New</a>
                        <a href="/admin/add_subregion" class="btn">Add New Sub Region</a>
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
							<th><?php  echo $this->gherxlib->getDynamicState($this->config->item('country')); ?></th>
							<th><?php  echo $this->gherxlib->getDynamicRegion($this->config->item('country')); ?></th>
							<th>Sub Region</th>
							<th style="width:30%">Postcodes</th>
                            <th>Action</th>
						</tr>
					</thead>

					<tbody>
                        <?php 
                            foreach($regions->result_array() as $region){
                        ?>
                                <tr>
                                    <td><?php echo $region['region_state'] ?></td>
                                    <td><a href="/reports/edit_main_region/<?php echo$region['regions_id'];?>"><?php echo $region['region_name'];?></a></td>
                                    <td>
                                        <form action="/admin/edit_subregion" method="POST">
                                            <input type="hidden" name="subregion_id" value="<?php echo $region['sub_region_id']; ?>">
                                            <input type="hidden" name="subregion_name" value="<?php echo $region['subregion_name']; ?>">
                                            <input type="hidden" name="region_id" value="<?php echo $region['regions_id']; ?>">
                                            <input type="submit" name="submit" value="<?php echo $region['subregion_name']; ?>" style="border: none!important;background-color: #fff !important;cursor: pointer;color: #0082c6;">
                                        </form>
                                    </td>
                                    <td><?php echo str_replace(",",", ",$region['postcode']);?></td>
                                    <td class="action_div">
                                    <form action="/admin/edit_subregion" method="POST">
                                            <input type="hidden" name="subregion_id" value="<?php echo $region['sub_region_id']; ?>">
                                            <input type="hidden" name="subregion_name" value="<?php echo $region['subregion_name']; ?>">
                                            <input type="hidden" name="region_id" value="<?php echo $region['regions_id']; ?>">
                                            <button class="btn_edit_pencil"><i class="font-icon font-icon-pencil"></i></button>
                                        </form>
                                        | 
                                        <a data-id="<?php echo $region['postcode_region_id'] ?>" data-toggle="tooltip" title="Delete" href="javascript:void(0)" class="btn_delete action_a" ><span class="glyphicon glyphicon-trash"></span></a>
                                    </td>
                                </tr>
                        <?php
                            }
                        ?>
					</tbody>

				</table>
			</div>
			

		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
	This page allows you to move properties to a new region by moving postcodes
	</p>

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
			confirmButtonClass: "btn-success",
			showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
            timer: <?php echo $this->config->item('timer') ?>
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


    $('.btn_delete').click(function(){

            var id = $(this).attr('data-id');

            swal({
                title: "Warning!",
                text: "Are you sure you want to delete?",
                type: "warning",
                showCancelButton: true,
                cancelButtonText: "Cancel!",
                confirmButtonClass: "btn-success",
                cancelButtonClass: "btn-danger",
                confirmButtonText: "Yes",                       
                closeOnConfirm: false,
            },
            function(isConfirm) {
                
                if (isConfirm) { // yes			
                        
                        $('#load-screen').show(); //show loader
                        jQuery.ajax({
                            type: "POST",
                            url: "/properties/deleteRegion",
                            data: { 
                                id: id
                            }

                        }).done(function( retval ) {

                                $('#load-screen').hide(); //hide loader
                                swal({
                                    title:"Success!",
                                    text: "Region Deleted Successfully",
                                    type: "success",
                                    showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
                                    timer: <?php echo $this->config->item('timer') ?>
                                });	
                                
                                var full_url = window.location.href;
                                setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);	
                                
                        });	
                }
                
            });

    })


});
</script>
