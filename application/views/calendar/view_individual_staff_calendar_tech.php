<?php  ?>
<link rel="stylesheet" href="/inc/css/lib/fullcalendar/fullcalendar.min.css">
<link rel="stylesheet" href="/inc/css/separate/pages/calendar.min.css">
<style>
    .calendar-page-content-in{
        margin-right:0px!important;
    }
	.fc-popover{
		width:300px!important;
	}
	#calendar .fc-header-toolbar{
		border: 1px solid #d8e2e7;
		border-bottom:0px;
	}
	.fc-addNewButton-button{
		background:#00a8ff;
		color:#fff;
		font-weight:normal;
	}
</style>
<div class="box-typical box-typical-padding">
    <?php 
  
        // breadcrumbs template
        $bc_items = array(
            array(
                'title' => $title,
                'status' => 'active',
                'link' => "/calendar/my_calendar"
            )
        );
        $bc_data['bc_items'] = $bc_items;
        $this->load->view('templates/breadcrumbs', $bc_data);
	?>
    <section>
		<div class="body-typical-body">


                <div class="calendar-page">
					<div class="calendar-page-content">
						<div class="calendar-page-content-in">
							<div id='calendar'></div>
						</div><!--.calendar-page-content-in-->
					</div><!--.calendar-page-content-->
				</div><!--.calendar-page-->
            

		</div>
	</section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>This page shows all calendar activity</p>

</div>
<!-- Fancybox END -->




	<script type="text/javascript" src="/inc/js/lib/match-height/jquery.matchHeight.min.js"></script>
	<script type="text/javascript" src="/inc/js/lib/moment/moment-with-locales.min.js"></script>
	<script src="/inc/js/lib/fullcalendar/fullcalendar.min.js"></script>
	<script src="/inc/js/lib/fullcalendar/tech_calendar_tech.js"></script>
	
	
	<script>


		jQuery(document).ready(function() {

		

		}); //document ready end


	   
	</script>