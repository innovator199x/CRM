
<div class="box-typical box-typical-padding">
    <?php 
 
        // breadcrumbs template
        $bc_items = array(
            array(
                'title' => $title,
                'status' => 'active',
                'link' => "/calendar/add_new_entry"
            )
        );
        $bc_data['bc_items'] = $bc_items;
        $this->load->view('templates/breadcrumbs', $bc_data);
	?>
    <section>
		<div class="body-typical-body">

       <a class="btn hidden_fancybox_link" data-fancybox="" data-type="ajax" data-src="/calendar/add_calendar_entry_static/?add=1" href="javascript:;">Add New Calendar Entry</a>
            

		</div>
	</section>

</div>

<!-- Fancybox Start -->

<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>This page allows to add new calendar entry.</p>

</div>

<!-- Fancybox END -->

<script type="text/javascript">
    $(document).ready(function() {
        $(".hidden_fancybox_link").fancybox().trigger('click');
    });
</script>