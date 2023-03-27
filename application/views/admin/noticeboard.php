
<link rel="stylesheet" href="/inc/css/lib/summernote/summernote.css">
<style>
.popover-content{display:none;}
.checkbox input{
    position: relative!important;
    visibility: visible!important;
}
</style>
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

	<section>
		<div class="body-typical-body">
			<div class="row">
                <div class="col-md-12 columns">
                    <div class="agency_note">
                        <?php echo form_open('/admin/noticeboard','id=noteciboard_form'); ?>
                            <h4 class="m-t-lg with-border">Agency Noticeboard</h4>
                            <div class="form-group">
                                <textarea class="summernote" name="notice" id="notice" ><?php echo $noticeboard_row['notice'] ?></textarea>
                            </div>
                            <div class="form-group">
                                <input type="hidden" name="nb_id" value="<?php echo $noticeboard_row['id']; ?>" />
                                <input type="submit" class="btn" name="btn_modify_noticeboard" value="Modify">
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 columns">
                    <div class="agency_note">
                        <?php echo form_open('/admin/change_statement_generic_note','id=statement_generic_note_form'); ?>
                            <h4 class="m-t-lg with-border">Agency Statement Generic Note</h4>
                            <div class="form-group">
                                <textarea class="summernote" name="statement_generic_note" id="statement_generic_note"><?php echo $agency_statement_row['statements_generic_note']; ?></textarea>
                            </div>
                            <div class="form-group">
                                <input type="submit" class="btn" name="btn_modify_agency_statement" value="Modify">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
		</div>
	</section>

</div>


<!-- Fancybox START -->

<!-- ABOUT TEXT -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
   This page will allow you to add/edit agency noticeboard.
	</p>

</div>

<script type="text/javascript" src="/inc/js/lib/summernote/summernote.min.js"></script>
<script type="text/javascript">

$(function(){

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
    
    jQuery('.summernote').summernote();
    
});
</script>

