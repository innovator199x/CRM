

	    </div><!--.container-fluid-->
	</div><!--.page-content-->



<!-- Fancybox Start -->
<a href="javascript:void(0);" id="search_fb_link" class="fb_trigger" data-fancybox data-src="#search_fb">Trigger the fancybox</a>
<div id="search_fb" class="fancybox" style="display:none;" >

<h4>Search</h4>

	<?php
		$form_attr = array(
			'id' => 'jform'
		);
		echo form_open('sys/search_results',$form_attr);
		?>

		<div class="form-group row">
			<select id="search_type" name="search_type" class="form-control">
				<option value="1">Job ID</option>
				<option value="2">Property ID</option>
				<option value="3">Phone</option>
				<option value="4">Address</option>
				<option value="5">Landlord</option>
				<option value="6">Agency</option>
				<option value="7">Building Name</option>
			</select>
		</div>
		<div class="form-group row">
			<input style="width: 300px;" class="form-control search_val" type="text" name="search_val" />
		</div>
		<div class="form-group row" style="margin-bottom:0;">
			<button type="submit" style="margin-bottom:0;" class="btn btn-inline">Go</button>
		</div>

	</form>

</div>
<!-- Fancybox END -->

<div class="modal bs-example-modal-sm" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
      	<h7>You've been idle for 30 Minutes <i class="fa fa-clock-o"></i></h7>
      </div>
      <div class="modal-body">
		<p>You will be logout in <span id="counter">15</span> second(s) unless you press 'Extend Session'.</p>
      	<i class="fa fa-question-circle"></i> Do you want to extend?
	  </div>
      <div class="modal-footer"><a href="javascript:;" id="extendBtn" class="btn btn-primary btn-block">Extend Session</a></div>
    </div>
  </div>
</div>

<?php
if( $exclude_gmap == false ){

	if( $is_tech_run_map == true ){ // used on tech run
	?>
		<script>
		function callbackGoogleAPI() {
			try {
				initGoogleAPI();
			}
			catch(ex) {}
		}
		</script>
		<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo $this->config->item('gmap_api_key'); ?>&callback=callbackGoogleAPI&v=3"></script>
	<?php
	}else{ // used on google address autocomplete
	?>
		<script>
		function initPlaces() {
			try {
				initAutocomplete();
			}
			catch(ex) {}
		}
		</script>
		<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo $this->config->item('gmap_api_key'); ?>&callback=initPlaces&libraries=places" async defer></script>
	<?php
	}
	
}
?>

<script src="/inc/js/lib/input-mask/jquery.mask.min.js"></script>
<script src="/inc/js/gherx.js"></script>

<script>

	$(document).idle({
		onIdle: function(){
			$('.bs-example-modal-sm').modal('show');
			var cTimer = setInterval(function(){ countdown(); },1000);
			jQuery("#extendBtn").click(function(){
				jQuery.ajax({
					type: "POST",
					url: "<?php echo base_url('/sys/check_agency_session') ?>",
				}).done(function(data){
					if (data == "true") {
						$('#counter').html(15)
					clearInterval(cTimer)
					$('.bs-example-modal-sm').modal('hide');
					}else {
						location.href = '<?=base_url()?>sys/logout';
					}
				});
			});
		},
		events: 'mousemove keydown mousedown touchstart',
		// extend to 120 minutes
		idle: 60000*120
		//idle: 60000
	})

	function countdown() {
			var i = document.getElementById('counter');
			if (parseInt(i.innerHTML)<=1) {
				$('.bs-example-modal-sm').modal('hide');
					location.href = '<?=base_url()?>sys/logout';
			}
			i.innerHTML = parseInt(i.innerHTML)-1;
	}

	$(document).ready(function() {


		// about page
		jQuery("#search_icon_fb").click(function(){
			jQuery("#search_fb_link").click();
		});

		// about page
		jQuery("#about_page_link").click(function(){
			jQuery("#about_page_fb_link").click();
		});

		/*
		// prevent session time out
		var refreshTime = 300000; // every 5 minutes in milliseconds
		window.setInterval( function() {
				jQuery.ajax({
						cache: false,
						type: "GET",
						url: "sys/refreshSession",
						success: function(data) {
							console.log('Refresh Session');
						}
				});
		}, refreshTime );
		*/

		// loader
		$("#status").fadeOut(250);
		$("#preloader").delay(250).fadeOut("slow");

		//init datepicker
		jQuery('.flatpickr').flatpickr({
			dateFormat: "d/m/Y",
			locale: {
				firstDayOfWeek: 1
			}
		});


		// region filter
		jQuery(document).mouseup(function (e){
			var container = jQuery("#region_dp_div");
			if (!container.is(e.target) // if the target of the click isn't the container...
				&& container.has(e.target).length === 0) {
				container.hide();
			}
		});
		jQuery("#region_filter_state").click(function(){
			jQuery("#region_dp_div").show();
		});

		// allow crm CI ---> old CRM login redirect
		jQuery(".page-content a").each(function(){

			var a_dom = jQuery(this);
			var href = a_dom.attr("href");
			var staff_id = '<?php echo $this->session->staff_id ?>';

			if( href != '' && href.search("http") != -1 ){ // valid links only

				// source: https://dmitripavlutin.com/parse-url-javascript/
				var url_obj = new URL(href);
				
				// allowed domains to update links
				var allowed_domains_arr = ["crmdev.sats.com.au", "crm.sats.com.au", "crm.sats.co.nz"];

				if( url_obj.hostname.search("ci") == -1 && jQuery.inArray( url_obj.hostname, allowed_domains_arr ) != -1 ){ // non-CI old crm sites only				

					var page = url_obj.pathname.substring(1)+url_obj.search;	// page and parameters	
					reconstruct_url = url_obj.origin+'/link_login.php?staff_id='+staff_id+'&page='+encodeURIComponent(page);
					a_dom.attr("href",reconstruct_url); // update link
					
				}			

			}

		});

	});
</script>

<script src="/inc/js/app.js"></script>

</body>
</html>
<?php
if( isset($start_load_time) ){
	$time_elapsed_secs = microtime(true) - $start_load_time;
	echo "<p style='text-align:center;'>Execution Time: {$time_elapsed_secs}</p>";
}
 ?>
